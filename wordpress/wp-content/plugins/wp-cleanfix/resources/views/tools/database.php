<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Compact index', WPCLEANFIX_TEXTDOMAIN ), null, true ) ?>

<div>
  <form>
    <h3><?php _e( 'Compact index', WPCLEANFIX_TEXTDOMAIN ) ?></h3>

    <div class="wp-cleanfix-info">
      <?php _e( 'This tool will perform a datbase table index compact on the following tables.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <?php
       echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'wp-cleanfix-tools-database-make-backup' )
                                                                     ->checked( true )
                                                                     ->right_label( __( 'Make a backup copy before compact', WPCLEANFIX_TEXTDOMAIN ) );
       ?>
    </p>

    <?php echo WPCleanFix()->view( 'tools.database-tables' )->with( 'database', $database ) ?>

  </form>

</div>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();
