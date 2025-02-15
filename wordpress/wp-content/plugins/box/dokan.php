<?php
/*
Plugin Name: Dokan - Multi-vendor Marketplace
Plugin URI: https://wedevs.com/products/plugins/dokan/
Description: An e-commerce marketplace plugin for WordPress. Powered by WooCommerce and weDevs.
Version: 2.5
Author: weDevs
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2015 weDevs (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Backwards compatibility for older than PHP 5.3.0
if ( !defined( '__DIR__' ) ) {
    define( '__DIR__', dirname( __FILE__ ) );
}

define( 'DOKAN_PLUGIN_VERSION', '2.5' );
define( 'DOKAN_FILE', __FILE__ );
define( 'DOKAN_DIR', __DIR__ );
define( 'DOKAN_INC_DIR', __DIR__ . '/includes' );
define( 'DOKAN_LIB_DIR', __DIR__ . '/lib' );
define( 'DOKAN_PLUGIN_ASSEST', plugins_url( 'assets', __FILE__ ) );
// give a way to turn off loading styles and scripts from parent theme

if ( !defined( 'DOKAN_LOAD_STYLE' ) ) {
    define( 'DOKAN_LOAD_STYLE', true );
}

if ( !defined( 'DOKAN_LOAD_SCRIPTS' ) ) {
    define( 'DOKAN_LOAD_SCRIPTS', true );
}

/**
 * Autoload class files on demand
 *
 * `Dokan_Installer` becomes => installer.php
 * `Dokan_Template_Report` becomes => template-report.php
 *
 * @since 1.0
 *
 * @param string  $class requested class name
 */
function dokan_autoload( $class ) {
    if ( stripos( $class, 'Dokan_' ) !== false ) {
        $class_name = str_replace( array( 'Dokan_', '_' ), array( '', '-' ), $class );
        $file_path = __DIR__ . '/classes/' . strtolower( $class_name ) . '.php';

        if ( file_exists( $file_path ) ) {
            require_once $file_path;
        }
    }
}

spl_autoload_register( 'dokan_autoload' );

/**
 * WeDevs_Dokan class
 *
 * @class WeDevs_Dokan The class that holds the entire WeDevs_Dokan plugin
 */
final class WeDevs_Dokan {

    private $is_pro = false;

