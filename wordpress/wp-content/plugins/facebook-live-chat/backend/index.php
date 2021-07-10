<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
/*
* Add menu admin options
*/
add_action( 'admin_menu', 'fb_live_chat_menu_options' );
function fb_live_chat_menu_options(){
	add_submenu_page( 'options-general.php', 'Facebook Live Chat', 'Facebook Live Chat', 'manage_options', 'facebook_live_chat_options_page', 'facebook_live_chat_options_page' );
}
/*
* Add Upload style and script
*/
function fb_live_chat_admin_scripts() {
    //if($_GET && $_GET["page"] && get_option("live_chat_facebook_enable") ){
      $page = $_GET["page"];
    //}else{
    //  $page = null;
    //}
    if ( $page && $page == "facebook_live_chat_options_page" ) {
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
        wp_register_script('fb-live-upload', NJ_LIVE_CHAT__PLUGIN_URL . 'js/live-chat.js', array('jquery','media-upload','thickbox'));
        wp_enqueue_script('fb-live-upload');
    }

}
add_action( 'admin_enqueue_scripts', 'fb_live_chat_admin_enqueue_scripts' );
function fb_live_chat_admin_enqueue_scripts(){
    wp_enqueue_script('jqColorPicker', NJ_LIVE_CHAT__PLUGIN_URL . 'js/jqColorPicker.min.js');
}
function fb_live_chat_admin_style() {
    wp_enqueue_style('thickbox');
    wp_enqueue_style( 'admin_live_chat_fb', NJ_LIVE_CHAT__PLUGIN_URL . 'css/backend_live_chat.css', false, '1.0.0' );
}
add_action('admin_print_scripts', 'fb_live_chat_admin_scripts');
add_action('admin_print_styles', 'fb_live_chat_admin_style');
/*
* Add form options
*/
function facebook_live_chat_options_page(){
	?>
    <div class="wrap">
        <form action="options.php" method="post">
        <?php settings_fields("wap_form") ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_enable"><?php echo __("Enable","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_enable" type="checkbox" value="ok" <?php if ( get_option("live_chat_facebook_enable") =="ok" ){ echo 'checked="checked"'; } ?>   />
                        <p class="description" id="tagline-description">Turn on or off Facebook Live Chat</p>
                    </td>
                </tr>
              <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_enable"><?php echo __("Disable mobile","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_disable_moblie" type="checkbox" value="ok" <?php if ( get_option("live_chat_facebook_disable_moblie") =="ok" ){ echo 'checked="checked"'; } ?>   />
                        <p class="description" id="tagline-description">Disable Facebook Live Chat for mobile</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_user"><?php echo __("Your Facebook Fan Page URL","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_user" id="live_chat_facebook_user" type="text" value="<?php echo get_option("live_chat_facebook_user"); ?>" class="regular-text" />
                         <p class="description" id="tagline-description">Enter your fanpage url. Eg: https://www.facebook.com/ninjateam.org</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_title"><?php echo __("Title","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_title" id="live_chat_facebook_title" type="text" value="<?php echo get_option("live_chat_facebook_title")?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_open"><?php echo __("Open Chat Text","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_open" id="live_chat_facebook_open" type="text" value="<?php echo get_option("live_chat_facebook_open"); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_enable_hello"><?php echo __("Enable hello chat","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_enable_hello" type="checkbox" value="ok" <?php if ( get_option("live_chat_facebook_enable_hello") =="ok" || !get_option("live_chat_check_update1") ){ echo 'checked="checked"'; } ?>   />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_text"><?php echo __("Hello Content","live_chat") ?></label></th>
                    <td>
                        <textarea name="live_chat_facebook_text" style="width: 25em;" class="code" rows="3"><?php echo get_option("live_chat_facebook_text"); ?></textarea>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_start"><?php echo __("Start Button Text","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_start" id="live_chat_facebook_start" type="text" value="<?php echo get_option("live_chat_facebook_start"); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_close"><?php echo __("Hide Close Botton","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_close" type="checkbox" value="ok" <?php if ( get_option("live_chat_facebook_close") =="ok" ){ echo 'checked="checked"'; } ?>   />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_logo"><?php echo __("Custom Logo","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_logo" id="live_chat_facebook_logo" type="text" value="<?php echo get_option("live_chat_facebook_logo"); ?>" class="regular-text" />
                        <a class="button" id="upload-logo-live-chat">Upload</a>
                        <p class="description" id="tagline-description">Upload your custom logo in top bar. Recommend size is 20X20px</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_backgroud"><?php echo __("Main Color","live_chat") ?></label></th>
                    <td>
                        <input name="live_chat_facebook_backgroud" class="color" id="live_chat_facebook_backgroud" type="text" value="<?php echo get_option("live_chat_facebook_backgroud"); ?>" class="regular-text" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php echo __("Start Minimized","live_chat") ?></label></th>
                    <td>
                        <input <?php if ( get_option("live_chat_facebook_start_disktop") == 1 ) {echo 'checked="checked"';} ?> id="live_chat_facebook_start_disktop" name="live_chat_facebook_start_disktop" type="checkbox" value="1" /> <label for="live_chat_facebook_start_disktop">Desktop</label>
                        <input <?php if ( get_option("live_chat_facebook_start_mobile") == 1 ) {echo 'checked="checked"';} ?> id="live_chat_facebook_start_mobile" name="live_chat_facebook_start_mobile" type="checkbox" value="1" /> <label for="live_chat_facebook_start_mobile" >Mobile</label>
                        <p class="description">Select minimized for Mobile, Desktop when first time visitor goes to your website</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php echo __("Display","live_chat") ?></label></th>
                    <td>
                        <?php $display = get_option("live_chat_facebook_hide_display"); ?>
                        <select name="live_chat_facebook_hide_display" id="ninja-display">
                            <option value="0"><?php echo __("Display for pages...","live_chat") ?></option>
                            <option <?php if ( $display == 1) {echo 'selected="selected"';} ?> value="1"><?php echo __("Display all pages but except","live_chat") ?></option>
                        </select>
                    </td>
                </tr>
                 <tr valign="top" id="ninja-hide-tr1" class="<?php if ( $display == 1) {echo 'hide';} ?>">
                    <th scope="row"><label for="live_chat_facebook_backgroud"><?php echo __("Where you want to display","live_chat") ?></label></th>
                    <td>
                        <input value="1" name="live_chat_check_update"  type="hidden" />
                        <input value="1" name="live_chat_check_update1"  type="hidden" />
                        <input type="checkbox" id="fb-checkall" /> <label for="fb-checkall">All</label>
                        <ul id="live_chat_facebook_show_page" class="live_chat_facebook_show_page">
                        <?php $new = new WP_Query(array("posts_per_page"=>-1,"post_type"=>"page"));
                            $array_show = get_option( "live_chat_facebook_show");
                            if ( !get_option( "live_chat_facebook_show") ) {
                               $array_show = array();
                            }
                            while ( $new->have_posts() ) : $new->the_post() ;
                            ?>
                            <li><input <?php if ( get_option("live_chat_check_update") ) {
                                if ( in_array(get_the_ID(), $array_show ) ) { echo 'checked="checked"'; }
                            }else{
                               echo 'checked="checked"';
                            }  ?> name="live_chat_facebook_show[]" class="live_chat_facebook_show" type="checkbox" value="<?php the_ID() ?>" id="live_chat_facebook_show_<?php the_ID() ?>" /> <label for="live_chat_facebook_show_<?php the_ID() ?>"><?php the_title() ?></label></li>
                            <?php
                            endwhile;wp_reset_postdata();
                         ?>
                         </ul>
                         <p class="description">Select where you want to display Facebook Live Chat</p>
                    </td>
                </tr>
                <tr valign="top" id="ninja-hide-tr2"  class="<?php if ( $display != 1) {echo 'hide';} ?>">
                    <th scope="row"><label for="live_chat_facebook_backgroud"><?php echo __("Display all pages but except","live_chat") ?></label></th>
                    <td>
                        <input type="checkbox" id="fb-checkall1" /> <label for="fb-checkall">All</label>
                        <ul id="live_chat_facebook_hide_page" class="live_chat_facebook_show_page">
                        <?php $new = new WP_Query(array("posts_per_page"=>-1,"post_type"=>"page"));
                            $array_hide = get_option( "live_chat_facebook_hide");
                            if ( !$array_hide ){
                                $array_hide = array();
                            }
                            while ( $new->have_posts() ) : $new->the_post() ;
                            ?>
                            <li><input <?php if ( get_option("live_chat_check_update") ) {
                                if ( @in_array(get_the_ID(), $array_hide ) ) { echo 'checked="checked"'; }
                            }else{
                               echo 'checked="checked"';
                            }  ?> name="live_chat_facebook_hide[]" class="live_chat_facebook_show1" type="checkbox" value="<?php the_ID() ?>" id="live_chat_facebook_show_exc_<?php the_ID() ?>" /> <label for="live_chat_facebook_show_exc_<?php the_ID() ?>"><?php the_title() ?></label></li>
                            <?php
                            endwhile;wp_reset_postdata();
                         ?>
                         </ul>
                         <p class="description">Select where you want to display Facebook Live Chat</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="live_chat_facebook_backgroud"><?php echo __("Languages","live_chat") ?></label></th>
                    <td>
                        <?php $config = array(
                        	// Afrikaans
                        	'af_ZA' => 'Afrikaans',
                        	// Arabic
                        	'ar_AR' => 'Arabic',
                        	// Azerbaijani
                        	'az_AZ' => 'Azerbaijani',
                        	// Belarusian
                        	'be_BY' => 'Belarusian',
                        	// Bulgarian
                        	'bg_BG' => 'Bulgarian',
                        	// Bengali
                        	'bn_IN' => 'Bengali',
                        	// Bosnian
                        	'bs_BA' => 'Bosnian',
                        	// Catalan
                        	'ca_ES' => 'Catalan',
                        	// Czech
                        	'cs_CZ' => 'Czech',
                        	// Welsh
                        	'cy_GB' => 'Welsh',
                        	// Danish
                        	'da_DK' => 'Danish',
                        	// German
                        	'de_DE' => 'German',
                        	// Greek
                        	'el_GR' => 'Greek',
                        	// English (UK)
                        	'en_GB' => 'English (UK)',
                        	// English (Pirate)
                        	'en_PI' => 'English (Pirate)',
                        	// English (Upside Down)
                        	'en_UD' => 'English (Upside Down)',
                        	// English (US)
                        	'en_US' => 'English (US)',
                        	// Esperanto
                        	'eo_EO' => 'Esperanto',
                        	// Spanish (Spain)
                        	'es_ES' => 'Spanish (Spain)',
                        	// Spanish
                        	'es_LA' => 'Spanish',
                        	// Estonian
                        	'et_EE' => 'Estonian',
                        	// Basque
                        	'eu_ES' => 'Basque',
                        	// Persian
                        	'fa_IR' => 'Persian',
                        	// Leet Speak
                        	'fb_LT' => 'Leet Speak',
                        	// Finnish
                        	'fi_FI' => 'Finnish',
                        	// Faroese
                        	'fo_FO' => 'Faroese',
                        	// French (Canada)
                        	'fr_CA' => 'French (Canada)',
                        	// French (France)
                        	'fr_FR' => 'French (France)',
                        	// Frisian
                        	'fy_NL' => 'Frisian',
                        	// Irish
                        	'ga_IE' => 'Irish',
                        	// Galician
                        	'gl_ES' => 'Galician',
                        	// Hebrew
                        	'he_IL' => 'Hebrew',
                        	// Hindi
                        	'hi_IN' => 'Hindi',
                        	// Croatian
                        	'hr_HR' => 'Croatian',
                        	// Hungarian
                        	'hu_HU' => 'Hungarian',
                        	// Armenian
                        	'hy_AM' => 'Armenian',
                        	// Indonesian
                        	'id_ID' => 'Indonesian',
                        	// Icelandic
                        	'is_IS' => 'Icelandic',
                        	// Italian
                        	'it_IT' => 'Italian',
                        	// Japanese
                        	'ja_JP' => 'Japanese',
                        	// Georgian
                        	'ka_GE' => 'Georgian',
                        	// Khmer
                        	'km_KH' => 'Khmer',
                        	// Korean
                        	'ko_KR' => 'Korean',
                        	// Kurdish
                        	'ku_TR' => 'Kurdish',
                        	// Latin
                        	'la_VA' => 'Latin',
                        	// Lithuanian
                        	'lt_LT' => 'Lithuanian',
                        	// Latvian
                        	'lv_LV' => 'Latvian',
                        	// Macedonian
                        	'mk_MK' => 'Macedonian',
                        	// Malayalam
                        	'ml_IN' => 'Malayalam',
                        	// Malay
                        	'ms_MY' => 'Malay',
                        	// Norwegian (bokmal)
                        	'nb_NO' => 'Norwegian (bokmal)',
                        	// Nepali
                        	'ne_NP' => 'Nepali',
                        	// Dutch
                        	'nl_NL' => 'Dutch',
                        	// Norwegian (nynorsk)
                        	'nn_NO' => 'Norwegian (nynorsk)',
                        	// Punjabi
                        	'pa_IN' => 'Punjabi',
                        	// Polish
                        	'pl_PL' => 'Polish',
                        	// Pashto
                        	'ps_AF' => 'Pashto',
                        	// Portuguese (Brazil)
                        	'pt_BR' => 'Portuguese (Brazil)',
                        	// Portuguese (Portugal)
                        	'pt_PT' => 'Portuguese (Portugal)',
                        	// Romanian
                        	'ro_RO' => 'Romanian',
                        	// Russian
                        	'ru_RU' => 'Russian',
                        	// Slovak
                        	'sk_SK' => 'Slovak',
                        	// Slovenian
                        	'sl_SI' => 'Slovenian',
                        	// Albanian
                        	'sq_AL' => 'Albanian',
                        	// Serbian
                        	'sr_RS' => 'Serbian',
                        	// Swedish
                        	'sv_SE' => 'Swedish',
                        	// Swahili
                        	'sw_KE' => 'Swahili',
                        	// Tamil
                        	'ta_IN' => 'Tamil',
                        	// Telugu
                        	'te_IN' => 'Telugu',
                        	// Thai
                        	'th_TH' => 'Thai',
                        	// Filipino
                        	'tl_PH' => 'Filipino',
                        	// Turkish
                        	'tr_TR' => 'Turkish',
                        	//
                        	'uk_UA' => 'Ukrainian',
                        	// Vietnamese
                        	'vi_VN' => 'Vietnamese',
                        	//
                        	'zh_CN' => 'Simplified Chinese (China)',
                        	//
                        	'zh_HK' => 'Traditional Chinese (Hong Kong)',
                        	//
                        	'zh_TW' => 'Traditional Chinese (Taiwan)',
                        );
                        $lang = get_option("live_chat_facebook_lang");
                        if ( !$lang ) {
                            $lang = "en_US";
                        }
                         ?>
                         <select name="live_chat_facebook_lang">
                             <?php foreach ( $config as $k => $v ) {
                             ?>
                             <option <?php if ( $lang == $k) {echo 'selected="selected"';} ?>  value="<?php echo $k ?>"><?php echo $v ?></option>
                             <?php
                             } ?>
                         </select>
                    </td>
                </tr>
             </table>
             <?php submit_button("Save") ?>
          </form>
      </div>
    <?php
}

/*
* Save options
*/
add_action("admin_init","fb_live_chat_save_form");
function fb_live_chat_save_form(){
    register_setting("wap_form","live_chat_facebook_start_disktop");
    register_setting("wap_form","live_chat_facebook_disable_moblie");
    register_setting("wap_form","live_chat_facebook_start_tablet");
    register_setting("wap_form","live_chat_facebook_start_mobile");
    register_setting("wap_form","live_chat_facebook_show");
    register_setting("wap_form","live_chat_facebook_hide_display");
    register_setting("wap_form","live_chat_facebook_hide");
    register_setting("wap_form","live_chat_check_update");
    register_setting("wap_form","live_chat_check_update1");

    register_setting("wap_form","live_chat_facebook_enable_hello");
     register_setting("wap_form","live_chat_facebook_start");
     register_setting("wap_form","live_chat_facebook_text");
     register_setting("wap_form","live_chat_facebook_open");
     register_setting("wap_form","live_chat_facebook_title");
     register_setting("wap_form","live_chat_facebook_user");
     register_setting("wap_form","live_chat_facebook_backgroud");
     register_setting("wap_form","live_chat_facebook_logo");
     register_setting("wap_form","live_chat_facebook_close");
     register_setting("wap_form","live_chat_facebook_enable");
     register_setting("wap_form","live_chat_facebook_lang");
}
