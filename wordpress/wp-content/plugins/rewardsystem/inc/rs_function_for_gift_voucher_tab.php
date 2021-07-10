<?php

class RSFunctionForGiftVoucher {

    public function __construct() {

        add_action('wp_enqueue_scripts', array($this, 'wp_enqueqe_script_for_footable'));

        add_action('admin_enqueue_scripts', array($this, 'wp_enqueqe_script_for_footable'));

        add_action('admin_enqueue_scripts', array($this, 'date_enqueqe_script_for_gift'));

        add_action('admin_head', array($this, 'rs_validation_of_input_field_in_gift_voucher'));

        add_action('wp_ajax_nopriv_rewardsystem_point_vouchers', array($this, 'process_ajax_request_rs_point_vouchers'));

        add_action('wp_ajax_rewardsystem_point_vouchers', array($this, 'process_ajax_request_rs_point_vouchers'));

        add_action('wp_ajax_nopriv_rewardsystem_point_bulk_vouchers', array($this, 'process_ajax_request_for_rs_bulk_point_vouchers'));

        add_action('wp_ajax_rewardsystem_point_bulk_vouchers', array($this, 'process_ajax_request_for_rs_bulk_point_vouchers'));

        add_action('wp_ajax_nopriv_rewardsystem_redeem_voucher_codes', array($this, 'process_ajax_request_to_redeem_voucher_reward_system'));

        add_action('wp_ajax_rewardsystem_redeem_voucher_codes', array($this, 'process_ajax_request_to_redeem_voucher_reward_system'));

        add_action('wp_ajax_nopriv_rewardsystem_delete_array', array($this, 'delete_array_keys_rs_point_vouchers'));

        add_action('wp_ajax_rewardsystem_delete_array', array($this, 'delete_array_keys_rs_point_vouchers'));

        if (get_option('rs_show_hide_redeem_voucher') == '1') {

            if (get_option('rs_redeem_voucher_position') == '1') {

                add_action('woocommerce_before_my_account', array($this, 'reward_system_my_account_voucher_redeem'));
            } else {
                add_action('woocommerce_after_my_account', array($this, 'reward_system_my_account_voucher_redeem'));
            }
        }

        add_shortcode('rs_redeem_vouchercode', array($this, 'rewardsystem_myaccount_voucher_redeem_shortcode'));

        add_filter('woocommerce_login_redirect', array($this, 'rs_function_to_redirect_after_login_and_registration'));

        add_filter('woocommerce_registration_redirect', array($this, 'rs_function_to_redirect_after_login_and_registration'));
    }

    public static function date_enqueqe_script_for_gift() {

        wp_enqueue_script('jquery-ui-datepicker');
        wp_register_script('wp_reward_jquery_ui', plugins_url('rewardsystem/js/jquery-ui.js'));
    }

    public static function wp_enqueqe_script_for_footable() {
        wp_register_script('wp_reward_footable', plugins_url('rewardsystem/js/footable.js'));
        wp_register_script('wp_reward_footable_filter', plugins_url('rewardsystem/js/footable.filter.js'));
        wp_enqueue_script('wp_reward_footable');
        wp_enqueue_script('wp_reward_footable_filter');
    }

    public static function rs_validation_of_input_field_in_gift_voucher() {
        ?>

        <script type="text/javascript">
            jQuery(function () {
                jQuery('body').on('blur', '#rs_point_voucher_reward_points[type=text],\n\
                                           #rs_point_bulk_voucher_points[type=text]', function () {
                    jQuery('.wc_error_tip').fadeOut('100', function () {
                        jQuery(this).remove();
                    });

                    return this;
                });

                jQuery('body').on('keyup change', '#rs_point_voucher_reward_points[type=text],\n\
                                           #rs_point_bulk_voucher_points[type=text]', function () {
                    var value = jQuery(this).val();
                    var regex = new RegExp("[^\+0-9\%.\\" + woocommerce_admin.mon_decimal_point + "]+", "gi");
                    var newvalue = value.replace(regex, '');

                    if (value !== newvalue) {
                        jQuery(this).val(newvalue);
                        if (jQuery(this).parent().find('.wc_error_tip').size() == 0) {
                            var offset = jQuery(this).position();
                            jQuery(this).after('<div class="wc_error_tip">' + woocommerce_admin.i18n_mon_decimal_error + " Negative Values are not allowed" + '</div>');
                            jQuery('.wc_error_tip')
                                    .css('left', offset.left + jQuery(this).width() - (jQuery(this).width() / 2) - (jQuery('.wc_error_tip').width() / 2))
                                    .css('top', offset.top + jQuery(this).height())
                                    .fadeIn('100');
                        }
                    }
                    return this;
                });



                jQuery("body").click(function () {
                    jQuery('.wc_error_tip').fadeOut('100', function () {
                        jQuery(this).remove();
                    });

                });
            });
        </script>
        <?php
    }

