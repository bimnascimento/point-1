<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mix & Match Product Class.
 *
 * @class    WC_Product_Mix_and_Match
 * @version  1.2.0
 */

class WC_Product_Mix_and_Match extends WC_Product {

	/** @private array Price-specific data, used to caculate min/max product prices for display and min/max prices incl/excl tax. */
	private $pricing_data = array();

	/** @private array of child products/variations. */
	private $children;

	/** @private array of child keys that are available. */
	private $available_children;

	/** @private boolean In per-product pricing mode, the sale status of the product is defined by the children. */
	private $on_sale;

	/** @private boolean True if children stock can fill all slots. */
	private $has_enough_children_in_stock;

	/** @private boolean True if children must be backordered to fill all slots. */
	private $backorders_required;

	/** @private boolean True if product data is in sync with children. */
	private $is_synced = false;

	/**
	 * Props to store max raw prices.
	 * @var mixed
	 */
	private $max_raw_price;
	private $max_raw_regular_price;

	/**
	 * Runtime cache for calculated prices.
	 * @var array
	 */
	private $mnm_price_cache = array();

	// Define type-specific props.
	protected $extra_data = array(
		'min_raw_price'         => '',
		'min_raw_regular_price' => '',
		'min_container_size'    => 0,
		'max_container_size'    => null,
		'contents'              => array(),
		'priced_per_product'    => false,
		'shipped_per_product'   => false
	);

	/**
	 * __construct function.
	 *
	 * @param  mixed $product
	 */
	public function __construct( $product ) {

		// Back-compat.
		$this->product_type = 'mix-and-match';

		parent::__construct( $product );
	}


