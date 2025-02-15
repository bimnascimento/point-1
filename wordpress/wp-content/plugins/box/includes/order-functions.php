<?php
/**
 * Dokan get seller amount from order total
 *
 * @param int $order_id
 *
 * @return float
 */
function dokan_get_seller_amount_from_order( $order_id ) {

    $order          = new WC_Order( $order_id );
    $seller_id      = dokan_get_seller_id_by_order( $order_id );
    $percentage     = dokan_get_seller_percentage( $seller_id );

    $order_total    = $order->get_total();
    $order_shipping = $order->get_total_shipping();
    $order_tax      = $order->get_total_tax();
    $extra_cost     = $order_shipping + $order_tax;
    $order_cost     = $order_total - $extra_cost;
    $order_status   = $order->post_status;

    $net_amount     = ( ( $order_cost * $percentage ) / 100 ) + $extra_cost;

    return $net_amount;
}
/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 *
 * @return array
 */
function dokan_get_seller_orders( $seller_id, $status = 'all', $order_date = NULL, $limit = 10, $offset = 0 ) {
    global $wpdb;

    $cache_key = 'dokan-seller-orders-' . $status . '-' . $seller_id;
    $orders = wp_cache_get( $cache_key, 'dokan' );

    if ( $orders === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );
        $date_query = ( $order_date ) ? $wpdb->prepare( ' AND DATE( p.post_date ) = %s', $order_date ) : '';
        $sql = "SELECT do.order_id, p.post_date
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_status != 'trash'
                    $date_query
                    $status_where
                GROUP BY do.order_id
                ORDER BY p.post_date DESC
                LIMIT $offset, $limit";

        $orders = $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );
        wp_cache_set( $cache_key, $orders, 'dokan' );
    }

    return $orders;
}

/**
 * Get all the orders from a specific date range
 *
 * @global object $wpdb
 * @param int $seller_id
 *
 * @return array
 */
function dokan_get_seller_orders_by_date( $start_date, $end_date, $seller_id = false, $status = 'all' ) {
    global $wpdb;

    $seller_id = ! $seller_id ? get_current_user_id() : intval( $seller_id );
    $start_date = date( 'Y-m-d', strtotime( $start_date ) );
    $end_date = date( 'Y-m-d', strtotime( $end_date ) );

    $cache_key = md5( 'dokan-seller-orders-' . $end_date . '-' . $end_date. '-' . $seller_id );
    $orders = wp_cache_get( $cache_key, 'dokan' );
    if ( $orders === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );
        $date_query = $wpdb->prepare( ' AND DATE( p.post_date ) >= %s AND DATE( p.post_date ) <= %s', $start_date, $end_date );
        $sql = "SELECT do.*, p.post_date
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_status != 'trash'
                    $date_query
                    $status_where
                GROUP BY do.order_id
                ORDER BY p.post_date ASC";
        $orders = $wpdb->get_results( $wpdb->prepare( $sql, $seller_id ) );

        wp_cache_set( $cache_key, $orders, 'dokan' );
    }

    return $orders;
}

/**
 * Get seller refund by date range
 *
 * @param  string  $start_date
 * @param  string  $end_date
 * @param  int $seller_id
 *
 * @return object
 */
function dokan_get_seller_refund_by_date( $start_date, $end_date, $seller_id = false ) {
    global $wpdb;

    $seller_id           = ! $seller_id ? get_current_user_id() : intval( $seller_id );
    $refund_status_where = $wpdb->prepare( ' AND status = %d', 1 );
    $refund_date_query   = $wpdb->prepare( ' AND DATE( date ) >= %s AND DATE( date ) <= %s', $start_date, $end_date );

    $refund_sql = "SELECT *
            FROM {$wpdb->prefix}dokan_refund
            WHERE
                seller_id = %d
                $refund_date_query
                $refund_status_where
            ORDER BY date ASC";

    return $wpdb->get_results( $wpdb->prepare( $refund_sql, $seller_id ) );
}

