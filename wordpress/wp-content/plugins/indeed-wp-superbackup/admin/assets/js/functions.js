function ibk_save_destination_metas(auth, status_type){
	jQuery('#ibk_saving_message').fadeIn(100, function(){
		jQuery(this).html('Saving...');
	});
	var obj = {
		action: 'ibk_save_destination_metas_via_ajax',
		is_edit: jQuery('#ibk_destination-is_edit').val(),
		id: jQuery('#ibk_destination-id').val(),
		name: jQuery('#ibk_destination-name').val(),
		admin_box_color: jQuery('#ibk_admin_box_color').val(),
		type: jQuery("input[name=type]:checked").val(),
		connected: jQuery('#ibk_connected').val(),
		status: status_type,
	};
	switch (obj.type){
		case 'google':
			obj.client_id = jQuery('#client_id').val();
			obj.client_secret = jQuery('#client_secret').val();
			obj.redirect_uri = jQuery('#redirect_uri').val();
			obj.access_token = jQuery('#access_token').val();
			obj.refresh_token = jQuery('#refresh_token').val();
			obj.folder_id = jQuery('#folder_id').val();
		break;
		case 'local':
			obj.local_folder_target = jQuery('#local_folder_target').val();
		break;
		case 'ftp':
			obj.server_address = jQuery('#ftp-server_address').val();
			obj.username = jQuery('#ftp-username').val();
			obj.password = jQuery('#ftp-password').val();
			obj.directory = jQuery('#ftp-directory').val();
			obj.protocol = jQuery('#ftp-protocol').val();
			obj.server_port = jQuery('#ftp-server_port').val();
			obj.server_timeout = jQuery('#ftp-server_timeout').val();
			obj.passive_mode = jQuery('#ftp-passive_mode').val();
		break;
		case 'rackspace':
			obj.username = jQuery('#rs_username').val();
			obj.api_key = jQuery('#rs_api_key').val();
			obj.container = jQuery('#rs_container').val();
			obj.container_url = jQuery('#rs_container_url').val();
			obj.region = jQuery('#rs_region').val();
		break;
		case 'amazon':
			obj.aws_key = jQuery('#aws_key').val();		
			obj.aws_secret_key = jQuery('#aws_secret_key').val();		
			obj.aws_region = jQuery('#aws_region').val();	
			obj.aws_ssl = jQuery('#aws_ssl').val();
			obj.aws_bucket = jQuery('#aws_bucket').val();	
			obj.subfolder = jQuery('#amazon_subfolder').val();
		break;
		case 'dropbox':
			obj.path = jQuery("#dropbox_path").val();
		break;
		case 'onedrive':
			obj.client_id = jQuery('#onedrive_client_id').val();
			obj.client_secret = jQuery('#onedrive_client_secret').val();
			obj.redirect_uri = jQuery('#onedrive_redirect_uri').val();
		break;
		case 'copy':
			obj.path = jQuery('#copy_path').val();
		break;
	}

	jQuery.ajax({
		type: "post",
		url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
		data: obj,
		success: function (response){
			if (response){
				
				if (auth){
					
					jQuery('#ibk_saving_message').fadeOut(400, function(){
						jQuery(this).html('Redirecting...');
						jQuery(this).fadeIn(100);
					});
					
					switch (obj.type){
						case 'google':							
							ibk_authorize_google();//authorize google
						break;
						case 'local':
						case 'rackspace':
						case 'amazon':
							if (status_type==1){
								//cloud destinatination
								window.location = window.ibk_admin_url+'&tab=cloud&destinations=true';
							} else {
								//standard destination
								window.location = window.ibk_admin_url+'&tab=destinations';					
							}  						
						break;
						case 'ftp':
							ibk_test_ftp_connection(status_type);
						break;
						case 'dropbox':
							ibk_dropbox_redirect();
						break;
						case 'onedrive':
							ibk_onedrive_redirect();
						break;
						case 'copy':
							ibk_copydotcom_redirect();
						break;
					}					
				} else {
					jQuery('#ibk_saving_message').fadeOut(400, function(){
						jQuery(this).html('Saved');
						jQuery(this).fadeIn(100);
						if (status_type==1){
							//cloud destinatination
							window.location = window.ibk_admin_url+'&tab=cloud&destinations=true';
						} else {
							//standard destination
							window.location = window.ibk_admin_url+'&tab=destinations';					
						}
					});
				}
				
			}
		}
	});
}

