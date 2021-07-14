<?php

namespace WPCleanFix\Modules\Database;

use WPCleanFix\Modules\Module;

class DatabaseModule extends Module
{
  protected $view = 'module.index';

  protected $tests = [
    'WPCleanFix\Modules\Database\DatabaseTablesTest'
  ];

  public function __construct(  )
  {
    parent::__construct();

    add_action( 'wp_cleanfix_detail_DatabaseTablesTest', [ $this, 'wp_cleanfix_detail'] );
  }

  public function getMetaBoxTitle()
  {
    return __( 'Database' );
  }

  public function wp_cleanfix_detail(  )
  {
    echo WPCleanFix()->view( 'database.index' )->with( 'module', $this );
  }

}
