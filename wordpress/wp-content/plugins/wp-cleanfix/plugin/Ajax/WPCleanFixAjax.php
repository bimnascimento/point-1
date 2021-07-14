<?php

namespace WPCleanFix\Ajax;

use WPCleanFix\WPBones\Foundation\WordPressAjaxServiceProvider as ServiceProvider;
use WPCleanFix\Tools\Database;
use WPCleanFix\Tools\Posts;
use WPCleanFix\Tools\Comments;
use WPCleanFix\Tools\Postmeta;

class WPCleanFixAjax extends ServiceProvider
{

  /**
   * List of the ajax actions executed by both logged and not logged users.
   * Here you will used a methods list.
   *
   * @var array
   */
  protected $trusted = [];

  /**
   * List of the ajax actions executed only by logged in users.
   * Here you will used a methods list.
   *
   * @var array
   */
  protected $logged = [
    'wp_cleanfix_refresh',
    'wp_cleanfix',
    'wp_cleanfix_tools_compact_table',
    'wp_cleanfix_tools_post_find',
    'wp_cleanfix_tools_post_replace',
    'wp_cleanfix_tools_comment_find',
    'wp_cleanfix_tools_comment_replace',
    'wp_cleanfix_tools_postmeta_find',
    'wp_cleanfix_tools_postmeta_replace'
  ];

  /**
   * List of the ajax actions executed only by not logged in user, usually from frontend.
   * Here you will used a methods list.
   *
   * @var array
   */
  protected $notLogged = [];

  public function wp_cleanfix_refresh()
  {
    $payloadTest   = isset( $_POST[ 'test' ] ) ? $_POST[ 'test' ] : false;
    $payloadModule = isset( $_POST[ 'module' ] ) ? $_POST[ 'module' ] : false;

    if ( $payloadModule && $payloadTest ) {
      $instance = $GLOBALS[ 'WPCleanFixModules' ]->{$payloadModule}->{$payloadTest};

      $response = [
        'test' => $payloadTest,
        'html' => $instance->htmlRow()
      ];

      return wp_send_json( $response );
    }

    wp_send_json_error();
  }

  public function wp_cleanfix()
  {
    $payloadTest   = isset( $_POST[ 'test' ] ) ? $_POST[ 'test' ] : false;
    $payloadModule = isset( $_POST[ 'module' ] ) ? $_POST[ 'module' ] : false;
    $extra         = isset( $_POST[ 'extra' ] ) ? $_POST[ 'extra' ] : null;

    if ( $payloadModule && $payloadTest ) {
      $instance = $GLOBALS[ 'WPCleanFixModules' ]->{$payloadModule}->{$payloadTest};

      $response = [
        'test' => $payloadTest,
        'html' => $instance->cleanFix( $extra )->test()->htmlRow()
      ];

      return wp_send_json( $response );
    }

    wp_send_json_error();
  }

  public function wp_cleanfix_tools_compact_table()
  {
    $table  = isset( $_POST[ 'table_name' ] ) ? $_POST[ 'table_name' ] : '';
    $backup = isset( $_POST[ 'backup' ] ) ? $_POST[ 'backup' ] : false;

    if ( empty( $table ) ) {
      wp_send_json_error(
        [
          'data' => __( 'No table name', WPCLEANFIX_TEXTDOMAIN )
        ]
      );
    }

    $database = new Database();

    // Enable maintenance mode
    $database->setMaintenance();

    // This operation could take an expensive time processing
    @set_time_limit( 300 );

    // Compact
    $result = $database->compactTable( $table, ( $backup == 'true' ) );

    if ( false === $result ) {
      wp_send_json_error(
        [
          'data' => __( 'Error while compacting', WPCLEANFIX_TEXTDOMAIN )
        ]
      );
    }

    // Turn off maintenance mode
    $database->setMaintenance( false );

    wp_send_json_success(
      [
        'info' => $database->getTableInformation( $table )
      ]
    );
  }

