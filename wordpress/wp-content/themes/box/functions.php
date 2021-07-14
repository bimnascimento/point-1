<?php

add_action('wp_enqueue_scripts', 'box_init_css', 1001);

// Load CSS
function box_init_css() {

    // child theme styles
    wp_deregister_style( 'styles-child' );
    wp_register_style( 'styles-child', get_stylesheet_directory_uri() . '/style.css' );
    wp_enqueue_style( 'styles-child' );

    if (is_rtl()) {
        wp_deregister_style( 'styles-child-rtl' );
        wp_register_style( 'styles-child-rtl', get_stylesheet_directory_uri() . '/style_rtl.css' );
        wp_enqueue_style( 'styles-child-rtl' );
    }

}



//Remova as Versões do WordPress
add_filter( 'the_generator', '__return_null');
function remove_cssjs_ver( $src ) {
    if( strpos( $src, '?ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}

//Remova Scripts de Versão
add_filter( 'style_loader_src', 'remove_cssjs_ver', 1000 );
add_filter( 'script_loader_src', 'remove_cssjs_ver', 1000 );

//Habilite Shortcodes na área de Widgets
//add_filter('widget_text', 'do_shortcode');

///*
function my_scripts_method() {
    //wp_enqueue_script( 'scriptaculous' );
    /*
    global $wp_scripts, $wp_styles;
    foreach( $wp_scripts->queue as $handle ) :
        dump($handle);
    endforeach;
    */
    //$slug = basename(get_permalink());
    //$slug = get_page();
    //dump($slug);
    //$get_the_url = 'http://maps.google.com/maps/api/js?key=AIzaSyBizveBUTEM2KVKlJnV5hifKmVmfggjkJQ';
    //wp_register_script( 'mapa-distancia', $get_the_url, array('jquery'), null, true);
    //wp_enqueue_script('mapa-distancia');
}
//add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
//*/

//Restrinja o WooCommerce
//add_action( 'wp_enqueue_scripts', 'grd_woocommerce_script_cleaner', 99 );
function grd_woocommerce_script_cleaner() {

    $array_plugins = apply_filters('active_plugins', get_option( 'active_plugins' ));
    if( !in_array('woocommerce/woocommerce.php', $array_plugins ) ) return;

    // Remove the generator tag, to reduce WooCommerce based hacking attacks
    remove_action( 'wp_head', array( $GLOBALS['woocommerce'], 'generator' ) );
    // Unless we're in the store, remove all the scripts and junk!
    if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
        wp_dequeue_style( 'woocommerce_frontend_styles' );
        wp_dequeue_style( 'woocommerce-general');
        wp_dequeue_style( 'woocommerce-layout' );
        wp_dequeue_style( 'woocommerce-smallscreen' );
        wp_dequeue_style( 'woocommerce_fancybox_styles' );
        wp_dequeue_style( 'woocommerce_chosen_styles' );
        wp_dequeue_style( 'woocommerce_prettyPhoto_css' );
        wp_dequeue_style( 'select2' );
        wp_dequeue_script( 'wc-add-payment-method' );
        wp_dequeue_script( 'wc-lost-password' );
        wp_dequeue_script( 'wc_price_slider' );
        wp_dequeue_script( 'wc-single-product' );
        wp_dequeue_script( 'wc-add-to-cart' );
        wp_dequeue_script( 'wc-cart-fragments' );
        wp_dequeue_script( 'wc-credit-card-form' );
        wp_dequeue_script( 'wc-checkout' );
        wp_dequeue_script( 'wc-add-to-cart-variation' );
        wp_dequeue_script( 'wc-single-product' );
        wp_dequeue_script( 'wc-cart' );
        wp_dequeue_script( 'wc-chosen' );
        wp_dequeue_script( 'woocommerce' );
        wp_dequeue_script( 'prettyPhoto' );
        wp_dequeue_script( 'prettyPhoto-init' );
        wp_dequeue_script( 'jquery-blockui' );
        wp_dequeue_script( 'jquery-placeholder' );
        wp_dequeue_script( 'jquery-payment' );
        wp_dequeue_script( 'jqueryui' );
        wp_dequeue_script( 'fancybox' );
        wp_dequeue_script( 'wcqi-js' );

        //wp_dequeue_script( 'rpship-calculator-setting' );

    }
}

/*
add_action( 'phpmailer_init', 'send_smtp_email' );
function send_smtp_email( $phpmailer ) {
    $phpmailer->isSMTP();
    $phpmailer->Host       = SMTP_HOST;
    $phpmailer->SMTPAuth   = SMTP_AUTH;
    $phpmailer->Port       = SMTP_PORT;
    $phpmailer->Username   = SMTP_USER;
    $phpmailer->Password   = SMTP_PASS;
    $phpmailer->SMTPSecure = SMTP_SECURE;
    $phpmailer->From       = SMTP_FROM;
    $phpmailer->FromName   = SMTP_NAME;
}
*/

//require_once('_dump.php');
//require_once('_manutencao.php');
require_once('_admin_css.php');

add_action( 'init', 'process_post' );
function process_post() {

        //dump($_SERVER["HTTP_HOST"],true);

        //if($_SERVER["HTTPS"] != "on" && $_SERVER["HTTP_HOST"]=="www.pointlave.com.br"){
        if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) != "on" && $_SERVER["HTTP_HOST"] == "www.pointlave.com.br" ){
            header("Location: https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
            exit();
        }

      /*
      if( !isset($_COOKIE['visita']) ) {
          setcookie('visita', session_id(), strtotime('+1 day'));
      } else {
          //echo "The cookie '" . $visita . "' is set.";
          //echo "Cookie is:  " . $_COOKIE[$visita];
      }
      //unset( $_COOKIE[$visita] );
      //setcookie( $visita, null, strtotime('-1 day') );
      */

      $_SESSION["ipAtual"] = get_ip();

      if( !isset($_COOKIE['visita']) ) {
          setcookie('visita', session_id(), strtotime('+1 day'));
          //setcookie('cidadeAtual', session_id(), strtotime('+1 day'));
      }

      //if( !isset($_COOKIE['cidadeAtual']) && isset($_SESSION['enderecoCidade']) ) {
          //setcookie('cidadeAtual', $_SESSION['enderecoCidade'], strtotime('+30 day'));
      //}

      //$enderecoDistancia = isset($_SESSION['enderecoDistancia']) ? $_SESSION['enderecoDistancia'] : false;
      //if( !isset($_COOKIE['enderecoDistancia']) && isset($_SESSION['enderecoDistancia']) ) {
      /*if( isset($_SESSION['enderecoDistancia']) ) {
          setcookie('enderecoDistancia',$_SESSION['enderecoDistancia'], strtotime('+1 day'));
      }

      if( isset($_SESSION['enderecoCEP']) ) {
          setcookie('enderecoCEP',$_SESSION['enderecoCEP'], strtotime('+1 day'));
      }*/

      //dump($_SESSION);
      //dump($_COOKIE['site_id']);,


      global $wpdb;
      $current_site_id = (int) $wpdb->blogid;
      $slug = basename(get_permalink());
      //$site_id = get_site_transient( 'site-id' );
      $site_id = isset($_COOKIE['site-id']) ? $_COOKIE['site-id'] : '';
      //dump($site_id);
      //if( $current_site_id != 1 && empty($site_id) ){
          //set_site_transient( 'site-id', $current_site_id, 7884000 ); // 3 meses
          //setcookie( 'site-id', $current_site_id, strtotime('+365 day'));
      //}

      $absolute_url = full_url( $_SERVER );

      //dump($site_id);
      //dump($absolute_url);
      //delete_site_transient( 'site-id' );

      if( $current_site_id == 1 && !empty($site_id) && ( $absolute_url=='http://192.168.1.25/pointlave/' || $absolute_url=='http://www.pointlave.com.br/' || $absolute_url=='https://www.pointlave.com.br/' ) ){
          $url = get_site_url($site_id);
          //dump($url,true);
          wp_redirect( $url );
          exit;
      }

      if( $current_site_id == 1 && isset($_GET['site-id']) ){
          setcookie( 'site-id', $_GET['site-id'], strtotime('+365 day'));
          $url = get_site_url($_GET['site-id']);
          //dump($url);
          wp_redirect( $url );
          exit;
      }
      if( isset($_GET['reset']) && $_GET['reset']=='site-id' ){
           setcookie('site-id','');
           unset($_COOKIE['site-id']);
      }
}


function pid_teste() {

    $enderecoCEPCookie = isset($_COOKIE['enderecoCEP']) ? $_COOKIE['enderecoCEP'] : false;
    $enderecoCEP = isset($_SESSION['enderecoCEP']) ? $_SESSION['enderecoCEP'] : $enderecoCEPCookie;
    //dump($enderecoCEPCookie);
    //dump($enderecoCEP);

    $enderecoDistanciaCookie = isset($_COOKIE['enderecoDistancia']) ? $_COOKIE['enderecoDistancia'] : false;
    $enderecoDistancia = isset($_SESSION['enderecoDistancia']) ? $_SESSION['enderecoDistancia'] : $enderecoDistanciaCookie;
    //dump($enderecoDistanciaCookie);
    //dump($enderecoDistancia);

    if( is_user_logged_in() ){

          global $current_user, $wpdb;
          $customer_id = get_current_user_id();
          $customer_user = wp_get_current_user();
          $customer_name = $current_user->display_name;
          $customer_login = $current_user->user_login;
          $customer_email = $current_user->user_email;
          $enderecoCEP = get_user_meta( $customer_id , 'billing_postcode', true );
          $endereco = get_user_meta( $customer_id , 'billing_address_1', true );
          $enderecoBairro = get_user_meta( $customer_id , 'billing_neighborhood', true );
          $enderecoCidade = get_user_meta( $customer_id , 'billing_city', true );
          $enderecoEstado = get_user_meta( $customer_id , 'billing_state', true );

          if( $endereco && $enderecoBairro && $enderecoCEP ){
              $enderecoDistancia = $endereco.', '.$enderecoBairro.', '.$enderecoCidade.' - '.$enderecoEstado.', '.$enderecoCEP;
          }

    }

    echo '<script>';
    if( isset($_SESSION['enderecoLavanderias']) ){
      foreach ($_SESSION['enderecoLavanderias'] as $item) {
          echo 'enderecoLavanderias.push(".'.$item.'");';
      }
    }
    echo ' enderecoDistancia = "'.$enderecoDistancia.'"; ';
    echo ' enderecoCEP = "'.$enderecoCEP.'"; ';
    echo '</script>';

    /*
    global $wpdb;
    // sorry about format I hate scrollbars in answers.
    $your_transients = $wpdb->get_results(
           "SELECT option_name AS name, option_value AS value FROM $wpdb->options
            WHERE option_name LIKE '_transient_itens%'"
    );
    //$itens_cliente = get_transient();
    foreach ($your_transients as $key => $value) {
        $transient_name = explode('_',$value->name);
        $transient_name = $transient_name[2];
        //dump( $transient_name );
        delete_transient( $transient_name );
    }
    */


}
add_action('wp_head','pid_teste',10);





///*

require_once('plugins/plugins.php');
require_once('font/fonts.php');
require_once('js/script.php');
require_once('css/style.php');


//require_once('_get_ip.php');
//require_once('_pid.php');
//require_once('_shortcode_cidade.php');
//require_once('_shortcode_estado.php');


require_once('_functions.php');
require_once('_new_user_errors.php');
require_once('_new_user.php');
require_once('_new_user_cidade.php');
require_once('_shortcode_valida_email.php');

require_once('_save_lavanderia_custom.php');

require_once('_shortcode_info_lavanderia.php');
require_once('_button_suporte_lavanderia.php');
require_once('_lista_itens_lavanderia.php');

require_once('_salva_endereco_distancia.php');
require_once('_update_shipping_method_custom.php');

//require_once('_woocommerce_calculated_shipping_custom.php');
require_once('_woocommerce_checkout_update_order_review_custom.php');
//require_once('_woocommerce_checkout_process_custom.php');


require_once('_wc_vendors_name_loop_custom.php');
require_once('_dokan_product_seller_link_custom.php');


require_once('_porto_product_quickview.php');

require_once('_add_login_out_item_to_menu.php');
require_once('_link_logout.php');

require_once('_filtro_cat_lavanderia.php');
require_once('_carrinho_somente_uma_lavanderia.php');

require_once('_dokan_add_menu_ajuda.php');

require_once('_minha_conta.php');




//*/







/*
$.get( rp_ajax_url, { action: "cienna_show_slider_callback" }, function( data ) {
  console.log(data);
});
add_action('wp_ajax_nopriv_cienna_show_slider_callback', 'cienna_show_slider_callback');
add_action('wp_ajax_cienna_show_slider_callback', 'cienna_show_slider_callback');
function cienna_show_slider_callback() {
    global $post, $product, $woocommerce; // just in case if your template file need this
    ob_start();
    ?>
    <?php woocommerce_get_template( 'archive-product.php'); ?>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    echo $output;
    die();
}
*/




/*
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail');
remove_action('woocommerce_before_shop_loop_item_title', 'porto_loop_product_thumbnail');
add_action('woocommerce_before_shop_loop_item_title', 'porto_loop_product_thumbnail_custom');
// change product thumbnail in products list page
function porto_loop_product_thumbnail_custom() {
    global $porto_settings;
    $id = get_the_ID();
    $size = 'shop_catalog';
    $gallery = get_post_meta($id, '_product_image_gallery', true);
    $attachment_image = '';
    if (!empty($gallery) && $porto_settings['category-image-hover']) {
        $gallery = explode(',', $gallery);
        $first_image_id = $gallery[0];
        $attachment_image = wp_get_attachment_image($first_image_id , $size, false, array('class' => 'hover-image'));
    }
    $thumb_image = get_the_post_thumbnail($id , $size, array('class' => ''));
    if (!$thumb_image) {
        if ( wc_placeholder_img_src() ) {
            $thumb_image = wc_placeholder_img( $size );
        }
    }
    echo '<div class="aa inner'.(($attachment_image)?' img-effect':'').'">';
    // show images
    echo $thumb_image;
    echo $attachment_image;
    echo '</div>';
}
*/




/*
//ADICIONAR AO CARRINHO SOMENTE ITEM DE UMA LOJA
//add_filter( 'woocommerce_add_cart_item_data', 'woo_custom_add_to_cart2', 10, 2);
function woo_custom_add_to_cart2( $cart_item_data,$id) {
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
          //$sold_individually = get_post_meta( $id, 'sold_individually', true );
          //dump($id);
          //exit;
          //exit;
          //$woocommerce->cart->remove_cart_item($product_3);


      }
    return $cart_item_data;
}
*/





/*
add_filter('login_redirect', function($redirect_to, $request_redirect_to, $user)
{
    global $blog_id;
    if (!is_wp_error($user) && $user->ID != 0)
    {
        $user_info = get_userdata($user->ID);
        if ($user_info->primary_blog)
        {
            $primary_url = get_blogaddress_by_id($user_info->primary_blog) . 'wp-admin/';
            $user_blogs = get_blogs_of_user($user->ID);

            //Loop and see if user has access
            $allowed = false;
            foreach($user_blogs as $user_blog)
            {
                if($user_blog->userblog_id == $blog_id)
                {
                    $allowed = true;
                    break;
                }
            }

            //Let users login to others blog IF we can get their primary blog URL and they are not allowed on this blog
            if ($primary_url && !$allowed)
            {
                wp_redirect($primary_url);
                die();
            }
        }
    }
    return $redirect_to;
}, 100, 3);
*/






/*
function shortcode_lista_itens2($atts, $content, $tag) {
      $return = '';
      //$cliente = $atts['cliente'];
      //dump($cliente);

            ob_start();

            global $product;
            $cliente = $product->slug;

        	  $totalItens = 0;

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
                'hide_empty'   => 0,
                'parent'       => $cat_id,
            );
            $product_categories = get_terms( 'product_cat', $argsCat );
            $count = count($product_categories);
            if ( $count > 0 ){
                foreach ( $product_categories as $cat ) {

                  if( strpos($cat->slug, 'opcao-') !== false ) continue;
                  //if (strpos($cat->slug, 'opcao') !== false) break;

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
                        $lista .= '[wcplpro categories_inc="'.$cat->term_id.'" wcplid="lista-'.$cat->slug.'" posts_inc="'.($produtos_array).'" ]';
                        $lista .= '[/vc_column_text]
                                [/vc_tta_section]';
					    $totalItens++;
                    }
                }
            }
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

            echo do_shortcode( $lista );
            $return = ob_get_contents();
          ob_get_clean();
      return $return;
}
add_shortcode('shortcode_lista_itens', 'shortcode_lista_itens2');
*/






/*
//BUTTON SUPORTE PARA LAVANDERIAS
function shortcode_get_suporte($atts, $content, $tag) {
      $return = '';
      ob_start();

      global $product;
      $cliente = $product->slug;
      $author = get_user_by( 'id', $product->post->post_author );
      echo '<div class="suporte-lavanderia">';
      do_action( 'dokan_after_store_tabs', $author->ID );
      echo '</div>';
      $return = ob_get_contents();
      ob_get_clean();
      return $return;
}
add_shortcode('shortcode_get_suporte', 'shortcode_get_suporte');
*/







/*
// SHORTCODE LISTA ITENS - CATEGORIA
function shortcode_lista($atts, $content, $tag) {
      $return = '';

      ob_start();

      global $product;
      $cliente = $product->slug;
      //dump($cliente);

      $author = get_user_by( 'id', $product->post->post_author );
      $store_info = dokan_get_store_info( $author->ID );

      //$store_user    = get_userdata( get_query_var( 'author' ) );
      //dump($author->ID);
      //exit;
      //$store_info    = dokan_get_store_info( $store_user->ID );
      //$store_tabs    = dokan_get_store_tabs( $store_user->ID );

      $setting_go_vacation = $store_info['setting_go_vacation']; //yes

      $settings_closing_style = $store_info['settings_closing_style']; //datewise

      $settings_close_from = $store_info['settings_close_from'];
      $settings_close_to = $store_info['settings_close_to'];

      $from_date = date( 'Y-m-d', strtotime( $settings_close_from ) );
      $to_date = date( 'Y-m-d', strtotime( $settings_close_to ) );
      $now = date( 'Y-m-d' );

      $retorno = date( 'd/m/Y', strtotime("+1 days",strtotime($settings_close_to))  );

      //dump($from_date);
      //dump($to_date);
      //dump($now);
      if ( $from_date <= $now && $to_date >= $now ) {
          // Date is within beginning and ending time
          //$this->update_product_status( 'publish', 'vacation' );
      } else {
          // Date is not within beginning and ending time
          //$this->update_product_status( 'vacation', 'publish' );
      }

      if ( $setting_go_vacation == 'yes' && ( ( $settings_closing_style == 'datewise' && $from_date <= $now && $to_date >= $now ) || $settings_closing_style == 'instantly' ) ) {

          // Date is within beginning and ending time
          //print 'A data está dentro da hora de início e término';
          //dump($store_info['setting_vacation_message']);
          $lista = '<div class="nenhum-item-lavanderia">';
            if($settings_closing_style == 'datewise') $lista .= ' Estamos de férias, retornamos em: <b>'.$retorno.'</b>';
            else $lista .= ' Estamos de férias, em breve estaremos de volta!';
            $lista .= '<div class="lavanderia-ferias"></div>';
            $lista .= $store_info['setting_vacation_message'];
          $lista .= '</div>';

      }else{

        $lista = '[vc_row][vc_column width="2/3"][vc_custom_heading text="Adicione as peças para enviar a lavanderia" font_container="tag:h2|font_size:27px|text_align:center|color:%2300317c|line_height:34px" google_fonts="font_family:Cabin%3Aregular%2Citalic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic|font_style:400%20italic%3A400%3Aitalic" el_class="title-vantagem sombraTexto2- title-enviar-peca" ][vc_column_text][shortcode_lista_itens][/vc_column_text][/vc_column][vc_column width="1/3" ][vc_custom_heading text="Pedido por peça" font_container="tag:h2|font_size:27px|text_align:center|color:%231e73be" google_fonts="font_family:Cabin%3Aregular%2Citalic%2C500%2C500italic%2C600%2C600italic%2C700%2C700italic|font_style:500%20bold%20italic%3A500%3Aitalic" el_class="title-vantagem sombraTexto2- title-pedido-peca" ][dhvc_woo_product_page_add_to_cart el_class="cart-pedido-lavanderia"][porto_block el_class="carrinho-pedido-lavanderia" name="bloco-carrinho"][/vc_column][/vc_row]';
        //is_sticky="yes" sticky_min_width="767" sticky_top="40" sticky_bottom="20"

      }
      echo do_shortcode( $lista );

      $return = ob_get_contents();
      ob_get_clean();
      return $return;
}
add_shortcode('shortcode_lista', 'shortcode_lista');
*/





/**/
