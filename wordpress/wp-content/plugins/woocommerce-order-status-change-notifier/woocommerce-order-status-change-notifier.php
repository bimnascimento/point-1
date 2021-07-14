<?php
/**
 * Plugin Name: WooCommerce Order Status Change Notifier
 * Plugin URI: http://woothemes.com/products/woocommerce-order-status-change-notifier-/
 * Description: Supercharge your Order Status changes. Add notes, control outbound email notifications, and store order change comments to orders in a single step. Order Status Comments are saved to the order and included in outbound emails.
 * Text Domain: woocommerce-order-status-change-notifier
 * Author: WooThemes
 * Author URI: http://woothemes.com/
 * Developer: RTD LLC Development
 * Developer URI: mailto:dev@ribbedtee.com
 * Version: 1.1.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 */
woothemes_queue_update( plugin_basename( __FILE__ ), '1b88ea41fc9fb419fbc8195f8ce7f09d', '907362' );

$wc_order_status_comment = new WC_Order_Status_Comment();

class WC_Order_Status_Comment {

	public function __construct() {
		$this->statuses_without_emailers = array('pending', 'on-hold', 'cancelled', 'refunded', 'failed');
		$this->comment_mailed_as_note	 = false;

		add_filter('woocommerce_admin_order_data_after_order_details', array($this, 'add_js_to_order'), 10, 1);

		add_action('admin_enqueue_scripts', array($this, 'scripts'));
		add_action('woocommerce_order_status_changed', array($this, 'process_order_status'), -100, 3);
		add_action('woocommerce_admin_order_actions_end', array($this, 'add_actions'), 110);
		add_action('in_admin_footer', array($this, 'admin_footer'));
		add_action('wp_ajax_wc_osc_update_order_status_comment', array($this, 'ajax_action'));
		add_action('wc_order_status_comment_save', array($this, 'save_comment'), 10, 2);

		//formatted comment for WC emails
		add_action('woocommerce_email_after_order_table', array($this, 'add_order_status_comment_to_wc_emails'), 10, 3);

		// got checkbox
		if (isset($_POST['oscn_status_comment_notify_customer'])) {

			// WC internal
			$ids = array(
				'customer_processing_order',
				'customer_note',
				//'customer_invoice',
				'customer_completed_order'
			);

			//take extra statuses from addons
			$addon_statuses = apply_filters('wc_order_statuses', array());
			foreach ($addon_statuses as $status => $title) {
				$ids[] = str_replace("wc-", "", $status) . "_email";
			}

			// support WooCommerce Order Status Manager
			$this->WOSM_emails = get_posts( array(
				'post_type'        => 'wc_order_email',
				'post_status'      => 'publish',
				'nopaging'         => true,
				'suppress_filters' => 1
			) );
			foreach ($this->WOSM_emails as $email) {
				$ids[] = 'wc_order_status_email_' . esc_attr( $email->ID );
			}

			// on /off emails
			foreach ($ids as $id)
				add_filter("woocommerce_email_enabled_$id", ($_POST['oscn_status_comment_notify_customer'] == 'Yes') ? '__return_true' : '__return_false');
		}
	}

	public function add_js_to_order($order_id) {
		wp_enqueue_script("woocommerce-order-status-change-notifier", plugin_dir_url(__FILE__) . 'js/add-order-status-comment.js');
	}

	public function scripts() {
		if (get_current_screen()->id == 'edit-shop_order') {
//			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_style('thickbox');
			wp_enqueue_script('thickbox');
			wp_enqueue_style("woocommerce-order-status-change-notifier-css", plugin_dir_url(__FILE__) . 'css/main.css');
		}
		if (get_current_screen()->id == 'woocommerce_page_woocommerce_status_and_actions') {
			wp_enqueue_script("woocommerce-order-status-change-notifier-tag", plugin_dir_url(__FILE__) . 'js/show-new-tag.js');
		}
	}

	public function admin_footer() {
		if (get_current_screen()->id == 'edit-shop_order') {
			$this->render('footer', array('ajax' => admin_url('admin-ajax.php')));
		}
	}

	public function ajax_action() {
		if (isset($_POST['method'])) {
			switch ($_POST['method']) {
				case 'change_status':
					if (isset($_POST['order_id'])) {
						$order	 = wc_get_order($_POST['order_id']);
						$status	 = $_POST['status'];
						$order->update_status($status);
					}
					_e('Status changed', 'woocommerce-order-status-change-notifier');
					die();
			}
		}
	}

