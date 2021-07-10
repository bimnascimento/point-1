<ul id="arfoptionul_<?php echo $field['id'] ?>" class="arfoptionul">
    <?php
    if ($field['type'] == 'radio' or $field['type'] == 'checkbox') {
        require(VIEWS_PATH . '/radiobutton.php');
    } else {
        foreach ($field['options'] as $opt_key => $opt) {
            $field_val = apply_filters('arfdisplaysavedfieldvalue', $opt, $opt_key, $field);
            $opt = apply_filters('show_field_label', $opt, $opt_key, $field);
            require(VIEWS_PATH . '/optionsingle.php');
        }
    }
    ?>
</ul>   

