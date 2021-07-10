<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * @brief Adds UI elements to the order editor in the admin area
 *
 */
class WC_Deposits_Admin_Order {
  public function __construct(&$wc_deposits)
  {
    // Hook the order admin page
    add_action('woocommerce_admin_order_item_headers', array($this, 'admin_order_item_headers'));
    add_action('woocommerce_admin_order_totals_after_total', array($this, 'admin_order_totals_after_total'));
    add_action('woocommerce_admin_order_item_values', array($this, 'admin_order_item_values'), 10, 3);
    add_action('woocommerce_saved_order_items', array($this, 'saved_order_items'), 10, 2);
    add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
  }

  function enqueue_scripts() {
    $is_order_editor = false;

    if (function_exists('get_current_screen')) {
      $screen = get_current_screen();
      if ($screen) $is_order_editor = $screen->id === 'shop_order';
    }

    if ($is_order_editor) {
      wp_enqueue_script('jquery.bind-first', WC_DEPOSITS_PLUGIN_URL . '/assets/js/jquery.bind-first-0.2.3.min.js');
      wp_enqueue_script('wc-deposits-admin-orders', WC_DEPOSITS_PLUGIN_URL . '/assets/js/admin/admin-orders.js');
    }
  }

  public function admin_order_item_headers()
  {
    ?>
    <th class="deposit-paid"><?php _e('Paid', 'woocommerce-deposits'); ?></th>
    <th class="deposit-remaining"><?php _e('Remaining', 'woocommerce-deposits'); ?></th>
    <?php
  }

  public function admin_order_item_values($product, $item, $item_id)
  {
    global $post;
    $order = $post ? wc_get_order($post->ID) : null;
    $item_meta = array();
    $paid = '';
    $remaining = '';
    $price_args = array();
    if ($order) {
      $price_args = array('currency', $order->get_order_currency());
    }
    if ($product && isset($item['wc_deposit_meta'])) {
      $item_meta = maybe_unserialize($item['wc_deposit_meta']);
      if (is_array($item_meta) && isset($item_meta['deposit']))
        $paid = $item_meta['deposit'];
      if (is_array($item_meta) && isset($item_meta['remaining']))
        $remaining = $item_meta['remaining'];
    }
    ?>
    <td class="deposit-paid" width="1%">
      <div class="view">
        <?php
          if ($paid)
            echo woocommerce_price($paid, $price_args);
        ?>
      </div>
      <?php if ($product) { ?>
      <div class="edit" style="display: none;">
        <input type="text" name="deposit_paid[<?php echo absint($item_id); ?>]" placeholder="<?php echo wc_format_localized_price(0); ?>" value="<?php echo $paid; ?>" class="deposit_paid wc_input_price" data-total="<?php echo $paid; ?>" />
      </div>
      <?php } ?>
    </td>
    <td class="deposit-remaining" width="1%">
      <div class="view">
        <?php
          if ($remaining)
            echo woocommerce_price($remaining, $price_args);
        ?>
      </div>
      <?php if ($product) { ?>
      <div class="edit" style="display: none;">
        <input type="text" name="deposit_remaining[<?php echo absint($item_id); ?>]" placeholder="<?php echo wc_format_localized_price(0); ?>" value="<?php echo $remaining; ?>" class="deposit_remaining wc_input_price" data-total="<?php echo $remaining; ?>" />
      </div>
      <?php } ?>
    </td>
    <?php
  }

  public function admin_order_totals_after_total($order_id)
  {
    $order = wc_get_order($order_id);
    $remaining = get_post_meta($order_id, '_wc_deposits_remaining', true);
    ?>
      <tr>
        <td class="label"><?php _e('Order Remaining', 'woocommerce-deposits'); ?>:</td>
        <td class="remaining">
          <div class="view"><?php echo wc_price($remaining, array('currency' => $order->get_order_currency())); ?></div>
          <div class="edit" style="display: none;">
            <input type="text" class="wc_input_price" id="_order_remaining" name="_order_remaining" placeholder="<?php echo wc_format_localized_price(0); ?>" value="<?php echo (isset($remaining) ) ? esc_attr(wc_format_localized_price($remaining)) : ''; ?>" />
            <div class="clear"></div>
          </div>
        </td>
        <td>
        <?php if ($order->is_editable()) : ?><div class="wc-order-edit-line-item-actions"><a class="edit-order-item" href="#"></a></div><?php endif; ?>
        </td>
      </tr>
    <?php
  }

  public function saved_order_items($order_id, $items) {
    if (isset($items['order_item_id'])) {
      $deposit_paid = isset($items['deposit_paid']) ? $items['deposit_paid'] : array();
      $deposit_remaining = isset($items['deposit_remaining']) ? $items['deposit_remaining'] : array();
      foreach($items['order_item_id'] as $item_id) {
        $meta = array();
        $paid = isset($deposit_paid[$item_id]) ? floatval($deposit_paid[$item_id]) : null;
        $total = wc_get_order_item_meta($item_id, '_line_total');
        if ($paid !== null && floatval($paid) >= 0 && floatval($paid) <= floatval($total)) {
          $meta['deposit'] = floatval($paid);
          $meta['remaining'] = floatval($total) - floatval($paid);
        }
        if ($paid !== null && $paid > 0) {
          $meta['enable'] = 'yes';
        } else {
          $meta['enable'] = 'no';
        }
        wc_update_order_item_meta($item_id, 'wc_deposit_meta', $meta);
      }
    }
    $order_remaining = 0;
    if (isset($items['_order_remaining'])) {
      $order_remaining = floatval($items['_order_remaining']);
    }
    update_post_meta($order_id, '_wc_deposits_remaining', $order_remaining);
  }
}
