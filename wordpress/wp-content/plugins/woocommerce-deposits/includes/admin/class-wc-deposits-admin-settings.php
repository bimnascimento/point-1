<?php

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * @brief Adds a new panel to the WooCommerce Settings
 *
 */
class WC_Deposits_Admin_Settings
{
  public $wc_deposits;

  public function __construct(&$wc_deposits)
  {
    $this->wc_deposits = $wc_deposits;
    // Hook the settings page
    add_filter('woocommerce_settings_tabs_array', array($this, 'settings_tabs_array'), 21);
    add_action('woocommerce_settings_tabs_wc-deposits', array($this, 'settings_tabs_wc_deposits'));
    add_action('woocommerce_update_options_wc-deposits', array($this, 'update_options_wc_deposits'));
  }

  public function settings_tabs_array($tabs)
  {
    $tabs['wc-deposits'] = __('Deposits', 'woocommerce-deposits');
    return $tabs;
  }

  /**
  * @brief Write out settings html
  *
  * @param array $settings ...
  * @return void
  */
  public function settings_tabs_wc_deposits()
  {
    $settings = array(

        /*
         * Site-wide settings
         */

        'sitewide_title' => array(
          'name'  => __( 'Site-wide Settings', 'woocommerce-deposits' ),
          'type'  => 'title',
          'desc'  => '',
          'id'    => 'wc_deposits_site_wide_title'
        ),
        'deposits_disable' => array(
          'name'  => __('Disable Deposits', 'woocommerce-deposits'),
          'type'  => 'checkbox',
          'desc'  => __('Check this to disable all deposit functionality with one click.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_site_wide_disable',
        ),
        'deposits_default' => array(
          'name'  => __('Default Selection', 'woocommerce-deposits'),
          'type'  => 'radio',
          'desc'  => __('Select the default deposit option.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_default_option',
          'options' => array(
            'deposit' => __('Pay Deposit', 'woocommerce-deposits'),
            'full'    => __('Full Amount', 'woocommerce-deposits')
          ),
          'default' => 'deposit'
        ),
        'deposits_tax' => array(
          'name'  => __('Display Taxes', 'woocommerce-deposits'),
          'type'  => 'checkbox',
          'desc'  => __('Check this to count taxes as part of deposits for purposes of display to the customer. (Taxes are always collected upfront)', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_tax_display',
        ),
        'deposits_stock' => array(
          'name'  => __('Reduce Stocks On', 'woocommerce-deposits'),
          'type'  => 'radio',
          'desc'  => __('Choose when to reduce stocks.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_reduce_stock',
          'options' => array(
            'deposit' => __('Deposit Payment', 'woocommerce-deposits'),
            'full'    => __('Full Payment', 'woocommerce-deposits')
          ),
          'default' => 'full'
        ),
        'deposits_payaple' => array(
          'name'  => __('Enable Second Payment', 'woocommerce-deposits'),
          'type'  => 'checkbox',
          'desc'  => __('Uncheck this to prevent the customer from making the second payment. (You\'ll be have to manually mark the order as completed)',
            'woocommerce-deposits'),
          'id'    => 'wc_deposits_remaining_payable',
          'default' => 'yes',
        ),
        'sitewide_end' => array(
          'type'  => 'sectionend',
          'id'    => 'wc_deposits_site_wide_end'
        ),

        /*
         * Section for buttons
         */

        'buttons_title' => array(
          'name'  => __( 'Buttons', 'woocommerce-deposits' ),
          'type'  => 'title',
          'desc'  => __('No HTML allowed. Text will be translated to the user if a translation is available.<br/>Please note that any overflow will be hidden, since button width is theme-dependent.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_buttons_title'
        ),
        'deposits_button_deposit' => array(
          'name'  => __('Deposit Button Text', 'woocommerce-deposits'),
          'type'  => 'text',
          'desc'  => __('Text displayed in the \'Pay Deposit\' button.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_button_deposit',
          'default' => 'Pay Deposit'
        ),
        'deposits_button_full' => array(
          'name'  => __('Full Amount Button Text', 'woocommerce-deposits'),
          'type'  => 'text',
          'desc'  => __('Text displayed in the \'Full Amount\' button.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_button_full_amount',
          'default' => 'Full Amount'
        ),
        'buttons_end' => array(
          'type'  => 'sectionend',
          'id'    => 'wc_deposits_buttons_end'
        ),

        /*
         * Section for messages
         */

        'messages_title' => array(
          'name'  => __( 'Messages', 'woocommerce-deposits' ),
          'type'  => 'title',
          'desc'  => __('Please check the documentation for allowed HTML tags.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_messages_title'
        ),
        'deposits_message_deposit' => array(
          'name'  => __('Deposit Message', 'woocommerce-deposits'),
          'type'  => 'textarea',
          'desc'  => __('Message to show when \'Pay Deposit\' is selected on the product\'s page.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_message_deposit',
        ),
        'deposits_message_full' => array(
          'name'  => __('Full Amount Message', 'woocommerce-deposits'),
          'type'  => 'textarea',
          'desc'  => __('Message to show when \'Full Amount\' is selected on the product\'s page.', 'woocommerce-deposits'),
          'id'    => 'wc_deposits_message_full_amount',
        ),
        'messages_end' => array(
          'type'  => 'sectionend',
          'id'    => 'wc_deposits_messages_end'
        ),
    );

    woocommerce_admin_fields($settings);

    /*
     * Allowed gateways
     */

    $gateways_settings = array();

    $gateways_settings['gateways_title'] = array(
      'name'  => __('Disallowed Gateways', 'woocommerce-deposits'),
      'type'  => 'title',
      'desc'  => __('Disallow the following gateways when there is a deposit in the cart.', 'woocommerce-deposits'),
      'id'    => 'wc_deposits_gateways_title'
    );

    $gateways = WC()->payment_gateways()->payment_gateways();

    $group = 'start';

    foreach($gateways as $key => $gateway) {
      if ($key === 'wc-booking-gateway') // Protect the wc-booking-gateway
        continue;
      $title = $gateway->get_title();
      $gateways_settings['gateway_' . $key] = array(
        'name' => __('Disallowed For Deposits', 'woocommerce-deposits'),
        'type' => 'checkbox',
        'desc' => $title,
        'id'   => 'wc_deposits_disabled_gateways[' . $key . ']',
        'checkboxgroup' => $group,
      );
      $group = 'wc_deposits_disabled_gateways';
    }

    $gateways_settings['gateways_end'] = array(
      'type'   => 'sectionend',
      'id'     => 'wc_deposits_gateways_end'
    );

    woocommerce_admin_fields($gateways_settings);
  }

  /**
  * @brief Save all settings on POST
  *
  * @return void
  */
  public function update_options_wc_deposits()
  {
    $allowed_html = array(
      'a' => array('href' => true, 'title' => true),
      'br' => array(), 'em' => array(),
      'strong' => array(), 'p' => array(),
      's' => array(), 'strike' => array(),
      'del' => array(), 'u' => array()
    );

    $sitewide_disable = isset($_POST['wc_deposits_site_wide_disable']) ? 'yes' : 'no';
    $default_option = isset($_POST['wc_deposits_default_option']) ?
      ($_POST['wc_deposits_default_option'] === 'deposit' ? 'deposit' : 'full') : 'deposit';
    $reduce_stock = isset($_POST['wc_deposits_reduce_stock']) ?
      ($_POST['wc_deposits_reduce_stock'] === 'deposit' ? 'deposit' : 'full') : 'full';
    $display_tax = isset($_POST['wc_deposits_tax_display']) ? 'yes' : 'no';
    $deposit_payble = isset($_POST['wc_deposits_remaining_payable']) ? 'yes' : 'no';
    $button_deposit = isset($_POST['wc_deposits_button_deposit']) ? esc_html($_POST['wc_deposits_button_deposit']) : 'Pay Deposit';
    $button_full = isset($_POST['wc_deposits_button_full_amount']) ? esc_html($_POST['wc_deposits_button_full_amount']) : 'Full Amount';
    $message_deposit = isset($_POST['wc_deposits_message_deposit']) ? wp_kses($_POST['wc_deposits_message_deposit'], $allowed_html) : '';
    $message_full = isset($_POST['wc_deposits_message_full_amount']) ? wp_kses($_POST['wc_deposits_message_full_amount'], $allowed_html) : '';

    update_option('wc_deposits_site_wide_disable', $sitewide_disable);
    update_option('wc_deposits_default_option', $default_option);
    update_option('wc_deposits_tax_display', $display_tax);
    update_option('wc_deposits_remaining_payable', $deposit_payble);
    update_option('wc_deposits_button_deposit', $button_deposit); // No need for addslashes(), esc_html() takes care of it.
    update_option('wc_deposits_button_full_amount', $button_full); // Likewise.
    update_option('wc_deposits_message_deposit', $message_deposit); // No need for addslashes(), wp_kses() takes care of it.
    update_option('wc_deposits_message_full_amount', $message_full); // Likewise.
    update_option('wc_deposits_reduce_stock', $reduce_stock);

    $gateways_disabled = array();

    $gateways = WC()->payment_gateways()->payment_gateways();

    if (isset($_POST['wc_deposits_disabled_gateways'])) {
      foreach($gateways as $key => $gateway) {
        if (isset($_POST['wc_deposits_disabled_gateways'][$key]) &&
            ($_POST['wc_deposits_disabled_gateways'][$key] === 'yes' ||
             $_POST['wc_deposits_disabled_gateways'][$key] === '1' ||
             $_POST['wc_deposits_disabled_gateways'][$key] === 'on' ||
             $_POST['wc_deposits_disabled_gateways'][$key] === 'checked')) {
          $gateways_disabled[$key] = 'yes';
        } else {
          $gateways_disabled[$key] = 'no';
        }
      }
    }

    update_option('wc_deposits_disabled_gateways', $gateways_disabled);
  }

}
