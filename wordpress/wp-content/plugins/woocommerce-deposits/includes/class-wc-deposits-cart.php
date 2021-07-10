<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class WC_Deposits_Cart
{
  public function __construct(&$wc_deposits)
  {
    // Hook cart functionality
    add_action('woocommerce_cart_item_subtotal', array($this, 'cart_item_subtotal'), 10, 3);

    add_filter('woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 2 );
    add_filter('woocommerce_get_cart_item_from_session', array($this,'get_cart_item_from_session'), 10, 2);

    add_action('woocommerce_cart_updated', array($this, 'cart_updated'));
    add_action('woocommerce_after_cart_item_quantity_update', array($this,'after_cart_item_quantity_update'), 10, 2);

    add_filter('woocommerce_cart_total', array($this, 'cart_total'));
    add_filter('woocommerce_calculated_total', array($this, 'calculated_total'), 10, 2);

    add_action('woocommerce_cart_totals_after_order_total', array($this, 'cart_totals_after_order_total'));
  }

  private function update_deposit_meta($product, $quantity, &$cart_item_data)
  {
    if ($product->wc_deposits_enable_deposit === 'yes' && isset($cart_item_data['deposit']) &&
        $cart_item_data['deposit']['enable'] === 'yes')
    {
      $deposit_amount = $product->wc_deposits_deposit_amount;
      $deposit = $deposit_amount;

      if ($product->is_type('booking')) {
        $amount = $cart_item_data['booking']['_cost'];
        if ($product->has_persons() && $product->wc_deposits_enable_per_person == 'yes')
        {
          $persons = array_sum($cart_item_data['booking']['_persons']);
          if ($product->wc_deposits_amount_type === 'fixed') {
            $deposit = $deposit_amount * $persons;
          } else { // percent
            $deposit = $deposit_amount / 100.0 * $amount;
          }
        } else {
          if ($product->wc_deposits_amount_type === 'percent') {
            $deposit = $deposit_amount / 100.0 * $amount;
          }
        }
      } else {
        if ($product->is_type('variable')) {
          $amount = $cart_item_data['line_subtotal'];
        } else {
          $amount = $product->get_price_excluding_tax($quantity);
        }
        if ($product->wc_deposits_amount_type === 'fixed') {
          $deposit = $deposit * $quantity;
        } else {
          $deposit = $amount * ($deposit_amount / 100.0);
        }
      }

      if ($deposit < $amount && $deposit > 0) {
        $cart_item_data['deposit']['deposit'] = $deposit;
        $cart_item_data['deposit']['remaining'] = $amount - $deposit;
        $cart_item_data['deposit']['total'] = $amount;
      } else {
        $cart_item_data['deposit']['enable'] = 'no';
      }
    }
  }

  public function get_cart_item_from_session($cart_item, $values)
  {
    if (!empty($values['deposit'])) {
      $cart_item['deposit'] = $values['deposit'];
    }
    return $cart_item;
  }

  public function add_cart_item($cart_item, $cart_item_key)
  {
    $product = $cart_item['data'];

    if ($product->wc_deposits_enable_deposit === 'yes' && !empty($cart_item['deposit']) && $cart_item['deposit']['enable'] === 'yes')
    {
      $this->update_deposit_meta($product, $cart_item['quantity'], $cart_item);
    }

    return $cart_item;
  }

  public function cart_updated()
  {
    foreach(WC()->cart->cart_contents as $cart_item_key => &$cart_item) {
      $this->update_deposit_meta($cart_item['data'], $cart_item['quantity'], $cart_item);
    }
  }

  public function after_cart_item_quantity_update($cart_item_key, $quantity)
  {
    $product = WC()->cart->cart_contents[$cart_item_key]['data'];
    $this->update_deposit_meta($product, $quantity, WC()->cart->cart_contents[$cart_item_key]);
  }

  /**
  * @brief Hook the subtotal display and show the deposit and remaining amount
  *
  * @param string $subtotal ...
  * @param array $cart_item ...
  * @param mixed $cart_item_key ...
  * @return string
  */
  public function cart_item_subtotal($subtotal, $cart_item, $cart_item_key)
  {
    $product = $cart_item['data'];

    if ($product->wc_deposits_enable_deposit === 'yes' && !empty($cart_item['deposit']) && $cart_item['deposit']['enable'] === 'yes')
    {
      $tax = get_option('wc_deposits_tax_display', 'no') === 'yes' ?  $product->get_price_including_tax($cart_item['quantity']) -
        $product->get_price_excluding_tax($cart_item['quantity']) : 0;
      $deposit = $cart_item['deposit']['deposit'];
      $remaining = $cart_item['deposit']['remaining'];

      return woocommerce_price($deposit + $tax) . ' ' . __('Deposit', 'woocommerce-deposits') . '<br/>(' .
             woocommerce_price($remaining) . ' ' . __('Remaining', 'woocommerce-deposits') . ')';
    } else {
      return $subtotal;
    }
  }


  public function cart_total($cart_total)
  {
    $cart = WC()->cart;
    $total = $cart->total + $cart->deposit_remaining;
    return woocommerce_price($total);
  }

  /**
  * @brief Calculate cart total
  *
  * @param mixed $cart_total ...
  * @param mixed $cart ...
  *
  * @return float
  */
  public function calculated_total($cart_total, $cart)
  {
    $cart_original = $cart_total;
    $deposit_upfront = 0;
    $deposit_remaining = 0;
    $deposit_total = 0;

    $items = array();

    foreach($cart->cart_contents as $cart_item_key => &$cart_item) {
      if (isset($cart_item['deposit']) && $cart_item['deposit']['enable'] === 'yes')
      {
        $this->update_deposit_meta($cart_item['data'], $cart_item['quantity'], $cart_item);
        $deposit_upfront += $cart_item['deposit']['deposit'];
        $deposit_remaining += $cart_item['deposit']['remaining'];
        $deposit_total += $cart_item['deposit']['total'];
        $items[] = &$cart_item;
      }
    }

    if ($deposit_total > 0) {
      foreach ($items as &$item) {
        $item['deposit']['ratio'] = (double)$item['deposit']['total'] / (double)$deposit_total;
      }
    }

    if ($deposit_remaining > 0) {
      $cart_total -= $deposit_remaining;

      $fees = $cart->tax_total + $cart->shipping_tax_total + $cart->shipping_total + $cart->fee_total;

      $min_amount = $deposit_upfront + $fees > $cart_total ? $deposit_upfront + $fees : $cart_total;

      if ($cart_total < $min_amount) {
        $difference = abs($min_amount - $cart_total);

        if ($difference > $deposit_remaining) $difference = $deposit_remaining;

        if ($difference > 0) {
          foreach($items as &$item) {
            $item['deposit']['remaining'] -= $difference * $item['deposit']['ratio'];
            if ($item['deposit']['remaining'] < 0) {
              $item['deposit']['remaining'] = 0;
            }
          }

          $deposit_remaining -= $difference;
          $cart_total += $difference;
        }
      }
    }

    $cart->deposit_remaining = $deposit_remaining;

    return $cart_total;
  }

  public function deposit_paid_html()
  {
    $paid = WC()->cart->total;
    echo '<strong>' . woocommerce_price($paid) . '</strong>';
  }

  public function deposit_remaining_html()
  {
    $remaining = 0;
    if (isset(WC()->cart->deposit_remaining))
      $remaining = WC()->cart->deposit_remaining;
    echo '<strong>' . woocommerce_price($remaining) . '</strong>';
  }

  public function cart_totals_after_order_total()
  {
    ?>
    <tr class="order-paid">
      <th><?php _e('To Pay', 'woocommerce-deposits'); ?></th>
      <td><?php $this->deposit_paid_html(); ?></td>
    </tr>
    <tr class="order-remaining">
      <th><?php _e('Remaining Amount', 'woocommerce-deposits'); ?></th>
      <td><?php $this->deposit_remaining_html(); ?></td>
    </tr>
    <?php
  }
}
