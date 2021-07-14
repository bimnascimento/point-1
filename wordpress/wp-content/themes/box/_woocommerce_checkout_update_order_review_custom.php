<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_action( 'woocommerce_checkout_update_order_review', 'woocommerce_checkout_update_order_review_custom', 10, 2 );
function woocommerce_checkout_update_order_review_custom( $array, $int ) {

      //dump('woocommerce_checkout_update_order_review_custom');
      //exit;

     //$dados = array();
     $array = explode("&",$array);
     foreach($array as $val){
        $val = explode("=",$val);
        $valor[$val[0]] = $val[1];
     }
     //array_push($dados,$valor);
     //echo '<pre>';print_r($_POST);echo '</pre>';

     $customer_id = get_current_user_id();

     $billing_cpf = $valor['billing_cpf'];
     $billing_cpf = str_replace('_','',$billing_cpf);
     if( !empty($billing_cpf) ){
          update_user_meta( $customer_id, 'billing_cpf', sanitize_text_field( $billing_cpf ) );
     }

     $billing_rg = $valor['billing_rg'];
     $billing_rg = str_replace('_','',$billing_rg);
     if( !empty($billing_rg) ){
          update_user_meta( $customer_id, 'billing_rg', sanitize_text_field( $billing_rg ) );
     }

     $billing_phone = $valor['billing_phone'];
     $billing_phone = str_replace('_','',$billing_phone);
     if( !empty($billing_phone) ){
          update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $billing_phone ) );
     }

     $billing_cellphone = $valor['billing_cellphone'];
     $billing_cellphone = str_replace('_','',$billing_cellphone);
     if( !empty($billing_cellphone) ){
          update_user_meta( $customer_id, 'billing_cellphone', sanitize_text_field( $billing_cellphone ) );
     }

     $billing_birthdate = $valor['billing_birthdate'];
     $billing_birthdate = str_replace('_','',$billing_birthdate);
     if( !empty($billing_birthdate) ){
          update_user_meta( $customer_id, 'billing_birthdate', sanitize_text_field( $billing_birthdate ) );
     }

     $billing_number = $valor['billing_number'];
     $billing_number = str_replace('_','',$billing_number);
     if( !empty($billing_number) ){
          update_user_meta( $customer_id, 'billing_number', sanitize_text_field( $billing_number ) );
     }

     $billing_address_2 = $valor['billing_address_2'];
     $billing_address_2 = str_replace('_','',$billing_address_2);
     if( !empty($billing_address_2) ){
          update_user_meta( $customer_id, 'billing_address_2', sanitize_text_field( $billing_address_2 ) );
     }

     $billing_persontype = $valor['billing_persontype'];
     $billing_persontype = str_replace('_','',$billing_persontype);
     if( !empty($billing_persontype) ){
          update_user_meta( $customer_id, 'billing_persontype', sanitize_text_field( $billing_persontype ) );
     }

     $billing_email = $valor['billing_email'];
     $billing_email = str_replace('_','',$billing_email);
     if( !empty($billing_email) ){
          update_user_meta( $customer_id, 'billing_email', sanitize_text_field( $billing_email ) );
     }

     $billing_sex = $valor['billing_sex'];
     $billing_sex = str_replace('_','',$billing_sex);
     if( !empty($billing_sex) ){
          update_user_meta( $customer_id, 'billing_sex', sanitize_text_field( $billing_sex ) );
     }

     $shipping_postcode = $valor['shipping_postcode'];
     $shipping_postcode = str_replace('_','',$shipping_postcode);
     if( !empty($shipping_postcode) ){
          update_user_meta( $customer_id, 'shipping_postcode', sanitize_text_field( $shipping_postcode ) );
     }

     $shipping_address_1 = $valor['shipping_address_1'];
     $shipping_address_1 = str_replace('_','',$shipping_address_1);
     if( !empty($shipping_address_1) ){
          update_user_meta( $customer_id, 'shipping_address_1', sanitize_text_field( $shipping_address_1 ) );
     }

     $shipping_number = $valor['shipping_number'];
     $shipping_number = str_replace('_','',$shipping_number);
     if( !empty($shipping_number) ){
          update_user_meta( $customer_id, 'shipping_number', sanitize_text_field( $shipping_number ) );
     }

     $shipping_address_2 = $valor['shipping_address_2'];
     $shipping_address_2 = str_replace('_','',$shipping_address_2);
     if( !empty($shipping_address_2) ){
          update_user_meta( $customer_id, 'shipping_address_2', sanitize_text_field( $shipping_address_2 ) );
     }

     $shipping_neighborhood = $valor['shipping_neighborhood'];
     $shipping_neighborhood = str_replace('_','',$shipping_neighborhood);
     if( !empty($shipping_neighborhood) ){
          update_user_meta( $customer_id, 'shipping_neighborhood', sanitize_text_field( $shipping_neighborhood ) );
     }

     $shipping_city = $valor['shipping_city'];
     $shipping_city = str_replace('_','',$shipping_city);
     if( !empty($shipping_city) ){
          update_user_meta( $customer_id, 'shipping_city', sanitize_text_field( $shipping_city ) );
     }

     $order_comments = $valor['order_comments'];
     $order_comments = str_replace('_','',$order_comments);
     if( !empty($order_comments) ){
          update_user_meta( $customer_id, 'order_comments', sanitize_text_field( $order_comments ) );
     }

     if( !empty( $valor['pagseguro_payment_method'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_payment_method', sanitize_text_field( $valor['pagseguro_payment_method'] ) );
     if( !empty( $valor['pagseguro_card_holder_name'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_name', sanitize_text_field( $valor['pagseguro_card_holder_name'] ) );
     if( !empty( $valor['pagseguro_card_holder_number'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_number', sanitize_text_field( $valor['pagseguro_card_holder_number'] ) );
     if( !empty( $valor['pagseguro_card_holder_expiry'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_expiry', sanitize_text_field( $valor['pagseguro_card_holder_expiry'] ) );
     if( !empty( $valor['pagseguro_card_holder_cvc'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_cvc', sanitize_text_field( $valor['pagseguro_card_holder_cvc'] ) );
     if( !empty( $valor['pagseguro_card_installments'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_installments', sanitize_text_field( $valor['pagseguro_card_installments'] ) );
     if( !empty( $valor['pagseguro_card_holder_cpf'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_cpf', sanitize_text_field( $valor['pagseguro_card_holder_cpf'] ) );
     if( !empty( $valor['pagseguro_card_holder_birth_date'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_birth_date', sanitize_text_field( $valor['pagseguro_card_holder_birth_date'] ) );
     if( !empty( $valor['pagseguro_card_holder_phone'] ) )
         update_user_meta( get_current_user_id(), 'pagseguro_card_holder_phone', sanitize_text_field( $valor['pagseguro_card_holder_phone'] ) );

     return;




};
?>
