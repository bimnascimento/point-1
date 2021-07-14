<?php

namespace WPCleanFix\Modules\Comments;

use WPCleanFix\Modules\Test;

class SpamTest extends Test
{
  public function test()
  {
    $issues = $this->getCommentsWithApproved( 'spam' );

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s comment marked as spam',
                 'You have %s comments marked as spam',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'comment_author'  => '(%s)',
             'comment_content' => '%s'
           ]
         )
         ->fix( __( 'Fix: click here to delete your SPAM comments.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteCommentsWithApproved( 'spam' );

    return $this;
  }

  public function getName()
  {
    return __( 'Spam', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'These comments are marked as spam.', WPCLEANFIX_TEXTDOMAIN );
  }
}
