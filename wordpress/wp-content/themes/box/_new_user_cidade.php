<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//PERSONALIZA CAMPOS NEW USER - CIDADES
add_action( 'user_register', '_new_user_cidade', 0, 1 );
add_action( 'woocommerce_created_customer', '_new_user_cidade', 0, 1 );
function _new_user_cidade( $customer_id ) {

      $endereco = trim($_POST['address_1']);
      $enderecoBairro = trim($_POST['neighborhood']);
      $enderecoCidade = trim($_POST['city']);
      $enderecoEstado = trim($_POST['state']);
      
      if( isset($endereco) ){
        update_user_meta( $customer_id, 'address_1', sanitize_text_field( $endereco ) );
        update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $endereco ) );
        update_user_meta( $customer_id, 'shipping_address_1', sanitize_text_field( $endereco ) );
      }

      if( isset($enderecoBairro) ){
        update_user_meta( $customer_id, 'neighborhood', sanitize_text_field( $enderecoBairro ) );
        update_user_meta( $customer_id, 'billing_neighborhood', sanitize_text_field( $enderecoBairro ) );
        update_user_meta( $customer_id, 'shipping_neighborhood', sanitize_text_field( $enderecoBairro ) );
      }

      update_user_meta( $customer_id, 'city', sanitize_text_field( $enderecoCidade ) );
      update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $enderecoCidade ) );
      update_user_meta( $customer_id, 'shipping_city', sanitize_text_field( $enderecoCidade ) );

      update_user_meta( $customer_id, 'state', sanitize_text_field( $enderecoEstado ) );
      update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $enderecoEstado ) );
      update_user_meta( $customer_id, 'shipping_state', sanitize_text_field( $enderecoEstado ) );

      update_user_meta( $customer_id, 'country', sanitize_text_field( "BR" ) );
      update_user_meta( $customer_id, 'billing_country', sanitize_text_field( "BR" ) );
      update_user_meta( $customer_id, 'shipping_country', sanitize_text_field( "BR" ) );


}
?>
