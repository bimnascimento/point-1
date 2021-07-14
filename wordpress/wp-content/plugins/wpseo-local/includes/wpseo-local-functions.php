<?php
/**
 * @package WPSEO_Local\Frontend
 */

/**
 * Address shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_address( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'                 => '',
		'hide_name'          => false,
		'hide_address'       => false,
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_vat'           => false,
		'show_tax'           => false,
		'show_coc'           => false,
		'show_price_range'   => false,
		'show_logo'          => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'comment'            => '',
		'from_sl'            => false,
		'from_widget'        => false,
		'widget_title'       => '',
		'before_title'       => '',
		'after_title'        => '',
		'echo'               => false,
	), $atts, 'wpseo_local_show_address' ) );

	$options = get_option( 'wpseo_local' );
	if ( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		$atts['show_opening_hours'] = false;
	}

	$is_postal_address = false;

	if ( wpseo_has_multiple_locations() ) {
		// Don't show anything if you don't have permission for this location.
		if ( 'publish' != get_post_status( $atts['id'] ) && ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() ) {
				$atts['id'] = get_queried_object_id();
			}

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) ) {
				return '';
			}
		}
		else if ( $atts['id'] == '' ) {
			return is_singular() ? __( 'Please provide a post ID if you want to show an address outside a Locations singular page', 'yoast-local-seo' ) : '';
		}

		// Get the location data if its already been entered.
		$business_name          = get_the_title( $atts['id'] );
		$business_type          = get_post_meta( $atts['id'], '_wpseo_business_type', true );
		$business_address       = get_post_meta( $atts['id'], '_wpseo_business_address', true );
		$business_address_2     = get_post_meta( $atts['id'], '_wpseo_business_address_2', true );
		$business_city          = get_post_meta( $atts['id'], '_wpseo_business_city', true );
		$business_state         = get_post_meta( $atts['id'], '_wpseo_business_state', true );
		$business_zipcode       = get_post_meta( $atts['id'], '_wpseo_business_zipcode', true );
		$business_country       = get_post_meta( $atts['id'], '_wpseo_business_country', true );
		$business_phone         = get_post_meta( $atts['id'], '_wpseo_business_phone', true );
		$business_phone_2nd     = get_post_meta( $atts['id'], '_wpseo_business_phone_2nd', true );
		$business_fax           = get_post_meta( $atts['id'], '_wpseo_business_fax', true );
		$business_email         = get_post_meta( $atts['id'], '_wpseo_business_email', true );
		$business_vat           = get_post_meta( $atts['id'], '_wpseo_business_vat_id', true );
		$business_tax           = get_post_meta( $atts['id'], '_wpseo_business_tax_id', true );
		$business_coc           = get_post_meta( $atts['id'], '_wpseo_business_coc_id', true );
		$business_price_range   = get_post_meta( $atts['id'], '_wpseo_business_price_range', true );
		$business_url           = get_post_meta( $atts['id'], '_wpseo_business_url', true );
		$business_location_logo = get_post_meta( $atts['id'], '_wpseo_business_location_logo', true );
		$is_postal_address      = get_post_meta( $atts['id'], '_wpseo_is_postal_address', true );
		$is_postal_address      = $is_postal_address == '1';

		if ( empty( $business_url ) ) {
			$business_url = get_permalink( $atts['id'] );
		}
	}
	else {
		$business_name        = isset( $options['location_name'] ) ? $options['location_name'] : '';
		$business_type        = isset( $options['business_type'] ) ? $options['business_type'] : '';
		$business_address     = isset( $options['location_address'] ) ? $options['location_address'] : '';
		$business_address_2   = isset( $options['location_address'] ) ? $options['location_address_2'] : '';
		$business_city        = isset( $options['location_city'] ) ? $options['location_city'] : '';
		$business_state       = isset( $options['location_state'] ) ? $options['location_state'] : '';
		$business_zipcode     = isset( $options['location_zipcode'] ) ? $options['location_zipcode'] : '';
		$business_country     = isset( $options['location_country'] ) ? $options['location_country'] : '';
		$business_phone       = isset( $options['location_phone'] ) ? $options['location_phone'] : '';
		$business_phone_2nd   = isset( $options['location_phone_2nd'] ) ? $options['location_phone_2nd'] : '';
		$business_fax         = isset( $options['location_fax'] ) ? $options['location_fax'] : '';
		$business_email       = isset( $options['location_email'] ) ? $options['location_email'] : '';
		$business_url         = ! empty( $options['location_url'] ) ? $options['location_url'] : WPSEO_Sitemaps_Router::get_base_url( '' );
		$business_vat         = isset( $options['location_vat_id'] ) ? $options['location_vat_id'] : '';
		$business_tax         = isset( $options['location_tax_id'] ) ? $options['location_tax_id'] : '';
		$business_coc         = isset( $options['location_coc_id'] ) ? $options['location_coc_id'] : '';
		$business_price_range = isset( $options['location_price_range'] ) ? $options['location_price_range'] : '';
	}

	if ( '' == $business_type ) {
		$business_type = 'LocalBusiness';
	}

	/*
	* This array can be used in a filter to change the order and the labels of contact details
	*/
	$business_contact_details = array(
		array(
			'key'   => 'phone',
			'label' => __( 'Phone', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'phone_2',
			'label' => __( 'Secondary phone', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'fax',
			'label' => __( 'Fax', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'email',
			'label' => __( 'Email', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'url',
			'label' => __( 'URL', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'vat',
			'label' => __( 'VAT ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'tax',
			'label' => __( 'Tax ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'coc',
			'label' => __( 'Chamber of Commerce ID', 'yoast-local-seo' ),
		),
		array(
			'key'   => 'price_range',
			'label' => __( 'Price range', 'yoast-local-seo' ),
		),
	);

	$business_contact_details = apply_filters( 'wpseo_local_contact_details', $business_contact_details );

	$tag_title_open  = '';
	$tag_title_close = '';
	if ( ! $atts['oneline'] ) {
		if ( ! $atts['from_widget'] ) {
			$tag_name        = apply_filters( 'wpseo_local_location_title_tag_name', 'h3' );
			$tag_title_open  = '<' . esc_html( $tag_name ) . '>';
			$tag_title_close = '</' . esc_html( $tag_name ) . '>';
		}
		else if ( $atts['from_widget'] && $atts['widget_title'] == '' ) {
			$tag_title_open  = $atts['before_title'];
			$tag_title_close = $atts['after_title'];
		}
	}

	$output = '<div id="wpseo_location-' . esc_attr( $atts['id'] ) . '" class="wpseo-location" itemscope itemtype="http://schema.org/' . ( ( $is_postal_address ) ? 'PostalAddress' : esc_attr( $business_type ) ) . '">';

	// Show URL as hidden meta, when URL is not visible, since it's required by Schema.org markup.
	if ( false === $atts['show_url'] ) {
		$output .= '<meta itemprop="url" content="' . esc_url( $business_url ) . '">';
	}

	// Add featured image as image itemprop.
	if ( wpseo_has_multiple_locations() ) {
		if ( true === has_post_thumbnail( $atts['id'] ) ) {
			$business_image = esc_url( get_the_post_thumbnail_url( $atts['id'] ) );
		}
	}

	if ( ! isset( $business_image_output ) && isset( $options['business_image'] ) ) {
		$business_image = wp_get_attachment_image_url( $options['business_image'], 'full' );
	}

	if ( isset( $business_image ) ) {
		$output .= '<meta itemprop="image" content="' . $business_image . '">';
	}

	if ( false == $atts['hide_name'] ) {
		$output .= $tag_title_open . ( ( $atts['from_sl'] ) ? '<a href="' . esc_url( $business_url ) . '">' : '' ) . '<span class="wpseo-business-name" itemprop="name">' . esc_html( $business_name ) . '</span>' . ( ( $atts['from_sl'] ) ? '</a>' : '' ) . $tag_title_close;
	}

	if ( $atts['show_logo'] ) {
		if ( empty( $business_location_logo ) ) {
			$wpseo_options          = get_option( 'wpseo' );
			$business_location_logo = $wpseo_options['company_logo'];
		}

		if ( ! empty( $business_location_logo ) ) {
			$output .= '<figure itemprop="logo" itemscope itemtype="http://schema.org/ImageObject">';
			$output .= '<img itemprop="url" src="' . $business_location_logo . '" alt="' . esc_attr( get_post_meta( yoast_wpseo_local_get_attachment_id_from_src( $business_location_logo ), '_wp_attachment_image_alt', true ) ) . '"/>';
			$output .= '</figure>';
		}
	}

	$output .= '<' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . ' ' . ( ( $is_postal_address ) ? '' : 'itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"' ) . ' class="wpseo-address-wrapper">';

	// Output city/state/zipcode in right format.
	$address_format        = ! empty( $options['address_format'] ) ? $options['address_format'] : 'address-state-postal';
	$format                = new WPSEO_Local_Address_Format();
	$address_format_output = $format->get_address_format( $address_format, array(
		'show_logo'          => ! empty( $business_location_logo ) ? true : false,
		'business_address'   => $business_address,
		'business_address_2' => $business_address_2,
		'oneline'            => $atts['oneline'],
		'business_zipcode'   => $business_zipcode,
		'business_city'      => $business_city,
		'business_state'     => $business_state,
		'show_state'         => $atts['show_state'],
		'escape_output'      => false,
		'use_tags'           => true,
	) );

	if ( ! empty( $address_format_output ) && false === $atts['hide_address'] ) {
		$output .= $address_format_output;
	}


	if ( $atts['show_country'] && ! empty( $business_country ) ) {
		$output .= ( $atts['oneline'] ) ? ', ' : ' ';
	}

	if ( $atts['show_country'] && ! empty( $business_country ) ) {
		$output .= '<' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '  class="country-name" itemprop="addressCountry">' . WPSEO_Local_Frontend::get_country( $business_country ) . '</' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '>';
	}
	$output .= '</' . ( ( $atts['oneline'] ) ? 'span' : 'div' ) . '>';

	$details_output = '';
	foreach ( $business_contact_details as $order => $details ) {

		if ( 'phone' == $details['key'] && $atts['show_phone'] && ! empty( $business_phone ) ) {
			/* translators: %s extends to the label for phone */
			$details_output .= sprintf( '<span class="wpseo-phone">%s: <a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $business_phone ) ) . '" class="tel"><span itemprop="telephone">' . esc_html( $business_phone ) . '</span></a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'phone_2' == $details['key'] && $atts['show_phone_2'] && ! empty( $business_phone_2nd ) ) {
			/* translators: %s extends to the label for 2nd phone */
			$details_output .= sprintf( '<span class="wpseo-phone2nd">%s: <a href="' . esc_url( 'tel:' . preg_replace( '/[^0-9+]/', '', $business_phone_2nd ) ) . '" class="tel">' . esc_html( $business_phone_2nd ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'fax' == $details['key'] && $atts['show_fax'] && ! empty( $business_fax ) ) {
			/* translators: %s extends to the label for fax */
			$details_output .= sprintf( '<span class="wpseo-fax">%s: <span class="tel" itemprop="faxNumber">' . esc_html( $business_fax ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'email' == $details['key'] && $atts['show_email'] && ! empty( $business_email ) ) {
			/* translators: %s extends to the label for e-mail */
			$details_output .= sprintf( '<span class="wpseo-email">%s: <a href="' . esc_url( 'mailto:' . $business_email ) . '" itemprop="email">' . esc_html( $business_email ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'url' == $details['key'] && $atts['show_url'] ) {
			/* translators: %s extends to the label for business url */
			$details_output .= sprintf( '<span class="wpseo-url">%s: <a href="' . esc_url( $business_url ) . '" itemprop="url">' . esc_html( $business_url ) . '</a></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'vat' == $details['key'] && $atts['show_vat'] && ! empty( $business_vat ) ) {
			/* translators: %s extends to the label for businss VAT number */
			$details_output .= sprintf( '<span class="wpseo-vat">%s: <span itemprop="vatID">' . esc_html( $business_vat ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'tax' == $details['key'] && $atts['show_tax'] && ! empty( $business_tax ) ) {
			/* translators: %s extends to the label for business tax number */
			$details_output .= sprintf( '<span class="wpseo-tax">%s: <span itemprop="taxID">' . esc_html( $business_tax ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'coc' == $details['key'] && $atts['show_coc'] && ! empty( $business_coc ) ) {
			/* translators: %s extends to the label for business COC number*/
			$details_output .= sprintf( '<span class="wpseo-vat">%s: ' . esc_html( $business_coc ) . '</span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
		if ( 'price_range' == $details['key'] && $atts['show_price_range'] && ! empty( $business_price_range ) ) {
			/* translators: %s extends to the label for business Price Range */
			$details_output .= sprintf( '<span class="wpseo-price-range">%s: <span itemprop="priceRange">' . esc_html( $business_price_range ) . '</span></span>' . ( ( $atts['oneline'] ) ? ' ' : '<br/>' ), esc_html( $details['label'] ) );
		}
	}

	if ( '' != $details_output && true == $atts['oneline'] ) {
		$output .= ' - ';
	}

	$output .= $details_output;

	if ( $atts['show_opening_hours'] ) {
		$args = array(
			'id'          => ( wpseo_has_multiple_locations() ) ? $atts['id'] : '',
			'hide_closed' => $atts['hide_closed'],
		);
		$output .= '<br/>' . wpseo_local_show_opening_hours( $args, true, false ) . '<br/>';
	}
	$output .= '</div>';

	if ( $atts['comment'] != '' ) {
		$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
	}

	if ( $atts['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Shortcode for shoing all locations at once. May come handy for "office overview" pages
 *
 * @since 1.1.7
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_all_locations( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'number'             => -1,
		'term_id'            => '',
		'orderby'            => 'menu_order title',
		'order'              => 'ASC',
		'show_state'         => true,
		'show_country'       => true,
		'show_phone'         => true,
		'show_phone_2'       => true,
		'show_fax'           => true,
		'show_email'         => true,
		'show_url'           => false,
		'show_opening_hours' => false,
		'hide_closed'        => false,
		'oneline'            => false,
		'echo'               => false,
		'comment'            => '',
	), $atts, 'wpseo_local_show_all_locations' ) );

	// Don't show any data when post_type is not activated. This function/shortcode makes no sense for single location.
	if ( ! wpseo_has_multiple_locations() ) {
		return '';
	}

	$output    = '';
	$tax_query = array();

	if ( '' != $atts['term_id'] ) {
		$tax_query[] = array(
			'taxonomy' => 'wpseo_locations_category',
			'field'    => 'term_id',
			'terms'    => $atts['term_id'],
		);
	}

	$locations = new WP_Query( array(
		'post_type'      => 'wpseo_locations',
		'posts_per_page' => $atts['number'],
		'orderby'        => $atts['orderby'],
		'order'          => $atts['order'],
		'fields'         => 'ids',
		'tax_query'      => $tax_query,
	) );

	if ( $locations->post_count > 0 ) :
		$output .= '<div class="wpseo-all-locations">';
		foreach ( $locations->posts as $location_id ) :

			$location = apply_filters( 'wpseo_all_locations_location', wpseo_local_show_address( array(
				'id'                 => $location_id,
				'show_state'         => $atts['show_state'],
				'show_country'       => $atts['show_country'],
				'show_phone'         => $atts['show_phone'],
				'show_phone_2'       => $atts['show_phone_2'],
				'show_fax'           => $atts['show_fax'],
				'show_email'         => $atts['show_email'],
				'show_url'           => $atts['show_url'],
				'show_opening_hours' => $atts['show_opening_hours'],
				'hide_closed'        => $atts['hide_closed'],
				'oneline'            => $atts['oneline'],
				'echo'               => false,
			) ) );

			$output .= $location;

		endforeach;

		if ( $atts['comment'] != '' ) {
			$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
		}

		$output .= '</div>';

	else :
		echo '<p>' . __( 'There are no locations to show.', 'yoast-local-seo' ) . '</p>';
	endif;

	if ( $atts['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Maps shortcode handler
 *
 * @since 0.1
 *
 * @param array $atts Array of shortcode parameters.
 *
 * @return string
 */
function wpseo_local_show_map( $atts ) {
	global $map_counter, $wpseo_enqueue_geocoder, $wpseo_map;

	$options   = get_option( 'wpseo_local' );
	$tax_query = array();

	// Backwards compatibility for scrollable / zoomable functions.
	if ( is_array( $atts ) && ! array_key_exists( 'zoomable', $atts ) ) {
		$atts['zoomable'] = ( isset( $atts['scrollable'] ) ) ? $atts['scrollable'] : true;
	}

	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'                      => '',
		'term_id'                 => '',
		'center'                  => '',
		'width'                   => 400,
		'height'                  => 300,
		'zoom'                    => -1,
		'show_route'              => true,
		'show_state'              => true,
		'show_country'            => false,
		'show_url'                => false,
		'show_email'              => false,
		'default_show_infowindow' => false,
		'map_style'               => ( isset( $options['map_view_style'] ) ) ? $options['map_view_style'] : 'ROADMAP',
		'scrollable'              => true,
		'draggable'               => true,
		'marker_clustering'       => false,
		'show_route_label'        => ( isset( $options['show_route_label'] ) && ! empty( $options['show_route_label'] ) ) ? $options['show_route_label'] : __( 'Show route', 'yoast-local-seo' ),
		'from_sl'                 => false,
		'show_category_filter'    => false,
		'echo'                    => false,
	), $atts, 'wpseo_local_show_map' ) );

	if ( ! isset( $map_counter ) ) {
		$map_counter = 0;
	}
	else {
		$map_counter++;
	}

	// Check if zoom is set to true or false by the wpseo_check_falses function. If so, turn them back into 0 or 1.
	if ( true === $atts['zoom'] ) {
		$atts['zoom'] = 1;
	}
	else if ( false === $atts['zoom'] ) {
		$atts['zoom'] = 0;
	}

	$location_array     = $lats = $longs = array();
	$location_array_str = '';

	$default_custom_marker = '';
	$all_categories        = array();

	if ( isset( $options['custom_marker'] ) && intval( $options['custom_marker'] ) && empty( $default_custom_marker ) ) {
		$default_custom_marker = wp_get_attachment_url( $options['custom_marker'] );
	}

	if ( ! wpseo_has_multiple_locations() ) {
		$atts['id'] = '';

		$location_array[] = array(
			'location_name'      => ( isset( $options['location_name'] ) ) ? $options['location_name'] : '',
			'location_url'       => WPSEO_Sitemaps_Router::get_base_url( '' ),
			'location_email'     => ( isset( $options['location_email'] ) ) ? $options['location_email'] : '',
			'location_address'   => ( isset( $options['location_address'] ) ) ? $options['location_address'] : '',
			'location_address_2' => ( isset( $options['location_address_2'] ) ) ? $options['location_address_2'] : '',
			'location_city'      => ( isset( $options['location_city'] ) ) ? $options['location_city'] : '',
			'location_state'     => ( isset( $options['location_state'] ) ) ? $options['location_state'] : '',
			'location_zipcode'   => ( isset( $options['location_zipcode'] ) ) ? $options['location_zipcode'] : '',
			'location_country'   => ( isset( $options['location_country'] ) ) ? $options['location_country'] : '',
			'location_phone'     => ( isset( $options['location_phone'] ) ) ? $options['location_phone'] : '',
			'location_phone_2nd' => ( isset( $options['location_phone_2nd'] ) ) ? $options['location_phone_2nd'] : '',
			'coordinates_lat'    => ( isset( $options['location_coords_lat'] ) ) ? $options['location_coords_lat'] : '',
			'coordinates_long'   => ( isset( $options['location_coords_long'] ) ) ? $options['location_coords_long'] : '',
			'custom_marker'      => $default_custom_marker,
			'categories'         => array(),
		);

	}
	else {
		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() ) {
				$atts['id'] = get_queried_object_id();
			}

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) ) {
				return '';
			}
		}
		else if ( $atts['id'] != 'all' && empty( $atts['id'] ) ) {
			return ( true == is_singular( 'wpseo_locations' ) ) ? __( 'Please provide a post ID when using this shortcode outside a Locations singular page', 'yoast-local-seo' ) : '';
		}

		// Define tax_query when term_id is selected.
		if ( $atts['id'] == 'all' && $atts['term_id'] != '' ) {
			$tax_query[] = array(
				'taxonomy' => 'wpseo_locations_category',
				'field'    => 'term_id',
				'terms'    => $atts['term_id'],
			);
		}

		$location_ids = explode( ',', $atts['id'] );
		if ( $atts['id'] == 'all' || ( $atts['id'] != 'all' && count( $location_ids ) > 1 ) ) {
			$args = array(
				'post_type'      => 'wpseo_locations',
				'posts_per_page' => ( $atts['id'] == 'all' ) ? -1 : count( $location_ids ),
				'post_status'    => ( is_user_logged_in() && current_user_can( 'edit_posts' ) ? 'any' : 'publish' ),
				'fields'         => 'ids',
				'meta_query'     => array(
					array(
						'key'     => '_wpseo_business_address',
						'value'   => '',
						'compare' => '!=',
					),
				),
				'tax_query'      => $tax_query,
			);

			if ( count( $location_ids ) > 1 ) {
				$args['post__in'] = $location_ids;
			}

			$location_ids = get_posts( $args );
		}

		foreach ( $location_ids as $location_id ) {
			$custom_marker       = wpseo_local_get_custom_marker( $location_id, 'wpseo_locations_category' );
			$location_categories = array();

			// Put all categories in an array, to be passed on to the map later on and for the categories filter.
			$categories = get_the_terms( $location_id, 'wpseo_locations_category' );
			if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
				foreach ( $categories as $category ) {
					$all_categories[ $category->slug ] = $category->name;
					$location_categories[]             = $category->slug;
				}
			}

			$tmp_array = array(
				'location_id'        => $location_id,
				'location_name'      => get_the_title( $location_id ),
				'location_url'       => get_post_meta( $location_id, '_wpseo_business_url', true ),
				'location_email'     => get_post_meta( $location_id, '_wpseo_business_email', true ),
				'location_address'   => get_post_meta( $location_id, '_wpseo_business_address', true ),
				'location_address_2' => get_post_meta( $location_id, '_wpseo_business_address_2', true ),
				'location_city'      => get_post_meta( $location_id, '_wpseo_business_city', true ),
				'location_state'     => get_post_meta( $location_id, '_wpseo_business_state', true ),
				'location_zipcode'   => get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
				'location_country'   => get_post_meta( $location_id, '_wpseo_business_country', true ),
				'location_phone'     => get_post_meta( $location_id, '_wpseo_business_phone', true ),
				'location_phone_2nd' => get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
				'coordinates_lat'    => get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
				'coordinates_long'   => get_post_meta( $location_id, '_wpseo_coordinates_long', true ),
				'custom_marker'      => ( $custom_marker != '' ) ? esc_url( $custom_marker ) : esc_url( $default_custom_marker ),
				'categories'         => $location_categories,
			);

			if ( empty( $tmp_array['location_url'] ) ) {
				$tmp_array['location_url'] = get_permalink( $location_id );
			}

			$location_array[] = $tmp_array;
		}
	}

	// Convert possible comma's in the lat/long to points.
	foreach ( $location_array as $key => $location ) {
		$location_array[ $key ]['coordinates_lat']  = str_replace( ',', '.', $location['coordinates_lat'] );
		$location_array[ $key ]['coordinates_long'] = str_replace( ',', '.', $location['coordinates_long'] );
	}

	$noscript_output = '<ul>';
	foreach ( $location_array as $key => $location ) {

		if ( $location['coordinates_lat'] != '' && $location['coordinates_long'] != '' ) {
			$full_address = wpseo_local_show_address( array(
				'id'                 => $location['location_id'],
				'hide_name'          => true,
				'business_address'   => wpseo_cleanup_string( $location['location_address'] ),
				'business_address_2' => wpseo_cleanup_string( $location['location_address_2'] ),
				'oneline'            => false,
				'business_zipcode'   => $location['location_zipcode'],
				'business_city'      => $location['location_city'],
				'business_state'     => $location['location_state'],
				'show_state'         => $atts['show_state'],
				'show_country'       => false,
				'show_phone'         => false,
				'escape_output'      => true,
				'use_tags'           => true,
			) );

			$location_array_str .= "location_data.push( {
				'name': '" . wpseo_cleanup_string( $location['location_name'] ) . "',
				'url': '" . wpseo_cleanup_string( $location['location_url'] ) . "',
				'address': '" . $full_address . "',
				'country': '" . WPSEO_Local_Frontend::get_country( $location['location_country'] ) . "',
				'show_country': " . ( ( $atts['show_country'] ) ? 'true' : 'false' ) . ",
				'url': '" . esc_url( $location['location_url'] ) . "',
				'show_url': " . ( ( $atts['show_url'] ) ? 'true' : 'false' ) . ",
				'email': '" . $location['location_email'] . "',
				'show_email': " . ( ( $atts['show_email'] ) ? 'true' : 'false' ) . ",
				'phone': '" . wpseo_cleanup_string( $location['location_phone'] ) . "',
				'phone_2nd': '" . wpseo_cleanup_string( $location['location_phone_2nd'] ) . "',
				'lat': " . wpseo_cleanup_string( $location['coordinates_lat'] ) . ",
				'long': " . wpseo_cleanup_string( $location['coordinates_long'] ) . ",
				'custom_marker': '" . wpseo_cleanup_string( $location['custom_marker'] ) . "',
				'categories': '" . join( ', ', $location['categories'] ) . "', 
			} );\n";
		}

		$noscript_output .= '<li>';
		if ( $location['location_url'] != get_permalink() ) {
			$noscript_output .= '<a href="' . $location['location_url'] . '">';
		}
		$noscript_output .= $location['location_name'];
		if ( $location['location_url'] != get_permalink() ) {
			$noscript_output .= '</a>';
		}
		$noscript_output .= '</li>';
		$noscript_output .= '<li><a href="mailto:' . $location['location_email'] . '">' . $location['location_email'] . '</a></li>';

		$full_address = $location['location_address'] . ', ' . $location['location_city'] . ( ( strtolower( $location['location_country'] ) == 'us' ) ? ', ' . $location['location_state'] : '' ) . ', ' . $location['location_zipcode'] . ', ' . WPSEO_Local_Frontend::get_country( $location['location_country'] );

		$location_array[ $key ]['full_address'] = $full_address;

		$lats[]  = $location['coordinates_lat'];
		$longs[] = $location['coordinates_long'];
	}
	$noscript_output .= '</ul>';

	$map                    = '';
	$wpseo_enqueue_geocoder = true;

	if ( ! is_array( $lats ) || empty( $lats ) || ! is_array( $longs ) || empty( $longs ) ) {
		return;
	}

	if ( $atts['center'] === '' ) {
		$center_lat  = ( min( $lats ) + ( ( max( $lats ) - min( $lats ) ) / 2 ) );
		$center_long = ( min( $longs ) + ( ( max( $longs ) - min( $longs ) ) / 2 ) );
	}
	else {
		$center_lat  = get_post_meta( $atts['center'], '_wpseo_coordinates_lat', true );
		$center_long = get_post_meta( $atts['center'], '_wpseo_coordinates_long', true );
	}

	// Default to zoom 10 if there's only one location as a center + bounds would zoom in far too much.
	if ( -1 == $atts['zoom'] && 1 === count( $location_array ) ) {
		$atts['zoom'] = 10;
	}

	if ( $location_array_str != '' ) {

		$wpseo_map .= '<script type="text/javascript">
			var map_' . $map_counter . ';
			var directionsDisplay_' . $map_counter . ';

			function wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '() {
				var location_data = new Array();' . PHP_EOL . $location_array_str . '
				map_' . $map_counter . ' = wpseo_show_map( location_data, ' . $map_counter . ', ' . $center_lat . ', ' . $center_long . ', ' . $atts['zoom'] . ', "' . $atts['map_style'] . '", "' . $atts['scrollable'] . '", "' . $atts['draggable'] . '", "' . $atts['default_show_infowindow'] . '", "' . is_admin() . '", "' . $atts['marker_clustering'] . '" );
				directionsDisplay_' . $map_counter . ' = wpseo_get_directions(map_' . $map_counter . ', location_data, ' . $map_counter . ', "' . $atts['show_route'] . '");
			}

			if( window.addEventListener )
				window.addEventListener( "load", wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . ', false );
			else if(window.attachEvent )
				window.attachEvent( "onload", wpseo_map_init' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . ');
		</script>' . PHP_EOL;

		// Override(reset) the setting for images inside the map.
		$map .= '<div id="map_canvas' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" class="wpseo-map-canvas" style="max-width: 100%; width: ' . $atts['width'] . 'px; height: ' . $atts['height'] . 'px;">' . $noscript_output . '</div>';

		$route_tag = apply_filters( 'wpseo_local_location_route_title_name', 'h3' );

		if ( $atts['show_route'] && ( ( $atts['id'] != 'all' && strpos( $atts['id'], ',' ) === false ) || $atts['from_sl'] ) ) {
			$map .= '<div id="wpseo-directions-wrapper"' . ( ( $atts['from_sl'] ) ? ' style="display: none;"' : '' ) . '>';
			$map .= '<' . esc_html( $route_tag ) . ' id="wpseo-directions" class="wpseo-directions-heading">' . __( 'Route', 'yoast-local-seo' ) . '</' . esc_html( $route_tag ) . '>';
			$map .= '<form action="" method="post" class="wpseo-directions-form" id="wpseo-directions-form' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" onsubmit="wpseo_calculate_route( map_' . $map_counter . ', directionsDisplay_' . $map_counter . ', ' . $location_array[0]['coordinates_lat'] . ', ' . $location_array[0]['coordinates_long'] . ', ' . $map_counter . '); return false;">';
			$map .= '<p>';
			$map .= __( 'Your location', 'yoast-local-seo' ) . ': <input type="text" size="20" id="origin' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '" value="' . ( ! empty( $_REQUEST['wpseo-sl-search'] ) ? esc_attr( $_REQUEST['wpseo-sl-search'] ) : '' ) . '" />';
			$map .= '<input type="submit" class="wpseo-directions-submit" value="' . $atts['show_route_label'] . '">';
			$map .= '<span id="wpseo-noroute" style="display: none;">' . __( 'No route could be calculated.', 'yoast-local-seo' ) . '</span>';
			$map .= '</p>';
			$map .= '</form>';
			$map .= '<div id="directions' . ( ( $map_counter != 0 ) ? '_' . $map_counter : '' ) . '"></div>';
			$map .= '</div>';
		}

		// Show the filter if categories are set, there's more than 1 and if the filter is enabled.
		if ( isset( $all_categories ) && count( $all_categories ) > 1 && $atts['show_category_filter'] ) {
			$map .= '<select id="filter-by-location-category-' . $map_counter . '" class="location-category-filter" onchange="filterMarkers(this.value, ' . $map_counter . ')">';
			$map .= '<option value="">' . __( 'All categories', 'yoast-local-seo' ) . '</option>';
			foreach ( $all_categories as $category_slug => $category_name ) {
				$map .= '<option value="' . $category_slug . '">' . $category_name . '</option>';
			}
			$map .= '</select>';
		}
	}

	if ( $atts['echo'] ) {
		echo $map;
	}

	return $map;
}

/**
 * Opening hours shortcode handler, for not breaking backwards compatibility
 *
 * @param  array $atts Array of shortcode attributes.
 *
 * @return string
 */
function wpseo_local_show_openinghours_shortcode_cb( $atts ) {
	return wpseo_local_show_opening_hours( $atts );
}

/**
 * Function for displaying opening hours
 *
 * @since 0.1
 *
 * @param array $atts        Array of shortcode parameters.
 * @param bool  $show_schema choose to show schema.org HTML or not.
 * @param bool  $standalone  Whether the opening hours are used stand alone or part of another function (like address).
 *
 * @return string
 */
function wpseo_local_show_opening_hours( $atts, $show_schema = true, $standalone = true ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id'          => '',
		'hide_closed' => false,
		'echo'        => false,
		'comment'     => '',
		'show_days'   => array(),
	), $atts, 'wpseo_local_opening_hours' ) );

	$options = get_option( 'wpseo_local' );
	if ( isset( $options['hide_opening_hours'] ) && $options['hide_opening_hours'] == 'on' ) {
		return false;
	}

	if ( wpseo_has_multiple_locations() ) {
		// Don't show anything if you don't have permission for this location.
		if ( 'publish' != get_post_status( $atts['id'] ) && ! current_user_can( 'edit_posts' ) ) {
			return '';
		}

		if ( get_post_type() == 'wpseo_locations' ) {
			if ( ( $atts['id'] == '' || $atts['id'] == 'current' ) && ! is_post_type_archive() ) {
				$atts['id'] = get_queried_object_id();
			}

			if ( is_post_type_archive() && ( $atts['id'] == '' || $atts['id'] == 'current' ) ) {
				return '';
			}
		}
	}
	else {
		$atts['id'] = '';
	}


	// Output meta tags with required address information when using this as stand alone.
	$business_type = $business_name = $business_address = $business_phone = null;
	if ( true == $standalone ) {
		if ( true == wpseo_has_multiple_locations() ) {
			$business_name    = get_the_title( $atts['id'] );
			$business_type    = get_post_meta( $atts['id'], '_wpseo_business_type', true );
			$business_address = get_post_meta( $atts['id'], '_wpseo_business_address', true );
			$business_phone   = get_post_meta( $atts['id'], '_wpseo_business_phone', true );
		}
		else {
			$business_name    = isset( $options['location_name'] ) ? $options['location_name'] : '';
			$business_type    = isset( $options['business_type'] ) ? $options['business_type'] : '';
			$business_address = isset( $options['business_address'] ) ? $options['business_address'] : '';
			$business_phone   = isset( $options['business_phone'] ) ? $options['business_phone'] : '';
		}

		if ( '' == $business_type ) {
			$business_type = 'LocalBusiness';
		}
	}

	$output = '';
	// Output meta tags with required address information when using this as stand alone.
	if ( true == $standalone ) {
		$output .= '<div class="wpseo-opening-hours-wrapper" itemscope itemtype="http://schema.org/' . esc_attr( $business_type ) . '">';
		$output .= '<meta itemprop="name" content="' . esc_attr( $business_name ) . '">';
		$output .= '<meta itemprop="address" content="' . esc_attr( $business_address ) . '">';
		$output .= '<meta itemprop="telephone" content="' . esc_attr( $business_phone ) . '">';

		// Add featured image as image itemprop.
		if ( wpseo_has_multiple_locations() ) {
			if ( true === has_post_thumbnail( $atts['id'] ) ) {
				$business_image = esc_url( get_the_post_thumbnail_url( $atts['id'] ) );
			}
		}

		if ( ! isset( $business_image_output ) ) {
			$business_image = wp_get_attachment_image_url( $options['business_image'], 'full' );
		}

		if ( isset( $business_image ) ) {
			$output .= '<meta itemprop="image" content="' . $business_image . '">';
		}
	}
	$output .= '<table class="wpseo-opening-hours">';

	// Make the array itterable (Is that a word?).
	$days = new ArrayIterator( array(
		'sunday'    => __( 'Sunday', 'yoast-local-seo' ),
		'monday'    => __( 'Monday', 'yoast-local-seo' ),
		'tuesday'   => __( 'Tuesday', 'yoast-local-seo' ),
		'wednesday' => __( 'Wednesday', 'yoast-local-seo' ),
		'thursday'  => __( 'Thursday', 'yoast-local-seo' ),
		'friday'    => __( 'Friday', 'yoast-local-seo' ),
		'saturday'  => __( 'Saturday', 'yoast-local-seo' ),
	) );

	// Make sure it can be looped infinite times.
	$days = new InfiniteIterator( $days );
	$days = new LimitIterator( $days, get_option( 'start_of_week' ), 7 );

	if ( ! is_array( $atts['show_days'] ) ) {
		$show_days = explode( ',', $atts['show_days'] );
	}
	else {
		$show_days = (array) $atts['show_days'];
	}

	// Loop through the days array where start_of_week is the first key, with a max of 7.
	foreach ( $days as $key => $day ) {

		// Check if the opening hours for this day should be shown.
		if ( is_array( $show_days ) && ! empty( $show_days ) && ! in_array( $key, $show_days ) ) {
			continue;
		}

		$multiple_opening_hours = isset( $options['multiple_opening_hours'] ) && $options['multiple_opening_hours'] == 'on';
		$day_abbr               = ucfirst( substr( $key, 0, 2 ) );

		if ( wpseo_has_multiple_locations() ) {
			$field_name        = '_wpseo_opening_hours_' . $key;
			$value_from        = get_post_meta( $atts['id'], $field_name . '_from', true );
			$value_to          = get_post_meta( $atts['id'], $field_name . '_to', true );
			$value_second_from = get_post_meta( $atts['id'], $field_name . '_second_from', true );
			$value_second_to   = get_post_meta( $atts['id'], $field_name . '_second_to', true );

			$multiple_opening_hours = get_post_meta( $atts['id'], '_wpseo_multiple_opening_hours', true );
			$multiple_opening_hours = ! empty( $multiple_opening_hours );
		}
		else {
			$field_name        = 'opening_hours_' . $key;
			$value_from        = isset( $options[ $field_name . '_from' ] ) ? esc_attr( $options[ $field_name . '_from' ] ) : '';
			$value_to          = isset( $options[ $field_name . '_to' ] ) ? esc_attr( $options[ $field_name . '_to' ] ) : '';
			$value_second_from = isset( $options[ $field_name . '_second_from' ] ) ? esc_attr( $options[ $field_name . '_second_from' ] ) : '';
			$value_second_to   = isset( $options[ $field_name . '_second_to' ] ) ? esc_attr( $options[ $field_name . '_second_to' ] ) : '';
		}

		if ( ( $value_from == 'closed' || $value_to == 'closed' ) && $atts['hide_closed'] ) {
			continue;
		}

		$value_from_formatted        = $value_from;
		$value_to_formatted          = $value_to;
		$value_second_from_formatted = $value_second_from;
		$value_second_to_formatted   = $value_second_to;

		if ( ! isset( $options['opening_hours_24h'] ) || $options['opening_hours_24h'] != 'on' ) {
			$value_from_formatted        = date( 'g:i A', strtotime( $value_from ) );
			$value_to_formatted          = date( 'g:i A', strtotime( $value_to ) );
			$value_second_from_formatted = date( 'g:i A', strtotime( $value_second_from ) );
			$value_second_to_formatted   = date( 'g:i A', strtotime( $value_second_to ) );
		}

		$output .= '<tr>';
		$output .= '<td class="day">' . $day . '</td>';
		$output .= '<td class="time">';

		$output_time = '';
		if ( $value_from != 'closed' && $value_to != 'closed' ) {
			$output_time .= '<time ' . ( ( $show_schema ) ? 'itemprop="openingHours"' : '' ) . ' datetime="' . $day_abbr . ' ' . $value_from . '-' . $value_to . '" content="' . $day_abbr . ' ' . $value_from . '-' . $value_to . '">' . $value_from_formatted . ' - ' . $value_to_formatted . '</time>';
		}
		else {
			$output_time .= __( 'Closed', 'yoast-local-seo' );
		}

		if ( $multiple_opening_hours ) {
			if ( $value_from != 'closed' && $value_to != 'closed' && $value_second_from != 'closed' && $value_second_to != 'closed' ) {
				$output_time .= '<span class="openingHoursAnd"> ' . __( 'and', 'yoast-local-seo' ) . ' </span> ';
				$output_time .= '<time ' . ( ( $show_schema ) ? 'itemprop="openingHours"' : '' ) . ' datetime="' . $day_abbr . ' ' . $value_second_from . '-' . $value_second_to . '" content="' . $day_abbr . ' ' . $value_second_from . '-' . $value_second_to . '">' . $value_second_from_formatted . ' - ' . $value_second_to_formatted . '</time>';
			}
			else {
				$output_time .= '';
			}
		}

		$output_time = apply_filters( 'wpseo_opening_hours_time', $output_time, $day, $value_from, $value_to, $atts );
		$output .= $output_time;
		$output .= '</td>';
		$output .= '</tr>';
	}

	$output .= '</table>';

	if ( true == $standalone ) {
		$output .= '</div>'; // .wpseo-opening-hours-wrapper
	}

	if ( $atts['comment'] != '' ) {
		$output .= '<div class="wpseo-extra-comment">' . wpautop( html_entity_decode( $atts['comment'] ) ) . '</div>';
	}

	if ( $atts['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Get the location details
 *
 * @param string $location_id Optional. Only use this when multiple locations are enabled in the website.
 *
 * @return array|bool Array with location details.
 */
function wpseo_get_location_details( $location_id = '' ) {
	$options          = get_option( 'wpseo_local' );
	$location_details = array();

	if ( wpseo_has_multiple_locations() && $location_id == '' ) {
		return false;
	}
	else if ( wpseo_has_multiple_locations() ) {
		if ( $location_id == null ) {
			return false;
		}

		$location_details = array(
			'business_address'     => get_post_meta( $location_id, '_wpseo_business_address', true ),
			'business_city'        => get_post_meta( $location_id, '_wpseo_business_city', true ),
			'business_state'       => get_post_meta( $location_id, '_wpseo_business_state', true ),
			'business_zipcode'     => get_post_meta( $location_id, '_wpseo_business_zipcode', true ),
			'business_country'     => get_post_meta( $location_id, '_wpseo_business_country', true ),
			'business_phone'       => get_post_meta( $location_id, '_wpseo_business_phone', true ),
			'business_phone_2nd'   => get_post_meta( $location_id, '_wpseo_business_phone_2nd', true ),
			'business_coords_lat'  => get_post_meta( $location_id, '_wpseo_coordinates_lat', true ),
			'business_coords_long' => get_post_meta( $location_id, '_wpseo_coordinates_long', true ),
		);
	}
	else if ( wpseo_has_multiple_locations() ) {
		$location_details = array(
			'business_address'     => $options['location_address'],
			'business_city'        => $options['location_city'],
			'business_state'       => $options['location_state'],
			'business_zipcode'     => $options['location_zipcode'],
			'business_country'     => $options['location_country'],
			'business_phone'       => $options['location_phone'],
			'business_phone_2nd'   => isset( $options['location_phone_2nd'] ) ? $options['location_phone_2nd'] : '',
			'business_coords_lat'  => $options['location_coords_lat'],
			'business_coords_long' => $options['location_coords_long'],
		);
	}

	return $location_details;
}

/**
 * Checks whether website uses multiple location (Custom Post Type) or not (info from options)
 *
 * @return bool Multiple locations enbaled or not
 */
function wpseo_has_multiple_locations() {
	$options = get_option( 'wpseo_local' );

	return isset( $options['use_multiple_locations'] ) && $options['use_multiple_locations'] == 'on';
}

/**
 * Check whether the usage of current location for Map routes is allowed.
 *
 * @return bool Is allowed to use current location or not.
 */
function wpseo_may_use_current_location() {
	$options = get_option( 'wpseo_local' );

	return isset( $options['detect_location'] ) && $options['detect_location'] == 'on';
}

/**
 * @param bool $use_24h True if time should be displayed in 24 hours. False if time should be displayed in AM/PM mode.
 * @param int  $default default time for dropdown.
 *
 * @return string Complete dropdown with all options.
 */
function wpseo_show_hour_options( $use_24h = false, $default = 9 ) {
	$output = '<option value="closed">' . __( 'Closed', 'yoast-local-seo' ) . '</option>';

	for ( $i = 0; $i < 24; $i++ ) {
		$time                = strtotime( sprintf( '%1$02d', $i ) . ':00' );
		$time_quarter        = strtotime( sprintf( '%1$02d', $i ) . ':15' );
		$time_half           = strtotime( sprintf( '%1$02d', $i ) . ':30' );
		$time_threequarters  = strtotime( sprintf( '%1$02d', $i ) . ':45' );
		$value               = date( 'H:i', $time );
		$value_quarter       = date( 'H:i', $time_quarter );
		$value_half          = date( 'H:i', $time_half );
		$value_threequarters = date( 'H:i', $time_threequarters );

		$time_value               = date( 'g:i A', $time );
		$time_quarter_value       = date( 'g:i A', $time_quarter );
		$time_half_value          = date( 'g:i A', $time_half );
		$time_threequarters_value = date( 'g:i A', $time_threequarters );

		if ( $use_24h ) {
			$time_value               = date( 'H:i', $time );
			$time_quarter_value       = date( 'H:i', $time_quarter );
			$time_half_value          = date( 'H:i', $time_half );
			$time_threequarters_value = date( 'H:i', $time_threequarters );
		}

		$output .= '<option value="' . $value . '"' . selected( $value, $default, false ) . '>' . $time_value . '</option>';
		$output .= '<option value="' . $value_quarter . '" ' . selected( $value_quarter, $default, false ) . '>' . $time_quarter_value . '</option>';
		$output .= '<option value="' . $value_half . '" ' . selected( $value_half, $default, false ) . '>' . $time_half_value . '</option>';
		$output .= '<option value="' . $value_threequarters . '" ' . selected( $value_threequarters, $default, false ) . '>' . $time_threequarters_value . '</option>';
	}

	return $output;
}

/**
 * Geocode the given address.
 *
 * @param string $address The address that needs to be geocoded.
 *
 * @return array|WP_Error
 */
function wpseo_geocode_address( $address ) {
	$geocode_url = 'https://maps.google.com/maps/api/geocode/json?address=' . urlencode( $address ) . '&oe=utf8&sensor=false';
	$api_key     = yoast_wpseo_local_get_api_key_server();
	if ( ! empty( $api_key ) ) {
		$geocode_url .= '&key=' . $api_key;
	}

	$response = wp_remote_get( $geocode_url );

	if ( is_wp_error( $response ) || $response['response']['code'] != 200 || empty( $response['body'] ) ) {
		return new WP_Error( 'wpseo-no-response', "Didn't receive a response from Maps API" );
	}

	$response_body = json_decode( $response['body'] );

	if ( 'OK' != $response_body->status ) {
		$error_code = 'wpseo-zero-results';
		if ( $response_body->status == 'OVER_QUERY_LIMIT' ) {
			$error_code = 'wpseo-query-limit';
		}

		return new WP_Error( $error_code, $response_body->status );
	}

	return $response_body;
}

/**
 * Checks whether array keys are meant to mean false but aren't set to false.
 *
 * @param array $atts Array to check.
 *
 * @return array
 */
function wpseo_check_falses( $atts ) {
	if ( ! is_array( $atts ) ) {
		return $atts;
	}

	foreach ( $atts as $key => $value ) {
		if ( $value === 'false' || $value === 'off' || $value === 'no' || $value === '0' ) {
			$atts[ $key ] = false;
		}
		else if ( $value === 'true' || $value === 'on' || $value === 'yes' || $value === '1' ) {
			$atts[ $key ] = true;
		}
	}

	return $atts;
}

// Set the global to false, if the script is needed, the global will be set to true.
$wpseo_enqueue_geocoder = false;
/**
 * Places scripts in footer for Google Maps use.
 */
function wpseo_enqueue_geocoder() {
	global $wpseo_enqueue_geocoder, $wpseo_map;

	if ( is_admin() && 'wpseo_locations' == get_post_type() ) {
		global $wpseo_enqueue_geocoder;

		$wpseo_enqueue_geocoder = true;
	}

	if ( $wpseo_enqueue_geocoder ) {
		$options         = get_option( 'wpseo_local' );
		$detect_location = isset( $options['detect_location'] ) && $options['detect_location'] == 'on';
		$default_country = isset( $options['default_country'] ) ? $options['default_country'] : '';
		if ( '' != $default_country ) {
			$default_country = WPSEO_Local_Frontend::get_country( $default_country );
		}

		// Load frontend scripts.
		$script_name = 'wp-seo-local-frontend-430.min.js';
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$script_name = 'wp-seo-local-frontend-430.js';
		}
		wp_enqueue_script( 'wpseo-local-frontend', plugins_url( 'js/' . $script_name, dirname( __FILE__ ) ), '', WPSEO_LOCAL_VERSION, true );

		wp_localize_script( 'wpseo-local-frontend', 'wpseo_local_data', array(
			'ajaxurl'                   => 'admin-ajax.php',
			'adminurl'                  => admin_url(),
			'has_multiple_locations'    => wpseo_has_multiple_locations(),
			'unit_system'               => ! empty( $options['unit_system'] ) ? $options['unit_system'] : 'METRIC',
			'default_country'           => $default_country,
			'detect_location'           => $detect_location,
			'marker_cluster_image_path' => apply_filters( 'wpseo_local_marker_cluster_image_path', esc_url( trailingslashit( plugin_dir_url( dirname( __FILE__ ) ) ) . 'images/m' ) ),
		) );

		// Load Maps API script.
		$locale = get_locale();
		$locale = explode( '_', $locale );

		// Check if it might be a language spoken in more than one country.
		if ( isset( $locale[1] ) && in_array( $locale[0], array(
				'en',
				'de',
				'es',
				'it',
				'pt',
				'ro',
				'ru',
				'sv',
				'nl',
				'zh',
				'fr',
			) )
		) {
			$language = $locale[0] . '-' . $locale[1];
		}
		else if ( isset( $locale[1] ) ) {
			$language = $locale[1];
		}
		else {
			$language = $locale[0];
		}

		// Build Google Maps embedding URL.
		$google_maps_url = '//maps.google.com/maps/api/js';
		$api_key_browser = yoast_wpseo_local_get_api_key_browser();
		if ( ! empty( $api_key_browser ) ) {
			$google_maps_url = add_query_arg( array(
				'key' => $api_key_browser,
			), $google_maps_url );
		}

		if ( ! empty( $language ) ) {
			$google_maps_url = add_query_arg( array(
				'language' => esc_attr( strtolower( $language ) ),
			), $google_maps_url );
		}

		wp_enqueue_script( 'maps-geocoder', $google_maps_url, array(), null, true );

		echo '<style type="text/css">.wpseo-map-canvas img { max-width: none !important; }</style>' . PHP_EOL;
	}

	echo $wpseo_map;
}

/**
 * This function will clean up the given string and remove all unwanted characters.
 *
 * @param string $string String that has to be cleaned.
 *
 * @uses wpseo_utf8_to_unicode() to convert string to array of unicode characters.
 * @uses wpseo_unicode_to_utf8() to convert the unicode array back to a regular string.
 * @return string The clean string.
 */
function wpseo_cleanup_string( $string ) {
	$string = esc_attr( $string );

	// First generate array of all unicodes of this string.
	$unicode_array = wpseo_utf8_to_unicode( $string );
	foreach ( $unicode_array as $key => $unicode_item ) {
		// Remove unwanted unicode characters.
		if ( in_array( $unicode_item, array( 8232 ) ) ) {
			unset( $unicode_array[ $key ] );
		}
	}

	// Revert back to normal string.
	$string = wpseo_unicode_to_utf8( $unicode_array );

	return $string;
}

/**
 * Converts a string to array of unicode characters.
 *
 * @param string $str String that has to be converted to unicde array.
 *
 * @return array Array of unicode characters.
 */
function wpseo_utf8_to_unicode( $str ) {
	$unicode     = array();
	$values      = array();
	$looking_for = 1;

	for ( $i = 0; $i < strlen( $str ); $i++ ) {
		$this_value = ord( $str[ $i ] );

		if ( $this_value < 128 ) {
			$unicode[] = $this_value;
		}
		else {
			if ( count( $values ) == 0 ) {
				$looking_for = ( $this_value < 224 ) ? 2 : 3;
			}

			$values[] = $this_value;
			if ( count( $values ) == $looking_for ) {
				$number = ( $looking_for == 3 ) ? ( ( ( $values[0] % 16 ) * 4096 ) + ( ( $values[1] % 64 ) * 64 ) + ( $values[2] % 64 ) ) : ( ( ( $values[0] % 32 ) * 64 ) + ( $values[1] % 64 ) );

				$unicode[]   = $number;
				$values      = array();
				$looking_for = 1;
			}
		}
	}

	return $unicode;
}

/**
 * Converts unicode character array back to regular string.
 *
 * @param array $string_array Array of unicode characters.
 *
 * @return string Converted string.
 */
function wpseo_unicode_to_utf8( $string_array ) {
	$utf8 = '';

	foreach ( $string_array as $unicode ) {
		if ( $unicode < 128 ) {
			$utf8 .= chr( $unicode );
		}
		elseif ( $unicode < 2048 ) {
			$utf8 .= chr( 192 + ( ( $unicode - ( $unicode % 64 ) ) / 64 ) );
			$utf8 .= chr( 128 + ( $unicode % 64 ) );
		}
		else {
			$utf8 .= chr( 224 + ( ( $unicode - ( $unicode % 4096 ) ) / 4096 ) );
			$utf8 .= chr( 128 + ( ( ( $unicode % 4096 ) - ( $unicode % 64 ) ) / 64 ) );
			$utf8 .= chr( 128 + ( $unicode % 64 ) );
		}
	}

	return $utf8;
}


/**
 * Run the upgrade procedures.
 *
 * @param string $db_version Version from database to check with.
 */
function wpseo_local_do_upgrade( $db_version ) {

	if ( version_compare( $db_version, '1.3.1', '<' ) ) {
		$options = get_option( 'wpseo_local' );

		if ( ! is_array( $options ) ) {
			return;
		}

		// Convert checkbox values from "1" to "on".
		foreach ( $options as $key => $value ) {
			if ( ! in_array( $key, array(
				'use_multiple_locations',
				'opening_hours_24h',
				'multiple_opening_hours',
			) )
			) {
				continue;
			}

			if ( $value == '1' ) {
				$options[ $key ] = 'on';
			}
		}
	}

	if ( version_compare( $db_version, '3.4', '<=' ) ) {
		// Update businesstypes from Attorneys to LegalServices if upgrading from version 3.4 or below.
		yoast_wpseo_local_update_business_type();
	}
}

/**
 * Retrieves excerpt from specific post.
 *
 * @param int $post_id The post ID of which the excerpt should be retrieved.
 *
 * @return string
 */
function wpseo_local_get_excerpt( $post_id ) {
	global $post;

	$original_post = $post;
	$post          = get_post( $post_id );
	setup_postdata( $post );

	$output = get_the_excerpt();

	// Set back original $post;.
	$post = $original_post;
	wp_reset_postdata();

	return $output;
}

/**
 * Create an upload field for an image
 */
function wpseo_local_upload_image() {

	$output = '';

	$output = '<p class="desc label" style="border:none; margin-bottom: 0;">' . __( 'If you want the map to display a custom marker pin for your locations, please upload it here.', 'yoast-local-seo' ) . '</p>';

	$output .= '<label for="upload_image">';
	$output .= '<input id="upload_image" type="text" size="36" name="ad_image" value="http://" /> ';
	$output .= '<input id="upload_image_button" class="button" type="button" value="Upload Image" />';
	$output .= '<br />Enter a URL or upload an image';
	$output .= '</label>';
	$output .= '<br class="clear"/>';

	return $output;
}


/**
 * Get the custom marker from categories or general Local SEO settings.
 *
 * @param int    $post_id  The post id.
 * @param string $taxonomy String The taxonomy name from the location category.
 *
 * @return false|string
 */
function wpseo_local_get_custom_marker( $post_id = null, $taxonomy = '' ) {

	$custom_marker = '';

	if ( ! empty( $post_id ) && ! empty( $taxonomy ) ) {
		if ( '' != get_post_meta( $post_id, '_wpseo_business_location_custom_marker', true ) ) {
			$custom_marker = get_post_meta( $post_id, '_wpseo_business_location_custom_marker', true );
		}
		else {
			$terms = get_the_terms( $post_id, $taxonomy );
			if ( false !== $terms ) {
				$terms = wp_list_pluck( $terms, 'term_id' );
			}
			$terms = apply_filters( 'wpseo_local_custom_marker_order', $terms );

			if ( class_exists( 'WPSEO_Primary_Term' ) ) {
				// Check if there's a primary term.
				$primary_term = new WPSEO_Primary_Term( $taxonomy, $post_id );
				$primary_term = $primary_term->get_primary_term();

				if ( ! empty( $primary_term ) ) {
					// If there is a primary term, replace the term array with the primary term.
					$terms = array( $primary_term );
				}
			}

			if ( ! empty( $terms ) ) {
				foreach ( $terms as $term_id ) {
					$tax_meta = WPSEO_Taxonomy_Meta::get_term_meta( (int) $term_id, $taxonomy );

					if ( isset( $tax_meta['wpseo_local_custom_marker'] ) && ! empty( $tax_meta['wpseo_local_custom_marker'] ) ) {
						$custom_marker = wp_get_attachment_url( $tax_meta['wpseo_local_custom_marker'] );
					}

					break;
				}
			}
		}
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['custom_marker'] ) && intval( $options['custom_marker'] ) ) {
			$custom_marker = wp_get_attachment_url( $options['custom_marker'] );
		}
	}

	return $custom_marker;
}

/**
 * @param string $value The value of the Business types array.
 * @param string $key   The key of the Business types array.
 */
function wpseo_local_sanitize_business_types( &$value, &$key ) {
	$value = str_replace( '&mdash;', '', $value );
	$value = trim( $value );
}

/**
 * @param array $atts Attributes array for the logo shortcode.
 *
 * @return string
 */
function wpseo_local_show_logo( $atts ) {
	$atts = wpseo_check_falses( shortcode_atts( array(
		'id' => get_the_ID(),
	), $atts ) );

	$output = '';

	if ( 'wpseo_locations' !== get_post_type( $atts['id'] ) ) {
		return '';
	}

	$location_logo = get_post_meta( $atts['id'], '_wpseo_business_location_logo', true );

	if ( '' === $location_logo ) {
		$wpseo_options = get_option( 'wpseo' );
		$location_logo = $wpseo_options['company_logo'];
	}

	if ( '' !== $location_logo ) {
		$output = '<img src="' . esc_url( $location_logo ) . '" alt="' . esc_attr( $location_logo_alt = get_post_meta( yoast_wpseo_local_get_attachment_id_from_src( $location_logo ), '_wp_attachment_image_alt', true ) ) . '">';
	}

	if ( ! empty( $output ) ) {
		return $output;
	}
}

/**
 * Return the ID of an image by src.
 *
 * @param string $src The image src.
 *
 * @return null|string
 */
function yoast_wpseo_local_get_attachment_id_from_src( $src ) {
	global $wpdb;
	$id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid = %s", array(
		$src,
	) ) );

	return $id;
}

/**
 * Update business type from Attorney to Legal Service
 */
function yoast_wpseo_local_update_business_type() {
	if ( wpseo_has_multiple_locations() ) {
		$locations_args = array(
			'post_type'  => 'wpseo_locations',
			'nopaging'   => true,
			'meta_query' => array(
				array(
					'key'     => '_wpseo_business_type',
					'value'   => 'Attorney',
					'compare' => '=',
				),
			),
		);

		$locations = new WP_Query( $locations_args );

		if ( $locations->have_posts() ) {
			while ( $locations->have_posts() ) {
				$locations->the_post();
				update_post_meta( get_the_ID(), '_wpseo_business_type', 'LegalService' );
			}
		}
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['business_type'] ) && 'Attorney' == $options['business_type'] ) {
			$options['business_type'] = 'LegalService';

			update_option( 'wpseo_local', $options );
		}
	}
}

/**
 * @return string|void
 */
function yoast_wpseo_local_get_api_key_browser() {
	$api_key_browser = '';
	if ( defined( 'WPSEO_LOCAL_API_KEY_BROWSER' ) ) {
		$api_key_browser = WPSEO_LOCAL_API_KEY_BROWSER;
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['api_key_browser'] ) ) {
			$api_key_browser = $options['api_key_browser'];
		}
	}

	return esc_attr( $api_key_browser );
}

/**
 * @return string|void
 */
function yoast_wpseo_local_get_api_key_server() {
	$api_key_server = '';
	if ( defined( 'WPSEO_LOCAL_API_KEY_SERVER' ) ) {
		$api_key_server = WPSEO_LOCAL_API_KEY_SERVER;
	}
	else {
		$options = get_option( 'wpseo_local' );
		if ( isset( $options['api_key'] ) ) {
			$api_key_server = $options['api_key'];
		}
	}

	return esc_attr( $api_key_server );
}

/**
 * Wrapper function to check whether a location is currently open or closed.
 *
 * @param null $post A post ID.
 *
 * @return bool|WP_Error
 *
 * @since 4.2
 */
function yoast_seo_local_is_location_open( $post = null ) {
	$timezone_repository = new WPSEO_Local_Timezone_Repository();

	return $timezone_repository->is_location_open( $post );
}



