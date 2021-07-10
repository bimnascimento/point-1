<?php

global $wpdb, $arfform, $arffield, $db_record, $arfrecordmeta, $user_ID, $arfsettings, $arfcreatedentry, $arfform_params, $MdlDb, $arf_modal_form_loaded, $arformcontroller, $arsettingcontroller, $arrecordcontroller, $armainhelper, $arrecordhelper, $arfieldhelper, $maincontroller, $arf_font_awesome_loaded,$arfversion;

$arf_modal_form_loaded++;

$wp_upload_dir = wp_upload_dir();
    $upload_dir = $wp_upload_dir['basedir'] . '/arforms/css/';
    
    if (is_ssl()) {
        $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
        $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/css');
    } else {
        $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/css';
    }

    $fid = $upload_main_url . '/maincss_' . $form->id . '.css';
    $css_file = $upload_dir.'maincss_'.$form->id.'.css';
    
    $maincss = "<link rel='stylesheet' type='text/css' href='".$fid."?ver=".$arfversion."' />";
	
if( isset($_COOKIE['load_footer_scripts']) && $_COOKIE['load_footer_scripts'] != '' && $_COOKIE['load_footer_scripts'] ==  base64_encode('load_footer_scripts') ){
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/css/arf_front.css?ver=".$arfversion."' />";
    
    echo "<script type='text/javascript' src='".ARFURL."/js/arf_modal_js.js?ver=".$arfversion."'></script>";
	
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/moment-with-locales.js?ver=".$arfversion."'></script>";
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/bootstrap-datetimepicker.js?ver=".$arfversion."'></script>";
	
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/modernizr.js?ver=".$arfversion."'></script>";
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/bootstrap-slider.js?ver=".$arfversion."'></script>";
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/bootstrap/css/bootstrap-slider.css?ver=".$arfversion."' />";
	
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/bootstrap-select.js?ver=".$arfversion."'></script>";
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/bootstrap/css/bootstrap.css?ver=".$arfversion."' />";
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/bootstrap/css/bootstrap-select.css?ver=".$arfversion."' />";
	
	
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/css/arf_filedrag.css?ver=".$arfversion."' />";
	
	
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/css/colpick.css?ver=".$arfversion."' />";
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/css/font-awesome.min.css?ver=".$arfversion."' />";
	
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/icheck.min.js?ver=".$arfversion."'></script>";
	
	echo "<script type='text/javascript' src='".ARFURL."/js/arf_conditional_logic.js?ver=".$arfversion."'></script>";
	echo "<script type='text/javascript' src='".ARFURL."/bootstrap/js/bootstrap-inputmask.js?ver=".$arfversion."'></script>";
	
	echo "<script type='text/javascript' src='".ARFURL."/js/colpick.js?ver=".$arfversion."'></script>";
	echo "<script type='text/javascript' src='".ARFURL."/js/jquery.simple-color-picker.js?ver=".$arfversion."'></script>";
	echo "<script type='text/javascript' src='".ARFURL."/js/recaptcha_ajax.js?ver=".$arfversion."'></script>";
	echo "<link rel='stylesheet' type='text/css' href='".ARFURL."/css/recaptcha_style.css?ver=".$arfversion."' />";
	
	echo "<script type='text/javascript' src='".ARFURL."/js/arforms.js?ver=".$arfversion."'></script>";
	
	echo $maincss;
}

$open_inactivity_value = '1';
$open_scroll_value = '10';
$open_delay_value = '200';
$overlay_value = '0.6';

$is_onload = false;
$is_scroll = false;
$is_x_seconds = false;

$arf_popup_data_uniq_id = $form->id;
$arf_popup_data_uniq_id = rand(1, 99999);
if(empty($arf_popup_data_uniq_id) || $arf_popup_data_uniq_id == ''){
    $arf_popup_data_uniq_id = $form->id;
}


if ($type == 'onload') {
    $type = 'link';
    $is_onload = true;

    if (!empty($open_delay) && is_numeric($open_delay)) {
        $open_delay_value = ($open_delay * 1000);
    }
}

if ($type == 'x_seconds') {
    $type = 'link';
    $is_onload = true;
    $is_x_seconds = true;
    if (!empty($open_inactivity) && is_numeric($open_inactivity)) {
        $open_inactivity_value = $open_inactivity;
    }
}
if ($type == 'scroll') {
    $type = 'link';
    $is_onload = true;
    $is_scroll = true;
    if (!empty($open_scroll) && is_numeric($open_scroll)) {
        $open_scroll_value = $open_scroll;
    }
}

if (is_numeric($overlay)) {
    $overlay_value = $overlay;
}


$form_name = $form->name;

$arfaset = get_option('arfa_options');

$is_modal_form = true;

$is_widget_or_modal = true;



