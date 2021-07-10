=== Redirect multisite user to their own site ===
Contributors: markhowellsmead
Donate link: https://www.paypal.me/mhmli
Tags: multisite, wpmu, redirect, user, parse_request
Requires at least: 4.5
Tested up to: 4.6.1
Stable tag: 1.1.1
License: GPL v3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Redirect a multisite user to their own site.

== Description ==

If the current user in a multisite environment accesses a subsite to which access has not been granted, then redirect the user back to their own site. If the user has been granted access to more than one site in the Multisite installation, then no redirect can occur. (Because there is no way of finding out which one is “correct”.)

== Information for developers ==
There are three action hooks available to developers. You can use them, for example, to write a log file or notify an email address when an action is carried out.

* `redirect-multisite-user-to-their-own-site/no-sites` fires when the user is logged in but does not have specific access to any sites in the current Multisite installation. Receives the values `$current_user` (WordPress User object) and `$current_site_id` (integer) as attributes.
* `redirect-multisite-user-to-their-own-site/redirecting` fires immediately before the user is redirected to the appropriate site. Receives the values `$current_user` (WordPress User object), `$current_site_id` (integer) and `$user_target_site->siteurl` (the URL to which the user will be redirected) as attributes.
* `redirect-multisite-user-to-their-own-site/not-allowed` fires when the user has authorized access to more than one site in the installation, and can therefore not be redirected. Receives the values `$current_user` (WordPress User object), `$current_site_id` (integer) and `$user_sites` (array) as attributes.

This plugin requires PHP 5.3 or newer because it uses PHP namespaces.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu.
3. That's it.

== Changelog ==

= 1.1.1 =
* Extend README with additional clarification. No functional changes.

= 1.1.0 =
* Revise text domain slug, action hook names and Plugin URI in plugin description.

= 1.0.1 =
* Change plugin URL to WordPress repository version.
* Change text domain to reflect real plugin name.

= 1.0.0 =
* Initial WordPress SVN repository commit.

= 0.3.0 =
* Fix logic to check whether user is logged in, and to verify that the current site ID is in the array of the user's sites.

= 0.2.0 =
* Fix namespace syntax
* Amend variable naming conventions
* Add actions to allow external code to access the functionality.

= 0.1.0 =
* Initial commit of pre-release version.
