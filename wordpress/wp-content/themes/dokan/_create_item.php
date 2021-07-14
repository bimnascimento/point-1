<?php

//dump($post_id);
// FUNCAO CRIA ITENS - LAVANDERIA
function create_item( $customer_id, $tag, $post_id, $item, $valor, $peso, $prazo, $categorias, $lista_categories ){

              global $current_user, $wpdb;
              // VERIFICA SE JA EXISTE ITEM - LAVANDERIA
              $querystr = "
                          SELECT $wpdb->posts.ID
                          FROM $wpdb->posts
                          WHERE $wpdb->posts.post_title = '$item'
                          AND $wpdb->posts.post_type = 'product'
                          AND $wpdb->posts.post_author = '$customer_id'
                          ";
              $post_id_item = $wpdb->get_var($querystr);

              //dump($post_id_item);
              //exit;
              if( empty($post_id_item) ){

                    $categorias_ids = array();

                    $caracteristicas = getIdObject($lista_categories,'servcliente_informar-caracteristicas');
                    array_push($categorias_ids,$caracteristicas);

                    $cores = getIdObject($lista_categories,'servcliente_informar-cores');
                    array_push($categorias_ids,$cores);

                    $defeitos = getIdObject($lista_categories,'servcliente_informar-defeitos');
                    array_push($categorias_ids,$defeitos);

                    $marca = getIdObject($lista_categories,'servcliente_informar-marca');
                    array_push($categorias_ids,$marca);

                    foreach ($categorias as $cat) {
                       $cat = getIdObject($lista_categories,$cat);
                       array_push($categorias_ids,$cat);
                    }

                    //dump($categorias_ids);
                    //dump($lista_categories);
                    //exit;

                    //CREATE ITEM - ITEM PARA LAVANDERIA
                    $post_id_item = wp_insert_post( array(
                        'post_author' => (int)$customer_id,
                        'post_title' => $item,
                        //'post_name' => $shopurl,
                        //'post_content' => 'Este item foi criado pelo administrador, modifique o valor, prazo e categorias de acordo com sua lavanderia!',


                        'post_status' => 'draft',
                        //'post_status' => 'publish',


                        'post_type' => "product",
                        'comment_status' => "closed",
                        'ping_status' => "closed",
                    ) );

                    //$_tied_products_lavanderia = get_post_meta( $produto_lavanderia, '_tied_products', true ) ;
                    //dump($produto_lavanderia, true);
                    //update_post_meta( $produto_lavanderia, '_dependency_type', 3);
                    //update_post_meta( $produto_lavanderia, '_tied_products', array( (int) $post_id_item ) );

              //} // FIM CRIA ITEM


                    wp_set_object_terms( $post_id_item, $categorias_ids, 'product_cat' ); // TEMP
                    wp_set_object_terms( $post_id_item, 'simple', 'product_type' );
                    wp_set_object_terms( $post_id_item, array($tag), 'product_tag');

                    global $product;
                    $product = wc_get_product( $post_id_item );
                    $product->set_catalog_visibility('hidden');
                    $product->save();

                    //if( strpos($store_slug, 'lavanderia-sul-america') === false ) $valor = 0;
                    $valor_import_item = $valor;

                    //update_post_meta( $post_id, '_visibility', 'hidden' );
                    update_post_meta( $post_id_item, 'total_sales', 0 );
                    update_post_meta( $post_id_item, 'prazo', (int) $prazo );
                    update_post_meta( $post_id_item, '_stock_status', 'instock');
                    update_post_meta( $post_id_item, '_downloadable', 'no' );
                    update_post_meta( $post_id_item, '_virtual', 'no' );
                    update_post_meta( $post_id_item, '_regular_price', (float) $valor_import_item );
                    update_post_meta( $post_id_item, '_sale_price', '' );
                    update_post_meta( $post_id_item, '_purchase_note', '' );
                    update_post_meta( $post_id_item, '_featured', 'no' );
                    update_post_meta( $post_id_item, '_weight', (float) $peso);
                    update_post_meta( $post_id_item, '_length', 1 );
                    update_post_meta( $post_id_item, '_width', 1 );
                    update_post_meta( $post_id_item, '_height', 1 );
                    //update_post_meta( $post_id_item, '_sku', 'LT00-1' );
                    update_post_meta( $post_id_item, '_product_attributes', array() );
                    update_post_meta( $post_id_item, '_sale_price_dates_from', '' );
                    update_post_meta( $post_id_item, '_sale_price_dates_to', '' );
                    update_post_meta( $post_id_item, '_price', (float) $valor_import_item );
                    update_post_meta( $post_id_item, '_sold_individually', 'no' );
                    update_post_meta( $post_id_item, '_manage_stock', 'no' );
                    //update_post_meta( $post_id_item, '_manage_stock', 'yes' );
                    //update_post_meta( $post_id_item, '_backorders', 'no' );
                    //update_post_meta( $post_id_item, '_stock', 10 );
                    update_post_meta( $post_id_item, 'tm_meta_cpf', array('mode'=>'builder','override_display'=>'','override_final_total_box'=>'') );
                    update_post_meta( $post_id_item, 'layout', 'fullwidth');
                    update_post_meta( $post_id_item, '_dependency_type', 2);
                    update_post_meta( $post_id_item, '_tied_products', array( (int) $post_id ) );
                    update_post_meta( $post_id_item, 'sticky_header', 'yes');
                    update_post_meta( $post_id_item, 'header_view', 'fixed');
                    //$page = get_page_by_path('modelo-lavanderia');
                    //$page = $page->ID;
                    //$sku = get_post_meta($theid, '_sku', true );
                    //update_post_meta( $post_id_item, 'dhvc_woo_page_product', $page);
                    //update_post_meta( $post_id_item, 'sidebar', 'home-sidebar');
                    update_post_meta( $post_id_item, 'sticky_sidebar', 'no');
                    update_post_meta( $post_id_item, 'footer_view', 'fixed');
                    //$customer_id
                    //post_author_override = 22 // AUTOR
                    update_post_meta( $post_id_item, 'post_author_override', (int) $customer_id );
                    //product_shipping_class = 20 //MOTO E CARRO
                    update_post_meta( $post_id_item, 'product_shipping_class', 20 );
                    //_reviews_allowed = yes // AVALIACAO
                    update_post_meta( $post_id_item, '_reviews_allowed', 'no' );

              } // FIM CRIA ITEM

} // FIM FUNCAO CRIA ITEM

?>