if (empty($modal_width)) {
    $modal_width = 800;
}





$form_css_submit = maybe_unserialize($form->form_css);
if (is_array($form_css_submit)) {
    if ($form_css_submit['arfsubmitbuttontext'] != '')
        $submit = $form_css_submit['arfsubmitbuttontext'];
    else
        $submit = $arfsettings->submit_value;
}


$success_image = '<img src="' . ARFIMAGESURL . '/success_icons/' . $arfaset->arfsucessiconsetting . '" style="padding-right:0px; margin-right:10px;" alt="" />';

$saved_message = isset($form->options['success_msg']) ? '<div id="arf_message_success"> 
				<div class="msg-detail">
                    <div class="msg-description-success">' . $form->options['success_msg'] . '</div>
				</div>

            </div>' : $arfsettings->success_msg;

$wp_upload_dir = wp_upload_dir();
$upload_dir = $wp_upload_dir['basedir'] . '/arforms/css/';


if (is_ssl()) {
    $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
    $upload_css_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/css');
} else {
    $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
    $upload_css_url = $wp_upload_dir['baseurl'] . '/arforms/css';
}

$fid = $upload_main_url . '/maincss_' . $form->id . '.css';
wp_register_style('arfformscss' . $form->id, $fid,array(),$arfversion);
wp_print_styles('arfformscss' . $form->id, $fid);
?>
<style type="text/css">
    #arf-popup-form-<?php echo $form->id; ?>.arf_popup_<?php echo $arf_popup_data_uniq_id; ?> .arf_fly_sticky_btn{
        background:<?php echo $bgcolor; ?>;
        color:<?php echo $txtcolor; ?>;
    }
    .arfmodal { margin:0; padding:0; }
<?php
$custom_css_array_form = array(
    'arf_form_fly_sticky' => '.arf_fly_sticky_btn',
    'arf_form_modal_css' => '.arfmodal',
    'arf_form_link_css' => '.arform_modal_link_' . $form->id,
    'arf_form_link_hover_css' => '.arform_modal_link_' . $form->id . ':hover',
    'arf_form_button_css' => '.arform_modal_button_' . $form->id,
    'arf_form_button_hover_css' => '.arform_modal_button_' . $form->id . ':hover',
);

foreach ($custom_css_array_form as $custom_css_block_form => $custom_css_classes_form) {
    if (isset($form->options[$custom_css_block_form]) and $form->options[$custom_css_block_form] != '') {

        $form->options[$custom_css_block_form] = $arformcontroller->br2nl($form->options[$custom_css_block_form]);

        if ($custom_css_block_form == 'arf_form_modal_css') {
            echo '#popup-form-' . $form->id . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
        } elseif ($custom_css_block_form == 'arf_form_link_css' || $custom_css_block_form == 'arf_form_button_css' || $custom_css_block_form == 'arf_form_link_hover_css' || $custom_css_block_form == 'arf_form_button_hover_css') {
            echo $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
        } else {
            echo '#arf-popup-form-' . $form->id . ' ' . $custom_css_classes_form . ' { ' . $form->options[$custom_css_block_form] . ' } ';
        }
    } 
}


?>#popup-form-<?php echo $form->id; ?>.arf_flymodal .arfmodal-header, 
    #popup-form-<?php echo $form->id; ?>.arform_right_fly_form_block_right_main .arfmodal-header, 
    #popup-form-<?php echo $form->id; ?>.arform_sb_fx_form_left_<?php echo $form->id; ?> .arfmodal-header, 
    #popup-form-<?php echo $form->id; ?>.arform_bottom_fixed_form_block_top .arfmodal-header { border-bottom:none; }
</style>
<div><script type="text/javascript">
    function popup_tb_show(form_id, submitted)
    {
        var last_open_modal = jQuery('#current_modal').val();
        if (last_open_modal == 'arf_modal_left')
        {
            jQuery('.arform_side_block_left_' + form_id).trigger("click");
        }
        else if (last_open_modal == 'arf_modal_right')
        {
            jQuery('.arform_side_block_right_' + form_id).trigger("click");
        }
        else if (last_open_modal == 'arf_modal_top')
        {
            setTimeout(function () {
                jQuery('.arform_bottom_fixed_form_block_top_main').css('display', 'block');
                jQuery('.arform_bottom_fixed_form_block_top_main').css('height', 'auto');
            }, 500);
        }
        else if (last_open_modal == 'arf_modal_bottom')
        {
            setTimeout(function () {
                jQuery('.arform_bottom_fixed_form_block_bottom_main').css('display', 'block');
                jQuery('.arform_bottom_fixed_form_block_bottom_main').css('height', 'auto');
            }, 500);
        }
        else if (last_open_modal == 'arf_modal_sitcky_left')
        {
            setTimeout(function () {
                jQuery('.arform_bottom_fixed_form_block_left_main').css('display', 'block');
                jQuery('.arform_bottom_fixed_form_block_left_main').css('height', 'auto');
            }, 500);
        }
        else if (last_open_modal == 'arf_modal_default')
        {
            jQuery('#arf_modal_default').trigger("click");
            if (submitted == true) {
                var len = jQuery('.arfmodal-backdrop').length;
                jQuery('.arfmodal-backdrop').each(function () {

                    if (len != 1) {
                        jQuery(this).remove();
                    }
                    len = len - 1;
                });
            }
        }
    }
