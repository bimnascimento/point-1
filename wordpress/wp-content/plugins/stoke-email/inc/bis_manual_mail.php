<?php

class BisManualMailSending {

    public static function array_search_multi_two_val($needle, $needle2, $haystack) {
        foreach ($haystack as $key => $value) {
            //var_dump($haystack);
            //previous notification mail should not affect for same product and same person
            if (in_array($needle, $value) && in_array($needle2, $value) && !isset($haystack[$key]['notification_time'])) {
                return $key;
            }
        }
        return false;
    }
   
    public static function mail_send_ajax_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('.email_list_metabox span.bis_mail_send').on("click", bisSendNow);
            });
            function bisSendNow() {
                var send_confirm = confirm('Do you want to Send Mail Notification Now?');
                if (send_confirm == true) {
                    var sendnow_variartion_id = jQuery(this).attr('data-sendnowvarid');
                    var sendnow_product_id = jQuery(this).attr('data-sendnowproid');
                    if (typeof sendnow_variartion_id !== 'undefined' && sendnow_variartion_id !== false) {
                        var mailids = Array();
                        var mergeddatasmail = {};
                        jQuery('.mail_send_op' + sendnow_variartion_id).each(function () {
                            if (jQuery(this).is(':checked')) {
                                mailids.push(jQuery(this).next().attr('id'));
                                mergeddatasmail[jQuery(this).next().attr('id')] = jQuery(this).next().attr('data-lang');
                                
                                this.checked = false; 
                              <?php if ('on' == get_option('bis_delete_uncheck_subscribers')) { ?> 
                                jQuery(this).parent("p").fadeOut('5000');
                              <?php } ?>
                            }
                        });
                        console.log(mailids);
                        console.log(mergeddatasmail);
                        var data = ({
                            action: 'sendemailnow',
                            mailids: mailids,
                            mergedemaildata: mergeddatasmail,
                            productid: sendnow_product_id,
                            variationid: sendnow_variartion_id, //for variation level
                        });
                        // console.log(variation_id.length);
                    }
                    else {
                        var mailids = Array();
                        var mergeddatasmail = {};
                        jQuery('.mail_send_op').each(function () {
                            if (jQuery(this).is(':checked')) {
                                mailids.push(jQuery(this).next().attr('id'));
                                mergeddatasmail[jQuery(this).next().attr('id')] = jQuery(this).next().attr('data-lang');
                               this.checked = false; 
                                <?php if ('on' == get_option('bis_delete_uncheck_subscribers')) { ?> 
                                jQuery(this).parent("p").fadeOut('5000');
                              <?php } ?>
                            }
                        });
                        console.log(mailids);
                        console.log(mergeddatasmail);
                        var data = ({
                            action: 'sendemailnow',
                            mailids: mailids,
                            mergedemaildata: mergeddatasmail,
                            productid: sendnow_product_id, //for product level
                        });
                    }
                    jQuery.post(ajaxurl, data,
                            function (response) {
                            });
                }
            }
        </script>
        <style type="text/css">
        <?php if ('on' == get_option('bis_automatic_noti_mail')) { ?>
                .email_list_metabox span.bis_mail_send{
                    display:none;
                }
        <?php } else { ?>
                .email_list_metabox span.bis_mail_send{
                    display:block;
                }
        <?php } ?>
            .email_list_metabox span.bis_mail_send{
                width:100%;
                text-align:center;
                margin-top:10px;
            }
        </style>
        <?php
    }
      
    public static function bis_send_mail() {
        if (isset($_POST['productid'])) {
            global $wpdb;
            global $woocommerce;
   
            $productid = $_POST['productid'];
            $post_id = $productid; // for using same code
            $send_mail_list = $_POST['mailids'];
            $translated_subject = get_option('instock_email_subject');
            $translated_message = get_option('instock_email_messages');
            //desing options
            $header_color = get_option('bis_email_header_color');
            $header_text_color = get_option('bis_email_header_text_color');
            $body_color = get_option('bis_email_body_color');
            $body_text_color = get_option('bis_email_body_text_color');

            if (isset($_POST['variationid'])) {
                $variation_id = $_POST['variationid'];
//echo 'variable';
                foreach ($send_mail_list as $mail_id) {

                    if (isset($_POST['mergedemaildata'])) {
                        $mergedemaildata = $_POST['mergedemaildata'];
                    } else {
                        $mergedemaildata = '';
                    }
                    $getlanguage = @$mergedemaildata[$mail_id];


                    if (isset($mail_id)) {
                        $to = $mail_id; // note the comma
                        $subscribe_lang = $getlanguage;
                        if ($subscribe_lang != 'not_active') {
                            //subject translation
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $subscribe_lang));

                            if (!empty($string)) {
                                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $subscribe_lang));
                                $translated_subject = $translated_string[0]->value;
                            } else {
                                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject'));
                                $string_id = $string[0]->id;
                                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                                // update_option('translated_string', $translated_string[0]->value);
                                $translated_subject = $translated_string[0]->value;
                            }
                            if ($translated_subject == NULL) {
                                $translated_subject = get_option('instock_email_subject');
                            }

                            //msassage translation
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $subscribe_lang));
                            if (!empty($string)) {
                                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $subscribe_lang));
                                $translated_message = $translated_string[0]->value;
                            } else {
                                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages'));
                                $string_id = $string[0]->id;
                                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                                // update_option('translated_string', $translated_string[0]->value);
                                $translated_message = $translated_string[0]->value;
                            }
                            if ($translated_message == NULL) {
                                $translated_message = get_option('instock_email_messages');
                            }
                        } else {//plugin not active so use get_option
                            $translated_subject = get_option('instock_email_subject');
                            $translated_message = get_option('instock_email_messages');
                        }
                    }

                    if (!$translated_subject) {
                        $translated_subject = get_option('instock_email_subject');
                    }
                    if (!$translated_message) {
                        $translated_message = get_option('instock_email_messages');
                    }
                    if(strchr(getting_permalink($post_id),"?")){
                    $cart_url = add_query_arg('add-to-cart',$post_id,getting_permalink($post_id));
                    }
                    else{
                    $cart_url = add_query_arg('?add-to-cart',$post_id,getting_permalink($post_id));
                    }
                    $return_attribute_slug=get_attribute_slug($variation_id);
                    $link=  implode("&",$return_attribute_slug);
                    $subject = str_replace('[product_url]', get_permalink($variation_id), str_replace('[product_title]', get_the_title($variation_id), str_replace('[site_title]', get_option('blogname'), $translated_subject)));
                    $message = str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>",\str_replace('[cart_url]',"<a href=" .$cart_url."&variation_id=". $variation_id. "&".$link. ">" .$cart_url."&variation_id=". $variation_id. "&".$link."</a>", str_replace('[product_title]', get_the_title($variation_id), str_replace('[site_title]', get_option('blogname'), str_replace('[productinfo]', '', $translated_message)))));
                    ob_start();
                    wc_get_template('emails/email-header.php', array('email_heading' => $subject));
                    echo $message;
                    wc_get_template('emails/email-footer.php');
                    $woo_temp_msg = ob_get_clean();
                    if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                        $mainname = get_option('woocommerce_email_from_name');
                        $mainemail = get_option('woocommerce_email_from_address');
