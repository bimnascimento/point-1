<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Methods related to user handling in WooCommerce
 *
 * @class WCCF_WC_User
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_WC_User')) {

class WCCF_WC_User
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

        /**
         * FRONTEND ACCOUNT FIELDS
         */

        // Print user fields in frontend account edit page
        add_action('woocommerce_edit_account_form', array($this, 'print_fields_frontend_account_edit'));

        // Validate user fields submitted from account details page
        add_action('woocommerce_save_account_details_errors', array($this, 'validate_user_field_values_account_details'), 10, 2);

        /**
         * FRONTEND ADDRESS FIELDS
         */

        // Print user fields in frontend address edit pages
        add_action('woocommerce_before_edit_address_form_billing', array($this, 'print_fields_frontend_billing_address_edit'));
        add_action('woocommerce_after_edit_address_form_billing', array($this, 'print_fields_frontend_billing_address_edit'));
        add_action('woocommerce_before_edit_address_form_shipping', array($this, 'print_fields_frontend_shipping_address_edit'));
        add_action('woocommerce_after_edit_address_form_shipping', array($this, 'print_fields_frontend_shipping_address_edit'));

        // Validate user fields submitted from address edit page
        // NOTE: This is a hack since there's no hook for validation, change this bit later
        add_filter('woocommerce_edit_address_slugs', array($this, 'validate_user_field_values_address'));

        // Save address related user field values on address update
        add_action('woocommerce_customer_save_address', array($this, 'save_user_field_values_address_update'), 10, 2);
    }

    /**
     * Print user fields in frontend WooCommerce account edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_account_edit()
    {
        // Get user fields to print
        $fields = WCCF_User_Field_Controller::get_filtered();

        // Print only fields set to display on user profile
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Validate user fields submitted from account details page
     *
     * @access public
     * @return void
     */
    public function validate_user_field_values_account_details()
    {
        // Validate only those fields that were displayed
        $fields = WCCF_User_Field_Controller::get_filtered();
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'user_profile');

        // Validate user fields
        WCCF_Field_Controller::validate_posted_field_values('user_field', array(
            'fields' => $fields,
        ));
    }

    /**
     * Print user fields in frontend billing address edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_billing_address_edit()
    {
        // Get corresponding checkout hook
        $checkout_hook = strpos(current_filter(), 'before') !== false ? 'woocommerce_before_checkout_billing_form' : 'woocommerce_after_checkout_billing_form';

        // Get fields to print
        $fields = WCCF_User_Field_Controller::get_filtered(null, array(), $checkout_hook);
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'billing_address');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Print user fields in frontend shipping address edit page
     *
     * @access public
     * @return void
     */
    public function print_fields_frontend_shipping_address_edit()
    {
        // Get corresponding checkout hook
        $checkout_hook = strpos(current_filter(), 'before') !== false ? 'woocommerce_before_checkout_shipping_form' : 'woocommerce_after_checkout_shipping_form';

        // Get fields to print
        $fields = WCCF_User_Field_Controller::get_filtered(null, array(), $checkout_hook);
        $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', 'shipping_address');

        // Print user fields
        WCCF_Field_Controller::print_fields($fields, get_current_user_id());
    }

    /**
     * Validate user fields submitted from address edit page
     * NOTE: This is a hack since there's no hook for validation, change this bit later
     *
     * @access public
     * @param array $slugs
     * @return void
     */
    public function validate_user_field_values_address($slugs = array())
    {
        // Check if validation is needed
        if (doing_filter('template_redirect')) {

            global $wp;

            // Prevent infinite loop
            remove_filter('woocommerce_edit_address_slugs', array($this, 'validate_user_field_values_address'));

            // Check which address is being saved
            $address_type = isset($wp->query_vars['edit-address']) ? wc_edit_address_i18n(sanitize_title($wp->query_vars['edit-address']), true) : 'billing';

            // Validate only those fields that were displayed
            $fields = WCCF_User_Field_Controller::get_filtered();
            $fields = WCCF_Field_Controller::filter_by_property($fields, 'display_as', $address_type . '_address');

            // Validate user fields
            WCCF_Field_Controller::validate_posted_field_values('user_field', array(
                'fields' => $fields,
            ));
        }

        // Return filter value
        return $slugs;
    }

    /**
     * Save address related user field values on address update
     *
     * @access public
     * @param int $user_id
     * @param string $address_type
     * @return void
     */
    public function save_user_field_values_address_update($user_id, $address_type)
    {
        // Store posted field values
        WCCF_Field_Controller::store_field_values($user_id, 'user_field', true);
    }





}

WCCF_WC_User::get_instance();

}
