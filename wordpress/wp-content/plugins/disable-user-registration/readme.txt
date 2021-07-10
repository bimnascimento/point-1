=== Disable User Registration ===

Contributors: acub
Tags: disable user registration, disable registration, disable sign-up, security
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=LC9KYCUBBDSY2&lc=RO&item_name=websiter%2ero&item_number=dur%2dplugin&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_LG%2egif%3aNonHosted
Requires at least: 3.5
Tested up to: 4.7
Stable tag: 1.0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Stops bots from registering accounts when <code>Anyone can register</code> is unchecked in Dashboard > Settings > General > Membership.

== Description ==

Un-checking "Anyone can register" in Settings only hides the sign-up link from login page, it doesn't disable user registration and bots can continue to create users programatically.

This plugin stops them. Activate it, uncheck "Anyone can register" and the only way to add new users will be from Dashboard, being logged in with an account having the `create_users` capability, by going to `Users > Add new`.

= Very Important Notice =

If you managed to lock yourself out of the admin area and are trying to create a new user with <code>administrator</code> role by uploading and executing a <code>php</code> file on your server, this plugin will effectively block the attempt. You will need to rename or delete the folder of this plugin (in order to deactivate it) to be able to add users from scripts without being logged in into an account having the `create_users` capability.

== Installation ==

1. Install the plugin from WP repository or upload it to the `/wp-content/plugins/` directory of your website.
2. Activate from 'Plugins' menu in WordPress.
3. You're done. No more bot created user accounts!
3. Happy WordPress-ing!

== Uninstall ==

1. Deactivate from Plugins list in WordPress Dashboard...
2. ...or rename/delete plugin folder (`wp-content/plugins/disable-user-registration`)

== Frequently Asked Questions ==

= Are you going to maintain this plugin? =

Yes, I intend to keep this plugin compatible with the latest version of WordPress.

= It doesn't work! A new user just registered on my website! =

Make sure the `"Anyone can register"` checkbox is unchecked in Dashboard > Settings > General. If it was checked, do not forget to save changes (bottom of page) after you uncheck it!

Also, please note this plugin was tested against the `known` methods of adding users in WordPress from script files. As soon as I learn about new ways of creating users in WordPress from outside Dashboard I will find a way to block it and update the plugin.

Needless to say, if you chance upon such a method, I would be grateful to receive the script/file you believe was responsible for creating a user, even if it looks like nonsense at first glance.

= Other plugins provide same functionality. Why is this one better? =

At the time I coded this plugin, one other plugin (Disable Registration Page) existed, disabling registration page. Upon close inspection, it turned out it didn't disable user registration, it only redirects when `$_GET` parameter of `action` is set to `registration`. Basically, it doesn't stop bots from creating users programmatically, it only stops humans from accessing the registration page.

From my point of view, the only effective security measure to disable unauthorised user registration is by not allowing it for any script that is not logged in with an account having the `create_users` capability.

In addition, my plugin correctly allows user registration when `"Anyone can register"` is checked in Dashboard > Settings > General > Membership.

== Changelog ==

= 1.0.1 =
* Launched.