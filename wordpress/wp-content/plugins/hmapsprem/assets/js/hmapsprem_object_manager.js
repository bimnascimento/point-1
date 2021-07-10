/*
	HPLUGIN OBJECT MANAGER
*/

//globals
var default_hplugin_object;
var hplugin_current_object_id;
var hplugin_current_main_object;
var dropdown_object_name_singular = 'Map';
var dropdown_object_name_plural = 'Maps';


//PAGE LOAD
//get default object
jQuery(function(){
	//get object
	jQuery.getJSON(plugin_url +'objects/default_object.js', function(data){
		//attach to global object
		default_hplugin_object = data;
	});
});



//OBJECT MANAGEMENT
//get object list
function hplugin_get_object_list(callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_get_object_list'
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json +"'));");
			}
		}
	});
}
//get object
function hplugin_get_object(object_id, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_get_object',
			'object_id': object_id
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json.replace(/'/g,"&#39;") +"'));");
			}
		}
	});
}
//replace all
function replaceAll(string, find, replace){
	return string.replace(new RegExp(escapeRegExp(find), 'g'), replace);
}

//insert object
function hplugin_insert_object(object_name, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_insert_object',
			'object_name': object_name,
			'object': JSON.stringify(default_hplugin_object)
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json +"'));");
			}
		}
	});
}
//update object
function hplugin_update_object(object_id, object, callback){
	/*
		custom: required for google maps marker pointers
	*/
	//clone object
	var object_clone = jQuery.extend(true, {}, object);
	//remove marker pointers from object clone
	jQuery.each(object_clone.map_markers, function(key,val){
		delete val.gmp;
	});
	//remove drawing pointers from object clone
	jQuery.each(object_clone.map_poly, function(key,val){
		delete val.gmd;
	});
	//persist
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_update_object',
			'object_id': object_id,
			'object': JSON.stringify(object_clone)
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json +"'));");
			}
		}
	});
}
//rename object
function hplugin_rename_object(object_id, object_name, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_rename_object',
			'object_id': object_id,
			'object_name': object_name
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json +"'));");
			}
		}
	});
}
//delete object
function hplugin_delete_object(object_id, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_delete_object',
			'object_id': object_id
		},
		dataType: "json"
	}).done(function(){
		if(typeof callback !== 'undefined' && callback !== 'undefined'){
			eval(""+ callback +"('"+ object_id +"');");
		}
	});
}
//duplicate object
function hplugin_duplicate_object(object_id, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_duplicate_object',
			'object_id': object_id
		},
		dataType: "json"
	}).done(function(data){
		if(typeof callback !== 'undefined' && callback !== 'undefined'){
			eval(""+ callback +"();");
		}
	});
}
//get object export
function hplugin_get_object_export(object_id, callback){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_get_object_export',
			'object_id': object_id
		},
		dataType: "json"
	}).done(function(json_object){
		if(typeof callback !== 'undefined' && callback !== 'undefined' && typeof json_object !== 'undefined' && json_object !== 'undefined'){
			if(typeof json_object === 'boolean'){
				eval(""+ callback +"("+ json_object +");");
			}else{
				var json = encodeURIComponent(JSON.stringify(json_object));
				eval(""+ callback +"(extract_json_object('"+ json +"'));");
			}
		}
	});
}



