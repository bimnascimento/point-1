<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Checkout field object class
 *
 * @class WCCF_Checkout_Field
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Checkout_Field')) {

class WCCF_Checkout_Field extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_checkout_field';
    protected $post_type_short          = 'checkout_field';
    protected $post_type_abbreviation   = 'cf';

    // Define properties unique to this object type
    protected $pricing_method;
    protected $pricing_value;
    protected $position;
    protected $tax_class;

    // Define meta keys
    protected static $meta_properties = array(
        'pricing_method'    => 'string',
        'pricing_value'     => 'float',
        'position'          => 'string',
        'tax_class'         => 'string',
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
     * Sanitize position value
     *
     * @access public
     * @param array $data
     * @return mixed
     */
    protected function sanitize_position_value($data)
    {
        $positions = WCCF_WC_Checkout::get_positions();

        // Check if correct position was selected
        if (!empty($data['position']) && isset($positions[$data['position']])) {
            return $data['position'];
        }

        // Return default position
        $array_keys = array_keys($positions);
        return array_shift($array_keys);
    }


}
}
