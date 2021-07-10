//MAPS_MARKERS VIEW

//view globals
var map_markers_object_load_timer_categories;
var current_marker_page = 1;

//view load
jQuery(function(){
	//extract categories
	extract_marker_categories();
	//set map holder droppable
	set_map_droppable();
	//manage category selector
	manage_category_selector();
	//make marker mass update droppable
	set_mass_marker_update_droppable();
});

//extract categories
function extract_marker_categories(){
	if(typeof map_markers_object === 'undefined'){
		clearTimeout(map_markers_object_load_timer_categories);
		map_markers_object_load_timer_categories = setTimeout("extract_marker_categories();",100);
	}else{
		clearTimeout(map_markers_object_load_timer_categories);
		//loop through markers
		jQuery('#map_marker_category').empty();
		jQuery.each(map_markers_object.categories, function(key,val){
			//check if still active
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
					jQuery('#map_marker_category').append('<option value="'+ val.category_id +'">'+ val.category +'</option>');
				}
			}
		});
		//enable components
		switch_components();
		//bind marker pack change listner
		bind_map_marker_pack_change_listener();
		//load presets for category
		load_presets_for_map_category();
	}
}

//bind marker pack change listner
function bind_map_marker_pack_change_listener(){
	jQuery('#map_marker_category').on('change', function(){
		load_presets_for_map_category();
	});
}

//load colour presets for category
function load_presets_for_map_category(){
	//get marker category
	var category_id = parseInt(jQuery("#map_marker_category option").filter(":selected").val());
	//empty container
	jQuery('.hero_preset_holder').empty();
	//remove notice
	jQuery('.pack_notice_copy').remove();
	//populate container
	var check_count = 0;
	jQuery.each(map_markers_object.categories[category_id].links, function(key,val){
		if(check_count == 0){
			flid = val.link;
		}
		var preset_html = '<div id="colour_preset_cat_'+ val.link +'" class="hero_preset_color rounded_20" onclick="load_map_markers_for_colour_preset('+ category_id +', '+ val.link +');">';
		preset_html += '<div class="hero_preset_one rounded_left_20" style="background-color: rgb('+ hexToRgb(val.primary_colour) +');"></div>';
		preset_html += '<div class="hero_preset_two rounded_right_20" style="background-color: rgb('+ hexToRgb(val.secondary_colour) +');"></div>';
		preset_html += '</div>';
		jQuery('.hero_preset_holder').append(preset_html);
		check_count++;
	});
	//load markers for category colour preset
	load_map_markers_for_colour_preset(category_id,flid);
	//check for Custom pack
	if(map_markers_object.categories[category_id].category == 'Custom'){
		//change opacity
		jQuery('.hero_preset_color').css({
			'opacity': 0.1
		}).parent().parent().append('<div class="size_12 pack_notice_copy" style="float:left; margin:9px 0 0 15px;">Color schemes are not supported for custom markers</div>');
	}
	//check for imported pack
	if(map_markers_object.categories[category_id].imported == 1){
		//change opacity
		jQuery('.hero_preset_color').css({
			'opacity': 0.1
		}).parent().parent().append('<div class="size_12 pack_notice_copy" style="float:left; margin:9px 0 0 15px;">Color schemes are not supported for custom marker packs</div>');
	}
}

//load markers for category colour preset
function load_map_markers_for_colour_preset(category_id,link_id){
	jQuery('#marker_display_holder').empty();
	jQuery('.hero_preset_color').removeClass('hero_preset_active');
	jQuery('#colour_preset_cat_'+ link_id).addClass('hero_preset_active');
	jQuery.each(map_markers_object.categories[category_id].links[link_id]['markers'], function(key,val){
		if(val.deleted == 0){
			var width_resize_ratio = (30 / val.width);
			var new_height = parseInt(val.height * width_resize_ratio);
			var img_container = '<div class="marker_img_container" style="width:40px; height:'+ (new_height + 9) +'px">';
					img_container += '<img style="z-index:99999;" data-colour="'+ map_markers_object.categories[category_id].links[link_id].primary_colour +'" data-leftoffset="'+ val.left_offset +'" data-topoffset="'+ val.top_offset +'" data-width="'+ val.width +'" data-height="'+ val.height +'" data-id="'+ val.marker_id +'" id="marker_'+ val.marker_id +'" src="data:image/png;base64,'+ val.img_binary +'">';
				img_container += '</div>';
			jQuery('#marker_display_holder').append(img_container);
		}
	});
	//set location markers draggable
	set_markers_draggable();
}

