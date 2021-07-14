<?php
/**
 * Plugin Name: WP Edit Menu
 * Plugin URI: 
 * Description: Edit menu items at bulk. You can edit any menu and delete menu items quickly as desired.
 * Version: 1.0.0
 * Author: saurav.rox
 * Author URI: saurabadhikari.com.np
 * License: GPLv3 or later
 * Text Domain: wp-edit-menu
 * Domain Path: /languages/
 */
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WPEM_BASE_PATH', dirname( __FILE__ ) );

//loading main file of the plugin.
include WPEM_BASE_PATH.'/includes/class-wpem-main.php';