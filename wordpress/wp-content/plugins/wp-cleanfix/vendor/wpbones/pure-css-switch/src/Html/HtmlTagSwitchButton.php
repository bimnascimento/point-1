<?php

namespace WPCleanFix\PureCSSSwitch\Html;

use WPCleanFix\WPBones\Html\HtmlTag;

if ( ! function_exists( 'wpbones_is_true' ) ) {

  /**
   * Utility to check if a value is true.
   *
   * @param mixed $value String, boolean or integer.
   *
   * @return bool
   */
  function wpbones_is_true( $value )
  {
    return ! in_array( strtolower( $value ), [ '', 'false', '0', 'no', 'n', 'off', null ] );
  }
}

class HtmlTagSwitchButton extends HtmlTag
{
  protected $attributes = [
    'name'  => null,
    'id'    => null,
    'value' => '1',
    'class' => ''
  ];

  protected $guardedAttributes = [
    'theme'       => 'flat-round',
    'right_label' => null,
    'left_label'  => null,
    'checked'     => false,
    'disabled'    => false,
    'mode'        => 'switch'
  ];

  public static function __callStatic( $name, $arguments )
  {
    $args = ( isset( $arguments[ 0 ] ) && ! is_null( $arguments[ 0 ] ) ) ? $arguments[ 0 ] : [];

    $instance = new self();

    return $instance->{$name}( $args );
  }

  public function html()
  {
    if ( empty( $this->id ) ) {
      $this->id = $this->name;
    }

    if ( empty( $this->name ) ) {
      $this->name = $this->id;
    }

    $mode = 'switch';
    if( in_array( $this->mode, [ 'switch', 'select' ] ) ) {
      $mode = $this->mode;
    }

    $checked    = wpbones_is_true( $this->checked ) ? 'checked="checked"' : '';
    $disabled   = wpbones_is_true( $this->disabled ) ? 'disabled="disabled"' : '';
    $leftLabel  = wpbones_is_true( $this->left_label ) ? "<label for=\"{$this->id}\">{$this->left_label}</label>" : '';
    $rightLabel = wpbones_is_true( $this->right_label ) ? "<label for=\"{$this->id}\">{$this->right_label}</label>" : '';

    ob_start(); ?>

    <div class="wpbones-switch-button wpbones-switch-mode-<?php echo $mode ?> wpbones-switch-button-<?php echo $this->theme ?> <?php echo $disabled ? 'disabled' : '' ?>">
      <input type="hidden"
             name="<?php echo $this->name ?>"
             value="0"/>
      <?php echo $leftLabel ?>
      <input
        <?php echo $this->formatDataAttributes() ?>
        <?php echo $this->formatAttributes() ?>
        <?php echo $this->formatGlobalAttributes() ?>
          type="checkbox"
        <?php echo $checked ?>
        <?php echo $disabled ?>
          value="<?php echo $this->value ?>"/>
      <label for="<?php echo $this->id ?>"></label>
      <?php echo $rightLabel ?>
    </div>

    <?php
    $html = ob_get_contents();
    ob_end_clean();

    return $html;
  }
}