<?php

namespace WPCleanFix\Modules;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Module
{
  protected $tests = [];

  protected $view = '';

  protected $instances = [];

  abstract public function getMetaBoxTitle();

  public function __construct()
  {
    foreach ( $this->tests as $test ) {
      $key                     = wp_cleanfix_get_base_classname( $test );
      $this->instances[ $key ] = new $test( $this );
    }
  }

  public function __get( $name )
  {
    foreach ( $this->instances as $className => $instance ) {
      if ( $name == $className || strtolower( $className ) == $name ) {
        return $instance;
      }
    }

    $method_name = 'get' . ucfirst( $name ) . 'Attribute';

    if ( method_exists( $this, $method_name ) ) {
      return call_user_func( [ $this, $method_name ] );
    }

    return null;
  }

  public function getTestsAttribute()
  {
    return $this->instances;
  }

  public function getToolBarAttribute()
  {
    ob_start(); ?>

    <div class="wp-cleanfix-toolbar">
      <div class="wp-cleanfix-refresh-all alignleft">
        <button class="button button-secondary">
          <span class="dashicons dashicons-update"></span> <?php _e( 'Refresh all', WPCLEANFIX_TEXTDOMAIN ) ?>
        </button>
      </div>
      <div class="wp-cleanfix-fix-all alignright">
        <button class="button button-primary">
          <span class="dashicons dashicons-editor-removeformatting"></span> <?php _e( 'Fix all', WPCLEANFIX_TEXTDOMAIN ) ?>
        </button>
      </div>
    </div>

    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

  public function addMetaBox()
  {
    add_meta_box( sanitize_title( get_called_class() ),
                  $this->getMetaBoxTitle(),
                  [ $this, 'view' ],
                  get_current_screen(),
                  'normal',
                  'core'
    );
  }

  public function view()
  {
    echo WPCleanFix()
      ->view( $this->view )
      ->with( 'module', $this );
  }

}