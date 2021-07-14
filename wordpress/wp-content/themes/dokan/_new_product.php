<?php



// ADD TAG E PERSONALIZA ITEM ( NEW - UPDATE )
add_action('dokan_new_process_product_meta', 'add_new_item_custom2', 25, 1);
add_action('dokan_process_product_meta', 'add_new_item_custom2', 25, 1);
function add_new_item_custom2( $product_id ) {

    //echo '<script>console.log("aaa")</script>';

    //dump($product_id);
    //exit;
    $current_user = wp_get_current_user();
    $store_id   = get_current_user_id();
    $store_info = dokan_get_store_info( $store_id );
    $store_slug = $current_user->user_nicename;
    $store_name = $store_info['store_name'];
    $store_phone = $store_info['phone'];

    $tag = get_term_by( 'slug', $store_slug, 'product_tag');
    $tag = $tag->term_id;

    //SET THE TAGS **NOT WORKING**
    wp_set_object_terms($product_id, array($tag), 'product_tag');

    global $wpdb;
    $querystr = "
        SELECT $wpdb->posts.ID
        FROM $wpdb->posts
        WHERE $wpdb->posts.post_name = '$store_slug'
        # AND $wpdb->posts.post_author = '$customer_id'
     ";
    $produto_lavanderia = $wpdb->get_var($querystr);
    if(empty($produto_lavanderia)) dump('Ocorreu um erro, fale com o administrador!',true);

    $_weight = floatval( $_POST['_weight'] );
    update_post_meta( $product_id, '_weight', $_weight );
    update_post_meta( $product_id, '_length', 1 );
    update_post_meta( $product_id, '_width', 1 );
    update_post_meta( $product_id, '_height', 1 );

    $prazo = (int) $_POST['prazo'];
    if($prazo==0) $prazo = 1;
    update_post_meta( $product_id, 'prazo', $prazo );

    update_post_meta( $product_id, '_dependency_type', 2);
    update_post_meta( $product_id, '_tied_products', array((int)$produto_lavanderia) );

    //update_post_meta( $product_id, '_visibility', 'hidden');
    update_post_meta( $product_id, 'layout', 'fullwidth');
    update_post_meta( $product_id, 'sticky_header', 'yes');
    update_post_meta( $product_id, 'header_view', 'fixed');
    //update_post_meta( $product_id, 'sidebar', 'home-sidebar');
    update_post_meta( $product_id, 'sticky_sidebar', 'no');
    update_post_meta( $product_id, 'footer_view', 'fixed');

    if( $_POST['_manage_stock'] == 'no' ){
        update_post_meta( $product_id, '_stock', 1);
        update_post_meta( $product_id, '_stock_status', 'instock');
    }

    global $product;
    $product = wc_get_product( $product_id );
    //dump($product->status,true);
    /*
    if($product->status == 'publish'){
      //$_tied_products_lavanderia_array = array();
      $_tied_products_lavanderia = get_post_meta( $produto_lavanderia, '_tied_products', true ) ;
      //dump($_tied_products_lavanderia, true);
      if( count($_tied_products_lavanderia) > 0 ){
          array_push($_tied_products_lavanderia, (int) $product_id);
          update_post_meta( $produto_lavanderia, '_tied_products', $_tied_products_lavanderia );
      }else{
          update_post_meta( $produto_lavanderia, '_tied_products', array( (int) $product_id ) );
      }
      //dump($_tied_products_lavanderia, true);
      update_post_meta( $produto_lavanderia, '_dependency_type', 3);

    }
    */
    $product->set_catalog_visibility('hidden');
    //$product->set_comment_status('closed');
    //$product->set_ping_status('closed');
    $product->save();
    //dump($product);
    //exit;

    /*
    global $product;
    global $post;
    //$attributes    = (array) maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
    $product = wc_get_product( $product_id );
    //$attributes = $product->get_variation_attributes();
    //$post  = get_post( $product_id );
    dump($product);
    exit;

    global $woocommerce;
    // Array of defined attribute taxonomies
    $attribute_taxonomies = wc_get_attribute_taxonomies();
    // Product attributes - taxonomies and custom, ordered, with visibility and variation attributes set
    $attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );
    dump( function_exists( 'wc_get_attribute_taxonomies' ) );

    exit;
    */
}


 ?>
