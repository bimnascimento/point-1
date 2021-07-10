<?php
/*
 * Used for Cloud section
 */

if (!class_exists('IndeedSnapshotText')){
	class IndeedSnapshotText{
		private $snapshot_id = FALSE;
		private $destination_id = FALSE;
		private $destination_type = FALSE;
		private $file_name = FALSE;		
		private $temp_file = FALSE;
		private $temp_dir = FALSE;
		
		public function __construct($destinationId, $snapshotId){
			ibk_debug("Snapshot Text: START");
			$this->destination_id = $destinationId;	
			$this->snapshot_id = $snapshotId;
			$this->destination_type = ibk_get_destination_type($destinationId);
		}
		
		public function set_log($snapshotMetas, $fileName, $temp_dir, $remove_instance=FALSE, $sites_folders=array()){
			/*
			 * write current snapshot log into file
			 * @param $snapshotMetas current snapshot settings, $fileName name of zip snapshot instance 
			 */
			ibk_debug("Snapshot Text: START Set Log");
			$this->file_name = 'superbackup_' . $this->snapshot_id . '.log';//log FILENAME
			$this->temp_dir = WP_CONTENT_DIR . '/uploads/' . $temp_dir . '/';
			$this->temp_file = $this->temp_dir . $this->file_name;	
					
			$date = explode('_', $fileName);
			if (isset($date[3])){
				$last_run = str_replace('.zip', '', $date[3]);
			}
			
			$this->get_file();
			
			if (file_exists($this->temp_file)){				
				$str = file_get_contents($this->temp_file);
				ibk_debug("Snapshot Text: Set Log - Getting data from temporary file - SUCESS", 1);
				unlink($this->temp_file);
				ibk_debug("Snapshot Text: Set Log - Delete temporary file", 2);
			} else {
				ibk_debug("Snapshot Text: Set Log - Temporary file does not exists - ERROR", 1);
				$str = FALSE;
			}
			
			if ($str){
				//adding new instances of snapshot
				ibk_debug("Snapshot Text: Set Log - START write new data", 1);
				$data = unserialize($str);
				$data['file_arr'][] = $fileName;
				$data['last_run'] = $last_run;
				
				//remove file $remove_instance (version limit)
				if ($remove_instance){
					//echo array_search($remove_instance, $data['file_arr']),"<br/>";
					$removed_key = array_search($remove_instance, $data['file_arr']);
					if ($removed_key!==FALSE){						
						unset($data['file_arr'][$removed_key]);
					}
					ibk_debug("Snapshot Text: Set Log - Remove old instance that", 2);
				}
			} else {
				//create snapshot log
				$data = array(
								'snapshot_name' => $snapshotMetas['name'],
								'snapshot_description' => $snapshotMetas['description'],
								'admin_box_color' => $snapshotMetas['admin_box_color'],								
								'tables' => $snapshotMetas['save_db_table_list'],
								'last_run' => $last_run,
								'file_arr' => array($fileName),
								'blog_id' => $snapshotMetas['blog_id'],
							);
				if ($snapshotMetas['save_files']=='all'){					
					$data['files'] = 'themes,plugins,uploads,wp-config.php';
				} else if ($snapshotMetas['save_files']=='none'){
					$data['files'] = '';
				} else {
					$data['files'] = $snapshotMetas['save_files_list'];
				}
				ibk_debug("Snapshot Text: Set Log - Files : " . $data['files'], 2);
				
				$tables = explode(',', $snapshotMetas['save_db_table_list']);
				if ($tables){
					foreach ($tables as $t){
						if (!empty($snapshotMetas['blog_id']) && $snapshotMetas['blog_id']>1){
							$blog_id = $snapshotMetas['blog_id'];
						} else {
							$blog_id = get_current_blog_id();
						}
						if (ibk_is_table_native_wp($t, $blog_id)){
							$native_tb[] = $t;
						}
					}
					ibk_debug("Snapshot Text: Set Log - DB Tables : " . $snapshotMetas['save_db_table_list'], 2);
				}
				if (!empty($native_tb)){
					$data['native_wp_tables'] = implode(',', $native_tb);
					ibk_debug("Snapshot Text: Set Log - Native DB Tables : " . $data['native_wp_tables'], 2);
				}
				
				if (!empty($snapshotMetas['blog_id']) && $snapshotMetas['blog_id']>1){
					if (!empty($sites_folders)){
						$data['sites_folders'] = implode(',', $sites_folders);
						ibk_debug("Snapshot Text: Set Log - Site Folders : " . $data['sites_folders'], 2);
					} 
				}
			}
			
			//$data_for_write = json_encode($data);
			$data_for_write = serialize($data);
			
			file_put_contents($this->temp_file, $data_for_write);
			ibk_debug("Snapshot Text: FINISH Set Log");
			
			$this->send_file();
			unlink($this->temp_file);			
		}
		
		private function get_file(){
			ibk_debug("Snapshot Text: START Get File");
			switch ($this->destination_type){
				case 'local':
					require_once IBK_PATH . 'classes/API/IndeedLocal.class.php';
					$obj = new IndeedLocal($this->destination_id);
					$success = $obj->get_file($destination_metas['local_folder_target'] . $this->file_name, $this->temp_file);
					if ($success){
						ibk_debug("Snapshot Text: Get File From Local Destination - SUCCESS ", 1);
					} else {
						ibk_debug("Snapshot Text: Get File From Local Destination - ERROR ", 1);
					}
				break;
				case 'ftp':
					require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
					$obj = new IndeedFtp($this->destination_id);
					$obj->login();
					$file = $obj->get_log_file( $this->snapshot_id );
					$success = $obj->copy_file_to_local($file, $this->temp_file);
					if ($success){
						ibk_debug("Snapshot Text: Get File From FTP - SUCCESS ", 1);
					} else {
						ibk_debug("Snapshot Text: Get File From FTP - ERROR ", 1);
					}
				break;
				case 'google':
					//download the log file from google if exists
					require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
					$goo = new IndeedGoogle($this->destination_id);
					$goo->login();
					$data = $goo->retrieveAllFiles();
					if ($data){
						foreach ($data as $file_obj){
							if (preg_match("#^superbackup(.*)$#i", $file_obj->title)){
								$is_log = explode('.', $file_obj->title);
								if (isset($is_log[1]) && $is_log[1]=='log'){
									//it's a zip file
									$file_name_data = explode('_', $is_log[0]);
									if ($file_name_data[1]==$this->snapshot_id ){
										//it's a instance of our snapshot
										$target_id = $file_obj->id;
									}
								}
							}
						}
						if (!empty($target_id)){
							$file = $goo->downloadFile($target_id, $this->temp_dir);
							if ($file){
								ibk_debug("Snapshot Text: Get File From GoogleDrive - SUCCESS ", 1);
								$goo->deleteFile($target_id);
							} else {
								ibk_debug("Snapshot Text: Get File From GoogleDrive - ERROR ", 1);
							}
						} else {
							ibk_debug("Snapshot Text: Get File From GoogleDrive - ERROR (no target id)", 2);
						}						
					} else {
						ibk_debug("Snapshot Text: Get File From GoogleDrive - ERROR (no file list)", 2);
					}
				break;
				case 'dropbox':
					require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
					$obj = new IndeedDropbox($this->destination_id);
					$obj->login();
					$files = $obj->get_files();
					if ($files){
						foreach ($files as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								$is_log = explode('.', $file);
								if (isset($is_log[1]) && $is_log[1]=='log'){
									//it's a zip file
									$file_name_data = explode('_', $is_log[0]);
									if ($file_name_data[1]==$this->snapshot_id ){
										//it's a instance of our snapshot
										$source_file = $file;
									}
								}
							}
						}
						if (!empty($source_file)){
							$file = $obj->get_file($source_file, $this->temp_dir);
							if ($file){
								ibk_debug("Snapshot Text: Get File From Dropbox - SUCCESS ", 1);
								$obj->delete_file($source_file);
							} else {
								ibk_debug("Snapshot Text: Get File From Dropbox - ERROR ", 1);
							}
						}						
					} else {
						ibk_debug("Snapshot Text: Get File From Dropbox - ERROR (no file list)", 2);
					}				
				break;
				case 'amazon':
					require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
					$obj = new IndeedAmazonS3($this->destination_id);
					$files = $obj->get_files_list();
					if ($files){
						foreach ($files as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								$is_log = explode('.', $file);
								if (isset($is_log[1]) && $is_log[1]=='log'){
									//it's a zip file
									$file_name_data = explode('_', $is_log[0]);
									if ($file_name_data[1]==$this->snapshot_id ){
										//it's a instance of our snapshot
										$source_file = $file;
									}
								}
							}
						}
						if (!empty($source_file)){
							$file = $obj->get_file($source_file, $this->temp_dir);
							if ($file){
								ibk_debug("Snapshot Text: Get File From Amazon - SUCCESS ", 1);
								$obj->delete_file($source_file);
							} else {
								ibk_debug("Snapshot Text: Get File From Amazon - ERROR ", 1);
							}
						}						
					} else {
						ibk_debug("Snapshot Text: Get File From Amazon - ERROR (no file list)", 2);
					}					
				break;
				
				case 'onedrive':
					require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
					$obj = new IndeedOneDrive($this->destination_id);
					$files = $obj->return_all_files();
					if ($files){
						foreach ($files as $file_arr){
							$file = $file_arr['name'];
							if (preg_match("#superbackup(.*)$#i", $file)){
								$is_log = explode('.', $file);
								if (isset($is_log[1]) && $is_log[1]=='log'){
									//it's a zip file
									$file_name_data = explode('_', $is_log[0]);
									if ($file_name_data[1]==$this->snapshot_id ){
										//it's a instance of our snapshot
										$source_file = $file;
									}
								}
							}
						}
						if (!empty($source_file)){
							$file = $obj->get_file_by_name($source_file, $this->temp_dir . basename($source_file));
							if ($file){
								ibk_debug("Snapshot Text: Get File From OneDrive - SUCCESS ", 1);
								$obj->delete_file($source_file);
							} else {
								ibk_debug("Snapshot Text: Get File From OneDrive - ERROR ", 1);
							}
						}
					} else {
						ibk_debug("Snapshot Text: Get File From OneDrive - ERROR (no file list)", 2);
					}					
					break;
				
				case 'copy':
					if (!class_exists('IndeedCopyDotCom')){
						require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
					}
					$object = new IndeedCopyDotCom($this->destination_id);
					$object->login();
					$files = $object->get_all_files();
					if ($files){
						foreach ($files as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								$is_log = explode('.', $file);
								if (isset($is_log[1]) && $is_log[1]=='log'){
									//it's a zip file
									$file_name_data = explode('_', $is_log[0]);
									if ($file_name_data[1]==$this->snapshot_id ){
										//it's a instance of our snapshot
										$source_file = $file;
									}
								}
							}
						}
						if (!empty($source_file)){
							$file = $object->download_file($source_file, $this->temp_dir . basename($source_file) );
							if ($file){
								ibk_debug("Snapshot Text: Get File From Copy.com - SUCCESS ", 1);
								$object->delete_file($source_file);
							} else {
								ibk_debug("Snapshot Text: Get File From Copy.com - ERROR ", 1);
							}
						}
					} else {
						ibk_debug("Snapshot Text: Get File From Copy.com - ERROR (no file list)", 2);
					}
					break;
			}
			ibk_debug("Snapshot Text: FINISH Get File");
		}
		
		private function send_file(){
			/*
			 * @param none
			 * @return none
			 */
			ibk_debug("Snapshot Text: START Send File");
			$sent = FALSE;
			switch ($this->destination_type){
				case 'local':
					if (!class_exists('IndeedLocal')){
						require_once IBK_PATH . 'classes/API/IndeedLocal.class.php';
					}					
					$obj = new IndeedLocal($this->destination_id);
					$sent = $obj->send_file($this->temp_file, $this->file_name);
				break;
				case 'ftp':
					if (!class_exists('IndeedFtp')){
						require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
					}
					$obj = new IndeedFtp($this->destination_id);
					$obj->login();
					$sent = $obj->send_file( $this->temp_file );
				break;
				case 'google':
					if (!class_exists('IndeedGoogle')){
						require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
					}					
					$goo = new IndeedGoogle($this->destination_id);
					$sent = $goo->send_file( $this->temp_file );
				break;
				case 'dropbox':
					if (!class_exists('IndeedDropbox')){
						require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';						
					}
					$obj = new IndeedDropbox($this->destination_id);
					$obj->login();
					$sent = $obj->send_file($this->temp_file, basename($this->temp_file));		
				break;
				case 'amazon':
					if (!class_exists('IndeedAmazonS3')){
						require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';						
					}
					$obj = new IndeedAmazonS3($this->destination_id);
					$sent = $obj->send_file($this->temp_file);				
				break;
				case 'onedrive':
					if (!class_exists('IndeedOneDrive')){
						require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
					}
					$obj = new IndeedOneDrive($this->destination_id);
					$sent = $obj->send_file($this->temp_file, basename($this->temp_file));
				break;
				case 'copy':
					if (!class_exists('IndeedCopyDotCom')){
						require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
					}
					$object = new IndeedCopyDotCom($this->destination_id);
					$object->login();
					$sent = $object->upload_file($this->temp_file, basename($this->temp_file));
				break;					
			}
			if ($sent){
				ibk_debug("Snapshot Text: Send File to " . $this->destination_type . " Destination - SUCCESS", 1);
			} else {
				ibk_debug("Snapshot Text: Send File to " . $this->destination_type . " Destination - ERROR", 1);
			}
			ibk_debug("Snapshot Text: FINISH Send File");
		}
		
	}//end of class IndeedSnapshotText
}//end of if