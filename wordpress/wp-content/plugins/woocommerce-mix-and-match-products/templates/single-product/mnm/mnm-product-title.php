<?php
/**
 * MNM Item Product Title
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly	
}
?>
<label for="mnm-<?php echo WC_MNM_Core_Compatibility::get_id( $mnm_product ); ?>">
	<?php echo ( ! $mnm_product->is_type( 'product_variation' ) && $mnm_product->is_visible() ) || ( $mnm_product->is_type( 'product_variation' ) && $mnm_product->variation_is_visible() ) ? '<a href="' . $mnm_product->get_permalink() . '" target="_blank">' . $mnm_product->get_title() . '</a>' : $mnm_product->get_title(); ?>
</label>