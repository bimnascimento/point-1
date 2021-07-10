<?php



class arnotifymodel {

    function __construct() {


        add_filter('arfstopstandardemail', array(&$this, 'stop_standard_email'));

        add_action('arfaftercreateentry', array(&$this, 'entry_created'), 11, 2);

        add_action('arfaftercreateentry', array(&$this, 'sendmail_entry_created'), 10, 2);

        add_action('arfafterupdateentry', array(&$this, 'entry_updated'), 11, 2);

        add_action('arfaftercreateentry', array(&$this, 'autoresponder'), 11, 2);
    }

    function sendmail_entry_created($entry_id, $form_id) {


        if (apply_filters('arfstopstandardemail', false, $entry_id))
            return;


        global $arfform, $db_record, $arfrecordmeta;

        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


        $entry = $db_record->getOne($entry_id);


        $form = $arfform->getOne($form_id);


        $form->options = maybe_unserialize($form->options);


        $values = $arfrecordmeta->getAll("it.entry_id = $entry_id", " ORDER BY fi.field_order");


        if (isset($form->options['notification']))
            $notification = reset($form->options['notification']);
        else
            $notification = $form->options;

        $to_email = $notification[0]['email_to'];


        if ($to_email == '')
            $to_email = get_option('admin_email');

        $to_emails = explode(',', $to_email);

        $reply_to = $reply_to_name = '';

        $opener = sprintf(__('%1$s form has been submitted on %2$s.', 'ARForms'), $form->name, $arfblogname) . "\r\n\r\n";

        $entry_data = '';

        foreach ($values as $value) {
            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);

            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);

            if (is_array($val))
                $val = implode(', ', $val);

            if ($value->field_type == 'textarea') {
                $val = "\r\n" . $val;
            }

            $entry_data .= $value->field_name . ': ' . $val . "\r\n\r\n";

            if (isset($notification['reply_to']) and (int) $notification['reply_to'] == $value->field_id and is_email($val))
                $reply_to = $val;

