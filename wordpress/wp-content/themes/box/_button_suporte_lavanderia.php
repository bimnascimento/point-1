<?php
if ( ! defined( 'ABSPATH' ) ) exit;


//BUTTON SUPORTE PARA LAVANDERIAS
function shortcode_get_suporte($atts, $content, $tag) {
      $return = '';
      ob_start();

      global $product, $post, $current_user, $wpdb;
      //$cliente = $product->slug;
      //$author = get_user_by( 'id', $product->post->post_author );
  	  $author = get_the_author_meta('ID');
      echo '<div class="suporte-lavanderia">';
      do_action( 'dokan_after_store_tabs', $author );
      echo '</div>';
      $return = ob_get_contents();
      ob_get_clean();
      return $return;
}
add_shortcode('shortcode_get_suporte', 'shortcode_get_suporte');

?>
