<?php
/**
 * Useful functions for the plugin
 *
 * @author      WooThemes
 * @package     WC_OD
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Gets the value of the query string argument.
 *
 * @since 1.0.0
 * @param string $arg The query string argument.
 * @return mixed      The argument value.
 */
function wc_od_get_query_arg( $arg ) {
	$value = '';
	$arg = sanitize_key( $arg );
	if ( ! empty( $_POST ) && isset( $_POST['_wp_http_referer'] ) ) {
		$query_string = parse_url( $_POST['_wp_http_referer'], PHP_URL_QUERY );
		if ( $query_string ) {
			$query_args = array();
			parse_str( $query_string, $query_args );
			if ( isset( $query_args[ $arg ] ) ) {
				$value = $query_args[ $arg ];
			}
		}
	} elseif ( isset( $_GET[ $arg ] ) ) {
		$value = $_GET[ $arg ];
	}

	return urldecode( $value );
}

/**
 * Gets the specified admin url.
 *
 * @since 1.0.0
 * @param string $section      Optional. The section name parameter.
 * @param array  $extra_params Optional. Additional parameters in pairs key => value.
 * @return string The admin page url.
 */
function wc_od_get_settings_url( $section = '', $extra_params = array() ) {
	$url = 'admin.php?page=' . urlencode( WC_OD_Utils::get_woocommerce_settings_page_slug() );
	$url .= '&amp;tab=shipping';

	if ( $section ) {
		$url .= '&amp;section=' . urlencode( $section );
	}

	if ( ! empty( $extra_params ) ) {
		foreach( $extra_params as $param => $value ) {
			$url .= '&amp;' . esc_attr( $param ) . '=' . urlencode( $value );
		}
	}

	return admin_url( $url );
}

/**
 * Gets the plugin prefix.
 *
 * Note: The prefix is used for the settings Ids.
 *
 * @since 1.1.0
 *
 * @return string The plugin prefix.
 */
function wc_od_get_prefix() {
	return 'wc_od_';
}

/**
 * Removes the plugin prefix from the beginning of the string.
 *
 * @since 1.0.0
 * @param string $string The string to parse.
 * @return string The parsed string.
 */
function wc_od_no_prefix( $string ) {
	$prefix = wc_od_get_prefix();
	if ( $prefix === substr( $string, 0, strlen( $prefix ) ) ) {
		$string = substr( $string, strlen( $prefix ) );
	}

	return $string;
}

/**
 * Maybe adds the plugin prefix to the beginning of the string.
 *
 * @since 1.0.0
 * @param string $string The string to parse.
 * @return string The parsed string.
 */
function wc_od_maybe_prefix( $string ) {
	$prefix = wc_od_get_prefix();
	$string = wc_od_no_prefix( $string );

	return $prefix . $string;
}

/**
 * Gets templates passing attributes and including the file.
 *
 * @since 1.0.0
 *
 * @param string $template_name The template name.
 * @param array  $args          Optional. The template arguments.
 */
function wc_od_get_template( $template_name, $args = array() ) {
	wc_get_template( $template_name, $args, WC_TEMPLATE_PATH, WC_OD_PATH . 'templates/' );
}

/**
 * Gets the week days in a pair index => label.
 *
 * @since 1.0.0
 *
 * @global WP_Locale $wp_locale The WP_Locale instance.
 *
 * @return array The week days.
 */
function wc_od_get_week_days() {
	global $wp_locale;

	return $wp_locale->weekday;
}

/**
 * Formats a delivery range.
 *
 * @since 1.1.0
 *
 * @param array $range An associative array with the 'min' and 'max' values.
 * @param bool  $echo  Optional. Whether to echo or just return the string.
 * @return string The formatted delivery range.
 */
function wc_od_format_delivery_range( $range, $echo = false ) {
	if ( $range['min'] === $range['max'] ) {
		$output = $range['max'];
	} else {
		$output = "{$range['min']}-{$range['max']}";
	}

	/**
	 * Filter the formatted delivery range.
	 *
	 * @since 1.1.0
	 *
	 * @param string $output The output string.
	 * @param array  $range  An associative array with the 'min' and 'max' values.
	 */
	$output = apply_filters( 'wc_od_format_delivery_range', $output, $range );

	if ( $echo ) {
		echo $output;
	}

	return $output;
}

/**
 * Prints the order delivery details.
 *
 * @since 1.1.0
 *
 * @param array $args The arguments.
 */