    public static function process_ajax_request_rs_point_vouchers() {
        if (isset($_POST['vouchercode']) && ($_POST['voucherpoints'])) {

            $checkifexists = get_option('rsvoucherlists');
            if (!empty($checkifexists)) {
                foreach (get_option('rsvoucherlists') as $updates) {
                    if (!array_key_exists($_POST['vouchercode'], $updates)) {
                        $newupdatess[] = $updates;
                    } else {
                        echo "1";
                        exit();
                    }
                }

                $newupdates = array(
                    array(
                        $_POST['vouchercode'] => array('points' => $_POST['voucherpoints'], 'vouchercode' => $_POST['vouchercode'], 'vouchercreated' => $_POST['vouchercreated'], 'voucherexpiry' => $_POST['voucherexpiry'], 'memberused' => '', 'voucherused' => '')
                    ),
                );
                $array1 = (array) $newupdatess;
                $array2 = $newupdates;
                $array3 = array_merge($array1, $array2);
                update_option('rsvoucherlists', array_filter($array3));
            } else {

                $newupdates = array(
                    array(
                        $_POST['vouchercode'] => array('points' => $_POST['voucherpoints'], 'vouchercode' => $_POST['vouchercode'], 'vouchercreated' => $_POST['vouchercreated'], 'voucherexpiry' => $_POST['voucherexpiry'], 'memberused' => '', 'voucherused', '')
                    ),
                );
                update_option('rsvoucherlists', $newupdates);
            }
        }
        exit();
    }

    public static function process_ajax_request_for_rs_bulk_point_vouchers() {
        if (isset($_POST['vouchercount']) && ($_POST['bulkvoucherpoints'])) {

            $checkifexists = get_option('rsvoucherlists');
            if (!empty($checkifexists)) {

                for ($i = 1; $i <= $_POST['vouchercount']; $i++) {
                    $num = mt_rand(1000001, 9999998);
                    $output = sprintf('%07x', $num);
                    $newvouchercode[] = $_POST['voucherprefix'] . $output;
                }
                foreach (get_option('rsvoucherlists') as $updates) {
                    foreach ($newvouchercode as $codess) {
                        if (!array_key_exists($codess, $updates)) {
                            $newupdatess[] = array_filter($updates);
                        } else {
                            echo "1";
                        }
                    }
                }
                foreach ($newvouchercode as $newcodess) {
                    $newupdates = array(
                        array(
                            $newcodess => array('points' => $_POST['bulkvoucherpoints'], 'vouchercode' => $newcodess, 'vouchercreated' => $_POST['bulkvouchercreated'], 'voucherexpiry' => $_POST['bulkvoucherexpiry'], 'memberused' => '', 'voucherused' => '')
                        ),
                    );
                    $array1 = (array) $newupdatess;
                    $array2 = $newupdates;
                    $array3 = array_merge((array) get_option('rsvoucherlists'), $array2);

                    $array3 = array_map("unserialize", array_unique(array_map("serialize", $array3)));
                    update_option('rsvoucherlists', array_filter($array3));
                }
            } else {
                for ($i = 1; $i <= $_POST['vouchercount']; $i++) {
                    $num = mt_rand(1000001, 9999998);
                    $output = sprintf('%07x', $num);
                    $newvouchercode[] = $_POST['voucherprefix'] . $output;
                }
                foreach ($newvouchercode as $newcde) {
                    $newupdates = array(
                        array(
                            $newcde => array('points' => $_POST['bulkvoucherpoints'], 'vouchercode' => $newcde, 'vouchercreated' => $_POST['bulkvouchercreated'], 'voucherexpiry' => $_POST['bulkvoucherexpiry'], 'memberused' => '', 'voucherused', '')
                        ),
                    );
                    $array2 = $newupdates;
                    $array3 = array_merge((array) get_option('rsvoucherlists'), $array2);

                    $array3 = array_map("unserialize", array_unique(array_map("serialize", $array3)));
                    update_option('rsvoucherlists', array_filter($array3));
                }
            }
        }
        exit();
    }

