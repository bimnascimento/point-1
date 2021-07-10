//MAPS_SETUP VIEW

//view globals
var hmapsprem_font_load;

//view load
jQuery(function(){
	//define shortcode
	jQuery('#shortcode').val('[hmapsprem id='+ object_id +']');
	//set map_width initial value based on responsive switch
	if(main_object.map_setup.responsive == "true"){
		jQuery('#map_width').removeClass('hero_px').addClass('hero_perc').attr('readonly',true);
	}else{
		jQuery('#map_width').removeClass('hero_perc').addClass('hero_px');
	}
	//bind responsive switch listener
	bind_responsive_switch_listener();
    //get google fonts
    hmapsprem_get_global_fonts();
});

//bind responsive switch listener
function bind_responsive_switch_listener(){
	jQuery('.responsive_switch').on('change', function(){
		//manage responsive switch
		manage_responsive_switch();
	});
}

//manage responsive switch
function manage_responsive_switch(){
	if(jQuery('#responsive').is(':checked')){
		//set map width to percentage (100%)
		jQuery('#map_width').removeClass('hero_px').addClass('hero_perc').val(100).attr('readonly',true).trigger('change');
	}else{
		//set map width to px (default to default_map_width)
		jQuery('#map_width').removeClass('hero_perc').addClass('hero_px').val(default_map_width).removeAttr('readonly').trigger('change');
	}
}

//get global fonts
function hmapsprem_get_global_fonts(){
    if(typeof hmapsprem_google_fonts !== 'undefined'){
        populate_fonts_dropdown();
    }else{
        clearTimeout(hmapsprem_font_load);
        hmapsprem_font_load = setTimeout(function(){
            hmapsprem_get_global_fonts();
        }, 100);
    }
}

//populate fonts dropdown
function populate_fonts_dropdown(){
    //empty dropdown
    jQuery('#font_family').empty();
    var cur_val = eval("main_object.map_setup.font_family");
    jQuery.each(hmapsprem_google_fonts, function(key,val){
        var sel = '';
        if(cur_val == val){
            sel = 'selected="selected"';
        }
        jQuery('#font_family').append('<option value="'+ val +'" '+ sel +'>'+ val +'</option>');
    });
}