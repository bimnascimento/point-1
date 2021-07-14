<?php

namespace WPCleanFix\Http\Controllers;

use WPCleanFix\Tools\Database;
use WPCleanFix\Tools\Posts;
use WPCleanFix\Tools\Comments;
use WPCleanFix\Tools\Postmeta;
use WPCleanFix\PureCSSTabs\PureCSSTabsProvider;
use WPCleanFix\PureCSSSwitch\PureCSSSwitchProvider;

class ToolsController extends Controller
{
  public function index()
  {
    // enqueue pure css tabs
    PureCSSTabsProvider::enqueueStyles();

    PureCSSSwitchProvider::enqueueStyles();

    $with = [
      'database' => ( new Database ),
      'posts'    => ( new Posts ),
      'comments' => ( new Comments ),
      'postmeta' => ( new Postmeta ),
    ];

    return WPCleanFix()
      ->view( 'tools.index' )
      ->with( $with )
      ->withAdminStyles( 'wp-cleanfix-tools' )
      ->withAdminScripts( 'wp-cleanfix-tools' );
  }


}