<?php
global $woocommerce, $current_user, $wpdb;

$order_id = isset( $_GET['order_id'] ) ? intval( $_GET['order_id'] ) : 0;

if ( !dokan_is_seller_has_order( $current_user->ID, $order_id ) ) {
    echo '<div class="dokan-alert dokan-alert-danger">' . __( 'This is not yours, I swear!', 'dokan' ) . '</div>';
    return;
}

$statuses = wc_get_order_statuses();
$order    = new WC_Order( $order_id );
?>
<div class="dokan-clearfix">
    <div class="dokan-w8" style="margin-right:3%;">

        <div class="dokan-clearfix">
            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php printf( __( 'Order', 'dokan' ) . '#%d', $order->id ); ?></strong> &rarr; <?php _e( 'Order Items', 'dokan' ); ?></div>
                    <div class="dokan-panel-body" id="woocommerce-order-items">

                        <?php
                        if ( !WeDevs_Dokan::init()->is_pro() ) { ?>
                            <table cellpadding="0" cellspacing="0" class="dokan-table order-items">
                                <thead>
                                    <tr>
                                        <th class="item" colspan="2"><?php _e( 'Item', 'dokan' ); ?></th>

                                        <?php do_action( 'woocommerce_admin_order_item_headers' ); ?>

                                        <th class="quantity"><?php _e( 'Qty', 'dokan' ); ?></th>

                                        <th class="line_cost"><?php _e( 'Totals', 'dokan' ); ?></th>
                                    </tr>
                                </thead>
                                <tbody id="order_items_list">

                                    <?php
                                        // List order items
                                        $order_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', array( 'line_item', 'fee' ) ) );

                                        foreach ( $order_items as $item_id => $item ) {

                                            switch ( $item['type'] ) {
                                                case 'line_item' :
                                                    $_product   = $order->get_product_from_item( $item );
                                                    $item_meta  = $order->get_item_meta( $item_id );

                                                    dokan_get_template_part( 'orders/order-item-html', '', array(
                                                        'order' => $order,
                                                        'item_id' => $item_id,
                                                        '_product' => $_product,
                                                        'item' => $item
                                                    ) );
                                                break;
                                                case 'fee' :
                                                    dokan_get_template_part( 'orders/order-fee-html', '', array(
                                                        'item_id' => $item_id,
                                                        'item_meta' => $item_meta
                                                    ) );

                                                break;
                                            }

                                            do_action( 'woocommerce_order_item_' . $item['type'] . '_html', $item_id, $item );

                                        }
                                    ?>
                                </tbody>

                                <tfoot>
                                    <?php
                                        if ( $totals = $order->get_order_item_totals() ) {
                                            foreach ( $totals as $total ) {
                                                ?>
                                                <tr>
                                                    <th colspan="2"><?php echo $total['label']; ?></th>
                                                    <td colspan="2" class="value"><?php echo $total['value']; ?></td>
                                                </tr>
                                                <?php
                                            }
                                        }
                                    ?>
                                </tfoot>

                            </table>

                            <?php
                            $coupons = $order->get_items( array( 'coupon' ) );

                            if ( $coupons ) {
                                ?>
                                <table class="dokan-table order-items">
                                    <tr>
                                        <th><?php _e( 'Coupons', 'dokan' ); ?></th>
                                        <td>
                                            <ul class="list-inline"><?php
                                                foreach ( $coupons as $item_id => $item ) {

                                                    $post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE post_title = %s AND post_type = 'shop_coupon' AND post_status = 'publish' LIMIT 1;", $item['name'] ) );

                                                    $link = dokan_get_coupon_edit_url( $post_id );

                                                    echo '<li><a data-html="true" class="tips code" title="' . esc_attr( wc_price( $item['discount_amount'] ) ) . '" href="' . esc_url( $link ) . '"><span>' . esc_html( $item['name'] ). '</span></a></li>';
                                                }
                                            ?></ul>
                                        </td>
                                    </tr>
                                </table>
                                <?php
                            }
                        } else {
                            $data  = get_post_meta( $order_id );
                            include( DOKAN_INC_DIR . '/pro/templates/orders/views/html-order-items.php' );
                        } ?>
                    </div>
                </div>
            </div>

            <div class="clear"></div>
            <div class="confirma-acao">
                Liberar Entrega <b>(Pedido Concluído)</b> - MotoBoy ?
                <label class="ios7-switch switch-page switch-motoboy">
                    <input type="checkbox" />
                    <span></span>
                </label>
            </div>

            <div class="clear"></div>

            <?php do_action( 'dokan_order_detail_after_order_items', $order ); ?>

            <div class="dokan-left" style="min-width: 49%; margin-right:2%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php _e( 'Billing Address', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php echo $order->get_formatted_billing_address(); ?>
                    </div>
                </div>
            </div>

            <div class="dokan-left" style="min-width: 49%;">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php _e( 'Shipping Address', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php echo $order->get_formatted_shipping_address(); ?>
                    </div>
                </div>
            </div>

            <div class="clear"></div>





            <?php /* ?>
            <div class="" style="100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php _e( 'Downloadable Product Permission', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body">
                        <?php
                            dokan_get_template_part( 'orders/downloadable', '', array( 'order'=> $order ) );
                        ?>
                    </div>
                </div>
            </div>
            <?php */ ?>

        </div>
    </div>

    <div class="dokan-w4">
        <div class="row dokan-clearfix">
            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php _e( 'General Details', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body general-details">
                        <ul class="list-unstyled order-status">
                            <li>
                                <span><?php _e( 'Order Status:', 'dokan' ); ?></span>
                                <label class="dokan-label dokan-label-<?php echo dokan_get_order_status_class( $order->post_status ); ?>"><?php echo isset( $statuses[$order->post_status] ) ? $statuses[$order->post_status] : $order->post_status; ?></label>

                                <?php
                                $status = $order->post_status;
                                if ( dokan_get_option( 'order_status_change', 'dokan_selling', 'on' ) == 'on' && !in_array( $status, array('wc-completed','wc-cancelled','wc-refunded') )   ) {?>
                                    <a href="#" class="dokan-edit-status"><small><?php _e( '&nbsp; Edit', 'dokan' ); ?></small></a>
                                <?php } ?>
                            </li>
                            <li class="dokan-hide">
                                <form id="dokan-order-status-form" action="" method="post">

                                  <?php

                                  echo 'Para <b>cancelamento</b>, entre em contato com nossa administração no menu ajuda.'

                                  ?>

                                    <select id="order_status" name="order_status" class="form-control">
                                        <?php

                                        foreach ( $statuses as $status => $label ) {

                                            //dump($status);

                                             if( $status == 'wc-refunded' || $status == 'wc-failed' || $status == 'wc-cancelled' ) {
                                                 continue;
                                             }
                                            echo '<option value="' . esc_attr( $status ) . '" ' . selected( $status, $order->post_status, false ) . '>' . esc_html__( $label, 'dokan' ) . '</option>';
                                        }
                                        ?>
                                    </select>


                                    <input type="hidden" name="order_id" value="<?php echo $order->id; ?>">
                                    <input type="hidden" name="action" value="dokan_change_status">
                                    <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'dokan_change_status' ); ?>">
                                    <input type="submit" class="dokan-btn dokan-btn-success dokan-btn-sm" name="dokan_change_status" value="<?php _e( 'Update', 'dokan' ); ?>">

                                    <a href="#" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-cancel-status"><?php _e( 'Cancel', 'dokan' ) ?></a>
                                </form>
                            </li>
                            <li>
                                <span><?php _e( 'Order Date:', 'dokan' ); ?></span>
                                <?php echo $order->order_date; ?>
                            </li>
                        </ul>

                        <ul class="list-unstyled customer-details">
                            <li>
                                <span><?php _e( 'Customer:', 'dokan' ); ?></span>
                                <?php
                                $customer_user = absint( get_post_meta( $order->id, '_customer_user', true ) );
                                if ( $customer_user && $customer_user != 0 ) {
                                    $customer_userdata = get_userdata( $customer_user );
                                    $display_name =  $customer_userdata->display_name;
                                } else {
                                    $display_name = get_post_meta( $order->id, '_billing_first_name', true ). ' '. get_post_meta( $order->id, '_billing_last_name', true );
                                }
                                ?>
                                <a href="#"><?php echo $display_name; ?></a><br>
                            </li>
                            <li>
                                <span><?php _e( 'Email:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $order->id, '_billing_email', true ) ); ?>
                            </li>
                            <li>
                                <span><?php _e( 'Phone:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $order->id, '_billing_phone', true ) ); ?>
                            </li>
                            <li>
                                <span><?php _e( 'Customer IP:', 'dokan' ); ?></span>
                                <?php echo esc_html( get_post_meta( $order->id, '_customer_ip_address', true ) ); ?>
                            </li>
                        </ul>

                        <?php
                        if ( get_option( 'woocommerce_enable_order_comments' ) != 'no' ) {
                            $customer_note = get_post_field( 'post_excerpt', $order->id );

                            if ( !empty( $customer_note ) ) {
                                ?>
                                <div class="alert alert-success customer-note">
                                    <strong><?php _e( 'Customer Note:', 'dokan' ) ?></strong><br>
                                    <?php echo wp_kses_post( $customer_note ); ?>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="" style="width:100%">
                <div class="dokan-panel dokan-panel-default">
                    <div class="dokan-panel-heading"><strong><?php _e( 'Order Notes', 'dokan' ); ?></strong></div>
                    <div class="dokan-panel-body" id="dokan-order-notes">
                        <?php
                        $args = array(
                            'post_id' => $order_id,
                            'approve' => 'approve',
                            'type' => 'order_note'
                        );

                        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        $notes = get_comments( $args );

                        echo '<ul class="order_notes list-unstyled">';

                        if ( $notes ) {
                            foreach( $notes as $note ) {
                                $note_classes = get_comment_meta( $note->comment_ID, 'is_customer_note', true ) ? array( 'customer-note', 'note' ) : array( 'note' );

                                ?>
                                <li rel="<?php echo absint( $note->comment_ID ) ; ?>" class="<?php echo implode( ' ', $note_classes ); ?>">
                                    <div class="note_content">
                                        <?php echo wpautop( wptexturize( wp_kses_post( $note->comment_content ) ) ); ?>
                                    </div>
                                    <p class="meta">
                                        <?php printf( __( 'added %s ago', 'dokan' ), human_time_diff( strtotime( $note->comment_date_gmt ), current_time( 'timestamp', 1 ) ) ); ?> <a href="#" class="delete_note"><?php _e( 'Delete note', 'woocommerce' ); ?></a>
                                    </p>
                                </li>
                                <?php
                            }
                        } else {
                            echo '<li>' . __( 'There are no notes for this order yet.', 'dokan' ) . '</li>';
                        }

                        echo '</ul>';

                        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
                        ?>
                        <div class="add_note">
                            <h4><?php _e( 'Add note', 'dokan' ); ?></h4>
                            <form class="dokan-form-inline" id="add-order-note" role="form" method="post">
                                <p>
                                    <textarea type="text" id="add-note-content" name="note" class="form-control" cols="19" rows="3"></textarea>
                                </p>
                                <div class="clearfix">
                                    <div class="order_note_type dokan-form-group">
                                        <select name="note_type" id="order_note_type" class="dokan-form-control">
                                            <option value="customer"><?php _e( 'Customer note', 'dokan' ); ?></option>
                                            <option value=""><?php _e( 'Private note', 'dokan' ); ?></option>
                                        </select>
                                    </div>

                                    <input type="hidden" name="security" value="<?php echo wp_create_nonce('add-order-note'); ?>">
                                    <input type="hidden" name="delete-note-security" id="delete-note-security" value="<?php echo wp_create_nonce('delete-order-note'); ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $order->id; ?>">
                                    <input type="hidden" name="action" value="dokan_add_order_note">
                                    <input type="submit" name="add_order_note" class="add_note btn btn-sm btn-theme" value="<?php esc_attr_e( 'Add Note', 'dokan' ); ?>">
                                </div>
                            </form>
                            <?php /* ?>
                            <div class="clearfix dokan-form-group" style="margin-top: 10px;">
                                <!-- Trigger the modal with a button -->
                                <input type="button" data-toggle="modal" data-target="#tracking-modal" id="add-tracking-number" name="add_tracking_number" class="dokan-btn dokan-btn-success grant_access" value="<?php esc_attr_e( 'Tracking Number', 'dokan' ); ?>">

                            </div>


                            <!-- Modal -->
                            <div class="modal fade" id="tracking-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                            <h2 class="modal-title" id="myModalLabel"><?php _e('Shipment Tracking','dokan');?></h2>
                                        </div>
                                        <form id="add-shipping-tracking-form" method="post">
                                            <div class="modal-body">
                                                <h5><?php _e('Shipping Provider','dokan');?></h5>
                                                <select name="shipping_provider" id="shipping_provider" class="form-control">
                                                    <optgroup label="Australia">
                                                        <option><?php _e('Australia','dokan');?></option>
                                                        <option><?php _e('Fedex','dokan');?></option>
                                                    </optgroup>
                                                    <optgroup label="Canada">
                                                        <option><?php _e('Canada Post','dokan');?></option>
                                                    </optgroup>
                                                </select>
                                                <h5><?php _e('Tracking Number','dokan');?></h5>
                                                <input type="text" name="tracking_number" id="tracking_number" value="">
                                                <h5><?php _e('Date Shipped','dokan');?></h5>
                                                <input type="text" name="shipped_date" id="shipped-date" value="" placeholder="YYYY-MM-DD">
                                                <input type="hidden" name="security" id="security" value="<?php echo wp_create_nonce('add-shipping-tracking-info'); ?>">
                                                <input type="hidden" name="post_id" id="post-id" value="<?php echo $order->id; ?>">
                                                <input type="hidden" name="action" id="action" value="dokan_add_shipping_tracking_info">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-default" data-dismiss="modal"><?php _e('Close','dokan');?></button>
                                                <input id="add-tracking-details" type="button" class="btn btn-primary" value="<?php _e('Add Tracking Details','dokan');?>">
                                            </div>
                                        </form>

                                    </div>
                                </div>
                            </div>
                            <?php */ ?>
                        </div> <!-- .add_note -->

                    </div> <!-- .dokan-panel-body -->
                </div> <!-- .dokan-panel -->
            </div>
        </div> <!-- .row -->
    </div> <!-- .col-md-4 -->
</div>
