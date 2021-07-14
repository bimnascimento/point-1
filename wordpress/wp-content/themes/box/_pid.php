<?php
if ( ! defined( 'ABSPATH' ) ) exit;
///*
function pid() {

      //global $current_screen;
      global $post;
      //dump($post);
      ?>

      <script type="text/javascript">
      var home_url = '<?php echo home_url(); ?>';
  	  var post_id, cart_item_key, wpnonce;

      <?php if( isset( $post ) ){
          //$type = $current_screen->post_type;
          //$cart_item_key = $_GET["cart_item_key"];
      ?>
      post_id = '<?php echo $post->ID; ?>';
      <?php } ?>

      <?php if( isset( $_GET["cart_item_key"] ) ){
          $cart_item_key = $_GET["cart_item_key"];
      ?>
      cart_item_key = "<?php echo $cart_item_key; ?>";
      <?php } ?>

      <?php if( isset( $_GET["_wpnonce"] ) ){
          $wpnonce = $_GET["_wpnonce"];
      ?>
          wpnonce = "<?php echo $wpnonce;  ?>";
      <?php } ?>

      var plugins_url = "<?php echo plugins_url(); ?>";
  	  var jc_item = '';
      var excluir_item;

      var ipAtual = "<?php echo do_shortcode('[get_ip]');  ?>";

      var cidadeAtual = "<?php echo do_shortcode('[shortcode_cidade]');  ?>";
      var estadoAtual = "<?php echo do_shortcode('[shortcode_estado]'); ?>";

      var endereco = "<?php echo isset($_SESSION['endereco']) ? $_SESSION['endereco'] : false; ?>";
      endereco = endereco.trim();
      var enderecoCEP = "<?php echo isset($_SESSION['enderecoCEP']) ? $_SESSION['enderecoCEP'] : false; ?>";
      var enderecoBairro = "<?php echo isset($_SESSION['enderecoBairro']) ? $_SESSION['enderecoBairro'] : false; ?>";
      var enderecoCidade = "<?php echo isset($_SESSION['enderecoCidade']) ? $_SESSION['enderecoCidade'] : false; ?>";
      var enderecoEstado = "<?php echo isset($_SESSION['enderecoEstado']) ? $_SESSION['enderecoEstado'] : false; ?>";
      var enderecoDistancia = '';
      if(endereco) enderecoDistancia = endereco + ', ' + enderecoCidade;

      <?php
      //if(is_user_logged_in()){

      //}
      ?>

      </script>

      <?php

      //global $wpdb;
      ///$current_site_id = (int) $wpdb->blogid;
      //dump($current_site_id,true);

}
add_action('wp_head','pid');
//*/


 ?>
