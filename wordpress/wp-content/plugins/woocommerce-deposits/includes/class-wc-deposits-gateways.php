<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * @brief Handle specific gateways
 *
 */
class WC_Deposits_Gateways
{
  public function __construct(&$wc_deposits) {
    add_filter('woocommerce_paypal_args', array($this, 'paypal_args'));
  }

  public function paypal_args($args) {
    $custom = maybe_unserialize($args['custom']);
    if (is_array($custom)) {
      list($order_id, $order_key) = $custom;
      $order = wc_get_order($order_id);
      if ($order) {
        if ($order->get_status() === 'partially-paid') {
          $args['invoice'] = $args['invoice'] . '-WCDP';
          unset($args['tax_cart']);
          unset($args['discount_amount_cart']);
        }
      }
    }
    return $args;
  }

}
