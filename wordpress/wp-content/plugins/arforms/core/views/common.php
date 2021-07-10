<?php

global $arfsettings, $arf_captcha_loaded, $arf_file_loaded, $arf_modal_form_loaded, $arftimepickerloaded, $arf_slider_loaded, $arf_selectbox_loaded, $arf_radio_checkbox_loaded, $arfdatepickerloaded, $arf_conditional_logic_loaded, $arf_inputmask_loaded, $arfcolorpicker_loaded, $arfcolorpicker_basic_loaded, $arf_wizard_form_loaded, $arf_survey_form_loaded, $is_multi_column_loaded, $arf_font_awesome_loaded, $arfversion;


if( $arf_captcha_loaded > 0 ){
	wp_print_scripts('recaptcha-ajax');
	wp_print_styles('arfrecaptchacss');
	$arf_captcha_loaded = 0;
}

if( $arf_file_loaded > 0 ){	
	if($arfsettings->form_submit_type == 1)
	{
		if(version_compare( $GLOBALS['wp_version'], '3.7', '<'))
		{
			wp_register_script('filedrag', ARFURL.'/js/filedrag/filedrag_front_lower.js',array(),$arfversion);
			wp_print_scripts('filedrag');
		}
		else
		{
			wp_register_script('filedrag', ARFURL.'/js/filedrag/filedrag_front.js',array(),$arfversion);
			wp_print_scripts('filedrag');
		}
	}
	wp_print_styles('arf-filedrag');
	$arf_file_loaded = 0;	
}

if( $arf_modal_form_loaded > 0 ){
	wp_print_scripts('arf-modal-js');
	$arf_modal_form_loaded = 0;
}

if( $arftimepickerloaded && count($arftimepickerloaded) > 0 ){

    wp_print_styles('form_custom_css-default_theme');
    for ($x = 1; $x <= 24; $x++) {
        wp_print_styles('form_custom_css-'.$x);
    }

}

if( $arf_slider_loaded && count($arf_slider_loaded) > 0 ){
	wp_print_scripts('arfbootstrap-modernizr-js');
	wp_print_scripts('arfbootstrap-slider-js');
	wp_print_styles('arfbootstrap-slider');
}

if( $arf_selectbox_loaded && count($arf_selectbox_loaded) > 0 )
{
	wp_print_styles('arfbootstrap-select');
	wp_print_scripts('jquery-bootstrap-slect');
}

if( $arf_radio_checkbox_loaded && count($arf_radio_checkbox_loaded) > 0 )
{
	wp_print_scripts('jquery-icheck');
}

if( $arfdatepickerloaded && count($arfdatepickerloaded) > 0 )
{       
    
    wp_print_styles('form_custom_css-default_theme');
    for ($x = 1; $x <= 24; $x++) {
        wp_print_styles('form_custom_css-'.$x);
    } 
    
    
}

if( $arf_conditional_logic_loaded && count($arf_conditional_logic_loaded) > 0 )
{
	wp_print_scripts('arf-conditional-logic-js');
}

if( $arf_inputmask_loaded && count($arf_inputmask_loaded) > 0 ){
	wp_print_scripts('arfbootstrap-inputmask');
}

if( $arfcolorpicker_loaded && count($arfcolorpicker_loaded) > 0 ){
	wp_print_styles('arf-colorpicker');
	wp_print_scripts('arf-colorpicker-js');
}

if( $arfcolorpicker_basic_loaded && count($arfcolorpicker_basic_loaded) > 0 ){
	wp_print_scripts('arf-colorpicker-basic-js');
}

if( $arf_survey_form_loaded && count($arf_survey_form_loaded) > 0 ){
	wp_print_scripts('jquery-ui-core');		
	wp_print_scripts('jquery-ui-widget');
	wp_print_scripts('jquery-ui-progressbar');
	wp_print_scripts('jquery-effects-slide');
}

if( $arf_wizard_form_loaded && count($arf_wizard_form_loaded) > 0 ){
	wp_print_scripts('jquery-ui-core');		
	wp_print_scripts('jquery-effects-slide');
}

if( $arf_font_awesome_loaded )
    wp_print_styles( 'arf-fontawesome-css' );

wp_print_scripts('arfbootstrap-js');
wp_print_scripts('jquery-validation');

?>
<div id="main_form_flag_mange"></div>
<script type="text/javascript">

//<![CDATA[

<?php 

echo "__ARFMAINURL='". ARFSCRIPTURL ."';\n";

echo "__ARFERR='".addslashes(__('Sorry, this file type is not permitted for security reasons.', 'ARForms'))."';\n";

echo "__ARFAJAXURL='".admin_url('admin-ajax.php')."';\n";

echo "__ARFSTRRNTH_INDICATOR='".addslashes(__('Strength indicator', 'ARForms'))."';\n";

echo "__ARFSTRRNTH_SHORT='".addslashes(__('Short', 'ARForms'))."';\n";

echo "__ARFSTRRNTH_BAD='".addslashes(__('Bad', 'ARForms'))."';\n";

echo "__ARFSTRRNTH_GOOD='".addslashes(__('Good', 'ARForms'))."';\n";

echo "__ARFSTRRNTH_STRONG='".addslashes(__('Strong', 'ARForms'))."';\n";


if( $is_multi_column_loaded )
	$is_multi_column_loaded = array_unique($is_multi_column_loaded);
if( is_rtl() && count($is_multi_column_loaded) > 0 )	
{
	$form_str = "";
	foreach($is_multi_column_loaded as $multicol_forms)
	{
		$form_str .= "#form_".$multicol_forms.", ";	
	}
	$form_str = rtrim($form_str, ", ");
?>
jQuery(document).ready(function(){
	
	var screenwidth = jQuery(window).width();
	if(screenwidth >= 480)
	{
		var tabindex = 2;
		jQuery("<?php echo $form_str?>").each(function(){
		var form = jQuery(this);
		var two_col_1_tabi 		= '';
		var two_col_1_field 	= '';
		var three_col_1_tabi 	= '';
		var three_col_1_field 	= '';
		var three_col_2_tabi	= '';
		var three_col_2_field	= '';
		
		jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function(e, item){
			var field = jQuery(this);
				field_id	= field.attr('name');
				field_type	= field.attr('type');
			if( field_id && ( field_id.indexOf('item_meta') != '-1' || field_id.indexOf('vpb_captcha_code') != '-1' ) )
			{
				if( jQuery(field).parents('.arfformfield').first().hasClass('frm_first_half') ){
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					
					two_col_1_tabi 	= tabindex;
					two_col_1_field = item;
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex);							
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_last_half') ){
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					
					jQuery(two_col_1_field).attr('tabindex', tabindex);		
					jQuery(field).attr('tabindex', two_col_1_tabi);			
					
					change_tabindex_radio(two_col_1_field, tabindex);	
					change_tabindex_radio(field, two_col_1_tabi);
						
					two_col_1_tabi 	= '';
					two_col_1_field = ''; 
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_first_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex);
										
					three_col_1_tabi  = tabindex;
					three_col_1_field = item;
					three_col_2_tabi	= '';
					three_col_2_field	= ''; 	
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(three_col_1_field).attr('tabindex', tabindex);	
					jQuery(field).attr('tabindex', three_col_1_tabi);
					
					change_tabindex_radio(three_col_1_field, tabindex);
					change_tabindex_radio(field, three_col_1_tabi);						
					
					three_col_2_tabi	= three_col_1_tabi;
					three_col_1_tabi	= tabindex;				
					three_col_2_field	= item;
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_last_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(three_col_1_field).attr('tabindex', tabindex);	
					jQuery(three_col_2_field).attr('tabindex', three_col_1_tabi);	
					jQuery(field).attr('tabindex', three_col_2_tabi);
					
					change_tabindex_radio(three_col_1_field, tabindex);
					change_tabindex_radio(three_col_2_field, three_col_1_tabi);
					change_tabindex_radio(field, three_col_2_tabi);
					
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= ''; 
				} else {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex); 
				}			
				tabindex++;		
			}
		});		
	});
	}
});

jQuery(window).resize(function(){
var screenwidth = jQuery(window).width();
if(screenwidth >= 480)
{
	
	var tabindex = 2;
	jQuery("<?php echo $form_str?>").each(function(){
	var form = jQuery(this);
	var two_col_1_tabi 		= '';
	var two_col_1_field 	= '';
	var three_col_1_tabi 	= '';
	var three_col_1_field 	= '';
	var three_col_2_tabi	= '';
	var three_col_2_field	= '';
	
	jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function(e, item){
			var field = jQuery(this);
				field_id	= field.attr('name');
				field_type	= field.attr('type');
			if( field_id && ( field_id.indexOf('item_meta') != '-1' || field_id.indexOf('vpb_captcha_code') != '-1' ) )
			{
				if( jQuery(field).parents('.arfformfield').first().hasClass('frm_first_half') ){
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					
					two_col_1_tabi 	= tabindex;
					two_col_1_field = item;
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex);							
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_last_half') ){
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					
					jQuery(two_col_1_field).attr('tabindex', tabindex);		
					jQuery(field).attr('tabindex', two_col_1_tabi);			
					
					change_tabindex_radio(two_col_1_field, tabindex);	
					change_tabindex_radio(field, two_col_1_tabi);
						
					two_col_1_tabi 	= '';
					two_col_1_field = ''; 
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_first_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex);
										
					three_col_1_tabi  = tabindex;
					three_col_1_field = item;
					three_col_2_tabi	= '';
					three_col_2_field	= ''; 	
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(three_col_1_field).attr('tabindex', tabindex);	
					jQuery(field).attr('tabindex', three_col_1_tabi);
					
					change_tabindex_radio(three_col_1_field, tabindex);
					change_tabindex_radio(field, three_col_1_tabi);						
					
					three_col_2_tabi	= three_col_1_tabi;
					three_col_1_tabi	= tabindex;				
					three_col_2_field	= item;
				} else if( jQuery(field).parents('.arfformfield').first().hasClass('frm_last_third') ) {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					
					jQuery(three_col_1_field).attr('tabindex', tabindex);	
					jQuery(three_col_2_field).attr('tabindex', three_col_1_tabi);	
					jQuery(field).attr('tabindex', three_col_2_tabi);
					
					change_tabindex_radio(three_col_1_field, tabindex);
					change_tabindex_radio(three_col_2_field, three_col_1_tabi);
					change_tabindex_radio(field, three_col_2_tabi);
					
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= ''; 
				} else {
					two_col_1_tabi 		= '';
					two_col_1_field 	= '';
					three_col_1_tabi 	= '';
					three_col_1_field 	= '';
					three_col_2_tabi	= '';
					three_col_2_field	= '';
					jQuery(field).attr('tabindex', tabindex);
					change_tabindex_radio(field, tabindex); 
				}			
				tabindex++;		
			}
		});		
	});
} else {
	var tabindex = 2;
	jQuery("<?php echo $form_str?>").each(function(){	
		var form = jQuery(this);
		jQuery(form).find('input, textarea, select, .vpb_input_fields').each(function(e, item){
			var field = jQuery(this);
				field_id	= field.attr('name');
				field_type	= field.attr('type');
			if( field_id && ( field_id.indexOf('item_meta') != '-1' || field_id.indexOf('vpb_captcha_code') != '-1' ) )
			{
				field.attr("tabindex", tabindex);
				tabindex++;
			}
		});		
	});
}

});
<?php
}	

