<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//VALIDAR FORM CADASTRO - NEW USER
add_action( 'woocommerce_register_post', '_new_post',0, 3 );
add_filter( 'woocommerce_process_registration_errors', '_new_post',0,3);
add_filter( 'registration_errors', '_new_post',0,3);
add_filter( 'woocommerce_registration_errors' , '_new_post',0,3);
function _new_post($reg_errors, $sanitized_user_login, $user_email ) {

            //dump('_new_post',true);
            //dump($_POST,true);

            $cidadeAtual = do_shortcode('[shortcode_cidade]');
            $estadoAtual = do_shortcode('[shortcode_estado]');

            if( isset($_POST['account_first_name']) ) $_POST['fname'] = $_POST['account_first_name'];
            if( isset($_POST['account_last_name']) ) $_POST['lname'] = $_POST['account_last_name'];

            //$_POST['postcode'] = isset($_POST['cidade_cep']) ? $_POST['cidade_cep'] : '';



    return $reg_errors;

}


//VALIDAR FORM CADASTRO - NEW USER
add_action( 'woocommerce_register_post', '_new_user_errors',100, 3 );
add_filter( 'woocommerce_process_registration_errors', '_new_user_errors',100,3);
add_filter( 'registration_errors', '_new_user_errors',100,3);
add_filter( 'woocommerce_registration_errors' , '_new_user_errors',100,3);
function _new_user_errors($reg_errors, $sanitized_user_login, $user_email ) {

  //global $error;
  //$error = new WP_Error();

  //dump('registration_errors_validation_custom');
  //dump($login);
  //dump($email);
  //dump($errors);
  //dump($_POST);
  //exit;

  extract( $_POST );
	If ( strcmp( $password, $confirm_password ) !== 0 ) {
			return new WP_Error( 'registration-error', __( 'Por favor, corrija suas senhas são diferentes.', 'porto' ) );
	}


  //https://docs.woocommerce.com/document/tutorial-customising-checkout-fields-using-actions-and-filters/

  //TITULO = Lava e Traz - Juiz de Fora - Sua Lavanderia Online!
  //GET NOME DA CIDADE - NOME TITULO[1];
  /*
  global $wpdb;
  $current_site_id = (int) $wpdb->blogid;
  $blog_details = get_blog_details($current_site_id);
  $blog_name = $blog_details->blogname;
  $cidade = explode('-',$blog_name);
  $cidade = $cidade[1];
  //dump($cidade);
  */
  $cidadeAtual = do_shortcode('[shortcode_cidade]');
  $estadoAtual = do_shortcode('[shortcode_estado]');
  //dump($cidadeAtual);
  //exit;

  //unset($_SESSION["enderecoDistancia"]);
  //unset($_SESSION["enderecoCidade"]);
  //unset($_SESSION["enderecoEstado"]);

  $_POST['postcode'] = $_POST['account_cep'];
  //$_POST['postcode'] = isset($_SESSION['enderecoCEP']) ? $_SESSION['enderecoCEP'] : $_POST['account_cep'];

  /*
  dump( $_POST['postcode'] );
  dump( $_SESSION["enderecoCidade"] );
  dump( $_SESSION["enderecoEstado"] );
  dump( $cidadeAtual );
  dump( $estadoAtual );
  exit;
  */

  if( !isset($_POST["cidade_cep"]) || !isset($_POST["estado_cep"]) ){
      $_POST['postcode'] = '';
      $_POST['account_cep'] = '';
      return new WP_Error( 'registration-error', 'Por favor, preencha o CEP.');
  }

  if( $_POST["cidade_cep"] != $cidadeAtual || $_POST["estado_cep"] != $estadoAtual ){
      $_POST['postcode'] = '';
      $_POST['account_cep'] = '';
      return new WP_Error( 'registration-error', 'Por favor, informe um CEP de '.$cidadeAtual.' - '.$estadoAtual.'.' );
  }

  //exit;

  if( isset($_POST['account_first_name']) ) $_POST['fname'] = $_POST['account_first_name'];
  if( isset($_POST['account_last_name']) ) $_POST['lname'] = $_POST['account_last_name'];

  $_POST['address_1'] = $_POST["endereco_cep"];
  $_POST['neighborhood'] = $_POST["bairro_cep"];
  $_POST['city'] = isset($_POST['cidade_cep']) ? $_POST['cidade_cep'] : $cidadeAtual;
  $_POST['state'] = isset($_POST['estado_cep']) ? $_POST['estado_cep'] : $estadoAtual;

  $_POST['shopurl'] = $_POST['shopurl'];

  $_POST['billing_first_name'] = $_POST['account_first_name'];
  $_POST['billing_last_name'] = $_POST['account_last_name'];
  $_POST['billing_email'] = $_POST['email'];
  $_POST['billing_phone'] = $_POST['account_telefone'];
  $_POST['billing_postcode'] = $_POST['postcode'];

  $_POST['shipping_first_name'] = $_POST['account_first_name'];
  $_POST['shipping_last_name'] = $_POST['account_last_name'];
  $_POST['shipping_postcode'] = $_POST['postcode'];

  //$_POST['account_username'] = $_POST['password'];
  $_POST['account_password'] = $_POST['password'];
  $_POST['account_password-2'] = $_POST['confirm_password'];

  $_POST['first_name'] = $_POST['account_first_name'];
  $_POST['last_name'] = $_POST['account_last_name'];

  //$enderecoCidade = isset($_SESSION["enderecoCidade"]) ? $_SESSION["enderecoCidade"] : 'null';
  //dump($enderecoCidade);

  //dump($sanitized_user_login);
  //dump($user_email);

  //exit;
  if ( isset( $_POST['shopurl'] ) && !empty( $_POST['shopurl'] ) ) {
  	    $tag = get_term_by( 'slug', $_POST['shopurl'], 'product_tag');
      	if ( !empty($tag) ) {
      	     return new WP_Error( 'registration-error', __( 'Por favor, escolha outra URL para sua lavanderia.', 'porto' ) );
      	}
  }


  return $reg_errors;
}
?>