function wc_od_order_delivery_details( $args = array() ) {
	$defaults = array(
		'title' => __( 'Shipping and delivery', 'woocommerce-order-delivery' ),
	);

	/**
	 * Filter the arguments used by the order/delivery-date.php template.
	 *
	 * @since 1.1.0
	 *
	 * @param array $args The arguments.
	 */
	$args = apply_filters( 'wc_od_order_delivery_details_args', wp_parse_args( $args, $defaults ) );

	wc_od_get_template( 'order/delivery-date.php', $args );
}


/** Datetime functions ********************************************************/


/**
 * Parses a string into a DateTime object, optionally forced into the given timezone.
 *
 * @since 1.0.0
 * @param string       $string    A string representing a datetime
 * @param DateTimeZone $timezone  Optional. The timezone.
 * @return DateTime  The DataTime object.
 */
function wc_od_parse_datetime( $string, $timezone = null ) {
	if ( ! $timezone ) {
		$timezone = new DateTimeZone( 'UTC' );
	}

	$date = new DateTime( $string, $timezone );
	$date->setTimezone( $timezone );

	return $date;
}

/**
 * Takes the year-month-day values of the given DateTime and converts them to a new UTC DateTime.
 *
 * @since 1.0.0
 * @param DateTime $datetime The datetime.
 * @return DateTime The DataTime object.
 */
function wc_od_strip_time( $datetime ) {
	return new DateTime( $datetime->format( 'Y-m-d' ) );
}

/**
 * Parses a string into a DateTime object.
 *
 * @since 1.0.0
 * @param string $string      A string representing a time.
 * @param string $time_format The time format.
 * @return string The sanitized time.
 */
function wc_od_sanitize_time( $string, $time_format = 'H:i' ) {
	if ( ! $string ) {
		return '';
	}

	$timestamp = strtotime( $string );
	if ( false === $timestamp ) {
		return '';
	}

	return date( $time_format, $timestamp );
}

/**
 * Gets the localized date with the date format.
 *
 * @since 1.0.0
 * @param string|int $date   The date to localize.
 * @param string     $format Optional. The date format. If null use the general WordPress date format.
 * @return string|null The localized date string. Null if the date is not valid.
 */
function wc_od_localize_date( $date, $format = null ) {
	$timestamp = wc_od_get_timestamp( $date );
	$date_i18n = null;

	if ( false !== $timestamp ) {
		if ( ! $format ) {
			$format = get_option( 'date_format', 'F j, Y' );
		}

		$date_i18n = date_i18n( $format , $timestamp );
	}

	return $date_i18n;
}

/**
 * Checks if it's a valid timestamp.
 *
 * @since 1.1.0
 *
 * @param string|int $timestamp Timestamp to validate.
 *
 * @return bool True if the parameter is a timestamp. False otherwise.
 */
function wc_od_is_timestamp( $timestamp ) {
	return ( is_numeric( $timestamp ) && (int) $timestamp == $timestamp );
}

/**
 * Gets the timestamp value for the date string.
 *
 * If $date is already a timestamp (integer or string), only it's parsed to integer.
 *
 * @since 1.1.0
 *
 * @param string|int $date The date to process.
 * @return false|int The timestamp value. False for invalid values.
 */
function wc_od_get_timestamp( $date ) {
	if ( wc_od_is_timestamp( $date ) ) {
		return (int) $date;
	}

	return strtotime( $date );
}

/**
 * Gets the timezone string for a site.
 *
 * Source: https://www.skyverge.com/blog/down-the-rabbit-hole-wordpress-and-timezones/
 *
 * @since 1.0.4
 *
 * @return string A valid PHP timezone string.
 */
function wc_od_get_timezone_string() {
	// If site timezone string exists, return it.
	if ( $timezone = get_option( 'timezone_string' ) ) {
		return $timezone;
	}

	// Get UTC offset, if it isn't set then return UTC.
	if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) ) {
		return 'UTC';
	}

	// Adjust UTC offset from hours to seconds.
	$utc_offset *= 3600;

	// Attempt to guess the timezone string from the UTC offset.
	if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
		return $timezone;
	}

	// Last try, guess timezone string manually.
	$is_dst = date( 'I' );

	foreach ( timezone_abbreviations_list() as $abbr ) {
		foreach ( $abbr as $city ) {
			if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset ) {
				return $city['timezone_id'];
			}
		}
	}

	// Fallback to UTC.
	return 'UTC';
}

