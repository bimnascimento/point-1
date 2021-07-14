<?php

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'wp_cleanfix_get_base_classname' ) ) {
  function wp_cleanfix_get_base_classname( $className )
  {
    $parts  = explode( '\\', $className );
    $result = array_pop( $parts );

    return $result;
  }
}