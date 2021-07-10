<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */
function wc_deposits_woocommerce_is_active() {
  if (!function_exists( 'is_plugin_active_for_network'))
    require_once(ABSPATH . '/wp-admin/includes/plugin.php');
  // Check if WooCommerce is active
  if (!in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return is_plugin_active_for_network('woocommerce/woocommerce.php');
  }
  return true;
}

/** http://jaspreetchahal.org/how-to-lighten-or-darken-hex-or-rgb-color-in-php-and-javascript/
* @param $color_code
* @param int $percentage_adjuster
* @return array|string
* @author Jaspreet Chahal
*/
function wc_deposits_adjust_colour($color_code,$percentage_adjuster = 0) {
  $percentage_adjuster = round($percentage_adjuster/100,2);
  if(is_array($color_code)) {
    $r = $color_code["r"] - (round($color_code["r"])*$percentage_adjuster);
    $g = $color_code["g"] - (round($color_code["g"])*$percentage_adjuster);
    $b = $color_code["b"] - (round($color_code["b"])*$percentage_adjuster);

    return array("r"=> round(max(0,min(255,$r))),
      "g"=> round(max(0,min(255,$g))),
      "b"=> round(max(0,min(255,$b))));
  }
  else if(preg_match("/#/",$color_code)) {
    $hex = str_replace("#","",$color_code);
    $r = (strlen($hex) == 3)? hexdec(substr($hex,0,1).substr($hex,0,1)):hexdec(substr($hex,0,2));
    $g = (strlen($hex) == 3)? hexdec(substr($hex,1,1).substr($hex,1,1)):hexdec(substr($hex,2,2));
    $b = (strlen($hex) == 3)? hexdec(substr($hex,2,1).substr($hex,2,1)):hexdec(substr($hex,4,2));
    $r = round($r - ($r*$percentage_adjuster));
    $g = round($g - ($g*$percentage_adjuster));
    $b = round($b - ($b*$percentage_adjuster));

    return "#".str_pad(dechex( max(0,min(255,$r)) ),2,"0",STR_PAD_LEFT)
      .str_pad(dechex( max(0,min(255,$g)) ),2,"0",STR_PAD_LEFT)
      .str_pad(dechex( max(0,min(255,$b)) ),2,"0",STR_PAD_LEFT);

  }
}

/**
* @brief returns the frontend colours from the WooCommerce settings page, or the defaults.
*
* @return array
*/

function wc_deposits_woocommerce_frontend_colours()
{
  $colors = (array)get_option('woocommerce_colors');
  if (empty($colors['primary'])) $colors['primary'] = '#ad74a2';
  if (empty($colors['secondary'])) $colors['secondary'] = '#f7f6f7';
  if (empty($colors['highlight'])) $colors['highlight'] = '#85ad74';
  if (empty($colors['content_bg'])) $colors['content_bg'] = '#ffffff';
  return $colors;
}
