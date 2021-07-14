<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ){
	exit;
}

/**
 * Mix and Match order helper functions
 *
 * @class 	WC_Mix_and_Match_Helpers
 * @version 1.2.0
 * @since   1.0.0
 */

class WC_Mix_and_Match_Helpers {

	/**
	 * Calculates bundled product prices incl. or excl. tax depending on the 'woocommerce_tax_display_shop' setting.
	 * for WC < 2.7.
	 *
	 * @param  WC_Product   $product    the product
	 * @param  double       $price      the product price
	 * @return double                   modified product price incl. or excl. tax
	 */
	public static function get_product_display_price( $product, $price ) {
		return WC_MNM_Core_Compatibility::wc_get_price_to_display( $product, array( 'price' => $price ) );
	}

	/**
	 * Get formatted variation data with WC < 2.4 back compat and proper formatting of text-based attribute names.
	 *
	 * @param  WC_Product_Variation  $variation   the variation
	 * @return string                             formatted attributes
	 */
	public static function get_formatted_variation_attributes( $variation, $flat = false ) {

		_deprecated_function( __METHOD__ . '()', '1.2.0', 'wc_get_formatted_variation()' );

		return WC_MNM_Core_Compatibility::wc_get_formatted_variation( $variation, $flat );
	}

	/**
	 * Product types supported by the plugin.
	 * You can dynamically attach these product types to Mix and Match Product.
	 *
	 * @public
	 * @static
	 * @since  1.1.6
	 * @return array
	 */
	public static function get_supported_product_types() {
		return apply_filters( 'woocommerce_mnm_supported_products', array( 'simple', 'variation' ) );
	}

	/*-----------------------------------------------------------------------------------*/
	/* Deprecated Functions */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Helper method to get the version of the currently installed WooCommerce
	 *
	 * @since 1.0.5
	 * @return string woocommerce version number or null
	 */
	private static function get_wc_version() {
		_deprecated_function( __METHOD__, '1.2.0', 'WC_MNM_Core_Compatibility::get_wc_version()' );
		return WC_MNM_Core_Compatibility::get_wc_version();
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.4 or greater
	 *
	 * @since 1.0.5
	 * @return boolean true if the installed version of WooCommerce is 2.2 or greater
	 */
	public static function is_wc_version_gte_2_4() {
		_deprecated_function( __METHOD__, '1.2.0', 'WC_MNM_Core_Compatibility::is_wc_version_gte( "2.4.0" )' );
		return WC_MNM_Core_Compatibility::is_wc_version_gte( '2.4.0' );
	}

} //end class