//BI-DIRECTIONAL BINDINGS (OBJECT <--> COMPONENT)
//bind components in view
function hplugin_bind_view_components(object_id, object){
	//define current object id
	hplugin_current_object_id = object_id;
	//define current main object
	hplugin_current_main_object = object;
	//set data to object values
	hplugin_set_data_object_values();
	//bind view navigation
	hplugin_event_subscribe('map-data-object','hplugin_set_data_object_values',undefined);
}
//set data to object values
function hplugin_set_data_object_values(){
	//look for data type and find match
	jQuery('input').each(function(index, element){
		if(jQuery(this).data('node_val')){
			//get node value
			var node_val = jQuery(this).data('node_val');
			
			var object_val = eval("hplugin_current_main_object."+ node_val.replace(/\//g,".") +"");
			var object_ref = 'hplugin_current_main_object.'+ node_val.replace(/\//g,".");
			
			switch(element.type){
				case 'text':
					//set value
					jQuery(this).val(object_val);
					//declare input management function
					var input_man_func = function(){
						//check if px, ms or perc
						var data_type = jQuery(this).data('hero_type');
						//set json object value
						if(data_type == 'px' || data_type == 'ms' || data_type == 'perc'){
							eval(""+ object_ref +" = "+ parseInt(jQuery(this).val()) +";");
						}else{
							eval(""+ object_ref +" = '"+ jQuery(this).val() +"';");
						}
						//flag save
						flag_save_required('hplugin_persist_object_data');
					}
					//bind change event
					jQuery(this).parent().on('change keyup paste', 'input', input_man_func);
				break;
				case 'password':
					//set value
					jQuery(this).val(object_val);
					//declare input management function
					var input_man_func = function(){
						//set json object value
						eval(""+ object_ref +" = '"+ jQuery(this).val() +"';");
						//flag save
						flag_save_required('hplugin_persist_object_data');
					}
					//bind change event
					jQuery(this).parent().on('change keyup paste', 'input', input_man_func);
				break;
				case 'checkbox':
					//set value
					if(object_val === true){
						jQuery(this).prop('checked',true);
					}else{
						jQuery(this).prop('checked',false);
					}
					//declare input management function
					var input_man_func = function(){
						//set json object value
						if(jQuery(this).is(':checked')){
							eval(""+ object_ref +" = true;");
						}else{
							eval(""+ object_ref +" = false;");
						}
						//flag save
						flag_save_required('hplugin_persist_object_data');
					}
					//bind change event
					jQuery(this).parent().on('change', 'input', input_man_func);				
				break;
				case 'radio':
					//set value
					if(jQuery(this).val() === object_val){
						jQuery(this).prop('checked',true);
					}else{
						jQuery(this).prop('checked',false);
					}
					//declare input management function
					var input_man_func = function(){
						//set json object value
						eval(""+ object_ref +" = '"+ jQuery(this).val() +"';");
						//flag save
						flag_save_required('hplugin_persist_object_data');
					}
					//bind change event
					jQuery(this).parent().on('change', 'input', input_man_func);
				break;
			}
		}
    });
	jQuery('textarea').each(function(index, element){
		if(jQuery(this).data('node_val')){
			//get node value
			var node_val = jQuery(this).data('node_val');
			var object_val = eval("hplugin_current_main_object."+ node_val.replace(/\//g,".") +"");
			var object_ref = 'hplugin_current_main_object.'+ node_val.replace(/\//g,".");
			//set value
			jQuery(this).val(object_val);
			//declare input management function
			var input_man_func = function(){
				//set json object value
				eval(""+ object_ref +" = '"+ jQuery(this).val() +"';");
				//flag save
				flag_save_required('hplugin_persist_object_data');
			}
			//bind change event
			jQuery(this).parent().on('change keyup paste', 'textarea', input_man_func);
		}
	});
	jQuery('select').each(function(index, element){
		if(jQuery(this).data('node_val')){
			//get node value
			var node_val = jQuery(this).data('node_val');
			var object_val = eval("hplugin_current_main_object."+ node_val.replace(/\//g,".") +"");
			var object_ref = 'hplugin_current_main_object.'+ node_val.replace(/\//g,".");
			//set value
			jQuery(this).val(object_val);
			//declare input management function
			var input_man_func = function(){
				//set json object value
				eval(""+ object_ref +" = '"+ jQuery(this).val() +"';");
				//flag save
				flag_save_required('hplugin_persist_object_data');
			}
			//bind change event
			jQuery(this).parent().on('change', 'select', input_man_func);
		}
	});
}
//save object data
function hplugin_persist_object_data(){
	//save
	hplugin_update_object(hplugin_current_object_id, hplugin_current_main_object, 'hplugin_object_data_saved');
}
//object data saved
function hplugin_object_data_saved(){
	//success
	show_message('success', 'Update Success', 'The changes to your '+ dropdown_object_name_singular +' have been successfully persisted.');
}
//update database objects with default object (adds new columns to existing objects while preserving existing data)
function update_database_objects(){
	jQuery.ajax({
		url: ajax_url,
		type: "POST",
		data: {
			'action': plugin_name +'_update_database_objects'
		},
		dataType: "json"
	}).done(function(response){
		console_log('Database objects successfully updated');
	}).fail(function(error){
		console_log('Error updating database objects');
		console_log(error);
	});
}