/**
 * Gets the unix timestamp for a date already adjusted in the site's timezone.
 *
 * @since 1.0.4
 *
 * @param string $date A local datetime string.
 * @return int The unix timestamp.
 */
function wc_od_local_datetime_to_timestamp( $date ) {
	$datetime = new DateTime( $date, new DateTimeZone( wc_od_get_timezone_string() ) );

	return $datetime->format( 'U' );
}

/**
 * Gets the date representing the current day in the site's timezone.
 *
 * @since 1.1.0
 *
 * @param bool   $timestamp Optional. True to return a timestamp. False for a date string.
 * @param string $format    Optional. The date format.
 * @return mixed The current date string or timestamp. False on failure.
 */
function wc_od_get_local_date( $timestamp = true, $format = 'Y-m-d' ) {
	$date = current_time( $format );

	return ( $timestamp ? strtotime( $date ) : $date );
}

/**
 * Gets the date format for the specified programming language.
 *
 * NOTE: The format can be translated for each language. It uses the ISO 8601 as the default date format.
 *
 * @since 1.1.0
 *
 * @param string $language Optional. The programming language [php, js].
 * @return string The date format.
 */
function wc_od_get_date_format( $language = 'php' ) {
	$date_format = _x( 'Y-m-d', 'date format for php', 'woocommerce-order-delivery' );

	if ( 'js' === $language ) {
		$date_format = _x( 'yyyy-mm-dd', 'date format for js', 'woocommerce-order-delivery' );
	}

	return $date_format;
}


/** Countries & states functions **********************************************/


/**
 * Gets the countries you ship to.
 *
 * @since 1.0.0
 *
 * @return array
 */
function wc_od_get_countries() {
	return WC()->countries->get_shipping_countries();
}

/**
 * Gets the country states you ship to.
 *
 * @since 1.0.0
 *
 * @return array
 */
function wc_od_get_country_states() {
	return WC()->countries->get_shipping_country_states();
}

/**
 * Gets the country states you ship to.
 *
 * The state's information is formatted for the select2 library.
 *
 * @since 1.0.0
 *
 * @return array
 */
function wc_od_get_country_states_for_select2() {
	$formatted_country_states = array();
	$country_states = wc_od_get_country_states();
	foreach ( $country_states as $country => $states ) {
		$formatted_country_states[ $country ] = array();
		foreach ( $states as $key => $state ) {
			$formatted_country_states[ $country ][] = array( 'id' => $key, 'text' => $state );
		}
	}

	return $formatted_country_states;
}


/** Shipping & Delivery functions **********************************************/


/**
 * Gets the days by the specified property and value.
 *
 * @since 1.0.0
 * @param array $days      The days data.
 * @param string $property The day property to filter.
 * @param mixed $value     The property value to search.
 * @return array The filtered days.
 */
function wc_od_get_days_by( $days, $property, $value ) {
	$filtered_days = array();
	foreach ( $days as $index => $day ) {
		if ( isset( $day[ $property ] ) && $value === $day[ $property ] ) {
			$filtered_days[ $index ] = $day;
		}
	}

	return $filtered_days;
}

/**
 * Gets the events.
 *
 * @since 1.0.0
 * @param array $filters The filters for retrieve the events.
 * @return array The filtered events.
 */
function wc_od_get_events( $filters = array() ) {
	$event_type = ( isset( $filters['type'] ) ? $filters['type'] : 'event' ) ;
	$event_class = 'WC_OD_Event';
	if ( 'delivery' === $event_type ) {
		$event_class = 'WC_OD_Delivery_Event';
	}

	$event_filters = array_diff_key( $filters, array_flip( array( 'timezone', 'start', 'end', 'type' ) ) );
	$event_filters['range_start'] = wc_od_parse_datetime( $filters['start'] );
	$event_filters['range_end'] = wc_od_parse_datetime( $filters['end'] );

	// Parse the timezone parameter if it is present.
	$timezone = null;
	if ( isset( $filters['timezone'] ) && $filters['timezone'] ) {
		$timezone = new DateTimeZone( $filters['timezone'] );
	}

	$setting_name = $event_type . '_events';
	$events = WC_OD()->settings()->get_setting( $setting_name, array() );
	$filtered_events = array();
	foreach ( $events as $eventData ) {
		$event = new $event_class( $eventData, $timezone );
		if ( $event->is_valid( $event_filters ) ) {
			$filtered_events[] = $event->to_array();
		}
	}

	/**
	 * Filter the events.
	 *
	 * @since 1.1.0
	 *
	 * @param array $events  An array with the events.
	 * @param array $filters An array with the filters used to get the events.
	 */
	return apply_filters( 'wc_od_get_events', $filtered_events, $filters );
}

