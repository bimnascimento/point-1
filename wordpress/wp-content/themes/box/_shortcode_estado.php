<?php
if ( ! defined( 'ABSPATH' ) ) exit;

//RETORNA ESTADO ATUAL
function shortcode_estado($atts, $content, $tag) {
      $output = '';
      global $wpdb;
      ob_start();

      //TITULO = Lava e Traz - Juiz de Fora - MG - Sua Lavanderia Online!
      //GET ESATDO - NOME TITULO[1];
      $current_site_id = (int) $wpdb->blogid;
      $blog_details = get_blog_details($current_site_id);
      $blog_name = $blog_details->blogname;
      $estado = explode('-',$blog_name);
      $estado = trim($estado[2]);
      echo $estado;
      //*/

      $output = ob_get_contents();
      ob_end_clean();
      return trim($output);
}
add_shortcode('shortcode_estado', 'shortcode_estado');

?>
