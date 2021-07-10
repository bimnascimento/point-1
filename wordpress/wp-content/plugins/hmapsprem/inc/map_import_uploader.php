<?php
	
	#SECURITY CHECK
	require_once('frame_sec.check.php');
	if(isset($secure_tag) && $secure_tag){ //secure (display content)

		#MARKER PACK UPLOADER
		
		//check for post
		if($_SERVER['REQUEST_METHOD'] === 'POST'){
			
			//vars
			$tmp_dir = '../_map_import_uploads/';
			$file_mimes = array(
				'text/plain'
			);
			
			//check file type
			if(in_array($_FILES['map_import']['type'], $file_mimes)){
				//process file
				//if(json_decode(file_get_contents($_FILES['map_import']['tmp_name']), true)){ //check if valid
					//check tmp dir
					if(!is_dir($tmp_dir)){
						mkdir($tmp_dir);
					}
					//place file in tmp_dir
					$file_name = sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),mt_rand(0, 0x0fff) | 0x4000,mt_rand(0, 0x3fff) | 0x8000,mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
					$file = $tmp_dir . $file_name . '.txt';
					move_uploaded_file($_FILES['map_import']['tmp_name'], $file);
				//}
				echo '
					<script type="text/javascript" data-cfasync="false">
						window.parent.process_map_import(\'process_complete\');
					</script>
				';
			}else{
				echo '
					<script type="text/javascript" data-cfasync="false">
						window.parent.show_message("error", "Upload Error", "The selected file was not a valid Hero Map.");
					</script>
				';
			}
			
		}

?>

<!--BEGIN: includes-->
<link type="text/css" rel="stylesheet" href="../assets/css/map_import_uploader.css"></link>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" data-cfasync="false"></script>
<script type="text/javascript" src="../assets/js/map_import_uploader.js" data-cfasync="false"></script>
<!--END: includes-->

<!--BEGIN: upload form-->
<div class="map_import_uploader">
	<form method="post" enctype="multipart/form-data" id="map-import-uploader">
    	<div class="hero_form_row_full">
            <input type="file" id="map_import" name="map_import">
            <div class="map-import-upload-btn"><a class="hero_button_auto green_button rounded_3 size_14">Choose File</a></div>
        </div>
    </form>
</div>
<!--END: upload form-->

<?php
	}
?>