//set map holder droppable
function set_map_droppable(){
	jQuery('#hero_map_main').droppable({
		tolerance: 'fit',
		drop: function(event, ui){
			var marker = jQuery('#'+ ui.draggable.prop('id'));
			var width_resize_ratio = (30 / marker.data('width'));
			var new_height = parseInt(marker.data('height') * width_resize_ratio);
			marker.stop().animate({
				'width': 30 +'px',
				'height': new_height +'px'
			},200);
			//get cursor position
			var offset = jQuery(this).offset();
            var x = event.pageX - offset.left;
            var y = event.pageY - offset.top;
			var point = new google.maps.Point(x,y);
			var latlng = map_overlay.getProjection().fromContainerPixelToLatLng(point);
			//place marker
			place_map_marker(ui.draggable.prop('id'), marker.attr('src'), latlng);
		}
    });
}
//disable map droppable
function disable_map_droppable(){
	jQuery('#hero_map_main').droppable('disable');
}
//enable map droppable
var reset_map_drop_timer;
function enable_map_droppable(){
	clearTimeout(reset_map_drop_timer);
	reset_map_drop_timer = setTimeout(function(){
		jQuery('#hero_map_main').droppable('enable');
	}, 100);
}

//update marker image
function update_marker_image(new_marker_data, marker_src){
	//remove current
	currently_editing_marker_object.gmp.setMap(null);
	currently_editing_marker_object.gmp = null;
	//update marker id
	currently_editing_marker_object.marker_id = new_marker_data.marker_id;
	//update the image on the map
	var width = new_marker_data.width;
	var height = new_marker_data.height;
	var top_offset = new_marker_data.top_offset;
	var left_offset = new_marker_data.left_offset;
	var latlng_object = currently_editing_marker_object.latlng.split(',');
	var latlng = new google.maps.LatLng(latlng_object[0] , latlng_object[1]);
	/*
		note:
		possibly consider updating the link colour to match the new marker image
	*/
	//create new marker
	var icon_object = {
		url: marker_src,
		size: new google.maps.Size(width, height),
		origin: new google.maps.Point(0,0),
		anchor: new google.maps.Point(left_offset, top_offset)
	};
	var new_map_marker = new google.maps.Marker({
		position: latlng,
		draggable: true,
		raiseOnDrag: true,
		icon: icon_object,
		map: google_map
	});
	//update current icon object gmp
	currently_editing_marker_object.gmp = new_map_marker;
	//bind marker listeners
	bind_marker_listeners(new_map_marker,currently_editing_icon_id);
	//update display image
	jQuery('.marker_img_holder .marker_img').css({
		'background-image': 'url('+ marker_src +')'
	});
	//flag save
	flag_save_required('hplugin_persist_object_data');
}

//set location markers draggable
function set_markers_draggable(){
	jQuery('.marker_img_container').each(function(key,val){
		var img = jQuery(this).children('img');
		var left_offset = img.data('leftoffset');
		var top_offset = img.data('topoffset');
		img.draggable({
			revert: true,
			cursorAt: {
				left: left_offset,
				top: (top_offset + 5)
			},
			start: function(){
				var marker = jQuery(this);
				var width = marker.data('width');
				var height = marker.data('height');
				marker.stop().animate({
					'width': width +'px',
					'height': height +'px'
				},200);
			},
			stop: function(){
				var marker = jQuery(this);
				var width_resize_ratio = (30 / marker.data('width'));
				var new_height = parseInt(marker.data('height') * width_resize_ratio);
				marker.stop().animate({
					'width': 30 +'px',
					'height': new_height +'px'
				},200);
			}
		});
	});
}

