<?php

if (!defined('ABSPATH'))
    die("Can't load this file directly");

class WPIE_PRODUCT {

    function __construct() {

        add_action('wp_ajax_wpie_get_product_export_preview', array(&$this, 'wpie_get_product_export_preview'));

        add_action('wp_ajax_wpie_get_product_import_preview', array(&$this, 'wpie_get_product_import_preview'));

        add_action('wp_ajax_wpie_create_product_csv', array(&$this, 'wpie_create_product_csv'));

        add_action('wp_ajax_wpie_update_product_csv', array(&$this, 'wpie_update_product_csv'));

        add_action('wp_ajax_wpie_upload_csv_file', array(&$this, 'wpie_upload_csv_file'));

        add_action('wp_ajax_wpie_save_product_fields', array(&$this, 'wpie_save_product_fields'));

        add_action('wp_ajax_wpie_create_product_preview', array(&$this, 'wpie_create_product_preview'));

        add_action('wp_ajax_wpie_execute_data_query', array(&$this, 'wpie_execute_data_query'));

        add_action('wp_ajax_wpie_import_products', array(&$this, 'wpie_import_products'));

        add_action('wp_ajax_wpie_remove_export_entry', array(&$this, 'wpie_remove_export_entry'));

        add_action('init', array(&$this, 'wpie_download_import_export_file'));

        add_filter('upload_mimes', array(&$this, 'wpie_update_mimes_data'));

        add_action('wp_ajax_wpie_import_products_percentage', array(&$this, 'wpie_import_products_percentage'));

        add_action('init', array(&$this, 'update_product_attributes'));
    }

    function wpie_update_mimes_data($mime_types = array()) {

        $mime_types['csv'] = 'text/csv';

        return $mime_types;
    }

    function wpie_get_product_count() {

        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'product',
            'fields' => 'ids'
        );
        $product_results = new WP_Query($query_args);

        $total_products = count($product_results->get_posts());

