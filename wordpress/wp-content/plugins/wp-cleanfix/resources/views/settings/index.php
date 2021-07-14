<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wp-cleanfix-settings wrap">

  <h1><?php _e( 'WP CleanFix Settings', WPCLEANFIX_TEXTDOMAIN ) ?></h1>

  <?php if ( isset( $feedback ) ) : ?>

    <div id="message"
         class="updated notice is-dismissible"><p><?php echo $feedback ?></p></div>

  <?php endif; ?>

  <div class="wpbones-tabs">
    <?php echo WPCleanFix()->view( 'settings.database' )->with( 'plugin', $plugin ) ?>
    <?php echo WPCleanFix()->view( 'settings.options' )->with( 'plugin', $plugin ) ?>
  </div>

</div>