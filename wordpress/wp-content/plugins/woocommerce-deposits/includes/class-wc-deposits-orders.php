<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}


class WC_Deposits_Orders
{
  public function __construct(&$wc_deposits)
  {
    // Payment complete events
    add_action('woocommerce_order_status_completed', array($this, 'order_status_completed'));
    add_action('woocommerce_pre_payment_complete', array($this, 'pre_payment_complete'));
    add_filter('woocommerce_payment_complete_reduce_order_stock', array($this, 'payment_complete_reduce_order_stock'), 10, 2);

    // Order statuses
    add_filter('wc_order_statuses', array($this, 'order_statuses'));
    add_filter('wc_order_is_editable', array($this, 'order_is_editable'), 10, 2);
    add_filter('woocommerce_payment_complete_order_status', array($this, 'payment_complete_order_status'), 10, 2);
    add_filter('woocommerce_valid_order_statuses_for_payment', array($this, 'valid_order_statuses_for_payment'), 10, 2);
    add_filter('woocommerce_valid_order_statuses_for_payment_complete', array($this, 'valid_order_statuses_for_payment_complete'), 10, 2);
    add_filter('woocommerce_order_has_status', array($this, 'order_has_status'), 10, 3);

    // Order handling
    add_action('woocommerce_add_order_item_meta', array($this, 'add_order_item_meta'), 10, 3);
    add_filter('woocommerce_get_order_item_totals', array($this, 'get_order_item_totals'), 10, 2);
    add_filter('woocommerce_order_formatted_line_subtotal', array($this, 'order_formatted_line_subtotal'), 10, 3);
    add_filter('woocommerce_get_formatted_order_total', array($this, 'get_formatted_order_total'), 10, 2);
    add_filter('woocommerce_order_amount_total', array($this, 'order_amount_total'), 10, 2);
    add_filter('woocommerce_order_amount_total_shipping', array($this, 'order_amount_total_shipping'), 10, 2);
    add_filter('woocommerce_order_amount_shipping_tax', array($this, 'order_amount_shipping_tax'), 10, 2);
    add_filter('woocommerce_order_amount_order_discount', array($this, 'order_amount_order_discount'), 10, 2);
    add_filter('woocommerce_order_amount_cart_discount', array($this, 'order_amount_cart_discount'), 10, 2);
    add_filter('woocommerce_order_amount_item_subtotal', array($this, 'order_amount_item_subtotal'), 10, 3);
    add_filter('woocommerce_order_amount_item_total', array($this, 'order_amount_item_total'), 10, 5);
    add_filter('woocommerce_order_get_items', array($this, 'order_get_items'), 10, 2);
  }

  public function order_status_completed($order_id) {
    $order = wc_get_order($order_id);

    if (isset($order->wc_deposits_remaining) && $order->wc_deposits_remaining > 0) {
      update_post_meta($order_id, '_wc_deposits_remaining_paid', 'yes');
      update_post_meta($order_id, '_order_total', $order->wc_deposits_remaining + $order->order_total);
      update_post_meta($order_id, '_wc_deposits_remaining', 0);
    }
  }

  public function pre_payment_complete($order_id)
  {
    $order = wc_get_order($order_id);
    $status = $order->get_status();

    if ($status === 'partially-paid' && $order->wc_deposits_remaining_paid !== 'yes') {
      update_post_meta($order_id, '_wc_deposits_remaining_paid', 'yes');
      update_post_meta($order_id, '_order_total', $order->wc_deposits_remaining + $order->order_total);
      update_post_meta($order_id, '_wc_deposits_remaining', 0);
    }
  }

  public function payment_complete_reduce_order_stock($reduce, $order_id) {
    $order = wc_get_order($order_id);
    $status = $order->get_status();
    $reduce_on = get_option('wc_deposits_reduce_stock', 'full');

    if ($status === 'partially-paid' && $reduce_on === 'full') {
      $reduce = false;
    }
    else if ($status !== 'partially-paid' && $reduce_on === 'deposit') {
      $reduce = false;
    }

    return $reduce;
  }

