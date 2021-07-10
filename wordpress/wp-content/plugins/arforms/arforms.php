<?php @session_start();
@error_reporting(E_ERROR | E_WARNING | E_PARSE);
//@error_reporting(E_ALL);
/*
Plugin Name: ARForms
Description: Exclusive Wordpress Form Builder Plugin With Seven Most Popular E-Mail Marketing Tools Integration
Version: 2.7.8
Plugin URI: http://www.arformsplugin.com/
Author: Repute InfoSystems
Author URI: http://reputeinfosystems.com/
Text Domain: ARForms
*/

if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false))
        	header('X-UA-Compatible: IE=edge,chrome=1');
			
define('ARFPLUGINTITLE', 'ARForms');
define('ARFPLUGINNAME', 'ARForms');
define('FORMPATH', WP_PLUGIN_DIR.'/arforms');
define('MODELS_PATH', FORMPATH.'/core/models');
define('VIEWS_PATH', FORMPATH.'/core/views');
define('HELPERS_PATH', FORMPATH.'/core/helpers');
define('CONTROLLERS_PATH', FORMPATH.'/core/controllers');
define('TEMPLATES_PATH', FORMPATH.'/core/templates');
define('AUTORESPONDER_PATH', FORMPATH.'/core/ar/');
define('WP_FB_SESSION',session_id());
@define('FS_METHOD','direct');

global $arfsiteurl;
$arfsiteurl = home_url();
if(is_ssl() and (!preg_match('/^https:\/\/.*\..*$/', $arfsiteurl) or !preg_match('/^https:\/\/.*\..*$/', WP_PLUGIN_URL))){
    $arfsiteurl = str_replace('http://', 'https://', $arfsiteurl);
    define('ARFURL', str_replace('http://', 'https://', WP_PLUGIN_URL.'/arforms'));
}else
    define('ARFURL', WP_PLUGIN_URL.'/arforms');
	

if( !defined('ARF_FILEDRAG_SCRIPT_URL') ){
    define('ARF_FILEDRAG_SCRIPT_URL',plugins_url('',__FILE__));
}

define('ARFSCRIPTURL', $arfsiteurl . (is_admin() ? '/wp-admin' : '') .'/index.php?plugin=ARForms');
define('ARFIMAGESURL', ARFURL.'/images');
define('ARFAWEBERURL',ARFURL.'/core/ar/aweber/configuration.php');
require_once(FORMPATH.'/core/wp_ar_auto_update.php');
require_once(MODELS_PATH.'/arsettingmodel.php');
require_once(MODELS_PATH.'/arstylemodel.php');




$wp_upload_dir 	= wp_upload_dir();
$imageupload_dir = $wp_upload_dir['basedir'].'/arforms/userfiles/';
$imageupload_dir_sub = $wp_upload_dir['basedir'].'/arforms/userfiles/thumbs/';

if(!is_dir($imageupload_dir))
	wp_mkdir_p($imageupload_dir);	

if(!is_dir($imageupload_dir_sub))
	wp_mkdir_p($imageupload_dir_sub);	

if (!defined ('IS_WPMU')){
   global $wpmu_version;
    $is_wpmu = ((function_exists('is_multisite') and is_multisite()) or $wpmu_version) ? 1 : 0;
    define('IS_WPMU', $is_wpmu);
}

global $arfversion, $arfdbversion, $arfadvanceerrcolor, $arf_memory_limit, $memory_limit;
$arfversion = '2.7.8';
$arfdbversion = '2.7.8';
$arf_memory_limit = 256;
$memory_limit = ini_get("memory_limit");

if (isset($memory_limit)) {
    if (preg_match('/^(\d+)(.)$/', $memory_limit, $matches)) {
        if ($matches[2] == 'M') {
            $memory_limit = $matches[1] * 1024 * 1024;
        } else if ($matches[2] == 'K') {
            $memory_limit = $matches[1] * 1024;
        }
    }
} else {
    $memory_limit = 0;
}

global $arfajaxurl;
$arfajaxurl = admin_url('admin-ajax.php');

global $arformsplugin;

global $arfloadcss, $arfforms_loaded, $arfcssloaded, $arfsavedentries;
$arfloadcss = $arfcssloaded = false;
$arfforms_loaded = $arfsavedentries = array();

require_once(HELPERS_PATH. '/armainhelper.php');
global $armainhelper;
$armainhelper = new armainhelper();

require_once(MODELS_PATH.'/arinstallermodel.php');  
require_once(MODELS_PATH.'/arfieldmodel.php');
require_once(MODELS_PATH.'/arformmodel.php');
require_once(MODELS_PATH.'/arrecordmodel.php');
require_once(MODELS_PATH.'/arrecordmeta.php');