/**
 * Get seller withdraw by date range
 *
 * @param  string  $start_date
 * @param  string  $end_date
 * @param  int $seller_id
 *
 * @return object
 */
function dokan_get_seller_withdraw_by_date( $start_date, $end_date, $seller_id = false ) {
    global $wpdb;

    $seller_id             = ! $seller_id ? get_current_user_id() : intval( $seller_id );
    $withdraw_status_where = $wpdb->prepare( ' AND status = %d', 1 );
    $withdraw_date_query   = $wpdb->prepare( ' AND DATE( date ) >= %s AND DATE( date ) <= %s', $start_date, $end_date );

    $withdraw_sql = "SELECT *
            FROM {$wpdb->prefix}dokan_withdraw
            WHERE
                user_id = %d
                $withdraw_date_query
                $withdraw_status_where
            ORDER BY date ASC";

    return $wpdb->get_results( $wpdb->prepare( $withdraw_sql, $seller_id ) );
}

/**
 * Get the orders total from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_get_seller_orders_number( $seller_id, $status = 'all' ) {
    global $wpdb;

    $cache_key = 'dokan-seller-orders-count-' . $status . '-' . $seller_id;
    $count = wp_cache_get( $cache_key, 'dokan' );

    if ( $count === false ) {
        $status_where = ( $status == 'all' ) ? '' : $wpdb->prepare( ' AND order_status = %s', $status );

        $sql = "SELECT COUNT(do.order_id) as count
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_status != 'trash'
                    $status_where";

        $result = $wpdb->get_row( $wpdb->prepare( $sql, $seller_id ) );
        $count  = $result->count;

        wp_cache_set( $cache_key, $count, 'dokan' );
    }

    return $count;
}

/**
 * Get all the orders from a specific seller
 *
 * @global object $wpdb
 * @param int $seller_id
 * @return array
 */
function dokan_is_seller_has_order( $seller_id, $order_id ) {
    global $wpdb;

    $sql = "SELECT do.order_id, p.post_date
            FROM {$wpdb->prefix}dokan_orders AS do
            LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
            WHERE
                do.seller_id = %d AND
                p.post_status != 'trash' AND
                do.order_id = %d
            GROUP BY do.order_id";

    return $wpdb->get_row( $wpdb->prepare( $sql, $seller_id, $order_id ) );
}

/**
 * Count orders for a seller
 *
 * @global WPDB $wpdb
 * @param int $user_id
 * @return array
 */
function dokan_count_orders( $user_id ) {
    global $wpdb;

    $cache_key = 'dokan-count-orders-' . $user_id;
    $counts = wp_cache_get( $cache_key, 'dokan' );

    if ( $counts === false ) {
        $counts = array(
            'wc-pending' => 0,
            'wc-completed' => 0,
            'wc-on-hold' => 0,
            'wc-processing' => 0,
            'wc-refunded' => 0,
            'wc-cancelled' => 0,
            'total' => 0,
            'wc-failed' => 0,
            'wc-pagamento-ok' => 0,
            'wc-saiu-para-entrega' => 0,
            'wc-saiu-para-coleta' => 0,
            'wc-lavando-pecas' => 0,
          );

        $sql = "SELECT do.order_status
                FROM {$wpdb->prefix}dokan_orders AS do
                LEFT JOIN $wpdb->posts p ON do.order_id = p.ID
                WHERE
                    do.seller_id = %d AND
                    p.post_type = 'shop_order' AND
                    p.post_status != 'trash'";

        $results = $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );

        if ($results) {
            $total = 0;

            foreach ($results as $order) {
                if ( isset( $counts[$order->order_status] ) ) {
                    $counts[$order->order_status] += 1;
                    $counts['total'] += 1;
                }
            }
        }

        $counts = (object) $counts;
        wp_cache_set( $cache_key, $counts, 'dokan' );
    }

    return $counts;
}