        return $total_products;
    }

    function wpie_get_all_products() {

        $query_args = array(
            'posts_per_page' => 2000,
            'post_type' => 'product',
            'orderby' => 'post_date',
            'order' => 'ASC',
        );
        $product_results = new WP_Query($query_args);

        $product_list = $product_results->get_posts();

        return $product_list;
    }

    function wpie_get_product_export_preview() {

        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        $data_query = isset($_POST['product_query']) ? $_POST['product_query'] : "";

        $total_products = isset($_POST['total_products']) ? $_POST['total_products'] : 0;

        $product_limit = isset($_POST['product_limit']) ? $_POST['product_limit'] : 0;

        $product_limit = $product_limit - $record_offset;

        $product_offset = isset($_POST['product_offset']) ? $_POST['product_offset'] : 0;

        $product_list_data = $this->get_filter_product_data($data_query, $record_offset, $record_limit, $product_offset, $product_limit, 1);

        $temp_data = json_decode(json_encode($product_query_data['product_data']), 1);

        $final_data = array();

        $product_field_list = $this->get_updated_product_fields();

        foreach ($product_list_data['product_data'] as $product_info) {

            $data_result = array();

            foreach ($product_field_list['product_fields'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $output = "";

                    $temp_data = $field_data['field_key'];

                    if ($field_data['field_key'] == 'images') {

                        foreach ($product_info[$temp_data] as $product_images) {

                            $output .= $product_images['src'] . ',';
                        }

                        $output = substr($output, 0, -1);
                    } else {

                        $output = isset($product_info[$temp_data]) ? $product_info[$temp_data] : "";
                    }

                    $data_result[] = $output;
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_products;

        $return_value['recordsTotal'] = $total_products;

        echo json_encode($return_value);

        die();
    }

    function wpie_create_product_filter_query($wpie_data) {

        $product_categories = isset($wpie_data['wpie_product_category']) ? $wpie_data['wpie_product_category'] : array();

        $product_ids = isset($wpie_data['wpie_product_ids']) ? $wpie_data['wpie_product_ids'] : array();

        $author_ids = isset($wpie_data['wpie_product_author_ids']) ? $wpie_data['wpie_product_author_ids'] : array();

        $total_records = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $offset_records = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $end_date = "";

        $start_date = "";

        if ($temp_start_date != "") {
            $temp_start_date = explode('-', $temp_start_date);

            $start_date = $temp_start_date[2] . '-' . $temp_start_date[0] . '-' . $temp_start_date[1];
        }
        if ($temp_end_date != "") {
            $temp_end_date = explode('-', $temp_end_date);

            $end_date = $temp_end_date[2] . '-' . $temp_end_date[0] . '-' . $temp_end_date[1];
        }

        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'product',
            'orderby' => 'ID',
            'order' => 'ASC',
            'fields' => 'ids',
        );

        if (!empty($product_categories)) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $product_categories
                ),
            );
        }

        if (!empty($product_ids)) {
            $query_args['post__in'] = $product_ids;
        }

        if ($total_records != "" && $total_records > 0) {
            $query_args['posts_per_page'] = $total_records;

            if ($offset_records != "" && $offset_records >= 0) {
                $query_args['offset'] = $offset_records;
            }
        }

        if ($end_date != "" || $start_date != "") {
            $date_data = array();

            if ($end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }

            if ($start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $query_args['date_query'] = array($date_data);
        }

        return json_encode($query_args);
    }

    function get_filter_product_data($wpie_data, $start_product, $total_records = 0, $product_offset = "", $product_limit = "", $is_preview = 0) {

        global $wpie_get_record_count;

        $product_data_list = array();


        if ($product_limit != "" && $total_records >= $product_limit) {
            $total_records = $product_limit;
            $product_data_list['status'] = "completed";
        }
        if ($product_offset != "" && $start_product == 0) {
            $start_product = $product_offset;
        }
        if ($total_records == 0) {
            $total_records = $wpie_get_record_count;
        }
        $query_args = json_decode(stripslashes($wpie_data), 1);

        $query_args['posts_per_page'] = $total_records;

        $query_args['offset'] = $start_product;

        $product_results = new WP_Query($query_args);

        $product_flag = 0;

        while ($product_results->have_posts()) {
            $product_results->the_post();
            global $product;

            if (!is_object($product) || $product_flag > 0) {
                if (function_exists(get_product)) {
                    $product = get_product($product_results->post);
                } else {
                    $product = new WC_product($product_results->post);
                }
                $product_flag++;
            }
            /* wp_set_object_terms($product_results->post->ID,$product_info['_product_type'],'product_type');	 */
            $product_data_list['product_data'][] = array(
                'post_title' => @$product->post->post_title,
                'id' => @(int) $product->is_type('variation') ? $product->get_variation_id() : $product->id,
                'created_at' => @$product->get_post_data()->post_date_gmt,
                'updated_at' => @$product->get_post_data()->post_modified_gmt,
                '_product_type' => @$product->product_type,
                'post_status' => @$product->get_post_data()->post_status,
                '_downloadable' => @$product->is_downloadable() ? 'yes' : 'no',
                '_virtual' => @$product->is_virtual() ? 'yes' : 'no',
                'permalink' => @$product->get_permalink(),
                '_sku' => @$product->get_sku(),
                '_price' => @$product->get_price(),
                '_regular_price' => @$product->get_regular_price(),
                '_sale_price' => @$product->get_sale_price() ? $product->get_sale_price() : null,
                'price_html' => @$product->get_price_html(),
                'taxable' => @$product->is_taxable() ? 'yes' : 'no',
                '_tax_status' => @$product->get_tax_status(),
                '_tax_class' => @$product->get_tax_class(),
                '_manage_stock' => @$product->managing_stock() ? 'yes' : 'no',
                '_stock' => @$product->get_stock_quantity(),
                '_stock_status' => @$product->is_in_stock() ? 'instock' : "outofstock",
                'backorders_allowed' => @$product->backorders_allowed(),
                '_backorders' => @$product->backorders,
                '_sold_individually' => @$product->is_sold_individually() ? 'yes' : 'no',
                'purchaseable' => @$product->is_purchasable() ? 'yes' : 'no',
                '_featured' => @$product->is_featured() ? 'yes' : 'no',
                '_visibility' => @$product->visibility,
                'catalog_visibility' => @$product->visibility,
                'on_sale' => @$product->is_on_sale() ? 'yes' : 'no',
                '_product_url' => @$product->is_type('external') ? $this->wpie_get_product_url($product) : '',
                '_button_text' => @$product->is_type('external') ? $this->wpie_get_button_text($product) : '',
                '_weight' => @$product->get_weight() ? wc_format_decimal($product->get_weight(), 2) : null,
                'dimensions' => @array(
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'unit' => get_option('woocommerce_dimension_unit'),
                ),
                '_length' => @$product->length,
                '_width' => @$product->width,
                '_height' => @$product->height,
                'unit' => @get_option('woocommerce_dimension_unit'),
                'shipping_required' => @$product->needs_shipping() ? 'yes' : 'no',
                'shipping_taxable' => @$product->is_shipping_taxable() ? 'yes' : 'no',
                'product_shipping_class' => @$product->get_shipping_class(),
                'shipping_class_id' => @( 0 !== $product->get_shipping_class_id() ) ? $product->get_shipping_class_id() : null,
                'post_content' => @do_shortcode($product->get_post_data()->post_content),
                'post_excerpt' => @$product->get_post_data()->post_excerpt,
                'comment_status' => @$product->get_post_data()->comment_status,
                'average_rating' => @wc_format_decimal($product->get_average_rating(), 2),
                'rating_count' => @(int) $product->get_rating_count(),
                'related_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', array_values($product->get_related())))),
                '_upsell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $product->get_upsells()))),
                '_crosssell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $product->get_cross_sells()))),
                'parent_id' => @$product->post->post_parent,
                'product_cat' => @$this->wpie_get_categories_by_producy_id($product->id), //implode(',',wp_get_post_terms( $product->id, 'product_cat', array( 'fields' => 'names' ) )),
                'product_tag' => @implode(',', wp_get_post_terms($product->id, 'product_tag', array('fields' => 'names'))),
                'images' => @$this->wpie_get_images($product),
                'has_post_thumbnail' => @has_post_thumbnail($product->id) ? 'yes' : 'no',
                'featured_src' => @wp_get_attachment_url(get_post_thumbnail_id($product->is_type('variation') ? $product->variation_id : $product->id )),
                '_product_attributes' => @$this->wpie_get_attributes($product),
                '_product_custom_fields' => @maybe_serialize(get_post_custom($product->id)),
                'downloads' => @$this->wpie_get_downloads($product),
                '_downloadable_files' => @maybe_serialize($product->get_files()),
                '_download_limit' => @$product->download_limit,
                '_download_expiry' => @$product->download_expiry,
                '_download_type' => @$product->download_type,
                '_purchase_note' => @do_shortcode(wp_kses_post($product->purchase_note)),
                'total_sales' => @metadata_exists('post', $product->id, 'total_sales') ? (int) get_post_meta($product->id, 'total_sales', true) : 0,
                'ping_status' => @$product->get_post_data()->ping_status ? 'open' : 'close',
                'variations' => @array(),
                '_variation_description' => "",
                'menu_order' => @$product->get_post_data()->menu_order,
                'post_parent' => @$product->get_post_data()->post_parent,
            );

            $product_data_list['current_product_id'] = $product->id;
            $start_product++;

            $product_data_list['start_product'] = $start_product;

            if ($product_limit != "" && $product_limit > 0) {
                $product_limit--;
                $product_data_list['product_limit'] = $product_limit;
                if ($product_data_list['product_limit'] == 0) {
                    if ($product->is_type('variable') && $product->has_child()) {
                        $product_data_list['child_product'] = 1;
                        $_SESSION['product_parent_stat'] = "completed";
                    }
                    $product_data_list['status'] = "pending";

                    break;
                }
            }

            if ($product->is_type('variable') && $product->has_child()) {
                $product_data_list['child_product'] = 1;
                if ($is_preview == 0) {
                    break;
                }
            }
        }


        wp_reset_postdata();
        return $product_data_list;
    }

    function get_filter_product_child_data($current_product_id, $start_child, $total_records = 0) {

        global $wpie_get_record_count;

        if ($total_records == 0) {
            $total_records = $wpie_get_record_count;
        }

        $product_data_list = array();

        if (function_exists(get_product)) {
            $product = get_product($current_product_id);
        } else {
            $product = new WC_product($current_product_id);
        }

        if ($product->is_type('variable') && $product->has_child()) {

            $child_count = 0;

            $start_offset = $start_child;
            foreach ($product->get_children() as $child_id) {

                $child_count++;

                if (!($start_offset < $child_count && ($total_records + $start_offset) > $child_count)) {

                    continue;
                }

                $variation = $product->get_child($child_id);

                if (!$variation->exists()) {
                    continue;
                }

                $variation = $product->get_child($child_id);

                if ($variation->product_type == 'variation') {
                    $product_data_list['product_data'][] = array(
                        'post_title' => @$variation->post->post_title,
                        'id' => @$variation->get_variation_id(),
                        'created_at' => @$variation->get_post_data()->post_date_gmt,
                        'updated_at' => @$variation->get_post_data()->post_modified_gmt,
                        '_product_type' => @$variation->product_type,
                        'post_status' => @$variation->get_post_data()->post_status,
                        '_downloadable' => @$variation->is_downloadable() ? 'yes' : 'no',
                        '_virtual' => @$variation->is_virtual() ? 'yes' : 'no',
                        'permalink' => @$variation->get_permalink(),
                        '_sku' => @$variation->get_sku(),
                        '_price' => @$variation->get_price(),
                        '_regular_price' => @$variation->get_regular_price(),
                        '_sale_price' => @$variation->get_sale_price() ? $variation->get_sale_price() : null,
                        'price_html' => @$variation->get_price_html(),
                        'taxable' => @$variation->is_taxable() ? 'yes' : 'no',
                        '_tax_status' => @$variation->get_tax_status(),
                        '_tax_class' => @$variation->get_tax_class(),
                        '_manage_stock' => @$variation->managing_stock() ? 'yes' : 'no',
                        '_stock' => @$variation->get_stock_quantity(),
                        '_stock_status' => @$variation->is_in_stock() ? 'instock' : "outofstock",
                        'backorders_allowed' => @$variation->backorders_allowed() ? 'yes' : 'no',
                        '_backorders' => @$variation->backorders,
                        '_sold_individually' => @$variation->is_sold_individually() ? 'yes' : 'no',
                        'purchaseable' => @$variation->is_purchasable() ? 'yes' : 'no',
                        '_featured' => @$variation->is_featured() ? 'yes' : 'no',
                        '_visibility' => @$variation->visibility, //$variation->variation_is_visible()?'yes':'no',
                        'catalog_visibility' => @$variation->visibility,
                        'on_sale' => @$variation->is_on_sale() ? 'yes' : 'no',
                        '_product_url' => @$variation->is_type('external') ? $this->wpie_get_product_url($variation) : '',
                        '_button_text' => @$variation->is_type('external') ? $this->wpie_get_button_text($variation) : '',
                        '_weight' => @$variation->get_weight() ? wc_format_decimal($variation->get_weight(), 2) : null,
                        'dimensions' => @array(
                    'length' => $variation->length,
                    'width' => $variation->width,
                    'height' => $variation->height,
                    'unit' => get_option('woocommerce_dimension_unit'),
                        ),
                        '_length' => @$variation->length,
                        '_width' => @$variation->width,
                        '_height' => @$variation->height,
                        'unit' => @get_option('woocommerce_dimension_unit'),
                        'product_shipping_class' => @$variation->get_shipping_class(),
                        'shipping_class_id' => @( 0 !== $variation->get_shipping_class_id() ) ? $variation->get_shipping_class_id() : null,
                        'shipping_required' => @$variation->needs_shipping() ? 'yes' : 'no',
                        'shipping_taxable' => @$variation->is_shipping_taxable() ? 'yes' : 'no',
                        'post_content' => @do_shortcode($variation->get_post_data()->post_content),
                        'post_excerpt' => @$variation->get_post_data()->post_excerpt,
                        'comment_status' => @$variation->get_post_data()->comment_status,
                        'average_rating' => @wc_format_decimal($variation->get_average_rating(), 2),
                        'rating_count' => @(int) $variation->get_rating_count(),
                        'related_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', array_values($variation->get_related())))),
                        '_upsell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $variation->get_upsells()))),
                        '_crosssell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $variation->get_cross_sells()))),
                        'parent_id' => @$variation->post->post_parent,
                        'product_cat' => @$this->wpie_get_categories_by_producy_id($variation->id), //implode(',',wp_get_post_terms( $variation->id, 'product_cat', array( 'fields' => 'names' ) )),
                        'product_tag' => @implode(',', wp_get_post_terms($variation->id, 'product_tag', array('fields' => 'names'))),
                        'images' => @$this->wpie_get_images($variation),
                        'has_post_thumbnail' => @has_post_thumbnail($variation->get_variation_id()) ? 'yes' : 'no',
                        'featured_src' => @wp_get_attachment_url(get_post_thumbnail_id($variation->is_type('variation') ? $variation->variation_id : $variation->id )),
                        '_product_attributes' => @$this->wpie_get_attributes($variation),
                        '_product_custom_fields' => @maybe_serialize(get_post_custom($child_id)),
                        'downloads' => @$this->wpie_get_downloads($variation),
                        '_downloadable_files' => @maybe_serialize($variation->get_files()),
                        '_download_limit' => @$variation->download_limit,
                        '_download_expiry' => @$variation->download_expiry,
                        '_download_type' => @$variation->download_type,
                        '_purchase_note' => @do_shortcode(wp_kses_post($variation->purchase_note)),
                        'total_sales' => @metadata_exists('post', $variation->id, 'total_sales') ? (int) get_post_meta($variation->id, 'total_sales', true) : 0,
                        'ping_status' => @$variation->get_post_data()->ping_status ? 'open' : 'close',
                        'variations' => @array(),
                        '_variation_description' => @get_post_meta($variation->id, '_variation_description', true),
                        'menu_order' => @$variation->get_post_data()->menu_order,
                        'post_parent' => @(int) $product->is_type('variation') ? $product->get_variation_id() : $product->id,
                    );
                }

                $start_child++;
                $product_data_list['start_child'] = $start_child;
                $product_data_list['get_child'] = 1;
            }
        }
        return $product_data_list;
    }

    function wpie_get_product_url($product) {
        if (function_exists($product->get_product_url())) {
            return $product->get_product_url();
        } else {
            return get_post_meta($product->id, '_product_url', true);
        }
    }

    function wpie_get_button_text($product) {
        if (function_exists($product->get_button_text())) {
            return $product->get_button_text();
        } else {
            return get_post_meta($product->id, '_button_text', true);
        }
    }

    function wpie_get_sku_by_id($ids = "") {
        if ($ids != "") {
            $product_ids = @explode(',', $ids);

            if (is_array($product_ids) && !empty($product_ids)) {
                $sku_list = array();

                foreach ($product_ids as $product_id) {

                    $current_sku = get_post_meta($product_id, '_sku', true);

                    if (trim($current_sku) != "") {
                        $sku_list[] = $current_sku;
                    }
                }

                return @implode(',', $sku_list);
            } else {
                return "";
            }
        } else {
            return "";
        }
    }

    function wpie_get_categories_by_producy_id($product_id) {
        $categories_list = wp_get_post_terms($product_id, 'product_cat');

        $product_categories = array();

        $temp_count = 0;

        foreach ($categories_list as $category) {
            $product_categories[$temp_count]['name'] = $category->name;

            $product_categories[$temp_count]['slug'] = $category->slug;

            $temp_count++;
        }

        return json_encode($product_categories);
    }

    function wpie_get_images($product) {

        $images = $attachment_ids = array();

        if ($product->is_type('variation')) {

            if (has_post_thumbnail($product->get_variation_id())) {

// Add variation image if set
                $attachment_ids[] = get_post_thumbnail_id($product->get_variation_id());
            } elseif (has_post_thumbnail($product->id)) {

// Otherwise use the parent product featured image if set
                $attachment_ids[] = get_post_thumbnail_id($product->id);
            }
        } else {

// Add featured image
            if (has_post_thumbnail($product->id)) {
                $attachment_ids[] = get_post_thumbnail_id($product->id);
            }

// Add gallery images
            $attachment_ids = array_merge($attachment_ids, $product->get_gallery_attachment_ids());
        }

// Build image data
        foreach ($attachment_ids as $position => $attachment_id) {

            $attachment_post = get_post($attachment_id);

            if (is_null($attachment_post)) {
                continue;
            }

            $attachment = wp_get_attachment_image_src($attachment_id, 'full');

            if (!is_array($attachment)) {
                continue;
            }

            $images[] = array(
                'id' => (int) $attachment_id,
                'created_at' => $attachment_post->post_date_gmt,
                'updated_at' => $attachment_post->post_modified_gmt,
                'src' => current($attachment),
                'title' => get_the_title($attachment_id),
                'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
                'position' => (int) $position,
            );
        }

        return $images;
    }

    function wpie_get_attributes($product) {

        $attributes = array();

        if ($product->is_type('variation')) {

// variation attributes
            $attributes = $product->get_variation_attributes();
        } else {
            $attributes = $product->get_attributes();
        }

        if (empty($attributes)) {
            $attributes = "";
        } else {
            $temp_attr = array();

            foreach ($attributes as $attribute) {

                if ($attribute['is_taxonomy'] && !taxonomy_exists($attribute['name'])) {
                    continue;
                }
                if ($attribute['is_taxonomy']) {

                    $values = wc_get_product_terms($product->id, $attribute['name'], array('fields' => 'names'));

                    $attribute['value'] = apply_filters('woocommerce_attribute', implode(', ', $values), $attribute, $values);
                }
                $temp_attr[] = $attribute;
            }
//$attributes = maybe_serialize($temp_attr);

            $new_temp_array = array();

            $temp_array_count = 0;

            foreach ($temp_attr as $temp_attr_all_data) {
                foreach ($temp_attr_all_data as $key => $value) {
                    $new_temp_array[$temp_array_count][$key] = @str_replace(",", "~||~", @str_replace('"', "``", $value));
                }
                $temp_array_count++;
            }

            $attributes = @str_replace("~||~", ",", str_replace(",", "||", json_encode($new_temp_array)));
        }
        return $attributes;
    }

    function wpie_get_downloads($product) {
        $downloads = array();

        if ($product->is_downloadable()) {

            foreach ($product->get_files() as $file_id => $file) {

                $downloads[] = array(
                    'id' => $file_id, // do not cast as int as this is a hash
                    'name' => $file['name'],
                    'file' => $file['file'],
                );
            }
        }

        return $downloads;
    }

    function get_updated_product_fields() {

        $old_product_fields = $this->get_new_product_fields();

        $new_fields = get_option('wpie_product_fields', $old_product_fields);

        $new_fields = maybe_unserialize($new_fields);

        return $new_fields;
    }

    function get_new_product_fields() {

        $product_fields = maybe_serialize($this->get_product_fields());

        return $product_fields;
    }

    function get_product_fields() {
        $get_product_fields = array(
            'product_fields' => array(
                array(
                    'field_key' => 'id',
                    'field_display' => 1,
                    'field_title' => 'Id',
                    'field_value' => 'Id',
                ),
                array(
                    'field_key' => 'post_title',
                    'field_display' => 1,
                    'field_title' => 'Product Name',
                    'field_value' => 'Product Name',
                ),
                array(
                    'field_key' => 'created_at',
                    'field_display' => 1,
                    'field_title' => 'Created Date',
                    'field_value' => 'Created Date',
                ),
                array(
                    'field_key' => 'post_content',
                    'field_display' => 1,
                    'field_title' => 'Description',
                    'field_value' => 'Description',
                ),
                array(
                    'field_key' => '_product_type',
                    'field_display' => 1,
                    'field_title' => 'Product Type',
                    'field_value' => 'Product Type',
                ),
                array(
                    'field_key' => 'product_cat',
                    'field_display' => 1,
                    'field_title' => 'Categories',
                    'field_value' => 'Categories',
                ),
                array(
                    'field_key' => '_price',
                    'field_display' => 1,
                    'field_title' => 'Price',
                    'field_value' => 'Price',
                ),
                array(
                    'field_key' => 'post_excerpt',
                    'field_display' => 1,
                    'field_title' => 'Short Description',
                    'field_value' => 'Short Description',
                ),
                array(
                    'field_key' => 'post_status',
                    'field_display' => 1,
                    'field_title' => 'Product Status',
                    'field_value' => 'Product Status',
                ),
                array(
                    'field_key' => 'permalink',
                    'field_display' => 1,
                    'field_title' => 'Permalink',
                    'field_value' => 'Permalink',
                ),
                array(
                    'field_key' => 'product_tag',
                    'field_display' => 1,
                    'field_title' => 'Tags',
                    'field_value' => 'Tags',
                ),
                array(
                    'field_key' => '_sku',
                    'field_display' => 1,
                    'field_title' => 'SKU',
                    'field_value' => 'SKU',
                ),
                array(
                    'field_key' => '_sale_price',
                    'field_display' => 1,
                    'field_title' => 'Sale Price',
                    'field_value' => 'Sale Price',
                ),
                array(
                    'field_key' => '_visibility',
                    'field_display' => 1,
                    'field_title' => 'Visibility',
                    'field_value' => 'Visibility',
                ),
                array(
                    'field_key' => 'on_sale',
                    'field_display' => 1,
                    'field_title' => 'On Sale',
                    'field_value' => 'On Sale',
                ),
                array(
                    'field_key' => '_stock_status',
                    'field_display' => 1,
                    'field_title' => 'Stock Status',
                    'field_value' => 'Stock Status',
                ),
                array(
                    'field_key' => '_regular_price',
                    'field_display' => 1,
                    'field_title' => 'Regular Price',
                    'field_value' => 'Regular Price',
                ),
                array(
                    'field_key' => 'total_sales',
                    'field_display' => 1,
                    'field_title' => 'Total Sales',
                    'field_value' => 'Total Sales',
                ),
                array(
                    'field_key' => '_downloadable',
                    'field_display' => 1,
                    'field_title' => 'Downloadable',
                    'field_value' => 'Downloadable',
                ),
                array(
                    'field_key' => '_virtual',
                    'field_display' => 1,
                    'field_title' => 'Virtual',
                    'field_value' => 'Virtual',
                ),
                array(
                    'field_key' => '_purchase_note',
                    'field_display' => 1,
                    'field_title' => 'Purchase Note',
                    'field_value' => 'Purchase Note',
                ),
                array(
                    'field_key' => '_weight',
                    'field_display' => 1,
                    'field_title' => 'Weight',
                    'field_value' => 'Weight',
                ),
                array(
                    'field_key' => '_length',
                    'field_display' => 1,
                    'field_title' => 'Length',
                    'field_value' => 'Length',
                ),
                array(
                    'field_key' => '_width',
                    'field_display' => 1,
                    'field_title' => 'Width',
                    'field_value' => 'Width',
                ),
                array(
                    'field_key' => '_height',
                    'field_display' => 1,
                    'field_title' => 'Height',
                    'field_value' => 'Height',
                ),
                array(
                    'field_key' => 'unit',
                    'field_display' => 1,
                    'field_title' => 'Unit',
                    'field_value' => 'Unit',
                ),
                array(
                    'field_key' => '_sold_individually',
                    'field_display' => 1,
                    'field_title' => 'Sold Individually',
                    'field_value' => 'Sold Individually',
                ),
                array(
                    'field_key' => '_manage_stock',
                    'field_display' => 1,
                    'field_title' => 'Manage Stock',
                    'field_value' => 'Manage Stock',
                ),
                array(
                    'field_key' => '_stock',
                    'field_display' => 1,
                    'field_title' => 'Stock',
                    'field_value' => 'Stock',
                ),
                array(
                    'field_key' => 'backorders_allowed',
                    'field_display' => 1,
                    'field_title' => 'Backorders Allowed',
                    'field_value' => 'Backorders Allowed',
                ),
                array(
                    'field_key' => '_backorders',
                    'field_display' => 1,
                    'field_title' => 'Backorders',
                    'field_value' => 'Backorders',
                ),
                array(
                    'field_key' => 'purchaseable',
                    'field_display' => 1,
                    'field_title' => 'Purchaseable',
                    'field_value' => 'Purchaseable',
                ),
                array(
                    'field_key' => '_featured',
                    'field_display' => 1,
                    'field_title' => 'Featured',
                    'field_value' => 'Featured',
                ),
                array(
                    'field_key' => 'taxable',
                    'field_display' => 1,
                    'field_title' => 'Is Taxable',
                    'field_value' => 'Is Taxable',
                ),
                array(
                    'field_key' => '_tax_status',
                    'field_display' => 1,
                    'field_title' => 'Tax Status',
                    'field_value' => 'Tax Status',
                ),
                array(
                    'field_key' => '_tax_class',
                    'field_display' => 1,
                    'field_title' => 'Tax Class',
                    'field_value' => 'Tax Class',
                ),
                array(
                    'field_key' => 'images',
                    'field_display' => 1,
                    'field_title' => 'Product Images',
                    'field_value' => 'Product Images',
                ),
                array(
                    'field_key' => 'has_post_thumbnail',
                    'field_display' => 1,
                    'field_title' => 'Product Image Set',
                    'field_value' => 'Product Image Set',
                ),
                array(
                    'field_key' => '_download_limit',
                    'field_display' => 1,
                    'field_title' => 'Download Limit',
                    'field_value' => 'Download Limit',
                ),
                array(
                    'field_key' => '_download_expiry',
                    'field_display' => 1,
                    'field_title' => 'Download Expiry',
                    'field_value' => 'Download Expiry',
                ),
                array(
                    'field_key' => '_downloadable_files',
                    'field_display' => 1,
                    'field_title' => 'Downloadable Files',
                    'field_value' => 'Downloadable Files',
                ),
                array(
                    'field_key' => '_download_type',
                    'field_display' => 1,
                    'field_title' => 'Download Type',
                    'field_value' => 'Download Type',
                ),
                array(
                    'field_key' => '_product_url',
                    'field_display' => 1,
                    'field_title' => 'Product URL',
                    'field_value' => 'Product URL',
                ),
                array(
                    'field_key' => '_button_text',
                    'field_display' => 1,
                    'field_title' => 'Button Text',
                    'field_value' => 'Button Text',
                ),
                array(
                    'field_key' => 'shipping_required',
                    'field_display' => 1,
                    'field_title' => 'Shipping Required',
                    'field_value' => 'Shipping Required',
                ),
                array(
                    'field_key' => 'shipping_taxable',
                    'field_display' => 1,
                    'field_title' => 'Shipping Taxable',
                    'field_value' => 'Shipping Taxable',
                ),
                array(
                    'field_key' => 'product_shipping_class',
                    'field_display' => 1,
                    'field_title' => 'Shipping Class',
                    'field_value' => 'Shipping Class',
                ),
                array(
                    'field_key' => 'shipping_class_id',
                    'field_display' => 1,
                    'field_title' => 'Shipping Class Id',
                    'field_value' => 'Shipping Class Id',
                ),
                array(
                    'field_key' => 'average_rating',
                    'field_display' => 1,
                    'field_title' => 'Average Rating',
                    'field_value' => 'Average Rating',
                ),
                array(
                    'field_key' => 'rating_count',
                    'field_display' => 1,
                    'field_title' => 'Rating Count',
                    'field_value' => 'Rating Count',
                ),
                array(
                    'field_key' => 'related_ids',
                    'field_display' => 1,
                    'field_title' => 'Related Ids',
                    'field_value' => 'Related Ids',
                ),
                array(
                    'field_key' => '_upsell_ids',
                    'field_display' => 1,
                    'field_title' => 'Upsell Ids',
                    'field_value' => 'Upsell Ids',
                ),
                array(
                    'field_key' => '_crosssell_ids',
                    'field_display' => 1,
                    'field_title' => 'Cross Sell Ids',
                    'field_value' => 'Cross Sell Ids',
                ),
                array(
                    'field_key' => '_product_attributes',
                    'field_display' => 1,
                    'field_title' => 'Attributes',
                    'field_value' => 'Attributes',
                ),
                array(
                    'field_key' => '_product_custom_fields',
                    'field_display' => 1,
                    'field_title' => 'Custom Fields',
                    'field_value' => 'Custom Fields',
                ),
                array(
                    'field_key' => 'post_parent',
                    'field_display' => 1,
                    'field_title' => 'Product Parent id',
                    'field_value' => 'Product Parent id',
                ),
                array(
                    'field_key' => '_variation_description',
                    'field_display' => 1,
                    'field_title' => 'Variation Description',
                    'field_value' => 'Variation Description',
                ),
                array(
                    'field_key' => 'menu_order',
                    'field_display' => 1,
                    'field_title' => 'Menu Order',
                    'field_value' => 'Menu Order',
                ),
                array(
                    'field_key' => 'comment_status',
                    'field_display' => 1,
                    'field_title' => 'Comment Status',
                    'field_value' => 'Comment Status',
                ),
                array(
                    'field_key' => 'ping_status',
                    'field_display' => 1,
                    'field_title' => 'Ping Status',
                    'field_value' => 'Ping Status',
                ),
            ),
        );

        return $get_product_fields;
    }

    function wpie_create_product_csv() {

        global $wpdb;

        $return_value = array();

        $product_export_data = $this->get_product_export_fields_data();

        $product_query_data = $this->wpie_create_product_filter_query($_POST);

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $filename = 'product_' . date('Y_m_d_H_i_s') . '.csv';

        $fh = @fopen(WPIE_UPLOAD_DIR . '/' . $filename, 'w+');

        //fputs($fh, $bom = ( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        if (!empty($product_export_data)) {
            foreach ($product_export_data as $new_data) {
                @fputcsv($fh, $new_data, $wpie_export_separator);
            }
        }

        @fclose($fh);

