<?php

namespace WPCleanFix\Modules\Taxonomies;

use WPCleanFix\Modules\Test;

class ConsistentTermsTest extends Test
{
  public function test()
  {
    $issues = $this->getConsistentTerms();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s orphan term. This term does not extis as taxonomy',
                 'You have %s orphan terms. These terms does not exists as taxonomy',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [ 'name' => '%s' ]
         )
         ->fix( __( 'Fix: click here to repair terms', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteConsistentTerms();

    return $this;
  }

  public function getName()
  {
    return __( 'Consistent Terms', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'These are orphan Terms and they don\'t exist in the taxonomy table.', WPCLEANFIX_TEXTDOMAIN );
  }
}
