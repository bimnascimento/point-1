<?php 
if (!class_exists('IndeedDoCheckDestination')):

class IndeedDoCheckDestination{
	private $service_type;
	private $file;
	private $file_name = 'ibk_test_file.txt';
	private $destination_id = 0;
	private $destination_metas = array();
	
	function __construct($destination_id=0){
		/*
		 * @param int
		 * @return none
		 */
		if ($destination_id){
			$this->destination_metas = ibk_return_metas_from_custom_db('destinations', $destination_id);
			$this->service_type = $this->destination_metas['type'];
			$this->destination_id = $destination_id;
		}
	}
	
	public function check(){
		/*
		 * @param none
		 * @return bool
		 */
		if ($this->service_type){
			$created_file = $this->create_file();
			if (empty($created_file)){
				return FALSE;
			}
			
			switch ($this->service_type){
				case 'google':
						require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
						$object = new IndeedGoogle($this->destination_id);
						$object->login();
						$response = $object->send_file($this->file);//send
						unset($object);
						$object = new IndeedGoogle($this->destination_id);
						$object->login();
						if ($response){
							$this->delete_file();// delete from local
							$file_id = $object->get_file_id_by_name($this->file_name);
							if ($file_id){
								$object->deleteFile($file_id);//delete from remote
								return TRUE;
							}
						}
						return FALSE;
					break;
				case 'ftp':
						require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
						$object = new IndeedFtp($this->destination_id);
						$object->login();
						$object->send_file($this->file);
						$this->delete_file();
						$delete = $object->delete_file($this->file_name);
						if ($delete){
							return TRUE;
						}
						return FALSE;
					break;
				case 'local':					
					$this->delete_file();
					$to = $this->destination_metas['local_folder_target'];
					if (substr($to, -1)!='/'){
						$to .= '/';
					}
					if (ibk_check_dir_if_writable($to)){
						return TRUE;
					}
					return FALSE;
					break;
				case 'amazon':
						require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
						$object = new IndeedAmazonS3($this->destination_id);
						$send = $object->send_file($this->file);
						if ($send){
							$object->delete_file($this->file_name);
							return TRUE;
						}
						return FALSE;
					break;
				case 'dropbox':
						require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
						$object = new IndeedDropbox($this->destination_id);
						$object->login();
						$object->send_file($this->file, basename($this->file));
						$this->delete_file();
						$delete = $object->delete_file($this->file_name);
						if ($delete){
							return TRUE;
						}
						return FALSE;
					break;
				case 'onedrive':
						require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
						$object = new IndeedOneDrive($this->destination_id);
						$send = $object->send_file($this->file, basename($this->file));
						if ($send){
							$object->delete_file($this->file_name);
							return TRUE;
						}
						return FALSE;
					break;
			}
		}
	}
	
	public function create_file(){
		/*
		 * @param none
		 * @return none
		 */
		$data = get_option('ibk_general_metas');
		$dir = $data['ibk_backup_dir'];
		$this->file = WP_CONTENT_DIR . '/uploads/' . $dir . '/' . $this->file_name;
		@file_put_contents($this->file, 'Hello World');
		if (file_exists($this->file)){
			return TRUE;
		}
		return FALSE;
	}
	
	public function delete_file(){
		/*
		 * @param none
		 * @return none
		 */
		if ($this->file && file_exists($this->file)){
			@unlink($this->file);
		}
	}
	
}

endif;