<?php
add_action('wp_enqueue_scripts', 'plugins_box', 1001);
// Load CSS
function plugins_box() {


  wp_register_style( 'jquery-confirm-child', get_stylesheet_directory_uri() . '/plugins/jquery-confirm.min.css' );
  wp_enqueue_style( 'jquery-confirm-child' );

  //wp_enqueue_script( 'jquery-confirm-child', get_stylesheet_directory_uri() . '/plugins/jquery-confirm.min.js', array( 'jquery' ), null, true );
  wp_enqueue_script( 'jquery-confirm-child', get_stylesheet_directory_uri() . '/plugins/jquery-confirm.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'jquery-confirm-child' );

  wp_enqueue_script( 'maskedinput-child', get_stylesheet_directory_uri() . '/plugins/jquery.maskedinput.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'maskedinput-child' );

  wp_enqueue_script( 'jcarousel-child', get_stylesheet_directory_uri() . '/plugins/jquery.jcarousel.min.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'jcarousel-child' );

  wp_enqueue_script( 'jbox', get_stylesheet_directory_uri() . '/plugins/jbox/jBox.min.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'jbox' );
  wp_enqueue_script( 'jbox-notice', get_stylesheet_directory_uri() . '/plugins/jbox/plugins/Notice/jBox.Notice.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'jbox-notice' );
  wp_register_style( 'jbox-notice', get_stylesheet_directory_uri() . '/plugins/jbox/plugins/Notice/jBox.Notice.css' );
  wp_enqueue_style( 'jbox-notice' );
  wp_register_style( 'jbox', get_stylesheet_directory_uri() . '/plugins/jbox/jBox.css' );
  wp_enqueue_style( 'jbox' );


  //http://maps.google.com/maps/api/js?sensor=false

  //$get_the_url = 'http://maps.google.com/maps/api/js?key=AIzaSyBizveBUTEM2KVKlJnV5hifKmVmfggjkJQ';
  $get_the_url = 'https://maps.google.com/maps/api/js?key=AIzaSyBizveBUTEM2KVKlJnV5hifKmVmfggjkJQ';
  //$get_the_url = get_stylesheet_directory_uri() . '/plugins/googlemaps.js?key=AIzaSyBizveBUTEM2KVKlJnV5hifKmVmfggjkJQ';
  wp_register_script( 'mapa-distancia', $get_the_url, array('jquery'), null, true);
  wp_enqueue_script('mapa-distancia');

  /*
  $test_the_url = @fopen( $get_the_url,'r' );
  //dump($test_the_url);
  if ( $test_the_url !== false ) {
      function load_external_mapa_distancia() {
          wp_register_script( 'mapa-distancia',$get_the_url, array('jquery'), null, true);
          wp_enqueue_script('mapa-distancia');
      }
      add_action('wp_enqueue_scripts', 'load_external_mapa_distancia');
  } else {
      function load_local_mapa_distancia() {
          wp_register_script('mapa-distancia', get_stylesheet_directory_uri() . '/plugins/mapa-distancia.js?sensor=false', array( 'jquery' ), null, true );
          wp_enqueue_script('mapa-distancia');
      }
      add_action('wp_enqueue_scripts', 'load_local_mapa_distancia');
  }
  */

  //wp_register_script( 'mapa-distancia', $get_the_url, array('jquery'), null, true);
  //wp_enqueue_script('mapa-distancia');

  //wp_register_script('mapa-distancia', get_stylesheet_directory_uri() . '/plugins/mapa-distancia.js?sensor=false', array( 'jquery' ), null, true );
  //wp_enqueue_script('mapa-distancia');

  /*
  wp_register_style( 'maxlength-css-child', get_stylesheet_directory_uri() . '/plugins/jquery.maxlength.css' );
  wp_enqueue_style( 'maxlength-css-child' );
  wp_enqueue_script( 'maxlength-plugin-child', get_stylesheet_directory_uri() . '/plugins/jquery.plugin.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'maxlength-plugin-child' );
  wp_enqueue_script( 'maxlength-child', get_stylesheet_directory_uri() . '/plugins/jquery.maxlength.js', array( 'jquery' ), null, true );
  wp_enqueue_media( 'maxlength-child' );
  */

  wp_register_style( 'switch-child', get_stylesheet_directory_uri() . '/plugins/ios7-switch.css' );
  wp_enqueue_style( 'switch-child' );



}
?>
