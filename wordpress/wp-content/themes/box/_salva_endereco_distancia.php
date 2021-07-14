<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_ajax_nopriv_salva_endereco_distancia', 'salva_endereco_distancia');
add_action('wp_ajax_salva_endereco_distancia', 'salva_endereco_distancia');
function salva_endereco_distancia() {

      if(!session_id()) {
         @session_start();
      }


      unset($_SESSION["endereco"]);
      unset($_SESSION["enderecoCEP"]);
      unset($_SESSION["enderecoBairro"]);
      unset($_SESSION["enderecoCidade"]);
      unset($_SESSION["enderecoEstado"]);
      unset($_SESSION["enderecoDistancia"]);
      unset($_SESSION["enderecoLavanderias"]);

      //if( isset($_POST['cep']) ) {
              extract( $_POST );
              /*
              [cep] => 36035000
              [tipoDeLogradouro] => Avenida
              [logradouro] => Barão do Rio Branco
              [bairro] => Centro
              [cidade] => Juiz de Fora
              [estado] => MG
              //enderecoDistancia = data.tipoDeLogradouro + ' ' + data.logradouro + ', ' + data.cidade;
              //enderecoCidade = data.cidade;
              */


              //if( $cidade != do_shortcode('[shortcode_cidade]') || $estado != do_shortcode('[shortcode_estado]') )  return;
              if( empty($cep)  || $localidade != do_shortcode('[shortcode_cidade]') || $uf != do_shortcode('[shortcode_estado]')) {
                  echo 'vazio!';
                  exit;
              }

              //$endereco = trim($tipoDeLogradouro.' '.$logradouro);
              $endereco = trim($logradouro);
              $_SESSION["endereco"] = $endereco;
              $_SESSION["enderecoCEP"] = $cep;
              $_SESSION["enderecoBairro"] = $bairro;
              $_SESSION["enderecoCidade"] = $localidade;
              $_SESSION["enderecoEstado"] = $uf;
              $_SESSION["enderecoDistancia"] = $endereco.', '.$bairro.', '.$localidade.' - '.$uf.', '.$cep;
              setcookie('enderecoDistancia',$_SESSION['enderecoDistancia'], strtotime('+1 day'));
              setcookie('enderecoCEP',$_SESSION['enderecoCEP'], strtotime('+1 day'));

              //$arquivo = ABSPATH .'/ceps.txt';
              //$arquivo = fopen($arquivo, "w");
              //$_SESSION["ipAtual"] = get_ip();
              //$conteudo = "\r\n" . get_ip() . ' - ';
              //$conteudo .= "\t" . $_SESSION["enderecoDistancia"];
              //fwrite($arquivo, $conteudo);
              //fclose($arquivo);

              $f = fopen( ABSPATH .'/ceps.txt',"a+",0 );
              $linha = "\r\n".get_ip() . ' - '.$_SESSION["enderecoDistancia"];
              fwrite( $f, $linha, strlen($linha) );
              fclose( $f );

              //if( is_user_logged_in() && $cidade === do_shortcode('[shortcode_cidade]') && $estado === do_shortcode('[shortcode_estado]') ){
              if( is_user_logged_in() ){

                $customer_id = get_current_user_id();

                update_user_meta( $customer_id, 'postcode', sanitize_text_field( $cep ) );
                update_user_meta( $customer_id, 'billing_postcode', sanitize_text_field( $cep ) );
                //update_user_meta( $customer_id, 'shipping_postcode', sanitize_text_field( $cep ) );

                update_user_meta( $customer_id, 'address_1', sanitize_text_field( $endereco ) );
                update_user_meta( $customer_id, 'billing_address_1', sanitize_text_field( $endereco ) );
                //update_user_meta( $customer_id, 'shipping_address_1', sanitize_text_field( $endereco ) );

                //update_user_meta( $customer_id, 'address_2', null );
                //update_user_meta( $customer_id, 'billing_address_2', null );
                //update_user_meta( $customer_id, 'shipping_address_2', sanitize_text_field( '' ) );

                //update_user_meta( $customer_id, 'number', null );
                //update_user_meta( $customer_id, 'billing_number', null );
                //update_user_meta( $customer_id, 'shipping_number', sanitize_text_field( '' ) );


                update_user_meta( $customer_id, 'neighborhood', sanitize_text_field( $bairro ) );
                update_user_meta( $customer_id, 'billing_neighborhood', sanitize_text_field( $bairro ) );
                //update_user_meta( $customer_id, 'shipping_neighborhood', sanitize_text_field( $bairro ) );

                update_user_meta( $customer_id, 'city', sanitize_text_field( $localidade ) );
                update_user_meta( $customer_id, 'billing_city', sanitize_text_field( $localidade ) );
                //update_user_meta( $customer_id, 'shipping_city', sanitize_text_field( $cidade ) );

                update_user_meta( $customer_id, 'state', sanitize_text_field( $uf ) );
                update_user_meta( $customer_id, 'billing_state', sanitize_text_field( $uf ) );
                //update_user_meta( $customer_id, 'shipping_state', sanitize_text_field( $estado ) );

                    //wc_clear_notices();
                    $lavanderia = get_user_meta(  $customer_id , 'dokan_store_name', true );
                    if( !empty($lavanderia) ){

                              $store_info = dokan_get_store_info( $customer_id );

                              $store_info['address']['endereco'] = sanitize_text_field( $endereco );
                              $store_info['address']['city'] = sanitize_text_field( $localidade );
                              $store_info['address']['zip'] = sanitize_text_field( $cep );
                              $store_info['address']['state'] = sanitize_text_field( $uf );
                              $store_info['address']['bairro'] = sanitize_text_field( $bairro );

                              $dokan_settings['address'] = $store_info['address'];

                              update_user_meta( $customer_id, 'dokan_profile_settings', $dokan_settings );

                    }

              }

              echo 'SessaoEndereco: '.$cep.' - '.$localidade.' - '.$endereco;


      //}else{
        //echo 'VAZIO';
      //}
      exit;
}

?>
