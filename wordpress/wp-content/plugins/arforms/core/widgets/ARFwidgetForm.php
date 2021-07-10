<?php

class ARFwidgetForm extends WP_Widget {

    function __construct() {
        $widget_ops = array('description' => __("Display Form of ARForms", 'ARForms'));
        parent::__construct('arforms_widget_form', __('ARForms Form', 'ARForms'), $widget_ops);

        add_action('load-widgets.php', array(&$this, 'arf_load_wiget_colorpicker'));
    }

    function arf_load_wiget_colorpicker() {
        wp_enqueue_style('iris');
        wp_enqueue_script('iris');
    }

    function form($instance) {

        
        $instance = wp_parse_args((array) $instance, array('title' => false, 'form' => false, 'widget_type' => 'normal', 'link_type' => 'link', 'link_position' => 'top', 'link_position_fly' => 'top', 'height' => 'auto', 'width' => '800', 'desc' => 'Click here to open Form', 'button_angle' => '0', 'scroll' => '10', 'delay' => '0', 'overlay' => '0.6', 'show_close_link' => 'yes', 'modal_bgcolor' => '#000000'));

        echo "<style type='text/css'>";
        echo ".wp-picker-container, .wp-picker-container:active{ position:relative; top:15px;left:10px; }";
        echo "</style>";
        ?>

        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'ARForms') ?>:</label>
            <input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr(stripslashes($instance['title'])); ?>" /></p>

        <p><label for="<?php echo $this->get_field_id('form'); ?>"><?php _e('Form', 'ARForms') ?>:</label>


            <?php
            global $arformhelper;
            $arformhelper->forms_dropdown_widget($this->get_field_name('form'), $instance['form'], false, $this->get_field_id('form'))
            ?>
        </p>