    /**
     * Constructor for the WeDevs_Dokan class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {

        if ( ! function_exists( 'WC' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            deactivate_plugins( plugin_basename( __FILE__ ) );

            wp_die( '<div class="error"><p>' . sprintf( __( '<b>Dokan</b> requires %sWoocommerce%s to be installed & activated!', 'dokan' ), '<a target="_blank" href="https://wordpress.org/plugins/woocommerce/">', '</a>' ) . '</p></div>' );
        }

        global $wpdb;

        $wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';
        $wpdb->dokan_orders   = $wpdb->prefix . 'dokan_orders';

        //includes file
        $this->includes();

        // init actions and filter
        $this->init_filters();
        $this->init_actions();

        // initialize classes
        $this->init_classes();

        //for reviews ajax request
        $this->init_ajax();

        do_action( 'dokan_loaded' );
    }

    /**
     * Initializes the WeDevs_Dokan() class
     *
     * Checks for an existing WeDevs_WeDevs_Dokan() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new WeDevs_Dokan();
        }

        return $instance;
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Get the template path.
     *
     * @return string
     */
    public function template_path() {
        return apply_filters( 'dokan_template_path', 'dokan/' );
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public static function activate() {
        if ( ! function_exists( 'WC' ) ) {
            return;
        }

        global $wpdb;

        $wpdb->dokan_withdraw     = $wpdb->prefix . 'dokan_withdraw';
        $wpdb->dokan_orders       = $wpdb->prefix . 'dokan_orders';
        $wpdb->dokan_announcement = $wpdb->prefix . 'dokan_announcement';
        $wpdb->dokan_refund       = $wpdb->prefix . 'dokan_refund';

        require_once __DIR__ . '/includes/functions.php';

        $installer = new Dokan_Installer();
        $installer->do_install();
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public static function deactivate() {

    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dokan', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    function init_actions() {

        // Localize our plugin
        add_action( 'admin_init', array( $this, 'load_table_prifix' ) );

        add_action( 'init', array( $this, 'localization_setup' ) );
        add_action( 'init', array( $this, 'register_scripts' ) );

        add_action( 'template_redirect', array( $this, 'redirect_if_not_logged_seller' ), 11 );

        add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'login_enqueue_scripts', array( $this, 'login_scripts') );

        // add_action( 'admin_init', array( $this, 'install_theme' ) );
        add_action( 'admin_init', array( $this, 'block_admin_access' ) );
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links' ) );
    }

    public function register_scripts() {
        $suffix   = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

        // register styles
        wp_register_style( 'jquery-ui', plugins_url( 'assets/css/jquery-ui-1.10.0.custom.css', __FILE__ ), false, null );
        wp_register_style( 'fontawesome', plugins_url( 'assets/css/font-awesome.min.css', __FILE__ ), false, null );
        wp_register_style( 'washicons', plugins_url( 'assets/css/washicons.css', __FILE__ ), false, null );
        wp_register_style( 'dokan-extra', plugins_url( 'assets/css/dokan-extra.css', __FILE__ ), false, null );
        wp_register_style( 'dokan-style', plugins_url( 'assets/css/style.css', __FILE__ ), false, null );
        wp_register_style( 'dokan-chosen-style', plugins_url( 'assets/css/chosen.min.css', __FILE__ ), false, null );
        wp_register_style( 'dokan-magnific-popup', plugins_url( 'assets/css/magnific-popup.css', __FILE__ ), false, null );
        wp_register_style( 'modalcss', plugins_url( 'assets/css/modalcss.css', __FILE__ ), false, null );

        // register scripts
        wp_register_script( 'jquery-flot', plugins_url( 'assets/js/flot-all.min.js', __FILE__ ), false, null, true );
        wp_register_script( 'jquery-chart', plugins_url( 'assets/js/Chart.min.js', __FILE__ ), false, null, true );
        wp_register_script( 'dokan-tabs-scripts', plugins_url( 'assets/js/jquery.easytabs.min.js', __FILE__ ), false, null, true );
        wp_register_script( 'dokan-hashchange-scripts', plugins_url( 'assets/js/jquery.hashchange.min.js', __FILE__ ), false, null, true );
        wp_register_script( 'dokan-tag-it', plugins_url( 'assets/js/tag-it.min.js', __FILE__ ), array( 'jquery' ), null, true );
        wp_register_script( 'chosen', plugins_url( 'assets/js/chosen.jquery.min.js', __FILE__ ), array( 'jquery' ), null, true );
        wp_register_script( 'dokan-popup', plugins_url( 'assets/js/jquery.magnific-popup.min.js', __FILE__ ), array( 'jquery' ), null, true );
        wp_register_script( 'modaljs', plugins_url( 'assets/js/modaljs.js', __FILE__ ), false, null, true );
        wp_register_script( 'bootstrap-tooltip', plugins_url( 'assets/js/bootstrap-tooltips.js', __FILE__ ), false, null, true );
        wp_register_script( 'form-validate', plugins_url( 'assets/js/form-validate.js', __FILE__ ), array( 'jquery' ), null, true  );

        // these two is required for image croping functionalities written in dokan-script
        wp_register_script( 'customize-base', site_url( 'wp-includes/js/customize-base.js' ), array( 'jquery', 'json2', 'underscore' ), null, true );
        wp_register_script( 'customize-model', site_url( 'wp-includes/js/customize-models.js' ), array( 'underscore', 'backbone' ), null, true );


        wp_register_script( 'dokan-script', plugins_url( 'assets/js/all.js', __FILE__ ), array( 'imgareaselect', 'customize-base', 'customize-model' ), null, true );
        wp_register_script( 'dokan-product-shipping', plugins_url( 'assets/js/single-product-shipping.js', __FILE__ ), false, null, true );

        if ( $this->is_pro() ) {
            wp_register_script( 'accounting', WC()->plugin_url() . '/assets/js/accounting/accounting' . $suffix . '.js', array( 'jquery' ), '0.3.2' );
        }
        wp_register_script( 'dokan-frontend-script', plugins_url( 'assets/js/frontend-script.js', __FILE__ ), false, null, true );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function scripts() {

        wp_enqueue_script( 'dokan-frontend-script' );

        $dokan_plugin = array(
            'url'     => plugins_url( '', __FILE__ ),
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
        );

        wp_localize_script( 'dokan-frontend-script', 'dokan_plugin', $dokan_plugin );

        if ( is_singular( 'product' ) && !get_query_var( 'edit' ) ) {
            wp_enqueue_script( 'dokan-product-shipping' );
            $localize_script = array(
                'ajaxurl'     => admin_url( 'admin-ajax.php' ),
                'nonce'       => wp_create_nonce( 'dokan_reviews' ),
                'ajax_loader' => plugins_url( 'assets/images/ajax-loader.gif', __FILE__ ),
                'seller'      => array(
                    'available'    => __( 'Available', 'dokan' ),
                    'notAvailable' => __( 'Not Available', 'dokan' )
                ),
                'delete_confirm' => __('Você tem certeza?', 'dokan' ),
                'wrong_message' => __('Algo deu errado. Por favor, tente novamente.', 'dokan' ),
            );
            wp_localize_script( 'jquery', 'dokan', $localize_script );
        }

        $page_id = dokan_get_option( 'dashboard', 'dokan_pages' );

        // bailout if not dashboard
        if ( ! $page_id ) {
            return;
        }

        if ( ! function_exists( 'WC' ) ) {
            return;
        }

        $localize_script = array(
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'dokan_reviews' ),
            'ajax_loader' => plugins_url( 'assets/images/ajax-loader.gif', __FILE__ ),
            'seller'      => array(
                'available'    => __( 'Available', 'dokan' ),
                'notAvailable' => __( 'Not Available', 'dokan' )
            ),
            'delete_confirm' => __('Você tem certeza?', 'dokan' ),
            'wrong_message' => __('Algo deu errado. Por favor, tente novamente.', 'dokan' ),
            'duplicates_attribute_messg' => __( 'Essa opção já existe, tente outra.', 'dokan' ),
            'variation_unset_warning' => __( 'Warning! This product will not have any variations if this option is not checked.', 'dokan' ),
        );

        $form_validate_messages = array(
            'required'        => __( "This field is required", 'dokan' ),
            'remote'          => __( "Please fix this field.", 'dokan' ),
            'email'           => __( "Please enter a valid email address." , 'dokan' ),
            'url'             => __( "Please enter a valid URL." , 'dokan' ),
            'date'            => __( "Please enter a valid date." , 'dokan' ),
            'dateISO'         => __( "Please enter a valid date (ISO)." , 'dokan' ),
            'number'          => __( "Please enter a valid number." , 'dokan' ),
            'digits'          => __( "Please enter only digits." , 'dokan' ),
            'creditcard'      => __( "Please enter a valid credit card number." , 'dokan' ),
            'equalTo'         => __( "Please enter the same value again." , 'dokan' ),
            'maxlength_msg'   => __( "Please enter no more than {0} characters." , 'dokan' ),
            'minlength_msg'   => __( "Please enter at least {0} characters." , 'dokan' ),
            'rangelength_msg' => __( "Please enter a value between {0} and {1} characters long." , 'dokan' ),
            'range_msg'       => __( "Please enter a value between {0} and {1}." , 'dokan' ),
            'max_msg'         => __( "Please enter a value less than or equal to {0}." , 'dokan' ),
            'min_msg'         => __( "Please enter a value greater than or equal to {0}." , 'dokan' ),
        );

        wp_localize_script( 'form-validate', 'DokanValidateMsg', $form_validate_messages );

        if ( $this->is_pro() ) {
            $general_settings = get_option( 'dokan_general', [] );
            $banner_width = ! empty( $general_settings['store_banner_width'] ) ? $general_settings['store_banner_width'] : 625;
            $banner_height = ! empty( $general_settings['store_banner_height'] ) ? $general_settings['store_banner_height'] : 300;
            $has_flex_width = ! empty( $general_settings['store_banner_flex_width'] ) ? $general_settings['store_banner_flex_width'] : true;
            $has_flex_height = ! empty( $general_settings['store_banner_flex_height'] ) ? $general_settings['store_banner_flex_height'] : true;

            $dokan_refund = array(
                'mon_decimal_point'             => wc_get_price_decimal_separator(),
                'remove_item_notice'            => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'dokan' ),
                'i18n_select_items'             => __( 'Please select some items.', 'dokan' ),
                'i18n_do_refund'                => __( 'Are you sure you wish to process this refund request? This action cannot be undone.', 'dokan' ),
                'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'dokan' ),
                'remove_item_meta'              => __( 'Remove this item meta?', 'dokan' ),
                'ajax_url'                      => admin_url( 'admin-ajax.php' ),
                'order_item_nonce'              => wp_create_nonce( 'order-item' ),
                'post_id'                       => isset( $_GET['order_id'] ) ? $_GET['order_id'] : '',
                'currency_format_num_decimals'  => wc_get_price_decimals(),
                'currency_format_symbol'        => get_woocommerce_currency_symbol(),
                'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
                'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
                'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS
                'rounding_precision'            => wc_get_rounding_precision(),
                'store_banner_dimension'        => [ 'width' => $banner_width, 'height' => $banner_height, 'flex-width' => $has_flex_width, 'flex-height' => $has_flex_height ],
                'selectAndCrop'                 => __( 'Select and Crop' ),
                'chooseImage'                   => __( 'Choose Image', 'dokan' )
            );

            wp_localize_script( 'dokan-script', 'dokan_refund', $dokan_refund );
            wp_enqueue_script( 'accounting' );
        }

        // load only in dokan dashboard and edit page
        if ( is_page( $page_id ) || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) {


            if ( DOKAN_LOAD_STYLE ) {
                wp_enqueue_style( 'jquery-ui' );
                wp_enqueue_style( 'fontawesome' );
                wp_enqueue_style( 'washicons' );
                wp_enqueue_style( 'dokan-extra' );
                wp_enqueue_style( 'dokan-style' );
                wp_enqueue_style( 'dokan-magnific-popup' );
                wp_enqueue_style( 'woocommerce-general' );
                wp_enqueue_style( 'modalcss' );
            }

            if ( DOKAN_LOAD_SCRIPTS ) {

                $scheme       = is_ssl() ? 'https' : 'http';
                $api_key      = dokan_get_option( 'gmap_api_key', 'dokan_general' );

                //wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?key=' . $api_key );

                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui' );
                wp_enqueue_script( 'jquery-ui-autocomplete' );
                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_enqueue_script( 'underscore' );
                wp_enqueue_script( 'post' );
                wp_enqueue_script( 'dokan-tag-it' );
                wp_enqueue_script( 'modaljs' );
                wp_enqueue_script( 'bootstrap-tooltip' );
                wp_enqueue_script( 'form-validate' );
                wp_enqueue_script( 'dokan-tabs-scripts' );
                wp_enqueue_script( 'jquery-chart' );
                wp_enqueue_script( 'jquery-flot' );
                wp_enqueue_script( 'chosen' );
                wp_enqueue_media();
                wp_enqueue_script( 'dokan-popup' );
                wp_enqueue_script( 'wc-password-strength-meter' );

                wp_enqueue_script( 'dokan-script' );
                wp_localize_script( 'jquery', 'dokan', $localize_script );            }
        }

        // store and my account page
        $custom_store_url = dokan_get_option( 'custom_store_url', 'dokan_general', 'store' );
        //if ( get_query_var( $custom_store_url ) || get_query_var( 'store_review' ) || is_account_page() ) {

            if ( DOKAN_LOAD_STYLE ) {
                wp_enqueue_style( 'fontawesome' );
                wp_enqueue_style( 'washicons' );
                wp_enqueue_style( 'dokan-style' );
            }

            if ( DOKAN_LOAD_SCRIPTS ) {
                $scheme       = is_ssl() ? 'https' : 'http';
                $api_key      = dokan_get_option( 'gmap_api_key', 'dokan_general' );

                //wp_enqueue_script( 'google-maps', $scheme . '://maps.google.com/maps/api/js?key=' . $api_key );

                wp_enqueue_script( 'jquery-ui-sortable' );
                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_enqueue_script( 'bootstrap-tooltip' );
                wp_enqueue_script( 'chosen' );
                wp_enqueue_script( 'form-validate' );
                wp_enqueue_script( 'dokan-script' );
                wp_localize_script( 'jquery', 'dokan', $localize_script );
            }
        //}

        // load dokan style on every pages. requires for shortcodes in other pages
        if ( DOKAN_LOAD_STYLE ) {
            wp_enqueue_style( 'dokan-style' );
            wp_enqueue_style( 'washicons' );
            wp_enqueue_style( 'fontawesome' );
        }

        //load country select js in seller settings store template
        global $wp;
        if ( isset( $wp->query_vars['settings'] ) == 'store' ) {
            wp_enqueue_script( 'wc-country-select' );
        }

        do_action( 'dokan_after_load_script' );
    }


