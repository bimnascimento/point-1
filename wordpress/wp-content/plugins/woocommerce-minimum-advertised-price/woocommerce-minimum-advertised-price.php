<?php
/**
 * Plugin Name: WooCommerce Minimum Advertised Price
 * Plugin URI: http://www.woocommerce.com/products/minimum-advertised-price/
 * Description: Easily set a minimum advertised price and hide the regular price until the product is added to the cart
 * Author: SkyVerge
 * Author URI: http://www.woocommerce.com
 * Version: 1.8.0
 * Text Domain: woocommerce-minimum-advertised-price
 * Domain Path: /i18n/languages/
 *
 * Copyright: (c) 2013-2017, SkyVerge (info@skyverge.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   WC-Minimum-Advertised-Price
 * @author    SkyVerge
 * @category  Plugin
 * @copyright Copyright (c) 2013-2017, SkyVerge
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

// Required functions
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'woo-includes/woo-functions.php' );
}

// Plugin updates
woothemes_queue_update( plugin_basename( __FILE__ ), '7a296466c278db3913f7e3bed2a160d1', '201953' );

// WC active check
if ( ! is_woocommerce_active() ) {
	return;
}

// Required library class
if ( ! class_exists( 'SV_WC_Framework_Bootstrap' ) ) {
	require_once( plugin_dir_path( __FILE__ ) . 'lib/skyverge/woocommerce/class-sv-wc-framework-bootstrap.php' );
}

SV_WC_Framework_Bootstrap::instance()->register_plugin( '4.6.0', __( 'WooCommerce Minimum Advertised Price', 'woocommerce-minimum-advertised-price' ), __FILE__, 'init_woocommerce_minimum_advertised_price', array(
	'minimum_wc_version'   => '2.5.5',
	'minimum_wp_version'   => '4.1',
	'backwards_compatible' => '4.4',
) );

function init_woocommerce_minimum_advertised_price() {

/**
 * # WooCommerce Minimum Advertised Price Main Plugin Class
 *
 * ## Plugin Overview
 *
 * This plugin adds a new pricing field named MAP to simple and variation products,
 * which when set with a numeric value, will be shown on the frontend with a
 * strikethrough in place of the true product price, along with either a message to
 * "See price in cart" or a link/button to click to "See Price".  Once the product
 * is in the cart the true price will be shown.  This allows shops to enforce
 * resale price maintenance policies.
 *
 *
 * ## Terminology
 *
 * + `MAP` - Minimum Advertised Price, the lowest price a retailer or distributor
 *   can advertise for a product.
 *
 * ## Admin Considerations
 *
 * A MAP meta field is added to the product data tab for simple products and variable products
 * Settings are added under WooCommerce > Settings > Catalog
 *
 * ## Frontend Considerations
 *
 * On the catalog page the MAP will be shown in place of the product price, with
 * a strikethrough and text indicating that the customer can "See price in cart"
 * or take an action to "See Price", as determined by the Catalog configuration.
 * If the price can be seen 'on gesture' a button will be displayed for
 * non-variable products to show the price in a modeless dialog box, which is
 * rendered by the included template file.
 *
 * On the product page the pricing will be displayed similarly to the catalog
 * page, though the "on gesture" action will be available for variable products
 * once a variation is configured.
 *
 * In the cart the MAP price will be shown along with the actual price, and the
 * savings, as determined by teh Catalog configuration.
 *
 * ## Database
 *
 * ### Global Settings
 *
 * + `wc_minimum_advertised_price_display_location` - where to display the actual price, 'on_gesture' or 'in_cart'
 * + `wc_minimum_advertised_price_label` - the label shown next to the minimum advertised price, ie "See Price in Cart"
 * + `wc_minimum_advertised_price_show_savings` - whether to display a "save $X.XX" label along with the actual price
 *
 * ### Options table
 *
 * + `wc_minimum_advertised_price_version` - the current plugin version, set on install/upgrade
 *
 * ### Product Meta
 *
 * + `_minimum_advertised_price` - the minimum advertised price for the product
 * + `_min_minimum_advertised_price` - the smallest minimum advertised price of a set of product variations
 * + `_max_minimum_advertised_price` - the largest minimum advertised price of a set of product variations
 *
 * @since 1.0
 */
class WC_Minimum_Advertised_Price extends SV_WC_Plugin {


	/** the current plugin version */
	const VERSION = '1.8.0';

	/** @var WC_Minimum_Advertised_Price single instance of this plugin */
	protected static $instance;

	/** string the plugin id */
	const PLUGIN_ID = 'minimum_advertised_price';

	/** @var \WC_Minimum_Advertised_Price_Admin The admin handling class */
	protected $admin;

	/** @var \WC_Minimum_Advertised_Price_Frontend The frontend handling class */
	protected $frontend;

