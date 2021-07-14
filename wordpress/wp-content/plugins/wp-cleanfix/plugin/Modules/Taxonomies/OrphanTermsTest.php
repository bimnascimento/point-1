<?php

namespace WPCleanFix\Modules\Taxonomies;

use WPCleanFix\Modules\Test;

class OrphanTermsTest extends Test
{
  public function test()
  {
    $issues = $this->getOrphanTerms();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s orphan generic term',
                 'You have %s orphan generic terms',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'name'     => '%s',
             'taxonomy' => ' (%s)'
           ]
         )
         ->fix( __( 'Fix: click here to delete all orphan terms.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteOrphanTerms();

    return $this;
  }

  public function getName()
  {
    return __( 'Orphan Terms', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Check for unused generic terms.', WPCLEANFIX_TEXTDOMAIN );
  }
}
