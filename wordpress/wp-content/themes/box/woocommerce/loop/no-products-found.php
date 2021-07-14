<?php
/**
 * Displayed when no products are found matching the current query
 *
 * @version     2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}/*
if(!is_search()){	do_action( 'woocommerce_archive_description' );}*/
?><?php wc_print_notices(); ?>

<script>jQuery(".loading-site").fadeOut(800);</script><div class="woocommerce-info woocommerce-msg">	<?php _e( 'No products were found matching your selection.', 'porto' ); ?></div>