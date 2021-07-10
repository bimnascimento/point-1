<?php
/*
  Plugin Name: WooCommerce Back In Stock Notifier
  Plugin URI:
  Description:  WooCommerce Back In Stock Notifier is a WooCommerce Extension Plugin to notify all the users who subscribed to get notified when the Product is Back In Stock.
  Version: 7.9
  Author: Fantastic Plugins
  Author URI:
 */

/*
  Copyright 2014 Fantastic Plugins. All Rights Reserved.
  This Software should not be used or changed without the permission
  of Fantastic Plugins.
 */

class BackInStockNotifier {

    public static function check_woocommerce_plugin_is_active() {
        if (is_multisite()) {
            // This Condition is for Multi Site WooCommerce Installation
            if (!is_plugin_active_for_network('woocommerce/woocommerce.php') && (!is_plugin_active('woocommerce/woocommerce.php'))) {
                if (is_admin()) {
                    $variable = "<div class='error'><p> WooCommerce Back In Stock Notifier will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin. </p></div>";
                    echo $variable;
                }
                return;
            }
        } else {
            // This Condition is for Single Site WooCommerce Installation
            if (!is_plugin_active('woocommerce/woocommerce.php')) {
                if (is_admin()) {
                    $variable = "<div class='error'><p> WooCommerce Back In Stock Notifier will not work until WooCommerce Plugin is Activated. Please Activate the WooCommerce Plugin. </p></div>";
                    echo $variable;
                }
                return;
            }
        }
    }

    public static function footer_link() {
        global $post, $product;
        global $wpdb;
        global $woocommerce;
        $unsubscribe_link = esc_url_raw(add_query_arg(array('bis_product_id' => $_POST['productid'], 'bis_variation_id' => $_POST['variationid'] != '' ? $_POST['variationid'] : 'no', 'bis_mailid' => $_POST['notifyaddress']), get_post_permalink($_POST['productid'])));
        $post_id = $_POST['productid'];
        $post = get_post($_POST['productid']);
        $currentuser_lang = empty($_POST['lang']) ? "en" : $_POST['lang'];
        if ($_POST['lang'] != 'not_active') {
            $subscribe_lang = $currentuser_lang;
            //subject translation
            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_line_editor', $subscribe_lang));

            if (!empty($string)) {
                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_line_editor', $subscribe_lang));
                @$footer_line = $translated_string[0]->value;
            } else {
                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_line_editor'));
                @$string_id = $string[0]->id;
                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                // update_option('translated_string', $translated_string[0]->value);
                @$footer_line = $translated_string[0]->value;
            }
            if ($footer_line == NULL) {
                $footer_line = get_option('footer_line_editor');
            }


            //For Footer Text
            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_link_editor', $subscribe_lang));

            if (!empty($string)) {
                $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_link_editor', $subscribe_lang));
                @$footer_link = $translated_string[0]->value;
            } else {
                $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'footer_link_editor'));
                $string_id = $string[0]->id;
                $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$subscribe_lang'");
                // update_option('translated_string', $translated_string[0]->value);
                @$footer_link = $translated_string[0]->value;
            }
            if ($footer_link == NULL) {
                $footer_link = get_option('footer_link_editor');
            }
        } else {
            $footer_line = apply_filters('footer_custom_message', get_option('footer_line_editor'));

            $footer_link = apply_filters('footer_link_text', get_option('footer_link_editor'));
        }


        if (get_option('instock_subscription_unsubscribe_link') == 'yes') {
            $footercustomization = ' <a href="' . $unsubscribe_link . '"> ' . $footer_link . ' </a>';
            $footer_real = str_replace(array('[product_title]', '[unsublink]'), array(get_the_title($post_id), $footercustomization), $footer_line);
            $returntestingplugin = $footer_real;
            return $returntestingplugin;
        } else {
            return get_option('woocommerce_email_footer_text');
        }
    }

    public static function check_product_in_stock_troubleshooting($message, $object) {
        global $woocommerce;
        if (get_option('show_notifyme_label') != 'woocommerce_stock_html') {
            if ($object->is_type('simple')) {
                return $message . self::checkproductinstock('', '');
            }
        }
        return $message;
    }

    public static function alter_available_variation_for_backward_compatible($array, $thisobject, $current_obj) {
        global $woocommerce;
        if ($woocommerce->version < (float) "2.2.0") {
            // for less than 2.2 version

            if (!$array['is_in_stock']) {

                $current_data = self::checkproductinstock($array['availability_html'], $array['availability_html'], $current_obj);
                $array['availability_html'] = $current_data;
            }
        }
        return $array;
    }

    public static function checkproductinstock($availability, $availability_availability, $obj = '') {
        global $post;
        global $product;
        $new = '';

        $getproductobject = @get_product($post->ID);
        if (!$getproductobject->is_type('variable') && (!$getproductobject->is_type('variable-subscription')) && ($obj == '')) {
            $obj = $getproductobject;
        }

        if ($obj != '') {
            if (is_user_logged_in()) {
                if ("yes" == get_option("bis_show_form_member")) {
                    $terms = @wp_get_post_terms($post->ID, 'product_cat');
                    $cat_present = false;
                    foreach ($terms as $term) {
                        if (in_array($term->term_id, (array) get_option("bis_hide_products_cat"))) {
                            $cat_present = true;
                        }
                    }


                    if ($cat_present != true) {
                        $bis_hide_products = get_option('bis_hide_products');
                        if (is_array($bis_hide_products)) {
                            $bis_hide_products = get_option('bis_hide_products');
                        } else {
                            $bis_hide_products = explode(',', $bis_hide_products);
                        }
                        if (!in_array(@$post->ID, (array) $bis_hide_products)) {
                            if (is_product() || is_page()) {
                                $unsubscribe = false;
                                //Unsubscribe function
                                if (isset($_GET['bis_product_id'])) {

                                    if ($_GET['bis_variation_id'] != 'no') { //handle for variable product
                                        // New Code to Alter Subscription Function
                                        $getdatas = get_post_meta($_GET['bis_product_id'], 'notification_email_list_' . $_GET['bis_variation_id'], true);

                                        $mail_id = $_GET['bis_mailid'];
                                        $keytodelete = backinstocknotifier_searchmailid($mail_id, $getdatas);
                                        // var_dump($keytodelete);
                                        if (($keytodelete != null) || ($keytodelete == 0)) {


                                            unset($getdatas[$keytodelete]);

                                            if (update_post_meta($_GET['bis_product_id'], 'notification_email_list_' . $_GET['bis_variation_id'], $getdatas)) {
                                                $unsubscribe = true;
                                            }
                                            delete_post_meta($_GET['bis_product_id'], 'mailsending_op_' . $_GET['bis_variation_id'] . '_' . $keytodelete);
                                        }
                                    } else { // handle for simple product
                                        // New Code to Alter Subscription Function
                                        $getdatas = get_post_meta($_GET['bis_product_id'], 'notification_email_list', true);

                                        $mail_id = $_GET['bis_mailid'];
                                        $keytodelete = backinstocknotifier_searchmailid($mail_id, $getdatas);
                                        // var_dump($keytodelete);
                                        if (is_array($getdatas)) {
                                            if (($keytodelete != null) || ($keytodelete == 0)) {


                                                unset($getdatas[$keytodelete]);

                                                if (update_post_meta($_GET['bis_product_id'], 'notification_email_list', $getdatas)) {
                                                    $unsubscribe = true;
                                                }
                                                delete_post_meta($_GET['bis_product_id'], 'mailsending_op_' . $keytodelete);
                                            }
                                        }
                                    }
                                }
                                //Unsubscribe function END
                                if (get_option('backinstock_notify_button_line') == "yes") {
                                    $new_line = '<br/>';
                                } else {
                                    $new_line = '';
                                }
                                if ($unsubscribe) {
                                    $unsubscribe_text = __('You Have been successfully unsubscribed', 'backinstocknotifier');
                                } else {
                                    $unsubscribe_text = '';
                                }
                                //var_dump($obj->is_in_stock());
                                if (!$obj->product_type == 'variation') {
                                    if (!$product->is_in_stock()) {

                                        $new = "<form class='outofstocknotifyme' action='' method='post'>


                <p id='bis_form_title'>" . get_option('instock_get_notified_form_caption') . "</p>";
                                        if (get_option('bis_error_msg_position') == 'above') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'above') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }

                                        $new .= "<p class = 'notifymeptag'><input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail' name = 'subscribersemail' placeholder = '" . get_option('instock_get_notified_placeholder_text') . "' value = ''/>" . $new_line . "<input type = 'submit' class = 'notifyme' id = 'backinstock_button' name = 'subscribeme' value = '" . get_option('instock_get_notified_button_label') . "'/></p><p><input type = 'hidden' name = 'variation_id' value = ''></p></form>";
                                        if (get_option('bis_error_msg_position') == 'below') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'below' || get_option('bis_success_msg_position') == 'formtop') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }
                                    }
                                } else {
                                    $current_user = get_current_user_id();
                                    $user_datas = get_userdata($current_user);
                                    if (!empty($user_datas)) {
                                        $user_email = $user_datas->user_email;
                                    }
                                    $stock_status = $obj->is_in_stock();

                                    if (!$stock_status) {
                                        $new = "<form class = 'outofstocknotifyme' id = 'bis_outofstock' action = '' method = 'post' style='display: block;'>
                <div class = 'outofstock_message'></div>
                               <p id='bis_form_title'>" . get_option('instock_get_notified_form_caption') . "</p>";
                                        if (get_option('bis_error_msg_position') == 'above') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'above') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }

                                        $new .= "<p class = 'notifymeptag'><input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail' name = 'subscribersemail' placeholder = '" . get_option('instock_get_notified_placeholder_text') . "' value = $user_email>" . $new_line . "<input type = 'submit' class = 'notifyme' id = 'backinstock_button' name = 'subscribeme' value = '" . get_option('instock_get_notified_button_label') . "'/></p>
                               <p><input type = 'hidden' name = 'out_stock_variable_id' value = ''></form></p>";
                                        if (get_option('bis_error_msg_position') == 'below') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'below' || get_option('bis_success_msg_position') == 'formtop') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }
                                    }
                                }

                                return $availability . $unsubscribe_text . $new;
                            }
                        }
                    }
                }
            }

            if (!is_user_logged_in()) {
                if ('yes' == get_option('bis_show_form_guest')) {
                    $terms = wp_get_post_terms($post->ID, 'product_cat');
                    $cat_present = false;
                    foreach ($terms as $term) {
                        if (in_array($term->term_id, (array) get_option("bis_hide_products_cat"))) {
                            $cat_present = true;
                        }
                    }

                    if ($cat_present != true) {
                        $bis_hide_products = get_option('bis_hide_products');
                        if (is_array($bis_hide_products)) {
                            $bis_hide_products = get_option('bis_hide_products');
                        } else {
                            $bis_hide_products = explode(',', $bis_hide_products);
                        }
                        if (!in_array($post->ID, (array) $bis_hide_products)) {

                            if (is_product() || is_page()) {
                                $unsubscribe = false;
                                //Unsubscribe function
                                if (isset($_GET['bis_product_id'])) {

                                    if ($_GET['bis_variation_id'] != 'no') { //handle for variable product
                                        $arr = get_post_meta($_GET['bis_product_id'], 'notification_email_list_' . $_GET['bis_variation_id'] . '');

                                        foreach ($arr as $newarr) {
                                            $key_to_delete = array_search($_GET['bis_mailid'], $newarr);
                                            $newarr = array_diff($newarr, array($_GET['bis_mailid']));
                                            if (update_post_meta($_GET['bis_product_id'], 'notification_email_list_' . $_GET['bis_variation_id'] . '', $newarr)) {
                                                $unsubscribe = true;
                                            }
                                            delete_post_meta($_GET['bis_product_id'], 'mailsending_op_' . $_GET['bis_variation_id'] . '_' . $key_to_delete);
                                        }
                                    } else { // handle for simple product
                                        $arr = get_post_meta($_GET['bis_product_id'], 'notification_email_list');

                                        foreach ($arr as $newarr) {
                                            //var_dump($newarr);
                                            $key_to_delete = array_search($_GET['bis_mailid'], $newarr);
                                            // var_dump($key_to_delete);
                                            $newarr = array_diff($newarr, array($_GET['bis_mailid']));
                                            if (update_post_meta($_GET['bis_product_id'], 'notification_email_list', $newarr)) {
                                                $unsubscribe = true;
                                            }
                                            delete_post_meta($_GET['bis_product_id'], 'mailsending_op_' . $key_to_delete);
                                        }
                                    }
                                }
                                //Unsubscribe function END
                                if (get_option('backinstock_notify_button_line') == "yes") {
                                    $new_line = '<br/>';
                                } else {
                                    $new_line = '';
                                }
                                if ($unsubscribe) {
                                    $unsubscribe_text = __('You Have been successfully unsubscribed', 'backinstocknotifier');
                                } else {
                                    $unsubscribe_text = '';
                                }
                                if (!$obj->product_type == 'variation') {
                                    if (!$product->is_in_stock()) {

                                        $new = "<form class='outofstocknotifyme' action='' method='post'>


                <p id='bis_form_title'>" . get_option('instock_get_notified_form_caption') . "</p>";
                                        if (get_option('bis_error_msg_position') == 'above') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'above') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }

                                        $new .= "<p class = 'notifymeptag'><input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail' name = 'subscribersemail' placeholder = '" . get_option('instock_get_notified_placeholder_text') . "' value = ''/>" . $new_line . "<input type = 'submit' class = 'notifyme' id = 'backinstock_button' name = 'subscribeme' value = '" . get_option('instock_get_notified_button_label') . "'/></p><p><input type = 'hidden' name = 'variation_id' value = ''></p></form>";
                                        if (get_option('bis_error_msg_position') == 'below') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'below' || get_option('bis_success_msg_position') == 'formtop') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }
                                    }
                                } else {
//                                $current_user = get_current_user_id();
//                               $user_datas = get_userdata($current_user);
//                               if (!empty($user_datas)) {
//                               $user_email=$user_datas->user_email;                                
//                               }
                                    $stock_status = $obj->is_in_stock();
                                    if (!$stock_status) {
                                        $new = "<form class = 'outofstocknotifyme' id = 'bis_outofstock' action = '' method = 'post' style='display: block;'>
                <div class = 'outofstock_message'></div>
                               <p id='bis_form_title'>" . get_option('instock_get_notified_form_caption') . "</p>";
                                        if (get_option('bis_error_msg_position') == 'above') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'above') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }

                                        $new .= "<p class = 'notifymeptag'><input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail' name = 'subscribersemail' placeholder = '" . get_option('instock_get_notified_placeholder_text') . "' value = ''>" . $new_line . "<input type = 'submit' class = 'notifyme' id = 'backinstock_button' name = 'subscribeme' value = '" . get_option('instock_get_notified_button_label') . "'/></p>
                               <p><input type = 'hidden' name = 'out_stock_variable_id' value = ''></form></p>";
                                        if (get_option('bis_error_msg_position') == 'below') {
                                            $new .= "<div class='outofstock_error_msg'></div>";
                                        }
                                        if (get_option('bis_success_msg_position') == 'below' || get_option('bis_success_msg_position') == 'formtop') {

                                            $new .= "<div class='outofstock_success_msg'></div>";
                                        }
                                    }
                                }
                                return $availability . $unsubscribe_text . $new;
                            }
                        }
                    }
                }
            }
        }

        return $availability;
    }

    public static function notifycss() {
        ?>
        <style type="text/css">
        <?php if (get_option('backinstock_notify_button_line') != "yes") { ?>
                .notifymeptag {

                    display:inline-flex;
                    clear: both;

                }
        <?php } ?>
            .outofstock_success_msg{
                color:<?php echo get_option('instock_sucess_font_color'); ?>;
                font-style:<?php echo get_option('bis_success_msg_style'); ?>;
                font-weight:<?php echo get_option('bis_success_msg_weight'); ?>;
            }
            .outofstock_error_msg{
                color:<?php echo get_option('instock_error_font_color'); ?>;
                font-style:<?php echo get_option('bis_error_msg_style'); ?>;
                font-weight:<?php echo get_option('bis_error_msg_weight'); ?>;
            }
        <?php echo get_option('instock_custom_css'); ?>
        </style>
        <?php
    }

    public static function enqueuenotifyscript() {
        wp_enqueue_script('jquery');
    }

    public static function in_array_multi($single, $mutli) {
        if (in_array($single, $mutli)) {
            return true;
        }
        foreach ($mutli as $multi_array) {
            if (is_array($multi_array) && self::in_array_multi($single, $multi_array)) //should be array as well in array
                return true;
        }
        return false;
    }

    public static function array_search_multi($needle, $haystack) {
        foreach ($haystack as $key => $value) {
            if (in_array($needle, $value)) {
                return $key;
            }
        }
        return false;
    }

    public static function notifyscript($post) {

        $newid = $post;
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
        <?php
        $current_user = get_current_user_id();
        $user_datas = get_userdata($current_user)
        ?>
                var prefil_email = <?php
        if (!empty($user_datas)) {
            echo "true";
        } else {
            echo "false";
        }
        ?>;
                if (prefil_email) {
                    var user_email_id = "<?php
        if (!empty($user_datas)) {
            echo $user_datas->user_email;
        }
        ?>";
                    jQuery("#backinstock_textbox").val(user_email_id);  // adding user email by default if the user is logged in
                }
                jQuery(document).on('click', '.notifyme', function () {
                    //alert("You Clicked Subscription");
                    var notifyaddress = jQuery('.subscribersemail').val();
                    var out_stock_variable_id = jQuery('input[name="variation_id"]').val();
        <?php
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// check for plugin using plugin name
        $plugin = "wpml-string-translation/plugin.php";
        if (is_plugin_active($plugin)) {
//plugin is activated
            $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
            echo 'var lang_code = "' . $currentuser_lang . '";';
        } else {
            echo 'var lang_code = "not_active";';
        }
        ?>
                    if (notifyaddress === '') {
                        // alert('Email Field Should Not Be Left Blank');
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 1000; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }
                    if (IsEmail(notifyaddress) === false) {
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 1000; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }

                    var data = ({
                        action: 'outofstocknotify',
                        notifyaddress: notifyaddress,
                        productid: <?php echo $newid; ?>,
                        lang: lang_code,
                        variationid: out_stock_variable_id,
                    });
                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                            function (response) {
                                //alert(response);
                                jQuery('.subscribersemail').val("");

                                if (response == "mailexist") {
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                                        jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option("instock_already_subscribed")); ?>");
                                        jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                                        jQuery('.outofstock_error_msg').fadeIn();
                                        jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option("instock_already_subscribed")); ?>");
                                        jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 1000; ?>);
                                        jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                                } else {
        <?php if ('formtop' == get_option('bis_success_msg_position')) { ?>
                                        jQuery('.notifymeptag').css('display', 'none');
        <?php } ?>
        <?php if ('static' == get_option('bis_success_msg_effect')) { ?>
                                        jQuery('.outofstock_success_msg').text("<?php echo addslashes(get_option('instock_subscribe_email_success')); ?>");
                                        jQuery('.outofstock_error_msg').text("");
        <?php } else { ?>
                                        jQuery('.outofstock_success_msg').fadeIn();
                                        jQuery('.outofstock_success_msg').text("<?php echo addslashes(get_option('instock_subscribe_email_success')); ?>");
                                        jQuery('.outofstock_success_msg').fadeOut(<?php echo get_option('instock_sucess_fadein_time') * 1000; ?>);
                                        jQuery('.outofstock_error_msg').text("");
        <?php } ?>
                                }

                                console.log(response);

                            });
                    return false;
                });
            });
            function IsEmail(email) {
                var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                if (!regex.test(email)) {
                    return false;
                } else {
                    return true;
                }
            }
        </script>
        <?php
    }

    public static function saveajaxvalue() {
        if ($_POST['notifyaddress']) {
            $currentuser_lang = empty($_POST['lang']) ? "en" : $_POST['lang'];
            if ($_POST['variationid'] != '') { //statements for variable product
                $mail_list_array[] = array("mail" => $_POST['notifyaddress'], "lang" => $currentuser_lang);
//$arr = array("mixed array", "of" => "different values");
                if (metadata_exists('post', $_POST['productid'], "notification_email_list_" . $_POST['variationid'] . "") == '1') {
                    $meta = get_post_meta($_POST['productid'], "notification_email_list_" . $_POST['variationid'] . "", true);
                    if (self::in_array_multi($_POST['notifyaddress'], $meta)) { //checking mail for duplication
                        $key_to_check = backinstocknotifier_searchmailid($_POST['notifyaddress'], $meta);
                        if (($key_to_check != null) || ($key_to_check == 0)) {
                            if (get_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $key_to_check, true) == '1') {
                                echo "mailexist";
                            } else {
                                update_post_meta($_POST['productid'], 'notification_email_list_' . $_POST['variationid'] . '', $meta);
                                update_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $key_to_check, '1');
                                echo "mailchecked and ";
                                $existing_master_list = get_option('bis_master_list');
                                if ($existing_master_list == false) {
                                    $existing_master_list = array();
                                }
                                $current_time = current_time('timestamp');
                                $existing_master_list[] = array("product_id" => $_POST['productid'], "variationid" => $_POST['variationid'], "email" => $_POST['notifyaddress'], "time" => $current_time);
                                update_option('bis_master_list', $existing_master_list);
                                include_once 'inc/bis_subscribe_mail.php';
                            }
                        }
                        exit();
                        return false;
                    }
                    if (is_array($meta) && !empty($meta)) { // reassining mail sending option
                        end($meta);
                        $limit = key($meta);
                        for ($i = 0; $i <= $limit; $i++) {

                            if (metadata_exists('post', $_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $i) == '1') {
                                $mail_op[] = get_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $i, true);
                                delete_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $i);
                            }
                        }
                        foreach (@$mail_op as $key => $op) {
                            update_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $key, $op);
                        }
                    }// reassining mail sending option END
                    $newmaillist = array_merge($meta, $mail_list_array);
                    update_post_meta($_POST['productid'], "notification_email_list_" . $_POST['variationid'] . "", $newmaillist);
