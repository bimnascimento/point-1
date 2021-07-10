<?php

/**
 * Plugin Name: Enable/Disable Auto Login when Register
 * Plugin URI: https://aftabhusain.wordpress.com/
 * Description: The plugin provides  feature to enable/disable auto login when user register
 * Version: 1.0.0
 * Author: Aftab Husain
 * Author URI: https://aftabhusain.wordpress.com/
 * Author Email: amu02.aftab@gmail.com
 * License: GPLv2
 */

//define('WP_DEBUG',true);
define('ALWR_REGISTRATION_PAGE_DIRECTORY', plugin_dir_path(__FILE__).'pages/');
ob_start();
global $wpdb;


// New menu submenu for plugin options in Settings menu
add_action('admin_menu', 'aft_options_menu');
function aft_options_menu() {
	add_options_page('Auto Login when Resister', 'Auto Login when Resister', 'manage_options', 'auto_login_when_register_setting', 'alwr_logo_plugin_pages');
	
}

function alwr_logo_plugin_pages() {

   $itm = ALWR_REGISTRATION_PAGE_DIRECTORY.$_GET["page"].'.php';
   include($itm);
}

//code to auto login start
$alwr_enable_auto_login= get_option('alwr_enable_auto_login');


function alwr_auto_user_auto_log_in($user_id){
  if(!is_user_logged_in()){
    $secure_cookie = is_ssl();
    $secure_cookie = apply_filters('secure_signon_cookie', $secure_cookie, array());
    global $auth_secure_cookie;
    $auth_secure_cookie = $secure_cookie;
    
    wp_set_auth_cookie($user_id, true, $secure_cookie);
    $user_info = get_userdata($user_id);
    do_action('wp_login', $user_info->user_login, $user_info);

  }
}
if($alwr_enable_auto_login == 'true'){
add_action('user_register', 'alwr_auto_user_auto_log_in');
}

//code to auto login end
?>
