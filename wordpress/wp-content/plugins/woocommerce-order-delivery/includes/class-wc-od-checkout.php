<?php
/**
 * Class to handle the plugin behaviour in the checkout page
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Checkout' ) ) {

	class WC_OD_Checkout extends WC_OD_Singleton {

		/**
		 * The first allowed date for ship an order.
		 *
		 * Calculate this data is a heavy process, so we defined this property
		 * to store the value and execute the process just one time per request.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var int A timestamp representing the first allowed date to ship an order.
		 */
		private $first_shipping_date;

		/**
		 * The first allowed date for deliver an order.
		 *
		 * Calculate this data is a heavy process, so we defined this property
		 * to store the value and execute the process just one time per request.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var int A timestamp representing the first allowed date to deliver an order.
		 */
		private $first_delivery_date;


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			parent::__construct();

			// WP Hooks.
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
			add_action( 'wp_footer', array( $this, 'print_calendar_settings' ) );

			// WooCommerce hooks.
			add_action( 'woocommerce_checkout_shipping', array( $this, 'checkout_content' ), 99 );
			add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_delivery_date' ) );
			add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'add_calendar_settings_fragment' ) );

			if ( version_compare( WC()->version, '3.0', '<' ) ) {
				add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'update_order_meta' ) );
			} else {
				add_action( 'woocommerce_checkout_create_order', array( $this, 'update_order_meta' ) );
			}
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.0.0
		 */
		public function enqueue_scripts() {
			if ( is_checkout() && WC()->cart->needs_shipping() && 'calendar' === WC_OD()->settings()->get_setting( 'checkout_delivery_option' ) ) {
				wc_od_enqueue_datepicker( 'checkout' );
				wp_enqueue_script( 'wc-od-checkout', WC_OD_URL . 'assets/js/wc-od-checkout.js', array( 'jquery', 'wc-od-datepicker' ), WC_OD_VERSION, true );
			}
		}

		/**
		 * Gets the delivery date field arguments.
		 *
		 * @since 1.0.1
		 *
		 * @return array An array with the delivery date field arguments.
		 */
		public function get_delivery_date_field_args() {
			return wc_od_get_delivery_date_field_args( array(), 'checkout' );
		}

		/**
		 * Adds the custom content to the checkout form.
		 *
		 * @since 1.0.0
		 */
		public function checkout_content() {
			if ( is_checkout() && WC()->cart->needs_shipping() ) {
				$checkout = WC()->checkout();

				/**
				 * Filter the arguments used by the checkout/form-delivery-date.php template.
				 *
				 * @since 1.1.0
				 *
				 * @param array $args The arguments.
				 */
				$args = apply_filters( 'wc_od_checkout_delivery_details_args', array(
					'title'               => __( 'Shipping and delivery', 'woocommerce-order-delivery' ),
					'checkout'            => $checkout,
					'delivery_date_field' => woocommerce_form_field( 'delivery_date', $this->get_delivery_date_field_args(), $checkout->get_value( 'delivery_date' ) ),
					'delivery_option'     => WC_OD()->settings()->get_setting( 'checkout_delivery_option' ),
					'shipping_date'       => wc_od_localize_date( $this->get_first_shipping_date() ),
					'delivery_range'      => WC_OD()->settings()->get_setting( 'delivery_range' ),
				) );

				wc_od_get_template( 'checkout/form-delivery-date.php', $args );
			}
		}

		/**
		 * Gets the arguments used to calculate the delivery date.
		 *
		 * @since 1.1.0
		 *
		 * @return array An array with the arguments.
		 */
		public function get_delivery_date_args() {
			$today           = wc_od_get_local_date();
			$start_timestamp = strtotime( $this->min_delivery_days() . ' days', $today );
			$end_timestamp   = strtotime( ( $this->max_delivery_days() + 1 ) . ' days', $today ); // Non-inclusive.

			/**
			 * Filter the arguments used to calculate the delivery date.
			 *
			 * @since 1.1.0
			 *
			 * @param array $args The arguments.
			 */
			return apply_filters( 'wc_od_delivery_date_args', array(
				'start_date'         => $start_timestamp,
				'end_date'           => $end_timestamp,
				'delivery_days'      => WC_OD()->settings()->get_setting( 'delivery_days' ),
				'disabled_days_args' => array(
					'type'    => 'delivery',
					'start'   => date( 'Y-m-d', $start_timestamp ),
					'end'     => date( 'Y-m-d', $end_timestamp ),
					'country' => WC()->customer->get_shipping_country(),
					'state'   => WC()->customer->get_shipping_state(),
				),
			) );
		}

		/**
		 * Gets the calendar settings.
		 *
		 * @since 1.1.0
		 *
		 * @return array An array with the calendar settings.
		 */
		public function get_calendar_settings() {
			$seconds_in_a_day = 86400;
			$date_format      = wc_od_get_date_format( 'php' );
			$args             = $this->get_delivery_date_args();

			return wc_od_get_calendar_settings( array(
				'startDate'          => wc_od_localize_date( $args['start_date'], $date_format ),
				'endDate'            => wc_od_localize_date( ( wc_od_get_timestamp( $args['end_date'] ) - $seconds_in_a_day ), $date_format ), // Inclusive.
				'daysOfWeekDisabled' => array_keys( wc_od_get_days_by( $args['delivery_days'], 'enabled', '0' ) ),
				'datesDisabled'      => wc_od_get_disabled_days( $args['disabled_days_args'], 'checkout' ),
			), 'checkout' );
		}

		/**
		 * Prints the script with the calendar settings.
		 *
		 * NOTE: This script is equivalent to use the wp_localize_script(), but adding an id attribute to the script tag.
		 * This id is necessary to identify the script to refresh on the update_order_review_fragments action.
		 *
		 * @since 1.1.0
		 */
		public function print_calendar_settings() {
			if ( ! is_checkout() || ! WC()->cart->needs_shipping() || 'calendar' !== WC_OD()->settings()->get_setting( 'checkout_delivery_option' ) ) {
				return;
			}

			$settings = $this->get_calendar_settings();
			?>
			<script id="wc_od_checkout_l10n" type="text/javascript">
				/* <![CDATA[ */
				var wc_od_checkout_l10n = <?php echo wp_json_encode( $settings ); ?>;
				/* ]]> */
			</script>
			<?php
		}

		/**
		 * Adds the calendar settings fragment.
		 *
		 * NOTE: Allow refresh the calendar settings when the checkout form change.
		 *
		 * @since 1.1.0
		 *
		 * @param array $fragments The fragments to update in the checkout form.
		 * @return mixed An array with the checkout fragments.
		 */
		public function add_calendar_settings_fragment( $fragments ) {
			ob_start();
			$this->print_calendar_settings();
			$wc_od_checkout_settings = trim( ob_get_clean() );

			$fragments['#wc_od_checkout_l10n'] = $wc_od_checkout_settings;

			return $fragments;
		}

		/**
		 * Validates the delivery date field on the checkout process.
		 *
		 * TODO: Use the $data and $errors parameters when the minimum WC version is 3.0+.
		 *
		 * @since 1.0.0
		 */
		public function validate_delivery_date() {
			$field_args    = $this->get_delivery_date_field_args();
			$delivery_date = ( isset( $_POST['delivery_date'] ) ? sanitize_text_field( $_POST['delivery_date'] ) : '' );

			// Validation: Required field.
			if ( isset( $field_args['required'] ) && $field_args['required'] && ! $delivery_date ) {

				wc_add_notice( __( '<strong>Delivery Date</strong> is a required field.', 'woocommerce-order-delivery' ), 'error' );

			// Validation: Invalid date.
			} elseif ( $delivery_date && ! wc_od_validate_delivery_date( $_POST['delivery_date'], $this->get_delivery_date_args(), 'checkout' ) ) {

				wc_add_notice( __( '<strong>Delivery Date</strong> is not a valid date.', 'woocommerce-order-delivery' ), 'error' );

			}
		}

		/**
		 * Validates if the day of the week is enabled for the delivery.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 This validation is done in the 'validate_delivery_date' method.
		 *
		 * @param boolean $valid Is valid the delivery date?
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_delivery_day( $valid ) {
			_deprecated_function( __METHOD__, '1.1.0' );

			return $valid;
		}

		/**
		 * Validates if the minimum days for the delivery is satisfied.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 This validation is done in the 'validate_delivery_date' method.
		 *
		 * @param boolean $valid Is valid the delivery date?
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_minimum_days( $valid ) {
			_deprecated_function( __METHOD__, '1.1.0' );

			return $valid;
		}

		/**
		 * Validates if the maximum days for the delivery is satisfied.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 This validation is done in the 'validate_delivery_date' method.
		 *
		 * @param boolean $valid Is valid the delivery date?
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_maximum_days( $valid ) {
			_deprecated_function( __METHOD__, '1.1.0' );

			return $valid;
		}

		/**
		 * Validates that not exists events for the delivery date.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 This validation is done in the 'validate_delivery_date' method.
		 *
		 * @param boolean $valid Is valid the delivery date?
		 * @return boolean True if the delivery date is valid. False otherwise.
		 */
		public function validate_no_events( $valid ) {
			_deprecated_function( __METHOD__, '1.1.0' );

			return $valid;
		}

		/**
		 * Gets the delivery date to save with the order.
		 *
		 * @since 1.1.0
		 *
		 * @return string|false The delivery date string. False otherwise.
		 */
		public function get_order_delivery_date() {
			$delivery_date = false;

			// The delivery date posted by the customer.
			if ( isset( $_POST['delivery_date'] ) && $_POST['delivery_date'] ) {
				$delivery_date = strtotime( $_POST['delivery_date'] );
			// Assigns a delivery date automatically.
			} elseif ( 'auto' === WC_OD()->settings()->get_setting( 'delivery_date_field' ) ) {
				$delivery_date = wc_od_get_first_delivery_date( array(
					'end_date' => strtotime( ( $this->max_delivery_days() + 1 ) . ' days', wc_od_get_local_date() ), // Non-inclusive.
					'disabled_days_args' => array(
						'type'    => 'delivery',
						'country' => WC()->customer->get_shipping_country(),
						'state'   => WC()->customer->get_shipping_state(),
					),
				), 'checkout-auto' );
			}

			if ( $delivery_date ) {
				// Stores the date in the ISO 8601 format.
				$delivery_date = wc_od_localize_date( $delivery_date, 'Y-m-d' );
			}

			return $delivery_date;
		}

		/**
		 * Saves the order delivery date during checkout.
		 *
		 * Accepts a post object since 1.1.0 to add compatibility with WC 3.0+.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed $the_order Post object or post ID of the order.
		 */
		public function update_order_meta( $the_order ) {
			$delivery_date = $this->get_order_delivery_date();

			if ( $delivery_date ) {
				wc_od_update_order_meta( $the_order, '_delivery_date', $delivery_date, false );
			}
		}

		/**
		 * Gets the first day to ship the orders.
		 *
		 * @since 1.0.0
		 *
		 * @return int A timestamp representing the first allowed date to ship the orders.
		 */
		public function get_first_shipping_date() {
			if ( ! $this->first_shipping_date ) {
				$this->first_shipping_date = wc_od_get_first_shipping_date( array(), 'checkout' );
			}

			return $this->first_shipping_date;
		}

		/**
		 * Gets the first day to deliver the orders.
		 *
		 * @since 1.0.0
		 *
		 * @return int A timestamp representing the first allowed date to deliver the orders.
		 */
		public function get_first_delivery_date() {
			if ( ! $this->first_delivery_date ) {
				$this->first_delivery_date = wc_od_get_first_delivery_date( array(
					'shipping_date' => $this->get_first_shipping_date(),
				), 'checkout' );
			}

			return $this->first_delivery_date;
		}

		/**
		 * Gets the minimum days for delivery.
		 *
		 * @since 1.0.0
		 *
		 * @return int The minimum days for delivery.
		 */
		public function min_delivery_days() {
			$seconds_in_a_day  = 86400;
			$min_delivery_days = ( ( $this->get_first_delivery_date() - wc_od_get_local_date() ) / $seconds_in_a_day );

			/**
			 * Filters the minimum days for delivery.
			 *
			 * @since 1.0.0
			 *
			 * @param int $min_delivery_days The minimum days for delivery.
			 */
			$min_delivery_days = apply_filters( 'wc_od_min_delivery_days', $min_delivery_days );

			return intval( $min_delivery_days );
		}

		/**
		 * Gets the maximum days for delivery.
		 *
		 * @since 1.0.0
		 *
		 * @return int The maximum days for delivery.
		 */
		public function max_delivery_days() {
			/**
			 * Filters the maximum days for delivery.
			 *
			 * @since 1.0.0
			 *
			 * @param int $max_delivery_days The maximum days for delivery.
			 */
			$max_delivery_days = apply_filters( 'wc_od_max_delivery_days', WC_OD()->settings()->get_setting( 'max_delivery_days' ) );

			return intval( $max_delivery_days );
		}
	}
}
