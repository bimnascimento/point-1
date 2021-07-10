<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_INIT {

    function __construct() {

        register_activation_hook(WPIE_PLUGIN_DIR . '/woo-import-export.php', array('WPIE_INIT', 'wpie_install_plugin_data'));

        register_uninstall_hook(WPIE_PLUGIN_DIR . '/woo-import-export.php', array('WPIE_INIT', 'wpie_uninstall_plugin_data'));

        global $woocommerce;

        $plugins = get_option('active_plugins');

        if (!function_exists('is_plugin_active_for_network')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }

        $required_woo_plugin = 'woocommerce/woocommerce.php';

        if (in_array($required_woo_plugin, $plugins) || is_plugin_active_for_network($required_woo_plugin)) {

            if (class_exists('Woocommerce')) {
                $this->wpie_init_plugin();
            } else {
                add_action('woocommerce_loaded', array(&$this, 'wpie_init_plugin'));
            }
        }
    }

    function wpie_init_plugin() {

        add_action('plugins_loaded', array(&$this, 'wpie_load_textdomain'));

        add_action('admin_enqueue_scripts', array(&$this, 'wpie_set_admin_css'), 10);

        add_action('admin_enqueue_scripts', array(&$this, 'wpie_set_admin_js'), 10);

        add_action('admin_menu', array(&$this, 'wpie_set_menu'));

        add_action('admin_init', array(&$this, 'wpie_db_check'));

        add_action('admin_head', array($this, 'wpie_hide_all_notice_to_admin_side'), 10000);

        add_filter('admin_footer_text', array(&$this, 'wpie_replace_footer_admin'));

        add_filter('update_footer', array(&$this, 'wpie_replace_footer_version'), '1234');

        add_action('admin_init', array(&$this, 'wpie_download_exported_data'));

        add_action('wp_ajax_wpie_activate_license', array(&$this, 'wpie_activate_license'));

        add_action('wp_ajax_wpie_license_deacivation', array(&$this, 'wpie_license_deacivation'));
    }

    function wpie_install_plugin_data() {

        $wpie_plugin_version = get_option('wpie_plugin_version');

        if (!isset($wpie_plugin_version) || $wpie_plugin_version == '') {

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            global $wpdb, $wpie_product, $wpie_order, $wpie_user, $wpie_product_category, $wpie_coupon;

            $charset_collate = '';

            if ($wpdb->has_cap('collation')) {

                if (!empty($wpdb->charset))
                    $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

                if (!empty($wpdb->collate))
                    $charset_collate .= " COLLATE $wpdb->collate";
            }

            update_option('wpie_plugin_version', WPIE_PLUGIN_VERSION);

            $export_log = $wpdb->prefix . 'wpie_export_log';

            $export_log_table = "CREATE TABLE IF NOT EXISTS {$export_log}(
							
                            export_log_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                            export_log_file_type VARCHAR(150) NOT NULL, 
                            export_log_file_name VARCHAR(150) NOT NULL,
                            export_log_data VARCHAR(150) NOT NULL,
                            create_date DATETIME NOT NULL 

                            ){$charset_collate}";

            dbDelta($export_log_table);

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
        }
    }

    function wpie_db_check() {

        $this->wpie_set_time_limit(0);

        $this->wpie_install_plugin_data();
    }

    function wpie_uninstall_plugin_data() {

        global $wpdb, $wpie_scheduled;
        if (is_multisite()) {
            $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
            if ($blogs) {

                foreach ($blogs as $blog) {

                    switch_to_blog($blog['blog_id']);

                    delete_option('wpie_plugin_version');

                    delete_option('wpie_sort_order');

                    delete_option('wpie_product_scheduled_data');

                    delete_option('wpie_order_scheduled_data');

                    delete_option('wpie_user_scheduled_data');

                    delete_option('wpie_product_fields');

                    delete_option('wpie_product_cat_fields');

                    delete_option('wpie_order_fields');

                    delete_option('wpie_user_fields');

                    delete_option('wpie_coupon_fields');

                    $wpie_export_log = $wpdb->prefix . 'wpie_export_log';

                    $wpdb->query("DROP TABLE IF EXISTS $wpie_export_log");

                    $wpie_scheduled->wpie_delete_all_cron();
                }
                restore_current_blog();
            }
        } else {
            delete_option('wpie_plugin_version');

            delete_option('wpie_sort_order');

            delete_option('wpie_product_scheduled_data');

            delete_option('wpie_order_scheduled_data');

            delete_option('wpie_user_scheduled_data');

            delete_option('wpie_product_fields');

            delete_option('wpie_product_cat_fields');

            delete_option('wpie_order_fields');

            delete_option('wpie_user_fields');

            delete_option('wpie_coupon_fields');

            $wpie_export_log = $wpdb->prefix . 'wpie_export_log';

            $wpdb->query("DROP TABLE IF EXISTS $wpie_export_log");

            $wpie_scheduled->wpie_delete_all_cron();
        }
    }

    function wpie_load_textdomain() {
        load_plugin_textdomain(WPIE_TEXTDOMAIN, false, 'woo-import-export/languages/');
    }

    function wpie_set_admin_css() {

        wp_register_style('wpie-admin-css', WPIE_CSS_URL . '/wpie-admin.min.css', array(), WPIE_PLUGIN_VERSION);

        wp_register_style('wpie-bootstrap-css', WPIE_CSS_URL . '/wpie-bootstrap.min.css', array(), WPIE_PLUGIN_VERSION);

        wp_register_style('wpie-bootstrap-datepicker-css', WPIE_CSS_URL . '/wpie-bootstrap-datepicker.min.css', array(), WPIE_PLUGIN_VERSION);

        wp_register_style('wpie-chosen-css', WPIE_CSS_URL . '/wpie-chosen.min.css', array(), WPIE_PLUGIN_VERSION);

        wp_register_style('wpie-datatables-css', WPIE_CSS_URL . '/wpie-datatables.min.css', array(), WPIE_PLUGIN_VERSION);

        wp_register_style('wpie-datatables-bootstrap-css', WPIE_CSS_URL . '/wpie-datatables-bootstrap.min.css', array(), WPIE_PLUGIN_VERSION);


        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-home' || $_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-product-categories' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-users' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-settings')) {

            wp_enqueue_style('wpie-admin-css');

            wp_enqueue_style('wpie-bootstrap-css');
        }

        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-users')) {

            wp_enqueue_style('wpie-bootstrap-datepicker-css');
        }

        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-product-categories' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-users')) {

            wp_enqueue_style('wpie-chosen-css');

            wp_enqueue_style('wpie-datatables-css');

            wp_enqueue_style('wpie-datatables-bootstrap-css');
        }
    }

    function wpie_set_admin_js() {

        wp_register_script('wpie-admin-js', WPIE_JS_URL . '/wpie-admin.min.js', array('jquery'), WPIE_PLUGIN_VERSION, true);

        wp_register_script('wpie-bootstrap-js', WPIE_JS_URL . '/wpie-bootstrap.min.js', array('jquery'), WPIE_PLUGIN_VERSION, true);

        wp_register_script('wpie-chosen-js', WPIE_JS_URL . '/wpie-chosen.min.js', array('jquery'), WPIE_PLUGIN_VERSION, true);

        wp_register_script('wpie-bootstrap-datepicker-js', WPIE_JS_URL . '/wpie-bootstrap-datepicker.min.js', array('jquery', 'wpie-bootstrap-js'), WPIE_PLUGIN_VERSION, true);

        wp_register_script('wpie-datatables-js', WPIE_JS_URL . '/wpie-datatables.min.js', array('jquery'), WPIE_PLUGIN_VERSION, true);

        wp_register_script('wpie-bpopup-js', WPIE_JS_URL . '/wpie-bpopup.min.js', array('jquery'), WPIE_PLUGIN_VERSION, true);

        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-home' || $_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-product-categories' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-users' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-settings')) {

            wp_enqueue_script('jquery');

            wp_enqueue_script('plupload');

            wp_enqueue_script('wpie-admin-js');

            $wpie_localize_script_data = array(
                'wpie_ajax_url' => admin_url('admin-ajax.php'),
                'wpie_site_url' => site_url(),
                'upload_url' => WPIE_UPLOAD_URL,
                'upload_dir' => WPIE_UPLOAD_DIR,
                'plugin_url' => WPIE_PLUGIN_URL,
            );

            wp_localize_script('wpie-admin-js', 'wpie_plugin_settings', $wpie_localize_script_data);

            wp_enqueue_script('wpie-bootstrap-js');

            wp_enqueue_script('wpie-bpopup-js');
        }
        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-users')) {

            wp_enqueue_script('wpie-bootstrap-datepicker-js');
        }
        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-product-categories' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-users')) {

            wp_enqueue_script('wpie-chosen-js');

            wp_enqueue_script('wpie-datatables-js');

            //Plupload File
            wp_enqueue_script('plupload-all');
        }
    }

    function wpie_hide_all_notice_to_admin_side() {
        if (isset($_REQUEST['page']) && ($_REQUEST['page'] == 'wpie-home' || $_REQUEST['page'] == 'wpie-products' || $_REQUEST['page'] == 'wpie-product-categories' || $_REQUEST['page'] == 'wpie-orders' || $_REQUEST['page'] == 'wpie-users' || $_REQUEST['page'] == 'wpie-coupons' || $_REQUEST['page'] == 'wpie-settings')) {
            remove_all_actions('admin_notices', 10000);
            remove_all_actions('all_admin_notices', 10000);
            remove_all_actions('network_admin_notices', 10000);
            remove_all_actions('user_admin_notices', 10000);
        }
    }

    function wpie_set_menu() {
        global $current_user;

        if (current_user_can('administrator') || is_super_admin()) {
            $wpie_caps = $this->wpie_user_capabilities();

            foreach ($wpie_caps as $wpie_cap => $cap_desc) {
                $current_user->add_cap($wpie_cap);
            }
        }

        $menu_place = $this->get_dynamic_position(28.1, .1);


        add_menu_page(__('WooCommerce Import Export Dashboard', WPIE_TEXTDOMAIN), __('Woo Imp Exp', WPIE_TEXTDOMAIN), 'wpie_manage_dashboard', 'wpie-home', array(&$this, 'wpie_get_page'), WPIE_IMAGES_URL . '/wpie_logo.png', $menu_place);

        add_submenu_page('wpie-home', __('Dashboard', WPIE_TEXTDOMAIN), __('Dashboard', WPIE_TEXTDOMAIN), 'wpie_manage_dashboard', 'wpie-home', array(&$this, 'wpie_get_page'));

        add_submenu_page('wpie-home', __('Products', WPIE_TEXTDOMAIN), __('Products', WPIE_TEXTDOMAIN), 'wpie_manage_products', 'wpie-products', array(&$this, 'wpie_get_page'));

        add_submenu_page('wpie-home', __('Product Categories', WPIE_TEXTDOMAIN), __('Product Categories', WPIE_TEXTDOMAIN), 'wpie_manage_product_categories', 'wpie-product-categories', array(&$this, 'wpie_get_page'));

        add_submenu_page('wpie-home', __('Orders', WPIE_TEXTDOMAIN), __('Orders', WPIE_TEXTDOMAIN), 'wpie_manage_orders', 'wpie-orders', array(&$this, 'wpie_get_page'));

        add_submenu_page('wpie-home', __('Users', WPIE_TEXTDOMAIN), __('Users', WPIE_TEXTDOMAIN), 'wpie_manage_users', 'wpie-users', array(&$this, 'wpie_get_page'));

        add_submenu_page('wpie-home', __('Coupons', WPIE_TEXTDOMAIN), __('Coupons', WPIE_TEXTDOMAIN), 'wpie_manage_coupons', 'wpie-coupons', array(&$this, 'wpie_get_page'));

        //add_submenu_page('wpie-home', __('Settings', WPIE_TEXTDOMAIN), __('Settings', WPIE_TEXTDOMAIN), 'wpie_manage_settings', 'wpie-settings', array(&$this, 'wpie_get_page'));
    }

    function wpie_get_page() {
        global $wpie_init;


        if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-home' && file_exists(WPIE_VIEW_DIR . '/wpie-dashboard.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-dashboard.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-home' && file_exists(WPIE_VIEW_DIR . '/wpie-dashboard.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-dashboard.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-products' && file_exists(WPIE_VIEW_DIR . '/wpie-products.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-products.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-product-categories' && file_exists(WPIE_VIEW_DIR . '/wpie-product-categories.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-product-categories.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-orders' && file_exists(WPIE_VIEW_DIR . '/wpie-orders.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-orders.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-users' && file_exists(WPIE_VIEW_DIR . '/wpie-users.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-users.php');
        } else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-coupons' && file_exists(WPIE_VIEW_DIR . '/wpie-coupons.php')) {

            require_once( WPIE_VIEW_DIR . '/wpie-coupons.php');
        }