//new for mail send option
                    $updated_list = get_post_meta($_POST['productid'], "notification_email_list_" . $_POST['variationid'] . "", true);
                    end($updated_list);
                    $last_key = key($updated_list);
                    update_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $last_key, '1');
                } else {
                    update_post_meta($_POST['productid'], "notification_email_list_" . $_POST['variationid'] . "", $mail_list_array);
                    update_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_0', '1'); //new array, so appending zero
                }
            } else { //statements for simple product
                $mail_list_array[] = array("mail" => $_POST['notifyaddress'], "lang" => $currentuser_lang);
//$arr = array("mixed array", "of" => "different values");
                if (metadata_exists('post', $_POST['productid'], 'notification_email_list') == '1') {
                    $meta = get_post_meta($_POST['productid'], "notification_email_list", true);
                    if (self::in_array_multi($_POST['notifyaddress'], $meta)) { //checking mail for duplication
                        $key_to_check = backinstocknotifier_searchmailid($_POST['notifyaddress'], $meta);
                        if (($key_to_check != null) || ($key_to_check == 0)) {

                            if (get_post_meta($_POST['productid'], 'mailsending_op_' . $key_to_check, true) == '1') {
                                echo "mailexist";
                            } else {
                                update_post_meta($_POST['productid'], 'notification_email_list', $meta);
                                update_post_meta($_POST['productid'], 'mailsending_op_' . $key_to_check, '1');
                                echo "mailchecked and ";


                                $existing_master_list = get_option('bis_master_list');
                                if ($existing_master_list == false) {
                                    $existing_master_list = array();
                                }
                                $current_time = current_time('timestamp');
                                $existing_master_list[] = array("product_id" => $_POST['productid'], "variationid" => $_POST['variationid'], "email" => $_POST['notifyaddress'], "time" => $current_time);
                                update_option('bis_master_list', $existing_master_list);
                                include_once 'inc/bis_subscribe_mail.php';
                            }
                        }
                        exit();
                        return false;
                    }
                    if (is_array($meta) && !empty($meta)) { // reassining mail sending option
                        end($meta);
                        $limit = key($meta);
                        for ($i = 0; $i <= $limit; $i++) {

                            if (metadata_exists('post', $_POST['productid'], 'mailsending_op_' . $i) == '1') {
                                $mail_op[] = get_post_meta($_POST['productid'], 'mailsending_op_' . $i, true);
                                delete_post_meta($_POST['productid'], 'mailsending_op_' . $i);
                            }
                        }
                        foreach ($mail_op as $key => $op) {
                            update_post_meta($_POST['productid'], 'mailsending_op_' . $key, $op);
                        }
                    } // reassining mail sending option END

                    $newmaillist = array_merge($meta, $mail_list_array);
                    update_post_meta($_POST['productid'], "notification_email_list", $newmaillist);
//new for mail send option
                    $updated_list = get_post_meta($_POST['productid'], "notification_email_list", true);
                    end($updated_list);
                    $last_key = key($updated_list);
                    update_post_meta($_POST['productid'], 'mailsending_op_' . $last_key, '1');
                } else {
                    update_post_meta($_POST['productid'], "notification_email_list", $mail_list_array);
                    update_post_meta($_POST['productid'], 'mailsending_op_0', '1'); //new array, so appending zero
                }
            }