//place map marker
function place_map_marker(id,marker_src,latlng){
	//place marker on map
	var marker = jQuery('#'+ id);
	var width = marker.data('width');
	var height = marker.data('height');
	var top_offset = marker.data('topoffset');
	var left_offset = marker.data('leftoffset');
	var marker_id = marker.data('id');
	var colour = marker.data('colour');
	var icon_object = {
		url: marker_src,
		size: new google.maps.Size(width, height),
		origin: new google.maps.Point(0,0),
		anchor: new google.maps.Point(left_offset, top_offset)
	};
	var map_marker = new google.maps.Marker({
		position: latlng,
		draggable: true,
		raiseOnDrag: true,
		icon: icon_object,
		map: google_map,
		zIndex: 2
	});
	//add marker to map_object
	var marker_object = {
		"marker_id": marker_id,
		"marker_category": "uncategorised",
		"latlng": latlng.lat() +','+ latlng.lng(),
		"title": "",
		"info_window_show": false,
		"info_window_content": "",
		"new": true,
		"gmp": map_marker, //used as pointer to marker icon on map
		"link_show": false,
		"link_title": "",
		"link": "",
		"link_colour": colour,
		"link_target": "_blank",
		"custom_param": "",
		"deleted": false
	};
	//generate random string as id
	var icon_id = grs();
	//add the marker_object to the map_object
	main_object.map_markers[icon_id] = marker_object;
	//bind marker listeners
	bind_marker_listeners(map_marker,icon_id);
	//flag save
	flag_save_required('hplugin_persist_object_data');
	//update markers table
	populate_markers_table(jQuery('#marker_table_category_selector').val());
	//remove search
	if(typeof map_search_marker != 'undefined'){
		map_search_marker.setMap(null);
		map_search_marker = undefined;
	}
}

//manage category selector
function manage_category_selector(){
	//empty selector
	jQuery('#marker_table_category_selector').empty();
	//sort categories
	main_object.map_marker_categories.sort();
	//populate selector (default to "uncategorised")
	var default_sel = "uncategorised";
	jQuery.each(main_object.map_marker_categories, function(key, val){
		if(val == default_sel){
			jQuery('#marker_table_category_selector').append('<option value="'+ val +'" selected>'+ val +'</option>');
		}else{
			jQuery('#marker_table_category_selector').append('<option value="'+ val +'">'+ val +'</option>');
		}
	});
	update_select_component(jQuery('#marker_table_category_selector'));
	//bind category selector
	jQuery('#marker_table_category_selector').off().on('change', function(){
		populate_markers_table(jQuery(this).val());
		//hide marker mass update panel
		hide_marker_mass_update_panel();
	});
	//load default category markers
	populate_markers_table(default_sel);
}

//populate markers table
function populate_markers_table(cat){
	//empty table
	jQuery('#category_content_holder').empty();
	//add markers (based on selected category)
	var found = false;
	var marker_count = 1;
	var items_per_page = jQuery('.hero_items_per_page').val();
	var current_page = current_marker_page;
	if(typeof current_page == 'undefined'){
		current_page = 1;
	}
	var start_display = (((current_page * items_per_page) - items_per_page) + 1);
	var end_display = (current_page * items_per_page);
	//update display text
	jQuery.each(main_object.map_markers, function(key,val){
		if(val.marker_category == cat){
			found = true;
			if(marker_count >= start_display && marker_count <= end_display){
				//generate marker data html
				if(val.marker_id != null){
					var img_binary = get_marker_data_from_object(val.marker_id).img_binary;
				}else{
					var img_binary = map_config.default_img.binary
				}
				var marker_data = get_marker_data_from_object(val.marker_id);
				var marker_data_html = '<div class="hero_col_12" id="marker_data_table_' + key + '">';
				marker_data_html += '<div class="hero_col_1"><div class="marker_data_table_img_container"><div class="marker_data_table_img" style="background-image:url(data:image/png;base64,' + img_binary + ');"></div></div></div>';
				marker_data_html += '<div class="hero_col_4" class="table_latlng"><span>' + val.latlng + '</span></div>';
				marker_data_html += '<div class="hero_col_3"><span>' + val.title + '</span></div>';
				marker_data_html += '<div class="hero_col_2"><span>' + val.marker_category + '</span></div>';
				marker_data_html += '<div class="hero_col_2">';
				marker_data_html += '<div class="hero_edits rounded_20">';
				marker_data_html += '<div data-tooltip="Edit Marker" class="hero_edit_item" onclick="launch_marker_editor_from_table(\'' + key + '\');" style="background-image:url(' + plugin_url + '/assets/images/admin/edit_icon.png)"></div>';
				marker_data_html += '<div data-tooltip="Delete Marker" class="hero_edit_item" onclick="remove_marker_from_table(\'' + key + '\');" style="background-image:url(' + plugin_url + '/assets/images/admin/delete_icon.png)"></div>';
				marker_data_html += '</div>';
				marker_data_html += '</div>';
				marker_data_html += '</div>';
				//insert marker data
				jQuery('#category_content_holder').append(marker_data_html);
			}
			marker_count++;
		}
	});
	marker_count--;
	//calculate number of pages
	var num_pages_total = Math.ceil(marker_count / items_per_page);
	//check if markers available
	if(current_marker_page > num_pages_total){
		current_marker_page--;
		populate_markers_table(cat);
	}
    if(current_marker_page == 0 && num_pages_total > 0){
        current_marker_page = 1;
        populate_markers_table(cat);
    }
	//update page display copy
	jQuery('.hero_paging_holder span').html('page '+ current_marker_page +' of '+ num_pages_total);
	//check if markers found
	if(!found){
        jQuery('#no_data_message').remove();
		var not_found_html = '<div class="hero_col_12" id="no_data_message"><span>There are no markers in this category. To add a marker to this category, click on a location marker on the map above and select this marker category from the drop-down provided.</span></div>';
		jQuery('#category_content_holder').append(not_found_html);
	}
	//manage paging display
	if(current_marker_page <= 1){
		jQuery('.hero_paging_prev').css('visibility','hidden').off();
	}else{
		//bind paging buttons
		jQuery('.hero_paging_prev').css('visibility','visible').off().on('click', function(){
			current_marker_page--;
			populate_markers_table(cat);
		});
	}
	if(current_marker_page == num_pages_total){
		jQuery('.hero_paging_next').css('visibility','hidden').off();
	}else{
		//bind paging buttons
		jQuery('.hero_paging_next').css('visibility','visible').off().on('click', function(){
			current_marker_page++;
			populate_markers_table(cat);
		});
	}
	jQuery('.hero_items_per_page').off('change.paging').on('change.paging', function(){
		jQuery('.hero_items_per_page').val(jQuery(this).val());
		update_select_component(jQuery('#hero_items_per_page1'));
		update_select_component(jQuery('#hero_items_per_page2'));
		populate_markers_table(cat);
	});
}

