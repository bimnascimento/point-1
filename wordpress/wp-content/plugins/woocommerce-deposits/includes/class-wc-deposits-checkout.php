<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class WC_Deposits_Checkout
{
  public $wc_deposits;
  public $checkout;

  public function __construct(&$wc_deposits)
  {
    $this->wc_deposits = $wc_deposits;

    add_action('woocommerce_review_order_after_order_total', array($this, 'review_order_after_order_total'));
    add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'), 10, 2);

    // Hook the payments gateways filter to remove the ones we don't want
    add_filter('woocommerce_available_payment_gateways', array($this, 'available_payment_gateways'));
  }

  public function review_order_after_order_total($order)
  {
    ?>
    <tr class="order-paid">
      <th><?php _e('To Pay', 'woocommerce-deposits'); ?></th>
      <td><?php $this->wc_deposits->cart->deposit_paid_html(); ?></td>
    </tr>
    <tr class="order-remaining">
      <th><?php _e('Remaining Amount', 'woocommerce-deposits'); ?></th>
      <td><?php $this->wc_deposits->cart->deposit_remaining_html(); ?></td>
    </tr>
    <?php
  }

  /**
  * @brief Updates the order metadata with deposit information
  *
  * @return void
  */
  public function checkout_order_processed($order_id, $posted)
  {
    $remaining = WC()->cart->deposit_remaining;

    if ($remaining > 0) {
      update_post_meta($order_id, '_wc_deposits_remaining', $remaining);
      update_post_meta($order_id, '_wc_deposits_remaining_paid', 'no');
    }
  }

  /**
  * @brief Removes the unwanted gateways from the settings page when there's a deposit
  *
  * @return mixed
  */
  public function available_payment_gateways($gateways)
  {
    $has_deposit = false;

    $pay_slug = get_option('woocommerce_checkout_pay_endpoint', 'order-pay');
    $order_id = absint(get_query_var($pay_slug));

    if ($order_id > 0) {
      $order = wc_get_order($order_id);
      if ($order) {
        $has_deposit = $order->has_status('partially-paid');
      }
      if (!$has_deposit) {
        $items = $order->get_items();
        foreach($items as $item_key => $item) {
          if (isset($item['wc_deposit_meta'])) {
            $meta = maybe_unserialize($item['wc_deposit_meta']);
            if ($meta && isset($meta['enable']) && $meta['enable'] === 'yes') {
              $has_deposit = true;
              break;
            }
          }
        }
      }
    } else {
      foreach(WC()->cart->cart_contents as $cart_item_key => $cart_item) {
        if (is_array($cart_item['deposit']) &&
            isset($cart_item['deposit']['enable']) &&
            $cart_item['deposit']['enable'] === 'yes')
        {
          $has_deposit = true;
          break;
        }
      }
    }

    if ($has_deposit) {
      $disallowed_gateways = get_option('wc_deposits_disabled_gateways');
      if (is_array($disallowed_gateways))
      {
        foreach($disallowed_gateways as $key => $value) {
          if ($value === 'yes') {
            unset($gateways[$key]);
          }
        }
      }
    }
    return $gateways;
  }
}
