<?php
defined('_VALID_AI') or die('Direct Access to this location is not allowed.');
/*
xPlugin Name: PHP Browser Detection
xPlugin URI: http://wordpress.org/extend/plugins/php-browser-detection/
xDescription: Use PHP to detect browsers for conditional CSS or to detect mobile phones.
xVersion: 2.2.3
xAuthor: Mindshare Studios, Inc.
xAuthor URI: http://mind.sh/are
xLicense: GNU General Public License v3
xLicense URI: license.txt
xText Domain: php-browser-detection
*/

/**
 *
 * Copyright 2009-2014 Marty Thornley / Mindshare Studios, Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 3, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

// add support for Windows systems without PHP's fnmatch()
if(!function_exists('fnmatch')) {
	function fnmatch($pattern, $string) {
		return preg_match("#^".strtr(preg_quote($pattern, '#'), array('\*' => '.*', '\?' => '.'))."$#i", $string);
	}
}

/**
 *  Cache the reading by using ob_get_contents across several requests.
 *  The data is stored in the request also to avoid several readings 
 *  when different browsers are configured.  
 */ 
function ai_parse_ini_file_ext ($file) {
    if (isset($GLOBALS["ai_browser_file"])) {
        return $GLOBALS["ai_browser_file"];
    }
    ob_start();
    include $file;
    $str = ob_get_contents();
    ob_end_clean();
    if(version_compare(PHP_VERSION, '5.3.0') >= 0) {
      $browser_data = parse_ini_string($str, true, INI_SCANNER_RAW);
    } else {
      die("Advanced iframe pro browser detection needs at least php 5.3!<br />Please upgrade your php version to use this feature.");
    } 
    $GLOBALS["ai_browser_file"] = $browser_data;
    return $browser_data;  
}

/**
 * Returns array of all browser info.
 *
 * @usage $browserInfo = php_browser_info();
 *
 * @return array
 */
function ai_php_browser_info() {
	
  if (isset($GLOBALS["ai_browser_info"])) {
        return $GLOBALS["ai_browser_info"];
  }
  $agent = $_SERVER['HTTP_USER_AGENT'];

	$x = dirname(__FILE__);
	$browscap = $x.'/php-browser-detection-browscap.ini';
	if(!is_file(realpath($browscap))) {
		return array('error' => 'No php-browser-detection-browscap.ini file found.');
	}
	$agent = $agent ? $agent : $_SERVER['HTTP_USER_AGENT'];
  $yu = array();
	$q_s = array("#\.#", "#\*#", "#\?#");
	$q_r = array("\.", ".*", ".?");

  $brows = ai_parse_ini_file_ext(realpath($browscap), TRUE);

	foreach($brows as $k => $t) {
		if(fnmatch($k, $agent)) {
			$yu['browser_name_pattern'] = $k;
			$pat = preg_replace($q_s, $q_r, $k);
			$yu['browser_name_regex'] = strtolower("^$pat$");
			foreach($brows as $g => $r) {
				if(isset($t['Parent']) && $t['Parent'] == $g) {
					foreach($brows as $a => $b) {
            if(isset($r['Parent']) &&  $r['Parent'] == $a) {
							$yu = array_merge($yu, $b, $r, $t);
							foreach($yu as $d => $z) {
								$l = strtolower($d);
								$hu[$l] = $z;
							}
						}
					}
				}
			}
			break;
		}
	}
	if(isset($hu)) {
		$GLOBALS["ai_browser_info"] = $hu;
    return $hu;
	} else {
    $GLOBALS["ai_browser_info"] = FALSE;
		return FALSE;
	}
}

/**
 *
 * Returns the browser version number.
 *
 * @return mixed
 */
function ai_get_browser_version() {
	$browserInfo = ai_php_browser_info();
	return $browserInfo['version'];
}

