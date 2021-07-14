<?php

/**
 * Plugin Name: WP CleanFix
 * Plugin URI: http://undolog.com
 * Description: The all in one tool for check, repair, fix and optimize your WordPress blog
 * Version: 5.3.5
 * Author: Giovambattista Fazioli
 * Author URI: http://undolog.com
 * Text Domain: wp-cleanfix
 * Domain Path: localization
 *
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/
use WPCleanFix\WPBones\Foundation\Plugin;

require_once __DIR__ . '/bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Bootstrap the plugin
|--------------------------------------------------------------------------
|
| We need to bootstrap the plugin.
|
*/

// comodity define for text domain
define( 'WPCLEANFIX_TEXTDOMAIN', 'wp-cleanfix' );

$GLOBALS[ 'WPCleanFix' ] = require_once __DIR__ . '/bootstrap/plugin.php';

if ( ! function_exists( 'WPCleanFix' ) ) {

  /**
   * Return the instance of plugin.
   *
   * @return Plugin
   */
  function WPCleanFix()
  {
    return $GLOBALS[ 'WPCleanFix' ];
  }
}