<?php if ($type == 'link' && $is_onload) { ?>
        jQuery(document).ready(function () {

    <?php if ($is_scroll) { ?>

                var arf_open_scroll = '<?php echo $open_scroll_value; ?>';
                var arf_op_welcome = false;

                jQuery(window).scroll(function (event) {
                    var scrollPercent = 100 * jQuery(window).scrollTop() / (jQuery(document).height() - jQuery(window).height());
                    if (Math.round(scrollPercent) == arf_open_scroll) {

                    }
                });

                jQuery(window).scroll(function () {
                    var h = jQuery(document).height() - jQuery(window).height();
                    var sp = jQuery(window).scrollTop();
                    var p = parseInt(sp / h * 100);

                    if (p >= arf_open_scroll && arf_op_welcome == false) {
                        jQuery('.arform_modal_link_<?php echo $form->id; ?>').trigger('click');
                        arf_op_welcome = true;
                    }
                });

    <?php } elseif ($is_x_seconds) { ?>

                var arf_idletime = 0;
                var arf_open_inactivity = '<?php echo $open_inactivity_value; ?>';
                function arf_timerIncrement()
                {
                    arf_idletime++;
                    if (arf_idletime > arf_open_inactivity)
                    {
                        window.clearTimeout(arf_idleInterval);
                        jQuery('.arform_modal_link_<?php echo $form->id; ?>').trigger('click');
                    }
                }
                var arf_idleInterval = setInterval(arf_timerIncrement, 1000);
                jQuery(this).mousemove(function (e) {
                    arf_idletime = 0;
                });
                jQuery(this).keypress(function (e) {
                    arf_idletime = 0;
                });

    <?php } else { ?>
                setTimeout(function () {
                    jQuery('.arform_modal_link_<?php echo $form->id; ?>').trigger('click');
                }, <?php echo $open_delay_value; ?>);
    <?php } ?>
        });
<?php } ?>




    jQuery(document).ready(function () {
        jQuery('.arform_right_fly_form_block_right_main').hide();
        jQuery('.arform_left_fly_form_block_left_main').hide();

        var mybtnangle = <?php echo $btn_angle; ?>;
        var myformid = <?php echo $form->id; ?>;
        var mypopup_data_uniq_id =  <?php echo $arf_popup_data_uniq_id; ?>

        if (Number(mybtnangle) == -90)
        {
            jQuery('.arf_popup_' + mypopup_data_uniq_id).find('.arform_side_block_right_' + myformid + '').css('transform-origin', 'bottom right');
            jQuery('.arf_popup_' + mypopup_data_uniq_id).find('.arform_side_block_left_' + myformid + '').css('transform-origin', 'top left');

        }
        else if (Number(mybtnangle) == 90)
        {
            jQuery('.arf_popup_' + mypopup_data_uniq_id).find('.arform_side_block_right_' + myformid + '').css('transform-origin', 'top right');
            jQuery('.arf_popup_' + mypopup_data_uniq_id).find('.arform_side_block_left_' + myformid + '').css('transform-origin', 'bottom left');
        }


    });
    </script></div><?php
$checkradio_property = "";
if ($form_css_submit['arfcheckradiostyle'] != "") {
    if ($form_css_submit['arfcheckradiostyle'] != "none") {
        if ($form_css_submit['arfcheckradiocolor'] != "default" && $form_css_submit['arfcheckradiocolor'] != "") {
            if ($form_css_submit['arfcheckradiostyle'] == "custom" || $form_css_submit['arfcheckradiostyle'] == "futurico" || $form_css_submit['arfcheckradiostyle'] == "polaris") {
                $checkradio_property = $form_css_submit['arfcheckradiostyle'];
            } else {
                $checkradio_property = $form_css_submit['arfcheckradiostyle'] . "-" . $form_css_submit['arfcheckradiocolor'];
            }
        } else {
            $checkradio_property = $form_css_submit['arfcheckradiostyle'];
        }
    } else {
        $checkradio_property = "";
    }
}

$checked_checkbox_property = '';
$checked_radio_property = '';

