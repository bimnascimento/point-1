<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Version Control
 *
 * WARNING: Make sure to update version number here as well as in the main class name
 */
$version = '2';

global $rightpress_helper_version;

if (!$rightpress_helper_version || $rightpress_helper_version < $version) {
    $rightpress_helper_version = $version;
}

/**
 * Proxy Class
 */
if (!class_exists('RightPress_Helper')) {

final class RightPress_Helper
{

    /**
     * Method overload
     *
     * @access public
     * @param string $method_name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic($method_name, $arguments)
    {
        // Get latest version of the main class
        global $rightpress_helper_version;

        // Get main class name
        $class_name = 'RightPress_Helper_' . $rightpress_helper_version;

        // Call main class
        return call_user_func_array(array($class_name, $method_name), $arguments);
    }
}
}

/**
 * Main Class
 */
if (!class_exists('RightPress_Helper_2')) {

final class RightPress_Helper_2
{

    /**
     * Include template
     *
     * @access public
     * @param string $template
     * @param string $plugin_path
     * @param string $plugin_name
     * @param array $args
     * @return string
     */
    public static function include_template($template, $plugin_path, $plugin_name, $args = array())
    {
        if ($args && is_array($args)) {
            extract($args);
        }

        // Get template path
        $template_path = self::get_template_path($template, $plugin_path, $plugin_name);

        // Check if template exists
	if (!file_exists($template_path)) {

            // Add admin debug notice
            _doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $template_path), get_bloginfo('version'));
            return;
	}

        // Include template
        include $template_path;
    }

    /**
     * Select correct template (allow overrides in theme folder)
     *
     * @access public
     * @param string $template
     * @param string $plugin_path
     * @param string $plugin_name
     * @return string
     */
    public static function get_template_path($template, $plugin_path, $plugin_name)
    {
        $template = rtrim($template, '.php') . '.php';

        // Check if this template exists in current theme
        if (!($template_path = locate_template(array($plugin_name . '/' . $template)))) {
            $template_path = $plugin_path . 'templates/' . $template;
        }

        return $template_path;
    }

    /**
     * Check WooCommerce version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function wc_version_gte($version)
    {
        if (defined('WC_VERSION') && WC_VERSION) {
            return version_compare(WC_VERSION, $version, '>=');
        }
        else if (defined('WOOCOMMERCE_VERSION') && WOOCOMMERCE_VERSION) {
            return version_compare(WOOCOMMERCE_VERSION, $version, '>=');
        }
        else {
            return false;
        }
    }

    /**
     * Check WordPress version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function wp_version_gte($version)
    {
        $wp_version = get_bloginfo('version');

        // Treat release candidate strings
        $wp_version = preg_replace('/-RC.+/i', '', $wp_version);

        if ($wp_version) {
            return version_compare($wp_version, $version, '>=');
        }

        return false;
    }

    /**
     * Check PHP version
     *
     * @access public
     * @param string $version
     * @return bool
     */
    public static function php_version_gte($version)
    {
        return version_compare(PHP_VERSION, $version, '>=');
    }

    /**
     * Check if string contains phrase that starts with a given string
     *
     * @access public
     * @param string $string
     * @param string $phrase
     * @return bool
     */
    public static function string_contains_phrase($string, $phrase)
    {
        return preg_match('/.*(^|\s|#)' . preg_quote($phrase) . '.*/i', $string) === 1 ? true : false;
    }

    /**
     * Get list of roles assigned to current user
     *
     * @access public
     * @return array
     */
    public static function current_user_roles()
    {
        // User is not logged in
        if (!is_user_logged_in()) {
            return array();
        }

        // Get user roles
        $current_user = wp_get_current_user();
        return $current_user->roles;
    }

    /**
     * Get list of capabilities assigned to current user
     *
     * @access public
     * @return array
     */
    public static function current_user_capabilities()
    {
        // Groups plugin active?
        if (class_exists('Groups_User') && class_exists('Groups_Wordpress')) {
            $groups_user = new Groups_User(get_current_user_id());

            if ($groups_user) {
                return $groups_user->capabilities_deep;
            }
            else {
                return array();
            }
        }

        // Get regular WP capabilities
        else {

            $current_user = wp_get_current_user();
            $all_current_user_capabilities = $current_user->allcaps;
            $current_user_capabilities = array();

            if (is_array($all_current_user_capabilities)) {
                foreach ($all_current_user_capabilities as $capability => $status) {
                    if ($status) {
                        $current_user_capabilities[] = $capability;
                    }
                }
            }

            return $current_user_capabilities;
        }
    }

    /**
     * Get optimized lowercase locale with dash as a separator
     *
     * @access public
     * @param string $method
     *    - single - return first part of the locale only
     *    - double - return both parts of the locale only
     *    - mixed - return first part if both locales match and both parts if they differ
     * @return string
     */
    public static function get_optimized_locale($method = 'single')
    {
        // Split WordPress locale
        $parts = explode('_', get_locale());

        // Expected result?
        if (is_array($parts) && count($parts) == 2 && $parts[1] != 'US') {
            $first = strtolower($parts[0]);
            $second = strtolower($parts[1]);

            // Single, double or mixed?
            if ($method == 'single') {
                return $first;
            }
            else if ($method == 'double') {
                return $first . '-' . $second;
            }
            else if ($method == 'mixed') {
                return $first == $second ? $first : $first . '-' . $second;
            }
        }

        // Fallback
        return $method == 'double' ? 'en_en' : 'en';
    }

    /**
     * Add WooCommerce notice
     *
     * @access public
     * @param string $message
     * @param string $notice_type
     * @return void
     */
    public static function wc_add_notice($message, $notice_type = 'success')
    {
        wc_add_notice($message, $notice_type);
    }

    /**
     * Get array of term ids - parent term id and all children ids
     *
     * @access public
     * @param int $id
     * @param string $taxonomy
     * @return array
     */
    public static function get_term_with_children($id, $taxonomy)
    {
        $term_ids = array();

        // Check if term exists
        if (!get_term_by('id', $id, $taxonomy)) {
            return $term_ids;
        }

        // Store parent
        $term_ids[] = (int) $id;

        // Get and store children
        $children = get_term_children($id, $taxonomy);
        $term_ids = array_merge($term_ids, $children);
        $term_ids = array_unique($term_ids);

        return $term_ids;
    }

    /**
     * Check if post exists
     *
     * @access public
     * @param int $post_id
     * @return bool
     */
    public static function post_exists($post_id)
    {
        return get_post_status($post_id) !== false;
    }

    /**
     * Check post type
     *
     * @access public
     * @param mixed $post
     * @param string $type
     * @return bool
     */
    public static function post_type_is($post, $type)
    {
        return get_post_type($post) === $type;
    }

    /**
     * Check post status
     *
     * @access public
     * @param mixed $post
     * @param string $status
     * @return bool
     */
    public static function post_status_is($post, $status)
    {
        $post_id = is_object($post) ? $post->ID : $post;
        return get_post_status($post_id) === $status;
    }

    /**
     * Check if post is existant and not in trash
     *
     * @access public
     * @param int $post_id
     * @return bool
     */
    public static function post_is_active($post_id)
    {
        return self::post_exists($post_id) && !self::post_is_trashed($post_id);
    }

    /**
     * Check if post is trashed
     *
     * @access public
     * @param int $post_id
     * @return bool
     */
    public static function post_is_trashed($post_id)
    {
        return self::post_status_is($post_id, 'trash');
    }

    /**
     * Maybe strip dash and number from the end of term slug
     *
     * @access public
     * @param string $slug
     * @return string
     */
    public static function clean_term_slug($slug)
    {
        return preg_replace('/-\d+/', '', $slug);
    }

    /**
     * Unwrap array elements from get_post_meta moves all [0] elements one level higher
     *
     * @access public
     * @param array $input
     * @return array
     */
    public static function unwrap_post_meta($input)
    {
        $output = array();

        foreach ((array) $input as $key => $value) {
            if (count($value) == 1) {
                if (is_array($value)) {
                    $output[$key] = $value[0];
                }
                else {
                    $output[$key] = $value;
                }
            }
            else if (count($value) > 1) {
                $output[$key] = $value;
            }
        }

        return $output;
    }

    /**
     * Cast value to specified data type
     * Accepts types: int, bool, float, string, array, object, unset
     * Casts null and empty string to null for int and float (instead of 0) to differentiate between empty value and a zero set by user
     *
     * @access public
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    public static function cast_to($type = 'string', $value = '')
    {
        if ($type === 'int') {
            return ($value !== '' && $value !== null) ? (int) $value : null;
        }
        else if ($type === 'bool') {
            return (bool) $value;
        }
        else if ($type === 'float') {
            return ($value !== '' && $value !== null) ? (float) $value : null;
        }
        else if ($type === 'string') {
            return (string) $value;
        }
        else if ($type === 'array') {
            return (array) $value;
        }
        else if ($type === 'object') {
            return (object) $value;
        }
        else if ($type === 'unset') {
            return (unset) $value;
        }
        else {
            return $value;
        }
    }

    /**
     * Get empty value by data type
     * Accepts types: int, bool, float, string, array, object, unset
     * Uses null instead of 0 for int and float to indicate that value is indeed empty and not a zero set by user
     *
     * @access public
     * @param string $type
     * @return mixed
     */
    public static function get_empty_value_by_type($type = 'string')
    {
        if ($type === 'int') {
            return null;
        }
        else if ($type === 'bool') {
            return false;
        }
        else if ($type === 'float') {
            return null;
        }
        else if ($type === 'string') {
            return '';
        }
        else if ($type === 'array') {
            return array();
        }
        else if ($type === 'object') {
            return new stdClass();
        }
        else if ($type === 'unset') {
            return null;
        }
        else {
            return '';
        }
    }

    /**
     * Insert element to array after specific key
     *
     * @access public
     * @param array $array
     * @param string $search
     * @param array $insert
     * @return array
     */
    public static function insert_to_array_after_key($array, $search, $insert)
    {
        // Get position of the seach key
        if (isset($array[$search])) {
            $position = array_search($search, array_keys($array)) + 1;
        }
        else {
            $position = count($array);
        }

        // Extract array parts before and after proposed position
        $before = array_slice($array, 0, $position, true);
        $after = array_slice($array, $position, null, true);

        // Merge arrays and return
        return array_merge($before, $insert, $after);
    }

    /**
     * Shorten text
     *
     * @access public
     * @param string $text
     * @param int $max_chars
     * @return string
     */
    public static function shorten_text($text, $max_chars)
    {
        return substr($text, 0, $max_chars) . '...';
    }

    /**
     * Check if post meta key exists for a given post
     *
     * @access public
     * @param int $post_id
     * @param string $meta_key
     * @return bool
     */
    public static function post_meta_key_exists($post_id, $meta_key)
    {
        return self::meta_key_exists($meta_key, 'post', $post_id);
    }

    /**
     * Check if order item meta key exists for a given order item
     *
     * @access public
     * @param int $order_item_id
     * @param string $meta_key
     * @return bool
     */
    public static function order_item_meta_key_exists($order_item_id, $meta_key)
    {
        return self::meta_key_exists($meta_key, 'order_item', $order_item_id);
    }

    /**
     * Check if user meta key exists for a given user
     *
     * @access public
     * @param int $user_id
     * @param string $meta_key
     * @return bool
     */
    public static function user_meta_key_exists($user_id, $meta_key)
    {
        return self::meta_key_exists($meta_key, 'user', $user_id);
    }

    /**
     * Check if meta key exists for item of a given context
     *
     * Supported meta contexts: post, order_item, user
     *
     * @access public
     * @param string $meta_key
     * @param mixed $meta_contexts
     * @param int $item_id
     * @return bool
     */
    public static function meta_key_exists($meta_key, $meta_contexts = null, $item_id = null)
    {
        return self::get_meta($meta_key, $meta_contexts, $item_id, true);
    }

    /**
     * Get meta row by meta key
     *
     * Supported meta contexts: post, order_item, user
     *
     * @access public
     * @param string $meta_key
     * @param mixed $meta_contexts
     * @param int $item_id
     * @return bool
     */
    public static function get_meta_row($meta_key, $meta_contexts = null, $item_id = null)
    {
        return self::get_meta($meta_key, $meta_contexts, $item_id, false, true);
    }

    /**
     * Get meta value by meta key
     *
     * Supported meta contexts: post, order_item, user
     *
     * @access public
     * @param string $meta_key
     * @param mixed $meta_contexts
     * @param int $item_id
     * @param bool $count_only
     * @param bool $get_row
     * @return mixed
     */
    public static function get_meta($meta_key, $meta_contexts = null, $item_id = null, $count_only = false, $get_row = false)
    {
        global $wpdb;

        $meta_contexts = (array) $meta_contexts;

        // Get all contexts if left empty
        if (empty($meta_contexts)) {
            $meta_contexts = array('post', 'order_item', 'user');
        }

        // Check all meta contexts
        foreach ($meta_contexts as $meta_context) {

            // Get meta table name
            $table = _get_meta_table($meta_context);

            // Set up item constraint
            $item_constraint = $item_id !== null ? 'AND ' . $meta_context . '_id = ' . absint($item_id) : '';

            // Prepare fields to get
            if ($get_row) {
                $fields = '*';
            }
            else if ($count_only) {
                $fields = 'COUNT(*)';
            }
            else {
                $fields = 'meta_value';
            }

            // Prepare query
            $sql = $wpdb->prepare("SELECT $fields FROM $table WHERE meta_key = %s $item_constraint", $meta_key);

            // Run query
            if ($get_row) {
                $result = $wpdb->get_row($sql, ARRAY_A);
            }
            else {
                $result = $wpdb->get_var($sql);
            }

            // Check result
            if ($result) {
                return $count_only ? true : $result;
            }
        }

        return false;
    }

    /**
     * Delete meta row by meta key
     *
     * Supported meta contexts: post, order_item, user
     *
     * @access public
     * @param string $meta_key
     * @param mixed $meta_contexts
     * @param int $item_id
     * @return bool
     */
    public static function delete_meta($meta_key, $meta_contexts = null, $item_id = null)
    {
        $meta_contexts = (array) $meta_contexts;

        // Get all contexts if left empty
        if (empty($meta_contexts)) {
            $meta_contexts = array('post', 'order_item', 'user');
        }

        // Check all meta contexts
        foreach ($meta_contexts as $meta_context) {

            // Delete meta row(s)
            delete_metadata($meta_context, $item_id, $meta_key, '', !$item_id);
        }
    }

    /**
     * Get hash - either random or based on provided data
     *
     * @access public
     * @param bool $long
     * @param mixed $data
     * @return string
     */
    public static function get_hash($long = false, $data = null)
    {
        // Get data to hash
        $data = $data !== null ? json_encode($data) : (rand() . time() . rand());

        // Generate hash
        $hash = md5($data);

        // Shorten hash if needed and return
        return $long ? $hash : substr($hash, 0, 8);
    }

    /**
     * Get array value by key or return false if not set
     *
     * Unserializes value if serialized
     * Searches first level only
     *
     * @access public
     * @param array $array
     * @param mixed $key
     * @return mixed
     */
    public static function array_value_or_false($array, $key)
    {
        return isset($array[$key]) ? maybe_unserialize($array[$key]) : false;
    }

    /**
     * Get file content type (mime type) depending on PHP version
     *
     * @access public
     * @param string $file_path
     * @return string
     */
    public static function get_file_content_type($file_path)
    {
        // Since PHP version 5.3
        if (self::php_version_gte('5.3')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            return finfo_file($finfo, $file_path);
        }
        else {
            return mime_content_type($file_path);
        }
    }

    /**
     * Print link to post edit page
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @param int $max_chars
     * @return void
     */
    public static function print_link_to_post($id, $title = '', $pre = '', $post = '', $max_chars = null)
    {
        echo self::get_link_to_post_html($id, $title, $pre, $post, $max_chars);
    }

    /**
     * Format link to post edit page
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @param int $max_chars
     * @return string
     */
    public static function get_link_to_post_html($id, $title = '', $pre = '', $post = '', $max_chars = null)
    {
        // Get title to display
        $link_title = '';
        $title_to_display = !empty($title) ? $title : '#' . $id;

        // Maybe shorten title
        if ($max_chars !== null && strlen($title_to_display) > ($max_chars + 3)) {
            $link_title = $title_to_display;
            $title_to_display = self::shorten_text($title_to_display, $max_chars);
        }

        // Make link and return
        return $pre . ' <a href="post.php?post=' . $id . '&action=edit" title="' . $link_title . '">' . $title_to_display . '</a> ' . $post;
    }

    /**
     * Print frontend link to post
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function print_frontend_link_to_post($id, $title = '', $pre = '', $post = '')
    {
        echo self::get_frontend_link_to_post_html($id, $title, $pre, $post);
    }

    /**
     * Format frontend link to post
     *
     * @access public
     * @param int $id
     * @param string $title
     * @param string $pre
     * @param string $post
     * @return void
     */
    public static function get_frontend_link_to_post_html($id, $title = '', $pre = '', $post = '')
    {
        $title_to_display = !empty($title) ? $title : '#' . $id;
        $html = $pre . ' <a href="' . get_permalink($id) . '">' . $title_to_display . '</a> ' . $post;
        return $html;
    }

    /**
     * Check if value is date with correct format
     *
     * @access public
     * @param string $value
     * @param string $format
     * @return bool
     */
    public static function is_date($value, $format)
    {
        $is_date = false;

        // Maybe we have a newer PHP version?
        if (self::php_version_gte('5.3')) {

            // Initialize DateTime object
            $datetime = DateTime::createFromFormat($format, $value, self::get_time_zone());

            // Check if dates correspond
            if ($datetime && $datetime->format($format) === $value) {
                $is_date = true;
            }
        }

        // Unfortunately...
        else {

            // Remember current time zone and set ours (needed for date() function)
            $previous_timezone = @date_default_timezone_get();
            date_default_timezone_set(self::get_time_zone_string());

            // Check if date is valid
            if ($timestamp = strtotime($value)) {
                if (date($format, $timestamp) === $value) {
                    $is_date = true;
                }
            }

            // Revert to previous default time zone
            date_default_timezone_set($previous_timezone);
        }

        return $is_date;
    }

    /**
     * Make readable date/time from timestamp (yyyy-mm-dd hh:mm:ss)
     *
     * @access public
     * @param int $timestamp
     * @return string
     */
    public static function get_iso_datetime($timestamp = null)
    {
        $timestamp = ($timestamp === null ? time() : $timestamp);
        return self::get_adjusted_datetime($timestamp, 'Y-m-d H:i:s');
    }

    /**
     * Get timezone-adjusted formatted date/time string
     *
     * @access public
     * @param int $timestamp
     * @param string $format
     * @return string
     */
    public static function get_adjusted_datetime($timestamp, $format = null)
    {
        // Get datetime object
        $date_time = self::get_datetime_object($timestamp);

        // Get datetime as string in ISO format
        $date_time_iso = $date_time->format('Y-m-d H:i:s');

        // Hack to make date_i18n() work with our time zone
        $date_time_utc = new DateTime($date_time_iso);
        $time_zone_utc = new DateTimeZone('UTC');
        $date_time_utc->setTimezone($time_zone_utc);

        // No format passed? Get it from WordPress settings
        if ($format === null) {
            $format = get_option('date_format') . ' ' . get_option('time_format');
        }

        // Format and return
        return date_i18n($format, $date_time_utc->format('U'));
    }

    /**
     * Get usable datetime object with correct time zone from timestamp
     *
     * @access public
     * @param int $timestamp
     * @return object
     */
    public static function get_datetime_object($timestamp = null)
    {
        $timestamp = $timestamp ? '@' . $timestamp : null;
        $date_time = new DateTime($timestamp);
        $time_zone = self::get_time_zone();
        $date_time->setTimezone($time_zone);
        return $date_time;
    }

    /**
     * Get timezone object
     *
     * @access public
     * @return object
     */
    public static function get_time_zone()
    {
        return new DateTimeZone(self::get_time_zone_string());
    }

    /**
     * Get timezone string
     *
     * @access public
     * @return string
     */
    public static function get_time_zone_string()
    {
        if ($time_zone = get_option('timezone_string')) {
            return $time_zone;
        }

        if ($utc_offset = get_option('gmt_offset')) {

            $utc_offset = $utc_offset * 3600;
            $dst = date('I');

            // Try to get timezone name from offset
            if ($time_zone = timezone_name_from_abbr('', $utc_offset)) {
                return $time_zone;
            }

            // Try to guess timezone by looking at a list of all timezones
            foreach (timezone_abbreviations_list() as $abbreviation) {
                foreach ($abbreviation as $city) {
                    if ($city['dst'] == $dst && $city['offset'] == $utc_offset) {
                        return $city['timezone_id'];
                    }
                }
            }
        }

        return 'UTC';
    }

    /**
     * Check if this is a demo of the plugin
     *
     * @access public
     * @return bool
     */
    public static function is_demo()
    {
        return (strpos(self::get_request_url(), 'demo.rightpress.net') !== false);
    }

    /**
     * Get full URL of current request
     *
     * @access public
     * @return string
     */
    public static function get_request_url()
    {
        return 'http' . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /**
     * Get WooCommerce order
     *
     * @access public
     * @param int $order_id
     * @return object
     */
    public static function wc_get_order($order_id)
    {
        return self::wc_version_gte('2.2') ? wc_get_order($order_id) : new WC_Order($order_id);
    }

    /**
     * Get WooCommerce product
     *
     * @access public
     * @param int $product_id
     * @return object
     */
    public static function wc_get_product($product_id)
    {
        return self::wc_version_gte('2.2') ? wc_get_product($product_id) : get_product($product_id);
    }

    /**
     * Check if value is empty but not zero, that is - empty string,
     * null, boolean false or empty array
     *
     * @access public
     * @param mixed $value
     * @return bool
     */
    public static function is_empty($value)
    {
        return ($value === '' || $value === null || $value === false || count($value) === 0);
    }

    /**
     * Check if current page is backend user edit page
     *
     * @access public
     * @return bool
     */
    public static function is_wp_backend_user_edit_page()
    {
        return defined('IS_PROFILE_PAGE');
    }

    /**
     * Check if current page is backend new user page
     *
     * @access public
     * @return bool
     */
    public static function is_wp_backend_new_user_page()
    {
        if (!function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();
        return (is_object($screen) && $screen->base === 'user' && $screen->action === 'add');
    }

    /**
     * Check if current page is frontend user registration page
     *
     * @access public
     * @return bool
     */
    public static function is_wp_frontend_user_registration_page()
    {
        global $pagenow;
        return ($pagenow === 'wp-login.php' && !empty($_REQUEST['action']) && $_REQUEST['action'] === 'register');
    }

    /**
     * Filter associative array by an array of allowed keys
     *
     * @access public
     * @param array $input
     * @param array $allowed
     * @return array
     */
    public static function filter_by_keys($input, $allowed)
    {
        return array_intersect_key($input, array_flip($allowed));
    }

    /**
     * Filter associative array by an array of allowed keys and set default
     * values if they do not exist
     *
     * @access public
     * @param array $input
     * @param array $allowed
     * @return array
     */
    public static function filter_by_keys_with_defaults($input, $allowed)
    {
        return array_merge($allowed, self::filter_by_keys($input, array_keys($allowed)));
    }

    /**
     * Support for currency switcher extensions
     *
     * This is only supposed to be used for fees or fixed discounts set in config
     * of our own extensions - do not apply this to any prices set in WooCommerce
     * as currency switcher extensions are already converting those prices
     *
     * @access public
     * @param float $amount
     * @param string $to_currency
     * @param string $from_currency
     * @return float
     */
    public static function get_amount_in_currency($amount, $to_currency = null, $from_currency = null)
    {
        // Get from currency
        $from_currency = $from_currency ?: get_option('woocommerce_currency');

        // Get to currency
        $to_currency = $to_currency ?: get_woocommerce_currency();

        // Currency Switcher for WooCommerce by Aelia
        // https://aelia.co/shop/currency-switcher-woocommerce/
        $amount = apply_filters('wc_aelia_cs_convert', $amount, $from_currency, $to_currency);

        // WooCommerce Currency Switcher by RealMag777
        // https://wordpress.org/plugins/woocommerce-currency-switcher/
        // https://codecanyon.net/item/woocommerce-currency-switcher/8085217
        $amount = apply_filters('woocs_exchange_value', $amount);

        // Return possibly converted amount
        return (float) $amount;
    }

    /**
     * Check what kind of WordPress request this is
     *
     * Adapted from WooCommerce since they have set their method to private
     *
     * @access public
     * @param string $type
     * @return bool
     */
    public static function is_request($type)
    {
        switch ($type)
        {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return ((!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON'));
        }
    }

    /**
     * Attempt to get WooCommerce Product id
     *
     * @access public
     * @param mixed $product_id
     * @return void
     */
    public static function get_wc_product_id($product_id = null)
    {
        // Already set
        if ($product_id !== null) {
            return (int) $product_id;
        }

        global $post;

        // Post of type product
        if ($post && is_object($post) && isset($post->post_type) && $post->post_type === 'product') {
            return (int) $post->ID;
        }

        // Add to cart via GET
        if (isset($_GET['add-to-cart']) && is_numeric($_GET['add-to-cart']) && RightPress_Helper::post_type_is($_GET['add-to-cart'], 'product')) {
            return (int) $_GET['add-to-cart'];
        }

        // Add to cart via POST - version 1
        if (isset($_POST['action']) && $_POST['action'] === 'woocommerce_add_to_cart' && isset($_POST['product_id']) && is_numeric($_POST['product_id']) && RightPress_Helper::post_type_is($_POST['product_id'], 'product')) {
            return (int) $_POST['product_id'];
        }

        // Add to cart via POST - version 2
        if (isset($_POST['add-to-cart']) && is_numeric($_POST['add-to-cart']) && RightPress_Helper::post_type_is($_POST['add-to-cart'], 'product')) {
            return (int) $_POST['add-to-cart'];
        }

        // Failed figuring out product id
        return null;
    }

    /**
     * Attempt to get WooCommerce Order id
     *
     * @access public
     * @param mixed $order_id
     * @return void
     */
    public static function get_wc_order_id($order_id = null)
    {
        // Already set
        if ($order_id !== null) {
            return $order_id;
        }

        global $post;

        // Post of type shop order
        if ($post && is_object($post) && isset($post->post_type) && $post->post_type === 'shop_order') {
            return $post->ID;
        }

        // View Order query var
        if (get_query_var('view-order')) {
            return get_query_var('view-order');
        }

        // Order Received query var
        if (get_query_var('order-received')) {
            return get_query_var('order-received');
        }

        // Order items ajax request
        if (is_admin() && is_ajax() && isset($_POST['action']) && in_array($_POST['action'], array('woocommerce_load_order_items', 'woocommerce_save_order_items'), true) && !empty($_POST['order_id'])) {
            return $_POST['order_id'];
        }

        // Failed figuring out order id
        return null;
    }

    /**
     * Check if user owns WooCommerce Order
     *
     * @access public
     * @param int $user_id
     * @param int $order_id
     * @return bool
     */
    public static function user_owns_wc_order($user_id, $order_id)
    {
        // Load order object
        $order = RightPress_Helper::wc_get_order($order_id);

        // Check if order was loaded
        if ($order) {

            // Get user id from order
            $order_user_id = $order->get_user_id();

            // Check if order belongs to user
            if (!empty($order_user_id) && (int) $user_id === (int) $order_user_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user owns WooCommerce Order Item
     *
     * @access public
     * @param int $user_id
     * @param int $order_item_id
     * @return bool
     */
    public static function user_owns_wc_order_item($user_id, $order_item_id)
    {
        // Get order id
        $order_id = RightPress_Helper::get_wc_order_id_from_order_item_id($order_item_id);

        // Check if user owns order
        return $order_id ? RightPress_Helper::user_owns_wc_order($user_id, $order_id) : false;
    }

    /**
     * Get WooCommerce Order id from Order Item id
     *
     * @access public
     * @param int $order_item_id
     * @return int
     */
    public static function get_wc_order_id_from_order_item_id($order_item_id)
    {
        global $wpdb;

        // Get order id
        $table = _get_meta_table('order_item');
        $sql = $wpdb->prepare("SELECT order_id FROM $table WHERE order_item_id = %d", absint($order_item_id));
        $order_id = $wpdb->get_var($sql);

        // Check if order id was found
        return $order_id ?: false;
    }

    /**
     * Get WooCommerce coupon id from code
     *
     * @access public
     * @param string $code
     * @return int
     */
    public static function get_wc_coupon_id_from_code($code)
    {
        global $wpdb;
        return absint($wpdb->get_var($wpdb->prepare(apply_filters('woocommerce_coupon_code_query', "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish'"), $code)));
    }

    /**
     * Ensure string is unique by appending suffix to it
     *
     * @access public
     * @param string $string
     * @param mixed $compare
     * @param string $suffix
     * @return string
     */
    public static function ensure_unique_string($string, $compare, $suffix = '_1')
    {
        // Iterate till we come up with unique string
        while (true) {

            // Check if string is unique
            if (is_array($compare)) {
                $is_unique = !in_array($string, $compare, true);
            }
            else {
                $is_unique = $string !== $compare;
            }

            // Either break and return or append suffix and try again
            if ($is_unique) {
                break;
            }
            else {
                $string .= $suffix;
            }
        }

        return $string;
    }


}
}
