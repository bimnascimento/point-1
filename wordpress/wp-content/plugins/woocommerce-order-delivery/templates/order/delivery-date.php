<?php
/**
 * Order delivery details
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
	<header>
		<h2><?php echo esc_html( $title ); ?></h2>
	</header>

	<?php do_action( 'wc_od_order_before_delivery_details', $args ); ?>

	<?php if ( isset( $delivery_date ) ) : ?>

		<p><?php printf(
			__( 'We will try our best to deliver your order on %s.', 'woocommerce-order-delivery' ),
			"<strong>{$delivery_date}</strong>" );
		?></p>

	<?php elseif ( isset( $shipping_date ) ): ?>

		<p><?php printf( __( 'We estimate that your order will be shipped on %s.', 'woocommerce-order-delivery' ), '<strong>' . $shipping_date . '</strong>' ); ?> </p>
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

	<?php do_action( 'wc_od_order_after_delivery_details', $args ); ?>
</div>