?>
if(typeof(__ARFERR)!='undefined'){
	var file_error = __ARFERR;
}else{
	var file_error = 'Sorry, this file type is not permitted for security reasons.';
}
	
<?php if( $preview != true ){ ?>
var form1chk = "";
jQuery(document).ready(function($){
var modalsCollection = document.querySelectorAll(".arfmodal");
var modalhtml = "";
var bodyhtml = "";
if(modalsCollection.length>0)
{
	for(var i=0;i<modalsCollection.length;i++)
	{
		var modal1 = modalsCollection[i].id;
		var checkmodal = modal1.split("-");
		if(checkmodal[0]=='popup' && checkmodal[1]=='form')
		{
			var attr_style = jQuery('#'+modal1).attr('style');
			var aria_hidden = jQuery('#'+modal1).attr('aria-hidden');
			var class_style = jQuery('#'+modal1).attr('class');
			modalhtml += '<div id="'+modal1+'" class="'+class_style+'" aria-hidden="'+aria_hidden+'" style="'+attr_style+'">';
			modalhtml+= jQuery('#'+modal1).html();
			modalhtml += '</div>';
			
			jQuery('#'+modal1).empty();
			jQuery('#'+modal1).removeAttr('class');
			jQuery('#'+modal1).removeAttr('aria-hidden');
			jQuery('#'+modal1).removeAttr('style');
			jQuery('#'+modal1).attr('style','display:none;');
			jQuery('#'+modal1).removeAttr('id');
		}
	}
	if(modalhtml!="")
	{
		jQuery('body').append(modalhtml);
	}
}


if(document.getElementsByClassName){
	var modalsCollection = document.getElementsByClassName("arf_flymodal");
}
else
{
	var modalsCollection = document.querySelectorAll(".arf_flymodal");
}
var modalhtml = "";
var bodyhtml = "";
var modal1 = '';
var checkmodal = '';
var attr_style = '';
var class_style = '';
if(modalsCollection.length>0)
{	
	for(var i=0;i<modalsCollection.length;i++)
	{
		modal1 = modalsCollection[i].id;
		checkmodal = modal1.split("-");
		if(checkmodal[0]=='arf' && checkmodal[1]=='popup' && checkmodal[2]=='form')
		{
			attr_style = jQuery('#'+modal1).attr('style');
			class_style = jQuery('#'+modal1).attr('class');
			modalhtml += '<div id="'+modal1+'" class="'+class_style+'" style="'+attr_style+'">';
			modalhtml += jQuery('#'+modal1).html();
			modalhtml += '</div>';
			
			jQuery('#'+modal1).empty();
			jQuery('#'+modal1).attr('style','display:none;');
			jQuery('#'+modal1).removeAttr('id');
		}
	}
	if(modalhtml!="")
	{
		jQuery('body').append(modalhtml);
	}
}

arfjqueryobj = new Object();
if (typeof $(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation == 'function') 
{
	arfjqueryobj = $;
}
else
{
	arfjqueryobj = jQuery.noConflict( true );
}
	
jQuery(document).ready(function(arfjqueryobj){

arfjqueryobj(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({ 
			submitSuccess: function ($form,event) {
						var form1  = jQuery($form).attr('id');
                                                var form_data_id = jQuery($form).attr('data-id');
                                                
                                                jQuery("form.arfshowmainform").each(function(){
                                                    if(jQuery(this).attr('data-id') == form_data_id){
                                                        

                                                        var object = jQuery(this);
                                                        var arf_form_id = jQuery('#'+form1).find("input[name='form_id']").attr('value'); 


                                                        var break_form_id = jQuery(object).find('input[name="form_id"]').val();
                                                        var break_val = jQuery(object).find('#submit_form_'+break_form_id).val(); 
                                                        var next_id = jQuery(object).find('#submit_form_'+break_form_id).attr('data-val');
                                                        var max_id = jQuery(object).find('#submit_form_'+break_form_id).attr('data-max');                                                

                                                        if(break_val == 1 ) {

                                                                event.preventDefault();
                                                                
                                                                var is_goto_next = false;
                                                                if(jQuery(object).find('#form_submit_type').val() == 1)
                                                                {					
                                                                        var upload_flag = 0;
                                                                        jQuery(object).find( ".original" ).each(function( index ) {
                                                                                var fileToUpload = jQuery(this).attr('data-file-valid');
                                                                                if(fileToUpload == 'false')
                                                                                {
                                                                                        var fileId = jQuery(this).attr('id');
                                                                                        var file = document.getElementById(fileId);

                                                                                                        var $this = jQuery('#'+fileId);
                                                                                                        var	$controlGroup = $this.parents(".control-group").first();
                                                                                                        var	$helpBlock = $controlGroup.find(".help-block").first();

                                                                                                        if( jQuery('#'+fileId).attr('data-invalid-message') !== undefined && jQuery('#'+fileId).attr('data-invalid-message') !='' ){
                                                                                                                var arf_invalid_file_message = jQuery('#'+fileId).attr('data-invalid-message');
                                                                                                        }else{
                                                                                                                var arf_invalid_file_message = file_error;
                                                                                                        }
                                                                                                        var form_id = $this.closest('form').find('#form_id').val();					
                                                                                                        var error_type =  ( jQuery('#form_tooltip_error_'+form_id).val() == 'advance' ) ? 'advance' : 'normal';

                                                                                                        if( error_type == 'advance' )
                                                                                                        {
                                                                                                                arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                                                                        } else {
                                                                                                                if(!$helpBlock.length) {
                                                                                                                        $helpBlock = jQuery('<div class="help-block"><ul><li>'+arf_invalid_file_message+'</li></ul></div>');
                                                                                                                        $controlGroup.find('.controls').append($helpBlock);
                                                                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                                                                }
                                                                                                                else
                                                                                                                {
                                                                                                                        $helpBlock = jQuery('<ul role="alert"><li>'+arf_invalid_file_message+'</li></ul>');
                                                                                                                        $controlGroup.find('.controls .help-block').append($helpBlock);
                                                                                                                        $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                                                                }	
                                                                                                        }								
                                                                                                        upload_flag++;								

                                                                                }
                                                                        });
                                                                        if(upload_flag > 0)
                                                                        {

                                                                                jQuery('#submit_loader').hide();
                                                                                jQuery(object).find('input[type="submit"]').show('');
                                                                                is_goto_next = false;
                                                                        }
                                                                        else
                                                                        {
                                                                                
                                                                                        if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
                                                                                        is_goto_next = false;

                                                                                        checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                                                        } else{
                                                                                                is_goto_next = true;
                                                                                        }
                                                                                
                                                                        }
                                                                }
                                                                else
                                                                {
                                                                                
                                                                                if(!(arf_validate_file(event,form1)))
                                                                                {
                                                                                        event.preventDefault();
                                                                                        is_goto_next = false;
                                                                                } 
                                                                                else
                                                                                {	
                                                                                        
                                                                                        if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
                                                                                        is_goto_next = false;

                                                                                        checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                                                        } else {
                                                                                                is_goto_next = true;
                                                                                        }
                                                                                        
                                                                                }	
                                                                }

                                                                if( next_id == max_id && is_goto_next == true ){
                                                                        jQuery(object).find('#submit_form_'+break_form_id).val('0');
                                                                        go_next(next_id, object);		
                                                                } else if( is_goto_next == true ) {
                                                                        next_id_new = parseInt(next_id) + parseInt(1);
                                                                        jQuery(object).find('#submit_form_'+break_form_id).attr('data-val', next_id_new);
                                                                        go_next(next_id, object);	
                                                                }

                                                                if( is_goto_next == true ) {
                                                                        jQuery(object).find('div').removeClass('arf_error');
                                                                        jQuery(object).find(".help-block").empty(); 
                                                                        jQuery(object).find('.frm_error_style').hide();
                                                                }
                                                                

                                                        } else {

                                                                        var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');

                                                                        if(checkwhichsubmit != 1)
                                                                        {
                                                                                var arf_is_prevalidate = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('value'); 
                                                                                var arf_is_prevalidate_form = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('data-validate');
                                                                                if( arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1 )
                                                                                {					
                                                                                        arf_is_validateform_outside( jQuery(object), event );
                                                                                        event.preventDefault();
                                                                                        return;
                                                                                }
                                                                                jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).val( arf_is_prevalidate_form );
                                                                                

                                                                                var arf_prevalidate = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('value'); 
                                                                                var arf_prevalidate_form = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('data-validate');
                                                                                if( arf_prevalidate == 1 && arf_prevalidate_form == 1 )
                                                                                {					
                                                                                        arf_validate_form_outside( jQuery(object), event );
                                                                                        event.preventDefault();
                                                                                        return;
                                                                                }
                                                                                jQuery(object).find("#arf_validate_outside_"+arf_form_id).val( arf_prevalidate_form );
                                                                        }

                                                                        var is_submit_enable = jQuery(object).find('.arf_submit_btn').attr('disabled');
                                                                        if( is_submit_enable == true || is_submit_enable == 'disabled' )
                                                                        {
                                                                                event.preventDefault();
                                                                                return;
                                                                        }
                                                                        
                                                                        if(jQuery(object).find('#form_submit_type').val() == 1)
                                                                        {
                                                                                event.preventDefault();					
                                                                                var upload_flag = 0;
                                                                                jQuery(object).find( ".original" ).each(function( index ) {
                                                                                        var fileToUpload = jQuery(this).attr('data-file-valid');
                                                                                        if(fileToUpload == 'false')
                                                                                        {
                                                                                                var fileId = jQuery(this).attr('id');
                                                                                                var file = document.getElementById(fileId);

                                                                                                if( jQuery('#'+fileId).attr('data-invalid-message') !== undefined && jQuery('#'+fileId).attr('data-invalid-message') !='' ){
                                                                                                        var arf_invalid_file_message = jQuery('#'+fileId).attr('data-invalid-message');
                                                                                                }else{
                                                                                                        var arf_invalid_file_message = file_error;
                                                                                                }
                                                                                                                var $this = jQuery('#'+fileId);
                                                                                                                var	$controlGroup = $this.parents(".control-group").first();
                                                                                                                var	$helpBlock = $controlGroup.find(".help-block").first();

                                                                                                                var form_id = $this.closest('form').find('#form_id').val();					
                                                                                                                var error_type =  ( jQuery('#form_tooltip_error_'+form_id).val() == 'advance' ) ? 'advance' : 'normal';

                                                                                                                if( error_type == 'advance' )
                                                                                                                {
                                                                                                                        arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
                                                                                                                } else {
                                                                                                                        if(!$helpBlock.length) {
                                                                                                                                $helpBlock = jQuery('<div class="help-block"><ul><li>'+arf_invalid_file_message+'</li></ul></div>');
                                                                                                                                $controlGroup.find('.controls').append($helpBlock);
                                                                                                                                $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                                $helpBlock = jQuery('<ul role="alert"><li>'+arf_invalid_file_message+'</li></ul>');
                                                                                                                                $controlGroup.find('.controls .help-block').append($helpBlock);
                                                                                                                                $controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
                                                                                                                        }	
                                                                                                                }
                                                                                                                upload_flag++;								

                                                                                        }
                                                                                });
                                                                                        if(upload_flag > 0)
                                                                                        {

                                                                                                jQuery('#submit_loader').hide();
                                                                                                jQuery(object).find('input[type="submit"]').show('');
                                                                                                is_goto_next = false;
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                                
                                                                                                        if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
                                                                                                        is_goto_next = false;

                                                                                                        checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                                                                        } else {


                                                                                                                if((jQuery('#form_submit_type').val() == 0 || jQuery('#form_submit_type').val() == 1) && jQuery(object).find('#vpb_captcha_code_'+break_form_id).length <= 0)
                                                                                                                {
                                                                                                                        if( jQuery(object).find('#recaptcha_style').length > 0 ){
                                                                                                                                jQuery(object).find('#recaptcha_style').html('  ');
                                                                                                                        }
                                                                                                                }

                                                                                                                jQuery(object).find('#previous_last').css('display', 'none');  	

                                                                                                                var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
                                                                                                                var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');

                                                                                                                if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
                                                                                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
                                                                                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
                                                                                                                } else {
                                                                                                                        jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
                                                                                                                }
                                                                                                                jQuery(object).find('.arf_submit_btn').attr('disabled', true);
                                                                                                                arfformsubmission(object,'<?php echo ARFSCRIPTURL ?>', 'yes');		
                                                                                                        }	
                                                                                                
                                                                                        }
                                                                                }
                                                                                else
                                                                                {
                                                                                        
                                                                                        if(!(arf_validate_file(event,form1)))
                                                                                        {
                                                                                                event.preventDefault();
                                                                                                is_goto_next = false;
                                                                                        } 
                                                                                        else
                                                                                        {	
                                                                                                
                                                                                                if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
                                                                                                is_goto_next = false;
                                                                                                event.preventDefault();	
                                                                                                checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'no');

                                                                                                } else {

                                                                                                        var is_submit_form = jQuery(object).find('#is_submit_form_'+break_form_id).val();

                                                                                                        if( is_submit_form == 1 ){

                                                                                                                if( jQuery(object).find('#recaptcha_style').length > 0 ){
                                                                                                                        jQuery(object).find('#recaptcha_style').html('  ');
                                                                                                                }

                                                                                                                jQuery(object).find('#is_submit_form_'+break_form_id).val('0');

                                                                                                                jQuery(object).find('#previous_last').css('display', 'none');  	

                                                                                                                var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
                                                                                                                var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');

                                                                                                                if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
                                                                                                                        jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
                                                                                                                        jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
                                                                                                                } else {
                                                                                                                        jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
                                                                                                                }
                                                                                                                jQuery(object).find('.arf_submit_btn').attr('disabled', true);

                                                                                                                jQuery(object).submit();		
                                                                                                        } 

                                                                                                }
                                                                                                
                                                                                        }
                                                                                }
                                                        }

                                                    }                                                        
                                                });
                        },
			submitError: function ($form,event) {
				var form1  = jQuery($form).attr('id');			
				var form_data_id = jQuery($form).attr('data-id');
                                
                                jQuery("form.arfshowmainform").each(function(){
                                    if(jQuery(this).attr('data-id') == form_data_id){
                                        object = jQuery(this);                                    
                                

                                        if( jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').length > 0 )
                                        {
                                                var scrolltop = jQuery( jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first() ).offset().top;
                                                jQuery(window.opera?'.arfmodal-body':'.arfmodal-body').animate({ scrollTop:jQuery(jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first() ).offset().top - jQuery(jQuery('.arfmodal-body').parent(object).find('.arfformfield').first() ).offset().top - 50 }, 'slow' ); 

                                                var tmp_div_id = jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error').first().attr('id');
                                                var tmp_field_id = jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
                                                jQuery('.arfmodal-body').parent(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
                                                if( jQuery('#'+tmp_field_id).is('select') ){
                                                        jQuery('.arfmodal-body').parent(object).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                }

                                                revalidate_focus(tmp_field_id, tmp_div_id);
                                        }	
                                        else if( jQuery(object).find('.arfformfield.arf_error').length > 0 ) 
                                        {
                                                jQuery(window.opera?'html, .arfmodal-body':'html, body, .arfmodal-body').animate({ scrollTop: jQuery( jQuery(object).find('.arfformfield.arf_error').first() ).offset().top-100 }, 'slow' ); 

                                                var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
                                                var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');

                                                jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();

                                                if( jQuery('#'+tmp_field_id).is('select') ){
                                                        jQuery(object).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                }

                                                revalidate_focus(tmp_field_id, tmp_div_id); 						

                                        }

                                        var checkwhichsubmit = jQuery('#'+form1).find("input[name='form_submit_type']").attr('value');
                                        if(checkwhichsubmit!=1)
                                        {
                                                if(!(arf_validate_file(event,form1)))
                                                {
                                                        event.preventDefault();
                                                }
                                        }
                                
                                    }                                                        
                                });                        
                        }

	});
});


});

	
<?php } ?>

