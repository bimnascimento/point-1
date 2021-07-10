//MAP_IMPORTER_VIEW VIEW

//view load
jQuery(function(){
	//set header
	set_current_header_label('Currently Viewing','Map Import Manager');
	//load iframe for map import
	load_iframe();
});

//load iframe for map import
function load_iframe(){
	load_secure_iframe('inc/map_import_uploader.php', 50, '.map_import_upload_holder');
}

//detect processing completion
function process_complete(){
	//show success message
	window.parent.show_message("success", "Upload Success", "The selected map has been successfully imported.");
	//reload iframe (new security token)
	load_iframe();
	//navigate to marker packs page
	load_core_view(0, 'dashboard', 'Dashboard', 'dashboard/',undefined,undefined, false, false);
	//reload sidebar elements
	prepopulate_sidebar_elements();
}