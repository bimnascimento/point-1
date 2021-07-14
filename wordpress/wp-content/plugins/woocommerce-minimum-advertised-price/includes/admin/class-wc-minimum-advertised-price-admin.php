<?php
/**
 * WooCommerce Minimum Advertised Price
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Minimum Advertised Price to newer
 * versions in the future. If you wish to customize WooCommerce Minimum Advertised Price for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-minimum-advertised-price/ for more information.
 *
 * @package     WC-Minimum-Advertised-Price/Admin
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Minimum Advertised Price admin handler.  Loads all Minimum
 * Advertised Price product data panels and modifications for WooCommerce
 * general settings.
 *
 * @since 1.0
 */
class WC_Minimum_Advertised_Price_Admin {


	/**
	 * Initialize the admin, adding actions to properly display and handle
	 * the minimum advertised price fields
	 * @since 1.0
	 */
	public function __construct() {

		// add general settings to WooCommerce > Settings > Products
		add_filter( 'woocommerce_products_general_settings', array( $this, 'add_global_settings' ) );

		// Load styles/scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'load_styles_scripts' ) );


		/* Simple Product Hooks */

		// display the simple product meta field
		add_action( 'woocommerce_product_options_pricing', array( $this, 'add_map_field_to_simple_product' ) );

		// save the simple product meta field
		add_action( 'woocommerce_process_product_meta',    array( $this, 'save_simple_product_map' ) );


		/* Variable Product Hooks */

		// adds the product variation 'MAP' bulk edit action
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( $this, 'add_variable_product_bulk_edit_map_action' ) );

		// save variation minimum advertised price for bulk edit action
		add_action( 'woocommerce_bulk_edit_variations_default', array( $this, 'variation_bulk_action_variable_map' ), 10, 4 );

