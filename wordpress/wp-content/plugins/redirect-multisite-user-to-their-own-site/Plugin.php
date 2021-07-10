<?php
/*
Plugin Name: Redirect multisite user to their own site
Plugin URI: https://wordpress.org/plugins/redirect-multisite-user-to-their-own-site/
Description: If the current user in a multisite environment accesses a subsite to which access has not been granted, then redirect the user back to their own site. This plugin requires PHP 5.3 or newer because it uses PHP namespaces.
Author: Mark Howells-Mead
Version: 1.1.1
Author URI: https://www.permanenttourist.ch/
Text Domain: redirect-multisite-user-to-their-own-site
*/

namespace MHM\MultisiteRedirectuser;

class Plugin
{
    public function __construct()
    {
        load_plugin_textdomain('redirect-multisite-user-to-their-own-site');
        if (is_multisite()) {
            add_action('parse_request', array($this, 'checkAccess'));
        }
    }

    public function checkAccess()
    {
        global $current_user, $wpdb;

        if(is_admin()) return;

        $current_site_id = (int) $wpdb->blogid;
        //dump($current_site_id);

        $user_sites = get_blogs_of_user($current_user->ID);
        //dump($user_sites,false,true);

        //dump($current_site_id);
        //$user = get_user_by( 'id', $current_user->ID );
        //$store_info = dokan_get_store_info( $user->ID );
        //$user_info = get_user_meta(  $current_user->ID , 'dokan_store_name', true );
        //dump($user_info);
        //if( isset($_GET['sid']) ){
            //setcookie('site_id', $_GET['sid'], strtotime('+2 year'));
        //}
        //dump($_SERVER[ 'HTTP_HOST' ]);
        //if( isset($_COOKIE['site_id']) && $_COOKIE['site_id']!=$current_site_id ){
            //wp_redirect( get_site_url($_COOKIE['site_id']) );
            //exit;
            //dump(get_site_url(1));
            //dump("aaaa");
        //}
       //dump(($_COOKIE['site_id']));
       //dump($current_site_id);

       //$slug = basename(get_permalink());
       //$slug = get_page();

       //$site_id = get_site_transient( 'site-id' );
       //$site_id = isset($_COOKIE['site-id']) ? $_COOKIE['site-id'] : '';
       //dump($site_id);
       //$absolute_url = full_url( $_SERVER );
       //if( $current_site_id == 1 && !empty($site_id) && ( $absolute_url=='http://192.168.1.25/fastlave/' || $absolute_url=='http://www.fastlave.com.br/' || $absolute_url=='https://www.fastlave.com.br/' ) ){
           //$url = get_site_url($site_id);
           //dump($url);
           //wp_redirect( $url );
           //exit;
       //}
       //if( isset($_GET['reset']) && $_GET['reset']=='site-id' ){
            //delete_site_transient('site-id');
            //setcookie('site-id','');
            //unset($_COOKIE['site-id']);
       //}
       //setcookie('site-id','');

        if (is_array($user_sites)) {
            switch (count($user_sites)) {
                case 0:
                    //dump('entrou 0',false, false);
                    if (is_user_logged_in()) {
                        do_action('redirect-multisite-user-to-their-own-site/no-sites', $current_user, $current_site_id);
                    }else{
                      unset($_SESSION['logged_sucesso']);
                    }
                    //dump($_SESSION['logged_sucesso'], false, false);
                    break;
                case 1:
                    //dump('entrou 1',false, false);
                    //$logged_sucesso = $_SESSION['logged_sucesso'];
                    $logged_sucesso = isset($_SESSION["logged_sucesso"]) ? $_SESSION["logged_sucesso"] : false;
                    $user_target_site = array_values($user_sites)[0];
                    //dump($user_target_site);
                    //dump($current_site_id);
                    //exit;
                    if ( (int) $user_target_site->userblog_id !== $current_site_id && !$logged_sucesso ) { //&& $current_site_id != 1

                          do_action('redirect-multisite-user-to-their-own-site/redirecting', $current_user, $current_site_id, $user_target_site->siteurl);
                          $_SESSION['logged_sucesso'] = true;
                          //$loja = get_user_meta(  $current_user->ID , 'dokan_store_name', true );

                          $user_login_acess = get_user_meta( $current_user->ID, 'user_login_acess', true );
                          //update_user_meta( $current_user->ID, 'user_login_acess', ( (int) $user_login_acess) + 1 );
        									$lavanderia = get_user_meta(  $current_user->ID , 'dokan_store_name', true );
                          //dump((int)$user_login_acess);
                          //exit;

                          //setcookie('site_id', $current_site_id, strtotime('+2 year'));


        									if( (int)$user_login_acess <= 1 && !empty($lavanderia) ){
        											wp_redirect($user_target_site->siteurl.'/?page=configuracao-lavanderia');
        									}else if( !empty($lavanderia) ){
                              wp_redirect($user_target_site->siteurl.'/area-administrativa');
                          }else{
                              wp_redirect($user_target_site->siteurl);
                          }
                          exit;

                    }
                    //dump($logged_sucesso, false, false);
                    break;
                default:
                    //dump('entrou default',false, true);
                    if (!array_key_exists($current_site_id, $user_sites)) {
                        do_action('redirect-multisite-user-to-their-own-site/not-allowed', $current_user, $current_site_id, $user_sites);
                    }
                    break;
            }
        }
    }
}

new Plugin();
