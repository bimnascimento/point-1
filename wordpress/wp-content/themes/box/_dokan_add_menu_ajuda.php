<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function prefix_dokan_add_seller_nav( $urls ) {
    $urls['ajuda'] = array(
        'title' => __( 'Ajuda', 'dokan'),
        'icon'  => '<i class="fa fa-question-circle"></i>',
        'url'   => home_url('../fale-conosco'),
    );
    return $urls;
}
add_filter( 'dokan_get_dashboard_nav', 'prefix_dokan_add_seller_nav' );

?>
