<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class TemporaryTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getTemporaryPostMeta();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s temporary post meta',
                 'You have %s temporary posts meta',
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
    $this->deleteTemporaryPostMeta();

    return $this;
  }

  public function getName()
  {
    return __( 'Temporary', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'These records are stored by WordPress as temporary data. If you like you can safely delete them.', WPCLEANFIX_TEXTDOMAIN );

  }
}
