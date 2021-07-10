<?php
    /**
     * ReduxSAFramework Config File
     */

    if ( ! class_exists( 'ReduxSA' ) ) {
        return;
    }

    // Get page list
    global $wpdb;
    foreach ($wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish'", ARRAY_A) as $_page){
          $_pages[$_page['ID']] = $_page['post_title'];
    }


    // Basic options
    $cache_modes = array(
          'disk_cache_rewrite' => esc_html__('Disk Cache with Rewrites', 'swift_performance'),
          'disk_cache_php' => esc_html__('Disk Cache with PHP', 'swift_performance'),
    );

    // Memcached support
    // TODO memcache isn't implemented in 1.0
    if (false && class_exists('Memcached')){
      $cache_modes['memcached_php'] = esc_html__('Memcached with PHP', 'swift_performance');
    }

    /**
     * Validate Purchase Key via API
     * @param array $field
     * @param mixed $value
     * @param mixed $existing_value
     * @return array
     */
    function swift_performance_validate_purchase_key_callback($field, $value, $existing_value) {
      $error            = true;
      $field['msg']     = esc_html__('API connection error, please try later', 'swift_performance');
      $return['value']  = '';

      $validate = wp_remote_get(SWIFT_PERFORMANCE_API_URL . 'validate?purchase_key=' . $value . '&site=' . urlencode(home_url()));

            if (!is_wp_error($validate)){
                  if ($validate['response']['code'] == 200){
                        $return['value'] = $value;
                        $error = false;
                  }
                  else {
                        $field['msg'] = esc_html__('Purchase Key is invalid', 'swift_performance');
                  }
            }

            if ($error == true) {
                $return['error']  = $field;
            }
            return $return;
      }

      /**
       * Check is the cache path exists and writable
       * @param array $field
       * @param mixed $value
       * @param mixed $existing_value
       * @return array
       */
      function swift_performance_validate_cache_path_callback($field, $value, $existing_value) {
            $return['value']  = $value;
            $error = false;

            if (!file_exists($value)){
                  @mkdir($value, 0777, true);
                  if (!file_exists($value)){
                        $error = true;
                        $field['msg'] = esc_html__('Cache directory doesn\'t exists', 'swift_performance');
                  }
            }
            else if (!is_dir($value)){
                  $error = true;
                  $field['msg'] = esc_html__('Cache directory should be a directory', 'swift_performance');
            }
            else if (!is_writable($value)){
                  $error = true;
                  $field['msg'] = esc_html__('Cache directory isn\'t writable for WordPress. Please change the permissions.', 'swift_performance');
            }

            if ($error == true) {
                  $return['value']  = $existing_value;
                  $return['error']  = $field;
            }
            return $return;
    }

    /**
     * Check is htaccess writable
     * @param array $field
     * @param mixed $value
     * @param mixed $existing_value
     * @return array
     */
    function swift_performance_validate_cache_mode_callback($field, $value, $existing_value) {
          $return['value']  = $value;
          $error = false;

          // Check htaccess only for Apache
          if (Swift_Performance::server_software() != 'apache'){
                return $return;
          }

          $htaccess = ABSPATH . '.htaccess';

          if (!file_exists($htaccess)){
                @touch($htaccess);
                if (!file_exists($htaccess)){
                      $error = true;
                      $field['msg'] = esc_html__('htaccess doesn\'t exists', 'swift_performance');
                }
          }
          else if (!is_writable($htaccess)){
                $error = true;
                $field['msg'] = esc_html__('htaccess isn\'t writable for WordPress. Please change the permissions.', 'swift_performance');
          }

          if ($error == true) {
                $return['value']  = $existing_value;
                $return['error']  = $field;
          }
          return $return;
 }


    $opt_name = "swift_performance_options";

    $args = array(
        'opt_name'             => $opt_name,
        'display_name'         => esc_html__('Settings', 'swift_performance'),
        'display_version'      => false,
        'menu_type'            => 'submenu',
        'allow_sub_menu'       => true,
        'menu_title'           => __( 'Swift Performance', 'swift_performance' ),
        'page_title'           => __( 'Swift Performance', 'swift_performance' ),
        'google_api_key'       => '',
        'google_update_weekly' => false,
        'async_typography'     => true,
        'admin_bar'            => false,
        'admin_bar_icon'       => 'dashicons-dashboard',
        'admin_bar_priority'   => 50,
        'global_variable'      => '',
        'dev_mode'             => false,
        'update_notice'        => false,
        'customizer'           => true,
        'page_priority'        => 2,
        'page_parent'          => 'tools.php',
        'page_permissions'     => 'manage_options',
        'menu_icon'            => '',
        'last_tab'             => '',
        'page_icon'            => 'icon-dashboard',
        'page_slug'            => 'swift-performance',
        'save_defaults'        => true,
        'default_show'         => false,
        'default_mark'         => '',
        'show_import_export'   => true,
        'transient_time'       => 60 * MINUTE_IN_SECONDS,
        'output'               => false,
        'output_tag'           => false,
        'database'             => '',
        'use_cdn'              => true,
        'footer_credit'        => ' ',
        'hints'                => array(
              'icon'          => 'el el-question-sign',
              'icon_position' => 'right',
              'icon_color'    => 'lightgray',
              'icon_size'     => 'normal',
              'tip_style'     => array(
                  'color'   => 'light',
                  'shadow'  => true,
                  'rounded' => false,
                  'style'   => '',
              ),
              'tip_position'  => array(
                  'my' => 'top left',
                  'at' => 'bottom right',
              ),
              'tip_effect'    => array(
                  'show' => array(
                      'effect'   => 'slide',
                      'duration' => '500',
                      'event'    => 'mouseover',
                  ),
                  'hide' => array(
                      'effect'   => 'slide',
                      'duration' => '500',
                      'event'    => 'click mouseleave',
                  ),
            ),
        )
      );

    ReduxSA::setArgs( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     *
     * ---> START SECTIONS
     *
     */

     ReduxSA::setSection ( $opt_name, array (
                 'title' => esc_html__( 'General', 'swift_performance' ),
                 'id' => 'general-tab',
                 'fields' => array (
                       array(
                           'id'         => 'purchase-key',
                           'type'       => 'text',
                           'title'      => esc_html__( 'Envato Purchase Key', 'swift_performance' ),
                           'validate_callback' => 'swift_performance_validate_purchase_key_callback',
                           'default'    => ''
                       ),
                        array(
                             'id'	=> 'normalize-static-resources',
                             'type'	=> 'checkbox',
                             'title'	=> esc_html__( 'Normalize Static Resources', 'swift_performance' ),
                             'subtitle' => esc_html__('Remove unnecessary query string from CSS, JS and image files.', 'swift_performance'),
                             'default' => 1
                        ),
                        array(
                             'id'	=> 'whitelabel',
                             'type'	=> 'checkbox',
                             'title' => esc_html__( 'Whitelabel', 'swift_performance' ),
                             'subtitle' => esc_html__('Prevent to add Swift Performance response header and HTML comment', 'swift_performance'),
                             'default' => 0
                        ),
                  )
            )
      );

      ReduxSA::setSection ( $opt_name, array (
                  'title' => esc_html__( 'Images', 'swift_performance' ),
                  'id' => 'general-images',
                  'icon' => 'el el-picture',
                  'fields' => array (
                         array(
                              'id'         => 'optimize-uploaded-images',
                              'type'       => 'checkbox',
                              'title'      => esc_html__( 'Optimize Images', 'swift_performance' ),
                              'subtitle'   => sprintf(esc_html__('Enable if you would like to optimize the images during the upload using the our Image Optimization API service. Already uploaded images can be optimized %shere%s', 'swift_performance'), '<a href="'.esc_url(add_query_arg('page', 'swift-performance-optimize-images', admin_url('upload.php'))).'" target="_blank">', '</a>'),
                              'default'    => 0,
                              'required'   => array('purchase-key', '!=', '')

                         ),
                         array(
                             'id'         => 'base64-small-images',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Inline Small Images', 'swift_performance' ),
                             'subtitle'   => esc_html__('Use base64 encoded inline images for small images', 'swift_performance'),
                             'default'    => 0,
                         ),
                         array(
                             'id'         => 'base64-small-images-size',
                             'type'       => 'text',
                             'title'      => esc_html__( 'File Size Limit (bytes)', 'swift_performance' ),
                             'subtitle'   => esc_html__('File size limit for inline images', 'swift_performance'),
                             'default'    => '1000',
                             'required'   => array('base64-small-images', '=', 1),
                         ),
                         array(
                              'id'         => 'exclude-base64-small-images',
                              'type'       => 'multi_text',
                              'title'	=> esc_html__('Exclude Images', 'swift_performance'),
                              'subtitle'   => esc_html__('Exclude images from being embedded if one of these strings is found in the match.', 'swift_performance'),
                              'required'   => array('base64-small-images', '=', 1),
                         ),
                         array(
                              'id'         => 'lazy-load-images',
                              'type'       => 'checkbox',
                              'title'      => esc_html__( 'Lazy Load', 'swift_performance' ),
                              'subtitle'   => esc_html__('Enable if you would like lazy load for images.', 'swift_performance'),
                              'default'    => 1
                         ),
                         array(
                              'id'         => 'load-images-on-user-interaction',
                              'type'       => 'checkbox',
                              'title'      => esc_html__( 'Load Images on User Interaction', 'swift_performance' ),
                              'subtitle'   => esc_html__('Enable if you would like to load full images only on user interaction (mouse move, sroll, device motion)', 'swift_performance'),
                              'default'    => 0,
                              'required'   => array('lazy-load-images', '=', 1),
                         ),
                         array(
                             'id'         => 'base64-lazy-load-images',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Inline Lazy Load Images', 'swift_performance' ),
                             'subtitle'   => esc_html__('Use base64 encoded inline images for lazy load', 'swift_performance'),
                             'default'    => 1,
                             'required'   => array('lazy-load-images', '=', 1),
                         ),
                         array(
                             'id'         => 'force-responsive-images',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Force Responsive Images', 'swift_performance' ),
                             'subtitle'   => esc_html__('Force all images to use srcset attribute if it is possible', 'swift_performance'),
                             'default'    => 0,
                         ),
                   )
             )
       );

      ReduxSA::setSection ( $opt_name, array (
                 'title' => esc_html__( 'Asset Manager', 'swift_performance' ),
                 'id' => 'asset-manager-tab',
                 'icon' => 'el el-list-alt',
                 'fields' => array (
                        array(
                             'id'         => 'merge-scripts',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Merge Scripts', 'swift_performance' ),
                             'subtitle'   => esc_html__('Merge javascript files to reduce number of HTML requests ', 'swift_performance'),
                             'default'    => 0,
                             'ajax_save' => false
                        ),
                        array(
                             'id'         => 'merge-scripts-exlude-3rd-party',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Exclude 3rd scripts', 'swift_performance' ),
                             'subtitle'   => esc_html__('Exclude 3rd party scripts from merged scripts', 'swift_performance'),
                             'required'   => array('merge-scripts', '=', 1),
                             'default'    => 0,
                        ),
                        array(
                             'id'         => 'exclude-scripts',
                             'type'       => 'multi_text',
                             'title'	=> esc_html__('Exclude Scripts', 'swift_performance'),
                             'subtitle'   => esc_html__('Exclude scripts from being merged if one of these strings is found in the match.', 'swift_performance'),
                             'required'   => array('merge-scripts', '=', 1),
                        ),
                        array(
                             'id'         => 'exclude-script-localizations',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__('Exclude Script Localizations', 'swift_performance'),
                             'subtitle'   => esc_html__('Exclude javascript localizations from merged scirpts.', 'swift_performance'),
                             'default'    => 1,
                             'required'   => array('merge-scripts', '=', 1),
                        ),
                        array(
                             'id'         => 'minify-scripts',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Minify Javascripts', 'swift_performance' ),
                             'default'    => 1,
                             'required'   => array('merge-scripts', '=', 1),
                        ),
                        array(
                             'id'         => 'proxy-3rd-party-assets',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Proxy 3rd Party Assets', 'swift_performance' ),
                             'subtitle'	=> esc_html__( 'Proxy 3rd party javascript and CSS files which created by javascript (eg: Google Analytics)', 'swift_performance' ),
                             'default'    => 0,
                             'required'   => array('merge-scripts', '=', 1),
                        ),
                        array(
                             'id'         => 'exclude-3rd-party-assets',
                             'type'       => 'multi_text',
                             'title'	=> esc_html__( 'Exclude 3rd Party Assets', 'swift_performance' ),
                             'subtitle'   => esc_html__('Exclude scripts from being proxied if one of these strings is found in the match.', 'swift_performance'),
                             'required'   => array('merge-scripts', '=', 1),
                        ),
                        array(
                             'id'         => 'merge-styles',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Merge Styles', 'swift_performance' ),
                             'subtitle'   => esc_html__('Merge CSS files to reduce number of HTML requests', 'swift_performance'),
                             'default'    => 0,
                             'ajax_save' => false
                        ),
                        array(
                             'id'         => 'inline_critical_css',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__('Print critical CSS inline', 'swift_performance'),
                             'subtitle'   => esc_html__('Enable if you would like to print the critical CSS into the header, instead of a seperated CSS file.', 'swift_performance'),
                             'required'   => array('merge-styles', '=', 1),
                             'default'    => 1,
                        ),
                        array(
                             'id'         => 'inline_full_css',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__('Print full CSS inline', 'swift_performance'),
                             'subtitle'   => esc_html__('Enable if you would like to print the merged CSS into the footer, instead of a seperated CSS file.', 'swift_performance'),
                             'required'   => array('merge-styles', '=', 1),
                             'default'    => 1,
                        ),
                        array(
                             'id'         => 'minify-css',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Minify CSS', 'swift_performance' ),
                             'default'    => 1,
                             'required'   => array('merge-styles', '=', 1),
                        ),
                        array(
                             'id'         => 'compress-css',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Compress CSS', 'swift_performance' ),
                             'subtitle'	=> esc_html__( 'Extra compress for critical CSS (BETA)', 'swift_performance' ),
                             'default'    => 0,
                             'required'   => array(
                                   array('merge-styles', '=', 1),
                                   array('merge-scripts', '=', 1),
                             ),
                        ),
                        array(
                             'id'         => 'remove-keyframes',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Remove Keyframes', 'swift_performance' ),
                             'subtitle'	=> esc_html__( 'Remove CSS animations from critical CSS', 'swift_performance' ),
                             'default'    => 1,
                             'required'   => array('merge-styles', '=', 1),
                        ),
                        array(
                             'id'         => 'merge-styles-exclude-3rd-party',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__('Exclude 3rd Party CSS', 'swift_performance'),
                             'subtitle'   => esc_html__('Exclude 3rd party CSS files (eg: Google Fonts CSS) from merged styles', 'swift_performance'),
                             'required'   => array('merge-styles', '=', 1),
                             'default'    => 0,
                        ),
                        array(
                             'id'         => 'exclude-styles',
                             'type'       => 'multi_text',
                             'title'	=> esc_html__( 'Exclude Styles', 'swift_performance' ),
                             'subtitle'   => esc_html__('Exclude style from being merged if one of these strings is found in the match. ', 'swift_performance'),
                             'required'   => array('merge-styles', '=', 1),
                        ),
                        array(
                             'id'         => 'merge-assets-logged-in-users',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Merge Assets for Logged in Users', 'swift_performance' ),
                             'subtitle'   => esc_html__('Enable if you would like to merge styles and scripts for logged in users as well.', 'swift_performance'),
                             'default'    => 0
                        ),
                        array(
                             'id'         => 'merge-background-only',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Merge Assets in Background', 'swift_performance' ),
                             'subtitle'   => esc_html__('In some cases the generating the critical CSS takes some time. If you enable this option the plugin will generate it in the background.', 'swift_performance'),
                             'default'    => 0,
                             'required'   => array('enable-caching', '=', 1)
                        ),
                        array(
                             'id'         => 'minify-html',
                             'type'       => 'checkbox',
                             'title'	=> esc_html__( 'Minify HTML', 'swift_performance' ),
                             'default'    => 0,
                        ),
                        array(
                             'id'         => 'use-compute-api',
                             'type'       => 'checkbox',
                             'title'      => esc_html__( 'Use Compute API', 'swift_performance' ),
                             'subtitle'   => esc_html__('Speed up merging process and decrease CPU usage.', 'swift_performance'),
                             'default'    => 0,
                             'required'   => array('purchase-key', '!=', '')
                        ),
                  )
            )
      );

      ReduxSA::setSection ( $opt_name, array (
                 'title' => esc_html__( 'Caching', 'swift_performance' ),
                 'id' => 'chache-tab',
                 'icon' => 'el el-graph',
                 'fields' => array (
                       array(
                             'id'         => 'enable-caching',
                             'type'	      => 'checkbox',
                             'title'      => esc_html__( 'Enable Caching', 'swift_performance' ),
                             'default'    => 1,
                             'ajax_save' => false
                       ),
                       array(
                             'id'                => 'caching-mode',
                             'type'              => 'select',
                             'title'	       => esc_html__( 'Caching Mode', 'swift_performance' ),
                             'options'           => $cache_modes,
                             'default'           => 'disk_cache_php',
                             'required'          => array('enable-caching', '=', 1),
                             'validate_callback' => 'swift_performance_validate_cache_mode_callback'
                       ),
                       array(
                              'id'	      => 'cache-path',
                              'type'	=> 'text',
                              'title'	=> esc_html__( 'Cache Path', 'swift_performance' ),
                              'default'   => WP_CONTENT_DIR . '/cache/',
                              'required'  => array(
                                    array('caching-mode', 'contains', 'disk_cache'),
                                    array('enable-caching', '=', 1)
                              ),
                              'validate_callback' => 'swift_performance_validate_cache_path_callback',
                       ),
                       array(
                            'id'         => 'cache-expiry-mode',
                            'type'       => 'select',
                            'title'	     => esc_html__( 'Cache Expiry Mode', 'swift_performance' ),
                            'required'   => array('enable-caching', '=', 1),
                            'options'    => array(
                                  'timebased'   => esc_html__('Time based mode', 'swift_performance'),
                                  'intelligent' => esc_html__('Intelligent mode (Recommended)', 'swift_performance'),
                            ),
                            'default'    => 'timebased'
                       ),
                       array(
                              'id'	      => 'cache-expiry-time',
                              'type'	=> 'text',
                              'title'	=> esc_html__( 'Cache Expiry Time', 'swift_performance' ),
                              'subtitle'  => esc_html__( 'Cache expiry time in seconds', 'swift_performance' ),
                              'default'   => '1800',
                              'required'  => array('cache-expiry-mode', '=', 'timebased')
                       ),
                       array(
                              'id'	      => 'cache-garbage-collection-time',
                              'type'	=> 'text',
                              'title'	=> esc_html__( 'Garbage Collection Interval', 'swift_performance' ),
                              'subtitle'  => esc_html__( 'How often should check the expired cached pages', 'swift_performance' ),
                              'default'   => '300',
                              'required'  => array('cache-expiry-mode', '=', 'timebased')
                       ),
                       array(
                           'id'          => 'automated_prebuild_cache',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Prebuild Cache Automatically', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'This option will prebuild the cache after it was cleared', 'swift_performance' ),
                           'default'     => 0,
                           'required'  => array('cache-expiry-mode', '=', 'timebased')
                       ),
                       array(
                           'id'          => 'resource-saving-mode',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Resource saving mode', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'This option will reduce intelligent cache check requests. Recommended for limited resource severs', 'swift_performance' ),
                           'default'     => 1,
                           'required'  => array('cache-expiry-mode', '=', 'intelligent')
                       ),
                       array(
                           'id'          => 'disable-instant-reload',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Disable Instant Reload', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'If you disable instant reload the plugin will override the cache if intelligent cache detect changes, however it won\'t replace the page content instantly for the user.', 'swift_performance' ),
                           'default'     => 1,
                           'required'  => array('cache-expiry-mode', '=', 'intelligent')
                       ),
                       array(
                           'id'          => 'enable-caching-logged-in-users',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Enable Caching for logged in users', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'This option can increase the total cache size, depending on the count of your users.', 'swift_performance' ),
                           'default'     => 0,
                           'required'  => array('cache-expiry-mode', '=', 'intelligent')
                       ),
                       array(
                           'id'          => 'mobile-support',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Enable Mobile Device Support', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'You can create separate cache for mobile devices, it can be useful if your site not just responsive, but it has a separate mobile theme/layout (eg: AMP). ', 'swift_performance' ),
                           'default'     => 0,
                           'required'    => array('enable-caching', '=', 1),
                       ),
                       array(
                           'id'          => 'browser-cache',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Enable Browser Cache', 'swift_performance' ),
                           'subtitle'    => esc_html__( ' If you enable this option it will generate htacess/nginx rules for browser cache. (Expire headers should be configured on your server as well)', 'swift_performance' ),
                           'default'     => 1,
                           'required'   => array('enable-caching', '=', 1),
                           'ajax_save' => false
                       ),
                       array(
                           'id'          => 'enable-gzip',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Enable Gzip', 'swift_performance' ),
                           'subtitle'    => esc_html__( ' If you enable this option it will generate htacess/nginx rules for gzip compression. (Compression should be configured on your server as well)', 'swift_performance' ),
                           'default'     => 1,
                           'required'   => array('enable-caching', '=', 1),
                           'ajax_save' => false
                       ),
                       array(
                           'id'          => '304-header',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Send 304 Header', 'swift_performance' ),
                           'default'     => 0,
                           'required'   => array('enable-caching', '=', 1),
                       ),
                       array(
                           'id'          => 'cache-404',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Cache 404 pages', 'swift_performance' ),
                           'default'     => 1,
                           'required'   => array('enable-caching', '=', 1),
                       ),
                       array(
                           'id'         => 'exclude-pages',
                           'type'       => 'select',
                           'multi'      => true,
                           'title'      => esc_html__( 'Exclude Pages', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'Select pages which shouldn\'t be cached.', 'swift_performance' ),
                           'required'   => array('enable-caching', '=', 1),
                           'options'    => $_pages
                       ),
                       array(
                           'id'         => 'exclude-strings',
                           'type'       => 'multi_text',
                           'title'      => esc_html__( 'Exclude Strings', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'URLs which contains that string won\'t be cached. Use leading/trailing # for regex', 'swift_performance' ),
                           'required'   => array('enable-caching', '=', 1),
                           'default'    => 0,
                       ),
                       array(
                           'id'         => 'exclude-useragents',
                           'type'       => 'multi_text',
                           'title'      => esc_html__( 'Exclude User Agents', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'User agents which contains that string won\'t be cached. Use leading/trailing # for regex', 'swift_performance' ),
                           'required'   => array('enable-caching', '=', 1),
                           'default'    => 0,
                       ),
                       array(
                           'id'          => 'exclude-crawlers',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Exclude Crawlers', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'Exclude known crawlers from cache', 'swift_performance' ),
                           'default'     => 0,
                           'required'   => array('enable-caching', '=', 1),
                       ),
                       array(
                           'id'          => 'dynamic-caching',
                           'type'        => 'checkbox',
                           'title'       => esc_html__( 'Enable Dynamic Caching', 'swift_performance' ),
                           'subtitle'    => esc_html__( 'If you enable this option you can specify cacheable $_GET and $_POST requests', 'swift_performance' ),
                           'default'     => 0,
                           'required'   => array('enable-caching', '=', 1),
                       ),
                       array(
                           'id'         => 'cacheable-dynamic-requests',
                           'type'       => 'multi_text',
                           'title'      => esc_html__( 'Cacheable Dynamic Requests', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'Specify $_GET and/or $_POST keys what should be cached. Eg: "s" to cache search requests', 'swift_performance' ),
                           'default'    => 0,
                           'required'   => array('dynamic-caching', '=', 1)
                       ),
                       array(
                           'id'         => 'cacheable-ajax-actions',
                           'type'       => 'multi_text',
                           'title'      => esc_html__( 'Cacheable AJAX Actions', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'With this option you can cache resource-intensive AJAX requests', 'swift_performance' ),
                           'default'    => 0,
                           'required'   => array('enable-caching', '=', 1)
                       ),
                       array(
                           'id'         => 'ajax-cache-expiry-time',
                           'type'	    => 'text',
                           'title'	    => esc_html__( 'AJAX Cache Expiry Time', 'swift_performance' ),
                           'subtitle'   => esc_html__( 'Cache expiry time for AJAX requests in seconds', 'swift_performance' ),
                           'default'    => '1440',
                           'required'  => array(
                                 array('cache-expiry-mode', '=', 'timebased')
                           )
                       ),
                  )
            )
      );

     ReduxSA::setSection ( $opt_name, array (
                 'title' => esc_html__( 'CDN', 'swift_performance' ),
                 'desc' => __('Speed up your website with', 'swift_performance').' <a href="//tracking.maxcdn.com/c/258716/3968/378" target="_blank">MaxCDN</a>',
                 'id' => 'cdn-tab',
                 'icon' => 'el el-tasks',
                 'fields' => array (
                       array(
                                   'id'	=> 'enable-cdn',
                                   'type'	=> 'checkbox',
                                   'title' => esc_html__( 'Enable CDN', 'swift_performance' ),
                                   'default' => 0
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-master',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'CDN Hostname', 'swift_performance' ),
                                   'required' => array('enable-cdn', '=', 1)
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-slot-1',
                                   'type'	=> 'text',
                                   'title' => esc_html__( 'CDN Hostname for Javascript ', 'swift_performance' ),
                                   'required' => array('cdn-hostname-master', '!=', ''),
                                   'subtitle' => esc_html__('Use different hostname for javascript files', 'swift_performance'),
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-slot-2',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'CDN Hostname for Media files', 'swift_performance' ),
                                   'required' => array('cdn-hostname-slot-1', '!=', ''),
                                   'subtitle' => esc_html__('Use different hostname for media files', 'swift_performance'),
                       ),
                       array(
                                   'id'	=> 'enable-cdn-ssl',
                                   'type'	=> 'checkbox',
                                   'title'	=> esc_html__( 'Enable CDN on SSL', 'swift_performance' ),
                                   'default' => 0,
                                   'subtitle' => esc_html__('You can specify different hostname(s) for SSL, or leave them blank for use the same host on HTTP and SSL', 'swift_performance'),
                                   'required' => array('enable-cdn', '=', 1)
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-master-ssl',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'SSL CDN Hostname', 'swift_performance' ),
                                   'required' => array('enable-cdn-ssl', '=', 1)
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-slot-1-ssl',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'CDN Hostname for Javascript ', 'swift_performance' ),
                                   'required' => array('cdn-hostname-master-ssl', '!=', ''),
                                   'subtitle' => esc_html__('Use different hostname for javascript files', 'swift_performance'),
                       ),
                       array(
                                   'id'	=> 'cdn-hostname-slot-2-ssl',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'CDN Hostname for Media files', 'swift_performance' ),
                                   'required' => array('cdn-hostname-slot-1-ssl', '!=', ''),
                                   'subtitle' => esc_html__('Use different hostname for media files', 'swift_performance'),
                       ),
                       array(
                                   'id'	=> 'maxcdn-alias',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'MAXCDN Alias', 'swift_performance' ),
                                   'required' => array('enable-cdn', '=', 1),
                       ),
                       array(
                                   'id'	=> 'maxcdn-key',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'MAXCDN Consumer Key', 'swift_performance' ),
                                   'required' => array('enable-cdn', '=', 1),
                       ),
                       array(
                                   'id'	=> 'maxcdn-secret',
                                   'type'	=> 'text',
                                   'title'	=> esc_html__( 'MAXCDN Consumer Secret', 'swift_performance' ),
                                   'required' => array('enable-cdn', '=', 1),
                       ),
                  )
            )
      );

    /*
     *
     * ---> END SECTIONS
     *
     */

     add_action( 'admin_menu', 'remove_reduxsa_menu',12 );
     function remove_reduxsa_menu() {
         remove_submenu_page('tools.php','reduxsa-about');
     }

?>
