<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix and Match front-end functions and filters.
 *
 * @class 	WC_Mix_and_Match_Display
 * @since   1.0.0
 * @version 1.0.4
 */

class WC_Mix_and_Match_Display {

	private $enqueued_table_item_js = false;

	/**
	 * __construct function.
	 *
	 * @return object
	 */
	public function __construct() {

		// Single product template.
		add_action( 'woocommerce_mix-and-match_add_to_cart', array( $this, 'add_to_cart_template' ) );

		// Add preamble info to bundled products.
		add_filter( 'woocommerce_cart_item_name', array( $this, 'in_cart_item_title' ), 10, 3 );
		add_filter( 'woocommerce_order_item_name', array( $this, 'order_table_item_title' ), 10, 2 );

		// hide Container size meta in my-account.
		add_filter( 'woocommerce_order_items_meta_get_formatted', array( $this, 'order_item_meta' ), 10, 2 );

		// Change the tr class attributes when displaying bundled items in templates.
		add_filter( 'woocommerce_cart_item_class', array( $this, 'cart_item_class' ), 10, 3 );
		add_filter( 'woocommerce_order_item_class', array( $this, 'order_item_class' ), 10, 3 );

		// Front end scripts- validation + price updates.
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

		// QV support.
		add_action( 'wc_quick_view_enqueue_scripts', array( $this, 'quickview_support' ) );

		// indent items in emails.
		add_action( 'woocommerce_email_styles', array( $this, 'email_styles' ) );
	}

	/*-----------------------------------------------------------------------------------*/
	/*  Single Product Display                                                           */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Add-to-cart template for mix & match products.
	 * @return void
	 */
	public function add_to_cart_template() {

		global $product;

		// Enqueue scripts and styles - then, initialize js variables.
		wp_enqueue_script( 'wc-add-to-cart-mnm' );
		wp_enqueue_style( 'wc-mnm-frontend' );

		// Load the add to cart template.
		wc_get_template(
			'single-product/add-to-cart/mnm.php',
			array(
				'container'	      => $product,
				'min_container_size'  => $product->get_min_container_size(),
				'max_container_size'  => $product->get_max_container_size(),
				'mnm_products'    => $product->get_available_children(),
			),
			'',
			WC_Mix_and_Match()->plugin_path() . '/templates/'
		);

	}


	/*-----------------------------------------------------------------------------------*/
	/*  Cart and Order Display                                                           */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Adds title preambles to cart items.
	 *
	 * @param  string   $content
	 * @param  array    $cart_item_values
	 * @param  string   $cart_item_key
	 * @return string
	 */
	public function in_cart_item_title( $content, $cart_item_values, $cart_item_key ) {

		if ( ! empty( $cart_item_values[ 'mnm_container' ] ) ) {
			$this->enqueue_table_item_js();
		}

		return $content;

	}


	/**
	 * Adds bundled item title preambles to order-details template.
	 *
	 * @param  string 	$content
	 * @param  array 	$order_item
	 * @return string
	 */
	public function order_table_item_title( $content, $order_item ) {

		if ( ! empty( $order_item[ 'mnm_container' ] ) ) {

			if ( function_exists( 'is_account_page' ) && is_account_page() || function_exists( 'is_checkout' ) && is_checkout() ) {
				// Thank-you page and orders viewed in my-account.
				$this->enqueue_table_item_js();
			} else {
				// E-mails.
				return '<small>' . $content . '</small>';
			}
		}

		return $content;
	}


