<?php
/**
 * WC_MNM_Data class
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MnM Data class.
 *
 * MnM Data filters and includes.
 *
 * @class    WC_MNM_Data
 * @version  1.2.0
 */
class WC_MNM_Data {

	public static function init() {

		if ( WC_MNM_Core_Compatibility::is_wc_version_gte( '3.0.0' ) ) {

			// Product Bundle CPT data store.
			require_once( 'class-wc-product-mix-and-match-data-store-cpt.php' );

			// Register the Product Bundle Custom Post Type data store.
			add_filter( 'woocommerce_data_stores', array( __CLASS__, 'register_mnm_type_data_store' ), 10 );
		}
	}

	/**
	 * Registers the Product Bundle Custom Post Type data store.
	 *
	 * @param  array  $stores
	 * @return array
	 */
	public static function register_mnm_type_data_store( $stores ) {

		$stores[ 'product-mix-and-match' ] = 'WC_Product_MNM_Data_Store_CPT';

		return $stores;
	}
}

WC_MNM_Data::init();
