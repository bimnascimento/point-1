<?php 
if (!class_exists('IndeedRackSpace')){
	class IndeedRackSpace{
		private $metas=FALSE;
		private $destination_id=FALSE;
	
		public function __construct($id) {
			/*
			 * @param id of destination
			 * @return none
			 */
			$this->destination_id = $id;
			$this->set_metas();
			
			//load the classes
			if (!class_exists('ClassLoader')){
				require_once IBK_PATH . 'classes/API/OpenCloud/ClassLoader.class.php';
			}			
			require_once IBK_PATH . 'classes/API/OpenCloud/Globals.php';	
			$classLoader = new ClassLoader();
			$classLoader->registerNamespaces(array(
					'OpenCloud' => IBK_PATH . 'classes/API'
			));
			$classLoader->register();
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
				foreach (array('username', 'api_key', 'container', 'region') as $k){//'container_url',
					$data = $wpdb->get_row('SELECT meta_value FROM ' . $table_name . ' WHERE meta_name="'.$k.'" AND destination_id='.$this->destination_id);
					if (!empty($data->meta_value)) $this->metas[$k] = $data->meta_value;					
				}
			}
		}

		private function connection_object(){
			$conn = new \OpenCloud\Rackspace(
					'https://identity.api.rackspacecloud.com/v2.0/',
					array(  'username' => $this->metas['username'],
							'apiKey' => $this->metas['api_key']  )
			);
			$obj = $conn->ObjectStore( 'cloudFiles', $this->metas['region'], 'publicURL' );//$this->metas['serviceName']
			return $obj;
		}
	
		private function set_container(){
			$obj = $this->connection_object();
			$container = $obj->Container($this->metas['container']);
			return $container;
		}
	
		public function send_file( $file_path ){
			/*
			 * @param full path
			 */					
			$container = $this->set_container();
			$file = $container->DataObject();
			$file->SetData( file_get_contents( $file_path ) );
			$file->name = basename($file_path);
			$value = $file->Create();
			return $value;
		}
	
	}//end of IndeedRackSpace	
}