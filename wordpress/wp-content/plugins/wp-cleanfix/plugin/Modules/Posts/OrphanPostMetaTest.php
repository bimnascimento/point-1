<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class OrphanPostMetaTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getPostMetaWithoutPosts();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s orphan post meta',
                 'You have %s orphan post meta',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'meta_key' => '%s',
             'number'   => '(%s)'
           ]
         )
         ->fix( __( 'Fix: click here to safely and permanently delete them.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    // for this method see parent module
    $this->deletePostMetaWithoutPosts();

    return $this;
  }

  public function getName()
  {
    return __( 'Orphan Post Meta', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Post Meta data not assigned to a post. These are the extra properties for a standard post type (post, page, custom post type, etc...). Sometimes, post meta records may exist without being associated with post: in this case, post meta are orphan and can be deleted.', WPCLEANFIX_TEXTDOMAIN );

  }
}
