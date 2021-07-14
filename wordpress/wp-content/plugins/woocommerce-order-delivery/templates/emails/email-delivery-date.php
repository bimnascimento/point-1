<?php
/**
 * Email delivery details
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<h2><?php echo esc_html( $title ); ?></h2>

<?php do_action( 'wc_od_email_before_delivery_details', $args ); ?>

<?php if ( isset( $delivery_date ) ) : ?>

	<p><?php printf(
		__( 'We will try our best to deliver your order on %s.', 'woocommerce-order-delivery' ),
		"<strong>{$delivery_date}</strong>" );
	?></p>

<?php endif; ?>

<?php do_action( 'wc_od_email_after_delivery_details', $args ); ?>
