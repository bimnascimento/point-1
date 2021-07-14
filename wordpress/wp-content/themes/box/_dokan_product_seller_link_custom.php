<?php
if ( ! defined( 'ABSPATH' ) ) exit;


// ADD NOME DA LAVANDERIA NO ITEM
function dokan_product_seller_link_custom() {
    global $product;
    $author     = get_user_by( 'id', $product->post->post_author );
    $store_info = dokan_get_store_info( $author->ID );
    ?>
    <span>
        <?php _e( 'Store Name:', 'dokan' ); ?>
    </span>

    <span class="details">
        <?php printf( '<a href="%s">%s</a>', dokan_get_store_url( $author->ID ), $store_info['store_name'] ); ?>
    </span>
<?php
}
add_action( 'woocommerce_single_product_summary', 'dokan_product_seller_link_custom', 8 );
?>