if( $checkradio_property == 'custom' ){
    $arf_font_awesome_loaded = 1;

    $checked_checkbox_property = '';
    if($form_css_submit['arf_checked_checkbox_icon'] != ""){
        $checked_checkbox_property = ' arfa '. $form_css_submit['arf_checked_checkbox_icon'];
    } else {
        $checked_checkbox_property = '';
    }
    $checked_radio_property = '';
    if($form_css_submit['arf_checked_radio_icon'] != ""){
        $checked_radio_property = ' arfa '. $form_css_submit['arf_checked_radio_icon'];
    } else {
        $checked_radio_property = '';
    }
}        

if ($is_onload) {
    $style_onload = ' style="display:none !important;"';
} else {
    $style_onload = ' style="cursor:pointer;"';
}


if ($type == 'link' || $type == '') {
    echo '<div><a href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default" data-toggle="arfmodal" title="' . $form_name . '" class="arform_modal_link_' . $form->id . '" ' . $style_onload . '>' . $desc . '</a></div>';
} elseif ($type != 'fly' && $type != 'sticky') {
    echo '<div><button href="#" onclick="open_modal_box(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $is_close_link . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" id="arf_modal_default" data-toggle="arfmodal"  title="' . $form_name . '" class="arform_modal_button_' . $form->id . ' arform_modal_button_popup_' . $arf_popup_data_uniq_id .'" ' . $style_onload . '>' . $desc . '</button></div>';
}

$form_opacity = ($form_css_submit['arfmainform_opacity'] == '' || $form_css_submit['arfmainform_opacity'] > 1) ? 1 : $form_css_submit['arfmainform_opacity'];

