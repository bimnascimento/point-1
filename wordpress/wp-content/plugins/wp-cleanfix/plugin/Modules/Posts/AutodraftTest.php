<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class AutodraftTest extends Test
{
  public function test()
  {
    $issues = $this->getPostsWithStatus();

    $count = isset( $issues[0]->number ) ? $issues[0]->number : 0;

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s post in autodraft',
                 'You have %s posts in autodraft',
                 $count, WPCLEANFIX_TEXTDOMAIN
             ),
             $count
           ),
           [
             'post_title' => '%s',
             'number'     => '(%s)'
           ]
         )
         ->fix( __( 'Fix: click here to delete the auto drafted posts. This action is safe.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deletePostsWithStatus();

    return $this;
  }

  public function getName()
  {
    return __( 'Autodraft', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Post in Auto Draft. WordPress saves an Auto Draft in the database every n seconds. The Auto draft is different from draft, however you can safely remove it to gain more space in the database.', WPCLEANFIX_TEXTDOMAIN );
  }
}
