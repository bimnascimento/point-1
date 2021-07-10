<?php
/*
Plugin Name: Dokan Live Search
Plugin URI: http://wedevs.com/
Description: Live product search for your site
Version: 0.1
Author: WeDevs Team
Author URI: http://wedevs.com/
License: GPL2
*/

/**
 * Copyright (c) 2014 weDevs (email: info@wedevs.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;


include_once 'classes/class-dokan-live-search.php';


/**
 * Dokan_Live_Search class
 *
 * @class Dokan_Live_Search The class that holds the entire Dokan_Live_Search plugin
 */
class Dokan_Live_Search {

    /**
     * Constructor for the Dokan_Live_Search class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );

        // Widget initialization hook
        add_action('widgets_init',array($this,'initialize_widget_register'));

        // Loads frontend scripts and styles
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // removing redirection to single product page
        add_filter( 'woocommerce_redirect_single_search_result', '__return_false' );

    }

    /**
     * Initializes the Dokan_Live_Search() class
     *
     * Checks for an existing Dokan_Live_Search() instance
     * and if it doesn't find one, creates it.
     */

    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Live_Search();
        }

        return $instance;
    }


    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dokan_ls', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style()
     */
    public function enqueue_scripts() {

        /**
         * All styles goes here
         */
        wp_enqueue_style( 'dokan-ls-custom-style', plugins_url( 'css/style.css', __FILE__ ), false, date( 'Ymd' ) );

        /**
         * All scripts goes here
         */
        wp_enqueue_script( 'dokan-ls-custom-js', plugins_url( 'js/script.js', __FILE__ ), array( 'jquery' ), false, true );

        /**
         * Example for setting up text strings from Javascript files for localization.
         */
        wp_localize_script( 'dokan-ls-custom-js', 'dokanLiveSearch', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'loading_img' => plugins_url( 'images/loading.gif', __FILE__ )
        ));

    }

    /**
     * Callback for Widget Initialization
     *
     * @return void
     */
    public function initialize_widget_register(){
        register_widget( 'Dokan_Live_Search_Widget' );
    }

}

$dokan_live_search = Dokan_Live_Search::init();