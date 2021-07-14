<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'woocommerce_add_cart_item_data', 'woo_custom_add_to_cart', 10, 2);
function woo_custom_add_to_cart( $cart_item_data, $id) {
      global $woocommerce;
      global $product;
      $productAdd = wc_get_product( $id );
      $authorAdd = get_user_by('id', $productAdd->post->post_author );
      $authorAdd_id = $authorAdd->ID;
      foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
          $idProdutoCart = $cart_item['product_id'];
          $productCart = wc_get_product( $idProdutoCart );
          $authorCart = get_user_by('id', $productCart->post->post_author );
          $authorCart_id = $authorCart->ID;
          if( $authorCart_id != $authorAdd_id ){
              $woocommerce->cart->empty_cart();
          }
      }
    return $cart_item_data;
}

?>