/**
 * Gets the disabled days for the specified arguments.
 *
 * @since 1.1.0
 *
 * @param array  $args    Optional. The arguments.
 * @param string $context Optional. The context.
 * @return array An array with the disabled days.
 */
function wc_od_get_disabled_days( $args = array(), $context = '' ) {
	$today = wc_od_get_local_date();
	$max_delivery_days = ( WC_OD()->settings()->get_setting( 'max_delivery_days' ) + 1 ); // Non-inclusive.

	$defaults = array(
		'type'  => 'shipping',
		'start' => date( 'Y-m-d', $today ),
		'end'   => date( 'Y-m-d', strtotime( "+ {$max_delivery_days} days" , $today ) ),
	);

	/**
	 * Filter the arguments used to calculate the disabled days.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $args    The arguments.
	 * @param string $context The context.
	 */
	$args = apply_filters( 'wc_od_get_disabled_days_args', wp_parse_args( $args, $defaults ), $context );

	$disabled_days = array();
	$events        = wc_od_get_events( $args );
	$date_format   = wc_od_get_date_format( 'php' );

	foreach ( $events as $event ) {
		$start_timestamp = wc_od_get_timestamp( $event['start'] );
		$disabled_days[] = wc_od_localize_date( $start_timestamp, $date_format );

		if ( isset( $event['end'] ) ) {
			$end_timestamp = wc_od_get_timestamp( $event['end'] );

			while ( $start_timestamp < $end_timestamp ) {
				$start_timestamp = strtotime( '+1 day', $start_timestamp );
				$disabled_days[] = wc_od_localize_date( $start_timestamp, $date_format );
			}
		}
	}

	$disabled_days = array_unique( $disabled_days );

	/**
	 * Filter the disabled days.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $days    An array with the disabled dates.
	 * @param array  $args    The arguments used to disable the days.
	 * @param string $context The context.
	 */
	return apply_filters( 'wc_od_get_disabled_days', $disabled_days, $args, $context );
}

/**
 * Gets if the specified day is disabled or not.
 *
 * @since 1.1.0
 *
 * @param string|int $date    The date string or timestamp.
 * @param array      $args    Optional. The optional arguments.
 * @param string     $context Optional. The context.
 * @return bool|null True if the date is disabled. False otherwise. Null on failure.
 */
function wc_od_is_disabled_day( $date, $args = array(), $context = '' ) {
	$timestamp = wc_od_get_timestamp( $date );
	if ( ! $timestamp ) {
		return null;
	}

	$args['start'] = date( 'Y-m-d', $timestamp );
	$args['end']   = date( 'Y-m-d', strtotime( '+1 day', $timestamp ) );

	$days = wc_od_get_disabled_days( $args, $context );

	/**
	 * Filter if the specified day is disabled or not.
	 *
	 * @since 1.1.0
	 *
	 * @param bool   $disabled True if the day is disabled. False otherwise.
	 * @param int    $date     A timestamp representing the day to check.
	 * @param array  $args     The optional arguments.
	 * @param string $context  The context.
	 */
	return apply_filters( 'wc_od_is_disabled_day', ( ! empty( $days ) ), $timestamp, $args, $context );
}

/**
 * Gets if the specified delivery date is valid or not.
 *
 * @since 1.1.0
 *
 * @param string|int $date    The delivery date string or timestamp.
 * @param array      $args    Optional. The optional arguments.
 * @param string     $context Optional. The context.
 * @return bool True if the delivery date is a valid date. False otherwise.
 */