    public static function reward_system_my_account_voucher_redeem() {
        ?>
        <h3><?php echo get_option('rs_redeem_your_gift_voucher_label'); ?></h3>
        <input type="text" size="50" name="rs_redeem_voucher" id="rs_redeem_voucher_code" value=""><input type="submit" style="margin-left:10px;" class="button <?php echo get_option('rs_extra_class_name_redeem_gift_voucher_button'); ?>" name="rs_submit_redeem_voucher" id="rs_submit_redeem_voucher" value="<?php echo get_option('rs_redeem_gift_voucher_button_label'); ?>"/>
        <div class="rs_redeem_voucher_error" style="color:red;"></div>
        <div class="rs_redeem_voucher_success" style="color:green"></div>
        <script type="text/javascript">
            jQuery(document).ready(function () {
                jQuery('#rs_submit_redeem_voucher').click(function () {

                    var redeemvouchercode = jQuery('#rs_redeem_voucher_code').val();
                    var new_redeemvouchercode = redeemvouchercode.replace(/\s/g, '');
                    if (new_redeemvouchercode === '') {
                        jQuery('.rs_redeem_voucher_error').html('<?php echo addslashes(get_option('rs_voucher_redeem_empty_error')); ?>').fadeIn().delay(5000).fadeOut();
                        return false;
                    } else {
                        jQuery('.rs_redeem_voucher_error').html('');
                        var dataparam = ({
                            action: 'rewardsystem_redeem_voucher_codes',
                            redeemvouchercode: new_redeemvouchercode,
                        });
                        jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", dataparam,
                                function (response) {
                                    console.log(jQuery.parseHTML(response));
                                    jQuery('.rs_redeem_voucher_success').html(jQuery.parseHTML(response)).fadeIn().delay(5000).fadeOut();
                                    jQuery('#rs_redeem_voucher_code').val('');
                                });
                        return false;
                    }
                });
            });
        </script>
        <?php
    }

    public static function rewardsystem_myaccount_voucher_redeem_shortcode() {
        wp_enqueue_script('giftvoucher', false, array(), '', true);
        ob_start();
        if (is_user_logged_in()) {
            ?>
            <h3><?php _e('Redeem your Gift Voucher', 'rewardsystem'); ?></h3>
            <input type="text" size="50" name="rs_redeem_voucher" id="rs_redeem_voucher_code" value=""><input type="submit" style="margin-left:10px;" class="button <?php echo get_option('rs_extra_class_name_redeem_gift_voucher_button'); ?>" name="rs_submit_redeem_voucher" id="rs_submit_redeem_voucher" value="<?php _e('Redeem Gift Voucher', 'rewardsystem'); ?>"/>
            <div class="rs_redeem_voucher_error" style="color:red;"></div>
            <div class="rs_redeem_voucher_success" style="color:green"></div>            
            <?php
        } else {
            $myaccountlink = get_permalink(get_option('woocommerce_myaccount_page_id'));
            $myaccounttitle = get_the_title(get_option('woocommerce_myaccount_page_id'));
            $linkforlogin = add_query_arg('redirect_to', get_permalink(), $myaccountlink);
            ?>
            <?php ob_start(); ?><a href="<?php echo $linkforlogin; ?>"><?php echo addslashes(get_option('rs_redeem_voucher_login_link_label')); ?></a>                
            <?php
            $message_for_guest = get_option("rs_voucher_redeem_guest_error_message");
            $redeem_voucher_guest_to_find = "[rs_login_link]";
            $redeem_voucher_guest_to_replace = ob_get_clean();
            $redeem_voucher_guest_replaced_content = str_replace($redeem_voucher_guest_to_find, $redeem_voucher_guest_to_replace, $message_for_guest);
            echo $redeem_voucher_guest_replaced_content;            
        }
        $maincontent = ob_get_clean();
        return $maincontent;
    }

    public static function rs_function_to_redirect_after_login_and_registration($redirect) {

        if (isset($_REQUEST['redirect_to'])) {
            $redirect = $_REQUEST['redirect_to'];
        }
        return $redirect;
    }

    public static function search($array, $key, $value) {
        $results = array();

        if (is_array($array)) {
            if (isset($array[$key]) && $array[$key] == $value) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, self::search($subarray, $key, $value));
            }
        }

        return $results;
    }

    public static function process_ajax_request_to_redeem_voucher_reward_system() {

        $newone = array();
        $userid = get_current_user_id();
        $banning_type = FPRewardSystem::check_banning_type($userid);
        if ($banning_type != 'earningonly' && $banning_type != 'both') {
            if (isset($_POST['redeemvouchercode'])) {

                if (is_array(get_option('rsvoucherlists'))) {
                    foreach (get_option('rsvoucherlists')as $newones) {
                        if (!array_key_exists($_POST['redeemvouchercode'], $newones)) {
                            $newone[] = $newones;
                        }
                    }
                }
                if (is_array(get_option('rs_list_of_gift_vouchers_created'))) {
                    foreach (get_option('rs_list_of_gift_vouchers_created')as $newones1) {
                        if (!array_key_exists($_POST['redeemvouchercode'], $newones1)) {
                            $newone1[] = $newones1;
                        }
                    }
                }

                $findedarray = self::search(get_option('rs_list_of_gift_vouchers_created'), 'vouchercode', $_POST['redeemvouchercode']);
                if (empty($findedarray)) {
                    $findedarray = self::search(get_option('rsvoucherlists'), 'vouchercode', $_POST['redeemvouchercode']);
                }

                if (empty($findedarray)) {
                    echo addslashes(get_option('rs_invalid_voucher_code_error_message'));
                    exit();
                } else {
                    $restrictuserpoints = get_option('rs_max_earning_points_for_user');
                    $enabledisablemaxpoints = get_option('rs_enable_disable_max_earning_points_for_user');
                    $dateformat = get_option('date_format');
                    $todays_date = date_i18n($dateformat);
                    $today = strtotime($todays_date);
                    $exp_date = $findedarray[0]['voucherexpiry'];
                    $vouchercreated = $findedarray[0]['vouchercreated'];


                    $voucherused = isset($findedarray[0]['memberused']) != '' ? $findedarray[0]['memberused'] : '';

                    $voucherpoints = $findedarray[0]['points'];
                    $noofdays = get_option('rs_point_to_be_expire');

                    if (($noofdays != '0') && ($noofdays != '')) {
                        $date = time() + ($noofdays * 24 * 60 * 60);
                    } else {
                        $date = '999999999999';
                    }
                    if ($voucherused == '') {
                        if ($exp_date != '') {
                            $expiration_date = strtotime($exp_date);
                            if ($expiration_date >= $today) {
                                if ($enabledisablemaxpoints == 'yes') {
                                    if (($restrictuserpoints != '') && ($restrictuserpoints != '0')) {
                                        $getoldpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                        if ($getoldpoints <= $restrictuserpoints) {
                                            $totalpointss = $getoldpoints + $voucherpoints;
                                            if ($totalpointss <= $restrictuserpoints) {
                                                $voucherpoints = $findedarray[0]['points'];
                                                $translatedstring = $_POST['redeemvouchercode'];
                                                $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                                $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                                RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                                $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                                $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, $equredeemamt, '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                                $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                                $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                                $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                                $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                                echo addslashes($rs_voucher_redeem_success_message_replaced);
                                            } else {
                                                $insertpoints = $restrictuserpoints - $getoldpoints;
                                                RSPointExpiry::insert_earning_points(get_current_user_id(), $insertpoints, '0', $date, 'MREPFU', $order_id, $totalearnedpoints, $totalredeempoints, '');
                                                $equearnamt = RSPointExpiry::earning_conversion_settings($insertpoints);
                                                $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                RSPointExpiry::record_the_points(get_current_user_id(), $insertpoints, '0', $date, 'MREPFU', $equearnamt, $equredeemamt, $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                            }
                                        } else {
                                            RSPointExpiry::insert_earning_points(get_current_user_id(), '0', '0', $date, 'MREPFU', $order_id, '0', '0', '');
                                            $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            RSPointExpiry::record_the_points(get_current_user_id(), '0', '0', $date, 'MREPFU', '0', '0', $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                        }
                                    } else {
                                        $voucherpoints = $findedarray[0]['points'];
                                        $translatedstring = $_POST['redeemvouchercode'];
                                        $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                        $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                        RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                        $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                        $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                        RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                        $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                        $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                        $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                        $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                        echo addslashes($rs_voucher_redeem_success_message_replaced);
                                    }
                                } else {
                                    $voucherpoints = $findedarray[0]['points'];
                                    $translatedstring = $_POST['redeemvouchercode'];
                                    $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                    $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                    RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                    $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                    $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                    RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                    $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                    $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                    $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                    $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                    echo addslashes($rs_voucher_redeem_success_message_replaced);
                                }
                                $updates1 = array(
                                    array(
                                        $_POST['redeemvouchercode'] => array('points' => $voucherpoints, 'vouchercode' => $_POST['redeemvouchercode'], 'vouchercreated' => $vouchercreated, 'voucherexpiry' => $exp_date, 'memberused' => get_current_user_id())
                                    ),
                                );
                                $array11 = $newone1;
                                $array21 = $updates1;
                                $array31 = array_merge((array) $array11, (array) $array21);
                                update_option('rs_list_of_gift_vouchers_created', array_filter($array31));
                            } else {
                                echo addslashes(get_option('rs_voucher_code_expired_error_message'));
                            }
                        } else {
                            // Coupon Never Expired
                            if ($enabledisablemaxpoints == 'yes') {
                                if (($restrictuserpoints != '') && ($restrictuserpoints != '0')) {
                                    $getoldpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                    if ($getoldpoints <= $restrictuserpoints) {
                                        $totalpointss = $getoldpoints + $voucherpoints;
                                        if ($totalpointss <= $restrictuserpoints) {
                                            $voucherpoints = $findedarray[0]['points'];
                                            $translatedstring = $_POST['redeemvouchercode'];
                                            $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                            $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                            RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                            $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                            $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                            $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, $equredeemamt, '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                            $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                            $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                            $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                            $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                            echo addslashes($rs_voucher_redeem_success_message_replaced);
                                        } else {
                                            $insertpoints = $restrictuserpoints - $getoldpoints;
                                            RSPointExpiry::insert_earning_points(get_current_user_id(), $insertpoints, '0', $date, 'MREPFU', $order_id, $totalearnedpoints, $totalredeempoints, '');
                                            $equearnamt = RSPointExpiry::earning_conversion_settings($insertpoints);
                                            $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                            $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            RSPointExpiry::record_the_points(get_current_user_id(), $insertpoints, '0', $date, 'MREPFU', $equearnamt, $equredeemamt, $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                        }
                                    } else {
                                        RSPointExpiry::insert_earning_points(get_current_user_id(), '0', '0', $date, 'MREPFU', $order_id, '0', '0', '');
                                        $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                        RSPointExpiry::record_the_points(get_current_user_id(), '0', '0', $date, 'MREPFU', '0', '0', $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                    }
                                } else {
                                    $voucherpoints = $findedarray[0]['points'];
                                    $translatedstring = $_POST['redeemvouchercode'];
                                    $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                    $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                    RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                    $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                    $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                    RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                    $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                    $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                    $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                    $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                    echo addslashes($rs_voucher_redeem_success_message_replaced);
                                }
                            } else {
                                $voucherpoints = $findedarray[0]['points'];
                                $translatedstring = $_POST['redeemvouchercode'];
                                $redeempoints = RSFunctionToApplyCoupon::update_redeem_reward_points_to_user('0', get_current_user_id());
                                $pointsredeemed = RSPointExpiry::perform_calculation_with_expiry($redeempoints, get_current_user_id());
                                RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, '0', $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                echo addslashes($rs_voucher_redeem_success_message_replaced);
                            }
                            $updates1 = array(
                                array(
                                    $_POST['redeemvouchercode'] => array('points' => $voucherpoints, 'vouchercode' => $_POST['redeemvouchercode'], 'vouchercreated' => $vouchercreated, 'voucherexpiry' => $exp_date, 'memberused' => get_current_user_id())
                                ),
                            );
                            $array11 = $newone1;
                            $array21 = $updates1;
                            $array31 = array_merge((array) $array11, (array) $array21);
                            update_option('rs_list_of_gift_vouchers_created', array_filter($array31));
                        }
                        $updates = array(
                            array(
                                $_POST['redeemvouchercode'] => array('points' => $voucherpoints, 'vouchercode' => $_POST['redeemvouchercode'], 'vouchercreated' => $vouchercreated, 'voucherexpiry' => $exp_date, 'memberused' => get_current_user_id(), 'voucherused' => '1')
                            ),
                        );

                        $array1 = $newone;
                        $array2 = $updates;
                        $array3 = array_merge((array) $array1, (array) $array2);
                        update_option('rsvoucherlists', array_filter($array3));
                    } else {
                        echo addslashes(get_option('rs_voucher_code_used_error_message'));
                    }
                }
            }
        } else {
            echo addslashes(get_option('rs_banned_user_redeem_voucher_error'));
        }
        do_action('fp_reward_point_for_using_gift_voucher');
        exit();
    }

    public static function delete_array_keys_rs_point_vouchers() {
        if (isset($_POST['deletecode'])) {
            $checkifexists = get_option('rsvoucherlists');
            if (!empty($checkifexists)) {
                foreach (get_option('rsvoucherlists') as $updates) {
                    if (array_key_exists($_POST['deletecode'], $updates)) {
                        unset($updates);
                    }
                    $newupdates[] = $updates;
                }
                $new_array_without_nulls = array_filter($newupdates);
                update_option('rsvoucherlists', $new_array_without_nulls);
            }
        }
        exit();
    }

}

new RSFunctionForGiftVoucher();
