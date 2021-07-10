<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * User field object class
 *
 * @class WCCF_User_Field
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_User_Field')) {

class WCCF_User_Field extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_user_field';
    protected $post_type_short          = 'user_field';
    protected $post_type_abbreviation   = 'uf';

    // Define properties unique to this object type
    protected $position;
    protected $display_as;

    // Define meta keys
    protected static $meta_properties = array(
        'position'      => 'string',
        'display_as'    => 'string',
    );

    /**
     * Constructor class
     *
     * @access public
     * @param mixed $id
     * @param object $trigger
     * @return void
     */
    public function __construct($id)
    {
        // Construct parent first
        parent::__construct($id);
    }

    /**
     * Get meta properties
     *
     * @access public
     * @return array
     */
    protected function get_meta_properties()
    {
        return array_merge(parent::get_meta_properties(), self::$meta_properties);
    }

    /**
     * Get stored field value from user meta
     *
     * @access public
     * @param int $user_id
     * @param string $access_key
     * @return mixed
     */
    public function get_stored_value_from_meta($user_id, $access_key)
    {
        // Check if such meta key exists
        if (!RightPress_Helper::user_meta_key_exists($user_id, $access_key)) {
            return null;
        }

        // Get and return value from meta
        return get_user_meta($user_id, $access_key, true);
    }

    /**
     * Get display as value
     *
     * @access public
     * @return string
     */
    public function get_display_as()
    {
        // Return object property
        return isset($this->display_as) ? $this->display_as : null;
    }


}
}
