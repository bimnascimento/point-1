<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Cart
 *
 * @class WCCF_WC_Cart
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Cart')) {

class WCCF_WC_Cart
{
    private $custom_woocommerce_price_num_decimals;

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

        // Add to cart validation
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_cart_item_product_field_values'), 10, 4);
        add_action('wp_loaded', array($this, 'maybe_redirect_to_product_page_after_failed_validation'), 20);

        // Add field values to cart item meta data
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_product_field_values'), 10, 3);

        // Adjust cart item pricing
        add_filter('woocommerce_add_cart_item', array($this, 'adjust_cart_item_pricing'), 11);

        // Cart loaded from session
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'get_cart_item_from_session'), 11, 3);

        // Print more decimals in cart item price if needed
        add_filter('woocommerce_cart_item_price', array($this, 'print_more_decimals_in_price'), 1, 3);

        // Get values for display in cart
        add_filter('woocommerce_get_item_data', array($this, 'get_values_for_display'), 11, 2);

        // Add configuration query vars to product link
        add_filter('woocommerce_cart_item_permalink', array($this, 'add_query_vars_to_cart_item_link'), 99, 3);

        // Disable quantity change in cart if at least one field that has value in cart item meta is quantity based
        add_filter('woocommerce_cart_item_quantity', array($this, 'maybe_disable_quantity_change'), 99, 2);
    }

    /**
     * Validate product field values on add to cart
     *
     * @access public
     * @param bool $is_valid
     * @param int $product_id
     * @param int $quantity
     * @param int $variation_id
     * @return bool
     */
    public function validate_cart_item_product_field_values($is_valid, $product_id, $quantity, $variation_id = null)
    {
        // Maybe skip product fields for this product based on various conditions
        if (WCCF_WC_Product::skip_product_fields($product_id, $variation_id)) {
            return $is_valid;
        }

        // Get fields for validation
        $fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => $product_id, 'child_id' => $variation_id));

        // Validate all fields
        // Note - we will need to pass $variation_id here somehow if we ever implement variation-level conditions
        $validation_result = WCCF_Field_Controller::validate_posted_field_values('product_field', array(
            'object_id' => $product_id,
            'fields'    => $fields,
            'quantity'  => $quantity,
        ));

        if (!$validation_result) {
            define('WCCF_ADD_TO_CART_VALIDATION_FAILED', true);
            return false;
        }

        return $is_valid;
    }

    /**
     * Maybe redirect to product page if add to cart action was initiated via
     * URL and its validation failed and URL does not include product URL
     *
     * @access public
     * @return void
     */
    public function maybe_redirect_to_product_page_after_failed_validation()
    {
        // Our validation failed
        if (defined('WCCF_ADD_TO_CART_VALIDATION_FAILED') && WCCF_ADD_TO_CART_VALIDATION_FAILED) {

            // Add to cart was from link as opposed to regular add to cart when data is posted
            if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['add-to-cart'])) {

                // Get product
                $product = RightPress_Helper::wc_get_product($_GET['add-to-cart']);

                // Product was not loaded
                if (!$product) {
                    return;
                }

                // Get urls to compare
                $request_url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                $product_url = untrailingslashit(get_permalink($product->id));

                // Current request url does not contain product url
                if (strpos($request_url, str_replace(array('http://', 'https://'), array('', ''), $product_url)) === false) {

                    // Add query string to product url
                    if (strpos($product_url, '?') === false) {
                        $redirect_url = $product_url . $_SERVER['REQUEST_URI'];
                    }
                    else {
                        $redirect_url = $product_url . str_replace('?', '&', $_SERVER['REQUEST_URI']);
                    }

                    // Unset notices since we will repeat the same exact process and all notices will be added again
                    wc_clear_notices();

                    // Redirect to product page
                    wp_redirect($redirect_url);
                    exit;
                }
            }
        }
    }

    /**
     * Add product field values to cart item meta
     *
     * @access public
     * @param array $cart_item_data
     * @param int $product_id
     * @param int $variation_id
     * @return array
     */
    public function add_cart_item_product_field_values($cart_item_data, $product_id, $variation_id)
    {
        // Maybe skip product fields for this product based on various conditions
        if (WCCF_WC_Product::skip_product_fields($product_id, $variation_id)) {
            return $cart_item_data;
        }

        // Get fields to save values for
        $fields = WCCF_Product_Field_Controller::get_filtered(null, array('item_id' => $product_id, 'child_id' => $variation_id));

        // Get quantity
        $quantity = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount($_REQUEST['quantity']);

        // Sanitize field values
        // Note - we will need to pass $variation_id here somehow if we ever implement variation-level conditions
        $values = WCCF_Field_Controller::sanitize_posted_field_values('product_field', array(
            'object_id'         => $product_id,
            'fields'            => $fields,
            'quantity'          => $quantity,
        ));

        // Check if we have any values to store
        if ($values) {

            // Store values
            $cart_item_data['wccf'] = $values;

            // Check if we have any quantity based fields added to values (may need to display more decimals so that the total adds up correctly)
            foreach ($values as $field_id => $value) {
                if (isset($fields[$field_id]) && $fields[$field_id]->is_quantity_based()) {
                    $cart_item_data['wccf_quantity_based_hash'] = RightPress_Helper::get_hash();
                    break;
                }
            }
        }

        return $cart_item_data;
    }

    /**
     * Adjust cart item pricing
     *
     * @access public
     * @param array $cart_item
     * @return
     */
    public function adjust_cart_item_pricing($cart_item)
    {
        // Proceed only if we have any custom fields set
        if (!empty($cart_item['wccf'])) {

            // Get quantity
            $quantity = !empty($cart_item['quantity']) ? (int) $cart_item['quantity'] : 1;

            // Get variation id
            $variation_id = !empty($cart_item['variation_id']) ? $cart_item['variation_id'] : null;

            // Get adjusted price
            $adjusted_price = WCCF_Pricing::get_adjusted_price($cart_item['data']->price, $cart_item['product_id'], $variation_id, $cart_item['wccf'], $quantity, false, false);

            // Allow more spaces to fix totals for quantity based price adjusting fields
            $adjusted_price = WCCF_Pricing::fix_quantity_based_fields_product_price($adjusted_price, $quantity);

            // Check if price was actually adjusted
            if ($adjusted_price !== (float) $cart_item['data']->price) {

                // Set new price
                $cart_item['data']->set_price($adjusted_price);
            }

            // Add a flag that this is a cart item product so that we don't adjust the price again with default values via filter hook woocommerce_get_price
            $cart_item['data']->wccf_cart_item_product = true;
        }

        return $cart_item;
    }

    /**
     * Cart loaded from session
     *
     * @access public
     * @param array $cart_item
     * @param array $values
     * @param string $key
     * @return array
     */
    public function get_cart_item_from_session($cart_item, $values, $key)
    {
        // Check if we have any product field data stored in cart
        if (!empty($values['wccf'])) {

            // Migrate data if needed
            if (WCCF_Migration::support_for('1')) {
                foreach ($values['wccf'] as $key => $value) {
                    if (isset($value['key']) && !isset($value['data'])) {
                        $values['wccf'] = WCCF_Migration::product_fields_in_cart_from_1_to_2($values['wccf']);
                        break;
                    }
                }
            }

            // Possibly adjust cart item pricing
            $cart_item['wccf'] = $values['wccf'];
            $cart_item = $this->adjust_cart_item_pricing($cart_item);
        }

        return $cart_item;
    }

    /**
     * Print more decimals in cart item price if needed
     *
     * @access public
     * @param string $price_html
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function print_more_decimals_in_price($price_html, $cart_item, $cart_item_key)
    {
        if (!empty($cart_item['wccf_quantity_based_hash'])) {

            // Get quantity
            $quantity = !empty($cart_item['quantity']) ? $cart_item['quantity'] : 1;

            // Check how many decimals we need to display
            $decimals = WCCF_Pricing::get_required_decimals_to_fix_price($cart_item['data']->price, $quantity);

            // Check if we need to display more decimals
            if ($decimals > (int) wc_get_price_decimals()) {

                // Get product from cart
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                // Add temporary filter to display more decimals
                $this->custom_woocommerce_price_num_decimals = $decimals;
                add_filter('option_woocommerce_price_num_decimals', array($this, 'change_woocommerce_decimals'), 99);

                // Get product price
                $price_html = WC()->cart->get_product_price($_product);

                // Remove temporary filter
                $this->custom_woocommerce_price_num_decimals = null;
                remove_filter('option_woocommerce_price_num_decimals', array($this, 'change_woocommerce_decimals'), 99);
            }
        }

        return $price_html;
    }

    /**
     * Maybe change WooCommerce price decimals value
     *
     * @access public
     * @param int $decimals
     * @return int
     */
    public function change_woocommerce_decimals($decimals)
    {
        if (!RightPress_Helper::is_empty($this->custom_woocommerce_price_num_decimals)) {
            return $this->custom_woocommerce_price_num_decimals;
        }

        return $decimals;
    }

    /**
     * Get product field values to display in cart
     *
     * @access public
     * @param array $data
     * @param array $cart_item
     * @return array
     */
    public function get_values_for_display($data, $cart_item)
    {
        if (!empty($cart_item['wccf'])) {
            foreach ($cart_item['wccf'] as $field_id => $field_value) {

                // Get quantity index
                $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);

                // Get clean field id
                if ($quantity_index) {
                    $field_id = WCCF_Field_Controller::clean_field_id($field_id);
                }

                // Get field
                $field = WCCF_Field_Controller::get($field_id, 'wccf_product_field');

                // Make sure this field exists
                if (!$field) {
                    continue;
                }

                // Check if pricing can be displayed for this product
                $display_pricing = !WCCF_WC_Product::skip_pricing($cart_item['data']->id);

                // Get display value
                $display_value = $field->format_display_value($field_value, $display_pricing, true);

                // Get field label
                $field_label = $field->get_label();

                // Field label treatment for quantity based product fields
                if ($quantity_index) {
                    $field_label = WCCF_Field_Controller::get_quantity_adjusted_field_label($field_label, $quantity_index);
                }

                // Add to data array
                $data[] = array(
                    'name'      => $field_label,
                    'value'     => $display_value,
                    'display'   => $display_value,
                );
            }
        }

        return $data;
    }

    /**
     * Add configuration query vars to product link
     *
     * @access public
     * @param string $link
     * @param array $cart_item
     * @param string $cart_item_key
     * @return string
     */
    public function add_query_vars_to_cart_item_link($link, $cart_item, $cart_item_key)
    {
        // No link provided
        if (empty($link)) {
            return $link;
        }

        // Do not add query vars
        if (!apply_filters('wccf_preconfigured_cart_item_product_link', true, $link, $cart_item, $cart_item_key)) {
            return $link;
        }

        $new_link = $link;
        $quantity_based_field_found = false;

        // Add a flag to indicate that this link is cart item link to product
        $new_link = add_query_arg('wccf_qv_conf', 1, $new_link);

        // Cart item does not have custom fields
        if (empty($cart_item['wccf'])) {
            return $new_link;
        }

        // Iterate over field values
        foreach ($cart_item['wccf'] as $field_id => $field_value) {

            // Load field
            $field = WCCF_Field_Controller::cache(WCCF_Field_Controller::clean_field_id($field_id));

            // Unable to load field - if we can't get full configuration, don't add anything at all
            if (!$field) {
                return $link;
            }

            // Check if field is quantity based
            $quantity_based_field_found = $quantity_based_field_found ?: $field->is_quantity_based();

            // Get quantity index
            $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);

            // Get query var key
            $query_var_key = 'wccf_' . $field->get_context() . '_' . $field->get_id() . ($quantity_index ? ('_' . $quantity_index) : '');

            // Handle array values
            if (is_array($field_value['value'])) {

                // Fix query var key
                $query_var_key .= '[]';

                $is_first = true;

                foreach ($field_value['value'] as $single_value) {

                    // Encode current value
                    $current_value = rawurlencode($single_value);

                    // Handle first value
                    if ($is_first) {

                        // Add query var
                        $new_link = add_query_arg($query_var_key, $current_value, $new_link);

                        // Check if query var was added
                        if (strpos($new_link, $query_var_key) !== false) {
                            $is_first = false;
                        }
                    }
                    // Handle subsequent values - add_query_arg does not allow duplicate query vars
                    else {

                        if ($frag = strstr($new_link, '#')) {
                            $new_link = substr($new_link, 0, -strlen($frag));
                        }

                        $new_link .= '&' . $query_var_key . '=' . $current_value;

                        if ($frag) {
                            $new_link .= $frag;
                        }
                    }

                }
            }
            else {
                $new_link = add_query_arg($query_var_key, rawurlencode($field_value['value']), $new_link);
            }
        }

        // Add quantity
        if ($quantity_based_field_found && strpos($new_link, 'wccf_') !== false && !empty($cart_item['quantity']) && $cart_item['quantity'] > 1) {
            $new_link .= '&wccf_quantity=' . $cart_item['quantity'];
        }

        // Bail if our URL is longer than URL length limit of 2000
        if (strlen($new_link) > 2000) {
            return $link;
        }

        // Return new link
        return $new_link;
    }

    /**
     * Disable quantity change in cart if at least one field that has value in cart item meta is quantity based
     *
     * @access public
     * @param string $html
     * @param string $cart_item_key
     * @return string
     */
    public function maybe_disable_quantity_change($html, $cart_item_key)
    {
        // Get cart item
        $cart = WC()->cart->get_cart();
        $cart_item = $cart[$cart_item_key];

        // Check if cart item has any custom field values
        if (!empty($cart_item['wccf'])) {

            // Iterate over custom field values
            foreach ($cart_item['wccf'] as $field_id => $field_value) {

                // Load field
                $field = WCCF_Field_Controller::cache($field_id);

                // Field is quantity based
                if ($field && $field->is_quantity_based()) {

                    // Disable quantity change
                    $quantity = !empty($cart_item['quantity']) ? $cart_item['quantity'] : 1;
                    return sprintf('%d <input type="hidden" name="cart[%s][qty]" value="%d" />', $quantity, $cart_item_key, $quantity);
                }
            }
        }

        return $html;
    }



}

WCCF_WC_Cart::get_instance();

}