	/**
	 * Enqeue js that wraps bundled table items in a div in order to apply indentation reliably.
	 *
	 * @since 1.0.2
	 */
	private function enqueue_table_item_js() {

		if ( ! $this->enqueued_table_item_js ) {
			wc_enqueue_js( "
				var wc_mnm_wrap_mnm_table_item = function() {
					jQuery( '.mnm_table_item td.product-name' ).wrapInner( '<div class=\"mnm_table_item_indent\"></div>' );
				}

				jQuery( 'body' ).on( 'updated_checkout', function() {
					wc_mnm_wrap_mnm_table_item();
				} );

				wc_mnm_wrap_mnm_table_item();
			" );

			$this->enqueued_table_item_js = true;
		}
	}

	/**
	 * Hide the "Container size" meta in the my-account area.
	 *
	 * @param  array  $formatted_meta
	 * @param  obj    $order
	 * @return array
	 */
	public function order_item_meta( $formatted_meta, $order ){
		foreach( $formatted_meta as $id => $meta ){
			if ( $meta['key'] ==  __( 'Container size', 'woocommerce-mix-and-match-products' ) ){
				unset( $formatted_meta[$id] );
			}
		}
		return $formatted_meta;
	}


	/**
	 * Changes the tr class of MNM content items to allow their styling.
	 *
	 * @param  string  $class
	 * @param  array   $values
	 * @param  string  $values_key
	 * @return string
	 */
	public function cart_item_class( $class, $values, $values_key ) {

		if ( isset( $values[ 'mnm_config' ] ) && ! empty( $values[ 'mnm_config' ] ) ) {
			$class .= ' mnm_table_container';
		}

		if ( isset( $values[ 'mnm_container' ] ) && ! empty( $values[ 'mnm_container' ] ) ) {
			$class .= ' mnm_table_item';
		}

		return $class;
	}

	/**
	 * Changes the tr class of MNM content items to allow their styling in orders.
	 *
	 * @param  string    $class
	 * @param  array     $values
	 * @param  WC_Order  $order
	 * @return string
	 */
	public function order_item_class( $class, $values, $order ) {

		if ( isset( $values[ 'mnm_config' ] ) && ! empty( $values[ 'mnm_config' ] ) ) {
			$class .= ' mnm_table_container';
		}

		if ( isset( $values[ 'mnm_container' ] ) && ! empty( $values[ 'mnm_container' ] ) ) {
			$class .= ' mnm_table_item';

			// Find if it's the first/last one and a suitable CSS class.
			$first_child = '';
			$last_child  = '';

			foreach ( $order->get_items( 'line_item' ) as $order_item ) {

				if ( ! empty( $values[ 'mnm_container' ] ) && ! empty( $order_item[ 'mnm_container' ] ) ) {
					if ( $first_child === '' ) {
						$first_child = $order_item;
					}
					$last_child = $order_item;
				}
			}

			if ( $values == $first_child ) {
				$class .= ' mnm_table_item_first';
			}

			if ( $values == $last_child ) {
				$class .= ' mnm_table_item_last';
			}

		}

		return $class;
	}

	/*-----------------------------------------------------------------------------------*/
	/*  Scripts and Styles                                                               */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Load scripts.
	 */
	public function frontend_scripts() {

		wp_register_style( 'wc-mnm-frontend', WC_Mix_and_Match()->plugin_url() . '/assets/css/mnm-frontend.css', array(), WC_Mix_and_Match()->version );
		wp_enqueue_style( 'wc-mnm-frontend' );

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_register_script( 'wc-add-to-cart-mnm', WC_Mix_and_Match()->plugin_url() . '/assets/js/add-to-cart-mnm' . $suffix . '.js', array( 'jquery', 'jquery-blockui', 'wc-add-to-cart-variation' ), WC_Mix_and_Match()->version, true );

		$params = array(
			'i18n_free'                    => __( 'Free!', 'woocommerce-mix-and-match-products' ),
			'i18n_qty_message'             => __( 'You have selected %s items. ', 'woocommerce-mix-and-match-products' ),
			'i18n_qty_message_single'      => __( 'You have selected %s item. ', 'woocommerce-mix-and-match-products' ),
			'i18n_qty_error'               => __( '%vPlease select %s items to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_qty_error_single'        => __( '%vPlease select %s item to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_empty_error'   		   => __( 'Please select at least 1 item to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_min_max_qty_error'      => __( '%vPlease choose between %min and %max items to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_min_qty_error_singular' => __( '%vPlease choose at least %min item to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_min_qty_error'          => __( '%vPlease choose at least %min items to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_max_qty_error_singular' => __( '%vPlease choose fewer than %max item to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'i18n_max_qty_error'          => __( '%vPlease choose fewer than %max items to continue&hellip;', 'woocommerce-mix-and-match-products' ),
			'currency_symbol'              => get_woocommerce_currency_symbol(),
			'currency_position'            => esc_attr( stripslashes( get_option( 'woocommerce_currency_pos' ) ) ),
			'currency_format_num_decimals' => absint( get_option( 'woocommerce_price_num_decimals' ) ),
			'currency_format_decimal_sep'  => esc_attr( stripslashes( get_option( 'woocommerce_price_decimal_sep' ) ) ),
			'currency_format_thousand_sep' => esc_attr( stripslashes( get_option( 'woocommerce_price_thousand_sep' ) ) ),
			'currency_format_trim_zeros'   => false == apply_filters( 'woocommerce_price_trim_zeros', false ) ? 'no' : 'yes',
		);

		wp_localize_script( 'wc-add-to-cart-mnm', 'wc_mnm_params', apply_filters( 'woocommerce_mnm_add_to_cart_parameters', $params ) );

	}

	/**
	 * QuickView scripts init.
	 */
	public function quickview_support() {

		if ( ! is_product() ) {
			$this->frontend_scripts();
			wp_enqueue_script( 'wc-add-to-cart-mnm' );
			wp_enqueue_style( 'wc-mnm-styles' );
		}
	}


	/*-----------------------------------------------------------------------------------*/
	/* Emails */
	/*-----------------------------------------------------------------------------------*/

	/**
	 * Indent bundled items in emails.
	 *
	 * @param  string  $css
	 * @return string
	 */
	function email_styles( $css ) {
		$css = $css . ".mnm_table_item td:nth-child(1) { padding-left: 35px !important; } .mnm_table_item td { border-top: none; }";
		return $css;
	}

} // End class.
