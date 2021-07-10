<li id="arfoptionorder_<?php echo $field['id']; ?>-<?php echo $opt_key ?>" class="arfoptionli">
    
    <input type="hidden" name="arf_op_temp_val[<?php echo $field['id'] ?>][]" id="ar_op_value_temp_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" value="<?php echo htmlspecialchars($field_val); ?>" />
    <?php
    if (isset($is_preset_field_choices) && $is_preset_field_choices)
        require(VIEWS_PATH . '/addoptionjs.php');
    ?>
    <span id="arffielddelete_<?php echo $field['id']; ?>-<?php echo $opt_key ?>_container" class="arfsingleoption">

<?php if ($field['type'] != 'select'){ ?>

            <input type="<?php echo $field['type'] ?>" <?php echo ($field['type'] == 'checkbox') ? 'class="class_checkbox checkbox_radio_class"' : 'class="class_radio checkbox_radio_class"'; ?> id="fieldcheck_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" name="arf_op_item_meta[<?php echo $field['id']; ?>]<?php echo ($field['type'] == 'checkbox') ? '[]' : ''; ?>" onchange="arfchangesubcheckradio('<?php echo $field['id'] ?>-<?php echo $opt_key ?>');" value="<?php echo esc_attr($field_val) ?>"<?php echo isset($checked) ? $checked : ''; ?>/>

<?php  } ?>
<?php if ($field['type'] == 'radio' || $field['type'] == 'select' || $field['type'] == 'checkbox'){ ?>
			<?php
			
			if(is_array($opt)) {
				$opt = $opt['label'];
				$field_val = $field_val['value'];
			}
			
			?>
			
            <label class="textbox_label arf_op_not_send_ajax arfoptioneditorfield field_<?php echo $field['id'] ?>_option <?php echo $field['field_options']['separate_value'] ? 'arfwithkey' : ''; ?>" data-fid="<?php echo $field['id'] ?>" data-op-key="<?php echo $opt_key; ?>" id="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>">
                <?php echo htmlspecialchars($opt); ?>
            </label>
           
            <span class="frm_option_key field_<?php echo $field['id'] ?>_option_key" <?php echo $field['field_options']['separate_value'] ? "style='display:block;'" : "style='display:none;'"; ?>>
                <label class="textbox_label arf_op_not_send_ajax arfshowfieldclick arfoptioneditorfield_key" data-fid="<?php echo $field['id'] ?>" data-op-key="<?php echo $opt_key; ?>" id="field_key_<?php echo $field['id'] ?>-<?php echo $opt_key ?>"><?php echo $field_val ?></label>
            </span>

            <a  onclick="arfaddnewfieldoption('<?php echo $field['id'] ?>')" class="frm_single_visible_hover" style="float:left; margin:4px 2px 0 3px;"><img src="<?php echo ARFIMAGESURL ?>/add-plus.png" alt="Add" style="vertical-align:middle;" /></a>
            
            <a onclick="arffielddelete_option(<?php echo $field['id'] ?>,<?php echo $opt_key ?>);" class="frm_single_visible_hover"><img src="<?php echo ARFIMAGESURL ?>/trash.png" alt="Delete" style="vertical-align:middle;float:left;margin-top:5px;margin-left:2px;" /></a>
            <span><img src="<?php echo ARFIMAGESURL ?>/move-icon2.png" alt="Move" style="vertical-align:middle; float:left; cursor:move; margin:6px 0 0 5px;" /></span>
            
        <?php } else { ?>
            
            <label class="arf_op_not_send_ajax arfoptioneditorfield field_<?php echo $field['id'] ?>_option <?php echo $field['field_options']['separate_value'] ? 'arfwithkey' : ''; ?>" id="field_<?php echo $field['id'] ?>-<?php echo $opt_key ?>"><?php echo $opt ?></label>
            <span class="frm_option_key field_<?php echo $field['id'] ?>_option_key" >
                <label class="textbox_label arf_op_not_send_ajax arfshowfieldclick arfoptioneditorfield_key" id="field_key_<?php echo $field['id'] ?>-<?php echo $opt_key ?>"><?php echo $field_val ?></label>
            </span>
            <a href="javascript:arfaddnewfieldoption(<?php echo $field['id'] ?>);" class="frm_single_visible_hover" style="float:left; margin:4px 2px 0 3px;"><img src="<?php echo ARFIMAGESURL ?>/add-plus.png" alt="Add" style="vertical-align:middle;" /></a>
            <a href="javascript:arffielddelete_option(<?php echo $field['id'] ?>,<?php echo $opt_key ?>);" class="frm_single_visible_hover"><img src="<?php echo ARFIMAGESURL ?>/trash.png" alt="Delete" style="vertical-align:middle;" /></a>
            <span><img src="<?php echo ARFIMAGESURL ?>/move-icon2.png" alt="Move" style="vertical-align:middle; float:left; cursor:move; margin:6px 0 0 5px;" /></span>
            
        <?php } ?>
    </span>
    <div class="clear"></div>

    
    <input type="hidden" name="arf_op_value[<?php echo $field['id'] ?>][]" id="ar_op_value_key_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" value="<?php echo htmlspecialchars($field_val); ?>" />
    <input type="hidden" name="arf_op_label[<?php echo $field['id'] ?>][]" id="ar_op_lable_<?php echo $field['id'] ?>-<?php echo $opt_key ?>" value="<?php echo htmlspecialchars($opt); ?>" />
</li>


<?php
unset($field_val);

unset($opt);

unset($opt_key);
?>