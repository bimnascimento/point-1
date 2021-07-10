//MAPS_ADVANCED VIEW

//view load
jQuery(function(){
	//configure view components
	configure_view();
	//bind button listeners
	bind_button_listeners();
});

//configure view components
function configure_view(){
	//marker animation
	jQuery.each(map_config.marker_animation_types, function(key,val){
		jQuery('#marker_animation').append('<option value="'+ key +'" '+ (main_object.map_advanced.marker_animation == key ? 'selected' : '') +'>'+ val +'</option>');
	});
}

//bind button listeners
function bind_button_listeners(){
	//map load zoom
	jQuery('#get_load_zoom_btn').off().on('click', function(){
		//get map zoom
		var cur_zoom = google_map.getZoom();
		main_object.map_advanced.map_load_zoom = cur_zoom;
		jQuery('#map_load_zoom').val(main_object.map_advanced.map_load_zoom);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//marker click zoom
	jQuery('#get_marker_click_zoom_btn').off().on('click', function(){
		//get map zoom
		var cur_zoom = google_map.getZoom();
		main_object.map_advanced.marker_click_zoom = cur_zoom;
		jQuery('#marker_click_zoom').val(main_object.map_advanced.marker_click_zoom);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}