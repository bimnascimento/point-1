<?php if ( ! defined( 'ABSPATH' ) ) exit;

if ( empty( $count[ 'count' ] ) ) : ?>

  <div>
    <p><?php _e( 'No matching strings found!', WPCLEANFIX_TEXTDOMAIN ) ?></p>
  </div>

<?php else : ?>

  <div class="wp-cleanfix-tools-comments-feedback-found">
    <?php
    printf( '<p>%s, %s %s </p><p>%s %s</p>',
            sprintf( _n( 'Searched in <strong>%s</strong> comment only', 'Searched in <strong>%s</strong> comments', $count[ 'total_comments' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'total_comments' ] ),
            sprintf( _n( 'only the <strong>%s</strong> string will be replaced', 'the <strong>%s</strong> strings will be replaced', $count[ 'count' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'count' ] ),
            sprintf( _n( 'in <strong>%s</strong> comment only', 'in <strong>%s</strong> comments', $count[ 'affected_comments' ], WPCLEANFIX_TEXTDOMAIN ), $count[ 'affected_comments' ] ),
            sprintf( '<button href="#" data-confirm="%s" class="button button-primary wp-cleanfix-tools-comments-replace-button">%s</button>', __( 'Are you sure to replace the strings? This operation is irreversible!', WPCLEANFIX_TEXTDOMAIN ), __( 'Continue ?', WPCLEANFIX_TEXTDOMAIN ) ),
            sprintf( '<button href="#" class="button button-secondary wp-cleanfix-tools-comments-replace-cancel-button">%s</button>', __( 'Cancel', WPCLEANFIX_TEXTDOMAIN ) )
    );
    ?>
  </div>

<?php endif;
