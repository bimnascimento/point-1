<?php
if ( ! defined( 'ABSPATH' ) ) exit;


//RETORNA CIDADE ATUAL
function shortcode_cidade($atts, $content, $tag) {
      $output = '';
      global $wpdb;
      ob_start();

      //TITULO = Lava e Traz - Juiz de Fora - MG - Sua Lavanderia Online!
  	  //GET CIDADE - NOME TITULO[1];
      $current_site_id = (int) $wpdb->blogid;
      $blog_details = get_blog_details($current_site_id);
      $blog_name = $blog_details->blogname;
      $cidade = explode('-',$blog_name);
      $cidade = trim($cidade[1]);
      echo $cidade;

      //if( !isset($_COOKIE['cidadeAtual']) && isset($cidade) ) {
          //setcookie('cidadeAtual', $cidade, strtotime('+30 day'));
      //}

      //*/

      $output = ob_get_contents();
      ob_end_clean();
      return $output;
      exit;

}
add_shortcode('shortcode_cidade', 'shortcode_cidade');


?>
