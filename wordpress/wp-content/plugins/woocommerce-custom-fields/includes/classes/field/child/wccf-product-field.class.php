<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Product field object class
 *
 * @class WCCF_Product_Field
 * @package WooCommerce Custom Fields
 * @author RightPress
 */
if (!class_exists('WCCF_Product_Field')) {

class WCCF_Product_Field extends WCCF_Field
{
    // Define post type title
    protected $post_type                = 'wccf_product_field';
    protected $post_type_short          = 'product_field';
    protected $post_type_abbreviation   = 'pf';

    // Define properties unique to this object type
    protected $pricing_method;
    protected $pricing_value;
    protected $quantity_based;

    // Define meta keys
    protected static $meta_properties = array(
        'pricing_method'    => 'string',
        'pricing_value'     => 'float',
        'quantity_based'    => 'bool',
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
     * Get stored field value from order item meta
     *
     * @access public
     * @param int $item_id
     * @param string $access_key
     * @return mixed
     */
    public function get_stored_value_from_meta($item_id, $access_key)
    {
        // Check if such meta key exists
        if (!RightPress_Helper::order_item_meta_key_exists($item_id, $access_key)) {
            return null;
        }

        // Get and return value from meta
        return wc_get_order_item_meta($item_id, $access_key, true);
    }


}
}
