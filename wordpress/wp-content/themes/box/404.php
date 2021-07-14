<?php get_header(); ?>
<?php
global $porto_settings;
?>
<div id="content" class="no-content">
    <div class="container">
        <section class="page-not-found">
            <div class="row">
                    <div class="col-md-12">
                        <div class="page-not-found-main">
                            <h2 class="entry-title"><?php //_e('404', 'porto') ?> <i class="fa fa-battery-1 faa-slow text-danger faa-flash animated"></i></h2>
                            <p><?php _e("We're sorry, but the page you were looking for doesn't exist.", 'porto') ?></p>
                        </div>
                    </div>
                <?php if ($porto_settings['error-block']) : ?>
                    <div class="col-md-12">
                        <?php echo do_shortcode('[porto_block name="' . $porto_settings['error-block'] . '"]') ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
<style>
#main {
    margin-top: 80px;
}
.footer-main {
    display:none !important;
}
</style>
<?php get_footer() ?>
