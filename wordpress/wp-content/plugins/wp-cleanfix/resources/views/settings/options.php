<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Options', WPCLEANFIX_TEXTDOMAIN ) ) ?>

  <form action=""
        method="post">

    <?php wp_nonce_field( 'wp_cleanfix' ); ?>

    <p>
      <label for="Options/expiry_date">
        <?php _e( 'Expiry date', WPCLEANFIX_TEXTDOMAIN ) ?>:
        <select name="Options/expiry_date"
                id="Options/expiry_date">
          <option <?php selected( '0', $plugin->options->get( 'Options.expiry_date' ) ) ?> value="0"><?php _e( 'Today', WPCLEANFIX_TEXTDOMAIN ) ?></option>
          <option <?php selected( DAY_IN_SECONDS, $plugin->options->get( 'Options.expiry_date' ) ) ?> value="<?php echo DAY_IN_SECONDS ?>"><?php _e( 'Daily', WPCLEANFIX_TEXTDOMAIN ) ?></option>
          <option <?php selected( WEEK_IN_SECONDS, $plugin->options->get( 'Options.expiry_date' ) ) ?> value="<?php echo WEEK_IN_SECONDS ?>"><?php _e( 'Weekly', WPCLEANFIX_TEXTDOMAIN ) ?></option>
        </select>
      </label>
    </p>

    <p>
      <?php
       echo WPCleanFix\PureCSSSwitch\Html\HtmlTagSwitchButton::name( 'Options/safe_mode' )
                                                                     ->checked( $plugin->options->get( 'Options/safe_mode' ) )
                                                                     ->right_label( __( 'Safe mode', WPCLEANFIX_TEXTDOMAIN ) );
       ?>
    </p>

    <div class="wp-cleanfix-info">
      <?php _e( 'We will use the WordPress function <code>get_transient()</code> to remove the expired transients.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p class="clearfix">
      <button class="button button-primary alignright">Update</button>
    </p>

  </form>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();