function ibk_authorize_google(){
	event.preventDefault();
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_google_authorize_ajax',
        	destination_id: jQuery('#ibk_destination-id').val()
        },
   		success: function (response){
   			if (response==false){
   				alert('error somewhere');
   			} else {
   				window.location = response;
   			}
   		}
   	});
}

function ibk_dropbox_redirect(){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_get_dropbox_auth_url',
        	destination_id: jQuery('#ibk_destination-id').val()
        },
   		success: function (response){
   			if (response==false){
   				alert('error somewhere');
   			} else {
   				window.location = response;
   			}
   		}
   	});	
}

function ibk_onedrive_redirect(){
	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_get_onedrive_auth_url',
        	destination_id: jQuery('#ibk_destination-id').val(),
        	onedrive_client_id: jQuery('#onedrive_client_id').val(),
        	onedrive_client_secret: jQuery('#onedrive_client_secret').val()
        },
   		success: function (response){
   			if (response==false){
   				alert('error somewhere');
   			} else {
   				window.location = response;
   			}
   		}
	});
}

function ibk_copydotcom_redirect(){
	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_get_copydotcom_auth_url',
        	destination_id: jQuery('#ibk_destination-id').val()
        },
   		success: function (response){
   			if (response==false){
   				alert('error somewhere');
   			} else {
   				window.location = response;
   			}
   		}
	});	
}

function indeed_stripslashes(str) {
	  return (str + '').replace(/\\(.?)/g, function(s, n1) {
	      switch (n1) {
	        case '\\':
	          return '\\';
	        case '0':
	          return '\u0000';
	        case '':
	          return '';
	        default:
	          return n1;
	      }
	    });
	}


function ibk_test_ftp_connection(status_type){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_test_ftp_connection',
        	destination_id: jQuery('#ibk_destination-id').val()
        },
   		success: function (response){
   			if (response==false){
   				alert('error somewhere');
   			} else {
				if (status_type==1){
					//cloud destinatination
					window.location = window.ibk_admin_url+'&tab=cloud&destinations=true';
				} else {
					//standard destination
					window.location = window.ibk_admin_url+'&tab=destinations';					
				}   				
   			}
   		}
   	});	
}

function ibk_write_tag_value(id, hiddenId, viewDivId, prevDivPrefix){
    if (jQuery(id).val()==0){
    	return;
    } else if (jQuery(id).val()==-1){
    	jQuery(id).val(0); 
    	jQuery(hiddenId).val('');
    	jQuery(viewDivId).empty();
    	return;
    }
    hidden_i = jQuery(hiddenId).val();
    
    if (hidden_i!=''){
    	show_arr = hidden_i.split(',');
    } else {
    	show_arr = new Array();
    }
    
    var blog_id = 0;
    if (jQuery("#ibk_backup_site_id").attr("name")=="blog_id"){
    	blog_id = jQuery("#ibk_backup_site_id").val();
    }
    
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_get_table_list_via_ajax',
        	type: jQuery(id).val(),
        	site: blog_id,
        },
   		success: function (response){
   			var show_str = jQuery(hiddenId).val();
   			if (show_str!=''){
   				var show_arr = show_str.split(',');
   			} else {
   				var show_arr = [];
   			}
   			
   			var main_obj = jQuery.parseJSON(response);
   			var obj = main_obj.values;
   			for (var key in obj) {
   				if (show_arr.indexOf(key) > -1){
   					continue;
   				}
				show_arr.push(key);	
				var class_div = "ibk-tag-item";
				if (typeof main_obj.native[key]!='undefined' && main_obj.native[key]==1){
					var class_div = "ibk-tag-item-native";
				}
   	   			jQuery(viewDivId).append('<div id="'+prevDivPrefix+key+'" class="' + class_div + '">'+obj[key]+'<div class="ibk-remove-tag" onclick="ibk_remove_db_tag(\''+key+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
   			}
  			var str = show_arr.join(',');
	   		jQuery(hiddenId).val(str);
	   		jQuery(id).val(0);   			
   		}
   	});
}

