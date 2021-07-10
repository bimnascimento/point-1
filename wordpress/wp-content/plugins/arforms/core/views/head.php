<?php 
global $arfversion;
if (isset($css_file)){ 

    if (is_array($css_file)){
		$i = 1;
        foreach ($css_file as $file) {

			wp_register_style('arfformheadcss-'.$i, $file,array(),$arfversion);
			wp_print_styles('arfformheadcss-'.$i);
			$i++;
		}	
			
    }else{?>

<?php
wp_register_style('arf-formheadcss', $css_file,array(),$arfversion);
wp_print_styles('arf-formheadcss');
?>
<?php } 

}



if (isset($js_file)){ 

    if (is_array($js_file)){
		$i = 1;
        foreach ($js_file as $file) {

			wp_register_script('arf-arformsjs-'.$i, $file,array(),$arfversion);
			wp_print_scripts('arf-arformsjs-'.$i);
			$i++;
 		}
    }else{?>

<?php
wp_register_script('arf-arformsjs', $js_file,array(),$arfversion);
wp_print_scripts('arf-arformsjs');
?>
<?php 

    }

}

?>