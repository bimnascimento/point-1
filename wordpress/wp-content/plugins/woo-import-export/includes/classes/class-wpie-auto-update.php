<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_AUTO_UPDATE {

    function __construct() {

        add_action('admin_init', array(&$this, 'wpie_update_data'));

        add_filter('pre_set_site_transient_update_plugins', array(&$this, 'wpie_check_update'));

        add_filter('plugins_api', array(&$this, 'wpie_check_info'), 10, 3);

        add_action('in_plugin_update_message-woo-import-export/woo-import-export.php', array(&$this, 'wpie_upgrade_message_link'));
    }

    function wpie_update_data() {

        global $wpdb, $wpie_new_version, $wpie_product, $wpie_order, $wpie_user, $wpie_product_category, $wpie_coupon;

        if (!isset($wpie_new_version) || $wpie_new_version == "") {
            $wpie_new_version = get_option('wpie_plugin_version');
        }

        if (version_compare($wpie_new_version, '2.0', '<')) {

            $product_fields = $wpie_product->get_new_product_fields();

            update_option('wpie_product_fields', $product_fields);

            $order_fields = $wpie_order->get_new_order_fields();

            update_option('wpie_order_fields', $order_fields);

            $user_fields = $wpie_user->get_new_user_fields();

            update_option('wpie_user_fields', $user_fields);

            $product_cat_fields = $wpie_product_category->get_new_product_cat_fields();

            update_option('wpie_product_cat_fields', $product_cat_fields);

            $coupon_fields = $wpie_coupon->get_new_coupon_fields();

            update_option('wpie_coupon_fields', $coupon_fields);

            $export_log = $wpdb->prefix . 'wpie_export_log';

            $wpdb->query("ALTER TABLE " . $export_log . " MODIFY export_log_file_type VARCHAR(50),MODIFY export_log_file_name VARCHAR(150),MODIFY export_log_data VARCHAR(150)");

            //update_option('wpie_plugin_version', '2.0');

            $wpie_new_version = '2.0';
        }
        if (version_compare($wpie_new_version, '2.3', '<')) {

            update_option('wpie_plugin_version', '2.3');

            $wpie_new_version = '2.3';
        }
    }

    function wpie_check_update($transient) {


        $key_nonce = "";

        if (empty($transient->checked)) {
            return $transient;
        }


        $remote_version = $this->wpie_get_remote_version();


        $post_data = get_option('wpie_current_site_plugin_date_format');

        if ($post_data != "") {
            $post_data = maybe_unserialize(base64_decode($post_data));

            if (isset($post_data['purchase_code']) && $post_data['purchase_code'] != "") {
                $key_nonce = base64_encode($post_data['purchase_code']);
            }
        }
        if (version_compare(WPIE_PLUGIN_VERSION, $remote_version, '<') && $key_nonce != "") {
            $obj = new stdClass();
            $obj->slug = 'woo-import-export';
            $obj->new_version = $remote_version;
            $obj->url = WPIE_PLUGIN_SITE . '?wpie_update_key_nonce=' . $key_nonce;
            $obj->package = WPIE_PLUGIN_SITE . '?wpie_update_key_nonce=' . $key_nonce;
            $transient->response['woo-import-export/woo-import-export.php'] = $obj;
        }

        return $transient;
    }

    function wpie_check_info($false, $action, $arg) {

        if ($arg->slug === 'woo-import-export') {
            $information = $this->wpie_get_remote_information();

            return $information;
        }
        return false;
    }

    function wpie_get_remote_version() {

        $site_data = array();

        $site_data['plugin_name'] = 'woo-import-export';

        $valstring = maybe_serialize($site_data);

        $post_data = base64_encode($valstring);

        $response = wp_remote_post(WPIE_PLUGIN_SITE, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(),
            'body' => array('get_product_version' => $post_data),
            'cookies' => array()
                )
        );

        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "") {

            $response_result = @maybe_unserialize(base64_decode($response["body"]));

            if ($response_result{'plugin_version'} != '0') {
                return $response_result{'plugin_version'};
            }
        }

        return false;
    }

    function wpie_get_remote_information() {
        $site_data = array();

        $site_data['plugin_name'] = 'woo-import-export';

        $key_nonce = "";

        $new_data = get_option('wpie_current_site_plugin_date_format');

        if ($new_data != "") {
            $new_data = maybe_unserialize(base64_decode($new_data));

            if (isset($new_data['purchase_code']) && $new_data['purchase_code'] != "") {
                $key_nonce = base64_encode($new_data['purchase_code']);
            }
        }

        $site_data['plugin_update_url'] = WPIE_PLUGIN_SITE . '?wpie_update_key_nonce=' . $key_nonce;

        $valstring = maybe_serialize($site_data);

        $post_data = base64_encode($valstring);

        $response = wp_remote_post(WPIE_PLUGIN_SITE, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(),
            'body' => array('get_product_info' => $post_data),
            'cookies' => array()
                )
        );

        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "") {
            $response_result = @maybe_unserialize(base64_decode($response["body"]));

            if ($response_result['product_info']) {
                return $response_result['product_info'];
            }
        }
        return false;
    }

    function wpie_upgrade_message_link() {
        
    }

}

?>