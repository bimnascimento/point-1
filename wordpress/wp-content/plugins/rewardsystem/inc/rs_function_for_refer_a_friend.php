<?php

class RSFunctionForReferAFriend {

    public function __construct() {

        //Register a Shortcode which was in admin settings

        add_shortcode('rs_refer_a_friend', array($this, 'reward_system_refer_a_friend_shortcode'));

        add_action('wp_ajax_nopriv_rs_refer_a_friend_ajax', array($this, 'reward_system_process_ajax_request'));

        add_action('wp_ajax_rs_refer_a_friend_ajax', array($this, 'reward_system_process_ajax_request'));
    }

    public static function reward_system_refer_a_friend_shortcode() {
        wp_enqueue_script('referfriend', false, array(), '', true);
        ob_start();
        ?>
        <style type="text/css">
            <?php echo get_option('rs_refer_a_friend_custom_css'); ?>;
        </style>
        <?php
        if (is_user_logged_in()) {
            ?>
            <form id="rs_refer_a_friend_form" method="post">
                <table class="shop_table my_account_referrals">
                    <tr>
                        <td><h3><?php echo addslashes(get_option('rs_my_rewards_friend_name_label')); ?></h3></td>
                        <td><input type="text" name="rs_friend_name" placeholder ="<?php echo addslashes(get_option('rs_my_rewards_friend_name_placeholder')); ?>" id="rs_friend_name" value=""/>
                            <br>
                            <div class="rs_notification"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><h3><?php echo addslashes(get_option('rs_my_rewards_friend_email_label')); ?></h3></td>
                        <td><input type="text" name="rs_friend_email" placeholder="<?php echo addslashes(get_option('rs_my_rewards_friend_email_placeholder')); ?>" id="rs_friend_email" value=""/>
                            <br>
                            <div class="rs_notification"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><h3><?php echo addslashes(get_option('rs_my_rewards_friend_subject_label')); ?></h3></td>
                        <td><input type="text" name="rs_friend_subject" id="rs_friend_subject" placeholder ="<?php echo addslashes(get_option('rs_my_rewards_friend_email_subject_placeholder')); ?>" value=""/>
                            <br>
                            <div class="rs_notification"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><h3><?php echo addslashes(get_option('rs_my_rewards_friend_message_label')); ?></h3></td>
                        <?php
                        $currentuserid = get_current_user_id();
                        $user_info = get_userdata($currentuserid);
                        if (get_option('rs_generate_referral_link_based_on_user') == '1') {
                            $referralperson = $user_info->user_login;
                        } else {
                            $referralperson = $currentuserid;
                        }
                        $friend_free_product_to_find = "[site_referral_url]";
                        $friend_free_product_to_replace = esc_url_raw(add_query_arg('ref', $referralperson, get_option('rs_prefill_generate_link')));
                        $friend_free_product_replaced = str_replace($friend_free_product_to_find, $friend_free_product_to_replace, addslashes(htmlentities(get_option('rs_friend_referral_link'))));
                        $referurl = $friend_free_product_replaced;
                        ?>
                        <td><textarea rows="5" cols="35" id="rs_your_message" placeholder ="<?php echo addslashes(get_option('rs_my_rewards_friend_email_message_placeholder')); ?>" name="rs_your_message"><?php echo $referurl; ?></textarea>
                            <br>
                            <div class="rs_notification"></div>
                        </td>
                    </tr>


                    <?php
                    $show = get_option('rs_show_hide_iagree_termsandcondition_field');
                    if ($show == '2') {
                        ?>    
                        <tr>
                            <td colspan="2">

                                <input type="checkbox" name="rs_terms"  id="rs_terms" /> 
                                <?php
                                $initialmessage = addslashes(get_option('rs_refer_friend_iagreecaption_link'));
                                $stringtofind = "{termsandconditions}";
                                $hyperlinkforterms = get_option('rs_refer_friend_termscondition_url');
                                $stringtoreplace = "<a href='$hyperlinkforterms'>" . addslashes(get_option('rs_refer_friend_termscondition_caption')) . "</a>";
                                $replacedcontent = str_replace($stringtofind, $stringtoreplace, $initialmessage);
                                echo $replacedcontent;
                                ?>  

                                <div class ="iagreeerror" style="display:none;"><?php echo addslashes(get_option('rs_iagree_error_message')); ?></div>

                            </td>
                        <br>
                        <div class="rs_notification"></div> 
                        </tr>
                    <?php } ?>    
                </table>    
                <input type="submit" class="button-primary" name="submit" id="rs_refer_submit" value="<?php _e('Send Mail', 'rewardsystem'); ?>"/>
                <div class="rs_notification_final"></div>
            </form>
            <?php
        } else {
            $myaccountlink = get_permalink(get_option('woocommerce_myaccount_page_id'));
            $myaccounttitle = get_the_title(get_option('woocommerce_myaccount_page_id'));
            $linkforlogin = add_query_arg('redirect_to',get_permalink(),$myaccountlink);
            echo 'Please Login to View this Page <a href=' . $linkforlogin . '> Login </a>';
        }
        $maincontent = ob_get_clean();
        return $maincontent;
    }

    public static function reward_system_process_ajax_request() {
        global $woocommerce;
        if (isset($_POST)) {
            if (isset($_POST['friendname'])) {
                $friendname = $_POST['friendname'];
            }
            if (isset($_POST['friendemail'])) {
                $friendemail = $_POST['friendemail'];
            }
            if (isset($_POST['friendsubject'])) {
                $friendsubject = $_POST['friendsubject'];
            }
            if (isset($_POST['friendmessage'])) {
                
            }
            $name_n = explode(",", $friendname);
            $email_n = explode(",", $friendemail);
            foreach ($email_n as $key => $value) {
                $friendmessage = __('Hi ', 'rewardsystem') . $name_n[$key] . '<br>';
                $friendmessage .= $_POST['friendmessage'];
                ob_start();
                wc_get_template('emails/email-header.php', array('email_heading' => $friendsubject));
                echo wpautop(stripslashes($friendmessage));
                wc_get_template('emails/email-footer.php');
                $woo_rs_msg = ob_get_clean();
                $headers = "MIME-Version: 1.0\r\n";
                $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                $headers .= "From: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
                $headers .= "Reply-To: " . get_option('woocommerce_email_from_name') . " <" . get_option('woocommerce_email_from_address') . ">\r\n";
                if (get_option('rs_select_mail_function') == '1') {
                    mail($value, $friendsubject, $woo_rs_msg, $headers);
                } else {
                    if ((float) $woocommerce->version <= (float) ('2.2.0')) {
                        wp_mail($value, $friendsubject, $woo_rs_msg, $headers);
                    } else {
                        $mailer = WC()->mailer();
                        $mailer->send($value, $friendsubject, $woo_rs_msg, $headers);
                    }
                }
                error_reporting(E_ALL);
                ini_set('display_errors', '1');
            }
        }
        exit();
    }

}

new RSFunctionForReferAFriend();