  public function wp_cleanfix_tools_post_find()
  {
    $post_type   = isset( $_POST[ 'post_type' ] ) ? $_POST[ 'post_type' ] : '';
    $post_status = isset( $_POST[ 'post_status' ] ) ? $_POST[ 'post_status' ] : '';
    $find        = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace     = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $posts = new Posts();

    $count = $posts->getCount( $post_type, $post_status, $find, $replace );

    wp_send_json_success(
      [
        'count'    => $count,
        'feedback' => WPCleanFix()->view( 'tools.post-feedback' )->with( 'count', $count )->render()
      ]
    );
  }

  public function wp_cleanfix_tools_post_replace()
  {
    $post_type   = isset( $_POST[ 'post_type' ] ) ? $_POST[ 'post_type' ] : '';
    $post_status = isset( $_POST[ 'post_status' ] ) ? $_POST[ 'post_status' ] : '';
    $find        = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace     = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $posts = new Posts();

    // search again
    $count = $posts->getCount( $post_type, $post_status, $find, $replace );

    $result = $posts->replace( $post_type, $post_status, $find, $replace );

    if ( $result ) {
      wp_send_json_success(
        [
          'feedback' => WPCleanFix()
            ->view( 'tools.post-feedback-replace' )
            ->with( 'count', $count )
            ->with( 'find', $find )
            ->with( 'replace', $replace )
            ->render()
        ]
      );
    }
  }

  public function wp_cleanfix_tools_comment_find()
  {
    $comment_approved = isset( $_POST[ 'comment_approved' ] ) ? $_POST[ 'comment_approved' ] : '';
    $comment_type     = isset( $_POST[ 'comment_type' ] ) ? $_POST[ 'comment_type' ] : '';
    $find             = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace          = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $comments = new Comments();

    $count = $comments->getCount( $comment_approved, $comment_type, $find, $replace );

    wp_send_json_success(
      [
        'count'    => $count,
        'feedback' => WPCleanFix()->view( 'tools.comment-feedback' )->with( 'count', $count )->render()
      ]
    );
  }

  public function wp_cleanfix_tools_comment_replace()
  {
    $comment_approved = isset( $_POST[ 'comment_approved' ] ) ? $_POST[ 'comment_approved' ] : '';
    $comment_type     = isset( $_POST[ 'comment_type' ] ) ? $_POST[ 'comment_type' ] : '';
    $find             = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace          = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $comments = new Comments();

    // search again
    $count = $comments->getCount( $comment_approved, $comment_type, $find, $replace );

    $result = $comments->replace( $comment_approved, $comment_type, $find, $replace );

    if ( $result ) {
      wp_send_json_success(
        [
          'feedback' => WPCleanFix()
            ->view( 'tools.comment-feedback-replace' )
            ->with( 'count', $count )
            ->with( 'find', $find )
            ->with( 'replace', $replace )
            ->render()
        ]
      );
    }
  }

  public function wp_cleanfix_tools_postmeta_find()
  {
    $column  = isset( $_POST[ 'column' ] ) ? $_POST[ 'column' ] : '';
    $find    = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $postmeta = new Postmeta();

    $count = $postmeta->getCount( $column, $find, $replace );

    wp_send_json_success(
      [
        'count'    => $count,
        'feedback' => WPCleanFix()->view( 'tools.postmeta-feedback' )->with( 'count', $count )->render()
      ]
    );
  }

  public function wp_cleanfix_tools_postmeta_replace()
  {
    $column  = isset( $_POST[ 'column' ] ) ? $_POST[ 'column' ] : '';
    $find             = isset( $_POST[ 'find' ] ) ? $_POST[ 'find' ] : '';
    $replace          = isset( $_POST[ 'replace' ] ) ? $_POST[ 'replace' ] : '';

    $postmeta = new Postmeta();

    // search again
    $count = $postmeta->getCount( $column, $find, $replace );

    $result = $postmeta->replace( $column, $find, $replace );

    if ( $result ) {
      wp_send_json_success(
        [
          'feedback' => WPCleanFix()
            ->view( 'tools.postmeta-feedback-replace' )
            ->with( 'count', $count )
            ->with( 'find', $find )
            ->with( 'replace', $replace )
            ->render()
        ]
      );
    }
  }
}