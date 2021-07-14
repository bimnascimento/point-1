<?php

namespace WPCleanFix\Modules\Comments;

use WPCleanFix\Modules\Module;

class CommentsModule extends Module
{
  protected $view = 'module.index';

  protected $tests = [
    'WPCleanFix\Modules\Comments\UnapprovedTest',
    'WPCleanFix\Modules\Comments\TrashTest',
    'WPCleanFix\Modules\Comments\SpamTest',
  ];

  public function getMetaBoxTitle()
  {
    return __( 'Comments', WPCLEANFIX_TEXTDOMAIN );
  }

  /*
  |--------------------------------------------------------------------------
  | Module methods
  |--------------------------------------------------------------------------
  |
  | Here you'll find the module methods used by single test.
  |
  */

  public function getCommentsWithApproved( $approved = '0' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT

IF( LENGTH( comment_author ) > 24,
    CONCAT( SUBSTRING( TRIM( comment_author ), 1, 24 ), '...' ),
    TRIM( comment_author )
) AS comment_author,

IF( LENGTH( comment_content ) > 50,
    CONCAT( SUBSTRING( TRIM( comment_content ), 1, 40 ), '...' ),
    TRIM( comment_content )
) AS comment_content

FROM {$wpdb->comments}
WHERE comment_approved = '{$approved}'
ORDER BY comment_ID
SQL;

    $result = $wpdb->get_results( $sql );
    foreach ( $result as $row ) {
      $row->comment_content = esc_attr( preg_replace( '/[^[:print:]]/', '', strip_shortcodes( strip_tags( $row->comment_content ) ) ) );
      if ( strlen( $row->comment_content ) > 40 ) {
        $row->comment_content = substr( $row->comment_content, 0, 40 ) . '...';
      }
      elseif ( empty( $row->content ) ) {
        $row->comment_content = '...';
      }
    }

    return $result;

  }

  public function deleteCommentsWithApproved( $approved = '0' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
DELETE FROM {$wpdb->comments}
WHERE comment_approved = '{$approved}'
SQL;

    return $wpdb->query( $sql );
  }


}
