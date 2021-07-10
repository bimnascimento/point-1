<?php
/*
Plugin Name: WooCommerce Debugger
Plugin URI: http://mikejolley.com
Description: Enable template path hints, log hooks in your browser console, quickly generate a debug report, and disable/restore all plugins in bulk.
Version: 1.0.0
Author: Mike Jolley
Author URI: http://mikejolley.com
*/

function wc_debugger_init() {
	if ( current_user_can( 'manage_options' ) )
		$GLOBALS['WC_Debugger'] = new WC_Debugger();
}

add_action( 'plugins_loaded', 'wc_debugger_init' );

/**
 * WC_Debugger class.
 */
class WC_Debugger {

	var $log;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		$this->version = '1.0.0';
		$this->log = array();

		// Localise
		load_plugin_textdomain( 'wc-debugger', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Scipts and styles
		add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue' ) );

		// Logging + debug
		add_action( 'all', array( &$this, 'log_hook' ) );
		add_action( 'wp_footer', array( &$this, 'footer' ), 999 );
		add_action( 'init', array( &$this, 'actions' ) );

		// Template path hints
		if ( get_option( 'wc_debugger_show_hints' ) )
			add_action( 'woocommerce_before_template_part', array( &$this, 'start_template' ), 10, 3 );
	}

	/**
	 * enqueue function.
	 *
	 * @access public
	 */
	function enqueue() {
        wp_enqueue_style( 'wc-debugger-css', plugins_url( 'css/style.css', __FILE__ ) );
        wp_enqueue_script( 'wc-debugger-js', plugins_url( 'js/debug.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'jquery-tiptip', plugins_url( 'js/jquery.tipTip.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );
        wp_enqueue_script( 'jquery-blockui', plugins_url( 'js/jquery.blockUI.min.js', __FILE__ ), array( 'jquery' ), $this->version, true );

        $params = array(
			'i18n_disable' => __( 'Are you sure you want to disable all plugins? Use a your own risk!', 'wc-debugger' ),
			'report_url'   => admin_url( 'admin.php?page=woocommerce_status' )
		);

		wp_localize_script( 'wc-debugger-js', 'wc_debug_params', $params );

	}

	/**
	 * show_hook function.
	 *
	 * @access public
	 * @return void
	 */
	function log_hook() {
		global $wp_current_filter;

		$current_filter = array_pop( $wp_current_filter );

		if ( strstr( $current_filter, 'woocommerce' ) || strstr( $current_filter, 'wc' ) )
			$this->log[] = $current_filter;
	}


	/**
	 * footer function.
	 *
	 * @access public
	 * @return void
	 */
	function footer() {
		?>
		<script type="text/javascript">
			console.log("\n\n\n\n-----------------------[start]-----------------------");
			<?php
				foreach ( $this->log as $log )
					echo 'console.log( "' . esc_js( $log ) . '" );';
			?>
			console.log("-----------------------[end]-----------------------");
		</script>
		<?php
		$this->display();
	}


	/**
	 * display function.
	 *
	 * @access public
	 */
	function display() {
		?>
		<div id="wc_debugger">
			<ul class="actions">
				<li><?php _e( 'WC Debugger:', 'wc-debugger' ); ?></li>

				<?php if ( ! get_option( 'wc_debugger_show_hints' ) ) : ?>
					<li><a href="<?php echo add_query_arg( 'show_hints', 'yes' ); ?>"><?php _e( 'Show template hints', 'wc-debugger' ); ?></a></li>
				<?php else : ?>
					<li><a href="<?php echo add_query_arg( 'show_hints', 'no' ); ?>"><?php _e( 'Hide template hints', 'wc-debugger' ); ?></a></li>
				<?php endif; ?>

				<?php if ( ! get_option( 'wc_debug_plugins_disabled' ) ) : ?>
					<li><a href="<?php echo add_query_arg( 'disable_plugins', 1 ); ?>" class="disable_plugins"><?php _e( 'Disable plugins', 'wc-debugger' ); ?></a></li>
				<?php else : ?>
					<li><a href="<?php echo add_query_arg( 'restore_plugins', 1 ); ?>" class="restore_plugins"><?php _e( 'Restore plugins', 'wc-debugger' ); ?></a></li>
				<?php endif; ?>

				<li><a href="#" class="debug_report" download="debug_report.txt"><?php _e( 'Get Debug Report', 'wc-debugger' ); ?></a></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * actions function.
	 *
	 * @access public
	 * @return void
	 */
	function actions() {
		if ( ! empty( $_GET['disable_plugins'] ) && ! get_option( 'wc_debug_plugins_disabled' ) ) {
			$this->disable_plugins();
		}
		elseif ( ! empty( $_GET['restore_plugins'] ) && get_option( 'wc_debug_plugins_disabled' ) ) {
			$this->restore_plugins();
		}
		elseif ( ! empty( $_GET['show_hints'] ) && $_GET['show_hints'] == 'yes' ) {
			update_option( 'wc_debugger_show_hints', true );

			// Go back
			wp_redirect( esc_url( wp_get_referer() ) );
			exit;
		}
		elseif ( ! empty( $_GET['show_hints'] ) && $_GET['show_hints'] == 'no' ) {
			update_option( 'wc_debugger_show_hints', false );

			// Go back
			wp_redirect( esc_url( wp_get_referer() ) );
			exit;
		}
	}

	/**
	 * disable_plugins function.
	 *
	 * @access public
	 * @return void
	 */
	function disable_plugins() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		// Backup plugins
		update_option( 'wc_debug_plugins_disabled', 1 );
		update_option( 'active_plugins_backup', $active_plugins );

		// Remove WC and this plugin
		$filter = array(
			'woocommerce/woocommerce.php',
			'woocommerce-debugger/debugger.php'
		);

		$new_plugins = array_intersect( $active_plugins, $filter );

		update_option( 'active_plugins', $new_plugins );

		// Go back
		wp_redirect( esc_url( wp_get_referer() ) );
		exit;
	}

	/**
	 * restore_plugins function.
	 *
	 * @access public
	 * @return void
	 */
	function restore_plugins() {
		$active_plugins_backup = (array) get_option( 'active_plugins_backup', array() );

		delete_option( 'wc_debug_plugins_disabled' );
		delete_option( 'active_plugins_backup' );
		update_option( 'active_plugins', $active_plugins_backup );

		// Go back
		wp_redirect( esc_url( wp_get_referer() ) );
		exit;
	}

	/**
	 * show_template function.
	 *
	 * @access public
	 * @param mixed $template_name
	 * @param mixed $template_path
	 * @param mixed $located
	 * @return void
	 */
	function start_template( $template_name, $template_path, $located ) {

		$class = array( 'wc_template_path_hint' );

		if ( ! strstr( $located, 'woocommerce/templates/' ) )
			$class[] = 'overridden';

		echo '<div class="' . implode( ' ', $class ) . '"><label class="wc_template_file_name" data-tip="' . esc_attr( str_replace( ABSPATH, '', $located ) ) . '">' . esc_html( $template_name ) . '</label></div>';
	}
}