/**
 * Update the child order status when a parent order status is changed
 *
 * @global object $wpdb
 * @param int $order_id
 * @param string $old_status
 * @param string $new_status
 */
function dokan_on_order_status_change( $order_id, $old_status, $new_status ) {
    global $wpdb;

    // make sure order status contains "wc-" prefix
    if ( stripos( $new_status, 'wc-' ) === false ) {
        $new_status = 'wc-' . $new_status;
    }

    // insert on dokan sync table
    $wpdb->update( $wpdb->prefix . 'dokan_orders',
        array( 'order_status' => $new_status ),
        array( 'order_id' => $order_id ),
        array( '%s' ),
        array( '%d' )
    );

    // if any child orders found, change the orders as well
    $sub_orders = get_children( array( 'post_parent' => $order_id, 'post_type' => 'shop_order' ) );
    if ( $sub_orders ) {
        foreach ($sub_orders as $order_post) {
            $order = new WC_Order( $order_post->ID );
            $order->update_status( $new_status );
        }
    }
}

add_action( 'woocommerce_order_status_changed', 'dokan_on_order_status_change', 10, 3 );


/**
 * Mark the parent order as complete when all the child order are completed
 *
 * @param int $order_id
 * @param string $old_status
 * @param string $new_status
 * @return void
 */
function dokan_on_child_order_status_change( $order_id, $old_status, $new_status ) {
    $order_post = get_post( $order_id );

    // we are monitoring only child orders
    if ( $order_post->post_parent === 0 ) {
        return;
    }

    // get all the child orders and monitor the status
    $parent_order_id = $order_post->post_parent;
    $sub_orders      = get_children( array( 'post_parent' => $parent_order_id, 'post_type' => 'shop_order' ) );


    // return if any child order is not completed
    $all_complete = true;

    if ( $sub_orders ) {
        foreach ($sub_orders as $sub) {
            $order = new WC_Order( $sub->ID );

            if ( $order->post_status != 'wc-completed' ) {
                $all_complete = false;
            }
        }
    }

    // seems like all the child orders are completed
    // mark the parent order as complete
    if ( $all_complete ) {
        $parent_order = new WC_Order( $parent_order_id );
        $parent_order->update_status( 'wc-completed', __( 'Mark parent order completed when all child orders are completed.', 'dokan' ) );
    }
}

add_action( 'woocommerce_order_status_changed', 'dokan_on_child_order_status_change', 99, 3 );


/**
 * Delete a order row from sync table when a order is deleted from WooCommerce
 *
 * @global object $wpdb
 * @param type $order_id
 */
function dokan_delete_sync_order( $order_id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'dokan_orders', array( 'order_id' => $order_id ) );
}

/**
 * Delete a order row from sync table to not insert duplicate
 *
 * @global object $wpdb
 * @param type $order_id, $seller_id
 *
 * @since  2.4.11
 */
function dokan_delete_sync_duplicate_order( $order_id, $seller_id ) {
    global $wpdb;

    $wpdb->delete( $wpdb->prefix . 'dokan_orders', array( 'order_id' => $order_id, 'seller_id' => $seller_id ) );
}

/**
 * Insert a order in sync table once a order is created
 *
 * @global object $wpdb
 * @param int $order_id
 */
function dokan_sync_insert_order( $order_id ) {
    global $wpdb;

    if ( get_post_meta( $order_id, 'has_sub_order', true ) == true ) {
        return;
    }

    $order              = new WC_Order( $order_id );
    $seller_id          = dokan_get_seller_id_by_order( $order_id );
    $order_total        = $order->get_total();
    $order_status       = $order->post_status;
    $admin_commission   = dokan_get_admin_commission_by( $order, $seller_id );
    $net_amount         = $order_total - $admin_commission;
    $net_amount         = apply_filters( 'dokan_order_net_amount', $net_amount, $order );

    dokan_delete_sync_duplicate_order( $order_id, $seller_id );

    $wpdb->insert( $wpdb->prefix . 'dokan_orders',
        array(
            'order_id'     => $order_id,
            'seller_id'    => $seller_id,
            'order_total'  => $order_total,
            'net_amount'   => $net_amount,
            'order_status' => $order_status,
        ),
        array(
            '%d',
            '%d',
            '%f',
            '%f',
            '%s',
        )
    );
}