//launch marker editor from table
function launch_marker_editor_from_table(icon_id){
	//populate edit panel
	populate_edit_panel(main_object.map_markers[icon_id], icon_id);
	//show edit panel
	show_marker_edit_panel();
}

//remove marker from table
function remove_marker_from_table(icon_id){
	if(window.confirm('Are you sure you want to delete this marker?')){
		//mark marker deleted
		remove_location_marker_from_object(main_object.map_markers[icon_id], icon_id);
		//hide panel
		hide_marker_edit_panel();
		//hide arrows
		hide_arrows();
		//hide tooltip
		hide_hplugin_tooltip();
		//disable marker icon change
		disable_marker_icon_change();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	}
}

//populate categories table
function populate_categories_table(){
	//update popup buttons (override)
	jQuery('.hero_popup_update_btn').remove();
	jQuery('.hero_popup_cancel_btn').html('Close');
	//hide marker edit panel
	hide_marker_edit_panel();
	//hide arrows
	hide_arrows();
	//empty table
	jQuery('#current_marker_categories_holder').empty();
	//sort categories
	main_object.map_marker_categories.sort();
	//add categories
	jQuery.each(main_object.map_marker_categories, function(key,val){
		//generate category data html
		var marker_category_html  = '<div class="hero_col_12" id="category_row_item_'+ key +'">';
				marker_category_html += '<div class="hero_col_7"><span>'+ val +'</span></div>';
				//count number of markers in category
				var mcount = 0;
				jQuery.each(main_object.map_markers, function(key1,val1){
					if(val1.marker_category == val){
						mcount++;
					}
				});
				marker_category_html += '<div class="hero_col_3">';
					marker_category_html += '<div class="hero_count_holder rounded_20">';
						marker_category_html += '<div class="hero_count_item"><div class="hero_count">'+ mcount +'</div></div>';
					marker_category_html += '</div>';
				marker_category_html += '</div>';
				marker_category_html += '<div class="hero_col_2">';
					if(val != 'uncategorised'){
						marker_category_html += '<div class="hero_edits rounded_20">';
							marker_category_html += '<div data-tooltip="Rename Category" class="hero_edit_item" onclick="rename_category_option('+ key +',\''+ val +'\');" style="background-image:url('+ plugin_url +'/assets/images/admin/rename_icon.png)"></div>';
							marker_category_html += '<div data-tooltip="Delete Category" class="hero_edit_item" onclick="request_category_delete(\''+ val +'\');" style="background-image:url('+ plugin_url +'/assets/images/admin/delete_icon.png)"></div>';
						marker_category_html += '</div>';
					}
				marker_category_html += '</div>';
			marker_category_html += '</div>';
		//insert marker data
		jQuery('#current_marker_categories_holder').append(marker_category_html);
	});
}

