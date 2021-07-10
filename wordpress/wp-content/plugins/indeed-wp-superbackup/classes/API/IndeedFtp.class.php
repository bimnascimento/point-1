<?php 
if (!class_exists('IndeedFtp')){
	class IndeedFtp{
		private $metas = false;
		private $destination_id = false;
		private $connection;
		private $protocol;
		private $errors = FALSE;
		
		public function __construct($id){
			$this->destination_id = $id;
			if (!function_exists('ibk_return_metas_from_custom_db')){
				require_once IBK_PATH . 'utilities.php';
			}
			if ($this->destination_id){
				$this->metas = ibk_return_metas_from_custom_db('destinations', $this->destination_id);
			}			
			if (!empty($this->connection)) unset($this->connection);
			if (!$this->metas) {
				$this->errors[] = 'Could not get the metas for this ID';
			}
		}
		
		public function get_metas(){
			return $this->metas;
		}
		
		public function login(){
			if ($this->metas['protocol']=='ftp'){
				/************************** FTP ***************************/
				if (empty($this->metas['server_port'])) $this->metas['server_port'] = 21;
				$this->connection = @ftp_connect( $this->metas['server_address'], (int)$this->metas['server_port'] );
				
				if (is_resource($this->connection)){
					$login = @ftp_login( $this->connection, $this->metas['username'], $this->metas['password'] );
					if ($login){
						if ($this->metas['passive_mode'] == 1) {
							ftp_pasv($this->connection, true);							
						} else {
							ftp_pasv($this->connection, false);
						}						
						//if (empty($this->metas['server_timeout'])) $this->metas['server_timeout'] = 90;
						//@ftp_set_option($this->connection, FTP_TIMEOUT_SEC, $this->metas['server_timeout']);
						return TRUE;
					} else {
						$this->errors[] = "Login Failed! Wrong username or password.";
						return FALSE;
					}
				} else {
					$this->errors[] = "Login Failed! Wrong server address or server port.";
					return FALSE;
				}
			} elseif ($this->metas['protocol']=='sftp'){
				/************************** sFTP ***************************/
				require_once IBK_PATH . 'classes/API/phpseclib/NET/SFTP.php';
				if (empty($this->metas['server_port'])) $this->metas['server_port'] = 22;
				if (empty($this->metas['server_timeout'])) $this->metas['server_timeout'] = 90;
				$this->connection = new Net_SFTP($this->metas['server_address'], $this->metas['server_port'], $this->metas['server_timeout']);
				if ($this->connection->login($this->metas['username'], $this->metas['password'])) {
					return TRUE;
				} else {
					$this->errors[] = "Login Failed! Wrong username or password.";
					return FALSE;
				}
			}
		}
		
		public function send_file($filename) {
			try{
				$this->set_target_directory();
				
				if ($this->metas['protocol'] == "sftp") {
					//sFTP
					$this->connection->put(basename($filename), $filename, NET_SFTP_LOCAL_FILE);
				} else {
					//FTP
					ftp_put($this->connection, basename($filename), $filename, FTP_BINARY);
				}			
			} catch (Exception $e){
				$this->errors[] = 'Erorr at sending file to server.' . $e->getMessage();	
			}

			if ($this->errors){
				return $this->errors;	
			} else {
				return TRUE;
			}
		}	
		
		public function delete_file($target_file) {
			/*
			 * @param target_file full path
			 * @return none
			 */
			if (ftp_delete($this->connection, $target_file)) {
				return true;
			} else {
				return false;
			}
		}		

		private function set_target_directory() {
			if ($this->metas['directory']){
				try{
					if ($this->metas['protocol'] == "sftp"){
						/**************** sFTP *****************/
					} else {
						/**************** FTP ******************/
						if (@ftp_chdir($this->connection, $this->metas['directory'])){//check if exists, and set it if exists
							@ftp_chdir($this->connection, $this->metas['directory']);//change current directory
						} else {
							//create current path and set the target
							$dir_arr = explode('/', $this->metas['directory']);
							if ($dir_arr && count($dir_arr)){
								$path = '';
								if ($this->metas['directory'][0] == "/"){
									$path = '/';
								} else {
									$path = ftp_pwd($this->connection);
								}
								foreach ($dir_arr as $dir) {
									$path .= $dir;
									if (!@ftp_chdir($this->connection, $path)) {
										if (ftp_mkdir($this->connection, $path)) {
											@ftp_chdir($this->connection, $path);
										}
									}
									if ($path != "/"){
										$path .= "/";
									}
								}
							}
						}
					}//end of ftp					
				} catch (Exception $e){
					$this->errors[] = 'Error at setting the target directory. ' . $e->getMessage();
				}			
			}
		}//end of set_target_directory()
		
		private function get_remote_directory() {
			if ($this->connection['protocol'] == "sftp") {
				return $this->connection->pwd();
			} else {
				return ftp_pwd($this->connection);
			}
		}
		
		public function logout() {
			if ($this->metas['protocol'] == "sftp") {
				unset($this->connection);
			} else {
				ftp_close($this->connection);
			}
		}
		
		public function list_snapshots($snapshot_id){
			/*
			 * return array with all snapshots for a given snapshot id
			 */
			$return_arr = FALSE;
			if ($this->connection){
				
				if (substr($this->metas['directory'], -1, 1)!='/'){
					$this->metas['directory'] .= '/';
				}
				
				$files = ftp_nlist($this->connection, $this->metas['directory'] );				
				foreach ($files as $file){
					if (preg_match("#^superbackup(.*)$#i", $file)){						
						//it contains indeed
						$is_zip_data = explode('.', $file);
						if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){							
							//it's a zip file
							$file_name_data = explode('_', $is_zip_data[0]);
							if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed')){
								//it's a instance of our snapshot
								$return_arr[$file_name_data[3]]	= $this->metas['directory'] . $file;
							}
						}
					}
				}
			}
			return $return_arr;		
		}
		
		public function get_log_file($snapshot_id){
			/*
			 * @param id of snapshot
			 * @return file path
			 */
			if ($this->connection){
				$files = ftp_nlist($this->connection, $this->metas['directory'] );	
				
				foreach ($files as $file){
					if (preg_match("#^superbackup(.*)$#i", $file)){
						$is_log = explode('.', $file);
						if (isset($is_log[1]) && $is_log[1]=='log'){
							$file_name_data = explode('_', $is_log[0]);
							if ($file_name_data[1]==$snapshot_id){
								return $this->metas['directory'] . $file;
							}
						}
					}
				}
			}
			return FALSE;
		}
		
		public function get_log_files(){
			/*
			 * search for all log files, and return them into array
			 * @param none
			 */
			$return_arr = FALSE;
			if ($this->connection){
				$files = ftp_nlist($this->connection, $this->metas['directory'] );
				if (substr($this->metas['directory'], -1, 1)!='/'){
					$this->metas['directory'] .= '/';
				}
				if($files){
					foreach ($files as $file){
						if (preg_match("#^superbackup(.*)$#i", $file)){
							$is_log = explode('.', $file);
							if (isset($is_log[1]) && $is_log[1]=='log'){
								$return_arr[$file] = $this->metas['directory'] .  $file;
							}
						}
					}
				}
			}
			return $return_arr;
		}
		
		public function copy_file_to_local($source_file, $target_file_path){
			/*
			 * copy file from ftp to local 
			 * @param source path to file, where to copy
			 */
			if ($this->connection && $source_file){
				if (ftp_size($this->connection, $source_file)){//does the file really exists?
					ftp_get($this->connection, $target_file_path, $source_file, FTP_BINARY);
					return $target_file_path;
				}
			}
			return FALSE;
		}
		
		public function delete_target_file($target_file){
			if (ftp_size($this->connection, $target_file)){
				ftp_delete($this->connection, $target_file);
			}			
		}

	}// end of class IndeedFtp
}//end of if