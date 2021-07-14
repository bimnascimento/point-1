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
 * @package     WC-Minimum-Advertised-Price/Frontend
 * @author      SkyVerge
 * @copyright   Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

/**
 * WooCommerce Minimum Advertised Price frontend handler.
 *
 * @since 1.0
 */
class WC_Minimum_Advertised_Price_Frontend {


	/** @var string Where to display actual price, either in cart only, or in cart/on gesture. */
	private $display_location = '';

	/** @var bool Whether any supported shortcodes are being displayed. */
	private $has_shortcode = false;


	/**
	 * Initializes the frontend.
	 *
	 * @since 1.0
	 */
	public function __construct() {

		// Set where to display the actual price.
		$this->display_location = get_option( 'wc_minimum_advertised_price_display_location' );
		// Assume that no supported shortcodes are being displayed.
		$this->has_shortcode    = false;

		// Determine if any available post content contains a supported shortcode.
		add_filter( 'the_posts', array( $this, 'check_content_for_shortcodes' ) );

		// filter price HTML on loop & single product pages.
		add_filter( 'woocommerce_get_price_html',            array( $this, 'get_price_html' ), 10, 2 );

		if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
			add_filter( 'woocommerce_product_variation_get_price_html',      array( $this, 'get_price_html' ), 10, 2 );
			add_filter( 'woocommerce_product_variation_get_sale_price_html', array( $this, 'get_price_html' ), 10, 2 );
		} else {
			add_filter( 'woocommerce_variation_price_html',      array( $this, 'get_price_html' ), 10, 2 );
			add_filter( 'woocommerce_variation_sale_price_html', array( $this, 'get_price_html' ), 10, 2 );
		}

		// For same-price product variations, add the price_html back into the data
		// structure for the frontend so we have a place to hook in and show our "See Price" action.
		add_filter( 'woocommerce_available_variation', array( $this, 'same_price_variation_price_html' ), 10, 3 );

		// Load filters after WC loads.
		add_action( 'sv_wc_framework_plugins_loaded', array( $this, 'load_filters' ) );

		// Load javascript/css to display dialog when 'see price' button is clicked.
		if ( 'on_gesture' === $this->display_location ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp_head',            array( $this, 'inline_styles' ), 100 );
		}