add_action( 'woocommerce_checkout_update_order_meta', 'dokan_sync_insert_order' );
add_action( 'dokan_checkout_update_order_meta', 'dokan_sync_insert_order' );


/**
 * Get a seller ID based on WooCommerce order.
 *
 * If multiple post author is found, then this order contains products
 * from multiple sellers. In that case, the seller ID becomes `0`.
 *
 * @global object $wpdb
 * @param int $order_id
 * @return int
 */
function dokan_get_seller_id_by_order( $order_id ) {
    global $wpdb;

    $sql = "SELECT p.post_author AS seller_id
            FROM {$wpdb->prefix}woocommerce_order_items oi
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oim.order_item_id = oi.order_item_id
            LEFT JOIN $wpdb->posts p ON oim.meta_value = p.ID
            WHERE oim.meta_key = '_product_id' AND oi.order_id = %d GROUP BY p.post_author";

    $sellers = $wpdb->get_results( $wpdb->prepare( $sql, $order_id ) );

    if ( count( $sellers ) > 1 ) {
        foreach ( $sellers as $seller ) {
            $seller_id[] = (int) $seller->seller_id;
        }

        return $seller_id;

    } else if ( count( $sellers ) == 1 ) {

        $seller_id = reset( $sellers )->seller_id;

        return $seller_id;
    }

    return 0;
}


/**
 * Get bootstrap label class based on order status
 *
 * @param string $status
 * @return string
 */
function dokan_get_order_status_class( $status ) {
    //dump($status);
    switch ($status) {
        case 'completed':
        case 'wc-completed':
            return 'success';
            break;

        case 'pending':
        case 'wc-pending':
            return 'danger';
            break;

        case 'on-hold':
        case 'wc-on-hold':
            return 'warning';
            break;

        case 'processing':
        case 'wc-processing':
            return 'info';
            break;

        case 'refunded':
        case 'wc-refunded':
            return 'default';
            break;

        case 'cancelled':
        case 'wc-cancelled':
            return 'default';
            break;

        case 'failed':
        case 'wc-failed':
            return 'danger';
            break;


        case 'pagamento-ok':
        case 'wc-pagamento-ok':
            return 'success';
            break;

        case 'saiu-para-entrega':
        case 'wc-saiu-para-entrega':
            return 'warning';
            break;

        case 'saiu-para-coleta':
        case 'wc-saiu-para-coleta':
            return 'warning';
            break;

        case 'lavando-pecas':
        case 'wc-lavando-pecas':
            return 'danger';
            break;
    }
}


/**
 * Get translated string of order status
 *
 * @param string $status
 * @return string
 */
function dokan_get_order_status_translated( $status ) {
    switch ($status) {
        case 'completed':
        case 'wc-completed':
            return __( 'Completed', 'dokan' );
            break;

        case 'pending':
        case 'wc-pending':
            return __( 'Pending Payment', 'dokan' );
            break;

        case 'on-hold':
        case 'wc-on-hold':
            return __( 'On-hold', 'dokan' );
            break;

        case 'processing':
        case 'wc-processing':
            return __( 'Processing', 'dokan' );
            break;

        case 'refunded':
        case 'wc-refunded':
            return __( 'Refunded', 'dokan' );
            break;

        case 'cancelled':
        case 'wc-cancelled':
            return __( 'Cancelled', 'dokan' );
            break;

        case 'pagamento-ok':
        case 'wc-pagamento-ok':
            return __( 'Pgto Confirmado', 'dokan' );
            break;

        case 'saiu-para-entrega':
        case 'wc-saiu-para-entrega':
            return __( 'Saiu Entrega', 'dokan' );
            break;

        case 'saiu-para-coleta':
        case 'wc-saiu-para-coleta':
            return __( 'Saiu Coleta', 'dokan' );
            break;

        case 'lavando-pecas':
        case 'wc-lavando-pecas':
            return __( 'Lavando Peças', 'dokan' );
            break;

        case 'failed':
        case 'wc-failed':
            return __( 'Falhado', 'dokan' );
            break;
    }
}


