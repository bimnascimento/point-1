<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $count['affected_postmeta'] ) ) : ?>

  <div>
    <p><?php _e( 'No matching strings found!', WPCLEANFIX_TEXTDOMAIN ) ?></p>
  </div>

<?php else : ?>

  <div class="wp-cleanfix-tools-posts-feedback-found">
  <?php
  printf( '<p>%s, %s</p><p>%s %s</p>',
          sprintf( _n( 'Searched in <strong>%s</strong> postmeta only', 'Searched in <strong>%s</strong> postmeta', $count[ 'count' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'count' ] ),
          sprintf( _n( 'only <strong>%s</strong> matched will be replaced', 'the <strong>%s</strong> matches will be replaced', $count[ 'affected_postmeta' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'affected_postmeta' ] ),
          sprintf( '<button href="#" data-confirm="%s" class="button button-primary wp-cleanfix-tools-postmeta-replace-button">%s</button>', __( 'Are you sure to replace the strings? This operation is irreversible!', WPCLEANFIX_TEXTDOMAIN ), __( 'Continue ?', WPCLEANFIX_TEXTDOMAIN )  ),
          sprintf( '<button href="#" class="button button-secondary wp-cleanfix-tools-postmeta-replace-cancel-button">%s</button>', __( 'Cancel', WPCLEANFIX_TEXTDOMAIN ) )
  );
  ?>
  </div>

<?php endif;