function ibk_display_site_to_bk_selection(s){
	jQuery('#ibk_select_field_db_tables').val(-1);
	ibk_write_tag_value('#ibk_select_field_db_tables', '#save_db_table_list', '#ibk-database-list-tables', 'backup-t-items-');
	if (jQuery(s).is(":checked")){
		jQuery("#ibk_backup_site_id").attr("name", "blog_id");
		setTimeout(function(){ jQuery("#the_select_of_blog_id").fadeIn(200);},500);
		jQuery("#ibk_backup_all_sites").attr("name", "");
	} else {
		jQuery("#ibk_backup_site_id").attr("name", "");
		jQuery("#the_select_of_blog_id").fadeOut(200, function(){
			jQuery("#the_select_of_blog_id").css("display", "none");
		});
		jQuery("#ibk_backup_all_sites").attr("name", "blog_id");
	}
}

function ibk_write_tag_value_migrate(id, hiddenId, viewDivId, prevDivPrefix){
	var the_selected_val = id.value;
	
    if (the_selected_val==0){
    	return;
    } else if (the_selected_val==-1){
    	jQuery(id).val(0); 
    	jQuery(hiddenId).val('');
    	jQuery(viewDivId).empty();
    	return;
    } else if(the_selected_val=='all' || the_selected_val=='non_wp'){
    	if (jQuery('#migrate_non_wp_tables').val()==0){
        	jQuery(viewDivId).append('<div id="ibk_migrate_all_non_wp" class="ibk_migrate_all_non_wp">All Non Wp<div class="ibk-remove-tag" onclick="ibk_remove_all_none_wp_Table_opt();" title="Removing tag">x</div></div>');
        	jQuery('#migrate_non_wp_tables').val(1);
    	}
        if (the_selected_val=='all'){
        	the_selected_val = 'wp';
        } else {
        	jQuery(id).val(0); 
        	return;
        }    		
    }
    
    var hidden_i = jQuery(hiddenId).val();
    
    if (hidden_i!=''){
    	show_arr = hidden_i.split(',');
    } else {
    	show_arr = new Array();
    }

   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_get_table_list_via_ajax',
        	type: the_selected_val
        },
   		success: function (response){
   			//var arr = jQuery.parseJSON(response);
   			var arr = response.split(',');
   			for (i=0;i<arr.length;i++){
   				if (show_arr.indexOf(arr[i])==-1){
   					show_arr.push(arr[i]);	
   	   				jQuery(viewDivId).append('<div id="'+prevDivPrefix+arr[i]+'" class="ibk-tag-item">'+window.ibk_wp_db_prefix+arr[i]+'<div class="ibk-remove-tag" onclick="ibk_remove_db_tag(\''+arr[i]+'\', \'#'+prevDivPrefix+'\', \''+hiddenId+'\');" title="Removing tag">x</div></div>');
   				}    	
   			}
  			str = show_arr.join(',');
	   		jQuery(hiddenId).val(str);
	   		jQuery(id).val(0);   			
   		}
   	});
}

function ibk_remove_all_none_wp_Table_opt(){
	jQuery("#ibk_migrate_all_non_wp").remove();
	jQuery('#migrate_non_wp_tables').val(0);
}