function wc_od_validate_delivery_date( $date, $args = array(), $context = '' ) {
	$delivery_timestamp = wc_od_get_timestamp( $date );

	if ( ! $delivery_timestamp ) {
		return false;
	}

	$defaults = array(
		'start_date'         => false,
		'end_date'           => false, // The maximum date (Non-inclusive).
		'delivery_days'      => WC_OD()->settings()->get_setting( 'delivery_days' ),
		'disabled_days'      => null, // Use these disabled days if not null.
		'disabled_days_args' => array( // Arguments used by the wc_od_disabled_days() function.
			'type'    => 'delivery',
			'country' => '', // Events for all countries.
		),
	);

	/**
	 * Filter the arguments used to validate the delivery date.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $args    The arguments.
	 * @param string $context The context.
	 */
	$args = apply_filters( 'wc_od_validate_delivery_date_args', wp_parse_args( $args, $defaults ), $context );

	$valid = true;

	// Validate start_date.
	if ( $args['start_date'] ) {
		$start_timestamp = wc_od_get_timestamp( $args['start_date'] );

		// Out of range.
		if ( ! $start_timestamp || $delivery_timestamp < $start_timestamp ) {
			$valid = false;
		}
	}

	// Validate end_date.
	if ( $valid && $args['end_date'] ) {
		$end_timestamp = wc_od_get_timestamp( $args['end_date'] );

		// Out of range.
		if ( ! $end_timestamp || $delivery_timestamp >= $end_timestamp ) {
			$valid = false;
		}
	}

	// Validate delivery_days.
	if ( $valid ) {
		// Fetch the keys of the enabled days.
		if ( ! empty( $args['delivery_days'] ) && is_array( reset( $args['delivery_days'] ) ) ) {
			$delivery_days = array_keys( wc_od_get_days_by( $args['delivery_days'], 'enabled', '1' ) );
		} else {
			$delivery_days = $args['delivery_days'];
		}

		// Date not enabled.
		if ( ! in_array( date( 'w', $delivery_timestamp ), $delivery_days ) ) {
			$valid = false;
		}
	}

	// Validate disabled days.
	if ( $valid ) {
		$delivery_date = wc_od_localize_date( $delivery_timestamp, wc_od_get_date_format( 'php' ) );

		if ( ( $args['disabled_days'] && in_array( $delivery_date, $args['disabled_days'] ) ) ||
			wc_od_is_disabled_day( $delivery_date, $args['disabled_days_args'], $context ) ) {
			$valid = false;
		}
	}

	/**
	 * Filters the delivery date validation.
	 *
	 * @since 1.0.0
	 *
	 * @param bool   $valid              Is it a valid delivery date?.
	 * @param int    $delivery_timestamp The delivery date timestamp.
	 * @param array  $args               Since 1.1.0. A array with the arguments used to validate the date.
	 * @param string $context            Since 1.1.0. The context.
	 */
	return apply_filters( 'wc_od_validate_delivery_date', $valid, $delivery_timestamp, $args, $context );
}

/**
 * Gets the first day to ship the orders.
 *
 * @since 1.1.0
 *
 * @param array  $args    Optional. The arguments used to calculate the date.
 * @param string $context Optional. The context.
 * @return int A timestamp representing the first allowed date to ship the orders. False on failure.
 */
function wc_od_get_first_shipping_date( $args = array(), $context = '' ) {
	$defaults = array(
		'min_working_days'   => WC_OD()->settings()->get_setting( 'min_working_days' ),
		'shipping_days'      => WC_OD()->settings()->get_setting( 'shipping_days' ),
		'days_for_shipping'  => 0,
		'start_date'         => wc_od_get_local_date( false ), // Accept strings or timestamps.
		'end_date'           => false, // The maximum date (Non-inclusive) to look for a valid date.
		'disabled_days_args' => array( // Arguments passed to the wc_od_disabled_days() function.
			'type' => 'shipping',
		),
	);

	/**
	 * Filter the arguments used to calculate the first shipping date.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $args    The arguments.
	 * @param string $context The context.
	 */
	$args = apply_filters( 'wc_od_first_shipping_date_args', wp_parse_args( $args, $defaults ), $context );

	/**
	 * Before executing any calculation, it forces a shipping date if the returned value by the filter is not false.
	 *
	 * @since 1.1.0
	 *
	 * @param int|false $timestamp A timestamp representing the first shipping date.
	 * @param array     $args      The arguments.
	 * @param string    $context   The context.
	 */
	$first_shipping_date = apply_filters( 'wc_od_pre_get_first_shipping_date', false, $args, $context );

	if ( $first_shipping_date ) {
		return $first_shipping_date;
	}

	$start_timestamp = wc_od_get_timestamp( $args['start_date'] );
	if ( ! $start_timestamp ) {
		return false;
	}

	$deadline = wc_od_get_timestamp( $args['end_date'] );
	$today    = wc_od_get_local_date();
	$wday     = date( 'w', $start_timestamp );

	do {
		// Allowed shipping day.
		if ( $args['shipping_days'][ $wday ]['enabled'] ) {
			$timestamp = strtotime( "{$args['days_for_shipping']} days", $start_timestamp );

			// The day is not disabled for shipping.
			if ( ! wc_od_is_disabled_day( $timestamp, $args['disabled_days_args'], $context ) ) {

				// It's the current day.
				if ( 0 === $args['days_for_shipping'] && $today === $timestamp ) {
					// Checks the time.
					if ( $args['shipping_days'][ $wday ]['time'] ) {
						$timestamp_limit = strtotime( wc_od_get_local_date( false ) . " {$args['shipping_days'][ $wday ]['time']}" );

						// We can start to process the order today.
						if ( current_time( 'timestamp' ) < $timestamp_limit ) {
							$first_shipping_date = $today;
							$args['min_working_days']--;
						}
					} else {
						// We can start to process the order today.
						$first_shipping_date = $today;
						$args['min_working_days']--;
					}
				} else {
					$first_shipping_date = $timestamp;
					$args['min_working_days']--;
				}

				// Not found yet.
				if ( -1 < $args['min_working_days'] ) {
					$first_shipping_date = false;
				}
			}
		}

		$args['days_for_shipping']++;
		$wday = ( ( $wday + 1 ) % 7 );
	} while ( ! $first_shipping_date && ( ! $deadline || $timestamp < $deadline ) );

	/**
	 * Filter the first shipping date.
	 *
	 * @since 1.1.0
	 *
	 * @param int    $timestamp A timestamp representing the first shipping date.
	 * @param array  $args      A array with the arguments used to calculate the date.
	 * @param string $context   The context.
	 */
	return apply_filters( 'wc_od_get_first_shipping_date', $first_shipping_date, $args, $context );
}

