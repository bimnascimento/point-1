<?php

namespace WPCleanFix\Http\Controllers;

use WPCleanFix\ActionsAndFiltersJS\ActionsAndFiltersJSProvider;

class DashboardController extends Controller
{

  public function load()
  {
    wp_enqueue_script( 'common' );
    wp_enqueue_script( 'wp-lists' );
    wp_enqueue_script( 'postbox' );

    wp_enqueue_style( 'wp-cleanfix-dashboard',
                      WPCleanFix()->css . '/wp-cleanfix-dashboard.min.css',
                      [],
                      WPCleanFix()->Version );

    $refs = ActionsAndFiltersJSProvider::enqueueScripts();

    wp_enqueue_script( 'wp-cleanfix-dashboard',
                       WPCleanFix()->js . '/wp-cleanfix-dashboard.min.js',
                       [ $refs ],
                       WPCleanFix()->Version,
                       true );

    $GLOBALS[ 'WPCleanFixModules' ]->addMetaBoxes();
  }

  public function index()
  {
    return WPCleanFix()
      ->view( 'dashboard.index' )
      ->with( 'modules', $GLOBALS[ 'WPCleanFixModules' ] );
  }
}