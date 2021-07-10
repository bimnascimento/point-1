<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product property object class
 *
 * @class WCCF_Product_Property
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Product_Property')) {

class WCCF_Product_Property extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_product_prop';
    protected $post_type_short          = 'product_prop';
    protected $post_type_abbreviation   = 'pp';

    // Define properties unique to this object type
    protected $public;
    protected $pricing_method;
    protected $pricing_value;

    // Define meta keys
    protected static $meta_properties = array(
        'public'            => 'bool',
        'pricing_method'    => 'string',
        'pricing_value'     => 'float',
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
