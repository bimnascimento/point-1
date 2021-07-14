<?php
/*
Plugin Name: WooCommerce Deposits
Plugin URI: http://www.codebuffalo.com/
Description: Adds deposits support to WooCommerce.
Version: 1.5
Author: Abdullah Ali
Author URI: http://www.codebuffalo.com/
Text Domain: woocommerce-deposits
Domain Path: /locale

Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit;
}

require_once ('includes/wc-deposits-functions.php');

if (wc_deposits_woocommerce_is_active()) :

/**
 * @brief Main WC_Deposits class
 *
 */
class WC_Deposits {

  // Components
  public $cart;
  public $add_to_cart;
  public $orders;
  public $emails;
  public $checkout;
  public $gateways;
  public $admin_product;
  public $admin_order;
  public $admin_settings;
  public $admin_reports;

  public $admin_notices;

  /**
  * @brief Returns the global instance
  *
  * @param array $GLOBALS ...
  * @return mixed
  */
  public static function &get_singleton() {
    if (!isset($GLOBALS['wc_deposits']))
      $GLOBALS['wc_deposits'] = new WC_Deposits();
    return $GLOBALS['wc_deposits'];
  }

  /**
  * @brief Constructor
  *
  * @return void
  */
  private function __construct()
  {
    define('WC_DEPOSITS_VERSION', '1.5');
    define('WC_DEPOSITS_TEMPLATE_PATH', untrailingslashit(plugin_dir_path(__FILE__)) . '/templates/');
    define('WC_DEPOSITS_PLUGIN_URL', untrailingslashit(plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__))));
    define('WC_DEPOSITS_MAIN_FILE', __FILE__);

    add_action('init', array($this, 'load_plugin_textdomain'));
    add_action('init', array($this, 'register_order_status'));
    add_action('woocommerce_init', array($this, 'early_includes'));
    add_action('woocommerce_loaded', array($this, 'includes'));
    add_action('admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts_and_styles'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
    add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

    if (is_admin()) {
      add_action('admin_notices', array($this, 'show_admin_notices'));
      $this->admin_includes();
    }
  }

  /**
  * @brief Localisation
  *
  * @return void
  */
  public function load_plugin_textdomain() {
    load_plugin_textdomain('woocommerce-deposits', false, dirname(plugin_basename( __FILE__ )) . '/locale/');
  }

  /**
  * @brief Enqueues front-end styles
  *
  * @return void
  */
  public function enqueue_styles()
  {
    if (!$this->is_disabled())
    {
      wp_enqueue_style('toggle-switch', plugins_url('assets/css/toggle-switch.css', __FILE__ ), array(), '3.0', 'screen');
      wp_enqueue_style('wc-deposits-frontend-styles', plugins_url('assets/css/style.css', __FILE__ ));
    }
  }

  /**
  * @brief Early includes
  *
  * @since 1.3
  *
  * @return void
  */
  public function early_includes() {
    include('includes/class-wc-deposits-emails.php');
    $this->emails = new WC_Deposits_Emails($this);
  }

  /**
  * @brief Load classes
  *
  * @return void
  */
  public function includes() {
    if (!$this->is_disabled())
    {
      include('includes/class-wc-deposits-cart.php');
      include('includes/class-wc-deposits-checkout.php');
      include('includes/class-wc-deposits-add-to-cart.php');

      $this->cart = new WC_Deposits_Cart($this);
      $this->checkout = new WC_Deposits_Checkout($this);
      $this->add_to_cart = new WC_Deposits_Add_To_Cart($this);
    }
    // Always active
    include('includes/class-wc-deposits-orders.php');
    $this->orders = new WC_Deposits_Orders($this);
    include('includes/class-wc-deposits-gateways.php');
    $this->gateways = new WC_Deposits_Gateways($this);
  }

  /**
  * @brief Load front-end scripts
  *
  * @return void
  */
  public function enqueue_frontend_scripts()
  {
  }

  /**
  * @brief Load admin includes
  *
  * @return void
  */
  public function admin_includes() {
    $this->admin_notices = array();

    include('includes/admin/class-wc-deposits-admin-settings.php');
    include('includes/admin/class-wc-deposits-admin-product.php');
    include('includes/admin/class-wc-deposits-admin-order.php');

    $this->admin_settings = new WC_Deposits_Admin_Settings($this);
    $this->admin_product = new WC_Deposits_Admin_Product($this);
    $this->admin_order = new WC_Deposits_Admin_Order($this);

    add_filter('woocommerce_admin_reports', array($this, 'admin_reports'));
  }

  public function admin_reports($reports) {
    if (!$this->admin_reports) {
      $admin_reports = include('includes/admin/class-wc-deposits-admin-reports.php');
      $this->admin_reports = $admin_reports;
    }
    return $this->admin_reports->admin_reports($reports);
  }

  /**
  * @brief Load admin scripts and styles
  *
  * @return void
  */
  public function enqueue_admin_scripts_and_styles() {
    wp_enqueue_script('jquery');
    wp_enqueue_style('wc-deposits-frontend-style', plugins_url( 'assets/css/admin-style.css', __FILE__ ));
  }

  /**
  * @brief Display all buffered admin notices
  *
  * @return void
  */
  public function show_admin_notices() {
    foreach($this->admin_notices as $notice) {
      ?>
      <div class='<?php echo esc_attr($notice['type']); ?>'><p><?php _e($notice['content'], 'woocommerce-deposits'); ?></p></div>
      <?php
    }
  }

  /**
  * @brief Add a new notice
  *
  * @param $content Notice contents
  * @param $type Notice class
  *
  * @return void
  */
  public function enqueue_admin_notice($content, $type) {
    array_push($this->admin_notices, array('content' => $content, 'type' => $type));
  }

  public function is_disabled() {
    return get_option('wc_deposits_site_wide_disable') === 'yes';
  }

  /**
  * @brief Register a custom order status
  *
  * @since 1.3
  *
  * @return void
  */
  public function register_order_status() {
    if (version_compare(WC_VERSION, '2.2.0', '<'))
    {
      wp_insert_term('wc-partially-paid', 'shop_order_status');
    } else {
      register_post_status('wc-partially-paid', array(
        'label'                     => _x('Partially Paid', 'Order status', 'woocommerce-deposits'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Partially Paid <span class="count">(%s)</span>',
                                               'Partially Paid <span class="count">(%s)</span>', 'woocommerce-deposits')
      ));
    }
  }

}

// Install the singleton instance
WC_Deposits::get_singleton();

endif;