if ($form_opacity < 1 && $type != 'fly' && $type != 'sticky') {
    ?><style type="text/css">
        #popup-form-<?php echo $form->id; ?>.arfmodal {
            background:none;
            border:none;
            box-shadow:none;
        }
        .arfmodal .arfmodal-body .ar_main_div_<?php echo $form->id; ?> .arf_fieldset { background: rgba(<?php echo $arsettingcontroller->hex2rgb($form_css_submit['arfmainformbgcolorsetting']); ?>, <?php echo $form_opacity; ?> ) url(<?php echo $form_css_submit['arfmainform_bg_img']; ?>);  background-repeat:no-repeat; background-position:top left; }
        .arfmodal-backdrop, .arfmodal-backdrop.arffade.in {
            opacity: 0.5;
        }
        .arfmodal-body { padding:0 !important; }
        #popup-form-<?php echo $form->id; ?>.arf_flymodal, #popup-form-<?php echo $form->id; ?>.arform_right_fly_form_block_right_main, #popup-form-<?php echo $form->id; ?>.arform_sb_fx_form_left_<?php echo $form->id; ?>, #popup-form-<?php echo $form->id; ?>.arform_bottom_fixed_form_block_top {
            background : rgba(<?php echo $arsettingcontroller->hex2rgb($form_css_submit['arfmainformbgcolorsetting']); ?>, <?php echo $form_opacity; ?> );
            background-position: left top;
            background-repeat: no-repeat; 
            <?php if ($form_opacity == 0) { ?> border: 0px !important; box-shadow:none !important;<?php } ?>
        }
    </style><?php
} else if ($form_opacity == 1 && $type != 'fly' && $type != 'sticky') {
    ?><style type="text/css">
        #popup-form-<?php echo $form->id; ?>.arfmodal {
            background:none;
            border:none;
            box-shadow:none;
        }
    </style><?php
}
if ($type == 'fly') {
    if ($position == 'right') {
        $button_angle_class = '';
        if ($btn_angle != '' && $btn_angle != '0') {
            $button_angle_class = '-webkit-transform:rotate(' . $btn_angle . 'deg);
									-moz-transform:rotate(' . $btn_angle . 'deg);
									-ms-transform:rotate(' . $btn_angle . 'deg);
									-o-transform:rotate(' . $btn_angle . 'deg);
									transform:rotate(' . $btn_angle . 'deg);';
        }

        echo '<style>.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_right_' . $form->id . ' {opacity:1;top:50%; right:-2px; position:fixed;z-index:9999; background:#8ccf7a; border:none; border-right:0px; padding:10px 13px 0 13px; cursor:pointer; border-top-left-radius:3px; border-bottom-left-radius:3px; font-size:14px; height:25px; color:#ffffff; font-weight:bold; ' . $button_angle_class . '}
			 .arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_right_' . $form->id . ':hover {opacity:1;}
			 .arf_popup_' . $arf_popup_data_uniq_id . ' .arform_sb_fx_form_right_' . $form->id . ' {position:fixed;}
			 </style>';        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

        echo '<span href="#" onclick="open_modal_box_fly_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_right_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

        echo '<div class="arform_side_fixed_form_block_right_main_' . $form->id . '">';
        echo '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_right_fly_form_block_right_main arform_sb_fx_form_right_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="max-height: ' . $modal_height . 'px; width: ' . $modal_width . 'px;z-index:9999; top:20%; right:-110%;">';
    } else {
        $button_angle_class = '';
        if ($btn_angle != '' && $btn_angle != '0') {
            $button_angle_class = '-webkit-transform:rotate(' . $btn_angle . 'deg);
									-moz-transform:rotate(' . $btn_angle . 'deg);
									-ms-transform:rotate(' . $btn_angle . 'deg);
									-o-transform:rotate(' . $btn_angle . 'deg);
									transform:rotate(' . $btn_angle . 'deg);';
        }

        echo '<style>.arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_left_' . $form->id . ' {opacity:1;top:50%;left:-2px; position:fixed;z-index:9999; background:#2d6dae; border:none; border-left:0px; padding:10px 13px 0 13px; cursor:pointer; border-top-right-radius:3px; border-bottom-right-radius:3px; font-size:14px; height:25px; color:#ffffff; font-weight:bold; ' . $button_angle_class . ' }
			 .arf_popup_' . $arf_popup_data_uniq_id . ' .arform_side_block_left_' . $form->id . ':hover {opacity:1;}
			 .arf_popup_' . $arf_popup_data_uniq_id . ' .arform_sb_fx_form_left_' . $form->id . ' { position:fixed; }
			 </style>';        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

        echo '<span href="#" onclick="open_modal_box_fly_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');"  title="' . $form_name . '" class="arform_side_block_left_' . $form->id . ' arf_fly_sticky_btn">' . $desc . '</span>';

        echo '<div class="arform_side_fixed_form_block_left_main_' . $form->id . '">';
        echo '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arform_left_fly_form_block_left_main arform_sb_fx_form_left_' . $form->id . ' arf_pop_' . $arf_popup_data_uniq_id . '" style="max-height: ' . $modal_height . 'px; width: ' . $modal_width . 'px;z-index:9999; top:20%; right:110%; ">';
    }
} elseif ($type == 'sticky') {
    if ($position == 'top') {
        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_top arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:10000;">';
        echo '<div class="arform_bottom_fixed_form_block_top_main" style="display:none;">';
        echo '<div id="popup-form-' . $form->id . '"  aria-hidden="false" class="arform_bottom_fixed_form_block_top arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;max-height: ' . $modal_height . 'px; width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;" >';
    }
     else if ($position == 'left') {
        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_left arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';

        echo '<div class="arform_bottom_fixed_block_left arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_left(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer; ">
				<span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
			  </div>';
        echo '<div style="clear:both;"></div>';

        echo '<div class="arform_bottom_fixed_form_block_left_main" style="float:left;  margin-left:-' . $modal_width . 'px">';
        echo '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_left arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block; max-height: ' . $modal_height . 'px; width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;  border:none;">';
    } else if ($position == 'right') {
        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_right arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:9999;">';
        echo '<div class="arform_bottom_fixed_block_right arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_right(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
				<span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
			  </div>';
        echo '<div style="clear:both;"></div>';

        echo '<div class="arform_bottom_fixed_form_block_right_main" style="float:right; margin-right:-' . $modal_width . 'px"" >';
        echo '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_right arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;max-height: ' . $modal_height . 'px; width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';


    } else {
        
        echo '<div id="arf-popup-form-' . $form->id . '" class="arf_flymodal arform_bottom_fixed_main_block_bottom arf_popup_' . $arf_popup_data_uniq_id . '" style="z-index:10000;">';
        echo '<div class="arform_bottom_fixed_block_bottom arf_fly_sticky_btn arform_modal_stickybottom_' . $form->id . '" onclick="open_modal_box_sitcky_bottom(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
				<span href="#" data-toggle="arfmodal" title="' . $form_name . '" >' . $desc . '</span>
			  </div>';
        echo '<div style="clear:both;"></div>';
        echo '<div class="arform_bottom_fixed_form_block_bottom_main" style="display:none;">';
        echo '<div id="popup-form-' . $form->id . '" aria-hidden="false" class="arf_flymodal arform_bottom_fixed_form_block_bottom arf_pop_' . $arf_popup_data_uniq_id . '" style="display:block;max-height: ' . $modal_height . 'px; width: ' . ($modal_width) . 'px; left: 20%;z-index:9999;border:none;">';
    }
} else {
    echo '<div id="popup-form-' . $form->id . '" style="display:none;max-height: ' . $modal_height . 'px; width: ' . $modal_width . 'px; left: 20%;" aria-hidden="false" class="arfmodal arfhide arffade arf_pop_' . $arf_popup_data_uniq_id . '">';
}
global $arfajaxurl;
echo '<input type="hidden" value="' . $arfajaxurl . '" id="admin_ajax_url" name="admin_ajax_url" >';
$_SESSION['last_open_modal'] = isset($_SESSION['last_open_modal']) ? $_SESSION['last_open_modal'] : '';
echo '<input type="hidden" value="' . $_SESSION['last_open_modal'] . '" id="current_modal" name="current_modal" >';

