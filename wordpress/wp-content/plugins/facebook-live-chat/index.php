<?php
/**
 * @package Ninjateam livechat
 * @version 2.6
 */
/*
Plugin Name: Facebook Live Chat For WordPress
Plugin URI: http://ninjateam.org/facebook-live-chat/
Description: Facebook Live Chat is a WordPress plugin allow put your live chat box on your website, visitors can chat with you via Facebook.
Author: NinjaTeam
Version: 2.6
Author URI: http://ninjateam.org
*/
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
define( 'NJ_LIVE_CHAT__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'NJ_LIVE_CHAT__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
/*
* Add default options active plugin
*/
function ninja_live_fb_chat_on_plugin_activation() {
    if ( is_multisite() ) {
        $blog_list = get_blog_list( 0, 'all' );
        foreach ($blog_list AS $blog) { 
            add_blog_option($blog['blog_id'],"live_chat_facebook_enable","ok"); // Enable Chat
            add_blog_option($blog['blog_id'],"live_chat_facebook_backgroud","#3a5897"); // Main Color
            add_blog_option($blog['blog_id'],"live_chat_facebook_open","Open Chat"); 
            add_blog_option($blog['blog_id'],"live_chat_facebook_title","Facebook Chat");
            add_blog_option($blog['blog_id'],"live_chat_facebook_user","https://www.facebook.com/ninjateam.org"); 
            add_blog_option($blog['blog_id'],"live_chat_facebook_text","Hello! Thanks for visiting us. Please press Start button to chat with our support :)"); 
            add_blog_option($blog['blog_id'],"live_chat_facebook_start","Start");
            add_blog_option($blog['blog_id'],"live_chat_facebook_start_disktop","1");  
            add_blog_option($blog['blog_id'],"live_chat_facebook_lang","en_US");
            add_blog_option($blog['blog_id'],"live_chat_facebook_enable_hello","ok`");                
        }
    }else{
        add_option("live_chat_facebook_enable","ok"); // Enable Chat
        add_option("live_chat_facebook_backgroud","#3a5897"); // Main Color
        add_option("live_chat_facebook_open","Open Chat"); 
        add_option("live_chat_facebook_title","Facebook Chat");
        add_option("live_chat_facebook_user","https://www.facebook.com/ninjateam.org"); 
        add_option("live_chat_facebook_text","Hello! Thanks for visiting us. Please press Start button to chat with our support :)"); 
        add_option("live_chat_facebook_start","Start");
        add_option("live_chat_facebook_start_disktop","1");  
        add_option("live_chat_facebook_lang","en_US");
        add_option("live_chat_facebook_enable_hello","ok");  
    }
   
}
register_activation_hook( __FILE__, 'ninja_live_fb_chat_on_plugin_activation' );
/*
* Include Back-end
*/
include NJ_LIVE_CHAT__PLUGIN_DIR."backend/index.php";

/*
* Multisite
*/
add_action( 'wpmu_new_blog', 'nj_chat_wpmu_new_blog_update_option', 10, 6 );
function nj_chat_wpmu_new_blog_update_option( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
    add_blog_option($blog_id,"live_chat_facebook_enable","ok"); // Enable Chat
    add_blog_option($blog_id,"live_chat_facebook_backgroud","#3a5897"); // Main Color
    add_blog_option($blog_id,"live_chat_facebook_open","Open Chat"); 
    add_blog_option($blog_id,"live_chat_facebook_title","Facebook Chat");
    add_blog_option($blog_id,"live_chat_facebook_user","https://www.facebook.com/ninjateam.org"); 
    add_blog_option($blog_id,"live_chat_facebook_text","Hello! Thanks for visiting us. Please press Start button to chat with our support :)"); 
    add_blog_option($blog_id,"live_chat_facebook_start","Start");
    add_blog_option($blog_id,"live_chat_facebook_start_disktop","1");  
    add_blog_option($blog_id,"live_chat_facebook_lang","en_US");
    add_blog_option($blog_id,"live_chat_facebook_enable_hello","ok");    
}
/*
* Plugin Dokan 
*/
if ( in_array( 'dokan-lite/dokan.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
   /*
    * Include Font-end dokan
    */
    if ( get_option("live_chat_facebook_enable") ) {
      include NJ_LIVE_CHAT__PLUGIN_DIR."fontend/dokan_index.php";  
      include NJ_LIVE_CHAT__PLUGIN_DIR."backend/dokan_shortcode_settings.php"; 
    }
}else{
    /*
    * Include Font-end
    */
    if ( get_option("live_chat_facebook_enable") ) {
      include NJ_LIVE_CHAT__PLUGIN_DIR."fontend/index.php";  
    }
}

