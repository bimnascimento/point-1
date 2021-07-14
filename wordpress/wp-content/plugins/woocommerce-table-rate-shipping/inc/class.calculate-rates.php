<?php
/*
 * Table Rate Shipping Method Extender Class
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'WooCommerce' ) ) {

	if ( class_exists( 'BE_Table_Rate_Calculate' ) ) return;

	class BE_Table_Rate_Calculate {

		/*
		 * Shipping Package Information
		 */
		private $method;

		/*
		 * Table Rates from Database
		 */
		private $table_rates;

		/**
		 * Cloning is forbidden. Will deactivate prior 'instances' users are running
		 *
		 * @since 4.0
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @since 4.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'be-table-ship' ), '4.0' );
		}

		/**
		 * __construct function.
		 *
		 * @access public
		 * @return void
		 */
		function __construct( $method, $table_rates ) {

			$this->method 		= $method;
			$this->table_rates 	= $table_rates;
		}


		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function calculate_shipping( $package = array() ) {

			if( is_array( $package ) && ! empty( $package ) && isset( $package['contents'] ) ) {
				// calculate cart statistics for processing table rates
				$cart_data = array();

				switch( $this->method->condition ) {
					case 'per-order':
						$cart_data = array( 'per-order' => $this->calculate_totals_order( $package['contents'] ) );
						break;

					case 'per-item':
						$cart_data = $this->calculate_totals_item( $package['contents'] );
						break;

					case 'per-line-item':
						$cart_data = $this->calculate_totals_item( $package['contents'], true );
						break;
					
					case 'per-class':
						$cart_data = $this->calculate_totals_class( $package['contents'] );
						break;
					
					default:
						$cart_data = apply_filters( 'betrs_calculate_method_totals', $cart_data, $this->method->condition, $package, $this->method );
						break;
				}

				// send to calculator for processing
				if( ! empty( $cart_data ) ) {
					$rates = array();
					foreach( $cart_data as $key => $data ) {
						$rates[ $key ] = $this->process_table_rates( $data );
					}
				}
				$shipping_options = $first_option = array();

				// ensure that all rates cover all items in the cart
				if( is_array( $rates ) && count( $rates ) > 0 ) {
					$n = 0;
					foreach( $rates as $key => $rate ) {
						if( $n == 0 ) {
							$first_option = $rate;
							$first_key = $key;
							$n++;
							continue;
						}

						$rates[ $key ] = array_intersect_key( $rate, $first_option );
						$first_option = array_intersect_key( $first_option, $rate );
					}
					$rates[ $first_key ] = $first_option;
				}

				// Adjust for single class only if necessary
				if( $this->method->condition == 'per-class' ) {
					switch( $this->method->single_class ) {
						case 'priority':
							$highest_key = $highest_priority = 0;

							foreach( $rates as $class_key => $options ) {
								$get_priority = get_term_meta( $class_key, 'priority', true );

								if( $get_priority > $highest_priority ) {
									$highest_priority = $get_priority;
									$highest_key = $class_key;
								}

							}
							return $rates[ $highest_key ];
							break;
						
						case 'cost_high':
							$options_for_return = array();

							foreach( $rates as $class_key => $options ) {

								foreach( $options as $op_key => $option ) {

									if( ! isset( $options_for_return[ $op_key] ) ) {
										// initialize an option for this ID
										$options_for_return[ $op_key ] = $option;

									} else {
										// determine if this is the option to be returned based on cost
										if( $option['cost'] > $options_for_return[ $op_key ]['cost'] )
											$options_for_return[ $op_key ] = $option;
									}
								}

							}

							return $options_for_return;
							break;

						case 'disabled':
							break;
						
						default:
							return apply_filters( 'betrs_condition_single_class_' . $this->method->single_class, $rates );
							break;
					}
				}

				// compile for return (combine Per Item and Per Class data to one price)
				$shipping_options = array();
				foreach( $rates as $key => $rate ) {
					foreach( $rate as $op_key => $op ) {
						if( ! isset( $shipping_options[ $op_key ] ) ) {
							$shipping_options[ $op_key ] = $op;
							continue;
						}

						$shipping_options[ $op_key ]['cost'] += $op['cost'];
						$shipping_options[ $op_key ]['description'] = $op['description'];
					}
				}

				return $shipping_options;
			}

		}


		/**
		 * calculate order totals (Per Order).
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function calculate_totals_order( $items ) {
			// setup initialized variables
			$subtotal = $quantity = $weight = $height = $width = $length = $area = $volume = 0;
			$products = $shipping_classes = $categories = array();

			// cycle through cart items
			foreach( $items as $item_ar ) {

				// only count the ones that apply to shipping
				if( isset( $item_ar['data'] ) && $item_ar['data']->needs_shipping() ) {
					$item = $item_ar['data'];

					// manage measurement calculations
					$t_height = $item->get_height();
					$t_width = $item->get_width();
					$t_length = $item->get_length();

					$height += $t_height * $item_ar['quantity'];
					$width += $t_width * $item_ar['quantity'];
					$length += $t_length * $item_ar['quantity'];
					$area += $t_height * $t_width * $item_ar['quantity'];
					$volume += $t_height * $t_width * $t_length * $item_ar['quantity'];

					// adjust number data
					$subtotal += $this->get_line_item_price( $item_ar );
					$quantity += $item_ar['quantity'];
					$weight += $this->get_line_item_weight( $item ) * $item_ar['quantity'];

					// add additional product information
					$products[] = $item->get_id();
					$shipping_classes[] = $item->get_shipping_class_id();
					$get_categories = ( $item->get_type() == 'variation' ) ? get_the_terms( $item->get_parent_id(), 'product_cat' ) : get_the_terms( $item->get_id(), 'product_cat' );
					if( $get_categories ) {
						foreach( $get_categories as $cat ){
						   $categories[] = $cat->term_id;
						}
					}
				}

			}

			$shipping_classes = array_unique( $shipping_classes );
			$categories = array_unique( $categories );
			$weight = ( $this->method->round_weight === 'yes' ) ? ceil( $weight ) : $weight;

			// setup outgoing data for return
			$data = array(
				'subtotal' 			=> $subtotal,
				'quantity' 			=> $quantity,
				'weight' 			=> $weight,
				'height' 			=> $height,
				'width' 			=> $width,
				'length' 			=> $length,
				'area' 				=> $area,
				'volume' 			=> $volume,
				'products' 			=> $products,
				'shipping_classes' 	=> $shipping_classes,
				'categories' 		=> $categories,
				);

			return $data;

		}


		/**
		 * calculate order totals (Per Item and Per Line Item).
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function calculate_totals_item( $items, $per_line = false ) {
			// setup initialized variables
			$data = array();

			// cycle through cart items
			foreach( $items as $item_ar ) {

				// only count the ones that apply to shipping
				if( isset( $item_ar['data'] ) && $item_ar['data']->needs_shipping() ) {
					$item = $item_ar['data'];
					$categories = array();

					// manage measurement calculations
					$t_height = $item->get_height();
					$t_width = $item->get_width();
					$t_length = $item->get_length();

					// add additional product information
					$get_categories = get_the_terms( $item->get_id(), 'product_cat' );
					if( $get_categories ) {
						foreach( $get_categories as $cat ){
						   $categories[] = $cat->term_id;
						}
					}

					// adjust numbers if Per Line Item is the selected condition
					if( $per_line === true ) {
						$weight = $this->get_line_item_weight( $item ) * $item_ar['quantity'];
						$weight = ( $this->method->round_weight === 'yes' ) ? ceil( $weight ) : $weight;

						// setup outgoing data for return
						$data[ $item->get_id() ] = array(
							'subtotal' 			=> $this->get_line_item_price( $item_ar ),
							'quantity' 			=> $item_ar['quantity'],
							'weight' 			=> $weight,
							'height' 			=> $t_height * $item_ar['quantity'],
							'width' 			=> $t_width * $item_ar['quantity'],
							'length' 			=> $t_length * $item_ar['quantity'],
							'area' 				=> $t_height * $t_width * $item_ar['quantity'],
							'volume' 			=> $t_height * $t_width * $t_length * $item_ar['quantity'],
							'products' 			=> array( $item->get_id() ),
							'shipping_classes' 	=> array( $item->get_shipping_class_id() ),
							'categories' 		=> $categories,
							);
					} else {
						$weight = $this->get_line_item_weight( $item );
						$weight = ( $this->method->round_weight === 'yes' ) ? ceil( $weight ) : $weight;

						// setup outgoing data for return
						$data[ $item->get_id() ] = array(
							'subtotal' 			=> $this->get_line_item_price( $item_ar ) / $item_ar['quantity'],
							'quantity' 			=> $item_ar['quantity'],
							'weight' 			=> $weight,
							'height' 			=> $t_height,
							'width' 			=> $t_width,
							'length' 			=> $t_length,
							'area' 				=> $t_height * $t_width,
							'volume' 			=> $t_height * $t_width * $t_length,
							'products' 			=> array( $item->get_id() ),
							'shipping_classes' 	=> array( $item->get_shipping_class_id() ),
							'categories' 		=> $categories,
							);
					}

				}

			}

			return $data;

		}


		/**
		 * calculate order totals.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function calculate_totals_class( $items ) {
			// setup empty return value
			$data = array();

			// cycle through cart items
			foreach( $items as $item_ar ) {

				// only count the ones that apply to shipping
				if( isset( $item_ar['data'] ) && $item_ar['data']->needs_shipping() ) {
					// initialize necessary variables
					$item = $item_ar['data'];
					$shipping_class_id = $item->get_shipping_class_id();

					if( ! isset( $data[ $shipping_class_id ] ) ) {

						$data[ $shipping_class_id ] = array(
							'subtotal' 			=> 0,
							'quantity' 			=> 0,
							'weight' 			=> 0,
							'height' 			=> 0,
							'width' 			=> 0,
							'length' 			=> 0,
							'area' 				=> 0,
							'volume' 			=> 0,
							'products' 			=> array(),
							'shipping_classes' 	=> array( $shipping_class_id ),
							'categories' 		=> array(),
							);
					}

					// manage measurement calculations
					$t_height = $item->get_height();
					$t_width = $item->get_width();
					$t_length = $item->get_length();

					// add additional product information
					$get_categories = get_the_terms( $item->get_id(), 'product_cat' );
					if( $get_categories ) {
						foreach( $get_categories as $cat ){
						   $data[ $shipping_class_id ]['categories'][] = $cat->term_id;
						}
					}

					// calculate product weight based on settings
					$weight = $this->get_line_item_weight( $item ) * $item_ar['quantity'];
					$weight = ( $this->method->round_weight === 'yes' ) ? ceil( $weight ) : $weight;

					// setup outgoing data for return
					$data[ $shipping_class_id ][ 'subtotal' ]		+= $this->get_line_item_price( $item_ar );
					$data[ $shipping_class_id ][ 'quantity' ]		+= $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'weight' ]			+= $weight;
					$data[ $shipping_class_id ][ 'height' ]			+= $t_height * $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'width' ]			+= $t_width * $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'length' ]			+= $t_length * $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'area' ]			+= $t_height * $t_width * $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'volume' ]			+= $t_height * $t_width * $t_length * $item_ar['quantity'];
					$data[ $shipping_class_id ][ 'products' ][]		= $item->get_id();

				}

			}

			// clear out unnecessary data
			foreach( $data as $key => $value ) {
				$data[ $key ]['categories'] = array_unique( $value['categories'] );
			}

			return $data;
		}


		/**
		 * Find valid options for calculated cart data.
		 *
		 * @access public
		 * @param array $package (default: array())
		 * @return array
		 */
		function process_table_rates( $cart_data ) {
			// setup necessary variables
			$rates = array();

			if( ! empty( $cart_data ) && ! empty( $this->table_rates ) ) {
				// step through each table rate row
				foreach( $this->table_rates['settings'] as $o_key => $option ) {
					$cost = false;
					$description = "";

					foreach( $option['rows'] as $r_key => $row ) {

						if( ! empty( $row['conditions'] ) ) {
							$qualifies = false;
							$results = array();

							foreach( $row['conditions'] as $cond ) {
								$results[] = $this->determine_condition_result( $cond, $cart_data );
							}
							if( in_array( true, $results ) && !in_array( false, $results ) )
								$qualifies = true;

							if( $qualifies === true ) {

								// calculate costs for qualifying row
								$cost = $this->calculate_shipping_costs( $row['costs'], $cart_data );
								$description = $row['description'];

							}
						} else {
							$cost = $this->calculate_shipping_costs( $row['costs'], $cart_data );
							$description = $row['description'];
						}

					}

					// setup shipping option if cart qualifies
					if( $cost !== false ) {
						$option_id = $this->generate_option_id( $option['option_id'] );
						$rates[ $option['option_id'] ] = array(
							'id'        	=> $option_id,
							'label'     	=> $option['title'],
							'cost'      	=> $cost,
							'description' 	=> $description,
							'default'	 	=> $option['default'],
							'hide_ops'	 	=> $option['hide_ops'],
							);
					}
				}
			}

			return $rates;
		}


		/**
		 * Determine if cart information qualifies for given condition.
		 *
		 * @access public
		 * @param array $cond, array $cart_data
		 * @return bool
		 */
		function determine_condition_result( $cond, $cart_data ) {

			if( is_array( $cond ) && isset( $cond['cond_type'] ) ) {
				$cond_type = sanitize_title( $cond['cond_type'] );

				// perform the correct check based on condition type
				if( in_array( $cond_type, array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ) ) ) {
					// allow third party plugins to adjust numbers conversion
					$cond_tertiary = apply_filters( 'betrs_condition_tertiary_' . $cond_type, floatval( $cond['cond_tertiary'] ), $cond );

					if( isset( $cart_data[ $cond_type ] ) )
						$comparison = $cart_data[ $cond_type ];

					else
						return false;

					switch( $cond['cond_secondary'] ) {
						case 'greater_than':
							if( $comparison > $cond_tertiary )
								return true;
							break;
						case 'less_than':
							if( $comparison < $cond_tertiary )
								return true;
							break;
						case 'equal_to':
							if( $comparison == $cond_tertiary )
								return true;
							break;
						
						default:
							return apply_filters( 'betrs_condition_secondary_numbers', false, $cond, $cart_data );
							break;
					}

				} elseif( in_array( $cond_type, array( 's_class', 'product', 'category' ) ) ) {
					$cond_tertiary = apply_filters( 'betrs_condition_tertiary_' . $cond_type, $cond['cond_tertiary'], $cond );
					
					switch( $cond_type ) {
						case 's_class':
							$comparison = apply_filters( 'betrs_comparison_tertiary_' . $cond_type, $cart_data['shipping_classes'], $cond );
							break;
						case 'product':
							$comparison = apply_filters( 'betrs_comparison_tertiary_' . $cond_type, $cart_data['products'], $cond );
							break;
						case 'category':
							$comparison = apply_filters( 'betrs_comparison_tertiary_' . $cond_type, $cart_data['categories'], $cond );
							break;
						
						default:
							return apply_filters( 'betrs_condition_secondary_classes', false, $cond, $cart_data );
							break;
					}

					if( is_array( $comparison ) ) {
						if( is_array( $cond_tertiary ) ) {
							if( $cond['cond_secondary'] === 'includes' ) {
								foreach( $comparison as $comp ) {
									if( in_array( $comp, $cond_tertiary ) )
										return true;
								}
							}

							if( $cond['cond_secondary'] === 'excludes' ) {
								$temp = true;
								foreach( $comparison as $comp ) {
									if( in_array( $comp, $cond_tertiary ) )
										$temp = false;
								}

								return $temp;
							}
						} else {

							if( $cond['cond_secondary'] === 'includes' && in_array( $cond_tertiary, $comparison ) )
								return true;

							if( $cond['cond_secondary'] === 'excludes' && ! in_array( $cond_tertiary, $comparison ) )
								return true;
						}
					}

				} elseif( $cond_type == 'dates' ) {
					$dates = json_decode( stripslashes( $cond['cond_secondary'] ) );

					if( isset( $dates->start ) && isset( $dates->end ) ) {
						// convert dates to timestamps
						$start_date = strtotime( $dates->start );
						$end_date = strtotime( $dates->end );
						$now = strtotime("now");

						// Check that user date is between start & end
						return ( ( $now >= $start_date ) && ( $now <= $end_date ) );
					}

				} elseif( $cond_type == 'dayweek' ) {
					
					if( sanitize_title( $cond['cond_secondary'] ) == date( 'w' ) )
						return true;

				} else {

					return apply_filters( 'betrs_determine_condition_result', false, $cond, $cart_data );
				}

			}

			return false;
		}


		/**
		 * Determine if cart information qualifies for given condition.
		 *
		 * @access public
		 * @param array $cond, array $cart_data
		 * @return float
		 */
		function calculate_shipping_costs( $costs, $cart_data ) {
			if( ! is_array( $costs ) ) return 0;

			// cycle through the different cost options
	        $cost_ops = apply_filters( 'betrs_shipping_cost_options', array(
	                ''          => get_woocommerce_currency_symbol(),
	                '%'         => '%',
	                'x'         => __( 'multiplied by', 'be-table-ship' ),
	                'every'     => __( 'for every', 'be-table-ship' ),
	            ) );
	        $calcs = array();

	        foreach( $costs as $cost ) {

		        switch ( $cost['cost_type'] ) {
		        	case '':
		        		$calcs[] = $cost['cost_value'];
		        		break;
		        	case '%':
		        		$calcs[] = $cart_data['subtotal'] * ( $cost['cost_value'] / 100 );
		        		break;
		        	case 'x':
		        		$calcs[] = $cost['cost_value'] * $cart_data[ $cost['cost_op_extra'] ];
		        		break;
		        	case 'every':
		        		if( $cost['cost_op_extra'] === 'dimensions' ) {
		        			// determine which dimensional value to multiply by
		        			if( isset( $cart_data[ $cost['cost_op_extra_secondary'] ] ) ) {
		        				$calcs[] = ceil( $cart_data[ $cost['cost_op_extra_secondary'] ] / $cost['cost_op_extra_val'] ) * $cost['cost_value'];
		        			}
		        		} else {
		        			// calculate the value based on select data
		        			if( isset( $cart_data[ $cost['cost_op_extra'] ] ) ) {
		        				$calcs[] = ceil( $cart_data[ $cost['cost_op_extra'] ] / $cost['cost_op_extra_val'] ) * $cost['cost_value'];
		        			}
		        		}
		        		break;
		        	
		        	default:
		        		$calcs[] = (float) apply_filters( 'betrs_determine_cost_result', $cost['cost_value'], $cost['cost_type'], $cost );
		        		break;
		        }

	        }

	        return array_sum( array_map( 'floatval', $calcs ) );
		}


		/**
		 * determine item price based on TRS tax and coupon settings.
		 *
		 * @access public
		 * @param array $item
		 * @return float
		 */
		function get_line_item_price( $item ) {

			if( $this->method->include_coupons === 'yes' ) {
				if( $this->method->includetax === 'yes' ) {
					return $item['line_total'] + $item['line_tax'];
				} else {
					return $item['line_total'];
				}
			} else {
				if( $this->method->includetax === 'yes' ) {
					return $item['line_subtotal'] + $item['line_subtotal_tax'];
				} else {
					return $item['line_subtotal'];
				}
			}

			return 0;
		}


		/**
		 * determine item weight based on TRS volumetric settings.
		 *
		 * @access public
		 * @param array $item
		 * @return float
		 */
		function get_line_item_weight( $item ) {

			if( isset( $this->method->volumetric_number ) && is_numeric( $this->method->volumetric_number ) && $this->method->volumetric_number > 0 ) {

				// manage measurement calculations
				$height = ( $item->get_height() === '' ) ? 1 : $item->get_height();
				$width = ( $item->get_width() === '' ) ? 1 : $item->get_width();
				$length = ( $item->get_length() === '' ) ? 1 : $item->get_length();

				$volume = $height * $width * $length;

				if( $this->method->volumetric_operand === '' ) {
					$volumetric = $volume * $this->method->volumetric_number;
				} else {
					$volumetric = $volume / $this->method->volumetric_number;
				}

				if( $volumetric > $item->get_weight() )
					return $volumetric;

			}

			return $item->get_weight();
		}


		/**
		 * Setup shipping option ID tag.
		 *
		 * @access public
		 * @param int $option_id
		 * @return string
		 */
		function generate_option_id( $option_id ) {
			$option_id = (int) $option_id;

			return $this->method->id . '_' . $this->method->instance_id . '-' . $option_id;
		}

	}

}

?>