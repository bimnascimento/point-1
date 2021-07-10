<?php

class RSFunctionForOfflineOnlineRewards {

    public function __construct() {

        add_action('woocommerce_admin_field_rs_offline_online_rewards_voucher_settings', array($this, 'settings_for_voucher_code_offline_online_rewards'));

        add_action('wp_ajax_rs_create_voucher_code', array($this, 'process_ajax_to_create_voucher_code'));

        add_action('wp_ajax_rssplitvouchercode', array($this, 'process_to_split_voucher_codes'));

        add_action('woocommerce_admin_field_rs_offline_online_rewards_display_table_settings', array($this, 'table_to_display_created_voucher_codes'));

        add_shortcode('sumo_code_field', array($this, 'rs_form_for_redeeming_voucher_codes'));

        add_shortcode('sumo_current_balance', array($this, 'rs_function_for_current_available_points'));

        add_action('wp_ajax_delete_reward_code', array($this, 'delete_array_keys_rs_point_vouchers'));

        add_action('admin_head', array($this, 'import_giftvoucher'));
    }

    public static function import_giftvoucher() {
        $get_gift_voucher = '';
        $get_option = get_option('once_create_gift');
        if ($get_option != '2') {
            $get_gift_voucher = get_option('rsvoucherlists');
            if (is_array($get_gift_voucher)) {
                if (!empty($get_gift_voucher)) {
                    foreach ($get_gift_voucher as $farray) {
                        foreach ($farray as $variavble1) {
                            if ($variavble1['voucherexpiry'] != '') {
                                $voucherexpiry = $variavble1['voucherexpiry'];
                            } else {
                                $voucherexpiry = '';
                            }
                            if ($variavble1['memberused'] != '') {
                                $username = $variavble1['memberused'];
                            } else {
                                $username = '';
                            }
                            $newupdates[$variavble1['vouchercode']] = array(
                                $variavble1['vouchercode'] => array('points' => $variavble1['points'], 'vouchercode' => $variavble1['vouchercode'], 'vouchercreated' => $variavble1['vouchercreated'], 'voucherexpiry' => $voucherexpiry, 'memberused' => $username)
                            );
                        }
                    }
                    $array3 = array_merge((array) get_option('rs_list_of_gift_vouchers_created'), $newupdates);

                    $array3 = array_map("unserialize", array_unique(array_map("serialize", $array3)));
                    update_option('rs_list_of_gift_vouchers_created', array_filter($array3));
                    update_option('once_create_gift', '2');
                }
            }
        }
        if (isset($_POST['rs_import_reward_codes_csv_from_old'])) {
            if (isset($_POST['rs_voucher_code_import_type'])) {
                if ($_POST['rs_voucher_code_import_type'] == '1') {
                    if ($_FILES["file"]["error"] > 0) {
                        echo "Error: " . $_FILES["file"]["error"] . "<br>";
                    } else {
                        $mimes = array('text/csv',
                            'text/plain',
                            'application/csv',
                            'text/comma-separated-values',
                            'application/excel',
                            'application/vnd.ms-excel',
                            'application/vnd.msexcel',
                            'text/anytext',
                            'application/octet-stream',
                            'application/txt');
                        if (in_array($_FILES['file']['type'], $mimes)) {

                            self::inputCSV($_FILES["file"]["tmp_name"]);
                        } else {
                            ?>
                            <style type="text/css">
                                div.error {
                                    display:block;
                                }
                            </style>
                            <?php
                        }
                    }
                    $myurl = get_permalink();
                } else {
                    if ($_POST['rs_voucher_code_import_type'] == '2') {
                        if ($_FILES["file"]["error"] > 0) {
                            echo "Error: " . $_FILES["file"]["error"] . "<br>";
                        } else {
                            $mimes = array('text/csv',
                                'text/plain',
                                'application/csv',
                                'text/comma-separated-values',
                                'application/excel',
                                'application/vnd.ms-excel',
                                'application/vnd.msexcel',
                                'text/anytext',
                                'application/octet-stream',
                                'application/txt');
                            if (in_array($_FILES['file']['type'], $mimes)) {
                                self::inputCSV1($_FILES["file"]["tmp_name"]);
                            } else {
                                ?>
                                <style type="text/css">
                                    div.error {
                                        display:block;
                                    }
                                </style>
                                <?php
                            }
                        }
                        $myurl = get_permalink();
                    }
                }
            }
        }
    }

