<?php

namespace WPCleanFix\Providers;

use WPCleanFix\WPBones\Support\ServiceProvider;

class WPCleanFixModulesProviders extends ServiceProvider
{
  protected $modules = [
    'WPCleanFix\Modules\Database\DatabaseModule',
    'WPCleanFix\Modules\Posts\PostsModule',
    'WPCleanFix\Modules\Taxonomies\TaxonomiesModule',
    'WPCleanFix\Modules\Options\OptionsModule',
    'WPCleanFix\Modules\Comments\CommentsModule',
    'WPCleanFix\Modules\Users\UsersModule',
  ];

  protected $instances = [];

  public function __get( $name )
  {
    foreach ( $this->instances as $key => $module ) {
      if ( $name == $key || strtolower( $key ) == $name ) {
        return $module;
      }
    }
  }

  public function register()
  {
    //plugin list
    add_action( 'plugin_action_links_' . WPCleanFix()->pluginBasename, [ $this, 'plugin_action_links' ], 10, 4 );

    foreach ( $this->modules as $className ) {
      $key                     = wp_cleanfix_get_base_classname( $className );
      $this->instances[ $key ] = new $className;
    }

    $GLOBALS[ 'WPCleanFixModules' ] = $this;
  }

  public function plugin_action_links( $links )
  {
    $clean    = '<a href="' . menu_page_url( 'wp_cleanfix_slug_menu', false ) . '">' . __( 'Clean & Fix', WPCLEANFIX_TEXTDOMAIN ) . '</a>';
    $settings = '<a href="' . menu_page_url( 'wpcleanfix_settings', false ) . '">' . __( 'Settings', WPCLEANFIX_TEXTDOMAIN ) . '</a>';

    array_unshift( $links, $clean, $settings );

    return $links;
  }

  public function addMetaBoxes()
  {
    foreach ( $this->instances as $module ) {
      $module->addMetaBox();
    }

    return $this->instances;
  }

  public function getModules()
  {
    return $this->instances;
  }

}