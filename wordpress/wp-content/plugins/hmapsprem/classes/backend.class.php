<?php

	#PLUGIN BACK-END MANAGEMENT
	class hmapsprem_backend{
		
		#CONSTRUCT
		public function __construct(){
		}
		
		#INSERT OBJECT
		public function insert_object(){
			//check for object
			if(isset($_POST['object_name']) && isset($_POST['object'])){
				//access globals
				global $wpdb;
				//insert default object
				$sql_insert = $wpdb->query("
					INSERT INTO `". $wpdb->base_prefix ."hmapsprem_default_storage_table` (`object_name`, `json_object`)
					VALUES('". str_replace('"','&quot;',str_replace('\'','&#8217;',$_POST['object_name'])) ."','". $_POST['object'] ."');
				");
				echo json_encode($wpdb->insert_id);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#GET OBJECT LIST
		public function get_object_list(){
			//access globals
			global $wpdb;
			//select object list
			$objects = $wpdb->get_results("
				SELECT
					*
				FROM
					`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
				WHERE
					`deleted` = 0;
			");
			//check that at least one object exists
			if(count($objects) > 0){
				//create return object
				$object_list = array();
				//iterate results
				foreach($objects as $object){
					array_push($object_list, array(
						'object_id' => intval($object->storage_id),
						'object_name' => $object->object_name
					));
				}
				//return object list
				echo json_encode($object_list);
				exit();				
			}
			//respond with nothing found
			echo json_encode(false);
			exit();
		}
		
		#GET OBJECT
		public function get_object(){
			//check for object
			if(isset($_POST['object_id'])){
				//access globals
				global $wpdb;
				//select object
				$object = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					WHERE
						`storage_id` = ". $_POST['object_id'] ."
						AND `deleted` = 0;
				");
				//check that object exists
				if(count($object) > 0){
					//return object
					echo json_encode(array(
						'object_name' => addslashes($object[0]->object_name),
						'object' => $object[0]->json_object
					));
					exit();
				}
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#UPDATE OBJECT
		public function update_object(){
			//check for object
			if(isset($_POST['object_id']) && isset($_POST['object'])){
				//access globals
				global $wpdb;
				//insert default object
				$sql_update = $wpdb->query("
					UPDATE
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					SET
						`json_object` = '". $_POST['object'] ."'
					WHERE
						`storage_id` = ". $_POST['object_id'] .";
				");
				echo json_encode(true);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#RENAME OBJECT
		public function rename_object(){
			//check for object
			if(isset($_POST['object_id']) && isset($_POST['object_name'])){
				//access globals
				global $wpdb;
				//insert default object
				$sql_update = $wpdb->query("
					UPDATE
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					SET
						`object_name` = '". str_replace('"','&quot;',str_replace('\'','&#8217;',$_POST['object_name'])) ."'
					WHERE
						`storage_id` = ". $_POST['object_id'] .";
				");
				echo json_encode(true);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#DELETE OBJECT
		public function delete_object(){
			//check for object_id
			if(isset($_POST['object_id'])){
				//access globals
				global $wpdb;
				//insert default object
				$sql_update = $wpdb->query("
					UPDATE
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					SET
						`deleted` = 1
					WHERE
						`storage_id` = ". $_POST['object_id'] .";
				");
				echo json_encode(true);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#DUPLICATE OBJECT
		public function duplicate_object(){
			//check for object_id
			if(isset($_POST['object_id'])){
				//access globals
				global $wpdb;
				//create clone object
				$wpdb->query("
					INSERT INTO `". $wpdb->base_prefix ."hmapsprem_default_storage_table`(`object_name`,`json_object`)
					SELECT CONCAT(`object_name`,' (clone)'),`json_object` FROM `". $wpdb->base_prefix ."hmapsprem_default_storage_table` WHERE `storage_id` = ". $_POST['object_id'] .";
				");
				echo json_encode($wpdb->insert_id);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#GET MARKERS
		public function get_markers(){
			//access globals
			global $wpdb;
			//get markers
			$markers = array(
				'categories' => array()
			);
			$markers_object = $wpdb->get_results("
				SELECT
					`mc`.`category_id`,
					`mc`.`name` AS 'category',
					`mc`.`imported`,
					`mc`.`deleted` AS 'cat_deleted',
					`m`.`marker_id`,
					`m`.`img_binary`,
					`m`.`width`,
					`m`.`height`,
					`m`.`left_offset`,
					`m`.`top_offset`,
					`m`.`link`,
					`m`.`primary_colour`,
					`m`.`secondary_colour`,
					`m`.`deleted`
				FROM
					`". $wpdb->base_prefix ."hmapsprem_markers` `m`
					INNER JOIN `". $wpdb->base_prefix ."hmapsprem_marker_categories` `mc` ON(`mc`.`category_id` = `m`.`category_id`);
			");
			//check marker object
			if(count($markers_object) > 0){
				//extract categories
				foreach($markers_object as $marker){
					$markers['categories'][intval($marker->category_id)] = array(
						'category_id' => intval($marker->category_id),
						'category' => $marker->category,
						'imported' => $marker->imported,
						'deleted' => $marker->cat_deleted,
						'links' => array()
					);
				}
				//extract links per category
				foreach($markers_object as $marker){
					$markers['categories'][intval($marker->category_id)]['links'][intval($marker->link)] = array(
						'primary_colour' => $marker->primary_colour,
						'secondary_colour' => $marker->secondary_colour,
						'link' => intval($marker->link),
						'markers' => array()
					);
				}
				//link markers to category links
				foreach($markers_object as $marker){
					array_push($markers['categories'][intval($marker->category_id)]['links'][intval($marker->link)]['markers'], array(
						'marker_id' => intval($marker->marker_id),
						'img_binary' => base64_encode($marker->img_binary),
						'width' => intval($marker->width),
						'height' => intval($marker->height),
						'left_offset' => intval($marker->left_offset),
						'top_offset' => intval($marker->top_offset),
						'deleted' => intval($marker->deleted)
					));
				}
			}else{
				$markers = false;
			}
			//respond
			echo json_encode($markers);
			exit();
		}
		
		#GET CUSTOM MARKERS
		public function get_custom_markers(){
			//access globals
			global $wpdb;
			//get custom markers
			$custom_markers = array();
			$custom_marker_results = $wpdb->get_results("
				SELECT
					`m`.*
				FROM
					`". $wpdb->base_prefix ."hmapsprem_markers` `m`
					INNER JOIN `". $wpdb->base_prefix ."hmapsprem_marker_categories` `mc` ON(`mc`.`category_id` = `m`.`category_id` AND `mc`.`name` = 'Custom')
				WHERE
					`m`.`deleted` = 0
				GROUP BY
					`m`.`marker_id`
				ORDER BY
					`m`.`created` DESC;
			");
			if(count($custom_marker_results) > 0){
				//loop through markers and base64 encode image data
				foreach($custom_marker_results as $marker){
					array_push($custom_markers, array(
						'marker_id' => intval($marker->marker_id),
						'width' => intval($marker->width),
						'height' => intval($marker->height),
						'left_offset' => intval($marker->left_offset),
						'top_offset' => intval($marker->top_offset),
						'img_binary' => base64_encode($marker->img_binary)
					));
				}
			}else{
				$custom_markers = false;
			}
			//respond
			echo json_encode($custom_markers);
			exit();
		}
		
		#UPDATE CUSTOM MARKER OFFSET
		public function update_custom_marker_offset(){
			//access globals
			global $wpdb;
			//get post data
			$marker_id = intval($_POST['marker_id']);
			$top_offset = intval($_POST['top_offset']);
			$left_offset = intval($_POST['left_offset']);
			//update marker
			$wpdb->query("
				UPDATE `". $wpdb->base_prefix ."hmapsprem_markers`
				SET `left_offset` = ". $left_offset .", `top_offset` = ". $top_offset ."
				WHERE `marker_id` = ". $marker_id .";
			");
			echo json_encode(true);
			exit();	
		}
		
		#REMOVE CUSTOM MARKER
		public function remove_custom_marker(){
			//access globals
			global $wpdb;
			//get post data
			$marker_id = intval($_POST['marker_id']);
			//remove marker
			$wpdb->query("
				UPDATE `". $wpdb->base_prefix ."hmapsprem_markers`
				SET `deleted` = 1
				WHERE `marker_id` = ". $marker_id .";
			");
			echo json_encode(true);
			exit();
		}
		
		#GET OBJECT EXPORT
		public function get_object_export(){
			//check for object
			if(isset($_POST['object_id'])){
				//access globals
				global $wpdb;
				//get markers object
				$markers_object = $wpdb->get_results("
					SELECT
						`m`.`marker_id`,
						`m`.`img_binary`,
						`m`.`width`,
						`m`.`height`,
						`m`.`left_offset`,
						`m`.`top_offset`,
						`m`.`link`,
						`m`.`primary_colour`,
						`m`.`secondary_colour`
					FROM
						`". $wpdb->base_prefix ."hmapsprem_markers` `m`;
				");
				//select object
				$object = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					WHERE
						`storage_id` = ". $_POST['object_id'] ."
						AND `deleted` = 0;
				");
				//check that object exists
				if(count($object) > 0){
					//get json object
					$db_object = str_replace('\'','&#39;',$object[0]->json_object);
					$json_object = json_decode($db_object, false);
					//create active markers array
					$active_markers = array();
					//loop through markers object and add marker_id to active markers array
					foreach($json_object->map_markers as $marker){
						array_push($active_markers, $marker->marker_id);
					}
					//remove duplicates and reindex
					$active_markers = array_values(array_unique($active_markers));
					//get active marker data
					$marker_data = array();
					foreach($active_markers as $marker_id){
						foreach($markers_object as $key=>$marker_info){
							if($marker_info->marker_id == $marker_id){
								array_push($marker_data, array(
									'marker_id' => intval($marker_info->marker_id),
									'img_binary' => base64_encode($marker_info->img_binary),
									'width' => intval($marker_info->width),
									'height' => intval($marker_info->height),
									'left_offset' => intval($marker_info->left_offset),
									'top_offset' => intval($marker_info->top_offset),
									'link' => 0,
									'primary_colour' => '#999999',
									'secondary_colour' => '#333333'
								));
								break;
							}
						}
					}
					//return object
					echo json_encode(array(
						'object_name' => $object[0]->object_name,
						'object' => $json_object,
						'markers_object' => $marker_data
					));
					exit();
				}
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
		#DELETE MARKER PACK
		public function delete_marker_pack(){
			//check for object
			if(isset($_POST['category_id'])){
				//access globals
				global $wpdb;
				//mark category as deleted
				$sql_update = $wpdb->query("
					UPDATE
						`". $wpdb->base_prefix ."hmapsprem_marker_categories`
					SET
						`deleted` = 1
					WHERE
						`category_id` = ". $_POST['category_id'] .";
				");
				echo json_encode(true);
				exit();
			}
			//respond with error
			echo json_encode(false);
			exit();
		}
		
	}