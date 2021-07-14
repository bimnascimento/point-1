<?php
/*
Plugin Name: WooSlider - WooCommerce Products Slideshow
Plugin URI: http://woothemes.com/products/wooslider-products-slideshow/
Description: Add slideshows of your WooCommerce products to WooSlider.
Version: 1.0.10
Author: WooThemes
Author URI: http://woothemes.com/
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/
/*  Copyright 2012  WooThemes  (email : info@woothemes.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once( 'classes/class-wooslider-wc-products.php' );

if ( ! function_exists( 'woothemes_queue_update' ) )
    require_once( 'woo-includes/woo-functions.php' );

/* Integrate with the WooThemes Updater plugin for plugin updates. */
woothemes_queue_update( plugin_basename( __FILE__ ), 'fb2387de8d3a8501dab2329290f9d22e', '82250' );

// Make sure both WooSlider and WooCommerce are active.
$active_plugins = apply_filters( 'active_plugins', get_option('active_plugins' ) );
if ( in_array( 'wooslider/wooslider.php', $active_plugins ) && in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
    add_action( 'plugins_loaded', array( 'WooSlider_WC_Products', 'get_instance' ) );
} else {
    add_action( 'admin_notices', 'wooslider_wc_products_deactivated' );
}

function wooslider_wc_products_deactivated() {
    echo '<div class="error"><p>' . sprintf( __( 'WooSlider - WooCommerce Products Slideshow requires %s & %s to be installed and active.', 'wooslider-products-slideshow' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>', '<a href="http://www.woothemes.com/products/wooslider/" target="_blank">WooSlider</a>' ) . '</p></div>';
}
?>