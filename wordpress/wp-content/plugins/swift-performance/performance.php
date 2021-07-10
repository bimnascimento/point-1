<?php

/**
 * Plugin Name: Swift Performance
 * Plugin URI: http://swift-performance.swte.ch
 * Description: Boost your WordPress site
 * Version: 1.1
 * Author: SWTE
 * Author URI: http://swte.ch
 */

class Swift_Performance {

	/**
	 * Loaded modules
	 */
	public $modules = array();

	/**
	 * Create instance
	 */
	public function __construct() {
		// Clean htaccess and scheduled events on deactivate
		register_deactivation_hook( __FILE__, array('Swift_Performance', 'deactivate'));

		// Regenerate htaccess on activation
		register_activation_hook( __FILE__, array('Swift_Performance', 'activate'));

		// Set constants
		if (!defined('SWIFT_PERFORMANCE_URI')){
			define('SWIFT_PERFORMANCE_URI', trailingslashit(plugins_url() . '/'. basename(__DIR__)));
		}

		if (!defined('SWIFT_PERFORMANCE_DIR')){
			define('SWIFT_PERFORMANCE_DIR', trailingslashit(__DIR__));
		}

		if (!defined('SWIFT_PERFORMANCE_VER')){
			define('SWIFT_PERFORMANCE_VER', '1.1');
		}

		if (!defined('SWIFT_PERFORMANCE_API_URL')){
			define('SWIFT_PERFORMANCE_API_URL', 'http://api.swteplugins.com/sp_v2/');
		}

		// Include framework
		include_once 'includes/framework/framework.php';
		include_once 'includes/framework/framework-config.php';

		// Init Swift Performance
		$this->init();

		// Load assets on backend
		add_action('admin_enqueue_scripts', array($this, 'load_assets'));

		// Ajax handlers
		add_action('wp_ajax_swift_performance_clear_cache', array($this, 'ajax_clear_all_cache'));
		add_action('wp_ajax_swift_performance_clear_assets_cache', array($this, 'ajax_clear_assets_cache'));
		add_action('wp_ajax_swift_performance_prebuild_cache', array($this, 'ajax_prebuild_cache'));
		add_action('wp_ajax_swift_performance_show_rewrites', array($this, 'ajax_show_rewrites'));

		// Create prebuild cache hook
		add_action( 'swift_performance_prebuild_cache', array('Swift_Performance', 'prebuild_cache'));

		// Clear cache, manage rewrite rules, scheduled jobs after options was saved
		add_action('wp_ajax_swift_performance_options_ajax_save', array('Swift_Performance', 'options_saved'), 0);

		// Add actions to redux header
		add_action('reduxsa/page/swift_performance_options/form/before', function(){
		      require 'includes/framework/settings-header.php';
		});

		// Create cache expiry cron schedule
		add_filter( 'cron_schedules',	function ($schedules){
			// Common cache
			$schedules['swift_performance_cache_expiry'] = array(
				'interval' => max(Swift_Performance::get_option('cache-garbage-collection-time'), 1),
				'display' => __('Swift Performance Cache Expiry')
			);

			// Assets cache
			$schedules['swift_performance_assets_cache_expiry'] = array(
				'interval' => 3600,
				'display' => __('Swift Performance Assets Cache Expiry')
			);

			return $schedules;
		});

		// Admin menus
		add_action('admin_bar_menu', array('Swift_Performance', 'toolbar_items'),100);

		// Clear caches
		add_action('init', function(){
			if (!isset($_GET['swift-performance-action'])){
				return;
			}

			if ($_GET['swift-performance-action'] == 'clear-all-cache' && current_user_can('manage_options') && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'clear-swift-cache')){
				Swift_Performance_Cache::clear_all_cache();
				self::add_notice(esc_html__('All cache cleared', 'swift_performance'), 'success');
			}

			if ($_GET['swift-performance-action'] == 'clear-assets-cache' && current_user_can('manage_options') && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'clear-swift-assets-cache')){
				Swift_Performance_Asset_Manager::clear_assets_cache();
				self::add_notice(esc_html__('Assets cache cleared', 'swift_performance'), 'success');
			}

			if ($_GET['swift-performance-action'] == 'purge-cdn' && current_user_can('manage_options') && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'purge-swift-cdn')){
				if (self::check_option('enable-caching', 1)){
					Swift_Performance_Cache::clear_all_cache();
				}
				else if (self::check_option('merge-scripts',1) || self::check_option('merge-styles',1)){
					Swift_Performance_Asset_Manager::clear_assets_cache();
				}

				Swift_Performance_CDN_Manager::purge_cdn();
			}
		});

		// Show runtime Messages
		add_action('admin_notices', array($this, 'admin_notices'));

		// Create clear cache hook for scheduled events
		if (Swift_Performance::check_option('cache-expiry-mode', 'timebased')){
			add_action('swift_performance_clear_cache', array('Swift_Performance_Cache', 'clear_all_cache'));
			add_action('swift_performance_clear_expired', array('Swift_Performance_Cache', 'clear_expired'));
		}

		// Create clear assets cache hook for scheduled events
		add_action('swift_performance_clear_assets_cache', array('Swift_Performance_Asset_Manager', 'clear_assets_cache'));

		// Plugin updater
		if (self::check_option('purchase-key', '', '!=')){
			require 'includes/puc/plugin-update-checker.php';
			$update_checker = Puc_v4_Factory::buildUpdateChecker(
				add_query_arg('purchase_key', self::get_option('purchase-key'), SWIFT_PERFORMANCE_API_URL . 'update/'),
				__FILE__,
				'swift-performance'
			);

			// Add purchase key to download url
			add_filter('puc_request_info_result-swift-performance', function($info){
				$info->download_url = str_replace('[[PARAMETERS]]', '?purchase_key=' . Swift_Performance::get_option('purchase-key') . '&site=' . site_url(), $info->download_url);
				return $info;
			});
		}

		add_filter('plugin_action_links', function ($links, $file) {
			if ($file == plugin_basename(__FILE__)) {
				$settings_link = '<a href="' . menu_page_url('swift-performance', false) . '">'.__('Settings','swift-performance').'</a>';
				array_unshift($links, $settings_link);
			}

			return $links;
		}, 10, 2);
	}

	public function load_assets($hook) {
		if($hook == 'tools_page_swift-performance') {
			wp_enqueue_script( 'swift-performance', SWIFT_PERFORMANCE_URI . 'js/scripts.js', array('jquery'), SWIFT_PERFORMANCE_VER );
			wp_localize_script( 'swift-performance', 'swift_performance', array('nonce' => wp_create_nonce('swift-performance-ajax-nonce')));
			wp_enqueue_style( 'swift-performance', SWIFT_PERFORMANCE_URI . 'css/styles.css', array(), SWIFT_PERFORMANCE_VER );
		}
	}

	/**
	 * Init Swift Performance
	 */
	public function init(){
		if (!defined('SWIFT_PERFORMANCE_CACHE_DIR')){
			define('SWIFT_PERFORMANCE_CACHE_DIR', trailingslashit(self::get_option('cache-path')).'swift-performance/' . parse_url(home_url(), PHP_URL_HOST) . '/');
		}

		if (!defined('SWIFT_PERFORMANCE_CACHE_URL')){
			define('SWIFT_PERFORMANCE_CACHE_URL', str_replace(ABSPATH, self::home_url(), SWIFT_PERFORMANCE_CACHE_DIR));
		}


		// Cache
		$this->modules['cache'] =  require_once 'modules/cache/cache.php';

		// CDN Manager
		if (self::check_option('enable-cdn', 1)){
			$this->modules['cdn-manager'] =  require_once 'modules/cdn/cdn-manager.php';
		}

		// Asset Manager
		$this->modules['asset-manager'] = require_once 'modules/asset-manager/asset-manager.php';


		// Image optimizer
		$this->modules['cdn-manager'] =  require_once 'modules/image-optimizer/image-optimizer.php';
	}

	/**
	 * Print admin notices
	 */
	public function admin_notices(){
		$messages = get_option('swift_performance_messages', array());
		foreach((array)$messages as $message){
			$class = ($message['type'] == 'success' ? 'updated' : ($message['type'] == 'warning' ? 'update-nag' : ($message['type'] == 'error' ? 'error' : 'notice')));
			echo '<div class="'.$class.'" style="padding:25px 10px 10px 10px;position: relative;display: block;"><span style="color:#888;position:absolute;top:5px;left:5px;">'.esc_html__('Swift Performance','swift_performance').'</span>'.$message['message'].'</div>';
		}
		delete_option('swift_performance_messages');
	}

	/**
	 * Clear all cache ajax callback
	 */
	public function ajax_clear_all_cache(){
		// Check user and nonce
		if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
			wp_send_json(
				array(
					'type' => 'critical',
					'text' => __('Your session has expired. Please refresh the page and try again.', 'swift_performance')
				)
			);
		}

		Swift_Performance_Cache::clear_all_cache();
		wp_send_json(
			array(
				'type' => 'success',
				'text' => __('Cache cleared', 'swift_performance')
			)
		);
	}

	/**
	 * Clear assets cache ajax callback
	 */
	public function ajax_clear_assets_cache(){
		// Check user and nonce
		if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
			wp_send_json(
				array(
					'type' => 'critical',
					'text' => __('Your session has expired. Please refresh the page and try again.', 'swift_performance')
				)
			);
		}

		Swift_Performance_Cache::recursive_rmdir('css');
		Swift_Performance_Cache::recursive_rmdir('js');
		wp_send_json(
			array(
				'type' => 'success',
				'text' => __('Assets cache cleared', 'swift_performance')
			)
		);
	}

	/**
	 * Prebuild cache ajax callback
	 */
	public function ajax_prebuild_cache(){
		// Check user and nonce
		if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
			wp_send_json(
				array(
					'type' => 'critical',
					'text' => __('Your session has expired. Please refresh the page and try again.', 'swift_performance')
				)
			);
		}

		wp_schedule_single_event(time(), 'swift_performance_prebuild_cache');
		wp_send_json(
			array(
				'type' => 'info',
				'text' => __('Prebuilding cache is in progress', 'swift_performance')
			)
		);
	}

	public function ajax_show_rewrites(){
		// Check user and nonce
		if (!current_user_can('manage_options') || !isset($_REQUEST['_wpnonce']) || !wp_verify_nonce($_REQUEST['_wpnonce'], 'swift-performance-ajax-nonce')){
			wp_send_json(
				array(
					'type' => 'critical',
					'text' => __('Your session has expired. Please refresh the page and try again.', 'swift_performance')
				)
			);
		}

		switch (self::server_software()){
			case 'apache':
				$htaccess = trailingslashit(str_replace(site_url(), ABSPATH, home_url())) . '.htaccess';

		            if (file_exists($htaccess) && is_writable($htaccess)){
		               	$message = __('It seems that your htaccess is writable, you don\'t need to add rules manually.', 'swift_performance');
		            }
				else {
					$message = __('It seems that your htaccess is NOT writable, you need to add rules manually.', 'swift_performance');
				}
				break;
			case 'nginx':
				$message = __('You need to add rewrite rules manually to your Nginx config file.', 'swift_performance');
				break;
			default:
				$message = __('Caching with rewrites currently available on Apache and Nginx only.', 'swift_performance');


		}

		wp_send_json(
			array(
				'type' => 'info',
				'text' => $message,
				'rewrites' => get_option('swift_performance_rewrites'),
			)
		);
	}

	/**
	 * Prebuild cache callback
	 */
	public static function prebuild_cache(){
		$current_process = mt_rand(0,PHP_INT_MAX);
		set_transient('swift_performance_prebuild_cache_pid', $current_process, 600);
		set_time_limit(600);
		global $wpdb;
		$posts = $wpdb->get_results("SELECT option_value as ID FROM {$wpdb->options} WHERE option_name = 'page_on_front' UNION SELECT meta_value as ID FROM {$wpdb->postmeta} WHERE meta_key = '_menu_item_object_id' UNION SELECT ID FROM {$wpdb->posts} WHERE post_status = 'publish'", ARRAY_A);
		foreach ($posts as $post){
			$prebuild_process = get_transient('swift_performance_prebuild_cache_pid');
			if ($prebuild_process !== false || $prebuild_process != $current_process){
				break;
			}
			$permalink = get_permalink($post['ID']);

			$device_prefix = (self::check_option('mobile_support', 1) && is_mobile() ? 'mobile' : 'desktop');

			$cache_path = parse_url(home_url(), PHP_URL_HOST) . '/' . $device_prefix . '/unauthenticated/' . parse_url($permalink, PHP_URL_PATH) . '/';

			if (file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $cache_path . 'index.html')){
				continue;
			}
			wp_remote_get($permalink, array('X_MERGE_ASSETS' => 1));
		}
	}

	/**
	 * Add toolbar options
	 * @param WP_Admin_Bar $admin_bar
	 */
	public static function toolbar_items($admin_bar){
		if (current_user_can('manage_options')){
			$current_page = site_url(str_replace(site_url(), '', 'http'.(isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));

			$admin_bar->add_menu(array(
				'id'    => 'swift-performance',
				'title' => esc_html__('Swift Performance', 'swift_performance'),
				'href'  => esc_url(admin_url('tools.php?page=swift-performance',false)),
			 ));

			if(Swift_Performance::check_option('enable-caching', 1)){
				$admin_bar->add_menu(array(
					'id'    => 'clear-swift-cache',
					'parent' => 'swift-performance',
					'title' => esc_html__('Clear Cache', 'swift_performance'),
					'href'  => esc_url(wp_nonce_url(add_query_arg('swift-performance-action', 'clear-all-cache', $current_page), 'clear-swift-cache')),
				 ));
			}
			if(Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1)){
				$admin_bar->add_menu(array(
					'id'    => 'clear-swift-assets-cache',
					'parent' => 'swift-performance',
					'title' => esc_html__('Clear Assets Cache', 'swift_performance'),
					'href'  => esc_url(wp_nonce_url(add_query_arg('swift-performance-action', 'clear-assets-cache', $current_page), 'clear-swift-assets-cache')),
				 ));
			}

			if (Swift_Performance::check_option('enable-cdn',1) && Swift_Performance::check_option('maxcdn-key','','!=') && Swift_Performance::check_option('maxcdn-secret','','!=')){
				$admin_bar->add_menu(array(
					'id'    => 'purge-swift-cdn',
					'parent' => 'swift-performance',
					'title' => esc_html__('Purge CDN (All zones)', 'swift_performance'),
					'href'  => esc_url(wp_nonce_url(add_query_arg('swift-performance-action', 'purge-cdn', $current_page), 'purge-swift-cdn')),
				));
			}

		}
	}

	/**
	 * Clean htaccess and scheduled hooks on deactivation
	 */
	public static function deactivate(){
		wp_clear_scheduled_hook('swift_performance_clear_cache');
		wp_clear_scheduled_hook('swift_performance_clear_assets_cache');
		self::write_rewrite_rules();
	}

	/**
	 * Generate htaccess and scheduled hooks on activation
	 */
	public static function activate(){
		// Schedule clear cache if cache mode is timebased
		if (self::check_option('enable-caching', 1) && self::check_option('cache-expiry-mode', 'timebased')){
			if (!wp_next_scheduled( 'swift_performance_clear_expired')) {
				wp_schedule_event(time() + self::get_option('cache-expiry-time'), 'swift_performance_cache_expiry', 'swift_performance_clear_expired');
			}
		}

		// Schedule clear assets cache if proxy is enabled
		if (self::check_option('merge-scripts', 1) && self::check_option('proxy-3rd-party-assets', 1)){
			if (!wp_next_scheduled( 'swift_performance_clear_assets_cache')) {
				wp_schedule_event(time(), 'swift_performance_assets_cache_expiry', 'swift_performance_clear_assets_cache');
			}
		}

		// Build rewrite rules
		$rules = self::build_rewrite_rules();
		self::write_rewrite_rules($rules);
	}

	/**
	 * Write rewrite rules, clear scheduled hooks, set schedule (if necessary), clear cache on save
	 */
	public static function options_saved(){
		// Build rewrite rules
		$rules = self::build_rewrite_rules();
		self::write_rewrite_rules($rules);

		// Clear previously scheduled hooks
		wp_clear_scheduled_hook('swift_performance_clear_cache');
		wp_clear_scheduled_hook('swift_performance_clear_assets_cache');

		// Clear cache
		add_action('shutdown', function(){
			Swift_Performance_Cache::clear_all_cache();
		},PHP_INT_MAX);

		// Schedule clear cache if cache mode is timebased
		if (self::check_option('enable-caching', 1) && self::check_option('cache-expiry-mode', 'timebased')){
			if (!wp_next_scheduled( 'swift_performance_clear_expired')) {
    				wp_schedule_event(time() + self::get_option('cache-expiry-time'), 'swift_performance_cache_expiry', 'swift_performance_clear_expired');
  			}
		}

		// Schedule clear assets cache if proxy is enabled
		if (self::check_option('merge-scripts', 1) && self::check_option('proxy-3rd-party-assets', 1)){
			if (!wp_next_scheduled( 'swift_performance_clear_assets_cache')) {
    				wp_schedule_event(time(), 'swift_performance_assets_cache_expiry', 'swift_performance_clear_assets_cache');
  			}
		}
	}

	/**
	 * Build rewrite rules based on settings and server software
	 */
	public static function build_rewrite_rules(){
		$rules = $errors = array();
		$server_software = self::server_software();
		try{
			if (isset($_REQUEST['data'])){
				parse_str(urldecode($_REQUEST['data']), $data);
			}
			else {
				$data['swift_performance_options'] = get_option('swift_performance_options', array());
			}
			// Browser cache
			if (isset($data['swift_performance_options']['enable-caching']) && $data['swift_performance_options']['enable-caching'] == 1 && isset($data['swift_performance_options']['enable-gzip']) && $data['swift_performance_options']['enable-gzip'] == 1){
				switch($server_software){
					case 'apache':
						$rules['compression'] = apply_filters('swift_performance_browser_gzip', file_get_contents(SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/htaccess-deflate.txt'));
						break;
					case 'nginx':
						$rules['compression'] = apply_filters('swift_performance_browser_gzip', file_get_contents(SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/nginx-deflate.txt'));
						break;
					default:
						throw new Exception(esc_html__('Advanced Cache Control doesn\'t supported on your server', 'swift_performance'));
				}
			}
			if (isset($data['swift_performance_options']['enable-caching']) && $data['swift_performance_options']['enable-caching'] == 1 && isset($data['swift_performance_options']['browser-cache']) && $data['swift_performance_options']['browser-cache'] == 1){
				switch($server_software){
					case 'apache':
						$rules['cache-control'] = apply_filters('swift_performance_browser_cache', file_get_contents(SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/htaccess-browser-cache.txt'));
						break;
					case 'nginx':
						$rules['cache-control'] = apply_filters('swift_performance_browser_cache', file_get_contents(SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/nginx-browser-cache.txt'));
						break;
					default:
						throw new Exception(esc_html__('Advanced Cache Control doesn\'t supported on your server', 'swift_performance'));
				}
			}
			if (isset($data['swift_performance_options']['enable-caching']) && $data['swift_performance_options']['enable-caching'] == 1 && isset($data['swift_performance_options']['caching-mode']) && $data['swift_performance_options']['caching-mode'] == 'disk_cache_rewrite'){
				switch($server_software){
					case 'apache':
						$rules['basic'] = apply_filters('swift_performance_cache_rewrites', include_once SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/htaccess.php');
						break;
					case 'nginx':
						$rules['basic'] = apply_filters('swift_performance_cache_rewrites', include_once SWIFT_PERFORMANCE_DIR . 'modules/cache/rewrites/nginx.php');;
						break;
					default:
						throw new Exception(esc_html__('Rewrite mode isn\'t supported on your server', 'swift_performance'));
				}
			}

			return $rules;
		}
		catch(Exception $e){
			self::add_notice($e->getMessage(), 'error');
		}
	}


	/**
	 * Write rewrite rules if it is possible, otherwise add warning with rules
	 * @param array $rules
	 */
	public static function write_rewrite_rules($rules = array()){
		$multisite_padding = (is_multisite() ? ' - ' . hash('crc32',home_url()) : '');
		$server_software = self::server_software();
		if ($server_software == 'apache' && file_exists(ABSPATH . '.htaccess')){
			if (is_writable(ABSPATH . '.htaccess')){
				$rewrites = '';
				$htaccess = file_get_contents(ABSPATH . '.htaccess');
				$htaccess = preg_replace("~###BEGIN Swift Performance{$multisite_padding}###(.*)###END Swift Performance{$multisite_padding}###\n?~is", '', $htaccess);
				if (!empty($rules)){
					$rewrites = "###BEGIN Swift Performance{$multisite_padding}###\n" . implode("\n", $rules) . "\n###END Swift Performance{$multisite_padding}###\n";
					$htaccess = $rewrites . $htaccess;
				}
				@file_put_contents(ABSPATH . '.htaccess', $htaccess);
				update_option('swift_performance_rewrites', $rewrites, false);
			}
		}
		else if ($server_software == 'nginx'){
			$rewrites = "###BEGIN Swift Performance{$multisite_padding}###\n" . implode("\n", $rules) . "\n###END Swift Performance{$multisite_padding}###\n";
			update_option('swift_performance_rewrites', $rewrites, false);
		}
	}

	/**
	 * Set messages
	 */
	public static function add_notice($message, $type = 'info'){
		$messages = get_option('swift_performance_messages', array());
		$messages[md5($message.$type)] = array('message' => $message, 'type' => $type);
		update_option('swift_performance_messages', $messages);
	}

	/**
	 * Extend is_admin to check if current page is login or register page
	 */
	public static function is_admin() {
    		return (is_admin() || in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' )) || (isset($_GET['vc_editable']) && $_GET['vc_editable'] == 'true') || isset($_GET['customize_theme']) );
	}

	/**
	 * Bypass built in function to be able call it early
	 */
	public static function is_user_logged_in(){
		return (isset($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_COOKIE[LOGGED_IN_COOKIE]));
	}

	/**
	 * Bypass built in function to be able call it early
	 */
	public static function is_404(){
		global $wp_query;
		return (isset( $wp_query ) && !empty($wp_query) ? is_404() : false);
	}

	/**
	 * Bypass built in function to be able call it early
	 */
	public static function is_feed(){
		global $wp_query;
		return (isset( $wp_query ) && !empty($wp_query) ? is_feed() : false);
	}

	/**
	 * Bypass built in function to be able get unfiltered home url
	 * @return string
	 */
	public static function home_url(){
		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if (isset($alloptions['home'])){
			$home_url = $alloptions['home'];
		}
		global $wpdb;
		$home_url = $wpdb->get_var("SELECT option_value FROM {$wpdb->options} WHERE option_name = 'home'");
		return trailingslashit($home_url);
	}

	/**
	 * Check Swift Performance settings
	 * @param string $key
	 * @param mixed $value
	 * @return boolean
	 */
	public static function check_option($key, $value, $condition = '='){
	      global $swift_performance_options;
	      if ($condition == '='){
	            return self::get_option($key) == $value;
	      }
	      else if ($condition == '!='){
	            return self::get_option($key) != $value;
	      }
		else if (strtoupper($condition) == 'IN'){
			return in_array(self::get_option($key), (array)$value);
		}

	}

	/**
	 * Get Swift Performance option
	 * @param string $key
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get_option($key, $default = ''){
	      global $swift_performance_options;
		if (empty($swift_performance_options)){
		    $swift_performance_options = get_option('swift_performance_options', array());
		}
	      if (isset($swift_performance_options[$key])){
	            return apply_filters('swift_performance_option', $swift_performance_options[$key], $key);
	      }
	      else {
	            return apply_filters('swift_performance_option', false, $key);
	      }
	}

	/**
	 * Determine the server software
	 */
	public static function server_software(){
		return (preg_match('~(apache|litespeed|LNAMP)~i', $_SERVER['SERVER_SOFTWARE']) ? 'apache' : (preg_match('~(nginx|flywheel)~i', $_SERVER['SERVER_SOFTWARE']) ? 'nginx' : 'unknown'));
	}

	/**
	 * Use compute API
	 * @param array $args
	 * @return string|boolean false on error, response string on success
	 */
	public static function compute_api($args){
		if (self::check_option('purchase-key', '') || self::check_option('use-compute-api', 1, '!=')){
			return false;
		}
		$response = wp_remote_post (
			SWIFT_PERFORMANCE_API_URL . 'compute/' ,array(
					'timeout' => 300,
					'sslverify' => false,
					'user-agent' => 'SwiftPerformance',
					'headers' => array (
							'X-ENVATO-PURCHASE-KEY' => trim (self::get_option('purchase-key'))
					),
					'body' => array (
							'site' => trailingslashit(home_url()),
							'args' => json_encode($args)
					)
			)
		);

		if (is_wp_error($response)){
			return false;
		}
		else{
			if ($response['response']['code'] != 200){
				return false;
			}
			if (empty($response['body'])){
				return false;
			}
			return $response['body'];
		}
	}

	/**
       * Get image id from url
       * @param string url
       * @return int
       */
      public static function get_image_id($url){
            $upload_dir = wp_upload_dir();
		global $wpdb;

            $image = preg_replace('~-(\d*)x(\d*)\.(jpe?g|gif|png)$~', '.$3', str_replace(trailingslashit(apply_filters('swift_performance_media_host', preg_replace('~https?:~','',$upload_dir['baseurl']))), '', preg_replace('~https?:~','',$url)));
            return $wpdb->get_var("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value = '{$image}'");
      }
}

$GLOBALS['swift_performance'] = new Swift_Performance();

?>
