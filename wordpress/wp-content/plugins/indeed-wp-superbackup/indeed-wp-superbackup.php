<?php
/*
Plugin Name: WP SuperBackup
Plugin URI: http://www.superbackup.wpindeed.com/
Description: The Best Solution for BackUp&Restore with Cloud Synchronization integrated with the best Cloud Destinations.
Version: 1.9
Author: indeed
Author URI: http://www.wpindeed.com
*/

//set the paths
if (!defined('IBK_PATH')){
	define('IBK_PATH', plugin_dir_path(__FILE__));
}
if (!defined('IBK_URL')){
	define('IBK_URL', plugin_dir_url(__FILE__));
}

require_once IBK_PATH . 'utilities.php';// load the utilities
if (is_admin()){
	//Load The Admin Class
	if (!class_exists('IndeedAdmin')){
		require_once IBK_PATH . 'classes/IndeedAdmin.class.php';
	}		
	$obj = new IndeedAdmin();//initiate admin object
	
	$ext_menu = 'ibk_admin';	
	include_once plugin_dir_path(__FILE__) . 'extensions_plus/index.php';

	
}

function ibk_run_backup($backup_id) {
	/*
	 * main plugin function
	 * @param id of backup item to run
	 * @return none
	 */
	require_once IBK_PATH . 'classes/IndeedDoBackup.class.php';
	$obj = new IndeedDoBackup($backup_id);
}
add_action( 'indeed_main_job', 'ibk_run_backup', 99, 1 );

function ibk_run_restore($zip_file_path, $table_to_restore=FALSE, $files_to_restore=FALSE, $is_migrate=FALSE, $migrate_settings=FALSE, $other_settings=FALSE) {
	/*
	 * main plugin function
	 * @param full path of source zip file
	 * @return none
	 */
	
	if (!empty($zip_file_path)){
		if (!class_exists('IndeedDoRestore')){
			require_once IBK_PATH . 'classes/IndeedDoRestore.class.php';
		}
		$obj = new IndeedDoRestore($zip_file_path, $table_to_restore, $files_to_restore, $is_migrate, $migrate_settings, $other_settings);
	}
}
add_action( 'indeed_restore_job', 'ibk_run_restore', 98, 6 );