        <p><label for=""><?php _e('Form Type', 'ARForms') ?>:</label>
            <br /><input type="radio" class="rdomodal" <?php checked($instance['widget_type'], 'normal'); ?> name="<?php echo $this->get_field_name('widget_type'); ?>" value="normal" id="<?php echo $this->get_field_id('widget_type'); ?>_type_normal" onchange="arf_change_type('<?php echo $this->get_field_name('widget_type'); ?>', '<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>');" /><label for="<?php echo $this->get_field_id('widget_type'); ?>_type_normal"><span></span>&nbsp;<?php _e('Internal', 'ARForms'); ?></label>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['widget_type'], 'popup'); ?> name="<?php echo $this->get_field_name('widget_type'); ?>" value="popup" id="<?php echo $this->get_field_id('widget_type'); ?>_type_popup" onchange="arf_change_type('<?php echo $this->get_field_name('widget_type'); ?>', '<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>');" /><label for="<?php echo $this->get_field_id('widget_type'); ?>_type_popup"><span></span>&nbsp;<?php _e('External popup window', 'ARForms'); ?></label>
        </p>

        <p id="<?php echo $this->get_field_id('link_type'); ?>_label" <?php if ($instance['widget_type'] != 'popup' || $instance['link_type'] == 'onload' || $instance['link_type'] == 'scroll') { ?> style="display:none;"<?php } ?> ><label for="<?php echo $this->get_field_id('desc'); ?>"><?php _e('Label', 'ARForms'); ?></label>
            <input type="text" style="width:220px;" name="<?php echo $this->get_field_name('desc'); ?>" id="<?php echo $this->get_field_id('desc'); ?>" value="<?php echo $instance['desc']; ?>" />
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_div'; ?>" <?php if ($instance['widget_type'] != 'popup') { ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('link_type'); ?>"><?php _e('Link Type ?', 'ARForms') ?>:</label>
            <select onchange="arf_change_link_type('<?php echo $this->get_field_id('link_type'); ?>', '<?php echo $this->get_field_id('link_position'); ?>', '<?php echo $this->get_field_id('link_position_fly'); ?>', '<?php echo $this->get_field_id('button_angle'); ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol') ?>', '<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol') ?>');" name="<?php echo $this->get_field_name('link_type'); ?>" id="<?php echo $this->get_field_id('link_type'); ?>" data-width="150px">
                <option value="link" <?php selected($instance['link_type'], 'link'); ?>><?php _e('Link', 'ARForms'); ?></option>
                <option value="button" <?php selected($instance['link_type'], 'button'); ?>><?php _e('Button', 'ARForms'); ?></option>
                <option value="sticky" <?php selected($instance['link_type'], 'sticky'); ?>><?php _e('Sticky', 'ARForms'); ?></option>
                <option value="fly" <?php selected($instance['link_type'], 'fly'); ?>><?php _e('Fly', 'ARForms'); ?></option>
                <option value="onload" <?php selected($instance['link_type'], 'onload'); ?>><?php _e('On Page Load', 'ARForms'); ?></option>


                <option value="scroll" <?php selected($instance['link_type'], 'scroll'); ?>><?php _e('When user scroll page', 'ARForms'); ?></option>

            </select>
        </p>




        <p id="<?php echo $this->get_field_id('link_type') . '_scroll'; ?>"  <?php echo ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'scroll') ? '' : 'style="display:none"'; ?> ><label for="<?php echo $this->get_field_id('scroll'); ?>"><?php _e('Open popup when user scroll % of page after page load', 'ARForms'); ?></label>
            <input type="text" style="width:77px;" name="<?php echo $this->get_field_name('scroll'); ?>" id="<?php echo $this->get_field_id('scroll'); ?>" value="<?php echo $instance['scroll']; ?>" /> %
            <span style="font-style:italic;">&nbsp;<?php _e('(eg. 100% - end of page)', 'ARForms'); ?></span>
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_delay'; ?>"  <?php echo ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'onload') ? '' : 'style="display:none"'; ?> ><label for="<?php echo $this->get_field_id('delay'); ?>"><?php _e('Open popup after page load', 'ARForms'); ?></label>
            <input type="text" style="width:77px;" name="<?php echo $this->get_field_name('delay'); ?>" id="<?php echo $this->get_field_id('delay'); ?>" value="<?php echo $instance['delay']; ?>" />
            <span><?php _e('(in seconds)', 'ARForms'); ?></span>
        </p>





        <p id="<?php echo $this->get_field_id('link_position') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'sticky') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('link_position'); ?>"><?php _e('Link Position?', 'ARForms') ?>:</label>
            <select name="<?php echo $this->get_field_name('link_position'); ?>"  id="<?php echo $this->get_field_id('link_position'); ?>" data-width="150px">
                <option value="top" <?php selected($instance['link_position'], 'top'); ?>><?php _e('Top', 'ARForms'); ?></option>
                <option value="bottom" <?php selected($instance['link_position'], 'bottom'); ?>><?php _e('Bottom', 'ARForms'); ?></option>
                <option value="left" <?php selected($instance['link_position'], 'left'); ?>><?php _e('Left', 'ARForms'); ?></option>
                <option value="right" <?php selected($instance['link_position'], 'right'); ?>><?php _e('Right', 'ARForms'); ?></option>
            </select>
        </p>

        <p id="<?php echo $this->get_field_id('link_position_fly') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'fly') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label style="text-align:left;"><?php _e('Link Position?', 'ARForms') ?>:</label>
            <select name="<?php echo $this->get_field_name('link_position_fly'); ?>" id="<?php echo $this->get_field_id('link_position'); ?>" data-width="150px" ><label for="<?php echo $this->get_field_id('link_position_fly'); ?>">
                    <option value="left" <?php selected($instance['link_position_fly'], 'left'); ?>><?php _e('Left', 'ARForms'); ?></option>
                    <option value="right" <?php selected($instance['link_position_fly'], 'right'); ?>><?php _e('Right', 'ARForms'); ?></option>
            </select>
        </p>
        
        <div id="<?php echo $this->get_field_id('link_type') . '_overlay'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] != 'fly' and $instance['link_type'] != 'sticky') {
            
        } else {
            ?>style="display:none;" <?php } ?>>
        <div style="width: 100%">
            <label for="<?php echo $this->get_field_id('overlay'); ?>"><?php _e('Background Overlay', 'ARForms') ?>:</label>
            <select name="<?php echo $this->get_field_name('overlay'); ?>" class="txtmodal" id="<?php echo $this->get_field_id('overlay'); ?>" style="width:80px;" >
                <option <?php echo ($instance['overlay'] == '0') ? "selected=selected" : ''; ?> value="0"><?php _e('0 (None)', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.1') ? "selected=selected" : ''; ?> value="0.1" ><?php _e('10%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.2') ? "selected=selected" : ''; ?> value="0.2" ><?php _e('20%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.3') ? "selected=selected" : ''; ?> value="0.3" ><?php _e('30%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.4') ? "selected=selected" : ''; ?> value="0.4" ><?php _e('40%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.5') ? "selected=selected" : ''; ?> value="0.5" ><?php _e('50%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.6') ? "selected=selected" : ''; ?> value="0.6" ><?php _e('60%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.7') ? "selected=selected" : ''; ?> value="0.7" ><?php _e('70%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.8') ? "selected=selected" : ''; ?> value="0.8" ><?php _e('80%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '0.9') ? "selected=selected" : ''; ?> value="0.9" ><?php _e('90%', 'ARForms'); ?></option>
                <option <?php echo ($instance['overlay'] == '1') ? "selected=selected" : ''; ?> value="1" ><?php _e('100%', 'ARForms'); ?></option>
            </select>
        </div>
        <div style="width: 100%; margin-top:15px;">
            <label for="<?php echo $this->get_field_id('overlay'); ?>"><?php _e('Background Color', 'ARForms') ?>:</label>
            <span> <input size="7" type="text" name="<?php echo $this->get_field_name('modal_bgcolor'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $instance['modal_bgcolor'];  ?>"> </span>
        </div>
        </div>



        <p id="<?php echo $this->get_field_id('link_type') . '_close_link'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] != 'fly' and $instance['link_type'] != 'sticky') {
            
        } else {
            ?>style="display:none;"<?php } ?>>
            <label for=""><?php _e('Show Close Button', 'ARForms') ?>:&nbsp;</label>
            <input type="radio" class="rdomodal" <?php checked($instance['show_close_link'], 'yes'); ?> name="<?php echo $this->get_field_name('show_close_link'); ?>" value="yes" id="<?php echo $this->get_field_id('show_close_link'); ?>_yes" />
            <label for="<?php echo $this->get_field_id('show_close_link'); ?>_yes"><span></span><?php _e('Yes', 'ARForms'); ?></label>
            &nbsp;&nbsp;
            <input type="radio" class="rdomodal" <?php checked($instance['show_close_link'], 'no'); ?> name="<?php echo $this->get_field_name('show_close_link'); ?>" value="no" id="<?php echo $this->get_field_id('show_close_link'); ?>_no"  />
            <label for="<?php echo $this->get_field_id('show_close_link'); ?>_no"><span></span><?php _e('No', 'ARForms'); ?></label>
        </p>


        <?php
        $arf_fly_sticky_btn_val = ( isset($instance['arf_fly_modal_btn_bgcol']) and ! empty($instance['arf_fly_modal_btn_bgcol']) ) ? $instance['arf_fly_modal_btn_bgcol'] : '#8ccf7a';
        ?>

        <p id="<?php echo $this->get_field_id('arf_fly_modal_btn_bgcol') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and ( $instance['link_type'] == 'fly' or $instance['link_type'] == 'sticky' or $instance['link_type'] == 'button')) {
            
        } else {
            ?> style="display:none;" <?php } ?>>
            <label style="text-align:left;"><?php _e('Button Background Color', 'ARForms'); ?>: </label>
            <input type="text" name="<?php echo $this->get_field_name('arf_fly_modal_btn_bgcol'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $arf_fly_sticky_btn_val; ?>">
        </p>

        <?php
        $arf_fly_sticky_btn_txtval = (isset($instance['arf_fly_modal_btn_txtcol']) and ! empty($instance['arf_fly_modal_btn_txtcol']) ) ? $instance['arf_fly_modal_btn_txtcol'] : '#ffffff';
        ?>

        <p id="<?php echo $this->get_field_id('arf_fly_modal_btn_txtcol') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and ( $instance['link_type'] == 'fly' or $instance['link_type'] == 'sticky' or $instance['link_type'] == 'button')) {
            
        } else {
            ?> style="display:none;" <?php } ?>>
            <label style="text-align:left;"><?php _e('Text Color', 'ARForms'); ?>:</label>
            <input type="text" name="<?php echo $this->get_field_name('arf_fly_modal_btn_txtcol'); ?>" class="arf_fly_modal_btn_style" value="<?php echo $arf_fly_sticky_btn_txtval; ?>">
        </p>

        <p id="<?php echo $this->get_field_id('link_type') . '_height'; ?>" <?php if ($instance['widget_type'] != 'popup') { ?>style="display:none;"<?php } ?>>
            <label style="text-align:left;"><?php _e('Height :', 'ARForms'); ?></label>&nbsp;&nbsp;<input type="text" onkeyup="if(jQuery(this).val() == 'auto') {jQuery('span#arf_widget_height_px').hide();}else{ jQuery('span#arf_widget_height_px').show();}" class="txtmodal" name="<?php echo $this->get_field_name('height'); ?>" id="<?php echo $this->get_field_id('height'); ?>" value="<?php echo $instance['height']; ?>" style="width:70px;" />&nbsp;<span class="arf_px" id="arf_widget_height_px" style="display: none;" ><?php _e('px', 'ARForms'); ?></span>
        </p> 

        <p id="<?php echo $this->get_field_id('link_type') . '_width'; ?>" <?php if ($instance['widget_type'] != 'popup') { ?>style="display:none;"<?php } ?>>
            <label style="text-align:left;"><?php _e('Width :', 'ARForms'); ?></label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" class="txtmodal" name="<?php echo $this->get_field_name('width'); ?>" id="<?php echo $this->get_field_id('width'); ?>" value="<?php echo $instance['width']; ?>" style="width:70px;" />&nbsp;<?php _e('px', 'ARForms'); ?>
            <span style="display: inline-block; float:left; width:100%; margin-bottom:10px; font-size: 12px; font-style: italic;"><?php _e('Form width will be overwritten', 'ARForms'); ?></span>
        </p>

        <p id="<?php echo $this->get_field_id('button_angle') . '_div'; ?>" <?php
        if ($instance['widget_type'] == 'popup' and $instance['link_type'] == 'fly') {
            
        } else {
            ?>style="display:none;"<?php } ?>><label for="<?php echo $this->get_field_id('button_angle'); ?>"><?php _e('Button Angle', 'ARForms') ?>:</label>

            <select name="<?php echo $this->get_field_name('button_angle'); ?>" class="txtmodal" id="<?php echo $this->get_field_id('button_angle'); ?>" style="width:70px;" >
                <option value="0" <?php
                if ($instance['button_angle'] == 0) {
                    echo "selected=selected";
                }
                ?> >0</option>
                <option value="90" <?php
                if ($instance['button_angle'] == 90) {
                    echo "selected=selected";
                }
                ?>>90</option>
                <option value="-90" <?php
                if ($instance['button_angle'] == -90) {
                    echo "selected=selected";
                }
                ?>>-90</option>
            </select>
        </p>

        <script type="text/javascript">
            function arf_change_type(name, id, link_position, link_position_fly, btn_bg_col, btn_txt_col, angle_div) {
                var type_val = jQuery('input[name="' + name + '"]:checked').val();
                var link_type = jQuery('#' + id).val();
                if (type_val == 'popup') {
                    jQuery('#' + id + '_div').show();
                    jQuery('#' + id + '_label').show();
                    jQuery('#' + id + '_height').show();
                    jQuery('#' + id + '_width').show();
                    jQuery('select#' + id).trigger('change');

                    jQuery('#' + id + '_close_link').show();


                    if (link_type == 'scroll') {
                        jQuery('#' + id + '_scroll').show();
                    } else {
                        jQuery('#' + id + '_scroll').hide();
                    }

                    if (link_type == 'onload') {
                        jQuery('#' + id + '_delay').show();
                    } else {
                        jQuery('#' + id + '_delay').hide();
                    }
                    
                    if (link_type == 'onload' || link_type == 'scroll') {
                        jQuery('#'+id+'_label').hide();
                    } else {
                        jQuery('#'+id+'_label').show();
                    }

                    if (link_type == 'sticky' || link_type == 'fly') {
                        jQuery('#' + id + '_overlay').hide();
                        jQuery('#' + id + '_close_link').hide();
                    } else {
                        jQuery('#' + id + '_overlay').show();
                        jQuery('#' + id + '_close_link').show();
                    }

                } else if (type_val == 'normal') {
                    jQuery('#' + id + '_div').hide();
                    jQuery('#' + id + '_label').hide();
                    jQuery('#' + id + '_height').hide();
                    jQuery('#' + id + '_width').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#' + angle_div + '_div').hide();
                }

            }

            function arf_change_link_type(id, link_position, link_position_fly, button_angle, btn_bg_col, btn_txt_col) {
                var link_type = jQuery('#' + id).val();

                if (link_type == 'sticky') {
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + link_position + '_div').show();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#'+id+'_label').show();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                    
                    
                } else if (link_type == 'fly') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').show();
                    jQuery('#' + button_angle + '_div').show();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#'+id+'_label').show();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();



                } else if (link_type == 'scroll') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#'+id+'_label').hide();


                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_scroll').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();

                } else if (link_type == 'onload') {

                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#'+id+'_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#' + id + '_close_link').show();

                } else if (link_type == 'button') {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').show();
                    jQuery('#' + btn_txt_col + '_div').show();
                    jQuery('#'+id+'_label').show();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();

                } else {
                    jQuery('#' + link_position + '_div').hide();
                    jQuery('#' + link_position_fly + '_div').hide();
                    jQuery('#' + button_angle + '_div').hide();
                    jQuery('#' + btn_bg_col + '_div').hide();
                    jQuery('#' + btn_txt_col + '_div').hide();
                    jQuery('#'+id+'_label').hide();


                    jQuery('#' + id + '_scroll').hide();
                    jQuery('#' + id + '_delay').hide();
                    jQuery('#' + id + '_overlay').hide();
                    jQuery('#' + id + '_close_link').hide();
                }

                if (link_type == 'link') {
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#'+id+'_label').show();
                }
                if (link_type == 'button') {
                    jQuery('#' + id + '_close_link').show();
                    jQuery('#' + id + '_overlay').show();
                    jQuery('#'+id+'_label').show();
                }

            }

            jQuery(document).ready(function () {
                jQuery('.arf_fly_modal_btn_style').iris();
                jQuery(document).click(function (e) {
                    if (!jQuery(e.target).is(".arf_fly_modal_btn_style, .iris-picker, .iris-picker-inner")) {
                        jQuery('.arf_fly_modal_btn_style').iris('hide');
                    }
                });
                jQuery('.arf_fly_modal_btn_style').click(function (event) {
                    jQuery('.arf_fly_modal_btn_style').iris('hide');
                    jQuery(this).iris('show');
                });
            });

        </script>
        <?php
    }

    function update($new_instance, $old_instance) {
        return $new_instance;
    }

    function widget($args, $instance) {
        global $arfform,$arfversion;
        extract($args);
        ?>
        <style>
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.left_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.right_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.top_container,
            .ar_main_div_<?php echo $instance['form']; ?> .arf_submit_div.none_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> #brand-div { font-size: 10px; color: #444444; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.left_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.right_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.top_container,
            .ar_main_div_<?php echo $instance['form']; ?> #brand-div.none_container { text-align:center !important; clear:both !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.left_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.right_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.top_container, 
            .ar_main_div_<?php echo $instance['form']; ?> #hexagon.none_container { text-align:center !important; margin-left:auto !important; margin-right:auto !important; }

            .ar_main_div_<?php echo $instance['form']; ?> .arfsubmitbutton .arf_submit_btn { margin: 10px 0 0 0 !important; } 

        </style>
        <?php
        $form_name = $arfform->getName($instance['form']);
        global $wpdb;
        $form_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "arf_forms WHERE id = %d", $instance['form']));
        if ($form_data) {
            $formoptions = maybe_unserialize($form_data->options);
            if (isset($formoptions['display_title_form']) and $formoptions['display_title_form'] == '1') {
                $is_title = true;
                $is_description = true;
            } else {
                $is_title = false;
                $is_description = false;
            }
        }

        $title = apply_filters('widget_title', empty($instance['title']) ? '' : $instance['title']);
        echo $before_widget;
        echo '<div class="arf_widget_form">';
        if ($title)
            echo $before_title . stripslashes($title) . $after_title;
        $wp_upload_dir = wp_upload_dir();
        if (is_ssl()) {
            $upload_main_url = str_replace("http://", "https://", $wp_upload_dir['baseurl'] . '/arforms/maincss');
        } else {
            $upload_main_url = $wp_upload_dir['baseurl'] . '/arforms/maincss';
        }

        global $armainhelper, $arrecordcontroller;
        $fid = $upload_main_url . '/maincss_' . $instance['form'] . '.css';
        wp_register_style('arfformscss' . $instance['form'], $fid,array(),$arfversion);
        $armainhelper->load_styles(array('arfformscss' . $instance['form']));

        if ($instance['widget_type'] == 'popup') {
            if ($instance['link_type'] == 'sticky')
                $arf_position = $instance['link_position'];
            else if ($instance['link_type'] == 'fly')
                $arf_position = $instance['link_position_fly'];
            else
                $arf_position = '';

            
            echo $arrecordcontroller->show_form_popup($instance['form'], '', $is_title, $is_description, $instance['desc'], $instance['link_type'], $instance['height'], $instance['width'], $arf_position, $instance['button_angle'], $instance['arf_fly_modal_btn_bgcol'], $instance['arf_fly_modal_btn_txtcol'], '', $instance['scroll'], $instance['delay'], $instance['overlay'], $instance['show_close_link'],$instance['modal_bgcolor']);
        } else {
            echo $arrecordcontroller->show_form($instance['form'], '', $is_title, $is_description, false, true);
        }

        $arfsidebar_width = '';
        echo '</div>';
        echo $after_widget;
    }

}
?>