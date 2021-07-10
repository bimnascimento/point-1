//MARKER PROCESSOR

//load
jQuery(function(){
	//process map import
	process_map_import();
});

//process marker pack
function process_map_import(callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_process_map_import'
		},
		dataType: "json"
	}).done(function(response){
		if(typeof callback !== 'undefined'){
			eval(""+ callback +"();");
		}
	});
}