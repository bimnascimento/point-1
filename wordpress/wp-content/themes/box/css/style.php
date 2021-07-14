<?php

add_action('wp_enqueue_scripts', 'box_css', 1001);

// Load CSS
function box_css() {

  //wp_register_style( 'iframe-child', get_stylesheet_directory_uri() . '/js/plugins/jquery.fancybox.min.css' );
  //wp_enqueue_style( 'iframe-child' );

  //wp_register_style( 'extra-child', get_stylesheet_directory_uri() . '/js/plugins/tm-epo.min.css' );
  //wp_enqueue_style( 'extra-child' );

  wp_register_style( 'animation-child', get_stylesheet_directory_uri() . '/css/animation.css' );
  wp_enqueue_style( 'animation-child' );

  //wp_register_style( 'form2-child', get_stylesheet_directory_uri() . '/css/form2.css' );
  //wp_enqueue_style( 'form2-child' );
  wp_register_style( 'form-child', get_stylesheet_directory_uri() . '/css/form.css' );
  wp_enqueue_style( 'form-child' );

  wp_register_style( 'produto-child', get_stylesheet_directory_uri() . '/css/produto.css' );
  wp_enqueue_style( 'produto-child' );

  wp_register_style( 'lavanderia-child', get_stylesheet_directory_uri() . '/css/lavanderia.css' );
  wp_enqueue_style( 'lavanderia-child' );

  wp_register_style( 'login-child', get_stylesheet_directory_uri() . '/css/login_cadastro.css' );
  wp_enqueue_style( 'login-child' );

  wp_register_style( 'checkout2-child', get_stylesheet_directory_uri() . '/css/checkout2.css' );
  wp_enqueue_style( 'checkout2-child' );
  wp_register_style( 'checkout-child', get_stylesheet_directory_uri() . '/css/checkout.css' );
  wp_enqueue_style( 'checkout-child' );

  wp_register_style( 'loja-child', get_stylesheet_directory_uri() . '/css/loja.css' );
  wp_enqueue_style( 'loja-child' );

  wp_register_style( 'conta-child', get_stylesheet_directory_uri() . '/css/conta.css' );
  wp_enqueue_style( 'conta-child' );

  wp_register_style( 'page-child', get_stylesheet_directory_uri() . '/css/page.css' );
  wp_enqueue_style( 'page-child' );

  wp_register_style( 'menu-child', get_stylesheet_directory_uri() . '/css/menu.css' );
  wp_enqueue_style( 'menu-child' );

  // child theme styles

  wp_register_style( 'box-child', get_stylesheet_directory_uri() . '/css/box.css' );
  wp_enqueue_style( 'box-child' );

  //wp_register_style( 'mobile2-child', get_stylesheet_directory_uri() . '/css/mobile2.css' );
  //wp_enqueue_style( 'mobile2-child' );
  wp_register_style( 'mobile-child', get_stylesheet_directory_uri() . '/css/mobile.css' );
  wp_enqueue_style( 'mobile-child' );

  //wp_register_style( 'theme1-child', get_stylesheet_directory_uri() . '/css/theme1.css' );
  //wp_enqueue_style( 'theme1-child' );

  wp_register_style( '320-child', get_stylesheet_directory_uri() . '/css/style_320.css' );
  wp_enqueue_style( '320-child' );

  wp_register_style( '480-child', get_stylesheet_directory_uri() . '/css/style_480.css' );
  wp_enqueue_style( '480-child' );

  wp_register_style( '768-child', get_stylesheet_directory_uri() . '/css/style_768.css' );
  wp_enqueue_style( '768-child' );

  wp_register_style( '1024-child', get_stylesheet_directory_uri() . '/css/style_1024.css' );
  wp_enqueue_style( '1024-child' );

  wp_register_style( '1200-child', get_stylesheet_directory_uri() . '/css/style_1200.css' );
  wp_enqueue_style( '1200-child' );

}
?>