// To send HTML mail, the Content-type header must be set
                        $headers = 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
// Additional headers
                        $headers .= 'From:' . $mainname . '  <' . $mainemail . '>' . "\r\n";
// Mail it
                        if ('mail' == get_option('bis_mail_function')) {
                            if (mail($to, $subject, $woo_temp_msg, $headers)) {
                                $master_list = get_option('bis_master_list');
                                $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                $current_time = current_time('timestamp');
                                $master_list[$key]['notification_time'] = $current_time;
                                update_option('bis_master_list', $master_list);


                                $updated_list = get_post_meta($post_id, 'notification_email_list_' . $variation_id . '', true);
                                $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                                //var_dump($key_to_delete);
                                if (($key_to_delete != null) || ($key_to_delete == 0)) {
                                    //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                                    if ('on' == get_option('bis_delete_uncheck_subscribers')){
                                    unset($updated_list[$key_to_delete]);}
                                    // var_dump($updated_list);
                                    // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                                    //var_dump($updating_list);
                                    update_post_meta($post_id, 'notification_email_list_' . $variation_id . '', $updated_list);
                                    delete_post_meta($post_id, 'mailsending_op_' . $variation_id . '_' . $key_to_delete);
                                }
                            }
                        } elseif ('wp_mail' == get_option('bis_mail_function')) {
                            if (wp_mail($to, $subject, $woo_temp_msg, $headers)) {
                                $master_list = get_option('bis_master_list');
                                $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                $current_time = current_time('timestamp');
                                $master_list[$key]['notification_time'] = $current_time;
                                update_option('bis_master_list', $master_list);

                                $updated_list = get_post_meta($post_id, 'notification_email_list_' . $variation_id . '', true);
                                $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                                //var_dump($key_to_delete);
                                if (($key_to_delete != null) || ($key_to_delete == 0)) {
                                    //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                                    if ('on' == get_option('bis_delete_uncheck_subscribers')){
                                    unset($updated_list[$key_to_delete]);}
                                    // var_dump($updated_list);
                                    // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                                    //var_dump($updating_list);
                                    update_post_meta($post_id, 'notification_email_list_' . $variation_id . '', $updated_list);
                                    delete_post_meta($post_id, 'mailsending_op_' . $variation_id . '_' . $key_to_delete);
                                }
                            }
                        }
                    } else {
                        $mailer = WC()->mailer();
                        $mailer->send($to, $subject, $woo_temp_msg, '', '');
                        $master_list = get_option('bis_master_list');
                        $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                        $current_time = current_time('timestamp');
                        $master_list[$key]['notification_time'] = $current_time;
                        update_option('bis_master_list', $master_list);

                        $updated_list = get_post_meta($post_id, 'notification_email_list_' . $variation_id . '', true);
                        $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                        //var_dump($key_to_delete);
                        if (($key_to_delete != null) || ($key_to_delete == 0)) {
                            //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                            if ('on' == get_option('bis_delete_uncheck_subscribers')){
                            unset($updated_list[$key_to_delete]);}
                            // var_dump($updated_list);
                            // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                            //var_dump($updating_list);
                            update_post_meta($post_id, 'notification_email_list_' . $variation_id . '', $updated_list);
                            delete_post_meta($post_id, 'mailsending_op_' . $variation_id . '_' . $key_to_delete);
                        }
                    }
                    //deleting each subscriber and mail sending op in every iteration
                }
            } else {
//echo 'simple';
                foreach ($send_mail_list as $mail_id) {
                    if (isset($_POST['mergedemaildata'])) {
                        $mergedemaildata = $_POST['mergedemaildata'];
                    } else {
                        $mergedemaildata = '';
                    }
                    $getlanguage = @$mergedemaildata[$mail_id];

                    // Main file for changing Manual mail Triggering

                    if (isset($mail_id)) {
                        $to = $mail_id; // note the comma
                        $subscribe_lang = $getlanguage;
                        if ($subscribe_lang != 'not_active') {
                            //subject translation
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $subscribe_lang));

                            if (!empty($string)) {
                                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $subscribe_lang));
                                $translated_subject = $translated_string[0]->value;
                            } else {
                                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject'));
                                $string_id = $string[0]->id;
                                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                                // update_option('translated_string', $translated_string[0]->value);
                                $translated_subject = $translated_string[0]->value;
                            }
                            //msassage translation
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $subscribe_lang));
                            if (!empty($string)) {
                                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $subscribe_lang));
                                $translated_message = $translated_string[0]->value;
                            } else {
                                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages'));
                                $string_id = $string[0]->id;
                                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                                // update_option('translated_string', $translated_string[0]->value);
                                $translated_message = $translated_string[0]->value;
                            }
                        } else {// plugin not active so use get_option
                            $translated_subject = get_option('instock_email_subject');
                            $translated_message = get_option('instock_email_messages');
                        }
                    }
                    if (!$translated_subject) {
                        $translated_subject = get_option('instock_email_subject');
                    }
                    if (!$translated_message) {
                        $translated_message = get_option('instock_email_messages');
                    }
                    if(strchr(getting_permalink($post_id),"?")){
                    $cart_url = add_query_arg('add-to-cart',$post_id,getting_permalink($post_id));
                    }
                    else{
                    $cart_url = add_query_arg('?add-to-cart',$post_id,getting_permalink($post_id));
                    }
                    //$cart_url = add_query_arg('?add-to-cart',$post_id,getting_permalink($post_id));
                    $subject = str_replace('[product_url]', get_permalink($post_id), str_replace('[product_title]', get_the_title($post_id), str_replace('[site_title]', get_option('blogname'), $translated_subject)));
                    $message = str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>",\str_replace('[cart_url]',"<a href=" . $cart_url. ">" .$cart_url. "</a>", str_replace('[product_title]', get_the_title($post_id), str_replace('[site_title]', get_option('blogname'), str_replace('[productinfo]', '', $translated_message)))));
                                    
                    ob_start();
                    wc_get_template('emails/email-header.php', array('email_heading' => $subject));
                    echo $message;
                    wc_get_template('emails/email-footer.php');
                    $woo_temp_msg = ob_get_clean();
                    if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                        $mainname = get_option('woocommerce_email_from_name');
                        $mainemail = get_option('woocommerce_email_from_address');
                        // To send HTML mail, the Content-type header must be set
                        $headers = 'MIME-Version: 1.0' . "\r\n";
                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
                        // Additional headers
                        $headers .= 'From:' . $mainname . '  <' . $mainemail . '>' . "\r\n";
                        // Mail it
                        if ('mail' == get_option('bis_mail_function')) {
                            if (mail($to, $subject, $woo_temp_msg, $headers)) {
                                $master_list = get_option('bis_master_list');
                                $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                $current_time = current_time('timestamp');
                                $master_list[$key]['notification_time'] = $current_time;
                                update_option('bis_master_list', $master_list);



                                $updated_list = get_post_meta($post_id, 'notification_email_list', true);

                                $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                                //var_dump($key_to_delete);
                                if (($key_to_delete != null) || ($key_to_delete == 0)) {
                                    //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                                    if ('on' == get_option('bis_delete_uncheck_subscribers')){
                                    unset($updated_list[$key_to_delete]);}
                                    //  var_dump($updated_list);
                                    // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                                    //var_dump($updating_list);
                                    update_post_meta($post_id, 'notification_email_list', $updated_list);
                                    delete_post_meta($post_id, 'mailsending_op_' . $key_to_delete);
                                }
                            }
                        } elseif ('wp_mail' == get_option('bis_mail_function')) {
                            if (wp_mail($to, $subject, $woo_temp_msg, $headers)) {
                                $master_list = get_option('bis_master_list');
                                $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                $current_time = current_time('timestamp');
                                $master_list[$key]['notification_time'] = $current_time;
                                update_option('bis_master_list', $master_list);


                                $updated_list = get_post_meta($post_id, 'notification_email_list', true);

                                $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                                //var_dump($key_to_delete);
                                if (($key_to_delete != null) || ($key_to_delete == 0)) {
                                    //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                                    if ('on' == get_option('bis_delete_uncheck_subscribers')){
                                    unset($updated_list[$key_to_delete]);}
                                    //var_dump($updated_list);
                                    // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                                    //var_dump($updating_list);
                                    update_post_meta($post_id, 'notification_email_list', $updated_list);
                                    delete_post_meta($post_id, 'mailsending_op_' . $key_to_delete);
                                }
                            }
                        }
                    } else {
                        $mailer = WC()->mailer();
                        $mailer->send($to, $subject, $woo_temp_msg, '', '');
                        $master_list = get_option('bis_master_list');
                        $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                        $current_time = current_time('timestamp');
                        $master_list[$key]['notification_time'] = $current_time;
                        update_option('bis_master_list', $master_list);


                        $updated_list = get_post_meta($post_id, 'notification_email_list', true);

                        $key_to_delete = backinstocknotifier_searchmailid($mail_id, $updated_list); // $mail_id -> current mail in interation
                        //var_dump($key_to_delete);
                        if (($key_to_delete != null) || ($key_to_delete == 0)) {
                            //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
                            if ('on' == get_option('bis_delete_uncheck_subscribers')){
                            unset($updated_list[$key_to_delete]);}
                            //var_dump($updated_list);
                            // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
                            //var_dump($updating_list);
                            update_post_meta($post_id, 'notification_email_list', $updated_list);
                            delete_post_meta($post_id, 'mailsending_op_' . $key_to_delete);
                        }
                    }
                    //deleting each subscriber and mail sending op in every iteration
                    //$key_to_delete = array_search($mail_id, $updated_list); // $mail_id -> current mail in interation
                    //$updating_list = array_diff($updated_list, array($mail_id));
                }
            }
        }
        exit();
    }

}

add_action('admin_footer', array('BisManualMailSending', 'mail_send_ajax_script'));
add_action('wp_ajax_sendemailnow', array('BisManualMailSending', 'bis_send_mail'));
//add_action('wp_head', array('BisManualMailSending','getting_permalink'));


function getnotificationlist() {
    $post_id = '8';
    $mail_id = 'pawnrick665@gmail.com';
    $updated_list = get_post_meta($post_id, 'notification_email_list', true);
    //var_dump($updated_list);
    //$updated_list = array('', 'vignesh.p@asenton.com');
    // var_dump($updating_list);
    //update_post_meta($post_id, 'notification_email_list', $updating_list);
    //delete_post_meta($post_id, 'mailsending_op_' . $key_to_delete);
}
function getting_permalink($postid)
     {  
    global $post;
    return get_post_permalink($postid);
   //var_dump(get_option('get_permalink')); 
    }
function backinstocknotifier_searchmailid($mailid, $listofsubscription) {
    if (is_array($listofsubscription)) {
        foreach ($listofsubscription as $key => $val) {
            if ($val['mail'] === $mailid) {
                return $key;
            }
        }
    }
    return null;
}
?>