/**
 * Gets the first day to deliver the orders.
 *
 * @since 1.1.0
 *
 * @param array  $args    Optional. The arguments used to calculate the date.
 * @param string $context Optional. The context.
 * @return false|int A timestamp representing the first allowed date to deliver the orders. False on failure.
 */
function wc_od_get_first_delivery_date( $args = array(), $context = '' ) {
	$defaults = array(
		'delivery_days'      => WC_OD()->settings()->get_setting( 'delivery_days' ),
		'delivery_range'     => WC_OD()->settings()->get_setting( 'delivery_range' ),
		'shipping_date'      => '', // Accept strings or timestamps.
		'end_date'           => false, // The maximum date (Non-inclusive) to look for a valid date.
		'disabled_days_args' => array( // Arguments used by the wc_od_disabled_days() function.
			'type'    => 'delivery',
			'country' => '', // Events for all countries.
		),
	);

	$args = wp_parse_args( $args, $defaults );

	// Avoid to calculate the default shipping date value if this will be overridden in the wp_parse_args() function.
	// We use an empty string instead of 'false' to avoid conflict with an invalid timestamp.
	if ( '' === $args['shipping_date'] ) {
		$args['shipping_date'] = wc_od_get_first_shipping_date( array(), $context );
	}

	/**
	 * Filter the arguments used to calculate the first delivery date.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $args    The arguments.
	 * @param string $context The context.
	 */
	$args = apply_filters( 'wc_od_first_delivery_date_args', $args, $context );

	/**
	 * Before executing any calculation, it forces a delivery date if the returned value by the filter is not false.
	 *
	 * @since 1.1.0
	 *
	 * @param int|false $timestamp A timestamp representing the first delivery date.
	 * @param array     $args      The arguments.
	 * @param string    $context   The context.
	 */
	$first_delivery_date = apply_filters( 'wc_od_pre_get_first_delivery_date', false, $args, $context );

	if ( $first_delivery_date ) {
		return $first_delivery_date;
	}

	$shipping_timestamp = wc_od_get_timestamp( $args['shipping_date'] );
	if ( ! $shipping_timestamp ) {
		return false;
	}

	$seconds_in_a_day   = 86400;
	$today              = wc_od_get_local_date();
	$days_for_delivery  = ( ( $shipping_timestamp - $today ) / $seconds_in_a_day );
	$wday               = date( 'w', $shipping_timestamp );
	$min_delivery_days  = $args['delivery_range']['min'];
	$deadline           = wc_od_get_timestamp( $args['end_date'] );

	do {
		$timestamp = strtotime( "{$days_for_delivery} days", $today );

		/*
		 * Special Case: The current date is the shipping date and the minimum delivery days is higher than zero.
		 * We do not deliver this day because it is disabled. But it is a working day for the shipping company.
		 */
		if ( $args['delivery_days'][ $wday ]['enabled'] || ( $shipping_timestamp === $timestamp && 0 < $min_delivery_days ) ) {
			// The day is not disabled for delivery.
			if ( ! wc_od_is_disabled_day( $timestamp, $args['disabled_days_args'], $context ) ) {
				if ( 0 >= $min_delivery_days ) {
					$first_delivery_date = $timestamp;
				}

				// Weekday.
				$min_delivery_days--;
			}
		}

		$days_for_delivery++;
		$wday = ( ( $wday + 1 ) % 7 );
	} while ( ! $first_delivery_date && ( ! $deadline || $timestamp < $deadline ) );

	/**
	 * Filter the first delivery date.
	 *
	 * @since 1.1.0
	 *
	 * @param int    $timestamp A timestamp representing the first delivery date.
	 * @param array  $args      A array with the arguments used to calculate the date.
	 * @param string $context   The context.
	 */
	return apply_filters( 'wc_od_get_first_delivery_date', $first_delivery_date, $args, $context );
}

