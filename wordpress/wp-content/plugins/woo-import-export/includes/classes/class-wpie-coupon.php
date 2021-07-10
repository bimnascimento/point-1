<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_COUPON {

    function __construct() {

        add_action('wp_ajax_wpie_create_coupon_csv', array(&$this, 'wpie_create_coupon_csv'));

        add_action('wp_ajax_wpie_execute_coupon_data_query', array(&$this, 'wpie_execute_coupon_data_query'));

        add_action('wp_ajax_wpie_update_coupon_csv', array(&$this, 'wpie_update_coupon_csv'));

        add_action('wp_ajax_wpie_create_coupon_preview', array(&$this, 'wpie_create_coupon_preview'));

        add_action('wp_ajax_wpie_get_coupon_export_preview', array(&$this, 'wpie_get_coupon_export_preview'));

        add_action('wp_ajax_wpie_save_coupon_fields', array(&$this, 'wpie_save_coupon_fields'));

        add_action('wp_ajax_wpie_import_coupon', array(&$this, 'wpie_import_coupon'));

        add_action('wp_ajax_wpie_get_coupon_import_preview', array(&$this, 'wpie_get_coupon_import_preview'));

        add_action('wp_ajax_wpie_import_coupon_percentage', array(&$this, 'wpie_import_coupon_percentage'));
    }

    function wpie_get_coupon_count() {

        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'fields' => 'ids',
        );

        $total_coupon = count(get_posts($query_args));

        return $total_coupon;
    }

    function get_coupon_list() {
        $query_args = array(
            'posts_per_page' => 2000,
            'post_type' => 'shop_coupon',
            'post_status' => 'publish',
            'orderby' => 'ID',
            'order' => 'ASC',
        );

        $coupon_list = get_posts($query_args);

        return $coupon_list;
    }

    function get_updated_coupon_fields() {

        $old_coupon_fields = $this->get_new_coupon_fields();

        $new_fields = get_option('wpie_coupon_fields', $old_coupon_fields);

        $new_fields = maybe_unserialize($new_fields);

        return $new_fields;
    }

    function get_new_coupon_fields() {

        $coupon_fields = maybe_serialize($this->get_coupon_fields());

        return $coupon_fields;
    }

    function get_coupon_fields() {
        $get_coupon_fields = array(
            'coupon_fields' => array(
                array(
                    'field_key' => 'ID',
                    'field_display' => 1,
                    'field_title' => 'Id',
                    'field_value' => 'Id',
                ),
                array(
                    'field_key' => 'post_title',
                    'field_display' => 1,
                    'field_title' => 'Code',
                    'field_value' => 'Code',
                ),
                array(
                    'field_key' => 'coupon_amount',
                    'field_display' => 1,
                    'field_title' => 'Coupon Amount',
                    'field_value' => 'Coupon Amount',
                ),
                array(
                    'field_key' => 'post_date',
                    'field_display' => 1,
                    'field_title' => 'Created Date',
                    'field_value' => 'Created Date',
                ),
                array(
                    'field_key' => 'expiry_date',
                    'field_display' => 1,
                    'field_title' => 'Expiry Date',
                    'field_value' => 'Expiry Date',
                ),
                array(
                    'field_key' => 'post_excerpt',
                    'field_display' => 1,
                    'field_title' => 'Description',
                    'field_value' => 'Description',
                ),
                array(
                    'field_key' => 'discount_type',
                    'field_display' => 1,
                    'field_title' => 'Discount Type',
                    'field_value' => 'Discount Type',
                ),
                array(
                    'field_key' => 'post_type',
                    'field_display' => 1,
                    'field_title' => 'Post Type',
                    'field_value' => 'Post Type',
                ),
                array(
                    'field_key' => 'post_name',
                    'field_display' => 1,
                    'field_title' => 'Coupon Name',
                    'field_value' => 'Coupon Name',
                ),
                array(
                    'field_key' => 'individual_use',
                    'field_display' => 1,
                    'field_title' => 'Individual Use',
                    'field_value' => 'Individual Use',
                ),
                array(
                    'field_key' => 'product_ids',
                    'field_display' => 1,
                    'field_title' => 'Product Ids',
                    'field_value' => 'Product Ids',
                ),
                array(
                    'field_key' => 'usage_limit',
                    'field_display' => 1,
                    'field_title' => 'Usage Limit',
                    'field_value' => 'Usage Limit',
                ),
                array(
                    'field_key' => 'free_shipping',
                    'field_display' => 1,
                    'field_title' => 'Free Shipping',
                    'field_value' => 'Free Shipping',
                ),
                array(
                    'field_key' => 'post_status',
                    'field_display' => 1,
                    'field_title' => 'Post Status',
                    'field_value' => 'Post Status',
                ),
                array(
                    'field_key' => 'coupon_attributes',
                    'field_display' => 1,
                    'field_title' => 'Attributes',
                    'field_value' => 'Attributes',
                ),
                array(
                    'field_key' => 'post_parent',
                    'field_display' => 1,
                    'field_title' => 'Product Parent id',
                    'field_value' => 'Product Parent id',
                ),
                array(
                    'field_key' => 'menu_order',
                    'field_display' => 1,
                    'field_title' => 'Menu Order',
                    'field_value' => 'Menu Order',
                ),
                array(
                    'field_key' => 'comment_status',
                    'field_display' => 1,
                    'field_title' => 'Comment Status',
                    'field_value' => 'Comment Status',
                ),
                array(
                    'field_key' => 'ping_status',
                    'field_display' => 1,
                    'field_title' => 'Ping Status',
                    'field_value' => 'Ping Status',
                ),
            ),
        );

        return $get_coupon_fields;
    }

    function wpie_get_coupon_export_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where (`export_log_file_type` = 'csv' or `export_log_file_type` = 'export') and `export_log_data`='Coupon' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_get_coupon_import_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where `export_log_file_type` = 'import' and `export_log_data`='Coupon' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_create_coupon_csv() {

        global $wpdb;

        $return_value = array();

        $coupon_export_data = $this->get_coupon_export_fields_data();

        $coupon_query_data = $this->wpie_create_coupon_filter_query($_POST);

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $filename = 'coupon_' . date('Y_m_d_H_i_s') . '.csv';

        $fh = @fopen(WPIE_UPLOAD_DIR . '/' . $filename, 'w+');

        if (!empty($coupon_export_data)) {
            foreach ($coupon_export_data as $new_data) {
                @fputcsv($fh, $new_data, $wpie_export_separator);
            }
        }

        @fclose($fh);

        $new_values = array();

        $new_values['export_log_file_type'] = 'export';
        $new_values['export_log_file_name'] = $filename;
        $new_values['export_log_data'] = 'Coupon';
        $new_values['create_date'] = current_time('mysql');

        $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
        $new_log_id = $wpdb->insert_id;

        $return_value['message'] = 'success';
        $return_value['file_name'] = WPIE_UPLOAD_DIR . '/' . $filename;
        $return_value['status'] = 'pending';
        $return_value['coupon_query'] = $coupon_query_data;
        $return_value['coupon_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";
        $return_value['coupon_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $filename . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                '<div class="wpie-log-delete-action wpie-export-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $filename . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                '</div>';
        $return_value['data'] = array('', $new_values['export_log_file_name'], $new_values['create_date'], $data_action);

        echo json_encode($return_value);

        die();
    }

    function get_coupon_export_fields_data() {

        $csv_data = "";

        $coupon_field_list = $this->get_updated_coupon_fields();

        $count = 0;

        foreach ($coupon_field_list['coupon_fields'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        return $csv_data;
    }

    function wpie_create_coupon_filter_query($wpie_data) {

        $coupon_ids = isset($wpie_data['wpie_coupon_ids']) ? $wpie_data['wpie_coupon_ids'] : array();

        $total_records = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $offset_records = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $end_date = "";

        $start_date = "";

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
            'post_type' => 'shop_coupon',
            'orderby' => 'ID',
            'order' => 'ASC',
        );


        if (!empty($coupon_ids)) {
            $query_args['post__in'] = $coupon_ids;
        }

        if ($total_records != "" && $total_records > 0) {
            $query_args['posts_per_page'] = $total_records;

            if ($offset_records != "" && $offset_records >= 0) {
                $query_args['offset'] = $offset_records;
            }
        }

        if ($end_date != "" || $start_date != "") {
            $date_data = array();

            if ($end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }

            if ($start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $query_args['date_query'] = array($date_data);
        }

        return json_encode($query_args);
    }

    function wpie_execute_coupon_data_query() {

        $query_args = isset($_POST['data_query']) ? json_decode(stripslashes($_POST['data_query']), 1) : "";

        $query_args['fields'] = "ids";

        $coupon_results = new WP_Query($query_args);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['total_results'] = count($coupon_results->get_posts());

        echo json_encode($return_value);

        die();
    }

    function wpie_update_coupon_csv() {

        $file_name = isset($_POST['file_name']) ? stripslashes($_POST['file_name']) : "";

        $coupon_query = isset($_POST['coupon_query']) ? stripslashes($_POST['coupon_query']) : "";

        $start_coupon = isset($_POST['start_coupon']) ? $_POST['start_coupon'] : "";

        $coupon_offset = isset($_POST['coupon_offset']) ? $_POST['coupon_offset'] : 0;

        $coupon_limit = isset($_POST['coupon_limit']) ? $_POST['coupon_limit'] : "";

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $return_value = array();

        $return_value['coupon_limit'] = $coupon_limit;

        if ($file_name != "" && file_exists($file_name)) {

            $fh = @fopen($file_name, 'a+');

            $coupon_list_data = $this->get_filter_coupon_data($coupon_query, $start_coupon, $coupon_limit, $coupon_offset, $coupon_limit);

            $coupon_field_list = $this->get_updated_coupon_fields();

            $return_value['start_coupon'] = isset($coupon_list_data['start_coupon']) ? $coupon_list_data['start_coupon'] : 0;

            if (!empty($coupon_list_data['coupon_data'])) {

                if ($coupon_list_data['status'] == "completed" || (isset($coupon_list_data['coupon_limit']) && $coupon_list_data['coupon_limit'] == 0)) {
                    $return_value['status'] = 'completed';
                } else {
                    $return_value['status'] = 'pending';
                }

                $return_value['coupon_limit'] = isset($coupon_list_data['coupon_limit']) ? $coupon_list_data['coupon_limit'] : "";

                foreach ($coupon_list_data['coupon_data'] as $coupon_info) {

                    $data_result = array();

                    foreach ($coupon_field_list['coupon_fields'] as $field_data) {

                        if ($field_data['field_display'] == 1) {

                            $field_key = $field_data['field_key'];
                            $data_result[] = isset($coupon_info->$field_key) ? $coupon_info->$field_key : "";
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

    function get_filter_coupon_data($coupon_query, $start_coupon, $total_records = 0, $coupon_offset, $coupon_limit) {
        global $wpie_get_record_count;

        $coupon_data_list = array();

        if ($coupon_limit != "" && $total_records >= $coupon_limit) {
            $total_records = $coupon_limit;
            $coupon_data_list['status'] = "completed";
        }
        if ($coupon_offset != "" && $start_coupon == 0) {
            $start_coupon = $coupon_offset;
        }
        if ($total_records == 0) {
            $total_records = $wpie_get_record_count;
        }

        $query_args = json_decode(stripslashes($coupon_query), 1);

        $query_args['posts_per_page'] = $total_records;

        $query_args['offset'] = $start_coupon;

        $coupon_results = new WP_Query($query_args);

        $coupon_list = $coupon_results->get_posts();

        if (!empty($coupon_list)) {
            foreach ($coupon_list as $new_coupon) {
                $coupon_meta = get_post_meta($new_coupon->ID);

                if (isset($coupon_meta['product_ids'][0]) && $coupon_meta['product_ids'][0] != "") {
                    $coupon_product_ids = $this->get_coupons_product_sku($coupon_meta['product_ids'][0]);
                } else {
                    $coupon_product_ids = "";
                }
                if (isset($coupon_meta['exclude_product_ids'][0]) && $coupon_meta['exclude_product_ids'][0] != "") {
                    $exclude_product_ids = $this->get_coupons_product_sku($coupon_meta['exclude_product_ids'][0]);
                } else {
                    $exclude_product_ids = "";
                }
                if (isset($coupon_meta['product_categories'][0]) && $coupon_meta['product_categories'][0] != "") {
                    $coupon_product_categories = $this->get_product_categories_slug($coupon_meta['product_categories'][0]);
                } else {
                    $coupon_product_categories = "";
                }
                if (isset($coupon_meta['exclude_product_categories'][0]) && $coupon_meta['exclude_product_categories'][0] != "") {
                    $exclude_product_categories = $this->get_product_categories_slug($coupon_meta['exclude_product_categories'][0]);
                } else {
                    $exclude_product_categories = "";
                }

                $coupon_meta['coupon_product_ids'][0] = $coupon_product_ids;

                $coupon_meta['coupon_exclude_product_ids'][0] = $exclude_product_ids;

                $coupon_meta['coupon_product_categories'][0] = $coupon_product_categories;

                $coupon_meta['coupon_exclude_product_categories'][0] = $exclude_product_categories;

                $new_coupon->coupon_attributes = maybe_serialize($coupon_meta);

                $new_coupon->coupon_amount = $coupon_meta['coupon_amount'][0];

                $new_coupon->expiry_date = $coupon_meta['expiry_date'][0];

                $new_coupon->discount_type = $coupon_meta['discount_type'][0];

                $new_coupon->individual_use = $coupon_meta['individual_use'][0];

                $new_coupon->product_ids = $this->get_coupons_product_sku($coupon_meta['product_ids'][0]);

                $new_coupon->usage_limit = $coupon_meta['usage_limit'][0];

                $new_coupon->free_shipping = $coupon_meta['free_shipping'][0];

                $coupon_data_list['coupon_data'][] = $new_coupon;
                $start_coupon++;

                $coupon_data_list['start_coupon'] = $start_coupon;

                if ($coupon_limit != "" && $coupon_limit > 0) {
                    $coupon_limit--;
                    $coupon_data_list['coupon_limit'] = $coupon_limit;
                    if ($coupon_data_list['coupon_limit'] == 0) {
                        $coupon_data_list['status'] = "completed";
                        break;
                    }
                }
            }
        }

        wp_reset_postdata();

        return $coupon_data_list;
    }

    function get_coupons_product_sku($product_list = "") {
        $product_data = array();

        if ($product_list != "") {
            $product_ids = explode(',', $product_list);

            if (!empty($product_ids)) {
                foreach ($product_ids as $product_id) {
                    $new_product_sku = get_post_meta($product_id, '_sku', true);

                    if ($new_product_sku != "") {
                        $product_data[$product_id] = $new_product_sku;
                    }
                }
            }
        }
        return implode(',', $product_data);
    }

    function get_product_categories_slug($categories_list = "") {
        $categories_data = array();

        $categories_ids = maybe_unserialize(maybe_unserialize($categories_list));

        if (!empty($categories_ids)) {
            foreach ($categories_ids as $categories_id) {
                if (!isset($categories_data[$categories_id]) || $categories_data[$categories_id] == "") {
                    $new_category = get_term_by('id', $categories_id, 'product_cat');

                    if (isset($new_category->slug) && $new_category->slug != "") {
                        $categories_data[$categories_id] = $new_category->slug;
                    }
                }
            }
        }

        return implode(',', $categories_data);
    }

    function wpie_create_coupon_preview() {

        $return_value = array();

        $coupon_query_data = $this->wpie_create_coupon_filter_query($_POST);

        $return_value['message'] = 'success';

        $return_value['coupon_query'] = $coupon_query_data;

        $return_value['coupon_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";

        $return_value['coupon_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $return_value['total_coupons'] = $this->get_coupon_total_records_count(json_decode($coupon_query_data, 1));

        echo json_encode($return_value);

        die();
    }

    function get_coupon_total_records_count($query_args) {

        $query_args['fields'] = "ids";

        $product_results = new WP_Query($query_args);

        return count($product_results->get_posts());
    }

    function wpie_get_coupon_export_preview() {

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $data_query = isset($_POST['coupon_query']) ? $_POST['coupon_query'] : "";

        $total_coupons = isset($_POST['total_coupons']) ? $_POST['total_coupons'] : 0;

        $coupon_limit = isset($_POST['coupon_limit']) ? $_POST['coupon_limit'] : 0;

        $coupon_limit = $coupon_limit - $record_offset;

        $coupon_offset = isset($_POST['coupon_offset']) ? $_POST['coupon_offset'] : 0;

        $product_list_data = $this->get_filter_coupon_data($data_query, $record_offset, $record_limit, $coupon_offset, $coupon_limit);

        $final_data = array();

        $coupon_field_list = $this->get_updated_coupon_fields();

        foreach ($product_list_data['coupon_data'] as $coupon_info) {

            $data_result = array();

            if (!empty($coupon_info)) {
                foreach ($coupon_field_list['coupon_fields'] as $field_data) {

                    if ($field_data['field_display'] == 1) {

                        $temp_data = $field_data['field_key'];

                        $data_result[] = isset($coupon_info->$temp_data) ? $coupon_info->$temp_data : "";
                    }
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_coupons;

        $return_value['recordsTotal'] = $total_coupons;

        echo json_encode($return_value);

        die();
    }

    function wpie_save_coupon_fields() {

        $old_coupon_fields = $this->get_updated_coupon_fields();

        $new_fields = array();

        foreach ($old_coupon_fields as $coupon_fields_key => $coupon_fields_data) {

            foreach ($coupon_fields_data as $key => $value) {

                $new_fields[$coupon_fields_key][$key]['field_key'] = $value['field_key'];

                $new_fields[$coupon_fields_key][$key]['field_display'] = isset($_POST['wpie_' . $value['field_key'] . '_field_check']) ? $_POST['wpie_' . $value['field_key'] . '_field_check'] : "";

                $new_fields[$coupon_fields_key][$key]['field_title'] = $value['field_title'];

                $new_fields[$coupon_fields_key][$key]['field_value'] = $value['field_title']; //isset($_POST['wpie_' . $value['field_key'] . '_field']) ? $_POST['wpie_' . $value['field_key'] . '_field'] : "";
            }
        }

        $new_fields_data = maybe_serialize($new_fields);

        update_option('wpie_coupon_fields', $new_fields_data);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['message_content'] = __('Changes Saved Successfully.', WPIE_TEXTDOMAIN);

        $return_value['preview_fields'] = $this->get_coupon_preview_fields();

        echo json_encode($return_value);

        die();
    }

    function get_coupon_preview_fields() {

        $coupon_fields = $this->get_updated_coupon_fields();

        $preview_fields_data = '<table class="wpie-product-filter-data wpie-datatable table table-striped table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>';

        foreach ($coupon_fields as $new_coupon_fields) {
            foreach ($new_coupon_fields as $coupon_fields_data)
                if ($coupon_fields_data['field_display'] == 1) {
                    $preview_fields_data .= '<th>' . $coupon_fields_data['field_title'] . '</th>';
                }
        }

        $preview_fields_data .="   </tr>

                </thead>
            </table>";
        return $preview_fields_data;
    }

    function get_coupon_export_data($wpie_data = array()) {
        $csv_data = "";

        $coupon_field_list = $this->get_updated_coupon_fields();

        $coupon_list_data = $this->get_filter_coupon($wpie_data);

        $count = 0;

        foreach ($coupon_field_list['coupon_fields'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        foreach ($coupon_list_data as $coupon_info) {
            $count++;

            $data_result = array();

            foreach ($coupon_field_list['coupon_fields'] as $field_data) {


                if ($field_data['field_display'] == 1) {
                    $temp_data = $field_data['field_key'];
                    $data_result[] = isset($coupon_info->$temp_data) ? $coupon_info->$temp_data : "";
                }
            }

            $csv_data[$count] = $data_result;
        }

        return $csv_data;
    }

    function get_filter_coupon($wpie_data) {

        $coupon_ids = isset($wpie_data['wpie_coupon_ids']) ? $wpie_data['wpie_coupon_ids'] : array();

        $total_records = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $offset_records = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $end_date = "";

        $start_date = "";

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
            'post_type' => 'shop_coupon',
            'orderby' => 'ID',
            'order' => 'ASC',
        );


        if (!empty($coupon_ids)) {
            $query_args['post__in'] = $coupon_ids;
        }

        if ($total_records != "" && $total_records > 0) {
            $query_args['posts_per_page'] = $total_records;

            if ($offset_records != "" && $offset_records >= 0) {
                $query_args['offset'] = $offset_records;
            }
        }

        if ($end_date != "" || $start_date != "") {
            $date_data = array();

            if ($end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }

            if ($start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $query_args['date_query'] = array($date_data);
        }

        $coupon_results = new WP_Query($query_args);

        $coupon_list = $coupon_results->get_posts();

        if (!empty($coupon_list)) {
            foreach ($coupon_list as $new_coupon) {
                $coupon_meta = get_post_meta($new_coupon->ID);

                if (isset($coupon_meta['product_ids'][0]) && $coupon_meta['product_ids'][0] != "") {
                    $coupon_product_ids = $this->get_coupons_product_sku($coupon_meta['product_ids'][0]);
                } else {
                    $coupon_product_ids = "";
                }
                if (isset($coupon_meta['exclude_product_ids'][0]) && $coupon_meta['exclude_product_ids'][0] != "") {
                    $exclude_product_ids = $this->get_coupons_product_sku($coupon_meta['exclude_product_ids'][0]);
                } else {
                    $exclude_product_ids = "";
                }
                if (isset($coupon_meta['product_categories'][0]) && $coupon_meta['product_categories'][0] != "") {
                    $coupon_product_categories = $this->get_product_categories_slug($coupon_meta['product_categories'][0]);
                } else {
                    $coupon_product_categories = "";
                }
                if (isset($coupon_meta['exclude_product_categories'][0]) && $coupon_meta['exclude_product_categories'][0] != "") {
                    $exclude_product_categories = $this->get_product_categories_slug($coupon_meta['exclude_product_categories'][0]);
                } else {
                    $exclude_product_categories = "";
                }

                $coupon_meta['coupon_product_ids'][0] = $coupon_product_ids;

                $coupon_meta['coupon_exclude_product_ids'][0] = $exclude_product_ids;

                $coupon_meta['coupon_product_categories'][0] = $coupon_product_categories;

                $coupon_meta['coupon_exclude_product_categories'][0] = $exclude_product_categories;

                $new_coupon->coupon_attributes = maybe_serialize($coupon_meta);

                $new_coupon->coupon_amount = $coupon_meta['coupon_amount'][0];

                $new_coupon->expiry_date = $coupon_meta['expiry_date'][0];

                $new_coupon->discount_type = $coupon_meta['discount_type'][0];

                $new_coupon->individual_use = $coupon_meta['individual_use'][0];

                $new_coupon->product_ids = $coupon_meta['product_ids'][0];

                $new_coupon->usage_limit = $coupon_meta['usage_limit'][0];

                $new_coupon->free_shipping = $coupon_meta['free_shipping'][0];
            }
        }


        wp_reset_postdata();

        return $coupon_list;
    }

    function wpie_import_coupon() {

        global $wpdb;

        $return_value = array();

        $return_value['message'] = 'error';

        $file_url = isset($_POST['wpie_import_file_url']) ? $_POST['wpie_import_file_url'] : "";

        $file_path_data = isset($_POST['wpie_csv_upload_file']) ? $_POST['wpie_csv_upload_file'] : "";

        $process_status = isset($_POST['status']) ? $_POST['status'] : "pending";

        $wpie_import_determinator = (isset($_POST['wpie_import_determinator']) || trim($_POST['wpie_import_determinator']) != "") ? $_POST['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($_POST['wpie_data_update_option']) ? $_POST['wpie_data_update_option'] : "coupon_code";

        $coupon_field_list = $this->get_updated_coupon_fields();

        if (session_id() == '') {
            session_start();
        }

        if ($process_status == "start") {

            $_SESSION['coupon_imported_ids'] = array();

            $_SESSION['coupon_old_new_ids'] = array();

            $_SESSION['coupon_old_new_id_list'] = array();
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
                $new_values['export_log_data'] = 'Coupon';
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
                            if ($csv_new_column == 'Id' || $csv_new_column == 'Code') {
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
                                if ($csv_new_column == 'Id' || $csv_new_column == 'Code') {
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

                        if (isset($import_data[0]['Code'])) {
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

                if ($total_records <= @count($_SESSION['coupon_old_new_ids'])) {
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

                $wpie_coupon_create_method = isset($_POST['wpie_coupon_create_method']) ? $_POST['wpie_coupon_create_method'] : "";

                $import_type = 'normal';

                $coupon_updated_data = $this->wpie_create_new_coupon($import_data, $wpie_coupon_create_method, $import_type, $wpie_data_update_option);

                $return_value['coupon_offset'] = @count($_SESSION['coupon_old_new_ids']);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_create_new_coupon($coupon_data = array(), $wpie_coupon_create_method = "", $import_type = 'normal', $wpie_data_update_option = "coupon_code") {

        if (session_id() == '') {
            session_start();
        }

        $coupon_field_list = $this->get_updated_coupon_fields();


        foreach ($coupon_data as $coupon_info) {

            $current_coupon_id = "";

            if (isset($coupon_info['Id'])) {

                $coupon_info['Id'] = intval($coupon_info['Id']);

                $old_coupon_id = $coupon_info['Id'];

                if (isset($_SESSION['coupon_old_new_ids'][$old_coupon_id]) && $_SESSION['coupon_old_new_ids'][$old_coupon_id] != "") {
                    continue;
                }
                if (isset($_SESSION['coupon_old_new_id_list'][$old_coupon_id]) && $_SESSION['coupon_old_new_id_list'][$old_coupon_id] != "") {
                    $current_coupon_id == $_SESSION['coupon_old_new_id_list'][$old_coupon_id];
                }

                if ($current_coupon_id == "" && $wpie_data_update_option == "coupon_id") {

                    if (get_post_status($old_coupon_id) === false) {
                        
                    } else {
                        $current_coupon_id = $old_coupon_id;
                    }
                }
            }

            if ($current_coupon_id == "" && isset($coupon_info['Code']) && $coupon_info['Code'] != "") {
                $current_coupon_id = $this->get_coupon_id_from_code($coupon_info['Code']);
            }

            if (!isset($coupon_info['Post Status']) || $coupon_info['Post Status'] == "") {
                $coupon_info['Post Status'] = 'publish';
            }
            if (($wpie_coupon_create_method == 'update_coupon' || $wpie_coupon_create_method == 'skip_coupon') && $current_coupon_id != "" && $current_coupon_id > 0) {
                if ($wpie_coupon_create_method == 'skip_coupon') {
                    if ($import_type == 'normal') {
                        $_SESSION['coupon_imported_ids'][] = $current_coupon_id;
                    }
                    if (isset($coupon_info['Id'])) {
                        $old_temp_coupon_id = $coupon_info['Id'];
                        $_SESSION['coupon_old_new_ids'][$old_temp_coupon_id] = $current_coupon_id;

                        $_SESSION['coupon_old_new_id_list'][$old_temp_coupon_id] = $current_coupon_id;
                    }
                    continue;
                }

                if ($current_coupon_id != "" && $wpie_coupon_create_method == 'update_coupon') {

                    $update_coupon_data = array();

                    $update_coupon_data ['ID'] = $current_coupon_id;

                    if (isset($coupon_info['Coupon Name'])) {
                        $update_coupon_data ['post_name'] = $coupon_info['Coupon Name'];
                    }
                    if (isset($coupon_info['Post Type'])) {
                        $update_coupon_data ['post_type'] = $coupon_info['Post Type'];
                    }
                    if (isset($coupon_info['Code'])) {
                        $update_coupon_data ['post_title'] = $coupon_info['Code'];
                    }
                    if (isset($coupon_info['Post Status'])) {
                        $update_coupon_data ['post_status'] = $coupon_info['Post Status'];
                    }
                    if (isset($coupon_info['Ping Status'])) {
                        $update_coupon_data ['ping_status'] = $coupon_info['Ping Status'];
                    }
                    if (isset($coupon_info['Description'])) {
                        $update_coupon_data ['post_excerpt'] = $coupon_info['Description'];
                    }
                    if (isset($coupon_info['Created Date'])) {
                        $update_coupon_data ['post_date'] = $coupon_info['Created Date'];
                    }

                    $new_coupon_id = wp_update_post($update_coupon_data, true);
                }
            }

            if ($current_coupon_id == 0) {

                $new_coupon_data = array();

                if (isset($coupon_info['Coupon Name'])) {
                    $new_coupon_data ['post_name'] = $coupon_info['Coupon Name'];
                }
                if (isset($coupon_info['Post Type'])) {
                    $new_coupon_data ['post_type'] = $coupon_info['Post Type'];
                } else {
                    $new_coupon_data ['post_type'] = "shop_coupon";
                }
                if (isset($coupon_info['Code'])) {
                    $new_coupon_data ['post_title'] = $coupon_info['Code'];
                }
                if (isset($coupon_info['Post Status'])) {
                    $new_coupon_data ['post_status'] = $coupon_info['Post Status'];
                }
                if (isset($coupon_info['Ping Status'])) {
                    $new_coupon_data ['ping_status'] = $coupon_info['Ping Status'];
                }
                if (isset($coupon_info['Description'])) {
                    $new_coupon_data ['post_excerpt'] = $coupon_info['Description'];
                }
                if (isset($coupon_info['Created Date'])) {
                    $new_coupon_data ['post_date'] = $coupon_info['Created Date'];
                }

                $current_coupon_id = wp_insert_post($new_coupon_data, false);
            }
            if ($import_type == 'normal') {
                $_SESSION['coupon_imported_ids'][] = $current_coupon_id;
            }

            if ($current_coupon_id > 0 && isset($coupon_info['Attributes']) && $coupon_info['Attributes'] != "") {
                $coupon_attributes = maybe_unserialize($coupon_info['Attributes']);

                if (!empty($coupon_attributes)) {
                    foreach ($coupon_attributes as $key => $value) {

                        if ($key == 'product_ids' || $key == 'exclude_product_ids') {

                            $product_new_ids = array();

                            if ($key == 'product_ids' && isset($coupon_attributes['coupon_product_ids'][0]) && $coupon_attributes['coupon_product_ids'][0] != "") {
                                $product_new_ids = explode(',', $coupon_attributes['coupon_product_ids'][0]);
                            } else if ($key == 'exclude_product_ids' && isset($coupon_attributes['coupon_exclude_product_ids'][0]) && $coupon_attributes['coupon_exclude_product_ids'][0] != "") {
                                $product_new_ids = explode(',', $coupon_attributes['coupon_exclude_product_ids'][0]);
                            }

                            if (is_array($product_new_ids) && !empty($product_new_ids)) {
                                $existing_post_query = array(
                                    'posts_per_page' => 1000,
                                    'meta_query' => array(
                                        array(
                                            'key' => '_sku',
                                            'value' => array_values($product_new_ids),
                                            'compare' => 'IN'
                                        ),
                                    ),
                                    'post_type' => 'product',
                                    'fields' => 'ids',
                                );

                                $existing_product = get_posts($existing_post_query);



                                if (!empty($existing_product)) {
                                    $updated_new_product_ids = @implode(',', array_values($existing_product));
                                }
                            }
                            update_post_meta($current_coupon_id, $key, $updated_new_product_ids);
                        } else if (($key == 'product_categories' || $key == 'exclude_product_categories')) {

                            $product_cat_data_new = "";

                            if ($key == 'product_categories' && isset($coupon_attributes['coupon_product_categories'][0]) && $coupon_attributes['coupon_product_categories'][0] != "") {
                                $product_cat_data_new = explode(',', $coupon_attributes['coupon_product_categories'][0]);
                            } else if ($key == 'exclude_product_ids' && isset($coupon_attributes['coupon_exclude_product_categories'][0]) && $coupon_attributes['coupon_exclude_product_categories'][0] != "") {

                                $product_cat_data_new = explode(',', $coupon_attributes['coupon_exclude_product_categories'][0]);
                            }
                            $updated_new_cat_ids = maybe_unserialize($value[0]);

                            if (is_array($product_cat_data_new) && !empty($product_cat_data_new)) {
                                $updated_new_cat = array();

                                foreach ($product_cat_data_new as $new_keys => $new_values) {
                                    if ($new_values != "") {
                                        $new_cat_data = get_term_by('slug', $new_values, 'product_cat');

                                        if (isset($new_cat_data->id) && $new_cat_data->id != "") {
                                            $updated_new_cat[] = $new_cat_data->id;
                                        }
                                    }
                                }
                                if (!empty($updated_new_cat)) {
                                    $updated_new_cat_ids = @implode(',', $updated_new_cat);
                                }
                            }

                            update_post_meta($current_coupon_id, $key, $updated_new_cat_ids);
                        } else if ($key == 'customer_email') {
                            update_post_meta($current_coupon_id, $key, maybe_unserialize(maybe_unserialize($value[0])));
                        } else if ($key != 'coupon_product_ids' && $key != 'coupon_exclude_product_ids' && $key != 'coupon_product_categories' && $key != 'coupon_exclude_product_categories') {
                            update_post_meta($current_coupon_id, $key, $value[0]);
                        }
                    }
                }
            }

            $coupon_fields_title = array();

            foreach ($coupon_field_list['coupon_fields'] as $field_data) {
                $coupon_fields_title[] = $field_data['field_title'];
            }

            foreach ($coupon_info as $key => $value) {
                if (!in_array($key, $coupon_fields_title)) {
                    update_post_meta($current_coupon_id, $key, $value);
                }
            }

            $include_array = array("coupon_amount" => "Coupon Amount", "expiry_date" => "Expiry Date", "discount_type" => "Discount Type", "individual_use" => "Individual Use", "usage_limit" => "Usage Limit", "free_shipping" => "Free Shipping");

            foreach ($include_array as $key => $value) {
                if (isset($coupon_info[$value])) {
                    update_post_meta($current_coupon_id, $key, $coupon_info[$value]);
                }
            }

            if (isset($coupon_info['Product Ids'])) {

                $product_new_ids = explode(',', $coupon_info['Product Ids']);
                if (is_array($product_new_ids) && !empty($product_new_ids)) {
                    $existing_post_query = array(
                        'posts_per_page' => 1000,
                        'meta_query' => array(
                            array(
                                'key' => '_sku',
                                'value' => array_values($product_new_ids),
                                'compare' => 'IN'
                            ),
                        ),
                        'post_type' => 'product',
                        'fields' => 'ids',
                    );

                    $existing_product = get_posts($existing_post_query);



                    if (!empty($existing_product)) {
                        $updated_new_product_ids = @implode(',', array_values($existing_product));
                    }
                }

                update_post_meta($current_coupon_id, "product_ids", $updated_new_product_ids);
            }

            if (isset($coupon_info['Id'])) {

                $old_temp_coupon_id = $coupon_info['Id'];
                $_SESSION['coupon_old_new_ids'][$old_temp_coupon_id] = $current_coupon_id;
            }
        }

        return "";
    }

    function get_coupon_id_from_code($code) {

        global $wpdb;

        return absint($wpdb->get_var($wpdb->prepare(apply_filters('woocommerce_coupon_code_query', "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish'"), $code)));
    }

    function wpie_get_coupon_import_preview() {

        if (session_id() == '') {
            session_start();
        }

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $query_args = array(
            'post_type' => 'shop_coupon',
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post__in' => $_SESSION['coupon_imported_ids']
        );

        $total_coupon = count($_SESSION['coupon_imported_ids']);

        $data_query = addslashes(json_encode($query_args));


        $product_list_data = $this->get_filter_coupon_data($data_query, $record_offset, $record_limit, 0, 0);

        $final_data = array();

        $coupon_field_list = $this->get_updated_coupon_fields();

        foreach ($product_list_data['coupon_data'] as $coupon_info) {

            $data_result = array();

            foreach ($coupon_field_list['coupon_fields'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $temp_data = $field_data['field_key'];

                    $data_result[] = isset($coupon_info->$temp_data) ? $coupon_info->$temp_data : "";
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_coupon;

        $return_value['recordsTotal'] = $total_coupon;

        echo json_encode($return_value);

        die();
    }

    function wpie_set_coupon_import_data($wpie_data = array()) {

        global $wpdb;

        $file_url = isset($wpie_data['wpie_import_file_url']) ? $wpie_data['wpie_import_file_url'] : "";

        $file_path_data = isset($wpie_data['wpie_csv_upload_file']) ? $wpie_data['wpie_csv_upload_file'] : "";

        $coupon_offset = isset($wpie_data['coupon_offset']) ? $wpie_data['coupon_offset'] : 0;

        $process_status = isset($wpie_data['status']) ? $wpie_data['status'] : "pending";

        $wpie_import_determinator = (isset($wpie_data['wpie_import_determinator']) || trim($wpie_data['wpie_import_determinator']) != "") ? $wpie_data['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($wpie_data['wpie_data_update_option']) ? $wpie_data['wpie_data_update_option'] : "coupon_code";

        $coupon_field_list = $this->get_updated_coupon_fields();

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
            $new_values['export_log_data'] = 'Coupon';
            $new_values['create_date'] = current_time('mysql');

            $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);


            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($fh !== FALSE) {

                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'Code') {
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
                                if ($csv_new_column == 'Id' || $csv_new_column == 'Code') {
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

                        if (isset($import_data[0]['Code'])) {
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

            $wpie_coupon_create_method = isset($wpie_data['wpie_coupon_create_method']) ? $wpie_data['wpie_coupon_create_method'] : "";
            $import_type = 'scheduled';
            $coupon_updated_data = $this->wpie_create_new_coupon($import_data, $wpie_coupon_create_method, $import_type, $wpie_data_update_option);
        }
    }

    function wpie_import_coupon_percentage() {

        if (session_id() == '') {
            session_start();
        }
        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['coupon_offset'] = isset($_SESSION['coupon_old_new_ids']) ? count($_SESSION['coupon_old_new_ids']) : 0;

        $return_value['total_records'] = isset($_SESSION['coupon_total_records']) ? $_SESSION['coupon_total_records'] : 0;

        echo json_encode($return_value);

        die();
    }

}
