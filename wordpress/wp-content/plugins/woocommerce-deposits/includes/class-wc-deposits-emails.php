<?php
/*Copyright: © 2014 Abdullah Ali.
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

/**
 * @brief Handles email notifications
 *
 * @since 1.3
 *
 */
class WC_Deposits_Emails
{

  public $actions = array();

  public function __construct(&$wc_deposits) {

    $email_actions = array(
      array(
        'from' => array('pending', 'on-hold', 'failed', 'draft'),
        'to' => array('partially-paid')
      ),
      array(
        'from' => array('partially-paid'),
        'to' => array('processing', 'completed', 'on-hold')
      )
    );

    foreach($email_actions as $action) {
      foreach($action['from'] as $from) {
        foreach($action['to'] as $to) {
          $this->actions[] = 'woocommerce_order_status_' . $from . '_to_' . $to;
        }
      }
    }

    $this->actions = array_unique($this->actions);

    // WooCommerce 2.3 Compatibility
    if (version_compare(WC_VERSION, '2.3.0', '<')) {
      // hook WC::send_transactional_email to our new order status
      foreach($this->actions as $action) {
        add_action($action, array(WC(), 'send_transactional_email'), 10, 10);
      }
    } else {
      add_filter('woocommerce_email_actions', array($this, 'email_actions'));
    }

    add_action('woocommerce_email', array($this, 'register_hooks'));
    add_filter('woocommerce_email_classes', array($this, 'email_classes'));
  }

  public function email_actions($actions) {
    return array_unique(array_merge($actions, $this->actions));
  }

  /**
  * @brief Hook our custom order status to all relevant existing email classes
  *
  * @since 1.3
  *
  * @return void
  */
  public function register_hooks($wc_emails) {
    $class_actions = array(
      'WC_Email_New_Order' => array(
        array(
          'from' => array('pending', 'failed', 'draft'),
          'to' => array('partially-paid')
        ),
      ),
      'WC_Email_Customer_Processing_Order' => array(
        /**
          @since 1.5
            Uncomment the following for old behaviour, this is now the responsibility of WC_Deposits_Email_Customer_Partially_Paid.
                array(
                  'from' => array('pending'),
                  'to' => array('partially-paid')
                ),
        */
        array(
          'from' => array('partially-paid'),
          'to' => array('processing', 'on-hold')
        ),
      ),
    );

    foreach($wc_emails->emails as $class => $instance) {
      if (isset($class_actions[$class])) {
        foreach($class_actions[$class] as $actions) {
          foreach($actions['from'] as $from) {
            foreach($actions['to'] as $to) {
              add_action('woocommerce_order_status_' . $from . '_to_' . $to . '_notification', array($instance, 'trigger'));
            }
          }
        }
      }
    }
  }

  public function email_classes($emails) {
    $emails['WC_Deposits_Email_Parital_Payment'] = include('emails/class-wc-deposits-email-partial-payment.php');
    $emails['WC_Deposits_Email_Full_Payment'] = include('emails/class-wc-deposits-email-full-payment.php');
    $emails['WC_Deposits_Email_Customer_Partially_Paid'] = include('emails/class-wc-deposits-email-customer-partially-paid.php');
    return $emails;
  }
}
