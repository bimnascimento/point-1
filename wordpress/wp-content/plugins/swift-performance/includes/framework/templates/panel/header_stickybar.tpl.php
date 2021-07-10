<?php
    /**
     * The template for the header sticky bar.
     * Override this template by specifying the path where it is stored (templates_path) in your ReduxSA config.
     *
     * @author        ReduxSA Framework
     * @package       ReduxSAFramework/Templates
     * @version:      3.5.7.8
     */
?>
<div id="reduxsa-sticky">
    <div id="info_bar">

        <a href="javascript:void(0);" class="expand_options<?php echo esc_attr(( $this->parent->args['open_expanded'] ) ? ' expanded' : ''); ?>"<?php echo $this->parent->args['hide_expand'] ? ' style="display: none;"' : '' ?>>
            <?php esc_attr_e( 'Expand', 'reduxsa-framework' ); ?>
        </a>

        <div class="reduxsa-action_bar">
            <span class="spinner"></span>
            <?php if ( false === $this->parent->args['hide_save'] ) { ?>
                <?php submit_button( esc_attr__( 'Save Changes', 'reduxsa-framework' ), 'primary', 'reduxsa_save', false ); ?>
            <?php } ?>
            
            <?php if ( false === $this->parent->args['hide_reset'] ) { ?>
                <?php submit_button( esc_attr__( 'Reset Section', 'reduxsa-framework' ), 'secondary', $this->parent->args['opt_name'] . '[defaults-section]', false, array( 'id' => 'reduxsa-defaults-section' ) ); ?>
                <?php submit_button( esc_attr__( 'Reset All', 'reduxsa-framework' ), 'secondary', $this->parent->args['opt_name'] . '[defaults]', false, array( 'id' => 'reduxsa-defaults' ) ); ?>
            <?php } ?>
        </div>
        <div class="reduxsa-ajax-loading" alt="<?php esc_attr_e( 'Working...', 'reduxsa-framework' ) ?>">&nbsp;</div>
        <div class="clear"></div>
    </div>

    <!-- Notification bar -->
    <div id="reduxsa_notification_bar">
        <?php $this->notification_bar(); ?>
    </div>


</div>