global $MdlDb;
global $arffield;
global $arfform;
global $db_record;
global $arfrecordmeta;

global $arfsettings;
global $style_settings;
global $arsettingmodel;

$MdlDb              = new arinstallermodel();
$arffield          	= new arfieldmodel();
$arfform           	= new arformmodel();
$db_record          = new arrecordmodel();
$arfrecordmeta     	= new arrecordmeta();
$arsettingmodel		= new arsettingmodel();

require_once(CONTROLLERS_PATH . '/maincontroller.php');
require_once(CONTROLLERS_PATH . '/arformcontroller.php');

global $maincontroller;
global $arformcontroller;

$maincontroller         = new maincontroller();
$arformcontroller       = new arformcontroller();

require_once(HELPERS_PATH. '/arrecordhelper.php');
require_once(HELPERS_PATH. '/arformhelper.php');
require_once(MODELS_PATH.'/arnotifymodel.php');
	
	global $arnotifymodel;
	$arnotifymodel = new arnotifymodel();
	
	require_once(CONTROLLERS_PATH . "/arrecordcontroller.php");
	require_once(CONTROLLERS_PATH . "/arfieldcontroller.php");
	require_once(CONTROLLERS_PATH . "/arsettingcontroller.php");
	
	global $arrecordcontroller;
	global $arfieldcontroller;
	global $arsettingcontroller;
	
	$arrecordcontroller     = new arrecordcontroller();
	$arfieldcontroller      = new arfieldcontroller();
	$arsettingcontroller    = new arsettingcontroller();
	
	require_once(HELPERS_PATH. "/arfieldhelper.php");
	global $arfieldhelper;
	global $arrecordhelper;
	global $arformhelper;
	$arfieldhelper  = new arfieldhelper();
	$arrecordhelper = new arrecordhelper();
	$arformhelper	= new arformhelper();

	global $arfnextpage, $arfprevpage;
	$arfnextpage = $arfprevpage = array();
	
	global $arfmediaid;
	$arfmediaid = array();

	global $arfreadonly;
	$arfreadonly = false;
	
	global $arfshowfields, $arfrtloaded, $arfdatepickerloaded;
	global $arftimepickerloaded, $arfhiddenfields, $arfcalcfields, $arfinputmasks;

	$arfshowfields = $arfrtloaded = $arfdatepickerloaded = $arftimepickerloaded = array();
	$arfhiddenfields = $arfcalcfields = $arfinputmasks = array();

global $arfpagesize;
$arfpagesize = 20;
global $arfsidebar_width;
$arfsidebar_width = '';

global $arf_column_classes, $arf_column_classes_edit;
$arf_column_classes = $arf_column_classes_edit = array();
global $arf_page_number;
$arf_page_number = 0;
global $submit_ajax_page;
$submit_ajax_page = 0;
global $arf_section_div;
$arf_section_div = 0;
global $arf_captcha_loaded, $arf_file_loaded, $arf_modal_form_loaded;
$arf_captcha_loaded = $arf_file_loaded = $arf_modal_form_loaded = 0;

global $arf_slider_loaded;
$arf_slider_loaded = array();

global $arfmsgtounlicop;
$arfmsgtounlicop = '';

global $arf_password_loaded;
$arf_password_loaded = array();

global $arf_previous_label; 
$arf_previous_label = array();

global $arf_selectbox_loaded; 
$arf_selectbox_loaded = array();

global $arf_radio_checkbox_loaded;
$arf_radio_checkbox_loaded = array();

global $arf_conditional_logic_loaded;
$arf_conditional_logic_loaded = array();

global $arf_inputmask_loaded; 
$arf_inputmask_loaded = array();

global $arfcolorpicker_loaded; 
$arfcolorpicker_loaded = array();

global $arfcolorpicker_basic_loaded; 
$arfcolorpicker_basic_loaded = array();

global $arf_wizard_form_loaded;
$arf_wizard_form_loaded = array();

global $arf_survey_form_loaded;
$arf_survey_form_loaded = array();

global $arf_entries_action_column_width;
$arf_entries_action_column_width = 120;

global $is_multi_column_loaded;
$is_multi_column_loaded = array();

global $api_url, $plugin_slug;

if (class_exists('WP_Widget')) {
    require_once(FORMPATH . '/core/widgets/ARFwidgetForm.php');
    add_action('widgets_init', create_function('', 'return register_widget("ARFwidgetForm");'));
}



if( file_exists( FORMPATH.'/core/vc/class_vc_extend.php' )){
	require_once( ( FORMPATH.'/core/vc/class_vc_extend.php' ) );
	global $arforms_vdextend;
	$arforms_vdextend = new ARForms_VCExtendArp();	
}




