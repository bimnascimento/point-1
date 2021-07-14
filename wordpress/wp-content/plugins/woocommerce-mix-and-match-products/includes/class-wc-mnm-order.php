<?php
/**
 * Mix and Match order functions and filters.
 *
 * @class 	WC_Mix_and_Match_Order
 * @version 1.2.0
 * @since   1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WC_Mix_and_Match_Order {

	/**
	 * Flag to prevent 'woocommerce_order_get_items' filters from modifying original order line items when calling 'WC_Order::get_items'.
	 * @var boolean
	 */
	public static $override_order_items_filters = false;

	/**
	 * Setup order class.
	 */
	public function __construct() {

		// Filter price output shown in cart, review-order & order-details templates.
		add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'order_item_subtotal' ), 10, 3 );

		// Bundle containers should not affect order status.
		add_filter( 'woocommerce_order_item_needs_processing', array( $this, 'container_item_needs_processing' ), 10, 3 );

		// Modify order items to include bundle meta.
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'add_order_item_meta' ), 10, 3 );

		// Hide bundle configuration metadata in order line items.
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'hidden_order_item_meta' ) );

		// Filter order item count in the front-end.
		add_filter( 'woocommerce_get_item_count', array( $this, 'order_item_count' ), 10, 3 );

		// Filter admin dashboard item count and classes.
		if ( is_admin() ) {
			add_filter( 'woocommerce_admin_order_item_count', array( $this, 'order_item_count_string' ), 10, 2 );
			add_filter( 'woocommerce_admin_html_order_item_class', array( $this, 'html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_class', array( $this, 'html_order_item_class' ), 10, 2 );
		}

		// Modify product while completing payment - @see 'get_processing_product_from_item()' and 'container_item_needs_processing()'.
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'apply_get_product_from_item_filter' ) );
		add_action( 'woocommerce_payment_complete', array( $this, 'remove_get_product_from_item_filter' ) );
	}


	/*
	|--------------------------------------------------------------------------
	| API functions.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Modifies bundle parent/child order items depending on their shipping setup. Reconstructs an accurate representation of a bundle for shipping purposes.
	 * Used in combination with 'get_product_from_item', right below.
	 *
	 * Adds the totals of "packaged" items to the container totals and creates a container "Contents" meta field to provide a description of the included items.
	 *
	 * @param  array     $items
	 * @param  WC_Order  $order
	 * @return array
	 */
	public function get_order_items( $items, $order ) {

		// Nobody likes infinite loops.
		if ( self::$override_order_items_filters ) {
			return $items;
		}

		// Right?
		self::$override_order_items_filters = true;

		$return_items = array();

		foreach ( $items as $item_id => $item ) {

			if ( wc_mnm_is_mnm_container_order_item( $item ) ) {

				/*
				 * Add the totals of "packaged" items to the container totals and create a container "Contents" meta field to provide a description of the included products.
				 */
				$product = wc_get_product( $item->get_product_id() );

				if ( $product && $product->needs_shipping() && $child_items = wc_mnm_get_mnm_order_items( $item, $items ) ) {

					if ( ! empty( $child_items ) ) {

						// Aggregate contents.
						$contents = array();

						// Aggregate prices.
						$bundle_totals = array(
							'subtotal'     => $item->get_subtotal(),
							'total'        => $item->get_total(),
							'subtotal_tax' => $item->get_subtotal_tax(),
							'total_tax'    => $item->get_total_tax(),
							'taxes'        => $item->get_taxes()
						);

						foreach ( $child_items as $child_item_id => $child_item ) {

							// If the child is "packaged" in its parent...
							if ( 'no' === $child_item->get_meta( '_mnm_item_needs_shipping', true ) ) {

								$child_variation_id = $child_item->get_variation_id();
								$child_product_id   = $child_item->get_product_id();
								$child_id           = $child_variation_id ? $child_variation_id : $child_product_id;
								$child              = wc_get_product( $child_id );

								if ( ! $child || ! $child->needs_shipping() ) {
									continue;
								}

								/*
								 * Add item into a new container "Contents" meta.
								 */

								$sku = $child->get_sku();

								if ( ! $sku ) {
									$sku = '#' . $child_id;
								}

								$meta = strip_tags( wc_display_item_meta( $child_item, array(
									'before'    => '',
									'separator' => ', ',
									'after'     => '',
									'echo'      => false,
									'autop'     => false,
								) ) );

								$contents[] = array(
									'title'       => $child_item[ 'name' ] . ( $meta ? '(' . $meta . ')' : '' ),
									'description' => sprintf( __( 'Quantity: %1$s, SKU: %2$s', 'woocommerce-mix-and-match-products' ), $child_item[ 'qty' ], $sku )
								);

								/*
								 * Add item totals to the container totals.
								 */

								$bundle_totals[ 'subtotal' ]     += $child_item->get_subtotal();
								$bundle_totals[ 'total' ]        += $child_item->get_total();
								$bundle_totals[ 'subtotal_tax' ] += $child_item->get_subtotal_tax();
								$bundle_totals[ 'total_tax' ]    += $child_item->get_total_tax();

								$child_item_tax_data = $child_item->get_taxes();

								$bundle_totals[ 'taxes' ][ 'total' ]    = array_merge( $bundle_totals[ 'taxes' ][ 'total' ], $child_item_tax_data[ 'total' ] );
								$bundle_totals[ 'taxes' ][ 'subtotal' ] = array_merge( $bundle_totals[ 'taxes' ][ 'subtotal' ], $child_item_tax_data[ 'subtotal' ] );
							}
						}

						// Create a clone to ensure item totals will not be modified permanently.
						$item = clone $item;

						$item->set_props( $bundle_totals );

						// Create a meta field for each bundled item.
						if ( ! empty( $contents ) ) {
							foreach ( $contents as $contained ) {
								$item->add_meta_data( $contained[ 'title' ], $contained[ 'description' ] );
							}
						}
					}
				}

			} elseif ( wc_mnm_is_mnm_order_item( $item, $items ) ) {

				$variation_id = $item->get_variation_id();
				$product_id   = $item->get_product_id();
				$id           = $variation_id ? $variation_id : $product_id;
				$product      = wc_get_product( $id );

				if ( $product && $product->needs_shipping() && 'no' === $item->get_meta( '_mnm_item_needs_shipping', true ) ) {

					$item_totals = array(
						'subtotal'     => 0,
						'total'        => 0,
						'subtotal_tax' => 0,
						'total_tax'    => 0,
						'taxes'        => array( 'total' => array(), 'subtotal' => array() )
					);

					// Create a clone to ensure item totals will not be modified permanently.
					$item = clone $item;

					$item->set_props( $item_totals );
				}
			}

			$return_items[ $item_id ] = $item;
		}

		// End of my awesome infinite looping prevention mechanism.
		self::$override_order_items_filters = false;

		return $return_items;
	}

	/**
	 * Modifies parent/child order products in order to reconstruct an accurate representation of a bundle for shipping purposes:
	 *
	 * - If it's a container, then its weight is modified to include the weight of "packaged" children.
	 * - If a child is "packaged" inside its parent, then it is marked as virtual.
	 *
	 * Used in combination with 'get_order_items', right above.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return WC_Product
	 */
	public function get_product_from_item( $product, $item, $order ) {

		// If it's a container item...
		if ( wc_mnm_is_mnm_container_order_item( $item ) ) {

			if ( $product->needs_shipping() ) {

				// If it needs shipping, modify its weight to include the weight of all "packaged" items.
				if ( $bundle_weight = $item->get_meta( '_bundle_weight', true ) ) {
					$product->set_weight( $bundle_weight );
				}

				// Override SKU with kit/bundle SKU if needed.
				$child_items         = wc_mnm_get_mnm_order_items( $item, $order );
				$packaged_products   = array();
				$packaged_quantities = array();

				// Find items shipped in the container:
				foreach ( $child_items as $child_item ) {

					if ( 'no' === $child_item->get_meta( '_mnm_item_needs_shipping', true ) ) {

						$child_variation_id = $child_item->get_variation_id();
						$child_product_id   = $child_item->get_product_id();
						$child_id           = $child_variation_id ? $child_variation_id : $child_product_id;

						$child_product    = wc_get_product( $child_id );

						if ( ! $child_product || ! $child_product->needs_shipping() ) {
							continue;
						}

						$packaged_products[]              = $child_product;
						$packaged_quantities[ $child_id ] = $child_item->get_quantity();
					}
				}

				$sku     = $product->get_sku( 'edit' );
				$new_sku = apply_filters( 'woocommerce_mnm_sku_from_order_item', $sku, $product, $item, $order, $packaged_products, $packaged_quantities );

				if ( $sku !== $new_sku ) {
					$product->set_sku( $new_sku );
				}
			}

		// If it's a child item...
		} elseif ( wc_mnm_is_mnm_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( 'no' === $item->get_meta( '_mnm_item_needs_shipping', true ) ) {
					$product->set_virtual( 'yes' );
					$product->set_weight( '' );
				}
			}
		}

		return $product;
	}


	/*
	|--------------------------------------------------------------------------
	| Filter hooks.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Modify the subtotal of order-items (order-details.php) depending on the bundles's pricing strategy.
	 *
	 * @param  string   $subtotal   the item subtotal
	 * @param  array    $item       the items
	 * @param  WC_Order $order      the order
	 * @return string               modified subtotal string.
	 */
	public function order_item_subtotal( $subtotal, $item, $order ) {

		// If it's a bundled item...
		if ( $parent_item = wc_mnm_get_mnm_order_item_container( $item, $order ) ) {

			$per_product_pricing = $parent_item->get_meta( '_per_product_pricing', true );

			if ( $per_product_pricing === 'no' ) {
				return '';
			} else {

				if ( function_exists( 'is_account_page' ) && is_account_page() || function_exists( 'is_checkout' ) && is_checkout() ) {
					$wrap_start = '';
					$wrap_end   = '';
				} else {
					$wrap_start = '<small>';
					$wrap_end   = '</small>';
				}

				$subtotal_desc = __( 'Item subtotal', 'woocommerce-mix-and-match-products' ) . ': ';
				$subtotal      = $wrap_start . $subtotal_desc . $subtotal . $wrap_end;
			}
		}

		// If it's a container...
		if ( wc_mnm_is_mnm_container_order_item( $item ) ) {

			// Create a clone to ensure item totals will not be modified permanently.
			$item = clone $item;

			if ( ! isset( $item->child_subtotals_added ) ) {

				$children = wc_mnm_get_mnm_order_items( $item, $order );

				if ( ! empty( $children ) ) {

					foreach ( $children as $child ) {
						$item->set_subtotal( $item->get_subtotal( 'edit' ) + $child->get_subtotal( 'edit' ) );
						$item->set_subtotal_tax( $item->get_subtotal_tax( 'edit' ) + $child->get_subtotal_tax( 'edit' ) );
					}

					$item->child_subtotals_added = 'yes';

					$subtotal = $order->get_formatted_line_subtotal( $item );
				}
			}
		}

		return $subtotal;
	}


	/**
	 * Filters the reported number of order items.
	 * Do not count bundled items.
	 *
	 * @param  int          $count      initial reported count
	 * @param  string       $type       line item type
	 * @param  WC_Order     $order      the order
	 * @return int                      modified count
	 */
	public function order_item_count( $count, $type, $order ) {

		$subtract = 0;

		foreach ( $order->get_items( 'line_item' ) as $item ) {
			// If it's a bundled item...
			if ( wc_mnm_is_mnm_order_item( $item, $order ) ) {
				$subtract += $item->get_quantity();
			}
		}

		$new_count = $count - $subtract;

		return $new_count;
	}


	/**
	 * Filters the string of order item count.
	 * Include bundled items as a suffix.
	 *
	 * @see    order_item_count
	 * @param  int       $count      initial reported count
	 * @param  WC_Order  $order      the order
	 * @return int                   modified count
	 */
	public function order_item_count_string( $count, $order ) {

		$add = 0;

		foreach ( $order->get_items( 'line_item' ) as $item ) {

			// If it's a bundled item...
			if ( wc_mnm_is_mnm_order_item( $item, $order ) ) {
				$add += $item->get_quantity();
			}
		}

		if ( $add > 0 ) {
			return sprintf( __( '%1$s, %2$s mixed', 'woocommerce-mix-and-match-products' ), $count, $add );
		}

		return $count;
	}


	/**
	 * Filters the order item admin class.
	 *
	 * @param  string   $class     class
	 * @param  array    $item      the order item
	 * @return string              modified class
	 */
	public function html_order_item_class( $class, $item ) {

		// if it is a mnm container
		if ( isset( $item[ 'mnm_config' ] ) && ! empty( $item[ 'mnm_config' ] ) ) {
			$class .= ' mnm_table_container';
		}

		// If it's a bundled item
		if ( isset( $item[ 'mnm_container' ] ) && ! empty( $item[ 'mnm_container' ] ) ) {
			$class .= ' mnm_table_item';
		}

		return $class;
	}


	/**
	 * MnM Bundle Containers need no processing - let it be decided by child items only.
	 *
	 * @param  boolean      $is_needed   product needs processing: true/false
	 * @param  WC_Product   $product     the product
	 * @param  int          $order_id    the order id
	 * @return boolean                   modified product needs processing status
	 */
	public function container_item_needs_processing( $is_needed, $product, $order_id ) {

		if ( $product->is_type( 'mix-and-match' ) && isset( $product->bundle_needs_processing ) && 'no' === $product->bundle_needs_processing ) {
			$is_needed = false;
		}

		return $is_needed;
	}


	/**
	 * Hides bundle metadata.
	 *
	 * @param  array    $hidden     hidden meta strings
	 * @return array                modified hidden meta strings
	 */
	public function hidden_order_item_meta( $hidden ) {
		return array_merge( $hidden, array( '_mnm_config', '_mnm_container', '_per_product_pricing', '_per_product_shipping', '_mnm_cart_key', '_bundled_shipping', '_bundled_weight', '_mnm_item_needs_shipping', '_bundle_weight', '_mnm_items_need_processing' ) );
	}


	/**
	 * Add MnM bundle info meta to order items.
	 *
	 * @param  WC_Order_Item  $order_item
	 * @param  string         $cart_item_key
	 * @param  array          $cart_item_values
	 * @return void
	 */
	public function add_order_item_meta( $order_item, $cart_item_key, $cart_item_values ) {

		// Add metadata to child items.
		if ( wc_mnm_is_mnm_cart_item( $cart_item_values ) ) {

			$order_item->add_meta_data( '_mnm_container', $cart_item_values[ 'mnm_container' ], true );

			// Store shipping data.
			$needs_shipping = $cart_item_values[ 'data' ]->needs_shipping();

			$order_item->add_meta_data( '_mnm_item_needs_shipping', $needs_shipping ? 'yes' : 'no', true );

			if ( $container = wc_mnm_get_mnm_cart_item_container( $cart_item_values ) ) {

				// Use "Purchased with" to imply that item is physically shipped separately from its container.
				// Use "Part of" to imply that item is physically assembled or packaged in its container.
				$part_of_meta_name = $needs_shipping ? __( 'Purchased with', 'woocommerce-mix-and-match-products' ) : __( 'Part of', 'woocommerce-mix-and-match-products' );
				$part_of_meta_name = apply_filters( 'woocommerce_mnm_order_item_part_of_meta_name', $part_of_meta_name, $cart_item_values, $cart_item_key );

				if ( $part_of_meta_name ) {
					$order_item->add_meta_data( $part_of_meta_name, $container[ 'data' ]->get_title(), true );
				}
			}

			// Fire 'woocommerce_mnm_item_add_order_item_meta' action.
			// Note that the order item is not yet saved at this time.
			do_action( 'woocommerce_mnm_item_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item->get_id(), $order_item );
		}

		// Add data to the container item.
		if ( wc_mnm_is_mnm_container_cart_item( $cart_item_values ) && $cart_item_values[ 'data' ]->is_type( 'mix-and-match' ) ) {

			$min_container_size = intval( $cart_item_values[ 'data' ]->get_min_container_size() );
			$max_container_size = $cart_item_values[ 'data' ]->get_max_container_size();

			if( $min_container_size === $max_container_size && $min_container_size > 0 ){
				$container_size_meta_value = $min_container_size;
			} else {
				//$container_size_meta_value = sprintf( __( 'Min: %1$s, Max: %2$s', 'woocommerce-mix-and-match-products' ), $min_container_size, $max_container_size !== '' ? intval( $max_container_size ) : __( 'Unlimited', 'woocommerce-mix-and-match-products' ) );
				$container_size_meta_value = 0;
				// Count the total number of items in the container.
				foreach( $cart_item_values[ 'mnm_config' ] as $mnm_id => $mnm_data ){
					$container_size_meta_value += $mnm_data['quantity'];
				}
			}

			// Note that the order item is not yet saved at this time.
			$container_size_meta_value = apply_filters( 'woocommerce_mnm_order_item_container_size_meta_value', $container_size_meta_value, $order_item->get_id(), $cart_item_values, $cart_item_key );

			if ( $container_size_meta_value ) {
				$order_item->add_meta_data( __( 'Container size', 'woocommerce-mix-and-match-products' ), $container_size_meta_value, true );
			}

			$order_item->add_meta_data( '_mnm_config', $cart_item_values[ 'mnm_config' ], true );
			$order_item->add_meta_data( '_mnm_cart_key', $cart_item_key, true );

			$per_product_pricing = $cart_item_values[ 'data' ]->is_priced_per_product() ? 'yes' : 'no';

			$order_item->add_meta_data( '_per_product_pricing', $per_product_pricing, true );

			// Store shipping data.
			$needs_shipping = $cart_item_values[ 'data' ]->needs_shipping();

			// If it's a physical container item, grab its aggregate weight from the package data.
			if ( $needs_shipping ) {

				$packaged_item_values = false;

				foreach ( WC()->cart->get_shipping_packages() as $package ) {
					if ( isset( $package[ 'contents' ][ $cart_item_key ] ) ) {
						$packaged_item_values = $package[ 'contents' ][ $cart_item_key ];
						break;
					}
				}

				if ( ! empty( $packaged_item_values ) ) {
					$bundled_weight = $packaged_item_values[ 'data' ]->get_weight();
					$order_item->add_meta_data( '_bundle_weight', $bundled_weight, true );
				}

			// If it's a virtual container item, look at its children to see if any of them needs processing.
			} elseif ( false === $this->mnm_items_need_processing( $cart_item_values ) ) {
				$order_item->add_meta_data( '_mnm_items_need_processing', 'no', true );
			}

			// Fire 'woocommerce_mnm_container_add_order_item_meta' action.
			// Note that the order item is not yet saved at this time.
			do_action( 'woocommerce_mnm_container_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item->get_id(), $order_item );
		}
	}


	/**
	 * Given a virtual MnM container cart item, find if any of its children need processing.
	 *
	 * @since  1.2.0
	 *
	 * @param  array  $item_values
	 * @return mixed
	 */
	private function mnm_items_need_processing( $item_values ) {

		$child_keys        = wc_mnm_get_mnm_cart_items( $item_values, WC()->cart->cart_contents, true, true );
		$processing_needed = false;

		if ( ! empty( $child_keys ) && is_array( $child_keys ) ) {
			foreach ( $child_keys as $child_key ) {
				$child_product = WC()->cart->cart_contents[ $child_key ][ 'data' ];
				if ( false === $child_product->is_downloadable() || false === $child_product->is_virtual() ) {
					$processing_needed = true;
					break;
				}
			}
		}

		return $processing_needed;
	}


	/**
	 * Activates the 'get_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function apply_get_product_from_item_filter( $order_id ) {
		add_filter( 'woocommerce_get_product_from_item', array( $this, 'get_processing_product_from_item' ), 10, 3 );
	}


	/**
	 * Deactivates the 'get_product_from_item' filter below.
	 *
	 * @param  string  $order_id
	 * @return void
	 */
	public function remove_get_product_from_item_filter( $order_id ) {
		remove_filter( 'woocommerce_get_product_from_item', array( $this, 'get_processing_product_from_item' ), 10, 3 );
	}


	/**
	 * Filters 'get_product_from_item' to add data used for 'woocommerce_order_item_needs_processing'.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $item
	 * @param  WC_Order    $order
	 * @return WC_Product
	 */
	public function get_processing_product_from_item( $product, $item, $order ) {

		if ( ! empty( $product ) && $product->is_virtual() ) {

			// Process container.
			if ( $child_items = wc_mnm_get_mnm_order_items( $item, $order ) ) {

				// If no child requires processing and the container is virtual, it should not require processing - @see 'container_item_needs_processing()'.
				if ( sizeof( $child_items ) > 0 ) {
					if ( 'no' === $item->get_meta( '_mnm_items_need_processing', true ) ) {
						$product->bundle_needs_processing = 'no';
					}
				}
			}
		}

		return $product;
	}


	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function get_bundled_order_item_container( $item, $order ) {
		_deprecated_function( __METHOD__ . '()', '1.2.0', 'wc_mnm_get_mnm_order_item_container()' );
		return wc_mnm_get_mnm_order_item_container( $item, $order );
	}
}