/**
 *
 * Conditional to test for Firefox.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_firefox($version = '') {
	$browserInfo = ai_php_browser_info();
	if(isset($browserInfo['browser']) && $browserInfo['browser'] == 'Firefox') {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 *
 * Conditional to test for Safari.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_safari($version = '') {
	$browserInfo = ai_php_browser_info();
	if(isset($browserInfo['browser']) && $browserInfo['browser'] == 'Safari') {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for Chrome.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_chrome($version = '') {
	$browserInfo = ai_php_browser_info();
	if(isset($browserInfo['browser']) && $browserInfo['browser'] == 'Chrome') {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for Opera.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_opera($version = '') {
	$browserInfo = ai_php_browser_info();
	if(isset($browserInfo['browser']) && $browserInfo['browser'] == 'Opera') {
		if($version == '') {
			return TRUE;
		} elseif($browserInfo['majorver'] == $version) {
			return TRUE;
		} else {
			return FALSE;
		}
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for IE.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_ie($version = '') {
	$browserInfo = ai_php_browser_info();
	if(isset($browserInfo['browser']) && $browserInfo['browser'] == 'IE') {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for mobile devices.
 *
 * @return bool
 */
function ai_is_mobile() {
	$browser_info = ai_php_browser_info();
  if(isset($browser_info['ismobiledevice'])) {
		if($browser_info['ismobiledevice'] == 1 || $browser_info['ismobiledevice'] == "true" || 
       isset($browser_info['device_type']) &&  strpos($browser_info['device_type'], "Mobile") !== FALSE) {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Conditional to test for tablet devices.
 *
 * @return bool
 */
function ai_is_tablet() {
	$browser_info = ai_php_browser_info();
	if(isset($browser_info['istablet'])) {
		if($browser_info['istablet'] == 1 || $browser_info['istablet'] == "true" || 
        isset($browser_info['device_type']) && strpos($browser_info['device_type'], "Tablet") !== FALSE) {
			return TRUE;
		}
	}
	return FALSE;
}


/**
 * Conditional to test for android devices.
 *
 * @return bool
 */
function ai_is_android() {
	$browser_info = ai_php_browser_info();
	if(isset($browser_info['platform'])) {
		if($browser_info['platform'] == 'Android') {
			return TRUE;
		}
	}
	return FALSE;
}

/**
 * Conditional to test for android devices.
 *
 * @return bool
 */
function ai_is_ios() {
	$browser_info = ai_php_browser_info();
	if(isset($browser_info['platform'])) {
		if($browser_info['platform'] == 'iOS') {
			return TRUE;
		}
	}
	return FALSE;
}


/**
 * Conditional to test for android tablet devices.
 *
 * @return bool
 */
function ai_is_androidtablet() {
  return ai_is_android() && ai_is_tablet();
}

/**
 * Conditional to test for iPhone.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_iphone($version = '') {
	$browserInfo = ai_php_browser_info();
	if((isset($browserInfo['browser']) && $browserInfo['browser'] == 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for iPad.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_ipad($version = '') {
	$browserInfo = ai_php_browser_info();
	if(preg_match("/iPad/", $browserInfo['browser_name_pattern'], $matches) || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for iPod.
 *
 * @param string $version
 *
 * @return bool
 */
function ai_is_ipod($version = '') {
	$browserInfo = ai_php_browser_info();
	if(preg_match("/iPod/", $browserInfo['browser_name_pattern'], $matches)) {
		if($version == '') :
			return TRUE;
		elseif($browserInfo['majorver'] == $version) :
			return TRUE;
		else :
			return FALSE;
		endif;
	} else {
		return FALSE;
	}
}

/**
 * Conditional to test for desktop devices.
 *
 * @return bool
 */
function ai_is_desktop() {
	$browser_info = ai_php_browser_info();   
  if(isset($browser_info['device_type']) && strpos($browser_info['device_type'], "Desktop") !== FALSE) {
		return TRUE;
	}
	return FALSE;
}

/**
 *  Test if a browser = now crawler/bot or something like that.
 */ 
function ai_is_browser() {
  return ai_is_desktop() || ai_is_tablet() ||  ai_is_mobile();
}

// everything below here was removed as it is not used, needed or depricated