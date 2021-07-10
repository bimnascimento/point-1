//DASHBOARD VIEW CORE

//load
jQuery(function(){
	//load maps into sidebar
	hplugin_get_object_list('populate_hplugin_dashboard_table');
	//populate dashboard containers
	populate_dashboard_containers();
	//set dashboard table name
	jQuery('.dashboard_table_name').html(dropdown_object_name_plural);
	//reset map markers object
	if(typeof map_markers_object !== 'undefined'){
		map_markers_object = undefined;
	}
	
});

//populate dashboard containers
function populate_dashboard_containers(){
	jQuery('#plugin_version').html(plugin_version);
	jQuery('#plugin_last_update').html(plugin_last_updated);
	jQuery('#plugin_release_date').html(plugin_first_release);
}

//populate dashboard table
function populate_hplugin_dashboard_table(object){
	jQuery('#dashboard_object_holder').empty();
	if(object && object.length > 0){
		jQuery.each(object, function(key,val){
			var row_html = '<div class="hero_col_12" id="dashboard_item_'+ val.object_id +'" style="overflow:hidden;">';
					row_html += '<div class="hero_col_4" style="cursor:pointer;" data-json="'+ encodeURIComponent(JSON.stringify({"object_id": val.object_id})) +'" onclick="load_sidebar_dropdown_view(jQuery(this),\'dropdown_default\',\'load_default_object\');"><span>'+ val.object_name +'</span></div>';
					row_html += '<div class="hero_col_4"><span><input class="hero_ctc" style="width:100%;" onclick="jQuery(this).select();" type="text" value="['+ plugin_name +' id='+ val.object_id +']" readonly></span></div>';
					row_html += '<div class="hero_col_4">';
						row_html += '<div class="hero_edits rounded_20">';
							row_html += '<div data-tooltip="Rename '+ dropdown_object_name_singular +'" class="hero_edit_item" onclick="rename_object_request('+ val.object_id +',\''+ val.object_name +'\');" style="background-image:url('+ plugin_url +'/assets/images/admin/rename_icon.png)"></div>';
							row_html += '<div data-tooltip="Edit '+ dropdown_object_name_singular +'" class="hero_edit_item" data-json="'+ encodeURIComponent(JSON.stringify({"object_id": val.object_id})) +'" onclick="load_sidebar_dropdown_view(jQuery(this),\'dropdown_default\',\'load_default_object\');" style="background-image:url('+ plugin_url +'/assets/images/admin/edit_icon.png)"></div>';
							row_html += '<div data-tooltip="Duplicate '+ dropdown_object_name_singular +'" class="hero_edit_item" onclick="duplicate_object_request('+ val.object_id +');" style="background-image:url('+ plugin_url +'/assets/images/admin/duplicate_icon.png)"></div>';
							row_html += '<div data-tooltip="Export '+ dropdown_object_name_singular +'" class="hero_edit_item" onclick="request_export_map_object('+ val.object_id +');" style="background-image:url('+ plugin_url +'/assets/images/admin/save_icon.png)"></div>';
							row_html += '<div data-tooltip="Delete '+ dropdown_object_name_singular +'" class="hero_edit_item" onclick="delete_object_request('+ val.object_id +');" style="background-image:url('+ plugin_url +'/assets/images/admin/delete_icon.png)"></div>';
						row_html += '</div>';
					row_html += '</div>';
				row_html += '</div>';
			jQuery('#dashboard_object_holder').append(row_html);
		});
		//trigger tooltips
		activate_hplugin_tooltips();
	}else{
		var row_html = '<div class="hero_col_12" id="no_data_message"><span>To add your first '+ dropdown_object_name_singular +', click on "'+ dropdown_object_name_plural +'" in the sidebar and select "Add New".</span></div>';
		jQuery('#dashboard_object_holder').append(row_html);
	}
}

//request export map object
function request_export_map_object(map_id){
	//get object export
	hplugin_get_object_export(map_id, 'export_map_object');
}

