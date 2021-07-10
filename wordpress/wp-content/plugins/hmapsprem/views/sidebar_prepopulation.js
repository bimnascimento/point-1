//SIDEBAR PRE_POPULATION

//load
/*
	note: this method is required by the core framework and is called when the plugin is initialised.
*/
var first_load = true;
function prepopulate_sidebar_elements(){
	//load objects into sidebar
	hplugin_get_object_list('load_objects_into_sidebar');
	//bind buttons
	bind_sidebar_btn_listeners()
}

//load objects into sidebar
function load_objects_into_sidebar(object){
	jQuery('.dropdown_submenu_holder li').remove();
	if(object){
		jQuery.each(object, function(key,val){
			add_sidebar_element('dropdown_default', val.object_id, val.object_name, {"object_id": val.object_id}, 'load_default_object');
		});
		if(!first_load){
			var sidebar_item_height = jQuery('.hero_sidebar_item').height();
			var sub_item_height = jQuery('.hero_sub').height();
			jQuery('#dropdown_default').stop().animate({
				'height': (sidebar_item_height + sub_item_height) +'px'
			},300);
		}
	}
	first_load = false;
}

//bind sidebar button listeners
function bind_sidebar_btn_listeners(){
	jQuery('.hero_sidebar').on('click','#sidebar_add_new_btn', function(){
		//show add new
		hplugin_show_add();
	});
}

//show object add
var add_new_active = false;
function hplugin_show_add(){
	if(!add_new_active){
		var add_html = '<div class="hero_add_new">';
				add_html += '<div class="hero_new_wrap">';
					add_html += '<form id="insert_object_form">';
						add_html += '<input type="text" data-size="lrg" placeholder="'+ dropdown_object_name_singular +' Name" name="new_object_name" id="new_object_name">';
						add_html += '<div class="hero_sidebar_button size_11 rounded_3 hero_white" id="add_new_object_btn">Add</div>';
					add_html += '</form>';
				add_html += '</div>';
			add_html += '</div>';
		jQuery(add_html).insertAfter(jQuery('#dropdown_default'));
		jQuery('#new_object_name').focus();
		add_new_active = true;
		animate_add_open();
	}
}

//bind menu add listener
var block_object_add = false;
function bind_menu_add_listener(){
	jQuery('#insert_object_form').off().on('submit', function(event){
		//prevent default
		event.preventDefault();
		if(!block_object_add){
			block_object_add = true;
			//remove error
			jQuery('#new_object_name').removeClass('has-error');
			//get object name
			var object_name = jQuery('#new_object_name').val();
			//check object name
			if(object_name.length > 1){
				generate_new_object(object_name);
			}else{
				jQuery('#new_object_name').addClass('has-error');
				block_object_add = false;
			}
		}
	});
	jQuery('#add_new_object_btn').off().on('click', function(){
		jQuery('#insert_object_form').submit();
	});
}

//generate new object
function generate_new_object(object_name){
	 hplugin_insert_object(object_name, 'update_sidebar_objects');
}

//update sidebar objects
function update_sidebar_objects(object_id){
	hplugin_get_object_list('load_objects_into_sidebar');
	show_message('success', dropdown_object_name_singular +' Added', 'Your new '+ dropdown_object_name_singular.toLowerCase() +' has been successfully created.');
	manual_load_core_view('dropdown_default', {"object_id": object_id}, 'load_default_object');
	animate_add_closed();
	add_new_active = false;
}

//animate open add new
function animate_add_open(){
	jQuery('.hero_add_new').animate({
		'height': 50 +'px'		
	},300, function(){
		bind_menu_add_listener();
	});
}

//animate add closed
function animate_add_closed(){
	jQuery('.hero_add_new').animate({
		'height': 0 +'px'	
	},300,function(){
		jQuery('.hero_add_new').remove();
		block_object_add = false;
	});			
}