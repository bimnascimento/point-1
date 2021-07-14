<?php

namespace WPCleanFix\PureCSSSwitch;

class PureCSSSwitchProvider
{

  public static function css( $minified = true )
  {
    $file = __FILE__;

    $path = rtrim( plugin_dir_url( $file ), '\/' );

    $minified = $minified ? ".min" : "";

    $css = "{$path}/public/css/wpbones-switch-theme{$minified}.css";

    return $css;
  }

  public static function enqueueStyles( $minified = true )
  {
    wp_enqueue_style( 'wpbones-switch-theme', self::css( $minified ),
                      [],
                      WPCleanFix()->Version );
  }
}