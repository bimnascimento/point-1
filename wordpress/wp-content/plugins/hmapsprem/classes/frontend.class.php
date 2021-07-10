<?php

	#PLUGIN FRONT-END MANAGEMENT
	class hmapsprem_frontend{
		
		#IMPLEMENT SHORTCODE LISTENER
		public function get_shortcode_content($atts){
			//fetch object and return
			return $this->get_object_from_database($atts['id']);
		}
		
		#GET OBJECT FROM DATABASE
		private function get_object_from_database($object_id){
			//check for object
			if(isset($object_id)){
				//access globals
				global $wpdb, $hmapsprem_helper;
				//select object
				$object = $wpdb->get_results("
					SELECT
						*
					FROM
						`". $wpdb->base_prefix ."hmapsprem_default_storage_table`
					WHERE
						`storage_id` = ". $object_id ."
						AND `deleted` = 0;
				");
				//check that object exists
				if(count($object) > 0){
					//generate unique name
					$unique_name = str_replace('-','',$hmapsprem_helper->genGUID());
					//decode map object
					$map_object = json_decode($object[0]->json_object);
					//extract markers required for map from object
					$marker_ids_array = array();
					foreach($map_object->map_markers as $marker){
						array_push($marker_ids_array, $marker->marker_id);
					}
					//fetch marker data
					$marker_data = $this->get_markers_frontend(array_unique($marker_ids_array));
					//append marker data to map object
					$map_object->marker_data = $marker_data;
					//generate response output
					$response = '
						<div class="hmapsprem_container">
							<div id="hmapsprem_map_'. $unique_name .'" class="hmapsprem_map_container"></div>
							<script type="text/javascript" data-cfasync="false">
								var hmapsprem_default_object_'. $unique_name .' = '. json_encode($map_object) .';
								jQuery(function(){
									hmapsprem_initialise_frontend("'. $unique_name .'");
								});
							</script>
						</div>
					';
					return $response;
				}
			}
			//respond with not found
			return 'Unable to locate map with id: '. $object_id;
		}

		#GET MARKERS
		private function get_markers_frontend($marker_ids_array){
			//access globals
			global $wpdb;
			//get marker ids array
			$marker_ids = implode(',', $marker_ids_array);
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
					INNER JOIN `". $wpdb->base_prefix ."hmapsprem_marker_categories` `mc` ON(`mc`.`category_id` = `m`.`category_id`)
				WHERE
					`m`.`marker_id` IN(". $marker_ids .");
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
			return $markers;
		}
		
	}	