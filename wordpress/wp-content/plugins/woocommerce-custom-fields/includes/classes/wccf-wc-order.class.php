<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to WooCommerce Order
 *
 * @class WCCF_WC_Order
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_Order')) {

class WCCF_WC_Order
{
    protected static $hidden_order_item_meta_key_cache = array();

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

        // CHECKOUT FIELD RELATED

        // Display checkout field values
        add_action('add_meta_boxes', array($this, 'add_meta_box_checkout_field'), 99, 2);

        // Print checkout field values in frontend
        $position = apply_filters('wccf_checkout_field_values_display_position', 10);
        add_action('woocommerce_order_details_after_order_table', array($this, 'print_checkout_field_values_in_frontend'), $position);

        // Send checkout field files as attachments
        add_filter('woocommerce_email_attachments', array($this, 'attach_checkout_field_files'), 99, 3);

        // Print checkout field details in email
        add_action('woocommerce_email_customer_details', array($this, 'print_checkout_field_values_in_email'), 11, 3);

        // ORDER FIELD RELATED

        // Display backend fields
        add_action('add_meta_boxes', array($this, 'add_meta_box_order_field'), 99, 2);

        // Add enctype attribute to the order edit page form to allow file uploads
        add_action('post_edit_form_tag', array($this, 'maybe_add_enctype_attribute'));

        // Save order field data
        add_action('save_post', array($this, 'save_order_field_values'), 10, 2);

        // Print order field values in frontend
        add_action('woocommerce_order_details_after_order_table', array($this, 'print_order_field_values_in_frontend'));

        // Print order field details in email
        add_action('woocommerce_email_customer_details', array($this, 'print_order_field_values_in_email'), 12, 3);

        // PRODUCT FIELD RELATED

        // Hide order item meta
        add_filter('woocommerce_hidden_order_itemmeta', array($this, 'hidden_order_item_meta'));

        // Send product field files as attachments
        add_filter('woocommerce_email_attachments', array($this, 'attach_product_field_files'), 99, 3);

        // Format order item meta for frontend display
        add_filter('woocommerce_order_items_meta_get_formatted', array($this, 'format_order_item_meta'), 10, 2);

        // Display order item meta in backend
        add_filter('woocommerce_after_order_itemmeta', array($this, 'display_backend_order_item_meta'), 10, 3);

        // USER FIELD RELATED

        // Print user field values in admin order view
        add_action('woocommerce_admin_order_data_after_order_details', array($this, 'print_field_values_backend_order_view'));
        add_action('woocommerce_admin_order_data_after_billing_address', array($this, 'print_field_values_backend_order_view'));
        add_action('woocommerce_admin_order_data_after_shipping_address', array($this, 'print_field_values_backend_order_view'));

        // Display user field values (except address) in frontend order view
        add_action('woocommerce_order_details_after_customer_details', array($this, 'print_user_field_values_in_frontend_order_view'));

        // Display user field values (except address) in emails
        add_filter('woocommerce_email_customer_details_fields', array($this, 'add_user_field_values_for_emails'), 10, 3);

