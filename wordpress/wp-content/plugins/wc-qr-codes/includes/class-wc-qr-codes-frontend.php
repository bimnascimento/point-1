<?php

/**
 * Frondend class
 */
class WCQRCodesFrentend {

    function __construct() {
        add_filter('woocommerce_product_tabs', array(&$this, 'wc_qr_codes_product_tab'));
    }

    /**
     * add new woocommerce tab for display qr codes
     * @param array $tabs
     * @return array
     */
    function wc_qr_codes_product_tab($tabs) {
        $tabs['test_tab'] = array(
            'title' => __('QR Codes', 'woocommerce'),
            'priority' => 50,
            'callback' => array(&$this, 'wc_qr_codes_product_tab_content')
        );

        return $tabs;
    }
    /**
     * Display QR code in product page
     * @global object $product
     */
    function wc_qr_codes_product_tab_content() {
        global $product;
        echo '<h2>QR Codes</h2>';
        $is_qr_code_exist = get_post_meta($product->get_id(), '_is_qr_code_exist', true);
        $product_qr_code = get_post_meta($product->get_id(), '_product_qr_code', true);
        if (!empty($is_qr_code_exist) && !empty($product_qr_code) && file_exists(WCQRC_QR_IMAGE_DIR . $product_qr_code)) {
            echo '<div class="wc-qr-codes-container">';
            echo '<img class="wcqrc-qr-code-img" src="' . WCQRC_QR_IMAGE_URL . $product_qr_code . '" alt="QR Code" />';
            echo '</div>';
        }
    }
}
