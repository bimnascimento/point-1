<?php
/**
 * Core Functions
 */
if (!function_exists('itf_wp_plugin_extension_core')) :

	/**
	 * @return \iTechFlare\WP\Plugin\iTechFlareCore
	 */
	function itf_wp_plugin_extension_core()
	{
		static $itf_wp;
		if (!$itf_wp) {
			/**
			 * \iTechFlare\WP\Plugin\iTechFlareCore
			 */
			$itf_wp = \iTechFlare\WP\Plugin\iTechFlareCore::getInstance();
			// doing init
			do_action('itf_admin_load_extension', $itf_wp);
		}
		return $itf_wp;
	}
endif;

/**
 * Plugins Loaded Load
 * Load The Core
 */
add_action('plugins_loaded', 'itf_wp_plugin_extension_core');