/**
 * Get product items list from order
 *
 * @since 1.4
 *
 * @param  object $order
 * @param  string $glue
 *
 * @return string list of products
 */
function dokan_get_product_list_by_order( $order, $glue = ',' ) {

    $product_list = '';
    $order_item   = $order->get_items();

    foreach( $order_item as $product ) {
        $prodct_name[] = $product['name'];
    }

    $product_list = implode( $glue, $prodct_name );

    return $product_list;
}

/**
 * Insert a order in sync table once a order is created
 *
 * @global object $wpdb
 * @param int $order_id
 * @since 2.4
 */
function dokan_sync_order_table( $order_id ) {
    global $wpdb;

    $order          = new WC_Order( $order_id );
    $seller_id      = dokan_get_seller_id_by_order( $order_id );
    $order_total    = $order->get_total();

    if ( $order->get_total_refunded() ) {
        $order_total = $order_total - $order->get_total_refunded();
    }

    $order_status   = $order->post_status;
    $admin_commission   = dokan_get_admin_commission_by( $order, $seller_id );
    $net_amount         = $order_total - $admin_commission;
    $net_amount     = apply_filters( 'dokan_sync_order_net_amount', $net_amount, $order );

    $wpdb->insert( $wpdb->prefix . 'dokan_orders',
        array(
            'order_id'     => $order_id,
            'seller_id'    => $seller_id,
            'order_total'  => $order_total,
            'net_amount'   => $net_amount,
            'order_status' => $order_status,
        ),
        array(
            '%d',
            '%d',
            '%f',
            '%f',
            '%s',
        )
    );
}

/**
 * Insert a order in sync table once a refund is created
 *
 * @global object $wpdb
 * @param int $order_id
 * @since 2.4.11
 */
function dokan_sync_refund_order( $order_id, $refund_id ) {
    global $wpdb;

    $order          = new WC_Order( $order_id );
    $seller_id      = dokan_get_seller_id_by_order( $order_id );
    $order_total    = $order->get_total();

    if ( $order->get_total_refunded() ) {
        $order_total = $order_total - $order->get_total_refunded();
    }

    $order_status   = $order->post_status;
    $admin_commission   = dokan_get_admin_commission_by( $order, $seller_id );
    $net_amount         = $order_total - $admin_commission;
    $net_amount     = apply_filters( 'dokan_order_refunded_net_amount', $net_amount, $order );

    $wpdb->update( $wpdb->prefix . 'dokan_orders',
        array(
            'order_total'  => $order_total,
            'net_amount'   => $net_amount,
            'order_status' => $order_status,
        ),
        array(
            'order_id'     => $order_id
        ),
        array(
            '%f',
            '%f',
            '%s',
        )
    );
    update_post_meta( $order_id, 'dokan_refund_processing_id', $refund_id );
}
add_action( 'woocommerce_order_refunded', 'dokan_sync_refund_order', 10, 2 );


/**
 * Insert a order in sync table once a refund is deleted
 *
 * @global object $wpdb
 * @param int $order_id
 * @since 2.4.11
 */
function dokan_delete_refund_order( $refund_id, $order_id ) {
    dokan_sync_refund_order( $order_id, $refund_id );
    delete_post_meta( $order_id, 'dokan_refund_processing_id' );
}
add_action( 'woocommerce_refund_deleted', 'dokan_delete_refund_order', 10, 2 );


