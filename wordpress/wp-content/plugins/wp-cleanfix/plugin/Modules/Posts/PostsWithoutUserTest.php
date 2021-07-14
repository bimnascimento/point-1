<?php

namespace WPCleanFix\Modules\Posts;

use WPCleanFix\Modules\Test;

class PostsWithoutUserTest extends Test
{
  public function test()
  {
    // for this method see parent module
    $issues = $this->getPostsWithoutUser();

    $this->issues( $issues )
         ->detailSelect(
           sprintf(
             _n( 'You have %s post not correctly assigned to any author',
                 'You have %s posts not correctly assigned to any author',
                 count( $issues ), WPCLEANFIX_TEXTDOMAIN
             ),
             count( $issues )
           ),
           [
             'post_title' => '%s',
           ],
           $this->getUsersSelect()
         )
         ->beforeSend( 'wp_cleanfix_user_before_send' )
         ->filter( 'wp_cleanfix_user_id' )
         ->fix( __( 'Fix: click here to repair posts without authors.', WPCLEANFIX_TEXTDOMAIN ) );

    return $this;
  }

  protected function getUsersSelect()
  {
    ob_start(); ?>
    <select name="wp-cleanfix-user">
      <option selected
              disabled="disabled"
              style="display:none"><?php _e( 'Choose a new user...', WPCLEANFIX_TEXTDOMAIN ) ?></option>
      <?php foreach ( get_users() as $user ) : ?>
        <option value="<?php echo $user->ID ?>"><?php printf( '%s (%s)', $user->display_name, $user->user_email ) ?></option>
      <?php endforeach; ?>
    </select>
    <script>
      jQuery( function( $ )
      {
        function wp_cleanfix_user_before_send()
        {
          const user_id = $( 'select[name="wp-cleanfix-user"]' ).val();

          if( user_id === null ) {
            alert( '<?php _e( 'Warning! You must select a User before fix this issue.', WPCLEANFIX_TEXTDOMAIN ) ?>' );

            return false;
          }

          return true;
        }

        wpbones_add_filter( 'wp_cleanfix_user_before_send', wp_cleanfix_user_before_send );

        function wp_cleanfix_user_id()
        {
          const user_id = $( 'select[name="wp-cleanfix-user"]' ).val();

          return { user_id : user_id };
        }

        wpbones_add_filter( 'wp_cleanfix_user_id', wp_cleanfix_user_id );
      } );
    </script>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
  }

  public function cleanFix()
  {
    // TODO: Implement fix() method.

    if ( func_num_args() ) {
      $args    = func_get_arg( 0 );
      $user_id = $args[ 'user_id' ];
      $this->updatePostsWithoutUser( $user_id );
    }

    // for this method see parent module

    return $this;
  }

  public function getName()
  {
    return __( 'Posts without author', WPCLEANFIX_TEXTDOMAIN );
  }

  public function getDescription()
  {
    return __( 'Posts without author.', WPCLEANFIX_TEXTDOMAIN );

  }
}
