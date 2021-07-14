<?php
/**
 * @package WPSEO_Local/Deprecated
 *
 * Contains all the deprecated functions.
 */

/**
 * @deprecated Deprecated since version 3.3.1. Uses the new WPSEO_Local_Address_Format class now.
 *
 * @param string $business_address The address of the business.
 * @param bool   $oneline          Whether to show the address on one line or not.
 * @param string $business_zipcode The business zipcode.
 * @param string $business_city    The business city.
 * @param string $business_state   The business state.
 * @param bool   $show_state       Whether to show the state or not.
 * @param bool   $escape_output    Whether to escape the output or not.
 * @param bool   $use_tags         Whether to use HTML tags in the outpput or not.
 *
 * @return string
 */
function wpseo_local_get_address_format( $business_address = '', $oneline = false, $business_zipcode = '', $business_city = '', $business_state = '', $show_state = false, $escape_output = false, $use_tags = true ) {
	$options        = get_option( 'wpseo_local' );
	$address_format = ! empty( $options['address_format'] ) ? $options['address_format'] : 'address-state-postal';

	$format = new WPSEO_Local_Address_Format();
	$output = $format->get_address_format( $address_format, array(
		'business_address' => $business_address,
		'oneline'          => $oneline,
		'business_zipcode' => $business_zipcode,
		'business_city'    => $business_city,
		'business_state'   => $business_state,
		'show_state'       => $show_state,
		'escape_output'    => $escape_output,
		'use_tags'         => $use_tags,
	) );

	return trim( $output );
}