    /**
     * Include all the required files
     *
     * @return void
     */
    function includes() {
        $lib_dir     = __DIR__ . '/lib/';
        $inc_dir     = __DIR__ . '/includes/';
        $classes_dir = __DIR__ . '/classes/';

        require_once $inc_dir . 'functions.php';
        require_once $inc_dir . 'widgets/menu-category.php';
        require_once $inc_dir . 'widgets/store-menu-category.php';
        require_once $inc_dir . 'widgets/bestselling-product.php';
        require_once $inc_dir . 'widgets/top-rated-product.php';
        require_once $inc_dir . 'widgets/store-menu.php';
        require_once $inc_dir . 'wc-functions.php';
        require_once $lib_dir . 'class-wedevs-insights.php';
        require_once $inc_dir . '/admin/setup-wizard.php';
        require_once $classes_dir . 'seller-setup-wizard.php';

        // Load free or pro moduels
        if ( file_exists( DOKAN_INC_DIR . '/pro/dokan-pro-loader.php' ) ) {
            include_once DOKAN_INC_DIR . '/pro/dokan-pro-loader.php';

            $this->is_pro = true;
        } else if ( file_exists( DOKAN_INC_DIR . '/free/dokan-free-loader.php' ) ) {
            include_once DOKAN_INC_DIR . '/free/dokan-free-loader.php';
        }

        require_once $inc_dir . 'wc-template.php';

        if ( is_admin() ) {
            require_once $inc_dir . 'admin/admin.php';
            require_once $inc_dir . 'admin/ajax.php';
            require_once $inc_dir . 'admin-functions.php';
        } else {
            require_once $inc_dir . 'template-tags.php';
        }

    }

