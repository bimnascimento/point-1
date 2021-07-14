<?php

namespace WPCleanFix\PureCSSTabs;

class PureCSSTabsProvider
{

  public static function css( $minified = true )
  {
    $file = __FILE__;

    $path = rtrim( plugin_dir_url( $file ), '\/' );

    $minified = $minified ? ".min" : "";

    $css = "{$path}/public/css/wpbones-tabs{$minified}.css";

    return $css;
  }

  public static function enqueueStyles( $minified = true )
  {
    wp_enqueue_style( 'wpbones-tabs', self::css( $minified ),
                      [],
                      WPCleanFix()->Version );
  }

  /**
   * Display the open tab.
   *
   * @param string $label    The label of tab.
   * @param null   $id       Optional. ID of tab. If null, will sanitize_title() the label.
   * @param bool   $selected Optional. Default false. TRUE for checked.
   */
  public static function openTab( $label, $id = null, $selected = false )
  {
    if ( is_null( $id ) ) {
      $id = sanitize_title( $label );
    }

    if( is_string( $selected ) ) {
      $selected = ( sanitize_title( $selected ) === $id );
    }

    ?>
    <input id="<?php echo $id ?>"
           type="radio"
           name="tabs"
      <?php checked( $selected, true ) ?>
           aria-hidden="true">
    <label for="<?php echo $id ?>"
           tabindex="0">
      <?php echo $label ?>
    </label>
    <div class="wpbones-tab">
    <?php
  }

  public static function closeTab()
  {
    ?></div><?php
  }

  /**
   * Display tabs by array
   *
   *     self::tabs(
   *       'tab-1' => [ 'label' => 'Tab 1', 'content' => 'Hello', 'selected' => true ],
   *       'tab-2' => [ 'label' => 'Tab 1', 'content' => 'Hello' ],
   *       ...
   *     );
   *
   * @param array $array
   */
  public static function tabs( $array = [] )
  {
    if ( ! empty( $array ) ) {
      ?>
      <div class="wpbones-tabs"><?php
      foreach ( $array as $key => $tab ) {
        $selected = isset( $tab[ 'selected' ] ) ? $tab[ 'selected' ] : null;
        self::openTab( $tab[ 'label' ], $key, $selected );
        echo $tab[ 'content' ];
        self::closeTab();
      }
      ?></div><?php
    }
  }
}