//master list store
            $existing_master_list = get_option('bis_master_list');
            if ($existing_master_list == false) {
                $existing_master_list = array();
            }
            $current_time = current_time('timestamp');
            $existing_master_list[] = array("product_id" => $_POST['productid'], "variationid" => $_POST['variationid'], "email" => $_POST['notifyaddress'], "time" => $current_time);
            update_option('bis_master_list', $existing_master_list);
            include_once 'inc/bis_subscribe_mail.php';
        }
        exit();
    }

    public static function addmetabox_notifyurl() {
        add_meta_box('BackInStockNotifier::notifyurl_metabox', 'Back In Stock Notify List', 'BackInStockNotifier::notifyurl_metabox', 'product', 'side', 'default');
    }

    public static function add_variable_meta() {
        global $product;
        $product = get_product(get_the_ID());
        $looking_variable = array('variable', 'variable-subscription');
        if (in_array($product->product_type, $looking_variable)) { // display only on variable product page
            add_meta_box('variation_stock', 'Back In Stock Notify List for Variations', 'BackInStockNotifier::notify_variable', 'product', 'side', 'default');
        }
    }

    public static function notify_variable() {
        global $post_id;
        global $product;
        $product = get_product(get_the_ID());

        $variations = $product->get_available_variations(); // will work only on variable product

        echo "<div class='email_list_metabox'>";
        foreach ($variations as $each_variations) {
            $variableid[] = $each_variations['variation_id'];
            echo '<b>Manually Add Mail for #' . $each_variations['variation_id'] . '</b>';
            if (get_option('bis_error_msg_position') == 'above') {
                echo "<div class='outofstock_error_msg'></div>";
            }
            if (get_option('bis_success_msg_position') == 'above') {
//mail_send_notification_'.$user_id.'_'.$pro_id_subs.'_'
                echo "<div class='outofstock_success_msg'></div>";
            }
            echo "<input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail_" . $each_variations['variation_id'] . "_" . $product->id . "' name = 'subscribersemail' placeholder = 'Your Email Address' value = ''/><span class='notifyme  button-primary button-small' data-productid='" . $product->id . "' data-variationid='" . $each_variations['variation_id'] . "'/>";
            _e('Add', 'backinstocknotifier');
            echo "</span>";
            $variable_mail = get_post_meta($post_id, "notification_email_list_" . $each_variations['variation_id'] . "");
            echo '<h4>';
            _e('Email List for Variation id', 'backinstocknotifier');
            echo '#' . $each_variations['variation_id'] . '</h4>';
            if (!empty($variable_mail)) {
                echo '<h5>' . count($variable_mail[0]) . ' - Subscribers</h5>';
            } else {
                echo '<h5> 0 - Subscribers</h5>';
            }

//If values are present show the button
            if (!empty($variable_mail) || !empty($variable_mail[0])) {
                echo "<span class='bis_checkall button button-secondary button-large' data-variationid='" . $each_variations['variation_id'] . "'>";
                _e('Select All', 'backinstocknotifier');
                echo "</span><span class='bis_uncheckall button button-secondary button-large' data-variationid='" . $each_variations['variation_id'] . "'>";
                _e('Deselect All', 'backinstocknotifier');
                echo "</span>";
                echo "</span><span class='bis_mail_send button button-primary button-large' data-sendnowproid='" . $post_id . "' data-sendnowvarid='" . $each_variations['variation_id'] . "'>";
                _e('Send Notifications Now', 'backinstocknotifier');
                echo "</span>";
                echo '<h5>';
                _e('Only Selected Email IDs will recieve In Stock Notification Email', 'backinstocknotifier');
                echo '</h5>';
            }


            if (is_array($variable_mail)) {
                foreach ($variable_mail as $eachvar) {
                    if (is_array($eachvar)) {
                        foreach ($eachvar as $key => $eachlist) {
                            $mail_sending_opt = checked(get_post_meta($post_id, 'mailsending_op_' . $each_variations['variation_id'] . '_' . $key, true), '1', false);
                            if (!empty($eachlist)) {
                                if (isset($eachlist['mail'])) {
                                    echo "<p><input class='mail_send_op" . $each_variations['variation_id'] . "' type='checkbox' name='mailsending_op_" . $each_variations['variation_id'] . "_" . $key . "' value='1'" . $mail_sending_opt . "><span class='delete' id=" . $eachlist['mail'] . " data-lang=" . $eachlist['lang'] . " data-productid=" . $post_id . " data-variationid=" . $each_variations['variation_id'] . "><a href=mailto:" . $eachlist['mail'] . " class='sendamail' > " . $eachlist['mail'] . " </a> <a class='delete1'> &#10006; </a></span></p>";
                                } else {//old data
                                    echo "<p><input class='mail_send_op" . $each_variations['variation_id'] . "' type='checkbox' name='mailsending_op_" . $each_variations['variation_id'] . "_" . $key . "' value='1'" . $mail_sending_opt . "><span class='delete' id=" . $eachlist . " data-productid=" . $post_id . " data-variationid=" . $each_variations['variation_id'] . "><a href=mailto:" . $eachlist . " class='sendamail' > $eachlist </a> <a class='delete1'> &#10006; </a></span></p>";
                                }
                            }
                        }
                    }
                }
                if (empty($variable_mail) || empty($variable_mail[0])) {
                    echo "<h5>No Email Subscribers from list</h5>";
                }
            }
            echo '<br/>';
        }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {

                jQuery(document).on('click', '.notifyme', function () {
                    //alert("You Clicked Subscription");
                    var productid = jQuery(this).data('productid');
                    var variationid = jQuery(this).data('variationid');//alert(variationid);
                    var notifyaddress = jQuery('.subscribersemail_' + variationid + '_' + productid).val();
                    // var langcode = "not_active";
        <?php
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// check for plugin using plugin name
        $plugin = "wpml-string-translation/plugin.php";
        if (is_plugin_active($plugin)) {
//plugin is activated
            $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
            echo 'var lang_code = "' . $currentuser_lang . '";';
        } else {
            echo 'var lang_code = "not_active";';
        }
        ?>

                    if (notifyaddress === '') {
                        // alert('Email Field Should Not Be Left Blank');
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }
                    if (IsEmail(notifyaddress) === false) {
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }

                    var data = ({
                        action: 'updatemetabox',
                        notifyaddress: notifyaddress,
                        productid: productid,
                        variationid: variationid,
                        lang: lang_code


                    });
                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                            function (response) {
                                //alert(response);
                                jQuery('.subscribersemail_' + variationid + '_' + productid).val("");

                                if (response == "mailexist") {
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                                        jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option("instock_already_subscribed")); ?>");
                                        jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                                        jQuery('.outofstock_error_msg').fadeIn();
                                        jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option("instock_already_subscribed")); ?>");
                                        jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                                        jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                                } else {
        <?php if ('formtop' == get_option('bis_success_msg_position')) { ?>
                                        jQuery('.notifymeptag').css('display', 'none');
        <?php } ?>
        <?php if ('static' == get_option('bis_success_msg_effect')) { ?>
                                        jQuery('.outofstock_success_msg').text("<?php echo addslashes(get_option('instock_subscribe_email_success')); ?>");
                                        jQuery('.outofstock_error_msg').text("");
        <?php } else { ?>
                                        jQuery('.outofstock_success_msg').fadeIn();
                                        jQuery('.outofstock_success_msg').text("<?php echo addslashes(get_option('instock_subscribe_email_success')); ?>");
                                        jQuery('.outofstock_success_msg').fadeOut(<?php echo get_option('instock_sucess_fadein_time') * 1000; ?>);
                                        jQuery('.outofstock_error_msg').text("");
        <?php } ?>
                                }

                                console.log(response);

                            });

                    return false;

                });
                function IsEmail(email) {
                    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    if (!regex.test(email)) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }

            });        </script>
        <?php
        echo "</div>";
    }

    public static function checkindashboard() {
        ?>
        <script type="text/javascript">
                    jQuery(document).ready(function () {

                jQuery('.bis_checkall').click(function (event) {
                    event.preventDefault();
                    var variation_id = jQuery(this).attr('data-variationid');
                    if (typeof variation_id !== 'undefined' && variation_id !== false) {
                        jQuery('.mail_send_op' + variation_id).each(function () {
                            this.checked = true;
                        });
                    }
                    else {
                        jQuery('.mail_send_op').each(function () {
                            this.checked = true;
                        });
                    }
                });
                jQuery('.bis_uncheckall').click(function (event) {
                    event.preventDefault();
                    var variation_id = jQuery(this).attr('data-variationid');
                    if (typeof variation_id !== 'undefined' && variation_id !== false) {
                        jQuery('.mail_send_op' + variation_id).each(function () {
                            this.checked = false;
                        });
                    }
                    else {
                        jQuery('.mail_send_op').each(function () {
                            this.checked = false;
                        });
                    }
                });

                jQuery('.email_list_metabox span.delete').on("click", onclicklink).on("click", ".sendamail",
                        function (e) {
                            e.stopPropagation();
                        });
                function onclicklink() {
                    var attrclass = jQuery(this).attr('id');
                    var productid = jQuery(this).attr('data-productid');
                    var attr = jQuery(this).attr('data-variationid');
                    if (typeof attr !== 'undefined' && attr !== false) {
                        var variation_id = jQuery(this).attr('data-variationid');
                        var data = ({
                            action: 'emaillistremoval',
                            attrclass: attrclass,
                            productid: productid,
                            variationid: variation_id, //for variation level
                        });
                        // console.log(variation_id.length);
                    }
                    else {
                        var data = ({
                            action: 'emaillistremoval',
                            attrclass: attrclass,
                            productid: productid, //for product level
                        });
                    }
                    jQuery(this).css('display', 'none');
                    jQuery(this).parent().css('display', 'none');
                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                            function (response) {
                            });
                    return false;
                }
            });
        </script>

        <style type="text/css">
            .email_list_metabox .delete1 {
                background: #000000;
                background-repeat: no-repeat;
                border-radius: 100%;
                color:#fff;
                padding-left:5px;
                padding-right: 5px;
                cursor:pointer;
                position:absolute;
                right:24px;
            }
            .email_list_metabox span.bis_checkall{
                margin-right: 10px;
            }
        </style>
        <?php
    }

    public static function admin_ajax_request() {
        if ($_POST['attrclass']) {
            if ($_POST['variationid']) {//for variations level
                $arr = get_post_meta($_POST['productid'], 'notification_email_list_' . $_POST['variationid'] . '', true);

                foreach ($arr as $key => $newarr) {
                    if (isset($newarr['mail'])) {
                        if (in_array($_POST['attrclass'], $newarr)) {
                            $key_to_delete = $key;
                            unset($arr[$key_to_delete]); //key to delete applicable for list too
                            update_post_meta($_POST['productid'], 'notification_email_list_' . $_POST['variationid'] . '', $arr);
                            delete_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $key_to_delete);
                        }
                    } else {
                        if (strcmp($_POST['attrclass'], $newarr) == 0) {
                            $key_to_delete = $key;
                            $newarr = array_diff($arr, array($_POST['attrclass']));
                            update_post_meta($_POST['productid'], 'notification_email_list_' . $_POST['variationid'] . '', $arr);
                            delete_post_meta($_POST['productid'], 'mailsending_op_' . $_POST['variationid'] . '_' . $key_to_delete);
                        }
                    }
                }
            } else {//for product level
                $arr = get_post_meta($_POST['productid'], 'notification_email_list', true);

                foreach ($arr as $key => $newarr) {
                    if (isset($newarr['mail'])) {
                        if (in_array($_POST['attrclass'], $newarr)) {
                            $key_to_delete = $key;
                            unset($arr[$key_to_delete]); //key to delete applicable for list too
                            update_post_meta($_POST['productid'], 'notification_email_list', $arr);
                            delete_post_meta($_POST['productid'], 'mailsending_op_' . $key_to_delete);
                        }
                    } else {
                        if (strcmp($_POST['attrclass'], $newarr) == 0) {
                            $key_to_delete = $key;
                            unset($arr[$key_to_delete]); //key to delete applicable for list too
                            update_post_meta($_POST['productid'], 'notification_email_list', $arr);
                            delete_post_meta($_POST['productid'], 'mailsending_op_' . $key_to_delete);
                        }
                    }
                }
            }
        }
        exit();
    }

    public static function notifyurl_metabox() {
        global $post_id;
        global $product; //var_dump($post_id);
        $looking_variable = array('variable', 'variable-subscription');


        $variable = get_post_meta($post_id, 'notification_email_list');
        echo "<div class='email_list_metabox'>";
        if ($product->product_type == 'simple') {
            echo '<b>Manually Add Mail</b>';
            if (get_option('bis_error_msg_position') == 'above') {
                echo "<div class='outofstock_error_msg'></div>";
            }
            if (get_option('bis_success_msg_position') == 'above') {

                echo "<div class='outofstock_success_msg'></div>";
            }


            echo "<input type = 'text' id = 'backinstock_textbox' class = 'subscribersemail' name = 'subscribersemail' placeholder = 'Your Email Address' value = ''/><span class='notifyme  button-primary button-small'/>";
            _e('Add', 'backinstocknotifier');
            echo "</span>";
        }
        if (in_array($product->product_type, $looking_variable)) {// for variation products
            if (!empty($variable)) {
                echo "<h4>" . count($variable[0]) . " - Subscribers at Product level</h4>";
            } else {
                echo "<h4> 0 - Subscribers at Product level</h4>";
            }
        } else {
            if (!empty($variable)) {
                echo "<h4>" . count($variable[0]) . " - Subscribers</h4>";
            } else {
                echo "<h4> 0 - Subscribers</h4>";
            }
        }

        if (!empty($variable) && !empty($variable[0])) {
            echo "<span class='bis_checkall button button-secondary button-large'>";
            _e('Select All', 'backinstocknotifier');
            echo "</span><span class='bis_uncheckall button button-secondary button-large'>";
            _e('Deselect All', 'backinstocknotifier');
            echo "</span>";
            echo "<span class='bis_mail_send button button-primary button-large' data-sendnowproid='" . $post_id . "'>";
            _e('Send Notifications Now', 'backinstocknotifier');
            echo "</span>";
            echo '<h5>';
            _e('Only Selected Email IDs will recieve In Stock Notification Email', 'backinstocknotifier');
            echo '</h5>';
        }
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery(document).on('click', '.notifyme', function () {
                    //alert("You Clicked Subscription");
                    var notifyaddress = jQuery('.subscribersemail').val();
                    // var langcode = "not_active";
                    var variationid = '';
        <?php
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

// check for plugin using plugin name
        $plugin = "wpml-string-translation/plugin.php";
        if (is_plugin_active($plugin)) {
//plugin is activated
            $currentuser_lang = isset($_SESSION['wpml_globalcart_language']) ? $_SESSION['wpml_globalcart_language'] : ICL_LANGUAGE_CODE;
            echo 'var lang_code = "' . $currentuser_lang . '";';
        } else {
            echo 'var lang_code = "not_active";';
        }
        ?>
                    if (notifyaddress === '') {
                        // alert('Email Field Should Not Be Left Blank');
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_empty_email')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }
                    if (IsEmail(notifyaddress) === false) {
        <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_success_msg').text("");
        <?php } else { ?>
                            jQuery('.outofstock_error_msg').fadeIn();
                            jQuery('.outofstock_error_msg').text("<?php echo addslashes(get_option('instock_error_email_format')); ?>");
                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                            jQuery('.outofstock_success_msg').text("");
        <?php } ?>

                        return false;
                    }
        <?php if ($product->product_type == 'simple') { ?>
                        var data = ({
                            action: 'updatemetabox',
                            notifyaddress: notifyaddress,
                            productid: <?php echo $product->id; ?>,
                            variationid: variationid,
                            lang: lang_code

                        });
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                                function (response) {
                                    //alert(response);
                                    jQuery('.subscribersemail').val("");

                                    if (response == "mailexist") {
            <?php if ('static' == get_option('bis_error_msg_effect')) { ?>
                                            jQuery('.outofstock_error_msg').text("<?php echo get_option("instock_already_subscribed"); ?>");
                                            jQuery('.outofstock_success_msg').text("");
            <?php } else { ?>
                                            jQuery('.outofstock_error_msg').fadeIn();
                                            jQuery('.outofstock_error_msg').text("<?php echo get_option("instock_already_subscribed"); ?>");
                                            jQuery('.outofstock_error_msg').fadeOut(<?php echo get_option('instock_error_fadein_time') * 200; ?>);
                                            jQuery('.outofstock_success_msg').text("");
            <?php } ?>

                                    } else {
            <?php if ('formtop' == get_option('bis_success_msg_position')) { ?>
                                            jQuery('.notifymeptag').css('display', 'none');
            <?php } ?>
            <?php if ('static' == get_option('bis_success_msg_effect')) { ?>
                                            jQuery('.outofstock_success_msg').text("<?php echo get_option('instock_subscribe_email_success'); ?>");
                                            jQuery('.outofstock_error_msg').text("");
            <?php } else { ?>
                                            jQuery('.outofstock_success_msg').fadeIn();
                                            jQuery('.outofstock_success_msg').text("<?php echo get_option('instock_subscribe_email_success'); ?>");
                                            jQuery('.outofstock_success_msg').fadeOut(<?php echo get_option('instock_sucess_fadein_time') * 1000; ?>);
                                            jQuery('.outofstock_error_msg').text("");
            <?php } ?>
                                    }

                                    console.log(response);

                                });
                        return false;
        <?php } ?>
                });
                function IsEmail(email) {
                    var regex = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                    if (!regex.test(email)) {
                        return false;
                    } else {
                        return true;
                    }
                }
            }

            });        </script>
        <?php
        if (is_array($variable)) {
            foreach ($variable as $values) {
                if (is_array($values)) {
                    foreach ($values as $key => $value) {
                        $mail_sending_opt = checked(get_post_meta($post_id, 'mailsending_op_' . $key, true), '1', false);
                        if (isset($value['mail'])) {
                            echo "<p><input class='mail_send_op' type='checkbox' name='mailsending_op_" . $key . "' value='1'" . $mail_sending_opt . "><span class='delete' id=" . $value['mail'] . " data-lang=" . $value['lang'] . " data-productid=" . $post_id . "><a href=mailto:" . $value['mail'] . " class='sendamail' > " . $value['mail'] . " </a> <a class='delete1'> &#10006;</a> </label></span></p>";
                        } else {
                            echo "<p><input class='mail_send_op' type='checkbox' name='mailsending_op_" . $key . "' value='1'" . $mail_sending_opt . "><span class='delete' id=" . $value . " data-productid=" . $post_id . "><a href=mailto:" . $value . " class='sendamail' > $value </a> <a class='delete1'> &#10006;</a> </label></span></p>";
                        }

// var_dump(get_post_meta($post_id, 'mailsending_op_' . $key, true));
//  var_dump(get_post_meta($post_id, 'mailsending_op_2', true));
                    }
                }
            }
            if (empty($variable) || empty($variable[0])) {
                echo "<h5>No Email Subscribers from list</h5>";
            }
        }
        echo "</div>";
    }

    public static function get_current_count_subscribers($productid) {
        if (function_exists('get_product')) {
            $checkproducttype = get_product($productid);
            $listofemailids = array();
            if ($checkproducttype->is_type('simple')) {
                if (is_array(get_post_meta($productid, 'notification_email_list', true))) {
                    foreach (get_post_meta($productid, 'notification_email_list', true) as $value) {
                        $listofemailids[] = $value['mail'];
                    }
                }
                update_post_meta($productid, 'notification_email_list_count', count($listofemailids));
                return count($listofemailids);
            } else {
                if ($checkproducttype->is_type('variable') || ($checkproducttype->is_type('variable-subscription'))) {
                    $mailids = array();
                    $new = $checkproducttype->get_available_variations();
                    foreach ($new as $mainnew) {
                        $variationsubscribercounts = get_post_meta($productid, 'notification_email_list_' . $mainnew['variation_id'] . "", true);
                        if (is_array($variationsubscribercounts)) {
                            foreach ($variationsubscribercounts as $eachcount) {
                                $mailids[] = $eachcount['mail'];
                            }
                        }
                    }
                }
                update_post_meta($productid, 'notification_email_list_count', count($mailids));
                return count($mailids);
            }
        }
    }

    public static function add_meta_data() {
        global $post;
//        echo "<pre>";
//        var_dump(get_post_meta($post->ID, 'notification_email_list', true));
//        echo "</pre>";
//        $listofmailids = array();
//        foreach (get_post_meta($post->ID, 'notification_email_list', true) as $value) {
//            $listofmailids[] = $value['mail'];
//        }
//        echo count($listofmailids);
        $variable = get_product($post->ID);
        $mailids = array();
        $new = $variable->get_available_variations();
        foreach ($new as $mainnew) {
            $variationsubscribercounts = get_post_meta($post->ID, 'notification_email_list_' . $mainnew['variation_id'] . "", true);
            if (is_array($variationsubscribercounts)) {
                foreach ($variationsubscribercounts as $eachcount) {
                    $mailids[] = $eachcount['mail'];
                }
            }
        }
        var_dump(count($mailids));
    }

    public static function add_meta_key_by_default($post_id) {
        $post = get_post($post_id);
        if ($post->post_type == 'product') {
            global $product;

            if (metadata_exists('post', $post_id, 'notification_email_list') == '1') {
                $getlist = get_post_meta($post_id, 'notification_email_list', true);
                foreach ($getlist as $list) {
                    $arrayvalue = count($list['mail']);
                    update_post_meta($post_id, 'notification_email_list_count', $arrayvalue);
                }
            } else {
                update_post_meta($post_id, 'notification_email_list_count', 0);
            }
        }
    }

    public static function array_search_multi_two_val($needle, $needle2, $haystack) {
        foreach ($haystack as $key => $value) {
            //previous notification mail should not affect for same product and same person
            if (in_array($needle, $value) && in_array($needle2, $value) && !isset($haystack[$key]['notification_time'])) {
                return $key;
            }
        }
        return false;
    }

    public static function bis_mail_on_meta_update($post_id, $post) {
        $post = get_post($post_id);
        global $wpdb;
        global $woocommerce;

        $translated_subject = get_option('instock_email_subject');
        $translated_message = get_option('instock_email_messages');

        if ($post->post_type == 'product') {
            if ('on' == get_option('bis_automatic_noti_mail')) { // if automatic mail is on then, proceed
                $header_color = get_option('bis_email_header_color');
                $header_text_color = get_option('bis_email_header_text_color');
                $body_color = get_option('bis_email_body_color');
                $body_text_color = get_option('bis_email_body_text_color');
// file_put_contents(plugin_dir_path(__FILE__) . "test.txt", "ggg");
//    if ($meta_key == '_stock_status' || $meta_key == '_stock') {

                $product = get_product($post_id);
                if ($product->is_in_stock()) {
                    $newwishlist = get_post_meta($post_id, 'notification_email_list');

                    if (metadata_exists('post', $post_id, 'notification_email_list') == '1') {
                        foreach ($newwishlist as $key) {
//                            $newcount = count($key);
//                            $i = 1;
                            foreach ($key as $send_key => $newkey) {
// var_dump(get_post_meta($post_id, 'mailsending_op_' . $key, true));
                                if (get_post_meta($post_id, 'mailsending_op_' . $send_key, true) == '1') {  //get_post_meta($post_id, 'mailsending_op_' . $send_key, true) == '1'
                                    if (isset($newkey['mail'])) {
                                        $to = $newkey['mail']; //var_dump($to);
// note the comma
                                        $subscribe_lang = $newkey['lang'];
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
                                    } else {//old data, go normal
                                        $to = $newkey;
                                        $translated_subject = get_option('instock_email_subject');
                                        $translated_message = get_option('instock_email_messages');
                                    }

                                    if (!$translated_subject) {
                                        $translated_subject = get_option('instock_email_subject');
                                    }
                                    if (!$translated_message) {
                                        $translated_message = get_option('instock_email_messages');
                                    }
                                    if (strchr(getting_permalink($post_id), "?")) {
                                        $cart_url = add_query_arg('add-to-cart', $post_id, getting_permalink($post_id));
                                    } else {
                                        $cart_url = add_query_arg('?add-to-cart', $post_id, getting_permalink($post_id));
                                    }
                                    //$cart_url = add_query_arg('?add-to-cart',$post_id,get_post_permalink());
                                    $subject = str_replace('[product_url]', get_permalink($post_id), str_replace('[product_title]', get_the_title($post_id), str_replace('[site_title]', get_option('blogname'), $translated_subject)));
                                    $instock = str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>", \str_replace('[cart_url]', "<a href=" . $cart_url . ">" . $cart_url . "</a>", str_replace('[product_title]', get_the_title($post_id), str_replace('[site_title]', get_option('blogname'), str_replace('[productinfo]', '', $translated_message)))));
                                    $message = $instock;

//$woocommerce_email_footer_text = str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>");
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

                                                self::unset_email_id_from_list_simple($post_id, $to);
                                            }
                                        } elseif ('wp_mail' == get_option('bis_mail_function')) {
                                            if (wp_mail($to, $subject, $woo_temp_msg, $headers)) {
                                                $master_list = get_option('bis_master_list');
                                                $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                                $current_time = current_time('timestamp');
                                                $master_list[$key]['notification_time'] = $current_time;
                                                update_option('bis_master_list', $master_list);
                                                self::unset_email_id_from_list_simple($post_id, $to);
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
                                        self::unset_email_id_from_list_simple($post_id, $to);
                                    }
                                }// Checking for sending mail to selected ends here
                            }
                        }
                    }
                }

// seperate mail function for variations
//  check for variable product only
                if (($product->product_type == 'variable') || ($product->product_type == 'variable-subscription')) {
//file_put_contents(plugin_dir_path(__FILE__) . "test.txt", "ok");
                    $variations = $product->get_available_variations();

                    foreach ($variations as $each_variations) {
                        $variartion_list = get_post_meta($post_id, "notification_email_list_" . $each_variations['variation_id'] . "");
                        if ($each_variations['is_in_stock']) {

                            if (metadata_exists('post', $post_id, "notification_email_list_" . $each_variations['variation_id'] . "") == '1') {

                                foreach ($variartion_list as $mail_list) {


// cloning the above mail
                                    $newcount = count($mail_list);
                                    $i = 0;
                                    foreach ($mail_list as $send_key => $newkey) {
                                        if (get_post_meta($post_id, 'mailsending_op_' . $each_variations['variation_id'] . '_' . $send_key, true) == '1') {
                                            if (isset($newkey['mail'])) {
                                                $to = $newkey['mail']; // note the comma
                                                $subscribe_lang = $newkey['lang'];
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
                                            } else {//old data, so use normal
                                                $to = $newkey;
                                                $translated_subject = get_option('instock_email_subject');
                                                $translated_message = get_option('instock_email_messages');
                                            }

                                            if (!$translated_subject) {
                                                $translated_subject = get_option('instock_email_subject');
                                            }
                                            if (!$translated_message) {
                                                $translated_message = get_option('instock_email_messages');
                                            }
                                            if (strchr(getting_permalink($post_id), "?")) {
                                                $cart_url = add_query_arg('add-to-cart', $post_id, getting_permalink($post_id));
                                            } else {
                                                $cart_url = add_query_arg('?add-to-cart', $post_id, getting_permalink($post_id));
                                            }
                                            // $cart_url = add_query_arg('?add-to-cart',$post_id,get_post_permalink());
                                            $return_attribute_slug = get_attribute_slug($each_variations['variation_id']);
                                            $link = implode("&", $return_attribute_slug);
                                            $subject = str_replace('[product_url]', get_permalink($each_variations['variation_id']), str_replace('[product_title]', get_the_title($each_variations['variation_id']), str_replace('[site_title]', get_option('blogname'), $translated_subject)));
                                            $instock = str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>", \str_replace('[cart_url]', "<a href=" . $cart_url . "&variation_id=" . $each_variations['variation_id'] . "&" . $link . ">" . $cart_url . "&variation_id=" . $each_variations['variation_id'] . "&" . $link . "</a>", str_replace('[product_title]', get_the_title($each_variations['variation_id']), str_replace('[site_title]', get_option('blogname'), str_replace('[productinfo]', '', $translated_message)))));
                                            $message = $instock;
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
                                                        self::unset_email_id_from_list_variable($post_id, $each_variations['variation_id'], $to);
                                                    }
                                                } elseif ('wp_mail' == get_option('bis_mail_function')) {
                                                    if (wp_mail($to, $subject, $woo_temp_msg, $headers)) {
                                                        $master_list = get_option('bis_master_list');
                                                        $key = self::array_search_multi_two_val($to, $post_id, $master_list);
                                                        $current_time = current_time('timestamp');
                                                        $master_list[$key]['notification_time'] = $current_time;
                                                        update_option('bis_master_list', $master_list);
                                                        self::unset_email_id_from_list_variable($post_id, $each_variations['variation_id'], $to);
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

                                                self::unset_email_id_from_list_variable($post_id, $each_variations['variation_id'], $to);
                                            }
                                        }// Checking for sending mail to selected ends here
                                        //      if (get_option('backinstock_delete_email_id') == 'yes') {
//                                        if (++$i === $newcount) {
//
//                                            // delete_post_meta($post_id, "notification_email_list_" . $each_variations['variation_id'] . "");
//                                        }
//                                        delete_post_meta($post_id, 'mailsending_op_' . $each_variations['variation_id'] . '_' . $send_key);
//       }
                                    }
                                }
                            }
                        }
                    }
                }
            }