function ibk_remove_db_tag(removeVal, prevDivPrefix, hiddenId){
	jQuery(prevDivPrefix+removeVal).fadeOut(200, function(){
		jQuery(this).remove();
	});	    
    hidden_i = jQuery(hiddenId).val();
    show_arr = hidden_i.split(',');    
    show_arr = ibk_remove_array_element(removeVal, show_arr);
    str = show_arr.join(',');
	jQuery(hiddenId).val(str);
}
	

function ibk_remove_array_element(elem, arr){
	for (i=0;i<arr.length;i++) {
	    if(arr[i]==elem){
	    	arr.splice(i, 1);
	    }
	}
	return arr;
}

function ibk_make_inputh_string(divCheck, showValue, hidden_input_id){
    str = jQuery(hidden_input_id).val();
    if(str==-1) str = '';
    if(str!='') show_arr = str.split(',');
    else show_arr = new Array();
    if(jQuery(divCheck).is(':checked')){
        show_arr.push(showValue);
    }else{
        var index = show_arr.indexOf(showValue);
        show_arr.splice(index, 1);
    }
    str = show_arr.join(',');
    if(str=='') str = -1;
    jQuery(hidden_input_id).val(str);
}

function ibk_delete_item(i, t, n, tt){
	var conf = confirm('Delete Backup "'+n+'" ?');
	if (conf){
	   	jQuery.ajax({
	        type: "post",
	        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
	        data: {
	        	action: 'ibk_delete_item_via_ajax',
	        	id: i,
	        	type: t
	        },
	   		success: function (response){
	   			if (t=='backup'){
	   				window.location = window.ibk_admin_url+'&tab=manage_backups';
	   			} else {
	   				if (tt==1){
	   					window.location = window.ibk_admin_url+'&tab=cloud&destinations=true';
	   					return;
	   				}
	   				window.location = window.ibk_admin_url+'&tab=destinations';
	   			}
	   			
	   		}
	   	});		
	}
}

jQuery(document).ready(function() {
    jQuery('#specified_date').datepicker({
        dateFormat : 'dd-mm-yy'
    });
});

function indeed_select_show_div(check, value, target){
	//show a div if a select value its selected
	if (jQuery(check).val()==value){
		jQuery(target).fadeIn(400);
	} else {
		jQuery(target).fadeOut(400);
	}
}

function ibk_change_color_scheme(id, value, where ){
    jQuery('#colors_ul li').each(function(){
        jQuery(this).attr('class', 'ibk-color-scheme-item');
    });
    jQuery(id).attr('class', 'ibk-color-scheme-item-selected');
    jQuery(where).val(value);
}

function ibk_show_destination_type(v){
	var arr = ['#ibk-google', '#ibk-ftp', '#ibk-dropbox', '#ibk-local', '#ibk-rackspace', '#ibk-amazon', '#ibk-onedrive', '#ibk-copy'];
	for(i=0;i<arr.length;i++){
		jQuery(arr[i]).fadeOut(200);
	}
	if (v!=0){
		jQuery('#ibk-'+v).fadeIn(200);
	}
}

function ibk_delete_log(i, d){
	//
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_delete_log_via_ajax',
        	process_id: i,
        },
   		success: function (response){
   			jQuery(d).fadeOut(400, function(){
   				jQuery(this).remove();
   			})
   		}
   	});
}

function ibk_backup_interval(v){
	jQuery('#cron-specified_date').css('display', 'none');
	jQuery('#cron-periodically').css('display', 'none');
	jQuery("#bttn_save_and_run").css('display', 'none');
	if (v==0){
		jQuery("#bttn_save_and_run").fadeIn(300);
	} else if (v==-1){
		jQuery('#cron-specified_date').fadeIn(300);			
	} else if (v==1){
		jQuery('#cron-periodically').fadeIn(300);
	}
}

function ibk_open_popup(t, i){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_return_popup_via_ajax',
        	id: i,
        	type: t,
        },
   		success: function (response){
   			jQuery('#indeed_popup_wrapp').html(response);
   		}
   	});	
}

