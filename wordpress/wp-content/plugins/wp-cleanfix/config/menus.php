<?php

/*
|--------------------------------------------------------------------------
| Plugin Menus routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the menu routes for a plugin.
| In this context the route are the menu link.
|
*/

return [
  'wp_cleanfix_slug_menu' => [
    "menu_title" => "WP CleanFix",
    'capability' => 'manage_options',
    'icon'       => 'dashicons-admin-tools',
    'items'      => [
      [
        "menu_title" => "CleanFix",
        'capability' => 'manage_options',
        'route'      => [
          'load' => 'DashboardController@load',
          'get'  => 'DashboardController@index'
        ],
      ],
      'settings' => [
        "menu_title" => "Settings",
        'route'      => [
          'get'  => 'SettingsController@index',
          'post' => 'SettingsController@update',
        ],
      ],
      [
        "menu_title" => "Tools",
        'route'      => [
          'get'  => 'ToolsController@index',
        ],
      ],
    ]
  ]
];
