<?php

namespace WPCleanFix\Tools;

if ( ! defined( 'ABSPATH' ) ) exit;

class Database
{

  const COMAPCT_POSTFIX = '_compact';
  const BACKUP_POSTFIX  = '_backup';
  const TIME_LIMIT      = 300;

  protected $tables = [];

  public function __get( $name )
  {
    $method_name = 'get' . ucfirst( $name ) . 'Attribute';

    if ( method_exists( $this, $method_name ) ) {
      return call_user_func( [ $this, $method_name ] );
    }
  }

  protected function getTablesAttribute()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $this->tables = [
      $wpdb->commentmeta => __( 'Comment Meta', WPCLEANFIX_TEXTDOMAIN ),
      $wpdb->options     => __( 'Options', WPCLEANFIX_TEXTDOMAIN ),
      $wpdb->postmeta    => __( 'Post Meta', WPCLEANFIX_TEXTDOMAIN ),
      $wpdb->usermeta    => __( 'User Meta', WPCLEANFIX_TEXTDOMAIN ),
    ];

    return $this->tables;

  }

  /**
   * Return the temporary table name from a WordPress table name. Return FALSE on error,
   *
   * @param $original
   *
   * @return bool|string
   */
  public function createTemporaryTable( $original )
  {
    if ( empty( $original ) ) {
      return false;
    }

    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $table_name = sprintf( '%s%s', $original, self::COMAPCT_POSTFIX );

    // Drop table - if exists of not
    $wpdb->hide_errors();
    $sql    = sprintf( 'DROP TABLE %s', $table_name );
    $result = $wpdb->query( $sql );
    $wpdb->show_errors();

    // Create table
    $sql    = sprintf( 'CREATE TABLE %s LIKE %s', $table_name, $original );
    $result = $wpdb->query( $sql );

    if ( false === $result ) {
      return false;
    }

    return $table_name;
  }

  /**
   * Execute a copy from orginal table and returns an integer corresponding to the number of rows affected/selected.
   * If there is a MySQL error, the function will return FALSE.
   *
   * @param string $tableName A WordPress database ( $wpdb->options )
   * @param bool   $doBackup  Create a backup
   *
   * @return bool|int
   */
  public function compactTable( $tableName, $doBackup )
  {
    if ( empty( $tableName ) ) {
      return false;
    }

    global $wpdb;

    $compact_table_name = $this->createTemporaryTable( $tableName );

    if ( empty( $compact_table_name ) ) {
      return false;
    }

    $result = false;

    switch ( $tableName ) {

      case $wpdb->commentmeta :
        $sql    = sprintf( 'INSERT INTO %s (comment_id,meta_key,meta_value) SELECT %s.comment_id, %s.meta_key, %s.meta_value FROM %s', $compact_table_name, $tableName, $tableName, $tableName, $tableName );
        $result = $wpdb->query( $sql );
        break;

      case $wpdb->options:
        $sql    = sprintf( 'INSERT INTO %s (option_name,option_value,autoload) SELECT %s.option_name, %s.option_value, %s.autoload FROM %s', $compact_table_name, $tableName, $tableName, $tableName, $tableName );
        $result = $wpdb->query( $sql );
        break;

      case $wpdb->usermeta:
        $sql    = sprintf( 'INSERT INTO %s (user_id,meta_key,meta_value) SELECT %s.user_id, %s.meta_key, %s.meta_value FROM %s', $compact_table_name, $tableName, $tableName, $tableName, $tableName );
        $result = $wpdb->query( $sql );
        break;

      case $wpdb->postmeta:
        $sql    = sprintf( 'INSERT INTO %s (post_id,meta_key,meta_value) SELECT %s.post_id, %s.meta_key, %s.meta_value FROM %s', $compact_table_name, $tableName, $tableName, $tableName, $tableName );
        $result = $wpdb->query( $sql );
        break;
    }

    if ( false === $result ) {
      return false;
    }

    // Do a backup copy
    $backupName = sprintf( '%s%s', $tableName, self::BACKUP_POSTFIX );

    // Drop table - if exists of not
    $wpdb->hide_errors();
    $sql    = sprintf( 'DROP TABLE %s', $backupName );
    $result = $wpdb->query( $sql );
    $wpdb->show_errors();

    if ( $doBackup ) {
      $sql    = sprintf( 'RENAME TABLE %s TO %s', $tableName, $backupName );
      $result = $wpdb->query( $sql );
    }
    else {
      $wpdb->hide_errors();
      $sql    = sprintf( 'DROP TABLE %s', $tableName );
      $result = $wpdb->query( $sql );
      $wpdb->show_errors();
    }

    $sql    = sprintf( 'RENAME TABLE %s TO %s', $compact_table_name, $tableName );
    $result = $wpdb->query( $sql );

    if ( false === $result ) {
      return false;
    }

    $wpdb->flush();

    return $result;

  }

  /**
   * Hardcore maintenance mode
   *
   * @param bool $enable Optional. TRUE to enable a low-level maintenance mode
   */
  public function setMaintenance( $enable = true )
  {
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    WP_Filesystem();

    global $wp_filesystem;

    // Special .maintenance file
    $file = $wp_filesystem->abspath() . '.maintenance';
    if ( $enable ) {

      // Create maintenance file to signal that we are upgrading
      $maintenance_string = '<?php $upgrading = ' . time() . '; ?>';
      $wp_filesystem->delete( $file );
      $wp_filesystem->put_contents( $file, $maintenance_string, FS_CHMOD_FILE );
    }
    else if ( ! $enable && $wp_filesystem->exists( $file ) ) {
      $wp_filesystem->delete( $file );
    }
  }

  public function getTableInformation( $tableName )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $info = $wpdb->get_row( "SHOW TABLE STATUS LIKE '{$tableName}'" );

    return $info;
  }

}