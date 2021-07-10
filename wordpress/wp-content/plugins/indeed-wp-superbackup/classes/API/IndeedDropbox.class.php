<?php 
if (!class_exists('IndeedDropbox')){
	class IndeedDropbox{
		private $key = 'v4afpyysjv0onbn'; 
		private $secret = 'ie7wj3w9uvs1l53';
		private $destination_id = FALSE;
		private $request_tokens = FALSE;
		private $access_tokens = FALSE;		
		private $dropbox = FALSE;
		private $meta_arr;
		
		public function __construct($destination_id=FALSE, $key=FALSE, $secret=FALSE){
			/*
			 * include the libs that we need for dropbox
			 * set the destination id
			 */
			if ($destination_id){
				$this->destination_id = $destination_id;
				if (!function_exists('ibk_return_metas_from_custom_db')){
					require_once IBK_PATH . 'utilities.php';
				}
				if ($this->destination_id){
					$this->meta_arr = ibk_return_metas_from_custom_db('destinations', $this->destination_id);
				}	
			}
			if ($key && $secret){
				$this->key = $key;
				$this->secret = $secret;
			}
			$this->include_libs();
		}
		
		public function dropbox_auth($redirect_url){
			/*
			 * dropbox authentification
			 * we assume that if we here the $_REQUEST['oauth_token'] is set
			 * @param redirect url
			 * @return none
			 */
			$oauth = new Dropbox_OAuth_PEAR($this->key, $this->secret);
			$tokens['token'] = $_REQUEST['oauth_token'];
			$tokens['token_secret'] = $this->get_request_tokens($tokens['token']);//getting the token secret and destination id
			$oauth->setToken($tokens);//set the request token
			$access_tokens = $oauth->getAccessToken();//getting the access tokens
			$this->save_access_tokens($access_tokens);//store the access tokens
			wp_redirect($redirect_url);//go where you want
			edit();
		}
		
		private function include_libs(){
			/*
			 * include the dropbox libraries and PEAR
			 * @param none
			 * @return none
			 */
			require_once IBK_PATH . 'classes/API/Dropbox/includes/Dropbox/autoload.php';
			set_include_path(IBK_PATH. 'classes/API/Dropbox/includes/PEAR_Includes' . PATH_SEPARATOR . get_include_path());
		}
		
		public function get_authentification_link(){
			/*
			 * get authentification link to dropbox
			 * @param none
			 * @return none
			 */
			$oauth = new Dropbox_OAuth_PEAR($this->key, $this->secret);
			$request_tokens = $oauth->getRequestToken();
			$this->save_request_tokens($request_tokens);
			$admin_url = admin_url() . 'admin.php?page=ibk_admin&tab=destinations';
			$dropbox_url = $oauth->getAuthorizeUrl($admin_url);
			return $dropbox_url;	
		}
		
		private function save_request_tokens($tokens){
			if ($this->destination_id){
				global $wpdb;
				$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
				
				//delete the request tokens for this destination id in case there exists ( edit )
				$wpdb->query('DELETE FROM ' . $table_name . ' WHERE meta_name = "request_token" AND destination_id="'.$this->destination_id.'";');
				$wpdb->query('DELETE FROM ' . $table_name . ' WHERE meta_name = "request_token_secret" AND destination_id="'.$this->destination_id.'";');
				
				$wpdb->query('INSERT INTO ' . $table_name . ' VALUES(null, '.$this->destination_id.', "request_token", "'.$tokens['token'].'");');
				$wpdb->query('INSERT INTO ' . $table_name . ' VALUES(null, '.$this->destination_id.', "request_token_secret", "'.$tokens['token_secret'].'");');
			}
		}
		
		private function save_access_tokens($tokens){
			if ($this->destination_id){
				global $wpdb;
				$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
				
				//delete the request tokens for this destination id in case there exists ( reauthentification )
				$wpdb->query('DELETE FROM ' . $table_name . ' WHERE meta_name = "access_token" AND destination_id="'.$this->destination_id.'";');
				$wpdb->query('DELETE FROM ' . $table_name . ' WHERE meta_name = "access_token_secret" AND destination_id="'.$this->destination_id.'";');
				
				$wpdb->query('INSERT INTO ' . $table_name . ' VALUES(null, '.$this->destination_id.', "access_token", "'.$tokens['token'].'");');
				$wpdb->query('INSERT INTO ' . $table_name . ' VALUES(null, '.$this->destination_id.', "access_token_secret", "'.$tokens['token_secret'].'");');
			}			
		}
		
		private function get_request_tokens($request_token_access){
			/*
			 * set the destinations id and then return the token_secret
			 * @param  $_REQUEST['oauth_token'] aka request token access
			 */
			global $wpdb;
			$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
			$data = $wpdb->get_row('SELECT destination_id FROM ' . $table_name . ' WHERE meta_name="request_token" AND meta_value="'.$request_token_access.'";');
			if (!empty($data->destination_id)) $this->destination_id = $data->destination_id;
			$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="request_token_secret" AND destination_id="' . $this->destination_id . '";');
			if (!empty($data->meta_value)) {
				return $data->meta_value;//the request access token secret
			}
		}
		
		public function get_access_tokens(){
			$tokens = FALSE;
			if ($this->destination_id){
				global $wpdb;
				$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
				$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="access_token" AND destination_id='.$this->destination_id);
				if (!empty($data->meta_value)) $tokens['token'] = $data->meta_value;
				$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="access_token_secret" AND destination_id='.$this->destination_id);
				if (!empty($data->meta_value)) $tokens['token_secret'] = $data->meta_value;
			}
			return $tokens;
		}		
		
		public function login(){
			$oauth = new Dropbox_OAuth_PEAR($this->key, $this->secret);
			
			$tokens = $this->get_access_tokens();
			if($tokens){
				$oauth->setToken($tokens);
				$this->dropbox = new Dropbox_API($oauth, Dropbox_API::ROOT_SANDBOX );			
			}
		}
		
		public function send_file($filename, $filename_on_dropbox){
			/*
			 * send file to dropbox
			 * @param:
			 * $filename - full path of file that must be send
			 * $filename_on_dropbox - full path of file that will be stored in dropbox
			 */
			try {
				if (!empty($this->meta_arr['path'])){
					if (strpos($this->meta_arr['path'] , '/')!=-1){
						$this->meta_arr['path'] .= '/';
					}
					$filename_on_dropbox = $this->meta_arr['path'] . $filename_on_dropbox;
				}
				$result = $this->dropbox->putFile( $filename_on_dropbox, $filename);
				return $result;
			} catch (Exception $e){
				
			}
		}
		
		public function get_files(){
			/*
			 * @param none
			 * @return array with all files
			 */
			if ($this->dropbox){
				
				$data = $this->dropbox->delta('');
				if (!empty($data['entries'])){
					foreach ($data['entries'] as $k=>$v){
						if (!$v[1]['is_dir'] ){
							if (!empty($this->meta_arr['path'])){
								if (strpos($v[0], $this->meta_arr['path'])!==FALSE){
									$arr[] = $v[0];
								}
							} else {
								$arr[] = $v[0];
							}							
						}						
					}
					return $arr;					
				}
			}
			return FALSE;
		}
		
		public function delete_file($file_path=FALSE){
			/*
			 * delete file
			 * @param path of file
			 * @return json if succeed 
			 */
			if ($this->dropbox && $file_path){
				return $this->dropbox->delete($file_path, 'auto');				
			}
			return FALSE;		
		}
		
		public function get_file($source_file, $target_file_path){
			/*
			 * @param source file, target path where to copy
			 * @return true if ok
			 */
			if ($this->dropbox && $source_file && $target_file_path){
				$name_arr = explode('/', $source_file);
				end($name_arr);
				$target  = $target_file_path . '/'.current($name_arr);
				$data = $this->dropbox->getFile($source_file, 'auto');
				file_put_contents($target, $data );
				return $target;
			}
			return FALSE;
		}
		
		public function get_logs_files(){
			/*
			 * search for all log files, and return them into array
			 * @param none
			 */
			$return_arr = FALSE;
			$data = $this->get_files();
			if ($data){	
				foreach ($data as $file){
					if (preg_match("#superbackup(.*)$#i", $file)){
						$is_log = explode('.', basename($file) );
						if (isset($is_log[1]) && $is_log[1]=='log'){
							$return_arr[] = $file;
						}
					}
				}
			}
			return $return_arr;			
		}
		
		public function get_url_for_file($path=''){
			/*
			 * @param string
			 * @return string
			 */
			if ($this->dropbox && $path){
				$data = $this->dropbox->media($path, 'auto');
				if (!empty($data['url'])){
					return $data['url'];
				}
			}
			return '';
		}
			
		
	}//end of class
}
