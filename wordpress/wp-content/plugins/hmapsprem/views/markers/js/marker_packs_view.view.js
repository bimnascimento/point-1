//MARKER_PACKS_VIEW VIEW

//view globals
var markers_object;

//view load
jQuery(function(){
	//get markers
	get_markers();
});

//get markers
function get_markers(){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_get_markers'
		},
		dataType: "json"
	}).done(function(markers){
		//set view global
		markers_object = markers;
		//extract categories
		extract_categories(markers);
	});
}

//extract categories
function extract_categories(markers){
	//loop through markers
	jQuery('#marker_category').empty();
	jQuery.each(markers.categories, function(key,val){
		if(val.deleted == 0){
			//check that markers exist for preset
			var live_markers = false;
			jQuery.each(val.links, function(i,v){
				jQuery.each(v.markers, function(k,s){
					if(s.deleted == 0){
						live_markers = true;
						return false;
					}
				});
			});
			//place category
			if(live_markers){
				jQuery('#marker_category').append('<option value="'+ val.category_id +'">'+ val.category +'</option>');
			}
		}
	});
	//enable components
	switch_components();
	//update select component
	update_select_component(jQuery('#marker_category'));
	//bind marker pack change listner
	bind_marker_pack_change_listener();
	//load presets for category
	load_presets_for_category();
}

//bind marker pack change listner
function bind_marker_pack_change_listener(){
	jQuery('#marker_category').on('change', function(){
		load_presets_for_category();
	});
}

//load colour presets for category
function load_presets_for_category(){
	//get marker category
	var category_id = parseInt(jQuery("#marker_category option").filter(":selected").val());
	//empty container
	jQuery('.hero_preset_holder').empty();
	//remove notice
	jQuery('.pack_notice_copy').remove();
	//populate container
	var check_count = 0;
	jQuery.each(markers_object.categories[category_id].links, function(key,val){
		var active = '';
		if(check_count == 0){
			flid = val.link;
		}
		var preset_html = '<div id="colour_preset_cat_'+ val.link +'" class="hero_preset_color rounded_20" onclick="load_markers_for_colour_preset('+ category_id +', '+ val.link +');">';
		preset_html += '<div class="hero_preset_one rounded_left_20" style="background-color: rgb('+ hexToRgb(val.primary_colour) +');"></div>';
		preset_html += '<div class="hero_preset_two rounded_right_20" style="background-color: rgb('+ hexToRgb(val.secondary_colour) +');"></div>';
		preset_html += '</div>';
		jQuery('.hero_preset_holder').append(preset_html);
		check_count++;
	});
	jQuery('#marker_display_holder').append('<div class="clear:both;"></div>');
	//load markers for category colour preset
	load_markers_for_colour_preset(category_id,flid);
	//check for Custom pack
	if(markers_object.categories[category_id].category == 'Custom'){
		//change opacity
		jQuery('.hero_preset_color').css({
			'opacity': 0.1
		}).parent().parent().append('<div class="size_12 pack_notice_copy" style="float:right; margin:9px 15px 0 0;">Color schemes are not supported for custom markers</div>');
	}
	//check for imported pack
	if(markers_object.categories[category_id].imported == 1){
		//change opacity
		jQuery('.hero_preset_color').css({
			'opacity': 0.1
		}).parent().parent().append('<div class="size_12 pack_notice_copy" style="float:right; margin:9px 15px 0 0;">Color schemes are not supported for custom marker packs</div>');
		//add delete button
		jQuery('.delete_pack_btn').remove();
		jQuery('<div class="delete_pack_btn" style="float:right; margin:5px 0 0 0;"><div style="margin-right:0;" class="hero_button_auto red_button rounded_3" onclick="request_marker_pack_delete('+ category_id +');">DELETE MARKER PACK</div></div>').insertAfter('#marker_display_holder');
	}else{
		jQuery('.delete_pack_btn').remove();
	}
}

//request marker pack delete
function request_marker_pack_delete(category_id){
	if(window.confirm('Are you sure you want to delete this custom marker pack?')){
		delete_marker_pack(category_id);
	}
}

//delete marker pack
function delete_marker_pack(category_id){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_delete_marker_pack',
			'category_id': category_id
		},
		dataType: "json"
	}).done(function(markers){
		//get updated markers
		get_markers();
		//update select component
		jQuery('#marker_category option:contains("Default")').attr('selected','selected');
	});
}

//load markers for category colour preset
function load_markers_for_colour_preset(category_id,link_id){
	jQuery('#marker_display_holder').empty();
	jQuery('.hero_preset_color').removeClass('hero_preset_active');
	jQuery('#colour_preset_cat_'+ link_id).addClass('hero_preset_active');
	jQuery.each(markers_object.categories[category_id].links[link_id]['markers'], function(key,val){
		if(val.deleted == 0){
			var img_container = '<div class="marker_img_container">';
			img_container += '<img src="data:image/png;base64,'+ val.img_binary +'">';
			img_container += '</div>';
			jQuery('#marker_display_holder').append(img_container);
		}
	});
}