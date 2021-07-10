<?php 
if (!class_exists('IndeedGoogle')){
	
	class IndeedGoogle{
		
		private $client = false;
		private $metas = false;
		private $destination_id = false;
		
		public function __construct($id=false){
			$this->destination_id = $id;
			if (!function_exists('ibk_return_metas_from_custom_db')){
				require_once IBK_PATH . 'utilities.php';
			}
			if ($this->destination_id){
				$this->metas = ibk_return_metas_from_custom_db('destinations', $this->destination_id);
				$this->metas['access_token'] = str_replace('&quot;', '"', $this->metas['access_token']);
			}
		}
		
		public function get_metas(){
			//getter
			return $this->metas;
		}
	
		public function set_metas($data){
			//setter
			$this->metas = $data;
		}
		
		private function init(){
			if ($this->metas){
				require_once IBK_PATH . 'classes/API/Google/autoload.php';
				$this->client = new Google_Client();
				$this->client->setAccessType('offline');
				$this->client->setApprovalPrompt('force');
				$this->client->setClientId($this->metas['client_id']);
				$this->client->setClientSecret($this->metas['client_secret']);//
				$this->client->setScopes(array('https://www.googleapis.com/auth/drive'));
				$this->client->setRedirectUri($this->metas['redirect_uri']);
			}
		}	
		
		public function update_metas(){
			//access_token must be use with escape character
			$this->metas['access_token'] = str_replace('"', "&quot;", $this->metas['access_token']);
			if ($this->destination_id){
				global $wpdb;
				$table = $wpdb->base_prefix.'indeed_destination_metas';
				foreach ($this->metas as $k=>$v){
					$wpdb->query('UPDATE '.$table.' SET meta_value=\''.$v.'\' WHERE destination_id=\''.$this->destination_id.'\' AND meta_name=\''.$k.'\';');
				}			
			}
			$this->metas['access_token'] = str_replace('&quot;', '"', $this->metas['access_token']);
		}
			
		public function generate_link(){
			$this->init();
			$authUrl = $this->client->createAuthUrl();
			return $authUrl;
		}
		
		public function authorize(){
			$this->init();
			$this->client->authenticate($_GET['code']);
			$this->metas['access_token'] = $this->client->getAccessToken();
			$this->metas['refresh_token'] = $this->client->getRefreshToken();
			$this->update_metas();
			return $this->client->getAccessToken();
		}	
		
		public function send_file($filename){
			$this->init();
			$chunk_size = 1 * 1024 * 1024;
					
			$this->client->setAccessToken($this->metas['access_token']);
			$this->client->refreshToken($this->metas['refresh_token']);
			if ($this->client->isAccessTokenExpired()) {
				$this->client->refreshToken($this->metas['refresh_token']);
				$this->metas['access_token'] = $this->client->getAccessToken();
				$this->metas['refresh_token'] = $this->client->getRefreshToken();
				$this->update_metas();
			}
			
			
			/*************************************
						SEND FILE 
			*************************************/
			$service = new Google_Service_Drive($this->client);
			$file = new Google_Service_Drive_DriveFile();
			$file->title = basename($filename);
			if (!empty($this->metas['folder_id'])){
				$parent = new Google_Service_Drive_ParentReference();
				$parent->setId($this->metas['folder_id']);
				$file->setParents(array($parent));
			}

			
			$this->client->setDefer(true);
			$request = $service->files->insert($file);
			$media = new Google_Http_MediaFileUpload(
					$this->client,
					$request,
					'application/x-zip',//'text/plain'
					null,
					true,
					$chunk_size
			);
			$file_size = filesize($filename);
			$media->setFileSize( $file_size );
			$handle = fopen($filename, "rb");
			while (!feof($handle)){
			 	$chunk = fread($handle, $chunk_size);
			 	$media->nextChunk($chunk);
			}
			fclose($handle);
			$http_result_code = $media->getHttpResultCode();
			if ($http_result_code == 200) {
				return TRUE;
			} else {
				return FALSE;
			}
		}

		public function login(){
			$this->init();
			if($this->metas['access_token'] && $this->metas['access_token'] != ''){
				$this->client->setAccessToken($this->metas['access_token']);
			}
			if($this->metas['refresh_token'] && $this->metas['refresh_token'] != ''){
				$this->client->refreshToken($this->metas['refresh_token']);
			}
			if ($this->client->isAccessTokenExpired() && $this->metas['refresh_token'] != '') {
				$this->client->refreshToken($this->metas['refresh_token']);
				$this->metas['access_token'] = $this->client->getAccessToken();
				$this->metas['refresh_token'] = $this->client->getRefreshToken();
				$this->update_metas();
			}
		}
		
		public function deleteFile($fileId){
			/*
			 * Permanently delete a file, skipping the trash.
			 * @param String $fileId ID of the file to delete.
			 * @return none
			 */			
			 try {
			 	$service = new Google_Service_Drive($this->client);
			 	$service->files->delete($fileId);
			 } catch (Exception $e) {
			 	print "An error occurred: " . $e->getMessage();
			 }
		}
		
		public function retrieveAllFiles(){
			/*
			 * Retrieve a list of File resources.
			 * @return Array List of Google_Service_Drive_DriveFile resources.
			 */			
			$service = new Google_Service_Drive($this->client);
			$result = array();
			$pageToken = NULL;
			do {
				try {
					$parameters = array();
					if ($pageToken) {
						$parameters['pageToken'] = $pageToken;
					}
					if (!empty($this->metas['folder_id'])){
						$parameters['q'] = "'" . $this->metas['folder_id'] . "' in parents and trashed=false";
					}
					$files = $service->files->listFiles($parameters);		
					@$result = array_merge($result, $files->getItems());
					$pageToken = $files->getNextPageToken();
				} catch (Exception $e) {
					//print "An error occurred: " . $e->getMessage();
					$pageToken = NULL;
				}
			} while ($pageToken);
			return $result;
		}
		
		public function get_log_files(){
			/*
			 * search for all log files, and return them into array
			 * @param none
			 */
			$return_arr = FALSE;
			$data_obj = $this->retrieveAllFiles();
			foreach ($data_obj as $file_obj){
				$file_obj->originalFilename;
				if (preg_match("#^superbackup(.*)$#i", $file_obj->originalFilename)){
					$is_log = explode('.', $file_obj->originalFilename);
					if (isset($is_log[1]) && $is_log[1]=='log'){
						$return_arr[$file_obj->title] = $file_obj->id;
					}
				}
			}
			return $return_arr;
		}
		
		public function downloadFile($fileId, $target_path) {
			/*
			 * Download file
			 * @param File $file Drive File instance, $target_path full path where to store the file 
			 * @return none
			 */	
			$service = new Google_Service_Drive($this->client);
			$file_obj = $service->files->get($fileId);			
			$name = $file_obj->originalFilename;
			$downloadUrl = $file_obj->downloadUrl;
			if ($downloadUrl) {
				$request = new Google_Http_Request($downloadUrl, 'GET', null, null);
				$httpRequest = $service->getClient()->getAuth()->authenticatedRequest($request);
				if ($httpRequest->getResponseHttpCode() == 200) {
					file_put_contents($target_path . $name, $httpRequest->getResponseBody() );
					return $target_path . $name;
				} else {
					// An error occurred.
					return null;
				}
			} else {
				// The file doesn't have any content stored on Drive.
				return null;
			}			

		}
		
		public function get_file_url($fileId){
			/*
			 * @param int 
			 * @return string
			 */
			if ($fileId){
				$service = new Google_Service_Drive($this->client);
				$file_obj = $service->files->get($fileId);	
				$href = $file_obj->webContentLink;	
				if (!empty($href)){
					return $href;
				}		
			}
			return '';
		}
		
		public function get_file_id_by_name($name=''){
			/*
			 * @param string
			 * @return string
			 */
			$return_arr = FALSE;
			$data_obj = $this->retrieveAllFiles();
			foreach ($data_obj as $file_obj){
				if ($name==$file_obj->originalFilename){
					return $file_obj->id;
				}
			}
			return '';
		}

			
	}//end of IndeedGoogle Class

}