<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//CUSTOM NEW USER
add_action( 'user_register', '_new_user', 0, 1 );
add_action( 'woocommerce_created_customer', '_new_user', 0, 1 );
function _new_user( $customer_id ) {

      //dump('_new_user',true);

      if ( isset( $_POST['first_name'] ) ) {
            update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
            update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['first_name'] ) );
            update_user_meta( $customer_id, 'shipping_first_name', sanitize_text_field( $_POST['first_name'] ) );
      }
      if ( isset( $_POST['last_name'] ) ) {
            update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
            update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['last_name'] ) );
            update_user_meta( $customer_id, 'shipping_last_name', sanitize_text_field( $_POST['last_name'] ) );
      }
      if ( isset( $_POST['billing_phone'] ) ) {
            update_user_meta( $customer_id, 'phone', sanitize_text_field( $_POST['phone'] ) );
            update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['phone'] ) );
      }
      if ( isset( $_POST['billing_email'] ) ) {
            update_user_meta( $customer_id, 'billing_email', sanitize_text_field( $_POST['billing_email'] ) );
      }
      if ( isset( $_POST['postcode'] ) ) {
            update_user_meta( $customer_id, 'postcode', sanitize_text_field( $_POST['postcode'] ) );
            update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $_POST['postcode'] ) );
            update_user_meta( $customer_id, 'shipping_postcode', sanitize_text_field( $_POST['postcode'] ) );
      }
      if ( isset( $_POST['account_password'] ) ) {
            update_user_meta( $customer_id, 'account_password', sanitize_text_field( $_POST['account_password'] ) );
      }

      //dump($prev_dokan_settings);
      //dump($_POST);
      //exit;

}

?>
