<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Order field object class
 *
 * @class WCCF_Order_Field
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Order_Field')) {

class WCCF_Order_Field extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_order_field';
    protected $post_type_short          = 'order_field';
    protected $post_type_abbreviation   = 'of';

    // Define properties unique to this object type
    protected $public;

    // Define meta keys
    protected static $meta_properties = array(
        'public' => 'bool',
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


}
}