// }
        }
    }

    public static function unset_email_id_from_list_simple($post_id, $to) {
        $updated_list = get_post_meta($post_id, 'notification_email_list', true);
        $key_to_delete = backinstocknotifier_searchmailid($to, $updated_list); // $mail_id -> current mail in interation
        //var_dump($key_to_delete);
        if (($key_to_delete != null) || ($key_to_delete == 0)) {
            //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));

            if ('on' == get_option('bis_delete_uncheck_subscribers')) {
                unset($updated_list[$key_to_delete]);
            }

            update_post_meta($post_id, 'notification_email_list', $updated_list);

            delete_post_meta($post_id, 'mailsending_op_' . $key_to_delete);
        }
    }

    public static function unset_email_id_from_list_variable($post_id, $variation_id, $to) {
        $updated_list = get_post_meta($post_id, 'notification_email_list_' . $variation_id . '', true);
        $key_to_delete = backinstocknotifier_searchmailid($to, $updated_list); // $mail_id -> current mail in interation
        //var_dump($key_to_delete);
        if (($key_to_delete != null) || ($key_to_delete == 0)) {
            //var_dump(array($key_to_delete => $updated_list[$key_to_delete]));
            if ('on' == get_option('bis_delete_uncheck_subscribers')) {
                unset($updated_list[$key_to_delete]);
            }
            // var_dump($updated_list);
            // $updating_list = array_diff($updated_list, array($key_to_delete => $updated_list[$key_to_delete]));
            //var_dump($updating_list);
            update_post_meta($post_id, 'notification_email_list_' . $variation_id . '', $updated_list);
            delete_post_meta($post_id, 'mailsending_op_' . $variation_id . '_' . $key_to_delete);
        }
    }

    public static function settings_tab_instock_mailer($settings_tabs) {
        $settings_tabs['instockmailer'] = __('Back In Stock Notifier', 'backinstocknotifier');

        return $settings_tabs;
    }

