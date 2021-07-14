<?php

namespace WPCleanFix\Modules\Comments;

use WPCleanFix\Modules\Test;

class UnapprovedTest extends Test
{
  public function test()
  {
    $issues = $this->getCommentsWithApproved();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s comment unapproved',
                 'You have %s comments unapproved',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'comment_author'  => '(%s)',
             'comment_content' => '%s'
           ]
         )
         ->fix( __( 'Fix: click here to delete your unapproved comments.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteCommentsWithApproved( );

    return $this;
  }

  public function getName()
  {
    return __( 'Unapproved', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Comments unapproved', WPCLEANFIX_TEXTDOMAIN );
  }
}
