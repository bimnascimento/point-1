<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_ORDER {

    function __construct() {

        add_filter('woocommerce_order_number', array(&$this, 'wpie_woocommerce_order_number'), 9999);

        add_action('wp_ajax_wpie_create_order_csv', array(&$this, 'wpie_create_order_csv'));

        add_action('wp_ajax_wpie_execute_order_data_query', array(&$this, 'wpie_execute_order_data_query'));

        add_action('wp_ajax_wpie_update_order_csv', array(&$this, 'wpie_update_order_csv'));

        add_action('wp_ajax_wpie_save_order_fields', array(&$this, 'wpie_save_order_fields'));

        add_action('wp_ajax_wpie_create_order_preview', array(&$this, 'wpie_create_order_preview'));

        add_action('wp_ajax_wpie_get_order_export_preview', array(&$this, 'wpie_get_order_export_preview'));

        add_action('wp_ajax_wpie_import_order', array(&$this, 'wpie_import_order'));

        add_action('wp_ajax_wpie_get_order_import_preview', array(&$this, 'wpie_get_order_import_preview'));

        add_action('wp_ajax_wpie_import_order_percentage', array(&$this, 'wpie_import_order_percentage'));
    }

    function get_updated_order_fields() {

        $old_order_fields = $this->get_new_order_fields();

        $new_fields = get_option('wpie_order_fields', $old_order_fields);

        $new_fields = maybe_unserialize($new_fields);

        return $new_fields;
    }

    function get_woo_order_status() {

        $shop_order_status = array();

        if (function_exists('wc_get_order_statuses')) {
            $shop_order_status = wc_get_order_statuses();
        } else {
            $shop_order_status = get_terms('shop_order_status', 'orderby=id&hide_empty=1');
        }

        return $shop_order_status;
    }

    function get_order_list() {

        $query_args = array(
            'posts_per_page' => 2000,
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids',
        );
        if (function_exists('wc_get_order_statuses')) {

            $query_args['post_status'] = array_keys($this->get_woo_order_status());
        }

        $orders_list = get_posts($query_args);

        return $orders_list;
    }

    function get_new_order_fields() {

        $order_fields = maybe_serialize($this->order_field_list());

        return $order_fields;
    }

    function order_field_list() {
        $field_list = array(
            'order_field' => array(
                array(
                    'field_key' => 'id',
                    'field_display' => 1,
                    'field_title' => 'Id',
                    'field_value' => 'Id',
                ),
                array(
                    'field_key' => 'order_final_status',
                    'field_display' => 1,
                    'field_title' => 'Status',
                    'field_value' => 'Status',
                ),
                array(
                    'field_key' => 'order_date',
                    'field_display' => 1,
                    'field_title' => 'Order Date',
                    'field_value' => 'Order Date',
                ),
                array(
                    'field_key' => '_billing_first_name',
                    'field_display' => 1,
                    'field_title' => 'First Name (Billing)',
                    'field_value' => 'First Name (Billing)',
                ),
                array(
                    'field_key' => '_billing_last_name',
                    'field_display' => 1,
                    'field_title' => 'Last Name (Billing)',
                    'field_value' => 'Last Name (Billing)',
                ),
                array(
                    'field_key' => '_billing_company',
                    'field_display' => 1,
                    'field_title' => 'Company (Billing)',
                    'field_value' => 'Company (Billing)',
                ),
                array(
                    'field_key' => '_billing_address_1',
                    'field_display' => 1,
                    'field_title' => 'Address 1 (Billing)',
                    'field_value' => 'Address 1 (Billing)',
                ),
                array(
                    'field_key' => '_billing_address_2',
                    'field_display' => 1,
                    'field_title' => 'Address 2 (Billing)',
                    'field_value' => 'Address 2 (Billing)',
                ),
                array(
                    'field_key' => '_billing_city',
                    'field_display' => 1,
                    'field_title' => 'City (Billing)',
                    'field_value' => 'City (Billing)',
                ),
                array(
                    'field_key' => '_billing_postcode',
                    'field_display' => 1,
                    'field_title' => 'Postcode (Billing)',
                    'field_value' => 'Postcode (Billing)',
                ),
                array(
                    'field_key' => '_billing_country',
                    'field_display' => 1,
                    'field_title' => 'Country (Billing)',
                    'field_value' => 'Country (Billing)',
                ),
                array(
                    'field_key' => '_billing_state',
                    'field_display' => 1,
                    'field_title' => 'State (Billing)',
                    'field_value' => 'State (Billing)',
                ),
                array(
                    'field_key' => '_billing_email',
                    'field_display' => 1,
                    'field_title' => 'Email (Billing)',
                    'field_value' => 'Email (Billing)',
                ),
                array(
                    'field_key' => '_billing_phone',
                    'field_display' => 1,
                    'field_title' => 'Phone (Billing)',
                    'field_value' => 'Phone (Billing)',
                ),
                array(
                    'field_key' => 'shipping_first_name',
                    'field_display' => 1,
                    'field_title' => 'First Name (Shipping)',
                    'field_value' => 'First Name (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_last_name',
                    'field_display' => 1,
                    'field_title' => 'Last Name (Shipping)',
                    'field_value' => 'Last Name (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_company',
                    'field_display' => 1,
                    'field_title' => 'Company (Shipping)',
                    'field_value' => 'Company (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_address_1',
                    'field_display' => 1,
                    'field_title' => 'Address 1 (Shipping)',
                    'field_value' => 'Address 1 (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_address_2',
                    'field_display' => 1,
                    'field_title' => 'Address 2 (Shipping)',
                    'field_value' => 'Address 2 (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_city',
                    'field_display' => 1,
                    'field_title' => 'City (Shipping)',
                    'field_value' => 'City (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_postcode',
                    'field_display' => 1,
                    'field_title' => 'Postcode (Shipping)',
                    'field_value' => 'Postcode (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_state',
                    'field_display' => 1,
                    'field_title' => 'State (Shipping)',
                    'field_value' => 'State (Shipping)',
                ),
                array(
                    'field_key' => '_shipping_country',
                    'field_display' => 1,
                    'field_title' => 'Country (Shipping)',
                    'field_value' => 'Country (Shipping)',
                ),
                array(
                    'field_key' => 'customer_note',
                    'field_display' => 1,
                    'field_title' => 'Customer Note',
                    'field_value' => 'Customer Note',
                ),
                array(
                    'field_key' => '_shipping_method_title',
                    'field_display' => 1,
                    'field_title' => 'Method Title (Shipping)',
                    'field_value' => 'Method Title (Shipping)',
                ),
                array(
                    'field_key' => '_payment_method_title',
                    'field_display' => 1,
                    'field_title' => 'Payment Method Title',
                    'field_value' => 'Payment Method Title',
                ),
                array(
                    'field_key' => '_cart_discount',
                    'field_display' => 1,
                    'field_title' => 'Cart Discount',
                    'field_value' => 'Cart Discount',
                ),
                array(
                    'field_key' => '_order_tax',
                    'field_display' => 1,
                    'field_title' => 'Order Tax',
                    'field_value' => 'Order Tax',
                ),
                array(
                    'field_key' => '_order_shipping_tax',
                    'field_display' => 1,
                    'field_title' => 'Order Tax (Shipping)',
                    'field_value' => 'Order Tax (Shipping)',
                ),
                array(
                    'field_key' => '_order_total',
                    'field_display' => 1,
                    'field_title' => 'Order Total',
                    'field_value' => 'Order Total',
                ),
                array(
                    'field_key' => '_completed_date',
                    'field_display' => 1,
                    'field_title' => 'Completed Date',
                    'field_value' => 'Completed Date',
                ),
                array(
                    'field_key' => 'total_diff_no_product',
                    'field_display' => 1,
                    'field_title' => 'Number of different items',
                    'field_value' => 'Number of different items',
                ),
                array(
                    'field_key' => 'totle_no_of_product',
                    'field_display' => 1,
                    'field_title' => 'Total number of items',
                    'field_value' => 'Total number of items',
                ),
                array(
                    'field_key' => 'order_data_status',
                    'field_display' => 1,
                    'field_title' => 'Status Key',
                    'field_value' => 'Status Key',
                ),
                array(
                    'field_key' => '_payment_method',
                    'field_display' => 1,
                    'field_title' => 'Payment Method',
                    'field_value' => 'Payment Method',
                ),
                array(
                    'field_key' => '_order_discount',
                    'field_display' => 1,
                    'field_title' => 'Order Discount',
                    'field_value' => 'Order Discount',
                ),
                array(
                    'field_key' => '_order_key',
                    'field_display' => 1,
                    'field_title' => 'Order Key',
                    'field_value' => 'Order Key',
                ),
                array(
                    'field_key' => '_order_currency',
                    'field_display' => 1,
                    'field_title' => 'Order Currency',
                    'field_value' => 'Order Currency',
                ),
                array(
                    'field_key' => 'product_data',
                    'field_display' => 1,
                    'field_title' => 'Product Data',
                    'field_value' => 'Product Data',
                ),
                array(
                    'field_key' => 'coupon_data',
                    'field_display' => 1,
                    'field_title' => 'Coupon Data',
                    'field_value' => 'Coupon Data',
                ),
                array(
                    'field_key' => 'shipping_data',
                    'field_display' => 1,
                    'field_title' => 'Shipping Data',
                    'field_value' => 'Shipping Data',
                ),
                array(
                    'field_key' => 'tax_data',
                    'field_display' => 1,
                    'field_title' => 'Tax Data',
                    'field_value' => 'Tax Data',
                ),
                array(
                    'field_key' => 'fee_data',
                    'field_display' => 1,
                    'field_title' => 'Fee Data',
                    'field_value' => 'Fee Data',
                ),
                array(
                    'field_key' => 'order_custom_fields',
                    'field_display' => 1,
                    'field_title' => 'Custom Fields',
                    'field_value' => 'Custom Fields',
                ),
                array(
                    'field_key' => 'refund_data',
                    'field_display' => 1,
                    'field_title' => 'Refund Data',
                    'field_value' => 'Refund Data',
                ),
                array(
                    'field_key' => 'refund_custom_fields',
                    'field_display' => 1,
                    'field_title' => 'Refund Custom Fields',
                    'field_value' => 'Refund Custom Fields',
                )
            )
        );

        return $field_list;
    }

    function wpie_woocommerce_order_number($order_id = 0, $order = array()) {

        $order_number = $order_id;

        if ($order_id != 0) {
            $new_order_number = get_post_meta($order_id, '_wpie_order_number', true);
            if ((int) $new_order_number != 0 && $new_order_number > 0) {
                $order_number = $new_order_number;
            }
        }
        return $order_number;
    }

    function wpie_create_order_csv() {

        global $wpdb;

        $return_value = array();

        $product_export_data = $this->get_order_export_fields_data();

        $order_query_data = $this->wpie_create_order_filter_query($_POST);

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $filename = 'order_' . date('Y_m_d_H_i_s') . '.csv';

        $fh = @fopen(WPIE_UPLOAD_DIR . '/' . $filename, 'w+');

        if (!empty($product_export_data)) {
            foreach ($product_export_data as $new_data) {
                @fputcsv($fh, $new_data, $wpie_export_separator);
            }
        }

        @fclose($fh);

        $new_values = array();

        $new_values['export_log_file_type'] = 'export';
        $new_values['export_log_file_name'] = $filename;
        $new_values['export_log_data'] = 'Order';
        $new_values['create_date'] = current_time('mysql');

        $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
        $new_log_id = $wpdb->insert_id;

        $return_value['wpie_product_category'] = isset($_POST['wpie_product_category']) ? $_POST['wpie_product_category'] : array();
        $return_value['wpie_product_ids'] = isset($_POST['wpie_product_ids']) ? $_POST['wpie_product_ids'] : array();
        $return_value['message'] = 'success';
        $return_value['file_name'] = WPIE_UPLOAD_DIR . '/' . $filename;
        $return_value['status'] = 'pending';
        $return_value['order_query'] = $order_query_data;
        $return_value['order_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";
        $return_value['order_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";


        $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $filename . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                '<div class="wpie-log-delete-action wpie-export-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $filename . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                '</div>';

        $return_value['data'] = array('', $new_values['export_log_file_name'], $new_values['create_date'], $data_action);


        echo json_encode($return_value);

        die();
    }

    function wpie_create_order_filter_query($wpie_data) {

        $order_status = isset($wpie_data['wpie_order_status']) ? $wpie_data['wpie_order_status'] : array();

        $order_product_category = isset($wpie_data['wpie_product_category']) ? $wpie_data['wpie_product_category'] : array();

        $order_product = isset($wpie_data['wpie_product_ids']) ? $wpie_data['wpie_product_ids'] : array();

        $order_ids = isset($wpie_data['wpie_order_ids']) ? $wpie_data['wpie_order_ids'] : array();

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        if ($temp_start_date != "") {
            $temp_start_date = explode('-', $temp_start_date);

            $start_date = $temp_start_date[2] . '-' . $temp_start_date[0] . '-' . $temp_start_date[1];
        }
        if ($temp_end_date != "") {
            $temp_end_date = explode('-', $temp_end_date);

            $end_date = $temp_end_date[2] . '-' . $temp_end_date[0] . '-' . $temp_end_date[1];
        }
        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'shop_order',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC',
        );

        if ($temp_end_date != "" || $temp_start_date != "") {
            $date_data = array();

            if ($temp_end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }
            if ($temp_start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $query_args['date_query'] = array($date_data);
        }
        if (!empty($order_ids)) {
            $query_args['post__in'] = $order_ids;
        }

        if (function_exists('wc_get_order_statuses')) {
            if (!empty($order_status)) {
                $query_args['post_status'] = $order_status;
            } else {
                $query_args['post_status'] = array_keys($this->get_woo_order_status());
            }
        } else {
            if (!empty($order_status)) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'shop_order_status',
                        'field' => 'id',
                        'terms' => $order_status
                    )
                );
            }
        }

        return json_encode($query_args);
    }

    function get_order_export_fields_data() {

        $csv_data = "";

        $count = 0;

        $order_field_list = $this->get_updated_order_fields();

        foreach ($order_field_list['order_field'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }
        return $csv_data;
    }

    function wpie_execute_order_data_query() {

        $query_args = isset($_POST['data_query']) ? json_decode(stripslashes($_POST['data_query']), 1) : "";

        $query_args['fields'] = "ids";

        $order_results = new WP_Query($query_args);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['total_results'] = count($order_results->get_posts());

        echo json_encode($return_value);

        die();
    }

    function wpie_update_order_csv() {

        $file_name = isset($_POST['file_name']) ? stripslashes($_POST['file_name']) : "";

        $order_query = isset($_POST['order_query']) ? stripslashes($_POST['order_query']) : "";

        $start_order = isset($_POST['start_order']) ? $_POST['start_order'] : "";

        $order_offset = isset($_POST['order_offset']) ? $_POST['order_offset'] : 0;

        $order_limit = isset($_POST['order_limit']) ? $_POST['order_limit'] : "";

        $status = isset($_POST['status']) ? $_POST['status'] : "";

        $wpie_product_category = isset($_POST['wpie_product_category']) ? $_POST['wpie_product_category'] : array();

        $wpie_product_ids = isset($_POST['wpie_product_ids']) ? $_POST['wpie_product_ids'] : array();

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $return_value = array();

        $return_value['order_limit'] = $order_limit;


        if ($file_name != "" && file_exists($file_name)) {

            $fh = @fopen($file_name, 'a+');

            $order_list_data = $this->get_filter_order_data($order_query, $start_order, $order_limit, $order_offset, $order_limit, $wpie_product_category, $wpie_product_ids);

            $order_field_list = $this->get_updated_order_fields();

            $return_value['start_order'] = isset($order_list_data['start_order']) ? $order_list_data['start_order'] : 0;

            if (!empty($order_list_data['order_data'])) {

                if ($order_list_data['status'] == "completed" || (isset($order_list_data['order_limit']) && $order_list_data['order_limit'] == 0)) {
                    $return_value['status'] = 'completed';
                } else {
                    $return_value['status'] = 'pending';
                }

                $return_value['order_limit'] = isset($order_list_data['order_limit']) ? $order_list_data['order_limit'] : "";

                foreach ($order_list_data['order_data'] as $order_info) {

                    $data_result = array();

                    foreach ($order_field_list['order_field'] as $field_data) {

                        if ($field_data['field_display'] == 1) {

                            $field_key = $field_data['field_key'];
                            $data_result[] = isset($order_info->$field_key) ? $order_info->$field_key : "";
                        }
                    }

                    @fputcsv($fh, $data_result, $wpie_export_separator);
                }
            } else {

                $return_value['status'] = 'completed';
            }
            @fclose($fh);

            $return_value['message'] = 'success';

            $return_value['file_name'] = $file_name;
        }

        echo json_encode($return_value);

        die();
    }

    function get_filter_order_data($order_query, $start_order, $total_records = 0, $order_offset, $order_limit, $wpie_product_category, $wpie_product_ids) {

        global $wpie_get_record_count;

        if (session_id() == '') {
            session_start();
        }

        $order_data_list = array();

        if ($order_limit != "" && $total_records >= $order_limit) {
            $total_records = $order_limit;
            $order_data_list['status'] = "completed";
        }
        if ($order_offset != "" && $start_order == 0) {
            $start_order = $order_offset;
        }
        if ($total_records == 0) {
            $total_records = $wpie_get_record_count;
        }

        $query_args = json_decode(stripslashes($order_query), 1);

        $query_args['posts_per_page'] = $total_records;

        $query_args['offset'] = $start_order;

        $export_orders = new WP_Query($query_args);

        $order_results = $export_orders->get_posts();

        $order_data = array();

        if (!empty($order_results)) {
            foreach ($order_results as $order_result) {

                if (function_exists('wc_get_order_statuses')) {
                    $order = new WC_Order($order_result);
                } else {
                    $order = new WC_Order();
                    $order->populate($order_result);
                }
                $order_items = $order->get_items(array('line_item', 'coupon', 'shipping', 'tax', 'fee'));

                $order_product = array();

                $order_fee = array();

                $order_tax = array();

                $order_coupon = array();

                $order_shipping = array();

                foreach ($order_items as $key => $value) {
                    if ($value['type'] == 'line_item') {
                        $order_product[] = $value;
                    } else if ($value['type'] == 'coupon') {
                        $order_coupon[] = $value;
                    } else if ($value['type'] == 'shipping') {
                        $order_shipping[] = $value;
                    } else if ($value['type'] == 'tax') {
                        $order_tax[] = $value;
                    } else if ($value['type'] == 'fee') {
                        $order_fee[] = $value;
                    }
                }
                $order->product_data = $order_product;
                $order->coupon_data = !empty($order_coupon) ? str_replace(',', "||", json_encode($order_coupon)) : "";
                $order->shipping_data = !empty($order_shipping) ? str_replace(',', "||", json_encode($order_shipping)) : "";
                $order->tax_data = !empty($order_tax) ? str_replace(',', "||", json_encode($order_tax)) : "";
                $order->fee_data = !empty($order_fee) ? str_replace(',', "||", json_encode($order_fee)) : "";

                $order_custom_fields = get_post_meta($order->id);

                if (!isset($order->order_custom_fields)) {
                    $order->order_custom_fields = !empty($order_custom_fields) ? str_replace(',', "||", json_encode($order_custom_fields)) : "";
                }

                foreach ($order_custom_fields as $key => $value) {
                    $order->$key = $value[0];
                }
                $order->_shipping_method_title = $order->get_shipping_method();

                $shop_order_status = $this->get_woo_order_status();

                if (function_exists('wc_get_order_statuses')) {
                    $order->order_final_status = $shop_order_status[$order->post_status];

                    $order->order_data_status = $order->post_status;
                } else {
                    $order->order_final_status = $order->status;

                    $order->order_data_status = $order->status;
                }

                //unset( $order->order_custom_fields );
                // search product filter

                $filter_flag = 1;

                if (!empty($wpie_product_ids)) {
                    $filter_flag = 0;
                    foreach ($order_product as $new_product) {
                        if (in_array($new_product['product_id'], $wpie_product_ids)) {
                            $filter_flag = 1;
                            break;
                        }
                    }
                }
                // se3arch product category filter
                if (!empty($wpie_product_category) && $filter_flag == 1) {
                    $filter_flag = 0;
                    foreach ($order_product as $new_product) {

                        $cat_list = wp_get_post_terms($new_product['product_id'], 'product_cat', array('fields' => 'ids'));
                        if (!empty($cat_list)) {
                            foreach ($cat_list as $product_cat) {
                                if (in_array($product_cat, $wpie_product_category)) {
                                    $filter_flag = 1;
                                    break;
                                }
                            }
                        }
                    }
                }


                if ($filter_flag == 1) {
                    $total_diff_no_product = 0;

                    $totle_no_of_product = 0;

                    $final_product_list = array();

                    foreach ($order_product as $product_data) {
                        $totle_no_of_product += $product_data['qty'];
                        $total_diff_no_product++;
                        $product_data['_sku'] = get_post_meta($product_data['product_id'], '_sku', true);
                        $final_product_list[] = $product_data;
                    }
                    $order->product_data = !empty($final_product_list) ? str_replace(',', "||", json_encode($final_product_list)) : "";

                    $order->total_diff_no_product = $total_diff_no_product;

                    $order->totle_no_of_product = $totle_no_of_product;

                    $child_post = get_posts(array('post_type' => 'shop_order_refund', 'post_status' => 'any', 'post_parent' => $order->id));

                    if ($child_post) {
                        $order->refund_data = str_replace(',', "||", json_encode($child_post));

                        $order->refund_custom_fields = str_replace(',', "||", json_encode(get_post_meta($child_post[0]->ID)));
                    } else {
                        $order->refund_data = "";

                        $order->refund_custom_fields = "";
                    }

                    $order_data_list['order_data'][] = $order;
                }


                $start_order++;

                $order_data_list['start_order'] = $start_order;
            }
        }

        return $order_data_list;
    }

    function get_coupon_amount($order_id, $coupon) {
        global $wpdb;

        $coupon_query = '
			SELECT meta_value
				FROM ' . $wpdb->prefix . 'woocommerce_order_items woi
				LEFT JOIN ' . $wpdb->prefix . 'woocommerce_order_itemmeta woim
					ON woi.order_item_id = woim.order_item_id
			WHERE 
				order_item_type = "coupon"
				AND order_id =' . $order_id . '
				AND order_item_name="%s"
				AND meta_key="discount_amount"
		';

        $coupon_results = $wpdb->get_results($wpdb->prepare($coupon_query, $coupon));

        if (isset($coupon_results[0]))
            return round($coupon_results[0]->meta_value, 2);
        else
            return 0;
    }

    function wpie_save_order_fields() {

        $old_order_fields = $this->get_updated_order_fields();

        $new_fields = array();

        foreach ($old_order_fields as $order_fields_key => $order_fields_data) {

            foreach ($order_fields_data as $key => $value) {

                $new_fields[$order_fields_key][$key]['field_key'] = $value['field_key'];

                $new_fields[$order_fields_key][$key]['field_display'] = isset($_POST['wpie_' . $value['field_key'] . '_field_check']) ? $_POST['wpie_' . $value['field_key'] . '_field_check'] : "";

                $new_fields[$order_fields_key][$key]['field_title'] = $value['field_title'];

                $new_fields[$order_fields_key][$key]['field_value'] = $value['field_title']; //isset($_POST['wpie_' . $value['field_key'] . '_field']) ? $_POST['wpie_' . $value['field_key'] . '_field'] : "";
            }
        }

        $new_fields_data = maybe_serialize($new_fields);

        update_option('wpie_order_fields', $new_fields_data);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['message_content'] = __('Changes Saved Successfully.', WPIE_TEXTDOMAIN);

        $return_value['preview_fields'] = $this->get_order_preview_fields();

        echo json_encode($return_value);

        die();
    }

    function get_order_preview_fields() {

        $order_fields = $this->get_updated_order_fields();

        $preview_fields_data = '<table class="wpie-product-filter-data wpie-datatable table table-striped table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>';

        foreach ($order_fields as $new_order_fields) {
            foreach ($new_order_fields as $order_fields_data)
                if ($order_fields_data['field_display'] == 1) {
                    $preview_fields_data .= '<th>' . $order_fields_data['field_title'] . '</th>';
                }
        }

        $preview_fields_data .="   </tr>

                </thead>
            </table>";

        return $preview_fields_data;
    }

    function wpie_create_order_preview() {

        $return_value = array();

        $order_query_data = $this->wpie_create_order_filter_query($_POST);

        $return_value['message'] = 'success';

        $return_value['order_query'] = $order_query_data;

        $return_value['order_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";

        $return_value['order_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $return_value['total_order'] = $this->get_order_total_records_count(json_decode($order_query_data, 1));

        echo json_encode($return_value);

        die();
    }

    function get_order_total_records_count($query_args) {

        $query_args['fields'] = "ids";

        $order_results = new WP_Query($query_args);

        return count($order_results->get_posts());
    }

    function wpie_get_order_export_preview() {

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $data_query = isset($_POST['order_query']) ? $_POST['order_query'] : "";

        $total_order = isset($_POST['total_order']) ? $_POST['total_order'] : 0;

        $order_limit = isset($_POST['order_limit']) ? $_POST['order_limit'] : 0;

        $order_limit = $order_limit - $record_offset;

        $order_offset = isset($_POST['order_offset']) ? $_POST['order_offset'] : 0;

        $product_list_data = $this->get_filter_order_data($data_query, $record_offset, $record_limit, $order_offset, $order_limit);

        $final_data = array();

        $order_field_list = $this->get_updated_order_fields();

        foreach ($product_list_data['order_data'] as $order_info) {

            $data_result = array();

            foreach ($order_field_list['order_field'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $temp_data = $field_data['field_key'];

                    $data_result[] = isset($order_info->$temp_data) ? $order_info->$temp_data : "";
                    ;
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_order;

        $return_value['recordsTotal'] = $total_order;

        echo json_encode($return_value);

        die();
    }

    function wpie_import_order() {

        global $wpdb;

        $return_value = array();

        $return_value['message'] = 'error';

        $file_url = isset($_POST['wpie_import_file_url']) ? $_POST['wpie_import_file_url'] : "";

        $file_path_data = isset($_POST['wpie_csv_upload_file']) ? $_POST['wpie_csv_upload_file'] : "";

        $wpie_import_determinator = (isset($_POST['wpie_import_determinator']) || trim($_POST['wpie_import_determinator']) != "") ? $_POST['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($_POST['wpie_data_update_option']) ? $_POST['wpie_data_update_option'] : "order_key";

        $process_status = isset($_POST['status']) ? $_POST['status'] : "pending";

        $order_field_list = $this->get_updated_order_fields();

        if (session_id() == '') {
            session_start();
        }

        if ($process_status == "start") {

            $_SESSION['order_imported_ids'] = array();

            $_SESSION['order_old_new_ids'] = array();

            $_SESSION['order_old_new_id_list'] = array();
        }

        $file_path = "";

        if ($file_path_data != "") {
            $file_path = $file_path_data;
        } else if ($file_url != "") {
            $file_path = $file_url;
        }

        if ($file_path != "") {

            if ($process_status == "start") {

                $new_file = pathinfo($file_path);

                $new_values = array();

                $new_values['export_log_file_type'] = 'import';
                $new_values['export_log_file_name'] = $new_file['basename'];
                $new_values['export_log_data'] = 'Order';
                $new_values['create_date'] = current_time('mysql');

                $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
                $new_log_id = $wpdb->insert_id;

                $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $new_values['export_log_file_name'] . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                        '<div class="wpie-log-delete-action wpie-import-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $new_values['export_log_file_name'] . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                        '</div>';
                $return_value['data'] = array('', substr($new_values['export_log_file_name'], 11), $new_values['create_date'], $data_action);
            }

            if ($process_status == "error" || $process_status == "start") {
                $process_status = "pending";
            }

            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($fh !== FALSE) {

                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'Order Key') {
                                $csv_temp_count++;
                                break;
                            }
                        }
                        if ($csv_temp_count == 0) {
                            break;
                        }
                    }

                    $import_data_temp[] = $new_line;

                    $csv_temp_count++;
                }
                if ($csv_temp_count == 0) {
                    if ($wpie_import_determinator == ",") {
                        $wpie_import_determinator = ";";
                    } else {
                        $wpie_import_determinator = ",";
                    }
                    @rewind($fh);
                    while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {

                        if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                            foreach ($new_line as $csv_new_column) {
                                if ($csv_new_column == 'Id' || $csv_new_column == 'Order Key') {
                                    $csv_temp_count++;
                                    break;
                                }
                            }
                            if ($csv_temp_count == 0) {
                                break;
                            }
                        }
                        $import_data_temp[] = $new_line;

                        $csv_temp_count++;
                    }
                }
                fclose($fh);

                $fields_title_array = $import_data_temp[0];

                unset($import_data_temp[0]);

                $count = 0;
                $total_records = count($import_data_temp);


                foreach ($import_data_temp as $data) {
                    $temp_count = 0;
                    foreach ($data as $key => $value) {
                        $temp_key = $fields_title_array[$temp_count];
                        $import_data[$count][$temp_key] = $value;
                        $temp_count++;
                    }
                    if (!isset($import_data[0]['Id'])) {

                        if (isset($import_data[0]['Order Key'])) {
                            $import_data[$count]['Id'] = $count+999999;
                        } else {
                            $import_data = array();
                            $return_value['status'] = "error_completed";
                            $return_value['message'] = "success";
                            $return_value['message_text'] = __('File or Seprator is invalid.', WPIE_TEXTDOMAIN);
                            echo json_encode($return_value);
                            die();
                        }
                    }
                    $count++;
                }

                if ($total_records <= @count($_SESSION['order_old_new_ids'])) {
                    $return_value['status'] = "completed";
                    $return_value['message_text'] = __('Data Successfully Imported', WPIE_TEXTDOMAIN);
                } else {
                    $return_value['status'] = "pending";
                }
                $return_value['total_records'] = $total_records;

                $return_value['message'] = 'success';
            } else {

                $return_value['message_text'] = __('Could not open file.', WPIE_TEXTDOMAIN);
            }
            if (!empty($import_data)) {

                $wpie_order_create_method = isset($_POST['wpie_order_create_method']) ? $_POST['wpie_order_create_method'] : "";
                $import_type = 'normal';
                $order_updated_data = $this->wpie_create_new_order($import_data, $wpie_order_create_method, $import_type, $wpie_data_update_option);
                $return_value['order_offset'] = @count($_SESSION['order_old_new_ids']);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wc_get_order_id_by_order_key($order_key = 0) {
        global $wpdb;

        $order_id = $wpdb->get_var($wpdb->prepare("SELECT post_id FROM {$wpdb->prefix}postmeta WHERE meta_key = '_order_key' AND meta_value = %s", $order_key));

        return $order_id;
    }

    function wpie_create_new_order($order_data = array(), $wpie_order_create_method = "", $import_type = 'normal', $wpie_data_update_option = "order_key") {

        if (session_id() == '') {
            session_start();
        }

        $order_field_list = $this->get_updated_order_fields();

        foreach ($order_data as $order_info) {

            $duplicate_order_id = "";

            if (isset($order_info['Id'])) {

                $order_info['Id'] = intval($order_info['Id']);

                $old_order_id = $order_info['Id'];

                if (isset($_SESSION['order_old_new_ids'][$old_order_id]) && $_SESSION['order_old_new_ids'][$old_order_id] != "") {
                    continue;
                }
                if (isset($_SESSION['order_old_new_id_list'][$old_order_id]) && $_SESSION['order_old_new_id_list'][$old_order_id] != "") {
                    $duplicate_order_id == $_SESSION['order_old_new_id_list'][$old_order_id];
                }

                if ($duplicate_order_id == "" && $wpie_data_update_option == "order_id") {

                    if (get_post_status($old_order_id) === false) {
                        
                    } else {
                        $duplicate_order_id = $old_order_id;
                    }
                }
            }

            if (isset($order_info['Order Key']) && $order_info['Order Key'] != "") {
                $duplicate_order_id = $this->wc_get_order_id_by_order_key($order_info['Order Key']);
            }

            if ($duplicate_order_id != "" && $wpie_order_create_method == 'skip_order') {
                if ($import_type == 'normal') {
                    $_SESSION['order_imported_ids'][] = $duplicate_order_id;
                }
                if (isset($order_info['Id'])) {
                    $old_temp_order_id = $order_info['Id'];
                    $_SESSION['order_old_new_ids'][$old_temp_order_id] = $duplicate_order_id;

                    $_SESSION['order_old_new_id_list'][$old_temp_order_id] = $duplicate_order_id;
                }
                continue;
            }

            $new_order_statuts = "";

            if (isset($order_info['Status Key']) && $order_info['Status Key'] != "") {

                $new_order_statuts = $order_info['Status Key'];

                if (function_exists('wc_get_order_statuses')) {
                    if ('wc-' !== substr($new_order_statuts, 0, 3)) {
                        $new_order_statuts = 'wc-' . $new_order_statuts;
                    }
                } else {
                    if ('wc-' === substr($new_order_statuts, 0, 3)) {
                        $new_order_statuts = substr($new_order_statuts, 3);
                    }
                }
            }

            if ($duplicate_order_id != "" && $wpie_order_create_method == 'update_order') {


                $order_data = array();

                if (isset($order_info['Order Date']) || $order_info['Order Date'] != "") {
                    $order_data['post_date'] = date('Y-m-d G:i:s', strtotime($order_info['Order Date']));
                }

                if ($new_order_statuts != "" && function_exists('wc_get_order_statuses')) {
                    $order_data['post_status'] = $new_order_statuts;
                }

                $order_data['ID'] = $duplicate_order_id;

                $order_id = wp_update_post($order_data, false);

                $new_order = new WC_Order($order_id);

                $order_item_old_array = array();

                if (isset($order_info['Product Data']) && $order_info['Product Data'] != "") {
                    $order_item_old_array[] = 'line_item';
                }
                if (isset($order_info['Coupon Data']) && $order_info['Coupon Data'] != "") {
                    $order_item_old_array[] = 'coupon';
                }
                if (isset($order_info['Shipping Data']) && $order_info['Shipping Data'] != "") {
                    $order_item_old_array[] = 'shipping';
                }
                if (isset($order_info['Tax Data']) && $order_info['Tax Data'] != "") {
                    $order_item_old_array[] = 'tax';
                }
                if (isset($order_info['Fee Data']) && $order_info['Fee Data'] != "") {
                    $order_item_old_array[] = 'fee';
                }

                if (!empty($order_item_old_array)) {
                    $old_order_items = $new_order->get_items($order_item_old_array);

                    if (!empty($old_order_items)) {
                        foreach ($old_order_items as $order_item_id => $item_value) {
                            @wc_delete_order_item($order_item_id);
                        }
                    }
                }
            } else {


                $order_data = array();

                if ((!isset($order_info['Order Date'])) || $order_info['Order Date'] == "") {
                    $order_info['Order Date'] = date('Y-m-d G:i:s');
                }

                $order_info['Order Date'] = date('Y-m-d G:i:s', strtotime($order_info['Order Date']));

                $order_data['post_name'] = 'order-' . $order_info['Order Date'];

                $order_data['post_name'] = 'Order &ndash;' . $order_info['Order Date'];

                $order_data['post_type'] = 'shop_order';

                $order_data['post_status'] = 'publish';

                $order_data['ping_status'] = 'closed';

                $order_data['post_date'] = $order_info['Order Date'];

                $first_name_billing = "";

                $last_name_billing = "";

                if (isset($order_info['First Name (Billing)']) && $order_info['First Name (Billing)'] != "") {
                    $first_name_billing = $order_info['First Name (Billing)'];
                }
                if (isset($order_info['Last Name (Billing)']) && $order_info['Last Name (Billing)'] != "") {
                    $last_name_billing = $order_info['Last Name (Billing)'];
                }

                if ($new_order_statuts != "" && function_exists('wc_get_order_statuses')) {
                    $order_data['post_status'] = $new_order_statuts;
                }

                $order_id = wp_insert_post($order_data, true);

                $new_order = new WC_Order($order_id);
            }

            if ($import_type == 'normal') {
                $_SESSION['order_imported_ids'][] = $order_id;
            }

            if ($new_order_statuts != "" && !function_exists('wc_get_order_statuses')) {

                $new_order->update_status($new_order_statuts);
            }

            update_post_meta($order_id, '_wpie_order_number', $order_info['Id']);

            if (isset($order_info['Custom Fields']) && ($order_other_data['Custom Fields'] != "")) {

                $order_custom_fields = json_decode(str_replace("||", ',', $order_info['Custom Fields']));
                if (is_array($order_custom_fields) && !empty($order_custom_fields)) {
                    foreach ($order_custom_fields as $key => $value) {
                        update_post_meta($order_id, $key, $value[0]);
                    }
                }
            }

            if ($duplicate_order_id > 0 && $wpie_order_create_method == 'create_order') {
                update_post_meta($order_id, '_order_key', 'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_')));
            } else {
                update_post_meta($order_id, '_order_key', $order_info['Order Key']);
            }



            $order_fields_title = array();

            foreach ($order_field_list['order_field'] as $field_data) {
                $order_fields_title[] = $field_data['field_title'];
            }

            foreach ($order_info as $key => $value) {
                if (!in_array($key, $order_fields_title)) {
                    update_post_meta($order_id, $key, $value);
                }
            }


            $include_array = array('_billing_first_name' => 'First Name (Billing)', '_billing_last_name' => 'Last Name (Billing)', '_billing_company' => 'Company (Billing)', '_billing_address_1' => 'Address 1 (Billing)', '_billing_address_2' => 'Address 2 (Billing)', '_billing_city' => 'City (Billing)',
                '_billing_postcode' => 'Postcode (Billing)', '_billing_country' => 'Country (Billing)', '_billing_state' => 'State (Billing)', '_billing_email' => 'Email (Billing)', '_billing_phone' => 'Phone (Billing)', '_shipping_first_name' => 'First Name (Shipping)',
                '_shipping_last_name' => 'Last Name (Shipping)', '_shipping_company' => 'Company (Shipping)', '_shipping_address_1' => 'Address 1 (Shipping)', '_shipping_address_2' => 'Address 2 (Shipping)', '_shipping_city' => 'City (Shipping)', '_shipping_postcode' => 'Postcode (Shipping)',
                '_shipping_state' => 'State (Shipping)', '_shipping_country' => 'Country (Shipping)', 'customer_note' => 'Customer Note', '_shipping_method_title' => 'Method Title (Shipping)', '_payment_method_title' => 'Payment Method Title', '_cart_discount' => 'Cart Discount', '_order_tax' => 'Order Tax', '_order_shipping_tax' => 'Order Tax (Shipping)', '_order_total' => 'Order Total', '_completed_date' => 'Completed Date', '_payment_method' => 'Payment Method', '_order_discount' => 'Order Discount', '_order_currency' => 'Order Currency'
            );

            foreach ($include_array as $key => $value) {
                if (isset($order_info[$value])) {
                    update_post_meta($order_id, $key, $order_info[$value]);
                }
            }

            if (isset($order_info['Email (Billing)']) && $order_info['Email (Billing)'] != "") {
                $customer_user = get_user_by('email', $order_info['Email (Billing)']);
                if ($customer_user) {
                    update_post_meta($order_id, '_customer_user', $customer_user->ID);
                }
            }

            if (isset($order_info['Refund Data']) && $order_info['Refund Data'] != "") {

                $order_refund_data = json_decode(str_replace("||", ',', $order_info['Refund Data']));
                $refund_data = $order_refund_data[0];
                $shop_order_refund = array(
                    'post_name' => $refund_data->post_name,
                    'post_type' => $refund_data->post_type,
                    'post_title' => $refund_data->post_title,
                    'post_status' => 'publish',
                    'ping_status' => $refund_data->ping_status,
                    'post_excerpt' => $refund_data->post_excerpt,
                    'comment_status' => $refund_data->comment_status,
                    'post_password' => $refund_data->post_password,
                    'post_parent' => $order_id,
                    'post_date' => $refund_data->post_date
                );

                if (function_exists('wc_get_order_statuses')) {
                    $shop_order_refund['post_status'] = $refund_data->post_status;
                }

                if ($duplicate_order_id > 0 && $wpie_order_create_method == 'update_order') {
                    $child_post = get_posts(array('post_type' => 'shop_order_refund', 'post_status' => 'any', 'post_parent' => $order->id, 'fields' => 'ids'));

                    if (!empty($child_post) && isset($child_post[0]) && $child_post[0] > 0) {

                        $shop_order_refund['ID'] = $child_post[0];

                        $shop_order_refund_id = wp_update_post($shop_order_refund, false);
                    } else {
                        $shop_order_refund_id = wp_insert_post($shop_order_refund, true);
                    }
                } else {
                    $shop_order_refund_id = wp_insert_post($shop_order_refund, true);
                }

                if (isset($order_info['Refund Custom Fields']) && $order_info['Refund Custom Fields'] != "") {
                    $order_refund_meta = json_decode(str_replace("||", ',', $order_info['Refund Custom Fields']));
                    if (is_array($order_refund_meta) && !empty($order_refund_meta)) {
                        foreach ($order_refund_meta as $key => $value) {
                            update_post_meta($shop_order_refund_id, $key, $value[0]);
                        }
                    }
                }
            }

            if (isset($order_info['Product Data']) && $order_info['Product Data'] != "") {
                $order_product_data = json_decode(str_replace("||", ',', $order_info['Product Data']), 1);
                if (is_array($order_product_data) && !empty($order_product_data)) {

                    foreach ($order_product_data as $order_item) {

                        $order_item_id = wc_add_order_item($order_id, array(
                            'order_item_name' => $order_item['name'],
                            'order_item_type' => $order_item['type']
                        ));

                        if ($order_item_id && is_array($order_item['item_meta']) && !empty($order_item['item_meta'])) {

                            $product_id_by_sku = "";

                            if (isset($order_item['_sku']) && !$order_item['_sku'] = "") {
                                $product_id_by_sku = $this->wc_get_product_id_by_sku($order_item['_sku']);
                            }
                            if ($product_id_by_sku) {
                                if (function_exists('get_product')) {
                                    $product = @get_product($product_id_by_sku);
                                } else {
                                    $product = new WC_product($product_id_by_sku);
                                }

                                if ($product) {
                                    if (isset($order_item['item_meta']['_product_id'])) {
                                        $order_item['item_meta']['_product_id'][0] = $product->id;
                                    }
                                    if (isset($order_item['item_meta']['_variation_id'])) {
                                        $order_item['item_meta']['_variation_id'][0] = isset($product->variation_id) ? $product->variation_id : 0;
                                    }

                                    if ($product->backorders_require_notification() && $product->is_on_backorder($order_item['item_meta']['_qty'][0])) {
                                        wc_update_order_item_meta($product_item_id, apply_filters('woocommerce_backordered_item_meta_name', __('Backordered', WPIE_TEXTDOMAIN)), $product_qty - max(0, $product->get_total_stock()));
                                    }

                                    if ($product->is_downloadable()) {
                                        $download_files = $product->get_files();

                                        foreach ($download_files as $download_id => $file) {
                                            @wc_downlodable_file_permission($download_id, $product->id, new WC_Order($order_id));
                                        }
                                    }
                                }
                            }

                            foreach ($order_item['item_meta'] as $order_item_meta_key => $order_item_meta_value) {
                                wc_update_order_item_meta($order_item_id, $order_item_meta_key, $order_item_meta_value[0]);
                            }
                        }
                    }
                }
            }
            if (isset($order_info['Coupon Data']) && $order_info['Coupon Data'] != "") {
                $order_coupon_data = json_decode(str_replace("||", ',', $order_info['Coupon Data']), 1);
                if (is_array($order_coupon_data) && !empty($order_coupon_data)) {

                    foreach ($order_coupon_data as $order_item) {

                        $order_item_id = wc_add_order_item($order_id, array(
                            'order_item_name' => $order_item['name'],
                            'order_item_type' => $order_item['type']
                        ));
                        if ($order_item_id) {
                            foreach ($order_item['item_meta'] as $order_item_meta_key => $order_item_meta_value) {
                                wc_update_order_item_meta($order_item_id, $order_item_meta_key, $order_item_meta_value[0]);
                            }
                        }
                    }
                }
            }
            if (isset($order_info['Shipping Data']) && $order_info['Shipping Data'] != "") {
                $order_shipping_data = json_decode(str_replace("||", ',', $order_info['Shipping Data']), 1);
                if (is_array($order_shipping_data) && !empty($order_shipping_data)) {

                    foreach ($order_shipping_data as $order_item) {

                        $order_item_id = wc_add_order_item($order_id, array(
                            'order_item_name' => $order_item['name'],
                            'order_item_type' => $order_item['type']
                        ));
                        if ($order_item_id) {
                            foreach ($order_item['item_meta'] as $order_item_meta_key => $order_item_meta_value) {
                                wc_update_order_item_meta($order_item_id, $order_item_meta_key, $order_item_meta_value[0]);
                            }
                        }
                    }
                }
            }
            if (isset($order_info['Tax Data']) && $order_info['Tax Data'] != "") {
                $order_tax_data = json_decode(str_replace("||", ',', $order_info['Tax Data']), 1);
                if (is_array($order_tax_data) && !empty($order_tax_data)) {

                    foreach ($order_tax_data as $order_item) {

                        $order_item_id = wc_add_order_item($order_id, array(
                            'order_item_name' => $order_item['name'],
                            'order_item_type' => $order_item['type']
                        ));
                        if ($order_item_id) {
                            foreach ($order_item['item_meta'] as $order_item_meta_key => $order_item_meta_value) {
                                wc_update_order_item_meta($order_item_id, $order_item_meta_key, $order_item_meta_value[0]);
                            }
                        }
                    }
                }
            }
            if (isset($order_info['Fee Data']) && $order_info['Fee Data'] != "") {
                $order_fee_data = json_decode(str_replace("||", ',', $order_info['Fee Data']), 1);
                if (is_array($order_fee_data) && !empty($order_fee_data)) {
                    foreach ($order_fee_data as $order_item) {

                        $order_item_id = wc_add_order_item($order_id, array(
                            'order_item_name' => $order_item['name'],
                            'order_item_type' => $order_item['type']
                        ));
                        if ($order_item_id) {
                            foreach ($order_item['item_meta'] as $order_item_meta_key => $order_item_meta_value) {
                                wc_update_order_item_meta($order_item_id, $order_item_meta_key, $order_item_meta_value[0]);
                            }
                        }
                    }
                }
            }

            if (isset($order_info['Id'])) {

                $old_temp_order_id = $order_info['Id'];
                $_SESSION['order_old_new_ids'][$old_temp_order_id] = $order_id;
            }

            wc_delete_shop_order_transients($order_id);

            do_action('woocommerce_new_order', $order_id);
        }

        return "";
    }

    function wc_get_product_id_by_sku($sku) {
        global $wpdb;

        $product_id = $wpdb->get_var($wpdb->prepare("
			SELECT posts.ID
			FROM $wpdb->posts AS posts
			LEFT JOIN $wpdb->postmeta AS postmeta ON ( posts.ID = postmeta.post_id )
			WHERE posts.post_type IN ( 'product', 'product_variation' )
			AND postmeta.meta_key = '_sku' AND postmeta.meta_value = '%s'
			LIMIT 1
		 ", $sku));

        return ( $product_id ) ? intval($product_id) : 0;
    }

    function wpie_get_order_import_preview() {

        if (session_id() == '') {
            session_start();
        }
        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $query_args = array(
            'post_type' => 'shop_order',
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post_status' => 'publish',
            'post__in' => $_SESSION['order_imported_ids']
        );

        if (function_exists('wc_get_order_statuses')) {
            if (!empty($order_status)) {
                $query_args['post_status'] = $order_status;
            } else {
                $query_args['post_status'] = array_keys($this->get_woo_order_status());
            }
        } else {
            if (!empty($order_status)) {
                $query_args['tax_query'] = array(
                    array(
                        'taxonomy' => 'shop_order_status',
                        'field' => 'id',
                        'terms' => $order_status
                    )
                );
            }
        }
        $total_order = count($_SESSION['order_imported_ids']);

        $data_query = addslashes(json_encode($query_args));


        $product_list_data = $this->get_filter_order_data($data_query, $record_offset, $record_limit, 0, 0, array(), array());

        $final_data = array();

        $order_field_list = $this->get_updated_order_fields();

        foreach ($product_list_data['order_data'] as $order_info) {

            $data_result = array();

            foreach ($order_field_list['order_field'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $temp_data = $field_data['field_key'];

                    $data_result[] = isset($order_info->$temp_data) ? $order_info->$temp_data : "";
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_order;

        $return_value['recordsTotal'] = $total_order;

        echo json_encode($return_value);

        die();
    }

    function wpie_get_order_export_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where (`export_log_file_type` = 'csv' or `export_log_file_type` = 'export') and `export_log_data`='Order' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_get_order_import_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where `export_log_file_type` = 'import' and `export_log_data`='Order' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function get_order_export_data($wpie_data = array()) {
        $csv_data = "";

        $order_field_list = $this->get_updated_order_fields();

        $order_list_data = $this->get_scheduled_order_data($wpie_data);

        $count = 0;

        foreach ($order_field_list['order_field'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        foreach ($order_list_data as $order_info) {
            $count++;

            $data_result = array();

            foreach ($order_field_list['order_field'] as $field_data) {


                if ($field_data['field_display'] == 1) {
                    $temp_data = $field_data['field_key'];
                    $data_result[] = isset($order_info->$temp_data) ? $order_info->$temp_data : "";
                }
            }

            $csv_data[$count] = $data_result;
        }

        return $csv_data;
    }

    function get_scheduled_order_data($wpie_data) {

        global $wpdb;

        $product_export_data = $this->get_order_export_fields_data();

        $order_query_data = $this->wpie_create_order_filter_query($wpie_data);

        $wpie_export_separator = (isset($wpie_data['wpie_export_separator']) && $wpie_data['wpie_export_separator'] != "") ? $wpie_data['wpie_export_separator'] : ",";

        $filename = 'order_' . date('Y_m_d_H_i_s') . '.csv';

        $fh = @fopen(WPIE_UPLOAD_DIR . '/' . $filename, 'w+');

        if (!empty($product_export_data)) {
            foreach ($product_export_data as $new_data) {
                @fputcsv($fh, $new_data, $wpie_export_separator);
            }
        }

        $order_list_data = $this->get_filter_order_data_scheduled($order_query_data);

        $order_field_list = $this->get_updated_order_fields();


        if (!empty($order_list_data['order_data'])) {

            foreach ($order_list_data['order_data'] as $order_info) {

                $data_result = array();

                foreach ($order_field_list['order_field'] as $field_data) {

                    if ($field_data['field_display'] == 1) {

                        $field_key = $field_data['field_key'];
                        $data_result[] = isset($order_info->$field_key) ? $order_info->$field_key : "";
                    }
                }

                @fputcsv($fh, $data_result, $wpie_export_separator);
            }
        }

        @fclose($fh);

        $new_values = array();

        $new_values['export_log_file_type'] = 'export';
        $new_values['export_log_file_name'] = $filename;
        $new_values['export_log_data'] = 'Order';
        $new_values['create_date'] = current_time('mysql');

        $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
    }

    function get_filter_order_data_scheduled($order_query) {

        $order_data_list = array();

        $query_args = json_decode(stripslashes($order_query), 1);

        $export_orders = new WP_Query($query_args);

        $order_results = $export_orders->get_posts();

        $order_data = array();

        if (!empty($order_results)) {
            foreach ($order_results as $order_result) {

                if (function_exists('wc_get_order_statuses')) {
                    $order = new WC_Order($order_result);
                } else {
                    $order = new WC_Order();
                    $order->populate($order_result);
                }
                $order_items = $order->get_items(array('line_item', 'coupon', 'shipping', 'tax', 'fee'));

                $order_product = array();

                $order_fee = array();

                $order_tax = array();

                $order_coupon = array();

                $order_shipping = array();

                foreach ($order_items as $key => $value) {
                    if ($value['type'] == 'line_item') {
                        $order_product[] = $value;
                    } else if ($value['type'] == 'coupon') {
                        $order_coupon[] = $value;
                    } else if ($value['type'] == 'shipping') {
                        $order_shipping[] = $value;
                    } else if ($value['type'] == 'tax') {
                        $order_tax[] = $value;
                    } else if ($value['type'] == 'fee') {
                        $order_fee[] = $value;
                    }
                }
                $order->product_data = $order_product;
                $order->coupon_data = !empty($order_coupon) ? str_replace(',', "||", json_encode($order_coupon)) : "";
                $order->shipping_data = !empty($order_shipping) ? str_replace(',', "||", json_encode($order_shipping)) : "";
                $order->tax_data = !empty($order_tax) ? str_replace(',', "||", json_encode($order_tax)) : "";
                $order->fee_data = !empty($order_fee) ? str_replace(',', "||", json_encode($order_fee)) : "";

                $order_custom_fields = get_post_meta($order->id);

                if (!isset($order->order_custom_fields)) {
                    $order->order_custom_fields = !empty($order_custom_fields) ? str_replace(',', "||", json_encode($order_custom_fields)) : "";
                }

                foreach ($order_custom_fields as $key => $value) {
                    $order->$key = $value[0];
                }
                $order->_shipping_method_title = $order->get_shipping_method();

                $shop_order_status = $this->get_woo_order_status();

                if (function_exists('wc_get_order_statuses')) {
                    $order->order_final_status = $shop_order_status[$order->post_status];

                    $order->order_data_status = $order->post_status;
                } else {
                    $order->order_final_status = $order->status;

                    $order->order_data_status = $order->status;
                }

                //unset( $order->order_custom_fields );
                // search product filter

                $filter_flag = 1;

                if (!empty($wpie_product_ids)) {
                    $filter_flag = 0;
                    foreach ($order_product as $new_product) {
                        if (in_array($new_product['product_id'], $wpie_product_ids)) {
                            $filter_flag = 1;
                            break;
                        }
                    }
                }
                // se3arch product category filter
                if (!empty($wpie_product_category) && $filter_flag == 1) {
                    $filter_flag = 0;
                    foreach ($order_product as $new_product) {

                        $cat_list = wp_get_post_terms($new_product['product_id'], 'product_cat', array('fields' => 'ids'));
                        if (!empty($cat_list)) {
                            foreach ($cat_list as $product_cat) {
                                if (in_array($product_cat, $wpie_product_category)) {
                                    $filter_flag = 1;
                                    break;
                                }
                            }
                        }
                    }
                }


                if ($filter_flag == 1) {
                    $total_diff_no_product = 0;

                    $totle_no_of_product = 0;

                    $final_product_list = array();

                    foreach ($order_product as $product_data) {
                        $totle_no_of_product += $product_data['qty'];
                        $total_diff_no_product++;
                        $product_data['_sku'] = get_post_meta($product_data['product_id'], '_sku', true);
                        $final_product_list[] = $product_data;
                    }
                    $order->product_data = !empty($final_product_list) ? str_replace(',', "||", json_encode($final_product_list)) : "";

                    $order->total_diff_no_product = $total_diff_no_product;

                    $order->totle_no_of_product = $totle_no_of_product;

                    $child_post = get_posts(array('post_type' => 'shop_order_refund', 'post_status' => 'any', 'post_parent' => $order->id));

                    if ($child_post) {
                        $order->refund_data = str_replace(',', "||", json_encode($child_post));

                        $order->refund_custom_fields = str_replace(',', "||", json_encode(get_post_meta($child_post[0]->ID)));
                    } else {
                        $order->refund_data = "";

                        $order->refund_custom_fields = "";
                    }

                    $order_data_list['order_data'][] = $order;
                }
            }
        }

        return $order_data_list;
    }

    function wpie_set_order_import_data($wpie_data = array()) {

        global $wpdb;

        $file_url = isset($wpie_data['wpie_import_file_url']) ? $wpie_data['wpie_import_file_url'] : "";

        $file_path_data = isset($wpie_data['wpie_csv_upload_file']) ? $wpie_data['wpie_csv_upload_file'] : "";

        $order_offset = isset($wpie_data['order_offset']) ? $wpie_data['order_offset'] : 0;

        $process_status = isset($wpie_data['status']) ? $wpie_data['status'] : "pending";

        $wpie_import_determinator = (isset($wpie_data['wpie_import_determinator']) || trim($wpie_data['wpie_import_determinator']) != "") ? $wpie_data['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($wpie_data['wpie_data_update_option']) ? $wpie_data['wpie_data_update_option'] : "order_key";

        $order_field_list = $this->get_updated_order_fields();

        $file_path = "";

        if ($file_path_data != "") {
            $file_path = $file_path_data;
        } else if ($file_url != "") {
            $file_path = $file_url;
        }

        if ($file_path != "") {

            $new_file = pathinfo($file_path);
            $new_values = array();

            $new_values['export_log_file_type'] = 'import';
            $new_values['export_log_file_name'] = $new_file['basename'];
            $new_values['export_log_data'] = 'Order';
            $new_values['create_date'] = current_time('mysql');

            $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);

            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($fh !== FALSE) {


                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'Order Key') {
                                $csv_temp_count++;
                                break;
                            }
                        }
                        if ($csv_temp_count == 0) {
                            break;
                        }
                    }

                    $import_data_temp[] = $new_line;

                    $csv_temp_count++;
                }
                if ($csv_temp_count == 0) {
                    if ($wpie_import_determinator == ",") {
                        $wpie_import_determinator = ";";
                    } else {
                        $wpie_import_determinator = ",";
                    }
                    @rewind($fh);
                    while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {

                        if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                            foreach ($new_line as $csv_new_column) {
                                if ($csv_new_column == 'Id' || $csv_new_column == 'Order Key') {
                                    $csv_temp_count++;
                                    break;
                                }
                            }
                            if ($csv_temp_count == 0) {
                                break;
                            }
                        }
                        $import_data_temp[] = $new_line;

                        $csv_temp_count++;
                    }
                }
                fclose($fh);

                $fields_title_array = $import_data_temp[0];

                unset($import_data_temp[0]);

                $count = 0;
                $total_records = count($import_data_temp);


                foreach ($import_data_temp as $data) {
                    foreach ($data as $key => $value) {
                        $temp_key = $fields_title_array[$key];
                        $import_data[$count][$temp_key] = $value;
                    }
                    if (!isset($import_data[0]['Id'])) {

                        if (isset($import_data[0]['Order Key'])) {
                            $import_data[$count]['Id'] = $count+999999;
                        } else {
                            $import_data = array();
                        }
                    }
                    $count++;
                }
            }
        }

        if (!empty($import_data)) {

            $wpie_order_create_method = isset($wpie_data['wpie_order_create_method']) ? $wpie_data['wpie_order_create_method'] : "";
            $import_type = 'scheduled';
            $order_updated_data = $this->wpie_create_new_order($import_data, $wpie_order_create_method, $import_type, $wpie_data_update_option);
        }
    }

    function wpie_import_order_percentage() {

        if (session_id() == '') {
            session_start();
        }
        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['order_offset'] = isset($_SESSION['order_old_new_ids']) ? count($_SESSION['order_old_new_ids']) : 0;

        $return_value['total_records'] = isset($_SESSION['order_total_records']) ? $_SESSION['order_total_records'] : 0;

        echo json_encode($return_value);

        die();
    }

}
