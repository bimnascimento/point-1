<?php 

	#PLUGIN INFORMATION
	/*
		Plugin Name: Hero Maps Premium
		Plugin URI: http://www.heroplugins.com
		Description: Easily create your own Google Maps with a simple drag and drop interface
		Version: 2.1.5
		Author: Hero Plugins
		Author URI: http://www.heroplugins.com
		License: GPLv2 or later
	*/
	
	#LICENSE INFORMATION
	/*  
		Copyright 2015  Hero Plugins (email : info@heroplugins.com)
	
		This program is free software; you can redistribute it and/or
		modify it under the terms of the GNU General Public License
		as published by the Free Software Foundation; either version 2
		of the License, or (at your option) any later version.
		
		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
		GNU General Public License for more details.
		
		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
	*/
	
	#PLUGIN INCLUDES
	require_once('classes/helper/check.helper.php');
	require_once('classes/management/activate_plugin.class.php');
	require_once('classes/management/update_plugin.class.php');
	require_once('classes/management/deactivate_plugin.class.php');
	require_once('classes/core/plugin_setup.class.php');
	require_once('classes/core/checkin.class.php');
	require_once('classes/core/promo.class.php');
	require_once('classes/core/display.class.php');
	require_once('classes/core/shortcode.class.php');
	require_once('classes/core/registration.class.php');
	require_once('classes/core/auto_generate.class.php');
	require_once('classes/core/frame_sec.class.php');
	require_once('classes/marker_processor.class.php');
	require_once('classes/map_importer.class.php');
	require_once('classes/marker_csv_importer.class.php');
	require_once('classes/core/object_management.class.php');
	require_once('classes/backend.class.php');
	require_once('classes/frontend.class.php');
	require_once('inc/ajax.calls.php');
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	#DEFINE HELPER CLASS POINTER
	$hmapsprem_helper;
	
	#PLUGIN ROOT
	class heroplugin_hmapsprem{

		#PLUGIN CONFIG
		private $plugin_name = 'hmapsprem';
		private $plugin_dir_name = 'hmapsprem';
		private $plugin_friendly_name = 'Hero Maps Premium';
		private $plugin_friendly_description = 'Easily create your own Google Maps with a simple drag and drop interface';
		private $plugin_version = '2.1.5';
		private $plugin_prefix = 'hmapsprem_';
		private $first_release = '2015-08-19';
		private $last_update = '2016-06-28';
		private $api_version = '2.0.1';
		
		#CLASS VARS
		private $plugin_dir;
		private $plugin_url;
		private $plugin_basename;
		private $plugin_old_version;
		private $plugin_uuid;

		#CONSTRUCT
		public function __construct(){

			//define plugin vars
			$this->plugin_dir = dirname(__FILE__);
			$this->plugin_basename = plugin_basename(__FILE__);
			$this->plugin_url = plugins_url($this->plugin_dir_name) .'/';
			
			//instantiate helper class
			global $hmapsprem_helper;
			$hmapsprem_helper = new hmapsprem_helper($this->plugin_prefix);

			//register management hooks
			register_activation_hook(__FILE__,array(new hmapsprem_activate($this->plugin_name, $this->plugin_version, $this->plugin_dir), 'setup_plugin')); //activate
			register_deactivation_hook(__FILE__,array(new hmapsprem_deactivate($this->plugin_name), 'teardown_plugin')); //deactivate
			
			//detect if update required
			global $wpdb;
			if($this->plugin_old_version == NULL && $hmapsprem_helper->onAdmin()){ //only make the DB call if required
				$plugin_lookup = $wpdb->get_results("SELECT * FROM `". $wpdb->base_prefix ."hplugin_root` WHERE `plugin_name` = '". $this->plugin_name ."';");
				if($plugin_lookup){
					$this->plugin_old_version = $plugin_lookup[0]->plugin_version;
					$this->plugin_uuid = $plugin_lookup[0]->plugin_uuid; //define plugin uuid for check-in
				}
				if(version_compare($this->plugin_old_version,$this->plugin_version,'<')){
					$update = new hmapsprem_update_plugin($this->plugin_name,$this->plugin_version,$this->plugin_old_version, $this->plugin_dir);
					$update->update_plugin();
				}
			}

			//instantiate object manager
			$object_manager = new hmapsprem_object_management($this->plugin_dir);
			add_action('wp_ajax_hmapsprem_update_database_objects', array(&$object_manager, 'update_database_objects')); //admin: update database objects

			//instantiate plugin setup
			new hmapsprem_setup($this->plugin_name,$this->plugin_dir,$this->plugin_url,$this->plugin_friendly_name,$this->plugin_version,$this->plugin_prefix,$this->first_release, $this->last_update, $this->plugin_friendly_description);
			
			//queue update check
			$checkin = new hmapsprem_checkin($this->plugin_basename,$this->plugin_name,$this->plugin_friendly_name,$this->api_version);
			add_filter('pre_set_site_transient_update_plugins', array(&$checkin, 'check_in'));
			
			//instantiate promotions class
			$promo = new hmapsprem_promo($this->plugin_basename,$this->plugin_name,$this->api_version);
			
			//instantiate admin class
			$backend = new hmapsprem_backend(); //this instance can be used by WP for ajax implementations
			
			//instantiate front-end class
			$frontend = new hmapsprem_frontend(); //this instance can be used by WP for ajax implementations
			
			//instantiate the frame security class
			$frame_sec = new hmapsprem_frame_sec($this->plugin_dir);
			
			//instantiate the marker pack processor
			$marker_processor = new hmapsprem_marker_processor($this->plugin_dir);
			
			//instantiate map importer
			$map_importer = new hmapsprem_map_importer($this->plugin_dir);
			
			//instantiate marker csv importer
			$marker_csv_importer = new hmapsprem_map_csv_importer($this->plugin_dir);
			
			//bind admin ajax listeners
			add_action('wp_ajax_hmapsprem_getPromotion', array(&$promo, 'get_promotion')); //admin: get plugin rating
			add_action('wp_ajax_hmapsprem_get_security_code', array(&$frame_sec, 'get_security_code')); //admin: get frame security code
			add_action('wp_ajax_hmapsprem_process_marker_packs', array(&$marker_processor, 'process_marker_packs')); //admin: process marker packs
			add_action('wp_ajax_hmapsprem_process_custom_markers', array(&$marker_processor, 'process_custom_markers')); //admin: process custom markers
			add_action('wp_ajax_hmapsprem_process_map_import', array(&$map_importer, 'process_map_import')); //admin: process map import
			add_action('wp_ajax_hmapsprem_process_marker_csv_import', array(&$marker_csv_importer, 'process_marker_csv_import')); //admin: process marker csv import
			
			//instantiate registrations class (register all ajax hooks)
			new hmapsprem_registration($this->plugin_prefix, $backend, $frontend);
			
			//configure auto-generation class and hooks
			$autogenerate = new hmapsprem_autogenerate($this->plugin_dir);
			add_action('wp_ajax_hmapsprem_autoGenerateViews', array(&$autogenerate, 'create_views')); //admin: get plugin rating
			
		}
		
	}
	
	#INITIALISE THE PLUGIN CODE WHEN WP INITIALISES
	new heroplugin_hmapsprem();