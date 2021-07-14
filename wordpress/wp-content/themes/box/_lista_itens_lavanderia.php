<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_action('wp_ajax_nopriv_get_lista_itens_ajax', 'get_lista_itens_ajax');
add_action('wp_ajax_get_lista_itens_ajax', 'get_lista_itens_ajax');
function get_lista_itens_ajax( $cat_id, $cat_slug, $cat_posts) {

      //$output = '';
      //global $wpdb;
      //ob_start();
      //dump($_POST);
      extract( $_POST );
      echo do_shortcode('[wcplpro categories_inc="'.$cat_id.'" wcplid="'.$cat_slug.'" posts_inc="'.$cat_posts.'" ]');
      //echo do_shortcode('[shortcode_cidade]');
      //echo $cat_posts;

      /*
      $love = get_post_meta( $_POST['post_id'], 'post_love', true );
    	$love++;
    	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
    		update_post_meta( $_POST['post_id'], 'post_love', $love );
    		echo $love;
    	}
    	die();
      */

      //$output = ob_get_contents();
      //ob_end_clean();
      //return $output;
      exit;
}

///*
function shortcode_lista_itens($atts, $content, $tag) {

            $return = '';

            $cliente_slug = get_post_field( 'post_name', get_post() );


            //delete_transient( 'itens-'.$cliente_slug );
            //$itens_cliente = get_transient( 'itens-'.$cliente_slug );
            //dump($itens_cliente);
            if( !empty($itens_cliente) ){
                    ob_start();
                    dump('TRANSIENT '.'itens-'.$cliente_slug);
                    echo do_shortcode( $itens_cliente );
                    $return = ob_get_contents();
                    ob_get_clean();
                    return $return;
            }

            ob_start();

            global $product;
        	  $totalItens = 0;
            /*
            if(strrpos($cliente,',') !== FALSE){
                  $clientes = array();
                  $clientes = explode(",",$cliente);
                  $clientes_array = array();
                  foreach ( $clientes as $cliente_array ) {
                      $cliente = get_term_by('slug', $cliente_array, 'product_tag');
                      $cliente = $cliente->term_id;
                      array_push($clientes_array,$cliente);
                  }
                  $clientes = array_map( 'intval', $clientes_array );
                  $clientes = array_unique( $clientes );
                  $cliente = $clientes;
            }else{
                  $cliente = get_term_by('slug', $cliente, 'product_tag');
                  $cliente = $cliente->term_id;
            }
            */
            $cliente = get_term_by('slug', $cliente_slug, 'product_tag');
            $cliente = $cliente->term_id;

            //BUSCA CATEGORIA ITENS
            $category = get_term_by( 'slug', 'itens', 'product_cat' );
            $cat_id = $category->term_id;
            $lista = '[vc_row el_class="produto-table"]
                        [vc_column]
                            [vc_tta_accordion style="flat" color="blue" c_position="right" active_section="1" no_fill="true" collapsible_all="false"]';

            $argsCat = array(
                'number'       => 0,
                'orderby'      => 'name',
                'order'        => 'ASC',
                'hide_empty'   => 1,
                'parent'       => $cat_id,
                //'ignore_sticky_posts' =>  true,
                //'no_found_rows'  => true
            );
            $product_categories = get_terms( 'product_cat', $argsCat );
            $count = count($product_categories);
            //dump($count);
            if ( $count > 0 ){


                $argsItem = array(
                    'posts_per_page' => -1,
                    'post_type' => 'product',
                    'orderby' => 'title',
                    'order' => 'ASC',
                    'ignore_sticky_posts' =>  true,
                    'no_found_rows'  => true
                );

                          $cat_promocao_slug = 'promocoes';
                          $cat_promocao = get_term_by( 'slug', $cat_promocao_slug, 'product_cat');
                          //dump($cat_promocao,true);
                          $argsItem['tax_query'] = array(
                                  'relation' => 'AND',
                                  array(
                                      'taxonomy' => 'product_cat',
                                      'field' => 'slug',
                                      'terms' => $cat_promocao_slug,
                                  ),
                                  array(
                                      'taxonomy' => 'product_tag',
                                      //'field' => 'slug',
                                      'field' => 'id',
                                      'terms' => $cliente,
                                  ),
                          );
                          $products = new WP_Query( $argsItem );
                          $total = $products->post_count;
                          //dump($total);
                          if( $total > 0 ){
                                    $cat_id   = $cat_promocao->term_id;
                                    $cat_slug = 'lista-'.$cat_promocao->slug;
                                    $cat_name = $cat_promocao->name;
                                    $cat_count = $cat_promocao->count;

                                    $produtos_array = array();
                                    while ( $products->have_posts() ){
                                        $products->the_post();
                                        $lista_post_id = get_the_ID();
                                        array_push($produtos_array, $lista_post_id);
                                    }
                                    wp_reset_postdata();
                                    $produtos_array = implode(',',$produtos_array);
                                    $lista .= '[vc_tta_section title="'.$cat_name.' ('.count($produtos_array).')" tab_id="'.$cat_slug.'" el_class="title-'.$cat_slug.' hidden2 click-get-itens click-get-itens-promocoes"]
                                                [vc_column_text]';
                                    $lista .= '<div class=" itens-categoria '.$cat_slug.'" data-cat-id="'.$cat_id.'" data-cat-count="'.$cat_count.'" data-cat-slug="'.$cat_slug.'" data-cat-posts="'.$produtos_array.'"></div>';
                                    $lista .= '[/vc_column_text]
                                            [/vc_tta_section]';
                          }

                          // NOVIDADES
                          $cat_promocao_slug = 'novidades';
                          $cat_promocao = get_term_by( 'slug', $cat_promocao_slug, 'product_cat');
                          //dump($cat_promocao,true);
                          $argsItem['tax_query'] = array(
                                  'relation' => 'AND',
                                  array(
                                      'taxonomy' => 'product_cat',
                                      'field' => 'slug',
                                      'terms' => $cat_promocao_slug,
                                  ),
                                  array(
                                      'taxonomy' => 'product_tag',
                                      //'field' => 'slug',
                                      'field' => 'id',
                                      'terms' => $cliente,
                                  ),
                          );
                          $products = new WP_Query( $argsItem );
                          $total = $products->post_count;
                          //dump($total);
                          if( $total > 0 ){
                                    $cat_id   = $cat_promocao->term_id;
                                    $cat_slug = 'lista-'.$cat_promocao->slug;
                                    $cat_name = $cat_promocao->name;
                                    $cat_count = $cat_promocao->count;

                                    $produtos_array = array();
                                    while ( $products->have_posts() ){
                                        $products->the_post();
                                        $lista_post_id = get_the_ID();
                                        array_push($produtos_array, $lista_post_id);
                                    }
                                    wp_reset_postdata();
                                    $produtos_array = implode(',',$produtos_array);
                                    $lista .= '[vc_tta_section title="'.$cat_name.' ('.count($produtos_array).')" tab_id="'.$cat_slug.'" el_class="title-'.$cat_slug.' hidden2 click-get-itens click-get-itens-novidades"]
                                                [vc_column_text]';
                                    $lista .= '<div class=" itens-categoria '.$cat_slug.'" data-cat-id="'.$cat_id.'" data-cat-count="'.$cat_count.'" data-cat-slug="'.$cat_slug.'" data-cat-posts="'.$produtos_array.'"></div>';
                                    $lista .= '[/vc_column_text]
                                            [/vc_tta_section]';
                          }

                          //Mais Pedidos
                          $cat_promocao_slug = 'mais-pedidos';
                          $cat_promocao = get_term_by( 'slug', $cat_promocao_slug, 'product_cat');
                          //dump($cat_promocao,true);
                          $argsItem['tax_query'] = array(
                                  'relation' => 'AND',
                                  array(
                                      'taxonomy' => 'product_cat',
                                      'field' => 'slug',
                                      'terms' => $cat_promocao_slug,
                                  ),
                                  array(
                                      'taxonomy' => 'product_tag',
                                      //'field' => 'slug',
                                      'field' => 'id',
                                      'terms' => $cliente,
                                  ),
                          );
                          $products = new WP_Query( $argsItem );
                          $total = $products->post_count;
                          //dump($total);
                          if( $total > 0 ){
                                    $cat_id   = $cat_promocao->term_id;
                                    $cat_slug = 'lista-'.$cat_promocao->slug;
                                    $cat_name = $cat_promocao->name;
                                    $cat_count = $cat_promocao->count;
                                    $lista .= '[vc_tta_section title="'.$cat_name.'" tab_id="'.$cat_slug.'" el_class="title-'.$cat_slug.' hidden2 click-get-itens click-get-itens-mais-pedidos"]
                                                [vc_column_text]';
                                    $produtos_array = array();
                                    while ( $products->have_posts() ){
                                        $products->the_post();
                                        $lista_post_id = get_the_ID();
                                        array_push($produtos_array, $lista_post_id);
                                    }
                                    wp_reset_postdata();
                                    $produtos_array = implode(',',$produtos_array);
                                    $lista .= '<div class=" itens-categoria '.$cat_slug.'" data-cat-id="'.$cat_id.'" data-cat-count="'.$cat_count.'" data-cat-slug="'.$cat_slug.'" data-cat-posts="'.$produtos_array.'"></div>';
                                    $lista .= '[/vc_column_text]
                                            [/vc_tta_section]';
                          }




                //dump($product_categories);
                foreach ( $product_categories as $cat ) {
                    //dump($cat);
                    if( strpos($cat->slug, 'servcliente') !== false
                    || strpos($cat->slug, 'novidade') !== false
                    || strpos($cat->slug, 'promocoes') !== false
                    || strpos($cat->slug, 'mais-pedidos') !== false
                     ) continue;

                    $cat = get_term_by( 'id', $cat->term_id, 'product_cat');

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
                                'terms' => $cliente,
                            ),
                    );
                    $products = new WP_Query( $argsItem );
                    $total = $products->post_count;
                    //dump($total);
                    if( $total > 0 ){
                            $cat_id   = $cat->term_id;
                            $cat_slug = 'lista-'.$cat->slug;
                            $cat_name = $cat->name;
                            $cat_count = $cat->count;
    					              $lista .= '[vc_tta_section title="'.$cat_name.'" tab_id="'.$cat_slug.'" el_class="title-'.$cat_slug.' click-get-itens"]
                                        [vc_column_text]';
                            $produtos_array = array();
                            while ( $products->have_posts() ){
                                $products->the_post();
                                $lista_post_id = get_the_ID();
                                array_push($produtos_array,$lista_post_id);
                            }
                            wp_reset_postdata();
                            $produtos_array = implode(',',$produtos_array);
                            $lista .= '<div class=" itens-categoria '.$cat_slug.'" data-cat-id="'.$cat_id.'" data-cat-count="'.$cat_count.'" data-cat-slug="'.$cat_slug.'" data-cat-posts="'.$produtos_array.'"></div>';
                            $lista .= '[/vc_column_text]
                                    [/vc_tta_section]';
					                  $totalItens++;

                    } // FIM TOTAL PRODUTOS NA CAT

                } // FIM LOOP CATEGORIAS

            } // FIM TOTAL CATEGORIAS

            $lista .= '[/vc_tta_accordion]
                        [/vc_column]
                    [/vc_row]';

            if( $totalItens == 0 ){

              $lista = '<div class="nenhum-item-lavanderia">';
                //if($settings_closing_style == 'datewise') $lista .= ' Estamos de férias, retornamos em: <b>'.$retorno.'</b>';
                $lista .= '<div class="nenhum-item-lavanderia-icon zoom"></div>';
                //$lista .= $store_info['setting_vacation_message'];
                $lista .= '<div class="ocupadas">Desculpe, mas estamos recebendo pedidos no momento, todas as nossas maquinas de lavar estão ocupadas.<br/><b>Tente mais tarde, obrigado!</b></div>';
              $lista .= '</div>';
              $lista .= '<style>.title-pedido-peca,.cart-pedido-lavanderia,.title-enviar-peca{display:none !important;}</style>';

      			  //$lista = '<div class="nenhum-item-lavanderia"><div class="icon"><i class="icon icon-wh-custom-washing-machine-2 faa-slow text-danger faa-flash animated"></i></div>Desculpe, todas as nossas maquinas de lavar estão ocupadas.<br/>Tente mais tarde!</div>';

      			}
            //set_transient( 'itens-'.$cliente_slug, $lista, 3600 );

            echo do_shortcode( $lista );
            $return = ob_get_contents();
          ob_get_clean();
      return $return;
}
add_shortcode('shortcode_lista_itens', 'shortcode_lista_itens');
//*/

