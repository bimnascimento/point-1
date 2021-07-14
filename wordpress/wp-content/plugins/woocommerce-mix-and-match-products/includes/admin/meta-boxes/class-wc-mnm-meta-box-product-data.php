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
 * @since 	1.0.0
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

		// Creates the MnM panel tab.
		add_action( 'woocommerce_product_data_tabs', array( __CLASS__, 'product_data_tab' ) );

		// Adds the mnm admin options.
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'container_size_options' ), 10 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'allowed_contents_options' ), 20 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'pricing_options' ), 30 );
		add_action( 'woocommerce_mnm_product_options', array( __CLASS__, 'shipping_options' ), 40 );

		// Creates the panel for selecting product options.
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'product_data_panel' ) );

		// Processes and saves the necessary post metas from the selections made above.
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'process_mnm_data' ) );
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
	 * @return array
	 */
	public static function product_data_tab( $tabs ) {

		global $post, $product_object, $mnm_product_object;

		/*
		 * Create a global MnM-type object to use for populating fields.
		 */

		$post_id = $post->ID;

		if ( empty( $product_object ) || false === $product_object->is_type( 'mix-and-match' ) ) {
			$mnm_product_object = $post_id ? new WC_Product_Mix_and_Match( $post_id ) : new WC_Product_Mix_and_Match();
		} else {
			$mnm_product_object = $product_object;
		}

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
	public static function product_data_panel() {
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

			// Avoid errors with Non-MNM products calling MNM-specific product methods.
			$product = new WC_Product_Mix_and_Match( $post_id );

			// Generate some data for the select2 input.
			$mnm_children = $product->get_children( 'edit' );
			?>

			<select id="mnm_allowed_contents" class="wc-product-search" name="mnm_allowed_contents[]" multiple="multiple" style="width: 400px;" data-sortable="sortable" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-mix-and-match-products' ); ?>" data-action="woocommerce_json_search_products_and_variations">
			<?php
				foreach ( $mnm_children as $child ) {
					if ( is_object( $child ) ) {
						echo '<option value="' . esc_attr( $child->get_id() ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $child->get_formatted_name() ) . '</option>';
					}
				}
			?>
			</select>
		</p>
	<?php
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
			'value'       => $mnm_product_object->get_priced_per_product( 'edit' ) ? 'yes' : 'no',
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
			'value'       => $mnm_product_object->get_shipped_per_product( 'edit' ) ? 'yes' : 'no',
			'description' => __( 'If your Mix-and-Match product consists of items that are assembled or packaged together, leave this option un-ticked and go to the Shipping tab to define the shipping properties of the entire bundle. Tick this option if the chosen items are shipped individually, without any change to their original shipping weight and dimensions.', 'woocommerce-mix-and-match-products' ),
			'desc_tip'    => true
		) );
	}


	/**
	 * Process, verify and save product data
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function process_mnm_data( $product ) {

		if ( $product->is_type( 'mix-and-match' ) ) {

			// Initialize mnm data.
			$mnm_contents_data = array();

			// Populate with product data.
			if ( isset( $_POST[ 'mnm_allowed_contents' ] ) && ! empty( $_POST[ 'mnm_allowed_contents' ] ) ) {

				$mnm_allowed_contents = array_filter( array_map( 'intval', (array) $_POST[ 'mnm_allowed_contents' ] ) );

				$unsupported_error = false;

				// Check product types of selected items.
				foreach ( $mnm_allowed_contents as $mnm_id ) {

					$mnm_product = wc_get_product( $mnm_id );

					if ( ! in_array( $mnm_product->get_type(), WC_Mix_and_Match_Helpers::get_supported_product_types() ) || ( $mnm_product->is_type( 'variation' ) && ! WC_MNM_Core_Compatibility::has_all_attributes_set( $mnm_product ) ) ) {
						$unsupported_error = true;
					} else {
						// Product-specific data, such as discounts, or min/max quantities in container may be included later on.
						$mnm_contents_data[ $mnm_id ][ 'product_id' ] = $mnm_product->get_id();
					}
				}

				if ( $unsupported_error ) {
					WC_Admin_Meta_Boxes::add_error( __( 'Mix & Match supports simple products and product variations with all attributes defined. Other product types and partially-defined variations cannot be added to the Mix & Match container.', 'woocommerce-mix-and-match-products' ) );
				}
			}

			// Show a notice if the user hasn't selected any items for the container.
			if ( empty( $mnm_contents_data ) ) {
				WC_Admin_Meta_Boxes::add_error( __( 'Please select at least one product to use for this Mix & Match product.', 'woocommerce-mix-and-match-products' ) );
			}

			$min_container_size = isset( $_POST[ '_mnm_min_container_size' ] ) ? absint( wc_clean( $_POST[ '_mnm_min_container_size' ] ) ) : 0;
			$max_container_size = isset( $_POST[ '_mnm_max_container_size' ] ) ? wc_clean( $_POST[ '_mnm_max_container_size' ] ) : '';
			$max_container_size = '' !== $max_container_size ? absint( $max_container_size ) : '';
			$max_container_size = $max_container_size > 0 && $max_container_size < $min_container_size ? $min_container_size : $max_container_size;

			$props = array(
				'min_container_size'  => $min_container_size,
				'max_container_size'  => $max_container_size,
				'contents'            => $mnm_contents_data,
				'priced_per_product'  => isset( $_POST[ '_mnm_per_product_pricing' ] ),
				'shipped_per_product' => isset( $_POST[ '_mnm_per_product_shipping' ] )
			);

			$product->set_props( $props );
		}
	}
}

// Launch the admin class.
WC_Mix_and_Match_Admin_Meta_Boxes::init();
