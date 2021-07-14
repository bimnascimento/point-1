<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class OrphanPostTypesTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getUnregisteredPostTypes();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s orphan post type',
                 'You have %s orphan post types',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'post_type' => '%s',
             'number'    => '(%s)'
           ]
         )
         ->confirm( __( 'Warning! Are you sure to delete the orphan posts permanently?', WPCLEANFIX_TEXTDOMAIN ) )
         ->fix( __( 'Fix: click here to delete orphan posts.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  public function cleanFix()
  {
    // for this method see parent module
    $this->deleteUnregisteredPostTypes();

    return $this;
  }

  public function getName()
  {
    return __( 'Orphan Post Types', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Some deactivated or deleted plugins could have left old post types in your database posts table. If you are almost sure that plugin does not exist anymore, you can delete these rows.', WPCLEANFIX_TEXTDOMAIN );

  }
}