/**
 * Gets the delivery date field arguments.
 *
 * @since 1.1.0
 *
 * @param array  $args    Optional. The arguments to overwrite.
 * @param string $context Optional. The context in which the form field is used.
 *
 * @return array An array with the delivery date field arguments.
 */
function wc_od_get_delivery_date_field_args( $args = array(), $context = '' ) {
	$defaults = array(
		'type'        => 'text',
		'label'       => __( 'Pick a delivery Date', 'woocommerce-order-delivery' ),
		'placeholder' => wc_od_get_date_format( 'js' ),
		'class'       => array( 'form-row-wide' ),
		'required'    => ( 'required' === WC_OD()->settings()->get_setting( 'delivery_date_field' ) ),
		'return'      => true,
		'value'       => '',
	);

	/**
	 * Filters the arguments for the delivery date field.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $args    The arguments for the delivery date field.
	 * @param string $context From 1.1.0. The context in which the form field is used.
	 */
	return apply_filters( 'wc_od_delivery_date_field_args', wp_parse_args( $args, $defaults ), $context );
}

/**
 * Gets the calendar settings that will be used to configure the datepicker.
 *
 * Note: Dates must have the same format as the 'format' parameter.
 *
 * @since 1.1.0
 *
 * @param array  $args     Optional. The parameters to overwrite the defaults.
 * @param string $context  Optional. The context.
 * @return array An array with the calendar settings.
 */
function wc_od_get_calendar_settings( $args = array(), $context = '' ) {
	$defaults = array(
		'language'           => get_bloginfo( 'language' ),
		'format'             => wc_od_get_date_format( 'js' ),
		'weekStart'          => get_option( 'start_of_week', 0 ),
		'startDate'          => '',
		'endDate'            => '',
		'daysOfWeekDisabled' => array(),
		'datesDisabled'      => array(),
		'clearBtn'           => ( 'required' !== WC_OD()->settings()->get_setting( 'delivery_date_field' ) ),
	);

	/**
	 * Filter the calendar settings.
	 *
	 * @since 1.1.0
	 *
	 * @param array  $settings The calendar settings.
	 * @param string $context  The context.
	 */
	return apply_filters( 'wc_od_get_calendar_settings', wp_parse_args( $args, $defaults ), $context );
}

/**
 * Enqueue the necessary scripts and styles to load the datepicker.
 *
 * @since 1.1.0
 *
 * @param string $context The context.
 */
function wc_od_enqueue_datepicker( $context = '' ) {
	wp_enqueue_style( 'bootstrap-datepicker', WC_OD_URL . 'assets/css/lib/bootstrap-datepicker.css', array(), '1.6.4' );
	wp_add_inline_style( 'bootstrap-datepicker', wc_od_get_datepicker_custom_styles( $context ) );

	wp_enqueue_script( 'bootstrap-datepicker', WC_OD_URL . 'assets/js/lib/bootstrap-datepicker.min.js', array( 'jquery' ), '1.6.4', true );
	wp_enqueue_script( 'wc-od-datepicker', WC_OD_URL . 'assets/js/wc-od-datepicker.js', array( 'jquery', 'bootstrap-datepicker' ), WC_OD_VERSION, true );

	/**
	 * Enqueue scripts after the datepicker scripts.
	 *
	 * @since 1.1.0
	 *
	 * @param string $context The context.
	 */
	do_action( 'wc_od_enqueue_datepicker', $context );
}

/**
 * Gets the datepicker custom styles.
 *
 * @since 1.1.0
 *
 * @param string $context Optional. The context. [checkout, settings]
 * @return string The datepicker styles.
 */