  public function payment_complete_order_status($new_status, $order_id)
  {
    $order = wc_get_order($order_id);
    $status = $order->get_status();

    if ($status !== 'partially-paid')
    {
      $remaining = $order->wc_deposits_remaining;
      $remaining_paid = $order->wc_deposits_remaining_paid;

      if ($remaining > 0 && $remaining_paid !== 'yes') {
        $new_status = 'partially-paid';
      }
    }

    return $new_status;
  }

  public function order_is_editable($editable, $order) {
    if ($order->has_status('partially-paid')) {
      $editable = false;
    }
    return $editable;
  }

  public function valid_order_statuses_for_payment($statuses, $order)
  {
    if (get_option('wc_deposits_remaining_payable', 'yes') === 'yes') {
      $statuses[] = 'partially-paid';
    }
    return $statuses;
  }

  public function valid_order_statuses_for_payment_complete($statuses, $order)
  {
    if (get_option('wc_deposits_remaining_payable', 'yes') === 'yes') {
      $statuses[] = 'partially-paid';
    }
    return $statuses;
  }

  /**
  * @brief Add the new 'Deposit paid' status to orders
  *
  * @return array
  */
  public function order_statuses($order_statuses) {
    $new_statuses = array();
    // Place the new status after 'Pending payment'
    foreach($order_statuses as $key => $value) {
      $new_statuses[$key] = $value;
      if ($key === 'wc-pending') {
        $new_statuses['wc-partially-paid'] = _x('Partially Paid', 'Order status', 'woocommerce-deposits');
      }
    }
    return $new_statuses;
  }

  public function order_has_status($has_status, $order, $status) {
    if ($order->get_status() === 'partially-paid') {
      if (is_array($status)) {
        if (in_array('pending', $status)) {
          $has_status = true;
        }
      } else {
        if ($status === 'pending') {
          $has_status = true;
        }
      }
    }
    return $has_status;
  }

  public function add_order_item_meta($item_id, $values, $cart_item_key)
  {
    $cart_item = WC()->cart->cart_contents[$cart_item_key];
    if (is_array($cart_item) && isset($cart_item['deposit'])) {
      woocommerce_add_order_item_meta($item_id, '_wc_deposit_meta', $cart_item['deposit']);
    }
  }

  public function get_order_item_totals($total_rows, $order)
  {
    $status = $order->get_status();
    $paid_today = $order->order_total;
    $remaining = $order->wc_deposits_remaining;
    $order_total = $paid_today + $remaining;
    $is_checkout = false;

    $received_slug = get_option('woocommerce_checkout_order_received_endpoint', 'order-received');
    $is_checkout = get_query_var($received_slug) === '' && is_checkout();

    if ($status === 'partially-paid' && $is_checkout && $remaining > 0) {
      $paid_today = $remaining;
      $remaining = 0;
    }

    $paid_today_label = $is_checkout || $status === 'pending' ?
      __('To Pay:', 'woocommerce-deposits') : __('Amount Paid:', 'woocommerce-deposits');

    $total_rows['order_total'] = array(
      'label' => __('Order Total:', 'woocommerce-deposits'),
      'value' => woocommerce_price($order_total, array('currency' => $order->get_order_currency()))
    );

    $total_rows['paid_today'] = array(
      'label' => $paid_today_label,
      'value' => woocommerce_price($paid_today, array('currency' => $order->get_order_currency()))
    );

    $total_rows['remaining_amount'] = array(
      'label' => __('Remaining Amount:', 'woocommerce-deposits'),
      'value' => woocommerce_price($remaining, array('currency' => $order->get_order_currency()))
    );

    return $total_rows;
  }

  public function order_formatted_line_subtotal($subtotal, $item, $order)
  {
    if (isset($item['wc_deposit_meta'])) {
      $deposit_meta = maybe_unserialize($item['wc_deposit_meta']);
    } else {
      return $subtotal;
    }

    if (is_array($deposit_meta) && isset($deposit_meta['enable']) && $deposit_meta['enable'] === 'yes') {
      $tax = get_option('wc_deposits_tax_display', 'no') === 'yes' ? floatval($item['line_tax']) : 0;
      $deposit = $deposit_meta['deposit'] + $tax;
      $remaining = $deposit_meta['remaining'];

      return woocommerce_price($deposit, array('currency' => $order->get_order_currency())) . ' ' . __('Deposit', 'woocommerce-deposits') . '<br/>(' .
             woocommerce_price($remaining, array('currency' => $order->get_order_currency())) . ' ' . __('Remaining', 'woocommerce-deposits') . ')';
    } else {
      return $subtotal;
    }
  }

