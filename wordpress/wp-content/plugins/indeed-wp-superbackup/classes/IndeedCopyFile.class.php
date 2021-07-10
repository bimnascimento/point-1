<?php 
if (!class_exists('IndeedCopyFile')){
	class IndeedCopyFile{
		private $general_metas = FALSE;
		
		public function __construct(){
			ibk_debug("Copy File: START");
			require_once IBK_PATH . 'utilities.php';			
			$this->general_metas = ibk_get_general_metas();// set general metas
			
			//MEMORY
			$this->set_memory_limit();
			
			//PERMISSIONS
			if (!ibk_check_dir_if_writable(WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/')){
				ibk_debug("Copy File: ERROR - Cannot write files to " . WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/');
			}
			
			//EXECUTION TIME
			@set_time_limit(3600);
		}
		
		public function get_file_from_url($url){
			/*
			 * copy file from url to temp dir
			 * @param source url
			 * @return 
			 */
			ibk_debug("Copy File: Get File From URL START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From URL - ERROR (no backup directory set)", 1);
				return FALSE;
			}
			$url =  esc_url_raw($url);
			if (!empty($this->general_metas['ibk_backup_dir'])){
				if (function_exists('curl_version')) {
					$end = end((explode('/', rtrim($url, '/'))));
					$zip_file_name = $end;
					@set_time_limit(900);
					$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $zip_file_name;
					//getting file
					$fp = fopen($zip, 'w');
					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_FILE, $fp);
					$data = curl_exec($ch);
					curl_close($ch);
					fclose($fp);
					if ($zip){
						ibk_debug("Copy File: Get File From URL FINISH - SUCCESS");
						return $zip;						
					}
				} else {
					//write log, curl is not available
					ibk_debug("Copy File: Get File From URL - ERROR (curl not available)");
				}
			}
			return FALSE;	
		}
		
		public function get_file_from_google_drive($destination_id, $fileID){
			ibk_debug("Copy File: Get File From GoogleDrive START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From GoogleDrive - ERROR (no backup directory set)", 1);
				return FALSE;
			}
			if ($destination_id && $fileID){
				if (!class_exists('IndeedGoogle')){
					require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
				}
				$goo = new IndeedGoogle($destination_id);
				$goo->login();
				$zip = $goo->downloadFile($fileID, WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' );
				if ($zip){
					ibk_debug("Copy File: Get File From GoogleDrive FINISH");
					return $zip;					
				}				
			} else {
				ibk_debug("Copy File: Get File From GoogleDrive - ERROR (no destination id or file id)", 1);
				return FALSE;
			}
			return FALSE;	
		}
		
		public function get_file_from_upload(){
			/*
			 * move the uploaded file into the temp directory
			 * @param none, use the $_FILES
			 * @return 
			 */	
			ibk_debug("Copy File: Get File From Upload Field START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From Upload Field - ERROR (no backup directory set)", 1);
				return FALSE;
			}
			@set_time_limit(900);
			$move = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $_FILES["upload_file"]['name'];
			move_uploaded_file($_FILES['upload_file']['tmp_name'], $move );
			ibk_debug("Copy File: Get File From Upload Field FINISH");
			return $move;
		}
		
		public function get_file_from_ftp($destination_id, $source_file){
			/*
			 * copy zip file from ftp and put into temp directory
			 * @param id of destination (getting metas for conn)
			 * source file
			 * @return 
			 */
			ibk_debug("Copy File: Get File From FTP START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From FTP - ERROR (no backup directory set)", 1);
				return FALSE;
			}
			if (!class_exists('IndeedFtp')){
				require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
			}
			if ($destination_id && $source_file){
				$obj = new IndeedFtp($destination_id);
				$obj->login();
				$end = end((explode('/', rtrim($source_file, '/'))));
				$zip_file_name = $end;
				$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/' . $zip_file_name;
				$file = $obj->copy_file_to_local($source_file, $zip);
				if ($file){
					unset($obj);
					unset($end);
					unset($zip);
					unset($zip_file_name);
					ibk_debug("Copy File: Get File From FTP FINISH");
					return $file;
				}				
			} else {
				ibk_debug("Copy File: Get File From FTP - ERROR (no destination id or source file)", 1);
				return FALSE;					
			}
			return FALSE;
		}

		public function get_file_from_dropbox($destination_id, $source_file){
			/*
			 * source file is incomplete, don't have the path, 
			 */
			ibk_debug("Copy File: Get File From Dropbox START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From Dropbox - ERROR (no backup directory set)", 1);
				return FALSE;
			}	
			if ($destination_id && $source_file){
				require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
				$obj = new IndeedDropbox($destination_id);
				$obj->login();
				$files = $obj->get_files();
				foreach ($files as $file){
					if (basename($file)==$source_file){
						$source_file = $file;
					}
				}
				$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/';				
				$file = $obj->get_file($source_file, $zip);
				if ($file){
					unset($obj);
					unset($files);
					ibk_debug("Copy File: Get File From Dropbox FINISH");
					return $file;
				}				
			} else {
				ibk_debug("Copy File: Get File From Dropbox - ERROR (no destination id or source file)");
				return FALSE;
			}
			return FALSE;
		}
		
		public function get_file_from_amazon($destination_id, $source_file){
			ibk_debug("Copy File: Get File From Amazon START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From Amazon - ERROR (no backup directory set)", 1);
				return FALSE;
			}
			if ($destination_id && $source_file){
				require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
				$obj = new IndeedAmazonS3($destination_id);
				$files = $obj->get_files_list();
				foreach ($files as $file){
					if (basename($file)==$source_file){
						$source_file = $file;
					}
				}
				$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/';				
				$file = $obj->get_file($source_file, $zip);
				if ($file){
					unset($obj);
					unset($files);
					ibk_debug("Copy File: Get File From Amazon FINISH");
					return $file;
				}
			} else {
				ibk_debug("Copy File: Get File From Amazon - ERROR (no destination id or source file)");
				return FALSE;				
			}
		}
		
		public function get_file_from_one_drive($destination_id, $source_file){
			/*
			 * @param int, string
			 * @return string (name of file)
			 */
			ibk_debug("Copy File: Get File From OneDrive START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From OneDrive - ERROR (no backup directory set)", 1);
				return '';
			}
			if ($destination_id && $source_file){
				if (!class_exists('IndeedOneDrive')){
					require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
				}
				$obj = new IndeedOneDrive($destination_id);
				
				$files = $obj->return_all_files();
				foreach ($files as $file_data){
					$file = $file_data['name'];
					if (basename($file)==$source_file){
						$source_file = $file;
						break;
					}
				}
				
				$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/';
				$file = $obj->get_file_by_name($source_file, $zip . basename($source_file));
				if ($file){
					unset($obj);
					unset($files);
					ibk_debug("Copy File: Get File From OneDrive FINISH");
					return $file;
				}
			} else {
				ibk_debug("Copy File: Get File From OneDrive - ERROR (no destination id or source file)");
				return FALSE;
			}
			
		}
		
		public function get_file_from_copydotcom($destination_id, $source_file){
			/*
			 * @param int, string
			 * @return string (name of file)
			 */
			ibk_debug("Copy File: Get File From Copy.com START");
			if (!$this->general_metas['ibk_backup_dir']){
				ibk_debug("Copy File: Get File From Copy.com - ERROR (no backup directory set)", 1);
				return '';
			}
			if ($destination_id && $source_file){
				require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
				$obj = new IndeedCopyDotCom($destination_id);
				$obj->login();
				$files = $obj->get_all_files();
				foreach ($files as $file){
					if (basename($file)==$source_file){
						$source_file = $file;
						break;
					}
				}
			
				$zip = WP_CONTENT_DIR . '/uploads/'. $this->general_metas['ibk_backup_dir'] . '/';
				$file = $obj->download_file($source_file, $zip . basename($source_file));
				if ($file){
					unset($obj);
					unset($files);
					ibk_debug("Copy File: Get File From Copy.com FINISH");
					return $file;
				}
			} else {
				ibk_debug("Copy File: Get File From Copy.com - ERROR (no destination id or source file)");
				return '';
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
		
	}//end of class
}//end of if
