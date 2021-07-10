<?php
	
	#SECURITY CHECK
	require_once('frame_sec.check.php');
	if(isset($secure_tag) && $secure_tag){ //secure (display content)

		#MARKER PACK UPLOADER
		
		//check for post
		if($_SERVER['REQUEST_METHOD'] === 'POST'){

			echo '
				<script type="text/javascript" data-cfasync="false">
					window.parent.process_complete();
				</script>
			';
			
		}

?>

<!--BEGIN: includes-->
<link type="text/css" rel="stylesheet" href="../assets/css/iframe.example.css"></link>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" data-cfasync="false"></script>
<script type="text/javascript" src="../assets/js/iframe.example.js" data-cfasync="false"></script>
<!--END: includes-->

<!--BEGIN: upload form-->
<div class="example_iframe">
	<form method="post" enctype="multipart/form-data" id="example-iframe-form">
    	<div class="hero_form_row_full">
            <input type="file" id="example_input" name="example_input">
            <div class="example-upload-btn"><a class="hero_button_auto green_button rounded_3 size_14">Test iFrame Upload</a></div>
        </div>
    </form>
</div>
<!--END: upload form-->

<?php
	}
?>