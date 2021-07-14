<?php

add_action('wp_enqueue_scripts', 'box_js', 1001);
function box_js() {

    //wp_enqueue_script( 'jcarousel-ajax-child', get_stylesheet_directory_uri() . '/js/jcarousel.ajax.js', array( 'jquery' ), null, true );
    //wp_enqueue_media( 'jcarousel-ajax-child' );
    //wp_localize_script( 'jcarousel-ajax-child', 'my_ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    
    wp_enqueue_script( 'object-child', get_stylesheet_directory_uri() . '/js/object.js', null, null, true );
    wp_enqueue_media( 'object-child' );

    wp_enqueue_script( 'login-child', get_stylesheet_directory_uri() . '/js/login.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'login-child' );

    wp_enqueue_script( 'loja-child', get_stylesheet_directory_uri() . '/js/loja.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'loja-child' );

    wp_enqueue_script( 'shipping-child', get_stylesheet_directory_uri() . '/js/shipping.js', array( 'jquery' ), null, true );
    wp_enqueue_media('shipping-child');

    wp_enqueue_script( 'conta-child', get_stylesheet_directory_uri() . '/js/conta.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'conta-child' );

    wp_enqueue_script( 'page-child', get_stylesheet_directory_uri() . '/js/page.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'page-child' );

    wp_enqueue_script( 'mobile-child', get_stylesheet_directory_uri() . '/js/mobile.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'mobile-child' );

    /*---*/

    wp_enqueue_script( 'script-child', get_stylesheet_directory_uri() . '/js/script.js', array( 'jquery' ), null, true );
    wp_enqueue_media( 'script-child' );

}
