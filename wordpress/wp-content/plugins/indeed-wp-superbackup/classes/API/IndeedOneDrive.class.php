<?php 
if (!class_exists('IndeedOneDrive')){
	class IndeedOneDrive{
		private $client;//OneDrive object
		private $state = null;
		private $client_id = '';
		private $client_secret = '';		
		private $destination_id = '';
		private $redirect_uri;
		
		public function __construct($destination_id=-1, $client_id='', $client_secret='', $redirect_uri=''){
			/*
			 * @param int, string, string
			 * @return none
			 */
			require_once IBK_PATH . 'classes/API/OneDrive/Client.php';
			require_once IBK_PATH . 'classes/API/OneDrive/Object.php';
			require_once IBK_PATH . 'classes/API/OneDrive/File.php';
			require_once IBK_PATH . 'classes/API/OneDrive/Folder.php';			
			
			///set redirect URI
			$this->redirect_uri = IBK_URL . 'admin/onedrive_landing_page.php';
			
			if ($destination_id>-1){
				////////// GET METAS FROM DB
				$this->destination_id = $destination_id;
				if (!function_exists('ibk_return_metas_from_custom_db')){
					require_once IBK_PATH . 'utilities.php';
				}
				$meta_arr = ibk_return_metas_from_custom_db('destinations', $this->destination_id);
				if ($meta_arr){
					if (!empty($meta_arr['state'])){
						$this->state = unserialize($meta_arr['state']);
					}
					if (!empty($meta_arr['client_id'])){
						$this->client_id = $meta_arr['client_id'];
					}
					if (!empty($meta_arr['client_secret'])){
						$this->client_secret = $meta_arr['client_secret'];
					}
				}
			} else {
				/////// NEw INSTANCE 
				if (!empty($client_id)){
					$this->client_id = $client_id;
				}
				if (!empty($client_secret)){
					$this->client_secret = $client_secret;
				}
				
			}
			
			if (!empty($this->client_id)){
				///CREATE ONEDRIVE MAIN OBJECT
				$this->client = new Client(array(
						'client_id' => $this->client_id,
						'state' => $this->state
				));				
			}
			
			if ($this->state){
				///check if token it's expired, and refresh if it's case
				$check = $this->client->getAccessTokenStatus();
				if ($check!=1){
					//Refresh Token
					$this->client->renewAccessToken($this->client_secret, $this->redirect_uri);
					$this->state = $this->client->getState();
					/////save state
					$this->save_credentials();
				}				
			}
			
		}
		
		public function generate_auth_link(){
			/*
			 * @param none
			 * @return none
			 */
			return $this->client->getLogInUrl(array(
												'wl.signin',
												'wl.basic',
												'wl.offline_access',
												'wl.skydrive_update',
												'onedrive.readwrite',
			), $this->redirect_uri);
		}
		
		public function set_state($code=''){
			/*
			 * @param int, string ($_GET['code'])
			 * @return none
			 */
			if ($this->client_id && $this->client_secret){
				$this->client->obtainAccessToken($this->client_secret, $code, $this->redirect_uri);
				$this->state = $this->client->getState();
				$this->save_credentials();		
				$this->set_connected_meta(1);
			}
		}
		
		private function save_credentials(){
			/*
			 * @param none
			 * @return none
			 */
			if ($this->destination_id){
				$object_data = array('state'=>serialize($this->state), 'client_id'=>$this->client_id, 'client_secret'=>$this->client_secret);
				global $wpdb;
				$meta_table_name = $wpdb->base_prefix . "indeed_destination_metas";				
				foreach ($object_data as $meta_name=>$meta_value){
					if (!empty($meta_value)){
						$data = $wpdb->get_row("SELECT meta_value FROM " . $meta_table_name . " WHERE destination_id='" . $this->destination_id . "' AND meta_name='" . $meta_name . "';");
						if ($data){
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
		
		public function send_file($source, $target){
			/*
			 * @param string, string
			 * @return bool
			 */
			if ($this->client && $source && $target){
				$this->client->upload_file($source, $target);
				return TRUE;
			}
			return FALSE;
		}
		
		public function return_all_files(){
			/*
			 * @param none
			 * @return array
			 */
			if ($this->client){
				return $this->client->return_all_files();
			}
		}
		
		public function delete_file($name=''){
			/*
			 * @param string
			 * @return none
			 */
			$meta_data = $this->client->get_file_meta_by_name($name);
			if (isset($meta_data['id'])){
				$this->client->deleteObject($meta_data['id']);
			}
		}
		
		public function get_file_by_name($file_name, $destination_path){
			/*
			 * @param string, stirng
			 * @return string
			 */
			$file_meta = $this->client->get_file_meta_by_name($file_name);
			if (!empty($file_meta['id'])){
				return $this->client->download_file($file_meta['id'], $destination_path, $file_meta['size']);
			}
		}
		
		public function get_all_files_with_metas(){
			return $this->client->return_all_files();
		}
		
		public function get_logs_files(){
			/*
			 * @param none
			 * @return array
			 */
			$return_arr = array();
			if ($this->client){
				$data = $this->client->return_all_files();
				if (!empty($data)){
					foreach ($data as $file_data){
						$file = $file_data['name'];
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
		
		public function get_url_for_file($name){
			/*
			 * @param string
			 * @return string
			 */
			return $this->client->generate_download_url($name);
		}
		
	}
}