    /**
     * Initialize filters
     *
     * @return void
     */
    function init_filters() {
        add_filter( 'posts_where', array( $this, 'hide_others_uploads' ) );
        add_filter( 'body_class', array( $this, 'add_dashboard_template_class' ), 99 );
        add_filter( 'wp_title', array( $this, 'wp_title' ), 20, 2 );
    }

    /**
     * Hide other users uploads for `seller` users
     *
     * Hide media uploads in page "upload.php" and "media-upload.php" for
     * sellers. They can see only thier uploads.
     *
     * FIXME: fix the upload counts
     *
     * @global string $pagenow
     * @global object $wpdb
     *
     * @param string  $where
     *
     * @return string
     */
    function hide_others_uploads( $where ) {
        global $pagenow, $wpdb;

        if ( ( $pagenow == 'upload.php' || $pagenow == 'media-upload.php' ) && current_user_can( 'dokandar' ) ) {
            $user_id = get_current_user_id();

            $where .= " AND $wpdb->posts.post_author = $user_id";
        }

        return $where;
    }

    /**
     * Init ajax classes
     *
     * @return void
     */
    function init_ajax() {
        $doing_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

        if ( $doing_ajax ) {
            Dokan_Ajax::init()->init_ajax();
            new Dokan_Pageviews();
        }
    }

