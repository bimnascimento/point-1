//MAPS_CONTROLS VIEW

//view config
var animation_timer = 300; //show/hide animation time in milliseconds

//view load
jQuery(function(){
	//populate view
	populate_view();
	//bind component change listeners
	bind_component_change_listeners();
	//bind view/hide controls
	bind_view_hide_controls();
	//configure view components
	configure_view();
});

//populate view
function populate_view(){
	//street view position
	jQuery.each(map_config.control_positions, function(key,val){
		jQuery('#street_view_position').append('<option value="'+ key +'" '+ (main_object.map_controls.street_view_position == key ? 'selected' : '') +'>'+ val +'</option>');
	});
	//map type position
	jQuery.each(map_config.control_positions, function(key,val){
		jQuery('#map_type_position').append('<option value="'+ key +'" '+ (main_object.map_controls.map_type_position == key ? 'selected' : '') +'>'+ val +'</option>');
	});
	//map type style
	jQuery.each(map_config.map_type_control_styles, function(key,val){
		jQuery('#map_type_style').append('<option value="'+ key +'" '+ (main_object.map_controls.map_type_style == key ? 'selected' : '') +'>'+ val +'</option>');
	});
	//rotate position
	jQuery.each(map_config.control_positions, function(key,val){
		jQuery('#rotate_position').append('<option value="'+ key +'" '+ (main_object.map_controls.rotate_position == key ? 'selected' : '') +'>'+ val +'</option>');
	});
	//zoom position
	jQuery.each(map_config.control_positions, function(key,val){
		jQuery('#zoom_position').append('<option value="'+ key +'" '+ (main_object.map_controls.zoom_position == key ? 'selected' : '') +'>'+ val +'</option>');
	});
}

//configure view
function configure_view(){
	var switch_check_arr = [
		'street_view',
		'map_type',
		'rotate',
		'zoom'
	];
	jQuery.each(switch_check_arr, function(key, val){
		var checked = eval("main_object.map_controls."+ val);
		var container = jQuery('#'+ val).closest('.hero_section_holder').find('.hide_container');
		if(checked){
			container.css({
				'height': container.children('.internal').height() +'px',
				'overflow': 'visible'
			});
		}else{
			container.css({
				'overflow': 'hidden',
				'height': 0
			});
		}
	});
}

//bind component change listeners
function bind_component_change_listeners(){
	//street view
	jQuery('#street_view, #street_view_position, #map_type, #map_type_position, #map_type_style, #rotate, #rotate_position, #zoom, #zoom_position, #scale, #show_location').on('change', function(){
		//manage map controls
		setTimeout(function(){
			manage_map_controls();
		},100);
	});
}

//bind view/hide controls
function bind_view_hide_controls(){
	jQuery('.hide_switch').on('change', function(){
		var checked = jQuery(this).is(':checked');
		var container = jQuery(this).closest('.hero_section_holder').find('.hide_container');
		if(checked){
			container.stop().animate({
				'height': container.children('.internal').height() +'px'
			},animation_timer, function(){
				container.css({
					'overflow': 'visible'
				})
			});
		}else{
			container.css({'overflow': 'hidden'}).stop().animate({
				'height': 0
			},animation_timer);
		}
	});
}