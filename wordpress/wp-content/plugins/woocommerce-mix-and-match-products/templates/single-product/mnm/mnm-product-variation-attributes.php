<?php
/**
 * MNM Item Variation Attributes
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ){
	exit; // Exit if accessed directly
}

echo WC_MNM_Core_Compatibility::wc_get_formatted_variation( $mnm_product );
