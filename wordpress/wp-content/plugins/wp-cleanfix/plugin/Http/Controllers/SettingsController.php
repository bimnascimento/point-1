<?php

namespace WPCleanFix\Http\Controllers;

use WPCleanFix\PureCSSTabs\PureCSSTabsProvider;
use WPCleanFix\PureCSSSwitch\PureCSSSwitchProvider;

class SettingsController extends Controller
{

  public function index()
  {
    // enqueue pure css tabs
    PureCSSTabsProvider::enqueueStyles();

    PureCSSSwitchProvider::enqueueStyles();

    // GET
    return WPCleanFix()
      ->view( 'settings.index' )
      ->withAdminStyles( 'wp-cleanfix-common' );
  }

  public function store()
  {
    // POST
  }

  public function update()
  {
    if ( $this->request->verifyNonce( 'wp_cleanfix' ) ) {

      // enqueue pure css tabs
      PureCSSTabsProvider::enqueueStyles();

      PureCSSSwitchProvider::enqueueStyles();

      WPCleanFix()->options->update( $this->request->getAsOptions() );

      return WPCleanFix()->view( 'settings.index' )
                     ->with( 'feedback', __( 'Settings updated!', WPCLEANFIX_TEXTDOMAIN ) );
    }
    else {
      return WPCleanFix()->view( 'settings.index' )
                     ->with( 'feedback', __( 'Action not allowed!', WPCLEANFIX_TEXTDOMAIN ) );
    }
  }

  public function destroy()
  {
    // DELETE
  }

}