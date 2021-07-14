<?php

namespace WPCleanFix\Modules;

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class Test
{
  protected $module = null;

  protected $level = 'warning';

  protected $detail = '';

  protected $confirm = '';

  protected $filter = '';

  protected $beforeSend = '';

  protected $fix = '';

  protected $count = 0;

  protected $deferer = false;

  protected $issues = [];

  abstract public function test();

  abstract public function cleanFix();

  abstract public function getName();

  abstract public function getDescription();

  public function __construct( $module )
  {
    $this->module = $module;

    if ( ! $this->deferer ) {
      $this->test();
    }
  }

  public function __call( $method_name, $arguments )
  {
    if ( method_exists( $this->module, $method_name ) ) {
      return call_user_func_array( [ $this->module, $method_name ], $arguments );
    }
  }

  public function module()
  {
    return $this->module;
  }

  public function issues( $issues )
  {
    $this->issues = $issues;

    return $this;
  }

  public function count( $value )
  {
    $this->count = $value;

    return $this;
  }

  public function level( $value )
  {
    $this->level = $value;

    return $this;
  }

  public function detail( $value )
  {
    $this->detail = $value;

    return $this;
  }

  public function confirm( $value )
  {
    $this->confirm = sprintf( 'data-confirm="%s"', $value );

    return $this;
  }

  public function filter( $value )
  {
    $this->filter = sprintf( 'data-filter="%s"', $value );

    return $this;
  }

  public function beforeSend( $value )
  {
    $this->beforeSend = sprintf( 'data-before_send="%s"', $value );

    return $this;
  }

  public function detailSelect( $firstElement, $formats = [], $extra = '' )
  {
    ob_start(); ?>

    <select name="foo">
      <option disabled="disabled"
              selected
              style="display:none">
        <?php echo $firstElement ?>
      </option>
      <?php if ( ! empty( $formats ) ) : ?>
        <?php foreach ( $this->issues as $issue ) : ?>
          <option disabled="disabled">
            <?php foreach ( $formats as $column => $format ) : ?>
              <?php if ( $column == 'expired' ) : ?>
                <?php printf( $format, human_time_diff( $issue->{$column} ) . ' ' . __( 'ago' ) ) ?>
              <?php else : ?>
                <?php printf( $format, $issue->{$column} ) ?>
              <?php endif; ?>
            <?php endforeach; ?>
          </option>
        <?php endforeach; ?>
      <?php endif; ?>
    </select>
    <?php echo $extra ?>

    <?php
    $content = ob_get_contents();
    ob_end_clean();

    $this->detail = $content;

    return $this;
  }

  public function fix( $value )
  {
    $this->fix = $value;

    return $this;
  }

  protected function hasToFix()
  {
    if ( count( $this->issues ) > 0 ) {
      return true;
    }

    return false;
  }

  public function getStatus()
  {
    if ( $this->hasToFix() ) {
      if ( $this->level == 'warning' ) {
        return '<span class="dashicons dashicons-warning"></span>';
      }
    }

    return '<span class="dashicons dashicons-yes"></span>';
  }

  public function getDetail()
  {
    if ( $this->hasToFix() ) {
      return $this->detail;
    }
  }

  public function getCleanFix()
  {
    if ( $this->hasToFix() ) {
      return '<button class="button button-primary"><span class="dashicons dashicons-editor-removeformatting"></span></button>';
    }
  }

  public function htmlRow()
  {
    $testClassName   = wp_cleanfix_get_base_classname( get_called_class() );
    $moduleClassName = wp_cleanfix_get_base_classname( get_class( $this->module ) );

    ob_start(); ?>

    <tr data-module="<?php echo $moduleClassName ?>"
        class="wp-cleanfix-test <?php sanitize_title( get_called_class() ) ?>">

      <td colspan="5"
          class="wp-cleanfix-hidle">
        <img class="loading"
             src="/wp-admin/images/loading.gif"/>
      </td>

      <td class="wp-cleanfix-refresh">
        <button data-test="<?php echo $testClassName ?>"
                class="button button-secondary">
          <span class="dashicons dashicons-update"></span>
        </button>
      </td>

      <td class="wp-cleanfix-title">
        <details>
          <summary><?php echo $this->getName() ?></summary>
          <small><?php echo $this->getDescription() ?></small>
        </details>

      </td>

      <td class="wp-cleanfix-status">
        <?php echo $this->getStatus() ?>
      </td>

      <td class="wp-cleanfix-content">
        <?php echo $this->getDetail() ?>
      </td>

      <td class="wp-cleanfix-actions">
        <?php if ( $this->hasToFix() ) : ?>
          <button data-test="<?php echo $testClassName ?>"
                  title="<?php echo $this->fix ?>"
            <?php echo $this->confirm ?>
            <?php echo $this->filter ?>
            <?php echo $this->beforeSend ?>
                  class="button button-primary">
            <span class="dashicons dashicons-editor-removeformatting"></span>
          </button>
        <?php endif; ?>
      </td>

    </tr>
    <?php do_action( "wp_cleanfix_detail_{$testClassName}" ) ?>

    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;

  }
}