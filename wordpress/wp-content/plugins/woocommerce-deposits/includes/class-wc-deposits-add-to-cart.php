<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

class WC_Deposits_Add_To_Cart
{
  public function __construct(&$wc_deposits)
  {
    // Add the required styles
    add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_inline_styles'));

    // Hook the add to cart form
    add_action('woocommerce_before_add_to_cart_button', array($this, 'before_add_to_cart_button'));
    add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 2);
  }

  /**
  * @brief Load the deposit-switch logic
  *
  * @return void
  */
  public function enqueue_scripts()
  {
    if (is_product()) {
      global $post;
      $product = wc_get_product($post->ID);

      if ($product && isset($product->wc_deposits_enable_deposit) && $product->wc_deposits_enable_deposit === 'yes')
      {
        wp_enqueue_script('wc-deposits-add-to-cart', WC_DEPOSITS_PLUGIN_URL . '/assets/js/add-to-cart.js');

        $message_deposit = get_option('wc_deposits_message_deposit');
        $message_full_amount = get_option('wc_deposits_message_full_amount');

        $message_deposit = stripslashes($message_deposit);
        $message_full_amount = stripslashes($message_full_amount);

        $script_args = array(
          'message' => array(
            'deposit' => __($message_deposit, 'woocommerce-deposits'),
            'full' => __($message_full_amount, 'woocommerce-deposits')
          )
        );

        if ($product->is_type('variable') && $product->wc_deposits_amount_type !== 'fixed') {
          foreach($product->get_children() as $variation_id) {
            $variation = $product->get_child($variation_id);
            if (!is_object($variation)) continue;
            $tax = get_option('wc_deposits_tax_display', 'no') === 'yes' ?
              $variation->get_price_including_tax() - $variation->get_price_excluding_tax() : 0;
            $amount = woocommerce_price($variation->get_price_excluding_tax() *
              ($product->wc_deposits_deposit_amount / 100.0) + $tax);
            $script_args['variations'][$variation_id] = array($amount);
          }
        }

        wp_localize_script('wc-deposits-add-to-cart', 'wc_deposits_add_to_cart_options', $script_args);
      }
    }
  }

  /**
  * @brief Enqueues front-end styles
  *
  * @return void
  */
  public function enqueue_inline_styles()
  {
    if (is_product()) {
      global $post;
      $product = wc_get_product($post->ID);

      if ($product && isset($product->wc_deposits_enable_deposit) && $product->wc_deposits_enable_deposit === 'yes')
      {
        // prepare inline styles
        $colors = wc_deposits_woocommerce_frontend_colours();
        $gstart = $colors['primary'];
        $gend = wc_deposits_adjust_colour($colors['primary'], 15);

        $style = "@media only screen {
            #wc-deposits-options-form input.input-radio:enabled ~ label { color: {$colors['secondary']}; }
            #wc-deposits-options-form div a.wc-deposits-switcher {
              background-color: {$colors['primary']};
              background: -moz-gradient(center top, {$gstart} 0%, {$gend} 100%);
              background: -moz-linear-gradient(center top, {$gstart} 0%, {$gend} 100%);
              background: -webkit-gradient(linear, left top, left bottom, from({$gstart}), to({$gend}));
              background: -webkit-linear-gradient({$gstart}, {$gend});
              background: -o-linear-gradient({$gstart}, {$gend});
              background: linear-gradient({$gstart}, {$gend});
            }
            #wc-deposits-options-form .amount { color: {$colors['highlight']}; }
            #wc-deposits-options-form .deposit-option { display: inline; }
          }";

        wp_add_inline_style('wc-deposits-frontend-styles', $style);
      }
    }
  }

  public function before_add_to_cart_button()
  {
    global $product;
    if ($product && isset($product->wc_deposits_enable_deposit) && $product->wc_deposits_enable_deposit === 'yes')
    {
      $tax = get_option('wc_deposits_tax_display', 'no') === 'yes' ?
        $product->get_price_including_tax() - $product->get_price_excluding_tax() : 0;
      if ($product->wc_deposits_amount_type === 'fixed') {
        $amount = woocommerce_price($product->wc_deposits_deposit_amount + $tax);
        if ($product->is_type('booking') && $product->has_persons() && $product->wc_deposits_enable_per_person === 'yes') {
          $suffix = __('per person', 'woocommerce-deposits');
        } else if ($product->is_type('booking')) {
          $suffix = __('per booking', 'woocommerce-deposits');
        } else if (!$product->is_sold_individually()) {
          $suffix = __('per item', 'woocommerce-deposits');
        } else {
          $suffix = '';
        }
      } else {
        if ($product->is_type('booking')) {
          $amount = '<span class=\'amount\'>' . round($product->wc_deposits_deposit_amount, 2) . '%' . '</span>';
          $suffix = __('of total value', 'woocommerce-deposits');
        } else if ($product->is_type('variable')) {
          $min_variation = $product->get_child($product->min_price_variation_id);
          $max_variation = $product->get_child($product->max_price_variation_id);
          $tax_min = get_option('wc_deposits_tax_display', 'no') === 'yes' ? $min_variation->get_price_including_tax() - $min_variation->get_price_excluding_tax() : 0;
          $tax_max = get_option('wc_deposits_tax_display', 'no') === 'yes' ? $max_variation->get_price_including_tax() - $max_variation->get_price_excluding_tax() : 0;
          $amount_min = woocommerce_price($min_variation->get_price_excluding_tax() * ($product->wc_deposits_deposit_amount / 100.0) + $tax_min);
          $amount_max = woocommerce_price($max_variation->get_price_excluding_tax() * ($product->wc_deposits_deposit_amount / 100.0) + $tax_max);
          $amount = $amount_min . '&nbsp;&ndash;&nbsp;' . $amount_max;
        } else {
          $amount = woocommerce_price($product->get_price_excluding_tax() * ($product->wc_deposits_deposit_amount / 100.0) + $tax);
        }
        if (!$product->is_sold_individually()) {
          $suffix = __('per item', 'woocommerce-deposits');
        } else {
          $suffix = '';
        }
      }
      $default = get_option('wc_deposits_default_option', 'deposit');
      $deposit_checked = ($default === 'deposit' ? 'checked=\'checked\'' : '');
      $full_checked = ($default === 'full' ? 'checked=\'checked\'' : '');
      $deposit_text = get_option('wc_deposits_button_deposit');
      $full_text = get_option('wc_deposits_button_full_amount');
      if ($deposit_text === false) $deposit_text = __('Pay Deposit', 'woocommerce-deposits');
      if ($full_text === false) $full_text = __('Full Amount', 'woocommerce-deposits');
      $deposit_text = stripslashes($deposit_text);
      $full_text = stripslashes($full_text);
      ?>
        <div id='wc-deposits-options-form'>
          <hr class='separator' />
          <label class='deposit-option'>
            <?php echo __('Deposit Option:', 'woocommerce-deposits'); ?>
            <span id='deposit-amount'><?php echo $amount; ?></span>
            <span id='deposit-suffix'><?php echo $suffix; ?></span>
          </label>
          <div class="deposit-options switch-toggle switch-candy switch-woocommerce-deposits">
            <input id='pay-deposit' name='deposit-radio' type='radio' <?php echo $deposit_checked; ?> class='input-radio' value='deposit'>
            <label for='pay-deposit' onclick=''><span><?php _e($deposit_text, 'woocommerce-deposits'); ?></span></label>
          <?php if (isset($product->wc_deposits_force_deposit) && $product->wc_deposits_force_deposit === 'yes') { ?>
            <input id='pay-full-amount' name='deposit-radio' type='radio' class='input-radio' disabled>
            <label for='pay-full-amount' onclick=''><span><?php _e($full_text, 'woocommerce-deposits'); ?></span></label>
          <?php } else { ?>
            <input id='pay-full-amount' name='deposit-radio' type='radio' <?php echo $full_checked; ?> class='input-radio' value='full'>
            <label for='pay-full-amount' onclick=''><span><?php _e($full_text, 'woocommerce-deposits'); ?></span></label>
          <?php } ?>
            <a class='wc-deposits-switcher'></a>
          </div>
          <span class='deposit-message' id='wc-deposits-notice'></span>
        </div>
      <?php
    }
  }

  public function add_cart_item_data($cart_item_meta, $product_id)
  {
    $product = wc_get_product($product_id);
    if ($product->wc_deposits_enable_deposit === 'yes')
    {
      if (!isset($_POST['deposit-radio'])) {
        $default = get_option('wc_deposits_default_option');
        $_POST['deposit-radio'] = $default ? $default : 'deposit';
      }
      $cart_item_meta['deposit'] = array(
        'enable' => $product->wc_deposits_force_deposit === 'yes' ? 'yes' : ($_POST['deposit-radio'] === 'full' ? 'no' : 'yes')
      );
    }
    return $cart_item_meta;
  }
}

