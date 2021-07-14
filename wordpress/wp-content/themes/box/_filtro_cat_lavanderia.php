<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('pre_get_posts','shop_filter_cat');
function shop_filter_cat($query) {
    if (!is_admin() && is_post_type_archive( 'product' ) && $query->is_main_query()) {
      $query->set('tax_query', array(
                    array ('taxonomy' => 'product_cat',
                                         'field' => 'slug',
                                         'terms' => 'lavanderias'
                                 )
                     )
       );
    }
}

?>
