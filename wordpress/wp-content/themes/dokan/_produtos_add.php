<?php

function pp($arr){
    $retStr = '<ul>';
    if (is_array($arr)){
        foreach ($arr as $key=>$val){
            if (is_array($val)){
                $retStr .= '<li>' . $key . ' => array(' . pp($val) . '),</li>';
            }else{
                $retStr .= '<li>' . $key . ' => ' . ($val == '' ? '""' : $val) . ',</li>';
            }
        }
    }
    $retStr .= '</ul>';
    return $retStr;
}
function pp_clean($arr){
    $retStr = ' ';
    if (is_array($arr)){
        foreach ($arr as $key=>$val){
            if (is_array($val)){
                $retStr .= ' --  ' . $key . ' => array(' . pp_clean($val) . ') |  ';
            }else{
                $retStr .= ' --  ' . $key . ' => ' . ($val == '' ? '""' : $val) . ' |  ';
            }
        }
    }
    $retStr .= '  ';
    return $retStr;
}


//CREATE TAG E PRODUTO - NOME DA LAVANDERIA
//add_action( 'user_register', 'user_register_tag_produto2', 0, 1 );
//add_action( 'woocommerce_created_customer', 'user_register_tag_produto', 0, 1 );
//add_action('wp_login', 'user_register_tag_produto2', 10, 2);
add_action('wp_head', 'check_new_produto_lavanderia_itens', 0);
//add_action('wp_footer', 'check_new_produto_lavanderia_itens', 0);
function check_new_produto_lavanderia_itens() {

  //dump('check_new_produto_lavanderia_itens');
  //return;

  if( is_admin() || !is_user_logged_in() ) return;

  global $current_user, $wpdb;

  $customer_id = get_current_user_id();
  $customer_user = wp_get_current_user();
  $customer_name = $current_user->display_name;
  $customer_login = $current_user->user_login;
  $customer_email = $current_user->user_email;
  /*
  $current_user->ID => 12
  $current_user->user_login => america
  $current_user->user_nicename => lavanderia-sul-america-matriz
  $current_user->user_email => america@lavaetraz.com.br
  $current_user->display_name => Unidade Matriz
  */

  $user_role = reset( $current_user->roles );
  if ( !in_array( 'seller', $current_user->roles ) ) return;

  if( isset($_GET['reset-acesso']) ) update_user_meta( $customer_id, 'user_login_acess', 0 );
  if( isset($_GET['reset-itens']) ) update_user_meta( $customer_id, 'itens_criados', false );

  $itens_criados = get_user_meta( $customer_id, 'itens_criados', true );
  if( !empty($itens_criados) ) return;

  //$user_login_acess = (int) get_user_meta( $customer_id, 'user_login_acess', true );
  //if( $user_login_acess > 1 ) return;

      $store_id   = get_current_user_id();
      $store_info = dokan_get_store_info( $store_id );
      $store_slug = $current_user->user_nicename;
      $store_name = $store_info['store_name'];
      $store_phone = $store_info['phone'];

      //dump($store_info);
      /*
      $store_info['store_name']
      $store_info['payment']['bank']['razao_social']
      $store_info['payment']['bank']['cnpj']
      $store_info['payment']['bank']['ie']
      $store_info['payment']['bank']['ac_name']
      $store_info['payment']['bank']['ac_number']
      $store_info['payment']['bank']['bank_name']
      $store_info['payment']['bank']['bank_addr']
      $store_info['payment']['bank']['swift']
      $store_info['phone']
      $store_info['address']['numero']
      $store_info['address']['complemento']
      $store_info['address']['zip']
      $store_info['address']['street_1']
      $store_info['address']['bairro']
      $store_info['address']['city']
      $store_info['address']['state']
      $store_info['address']['country']
      $store_info['address']['endereco']
      */

      //dump($store_slug);

      // CRIA UMA TAG COM O SLUG DA LAVANDERIA
      $tag = get_term_by( 'slug', $store_slug, 'product_tag');
      if( empty($tag) ){
          wp_insert_term( $store_slug, 'product_tag', array( 'slug' => $store_slug ) );
          $tag = get_term_by( 'slug', $store_slug, 'product_tag');
      }
      $tag = $tag->term_id;

      // VERIFICA SE JA EXISTE PRODUTO - LAVANDERIA
      $querystr = "
                  SELECT $wpdb->posts.ID
                  FROM $wpdb->posts
                  WHERE $wpdb->posts.post_name = '$store_slug'
                  AND $wpdb->posts.post_type = 'product'
                  ";
      $post_id = $wpdb->get_var($querystr);

      // SE NAO EXISTE CRIA PRODUTO - LAVANDERIA
      if( empty($post_id) ){

                //CRIA PRODUTO - LAVANDERIA
                $post_id = wp_insert_post( array(
                    'post_author' => $store_id,
                    'post_title' => $store_name,
                    'post_name' => $store_slug,
                    //'post_content' => pp($store_info),
                    'post_status' => 'publish',
                    'post_type' => "product",
                    'comment_status' => "closed",
                    'ping_status' => "closed",
                ) );

                //BUSCA CATEGORIA - TEMP LAVANDERIA
                $category = get_term_by( 'slug', 'temp-lavanderias', 'product_cat' );
                $cat_id = $category->term_id;
                wp_set_object_terms( $post_id, array($cat_id), 'product_cat' ); // TEMP
                wp_set_object_terms( $post_id, 'simple', 'product_type' );
                // INSERI TAG - NOME DA LAVANDERIA
                wp_set_object_terms( $post_id, array($tag), 'product_tag');
                //update_post_meta( $post_id, 'info_adm', pp_clean($store_info) );
                $valor_import = 0;
                //update_post_meta( $post_id, '_visibility', 'hidden' );
                //update_post_meta( $post_id, '_stock_status', 'instock');
                update_post_meta( $post_id, '_stock_status', 'outofstock');
                update_post_meta( $post_id, 'total_sales', '0' );
                update_post_meta( $post_id, '_downloadable', 'no' );
                update_post_meta( $post_id, '_virtual', 'no' );
                update_post_meta( $post_id, '_regular_price', $valor_import );
                update_post_meta( $post_id, '_sale_price', '' );
                update_post_meta( $post_id, '_purchase_note', '' );
                update_post_meta( $post_id, '_featured', 'no' );
                update_post_meta( $post_id, '_weight', 0 );
                update_post_meta( $post_id, '_length', 1 );
                update_post_meta( $post_id, '_width', 1 );
                update_post_meta( $post_id, '_height', 1 );
                //update_post_meta( $post_id, '_sku', 'LT00-1' );
                update_post_meta( $post_id, '_product_attributes', array() );
                update_post_meta( $post_id, '_sale_price_dates_from', '' );
                update_post_meta( $post_id, '_sale_price_dates_to', '' );
                update_post_meta( $post_id, '_price', $valor_import );
                update_post_meta( $post_id, '_sold_individually', 'yes' ); // 1 por pedido
                update_post_meta( $post_id, '_manage_stock', 'no' );
                //update_post_meta( $post_id, '_manage_stock', 'yes' );
                //update_post_meta( $post_id, '_backorders', 'no' );
                //update_post_meta( $post_id, '_stock', 10 );
                update_post_meta( $post_id, 'tm_meta_cpf', array('mode'=>'builder','override_display'=>'','override_final_total_box'=>'disable') );
                update_post_meta( $post_id, 'layout', 'fullwidth');
                //update_post_meta( $post_id, '_dependency_type', '2');
                //update_post_meta( $post_id, '_tied_products', array(684) );
                update_post_meta( $post_id, 'sticky_header', 'yes');
                update_post_meta( $post_id, 'header_view', 'fixed');
                //$page = get_page_by_path('modelo-lavanderia');
                //$page = $page->ID;
                //$sku = get_post_meta($theid, '_sku', true );
                //update_post_meta( $post_id, 'dhvc_woo_page_product', $page);
                //update_post_meta( $post_id, 'sidebar', 'home-sidebar');
                update_post_meta( $post_id, 'sticky_sidebar', 'no');
                update_post_meta( $post_id, 'footer_view', 'simple');
                //$customer_id
                //post_author_override = 22 // AUTOR
                update_post_meta( $post_id, 'post_author_override', $customer_id );
                //product_shipping_class = 20 //MOTO E CARRO
                update_post_meta( $post_id, 'product_shipping_class', 20 );
                //_reviews_allowed = yes // AVALIACAO
                update_post_meta( $post_id, '_reviews_allowed', 'no' );

                update_user_meta( $customer_id, 'itens_criados', true );
                return;
                //dump('CRIOU PRODUTO LAVANDERIA');
                //exit;
      }else{
          // DELETE PRODUCT
          //if( isset($_GET['delete-lavanderia']) ) wp_delete_post( $post_id, true);
      }

      // LOOP CATEGORIAS
      // BUSCA CATEGORIA ITENS
      $category = get_term_by( 'slug', 'itens', 'product_cat' );
      if( empty($category) ) dump('Não existe categoria - itens.',false,true);
      $cat_id = $category->term_id;
      $argsCat = array(
          'number'       => 0,
          'orderby'      => 'name',
          'order'        => 'ASC',
          'hide_empty'   => 0,
          'parent'       => $cat_id,
      );
      $lista_categories = get_terms( 'product_cat', $argsCat );

      /*
      //$mostrar_cat = true;
      $count = count($lista_categories);
      if ( $count > 0 && isset($mostrar_cat) ){
          foreach ( $lista_categories as $cat ) {
              //echo ('<b>ID:</b> '.$cat->term_id.'   --   <b>Nome:</b> '.$cat->name.'    --   <b>Slug:</b> '.$cat->slug.'<br/>');
              //echo '$'.str_replace('-','_',$cat->slug).' = getIdObject($lista_categories,\''.$cat->slug.'\'); <br/>';
              //echo '$'.str_replace('-','_',$cat->slug).'<br/>';
              echo '\''.$cat->slug.'\',<br/>';
          } // FIM LOOP CATEGORIAS
          exit;
      }

      //OPCOES
      //$opcao_mostrar = getIdObject($lista_categories,'opcao-mostrar');
      $opcao_caracteristicas = getIdObject($lista_categories,'opcao-caracteristicas');
      $opcao_cores = getIdObject($lista_categories,'opcao-cores');
      $opcao_defeitos = getIdObject($lista_categories,'opcao-defeitos');
      $opcao_marca = getIdObject($lista_categories,'opcao-marca');
      $opcao_somente_lavar = getIdObject($lista_categories,'opcao-somente-lavar');
      //CATEGORIAS
      $acessorios = getIdObject($lista_categories,'acessorios');
      $acessorios_femininos = getIdObject($lista_categories,'acessorios-femininos');
      $acessorios_masculinos = getIdObject($lista_categories,'acessorios-masculinos');
      $bichos_de_pelucia = getIdObject($lista_categories,'bichos-de-pelucia');
      $calcados = getIdObject($lista_categories,'calcados');
      $especial_advogados = getIdObject($lista_categories,'especial-advogados');
      $especial_bebes = getIdObject($lista_categories,'especial-bebes');
      $especial_hoteis = getIdObject($lista_categories,'especial-hoteis');
      $especial_noivas = getIdObject($lista_categories,'especial-noivas');
      $especial_saude = getIdObject($lista_categories,'especial-saude');
      $especial_restaurantes = getIdObject($lista_categories,'especial-restaurantes');
      $itens_de_acampamento = getIdObject($lista_categories,'itens-de-acampamento');
      $itens_de_automoveis = getIdObject($lista_categories,'itens-de-automoveis');
      $itens_de_cozinha = getIdObject($lista_categories,'itens-de-cozinha');
      $itens_domesticos = getIdObject($lista_categories,'itens-domesticos');
      $itens_para_casa = getIdObject($lista_categories,'itens-para-casa');
      $itens_para_empresas = getIdObject($lista_categories,'itens-para-empresas');
      $itens_para_lavagem_a_seco = getIdObject($lista_categories,'itens-para-lavagem-a-seco');
      $malas_e_mochilas = getIdObject($lista_categories,'malas-e-mochilas');
      $promocoes = getIdObject($lista_categories,'promocoes');
      $roupas_de_banho = getIdObject($lista_categories,'roupas-de-banho');
      $roupas_de_cama = getIdObject($lista_categories,'roupas-de-cama');
      $roupas_de_couro = getIdObject($lista_categories,'roupas-de-couro');
      $roupas_de_esportes = getIdObject($lista_categories,'roupas-de-esportes');
      $roupas_de_festas = getIdObject($lista_categories,'roupas-de-festas');
      $roupas_de_frio = getIdObject($lista_categories,'roupas-de-frio');
      $roupas_de_grife = getIdObject($lista_categories,'roupas-de-grife');
      $roupas_de_praia = getIdObject($lista_categories,'roupas-de-praia');
      $roupas_do_dia_a_dia = getIdObject($lista_categories,'roupas-do-dia-a-dia');
      $roupas_especiais = getIdObject($lista_categories,'roupas-especiais');
      $roupas_femininas = getIdObject($lista_categories,'roupas-femininas');
      $roupas_infantis = getIdObject($lista_categories,'roupas-infantis');
      $roupas_intimas_delicadas = getIdObject($lista_categories,'roupas-intimas-delicadas');
      $roupas_sociais = getIdObject($lista_categories,'roupas-sociais');
      $tapetes_cortinas = getIdObject($lista_categories,'tapetes-cortinas');
      $uniformes = getIdObject($lista_categories,'uniformes');
      */

      require_once('_produtos_lista.php');

      //dump('itens-criados');
      //exit;
      return;

}// FIM CHECK NEW PRODUTO - LAVANDERIA e ITENS DE LAVANDERIAS

function objArraySearch($array, $index, $value){
        foreach($array as $arrayInf) {
            if($arrayInf->{$index} == $value) {
                return $arrayInf;
            }
        }
        return null;
}
function getIdObject($array,$slug){
    $key = objArraySearch($array,'slug',$slug);
    if(!$key) return;
    return $key->term_id;
}

require_once('_create_item.php');






 ?>
