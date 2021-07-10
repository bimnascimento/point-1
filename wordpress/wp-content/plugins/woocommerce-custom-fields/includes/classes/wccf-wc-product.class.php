<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Product Page
 *
 * @class WCCF_WC_Product
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Product')) {

class WCCF_WC_Product
{
    private static $prices_subject_to_adjustment = null;

    // Singleton instance
    protected static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Continue when plugins are loaded
        add_action('plugins_loaded', array($this, 'plugins_loaded'), 1);
    }

    /**
     * On plugins loaded
     *
     * @access public
     * @return void
     */
    public function plugins_loaded()
    {
        // Check environment
        if (!WCCF::check_environment()) {
            return;
        }

        // Change product prices based on default product field values and product properties configured by admin
        add_filter('woocommerce_get_price', array($this, 'maybe_change_product_price'), 10, 2);
        add_filter('woocommerce_get_sale_price', array($this, 'maybe_change_product_price'), 10, 2);
        add_filter('woocommerce_get_regular_price', array($this, 'maybe_change_product_price'), 10, 2);
        add_filter('woocommerce_variation_prices_price', array($this, 'maybe_change_variation_price'), 10, 3);
        add_filter('woocommerce_variation_prices_regular_price', array($this, 'maybe_change_variation_price'), 10, 3);
        add_filter('woocommerce_variation_prices_sale_price', array($this, 'maybe_change_variation_price'), 10, 3);

        // PRODUCT FIELD RELATED

        // Change Add To Cart link in category pages if product contains at least one custom field
        add_filter('woocommerce_loop_add_to_cart_link', array($this, 'maybe_change_add_to_cart_link'), 10, 2);

        // Display product fields
        add_action('woocommerce_before_add_to_cart_button', array($this, 'display_product_fields'));

        // Maybe change product quantity
        add_filter('woocommerce_quantity_input_args', array($this, 'maybe_change_product_quantity'), 99, 2);

        // Ajax price update
        add_action('wp_ajax_wccf_product_field_price_update', array($this, 'ajax_product_price_update'));
        add_action('wp_ajax_nopriv_wccf_product_field_price_update', array($this, 'ajax_product_price_update'));

        // Ajax product field view refresh
        add_action('wp_ajax_wccf_refresh_product_field_view', array($this, 'ajax_refresh_product_field_view'));
        add_action('wp_ajax_nopriv_wccf_refresh_product_field_view', array($this, 'ajax_refresh_product_field_view'));

        // PRODUCT PROPERTY RELATED

        // Display product admin fields
        add_action('add_meta_boxes', array($this, 'add_meta_box_product_prop'), 99, 2);

        // Add enctype attribute to the product edit page form to allow file uploads
        add_action('post_edit_form_tag', array($this, 'maybe_add_enctype_attribute'));

        // Save product admin field data
        add_action('save_post', array($this, 'save_product_property_values'), 10, 2);

        // Display product properties in frontend custom tab
        add_filter('woocommerce_product_tabs', array($this, 'add_product_properties_tab'));
    }

    /**
     * Change Add To Cart link in category pages if product contains at least one custom field
     *
     * @access public
     * @param string $link
     * @param object $product
     * @return string
     */
    public function maybe_change_add_to_cart_link($link, $product)
    {
        $change_add_to_cart_link = false;

        // Maybe skip product fields for this product based on various conditions
        if (WCCF_WC_Product::skip_product_fields($product)) {
            return $link;
        }

        // Get applicable fields
        $fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => $product->id));

        // Only change link if at least one product field is set
        if (!empty($fields)) {

            // Change link only when at least one required field is set
            if (WCCF_Settings::get('change_add_to_cart_text') === '1') {
                foreach ($fields as $field) {
                    if ($field->is_required()) {
                        $change_add_to_cart_link = true;
                        break;
                    }
                }
            }
            // Change link when any field is set
            else {
                $change_add_to_cart_link = true;
            }
        }

        // Disable Add To Cart from category pages and link to product page to fill in custom fields
        if ($change_add_to_cart_link) {
            return sprintf('<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button %s product_type_%s">%s</a>',
                esc_url(get_permalink($product->id)),
                esc_attr($product->id),
                esc_attr($product->get_sku()),
                esc_attr(isset($quantity) ? $quantity : 1),
                '',
                esc_attr($product->product_type),
                esc_html(apply_filters('wccf_category_add_to_cart_text', __('View Product', 'rp_wccf'), $product->id))
            );
        }

        return $link;
    }

    /**
     * Change product prices based on default product field values and product properties configured by admin
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return float
     */
    public function maybe_change_product_price($price, $product)
    {
        return $this->maybe_change_price($price, $product);
    }

    /**
     * Change variation prices based on default product field values and product properties configured by admin
     *
     * Only runs on WC >= 2.4.7, in older versions variation prices are processed through maybe_change_product_price()
     * Only runs on specific occasions, like when printing variable product price in product list view, otherwise price is retrieved via get_price()
     * Need to monitor further changes to WC_Product_Variable::get_variation_prices() just in case they start retrieving prices through get_price()
     *
     * @access public
     * @param float $price
     * @param object $variation
     * @param object $product
     * @return float
     */
    public function maybe_change_variation_price($price, $variation, $product)
    {
        return $this->maybe_change_price($price, $product, $variation);
    }

    /**
     * Change product prices based on default product field values and product properties configured by admin
     *
     * @access public
     * @param float $price
     * @param object $product
     * @param object $variation
     * @return float
     */
    public function maybe_change_price($price, $product, $variation = null)
    {
        // Check if price can be adjusted for this product
        if (!WCCF_WC_Product::proceed_adjusting_prices($product, $variation)) {
            return $price;
        }

        // Get variation id and appropriate object id for caching
        $variation_id = $variation !== null ? $variation->id : null;
        $object_id = $variation_id !== null ? $variation_id : $product->id;

        // Get cached price validation hash
        $hash = WCCF_WC_Product::get_cached_price_validation_hash($price, $product);

        // Attempt to get adjusted price from cache
        $cached_price = WCCF_WC_Product::get_valid_cached_price($object_id, $hash);

        // Check if cached price exists and is not outdated
        if ($cached_price !== false) {

            // Return price from cache
            return $cached_price;
        }

        // Get adjusted price
        // Casting it to string because WooCommerce expects it to be a string
        $adjusted_price = (string) WCCF_Pricing::get_adjusted_price($price, $object_id, $variation_id);

        // Cache adjusted price
        WCCF_WC_Product::cache_price($object_id, $adjusted_price, $hash);

        // Return adjusted price
        return $adjusted_price;
    }

    /**
     * Check if prices can be changed by looking at various factors
     *
     * @access public
     * @param object $product
     * @param object $variation
     * @return bool
     */
    public static function proceed_adjusting_prices($product, $variation = null)
    {
        // Don't change prices for in admin ui
        if (is_admin() && !defined('DOING_AJAX')) {
            return false;
        }

        // Skip pricing adjustment for product based on various conditions
        if (WCCF_WC_Product::skip_pricing($product, $variation)) {
            return false;
        }

        // Check if this call is for a cart item product in which case pricing has already been sorted via filter hook woocommerce_get_cart_item_from_session
        if (isset($product->wccf_cart_item_product) && $product->wccf_cart_item_product === true) {
            return false;
        }

        // Check if at least one active product field or product property adjusts pricing
        if (!WCCF_WC_Product::prices_subject_to_adjustment()) {
            return false;
        }

        return true;
    }

    /**
     * Reset cached price for product
     *
     * @access public
     * @param int $product_id
     * @return void
     */
    public static function clear_cached_price($product_id)
    {
        // Clear cached product price
        delete_post_meta($product_id, 'wccf_cached_price');

        // Load product
        $product = wc_get_product($product_id);

        // Check if product is variable
        if ($product->product_type !== 'variable') {
            return;
        }

        // Iterate over product variations
        foreach ($product->get_children() as $variation_id) {

            // Clear cached variation price
            delete_post_meta($variation_id, 'wccf_cached_price');
        }
    }

    /**
     * Display custom product fields
     *
     * @access public
     * @return void
     */
    public function display_product_fields()
    {
        $product_id     = RightPress_Helper::get_wc_product_id();
        $product        = wc_get_product($product_id);
        $variation_id   = WCCF_WC_Product::get_variation_id(null, true);

        $conditional_product_fields = false;

        // Maybe skip product fields for this product based on various conditions
        if (is_object($product) && WCCF_WC_Product::skip_product_fields($product)) {
            return;
        }

        // Define filter params
        $filter_params = array(
            'item_id' => $product_id,
            'child_id' => $variation_id,
        );

        // Get all product fields
        $all_fields = WCCF_Product_Field_Controller::get_all();

        // Check if product is variable
        if ($product->product_type === 'variable') {

            // Check if at least one field has attribute-related conditions
            foreach ($all_fields as $field) {
                if ($field->has_product_attribute_conditions()) {

                    $conditional_product_fields = true;

                    // Ensure that frontend assets are loaded
                    WCCF_Assets::enqueue_frontend_scripts();

                    break;
                }
            }

            // Attempt to determine default variation if not set
            if ($variation_id === null) {
                $variation_id = WCCF_WC_Product::get_default_variation_id($product);
            }

            // Attempt to determine preselected attributes
            if ($preselected_attributes = $product->get_variation_default_attributes()) {
                $filter_params['variation_attributes'] = $preselected_attributes;
            }
        }

        // Open container
        $uses_attribute_conditions = $conditional_product_fields ? 'data-wccf-uses-attribute-conditions="1" ' : '';
        echo '<div id="wccf_product_field_master_container" ' . $uses_attribute_conditions . '>';

        // Filter out fields for display
        $fields = WCCF_Conditions::filter_fields($all_fields, $filter_params);

        // Get quantity if printing after failed add-to-cart validation
        $quantity = (!empty($_REQUEST['add-to-cart']) && !empty($_REQUEST['quantity'])) ? (int) $_REQUEST['quantity'] : null;

        // Alternatively try to get quantity from query vars
        if ($quantity === null && $_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['wccf_quantity'])) {
            $quantity = (int) $_GET['wccf_quantity'];
        }

        // Display list of fields
        WCCF_Field_Controller::print_fields($fields, null, $quantity);

        // Close container if needed
        echo '</div>';

        // Display grand total
        if (!empty($fields) || $conditional_product_fields) {
            WCCF_WC_Product::display_grand_total($product, $variation_id);
        }
    }

    /**
     * Display grand total
     *
     * @access public
     * @param object $product
     * @param int $variation_id
     * @return void
     */
    public static function display_grand_total($product, $variation_id)
    {
        global $post;

        // Check if post type is product
        if (is_object($post) && $post->post_type === 'product') {

            // Skip pricing adjustment based on various conditions
            if (WCCF_WC_Product::skip_pricing($post->ID)) {
                return;
            }
        }

        // Check if we need to display grand total
        if (WCCF_Settings::get('display_total_price')) {

            // Get data to display
            $label = __('Total', 'rp_wccf');
            $price = WCCF_WC_Product::get_product_price_html();

            // If default variation is not known for variable product, hide element initially
            $display = ($product->product_type === 'variable' && $variation_id === null) ? 'none' : 'block';

            // Display data
            echo '<dl class="wccf_grand_total" style="display: ' . $display . ';"><dt>' . $label . '</dt><dd>' . $price . ' </dd></dl>';

            // Hide WooCommerce single variation price
            echo '<style style="display: none;">div.single_variation_wrap div.single_variation span.price { display: none; }</style>';
        }
    }

    /**
     * Get product price to display
     *
     * @access public
     * @param float $adjustment
     * @return string
     */
    public static function get_product_price_html($adjustment = 0)
    {
        // Get adjusted product price
        $price = WCCF_WC_Product::get_product_price($adjustment);

        // Format and return price html
        return wc_price($price);
    }

    /**
     * Get product price
     *
     * @access public
     * @param float $adjustment
     * @return float
     */
    public static function get_product_price($adjustment = 0, $quantity = 1)
    {
        // Load product object
        $product_id = RightPress_Helper::get_wc_product_id();
        $product = wc_get_product($product_id);

        // Load default variation if possible for variable products
        if ($product->product_type === 'variable') {

            // Get default variation id
            $variation_id = WCCF_WC_Product::get_default_variation_id($product);

            // Replace product object with product variation object if default variation id was determined
            if ($variation_id) {
                $product = wc_get_product($variation_id);
            }
        }

        // Get default product price and possibly adjust it
        $price = $product->get_price() + $adjustment;

        // Maybe add tax
        if (get_option('woocommerce_tax_display_shop') === 'incl') {
            $price = $product->get_price_including_tax($quantity, $price);
        }
        else {
            $price = $product->get_price_excluding_tax($quantity, $price);
        }

        // Price can't be negative
        if ($price < 0) {
            $price = 0;
        }

        // Return product price
        return (float) $price;
    }

    /**
     * Dynamically update product price via Ajax on product field selection
     *
     * @access public
     * @return void
     */
    public function ajax_product_price_update()
    {
        try {

            // Get Ajax data
            $ajax_data = WCCF_WC_Product::get_ajax_request_data();
            extract($ajax_data);

            // Remove filter so we don't have our own pricing adjustment done twice
            remove_filter('woocommerce_get_price', array($this, 'maybe_change_product_price'), 10);

            // Get quantity
            $quantity = !empty($quantity) ? (int) $quantity : 1;

            // Get original display price
            $object_id = $variation_id !== null ? $variation_id : $product_id;
            $product =  wc_get_product($object_id);
            $price = $product->get_display_price('', 1);

            // Check if this product supports fields and pricing at all
            if (WCCF_WC_Product::skip_product_fields($product) || WCCF_WC_Product::skip_pricing($product)) {
                throw new Exception(__('Product does not support product fields or pricing adjustments.', 'rp_wccf'));
            }

            // Add filter again
            add_filter('woocommerce_get_price', array($this, 'maybe_change_product_price'), 10, 2);

            // Reconstruct configuration array
            $wccf = array();

            // Iterate over fields
            if (!empty($data['wccf']) && !empty($data['wccf']['product_field']) && is_array($data['wccf']['product_field'])) {
                foreach ($data['wccf']['product_field'] as $field_id => $field_value) {
                    $wccf[$field_id] = array(
                        'value' => $field_value,
                    );
                }
            }

            // Get adjusted price
            $adjusted_price = WCCF_Pricing::get_adjusted_price($price, $product_id, $variation_id, $wccf, $quantity, true, false);

            // Allow more spaces to fix totals for quantity based price adjusting fields
            $adjusted_price = WCCF_Pricing::fix_quantity_based_fields_product_price($adjusted_price, $quantity);

            // Get total
            $adjusted_total = $adjusted_price * $quantity;

            echo json_encode(array(
                'result'        => 'success',
                'price'         => $adjusted_total,
                'price_html'    => wc_price($adjusted_total)
            ));

        } catch (Exception $e) {
            echo json_encode(array(
                'result' => 'error',
            ));
        }

        exit;
    }

    /**
     * Ajax product field view refresh
     *
     * @access public
     * @return void
     */
    public function ajax_refresh_product_field_view()
    {
        try {

            $response_data = array();

            // Get Ajax data
            $ajax_data = WCCF_WC_Product::get_ajax_request_data();
            extract($ajax_data);

            // Get all product fields for display
            $fields = WCCF_Product_Field_Controller::get_filtered(null, array(
                'item_id'               => $product_id,
                'child_id'              => $variation_id,
                'variation_attributes'  => $attributes,
            ));

            // Iterate over fields
            foreach ($fields as $field) {
                for ($i = 0; $i < $quantity; $i++) {

                    // Start buffer
                    ob_start();

                    // Select correct quantity index
                    $quantity_index = $field->is_quantity_based() ? $i  : null;

                    // Print single field
                    WCCF_Field_Controller::print_fields(array($field), null, $quantity, $quantity_index);

                    // Get buffer contents and clean it
                    $response_data[] = array(
                        'field_id'      => $field->get_id(),
                        'element_id'    => 'wccf_' . $field->get_context() . '_' . $field->get_key() . ($quantity_index ? ('_' . $quantity_index) : ''),
                        'element_name'  => 'wccf[' . $field->get_context() . '][' . ($quantity_index ? ($field->get_id() . '_' . $quantity_index) : $field->get_id()) . ']' . ($field->accepts_multiple_values() ? '[]' : ''),
                        'field_type'    => $field->get_field_type(),
                        'html'          => ob_get_clean(),
                    );

                    // Field is not quantity based - print only one field
                    if (!$field->is_quantity_based()) {
                        break;
                    }
                }
            }

            // Send response
            echo json_encode(array(
                'result'    => 'success',
                'fields'    => $response_data,
            ));
        }
        catch (Exception $e) {
            echo json_encode(array(
                'result' => 'error',
            ));
        }

        exit;
    }

    /**
     * Check Ajax request and get data
     *
     * @access public
     * @return array
     */
    public static function get_ajax_request_data()
    {
        // Check if data was posted
        if (empty($_POST['data'])) {
            throw new Exception(__('No data received.', 'rp_wccf'));
        }

        // Parse product data and configuration
        $data = urldecode($_POST['data']);
        parse_str($data, $data);

        // Get product id
        if (isset($data['product_id']) && is_numeric($data['product_id'])) {
            $product_id = (int) $data['product_id'];
        }
        else if (isset($data['add-to-cart']) && is_numeric($data['add-to-cart'])) {
            $product_id = (int) $data['add-to-cart'];
        }
        else {
            throw new Exception(__('Product is not defined.', 'rp_wccf'));
        }

        // Get optional variation id
        if (isset($data['variation_id']) && is_numeric($data['variation_id'])) {
            $attributes = null;
            $variation_id = (int) $data['variation_id'];
        }
        else {
            $attributes = WCCF_WC_Product::get_attributes_array_from_data($data);
            $variation_id = WCCF_WC_Product::get_variation_id_from_attributes($product_id, $attributes);
        }

        // Get quantity
        $quantity = !empty($data['quantity']) ? (int) $data['quantity'] : 1;

        // Return array
        return compact('data', 'product_id', 'variation_id', 'attributes', 'quantity');
    }

    /**
     * Get attributes array from data array
     *
     * @access public
     * @param array $data
     * @return array
     */
    public static function get_attributes_array_from_data($data)
    {
        $attributes = array();

        foreach ($data as $key => $value) {
            if (RightPress_Helper::string_contains_phrase($key, 'attribute_pa_')) {
                $attributes[str_replace('attribute_', '', $key)] = $value;
            }
        }

        return $attributes;
    }

    /**
     * Get WooCommerce product types
     *
     * @access public
     * @return array
     */
    public static function get_product_types()
    {
        return wc_get_product_types();
    }

    /**
     * Add meta box for product properties
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_product_prop($post_type, $post)
    {
        // Not product?
        if ($post_type !== 'product') {
            return;
        }

        // Get product
        $product = wc_get_product($post->ID);

        // Make sure product type is not grouped
        if ($product->get_type() === 'grouped') {
            return;
        }

        // Get fields to display
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array(), null, true);

        // Add meta box if we have at least one field to display
        if (!empty($fields)) {
            add_meta_box(
                'wccf_product_properties',
                apply_filters('wccf_context_label', WCCF_Settings::get('alias_product_prop'), 'product_prop', 'backend'),
                array($this, 'print_meta_box_product_properties'),
                'product',
                'normal',
                'high'
            );
        }
    }

    /**
     * Print product properties meta box on product edit page
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_product_properties($post)
    {
        // Get fields to display
        $fields = WCCF_Product_Property_Controller::get_filtered();

        // Print fields
        WCCF_Field_Controller::print_fields($fields, $post->ID);
    }

    /**
     * Add enctype attribute to the product edit page form to allow file uploads
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function maybe_add_enctype_attribute($post)
    {
        // Skip other post types
        if ($post->post_type !== 'product') {
            return;
        }

        // Add enctype attribute
        echo ' enctype="multipart/form-data" ';
    }

    /**
     * Process product property data on save post action
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_product_property_values($post_id, $post)
    {
        // Only process posts with type product
        if ($post->post_type !== 'product') {
            return;
        }

        // Store posted field values
        WCCF_Field_Controller::store_field_values($post_id, 'product_prop');
    }

    /**
     * Maybe add product properties tab in product page
     *
     * @access public
     * @param array $tabs
     * @return array
     */
    public function add_product_properties_tab($tabs)
    {
        global $post;

        // Allow developers to hide default properties tab
        if (!apply_filters('wccf_display_product_properties', true, $post->ID)) {
            return $tabs;
        }

        // Get product properties
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array('item_id' => $post->ID));

        // Iterate over fields
        foreach ($fields as $field) {

            // Check if field is public
            if (!$field->is_public()) {
                continue;
            }

            // Get field value
            if (WCCF_Settings::get('display_default_product_prop_values')) {
                $field_value = $field->get_final_value($post->ID);
            }
            else {
                $field_value = $field->get_stored_value($post->ID);
            }

            // Check if field has value
            if ($field_value === false) {
                continue;
            }

            // Add tab
            $tabs = array_merge($tabs, array('wccf_product_properties' => array(
                'callback'  => array($this, 'print_product_properties_tab_content'),
                'title'     => apply_filters('wccf_context_label', WCCF_Settings::get('alias_product_prop'), 'product_prop', 'frontend'),
                'priority'  => apply_filters('wccf_product_properties_display_position', 21)
            )));

            // Break from cycle
            break;
        }

        // Return tabs
        return $tabs;
    }

    /**
     * Print product properties tab content
     *
     * @access public
     * @return void
     */
    public function print_product_properties_tab_content()
    {
        self::print_product_property_values_in_frontend();
    }

    /**
     * Display product properties anywhere via PHP function
     *
     * @access public
     * @param int $product_id
     * @return void
     */
    public static function print_product_properties_function($product_id = null)
    {
        // Get content and return
        return self::print_product_property_values_in_frontend($product_id, true);
    }

    /**
     * Print product property value list
     *
     * @access public
     * @param int $product_id
     * @param bool $return_html
     * @param bool $skip_filter
     * @return void
     */
    public static function print_product_property_values_in_frontend($product_id = null, $return_html = false, $skip_filter = false)
    {
        // Get product id if it was not passed in
        $product_id = RightPress_Helper::get_wc_product_id($product_id);

        // Check if product ID is set
        if (!$product_id) {
            return '';
        }

        // Get product properties for this product
        $fields = WCCF_Product_Property_Controller::get_filtered(null, array('item_id' => $product_id));

        // Get values to display
        $display = WCCF_Field_Controller::get_field_values_for_frontend($fields, $product_id, 'product_prop');

        // Allow developers to skip displaying frontend product property values in default position
        if (!$skip_filter && !apply_filters('wccf_frontend_display_product_property_values', true, $display, $product_id, $return_html)) {
            return;
        }

        // Include template if we have at least one public field with value
        if (!empty($display) && is_array($display)) {

            // Return instead of output?
            if ($return_html) {
                ob_start();
            }

            // Include template
            WCCF::include_template('product/product-properties-data', array(
                'fields' => $display,
            ));

            // Return instead of output?
            if ($return_html) {
                $content = ob_get_contents();
                ob_end_clean();
                return $content;
            }
        }
        else if ($return_html) {
            return '';
        }
    }

    /**
     * Store cached price in product meta
     *
     * @access public
     * @param int $object_id
     * @param float $price
     * @param string $hash
     * @return void
     */
    public static function cache_price($object_id, $price, $hash)
    {
        // Update cached price in product meta
        update_post_meta($object_id, 'wccf_cached_price', array('p' => $price, 'h' => $hash));
    }

    /**
     * Get valid cached price
     *
     * @access public
     * @param int $object_id
     * @param string $hash
     * @return mixed
     */
    public static function get_valid_cached_price($object_id, $hash)
    {
        // Get product price from cache
        $cached_price = get_post_meta($object_id, 'wccf_cached_price', true);

        // Check if cached price exists and is not outdated
        if (is_array($cached_price) && isset($cached_price['h']) && $cached_price['h'] === $hash) {

            // Return cached price
            return $cached_price['p'];
        }

        return false;
    }

    /**
     * Get cached price validation hash
     * Used to identify outdated cached prices
     *
     * @access public
     * @param float $price
     * @param object $product
     * @return string
     */
    public static function get_cached_price_validation_hash($price, $product)
    {
        $instance = self::get_instance();

        // Compose array with environment properties
        $environment = array(
            $price,
            $product->price,
            WCCF_WC_Product::skip_product_fields($product),
            WCCF_Settings::get_objects_revision(),
            WCCF_Settings::get('_all'),
        );

        // Generate and return hash
        return RightPress_Helper::get_hash(false, $environment);
    }

    /**
     * Check if at least one active product field or product property adjusts pricing
     *
     * @access public
     * @return bool
     */
    public static function prices_subject_to_adjustment()
    {
        // Check if we have this flag in memory
        if (self::$prices_subject_to_adjustment === null) {

            // Run query to find product fields or product properties with pricing
            $query = new WP_Query(array(
                'post_type'         => array('wccf_product_field', 'wccf_product_prop'),
                'post_status'       => 'publish',
                'fields'            => 'ids',
                'posts_per_page'    => 1,
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'       => 'pricing_value',
                        'value'     => NULL,
                        'compare'   => '!=',
                    ),
                    array(
                        'key'       => 'options',
                        'value'     => '"pricing_value";d:',
                        'compare'   => 'LIKE',
                    ),
                ),
                'tax_query' => array(
                    'relation'=> 'OR',
                    array(
                        'taxonomy'  => 'wccf_product_field_status',
                        'field'     => 'slug',
                        'terms'     => 'enabled',
                    ),
                    array(
                        'taxonomy'  => 'wccf_product_prop_status',
                        'field'     => 'slug',
                        'terms'     => 'enabled',
                    ),
                ),
            ));

            // Check if at least one field has pricing and store flag in memory
            self::$prices_subject_to_adjustment = ((int) $query->found_posts) > 0;
        }

        // Return flag from memory
        return self::$prices_subject_to_adjustment;
    }

    /**
     * Maybe skip all product field display for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @return bool
     */
    public static function skip_product_fields($product, $variation = null)
    {
        return WCCF_WC_Product::skip($product, $variation, 'product_fields');
    }

    /**
     * Maybe skip all pricing adjustments for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @return bool
     */
    public static function skip_pricing($product, $variation = null)
    {
        return WCCF_WC_Product::skip($product, $variation, 'pricing');
    }

    /**
     * Maybe skip all pricing adjustments for specific product
     *
     * @access public
     * @param mixed $product
     * @param mixed $variation
     * @param string $subject
     * @return bool
     */
    public static function skip($product, $variation, $subject)
    {
        // Get product
        if (!is_object($product)) {
            $product = wc_get_product($product);
        }

        // Get variation
        if ($variation !== null && !is_object($variation)) {
            $variation = wc_get_product($variation);
        }

        // Check if product was loaded
        if (!is_object($product)) {
            return true;
        }

        // Get product types to skip
        if ($subject === 'product_fields') {
            $product_types = array('grouped', 'external');
        }
        else {
            $product_types = array('grouped');
        }

        // Skip specific product types
        if (in_array($product->get_type(), apply_filters('wccf_skip_' . $subject . '_for_product_types', $product_types))) {
            return true;
        }

        // Allow developers to skip specific products or variations
        if (apply_filters('wccf_skip_' . $subject . '_for_product', false, $product, $variation)) {
            return true;
        }

        return false;
    }

    /**
     * Get product attribute term ids for product
     *
     * @access public
     * @param mixed $product
     * @return array
     */
    public static function get_attribute_term_ids($product)
    {
        $ids = array();

        // Product id was passed in
        if (!is_object($product)) {

            // Load product object
            $product = wc_get_product($product);

            if (!$product) {
                return $ids;
            }
        }

        // Iterate over attributes
        foreach ((array) $product->get_attributes() as $attribute_key => $attribute) {

            // Skip attributes used for variations
            if (!empty($attribute['is_variation'])) {
                continue;
            }

            // Get product terms
            $product_terms = (array) wc_get_product_terms($product->id, $attribute_key, array('fields' => 'slugs'));

            // Iterate over product terms
            foreach ($product_terms as $term_id => $term_slug) {

                // Add attribute id to list
                if (!in_array($term_id, $ids, true)) {
                    $ids[] = (int) $term_id;
                }
            }
        }

        return $ids;
    }

    /**
     * Attempt to get variation id
     *
     * If $check_attributes_in_request is true, this will attempt to determine variation id by attributes found in posted data or query string, e.g. ?attribute_pa_color=blue&attribute_pa_size=big
     *
     * @access public
     * @param mixed $variation_id
     * @param bool $check_attributes_in_request
     * @return void
     */
    public static function get_variation_id($variation_id = null, $check_attributes_in_request = false)
    {
        // Already set
        if ($variation_id !== null) {
            return (int) $variation_id;
        }

        // Add To Cart
        if (!empty($_REQUEST['add-to-cart']) && is_numeric($_REQUEST['add-to-cart']) && RightPress_Helper::post_type_is($_REQUEST['add-to-cart'], 'product')) {
            if (!empty($_REQUEST['variation_id']) && is_numeric($_REQUEST['variation_id']) && RightPress_Helper::post_type_is($_REQUEST['variation_id'], 'product_variation')) {
                return (int) $_REQUEST['variation_id'];
            }
        }

        // Add To Cart (the other way)
        if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_add_to_cart' && isset($_POST['product_id']) && is_numeric($_POST['product_id']) && RightPress_Helper::post_type_is($_POST['product_id'], 'product')) {
            if (!empty($_POST['variation_id']) && is_numeric($_POST['variation_id']) && RightPress_Helper::post_type_is($_POST['variation_id'], 'product_variation')) {
                return $_POST['product_id'];
            }
        }

        // Maybe attempt to determine variation id by looking into product attributes in request
        if ($check_attributes_in_request) {

            // Get attributes from request data
            $attributes = WCCF_WC_Product::get_attributes_array_from_data($_REQUEST);

            // Figure out product id
            $product_id = RightPress_Helper::get_wc_product_id();

            // Check if we were able to get product id
            if ($product_id && !empty($attributes)) {

                // Attempt to figure out variation id from attributes found in request
                return WCCF_WC_Product::get_variation_id_from_attributes($product_id, $attributes);
            }
        }

        // Failed figuring out variation id
        return null;
    }

    /**
     * Attempt to get variation id from a set of variable product attributes
     *
     * @access public
     * @param mixed $product
     * @param array $attributes
     * @return int
     */
    public static function get_variation_id_from_attributes($product, $attributes)
    {
        // Product id was passed in
        if (!is_object($product)) {

            // Load product object
            $product = wc_get_product($product);

            if (!$product) {
                return null;
            }
        }

        // Product is not variable
        if ($product->product_type !== 'variable') {
            return null;
        }

        // Get all available variations
        $all_variations = $product->get_available_variations();

        // Iterate over available variations
        foreach ($all_variations as $variation) {

            $attributes_match = true;

            // Iterate over attributes and check if each attribute is set for current variation
            foreach ($attributes as $attribute_key => $attribute_slug) {

                // Current attribute not set for this variation
                if (!isset($variation['attributes']['attribute_' . $attribute_key])) {
                    $attributes_match = false;
                    break;
                }

                // Get attribute value set for current variation
                $attribute_value = $variation['attributes']['attribute_' . $attribute_key];

                // Attribute value must either match requested attribute slug or be empty string (meaning that any value of this attribute works for this variation)
                if ($attribute_value !== '' && $attribute_value !== $attribute_slug) {
                    $attributes_match = false;
                    break;
                }
            }

            // Return matching variation id
            if ($attributes_match) {
                return (int) $variation['variation_id'];
            }
        }

        // No variations match attributes
        return null;
    }

    /**
     * Attempt to get default product variation id
     *
     * @access public
     * @param mixed $product
     * @return int
     */
    public static function get_default_variation_id($product)
    {
        // Product id was passed in
        if (!is_object($product)) {

            // Load product object
            $product = wc_get_product($product);

            if (!$product) {
                return null;
            }
        }

        // Product is not variable
        if ($product->product_type !== 'variable') {
            return null;
        }

        // Get all product attributes and all product variation attributes
        $all_attributes = $product->get_variation_attributes();
        $default_attributes = $product->get_variation_default_attributes();

        // Default variation is only known if all attributes have default attributes
        if (count($all_attributes) === count($default_attributes)) {

            // Get default variation id
            return (int) WCCF_WC_Product::get_variation_id_from_attributes($product, $default_attributes);
        }

        return null;
    }

    /**
     * Maybe change product quantity
     *
     * @access public
     * @param array $args
     * @param object $product
     * @return array
     */
    public function maybe_change_product_quantity($args, $product)
    {
        // Wrong page or request type
        if (!is_product() || $_SERVER['REQUEST_METHOD'] !== 'GET') {
            return $args;
        }

        // Quantity not specified
        if (empty($_GET['wccf_quantity'])) {
            return $args;
        }

        // Change quantity
        $args['input_value'] = (int) $_GET['wccf_quantity'];

        return $args;
    }









}

WCCF_WC_Product::get_instance();

}
