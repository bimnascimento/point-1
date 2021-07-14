<?php
add_action('wp_enqueue_scripts', 'font_css', 1001);
// Load CSS
function font_css() {


  wp_register_style( 'materialdesignicons-child', get_stylesheet_directory_uri() . '/font/material-design-iconic-font.css' );
  wp_enqueue_style( 'materialdesignicons-child' );

  wp_register_style( 'washicons-child', get_stylesheet_directory_uri() . '/font/washicons.css' );
  wp_enqueue_style( 'washicons-child' );

  wp_register_style( 'flaticon-child', get_stylesheet_directory_uri() . '/font/flaticon.css' );
  wp_enqueue_style( 'flaticon-child' );

  wp_register_style( 'font-awesome-animation-child', get_stylesheet_directory_uri() . '/font/font-awesome-animation.min.css' );
  wp_enqueue_style( 'font-awesome-animation-child' );

  wp_register_style( 'font-awesome-child', get_stylesheet_directory_uri() . '/font/font-awesome.min.css' );
  wp_enqueue_style( 'font-awesome-child' );




  wp_register_style( 'bitter-child', get_stylesheet_directory_uri() . '/font/bitter.css' );
  wp_enqueue_style( 'bitter-child' );

  wp_register_style( 'cabin-child', get_stylesheet_directory_uri() . '/font/cabin.css' );
  wp_enqueue_style( 'cabin-child' );

  wp_register_style( 'simple-line-child', get_stylesheet_directory_uri() . '/font/simple-line.css' );
  wp_enqueue_style( 'simple-line-child' );

  wp_register_style( 'web-child', get_stylesheet_directory_uri() . '/font/web.css' );
  wp_enqueue_style( 'web-child' );

  




}
?>
