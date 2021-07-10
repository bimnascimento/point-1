<?php 
if (!class_exists('IndeedDoRestore')){
	class IndeedDoRestore{
		private $general_metas = FALSE;
		private $temp_dir = FALSE;
		private $archive_dir = FALSE;
		private $restore_arr;// can contain: sql, wp-config.php, plugins, uploads, themes
		
		//input
		private $zip_file = FALSE;
		private $restore_files_list = FALSE;
		private $restore_tables_list = FALSE;
		private $is_migrate = FALSE;
		private $migrate_settings = FALSE;
		private $other_settings = FALSE;
		
		public function __construct($target_zip_file='', $table_to_restore=FALSE, $files_to_restore=FALSE, $is_migrate=FALSE, $migrate_settings=FALSE, $other_settings=FALSE){	
			#START PROCESS
			//$this->set_restore_log("Start Process!");		
			
			//check if ZipArchive is load
			if (!extension_loaded('zip')) {
				ibk_debug("Restore: ZIP - ERROR - no ZipArhive class - ");
				return;
			}
			
			///setting the input variables
			if ($target_zip_file){
				$this->zip_file = $target_zip_file;
			} else {				
				return FALSE;//if we don't have a source zip file ... end
			}
			if ($table_to_restore){
				$this->restore_tables_list = $table_to_restore;
			}
			if ($files_to_restore){
				$this->restore_files_list = $files_to_restore;		
			}
			if ($is_migrate){
				$this->is_migrate = TRUE;
			}
			if ($migrate_settings){
				$this->migrate_settings = $migrate_settings;
			}
			if ($other_settings){
				$this->other_settings = $other_settings;
				if (!empty($this->other_settings['native_wp_tables'])){
					//string to array for easy use
					$this->other_settings['native_wp_tables'] = explode(',', $this->other_settings['native_wp_tables']);
				}
				if (!empty($this->other_settings['sites_folders'])){
					//string to array for easy use
					$this->other_settings['sites_folders'] = explode(',', $this->other_settings['sites_folders']);
				}				
			}
			
			//running the actions
			require_once IBK_PATH . 'utilities.php';	
			
			ibk_debug("Restore: START Process");
			
				ibk_debug("Restore: Files for Restore: ".$this->restore_files_list,1);	
				ibk_debug("Restore: Tables for Restore: ".$this->restore_tables_list,1);	
				ibk_debug("Restore: Migrate Process: ".$this->is_migrate,1);	
					
			$this->general_metas = ibk_get_general_metas();// set general metas
			$this->set_temporary_dir();
			$this->set_memory_limit();
			
			if (!$this->can_we_write_files()){
				return;
			}
			
			$unzip = $this->unzip_file();
			if ($unzip){
				$this->restore_arr = $this->what_to_restore();
				$this->restore_the_files();
				$this->restore_the_db();
				$this->indeed_rmdir_recursive($this->temp_dir);//delete the temporary files and folder
				ibk_debug("Restore: Temporary Dir - Deleted",1);
				$this->delete_zip_file();
				ibk_debug("Restore: Zip File - Deleted",1);
				
				ibk_debug("Restore: END Process - SUCCESS");	
			} else {
				ibk_debug("Restore: END Process - ERROR - No unzip File");	
			}
			
			#END PROCESS
			$this->set_restore_log("Process End!");
			unlink(WP_CONTENT_DIR . '/uploads/indeed-backups/' . md5("indeed-super-backup") . '_restore.log');
		}
				
		public function set_source_zip($source_zip_file=''){
			/*
			 * @param string
			 * @return none
			 */
			$this->zip_file = $source_zip_file;
			unset($source_zip_file);	
		}
		
		private function set_temporary_dir(){
			/*
			 * @param none
			 * @return none
			 */			
			if (!empty($this->general_metas['ibk_backup_dir'])){				
				$dir =  WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'];
				$this->temp_dir = $dir . '/' . str_replace('.zip', '', basename($this->zip_file)) . '-restore';
				ibk_debug("Restore: Set Temporary Folder - ".$this->temp_dir,1);	
				
				//clean up
				unset($dir);
			} else {
				ibk_debug("Restore: Set Temporary Folder -  ERROR ");
			}
		}

		
		private function set_memory_limit(){
			/*
			 * @param none
			 * @return none
			 */
			if (!empty($this->general_metas['ibk_memory_limit'])){
				$this->general_metas['ibk_memory_limit'] = preg_replace('/\D/', '', $this->general_metas['ibk_memory_limit']);//remove characters from string
				$limit = $this->general_metas['ibk_memory_limit'];//put the M in limit
				unset($this->general_metas['ibk_memory_limit']);
			} else {
				$memory = (int)ini_get('memory_limit');
				$php_limit = ceil(memory_get_peak_usage()/1024/1024);
				if ($memory>$php_limit){
					$limit = $memory;
				} else {
					$limit = $php_limit;
				}
				//let's be sure we have >256M memory
				if ($limit<256){
					$limit = 256;
				}
				unset($php_limit);
			}
			$limit = $limit . 'M';
			ini_set('memory_limit', $limit);
			unset($limit);
		}

				
		private function unzip_file(){
			/*
			 * extract the zip file into temporary directory
			 * @param none
			 * @return bool
			 */
			@set_time_limit(3600);
			ibk_debug("Restore: ZIP - START UnZip file - ".$this->zip_file);	
			$this->set_restore_log("Unzip File: " . basename($this->zip_file) );
			if (class_exists('ZipArchive')){
				$zip = new ZipArchive;
				$res = $zip->open($this->zip_file);
				if ($res === TRUE) {
					$zip->extractTo($this->temp_dir);
					$zip->close();
					ibk_debug("Restore: ZIP - END UnZip file - SUCCESS - ".$this->zip_file);
					unset($zip);
					unset($res);	
					return TRUE;
				} else {
					ibk_debug("Restore: ZIP - END UnZip file - ERROR - can not open file - ".$this->zip_file);	
				}
			} else {
				ibk_debug("Restore: ZIP - END UnZip file - ERROR - no ZipArhive class - ".$this->zip_file);	
			}
			return FALSE;
		}

		private function what_to_restore(){
			/*
			 * @param none
			 * @return array
			 */
			ibk_debug("Restore: Set Files and DataBase Tables to be restored");	
			$this->set_restore_log("Set Files and DataBase Tables to be restored.");
			$restore = array();
			$dir = new DirectoryIterator($this->temp_dir);
			foreach ($dir as $fileinfo){
				if (!$fileinfo->isDot()){
					$restore[] = $fileinfo->getFilename();
				}
			}
			//clean up
			unset($dir);
			unset($fileinfo);
			
			return $restore;
		}		

		
		private function restore_the_files(){
			/*
			 * main function to restore the files
			 * @param none
			 * @return none
			 */

			@set_time_limit(3600);
			ibk_debug("Restore: FILES - START Restore");
				
			$excluded_folders = array(  
										WP_CONTENT_DIR . '/uploads/indeed-backups',
										WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'], 
										IBK_PATH,
									);

			if ($this->restore_files_list){
				$restore_only_arr = explode(',', $this->restore_files_list);
				$restore_arr = array_intersect($restore_only_arr, $this->restore_arr);
			} else {
				$restore_arr = $this->restore_arr;
			}
			
			if (in_array('themes', $restore_arr)){
				ibk_debug("Restore: FILES - Start restore <</theme>> folder.",1);	
				$this->set_restore_log('Restore the "/themes" folder.');
				$this->iterate_and_restore('themes', $excluded_folders);
				ibk_debug("Restore: FILES -  END restore <</theme>> folder.",1);
			}
			if (in_array('uploads', $restore_arr)){
				ibk_debug("Restore: FILES -  Start restore <</uploads>> folder.",1);
				$this->set_restore_log('Restore the "/uploads" folder.');				
				$this->iterate_and_restore('uploads', $excluded_folders);
				ibk_debug("Restore: FILES -  END restore <</uploads>> folder.",1);
			}
			if (in_array('plugins', $restore_arr)){
				ibk_debug("Restore: FILES -  Start restore <</plugins>> folder.",1);
				$this->set_restore_log('Restore the "/plugins" folder.');
				$this->iterate_and_restore('plugins', $excluded_folders);
				ibk_debug("Restore: FILES -  END restore <</plugins>> folder.",1);
			}			
			if (in_array('wp-config.php', $restore_arr) && !$this->is_migrate){
				ibk_debug("Restore: FILES -  Start restore <<wp-config.php>> folder.",1);
				$this->set_restore_log('Restore the "wp-config.php" file.');
				rename($this->temp_dir . '/wp-config.php', ABSPATH . 'wp-config.php');
				ibk_debug("Restore: FILES -  END restore <<wp-config.php>> folder.",1);
			}
			
			ibk_debug("Restore: FILES - END Restore");
			
			//clean up
			if (isset($excluded_folders)) unset($excluded_folders);
			if (isset($restore_only_arr)) unset($restore_only_arr);
			if (isset($restore_arr)) unset($restore_arr);
			
		}
		
		private function iterate_and_restore($target, $excluded_folders){	
			/*
			 * replace files and folders
			 * @param : 
			 * - target can be upload, themes, plugins or wp-config.php
			 * - full path to excluded folders
			 * @return none
			 */
			$old_files_arr = FALSE;
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->temp_dir . '/' . $target), RecursiveIteratorIterator::SELF_FIRST);
			//uploads multisite
			if ($target=='uploads' && !empty($this->other_settings['target_site']) && $this->other_settings['target_site']>1 && !empty($this->other_settings['sites_folders'])){
				$restore_uploads_multi = TRUE;
			} else {
				$restore_uploads_multi = FALSE;
			}
			
			foreach ($files as $file){
				$file = str_replace('\\', '/', $file);
				if ( in_array(substr($file, strrpos($file, '/')+1), array('.', '..')) ){
					continue;
				}
				$file = realpath($file);				
				if (is_file($file)){
					$old_file = str_replace($this->temp_dir . '/' . $target, WP_CONTENT_DIR . '/' . $target, $file);
					foreach ($excluded_folders as $excluded_dir){
						if (strpos($old_file, $excluded_dir)!==FALSE){
							continue 2;//skip current file from moving
						}
					}
					//echo 'File: ', $file, '<br/>Old File: ',$old_file,'<br/><br/>';
					if ($restore_uploads_multi){
						foreach ($this->other_settings['sites_folders'] as $v){
							if (strpos($old_file, '/uploads/' . $v)!==FALSE){
								$old_file = str_replace('/uploads/' . $v, '/uploads/sites/' . $this->other_settings['target_site'] . '/' . $v, $old_file);
							}
						}
					}
					$this->move_files($file, $old_file);
					
					ibk_debug("Restore: FILES -  Moved File - ".$file,2);					
					$old_files_arr[] = $old_file;					
				}
			}			
			
			////extra section, delete files that are included in old wp and not in the new one
			$delete_extra_files = FALSE;
			if ($delete_extra_files && $old_files_arr){
				//iterate in wp 
				$wp_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(WP_CONTENT_DIR . '/' . $target), RecursiveIteratorIterator::SELF_FIRST);
				foreach ($wp_files as $wp_file){
					if (!in_array($wp_file, $old_files_arr)){
						//unlink($wp_file);// remove extra file
					}
				}
			}

			//clean up
			if (isset($target)) unset($target);
			if (isset($excluded_folders)) unset($excluded_folders);
			if (isset($old_files_arr)) unset($old_files_arr);
			if (isset($files)) unset($files);
			if (isset($target)) unset($target);
			if (isset($restore_uploads_multi)) unset($restore_uploads_multi);
			if (isset($file)) unset($file);
			if (isset($old_file)) unset($old_file);
			if (isset($v)) unset($v);
			if (isset($excluded_dir)) unset($excluded_dir);
			if (isset($delete_extra_files)) unset($delete_extra_files);
			if (isset($wp_files)) unset($wp_files);			
			if (isset($wp_file)) unset($wp_file);
			
		}
		
		private function move_files($source, $target){
			/*
			 * @param string - full path , string
			 * @return none 
			 */	
			$current_dir = dirname($target);
			if (!file_exists($current_dir)) {					
				wp_mkdir_p($current_dir, 0777, true);
			}				
			rename( $source, $target );
			
			//clean up
			unset($current_dir);
			unset($target);
			unset($source);
		}		
		
		private function restore_the_db(){
			/*
			 * main function to restore the sql tables
			 * @param none
			 * @return none
			 */
			if (in_array('sql', $this->restore_arr) ){
				ibk_debug("Restore:  DATABASE - START restore DataBase",1);
				$sql_tables = $this->tables_to_restore();
				ibk_debug("Restore:  DATABASE - SQL Tables prepared - ".$sql_tables,2);
				
				
				if ($this->restore_tables_list){
					
					if ($this->is_migrate && isset($this->migrate_settings['migrate_wp_table_list'])){
						$wp_tables = array_keys(ibk_get_table_list('wp'));
						$no_wp_tables = array_diff($sql_tables, $wp_tables);
						$restore_only_arr = explode(',', $this->migrate_settings['migrate_wp_table_list']);
						$arr = array_intersect($sql_tables, $restore_only_arr);
						if ($this->migrate_settings['migrate_non_wp_tables']){
							$sql_tables_arr = array_merge($arr, $no_wp_tables);
						}
					} else {	
						$restore_only_arr = explode(',', $this->restore_tables_list);
						$sql_tables_arr = array_intersect($sql_tables, $restore_only_arr);						
					}				
				} else {
					$sql_tables_arr = $sql_tables;
				}
				
				////Exclude indeed tables
				if ($this->migrate_settings['exclude_indeed_tables']){
					$sql_tables_arr = array_diff($sql_tables_arr, array('indeed_backups', 'indeed_backup_metas', 'indeed_destinations', 'indeed_destination_metas'));
				}
				
				///Exclude migrate tables
				if ($this->is_migrate && is_multisite()){
					$sql_tables_arr = array_diff($sql_tables_arr, array('blogs', 'blog_versions', 'site'));
				}
				
				foreach ($sql_tables_arr as $sql_table){
					$this->restore_table($sql_table);					
				}
			ibk_debug("Restore:  DATABASE - END restore DataBase",1);	
			}
			
			//clean up
			if (isset($sql_tables)) unset($sql_tables);
			if (isset($wp_tables)) unset($wp_tables);
			if (isset($no_wp_tables)) unset($no_wp_tables);
			if (isset($restore_only_arr)) unset($restore_only_arr);
			if (isset($arr)) unset($arr);
			if (isset($sql_tables_arr)) unset($sql_tables_arr);
			if (isset($sql_table)) unset($sql_table);
		}
		
		private function tables_to_restore(){
			/*
			 * loop through sql folder and get name of each table that must be restored
			 * @param none
			 * @return array
			 */
			$sql_tables = array();
			
			$sql_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->temp_dir . '/sql/'), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($sql_files as $sql_file){
				$name = basename($sql_file);
				if ($name[0]!='.'){
					$sql_tables[] = str_replace('.sql', '', $name);
				}
			}		
			
			//clean up
			if (isset($sql_files)) unset($sql_files);
			if (isset($sql_file)) unset($sql_file);
			if (isset($name)) unset($name);
			
			return $sql_tables;	
		}
		
		private function restore_table($table_name=''){
			/*
			 * @param string
			 * @return none
			 */
			ibk_debug("Restore:  DATABASE - Restore Table: " . $table_name, 2);	
			$this->set_restore_log("Restore Table: " . $table_name);
			if (file_exists($this->temp_dir . '/sql/' . $table_name . '.sql')){
				global $wpdb;
				$file = new SplFileObject($this->temp_dir . '/sql/' . $table_name . '.sql');
				$temp_table = $table_name . '_indeed_temp';
				$no_errors = 1;	
				
				while (!$file->eof() && $no_errors!==FALSE) {
					@set_time_limit(3600);
					$q = $file->current();
					$file->next();
					if ($q){
						$no_errors = $wpdb->query($q);
					}			
				}//end of while feof
				
				unset($file);	

				if ($this->is_migrate && $table_name=='options' && $no_errors!==FALSE){
					//OPTION TABLE
					$no_errors = $this->update_temp_options_table($temp_table);
				} else if ( $this->is_migrate && $table_name=='usermeta' && $no_errors!==FALSE){
					$no_errors = $this->update_temp_usermeta_table($temp_table);
				} else if (is_multisite() && $this->is_migrate && $table_name=='sitemeta' && $no_errors!==FALSE){
					//multisite migrate
					$no_errors = $this->excluded_options_from_migrate($table_name, $temp_table);
				}					
				
				if ($no_errors!==FALSE){
					//DROP THE ORIGINAL TABLE AND RENAME THE TEMP
					$prefix = $wpdb->prefix;//standard prefix
					if (!empty($this->other_settings['target_site']) && $this->other_settings['target_site']>1 && !empty($this->other_settings['native_wp_tables'])){					
						if (in_array($table_name, $this->other_settings['native_wp_tables'])){
							if (isset($wpdb->base_prefix)){
								$prefix = $wpdb->base_prefix . $this->other_settings['target_site'] . "_";//MULTISITE prefix
							} else {
								$prefix = $wpdb->prefix . $this->other_settings['target_site'] . "_";//MULTISITE prefix
							}							
						}						
					}
					$original_table = $prefix . $table_name;					
					$wpdb->query("DROP TABLE IF EXISTS `" . $original_table . "`;");
					$wpdb->query("ALTER TABLE `" . $temp_table . "` RENAME `" . $original_table . "`;");					
				} else {
					///DROP THE TEMP TABLE, THERE is some errors
					$wpdb->query('DROP TABLE ' . $temp_table);		
				}
		
			}//end of table file exists
			
			//clean up
			unset($table_name);
			if (isset($file)) unset($file);
			if (isset($temp_table)) unset($temp_table);
			if (isset($no_errors)) unset($no_errors);
			if (isset($q)) unset($q);
			if (isset($prefix)) unset($prefix);
			if (isset($original_table)) unset($original_table);			
			
		}//end of function restore_table
		
		private function update_temp_options_table($temp_table){
			/*
			 * return an array with values for options that must be replace on migrate
			 * we must save the following options before truncate : siteurl, home, *blogname, *blogdescription,*admin email
			 * WordPress Address (URL) - siteurl
			 * Site Address (URL) - home 
			 * Site Title - blogname 
			 * Tagline - blogdescription 
			 * Email address - admin_email
			 * @param temp table options name
			 * @return FALSE if there's an error
			 */
			global $wpdb;
			$saved_options = array('siteurl', 'home');
			if (!empty($this->migrate_settings['exclude_site_title'])){
				$saved_options[] = 'blogname';
			}
			if (!empty($this->migrate_settings['exclude_tagline'])){
				$saved_options[] = 'blogdescription';
			}
			if (!empty($this->migrate_settings['exclude_email'])){
				$saved_options[] = 'admin_email';
			}
			
			$prefix = $wpdb->prefix;
			if (!empty($this->other_settings['target_site']) && $this->other_settings['target_site']>1 && !empty($this->other_settings['native_wp_tables'])){
				if (isset($wpdb->base_prefix)){
					$prefix = $wpdb->base_prefix . $this->other_settings['target_site'] . "_";//MULTISITE prefix
				} else {
					$prefix = $wpdb->prefix . $this->other_settings['target_site'] . "_";//MULTISITE prefix
				}				
			}
			
			foreach ($saved_options as $value){
				$data = $wpdb->get_row("SELECT option_value FROM " . $prefix . "options WHERE option_name='" . $value . "';");
				if (isset($data->option_value)){
					$no_errors = $wpdb->query("UPDATE " . $temp_table . " SET `option_value`='" . $data->option_value . "' WHERE `option_name`='" . $value . "';");
				}
			}
			
			if (!is_multisite()){
				//migrate to single
				$data = $wpdb->get_row("SELECT option_name, option_id FROM $temp_table WHERE option_name LIKE '%user_roles';");
				if ($data->option_name!= $wpdb->get_blog_prefix() . 'user_roles'){
					$no_errors = $wpdb->query("UPDATE $temp_table SET option_name='" . $wpdb->get_blog_prefix() . "user_roles' WHERE option_id='".$data->option_id."';");
				}			
			} else if (!empty($this->other_settings['target_site'])){
				//migrate to one from multi
				if ($this->other_settings['target_site']>1){
					$prefix = $wpdb->base_prefix . $this->other_settings['target_site'] . "_";
				} else {
					$prefix = $wpdb->get_blog_prefix();
				}
				$data = $wpdb->get_row("SELECT option_name, option_id FROM $temp_table WHERE option_name LIKE '%user_roles';");
				if ($data->option_name!= $prefix . 'user_roles'){
					$no_errors = $wpdb->query("UPDATE $temp_table SET option_name='" . $prefix . "user_roles' WHERE option_id='".$data->option_id."';");
				}				
			}
			
			//clean up
			if (isset($temp_table)) unset($temp_table);
			if (isset($saved_options)) unset($saved_options);
			if (isset($prefix)) unset($prefix);
			if (isset($saved_options)) unset($saved_options);
			if (isset($value)) unset($value);
			if (isset($data)) unset($data);						
			
			return $no_errors;
		}
		
		private function excluded_options_from_migrate($table_name, $temp_table){
			/*
			 * @param array tables_arr = array(table_name => array(opt1,opt2,...opt_n
			 * @return boolean
			 */
			$no_errors = TRUE;
			global $wpdb;
			$tables_arr = array();
			$table_structure = array(
								'sitemeta' => array('col_name'=>'meta_key', 'row_value'=>'meta_value')
							); 			
			if ($this->other_settings){
				if (isset($this->other_settings['multisite_settigs']['sitemeta']) && $this->other_settings['multisite_settigs']['sitemeta']=='siteurl'){
					$tables_arr['sitemeta'][] = 'siteurl';
				}	
			}
			if (isset($tables_arr[$table_name]) && count($tables_arr[$table_name])){
				foreach ($tables_arr[$table_name] as $option_name){
					$data = $wpdb->get_row("SELECT " . $table_structure[$table_name]['row_value'] . " FROM " . $wpdb->prefix . $table_name . " WHERE " . $table_structure[$table_name]['col_name'] . "='" . $option_name . "';");
					if (isset($data->{$table_structure[$table_name]['row_value']})){
						$no_errors = $wpdb->query("UPDATE " . $temp_table . " SET `".$table_structure[$table_name]['row_value']."`='" . $data->{$table_structure[$table_name]['row_value']} . "' WHERE `".$table_structure[$table_name]['col_name']."`='" . $option_name . "';");
					}
				}				
			}
			
			//clean up
			unset($table_name);
			unset($temp_table);
			unset($tables_arr);
			unset($table_structure);
			if (isset($option_name)) unset($option_name);
			if (isset($data)) unset($data);
			
			return $no_errors;
		}
		
		private function update_temp_usermeta_table($temp_table){
			/*
			 * used only for single site migrate
			 * @param temporary table name(string)
			 * @return boolean, true if ok
			 */
			$no_errors = TRUE;			
			if (!is_multisite()){
				global $wpdb;
				$prefix = $wpdb->get_blog_prefix();
				$data = $wpdb->get_results("SELECT meta_key, umeta_id FROM $temp_table WHERE meta_key LIKE '%capabilities'");
				if ($data){
					foreach ($data as $k=>$obj){
						if ($obj->meta_key!= $prefix . 'capabilities'){
							$no_errors = $wpdb->query("UPDATE $temp_table SET meta_key='" . $prefix . "capabilities' WHERE umeta_id='" . $obj->umeta_id . "';");
						}
					}
				}	
				$data = $wpdb->get_results("SELECT meta_key, umeta_id FROM $temp_table WHERE meta_key LIKE '%user_level'");
				if ($data){
					foreach ($data as $k=>$obj){
						if ($obj->meta_key!= $prefix . 'capatbilities'){
							$no_errors = $wpdb->query("UPDATE $temp_table SET meta_key='" . $prefix . "user_level' WHERE umeta_id='" . $obj->umeta_id . "';");
						}
					}
				}							
			}
			
			//clean up
			unset($temp_table);
			if (isset($prefix)) unset($prefix);
			if (isset($data)) unset($data);
			if (isset($k)) unset($k);
			if (isset($obj)) unset($obj);
			
			return $no_errors;
		}
		
		private function indeed_rmdir_recursive($dir){
			/*
			 * @param none
			 * @return none
			 */
			if (file_exists($dir)){
				foreach (scandir($dir) as $file) {
					if ('.' === $file || '..' === $file){
						continue;
					}
					if (is_dir("$dir/$file")){
						$this->set_restore_log("Delete Temporary files and folder.");
						$this->indeed_rmdir_recursive("$dir/$file");
					}
					else {
						unlink("$dir/$file");
					}
				}
				rmdir($dir);
			}
			
			//clean up
			unset($dir);
			if (isset($file)) unset($file);			
		}
		
		private function delete_zip_file(){
			/*
			 * @param none
			 * @return none
			 */
			$data = explode("/", $this->zip_file);
			end($data);
			$data_arr = explode("_", current($data));
			$meta = ibk_return_metas_from_custom_db('backups', $data_arr[2]);
			if (!empty($meta['destination'])){
				$destination_type = ibk_get_destination_type($meta['destination']);
				if (!empty($destination_type) && $destination_type=='local'){
					//avoid delete file from local
					return;
				}			
			}
			$this->set_restore_log("Delete Zip file.");
			unlink($this->zip_file);
			
			//clean up
			unset($data);
			unset($data_arr);
			unset($meta);
			if (isset($destination_type)) unset($destination_type);			
		}
		
		private function set_maintenance_mode($enable=FALSE){
			/*
			 * @param bool 
			 * @return none
			 */
			update_option('ibk_maintenance_mode', $enable);
			unset($enable);
		}
		
		private function set_restore_log($message){
			/*
			 * @param string
			 * @return none
			 */			
			$str = serialize(array(time()=>$message));
			$file_path = WP_CONTENT_DIR . '/uploads/indeed-backups/' . md5("indeed-super-backup") . '_restore.log';
			$file = fopen($file_path, 'w');
			fwrite($file, $str);
			fclose($file);
			
			//clean up
			unset($file_path);
			unset($file);
			unset($str);
			unset($message);
		}
		
		private function can_we_write_files(){
			/*
			 * @param none
			 * @return bool
			 */
			if (!ibk_check_dir_if_writable(WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/')){
				//temp dir
				ibk_debug("Restore: Cannot write on " . WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . " - ERROR");
				return FALSE;
			}
			return TRUE;
		}
		
		/**************** debugging ***************/
		private function write_into_debug_log($message){
			/*
			 * @param string
			 * @return none
			 */
			$file = IBK_PATH . 'restore_debugging.log';
			file_put_contents($file, $message, FILE_APPEND | LOCK_EX);
			unset($file);
			unset($message);
		}
		
		
	}//end of class IndeedDoRestore
}