var form1chk = "";
jQuery(document).ready(function($){
<?php if($preview==true){?>
var formpreview1 = $('.arfshowmainform').not('.arfpagebreakform').attr('id');
$('.arfshowmainform').not('.arfpagebreakform').find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({
			submitSuccess: function ($form,event) {
				var checkwhichsubmit = jQuery('.arfshowmainform').find("input[name='form_submit_type']").attr('value');
				
				object = jQuery('.arfshowmainform');
				var arf_form_id = jQuery(object).find("input[name='form_id']").attr('value'); 
				
				
				var arf_is_prevalidate = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('value'); 
				var arf_is_prevalidate_form = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('data-validate');
				if( arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1 )
				{					
					arf_is_validateform_outside( jQuery(object), event );
					event.preventDefault();
					return;
				}
				jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).val( arf_is_prevalidate_form );
				
				
				var arf_prevalidate = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('value'); 
				var arf_prevalidate_form = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('data-validate');				
				if( arf_prevalidate == 1 && arf_prevalidate_form == 1 )
				{					
					arf_validate_form_outside( jQuery(object), event );
					event.preventDefault();
					return;
				}
				jQuery(object).find("#arf_validate_outside_"+arf_form_id).val( arf_prevalidate_form );
				
				
				var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');
				
				var break_form_id = jQuery(object).find('input[name="form_id"]').val();
				var next_id = 0;
				var max_id = 0;
				var break_val = 0;
				event.preventDefault();	
				if(jQuery(object).find('#form_submit_type').val() == 1)
				{
					event.preventDefault();					
					var upload_flag = 0;
					jQuery( ".original" ).each(function( index ) {
						var fileToUpload = jQuery(this).attr('data-file-valid');
						if(fileToUpload == 'false')
						{
							var fileId = jQuery(this).attr('id');
							var file = document.getElementById(fileId);
							
							if( jQuery('#'+fileId).attr('data-invalid-message') !== undefined && jQuery('#'+fileId).attr('data-invalid-message') !='' ){	
								var arf_invalid_file_message = jQuery('#'+fileId).attr('data-invalid-message');
							}else{
								var arf_invalid_file_message = file_error;
							}			
									var $this = jQuery('#'+fileId);
									var	$controlGroup = $this.parents(".control-group").first();
									var	$helpBlock = $controlGroup.find(".help-block").first();
									
									var form_id = $this.closest('form').find('#form_id').val();					
									var error_type =  ( jQuery('#form_tooltip_error_'+form_id).val() == 'advance' ) ? 'advance' : 'normal';
										
									if( error_type == 'advance' )
									{
										arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
									} else {
										if(!$helpBlock.length) {
											$helpBlock = jQuery('<div class="help-block"><ul><li>'+arf_invalid_file_message+'</li></ul></div>');
											$controlGroup.find('.controls').append($helpBlock);
											$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
										}
										else
										{
											$helpBlock = jQuery('<ul role="alert"><li>'+arf_invalid_file_message+'</li></ul>');
											$controlGroup.find('.controls .help-block').append($helpBlock);
											$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
										}	
									}
									upload_flag++;								
								
						}
					});
						if(upload_flag > 0)
						{
							jQuery('#submit_loader').hide();
							jQuery(object).find('input[type="submit"]').show('');
							is_goto_next = false;
						}
						else
						{
							
								if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
								is_goto_next = false;
								
								checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																			
								} else {
									jQuery(object).find('#previous_last').css('display', 'none'); 
									
									var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
									var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
									
									if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
										jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
										jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
									} else {
										jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
									}
									jQuery(object).find('.arf_submit_btn').attr('disabled', true);
																
									setTimeout(function(){ 
										
										jQuery('#form_success_'+break_form_id).show(); 
										jQuery('html, body').animate({ scrollTop:jQuery('#message_success')}, 'slow' ); 
										jQuery('#form_<?php echo $form->form_key; ?>').show(); 
										jQuery(object).find('input[type="submit"]').removeAttr('style'); 
										jQuery(object).find('div').removeClass('arfblankfield'); jQuery(".help-block").empty(); 
										jQuery('#hexagon').css('display', 'none'); 
																														
										jQuery(object).find('.arf_submit_btn').attr('disabled', false);
																																																	
										var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value'); 
										reloadcapcha(object, captcha_key);
											
										var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_'+break_form_id+'"]').val();
										if( is_formreset == 1 )
										{
											jQuery('#form_<?php echo $form->form_key; ?>').trigger("reset");
											
											jQuery('.rate_widget').each(function(i) {
												var widget_id = jQuery(this).attr('id');
												var widget = jQuery(this);
												set_votes(widget, widget_id);	
											});
											jQuery(object).find('.progress, .arf_info').hide(); 
											jQuery(object).find('.arfajax-file-upload').show();
											jQuery(object).find('.ajax-file-remove').hide();
											jQuery(object).find('.arfajax-file-upload, .ajax-file-remove').each(function(){
												jQuery(this).css('margin-top',"0px");										   
											});	
											
											if( jQuery.isFunction( jQuery().selectpicker ) ){
												object.find('select').selectpicker('render');
											}
																							
											reset_like_field(object); 
											reset_slider_field(object);
											reset_running_total(object);
											reset_colorpicker(object);	
                                                                                        reset_datetimepicker(object);

											if( typeof reset_preview_out_side == 'function' ){
												reset_preview_out_side('<?php echo json_encode(array('id'=> $form->id, 'form_key' => $form->form_key)); ?>',object);
											}
 							
											if(typeof(__ARFSTRRNTH_INDICATOR)!='undefined'){
												var strenth_indicator = __ARFSTRRNTH_INDICATOR;
											}else{
												var strenth_indicator = 'Strength indicator';
											}
											jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
											jQuery(object).find('.arf_strenth_mtr .inside_title').html( strenth_indicator );
							
											jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function(i){
												jQuery(this).attr("checked", jQuery(this).is(':checked'));
												if( jQuery(this).is(':checked') ){
													jQuery(this).parent('div').addClass('checked');
												} else {
													jQuery(this).parent('div').removeClass('checked');											
												}
											});
											jQuery(object).find('input').iCheck('update');
										}
										
										arf_reset_page_nav();
										if( typeof arf_rule_apply_bulk == 'function' ){
											arf_rule_apply_bulk('<?php echo $form->form_key; ?>');	
										}
											
										
										var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_'+break_form_id+'"]').val();
										if( is_formreset_outside == 1 )
										{
											arf_resetform_outside(object, break_form_id);
										}
						
										var arf_data_validate = jQuery(object).find("#arf_validate_outside_"+break_form_id).attr('data-validate');
										jQuery(object).find("#arf_validate_outside_"+break_form_id).val(arf_data_validate);
										
										var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
										var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
												
										if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
											jQuery(object).find('.arf_submit_btn .arfstyle-label').show();	
											jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();	
										} else {
											jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
										} 
                                                                                arf_success_message_show_time = jQuery(object).find('#arf_success_message_show_time_'+break_form_id).val();
                                                                       
                                                                                if(!arf_success_message_show_time>0)
                                                                                {
                                                                                    arf_success_message_show_time=3;
                                                                                }
                                                                                
                                                                                if(arf_success_message_show_time!=0)
                                                                                {

                                                                                    arf_success_message_show_time= arf_success_message_show_time*1000;

                                                                                    setTimeout(function () {
                                                                                        jQuery('#form_success_'+break_form_id).hide("slow");
                                                                                    }, arf_success_message_show_time);

                                                                                }
									},3000);		 		
									
								}	
							
						}
					}
					else
					{
						
						if(!(arf_validate_file(event,formpreview1)))
						{
							event.preventDefault();
							is_goto_next = false;
						} 
						else
						{	
							
							if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
							is_goto_next = false;
							event.preventDefault();	
							checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																		
							} else {
								var is_submit_form = jQuery('#is_submit_form_'+break_form_id).val();
								
								if( is_submit_form == 1 ){
																					
									jQuery('#is_submit_form_'+break_form_id).val('0');
									jQuery(object).find('.arf_submit_btn').attr('disabled', true);
									
									jQuery(object).find('#previous_last').css('display', 'none'); 
									
									var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
									var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
									
									if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
										jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
										jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
									} else {
										jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
									}
																		
									setTimeout(function(){ 
										 
										jQuery('#form_success_'+break_form_id).show(); 
										jQuery('html, body').animate({ scrollTop:jQuery('#message_success')}, 'slow' ); 
										jQuery('#form_<?php echo $form->form_key; ?>').show(); 
										jQuery(object).find('input[type="submit"]').removeAttr('style'); 
										jQuery(object).find('div').removeClass('arfblankfield'); 
										jQuery(".help-block").empty(); jQuery('#hexagon').css('display', 'none'); 
										
										
										var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value'); 
										reloadcapcha(object, captcha_key); 
																		
									jQuery(object).find('.arf_submit_btn').attr('disabled', false);
																											
									var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_'+break_form_id+'"]').val();
									if( is_formreset == 1 )
									{
										jQuery('#form_<?php echo $form->form_key; ?>').trigger("reset"); 
										jQuery(object).find('input').iCheck('update'); 
										jQuery(object).find('.progress, .arf_info').hide();
										
										if( jQuery.isFunction( jQuery().selectpicker ) ){
											object.find('select').selectpicker('render');
										}
												
										reset_like_field(object);
										reset_slider_field(object);
										reset_running_total(object);
										reset_colorpicker(object);
                                                                                reset_datetimepicker(object)
										
										if( typeof reset_preview_out_side == 'function' ){
												reset_preview_out_side('<?php echo json_encode(array('id'=> $form->id, 'form_key' => $form->form_key)); ?>',object);
										}										
										
										if(typeof(__ARFSTRRNTH_INDICATOR)!='undefined'){
											var strenth_indicator = __ARFSTRRNTH_INDICATOR;
										}else{
											var strenth_indicator = 'Strength indicator';
										}
										jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
										jQuery(object).find('.arf_strenth_mtr .inside_title').html( strenth_indicator );
							
										jQuery('.rate_widget').each(function(i) {
											var widget_id = jQuery(this).attr('id');
											var widget = jQuery(this);
											set_votes(widget, widget_id);	
										});
										jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function(i){
											jQuery(this).attr("checked", jQuery(this).is(':checked'));
											if( jQuery(this).is(':checked') ){
												jQuery(this).parent('div').addClass('checked');
											} else {
												jQuery(this).parent('div').removeClass('checked');											
											}
										});
										jQuery(object).find('.original_normal').each(function(){
											var field_key	= jQuery(this).attr('id');
												field_key 	= field_key.replace('field_', '');
											jQuery('#file_name_'+field_key).text('<?php _e('No file selected', 'ARForms');?>');	
										});
									}
									
									jQuery('#is_submit_form_'+break_form_id).val('1');
									arf_reset_page_nav();
									
									if( typeof arf_rule_apply_bulk == 'function' ){
										arf_rule_apply_bulk('<?php echo $form->form_key; ?>');	
									}
									
									var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_'+break_form_id+'"]').val();
									if( is_formreset_outside == 1 )
									{
										arf_resetform_outside(object, break_form_id);
									}
												
									var arf_data_validate = jQuery(object).find("#arf_validate_outside_"+break_form_id).attr('data-validate');
									jQuery(object).find("#arf_validate_outside_"+break_form_id).val(arf_data_validate);
									
									var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
									var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
											
									if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
										jQuery(object).find('.arf_submit_btn .arfstyle-label').show();	
										jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();	
									} else {
										jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
									} 
                                                                        arf_success_message_show_time = jQuery(object).find('#arf_success_message_show_time_'+break_form_id).val();
                                                                        if(!arf_success_message_show_time>0)
                                                                        {
                                                                            arf_success_message_show_time=3;
                                                                        }
                                                                        if(arf_success_message_show_time!=0)
                                                                        {
                                                                            arf_success_message_show_time= arf_success_message_show_time*1000;
                                                                            
                                                                            setTimeout(function () {
                                                                                jQuery('#form_success_'+break_form_id).hide("slow");
                                                                            }, arf_success_message_show_time);

                                                                        }
											
									},3000);		 	 
									
                                                                        
									
								} 
									
							}
							
						}
					}				
				
			},
			submitError: function ($form,event) {
				object = jQuery('.arfshowmainform');
				if( jQuery(object).find('.arfformfield.arf_error').length > 0 ) {
					jQuery('html, body').animate({ scrollTop: jQuery( jQuery(object).find('.arfformfield.arf_error').first() ).offset().top-100 }, 'slow'); 
				}
					var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
					var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
										
					jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
					
					if( jQuery('#'+tmp_field_id).is('select') ){
						jQuery(object).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
					}
					revalidate_focus(tmp_field_id, tmp_div_id); 					
										
				event.preventDefault();		
			}
		});