function ibk_close_popup(){
	jQuery('#ibk_popup_box').fadeOut(300, function(){
		jQuery(this).remove();
	});
}

function ibk_check_and_h(from, target){
	if (jQuery(from).is(":checked")) jQuery(target).val(1);
	else jQuery(target).val(0);
}

function ibk_progress_bar_update(arr, i){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_check_log_status_via_ajax',
        	id: arr[i],
        },
   		success: function (response){
   				var obj = jQuery.parseJSON(response);
   				//console.log(obj.percent+' '+obj.msg);
   				if (obj.percent && obj.msg){
   	   				jQuery('#log_no_'+arr[i]+' .progress-bar').css('width', obj.percent+'%');
   	   				jQuery('#log_no_'+arr[i]+' .progress-bar').html(obj.percent+'%');
   	   				jQuery('#ibk_log_msg_'+arr[i]).html(obj.msg);  
   	   				if (obj.status==2){
   	   					//red progress bar
   	   					jQuery('#log_no_'+arr[i]+' .progress-bar').removeClass('progress-bar-success');
   	   					jQuery('#log_no_'+arr[i]+' .progress-bar').addClass('progress-bar-danger');
   	   				}
   	   				
   	   				if (obj.percent==100){
   	   					if (obj.status==1){
   	   						jQuery('#log_no_'+arr[i]+' .ibk-log-status').html('<i class="fa-ibk fa-check-circle-bk"></i>');
   	   					} else {
   	   						jQuery('#log_no_'+arr[i]+' .ibk-log-status').html('<i class="fa-ibk fa-error-circle-bk"></i>');
   	   					}
   	   					delete arr[i];
   	   				}
   	   				setTimeout(function(){
   	   	   				i++;
   	   	   				if (i==arr.length) i = 0;
   	   	   				ibk_progress_bar_update(arr, i);					
   	   				}, 500);   					
   				}  				
   		}
   	});	

}

function ibk_restore_snapshot(snapshot_id, extra_data){
	//run a restore process...
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_restore_snapshot_via_ajax',
        	id: snapshot_id,
        	data: extra_data,
        },
   		success: function (response){
   		}
   	});
}

function ibk_restore_popup(id_of_snapshot, id_of_destination){
	jQuery('#snapshot_list_versions').html('');
	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_restore_popup_box',
        	snapshot_id: id_of_snapshot,
        	destination_id: id_of_destination,
        },
        success: function (response) {
        	jQuery('#snapshot_list_versions').fadeOut(200, function(){
        		jQuery(this).html(response); 
        		jQuery(this).fadeIn(200);
        	});       	 	
        }
   	});
}

function ibk_download_popup(id_of_snapshot, id_of_destination){
	jQuery('#snapshot_list_versions').html('');
	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_download_popup_box',
        	snapshot_id: id_of_snapshot,
        	destination_id: id_of_destination,
        },
        success: function (response) {
        	jQuery('#snapshot_list_versions').fadeOut(200, function(){
        		jQuery(this).html(response); 
        		jQuery(this).fadeIn(200);
        	});       	 	
        }
   	});
}

function ibk_migrate_popup(snapshot_id, connection_id){
	jQuery('#snapshot_list_versions').html('');
	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_migrate_popup_box',
        	connection: connection_id,
        	snapshot: snapshot_id,
        	cloud_data: jQuery('#ibk-cloud-'+snapshot_id).val(),
        },
        success: function (response) {
        	jQuery('#snapshot_list_versions').fadeOut(200, function(){
        		jQuery(this).html(response); 
        		jQuery(this).fadeIn(200);
        	});       	 	
        }
   	});
}

function ibk_select_snapshot_instance(t, v){
	jQuery('.ibb-popup-list-snapshots-instances div').each(function(){
		jQuery(this).attr('class', 'ibk-restore-snapshot-item-popup');
	});
	jQuery(t).attr('class', 'ibk-restore-snapshot-item-popup-selected');
	jQuery('#ibk_source_file').val(v);
}

