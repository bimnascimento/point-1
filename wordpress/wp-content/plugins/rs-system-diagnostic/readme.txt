=== RS System Diagnostic ===
Contributors: RedSand, blackhawkcybersec, rsm-support
Donate link: https://www.redsandmarketing.com/rs-system-diagnostic-donate/
Tags: admin, config, configuration, debug, debugging, htaccess, php, php version, server, support, support plugin, system, system data, system info, tech support, wp-config
Requires at least: 3.7
Tested up to: 4.7
Stable tag: trunk
License: GPLv2

Easily gather all the your WordPress and site configuration data in seconds, and send it directly to tech support by email or URL.

== Description ==

RS System Diagnostic is a useful tool for debugging WordPress and website issues. It displays system technical data for debugging. This information can be downloaded as a text file (.txt), emailed directly from the plugin, or remotely viewed with a unique temporary URL. (This URL can be re-generated at any time, and generating a new URL will revoke access for anyone who previously had it.)

Whether you're debugging your site yourself, or providing the tech data for a support professional, this plugin makes it easy to gather all the data you need in seconds, without having to hunt for it. You might just find it one of the essential tools in your diagnostic toolbox.

**Features**

* Effortlessly get an overview of site configuration
* Send System Diagnostic Data by email
* Download System Diagnostic Data as a text file
* Optionally, allow remote viewing of System Diagnostic Data by URL

**RS System Diagnostic is extremely useful for**

* Website owners
* Tech support professionals
* WordPress plugin/theme developers
* Website developers

**Two options for viewing site diagnostic data**

* Basic View (the default)
* Advanced View

*Basic View* includes the most essential data about your system. This will be all you need in most cases.

