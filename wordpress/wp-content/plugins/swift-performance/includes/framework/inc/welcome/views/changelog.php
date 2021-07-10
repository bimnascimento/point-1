<div class="wrap about-wrap">
    <h1><?php esc_html_e( 'ReduxSA Framework - Changelog', 'reduxsa-framework' ); ?></h1>

    <div class="about-text">
        <?php esc_html_e( 'Our core mantra at ReduxSA is backwards compatibility. With hundreds of thousands of instances worldwide, you can be assured that we will take care of you and your clients.', 'reduxsa-framework' ); ?>
    </div>
    <div class="reduxsa-badge">
        <i class="el el-reduxsa"></i>
        <span>
            <?php printf( __( 'Version %s', 'reduxsa-framework' ), esc_html(ReduxSAFramework::$_version) ); ?>
        </span>
    </div>

    <?php $this->actions(); ?>
    <?php $this->tabs(); ?>

    <div class="changelog">
        <div class="feature-section">
            <?php echo wp_kses_post($this->parse_readme()); ?>
        </div>
    </div>

</div>