	private function must_send_note($old_status, $new_status) {
		// support WooCommerce Order Status Manager
		if(!empty($this->WOSM_emails)) {
			// code inherited from WOSM plugin
			$status_changes = array(
				$old_status . '_to_' . $new_status,
				$old_status . '_to_any',
				'any_to_' . $new_status,
			);
			foreach($this->WOSM_emails as $email) {
				$dispatch_conditions = get_post_meta( $email->ID, '_email_dispatch_condition' );
				// Try to find a match between current changes and the dispatch conditions
				foreach ( $dispatch_conditions as $condition ) {
					if ( in_array( $condition, $status_changes ) ) {
						return false; // oops WOSM will do it!
					}
				}
			}
		}

		if (in_array($new_status, $this->statuses_without_emailers))
			return true;

		// there are
		//	'woocommerce_order_status_pending_to_processing',
		//	'woocommerce_order_status_failed_to_processing',
		if ($new_status == 'processing' AND $old_status != 'pending' AND $old_status != 'failed')
			return true;

		return false;
	}

	public function process_order_status($order_id, $old_status, $new_status) {
		$order			 = new WC_Order($order_id);
		$status_comment	 = isset($_POST['oscn_status_comment']) ? stripslashes_deep($_POST['oscn_status_comment']) : '';

		// must fill {order_status_comment} for Custom Actions plugin
		$this->status_comment = $status_comment;
		add_filter('wp_mail', array($this, 'my_wp_mail_filter'));

		if ($status_comment) {
			do_action('wc_order_status_comment_save', $order, $status_comment);
		}

		if ( isset( $_POST['oscn_status_comment_notify_customer'] ) && $_POST['oscn_status_comment_notify_customer'] == 'Yes' && $this->must_send_note($old_status, $new_status)) {
			$this->comment_mailed_as_note	 = true;
			if (!$status_comment)
				$status_comment					 = sprintf(__( 'Order status changed from %s to %s.', 'woocommerce-order-status-change-notifier' ), wc_get_order_status_name($old_status), wc_get_order_status_name($new_status));

			global $woocommerce;
			$woocommerce->mailer(); //init mailer -- required!!
			do_action('woocommerce_new_customer_note_notification', array('order_id' => $order_id, 'customer_note' => $status_comment));
		}
	}

	public function save_comment($order, $status_comment) {
		$order->add_order_note("[[" . wc_get_order_status_name($order->post_status) . "|" . $status_comment . "]]");
	}

	public function my_wp_mail_filter($args) {
		$args['message']	 = str_replace('{order_status_comment}', $this->status_comment, $args['message']);
		if ($this->status_comment)
			$args['message']	 = str_replace('{order_status_comment_formatted}', "<p><strong>Order Status Comment:</strong>&nbsp;" . esc_html( $this->status_comment ) . "</p>\n", $args['message']);
		else
			$args['message']	 = str_replace('{order_status_comment_formatted}', "", $args['message']);
		return $args;
	}

	public function add_order_status_comment_to_wc_emails($order, $sent_to_admin, $plain_text = null) {
		if (!$this->comment_mailed_as_note && isset( $_POST['oscn_status_comment'] ) ) {
			if ($plain_text)
				echo "\n" . __( 'Order Status Comment:', 'woocommerce-order-status-change-notifier' ) . ' ' . esc_html( $_POST['oscn_status_comment'] ) . "}\n\n";
			else
				echo "<br><p><strong>" . __( 'Order Status Comment:', 'woocommerce-order-status-change-notifier' ) . "</strong>&nbsp;" . esc_html( $_POST['oscn_status_comment'] ) . "</p><br>\n";
		}
	}

	public function render($view, $params = array(), $path_views = null) {
		extract($params);
		$view = basename($view);
		include __DIR__ . "/views/$view.php";
	}

	public function add_actions($order) {
//		$status = wc_get_order_status_name($order->post_status);
		?>

		<a class="button tips change-status"  data-current-status="<?php echo $order->post_status ?>" data-order-id="<?php echo $order->id ?>" style="padding: 5px 4px 5px 5px;;" target="_blank" data-tip="<?php _e('Change status', 'woocommerce-order-status-change-notifier') ?>" ><img src="<?php echo plugin_dir_url(__FILE__) . 'img/change_status.ico' ?>" alt="<?php _e('Change status', 'woocommerce-order-status-change-notifier') ?>" width="14"></a>
		<script>
			jQuery( document ).ready( function( $ ) {
				$( 'a.print_list' ).attr( 'title', '<?php _e('Change Order Status', 'woocommerce-order-status-change-notifier') ?>' )
				// Tooltips
				var tiptip_args = {
					'fadeIn': 50,
					'fadeOut': 50,
					'delay': 200
				};
				$( function( ) {
					$( ".print_list" ).tipTip( tiptip_args );
				} );
			} )
		</script>
		<?php
	}

}