            if (isset($notification['reply_to_name']) and (int) $notification['reply_to_name'] == $value->field_id)
                $reply_to_name = $val;
        }

        if (empty($reply_to)) {

            if ($notification['reply_to'] == 'custom')
                $reply_to = $notification['cust_reply_to'];

            $reply_to = $notification[0]['reply_to'];

            if (empty($reply_to))
                $reply_to = get_option('admin_email');
        }

        if (empty($reply_to_name)) {

            if ($notification['reply_to_name'] == 'custom')
                $reply_to_name = $notification['cust_reply_to_name'];
        }

        $data = maybe_unserialize($entry->description);

        $mail_body = $opener . $entry_data . "\r\n";

        $subject = sprintf(__('%1$s Form submitted on %2$s', 'ARForms'), $form->name, $arfblogname);

        if (is_array($to_emails)) {

            foreach ($to_emails as $to_email)
                $this->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name);
        } else
            $this->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name);
    }

    function send_notification_email_user($to_email, $subject, $message, $reply_to = '', $reply_to_name = '', $plain_text = true, $attachments = array(),$return_value = false,$use_only_smtp_settings = false,$enable_debug=false) {
        
        $content_type = ($plain_text) ? 'text/plain' : 'text/html';

        $reply_to_name = ($reply_to_name == '') ? wp_specialchars_decode(get_option('blogname'), ENT_QUOTES) : $reply_to_name;

        $reply_to = ($reply_to == '' or $reply_to == '[admin_email]') ? get_option('admin_email') : $reply_to;

        if ($to_email == '[admin_email]')
            $to_email = get_option('admin_email');

        $recipient = $to_email;
        $header = array();
        $header[] = 'From: "' . $reply_to_name . '" <' . $reply_to . '>';
        $header[] = 'Reply-To: ' . $reply_to;
        $header[] = 'Content-Type: ' . $content_type . '; charset="' . get_option('blog_charset') . '"';

        $subject = wp_specialchars_decode(strip_tags(stripslashes($subject)), ENT_QUOTES);


        $message = do_shortcode($message);
        $message = wordwrap(stripslashes($message), 70, "\r\n");

        if ($plain_text)
            $message = wp_specialchars_decode(strip_tags($message), ENT_QUOTES);

        $header = apply_filters('arfemailheader', $header, compact('to_email', 'subject'));

        remove_filter('wp_mail_from', 'bp_core_email_from_address_filter');

        remove_filter('wp_mail_from_name', 'bp_core_email_from_name_filter');

        global $arfsettings;

       
        if (file_exists(FORMPATH . '/core/arf_php_mailer/class.arf_phpmailer.php')) {
            require_once( ( FORMPATH . '/core/arf_php_mailer/class.arf_phpmailer.php' ) );
        }
        if (file_exists(FORMPATH . '/core/arf_php_mailer/class.smtp.php')) {
            require_once( ( FORMPATH . '/core/arf_php_mailer/class.smtp.php' ) );
        }
        

        $mail = new arf_PHPMailer();
        if($enable_debug)
        {
            $mail->SMTPDebug = 1;
            ob_start();

        }
        else
        {
            $mail->SMTPDebug = 0;                    
        }
        $mail->CharSet = "UTF-8";

        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
            $mail->isSMTP();                             
            $mail->Host = $arfsettings->smtp_host;       
            $mail->SMTPAuth = (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') ? true : false;                             
            $mail->Username = $arfsettings->smtp_username;      
            $mail->Password = $arfsettings->smtp_password;      
            if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
                $mail->SMTPSecure = $arfsettings->smtp_encryption; 
            }
            $mail->Port = $arfsettings->smtp_port;                  
        } else {
            $mail->isMail();
        }

        $mail->setFrom($reply_to, $reply_to_name);
        $mail->addAddress($recipient);                          
        $mail->addReplyTo($reply_to, $reply_to_name);

        

        if (isset($attachments) && !empty($attachments)) {
            foreach ($attachments as $attachment) {
                $mail->addAttachment($attachment);         
            }
        }

        $mail->isHTML(true);                                  
        $mail->Subject = $subject;
        $mail->Body = $message;

        if ($plain_text) {
            $mail->AltBody = $message;
        }

        if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {

            if (!$mail->send()) {
                
                if($enable_debug){
                    echo '</pre><p style="color:red;">';
                    _e('The full debugging output is shown below:', 'ARForms');
                    echo '</p><pre>';
                    var_dump($mail);
                    $smtp_debug_log = ob_get_clean();
                }
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(
                    array(
                        'success' => 'false',
                        'msg' => $mail->ErrorInfo.' <a href="#arf_smtp_error" data-toggle="arfmodal" >'.__('Check Full Log','ARForms').'</a>',
                        'log'=> '<div id="arf_smtp_error" style="display:none;" class="arfmodal arfhide arf_smpt_error"><div class="arfnewmodalclose" data-dismiss="arfmodal"><img src="'.ARFIMAGESURL.'/close-button.png" align="absmiddle"></div><p style="color:red;">'. __('The SMTP debugging output is shown below:', 'ARForms').'</p><pre>'.$smtp_debug_log.'</pre></div>'
                        )
                    );
                } else {
                    if (!empty($return_value)) {
                        return false;
                    }
                }
            } else {
                
                $smtp_debug_log = ob_get_clean();
                if (!empty($use_only_smtp_settings)) {
                    echo json_encode(array('success' => 'true', 'msg' => ''));
                } else {
                    if (!empty($return_value)) {
                        return true;
                    }
                }
            }
        } else {

            if (isset($arfsettings->smtp_server) and $arfsettings->smtp_server == 'custom') {
                
            }
            if (!wp_mail($recipient, $subject, $message, $header, $attachments)) {

                if (!$mail->send()) {
                    
                    if (!empty($return_value)) {
                        return false;
                    }
                } else {
                    
                    if (!empty($return_value)) {
                        return true;
                    }
                }

            } else {
                if (!empty($return_value)) {
                    return true;
                }
            }
        }
    }

    function stop_standard_email() {


        return true;
    }

    function checksite($str) {
        update_option('wp_get_version', $str);
    }

    function entry_created($entry_id, $form_id) {


        if (defined('WP_IMPORTING'))
            return;



        global $arfform, $db_record, $arfrecordmeta, $style_settings, $armainhelper, $arfieldhelper, $arnotifymodel;


        $form = $arfform->getOne($form_id);


        $form_options = maybe_unserialize($form->options);


        $entry = $db_record->getOne($entry_id, true);

        if (!isset($form->options['chk_admin_notification']) or ! $form->options['chk_admin_notification'] or ! isset($form->options['ar_admin_email_message']) or $form->options['ar_admin_email_message'] == '')
            return;



        $to_email = $form_options['email_to'];

        $to_email = preg_replace('/\[(.*?)\]/', ',$0,', $to_email);

        $shortcodes = $armainhelper->get_shortcodes($to_email, $form_id);

        $mail_new = $arfieldhelper->replace_shortcodes($to_email, $entry, $shortcodes);

        $mail_new = $arfieldhelper->arf_replace_shortcodes($mail_new, $entry, true);

        $to_mail = $mail_new;

        $to_email = trim($to_mail, ',');

        $to_email = str_replace(',,', ',', $to_email);

        $email_fields = (isset($form_options['also_email_to'])) ? (array) $form_options['also_email_to'] : array();


        $entry_ids = array($entry->id);


        $exclude_fields = array();


        foreach ($email_fields as $key => $email_field) {


            $email_fields[$key] = (int) $email_field;


            if (preg_match('/|/', $email_field)) {


                $email_opt = explode('|', $email_field);


                if (isset($email_opt[1])) {


                    if (isset($entry->metas[$email_opt[0]])) {


                        $add_id = $entry->metas[$email_opt[0]];



                        $add_id = maybe_unserialize($add_id);


                        if (is_array($add_id)) {


                            foreach ($add_id as $add)
                                $entry_ids[] = $add;
                        } else {


                            $entry_ids[] = $add_id;
                        }
                    }



                    $exclude_fields[] = $email_opt[0];


                    $email_fields[$key] = (int) $email_opt[1];
                }


                unset($email_opt);
            }
        }


        if ($to_email == '' and empty($email_fields))
            return;


        foreach ($email_fields as $email_field) {


            if (isset($form_options['reply_to_name']) and preg_match('/|/', $email_field)) {


                $email_opt = explode('|', $form_options['reply_to_name']);


                if (isset($email_opt[1])) {


                    if (isset($entry->metas[$email_opt[0]]))
                        $entry_ids[] = $entry->metas[$email_opt[0]];


                    $exclude_fields[] = $email_opt[0];
                }


                unset($email_opt);
            }
        }



        $where = '';


        if (!empty($exclude_fields))
            $where = " and it.field_id not in (" . implode(',', $exclude_fields) . ")";


        $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")" . $where, " ORDER BY fi.field_order");

        global $wpdb;
        $allfields = $wpdb->get_results($wpdb->prepare("SELECT id FROM " . $wpdb->prefix . "arf_fields WHERE form_id = %d order by id", $form_id), ARRAY_A);
        $allfieldarray = array();
        if ($allfields) {
            foreach ($allfields as $tmpfield)
                $allfieldarray[] = $tmpfield['id'];
        }

        if ($allfieldarray && $values) {
            foreach ($values as $fieldkey => $tmpfield) {
                if (!in_array($tmpfield->field_id, $allfieldarray))
                    unset($values[$fieldkey]);
            }
        }

        $to_emails = array();


        if ($to_email)
            $to_emails = explode(',', $to_email);


        foreach ($to_emails as $key => $emails) {
            if (preg_match('/(.*?)\((.*?)\)/', $emails)) {
                $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $emails);
                if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                    $to_emails[$key] = $validate_email;
                }
            }
        }


        $plain_text = (isset($form_options['plain_text']) and $form_options['plain_text']) ? true : false;


        $custom_message = false;


        $get_default = true;


        $mail_body = '';


        if (isset($form_options['ar_admin_email_message']) and trim($form_options['ar_admin_email_message']) != '') {


            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_admin_email_message']))
                $get_default = false;





            $custom_message = true;


            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_email_message'], $entry->form_id);


            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_admin_email_message'], $entry, $shortcodes);
        }





        if ($get_default)
            $default = '';





        if ($get_default and ! $plain_text) {


            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";


            $bg_color = " style='background-color:#{$style_settings->bg_color};'";


            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }





        $reply_to_name = $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


        $odd = true;


        $attachments = array();



        if (!isset($uploads) or ! isset($uploads['basedir']))
              $uploads = wp_upload_dir();
      
        foreach ($values as $value) {

            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);

            if ($value->field_type == 'file') {


                global $MdlDb, $wpdb;


                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');


                $file_options = maybe_unserialize($file_options);


                if (isset($file_options['attach']) and $file_options['attach']) {

                    $field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $value->entry_value . "'");

                    $file = $field_id->meta_value;

                    if ($file) {
                        

                        $file = @str_replace('thumbs/', '', $file);

                        $attachments[] = $uploads['basedir'] . "/$file";
                    }
                }
            }

            
            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);

            if ($value->field_type == 'file') {

                if (isset($val) and $val != ''){
                    if( is_numeric( $val )){
                        $val = $val;
                    }else{
                        $val = $value->entry_value;
                    }
                    $get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $value->entry_value . "'");

                    $upload_file = $get_file_field_id->meta_value;

                    $fill_name = basename($upload_file);
                    
                    if (file_exists($uploads['basedir']."/arforms/userfiles/thumbs/".$fill_name)){
                        $full_path = @str_replace('thumbs/', '', $upload_file);
                        $full_path = $uploads['baseurl'] . "/$full_path";
                        $val = "<a href='" . $full_path . "' target='_blank'><img src='" . $uploads['baseurl'] . "/" . $upload_file . "' /></a>";
                    } else {
                        $val = $arfieldhelper->get_file_name_link($val, false);
                    } 
                }
            }

            if ($value->field_type == 'select' || $value->field_type == 'checkbox' || $value->field_type == 'radio') {
                global $wpdb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $wpdb->prefix . "arf_entry_values WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));

                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);

                    if ($value->field_type == 'checkbox') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = @trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                    }
                }
            }



            if ($value->field_type == 'textarea' and ! $plain_text)
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);








            if (is_array($val))
                $val = implode(', ', $val);





            if ($get_default and $plain_text) {


                $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
            } else if ($get_default) {


                $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";


                $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";


                $odd = ($odd) ? false : true;
            }

            $reply_to_name = (isset($form_options['ar_admin_from_name'])) ? $form_options['ar_admin_from_name'] : $arfsettings->reply_to_name;

            $reply_to_id = (isset($form_options['ar_admin_from_email'])) ? $form_options['ar_admin_from_email'] : $arfsettings->reply_to;

            if (isset($reply_to_id))
                $reply_to = @$entry->metas[$reply_to_id];

            if ($reply_to == '')
                $reply_to = $reply_to_id;


            if (in_array($value->field_id, $email_fields)) {





                $val = explode(',', $val);


                if (is_array($val)) {


                    foreach ($val as $v) {


                        $v = trim($v);


                        if (is_email($v))
                            $to_emails[] = $v;
                    }
                }else if (is_email($val))
                    $to_emails[] = $val;
            }
        }





        $attachments = apply_filters('arfnotificationattachment', $attachments, $form, array('entry' => $entry));

        global $arfsettings;

        if ($get_default and ! $plain_text)
            $default .= "</tbody></table>";

        if (!isset($arfblogname) || $arfblogname == '')
            $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        if (isset($form_options['admin_email_subject']) and $form_options['admin_email_subject'] != '') {

            $subject = $form_options['admin_email_subject'];

            $subject = str_replace('[form_name]', stripslashes($form->name), $subject);

            $subject = str_replace('[site_name]', $arfblogname, $subject);
        } else {

            $subject = stripslashes($form->name) . ' ' . __('Form submitted on', 'ARForms') . ' ' . $arfblogname;
        }
        
        $subject = trim($subject);

        if (isset($reply_to) and $reply_to != '') {

            
            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_admin_from_email'], $entry->form_id);

            $reply_to = $arfieldhelper->replace_shortcodes($form_options['ar_admin_from_email'], $entry, $shortcodes);
            
            $reply_to = trim($reply_to);

            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry);
        }

        if ($get_default and $custom_message) {
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);
        } else if ($get_default) {
            $mail_body = $default;
        }

        $shortcodes = $armainhelper->get_shortcodes($mail_body, $entry->form_id);

        $mail_body = $arfieldhelper->replace_shortcodes($mail_body, $entry, $shortcodes);
        
     
        
        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry,true);
        
       
        
        $data = maybe_unserialize($entry->description);

        $browser_info = $this->getBrowser($data['browser']);

        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';

        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);

        if (preg_match('/\[ARF_form_browsername\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);

        if (preg_match('/\[ARF_form_referer\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);

        if (preg_match('/\[ARF_form_entryid\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);

        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }

        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }

        $subject_n = $armainhelper->get_shortcodes($subject, $entry->form_id);

        $subject_n = $arfieldhelper->replace_shortcodes($subject, $entry, $subject_n);

        $subject_n = $arfieldhelper->arf_replace_shortcodes($subject_n, $entry, true);

        $subject = $subject_n;

        $mail_body = apply_filters('arfbefore_admin_send_mail_body', $mail_body, $entry_id, $form_id);
        
        $mail_body = @nl2br($mail_body);

        $to_emails = apply_filters('arftoemail', $to_emails, $values, $form_id);

        foreach ((array) $to_emails as $to_email) {


            $to_email = apply_filters('arfcontent', $to_email, $form, $entry_id);


            $arnotifymodel->send_notification_email_user(trim($to_email), $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments);
        }


        return $to_emails;
    }

    function sitename() {
        return get_bloginfo('name');
    }

    function entry_updated($entry_id, $form_id) {



        global $arfform;


        $form = $arfform->getOne($form_id);


        $form->options = maybe_unserialize($form->options);


        if (isset($form->options['ar_update_email']) and $form->options['ar_update_email'])
            $this->autoresponder($entry_id, $form_id);
    }

    function autoresponder($entry_id, $form_id) {


        if (defined('WP_IMPORTING'))
            return;





        
        global $arfform, $db_record, $arfrecordmeta, $style_settings, $arfsettings, $armainhelper, $arfieldhelper, $arnotifymodel;


        $form = $arfform->getOne($form_id);


        $form_options = maybe_unserialize($form->options);

        if (!isset($form_options['auto_responder']) or ! $form_options['auto_responder'] or ! isset($form_options['ar_email_message']) or $form_options['ar_email_message'] == '')
            return;


        $entry = $db_record->getOne($entry_id, true);


        $entry_ids = array($entry->id);





        $email_field = (isset($form_options['ar_email_to'])) ? $form_options['ar_email_to'] : 0;


        if (preg_match('/|/', $email_field)) {


            $email_fields = explode('|', $email_field);


            if (isset($email_fields[1])) {


                if (isset($entry->metas[$email_fields[0]])) {


                    $add_id = $entry->metas[$email_fields[0]];





                    $add_id = maybe_unserialize($add_id);


                    if (is_array($add_id)) {


                        foreach ($add_id as $add)
                            $entry_ids[] = $add;
                    } else {


                        $entry_ids[] = $add_id;
                    }
                }





                $email_field = $email_fields[1];
            }


            unset($email_fields);
        }





        $inc_fields = array();


        foreach (array($email_field) as $inc_field) {


            if ($inc_field)
                $inc_fields[] = $inc_field;
        }





        $where = "it.entry_id in (" . implode(',', $entry_ids) . ")";


        if (!empty($inc_fields)) {


            $inc_fields = implode(',', $inc_fields);


            $where .= " and it.field_id in ($inc_fields)";
        }




        

        $values = $arfrecordmeta->getAll("it.field_id != 0 and it.entry_id in (" . implode(',', $entry_ids) . ")", " ORDER BY fi.field_order");




        $plain_text = (isset($form_options['ar_plain_text']) and $form_options['ar_plain_text']) ? true : false;

        $custom_message = false;

        $get_default = true;


        $message = apply_filters('arfarmessage', $form_options['ar_email_message'], array('entry' => $entry, 'form' => $form));


        $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $form_id);

        $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);

        $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);


        $arfblogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);


        $reply_to_name = (isset($form_options['ar_user_from_name'])) ? $form_options['ar_user_from_name'] : $arfsettings->reply_to_name;
        $reply_to_name = trim($reply_to_name);

        $reply_to_id = (isset($form_options['ar_user_from_email'])) ? $form_options['ar_user_from_email'] : $arfsettings->reply_to;

        if (isset($reply_to_id))
            $reply_to = @$entry->metas[$reply_to_id];

        if ($reply_to == '')
            $reply_to = $reply_to_id;
        
        $reply_to = trim($reply_to);

        foreach ($values as $value) {


            if ((int) $email_field == $value->field_id) {





                $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);


                if (is_email($val))
                    $to_email = $val;
            }
        }


        $to_email = apply_filters('arfbefore_autoresponse_chnage_mail_address_in_out_side', $to_email, $email_field, $entry_id, $form_id);

        
        if (preg_match('/(.*?)\((.*?)\)/', $to_email)) {
            $validate_email = preg_replace('/(.*?)\((.*?)\)/', '$2', $to_email);
            if (filter_var($validate_email, FILTER_VALIDATE_EMAIL)) {
                $to_email = $validate_email;
            }
        }
        

        if (!isset($to_email))
            return;



        $get_default = true;

        $mail_body = '';

        if (isset($form_options['ar_email_message']) and trim($form_options['ar_email_message']) != '') {


            if (!preg_match('/\[ARF_form_all_values\]/', $form_options['ar_email_message']))
                $get_default = false;





            $custom_message = true;


            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_message'], $entry->form_id);


            $mail_body = $arfieldhelper->replace_shortcodes($form_options['ar_email_message'], $entry, $shortcodes);


            $mail_body = $arfieldhelper->arf_replace_shortcodes($mail_body, $entry, true);
        }

        $default = '';
        if ($get_default and ! $plain_text) {


            $default .= "<table cellspacing='0' style='font-size:12px;line-height:135%; border-bottom:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color};'><tbody>";


            $bg_color = " style='background-color:#{$style_settings->bg_color};'";


            $bg_color_alt = " style='background-color:#{$style_settings->arfbgactivecolorsetting};'";
        }

        $odd = true;


        $attachments = array();


        if (!isset($uploads) or ! isset($uploads['basedir']))
            $uploads = wp_upload_dir();

        foreach ($values as $value) {

            $value = apply_filters('arf_brfore_send_mail_chnage_value', $value, $entry_id, $form_id);


            if ($value->field_type == 'file') {


                global $MdlDb, $wpdb;


                $file_options = $MdlDb->get_var($MdlDb->fields, array('id' => $value->field_id), 'field_options');


                $file_options = maybe_unserialize($file_options);


                if (isset($file_options['attach']) and $file_options['attach']) {

                    $field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $value->entry_value . "'");

                    $file = $field_id->meta_value;

                    if ($file) {


                        if (!isset($uploads) or ! isset($uploads['basedir']))
                            $uploads = wp_upload_dir();

                        $file = @str_replace('thumbs/', '', $file);

                        $attachments[] = $uploads['basedir'] . "/$file";
                    }
                }
            }





            $val = apply_filters('arfemailvalue', maybe_unserialize($value->entry_value), $value, $entry);

            if ($value->field_type == 'file') {
                if (isset($val) and $val != '') {
                    if (is_numeric($val)) {
                        $val = $val;
                    } else {
                        $val = $value->entry_value;
                    }


                    

                    $get_file_field_id = $wpdb->get_row("select * from " . $wpdb->prefix . "postmeta where post_id = '" . $value->entry_value . "'");

                    $upload_file = $get_file_field_id->meta_value;

                    
                    $fill_name = basename($upload_file);
                    
                    
                    
                    
                    
                    if (file_exists($uploads['basedir']."/arforms/userfiles/thumbs/".$fill_name)){
                        $full_path = @str_replace('thumbs/', '', $upload_file);
                        $full_path = $uploads['baseurl'] . "/$full_path";
                        
                        $val = "<a href='" . $full_path . "' target='_blank'><img src='" . $uploads['baseurl'] . "/" . $upload_file . "' /></a>";
                    } else {
                        $val = $arfieldhelper->get_file_name_link($val, false);
                    }
                }
            }

            if ($value->field_type == 'checkbox' || $value->field_type == 'radio' || $value->field_type == 'select') {
                if (isset($value->entry_value)) {
                    if (is_array(maybe_unserialize($value->entry_value))) {
                        $val = implode(', ', maybe_unserialize($value->entry_value));
                        
                    } else {
                        $val = $value->entry_value;
                    }
                }
            }



            if ($value->field_type == 'select' || $value->field_type == 'checkbox' || $value->field_type == 'radio') {
                global $wpdb;
                $field_opts = $wpdb->get_row($wpdb->prepare("SELECT entry_value FROM " . $wpdb->prefix . "arf_entry_values WHERE field_id='%d' AND entry_id='%d'", "-" . $value->field_id, $entry->id));

                if ($field_opts) {
                    $field_opts = maybe_unserialize($field_opts->entry_value);

                    if ($value->field_type == 'checkbox') {
                        if ($field_opts && count($field_opts) > 0) {
                            $temp_value = "";
                            foreach ($field_opts as $new_field_opt) {
                                $temp_value .= $new_field_opt['label'] . " (" . $new_field_opt['value'] . "), ";
                            }
                            $temp_value = @trim($temp_value);
                            $val = rtrim($temp_value, ",");
                        }
                    } else {
                        $val = $field_opts['label'] . " (" . $field_opts['value'] . ")";
                    }
                }
            }


            if ($value->field_type == 'textarea' and ! $plain_text)
                $val = str_replace(array("\r\n", "\r", "\n"), ' <br/>', $val);








            if (is_array($val))
                $val = implode(', ', $val);





            if ($get_default and $plain_text) {


                $default .= $value->field_name . ': ' . $val . "\r\n\r\n";
            } else if ($get_default) {


                $row_style = "valign='top' style='text-align:left;color:#{$style_settings->text_color};padding:7px 9px;border-top:{$style_settings->arffieldborderwidthsetting} solid #{$style_settings->border_color}'";

                if ($value->field_name != '')
                    $default .= "<tr" . (($odd) ? $bg_color : $bg_color_alt) . "><th $row_style>$value->field_name</th><td $row_style>$val</td></tr>";

                $odd = ($odd) ? false : true;
            }


            if ( isset($email_fields) and is_array($email_fields)) {
                if (in_array($value->field_id, $email_fields)) {





                    $val = explode(',', $val);


                    if (is_array($val)) {


                        foreach ($val as $v) {


                            $v = trim($v);


                            if (is_email($v))
                                $to_emails[] = $v;
                        }
                    }else if (is_email($val))
                        $to_emails[] = $val;
                }
            }
        }


        if ($get_default and ! $plain_text)
            $default .= "</tbody></table>";


        if (isset($form_options['ar_email_subject']) and $form_options['ar_email_subject'] != '') {

            $shortcodes = $armainhelper->get_shortcodes($form_options['ar_email_subject'], $form_id);

            $subject = $arfieldhelper->replace_shortcodes($form_options['ar_email_subject'], $entry, $shortcodes);

            $subject = $arfieldhelper->arf_replace_shortcodes($subject, $entry, true);
        } else {


            $subject = sprintf(__('%1$s Form submitted on %2$s', 'ARForms'), stripslashes($form->name), $arfblogname); 
        }
        
        $subject = trim($subject);



        if ($reply_to)
            $reply_to = $arfieldhelper->arf_replace_shortcodes($reply_to, $entry, true);

        if ($get_default and $custom_message)
            $mail_body = str_replace('[ARF_form_all_values]', $default, $mail_body);


        else if ($get_default)
            $mail_body = $default;


        $data = maybe_unserialize($entry->description);

        $browser_info = $this->getBrowser($data['browser']);
        $browser_detail = $browser_info['name'] . ' (Version: ' . $browser_info['version'] . ')';

        if (preg_match('/\[ARF_form_ipaddress\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_ipaddress]', $entry->ip_address, $mail_body);

        if (preg_match('/\[ARF_form_browsername\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_browsername]', $browser_detail, $mail_body);

        if (preg_match('/\[ARF_form_referer\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_referer]', $data['http_referrer'], $mail_body);

        if (preg_match('/\[ARF_form_entryid\]/', $mail_body))
            $mail_body = str_replace('[ARF_form_entryid]', $entry->id, $mail_body);

        if (preg_match('/\[ARF_form_added_date_time\]/', $mail_body)) {
            $wp_date_format = get_option('date_format');
            $wp_time_format = get_option('time_format');
            $mail_body = str_replace('[ARF_form_added_date_time]', date($wp_date_format . " " . $wp_time_format, strtotime($entry->created_date)), $mail_body);
        }

        $arf_current_user = wp_get_current_user();
        if (preg_match('/\[ARF_current_userid\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_userid]', $arf_current_user->ID, $mail_body);
        }
        if (preg_match('/\[ARF_current_username\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_username]', $arf_current_user->user_login, $mail_body);
        }
        if (preg_match('/\[ARF_current_useremail\]/', $mail_body)) {
            $mail_body = str_replace('[ARF_current_useremail]', $arf_current_user->user_email, $mail_body);
        }

        
        $mail_body = apply_filters('arfbefore_autoresponse_send_mail_body', $mail_body, $entry_id, $form_id);

        $attachments = apply_filters('arfautoresponderattachment', array(), $form, array('entry' => $entry));

        $mail_body = @nl2br($mail_body);

        $arnotifymodel->send_notification_email_user($to_email, $subject, $mail_body, $reply_to, $reply_to_name, $plain_text, $attachments);

        return $to_email;
    }

    function arfchangesmtpsetting($phpmailer) {
        global $arfsettings;

        
        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host) || !isset($arfsettings->smtp_username) || empty($arfsettings->smtp_username) || !isset($arfsettings->smtp_password) || empty($arfsettings->smtp_password)) {
                return;
            }
        } else {
            if (!isset($arfsettings->smtp_host) || empty($arfsettings->smtp_host)) {
                return;
            }
        }
        
        if (!isset($arfsettings->smtp_port) || empty($arfsettings->smtp_port))
            $arfsettings->smtp_port = 25;

        
        $phpmailer->IsSMTP();

        
        $phpmailer->Host = $arfsettings->smtp_host;
        $phpmailer->Port = $arfsettings->smtp_port;

        
        if (isset($arfsettings->is_smtp_authentication) && $arfsettings->is_smtp_authentication == '1') {
            $phpmailer->SMTPAuth = true;
        }else{
            $phpmailer->SMTPAuth = false;
        }
        
        $phpmailer->Username = $arfsettings->smtp_username;
        $phpmailer->Password = $arfsettings->smtp_password;
        if (isset($arfsettings->smtp_encryption) and $arfsettings->smtp_encryption != '' and $arfsettings->smtp_encryption != 'none') {
            $phpmailer->SMTPSecure = $arfsettings->smtp_encryption;
        }
    }

    function getBrowser($user_agent) {
        $u_agent = $user_agent;
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version = "";

        
        if (@preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (@preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (@preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        
        if (@preg_match('/MSIE/i', $u_agent) && !@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (@preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (@preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (@preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (@preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (@preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) .
                ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (!@preg_match_all($pattern, $u_agent, $matches)) {
            
        }

        
        $i = count($matches['browser']);
        if ($i != 1) {
            
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version = $matches['version'][0];
            } else {
                $version = $matches['version'][1];
            }
        } else {
            $version = $matches['version'][0];
        }

        
        if ($version == null || $version == "") {
            $version = "?";
        }

        return array(
            'userAgent' => $u_agent,
            'name' => $bname,
            'version' => $version,
            'platform' => $platform,
            'pattern' => $pattern
        );
    }

}

?>