//rename category
function rename_category_option(key, val){
	//close all tooltips
	hide_hplugin_tooltip();
	//hide and remove other rename objects
	jQuery('.rename_object').stop().animate({
		'margin-left': '-'+ 100 +'%'
	},300, function(){
		jQuery(this).remove();
	});
	//get dashboard item attributes
	var item_height = jQuery('#category_row_item_'+ key).height();
	var item_padding_top = parseInt(jQuery('#category_row_item_'+ key).css('padding-top'));
	var item_padding_bottom = parseInt(jQuery('#category_row_item_'+ key).css('padding-top'));
	var item_total_height = (item_height + item_padding_top + item_padding_bottom);
	//construct rename html
	var rename_html  = '<div class="rename_object" id="rename_object_'+ key +'" style="height:'+ item_height +'px; margin-top:-'+ item_padding_top +'px; padding-top:'+ item_padding_top +'px; padding-bottom:'+ item_padding_bottom +'px;">';
			rename_html += '<div class="hero_col_4" style="padding-right:20px;"><span><input id="hero_rename_'+ key +'" class="hero_rename" type="text" value="'+ val +'"></span></div>';
			rename_html += '<div class="hero_col_4"><div class="hero_button_sml_dash_table darkgrey_button rounded_3" onclick="rename_category('+ key +',\''+ val +'\');");">SAVE</div></div>';
		rename_html += '</div>';
	//insert rename html after object
	jQuery('#category_row_item_'+ key).append(rename_html);
	//bind keyboard press "enter button"
	jQuery('#rename_object_'+ key).on('keypress', function(e){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == 13){
			rename_category(key,val);
		}
	});
	//animate
	jQuery('#rename_object_'+ key).stop().animate({
		'margin-left': 0
	},300);
	//set focus on the rename input
	jQuery('#hero_rename_'+ key).focus().select();
}

//rename category
function rename_category(key, val){
	//get updated value
	var updated_value = jQuery('#hero_rename_'+ key).val();
	//update object


	//remove old category
	main_object.map_marker_categories.splice(jQuery.inArray(val, main_object.map_marker_categories),1);
	//add new category
	main_object.map_marker_categories.push(updated_value);
	//move all markers in category to update category
	jQuery.each(main_object.map_markers, function(idx,value){
		if(value.marker_category == val){
			value.marker_category = updated_value;
		}
	});
	//populate categories table
	populate_categories_table();
	//check if deleted category was set as default display category
	if(val == main_object.map_settings.default_display_category){
		//update default to show all
		main_object.map_settings.default_display_category = "show_all";
	}
	//update markers table
	manage_category_selector();
	populate_markers_table(jQuery('#marker_table_category_selector').val());
	//hide tooltip
	hide_hplugin_tooltip();
	//flag save
	flag_save_required('hplugin_persist_object_data');
	//hide and remove other rename objects
	jQuery('.rename_object').stop().animate({
		'margin-left': '-'+ 100 +'%'
	},300, function(){
		jQuery(this).remove();
	});
}

//request category delete
function request_category_delete(cat){
	if(window.confirm('Are you sure you want to delete this category? All markers currently in this category will be moved to "uncategorised".')){
		//remove category
		main_object.map_marker_categories.splice(jQuery.inArray(cat, main_object.map_marker_categories),1);
		//move all markers in category to "uncategorised"
		jQuery.each(main_object.map_markers, function(key,val){
			if(val.marker_category == cat){
				val.marker_category = "uncategorised";
			}
		});
		//populate categories table
		populate_categories_table();
		//check if deleted category was set as default display category
		if(cat == main_object.map_settings.default_display_category){
			//update default to show all
			main_object.map_settings.default_display_category = "show_all";
		}
		//update markers table
		manage_category_selector();
		populate_markers_table(jQuery('#marker_table_category_selector').val());
		//hide tooltip
		hide_hplugin_tooltip();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	}
}

