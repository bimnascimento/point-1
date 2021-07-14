<?php

namespace WPCleanFix\Tools;

if ( ! defined( 'ABSPATH' ) ) exit;

class Posts
{

  public function __get( $name )
  {
    $method_name = 'get' . ucfirst( $name ) . 'Attribute';

    if ( method_exists( $this, $method_name ) ) {
      return call_user_func( [ $this, $method_name ] );
    }
  }

  public function getPostTypes()
  {
    $result = [];

    $postTypes = get_post_types();

    foreach ( $postTypes as $key ) {
      $post_type      = get_post_type_object( $key );
      $result[ $key ] = $post_type->labels->singular_name;
    }

    return $result;
  }

  public function getPostStatuses()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT post_status
FROM {$wpdb->posts}
GROUP BY post_status
ORDER BY post_status
SQL;

    $results = $wpdb->get_results( $sql );

    $statuses = [];

    foreach ( $results as $post_status ) {
      if ( ! empty( $post_status->post_status ) ) {
        $statuses[ $post_status->post_status ] = ucfirst( $post_status->post_status );
      }
    }

    return $statuses;
  }

  public function getCount( $postType, $postStatus, $find = '', $replace = '' )
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

    if ( ! empty( $postType ) && in_array( $postType, array_keys( $this->getPostTypes() ) ) ) {
      $where[] = sprintf( 'post_type = "%s"', $postType );
    }

    if ( ! empty( $postStatus ) && in_array( $postStatus, array_keys( $this->getPostStatuses() ) ) ) {
      $where[] = sprintf( 'post_status = "%s"', $postStatus );
    }

    $whereStr = implode( ' AND ', $where );

    $sql = <<<SQL
SELECT
  COUNT(*) AS total_posts,
  SUM( ROUND (
      (
        LENGTH(post_content)
          - LENGTH( REPLACE ( post_content, "{$find}", "") )
      ) / LENGTH("{$find}")
  ) ) AS count,
  SUM( IF( ROUND (
          (
            LENGTH(post_content)
              - LENGTH( REPLACE ( post_content, "{$find}", "") )
          ) / LENGTH("{$find}")
      ), 1, 0 ) ) AS affected_posts

FROM {$wpdb->posts}

{$whereStr}
SQL;

    $row = $wpdb->get_row( $sql );

    return [
      'total_posts'    => $row->total_posts,
      'count'          => is_null( $row->count ) ? 0 : $row->count,
      'affected_posts' => is_null( $row->affected_posts ) ? 0 : $row->affected_posts,
    ];

  }

  public function replace( $postType, $postStatus, $find = '', $replace = '' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    if ( empty( $find ) || $find == $replace ) {
      return false;
    }

    $where = [ 'WHERE 1' ];

    if ( ! empty( $postType ) && in_array( $postType, array_keys( $this->getPostTypes() ) ) ) {
      $where[] = sprintf( 'post_type = "%s"', $postType );
    }

    if ( ! empty( $postStatus ) && in_array( $postStatus, array_keys( $this->getPostStatuses() ) ) ) {
      $where[] = sprintf( 'post_status = "%s"', $postStatus );
    }

    $whereStr = implode( ' AND ', $where );

    $sql = <<<SQL
UPDATE {$wpdb->posts}
SET post_content = REPLACE (post_content, '{$find}', '{$replace}')
{$whereStr}
SQL;

    $result = $wpdb->query( $sql );

    return $result;

  }
}