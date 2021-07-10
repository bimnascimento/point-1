<?php
	
	//check for post
	if($_SERVER['REQUEST_METHOD'] === 'POST'){
		$file_url = $_POST['map_download_file'];
		$file_name = str_replace('&quot;','',str_replace('&#8217;','',$_POST['map_download_file_name']));
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Type: text/plain');
		header("Content-disposition: attachment; filename=\"" . $file_name . "\""); 
		header('Content-Length: ' . strlen($file_url));
		echo $file_url;
		exit();
	}

	//strip slashes
	function stripslashes_r($value){
		if(is_array($value)){
			return array_map('stripslashes_r', $value);
		}
		return stripslashes($value);
	}
	
?>