		// add MAP field to variable products under the 'Variations' tab after the shipping class dropdown
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'add_map_field_to_variable_product' ), 15, 3 );

		// save the MAP field for variable products
		add_action( 'woocommerce_save_product_variation', array( $this, 'save_variable_product_map' ) );

		// save the lowest/highest MAP for variable products
		add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_min_max_variable_product_map' ) );
		add_action( 'woocommerce_ajax_save_product_variations',  array( $this, 'save_min_max_variable_product_map' ) );


		/* Product list bulk edit hooks */

		// add Products list table MAP bulk edit field
		add_action( 'woocommerce_product_bulk_edit_end', array( $this, 'add_map_field_bulk_edit' ) );

		// save Products List table MAP bulk edit field
		add_action( 'woocommerce_product_bulk_edit_save', array( $this, 'save_map_field_bulk_edit' ) );
	}


	/** Global Configuration ******************************************************/


	/**
	 * Inject global settings into the Settings > Catalog page, immediately after the 'Pricing Options' section
	 *
	 * @since 1.1
	 * @param array $settings associative array of WooCommerce settings
	 * @return array associative array of WooCommerce settings
	 */
	public function add_global_settings( $settings ) {

		$updated_settings = array();

		foreach ( $settings as $setting ) {

			$updated_settings[] = $setting;

			if (     isset( $setting['id'] )
			     && 'product_measurement_options' === $setting['id']
			     && isset( $setting['type'] )
			     && 'sectionend' === $setting['type'] ) {

				$updated_settings = array_merge( $updated_settings, $this->get_global_settings() );
			}
		}

		return $updated_settings;
	}


	/**
	 * Returns the global settings array for the plugin
	 *
	 * @since 1.0
	 * @return array the global settings
	 */
	public static function get_global_settings() {

		return apply_filters( 'wc_minimum_advertised_price_global_settings', array(

			// section start
			array(
				'title' => __( 'Minimum Advertised Pricing Options', 'woocommerce-minimum-advertised-price' ),
				'type'  => 'title',
				'id'    => 'wc_minimum_advertised_price_title',
			),

			// Where to display price
			array(
				'title'    => __( 'Display Actual Price', 'woocommerce-minimum-advertised-price' ),
				'desc_tip' => __( 'Where to display the actual price: in the cart only, or in the cart and also in a popover on the catalog / product page when the "Show Price" button is clicked', 'woocommerce-minimum-advertised-price' ),
				'type'     => 'select',
				'id'       => 'wc_minimum_advertised_price_display_location',
				'default'  => 'in_cart',
				'options'  => array(
					'in_cart'     => __( 'In Cart Only', 'woocommerce-minimum-advertised-price' ),
					'on_gesture'  => __( 'In Cart / On Gesture', 'woocommerce-minimum-advertised-price' ),
				),
			),

			// Text to display next to the MAP
			array(
				'title'    => __( 'Minimum Advertised Price Label', 'woocommerce-minimum-advertised-price' ),
				'desc_tip' => __( 'Change the text displayed next to the minimum advertised price on loop/single product pages', 'woocommerce-minimum-advertised-price' ),
				'type'     => 'text',
				'id'       => 'wc_minimum_advertised_price_label',
				'default'  => __( 'See Price in Cart', 'woocommerce-minimum-advertised-price' ),
			),

			// Show savings
			array(
				'title'   => __( 'Show Savings', 'woocommerce-minimum-advertised-price' ),
				'id'      => 'wc_minimum_advertised_price_show_savings',
				'desc'    => __( 'Show a message with the savings when displaying the actual price', 'woocommerce-minimum-advertised-price' ),
				'default' => 'no',
				'type'    => 'checkbox',
			),

			array(
				'title'    => __( 'Price Displayed to Search Engines', 'woocommerce-minimum-advertised-price' ),
				'id'       => 'wc_minimum_advertised_price_structured_data_display',
				'desc_tip' => __( 'Select which price should be included in rich snippets. This setting has no effect if your theme overrides the `single-product/price.php` template.', 'woocommerce-minimum-advertised-price' ),
				'default'  => 'none',
				'options'  => array(
					'map'     => __( 'Minimum Advertised Price', 'woocommerce-minimum-advertised-price' ),
					'regular' => __( 'Regular Price', 'woocommerce-minimum-advertised-price' ),
					'none'    => __( 'None', 'woocommerce-minimum-advertised-price' ),
				),
				'type'     => 'radio',
			),

			// section end
			array(
				'type' => 'sectionend',
				'id'   => 'wc_minimum_advertised_price_title',
			),

		) );
	}


	/**
	 * Load admin styles and scripts
	 *
	 * @since 1.5.0
	 * @param string $hook_suffix the current URL filename, ie edit.php, post.php, etc
	 */
	public function load_styles_scripts( $hook_suffix ) {
		global $post_type;

		// load admin css only on view orders / edit order screens
		if ( 'product' === $post_type && in_array( $hook_suffix, array( 'edit.php', 'post.php', 'post-new.php' ), true ) ) {

			// admin JS
			wp_enqueue_script( 'wc-cog-admin', wc_minimum_advertised_price()->get_plugin_url() . '/assets/js/admin/wc-map-admin.min.js', array( 'jquery', 'wc-admin-product-meta-boxes', 'woocommerce_admin' ), WC_Minimum_Advertised_Price::VERSION );
		}
	}

	/** Product Configuration ******************************************************/


	/**
	 * Display our simple product Minimum Advertised Pricing field
	 *
	 * @since 1.0
	 */
	public function add_map_field_to_simple_product() {

		// add MAP
		woocommerce_wp_text_input( array(
				'id'        => '_minimum_advertised_price',
				'class'     => 'wc_input_price short',
				'label'     => _x( 'MAP', 'Minimum Advertised Price (acronym)', 'woocommerce-minimum-advertised-price' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'desc_tip'  => __( 'The Minimum Advertised Price (or MAP), set this to hide the Regular Price unless the product is added to the cart', 'woocommerce-minimum-advertised-price' ),
				'date_type' => 'price',
			)
		);
	}


	/**
	 * Save our simple product meta fields.
	 *
	 * @since 1.0
	 * @param int $product_id The product ID.
	 */
	public function save_simple_product_map( $product_id ) {

		if ( $product = wc_get_product( $product_id ) ) {

			// Avoid creating empty map entries on non-map products.
			if ( ! empty( $_POST['_minimum_advertised_price'] ) ) {
				SV_WC_Product_Compatibility::update_meta_data( $product, '_minimum_advertised_price', stripslashes( $_POST['_minimum_advertised_price'] ) );
			} else {
				SV_WC_Product_Compatibility::delete_meta_data( $product, '_minimum_advertised_price' );
			}
		}
	}


	/**
	 * Renders the 'MAP' bulk edit action on the product admin Variations tab
	 *
	 * @since 1.0
	 */
	public function add_variable_product_bulk_edit_map_action() {

		/* translators: Minimum Advertised Price (acronym) */
		echo '<option value="variable_minimum_advertised_price">' . esc_html__( 'Set MAP', 'woocommerce-minimum-advertised-price' ) . '</option>';
	}


	/**
	 * Set variation minimum advertised price for variations via bulk edit
	 *
	 * @since 1.5.0
	 * @param string $bulk_action
	 * @param array $data
	 * @param int $product_id
	 * @param array $variations
	 */
	public function variation_bulk_action_variable_map( $bulk_action, $data, $product_id, $variations ) {

		if ( 'variable_minimum_advertised_price' === $bulk_action && ! empty( $data['value'] ) ) {

			foreach ( $variations as $variation_id ) {

				$this->update_variation_product_map( $variation_id, wc_clean( $data['value'] ) );
			}
		}
	}


	/**
	 * Add MAP field to variable products under the 'Variations' tab after the shipping class dropdown
	 *
	 * @since 1.0
	 * @param string $loop
	 * @param array $variation_data
	 * @param \WP_Post $variation
	 */
	public function add_map_field_to_variable_product( $loop, $variation_data, $variation ) {

		// TODO still uses post meta, should be updated to WC 3.0+ CRUD, however doing wc_get_product( $variation )->get_meta_data() results in a different array than expected in get_post_meta() {FN 2017-03-16}
		$variation_data = array_merge( get_post_meta( $variation->ID ), $variation_data );
		$map            = ( isset( $variation_data['_minimum_advertised_price'][0] ) ) ? $variation_data['_minimum_advertised_price'][0] : '';

		?>
		<div>
			<p class="form-row form-row-first">
				<label><?php
					/* translators: MAP = Minimum Advertised Price; Placeholders: %s - currency symbol */
					printf( __( 'MAP: (%s)', 'woocommerce-minimum-advertised-price' ), esc_html( get_woocommerce_currency_symbol() ) );
				?></label>
				<input
					type="text"
					class="wc_input_price"
					name="variable_minimum_advertised_price[<?php echo esc_attr( $loop ); ?>]"
					size="6"
					placeholder="<?php esc_html_e( 'Variation MAP', 'woocommerce-minimum-advertised-price' ); ?>"
					value="<?php echo esc_attr( $map ); ?>"
				/>
			</p>
		</div>
		<?php
	}


	/**
	 * Helper method to update the minimum advertised price meta for a variation
	 *
	 * @since 1.5.0
	 * @param $variation_id int The variation ID
	 * @param $map string The minimum advertised price
	 */
	public function update_variation_product_map( $variation_id, $map ) {

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			if ( $variation_product = wc_get_product( $variation_id ) ) {
				if ( ! empty( $map ) ) {
					SV_WC_Product_Compatibility::update_meta_data( $variation_product, '_minimum_advertised_price', $map );
				} else {
					SV_WC_Product_Compatibility::delete_meta_data( $variation_product, '_minimum_advertised_price' );
				}
			}
		} else {
			if ( ! empty( $map ) ) {
				update_post_meta( $variation_id, '_minimum_advertised_price', $map );
			} else {
				delete_post_meta( $variation_id, '_minimum_advertised_price' );
			}
		}
	}


	/**
	 * Save our variable product meta fields.
	 *
	 * @since 1.0
	 * @param int $variation_id The product variation ID.
	 */
	public function save_variable_product_map( $variation_id ) {

		// Find the index for the given variation ID and save the associated MAP.
		$index = array_search( $variation_id, $_POST['variable_post_id'] );

		if ( false !== $index ) {

			$this->update_variation_product_map( $variation_id, $_POST['variable_minimum_advertised_price'][ $index ] );
		}
	}


	/**
	 * Save the minimum & maximum variation MAP prices to the parent variable product.
	 *
	 * @since 1.0
	 * @param int $post_id The post ID of the variable product.
	 */
	public function save_min_max_variable_product_map( $post_id ) {

		$lowest_minimum_advertised_price  = '';
		$highest_minimum_advertised_price = '';

		$product = wc_get_product( $post_id );

		if ( $product && isset( $_POST['variable_minimum_advertised_price'] ) && is_array( $_POST['variable_minimum_advertised_price'] ) ) {

			foreach ( $_POST['variable_minimum_advertised_price'] as $map ) {

				if ( ! is_numeric( $lowest_minimum_advertised_price ) || ( is_numeric( $map ) && $map < $lowest_minimum_advertised_price ) ) {
					$lowest_minimum_advertised_price = $map;
				}

				if ( ! is_numeric( $highest_minimum_advertised_price ) || ( is_numeric( $map ) && $map > $highest_minimum_advertised_price ) ) {
					$highest_minimum_advertised_price = $map;
				}
			}
		}

		if ( is_numeric( $lowest_minimum_advertised_price ) || is_numeric( $highest_minimum_advertised_price ) ) {
			SV_WC_Product_Compatibility::update_meta_data( $product, '_min_minimum_advertised_price', $lowest_minimum_advertised_price );
			SV_WC_Product_Compatibility::update_meta_data( $product, '_max_minimum_advertised_price', $highest_minimum_advertised_price );
		} else {
			// Don't create the min/max records for non-map variable products.
			SV_WC_Product_Compatibility::delete_meta_data( $product, '_min_minimum_advertised_price' );
			SV_WC_Product_Compatibility::delete_meta_data( $product, '_max_minimum_advertised_price' );
		}
	}


	/**
	 * Add a MAP bulk edit field, this is displayed on the Products list page
	 * when one or more products is selected, and the Edit Bulk Action is applied
	 *
	 * @since 1.0
	 */
	public function add_map_field_bulk_edit() {

		?>
		<div class="inline-edit-group">

			<label class="alignleft">

				<span class="title"><?php echo esc_html_x( 'MAP', 'Minimum Advertised Price (acronym)', 'woocommerce-minimum-advertised-price' ); ?></span>

				<span class="input-text-wrap">
					<select
						class="change_minimum_advertised_price change_to"
						name="change_minimum_advertised_price">
						<?php $options = array(
							''  => __( '— No Change —', 'woocommerce-minimum-advertised-price' ),
							'1' => __( 'Change to:', 'woocommerce-minimum-advertised-price' ),
							'2' => __( 'Increase by (fixed amount or %):', 'woocommerce-minimum-advertised-price' ),
							'3' => __( 'Decrease by (fixed amount or %):', 'woocommerce-minimum-advertised-price' ),
						); ?>
						<?php foreach ( $options as $key => $value ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
						<?php endforeach; ?>
					</select>
				</span>

			</label>

			<label class="alignright">
				<input
					type="text"
					name="_minimum_advertised_price"
					class="text minimum_advertised_price"
					placeholder="<?php esc_html_e( 'Enter MAP:', 'woocommerce-minimum-advertised-price' ); ?>"
					value=""
				/>
			</label>

		</div>
		<?php
	}


	/**
	 * Save the MAP bulk edit field.
	 *
	 * @since 1.0
	 * @param \WC_Product $product The product.
	 */
	public function save_map_field_bulk_edit( $product ) {

		if ( ! empty( $_REQUEST['change_minimum_advertised_price'] ) ) {

			$option_selected      = absint( $_REQUEST['change_minimum_advertised_price'] );
			$requested_map_change = stripslashes( $_REQUEST['_minimum_advertised_price'] );
			$current_map_value    = SV_WC_Product_Compatibility::get_meta( $product, '_minimum_advertised_price', true );

			switch ( $option_selected ) {

				// change MAP to fixed amount
				case 1 :
					$new_map = $requested_map_change;
				break;

				// increase MAP by fixed amount/percentage
				case 2 :

					if ( false !== strpos( $requested_map_change, '%' ) ) {
						$percent = str_replace( '%', '', $requested_map_change ) / 100;
						$new_map = $current_map_value + ( $current_map_value * $percent );
					} else {
						$new_map = $current_map_value + $requested_map_change;
					}

				break;

				// decrease MAP by fixed amount/percentage
				case 3 :

					if ( false !== strpos( $requested_map_change, '%' ) ) {
						$percent = str_replace( '%', '', $requested_map_change ) / 100;
						$new_map = $current_map_value - ( $current_map_value * $percent );
					} else {
						$new_map = $current_map_value - $requested_map_change;
					}

				break;

			}

			// update to new MAP if different than current MAP
			if ( isset( $new_map ) && $new_map != $current_map_value ) {

				SV_WC_Product_Compatibility::update_meta_data( $product, '_minimum_advertised_price', $new_map );
			}
		}
	}


}
