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
 * Product "See Price" Dialog content.
 *
 * @type \WC_Product $product The product.
 * @type string $minimum_advertised_price The minimum advertised price.
 * @type string $savings The savings string to display, if any.
 *
 * @version 1.8.0
 * @since 1.0
 */

?>
<div style="display: none;" class="wc-map-dialog" id="wc-map-see-price-<?php echo esc_attr( $product->get_id() ); ?>" title="<?php echo esc_attr( $product->get_title() ); ?>">

	<p class="price">
		<?php
		/* translators: Placeholders: %s - Minimum advertised price */
		printf( __( 'Advertised Price: %s', 'woocommerce-minimum-advertised-price' ),  '<del>' . $minimum_advertised_price . '</del>' ); ?>
	</p>
	<p class="price">
		<?php
		/* translators: Placeholders: %s - Actual price */
		printf( __( 'Price: %s', 'woocommerce-minimum-advertised-price' ), ': ' . wc_price( $product->get_price() ) ); ?>
	</p>

	<?php if ( ! empty( $savings ) ) : ?>

		<p class="price"><?php echo $savings; ?></p>

	<?php endif; ?>

	<br/>

	<?php if ( is_product() ) : ?>

		<button
			class="single_add_to_cart_button button alt">
			<?php esc_html_e( 'Add to cart', 'woocommerce-minimum-advertised-price' ); ?>
		</button>

	<?php else : ?>

		<a
			href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
			rel='nofollow'
			data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
			data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
			class="add_to_cart_button button product_type_simple">
			<?php esc_html_e( 'Add to cart', 'woocommerce-minimum-advertised-price' ); ?>
		</a>

	<?php endif; ?>

</div>