function pluginUninstall() {
    global $wpdb, $arsettingcontroller;

    if (IS_WPMU) {

        $blogs = $wpdb->get_results("SELECT blog_id FROM {$wpdb->blogs}", ARRAY_A);
        if ($blogs) {
            foreach ($blogs as $blog) {
                switch_to_blog($blog['blog_id']);

                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_autoresponder');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_fields');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_forms');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entries');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entry_values');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ar');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_views');
                $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ref_forms');

                delete_option('_transient_arf_options');
                delete_option('_transient_arfa_options');
                delete_option('arfa_css');
                delete_option('_transient_arfa_css');
                delete_option('arf_options');
                delete_option('arf_db_version');
                delete_option('arf_ar_type');
                delete_option('arf_current_tab');
                delete_option('arfdefaultar');
                delete_option('arfa_options');
                delete_option('arf_global_css');
                delete_option('widget_arforms_widget_form');
                delete_option('arf_plugin_activated');

                delete_option("arf_update_token");
                delete_option("arfformcolumnlist");
                delete_option("arfIsSorted");
                delete_option("arfSortOrder");
                delete_option("arfSortId");
				delete_option("arfSortInfo");
				
            }
            restore_current_blog();
        }
    } else {

        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_autoresponder');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_fields');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_forms');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entries');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_entry_values');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ar');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_views');
        $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'arf_ref_forms');


        delete_option('_transient_arf_options');
        delete_option('_transient_arfa_options');
        delete_option('arfa_css');
        delete_option('_transient_arfa_css');
        delete_option('arf_options');
        delete_option('arf_db_version');
        delete_option('arf_ar_type');
        delete_option('arf_current_tab');
        delete_option('arfdefaultar');
        delete_option('arfa_options');
        delete_option('arf_global_css');
        delete_option('widget_arforms_widget_form');
        delete_option('arf_plugin_activated');
  delete_option("arf_update_token");
                delete_option("arfformcolumnlist");
        delete_option("arfIsSorted");
        delete_option("arfSortOrder");
        delete_option("arfSortId");
		delete_option("arfSortInfo");
    }
    $arsettingcontroller->arfreqlicdeactuninst();
}

register_uninstall_hook(__FILE__, 'pluginUninstall');

global $arformcontroller;

$api_url = $arformcontroller->arfgetapiurl();
$plugin_slug = basename(dirname(__FILE__));

add_filter('pre_set_site_transient_update_plugins', 'arf_check_for_plugin_update');

function arf_check_for_plugin_update($checked_data) {
    global $api_url, $plugin_slug, $wp_version, $maincontroller, $arfversion;

    
    if (empty($checked_data->checked))
        return $checked_data;

    $args = array(
        'slug' => $plugin_slug,
        'version' => $arfversion,
        'other_variables' => $maincontroller->arf_get_remote_post_params(),
    );

    $request_string = array(
        'body' => array(
            'action' => 'basic_check',
            'request' => serialize($args),
            'api-key' => md5(home_url())
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
    );

    
    $raw_response = wp_remote_post($api_url, $request_string);

    if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
        $response = unserialize($raw_response['body']);
        if(isset($response->token)){
        update_option('arf_update_token',$response->token);    
        }
	
	
    if (is_object($response) && is_object($checked_data) && !empty($response))
        $checked_data->response[$plugin_slug . '/' . $plugin_slug . '.php'] = $response;

    return $checked_data;
}

add_filter('plugins_api', 'arf_plugin_api_call', 10, 3);

function arf_plugin_api_call($def, $action, $args) {
    global $plugin_slug, $api_url, $wp_version;

    if (!isset($args->slug) || ($args->slug != $plugin_slug))
        return false;


    $plugin_info = get_site_transient('update_plugins');
    $current_version = $plugin_info->checked[$plugin_slug . '/' . $plugin_slug . '.php'];
    $args->version = $current_version;

    $request_string = array(
        'body' => array(
            'action' => $action,
			'update_token' => get_site_option('arf_update_token'),
            'request' => serialize($args),
            'api-key' => md5(home_url())
        ),
        'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
    );

    $request = wp_remote_post($api_url, $request_string);

    if (is_wp_error($request)) {
        $res = new WP_Error('plugins_api_failed', 'An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', $request->get_error_message());
    } else {
        $res = unserialize($request['body']);

        if ($res === false)
            $res = new WP_Error('plugins_api_failed', 'An unknown error occurred', $request['body']);
    }

    return $res;
}

add_action('plugins_loaded', 'arf_arform_load_textdomain');

function arf_arform_load_textdomain() {
    load_plugin_textdomain('ARForms', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

?>