var formpreview = $('.arfpagebreakform').attr('id');
var formpreviewid = $('#'+formpreview);
$('.arfpagebreakform').find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation({ 
			submitSuccess: function ($form,event) {
						
						event.preventDefault();
							
						object = jQuery('.arfpagebreakform');
						
						var arf_form_id = jQuery(object).find("input[name='form_id']").attr('value');
						
												
						var break_form_id = jQuery(object).find('input[name="form_id"]').val();
						var break_val = jQuery('#submit_form_'+break_form_id).val(); 
						var next_id = jQuery('#submit_form_'+break_form_id).attr('data-val');
						var max_id = jQuery('#submit_form_'+break_form_id).attr('data-max');
												
						if(break_val == 1 ) {
						
							event.preventDefault();
							
							var is_goto_next = false;
							if(jQuery(object).find('#form_submit_type').val() == 1)
							{					
								var upload_flag = 0;
								jQuery( ".original" ).each(function( index ) {
									var fileToUpload = jQuery(this).attr('data-file-valid');
									if(fileToUpload == 'false')
									{
										var fileId = jQuery(this).attr('id');
										var file = document.getElementById(fileId);
										
										if( jQuery('#'+fileId).attr('data-invalid-message') !== undefined && jQuery('#'+fileId).attr('data-invalid-message') !='' ){
											var arf_invalid_file_message = jQuery('#'+fileId).attr('data-invalid-message');
										}else{
											var arf_invalid_file_message = file_error;
										}
												var $this = jQuery('#'+fileId);
												var	$controlGroup = $this.parents(".control-group").first();
												var	$helpBlock = $controlGroup.find(".help-block").first();
												
												var form_id = $this.closest('form').find('#form_id').val();					
												var error_type =  ( jQuery('#form_tooltip_error_'+form_id).val() == 'advance' ) ? 'advance' : 'normal';
													
												if( error_type == 'advance' )
												{
													arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
												} else {
													if(!$helpBlock.length) {
														$helpBlock = jQuery('<div class="help-block"><ul><li>'+arf_invalid_file_message+'</li></ul></div>');
														$controlGroup.find('.controls').append($helpBlock);
														$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
													}
													else
													{
														$helpBlock = jQuery('<ul role="alert"><li>'+arf_invalid_file_message+'</li></ul>');
														$controlGroup.find('.controls .help-block').append($helpBlock);
														$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
													}	
												}
												upload_flag++;								
											
									}
								});
								if(upload_flag > 0)
								{
									jQuery('#submit_loader').hide();
									jQuery(object).find('input[type="submit"]').show('');
									is_goto_next = false;
								}
								else
								{
									
										if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
										is_goto_next = false;
										
										checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																					
										} else {
											is_goto_next = true;
										}
									
								}
							}
							else
							{
									
									if(!(arf_validate_file(event,formpreview)))
									{
										event.preventDefault();
										is_goto_next = false;
									} 
									else
									{	
										
										if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
										is_goto_next = false;
										
										checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																					
										} else {
											is_goto_next = true;
										}
										
									}	
							}
							
							if( next_id == max_id && is_goto_next == true ){
								jQuery('#submit_form_'+break_form_id).val('0');
								go_next(next_id, object);	
							} else if( is_goto_next == true ) {
								next_id_new = parseInt(next_id) + parseInt(1);
								jQuery('#submit_form_'+break_form_id).attr('data-val', next_id_new);
								go_next(next_id, object);	
							}
							
							if( is_goto_next == true ) {
								jQuery(object).find('div').removeClass('error');
								jQuery(object).find(".help-block").empty(); 
								jQuery(object).find('.frm_error_style').hide();
							}
							

						} else {
						
							
							var arf_is_prevalidate = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('value'); 
							var arf_is_prevalidate_form = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('data-validate');
							if( arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1 )
							{					
								arf_is_validateform_outside( jQuery(object), event );
								event.preventDefault();
								return;
							}
							jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).val( arf_is_prevalidate_form );
							
					 
							var arf_prevalidate = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('value'); 
							var arf_prevalidate_form = jQuery(object).find("#arf_validate_outside_"+arf_form_id).attr('data-validate');
							
							if( arf_prevalidate == 1 && arf_prevalidate_form == 1 )
							{					
								arf_validate_form_outside( jQuery(object), event );
								event.preventDefault();
								return;
							}
							jQuery(object).find("#arf_validate_outside_"+arf_form_id).val( arf_prevalidate_form );
						
								var checkwhichsubmit = jQuery(object).find("input[name='form_submit_type']").attr('value');
								
								var is_submit_enable = jQuery(object).find('.arf_submit_btn').attr('disabled');
								if( is_submit_enable == true || is_submit_enable == 'disabled' )
								{
									event.preventDefault();
									return;
								}
				
								if(jQuery(object).find('#form_submit_type').val() == 1)
								{
									event.preventDefault();					
									var upload_flag = 0;
									jQuery( ".original" ).each(function( index ) {
										var fileToUpload = jQuery(this).attr('data-file-valid');
										if(fileToUpload == 'false')
										{
											var fileId = jQuery(this).attr('id');
											var file = document.getElementById(fileId);
											
											if( jQuery('#'+fileId).attr('data-invalid-message') !== undefined && jQuery('#'+fileId).attr('data-invalid-message') !='' )	{
												var arf_invalid_file_message = jQuery('#'+fileId).attr('data-invalid-message');
											}else{
												var arf_invalid_file_message = file_error;
											}
													var $this = jQuery('#'+fileId);
													var	$controlGroup = $this.parents(".control-group").first();
													var	$helpBlock = $controlGroup.find(".help-block").first();
													
													var form_id = $this.closest('form').find('#form_id').val();					
													var error_type =  ( jQuery('#form_tooltip_error_'+form_id).val() == 'advance' ) ? 'advance' : 'normal';
														
													if( error_type == 'advance' )
													{
														arf_show_tooltip($controlGroup, $helpBlock, arf_invalid_file_message);
													} else {
														if(!$helpBlock.length) {
															$helpBlock = jQuery('<div class="help-block"><ul><li>'+arf_invalid_file_message+'</li></ul></div>');
															$controlGroup.find('.controls').append($helpBlock);
															$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
														}
														else
														{
															$helpBlock = jQuery('<ul role="alert"><li>'+arf_invalid_file_message+'</li></ul>');
															$controlGroup.find('.controls .help-block').append($helpBlock);
															$controlGroup.find('.controls .help-block').removeClass('arfanimated bounceInDownNor').addClass('arfanimated bounceInDownNor');
														}	
													}	
													upload_flag++;								
												
										}
									});
										if(upload_flag > 0)
										{
											jQuery('#submit_loader').hide();
											jQuery(object).find('input[type="submit"]').show('');
											is_goto_next = false;
										}
										else
										{
											
												if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
												is_goto_next = false;
												
												checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																							
												} else {
													jQuery(object).find('#previous_last').css('display', 'none'); 
				    								
													var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
													var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
													
													if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
														jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
														jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
													} else {
														jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
													}
													jQuery(object).find('.arf_submit_btn').attr('disabled', true);
																		
													setTimeout(function(){ 
														
														jQuery('#form_success_'+break_form_id).show(); jQuery('html, body').animate({ scrollTop:jQuery('#message_success')}, 'slow' ); 
														jQuery('#form_<?php echo $form->form_key; ?>').show(); 
														jQuery(object).find('input[type="submit"]').removeAttr('style'); 
														jQuery(object).find('div').removeClass('arfblankfield'); jQuery(".help-block").empty(); 
														jQuery(object).find('#previous_last').show('');	
														jQuery(object).find('.arf_submit_btn').attr('disabled', false);														
														go_previous('0', break_form_id, 'yes', '<?php echo $form->form_key; ?>', '0');
														arf_reset_page_nav();
														
													var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_'+break_form_id+'"]').val();
													if( is_formreset == 1 )
													{	
														jQuery('#form_<?php echo $form->form_key; ?>').trigger("reset");														
														jQuery(object).find('.progress, .arf_info').hide(); 
														jQuery(object).find('input').iCheck('update');
														jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function(i){
															jQuery(this).attr("checked", jQuery(this).is(':checked'));
															if( jQuery(this).is(':checked') ){
																jQuery(this).parent('div').addClass('checked');
															} else {
																jQuery(this).parent('div').removeClass('checked');											
															}
														});
																												
														if( jQuery.isFunction( jQuery().selectpicker ) ){
															object.find('select').selectpicker('render');
														}
												
														reset_like_field(object);
														reset_slider_field(object);
														reset_running_total(object);
														reset_colorpicker(object);
                                                                                                                reset_datetimepicker(object)
														
														if( typeof reset_preview_out_side == 'function' ){
															reset_preview_out_side('<?php echo json_encode(array('id'=> $form->id, 'form_key' => $form->form_key)); ?>',object);
														}
																																						
														if(typeof(__ARFSTRRNTH_INDICATOR)!='undefined'){
															var strenth_indicator = __ARFSTRRNTH_INDICATOR;
														}else{
															var strenth_indicator = 'Strength indicator';
														}
														jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
														jQuery(object).find('.arf_strenth_mtr .inside_title').html( strenth_indicator );
													
													}
													
													if( typeof arf_rule_apply_bulk == 'function' ){
														arf_rule_apply_bulk('<?php echo $form->form_key; ?>');	
													}
													
													var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_'+break_form_id+'"]').val();
													if( is_formreset_outside == 1 )
													{
														arf_resetform_outside(object, break_form_id);
													}
									
														var arf_data_validate = jQuery(object).find("#arf_validate_outside_"+break_form_id).attr('data-validate');
														jQuery(object).find("#arf_validate_outside_"+break_form_id).val(arf_data_validate); 
														
														if(jQuery(object).find('#vpb_captcha_code_'+break_form_id).length > 0)
														{
															jQuery(object).find("#vpb_captcha_code_"+break_form_id).val('');
															jQuery(object).find("#vpb_captcha_"+break_form_id).addClass('control-group');
															jQuery(object).find('#captchaimg_'+break_form_id).attr('src',jQuery(object).find('#captchaimg_'+break_form_id).attr('src').substring(0,jQuery(object).find('#captchaimg_'+break_form_id).attr('src').lastIndexOf("?"))+"?rand="+Math.random()*1000+"&form_id="+break_form_id);							
														}
														else
														{
															var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value'); 
															reloadcapcha(object, captcha_key); 
														}														
														
														var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
														var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
														
														if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
															jQuery(object).find('.arf_submit_btn .arfstyle-label').show();	
															jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();	
														} else {
															jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
														}
											 
													},3000);		 		
													setTimeout(function(){jQuery('#form_success_'+break_form_id).hide("slow");},6000);
												}	
											
										}
									}
									else
									{
										
										if(!(arf_validate_file(event,formpreview)))
										{
											event.preventDefault();
											is_goto_next = false;
										} 
										else
										{	
											
											if ( jQuery(object).find("#recaptcha_style").length > 0 && jQuery(object).find("#recaptcha_style").is(":visible") == true ) {
											is_goto_next = false;
											event.preventDefault();	
											checkRecaptcha(object,'<?php echo ARFSCRIPTURL ?>', next_id, max_id, break_val, 'yes');
																						
											} else {
												var is_submit_form = jQuery('#is_submit_form_'+break_form_id).val();
												
												if( is_submit_form == 1 ){
																									
													jQuery('#is_submit_form_'+break_form_id).val('0');
													jQuery(object).find('#previous_last').css('display', 'none'); 
													jQuery(object).find('.arf_submit_btn').attr('disabled', true);
									
													var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
													var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
													
													if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
														jQuery(object).find('.arf_submit_btn .arfstyle-label').hide();	
														jQuery(object).find('.arf_submit_btn .arf_ie_image').css('display', 'inline-block');	
													} else {
														jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
													}
									
													setTimeout(function(){ 
														 
														jQuery('#form_success_'+break_form_id).show(); 
														jQuery('html, body').animate({ scrollTop:jQuery('#message_success')}, 'slow' ); 
														jQuery('#form_<?php echo $form->form_key; ?>').show(); 
														jQuery(object).find('input[type="submit"]').removeAttr('style'); 
														jQuery(object).find('div').removeClass('arfblankfield'); 
														jQuery(".help-block").empty(); 
														jQuery('#hexagon').css('display', 'none'); 
														go_previous('0', break_form_id, 'yes', '<?php echo $form->form_key; ?>', '0');
														arf_reset_page_nav();
														
													var is_formreset = jQuery(object).find('input[name="arf_is_resetform_aftersubmit_'+break_form_id+'"]').val();
													if( is_formreset == 1 )
													{
														
														jQuery('#form_<?php echo $form->form_key; ?>').trigger("reset");
														jQuery(object).find('.progress, .arf_info').hide();
														jQuery(object).find('input').iCheck('update');
														jQuery(object).find('input[type="checkbox"], input[type="radio"]').not('.arf_hide_opacity').each(function(i){
															jQuery(this).attr("checked", jQuery(this).is(':checked'));
															if( jQuery(this).is(':checked') ){
																jQuery(this).parent('div').addClass('checked');
															} else {
																jQuery(this).parent('div').removeClass('checked');											
															}
														});
														
														if( jQuery.isFunction( jQuery().selectpicker ) ){
															object.find('select').selectpicker('render');
														}
															
														reset_like_field(object);
														reset_slider_field(object);
														reset_running_total(object);
														reset_colorpicker(object);
                                                                                                                reset_datetimepicker(object)
														
														if( typeof reset_preview_out_side == 'function' ){
															reset_preview_out_side('<?php echo json_encode(array('id'=> $form->id, 'form_key' => $form->form_key)); ?>',object);
														}	
														
														if(typeof(__ARFSTRRNTH_INDICATOR)!='undefined'){
															var strenth_indicator = __ARFSTRRNTH_INDICATOR;
														}else{
															var strenth_indicator = 'Strength indicator';
														}
															
														jQuery(object).find('.arf_strenth_meter').removeClass('short bad good strong');
														jQuery(object).find('.arf_strenth_mtr .inside_title').html( strenth_indicator );
														jQuery(object).find('.arf_submit_btn').attr('disabled', false);
														
														jQuery(object).find('.original_normal').each(function(){
															var field_key	= jQuery(this).attr('id');
																field_key 	= field_key.replace('field_', '');
															jQuery('#file_name_'+field_key).text('<?php _e('No file selected', 'ARForms');?>');	
														});
									
													}
													
													if( typeof arf_rule_apply_bulk == 'function' ){
														arf_rule_apply_bulk('<?php echo $form->form_key; ?>');	
													}
													
													var is_formreset_outside = jQuery(object).find('input[name="arf_is_resetform_outside_'+break_form_id+'"]').val();
													if( is_formreset_outside == 1 )
													{
														arf_resetform_outside(object, break_form_id);
													}
													
													jQuery('#is_submit_form_'+break_form_id).val('1');
													
														var arf_data_validate = jQuery(object).find("#arf_validate_outside_"+break_form_id).attr('data-validate');
														jQuery(object).find("#arf_validate_outside_"+break_form_id).val(arf_data_validate); 
														
														if(jQuery(object).find('#vpb_captcha_code_'+break_form_id).length > 0)
														{
															jQuery(object).find("#vpb_captcha_code_"+break_form_id).val('');
															jQuery(object).find("#vpb_captcha_"+break_form_id).addClass('control-group');
															jQuery(object).find('#captchaimg_'+break_form_id).attr('src',jQuery(object).find('#captchaimg_'+break_form_id).attr('src').substring(0,jQuery(object).find('#captchaimg_'+break_form_id).attr('src').lastIndexOf("?"))+"?rand="+Math.random()*1000+"&form_id="+break_form_id);							
														}
														else
														{
															var captcha_key = jQuery(object).find('input[name="field_captcha"]').attr('value'); 
															reloadcapcha(object, captcha_key); 
														} 
														
														var arf_bowser_name 	= jQuery(object).find('#arf_browser_name').val();
														var arf_bowser_version 	= jQuery(object).find('#arf_browser_name').attr('data-version');
														
														if( arf_bowser_name == 'Opera' || ( arf_bowser_name == 'Internet Explorer' && arf_bowser_version <= 9 ) ){
															jQuery(object).find('.arf_submit_btn .arfstyle-label').show();	
															jQuery(object).find('.arf_submit_btn .arf_ie_image').hide();	
														} else {
															jQuery(object).find('.arf_submit_btn').toggleClass('data-loading');	
														} 
													},3000);		 	 
													setTimeout(function(){jQuery('#form_success_'+break_form_id).hide("slow");},6000);
													
												} 
													
											}
											
										}
									}
						}
						
			},
			submitError: function (formpreviewid,event) {
				
				object = jQuery('.arfshowmainform');
				if( jQuery(object).find('.arfformfield.arf_error').length > 0 ) 
				{
					jQuery('html, body').animate({ scrollTop: jQuery( jQuery(object).find('.arfformfield.arf_error').first() ).offset().top-100 }, 'slow' ); 
				}
					
					var tmp_div_id = jQuery(object).find('.arfformfield.arf_error').first().attr('id');
					var tmp_field_id = jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
										
					jQuery(object).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
					
					if( jQuery('#'+tmp_field_id).is('select') ){
						jQuery(object).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
					}
					revalidate_focus(tmp_field_id, tmp_div_id); 						
					
				
				var checkwhichsubmit = jQuery('#'+formpreview).find("input[name='form_submit_type']").attr('value');
				if(checkwhichsubmit!=1)
				{
					if(!(arf_validate_file(event,formpreview)))
					{
						event.preventDefault();
					}
				}
			}

	});		
		
