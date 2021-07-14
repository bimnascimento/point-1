<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function dump( $arg = '', $break = false, $exit = false ) {
    echo '<pre>';
    print_r($arg);
    echo '</pre>';
    if($break) break;
    if($exit) exit;
}

?>
