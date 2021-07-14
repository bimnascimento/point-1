<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Database', WPCLEANFIX_TEXTDOMAIN ), null, true ) ?>

  <form action=""
        method="post">

    <?php wp_nonce_field( 'wp_cleanfix' ); ?>

    <p>
      <?php
       echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'Database/ignore_innodb' )
                                                                     ->checked( $plugin->options->get( 'Database/ignore_innodb' ) )
                                                                     ->right_label( __( 'Ignore INNODB tables', WPCLEANFIX_TEXTDOMAIN ) );
       ?>
    </p>

    <div class="wp-cleanfix-info">
      <?php _e( 'The InnoDB tables could not be optimized on some MySQL version / Server.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <?php
       echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'Database/reset_auto_increment' )
                                                                     ->checked( $plugin->options->get( 'Database/reset_auto_increment' ) )
                                                                     ->right_label( __( 'Reset AUTO_INCREMENT', WPCLEANFIX_TEXTDOMAIN ) );
       ?>
    </p>

    <div class="wp-cleanfix-info">
      <?php _e( 'Once the optimization process ends, you are able the reset the AUTO_INCREMENT index function of the tables.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p class="clearfix">
      <button class="button button-primary alignright">Update</button>
    </p>

  </form>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();