<?php }else{?>

var flagdata = "";

jQuery(document).ready(function(arfjqueryobj){

arfjqueryobj(".arfshowmainform").not(".arfpagebreakform").find("input,select,textarea").not("[type=submit],div[id='recaptcha_style'] input").jqBootstrapValidation( 
	   {
			submitSuccess: function ($form,event) {
				var form1  = jQuery($form).attr('id');
				
				var object = jQuery('#'+form1);
                                
                                var form_data_id = jQuery($form).attr('data-id');
                                                                
				var arf_form_id = jQuery('#'+form1).find("input[name='form_id']").attr('value'); 
				
					var checkwhichsubmit = jQuery('#'+form1).find("input[name='form_submit_type']").attr('value');
					if(checkwhichsubmit==1)
					{
                                                event.preventDefault();
                                                jQuery("form.arfshowmainform").each(function(){
                                                    if(jQuery(this).attr('data-id') == form_data_id){
                                                       arfgetformerrors_new(jQuery(this),'<?php echo ARFSCRIPTURL ?>', event);
                                                       return false;
                                                    }
                                                });
						
					}
					else
					{
						
						var arf_is_prevalidate = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).val();  
					
						var arf_is_prevalidate_form = jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).attr('data-validate');
						if( arf_is_prevalidate == 1 && arf_is_prevalidate_form == 1 )
						{					
							arf_is_validateform_outside( jQuery(object), event );
							event.preventDefault();
							return;
						}

                                                jQuery(object).find("#arf_is_validate_outside_"+arf_form_id).val( arf_is_prevalidate );
						var arf_prevalidate = jQuery('#'+form1).find("#arf_validate_outside_"+arf_form_id).val();
					
						var arf_prevalidate_form = jQuery('#'+form1).find("#arf_validate_outside_"+arf_form_id).attr('data-validate');
						
						if( arf_prevalidate == 1 && arf_prevalidate_form == 1 )
						{					
							arf_validate_form_outside( jQuery('#'+form1), event );
							event.preventDefault();
							return;
						}
						jQuery(object).find("#arf_validate_outside_"+arf_form_id).val( arf_prevalidate_form );
					
						
						if(!(arf_validate_file(event,form1)))
						{
							event.preventDefault();
						}
						else
						{       
                                                        jQuery("form.arfshowmainform").each(function(){
                                                            if(jQuery(this).attr('data-id') == form_data_id){
                                                                arfgetformerrors_new(jQuery(this),'<?php echo ARFSCRIPTURL ?>', event);
                                                                return false;
                                                            }
                                                        
                                                    });
                                                        
						}	
					}
				
  			},
			submitError: function ($form ,event, $inputs) {
				var form1  = jQuery($form).attr('id');
				var object = jQuery('#'+form1);
                                var form_data_id = jQuery($form).attr('data-id');                                
                                
                                jQuery("form.arfshowmainform").each(function(){
                                    if(jQuery(this).attr('data-id') == form_data_id){
                                        var object1 = jQuery(this);
                                        
                                        if( jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').length > 0 )
                                        {
                                                var scrolltop = jQuery( jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first() ).offset().top;
                                                jQuery(window.opera?'.arfmodal-body':'.arfmodal-body').animate({ scrollTop:jQuery(jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first() ).offset().top - jQuery(jQuery('.arfmodal-body').parent(object1).find('.arfformfield').first() ).offset().top - 50 }, 'slow' ); 

                                                var tmp_div_id = jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error').first().attr('id');
                                                var tmp_field_id = jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');
                                                jQuery('.arfmodal-body').parent(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();
                                                if( jQuery('#'+tmp_field_id).is('select') ){
                                                        jQuery('.arfmodal-body').parent(object1).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                }
                                                revalidate_focus(tmp_field_id, tmp_div_id, object1); 					

                                        }	
                                        else if( jQuery(object1).find('.arfformfield.arf_error').length > 0 ) 
                                        {
                                                jQuery(window.opera?'html, .arfmodal-body':'html, body, .arfmodal-body').animate({ scrollTop: jQuery( jQuery(object1).find('.arfformfield.arf_error').first() ).offset().top-100 }, 'slow' ); 

                                                var tmp_div_id = jQuery(object1).find('.arfformfield.arf_error').first().attr('id');
                                                var tmp_field_id = jQuery(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().attr('id');

                                                jQuery(object1).find('.arfformfield.arf_error input, .arfformfield.arf_error select, .arfformfield.arf_error textarea').first().focus();

                                                if( jQuery('#'+tmp_field_id).is('select') ){
                                                        jQuery(object1).find('#'+tmp_div_id+' .dropdown-toggle[data-toggle="arfdropdown"]').focus();
                                                }
                                                revalidate_focus(tmp_field_id, tmp_div_id); 					

                                        }
                                        var checkwhichsubmit = jQuery('#'+form1).find("input[name='form_submit_type']").attr('value');
                                        if(checkwhichsubmit!=1)
                                        {
                                                if(!(arf_validate_file(event,form1)))
                                                {
                                                        event.preventDefault();
                                                }
                                        }
                                    }
                                });				
			}
			
			
		});
});



jQuery("#main_form_flag_mange").html(flagdata);	
<?php } ?>
<?php

if(!empty($arfhiddenfields) or (!empty($arfdatepickerloaded) and is_array($arfdatepickerloaded))

or (isset($load_lang) and !empty($load_lang)) or !empty($arftimepickerloaded) or !empty($arfcalcfields)){


if(!empty($arfdatepickerloaded) and is_array($arfdatepickerloaded)){

    global $style_settings; 

    $load_lang = array();

    reset($arfdatepickerloaded);

    $datepicker = key($arfdatepickerloaded); 

    

foreach($arfdatepickerloaded as $date_field_id => $options){ ?>  

<?php 
	$show_year_month_calendar = "true";
	if($options['show_year_month_calendar']<1) { $show_year_month_calendar = "false"; } 
        
        $show_time_calendar = "true";
	if($options['show_time_calendar']<1) { $show_time_calendar = "false"; }
        
        $arf_show_min_current_date = "true";
	if($options['arf_show_min_current_date']<1) { $arf_show_min_current_date = "false"; }
        
        if ($arf_show_min_current_date == "true"){
            $options['start_date'] = date('d/m/Y');
        } else {
            $options['start_date'] = $options['start_date'];
        }
        
        $arf_show_max_current_date = "true";
	if($options['arf_show_max_current_date']<1) { $arf_show_max_current_date = "false"; }
        
        if ($arf_show_max_current_date == "true"){
            $options['end_date'] = date('d/m/Y');
        } else {
            $options['end_date'] = $options['end_date'];
        }
        
        $date = new DateTime();
  
        if($options['end_date']=="" || !preg_match( "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $options['end_date'] ))
        {
            if($options['end_year']!="")
            {
                $options['end_date']="31/12/".$options['end_year'];
            }
            else
            {
                $options['end_date']="31/12/2050";
            }
        }
       
        if($options['start_date']=="" || !preg_match( "/^[0-9]{2}\/[0-9]{2}\/[0-9]{4}$/", $options['start_date'] ))
        {
            if($options['start_year']!="")
            {
                $options['start_date']="01/01/".$options['start_year'];
            }
            else
            {
                $options['start_date']="01/01/1950";
            }
        }
        
        $end_date_temp = explode("/", $options['end_date']);
        $date->setDate($end_date_temp[2], $end_date_temp[1], $end_date_temp[0]);
        
        
        $date1 = new DateTime();
        $start_date_temp = explode("/", $options['start_date']);
        $date1->setDate($start_date_temp[2], $start_date_temp[1], $start_date_temp[0]);
        
        
        if($options['date_format'] == 'MM/DD/YYYY' || $options['date_format'] == 'MMMM D, YYYY' || $options['date_format'] == 'MMM D, YYYY'){
            $start_date = $date1->format("m/d/Y");
            $end_date = $date->format("m/d/Y");
            $date_new_format = 'MM-DD-YYYY';
        } elseif ($options['date_format'] == 'DD/MM/YYYY' || $options['date_format'] == 'D MMMM, YYYY' || $options['date_format'] == 'D MMM, YYYY') {
            $start_date = $date1->format("d/m/Y");
            $end_date = $date->format("d/m/Y");
            $date_new_format = 'DD-MM-YYYY';
        } elseif ($options['date_format'] == 'YYYY/MM/DD' || $options['date_format'] == 'YYYY, MMMM D' || $options['date_format'] == 'YYYY, MMM D') {
            $start_date = $date1->format("Y/m/d");
            $end_date = $date->format("Y/m/d");
            $date_new_format = 'YYYY-MM-DD';
        } else {            
            $start_date = $date1->format("m/d/Y");
            $end_date = $date->format("m/d/Y");
            $date_new_format = 'MM-DD-YYYY';
            $options['date_format'] = 'MMM D, YYYY';
	}        
        
        if( $show_time_calendar == "true"){
            $date_new_format = $date_new_format." hh:mm A";
        }
        
        $off_days=array();
        
        if($options['off_days']!="")
        {
            $off_days = explode(",",$options['off_days']);
        }
       
        $off_days_result="";
        $off_day_count="";
        
        $off_day_count1="";
        foreach($off_days as $offday)
        {
            $off_day_count .= " day != ".$offday." &&";
            $off_day_count1 .= " day == ".$offday." ||";
        }
        
        
        if($options['off_days']!="" && $off_day_count!="")
        {
            $off_day_count = substr($off_day_count, 0, -2);
            $off_days_result=",beforeShowDay:function(date){ var day = date.getDay();return [(".$off_day_count.")]; }";
        }
        else
        {
            $off_days_result=",beforeShowDay:function(date){ var day = date.getDay();return [true]; }";
        }
        
        $options['locale'] = ( $options['locale'] != '' ) ? $options['locale'] : 'en';
       
        if ($show_time_calendar == "true"){
            $options['clock'] = (isset($options['clock']) and $options['clock']) ? $options['clock'] : 'h:mm A';
             $date_time_format_new = $options['date_format'].' '.$options['clock'];
        } else {
            $date_time_format_new = $options['date_format'];
        }
?>


setTimeout(function(){
    jQuery("#<?php echo $date_field_id ?>").trigger('change');
},200);
<?php
    $datetimepicker_locale = (in_array($options['locale'],array('ms','zh-HK'))) ? '' : $options['locale'];
    if( $datetimepicker_locale == 'hy' ){
        $datetimepicker_locale = 'hy-am';
    }
    
    if( $datetimepicker_locale == 'no' ){
        $datetimepicker_locale = 'nb';
    }
    
    if( $datetimepicker_locale == 'tu' ){
        $datetimepicker_locale = 'tr';
    }
?>
jQuery("form.arfshowmainform").each(function(){
        var date_data_id = $(this).attr('data-id');
        jQuery("form.arfshowmainform[data-id='" + date_data_id + "']").find("#<?php echo $date_field_id ?>").datetimepicker({
            
            useCurrent: false,
            format: '<?php echo $date_time_format_new; ?>',
            <?php if ($show_time_calendar == "true"){?>  
            stepping: <?php echo (isset($options['step']) and $options['step']) ? $options['step'] : '30'; ?>,
            <?php }?>
            locale: '<?php echo $datetimepicker_locale; ?>',    
            minDate: moment('<?php echo $start_date;?> 00:00 AM', '<?php echo $date_new_format;?>'),
            maxDate: moment('<?php echo $end_date;?> 11:59 PM', '<?php echo $date_new_format;?>'),
            daysOfWeekDisabled: [<?php echo $options['off_days'];?>]
        });

        jQuery("form.arfshowmainform[data-id='" + date_data_id + "']").find("#<?php echo $date_field_id ?>").on("dp.change", function(e) {
            jQuery(this).trigger('change');
        });
});







$(document).on("blur","#<?php echo $date_field_id ?>",function(){
    var dateText = jQuery(this).val();
    var n_date=new Date(dateText);
    var day = n_date.getDay();
    if(<?php echo $off_day_count1;?> '')
    {
        jQuery(this).val("");
    }
});
jQuery(document).on("click",".arf_submit_btn",function(){
    jQuery("#<?php echo $date_field_id ?>").trigger('blur');
});



<?php 

if(!empty($options['locale'])) $load_lang[] = $options['locale'];

} 

} 



if(!empty($arftimepickerloaded)){

}



if(!empty($arfcalcfields)){ 

global $MdlDb, $arffield; 



foreach($arfcalcfields as $result => $calc){ 

    preg_match_all("/\[(.?)\b(.*?)(?:(\/))?\]/s", $calc, $matches, PREG_PATTERN_ORDER);




    $field_keys = $calc_fields = array();



    foreach ($matches[0] as $match_key => $val){

        $val = trim(trim($val, '['), ']');

        $calc_fields[$val] = $arffield->getOne($val); 

        

        if($calc_fields[$val] and in_array($calc_fields[$val]->type, array('radio', 'scale', '10radio'))){

            $field_keys[$calc_fields[$val]->id] = 'input[name="item_meta['. $calc_fields[$val]->id .']"]';

        }else if($calc_fields[$val]->type == 'checkbox'){

            $field_keys[$calc_fields[$val]->id] = 'input[name="item_meta['. $calc_fields[$val]->id .'][]"]';

        }else{

            $field_keys[$calc_fields[$val]->id] = ($calc_fields[$val]) ? '#field_'. $calc_fields[$val]->field_key : '#field_'. $val;

        }

        

        $calc = str_replace($matches[0][$match_key], 'vals[\''.$calc_fields[$val]->id.'\']', $calc);

    }

?>

$('<?php echo implode(",", $field_keys) ?>').change(function(){

var vals=new Array();

<?php foreach($calc_fields as $calc_field){ 

if($calc_field->type == 'checkbox'){

?>$('<?php echo $field_keys[$calc_field->id] ?>:checked, <?php echo $field_keys[$calc_field->id] ?>[type=hidden]').each(function(){ 

    if(isNaN(vals['<?php echo $calc_field->id ?>'])){vals['<?php echo $calc_field->id ?>']=0;}

    vals['<?php echo $calc_field->id ?>'] += parseFloat($(this).val().match(/\d*(\.\d*)?$/)); });

<?php }else if($calc_field->type == 'date') { 

?>var d=$('<?php echo $field_keys[$calc_field->id]; ?>').val();
<?php 

global $style_settings;

if(in_array($style_settings->date_format, array('d/m/Y', 'j/m/y'))){

?>var darr=d.split("/");

vals['<?php echo $calc_field->id ?>']=new Date(darr[2],darr[1],darr[0]).getTime();

<?php }else if($style_settings->date_format == 'j-m-Y'){ 

?>var darr=d.split("-");

vals['<?php echo $calc_field->id ?>']=new Date(darr[2],darr[1],darr[0]).getTime();

<?php }else{

?>vals['<?php echo $calc_field->id ?>']=new Date(d).getTime();

<?php } 

?>vals['<?php echo $calc_field->id ?>']=Math.round(vals['<?php echo $calc_field->id ?>']/(1000*60*60*24));

<?php }else{

?>vals['<?php echo $calc_field->id ?>']=$('<?php 

echo $field_keys[$calc_field->id]; 

if(in_array($calc_field->type, array("radio", "scale", "10radio"))){

    echo ":checked, ". $field_keys[$calc_field->id] ."[type=hidden]";

} else if($calc_field->type == "select") {

    echo " option:selected, ". $field_keys[$calc_field->id] .":hidden";
}

?>').val();

if(typeof(vals['<?php echo $calc_field->id ?>'])=='undefined'){vals['<?php echo $calc_field->id ?>']=0;}else{ vals['<?php echo $calc_field->id ?>']=parseFloat(vals['<?php echo $calc_field->id ?>'].match(/-?\d*(\.\d*)?$/)); }

<?php } 

?>if(isNaN(vals['<?php echo $calc_field->id ?>'])){vals['<?php echo $calc_field->id ?>']=0;}

<?php }

?>var total=parseFloat(<?php echo $calc ?>);if(isNaN(total)){total=0;}

$("#field_<?php echo $result ?>").val(total).change();

});

$('<?php echo reset($field_keys) ?>').change();

<?php } 

}

} 



if(!empty($arfinputmasks)){

    foreach((array)$arfinputmasks as $f_key => $mask){

        if(is_numeric($f_key)){

?>$('input[name="item_meta[<?php echo $f_key ?>]"]').mask("<?php echo $mask ?>");

<?php   }else{ 

?>$('#field_<?php echo $f_key ?>]').mask("<?php echo $mask ?>");

<?php   }

        unset($f_key);

        unset($mask);

    }

}



?>

});



<?php if(isset($load_lang) and !empty($load_lang)){ ?>

var frmJsHost=(("https:"==document.location.protocol)?"https://":"http://");

<?php foreach($load_lang as $lang){ ?>


<?php }

} 


global $arftimepickerloaded;

if( !empty($arftimepickerloaded) ) {
	?>jQuery(document).ready(function($){ <?php
	foreach($arftimepickerloaded as $time_field_id => $options){ 	
		?>                

                
                
                setTimeout(function(){
                    $("#<?php echo $time_field_id ?>").trigger('change');
                },200);
          
                jQuery("form.arfshowmainform").each(function(){
                    var time_data_id = $(this).attr('data-id');
                    $("form.arfshowmainform[data-id='" + time_data_id + "']").find("#<?php echo $time_field_id ?>").datetimepicker({

                            format: '<?php echo (isset($options['clock']) and $options['clock']) ? 'H:mm' : 'h:mm A'; ?>',
                            stepping: <?php echo (isset($options['step']) and $options['step']) ? $options['step'] : '30'; ?>,
                            defaultDate: moment({hour: <?php echo (isset($options['default_hour'])) ? $options['default_hour'] : '00'; ?>, minute: <?php echo (isset($options['default_minutes'])) ? $options['default_minutes'] : '00'; ?>})
                    });
                    
                    $("form.arfshowmainform[data-id='" + time_data_id + "']").find("#<?php echo $time_field_id ?>").on("dp.change", function(e) {
                            jQuery(this).trigger('change');
                    });
                });
                
                
                
                
		<?php }
	?>});<?php		
}

global $arf_slider_loaded;

if (count($arf_slider_loaded) > 0) {
    ?>
                    jQuery(document).ready(function($){
                    window.prettyPrint && prettyPrint();
    <?php foreach ($arf_slider_loaded as $slider_field_id => $options) { ?>                   
                    setTimeout(function(){
			$("#<?php echo $slider_field_id;?>_slide").arf_slider({ tooltip: 'always', handle : '<?php echo $options['handle'];?>', value : '<?php echo $options['slider_value'];?>' }).on('slideStop', function(ev){
				var val = $(this).arf_slider('getValue');
				
				
				if( val || val == '0' ){
                                        var range_selector = $("#<?php echo $slider_field_id;?>").attr('data-slider-range-selector');
                                        if(range_selector == '1'){
                                           val = val[0]+" - "+val[1];
                                        }
                                        $("#<?php echo $slider_field_id;?>").val(val).trigger('change');
				}
			});
			<?php if($preview == true){ ?>if( $("#<?php echo $slider_field_id;?>").is(':visible') ) { $("#<?php echo $slider_field_id;?>").val('<?php echo $options['slider_value'];?>').trigger('change'); } <?php }?>			
		}, <?php if($preview == true){ ?>300<?php } else { ?>100<?php } ?>);
			<?php if($preview != true){ ?>if( $("#<?php echo $slider_field_id;?>").is(':visible') ) { $("#<?php echo $slider_field_id;?>").val('<?php echo $options['slider_value'];?>').trigger('change'); }<?php } ?>                        
    <?php } ?>

                });
                
    <?php
}

global $arf_password_loaded;

if( count($arf_password_loaded) > 0 ){
	?>
	jQuery(document).ready(function($){
		<?php foreach($arf_password_loaded as $password_key){
			?>
			$('#field_<?php echo $password_key;?>').on('keyup', function(){
				arf_password_meter('<?php echo $password_key;?>');
			});
			<?php
		} ?>
	});
	<?php
}
?>


//]]>

<?php 		
do_action('arf_after_footer_loaded');
?>
</script>
<?php  $arf_radio_checkbox_loaded = $arf_slider_loaded = $arftimepickerloaded = $arf_selectbox_loaded = $arf_conditional_logic_loaded = array(); ?>