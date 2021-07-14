<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class TrashTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getPostsWithStatus( 'trash' );

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s post in trash',
                 'You have %s posts in trash',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'post_title' => '%s',
             'number'     => '(%s)'
           ]
         )
         ->confirm( __( 'Warning! Are you sure to delete your trashed posts permanently?', WPCLEANFIX_TEXTDOMAIN ) )
         ->fix( __( 'Fix: click here to delete your posts in trash.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    // for this method see parent module
    $this->deletePostsWithStatus( 'trash' );

    return $this;
  }

  public function getName()
  {
    return __( 'Trash', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Post in trash.', WPCLEANFIX_TEXTDOMAIN );

  }
}
