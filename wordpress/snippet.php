
<?php


//DUMP
function dump( $arg = '', $break = false, $exit = false ) {
    echo '<pre>';
    print_r($arg);
    echo '</pre>';
    if($break) break;
    if($exit) exit;
}
//add_filter( 'dump', 'return_dump' );


//LINK LOGOUT
add_filter( 'logout_url', 't5_custom_logout_url', 10, 2 );
add_action( 'wp_loaded', 't5_custom_logout_action' );
function t5_custom_logout_url( $logout_url, $redirect ){
    $redirect = 'LAVAETRAZ';
    $url = add_query_arg( 'logout', 1, home_url( '/' ) );
    if ( ! empty ( $redirect ) )
        $url = add_query_arg( 'redirect', $redirect, $url );
    return $url;
}
function t5_custom_logout_action(){
    if ( ! isset ( $_GET['logout'] ) )
        return;
    wp_logout();
    $loc = isset ( $_GET['redirect'] ) ? $_GET['redirect'] : home_url( '/' );
    wp_redirect( $loc );
    exit;
}

//PEDIDO POR LAVANDERIA
add_filter( 'woocommerce_add_cart_item_data', 'woo_custom_add_to_cart', 10, 2);
function woo_custom_add_to_cart( $cart_item_data,$id) {
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

//LISTA ITENS POR CLIENTE
function shortcode_lista_itens($atts, $content, $tag) {
      $return = '';
      $cliente = $atts['cliente'];
      if(strrpos($cliente,',') !== FALSE){
            $clientes = array();
            $clientes = explode(",",$cliente);
            $clientes_array = array();
            foreach ( $clientes as $cliente_array ) {
                $cliente = get_term_by('name', $cliente_array, 'product_tag');
                $cliente = $cliente->term_id;
                array_push($clientes_array,$cliente);
            }
            $clientes = array_map( 'intval', $clientes_array );
            $clientes = array_unique( $clientes );
            $cliente = $clientes;
      }else{
            $cliente = get_term_by('name', $cliente, 'product_tag');
            $cliente = $cliente->term_id;
      }
          ob_start();
            $lista = '[vc_row el_class="produto-table"]
                        [vc_column]
                            [vc_tta_accordion style="flat" color="blue" c_position="right" active_section="0" no_fill="true"]';
            $argsCat = array(
                'number'       => 0,
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => 0,
                'parent'       => '21',
            );
            $product_categories = get_terms( 'product_cat', $argsCat );
            $count = count($product_categories);
            if ( $count > 0 ){
                foreach ( $product_categories as $cat ) {
                    $argsItem = array(
                        'posts_per_page' => -1,
                        'post_type' => 'product',
                        'orderby' => 'title',
                        'order' => 'ASC',
                    );
                    $argsItem['tax_query'] = array(
                            'relation' => 'AND',
                            array(
                                'taxonomy' => 'product_cat',
                                'field' => 'slug',
                                'terms' => $cat->slug,
                            ),
                            array(
                                'taxonomy' => 'product_tag',
                                //'field' => 'slug',
                                'field' => 'id',
                                'terms' => ($cliente),
                            ),
                    );
                    $products = new WP_Query( $argsItem );
                    $total = $products->post_count;
                    if($total>0){
                        $lista .= '[vc_tta_section title="'.$cat->name.'" tab_id="'.$cat->slug.'" el_class="title-'.$cat->slug.'"]
                                    [vc_column_text]';
                        $produtos_array = array();
                        while ( $products->have_posts() ){
                            $products->the_post();
                            $post_id = get_the_ID();
                            array_push($produtos_array,$post_id);
                        }
                        $produtos_array = implode(',',$produtos_array);
                        $lista .= '[wcplpro categories_inc="'.$cat->term_id.'" wcplid="lista-'.$cat->slug.'" posts_inc="'.($produtos_array).'" thumb=0 thumb_size=30 stock=1 qty=1 ajax=1 ]';
                        $lista .= '[/vc_column_text]
                                [/vc_tta_section]';
                    }
                }
            }
            $lista .= '[/vc_tta_accordion]
                        [/vc_column]
                    [/vc_row]';
            echo do_shortcode( $lista );
            $return = ob_get_contents();
          ob_get_clean();
      return $return;
}
add_shortcode('shortcode_lista_itens', 'shortcode_lista_itens');

//CREATE TAG NOME DA LOJA
function my_new_customer_data($data){
  //dump($data);
  $role = $data['role'];
  if ( $role == 'seller' ) {
      $loja = $data['user_nicename'];
      wp_insert_term( $loja, 'product_tag', array( 'slug' => $loja ) );
  }
  return $data;
}
add_filter( 'woocommerce_new_customer_data', 'my_new_customer_data');


//REMOVE LINK ADM LOJA
function prefix_dokan_add_seller_nav_1( $urls ) {
    unset( $urls['reviews'] );
    return $urls;
}
add_filter( 'dokan_get_dashboard_nav', 'prefix_dokan_add_seller_nav_1' );

//PADRAO PARA NOVO ITEM DE CLIENTE
add_action('dokan_new_process_product_meta', 'x_add_fields_save_2', 10, 1);
add_action('dokan_process_product_meta', 'x_add_fields_save_2', 10, 1);
function x_add_fields_save_2( $product_id ) {

    $current_user = wp_get_current_user();
    $loja = $current_user->user_nicename;
    $tag = get_term_by('name', $loja, 'product_tag');

    //SET THE TAGS **NOT WORKING**
    wp_set_object_terms($product_id, array($tag->term_id), 'product_tag');

    update_post_meta( $product_id, 'footer_view', 'simple');
    /*
    update_post_meta( $post_id, 'layout', 'fullwidth');
    update_post_meta( $post_id, '_dependency_type', '2');
    update_post_meta( $post_id, '_tied_products', array(684) );
    update_post_meta( $post_id, 'sticky_header', 'yes');
    update_post_meta( $post_id, 'header_view', 'fixed');
    update_post_meta( $post_id, 'dhvc_woo_page_product', 1395);
    update_post_meta( $post_id, 'sidebar', 'woo-product-sidebar');
    update_post_meta( $post_id, 'sticky_sidebar', 'no');
    update_post_meta( $post_id, 'footer_view', 'simple');
    */

}

//FILTRO Lavanderias
add_action('pre_get_posts','shop_filter_cat');
function shop_filter_cat($query) {
    if (!is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query()) {
      $query->set('tax_query', array(
                    array ('taxonomy' => 'product_cat',
                                         'field' => 'slug',
                                         'terms' => 'lavanderias'
                                 )
                     )
       );
    }
}




?>