function ibk_set_restore_migrate_process($post_data=FALSE){
	/*
	 * Copy zip file from somewhere and set the cron job to restore/migrate
	 * @ param $_POST serialized
	 * @return none
	 */
	if (!empty($post_data)){
		$post = unserialize($post_data);		
		$file = FALSE;
		$tables_to_restore = FALSE;
		$files_to_restore = FALSE;
		$migrate = FALSE;
		$migrate_settings = FALSE;
		$other_settings = FALSE;

		/********************* RESTORE ***********************/
		if (!empty($post['destination_id']) && !empty($post['snapshot_id'])){
			//RESTORE FROM SNAPSHOT POPUPS...
			$data = ibk_return_metas_from_custom_db('destinations', $post['destination_id']);
			if ($data['type']=='local'){
				$destination_metas = ibk_return_metas_from_custom_db('destinations', $post['destination_id']);
				$file = $destination_metas['local_folder_target'];
				if (substr($file, -1)!='/'){
					$file .= '/';
				}
				$file .= $post['source_file'];
			} else {
				require_once IBK_PATH . 'classes/IndeedCopyFile.class.php';
				$obj = new IndeedCopyFile();
				switch ($data['type']){
					case 'ftp':
						$file = $obj->get_file_from_ftp($post['destination_id'], $post['source_file']);
					break;
					case 'google':
						$file = $obj->get_file_from_google_drive($post['destination_id'], $post['source_file']);
					break;
					case 'dropbox':
						$file = $obj->get_file_from_dropbox($post['destination_id'], $post['source_file']);
					break;
					case 'amazon':
						$file = $obj->get_file_from_amazon($post['destination_id'], $post['source_file']);		
					break;
					case 'onedrive':
						$file = $obj->get_file_from_one_drive($post['destination_id'], $post['source_file']);
						break;
					case 'copy':
						$file = $obj->get_file_from_copydotcom($post['destination_id'], $post['source_file']);
						break;
				}
			}


		} else if (!empty($post['restore_url'])){
			//URL RESTORE
			require_once IBK_PATH . 'classes/IndeedCopyFile.class.php';
			$obj = new IndeedCopyFile();
			$file = $obj->get_file_from_url($post['restore_url']);
		}

		/********************** MIGRATE *************************/
		if (!empty($post['migrate_url'])){
				//URL migrate
				require_once IBK_PATH . 'classes/IndeedCopyFile.class.php';
				$obj = new IndeedCopyFile();
				$file = $obj->get_file_from_url($post['migrate_url']);
		}
		
		/********************* MIGRATE FROM CLOUD ***********************/

		if (!empty($post['cloud_connection_id']) && !empty($post['source_file'])){
			$migrate = TRUE;
			require_once IBK_PATH . 'classes/IndeedCopyFile.class.php';
			$obj = new IndeedCopyFile();
			switch ($post['destination_type']){
				case 'ftp':					
					$file = $obj->get_file_from_ftp($post['cloud_connection_id'], $post['source_file']);					
				break;
				case 'google':
					unset($obj);
					require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';					
					$obj = new IndeedGoogle($post['cloud_connection_id']);
					$obj->login();
					$files_objects = $obj->retrieveAllFiles();
					if (!empty($files_objects)){
						foreach ($files_objects as $file_obj){
							if ($file_obj->title==$post['source_file']){
								$target_id = $file_obj->id;
							}
						}
						if (!empty($target_id)){
							$gen_metas = ibk_get_general_metas();
							$target_dir = $gen_metas['ibk_backup_dir'];
							if (empty($target_dir)){
								$target_dir = 'isnapshots';
							}
							$target_dir = WP_CONTENT_DIR . '/uploads/' . $target_dir;
							$file = $obj->downloadFile($target_id, $target_dir);
						}
					}
				break;
				case 'dropbox':
					$file = $obj->get_file_from_dropbox($post['cloud_connection_id'], $post['source_file']);
				break;
				case 'amazon':
					$file = $obj->get_file_from_amazon($post['cloud_connection_id'], $post['source_file']);					
				break;
				case 'onedrive':
					$file = $obj->get_file_from_one_drive($post['cloud_connection_id'], $post['source_file']);
					break;
				case 'copy':
					$file = $obj->get_file_from_copydotcom($post['cloud_connection_id'], $post['source_file']);
					break;
			}
		}
		
		if (!$file && !empty($post['uploaded_zip_file'])){
			//restore or migrate from uploaded file
			$file = $post['uploaded_zip_file'];
		}
		
		if (isset($post['exclude_site_title']) && isset($post['exclude_tagline']) && isset($post['exclude_email'])){
			//adding extra settings for migrate
			$migrate_posible = array('exclude_site_title', 'exclude_tagline', 'exclude_email', 'migrate_wp_table_list', 'migrate_non_wp_tables', 'exclude_indeed_tables');
			foreach ($migrate_posible as $val){
				$migrate_settings[$val] = (isset($post[$val])) ? $post[$val] : '' ;
			}
			$migrate = TRUE;
		}
		if (!empty($post['exclude_multisite_siteurl'])){
			$other_settings['multisite_settigs']['sitemeta'] = 'siteurl';
		}
		
		if (!empty($post['multisite-single_site']) && is_multisite()){
			//one to multi
			$other_settings['target_site'] = $post['target_site'];
			if (!empty($post['sites_folders'])){
				$other_settings['sites_folders'] = $post['sites_folders'];//string
			}
			if (!empty($post['native_wp_tables'])){
				$other_settings['native_wp_tables'] = $post['native_wp_tables'];
			}						
		}
		
		//CRON IT
		if ($file){
			if (!empty($_REQUEST['tables_to_restore'])){
				$tables_to_restore = $post['tables_to_restore'];
			}
			if (!empty($_REQUEST['files_to_restore'])){
				$files_to_restore = $post['files_to_restore'];
			}
			wp_schedule_single_event( time()-1 , 'indeed_restore_job', array( $file, $tables_to_restore, $files_to_restore, $migrate, $migrate_settings, $other_settings ) );
		}
	}
}
add_action( 'indeed_set_restore_job_intermediate', 'ibk_set_restore_migrate_process', 97, 1 );

register_activation_hook( __FILE__, 'ibk_initiate_plugin' );
function ibk_initiate_plugin(){
	/*
	 * Create tables
	 * @param none
	 * @return none
	 * create db tables and local folder
	 */
	global $wpdb;
	// $prefix . indeed_backups
	$table_name = $wpdb->base_prefix . 'indeed_backups';
	if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE " . $table_name . " (
					id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name varchar(200),
					create_date datetime NOT NULL
		);";
		dbDelta( $sql );
	}
	// $prefix . indeed_backup_metas
	$table_name = $wpdb->base_prefix . 'indeed_backup_metas';
	if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE " . $table_name . " (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					backup_id int(9),
					meta_name varchar(200),
					meta_value text
		);";
		dbDelta( $sql );
	}

	// $prefix . indeed_destination
	$table_name = $wpdb->base_prefix . 'indeed_destinations';
	if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE " . $table_name . " (
					id int(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					name varchar(200),
					type varchar(200),
					create_date datetime NOT NULL,
					status tinyint(1) NOT NULL DEFAULT 0
		);";
		dbDelta( $sql );
	}
	// $prefix . indeed_destination
	$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
	if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE " . $table_name . " (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					destination_id int(9),
					meta_name varchar(200),
					meta_value text
		);";
		dbDelta( $sql );
	}

	//$prefix . indeed_logs
	$table_name = $wpdb->base_prefix . 'indeed_logs';
	if ($wpdb->get_var( "show tables like '$table_name'" ) != $table_name){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "CREATE TABLE " . $table_name . " (
					id int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					process_id int(11),
					action_id int(11),
					stage varchar(100),
					message text,
					type varchar(100),
					create_date datetime NOT NULL,
					status tinyint(2)
		);";
		dbDelta( $sql );
	}
	
	//Create temporary dir
	$dir = WP_CONTENT_DIR . '/uploads/isnapshots';
	if (!file_exists($dir)){
		@mkdir($dir, 0777, TRUE);
	}
	
	//Create default Local Directory
	$dir = WP_CONTENT_DIR . '/uploads/indeed-backups';
	if (!file_exists($dir)){
		@mkdir($dir, 0777, TRUE);
	}
}

