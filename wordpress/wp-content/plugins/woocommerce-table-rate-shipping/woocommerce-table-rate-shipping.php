<?php
/*
Plugin Name: WooCommerce Table Rate Shipping
Plugin URI: http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
Description: WooCommerce custom plugin designed to calculate shipping costs and add one or more rates based on a table of rules
Author: Bolder Elements
Author URI: http://www.bolderelements.net/
Version: 4.0.1

	Copyright: © 2017 Bolder Elements (email : info@bolderelements.net)
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) )
	exit;

add_action('plugins_loaded', 'woocommerce_table_rate_shipping_init', 0);

function woocommerce_table_rate_shipping_init() {

	//Check if WooCommerce is active
	if ( ! class_exists( 'WooCommerce' ) ) return;

	// Ensure there are not duplicate classes
	if ( class_exists( 'BE_Table_Rate_WC' ) ) return;

	// setup internationalization support
	load_plugin_textdomain('be-table-ship', false, 'woocommerce-table-rate-shipping/languages');

	// include Envato plugin updater file
	include_once( plugin_dir_path( __FILE__ ) . 'inc/envato-market-installer.php' );

	// included deprecated method for prior users
	if( get_option( 'be_woocommerce_shipping_zones' ) )
		include_once( plugin_dir_path( __FILE__ ) . 'deprecated/woocommerce-table-rate-shipping.php' );

	class BE_Table_Rate_WC {

		/*
		 * Table Rates Class
		 */
		public $table_rates;

		/**
		 * Cloning is forbidden. Will deactivate prior 'instances' users are running
		 *
		 * @since 4.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 4.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct() {
			// Include required files
			if( is_admin() ) {
				// Admin only includes
				add_action( 'admin_enqueue_scripts', array( $this, 'register_plugin_admin' ) );
				add_action( 'admin_footer', array( $this, 'add_script_admin' ) );
			}

			add_action( 'init', array( $this, 'includes' ) );
			add_action( 'woocommerce_after_shipping_rate', array( $this, 'display_option_description' ), 10, 2 );
			add_filter( 'woocommerce_shipping_methods', array( $this, 'add_shipping_method' ) );

		}


		/**
		 * setup included files
		 *
		 * @access public
		 * @return void
		 */
		function includes() {

			// Setup shipping method
			include_once( 'inc/class.shipping-method.php' );
			include_once( 'inc/class.table-rate_options.php' );
			include_once( 'inc/class.calculate-rates.php' );

			// Setup additional settings requirements
			include_once( 'inc/class.settings-shipping-classes.php' );
			include_once( 'inc/class.settings-table-rates.php' );
			$this->table_rates = new BETRS_Table_Rates();

			// Setup compatibility functions
			include_once( 'compatibility/comp.wpml.php' );

			// initialize frontend includes
			if( ! is_admin() ) {
				wp_enqueue_style( 'betrs_frontend_css', plugins_url( 'assets/css/frontend.css', __FILE__ ), '1.0', true );
			}

		}

		/**
		 * add_cart_rate_method function.
		 *
		 * @package		WooCommerce/Classes/Shipping
		 * @access public
		 * @param array $methods
		 * @return array
		 */
		function add_shipping_method( $methods ) {
			$methods['betrs_shipping'] = 'BE_Table_Rate_Method';
			//$methods['table_rate_shipping'] = 'BE_Table_Rate_Method';
			return $methods;
		}


	    /**
	     * display description if applicable.
	     *
	     * @access public
	     * @param mixed $method
	     * @return void
	     */
	    function display_option_description( $method, $index ) {

	    	$meta_data = $method->get_meta_data();
	    	if( isset( $meta_data['description'] ) )
	    		echo '<div class="betrs_option_desc">' . wp_kses_data( $meta_data['description'] ) . '</div>';
	    }


		/**
		 * Modify Scripts in Dashboard
		 *
		 * @access public
		 * @return void
		 */
		public function register_plugin_admin( $hook_suffix ) {
			wp_enqueue_script( 'jquery-ui-widget' );
			wp_enqueue_script( 'jquery-ui-position' );
			wp_enqueue_script( 'jquery-ui-button' );
			wp_enqueue_script( 'jquery-ui-menu' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'betrs_settings_js', plugins_url( 'assets/js/settings.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'betrs_settings_table_rates_js', plugins_url( 'assets/js/settings.table-rates.js', __FILE__ ), array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'comiseo.daterangepicker', plugins_url( 'assets/js/jquery.comiseo.daterangepicker.js', __FILE__ ), array( 'jquery', 'jquery-ui-widget', 'jquery-ui-position', 'jquery-ui-button', 'jquery-ui-menu', 'jquery-ui-datepicker' ), '1.0', true );
			wp_enqueue_script( 'moment.js', plugins_url( 'assets/js/moment.min.js', __FILE__ ), array( 'jquery' ), '1.0', true );

			wp_enqueue_style( 'betrs_dashboard_css', plugins_url( 'assets/css/dashboard.css', __FILE__ ), '1.0', true );
			wp_enqueue_style( 'comiseo.daterangepicker', plugins_url( 'assets/css/jquery.comiseo.daterangepicker.css', __FILE__ ), '1.0', true );
		}


		/**
		 * Add Script Directly to Dashboard Foot
		 */
		public function add_script_admin() {
			$betrs_data = array();

			// Setup translated strings
			$betrs_data = array(
				'ajax_url'					=> addcslashes( admin_url( 'admin-ajax.php', 'relative' ), '/' ),
				'ajax_loader_url'			=> plugins_url( 'assets/img/loader.gif', __FILE__ ),
				'text_ok'					=> __( 'OK' ),
				'text_edit'					=> __( 'Edit' ),
				'text_error'				=> __( 'Error' ),
				'text_upload'				=> __( 'Upload' ),
				'text_cancel'				=> __( 'Cancel' ),
				'text_delete_confirmation'	=> __( 'Are you sure you want to do this? Delete actions cannot be undone.', 'be-table-ship' ),
				'text_importing_table'		=> __( 'Import Table of Rates', 'be-table-ship' ),
				'text_importing_csv'		=> __( 'Select a CSV file', 'be-table-ship' ),
				'text_exporting'			=> __( 'Exporting', 'be-table-ship' ),
				);
?>
<script type='text/javascript'>
/* <![CDATA[ */
var betrs_data = <?php echo json_encode( $betrs_data ) . "\n"; ?>
/* ]]> */
</script>
<?php
		}

	} /* End Class BE_Table_Rate_WC */

	$GLOBALS['betrs_shipping'] = new BE_Table_Rate_WC();

} // End woocommerce_table_rate_shipping_init.
 

/**
 * Add links to dashboard Plugins page
 *
 * @access public
 * @return void
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'be_table_rate_wc_action_links' );
function be_table_rate_wc_action_links( $links ) {
	return array_merge(
		array(
			'settings' => '<a href="' . get_admin_url() . '/admin.php?page=wc-settings&tab=shipping">' . __( 'Settings', 'be-table-ship' ) . '</a>',
			'support' => '<a href="http://bolderelements.net/support/" target="_blank">' . __( 'Support', 'be-table-ship' ) . '</a>'
		),
		$links
	);
 
}

?>