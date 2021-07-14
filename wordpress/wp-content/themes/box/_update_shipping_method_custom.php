<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_nopriv_update_shipping_method_custom', 'update_shipping_method_custom');
add_action('wp_ajax_update_shipping_method_custom', 'update_shipping_method_custom');
function update_shipping_method_custom() {

    wc_clear_notices();
    WC_Shortcode_Cart::calculate_shipping();
    global $woocommerce, $product, $current_user, $wpdb;

    /*$arquivo = ABSPATH .'/ceps.txt';
    $arquivo = fopen($arquivo, "w");
    $conteudo = "\r\n" . "encontrado1";
    $conteudo .= "\t" ."encontrado2";
    fwrite($arquivo, $conteudo);
    fclose($arquivo);*/

    //dump($_POST,true);


            // SE OFR DENTRO DA LAVANDERIA
            if ( isset($_POST["product_id"]) ) {
              $qty = (isset($_POST['current_qty']) && $_POST['current_qty'] > 0) ? $_POST['current_qty'] : 1;
              if (isset($_POST['variation_id']) && $_POST['variation_id'] != "" && $_POST['variation_id'] > 0) {
                  $cart_item_key = WC()->cart->add_to_cart($_POST["product_id"], $qty, $_POST['variation_id']);
              } else {
                  $cart_item_key = WC()->cart->add_to_cart($_POST["product_id"], $qty);
              }
              $packages = WC()->cart->get_shipping_packages();
              $packages = WC()->shipping->calculate_shipping($packages);
              $available_methods = WC()->shipping->get_packages();
              WC()->cart->remove_cart_item($cart_item_key);
              if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                  /*
                  if( count($available_methods[0]["rates"]) == 1 ){
                      echo '<br/><b>Opção de Coleta:</b> ';
                  }else{
                      echo '<br/><b>Opções de Coleta:</b><br/>';
                  }
                  echo '<ul class="shipping_with_price">';
                  foreach ($available_methods[0]["rates"] as $key => $method) {
                      echo '<li><div class="item-entrega">'.wp_kses_post($method->label).': <b>'.wc_price($method->cost).'</b></div></li>';
                      //$count++;
                  }
                  echo '</ul>';
                  */

                  /*$arquivo = ABSPATH .'/ceps.txt';
                  $arquivo = fopen($arquivo, "w");
                  $conteudo = "\r\n" . "encontrado1";
                  $conteudo .= "\t" ."encontrado2";
                  fwrite($arquivo, $conteudo);
                  fclose($arquivo);*/

                  //ARQUIVO TXT
                  //$arquivo = ABSPATH .'/ceps.txt';
                  //Abri a conexao com o banco de dados
                  //$conexao = mysql_connect(“localhost”, “root”, “”);
                  //mysql_select_db(“bancoteste”);
                  //ABRIR O ARQUIVO TXT
                  //$arquivo = fopen($arquivo, "w");
                  //Faz a consulta no banco de dados
                  //$result = mysql_query(“select * from usuarios”);
                  //while($escrever = mysql_fetch_array($result)){
                   //$conteudo = "\r\n" . $escrever['login'];
                   //$conteudo .= "\t" . $escrever['nome'];
                   //ESCREVE NO ARQUIVO TXT
                   //fwrite($arquivo, $conteudo);
                  //}


                  echo '<br/><span class="msg success">Atendendo nesta região!!<br/>Faça seu pedido Online!</span>';
                  //echo '<br/><span class="msg success">'.count($produtos_array).' Lavanderia(s) encontrada(s) em sua região.<br/>Faça seu pedido Online!!</span>';
                  //echo '<script> window.itens = "'.implode(',',$produtos_array).'";</script>';
                  echo '<script>';
                  //echo 'jQuery(".archive-products, .shop-loop-before, .shop-loop-after").fadeIn("slow");';
                  echo 'jQuery(".shippingmethod_container .info").css("background","rgba(15, 156, 84, 0.79)");';
                  //echo 'MyObject.CalculaDistanciaCep();';
                  echo '</script>';
                  wc_clear_notices();
                  exit;
              }

              /*$arquivo = ABSPATH .'/ceps.txt';
              $arquivo = fopen($arquivo, "w");
              $conteudo = "\r\n" . "n encontrado1";
              $conteudo .= "\t" ."n encontrado2";
              fwrite($arquivo, $conteudo);
              fclose($arquivo);*/


              //echo '<br/><span class="msg error">Ainda não estamos atendendo nesta região, Obrigado!</span>';
              echo '<br/><span class="msg error">';
              echo 'Ainda não estamos atendendo nesta região, em breve novas áreas. Obrigado!<br/>';
              echo '</span>';
              echo '<script>';
              //echo 'jQuery(".archive-products, .shop-loop-before, .shop-loop-after").fadeOut("slow");';
              echo 'jQuery(".shippingmethod_container .info").css("background","rgba(142, 0, 0, 0.58)");';
              //echo 'enderecoLavanderias = [];';
              echo '</script>';
              wc_clear_notices();
              exit;
            }


    //LISTA DE LAVANDERIAS
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
                'terms' => 'lavanderias',
            ),
    );
    $products = new WP_Query( $argsItem );
    $total = $products->post_count;
    if($total>0){
        $produtos_array = array();
        echo '<script></script>';
        echo '<script>';
        //echo 'jQuery(".product").fadeOut("slow");';
        echo 'jQuery(".product").css("opacity","0.5");';
        echo 'enderecoLavanderias = [];';
        echo '</script>';
        while ( $products->have_posts() ){
            $products->the_post();
            $post_id = get_the_ID();
            $productAdd = wc_get_product( $post_id );
            $qty = (isset($_POST['current_qty']) && $_POST['current_qty'] > 0) ? $_POST['current_qty'] : 1;
            if (isset($_POST['variation_id']) && $_POST['variation_id'] != "" && $_POST['variation_id'] > 0) {
                $cart_item_key = WC()->cart->add_to_cart($_POST["product_id"], $qty, $_POST['variation_id']);
            } else {
                $cart_item_key = WC()->cart->add_to_cart($post_id, $qty);
                //$woocommerce->cart->add_to_cart($post_id,$qty);
                //WC_Cart::add_to_cart($post_id, $qty);
                //$product_id = apply_filters( 'wpml_object_id', $post_id, 'product', false, 'pt_BR' );
                //$woocommerce->cart->add_to_cart( $product_id );

            }
            //dump($productAdd,true);

            //WC()->customer->get_shipping_postcode();

            $packages = WC()->cart->get_shipping_packages();
            $packages = WC()->shipping->calculate_shipping($packages);
            $available_methods = WC()->shipping->get_packages();
            WC()->cart->remove_cart_item($cart_item_key);
            if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                array_push($produtos_array,'post-'.$post_id);
                echo '<script>';
                //echo 'jQuery(".post-'.$post_id.'").fadeIn("slow");';
                echo 'jQuery(".post-'.$post_id.'").css("opacity","1");';
                echo 'enderecoLavanderias.push(".post-'.$post_id.'");';
                //echo 'jQuery(".post-'.$post_id.' .product-box").addClass("active-box");';
                echo 'MyObject.mostraLavanderiasCep();';
                echo '</script>';
                //$count = 0;
                //echo '<ul class="shipping_with_price">';
                //echo '<script>console.log("ok");</script>';
                //exit;
                //foreach ($available_methods[0]["rates"] as $key => $method) {
                    //echo "<li>";
                    //echo wp_kses_post($method->label) . "&nbsp;<strong>(" . $method->cost . ")</strong>";
                    //echo "</li>";
                //}
                //echo '</ul>';
            }
        }
        if( count($produtos_array) > 0 ){

          $_SESSION["enderecoLavanderias"] = $produtos_array;


          /*$arquivo = ABSPATH .'/ceps.txt';
          $arquivo = fopen($arquivo, "w");
          $conteudo = "\r\n" . "encontrado1";
          $conteudo .= "\t" ."encontrado2";
          fwrite($arquivo, $conteudo);
          fclose($arquivo);*/


          echo '<br/><span class="msg success">'.count($produtos_array).' Lavanderia(s) encontrada(s) em sua região.';
          echo '<br/><a href="'.home_url('/').'" class="click-radar">Faça seu pedido Online!!</a></span>';
          //echo '<script> window.itens = "'.implode(',',$produtos_array).'";</script>';
          echo '<script>';
          echo 'jQuery(".archive-products, .shop-loop-before1, .shop-loop-after1").fadeIn("slow");';
          echo 'jQuery(".shippingmethod_container .info").css("background","rgba(15, 156, 84, 0.79)");';
          echo 'jQuery(".archive-products").css("margin-top","50px");';
          //echo 'MyObject.CalculaDistanciaCep();';
          echo '</script>';
          /*
          foreach ($produtos_array as $produto) {
            echo $produto.',';
            break;
          }
          */
          wc_clear_notices();
          exit;
        }
    }

    /*$arquivo = ABSPATH .'/ceps.txt';
    $arquivo = fopen($arquivo, "w");
    $conteudo = "\r\n" . "n encontrado1";
    $conteudo .= "\t" ."n encontrado2";
    fwrite($arquivo, $conteudo);
    fclose($arquivo);*/


    unset($_SESSION["enderecoLavanderias"]);
    echo '<br/><span class="msg error">';
    echo 'Ainda não estamos atendendo nesta região!!!<br/>';
    echo 'Crie sua conta, em breve novas lavanderias!<br/>';
    echo 'Entraremos em contato, Obrigado!<br/>';
    echo '</span>';
    echo '<script>';
    echo 'jQuery(".archive-products, .shop-loop-before, .shop-loop-after").fadeOut("slow");';
    echo 'jQuery(".shippingmethod_container .info").css("background","rgba(142, 0, 0, 0.58)");';
    echo 'enderecoLavanderias = [];';
    echo '</script>';

    wc_clear_notices();
    exit;
}
?>
