<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) && class_exists('SitePress') ) {

	if ( class_exists( 'BETRS_WPML' ) ) return;

	class BETRS_WPML {

		/**
		 * Cloning is forbidden. Will deactivate prior 'instances' users are running
		 *
		 * @since 4.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 4.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct() {

			// modify the necessary settings values through hooks and filters
			add_filter( 'betrs_condition_tertiary_subtotal', array( $this, 'price_currency_conversion' ), 10, 2 );
			add_filter( 'betrs_comparison_tertiary_s_class', array( $this, 'id_conversion_s_class' ), 10, 2 );
			add_filter( 'betrs_comparison_tertiary_product', array( $this, 'id_conversion_product' ), 10, 2 );
			add_filter( 'betrs_comparison_tertiary_category', array( $this, 'id_conversion_category' ), 10, 2 );
			add_filter( 'betrs_save_shipping_rate_label', array( $this, 'translate_shipping_label' ), 10, 3 );
			add_filter( 'betrs_shipping_rate_label', array( $this, 'get_translated_shipping_label' ), 10, 3 );
			add_filter( 'betrs_save_shipping_rate_description', array( $this, 'translate_shipping_description' ), 10, 3 );
			add_filter( 'betrs_shipping_rate_description', array( $this, 'get_translated_shipping_description' ), 10, 3 );

		}


		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function price_currency_conversion( $value, $cond ) {

			return apply_filters( 'wcml_raw_price_amount', $value );
		}


		/**
		 * convert shipping class ID to default language ID.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function id_conversion_s_class( $value, $cond ) {

    		// WPML translate shipping classes
			if( function_exists( 'icl_object_id' ) && function_exists( 'wpml_get_default_language' ) && is_array( $value ) ) {
				$default_language = wpml_get_default_language();

				foreach( $value as $key => $val ) {
					$value[ $key ] = icl_object_id( $val, 'product_shipping_class', true, $default_language );
				}
			}

			return $value;
		}


		/**
		 * convert product ID to default language ID.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function id_conversion_product( $value, $cond ) {

    		// WPML translate shipping classes
			if( function_exists( 'icl_object_id' ) && function_exists( 'wpml_get_default_language' ) && is_array( $value ) ) {
				$default_language = wpml_get_default_language();

				foreach( $value as $key => $val ) {
					$value[ $key ] = icl_object_id( $val, 'product', true, $default_language );
				}
			}

			return $value;
		}


		/**
		 * convert category ID to default language ID.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return void
		 */
		function id_conversion_category( $value, $cond ) {

    		// WPML translate shipping classes
			if( function_exists( 'icl_object_id' ) && function_exists( 'wpml_get_default_language' ) && is_array( $value ) ) {
				$default_language = wpml_get_default_language();

				foreach( $value as $key => $val ) {
					$value[ $key ] = icl_object_id( $val, 'product_cat', true, $default_language );
				}
			}

			return $value;
		}


		/**
		 * add shipping label to string translation.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function translate_shipping_label( $label, $option_id, $save_name ) {

			if( function_exists( 'icl_register_string' ) ) {
				$label = sanitize_text_field( $label );
				$option_id = (int) $option_id;
				list( $slug, $instance_id ) = explode( '-', sanitize_text_field( $save_name ) );
				icl_register_string( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id, $label );
			}

			return $label;
		}


		/**
		 * retrieve translated shipping label.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function get_translated_shipping_label( $label, $option_id, $instance_id ) {

			if( function_exists( 'icl_t' ) ) {
				$label = sanitize_text_field( $label );
				$option_id = (int) $option_id;
				$instance_id = (int) $instance_id;
			    $label = icl_t( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id, $label );
			}

			return $label;
		}


		/**
		 * add shipping description to string translation.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function translate_shipping_description( $desc, $option_id, $save_name ) {

			if( function_exists( 'icl_register_string' ) ) {
				$desc = wp_kses_data( $desc );
				$option_id = (int) $option_id;
				list( $slug, $instance_id ) = explode( '-', sanitize_text_field( $save_name ) );
				icl_register_string( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id . '-desc', $desc );
			}

			return $desc;
		}


		/**
		 * retrieve translated shipping description.
		 *
		 * @access public
		 * @param string $label
		 * @return string
		 */
		function get_translated_shipping_description( $desc, $option_id, $instance_id ) {

			if( function_exists( 'icl_t' ) ) {
				$desc = wp_kses_data( $desc );
				$option_id = (int) $option_id;
				$instance_id = (int) $instance_id;
			    $desc = icl_t( 'betrs_labels', 'instance_' . $instance_id . '-option_' . $option_id . '-desc', $desc );
			}

			return $desc;
		}

	}

	new BETRS_WPML();

}

?>