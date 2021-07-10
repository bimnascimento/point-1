<?php 
if (!class_exists('IndeedAmazonS3')){
	class IndeedAmazonS3{
		private $metas = FALSE;
		private $destination_id = FALSE;
		private $connection = FALSE;
		
		public function __construct($id){
			/*
			 * @param id of destination
			 * @return none
			 */
			$this->destination_id = $id;
			$this->set_metas();	
			
			//load the amazon classes
			if (!class_exists('CFRuntime')){
				require_once IBK_PATH . 'classes/API/AWS/sdk.class.php';
			}
			
			//auth
			$this->login();
		}
		
		private function set_metas(){
			/*
			 * getting the meta values from db for current destination id and set them into $this->metas
			 * @param none
			 * @return none
			 */
			if ($this->destination_id){
				global $wpdb;
				$arr = array();
				$table_name = $wpdb->base_prefix . 'indeed_destination_metas';
				foreach (array('aws_key', 'aws_secret_key', 'aws_region', 'aws_bucket', 'subfolder') as $k){ 
					$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="'.$k.'" AND destination_id='.$this->destination_id);
					if (!empty($data->meta_value)) $this->metas[$k] = $data->meta_value;
				}
				if (!empty($this->metas['subfolder'])){
					if (substr($this->metas['subfolder'], -1, 1)!='/'){
						$this->metas['subfolder'] .= '/';
					}
					if (substr($this->metas['subfolder'], 0, 1)=='/'){
						$this->metas['subfolder'] = substr_replace($this->metas['subfolder'], '', 0, 1);
					}
				}
			}			
		}
		
		private function login(){
			if (!empty($this->metas['aws_key']) && !empty($this->metas['aws_secret_key'])){
				$ssl = (empty($this->metas['aws_ssl'])) ? FALSE : TRUE;//use ssl?
				$this->connection = new AmazonS3(array(
						'key' => $this->metas['aws_key'],
						'secret' =>	$this->metas['aws_secret_key'],
						'certificate_authority'	=>	$ssl,
					)
				);
				$this->connection->set_region($this->metas['aws_region']);				
			}
		}
		
		public function send_file($file){
			/*
			 * @param full path of file that we want to send
			 * @return 
			 */
			$path = '';
			if (!empty($this->metas['subfolder'])){
				$path = $this->metas['subfolder'];
			}
			$result = $this->connection->create_object($this->metas['aws_bucket'], $path . basename($file), array('fileUpload' => $file));
			return $result;
		}
		
		public function get_file($source, $target_path){
			$result = $this->connection->get_object($this->metas['aws_bucket'], $source);
			if (!empty($result->body)){
				file_put_contents($target_path . $source, $result->body );
				return $target_path . $source;
			}
			return FALSE;			
		}
		
		public function get_files_list(){
			if ($this->connection && $this->metas['aws_bucket']){
				$params = array();
				if (!empty($this->metas['subfolder'])){
					$params['prefix'] = $this->metas['subfolder'];
				}
				$data = $this->connection->list_objects($this->metas['aws_bucket'], $params);
				if (!empty($data->body->Contents)){
					foreach ($data->body->Contents as $obj){
						$obj = (array)$obj;
						$arr[] = $obj['Key'];
					}
					return $arr;
				}				
			}
			return FALSE;
		}
		
		public function delete_file($filename){
			$this->connection->delete_object($this->metas['aws_bucket'], $filename);
		}
		
		public function get_logs_files(){
			/*
			 * search for all log files, and return them into array
			 * @param none
			 * @return array or bool
			 */
			$return_arr = FALSE;
			if ($this->connection){
				$data = $this->get_files_list();
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
		
		public function get_url_for_file($file_name=''){
			/*
			 * @param string
			 * @return string 
			 */
			if ($this->connection && $file_name){
				if (substr($file_name, 0, 1)=='/'){
					$file_name = substr_replace($file_name, '', 0, 1);
				}
				$url = $this->connection->get_object_url($this->metas['aws_bucket'], $file_name);
				return $url;
			}
			return '';
		}
		
		public function is_file_available_for_everyone($file_name=''){
			/*
			 * @param string
			 * @return bool
			 */
			if ($this->connection && $file_name){
				if (substr($file_name, 0, 1)=='/'){
					$file_name = substr_replace($file_name, '', 0, 1);
				}
				$data = $this->connection->get_object_acl($this->metas['aws_bucket'], $file_name);
				foreach ($data->body->AccessControlList->Grant as $obj){
					if (isset($obj->Grantee->URI) && $obj->Grantee->URI=='http://acs.amazonaws.com/groups/global/AllUsers' && $obj->Grantee->Permission='READ'){
						return TRUE;
					}
				}
			}
			return FALSE;		
		}
		
		
	}//end of class IndeedAmazonS3
}