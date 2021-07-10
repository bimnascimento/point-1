<?php

	#MARKER PACK PROCESSOR
	class hmapsprem_map_csv_importer{
		
		#CLASS VARS
		private $plugin_dir;
		private $marker_csv_import_dir = '/_marker_csv_import_uploads/';
		private $error_in_processing = false;
		
		#CONSTRUCT
		public function __construct($plugin_dir){
			//set class vars
			$this->plugin_dir = $plugin_dir;
		}
		
		#PROCESS CUSTOM MARKERS
		public function process_marker_csv_import(){
			if(isset($_POST['map_id']) && isset($_POST['file_name'])){
				//get map id
				$map_id = $_POST['map_id'];
				//get file name
				$file_name = $_POST['file_name'];
				//access globals
				global $wpdb;
				global $hmapsprem_helper;
				//fetch map object
				$object = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					WHERE
						`storage_id` = ". $_POST['map_id'] ."
						AND `deleted` = 0;
				");
				//check that object exists
				if(count($object) > 0){
					//set counters
					$row_count = 0;
					$success_count = 0;
					$error_count = 0;
					//create success array
					$success_array = array();
					//get object
					$object = json_decode($object[0]->json_object);
					//get file
					$path_to_file = $this->plugin_dir . $this->marker_csv_import_dir . $file_name;
					//extract csv data
					ini_set('auto_detect_line_endings', true);
					$handle = fopen($path_to_file ,'r');
					//exclude header
					$header_row = true;
					$csv_error_file_name = 'import_errors.csv';
					//delete file if exists
					if(file_exists($this->plugin_dir . $this->marker_csv_import_dir . $csv_error_file_name)){
						unlink($this->plugin_dir . $this->marker_csv_import_dir . $csv_error_file_name);
					}
					while(($csv_data = fgetcsv($handle,null,",")) !== false){
						//check line length
						if($csv_data !== array(null)){ //check for blank rows
							//check if header
							if(!$header_row){
								//increment the row count
								$row_count++;
								//check that all values are present
								if(count($csv_data) == 10){
									//testing
									//get line data
									$title = str_replace('"','&quot;',str_replace('\'','&#8217;',$csv_data[0]));
									$lat = $csv_data[1];
									$lng = $csv_data[2];
									$info_window_content = str_replace('"','&quot;',str_replace('\'','&#8217;',$csv_data[3]));
									$link_title = str_replace('"','&quot;',str_replace('\'','&#8217;',$csv_data[4]));
									$link = $csv_data[5];
									$link_colour = $csv_data[6];
									$link_target = $csv_data[7];
									$marker_category = str_replace('"','&quot;',str_replace('\'','&#8217;',$csv_data[8]));
									$custom_param = $csv_data[9];
									//check line data
									$status = true;
									//check title
									if(!$hmapsprem_helper->checkString($title)){ //required
										$status = false;
									}
									//check lat
									if(!$hmapsprem_helper->check_coords($lat)){ //required
										$status = false;
									}
									//check lng
									if(!$hmapsprem_helper->check_coords($lng)){ //required
										$status = false;
									}
									//check info window content
									if($info_window_content != "" && !$hmapsprem_helper->checkString($info_window_content)){
										$status = false;
									}
									//check link title
									if($link_title != "" && !$hmapsprem_helper->checkString($link_title)){
										$status = false;
									}
									//check link
									if($link != "" && !$hmapsprem_helper->check_valid_url($link)){
										$status = false;
									}
									//check link target
									if($link_target != "" && !$hmapsprem_helper->check_link_target($link_target)){
										$status = false;
									}
									//check link colour
									if($link_colour != "" && !$hmapsprem_helper->check_hex_colour($link_colour)){
										$status = false;
									}
									//check marker category
									if($marker_category != "" && !$hmapsprem_helper->checkString($marker_category)){
										$status = false;
									}
									//check custom parameter
									if($custom_param != "" && !$hmapsprem_helper->checkString($custom_param)){
										$status = false;
									}
									//check status
									if($status){
										//increment the success count
										$success_count++;
										//configure data
										//info window switch
										if($info_window_content == ""){
											$info_window_show = false;
										}else{
											$info_window_show = true;
										}
										//link switch
										if($link == "" || $link_title == ''){
											$link_show = false;
										}else{
											$link_show = true;
										}
										//marker category
										if($marker_category == ''){
											$marker_category = 'uncategorised';
										}
										//link colour
										if($link_colour == "" || !$hmapsprem_helper->check_hex_colour($link_colour)){
											$link_colour = '#000000';
										}
										//link target
										if($link_target == "" || !$hmapsprem_helper->check_link_target($link_target)){
											$link_target = '_blank';
										}
										//append to success array
										array_push($success_array, array(
											'marker_id' => null,
											'marker_category' => $marker_category,
											'latlng' => $lat .','. $lng,
											'title' => $title,
											'info_window_show' => $info_window_show,
											'info_window_content' => $info_window_content,
											'new' => true,
											'link_show' => $link_show,
											'link_title' => $link_title,
											'link' => $link,
											'link_colour' => $link_colour,
											'link_target' => $link_target,
											'custom_param' => $custom_param,
											'deleted' => false
										));
									}else{
										//increment the error count
										$error_count++;
										//add row to error csv
										$this->append_to_error_csv($csv_error_file_name, $csv_data);
									}
								}else{
									//increment the error count
									$error_count++;
									//add row to error csv
									$this->append_to_error_csv($csv_error_file_name, $csv_data);
								}
							}else{
								//set header false
								$header_row = false;
							}
						}
					}
					ini_set('auto_detect_line_endings', false);
					//check if successful rows
					$categories = array();
					if($success_count > 0){
						//extract marker categories
						foreach($success_array as $marker){
							array_push($categories, $marker['marker_category']);
						}
						//append marker categories to object
						$object_categories = (array) $object->map_marker_categories;
						foreach($categories as $category){
							array_push($object_categories, $category);
						}
						$object_categories = array_values(array_unique($object_categories, SORT_STRING));
						$object->map_marker_categories = (array) (json_decode(json_encode($object_categories)));
						//append markers to object
						$object_markers = (array) $object->map_markers;
						foreach($success_array as $marker){
							$object_markers['grs' . str_replace('-','',$hmapsprem_helper->genGUID())] = $marker;
						}
						$object->map_markers = json_decode(json_encode($object_markers));
						//persist object
						$wpdb->query("
							UPDATE
								`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
							SET
								`json_object` = '". json_encode($object, JSON_UNESCAPED_UNICODE) ."'
							WHERE
								`storage_id` = ". $map_id .";
						");
					}
					//clean all files except errors
					if($handle = opendir($this->plugin_dir . $this->marker_csv_import_dir)){
						while(false !== ($file = readdir($handle))){
							if('.' === $file) continue;
							if('..' === $file) continue;
							if('import_errors.csv' === $file) continue;
							$path_to_file = $this->plugin_dir . $this->marker_csv_import_dir . $file;
							//remove file
							unlink($path_to_file);
						}
					}
					closedir($handle);
					//respond
					echo json_encode(array(
						'row_count' => $row_count,
						'success_count' => $success_count,
						'error_count' => $error_count
					));
					exit();
				}
			}
			//respond
			echo json_encode(false);
			exit();
		}

		#GENERATE ERROR FILE
		private function append_to_error_csv($csv_error_file_name, $csv_data){
			//flag error
			$this->error_in_processing = true;
			//check if file exists
			$path_to_file = $this->plugin_dir . $this->marker_csv_import_dir . $csv_error_file_name;
			if(!file_exists($path_to_file)){
				//append header
				$error_file = fopen($path_to_file, "a+");
				$error_data = '"Location Title","Latitude","Longitude","Info Window Content","Link Title","Link","Link Color","Link Target","Marker Category","onClick Parameter"';
				fwrite($error_file, $error_data . PHP_EOL);
				fclose($error_file);
			}
			//process error file
			$error_file = fopen($path_to_file, "a+");
			$error_data = "\"". implode("\",\"", $csv_data) ."\"";
			fwrite($error_file, $error_data . PHP_EOL);
			fclose($error_file);
		}

	}