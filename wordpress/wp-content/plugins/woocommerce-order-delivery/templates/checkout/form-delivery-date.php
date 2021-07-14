<?php
/**
 * Checkout delivery date form
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<div id="wc-od">
	<h3><?php echo esc_html( $title ); ?></h3>

	<?php do_action( 'wc_od_checkout_before_delivery_details', $args ); ?>

	<?php if ( 'calendar' === $delivery_option ) : ?>

		<p><?php _e( 'We will try our best to deliver your order on the specified date.', 'woocommerce-order-delivery' ); ?></p>
		<?php echo $delivery_date_field; ?>

	<?php elseif ( isset( $shipping_date ) ) : ?>

		<p><?php printf( __( 'We estimate that your order will be shipped on %s.', 'woocommerce-order-delivery' ), "<strong>{$shipping_date}</strong>" ); ?></p>
		<p><?php printf(
			_n(
				'The delivery will take approximately %s working day from the shipping date.',
				'The delivery will take approximately %s working days from the shipping date.',
				( $delivery_range['min'] === $delivery_range['max'] && 1 === $delivery_range['min'] ? 1 : $delivery_range['max'] ),
				'woocommerce-order-delivery'
			),
			'<strong>' . wc_od_format_delivery_range( $delivery_range ) . '</strong>' );
		?></p>

	<?php endif; ?>

	<?php do_action( 'wc_od_checkout_after_delivery_details', $args ); ?>
</div>
