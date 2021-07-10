<?php

	#MARKER PACK PROCESSOR
	class hmapsprem_map_importer{
		
		#CLASS VARS
		private $plugin_dir;
		private $map_import_dir = '/_map_import_uploads/';
		
		#CONSTRUCT
		public function __construct($plugin_dir){
			//set class vars
			$this->plugin_dir = $plugin_dir;
		}
		
		#PROCESS CUSTOM MARKERS
		public function process_map_import(){
			//check directory for text files
			$file_mimes = array(
				'text/plain'
			);
			//loop through directory and check files
			if($handle = opendir($this->plugin_dir . $this->map_import_dir)){
				while(false !== ($file = readdir($handle))){
					if('.' === $file) continue;
					if('..' === $file) continue;
					$path_to_file = $this->plugin_dir . $this->map_import_dir . $file;
					//check if text file
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mimetype = finfo_file($finfo, $path_to_file);
					finfo_close($finfo);
					if(in_array($mimetype, $file_mimes)){ //txt
						//extract data
						$map_import_arr = html_entity_decode(file_get_contents($path_to_file));
						$map_import_arr = json_decode($map_import_arr, true);
						$map_name = $map_import_arr['object_name'] .' (import)';
						$markers_object = $map_import_arr['markers_object'];
						//access globals
						global $wpdb;
						//create new category
						$wpdb->query("
							INSERT INTO `". $wpdb->base_prefix ."hmapsprem_marker_categories` (`name`,`imported`)
							VALUES ('". $map_import_arr['object_name'] ."', 1);
						");
						$category_id = $wpdb->insert_id;
						//insert markers into new category
						$marker_id_lookup_arr = array();
						foreach($markers_object as $key=>$val){
							$wpdb->query("
								INSERT INTO `". $wpdb->base_prefix ."hmapsprem_markers` (`category_id`,`img_binary`,`width`,`height`,`left_offset`,`top_offset`,`link`,`primary_colour`,`secondary_colour`)
								VALUES (". $category_id .",'". addslashes(base64_decode($val['img_binary'])) ."',". intval($val['width']) .",". intval($val['height']) .",". intval($val['left_offset']) .",". intval($val['top_offset']) .",". intval($val['link']) .",'". $val['primary_colour'] ."','". $val['secondary_colour'] ."');
							");
							//get category id
							$marker_id = $wpdb->insert_id;
							//append new id to object
							array_push($marker_id_lookup_arr, array(
								'marker_id' => $val['marker_id'],
								'new_marker_id' => $marker_id
							));
						}
						//update marker id for markers
						foreach($map_import_arr['object']['map_markers'] as $key=>$val){
							$marker_id = $val['marker_id'];
							foreach($marker_id_lookup_arr as $k=>$v){
								if($v['marker_id'] == $marker_id){
									$map_import_arr['object']['map_markers'][$key]['marker_id'] = $v['new_marker_id'];								
									break;
								}
							}
						}
						//encode map_object
						//$map_object = str_replace('\\\\','&#x5c;', str_replace('\n', '<br>', json_encode($map_import_arr['object'])));
						$map_object = $map_import_arr['object'];
						foreach($map_object['map_markers'] as $key=>$marker){
							$map_object['map_markers'][$key]['info_window_content'] = str_replace('"','\"', str_replace('\\', '', $marker['info_window_content']));
						}


						//create map
						$wpdb->query("
							INSERT INTO `". $wpdb->base_prefix ."hmapsprem_default_storage_table` (`object_name`,`json_object`)
							VALUES ('". $map_name ."', '". json_encode($map_object, JSON_UNESCAPED_UNICODE) ."');
						");
						//get category id
						$storage_id = $wpdb->insert_id;				
						//remove file
						unlink($path_to_file);
					}
					
				}
				closedir($handle);
			}
			echo json_encode(true);
			exit();
		}

		#STRING CLEANER
		private function replace_content_inside_delimiters($start, $end, $new, $source){
			return preg_replace('#('.preg_quote($start).')(.*?)('.preg_quote($end).')#si', '$1'.$new.'$3', $source);
		}

	}