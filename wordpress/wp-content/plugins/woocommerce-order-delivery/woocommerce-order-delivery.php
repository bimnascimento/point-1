<?php
/**
 * Plugin Name: WooCommerce Order Delivery
 * Plugin URI: http://woothemes.com/products/woocommerce-order-delivery/
 * Description: Choose a delivery date during checkout for the order.
 * Version: 1.1.1
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: Themesquad
 * Developer URI: http://themesquad.com/
 * Requires at least: 4.1
 * Tested up to: 4.7.3
 *
 * Text Domain: woocommerce-order-delivery
 * Domain Path: /languages/
 *
 * Copyright: © 2009-2017 WooThemes.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), 'beaa91b8098712860ec7335d3dca61c0', '976514' );

/**
 * Singleton pattern
 */
if ( ! class_exists( 'WC_OD_Singleton' ) ) {
	require_once( 'includes/class-wc-od-singleton.php' );
}

if ( ! class_exists( 'WC_Order_Delivery' ) ) {

	final class WC_Order_Delivery extends WC_OD_Singleton {

		/**
		 * The plugin version.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var string
		 */
		public $version = '1.1.1';


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			parent::__construct();

			$this->define_constants();
			$this->includes();
			$this->init_hooks();
		}

		/**
		 * Define constants.
		 *
		 * @since 1.1.0
		 */
		public function define_constants() {
			$this->define( 'WC_OD_VERSION', $this->version );
			$this->define( 'WC_OD_PATH', plugin_dir_path( __FILE__ ) );
			$this->define( 'WC_OD_URL', plugin_dir_url( __FILE__ ) );
			$this->define( 'WC_OD_BASENAME', plugin_basename( __FILE__ ) );
		}

		/**
		 * Define constant if not already set.
		 *
		 * @since 1.1.0
		 * @access private
		 *
		 * @param string      $name  The constant name.
		 * @param string|bool $value The constant value.
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		}

		/**
		 * Auto-load in-accessible properties on demand.
		 *
		 * NOTE: Keep backward compatibility with some deprecated properties on this class.
		 *
		 * @since 1.1.0
		 *
		 * @param mixed $key The property name.
		 * @return mixed The property value.
		 */
		public function __get( $key ) {
			switch ( $key ) {
				case 'dir_path' :
					_deprecated_argument( 'WC_Order_Delivery->dir_path', '1.1.0', 'This property is deprecated and will be removed in future releases. Use the constant WC_OD_PATH instead.' );
					return WC_OD_PATH;

				case 'dir_url' :
					_deprecated_argument( 'WC_Order_Delivery->dir_url', '1.1.0', 'This property is deprecated and will be removed in future releases. Use the constant WC_OD_URL instead.' );
					return WC_OD_URL;

				case 'date_format' :
					_deprecated_argument( 'WC_Order_Delivery->date_format', '1.1.0', 'This property is deprecated and will be removed in future releases. Use the function wc_od_get_date_format() instead.' );
					return wc_od_get_date_format( 'php' );

				case 'date_format_js' :
					_deprecated_argument( 'WC_Order_Delivery->date_format', '1.1.0', 'This property is deprecated and will be removed in future releases. Use the function wc_od_get_date_format() instead.' );
					return wc_od_get_date_format( 'js' );

				case 'prefix' :
					_deprecated_argument( 'WC_Order_Delivery->prefix', '1.1.0', 'This property is deprecated and will be removed in future releases. Use the function wc_od_get_prefix() instead.' );
					return wc_od_get_prefix();
			}
		}

		/**
		 * Includes the necessary files.
		 *
		 * @since 1.0.0
		 */
		public function includes() {
			if ( is_woocommerce_active() ) {
				include_once( 'includes/class-wc-od-autoloader.php' );
				include_once( 'includes/wc-od-functions.php' );

				if ( is_admin() ) {
					include_once( 'includes/admin/wc-od-admin-init.php' );
				}
			}
		}

		/**
		 * Hook into actions and filters.
		 *
		 * @since 1.1.0
		 */
		private function init_hooks() {
			if ( is_woocommerce_active() ) {

				// Init.
				add_action( 'plugins_loaded', array( $this, 'init' ) );

				// Plugin action links.
				add_filter( 'plugin_action_links_' . WC_OD_BASENAME, array( $this, 'action_links' ) );

			} elseif ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_not_active' ) );
			}
		}

		/**
		 * Displays an admin notice when the WooCommerce plugin is not active.
		 *
		 * @since 1.0.0
		 */
		public function woocommerce_not_active() {
			if ( current_user_can( 'activate_plugins' ) ) :
			?>
			<div class="error">
				<p><strong><?php _e( 'WooCommerce Order Delivery', 'woocommerce-order-delivery' ); ?></strong>: <?php _e( 'The WooCommerce plugin is not active.', 'woocommerce-order-delivery' ); ?></p>
			</div>
			<?php
			endif;
		}

		/**
		 * Adds custom links to the plugins page.
		 *
		 * @since 1.0.0
		 *
		 * @param array $links The plugin links.
		 * @return array The filtered plugin links.
		 */
		public function action_links( $links ) {
			$settings_link = sprintf( '<a href="%1$s">%2$s</a>',
				wc_od_get_settings_url( WC_OD_Utils::get_shipping_options_section_slug() ),
				__( 'Settings', 'woocommerce-order-delivery' )
			);

			array_unshift( $links, $settings_link );

			return $links;
		}

		/**
		 * Init plugin.
		 *
		 * @since 1.0.0
		 */
		public function init() {
			// Load text domain.
			load_plugin_textdomain( 'woocommerce-order-delivery', false, dirname( WC_OD_BASENAME ) . '/languages' );

			// Load settings.
			$this->settings();

			// Load checkout.
			$this->checkout();

			// Load order details.
			$this->order_details();
		}

		/**
		 * Get Settings Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Settings
		 */
		public function settings() {
			return WC_OD_Settings::instance();
		}

		/**
		 * Get Checkout Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Checkout
		 */
		public function checkout() {
			return WC_OD_Checkout::instance();
		}

		/**
		 * Get Order_Details Class.
		 *
		 * @since 1.0.0
		 *
		 * @return WC_OD_Order_Details
		 */
		public function order_details() {
			return WC_OD_Order_Details::instance();
		}
	}

	/**
	 * The main function for returning the plugin instance and avoiding
	 * the need to declare the global variable.
	 *
	 * @since 1.0.0
	 *
	 * @return WC_Order_Delivery The *Singleton* instance.
	 */
	function WC_OD() {
		return WC_Order_Delivery::instance();
	}

	WC_OD();
}
