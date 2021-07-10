<?php 
if (!class_exists('IndeedLocal')){
	class IndeedLocal{
		private $destination_id = -1;
		private $destination_metas = array();
		
		function __construct($destination_id){
			$this->destination_id = $destination_id;
			$this->destination_metas = ibk_return_metas_from_custom_db('destinations', $this->destination_id);
			if (substr($this->destination_metas['local_folder_target'], -1, 1)!='/'){
				$this->destination_metas['local_folder_target'] .= '/';
			}
		}
		
		public function get_log_files(){
			/*
			 * search for all log files, and return them into array
			 * @param none
			 */
			$return_arr = array(); 
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->destination_metas['local_folder_target']), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $file){
				$filename = basename($file);
				if (preg_match("#^superbackup(.*)$#i", $filename)){
					$is_log = explode('.', $filename);					
					if (isset($is_log[1]) && $is_log[1]=='log'){
						$return_arr[$filename] = $this->destination_metas['local_folder_target'] . $filename;
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
			$file_path = '';
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->destination_metas['local_folder_target']), RecursiveIteratorIterator::SELF_FIRST);
			foreach ($files as $file){
				$filename = basename($file);
				if (preg_match("#^superbackup(.*)$#i", $filename)){
					$is_log = explode('.', $filename);
					if (isset($is_log[1]) && $is_log[1]=='log'){
						$file_name_data = explode('_', $is_log[0]);
						if ($file_name_data[1]==$snapshot_id){
							$file_path = $this->destination_metas['local_folder_target'] . $filename;
						}
					}
				}
			}
			return $file_path;
		}
		
		public function get_file($file_name, $target_file){
			/*
			 * @param file name (string), target file (string)
			 * @return boolean
			 */
			if ($file_name && $target_file){
				$file = $this->destination_metas['local_folder_target'] . $file_name;
				$value = copy($file, $target_file);
				return $value;				
			}
			return FALSE;
		}
		
		public function send_file($source, $file_name){
			/*
			 * @param source - full path to file (string), file name (string)
			 * @return boolean, TRUE IF OK
			 */
			if ($source && $file_name){
				$new_file = $this->destination_metas['local_folder_target'] . $file_name;
				$value = rename($source, $new_file );
				return $value;				
			}
			return FALSE;
		}
		
	}
}