echo '<input type="hidden" value="' . $is_close_link . '" id="is_close_link" name="is_close_link" >';

if ($type == 'fly') {
    if ($position == 'right') {
        echo '<button id="open_modal_box_fly_right_' . $form->id . '" onclick="open_modal_box_fly_left_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');"  data-toggle="arfmodal" title="' . $form_name . '"  class="close" type="button" style="margin-right:5px; margin-top:5px; z-index:9999;">x</button>';
    } else {
        echo '<button id="open_modal_box_fly_left_' . $form->id . '" onclick="open_modal_box_fly_right_move(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $arf_popup_data_uniq_id . '\');" class="close" type="button" style="margin-right:5px; margin-top:5px; z-index:9999;">x</button>';
    }
} else if ($type != 'sticky') {
    $display_button = ($is_close_link == 'no') ? 'display:none;' : '';
    echo '<button data-dismiss="arfmodal" class="close" type="button" style="margin-right:5px; margin-top:5px; z-index:9999; ' . $display_button . ' ">x</button>';
}

$arfmodalbodypadding = '0';


echo '<div class="arfmodal-body" style="padding:' . $arfmodalbodypadding . ';">';


$params = $arrecordcontroller->get_recordparams($form);


$form_css = maybe_unserialize($form->form_css);
if ($form_css != '') {
    if (is_array($form_css))
        $date_picker = $form_css['arfcalthemecss'];
    else
        $date_picker = $form_css->arfcalthemecss;
}


$date_picker_css = $armainhelper->jquery_css_url($date_picker);


wp_register_style('form_custom_css-'.$date_picker, $date_picker_css,array(),$arfversion);
if( isset($_COOKIE['load_footer_scripts']) && $_COOKIE['load_footer_scripts'] != '' && $_COOKIE['load_footer_scripts'] ==  base64_encode('load_footer_scripts') ){
	echo "<link rel='stylesheet' type='text/css' href='".$date_picker_css."?ver=".$arfversion."' />";
}


$message = $errors = '';


$arrecordhelper->enqueue_scripts($params);



