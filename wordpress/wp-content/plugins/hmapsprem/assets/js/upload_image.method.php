<?php

	#UPLOAD IMAGE
	class upload_image{
		
		#CLASS VARS
		private $check;
		private $guid;
		private $platform;
		private $client_version;
		private $debug;
		private $query_params;
		private $files;
		private $parameter_validation = true;
		private $parameter_validation_message = array();
		private $img_width = 1166;
		private $img_height = 1654;
		private $received_width;
		private $received_height;
		private $overlay_width = 4858;
		private $overlay_height = 6892;
		
		#CONSTRUCT
		public function __construct($platform,$client_version,$debug,$query_params,$post,$files){
			//set class var values
			$this->platform = $platform; //platform (mobile device platform e.g. Android, etc...)
			$this->client_version = $client_version; //client version (mobile application version)
			$this->debug = $debug; //debug mode (live or dev)
			$this->query_params = $query_params; //query params
			$this->files = $files;
			//instantiate guid helper class
			$this->guid = new guid_helper();
			//instantiate check helper class
			$this->check = new check_helper();
			//validate image
			$this->validate_image();
		}
		
		#VALIDATE IMAGE
		private function validate_image(){
			//validate image
			if(isset($this->files['upload_img']) && $this->files['upload_img']['error'] == 0){
				//define mime type array for validation
				$mimes = array('image/jpeg');
				//check mime type
				if(in_array($this->files['upload_img']['type'],$mimes)){
					//get temp file
					$temp_file = $this->files['upload_img']['tmp_name'];
					//get dimensions
					list($img_width, $img_height, $type, $attr) = getimagesize($temp_file);
					//check dimensions
					array_push($this->parameter_validation_message,'image: success');
					//set image dimensions
					$this->received_width = $img_width;
					$this->received_height = $img_height;
				}else{
					array_push($this->parameter_validation_message,'image: unsupported media type');
					$this->parameter_validation = false;
				}
			}else{
				array_push($this->parameter_validation_message,'image: not found');
				$this->parameter_validation = false;
			}
		}
		
		#INITIALISE
		public function init(){
			//check validation
			if($this->parameter_validation){
				//check if live mode
				if($this->debug == 'live'){
					//add over lay and persist image to directory (new directory per day)
					$dir_name = 'images/processed_images/'. date('Ymd') .'/';
					if(!is_dir($dir_name)){
						mkdir($dir_name);
					}
					$src = imagecreatefromjpeg($this->files['upload_img']['tmp_name']);
					$overlay = imagecreatefrompng('images/print_overlay.png');
					$output = imagecreatetruecolor($this->img_width, $this->img_height);
					imagecopyresampled($output, $src, 0, 0, 0, 0, $this->img_width, $this->img_height, $this->received_width, $this->received_height);
					imagecopyresampled($output, $overlay, 0, 0, 0, 0, $this->img_width, $this->img_height, $this->overlay_width, $this->overlay_height);
					//imagejpeg($output, $dir_name . $this->guid->make() .'.jpg', 100);
					imagepng($output, $dir_name . $this->guid->make() .'.png');
					imagedestroy($src);
					imagedestroy($overlay);
					imagedestroy($output);
				}
				//respond
				return array(200,array());
			}else{
				//parameter validation failed
				return array(400,array(),implode(' | ', $this->parameter_validation_message));
			}
		}
		
	}

?>