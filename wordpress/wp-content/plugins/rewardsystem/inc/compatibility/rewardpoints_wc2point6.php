<?php

/*
 * Reward Points Compatible with 2.6 of WooCommerce
 */

class FP_Reward_Points_WC_2P6 {

    function __construct() {
        add_action('wp_ajax_apply_sumo_reward_points', array($this, 'apply_redeeming_points'), 999);
        add_action('wp_ajax_sumo_updated_cart_total', array($this, 'recalculate_totals'), 999);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 999);
        add_action('wp_ajax_sumo_remove_coupon', array($this, 'remove_coupon_from_cart'), 999);
    }

    // Reward Points Compatible with Version 2.6 of WooCommerce
    public static function apply_redeeming_points() {
        RSFunctionToApplyCoupon::apply_matched_coupons();
        wc_print_notices();
        die();
    }

    // Recalculate Totals
    public static function recalculate_totals() {

        if (!defined('WOOCOMMERCE_CART')) {
            define('WOOCOMMERCE_CART', true);
        }
        WC()->cart->calculate_totals();
        RSFunctionForCart::get_reward_points_to_display_msg_in_cart_and_checkout();
        RSFunctionForCart::display_msg_in_cart_page();
        RSFunctionForCheckout::display_complete_message_cart_page();
        RSFunctionForCheckout::your_current_points_cart_page();
        RSFunctionForCart::display_msg_in_cart_page_for_balance_reward_points();
        RSFunctionForCart::display_redeem_points_buttons_on_cart_page();
        RSFunctionForCheckout::display_redeem_min_max_points_buttons_on_cart_page();
        woocommerce_cart_totals();

        die();
    }

    // Remove Coupon from Cart

    public static function remove_coupon_from_cart() {
        if (isset($_POST['coupon'])) {
            $coupon = wc_clean($_POST['coupon']);
            WC()->cart->remove_coupon($coupon);
            RSFunctionForCart::get_reward_points_to_display_msg_in_cart_and_checkout();
            RSFunctionForCart::display_msg_in_cart_page();
            RSFunctionForCheckout::display_complete_message_cart_page();
            RSFunctionForCheckout::your_current_points_cart_page();
            RSFunctionForCart::display_msg_in_cart_page_for_balance_reward_points();
            RSFunctionForCart::display_redeem_points_buttons_on_cart_page();
            RSFunctionForCheckout::display_redeem_min_max_points_buttons_on_cart_page();
            woocommerce_cart_totals();
        }
        die();
    }

    //register enqueue script for to perform redeeming on cart FP_Reward_Points_Main_Path
    public function enqueue_scripts() {
        global $woocommerce;
        if ((float) $woocommerce->version >= (float) ('2.6.0')) {
            //echo "you are right";
            $minimum_points = get_option("rs_minimum_redeeming_points");
            $maximum_points = get_option("rs_maximum_redeeming_points");
            $error_msg_min_max = do_shortcode(addslashes(get_option("rs_minimum_and_maximum_redeem_point_error_message")));
            $error_msg_min = do_shortcode(addslashes(get_option("rs_minimum_redeem_point_error_message")));
            $error_msg_max = do_shortcode(addslashes(get_option("rs_maximum_redeem_point_error_message")));


            if (is_cart() && is_user_logged_in()) {
                wp_enqueue_script('jquery');
                wp_register_script('sumo_reward_points_wc2p6', plugins_url('/js/sumorewardpoints_wc2p6.js', FP_Reward_Points_Main_Path));
                $global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id(), 'minimum_points' => $minimum_points, 'maximum_points' => $maximum_points, 'min_max_error' => $error_msg_min_max, 'min_error' => $error_msg_min, 'max_error' => $error_msg_max);
                wp_localize_script('sumo_reward_points_wc2p6', 'sumo_global_variable_js', $global_variable_for_js);
                wp_enqueue_script('sumo_reward_points_wc2p6', false, array(), '', true);
            }


            if (is_checkout() && is_user_logged_in()) {
                wp_enqueue_script('jquery');
                wp_register_script('checkoutscript', plugins_url('/js/checkoutscript.js', FP_Reward_Points_Main_Path));
                $global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id());
                wp_localize_script('checkoutscript', 'checkoutscript_variable_js', $global_variable_for_js);
                wp_enqueue_script('checkoutscript', false, array(), '', true);
            }
            
            //Form For Refer a Friend
            $refername = addslashes(get_option('rs_my_rewards_friend_name_error_message'));
            $referemail = addslashes(get_option('rs_my_rewards_friend_email_error_message'));
            $invalidemail = addslashes(get_option('rs_my_rewards_friend_email_is_not_valid'));
            $subject = addslashes(get_option('rs_my_rewards_email_subject_error_message'));
            $message = addslashes(get_option('rs_my_rewards_email_message_error_message'));
            $termandcondition = get_option('rs_show_hide_iagree_termsandcondition_field');

            wp_enqueue_script('jquery');
            wp_register_script('referfriend', plugins_url('/js/referfriend.js', FP_Reward_Points_Main_Path));
            $global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id(), 'refnameerrormsg' => $refername, 'refmailiderrormsg' => $referemail, 'invalidemail' => $invalidemail, 'subjecterror' => $subject, 'messageerror' => $message, 'enableterms' => $termandcondition);
            wp_localize_script('referfriend', 'referfriend_variable_js', $global_variable_for_js);

            //Form For Cash Back Request
            $currentuserpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
            $roundofftype = get_option('rs_round_off_type') == '1' ? '2' : '0';
            $currentuserpoints = round($currentuserpoints, $roundofftype);
            $rs_minimum_points_for_encash = get_option('rs_minimum_points_encashing_request') != '' ? get_option('rs_minimum_points_encashing_request') : 0;
            $rs_maximum_points_for_encash = get_option('rs_maximum_points_encashing_request') != '' ? get_option('rs_maximum_points_encashing_request') : $currentuserpoints;
            $select_payment_method = get_option('rs_select_payment_method');
            $redeempoint_for_cashback = get_option('rs_redeem_point_for_cash_back');
            $redeempoint_value_for_cashback = get_option('rs_redeem_point_value_for_cash_back');
            if (is_user_logged_in()) {
                $user_details = get_user_by('id', get_current_user_id());
                $username = $user_details->user_login;
            } else {
                $username = 'Guest';
            }
            wp_enqueue_script('jquery');
            wp_register_script('encashform', plugins_url('/js/encashformscript.js', FP_Reward_Points_Main_Path));
            $encash_global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id(), 'currentuserpoint' => $currentuserpoints, 'minimumpointforencash' => $rs_minimum_points_for_encash, 'maximumpointforencash' => $rs_maximum_points_for_encash, 'selectpaymentmethod' => $select_payment_method, 'redeempointforcashback' => $redeempoint_for_cashback, 'redeempointvalueforcashback' => $redeempoint_value_for_cashback, 'username' => $username);
            wp_localize_script('encashform', 'encashform_variable_js', $encash_global_variable_for_js);

            //Form For Send Points
            $currentuserpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
            $roundofftype = get_option('rs_round_off_type') == '1' ? '2' : '0';
            $currentuserpoints = round($currentuserpoints, $roundofftype);
            if (get_option('rs_limit_send_points_request') != '') {
                $limitotsendpointsreq = get_option('rs_limit_send_points_request');
            } else {
                $limitotsendpointsreq = '0';
            }
            $limitotsendpoints = get_option('rs_limit_for_send_point');
            if (is_user_logged_in()) {
                $user_details = get_user_by('id', get_current_user_id());
                $username = $user_details->user_login;
            } else {
                $username = 'Guest';
            }
            wp_enqueue_script('jquery');
            wp_register_script('formforsendpoints', plugins_url('/js/formforsendpointsscript.js', FP_Reward_Points_Main_Path));
            $sendpoints_global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id(),'currentuserpoint' => $currentuserpoints,'limittosendreq' => $limitotsendpointsreq,'sendpointlimit' => $limitotsendpoints, 'username' => $username);
            wp_localize_script('formforsendpoints', 'formforsendpoints_variable_js', $sendpoints_global_variable_for_js);
            
            //For Gift Voucher Redeem Field Shortcode 
            $error_msg = addslashes(get_option('rs_voucher_redeem_empty_error'));
            wp_enqueue_script('jquery');
            wp_register_script('giftvoucher', plugins_url('/js/giftvoucher.js', FP_Reward_Points_Main_Path));
            $global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id(), 'error' => $error_msg);
            wp_localize_script('giftvoucher', 'giftvoucher_variable_js', $global_variable_for_js);
            
            //For Cashback Table Shortcode 
            wp_enqueue_script('jquery');
            wp_register_script('encashform1', plugins_url('/js/encashform.js', FP_Reward_Points_Main_Path));
            $global_variable_for_js = array('wp_ajax_url' => admin_url('admin-ajax.php'), 'user_id' => get_current_user_id());
            wp_localize_script('encashform1', 'encashform1_variable_js', $global_variable_for_js);
            
            //social button
             wp_enqueue_script('jquery');
            wp_register_script('socialbutton', plugins_url('/js/socialbutton.js', FP_Reward_Points_Main_Path));
        }
    }

}

new FP_Reward_Points_WC_2P6();