if ($params['action'] == 'create' and $params['posted_form_id'] == $form->id and isset($_POST)) {


    $errors = $arfcreatedentry[$form->id]['errors'];



    if (!empty($errors)) {

        if ($arfsettings->form_submit_type != 1) {

            echo '<script type="text/javascript">
					jQuery(document).ready(function(){
						popup_tb_show(' . $form->id . ',false);
					});    
					</script>';
        }

        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);


        $values = $arrecordhelper->setup_new_vars($fields, $form);
        if ($arfsettings->form_submit_type == 1)
            $return["conf_method"] = "error";
        require(VIEWS_PATH . '/new.php');
        exit;
        ?><script type="text/javascript">jQuery(document).ready(function ($) {
                var frm_pos = jQuery('#form_<?php echo $form->form_key ?>').offset();
                var cOff = document.documentElement.scrollTop || document.body.scrollTop;
                if (frm_pos)
                    window.scrollTo(frm_pos.left, frm_pos.top);
            })</script><?php
    }else {


        $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);

        if (apply_filters('arfcontinuetocreate', true, $form->id)) {


            $values = $arrecordhelper->setup_new_vars($fields, $form, true);


            $created = $arfcreatedentry[$form->id]['entry_id'];


            $saved_message = apply_filters('arfcontent', $saved_message, $form, $created);


            $conf_method = apply_filters('arfformsubmitsuccess', 'message', $form, $form->options);

            if (!$created or ! is_numeric($created))
                $conf_method = 'message';

            if (!$created or ! is_numeric($created) or $conf_method == 'message') {
                if ($arfsettings->form_submit_type == 1)
                    $return["conf_method"] = $conf_method;

                $failed_msg = '<div class="frm_error_style" id="arf_message_error">
									<div class="msg-detail">
										<div class="msg-description-success">' . $arfsettings->failed_msg . '</div>
									</div>
								</div>';

                $message = (($created and is_numeric($created)) ? do_shortcode($saved_message) : $failed_msg);

                if (!isset($form->options['show_form']) or $form->options['show_form']) {


                    require(VIEWS_PATH . '/new.php');
                    exit;
                } else {


                    global $arfforms_loaded, $arfloadcss, $arfcssloaded;


                    $arfforms_loaded[] = $form;


                    if ($values['custom_style'])
                        $arfloadcss = true;





                    if (!$arfcssloaded and $arfloadcss) {


                        echo $maincontroller->footer_js('header');


                        $arfcssloaded = true;
                    }

                    $maincontroller->footer_js();

                    if ($arfsettings->form_submit_type != 1) {
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                popup_tb_show('<?php echo $form->id; ?>', true);
                            });
                        </script>
                        <?php
                    }
                    if ($arfsettings->form_submit_type == 1) {
                        $return["message"] = '<div class="arf_form arf_form_outer_wrapper ar_main_div_' . $form->id . '" id="arffrm_' . $form->id . '_container">' . $message . '</div>';
                        echo json_encode($return);
                        exit;
                    } else {
                        ?>
                        <div class="arf_form<?php echo ' arf_form_outer_wrapper ar_main_div_' . $form->id; ?>" id="arffrm_<?php echo $form->id ?>_container"><?php echo $message ?></div>
                        <?php
                    }

                    if ($arfsettings->form_submit_type == 1)
                        exit;
                }
            }else {
                if ($arfsettings->form_submit_type == 1)
                    $return["conf_method"] = $conf_method;

  
                $form_options = $form->options;

                if ($conf_method == 'page' and is_numeric($form_options['success_page_id'])) {
                    global $post, $arfsettings;
                    if ($form_options['success_page_id'] != $post->ID) {
                        $page = get_post($form_options['success_page_id']);
                        $old_post = $post;
                        $post = $page;
                        $content = apply_filters('arfcontent', $page->post_content, $form, $entry_id);

                        if ($arfsettings->form_submit_type == 1) {
                            $return["message"] = apply_filters('the_content', $content);
                        } else {

                            echo '<style type="text/css">#popup-form-' . $form->id . ' .arfmodal-body { background:#ffffff; }</style>';
                            echo $content;

                            global $arfforms_loaded, $arfloadcss, $arfcssloaded;
                            $arfforms_loaded[] = $form;
                            if ($values['custom_style'])
                                $arfloadcss = true;
                            $maincontroller->footer_js();
                        }

                        $post = $old_post;
                        if ($arfsettings->form_submit_type != 1) {
                            echo "<script type='text/javascript'>
									jQuery(document).ready(function(){
										popup_tb_show('" . $form->id . "',false);
									});    
								</script>";
                        }
                    }
                } else if ($method == 'redirect') {
                    $success_url = apply_filters('arfcontent', $form_options['success_url'], $form, $entry_id);
                    $success_msg = isset($form_options['success_msg']) ? stripslashes($form_options['success_msg']) : __('Please wait while you are redirected.', 'ARForms');
                    echo "<script type='text/javascript'> jQuery(document).ready(function($){ setTimeout(window.location='" . $success_url . "', 5000); });</script>";
                }


       
                if ($arfsettings->form_submit_type == 1) {
                    echo json_encode($return);
                    exit;
                }
            }
        }
    }
} else {

    $fields = $arfieldhelper->get_form_fields_tmp(false, $form->id, false, 0);


    do_action('arfdisplayformaction', $params, $fields, $form, $title, $description);





    $values = $arrecordhelper->setup_new_vars($fields, $form);

    $file_url = MODELS_PATH . "/GeoIP.dat";

    if (!(extension_loaded('geoip'))) {
        $gi = geoip_open($file_url, GEOIP_STANDARD);
    }

    $referrerinfo = $armainhelper->get_referer_info();

    $form_id = $form->id;

    $browser_info = $_SERVER['HTTP_USER_AGENT'];

    $ip_address = $_SERVER['REMOTE_ADDR'];

    $new_values['ip_address'] = isset($new_values['ip_address']) ? $new_values['ip_address'] : '';

    if (!(extension_loaded('geoip'))) {
        $country_name = @geoip_country_name_by_addr($gi, @$new_values['ip_address']);
    } else {
        $country_name = "";
    }

    if ($country_name == "") {
        $country_name = "";
    }

    $country = $country_name;

    $session_id = WP_FB_SESSION;

    $added_date = current_time('mysql');

    $sqlQyr = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "arf_views WHERE session_id= %s AND form_id = %d", $session_id, $form_id), 'ARRAY_A');
    $totalViews = $wpdb->num_rows;

    if ($totalViews == 0) {
        $wpdb->query($wpdb->prepare("insert into " . $MdlDb->views . " (form_id,browser_info,ip_address,country,session_id,added_date) VALUES ('%d','%s','%s','%s','%s','%s')", $form_id, $browser_info, $ip_address, $country, $session_id, $added_date));
    }

    require(VIEWS_PATH . '/new.php');

}?><div><input type="hidden" name="arfmainformurl" id="arfmainformurl" value="<?php echo ARFURL;?>" /><input type="hidden" name="arffiledragurl" id="arffiledragurl" value="<?php echo ARF_FILEDRAG_SCRIPT_URL; ?>" /></div><?php
echo '</div>';
echo '</div>';
if ($type == 'sticky') {
    echo '</div>';
    if ($position == 'top') {
        echo '<div style="clear:both;"></div>';
        echo '<div class="arform_bottom_fixed_block_top arf_fly_sticky_btn arform_modal_stickytop_' . $form->id . '" onclick="open_modal_box_sitcky_top(\'' . $form->id . '\', \'' . $modal_height . '\', \'' . $modal_width . '\', \'' . $checkradio_property . '\', \'' . $checked_checkbox_property . '\', \'' . $checked_radio_property . '\', \'' . $arf_popup_data_uniq_id . '\');" style="cursor:pointer;">
				<span href="#" data-toggle="arfmodal" title="' . $form_name . '">' . $desc . '</span>
			  </div>';
    }
    echo '</div>';
} elseif ($type == 'fly') {
    echo '</div>';
    echo '</div>';
}
?>
<script type="text/javascript" language="javascript">
    jQuery(document).ready(function () {


        var winodwHeight = jQuery(window).height();

<?php
if ($type == 'sticky' && $position == 'left') {
    ?>
            var modal_height_left = '<?php echo $modal_height; ?>';

            jQuery(".arform_bottom_fixed_main_block_left").css("top", Number(Number(winodwHeight) - Number(modal_height_left)) / Number(2));

            jQuery('#arf-popup-form-<?php echo $form->id; ?> .arform_bottom_fixed_block_left').parents('.arform_bottom_fixed_main_block_left').find('.arform_bottom_fixed_form_block_left_main').css('margin-top', '-35px');
            jQuery("#arf-popup-form-<?php echo $form->id; ?>.arform_bottom_fixed_main_block_left").css("display", "inline-block");


            jQuery('.arf_popup_<?php echo $arf_popup_data_uniq_id; ?>').find('.arform_modal_stickybottom_<?php echo $form->id; ?>').css('transform-origin', 'left top');
            if (jQuery.browser.version == 8.0 && jQuery.browser.msie) {

                jQuery('.arf_popup_<?php echo $arf_popup_data_uniq_id; ?>').find(".arform_modal_stickybottom_<?php echo $form->id; ?>").css("left", "0");
            }
    <?php
}
if ($type == 'sticky' && $position == 'right') {
    ?>
            var modal_height_right = '<?php echo $modal_height; ?>';

            jQuery(".arform_bottom_fixed_main_block_right").css("top", Number(Number(winodwHeight) - Number(modal_height_right)) / Number(2));

            jQuery('#arf-popup-form-<?php echo $form->id; ?> .arform_bottom_fixed_block_right').parents('.arform_bottom_fixed_main_block_right').find('.arform_bottom_fixed_form_block_right_main').css('margin-top', '-35px');
            jQuery("#arf-popup-form-<?php echo $form->id; ?>.arform_bottom_fixed_main_block_right").css("display", "inline-block");

            jQuery('.arf_popup_<?php echo $arf_popup_data_uniq_id; ?>').find('.arform_modal_stickybottom_<?php echo $form->id; ?>').css('transform-origin', 'right top');
 
            if (jQuery.browser.version == 8.0 && jQuery.browser.msie) {

                jQuery('.arf_popup_<?php echo $arf_popup_data_uniq_id; ?>').find(".arform_modal_stickybottom_<?php echo $form->id; ?>").css("right", "0");
            }
<?php } ?>
    });