//    public static function add_shortcode_blogname() {
//        return get_option('blogname');
//    }
//
//    public static function add_shortcode_productname() {
//        return get_the_title();
//    }
//
//    public static function add_shortcode_producturl() {
//        return "<a href=" . get_permalink() . " target='_blank'>" . get_permalink() . "</a>";
//    }

    public static function instock_mailer_admin_settings1() {

        global $woocommerce;
        $product_field_type_ids = get_option('bis_hide_products');
        $product_ids = !empty($product_field_type_ids) ? array_map('absint', (array) $product_field_type_ids) : null;
        if ($product_ids) {
            foreach ($product_ids as $product_id) {
                $rsproductids[] = $product_id;
                $productobject = new WC_Product($product_id);
                $rsproduct_name[] = $productobject->get_formatted_name($rsproductids);
            }
        }
        @$ajaxproductsearch = array_combine((array) $rsproductids, (array) $rsproduct_name);


        $listcategories = get_terms('product_cat');
        // var_dump($listcategories);
        if (is_array($listcategories)) {
            foreach ($listcategories as $category) {
                $categoryname[] = $category->name;
                $categoryid[] = $category->term_id;
            }
        }
        //  if (is_array(($categoryid) && ($categoryname))) {
        @$categorylist = array_combine((array) $categoryid, (array) $categoryname);



        return apply_filters('woocommerce_instock_mailer_settings', array(
            array(
                'name' => __('Back In Stock Notifier', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_mailer'
            ),
            array(
                'name' => __('[product_title] - To display the title of the subscribed product', 'backinstocknotifier'),
                'type' => 'title',
            ),
            array(
                'name' => __('[product_url] - To display the subscribed product page url', 'backinstocknotifier'),
                'type' => 'title',
            ),
            array(
                'name' => __('[cart_url] - To display the subscribed product cart link url', 'backinstocknotifier'),
                'type' => 'title',
            ),
//            array(
//                'name' => __('Clear Back In Stock Notify List After Back In Stock Notification', 'backinstocknotifier'),
//                'desc' => __('(in Single Product Page)'),
//                'id' => 'backinstock_delete_email_id',
//                'std' => 'yes',
//                'default' => 'yes',
//                'type' => 'checkbox',
//                'newids' => 'backinstock_delete_email_id',
//            ),
            array(
                'name' => __('Subscribe Form Title', 'backinstocknotifier'),
                'desc' => __('Please enter info for email subscription', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_get_notified_form_caption',
                'css' => 'min-width:500px',
                'std' => "(E-mail when Stock is available)",
                'type' => 'text',
                'newids' => 'instock_get_notified_form_caption',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Get Notified Text Box Placeholder Text', 'backinstocknotifier'),
                'desc' => __('Please enter the text for the get notified placeholder text box', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_get_notified_placeholder_text',
                'css' => 'min-width:500px',
                'std' => 'Your Email Address',
                'type' => 'text',
                'newids' => 'instock_get_notified_placeholder_text',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Show Button in new line', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'backinstock_notify_button_line',
                'std' => 'no',
                'default' => 'no',
                'type' => 'checkbox',
                'newids' => 'backinstock_notify_button_line',
            ),
            array(
                'name' => __('Get Notified Button Label', 'backinstocknotifier'),
                'desc' => __('Please enter the label for the get notified button', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_get_notified_button_label',
                'css' => 'min-width:500px',
                'std' => 'Get Notified',
                'type' => 'text',
                'newids' => 'instock_get_notified_button_label',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => '_instock_mailer'),
            array(
                'name' => __('Subscribe form Show/Hide Setting', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_subs_form_showhide'
            ),
            array(
                'name' => __('Show Subscribe form for Guest', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'bis_show_form_guest',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'checkbox',
                'newids' => 'bis_show_form_guest',
            ),
            array(
                'name' => __('Show Subscribe form for Members', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'bis_show_form_member',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'checkbox',
                'newids' => 'bis_show_form_member',
            ),
            array(
                'name' => __('Hide Subscribe form for selected Products', 'backinstocknotifier'),
                'desc' => __('', 'backinstocknotifier'),
                'id' => 'bis_hide_products',
                'css' => 'min-width:150px;',
                'std' => array(),
                'class' => 'bis_hide_products ajax_chosen_select_products_bis',
                'default' => array(),
                'newids' => 'bis_hide_products',
                'type' => 'bis_hide_products',
            ),
            array(
                'name' => __('Hide Subscribe form for selected Products Categories', 'backinstocknotifier'),
                'desc' => __('', 'backinstocknotifier'),
                'id' => 'bis_hide_products_cat',
                'css' => 'min-width:150px;',
                'std' => array(),
                'class' => 'bis_hide_products_cat',
                'default' => array(),
                'newids' => 'bis_hide_products_cat',
                'type' => 'multiselect',
                'options' => $categorylist,
            ),
            array('type' => 'sectionend', 'id' => '_instock_subs_form_showhide'),
            array(
                'name' => __('Error Message Setting', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_error_msg'
            ),
            array(
                'name' => __('Email Empty Field Error Message', 'backinstocknotifier'),
                'desc' => __('Please enter error message if email id is empty', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_error_empty_email',
                'css' => 'min-width:500px',
                'std' => 'Email Address can\'t be empty',
                'type' => 'text',
                'newids' => 'instock_error_empty_email',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Email Field Format Error Message', 'backinstocknotifier'),
                'desc' => __('Please enter error message if email id is not in valid format', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_error_email_format',
                'css' => 'min-width:500px',
                'std' => 'Please enter valid Email Address',
                'type' => 'text',
                'newids' => 'instock_error_email_format',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Already Subscribed Message', 'backinstocknotifier'),
                'desc' => __('Please enter Message to show if the user is already subscribed', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_already_subscribed',
                'css' => 'min-width:500px',
                'std' => 'You have already Subscribed',
                'type' => 'text',
                'newids' => 'instock_already_subscribed',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Error Message Position', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_error_msg_position',
                'class' => '',
                'std' => 'above',
                'default' => 'above',
                'newids' => 'bis_error_msg_position',
                'options' => array('above' => __('Above Form', 'backinstocknotifier'), 'below' => __('Below Form', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Error Message Effect', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_error_msg_effect',
                'class' => '',
                'std' => 'fadein',
                'default' => 'fadein',
                'newids' => 'bis_error_msg_effect',
                'options' => array('static' => __('Static', 'backinstocknotifier'), 'fadein' => __('Fade Out', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Error Message Fade Out Time', 'backinstocknotifier'),
                'desc' => __('Please enter time in seconds for which the message should Fade Out', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_error_fadein_time',
                'css' => 'min-width:500px',
                'std' => '5',
                'type' => 'text',
                'newids' => 'instock_error_fadein_time',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Error Message Font Color', 'backinstocknotifier'),
                'desc' => __('Please Choose Font color', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_error_font_color',
                'css' => 'max-width:500px',
                'std' => '#C12B2B',
                'class' => 'iriscolor',
                'type' => 'text',
                'newids' => 'instock_error_font_color',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Error Message Font Style', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_error_msg_style',
                'class' => '',
                'std' => 'normal',
                'default' => 'normal',
                'newids' => 'bis_error_msg_style',
                'options' => array('normal' => __('Normal', 'backinstocknotifier'), 'italic' => __('Italic', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Error Message Font Weight', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_error_msg_weight',
                'class' => '',
                'std' => 'normal',
                'default' => 'normal',
                'newids' => 'bis_error_msg_weight',
                'options' => array('normal' => __('Normal', 'backinstocknotifier'), 'bold' => __('Bold', 'backinstocknotifier'))
            ),
            array('type' => 'sectionend', 'id' => '_instock_error_msg'),
            array(
                'name' => __('Success Message Setting', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_sucess_msg'
            ),
            array(
                'name' => __('Success Subscription Message', 'backinstocknotifier'),
                'desc' => __('Please enter success message after user subscribed successfully', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_subscribe_email_success',
                'css' => 'min-width:500px',
                'std' => 'You are Subscribed to Back In Stock Notifier',
                'type' => 'text',
                'newids' => 'instock_subscribe_email_success',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Success Message Position', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_success_msg_position',
                'class' => '',
                'std' => 'above',
                'default' => 'above',
                'newids' => 'bis_success_msg_position',
                'options' => array('above' => __('Above Form', 'backinstocknotifier'), 'below' => __('Below Form', 'backinstocknotifier'), 'formtop' => __('Replace Form and Show Message', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Success Message Effect', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_success_msg_effect',
                'class' => '',
                'std' => 'fadein',
                'default' => 'fadein',
                'newids' => 'bis_success_msg_effect',
                'options' => array('static' => __('Static', 'backinstocknotifier'), 'fadein' => __('Fade Out', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Success Message Fade Out Time', 'backinstocknotifier'),
                'desc' => __('Please enter time in seconds for which the message should Fade Out', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_sucess_fadein_time',
                'css' => 'min-width:500px',
                'std' => '5',
                'type' => 'text',
                'newids' => 'instock_sucess_fadein_time',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Success Message Font Color', 'backinstocknotifier'),
                'desc' => __('Please Choose Font color', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_sucess_font_color',
                'css' => 'max-width:500px',
                'std' => '#008000',
                'class' => 'iriscolor',
                'type' => 'text',
                'newids' => 'instock_sucess_font_color',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Success Message Font Style', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_success_msg_style',
                'class' => '',
                'std' => 'normal',
                'default' => 'normal',
                'newids' => 'bis_success_msg_style',
                'options' => array('normal' => __('Normal', 'backinstocknotifier'), 'italic' => __('Italic', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Success Message Font Weight', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_success_msg_weight',
                'class' => '',
                'std' => 'normal',
                'default' => 'normal',
                'newids' => 'bis_success_msg_weight',
                'options' => array('normal' => __('Normal', 'backinstocknotifier'), 'bold' => __('Bold', 'backinstocknotifier'))
            ),
            array('type' => 'sectionend', 'id' => '_instock_sucess_msg'),
            array(
                'name' => __('Mail Settings', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_mailsettings'
            ),
            array(
                'name' => __('BackInStock Subscription Mail', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'instock_subscription_akn_mail',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'select',
                'newids' => 'instock_subscription_akn_mail',
                'options' => array('yes' => __('On', 'backinstocknotifier'), 'no' => __('Off', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Subscription Email Subject', 'backinstocknotifier'),
                'desc' => __('Please enter subject of your mail', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_subscription_email_subject',
                'css' => 'min-width:500px',
                'std' => 'Back In Stock Subscription - [site_title]',
                'type' => 'text',
                'newids' => 'instock_subscription_email_subject',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Subscription Email Message', 'backinstocknotifier'),
                'desc' => __('Enter custom email message', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_subscription_email_messages',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => 'Hi,<br>This mail is the Back In Stock Notification Subscription mail from [site_title]<br> ',
                'type' => 'textarea',
                'newids' => 'instock_subscription_email_messages',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Show Unsubscribe Link in Subscription Mail Footer', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'instock_subscription_unsubscribe_link',
                'std' => 'yes',
                'default' => 'yes',
                'type' => 'checkbox',
                'newids' => 'instock_subscription_unsubscribe_link',
            ),
// 6.4
            array(
                'name' => __('Subscription Footer Customization', 'backinstocknotifier'),
                'desc' => __('Custom Footer Line Editor', 'backinstocknotifier'),
                'id' => 'footer_line_editor',
                'css' => 'min-width:500px;min-height:200px',
                'std' => 'You have been Subscribed to [product_title] product [unsublink]',
                'type' => 'textarea',
                'newids' => 'footer_line_editor',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Unsubscribe Footer Link Caption Customization', 'backinstocknotifier'),
                'desc' => __('Custom Footer Link Caption Customization', 'backinstocknotifier'),
                'id' => 'footer_link_editor',
                'css' => 'min-width:500px',
                'std' => 'Click to Unsubscribe',
                'type' => 'text',
                'newids' => 'footer_link_editor',
                'desc_tip' => true,
            ),
            array(
                'name' => __('BackInStock Notification Mail', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_automatic_noti_mail',
                'class' => '',
                'std' => 'on',
                'default' => 'on',
                'newids' => 'bis_automatic_noti_mail',
                'options' => array('on' => __('Automatic', 'backinstocknotifier'), 'off' => __('Manual', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Delete/Un-CheckSubscribed User after Successful notification', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_delete_uncheck_subscribers',
                'class' => '',
                'std' => 'on',
                'default' => 'on',
                'newids' => 'bis_delete_uncheck_subscribers',
                'options' => array('on' => __('Delete', 'backinstocknotifier'), 'off' => __('Uncheck', 'backinstocknotifier'))
            ),
            array(
                'name' => __('In Stock Email Subject', 'backinstocknotifier'),
                'desc' => __('Please enter subject of your mail', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_email_subject',
                'css' => 'min-width:500px',
                'std' => 'Back In Stock Notification for [product_title] from [site_title]',
                'type' => 'text',
                'newids' => 'instock_email_subject',
                'desc_tip' => true,
            ),
            array(
                'name' => __('In Stock Email Message', 'backinstocknotifier'),
                'desc' => __('Enter custom email message', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_email_messages',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => 'Hi,<br>The product [product_title] on [site_title] is Back In Stock for purchase. This mail is a notification mail as you have subscribed for the Back In Stock Notification.<br> Check the [product_title] on [product_url]<br>Thanks.',
                'type' => 'textarea',
                'newids' => 'instock_email_messages',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => '_instock_mailsettings'),
            array(
                'name' => __('Advanced Settings', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_advanced'
            ),
            array(
                'name' => __('Custom CSS', 'backinstocknotifier'),
                'desc' => __('Use #backinstock_textbox for customising textbox and #backinstock_button for customising button', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_custom_css',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => '',
                'type' => 'textarea',
                'newids' => 'instock_custom_css',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Custom CSS', 'backinstocknotifier'),
                'desc' => __('Use #backinstock_textbox for customising textbox and #backinstock_button for customising button', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_custom_css',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => '',
                'type' => 'button',
                'newids' => 'instock_custom_css',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Master Table', 'backinstocknotifier'),
                'desc' => __('Use #backinstock_textbox for customising textbox and #backinstock_button for customising button', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_custom_master_table',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => '',
                'type' => 'cus_table',
                'newids' => 'instock_custom_master_table',
                'desc_tip' => true,
            ),
            array(
                'name' => __('Master Button', 'backinstocknotifier'),
                'desc' => __('Use #backinstock_textbox for customising textbox and #backinstock_button for customising button', 'backinstocknotifier'),
                'tip' => '',
                'id' => 'instock_custom_master_button',
                'css' => 'min-width:500px;min-height:200px;',
                'std' => '',
                'type' => 'button_master',
                'newids' => 'instock_custom_master_button',
                'desc_tip' => true,
            ),
            array('type' => 'sectionend', 'id' => '_instock_advanced'),
            array(
                'name' => __('Troubleshooting', 'backinstocknotifier'),
                'type' => 'title',
                'desc' => '',
                'id' => '_instock_troubleshooting'
            ),
            array(
                'name' => __('Use Mail Function', 'backinstocknotifier'),
                'type' => 'select',
                'id' => 'bis_mail_function',
                'class' => '',
                'std' => 'mail',
                'default' => 'mail',
                'newids' => 'bis_mail_function',
                'options' => array('mail' => __('mail', 'backinstocknotifier'), 'wp_mail' => __('wp_mail', 'backinstocknotifier'))
            ),
            array(
                'name' => __('Choose Hook for displaying the Subscribe Form Field', 'backinstocknotifier'),
                'desc' => __(''),
                'id' => 'show_notifyme_label',
                'std' => 'woocommerce_stock_html',
                'default' => 'no',
                'type' => 'select',
                'newids' => 'show_notifyme_label',
                'options' => array(
                    'woocommerce_stock_html' => __('woocommerce_stock_html', 'backinstocknotifier'),
                    'woocommerce_get_price_html' => __('woocommerce_get_price_html', 'backinstocknotifier')
                )
            ),
            array('type' => 'sectionend', 'id' => '_instock_troubleshooting'),
        ));
    }

    public static function add_product_selection_backward_compatibility() {

        global $woocommerce;
        //var_dump(get_option('bis_hide_products'));
        if ((float) $woocommerce->version > (float) ('2.2.0')) {
            ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="bis_hide_products"><?php _e('Hide Subscribe form for selected Products', 'backinstocknotifier'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="hidden" class="wc-product-search" style="width: 100%;" id="bis_hide_products"  name="bis_hide_products" data-placeholder="<?php _e('Search for a product&hellip;', 'backinstocknotifier'); ?>" data-action="woocommerce_json_search_products_and_variations" data-multiple="true" data-selected="<?php
                $json_ids = array();
                if (get_option('bis_hide_products') != "") {
                    $list_of_produts = get_option('bis_hide_products');
                    if (is_array($list_of_produts)) {
                        $product_ids = $list_of_produts;
                    } else {
                        $product_ids = array_filter(array_map('absint', (array) explode(',', get_option('bis_hide_products'))));
                    }


                    foreach ($product_ids as $product_id) {
                        $product = wc_get_product($product_id);
                        $json_ids[$product_id] = wp_kses_post($product->get_formatted_name());
                    } echo esc_attr(json_encode($json_ids));
                }
                ?>" value="<?php echo implode(',', array_keys($json_ids)); ?>" />
                </td>
            </tr>
                <?php } else { ?>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="bis_hide_products"><?php _e('Hide Subscribe form for selected Products', 'backinstocknotifier'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <select multiple name="bis_hide_products" style='width:500px;' id='bis_hide_products' class="bis_hide_products ajax_chosen_select_products_bis">
                    <?php
                    if ((array) get_option('bis_hide_products') != "") {
                        $list_of_produts = (array) get_option('bis_hide_products');
                        foreach ($list_of_produts as $rs_free_id) {
                            echo '<option value="' . $rs_free_id . '" ';
                            selected(1, 1);
                            echo '>' . ' #' . $rs_free_id . ' &ndash; ' . get_the_title($rs_free_id);
                        }
                    } else {
                        ?>
                            <option value=""></option>
                            <?php
                        }
                        ?>
                    </select>
                </td>
            </tr>
                        <?php
                    }
                }

                public static function add_ajax_chosen_to_that_script() {
                    global $woocommerce;
                    if (isset($_GET['tab'])) {
                        if ($_GET['tab'] == 'instockmailer') {
                            if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                                ?>
                    <script type="text/javascript">
                                jQuery(function () {
                                    // Ajax Chosen Product Selectors
                                    jQuery("select.ajax_chosen_select_products_bis").ajaxChosen({
                                        method: 'GET',
                                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                        dataType: 'json',
                                        afterTypeDelay: 100,
                                        data: {
                                            action: 'woocommerce_json_search_products_and_variations',
                                            security: '<?php echo wp_create_nonce("search-products"); ?>'
                                        }
                                    }, function (data) {
                                        var terms = {};
                                        jQuery.each(data, function (i, val) {
                                            terms[i] = val;
                                        });
                                        return terms;
                                    });
                                });
                    </script>
                    <?php
                }
            }
        }
    }

    public static function add_order_number_start_setting($settings) {
        $updated_settings = array();
        foreach ($settings as $section) {
// at the bottom of the General Options section
            if (isset($section['id']) && 'email_options' == $section['id'] &&
                    isset($section['type']) && 'sectionend' == $section['type']) {
                $updated_settings[] = array(
                    'name' => __('In Stock Email Subject', 'backinstocknotifier'),
                    'desc' => __('Please Enter Subject of your Mail', 'backinstocknotifier'),
                    'tip' => '',
                    'id' => 'instock_email_subject',
                    'css' => 'min-width:500px',
                    'std' => 'instock_email_subject',
                    'type' => 'text',
                    'newids' => '',
                    'desc_tip' => true,
                );
                $updated_settings[] = array(
                    'name' => __('In Stock Email Message', 'backinstocknotifier'),
                    'desc' => __('Enter Custom Email Message', 'backinstocknotifier'),
                    'tip' => '',
                    'id' => 'instock_email_messages',
                    'css' => 'min-width:500px;min-height:200px;',
                    'std' => 'instock_email_message',
                    'type' => 'textarea',
                    'newids' => '',
                    'desc_tip' => true,
                );
            }
            $updated_settings[] = $section;
        }
        return $updated_settings;
    }

    public static function instock_mailer_admin_settings() {
        woocommerce_admin_fields(BackInStockNotifier::instock_mailer_admin_settings1());
    }

    public static function instock_mailer_update_settings() {
        woocommerce_update_options(BackInStockNotifier::instock_mailer_admin_settings1());
    }

    public static function instock_mailer_default_settings() {
        global $woocommerce;
        foreach (BackInStockNotifier::instock_mailer_admin_settings1() as $setting)
            if (isset($setting['newids']) && ($setting['std'])) {
// var_dump($setting);
                add_option($setting['newids'], $setting['std']);
            }
        $bis_master_list = get_option('bis_master_list');
//If there is no previous entry then make it as array
        if ($bis_master_list == false || empty($bis_master_list)) {
            add_option('bis_master_list', array());
        }
    }

    public static function posts_columns($defaults) {
        $defaults['notify_list'] = __('Back In Stock Notifier List', 'backinstocknotifier');
        return $defaults;
    }

    public static function posts_custom_columns($column_name, $post_id) {
        if ($column_name === 'notify_list') {
            $getlist = self::get_current_count_subscribers($post_id);
            echo (int) $getlist;
        }
    }

    public static function posts_column_register_sortable($columns) {
        $columns['notify_list'] = 'notify_list';
        return $columns;
    }

    public static function event_column_orderby($query) {
        if (!is_admin())
            return;

        $orderby = $query->get('orderby');

        if ('notify_list' == $orderby) {
            $query->set('meta_key', 'notification_email_list_count');
            $query->set('orderby', 'meta_value_num');
        }
    }

    /* adding script in front end to check for variable level out of stock */

    public static function out_of_stock_for_variation() {
        global $post;
        global $product;
        if (function_exists('is_product')) {
            $product = get_product(get_the_ID());
            if (is_product()) { /* add the script only on product page */
                $looking_variable = array('variable', 'variable-subscription');
                if (in_array($product->product_type, $looking_variable)) {
                    if ($product->is_in_stock()) {
                        echo '<style>
                .outofstocknotifyme{
                display:none;
            }</style>';
                    }
                    $available_variations = $product->get_available_variations(); //details about variations

                    foreach ($available_variations as $attribute) {

                        if (!$attribute['is_in_stock']) {

                            echo '<script>jQuery("document").ready(function(){
       // console.log(jQuery(".variations_form").data());
        var variation = jQuery(".variations_form").data();
        jQuery(document).on("change","select",function(){';
                            /* START Statement for setting user email by default if he is logged in */
                            $current_user = get_current_user_id();
                            $user_datas = get_userdata($current_user);
                            if (!empty($user_datas)) {
                                $has_datas = "true";
                                $user_mail = $user_datas->user_email;
                            } else {
                                $has_datas = "false";
                            }
                            echo 'var prefil_email = ' . $has_datas . ';
                if (prefil_email) {
                    var user_email_id = "' . $user_mail . '";
                    jQuery("#backinstock_textbox").val(user_email_id);
                }';
                            /* END Statement for setting user email by default if he is logged in */

                            echo "var out_of_stock = '" . $attribute['variation_id'] . "';
                    var selected_id = jQuery(\"input[type='hidden'][name='variation_id']\").val();
                    if(out_of_stock==selected_id){
                    jQuery('.outofstocknotifyme').css('display','block');
                    jQuery('#bis_outofstock').css('display','block');
                    jQuery('input[name = \"out_stock_variable_id\"]').val(selected_id);
                     //console.log('rock');
                    }
               });
        });</script>";
                        }
                    }
                }
            }
        }
    }

    public static function save_mail_op($post_id) {
        $variable = get_post_meta($post_id, 'notification_email_list');
//$mail_sending_opt = get_post_meta($post_id, 'mailsending_op_' . $value, true);
        if (is_array($variable)) {
            foreach ($variable as $values) {
                if (is_array($values)) {
                    foreach ($values as $key => $value) {
                        if (isset($_POST['mailsending_op_' . $key])) {
                            update_post_meta($post_id, 'mailsending_op_' . $key, $_POST['mailsending_op_' . $key]);
                        } else {
                            update_post_meta($post_id, 'mailsending_op_' . $key, '0');
                        }
                    }
                }
            }
        }
    }

    public static function save_mail_op_variable($post_id) {
        global $post_id;
        global $product;
        if (!empty($post_id) && !is_null($post_id)) {
            $product = get_product($post_id);
            if (($product->product_type == 'variable') || ($product->product_type == 'variable-subscription')) {
                $variations = $product->get_available_variations(); // will work only on variable product

                foreach ($variations as $each_variations) {
                    $variable_mail = get_post_meta($post_id, "notification_email_list_" . $each_variations['variation_id'] . "");
                    if (is_array($variable_mail)) {
                        foreach ($variable_mail as $eachvar) {
                            foreach ($eachvar as $key => $eachlist) {
                                $variat_id = $each_variations['variation_id'];
                                if (isset($_POST['mailsending_op_' . $variat_id . '_' . $key])) {
                                    update_post_meta($post_id, 'mailsending_op_' . $variat_id . '_' . $key, $_POST['mailsending_op_' . $variat_id . '_' . $key]);
                                } else {
                                    update_post_meta($post_id, 'mailsending_op_' . $variat_id . '_' . $key, '0');
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    public static function bis_translate_file() {
        load_plugin_textdomain('backinstocknotifier', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public static function bis_unsubscribe_link($unsubscribe_link) {
        extract(shortcode_atts(array('link' => $unsubscribe_link), $unsubscribe_link));
        return $link;
    }

    public static function bis_add_csv_button() {
        $button_catption = __('Export CSV', 'backinstocknotifier');
        echo '<tr valign="top">
        <form method="post">
          <th scope="row" class="titledesc">
          <label for="bisexport">';
        _e('Export Current Email IDs from All Product Notify List as CSV', 'backinstocknotifier');
        echo '</label>
	  </th>
          <td class="forminp forminp-button">
          <input id="bis_csv" type="button" name="bisexport" value="' . $button_catption . '">
          </td>
          </form>
          </tr>';
    }

    public static function bis_add_master_list_csv_button() {
        $button_catption = __('Export CSV', 'backinstocknotifier');
        echo '<tr valign="top">
            <form method="post">
              <th scope="row" class="titledesc">
              <label for="master_bisexport">';
        _e('Export Master Log of All Subscribed Email IDs', 'backinstocknotifier');
        echo '</label>
              </th>
              <td class="forminp forminp-button">
              <input id="bis_master_csv" type="button" name="bis_master_csv" value="' . $button_catption . '">
              </td>
              </form>
              </tr>';
    }

    public static function bis_add_logo_input() {
        $button_catption = __('Upload Logo', 'backinstocknotifier');
        $mail_logo = get_option('bis_logo_mail');
        echo '<tr valign="top">
          <th scope="row" class="titledesc">
         <!-- <label for="bislogo">';
        _e('Upload Logo', 'backinstocknotifier');
        echo '</label> -->
	  </th>
          <td class="forminp forminp-button">
          <!-- <input id="bis_logo_mail" type="text" name="bis_logo_mail" value="' . $mail_logo . '"> -->
          <input id="bis_logo_upload" class="mail_logo_settings" type="button" name="bis_logo_upload" value="' . $button_catption . '">
          </td>
          </tr>';
    }

    public static function bis_master_table() {
        global $post;
        echo '<tr valign="top"><th scope="row" class="titledesc">Master Log of All Subscribed Email IDs</th><td><input type="text" id="bis_master_filter" name="bis_master_filter" placeholder="Type Here to Search Anything">';
        echo '<select id="bis_pagination">';
        for ($k = 1; $k <= 20; $k++) {

            if ($k == 10) {
                echo '<option value="' . $k . '" selected="selected">' . $k . '</option>';
            } else {
                echo '<option value="' . $k . '">' . $k . '</option>';
            }
        }
        '</select>';
        echo '<table class="bis_footable widefat table" data-page-size="10" data-filter="#bis_master_filter" data-filter-minimum="1">
	<thead>
		<tr>

                        <th class="bis_pad_l_10 small_col" data-type="numeric">S.No</th>
			<th class="bis_pad_l_10">Email Id</th>
			<th class="bis_pad_l_10">Product</th>
                        <th class="bis_pad_1_10">Date / Time</th>
                        <th class="bis_pad_1_10">Notification Mail / Date / Time</th>
                        <th class="bis_pad_1_10" data-sort-ignore="true">Send Mail</th>
                        <th class="bis_pad_1_10 bis_mas_small_col" data-sort-ignore="true"><a href="#" id="bis_mas_sel">Select All</a>&nbsp/&nbsp <a href="#" id="bis_mas_desel">Deselect All</a>&nbsp<a href="#" id="bis_mas_selected_del" class="button">Delete Selected</a></th>

		</tr>
	</thead>
        <tbody>';

        $bis_master_list = get_option('bis_master_list'); //echo '<pre>';var_dump($bis_master_list);echo '</pre>';
        $i = 1;
        if (is_array($bis_master_list)) {
            foreach ($bis_master_list as $list) {
                if (isset($list['product_id'], $list['email'], $list['time'])) {

                    $pro_id_subs = $list['product_id'];
                    $pro_obj = get_product($pro_id_subs);

                    if (isset($list['variationid'])) {
                        $variation_id_subs = $list['variationid'] != "" ? $list['variationid'] : '0';
                    } else {

                        $variation_id_subs = isset($list['variationid']) ? $list['variationid'] : $list['product_id'];
                    }
                    echo '  <tr>
  <td>';
                    echo $i;
                    echo '  </td>
         <td>';
                    echo $list['email'];
                    echo '  </td>
         <td>';
                    if ($pro_obj) {
                        if ($pro_obj->is_type('simple')) {
                            $prodtitle_subs = get_the_title($pro_id_subs);
                        } else if ($pro_obj->is_type('variable')) {
                            /* @var $list type */
                            if ($variation_id_subs != $pro_id_subs) {
                                $return_attribute_slug = get_attribute_slug($variation_id_subs);
                                $variation_attributes = implode($return_attribute_slug);
                                $prodtitle_subs = get_the_title($pro_id_subs) . ' ' . preg_replace("/attribute_/", "<br>", $variation_attributes);
                            } else {
                                $prodtitle_subs = get_the_title($pro_id_subs);
                            }
                        }
                    } else {
                        $prodtitle_subs = "<div class = 'biserrrmsg' style = color:red; > Product Info N/A </div>";
                    }
                    echo $prodtitle_subs;
                    echo '     </td>
                    <td>';
                    if (!empty($list['time'])) {
                        echo date(get_option('date_format') . '/' . get_option('time_format'), $list['time']);
                    } else {
                        echo '-';
                    }
                    echo '</td>';
                    echo '<td>';
                    if (isset($list['notification_time'])) {
                        echo 'Sent ' . date(get_option('date_format') . ' / ' . get_option('time_format'), $list['notification_time']);
                    } else {
                        echo 'Not Sent';
                    }


                 if($pro_obj){
                     echo '</td><td>
                 <input type ="button" class="manual_mail_send" value="Send Now" data-send_mail_subs = ' . $list['email'] . '  data-product_id_subs= ' . $list['product_id'] . ' data-time-info=' . $list['time'] . ' data-user-id = ' . $i . ' data-variation_id=' . $variation_id_subs . ' " /><span class="mail_send_notification_' . $i . '_' . $list['time'] . '" style="display:none;">Mail Sent Successfully </span></td>
                <td class="bis_mas_small_col">';
                 }  else {
                     echo '</td><td>
                 <input type ="button" disabled class="manual_mail_send" value="Send Now" data-send_mail_subs = ' . $list['email'] . '  data-product_id_subs= ' . $list['product_id'] . ' data-time-info=' . $list['time'] . ' data-user-id = ' . $i . ' data-variation_id=' . $variation_id_subs . ' " /><span class="mail_send_notification_' . $i . '_' . $list['time'] . '" style="display:none;">Mail Sent Successfully </span></td>
                <td class="bis_mas_small_col">';
                 }
                    
                    echo '<input type="checkbox" class="bis_mas_checkboxes" data-bismasid="' . ($i - 1) . '"/>';

                    echo '<a href="#" class="button bis_mas_check_indi" data-bistdelid="' . ($i - 1) . '">Delete this Row</a>';
                    echo '</td>
            </tr>';
                    $i++;
                } //  $testing = get_option('my_test');
            }
            $plugin = "wpml-string-translation/plugin.php";
            if (is_plugin_active($plugin)) {
//plugin is activated
                if (isset($list['product_id'])) {
                    $pro_obj = get_product($list['product_id']);
                    if ($pro_obj->is_type('simple')) {
                        $currentuser_lang = self::get_user_lang_manualmail($list['email'], $list['product_id']);
                        $lang_code = $currentuser_lang;
                    }
                    if ($pro_obj->is_type('variable')) {
                        $currentuser_lang = self::get_user_lang_manualmail_var($list['email'], $list['product_id'], $list['variationid']);
                        $lang_code = $currentuser_lang;
                    }
                } else {
                    $lang_code = "";
                }
            } else {
                $lang_code = "not_active";
            }
            ?>

            <script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery(".manual_mail_send").click(function () {
                        var mail_name_subs = jQuery(this).data('send_mail_subs');
                        var user_id = jQuery(this).data('user-id');
                        var time_info = jQuery(this).data('time-info');
                        var product_id_subs = jQuery(this).data('product_id_subs');
                        var lang_code_subs = "<?php echo $lang_code; ?>";
                        var variation_id_subs = jQuery(this).data('variation_id');
                        jQuery(this).hide();
                        //                         var test = ".mail_send_notification_"+user_id +"_"+product_id_subs;
                        //                         alert(test);
                        jQuery(".mail_send_notification_" + user_id + "_" + time_info).css("display", "block");
                        //jQuery(".mail_send_notification_"+user_id +"_"+product_id_subs+"_"+time_info).fadeIn(1000);
                        var data_passing = {
                            action: "send_Notify_subscriber",
                            mail_name_subs: mail_name_subs,
                            product_id_subs: product_id_subs,
                            variation_id_subs: variation_id_subs,
                            lang_code: lang_code_subs
                                    // variartion_level_id_subs:variartion_level_id_subs
                        };
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data_passing, function (response) {
                            console.log('Got this from the server: ' + response);
                        });
                    });
                });
            </script>
            <?php
            //  $getting_reponse=get_option('my_test');
            // var_dump($getting_reponse);
        } else {
            echo '  <tr>
            <td>';
            echo "empty";
            echo '  </td>
         <td>';
            echo "empty";
            echo '     </td>
                <td>';
            echo "empty";
            echo '     </td>
            </tr>';
            echo ' <td>';
            echo "-";
            echo '     </td>
            </tr>';
        }
        echo '</tbody>
            <tfoot>
		<tr>
			<td colspan="7">
				<div class="pagination pagination-centered hide-if-no-paging"></div>
			</td>
		</tr>
	</tfoot></table>';
        echo '<style>.bis_pad_l_10{padding-left:10px !important;}
            .footable > tbody > tr > td, .footable > thead > tr > th, .footable > thead > tr > td{
            text-align:center;}
            .small_col{
            width:50px !important;
            }</style>';
        echo '</td></tr>';
    }

    public static function get_user_lang_manualmail($user_mail_id, $product_id) {
        // global $post;
        $list_of_subs_user = get_post_meta($product_id, 'notification_email_list', true);

        foreach ($list_of_subs_user as $each_subs) {
            //var_dump($each_subs);
            if ($user_mail_id == $each_subs['mail']) {
                $user_selected_lang = $each_subs['lang'];
                //r_dump($user_selected_lang);
                return $user_selected_lang;
            }
        }
    }

    public static function get_user_lang_manualmail_var($user_mail_id, $product_id, $variation_id) {
        global $post;

        $list_of_subs_user_var = get_post_meta($product_id, 'notification_email_list_' . $variation_id, true);

        foreach ($list_of_subs_user_var as $each_subs) {
            //  var_dump($each_subs);
            if ($user_mail_id == $each_subs['mail']) {
                $user_selected_lang = $each_subs['lang'];
                //  var_dump($user_selected_lang);
                return $user_selected_lang;
            }
        }
    }

    public static function call_back_func() {
        if (isset($_POST['mail_name_subs'])) {
            global $wpdb;
            global $woocommerce;
            $translated_subject = '';

            if (isset($_POST['lang_code'])) {

                $currentuser_lang = empty($_POST['lang_code']) ? "en" : $_POST['lang_code'];
                // $mail_list_array[] = array("mail" => $_POST['mail_name_subs'], "lang" => $currentuser_lang);
                $to = $_POST['mail_name_subs'];
                $post_id = $_POST['product_id_subs'];
                $variation_id = $_POST['variation_id_subs'];
                $product_obj = get_product($post_id);
                if ($product_obj->is_type('simple')) {

                    // $getlanguage =  $currentuser_lang;
                    //$prod_title=$_POST['simple_prduct_name_subs'];
                    if ($currentuser_lang != 'not_active') {
                        $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $currentuser_lang));

                        if (!empty($string)) {
                            $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $currentuser_lang));
                            $translated_subject = $translated_string[0]->value;
                        } else {
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject'));
                            $string_id = $string[0]->id;
                            $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$currentuser_lang'");

                            @$translated_subject = $translated_string[0]->value;
                        }
                        if ($translated_subject == NULL) {
                            $translated_subject = get_option('instock_email_subject');
                        }

                        //msassage translation
                        $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $currentuser_lang));
                        if (!empty($string)) {
                            $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $currentuser_lang));
                            $translated_message = $translated_string[0]->value;
                        } else {
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages'));
                            $string_id = $string[0]->id;
                            $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$currentuser_lang'");
                            // update_option('translated_string', $translated_string[0]->value);
                            $translated_message = $translated_string[0]->value;
                        }
                        if ($translated_message == NULL) {
                            $translated_message = get_option('instock_email_messages');
                        }
                    } else {
                        $translated_subject = get_option('instock_email_subject');
                        $translated_message = get_option('instock_email_messages');
                    }
                    if (strchr(getting_permalink($post_id), "?")) {
                        $cart_url = add_query_arg('add-to-cart', $post_id, getting_permalink($post_id));
                    } else {
                        $cart_url = add_query_arg('?add-to-cart', $post_id, getting_permalink($post_id));
                    }
                    //$cart_url = add_query_arg('?add-to-cart',$post_id,get_post_permalink());
                    $message = \str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>", \str_replace('[cart_url]', "<a href=" . $cart_url . ">" . $cart_url . "</a>", \str_replace('[product_title]', get_the_title($post_id), \str_replace('[site_title]', get_option('blogname'), \str_replace('[productinfo]', '', $translated_message)))));
                    ob_start();
                    $subject = str_replace('[product_url]', get_permalink($post_id), str_replace('[product_title]', get_the_title($post_id), str_replace('[site_title]', get_option('blogname'), $translated_subject)));

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

                        if ('mail' == get_option('bis_mail_function')) {
                            mail($to, $subject, $woo_temp_msg, $headers);
                        } elseif ('wp_mail' == get_option('bis_mail_function')) {
                            wp_mail($to, $subject, $woo_temp_msg, $headers);
                        }
                    } else {
//                        $mainname = get_option('woocommerce_email_from_name');
//                        $mainemail = get_option('woocommerce_email_from_address');
//// To send HTML mail, the Content-type header must be set
//                        $headers = 'MIME-Version: 1.0' . "\r\n";
//                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
//// Additional headers
//                        $headers .= 'From:' . $mainname . '  <' . $mainemail . '>' . "\r\n";
                        $mailer = WC()->mailer();
                        $mailer->send($to, $subject, $woo_temp_msg, '', '');
                        echo "Mail Sent to '" . $to . "' ";
                    }
                } elseif ($product_obj->is_type('variable')) {
                    if ($currentuser_lang != 'not_active') {
                        $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $currentuser_lang));

                        if (!empty($string)) {
                            $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject', $currentuser_lang));
                            $translated_subject = $translated_string[0]->value;
                        } else {
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_subject'));
                            $string_id = $string[0]->id;
                            $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$currentuser_lang'");
                            // update_option('translated_string', $translated_string[0]->value);
                            if (isset($translated_string[0]->value)) {
                                $translated_subject = @$translated_string[0]->value;
                            }
                        }
                        if ($translated_subject == NULL) {
                            $translated_subject = get_option('instock_email_subject');
                        }

                        //msassage translation
                        $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $currentuser_lang));
                        if (!empty($string)) {
                            $translated_string = $wpdb->get_results($wpdb->prepare("SELECT value FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s AND language = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages', $currentuser_lang));
                            $translated_message = $translated_string[0]->value;
                        } else {
                            $string = $wpdb->get_results($wpdb->prepare("SELECT id FROM {$wpdb->prefix}icl_strings WHERE context = %s AND name = %s ", 'admin_texts_plugin_backinstocknotifier', 'instock_email_messages'));
                            $string_id = $string[0]->id;
                            $translated_string = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}icl_string_translations WHERE string_id = '$string_id' AND language= '$currentuser_lang'");
                            // update_option('translated_string', $translated_string[0]->value);
                            $translated_message = $translated_string[0]->value;
                        }
                        if ($translated_message == NULL) {
                            $translated_message = get_option('instock_email_messages');
                        }
                    } else {
                        $translated_subject = get_option('instock_email_subject');
                        $translated_message = get_option('instock_email_messages');
                    }
                    if (strchr(getting_permalink($post_id), "?")) {
                        $cart_url = add_query_arg('add-to-cart', $post_id, getting_permalink($post_id));
                    } else {
                        $cart_url = add_query_arg('?add-to-cart', $post_id, getting_permalink($post_id));
                    }
                    //$cart_url = add_query_arg('?add-to-cart',$post_id,get_post_permalink());
                    $return_attribute_slug = get_attribute_slug($variation_id);
                    $link = implode("&", $return_attribute_slug);
                    $message = \str_replace('[product_url]', "<a href=" . get_permalink($post_id) . ">" . get_permalink($post_id) . "</a>", \str_replace('[cart_url]', "<a href=" . $cart_url . "&variation_id=" . $variation_id . "&" . $link . ">" . $cart_url . "&variation_id=" . $variation_id . "&" . $link . "</a>", \str_replace('[product_title]', get_the_title($variation_id), \str_replace('[site_title]', get_option('blogname'), \str_replace('[productinfo]', '', $translated_message)))));
                    ob_start();
                    $subject = str_replace('[product_url]', get_permalink($variation_id), str_replace('[product_title]', get_the_title($variation_id), str_replace('[site_title]', get_option('blogname'), $translated_subject)));

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

                        if ('mail' == get_option('bis_mail_function')) {
                            mail($to, $subject, $woo_temp_msg, $headers);
                        } elseif ('wp_mail' == get_option('bis_mail_function')) {
                            wp_mail($to, $subject, $woo_temp_msg, $headers);
                        }
                    } else {
//                        $mainname = get_option('woocommerce_email_from_name');
//                        $mainemail = get_option('woocommerce_email_from_address');
//// To send HTML mail, the Content-type header must be set
//                        $headers = 'MIME-Version: 1.0' . "\r\n";
//                        $headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
//// Additional headers
//                        $headers .= 'From:' . $mainname . '  <' . $mainemail . '>' . "\r\n";
                        $mailer = WC()->mailer();
                        $mailer->send($to, $subject, $woo_temp_msg, '', '');
                        echo "Mail Sent to '" . $to . "' ";
                    }
                }
            }
        }
    }

    public static function bis_add_script_to_csvexport() {
        global $woocommerce;
        if (is_admin()) {
            if (isset($_REQUEST['page'])) {
                if (isset($_GET['tab'])) {
                    if ($_REQUEST['page'] == 'wc-settings' && $_GET['tab'] == 'instockmailer') {
                        $bis_settings_page = admin_url('admin.php?page=wc-settings&tab=instockmailer');
                        $arg = array('bis_export' => 'csv');
                        $url_to_send = esc_url_raw(add_query_arg($arg, $bis_settings_page));
                        $master_arg = array('bis_master_export' => 'master_csv');
                        $master_url_to_send = esc_url_raw(add_query_arg($master_arg, $bis_settings_page));
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function () {
                                jQuery('input[type=button]#bis_csv').click(function () {
                                    window.location.href = "<?php echo $url_to_send ?>";
                                });
                                // adding redirection for master too in here
                                jQuery('input[type=button]#bis_master_csv').click(function () {
                                    window.location.href = "<?php echo $master_url_to_send ?>";
                                });

                                //adding select all/deselect all for master list
                                jQuery('#bis_mas_sel').click(function (e) {
                                    e.preventDefault();
                                    jQuery('.bis_mas_checkboxes').prop('checked', true);
                                });
                                jQuery('#bis_mas_desel').click(function (e) {
                                    e.preventDefault();
                                    jQuery('.bis_mas_checkboxes').prop('checked', false);
                                });

                                jQuery('#bis_mas_selected_del').click(function (e) {
                                    e.preventDefault();
                                    var selection_for_delete = new Array();
                                    jQuery('.bis_mas_checkboxes').each(function (num) {
                                        if (jQuery(this).prop('checked')) {
                                            selection_for_delete.push(jQuery(this).data('bismasid'));
                                            jQuery(this).parents('tr:first').css('display', 'none');
                                        }
                                    });

                                    var data = ({
                                        action: 'deletemaslist',
                                        listids: selection_for_delete,
                                    });
                                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                                            function (response) {

                                            });
                                    // console.log(jQuery('.bis_mas_checkboxes'));
                                    console.log(selection_for_delete);
                                });

                                jQuery('.bis_footable').footable().on('click', '.bis_mas_check_indi', function (e) {
                                    e.preventDefault();
                                    var id_to_delete = jQuery(this).data('bistdelid');
                                    var data = ({
                                        action: 'deletemaslist',
                                        id_to_delete: id_to_delete,
                                    });
                                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", data,
                                            function (response) {

                                            });
                                    var footable = jQuery('.bis_footable').data('footable');

                                    var row = jQuery(this).parents('tr:first');
                                    console.log(row);

                                    footable.removeRow(row);
                                });

                                jQuery("#bis_hide_products").attr("multiple", "");
                                jQuery("#bis_hide_products").attr('data-placeholder', 'Select Product for which Susbscribe form should not appear...');


                        <?php
                        if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                            ?>
                                    jQuery("#bis_hide_products").chosen();
                        <?php } ?>
                                jQuery("#bis_hide_products_cat").attr("multiple", "");
                                jQuery("#bis_hide_products_cat").attr('data-placeholder', 'Select Product Categories for which Susbscribe form should not appear...');


                        <?php
                        if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                            ?>
                                    jQuery("#bis_hide_products_cat").chosen();
                        <?php } else {
                            ?>
                                    jQuery("#bis_hide_products_cat").select2();
                        <?php } ?>
                            }
                            );
                        </script>
                        <?php
                    }
                }
            }
        }
    }

    public static function bis_output_subscribers() {
        if (isset($_REQUEST['bis_export'])) {
            if ($_REQUEST['bis_export'] == 'csv') {
                $bis_content = "Product ID,";
                $bis_content .= "Product Name,";
                $bis_content .= "Email ID,\n";
                $args = array('post_type' => 'product', 'posts_per_page' => -1);
                $loop = new WP_Query($args);
                while ($loop->have_posts()) : $loop->the_post();
                    global $product;

//for variable product
                    $product = get_product($loop->post->ID);
                    if (($product->product_type == 'variable') || ($product->product_type == 'variable-subscription')) {
                        $variations = $product->get_available_variations(); // will work only on variable product

                        foreach ($variations as $each_variations) {
                            $variable_mail = get_post_meta($loop->post->ID, "notification_email_list_" . $each_variations['variation_id'] . "");
                            if (is_array($variable_mail) && !empty($variable_mail)) {

                                foreach ($variable_mail as $eachvar) {
                                    foreach ($eachvar as $key => $eachlist) {
                                        $bis_content .= $loop->post->ID . ",";
                                        $bis_content .= $loop->post->post_title . ",";
                                        $bis_content .= $eachlist['mail'];
                                        $bis_content .= "\n";
                                    }
                                }
                            }
                        }
                    } else { // for simple product
                        $mail_list_array = get_post_meta($loop->post->ID, 'notification_email_list');

                        if (is_array($mail_list_array) && !empty($mail_list_array)) {
                            foreach ($mail_list_array as $mail_list) {
                                foreach ($mail_list as $key => $eachmail) {
                                    $bis_content .= $loop->post->ID . ",";
                                    $bis_content .= $loop->post->post_title . ",";
                                    $bis_content .= $eachmail['mail'];
                                    $bis_content .= "\n";
                                }
                            }
                        }
                    }

                endwhile;
                wp_reset_query();
                $bis_content = strip_tags($bis_content);

                header("Content-Disposition: attachment; filename=BackInStock-" . date('d-m-Y') . ".csv");
                print($bis_content);
                exit();
            }
        }
    }

    public static function bis_output_master_subscribers() {
        if (isset($_REQUEST['bis_master_export'])) {
            if ($_REQUEST['bis_master_export'] == 'master_csv') {
                $bis_content = "Product ID,";
                $bis_content .= "Product Name,";
                $bis_content .= "Email ID,";
                $bis_content .= "Date - Time,";
                $bis_content .= "Notification Mail - Date - Time,\n";
                $bis_master_list = get_option('bis_master_list');
                if (is_array($bis_master_list)) {
                    foreach ($bis_master_list as $list) {
                        $bis_content .= $list['product_id'] . ",";
                        $bis_content .= get_the_title($list['product_id']) . ",";
                        $bis_content .= $list['email'] . ",";
                        $bis_content .= date(str_replace(",", "/", get_option('date_format')) . ' - ' . str_replace(",", "/", get_option('time_format')), $list['time']) . ",";
                        if (isset($list['notification_time'])) {
                            $bis_content .= "Sent - " . date(str_replace(",", "/", get_option('date_format')) . ' - ' . str_replace(",", "/", get_option('time_format')), $list['notification_time']) . ",";
                        } else {
                            $bis_content .= "Not Sent,";
                        }
                        $bis_content .= "\n";
                    }

                    $bis_content = strip_tags($bis_content);
                }
                header("Content-Disposition: attachment; filename=BackInStock-Master" . date('d-m-Y') . ".csv");
                print($bis_content);
                exit();
            }
        }
    }

    public static function bis_footable_scripts() {
        if (isset($_GET['tab'])) {
            if ($_GET['tab'] == 'instockmailer') {
                wp_register_style('footable_css', plugins_url('/css/footable.core.css', __FILE__));
                wp_enqueue_style('footable_css');
                wp_register_style('footablestand_css', plugins_url('/css/footable.standalone.css', __FILE__));
                wp_enqueue_style('footablestand_css');

                wp_enqueue_script('footable', plugins_url('/js/footable.js', __FILE__), array('jquery'));
                wp_enqueue_script('footable_sorting', plugins_url('/js/footable.sort.js', __FILE__), array('jquery'));
                wp_enqueue_script('footable_paginate', plugins_url('/js/footable.paginate.js', __FILE__), array('jquery'));
                wp_enqueue_script('footable_filter', plugins_url('/js/footable.filter.js', __FILE__), array('jquery'));
                wp_enqueue_script('footable_initialize', plugins_url('/js/footable_initialize.js', __FILE__), array('jquery'));

//include iris color picker
                wp_enqueue_style('wp-color-picker');
                wp_enqueue_script('wp-color', plugins_url('js/iris.js', __FILE__), array('wp-color-picker'), false, true);

                wp_enqueue_script('bis-admin-event', plugins_url('js/admin-event.js', __FILE__), array('jquery'));
            }
        }
    }

    public static function bis_delete_master_list() {
        $old_master_list = get_option('bis_master_list');
        if (isset($_POST['id_to_delete'])) {
            $id_to_delete = $_POST['id_to_delete'];
            unset($old_master_list[$id_to_delete]);
            $updated_master_list = array_values($old_master_list);
            update_option('bis_master_list', $updated_master_list);
        } else {
            $selected_deletion = $_POST['listids'];

            foreach ($selected_deletion as $index_to_delete) {
                echo $old_master_list[$index_to_delete]['email'];
                unset($old_master_list[$index_to_delete]);
            }
            $updated_master_list = array_values($old_master_list);
            update_option('bis_master_list', $updated_master_list);
        }
        exit();
    }

    public static function bis_enqueue_for_media_upload() {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        } else {
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_script('thickbox');
        }
    }

    public static function bis_header_problems() {
        ob_start();
    }

    public static function save_product_selection_backward_compatibility() {
        update_option('bis_hide_products', $_POST['bis_hide_products']);
    }

    public static function passing_product_and_page_id() {
        if (function_exists('is_product')) {
            if (is_product()) {
                global $post;           //var_export($post->ID);
                $return_product_content = self::notifyscript($post->ID);
                //return $return_product_content;
            }
        }
        if (is_page()) {
            global $page;
            $get_page_post = get_post($page); //var_export($testing);
            $get_page_content = self::get_regular_expressions_in_page_content($get_page_post->post_content);
            foreach ($get_page_content as $key) {
                //var_export($key);
                $return_product_content = self::notifyscript($key);
                //return $return_product_content;
            }
        }
    }

    public static function get_regular_expressions_in_page_content($str) {
        preg_match_all('/\d+/', $str, $matches);
        return $matches[0];
    }

}

//class ends here

function get_attribute_slug($varidid) {

    $get_productid = get_product($varidid);
    $get_variations = $get_productid->get_variation_attributes();
    foreach ($get_variations as $key => $value) {
        $var[] = $key . "=" . $value;
    }
    return $var;
}

/* Include once will help to avoid fatal error by load the files when you call init hook */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//check woocommerce is active or not

add_action('init', array('BackInStockNotifier', 'check_woocommerce_plugin_is_active'));
add_action('wp_enqueue_scripts', array('BackInStockNotifier', 'enqueuenotifyscript'));
add_filter('woocommerce_stock_html', array('BackInStockNotifier', 'checkproductinstock'), 10, 3);
add_filter('woocommerce_get_price_html', array('BackInStockNotifier', 'check_product_in_stock_troubleshooting'), 10, 2);

add_action('wp_head', array('BackInStockNotifier', 'notifycss'));
add_action('wp_head', array('BackInStockNotifier', 'passing_product_and_page_id'));
add_action('wp_ajax_outofstocknotify', array('BackInStockNotifier', 'saveajaxvalue'));
add_action('wp_ajax_nopriv_outofstocknotify', array('BackInStockNotifier', 'saveajaxvalue'));
add_action('wp_ajax_emaillistremoval', array('BackInStockNotifier', 'admin_ajax_request'));
add_action('wp_ajax_nopriv_emaillistremoval', array('BackInStockNotifier', 'admin_ajax_request'));
add_action('add_meta_boxes', array('BackInStockNotifier', 'addmetabox_notifyurl'));


// For to check the backward compatibility of old version 2.0 ~ to 2.1.x

add_filter("woocommerce_available_variation", array('BackInStockNotifier', 'alter_available_variation_for_backward_compatible'), 10, 3);

//adding meta for variable
add_action('add_meta_boxes', array('BackInStockNotifier', 'add_variable_meta'));
add_action('admin_footer', array('BackInStockNotifier', 'checkindashboard'));
add_action('woocommerce_update_options_instockmailer', array('BackInStockNotifier', 'instock_mailer_update_settings'));
register_activation_hook(__FILE__, array('BackInStockNotifier', 'instock_mailer_default_settings'));
add_action('woocommerce_settings_tabs_instockmailer', array('BackInStockNotifier', 'instock_mailer_admin_settings'));
add_filter('woocommerce_settings_tabs_array', array('BackInStockNotifier', 'settings_tab_instock_mailer'), 120);
new BackInStockNotifier();
//first register the column
add_filter('manage_edit-product_columns', array('BackInStockNotifier', 'posts_columns'));
//then you need to render the column
add_action('manage_product_posts_custom_column', array('BackInStockNotifier', 'posts_custom_columns'), 5, 2);
// Register the column as sortable
add_filter('manage_edit-product_sortable_columns', array('BackInStockNotifier', 'posts_column_register_sortable'));
add_action('pre_get_posts', array('BackInStockNotifier', 'event_column_orderby'));
//end _column_orderby
add_action('save_post_product', array('BackInStockNotifier', 'add_meta_key_by_default'), 20);
//Script at front end for vaiable level out of stock
add_action('wp_head', array('BackInStockNotifier', 'out_of_stock_for_variation'));

//Only mail when meta is changed   
add_action('save_post', array('BackInStockNotifier', 'bis_mail_on_meta_update'), 20, 2);


add_action('save_post', array('BackInStockNotifier', 'save_mail_op'));
add_action('save_post', array('BackInStockNotifier', 'save_mail_op_variable'));

add_action('plugins_loaded', array('BackInStockNotifier', 'bis_translate_file'));

//add_shortcode('unsubscribe_link', array('BackInStockNotifier', 'bis_unsubscribe_link'));
//custom button for csv export
add_action('woocommerce_admin_field_button', array('BackInStockNotifier', 'bis_add_csv_button'));
add_action('woocommerce_admin_field_button_master', array('BackInStockNotifier', 'bis_add_master_list_csv_button'));
add_action('wp_ajax_send_Notify_subscriber', array('BackInStockNotifier', 'call_back_func'));
add_action('woocommerce_admin_field_cus_table', array('BackInStockNotifier', 'bis_master_table'));

add_action('woocommerce_admin_field_cus_logo', array('BackInStockNotifier', 'bis_add_logo_input'));

add_action('admin_head', array('BackInStockNotifier', 'bis_add_script_to_csvexport'));
add_action('admin_init', array('BackInStockNotifier', 'bis_output_subscribers'));
add_action('admin_init', array('BackInStockNotifier', 'bis_output_master_subscribers'));

add_action('admin_enqueue_scripts', array('BackInStockNotifier', 'bis_footable_scripts'));

add_action('wp_ajax_deletemaslist', array('BackInStockNotifier', 'bis_delete_master_list'));
add_action('wp_ajax_updatemetabox', array('BackInStockNotifier', 'saveajaxvalue'));
add_action('woocommerce_admin_field_bis_hide_products', array('BackInStockNotifier', 'add_product_selection_backward_compatibility'));
add_action('woocommerce_update_option_bis_hide_products', array('BackInStockNotifier', 'save_product_selection_backward_compatibility'));


//add for media support
add_action('admin_enqueue_scripts', array('BackInStockNotifier', 'bis_enqueue_for_media_upload'));
add_action('init', array('BackInStockNotifier', 'bis_header_problems'));
require_once 'inc/bis_manual_mail.php';

add_action('admin_head', array('BackInStockNotifier', 'add_ajax_chosen_to_that_script'));

