<?php

global $current_user, $arformcontroller;
?>

<style>


    .wrap table.widefat {  


        background:none;


        width:98%;


    }


    .widefat th {


        background:#F9F9F9;


    }


    .widefat td {


        border:none;


        padding:10px 0px 10px 50px;


    } 

    .txtstandardnew {

        width:400px !important;

    }

    .lbltitle {

        font-size:14px !important;

    }
    .tdclass {

        padding-bottom:20px !important;
        padding-left:0px !important;

    }

    #autoresponder_settings .tdclass { 

        padding-bottom:25px !important;

    }

    .txtmultinew {

        width:400px !important;
        height:90px !important;
    }

    .txtmultinew.testmailmsg{
	height:50px !important
    }

    .dotted_line {
        border-bottom:1px solid #E3E4E7 !important;
    }

    #poststuff #post-body {
        margin-top: 35px !important;
    }

    .wrap .frm_verify_li {
        color:green;
    }
    .arfdisabled{
	cursor:not-allowed !important;
    }
</style>


<div class="wrap arf_setting_page">

    <span class="h2" style="padding-left:30px;padding-top:30px;position: absolute;"><?php _e('Global Settings', 'ARForms'); ?></span>

    <div id="poststuff" class="metabox-holder">


        <div id="post-body" style="margin-top:80px !important;">

            <div class="inside" style="background-color:#ffffff;">    

                <div class="formsettings1" style="border-bottom-left-radius: 0px; border-bottom-right-radius: 0px; background-color:#ffffff;">

                    <div class="setting_tabrow" style="background-color:#ffffff;">



                        <div class="tab" style="background-color:#ffffff;">
                            <?php
                            $setting_tab = get_option('arf_current_tab');
                            $setting_tab = (!isset($setting_tab) || empty($setting_tab) ) ? 'general_settings' : $setting_tab;
                            ?>            

                            <ul id="arfsettingpagenav" class="arfmainformnavigation" style="height:42px; padding-bottom:0px; margin-bottom:0px;">


                                <li class="general_settings <?php
                                if ($setting_tab == 'general_settings') {
                                    echo 'btn_sld';
                                } else {
                                    echo 'tab-unselected';
                                }
                                ?>"> <a href="javascript:show_form_settimgs('general_settings','autoresponder_settings');">
                                            <?php if ($setting_tab == 'general_settings') { ?>
                                            <img id="general_settings_img" src="<?php echo ARFIMAGESURL; ?>/general_settings.png" height="15" width="16" />
                                        <?php } else { ?>
                                            <img id="general_settings_img" src="<?php echo ARFIMAGESURL; ?>/general_settings_hover.png" height="15" width="16" />
                                        <?php } ?>
                                        &nbsp;&nbsp;<?php _e('General Settings', 'ARForms'); ?></a></li>


                                <li class="autoresponder_settings <?php
                                if ($setting_tab == 'autoresponder_settings') {
                                    echo 'btn_sld';
                                } else {
                                    echo 'tab-unselected';
                                }
                                ?>"><a href="javascript:show_form_settimgs('autoresponder_settings','general_settings');">            
                                            <?php if ($setting_tab == 'autoresponder_settings') { ?>
                                            <img id="autoresponder_settings_img" src="<?php echo ARFIMAGESURL; ?>/autoresponder_settings.png" height="15" width="16" />
                                        <?php } else { ?>
                                            <img id="autoresponder_settings_img" src="<?php echo ARFIMAGESURL; ?>/autoresponder_settings_hover.png" height="15" width="16" />
                                        <?php } ?>
                                        &nbsp;&nbsp;<?php _e('Email Marketing Tools', 'ARForms'); ?></a></li>

                                <?php foreach ($sections as $sec_name => $section) { ?>


                                    <li><a href="#<?php echo $sec_name ?>_settings"><?php echo ucfirst($sec_name) ?></a></li>


                                <?php } ?>

                            </ul>


                            <div class="clear"></div>



                        </div>


                    </div>

                </div>




                <div class="clear"></div>

                <form name="frm_settings_form" method="post" enctype="multipart/form-data" class="frm_settings_form" onsubmit="return global_form_validate();">


                    <input type="hidden" name="arfaction" value="process-form" />

                    <input type="hidden" name="arfcurrenttab" id="arfcurrenttab" value="<?php echo get_option('arf_current_tab'); ?>" />

                    <?php wp_nonce_field('update-options'); ?>

                    <div style="margin-left: 15px;">
                        <?php
                        if (isset($message) && $message != '') {
                            if (is_admin()) {
                                ?><div id="success_message" style="margin-bottom:0px !important; margin-top:30px !important; width:95%;"><div class="arfsuccessmsgicon"></div><div class="arf_success_message"><?php
                            } echo $message;
                            if (is_admin()) {
                                ?></div></div><?php
                            }
                        }
                        ?>

                        <?php if (isset($errors) && is_array($errors) && count($errors) > 0) { ?>



                            <div style="margin-bottom:0px; margin-top:8px;">

                                <ul id="frm_errors" style="margin-bottom: 3px; margin-top: 3px;">

                                    <?php
                                    foreach ($errors as $error)
                                        echo '<li><div class="arferrmsgicon"></div><div id="error_message">' . stripslashes($error) . '</div></li>';
                                    ?>

                                </ul>

                            </div>

                        <?php } ?>
                    </div>

                    <div style="clear:both"></div>




                    <div id="general_settings" style="border-top:none; background-color:#FFFFFF; border-radius:5px 5px 5px 5px; padding-top:10px; padding-left: 20px; padding-top: 30px; padding-bottom:1px; <?php if ($setting_tab != 'general_settings') echo 'display:none;'; ?>">


                        <table class="form-table" style="margin-top:0px;">

                            <?php
                            $hostname = $_SERVER["SERVER_NAME"];

                            $setact = 0;
                            global $arformsplugin;
                            $setact = $arformcontroller->$arformsplugin(); ?>

                            <tr class="arfmainformfield" valign="top">


                                <td class="lbltitle" colspan="2"><?php _e('RECAPTCHA Configuration', 'ARForms'); ?>&nbsp;


                                </td>

                            </tr> 

                            <tr class="arfmainformfield" valign="top">

                                <td colspan="2" style="padding-left:30px; padding-bottom:20px;">


                                    <label class="lblsubtitle">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo stripslashes(__('reCAPTCHA requires an API key, consisting of a "site" and a "private" key. You can sign up for a', 'ARForms')); ?> <a href="https://www.google.com/recaptcha/admin/create"><?php _e('free reCAPTCHA key', 'ARForms'); ?></a>.</label>


                                </td>

                            </tr>



                            <tr class="arfmainformfield" valign="top">


                                <td class="tdclass" style="padding-left:30px;" width="18%">
                                  


                                    <label class="lblsubtitle"><?php _e('Site Key', 'ARForms') ?></label>

                                </td>

                                <td>	
                                    <input type="text" name="frm_pubkey" id="frm_pubkey" class="txtstandardnew" size="42" value="<?php echo esc_attr($arfsettings->pubkey) ?>" />



                                </td>
                            </tr>


                            <tr class="arfmainformfield" valign="top">

                                <td class="tdclass">        			
                                    

                                    <label class="lblsubtitle"><?php _e('Private Key', 'ARForms') ?></label>

                                </td>

                                <td>	

                                    <input type="text" name="frm_privkey" id="frm_privkey" class="txtstandardnew" size="42" value="<?php echo esc_attr($arfsettings->privkey) ?>" />


                                </td>


                            </tr>





                            <tr class="arfmainformfield" valign="top">


                                <td class="tdclass">


                                    <label class="lblsubtitle" style="padding-right:10px;"><?php _e('reCAPTCHA Theme', 'ARForms') ?></label>

                                </td>    

                                <td style="padding-bottom:10px;">

                                    <div class="sltstandard" style="float:none;">

                                        <select name="frm_re_theme" id="frm_re_theme" style="width:180px;" data-width='230px'>


                                            <?php foreach (array('light' => __('Light', 'ARForms'), 'dark' => __('Dark', 'ARForms')) as $theme_value => $theme_name) { ?>


                                                <option value="<?php echo esc_attr($theme_value) ?>" <?php selected($arfsettings->re_theme, $theme_value) ?>><?php echo $theme_name ?></option>


                                            <?php } ?>


                                        </select></div>

                                </td>

                            </tr>     


                            <tr class="arfmainformfield" valign="top">

                                <td class="tdclass">	          

                                    <label class="lblsubtitle" style="margin-top:5px; padding-right:10px;"><?php _e('reCAPTCHA Language', 'ARForms') ?></label>

                                </td>    

                                <td style="padding-bottom:10px;">

                                    <div class="sltstandard" style="float:none;  margin-top:5px;">
                                        <select name="frm_re_lang" id="frm_re_lang" style="width:180px;" data-width='230px' data-size='10'>

                                            <?php
                                            $rclang = array();
                                            $rclang['en'] = __('English (US)', 'ARForms');
                                            $rclang['ar'] = __('Arabic', 'ARForms');
                                            $rclang['bn'] = __('Bengali', 'ARForms');
                                            $rclang['bg'] = __('Bulgarian', 'ARForms');
                                            $rclang['ca'] = __('Catalan', 'ARForms');
                                            $rclang['zh-CN'] = __('Chinese(Simplified)', 'ARForms');
                                            $rclang['zh-TW'] = __('Chinese(Traditional)', 'ARForms');
                                            $rclang['hr'] = __('Croatian', 'ARForms');
                                            $rclang['cs'] = __('Czech', 'ARForms');
                                            $rclang['da'] = __('Danish', 'ARForms');
                                            $rclang['nl'] = __('Dutch', 'ARForms');
                                            $rclang['en-GB'] = __('English (UK)', 'ARForms');
                                            $rclang['et'] = __('Estonian', 'ARForms');
                                            $rclang['fil'] = __('Filipino', 'ARForms');
                                            $rclang['fi'] = __('Finnish', 'ARForms');
                                            $rclang['fr'] = __('French', 'ARForms');
                                            $rclang['fr-CA'] = __('French (Canadian)', 'ARForms');
                                            $rclang['de'] = __('German', 'ARForms');
                                            $rclang['gu'] = __('Gujarati', 'ARForms');
                                            $rclang['de-AT'] = __('German (Autstria)', 'ARForms');
                                            $rclang['de-CH'] = __('German (Switzerland)', 'ARForms');
                                            $rclang['el'] = __('Greek', 'ARForms');
                                            $rclang['iw'] = __('Hebrew', 'ARForms');
                                            $rclang['hi'] = __('Hindi', 'ARForms');
                                            $rclang['hu'] = __('Hungarian', 'ARForms');
                                            $rclang['id'] = __('Indonesian', 'ARForms');
                                            $rclang['it'] = __('Italian', 'ARForms');
                                            $rclang['ja'] = __('Japanese', 'ARForms');
                                            $rclang['kn'] = __('Kannada', 'ARForms');
                                            $rclang['ko'] = __('Korean', 'ARForms');
                                            $rclang['lv'] = __('Latvian', 'ARForms');
                                            $rclang['lt'] = __('Lithuanian', 'ARForms');
                                            $rclang['ms'] = __('Malay', 'ARForms');
                                            $rclang['ml'] = __('Malayalam', 'ARForms');
                                            $rclang['mr'] = __('Marathi', 'ARForms');
                                            $rclang['no'] = __('Norwegian', 'ARForms');
                                            $rclang['fa'] = __('Persian', 'ARForms');
                                            $rclang['pl'] = __('Polish', 'ARForms');
                                            $rclang['pt'] = __('Portuguese', 'ARForms');
                                            $rclang['pt-BR'] = __('Portuguese (Brazil)', 'ARForms');
                                            $rclang['pt-PT'] = __('Portuguese (Portugal)', 'ARForms');
                                            $rclang['ro'] = __('Romanian', 'ARForms');
                                            $rclang['ru'] = __('Russian', 'ARForms');
                                            $rclang['sr'] = __('Serbian', 'ARForms');
                                            $rclang['sk'] = __('Slovak', 'ARForms');
                                            $rclang['sl'] = __('Slovenian', 'ARForms');
                                            $rclang['es'] = __('Spanish', 'ARForms');
                                            $rclang['es-149'] = __('Spanish (Latin America)', 'ARForms');
                                            $rclang['sv'] = __('Swedish', 'ARForms');
                                            $rclang['ta'] = __('Tamil', 'ARForms');
                                            $rclang['te'] = __('Telugu', 'ARForms');
                                            $rclang['th'] = __('Thai', 'ARForms');
                                            $rclang['tr'] = __('Turkish', 'ARForms');
                                            $rclang['uk'] = __('Ukrainian', 'ARForms');
                                            $rclang['ur'] = __('Urdu', 'ARForms');
                                            $rclang['vi'] = __('Vietnamese', 'ARForms');


                                            ?>

                                            <?php foreach ($rclang as $lang => $lang_name) { ?>


                                                <option value="<?php echo esc_attr($lang) ?>" <?php selected($arfsettings->re_lang, $lang) ?>><?php echo $lang_name ?></option>


                                            <?php } ?>


                                        </select>
                                    </div>

                                </td>


                            </tr>    


                            <tr class="arfmainformfield" valign="top">
                                <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                            </tr>

                            <?php
                            if (is_rtl()) {
                                $float_style = 'float:right;';
                            } else {
                                $float_style = 'float:left;';
                            }
                            ?>
                            <tr class="arfmainformfield">
                                <td valign="top" colspan="2" class="lbltitle"><br /><?php _e('Default Messages On Form', 'ARForms'); ?> </td>
                            </tr>

                            <tr>
                                <td class="tdclass" >        
                                    <label class="lblsubtitle"><?php _e('Blank Field', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> <br/>
                                </td>
                                <td class="arfmainformfield" >
                                    <input type="text" id="frm_blank_msg" name="frm_blank_msg" class="txtstandardnew" value="<?php echo esc_attr($arfsettings->blank_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                    <div class="tooltip_main" style=" <?php echo $float_style; ?>"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php _e('Message will be displayed when required fields is left blank.', 'ARForms') ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                    <div style="clear:both"></div>
                                    <div class="arferrmessage" id="arfblankerrmsg" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>
                                </td>


                            </tr>





                            <tr class="arfmainformfield">


                                <td class="tdclass">        
                                    <label class="lblsubtitle"><?php _e('Incorrect Field', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> <br/>

                                </td>

                                <td >
                                    <input type="text" id="arfinvalidmsg" name="frm_invalid_msg" class="txtstandardnew" value="<?php echo esc_attr($arfsettings->invalid_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                    <div class="tooltip_main" style=" <?php echo $float_style; ?>"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php _e('Message will be displayed when incorrect data is inserted of missing.', 'ARForms') ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                    <div style="clear:both"></div>
                                    <div class="arferrmessage" id="arfinvalidmsg_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>
                                </td>


                                </td>


                            </tr>


                            <tr class="arfmainformfield">


                                <td class="tdclass">


                                    <label class="lblsubtitle"><?php _e('Success Message', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> </td>

                                <td> 

                                    <input type="text" id="arfsuccessmsg" name="frm_success_msg" class="txtstandardnew" value="<?php echo esc_attr($arfsettings->success_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                    <div class="tooltip_main" style=" <?php echo $float_style; ?>"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php _e('Default message displayed after form is submitted.', 'ARForms') ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                    <div style="clear:both"></div>

                                    <div class="arferrmessage" id="arfsuccessmsgerr" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>


                                </td>


                            </tr>


                            <tr class="arfmainformfield">


                                <td class="tdclass">        


                                    <label class="lblsubtitle"><?php _e('Submission Failed Message', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>

                                <td >	

                                    <input type="text" id="arfmessagefailed" name="frm_failed_msg" class="txtstandardnew" value="<?php echo esc_attr($arfsettings->failed_msg) ?>" style=" <?php echo $float_style; ?>"/>

                                    <div class="tooltip_main" style=" <?php echo $float_style; ?>"><img src="<?php echo ARFIMAGESURL ?>/tooltips-icon.png" alt="?" class="arfhelptip" title="<?php _e('Message will be displayed when form is submitted but Duplicate entry exists.', 'ARForms') ?>" style="margin-left:10px; margin-top:4px;"/></div>
                                    <div style="clear:both"></div>

                                    <div class="arferrmessage" id="arferrormessagefailed" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>


                                </td>


                            </tr> 


                            <tr class="arfmainformfield">


                                <td class="tdclass" >    


                                    <label class="lblsubtitle"><?php _e('Default Submit Button', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label></td>


                                <td >

                                    <input type="text" class="txtstandardnew" value="<?php echo esc_attr($arfsettings->submit_value) ?>" id="arfvaluesubmit" name="frm_submit_value" />
                                    <div class="arferrmessage" id="arferrorsubmitvalue" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>


                                </td>


                            </tr>
                            <tr class="arfmainformfield" valign="top">
                                <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                            </tr>


                            <tr class="arfmainformfield">
                                <td valign="top" colspan="2" class="lbltitle"><br /><?php _e('Email Settings', 'ARForms'); ?></td>
                            </tr>

                            <tr>


                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('From/Replyto Name', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> </td>


                                <td valign="top" style="padding-bottom:10px;">


                                    <input type="text" class="txtstandardnew" id="frm_reply_to_name" name="frm_reply_to_name" value="<?php echo $arfsettings->reply_to_name; ?>" style="width:400px;">
                                    <div class="arferrmessage" id="frm_reply_to_name_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>

                                </td>


                            </tr>


                            <tr>


                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('From/Replyto Email', 'ARForms'); ?>&nbsp;&nbsp;<span style="vertical-align:middle" class="arfglobalrequiredfield">*</span></label> </td>


                                <td valign="top" style="padding-bottom:10px;">


                                    <input type="text" class="txtstandardnew" id="frm_reply_to" name="frm_reply_to" value="<?php echo $arfsettings->reply_to; ?>" style="width:400px;">
                                    <div class="arferrmessage" id="frm_reply_to_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div>
                                </td>


                            </tr>

                            <tr>
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Send Email SMTP', 'ARForms'); ?></label> </td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input type="radio" name="frm_smtp_server" class="rdostandard" id="arf_wordpress_smtp" onchange="arfchangesmtpsetting();" value="wordpress" <?php checked($arfsettings->smtp_server, 'wordpress'); ?> /><label for="arf_wordpress_smtp" style="margin-right:20px;"><span></span><?php _e('Wordpress Server', 'ARForms'); ?></label>
                                    <input type="radio" name="frm_smtp_server" class="rdostandard" id="arf_custom_custom" onchange="arfchangesmtpsetting();" value="custom" <?php checked($arfsettings->smtp_server, 'custom'); ?> /><label for="arf_custom_custom"><span></span><?php _e('SMTP Server', 'ARForms'); ?></label>
                                </td>
                            </tr>




                            <tr class="arfsmptpsettings" <?php echo ($arfsettings->smtp_server != 'custom') ? 'style="display:none;"' : ''; ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Authentication', 'ARForms'); ?></label> </td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <p>
                                        <input type="checkbox" class="chkstanard" onclick="arf_is_smtp_authentication();" id="is_smtp_authentication" name="is_smtp_authentication" value="1" <?php checked($arfsettings->is_smtp_authentication, 1) ?> style="border:none;">
                                        <label for="is_smtp_authentication"><span></span><?php _e('Enable SMTP authentication', 'ARForms') ?></label> 
                                    </p>
                                </td>
                            </tr>


                            <tr class="arfsmptpsettings" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('SMTP Host', 'ARForms'); ?></label></td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input type="text" class="txtstandardnew" id="frm_smtp_host" name="frm_smtp_host" value="<?php echo $arfsettings->smtp_host; ?>" style="width:400px;">
                                </td>
                            </tr>

                            <tr class="arfsmptpsettings" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('SMTP Port', 'ARForms'); ?></label></td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input onkeyup="arf_show_test_mail();" type="text" class="txtstandardnew" id="frm_smtp_port" name="frm_smtp_port" value="<?php echo $arfsettings->smtp_port; ?>" style="width:400px;">
                                </td>
                            </tr>



                            <tr class="arfsmptpsettings arf_authentication_field" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            } else {
                                if ($arfsettings->is_smtp_authentication != '1') {
                                    echo 'style="display:none;"';
                                }
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('SMTP Username', 'ARForms'); ?></label></td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input onkeyup="arf_show_test_mail();" type="text" class="txtstandardnew" id="frm_smtp_username" name="frm_smtp_username" value="<?php echo $arfsettings->smtp_username; ?>" style="width:400px;">
                                </td>
                            </tr>


                            <tr class="arfsmptpsettings arf_authentication_field" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            } else {
                                if ($arfsettings->is_smtp_authentication != '1') {
                                    echo 'style="display:none;"';
                                }
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('SMTP Password', 'ARForms'); ?></label></td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input onkeyup="arf_show_test_mail();" type="password" class="txtstandardnew" id="frm_smtp_password" name="frm_smtp_password" value="<?php echo $arfsettings->smtp_password; ?>" style="width:400px;">


                                </td>
                            </tr>


                            <tr class="arfsmptpsettings" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('SMTP Encription', 'ARForms'); ?></label></td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <input type="radio" name="frm_smtp_encryption" class="rdostandard" id="frm_smtp_encryption_none" value="none" <?php checked($arfsettings->smtp_encryption, 'none'); ?> /><label for="frm_smtp_encryption_none" style="margin-right:20px;"><span></span><?php _e('None', 'ARForms'); ?></label>
                                    <input type="radio" name="frm_smtp_encryption" class="rdostandard" id="frm_smtp_encryption_ssl" value="ssl" <?php checked($arfsettings->smtp_encryption, 'ssl'); ?> /><label for="frm_smtp_encryption_ssl" style="margin-right:20px;"><span></span><?php _e('SSL', 'ARForms'); ?></label>
                                    <input type="radio" name="frm_smtp_encryption" class="rdostandard" id="frm_smtp_encryption_tls" value="tls" <?php checked($arfsettings->smtp_encryption, 'tls'); ?> /><label for="frm_smtp_encryption_tls"><span></span><?php _e('TLS', 'ARForms'); ?></label>
                                </td>
                            </tr>
                            <?php
                            $smtp_test_mail_style = "disabled='disabled'";
                            $smtp_test_main_class = "arfdisabled";

                            if ($arfsettings->is_smtp_authentication == '1') {
                                if ($arfsettings->smtp_server == "custom" && $arfsettings->smtp_port != "" && $arfsettings->smtp_host != "" && $arfsettings->smtp_username != "" && $arfsettings->smtp_password != "") {
                                    $smtp_test_mail_style = "";
                                    $smtp_test_main_class = "";
                                } else {
                                    $smtp_test_mail_style = "disabled='disabled'";
                                    $smtp_test_main_class = "arfdisabled";
                                }
                            } else {
                                if ($arfsettings->smtp_server == "custom" && $arfsettings->smtp_port != "" && $arfsettings->smtp_host != "") {
                                    $smtp_test_mail_style = "";
                                    $smtp_test_main_class = "";
                                } else {
                                    $smtp_test_mail_style = "disabled='disabled'";
                                    $smtp_test_main_class = "arfdisabled";
                                }
                            }
                            ?>
                            <tr class="arfsmptpsettings" <?php
                            if ($arfsettings->smtp_server != 'custom') {
                                echo 'style="display:none;"';
                            }
                            ?> >
                                <td class="tdclass" valign="top" style="padding-left:20px;">
                                    <label class="lbltitle">
                                        <?php _e('Send Test E-mail', 'ARForms'); ?>
                                    </label>
                                </td>
                                <td valign="top" style="padding-bottom:10px;">
                                    <label id="arf_success_test_mail" style="font-size:12px; display:none; float:left; width:50%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#4c9738;"><?php _e('Your test mail is successfully sent', 'ARForms'); ?> </label>
                                    <label id="arf_error_test_mail" style="font-size:12px; display:none; float:left; width:50%; font-family:'open_sanssemibold',Arial,Helvetica,Verdana,sans-serif; color:#ff0000;"><?php _e('Your test mail is not sent for some reason, Please check your SMTP setting', 'ARForms'); ?> </label>
				</td>
			    </tr>
			    <tr class="arfsmptpsettings" <?php
if ($arfsettings->smtp_server != 'custom') {
    echo 'style="display:none;"';
}
?> >
				<td class="tdclass" valign="top" style="padding-left:20px;">
				    <label class="lblsubtitle">
<?php _e('To', 'ARForms'); ?>
				    </label>
				</td>
				<td valign="top" style="padding-bottom:10px;">
				    <input type="text" id="sendtestmail_to" name="sendtestmail_to" class="txtstandardnew <?php echo $smtp_test_main_class; ?>" value="<?php echo isset($arfsettings->smtp_send_test_mail_to) ? $arfsettings->smtp_send_test_mail_to : '' ?>" <?php echo $smtp_test_mail_style; ?> />
				</td>
			    </tr>

			    <tr class="arfsmptpsettings" <?php
if ($arfsettings->smtp_server != 'custom') {
    echo 'style="display:none;"';
}
?> >
				<td class="tdclass" valign="top" style="padding-left:20px;">
				    <label class="lblsubtitle">
<?php _e('Message', 'ARForms'); ?>
				    </label>
				</td>
				<td valign="top" style="padding-bottom:10px;">
				    <textarea class="txtmultinew testmailmsg  <?php echo $smtp_test_main_class; ?>" name="sendtestmail_msg" <?php echo $smtp_test_mail_style; ?> id="sendtestmail_msg" ><?php echo isset($arfsettings->smtp_send_test_mail_msg) ? $arfsettings->smtp_send_test_mail_msg : '' ?></textarea>
				</td>
			    </tr>

			    <tr class="arfsmptpsettings" <?php
			    if ($arfsettings->smtp_server != 'custom') {
				echo 'style="display:none;"';
			    }
?> >
				<td class="tdclass" valign="top" style="padding-left:20px;">
				    <label class="lblsubtitle">&nbsp;</label>
				</td>
				<td valign="top" style="padding-bottom:10px;">
				    <input type="button" value="<?php _e('Send test mail', 'ARForms'); ?>" class="arfbulkbtn arfemailaddbtn <?php echo $smtp_test_main_class; ?>" id="arf_send_test_mail" <?php echo $smtp_test_mail_style; ?> style="margin-left:0px;color:#ffffff;"> <img src="<?php echo ARFIMAGESURL . '/ajax_loader_gray_32.gif'; ?>" id="arf_send_test_mail_loader" style="display:none;position:relative;left:5px;top:5px;" width="16" height="16" /> <span style="font-style:italic;">(<?php _e('Test e-mail works only after configure SMTP server settings','ARForms'); ?>)</span>
				</td>
			    </tr>
                            <tr class="arfmainformfield" valign="top">
                                <td colspan="2"><div style="width:96%" class="dotted_line"></div></td>
                            </tr>


                            <tr class="arfmainformfield">
                                <td valign="top" colspan="2" class="lbltitle"><br /><?php _e('Other Settings', 'ARForms'); ?></td>
                            </tr>

                            <tr>

                                <?php if ($setact == 1) { ?>
                                <tr>


                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Rebranding', 'ARForms'); ?></label> </td>


                                    <td valign="top" style="padding-bottom:10px;">


                                        <p><input type="checkbox" class="chkstanard" id="arfmainformbrand" name="arfmainformbrand" value="1" <?php checked($arfsettings->brand, 1) ?> style="border:none;"><label for="arfmainformbrand"><span></span><?php _e('Remove rebranding link', 'ARForms') ?></label> 

                                        </p>


                                    </td>


                                </tr>

                                <tr>


                                    <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Affiliate Code', 'ARForms'); ?></label> </td>


                                    <td valign="top" style="padding-bottom:10px;">
                                        <input type="text" class="txtstandardnew" id="affiliate_code" name="affiliate_code" value="<?php echo $arfsettings->affiliate_code; ?>" style="width:400px;">
                                    </td>


                                </tr>

                            <?php } else { ?>
                                <input type="hidden" name="arfmainformbrand" value="0"  />
                            <?php } ?>
                            <tr>


                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Form Submission Method', 'ARForms'); ?></label> </td>


                                <td valign="top" style="padding-bottom:10px;">


                                    <input type="radio" class="rdostandard" onchange="arf_change_form_submission_type(this);" id="ajax_base_sbmt" <?php
                                    if ($arfsettings->form_submit_type == 1) {
                                        echo 'checked="checked"';
                                    } else {
                                        echo '';
                                    }
                                    ?> name="arfmainformsubmittype" value="1" style="margin-top:3px;"/><label class="lblsubtitle" for="ajax_base_sbmt"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Ajax based submission', 'ARForms'); ?></div></label>&nbsp; &nbsp; 
                                    <input type="radio" onchange="arf_change_form_submission_type(this);" class="rdostandard" id="normal_form_sbmt" <?php if ($arfsettings->form_submit_type == 0) echo 'checked="checked"'; ?> name="arfmainformsubmittype" value="0" style="margin-top:3px;"/><label class="lblsubtitle" for="normal_form_sbmt"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Normal submission', 'ARForms'); ?></div></label>&nbsp; &nbsp;


                                </td>


                            </tr>
                            <tr class="arf_success_message_show_time_wrapper" <?php
                            if ($arfsettings->form_submit_type == 0) {
                                echo 'style="display: none"';
                            }
                            ?> >


                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Success Message', 'ARForms'); ?></label> </td>


                                <td valign="top" style="padding-bottom:10px;">
                                    <?php
                                    if (!(isset($arfsettings->arf_success_message_show_time) && $arfsettings->arf_success_message_show_time >= 0)) {
                                        $arfsettings->arf_success_message_show_time = 3;
                                    }
                                    ?>
                                    <div class="arf_success_message_show_time_inner"><?php _e('Hide success message after', 'ARForms'); ?>
                                        <input type="text" name="arf_success_message_show_time" onkeydown="arfvalidatenumber_admin(this, event);" maxlength="3" value="<?php echo esc_attr($arfsettings->arf_success_message_show_time) ?>" class="arf_success_message_show_time txtstandardnew"/>
                                        <?php _e('seconds', 'ARForms'); ?></div>
                                    <div class="arf_success_message_show_time_inner">( <?php _e('Note : 0 ( zero ) means it will never hide success message', 'ARForms'); ?> )</div>


                                </td>


                            </tr>

                            <tr>

                                <td class="tdclass" valign="top" style="padding-left:30px;"><label class="lblsubtitle"><?php _e('Form Global CSS', 'ARForms'); ?></label> </td>

                                <td valign="top" style="padding-bottom:10px;"><textarea name="arf_global_css" id="arf_global_css" class="txtmultinew"><?php echo stripslashes_deep(get_option('arf_global_css')); ?></textarea></td>

                            </tr>
                            
                            
                            
                            
                            
                            <tr>

                                <td class="tdclass" valign="top" style="padding-left:30px; vertical-align:top;"><label class="lblsubtitle"><?php _e('Choose the character sets you want to add with google fonts', 'ARForms'); ?></label> </td>
                                
                                <td valign="top" style="padding-bottom:10px;">

                                    <?php
                                    $arf_character_arr = array('latin' => 'Latin', 'latin-ext' => 'Latin-ext', 'menu' => 'Menu', 'greek' => 'Greek', 'greek-ext' => 'Greek-ext', 'cyrillic' => 'Cyrillic',
                                        'cyrillic-ext' => 'Cyrillic-ext', 'vietnamese' => 'Vietnamese', 'arabic' => 'Arabic', 'khmer' => 'Khmer', 'lao' => 'Lao', 'tamil' => 'Tamil', 'bengali' => 'Bengali',
                                        'hindi' => 'Hindi', 'korean' => 'Korean');
                                    ?>
                                    <div style="width:400px; float:left;">
                                        <span style="width:400px; float:left;">
                                            <?php $arf_chk_counter = 1; ?>    
                                            <?php
                                            foreach ($arf_character_arr as $arf_character => $arf_character_value) {
                                                $default_charset = ( isset($arfsettings->arf_css_character_set[$arf_character]) ) ? $arfsettings->arf_css_character_set[$arf_character] : '';
                                                ?>
                                                <p style="width: 100px; float: left;"><input type="checkbox" class="chkstanard" id="arf_character_<?php echo $arf_character; ?>" name="arf_css_character_set[<?php echo $arf_character; ?>]" <?php checked($default_charset, $arf_character); ?> value="<?php echo $arf_character; ?>" style="border:none;"><label for="arf_character_<?php echo $arf_character; ?>"><span></span><?php echo $arf_character_value; ?></label></p>
                                                <?php echo ($arf_chk_counter % 4 == 0) ? '</span><span style="width:400px; float:left;">' : ''; ?>
                                                <?php $arf_chk_counter++; ?>    
<?php } ?>
                                        </span>
                                    </div>
                                </td>

                            </tr>
                            
                            
                            
                            

                            <input type="hidden" id="frm_permalinks" name="frm_permalinks" value="0" />

                        </table>


                    </div>


                    <div id="autoresponder_settings" style=" <?php if ($setting_tab != 'autoresponder_settings') echo 'display:none;'; ?> background-color:#FFFFFF; padding-top:10px; border-radius:5px 5px 5px 5px; padding-left: 20px; padding-top: 30px; padding-bottom:1px;">


                        <table class="wp-list-table widefat post " style="margin:0px 0 0 10px; border:none;">


                            <tr>

                                <th style="background:none; border:0px;" colspan="2"><img src="<?php echo ARFURL; ?>/images/aweber.png" align="absmiddle" /></th>
                            </tr>
                            <tr>

                                <?php $autores_type['aweber_type'] = ( $autores_type['aweber_type'] == 1 ) ? $autores_type['aweber_type'] : 0; ?>
                                <th style="background:none; border:0px;" width="18%">&nbsp;</th>
                                <th id="th_aweber" style=" background:none; border:none; <?php
                                if ($autores_type['aweber_type'] == 2)
                                    echo 'padding-left: 5px;';
                                else
                                    echo 'padding-left: 5px;';
                                ?>">
                                    <input type="radio" class="rdostandard" id="aweber_1" <?php if ($autores_type['aweber_type'] == 1) echo 'checked="checked"'; ?> name="aweber_type" value="1" style="margin-top:3px;" onclick="show_api('aweber');" /><label class="lblsubtitle" for="aweber_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using API', 'ARForms'); ?></div></label>&nbsp; &nbsp; 
                                    <input type="radio" class="rdostandard" id="aweber_2" <?php if ($autores_type['aweber_type'] == 0) echo 'checked="checked"'; ?> name="aweber_type" value="0" style="margin-top:3px;" onclick="show_web_form('aweber');" /><label class="lblsubtitle" for="aweber_2"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label>&nbsp; &nbsp;
                                    <input type="hidden" name="aweber_status" id="aweber_status" value="<?php echo $aweber_data->is_verify; ?>" />

                                </th>
                            </tr>

                            <tr id="aweber_api_tr1" <?php
                            if ($aweber_data->is_verify == '1') {
                                echo 'style="display:none;"';
                            } else if ($autores_type['aweber_type'] != 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>

                                <td class="tdclass" style="padding-right:20px; width:18%;"><label class="lblsubtitle"><?php _e('Enter consumer key', 'ARForms'); ?></label></td>

                                <td style=" padding-bottom:3px; padding-left:5px;"><input type="text" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="consumer_key" class="txtstandardnew" id="consumer_key" size="80" value="" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set aweber settings');" <?php } ?> />
                                    <div class="arferrmessage" id="consumer_key_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>

                            <tr id="aweber_api_tr2" <?php
                            if ($aweber_data->is_verify == '1') {
                                echo 'style="display:none;"';
                            } else if ($autores_type['aweber_type'] != 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>

                                <td class="tdclass" style="padding-right:20px; text-align:left; width:18%; padding-top:4px;"><label class="lblsubtitle"><?php _e('Enter consumer secret', 'ARForms'); ?></label></td>

                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="text" name="consumer_secret" class="txtstandardnew" id="consumer_secret" size="80" value="" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set aweber settings');" <?php } ?> />
                                    <div class="arferrmessage" id="consumer_secret_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>     

                            <tr id="aweber_api_tr3" <?php
                            if ($aweber_data->is_verify == '1') {
                                echo 'style="display:none;"';
                            } else if ($autores_type['aweber_type'] != 1) {
                                echo 'style="display:none;"';
                            }
                            ?>>

                                <td class="tdclass" style="padding-left:20px; text-align:left; width:18%;">&nbsp;</td>

                                <td><button class="greensavebtn"  style="width:120px; border:0px; color:#FFFFFF; height:40px; border-radius:3px;" type="button" name="continue" onclick="aweber_continue('<?php echo ARFAWEBERURL; ?>');"><img align="absmiddle" src="<?php echo ARFIMAGESURL ?>/continue_icon.png">&nbsp;&nbsp;<?php _e('Continue', 'ARForms') ?></button></td>

                            </tr>

                            <tr id="aweber_web_form_tr" <?php if ($autores_type['aweber_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="padding-right:20px; text-align:left; width:18%;"><label class="lblsubtitle"><?php _e('Webform code from Aweber', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="aweber_web_form" id="aweber_web_form" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set aweber settings');" <?php } ?>><?php echo stripslashes($aweber_data->responder_web_form); ?></textarea>


                                </td>

                            </tr>


                            <?php if ($aweber_data->responder_list_id != "") { ?>


                                <tr id="aweber_api_tr4" <?php if ($autores_type['aweber_type'] != 1) echo 'style="display:none;"'; ?>>


                                    <td class="tdclass" style="padding-right:20px; text-align:left; width:18%;"><label class="lblsubtitle"><?php _e('AWEBER LIST', 'ARForms'); ?></label></td>


                                    <td style="padding-left:5px; overflow: visible;">

                                        <span id="select_aweber">
                                            <div class="sltstandard" style="float:none; display:inline;">
                                                <select name="responder_list"  style="width:150px;" data-width='150px'>


                                                    <?php
                                                    $aweber_lists = explode("-|-", $aweber_data->responder_list_id);


                                                    $aweber_lists_name = explode("|", $aweber_lists[0]);


                                                    $aweber_lists_id = explode("|", $aweber_lists[1]);


                                                    $i = 0;


                                                    foreach ($aweber_lists_name as $aweber_lists_name1) {


                                                        if ($aweber_lists_id[$i] != "") {
                                                            ?>


                                                            <option value="<?php echo $aweber_lists_id[$i]; ?>" <?php
                                                            if ($aweber_lists_id[$i] == $aweber_data->responder_list) {
                                                                echo "selected=selected";
                                                            }
                                                            ?>><?php echo $aweber_lists_name1; ?></option>


                                                        <?php } ?>


                                                        <?php
                                                        $i++;
                                                    }
                                                    ?>


                                                </select>
                                            </div>

                                        </span>
                                        <span id="aweber_loader2" style="display:none;margin-left: 10px; padding-top: 6px; position: absolute;"><img align="absmiddle" src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>

                                        <div style="padding-left:5px; margin-top: 10px;" class="arlinks">
                                            <a href="javascript:void(0);" onclick="action_aweber('refresh');"><?php _e('Refresh List', 'ARForms'); ?></a>
                                            &nbsp;	&nbsp;	&nbsp;	&nbsp;
                                            <a href="javascript:void(0);" onclick="action_aweber('delete');"><?php _e('Delete Configuration', 'ARForms'); ?></a>
                                        </div>


                                    </td>


                                </tr>




                            <?php } ?>

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>
                        </table>


                        <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                            <tr>

                                <th style="background:none; border:none;" colspan="2"><img src="<?php echo ARFURL; ?>/images/mailchimp.png" align="absmiddle" /></th>

                                </th>

                            </tr>

                            <tr>
                                <?php $autores_type['mailchimp_type'] = ( $autores_type['mailchimp_type'] == 1 ) ? $autores_type['mailchimp_type'] : 0; ?>
                                <th style="width:18%; background:none; border:none;">&nbsp;</th>
                                <th id="th_mailchimp" style=" background:none; border:none; padding-left:5px; <?php
                                if ($autores_type['mailchimp_type'] == 2)
                                    echo 'padding-left: 5px;';
                                else
                                    echo 'padding-left: 5px;';
                                ?>">
                                    <input type="radio" class="rdostandard" id="mailchimp_1" <?php if ($autores_type['mailchimp_type'] == 1) echo 'checked="checked"'; ?> name="mailchimp_type" value="1" style="margin-top:3px;" onclick="show_api('mailchimp');" /><label class="lblsubtitle" for="mailchimp_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using API', 'ARForms'); ?></div></label> &nbsp; &nbsp; 
                                    <input type="radio" class="rdostandard" id="mailchimp_2" <?php if ($autores_type['mailchimp_type'] == 0) echo 'checked="checked"'; ?>  name="mailchimp_type" value="0" style="margin-top:3px;" onclick="show_web_form('mailchimp');" /><label class="lblsubtitle" for="mailchimp_2"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>

                            </tr>

                            <tr id="mailchimp_api_tr1" <?php if ($autores_type['mailchimp_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php _e('API Key', 'ARForms'); ?></label></td>

                                <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="mailchimp_api" class="txtstandardnew" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set mailchimp settings');" <?php } ?> id="mailchimp_api" size="80" onkeyup="show_verify_btn('mailchimp');" value="<?php echo $mailchimp_data->responder_api_key; ?>" /> &nbsp; &nbsp; 					
                                    <span id="mailchimp_link" <?php if ($mailchimp_data->is_verify == 1) { ?>style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('mailchimp', '0');" class="arlinks"><?php _e('Verify', 'ARForms'); ?></a></span>
                                    <span id="mailchimp_loader" style="display:none;"><img src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>   		
                                    <span id="mailchimp_verify" class="frm_verify_li" style="display:none;"><?php _e('Verified', 'ARForms'); ?></span>    
                                    <span id="mailchimp_error" class="frm_not_verify_li" style="display:none;"><?php _e('Not Verified', 'ARForms'); ?></span>
                                    <input type="hidden" name="mailchimp_status" id="mailchimp_status" value="<?php echo $mailchimp_data->is_verify; ?>" />
                                    <div class="arferrmessage" id="mailchimp_api_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>


                            <tr id="mailchimp_api_tr2" <?php if ($autores_type['mailchimp_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php _e('List ID', 'ARForms'); ?></label></td>

                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_mailchimp"><div class="sltstandard" style="float:none;display:inline;"><select name="mailchimp_listid" id="mailchimp_listid" <?php if ($mailchimp_data->is_verify == 0 || $mailchimp_data->responder_api_key == '') echo 'disabled="disabled"'; ?> style="width:150px;" data-width='150px'>
                                                <?php
                                                $lists = maybe_unserialize($mailchimp_data->responder_list_id);
                                                if (count($lists) > 0 and $lists != '') {

                                                    foreach ($lists as $list) {
                                                        if ($mailchimp_data->responder_list == $list['id'])
                                                            echo '<option selected="selected" value="' . $list['id'] . '">' . $list['name'] . '</option>';
                                                        else
                                                            echo '<option value="' . $list['id'] . '">' . $list['name'] . '</option>';
                                                    }
                                                }
                                                ?> 
                                            </select></div></span>
                                    <span id="mailchimp_loader2" style="display:none;margin-left: 10px; padding-top: 6px; position: absolute;"><img align="absmiddle"  src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>



                                    <div id="mailchimp_del_link" style="padding-left:5px; margin-top:10px;<?php if ($mailchimp_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">					
                                        <a href="javascript:void(0);" onclick="action_autores('refresh', 'mailchimp');"><?php _e('Refresh List', 'ARForms'); ?></a>
                                        &nbsp;	&nbsp;	&nbsp;	&nbsp;
                                        <a href="javascript:void(0);" onclick="action_autores('delete', 'mailchimp');"><?php _e('Delete Configuration', 'ARForms'); ?></a>
                                    </div>


                                </td>

                            </tr>

                            <tr id="mailchimp_web_form_tr" <?php if ($autores_type['mailchimp_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from Mailchimp', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="mailchimp_web_form" id="mailchimp_web_form" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set mailchimp settings');" <?php } ?>><?php echo stripslashes($mailchimp_data->responder_web_form); ?></textarea>



                                </td>

                            </tr>

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>

                        </table>            


                        <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                            <tr>

                                <th colspan="2" style="border:none; background:none;"><img src="<?php echo ARFURL; ?>/images/getresponse.png" align="absmiddle" /></th>

                            </tr>

                            <tr>
                                <?php $autores_type['getresponse_type'] = ( $autores_type['getresponse_type'] == 1 ) ? $autores_type['getresponse_type'] : 0; ?>
                                <th style="width:18%;  border:none; background:none;"></th>
                                <th id="th_getresponse" style=" padding-left:5px; border:none; background:none; <?php
                                if ($autores_type['getresponse_type'] == 2)
                                    echo 'padding-left: 5px;';
                                else
                                    echo 'padding-left: 5px;';
                                ?>">
                                    <input type="radio" class="rdostandard" id="getresponse_1" <?php if ($autores_type['getresponse_type'] == 1) echo 'checked="checked"'; ?> name="getresponse_type" value="1" style="margin-top:3px;" onclick="show_api('getresponse');" /><label class="lblsubtitle" for="getresponse_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using API', 'ARForms'); ?></div></label> &nbsp; &nbsp; 
                                    <input type="radio" class="rdostandard" id="getresponse_2" <?php if ($autores_type['getresponse_type'] == 0) echo 'checked="checked"'; ?> name="getresponse_type" value="0" style="margin-top:3px;" onclick="show_web_form('getresponse');" /><label class="lblsubtitle" for="getresponse_2"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>

                            </tr>

                            <tr id="getresponse_api_tr1" <?php if ($autores_type['getresponse_type'] != 1) echo 'style="display:none;"'; ?>>


                                <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php _e('API Key', 'ARForms'); ?></label></td>


                                <td style=" padding-bottom:3px; padding-left:5px;"><input type="text" name="getresponse_api" class="txtstandardnew" id="getresponse_api" size="80" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Getresponse settings');" <?php } ?> onkeyup="show_verify_btn('getresponse');" value="<?php echo $getresponse_data->responder_api_key; ?>" /> &nbsp; &nbsp; 

                                    <span id="getresponse_link" <?php if ($getresponse_data->is_verify == 1) { ?> style="display:none;"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('getresponse', '0');" class="arlinks"><?php _e('Verify', 'ARForms'); ?></a></span>
                                    <span id="getresponse_loader" style="display:none;"><img src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>
                                    <span id="getresponse_verify" class="frm_verify_li" style="display:none;"><?php _e('Verified', 'ARForms'); ?></span>                                
                                    <span id="getresponse_error" class="frm_not_verify_li" style="display:none;"><?php _e('Not Verified', 'ARForms'); ?></span>
                                    <input type="hidden" name="getresponse_status" id="getresponse_status" value="<?php echo $getresponse_data->is_verify; ?>" />
                                    <div class="arferrmessage" id="getresponse_api_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>


                            </tr>


                            <tr id="getresponse_api_tr2" <?php if ($autores_type['getresponse_type'] != 1) echo 'style="display:none;"'; ?>>


                                <td class="tdclass" style="width:18%; padding-right:20px; padding-top:3px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php _e('Campaign Name', 'ARForms'); ?></label></td>


                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_getresponse"><div class="sltstandard" style="float:none;display:inline;"><select name="getresponse_listid" id="getresponse_listid" <?php if ($getresponse_data->is_verify == 0 || $getresponse_data->responder_api_key == '') echo 'disabled="disabled"'; ?> style="width:150px;" data-width='150px'>
                                                <?php
                                                $lists = maybe_unserialize($getresponse_data->list_data);
                                                if ($lists && count($lists) > 0) {

                                                    foreach ($lists as $listid => $list) {
                                                        if ($getresponse_data->responder_list_id == $list['name'])
                                                            echo '<option selected="selected" value="' . $list['name'] . '">' . $list['name'] . '</option>';
                                                        else
                                                            echo '<option value="' . $list['name'] . '">' . $list['name'] . '</option>';
                                                    }
                                                }
                                                ?> 
                                            </select></div></span>
                                    <span id="getresponse_loader2" style="display:none;margin-left: 10px; padding-top: 6px; position: absolute;"><img align="absmiddle"  src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>


                                    <div id="getresponse_del_link" style="padding-left:5px; margin-top:10px;<?php if ($getresponse_data->is_verify == 0) { ?> display:none;<?php } ?>" class="arlinks">

                                        <a href="javascript:void(0);" onclick="action_autores('refresh', 'getresponse');"><?php _e('Refresh List', 'ARForms'); ?></a>
                                        &nbsp;	&nbsp;	&nbsp;	&nbsp;
                                        <a href="javascript:void(0);" onclick="action_autores('delete', 'getresponse');"><?php _e('Delete Configuration', 'ARForms'); ?></a>
                                    </div>


                                </td>


                            </tr>

                            <tr id="getresponse_web_form_tr" <?php if ($autores_type['getresponse_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from Getresponse', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="getresponse_web_form" id="getresponse_web_form" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Getresponse settings');" <?php } ?>><?php echo stripslashes($getresponse_data->responder_web_form); ?></textarea>


                                </td>

                            </tr>	

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>

                        </table>

                        <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">


                            <tr>


                                <th colspan="2" style="background:none; border:none;"><img src="<?php echo ARFURL; ?>/images/icontact.png" align="absmiddle" /></th>

                            </tr>

                            <tr>
                                <?php $autores_type['icontact_type'] = ( $autores_type['icontact_type'] == 1 ) ? $autores_type['icontact_type'] : 0; ?>

                                <th style="width:18%; background:none; border:none;"></th>
                                <th id="th_icontact" style="background:none; border:none; padding-left:5px; <?php
                                if ($autores_type['icontact_type'] == 2)
                                    echo 'padding-left: 5px;';
                                else
                                    echo 'padding-left: 5px;';
                                ?>">
                                    <input type="radio" class="rdostandard" id="icontact_1" <?php if ($autores_type['icontact_type'] == 1) echo 'checked="checked"'; ?> name="icontact_type" value="1" style="margin-top:3px;" onclick="show_api('icontact');" /><label class="lblsubtitle" for="icontact_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using API', 'ARForms'); ?></div></label> &nbsp; &nbsp; 
                                    <input type="radio" class="rdostandard" id="icontact_2" <?php if ($autores_type['icontact_type'] == 0) echo 'checked="checked"'; ?>  name="icontact_type" value="0" style="margin-top:3px;" onclick="show_web_form('icontact');" /><label class="lblsubtitle" for="icontact_2"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>

                            </tr>

                            <tr id="icontact_api_tr1" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; padding-bottom:3px; text-align: left;"><label class="lblsubtitle"><?php _e('APP ID', 'ARForms'); ?></label></td>

                                <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="icontact_api" class="txtstandardnew" id="icontact_api" size="80" onkeyup="show_verify_btn('icontact');" value="<?php echo $icontact_data->responder_api_key; ?>" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?>  <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>/>
                                    <div class="arferrmessage" id="icontact_api_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>


                            <tr id="icontact_api_tr2" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Username', 'ARForms'); ?></label></td>

                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="text" name="icontact_username" class="txtstandardnew" id="icontact_username" onkeyup="show_verify_btn('icontact');" size="80" value="<?php echo $icontact_data->responder_username; ?>" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?> />
                                    <div class="arferrmessage" id="icontact_username_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></div></td>


                            </tr>

                            <tr id="icontact_api_tr3" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Password', 'ARForms'); ?></label></td>

                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="password" name="icontact_password" class="txtstandardnew" id="icontact_password" onkeyup="show_verify_btn('icontact');" size="80" value="<?php echo $icontact_data->responder_password; ?>" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>/> &nbsp; &nbsp; 
                                    <span id="icontact_link" <?php if ($icontact_data->is_verify == 1) { ?> style="display:none"<?php } ?>><a href="javascript:void(0);" onclick="verify_autores('icontact', '0');" class="arlinks"><?php _e('Verify', 'ARForms'); ?></a></span>
                                    <span id="icontact_loader" style="display:none;"><img src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>           			<span id="icontact_verify" class="frm_verify_li" style="display:none;"><?php _e('Verified', 'ARForms'); ?></span>       						
                                    <span id="icontact_error" class="frm_not_verify_li" style="display:none;"><?php _e('Not Verified', 'ARForms'); ?></span>
                                    <input type="hidden" name="icontact_status" id="icontact_status" value="<?php echo $icontact_data->is_verify; ?>" />
                                    <div class="arferrmessage" id="icontact_password_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>


                            </tr>

                            <tr id="icontact_api_tr4" <?php if ($autores_type['icontact_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('List Name', 'ARForms'); ?></label></td>

                                <td style=" padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_icontact"><div class="sltstandard" style="float:none;display:inline;"><select name="icontact_listname" id="icontact_listname" <?php if ($icontact_data->is_verify == 0 || $icontact_data->responder_api_key == '' || $icontact_data->responder_username == '' || $icontact_data->responder_password == '') echo 'disabled="disabled"'; ?> style="width:150px;" data-width='150px'>
                                                <?php
                                                $lists = maybe_unserialize($icontact_data->responder_list_id);
                                                if ($lists && count($lists) > 0) {

                                                    foreach ($lists as $list) {
                                                        if ($icontact_data->responder_list == $list->listId)
                                                            echo '<option selected="selected" value="' . $list->listId . '">' . $list->name . '</option>';
                                                        else
                                                            echo '<option value="' . $list->listId . '">' . $list->name . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select></div></span>
                                    <span id="icontact_loader2" style="display:none;margin-left: 10px; padding-top: 6px; position: absolute;"><img align="absmiddle"  src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>


                                    <div id="icontact_del_link" style="padding-left:5px; margin-top:10px;<?php if ($icontact_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">

                                        <a href="javascript:void(0);" onclick="action_autores('refresh', 'icontact');"><?php _e('Refresh List', 'ARForms'); ?></a>
                                        &nbsp;	&nbsp;	&nbsp;	&nbsp;
                                        <a href="javascript:void(0);" onclick="action_autores('delete', 'icontact');"><?php _e('Delete Configuration', 'ARForms'); ?></a>
                                    </div>


                                </td>


                            </tr>

                            <tr id="icontact_web_form_tr" <?php if ($autores_type['icontact_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from Icontact', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="icontact_web_form" id="icontact_web_form" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Icontact settings');" <?php } ?>><?php echo stripslashes($icontact_data->responder_web_form); ?></textarea>


                                </td>

                            </tr>

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>

                        </table>


                        <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                            <tr>

                                <th colspan="2" style="background:none; border:none;"><img src="<?php echo ARFURL; ?>/images/constant-contact.png" align="absmiddle" /></th>


                            </tr>

                            <tr>
                                <?php $autores_type['constant_type'] = ( $autores_type['constant_type'] == 1 ) ? $autores_type['constant_type'] : 0; ?>
                                <th style="width:18%; background:none; border:none;">&nbsp;</th>
                                <th id="th_constant" style="background:none; border:none; padding-left:5px; <?php
                                if ($autores_type['constant_type'] == 2)
                                    echo 'padding-left: 5px;';
                                else
                                    echo 'padding-left: 5px;';
                                ?>">
                                    <input type="radio" class="rdostandard" id="constant_contact_1" <?php if ($autores_type['constant_type'] == 1) echo 'checked="checked"'; ?> name="constant_type" value="1" style="margin-top:3px;" onclick="show_api('constant');" /><label class="lblsubtitle" for="constant_contact_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using API', 'ARForms'); ?></div></label> &nbsp; &nbsp; 
                                    <input type="radio" class="rdostandard" id="constant_contact_2" <?php if ($autores_type['constant_type'] == 0) echo 'checked="checked"'; ?>  name="constant_type" value="0" style="margin-top:3px;" onclick="show_web_form('constant');" /><label class="lblsubtitle" for="constant_contact_2"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>

                            </tr>

                            <tr id="constant_api_tr1" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('API Key', 'ARForms'); ?></label></td>

                                <td style="padding-bottom:3px; padding-left:5px;"><input type="text" name="constant_api" class="txtstandardnew" onkeyup="show_verify_btn('constant');" id="constant_api" size="80" value="<?php echo $constant_data->responder_api_key; ?>" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>/>
                                    <div class="arferrmessage" id="constant_api_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>

                            <tr id="constant_api_tr2" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Access Token', 'ARForms'); ?></label></td>

                                <td style="padding-top:3px; padding-bottom:3px; padding-left:5px;"><input type="text" name="constant_access_token" onkeyup="show_verify_btn('constant');" class="txtstandardnew" id="constant_access_token" size="80" value="<?php echo $constant_data->responder_list_id; ?>" <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>/> &nbsp; &nbsp; 

                                    <span id="constant_link" <?php if ($constant_data->is_verify == 1) { ?> style="display:none;"<?php } ?> ><a href="javascript:void(0);" onclick="verify_autores('constant', '0');" class="arlinks"><?php _e('Verify', 'ARForms'); ?></a></span>
                                    <span id="constant_loader" style="display:none;"><img src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>
                                    <span id="constant_verify" class="frm_verify_li" style="display:none;"><?php _e('Verified', 'ARForms'); ?></span> 
                                    <span id="constant_error" class="frm_not_verify_li" style="display:none;"><?php _e('Not Verified', 'ARForms'); ?></span>
                                    <input type="hidden" name="constant_status" id="constant_status" value="<?php echo $constant_data->is_verify; ?>" />
                                    <div class="arferrmessage" id="constant_access_token_error" style="display:none;"><?php _e('This field cannot be blank.', 'ARForms'); ?></div></td>

                            </tr>

                            <tr id="constant_api_tr3" <?php if ($autores_type['constant_type'] != 1) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-top:3px; padding-bottom:3px; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('List Name', 'ARForms'); ?></label></td>

                                <td style="padding-top:3px; padding-bottom:3px; padding-left:5px; overflow: visible;"><span id="select_constant"><div class="sltstandard" style="float:none; display:inline;"><select name="constant_listname" id="constant_listname" <?php if ($constant_data->is_verify == 0 || $constant_data->responder_api_key == '' || $constant_data->responder_list_id == '') echo 'disabled="disabled"'; ?> style="width:150px;" data-width='150px'>
                                                <?php
                                                $lists_new = maybe_unserialize($constant_data->list_data);

                                                if ($lists_new && count($lists_new) > 0) {

                                                    foreach ($lists_new as $list) {
                                                        if ($constant_data->responder_list == $list['id'])
                                                            echo '<option selected="selected" value="' . $list['id'] . '">' . $list['name'] . '</option>';
                                                        else
                                                            echo '<option value="' . $list['id'] . '">' . $list['name'] . '</option>';
                                                    }
                                                }
                                                ?>   
                                            </select></div></span>
                                    <span id="constant_loader2" style="display:none;margin-left: 10px; padding-top: 6px; position: absolute;"><img align="absmiddle"  src="<?php echo ARFURL . '/images/loading_299_1.gif'; ?>" height="15" /></span>


                                    <div id="constant_del_link" style="padding-left:5px; margin-top:10px;<?php if ($constant_data->is_verify == 0) { ?>display:none;<?php } ?>" class="arlinks">

                                        <a href="javascript:void(0);" onclick="action_autores('refresh', 'constant');"><?php _e('Refresh List', 'ARForms'); ?></a>
                                        &nbsp;	&nbsp;	&nbsp;	&nbsp;
                                        <a href="javascript:void(0);" onclick="action_autores('delete', 'constant');"><?php _e('Delete Configuration', 'ARForms'); ?></a>
                                    </div>


                                </td>

                            </tr>

                            <tr id="constant_web_form_tr" <?php if ($autores_type['constant_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from Constant Contact', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="constant_web_form" id="constant_web_form" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set Constant Contact settings');" <?php } ?>><?php echo stripslashes($constant_data->responder_web_form); ?></textarea>


                                </td>

                            </tr>

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>

                        </table>

                        <table class="wp-list-table widefat post " style="margin:20px 0 0 10px; border:none;">

                            <tr>

                                <th style="background:none; border:none;" colspan="2"><img src="<?php echo ARFURL; ?>/images/gvo.png" align="absmiddle" /></label></th>

                            </tr>

                            <tr>
                                <?php $autores_type['gvo_type'] = ( $autores_type['gvo_type'] == 1 ) ? $autores_type['gvo_type'] : 0; ?>
                                <th style="width:18%; background:none; border:none;"></th>
                                <th id="th_gvo" style="padding-left:5px;background:none; border:none;"><input type="radio" class="rdostandard" id="gvo_1" <?php if ($autores_type['gvo_type'] == 0) echo 'checked="checked"'; ?>  name="gvo_type" value="0" style="margin-top:3px;" onclick="show_web_form('gvo');" /><label class="lblsubtitle" for="gvo_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>

                            </tr>

                            <tr id="gvo_web_form_tr" <?php if ($autores_type['gvo_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:18%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from GVO Campaign', 'ARForms'); ?></label></td>

                                <td style="padding-left:5px;">

                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="gvo_api" id="gvo_api" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set GVO settings');" <?php } ?>><?php echo stripslashes($gvo_data->responder_api_key); ?></textarea>

                                </td>

                            </tr>

                            <tr>
                                <td colspan="2" style="padding-left:5px;"><div class="dotted_line" style="width:96%"></div></td>
                            </tr>

                        </table>


                        <table class="wp-list-table widefat post " style="margin:20px 0 20px 10px; border:none;">

                            <tr>

                                <th style="background:none; border:none;" colspan="2"><img src="<?php echo ARFURL; ?>/images/ebizac.png" align="absmiddle" /></th>

                            </tr>

                            <tr>
                                <?php $autores_type['ebizac_type'] = ( $autores_type['ebizac_type'] == 1 ) ? $autores_type['ebizac_type'] : 0; ?>
                                <th style="width:18%; background:none; border:none;"></th>
                                <th id="th_ebizac" style="padding-left:5px;background:none; border:none;"><input type="radio" class="rdostandard" id="ebizac_1" <?php if ($autores_type['ebizac_type'] == 0) echo 'checked="checked"'; ?>  name="ebizac_type" value="0" style="margin-top:3px;" onclick="show_web_form('ebizac');" /><label class="lblsubtitle" for="ebizac_1"><span class="lblsubtitle_span"></span><div class="api_lable"><?php _e('Using Web-form', 'ARForms'); ?></div></label> &nbsp; &nbsp;
                                </th>	

                            </tr>

                            <tr id="ebizac_web_form_tr" <?php if ($autores_type['ebizac_type'] != 0) echo 'style="display:none;"'; ?>>

                                <td class="tdclass" style="width:17%; padding-right:20px; text-align: left;"><label class="lblsubtitle"><?php _e('Webform code from eBizac', 'ARForms'); ?></label></td>

                                <td style="verticle-align:middle; padding-left:5px;">
                                    <textarea <?php
                                    if ($setact != 1) {
                                        echo "readonly=readonly";
                                    }
                                    ?> name="ebizac_api" id="ebizac_api" class="txtmultinew" <?php if ($setact != 1) {?> onclick="alert('Please activate license to set eBizac settings');" <?php } ?>><?php echo stripslashes($ebizac_data->responder_api_key); ?></textarea>


                            </tr>


                        </table>

                        <?php do_action('arf_autoresponder_global_setting_block', $autores_type, $setact); ?>


                    </div>



                     <div id="verification_settings" style=" display:none; background-color:#FFFFFF; padding-top:10px; border-radius:5px 5px 5px 5px; padding-left: 20px;  padding-bottom:1px;">
                         	<?php
							
							?>
                     </div>

                    <?php
                    foreach ($sections as $sec_name => $section) {


                        if (isset($section['class'])) {


                            call_user_func(array($section['class'], $section['function']));
                        } else {


                            call_user_func((isset($section['function']) ? $section['function'] : $section));
                        }
                    }


                    $user_roles = $current_user->roles;


                    $user_role = array_shift($user_roles);
                    ?>

                    <br />
                    <p class="submit">


                        <button class="greensavebtn"  style="width:150px; border:0px; color:#FFFFFF; height:40px; border-radius:3px;margin-left:21%;" type="submit" ><img align="absmiddle" src="<?php echo ARFIMAGESURL ?>/save_icon.png">&nbsp;&nbsp;<?php _e('Save Changes', 'ARForms') ?></button>&nbsp;&nbsp;&nbsp;

                    </p>
                    <br />





                </form>


            </div>



        </div>


    </div>

    <div class="documentation_link" align="right"><a href="<?php echo ARFURL; ?>/documentation/index.html" style="margin-right:10px;" target="_blank"><?php _e('Documentation', 'ARForms'); ?></a>|<a href="https://helpdesk.arpluginshop.com/" style="margin-left:10px;" target="_blank"><?php _e('Support', 'ARForms'); ?></a>&nbsp;&nbsp;<img src="<?php echo ARFURL; ?>/images/dot.png" height="4" width="4" onclick="javascript:OpenInNewTab('<?php echo ARFURL; ?>/documentation/assets/sysinfo.php');" /></div>

</div>


<?php ?>
<script type="text/javascript" language="javascript">

    function show_form_settimgs(id1, id2)
    {
        document.getElementById(id1).style.display = 'block';
        document.getElementById(id2).style.display = 'none';
       
        document.getElementById('arfcurrenttab').value = id1;

        jQuery('.' + id1).addClass('btn_sld').removeClass('tab-unselected');
        jQuery('#' + id1 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id1 + '.png');
        jQuery('.' + id2).removeClass('btn_sld').addClass('tab-unselected');
        jQuery('#' + id2 + '_img').attr('src', '<?php echo ARFIMAGESURL; ?>/' + id2 + '_hover.png');
        
    }

</script>