// readfile(WPIE_UPLOAD_DIR . '/' . $filename);

        $new_values = array();

        $new_values['export_log_file_type'] = 'export';
        $new_values['export_log_file_name'] = $filename;
        $new_values['export_log_data'] = 'Product';
        $new_values['create_date'] = current_time('mysql');

        $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
        $new_log_id = $wpdb->insert_id;

        $return_value['message'] = 'success';
        $return_value['file_name'] = WPIE_UPLOAD_DIR . '/' . $filename;
        $return_value['status'] = 'pending';
        $return_value['product_query'] = $product_query_data;
        $return_value['product_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";
        $return_value['product_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $filename . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                '<div class="wpie-log-delete-action wpie-export-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $filename . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                '</div>';
        $return_value['data'] = array('', $filename, $new_values['create_date'], $data_action);
        echo json_encode($return_value);

        die();
    }

    function get_product_export_fields_data() {

        $csv_data = "";

        $product_field_list = $this->get_updated_product_fields();

        $count = 0;

        foreach ($product_field_list['product_fields'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        return $csv_data;
    }

    function wpie_update_product_csv() {

        if (session_id() == '') {
            session_start();
        }

        $file_name = isset($_POST['file_name']) ? stripslashes($_POST['file_name']) : "";

        $product_query = isset($_POST['product_query']) ? stripslashes($_POST['product_query']) : "";

        $start_product = isset($_POST['start_product']) ? $_POST['start_product'] : "";

        $current_product_id = isset($_POST['current_product_id']) ? $_POST['current_product_id'] : "";

        $child_product = isset($_POST['child_product']) ? $_POST['child_product'] : 0;

        $start_child = isset($_POST['start_child']) ? $_POST['start_child'] : "";

        $product_offset = isset($_POST['product_offset']) ? $_POST['product_offset'] : "";

        $product_limit = isset($_POST['product_limit']) ? $_POST['product_limit'] : "";

        $process_status = isset($_POST['status']) ? $_POST['status'] : "";

        $wpie_export_separator = (isset($_POST['wpie_export_separator']) && $_POST['wpie_export_separator'] != "") ? $_POST['wpie_export_separator'] : ",";

        $return_value = array();

        if (($start_product == "" || $start_product == 0 ) && $process_status == "pending") {

            $_SESSION['product_parent_stat'] = "";
        }

        $return_value['product_limit'] = $product_limit;

        if ($file_name != "" && file_exists($file_name)) {

            $fh = @fopen($file_name, 'a+');

            if ($child_product == 0) {

                if (isset($_SESSION['product_parent_stat']) && $_SESSION['product_parent_stat'] == "completed") {
                    $product_list_data = array();
                } else {
                    $product_list_data = $this->get_filter_product_data($product_query, $start_product, 0, $product_offset, $product_limit, 0);
                }
            } else {
                $product_list_data = $this->get_filter_product_child_data($current_product_id, $start_child);

                $product_list_data['current_product_id'] = $current_product_id;

                $product_list_data['start_product'] = $start_product;

                $product_list_data['child_product'] = $child_product;

                $product_list_data['product_limit'] = $product_limit;
            }
            $product_field_list = $this->get_updated_product_fields();

            $return_value['current_product_id'] = isset($product_list_data['current_product_id']) ? $product_list_data['current_product_id'] : 0;

            $return_value['start_product'] = isset($product_list_data['start_product']) ? $product_list_data['start_product'] : 0;

            $return_value['start_child'] = isset($product_list_data['start_child']) ? $product_list_data['start_child'] : 0;

            $return_value['child_product'] = isset($product_list_data['child_product']) ? $product_list_data['child_product'] : 0;

            $product_list_data['get_child'] = isset($product_list_data['get_child']) ? $product_list_data['get_child'] : 0;

            if (!empty($product_list_data['product_data'])) {

                if ($_SESSION['product_parent_stat'] != "completed" && ( $product_list_data['status'] == "completed" || (isset($product_list_data['product_limit']) && $product_list_data['product_limit'] == 0 && $product_list_data['get_child'] != 1) )) {
                    $return_value['status'] = 'completed';
                } else {

                    $return_value['status'] = 'pending';
                }

                $return_value['product_limit'] = isset($product_list_data['product_limit']) ? $product_list_data['product_limit'] : "";

                foreach ($product_list_data['product_data'] as $product_info) {

                    $data_result = array();

                    foreach ($product_field_list['product_fields'] as $field_data) {

                        if ($field_data['field_display'] == 1) {

                            $output = "";

                            $temp_data = $field_data['field_key'];

                            if ($field_data['field_key'] == 'images') {

                                foreach ($product_info[$temp_data] as $product_images) {

                                    $output .= $product_images['src'] . ',';
                                }

                                $output = substr($output, 0, -1);
                            } else {

                                $output = isset($product_info[$temp_data]) ? $product_info[$temp_data] : "";
                            }

                            $data_result[] = $output;
                        }
                    }

                    @fputcsv($fh, $data_result, $wpie_export_separator);
                }
            } else if ($product_list_data['current_product_id'] > 0 && $product_list_data['child_product'] > 0) {

                $return_value['child_product'] = 0;

                $return_value['start_child'] = 0;

                $return_value['status'] = 'pending';
            } else {

                $return_value['status'] = 'completed';
            }
            @fclose($fh);

            $return_value['message'] = 'success';

            $return_value['product_query'] = $product_query;

            $return_value['file_name'] = $file_name;
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_upload_csv_file() {

        $file = $_FILES['async-upload'];

        $uploaded_file = wp_handle_upload($file, array('test_form' => true, 'action' => 'wpie_upload_csv_file', 'test_type' => false, 'ext' => "csv", 'type' => 'text/csv'));

        $current_time = time();

        if ($uploaded_file && !isset($uploaded_file['error'])) {
            $return_value['file_status'] = "success";

            if (isset($_POST['chunks']) && isset($_POST['chunk']) && preg_match('/^[0-9]+$/', $_POST['chunk'])) {
                $final_file = basename($_POST['name']);
                rename($uploaded_file['file'], WPIE_UPLOAD_DIR . '/' . $final_file . '.' . $_POST['chunk'] . '.csv.tmp');
                $uploaded_file['file'] = WPIE_UPLOAD_DIR . '/' . $final_file . '.' . $_POST['chunk'] . '.csv.tmp';

                // Final chunk? If so, then stich it all back together
                if ($_POST['chunk'] == $_POST['chunks'] - 1) {
                    if ($wh = fopen(WPIE_UPLOAD_DIR . '/' . $current_time . "_" . $final_file, 'wb')) {
                        for ($i = 0; $i < $_POST['chunks']; $i++) {
                            $rf = WPIE_UPLOAD_DIR . '/' . $final_file . '.' . $i . '.csv.tmp';
                            if ($rh = fopen($rf, 'rb')) {
                                while ($line = fread($rh, 32768))
                                    fwrite($wh, $line);
                                fclose($rh);
                                @unlink($rf);
                            }
                        }
                        fclose($wh);
                        $uploaded_file['file'] = WPIE_UPLOAD_DIR . '/' . $current_time . "_" . $final_file;
                    }
                }
            }
        } else {
            $return_value['file_status'] = "fail";
        }

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['file_url'] = $uploaded_file['file'];

        echo json_encode($return_value);

        die();
    }

    function wpie_save_product_fields() {

        $old_product_fields = $this->get_updated_product_fields();

        $new_fields = array();

        foreach ($old_product_fields as $product_fields_key => $product_fields_data) {

            foreach ($product_fields_data as $key => $value) {

                $new_fields[$product_fields_key][$key]['field_key'] = $value['field_key'];

                $new_fields[$product_fields_key][$key]['field_display'] = isset($_POST['wpie_' . $value['field_key'] . '_field_check']) ? $_POST['wpie_' . $value['field_key'] . '_field_check'] : "";

                $new_fields[$product_fields_key][$key]['field_title'] = $value['field_title'];

                $new_fields[$product_fields_key][$key]['field_value'] = $value['field_title']; //isset($_POST['wpie_' . $value['field_key'] . '_field']) ? $_POST['wpie_' . $value['field_key'] . '_field'] : "";
            }
        }

        $new_fields_data = maybe_serialize($new_fields);

        update_option('wpie_product_fields', $new_fields_data);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['message_content'] = __('Changes Saved Successfully.', WPIE_TEXTDOMAIN);

        $return_value['preview_fields'] = $this->get_product_preview_fields();

        echo json_encode($return_value);

        die();
    }

    function get_product_preview_fields() {

        $product_fields = $this->get_updated_product_fields();

        $preview_fields_data = '<table class="wpie-product-filter-data wpie-datatable table table-striped table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>';

        foreach ($product_fields as $new_product_fields) {
            foreach ($new_product_fields as $product_fields_data)
                if ($product_fields_data['field_display'] == 1) {
                    $preview_fields_data .= '<th>' . $product_fields_data['field_title'] . '</th>';
                }
        }

        $preview_fields_data .="   </tr>

                </thead>
            </table>";
        return $preview_fields_data;
    }

    function wpie_get_product_export_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where `export_log_file_type` = 'csv' or `export_log_file_type` = 'export' and `export_log_data`='Product' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_get_product_import_log() {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "wpie_export_log where `export_log_file_type` = 'import' and `export_log_data`='Product' ORDER BY `export_log_id` DESC");

        return $results;
    }

    function wpie_create_product_preview() {

        $return_value = array();

        $product_query_data = $this->wpie_create_product_filter_query($_POST);

        $return_value['message'] = 'success';

        $return_value['product_query'] = $product_query_data;

        $return_value['product_offset'] = isset($_POST['wpie_offset_records']) ? $_POST['wpie_offset_records'] : "";

        $return_value['product_limit'] = isset($_POST['wpie_total_records']) ? $_POST['wpie_total_records'] : "";

        $return_value['total_products'] = $this->get_total_records_count(json_decode($product_query_data, 1));

        echo json_encode($return_value);

        die();
    }

    function get_total_records_count($query_args) {

        // $query_args['posts_per_page'] = -1;

        $query_args['fields'] = "ids";

        $product_results = new WP_Query($query_args);

        return count($product_results->get_posts());
    }

    function wpie_execute_data_query() {

        $query_args = isset($_POST['data_query']) ? json_decode(stripslashes($_POST['data_query']), 1) : "";

        //$query_args['posts_per_page'] = -1;

        $query_args['fields'] = "ids";

        $product_results = new WP_Query($query_args);

        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['total_results'] = count($product_results->get_posts());

        echo json_encode($return_value);

        die();
    }

    function get_product_export_data($wpie_data = array()) {


        $csv_data = "";

        $product_field_list = $this->get_updated_product_fields();

        $product_list_data = $this->get_filter_product($wpie_data);

        $count = 0;

        foreach ($product_field_list['product_fields'] as $field_data) {
            if ($field_data['field_display'] == 1) {

                $csv_data[$count][] = $field_data['field_value'];
            }
        }

        foreach ($product_list_data as $product_info) {
            $count++;

            $data_result = array();

            foreach ($product_field_list['product_fields'] as $field_data) {


                if ($field_data['field_display'] == 1) {

                    $output = "";
                    $temp_data = $field_data['field_key'];
                    if ($field_data['field_key'] == 'images') {
                        foreach ($product_info[$temp_data] as $product_images) {
                            $output .= $product_images['src'] . ',';
                        }
                        $output = substr($output, 0, -1);
                    } else {
                        $output = isset($product_info[$temp_data]) ? $product_info[$temp_data] : "";
                    }

                    $data_result[] = $output;
                }
            }

            $csv_data[$count] = $data_result;
        }

        return $csv_data;
    }

    function get_filter_product($wpie_data) {

        $product_categories = isset($wpie_data['wpie_product_category']) ? $wpie_data['wpie_product_category'] : array();

        $product_ids = isset($wpie_data['wpie_product_ids']) ? $wpie_data['wpie_product_ids'] : array();

        $author_ids = isset($wpie_data['wpie_product_author_ids']) ? $wpie_data['wpie_product_author_ids'] : array();

        $total_records = isset($wpie_data['wpie_total_records']) ? $wpie_data['wpie_total_records'] : "";

        $offset_records = isset($wpie_data['wpie_offset_records']) ? $wpie_data['wpie_offset_records'] : "";

        $temp_start_date = isset($wpie_data['wpie_start_date']) ? $wpie_data['wpie_start_date'] : "";

        $temp_end_date = isset($wpie_data['wpie_end_date']) ? $wpie_data['wpie_end_date'] : "";

        $end_date = "";

        $start_date = "";

        if ($temp_start_date != "") {
            $temp_start_date = explode('-', $temp_start_date);

            $start_date = $temp_start_date[2] . '-' . $temp_start_date[0] . '-' . $temp_start_date[1];
        }
        if ($temp_end_date != "") {
            $temp_end_date = explode('-', $temp_end_date);

            $end_date = $temp_end_date[2] . '-' . $temp_end_date[0] . '-' . $temp_end_date[1];
        }


        $query_args = array(
            'posts_per_page' => -1,
            'post_type' => 'product',
            'orderby' => 'ID',
            'order' => 'ASC',
                //'fields' 			=> 'ids',
        );

        if (!empty($product_categories)) {
            $query_args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $product_categories
                ),
            );
        }
        if (!empty($product_ids)) {
            $query_args['post__in'] = $product_ids;
        }

        if ($total_records != "" && $total_records > 0) {
            $query_args['posts_per_page'] = $total_records;

            if ($offset_records != "" && $offset_records >= 0) {
                $query_args['offset'] = $offset_records;
            }
        }

        if ($end_date != "" || $start_date != "") {
            $date_data = array();

            if ($end_date != "") {
                $date_data['before'] = $end_date . " 23:59:59";
            }

            if ($start_date != "") {
                $date_data['after'] = $start_date . " 00:00:00";
            }

            $date_data['inclusive'] = true;

            $query_args['date_query'] = array($date_data);
        }

        $product_results = new WP_Query($query_args);

        $product_data_list = array();
        $product_flag = 0;
        while ($product_results->have_posts()) {
            $product_results->the_post();
            global $product;

            if (!is_object($product) || $product_flag > 0) {
                if (function_exists(get_product)) {
                    $product = get_product($product_results->post);
                } else {
                    $product = new WC_product($product_results->post);
                }
                $product_flag++;
            }
            /* wp_set_object_terms($product_results->post->ID,$product_info['_product_type'],'product_type');	 */
            $product_data_list[] = array(
                'post_title' => @$product->post->post_title,
                'id' => @(int) $product->is_type('variation') ? $product->get_variation_id() : $product->id,
                'created_at' => @$product->get_post_data()->post_date_gmt,
                'updated_at' => @$product->get_post_data()->post_modified_gmt,
                '_product_type' => @$product->product_type,
                'post_status' => @$product->get_post_data()->post_status,
                '_downloadable' => @$product->is_downloadable() ? 'yes' : 'no',
                '_virtual' => @$product->is_virtual() ? 'yes' : 'no',
                'permalink' => @$product->get_permalink(),
                '_sku' => @$product->get_sku(),
                '_price' => @$product->get_price(),
                '_regular_price' => @$product->get_regular_price(),
                '_sale_price' => @$product->get_sale_price() ? $product->get_sale_price() : null,
                'price_html' => @$product->get_price_html(),
                'taxable' => @$product->is_taxable() ? 'yes' : 'no',
                '_tax_status' => @$product->get_tax_status(),
                '_tax_class' => @$product->get_tax_class(),
                '_manage_stock' => @$product->managing_stock() ? 'yes' : 'no',
                '_stock' => @$product->get_stock_quantity(),
                '_stock_status' => @$product->is_in_stock() ? 'instock' : "outofstock",
                'backorders_allowed' => @$product->backorders_allowed(),
                '_backorders' => @$product->backorders,
                '_sold_individually' => @$product->is_sold_individually() ? 'yes' : 'no',
                'purchaseable' => @$product->is_purchasable() ? 'yes' : 'no',
                '_featured' => @$product->is_featured() ? 'yes' : 'no',
                '_visibility' => @$product->visibility,
                'catalog_visibility' => @$product->visibility,
                'on_sale' => @$product->is_on_sale() ? 'yes' : 'no',
                '_product_url' => @$product->is_type('external') ? $this->wpie_get_product_url($product) : '',
                '_button_text' => @$product->is_type('external') ? $this->wpie_get_button_text($product) : '',
                '_weight' => @$product->get_weight() ? wc_format_decimal($product->get_weight(), 2) : null,
                'dimensions' => @array(
            'length' => $product->length,
            'width' => $product->width,
            'height' => $product->height,
            'unit' => get_option('woocommerce_dimension_unit'),
                ),
                '_length' => @$product->length,
                '_width' => @$product->width,
                '_height' => @$product->height,
                'unit' => @get_option('woocommerce_dimension_unit'),
                'shipping_required' => @$product->needs_shipping() ? 'yes' : 'no',
                'shipping_taxable' => @$product->is_shipping_taxable() ? 'yes' : 'no',
                'product_shipping_class' => @$product->get_shipping_class(),
                'shipping_class_id' => @( 0 !== $product->get_shipping_class_id() ) ? $product->get_shipping_class_id() : null,
                'post_content' => @do_shortcode($product->get_post_data()->post_content),
                'post_excerpt' => @$product->get_post_data()->post_excerpt,
                'comment_status' => @$product->get_post_data()->comment_status,
                'average_rating' => @wc_format_decimal($product->get_average_rating(), 2),
                'rating_count' => @(int) $product->get_rating_count(),
                'related_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', array_values($product->get_related())))),
                '_upsell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $product->get_upsells()))),
                '_crosssell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $product->get_cross_sells()))),
                'parent_id' => @$product->post->post_parent,
                'product_cat' => @$this->wpie_get_categories_by_producy_id($product->id), //implode(',',wp_get_post_terms( $product->id, 'product_cat', array( 'fields' => 'names' ) )),
                'product_tag' => @implode(',', wp_get_post_terms($product->id, 'product_tag', array('fields' => 'names'))),
                'images' => @$this->wpie_get_images($product),
                'has_post_thumbnail' => @has_post_thumbnail($product->id) ? 'yes' : 'no',
                'featured_src' => @wp_get_attachment_url(get_post_thumbnail_id($product->is_type('variation') ? $product->variation_id : $product->id )),
                '_product_attributes' => @$this->wpie_get_attributes($product),
                '_product_custom_fields' => @maybe_serialize(get_post_custom($product->id)),
                'downloads' => @$this->wpie_get_downloads($product),
                '_downloadable_files' => @maybe_serialize($product->get_files()),
                '_download_limit' => @$product->download_limit,
                '_download_expiry' => @$product->download_expiry,
                '_download_type' => @$product->download_type,
                '_purchase_note' => @do_shortcode(wp_kses_post($product->purchase_note)),
                'total_sales' => @metadata_exists('post', $product->id, 'total_sales') ? (int) get_post_meta($product->id, 'total_sales', true) : 0,
                'ping_status' => @$product->get_post_data()->ping_status ? 'open' : 'close',
                'variations' => @array(),
                '_variation_description' => "",
                'menu_order' => @$product->get_post_data()->menu_order,
                'post_parent' => @$product->get_post_data()->post_parent,
            );

            if ($product->is_type('variable') && $product->has_child()) {


                foreach ($product->get_children() as $child_id) {

                    $variation = $product->get_child($child_id);

                    if (!$variation->exists()) {
                        continue;
                    }
                    $variation = $product->get_child($child_id);

                    if ($variation->product_type == 'variation') {
                        $product_data_list[] = array(
                            'post_title' => @$variation->post->post_title,
                            'id' => @$variation->get_variation_id(),
                            'created_at' => @$variation->get_post_data()->post_date_gmt,
                            'updated_at' => @$variation->get_post_data()->post_modified_gmt,
                            '_product_type' => @$variation->product_type,
                            'post_status' => @$variation->get_post_data()->post_status,
                            '_downloadable' => @$variation->is_downloadable() ? 'yes' : 'no',
                            '_virtual' => @$variation->is_virtual() ? 'yes' : 'no',
                            'permalink' => @$variation->get_permalink(),
                            '_sku' => @$variation->get_sku(),
                            '_price' => @$variation->get_price(),
                            '_regular_price' => @$variation->get_regular_price(),
                            '_sale_price' => @$variation->get_sale_price() ? $variation->get_sale_price() : null,
                            'price_html' => @$variation->get_price_html(),
                            'taxable' => @$variation->is_taxable() ? 'yes' : 'no',
                            '_tax_status' => @$variation->get_tax_status(),
                            '_tax_class' => @$variation->get_tax_class(),
                            '_manage_stock' => @$variation->managing_stock() ? 'yes' : 'no',
                            '_stock' => @$variation->get_stock_quantity(),
                            '_stock_status' => @$variation->is_in_stock() ? 'instock' : "outofstock",
                            'backorders_allowed' => @$variation->backorders_allowed() ? 'yes' : 'no',
                            '_backorders' => @$variation->backorders,
                            '_sold_individually' => @$variation->is_sold_individually() ? 'yes' : 'no',
                            'purchaseable' => @$variation->is_purchasable() ? 'yes' : 'no',
                            '_featured' => @$variation->is_featured() ? 'yes' : 'no',
                            '_visibility' => @$variation->visibility, //$variation->variation_is_visible()?'yes':'no',
                            'catalog_visibility' => @$variation->visibility,
                            'on_sale' => @$variation->is_on_sale() ? 'yes' : 'no',
                            '_product_url' => @$variation->is_type('external') ? $this->wpie_get_product_url($variation) : '',
                            '_button_text' => @$variation->is_type('external') ? $this->wpie_get_button_text($variation) : '',
                            '_weight' => @$variation->get_weight() ? wc_format_decimal($variation->get_weight(), 2) : null,
                            'dimensions' => @array(
                        'length' => $variation->length,
                        'width' => $variation->width,
                        'height' => $variation->height,
                        'unit' => get_option('woocommerce_dimension_unit'),
                            ),
                            '_length' => @$variation->length,
                            '_width' => @$variation->width,
                            '_height' => @$variation->height,
                            'unit' => @get_option('woocommerce_dimension_unit'),
                            'product_shipping_class' => @$variation->get_shipping_class(),
                            'shipping_class_id' => @( 0 !== $variation->get_shipping_class_id() ) ? $variation->get_shipping_class_id() : null,
                            'shipping_required' => @$variation->needs_shipping() ? 'yes' : 'no',
                            'shipping_taxable' => @$variation->is_shipping_taxable() ? 'yes' : 'no',
                            'post_content' => @do_shortcode($variation->get_post_data()->post_content),
                            'post_excerpt' => @$variation->get_post_data()->post_excerpt,
                            'comment_status' => @$variation->get_post_data()->comment_status,
                            'average_rating' => @wc_format_decimal($variation->get_average_rating(), 2),
                            'rating_count' => @(int) $variation->get_rating_count(),
                            'related_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', array_values($variation->get_related())))),
                            '_upsell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $variation->get_upsells()))),
                            '_crosssell_ids' => @$this->wpie_get_sku_by_id(implode(',', array_map('absint', $variation->get_cross_sells()))),
                            'parent_id' => @$variation->post->post_parent,
                            'product_cat' => @$this->wpie_get_categories_by_producy_id($variation->id), //implode(',',wp_get_post_terms( $variation->id, 'product_cat', array( 'fields' => 'names' ) )),
                            'product_tag' => @implode(',', wp_get_post_terms($variation->id, 'product_tag', array('fields' => 'names'))),
                            'images' => @$this->wpie_get_images($variation),
                            'has_post_thumbnail' => @has_post_thumbnail($variation->get_variation_id()) ? 'yes' : 'no',
                            'featured_src' => @wp_get_attachment_url(get_post_thumbnail_id($variation->is_type('variation') ? $variation->variation_id : $variation->id )),
                            '_product_attributes' => @$this->wpie_get_attributes($variation),
                            '_product_custom_fields' => @maybe_serialize(get_post_custom($child_id)),
                            'downloads' => @$this->wpie_get_downloads($variation),
                            '_downloadable_files' => @maybe_serialize($variation->get_files()),
                            '_download_limit' => @$variation->download_limit,
                            '_download_expiry' => @$variation->download_expiry,
                            '_download_type' => @$variation->download_type,
                            '_purchase_note' => @do_shortcode(wp_kses_post($variation->purchase_note)),
                            'total_sales' => @metadata_exists('post', $variation->id, 'total_sales') ? (int) get_post_meta($variation->id, 'total_sales', true) : 0,
                            'ping_status' => @$variation->get_post_data()->ping_status ? 'open' : 'close',
                            'variations' => @array(),
                            '_variation_description' => @get_post_meta($variation->id, '_variation_description', true),
                            'menu_order' => @$variation->get_post_data()->menu_order,
                            'post_parent' => @(int) $product->is_type('variation') ? $product->get_variation_id() : $product->id,
                        );
                    }
                }
            }
        }

        wp_reset_postdata();
        return $product_data_list;
    }

    function wpie_import_products_percentage() {

        if (session_id() == '') {
            session_start();
        }
        $return_value = array();

        $return_value['message'] = 'success';

        $return_value['product_offset'] = isset($_SESSION['product_old_new_ids']) ? count($_SESSION['product_old_new_ids']) : 0;

        $return_value['total_records'] = isset($_SESSION['product_total_records']) ? $_SESSION['product_total_records'] : 0;

        echo json_encode($return_value);

        die();
    }

    function wpie_import_products() {

        global $wpdb, $wpie_fatch_import_records;

        $return_value = array();

        $return_value['message'] = 'error';

        $file_url = isset($_POST['wpie_import_file_url']) ? $_POST['wpie_import_file_url'] : "";

        $file_path_data = isset($_POST['wpie_csv_upload_file']) ? $_POST['wpie_csv_upload_file'] : "";

        $process_status = isset($_POST['status']) ? $_POST['status'] : "pending";

        $wpie_import_determinator = (isset($_POST['wpie_import_determinator']) || trim($_POST['wpie_import_determinator']) != "") ? $_POST['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($_POST['wpie_data_update_option']) ? $_POST['wpie_data_update_option'] : "product_sku";

        $product_field_list = $this->get_updated_product_fields();

        if (session_id() == '') {
            session_start();
        }
        if ($process_status == "start") {

            $_SESSION['group_ids'] = array();

            $_SESSION['product_total_records'] = 0;

            $_SESSION['group_child_id'] = array();

            $_SESSION['variable_ids'] = array();

            $_SESSION['imported_ids'] = array();

            $_SESSION['wpie_deleted_ids'] = array();

            $_SESSION['product_id_list'] = array();

            $_SESSION['product_old_new_ids'] = array();

            $_SESSION['product_old_new_id_list'] = array();
        }
        if ($process_status == 'import_completed') {


            if (!empty($_SESSION['wpie_deleted_ids'])) {
                $temp_count = 0;
                foreach ($_SESSION['wpie_deleted_ids'] as $key => $value) {

                    @wp_delete_post($key, true);
                    unset($_SESSION['wpie_deleted_ids'][$key]);
                    $temp_count++;
                    if ($temp_count == 5) {
                        break;
                    }
                }
            }

            if (empty($_SESSION['wpie_deleted_ids'])) {
                $return_value['status'] = "delete_completed";
            } else {
                $return_value['status'] = "import_completed";
            }
            $return_value['message'] = 'success';
        }

        if ($process_status == 'delete_completed') {
            if ((!empty($_SESSION['group_child_id'])) && (!empty($_SESSION['group_child_id']))) {
                $temp_count = 0;
                foreach ($_SESSION['group_child_id'] as $key => $value) {
                    $post_data = array('ID' => $key, 'post_parent' => $_SESSION['group_child_id'][$value]);
                    wp_update_post($post_data);
                    unset($_SESSION['group_child_id'][$key]);
                    if ($temp_count == 3) {
                        break;
                    }
                }
            }
            if (empty($_SESSION['group_child_id'])) {
                $return_value['status'] = "completed";
                $return_value['message_text'] = __('Data Successfully Imported', WPIE_TEXTDOMAIN);
            } else {
                $return_value['status'] = "delete_completed";
            }
            $return_value['message'] = 'success';
            echo json_encode($return_value);
            die();
        }

        $file_path = "";

        if ($file_path_data != "") {
            $file_path = $file_path_data;
        } else if ($file_url != "") {
            $file_path = $file_url;
        }

        if ($file_path != "") {

            if ($process_status == "start") {

                $new_file = pathinfo($file_path);

                $new_values = array();

                $new_values['export_log_file_type'] = 'import';
                $new_values['export_log_file_name'] = $new_file['basename'];
                $new_values['export_log_data'] = 'Product';
                $new_values['create_date'] = current_time('mysql');

                $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);
                $new_log_id = $wpdb->insert_id;

                $data_action = '<div class="wpie-log-action-wrapper">
                                            <div class="wpie-log-download-action "  file_name="' . $new_values['export_log_file_name'] . '">' . __('Download', WPIE_TEXTDOMAIN) . '</div>' .
                        '<div class="wpie-log-delete-action wpie-import-log-delete-action" log_id="' . $new_log_id . '" file_name="' . $new_values['export_log_file_name'] . '">' . __('Delete', WPIE_TEXTDOMAIN) . '</div>' .
                        '</div>';
                $return_value['data'] = array('', substr($new_values['export_log_file_name'], 11), $new_values['create_date'], $data_action);
            }

            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($process_status == "error" || $process_status == "start") {
                $process_status = "pending";
            }

            if ($fh !== FALSE) {

                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'SKU') {
                                $csv_temp_count++;
                                break;
                            }
                        }
                        if ($csv_temp_count == 0) {
                            break;
                        }
                    }

                    $import_data_temp[] = $new_line;

                    $csv_temp_count++;
                }
                if ($csv_temp_count == 0) {
                    if ($wpie_import_determinator == ",") {
                        $wpie_import_determinator = ";";
                    } else {
                        $wpie_import_determinator = ",";
                    }
                    @rewind($fh);
                    while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {

                        if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                            foreach ($new_line as $csv_new_column) {
                                if ($csv_new_column == 'Id' || $csv_new_column == 'SKU') {
                                    $csv_temp_count++;
                                    break;
                                }
                            }
                            if ($csv_temp_count == 0) {
                                break;
                            }
                        }
                        $import_data_temp[] = $new_line;

                        $csv_temp_count++;
                    }
                }
                fclose($fh);

                // $import_data_temp[0][0] = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $import_data_temp[0][0]);
                $fields_title_array = $import_data_temp[0];
                unset($import_data_temp[0]);
                $count = 0;
                $total_records = count($import_data_temp);
                $_SESSION['product_total_records'] = $total_records;
                foreach ($import_data_temp as $data) {
                    //if (array_keys($_SESSION['product_old_new_ids'])) {
                    $temp_count = 0;
                    foreach ($data as $key => $value) {
                        $temp_key = $fields_title_array[$temp_count];
                        $import_data[$count][$temp_key] = $value;

                        $temp_count++;
                    }
                    if (!isset($import_data[0]['Id'])) {

                        if (isset($import_data[0]['SKU'])) {
                            $import_data[$count]['Id'] = $count + 999999;
                        } else {
                            $import_data = array();
                            $return_value['status'] = "error_completed";
                            $return_value['message'] = "success";
                            $return_value['message_text'] = __('File or Seprator is invalid.', WPIE_TEXTDOMAIN);
                            echo json_encode($return_value);
                            die();
                        }
                    }
                    //  }
                    $count++;
                }

                if ($total_records <= @count($_SESSION['product_old_new_ids'])) {
                    $return_value['status'] = "import_completed";
                } else {
                    $return_value['status'] = "pending";
                }
                $return_value['total_records'] = $total_records;
                $return_value['message'] = 'success';
            } else {

                $return_value['message_text'] = __('Could not open file.', WPIE_TEXTDOMAIN);
            }

            if (!empty($import_data)) {


                $wpie_product_create_method = isset($_POST['wpie_product_create_method']) ? $_POST['wpie_product_create_method'] : "";

                $import_type = 'normal';

                $product_updated_data = $this->wpie_create_new_product($import_data, $wpie_product_create_method, $import_type, $wpie_data_update_option);

                $return_value['product_offset'] = @count($_SESSION['product_old_new_ids']);

                // $return_value['data'] = @$product_updated_data['data'];
                // $return_value['product_log'] = $this->set_product_import_errors($product_updated_data['product_log']);
            }
        }

        echo json_encode($return_value);

        die();
    }

    function wpie_create_new_product($product_data = array(), $product_create_method = "", $import_type = 'normal', $wpie_data_update_option = "product_sku") {

        if (session_id() == '') {
            session_start();
        }

        $wpie_product_log = array();

        $product_all_data = array();

        // $product_create_method = isset($_POST['wpie_product_create_method']) ? $_POST['wpie_product_create_method'] : "";

        $temp_offset = 0;

        try {

            foreach ($product_data as $product_info) {

                $existing_product_id = "";

                if (isset($product_info['Id'])) {

                    $product_info['Id'] = intval($product_info['Id']);

                    $old_product_id = $product_info['Id'];

                    if (isset($_SESSION['product_old_new_ids'][$old_product_id]) && $_SESSION['product_old_new_ids'][$old_product_id] != "") {
                        continue;
                    }
                    if (isset($_SESSION['product_old_new_id_list'][$old_product_id]) && $_SESSION['product_old_new_id_list'][$old_product_id] != "") {
                        $existing_product_id == $_SESSION['product_old_new_id_list'][$old_product_id];
                    }

                    if ($existing_product_id == "" && $wpie_data_update_option == "product_id") {

                        if (get_post_status($old_product_id) === false) {
                            
                        } else {
                            $existing_product_id = $old_product_id;
                        }
                    }
                }


                if (isset($product_info['Product Type']) || $product_info['Product Type'] != "") {
                    $product_info['Product Type'] = $product_info['Product Type'];
                } else {
                    $product_info['Product Type'] = "simple";
                }


                if ($existing_product_id == "" && isset($product_info['SKU']) && $product_info['SKU'] != "" && ($product_create_method == 'update_product' || $product_create_method == 'skip_product')) {
                    if ($product_info['Product Type'] == 'variation') {
                        $current_post_type = 'product_variation';
                    } else {
                        $current_post_type = 'product';
                    }
                    $existing_post_query = array(
                        'posts_per_page' => 1,
                        'meta_query' => array(
                            array(
                                'key' => '_sku',
                                'value' => $product_info['SKU'],
                                'compare' => '='
                            ),
                        ),
                        'post_type' => $current_post_type,
                        'fields' => 'ids',
                    );

                    $existing_product = get_posts($existing_post_query);

                    if (!empty($existing_product) && $existing_product[0] != "" && $existing_product[0] > 0) {

                        $existing_product_id = $existing_product[0];
                    }
                }
                if ($product_create_method == 'skip_product' && $existing_product_id != "" && $existing_product_id > 0 && $product_info['Product Type'] != 'variation') {
                    if (isset($product_info['Id'])) {
                        $old_temp_product_id = $product_info['Id'];
                        $_SESSION['product_old_new_ids'][$old_temp_product_id] = $existing_product_id;

                        $_SESSION['product_old_new_id_list'][$old_temp_product_id] = $existing_product_id;
                    }
                    $_SESSION['imported_ids'][] = $existing_product_id;
                    $_SESSION['product_id_list'][] = $existing_product_id;

                    $wpie_product_log[] = sprintf(__('Product #%s already Exist.', WPIE_TEXTDOMAIN), $existing_product_id);

                    continue;
                }


                if ($product_info['Product Type'] == 'simple' || $product_info['Product Type'] == 'external') {
                    //try to find a product with a matching SKU

                    if ($existing_product_id == "") {


                        $product_query = array(
                            'post_title' => isset($product_info['Product Name']) ? $product_info['Product Name'] : "Untitled",
                            'post_type' => 'product',
                            'post_status' => isset($product_info['Product Status']) ? $product_info['Product Status'] : "publish",
                            'post_content' => isset($product_info['Description']) ? $product_info['Description'] : "",
                            'ping_status' => isset($product_info['Ping Status']) ? $product_info['Ping Status'] : "open",
                            'comment_status' => isset($product_info['Comment Status']) ? $product_info['Comment Status'] : "closed",
                            'post_excerpt' => isset($product_info['Short Description']) ? $product_info['Short Description'] : "",
                            'menu_order' => isset($product_info['Menu Order']) ? $product_info['Menu Order'] : "0",
                        );
                        if (isset($product_info['Product Parent id']) && $product_info['Product Parent id'] > 0 && in_array($product_info['Product Parent id'], array_keys($_SESSION['group_ids']))) {
                            $product_query['post_parent'] = $_SESSION['group_ids'][$product_info['Product Parent id']];
                        }
                        $new_product_id = wp_insert_post($product_query, false);
                    } else {

                        $product_query = array();

                        if (isset($product_info['Product Name']) && $product_info['Product Name'] != "") {
                            $product_query['post_title'] = $product_info['Product Name'];
                        }
                        if (isset($product_info['Product Status']) && $product_info['Product Status'] != "") {
                            $product_query['post_status'] = $product_info['Product Status'];
                        } else {
                            $product_query['post_status'] = "publish";
                        }

                        if (isset($product_info['Description']) && $product_info['Description'] != "") {
                            $product_query['post_content'] = $product_info['Description'];
                        }
                        if (isset($product_info['Ping Status']) && $product_info['Ping Status'] != "") {
                            $product_query['ping_status'] = $product_info['Ping Status'];
                        }
                        if (isset($product_info['Comment Status']) && $product_info['Comment Status'] != "") {
                            $product_query['comment_status'] = $product_info['Comment Status'];
                        }
                        if (isset($product_info['Short Description']) && $product_info['Short Description'] != "") {
                            $product_query['post_excerpt'] = $product_info['Short Description'];
                        }
                        if (isset($product_info['Menu Order']) && $product_info['Menu Order'] != "") {
                            $product_query['menu_order'] = $product_info['Menu Order'];
                        }

                        if (isset($product_info['Product Parent id']) && $product_info['Product Parent id'] > 0 && in_array($product_info['Product Parent id'], array_keys($_SESSION['group_ids']))) {
                            $product_query['post_parent'] = $_SESSION['group_ids'][$product_info['Product Parent id']];
                        }

                        $product_query['ID'] = $existing_product_id;

                        $new_product_id = wp_update_post($product_query, false);
                    }

                    if ($new_product_id > 0) {
                        $wpie_product_log[] = $this->wpie_set_product_attributes($product_info, $new_product_id, $existing_product_id);

                        if (isset($product_info['Product Parent id']) && $product_info['Product Parent id'] > 0 && !(in_array($product_info['Product Parent id'], array_keys($_SESSION['group_ids'])))) {
                            $_SESSION['group_child_id'][$new_product_id] = $product_info['Product Parent id'];
                        }
                    }
                } else if ($product_info['Product Type'] == 'grouped') {

                    if ($existing_product_id == "") {
                        $product_query = array(
                            'post_title' => isset($product_info['Product Name']) ? $product_info['Product Name'] : "Untitled",
                            'post_type' => 'product',
                            'post_status' => isset($product_info['Product Status']) ? $product_info['Product Status'] : "publish",
                            'post_content' => isset($product_info['Description']) ? $product_info['Description'] : "",
                            'ping_status' => isset($product_info['Ping Status']) ? $product_info['Ping Status'] : "open",
                            'comment_status' => isset($product_info['Comment Status']) ? $product_info['Comment Status'] : "closed",
                            'post_excerpt' => isset($product_info['Short Description']) ? $product_info['Short Description'] : "",
                            'menu_order' => isset($product_info['Menu Order']) ? $product_info['Menu Order'] : "0",
                        );
                        $new_product_id = wp_insert_post($product_query, false);
                    } else {
                        $product_query = array();

                        if (isset($product_info['Product Name']) && $product_info['Product Name'] != "") {
                            $product_query['post_title'] = $product_info['Product Name'];
                        }
                        if (isset($product_info['Product Status']) && $product_info['Product Status'] != "") {
                            $product_query['post_status'] = $product_info['Product Status'];
                        } else {
                            $product_query['post_status'] = "publish";
                        }
                        if (isset($product_info['Description']) && $product_info['Description'] != "") {
                            $product_query['post_content'] = $product_info['Description'];
                        }
                        if (isset($product_info['Ping Status']) && $product_info['Ping Status'] != "") {
                            $product_query['ping_status'] = $product_info['Ping Status'];
                        }
                        if (isset($product_info['Comment Status']) && $product_info['Comment Status'] != "") {
                            $product_query['comment_status'] = $product_info['Comment Status'];
                        }
                        if (isset($product_info['Short Description']) && $product_info['Short Description'] != "") {
                            $product_query['post_excerpt'] = $product_info['Short Description'];
                        }
                        if (isset($product_info['Menu Order']) && $product_info['Menu Order'] != "") {
                            $product_query['menu_order'] = $product_info['Menu Order'];
                        }

                        $product_query['ID'] = $existing_product_id;

                        $new_product_id = wp_update_post($product_query, false);
                    }


                    $_SESSION['group_ids'][$product_info['Id']] = $new_product_id;

                    if ($new_product_id > 0) {

                        $wpie_product_log[] = $this->wpie_set_product_attributes($product_info, $new_product_id, $existing_product_id);
                    }
                } else if ($product_info['Product Type'] == 'variable') {

                    if ($existing_product_id == "") {
                        $product_query = array(
                            'post_title' => isset($product_info['Product Name']) ? $product_info['Product Name'] : "Untitled",
                            'post_type' => 'product',
                            'post_status' => isset($product_info['Product Status']) ? $product_info['Product Status'] : "publish",
                            'post_content' => isset($product_info['Description']) ? $product_info['Description'] : "",
                            'ping_status' => isset($product_info['Ping Status']) ? $product_info['Ping Status'] : "open",
                            'comment_status' => isset($product_info['Comment Status']) ? $product_info['Comment Status'] : "closed",
                            'post_excerpt' => isset($product_info['Short Description']) ? $product_info['Short Description'] : "",
                            'menu_order' => isset($product_info['Menu Order']) ? $product_info['Menu Order'] : "0",
                        );
                        $new_product_id = wp_insert_post($product_query, false);
                    } else {

                        $product_query = array();

                        if (isset($product_info['Product Name']) && $product_info['Product Name'] != "") {
                            $product_query['post_title'] = $product_info['Product Name'];
                        }
                        if (isset($product_info['Product Status']) && $product_info['Product Status'] != "") {
                            $product_query['post_status'] = $product_info['Product Status'];
                        } else {
                            $product_query['post_status'] = "publish";
                        }
                        if (isset($product_info['Description']) && $product_info['Description'] != "") {
                            $product_query['post_content'] = $product_info['Description'];
                        }
                        if (isset($product_info['Ping Status']) && $product_info['Ping Status'] != "") {
                            $product_query['ping_status'] = $product_info['Ping Status'];
                        }
                        if (isset($product_info['Comment Status']) && $product_info['Comment Status'] != "") {
                            $product_query['comment_status'] = $product_info['Comment Status'];
                        }
                        if (isset($product_info['Short Description']) && $product_info['Short Description'] != "") {
                            $product_query['post_excerpt'] = $product_info['Short Description'];
                        }
                        if (isset($product_info['Menu Order']) && $product_info['Menu Order'] != "") {
                            $product_query['menu_order'] = $product_info['Menu Order'];
                        }

                        $product_query['ID'] = $existing_product_id;

                        $new_product_id = wp_update_post($product_query, false);

                        $child_args = array(
                            'post_parent' => $new_product_id,
                            'post_type' => 'product_variation',
                            'posts_per_page' => -1
                        );

                        $child_posts = get_posts($child_args);

                        if (is_array($child_posts) && count($child_posts) > 0) {
                            foreach ($child_posts as $child_post) {
                                $child_id = $child_post->ID;
                                $_SESSION['wpie_deleted_ids'][$child_id] = $new_product_id;
                                //@wp_delete_post($child_post->ID, true);
                            }
                        }
                    }

                    $_SESSION['variable_ids'][$product_info['Id']] = $new_product_id;

                    if ($new_product_id > 0) {

                        $wpie_product_log[] = $this->wpie_set_product_attributes($product_info, $new_product_id, $existing_product_id);
                    }
                } else if ($product_info['Product Type'] == 'variation') {
                    if ($product_info['Product Parent id'] != "" && $product_info['Product Parent id'] > 0) {

                        $current_variation_id = $product_info['Id'];

                        if ($existing_product_id == "") {
                            //  @wp_delete_post($existing_product_id, true);
                        }

                        if (isset($_SESSION['wpie_deleted_ids'][$current_variation_id]) && $_SESSION['wpie_deleted_ids'][$current_variation_id] == $product_info['Product Parent id'] && $product_create_method == 'update_product') {
                            unset($_SESSION['wpie_deleted_ids'][$current_variation_id]);
                            $product_query = array();

                            if (isset($product_info['Product Name']) && $product_info['Product Name'] != "") {
                                $product_query['post_title'] = $product_info['Product Name'];
                            }
                            if (isset($product_info['Product Status']) && $product_info['Product Status'] != "") {
                                $product_query['post_status'] = $product_info['Product Status'];
                            } else {
                                $product_query['post_status'] = "publish";
                            }
                            if (isset($product_info['Description']) && $product_info['Description'] != "") {
                                $product_query['post_content'] = $product_info['Description'];
                            }
                            if (isset($product_info['Ping Status']) && $product_info['Ping Status'] != "") {
                                $product_query['ping_status'] = $product_info['Ping Status'];
                            }
                            if (isset($product_info['Comment Status']) && $product_info['Comment Status'] != "") {
                                $product_query['comment_status'] = $product_info['Comment Status'];
                            }
                            if (isset($product_info['Short Description']) && $product_info['Short Description'] != "") {
                                $product_query['post_excerpt'] = $product_info['Short Description'];
                            }
                            if (isset($product_info['Menu Order']) && $product_info['Menu Order'] != "") {
                                $product_query['menu_order'] = $product_info['Menu Order'];
                            }
                            if (isset($product_info['Product Parent id']) && $product_info['Product Parent id'] != "") {
                                $product_query['post_parent'] = @$_SESSION['variable_ids'][$product_info['Product Parent id']];
                            }
                            $product_query['ID'] = $current_variation_id;
                            $new_product_id = wp_update_post($product_query, false);
                        } else {
                            $product_query = array(
                                'post_title' => isset($product_info['Product Name']) ? $product_info['Product Name'] : "Untitled",
                                'post_type' => 'product_variation',
                                'post_status' => isset($product_info['Product Status']) ? $product_info['Product Status'] : "publish",
                                'post_content' => isset($product_info['Description']) ? $product_info['Description'] : "",
                                'ping_status' => isset($product_info['Ping Status']) ? $product_info['Ping Status'] : "open",
                                'comment_status' => isset($product_info['Comment Status']) ? $product_info['Comment Status'] : "closed",
                                'post_excerpt' => isset($product_info['Short Description']) ? $product_info['Short Description'] : "",
                                'post_parent' => @$_SESSION['variable_ids'][$product_info['Product Parent id']],
                                'menu_order' => isset($product_info['Menu Order']) ? $product_info['Menu Order'] : "0",
                            );
                            $new_product_id = wp_insert_post($product_query, false);
                        }
                        if ($new_product_id > 0) {

                            $wpie_product_log[] = $this->wpie_set_product_attributes($product_info, $new_product_id, $existing_product_id);
                        }
                    }
                }

                if ($product_info['Product Type'] != 'variation') {
                    $_SESSION['product_id_list'][] = $new_product_id;
                }

                $_SESSION['imported_ids'][] = $new_product_id;

                if (isset($product_info['Id'])) {

                    $old_temp_product_id = $product_info['Id'];
                    $_SESSION['product_old_new_ids'][$old_temp_product_id] = $new_product_id;
                }
            }
        } catch (Exception $e) {
            $product_all_data['error'] = $e->getMessage();
        }

        $product_all_data['data'] = array(); //$this->get_imported_product($imported_ids);

        $product_all_data['product_log'] = $wpie_product_log;

        if ($product_info['Product Type'] != 'variation') {
            $product_all_data['last_parent'] = $new_product_id;
        } else {
            $product_all_data['last_parent'] = isset($_POST['last_parent']) ? $_POST['last_parent'] : "";
        }

        return $product_all_data;
    }

    function wpie_set_product_attributes($product_info = array(), $new_product_id = 0, $existing_product_id = "") {

        if (isset($product_info['Id'])) {

            $old_temp_product_id = $product_info['Id'];

            $_SESSION['product_old_new_id_list'][$old_temp_product_id] = $new_product_id;
        }

        global $wpdb;

        $product_field_list = $this->get_updated_product_fields();

        $product_log = array();

        if (isset($product_info['Attributes']) && $product_info['Attributes'] != "") {

            $product_attributes_temp = json_decode(str_replace('||', ",", maybe_unserialize($product_info['Attributes'])), 1);

            $product_attributes = array();

            if (!empty($product_attributes_temp)) {
                $temp_array_count = 0;

                foreach ($product_attributes_temp as $temp_attr_all_data) {
                    foreach ($temp_attr_all_data as $key => $value) {
                        $product_attributes[$temp_array_count][$key] = @str_replace("``", '"', $value);
                    }
                    $temp_array_count++;
                }
            }
        }

        if (isset($product_info['Custom Fields']) && $product_info['Custom Fields'] != "") {

            $product_custom_fields = isset($product_info['Custom Fields']) ? $product_info['Custom Fields'] : "";

            $product_custom_fields = maybe_unserialize($product_custom_fields);

            foreach ($product_custom_fields as $key => $value) {
                update_post_meta($new_product_id, $key, maybe_unserialize($value[0]));
            }
        }
//        $exclude_array = array('Product Type', 'Price', 'Permalink', 'SKU', 'Sale Price', 'Visibility', 'On Sale', 'Stock Status', 'Regular Price', 'Total Sales', 'Virtual', 'Purchase Note', 'Weight', 'Length', 'Width', 'Height', 'Unit', 'Sold Individually', 'Manage Stock',
//            'Stock', 'Backorders Allowed', 'Backorders', 'Purchaseable', 'Featured', 'Is Taxable', 'Tax Status', 'Tax Class', 'Button Text', 'Shipping Required', 'Shipping Taxable', 'Shipping Class', 'Shipping Class Id', 'Average Rating', 'Rating Count', 'Variation Description');
//
//        foreach ($product_info as $key => $value) {
//            if (!in_array($key, $exclude_array)) {
//                update_post_meta($new_product_id, $key, $value[0]);
//            }
//        }

        if (is_array($product_attributes) && !empty($product_attributes)) {
            foreach ($product_attributes as $key => $value) {
                if ($value['is_taxonomy'] == 1) {
                    $attr_tax_name = $value['name'];

                    if (!taxonomy_exists($attr_tax_name)) {

                        global $wp_taxonomies, $wp;

                        $attr_tax_name = str_replace('pa_', '', sanitize_title($value['name']));

                        $attribute_taxonomy = array(
                            'attribute_label' => wc_attribute_label($attr_tax_name),
                            'attribute_name' => wc_sanitize_taxonomy_name($attr_tax_name),
                            'attribute_type' => 'select',
                            'attribute_orderby' => '',
                            'attribute_public' => 0
                        );

                        $attr_tax_name = $attribute_taxonomy['attribute_name'];

                        $temp = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute_taxonomy);

                        $new_taxonomy_id = $wpdb->insert_id;

                        $wp_taxonomies[$value['name']] = 1;

                        do_action('woocommerce_attribute_added', $new_taxonomy_id, $attribute_taxonomy);

                        flush_rewrite_rules();

                        delete_transient('wc_attribute_taxonomies');

                        $options = explode(',', $value['value']);

                        if (function_exists('wc_sanitize_term_text_based')) {
                            $taxonomy_values = array_map('wc_sanitize_term_text_based', $options);
                        } else {
                            $taxonomy_values = $options;
                        }


                        $taxonomy_values = array_filter($taxonomy_values, 'strlen');

                        $taxonomy_data = maybe_unserialize(get_option('wpie_taxonomy_data'));

                        $taxonomy_data[] = array(
                            'product_id' => $new_product_id,
                            'attr_tax_name' => 'pa_' . $attr_tax_name,
                            'taxonomy_values' => $taxonomy_values
                        );
                        $taxonomy_data = maybe_serialize($taxonomy_data);

                        update_option('wpie_taxonomy_data', $taxonomy_data);
                    } else {
                        $options = explode(',', $value['value']);

                        if (function_exists('wc_sanitize_term_text_based')) {
                            $taxonomy_values = array_map('wc_sanitize_term_text_based', $options);
                        } else {
                            $taxonomy_values = $options;
                        }

                        $taxonomy_values = array_filter($taxonomy_values, 'strlen');

                        foreach ($taxonomy_values as $term_value) {
                            wp_insert_term($term_value, $attr_tax_name);
                        }

                        wp_set_object_terms($new_product_id, $taxonomy_values, $attr_tax_name);
                    }
                }
            }
        }
        $product_fields_title = array();

        foreach ($product_field_list['product_fields'] as $field_data) {
            $product_fields_title[] = $field_data['field_title'];
        }

        foreach ($product_info as $key => $value) {
            if (substr($key, 0, 10) == "attribute:") {

                $attr_tax__temp_name = 'pa_' . @strtolower(substr($key, 10));

                $attr_tax_name = $attr_tax__temp_name;

                if (!taxonomy_exists($attr_tax_name)) {

                    global $wp_taxonomies, $wp;

                    $attr_tax_name = @substr($key, 10);

                    $attribute_taxonomy = array(
                        'attribute_label' => wc_attribute_label(ucfirst($attr_tax_name)),
                        'attribute_name' => wc_sanitize_taxonomy_name($attr_tax_name),
                        'attribute_type' => 'select',
                        'attribute_orderby' => '',
                        'attribute_public' => 0
                    );

                    $attr_tax_name = $attribute_taxonomy['attribute_name'];

                    $temp = $wpdb->insert($wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute_taxonomy);

                    $new_taxonomy_id = $wpdb->insert_id;

                    $wp_taxonomies[$attr_tax__temp_name] = 1;

                    do_action('woocommerce_attribute_added', $new_taxonomy_id, $attribute_taxonomy);

                    flush_rewrite_rules();

                    delete_transient('wc_attribute_taxonomies');

                    $options = explode(',', $value);

                    if (function_exists('wc_sanitize_term_text_based')) {
                        $taxonomy_values = array_map('wc_sanitize_term_text_based', $options);
                    } else {
                        $taxonomy_values = $options;
                    }

                    $taxonomy_values = array_filter($taxonomy_values, 'strlen');

                    $taxonomy_data = maybe_unserialize(get_option('wpie_taxonomy_data'));

                    $taxonomy_data[] = array(
                        'product_id' => $new_product_id,
                        'attr_tax_name' => 'pa_' . $attr_tax_name,
                        'taxonomy_values' => $taxonomy_values
                    );
                    $taxonomy_data = maybe_serialize($taxonomy_data);

                    update_option('wpie_taxonomy_data', $taxonomy_data);
                } else {

                    $options = explode(',', strtolower($value));

                    if (function_exists('wc_sanitize_term_text_based')) {
                        $taxonomy_values = array_map('wc_sanitize_term_text_based', $options);
                    } else {
                        $taxonomy_values = $options;
                    }

                    $taxonomy_values = array_filter($taxonomy_values, 'strlen');

                    foreach ($taxonomy_values as $term_value) {

                        wp_insert_term($term_value, $attr_tax_name);
                    }

                    $product_attribute_old = @metadata_exists('post', $new_product_id, '_product_attributes') ? get_post_meta($new_product_id, '_product_attributes', true) : "";
                    if ($product_attribute_old != "") {
                        $product_attribute_old = maybe_unserialize($product_attribute_old);
                    } else {
                        $product_attribute_old = array();
                    }

                    $product_attribute_old[$attr_tax_name]['name'] = $attr_tax_name;

                    $product_attribute_old[$attr_tax_name]['value'] = maybe_serialize($taxonomy_values);

                    $product_attribute_old[$attr_tax_name]['position'] = 0;

                    $product_attribute_old[$attr_tax_name]['is_variation'] = 1;

                    $product_attribute_old[$attr_tax_name]['is_taxonomy'] = 1;

                    $product_attribute_old[$attr_tax_name]['is_visible'] = 1;

                    if (isset($product_info['Product Type']) && $product_info['Product Type'] == 'variation') {

                        update_post_meta($new_product_id, 'attribute_' . $attr_tax_name, implode(',', $taxonomy_values));
                    }
                    update_post_meta($new_product_id, '_product_attributes', $product_attribute_old);

                    wp_set_object_terms($new_product_id, $taxonomy_values, $attr_tax_name);
                }
            } else if (!in_array($key, $product_fields_title)) {

                update_post_meta($new_product_id, $key, $value);
            }
        }


        $include_array = array("Variation Description" => "_variation_description", "SKU" => "_sku", "Sale Price" => "_sale_price", "Visibility" => "_visibility", "Stock Status" => "_stock_status", "Price" => "_price", "Regular Price" => "_regular_price", "Virtual" => "_virtual", "Purchase Note" => "_purchase_note", "Weight" => "_weight", "Length" => "_length", "Width" => "_width", "Height" => "_height", "Sold Individually" => "_sold_individually", "Manage Stock" => "_manage_stock",
            "Stock" => "_stock", "Backorders" => "_backorders", "Featured" => "_featured", "Tax Status" => "_tax_status", "Tax Class" => "_tax_class");

        foreach ($include_array as $key => $value) {
            if (isset($product_info[$key])) {
                update_post_meta($new_product_id, $value, $product_info[$key]);
            }
        }

        $product_new_category = isset($_POST['wpie_product_category']) ? $_POST['wpie_product_category'] : array();

        if (isset($product_info['Downloadable Files']) && $product_info['Downloadable Files'] == 'yes' || $product_info['Downloadable Files'] == '1') {

            if (isset($product_info['Download Type']) && $product_info['Download Type'] != "") {
                update_post_meta($new_product_id, '_download_type', $product_info['Download Type']);
            }
            if (isset($product_info['Download Limit']) && $product_info['Download Limit'] != "") {
                update_post_meta($new_product_id, '_download_limit', $product_info['Download Limit']);
            }
            if (isset($product_info['Download Expiry']) && $product_info['Download Expiry'] != "") {
                update_post_meta($new_product_id, '_download_expiry', $product_info['Download Expiry']);
            }
            if (isset($product_info['Downloadable Files']) && $product_info['Downloadable Files'] != "") {
                $product_log[] = $this->get_downlodable_file($new_product_id, $product_info['Downloadable Files']);
            }
        }

        if (isset($product_info['_downloadable_files']) && $product_info['_downloadable_files'] != "") {
            $product_log[] = $this->get_downlodable_file($new_product_id, $product_info['_downloadable_files']);
        }

        if (isset($product_info['Cross Sell Ids']) && $product_info['Cross Sell Ids'] != "") {
            update_post_meta($new_product_id, '_crosssell_ids', $this->get_ids_by_sku($product_info['Cross Sell Ids']));
        }

        if (isset($product_info['Upsell Ids']) && $product_info['Upsell Ids'] != "") {
            update_post_meta($new_product_id, '_upsell_ids', $this->get_ids_by_sku($product_info['Upsell Ids']));
        }

        if (isset($product_info['Related Ids']) && $product_info['Related Ids'] != "") {
            update_post_meta($new_product_id, 'related_ids', $this->get_ids_by_sku($product_info['Related Ids']));
        }


        if (isset($product_info['Product Type']) && $product_info['Product Type'] == 'external') {

            if (isset($product_info['Product URL']) && $product_info['Product URL'] != "") {

                update_post_meta($new_product_id, '_product_url', $product_info['Product URL']);
            }
            if (isset($product_info['Button Text']) && $product_info['Button Text'] != "") {

                update_post_meta($new_product_id, '_button_text', $product_info['Button Text']);
            }
        }
        if (!empty($product_new_category)) {

            wp_set_object_terms($new_product_id, $product_new_category, 'product_cat');
        } else if (isset($product_info['Categories'])) {

            $product_categories_new = @json_decode($product_info['Categories'], true);

            if (is_array($product_categories_new) && isset($product_categories_new[0]['name'])) {
                $old_categories = wp_get_post_terms($new_product_id, 'product_cat', array("fields" => "ids"));

                wp_remove_object_terms($new_product_id, $old_categories, 'product_cat');

                foreach ($product_categories_new as $product_current_category) {

                    $product_cat = get_term_by('slug', $product_current_category['slug'], 'product_cat');

                    $cat_id = $product_cat->term_id;

                    if (!$cat_id) {

                        $new_categories = wp_insert_term(
                                $product_current_category['name'], 'product_cat', array(
                            'description' => '',
                            'slug' => $product_current_category['slug']
                                )
                        );


                        if (!is_wp_error()) {
                            $cat_id = $new_categories['term_id'];
                        }
                    }

                    if ($cat_id) {
                        wp_set_object_terms($new_product_id, $cat_id, 'product_cat', true);
                    }
                }
            } else {
                wp_set_object_terms($new_product_id, explode(',', $product_info['Categories']), 'product_cat');
            }
        }

        if (isset($product_info['Tags']) && $product_info['Tags'] != "") {
            wp_set_object_terms($new_product_id, explode(',', $product_info['Tags']), 'product_tag');
        }
        if (isset($product_info['Product Type']) && $product_info['Product Type'] != "") {
            wp_set_object_terms($new_product_id, $product_info['Product Type'], 'product_type');
        }
        if (isset($product_info['Shipping Class']) && $product_info['Shipping Class'] != "") {
            wp_set_object_terms($new_product_id, $product_info['Shipping Class'], 'product_shipping_class');
        }


        $has_post_thumbnail = isset($product_info['Product Image Set']) ? $product_info['Product Image Set'] : "";

        if (isset($product_info['Product Images']) && $product_info['Product Images'] != "") {
            $product_log[] = $this->wpie_upload_remote_image($product_info['Product Images'], $new_product_id, $has_post_thumbnail, $existing_product_id);
        }
        return $product_log;
    }

    function wpie_upload_remote_image($images = "", $new_product_id = "", $has_post_thumbnail = "", $existing_product_id = "") {

        global $new_product_errors, $wpie_product;

        $image_list = @explode(',', $images);

        $old_attachments = get_posts(array(
            'post_type' => 'attachment',
            'posts_per_page' => -1,
            'post_status' => 'any',
            'post_parent' => $new_product_id
        ));

        $final_attachments = array();

        $post_thumb = 0;

        $post_thumb_id = 0;

        $old_post_thumbnail_id = get_post_thumbnail_id($new_product_id);

        if (!empty($old_attachments)) {
            foreach ($old_attachments as $old_attachment) {

                $duplicate_images = array_search($old_attachment->guid, $image_list);

                if ($duplicate_images === false) {
                    if (false === wp_delete_attachment($old_attachment->ID)) {
                        
                    }
                } else {
                    if ($old_post_thumbnail_id == $old_attachment->ID) {
                        $post_thumb_id = $old_post_thumbnail_id;
                    }
                    if ($duplicate_images == 0) {
                        $post_thumb++;
                    }

                    $final_attachments[] = $old_attachment;

                    unset($image_list[$duplicate_images]);
                }
            }
        }


        $new_product_errors = array();

        $image_gallery_ids = array();

        if (!empty($image_list)) {
            $wp_upload_dir = wp_upload_dir();

            foreach ($image_list as $image_index => $image_url) {

                if ($image_url != "") {
                    $image_url = str_replace(' ', '%20', trim($image_url));

                    $parsed_url = parse_url($image_url);

                    $pathinfo = pathinfo($parsed_url['path']);

                    $allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');

                    $url_ext = @explode('.', $image_url);

                    if (!empty($url_ext)) {
                        $image_ext = @strtolower(end($url_ext));
                    } else {
                        $image_ext = "";
                    }

                    if (!in_array($image_ext, $allowed_extensions)) {

                        $new_product_errors[] = sprintf(__('A valid file extension wasn\'t found in %s. Extension found was %s. Allowed extensions are: %s.', WPIE_TEXTDOMAIN), $image_url, $image_ext, implode(', ', $allowed_extensions));

                        continue;
                    }

                    $dest_filename = wp_unique_filename($wp_upload_dir['path'], $pathinfo['basename']);

                    $dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;

                    $dest_url = $wp_upload_dir['url'] . '/' . $dest_filename;

                    if (ini_get('allow_url_fopen')) {

                        if (!@copy($image_url, $dest_path)) {

                            $http_status = $http_response_header[0];

                            $new_product_errors[] = sprintf(__('%s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $image_url);
                        }
                    } elseif (function_exists('curl_init')) {

                        $ch = curl_init($image_url);

                        $fp = fopen($dest_path, "wb");

                        $options = array(
                            CURLOPT_FILE => $fp,
                            CURLOPT_HEADER => 0,
                            CURLOPT_FOLLOWLOCATION => 1,
                            CURLOPT_TIMEOUT => 60);

                        curl_setopt_array($ch, $options);

                        curl_exec($ch);

                        $http_status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

                        curl_close($ch);

                        fclose($fp);

                        if ($http_status != 200) {

                            unlink($dest_path);

                            $new_product_errors[] = sprintf(__('HTTP status %s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $image_url);
                        }
                    } else {

                        $new_product_errors[] = sprintf(__('Looks like %s is off and %s is not enabled. No images were imported.', WPIE_TEXTDOMAIN), '<code>allow_url_fopen</code>', '<code>cURL</code>');

                        break;
                    }

                    if (!file_exists($dest_path)) {

                        $new_product_errors[] = sprintf(__('Couldn\'t download file %s.', WPIE_TEXTDOMAIN), $image_url);

                        continue;
                    }

                    $new_post_image_paths[] = array(
                        'path' => $dest_path,
                        'source' => $image_url
                    );
                }
            }



            if (!empty($new_post_image_paths)) {
                foreach ($new_post_image_paths as $image_index => $dest_path_info) {

                    if ($existing_product_id != "" && $has_post_thumbnail == 'yes') {

                        $existing_attachment_query = array(
                            'numberposts' => 1,
                            'meta_key' => '_import_source',
                            'post_status' => 'inherit',
                            'post_parent' => $existing_product_id,
                            'meta_query' => array(
                                array(
                                    'key' => '_import_source',
                                    'value' => $dest_path_info['source'],
                                    'compare' => '='
                                )
                            ),
                            'post_type' => 'attachment',
                            'fields' => 'ids',
                        );

                        $existing_attachments = get_posts($existing_attachment_query);

                        if (!empty($existing_attachments) && isset($existing_attachments[0]) && $existing_attachments[0] != "" && $existing_attachments[0] > 0) {

                            $new_product_errors[] = sprintf(__('Skipping import of duplicate image %s.', WPIE_TEXTDOMAIN), $dest_path_info['source']);

                            continue;
                        }
                    }

                    if (!file_exists($dest_path_info['path'])) {

                        $new_product_errors[] = sprintf(__('Couldn\'t find local file %s.', WPIE_TEXTDOMAIN), $dest_path_info['path']);

                        continue;
                    }

                    $dest_url = str_ireplace(ABSPATH, home_url('/'), $dest_path_info['path']);

                    $path_parts = pathinfo($dest_path_info['path']);

                    $wp_filetype = wp_check_filetype($dest_path_info['path']);

                    $attachment = array(
                        'guid' => $dest_url,
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', $path_parts['filename']),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attachment_id = wp_insert_attachment($attachment, $dest_path_info['path'], $new_product_id);

                    require_once(ABSPATH . 'wp-admin/includes/image.php');

                    $attach_data = wp_generate_attachment_metadata($attachment_id, $dest_path_info['path']);

                    wp_update_attachment_metadata($attachment_id, $attach_data);

                    update_post_meta($attachment_id, '_import_source', $dest_path_info['source']);

                    if ($image_index == 0 && $has_post_thumbnail == 'yes' && $post_thumb == 0) {
                        if ($post_thumb_id == 0) {
                            update_post_meta($new_product_id, '_thumbnail_id', $attachment_id);
                        } else {
                            update_post_meta($new_product_id, '_thumbnail_id', $post_thumb_id);
                        }
                    } else {

                        $image_gallery_ids[] = $attachment_id;
                    }
                }
            }
        }
        if (!empty($final_attachments)) {
            foreach ($final_attachments as $new_attachment) {

                $attachment_id = $new_attachment->ID;

                require_once(ABSPATH . 'wp-admin/includes/image.php');

                $attach_data = wp_generate_attachment_metadata($attachment_id, $dest_path_info['path']);

                wp_update_attachment_metadata($attachment_id, $attach_data);

                update_post_meta($attachment_id, '_import_source', $dest_path_info['source']);

                if ($has_post_thumbnail == 'yes' && $post_thumb > 0) {
                    $post_thumb = 0;
                    if ($post_thumb_id == 0) {
                        update_post_meta($new_product_id, '_thumbnail_id', $attachment_id);
                    } else {
                        update_post_meta($new_product_id, '_thumbnail_id', $post_thumb_id);
                    }
                } else {

                    $image_gallery_ids[] = $attachment_id;
                }
            }
        }

        if (count($image_gallery_ids) > 0) {

            update_post_meta($new_product_id, '_product_image_gallery', implode(',', $image_gallery_ids));
        }

        return $new_product_errors;
    }

    function get_downlodable_file($new_product_id = 0, $downloadable_files = "") {
        global $download_product_errors;

        $downloadable_files = maybe_unserialize($downloadable_files);

        $downloadable_file_list = array();

        $download_product_errors = array();

        $wp_upload_dir = wp_upload_dir();

        $allowed_file_types = apply_filters('woocommerce_downloadable_file_allowed_mime_types', get_allowed_mime_types());

        if (is_array($downloadable_files) && !empty($downloadable_files)) {

            foreach ($downloadable_files as $file_hash => $file_data) {

                $file_url = str_replace(' ', '%20', trim($file_data['file']));

                $file_type = wp_check_filetype(strtok($file_url, '?'));

                $parsed_url = parse_url($file_url, PHP_URL_PATH);

                $parsed_info_url = parse_url($file_url);

                $pathinfo = pathinfo($parsed_info_url['path']);

                $extension = pathinfo($parsed_url, PATHINFO_EXTENSION);

                if (!empty($extension) && !in_array($file_type['type'], $allowed_file_types)) {

                    $download_product_errors[] = sprintf(__('A valid file extension wasn\'t found in %s. Extension found was %s. Allowed extensions are: %s.', WPIE_TEXTDOMAIN), $file_url, $extension, implode(',', $allowed_file_types));

                    continue;
                }

                $dest_filename = wp_unique_filename($wp_upload_dir['path'], $pathinfo['basename']);

                $dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;

                $dest_url = $wp_upload_dir['url'] . '/' . $dest_filename;
                if (ini_get('allow_url_fopen')) {
                    if (!@copy($file_url, $dest_path)) {
                        $http_status = $http_response_header[0];

                        $download_product_errors[] = sprintf(__('%s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $file_url);
                    }
                } elseif (function_exists('curl_init')) {

                    $ch = curl_init($file_url);

                    $fp = fopen($dest_path, "wb");

                    $options = array(
                        CURLOPT_FILE => $fp,
                        CURLOPT_HEADER => 0,
                        CURLOPT_FOLLOWLOCATION => 1,
                        CURLOPT_TIMEOUT => 60);

                    curl_setopt_array($ch, $options);

                    curl_exec($ch);

                    $http_status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

                    curl_close($ch);

                    fclose($fp);

                    if ($http_status != 200) {

                        unlink($dest_path);

                        $download_product_errors[] = sprintf(__('HTTP status %s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $file_url);
                    }
                } else {

                    $download_product_errors[] = sprintf(__('Looks like %s is off and %s is not enabled. No images were imported.', WPIE_TEXTDOMAIN), '<code>allow_url_fopen</code>', '<code>cURL</code>');

                    break;
                }

                if (!file_exists($dest_path)) {

                    $download_product_errors[] = sprintf(__('Couldn\'t download file %s.', WPIE_TEXTDOMAIN), $file_url);

                    continue;
                }
                $file_name = wc_clean($file_data['name']);

                $file_hash = md5($dest_url);

                $downloadable_file_list[$file_hash] = array(
                    'name' => $file_name,
                    'file' => $dest_url
                );
            }

            do_action('woocommerce_process_product_file_download_paths', $new_product_id, 0, $downloadable_file_list);

            update_post_meta($new_product_id, '_downloadable_files', $downloadable_file_list);
        } else if (!is_array($downloadable_files) && $downloadable_files != "") { {
                $downloadable_files = explode(",", $downloadable_files);
                if (is_array($downloadable_files)) {

                    foreach ($downloadable_files as $new_file_data) {

                        $new_file_data = explode("==", $new_file_data);

                        if (is_array($new_file_data) && isset($new_file_data[1])) {

                            $file_url = str_replace(' ', '%20', trim($new_file_data[1]));

                            $file_type = wp_check_filetype(strtok($file_url, '?'));

                            $parsed_url = parse_url($file_url, PHP_URL_PATH);

                            $parsed_info_url = parse_url($file_url);

                            $pathinfo = pathinfo($parsed_info_url['path']);

                            $extension = pathinfo($parsed_url, PATHINFO_EXTENSION);

                            if (!empty($extension) && !in_array($file_type['type'], $allowed_file_types)) {

                                $download_product_errors[] = sprintf(__('A valid file extension wasn\'t found in %s. Extension found was %s. Allowed extensions are: %s.', WPIE_TEXTDOMAIN), $file_url, $extension, implode(',', $allowed_file_types));

                                continue;
                            }

                            $dest_filename = wp_unique_filename($wp_upload_dir['path'], $pathinfo['basename']);

                            $dest_path = $wp_upload_dir['path'] . '/' . $dest_filename;

                            $dest_url = $wp_upload_dir['url'] . '/' . $dest_filename;

                            if (ini_get('allow_url_fopen')) {

                                if (!@copy($file_url, $dest_path)) {

                                    $http_status = $http_response_header[0];

                                    $download_product_errors[] = sprintf(__('%s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $file_url);
                                }
                            } elseif (function_exists('curl_init')) {

                                $ch = curl_init($file_url);

                                $fp = fopen($dest_path, "wb");

                                $options = array(
                                    CURLOPT_FILE => $fp,
                                    CURLOPT_HEADER => 0,
                                    CURLOPT_FOLLOWLOCATION => 1,
                                    CURLOPT_TIMEOUT => 60);

                                curl_setopt_array($ch, $options);

                                curl_exec($ch);

                                $http_status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));

                                curl_close($ch);

                                fclose($fp);

                                if ($http_status != 200) {

                                    unlink($dest_path);

                                    $download_product_errors[] = sprintf(__('HTTP status %s encountered while attempting to download %s', WPIE_TEXTDOMAIN), $http_status, $file_url);
                                }
                            } else {

                                $download_product_errors[] = sprintf(__('Looks like %s is off and %s is not enabled. No images were imported.', WPIE_TEXTDOMAIN), '<code>allow_url_fopen</code>', '<code>cURL</code>');

                                break;
                            }

                            if (!file_exists($dest_path)) {

                                $download_product_errors[] = sprintf(__('Couldn\'t download file %s.', WPIE_TEXTDOMAIN), $file_url);

                                continue;
                            }
                            $file_name = wc_clean($new_file_data[0]);

                            $file_hash = md5($dest_url);

                            $downloadable_file_list[$file_hash] = array(
                                'name' => $file_name,
                                'file' => $dest_url
                            );
                        }

                        do_action('woocommerce_process_product_file_download_paths', $new_product_id, 0, $downloadable_file_list);

                        update_post_meta($new_product_id, '_downloadable_files', $downloadable_file_list);
                    }
                }
            }
        }
        return $download_product_errors;
    }

    function get_ids_by_sku($sku_list = "") {
        if ($sku_list != "") {
            $sku_list_all = @explode(',', $sku_list);

            if (is_array($sku_list_all) && !empty($sku_list_all)) {
                $existing_post_query = array(
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_sku',
                            'value' => $sku_list_all,
                            'compare' => 'IN'
                        ),
                    ),
                    'post_type' => array('product'),
                    'fields' => 'ids',
                );


                $existing_product = get_posts($existing_post_query);

                if (!empty($existing_product)) {
                    return $existing_product;
                }
            }
        }

        return "";
    }

    function wpie_get_product_import_preview() {
        $return_value = array();

        $record_offset = isset($_POST['start']) ? $_POST['start'] : 0;

        $record_limit = isset($_POST['length']) ? $_POST['length'] : 0;

        if (session_id() == '') {
            session_start();
        }

        $query_args = array(
            'post_type' => 'product',
            'orderby' => 'post_date',
            'order' => 'ASC',
            'post__in' => $_SESSION['product_id_list']
        );

        $total_records = count($_SESSION['product_id_list']);
        $data_query = addslashes(json_encode($query_args));

        // function get_filter_product_data($wpie_data, $start_product, $total_records = 0, $product_offset = "", $product_limit = "", $is_preview = 0) {

        $product_list_data = $this->get_filter_product_data($data_query, $record_offset, $total_records, 0, $record_limit, 1);

        $temp_data = json_decode(json_encode($product_query_data['product_data']), 1);

        $final_data = array();

        $product_field_list = $this->get_updated_product_fields();

        foreach ($product_list_data['product_data'] as $product_info) {

            $data_result = array();

            foreach ($product_field_list['product_fields'] as $field_data) {

                if ($field_data['field_display'] == 1) {

                    $output = "";

                    $temp_data = $field_data['field_key'];

                    if ($field_data['field_key'] == 'images') {

                        foreach ($product_info[$temp_data] as $product_images) {

                            $output .= $product_images['src'] . ',';
                        }

                        $output = substr($output, 0, -1);
                    } else {

                        $output = isset($product_info[$temp_data]) ? $product_info[$temp_data] : "";
                    }

                    $data_result[] = $output;
                }
            }

            $final_data[] = $data_result;
        }

        $return_value['data'] = $final_data;

        $return_value['message'] = 'success';

        $return_value['recordsFiltered'] = $total_records;

        $return_value['recordsTotal'] = $total_records;

        echo json_encode($return_value);

        die();
    }

    function wpie_remove_export_entry() {

        global $wpdb;


        $log_id = isset($_POST['log_id']) ? $_POST['log_id'] : "";

        $file_name = isset($_POST['file_name']) ? $_POST['file_name'] : "";

        $return_value = array();

        $return_value['message'] = 'error';

        if ($log_id != "") {
            $wpdb->query($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "wpie_export_log WHERE export_log_id = %d ", $log_id));

            if (file_exists(WPIE_UPLOAD_DIR . '/' . $file_name)) {
                @unlink(WPIE_UPLOAD_DIR . '/' . $file_name);
            }

            $return_value['message'] = 'success';

            $return_value['message_content'] = __('Successfully Deleted.', WPIE_TEXTDOMAIN);
        }

        $return_value['data'] = '';

        echo json_encode($return_value);

        die();
    }

    function wpie_download_import_export_file() {
        if (isset($_POST['wpie_download_exported_file']) && $_POST['wpie_download_exported_file'] != "" && !isset($_POST['action'])) {


            $filename = $_POST['wpie_download_exported_file'];

            if (file_exists(WPIE_UPLOAD_DIR . '/' . $filename)) {

                header('Cache-Control:must-revalidate,post-check=0,pre-check=0');

                header('Content-Description: File Transfer');

                header('Content-Type: text/csv;');

                header('Content-Disposition: attachment; filename=' . $filename);

                header('Expires:0');

                header('Pragma: public');

                readfile(WPIE_UPLOAD_DIR . '/' . $filename);
            }

            die();
        }
    }

    function wpie_set_product_import_data($wpie_data = array()) {

        global $wpdb;

        if (session_id() == '') {
            session_start();
        }

        $file_url = isset($wpie_data['wpie_import_file_url']) ? $wpie_data['wpie_import_file_url'] : "";

        $file_path_data = isset($wpie_data['wpie_csv_upload_file']) ? $wpie_data['wpie_csv_upload_file'] : "";

        $product_offset = isset($wpie_data['product_offset']) ? $wpie_data['product_offset'] : 0;

        $process_status = isset($wpie_data['status']) ? $wpie_data['status'] : "pending";

        $wpie_import_determinator = (isset($wpie_data['wpie_import_determinator']) || trim($wpie_data['wpie_import_determinator']) != "") ? $wpie_data['wpie_import_determinator'] : ",";

        $wpie_data_update_option = isset($wpie_data['wpie_data_update_option']) ? $wpie_data['wpie_data_update_option'] : "product_sku";

        $product_field_list = $this->get_updated_product_fields();

        $file_path = "";

        if ($file_path_data != "") {
            $file_path = $file_path_data;
        } else if ($file_url != "") {
            $file_path = $file_url;
        }

        if ($file_path != "") {

            $new_file = pathinfo($file_path);

            $new_values = array();

            $new_values['export_log_file_type'] = 'import';
            $new_values['export_log_file_name'] = $new_file['basename'];
            $new_values['export_log_data'] = 'Product';
            $new_values['create_date'] = current_time('mysql');

            $res = $wpdb->insert($wpdb->prefix . "wpie_export_log", $new_values);

            $fh = @fopen($file_path, 'r');

            $import_data = array();

            if ($fh !== FALSE) {

                $csv_temp_count = 0;
                while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {
                    if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                        foreach ($new_line as $csv_new_column) {
                            if ($csv_new_column == 'Id' || $csv_new_column == 'SKU') {
                                $csv_temp_count++;
                                break;
                            }
                        }
                        if ($csv_temp_count == 0) {
                            break;
                        }
                    }

                    $import_data_temp[] = $new_line;

                    $csv_temp_count++;
                }
                if ($csv_temp_count == 0) {
                    if ($wpie_import_determinator == ",") {
                        $wpie_import_determinator = ";";
                    } else {
                        $wpie_import_determinator = ",";
                    }
                    @rewind($fh);
                    while (( $new_line = fgetcsv($fh, 0, $wpie_import_determinator) ) !== FALSE) {

                        if ($csv_temp_count == 0 && is_array($new_line) && !empty($new_line)) {
                            foreach ($new_line as $csv_new_column) {
                                if ($csv_new_column == 'Id' || $csv_new_column == 'SKU') {
                                    $csv_temp_count++;
                                    break;
                                }
                            }
                            if ($csv_temp_count == 0) {
                                break;
                            }
                        }
                        $import_data_temp[] = $new_line;

                        $csv_temp_count++;
                    }
                }
                fclose($fh);

                $fields_title_array = $import_data_temp[0];

                unset($import_data_temp[0]);

                $count = 0;
                $total_records = count($import_data_temp);


                foreach ($import_data_temp as $data) {
                    foreach ($data as $key => $value) {
                        $temp_key = $fields_title_array[$key];
                        $import_data[$count][$temp_key] = $value;
                    }
                    if (!isset($import_data[0]['Id'])) {

                        if (isset($import_data[0]['SKU'])) {
                            $import_data[$count]['Id'] = $count + 999999;
                        } else {
                            $import_data = array();
                        }
                    }
                    $count++;
                }
            }
        }

        if (!empty($import_data)) {

            $wpie_product_create_method = isset($wpie_data['wpie_product_create_method']) ? $wpie_data['wpie_product_create_method'] : "";
            $import_type = 'scheduled';
            $product_updated_data = $this->wpie_create_new_product($import_data, $wpie_product_create_method, $import_type, $wpie_data_update_option);

            if (!empty($_SESSION['wpie_deleted_ids'])) {
                foreach ($_SESSION['wpie_deleted_ids'] as $key => $value) {
                    @wp_delete_post($key, true);
                    unset($_SESSION['wpie_deleted_ids'][$key]);
                }
            }
        }

        if ((!empty($_SESSION['group_child_id'])) && (!empty($_SESSION['group_child_id']))) {
            foreach ($_SESSION['group_child_id'] as $key => $value) {
                $post_data = array('ID' => $key, 'post_parent' => $_SESSION['group_child_id'][$value]);
                wp_update_post($post_data);
                unset($_SESSION['group_child_id'][$key]);
            }
        }
    }

    function update_product_attributes() {

        $taxonomy_data = get_option('wpie_taxonomy_data');

        if ($taxonomy_data != "") {
            $taxonomy_data = maybe_unserialize($taxonomy_data);

            if (is_array($taxonomy_data) && !empty($taxonomy_data)) {
                foreach ($taxonomy_data as $new_data) {
                    $taxonomy_values = $new_data['taxonomy_values'];

                    $new_product_id = $new_data['product_id'];

                    $attr_tax_name = $new_data['attr_tax_name'];

                    if (taxonomy_exists($attr_tax_name)) {

                        foreach ($taxonomy_values as $term_value) {
                            wp_insert_term($term_value, $attr_tax_name);
                        }

                        $product_attribute_old = @metadata_exists('post', $new_product_id, '_product_attributes') ? get_post_meta($new_product_id, '_product_attributes', true) : "";

                        if ($product_attribute_old != "") {
                            $product_attribute_old = maybe_unserialize($product_attribute_old);
                        } else {
                            $product_attribute_old = array();
                        }

                        $product_attribute_old[$attr_tax_name]['name'] = $attr_tax_name;

                        $product_attribute_old[$attr_tax_name]['value'] = maybe_serialize($taxonomy_values);

                        $product_attribute_old[$attr_tax_name]['position'] = 0;

                        $product_attribute_old[$attr_tax_name]['is_variation'] = 1;

                        $product_attribute_old[$attr_tax_name]['is_taxonomy'] = 1;

                        $product_attribute_old[$attr_tax_name]['is_visible'] = 1;

                        update_post_meta($new_product_id, '_product_attributes', $product_attribute_old);

                        wp_set_object_terms($new_product_id, $taxonomy_values, $attr_tax_name);
                    }
                }
            }
            update_option('wpie_taxonomy_data', "");
        }
    }

}
