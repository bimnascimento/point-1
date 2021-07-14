<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//my_custom_shipping_calculator_field
//add_action( 'woocommerce_calculated_shipping', 'woocommerce_calculated_shipping_custom' );
function woocommerce_calculated_shipping_custom() {

    wc_clear_notices();
    $postcode = isset( $_REQUEST['calc_shipping_postcode'] ) ? $_REQUEST['calc_shipping_postcode'] : '';
    if ( strlen($postcode) == 9 && is_user_logged_in() ) {
        update_user_meta( get_current_user_id(), 'postcode', sanitize_text_field( $postcode ) );
        update_user_meta( get_current_user_id(), 'billing_postcode', sanitize_text_field( $postcode ) );
        update_user_meta( get_current_user_id(), 'shipping_postcode', sanitize_text_field( $postcode ) );
  	}
    wc_clear_notices();

}
?>
