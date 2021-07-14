<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class RevisionsTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getPostsWithType();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s post in revision',
                 'You have %s posts in revision',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'post_title' => '%s',
             'number'     => '(%s)'
           ]
         )
         ->fix( __( 'Fix: click here to delete your post revisions.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    // for this method see parent module
    $this->deletePostsWithType();

    return $this;
  }

  public function getName()
  {
    return __( 'Revisions', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Post in revision. WordPress auto-generates post revisions each time you save a newer version of the post. If you do not need them, you can permanently delete them to gain space in the database.', WPCLEANFIX_TEXTDOMAIN );

  }
}
