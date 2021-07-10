<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to user handling in WordPress
 *
 * @class WCCF_WP_User
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WP_User')) {

class WCCF_WP_User
{
    // Singleton instance
    protected static $instance = false;

    /**
     * Singleton control
     */
    public static function get_instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor class
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        // Continue when plugins are loaded
        add_action('plugins_loaded', array($this, 'plugins_loaded'), 1);
    }

    /**
     * On plugins loaded
     *
     * @access public
     * @return void
     */
    public function plugins_loaded()
    {
        // Check environment
        if (!WCCF::check_environment()) {
            return;
        }

        // Print user fields in frontend user registration page
        add_action('register_form', array($this, 'print_fields_frontend_register'));

        // Print user fields in backend user add page
        add_action('user_new_form', array($this, 'print_fields_backend_add'), 1);

        // Print user fields in backend user edit page
        add_action('show_user_profile', array($this, 'print_fields_backend_update'), 1);
        add_action('edit_user_profile', array($this, 'print_fields_backend_update'), 1);

        // Validate user field values from frontend user registration
        add_filter('registration_errors', array($this, 'validate_user_field_values'), 10, 3);

        // Save user field values for newly created user account
        add_action('user_register', array($this, 'save_user_field_values_create'));

        // Save user field values for user account updates
        add_action('profile_update', array($this, 'save_user_field_values_update'));
    }

    /**
     * Print user fields in frontend user registration page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_register()
    {
        // Get fields to display
        $fields = WCCF_User_Field_Controller::get_filtered();

        // Check if we have any fields to display
        if ($fields) {

            // Display list of fields
            WCCF_Field_Controller::print_fields($fields);
        }
    }

    /**
     * Print user fields in backend user add page
     *
     * @access public
     * @return void
     */
    public function print_fields_backend_add()
    {
        WCCF_WP_User::print_fields_backend();
    }

    /**
     * Print user fields in backend user edit page
     *
     * @access public
     * @param object $profileuser
     * @return void
     */
    public function print_fields_backend_update($profileuser)
    {
        WCCF_WP_User::print_fields_backend($profileuser->ID);
    }

    /**
     * Print user fields in backend user edit page
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function print_fields_backend($user_id = null)
    {
        // Get fields to display
        $fields = WCCF_User_Field_Controller::get_filtered();

        // Check if we have any fields to display
        if ($fields) {

            // Display title
            echo '<h2>' . apply_filters('wccf_context_label', WCCF_Settings::get('alias_user_field'), 'user_field', 'backend') . '</h2>';

            // Open container
            echo '<table class="form-table"><tbody>';

            // Display list of fields
            WCCF_Field_Controller::print_fields($fields, $user_id);

            // Close container
            echo '</tbody></table>';
        }
    }

    /**
     * Validate user field values from frontend user registration
     *
     * @access public
     * @param object $errors
     * @param string $sanitized_user_login
     * @param string $user_email
     * @return object
     */
    public function validate_user_field_values($errors, $sanitized_user_login, $user_email)
    {
        // Validate user fields
        WCCF_Field_Controller::validate_posted_field_values('user_field', array('wp_errors' => $errors));

        // Return errors
        return $errors;
    }

    /**
     * Save user field values for newly created user account
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function save_user_field_values_create($user_id)
    {
        define('WCCF_BACKEND_USER_REGISTER', true);
        WCCF_Field_Controller::store_field_values($user_id, 'user_field', true);
    }

    /**
     * Save user field values during user account updates
     *
     * @access public
     * @param int $user_id
     * @return void
     */
    public function save_user_field_values_update($user_id)
    {
        WCCF_Field_Controller::store_field_values($user_id, 'user_field', true);
    }



}

WCCF_WP_User::get_instance();

}
