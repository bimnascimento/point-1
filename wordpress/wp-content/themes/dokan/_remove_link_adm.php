<?php

// DOKAN - REMOVE LINK MENU ADM
function remove_link_adm( $urls ) {
    unset( $urls['reviews'] );
    return $urls;
}
add_filter( 'dokan_get_dashboard_nav', 'remove_link_adm' );
?>
