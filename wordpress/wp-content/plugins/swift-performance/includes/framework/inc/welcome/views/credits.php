<div class="wrap about-wrap">
    <h1><?php esc_html_e( 'ReduxSA Framework - A Community Effort', 'reduxsa-framework' ); ?></h1>

    <div class="about-text">
        <?php esc_html_e( 'We recognize we are nothing without our community. We would like to thank all of those who help ReduxSA to be what it is. Thank you for your involvement.', 'reduxsa-framework' ); ?>
    </div>
    <div class="reduxsa-badge">
        <i class="el el-reduxsa"></i>
        <span>
            <?php printf( __( 'Version %s', 'reduxsa-framework' ), esc_html(ReduxSAFramework::$_version )); ?>
        </span>
    </div>

    <?php $this->actions(); ?>
    <?php $this->tabs(); ?>

    <p class="about-description">
        <?php echo sprintf( __( 'ReduxSA is created by a community of developers world wide. Want to have your name listed too? <a href="%d" target="_blank">Contribute to ReduxSA</a>.', 'reduxsa-framework' ), 'https://github.com/reduxsaframework/reduxsa-framework/blob/master/CONTRIBUTING.md' );?>
    </p>

    <?php echo wp_kses_post($this->contributors()); ?>
</div>