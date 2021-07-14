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

defined( 'ABSPATH' ) or exit;

/**
 * Single Product Price, including microdata for SEO if needed.
 * Based on WC single-product/price.php version 1.6.4
 *
 * @type \WC_Product $product The product.
 *
 * @version 1.8.0
 * @since 1.4.0
 */

global $product;

if ( 'none' === get_option( 'wc_minimum_advertised_price_structured_data_display', 'none' ) ): ?>

	<div>
		<p class="price"><?php echo $product->get_price_html(); ?></p>
	</div>

<?php else: ?>

	<div itemprop="offers" itemscope itemtype="https://schema.org/Offer">

		<p class="price"><?php echo $product->get_price_html(); ?></p>

		<meta itemprop="price" content="<?php echo ( $product->is_type( 'variable' ) ? SV_WC_Product_Compatibility::get_meta( $product, '_min_minimum_advertised_price', true ) : WC_Minimum_Advertised_Price_Frontend::get_product_map( $product ) ); ?>" />
		<meta itemprop="priceCurrency" content="<?php echo get_woocommerce_currency(); ?>" />
		<link itemprop="availability" href="http://schema.org/<?php echo $product->is_in_stock() ? 'InStock' : 'OutOfStock'; ?>" />

	</div>

<?php endif;