*Advanced View* goes into a lot more depth. It scans your system and includes the content of important conguration files: `php.ini`, `.htaccess`, and `wp-config.php`. (WordPress Database keys and passwords are hidden for security, as it's not likely these would be needed to share these with tech support. You should still be judicious and security-minded when sharing this data.) Sometimes plugin users aren't sure how to use FTP to view their site's files, or just don't know where to find them. This eliminates all that, because the plugin knows where to find them. Not only that, now you have everything in one place. Advanced view can prove helpful when you're trying to solve some of the more complex site debugging issues, or if you need just to review your site's configuration and/or security settings. *Advanced View is only available to Super Admins on multisite installs.*


**List of Data Displayed in Basic View (the default)**

* Platform
* Browser Name
* Browser Version
* User-Agent String
* Website Name
* WordPress Address (URL)
* Site Address (URL)
* WordPress Version
* Multisite
* Permalink Structure
* Active Theme
* Web Host
* Name Servers
* Proxy
* PHP Version
* MySQL Version
* Server Software
* Server Cache
* Server Hostname
* Server API Name
* WP Memory Limit
* WP Admin Memory Limit
* Current WP Memory Used
* Max WP Memory Used
* PHP Safe Mode
* PHP Memory Limit
* PHP Upload Max Size
* PHP Post Max Size
* PHP Upload Max Filesize
* PHP Time Limit
* PHP Max Input Vars
* PHP Arg Separator
* PHP Allow URL File Open
* PHP Allow URL File Include
* PHP Short Open Tags
* Expose PHP
* WP_DEBUG
* WP_DEBUG_LOG
* WP_DEBUG_DISPLAY
* SCRIPT_DEBUG
* WP_CACHE
* AUTOSAVE_INTERVAL
* WP_POST_REVISIONS
* EMPTY_TRASH_DAYS
* DISALLOW_FILE_EDIT
* WP Database Size
* WP Table Prefix Length
* WP Table Prefix Default
* Show On Front
* Page On Front
* Page For Posts
* Registered Post Stati
* WP Remote Post
* PHP Sessions
* Default Session Name
* Session Cookie Path
* Session Save Path
* Use Session Cookies
* Session Use Cookies Only
* Session Cookie HTTP Only
* Session Cookie Secure
* DISPLAY ERRORS
* ERROR LOGGING
* ERROR LOG LOCATION
* ERROR REPORTING LEVEL
* FSOCKOPEN
* cURL
* SOAP Client
* SUHOSIN
* MySQLi
* Loaded PHP Extensions
* List of Active Plugins
* Number of Active Plugins
* List of Network Active Plugins (if Multisite)
* Number of Network Active Plugins (if Multisite)
* List of Inactive Plugins
* Number of Inactive Plugins

**Additional Data Displayed in Advanced View**

* File Contents: wp-config.php (sensitive data hidden)
* File Contents: .htaccess
* File Contents: php.ini
* PHP Configuration Settings
* Loaded PHP Extensions
* Defined PHP Functions (public)
* Disabled PHP Functions
* Defined PHP Classes
* Defined PHP Constants (public)
* Defined PHP Variables (public)
* Defined $_SERVER Variables
* Defined $_ENV Variables
* Defined $_SESSION Variables
* Request Headers
* Response Headers

*NOTE: Advanced View is only available to Super Admins on multisite installs, and is not available in remote viewing for security reasons.*

= Documentation / Tech Support =
* Documentation: [Plugin Homepage](https://www.redsandmarketing.com/plugins/rs-system-diagnostic/)
* Tech Support: [WordPress Plugin Support](https://www.redsandmarketing.com/plugins/wordpress-plugin-support/)

= Languages =

If you would like to help translate, please [get in touch with us](https://www.redsandmarketing.com/plugins/wordpress-plugin-support/).

= Minimum Requirements =

* **WordPress 3.7 or higher** (Recommended: WordPress 4.4 or higher)
* **PHP 5.3 or higher** (Recommended: PHP 5.5 or higher)

Please see the plugin documentation's [minimum requirements section](https://www.redsandmarketing.com/plugins/rs-system-diagnostic/#requirements) for more information.

= WordPress Security Note =
As with any WordPress plugin, for security reasons, you should only download plugins from the author's site and from official WordPress repositories. When other sites host a plugin that is developed by someone else, they may inject code into that could compromise the security of your blog. We cannot endorse a version of this that you may have downloaded from another site. If you have downloaded the "RS System Diagnostic" plugin from another site, please download the current release from the from the [official RS System Diagnostic page on WordPress.org](https://wordpress.org/plugins/rs-system-diagnostic/).

== Installation ==

= Installation Instructions =

**Option 1:** Install the plugin directly through the WordPress Admin Dashboard (Recommended)

1. Go to *Plugins* -> *Add New*.

2. Type *RS System Diagnostic* into the Search box, and click *Search Plugins*.

3. When the results are displayed, click *Install Now*.

4. When it says the plugin has successfully installed, click **Activate Plugin** to activate the plugin (or you can do this on the Plugins page).

**Option 2:** Install .zip file through WordPress Admin Dashboard

1. Go to *Plugins* -> *Add New* -> *Upload*.

2. Click *Choose File* and find `rs-system-diagnostic.zip` on your computer's hard drive.

3. Click *Install Now*.

4. Click **Activate Plugin** to activate the plugin (or you can do this on the Plugins page).

**Option 3:** Install .zip file through an FTP Client (Recommended for Advanced Users Only)

1. After downloading, unzip file and use an FTP client to upload the enclosed `rs-system-diagnostic` directory to your WordPress plugins directory (usually `/wp-content/plugins/`) on your web server.

2. Go to your Plugins page in the WordPress Admin Dashboard, and find this plugin in the list.

3. Click **Activate** to activate the plugin.

**You're done!**

= WordPress Security Note =
As with any WordPress plugin, for security reasons, you should only download plugins from the author's site and from official WordPress repositories. When other sites host a plugin that is developed by someone else, they may inject code into that could compromise the security of your blog. We cannot endorse a version of this that you may have downloaded from another site. If you have downloaded the "RS System Diagnostic" plugin from another site, please download the current release from the from the [official RS System Diagnostic page on WordPress.org](https://wordpress.org/plugins/rs-system-diagnostic/).

= Documentation / Tech Support =
* Documentation: [Plugin Homepage](https://www.redsandmarketing.com/plugins/rs-system-diagnostic/)
* Tech Support: [WordPress Plugin Support](https://www.redsandmarketing.com/plugins/wordpress-plugin-support/)

== Screenshots ==

1. Basic View - The default view. It includes essential data about your system.
2. Advanced View - Advanced View goes into a lot more depth. It also scans your system and includes important conguration files: php.ini, .htaccess, and wp-config.php. (Sensitive data hidden.) It also includes defined functions, disabled functions, defined classes, defined constants, defined variables, defined $_SERVER & $_ENV vars, defined $_SESSION vars, and headers (request & response).

== Changelog ==

= 1.0.9 =
*released 03/23/17*

* Made various code enhancements and improvements.

= 1.0.8 =
*released 01/27/17*

* Added WordPress.org URL, official URL, and security check URL for each plugin listed.
* Made various code enhancements and improvements.

= 1.0.7 =
*released 01/17/17*

* Made improvements to detection of web proxies (such as Cloudflare).
* Added PHP Extensions Loaded to the Basic View.
* Made some formatting improvements to the Basic View to fix some word wrapping issues.
* Added some error correction to prevent file read permission issues with certain server configurations.
* Made some improvements to the inline code documentation. This is a work in progress.
* Made various code enhancements and improvements.

= 1.0.6 =
*released 01/13/17*

* Now detects 90+ web hosting companies.
* Added detection of Server Caching: Varnish / Nginx Reverse-Proxy.
* Removed calls to deprecated WordPress function `get_currentuserinfo()` and replaced with `wp_get_current_user()`.
* Made various code enhancements and improvements.

= 1.0.5 =
*released 12/28/16*

* Made various code enhancements and improvements.

= 1.0.4 =
*released 12/26/16*

* Added detection of 80+ web hosting companies.
* Basic View: Added Detection of WAFs/CDNs/Proxies: Cloudflare, Incapsula, Sucuri, etc.
* Advanced View: Added output of defined functions (public).
* Advanced View: Added output of disabled functions.
* Advanced View: Added output of defined classes.
* Advanced View: Added output of defined constants (public).
* Advanced View: Added output of defined variables (public).
* Advanced View: Added output of defined $_SERVER & $_ENV vars.
* Advanced View: Added output of defined $_SESSION vars.
* Advanced View: Added output of Headers (Request & Response).
* Made various code enhancements and improvements.

= 1.0.3 =
*released 12/08/16*

* Made various code enhancements and improvements.

= 1.0.2 =
*released 08/17/16*

* Various code improvements.

= 1.0.1 =
*released 03/19/16*

* Added checks to make sure website meets minimum PHP and WordPress version requirements. (PHP: 5.3+, WP 3.7+)
* Added more web hosting environments that the plugin can detect.
* Various code improvements.

= 1.0 =
*released 03/16/16*

* Initial public plugin release.

Forked from the [Send System Info plugin](https://wordpress.org/plugins/send-system-info/) by John Regan in September 2015.


= Changelog =
For a complete list of changes to the plugin, view the [Changelog](https://www.redsandmarketing.com/plugins/rs-system-diagnostic/version-history/).

== Upgrade Notice ==
= 1.0.9 =

* Made various code enhancements and improvements. Please see Changelog for details.
