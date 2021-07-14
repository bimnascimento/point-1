<?php
/**
 * The main plugin class.
 *
 * Defines the plugin main functions and hooks for how to
 * enqueue the specific stylesheet and JavaScript.
 *
 * @package    WP_Edit_Menu
 * @author     saurav.rox <saurabadhikari.com.np>
 */
class WP_Edit_Menu_Main{

	function __construct()
	{
		add_action( 'init', array( $this, 'wpem_load_my_textdomain' ) );	
		add_action( 'admin_enqueue_scripts', array( $this, 'wpem_load_admin_scripts' ) );
		add_action( 'admin_footer', array( $this,'wpem_html_template' ) );
		add_action( 'wp_ajax_filter_menu', array( $this, 'wpem_remove_menu_entry' ) );
		add_action( 'wp_ajax_nopriv_filter_menu', array( $this, 'wpem_remove_menu_entry' ) );
	}

	/**
	 * Load plugin textdomain.
	 *
	 * @since 1.0.0
	 */
	function wpem_load_my_textdomain() {
	  load_plugin_textdomain( 'wp-edit-menu', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
	}

	/**
	 * Load plugin admin scripts.
	 *
	 * @since 1.0.0
	 */
	function wpem_load_admin_scripts() {
		
		wp_enqueue_style( 'wpem-custom-css', plugin_dir_url( __FILE__ ) . 'assets/css/custom.css', array(), '20150330', 'all' );
		wp_enqueue_script( 'wpem-custom-js', plugin_dir_url( __FILE__ ). 'assets/js/custom.js', array( 'jquery' ), '20150330', true );
		wp_localize_script(
				'wpem-custom-js',
				'menu_ajax_data',
				array( 'ajaxurl' => admin_url('admin-ajax.php') )
				);
		$extra_array = array(
					'lang' => array(
						'are_you_sure'       => __( 'Are you sure?', 'wp-edit-menu' ),
						'load_it'       => __( 'Selected items removed. Please reload the page.', 'wp-edit-menu' ),
					),
				);
		wp_localize_script( 'wpem-custom-js', 'WPEM_OBJ', $extra_array );
	}

	/**
	 * Load html template.
	 *
	 * @since 1.0.0
	 */
	function wpem_html_template()
	{ ?>
		<div class="temp-chkbox">
			<input type="checkbox" class="chkit" name="chk[{{index}}]">
		</div>
		<div class="menu-items-remove">
			<input type="button" class="button button-primary button-large" id="remove-items" name="submit" value="Remove Items">
		</div>
		<div class="temp-all-chkbox">
			<div class="wpem-chk-all"><label><input type="checkbox" class="all-chkit" name="chk-all"><?php _e('check to remove all the items','wp-edit-menu');?></label></div>
		</div>
	<?php
	}

	/**
	 * Function that removes menu item entry.
	 *
	 * @since 1.0.0
	 */
	function wpem_remove_menu_entry() {
		if($_REQUEST['val']) {
			$menuids = $_REQUEST['val'];
		   	foreach ( $menuids as $menuid ) {
	    		wp_delete_post( $menuid );
			}
			$result['type'] = "success";
		}
		else {
			$result['type'] = "error";
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		{
			$result = json_encode($result);
			echo $result;
		}
		wp_die();
	}
}
new WP_Edit_Menu_Main;