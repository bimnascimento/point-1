<?php
/**
 * Extension integrations.
 *
 * @class    WC_MNM_Compatibility
 * @version  1.2.0
 * @since    1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

class WC_Mix_and_Match_Compatibility {

	function __construct() {

		if ( is_admin() ) {
			// Check plugin min versions.
			add_action( 'admin_init', array( $this, 'add_compatibility_notices' ) );
		}

		// Deactivate functionality added by the min/max quantities mini-extension.
		if ( class_exists( 'WC_MNM_Min_Max_Quantities' ) ) {
			remove_action( 'woocommerce_mnm_loaded', 'WC_MNM_Min_Max_Quantities' );
		}

		// Initialize.
		add_action( 'plugins_loaded', array( $this, 'init' ), 100 );
	}

	/**
	 * Init compatibility classes.
	 *
	 * @return void
	 */
	public function init() {

		// Multiple Shipping Addresses support.
		if ( class_exists( 'WC_Ship_Multiple' ) ) {
			require_once( 'compatibility/class-wc-ship-multiple-compatibility.php' );
		}

		// Points and Rewards support.
		if ( class_exists( 'WC_Points_Rewards_Product' ) ) {
			require_once( 'compatibility/class-wc-pnr-compatibility.php' );
		}

		// Pre-orders support.
		if ( class_exists( 'WC_Pre_Orders' ) ) {
			require_once( 'compatibility/class-wc-po-compatibility.php' );
		}

		// Cost of Goods support.
		if ( class_exists( 'WC_COG' ) ) {
			require_once( 'compatibility/class-wc-cog-compatibility.php' );
		}

		// One Page Checkout support.
		if ( function_exists( 'is_wcopc_checkout' ) ) {
			require_once( 'compatibility/class-wc-opc-compatibility.php' );
		}

		// Wishlists support.
		if ( class_exists( 'WC_Wishlists_Plugin' ) ) {
			require_once( 'compatibility/class-wc-wl-compatibility.php' );
		}

		// Shipstation integration.
		require_once( 'compatibility/class-wc-shipstation-compatibility.php' );
	}

	/**
	 * Checks versions of compatible/integrated/deprecated extensions.
	 */
	public function add_compatibility_notices() {

		// Min/max mini-extension check.
		if ( class_exists( 'WC_MNM_Min_Max_Quantities' ) ) {
			$notice = sprintf( __( 'The <strong>WooCommerce Mix and Match: Min/Max Quantities</strong> mini-extension is now part of <strong>WooCommerce Mix and Match</strong>. Please deactivate and remove the <strong>WooCommerce Mix and Match: Min/Max Quantities</strong> plugin.', 'woocommerce-mix-and-match-products' ) );
			WC_MNM_Admin_Notices::add_notice( $notice, 'warning' );
		}
	}

	/**
	 * Tells if a product is a Name Your Price product, provided that the extension is installed.
	 *
	 * @param  mixed  $product
	 * @return boolean
	 */
	public function is_nyp( $product ) {

		if ( ! class_exists( 'WC_Name_Your_Price_Helpers' ) ) {
			return false;
		}

		if ( WC_Name_Your_Price_Helpers::is_nyp( $product ) ) {
			return true;
		}

		return false;
	}
}
