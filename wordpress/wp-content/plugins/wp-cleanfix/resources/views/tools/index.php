<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="wp-cleanfix-tools wrap">

  <h1><?php _e( 'WP CleanFix Tools', WPCLEANFIX_TEXTDOMAIN ) ?></h1>

  <?php if ( isset( $feedback ) ) : ?>

    <div id="message"
         class="updated notice is-dismissible">
      <p><?php echo $feedback ?></p>
    </div>

  <?php endif; ?>

  <div class="wpbones-tabs">

    <?php
    echo WPCleanFix()->view( 'tools.database' )
                     ->with( 'database', $database );
    ?>

    <?php
    echo WPCleanFix()->view( 'tools.posts' )
                     ->with( 'posts', $posts );
    ?>

    <?php
    echo WPCleanFix()->view( 'tools.comments' )
                     ->with( 'comments', $comments );
    ?>

    <?php
    echo WPCleanFix()->view( 'tools.postmeta' )
                     ->with( 'postmeta', $postmeta );
    ?>

  </div>

</div>