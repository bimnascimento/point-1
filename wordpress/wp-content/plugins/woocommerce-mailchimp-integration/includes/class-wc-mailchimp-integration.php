<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Mailchimp_Integration' ) ) {

	class WC_Mailchimp_Integration extends WC_Integration {

		/**
		 * Main plugin file
		 * @var string
		 */
		public $file;

		/**
		 * Version number
		 * @var string
		 */
		public $version;

		/**
		 * Instance of the class
		 * @var Object
		 */
		private static $instance = null;

		/**
		 * Instance of the API class.
		 * @var Object
		 */
		private static $api = null;

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->id = 'mailchimp';
			$this->method_title          = __( 'MailChimp', 'woocommerce-mailchimp-integration' );
			$this->method_description    = sprintf( __( 'MailChimp lets you manage your email campaigns. %sStart%s a MailChimp Campaign now!', 'woocommerce-mailchimp-integration' ), '<a href="https://admin.mailchimp.com/campaigns/create" target="_blank">', '</a>' );

			if ( is_admin() ) {
				// Load the settings
				$this->init_form_fields();
			}

			// Update the settings fields
			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'process_admin_options' ) );

			// Reload the settings fields
			add_action( 'woocommerce_update_options_integration_' . $this->id, array( $this, 'reload_settings_fields' ), 10 );

			// Add subscribe to newsletter checkbox at checkout
			add_action( 'woocommerce_review_order_before_submit' , array( $this , 'checkout_subscribe_checkbox' ) );

			// Send order data through to Mailchimp
			add_action( 'woocommerce_checkout_order_processed', array( $this, 'send_order_data' ), 10, 2 );

			// Remove order data when order refunded
			add_action( 'woocommerce_order_status_refunded', array( $this, 'remove_order_data' ), 10, 1 );

			// Remove and Re-add order when saving order
			add_action( 'save_post', array( $this, 'resend_order_data_on_save' ), 10, 2 );

			// add ecommerce campaign tracking
			add_action( 'init', array( $this, 'add_eCommerce360_cookie' ), 2, 0 );
		}

		public function reload_settings_fields() {
			$this->init_form_fields();
		}

		/**
		 * Main plugin instance, ensure only 1 instance is loaded.
		 * @param  string $version
		 * @param  string $file
		 * @return Object
		 */
		public static function instance ( $version, $file ) {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self( $version, $file );
			}
			return self::$instance;
		}

		/**
		 * Cloning is forbidden.
		 */
		public function __clone () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 */
		public function __wakeup () {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
		}

		/**
		 * Main API Instance, ensure only 1 API instance is loaded.
		 * @return Object
		 */
		public function get_api() {
			if ( is_null( self::$api ) ) {
				$api_key = $this->get_option( 'wc_mailchimp_api_key' );
				if ( $api_key == '' ) {
					return false;
				}
				require_once( 'class-wc-mailchimp-api.php' );
				self::$api = new WC_Mailchimp_API( $api_key, $this->get_option( 'wc_mailchimp_debug' ) );
			}
			return self::$api;
		}


		/**
		* get_option function.
		*
		* Gets and option from the settings API, using defaults if necessary to prevent undefined notices.
		*
		* @access public
		* @param string $key
		* @param mixed $empty_value
		* @return string The value specified for the option or a default value for the option
		*/
		public function get_option( $key, $empty_value = null ) {
			if ( empty( $this->settings ) ) {
				$this->init_settings();
			}

		// Get option default if unset
		if ( !isset( $this->settings[$key] ) ) {
			$form_fields = $this->get_form_fields();
			$this->settings[$key] = isset( $form_fields[$key]['default'] ) ? $form_fields[$key]['default'] : '';
		}

		if ( !is_null( $empty_value ) && empty( $this->settings[$key] ) )
			$this->settings[$key] = $empty_value;
			return $this->settings[$key];
		}

		/**
		 * Add subscribe to newsletter checkbox before checkout button
		 * @return bool
		 */
		public function checkout_subscribe_checkbox() {

			$wc_mailchimp_list_id = $this->get_option( 'wc_mailchimp_list_id', 0 );

			if ( 0 === $wc_mailchimp_list_id ) {
				return false;
			}

			// Ensure we still have an active connection
			$lists = $this->fetch_mailchimp_lists();

			if ( ! isset( $lists[ $wc_mailchimp_list_id ] ) ) {
				//List no longer exists on MailChimp
				return false;
			}

			$subscribe_checked = apply_filters( 'woocommerce_mailchimp_integration_subscribe_checked_by_default', 'yes' === $this->get_option( 'wc_mailchimp_subscribe_checkbox_checked_by_default', 'no' ) );

			// Hide unless an API key has been entered
			if ( $lists !== false ) {
				do_action( 'woocommerce_mailchimp_integration_before_subscribe_form' );
				echo '<p class="form-row checkout-subscribe-prompt clear">' . esc_html( $this->get_option( 'wc_mailchimp_text_heading' ) ) . '</p>' . "\n";
				echo '<p class="form-row checkout-subscribe-action"><label for="mailchimp-subscribe"><input type="checkbox" name="mailchimp-subscribe" id="mailchimp-subscribe" value="yes" ' . checked( $subscribe_checked, true, false ) . ' /> ' . esc_html( $this->get_option( 'wc_mailchimp_text_label' ) ) . '</label></p>' . "\n";
				do_action( 'woocommerce_mailchimp_integration_after_subscribe_form' );
			}

			return true;
		}


		/**
		 * Send through order data to mailchimp
		 * @param  int $order_id
		 * @param  array $posted
		 * @return bool
		 */
		public function send_order_data( $order_id, $posted ) {
			// Always send order data to MailChimp
			$order = wc_get_order( $order_id );

			$api = $this->get_api();
			if ( ! $api ) {
				return false;
			}

			$api->order_add( $order );

			if ( isset( $_POST['mailchimp-subscribe'] ) && 'yes' == $_POST['mailchimp-subscribe'] ) {
				$list_id = $this->get_option( 'wc_mailchimp_list_id' );

				$double_optin = $this->get_option( 'wc_mailchimp_double_optin' ) == 'yes';

				$api->subscribe_to_list( $list_id, $order, $double_optin );
			}

			return true;
		}

		/**
		 * Remove order data from Mailchimp when order refunded
		 * @param  int $order_id
		 * @return bool
		 */
		public function remove_order_data( $order_id ) {
			$order = wc_get_order( $order_id );
			$api = $this->get_api();

			if ( ! $order || ! $api ) {
				return false;
			}

			$api->order_delete( $order );
			return true;
		}

		/**
		 * Remove and re-add order on mailchimp when updating order
		 * @param  int $post_id
		 * @param  WP_Post $post
		 * @return bool
		 */
		public function resend_order_data_on_save( $post_id, $post ) {
			if ( ! $_POST || is_int( wp_is_post_revision( $post_id ) ) || is_int( wp_is_post_autosave( $post_id ) ) ) {
				return false;
			}

			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return false;
			}

			if ( $post->post_type != 'shop_order' ) {
				return false;
			}

			$order = wc_get_order( $post_id );
			if ( ! $order ) {
				return false;
			}

			$api = $this->get_api();
			if ( ! $api ) {
				return false;
			}

			$api->order_delete( $order );
			$api->order_add( $order );

			return true;
		}

		/**
		 * Define the MailChimp setting fields
		 * @return array
		 */
		public function init_form_fields() {

		$form_fields = array();

		$form_fields['wc_mailchimp_api_key'] = array(
			'title'       => __( 'MailChimp API Key', 'woocommerce-mailchimp-integration' ),
			'description' => sprintf( __( 'Your %1$sMailChimp API key%2$s.', 'woocommerce-mailchimp-integration' ), '<a href="http://kb.mailchimp.com/accounts/management/about-api-keys/" title="' . __( 'Find out where to get your Mailchimp API key.', 'woocommerce-mailchimp-integration' ) . '">', '</a>' ),
			'type'        => 'text',
			'placeholder' => '',
			'default'     => '',
		);

		$api_key = $this->get_option( 'wc_mailchimp_api_key' );
		$lists = $this->fetch_mailchimp_lists();

		// Hide unless an API key has been entered
		if ( $api_key && $lists !== false ) {
			$form_fields['wc_mailchimp_list_id'] = array(
				'title'       => __( 'MailChimp List', 'woocommerce-mailchimp-integration' ),
				'description' => __( 'The MailChimp list to subscribe customers to at checkout.', 'woocommerce-mailchimp-integration' ),
				'type'        => 'select',
				'default'     => '',
				'options' 	  => $lists,
			);

			$form_fields['wc_mailchimp_text_heading'] = array(
				'title'       => __( 'Checkout Subscription Text', 'woocommerce-mailchimp-integration' ),
				'description' => __( 'This text will be shown above the subscription checkbox', 'woocommerce-mailchimp-integration' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => __( 'Want updates about our products and promotions?', 'woocommerce-mailchimp-integration' ),
			);

			$form_fields['wc_mailchimp_text_label'] = array(
				'title'       => __( 'Checkbox Label Text', 'woocommerce-mailchimp-integration' ),
				'description' => __( 'This text will be shown beside the checkbox', 'woocommerce-mailchimp-integration' ),
				'type'        => 'text',
				'placeholder' => '',
				'default'     => __( 'Subscribe', 'woocommerce-mailchimp-integration' ),
			);

			$form_fields['wc_mailchimp_subscribe_checkbox_checked_by_default'] = array(
				'title'       => __( 'Subscribe Checkbox Default to Checked', 'woocommerce-mailchimp-integration' ),
				'description' => __( 'If checked, the subscribe checkbox, on checkout page, will be checked by default', 'woocommerce-mailchimp-integration' ),
				'type'        => 'checkbox',
				'default'     => 'no',
			);

			$form_fields['wc_mailchimp_double_optin'] = array(
				'title'       => __( 'Double Opt-In', 'woocommerce-mailchimp-integration' ),
				'label'       => __( 'Enable Double Opt-In', 'woocommerce-mailchimp-integration' ),
				'description' => __( 'Users who signup will receive an email first asking them to confirm their subscription.', 'woocommerce-mailchimp-integration' ),
				'type'        => 'checkbox',
				'default'     => 'no'
			);

			$debug = $this->get_option( 'wc_mailchimp_debug' ) == 'yes';

			$label = __( 'Enable Logging', 'woocommerce-mailchimp-integration' );
			$description = __( 'Enable logging of errors from the MailChimp API events.', 'woocommerce-mailchimp-integration' );

			if ( defined( 'WC_LOG_DIR' ) ) {
				$log_url = add_query_arg( 'tab', 'logs', add_query_arg( 'page', 'wc-status', admin_url( 'admin.php' ) ) );
				$log_key = 'woocommerce-mailchimp-integration-' . sanitize_file_name( wp_hash( 'woocommerce-mailchimp-integration' ) ) . '-log';
				$log_url = add_query_arg( 'log_file', $log_key, $log_url );

				$label .= ' | ' . sprintf( __( '%1$sView Log%2$s', 'woocommerce-mailchimp-integration' ), '<a href="' . esc_url( $log_url ) . '">', '</a>' );
			}

			$form_fields['wc_mailchimp_debug'] = array(
				'title'       => __( 'Debug Log', 'woocommerce-mailchimp-integration' ),
				'label'       => $label,
				'description' => $description,
				'type'        => 'checkbox',
				'default'     => 'no'
			);

		}

		$this->form_fields = $form_fields;

		}

		public function no_mailchimp_lists_notice() {
			echo '<div class="error"><p>' . __( 'You must first create a mailing list in your MailChimp account.', 'woocommerce-mailchimp-integration' ) . '</p></div>';
		}

		/**
		 * Retrieve all the mailchimp lists for the API key
		 * @return array
		 */
		public function fetch_mailchimp_lists() {

			if ( $this->get_api() ) {
				$lists = $this->get_api()->get_lists();
			}
			else {
				return false;
			}

			if ( $lists !== false ) {
				if ( $lists['total'] == 0 ) {
					$default = array(
						'' => __( 'You have no MailChimp lists...', 'woocommerce-mailchimp-integration' ),
					);
					add_action( 'admin_notices', array( $this, 'no_mailchimp_lists_notice' ) );
				}
				else {
					$default = array(
						'' => __( 'Please select list', 'woocommerce-mailchimp-integration' ),
					);
				}
				$lists_array = array();
				foreach ( $lists['data'] as $list ) {
					$lists_array[ $list['id'] ] = $list['name'];
				}
				return array_merge( $default, $lists_array );
			}

			return false;
		}

		/**
		 * Build parent category text chain
		 *
		 * @param  int     $id
		 * @param  string  $separator
		 * @param  boolean $nice_name
		 * @param  array   $visited
		 *
		 * @return string
		 */
		public static function get_category_parents( $id, $separator = ' - ', $nice_name = false, $visited = array() ) {
			$chain = '';
			$parent = get_term( $id, 'product_cat' );
			if ( is_wp_error( $parent ) )
				return $parent;

			if ( $nice_name ) {
				$name = $parent->slug;
			}
			else {
				$name = $parent->name;
			}

			if ( $parent->parent && ( $parent->parent != $parent->term_id ) && ! in_array( $parent->parent, $visited ) ) {
				$visited[] = $parent->parent;
				$chain .= self::get_category_parents( $parent->parent, $separator, $nice_name, $visited );
			}

			$chain .= $name . $separator;
			return $chain;
		}

		/**
		 * This function listens for query parameters in order to initiate the
		 * eCommerce tracking session.
		 *
		 * @since
		 */
		public function add_eCommerce360_cookie () {

			if ( isset($_COOKIE['woocommerce-mailchimp-integration'] )
			    || ! isset( $_GET['mc_cid'] )
				|| ! isset( $_GET['mc_eid'] ) ) {
				return;
			}

			$mailchimp_campaign_id              = sanitize_text_field( $_GET['mc_cid'] );
			$mailchimp_email_id                 = sanitize_text_field( $_GET['mc_eid'] );
			$mailchimp_integration_cookie_value = $mailchimp_campaign_id . '||' . $mailchimp_email_id;
			setcookie( 'woocommerce-mailchimp-integration', $mailchimp_integration_cookie_value, time() + 30 * DAY_IN_SECONDS, '/' );
		}
	}
}
