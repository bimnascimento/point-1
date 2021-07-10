//MARKER PACK UPLOADER

//frame load
jQuery(function(){
	//bind upload button
	bind_upload_btn();
});

//bind upload button
function bind_upload_btn(){
	jQuery('.map-import-upload-btn a').off().on('click', function(){
		jQuery('#map_import').trigger('click').off().on('change', function(){
			jQuery('#map-import-uploader').trigger('submit');
		});
	});
}