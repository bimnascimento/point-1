<?php
/**
 *  Dokan Dahsbaord Template
 *
 *  Dokan Dahsboard Review Widget Template
 *
 *  @since 2.4
 *
 *  @package dokan
 */

/*
?>

<div class="dashboard-widget reviews">
    <div class="widget-title"><i class="fa fa-comments"></i> <?php _e( 'Reviews', 'dokan' ); ?></div>

    <ul class="list-unstyled list-count">
        <li>
            <a href="<?php echo $reviews_url; ?>">
                <span class="title"><?php _e( 'All', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->total; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'hold' ), $reviews_url ); ?>">
                <span class="title"><?php _e( 'Pending', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->moderated; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'spam' ), $reviews_url ); ?>">
                <span class="title"><?php _e( 'Spam', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->spam; ?></span>
            </a>
        </li>
        <li>
            <a href="<?php echo add_query_arg( array( 'comment_status' => 'trash' ), $reviews_url ); ?>">
                <span class="title"><?php _e( 'Trash', 'dokan' ); ?></span> <span class="count"><?php echo $comment_counts->trash; ?></span>
            </a>
        </li>
    </ul>
</div> <!-- .reviews -->
<?php */

$suporte_url = home_url('/lavanderias/area-administrativa/support/');

function topic_count( $store_id ){

    global $wpdb;

    $sql = "SELECT `post_status`, count(`ID`) as count FROM {$wpdb->posts} as tp LEFT JOIN {$wpdb->postmeta} as tpm ON tp.ID = tpm.post_id WHERE tpm.meta_key ='store_id' AND tpm.meta_value = $store_id GROUP BY tp.post_status";
    $results = $wpdb->get_results( $sql );

    if ( $results ) {
        return $results;
    }

    return false;
}

$counts = topic_count( get_current_user_id() );
$redir_url = dokan_get_navigation_url( 'support' );

$count = 0;
if( $counts ){
    $count = wp_list_pluck( $counts, 'count', 'post_status' );
}

$defaults = array(
    'open' => 0,
    'closed' => 0,
);

$count  = wp_parse_args( $count, $defaults );

$open   = $count['open'];
$closed = $count['closed'];
$all    = $open + $closed;

$current_status = isset($_GET['ticket_status']) ? $_GET['ticket_status'] : 'open';


?>

<div class="dashboard-widget reviews">
    <div class="widget-title"><i class="fa fa-comments"></i> <?php _e( 'Suporte ao Cliente', 'dokan' ); ?></div>

    <ul class="list-unstyled list-count">
        <li >
            <a class="click-loading" href="<?php echo $redir_url.'?ticket_status=all' ?>"><?php echo __( 'Todos os chamados', 'dokan').' ('. $all.') ' ?></a>
        </li>
        <li >
            <a class="click-loading" href="<?php echo $redir_url.'?ticket_status=open' ?>"><?php echo __( 'Chamados em aberto', 'dokan').' ('. $open.') ' ?></a>
        </li>
        <li >
            <a class="click-loading" href="<?php echo $redir_url.'?ticket_status=closed' ?>"><?php echo __( 'Chamados fechados', 'dokan').' ('. $closed.')' ?></a>
        </li>
    </ul>

</div> <!-- .reviews -->
