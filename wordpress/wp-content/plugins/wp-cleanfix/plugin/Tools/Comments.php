<?php

namespace WPCleanFix\Tools;

if ( ! defined( 'ABSPATH' ) ) exit;

class Comments
{

  public function __get( $name )
  {
    $method_name = 'get' . ucfirst( $name ) . 'Attribute';

    if ( method_exists( $this, $method_name ) ) {
      return call_user_func( [ $this, $method_name ] );
    }
  }

  public function getCommentsApproved()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT comment_approved
FROM {$wpdb->comments}
GROUP BY comment_approved
ORDER BY comment_approved
SQL;

    $rows = $wpdb->get_results( $sql );

    $result = array(
      ''  => __( 'All', WPCLEANFIX_TEXTDOMAIN ),
      '0' => __( 'Unapproved', WPCLEANFIX_TEXTDOMAIN ),
      '1' => __( 'Approved', WPCLEANFIX_TEXTDOMAIN ),
    );
    foreach ( $rows as $comment_approved ) {
      if ( ! empty( $comment_approved->comment_approved ) && ! is_numeric( $comment_approved->comment_approved ) ) {
        $result[ $comment_approved->comment_approved ] = ucfirst( $comment_approved->comment_approved );
      }
    }

    return $result;
  }

  public function getCommentsType()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT comment_type
FROM {$wpdb->comments}
GROUP BY comment_type
ORDER BY comment_type
SQL;

    $rows = $wpdb->get_results( $sql );

    $result = [];

    foreach ( $rows as $comment_type ) {
      if ( ! empty( $comment_type->comment_type ) ) {
        $result[ $comment_type->comment_type ] = ucfirst( $comment_type->comment_type );
      }
    }

    return $result;
  }

  public function getCount( $commentType, $commentApproved, $find = '', $replace = '' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $count = 0;

    if ( empty( $find ) || $find == $replace ) {
      return [ 'count' => $count ];
    }

    $where = [ 'WHERE 1' ];

    if ( ! empty( $commentType ) && in_array( $commentType, array_keys( $this->getCommentsType() ) ) ) {
      $where[] = sprintf( 'comment_type = "%s"', $commentType );
    }

    if ( ! empty( $commentApproved ) && in_array( $commentApproved, array_keys( $this->getCommentsApproved() ) ) ) {
      $where[] = sprintf( 'comment_approved = "%s"', $commentApproved );
    }

    $whereStr = implode( ' AND ', $where );

    $sql = <<<SQL
SELECT
  COUNT(*) AS total_comments,
  SUM( ROUND (
      (
        LENGTH(comment_content)
          - LENGTH( REPLACE ( comment_content, "{$find}", "") )
      ) / LENGTH("{$find}")
  ) ) AS count,
  SUM( IF( ROUND (
          (
            LENGTH(comment_content)
              - LENGTH( REPLACE ( comment_content, "{$find}", "") )
          ) / LENGTH("{$find}")
      ), 1, 0 ) ) AS affected_comments

FROM {$wpdb->comments}

{$whereStr}
SQL;

    $row = $wpdb->get_row( $sql );

    return [
      'total_comments'    => $row->total_comments,
      'count'             => is_null( $row->count ) ? 0 : $row->count,
      'affected_comments' => is_null( $row->affected_comments ) ? 0 : $row->affected_comments,
    ];

  }

  public function replace( $commentType, $commentApproved, $find = '', $replace = '' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    if ( empty( $find ) || $find == $replace ) {
      return false;
    }

    $where = [ 'WHERE 1' ];

    if ( ! empty( $commentType ) && in_array( $commentType, array_keys( $this->getCommentsType() ) ) ) {
      $where[] = sprintf( 'comment_type = "%s"', $commentType );
    }

    if ( ! empty( $commentApproved ) && in_array( $commentApproved, array_keys( $this->getCommentsApproved() ) ) ) {
      $where[] = sprintf( 'comment_approved = "%s"', $commentApproved );
    }

    $whereStr = implode( ' AND ', $where );

    $sql = <<<SQL
UPDATE {$wpdb->comments}
SET comment_content = REPLACE (comment_content, '{$find}', '{$replace}')
{$whereStr}
SQL;

    $result = $wpdb->query( $sql );

    return $result;

  }
}