function ibk_check_restore_status(){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_check_restore_status',
        },
   		success: function (response){
   			if (response==0){
   				if (document.getElementById("ibk_restore_wall")) {
	   				//it exists and must be removed
	   				jQuery('#ibk_restore_wall').remove();
   				}
   			} else {   	
   				if (!document.getElementById("ibk_restore_wall")) {
   					jQuery('#wpcontent').after('<div class="ibk-restore-wall" id="ibk_restore_wall">'+
												'<div class="ibk-restore-loading-content">'+
												'<div class="ibk-restore-loading-content-title">Restore</div>'+
   													'<div class="ibk-restore-status">'+response+'</div>'+
   													'<div class="ibk-restore-loading"><img src="'+window.ibk_base_url+'/wp-content/plugins/indeed-wp-superbackup/admin/assets/images/loadbar.gif"/></div>'+
   												'</div>'+
												'</div>');
   				} else {
   					jQuery('.ibk-restore-status').html(response);
   					/*
   					jQuery('.ibk-restore-status').fadeOut(200, function(){
   						jQuery(this).html(response);
   						jQuery(this).fadeIn(200);
   					});
   					*/
   				}				
   	   	   		setTimeout(function(){
   	   	   	   		ibk_check_restore_status();					
   	   	   		}, 5000);      				
   			}					
   		}  				
   	});	
}

function ibk_clear_log_debug(){
	jQuery.ajax({
        type : "post",
		dataType: 'JSON',
        url :  window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data : {
                action: "ibk_clear_log_debug_file",
        },
        success: function (data) {
        	if (data==1){
        		jQuery('#ibk_debug_log').fadeOut(400, function(){
        			jQuery('#ibk_debug_log').html("");
        			jQuery('#ibk_debug_log').fadeIn(200);
        		});
        	}
		}
	});
}


function erase_backup_logs(u){
	window.location= u + '&older_than=' + jQuery("#ibk_older_than").val();
}



function ibk_run_backup_now(i){
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_run_backup_via_ajax',
        	id: i
        },
   		success: function (response){
   			window.location = document.URL; //refresh the page
   		}
   	});
}

function ibk_check_destination(i){
	jQuery('#wpcontent').after('<div class="ibk-restore-wall" id="ibk_testing_connection_wall">'+				
				'<div class="ibk-restore-loading-content">'+
				'<div class="ibk-close-testing-connection"><div class="ibk-close-bttn-connection-testing" onClick="jQuery(\'#ibk_testing_connection_wall\').remove();">x</div></div>'+
				'<div class="ibk-restore-loading-content-title">Testing Connection</div>'+
						'<div class="ibk-restore-status">Checking</div>'+
						'<div class="ibk-restore-loading"><img src="'+window.ibk_base_url+'/wp-content/plugins/indeed-wp-superbackup/admin/assets/images/loadbar.gif"/></div>'+
					'</div>'+
				'</div>');
   	jQuery.ajax({
        type: "post",
        url: window.ibk_base_url+'/wp-admin/admin-ajax.php',
        data: {
        	action: 'ibk_check_destination',
        	id: i
        },
   		success: function (response){
   			jQuery('.ibk-restore-loading').remove();
   			jQuery('.ibk-restore-status').empty();
   			jQuery('.ibk-restore-status').css('font-size', '17px');
   			jQuery('.ibk-restore-status').css('font-weight', '500');
   			if (response==1){
   				jQuery('.ibk-restore-status').html("<i class='fa-ibk fa-check-circle-bk'></i>Destination it's properly set and ready to work!");
   				setTimeout(function(){
   	   				jQuery('#ibk_testing_connection_wall').fadeOut(200);
   	   			}, 3000);  			
   			} else {
   				jQuery('.ibk-restore-status').html("<i class='fa-ibk fa-error-circle-bk'></i>This destination it's not working, try later or set another one!");
   			}
   		}
   	});
}