// SHORTCODE LISTA ITENS - CATEGORIA
function shortcode_lista($atts, $content, $tag) {
      $return = '';

      ob_start();

      global $product;
      //$cliente = $product->slug;
      //dump($cliente);

      $product = wc_get_product( get_the_ID() );
      $author = get_the_author_meta('ID');

      //$author = get_user_by( 'id', $product->post->post_author );
      $store_info = dokan_get_store_info( $author );

      //$store_user    = get_userdata( get_query_var( 'author' ) );
      //dump($author->ID);
      //exit;
      //$store_info    = dokan_get_store_info( $store_user->ID );
      //$store_tabs    = dokan_get_store_tabs( $store_user->ID );

      //$setting_go_vacation = $store_info['setting_go_vacation']; //yes
      $setting_go_vacation = !empty($store_info['setting_go_vacation']) ? $store_info['setting_go_vacation'] : false;

      //$settings_closing_style = $store_info['settings_closing_style']; //datewise
  	  $settings_closing_style = !empty($store_info['settings_closing_style']) ? $store_info['settings_closing_style'] : false;

      //$settings_close_from = $store_info['settings_close_from'];
  	  $settings_close_from = !empty($store_info['settings_close_from']) ? $store_info['settings_close_from'] : false;
      //$settings_close_to = $store_info['settings_close_to'];
  	  $settings_close_to = !empty($store_info['settings_close_to']) ? $store_info['settings_close_to'] : false;

  	  if( $settings_close_from ){
		  $from_date = date( 'Y-m-d', strtotime( $settings_close_from ) );
		  $to_date = date( 'Y-m-d', strtotime( $settings_close_to ) );
		  $now = date( 'Y-m-d' );
	  }

      $retorno = date( 'd/m/Y', strtotime("+1 days",strtotime($settings_close_to))  );

      //dump($from_date);
      //dump($to_date);
      //dump($now);
      //if ( $from_date <= $now && $to_date >= $now ) {
          // Date is within beginning and ending time
          //$this->update_product_status( 'publish', 'vacation' );
      //} else {
          // Date is not within beginning and ending time
          //$this->update_product_status( 'vacation', 'publish' );
      //}

      if ( $setting_go_vacation && $setting_go_vacation == 'yes' ){

        if ( $settings_closing_style == 'instantly' ){

            $lista = '<div class="nenhum-item-lavanderia">';
              $lista .= $store_info['setting_vacation_message'];
            $lista .= '</div>';

        }else if ( $settings_closing_style == 'datewise' && $from_date <= $now && $to_date >= $now ) {

            // Date is within beginning and ending time
            //print 'A data está dentro da hora de início e término';
            //dump($store_info['setting_vacation_message']);
            $lista = '<div class="nenhum-item-lavanderia">';
              if($settings_closing_style == 'datewise') $lista .= ' Estamos de férias, retornamos em: <b>'.$retorno.'</b>';
              else $lista .= ' Estamos de férias, em breve estaremos de volta!';
              $lista .= '<div class="lavanderia-ferias"></div>';
              $lista .= $store_info['setting_vacation_message'];
            $lista .= '</div>';

          }




      }else{

        $lista = '[vc_row]
                    [vc_column width="1/3"]
                        [vc_custom_heading text="Pedido por peça" font_container="tag:h2|font_size:27px|text_align:center|color:%231e73be" google_fonts="font_family:Cabin%3Aregular%2Citalic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic|font_style:500%20bold%20italic%3A500%3Aitalic" el_class="title-vantagem sombraTexto2- title-pedido-peca" ]
                        [dhvc_woo_product_page_add_to_cart el_class="cart-pedido-lavanderia"]
                        [porto_block el_class="carrinho-pedido-lavanderia" name="bloco-carrinho"]
                    [/vc_column]
                    [vc_column width="2/3" ]
                        [vc_custom_heading text="<b>2º Passo:</b> Adicione as peças para Lavar" font_container="tag:h2|font_size:27px|text_align:center|color:%2300317c|line_height:34px" google_fonts="font_family:Cabin%3Aregular%2Citalic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic|font_style:400%20italic%3A400%3Aitalic" el_class="title-vantagem sombraTexto2- title-enviar-peca" ][vc_column_text][shortcode_lista_itens][/vc_column_text]
                    [/vc_column]
                  [/vc_row]';
        //is_sticky="yes" sticky_min_width="767" sticky_top="40" sticky_bottom="20"
        //[shipping-calculator]
      }
      echo do_shortcode( $lista );

      $return = ob_get_contents();
      ob_get_clean();
      return $return;
}
add_shortcode('shortcode_lista', 'shortcode_lista');


?>
