<?php

namespace WPCleanFix\Tools;

if ( ! defined( 'ABSPATH' ) ) exit;

class Postmeta
{

  public function __get( $name )
  {
    $method_name = 'get' . ucfirst( $name ) . 'Attribute';

    if ( method_exists( $this, $method_name ) ) {
      return call_user_func( [ $this, $method_name ] );
    }
  }

  public function getCount( $column = 'meta_key', $find = '', $replace = '' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $count = 0;

    if ( empty( $find ) || $find == $replace ) {
      return [ 'count' => $count ];
    }

    if ( ! in_array( $column, [ 'meta_key', 'meta_value' ] ) ) {
      return [ 'count' => $count ];
    }

    if( $column == 'meta_key' && empty( $replace ) ) {
      return [ 'count' => $count ];
    }


    $sql = <<<SQL
SELECT
    COUNT(*) AS total_postmeta,
    ( SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE {$column} = '{$find}' ) AS affected_postmeta
    
FROM {$wpdb->postmeta}
SQL;

    $row = $wpdb->get_row( $sql );

    return [
      'count'             => $row->total_postmeta,
      'affected_postmeta' => is_null( $row->affected_postmeta ) ? 0 : $row->affected_postmeta,
    ];

  }

  public function replace( $column = 'meta_key', $find = '', $replace = '' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    if ( empty( $find ) || $find == $replace ) {
      return false;
    }

    if ( ! in_array( $column, [ 'meta_key', 'meta_value' ] ) ) {
      return false;
    }

    if( $column == 'meta_key' && empty( $replace ) ) {
      return false;
    }

    $sql = <<<SQL
UPDATE {$wpdb->postmeta}
SET {$column} = '{$replace}'
WHERE {$column} = '{$find}'
SQL;

    $result = $wpdb->query( $sql );

    return $result;

  }
}