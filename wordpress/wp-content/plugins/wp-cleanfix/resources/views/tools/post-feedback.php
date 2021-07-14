<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $count['count'] ) ) : ?>

  <div>
    <p><?php _e( 'No matching strings found!', WPCLEANFIX_TEXTDOMAIN ) ?></p>
  </div>

<?php else : ?>

  <div class="wp-cleanfix-tools-posts-feedback-found">
  <?php
  printf( '<p>%s, %s %s </p><p>%s %s</p>',
          sprintf( _n( 'Searched in <strong>%s</strong> post only', 'Searched in <strong>%s</strong> posts', $count[ 'total_posts' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'total_posts' ] ),
          sprintf( _n( 'only the <strong>%s</strong> string will be replaced', 'the <strong>%s</strong> strings will be replaced', $count[ 'count' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'count' ] ),
          sprintf( _n( 'in <strong>%s</strong> post only', 'in <strong>%s</strong> posts', $count[ 'affected_posts' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'affected_posts' ] ),
          sprintf( '<button href="#" data-confirm="%s" class="button button-primary wp-cleanfix-tools-posts-replace-button">%s</button>', __( 'Are you sure to replace the strings? This operation is irreversible!', WPCLEANFIX_TEXTDOMAIN ), __( 'Continue ?', WPCLEANFIX_TEXTDOMAIN )  ),
          sprintf( '<button href="#" class="button button-secondary wp-cleanfix-tools-posts-replace-cancel-button">%s</button>', __( 'Cancel', WPCLEANFIX_TEXTDOMAIN ) )
  );
  ?>
  </div>

<?php endif;