//        else if (isset($_REQUEST['page']) && $_REQUEST['page'] == 'wpie-settings' && file_exists(WPIE_VIEW_DIR . '/wpie-settings.php')) {
//
//            require_once( WPIE_VIEW_DIR . '/wpie-settings.php');
//        }
    }

    function wpie_user_capabilities() {
        $cap = array(
            'wpie_manage_dashboard' => __('User can manage Woo Imp Exp Dashboard', WPIE_TEXTDOMAIN),
            'wpie_manage_products' => __('User can manage Products', WPIE_TEXTDOMAIN),
            'wpie_manage_orders' => __('User can manage Orders', WPIE_TEXTDOMAIN),
            'wpie_manage_users' => __('User can manage Users', WPIE_TEXTDOMAIN),
            'wpie_manage_product_categories' => __('User can manage Product Categories', WPIE_TEXTDOMAIN),
            'wpie_manage_coupons' => __('User can manage Coupons', WPIE_TEXTDOMAIN),
                //  'wpie_manage_settings' => __('User can manage Settings', WPIE_TEXTDOMAIN)
        );

        return $cap;
    }

    function get_dynamic_position($start, $increment = 0.1) {
        foreach ($GLOBALS['menu'] as $key => $menu) {
            $menus_positions[] = $key;
        }
        if (!in_array($start, $menus_positions))
            return $start;

        while (in_array($start, $menus_positions)) {
            $start += $increment;
        }
        return $start;
    }

    function wpie_replace_footer_admin() {
        echo '';
    }

    function wpie_replace_footer_version() {
        return '';
    }

    function wpie_download_exported_data() {

        if (isset($_POST['wpie-product-csv-file-name']) && file_exists($_POST['wpie-product-csv-file-name'])) {

            $pathinfo = pathinfo($_POST['wpie-product-csv-file-name']);

            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');

            header('Content-Description: File Transfer');

            header('Content-Type: text/csv;');

            header('Content-Disposition: attachment; filename=' . $pathinfo['basename']);

            header('Expires:0');

            header('Pragma: public');

            readfile($_POST['wpie-product-csv-file-name']);

            die();
        }
    }

    function wpie_activate_license() {

        $site_data = array();

        $return_value = array();

        $return_value['message'] = 'error';

        $site_data['customer_name'] = '';

        $site_data['customer_email'] = get_option('admin_email');

        $site_data['purchase_code'] = trim($_POST["purchase_code"]);

        $site_data['domain_name'] = get_option('siteurl');

        $site_info = array();

        $site_info['blog_name'] = get_bloginfo('name');

        $site_info['description'] = get_bloginfo('description');

        $site_info['site_home_url'] = home_url();

        $site_info['admin_email'] = get_bloginfo('admin_email');

        $site_info['server_addr'] = $_SERVER['SERVER_ADDR'];

        $new_str = implode("^|^", $site_info);

        $new_plugin_code = base64_encode($new_str);

        $site_data['plugin_data'] = $new_plugin_code;

        $site_data['plugin_name'] = 'woo-import-export';

        $site_data['plugin_url'] = WPIE_PLUGIN_URL;

        $site_data['plugin_version'] = WPIE_PLUGIN_VERSION;

        $site_data['plugin_status'] = 'active';

        $valstring = maybe_serialize($site_data);

        $post_data = base64_encode($valstring);

        $response = wp_remote_post(WPIE_PLUGIN_SITE, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'verify_product_purchase' => $post_data),
            'cookies' => array()
                )
        );
        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "") {

            $response_result = @maybe_unserialize(base64_decode($response["body"]));

            if (isset($response_result{'vjit_verify'}) && $response_result{'vjit_verify'} == 'success') {

                update_option('wpie_current_site_plugin_date_format', isset($response_result{'vjit_verify_data'}) ? $response_result{'vjit_verify_data'} : 1);

                $return_value['message'] = $response_result{'vjit_verify'};

                $return_value['message_note'] = $response_result['vjit_verify_note'];

                $return_value['message_content'] = $response_result{'vjit_verify_msg'};
            } else {
                $return_value['message_content'] = __('Invalid Request Data.', WPIE_TEXTDOMAIN);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_license_deacivation() {

        $site_data = array();

        $return_value = array();

        $return_value['message'] = 'error';

        $site_info = array();

        $site_info['blog_name'] = get_bloginfo('name');

        $site_info['description'] = get_bloginfo('description');

        $site_info['site_home_url'] = home_url();

        $site_info['admin_email'] = get_bloginfo('admin_email');

        $site_info['server_addr'] = $_SERVER['SERVER_ADDR'];

        $new_str = implode("^|^", $site_info);

        $wpie_date_format = get_option('wpie_current_site_plugin_date_format');

        if ($wpie_date_format && $wpie_date_format != "") {
            $plugin_data = @maybe_unserialize(base64_decode($wpie_date_format));
        } else {
            $plugin_data = "";
        }

        $new_plugin_code = base64_encode($new_str);

        $site_data['plugin_info'] = $plugin_data;

        $site_data['plugin_data'] = $new_plugin_code;

        $site_data['plugin_name'] = 'woo-import-export';

        $site_data['plugin_url'] = WPIE_PLUGIN_URL;

        $site_data['plugin_version'] = WPIE_PLUGIN_VERSION;

        $site_data['plugin_status'] = 'deactive';

        $valstring = maybe_serialize($site_data);

        $post_data = base64_encode($valstring);

        $response = wp_remote_post(WPIE_PLUGIN_SITE, array(
            'method' => 'POST',
            'timeout' => 45,
            'redirection' => 5,
            'httpversion' => '1.1',
            'blocking' => true,
            'headers' => array(),
            'body' => array(
                'vjit_deacivate_license' => $post_data),
            'cookies' => array()
                )
        );


        if (array_key_exists('body', $response) && isset($response["body"]) && $response["body"] != "") {

            $response_result = @maybe_unserialize(base64_decode($response["body"]));

            if ($response_result{'vjit_verify'} == 'success') {
                $return_value['message'] = $response_result{'vjit_verify'};

                $return_value['message_note'] = $response_result['vjit_verify_note'];

                $return_value['message_content'] = $response_result{'vjit_verify_msg'};

                delete_option('wpie_current_site_plugin_date_format');
            } else {
                $return_value['message_content'] = __('Invalid Request Data.', WPIE_TEXTDOMAIN);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_set_time_limit($time = 0) {
        $safe_mode = ini_get('safe_mode');

        if ((!$safe_mode) || strtolower($safe_mode) == 'off') {
            @set_time_limit($time);
            @ini_set("memory_limit", "-1");
        }
    }

}