function wc_od_get_datepicker_custom_styles( $context = '' ) {
	$styles = '

/**
 * WC Order Delivery: Datepicker custom styles.
 */

.datepicker table {
	width: auto;
	border: 0;
}

.datepicker table tr td,
.datepicker table tr th {
	width: 24px;
	height: 24px;
}

.datepicker.dropdown-menu th,
.datepicker.datepicker-inline th,
.datepicker.dropdown-menu td,
.datepicker.datepicker-inline td {
	padding: 3px;
	background-color: #fff;
	box-sizing: content-box;
	vertical-align: middle;
}';

	/**
	 * Filter the datepicker custom styles.
	 *
	 * @since 1.1.0
	 *
	 * @param string $styles  The styles.
	 * @param string $context The context.
	 */
	return apply_filters( 'wc_od_datepicker_custom_styles', $styles, $context );
}


/** Backward compatibility functions **********************************************/


/**
 * Gets a property from the order.
 *
 * @since 1.1.0
 *
 * @param mixed  $the_order Post object or post ID of the order.
 * @param string $prop      Name of prop to get.
 * @return mixed|null The prop value. Null on failure.
 */
function wc_od_get_order_prop( $the_order, $prop ) {
	$order = ( $the_order instanceof WC_Order ? $the_order : wc_get_order( $the_order ) );

	if ( ! $order ) {
		return null;
	}

	$callable = array( $order, "get_{$prop}" );

	return ( is_callable( $callable ) ? call_user_func( $callable ) : $order->$prop );
}

/**
 * Gets an order meta data by key.
 *
 * @since 1.1.0
 *
 * @param mixed  $the_order Post object or post ID of the order.
 * @param string $key       Optional. The meta key to retrieve.
 * @param bool   $single    Optional. Whether to return a single value. Default true.
 * @return mixed The meta data value.
 */
function wc_od_get_order_meta( $the_order, $key = '', $single = true ) {
	$meta = ''; // WC_Order->get_meta returns an empty string by default.

	if ( version_compare( WC()->version, '3.0', '<' ) ) {
		$order_id = ( $the_order instanceof WC_Order ? $the_order->id : intval( $the_order ) );
		$meta = get_post_meta( $order_id, $key, $single );
	} else {
		$order = ( $the_order instanceof WC_Order ? $the_order : wc_get_order( $the_order ) );

		if ( $order ) {
			$meta = $order->get_meta( $key, $single );
		}
	}

	return $meta;
}

/**
 * Updates an order meta data by key.
 *
 * @since 1.1.0
 *
 * @param mixed  $the_order Post object or post ID of the order.
 * @param string $key       The meta key to update.
 * @param mixed  $value     The meta value.
 * @param bool   $save      Optional. True to save the meta immediately. Default false.
 * @return bool True on successful update, false on failure.
 */
function wc_od_update_order_meta( $the_order, $key, $value, $save = false ) {
	$updated = false;

	if ( version_compare( WC()->version, '3.0', '<' ) ) {
		$order_id = ( $the_order instanceof WC_Order ? $the_order->id : intval( $the_order ) );
		$updated = (bool) update_post_meta( $order_id, $key, $value );
	} else {
		$order = ( $the_order instanceof WC_Order ? $the_order : wc_get_order( $the_order ) );

		if ( $order ) {
			$old_value = $order->get_meta( $key );

			if ( $old_value !== $value ) {
				$order->update_meta_data( $key, $value );
				$updated = true;

				// Save the meta immediately.
				if ( $save ) {
					$order->save_meta_data();
				}
			}
		}
	}

	return $updated;
}

/**
 * Deletes an order meta data by key.
 *
 * @since 1.1.0
 *
 * @param mixed  $the_order Post object or post ID of the order.
 * @param string $key       The meta key to delete.
 * @param bool   $save      Optional. True to delete the meta immediately. Default false.
 * @return bool True on successful delete, false on failure.
 */
function wc_od_delete_order_meta( $the_order, $key, $save = false ) {
	$deleted = false;

	if ( version_compare( WC()->version, '3.0', '<' ) ) {
		$order_id = ( $the_order instanceof WC_Order ? $the_order->id : intval( $the_order ) );
		$deleted = delete_post_meta( $order_id, $key );
	} else {
		$order = ( $the_order instanceof WC_Order ? $the_order : wc_get_order( $the_order ) );

		if ( $order ) {
			$order->delete_meta_data( $key );
			$deleted = true;

			if ( $save ) {
				$order->save_meta_data();
			}
		}
	}

	return $deleted;
}