/**
 * Get if an order is a sub order or not
 *
 * @since 2.4.11
 *
 * @param int $order_id
 *
 * @return boolean
 */
function dokan_is_sub_order( $order_id ) {
    $parent_order_id =  wp_get_post_parent_id( $order_id );
    if ( 0 != $parent_order_id ) {
        return true;
    } else {
        return false;
    }

}


/**
 * Get toal number of orders in Dokan order table
 *
 * @since 2.4.3
 *
 * @return  int Order_count
 */
function dokan_total_orders() {
    global $wpdb;

    $order_count = $wpdb->get_var( "SELECT COUNT(id) FROM " . $wpdb->prefix . "dokan_orders " );

    return (int) $order_count;
}

/**
 * Return array of sellers with items
 *
 * @since 2.4.4
 *
 * @param type $id
 *
 * @return array $sellers_with_items
 */
function dokan_get_sellers_by( $order_id ) {

    $order       = new WC_Order( $order_id );
    $order_items = $order->get_items();

    $sellers = array();
    foreach ( $order_items as $item ) {
        $seller_id             = get_post_field( 'post_author', $item['product_id'] );
        $sellers[$seller_id][] = $item;
    }

    return $sellers;
}

/**
 * Return unique array of seller_ids from an order
 *
 * @since 2.4.9
 *
 * @param type $order_id
 *
 * @return array $seller_ids
 */
function dokan_get_seller_ids_by( $order_id ) {

    $sellers = dokan_get_sellers_by( $order_id );

    return array_unique( array_keys( $sellers ) );

}

/**
 *
 * @global object $wpdb
 * @param type $parent_order_id
 * @return type
 */
function dokan_get_suborder_ids_by ($parent_order_id){

    global $wpdb;

     $sql = "SELECT ID FROM " . $wpdb->prefix . "posts
             WHERE post_type = 'shop_order'
             AND post_parent = " . $parent_order_id;

     $sub_orders = $wpdb->get_results($sql);

    if ( ! $sub_orders ) {
        return null;
    }

    return $sub_orders;

}

/**
 * Return admin commisson from an order
 *
 * @since 2.4.12
 *
 * @param object $order
 *
 * @return float $commission
 */
function dokan_get_admin_commission_by( $order, $seller_id ) {

    if ( get_posts( array( 'post_parent' => $order->id, 'post_type' => 'shop_order' ) ) ) {
        return;
    }

    $admin_commission = 0;
    $refund_t = 0;
    $commissions = array();
    $i = 0;
    $total_line = 0;
    $commission_recipient = dokan_get_option( 'extra_fee_recipient', 'seller' );

    foreach ( $order->get_items() as $item_id => $item ) {

        $refund_t += $order->get_total_refunded_for_item( $item_id );
        $commissions[$i]['total_line'] = $item['line_total'] - $order->get_total_refunded_for_item( $item_id );
        $commissions[$i]['admin_percentage'] = 100 - dokan_get_seller_percentage( $seller_id, $item['product_id'] );
        $total_line += $commissions[$i]['total_line'];

        $i++;

    }

    $refund_t += $order->get_total_tax_refunded() + $order->get_total_shipping_refunded();
    $refund_ut = $order->get_total_refunded() - $refund_t;

    foreach ( $commissions as $commission ) {

        $commission['ut_amount'] = $refund_ut * ( $commission['total_line'] / $total_line );
        $admin_commission += ( $commission['total_line'] + $commission['ut_amount'] ) * $commission['admin_percentage'] /100;
    }

    if ( 'admin' == $commission_recipient ) {
        $total_extra = $order->get_total_tax() + $order->get_total_shipping();
        $net_extra   = $total_extra - ( $order->get_total_tax_refunded() + $order->get_total_shipping_refunded() );
        $admin_commission += $net_extra;
    }

    return apply_filters( 'dokan_order_admin_commission', $admin_commission, $order );
}