    public static function inputCSV1($data_path) {
        global $wpdb;
        $row = 1;
        if (($handle = fopen($data_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $datas = strtotime($data[2]);
                $datass = isset($data[2]) ? $datas : 999999999999;
                if ($data[3] != 'Never') {
                    if ($data[3] != '') {
                        $datasss = strtotime($data[3]);
                    } else {
                        $datasss = '';
                    }
                } else {
                    $datasss = '';
                }
                $expired = $datasss;
                $usedby = isset($data[4]) ? $data[4] : '';

                $collection[] = array($data[0], $data[1], $datass, $expired, $usedby);
            }
            foreach ($collection as $variavble1) {
                if ($variavble1[0] != '') {
                    $create_date = date_i18n('Y-m-d', $variavble1[2]);
                    if (isset($variavble1[3])) {
                        if ($variavble1[3] != '') {
                            $expired_date = date_i18n('Y-m-d', $variavble1[3]);
                        } else {
                            $expired_date = 'Never';
                        }
                    } else {
                        $expired_date = '';
                    }
                    if ($variavble1[4] != 'Notyet') {
                        if ($variavble1[4] != '') {
                            $getusermeta1 = $wpdb->get_results("SELECT `ID` FROM `wp_users` WHERE `user_login`='$variavble1[4]' ", ARRAY_A);
                            if (!empty($getusermeta1)) {
                                foreach ($getusermeta1 as $userid) {
                                    $user = $userid['ID'];
                                }
                            } else {
                                $user = '';
                            }
                        } else {
                            $user = '';
                        }
                    } else {
                        $user = '';
                    }
                    $newupdates[$variavble1[0]] = array(
                        $variavble1[0] => array('points' => $variavble1[1], 'vouchercode' => $variavble1[0], 'vouchercreated' => $create_date, 'voucherexpiry' => $expired_date, 'memberused' => $user)
                    );
                }
            }
            update_option('rs_list_of_gift_vouchers_created_import', $newupdates);
            $array3 = array_merge((array) get_option('rs_list_of_gift_vouchers_created'), $newupdates);
            $duplicate = self::get_keys_for_duplicate_values($array3);
            if (is_array($duplicate) && !empty($duplicate)) {
                foreach ($duplicate as $key => $duplicatekey) {
                    foreach ($array3 as $key => $keyunset) {
                        if (isset($keyunset[$duplicatekey])) {
                            unset($array3[$key]);
                        }
                    }
                    update_option('rs_list_of_gift_vouchers_created', $array3);
                }
                $array3 = array_merge(get_option('rs_list_of_gift_vouchers_created_import'), (array) get_option('rs_list_of_gift_vouchers_created'));
            }
            $array3 = array_map("unserialize", array_unique(array_map("serialize", $array3)));
            update_option('rs_list_of_gift_vouchers_created', array_filter($array3));
            fclose($handle);
        }
    }

    /*
     * Voucher  code creation settings
     * 
     */

    public static function settings_for_voucher_code_offline_online_rewards() {
        ?>
        <h3><?php _e('Voucher Code Settings', 'rewardsystem'); ?></h3>
        <table class="form-table">
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Prefix/Suffix', 'rewardsystem'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="checkbox" name="rs_enable_prefix_offline_online_rewards" class="rs_enable_prefix_offline_online_rewards"><span><?php _e('Prefix', 'rewardsystem'); ?></span>
                    <input type="text" name="rs_voucher_prefix_offline_online" class="rs_voucher_prefix_offline_online" />
                    <span class="rs_voucher_code_creation_error_for_prefix"></span><br><br>
                    <input type="checkbox" name="rs_enable_suffix_offline_online_rewards" class="rs_enable_suffix_offline_online_rewards"><span><?php _e('Suffix', 'rewardsystem'); ?></span>
                    <input type="text" name="rs_voucher_suffix_offline_online" class="rs_voucher_suffix_offline_online" />
                    <span class="rs_voucher_code_creation_error_for_suffix"></span>
                </td>
            </tr>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Select Voucher Code Type', 'rewardsystem'); ?></label><br/><br/>
                </th>
                <td class="forminp forminp-select">
                    <select name="rs_reward_code_type" id="rs_reward_code_type">
                        <option value="numeric"><?php _e('Numeric', 'rewardsystem'); ?></option>
                        <option value="alphanumeric"><?php _e('Alphanumeric', 'rewardsystem'); ?></option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label class="rs_exclude_characters_code_generation_label"><?php _e('Exclude alphabets for Voucher Code Creation', 'rewardsystem'); ?></label>                    
                </th>
                <td class="forminp forminp-select">
                    <input type="text" name="rs_exclude_characters_code_generation" class="rs_exclude_characters_code_generation" />
                    <label class="exclude_caption">Alphabets are comma separated(For eg: i,l,o)</label>
                </td>
            </tr>            
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Number of Characters for Voucher Code', 'rewardsystem'); ?></label>                    
                </th>
                <td class="forminp forminp-select">
                    <input type="number" name="rs_voucher_code_length_offline_online" class="rs_voucher_code_length_offline_online" />
                    <span class="rs_voucher_code_creation_error_for_character"></span>
                </td>
            </tr>            
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Reward Points Value per Voucher Code', 'rewardsystem'); ?></label>                    
                </th>
                <td class="forminp forminp-select">
                    <input type="number" step="0.01" name="rs_voucher_code_points_value_offline_online" class="rs_voucher_code_points_value_offline_online" />
                    <span class="rs_voucher_code_creation_error_for_rpv"></span>
                </td>
            </tr>            
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Number of Voucher Codes to be Generate', 'rewardsystem'); ?></label>                    
                </th>
                <td class="forminp forminp-select">
                    <input type="number" name="rs_voucher_code_count_offline_online" class="rs_voucher_code_count_offline_online" />
                    <span class="rs_voucher_code_creation_error_for_noofrc"></span>
                </td>
            </tr>            
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label><?php _e('Expired Date For gift', 'rewardsystem'); ?></label>                    
                </th>
                <td class="forminp forminp-select">
                    <input type="text" class="rs_gift_voucher_expiry" value="" name="rs_gift_voucher_expiry" id="rs_gift_voucher_expiry" />
                    <span class="rs_voucher_code_creation_error_for_expdate"></span>
                </td>
            </tr>            
        </table>      

        <div id="dialog1" hidden="hidden" ></div>

        <button class="button-primary rs_create_voucher_codes_offline_online" ><?php _e('Create Voucher Codes', 'rewardsystem'); ?></button>            
        <span class="preloader_image_online_offline_rewards"><img src="<?php echo WP_PLUGIN_URL; ?>/rewardsystem/img/update.gif" style="width:32px;height:32px; position: absolute;"/></span><br/><br/>        
        <style type="text/css">
            .rs_voucher_code_creation_error_for_character{
                font-size: 16px;
                color: red;
            }
            .rs_voucher_code_creation_error_for_rpv{
                font-size: 16px;
                color: red;
            }

            .rs_voucher_code_creation_error_for_noofrc{
                font-size: 16px;
                color: red;
            }

            .rs_voucher_code_creation_error_for_expdate{
                font-size: 16px;
                color: red;
            }

            .rs_voucher_code_creation_error_for_prefix{
                font-size: 16px;
                color: red;
            }

            .rs_voucher_code_creation_error_for_suffix{
                font-size: 16px;
                color: red;
            }
        </style>    
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">

        <script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>


        <script type="text/javascript">

            jQuery(document).ready(function () {
                jQuery("#opener").click(function () {



                });

                jQuery('#rs_gift_voucher_expiry').datepicker({dateFormat: 'yy-mm-dd', minDate: 0});
                jQuery('.preloader_image_online_offline_rewards').css("display", "none");
                var reward_code_type = jQuery('#rs_reward_code_type').val();
                if (reward_code_type == 'numeric') {
                    jQuery('.rs_exclude_characters_code_generation_label').closest('tr').hide();
                } else {
                    jQuery('.rs_exclude_characters_code_generation_label').closest('tr').show();
                }

                jQuery('#rs_reward_code_type').change(function () {
                    if (jQuery(this).val() == 'numeric') {
                        jQuery('.rs_exclude_characters_code_generation_label').closest('tr').hide();
                    } else {
                        jQuery('.rs_exclude_characters_code_generation_label').closest('tr').show();
                    }
                });

                jQuery('.rs_create_voucher_codes_offline_online').click(function () {


                    var prefix_enabled_value = jQuery('.rs_enable_prefix_offline_online_rewards').is(":checked") ? 'yes' : 'no';
                    var prefix_content = jQuery('.rs_voucher_prefix_offline_online').val();
                    var suffix_enabled_value = jQuery('.rs_enable_suffix_offline_online_rewards').is(":checked") ? 'yes' : 'no';
                    var sufffix_content = jQuery('.rs_voucher_suffix_offline_online').val();
                    var reward_code_type = jQuery('#rs_reward_code_type').val();
                    var exclude_content_code = jQuery('.rs_exclude_characters_code_generation').val();
                    var length_of_voucher_code = jQuery('.rs_voucher_code_length_offline_online').val();
                    var points_value_of_voucher_code = jQuery('.rs_voucher_code_points_value_offline_online').val();
                    var number_of_vouchers_to_be_created = jQuery('.rs_voucher_code_count_offline_online').val();
                    var gift_expired_date = jQuery('#rs_gift_voucher_expiry').val();
                    jQuery(this).attr('data-clicked', '1');
                    var dataclicked = jQuery(this).attr('data-clicked');
                    var dataparam = ({
                        action: 'rs_create_voucher_code',
                        proceedanyway: dataclicked,
                        prefix_enabled_value: prefix_enabled_value,
                        prefix_content: prefix_content,
                        suffix_enabled_value: suffix_enabled_value,
                        number_of_vouchers_to_be_created: number_of_vouchers_to_be_created,
                        sufffix_content: sufffix_content,
                        reward_code_type: reward_code_type,
                        length_of_voucher_code: length_of_voucher_code,
                        points_value_of_voucher_code: points_value_of_voucher_code,
                        exclude_content_code: exclude_content_code,
                        vouchercreated: '<?php echo date('Y-m-d'); ?>',
                        gift_expired_date: gift_expired_date,
                    });
                    if (prefix_enabled_value === 'yes' && suffix_enabled_value === 'yes') {
                        if (prefix_content === '') {
                            jQuery('.rs_voucher_code_creation_error_for_prefix').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_prefix').html('<?php _e('Prefix value Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_prefix').fadeOut(5000);
                            return false;
                        }

                        if (sufffix_content === '') {
                            jQuery('.rs_voucher_code_creation_error_for_suffix').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_suffix').html('<?php _e('Suffix value Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_suffix').fadeOut(5000);
                            return false;
                        }
                        if (length_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_character').html('<?php _e('Number of Characters for Voucher Code Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeOut(5000);
                            return false;
                        }
                        if (points_value_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_rpv').html('<?php _e('Reward Points Value per Voucher Code Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeOut(5000);
                            return false;
                        }
                        if (number_of_vouchers_to_be_created === '') {
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').html('<?php _e('Number of Voucher Codes to be Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeOut(5000);
                            return false;
                        }
                    } else if (prefix_enabled_value != 'yes' && suffix_enabled_value != 'yes') {
                        if (length_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_character').html('<?php _e('Number of Characters for Voucher Code Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeOut(5000);
                            return false;
                        }
                        if (points_value_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_rpv').html('<?php _e('Reward Points Value per Voucher Code Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeOut(5000);
                            return false;
                        }
                        if (number_of_vouchers_to_be_created === '') {
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').html('<?php _e('Number of Voucher Codes to be Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeOut(5000);
                            return false;
                        }
                    } else if (prefix_enabled_value == 'yes' && suffix_enabled_value != 'yes') {
                        if (prefix_content == '') {
                            jQuery('.rs_voucher_code_creation_error_for_prefix').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_prefix').html('<?php _e('Prefix value Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_prefix').fadeOut(5000);
                            return false;
                        }
                        if (length_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_character').html('<?php _e('Number of Characters for Voucher Code Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeOut(5000);
                            return false;
                        }
                        if (points_value_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_rpv').html('<?php _e('Reward Points Value per Voucher Code Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeOut(5000);
                            return false;
                        }
                        if (number_of_vouchers_to_be_created === '') {
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').html('<?php _e('Number of Voucher Codes to be Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeOut(5000);
                            return false;
                        }
                    } else if (prefix_enabled_value != 'yes' && suffix_enabled_value == 'yes') {
                        if (sufffix_content == '') {
                            jQuery('.rs_voucher_code_creation_error_for_suffix').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_suffix').html('<?php _e('Suffix value Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_suffix').fadeOut(5000);
                            return false;
                        }
                        if (length_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_character').html('<?php _e('Number of Characters for Voucher Code Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_character').fadeOut(5000);
                            return false;
                        }
                        if (points_value_of_voucher_code === '') {
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_rpv').html('<?php _e('Reward Points Value per Voucher Code Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_rpv').fadeOut(5000);
                            return false;
                        }
                        if (number_of_vouchers_to_be_created === '') {
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeIn();
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').html('<?php _e('Number of Voucher Codes to be Generated Should not be Empty', 'rewardsystem'); ?>');
                            jQuery('.rs_voucher_code_creation_error_for_noofrc').fadeOut(5000);
                            return false;
                        }
                    }
                    jQuery('.preloader_image_online_offline_rewards').css("display", "inline");

                    function getvouchercountofflinerewards(id) {
                        return jQuery.ajax({
                            type: 'POST',
                            url: "<?php echo admin_url('admin-ajax.php'); ?>",
                            data: ({
                                action: 'rssplitvouchercode',
                                ids: id,
                                prefix_enabled_value: prefix_enabled_value,
                                prefix_content: prefix_content,
                                suffix_enabled_value: suffix_enabled_value,
                                number_of_vouchers_to_be_created: number_of_vouchers_to_be_created,
                                sufffix_content: sufffix_content,
                                reward_code_type: reward_code_type,
                                length_of_voucher_code: length_of_voucher_code,
                                points_value_of_voucher_code: points_value_of_voucher_code,
                                exclude_content_code: exclude_content_code,
                                vouchercreated: '<?php echo date('Y-m-d'); ?>',
                                gift_expired_date: gift_expired_date,
                            }),
                            success: function (response) {
                                console.log(response);
                            },
                            dataType: 'json',
                            async: false
                        });

                    }

                    jQuery.post("<?php echo admin_url('admin-ajax.php'); ?>", dataparam,
                            function (response) {
                                console.log(response);

                                if (response != 'success') {
                                    var j = 1;
                                    var i, j, temparray, chunk = 10;
                                    for (i = 0, j = response.length; i < j; i += chunk) {
                                        temparray = response.slice(i, i + chunk);
                                        getvouchercountofflinerewards(temparray);
                                    }
                                    jQuery.when(getvouchercountofflinerewards()).done(function (a1) {
                                        var uniquekey = [];
                                        jQuery.each(response, function (i, el) {
                                            if (jQuery.inArray(el, uniquekey) === -1) {
                                                uniquekey.push(el);
                                            }
                                        });
                                        if (number_of_vouchers_to_be_created > uniquekey.length + 1) {

                                            jQuery("#dialog1").dialog({
                                                buttons: [
                                                    {
                                                        text: "Ok",
                                                        icons: {
                                                            primary: "ui-icon-heart"
                                                        },
                                                        click: function () {
                                                            jQuery(this).dialog("close");
                                                            location.reload();
                                                        }

                                                    }
                                                ]

                                            });
                                            jQuery('div#dialog1').on('dialogclose', function () {
                                                location.reload();
                                            });
                                            jQuery("#dialog1").html(+uniquekey.length + 'Unique code is Generated Please Increase number of Character to Create More Voucher');
                                        } else {
                                            location.reload();
                                        }
                                        jQuery('.rs_voucher_prefix_offline_online').val('');
                                        jQuery('.rs_voucher_suffix_offline_online').val('');
                                        jQuery('#rs_reward_code_type').val('');
                                        jQuery('.rs_exclude_characters_code_generation').val('');
                                        jQuery('.rs_voucher_code_length_offline_online').val('');
                                        jQuery('.rs_voucher_code_points_value_offline_online').val('');
                                        jQuery('.rs_voucher_code_count_offline_online').val('');
                                        jQuery('#rs_gift_voucher_expiry').val('');
                                        console.log('Ajax Done Successfully');
                                        jQuery('.preloader_image_online_offline_rewards').css("display", "none");

                                    });


                                }
                            }, 'json');
                    return false;
                });
            });
        </script>    
        <?php
    }

    public static function function_to_generate_random_code($array) {
        $reward_code_type = $array['reward_code_type'];
        $number_of_charaters_for_voucher_code = $array['characters_for_code'];
        $number_of_points_for_voucher_code = $array['point_for_code'];
        $number_of_vouchers_to_be_generated = $array['no_of_vouchercodes'];
        $gift_expired_date = $array['expiry_date'];
        $prefix_coupon_value = isset($array['prefix_coupon_value']) ? $array['prefix_coupon_value'] : '';
        $suffix_coupon_value = isset($array['suffix_coupon_value']) ? $array['suffix_coupon_value'] : '';
        $exclude_content_code = $array['exclude_content'];
        if ($reward_code_type == 'numeric') {
            if ($number_of_charaters_for_voucher_code != "" && $number_of_points_for_voucher_code != '' && $number_of_vouchers_to_be_generated != '') {
                $number_of_times = (int) $number_of_vouchers_to_be_generated;
                for ($k = 0; $k < $number_of_times; $k++) {
                    $random_code = '';
                    $check_duplicate_random_codes = array();
                    for ($j = 1; $j <= $number_of_charaters_for_voucher_code; $j++) {
                        $random_code .= rand(0, 9);
                    }
                    $random_codes[] = $prefix_coupon_value . $random_code . $suffix_coupon_value;
                }
            }
        } else {
            if ($number_of_charaters_for_voucher_code != "" && $number_of_points_for_voucher_code != '' && $number_of_vouchers_to_be_generated != '') {
                $list_of_characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $character_length = strlen($list_of_characters);
                $number_of_times = (int) $number_of_vouchers_to_be_generated;
                for ($k = 0; $k < $number_of_times; $k++) {
                    $randomstring = '';
                    for ($j = 1; $j <= $number_of_charaters_for_voucher_code; $j++) {
                        $randomstring .= $list_of_characters[rand(0, $character_length - 1)];
                    }
                    if ($exclude_content_code != "") {
                        $exclude_string = explode(",", $exclude_content_code);
                        $new_array = array();
                        foreach ($exclude_string as $value) {
                            $new_array[$value] = rand(0, 9);
                        }

                        $randomstring = strtr($randomstring, $new_array);
                        $random_codes[] = $prefix_coupon_value . $randomstring . $suffix_coupon_value;
                    } else {
                        $random_codes[] = $prefix_coupon_value . $randomstring . $suffix_coupon_value;
                    }
                }
            }
        }
        return $random_codes;
    }

    public static function process_ajax_to_create_voucher_code() {

        if (isset($_POST['proceedanyway'])) {
            if ($_POST['proceedanyway'] == '1') {

                $updated_voucher_code_new = array();

                $prefix_enabled_for_voucher = $_POST['prefix_enabled_value'];

                $prefix_coupon_value = $_POST['prefix_content'];

                $suffix_enabled_for_voucher = $_POST['suffix_enabled_value'];

                $suffix_coupon_value = $_POST['sufffix_content'];

                $reward_code_type = $_POST['reward_code_type'];

                $exclude_content_code = $_POST['exclude_content_code'];

                $number_of_charaters_for_voucher_code = $_POST['length_of_voucher_code'];

                $number_of_points_for_voucher_code = $_POST['points_value_of_voucher_code'];

                $number_of_vouchers_to_be_generated = $_POST['number_of_vouchers_to_be_created'] ? $_POST['number_of_vouchers_to_be_created'] : '';

                $gift_expired_date = $_POST['gift_expired_date'];

                $voucher_created_date = $_POST['vouchercreated'];

                if ($prefix_enabled_for_voucher == 'yes' && $suffix_enabled_for_voucher == 'yes') {
                    if ($prefix_coupon_value != '' && $suffix_coupon_value != '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                            'prefix_coupon_value' => $prefix_coupon_value,
                            'suffix_coupon_value' => $suffix_coupon_value,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    } elseif ($prefix_coupon_value == '' && $suffix_coupon_value == '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    } elseif ($prefix_coupon_value != '' && $suffix_coupon_value == '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                            'prefix_coupon_value' => $prefix_coupon_value,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    } elseif ($prefix_coupon_value == '' && $suffix_coupon_value != '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                            'suffix_coupon_value' => $suffix_coupon_value,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    }
                } elseif ($prefix_enabled_for_voucher != 'yes' && $suffix_enabled_for_voucher != 'yes') {
                    $value_to_generate_randomcode = array(
                        'reward_code_type' => $reward_code_type,
                        'characters_for_code' => $number_of_charaters_for_voucher_code,
                        'point_for_code' => $number_of_points_for_voucher_code,
                        'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                        'expiry_date' => $gift_expired_date,
                        'exclude_content' => $exclude_content_code,
                    );
                    $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                } elseif ($prefix_enabled_for_voucher == 'yes' && $suffix_enabled_for_voucher != 'yes') {
                    if ($prefix_coupon_value != '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                            'prefix_coupon_value' => $prefix_coupon_value,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    } else {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    }
                } elseif ($prefix_enabled_for_voucher != 'yes' && $suffix_enabled_for_voucher == 'yes') {
                    if ($suffix_coupon_value != '') {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                            'suffix_coupon_value' => $suffix_coupon_value,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    } else {
                        $value_to_generate_randomcode = array(
                            'reward_code_type' => $reward_code_type,
                            'characters_for_code' => $number_of_charaters_for_voucher_code,
                            'point_for_code' => $number_of_points_for_voucher_code,
                            'no_of_vouchercodes' => $number_of_vouchers_to_be_generated,
                            'expiry_date' => $gift_expired_date,
                            'exclude_content' => $exclude_content_code,
                        );
                        $random_codes = self::function_to_generate_random_code($value_to_generate_randomcode);
                    }
                }
                echo json_encode($random_codes);
            }
            exit();
        }
    }

    public static function process_to_split_voucher_codes() {
        if (isset($_POST['ids']) && !empty($_POST['ids'])) {
            $voucher_code = $_POST['ids'];
            $gift_expired_date = $_POST['gift_expired_date'];
            $voucher_created_date = $_POST['vouchercreated'];
            $number_of_points_for_voucher_code = $_POST['points_value_of_voucher_code'];
            foreach ($voucher_code as $each_code) {
                $newupdates[$each_code] = array(
                    $each_code => array(
                        'points' => $number_of_points_for_voucher_code,
                        'vouchercode' => $each_code,
                        'vouchercreated' => $voucher_created_date,
                        'voucherexpiry' => $gift_expired_date,
                        'memberused' => '')
                );
            }
            $old_data = get_option('rs_list_of_gift_vouchers_created') != false ? get_option('rs_list_of_gift_vouchers_created') : array();
            if (!empty($old_data)) {
                $merged_array = array_merge($old_data, $newupdates);
                update_option('rs_list_of_gift_vouchers_created', array_filter($merged_array));
            } else {
                update_option('rs_list_of_gift_vouchers_created', array_filter($newupdates));
            }

            echo json_encode('sucess');
        }
        exit();
    }

    public static function table_to_display_created_voucher_codes() {
        ?>
        <style type="text/css">
            .rs_reward_code_vouchers_click {

                border: 2px solid #a1a1a1;
                padding: 3px 9px;
                background: #dddddd;
                width: 5px;
                border-radius: 25px;
            }

            .rs_reward_code_vouchers_click:hover {
                cursor: pointer;
                background:red;
                color:#fff;
                border: 2px solid #fff;
            }
        </style>
        <table class="form-table">
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rs_import_gift_voucher_csv"><?php _e('Export Voucher Codes as CSV', 'rewardsystem'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="submit" id="rs_export_reward_codes_csv" name="rs_export_reward_codes_csv" value="Export Voucher Codes"/>
                </td>

            </tr>

            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rs_import_gift_voucher_csv"><?php _e('Import Gift voucher to CSV', 'rewardsystem'); ?></label>
                </th>
                <td class="forminp forminp-select">
                    <input type="file" id="rs_import_gift_voucher_csv" name="file" />
                </td>
            </tr>
            <tr valign="top">
                <th class="titledesc" scope="row">
                    <label for="rs_voucher_code_import_type"><?php _e('If Voucher Code already exists', 'rewardsystem'); ?></label>
                </th>
                <td class="forminp forminp-select">                
                    <select id ="rs_voucher_code_import_type" class="rs_voucher_code_import_type" name="rs_voucher_code_import_type">
                        <option value="1"><?php _e('Ignore Voucher Code', 'rewardsystem'); ?>  </option>
                        <option value="2"><?php _e('Replace Voucher Code', 'rewardsystem'); ?>  </option>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <td class="forminp forminp-select">
                    <input type="submit" id="rs_import_reward_codes_csv_from_old" name="rs_import_reward_codes_csv_from_old" value="Import Voucher Codes as CSV "/>
                </td>

            </tr>            
        </table>
        <?php
        if (isset($_POST['rs_export_reward_codes_csv'])) {
            $get_list_of_reward_codes = get_option('rs_list_of_gift_vouchers_created');
            $coupon_info = array();
            foreach ($get_list_of_reward_codes as $each_reward_code) {
                foreach ($each_reward_code as $code_info) {
                    $voucher_code = $code_info['vouchercode'];
                    $voucher_amount = $code_info['points'];
                    $voucher_created_date = $code_info['vouchercreated'];
                    $voucher_used_count = $code_info['memberused'] != "" ? get_user_by("id", $code_info['memberused'])->user_login : "Notyet";

                    if ($code_info['voucherexpiry'] != '') {
                        $voucher_expired_date = $code_info['voucherexpiry'];
                    } else {
                        $voucher_expired_date = 'Never';
                    }
                    $voucher_info_array[] = array($voucher_code, $voucher_amount, $voucher_created_date, $voucher_expired_date, $voucher_used_count);
                }
            }
            ob_end_clean();
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=list_of_reward_codes" . date("Y-m-d") . ".csv");
            header("Pragma: no-cache");
            header("Expires: 0");
            RSFunctionForMasterLog::outputCSV($voucher_info_array);
            exit();
        }

        $newwp_list_table_for_users = new WP_List_Table_for_NewGiftVoucher();
        $newwp_list_table_for_users->prepare_items();
        $plugin_url = WP_PLUGIN_URL;
        $newwp_list_table_for_users->search_box('Search', 'search_id');
        $newwp_list_table_for_users->display();
    }

    public static function inputCSV($data_path) {
        global $wpdb;
        $row = 1;
        if (($handle = fopen($data_path, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $row++;
                $datas = strtotime($data[2]);
                $datass = isset($data[2]) ? $datas : 999999999999;
                if ($data[3] != 'Never') {
                    if ($data[3] != '') {
                        $datasss = strtotime($data[3]);
                    } else {
                        $datasss = '';
                    }
                } else {
                    $datasss = '';
                }
                $expired = $datasss;
                $usedby = isset($data[4]) ? $data[4] : '';

                $collection[] = array($data[0], $data[1], $datass, $expired, $usedby);
            }

            $newupdates = array();
            foreach ($collection as $variavble1) {

                $create_date = date_i18n('Y-m-d', $variavble1[2]);
                if (isset($variavble1[3])) {
                    if ($variavble1[3] != '') {
                        $expired_date = date_i18n('Y-m-d', $variavble1[3]);
                    } else {
                        $expired_date = '';
                    }
                } else {
                    $expired_date = '';
                }
                if ($variavble1[4] != 'Notyet') {
                    if ($variavble1[4] != '') {
                        $getusermeta1 = $wpdb->get_results("SELECT `ID` FROM `wp_users` WHERE `user_login`='$variavble1[4]' ", ARRAY_A);
                        if (!empty($getusermeta1)) {
                            foreach ($getusermeta1 as $userid) {
                                $user = $userid['ID'];
                            }
                        } else {
                            $user = '';
                        }
                    } else {
                        $user = '';
                    }
                } else {
                    $user = '';
                }
                $newupdates[$variavble1[0]] = array(
                    $variavble1[0] => array('points' => $variavble1[1], 'vouchercode' => $variavble1[0], 'vouchercreated' => $create_date, 'voucherexpiry' => $expired_date, 'memberused' => $user)
                );
            }
            $get_value = get_option('rs_list_of_gift_vouchers_created');
            if (!empty($get_value)) {
                update_option('rs_list_of_gift_vouchers_created_import', get_option('rs_list_of_gift_vouchers_created'));
            } else {
                update_option('rs_list_of_gift_vouchers_created_import', $newupdates);
            }
            $array3 = array_merge((array) get_option('rs_list_of_gift_vouchers_created'), $newupdates);
            update_option('rs_list_of_gift_vouchers_created', $array3);
            $duplicate = self::get_keys_for_duplicate_values($array3);
            foreach ($duplicate as $key => $duplicatekey) {
                foreach ($array3 as $key => $keyunset) {
                    if (isset($keyunset[$duplicatekey])) {
                        unset($array3[$key]);
                    }
                }
                update_option('rs_list_of_gift_vouchers_created', $array3);
            }
            $array3 = array_merge(get_option('rs_list_of_gift_vouchers_created_import'), (array) get_option('rs_list_of_gift_vouchers_created'));
            $array3 = array_map("unserialize", array_unique(array_map("serialize", $array3)));
            update_option('rs_list_of_gift_vouchers_created', array_filter($array3));
            fclose($handle);
        }
    }

    public static function get_keys_for_duplicate_values($my_arr) {
        $dups = $new_arr = array();
        if (is_array($my_arr) && !empty($my_arr)) {
            foreach ($my_arr as $key) {
                if (is_array($key) && !empty($key)) {
                    foreach ($key as $val) {
                        if (!isset($new_arr[$val['vouchercode']])) {
                            $new_arr[$val['vouchercode']] = $val['vouchercode'];
                        } else {
                            if (isset($dups[$val['vouchercode']])) {
                                $dups[$val['vouchercode']] = $val['vouchercode'];
                            } else {
                                $dups[$val['vouchercode']] = $val['vouchercode'];
                            }
                        }
                    }
                }
            }
        }

        return $dups;
    }

    public static function search_code_to_redeem($array, $key, $value) {
        $results = array();
        if (is_array($array)) {
            if (isset($array[$key]) && strcasecmp($array[$key], $value) == 0) {
                $results[] = $array;
            }

            foreach ($array as $subarray) {
                $results = array_merge($results, self::search_code_to_redeem($subarray, $key, $value));
            }
        }

        return $results;
    }

    public static function rs_form_for_redeeming_voucher_codes() {
        ob_start();
        if (is_user_logged_in()) {
            ?>

            <style>
                .rs_form_for_claiming_offline_rewards{
                    margin-left: auto;
                    margin-right: auto;
                    width: 50%;
                }
                .rs_redeem_offline_rewards{
                    margin-left: auto;
                    margin-right: auto;
                    width: 50%;
                }
            </style>    


            <form class="rs_form_for_claiming_offline_rewards" method="post">
                <label><?php _e('Enter your Voucher Code below to claim', 'rewardsystem'); ?></label><br/><br/>

                <input type="text" name="rs_redeem_offline_online_rewards" class="rs_redeem_field_offline_online_rewards" placeholder="<?php echo get_option('rs_reward_code_field_placeholder'); ?>"/><br/><br/>


                <input type="submit" name="rs_offline_rewards_redeem" class="rs_offline_rewards_redeem" value="Submit"/>  

            </form>
            <?php
            if (isset($_POST['rs_offline_rewards_redeem'])) {
                $voucher_code_to_redeem = trim($_POST['rs_redeem_offline_online_rewards']);
//                $voucher_code_to_redeem = strtolower($voucher_code_to_redeem);
                $newone[] = '';
                $userid = get_current_user_id();
                $banning_type = FPRewardSystem::check_banning_type($userid);
                $order_id = '0';
                $pointsredeemed = '0';
                $redeempoints = '0';
                if ($banning_type != 'earningonly' && $banning_type != 'both') {
                    if (isset($voucher_code_to_redeem)) {

                        if (is_array(get_option('rs_list_of_gift_vouchers_created'))) {
                            foreach (get_option('rs_list_of_gift_vouchers_created')as $newones) {
                                if (!array_key_exists($voucher_code_to_redeem, $newones)) {
                                    $newone[] = $newones;
                                }
                            }
                        }

                        $findedarray = self::search_code_to_redeem(get_option('rs_list_of_gift_vouchers_created'), 'vouchercode', $voucher_code_to_redeem);
                        if (($findedarray == NULL) || ($findedarray == '')) {
                            echo addslashes(get_option('rs_invalid_voucher_code_error_message'));
                            exit();
                        } else {
                            $restrictuserpoints = get_option('rs_max_earning_points_for_user');
                            $enabledisablemaxpoints = get_option('rs_enable_disable_max_earning_points_for_user');
                            $todays_date = date_i18n("Y-m-d");
                            $today = strtotime($todays_date);
                            $vouchercreated = $findedarray[0]['vouchercreated'];
                            $memberused = isset($findedarray[0]['memberused']) != '' ? $findedarray[0]['memberused'] : '';
                            $voucherpoints = $findedarray[0]['points'];
                            $exp_date = $findedarray[0]['voucherexpiry'];
                            $noofdays = get_option('rs_point_to_be_expire');
                            if (($noofdays != '0') && ($noofdays != '')) {
                                $date = time() + ($noofdays * 24 * 60 * 60);
                            } else {
                                $date = '999999999999';
                            }
                            if ($memberused == '') {
                                if ($exp_date != '') {
                                    $expiration_date = strtotime($exp_date);
                                    if ($expiration_date > $today) {
                                        if ($enabledisablemaxpoints == 'yes') {
                                            if (($restrictuserpoints != '') && ($restrictuserpoints != '0')) {
                                                $getoldpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                if ($getoldpoints <= $restrictuserpoints) {
                                                    $totalpointss = $getoldpoints + $voucherpoints;
                                                    if ($totalpointss <= $restrictuserpoints) {
                                                        $voucherpoints = $findedarray[0]['points'];
                                                        $translatedstring = $voucher_code_to_redeem;
                                                        RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                                        $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                                        $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                        $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                        RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, $equredeemamt, '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                                        $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                                        $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                                        echo addslashes($rs_voucher_redeem_success_message_replaced);
                                                    } else {
                                                        $insertpoints = $restrictuserpoints - $getoldpoints;
                                                        RSPointExpiry::insert_earning_points(get_current_user_id(), $insertpoints, $pointsredeemed, $date, 'MREPFU', '0', $totalearnedpoints, $totalredeempoints, '');
                                                        $equearnamt = RSPointExpiry::earning_conversion_settings($insertpoints);
                                                        $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                        $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                        RSPointExpiry::record_the_points(get_current_user_id(), $insertpoints, $pointsredeemed, $date, 'MREPFU', $equearnamt, $equredeemamt, $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                                    }
                                                } else {
                                                    RSPointExpiry::insert_earning_points(get_current_user_id(), '0', '0', $date, 'MREPFU', $order_id, '0', '0', '');
                                                    $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                    RSPointExpiry::record_the_points(get_current_user_id(), '0', '0', $date, 'MREPFU', '0', '0', $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                                }
                                            } else {
                                                $voucherpoints = $findedarray[0]['points'];
                                                $translatedstring = $voucher_code_to_redeem;

                                                RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                                $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                                $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                                $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                                $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                                $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                                $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                                echo addslashes($rs_voucher_redeem_success_message_replaced);
                                            }
                                        } else {
                                            $voucherpoints = $findedarray[0]['points'];
                                            $translatedstring = $voucher_code_to_redeem;

                                            RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                            $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                            $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                            $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                            $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                            echo addslashes($rs_voucher_redeem_success_message_replaced);
                                        }
                                        $updates = array(
                                            array(
                                                $voucher_code_to_redeem => array('points' => $voucherpoints, 'vouchercode' => $voucher_code_to_redeem, 'vouchercreated' => $vouchercreated, 'voucherexpiry' => $exp_date, 'memberused' => get_current_user_id())
                                            ),
                                        );

                                        $array1 = $newone;
                                        $array2 = $updates;
                                        $array3 = array_merge((array) $array1, (array) $array2);

                                        update_option('rs_list_of_gift_vouchers_created', array_filter($array3));
                                    } else {
                                        echo addslashes(get_option('rs_voucher_code_expired_error_message'));
                                    }
                                } else {
                                    if ($enabledisablemaxpoints == 'yes') {
                                        if (($restrictuserpoints != '') && ($restrictuserpoints != '0')) {
                                            $getoldpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            if ($getoldpoints <= $restrictuserpoints) {
                                                $totalpointss = $getoldpoints + $voucherpoints;
                                                if ($totalpointss <= $restrictuserpoints) {
                                                    $voucherpoints = $findedarray[0]['points'];
                                                    $translatedstring = $voucher_code_to_redeem;
                                                    RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                                    $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                                    $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                    $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                    RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, $equredeemamt, '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                                    $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                                    $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                                    echo addslashes($rs_voucher_redeem_success_message_replaced);
                                                } else {
                                                    $insertpoints = $restrictuserpoints - $getoldpoints;
                                                    RSPointExpiry::insert_earning_points(get_current_user_id(), $insertpoints, $pointsredeemed, $date, 'MREPFU', '0', $totalearnedpoints, $totalredeempoints, '');
                                                    $equearnamt = RSPointExpiry::earning_conversion_settings($insertpoints);
                                                    $equredeemamt = RSPointExpiry::redeeming_conversion_settings($pointsredeemed);
                                                    $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                    RSPointExpiry::record_the_points(get_current_user_id(), $insertpoints, $pointsredeemed, $date, 'MREPFU', $equearnamt, $equredeemamt, $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                                }
                                            } else {
                                                RSPointExpiry::insert_earning_points(get_current_user_id(), '0', '0', $date, 'MREPFU', $order_id, '0', '0', '');
                                                $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                                RSPointExpiry::record_the_points(get_current_user_id(), '0', '0', $date, 'MREPFU', '0', '0', $order_id, '0', '0', '0', '', $totalpoints, '', '0');
                                            }
                                        } else {
                                            $voucherpoints = $findedarray[0]['points'];
                                            $translatedstring = $voucher_code_to_redeem;

                                            RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                            $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                            $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                            RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                            $rs_voucher_redeem_success_to_find = "[giftvoucherpoints]";
                                            $rs_voucher_redeem_success_to_replace = $voucherpoints;
                                            $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                            $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                            echo addslashes($rs_voucher_redeem_success_message_replaced);
                                        }
                                    } else {
                                        $voucherpoints = $findedarray[0]['points'];
                                        $translatedstring = $voucher_code_to_redeem;

                                        RSPointExpiry::insert_earning_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', '0', $voucherpoints, $redeempoints, '');
                                        $equearnamt = RSPointExpiry::earning_conversion_settings($voucherpoints);
                                        $totalpoints = RSPointExpiry::get_sum_of_total_earned_points(get_current_user_id());
                                        RSPointExpiry::record_the_points(get_current_user_id(), $voucherpoints, $pointsredeemed, $date, 'RPGV', $equearnamt, '0', '0', '0', '0', '0', $translatedstring, $totalpoints, '', '0');
                                        $rs_voucher_redeem_success_message = get_option('rs_voucher_redeem_success_message');
                                        $rs_voucher_redeem_success_message_replaced = str_replace($rs_voucher_redeem_success_to_find, $rs_voucher_redeem_success_to_replace, $rs_voucher_redeem_success_message);
                                        echo addslashes($rs_voucher_redeem_success_message_replaced);
                                    }
                                    $updates = array(
                                        array(
                                            $voucher_code_to_redeem => array('points' => $voucherpoints, 'vouchercode' => $voucher_code_to_redeem, 'vouchercreated' => $vouchercreated, 'voucherexpiry' => $exp_date, 'memberused' => get_current_user_id())
                                        ),
                                    );

                                    $array1 = $newone;
                                    $array2 = $updates;
                                    $array3 = array_merge((array) $array1, (array) $array2);

                                    update_option('rs_list_of_gift_vouchers_created', array_filter($array3));
                                }
                            } else {
                                echo addslashes(get_option('rs_voucher_code_used_error_message'));
                            }
                        }
                    }
                }
            }
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

    public static function rs_function_for_current_available_points() {
        if (is_user_logged_in()) {
            ?>
            <style type="text/css">
                #current_points_caption{
                    font-size: 20px;
                    margin-left: auto;
                    margin-right: auto;
                    width: 50%;
                }
            </style>    
            <?php
            $userid = get_current_user_id();
            $getusermeta = RSPointExpiry::get_sum_of_total_earned_points($userid) + get_user_meta($userid, '_my_reward_points', true);
            if ($getusermeta != '') {
                $roundofftype = get_option('rs_round_off_type') == '1' ? '2' : '0';
                echo "<br/><br/><br/><div id='current_points_caption'><b>" . get_option('rs_current_available_balance_caption') . "</b>" . " " . round(number_format((float) $getusermeta, 2, '.', ''), $roundofftype) . "</div>";
            } else {
                echo "<br/><br/><br/><div id='current_points_caption'><b>" . get_option('rs_current_available_balance_caption') . "</b>" . " " . "0" . "</div>";
            }
        }
    }

    public static function delete_array_keys_rs_point_vouchers() {
        if (isset($_POST['deletecode'])) {
            $checkifexists = get_option('rs_list_of_gift_vouchers_created');
            if (!empty($checkifexists)) {
                foreach (get_option('rs_list_of_gift_vouchers_created') as $updates) {
                    if (array_key_exists($_POST['deletecode'], $updates)) {
                        unset($updates);
                    }
                    $newupdates[] = $updates;
                }
                $new_array_without_nulls = array_filter($newupdates);
                update_option('rs_list_of_gift_vouchers_created', $new_array_without_nulls);
            }
        }
        exit();
    }

}

new RSFunctionForOfflineOnlineRewards();
