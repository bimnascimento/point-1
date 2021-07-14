<?php

/*
|--------------------------------------------------------------------------
| Plugin options
|--------------------------------------------------------------------------
|
| Here is where you can insert the options model of your plugin.
| These options model will store in WordPress options table
| (usually wp_options).
| You'll may get this option by using $plugin->options property
|
*/

return [
  'Database' => [
    'ignore_innodb'        => false,
    'reset_auto_increment' => true
  ],
  'Options'  => [
    'expiry_date' => 0,
    'safe_mode'   => true
  ],
];