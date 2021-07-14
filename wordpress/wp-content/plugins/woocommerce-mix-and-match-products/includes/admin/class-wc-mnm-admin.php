<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix and Match Admin Class
 *
 * Loads admin tabs and adds related hooks / filters
 * Adds and save product meta
 *
 * @class WC_Mix_and_Match_Admin
 * @since 	1.0.0
 * @version 1.2.0
 */
class WC_Mix_and_Match_Admin {

	/**
	 * Bootstraps the class and hooks required.
	 *
	 * @return void
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'includes' ) );

		// Admin jquery
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );
	}

	/**
	 * Admin init.
	 */
	public static function includes() {
		if ( WC_MNM_Core_Compatibility::is_wc_version_gte( '3.0.0' ) ) {
			require_once( 'meta-boxes/class-wc-mnm-meta-box-product-data.php' );
		} else {
			require_once( 'meta-boxes/legacy/class-wc-mnm-meta-box-product-data.php' );
		}
	}

	/**
	 * Load the product metabox script.
	 */
	public static function admin_scripts() {

		// Get admin screen id.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		// WooCommerce product admin page.
		if ( 'product' === $screen_id ) {

			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( 'wc_mnm_writepanel', WC_Mix_and_Match()->plugin_url() . '/assets/js/mnm-write-panel' . $suffix . '.js', array( 'jquery', 'wc-enhanced-select' ), WC_Mix_and_Match()->version );

			$params = array(
				'is_wc_version_gte_2_7' => WC_MNM_Core_Compatibility::is_wc_version_gte( '3.0.0' ) ? 'yes' : 'no'
			);

			wp_localize_script( 'wc_mnm_writepanel', 'wc_mnm_admin_params', $params );

			add_action( 'admin_head', array( __CLASS__, 'admin_header' ) );
		}

		// General admin styles.
		wp_register_style( 'wc_mnm_admin', WC_Mix_and_Match()->plugin_url() . '/assets/css/mnm-admin.css', array(), WC_Mix_and_Match()->version );
		wp_enqueue_style( 'wc_mnm_admin' );

		// WooCommerce order admin page.
		if ( 'shop_order' == $screen_id ) {
			wp_enqueue_style( 'wc_mnm_order_style', WC_Mix_and_Match()->plugin_url() . '/assets/css/mnm-edit-order.css', array(), WC_Mix_and_Match()->version );
		}
	}

	/**
	 * Add an icon to MNM product data tab
	 */
	public static function admin_header() { ?>
		<style>
			#woocommerce-product-data ul.wc-tabs li.mnm_product_options a:before { content: "\f538"; font-family: "Dashicons"; }
	    </style>
	    <?php
	}
}
// launch the admin class
WC_Mix_and_Match_Admin::init();
