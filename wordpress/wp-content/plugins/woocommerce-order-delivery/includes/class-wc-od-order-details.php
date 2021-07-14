<?php
/**
 * Class to handle the delivery date section in the order details and emails templates
 *
 * @author     WooThemes
 * @package    WC_OD
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_OD_Order_Details' ) ) {

	class WC_OD_Order_Details extends WC_OD_Singleton {

		/**
		 * The order ID.
		 *
		 * @since  1.0.0
		 * @access public
		 * @var int The order ID.
		 * @deprecated 1.1.0
		 */
		public $order_id;


		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 * @access protected
		 */
		protected function __construct() {
			parent::__construct();

			// WooCommerce order details hooks.
			add_action( 'woocommerce_view_order', array( $this, 'order_details' ), 20 );
			add_action( 'woocommerce_thankyou', array( $this, 'order_details' ), 20 );

			// WooCommerce mailing hooks.
			add_action( 'woocommerce_email_footer', array( $this, 'email_footer' ), 1 );
		}

		/**
		 * Displays the delivery date section at the end of the order details.
		 *
		 * @since 1.0.0
		 *
		 * @param int $order_id The order ID.
		 */
		public function order_details( $order_id ) {
			$delivery_date = wc_od_get_order_meta( $order_id, '_delivery_date' );

			if ( $delivery_date ) {
				$delivery_date_i18n = wc_od_localize_date( $delivery_date );

				if ( $delivery_date_i18n ) {
					wc_od_order_delivery_details( array(
						'title'         => __( 'Chosen delivery date', 'woocommerce-order-delivery' ),
						'delivery_date' => $delivery_date_i18n,
						'order_id'      => $order_id,
					) );
				}
			}
		}

		/**
		 * We use the email subject filter to capture the order data and
		 * include the delivery date section at the end of the emails.
		 *
		 * @since 1.0.0
		 * @deprecated 1.1.0 The order is fetched from the $email parameter in the 'email_footer' method.
		 *
		 * @param string $email_subject The email subject.
		 * @return string The email subject.
		 */
		public function capture_order( $email_subject ) {
			_deprecated_function( __METHOD__, '1.1.0' );

			return $email_subject;
		}

		/**
		 * Displays the delivery date section at the end of the emails.
		 *
		 * There is no other way compatible with all the WooCommerce versions.
		 *
		 * @since 1.0.0
		 *
		 * @param WC_Email $email The email instance.
		 */
		public function email_footer( $email ) {
			/**
			 * Filter the emails that will have the delivery information.
			 *
			 * @since 1.1.0
			 *
			 * @param array $email_ids An array with the email ids.
			 */
			$email_ids = apply_filters( 'wc_od_emails_with_delivery_details', array(
				'new_order',
				'customer_note',
				'customer_on_hold_order',
				'customer_processing_order',
				'customer_completed_order',
			) );

			if ( in_array( $email->id, $email_ids ) && $email->object instanceof WC_Order ) {
				$delivery_date = wc_od_get_order_meta( $email->object, '_delivery_date' );

				if ( $delivery_date ) {
					$delivery_date_i18n = wc_od_localize_date( $delivery_date );

					if ( $delivery_date_i18n ) {
						/**
						 * Filter the arguments used by the emails/email-delivery-date.php template.
						 *
						 * @since 1.1.0
						 *
						 * @param array $args The arguments.
						 */
						$args = apply_filters( 'wc_od_email_delivery_details_args', array(
							'title'         => __( 'Chosen delivery date', 'woocommerce-order-delivery' ),
							'delivery_date' => $delivery_date_i18n,
							'email'         => $email,
						) );

						wc_od_get_template( 'emails/email-delivery-date.php', $args );
					}
				}
			}
		}
	}
}
