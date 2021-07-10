<?php
/**
 *  Dokan Product category Walker Class
 *  @author weDevs
 */
class DokanCategoryWalker extends Walker_Category{
    public function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0 ) {

      global $current_user, $wpdb;

      $customer_id = get_current_user_id();
      $customer_user = wp_get_current_user();
      $customer_name = $current_user->display_name;
      $customer_login = $current_user->user_login;
      $customer_email = $current_user->user_email;


        $args = wp_parse_args(array(
            'name'    => 'product_cat',
        ), $args);

          /*
          if( !empty($category->description) ){
              $mostra = false;
              $capabilities_cat = explode(';',$category->description);
              foreach ($capabilities_cat as $capabilities) {
                    if( current_user_can($capabilities) )  $mostra = true;
              }
              if(!$mostra) return;
          }
          */

          

        extract($args);
        ob_start(); ?>
        <li>
            <input type="checkbox" <?php echo checked( in_array( $category->term_id, $selected ), true ); ?> id="category-<?php print $category->term_id; ?>" name="<?php print $name; ?>[]" value="<?php print $category->term_id; ?>" />
            <label for="category-<?php print $category->term_id; ?>">
                <?php print esc_attr( $category->name ); ?>
            </label>
        <?php // closing LI is added inside end_el
        $output .= ob_get_clean();
    }
}
