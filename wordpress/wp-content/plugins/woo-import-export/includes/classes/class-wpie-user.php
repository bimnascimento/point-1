<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_USER {

    function __construct() {

        add_action('wp_ajax_wpie_create_user_csv', array(&$this, 'wpie_create_user_csv'));

        add_action('wp_ajax_wpie_execute_user_data_query', array(&$this, 'wpie_execute_user_data_query'));

        add_action('wp_ajax_wpie_update_user_csv', array(&$this, 'wpie_update_user_csv'));

        add_action('wp_ajax_wpie_create_user_preview', array(&$this, 'wpie_create_user_preview'));

        add_action('wp_ajax_wpie_get_user_export_preview', array(&$this, 'wpie_get_user_export_preview'));

        add_action('wp_ajax_wpie_import_user', array(&$this, 'wpie_import_user'));

        add_action('wp_ajax_wpie_get_user_import_preview', array(&$this, 'wpie_get_user_import_preview'));

        add_action('wp_ajax_wpie_save_user_fields', array(&$this, 'wpie_save_user_fields'));

        add_action('wp_ajax_wpie_import_user_percentage', array(&$this, 'wpie_import_user_percentage'));
    }

    function wpie_get_user_count() {

        $user_query = array();

        $user_query['fields'] = 'ids';

        $user_query['number'] = 0;

        $total_user = count(get_users($user_query));

        return $total_user;
    }

    function wpie_get_author_list() {

        $query_args = array(
            'who' => 'authors',
            'number' => 2000,
            'fields' => array('ID', 'display_name', 'user_email'),
        );

        $user_query = new WP_User_Query($query_args);

        $user_results = $user_query->get_results();

        return $user_results;
    }

    function user_export_field_list() {
        $field_list = array(
            'user_field' => array(
                array(
                    'field_key' => 'ID',
                    'field_display' => 1,
                    'field_title' => 'Id',
                    'field_value' => 'Id',
                ),
                array(
                    'field_key' => 'user_role',
                    'field_display' => 1,
                    'field_title' => 'User Role',
                    'field_value' => 'User Role',
                ),
                array(
                    'field_key' => 'user_email',
                    'field_display' => 1,
                    'field_title' => 'User Email',
                    'field_value' => 'User Email',
                ),
                array(
                    'field_key' => 'user_login',
                    'field_display' => 1,
                    'field_title' => 'Username',
                    'field_value' => 'Username',
                ),
                array(
                    'field_key' => 'user_pass',
                    'field_display' => 1,
                    'field_title' => 'Password',
                    'field_value' => 'Password',
                ),
                array(
                    'field_key' => 'user_registered',
                    'field_display' => 1,
                    'field_title' => 'User Registered',
                    'field_value' => 'User Registered',
                ),
                array(
                    'field_key' => 'user_url',
                    'field_display' => 1,
                    'field_title' => 'Website',
                    'field_value' => 'Website',
                ),
                array(
                    'field_key' => 'billing_first_name',
                    'field_display' => 1,
                    'field_title' => 'First Name (Billing)',
                    'field_value' => 'First Name (Billing)',
                ),
                array(
                    'field_key' => 'billing_last_name',
                    'field_display' => 1,
                    'field_title' => 'Last Name (Billing)',
                    'field_value' => 'Last Name (Billing)',
                ),
                array(
                    'field_key' => 'billing_company',
                    'field_display' => 1,
                    'field_title' => 'Company (Billing)',
                    'field_value' => 'Company (Billing)',
                ),
                array(
                    'field_key' => 'billing_address_1',
                    'field_display' => 1,
                    'field_title' => 'Address 1 (Billing)',
                    'field_value' => 'Address 1 (Billing)',
                ),
                array(
                    'field_key' => 'billing_address_2',
                    'field_display' => 1,
                    'field_title' => 'Address 2 (Billing)',
                    'field_value' => 'Address 2 (Billing)',
                ),
                array(
                    'field_key' => 'billing_city',
                    'field_display' => 1,
                    'field_title' => 'City (Billing)',
                    'field_value' => 'City (Billing)',
                ),
                array(
                    'field_key' => 'billing_postcode',
                    'field_display' => 1,
                    'field_title' => 'Postcode (Billing)',
                    'field_value' => 'Postcode (Billing)',
                ),
                array(
                    'field_key' => 'billing_country',
                    'field_display' => 1,
                    'field_title' => 'Country (Billing)',
                    'field_value' => 'Country (Billing)',
                ),
                array(
                    'field_key' => 'billing_state',
                    'field_display' => 1,
                    'field_title' => 'State (Billing)',
                    'field_value' => 'State (Billing)',
                ),
                array(
                    'field_key' => 'billing_email',
                    'field_display' => 1,
                    'field_title' => 'Email (Billing)',
                    'field_value' => 'Email (Billing)',
                ),
                array(
                    'field_key' => 'billing_phone',
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
                    'field_key' => 'shipping_last_name',
                    'field_display' => 1,
                    'field_title' => 'Last Name (Shipping)',
                    'field_value' => 'Last Name (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_company',
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
                    'field_key' => 'shipping_address_2',
                    'field_display' => 1,
                    'field_title' => 'Address 2 (Shipping)',
                    'field_value' => 'Address 2 (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_city',
                    'field_display' => 1,
                    'field_title' => 'City (Shipping)',
                    'field_value' => 'City (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_postcode',
                    'field_display' => 1,
                    'field_title' => 'Postcode (Shipping)',
                    'field_value' => 'Postcode (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_state',
                    'field_display' => 1,
                    'field_title' => 'State (Shipping)',
                    'field_value' => 'State (Shipping)',
                ),
                array(
                    'field_key' => 'shipping_country',
                    'field_display' => 1,
                    'field_title' => 'Country (Shipping)',
                    'field_value' => 'Country (Shipping)',
                ),
                array(
                    'field_key' => 'wpie_user_meta',
                    'field_display' => 1,
                    'field_title' => 'User Meta',
                    'field_value' => 'User Meta',
                ),
                array(
                    'field_key' => 'wpie_user_capabilities',
                    'field_display' => 1,
                    'field_title' => 'User Capabilities',
                    'field_value' => 'User Capabilities',
                ),
            ),
        );

        return $field_list;
    }

    function get_new_user_fields() {

        $user_fields = maybe_serialize($this->user_export_field_list());

        return $user_fields;
    }

    function get_user_list() {
        $user_query = array();

        $user_query['fields'] = array('ID', 'display_name', 'user_email');

        $user_query['number'] = 2000;

        $user_list = get_users($user_query);

        return $user_list;
    }

    function get_updated_user_fields() {

        $old_user_fields = $this->get_new_user_fields();

        $new_fields = get_option('wpie_user_fields', $old_user_fields);

        $new_fields = maybe_unserialize($new_fields);

        return $new_fields;
    }

    function wpie_get_user_export_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where (`export_log_file_type` = 'csv' or `export_log_file_type` = 'export') and `export_log_data`='User' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_get_user_import_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where `export_log_file_type` = 'import' and `export_log_data`='User' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function get_user_export_fields_data() {

        $csv_data = "";

        $user_field_list = $this->get_updated_user_fields();

        $count = 0;

        foreach ($user_field_list['user_field'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        return $csv_data;
    }

    function wpie_create_user_filter_query($wpie_data = array()) {

        global $wpdb;

        $blog_id = get_current_blog_id();

        $user_ids = isset($wpie_data['wpie_user_id']) ? $wpie_data['wpie_user_id'] : array();

        $user_role = isset($wpie_data['wpie_user_role']) ? $wpie_data['wpie_user_role'] : array();

        $user_total_record = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $user_offset = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $user_min_spend = isset($wpie_data['wpie_user_min_spend']) ? $wpie_data['wpie_user_min_spend'] : 0;

        $start_date = "";

        $end_date = "";

        if ($temp_start_date != "") {
            $temp_start_date = explode('-', $temp_start_date);

            $start_date = $temp_start_date[2] . '-' . $temp_start_date[0] . '-' . $temp_start_date[1];
        }
        if ($temp_end_date != "") {
            $temp_end_date = explode('-', $temp_end_date);

            $end_date = $temp_end_date[2] . '-' . $temp_end_date[0] . '-' . $temp_end_date[1];
        }

        $user_query = array();

        if ($temp_end_date != "" || $temp_start_date != "") {
            $date_data = array();

            if ($temp_end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }
            if ($temp_start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $user_query['date_query'] = array($date_data);
        }

        if (!empty($user_ids)) {
            $user_query['include'] = $user_ids;
        }

        if ($user_total_record != "" && $user_total_record > 0) {
            $user_query['number'] = $user_total_record;

            if ($user_offset != "" && $user_offset > 0) {
                $user_query['offset'] = $user_offset;
            }
        }

        $user_query['fields'] = 'all_with_meta';

        if (!empty($user_role)) {
            if (count($user_role) == 1) {
                $user_query['role'] = $user_role[0];
            } else if (count($user_role) > 1) {

                $user_query['meta_query'] = array(array(
                        'key' => $wpdb->get_blog_prefix($blog_id) . 'capabilities',
                        'value' => '"(' . implode('|', array_map('preg_quote', $user_role)) . ')"',
                        'compare' => 'REGEXP'
                ));
            }
        }
        if ($user_min_spend > 0) {
            $user_query['meta_query'][] = array(
                'key' => '_money_spent',
                'value' => $user_min_spend,
                'compare' => '>=',
            );
        }

        $user_query['orderby'] = 'ID';

        $user_query['order'] = 'ASC';

        return json_encode($user_query);
    }

    function wpie_create_user_csv() {

        global $wpdb;

        $return_value = array();

        $user_export_data = $this->get_user_export_fields_data();

        $user_query_data = $this->wpie_create_user_filter_query($_POST);

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $filename = 'user_' . date('Y_m_d_H_i_s') . '.csv';

        $fh = @fopen(WPIE_UPLOAD_DIR . '/' . $filename, 'w+');

        if (!empty($user_export_data)) {
            foreach ($user_export_data as $new_data) {
                @fputcsv($fh, $new_data, $wpie_export_separator);
            }
        }

        @fclose($fh);

        $new_values = array();

        $new_values['export_log_file_type'] = 'export';
        $new_values['export_log_file_name'] = $filename;
        $new_values['export_log_data'] = 'User';
        $new_values['create_date'] = current_time('mysql');

        $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
        $new_log_id = $wpdb->insert_id;

        $return_value['message'] = 'success';
        $return_value['file_name'] = WPIE_UPLOAD_DIR . '/' . $filename;
        $return_value['status'] = 'pending';
        $return_value['user_query'] = $user_query_data;
        $return_value['user_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";
        $return_value['user_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $filename . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                '<div class="wpie-log-delete-action wpie-export-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $filename . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                '</div>';

        $return_value['data'] = array('', $filename, $new_values['create_date'], $data_action);


        echo json_encode($return_value);

        die();
    }

    function wpie_execute_user_data_query() {

        $query_args = isset($_POST['data_query']) ? json_decode(stripslashes($_POST['data_query']), 1) : "";

        $query_args['fields'] = "ids";

        $user_list = get_users($query_args);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['total_results'] = count($user_list);

        echo json_encode($return_value);

        die();
    }

    function wpie_update_user_csv() {

        $file_name = isset($_POST['file_name']) ? stripslashes($_POST['file_name']) : "";

        $user_query = isset($_POST['user_query']) ? stripslashes($_POST['user_query']) : "";

        $start_user = isset($_POST['start_user']) ? $_POST['start_user'] : "";

        $user_offset = isset($_POST['user_offset']) ? $_POST['user_offset'] : 0;

        $user_limit = isset($_POST['user_limit']) ? $_POST['user_limit'] : "";

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $return_value = array();

        $return_value['user_limit'] = $user_limit;

        if ($file_name != "" && file_exists($file_name)) {

            $fh = @fopen($file_name, 'a+');

            $user_list_data = $this->get_filter_user_data($user_query, $start_user, 0, $user_offset, $user_limit);

            $user_field_list = $this->get_updated_user_fields();

            $return_value['start_user'] = isset($user_list_data['start_user']) ? $user_list_data['start_user'] : $start_user;

            if (!empty($user_list_data['user_data'])) {

                if ($user_list_data['status'] == "completed" || (isset($user_list_data['user_limit']) && $user_list_data['user_limit'] == 0)) {
                    $return_value['status'] = 'completed';
                } else {
                    $return_value['status'] = 'pending';
                }

                $return_value['user_limit'] = isset($user_list_data['user_limit']) ? $user_list_data['user_limit'] : "";

                foreach ($user_list_data['user_data'] as $user_info) {

                    $data_result = array();

                    foreach ($user_field_list['user_field'] as $field_data) {

                        if ($field_data['field_display'] == 1) {

                            $field_key = $field_data['field_key'];
                            $data_result[] = isset($user_info->$field_key) ? $user_info->$field_key : "";
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

    function get_filter_user_data($user_query, $start_user, $total_records = 0, $user_offset, $user_limit) {
        global $wpie_get_record_count;

        $user_data_list = array();

        if ($user_limit != "" && $total_records >= $user_limit) {
            $total_records = $user_limit;
            $user_data_list['status'] = "completed";
        }
        if ($user_offset != "" && $start_user == 0) {
            $start_user = $user_offset;
        }
        if ($total_records == 0) {
            $total_records = $wpie_get_record_count;
        }

        $query_args = json_decode(stripslashes($user_query), 1);

        $query_args['number'] = $total_records;

        $query_args['offset'] = $start_user;

        $user_list = get_users($query_args);

        foreach ($user_list as $new_user) {
            foreach ($new_user->roles as $key => $value) {
                $new_user->user_role = $value;
            }

            $new_user->wpie_user_meta = @maybe_serialize(get_user_meta($new_user->ID));

            $new_user->wpie_user_capabilities = @maybe_serialize($new_user->allcaps);

            $user_data_list['user_data'][] = $new_user;

            $start_user++;

            $user_data_list['start_user'] = $start_user;

            if ($user_limit != "" && $user_limit > 0) {
                $user_limit--;
                $user_data_list['user_limit'] = $user_limit;
                if ($user_data_list['user_limit'] == 0) {
                    $user_data_list['status'] = "completed";
                    break;
                }
            }
        }

        return $user_data_list;
    }

    function wpie_create_user_preview() {

        $return_value = array();

        $user_query_data = $this->wpie_create_user_filter_query($_POST);

        $return_value['message'] = 'success';

        $return_value['user_query'] = $user_query_data;

        $return_value['user_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";

        $return_value['user_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $return_value['total_users'] = $this->get_user_total_records_count(json_decode($user_query_data, 1));

        echo json_encode($return_value);

        die();
    }

    function get_user_total_records_count($query_args) {

        $query_args['fields'] = "ids";

        $user_list = get_users($query_args);

        return count($user_list);
    }

    function wpie_get_user_export_preview() {

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $data_query = isset($_POST['user_query']) ? $_POST['user_query'] : "";

        $total_users = isset($_POST['total_users']) ? $_POST['total_users'] : 0;

        $user_limit = isset($_POST['user_limit']) ? $_POST['user_limit'] : 0;

        $user_limit = ($user_limit - $record_offset) > 0 ? ($user_limit - $record_offset) : $total_users;

        $user_offset = isset($_POST['user_offset']) ? $_POST['user_offset'] : 0;

        $product_list_data = $this->get_filter_user_data($data_query, $record_offset, $record_limit, $user_offset, $user_limit);

        $final_data = array();

        $user_field_list = $this->get_updated_user_fields();

        foreach ($product_list_data['user_data'] as $user_info) {

            $data_result = array();

            foreach ($user_field_list['user_field'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $temp_data = $field_data['field_key'];

                    $data_result[] = isset($user_info->$temp_data) ? $user_info->$temp_data : "";
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_users;

        $return_value['recordsTotal'] = $total_users;

        echo json_encode($return_value);

        die();
    }

    function get_user_csv_data($wpie_data = array()) {

        $csv_data = "";

        $count = 0;

        $user_field_lists = $this->get_updated_user_fields();

        $user_list_data = $this->wpie_get_user_data($wpie_data);



        foreach ($user_field_lists as $user_field_list) {
            foreach ($user_field_list as $field_data) {
                if ($field_data['field_display'] == 1) {

                    $csv_data[$count][] = $field_data['field_value'];
                }
            }
        }

        foreach ($user_list_data as $user_info) {
            $count++;

            $data_result = array();

            foreach ($user_field_lists as $user_field_list) {
                foreach ($user_field_list as $field_data) {

                    if ($field_data['field_display'] == 1) {
                        $temp_data = $field_data['field_key'];
                        $data_result[] = $user_info->$temp_data;
                    }
                }
            }

            $csv_data[$count] = $data_result;
        }

        return $csv_data;
    }

    function wpie_get_user_data($wpie_data = array()) {

        global $advanced_export, $wpdb;

        $blog_id = get_current_blog_id();

        $user_ids = isset($wpie_data['wpie_user_id']) ? $wpie_data['wpie_user_id'] : array();

        $user_role = isset($wpie_data['wpie_user_role']) ? $wpie_data['wpie_user_role'] : array();

        $user_total_record = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $user_offset = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $user_min_spend = isset($wpie_data['wpie_user_min_spend']) ? $wpie_data['wpie_user_min_spend'] : 0;

        $start_date = "";

        $end_date = "";

        if ($temp_start_date != "") {
            $temp_start_date = explode('-', $temp_start_date);

            $start_date = $temp_start_date[2] . '-' . $temp_start_date[0] . '-' . $temp_start_date[1];
        }
        if ($temp_end_date != "") {
            $temp_end_date = explode('-', $temp_end_date);

            $end_date = $temp_end_date[2] . '-' . $temp_end_date[0] . '-' . $temp_end_date[1];
        }

        $user_query = array();

        if ($temp_end_date != "" || $temp_start_date != "") {
            $date_data = array();

            if ($temp_end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }
            if ($temp_start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $user_query['date_query'] = array($date_data);
        }

        if (!empty($user_ids)) {
            $user_query['include'] = $user_ids;
        }

        if ($user_total_record != "" && $user_total_record > 0) {
            $user_query['number'] = $user_total_record;

            if ($user_offset != "" && $user_offset > 0) {
                $user_query['offset'] = $user_offset;
            }
        }

        $user_query['fields'] = 'all_with_meta';

        if (!empty($user_role)) {
            if (count($user_role) == 1) {
                $user_query['role'] = $user_role[0];
            } else if (count($user_role) > 1) {

                $user_query['meta_query'] = array(array(
                        'key' => $wpdb->get_blog_prefix($blog_id) . 'capabilities',
                        'value' => '"(' . implode('|', array_map('preg_quote', $user_role)) . ')"',
                        'compare' => 'REGEXP'
                ));
            }
        }
        if ($user_min_spend > 0) {
            $user_query['meta_query'][] = array(
                'key' => '_money_spent',
                'value' => $user_min_spend,
                'compare' => '>=',
            );
        }

        $user_query['orderby'] = 'ID';

        $user_query['order'] = 'ASC';

        $user_list = get_users($user_query);

        foreach ($user_list as $new_user) {
            foreach ($new_user->roles as $key => $value) {
                $new_user->user_role = $value;
            }

            $new_user->wpie_user_meta = @maybe_serialize(get_user_meta($new_user->ID));

            $new_user->wpie_user_capabilities = @maybe_serialize($new_user->allcaps);
        }

        return $user_list;
    }

    function wpie_import_user() {

        global $wpdb;

        $return_value = array();

        $return_value['message'] = 'error';

        $file_url = isset($_POST['wpie_import_file_url']) ? $_POST['wpie_import_file_url'] : "";

        $file_path_data = isset($_POST['wpie_csv_upload_file']) ? $_POST['wpie_csv_upload_file'] : "";

        $process_status = isset($_POST['status']) ? $_POST['status'] : "pending";

        $wpie_import_determinator = (isset($_POST['wpie_import_determinator']) || trim($_POST['wpie_import_determinator']) != "") ? $_POST['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($_POST['wpie_data_update_option']) ? $_POST['wpie_data_update_option'] : "user_email";

        $user_field_list = $this->get_updated_user_fields();

        if (session_id() == '') {
            session_start();
        }

        if ($process_status == "start") {

            $_SESSION['user_imported_ids'] = array();

            $_SESSION['user_old_new_ids'] = array();

            $_SESSION['user_old_new_id_list'] = array();
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
                $new_values['export_log_data'] = 'User';
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
                            if ($csv_new_column == 'Id' || $csv_new_column == 'User Email') {
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
                                if ($csv_new_column == 'Id' || $csv_new_column == 'User Email') {
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

                        if (isset($import_data[0]['User Email'])) {
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

                if ($total_records <= @count($_SESSION['user_old_new_ids'])) {
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

                $wpie_user_create_method = isset($_POST['wpie_user_create_method']) ? $_POST['wpie_user_create_method'] : "";

                $import_type = 'normal';

                $user_updated_data = $this->wpie_create_new_user($import_data, $wpie_user_create_method, $import_type, $wpie_data_update_option);
                $return_value['user_offset'] = @count($_SESSION['user_old_new_ids']);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_create_new_user($user_data = array(), $user_create_method = "", $import_type = 'normal', $wpie_data_update_option = "user_email") {

        global $wpdb;
        if (session_id() == '') {
            session_start();
        }
        $user_field_list = $this->get_updated_user_fields();

        foreach ($user_data as $user_info) {

            $existing_user = "";

            $existing_user_id = "";

            if (isset($user_info['Id'])) {

                $user_info['Id'] = intval($user_info['Id']);

                $old_user_id = $user_info['Id'];

                if (isset($_SESSION['user_old_new_ids'][$old_user_id]) && $_SESSION['user_old_new_ids'][$old_user_id] != "") {
                    continue;
                }
                if (isset($_SESSION['user_old_new_id_list'][$old_user_id]) && $_SESSION['user_old_new_id_list'][$old_user_id] != "") {
                    $existing_user_id == $_SESSION['user_old_new_id_list'][$old_user_id];
                }

                if ($existing_user_id == "" && $wpie_data_update_option == "user_id") {

                    if (get_user_by('id', $old_user_id) === false) {
                        
                    } else {
                        $existing_user_id = $old_user_id;
                    }
                }
            }

            if ($existing_user_id == "" && isset($user_info['User Email']) && $user_info['User Email'] != "") {
                $existing_user = get_user_by('email', $user_info['User Email']);
                $existing_user_id = $existing_user->ID;
            }

            $new_user_id = 0;

            if (isset($user_info['User Registered']) && ($user_info['User Registered'] == "0000-00-00 00:00:00" || @DateTime::createFromFormat('Y-m-d G:i:s', $user_info['User Registered']) === FALSE)) {
                $user_info['User Registered'] = date('Y-m-d G:i:s');
            }

            if ($existing_user_id == "" && isset($user_info['Username'])) {
                $existing_user = get_user_by('login', $user_info['Username']);
                $existing_user_id = $existing_user->ID;
            }

            if ($existing_user_id != "" && $user_create_method == 'skip_user') {
                if ($import_type == 'normal') {
                    $_SESSION['user_imported_ids'][] = $existing_user_id;
                }

                if (isset($user_info['Id'])) {
                    $old_temp_user_id = $user_info['Id'];
                    $_SESSION['user_old_new_ids'][$old_temp_user_id] = $existing_user_id;

                    $_SESSION['user_old_new_id_list'][$old_temp_user_id] = $existing_user_id;
                }
                continue;
            } else if ($user_create_method == 'update_user' && $existing_user_id != "") {

                $new_user_data = array();

                $new_user_data['ID'] = $existing_user_id;

                if (isset($user_info['Username'])) {
                    $new_user_data['user_login'] = $user_info['Username'];
                }
                if (isset($user_info['Website'])) {
                    $new_user_data['user_url'] = $user_info['Website'];
                }
                if (isset($user_info['Password'])) {
                    $new_user_data['user_pass'] = $user_info['Password'];
                }
                if (isset($user_info['User Email'])) {
                    $new_user_data['user_email'] = $user_info['User Email'];
                }
                if (isset($user_info['User Registered'])) {
                    $new_user_data['user_registered'] = $user_info['User Registered'];
                }
                if (isset($user_info['User Role'])) {
                    $new_user_data['role'] = $user_info['User Role'];
                }

                $new_user_id = wp_update_user($new_user_data);

                if ($new_user_id && isset($user_info['Password'])) {
                    $wpdb->update($wpdb->users, array('user_pass' => $user_info['Password']), array('ID' => $new_user_id));

                    wp_cache_delete($new_user_id, 'users');
                }
            } else {
                if ($existing_user_id != "") {
                    if ($import_type == 'normal') {
                        $_SESSION['user_imported_ids'][] = $existing_user_id;
                    }
                    if (isset($user_info['Id'])) {
                        $old_temp_user_id = $user_info['Id'];
                        $_SESSION['user_old_new_ids'][$old_temp_user_id] = $existing_user_id;

                        $_SESSION['user_old_new_id_list'][$old_temp_user_id] = $existing_user_id;
                    }
                    continue;
                } else {
                    $new_user_data = array();

                    if (isset($user_info['Username'])) {
                        $new_user_data['user_login'] = $user_info['Username'];
                    }
                    if (isset($user_info['Website'])) {
                        $new_user_data['user_url'] = $user_info['Website'];
                    }
                    if (isset($user_info['Password'])) {
                        $new_user_data['user_pass'] = $user_info['Password'];
                    }
                    if (isset($user_info['User Email'])) {
                        $new_user_data['user_email'] = $user_info['User Email'];
                    }
                    if (isset($user_info['User Registered'])) {
                        $new_user_data['user_registered'] = $user_info['User Registered'];
                    }
                    if (isset($user_info['User Role'])) {
                        $new_user_data['role'] = $user_info['User Role'];
                    }

                    $new_user_id = wp_insert_user($new_user_data);

                    if ($new_user_id && isset($user_info['Password'])) {
                        $wpdb->update($wpdb->users, array('user_pass' => $user_info['Password']), array('ID' => $new_user_id));

                        wp_cache_delete($new_user_id, 'users');
                    }
                }
            }

            if ($new_user_id != "" && $new_user_id > 0) {
                if ($import_type == 'normal') {
                    $_SESSION['user_imported_ids'][] = $new_user_id;
                }
                $new_user = new WP_User($new_user_id);

                if (isset($user_info['User Meta']) && $user_info['User Meta'] != "") {

                    $new_user_meta = @maybe_unserialize($user_info['User Meta']);

                    if (!empty($new_user_meta)) {
                        foreach ($new_user_meta as $meta_key => $meta_value) {
                            foreach ($meta_value as $key => $value) {
                                @update_user_meta($new_user_id, $meta_key, $value);
                            }
                        }
                    }
                }

                if (isset($user_info['User Capabilities']) && $user_info['User Capabilities'] != "") {

                    $new_user_cap = @maybe_unserialize($user_info['User Capabilities']);

                    if (!empty($new_user_cap)) {
                        foreach ($new_user_cap as $key => $value) {
                            $new_user->add_cap($key);
                        }
                    }
                }

                $user_fields_title = array();

                foreach ($user_field_list['user_field'] as $field_data) {
                    $user_fields_title[] = $field_data['field_title'];
                }

                foreach ($user_info as $key => $value) {
                    if (!in_array($key, $user_fields_title)) {
                        update_user_meta($new_user_id, $key, $value);
                    }
                }

                $include_array = array('billing_first_name' => 'First Name (Billing)', 'billing_last_name' => 'Last Name (Billing)', 'billing_company' => 'Company (Billing)', 'billing_address_1' => 'Address 1 (Billing)', 'billing_address_2' => 'Address 2 (Billing)', 'billing_city' => 'City (Billing)',
                    'billing_postcode' => 'Postcode (Billing)', 'billing_country' => 'Country (Billing)', 'billing_state' => 'State (Billing)',
                    'billing_email' => 'Email (Billing)', 'billing_phone' => 'Phone (Billing)', 'shipping_first_name' => 'First Name (Shipping)',
                    'shipping_last_name' => 'Last Name (Shipping)', 'shipping_company' => 'Company (Shipping)', 'shipping_address_1' => 'Address 1 (Shipping)',
                    'shipping_address_2' => 'Address 2 (Shipping)', 'shipping_city' => 'City (Shipping)', 'shipping_postcode' => 'Postcode (Shipping)',
                    'shipping_state' => 'State (Shipping)', 'shipping_country' => 'Country (Shipping)');

                foreach ($include_array as $key => $value) {
                    if (isset($user_info[$value])) {
                        update_user_meta($new_user_id, $key, $user_info[$value]);
                    }
                }
            }
            if (isset($user_info['Id'])) {

                $old_temp_user_id = $user_info['Id'];
                $_SESSION['user_old_new_ids'][$old_temp_user_id] = $new_user_id;
            }
        }

        return "";
    }

    function wpie_get_user_import_preview() {

        if (session_id() == '') {
            session_start();
        }

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $query_args = array(
            'include' => $_SESSION['user_imported_ids'],
            'fields' => 'all_with_meta',
            'orderby' => 'ID',
            'order' => 'ASC',
        );

        $total_user = count($_SESSION['user_imported_ids']);

        $data_query = addslashes(json_encode($query_args));


        $product_list_data = $this->get_filter_user_data($data_query, $record_offset, $record_limit, 0, 0);

        $final_data = array();

        $user_field_list = $this->get_updated_user_fields();

        foreach ($product_list_data['user_data'] as $user_info) {

            $data_result = array();

            foreach ($user_field_list['user_field'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $temp_data = $field_data['field_key'];

                    $data_result[] = isset($user_info->$temp_data) ? $user_info->$temp_data : "";
                    ;
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_user;

        $return_value['recordsTotal'] = $total_user;

        echo json_encode($return_value);

        die();
    }

    function wpie_save_user_fields() {

        $old_user_fields = $this->get_updated_user_fields();

        $new_fields = array();

        foreach ($old_user_fields as $user_fields_key => $user_fields_data) {

            foreach ($user_fields_data as $key => $value) {

                $new_fields[$user_fields_key][$key]['field_key'] = $value['field_key'];

                $new_fields[$user_fields_key][$key]['field_display'] = isset($_POST['wpie_' . $value['field_key'] . '_field_check']) ? $_POST['wpie_' . $value['field_key'] . '_field_check'] : "";

                $new_fields[$user_fields_key][$key]['field_title'] = $value['field_title'];

                $new_fields[$user_fields_key][$key]['field_value'] = $value['field_title']; //isset($_POST['wpie_' . $value['field_key'] . '_field']) ? $_POST['wpie_' . $value['field_key'] . '_field'] : "";
            }
        }

        $new_fields_data = maybe_serialize($new_fields);

        update_option('wpie_user_fields', $new_fields_data);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['message_content'] = __('Changes Saved Successfully.', WPIE_TEXTDOMAIN);

        $return_value['preview_fields'] = $this->get_user_preview_fields();

        echo json_encode($return_value);

        die();
    }

    function get_user_preview_fields() {

        $user_fields = $this->get_updated_user_fields();

        $preview_fields_data = '<table class="wpie-product-filter-data wpie-datatable table table-striped table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>';

        foreach ($user_fields as $new_user_fields) {
            foreach ($new_user_fields as $user_fields_data)
                if ($user_fields_data['field_display'] == 1) {
                    $preview_fields_data .= '<th>' . $user_fields_data['field_title'] . '</th>';
                }
        }

        $preview_fields_data .="   </tr>

                </thead>
            </table>";
        return $preview_fields_data;
    }

    function wpie_set_user_import_data($wpie_data = array()) {

        global $wpdb;

        $file_url = isset($wpie_data['wpie_import_file_url']) ? $wpie_data['wpie_import_file_url'] : "";

        $file_path_data = isset($wpie_data['wpie_csv_upload_file']) ? $wpie_data['wpie_csv_upload_file'] : "";

        $user_offset = isset($wpie_data['user_offset']) ? $wpie_data['user_offset'] : 0;

        $process_status = isset($wpie_data['status']) ? $wpie_data['status'] : "pending";

        $wpie_import_determinator = (isset($_POST['wpie_import_determinator']) || trim($_POST['wpie_import_determinator']) != "") ? $_POST['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($_POST['wpie_data_update_option']) ? $_POST['wpie_data_update_option'] : "user_email";

        $user_field_list = $this->get_updated_user_fields();

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
            $new_values['export_log_data'] = 'User';
            $new_values['create_date'] = current_time('mysql');

            $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);

            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($fh !== FALSE) {

                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'User Email') {
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
                                if ($csv_new_column == 'Id' || $csv_new_column == 'User Email') {
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

                        if (isset($import_data[0]['User Email'])) {
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

            $wpie_user_create_method = isset($wpie_data['wpie_user_create_method']) ? $wpie_data['wpie_user_create_method'] : "";
            $import_type = 'scheduled';
            $user_updated_data = $this->wpie_create_new_user($import_data, $wpie_user_create_method, $import_type, $wpie_data_update_option);
        }
    }

    function wpie_import_user_percentage() {

        if (session_id() == '') {
            session_start();
        }
        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['user_offset'] = isset($_SESSION['user_old_new_ids']) ? count($_SESSION['user_old_new_ids']) : 0;

        $return_value['total_records'] = isset($_SESSION['user_total_records']) ? $_SESSION['user_total_records'] : 0;

        echo json_encode($return_value);

        die();
    }

}
