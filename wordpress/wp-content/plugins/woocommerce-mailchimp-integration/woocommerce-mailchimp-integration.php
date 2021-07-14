<?php
/**
 * Plugin Name: WooCommerce MailChimp Integration
 * Version: 1.0.2
 * Plugin URI: https://woocommerce.com/products/woocommerce-mailchimp-integration/
 * Description: Send order and customer information to MailChimp, and subscribe customers to specific mailing lists.
 * Author: WooCommerce
 * Author URI: https://woocommerce.com/
 * Requires at least: 3.9
 * Tested up to: 4.0
 *
 * @package WooCommerce
 * @author WooThemes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
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
woothemes_queue_update( plugin_basename( __FILE__ ), '941fef7a9f3c991d036c162e9d96b5cd', '1552007' );


if ( is_woocommerce_active() ) :

/**
 * WooCommerce Mailchimp Integration main class.
 */
class WC_Mailchimp {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '1.0.2';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Display an admin notice, if setup is required.
		add_action( 'admin_notices', array( $this, 'maybe_display_admin_notices' ) );

		// Load plugin text domain
		add_action( 'after_setup_theme', array( $this, 'load_plugin_textdomain' ) );

		require_once( 'includes/class-wc-mailchimp-integration.php' );

		// Register the integration.
		add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
		add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'add_action_links' ) );
	}

	/**
	 * Display an admin notice, if not on the integration screen and if the account isn't yet connected.
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function maybe_display_admin_notices () {
		if ( isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset( $_GET['section'] ) && 'mailchimp' == $_GET['section'] ) return; // Don't show these notices on our admin screen.

		// Find a different method of retrieving this value.
		$api_key = WC()->integrations->integrations['mailchimp']->get_option( 'wc_mailchimp_api_key' );

		if ( '' == $api_key ) {
			$url = $this->get_settings_url();
			echo '<div class="updated fade"><p>' . sprintf( __( '%sWooCommerce MailChimp is almost ready.%s To get started, %sconnect your MailChimp account%s.', 'woocommerce-mailchimp-integration' ), '<strong>', '</strong>', '<a href="' . esc_url( $url ) . '">', '</a>' ) . '</p></div>' . "\n";
		}
	} // End maybe_display_admin_notices()

	/**
	 * Generate a URL to our specific settings screen.
	 * @access public
	 * @since  1.0.0
	 * @return string Generated URL.
	 */
	public function get_settings_url () {
		$url = admin_url( 'admin.php' );
		$url = add_query_arg( 'page', 'wc-settings', $url );
		$url = add_query_arg( 'tab', 'integration', $url );
		$url = add_query_arg( 'section', 'mailchimp', $url );

		return $url;
	}

	/**
	 * Add Settings link on plugins page
	 * @param array $links
	 * @return array
	 */
	public function add_action_links ( $links ) {
	    $new_links = array( 'configure' => '<a href="'. esc_url( $this->get_settings_url() ) .'">' . __( 'Configure', 'woocommerce-mailchimp-integration' ) . '</a>' );
	   	$links = array_merge( $new_links, $links );
	    return $links;
	}

	/**
	 * Return an instance of this class.
	 * @return object A single instance of this class.
	 */
	public static function get_instance () {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @return void
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( 'woocommerce-mailchimp-integration', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Add a new integration to WooCommerce.
	 *
	 * @param  array $integrations WooCommerce integrations.
	 *
	 * @return array               MailChimp integration.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'WC_Mailchimp_Integration';

		return $integrations;
	}
}

add_action( 'plugins_loaded', array( 'WC_Mailchimp', 'get_instance' ), 0 );


endif;
