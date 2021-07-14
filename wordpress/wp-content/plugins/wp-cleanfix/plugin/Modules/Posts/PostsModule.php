<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Module;

class PostsModule extends Module
{
  protected $view = 'module.index';

  protected $tests = [
    'WPCleanFix\Modules\Posts\AutodraftTest',
    'WPCleanFix\Modules\Posts\RevisionsTest',
    'WPCleanFix\Modules\Posts\PostsWithoutUserTest',
    'WPCleanFix\Modules\Posts\OrphanPostMetaTest',
    'WPCleanFix\Modules\Posts\OrphanAttachmentsTest',
    'WPCleanFix\Modules\Posts\OrphanPostTypesTest',
    'WPCleanFix\Modules\Posts\TemporaryTest',
    'WPCleanFix\Modules\Posts\TrashTest',
  ];

  public function getMetaBoxTitle()
  {
    return __( 'Posts, Pages and Custom Post Types', WPCLEANFIX_TEXTDOMAIN );
  }

  /*
  |--------------------------------------------------------------------------
  | Module methods
  |--------------------------------------------------------------------------
  |
  | Here you'll find the module methods used by single test.
  |
  */

  public function getPostsWithStatus( $status = 'auto-draft' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT DISTINCT( COUNT(*) ) AS number, post_title
FROM {$wpdb->posts}
WHERE post_status = '{$status}'
GROUP BY post_title
ORDER BY post_title
SQL;

    return $wpdb->get_results( $sql );
  }

  public function deletePostsWithStatus( $status = 'auto-draft' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql   = <<<SQL
SELECT ID
FROM {$wpdb->posts}
WHERE post_status = '{$status}'
SQL;
    $posts = $wpdb->get_results( $sql );

    foreach ( $posts as $post ) {
      wp_delete_post( $post->ID, true );
    }
  }

  public function getPostsWithType( $type = 'revision' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT DISTINCT( COUNT(*) ) AS number, post_title
FROM {$wpdb->posts}
WHERE post_type = '{$type}'
GROUP BY post_title
ORDER BY post_title
SQL;

    return $wpdb->get_results( $sql );
  }

  public function deletePostsWithType( $type = 'revision' )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql   = <<<SQL
SELECT ID
FROM {$wpdb->posts}
WHERE post_type = '{$type}'
SQL;
    $posts = $wpdb->get_results( $sql );

    foreach ( $posts as $post ) {
      wp_delete_post( $post->ID, true );
    }
  }

  public function getPostsWithoutUser()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $post_status = 'inherit';

    $sql = <<<SQL
SELECT
posts.post_title,
posts.ID AS post_id

FROM {$wpdb->posts} AS posts
LEFT JOIN {$wpdb->users} AS users ON ( users.ID = posts.post_author )

WHERE 1
AND users.ID IS NULL
AND posts.ID IS NOT NULL
AND posts.post_status <> '{$post_status}'
SQL;

    return $wpdb->get_results( $sql );
  }

  public function updatePostsWithoutUser( $user_id )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $posts = $posts = $this->getPostsWithoutUser();

    if ( ! empty( $posts ) ) {
      $stack = array();
      foreach ( $posts as $post ) {
        $stack[] = $post->post_id;
      }
      $ids = implode( ',', $stack );

      $sql = <<<SQL
UPDATE {$wpdb->posts}
SET post_author = {$user_id}
WHERE ID IN( {$ids} )
SQL;
      $wpdb->query( $sql );
    }
  }

  public function getPostMetaWithoutPosts()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT
DISTINCT( COUNT(*) ) AS number,
post_meta.meta_id,
post_meta.meta_key

FROM {$wpdb->postmeta} AS post_meta

LEFT JOIN {$wpdb->posts} posts ON ( posts.ID = post_meta.post_id )

WHERE posts.ID IS NULL
GROUP BY post_meta.meta_key
ORDER BY post_meta.meta_key
SQL;

    return $wpdb->get_results( $sql );
  }

  public function deletePostMetaWithoutPosts()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<< SQL