	/*
	|--------------------------------------------------------------------------
	| CRUD Getters.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Returns the base active price of the MnM bundle.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $context
	 * @return mixed
	 */
	public function get_price( $context = 'view' ) {
		$value = $this->get_prop( 'price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() ? (double) $value : $value;
	}


	/**
	 * Returns the base regular price of the MnM bundle.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $context
	 * @return mixed
	 */
	public function get_regular_price( $context = 'view' ) {
		$value = $this->get_prop( 'regular_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() ? (double) $value : $value;
	}


	/**
	 * Returns the base sale price of the MnM bundle.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $context
	 * @return mixed
	 */
	public function get_sale_price( $context = 'view' ) {
		$value = $this->get_prop( 'sale_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() && '' !== $value ? (double) $value : $value;
	}


	/**
	 * Minimum raw MnM bundle price getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_min_raw_price( $context = 'view' ) {
		$this->maybe_sync();
		$value = $this->get_prop( 'min_raw_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() && '' !== $value ? (double) $value : $value;
	}


	/**
	 * Minimum raw regular MnM bundle price getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_min_raw_regular_price( $context = 'view' ) {
		$this->maybe_sync();
		$value = $this->get_prop( 'min_raw_regular_price', $context );
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() && '' !== $value ? (double) $value : $value;
	}


	/**
	 * Per-Item Pricing getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $context
	 * @return boolean
	 */
	public function get_priced_per_product( $context = 'any' ) {
		return $this->get_prop( 'priced_per_product', $context );
	}


	/**
	 * Per-Item Shipping getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $context
	 * @return boolean
	 */
	public function get_shipped_per_product( $context = 'any' ) {
		return $this->get_prop( 'shipped_per_product', $context );
	}


	/**
	 * Return the product's minimum size limit.
	 * @param  string $context
	 * @return int
	 */
	public function get_min_container_size( $context = 'view' ) {
		$value = $this->get_prop( 'min_container_size', 'edit' );
		return 'view' === $context ? apply_filters( 'woocommerce_mnm_min_container_size', $value, $this ) : $value;
	}

	/**
	 * Return the product's maximum size limit.
	 * @param  string $context
	 * @return int
	 */
	public function get_max_container_size( $context = 'view' ) {
		$value = $this->get_prop( 'max_container_size', 'edit' );
		return 'view' === $context ? apply_filters( 'woocommerce_mnm_max_container_size', $value, $this ) : $value;
	}

	/**
	 * Contained product IDs getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string $context
	 * @return array
	 */
	public function get_contents( $context = 'view' ) {
		return $this->get_prop( 'contents', $context );
	}

	/*
	|--------------------------------------------------------------------------
	| CRUD Setters.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Minimum raw price setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_min_raw_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->set_prop( 'min_raw_price', $value );
	}


	/**
	 * Minimum raw regular bundle price setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_min_raw_regular_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->set_prop( 'min_raw_regular_price', $value );
	}


	/**
	 * Per-Item Pricing setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_priced_per_product( $value ) {
		$this->set_prop( 'priced_per_product', wc_string_to_bool( $value ) );
	}


	/**
	 * Per-Item Shipping setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_shipped_per_product( $value ) {
		$this->set_prop( 'shipped_per_product', wc_string_to_bool( $value ) );
	}


	/**
	 * Set the product's minimum size limit.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_min_container_size( $value ) {
		$this->set_prop( 'min_container_size', '' !== $value ? absint( $value ) : 0 );
	}


	/**
	 * Set the product's maximum size limit.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_max_container_size( $value ) {
		$this->set_prop( 'max_container_size', '' !== $value ? absint( $value ) : '' );
	}


	/**
	 * Contained product IDs setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $value
	 */
	public function set_contents( $value ) {
		$this->set_prop( 'contents', array_map( 'absint', ( array ) $value ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Other methods.
	|--------------------------------------------------------------------------
	*/


	/**
	 * Maximum raw MnM bundle price getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_max_raw_price( $context = 'view' ) {
		$this->maybe_sync();
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() && '' !== $this->max_raw_price ? (double) $this->max_raw_price : $this->max_raw_price;
	}

	/**
	 * Maximum raw regular MnM bundle price getter.
	 *
	 * @since  1.2.0
	 *
	 * @param  string  $context
	 * @return string
	 */
	public function get_max_raw_regular_price( $context = 'view' ) {
		$this->maybe_sync();
		return in_array( $context, array( 'view', 'sync' ) ) && $this->is_priced_per_product() && '' !== $this->max_raw_regular_price ? (double) $this->max_raw_regular_price : $this->max_raw_regular_price;
	}


	/**
	 * Maximum raw price setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_max_raw_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->max_raw_price = $value;
	}


	/**
	 * Maximum raw regular bundle price setter.
	 *
	 * @since  1.2.0
	 *
	 * @param  mixed  $value
	 */
	public function set_max_raw_regular_price( $value ) {
		$value = wc_format_decimal( $value );
		$this->max_raw_regular_price = $value;
	}



	/**
	 * Get internal type.
	 * @return string
	 */
	public function get_type() {
		return 'mix-and-match';
	}


	/**
	 * Is this a NYP product?
	 * @return boolean
	 */
	public function is_nyp() {
		if ( ! isset( $this->is_nyp ) ) {
			$this->is_nyp = WC_Mix_and_Match()->compatibility->is_nyp( $this );
		}
		return $this->is_nyp;
	}


	/**
	 * Mimics the return of the product's children posts.
	 * these are the items that are allowed to be in the container (but aren't actually child posts)
	 *
	 * @return array
	 */
	public function get_children() {

		if ( ! is_array( $this->children ) ) {

			$this->children = array();

			if ( $contents = $this->get_contents() ) {
				foreach ( $contents as $mnm_item_id => $mnm_item_data ) {

					$product = wc_get_product( $mnm_item_id );

					if ( $product ) {
						$this->children[ $mnm_item_id ] = $product;
					}
				}
			}

		}

		return apply_filters( 'woocommerce_mnm_get_children', $this->children, $this );
	}


	/**
	 * Get the product object of one of the child items.
	 *
	 * @param  int 	$child_id
	 * @return object 	WC_Product or WC_Product_Variation
	 */
	public function get_child( $child_id ) {

		if ( isset( $this->children[ $child_id ] ) ) {
			$child = $this->children[ $child_id ];
		} else {
			$child = wc_get_product( $child_id );
		}

		return apply_filters( 'woocommerce_mnm_get_child', $child, $this );
	}


	/**
	 * Returns whether or not the product has any child product.
	 *
	 * @return bool
	 */
	public function has_children() {

		return sizeof( $this->get_children() ) ? true : false;
	}


	/**
	 * Get an array of available children for the current product.
	 *
	 * @access public
	 * @return array
	 */
	public function get_available_children() {

		$this->maybe_sync();

		$available_children = array();

		foreach ( $this->get_children() as $child_id => $child ) {

			if ( $this->is_child_available( $child_id ) ) {
				$available_children[ $child_id ] = $child;
			}
		}

		return $available_children;
    }


    /**
     * Is child item available for inclusion in container.
     *
     * @access public
     * @param  int 	$child_id
     * @return boolean
     */
	public function is_child_available( $child_id ) {

		$this->maybe_sync();

		if ( ! empty( $this->available_children ) && in_array( $child_id, $this->available_children ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Stock of container is synced to allowed bundled items.
	 *
	 * @return boolean
	 */
	public function is_synced() {
		return $this->is_synced;
	}


	/**
	 * Sync if not synced.
	 *
	 * @since 1.2.0
	 */
	public function maybe_sync() {
		if ( ! $this->is_synced() ) {
			$this->sync();
		}
	}


    /**
     * Sync child data such as price, availability, etc.
     *
     * @return void
     */
	public function sync() {

		/*-----------------------------------------------------------------------------------*/
		/*	Sync Availability Data.
		/*-----------------------------------------------------------------------------------*/

		$this->available_children           = array();

		$min_raw_price                      = $this->get_price( 'sync' );
		$max_raw_price                      = $this->get_price( 'sync' );
		$min_raw_regular_price              = $this->get_regular_price( 'sync' );
		$max_raw_regular_price              = $this->get_regular_price( 'sync' );


		$this->has_enough_children_in_stock = false;
		$this->backorders_required          = false;

		$items_in_stock                     = 0;
		$backorders_allowed                 = false;
		$unlimited_stock_available          = false;

		$children                           = $this->get_children();
		$min_container_size                 = $this->get_min_container_size();
		$max_container_size                 = $this->get_max_container_size();

		if ( empty( $children ) ) {
			return;
		}

		foreach ( $children as $child_id => $child ) {

			// Skip any item that isn't in stock/purchasable.
			if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $child->is_in_stock() ) {
				continue;
			}

			// Store available child id.
			$this->available_children[] = $child_id;

			$unlimited_child_stock_available = false;
			$child_stock_available           = 0;
			$child_backorders_allowed        = false;
			$child_sold_individually         = $child->is_sold_individually();

			// Calculate how many slots this child can fill with backordered / non-backordered items.
			if ( $child->is_purchasable() ) {
				if ( $child->managing_stock() ) {

					$child_stock = $child->get_stock_quantity();

					if ( $child_stock > 0 ) {

						$child_stock_available = $child_stock;

						if ( $child->backorders_allowed() ) {
							$backorders_allowed = $child_backorders_allowed = true;
						}

					} elseif ( $child->backorders_allowed() ) {
						$backorders_allowed = $child_backorders_allowed = true;
					}

				} elseif ( $child->is_in_stock() ) {
					$unlimited_stock_available = $unlimited_child_stock_available = true;
				}

				// Set max number of slots according to stock status and max container size.
				if ( $child_sold_individually ) {
					$this->sold_individually = true;
					$this->pricing_data[ $child_id ][ 'slots' ] = 1;
				} else if ( $max_container_size > 0 ) {
					if ( $unlimited_child_stock_available || $child_backorders_allowed ) {
						$this->pricing_data[ $child_id ][ 'slots' ] = $max_container_size;
					} else {
						$this->pricing_data[ $child_id ][ 'slots' ] = $child_stock_available > $max_container_size ? $max_container_size : $child_stock_available;
					}
				// If max_container_size = 0, then unlimited so only limit by stock.
				} else if ( $unlimited_child_stock_available || $child_backorders_allowed ){
					$this->pricing_data[ $child_id ][ 'slots' ] = '';
				} else {
					$this->pricing_data[ $child_id ][ 'slots' ] = $child_stock_available;
				}

			} else {
				$this->pricing_data[ $child_id ][ 'slots' ] = 0;
			}

			// Store price and slots for the min/max price calculation.
			if ( $this->is_priced_per_product() ) {

				$this->pricing_data[ $child_id ][ 'price_raw' ]         = $child->get_price( 'edit' );
				$this->pricing_data[ $child_id ][ 'price' ]             = $child->get_price();
				$this->pricing_data[ $child_id ][ 'regular_price_raw' ] = $child->get_regular_price( 'edit' );
				$this->pricing_data[ $child_id ][ 'regular_price' ]     = $child->get_regular_price();

				// Amount used up in "cheapest" config.
				$this->pricing_data[ $child_id ][ 'slots_filled_min' ] = 0;
				// Amount used up in "most expensive" config.
				$this->pricing_data[ $child_id ][ 'slots_filled_max' ] = 0;

				// Save sale status for parent.
				if ( $child->is_on_sale( 'edit' ) ) {
					$this->on_sale = true;
				}
			}

			$items_in_stock += $child_stock_available;
		}

		// Update data for container availability.
		if ( $unlimited_stock_available || $backorders_allowed || $items_in_stock >= $min_container_size ) {
			$this->has_enough_children_in_stock = true;
		}

		if ( ! $unlimited_stock_available && $backorders_allowed && $items_in_stock < $min_container_size ) {
			$this->backorders_required = true;
		}

		/*-----------------------------------------------------------------------------------*/
		/*	Per Product Pricing Min/Max Prices.
		/*-----------------------------------------------------------------------------------*/

		if ( $this->is_priced_per_product() ) {

			/*-----------------------------------------------------------------------------------*/
			/*	Min Price.
			/*-----------------------------------------------------------------------------------*/

			// Slots filled so far.
			$filled_slots = 0;

			// Sort by cheapest.
			uasort( $this->pricing_data, array( $this, 'sort_by_price' ) );

			if ( $this->has_enough_children_in_stock ) {

				// Fill slots and calculate min price.
				foreach ( $this->pricing_data as $child_id => $data ) {

					$slots_to_fill = $min_container_size - $filled_slots;

					$items_to_use = $this->pricing_data[ $child_id ][ 'slots_filled_min' ] = $this->pricing_data[ $child_id ][ 'slots' ] !== '' ? min( $this->pricing_data[ $child_id ][ 'slots' ], $slots_to_fill ) : $slots_to_fill;

					$filled_slots += $items_to_use;

					$min_raw_price         += $items_to_use * $this->pricing_data[ $child_id ][ 'price_raw' ];
					$min_raw_regular_price += $items_to_use * $this->pricing_data[ $child_id ][ 'regular_price_raw' ];

					if ( $filled_slots >= $min_container_size ) {
						break;
					}
				}

			} else {

				// In the unlikely even that stock is insufficient, just calculate the min price from the cheapest child
				foreach ( $this->pricing_data as $child_id => $data ) {
					$this->pricing_data[ $child_id ][ 'slots_filled_min' ] = 0;
				}

				$cheapest_child_id   = current( array_keys( $this->pricing_data ) );
				$cheapest_child_data = current( array_values( $this->pricing_data ) );

				$this->pricing_data[ $cheapest_child_id ][ 'slots_filled_min' ] = $min_container_size;

				$min_raw_price         += $cheapest_child_data[ 'price_raw' ] * $min_container_size;
				$min_raw_regular_price += $cheapest_child_data[ 'regular_price_raw' ] * $min_container_size;
			}

			/*-----------------------------------------------------------------------------------*/
			/*	Max Price.
			/*-----------------------------------------------------------------------------------*/

			// Slots filled so far.
			$filled_slots = 0;

			// Sort by most expensive.
			arsort( $this->pricing_data );

			if ( $this->has_enough_children_in_stock && $max_container_size !== '' && ! $this->is_nyp() ) {

				// Fill slots and calculate max price.
				foreach ( $this->pricing_data as $child_id => $data ) {

					$slots_to_fill = $max_container_size - $filled_slots;

					$items_to_use = $this->pricing_data[ $child_id ][ 'slots_filled_max' ] = $this->pricing_data[ $child_id ][ 'slots' ] !== '' ? min( $this->pricing_data[ $child_id ][ 'slots' ], $slots_to_fill ) : $slots_to_fill;

					$filled_slots += $items_to_use;

					$max_raw_price         += $items_to_use * $this->pricing_data[ $child_id ][ 'price_raw' ];
					$max_raw_regular_price += $items_to_use * $this->pricing_data[ $child_id ][ 'regular_price_raw' ];

					if ( $filled_slots >= $max_container_size ) {
						break;
					}
				}

			} else {

				// In the unlikely even that stock is insufficient, just calculate the max price from the most expensive child.
				foreach ( $this->pricing_data as $child_id => $data ) {
					$this->pricing_data[ $child_id ][ 'slots_filled_max' ] = 0;
				}

				if ( $max_container_size !== '' && ! $this->is_nyp() ) {

					$priciest_child_id   = current( array_keys( $this->pricing_data ) );
					$priciest_child_data = current( array_values( $this->pricing_data ) );

					$this->pricing_data[ $priciest_child_id ][ 'slots_filled_max' ] = $max_container_size;

					$max_raw_price         += $priciest_child_data[ 'price_raw' ] * $max_container_size;
					$max_raw_regular_price += $priciest_child_data[ 'regular_price_raw' ] * $max_container_size;

				}
			}
		}

		if ( $this->is_nyp() || ( $this->is_priced_per_product() && $max_container_size === '' ) ) {
			$max_raw_price = $max_raw_regular_price = '';
		}

		$this->is_synced = true;

		do_action( 'woocommerce_mnm_synced', $this );

		/*
		 * Set min/max raw (regular) prices.
		 */

		$raw_price_meta_changed = false;

		if ( $this->get_min_raw_price( 'sync' ) !== $min_raw_price || $this->get_min_raw_regular_price( 'sync' ) !== $min_raw_regular_price ) {
			$raw_price_meta_changed = true;
		}

		$this->set_min_raw_price( $min_raw_price );
		$this->set_min_raw_regular_price( $min_raw_regular_price );
		$this->set_max_raw_price( $max_raw_price );
		$this->set_max_raw_regular_price( $max_raw_regular_price );

		if ( $raw_price_meta_changed ) {
			$this->data_store->update_raw_prices( $this );
		}
    }


	/**
	 * Sort array data by price.
	 *
	 * @param  array $a
	 * @param  array $b
	 * @return -1|0|1
	 */
    private function sort_by_price( $a, $b ) {

	    if ( $a[ 'price' ] == $b[ 'price' ] ) {
	        return 0;
	    }

	    return ( $a[ 'price' ] < $b[ 'price' ] ) ? -1 : 1;
	}


	/**
	 * Get min/max mnm price.
	 *
	 * @param  string $min_or_max
	 * @return mixed
	 */
	public function get_mnm_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			$this->maybe_sync();

			$cache_key = ( $display ? 'display' : 'raw' ) . '_price_' . $min_or_max;

			if ( isset( $this->mnm_price_cache[ $cache_key ] ) ) {
				$price = $this->mnm_price_cache[ $cache_key ];
			} else {
				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_price';
				if ( $this->$raw_price_fn_name() === '' ) {
					$price = '';
				} else {
					$price = $display ? wc_get_price_to_display( $this, array( 'price' => $this->get_price() ) ) : $this->get_price();
					if ( ! empty( $this->pricing_data ) ) {
						foreach ( $this->pricing_data as $child_id => $data ) {
							$qty = $data[ 'slots_filled_' . $min_or_max ];
							if ( $qty ) {
								$child = $this->get_child( $child_id );
								if ( $display ) {
									$price += wc_get_price_to_display( $child, array( 'qty' => $qty, 'price' => $data[ 'price' ] ) );
								} else {
									$price += $qty * $data[ 'price' ];
								}
							}
						}
					}
				}
			}

		} else {

			$price = $this->get_price();

			if ( $display ) {
				$price = wc_get_price_to_display( $this, array( 'price' => $price ) );
			}
		}

		return $price;
	}


	/**
	 * Get min/max MnM regular price.
	 *
	 * @param  string $min_or_max
	 * @return mixed
	 */
	public function get_mnm_regular_price( $min_or_max = 'min', $display = false ) {

		if ( $this->is_priced_per_product() ) {

			$this->maybe_sync();

			$cache_key = ( $display ? 'display' : 'raw' ) . '_regular_price_' . $min_or_max;

			if ( isset( $this->mnm_price_cache[ $cache_key ] ) ) {
				$price = $this->mnm_price_cache[ $cache_key ];
			} else {
				$raw_price_fn_name = 'get_' . $min_or_max . '_raw_regular_price';
				if ( $this->$raw_price_fn_name() === '' ) {
					$price = '';
				} else {
					$price = $display ? wc_get_price_to_display( $this, array( 'price' => $this->get_regular_price() ) ) : $this->get_regular_price();
					if ( ! empty( $this->pricing_data ) ) {
						foreach ( $this->pricing_data as $child_id => $data ) {
							$qty = $data[ 'slots_filled_' . $min_or_max ];
							if ( $qty ) {
								$child = $this->get_child( $child_id );
								if ( $display ) {
									wc_get_price_to_display( $child, array( 'qty' => $qty, 'price' => $data[ 'regular_price' ] ) );
								} else {
									$price += $qty * $data[ 'regular_price' ];
								}
							}
						}
					}
				}
			}

		} else {

			$price = $this->get_regular_price();

			if ( $display ) {
				$price = wc_get_price_to_display( $this, array( 'price' => $price ) );
			}
		}

		return $price;
	}


	/**
	 * MnM price including tax.
	 *
	 * @return mixed
	 */
	public function get_mnm_price_including_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			$this->maybe_sync();

			$cache_key = 'price_incl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->mnm_price_cache[ $cache_key ] ) ) {
				$price = $this->mnm_price_cache[ $cache_key ];
			} else {
				$price = wc_get_price_including_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
				if ( ! empty( $this->pricing_data ) ) {
					foreach ( $this->pricing_data as $child_id => $data ) {
						$item_qty = $qty * $data[ 'slots_filled_' . $min_or_max ];
						if ( $item_qty ) {
							$child = $this->get_child( $child_id );
							$price += wc_get_price_including_tax( $child, array( 'qty' => $item_qty, 'price' => $data[ 'price' ] ) );
						}
					}
				}
			}

		} else {
			$price = wc_get_price_including_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
		}

		return $price;
	}


	/**
	 * Min/max MnM price excl tax.
	 *
	 * @return mixed
	 */
	public function get_mnm_price_excluding_tax( $min_or_max = 'min', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			$this->maybe_sync();

			$cache_key = 'price_excl_tax_' . $min_or_max . '_' . $qty;

			if ( isset( $this->mnm_price_cache[ $cache_key ] ) ) {
				$price = $this->mnm_price_cache[ $cache_key ];
			} else {
				$price = wc_get_price_excluding_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
				if ( ! empty( $this->pricing_data ) ) {
					foreach ( $this->pricing_data as $child_id => $data ) {
						$item_qty = $qty * $data[ 'slots_filled_' . $min_or_max ];
						if ( $item_qty ) {
							$child = $this->get_child( $child_id );
							$price += wc_get_price_excluding_tax( $child, array( 'qty' => $item_qty, 'price' => $data[ 'price' ] ) );
						}
					}
				}
			}

		} else {
			$price = wc_get_price_excluding_tax( $this, array( 'qty' => $qty, 'price' => $this->get_price() ) );
		}

		return $price;
	}


	/**
	 * Returns range style html price string without min and max.
	 *
	 * @param  mixed    $price    default price
	 * @return string             overridden html price string (old style)
	 */
	public function get_price_html( $price = '' ) {

		if ( $this->is_priced_per_product() ) {

			$this->maybe_sync();

			// Get the price
			if ( $this->get_mnm_price( 'min' ) === '' ) {

				$price = apply_filters( 'woocommerce_mnm_empty_price_html', '', $this );

			} else {

				$price = wc_price( $this->get_mnm_price( 'min', true ) );

				if ( $this->is_on_sale() && $this->get_mnm_regular_price( 'min' ) !== $this->get_mnm_price( 'min' ) ) {
					$regular_price = wc_price( $this->get_mnm_regular_price( 'min', true ) );

					if ( $this->get_mnm_price( 'min' ) !== $this->get_mnm_price( 'max' ) ) {
						$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-mix-and-match-products' ), wc_get_price_html_from_text(), $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix() );
					} else {
						$price = $this->get_price_html_from_to( $regular_price, $price ) . $this->get_price_suffix();
					}

					$price = apply_filters( 'woocommerce_mnm_sale_price_html', $price, $this );

				} elseif ( $this->get_mnm_price( 'min' ) == 0 && $this->get_mnm_price( 'max' ) == 0 ) {

					$free_string = apply_filters( 'woocommerce_mnm_show_free_string', false, $this ) ? __( 'Free!', 'woocommerce-mix-and-match-products' ) : $price;
					$price       = apply_filters( 'woocommerce_mnm_free_price_html', $free_string, $this );

				} else {

					if ( $this->get_mnm_price( 'min' ) !== $this->get_mnm_price( 'max' ) ) {
						$price = sprintf( _x( '%1$s%2$s', 'Price range: from', 'woocommerce-mix-and-match-products' ), wc_get_price_html_from_text(), $price . $this->get_price_suffix() );
					} else {
						$price = $price . $this->get_price_suffix();
					}

					$price = apply_filters( 'woocommerce_mnm_price_html', $price, $this );
				}
			}

			$price = apply_filters( 'woocommerce_get_mnm_price_html', $price, $this );

			return apply_filters( 'woocommerce_get_price_html', $price, $this );

		} else {

			return parent::get_price_html();
		}
	}


	/**
	 * Prices incl. or excl. tax are calculated based on the bundled products prices, so get_price_suffix() must be overridden to return the correct field in per-product pricing mode.
	 *
	 * @param  mixed    $price  price string
	 * @param  mixed    $qty  item quantity
	 * @return string    modified price html suffix
	 */
	public function get_price_suffix( $price = '', $qty = 1 ) {

		if ( $this->is_priced_per_product() ) {

			$price_display_suffix  = get_option( 'woocommerce_price_display_suffix' );

			if ( $price_display_suffix ) {
				$price_display_suffix = ' <small class="woocommerce-price-suffix">' . $price_display_suffix . '</small>';

				if ( false !== strpos( $price_display_suffix, '{price_including_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_including_tax}', wc_price( $this->get_mnm_price_including_tax() * $qty ), $price_display_suffix );
				}

				if ( false !== strpos( $price_display_suffix, '{price_excluding_tax}' ) ) {
					$price_display_suffix = str_replace( '{price_excluding_tax}', wc_price( $this->get_mnm_price_excluding_tax() * $qty ), $price_display_suffix );
				}
			}

			return apply_filters( 'woocommerce_get_price_suffix', $price_display_suffix, $this );

		} else {

			return parent::get_price_suffix();
		}
	}


	/**
	 * A MnM product must contain children and have a price in static mode only.
	 *
	 * @return boolean
	 */
	public function is_purchasable() {

		$purchasable = true;

		// Products must exist of course
		if ( ! $this->exists() ) {
			$purchasable = false;

		// When priced statically a price needs to be set
		} elseif ( $this->is_priced_per_product() == false && $this->get_price() === '' ) {

			$purchasable = false;

		// Check the product is published
		} elseif ( $this->get_status() !== 'publish' && ! current_user_can( 'edit_post', $this->get_id() ) ) {

			$purchasable = false;

		} elseif ( false === $this->has_children() ) {

			$purchasable = false;
		}

		return apply_filters( 'woocommerce_is_purchasable', $purchasable, $this );
	}


    /**
	 * Returns whether or not the product container has any available child items.
	 *
	 * @return bool
	 */
	public function has_available_children() {
		return sizeof( $this->get_available_children() ) ? true : false;
	}


    /**
	 * Returns whether or not the product container's price is based on the included items.
	 *
	 * @return bool
	 */
	public function is_priced_per_product() {
		return apply_filters( 'woocommerce_mnm_priced_per_product', $this->get_priced_per_product(), $this );
	}


    /**
	 * Returns whether or not the product container's shipping cost is based on the included items.
	 *
	 * @return bool
	 */
	public function is_shipped_per_product() {
		return apply_filters( 'woocommerce_mnm_shipped_per_product', $this->get_shipped_per_product(), $this );
	}


    /**
	 * Get availability of container.
	 *
	 * @return array
	 */
	public function get_availability() {

		$backend_availability_data = parent::get_availability();

		if ( ! parent::is_in_stock() || $this->is_on_backorder() ) {
			return $backend_availability_data;
		}

		if ( ! is_admin() ) {

			$this->maybe_sync();

			$availability = $class = '';

			if ( ! $this->has_enough_children_in_stock ) {
				$availability = __( 'Insufficient stock', 'woocommerce-mix-and-match-products' );
				$class        = 'out-of-stock';
			}

			if ( $this->backorders_required ) {
				$availability = __( 'Available on backorder', 'woocommerce-mix-and-match-products' );
				$class        = 'available-on-backorder';
			}

			if ( $class == 'out-of-stock' || $class == 'available-on-backorder' ) {
				return array( 'availability' => $availability, 'class' => $class );
			}
		}

		return $backend_availability_data;
	}


    /**
	 * Returns whether container is in stock
	 *
	 * @return bool
	 */
	public function is_in_stock() {

		$backend_stock_status = parent::is_in_stock();

		if ( ! is_admin() ) {

			$this->maybe_sync();

			if ( $backend_stock_status === true && ! $this->has_enough_children_in_stock ) {

				return false;
			}
		}

		return $backend_stock_status;
	}


	/**
	 * Override on_sale status of mnm product. In per-product-pricing mode, true if a one of the child products is on sale, or if there is a base sale price defined.
	 *
	 * @param  string  $context
	 * @return boolean
	 */
	public function is_on_sale( $context = 'view' ) {

		$is_on_sale = false;

		if ( 'update-price' !== $context && $this->is_priced_per_product() ) {

			$this->maybe_sync();

			$is_on_sale = parent::is_on_sale( $context ) || ( $this->on_sale && $this->get_min_raw_regular_price( $context ) > 0 );

		} else {
			$is_on_sale = parent::is_on_sale( $context );
		}

		return 'view' === $context ? apply_filters( 'woocommerce_mnm_is_on_sale', $is_on_sale, $this ) : $is_on_sale;
	}


	/**
	 * Get the add to cart button text
	 *
	 * @return string
	 */
	public function add_to_cart_text() {

		$text = __( 'Read More', 'woocommerce-mix-and-match-products' );

		if ( $this->is_purchasable() && $this->is_in_stock() ) {
			$text =  __( 'Select options', 'woocommerce-mix-and-match-products' );
		}

		return apply_filters( 'mnm_add_to_cart_text', $text, $this );
	}


	/**
	 * Get the data attributes
	 *
	 * @return string
	 */
	public function get_data_attributes() {
		$attributes = array(
			'per_product_pricing' => $this->is_priced_per_product() ? 'true' : 'false',
			'container_id'        => $this->get_id(),
			'min_container_size'      => $this->get_min_container_size(),
			'max_container_size'      => $this->get_max_container_size(),
			'base_price'          => wc_get_price_to_display( $this, array( 'price' => $this->get_price() ) ),
			'base_regular_price'  => wc_get_price_to_display( $this, array( 'price' => $this->get_regular_price() ) )
		);

		$attributes = (array) apply_filters( 'woocommerce_mix_and_match_data_attributes', $attributes, $this );

		$data = '';

		foreach ( $attributes as $a => $att ){
			$data .= sprintf( 'data-%s="%s" ', esc_attr( $a ), esc_attr( $att ) );
		}

		return $data;
	}


	/**
	 * Get the min/max quantity of a child.
	 *
	 * @param  string $min_or_max
	 * @param  string $child_id
	 * @return int
	 */
	public function get_child_quantity( $min_or_max, $child_id ) {

		$this->maybe_sync();

		$qty = '';

		if ( $mnm_product = $this->get_child( $child_id ) ) {

			if ( $min_or_max === 'min' ) {
				$qty = 0;
			} else {
				if ( isset( $this->pricing_data[ $child_id ][ 'slots' ] ) ) {
					$qty = $this->pricing_data[ $child_id ][ 'slots' ];
				}
			}

			return apply_filters( 'woocommerce_mnm_quantity_input_' . $min_or_max, $qty, $mnm_product, $this );
		}

		return $qty;
	}


	/**
	 * Get the availability message of a child, taking its purchasable status into account.
	 *
	 * @param  string $child_id
	 * @return string
	 */
	public function get_child_availability_html( $child_id ) {

		$availability_html = '';

		if ( $mnm_product = $this->get_child( $child_id ) ) {

			// If not purchasable, the stock status is of no interest.
			if ( ! $mnm_product->is_purchasable() ) {
				$availability_html = '<p class="unavailable">' . __( 'Temporarily unavailable', 'woocommerce-mix-and-match-products' ) . '</p>';
			} else {

				$availability      = $mnm_product->get_availability();
				$availability_html = empty( $availability[ 'availability' ] ) ? '' : '<p class="stock ' . esc_attr( $availability[ 'class' ] ) . '">' . esc_html( $availability[ 'availability' ] ) . '</p>';
				$availability_html = apply_filters( 'woocommerce_stock_html', $availability_html, $availability[ 'availability' ], $mnm_product );
			}
		}

		return $availability_html;
	}


	/*
	|--------------------------------------------------------------------------
	| Deprecated methods.
	|--------------------------------------------------------------------------
	*/

	public function get_base_price() {
		_deprecated_function( __METHOD__ . '()', '1.2.0', __CLASS__ . '::get_price()' );
		return $this->get_price( 'edit' );
	}
	public function get_base_regular_price() {
		_deprecated_function( __METHOD__ . '()', '1.2.0', __CLASS__ . '::get_regular_price()' );
		return $this->get_regular_price( 'edit' );
	}
	public function get_base_sale_price() {
		_deprecated_function( __METHOD__ . '()', '1.2.0', __CLASS__ . '::get_sale_price()' );
		return $this->get_sale_price( 'edit' );
	}
	public function get_mnm_data() {
		_deprecated_function( __METHOD__ . '()', '1.2.0', __CLASS__ . '::get_sale_price()' );
		return $this->get_contents();
	}
	public function get_container_size( $context = 'view' ) {
		_deprecated_function( __METHOD__ . '()', '1.2.0', __CLASS__ . '::get_sale_price()' );
		return $this->get_min_container_size();
	}
}