		// Load the single-product/price.php template from the plugin.
		add_filter( 'woocommerce_locate_template', array( $this, 'locate_template' ), 20, 3 );
	}


	/**
	 * Add filters that require WC to be loaded first
	 * so the version can be checked and the proper filter used
	 *
	 * @internal
	 *
	 * @since 1.1
	 */
	public function load_filters() {

		add_filter( 'woocommerce_cart_item_price', array( $this, 'get_cart_item_price_html' ), 10, 2 );
	}


	/**
	 * Determine if any available post content contains a supported shortcode.
	 *
	 * @internal
	 *
	 * @since 1.5.1
	 * @param array $posts The currently queried post objects.
	 * @return array $posts The currently queried post objects, unmodified.
	 */
	public function check_content_for_shortcodes( $posts ) {

		// The built-in WooCommerce shortcodes.
		$shortcodes = array(
			'product',
			'product_category',
			'add_to_cart',
			'add_to_cart_url',
			'products',
			'recent_products',
			'sale_products',
			'best_selling_products',
			'top_rated_products',
			'featured_products',
			'product_attribute',
			'related_products',
		);

		/**
		 * Filter the supported shortcodes.
		 *
		 * @since 1.5.1
		 * @param array $widgets The shortcodes that trigger loading the necessary scripts & styles.
		 */
		$shortcodes = (array) apply_filters( 'wc_minimum_advertised_price_supported_shortcodes', $shortcodes );

		foreach ( $posts as $post ) {

			// Check if any of the WooCommerce shortcodes are present.
			foreach ( $shortcodes as $shortcode ) {

				if ( has_shortcode( $post->post_content, $shortcode ) ) {
					$this->has_shortcode = true;
					break;
				}
			}
		}

		return $posts;
	}


	/**
	 * Determine if any supported shortcodes are displayed.
	 *
	 * @since 1.5.1
	 * @return bool $has_shorcode Whether any supported shortcodes are displayed.
	 */
	private function has_shortcode() {
		return $this->has_shortcode;
	}


	/**
	 * Determine is a WooCommerce product widget is being displayed.
	 *
	 * @since 1.5.1
	 * @return bool $has_widget Whether a WooCommerce product widget is being displayed.
	 */
	private function has_widget() {

		$has_widget = false;

		// Built-in WooCommerce widgets.
		$widgets = array(
			'woocommerce_products',
			'woocommerce_recently_viewed_products',
			'woocommerce_top_rated_products',
		);

		/**
		 * Filter the supported widgets.
		 *
		 * @since 1.5.1
		 * @param array $widgets The widget IDs that trigger loading the necessary scripts & styles.
		 */
		$widgets = (array) apply_filters( 'wc_minimum_advertised_price_supported_widgets', $widgets );

		// Check if any of the supported widgets are present
		foreach ( $widgets as $widget_id ) {

			if ( is_active_widget( false, false, $widget_id ) ) {
				$has_widget = true;
				break;
			}
		}

		return $has_widget;
	}


	/**
	 * Load scripts for displaying price in dialog box.
	 *
	 * @internal
	 *
	 * @since 1.0
	 */
	public function load_scripts() {
		global $wp_scripts;

		// Check if on a WooCommerce page or shortcode.
		if ( ! is_woocommerce() && ! $this->has_shortcode() && ! $this->has_widget() ) {
			return;
		}

		// Load jQuery UI Dialog.
		wp_enqueue_script( 'jquery-ui-dialog' );

		// Load jQuery UI CSS.
		$jquery_version = isset( $wp_scripts->registered['jquery-ui-core']->ver ) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

		wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/' . $jquery_version . '/themes/smoothness/jquery-ui.css' );

		// add dialog handler
		wc_enqueue_js( "

			// Init the jQuery UI dialogs
			wcMapDialogHandler();

			// add click handler
			$( document ).on( 'click', 'a.wc-map-see-price', function( e ) {

				e.preventDefault();

				// set position for dialog and open it
				$( '#wc-map-see-price-' + $(this).data( 'product-id' ) ).dialog( 'option', 'position', { my: 'center bottom', at: 'center top', of: this } ).dialog( 'open' );

			} );

			// product page 'see price' -> 'add to cart' handler which submits the product page form
			$( document ).on( 'click', '.wc-map-dialog .single_add_to_cart_button', function() {
				$( '.cart' ).submit();
			} );

			// Reset the dialogs when a variation is switched for variable products
			$( 'form.variations_form' ).on( 'show_variation', function() {
				$( '.ui-wc-map-dialog' ).remove();
				wcMapDialogHandler();
			} );

			// 'See Price' dialog init
			function wcMapDialogHandler() {

				$( '.wc-map-dialog' ).dialog( {
					autoOpen: false,
					draggable: false,
					resizable: false,
					dialogClass: 'ui-wc-map-dialog'
				} );
			}
		" );
	}


	/**
	 * Display inline styles in <head>
	 *
	 * wp_add_inline_style() is not ideal because some themes remove WC's
	 * woocommerce-general style sheet, most notably Storefront.
	 *
	 * @internal
	 *
	 * @since 1.6.0
	 */
	public function inline_styles() {

		?>
		<style type="text/css">
			.woocommerce ul.products li.product a.wc-map-see-price {
				margin-bottom: 5px;
			}
			.wc-map-dialog a.added_to_cart {
				margin-left: 30px;
			}
			.ui-wc-map-dialog.ui-front {
				z-index: 5000;
			}
		</style>
		<?php
	}


	/**
	 * Return the minimum advertised price HTML.
	 *
	 * Returns the MAP HTML if a MAP is set for shop loop/single product pages.
	 * The MAP will be displayed with a strike-through, followed by the globally
	 * configured MAP label (label is added only on single product pages, since
	 * the "see price in cart" button if gesture's.
	 *
	 * @internal
	 *
	 * @since 1.0
	 * @param string $price_html The price HTML.
	 * @param \WC_Product $product The product.
	 * @return string HTML.
	 */
	public function get_price_html( $price_html, $product ) {

		// Return regular price if a MAP isn't set.
		if ( ! self::product_has_map( $product ) || ! $product->is_purchasable() ) {
			return $price_html;
		}

		// Get the MAP.
		$map = self::get_product_map( $product );

		if ( $product->is_type( array( 'simple', 'variation' ) ) ) {

			$price_html = '<del class="woocommerce-minimum-advertised-price">' . wc_price( $map ) . '</del>' . $this->get_map_label_html( $product );

		} elseif ( $product->is_type( 'variable' ) ) {

			$min_map = SV_WC_Product_Compatibility::get_meta( $product, '_min_minimum_advertised_price', true );
			$max_map = SV_WC_Product_Compatibility::get_meta( $product, '_max_minimum_advertised_price', true );

			// two different cases: a price range, or all the same price
			if ( $min_map === $max_map ) {
				$price_html =  '<del class="woocommerce-minimum-advertised-price">' . wc_price( $min_map ) . '</del>' . $this->get_map_label_html( $product );
			} else {
				$price_html = SV_WC_Product_Compatibility::wc_get_price_html_from_text( $product ) . '<del class="woocommerce-minimum-advertised-price">' . wc_price( $min_map ) . '</del>' . $this->get_map_label_html( $product );
			}

		}

		return $price_html;
	}


	/**
	 * If we're dealing with a variable product with all variations of the same
	 * price, by default the individual product variations price_html strings
	 * will not be included onto the page (since they're all the same).  However,
	 * we need them to hook onto and display our "See Price" action.
	 *
	 * @since 1.0
	 * @param array $available_variation the variation page data
	 * @param WC_Product_Variable $variable the variable product object
	 * @param WC_Product_Variation $variation the product variation object
	 * @return array the variation page data, with the price_html injected back in if need be
	 */
	public function same_price_variation_price_html( $available_variation, $variable, $variation ) {

		// if this variable product has a map price, and all the same prices, inject the variation price_html back in so we can hook onto it
		if ( 'on_gesture' === $this->display_location && ! $available_variation['price_html'] && self::product_has_map( $variable ) ) {

			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
				$min_price = $variable->get_variation_price( 'min' );
				$max_price = $variable->get_variation_price( 'max' );
			} else {
				$min_price = $variable->min_variation_price;
				$max_price = $variable->max_variation_price;
			}

			if ( $min_price === $max_price ) {
				$available_variation['price_html'] = '<span class="price">' . $variation->get_price_html() . '</span>';
			}
		}

		return $available_variation;
	}


	/**
	 * Render the minimum advertised price HTML.
	 *
	 * Renders the MAP HTML if a MAP is set for products on the cart page.
	 * The MAP will be displayed with a strike-through, followed by an optional
	 * "You save X.XX" amount label if enabled.
	 *
	 * @since 1.0
	 * @param string $price_html The price html.
	 * @param array $values The cart item values.
	 * @return string HTML
	 */
	public function get_cart_item_price_html( $price_html, $values ) {

		// Return regular price if a MAP isn't set, or no regular price.
		if ( ! self::product_has_map( $values['data'] ) ) {
			return $price_html;
		}

		$map = self::get_product_map( $values['data'] );

		$price_html = sprintf( '<del>%1$s</del>&nbsp;%2$s', wc_price( $map ), $price_html );

		if ( wc_minimum_advertised_price()->show_savings() ) {

			/* translators: Placeholders: %s - amount saved */
			$price_savings_html = sprintf( '<br/>'. __( 'You save: %s', 'woocommerce-minimum-advertised-price' ), wc_price( $map - $values['data']->get_price() ) );

			$price_html .= (string) apply_filters( 'wc_minimum_advertised_price_savings_html', $price_savings_html, $map, $values );
		}

		return $price_html;
	}


	/**
	 * Locates the `single-product/price.php` template file in our templates directory.
	 *
	 * @since 1.4.0
	 * @param string $template Already found template.
	 * @param string $template_name Searchable template name.
	 * @param string $template_path Template path.
	 * @return string Search result for the template.
	 */
	public function locate_template( $template, $template_name, $template_path ) {
		global $product;

		// Bail if not the price template.
		if ( 'single-product/price.php' !== $template_name ) {
			return $template;
		}

		// Bail if MAP isn't set.
		if ( ! self::product_has_map( $product ) ) {
			return $template;
		}

		// Bail if set to show the regular price (the WC default).
		if ( 'regular' === get_option( 'wc_minimum_advertised_price_structured_data_display', 'none' ) ) {
			return $template;
		}

		// Only keep looking if no custom theme template was found or if
 		// a default WooCommerce template was found.
 		if ( ! $template || SV_WC_Helper::str_starts_with( $template, WC()->plugin_path() ) ) {

 			// Set the path to our templates directory.
 			$plugin_path = wc_minimum_advertised_price()->get_plugin_path() . '/templates/';

 			// If a template is found, make it so.
 			if ( is_readable( $plugin_path . $template_name ) ) {
 				$template = $plugin_path . $template_name;
 			}
 		}

		return $template;
	}


	/** Helper methods ******************************************************/


	/**
	 * Checks if the given product has a minimum advertised price set.
	 *
	 * For variable products this will return true if at least one variation has a MAP set.
	 *
	 * @since 1.0
	 * @param \WC_Product $product Product object.
	 * @return bool true if the product has MAP set, false otherwise.
	 */
	public static function product_has_map( $product ) {

		$has_map = false;

		if ( $product->is_type( array( 'simple', 'variation' ) ) ) {
			$map     = SV_WC_Product_Compatibility::get_meta( $product, '_minimum_advertised_price', true );
			$has_map = ! empty( $map );
		} elseif ( $product->is_type( 'variable' ) ) {
			$min_map = SV_WC_Product_Compatibility::get_meta( $product, '_min_minimum_advertised_price', true );
			$has_map = ! empty( $min_map );
		}

		return $has_map;
	}


	/**
	 * Returns the minimum advertised price set for the given product
	 *
	 * @since 1.0
	 * @param \WC_Product $product Product object
	 * @return string the MAP
	 */
	public static function get_product_map( $product ) {

		$map = '';

		if ( $product && $product->is_type( array( 'simple', 'variation' ) ) ) {

			$map = SV_WC_Product_Compatibility::get_meta( $product, '_minimum_advertised_price', true );

			if ( SV_WC_Plugin_Compatibility::is_wc_version_gte_3_0() ) {
				$map = ! empty( $map ) && ! is_numeric( $map ) ? wc_format_decimal( $map ) : $map;
			} else {
				$map = ! empty( $map ) && ! is_numeric( $map ) ? woocommerce_format_decimal( $map ) : $map;
			}
		}

		return $map;
	}


	/**
	 * Returns the label displayed next to the MAP.
	 *
	 * - If the display location is "in cart" or the product is a variable type,
	 * this will return the "See price in cart" text.
	 * - Otherwise, if the display location is "on gesture" and we're on the
	 * product page, the "See Price" link will be returned.
	 *
	 * @since 1.0
	 * @param \WC_Product $product Product object instance passed to gesture label filter.
	 * @return string The configured minimum advertised price label.
	 */
	private function get_map_label_html( $product ) {

		$label_html = '';

		if ( 'in_cart' === $this->display_location || $product->is_type( 'variable' ) ) {
			$label_html = '&nbsp;<span class="wc-map-label">' . get_option( 'wc_minimum_advertised_price_label' ) . '</span>';
		} elseif ( 'on_gesture' === $this->display_location ) {
			$label_html = $this->get_map_see_price_html( $product );
		}

		return apply_filters( 'wc_minimum_advertised_price_label_html', $label_html, $product, $this->display_location );
	}


	/**
	 * Returns the "See Price" action HTML.
	 *
	 * @since 1.0
	 * @param \WC_Product $product Product object instance passed to gesture label filter.
	 * @return string The see price action HTML.
	 */
	private function get_map_see_price_html( $product ) {

		$action_html = '';

		if ( 'on_gesture' === $this->display_location && $product->is_purchasable() && $product->is_in_stock() ) {

			// Add button (on shop loop) or link (on single product page) to display dialog.
			$action_html = sprintf( ' <a href="#" rel="nofollow" data-product-id="%1$s" class="wc-map-see-price%2$s">%3$s</a>',
				$product->get_id(),
				( ! is_product() ) ? ' button' : '',
				esc_html__( 'See Price', 'woocommerce-minimum-advertised-price' )
			);

			// Add dialog HTML.
			$action_html .= $this->get_dialog_html( $product );
		}

		return apply_filters( 'wc_minimum_advertised_price_see_price_html', $action_html, $product, $this->display_location );
	}


	/**
	 * Return the HTML for the dialog shown when 'See Price' is clicked.
	 *
	 * @since 1.0
	 * @param \WC_Product $product Product object.
	 * @return string The dialog HTML.
	 */
	private function get_dialog_html( $product ) {

		ob_start();

		woocommerce_minimum_advertised_price_see_price_product_dialog( $product );

		return ob_get_clean();
	}


}
