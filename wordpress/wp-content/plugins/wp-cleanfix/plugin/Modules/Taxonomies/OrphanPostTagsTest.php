<?php

namespace WPCleanFix\Modules\Taxonomies;

use WPCleanFix\Modules\Test;

class OrphanPostTagsTest extends Test
{
  public function test()
  {
    $issues = $this->getOrphanPostTags();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s orphan tag',
                 'You have %s orphan tags',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'name' => '%s',
           ]
         )
         ->fix( __( 'Fix: click here to safely and permanently delete them.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    $this->deleteOrphanPostTags();

    return $this;
  }

  public function getName()
  {
    return __( 'Orphan Post Tags', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Check for unused post tags.', WPCLEANFIX_TEXTDOMAIN );
  }
}
