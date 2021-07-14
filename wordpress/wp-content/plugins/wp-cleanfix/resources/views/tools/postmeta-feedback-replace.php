<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $count['affected_postmeta'] ) ) : ?>

  <div>
    <p><?php _e( 'No matching strings found!', WPCLEANFIX_TEXTDOMAIN ) ?></p>
  </div>

<?php else : ?>

  <div class="wp-cleanfix-tools-postmeta-feedback-after-replace">
  <?php
  printf( '<p>%s, %s</p><p>%s</p>',
          sprintf( _n( 'Searched in <strong>%s</strong> postmeta only', 'Searched in <strong>%s</strong> postmeta', $count[ 'total_postmeta' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'total_postmeta' ] ),
          sprintf( _n( 'Replaced <strong>%s</strong> string only', 'Replaced <strong>%s</strong> strings', $count[ 'affected_postmeta' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'affected_postmeta' ] ),
          sprintf( '<button href="#" class="button button-hero button-primary wp-cleanfix-tools-postmeta-ok-button">%s</button>', __( 'Ok', WPCLEANFIX_TEXTDOMAIN ) )
  );
  ?>
  </div>

<?php endif;
