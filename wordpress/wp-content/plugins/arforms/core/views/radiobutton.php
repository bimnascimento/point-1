<?php


if (is_array($field['options'])){

    foreach($field['options'] as $opt_key => $opt){

        $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);

        $opt = apply_filters('show_field_label', $opt, $opt_key, $field);

        if (is_array($opt)) {
            $opt = $opt['label'];
            $field_val = ($field['field_options']['separate_value']) ? $field_val['value'] : $opt;
        }
        
        if($field['type'] == 'checkbox' ){
            
            if(is_array($field['value'])){
                $checked = '';
                foreach($field['value'] as $as_val){
                    if(trim($as_val) === trim($field_val)){
                         $checked = 'checked="true"';
                    }
                }

            }else{
                $checked = (isset($field['value']) and ( (!is_array($field['value']) && $field['value'] == $field_val ) || (is_array($field['value']) && in_array($field_val, $field['value'])))) ? ' checked="true"' : '';
            }
        }else{
            $checked = (isset($field['value']) and ( (!is_array($field['value']) && $field['value'] == $field_val ) || (is_array($field['value']) && in_array($field_val, $field['value'])))) ? ' checked="true"' : '';
        }
            
        
        

        require(VIEWS_PATH .'/optionsingle.php');

        

        unset($checked);

    }  

}

?>