    /**
     * Init all the classes
     *
     * @return void
     */
    function init_classes() {
        if ( is_admin() ) {
            new Dokan_Admin_User_Profile();
            Dokan_Admin_Ajax::init();
            new Dokan_Upgrade();
        } else {
            new Dokan_Pageviews();
        }

        new Dokan_Rewrites();
        new Dokan_Tracker();
        Dokan_Email::init();

        if ( is_user_logged_in() ) {
            Dokan_Template_Main::init();
            Dokan_Template_Dashboard::init();
            Dokan_Template_Products::init();
            Dokan_Template_Orders::init();
            Dokan_Template_Withdraw::init();
            Dokan_Template_Shortcodes::init();
            Dokan_Template_Settings::init();
        }
    }

    /**
     * Redirect if not logged Seller
     *
     * @since 2.4
     *
     * @return void [redirection]
     */
    function redirect_if_not_logged_seller() {
        global $post;

        $page_id = dokan_get_option( 'dashboard', 'dokan_pages' );

        if ( ! $page_id ) {
            return;
        }

        if ( is_page( $page_id ) ) {
            dokan_redirect_login();
            dokan_redirect_if_not_seller();
        }
    }

    /**
     * Block user access to admin panel for specific roles
     *
     * @global string $pagenow
     */
    function block_admin_access() {
        global $pagenow, $current_user;

        // bail out if we are from WP Cli
        if ( defined( 'WP_CLI' ) ) {
            return;
        }

        $no_access   = dokan_get_option( 'admin_access', 'dokan_general', 'on' );
        $valid_pages = array( 'admin-ajax.php', 'admin-post.php', 'async-upload.php', 'media-upload.php' );
        $user_role   = reset( $current_user->roles );

        if ( ( $no_access == 'on' ) && ( !in_array( $pagenow, $valid_pages ) ) && in_array( $user_role, array( 'seller', 'customer' ) ) ) {
            wp_redirect( home_url() );
            exit;
        }
    }

