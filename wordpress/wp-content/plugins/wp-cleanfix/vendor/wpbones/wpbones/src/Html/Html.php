<?php

namespace WPCleanFix\WPBones\Html;

class Html
{

  protected static $htmlTags = [
    'a'        => '\WPCleanFix\WPBones\Html\HtmlTagA',
    'button'   => '\WPCleanFix\WPBones\Html\HtmlTagButton',
    'checkbox' => '\WPCleanFix\WPBones\Html\HtmlTagCheckbox',
    'datetime' => '\WPCleanFix\WPBones\Html\HtmlTagDatetime',
    'fieldset' => '\WPCleanFix\WPBones\Html\HtmlTagFieldSet',
    'form'     => '\WPCleanFix\WPBones\Html\HtmlTagForm',
    'input'    => '\WPCleanFix\WPBones\Html\HtmlTagInput',
    'label'    => '\WPCleanFix\WPBones\Html\HtmlTagLabel',
    'optgroup' => '\WPCleanFix\WPBones\Html\HtmlTagOptGroup',
    'option'   => '\WPCleanFix\WPBones\Html\HtmlTagOption',
    'select'   => '\WPCleanFix\WPBones\Html\HtmlTagSelect',
    'textarea' => '\WPCleanFix\WPBones\Html\HtmlTagTextArea',
  ];

  public static function __callStatic( $name, $arguments )
  {
    if ( in_array( $name, array_keys( self::$htmlTags ) ) ) {
      $args = ( isset( $arguments[ 0 ] ) && ! is_null( $arguments[ 0 ] ) ) ? $arguments[ 0 ] : [];

      return new self::$htmlTags[ $name ]( $args );
    }
  }
}