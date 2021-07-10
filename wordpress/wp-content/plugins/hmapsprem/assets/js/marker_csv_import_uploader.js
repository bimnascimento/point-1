//MARKER PACK UPLOADER

//frame load
jQuery(function(){
	//bind upload button
	bind_upload_btn();
});

//bind upload button
function bind_upload_btn(){
	jQuery('.marker-csv-import-upload-btn a').off().on('click', function(){
		jQuery('#marker_csv_import').trigger('click').off().on('change', function(){
			jQuery('#marker-csv-import-uploader').trigger('submit');
		});
	});
}