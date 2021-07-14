<?php

namespace WPCleanFix\Modules\Options;

use WPCleanFix\Modules\Test;

class ExpiredSiteTransientTest extends Test
{
  public function test()
  {
    $issues = $this->getExpiredTransients( '_site' );

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s expired site transient',
                 'You have %s expired site transients',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'transient_name' => '%s',
             'expired'        => '(%s)'
           ]
         )
         ->fix( __( 'Fix: click here to delete your expired site transients.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteExpiredTransients( '_site' );

    return $this;
  }

  public function getName()
  {
    return __( 'Expired Site Transients', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Transients data are temporary values stored in the options database tables. When a transient expires you can safely remove it.', WPCLEANFIX_TEXTDOMAIN );
  }
}
