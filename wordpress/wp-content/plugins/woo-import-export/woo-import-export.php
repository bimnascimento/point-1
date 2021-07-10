<?php

/*
  Plugin Name: Woo Import Export | Shared By Themes24x7.com
  Description: Advanced WooCommerce Store Products, Orders, Users, Product Categories, Coupons data Import Export with Multiple Filter, Export Management, Field Management, Scheduled Management.
  Version: 2.3
  Author: VJInfotech
  Author URI: http://www.vjinfotech.com

 */
if (!defined('ABSPATH'))
    die("Can't load this file directly");

global $wpdb, $wpie_get_record_count, $wpie_fatch_import_records;

$wpie_get_record_count = 5;
$wpie_fatch_import_records = 15;

// Plugin version
if (!defined('WPIE_PLUGIN_VERSION')) {
    define('WPIE_PLUGIN_VERSION', '2.3');
}

// Plugin Folder Path
if (!defined('WPIE_PLUGIN_DIR')) {
    define('WPIE_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)));
}

if (is_ssl()) {
    define('WPIE_PLUGIN_URL', str_replace('http://', 'https://', WP_PLUGIN_URL . '/woo-import-export'));
    define('WPIE_CURRENT_PLUGIN_URL', str_replace('http://', 'https://', WP_PLUGIN_URL));
} else {
    define('WPIE_PLUGIN_URL', WP_PLUGIN_URL . '/woo-import-export');
    define('WPIE_CURRENT_PLUGIN_URL', WP_PLUGIN_URL);
}

if (!defined('WPIE_ASSETS_URL'))
    define('WPIE_ASSETS_URL', WPIE_PLUGIN_URL . '/assets');

if (!defined('WPIE_CSS_URL'))
    define('WPIE_CSS_URL', WPIE_ASSETS_URL . '/css');

if (!defined('WPIE_JS_URL'))
    define('WPIE_JS_URL', WPIE_ASSETS_URL . '/js');

if (!defined('WPIE_IMAGES_URL'))
    define('WPIE_IMAGES_URL', WPIE_ASSETS_URL . '/images');

if (!defined('WPIE_INCLUDES_DIR'))
    define('WPIE_INCLUDES_DIR', WPIE_PLUGIN_DIR . '/includes');

if (!defined('WPIE_CLASSES_DIR'))
    define('WPIE_CLASSES_DIR', WPIE_INCLUDES_DIR . '/classes');

if (!defined('WPIE_VIEW_DIR'))
    define('WPIE_VIEW_DIR', WPIE_INCLUDES_DIR . '/views');

if (!defined('WPIE_TEXTDOMAIN'))
    define('WPIE_TEXTDOMAIN', 'woo-import-export');

// Plugin site path
if (!defined('WPIE_PLUGIN_SITE')) {
    define('WPIE_PLUGIN_SITE', 'http://www.vjinfotech.com');
}

$wpupload_dir = wp_upload_dir();
$wpie_upload_dir = $wpupload_dir['basedir'] . '/woo-import-export';
$wpie_upload_url = $wpupload_dir['baseurl'] . '/woo-import-export';

define('WPIE_UPLOAD_DIR', $wpie_upload_dir);

define('WPIE_UPLOAD_URL', $wpie_upload_url);

wp_mkdir_p($wpie_upload_dir);

global $wpie_init, $wpie_product, $wpie_user, $wpie_order, $wpie_coupon, $wpie_product_category, $wpie_scheduled, $wpie_auto_update;

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-init.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-init.php' );

    $wpie_init = new WPIE_INIT();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-product.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-product.php' );

    $wpie_product = new WPIE_PRODUCT();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-product-category.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-product-category.php' );

    $wpie_product_category = new WPIE_PRODUCT_CATEGORY();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-user.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-user.php' );

    $wpie_user = new WPIE_USER();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-order.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-order.php' );

    $wpie_order = new WPIE_ORDER();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-coupon.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-coupon.php' );

    $wpie_coupon = new WPIE_COUPON();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-scheduled.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-scheduled.php' );

    $wpie_scheduled = new WPIE_SCHEDULED();
}

if (file_exists(WPIE_CLASSES_DIR . '/class-wpie-auto-update.php')) {
    require_once( WPIE_CLASSES_DIR . '/class-wpie-auto-update.php' );

    $wpie_auto_update = new WPIE_AUTO_UPDATE();
}