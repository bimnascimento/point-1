//MAPS_SETTINGS VIEW

//view config
var animation_timer = 300; //show/hide animation time in milliseconds
var colour_application_delay = 100;

//view load
jQuery(function(){
	//configure view
	configure_view();
	//bind component change listeners
	bind_component_change_listeners();
	//bind view hide controls
	bind_view_hide_controls();
	//bind button listeners
	bind_button_listeners();
});

//load google font (if required)
function hmapsprem_load_google_font(){
    var font_family = main_object.map_setup.font_family;
    //check font
    if(typeof font_family !== 'undefined' && font_family !== 'inherit'){
        //check if not default font
        if(jQuery.inArray(font_family,hmapsprem_default_fonts) == -1){
            var google_font_var = '<link href="https://fonts.googleapis.com/css?family='+ font_family +'" rel="stylesheet" type="text/css">';
        }
    }
    jQuery('head').append(google_font_var);
}

//configure view
function configure_view(){
	//insert map types and select current type
	jQuery.each(map_config.map_types, function(key,val){
		if(key == main_object.map_settings.map_type){
			jQuery('#map_type').append('<option value="'+ key +'" selected>'+ val.title +'</option>');
		}else{
			jQuery('#map_type').append('<option value="'+ key +'">'+ val.title +'</option>');
		}
	});
	//insert themes and select default value
	var theme_options = [];
	jQuery.each(map_config.map_themes, function(key,val){
		theme_options.push(key);
	});
	theme_options.sort();
	jQuery.each(theme_options, function(key,val){
		if(val == main_object.map_settings.map_theme){
			jQuery('#map_theme').append('<option value="'+ val +'" selected>'+ val +'</option>');
		}else{
			jQuery('#map_theme').append('<option value="'+ val +'">'+ val +'</option>');
		}
	});
	//set default state for theme control
	manage_theme_control();
	//bind map type change listener
	jQuery('#map_type').on('change', function(){
		//manage theme control
		manage_theme_control();
	});
	//set default state for auto fit container
	if(main_object.map_settings.auto_fit == 0){
		jQuery('.show_switch').closest('.hero_section_holder').find('.hide_container').css({
			'height': jQuery(this).children('.internal').height() +'px',
			'overflow': 'visible'
		});
	}
    //set default state for mobile pan lock container
    if(main_object.map_settings.mobile_pan_lock == 1){
        jQuery('.hide_switch').closest('.hero_section_holder').find('.hide_container.mobile_lock_holder').css({
            'height': jQuery(this).children('.internal').height() +'px',
            'overflow': 'visible'
        });
    }
	//set default for category selector container
	if(main_object.map_settings.show_category_selector == 1){
		jQuery('.hide_switch').closest('.hero_section_holder').find('.hide_container.show_category_select_options').css({
			'height': jQuery(this).children('.internal').height() +'px',
			'overflow': 'visible'
		});
	}
	//populate the default display category selector
	populate_default_display_category_selector();
	//select the category display component position
	jQuery('#default_category_component_position').val(main_object.map_settings.default_category_component_position);
	//update example category selector on style change
	jQuery('#category_selector_font_weight, #category_selector_font_size, #category_selector_border_weight, #category_selector_border_radius, #category_selector_vert_padding, #category_selector_hor_padding').on('change', function(){
		setTimeout(function(){
			update_example_category_selector();
		}, colour_application_delay);
	});
	setTimeout(function(){
		jQuery('#category_selector_font_colour').on('change', function(){
			setTimeout(function(){
				update_example_category_selector();
			}, colour_application_delay);
		});
	}, colour_application_delay);
	setTimeout(function(){
		jQuery('#category_selector_fill_colour').on('change', function(){
			setTimeout(function(){
				update_example_category_selector();
			}, colour_application_delay);
		});
	}, colour_application_delay);
	setTimeout(function(){
		jQuery('#category_selector_border_colour').on('change', function(){
			setTimeout(function(){
				update_example_category_selector();
			}, colour_application_delay);
		});
	}, colour_application_delay);
	update_example_category_selector();
    //update example tab category selector on style change
    jQuery('#category_tab_font_size, #category_tab_font_weight, #category_tab_border_bottom_only, #category_tab_border_weight, #category_tab_border_radius, #category_tab_vert_padding, #category_tab_hor_padding, #category_tab_vert_margin, #category_tab_hor_margin').on('change', function(){
        setTimeout(function(){
            update_example_category_tab_selector();
        }, 100);
    });
    setTimeout(function(){
        jQuery('#category_tab_bg_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_bg_active_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_font_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_font_active_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_fill_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_active_fill_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_border_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    setTimeout(function(){
        jQuery('#category_tab_border_active_colour').on('change', function(){
            setTimeout(function(){
                update_example_category_tab_selector();
            }, colour_application_delay);
        });
    }, colour_application_delay);
    update_example_category_tab_selector();
    //category selector select/tabs
    if(main_object.map_settings.category_selector_tabs == "false"){
        var content_height = jQuery('.category_selector_select_content .internal').height();
        //show select content
        jQuery('.category_selector_select_content').css({
            'overflow': 'hidden',
            'height': content_height +'px'
        });
        //hide tab content
        jQuery('.category_selector_tab_content').css({
            'overflow': 'hidden',
            'height': 0 +'px'
        });
    }else{
        var content_height = jQuery('.category_selector_tab_content .internal').height();
        //show tab content
        jQuery('.category_selector_tab_content').css({
            'overflow': 'hidden',
            'height': content_height +'px'
        });
        //hide select content
        jQuery('.category_selector_select_content').css({
            'overflow':'hidden',
            'height':0 + 'px'
        });
    }
}

//update example category selector
function update_example_category_selector(){
	jQuery('.example_category_selector').css({
		'border': main_object.map_settings.category_selector_border_weight +'px solid '+ main_object.map_settings.category_selector_border_colour,
		'-moz-border-radius': main_object.map_settings.category_selector_border_radius +'px',
		'-webkit-border-radius': main_object.map_settings.category_selector_border_radius +'px',
		'border-radius': main_object.map_settings.category_selector_border_radius +'px',
		'padding': main_object.map_settings.category_selector_vert_padding +'px '+ main_object.map_settings.category_selector_hor_padding +'px',
		'background-color': main_object.map_settings.category_selector_fill_colour,
		'color': main_object.map_settings.category_selector_font_colour,
		'font-size': main_object.map_settings.category_selector_font_size,
		'font-weight': main_object.map_settings.category_selector_font_weight,
        'font-family': main_object.map_setup.font_family
	});
}

//update example tab category selector
function update_example_category_tab_selector(){
    //container
    jQuery('.hmapsprem_cat_tab_container').css({
        'background-color': main_object.map_settings.category_tab_bg_colour
    });
    //inactive
    jQuery('.hmapsprem_cat_tab').css({
        'font-size': main_object.map_settings.category_tab_font_size +'px',
        'line-height': main_object.map_settings.category_tab_font_size +'px',
        'font-weight': main_object.map_settings.category_tab_font_weight,
        'padding': main_object.map_settings.category_tab_vert_margin +'px '+ main_object.map_settings.category_tab_hor_margin +'px',
        'margin-right': 0,
        'font-family': main_object.map_setup.font_family
    });
    jQuery('.hmapsprem_cat_tab a').css({
        'color': main_object.map_settings.category_tab_font_colour,
        'background-color': main_object.map_settings.category_tab_fill_colour,
        'padding': main_object.map_settings.category_tab_vert_padding +'px '+ main_object.map_settings.category_tab_hor_padding +'px',
        '-moz-border-radius': main_object.map_settings.category_tab_border_radius +'px',
        '-webkit-border-radius': main_object.map_settings.category_tab_border_radius +'px',
        'border-radius': main_object.map_settings.category_tab_border_radius +'px'
    });
    //active
    jQuery('.hmapsprem_cat_tab.active a').css({
        'color': main_object.map_settings.category_tab_font_active_colour,
        'background-color': main_object.map_settings.category_tab_active_fill_colour
    });
    //border (full/bottom only)
    if(main_object.map_settings.category_tab_border_bottom_only){
        //bottom only
        jQuery('.hmapsprem_cat_tab a').css({
            'border': 'none',
            'border-bottom': main_object.map_settings.category_tab_border_weight +'px solid '+ main_object.map_settings.category_tab_border_colour
        });
        jQuery('.hmapsprem_cat_tab.active a').css({
            'border': 'none',
            'border-bottom': main_object.map_settings.category_tab_border_weight +'px solid '+ main_object.map_settings.category_tab_border_active_colour
        });
    }else{
        //full border
        jQuery('.hmapsprem_cat_tab a').css({
            'border': main_object.map_settings.category_tab_border_weight +'px solid '+ main_object.map_settings.category_tab_border_colour
        });
        jQuery('.hmapsprem_cat_tab.active a').css({
            'border': main_object.map_settings.category_tab_border_weight +'px solid '+ main_object.map_settings.category_tab_border_active_colour
        });
    }
}

//populate default display category selector
function populate_default_display_category_selector(){
    //dropdown selector
	//empty selector
	jQuery('#default_display_category').empty();
	//sort categories
	main_object.map_marker_categories.sort();
	//add "show all"
	var default_sel = "uncategorised";
	if(main_object.map_settings.default_display_category == default_sel){
		jQuery('#default_display_category').append('<option value="show_all" selected>'+ main_object.map_settings.show_category_selector_copy +'</option>');
	}else{
		jQuery('#default_display_category').append('<option value="show_all">'+ main_object.map_settings.show_category_selector_copy +'</option>');
	}
	//populate selector (default to "show all")
	jQuery.each(main_object.map_marker_categories, function(key, val){
		if(val != default_sel){
			if(val == main_object.map_settings.default_display_category){
				jQuery('#default_display_category').append('<option value="'+ val +'" selected>'+ val +'</option>');
			}else{
				jQuery('#default_display_category').append('<option value="'+ val +'">'+ val +'</option>');
			}
		}
	});
	//bind "show_all" category name change
	jQuery('#show_category_selector_copy').on('change', function(){
		jQuery('#default_display_category option[value=show_all]').text(jQuery('#show_category_selector_copy').val());
		update_select_component(jQuery('#default_display_category'));
	});
    //tab selector
    //empty selector
    jQuery('.hmapsprem_cat_tab_container').empty();
    //sort categories
    main_object.map_marker_categories.sort();
    //populate selector //category_tab_default_categories
    if(main_object.map_marker_categories.length > 1){
        jQuery.each(main_object.map_marker_categories, function(key, val){
            if(val != default_sel){
                if(jQuery.inArray(val, main_object.map_settings.category_tab_default_categories) != -1){
                    //active
                    jQuery('.hmapsprem_cat_tab_container').append('<div onclick="manage_active_tab_state(this, \''+ val +'\');" class="hmapsprem_cat_tab active"><a>' + val + '</a></div>');
                }else{
                    //inactive
                    jQuery('.hmapsprem_cat_tab_container').append('<div onclick="manage_active_tab_state(this, \''+ val +'\');" class="hmapsprem_cat_tab"><a>' + val + '</a></div>');
                }
            }
        });
        jQuery('.hmapsprem_cat_tab_container').append('<div style="clear:both;"></div>');
    }else{
        jQuery('.hmapsprem_cat_tab_container').append('<div class="size_12 hero_darkgrey" style="padding:7px 11px;">In order to make use of marker category tabs, please add a category on the "Markers" tab.</div>');
    }
}

//manage active tab state
function manage_active_tab_state(elem, val){
    //check if currently active
    if(jQuery(elem).hasClass('active')){
        //deactivate
        jQuery(elem).removeClass('active');
        var index = main_object.map_settings.category_tab_default_categories.indexOf(val);
        if(index > -1){
            main_object.map_settings.category_tab_default_categories.splice(index, 1);
        }
        update_example_category_tab_selector();
    }else{
        //activate
        jQuery(elem).addClass('active');
        main_object.map_settings.category_tab_default_categories.push(val);
        update_example_category_tab_selector();
    }
    //sort array
    main_object.map_settings.category_tab_default_categories.sort();
    //flag save
    flag_save_required('hplugin_persist_object_data');
}

//manage theme control
function manage_theme_control(){
	//lookup in map_config
	var show;
	jQuery.each(map_config.map_types, function(key,val){
		if(key == jQuery('#map_type').val()){
			show = val.show_theme;
			return false;
		}
	});
	//get container
	var container = jQuery('.map_theme_container');
	//manage display
	if(show){
		container.stop().animate({
			'height': container.children('.internal').height() +'px'
		},animation_timer, function(){
			container.css({
				'overflow': 'visible'
			})
		});
	}else{
		var container = jQuery('.map_theme_container');
		container.css({'overflow': 'hidden'}).stop().animate({
			'height': 0
		},animation_timer);
	}
}

//bind component change listeners
function bind_component_change_listeners(){
	//map type
	jQuery('#map_type').off().on('change', function(){
		//update map type
		eval("google_map.setMapTypeId(google.maps.MapTypeId."+ jQuery(this).val() +");");
		//manage theme control
		manage_theme_control();
	});
	//map theme
	jQuery('#map_theme').off().on('change', function(){
		//manage map theme
		setTimeout(function(){
			manage_map_theme();
		}, 100);
	});
    //marker category dropdown selector
    jQuery('#category_selector_select, #category_selector_tabs').on('change.hmapselector', function(){
        if(jQuery('#category_selector_select').is(':checked')){
            //get category select content height
            var content_height = jQuery('.category_selector_select_content .internal').height();
            //show select content
            jQuery('.category_selector_select_content').css({'overflow': 'hidden'}).stop().animate({
                'height': content_height +'px'
            }, 300);
            //hide tab content
            jQuery('.category_selector_tab_content').css({'overflow': 'hidden'}).stop().animate({
                'height': 0 +'px'
            }, 300);
        }else{
            //get category select content height
            var content_height = jQuery('.category_selector_tab_content .internal').height();
            //show select content
            jQuery('.category_selector_select_content').css({'overflow': 'hidden'}).stop().animate({
                'height': 0 +'px'
            }, 300);
            //hide tab content
            jQuery('.category_selector_tab_content').css({'overflow': 'hidden'}).stop().animate({
                'height': content_height +'px'
            }, 300);
        }
    });
}

//bind view/hide controls
function bind_view_hide_controls(){
	jQuery('.show_switch').on('change', function(){
		var checked = jQuery(this).is(':checked');
		var container = jQuery(this).closest('.hero_section_holder').find('.hide_container');
		if(!checked){
			container.stop().animate({
				'height': container.children('.internal').height() +'px'
			},animation_timer, function(){
				container.css({
					'overflow': 'visible'
				});
			});
		}else{
			container.css({'overflow': 'hidden'}).stop().animate({
				'height': 0
			},animation_timer);
			//update map object
			jQuery('#map_center').val('0,0').trigger('change');
		}
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
	//bind view/hide controls
	jQuery('.hide_switch').on('change', function(){
		var checked = jQuery(this).is(':checked');
		var container = jQuery(this).closest('.hero_section_holder').find('.hide_container');
		if(checked){
			container.stop().animate({
				'height': container.children('.internal').height() +'px'
			},animation_timer, function(){
				container.css({
					'overflow': 'visible'
				});
                configure_view();
			});
		}else{
			container.css({'overflow': 'hidden'}).stop().animate({
				'height': 0
			},animation_timer);
		}
	});
}

//bind button listeners
function bind_button_listeners(){
	jQuery('#get_map_center_zoom_btn').off().on('click', function(){
		//get map center
		var latlon = google_map.getCenter();
		var lat = latlon.lat();
		var lon = latlon.lng();
		main_object.map_settings.map_center = lat +','+ lon;
		jQuery('#map_center').val(main_object.map_settings.map_center);
		//get map zoom
		var cur_zoom = google_map.getZoom();
		main_object.map_settings.rest_zoom = cur_zoom;
		jQuery('#rest_zoom').val(main_object.map_settings.rest_zoom);
		//flag save
		flag_save_required('hplugin_persist_object_data');
	});
}