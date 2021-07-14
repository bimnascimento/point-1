<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_filter( 'logout_url', 't5_custom_logout_url', 10, 2 );
add_action( 'wp_loaded', 't5_custom_logout_action' );
function t5_custom_logout_url( $logout_url, $redirect ){
    $redirect = 'POINTLAVE';
    $url = add_query_arg( 'logout', 1, home_url( '/' ) );
    if ( ! empty ( $redirect ) )
        $url = add_query_arg( 'redirect', $redirect, $url );
    return $url;
}
function t5_custom_logout_action(){
    if ( ! isset ( $_GET['logout'] ) )
        return;
    wp_logout();
    $loc = isset ( $_GET['redirect'] ) ? $_GET['redirect'] : home_url( '/' );
    //$loc = home_url('/');
    wp_redirect( $loc );
    exit;
}

?>
