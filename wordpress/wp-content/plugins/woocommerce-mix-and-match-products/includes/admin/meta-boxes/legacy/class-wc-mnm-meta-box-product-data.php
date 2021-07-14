<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix and Match Admin Class
 *
 * Loads admin tabs and adds related hooks / filters.
 * Adds and save product meta.
 *
 * @class   WC_Mix_and_Match_Admin_Meta_Boxes
 * @since 	1.2.0
 * @version 1.2.0
 */
class WC_Mix_and_Match_Admin_Meta_Boxes {

	/**
	 * Bootstraps the class and hooks required.
	 * @return void
	 */
	public static function init() {

		// Allows the selection of the 'mix and match' type.
		add_filter( 'product_type_selector', array( __CLASS__, 'product_selector_filter' ) );

		// Per-item pricing and shipping options.
		add_filter( 'product_type_options', array( __CLASS__, 'type_options' ) );

		// Creates the admin panel tab.
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );

		// Adds the base price fields.
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'base_price_options' ) );

		// Adds the mnm admin options.
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'container_size_options' ), 10 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'allowed_contents_options' ), 20 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'pricing_options' ), 30 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'shipping_options' ), 40 );

		// Creates the panel for selecting product options.
		add_action( 'woocommerce_product_write_panels', array( __CLASS__, 'product_write_panel' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_process_product_meta_mix-and-match', array( __CLASS__, 'process_meta' ) );
	}


	/**
	 * Adds the 'mix and match product' type to the product types dropdown.
	 *
	 * @param  array 	$options
	 * @return array
	 */
	public static function product_selector_filter( $options ) {
		$options[ 'mix-and-match' ] = __( 'Mix & Match product', 'woocommerce-mix-and-match-products' );
		return $options;
	}


	/**
	 * Mix-and-match type options.
	 *
	 * @param  array    $options
	 * @return array
	 */
	public static function type_options( $options ) {

		$options[ 'virtual' ][ 'wrapper_class' ]      .= ' show_if_mix-and-match';
		$options[ 'downloadable' ][ 'wrapper_class' ] .= ' show_if_mix-and-match';

		return $options;
	}


	/**
	 * Adds the MnM Product write panel tabs.
	 *
	 * @param  array $tabs
	 * @return string
	 */
	public static function product_data_tab( $tabs ) {

		$tabs[ 'mnm_options' ] = array(
			'label'  => __( 'Mix & Match', 'woocommerce-mix-and-match-products' ),
			'target' => 'mnm_product_data',
			'class'  => array( 'show_if_mix-and-match', 'mnm_product_tab', 'mnm_product_options' )
		);

		$tabs[ 'inventory' ][ 'class' ][] = 'show_if_mix-and-match';

		return $tabs;
	}


	/**
	 * Write panel.
	 *
	 * @return html
	 */
	public static function product_write_panel() {
		global $post;

		?>
		<div id="mnm_product_data" class="mnm_panel panel woocommerce_options_panel wc-metaboxes-wrapper">
			<div class="options_group mix_and_match">

				<?php do_action( 'woocommerce_mnm_product_options', $post->ID ); ?>

			</div> <!-- options group -->
		</div>

	<?php
	}


	/**
	 * Adds the container size option writepanel options.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.0.7
	 */
	public static function container_size_options( $post_id ) {
		woocommerce_wp_text_input( array(
			'id'            => '_mnm_min_container_size',
			'label'         => __( 'Minimum Container Size', 'woocommerce-mix-and-match-products' ),
			'wrapper_class' => 'mnm_container_size_options',
			'description'   => __( 'Minimum quantity for Mix and Match containers.', 'woocommerce-mix-and-match-products' ),
			'type'          => 'number',
			'desc_tip'      => true
		) );
		woocommerce_wp_text_input( array(
			'id'            => '_mnm_max_container_size',
			'label'         => __( 'Maximum Container Size', 'woocommerce-mix-and-match-products' ),
			'wrapper_class' => 'mnm_container_size_options',
			'description'   => __( 'Maximum quantity for Mix and Match containers. Leave blank to not enforce an upper quantity limit.', 'woocommerce-mix-and-match-products' ),
			'type'          => 'number',
			'desc_tip'      => true
		) );
	}


	/**
	 * Adds allowed contents select2 writepanel options.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.0.7
	 */
	public static function allowed_contents_options( $post_id ) { ?>

		<p id="mnm_allowed_contents_options" class="form-field">
			<label for="mnm_allowed_contents"><?php _e( 'Mix & Match Products', 'woocommerce-mix-and-match-products' ); ?></label>

			<?php

			// generate some data for the select2 input.

			$mnm_data = get_post_meta( $post_id, '_mnm_data', true );

			$product_ids = array_filter( array_map( 'absint', array_keys( (array)$mnm_data ) ) );
			$json_ids    = array();

			foreach ( $product_ids as $product_id ) {
				$product = wc_get_product( $product_id );

				if ( $product ) {
					$json_ids[ $product_id ] = rawurldecode( $product->get_formatted_name() );
				}
			}

			// Select2 posts value as comma-delimited list of IDs.
			$value = implode( ',', array_keys( $json_ids ) );

			// json-encode the json IDs for Select2.
			$json = json_encode( $json_ids );

			?>

			<input type="hidden" class="wc-product-search" style="width: 50%;" name="mnm_allowed_contents" data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-mix-and-match-products' ); ?>" data-allow_clear="true" data-multiple="true" data-action="woocommerce_json_search_products_and_variations" data-selected="<?php echo esc_attr( $json ); ?>" value="<?php echo $value; ?>" />
		</p>
	<?php
	}


	/**
	 * Adds the base and sale price option writepanel options.
	 *
	 * @return void
	 * @since  1.0.6
	 */
	public static function base_price_options() {

		global $thepostid;

		// Base Price fields to copy.
		$base_regular_price = get_post_meta( $thepostid, '_mnm_base_regular_price', true );
		$base_sale_price    = get_post_meta( $thepostid, '_mnm_base_sale_price', true );

		?><div class="wc_mnm_price_fields" style="display:none">
			<input type="hidden" id="_wc_mnm_base_regular_price" name="wc_mnm_base_regular_price_flip" value="<?php echo wc_format_localized_price( $base_regular_price ); ?>"/>
			<input type="hidden" id="_wc_mnm_base_sale_price" name="wc_mnm_base_sale_price_flip" value="<?php echo wc_format_localized_price( $base_sale_price ); ?>"/>
		</div><?php
	}


	/**
	 * Adds the MnM per-item pricing option.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.2.0
	 */
	public static function pricing_options( $post_id ) {

		global $mnm_product_object;

		// Per-Item Pricing.
		woocommerce_wp_checkbox( array(
			'id'          => '_mnm_per_product_pricing',
			'label'       => __( 'Per-Item Pricing', 'woocommerce-mix-and-match-products' ),
			'description' => __( 'When enabled, your Mix-and-Match product will be priced individually, based on standalone item prices and tax rates.', 'woocommerce-mix-and-match-products' ),
			'desc_tip'    => true
		) );
	}


	/**
	 * Adds the MnM per-item shipping option.
	 *
	 * @param int $post_id
	 * @return void
	 * @since  1.2.0
	 */
	public static function shipping_options( $post_id ) {

		global $mnm_product_object;

		// Per-Item Shipping.
		woocommerce_wp_checkbox( array(
			'id'          => '_mnm_per_product_shipping',
			'label'       => __( 'Per-Item Shipping', 'woocommerce-mix-and-match-products' ),
			'description' => __( 'If your Mix-and-Match product consists of items that are assembled or packaged together, leave this option un-ticked and go to the Shipping tab to define the shipping properties of the entire bundle. Tick this option if the chosen items are shipped individually, without any change to their original shipping weight and dimensions.', 'woocommerce-mix-and-match-products' ),
			'desc_tip'    => true
		) );
	}


	/**
	 * Process, verify and save product data.
	 *
	 * @param  int 	$post_id
	 * @return void
	 */
	public static function process_meta( $post_id ) {

		/*
		 * Base Prices.
		 */

		$date_from     = (string) isset( $_POST[ '_sale_price_dates_from' ] ) ? wc_clean( $_POST[ '_sale_price_dates_from' ] ) : '';
		$date_to       = (string) isset( $_POST[ '_sale_price_dates_to' ] ) ? wc_clean( $_POST[ '_sale_price_dates_to' ] )     : '';
		$regular_price = (string) isset( $_POST[ '_regular_price' ] ) ? wc_clean( $_POST[ '_regular_price' ] )                 : '';
		$sale_price    = (string) isset( $_POST[ '_sale_price' ] ) ? wc_clean( $_POST[ '_sale_price' ] )                       : '';

		update_post_meta( $post_id, '_mnm_base_regular_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
		update_post_meta( $post_id, '_mnm_base_sale_price', '' === $sale_price ? '' : wc_format_decimal( $sale_price ) );

		if ( $date_to && ! $date_from ) {
			$date_from = date( 'Y-m-d' );
		}

		if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
			update_post_meta( $post_id, '_mnm_base_price', wc_format_decimal( $sale_price ) );
		} elseif ( '' !== $sale_price && $date_from && strtotime( $date_from ) <= strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
			update_post_meta( $post_id, '_mnm_base_price', wc_format_decimal( $sale_price ) );
		} else {
			update_post_meta( $post_id, '_mnm_base_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
		}

		if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
			update_post_meta( $post_id, '_mnm_base_price', '' === $regular_price ? '' : wc_format_decimal( $regular_price ) );
			update_post_meta( $post_id, '_mnm_base_sale_price', '' );
		}

		/*
		 * Per-Item Pricing.
		 */

		if ( isset( $_POST[ '_mnm_per_product_pricing' ] ) ) {
			update_post_meta( $post_id, '_mnm_per_product_pricing', 'yes' );
		} else {
			update_post_meta( $post_id, '_mnm_per_product_pricing', 'no' );
		}

		/*
		 * Per-Item Shipping.
		 */

		if ( isset( $_POST[ '_mnm_per_product_shipping' ] ) ) {
			update_post_meta( $post_id, '_mnm_per_product_shipping', 'yes' );
		} else {
			update_post_meta( $post_id, '_mnm_per_product_shipping', 'no' );
		}

		/*
		 * Container Sizes.
		 */

		$min_container_size = isset( $_POST[ '_mnm_min_container_size' ] ) ? absint( wc_clean( $_POST[ '_mnm_min_container_size' ] ) ) : 0;
		$max_container_size = isset( $_POST[ '_mnm_max_container_size' ] ) ? wc_clean( $_POST[ '_mnm_max_container_size' ] ) : '';
		$max_container_size = '' !== $max_container_size ? absint( $max_container_size ) : '';
		$max_container_size = $max_container_size > 0 && $max_container_size < $min_container_size ? $min_container_size : $max_container_size;

		update_post_meta( $post_id, '_mnm_min_container_size', $min_container_size );
		update_post_meta( $post_id, '_mnm_max_container_size', $max_container_size );

		// Initialize mnm data.
		$mnm_contents_data = array();

		// Populate with product data.
		if ( isset( $_POST[ 'mnm_allowed_contents' ] ) && ! empty( $_POST[ 'mnm_allowed_contents' ] ) ) {

			$mnm_allowed_contents 	= array_filter( array_map( 'intval', explode( ',', $_POST[ 'mnm_allowed_contents' ] ) ) );

			$unsupported_error = false;

			// check product types of selected items.
			foreach ( $mnm_allowed_contents as $mnm_id ) {

				// Get product type.
				$product = wc_get_product( $mnm_id );

				if ( ! in_array( $product->product_type, WC_Mix_and_Match_Helpers::get_supported_product_types() ) || ( $product->is_type( 'variation' ) && ! $product->has_all_attributes_set() ) ) {
					$unsupported_error = true;
				} else {
					// Product-specific data, such as discounts, or min/max quantities in container may be included later on.
					$mnm_contents_data[ $mnm_id ][ 'product_id' ] = $product->id;
				}
			}

			if ( $unsupported_error ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Mix & Match supports simple products and product variations with all attributes defined. Other product types and partially-defined variations cannot be added to the Mix & Match container.', 'woocommerce-mix-and-match-products' ) );
			}
		}

		if ( ! empty( $mnm_contents_data ) ) {
			update_post_meta( $post_id, '_mnm_data', $mnm_contents_data );
		} else {
			delete_post_meta( $post_id, '_mnm_data' );
			WC_Admin_Meta_Boxes::add_error( __( 'Please select at least one product to use for this Mix & Match product.', 'woocommerce-mix-and-match-products' ) );
		}

		return $post_id;

	}
}

// Launch the admin class.
WC_Mix_and_Match_Admin_Meta_Boxes::init();
