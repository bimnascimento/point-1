<?php 
if (!class_exists('IndeedCopyDotCom')){
	class IndeedCopyDotCom{
		private $consumer_key = 'tCnOWx19ttXE5mPhe7sMcbQ5OmT8Ap7u';
		private $consumer_secret = 'AMAxSbykmeT3DWaCW9x5UMHQrZQXD7un1KFnUQHb3TydnY76';
		private $oauth_token = '';//FROM DB
		private $oauth_token_secret = '';//FROM DB
		private $path = '';//FROM DB
		private $destination_id = -1;
		private $client;//main object
		
		public function __construct($destination_id){
			/*
			 * @param int
			 * @return none
			 */
			require_once IBK_PATH . 'classes/API/CopyDotCom/API.php';
			$this->destination_id = $destination_id;
		}
		
		public function generate_auth_link(){
			/*
			 * STEP 1.
			 * @param none
			 * @return string
			 */
			$callback_url = IBK_URL . 'admin/copydotcom_landing_page.php';
			$q_string = 'oauth/request?scope={"profile":{"read":true},"filesystem":{"read":true,"write":true}}&oauth_callback=' . urlencode($callback_url);
			$consumer_object = new \Eher\OAuth\Consumer($this->consumer_key, $this->consumer_secret);
			$signature = new \Eher\OAuth\HmacSha1();
			$token_object = new \Eher\OAuth\Token($this->oauth_token, $this->oauth_token_secret);
			$request = \Eher\OAuth\Request::from_consumer_and_token(
																		$consumer_object,
																		$token_object,
																		'GET',
																		'https://api.copy.com/' . $q_string
																	);
			$request->sign_request($signature, $consumer_object, $token_object);
			$url = $request->to_url();
			$headers = array( 'X-Api-Version' => 1, 'Accept' => 'application/json' );			
			$data = wp_remote_get($url, array('timeout' => 15, 'headers' => $headers));
			$return_data = $data['body'];
			parse_str($return_data, $token_data);
			if (!empty($token_data['oauth_token_secret']) && !empty($token_data['oauth_token'])){
				$this->set_oauth_tokens('', $token_data['oauth_token_secret']);//SAVE secret token
				return 'https://www.copy.com/applications/authorize?oauth_token=' . urlencode($token_data['oauth_token']);				
			}
			return '';
		}
		
		public function auth(){
			/*
			 * STEP 2.
			 * @param none
			 * @return none
			 */
			$this->get_tokens_from_db();		
			$this->oauth_token = $_REQUEST['oauth_token'];//set the oauth_toekn
			$consumer_object = new \Eher\OAuth\Consumer($this->consumer_key, $this->consumer_secret);			
			$signature = new \Eher\OAuth\HmacSha1();			
			$token_object = new \Eher\OAuth\Token($this->oauth_token, $this->oauth_token_secret);
			$request = \Eher\OAuth\Request::from_consumer_and_token(
					$consumer_object,
					$token_object,
					'GET',
					'https://api.copy.com/oauth/access',
					array('oauth_verifier' => $_REQUEST['oauth_verifier'])
			);
			$request->sign_request($signature, $consumer_object, $token_object);
			$url = $request->to_url();
			$headers_arr = array('X-Api-Version' => 1, 'Accept' => 'application/json');
			$data = wp_remote_get( $url, array(
											'timeout' => 15,
											'headers' => $headers_arr
											)
			);
			parse_str($data['body'], $tokens_data);
			if (!empty($tokens_data['oauth_token']) && !empty($tokens_data['oauth_token_secret'])){
				$this->set_oauth_tokens($tokens_data['oauth_token'], $tokens_data['oauth_token_secret']);
				$this->set_connected_meta(1);
			}
		}
		
		public function login(){
			/*
			 * @param none
			 * @return none
			 */
			$this->get_tokens_from_db();
			if (!empty($this->consumer_key) && !empty($this->consumer_secret) && !empty($this->oauth_token) && !empty($this->oauth_token_secret)){
				$this->client = new Barracuda\Copy\API($this->consumer_key, $this->consumer_secret, $this->oauth_token, $this->oauth_token_secret);
			}			
		}
		
		public function upload_file($source='', $target=''){
			/*
			 * @param string, string
			 * @return string
			 */
			if ($this->client){
				$file_handler = fopen($source, 'rb');
				$chunk_size = 1024*1024*4;//1024 * 1024;//1MB chunks
				$parts = array();
				while ($data = fread($file_handler, $chunk_size)) {
					$part = $this->client->sendData($data);
					array_push($parts, $part);
				}
				fclose($file_handler);
				
				if (!empty($this->path)){
					if (substr($this->path, -1, 1)!='/'){
						$this->path .= '/';
					}	
					$target = $this->path . $target;				
				}
				if (substr($target, 0, 1)!='/'){
					$target = '/' . $target;
				}
				$this->client->createFile($target, $parts);
				return $target;
			}			
			return '';
		}
		
		public function delete_file($target_file=''){
			/*
			 * @param string
			 * @return none
			 */
			if ($this->client && $target_file){
				if (!empty($this->path)){
					if (substr($this->path, -1, 1)!='/'){
						$this->path .= '/';
					}
					$target_file = $this->path . $target_file;
				}
				if (substr($target_file, 0, 1)!='/'){
					//add /
					$target_file = '/' . $target_file;
				}
				$this->client->removeFile($target_file);
			}			
		}

		public function download_file($source_file = '', $target_file = ''){
			/*
			 * @param string, string
			 * @return string
			 */
			if ($this->client){
				if (!empty($this->path)){
					$path = $this->path;
					if (substr($path, -1, 1)!='/'){
						$path .= '/';
					}
					if (substr($path, 0, 1)!='/'){
						//add /
						$path = '/' . $path;
					}
				} else {
					$path = '/';
				}
				$files = $this->client->listPath($path, array("include_parts" => true));
				foreach ($files as $file) {
					if (strpos($file->path, $source_file)!==FALSE){
						$fh = fopen($target_file, 'a');
						foreach ($file->revisions[0]->parts as $part) {
							$chunk = $this->client->getPart($part->fingerprint, $part->size);
							fwrite($fh, $chunk);
						}
						fclose($fh);
						return $target_file;
					}
				}				
			}
			return '';			
		}
		
		public function get_download_link($target_file = ''){
			/*
			 * @param string
			 * @return string
			 */
			if ($this->client && $target_file){
				if (!empty($this->path)){
					$path = $this->path;
					if (substr($path, -1, 1)!='/'){
						$path .= '/';
					}
					if (substr($path, 0, 1)!='/'){
						//add /
						$path = '/' . $path;
					}
				} else {
					$path = '/';
				}
				$files = $this->client->listPath($path);
				foreach ($files as $file){
					if (strpos($file->path, $target_file)!==FALSE){
						if (isset($file->revisions) && isset($file->revisions[0]) && isset($file->revisions->download_url)){
							return $file->revisions[0]->download_url;
						} else {
							return $file->download_url;
						}
					}
				}
				
			}
			return '';			
		}
		
		public function get_all_files(){
			/*
			 * @param none
			 * @return array
			 */
			$arr = array();
			if ($this->client){
				if (!empty($this->path)){
					$path = $this->path;
					if (substr($path, -1, 1)!='/'){
						$path .= '/';
					}
					if (substr($path, 0, 1)!='/'){
						//add /
						$path = '/' . $path;
					}
				} else {
					$path = '/';
				}
				$items = $this->client->listPath($path);
				if ($items && is_array($items)){
					foreach ($items as $item){
						$arr[] = basename($item->path);
					}
				}			
			}
			return $arr;
		}

		public function get_tokens_from_db(){
			/*
			 * @param none
			 * @return none
			 */
			if ($this->destination_id){
				global $wpdb;
				$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
				$arr = array('oauth_token', 'oauth_token_secret', 'path');
				foreach ($arr as $value){
					$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="' . $value . '" AND destination_id='.$this->destination_id);
					if (!empty($data->meta_value)){
						$this->$value = $data->meta_value;
					}					
				}
			}
		}
		
		public function save_update_metas(){
			/*
			 * @param none
			 * @return none
			 */
			if ($this->destination_id){
				$object_data = array('oauth_token' => $this->oauth_token, 'oauth_token_secret' => $this->oauth_token_secret);
				global $wpdb;
				$meta_table_name = $wpdb->base_prefix . "indeed_destination_metas";
				
				foreach ($object_data as $meta_name=>$meta_value){
					if (!empty($meta_value)){
						$data = $wpdb->get_row("SELECT meta_value FROM " . $meta_table_name . " WHERE destination_id='" . $this->destination_id . "' AND meta_name='" . $meta_name . "' ;");
						if (!empty($data) && isset($data->meta_value)){
							//UPDATE
							$wpdb->query("UPDATE " . $meta_table_name . " SET meta_value='" . $meta_value . "' WHERE destination_id='" . $this->destination_id . "' AND meta_name='" . $meta_name . "';");
						} else {
							//SAVE
							$wpdb->query("INSERT INTO " . $meta_table_name . " VALUES(null, '" . $this->destination_id . "', '" . $meta_name . "', '" . $meta_value . "');");
						}
					}
				}
			}
		}
		
		public function set_oauth_tokens($oauth_token='', $oauth_token_secret=''){
			/*
			 * @param string, string
			 * @return none
			 */
			if (!empty($oauth_token)){
				$this->oauth_token = $oauth_token;
			}
			if (!empty($oauth_token_secret)){
				$this->oauth_token_secret = $oauth_token_secret;
			}
			//save them into DB
			$this->save_update_metas();			
		}
		
		private function set_connected_meta($value=0){
			/*
			 * @param int ( 1 || 0)
			 * @return none
			 */
			global $wpdb;
			$meta_table_name = $wpdb->base_prefix . "indeed_destination_metas";
			$data = $wpdb->get_row("SELECT meta_value FROM " . $meta_table_name . " WHERE destination_id='" . $this->destination_id . "' AND meta_name='connected' ;");
			if ($data){
				//UPDATE
				$wpdb->query("UPDATE " . $meta_table_name . " SET meta_value='" . $value . "' WHERE destination_id='" . $this->destination_id . "' AND meta_name='connected';");
			} else {
				//SAVE
				$wpdb->query("INSERT INTO " . $meta_table_name . " VALUES(null, '" . $this->destination_id . "', 'connected', '" . $value . "');");
			}
		}
		
		public function get_logs_files(){
			/*
			 * @param none
			 * @return array
			 */
			$return_arr = array();
			if ($this->client){
				$data = $this->get_all_files();
				if (!empty($data)){
					foreach ($data as $file){
						if (preg_match("#superbackup(.*)$#i", $file)){
							$is_log = explode('.', basename($file) );
							if (isset($is_log[1]) && $is_log[1]=='log'){
								$return_arr[] = $file;
							}
						}
					}
				}
			}
			return $return_arr;
		}

	}
}