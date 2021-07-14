<?php
/**
 * WC_MNM_Core_Compatibility class
 *
 * @author   SomewhereWarm <sw@somewherewarm.net>
 * @package  WooCommerce Mix and Match
 * @since    1.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Functions for WC core back-compatibility.
 *
 * @class  WC_MNM_Core_Compatibility
 * @since  1.2.0
 */
class WC_MNM_Core_Compatibility {

	/**
	 * Cache 'gte' comparison results.
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @since  1.2.0
	 *
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.7 or greater.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_7() {
		_deprecated_function( __METHOD__, '2.4.0', 'WC_MNM_Core_Compatibility::is_wc_version_gte("2.7.0")' );
		return self::is_wc_version_gte( '2.7' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.6 or greater.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_6() {
		_deprecated_function( __METHOD__, '2.4.0', 'WC_MNM_Core_Compatibility::is_wc_version_gte("2.6.0")' );
		return self::is_wc_version_gte( '2.6' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.5 or greater.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_5() {
		_deprecated_function( __METHOD__, '2.4.0', 'WC_MNM_Core_Compatibility::is_wc_version_gte("2.5.0")' );
		return self::is_wc_version_gte( '2.5' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 2.4 or greater.
	 *
	 * @since  1.2.0
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte_2_4() {
		_deprecated_function( __METHOD__, '2.4.0', 'WC_MNM_Core_Compatibility::is_wc_version_gte("2.4.0")' );
		return self::is_wc_version_gte( '2.4' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}
		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Back-compat wrapper for 'get_parent_id'.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_parent_id( $product ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return $product->get_parent_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->id ) : 0;
		}
	}

	/**
	 * Back-compat wrapper for 'get_id'.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product  $product
	 * @return mixed
	 */
	public static function get_id( $product ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return $product->get_id();
		} else {
			return $product->is_type( 'variation' ) ? absint( $product->variation_id ) : absint( $product->id );
		}
	}

	/**
	 * Back-compat wrapper for getting CRUD object props directly.
	 * Falls back to meta under WC 2.7+.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Data  $obj
	 * @param  string   $name
	 * @param  string   $context
	 * @return mixed
	 */
	public static function get_prop( $obj, $name, $context = 'view' ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			$get_fn = 'get_' . $name;
			return is_callable( array( $obj, $get_fn ) ) ? $obj->$get_fn( $context ) : $obj->get_meta( '_' . $name, true );
		} else {

			if ( 'status' === $name ) {
				$value = isset( $obj->post->post_status ) ? $obj->post->post_status : null;
			} elseif ( 'short_description' === $name ) {
				$value = isset( $obj->post->post_excerpt ) ? $obj->post->post_excerpt : null;
			} else {
				$value = $obj->$name;
			}

			return $value;
		}
	}

	/**
	 * Back-compat wrapper for setting CRUD object props directly.
	 * Falls back to meta under WC 2.7+.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Data  $product
	 * @param  string   $name
	 * @param  mixed    $value
	 * @return void
	 */
	public static function set_prop( $obj, $name, $value ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			$set_fn = 'set_' . $name;
			if ( is_callable( array( $obj, $set_fn ) ) ) {
				$obj->$set_fn( $value );
			} else {
				$obj->add_meta_data( '_' . $name, $value, true );
			}
		} else {
			$obj->$name = $value;
		}
	}

	/**
	 * Back-compat wrapper for getting CRUD object meta.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Data  $obj
	 * @param  string   $key
	 * @return mixed
	 */
	public static function get_meta( $obj, $key ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return $obj->get_meta( $key, true );
		} else {
			return get_post_meta( $obj->id, $key, true );
		}
	}

	/**
	 * Back-compat wrapper for 'wc_get_formatted_variation'.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product_Variation  $variation
	 * @param  boolean               $flat
	 * @return string
	 */
	public static function wc_get_formatted_variation( $variation, $flat = false ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return wc_get_formatted_variation( $variation, $flat );
		} elseif ( self::is_wc_version_gte( '2.5.0' ) ) {
			return $variation->get_formatted_variation_attributes( $flat );
		} else {
			return wc_get_formatted_variation( $variation->get_variation_attributes(), $flat );
		}
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once..
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $group
	 * @return string
	 */
	public static function wc_cache_helper_get_cache_prefix( $group ) {
		if ( self::is_wc_version_gte( '2.5.0' ) ) {
			return WC_Cache_Helper::get_cache_prefix( $group );
		} else {
			// Get cache key - uses cache key wc_orders_cache_prefix to invalidate when needed
			$prefix = wp_cache_get( 'wc_' . $group . '_cache_prefix', $group );

			if ( false === $prefix ) {
				$prefix = 1;
				wp_cache_set( 'wc_' . $group . '_cache_prefix', $prefix, $group );
			}

			return 'wc_cache_' . $prefix . '_';
		}
	}

	/**
	 * Increment group cache prefix (invalidates cache).
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $group
	 */
	public static function wc_cache_helper_incr_cache_prefix( $group ) {
		if ( self::is_wc_version_gte( '2.5.0' ) ) {
			WC_Cache_Helper::incr_cache_prefix( $group );
		} else {
			wp_cache_incr( 'wc_' . $group . '_cache_prefix', 1, $group );
		}
	}

	/**
	 * Returns the price including or excluding tax, based on the 'woocommerce_tax_display_shop' setting.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product $product
	 * @param  array $args
	 */
	public static function wc_get_price_to_display( $product, $args = array() ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return wc_get_price_to_display( $product, $args );
		} else {
			$args = wp_parse_args( $args, array(
				'qty'   => 1,
				'price' => '',
			) );
			return $product->get_display_price( $args['price'] );
		}
	}


	/**
	 * Get price including tax.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product $product
	 * @param  array $args
	 */
	public static function wc_get_price_including_tax( $product, $args = array() ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return wc_get_price_including_tax( $product, $args );
		} else {
			$args = wp_parse_args( $args, array(
				'qty'   => 1,
				'price' => '',
			) );
			return $product->get_price_including_tax( $args['qty'], $args['price'] );
		}
	}

	/**
	 * Get price excluding tax.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product $product
	 * @param  array $args
	 */
	public static function wc_get_price_excluding_tax( $product, $args = array() ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			return wc_get_price_excluding_tax( $product, $args );
		} else {
			$args = wp_parse_args( $args, array(
				'qty'   => 1,
				'price' => '',
			) );
			return $product->get_price_excluding_tax( $args['qty'], $args['price'] );
		}
	}

	/**
	 * Check if all variation's attributes are set.
	 *
	 * @since  1.2.0
	 *
	 * @param  WC_Product_Variation $variation
	 */
	public static function has_all_attributes_set( $variation ) {
		$set = true;
		foreach ( $variation->get_variation_attributes() as $att ) {
			if ( ! $att ) {
				$set = false;
				break;
			}
		}
		return $set;
	}

	/**
	 * Backwards compatible logging using 'WC_Logger' class.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $message
	 * @param  string  $level
	 * @param  string  $context
	 */
	public static function log( $message, $level, $context ) {
		if ( self::is_wc_version_gte( '3.0.0' ) ) {
			$logger = wc_get_logger();
			$logger->log( $level, $message, array( 'source' => $context ) );
		} else {
			$logger = new WC_Logger();
			$logger->add( $context, $message );
		}
	}
}