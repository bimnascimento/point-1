//MARKER PACK UPLOADER

//frame load
jQuery(function(){
	//bind upload button
	bind_upload_btn();
});

//bind upload button
function bind_upload_btn(){
	jQuery('.example-upload-btn a').off().on('click', function(){
		jQuery('#example_input').trigger('click').off().on('change', function(){
			jQuery('#example-iframe-form').trigger('submit');
		});
	});
}