<?php
/**
 *  Dashboard Widget Template
 *
 *  Dashboard Big Counter widget template
 *
 *  @since 2.4
 *
 *  @author weDevs <info@wedevs.com>
 *
 *  @package dokan
 */
 //dump($earning);
 //$earning = 542.23;
?>
<div class="dashboard-widget big-counter">
    <ul class="list-inline">
        <li>
            <div class="title"><?php _e( 'Pageview', 'dokan' ); ?></div>
            <div class="count"><?php echo dokan_number_format( $pageviews ); ?></div>
        </li>
        <li>
            <div class="title"><?php _e( 'Pedidos', 'dokan' ); ?></div>
            <div class="count">
                <?php
                $total =
                    $orders_count->{'wc-completed'} +
                    $orders_count->{'wc-processing'} +
                    $orders_count->{'wc-pagamento-ok'} +
                    $orders_count->{'wc-saiu-para-coleta'} +
                    $orders_count->{'wc-saiu-para-entrega'} +
                    $orders_count->{'wc-lavando-pecas'} +
                    $orders_count->{'wc-on-hold'};
                echo number_format_i18n( $total, 0 );
                ?>
            </div>
        </li>
        <li>
            <div class="title"><?php _e( 'Total', 'dokan' ); ?></div>
            <div class="count"><?php echo wc_price( $earning ); ?></div>
        </li>
        <li>
            <div class="title"><?php _e( 'Saldo', 'dokan' ); ?></div>
            <div class="count"><?php echo $seller_balance; ?></div>
        </li>

        <?php do_action( 'dokan_seller_dashboard_widget_counter' ); ?>

    </ul>
</div> <!-- .big-counter -->

<div class="dashboard-widget orders hidden">
    <div class="widget-title"><i class="fa fa-handshake-o"></i> <?php _e( 'Legenda', 'dokan' ); ?></div>
    <div class="info-legenda">
      <b>Saldo:</b>
      <br/>( <a href="<?php echo home_url('/lavanderias/area-administrativa/orders/?order_status=wc-completed');?>" class="click-loading">Finalizados</a> - Comissão ) - <a href="<?php echo home_url('/lavanderias/area-administrativa/withdraw/?type=approved');?>" class="click-loading">Retirada</a>
      <br/><b>Comissão:</b>
      <br/>( <a href="<?php echo home_url('/lavanderias/area-administrativa/orders/');?>" class="click-loading">Pedido</a> - Frete ) * 20%
      <?php /* ?>
      <br/><b>* Valor mínimo para retirada:</b> R$ 100,00
      <?php */ ?>
    </div>
</div>
