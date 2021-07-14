<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'MailChimp_API' ) ) {
	class WC_Mailchimp_API {

		/**
		 * API Base URL
		 * @var string
		 */
		private $api_url = "https://<dc>.api.mailchimp.com/2.0";

		/**
		 * Mailchimp API Key
		 * @var string
		 */
		private $api_key;

		/**
		 * Mailchimp API Debug Logging Flag
		 * @var bool
		 */
		private $debug = false;

		/**
		 * Constructor
		 * @param string $api_key
		 * @param string $debug
		 */
		public function __construct( $api_key, $debug ) {

			$this->debug = $debug == 'yes';

			if ( $this->debug ) {
				$this->log = new WC_Logger();
			}

			if ( $api_key && ( strpos( $api_key, '-' ) !== FALSE ) ) {
				$this->api_key = $api_key;
				list( , $datacentre ) = explode( '-', $this->api_key );
				$this->api_url = str_replace( '<dc>', $datacentre, $this->api_url );
			}
		}

		/**
		 * If API key enter is wrong, show warning
		 */
		public function show_api_key_error() {
			echo '<div class="error"><p>' . __( 'Invalid MailChimp API key, please check your key and enter it again.', 'woocommerce-mailchimp-integration' ) . '</p></div>';
		}

		/**
		 * Make a call to the API
		 * @param  string $endpoint
		 * @param  array  $body
		 * @param  string $method
		 * @return array
		 */
		private function perform_request( $endpoint, $body = array(), $method = 'POST' ) {

			// Set API key if not set
			if ( ! isset( $body['apikey'] ) ) {
				$body['apikey'] = $this->api_key;
			}

			if ( $body['apikey'] == '' ) {
				return new WP_Error( 'noKey', __( 'Please enter an API Key', 'woocommerce-mailchimp-integration' ) );
			}

			$args = array(
				'method' 	  => $method,
				'timeout'     => apply_filters( 'wc_mailchimp_api_timeout', 45 ), // default to 45 seconds
				'redirection' => 0,
				'httpversion' => '1.0',
				'sslverify'   => false,
				'blocking'    => true,
				'headers'     => array(
					'accept'       	=> 'application/json',
					'content-type' 	=> 'application/json',
				),
				'body'        => json_encode( $body ),
				'cookies'     => array(),
				'user-agent'  => "PHP " . PHP_VERSION . '/WooCommerce'
			);

			$response = wp_remote_request( $this->api_url . $endpoint, $args );

			// if debug enabled, log all requests
			if ( $this->debug ) {
				$this->log->add( 'woocommerce-mailchimp-integration', "MailChimp METHOD: " . $method . " \n BODY: " .  print_r( $body, true ) . " \n RESPONSE: " . print_r( $response, true ) );
			}

			$body = wp_remote_retrieve_body( $response );

			if ( is_wp_error( $body ) ) {
				return false;
			}

			$body = json_decode( $body, true );

			if ( isset( $body['status'] ) && 'error' == $body['status'] ) {
				if ( $body['name'] == 'Invalid_ApiKey' ) {
					add_action( 'admin_notices', array( $this, 'show_api_key_error' ) );
				}
			}

			return $response;
		}

		/**
		 * Send through order details to MailChimp
		 * @param  WC_Order $order
		 * @return bool
		 */
		public function order_add( $order ) {
			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				$order_date = $order->order_date;
			} else {
				$order_date = $order->get_date_created() ? gmdate( 'Y-m-d H:i:s', $order->get_date_created()->getOffsetTimestamp() ) : '';
			}

			$data = array( 'order' => array(
					'id' => version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id(),
					'email' => version_compare( WC_VERSION, '3.0', '<' ) ? $order->billing_email : $order->get_billing_email(),
					'total' => $order->get_total(),
					'order_date' => $order_date,
					'store_id' => md5( get_site_url() ),
					'store_name' => get_bloginfo( 'name' ),
				)
			);

			// Check if order was placed due to lead of a mailchimp campaign
			if ( isset( $_COOKIE['woocommerce-mailchimp-integration'] ) ) {
				$mailchimp_campaign_data = explode( '||', $_COOKIE['woocommerce-mailchimp-integration'] );
				$data['order']['campaign_id'] = $mailchimp_campaign_data[0];
				$data['order']['email_id'] = $mailchimp_campaign_data[1];
			}

			// Get order items and create variable containing all
			$items = array();
			foreach ( $order->get_items() as $item_key => $item ) {
				$cat_id = 0;
				$cat_name = '';
				$item_product = $order->get_product_from_item( $item );

				$all_cats = wp_get_object_terms( $item_product->get_id(), 'product_cat' );
				foreach ( $all_cats as $cat ) {
					if ( $cat->parent >= $cat_id ) {
						$cat_id = $cat->term_id;
					}
				}

				$category_parents = WC_Mailchimp_Integration::get_category_parents( $cat_id );

				if ( is_wp_error( $category_parents ) ) {
					$cat_name = 'none';
				}
				else {
					$cat_name = substr( $category_parents, 0, -2 );
				}

				$product_name = $item_product->get_title();
				$product_sku = $item_product->get_sku();

				if ( '' != $product_sku ) {
					$product_name .= ' | ' . $product_sku;
				}

				$product_name = apply_filters( 'woocommerce_mailchimp_integration_item_title', $product_name, $item_product );

				$items[] = array(
					'product_id' => $item_product->get_id(),
					'sku' => $product_sku,
					'product_name' => $product_name,
					'category_id' => $cat_id,
					'category_name' => $cat_name,
					'qty' => $item['qty'],
					'cost' => $item['line_total']
				);
			}

			$data['order']['items'] = $items;

			$response = $this->perform_request( '/ecomm/order-add.json', $data );

			$response_body = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $response_body ) ) {
				return false;
			}

			$response_json = json_decode( $response_body, true );

			if ( isset( $response_json['status'] ) && 'error' ==  $response_json['status'] ) {
				return false;
			}

			return true;
		}

		/**
		 * Remove order from MailChimp
		 * @param  object $order
		 * @return bool
		 */
		public function order_delete( $order ) {
			$data = array(
				'store_id' => md5( get_site_url() ),
				'order_id' => ( version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id() ),
			);

			$response = $this->perform_request( '/ecomm/order-del.json', $data );

			$response = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response = json_decode( $response, true );

			if ( isset( $response['status'] ) && 'error' ==  $response['status'] ) {
				return false;
			}

			return true;
		}

		/**
		 * Get a list of email lists
		 * @return bool|array
		 */
		public function get_lists() {
			$response = $this->perform_request( '/lists/list.json' );

			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response, true );

			if ( isset( $response['status'] ) && 'error' == $response['status'] ) {
				return false;
			}

			return $response;
		}

		/**
		 * Subscribe customer to email list
		 * @param  string $list_id
		 * @param  object $order
		 * @param  string $double_optin
		 * @param  array  $groupings
		 * @return bool
		 */
		public function subscribe_to_list( $list_id, $order, $double_optin, $groupings = array() ) {

			$pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );

			$data = array(
				'id' => $list_id,
				'email' => array( 'email' => $order->billing_email ),
				'merge_vars' => array(
					'FNAME' => $pre_wc_30 ? $order->billing_first_name : $order->get_billing_first_name(),
					'LNAME' => $pre_wc_30 ? $order->billing_last_name : $order->get_billing_last_name(),
					'ADDRESS' => array(
						'addr1'   => $pre_wc_30 ? $order->billing_address_1 : $order->get_billing_address_1(),
						'addr2'   => $pre_wc_30 ? $order->billing_address_2 : $order->get_billing_address_2(),
						'city'    => $pre_wc_30 ? $order->billing_city : $order->get_billing_city(),
						'state'   => $pre_wc_30 ? $order->billing_state : $order->get_billing_state(),
						'zip'     => $pre_wc_30 ? $order->billing_postcode : $order->get_billing_postcode(),
						'country' => $pre_wc_30 ? $order->billing_country : $order->get_billing_country(),
					)
				),
				'double_optin' => $double_optin,
				'update_existing' => true,
			);

			if ( ! empty( $groupings ) ) {
				$data['merge_vars']['groupings'] = array( $groupings );
			}

			$response = $this->perform_request( '/lists/subscribe.json', $data );

			$response = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response = json_decode( $response, true );

			if ( isset( $response['status'] ) && 'error' ==  $response['status'] ) {
				return false;
			}

			return true;
		}

		/**
		 * Subscribe to list by email address only
		 * @param  string      $list_id   [description]
		 * @param  string      $email     [description]
		 * @param  array       $groupings [description]
		 * @param  string|bool $location  [description]
		 * @return boolean           [description]
		 */
		public function subscribe_to_list_by_email( $list_id, $email, $groupings = array(), $location = false ) {
			$data = array(
				'id' => $list_id,
				'email' => array( 'email' => $email ),
				'double_optin' => false,
				'update_existing' => true,
			);

			if ( ! empty( $groupings ) ) {
				$data['merge_vars']['groupings'] = array( $groupings );
			}

			if ( $location ) {
				$data['merge_vars']['SIGNUPPAGE'] = $location;
			}

			$response = $this->perform_request( '/lists/subscribe.json', $data );

			$response = wp_remote_retrieve_body( $response );
			if ( is_wp_error( $response ) ) {
				return false;
			}

			$response = json_decode( $response, true );

			if ( isset( $response['status'] ) && 'error' ==  $response['status'] ) {
				return false;
			}

			return true;
		}
	}
}
