<?php
/**
 * Table Rate Shipping Options Table
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'BETRS_Table_Options' ) ) :

/*************************** LOAD THE BASE CLASS ********************************/

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/************************** CREATE A PACKAGE CLASS ******************************/

class BETRS_Table_Options extends WP_List_Table {

    /*
     * List of cost options
     */
    private $cost_ops = array();

    /*
     * List of unit types for 'multiplied by' cost option
     */
    private $cost_units_multi = array();

    /*
     * List of unit types for 'every' cost option
     */
    private $cost_units_every = array();

    /*
     * List of unit types
     */
    private $dimension_types = array();

    /*
     * List of Conditional Statements
     */
    private $conditional_statements = array();

    /*
     * Secondary rules for Conditional Statements
     */
    private $secondary_statements = array();

    function __construct( $args = array() ){
        global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'table_rate', 
            'plural'    => 'table_rates',
            'ajax'      => false
        ) );

        $this->cost_ops = apply_filters( 'betrs_shipping_cost_options', array(
                ''          => get_woocommerce_currency_symbol(),
                '%'         => '%',
                'x'         => __( 'multiplied by', 'be-table-ship' ),
                'every'     => __( 'for every', 'be-table-ship' ),
            ) );

        $this->cost_units_every = apply_filters( 'betrs_shipping_cost_units_every', array(
                'price'     => get_woocommerce_currency_symbol(),
                'weight'    => get_option( 'woocommerce_weight_unit' ),
                'dimensions'=> get_option( 'woocommerce_dimension_unit' ),
                'quantity'     => __( 'Item(s)', 'be-table-ship' ),
            ) );

        $this->cost_units_multi = apply_filters( 'betrs_shipping_cost_units_multiplied', array(
                'weight'    => __( 'Weight', 'woocommerce' ),
                'length'    => __( 'Length', 'woocommerce' ),
                'width'     => __( 'Width', 'woocommerce' ),
                'height'    => __( 'Height', 'woocommerce' ),
                'area'      => __( 'Surface Area', 'be-table-ship' ),
                'volume'    => __( 'Volume', 'be-table-ship' ),
                'quantity'     => __( 'Item Quantity', 'be-table-ship' ),
            ) );

        $this->dimension_types = apply_filters( 'betrs_shipping_dimension_types', array(
                'length'    => __( 'Length', 'woocommerce' ),
                'width'     => __( 'Width', 'woocommerce' ),
                'height'    => __( 'Height', 'woocommerce' ),
                'area'      => __( 'Surface Area', 'be-table-ship' ),
                'volume'    => __( 'Volume', 'be-table-ship' ),
            ) );

        $this->conditional_statements = apply_filters( 'betrs_shipping_cost_conditionals', array(
                'subtotal'  => __( 'Subtotal', 'woocommerce' ),
                'quantity'  => __( 'Quantity', 'woocommerce' ),
                'weight'    => __( 'Weight', 'woocommerce' ),
                'height'    => __( 'Height', 'woocommerce' ),
                'width'     => __( 'Width', 'woocommerce' ),
                'length'    => __( 'Length', 'woocommerce' ),
                'area'      => __( 'Surface Area', 'be-table-ship' ),
                'volume'    => __( 'Volume', 'be-table-ship' ),
                's_class'   => __( 'Shipping Class', 'woocommerce' ),
                'product'   => __( 'Products', 'woocommerce' ),
                'category'  => __( 'Categories', 'woocommerce' ),
                'dates'     => __( 'Date Range', 'be-table-ship' ),
                'dayweek'   => __( 'Day of Week', 'be-table-ship' ),
            ) );

        $this->secondary_statements = apply_filters( 'betrs_shipping_cost_conditionals_secondary', array(
            'greater_than'  => array(
                                    'title'         => __( 'Greater than', 'be-table-ship' ),
                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
                                    'tertiary'      => 'text',
                                    ),
            'less_than'     => array(
                                    'title'         => __( 'Less than', 'be-table-ship' ),
                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
                                    'tertiary'      => 'text',
                                    ),
            'equal_to'      => array(
                                    'title'         => __( 'Equal to', 'be-table-ship' ),
                                    'conditions'    => array( 'subtotal', 'quantity', 'weight', 'height', 'width', 'length', 'area', 'volume' ),
                                    'tertiary'      => 'text',
                                    ),
            'includes'      => array(
                                    'title'         => __( 'Includes', 'be-table-ship' ),
                                    'conditions'    => array( 's_class', 'product', 'category' ),
                                    'tertiary'      => 'select',
                                    ),
            'excludes'      => array(
                                    'title'         => __( 'Excludes', 'be-table-ship' ),
                                    'conditions'    => array( 's_class', 'product', 'category' ),
                                    'tertiary'      => 'select',
                                    ),
            ) );

    }
    

    /**
     * Disable pagination and bulk actions
     */
    function pagination( $which ) { }
    function get_bulk_actions() { }
    function bulk_actions($which = '') {  }
    

    /**
     * Add buttons to create and delete new rows
     */
    function extra_tablenav( $which ) {

        if( $which == 'bottom' ) {
?>
    <span class="alignleft">
        <a href="#" class="betrs_add_ops"><?php _e( 'Add Shipping Cost', 'be-table-ship' ); ?></a> |
        <a href="#" class="betrs_delete_ops"><?php _e( 'Delete Selected Costs', 'be-table-ship' ); ?></a>
    </span>
    <span class="alignright">
        <a href="#" class="betrs_table_import"><?php _e( 'Import', 'be-table-ship' ); ?></a> /
        <a href="#" class="betrs_table_export" download="example.csv"><?php _e( 'Export', 'be-table-ship' ); ?></a>
    </span>
    <span style="clear:both;"></span>
<?php
        }
    }
    

    /**
     * Text that appears when no subcategories have been created
     */
    function no_items() { _e( 'No shipping options have been added yet', 'be-table-ship' ); }


    /**
     * Conditional Statements Data Output
     */
    function column_conditions( $item ){
        $return = "";

        if( isset( $item[ 'conditions' ] ) && is_array( $item[ 'conditions' ] ) && ! empty( $item[ 'conditions' ] ) )
            foreach( $item[ 'conditions' ] as $key => $value )
                $return .= $this->generate_conditions_section( $value, $item['option_ID'], $item['row_ID'], $key );

        // add option for more
        $return .= '<a href="#" class="add_table_condition_op">' . __( 'Add Condition', 'be-table-ship' ) . '</a>';

        //Return the conditions settings
        return $return;
    }


    /**
     * Product Category Column Data Output
     */
    function column_costs( $item ){

        $return = "";

        if( isset( $item[ 'costs' ] ) && is_array( $item[ 'costs' ] ) )
            foreach( $item[ 'costs' ] as $value )
                $return .= $this->generate_cost_section( $value, $item['option_ID'], $item['row_ID'] );
        else
            $return .= $this->generate_cost_section( $item, $item['option_ID'], $item['row_ID'] );

        // add option for more
        $return .= '<a href="#" class="add_table_cost_op">' . __( 'Add another cost', 'be-table-ship' ) . '</a>';

        // Return the product categories
        return $return;
    }


    /**
     * Product Category Column Data Output
     */
    function column_description( $item ){

        // Return textarea
        return '<textarea name="option_description[' . (int) $item['option_ID'] . '][]" placeholder="' . __( 'Optional text shown to customer', 'be-table-ship' ) . '">' . wp_kses_data( $item[ 'description' ] ) . '</textarea>';
    }


    /**
     * Move Row Handler Output
     */
    function column_sort( $item ){

        // Add ability to move row
        return '<div class="move_row"></div>';
    }


    /**
     * Checkbox Column
     */
    function column_cb( $item ){
        return sprintf(
            //'<input type="checkbox" name="%1$s['.$this->fieldsInc.']" value="%2$s" />',
            '<input type="checkbox" name="%1$s" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item['row_ID']
        );
    }


    /**
     * If no function has been created for the given column ID
     */
    function column_default( $item, $column_name ){ return "Data Could Not Be Found"; }


    /**
     * Array of column IDs
     */
    function get_columns(){

        $columns = array(
            'cb'            => '&nbsp;',
            'conditions'    => __( 'Conditions', 'be-table-ship' ),
            'costs'         => __( 'Costs', 'be-table-ship' ),
            'description'   => __( 'Description', 'be-table-ship' ),
            'sort'          => '',
        );
        return $columns;
    }

    /**
     * Setup which products are displayed on the current page
     */
    function prepare_items() {
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();

    /* -- Register the Columns -- */
        $columns = $this->get_columns();
        $_wp_column_headers[$screen->id]=$columns;

    /* -- Fetch the items -- */
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        
        $total_items = count( $this->items );
        
    /* -- Register the pagination -- */
        
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => 99999,
            'total_pages' => 1,
        ) );

    }

    /**
     * add feature ID to single_row options
     */
    function single_row( $item ) {

        $row_class = '';
        if( isset( $item['row_ID'] ) && (int) $item['row_ID'] % 2 !== 1 ){
            $row_class .= ' class="alternate"';
        }

        echo '<tr' . $row_class . '>';
        echo $this->single_row_columns( $item );
        echo '</tr>';
    }
 
    /**
     * Display the table when rates are available
     */
    public function display_table() {
        $singular = $this->_args['singular'];
 
        $this->display_tablenav( 'top' );
 
        $this->screen->render_screen_reader_content( 'heading_list' );
?>
<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
    <thead>
    <tr>
        <?php $this->print_column_headers(); ?>
    </tr>
    </thead>
 
    <tbody id="the-list"<?php
        if ( $singular ) {
            echo " data-wp-lists='list:$singular'";
        } ?>>
        <?php $this->display_rows_or_placeholder(); ?>
    </tbody>
 
    <tfoot>
    <tr>
        <?php $this->print_column_headers( false ); ?>
    </tr>
    </tfoot>
 
</table>
<?php
        $this->display_tablenav( 'bottom' );
    }


    /**
     * Create section of options for single cost setting
     */
    function generate_cost_section( $item, $option_ID, $row_ID ){

        $price = ( isset( $item['cost_value'] ) ) ? wc_format_localized_price( $item['cost_value'], '' ) : '';
        $option_ID = (int) $option_ID;
        $row_ID = (int) $row_ID;
        $cost_type = sanitize_text_field( $item[ 'cost_type' ] );

        $return = '<div class="cost_op"><span><input type="text" name="cost_value[' . $option_ID . '][' . $row_ID . '][]" value="' . $price . '" placeholder="' . wc_format_decimal( 0, '' ) . '" size="8" class="wc_input_price" />';

        // setup select box options
        $return .= '<select name="cost_type[' . $option_ID . '][' . $row_ID . '][]" class="cost_type">';
        foreach( $this->cost_ops as $key => $value )
            $return .= '<option value="' . sanitize_text_field( $key ) . '" ' . selected( $cost_type, $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
        $return .= '</select><span class="betrs_delete_ops_cost betrs-small-delete"></span>';

        $return .= $this->generate_cost_section_extras( $cost_type, $item, $option_ID, $row_ID );

        $return .= '</span></div>';

        return $return;

    }


    /**
     * Create section of additional options for certain cost selections
     */
    function generate_cost_section_extras( $selected_op, $item, $option_ID, $row_ID ){

        $return = '<div class="cost_op_extras">';

        $option_ID = (int) $option_ID;
        $row_ID = (int) $row_ID;
        $selected_op = sanitize_title( $selected_op );

        switch( $selected_op ) {
            case 'x':
                $return .= '<span><input type="hidden" name="cost_op_extra_val[' . $option_ID . '][' . $row_ID . '][]" value="" />';
                $return .= '<input type="hidden" name="cost_op_extra_secondary[' . $option_ID . '][' . $row_ID . '][]" value="" />';
                $return .= '<select name="cost_op_extra[' . $option_ID . '][' . $row_ID . '][]" class="cost_op_extra">';
                foreach( $this->cost_units_multi as $key => $value ) {
                    $return .= '<option value="' . sanitize_title( $key ) . '" ' . selected( sanitize_title( $item[ 'cost_op_extra' ] ), $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
                }
                $return .= '</select></span>';
                break;
            case 'every':
                $return .= '<span><input type="text" name="cost_op_extra_val[' . $option_ID . '][' . $row_ID . '][]" class="cost_op_extra_val wc_input_price" value="' . wc_format_localized_price( $item[ 'cost_op_extra_val' ] ) . '" placeholder="' . wc_format_decimal( 0, '' ) . '" size="8" />';
                $return .= '<select name="cost_op_extra[' . $option_ID . '][' . $row_ID . '][]" class="cost_op_extra">';
                foreach( $this->cost_units_every as $key => $value ) {
                    $return .= '<option value="' . sanitize_title( $key ) . '" ' . selected( sanitize_title( $item[ 'cost_op_extra' ] ), $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
                }
                $return .= '</select>';

                $to_display = ( sanitize_title( $item[ 'cost_op_extra' ] ) != 'dimensions' ) ? 'style="display: none;"' : '';

                $return .= '<select name="cost_op_extra_secondary[' . $option_ID . '][' . $row_ID . '][]" class="cost_op_extra_secondary" ' . $to_display . '>';
                foreach( $this->dimension_types as $key => $value ) {
                    $return .= '<option value="' . sanitize_title( $key ) . '" ' . selected( sanitize_title( $item[ 'cost_op_extra_secondary' ] ), $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
                }
                $return .= '</select></span>';
                break;
            case '%':
            case '':
                $return .= '<input type="hidden" name="cost_op_extra[' . $option_ID . '][' . $row_ID . '][]" value="" />';
                $return .= '<input type="hidden" name="cost_op_extra_val[' . $option_ID . '][' . $row_ID . '][]" value="" />';
                $return .= '<input type="hidden" name="cost_op_extra_secondary[' . $option_ID . '][' . $row_ID . '][]" value="" />';
                break;
            default:
                do_action( 'betrs_cost_section_extras_op', $selected_op, $item );
                break;
        }

        $return .= '</div>';

        return $return;

    }


    /**
     * Create section of options for conditional settings
     */
    function generate_conditions_section( $item, $option_ID, $row_ID, $cond_key = 0 ){
        
        $price = ( isset( $item['price'] ) ) ? wc_format_decimal( $item['price'], '' ) : '';
        $type = ( isset( $item[ 'cond_type' ] ) ) ? sanitize_title( $item[ 'cond_type' ] ) : 'subtotal';

        $return = '<div class="condition_op"><span>';

        // setup select box options
        $return .= '<select name="cond_type[' . $option_ID . '][' . $row_ID . '][]" class="cond_type">';
        foreach( $this->conditional_statements as $key => $value )
            $return .= '<option value="' . sanitize_title( $key ) . '" ' . selected( $type, $key, false ) . '>' . sanitize_text_field( $value ) . '</option>';
        $return .= '</select> <span class="betrs_delete_ops_cond betrs-small-delete"></span>';

        $return .= $this->generate_conditions_section_extras( $type, $item, $option_ID, $row_ID, $cond_key );

        $return .= '</span></div>';

        return $return;

    }


    /**
     * Create section of additional options for conditional settings
     */
    function generate_conditions_section_extras( $type, $item, $option_ID, $row_ID, $cond_key = 0 ){
        global $wp_locale;
        
        $return = '<span class="cond_op_extras">';

        $cond_secondary = ( isset( $item['cond_secondary'] ) ) ? sanitize_text_field( $item['cond_secondary'] ) : '';

        if( isset( $item['cond_tertiary'] ) ) {
            if( is_array( $item['cond_tertiary'] ) ) {
                $cond_tertiary = array_map( 'sanitize_text_field', $item['cond_tertiary'] );
            } else {
                $cond_tertiary = sanitize_text_field( $item['cond_tertiary'] );
                if( is_numeric( $cond_tertiary ) )
                    $cond_tertiary = wc_format_localized_price( $cond_tertiary );
            }

        } else $cond_tertiary = '';

        ob_start();

        // Determine if secondary select box is needed
        $secondary_ops = array();
        foreach( $this->secondary_statements as $key => $value ) {
            if( isset( $value[ 'conditions' ] ) && is_array( $value[ 'conditions' ] ) ) {
                if( in_array( $type, $value[ 'conditions' ] ) )
                    $secondary_ops[] = $key;
            }
        }
        if( count( $secondary_ops ) > 0 ) {
?>

<select name="cond_secondary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" class="cond_secondary">
    <?php foreach( $secondary_ops as $op ) : ?>
        <?php if( isset( $this->secondary_statements[ $op ][ 'title' ] ) ) : ?>
    <option value="<?php echo $op; ?>" <?php selected( $op, $cond_secondary, true ); ?>><?php echo sanitize_text_field( $this->secondary_statements[ $op ][ 'title' ] ); ?></option>
        <?php endif; ?>
    <?php endforeach; ?>
</select>

<?php
        }

        // determine additional form fields
        switch( sanitize_title( $type ) ) {
            case 'dates':
?>
<input type="hidden" name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" size="8" />
<input id="cond_secondary_datepicker" name="cond_secondary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" value='<?php echo stripslashes( sanitize_text_field( $cond_secondary ) ); ?>'>
<script type="text/javascript">
jQuery(document).ready(function($) {
    jQuery("#cond_secondary_datepicker").daterangepicker({
         datepickerOptions : {
             numberOfMonths : 2,
             maxDate: null
         },
         presetRanges: [],
         initialText: "<?php _e( 'Select date range...', 'be-table-ship' ); ?>",
         dateFormat: "d M yy"
     });
});
</script>
<?php
                break;
            case 'dayweek':
?>

<select name="cond_secondary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" class="cond_secondary">
    <?php for ($day_index = 0; $day_index <= 6; $day_index++) : ?>
    <option value='<?php echo esc_attr($day_index);  ?>' <?php selected( $cond_secondary, $day_index, true ); ?>><?php echo $wp_locale->get_weekday( $day_index ); ?></option>
    <?php endfor; ?>
</select>
<input type="hidden" name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" size="8" />

<?php
                break;
            case 'product':
?>
<select name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][<?php echo $cond_key; ?>][]" class="cond_tertiary wc-enhanced-select" multiple="multiple">
                <?php
                    $sel_products = ( is_array( $cond_tertiary ) ) ? $cond_tertiary : array();
                    $products = get_posts( array( 'post_type' => 'product', 'numberposts' => -1, 'post_status' => 'publish', 'no_found_rows' => true, 'orderby' => 'title', 'order' => 'ASC' ) );
                    foreach ( $products as $prod ) {

                        $product = wc_get_product( $prod->ID );
                        if ( is_object( $prod ) ) {
                            echo '<option value="' . esc_attr( $prod->ID ) . '"' . selected( in_array( $prod->ID, $sel_products ), true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                        }
                    }
                ?>
            </select>
<?php
                break;
            case 'category':
                $terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
?>
<select name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" class="cond_tertiary">
                <?php 
                    foreach( $terms as $term ) : 
                        if( isset( $term->term_id ) ) :
                ?>
    <option value="<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $cond_tertiary, true ); ?>><?php echo $term->name; ?></option>
                <?php
                        endif;
                    endforeach;
                ?>
</select>
<?php
                break;
            case 's_class':
                $terms = WC()->shipping->get_shipping_classes();
?>
<select name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" class="cond_tertiary">
                <?php 
                    foreach( $terms as $term ) : 
                        if( isset( $term->term_id ) ) :
                ?>
    <option value="<?php echo $term->term_id; ?>" <?php selected( $term->term_id, $cond_tertiary, true ); ?>><?php echo $term->name; ?></option>
                <?php
                        endif;
                    endforeach;
                ?>
</select>
<?php
                break;
            default:
                if( count( $secondary_ops ) > 0 ) {
                    // add third form field if applicable
                    if( isset( $this->secondary_statements[ $op ]['tertiary'] ) ) {
                        switch( $this->secondary_statements[ $op ]['tertiary'] ) {
                            case 'text':
?>

<input type="text" name="cond_tertiary[<?php echo $option_ID; ?>][<?php echo $row_ID; ?>][]" value="<?php echo $cond_tertiary; ?>" size="8" />

<?php
                                break;
                            
                            default:
                                do_action( 'betrs_shipping_cost_conditionals_tertiary' );
                                break;
                        }
                    } else {
                        echo '<input type="text" name="cond_tertiary[' . $option_ID . '][' . $row_ID . '][]" size="8" value="' . $cond_tertiary . '" />';
                    }
            } // end if has secondary ops

            // allow other fields to be added externally
            do_action( 'betrs_shipping_cost_conditionals_after', $type, $item );
            break;

        } // end switch
        $return .= ob_get_contents();
        ob_end_clean();

        return $return .= '</span>';

    }

}

endif;