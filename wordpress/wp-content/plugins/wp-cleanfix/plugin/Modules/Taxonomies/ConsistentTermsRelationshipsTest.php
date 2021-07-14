<?php

namespace WPCleanFix\Modules\Taxonomies;

use WPCleanFix\Modules\Test;

class ConsistentTermsRelationshipsTest extends Test
{
  public function test()
  {
    $issues = $this->getConsistentTermsRelationships();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s row broken. This object ID or term taxonomy ID is not valid',
                 'You have %s broken rows. These object ID or terms taxonomy ID are not valid',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'name'     => '%s',
             'taxonomy' => '(%s)',
           ]
         )
         ->fix( __( 'Fix: click here to repair terms relationships.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteConsistentTermsRelationships();

    return $this;
  }

  public function getName()
  {
    return __( 'Consistent Terms/Relationships', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Check for term_relationships table and for missing taxonomy IDs.', WPCLEANFIX_TEXTDOMAIN );
  }
}