</script>


<style type="text/css">
    .arforms_model_view_<?php echo $form->id; ?> .arfmodal-backdrop,
    .arforms_model_view_<?php echo $form->id; ?> .arfmodal-backdrop.arffade.in {
        opacity: <?php echo $overlay_value; ?>;
        filter: alpha(opacity=<?php echo ($overlay_value * 100); ?>);
    }
    <?php if(!empty($modal_bgcolor)){ ?>
        .arforms_model_view_<?php echo $form->id; ?> .arfmodal-backdrop {background-color: <?php echo $modal_bgcolor; ?>;}
    <?php } ?>
<?php if ($type == 'button') { ?>

        .arform_modal_button_popup_<?php echo $arf_popup_data_uniq_id; ?> {
            background:<?php echo $bgcolor; ?>!important;
            color:<?php echo $txtcolor; ?>!important;
        }
<?php } ?>
</style>
<?php 
$hiddenvalue = "<input type='hidden' id='arf_settings_recaptcha_v2_public_key' value='{$arfsettings->pubkey}' />";
$hiddenvalue .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_theme' value='{$arfsettings->re_theme}' />";
$hiddenvalue .= "<input type='hidden' id='arf_settings_recaptcha_v2_public_lang' value='{$arfsettings->re_lang}' />";
echo $hiddenvalue;
?>