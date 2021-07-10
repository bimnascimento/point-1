<?php
if (!defined('WPINC')) {
    die;
}

class ARForms_VCExtendArp {

    protected static $instance = null;
    var $is_arforms_vdextend = 0;

    public function __construct() {
        add_action('init', array($this, 'ARFintegrateWithVC'));
        add_action('init', array($this, 'ArfCallmyFunction'));
    }

    public static function arp_get_instance() {
        if ($this->instance == null) {
            $this->instance = new self;
        }

        return $this->instance;
    }


    public function ARFintegrateWithVC() {
        if (function_exists('vc_map')) {
            global $arfversion, $armainhelper;


            if (version_compare(WPB_VC_VERSION, '4.3.4', '>=')) {


                if (isset($_REQUEST['vc_action']) && !empty($_REQUEST['vc_action'])) {

                    wp_register_style('arfbootstrap-css', ARFURL . '/bootstrap/css/bootstrap.css', array(), $arfversion);
                    wp_enqueue_style('arfbootstrap-css');

                    wp_enqueue_style('arfbootstrap-select', ARFURL . '/bootstrap/css/bootstrap-select.css', array(), $arfversion);

                    wp_register_script('arfbootstrap-js', ARFURL . '/bootstrap/js/bootstrap.min.js', array('jquery'), $arfversion);
                    wp_enqueue_script('arfbootstrap-js');

                    wp_enqueue_script('jquery-bootstrap-slect', ARFURL . '/bootstrap/js/bootstrap-select.js', array('jquery'), $arfversion);

                    wp_register_script('jquery-validation', ARFURL . '/bootstrap/js/jqBootstrapValidation.js', array('jquery'), $arfversion);
                    wp_enqueue_script('jquery-validation');
                    
                                wp_register_script('arforms_colpick-js', ARFURL . '/js/colpick.js', array(), $arfversion);
            $armainhelper->load_scripts(array('arforms_colpick-js'));


            wp_register_style('arforms_colpick-css', ARFURL . '/css/colpick.css', array(), $arfversion);
            $armainhelper->load_styles(array('arforms_colpick-css'));


            wp_register_script('arf-themepicker-js', ARFURL . '/js/jquery/jquery-ui-themepicker.js', array(), $arfversion);
            $armainhelper->load_scripts(array('arf-themepicker-js'));
                }
            }
           







            
            
            
            
            vc_map(array(
                'name' => __('ARForms', 'ARForms'),
                'description' => __('Exclusive Wordpress Form Builder Plugin', 'ARForms'),
                'base' => 'ARForms_popup',
                'category' => __('Content', 'ARForms'),
                'class' => '',
                'controls' => 'full',
                'admin_enqueue_css' => array(ARFURL . '/core/vc/arforms_vc.css'),
                'front_enqueue_css' => ARFURL . '/core/vc/arforms_vc.css',
                'front_enqueue_js' => ARFURL . '/core/vc/arforms_vc.js',
                'icon' => 'arforms_vc_icon',
                'params' => array(
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'id',
                        'value' => '',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'shortcode_type',
                        'value' => 'normal',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => 'ARForms_Popup_Shortode',
                        'heading' => false,
                        'param_name' => 'type',
                        'value' => 'link',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'position',
                        'value' => 'top',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'desc',
                        'value' => 'Click here to open Form',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'width',
                        'value' => '800',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'height',
                        'value' => 'auto',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'angle',
                        'value' => '0',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'bgcolor',
                        'value' => '#8ccf7a',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'txtcolor',
                        'value' => '#ffffff',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_inactivity',
                        'value' => '1',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                    array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_scroll',
                        'value' => '10',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'on_delay',
                        'value' => '0',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'overlay',
                        'value' => '0.6',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'is_close_link',
                        'value' => 'yes',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ), array(
                        "type" => "ARForms_Popup_Shortode",
                        'heading' => false,
                        'param_name' => 'modal_bgcolor',
                        'value' => '#000000',
                        'description' => __('&nbsp;', 'ARForms'),
                        'admin_label' => true
                    ),
                )
            ));
        }
    }

    public function ArfCallmyFunction() {
        if (function_exists('vc_add_shortcode_param')) {
            vc_add_shortcode_param('ARForms_Popup_Shortode', array($this, 'arforms_param_html'), ARFURL . '/core/vc/arforms_vc.js');
        }
    }

    public function arforms_param_html($settings, $value) {

        global $armainhelper, $arformhelper;

        echo '<input  id="Arf_param_id" type="hidden" name="id" value="" class="wpb_vc_param_value">';

        echo '<input id="' . esc_attr($settings['param_name']) . '" name="' . esc_attr($settings['param_name']) . '" class=" ' . esc_attr($settings['param_name']) . ' ' . esc_attr($settings['type']) . '_arfield" type="hidden" value="' . esc_attr($value) . '" />';



        if ($this->is_arforms_vdextend == 0) {
            $this->is_arforms_vdextend = 1;
            ?>



            <style type="text/css">

                @font-face {
                    font-family: 'open_sansregular';
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-regular-webfont.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-regular-webfont.eot?#iefix') format('embedded-opentype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-regular-webfont.woff') format('woff'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-regular-webfont.ttf') format('truetype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-regular-webfont.svg#open_sansregular') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'open_sansbold';
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-bold-webfont.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-bold-webfont.eot?#iefix') format('embedded-opentype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-bold-webfont.woff') format('woff'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-bold-webfont.ttf') format('truetype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-bold-webfont.svg#open_sansbold') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'open_sansextrabold';
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-extrabold-webfont.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-extrabold-webfont.eot?#iefix') format('embedded-opentype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-extrabold-webfont.woff') format('woff'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-extrabold-webfont.ttf') format('truetype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-extrabold-webfont.svg#open_sansextrabold') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'open_sanssemibold';
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-semibold-webfont.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/opensans-semibold-webfont.eot?#iefix') format('embedded-opentype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-semibold-webfont.woff') format('woff'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-semibold-webfont.ttf') format('truetype'),
                        url('<?php echo ARFURL; ?>/fonts/opensans-semibold-webfont.svg#open_sanssemibold') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }

                @font-face {
                    font-family: 'aileron_regular';
                    src: url('<?php echo ARFURL; ?>/fonts/aileron-regular-webfont.eot');
                    src: url('<?php echo ARFURL; ?>/fonts/aileron-regular-webfont.eot?#iefix') format('embedded-opentype'),
                        url('<?php echo ARFURL; ?>/fonts/aileron-regular-webfont.woff') format('woff'),
                        url('<?php echo ARFURL; ?>/fonts/aileron-regular-webfont.ttf') format('truetype'),
                        url('<?php echo ARFURL; ?>/fonts/aileron-regular-webfont.svg#aileron_regular') format('svg');
                    font-weight: normal;
                    font-style: normal;
                }


                .arfmodal_vc .btn-group.bootstrap-select 
                {
                    text-align:left;
                }

                .arfmodal_vc .btn-group .btn.dropdown-toggle,.arfmodal_vc .btn-group .arfbtn.dropdown-toggle {
                    border: 1px solid #CCCCCC;
                    background-color:#FFFFFF;
                    background-image:none;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.open .btn.dropdown-toggle,.arfmodal_vc .btn-group.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-bottom-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                }
                .arfmodal_vc .btn-group.dropup.open .btn.dropdown-toggle, .arfmodal_vc .btn-group.dropup.open .arfbtn.dropdown-toggle {
                    border:solid 1px #CCCCCC;
                    background-color:#FFFFFF;
                    border-top-color:transparent;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    outline:0 !important;
                    outline-style:none;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;
                    border-bottom-left-radius:6px;
                    border-bottom-right-radius:6px;
                }
                .arfmodal_vc .btn-group .arfdropdown-menu {
                    margin:0;
                }
                .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    border:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-top:none;
                    margin:0;
                    margin-top:-1px;
                    border-top-left-radius:0px;
                    border-top-right-radius:0px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu {
                    border-top:solid 1px #CCCCCC;
                    box-shadow:none;
                    -webkit-box-shadow:none;
                    -moz-box-shadow:none;
                    -o-box-shadow:none;
                    border-bottom:none;
                    margin:0;
                    margin-bottom:-1px;
                    border-bottom-left-radius:0px;
                    border-bottom-right-radius:0px;
                    border-top-left-radius:6px;
                    border-top-right-radius:6px;	
                }
                .arfmodal_vc .btn-group.dropup.open .arfdropdown-menu .arfdropdown-menu.inner {
                    border-top:none;
                }
                .arfmodal_vc .btn-group.open ul.arfdropdown-menu {
                    border:none;
                }

                .arfmodal_vc .arfdropdown-menu > li {
                    margin:0px;
                }

                .arfmodal_vc .arfdropdown-menu > li > a {
                    padding: 6px 12px;
                    text-decoration:none;
                }

                .arfmodal_vc .arfdropdown-menu > li:hover > a {
                    background:#1BBAE1;
                }

                .arfmodal_vc .bootstrap-select.btn-group, 
                .arfmodal_vc .bootstrap-select.btn-group[class*="span"] {
                    margin-bottom:5px;
                }

                .arfmodal_vc ul, .wrap ol {
                    margin:0;
                    padding:0;
                }

                .arfmodal_vc form {
                    margin:0;
                }	

                .arfmodal_vc label {
                    display:inline;
                    margin-left:5px;
                }

                .arfnewmodalclose
                {
                    font-size: 15px;
                    font-weight: bold;
                    height: 19px;
                    position: absolute;
                    right: 3px;
                    top:5px;
                    width: 19px;
                    cursor:pointer;
                    color:#D1D6E5;
                } 
                #arfinsertform
                {
                    text-align:center;
                }
                .newform_modal_title
                {
                    font-size:24px;
                    font-family:'open_sansextrabold', Arial, Helvetica, Verdana, sans-serif;
                    color:#d1d6e5;
                    margin-top:14px;
                }

                #arfcontinuebtn
                {
                    background:#1bbae1;
                    font-family:'open_sanssemibold', Arial, Helvetica, Verdana, sans-serif;
                    font-size:18px;
                    cursor:pointer;
                    color:#ffffff;
                    margin-top:10px;
                    padding-top:18px;	
                    height:42px;
                }

                .arfmodal_vc .txtmodal1 
                {
                    height:36px;
                    border:1px solid #cccccc;
                    -o-border-radius:3px;
                    -moz-border-radius:3px;
                    -webkit-border-radius:3px;
                    border-radius:3px;
                    color:#353942;
                    background:#FFFFFF;
                    font-family:'open_sansregular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:14px;
                    margin:0px;
                    letter-spacing:0.8px;
                    padding:0px 10px 0 10px;
                    width:360px;
                    outline:none;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-sizing: content-box;
                    -moz-box-sizing: content-box;
                    box-sizing: content-box;
                }
                .arfmodal_vc .txtmodal1:focus
                {
                    border:1px solid #1BBAE1;
                    box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -webkit-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -moz-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    -o-box-shadow: 0px 0px 1px rgba(0, 0, 0, 0), 0 1px 1px rgba(0, 0, 0, 0.1) inset;
                    transition:none;
                }
                .newmodal_field_title
                {
                    margin:20px 0 10px 0;
                    font-family:'open_sansbold', Arial, Helvetica, Verdana, sans-serif;
                    
                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vc input[class="rdomodal"] {
                    display:none;
                }

                .arfmodal_vc input[class="rdomodal"] + label {
                    color:#333333;
                    font-size:14px;
                    font-family:'aileron_regular', Arial, Helvetica, Verdana, sans-serif;
                }

                .arfmodal_vc input[class="rdomodal"] + label span {
                    display:inline-block;
                    width:19px;
                    height:19px;
                    margin:-1px 4px 0 0;
                    vertical-align:middle;
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -37px top no-repeat;
                    cursor:pointer;
                }

                .arfmodal_vc input[class="rdomodal"]:checked + label span
                {
                    background:url(<?php echo ARFURL; ?>/images/dark-radio-green.png) -56px top no-repeat;
                }
                .arfmodal_vcfields
                {
                    display:table;
                    text-align: center;
                    margin-top:10px;
                    width:100%;
                }
                .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    display:table-cell;
                    text-align:right;
                    width:45%;
                    padding-right:20px;	
                    font-family:'open_sansbold', Arial, Helvetica, Verdana, sans-serif;
                    font-weight:normal;
                    font-size:14px;
                    color:#353942;
                }
                .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    display:table-cell;
                    text-align:left;
                }
                .arfmodal_vc .arf_px
                {
                    font-family:'aileron_regular', Arial, Helvetica, Verdana, sans-serif;
                    font-size:12px;
                    color:#353942;	
                }

                
                body.rtl .arfnewmodalclose
                {
                    right:auto;
                    left:3px;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_left
                {
                    text-align:left;
                }
                body.rtl .arfmodal_vcfields .arfmodal_vcfield_right
                {
                    text-align:right;
                    padding-right:20px;	
                }
                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .filter-option
                {
                    top:5px;
                    right:8px;
                    left:auto;
                }

                body.rtl .arfmodal_vc .bootstrap-select.btn-group .arfbtn .caret
                {
                    left:8px;
                    right:auto;
                }
                body.rtl .arfmodal_vc .btn-group.open .arfdropdown-menu {
                    text-align:right;
                }

                .arf_coloroption_sub{
                    border: 4px solid #dcdfe4;
                    border-radius: 2px;
                    -webkit-border-radius: 2px;
                    -moz-border-radius: 2px;
                    -o-border-radius: 2px;
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                    margin-left:22px;
                    margin-top:5px;
                }

                .arf_coloroption{
                    cursor: pointer;
                    height: 22px;
                    width: 47px;
                }

                .arf_coloroption_subarrow_bg{
                    background: none repeat scroll 0 0 #dcdfe4;
                    height: 8px;
                    margin-left: 39px;
                    margin-top: -8px;
                    text-align: center;
                    vertical-align: middle;
                    width: 8px;
                }

                .arf_coloroption_subarrow{
                    background: <?php echo "url(" . ARFURL . "/images/colpickarrow.png) no-repeat center center"; ?>;
                    height: 3px;
                    padding-left: 5px;
                    padding-top: 6px;
                    width: 5px;
                }

                .colpick_hex{
                    z-index:999999;
                }
                .arfmodal_vc.fade{ opacity:1; }
            </style>        


            <div id="arfinsertform" class="arfmodal_vc fade">


                <div class="newform_modal_title_container">
                    <div class="newform_modal_title"><img src="<?php echo ARFIMAGESURL . '/add-newform-icon.png'; ?>" align="absmiddle" />&nbsp;<?php _e('ADD ARFORMS FORM', 'ARForms'); ?></div>
                </div>	

                <input type="hidden" id="form_title_i" value="" />
                <div class="newform_modal_fields" style="margin-bottom:30px;">

                    <div class="newmodal_field_title"><?php _e('Select a form to insert into page', 'ARForms'); ?>&nbsp;<span class="newmodal_required" style="color:#ff0000; vertical-align:top;">*</span></div>
                    <div class="newmodal_field">
                        <div class="sltmodal">
                            <?php $arformhelper->forms_dropdown_new('arfaddformid_vc_popup', '', 'Select form', 'arfaddformid_vc_popup', 'set_arfaddformid_vc_popup(this.value);') ?>
                        </div><div id="form_name_new_required" class="arferrmessage" style="display:none;"><?php _e('Please enter form name', 'ARForms'); ?></div>
                        <input type="hidden"  id="arf_blank_forms_msg" value="<?php _e('Please select a form', 'ARForms') ?>" />
                    </div>




                    
                    <input type="hidden" id="arf_shortcode_type" value="normal" name="shortcode_type"  class="wpb_vc_param_value" />
                    <div class="newmodal_field_title"><?php _e('How you want to include this form into page?', 'ARForms'); ?></div>
                    <div class="newmodal_field">
                        <input type="radio" class="rdomodal" checked="checked" name="shortcode_type" value="normal" id="shortcode_type_normal_vc" onclick="showarfpopupfieldlist();" /><label for="shortcode_type_normal_vc" <?php
                        if (is_rtl()) {
                            echo 'style="float:right; margin-right:167px;"';
                        }
                        ?>><span <?php
                                                                                                                                                                                                  if (is_rtl()) {
                                                                                                                                                                                                      echo 'style="margin-left:5px;"';
                                                                                                                                                                                                  }
                                                                                                                                                                                                  ?>></span><?php _e('Internal', 'ARForms'); ?></label>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <input type="radio" class="rdomodal" name="shortcode_type" value="popup" id="shortcode_type_popup_vc" onclick="showarfpopupfieldlist();" /><label for="shortcode_type_popup_vc" <?php
                        if (is_rtl()) {
                            echo 'style="float:right;"';
                        }
                        ?>><span <?php
                                                                                                                                                                              if (is_rtl()) {
                                                                                                                                                                                  echo 'style="margin-left:5px;"';
                                                                                                                                                                              }
                                                                                                                                                                              ?>></span><?php _e('External popup window', 'ARForms'); ?></label></div>
      



                    <div id="show_link_type_vc" style="display:none; margin-top:15px;">   

                        <div class="arfmodal_vcfields" id="normal_link_type"> 	
                            <div class="arfmodal_vcfield_left"><?php _e('Link Type?', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div class="sltmodal" style="float:none; font-size:15px; <?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                    <select class="wpb_vc_param_value" name="type" id="link_type_vc" data-width="272px" onChange="javascript:changetopposition(this.value);
                                            arf_set_link_type_data(this.value)">
                                        <option value="link" selected="selected"><?php _e('Link', 'ARForms'); ?></option>
                                        <option value="button"><?php _e('Button', 'ARForms'); ?></option>
                                        <option value="sticky"><?php _e('Sticky', 'ARForms'); ?></option>
                                        <option value="fly"><?php _e('Fly', 'ARForms'); ?></option>
                                        <option value="onload"><?php _e('On Page Load', 'ARForms'); ?></option>

                                        
                                        <option value="scroll"><?php _e('When user scroll page', 'ARForms'); ?></option>
                                    </select>
                                </div>
                            </div>          
                        </div>

                        <div class="arfmodal_vcfields" id="shortcode_caption_vc"> 	
                            <div class="arfmodal_vcfield_left"><?php _e('Caption :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <input type="text" name="desc" id="short_caption" value="Click here to open Form" class="wpb_vc_param_value txtmodal1" style="width:250px;" />
                            </div>          
                        </div>




                        <div class="arfmodal_vcfields" id="is_scroll_vc" style="display:none;"> 	
                            <div class="arfmodal_vcfield_left" style="vertical-align: top;"><?php _e('Open popup when user scroll % of page after page load', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <input type="text" name="on_scroll" id="open_scroll" value="10" class="wpb_vc_param_value txtmodal1" style="width:70px;" /> %
                                <span style="font-style:italic;">&nbsp;<?php _e('(eg. 100% - end of page)', 'ARForms'); ?></span>
                            </div>          
                        </div>


                        <div class="arfmodal_vcfields" id="is_delay_vc" style="display:none;"> 	
                            <div class="arfmodal_vcfield_left"><?php _e('Open popup after page load', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <input type="text" name="on_delay" id="open_delay" value="0" class="wpb_vc_param_value txtmodal1" style="width:70px;" />
                                <span><?php _e('(in seconds)', 'ARForms'); ?></span>
                            </div>          
                        </div>



                        <div class="arfmodal_vcfields" id="is_sticky_vc" style="display:none;"> 	
                            <div class="arfmodal_vcfield_left"><?php _e('Link Position?', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div class="sltmodal" style="float:none; font-size:15px;<?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                    <select name="position" id="link_position_vc" class="wpb_vc_param_value" data-width="150px">
                                        <option value="top" selected="selected"><?php _e('Top', 'ARForms'); ?></option>
                                        <option value="bottom"><?php _e('Bottom', 'ARForms'); ?></option>
                                        <option value="left" ><?php _e('Left', 'ARForms'); ?></option>
                                        <option value="right"><?php _e('Right', 'ARForms'); ?></option>
                                    </select>
                                </div>
                            </div>          
                        </div>






                        <div class="arfmodal_vcfields" id="overlay_div_vc" style="display:none;"> 	
                            <div class="arfmodal_vcfield_left" style="padding-top: 10px; vertical-align: top;"><?php _e('Background Overlay :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; float: left; margin-top:5px; <?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                    <select name="overlay" class="wpb_vc_param_value" id="overlay" data-width="90px" data-size="4">
                                        <option value="0"><?php _e('0 (None)', 'ARForms'); ?></option>
                                        <option value="0.1" ><?php _e('10%', 'ARForms'); ?></option>
                                        <option value="0.2" ><?php _e('20%', 'ARForms'); ?></option>
                                        <option value="0.3" ><?php _e('30%', 'ARForms'); ?></option>
                                        <option value="0.4" ><?php _e('40%', 'ARForms'); ?></option>
                                        <option value="0.5" ><?php _e('50%', 'ARForms'); ?></option>
                                        <option value="0.6" selected="selected" ><?php _e('60%', 'ARForms'); ?></option>
                                        <option value="0.7" ><?php _e('70%', 'ARForms'); ?></option>
                                        <option value="0.8" ><?php _e('80%', 'ARForms'); ?></option>
                                        <option value="0.9" ><?php _e('90%', 'ARForms'); ?></option>
                                        <option value="1" ><?php _e('100%', 'ARForms'); ?></option>
                                    </select>
                                </div>



                                <div style="display: inline-block; float:left; margin-left:10px;" class="arf_coloroption_sub">
                                    <div data-fid="arf_vc_modal_bg_color" class="arf_coloroption arfhex" style="background: #000000"></div>
                                    <div class="arf_coloroption_subarrow_bg">
                                        <div class="arf_coloroption_subarrow"></div>
                                    </div>
                                </div>
                                <div style="display: inline-block; float: left; height:40px; line-height: 40px; font-size: 12px; font-style: italic;  margin-left:5px;" >(<?php _e('Background Color', 'ARForms'); ?>)</div>
                                <input type="hidden" name="modal_bgcolor" id="arf_vc_modal_bg_color" class="wpb_vc_param_value" value="#000000" />








                            </div> 
                        </div>

                        <div class="arfmodal_vcfields" id="is_close_link_div_vc">
                            <div class="arfmodal_vcfield_left" style="vertical-align: top;"><?php _e('Show Close Button :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <input type="hidden" id="is_close_link_value" value="yes" name="is_close_link"  class="wpb_vc_param_value" />
                                <input onclick="is_close_link_change();" type="radio" class="rdomodal" checked="checked"  name="is_close_link_vc" value="yes" id="show_close_link_yes_vc" />
                                <label for="show_close_link_yes_vc" <?php
                                if (is_rtl()) {
                                    echo 'style="float:right; margin-right:167px;"';
                                }
                                ?>>
                                    <span <?php
                                    if (is_rtl()) {
                                        echo 'style="margin-left:5px;"';
                                    }
                                    ?>></span><?php _e('Yes', 'ARForms'); ?>
                                </label>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <input onclick="is_close_link_change();" type="radio" class="rdomodal" name="is_close_link_vc" value="no" id="show_close_link_no_vc" />
                                <label for="show_close_link_no_vc" <?php
                                if (is_rtl()) {
                                    echo 'style="float:right;"';
                                }
                                ?>>
                                    <span <?php
                                    if (is_rtl()) {
                                        echo 'style="margin-left:5px;"';
                                    }
                                    ?>></span><?php _e('No', 'ARForms'); ?>
                                </label>
                            </div>
                        </div>

                        <div class="arfmodal_vcfields" id="arfmodalbuttonstyles" style="display:none;">
                            <div class="arfmodal_vcfield_left" style='vertical-align:top;padding-top: 10px;'><?php _e('Colors :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div style="display:inline">
                                    <div class="height_setting" style="display:inline;float:left;width:100px;">
                                        <div style="display: inline-block;margin-left: 0px;" id="arf_btn_bgcolor" class="arf_coloroption_sub">
                                            <div data-fid="arf_modal_btn_bg_color.wpb_vc_param_value" class="arf_coloroption arfhex"></div>
                                            <div class="arf_coloroption_subarrow_bg">
                                                <div class="arf_coloroption_subarrow"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="bgcolor" id="arf_modal_btn_bg_color" class="wpb_vc_param_value" />
                                        <div><?php _e('Button Background', 'ARForms'); ?></div>
                                    </div>
                                    <div class="height_setting" style="display:inline;float:left;width:100px;">
                                        <div style="display: inline-block;margin-left: 0px;" id="arf_btn_txtcolor" class="arf_coloroption_sub">
                                            <div style="background:#ffffff;" data-fid="arf_modal_btn_txt_color.wpb_vc_param_value" class="arf_coloroption arfhex"></div>
                                            <div class="arf_coloroption_subarrow_bg">
                                                <div class="arf_coloroption_subarrow"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" name="txtcolor" id="arf_modal_btn_txt_color" class="wpb_vc_param_value" />
                                        <div><?php _e('Button Text', 'ARForms'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="arfmodal_vcfields"> 	
                            <div class="arfmodal_vcfield_left" style="vertical-align: top;padding-top: 10px;"><?php _e('Size :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div style="display:inline;">
                                    <div class="height_setting" style="display: inline;float: left;width: 140px;"><input type="text" onkeyup="if(jQuery(this).val() == 'auto') {jQuery('span#arf_vc_height_px').hide();}else{ jQuery('span#arf_vc_height_px').show();}" class="wpb_vc_param_value txtmodal1" name="height" id="modal_height" value="auto" style="width:70px;" />&nbsp;<span style="display:none;"  class="arf_px" id="arf_vc_height_px"><?php _e('px', 'ARForms'); ?></span><br/><div style="margin-top: 4px;padding-left: 22px;"><?php _e('Height', 'ARForms'); ?></div></div>                    
                                    <div class="height_setting" style="display: inline-block; float: none;">
                                        <input type="text" class="wpb_vc_param_value txtmodal1" name="width" id="modal_width" value="800" style="width:70px;" />&nbsp;<span class="arf_px"><?php _e('px', 'ARForms'); ?></span><br/><div style="margin-top: 4px;padding-left: 22px;"><?php _e('Width', 'ARForms'); ?></div>
                                        <div style="display: inline-block; float: left; font-size: 12px; font-style: italic;"><?php _e('Form width will be overwritten', 'ARForms'); ?></div>
                                    </div>
                                    
                                </div>
                            </div>          
                        </div>
                        <div class="arfmodal_vcfields" id="button_angle_div_vc" style="display:none;"> 	
                            <div class="arfmodal_vcfield_left" style="padding-top: 5px; vertical-align: top;"><?php _e('Button angle :', 'ARForms'); ?></div>
                            <div class="arfmodal_vcfield_right">
                                <div class="sltmodal" style="float:none; font-size:15px;display:inline-block; <?php
                                if (is_rtl()) {
                                    echo 'text-align:right;';
                                } else {
                                    echo 'text-align:left;';
                                }
                                ?>">
                                    <select name="angle" class="wpb_vc_param_value" id="button_angle" data-width="70px" onChange="changeflybutton();">
                                        <option value="0" selected="selected">0</option>
                                        <option value="90" >90</option>
                                        <option value="-90" >-90</option>
                                    </select>
                                </div>
                            </div>          
                        </div>
                    </div>
                </div>

                <div style="float:left; width:100%; height:25px;"> </div>
                <div style="clear:both;"></div>
                <script type="text/javascript">
                    __LINK_POSITION_TOP = '<?php _e('Top', 'ARForms'); ?>';
                    __LINK_POSITION_BOTTOM = '<?php _e('Bottom', 'ARForms'); ?>';
                    __LINK_POSITION_LEFT = '<?php _e('Left', 'ARForms'); ?>';
                    __LINK_POSITION_RIGHT = '<?php _e('Right', 'ARForms'); ?>';
                </script>
            </div>    


            <?php
        }
    }

}
?>