        // FIELD EDITING
        add_action('wp_ajax_wccf_get_backend_editing_field', array($this, 'ajax_get_backend_editing_field'));
        add_action('save_post', array($this, 'save_backend_editing_field_values'), 10, 2);
    }

    /**
     * Add meta box to display customer submitted checkout field values
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_checkout_field($post_type, $post)
    {
        // Not product?
        if ($post_type !== 'shop_order') {
            return;
        }

        // Get all fields with all statuses
        $all_fields = WCCF_Checkout_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Check if at least one Checkout Field is configured
        if (empty($all_fields)) {
            return;
        }

        // Add meta box
        add_meta_box(
            'wccf_checkout_field_values',
            apply_filters('wccf_context_label', WCCF_Settings::get('alias_checkout_field'), 'checkout_field', 'backend'),
            array($this, 'print_wccf_checkout_field_values'),
            'shop_order',
            'normal',
            'high'
        );
    }

    /**
     * Render checkout field data
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_wccf_checkout_field_values($post)
    {
        // Get applicable fields (do not filter them when displaying field values)
        $all_fields = WCCF_Checkout_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Track progress
        $container_open = false;

        // Iterate over fields
        foreach ($all_fields as $field) {

            // Get field stored value
            $stored_value = $field->get_stored_value($post->ID);

            // Check if field has any user-submitted value to display
            if ($stored_value !== false) {

                // Open container
                echo '<div class="view"><table cellspacing="0" class="display_meta"><tbody>';
                $container_open = true;
                break;
            }
        }

        // Check if container is open
        if ($container_open) {

            // Iterate over fields
            foreach ($all_fields as $field) {

                // Get field stored value
                $stored_value = $field->get_stored_value($post->ID);

                // Check if field has any user-submitted value to display
                if ($stored_value !== false) {

                    // Open table row and display label
                    echo '<tr><th>' . $field->get_label() . ':</th><td><p>';

                    // Allow field value editing
                    if (WCCF_Field_Controller::field_value_editing_allowed('checkout_field', $field, $stored_value, $post->ID) && $field->validate_stored_entry($post->ID)) {
                        echo '<span class="wccf_backend_editing_value" data-wccf-backend-editing="1" data-wccf-field-id="' . $field->get_id() . '" data-wccf-item-id="' . $post->ID . '">';
                        $backend_value_editing_allowed = true;
                    }

                    // Get formatted value for display
                    $display_value = $field->format_display_value(array(
                        'value' => $stored_value,
                        'data'  => array(),
                    ));

                    // Print field value
                    if (!RightPress_Helper::is_empty($display_value)) {
                        echo $display_value;
                    }
                    else {
                        echo '<span style="font-style: italic; color: #999;">' . __('empty', 'rp_wccf') . '</span>';
                    }

                    // Allow field value editing
                    if (!empty($backend_value_editing_allowed)) {
                        echo '</span>';
                    }

                    // Close table row
                    echo '</p></td></tr>';
                }
            }

            // Close container
            echo '</tbody></table></div>';
        }
        else {
            echo '<div class="wccf_meta_box_no_values">' . __('No field data stored for this order.', 'rp_wccf') . '</div>';
        }
    }

    /**
     * Add meta box for order fields
     *
     * @access public
     * @param string $post_type
     * @param object $post
     * @return void
     */
    public function add_meta_box_order_field($post_type, $post)
    {
        // Not order?
        if ($post_type !== 'shop_order') {
            return;
        }

        // Get all fields with all statuses
        $all_fields = WCCF_Order_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Check if at least one Order Field is configured
        if (empty($all_fields)) {
            return;
        }

        // Add meta box
        add_meta_box(
            'wccf_order_fields',
            apply_filters('wccf_context_label', WCCF_Settings::get('alias_order_field'), 'order_field', 'backend'),
            array($this, 'print_meta_box_order_fields'),
            'shop_order',
            'normal',
            'high'
        );
    }

    /**
     * Print backend order fields
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function print_meta_box_order_fields($post)
    {
        // Get fields to display
        $fields = WCCF_Order_Field_Controller::get_filtered();

        // Print fields
        WCCF_Field_Controller::print_fields($fields, $post->ID);

        // Check if any values were printed
        $any_values_printed = !empty($fields);

        // Get fields including disabled and archived to display previously added values
        $all_fields = WCCF_Order_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Iterate over all fields
        foreach ($all_fields as $field) {

            // Get field stored value
            $stored_value = $field->get_stored_value($post->ID);

            // Check if field has any previously stored value which has not been displayed yet
            if ($stored_value !== false && !$field->is_enabled()) {

                // Enrich with extra data
                $stored_value = array(
                    'value' => $stored_value,
                    'data'  => $field->get_stored_extra_data($post->ID),
                );

                // Print stored value as text
                echo '<div class="wccf_meta_box_field_container wccf_order_field_field_container ">';
                echo '<label>' . $field->get_label() . ' <span class="wccf_meta_box_inactive_field_status_title">(' . $field->get_status_title() . ')</span></label>';
                echo '<div class="wccf_meta_box_stored_value">' . $field->format_display_value($stored_value) . '</div>';
                echo '</div>';

                // Mark that we printed value
                $any_values_printed = true;
            }
        }

        // Print no data notice
        if (!$any_values_printed) {
            echo '<div class="wccf_meta_box_no_values">' . __('No field data stored for this order.', 'rp_wccf') . '</div>';
        }
    }

    /**
     * Add enctype attribute to the order edit page form to allow file uploads
     *
     * @access public
     * @param object $post
     * @return void
     */
    public function maybe_add_enctype_attribute($post)
    {
        // Skip other post types
        if ($post->post_type !== 'shop_order') {
            return;
        }

        // Add enctype attribute
        echo ' enctype="multipart/form-data" ';
    }

    /**
     * Hide order item meta (raw values and meta for internal use)
     *
     * @access public
     * @param array $hidden_keys
     * @return array
     */
    public function hidden_order_item_meta($hidden_keys)
    {
        // Check if order id can be determined
        if ($order_id = RightPress_Helper::get_wc_order_id()) {

            // Check if we have hidden keys already for this order
            if (!isset(self::$hidden_order_item_meta_key_cache[$order_id])) {

                self::$hidden_order_item_meta_key_cache[$order_id] = array();

                // Load order object
                $order = RightPress_Helper::wc_get_order($order_id);

                // Iterate over order items
                foreach ($order->get_items() as $order_item_key => $order_item) {

                    // Iterate over order item meta
                    foreach ($order_item['item_meta'] as $meta_key => $meta) {

                        // Check if this is our internal meta key; also match data stored with 1.x versions of this extension
                        if (preg_match('/^_wccf_/i', $meta_key) || preg_match('/^wccf_/i', $meta_key)) {

                            // Check if it already exists in hidden keys array
                            if (!in_array($meta_key, self::$hidden_order_item_meta_key_cache[$order_id])) {

                                // Add key to hidden keys array
                                self::$hidden_order_item_meta_key_cache[$order_id][] = $meta_key;
                            }
                        }
                    }
                }
            }

            // Add our hidden keys to the main hidden keys array
            $hidden_keys = array_merge($hidden_keys, self::$hidden_order_item_meta_key_cache[$order_id]);
        }

        return $hidden_keys;
    }

    /**
     * Process order field data on order save action
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_order_field_values($post_id, $post)
    {
        // Only process posts with type shop order
        if ($post->post_type !== 'shop_order') {
            return;
        }

        // Store posted field values
        WCCF_Field_Controller::store_field_values($post_id, 'order_field');
    }

    /**
     * Print order field values in frontend
     *
     * @access public
     * @param object $order
     * @param bool $skip_filter
     * @return void
     */
    public function print_order_field_values_in_frontend($order, $skip_filter = false)
    {
        // Get applicable fields
        $fields = WCCF_Order_Field_Controller::get_filtered(array('enabled', 'disabled', 'archived'), array('item_id' => $order->id));

        // Print fields
        WCCF_WC_Order::print_field_values_in_frontend('order_field', $fields, $order, $skip_filter);
    }

    /**
     * Print checkout field values in frontend
     *
     * @access public
     * @param object $order
     * @param bool $skip_filter
     * @return void
     */
    public function print_checkout_field_values_in_frontend($order, $skip_filter = false)
    {
        // Get applicable fields (do not filter them since conditions only need to be checked during Checkout)
        $fields = WCCF_Checkout_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Print fields
        WCCF_WC_Order::print_field_values_in_frontend('checkout_field', $fields, $order, $skip_filter);
    }

    /**
     * Print field values in frontend
     *
     * @access public
     * @param string $context
     * @param array $fields
     * @param object $order
     * @param bool $skip_filter
     * @return void
     */
    public static function print_field_values_in_frontend($context, $fields, $order, $skip_filter)
    {
        // Get values to display
        $display = WCCF_Field_Controller::get_field_values_for_frontend($fields, $order->id, $context);

        // Allow developers to skip displaying frontend field values in default position
        if (!$skip_filter && !apply_filters('wccf_frontend_display_' . $context . '_values', true, $display, $order->id)) {
            return;
        }

        // Check if we have at least one field with value
        if (!empty($display) && is_array($display)) {

            // Get appropriate template
            if ($context === 'checkout_field') {
                $template = 'order/checkout-field-data';
            }
            else if ($context === 'order_field') {
                $template = 'order/order-field-data';
            }
            else {
                return;
            }

            // Include template
            WCCF::include_template($template, array(
                'order'     => $order,
                'fields'    => $display,
            ));
        }
    }

    /**
     * Attach product field files to new order email
     *
     * @access public
     * @param array $attachments
     * @param string $email_id
     * @param object $order
     * @return array
     */
    public function attach_product_field_files($attachments, $email_id, $order)
    {
        // Check if files need to be attached
        if (!WCCF_WC_Order::attachments_allowed($email_id, 'product_field')) {
            return $attachments;
        }

        $attachments = (array) $attachments;

        // Iterate over order items
        foreach ($order->get_items() as $order_item_key => $order_item) {

            // Get order item meta
            $order_item_meta = RightPress_Helper::unwrap_post_meta($order_item['item_meta']);

            // Get quantity purchased
            $quantity = !empty($order_item_meta['_qty']) ? (int) $order_item_meta['_qty'] : 1;

            // Track which fields were already processed
            $processed_fields = array();

            // Iterate over order item meta
            foreach ($order_item_meta as $meta_key => $meta_value) {

                // Check if this is our field
                if (!preg_match('/^_wccf_pf_id_/i', $meta_key)) {
                    continue;
                }

                // Field already processed
                if (in_array($meta_value, $processed_fields, true)) {
                    continue;
                }
                else {
                    $processed_fields[] = $meta_value;
                }

                // Load field
                $field = WCCF_Field_Controller::get($meta_value);

                // Check if field was loaded and is file upload
                if (!$field || !$field->field_type_is('file')) {
                    continue;
                }

                // Handle quantity based fields
                for ($i = 0; $i < $quantity; $i++) {

                    // Get file access keys
                    $access_keys = (array) $field->get_stored_value($order_item_key, $i);

                    // Check if file was uploaded for this field
                    if (empty($access_keys)) {
                        continue;
                    }

                    // Iterate over files
                    foreach ($access_keys as $access_key) {

                        // Get file data
                        $file_data = WCCF_Files::get_data_by_access_key($access_key);

                        // Get temporary file path
                        if ($temporary_file_path = WCCF_Files::get_temporary_file($file_data)) {
                            $attachments[] = $temporary_file_path;
                        }
                    }
                }
            }
        }

        return array_unique($attachments);
    }

    /**
     * Attach checkout field files to new order email
     *
     * @access public
     * @param array $attachments
     * @param string $email_id
     * @param object $order
     * @return array
     */
    public function attach_checkout_field_files($attachments, $email_id, $order)
    {
        // Check if files need to be attached
        if (!WCCF_WC_Order::attachments_allowed($email_id, 'checkout_field')) {
            return $attachments;
        }

        $attachments = (array) $attachments;

        // Get all checkout fields
        $all_fields = WCCF_Checkout_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Iterate over fields
        foreach ($all_fields as $field) {

            // Check if field is file upload
            if (!$field->field_type_is('file')) {
                continue;
            }

            // Get file access key
            $access_keys = (array) $field->get_stored_value($order->id);

            // Check if file was uploaded for this field
            if (empty($access_keys)) {
                continue;
            }

            // Iterate over files
            foreach ($access_keys as $access_key) {

                // Get file data
                $file_data = WCCF_Files::get_data_by_access_key($access_key);

                // Get temporary file path
                if ($temporary_file_path = WCCF_Files::get_temporary_file($file_data)) {
                    $attachments[] = $temporary_file_path;
                }
            }
        }

        return $attachments;
    }

    /**
     * Check if files can be attached
     *
     * @access protected
     * @param string $email_id
     * @param string $context
     * @return bool
     */
    protected static function attachments_allowed($email_id, $context)
    {
        // Send attachments only with specific emails
        if (!in_array($email_id, apply_filters('wccf_attach_' . $context . '_files_email_ids', array('new_order')))) {
            return false;
        }

        // Send attachments only if this functionality is enabled
        if (!WCCF_Settings::get('attach_' . $context . '_files_new_order')) {
            return false;
        }

        return true;
    }

    /**
     * Format order item meta for display
     *
     * @access public
     * @param array $formatted_meta
     * @param object $item_meta
     * @return array
     */
    public function format_order_item_meta($formatted_meta, $item_meta)
    {
        // Get unprocessed meta
        $unprocessed_meta = RightPress_Helper::unwrap_post_meta($item_meta->meta);

        // Get display values
        $display_values = $this->get_display_values_from_order_item_meta($unprocessed_meta, $item_meta->product);

        // Iterate over display values
        foreach ($display_values as $display_value) {

            // Add to formatted meta array
            $formatted_meta[] = $display_value;
        }

        // Get values stored with version 1.x of this extension
        if (WCCF_Migration::support_for('1')) {
            foreach (WCCF_Migration::product_fields_in_order_item_from_1($formatted_meta, $item_meta) as $meta_key => $display_value) {
                $formatted_meta[$meta_key] = $display_value;
            }
        }

        return $formatted_meta;
    }

    /**
     * Display order item meta in order edit view
     *
     * @access public
     * @param int $order_item_id
     * @param array $order_item
     * @param object $product
     * @return void
     */
    public function display_backend_order_item_meta($order_item_id, $order_item, $product)
    {
        // Get unprocessed order item meta
        $unprocessed_meta = RightPress_Helper::unwrap_post_meta(get_metadata('order_item', $order_item_id));

        // Get fields and display values
        $fields = $this->get_fields_from_order_item_meta($unprocessed_meta, $product);
        $display_values = $this->get_display_values_from_order_item_meta($unprocessed_meta, $product, $fields, $order_item_id);

        // Maybe attempt to load value stored in version 1.x
        if (empty($display_values) && WCCF_Migration::support_for('1')) {
            $display_values = WCCF_Migration::product_fields_in_admin_order_item_from_1($order_item_id, $order_item, $product);
        }

        // Check if there are any values to display
        if (empty($display_values)) {
            return;
        }

        // Open table
        echo '<div class="wccf_order_item_meta_container"><table cellspacing="0" class="display_meta">';

        // Iterate over display values
        foreach ($display_values as $display_value) {

            // Print meta
            echo '<tr><th>' . $display_value['label'] . ':</th><td><p>' . $display_value['value'] . '</p></td></tr>';
        }

        // Close table
        echo '</table></div>';
    }

    /**
     * Get fields from unprocessed order item meta
     *
     * @access public
     * @param array $unprocessed_meta
     * @param object $product
     * @return array
     */
    public function get_fields_from_order_item_meta($unprocessed_meta, $product)
    {
        $fields = array();

        // Iterate over unprocessed item meta
        foreach ($unprocessed_meta as $meta_key => $meta_value) {

            // Check if current meta is for product field id
            if (!preg_match('/^_wccf_pf_id_/i', $meta_key)) {
                continue;
            }

            // Get field key from meta
            $field_key_from_meta = preg_replace('/^_wccf_pf_id_/i', '', $meta_key);

            // Load field
            $field = WCCF_Field_Controller::get($meta_value, 'wccf_product_field');

            // Check if field was loaded and is not trashed
            if (!$field || RightPress_Helper::post_is_trashed($field->get_id())) {
                continue;
            }

            // Add to fields list
            $fields[$field->get_id()] = $field;
        }

        return $fields;
    }

    /**
     * Get field values for display from unprocessed order item meta
     *
     * @access public
     * @param array $unprocessed_meta
     * @param object $product
     * @param array $fields
     * @param int $order_item_id
     * @return array
     */
    public function get_display_values_from_order_item_meta($unprocessed_meta, $product, $fields = null, $order_item_id = null)
    {
        $display_values = array();

        // Get fields
        $fields = $fields ?: $this->get_fields_from_order_item_meta($unprocessed_meta, $product);

        // Check if pricing can be displayed for this product
        $display_pricing = null;

        // Iterate over fields
        foreach ($fields as $field) {

            // Iterate over values
            foreach ($unprocessed_meta as $meta_key => $meta_value) {
                if (RightPress_Helper::string_contains_phrase($meta_key, $field->get_value_access_key())) {

                    // Get quantity index
                    if ($meta_key !== $field->get_value_access_key()) {

                        // Get potential quantity index to check
                        $quantity_index = str_replace(($field->get_value_access_key() . '_'), '', $meta_key);

                        // Quantity index must be numeric and can't be zero
                        if (!is_numeric($quantity_index) || (int) $quantity_index === 0) {
                            continue;
                        }

                        // Attempt to get extra data
                        $extra_data = RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_extra_data_access_key($quantity_index));

                        // Extra data not found or quantity index is not in the extra data array
                        if (empty($extra_data) || !is_array($extra_data) || !isset($extra_data['quantity_index']) || (int) $extra_data['quantity_index'] !== (int) $quantity_index) {
                            continue;
                        }

                        // Quantity index validation passed
                        $quantity_index = (int) $quantity_index;
                    }
                    else {
                        $quantity_index = null;
                    }

                    // Get stored value from meta
                    $field_value = RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_value_access_key($quantity_index));

                    // Check if value was found
                    if ($field_value !== false) {

                        // Check if pricing can be displayed for this product
                        if ($display_pricing === null) {
                            $display_pricing = !WCCF_WC_Product::skip_pricing($product);
                        }

                        // Enrich with extra data
                        $field_value = array(
                            'value' => $field_value,
                            'data'  => RightPress_Helper::array_value_or_false($unprocessed_meta, $field->get_extra_data_access_key($quantity_index)),
                        );

                        // Get display value
                        $display_value = $field->format_display_value($field_value, $display_pricing);

                        // Display empty notice if field is empty
                        if (RightPress_Helper::is_empty($display_value)) {
                            $display_value = '<span style="font-style: italic; color: #999;">' . __('empty', 'rp_wccf') . '</span>';
                        }

                        // Allow field value editing
                        if (!empty($order_item_id) && WCCF_Field_Controller::field_value_editing_allowed('product_field', $field, $field_value, $order_item_id)) {
                            $attributes = 'data-wccf-backend-editing="1" data-wccf-field-id="' . $field->get_id() . '" data-wccf-item-id="' . $order_item_id . '" data-wccf-quantity-index="' . $quantity_index . '" ';
                            $display_value = '<span class="wccf_backend_editing_value" ' . $attributes . '>' . $display_value . '</span>';
                        }

                        // Get field label
                        $field_label = $field->get_label();

                        if ($quantity_index) {
                            $field_label = WCCF_Field_Controller::get_quantity_adjusted_field_label($field_label, $quantity_index);
                        }

                        // Format meta
                        $display_values[] = array(
                            'key'   => 'wccf_pf_' . $field->get_key(),
                            'label' => $field_label,
                            'value' => $display_value,
                        );
                    }
                }
            }
        }

        return $display_values;
    }

    /**
     * Get backend value editing field via Ajax
     *
     * @access public
     * @return void
     */
    public function ajax_get_backend_editing_field()
    {
        try {

            // Field id or order id is not set
            if (empty($_POST['field_id']) || empty($_POST['item_id'])) {
                throw new Exception();
            }

            // Load field
            $field = WCCF_Field_Controller::cache($_POST['field_id']);

            // No such field
            if (!$field) {
                throw new Exception();
            }

            // User not allowed to perform this action
            if (!WCCF::is_authorized('edit_user_submitted_values', array('item_id' => $_POST['item_id'], 'context' => $field->get_context()))) {
                throw new Exception();
            }

            // Get quantity index
            $quantity_index = !empty($_POST['quantity_index']) ? (int) $_POST['quantity_index'] : null;

            // Get field html
            $field_html = WCCF_WC_Order::get_field_html_for_backend_value_editing($field, $_POST['item_id'], $quantity_index);

            // Return field html
            echo json_encode(array(
                'result'    => 'success',
                'field'     => $field_html,
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
     * Get field html for backend value editing
     *
     * @access public
     * @param object $field
     * @param int $item_id
     * @param int $quantity_index
     * @return string
     */
    public static function get_field_html_for_backend_value_editing($field, $item_id, $quantity_index = null)
    {
        // Get some field properties
        $field_id   = $field->get_id();
        $field_type = $field->get_field_type();
        $context    = $field->get_context();

        // Get stored value
        if ($context === 'user_field') {
            $stored_value = get_post_meta($item_id, $field->get_value_access_key(), true);
        }
        else {
            $stored_value = $field->get_stored_value($item_id, $quantity_index);
        }

        // Field name treatment for quantity based product fields
        $field_id_for_name = $quantity_index ? ($field_id . '_' . $quantity_index) : $field_id;

        // Configure field
        $attributes = array(
            'id'            => 'wccf_backend_editing_' . $item_id . '_' . $field_id . ($quantity_index ? ('_' . $quantity_index) : ''),
            'name'          => 'wccf_backend_editing[' . $item_id . '][' . $field_id_for_name . ']' . ($field->accepts_multiple_values() ? '[]' : ''),
            'class'         => 'wccf_backend_editing wccf_backend_editing_' . $context . ' wccf_backend_editing_' . $field_type . ' wccf_backend_editing_' . $context . '_' . $field_type,
            'required'      => $field->is_required(),
            'maxlength'     => $field->get_character_limit(),
            'value'         => $stored_value,
        );

        // Set options if this field has any
        if ($field->has_options()) {
            $attributes['options'] = $field->get_options_list();
        }

        // Start output buffering
        ob_start();

        // Print hidden placeholder input
        if (in_array($field_type, array('multiselect', 'checkbox', 'radio'), true)) {
            WCCF_FB::print_placeholder_input($attributes);
        }

        // Print field
        WCCF_FB::$field_type($attributes);

        // Return html
        return ob_get_clean();
    }

    /**
     * Save backend editing field values
     *
     * @access public
     * @param int $post_id
     * @param object $post
     * @return void
     */
    public function save_backend_editing_field_values($post_id, $post)
    {
        // Only process posts with type shop order
        if ($post->post_type !== 'shop_order') {
            return;
        }

        // No backend editing field values submitted
        if (empty($_POST['wccf_backend_editing']) || !is_array($_POST['wccf_backend_editing'])) {
            return;
        }

        // Iterate over backend editing field values
        foreach ($_POST['wccf_backend_editing'] as $item_id => $field_values) {
            foreach ($field_values as $field_id => $field_value) {

                // Get quantity index
                $quantity_index = WCCF_Field_Controller::get_quantity_index_from_field_id($field_id);

                // Get clean field id
                if ($quantity_index) {
                    $field_id = WCCF_Field_Controller::clean_field_id($field_id);
                }

                // Load field
                $field = WCCF_Field_Controller::cache($field_id);

                // No such field
                if (!$field) {
                    continue;
                }

                // User not allowed to perform this action
                if (!WCCF::is_authorized('edit_user_submitted_values', array('item_id' => $item_id, 'context' => $field->get_context()))) {
                    continue;
                }

                // Prepare multiselect field values
                if ($field->accepts_multiple_values()) {

                    // Ensure that value is array
                    $value = !RightPress_Helper::is_empty($field_value) ? (array) $field_value : array();

                    // Filter out hidden placeholder input value
                    $value = array_filter((array) $value, function($test_value) {
                        return trim($test_value) !== '';
                    });
                }
                else {
                    $value = trim($field_value);
                }

                // Field value is invalid
                if ((RightPress_Helper::is_empty($value) && $field->is_required()) || (!RightPress_Helper::is_empty($value) && !WCCF_Field_Controller::validate_field_value($field, $value, $item_id))) {
                    continue;
                }

                // Get stored value
                if ($field->context_is('user_field')) {
                    $stored_value = get_post_meta($item_id, $field->get_value_access_key($quantity_index), true);
                }
                else {
                    $stored_value = $field->get_stored_value($item_id, $quantity_index);
                }

                // Stored value integrity validation failed or new value does not differ from old value
                if ($stored_value === false || $stored_value === $value) {
                    continue;
                }

                // Store new value
                if ($field->context_is('user_field')) {
                    update_post_meta($item_id, $field->get_value_access_key(), $value);
                }
                else {
                    $field->store_in_meta($item_id, $field->get_value_access_key($quantity_index), $value);
                }

                // Update option labels in extra data if needed
                if ($field->has_options()) {

                    // Get current extra data value
                    if ($field->context_is('user_field')) {
                        $extra_data = get_post_meta($item_id, $field->get_extra_data_access_key(), true);
                    }
                    else {
                        $extra_data = $field->get_stored_extra_data($item_id, $quantity_index);
                    }

                    // Ensure that extra data value is array
                    $extra_data = $extra_data ? (array) $extra_data : array();

                    // Replace labels with a new set
                    $extra_data['labels'] = $field->get_option_labels_from_keys($value);

                    // Store extra data
                    if ($field->context_is('user_field')) {
                        update_post_meta($item_id, $field->get_extra_data_access_key(), $extra_data);
                    }
                    else {
                        $field->store_in_meta($item_id, $field->get_extra_data_access_key($quantity_index), $extra_data);
                    }
                }
            }
        }
    }

    /**
     * Print user field values in admin order view
     *
     * Note that we are loading values from order meta not from user meta since
     * we care more about what values user submitted when placing an order
     * and not what they look like now
     *
     * @access public
     * @param object $order
     * @return void
     */
    public function print_field_values_backend_order_view($order)
    {
        // Check which fields are being displayed
        $current_filter = current_filter();

        if ($current_filter === 'woocommerce_admin_order_data_after_billing_address') {
            $display_as = 'billing_address';
        }
        else if ($current_filter === 'woocommerce_admin_order_data_after_shipping_address') {
            $display_as = 'shipping_address';
        }
        else {
            $display_as = 'user_profile';
        }

        // Get applicable fields (do not filter them when displaying field values)
        $all_fields = WCCF_User_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));
        $fields = WCCF_Field_Controller::filter_by_property($all_fields, 'display_as', $display_as);

        // Get values to display
        $values = WCCF_WC_Order::get_field_values_from_order($order, $fields);

        $header_printed = false;

        // Iterate over values
        foreach ($values as $field_id => $value) {

            // Reference current field
            $field = $fields[$field_id];

            // Print section header
            if (!$header_printed) {

                // Fix element flow
                echo '<div style="clear: both;"></div>';

                // Display user profile field header
                if ($display_as === 'user_profile') {
                    echo '<h3>' . WCCF_Settings::get('alias_user_field') . '</h3>';
                }

                $header_printed = true;
            }

            // Open container
            echo '<p class="wccf_order_page_user_field_value"><strong>' . $field->get_label() . ':</strong>';

            // Allow field value editing
            if (WCCF_Field_Controller::field_value_editing_allowed('user_field', $field, $value, $order->id)) {
                echo '<span class="wccf_backend_editing_value" data-wccf-backend-editing="1" data-wccf-field-id="' . $field->get_id() . '" data-wccf-item-id="' . $order->id . '">';
                $backend_value_editing_allowed = true;
            }

            // Get formatted value for display
            $display_value = $field->format_display_value(array(
                'value' => $value,
                'data'  => array(),
            ));

            // Print field value
            if (!RightPress_Helper::is_empty($display_value)) {
                echo $display_value;
            }
            else {
                echo '<span style="font-style: italic; color: #999;">' . __('empty', 'rp_wccf') . '</span>';
            }

            // Allow field value editing
            if (!empty($backend_value_editing_allowed)) {
                echo '</span>';
            }

            // Close container
            echo '</p>';
        }
    }

    /**
     * Get field values stored for order
     *
     * @access public
     * @param object $order
     * @param array $fields
     * @return array
     */
    public static function get_field_values_from_order($order, $fields)
    {
        $values = array();

        // Iterate over fields
        foreach ($fields as $field) {

            // Validate stored entry
            if ((int) get_post_meta($order->id, $field->get_id_access_key(), true) !== $field->get_id()) {
                continue;
            }

            // Check if stored value exists
            if (!RightPress_Helper::post_meta_key_exists($order->id, $field->get_value_access_key())) {
                continue;
            }

            // Get field stored value
            $stored_value = get_post_meta($order->id, $field->get_value_access_key(), true);

            // Check if field has any user-submitted value to display
            if ($stored_value !== null) {
                $values[$field->get_id()] = $stored_value;
            }
        }

        return $values;
    }

    /**
     * Get field display values stored for order
     *
     * @access public
     * @param string $context
     * @param object $order
     * @param string $display_as
     * @return array
     */
    public static function get_field_display_values_from_order($context, $order, $display_as = 'user_profile')
    {
        $display_values = array();

        // Get applicable fields (do not filter them when displaying field values)
        $fields = WCCF_User_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));

        // Filter out user fileds by display property
        if ($context === 'user_field') {
            $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', $display_as);
        }

        // Get values
        $values = WCCF_WC_Order::get_field_values_from_order($order, $fields);

        // Iterate over values
        foreach ($values as $field_id => $value) {

            // Reference current field
            $field = $fields[$field_id];

            // Get formatted value for display
            $display_value = $field->format_display_value(array(
                'value' => $value,
                'data'  => array(),
            ));

            // Add to array
            $display_values[$field_id] = array(
                'id'            => $field_id,
                'value'         => $value,
                'display_value' => $display_value,
                'field'         => $field,
            );
        }

        return $display_values;
    }

    /**
     * Display user field values (except address) in frontend order view
     *
     * @access public
     * @param object $order
     * @param bool $skip_filter
     * @return void
     */
    public function print_user_field_values_in_frontend_order_view($order, $skip_filter = false)
    {
        // Get display values
        $display = WCCF_WC_Order::get_field_display_values_from_order('user_field', $order);

        // Allow developers to skip displaying frontend field values in default position
        if (!$skip_filter && !apply_filters('wccf_frontend_display_user_field_values', true, $display, $order->id)) {
            return;
        }

        // Check if we have something to display
        if (!empty($display) && is_array($display)) {

            // Include template
            WCCF::include_template('order/user-field-data', array(
                'order'     => $order,
                'fields'    => $display,
            ));
        }
    }

    /**
     * Display user field values (except address) in emails
     *
     * @access public
     * @param array $fields
     * @param bool $sent_to_admin
     * @param object $order
     * @return array
     */
    public function add_user_field_values_for_emails($fields, $sent_to_admin, $order)
    {
        $values = array();

        // Order not set
        if (!is_object($order)) {
            return $fields;
        }

        // Get display values
        $display = WCCF_WC_Order::get_field_display_values_from_order('user_field', $order);

        // Allow developers to skip displaying field values in emails
        if (!apply_filters('wccf_email_display_user_field_values', true, $display, $order->id)) {
            return $fields;
        }

        // Get them in required format
        foreach ($display as $value) {
            $values[] = array(
                'label' => $value['field']->get_label(),
                'value' => $value['display_value'],
            );
        }

        // Add to other fields and return
        return array_merge($fields, $values);
    }

    /**
     * Print checkout field values in email
     *
     * @access public
     * @param object $order
     * @param bool $sent_to_admin
     * @param string $plain_text
     * @return array
     */
    public function print_checkout_field_values_in_email($order, $sent_to_admin = false, $plain_text = false)
    {
        WCCF_WC_Order::print_field_values_in_email('checkout_field', $order, $sent_to_admin, $plain_text);
    }

    /**
     * Print order field values in email
     *
     * @access public
     * @param object $order
     * @param bool $sent_to_admin
     * @param string $plain_text
     * @return array
     */
    public function print_order_field_values_in_email($order, $sent_to_admin = false, $plain_text = false)
    {
        WCCF_WC_Order::print_field_values_in_email('order_field', $order, $sent_to_admin, $plain_text);
    }

    /**
     * Print field values in email
     *
     * @access public
     * @param string $context
     * @param object $order
     * @param bool $sent_to_admin
     * @param string $plain_text
     * @return array
     */
    public static function print_field_values_in_email($context, $order, $sent_to_admin, $plain_text, $skip_filter = false)
    {
        // Get applicable fields (do not filter them since conditions only need to be checked during Checkout)
        if ($context === 'order_field') {
            $fields = WCCF_Order_Field_Controller::get_filtered(array('enabled', 'disabled', 'archived'), array('item_id' => $order->id));
        }
        else if ($context === 'checkout_field') {
            $fields = WCCF_Checkout_Field_Controller::get_all(array('enabled', 'disabled', 'archived'));
        }
        else {
            return;
        }

        // Get values to display
        $display = WCCF_Field_Controller::get_field_values_for_frontend($fields, $order->id, $context);

        // Allow developers to skip displaying field values in emails
        if (!$skip_filter && !apply_filters('wccf_email_display_' . $context . '_values', true, $display, $order->id)) {
            return;
        }

        // Check if we have at least one field with value
        if (!empty($display) && is_array($display)) {

            // Get appropriate template
            if ($context === 'checkout_field') {
                $template = 'checkout-field-data';
            }
            else if ($context === 'order_field') {
                $template = 'order-field-data';
            }
            else {
                return;
            }

            // Check if plain text template needs to be used instead of regular one
            $template = ($plain_text ? 'emails/plain/' : 'emails/') . $template;

            // Include template
            WCCF::include_template($template, array(
                'order'     => $order,
                'fields'    => $display,
            ));
        }
    }





}

WCCF_WC_Order::get_instance();

}
