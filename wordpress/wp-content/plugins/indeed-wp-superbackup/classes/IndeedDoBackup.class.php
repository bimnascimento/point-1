<?php 
if (!class_exists('IndeedDoBackup')){
	class IndeedDoBackup{
		private $backup_id = FALSE;
		private $backup_metas = array();
		private $destination_metas = array();
		private $general_metas = array();
		private $filename = '';
		private $temp_dir_sql = '';
		private $created_zip_date = FALSE;
		private $log_object;
		private $sites_folders = array();
		
		
		public function __construct($id){
			/*
			 * @param id of Snapshot(backup) Item 
			 * @return none
			 */
			$this->backup_id = $id;//setting the backup id
			unset($id);
			if (!class_exists('IndeedDoLogs')){
				require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
			}			
			$this->log_object = new IndeedDoLogs();//Logs Object
			$this->log_object->set_type('backup');
			$this->log_object->set_action_id($this->backup_id);
			$this->log_object->create_log('start', 'Process Start!', 1);
			
			//check if ZipArchive is load
			if (!extension_loaded('zip')) {
				ibk_debug("Backup: Make Zip - ERROR - ZipArchive Class does not exists!");
				$this->log_object->insert_log('zip', 'Error: ZipArchive Class does not exists!', 2);
				return false;
			}
			
			if (!function_exists('ibk_return_metas_from_custom_db')){
				require_once IBK_PATH . 'utilities.php';
			}
			
			ibk_debug("Backup: START Process");			
			
			$this->created_zip_date = time();	

			$this->filename = 'superbackup_' . md5('superbackup_indeed') . '_' . $this->backup_id . '_' . $this->created_zip_date . '.zip';//ZIP FILENAME
			ibk_debug( "Backup: SET filename as: " . $this->filename );
			
			$this->log_object->insert_log('start-0', 'Starting Manage Settings and Options', 1);
			
			$this->init_backup_metas();//get and set backup metas
			$this->init_destination_metas();//get and set destination metas
			$this->init_general_metas();//set the general metas		
				
			if (!$this->can_we_write_files()){ //check the dirs if we can write on them
				return;
			}
			
			$this->set_cron();//set next cron job
			$this->set_memory_limit();
			
			$this->send_email(1);//send e-mail process start
			
			$this->log_object->insert_log('start-100', 'Finish Manage Settings and Options', 1);		
			
			//CREATE ZIP FILE.
			global $zip;
			//sart make
			$this->open_zip();
			
			$this->backup_db();//save sql stuff
			$this->backup_files();//make zip file	
						
			$this->log_object->insert_log('zip-0', 'Starting Create Zip File', 1);
			$zip->close();//CLOSE ZIP FILE
			$this->log_object->insert_log('zip-100', 'Finish Create Zip File', 1);
			unset($zip);
			$this->delete_temporary_sql_folder();

			$this->log_object->insert_log('sending_file-0', 'Starting Sending Backup File to Destination', 1);			
			$send = $this->move_file();//send file 
			
			if ($send){
				//process finish ok
				ibk_debug("Backup: Process Finished - SUCCESS - file sent");
				$this->log_object->insert_log('finish', 'Backup: Process Finish!', 1);
				$removed_instance = $this->check_version_limit();				
				$this->write_log_snapshot_file($removed_instance);
			} else {
				//error sending file to destination...
				$this->log_object->insert_log('finish', 'Error: Process Finish but file was not send.', 2);
				ibk_debug("Backup: Process Finished - ERROR - file was not send");
			}
			
			$this->send_email(2);//send e-mail process email
		}
		
		private function backup_db(){
			/*
			 * iterate through database tables and save them
			 * @param none
			 * @return none
			 */
			@set_time_limit(3600);
			
			$this->log_object->insert_log('sql-0', 'Starting Prepare Database for Backup', 1);
			
			//create temporary folder and sql files
			try {
				ibk_debug("Backup: START Backup DB");
				if (!empty($this->backup_metas['save_db_table_list'])){
					
					$created = $this->create_sql_temp_dir();
					if (!$created) return;
					
					$data = explode(',', $this->backup_metas['save_db_table_list']);
					$progress_step = floor(100/(int)count($data));
					$progress = 0;
					
					foreach ($data as $table){
						$this->write_sql_file($table);
						$progress = $progress + $progress_step;
						$this->log_object->insert_log('sql-' . $progress, 'Backup Database.', 1);
					}
					
					//clean up
					unset($data);
					unset($table);
					unset($created);
					unset($progress);
					unset($progress_step);
				}
				$this->log_object->insert_log('sql-100', 'Finish Prepare Database for Backup', 1);
				ibk_debug("Backup: Start Backup DB - SUCCESS");
			} catch (Exception $e){
				ibk_debug("Backup: eNDS Backup DB - ERROR");
				$msg = 'Unable to Backup Database. ' . $e->getMessage();
				$this->log_object->insert_log('sql', 'Error: '.$msg, 1);
				$this->send_email(3, $msg);//send the error via e-mail
				unset($msg);
				unset($e);
			}

			
			///make zip
			if ($this->temp_dir_sql){
				// put the sql files into zip
				ibk_debug("Backup: START Make Zip for DB", 1);
				$this->make_zip($this->temp_dir_sql);
				ibk_debug("Backup: FINISH Make Zip for DB",1);
			}		
		}
		
		private function backup_files(){
			/*
			 * add files and folders to zip file
			 * @param none
			 * @return none
			 */
			ibk_debug("Backup: START Backup Files");
			$this->log_object->insert_log('files-0', 'Starting Prepare Files for Backup', 1);
			try {
				$dirs = FALSE;
				if ($this->backup_metas['save_files'] == 'all') {
					$dirs = array(
									'themes',
									'plugins',
									'uploads',
									'wp-config.php',
					);
				} else if ($this->backup_metas['save_files']=='custom'){
					$dirs = explode(',', $this->backup_metas['save_files_list']);
				}

				$excluded_folders = ibk_get_local_storage_destination_dirs();
				$excluded_folders[] = WP_CONTENT_DIR . '/uploads/indeed-backups';
				$excluded_folders[] = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'];			
				
				/******************* START excluded folders ********************/
				if (!empty($this->backup_metas['excluded_folders'])){
					$excluded_folders_from_dashboard = explode(',', $this->backup_metas['excluded_folders']);
					foreach ($excluded_folders_from_dashboard as $k=>$v){
						$v = trim($v);
						if (substr($v, 0, 1)!='/'){
							$v = '/' . $v;
						} 
						if (substr($v, -1, 1)=='/'){
							$v = substr_replace($v, "", -1, 1);
						}
						//if (strpos($v, WP_CONTENT_DIR)===FALSE){
						//	$v = WP_CONTENT_DIR . $v;
						//}
						$excluded_folders[] = $v;
					}
				}
				/******************* END excluded folders ********************/

				/******************* START MultiSite ********************/
				$uploads_dir_arr = array();
				if (!empty($this->backup_metas['blog_id']) && $this->backup_metas['blog_id']>1 && in_array('uploads', $dirs)){					
					$dirs = array_diff($dirs, array('uploads'));//first we remove the uploads from our array
					
					$handle = opendir(WP_CONTENT_DIR . '/uploads/sites/' . $this->backup_metas['blog_id'] . "/");
					while (false !== ($entry = readdir($handle))) {
						if (strpos($entry, '.')!==0){
							$dirs[] = 'uploads/sites/' . $this->backup_metas['blog_id'] . "/" . $entry;
							$uploads_dir_arr[] = 'uploads/sites/' . $this->backup_metas['blog_id'] . "/" . $entry; 
							$this->sites_folders[] = $entry;
						}
					}					
					
					$handle = opendir(WP_CONTENT_DIR . '/uploads/');
					while (false !== ($entry = readdir($handle))) {
						if (strpos($entry, '.')!==0){
							if (!preg_match("/^[0-9]{4}$/", $entry) && $entry!='sites' && $entry!='indeed-backups' && $entry!=$this->general_metas['ibk_backup_dir']){ //no years allowed
								$dirs[] = 'uploads/' . $entry;
								$uploads_dir_arr[] = 'uploads/' . $entry;
							}
						}
					}
				}
				/******************* END MultiSite ********************/
				if(!empty($this->backup_metas['excluded_files'])){
					$excluded_files = explode(',', $this->backup_metas['excluded_files']);
					if ($excluded_files){
						foreach ($excluded_files as $k=>$v ){
							$excluded_files[$k] = trim($v);
						}
					}
				}
				$progress_step = floor(100/count($dirs));
				$progress = 0;
				
				foreach ($dirs as $dir){
					@set_time_limit(3600);
					$parent_folder_inside = (in_array($dir, $uploads_dir_arr)) ? 'uploads/' : "";
					
					//set the path to the target dir
					if ($dir=='wp-config.php'){
						$dir = ABSPATH . 'wp-config.php';
					} else {
						$dir = WP_CONTENT_DIR . '/' . $dir;
					}
					ibk_debug("Backup: START Make Zip for ==".$dir."==",1);					

						$this->make_zip($dir, $excluded_folders, $excluded_files, $parent_folder_inside);
					
					ibk_debug("Backup: FINISH Make Zip for ==".$dir."==",1);
					$progress += $progress_step;
					$this->log_object->insert_log('files-' . $progress, 'Backup Files.', 1);
				}
				$this->log_object->insert_log('files', 'Finish Prepare Files for Backup', 1);	
				ibk_debug("Backup: End Backup Files - SUCCESS");
							
			} catch (Exception $e){
				ibk_debug("Backup: End Backup Files - ERROR - ". $e->getMessage());	
				$msg = 'Unable to Backup Files. ' . $e->getMessage();
				$this->log_object->insert_log('files', 'Error: '.$msg, 1);
				$this->send_email(3, $msg);//send the error via e-mail
			}
			
			//clean up
			if (isset($e)) unset($e);
			if (isset($msg)) unset($msg);
			if (isset($dirs)) unset($dirs);
			if (isset($dir)) unset($dir);
			if (isset($excluded_folders)) unset($excluded_folders);
			if (isset($excluded_folders_from_dashboard)) unset($excluded_folders_from_dashboard);
			if (isset($k)) unset($k);
			if (isset($v)) unset($v);
			if (isset($handle)) unset($handle);
			if (isset($entry)) unset($entry);
			if (isset($uploads_dir_arr)) unset($uploads_dir_arr);
			if (isset($excluded_files)) unset($excluded_files);
			if (isset($parent_folder_inside)) unset($parent_folder_inside);			
		}

		
		private function create_sql_temp_dir(){
			/*
			 * Create temporary folder to hold sql files
			 * @param none
			 * @return boolean true if ok
			 */
			$created = FALSE;
			ibk_debug("Backup: Backup DB - Start create Temp Dir file", 1);
			$file = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/indeed_' . $this->backup_id . '_' . $this->created_zip_date;
			if (!file_exists($file)){
				try {
					$created = @mkdir($file, 0777, TRUE);
					ibk_debug("Backup: Backup DB - create Temp Dir file SUCCESS",1);
				} catch (Exception $e){
					ibk_debug("Backup: Backup DB - create Temp Dir file ERROR");
					$msg = 'Failed to create temporary folder: ' . $file . ', '. $e->getMessage();
					$this->log_object->insert_log('sql', 'Error: '.$msg, 2);
					$this->send_email(3, $msg);//send the error via e-mail
					unset($msg);
					unset($e);
				}				
			}
			ibk_debug("Backup: Backup DB - Start create Temp Dir SQL folder", 1);	
			
			$this->temp_dir_sql =  $file . '/sql';
			
			if (!file_exists($this->temp_dir_sql)){
				try {
					@mkdir($this->temp_dir_sql, 0777, TRUE);
					ibk_debug("Backup: Backup DB - create Temp Dir SQL folder SUCCESS",1);
				} catch (Exception $e){
					ibk_debug("Backup: Backup DB - create Temp Dir SQL folder ERROR");
					$msg = 'Failed to create temporary folder: ' . $this->temp_dir_sql . ', '. $e->getMessage();
					$this->log_object->insert_log('sql', 'Error: '.$msg, 2);
					$this->send_email(3, $msg);//send the error via e-mail
					unset($msg);
					unset($e);
				}
			}
			//clean up 
			unset($file);
			return $created;
		}

		private function write_sql_file($table_name){
			/*
			 * copy mysql table and store into a file
			 * @param target database table, sql dir
			 * @return none
			 */
			global $wpdb;
			ibk_debug("Backup: START SQL File ".$table_name,1);
			if (!empty($this->backup_metas['blog_id']) && $this->backup_metas['blog_id'] > 1 && ibk_is_table_native_wp($table_name, $this->backup_metas['blog_id'])){
				$the_prefix = $wpdb->base_prefix . $this->backup_metas['blog_id'] . '_';
			} else {
				$the_prefix = $wpdb->prefix;
			}
				
			//$table_name = str_replace($the_prefix, '', $table_name);
			$filename = $this->temp_dir_sql . '/' . $table_name .'.sql';
			try {
				ibk_debug("Backup: SQL File " . $table_name . " - Create file : " . $filename ,1);
				$file = fopen($filename, 'w');
			} catch (Exception $e){
				ibk_debug("Backup: SQL File " . $table_name . " - Create file " . $filename." ERROR " . $e->getMessage() ,1);
				$msg = 'Unable to create sql file: ' . $filename . '. ' . $e->getMessage();
				$this->log_object->insert_log('sql', 'Error: '.$msg, 2);
				$this->send_email(3, $msg);//send the error via e-mail
			}
		
			try {
				/*********** create table ***********/
				$temp_table_name = $table_name.'_indeed_temp';
				$q = 'SHOW CREATE TABLE ' . $the_prefix . $table_name;
				$data = $wpdb->get_results($q);
				$table = (array)$data[0];
				$table['Create Table'] = str_replace('CREATE TABLE `' . $the_prefix . $table_name . '`', 'CREATE TABLE IF NOT EXISTS `' . $temp_table_name . '`', $table['Create Table']);
				$create_str = str_replace("\n", "", $table['Create Table']) . "\n";
				ibk_debug("Backup: SQL File ".$table_name ." - write: ==".$create_str."== " ,2);
				fwrite($file, $create_str);
		
				ibk_debug("Backup: SQL File " . $table_name . " - write:==TRUNCATE TABLE `" . $temp_table_name . "`;== " ,2);
				fwrite($file, "TRUNCATE TABLE `" . $temp_table_name . "`;\n");
				/*********** insert values into table ************/
				$start_limit = 0;
				$limit_step = (empty($this->general_metas['ibk_db_segmentation'])) ? ibk_segmentation_sugestion() : $this->general_metas['ibk_db_segmentation'];
				$loop = TRUE;
				$str = '';
				while ($loop){
					@set_time_limit(3600);
					$q = "SELECT * FROM " . $the_prefix . $table_name . " LIMIT $start_limit, $limit_step";
					$data = $wpdb->get_results($q);
					if ($data){
						$str = '';
						$subarr = FALSE;
						foreach ($data as $table_row){
							foreach ($table_row as $k=>$v){
								$arr[] = "'" . $wpdb->_real_escape(trim($v)) . "'";
							}
							$substring = '';
							$substring .= "(";
							$substring .= implode(",", $arr);
							$substring .= ")";
							$subarr[] = $substring;
							unset($arr);
						}
						$str .= "INSERT INTO `" . $temp_table_name . "` VALUES";
						$str .= implode(",", $subarr);
						$str .= ";\n";
						ibk_debug("Backup: SQL File " . $table_name . " - write: ==" . $start_limit . "== " ,2);
						fwrite($file, $str);
						$loop = TRUE;
					} else {
						$loop = FALSE;
					}
					$start_limit = $start_limit + $limit_step;
				}
				fclose($file);
				ibk_debug("Backup: SQL File " . $table_name ." SUCCESS ",1);
			} catch (Exception $e){
				ibk_debug("Backup: SQL File ".$table_name ." ERROR " . $e->getMessage(),1);
				$msg = 'Unable to save table: ' . $table_name . '. ' . $e->getMessage();
				$this->log_object->insert_log('sql', 'Error: '.$msg, 2);
				$this->send_email(3, $msg);//send the error via e-mail
			}
				
			//clean up
			if (isset($table_name)) unset($table_name);
			if (isset($file)) unset($file);
			if (isset($filename)) unset($filename);
			if (isset($temp_table_name)) unset($temp_table_name);
			if (isset($start_limit)) unset($start_limit);
			if (isset($limit_step)) unset($limit_step);
			if (isset($loop)) unset($loop);
			if (isset($str)) unset($str);
			if (isset($the_prefix)) unset($the_prefix);
			if (isset($create_str)) unset($create_str);
			if (isset($q)) unset($q);
			if (isset($data)) unset($data);
			if (isset($substring)) unset($substring);
			if (isset($arr)) unset($arr);
			if (isset($subarr)) unset($subarr);
			if (isset($v)) unset($v);
			if (isset($k)) unset($k);
			if (isset($msg)) unset($msg);
			if (isset($e)) unset($e);
		}


		private function indeed_rmdir_recursive($dir) {
			/*
			 * delete a directory with all files and folders that contains
			 * @param target directory to delete
			 * @return none
			 */
			try {
				foreach (scandir($dir) as $file) {
					if ('.' === $file || '..' === $file){
						continue;
					}
					if (is_dir("$dir/$file")){
						$this->indeed_rmdir_recursive("$dir/$file");
					} else {
						unlink("$dir/$file");
					}
				}
				rmdir($dir);
				ibk_debug("Backup: Delete Temporary Files - SUCCESS", 1);				
			} catch (Exception $e){
				ibk_debug("Backup: Delete Temporary Files - ERROR");
				$msg = 'Unable to delete file or folder. ' . $e->getMessage();
				$this->log_object->insert_log('', 'Error: '.$msg, 2);
				$this->send_email(3, $msg);//send the error via e-mail
			}
			unset($dir);
			if (isset($file)) unset($file);
			if (isset($msg)) unset($msg);
			if (isset($e)) unset($e);

		}
		
		private function delete_temporary_sql_folder(){
			/*
			 * delete temporary sql folder after the zip file was closed
			 * @param none
			 * @return none
			 */
			if (!$this->general_metas['ibk_backup_files'] || $this->general_metas['ibk_backup_files'] == 0){				
				ibk_debug("Backup: START Delete Temporary Files", 1);
				$path = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/indeed_' . $this->backup_id . '_' . $this->created_zip_date;
				if (file_exists($path)){
					$this->indeed_rmdir_recursive($path);
				}
				unset($path);				
			}
		}
		
		private function open_zip(){
			/*
			 * @param none
			 * @return boolean true if ok
			 */
			global $zip;
			
			$zip = new ZipArchive();
			$zip->open(WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $this->filename, ZIPARCHIVE::CREATE);
			return TRUE;
			try {
				$zip = new ZipArchive();
				if (!$zip->open(WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $this->filename, ZIPARCHIVE::CREATE)) {
					ibk_debug("Backup: Make Zip - ERROR - Unable to create zip file on temporary destination!");
					return FALSE;
				}
			} catch (Exception $e){
				ibk_debug("Backup: Make Zip - ERROR - Unable to create zip file on temporary destination!" . $e->getMessage());
				$msg = 'Unable to create zip file! ' . $e->getMessage();
				$this->log_object->insert_log('zip', 'Error: '.$msg, 2);
				$this->send_email(3, $msg);//send the error via e-mail
				unset($msg);
				unset($e);
			}
			return TRUE;	
		}
		
		private function make_zip($source, $excluded_folders=array(), $excluded_files=FALSE, $parent_folder = '' ){
			/*
			 * @params
			 * sources string full path of files or folders
			 * excluded_files  array 
			 * parent_folder string
			 * @return none
			 */
			global $zip;			
			ibk_debug("Backup: Make Zip - START zip each file", 1);
			
		if(is_readable($source)){
			try {
				$source = str_replace('\\', '/', realpath($source));
				
				if (is_dir($source) === true){
					///////////////////////// FOLDER
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

					$arr = explode("/",$source);
					$maindir = $arr[count($arr)- 1];
					$source = "";
					for ($i=0; $i < count($arr) - 1; $i++) {
						$source .= '/' . $arr[$i];
					}
					$source = substr($source, 1);
					ibk_debug("Backup: Make Zip - add Dir: " . $maindir,2);
							
					foreach ($files as $file){
						
						//EXCLUDE FOLDERS
						foreach ($excluded_folders as $excluded_folder){
							if (strpos($file, $excluded_folder)!==FALSE){
								continue 2;
							}
						}
						
						if (is_dir($file) === true){
							ibk_debug("Backup: Make Zip - add Dir: ".$file,2);
							$zip->addEmptyDir($parent_folder . str_replace($source . '/', '', $file . '/'));
						} else if (is_file($file) === true){
							//EXCLUDE FILES
							if($excluded_files !== FALSE && is_array($excluded_files)){
								foreach ($excluded_files as $excluded_file){
									if (strpos($file, $excluded_file)!==FALSE){
										continue 2;
									}
								}
							}
							if(is_readable($file)){
								ibk_debug("Backup: Make Zip - add File: ".$file,2);
								$zip->addFile($file, $parent_folder . str_replace($source . '/', '', $file) );
							}else{
								ibk_debug("Backup: UNREADABLE file  - ERROR - could not be backed up: ".$file,2);
							}
						}							
					}//end foreach files
						
				} else if (is_file($source) === true){
					///////////////////////// FILE
					ibk_debug("Backup: Make Zip - add String: ".$source,2);
					$zip->addFromString(basename($source), file_get_contents($source));
				}
				ibk_debug("Backup: Make Zip - SUCCESS",1);
			} catch (Exception $e){
				ibk_debug("Backup: Make Zip - ERROR - Unable to add file to zip archive!" . $e->getMessage());
				$msg = 'Unable to add file to zip archive! ' . $e->getMessage();
				$this->log_object->insert_log('zip', 'Error: '.$msg, 2);
				$this->send_email(3, $msg);//send the error via e-mail
			}
		  }else{
		  ibk_debug("Backup: UNREADABLE dir  - ERROR - could not be backed up: ".$source,2);
		   }
							
			//clean up
			if (isset($excluded_folders)) unset($excluded_folders);
			if (isset($excluded_files)) unset($excluded_files);
			if (isset($excluded_folder)) unset($excluded_folder);
			if (isset($parent_folder)) unset($parent_folder);
			if (isset($source)) unset($source);
			if (isset($file)) unset($file);
			if (isset($zip_it)) unset($zip_it);
			if (isset($files)) unset($files);
			if (isset($msg)) unset($msg);
			if (isset($e)) unset($e);

		}
		
		
		private function move_file(){
			/*
			 * function that send zip file to destination
			 * @param none
			 * @return bool
			 */
			$file = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $this->filename;
			ibk_debug("Backup: START Move Files");
			@set_time_limit(3600);
			
			switch ($this->destination_metas['type']){
				case 'local':
					ibk_debug("Backup: Start Move Files -- 'local' ",1);
					try {
						//destination dir exists??
						if (!file_exists($this->destination_metas['local_folder_target'])) {
								ibk_debug("Backup: Move Files -- 'local' - start make directory ",1);
							@mkdir($this->destination_metas['local_folder_target'], 0777, TRUE);
								ibk_debug("Backup: Move Files -- 'local' - end make directory ",1);
						}
						//move file to destination dir
							ibk_debug("Backup: Move Files -- 'local' - start rename directory ",1);
						rename( $file, $this->destination_metas['local_folder_target'] . $this->filename );
							ibk_debug("Backup: Move Files -- 'local' - end rename directory ",1);
						
							$this->log_object->insert_log('sending_file-100', 'Finish Sending Backup File:  '.$this->filename.'  to Local Destination', 1);
							ibk_debug("Backup: Move Files -- 'local' - SUCCESS ",1);
						return TRUE;
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'local' - ERROR ". $e->getMessage());
						$msg = 'Failed to move Backup File '.$this->filename.' to Local Destination. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail		
						unset($msg);
						unset($e);				
					}					
					return FALSE;
				break;
				case 'google':					
					ibk_debug("Backup: START Move Files -- 'google' ",1);
					try {
						if (!class_exists('IndeedGoogle')){
							require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
						}						
						$goo = new IndeedGoogle($this->backup_metas['destination']);
						
						ibk_debug("Backup: Move Files -- 'google' - start send file ",1);
						$response = $goo->send_file( $file );
						
						if ($response===TRUE){
							//SUCCESS
							ibk_debug("Backup: Move Files -- 'google' - response received SUCCESS ",1);	
							$msg = 'Finish Sending Backup File: '.$this->filename.' to Google Drive';
							$this->log_object->insert_log('sending_file-100', $msg, 1);								
							ibk_debug("Backup:  Move Files -- 'google' - response received - delete zip Files ",1);							
							
							//DELETE ZIP FILE
							$this->delete_zip_file($file);
							
							//clean up
							unset($file);
							unset($response);
							unset($goo);
							ibk_debug("Backup: Move Files -- 'google' - SUCCESS ",1);
							
							return TRUE;
						} else {
							//ERROR
							if (is_array($response) && count($response)) {
								//write the error in logs 
								$msg = implode("\n", $reponse);
								$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
								ibk_debug("Backup:  Move Files -- 'google' - response received ERROR - ". $msg);	
								unset($msg);																					
							} else {
								ibk_debug("Backup:  Move Files -- 'google' - response received ERROR - empty ");
							}
						} 					
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'google' - ERROR " . $e->getMessage());	
						$msg = 'Fail sending Backup File ' . $this->filename . ' to Google Drive. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail	
						unset($e);
						unset($msg);					
					}
					if (isset($file)) unset($file);
					if (isset($response)) unset($response);
					if (isset($goo)) unset($goo);
					return FALSE;
				break;
				case 'ftp':
					ibk_debug("Backup: START Move Files -- 'ftp' ",1);
					try {
						if (!class_exists('IndeedFtp')){
							require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
						}						
						$ftp = new IndeedFtp($this->backup_metas['destination']);
						
						ibk_debug("Backup: Move Files -- 'ftp' - login step ",1);
						$ftp->login();
						
						ibk_debug("Backup: Move Files -- 'ftp' - start send file ",1);
						$response = $ftp->send_file( $file );
						$ftp->logout();

						if ($response===TRUE){
							ibk_debug("Backup:Move Files -- 'ftp' - response received SUCCESS ",1);	
							$this->log_object->insert_log('sending_file-100', 'Finish Sending Backup File: ' . $this->filename . ' to FTP', 1);
							ibk_debug("Backup: Move Files -- 'ftp' - response received - delete zip Files ",1);	
							
							$this->delete_zip_file($file);
							
							if (isset($file)) unset($file);
							if (isset($response)) unset($response);
							if (isset($ftp)) unset($ftp);
														
							return TRUE;
						} else {
							//we have an error
							if (is_array($response) && count($response)) {
								//write the error in logs 
								$msg = implode("\n", $reponse);
								$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
								ibk_debug("Backup: Move Files -- 'ftp' - response received ERROR - " . $msg);																						
							} else {
								ibk_debug("Backup: Move Files -- 'ftp' - response received ERROR - empty ");
							}
						}									
						ibk_debug("Backup: Move Files -- 'ftp' - SUCCESS ", 1);		
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'ftp' - ERROR ". $e->getMessage());
						$msg = 'Fail sending Backup File '.$this->filename.' to FTP. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail
						unset($e);
						unset($msg);
					}
					return FALSE;
				break;
				case 'dropbox':
					ibk_debug("Backup: START Move Files -- 'dropbox' ",1);
					try {
						if (!class_exists('IndeedDropbox')){
							require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
						}
						$dropbox_obj = new IndeedDropbox($this->backup_metas['destination']);
						ibk_debug("Backup: Move Files -- 'dropbox' - login step ",1);
						$logged = $dropbox_obj->login();
						ibk_debug("Backup: Move Files -- 'dropbox' - start send file ",1);
						$sent = $dropbox_obj->send_file($file, basename($file));						
						$msg = 'Finish Sending Backup File: '.$this->filename.' to Dropbox';
						$this->log_object->insert_log('sending_file-100', $msg, 1);
						
						ibk_debug("Backup: Move Files -- 'dropbox' - delete zip Files ",1);	
						$this->delete_zip_file($file);
						
						ibk_debug("Backup: Move Files -- 'dropbox' - SUCCESS ",1);	
						
						//clean up
						unset($dropbox_obj);
						unset($file);
						unset($msg);
						unset($logged);
						unset($sent);
						
						return TRUE;
						
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'dropbox' - ERROR ". $e->getMessage());
						$msg = 'Fail sending Backup File ' . $this->filename . ' to Dropbox. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail
						unset($msg);
						unset($e);
					}
					return FALSE;
				break;
				case 'rackspace':	
					ibk_debug("Backup: START Move Files -- 'rackspace' ",1);	
					try {
						require_once IBK_PATH . 'classes/API/IndeedRackSpace.class.php';						
						$obj = new IndeedRackSpace($this->backup_metas['destination']);
						
						ibk_debug("Backup: Move Files -- 'rackspace' - start send file ",1);
						$obj->send_file( $file );
						
						$msg = 'Finish Sending Backup File: ' . $this->filename . ' to Rackspace';
						$this->log_object->insert_log('sending_file-100', $msg, 1);
						ibk_debug("Backup: Move Files -- 'rackspace' - SUCCESS ",1);	
						unset($obj);
						unset($msg);
						return TRUE;
						
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'rackspace' - ERROR ".$e->getMessage());
						$msg = 'Fail sending Backup File '.$this->filename.' to RackSpace. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail		
						unset($msg);
						unset($e);				
					}
					return FALSE;
				break;
				case 'amazon':
					ibk_debug("Backup: START Move Files -- 'amazon' ",1);	
					try {
						if (!class_exists('IndeedAmazonS3')){
							require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
						}
						$obj = new IndeedAmazonS3($this->backup_metas['destination']);
						
						ibk_debug("Backup: Move Files -- 'amazon' - start send file ",1);
						$send = $obj->send_file($file);
						if ($send){
							$msg = 'Finish Sending Backup File: ' . $this->filename . ' to S3 Amazon';
							$this->log_object->insert_log('sending_file-100', $msg, 1);
							ibk_debug("Backup: Move Files -- 'amazon' - SUCCESS ");	
							unset($send);
							unset($obj);
							unset($msg);
							return TRUE;
						}
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'amazon' - ERROR " . $e->getMessage());
						$msg = 'Fail sending Backup File '.$this->filename.' to S3 Amazon. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail
						unset($e);
						unset($msg);				
					}
					return FALSE;
				break;
				case 'onedrive':
					ibk_debug("Backup: START Move Files -- 'OneDrive' ", 1 );
					try {
						if (!class_exists('IndeedOneDrive')){
							require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
						}
						$obj = new IndeedOneDrive($this->backup_metas['destination']);
						ibk_debug("Backup: Move Files -- 'OneDrive' - start send file ",1);
						$send = $obj->send_file($file, basename($file));
						if ($send){
							$msg = 'Finish Sending Backup File: ' . $this->filename . ' to OneDrive';
							$this->log_object->insert_log('sending_file-100', $msg, 1);
							ibk_debug("Backup: Move Files -- 'OneDrive' - SUCCESS ");
							unset($send);
							unset($obj);
							unset($msg);
							return TRUE;							
						}
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'OneDrive' - ERROR " . $e->getMessage());
						$msg = 'Fail sending Backup File '.$this->filename.' to OneDrive. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail
						unset($e);
						unset($msg);						
					}
					return FALSE;
					break;
				case 'copy':
					ibk_debug("Backup: START Move Files -- 'Copy.com' ", 1 );
					try {
						if (!class_exists('IndeedCopyDotCom')){
							require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
						}
						$obj = new IndeedCopyDotCom($this->backup_metas['destination']);
						$obj->login();
						ibk_debug("Backup: Move Files -- 'Copy.com' - start send file ",1);
						$send = $obj->upload_file($file, basename($file));
						if ($send){
							$msg = 'Finish Sending Backup File: ' . $this->filename . ' to Copy.com';
							$this->log_object->insert_log('sending_file-100', $msg, 1);
							ibk_debug("Backup: Move Files -- 'Copy.com' - SUCCESS ");
							unset($send);
							unset($obj);
							unset($msg);
							return TRUE;
						}
					} catch (Exception $e){
						ibk_debug("Backup: Move Files -- 'Copy.com' - ERROR " . $e->getMessage());
						$msg = 'Fail sending Backup File '.$this->filename.' to Copy.com. ' . $e->getMessage();
						$this->log_object->insert_log('sending_file', 'Error: '.$msg, 2);
						$this->send_email(3, $msg);//send the error via e-mail
						unset($e);
						unset($msg);
					}
					return FALSE;
					break;
			}
			return FALSE;
		}

	
		private function delete_zip_file($file){
			/*
			 * delete zip file
			 * @param zip file full path 
			 * @return none
			 */
			if (!$this->general_metas['ibk_backup_files'] || $this->general_metas['ibk_backup_files'] == 0){
				try {
					if (file_exists($file)){
						unlink($file);						
						ibk_debug("Backup: Delete Zip File '" . $file . "' - SUCCESS ",1);
						$this->log_object->insert_log('delete_zip', 'Delete temporary file: ' . $file , 1);
					}
				} catch (Exception $e){					
					ibk_debug("Backup: Delete Zip File - ERROR ".$e->getMessage());
					$msg = 'Unable to delete: ' . $file . '. '. $e->getMessage();
					$this->log_object->insert_log('delete_zip', 'Error: '.$msg, 2);
					$this->send_email(3, $msg);//send the error via e-mail
					unset($msg);
					unset($e);
				}
			}
			unset($file);
		}		
		
		
		
		
		private function check_version_limit(){
			/*
			 * use this for 'History Versions' available for each snapshot,
			 * @param none
			 * @return removed file
			 */
			switch ($this->destination_metas['type']){
				case 'local':			
					$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->destination_metas['local_folder_target']), RecursiveIteratorIterator::SELF_FIRST);
					$min_timestamp = time();
					foreach ($files as $file){
						$file = str_replace('\\', '/', $file);
						$file_h = basename($file);
						if (preg_match("#^superbackup(.*)$#i", $file_h)){
							//it contains indeed
							$is_zip_data = explode('.', $file_h);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot
									$snapshot_instances[$file_name_data[3]]	= $file;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
									}									
								}
							}
						}
						if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
							//history has hit the limit, we must delete the first instance of this snapshot
							if (file_exists($snapshot_instances[$min_timestamp])){
								unlink($snapshot_instances[$min_timestamp]);
								
								//clean up
								if (isset($files)) unset($files);
								if (isset($file)) unset($file);
								if (isset($file_h)) unset($file_h);
								if (isset($is_zip_data)) unset($is_zip_data);
								if (isset($file_name_data)) unset($file_name_data);
								
								return $snapshot_instances[$min_timestamp];
							}							
						}					
					}//end of foreach
				break;
				
				case 'ftp':
					if (!class_exists('IndeedFtp')){
						require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
					}
					$ftp = new IndeedFtp($this->backup_metas['destination']);//destination id
					$ftp->login();
					$snapshot_instances = $ftp->list_snapshots($this->backup_id);//snapshot id
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						$min_timestamp = time();
						foreach ($snapshot_instances as $k=>$v){
							if ($min_timestamp>$k){
								$min_timestamp = $k;
							}
						}
						$ftp->delete_target_file($snapshot_instances[$min_timestamp]);
						$ftp->logout();
						
						//clean up
						unset($ftp);
						
						return $snapshot_instances[$min_timestamp];
					}
					$ftp->logout();
				break;
				
				case 'google':
					require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';				
					$goo = new IndeedGoogle($this->backup_metas['destination']);
					$goo->login();
					$data = $goo->retrieveAllFiles();
					$min_timestamp = time();
					foreach ($data as $file_obj){
						if (preg_match("#^superbackup(.*)$#i", $file_obj->title)){
							//it contains indeed
							$is_zip_data = explode('.', $file_obj->title);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot				
									$snapshot_instances[$file_name_data[3]]	= $file_obj->id;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
										$removed_instance = $file_obj->title;
									}
								}
							}
						}
					}
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						//history has hit the limit, we must delete the first instance of this snapshot
						$goo->deleteFile($snapshot_instances[$min_timestamp]);
						
						//clean up
						unset($goo);
						unset($data);
						if (isset($is_zip_data)) unset($is_zip_data);
						if (isset($file_name_data)) unset($file_name_data);
						if (isset($file_obj)) unset($file_obj);
						
						return $removed_instance;
					}
				break;
				
				case 'dropbox':					
					require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
					$obj = new IndeedDropbox($this->backup_metas['destination']);
					$obj->login();
					$data = $obj->get_files();
					$min_timestamp = time();
					foreach ($data as $file){
						if (preg_match("#superbackup(.*)$#i", $file)){
							
							//it contains indeed
							$title = basename($file);
							$is_zip_data = explode('.', $title);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot
									$snapshot_instances[$file_name_data[3]]	= $file;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
									}
								}
							}
						}
					}
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						//history has hit the limit, we must delete the first instance of this snapshot
						$obj->delete_file($snapshot_instances[$min_timestamp]);
						
						//clean up
						unset($obj);
						if (isset($data)) unset($data);
						if (isset($file)) unset($file);
						if (isset($is_zip_data)) unset($is_zip_data);
						if (isset($file_name_data)) unset($file_name_data);
						
						return basename($snapshot_instances[$min_timestamp]);
					}
				break;
				
				case 'amazon':
					require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
					$obj = new IndeedAmazonS3($this->backup_metas['destination']);
					$data = $obj->get_files_list();
					$min_timestamp = time();
					foreach ($data as $file){
						if (preg_match("#superbackup(.*)$#i", $file)){
							//it contains indeed
							$title = basename($file);
							$is_zip_data = explode('.', $title);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot
									$snapshot_instances[$file_name_data[3]]	= $file;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
									}
								}
							}
						}
					}
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						//history has hit the limit, we must delete the first instance of this snapshot
						$obj->delete_file($snapshot_instances[$min_timestamp]);
						
						//clean up
						unset($obj);
						if (isset($data)) unset($data);
						if (isset($file)) unset($file);
						if (isset($is_zip_data)) unset($is_zip_data);
						if (isset($file_name_data)) unset($file_name_data);
						
						return basename($snapshot_instances[$min_timestamp]);
					}
				break;		

				case 'onedrive':
					require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
					$obj = new IndeedOneDrive($this->backup_metas['destination']);
					$files = $obj->return_all_files();
					$min_timestamp = time();
					foreach ($files as $file_arr){
						$file = $file_arr['name'];
						if (preg_match("#superbackup(.*)$#i", $file)){
							//it contains indeed
							$title = basename($file);
							$is_zip_data = explode('.', $title);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot
									$snapshot_instances[$file_name_data[3]]	= $file;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
									}
								}
							}
						}
					}
					
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						//history has hit the limit, we must delete the first instance of this snapshot
						$obj->delete_file($snapshot_instances[$min_timestamp]);
						//clean up
						unset($obj);
						if (isset($data)) unset($data);
						if (isset($file)) unset($file);
						if (isset($is_zip_data)) unset($is_zip_data);
						if (isset($file_name_data)) unset($file_name_data);
						return basename($snapshot_instances[$min_timestamp]);
					}
					
					break;
					
				case 'copy':
					require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
					$obj = new IndeedCopyDotCom($this->backup_metas['destination']);
					$obj->login();
					$files = $obj->get_all_files();
					$min_timestamp = time();
					foreach ($files as $file){
						if (preg_match("#superbackup(.*)$#i", $file)){
							//it contains indeed
							$title = basename($file);
							$is_zip_data = explode('.', $title);
							if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
								//it's a zip file
								$file_name_data = explode('_', $is_zip_data[0]);
								if ($file_name_data[2]==$this->backup_id && $file_name_data[1]==md5('superbackup_indeed') ){
									//it's a instance of our snapshot
									$snapshot_instances[$file_name_data[3]]	= $file;
									if ($file_name_data[3]<$min_timestamp){
										$min_timestamp = $file_name_data[3]; //store the minimum timestamp available
									}
								}
					 		}
						}
					}
							
					if (!empty($snapshot_instances) && count($snapshot_instances)>$this->backup_metas['max_archives']){
						//history has hit the limit, we must delete the first instance of this snapshot
						$obj->delete_file($snapshot_instances[$min_timestamp]);
						//clean up
						unset($obj);
						if (isset($data)) unset($data);
						if (isset($file)) unset($file);
						if (isset($is_zip_data)) unset($is_zip_data);
						if (isset($file_name_data)) unset($file_name_data);
						return basename($snapshot_instances[$min_timestamp]);
					}
						
				break;					
			}//end switch
			
			//clean up
			if (isset($obj)) unset($obj);
			if (isset($data)) unset($data);
			if (isset($file)) unset($file);
			if (isset($is_zip_data)) unset($is_zip_data);
			if (isset($file_name_data)) unset($file_name_data);
			if (isset($goo)) unset($goo);
		}
		
		private function write_log_snapshot_file($removed_file=FALSE){
			/*
			 * log text file (CLoud)
			 * @param file to remove from log
			 * @return none
			 */
			if (!class_exists('IndeedSnapshotText')){
				require_once IBK_PATH . 'classes/IndeedSnapshotText.class.php';
			}
			$cloud_obj = new IndeedSnapshotText($this->backup_metas['destination'], $this->backup_id);
			$cloud_obj->set_log($this->backup_metas, $this->filename, $this->general_metas['ibk_backup_dir'], $removed_file, $this->sites_folders);	
			unset($removed_file);			
			unset($cloud_obj);
		}		
		
		
		
		private function init_backup_metas(){
			/*
			 * set backup metas
			* @param none
			* @return none
			*/
			ibk_debug("Backup: START Set Snapshot Metas",1);
			if (!empty($this->backup_id)){
				try {
					$this->backup_metas = ibk_return_metas_from_custom_db('backups', $this->backup_id);
					ibk_debug("Backup: Set Snapshot Metas - SUCCESS", 1);
				} catch (Exception $e){
					ibk_debug("Backup: Set Snapshot Metas - ERROR");
					$msg = 'Unable to initiate Backup Metas. '. $e->getMessage();
					$this->log_object->insert_log('start', 'Error: '.$msg, 2);
					$this->send_email(3, $msg);//send the error via e-mail
					unset($msg);
					unset($e);
				}
			}
		}
		
		private function init_destination_metas(){
			/*
			 * set destination metas
			 * @param none
			 * @return none
			 */
		
			ibk_debug("Backup: START Set Destination Metas",1);
			if (!empty($this->backup_metas['destination'])){
				try {
					$this->destination_metas = ibk_return_metas_from_custom_db('destinations', $this->backup_metas['destination']);
					ibk_debug("Backup: Set Destination Metas - SUCCESS", 1);
				} catch (Exception $e){
					ibk_debug("Backup: Set Destination Metas - ERROR");
					$msg = 'Unable to initiate Destination Metas. '. $e->getMessage();
					$this->log_object->insert_log('start', 'Error: '.$msg, 2);
					$this->send_email(3, $msg);//send the error via e-mail
					unset($msg);
					unset($e);
				}
			}
		}
		
		private function init_general_metas(){
			/*
			 * @param none
			* @return none
			*/
			ibk_debug("Backup: SET General Metas", 1);
			$this->general_metas = ibk_get_general_metas();
		}
		
		private function can_we_write_files(){
			/*
			 * @param none
			 * @return bool
			 */
			if (!ibk_check_dir_if_writable(WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/')){
				//temp dir
				$this->log_object->insert_log('start-100', 'Error: Unable to write on: ' . WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'], 2);
				ibk_debug("Backup: Test write on " . WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . " - ERROR");
				return FALSE;
			}
			if (!ibk_check_dir_if_writable(WP_CONTENT_DIR . '/uploads/indeed-backups/')){
				//log dir
				$this->log_object->insert_log('start-100', 'Error: Unable to write on: ' . WP_CONTENT_DIR . '/uploads/indeed-backups/', 2);
				ibk_debug("Backup: Test write on " . WP_CONTENT_DIR . "/uploads/indeed-backups/ - ERROR");
				return FALSE;
			}
			if ($this->destination_metas['type']=='local' && !ibk_check_dir_if_writable($this->destination_metas['local_folder_target']) ){
				//local destination dir
				$this->log_object->insert_log('start-100', 'Error: Unable to write on: ' . $this->destination_metas['local_folder_target'], 2);
				ibk_debug("Backup: Test write on " . $this->destination_metas['local_folder_target'] . " - ERROR");
				return FALSE;
			}
			return TRUE;
		}
		
		private function set_cron(){
			/*
			 * if it's case set the cron for next backup
			*/
			if ($this->backup_metas['backup_interval_type']==1 && isset($this->backup_metas['cron-periodically'])){
				$time = time() + ($this->backup_metas['cron-periodically']*60*60);
				wp_schedule_single_event( $time , 'indeed_main_job', array( $this->backup_id ) );
				
				//clean up
				unset($time);
				unset($this->backup_metas['cron-periodically']);
				unset($this->backup_metas['backup_interval_type']);
			}
		}
		
		private function set_memory_limit(){
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
			ibk_debug('Backup: Memory limit set at: ' . $limit, 1);
			
			unset($limit);
		}
		
		private function send_email($type, $msg=FALSE){
			/*
			 * @param type int, msg string
			 * 1 - process start
			 * 2 - process end
			 * 3 - error occured
			 * use msg for errors
			 * @return none
			 */
			if (!$this->general_metas['ibk_email_sent']) return;
			ibk_debug("Backup: START Send Email",1);
			$do = FALSE;
			$site_name = get_option("siteurl");
			$backup_name = $this->backup_metas['name'];
			if ($type==1){
				//process start
				if ($this->general_metas['ibk_email_sent_2']){
					$do = true;
					$subject = $backup_name . ' started!';
					$msg = $backup_name . ' start at ' . date("Y-m-d H:i:s", $this->created_zip_date) . " on: " . $site_name;
				}
			} else if ($type==2){
				//process end
				if ($this->general_metas['ibk_email_sent_1']){
					$do = true;
					$subject = $backup_name . ' end!';
					$msg = $backup_name . ' end at ' . date("Y-m-d H:i:s", time()) . " on: " . $site_name;
				}
			} else {
				if ($this->general_metas['ibk_email_sent_3']){
					$do = true;
					$subject = $backup_name . ' ends, unexpected error!';
					if (!$msg){
						$msg = 'During ' . $backup_name . ' an unexpected error have occurred' . " on: " . $site_name . ' , at ' . date("Y-m-d H:i:s", time());
					}
				}
			}
			if ($do){
				$to = $this->general_metas['ibk_email'];
				$sent = wp_mail( $to, $subject, $msg );
				if ($sent){
					ibk_debug("Backup: Send Email" . $type . " succesfully sent!",1);
				}
			}
		
			//clean up
			if (isset($type)) unset($type);
			if (isset($msg)) unset($msg);
			if (isset($do)) unset($do);
			if (isset($backup_name)) unset($backup_name);
			if (isset($site_name)) unset($site_name);
			if (isset($to)) unset($to);
			if (isset($sent)) unset($sent);
			if (isset($subject)) unset($subject);
		}
				
	}//end of IndeedDoBackup Class
}

