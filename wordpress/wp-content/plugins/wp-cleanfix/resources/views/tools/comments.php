<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Comments', WPCLEANFIX_TEXTDOMAIN ) ) ?>

<div class="wp-cleanfix-tools-comments-find-replace">
  <form>
    <h3><?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?></h3>

    <div class="wp-cleanfix-info">
      <?php _e( 'This tool will perform a search for a text string within the <strong>comments content</strong> and will replace it with another text. <strong>Be careful because this operation is irreversible.</strong>', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <label for="wp-cleanfix-tools-comments-approved">
        <?php _e( 'Comments approved', WPCLEANFIX_TEXTDOMAIN ) ?>
        <select name="wp-cleanfix-tools-comments-approved"
                id="wp-cleanfix-tools-comments-approved">
          <?php foreach ( $comments->getCommentsApproved() as $key => $value ) : ?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>

    <p>
      <label for="wp-cleanfix-tools-comments-type">
        <?php _e( 'Comment type', WPCLEANFIX_TEXTDOMAIN ) ?>
        <select name="wp-cleanfix-tools-comments-type"
                id="wp-cleanfix-tools-comments-type">
          <option value=""><?php _e( 'All' ) ?></option>
          <?php foreach ( $comments->getCommentsType() as $key => $value ) : ?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>

    <div class="wp-cleanfix-info">
      <?php _e( 'The fields below are <strong>case sensitive</strong>.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <label for="wp-cleanfix-tools-comments-find">
        <?php _e( 'Find', WPCLEANFIX_TEXTDOMAIN ) ?>
        <textarea name="wp-cleanfix-tools-comments-find"
                  id="wp-cleanfix-tools-comments-find"></textarea>
      </label>
    </p>

    <p>
      <label for="wp-cleanfix-tools-comments-replace">
        <?php _e( 'Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
        <textarea name="wp-cleanfix-tools-comments-replace"
                  id="wp-cleanfix-tools-comments-replace"></textarea>
      </label>
    </p>

    <p class="clearfix">
      <button class="button button-secondary wp-clearfix-tools-comments-clear-fields alignleft">
        <?php _e( 'Clear Fields', WPCLEANFIX_TEXTDOMAIN ) ?>
      </button>
      <button class="button button-primary wp-clearfix-tools-comments-find-button alignright">
        <?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
      </button>
    </p>

    <div id="wp-clearfix-tools-comments-feedback"></div>

  </form>
</div>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();