DELETE post_meta
FROM {$wpdb->postmeta} AS post_meta
LEFT JOIN {$wpdb->posts} AS posts ON ( posts.ID = post_meta.post_id )
WHERE posts.ID IS NULL
SQL;

    $wpdb->query( $sql );
  }

  public function getAttachmentsWithNullPost()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
SELECT
 posts_attachment.post_title,
 posts_attachment.ID as attachment_id

FROM {$wpdb->posts} AS posts_attachment

LEFT JOIN {$wpdb->posts} AS posts ON ( posts_attachment.post_parent = posts.ID )

WHERE 1
AND posts_attachment.post_type = 'attachment'
AND posts_attachment.post_parent > 0
AND posts.ID IS NULL
SQL;

    // Put in cache transient.
    $cache = get_transient( 'wp-cleanfix-posts_attachments' );
    if ( empty( $cache ) ) {
      $cache = $wpdb->get_results( $sql );
      set_transient( 'wp-cleanfix-posts_attachments', $cache, 60 * 60 );
    }

    return $cache;
  }

  public function deleteAttachmentsWithNullPost()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    // Get from cache transient.
    $cache = get_transient( 'wp-cleanfix-posts_attachments' );

    if ( empty( $cache ) ) {
      $cache = $this->getAttachmentsWithNullPost();
      set_transient( 'wp-cleanfix-posts_attachments', $cache, 60 * 60 );
    }

    $stack = array();
    foreach ( $cache as $attachment ) {
      $stack[] = $attachment->attachment_id;
    }
    $ids = implode( ',', $stack );

    $sql = <<< SQL
UPDATE {$wpdb->posts}
SET post_parent = 0
WHERE ID IN ({$ids})
SQL;

    delete_transient( 'wpxcf-posts_attachments' );

    $wpdb->query( $sql );
  }

  public function getTemporaryPostMeta()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<< SQL
SELECT DISTINCT( COUNT(*) ) AS number, post_meta.meta_id, post_meta.meta_key
FROM {$wpdb->postmeta} post_meta
LEFT JOIN {$wpdb->posts} posts ON posts.ID = post_meta.post_id

WHERE posts.ID IS NOT NULL
AND (
   post_meta.meta_key = '_edit_lock'
OR post_meta.meta_key = '_edit_last'
OR post_meta.meta_key = '_wp_old_slug'
   )
GROUP BY post_meta.meta_key
ORDER BY post_meta.meta_key
SQL;

    return $wpdb->get_results( $sql );
  }

  public function deleteTemporaryPostMeta()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    $sql = <<<SQL
DELETE post_meta FROM {$wpdb->postmeta} AS post_meta

LEFT JOIN {$wpdb->posts} posts ON ( posts.ID = post_meta.post_id )

WHERE 1
AND posts.ID IS NOT NULL
AND (
   post_meta.meta_key = '_edit_lock'
OR post_meta.meta_key = '_edit_last'
OR post_meta.meta_key = '_wp_old_slug'
   )
SQL;

    $wpdb->query( $sql );
  }

  public function getUnregisteredPostTypes(  )
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    // get the registered post types
    $registerePostTypes = array_keys( get_post_types() );

    $array = [];

    foreach( $registerePostTypes as $key ) {
      $array[] = "'{$key}'";
    }

    $post_types_str = implode( ',', $array );

    $sql = <<<SQL
SELECT COUNT( posts.post_type ) AS number, posts.post_type 
FROM {$wpdb->posts} AS posts
WHERE posts.post_type NOT IN( {$post_types_str} )
GROUP BY posts.post_type
SQL;

    return $wpdb->get_results( $sql );
  }

  public function deleteUnregisteredPostTypes()
  {
    /**
     * @var wpdb $wpdb
     */
    global $wpdb;

    // get the registered post types
    $registerePostTypes = array_keys( get_post_types() );

    $array = [];

    foreach( $registerePostTypes as $key ) {
      $array[] = "'{$key}'";
    }

    $post_types_str = implode( ',', $array );

    $sql = <<<SQL
DELETE FROM {$wpdb->posts}
WHERE post_type NOT IN( {$post_types_str} )
SQL;

    $wpdb->query( $sql );

  }
}
