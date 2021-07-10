<?php 
session_start();


$image_width =  200;
$image_height = 50;
$characters_on_image = 8;
$font = '../../fonts/monofont.ttf';


$possible_letters = '23456789bcdfghjkmnpqrstvwxyz';
$random_dots = 0;
$random_lines = 20;
$captcha_text_color="ee432e";
$captcha_noice_color = "ee432e";

$code = '';
$i = 0;
while ($i < $characters_on_image) 
{ 
	$code .= substr($possible_letters, mt_rand(0, strlen($possible_letters)-1), 1);
	$i++;
}
$font_size = $image_height * 0.75;
$image = @imagecreate($image_width, $image_height);



$background_color = imagecolorallocate($image, 255, 255, 255);

$arr_text_color = RGB_HEX($captcha_text_color);
$text_color = imagecolorallocate($image, $arr_text_color['red'], 
$arr_text_color['green'], $arr_text_color['blue']);
$arr_noice_color = RGB_HEX($captcha_noice_color);
$image_noise_color = imagecolorallocate($image, $arr_noice_color['red'], 
$arr_noice_color['green'], $arr_noice_color['blue']);


for( $i=0; $i<$random_dots; $i++ ) 
{
	imagefilledellipse($image, mt_rand(0,$image_width),
 	mt_rand(0,$image_height), 2, 3, $image_noise_color);
}


for( $i=0; $i<$random_lines; $i++ ) 
{
	imageline($image, mt_rand(0,$image_width), mt_rand(0,$image_height),
 	mt_rand(0,$image_width), mt_rand(0,$image_height), $image_noise_color);
}


$textbox = imagettfbbox($font_size, 0, $font, $code); 
$x = ($image_width - $textbox[4])/2;
$y = ($image_height - $textbox[5])/2;
imagettftext($image, $font_size, 0, $x, $y, $text_color, $font , $code);


header('Content-Type: image/jpeg');
imagejpeg($image);
imagedestroy($image);
$form_id = $_REQUEST['form_id'];
$_SESSION['vpb_captcha_code_'.$form_id] = $code;

if($_REQUEST['is_update'] == 1)
{
    global $wpdb;

    
    $wpdb->query("update wp_options set option_value = '".$code."' where option_name= 'vpb_captcha_code_".$form_id."'");
}

function RGB_HEX ($hexstr)
{
	$int = hexdec($hexstr);
	return array("red" => 0xFF & ($int >> 0x10),"green" => 0xFF & ($int >> 0x8),"blue" => 0xFF & $int);
}
?>