<?php if ( ! defined( 'ABSPATH' ) ) exit;

WPCleanFix\PureCSSTabs\PureCSSTabsProvider::openTab( __( 'Posts', WPCLEANFIX_TEXTDOMAIN ) ) ?>

<div class="wp-cleanfix-tools-posts-find-replace">
  <form>
    <h3><?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?></h3>

    <div class="wp-cleanfix-info">
      <?php _e( 'This tool will perform a search for a text string within the <strong>posts content</strong> and will replace it with another text. <strong>Be careful because this operation is irreversible.</strong>', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <label for="wp-cleanfix-tools-posts-posttypes">
        <?php _e( 'Post Type', WPCLEANFIX_TEXTDOMAIN ) ?>
        <select name="wp-cleanfix-tools-posts-posttypes"
                id="wp-cleanfix-tools-posts-posttypes">
          <option value=""><?php _e( 'All' ) ?></option>
          <?php foreach ( $posts->getPostTypes() as $key => $value ) : ?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>

    <p>
      <label for="wp-cleanfix-tools-posts-poststatus">
        <?php _e( 'Post Status', WPCLEANFIX_TEXTDOMAIN ) ?>
        <select name="wp-cleanfix-tools-posts-poststatus"
                id="wp-cleanfix-tools-posts-poststatus">
          <option value=""><?php _e( 'All' ) ?></option>
          <?php foreach ( $posts->getPostStatuses() as $key => $value ) : ?>
            <option value="<?php echo $key ?>"><?php echo $value ?></option>
          <?php endforeach; ?>
        </select>
      </label>
    </p>

    <div class="wp-cleanfix-info">
      <?php _e( 'The fields below are <strong>case sensitive</strong>.', WPCLEANFIX_TEXTDOMAIN ) ?>
    </div>

    <p>
      <label for="wp-cleanfix-tools-posts-find">
        <?php _e( 'Find', WPCLEANFIX_TEXTDOMAIN ) ?>
        <textarea name="wp-cleanfix-tools-posts-find"
                  id="wp-cleanfix-tools-posts-find"></textarea>
      </label>
    </p>

    <p>
      <label for="wp-cleanfix-tools-posts-replace">
        <?php _e( 'Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
        <textarea name="wp-cleanfix-tools-posts-replace"
                  id="wp-cleanfix-tools-posts-replace"></textarea>
      </label>
    </p>

    <p class="clearfix">
      <button class="button button-secondary wp-clearfix-tools-posts-clear-fields alignleft">
        <?php _e( 'Clear Fields', WPCLEANFIX_TEXTDOMAIN ) ?>
      </button>
      <button class="button button-primary wp-clearfix-tools-posts-find-button alignright">
        <?php _e( 'Find & Replace', WPCLEANFIX_TEXTDOMAIN ) ?>
      </button>
    </p>

    <div id="wp-clearfix-tools-posts-feedback"></div>

  </form>
</div>

<?php WPCleanFix\PureCSSTabs\PureCSSTabsProvider::closeTab();

