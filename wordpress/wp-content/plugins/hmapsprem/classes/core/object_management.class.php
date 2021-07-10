<?php

	#PLUGIN AUTO-GENERATION MANAGEMENT
	class hmapsprem_object_management{
		
		#CLASS VARS
		private $plugin_dir;
		
		#CONSTRUCT
		public function __construct($plugin_dir){
			//define class vars
			$this->plugin_dir = $plugin_dir;
		}
		
		#UPDATE 'ACTIVE' OBJECTS IN DB
		public function update_database_objects(){
			//get default object
			if(file_exists(realpath($this->plugin_dir .'/objects') .'/default_object.js')){
				$handle = fopen(realpath($this->plugin_dir .'/objects') .'/default_object.js', 'r');
				$default_object = json_decode(fread($handle,filesize(realpath($this->plugin_dir .'/objects') .'/default_object.js')), true);
				fclose($handle);
				//access globals
				global $wpdb;
				//select active objects
				$objects = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					WHERE
						`deleted` = 0;
				");
				if(count($objects) > 0){
					//iterate results
					foreach($objects as $object){
						//extract object
						$json_string = $object->json_object;
						$json_string = html_entity_decode($json_string);
						$json_object = json_decode($json_string, true);
						foreach($json_object['map_markers'] as $key=>$marker){
							$json_object['map_markers'][$key]['info_window_content'] = str_replace('"','\"', str_replace('\\', '', $marker['info_window_content']));
						}
						//compare object to default object (loop through default object and add missing values to object)
						$new_object = $this->array_merge_recursive_distinct($default_object, $json_object);
						//replace special chars
						$final_object = json_encode($new_object, JSON_UNESCAPED_UNICODE);
						//save updated object
						$sql_update = "
							UPDATE `". $wpdb->base_prefix ."hmapsprem_default_storage_table`
							SET `json_object` = '". $final_object ."'
							WHERE `storage_id` = ". intval($object->storage_id) .";
						";
						$wpdb->query($sql_update);
					}			
				}
				//respond
				if($_SERVER['REQUEST_METHOD'] == 'POST'){
					echo json_encode(true);
					exit();
				}else{
					return true;
				}
			}
			//error
			echo json_encode(false);
			exit();
		}

		#RECURSIVELY MERGE 2 ARRAYS
		private function array_merge_recursive_distinct(array &$array1, array &$array2){
			$merged = $array1;
			foreach($array2 as $key => &$value){
				if($key == 'map_markers' || $key == 'map_poly' || $key == 'map_marker_categories'){ //slight modification to ignore map markers
					if(count($value) == 0){
						$merged[$key] = json_decode("{}");
					}else{
						$merged[$key] = $value;
					}
				}else{
					if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
						$merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
					}else{
						if(isset($array1[$key])){
							$merged[$key] = $value;
						}
					}
				}
			}
			return $merged;
		}
		
	}