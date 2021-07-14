<?php
/**
 * WooCommerce Minimum Advertised Price
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Minimum Advertised Price to newer
 * versions in the future. If you wish to customize WooCommerce Minimum Advertised Price for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-minimum-advertised-price/ for more information.
 *
 * @package     WC-Minimum-Advertised-Price/Templates
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

/**
 * Template Function Overrides
 */

defined( 'ABSPATH' ) or exit;


if ( ! function_exists( 'woocommerce_minimum_advertised_price_see_price_product_dialog' ) ) {

	/**
	 * Template function to render the "See Price" dialog
	 *
	 * @since 1.0
	 * @param \WC_Product $product the product object
	 */
	function woocommerce_minimum_advertised_price_see_price_product_dialog( $product ) {

		// Get product price and MAP.
		$price   = $product->get_price();
		$map     = WC_Minimum_Advertised_Price_Frontend::get_product_map( $product );
		// Get the savings string if configured to display.
		$savings = '';

		if ( wc_minimum_advertised_price()->show_savings() ) {

			/* translators: Placeholders: %s - amount saved */
			$savings = sprintf( __( 'You Save: %s', 'woocommerce-minimum-advertised-price' ), wc_price( $map - $price ) );

			if ( $map ) {
				$savings .= sprintf( ' (%s%%)', round( ( $map - $price ) / $map * 100, 2 ) );
			}
		}

		wc_get_template(
			'see-price-product-dialog.php',
			array(
				'product'                  => $product,
				'minimum_advertised_price' => wc_price( $map ),
				'savings'                  => $savings,
			),
			'',
			wc_minimum_advertised_price()->get_plugin_path() . '/templates/'
		);
	}

}
