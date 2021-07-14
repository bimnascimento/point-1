<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Post Meta', WPCLEANFIX_TEXTDOMAIN ) ) ?>

  <div class="wp-cleanfix-tools-postmeta-find-replace">
    <form>
      <h3><?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?></h3>

      <div class="wp-cleanfix-info">
        <?php _e( 'This tool will perform a search for a text string within the <strong>postmeta content</strong> and will replace it with another text. <strong>Be careful because this operation is irreversible.</strong>', WPCLEANFIX_TEXTDOMAIN ) ?>
      </div>

      <p>
        <label for="wp-cleanfix-tools-postmeta-column">
          <?php _e( 'Column', WPCLEANFIX_TEXTDOMAIN ) ?>
          <select name="wp-cleanfix-tools-postmeta-column"
                  id="wp-cleanfix-tools-postmeta-column">
            <option value="meta_key">meta_key</option>
            <option value="meta_value">meta_value</option>
          </select>
        </label>
      </p>

      <div class="wp-cleanfix-info">
        <?php _e( 'The fields below are <strong>case sensitive</strong>.', WPCLEANFIX_TEXTDOMAIN ) ?>
      </div>

      <p>
        <label for="wp-cleanfix-tools-postmeta-find">
          <?php _e( 'Find', WPCLEANFIX_TEXTDOMAIN ) ?>
          <textarea name="wp-cleanfix-tools-postmeta-find"
                    id="wp-cleanfix-tools-postmeta-find"></textarea>
        </label>
      </p>

      <p>
        <label for="wp-cleanfix-tools-postmeta-replace">
          <?php _e( 'Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
          <textarea name="wp-cleanfix-tools-postmeta-replace"
                    id="wp-cleanfix-tools-postmeta-replace"></textarea>
        </label>
      </p>

      <p class="clearfix">
        <button class="button button-secondary wp-clearfix-tools-postmeta-clear-fields alignleft">
          <?php _e( 'Clear Fields', WPCLEANFIX_TEXTDOMAIN ) ?>
        </button>
        <button class="button button-primary wp-clearfix-tools-postmeta-find-button alignright">
          <?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
        </button>
      </p>

      <div id="wp-clearfix-tools-postmeta-feedback"></div>

    </form>
  </div>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();