	/** @var bool whether to display the savings off the MAP or not */
	private $show_savings;


	/**
	 * Initializes the plugin
	 *
	 * @since 1.0
	 */
	public function __construct() {

		parent::__construct(
			self::PLUGIN_ID,
			self::VERSION,
			array(
				'text_domain' => 'woocommerce-minimum-advertised-price',
			)
		);

		// include required files
		$this->includes();

		// templates
		add_action( 'init', array( $this, 'include_template_functions' ), 25 );
	}


	/**
	 * Include required files
	 *
	 * @since 1.0
	 */
	private function includes() {

		if ( is_admin() ) {
			$this->admin_includes();
		} else {
			$this->frontend = $this->load_class( '/includes/class-wc-minimum-advertised-price-frontend.php', 'WC_Minimum_Advertised_Price_Frontend' );
		}
	}


	/**
	 * Include required files for admin
	 *
	 * @since 1.0
	 */
	private function admin_includes() {

		// load admin class
		$this->admin = $this->load_class( '/includes/admin/class-wc-minimum-advertised-price-admin.php', 'WC_Minimum_Advertised_Price_Admin' );
	}


	/**
	 * Method used to init WooCommerce Minimum Advertised Price template functions,
	 * making them pluggable by plugins and themes.
	 *
	 * @internal
	 *
	 * @since 1.0
	 */
	public function include_template_functions() {

		require_once( $this->get_plugin_path() . '/includes/wc-minimum-advertised-price-template-functions.php' );
	}


	/** Helper methods ******************************************************/


	/**
	 * Main Minimum Advertised Price Instance, ensures only one instance is/can be loaded
	 *
	 * @since 1.2.0
	 * @see \wc_minimum_advertised_price()
	 * @return \WC_Minimum_Advertised_Price
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * Get the Admin instance
	 *
	 * @since 1.7.0
	 * @return \WC_Minimum_Advertised_Price_Admin
	 */
	public function get_admin_instance() {
		return $this->admin;
	}


	/**
	 * Get the Front End instance
	 *
	 * @since 1.7.0
	 * @return \WC_Minimum_Advertised_Price_Frontend
	 */
	public function get_frontend_instance() {
		return $this->frontend;
	}


	/**
	 * Returns the plugin name, localized
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_plugin_name()
	 * @return string the plugin name
	 */
	public function get_plugin_name() {
		return __( 'WooCommerce Minimum Advertised Price', 'woocommerce-minimum-advertised-price' );
	}


	/**
	 * Returns __FILE__
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_file
	 * @return string the full path and filename of the plugin file
	 */
	protected function get_file() {
		return __FILE__;
	}


	/**
	 * Gets the plugin configuration URL
	 *
	 * @since 1.1
	 * @see SV_WC_Plugin::get_settings_url()
	 * @param string $_ unused
	 * @return string plugin settings URL
	 */
	public function get_settings_url( $_ = null ) {
		return admin_url( 'admin.php?page=wc-settings&tab=products' );
	}


	/**
	 * Gets the plugin documentation URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_documentation_url()
	 * @return string
	 */
	public function get_documentation_url() {
		return 'https://docs.woocommerce.com/document/woocommerce-minimum-advertised-price/';
	}


	/**
	 * Gets the plugin support URL
	 *
	 * @since 1.5.0
	 * @see SV_WC_Plugin::get_support_url()
	 * @return string
	 */
	public function get_support_url() {
		return 'https://woocommerce.com/my-account/tickets/';
	}


	/**
	 * Returns true if MAP-price savings should be shown, false otherwise.
	 *
	 * @since 1.0
	 * @return bool
	 */
	public function show_savings() {

		if ( ! isset( $this->show_savings ) ) {
			$this->show_savings = 'yes' === get_option( 'wc_minimum_advertised_price_show_savings' );
		}

		return $this->show_savings;
	}


	/** Lifecycle methods ******************************************************/


	/**
	 * Run every time.  Used since the activation hook is not executed when updating a plugin
	 *
	 * @since 1.0
	 */
	protected function install() {

		require_once( $this->get_plugin_path() . '/includes/admin/class-wc-minimum-advertised-price-admin.php' );

		// install default settings, terms, etc
		foreach ( WC_Minimum_Advertised_Price_Admin::get_global_settings() as $setting ) {

			if ( isset( $setting['default'] ) ) {
				add_option( $setting['id'], $setting['default'] );
			}
		}
	}


}


/**
 * Returns the One True Instance of Minimum Advertised Price
 *
 * @since 1.2.0
 * @return WC_Minimum_Advertised_Price
 */
function wc_minimum_advertised_price() {
	return WC_Minimum_Advertised_Price::instance();
}


// fire it up!
wc_minimum_advertised_price();

} // init_woocommerce_minimum_advertised_price()
