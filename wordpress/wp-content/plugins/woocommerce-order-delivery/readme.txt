=== WooCommerce Order Delivery ===
Contributors: woothemes, themesquad
Tags: woocommerce, delivery, date
Requires at least: 4.1
Tested up to: 4.7.3
Stable tag: 1.1.1
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
WC requires at least: 2.5.0
WC tested up to: 3.0.0

Choose a delivery date during checkout for the order.

== Description ==

This extension makes it easy for customers to choose a delivery date for their orders during the checkout process or simply notify them about the shipping and delivery estimated dates.

As the site owner, you can decide which dates are not available for shipping and delivery, these can be holidays or similar situations. In addition, you can disable specific delivery periods by country or state.

With the capture of the date delivery, you can process the orders more efficiently, improving your productivity and you will get customers that are satisfied.

= Features =

* Configure the settings with an intuitive and easy to use admin interface.
* Quick guide sections within the admin panel.
* Full localization support.
* Set your week days for shipping and delivery,
* the minimum number of days it takes for you to process and ship an order,
* the range of days it takes for you to deliver an order,
* and much more settings.
* Define the not allowed periods for ship and deliver orders, like your holidays or similar situations.
* Restricts specific delivery periods by *Country* or *States*.
* Display shipping and delivery information in the checkout page or let the customer to choose a delivery date.
* Include the delivery information in the order details, emails, etc.
* Sort your shop orders by delivery date.
* Integrated with the WooCommerce templates for extend/customize the delivery date sections in your theme.
* Developer friendly with tons of hooks and inline comments for extend the plugin's functionality easily.

== Installation ==

1. Unzip and upload the plugin’s folder to your /wp-content/plugins/ directory.
2. Activate the extension through the ‘Plugins’ menu in WordPress.
3. Go to WooCommerce > Settings > Shipping & Delivery to configure the plugin.

== Documentation & support ==

Visit our [Product page](http://docs.woothemes.com/document/woocommerce-order-delivery/) to read the documentation and get support.

== Screenshots ==

1. Checkout page with the shipping and delivery information.
2. Choosing a delivery date in the checkout page.

== Changelog ==

= 1.1.1 - March 30, 2017 =
* Fix - Fixed empty value in the 'states' field for the events of the delivery calendar.
* Tweak - Added 'clear' option in the 'states' field for the events of the delivery calendar.
* Tweak - Renamed WooCommerce version 2.7 to 3.0.

= 1.1.0 - March 9, 2017 =
 * Feature - Added a setting to make the delivery date an optional, required or auto-generated field in the checkout form.
 * Fix - Missing delivery info in the 'customer_on_hold_order' emails.
 * Fix - Display always the 'Delivery Date' column before the 'Date' column in the order list.
 * Fix - Fixed the appearance of the 'help tips' icons on the settings page.
 * Dev - Added plugin constants.
 * Dev - Deprecated 'dir_path', 'dir_url', 'date_format', 'date_format_js' and 'prefix' properties in the main class.
 * Dev - Updated bootstrap-datepicker.js library to the version 1.6.4.
 * Dev - Added wc-od-datepicker.js script to abstract the datepicker library.
 * Dev - Checkout class rewritten to make it more extensible by developers.
 * Dev - Set the minimum requirements to WP 4.1+ and WC 2.5+.
 * Dev - Moved class loading (autoload) code to the 'WC_OD_Autoloader' class.
 * Dev - Refactored singleton pattern code in the 'WC_OD_Singleton' class.
 * Tweak - Added compatibility with WooCommerce 2.7.
 * Tweak - Removed Select2 and jquery.BlockUI assets. It only uses the libraries included with WooCommerce.
 * Tweak - Added the template 'emails/email-delivery-date.php' to display the delivery details on emails.
 * Tweak - Updated the templates 'order/delivery-date.php' and 'checkout/form-delivery-date.php' to make them more customizable.
 * Tweak - Avoid duplicate numbers when displaying a delivery range with the minimum value equal to the maximum value.
 * Tweak - Added singular string for the delivery range text displayed in the checkout form.
 * Tweak - Use the global variable '$wp_locale' to fetch the weekdays strings in the function 'wc_od_get_week_days'.
 * Tweak - Use the timezone of the site instead of UTC for all the date operations.
 * Tweak - Added hooks to customize the calendar styles.

= 1.0.6 - January 19, 2017 =
 * Tweak - Calculate the first shipping and delivery dates using the site's timezone instead of UTC for a more accurate result.

= 1.0.5 - November 30, 2016 =
 * Fix - Fixed bug calculating the first shipping date for orders with min_working_days > 0 and ordered after the time limit.
 * Fix - Fixed deprecated notice with the woocommerce_update_option_X action hook when saving the plugin settings.

= 1.0.4 - November 21, 2016 =
 * Fix - Fixed issue when checking the time limit to deliver orders on the same day.

= 1.0.3 - October 18, 2016 =
 * Fix - Fixed the earlier day for UTC minus timezones in the checkout calendar.

= 1.0.2 - June 28, 2016 =
 * Tweak - Added WooCommerce 2.6 compatibility.
 * Fix - Fixed datepicker styles for the themes: Storefront 2.0, Twenty Fifteen 1.5 and Twenty Sixteen 1.2.
 * Fix - Fixed typo when calling the 'woocommerce_email_subject_customer_processing_order' in the WC_OD_Order_Details class.

= 1.0.1 - December 14, 2015 =
 * Fix - Added required field validation in the checkout form.

= 1.0.0 - March 9, 2015 =
 * Initial release.

== Upgrade Notice ==

= 1.1 =
1.1 is a major update. It is important that you make backups and ensure you have installed WC 2.5+ before upgrading.