//add marker category
function add_marker_category(){
	//get category value
	var cat_val = jQuery('#new_marker_category').val();
	//check value
	if(cat_val.length > 0){
		//add category
		main_object.map_marker_categories.push(cat_val);
		//clear field
		jQuery('#new_marker_category').val('');
		//update markers table
		manage_category_selector();
		//update table
		populate_categories_table();
		//flag save
		flag_save_required('hplugin_persist_object_data');
	}
}

//show marker mass update panel
function show_marker_mass_update_panel(){
	//hide marker edit panel
	hide_marker_edit_panel();
	//hide arrows
	hide_arrows();
	//show panel
	jQuery('.mass_marker_update_panel').stop().animate({
		'height': (jQuery('.mass_marker_update_panel_inner').height() + 1) +'px' //add 1 for border
	}, 500);
}

//hide marker mass update panel
function hide_marker_mass_update_panel(){
	jQuery('.mass_marker_update_panel').stop().animate({
		'height': 0 +'px'
	}, 500);
}


//make marker mass update droppable
function set_mass_marker_update_droppable(){
	jQuery('.mass_marker_update_drop').droppable({
		tolerance: 'fit',
		over: function(event, ui){
			//set indication border
			jQuery('.mass_marker_update_panel .mass_marker_update_panel_inner .mass_marker_update_drop_holder .mass_marker_update_drop').css({
				'border': '4px dashed #C6302A'
			});
		},
		out: function(event, ui){
			//reset indication border
			jQuery('.mass_marker_update_panel .mass_marker_update_panel_inner .mass_marker_update_drop_holder .mass_marker_update_drop').css({
				'border': '4px dashed #CCC'
			});
		},
		drop: function(event, ui){
			var marker = jQuery('#'+ ui.draggable.prop('id'));
			var width_resize_ratio = (30 / marker.data('width'));
			var new_height = parseInt(marker.data('height') * width_resize_ratio);
			marker.stop().animate({
				'width': 30 +'px',
				'height': new_height +'px'
			},200);
			//reset indication border
			jQuery('.mass_marker_update_panel .mass_marker_update_panel_inner .mass_marker_update_drop_holder .mass_marker_update_drop').css({
				'border': '4px dashed #CCC'
			});
			//get marker data id
			var marker_data_id = marker.data('id');
			//get marker data
			var new_marker_data = get_marker_data_from_object(marker_data_id);
			//update marker image
			update_mass_marker_image(new_marker_data, marker.attr('src'));
		}
    });
}


//update marker image
function update_mass_marker_image(new_marker_data, marker_src){
	//get current marker category
	var cur_cat = jQuery('#marker_table_category_selector').val();
	jQuery.each(main_object.map_markers, function(key,val){
		if(val.marker_category == cur_cat){
			//remove current
			val.gmp.setMap(null);
			val.gmp = null;
			//update marker id
			val.marker_id = new_marker_data.marker_id;
			//update the image on the map
			var width = new_marker_data.width;
			var height = new_marker_data.height;
			var top_offset = new_marker_data.top_offset;
			var left_offset = new_marker_data.left_offset;
			var latlng_object = val.latlng.split(',');
			var latlng = new google.maps.LatLng(latlng_object[0] , latlng_object[1]);
			/*
				note:
				possibly consider updating the link colour to match the new marker image
			*/
			//create new marker
			var icon_object = {
				url: marker_src,
				size: new google.maps.Size(width, height),
				origin: new google.maps.Point(0,0),
				anchor: new google.maps.Point(left_offset, top_offset)
			};
			var new_map_marker = new google.maps.Marker({
				position: latlng,
				draggable: true,
				raiseOnDrag: true,
				icon: icon_object,
				map: google_map
			});
			//update current icon object gmp
			val.gmp = new_map_marker;
			//bind marker listeners
			bind_marker_listeners(new_map_marker,key);
			//update display image
			jQuery('.marker_img_holder .marker_img').css({
				'background-image': 'url('+ marker_src +')'
			});
			//flag save
			flag_save_required('hplugin_persist_object_data');
		}
	});
	//update table
	populate_markers_table(jQuery('#marker_table_category_selector').val());
	//hide marker mass update panel
	hide_marker_mass_update_panel();
}