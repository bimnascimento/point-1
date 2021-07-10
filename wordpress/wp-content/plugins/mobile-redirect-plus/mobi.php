<?php
/*
Plugin Name: Mobile Redirect Plus
Plugin URI: http://iqbalbary.com/item/wp-redirect/
Description: Detect the mobile device and redirect to mobile optimize website
Version: 2.3
Author: Iqbal Bary <contact@iqbalbary.com>
Author URI: http://iqbalbary.com
*/

require_once 'includes/settings.php';

add_action('init', 'mobi_plus_redirect');
function mobi_plus_redirect() {
	//call the script
	require_once 'includes/Mobile_Detect.php';
	$detect = new Mobile_Detect_Plus;

	//Get all option for Redirect setting
	$red_plus = (array)get_option('mobi-setting');

	//initial count set
	add_option( 'mobi_count_ipad', '0', '', 'yes' );
	add_option( 'mobi_count_iphone', '0', '', 'yes' );
	add_option( 'mobi_count_android', '0', '', 'yes' );
	add_option( 'mobi_count_windowsphone', '0', '', 'yes' );
	add_option( 'mobi_count_other', '0', '', 'yes' );

	//Mobile Count Option
	function mobi_count_mobile($detect){
		if($detect->is('iPad')){
			$ipad_count = get_option( 'mobi_count_ipad' );
			update_option( 'mobi_count_ipad', $ipad_count+1 );
		}elseif($detect->isiOS()){
			$iphone_count = get_option( 'mobi_count_iphone' );
			update_option( 'mobi_count_iphone', $iphone_count+1 );
		} elseif($detect->isAndroidOS()){
			$android_count = get_option( 'mobi_count_android' );
			update_option( 'mobi_count_android', $android_count+1 );
		} elseif ($detect->is('WindowsPhoneOS')) {
			$windowsphone_count = get_option( 'mobi_count_windowsphone' );
			update_option( 'mobi_count_windowsphone', $windowsphone_count+1 );
		} else{
			$other_count = get_option( 'mobi_count_other' );
			update_option( 'mobi_count_other', $other_count+1 );
		}
	}

	//Check the session
	$session_check = $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	if(substr_count($session_check, 'main=true')>0){
		setcookie('fullsite','true');
		return;
	} 

	//Check Specific Page
	$check_url = str_replace('http://', '', $session_check);
	$check_url = str_replace('www.', '', $check_url);

	@$specific_page = str_replace('http://', '', $red_plus['specific_page']);
	@$specific_page = str_replace('www.', '', $specific_page);

	if(@$red_plus['redirect_page'] === 'no' && $check_url != $specific_page ){
		return;
	}

	//Check and Redirect
	if(!isset($_COOKIE['fullsite']) && $red_plus['redirect'] === 'yes'){
		//Check Tablet
		if($detect->isTablet()){
			if($red_plus['redirect_tab'] === 'yes'){
				return;
			}else{
				if($detect->is('iPad') && isset($red_plus['tab_ipad']) && !empty($red_plus['tab_ipad'])){
					$link_redirect = $red_plus['tab_ipad'];
				}elseif(isset($red_plus['tab_all']) && !empty($red_plus['tab_all'])){
					$link_redirect = $red_plus['tab_all'];
				}else{
					$link_redirect = $red_plus['link'];
				} 
				mobi_count_mobile($detect);wp_redirect( $link_redirect, 302 );exit();
			}
		}

		//Check mobile
		if($detect->isMobile()){
			if($detect->isiOS() && isset($red_plus['mobi_iphone']) && !empty($red_plus['mobi_iphone'])){
				$link_redirect = $red_plus['mobi_iphone'];
			}elseif($detect->isAndroidOS() && isset($red_plus['mobi_android']) && !empty($red_plus['mobi_android'])){
				$link_redirect = $red_plus['mobi_android'];
			}elseif($detect->is('WindowsPhoneOS') && isset($red_plus['mobi_windows']) && !empty($red_plus['mobi_windows'])){
				$link_redirect = $red_plus['mobi_windows'];
			}else{
				$link_redirect = $red_plus['link'];
			}	
			mobi_count_mobile($detect);wp_redirect( $link_redirect, 302 );exit();	
		}
	}
}