<?php

/**
 * Get a sellers remaining product count
 *
 * @param  int $user_id
 * @return int
 */
function dps_user_remaining_product( $user_id ) {
    
    $dps = Dokan_Product_Subscription::init();
    
    $remaining_product = (int) get_user_meta( $user_id, 'product_no_with_pack', true ) - $dps->get_number_of_product_by_seller( get_current_user_id() );

    return $remaining_product;
}