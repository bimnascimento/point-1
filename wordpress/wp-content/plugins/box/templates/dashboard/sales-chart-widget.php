<?php

/**
 *  Dokan Dahsboard Template
 *
 *  Dokan Dashboard Sales chart report widget
 *
 *  @since 2.4
 *
 *  @package dokan
 */
?>

<div class="dashboard-widget sells-graph">
    <div class="widget-title"><i class="fa fa-credit-card"></i> <?php _e( 'Últimos 10 dias', 'dokan' ); ?> <a class="click-loading" href="<?php echo home_url('/lavanderias/area-administrativa/reports/'); ?>">ver mais</a></div>

    <?php
	    require_once DOKAN_INC_DIR . '/reports.php';
	    dokan_dashboard_sales_overview();
    ?>
</div> <!-- .sells-graph -->
