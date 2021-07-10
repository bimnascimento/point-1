//MAPS_DEVELOPERS VIEW

//view config
var animation_timer = 300; //show/hide animation time in milliseconds

//view load
jQuery(function(){
	//place controls in correct state
	place_control_state();
	//bind view/hide controls
	bind_view_hide_controls();
});

//place controls in correct state
function place_control_state(){
	var switch_check_arr = [
		'javascript_callback'	
	];
	jQuery.each(switch_check_arr, function(key, val){
		var checked = eval("main_object.map_developers."+ val);
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

//bind view/hide controls
function bind_view_hide_controls(){
	jQuery('.hide_switch').on('change', function(){
		var checked = jQuery(this).is(':checked');
		var container = jQuery(this).closest('.hero_section_holder').find('.hide_container');
		if(checked){
			jQuery('#custom_param_container').fadeIn(300);
			container.stop().animate({
				'height': container.children('.internal').height() +'px'
			},animation_timer, function(){
				container.css({
					'overflow': 'visible'
				})
			});
		}else{
			jQuery('#custom_param_container').fadeOut(300);
			container.css({'overflow': 'hidden'}).stop().animate({
				'height': 0
			},animation_timer);
		}
	});
}