  public function get_formatted_order_total($total, $order)
  {
    $remaining = $order->wc_deposits_remaining;
    if ($remaining && floatval($remaining) > 0)
    {
      $total = woocommerce_price(floatval($remaining) + floatval($order->order_total), array('currency' => $order->get_order_currency()));
      $unpaid = woocommerce_price(floatval($remaining), array('currency' => $order->get_order_currency()));
      if ($order->get_status() == 'partially-paid') {
        $total = sprintf(__('%s (%s unpaid)', 'woocommerce-deposits'), $total, $unpaid);
      } else {
        $total = sprintf(__('%s', 'woocommerce-deposits'), $total);
      }

    }
    return $total;
  }

  public function order_amount_total($total, $order) {
    $status = $order->get_status();
    $remaining = $order->wc_deposits_remaining;
    $remaining_paid = $order->wc_deposits_remaining_paid;

    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
      return round($remaining, 2);

    return $total;
  }

  public function order_amount_total_shipping($total, $order) {
    $status = $order->get_status();
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
      return 0;

    return $total;
  }

  public function order_amount_shipping_tax($tax, $order) {
    $status = $order->get_status();
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
      return 0;

    return $tax;
  }

  public function order_amount_order_discount($discount, $order) {
    $status = $order->get_status();
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
      return 0;

    return $discount;
  }

  public function order_amount_cart_discount($discount, $order) {
    $status = $order->get_status();
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
      return 0;

    return $discount;
  }

  public function order_amount_item_subtotal($price, $order, $item) {

    $status = $order->get_status();

    if (isset($item['wc_deposit_meta'])) {
      $deposit_meta = maybe_unserialize($item['wc_deposit_meta']);
    } else {
      return $price;
    }

    if (isset($deposit_meta) && isset($deposit_meta['enable']) && $deposit_meta['enable'] === 'yes') {
      if ($status === 'partially-paid')
      {
        $price = floatval($deposit_meta['remaining']) / $item['qty'];
      } else {
        $price = floatval($deposit_meta['deposit']) / $item['qty'];
      }
      $price = round($price, 2);
    } else if ($status === 'partially-paid') {
      $price = 0; // ensure that fully paid items are not paid for yet again.
    }

    return $price;
  }

  public function order_amount_item_total($price, $order, $item, $inc_tax, $round) {
    if ($item) { // see https://github.com/woothemes/woocommerce/issues/7718
      $status = $order->get_status();

      if (isset($item['wc_deposit_meta'])) {
        $deposit_meta = maybe_unserialize($item['wc_deposit_meta']);
      } else {
        return $price;
      }

      if (isset($deposit_meta) && isset($deposit_meta['enable']) && $deposit_meta['enable'] === 'yes') {
        if ($status === 'partially-paid')
        {
          $price = floatval($deposit_meta['remaining']) / $item['qty'];
        } else {
          if ($inc_tax) {
            $price = (floatval($deposit_meta['deposit']) + $item['line_tax']) / $item['qty'];
          } else {
            $price = (floatval($deposit_meta['deposit'])) / $item['qty'];
          }
        }
        $price = $round ? round($price, 2) : $price;
      } else if ($status === 'partially-paid') {
        $price = 0; // ensure that fully paid items are not paid for yet again.
      }
    }
    return $price;
  }

  public function order_get_items($items, $order) {
    $status = $order->get_status();
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($status === 'partially-paid' && !$is_order_editor)
    {
      // remove everything that's not a line item with a remaining deposit, including fees
      $temp = array();
      foreach($items as $item) {
        if (isset($item) && isset($item['type']) && $item['type'] === 'line_item' && isset($item['wc_deposit_meta'])) {
          $deposit_meta = maybe_unserialize($item['wc_deposit_meta']);
          if (isset($deposit_meta) && isset($deposit_meta['enable']) && $deposit_meta['enable'] === 'yes') {
            $item['line_tax'] = 0;
            $item['line_total'] = round($deposit_meta['remaining'], 2);
            $temp[] = $item;
          }
        }
      }
      $items = $temp;
    }

    return $items;
  }
}