//export map object
function export_map_object(object){
	//generate file content
	var currentdate = new Date(); 
	var day = String(currentdate.getDate()).length === 1 ? '0'+ currentdate.getDate() : currentdate.getDate();
	var month = String((currentdate.getMonth()+1)).length === 1 ? '0'+ (currentdate.getMonth()+1) : (currentdate.getMonth()+1);
	var hours = String(currentdate.getHours()).length === 1 ? '0'+ currentdate.getHours() : currentdate.getHours();
	var minutes = String(currentdate.getMinutes()).length === 1 ? '0'+ currentdate.getMinutes() : currentdate.getMinutes();
	var seconds = String(currentdate.getSeconds()).length === 1 ? '0'+ currentdate.getSeconds() : currentdate.getSeconds();
	var datetime =  ''+ day + month + currentdate.getFullYear() +'_'+ hours + minutes + seconds;
	//set iframe src
	jQuery('#map_download_iframe_form').attr('action', plugin_url +'inc/object_export.php');
	//add value to form
	jQuery('#map_download_file').val(JSON.stringify(object));
	jQuery('#map_download_file_name').val('hmapsprem_'+ object.object_name +'_'+ datetime +'.txt');
	//submit form
	jQuery('#map_download_iframe_form').submit();	
}

//delete object request
function delete_object_request(object_id){
	if(window.confirm('Are you sure you want to delete this '+ dropdown_object_name_singular.toLowerCase() +'?')){
		//flag as first load to avoid expanding the object dropdown in the sidebar
		first_load = true;
		//delete map
		hplugin_delete_object(object_id,'update_hplugin_dashboard_data');
	}
}

//delete object
function update_hplugin_dashboard_data(){
	hplugin_get_object_list('populate_hplugin_dashboard_table');
	hplugin_get_object_list('load_objects_into_sidebar');
}

//duplicate object request
function duplicate_object_request(object_id){
	if(window.confirm('Are you sure you want to duplicate this '+ dropdown_object_name_singular.toLowerCase() +'?')){
		//flag as first load to avoid expanding the object dropdown in the sidebar
		first_load = true;
		//delete map
		hplugin_duplicate_object(object_id,'update_hplugin_dashboard_data');
	}
}

//rename object request
function rename_object_request(object_id, object_name){
	//close all tooltips
	hide_hplugin_tooltip();
	//hide and remove other rename objects
	jQuery('.rename_object').stop().animate({
		'margin-left': '-'+ 100 +'%'
	},300, function(){
		jQuery(this).remove();
	});
	//get dashboard item attributes
	var item_height = jQuery('#dashboard_item_'+ object_id).height();
	var item_padding_top = parseInt(jQuery('#dashboard_item_'+ object_id).css('padding-top'));
	var item_padding_bottom = parseInt(jQuery('#dashboard_item_'+ object_id).css('padding-top'));
	var item_total_height = (item_height + item_padding_top + item_padding_bottom);
	//construct rename html
	var rename_html  = '<div class="rename_object" id="rename_object_'+ object_id +'" style="height:'+ item_height +'px; margin-top:-'+ item_padding_top +'px; padding-top:'+ item_padding_top +'px; padding-bottom:'+ item_padding_bottom +'px;">';
			rename_html += '<div class="hero_col_4" style="padding-right:20px;"><span><input id="hero_rename_'+ object_id +'" class="hero_rename" type="text" value="'+ object_name +'"></span></div>';
			rename_html += '<div class="hero_col_4"><div class="hero_button_sml_dash_table darkgrey_button rounded_3" onclick="rename_object('+ object_id +');");">SAVE</div></div>';
		rename_html += '</div>';
	//insert rename html after object
	jQuery('#dashboard_item_'+ object_id).append(rename_html);
	//bind keyboard press "enter button"
	jQuery('#rename_object_'+ object_id).on('keypress', function(e){
		var keycode = (event.keyCode ? event.keyCode : event.which);
		if(keycode == 13){
			rename_object(object_id);
		}
	});
	//animate
	jQuery('#rename_object_'+ object_id).stop().animate({
		'margin-left': 0
	},300);
	//set focus on the rename input
	jQuery('#hero_rename_'+ object_id).focus().select();
}

//rename object
function rename_object(object_id){
	//get object name
	var object_name = jQuery('#hero_rename_'+ object_id).val();
	//rename object
	hplugin_rename_object(object_id, object_name, 'rename_object_complete');
}

//rename object complete
function rename_object_complete(){
	//close all tooltips
	hide_hplugin_tooltip();
	//hide and remove other rename objects
	jQuery('.rename_object').stop().animate({
		'margin-left': '-'+ 100 +'%'
	},300, function(){
		jQuery(this).remove();
		//flag as first load to avoid expanding the object dropdown in the sidebar
		first_load = true;
		update_hplugin_dashboard_data();
	});
}