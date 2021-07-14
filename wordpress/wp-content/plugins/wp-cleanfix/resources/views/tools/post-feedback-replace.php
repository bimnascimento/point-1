<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $count['count'] ) ) : ?>

  <div>
    <p><?php _e( 'No matching strings found!', WPCLEANFIX_TEXTDOMAIN ) ?></p>
  </div>

<?php else : ?>

  <div class="wp-cleanfix-tools-posts-feedback-after-replace">
  <?php
  printf( '<p>%s, %s %s </p><p>%s</p>',
          sprintf( _n( 'Searched in <strong>%s</strong> post only', 'Searched in <strong>%s</strong> posts', $count[ 'total_posts' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'total_posts' ] ),
          sprintf( _n( 'Replaced <strong>%s</strong> string only', 'Replaced <strong>%s</strong> strings', $count[ 'count' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'count' ] ),
          sprintf( _n( 'in <strong>%s</strong> post only', 'in <strong>%s</strong> posts', $count[ 'affected_posts' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'affected_posts' ] ),
          sprintf( '<button href="#" class="button button-hero button-primary wp-cleanfix-tools-posts-ok-button">%s</button>', __( 'Ok', WPCLEANFIX_TEXTDOMAIN ) )
  );
  ?>
  </div>

<?php endif;
