<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

// Check if WooCommerce is active
if ( class_exists( 'Woocommerce' ) || class_exists( 'WooCommerce' ) ) {
		
	if ( class_exists( 'BETRS_Table_Rates' ) ) return;

	class BETRS_Table_Rates {

		/*
		 * Table Rate Method ID
		 */
		private $table_rate_id;

		/*
		 * Table Rates Options Class
		 */
		private $table_rate_options;

		/*
		 * Table Rates Options Class
		 */
		private $saved_table_rates = array();


		/**
		 * Constructor.
		 */
		public function __construct( $shipping_method = null ) {

			// Save table of rates
	   		//add_action( 'woocommerce_update_options_shipping_' . $this->table_rate_id, array( $this, 'process_table_rates' ) );
	   		//add_action( 'admin_head', array( $this, 'init_variables' ) );

	   		// add ajax commands
			add_action( 'wp_ajax_betrs_add_table_rates_row', array( $this, 'add_table_rates_row' ) );
			add_action( 'wp_ajax_betrs_add_extra_costs_op', array( $this, 'add_extra_costs_op' ) );
			add_action( 'wp_ajax_betrs_add_extra_conditions_op', array( $this, 'add_extra_conditions_op' ) );
			add_action( 'wp_ajax_betrs_add_costs_op_details', array( $this, 'add_costs_op_details' ) );
			add_action( 'wp_ajax_betrs_add_conds_op_details', array( $this, 'add_conds_op_details' ) );
			add_action( 'wp_ajax_betrs_generate_option_slug', array( $this, 'generate_option_slug' ) );
        	add_action( 'wp_ajax_betrs_add_table_costs_row', array( $this, 'add_table_costs_row' ) );
        	add_action( 'wp_ajax_betrs_export_table', array( $this, 'export_table_rows' ) );
        	add_action( 'wp_ajax_betrs_import_table', array( $this, 'import_table_rows' ) );
		}


		/**
		 * Setup & display table structure
		 */
		public function init_variables() {

			if( empty( $this->table_rate_options ) )
	    		$this->table_rate_options = new BETRS_Table_Options();
	    }


		/**
		 * Setup & display table structure
		 */
		public function display( $row_count = null, $rowID = 0 ) {

			do_action( 'betrs_before_table_rates_parent' );

			if( $row_count != null || ! empty( $this->saved_table_rates ) ) :
?>
<div id="BETRS-table-rates-parent">

	<?php $this->display_rows( $row_count, $rowID ); ?>

</div>
<div id="BETRS-table-rates-footer">

	<?php $this->display_footer(); ?>

</div>
<?php
			else :
?>
<h4 style="text-align:center;">
	<?php _e( 'How many shipping options are you giving your customers?', 'be-table-ship' ); ?>
	<select id="betrs-shipping_options-setup">
		<option></option>
		<?php for($n=1;$n<11;$n++) echo '<option>' . $n . '</option>'; ?>
	</select>
</h4>
<?php
			endif;

			do_action( 'betrs_after_table_rates_parent' );
		}


		/**
		 * Setup & display table structure
		 */
		public function display_rows( $row_count = null, $rowID = 0 ) {

			if( $row_count != null ) {
				for( $n = 0; $n < $row_count; $n++) {
					$this->single_row( array(), $rowID );
					$rowID++;
				}
			} elseif( is_array( $this->saved_table_rates ) && ! empty( $this->saved_table_rates ) ) {
				foreach( $this->saved_table_rates['settings'] as $row ) {
					$this->single_row( $row, $row['option_id'] );
					$rowID++;
				}
			} else
				echo '<p class="no-items">' . __( 'No items found.' ) . '</p>';
		}


		/**
		 * Setup & display table structure
		 */
		public function display_footer() {
?>
<div class="footer-ops">
	<span><a href="#" class="button add" id="add_table_rates_row"><?php _e( 'Add Another Option', 'be-table-ship' ); ?></a></span>
	<br class="clear" />
</div>
<?php
		}


		/**
		 * Generate new row for table
		 */
		public function single_row( $item, $rowID = 0 ) {
			global $betrs_shipping;

			if( $rowID == 0 ) $rowID++;

			// Initialize necessary variables
			$this->init_variables();
			if( is_array( $item ) && ! empty( $item ) ) {
				if( is_array( $item['rows'] ) ) {
					foreach( $item['rows'] as $key => $value ) {
						$item['rows'][$key]['option_ID'] = $item['option_id'];
						$item['rows'][$key]['row_ID'] = $key;
					}
					$this->table_rate_options->items = $item['rows'];
					$this->table_rate_options->prepare_items();
				}
			}
?>
<div class="single-row" data-row_id='<?php echo $rowID; ?>'>
	<h4 class="shipping-headline"><span>Shipping Option #<?php echo $rowID; ?></span></h4>
	<div class="single-row-left">
		<div class="titlewrap">
			<label for="option_title">
				<?php _e( 'Shipping Option Title', 'be-table-ship' ); ?>
				<span><a href="#" class="remove-shipping-option" alt="<?php _e( 'Remove Shipping Option', 'be-table-ship' ); ?>" title="<?php _e( 'Remove Shipping Option', 'be-table-ship' ); ?>"></a></span>
			</label>
			<input type="text" name="option_title[<?php echo $rowID; ?>]" spellcheck="true" autocomplete="off" value="<?php echo sanitize_text_field( $item['title'] ); ?>" />
			<input type="hidden" name="option_id[<?php echo $rowID; ?>]" value="<?php echo $rowID; ?>" />
		</div>
		<?php $this->table_rate_options->display(); ?>
	</div>
	<div class="single-row-right">
		<label><?php _e( 'Additional Settings', 'be-table-ship' ); ?></label>
		<div class="additional-settings">
			<div class="clear">
				<h5><?php _e( 'Default Selection', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="default_select[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="default_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item['default'], true ); ?>>
				    <label class="onoffswitch-label" for="default_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<div class="clear">
				<h5><?php _e( 'Hide Other Options', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="hide_ops[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="hide_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item['hide_ops'], true ); ?>>
				    <label class="onoffswitch-label" for="hide_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<!-- COMING SOON -->
			<div class="clear" style="display:none;">
				<h5><?php _e( 'Recursive Pricing', 'be-table-ship' ); ?></h5>
				<div class="onoffswitch">
				    <input type="checkbox" name="recursive_op[<?php echo $rowID; ?>]" class="onoffswitch-checkbox" id="recursive_switch[<?php echo $rowID; ?>]" <?php checked( 'on', $item['recursive'], true ); ?>>
				    <label class="onoffswitch-label" for="recursive_switch[<?php echo $rowID; ?>]">
				        <span class="onoffswitch-inner"></span>
				        <span class="onoffswitch-switch"></span>
				    </label>
				</div>
			</div>
			<?php do_action( 'betrs_additional_rate_settings' ); ?>
		</div>
	</div>
	<div style="clear:both;"></div>
</div>
<?php
		}


		/**
		 * Generate new row for table
		 */
		public function add_table_rates_row() {

			// determine starting row ID
			$rowID = isset( $_POST['rowID'] ) ? (int) $_POST['rowID'] : 1;

			// Initialize necessary classes
			$this->init_variables();
			
			if( isset( $_POST['optionCount'] ) )
				$this->display( (int) $_POST['optionCount'], $rowID );
			else
				$this->single_row( array(), $rowID );

			die();
		}


		/**
		 * Save table rates
		 */
		public function process_table_rates( $options_save_name ) {

			if( isset( $_POST['option_title'] ) && is_array( $_POST['option_title'] ) ) {
				// create save variable
				$table_rates_saving = array();

				foreach ( $_POST['option_title'] as $key => $title ) {
					$key = (int) $key;

					$table_rates_saving[ $key ] = array(
						'title'			=> apply_filters( 'betrs_save_shipping_rate_label', sanitize_text_field( $title ), (int) $_POST['option_id'][ $key ], sanitize_title( $options_save_name ) ),
						'option_id'		=> (int) $_POST['option_id'][ $key ],
						'default'		=> ( isset( $_POST['default_select'][ $key ] ) ) ? 'on' : 'off',
						'hide_ops'		=> ( isset( $_POST['hide_ops'][ $key ] ) ) ? 'on' : 'off',
						'recursive'		=> ( isset( $_POST['recursive_op'][ $key ] ) ) ? 'on' : 'off',
						'rows'			=> array(),
						);

					if( isset( $_POST['option_description'][$key] ) && is_array( $_POST['option_description'][$key] ) ) {
						
						foreach ( $_POST['option_description'][$key] as $r_key => $desc ) {
							// process saved conditions
							$saved_conditions = array();

							if( isset( $_POST['cond_type'][ $key ][ $r_key ] ) && is_array( $_POST['cond_type'][ $key ][ $r_key ] ) ) {
								foreach( $_POST['cond_type'][ $key ][ $r_key ] as $c_key => $cond ) {
									$saved_conditions[ $c_key ] = array(
										'cond_type'					=> sanitize_title( $_POST['cond_type'][ $key ][ $r_key ][ $c_key ] ),
										'cond_secondary'			=> sanitize_text_field( $_POST['cond_secondary'][ $key ][ $r_key ][ $c_key ] ),
										'cond_tertiary'				=> ( ! is_array( $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] ) ) ? wc_format_decimal( $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] ) : array_map( 'intval', $_POST['cond_tertiary'][ $key ][ $r_key ][ $c_key ] ),
										);
								}
							}

							// process saved costs
							$saved_costs = array();
							if( isset( $_POST['cost_value'][ $key ][ $r_key ] ) && is_array( $_POST['cost_value'][ $key ][ $r_key ] ) && count( $_POST['cost_value'][ $key ][ $r_key ] ) ) {
								foreach( $_POST['cost_value'][ $key ][ $r_key ] as $c_key => $cost ) {
									$saved_costs[ $c_key ] = array(
										'cost_value'				=> wc_format_decimal( $cost ),
										'cost_type'					=> sanitize_text_field( $_POST['cost_type'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra'				=> sanitize_title( $_POST['cost_op_extra'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra_val'			=> wc_format_decimal( $_POST['cost_op_extra_val'][ $key ][ $r_key ][ $c_key ] ),
										'cost_op_extra_secondary'	=> sanitize_title( $_POST['cost_op_extra_secondary'][ $key ][ $r_key ][ $c_key ] ),
										);
								}
							}

							// compile all options for this row
							$table_rates_saving[ $key ]['rows'][$r_key] = array(
								'conditions'	=> $saved_conditions,
								'costs'			=> $saved_costs,
								'description'	=> apply_filters( 'betrs_save_shipping_rate_description', wp_kses_data( $desc ), (int) $_POST['option_id'][ $key ], sanitize_title( $options_save_name ) ),
								);
						}
					}
				}

				$table_rates_saving = apply_filters( 'betrs_processed_table_rates_settings', $table_rates_saving );
				$final_save = array( 'mode' => 'guided', 'settings' => (array) $table_rates_saving );
				update_option( sanitize_title( $options_save_name ), $final_save );
			}

		}


	    /**
	     * add shipping option to single_row options
	     */
	    public function add_table_costs_row() {

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the opion's table
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        $this->table_rate_options->single_row( $item );

	        die();
	    }


	    /**
	     * add another cost option settings box
	     */
	    public function add_extra_costs_op() {
	        
	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the cost section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_cost_section( $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add another cost option settings box
	     */
	    public function add_extra_conditions_op() {

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

	        // Add new row to the conditions section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_conditions_section( $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add additional required cost settings
	     */
	    public function add_costs_op_details() {

	    	if( ! isset( $_POST['selected'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = ( isset( $_POST['optionID'] ) ) ? (int) $_POST['optionID'] : '';
	    	$row_ID = ( isset( $_POST['rowID'] ) ) ? (int) $_POST['rowID'] : '';

	        // Update options in the cost section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_cost_section_extras( sanitize_title( $_POST['selected'] ), $item, $option_ID, $row_ID );

	        die();
	    }


	    /**
	     * add additional required cost settings
	     */
	    public function add_conds_op_details() {

	    	if( ! isset( $_POST['selected'] ) ) exit;

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die();
	    	if( ! isset( $_POST['rowID'] ) ) die();

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];
	    	$cond_ID = ( isset( $_POST['condID'] ) ) ? (int) $_POST['condID'] : 0;

	        // Update options in the conditions section
	        $item = array( 'option_ID' => $option_ID, 'row_ID' => $row_ID );
	        echo $this->table_rate_options->generate_conditions_section_extras( sanitize_title( $_POST['selected'] ), $item, $option_ID, $row_ID, $cond_ID );

	        die();
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    public function set_saved_table_rates( $saved_table_rates ) {

	    	$this->saved_table_rates = $saved_table_rates;
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    function import_table_rows() {
	    	// return error if data not included
	    	if( ! isset( $_POST['csvFile'] ) )
	    		die('0');

	    	// Exit if no option ID is provided
	    	if( ! isset( $_POST['optionID'] ) ) die('0');
	    	if( ! isset( $_POST['rowID'] ) ) die('0');

	    	// sanitize incoming data
	    	$csvText = explode( PHP_EOL, stripslashes( trim( $_POST['csvFile'] ) ) );
	    	$csvClean = array_map( 'sanitize_text_field', $csvText );
	    	$csvArray = array_map( 'str_getcsv', $csvClean );

			// Initialize necessary variables
			$this->init_variables();
	    	$option_ID = (int) $_POST['optionID'];
	    	$row_ID = (int) $_POST['rowID'];

			// setup data array for options table class
			$compiled_ar = array();
			foreach( $csvArray as $key => $csv ) {
				$rowID = (int) $csv[0];
				if( ! array_key_exists( $rowID, $compiled_ar ) )
					$compiled_ar[ $rowID ] = array(
						'conditions'	=> array(),
						'costs'			=> array(),
						'description'	=> "",
						'option_ID'		=> $option_ID,
						);

				switch ( strtolower( sanitize_title( $csv[1] ) ) ) {
					case 'cond':
						$csv = array_pad( $csv, 5, '' );
						$compiled_ar[ $rowID ]['conditions'][] = array(
							'cond_type'			=> sanitize_title( $csv[ 2 ] ),
							'cond_secondary'	=> sanitize_text_field( $csv[ 3 ] ),
							'cond_tertiary'		=> sanitize_text_field( $csv[ 4 ] )
							);
						break;
					
					case 'cost':
						$csv = array_pad( $csv, 7, '' );
						$compiled_ar[ $rowID ]['costs'][] = array(
							'cost_value'				=> sanitize_text_field( $csv[ 2 ] ),
							'cost_type'					=> sanitize_text_field( $csv[ 3 ] ),
							'cost_op_extra'				=> sanitize_title( $csv[ 4 ] ),
							'cost_op_extra_val'			=> sanitize_text_field( $csv[ 5 ] ),
							'cost_op_extra_secondary'	=> sanitize_title( $csv[ 6 ] ),
							);
						break;
					
					case 'desc':
						$csv = array_pad( $csv, 3, '' );
						$compiled_ar[ $rowID ]['description'] = wp_kses_data( $csv[ 2 ] );
						break;
					
					default:
						$default = apply_filters( 'betrs_import_table_row_types', null, strtolower( sanitize_title( $csv[1] ) ), $csv );
						if( $default != null && is_array( $default ) ) $compiled_ar[ $rowID ] = $default;
						break;
				}

			}

			foreach( $compiled_ar as $key => $value) {
				$value['row_ID'] = $row_ID;
				$this->table_rate_options->single_row( $value );
				$row_ID++;
			}

	    	die();
	    }


	    /**
	     * setup the instance ID for saving purposes
	     */
	    function export_table_rows() {
	    	// return error if data not included
	    	if( ! isset( $_POST['rowsSelected'] ) || ! is_array( $_POST['rowsSelected'] ) )
	    		die('0');

	    	// unserialize the form data for processing
	    	$compiled_ar = array();
			foreach ($_POST['rowsSelected'] as $key_rs => $value) {

				parse_str( $value, $processed_ar );
				$key = $key_rs+1;
				reset( $processed_ar['option_description'] );
				$op_ID = key( $processed_ar['option_description'] );

				// add conditions to csv data
				foreach( $processed_ar['cond_type'][ $op_ID ][ $key_rs ] as $cond_ID => $cond ) {
					$compiled_ar[] = array( 
						$key, 
						'Cond', 
						sanitize_title( $processed_ar['cond_type'][ $op_ID ][ $key_rs ][ $cond_ID ] ),
						sanitize_text_field( stripslashes( $processed_ar['cond_secondary'][ $op_ID ][ $key_rs ][ $cond_ID ] ) ),
						( ! is_array( $processed_ar['cond_tertiary'][ $op_ID ][ $key_rs ][ $cond_ID ] ) ) ? sanitize_text_field( $processed_ar['cond_tertiary'][ $op_ID ][ $key_rs ][ $cond_ID ] ) : json_encode( array_map( 'intval', $processed_ar['cond_tertiary'][ $op_ID ][ $key_rs ][ $cond_ID ] ) ),
						);
				}

				// add costs to csv data
				foreach( $processed_ar['cost_type'][ $op_ID ][ $key_rs ] as $cost_ID => $cost ) {
					$compiled_ar[] = array( 
						$key,
						'Cost',
						sanitize_text_field( $processed_ar['cost_value'][ $op_ID ][ $key_rs ][ $cost_ID ] ),
						sanitize_text_field( $processed_ar['cost_type'][ $op_ID ][ $key_rs ][ $cost_ID ] ),
						sanitize_title( $processed_ar['cost_op_extra'][ $op_ID ][ $key_rs ][ $cost_ID ] ),
						sanitize_text_field( $processed_ar['cost_op_extra_val'][ $op_ID ][ $key_rs ][ $cost_ID ] ),
						sanitize_title( $processed_ar['cost_op_extra_secondary'][ $op_ID ][ $key_rs ][ $cost_ID ] ),
						);
				}

				// add descriptions to csv data
				$compiled_ar[] = array( $key, 'Desc', wp_kses_data( stripslashes( $processed_ar['option_description'][ $op_ID ][ 0 ] ) ) );

			}

	    	// format CSV file data for exporting
	    	$this->outputCSV( $compiled_ar, $op_ID );

	    	die();
	    }


	    /**
	     * Create PHP file for CSV file output
	     */
	    function outputCSV( $data, $option_ID ) {
	    	$option_ID = (int) $option_ID;
    		$filename = "betrs_backup-option_" . $option_ID;

		    header("Content-type: text/csv");
		    header("Content-Disposition: attachment; filename={$filename}.csv");
		    header("Pragma: no-cache");
		    header("Expires: 0");

		    $this->formatCSV( $data );
		}


	    /**
	     * Format data for CSV file output
	     */
	    function formatCSV( $data ) {
	    	if( ! is_array( $data ) ) die('0');

	    	// Create a stream opening it with read / write mode
        	$stream = fopen( 'data://text/plain,' . "", 'w+' );

	        // Iterate over the data, writting each line to the text stream
			foreach ($data as $val) {
			    fputcsv($stream, $val);
			}

			// Rewind the stream
			rewind($stream);

			// You can now echo it's content
			echo stream_get_contents($stream);

			// Close the stream 
			fclose($stream);
	    }
	}

}

?>