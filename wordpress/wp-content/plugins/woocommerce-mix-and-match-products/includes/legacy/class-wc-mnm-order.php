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
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'add_order_item_meta' ), 10, 3 );

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
				$product = wc_get_product( $item[ 'product_id' ] );

				if ( $product && $product->needs_shipping() && $child_items = wc_mnm_get_mnm_order_items( $item, $items ) ) {

					if ( ! empty( $child_items ) ) {

						// Aggregate contents.
						$contents = array();

						// Aggregate prices.
						$bundle_totals = array(
							'line_subtotal'     => $item[ 'line_subtotal' ],
							'line_total'        => $item[ 'line_total' ],
							'line_subtotal_tax' => $item[ 'line_subtotal_tax' ],
							'line_tax'          => $item[ 'line_tax' ],
							'line_tax_data'     => maybe_unserialize( $item[ 'line_tax_data' ] )
						);

						foreach ( $child_items as $child_item_id => $child_item ) {

							// If the child is "packaged" in its parent...
							if ( isset( $child_item[ 'mnm_item_needs_shipping' ] ) && 'no' === $child_item[ 'mnm_item_needs_shipping' ] ) {

								$child = wc_get_product( $child_item[ 'product_id' ] );

								if ( ! $child || ! $child->needs_shipping() ) {
									continue;
								}

								/*
								 * Add item into a new container "Contents" meta.
								 */

								$sku = $child->get_sku();

								if ( ! $sku ) {
									$sku = '#' . WC_MNM_Core_Compatibility::get_id( $child );
								}

								$meta = '';

								if ( ! empty( $child_item[ 'item_meta' ] ) ) {

									$item_meta      = new WC_Order_Item_Meta( $child_item );
									$formatted_meta = $item_meta->display( true, true, '_', ', ' );

									if ( $formatted_meta ) {
										$meta = $formatted_meta;
									}
								}

								$contents[] = array(
									'title'       => $child_item[ 'name' ] . ( $meta ? '(' . $meta . ')' : '' ),
									'description' => sprintf( __( 'Quantity: %1$s, SKU: %2$s', 'woocommerce-mix-and-match-products' ), $child_item[ 'qty' ], $sku )
								);

								/*
								 * Add item totals to the container totals.
								 */

								$bundle_totals[ 'line_subtotal' ]     += $child_item[ 'line_subtotal' ];
								$bundle_totals[ 'line_total' ]        += $child_item[ 'line_total' ];
								$bundle_totals[ 'line_subtotal_tax' ] += $child_item[ 'line_subtotal_tax' ];
								$bundle_totals[ 'line_tax' ]          += $child_item[ 'line_tax' ];

								$child_item_line_tax_data = maybe_unserialize( $child_item[ 'line_tax_data' ] );

								$bundle_totals[ 'line_tax_data' ][ 'total' ]    = array_merge( $bundle_totals[ 'line_tax_data' ][ 'total' ], $child_item_line_tax_data[ 'total' ] );
								$bundle_totals[ 'line_tax_data' ][ 'subtotal' ] = array_merge( $bundle_totals[ 'line_tax_data' ][ 'subtotal' ], $child_item_line_tax_data[ 'subtotal' ] );
							}
						}

						$item[ 'line_tax_data' ] = serialize( $bundle_totals[ 'line_tax_data' ] );
						$item                    = array_merge( $item, $bundle_totals );

						// Create a meta field for each bundled item.
						if ( ! empty( $contents ) ) {

							$keys     = array_keys( $item[ 'item_meta_array' ] );
							$last_key = end( $keys );
							$loop     = 1;

							foreach ( $contents as $contained ) {
								$entry        = new stdClass();
								$entry->key   = $contained[ 'title' ];
								$entry->value = $contained[ 'description' ];

								$item[ 'item_meta_array' ][ $last_key + $loop ] = $entry;
								$item[ 'item_meta' ][ $contained[ 'title' ] ]   = $contained[ 'description' ];
								$loop++;
							}
						}
					}
				}

			} elseif ( wc_mnm_is_mnm_order_item( $item, $items ) ) {

				$product_id = ! empty( $item[ 'variation_id' ] ) ? $item[ 'variation_id' ]  : $item[ 'product_id' ];
				$product    = wc_get_product( $product_id );

				if ( $product && $product->needs_shipping() && isset( $item[ 'mnm_item_needs_shipping' ] ) && 'no' === $item[ 'mnm_item_needs_shipping' ] ) {

					$item[ 'line_subtotal' ]     = 0;
					$item[ 'line_total' ]        = 0;
					$item[ 'line_subtotal_tax' ] = 0;
					$item[ 'line_tax' ]          = 0;
					$item[ 'line_tax_data' ]     = serialize( array( 'total' => array(), 'subtotal' => array() ) );
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
				if ( isset( $item[ 'bundle_weight' ] ) ) {
					$product->weight = $item[ 'bundle_weight' ];
				}

				// Override SKU with kit/bundle SKU if needed.
				$child_items         = wc_mnm_get_mnm_order_items( $item, $order );
				$packaged_products   = array();
				$packaged_quantities = array();

				// Find items shipped in the container:
				foreach ( $child_items as $child_item ) {

					if ( isset( $child_item[ 'mnm_item_needs_shipping' ] ) && 'no' === $child_item[ 'mnm_item_needs_shipping' ] ) {

						$child_product_id = ! empty( $child_item[ 'variation_id' ] ) ? $child_item[ 'variation_id' ] : $child_item[ 'product_id' ];
						$child_product    = wc_get_product( $child_product_id );

						if ( ! $child_product || ! $child_product->needs_shipping() ) {
							continue;
						}

						$packaged_products[]                      = $child_product;
						$packaged_quantities[ $child_product_id ] = $child_item[ 'qty' ];
					}
				}

				$product->sku = apply_filters( 'woocommerce_mnm_sku_from_order_item', $product->sku, $product, $item, $order, $packaged_products, $packaged_quantities );
			}

		// If it's a child item...
		} elseif ( wc_mnm_is_mnm_order_item( $item, $order ) ) {

			if ( $product->needs_shipping() ) {

				// If it's "packaged" in its container, set it to virtual.
				if ( isset( $item[ 'mnm_item_needs_shipping' ] ) && 'no' === $item[ 'mnm_item_needs_shipping' ] ) {
					$product->virtual = 'yes';
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

			$per_product_pricing = ! empty( $parent_item ) && isset( $parent_item[ 'per_product_pricing' ] ) ? $parent_item[ 'per_product_pricing' ] : get_post_meta( $parent_item[ 'product_id' ] , '_mnm_per_product_pricing', true );

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

			if ( ! isset( $item[ 'subtotal_updated' ] ) ) {

				$children = wc_mnm_get_mnm_order_items( $item, $order );

				if ( ! empty( $children ) ) {

					foreach ( $children as $child ) {
						$item[ 'line_subtotal' ]     += $child[ 'line_subtotal' ];
						$item[ 'line_subtotal_tax' ] += $child[ 'line_subtotal_tax' ];
					}

					$item[ 'subtotal_updated' ] = 'yes';

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
			if ( isset( $item[ 'mnm_container' ] ) ) {
				$subtract += $item[ 'qty' ];
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
			if ( isset( $item[ 'mnm_container' ] ) ) {
				$add += $item[ 'qty' ];
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
	 * Add bundle info meta to order items.
	 *
	 * @param  int      $order_item_id      order item id
	 * @param  array    $cart_item_values   cart item data
	 * @return void
	 */
	public function add_order_item_meta( $order_item_id, $cart_item_values, $cart_item_key ) {

		// Add metadata to child items.
		if ( wc_mnm_is_mnm_cart_item( $cart_item_values ) ) {

			wc_add_order_item_meta( $order_item_id, '_mnm_container', $cart_item_values[ 'mnm_container' ] );

			// Store shipping data.
			$needs_shipping = $cart_item_values[ 'data' ]->needs_shipping();

			wc_add_order_item_meta( $order_item_id, '_mnm_item_needs_shipping', $needs_shipping ? 'yes' : 'no' );

			if ( $container = wc_mnm_get_mnm_cart_item_container( $cart_item_values ) ) {

				// Use "Purchased with" to imply that item is physically shipped separately from its container.
				// Use "Part of" to imply that item is physically assembled or packaged in its container.
				$part_of_meta_name = $needs_shipping ? __( 'Purchased with', 'woocommerce-mix-and-match-products' ) : __( 'Part of', 'woocommerce-mix-and-match-products' );
				$part_of_meta_name = apply_filters( 'woocommerce_mnm_order_item_part_of_meta_name', $part_of_meta_name, $cart_item_values, $cart_item_key );

				if ( $part_of_meta_name ) {
					wc_add_order_item_meta( $order_item_id, $part_of_meta_name, $container[ 'data' ]->get_title() );
				}
			}

			// Fire 'woocommerce_mnm_item_add_order_item_meta' action.
			do_action( 'woocommerce_mnm_item_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item_id );
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

			$container_size_meta_value = apply_filters( 'woocommerce_mnm_order_item_container_size_meta_value', $container_size_meta_value, $order_item_id, $cart_item_values, $cart_item_key );

			if ( $container_size_meta_value ) {
				wc_add_order_item_meta( $order_item_id, __( 'Container size', 'woocommerce-mix-and-match-products' ), $container_size_meta_value );
			}

			wc_add_order_item_meta( $order_item_id, '_mnm_config', $cart_item_values[ 'mnm_config' ] );

			wc_add_order_item_meta( $order_item_id, '_mnm_cart_key', $cart_item_key );

			$per_product_pricing = $cart_item_values[ 'data' ]->is_priced_per_product() ? 'yes' : 'no';
			wc_add_order_item_meta( $order_item_id, '_per_product_pricing', $per_product_pricing );

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
					wc_add_order_item_meta( $order_item_id, '_bundle_weight', $bundled_weight );
				}

			// If it's a virtual container item, look at its children to see if any of them needs processing.
			} elseif ( false === $this->mnm_items_need_processing( $cart_item_values ) ) {
				wc_add_order_item_meta( $order_item_id, '_mnm_items_need_processing', 'no' );
			}

			// Fire 'woocommerce_mnm_container_add_order_item_meta' action.
			do_action( 'woocommerce_mnm_container_add_order_item_meta', $cart_item_values, $cart_item_key, $order_item_id );
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
					if ( isset( $item[ 'mnm_items_need_processing' ] ) && 'no' === $item[ 'mnm_items_need_processing' ] ) {
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
