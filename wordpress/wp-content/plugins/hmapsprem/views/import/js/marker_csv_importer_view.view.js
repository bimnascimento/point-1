//MARKER_CSV_IMPORTER_VIEW VIEW

//view load
jQuery(function(){
	//set header
	set_current_header_label('Currently Viewing','Marker CSV Import Manager');
	//get maps list
	hplugin_get_object_list('populate_destination_map_select');
});

//load iframe for map import
function load_iframe(){
	load_secure_iframe('inc/marker_csv_import_uploader.php', 50, '.marker_csv_import_upload_holder');
}

//detect processing completion
function process_complete(){
	//show success message
	window.parent.show_message("success", "Upload Success", "The selected map markers have been successfully imported.");
	//reload iframe (new security token)
	load_iframe();
}

//populate destination map select
function populate_destination_map_select(json){
	//check maps
	if(json.length > 0){
		//populate select box
		jQuery('#destination_map_selection').empty();
		jQuery.each(json, function(key, val){
			jQuery('#destination_map_selection').append('<option value="'+ val.object_id +'">'+ val.object_name +'</option>');
		});
		switch_components();
		//fade in container
		jQuery('#upload_holding_container').fadeIn();
		//load iframe for marker csv import
		load_iframe();
	}else{
		jQuery('#destination_map_selection').replaceWith('<p>In order to upload markers to a map, you need to first create a new map. Please click on "Maps" in the sidebar and select "Add New".</p>');
	}
}

//process marker csv import
function process_marker_csv_import(csv_file_name){
	var destination_map = jQuery('#destination_map_selection').val();
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_process_marker_csv_import',
			'map_id': destination_map,
			'file_name': csv_file_name
		},
		dataType: "json"
	}).done(function(json_object){
		//place results
		jQuery('#importer_total_processed').html(json_object.row_count);
		jQuery('#importer_success_processed').html(json_object.success_count);
		jQuery('#importer_errors_processed').html(json_object.error_count);
		if(parseInt(json_object.error_count) > 0){
			jQuery('#upload_errors_holder').fadeIn(0);
		}else{
			jQuery('#upload_errors_holder').fadeOut(0);
		}
		//fade in results holder
		jQuery('#upload_results_holder').fadeIn(500);
	});
}
























