<?php
		
	#ACTIVATE PLUGIN
	class hmapsprem_activate{
		
		#CLASS VARS
		private $plugin_name;
		private $plugin_version;
		private $plugin_dir;
		private $plugin_old_version;
		
		#CONSTRUCT
		public function __construct($plugin_name,$plugin_version,$plugin_dir){
			//define class vars
			$this->plugin_name = $plugin_name;
			$this->plugin_version = $plugin_version;
			$this->plugin_dir = $plugin_dir;
			//update check
			$this->update_check();
		}
		
		#CHECK FOR UPGRADE
		private function update_check(){
			global $wpdb;
			$plugin_lookup = $wpdb->get_results("SELECT * FROM `". $wpdb->base_prefix ."hplugin_root` WHERE `plugin_name` = '". $this->plugin_name ."';");
			if($plugin_lookup){
				$this->plugin_old_version = $plugin_lookup[0]->plugin_version;
				if(version_compare($this->plugin_old_version,$this->plugin_version,'<')){
					$update = new hmapsprem_update_plugin($this->plugin_name,$this->plugin_version,$this->plugin_old_version,$this->plugin_dir);
					$update->update_plugin();
				}
			}
		}
		
		#ACTIVATE
		private function activate(){
			//access globals
			global $wpdb;
			global $hmapsprem_helper;
			//create the hplugin_root table if it doesn't exist
			$sql_create = "
				CREATE TABLE IF NOT EXISTS `". $wpdb->base_prefix ."hplugin_root` (
				  `hplugin_id` int(11) NOT NULL AUTO_INCREMENT,
				  `plugin_name` varchar(45) NOT NULL,
				  `plugin_version` varchar(10) NOT NULL,
				  `plugin_uuid` varchar(36) NOT NULL,
				  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `last_modified` datetime DEFAULT NULL,
				  `active` tinyint(1) NOT NULL DEFAULT '1',
				  `deleted` tinyint(1) NOT NULL DEFAULT '0',
				  PRIMARY KEY (`hplugin_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			dbDelta($sql_create);
			$sql_drop = "
				DROP TRIGGER IF EXISTS `". $wpdb->base_prefix ."hplugin_root`;
			";
			$wpdb->query($sql_drop);
			$sql_create = "
				CREATE TRIGGER `". $wpdb->base_prefix ."hplugin_root`
				BEFORE UPDATE ON `". $wpdb->base_prefix ."hplugin_root`
				FOR EACH ROW SET NEW.last_modified = NOW();
			";
			dbDelta($sql_create);
			//check if plugin exists in hplugin_root table
			$plugin_lookup = $wpdb->get_results("SELECT * FROM `". $wpdb->base_prefix ."hplugin_root` WHERE `plugin_name` = '". $this->plugin_name ."';");
			if(!$plugin_lookup){ //add if not exists
				$wpdb->query("INSERT INTO `". $wpdb->base_prefix ."hplugin_root` (`plugin_name`,`plugin_version`,`plugin_uuid`) VALUES('". $this->plugin_name ."','". $this->plugin_version ."','". $hmapsprem_helper->genGUID() ."');");
			}else{ //ensure that deleted = 0
				$wpdb->query("UPDATE `". $wpdb->base_prefix ."hplugin_root` SET `deleted` = 0, `active` = 1 WHERE `plugin_name` = '". $this->plugin_name ."';");
			}
		}
		
		#SETUP PLUGIN
		public function setup_plugin(){
			//activate plugin
			$this->activate();
			//create plugin tables
			$this->create_default_storage_table(); //default storage table
			
			$this->create_marker_categories_table(); //marker categories
			$this->create_markers_table(); //markers
			
			/*
				-- create your plugin specific tables here --
				
				note: Ensure that CREATE TABLE "IF NOT EXISTS" is used.
			*/
		}
		
		#CREATE DEFAULT STORAGE TABLE
		private function create_default_storage_table(){
			//access globals
			global $wpdb;
			//create the default_storage_table table if it doesn't exist
			$sql_create = "
				CREATE TABLE IF NOT EXISTS `". $wpdb->base_prefix ."hmapsprem_default_storage_table` (
					`storage_id` int(11) NOT NULL AUTO_INCREMENT,
					`object_name` varchar(100) NOT NULL,
					`json_object` longblob NOT NULL,
					`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`last_modified` datetime DEFAULT NULL,
					`deleted` tinyint(1) NOT NULL DEFAULT '0',
					PRIMARY KEY (`storage_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			dbDelta($sql_create);
			//drop trigger if exists
			$sql_drop = "
				DROP TRIGGER IF EXISTS `". $wpdb->base_prefix ."hmapsprem_default_storage_table`;
			";
			$wpdb->query($sql_drop);
			//re-create trigger
			$sql_create = "
				CREATE TRIGGER `". $wpdb->base_prefix ."hmapsprem_default_storage_table`
				BEFORE UPDATE ON `". $wpdb->base_prefix ."hmapsprem_default_storage_table`
				FOR EACH ROW SET NEW.last_modified = NOW();
			";
			dbDelta($sql_create);
			return true;
		}
		
		#CREATE MARKER CATEGORIES TABLE
		private function create_marker_categories_table(){
			//access globals
			global $wpdb;
			//create the hmapsprem_marker_categories table if it doesn't exist
			$sql_create = "
				CREATE TABLE IF NOT EXISTS `". $wpdb->base_prefix ."hmapsprem_marker_categories` (
					`category_id` int(11) NOT NULL AUTO_INCREMENT,
					`name` varchar(45) NOT NULL,
					`imported` tinyint(1) NOT NULL DEFAULT '0',
					`deleted` tinyint(1) NOT NULL DEFAULT '0',
					`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					PRIMARY KEY (`category_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			dbDelta($sql_create);
			return true;
		}
		
		#CREATE MARKERS TABLE
		private function create_markers_table(){
			//access globals
			global $wpdb;
			//create unique constraint values
			$contraint_uid = date('Hidmy'); // this is to prevent FK restraints from adding if on a multisite WP install
			//create the hmapsprem_markers table if it doesn't exist
			$sql_create = "
				CREATE TABLE IF NOT EXISTS `". $wpdb->base_prefix ."hmapsprem_markers` (
					`marker_id` bigint(20) NOT NULL AUTO_INCREMENT,
					`category_id` int(11) NOT NULL,
					`img_binary` blob NOT NULL,
					`width` int(11) NOT NULL,
					`height` int(11) NOT NULL,
					`left_offset` int(11) NOT NULL,
					`top_offset` int(11) NOT NULL,
					`link` int(11) NOT NULL,
					`primary_colour` varchar(7) NOT NULL,
					`secondary_colour` varchar(7) NOT NULL,
					`created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`deleted` tinyint(1) NOT NULL,
					PRIMARY KEY (`marker_id`),
					KEY `category_id_hmapsprem_markers_FK_idx` (`category_id`),
					CONSTRAINT `category_id_hmapsprem_markers_". $contraint_uid ."_FK` FOREIGN KEY (`category_id`) REFERENCES `". $wpdb->base_prefix ."hmapsprem_marker_categories` (`category_id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
			";
			dbDelta($sql_create);
			return true;
		}
		
	}