    /**
     * Load jquery in login page
     *
     * @since 2.4
     *
     * @return void
     */
    function login_scripts() {
        wp_enqueue_script( 'jquery' );
    }

    /**
     * Scripts and styles for admin panel
     */
    function admin_enqueue_scripts() {
        wp_enqueue_script( 'dokan_slider_admin', DOKAN_PLUGIN_ASSEST.'/js/admin.js', array( 'jquery' ) );

        if ( $this->is_pro() ) {
            $dokan_refund = array(
                'mon_decimal_point'             => wc_get_price_decimal_separator(),
                'remove_item_notice'            => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'dokan' ),
                'i18n_select_items'             => __( 'Please select some items.', 'dokan' ),
                'i18n_do_refund'                => __( 'Are you sure you wish to process this refund request? This action cannot be undone.', 'dokan' ),
                'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'dokan' ),
                'remove_item_meta'              => __( 'Remove this item meta?', 'dokan' ),
                'ajax_url'                      => admin_url( 'admin-ajax.php' ),
                'order_item_nonce'              => wp_create_nonce( 'order-item' ),
                'post_id'                       => isset( $_GET['order_id'] ) ? $_GET['order_id'] : '',
                'currency_format_num_decimals'  => wc_get_price_decimals(),
                'currency_format_symbol'        => get_woocommerce_currency_symbol(),
                'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
                'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
                'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS
                'rounding_precision'            => wc_get_rounding_precision(),
            );

            wp_localize_script( 'dokan_slider_admin', 'dokan_refund', $dokan_refund );
        }
    }

    /**
     * Load table prefix for withdraw and orders table
     *
     * @since 1.0
     *
     * @return void
     */
    function load_table_prifix() {
        global $wpdb;

        $wpdb->dokan_withdraw = $wpdb->prefix . 'dokan_withdraw';
        $wpdb->dokan_orders   = $wpdb->prefix . 'dokan_orders';
    }

    /**
     * Add body class for dokan-dashboard
     *
     * @param array $classes
     */
    function add_dashboard_template_class( $classes ) {
        $page_id = dokan_get_option( 'dashboard', 'dokan_pages' );

        if ( ! $page_id ) {
            return $classes;
        }

        if ( is_page( $page_id ) || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) ) {
            $classes[] = 'dokan-dashboard';
        }

        if ( dokan_is_store_page () ) {
            $classes[] = 'dokan-store';
        }

        $classes[] = 'dokan-theme-' . get_option( 'template' );

        return $classes;
    }


    /**
     * Create a nicely formatted and more specific title element text for output
     * in head of document, based on current view.
     *
     * @since Dokan 1.0.4
     *
     * @param string  $title Default title text for current view.
     * @param string  $sep   Optional separator.
     *
     * @return string The filtered title.
     */
    function wp_title( $title, $sep ) {
        global $paged, $page;

        if ( is_feed() ) {
            return $title;
        }

        if ( dokan_is_store_page() ) {
            $site_title = get_bloginfo( 'name' );
            $store_user = get_userdata( get_query_var( 'author' ) );
            $store_info = dokan_get_store_info( $store_user->ID );
            $store_name = esc_html( $store_info['store_name'] );
            $title      = "$store_name $sep $site_title";

            // Add a page number if necessary.
            if ( $paged >= 2 || $page >= 2 ) {
                $title = "$title $sep " . sprintf( __( 'Page %s', 'dokan' ), max( $paged, $page ) );
            }

            return $title;
        }

        return $title;
    }

    /**
     * Returns if the plugin is in PRO version
     *
     * @since 2.4
     *
     * @return boolean
     */
    public function is_pro() {
        return $this->is_pro;
    }

    /**
     * Plugin action links
     *
     * @param  array  $links
     *
     * @since  2.4
     *
     * @return array
     */
    function plugin_action_links( $links ) {

        if ( ! $this->is_pro() ) {
            $links[] = '<a href="https://wedevs.com/products/plugins/dokan/" target="_blank">' . __( 'Get PRO', 'dokan' ) . '</a>';
        }

        $links[] = '<a href="' . admin_url( 'admin.php?page=dokan-settings' ) . '">' . __( 'Settings', 'dokan' ) . '</a>';
        $links[] = '<a href="http://docs.wedevs.com/category/plugins/dokan-plugins/" target="_blank">' . __( 'Documentation', 'dokan' ) . '</a>';

        return $links;
    }

} // WeDevs_Dokan

/**
 * Load Dokan Plugin when all plugins loaded
 *
 * @return void
 */
function dokan_load_plugin() {
    WeDevs_Dokan::init();
}

add_action( 'plugins_loaded', 'dokan_load_plugin', 5 );

register_activation_hook( __FILE__, array( 'WeDevs_Dokan', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WeDevs_Dokan', 'deactivate' ) );
add_action( 'activated_plugin', array( 'Dokan_Installer', 'setup_page_redirect' ) );
