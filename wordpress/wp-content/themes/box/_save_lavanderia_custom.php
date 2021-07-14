<?php

add_action( 'dokan_store_profile_saved', '_save_lavanderia_custom', 10, 2 );
function _save_lavanderia_custom($store_id) {

	global $current_user, $wpdb;

  //$store_id   = get_current_user_id();
  $customer_user = wp_get_current_user();
  $store_info = dokan_get_store_info( $store_id );
  $store_slug = $current_user->user_nicename;
  $store_name = $store_info['store_name'];
  $store_phone = $store_info['phone'];

  // PRODUTO - LAVANDERIA
  $querystr = "
              SELECT $wpdb->posts.ID
              FROM $wpdb->posts
              WHERE $wpdb->posts.post_name = '$store_slug'
              AND $wpdb->posts.post_type = 'product'
              ";
  $post_id = $wpdb->get_var($querystr);
  if( !empty($post_id) ){

            $my_post = array(
                                'ID' => $post_id,
                                'post_title'   => $store_name,
                                //'post_content' => 'This is the updated content.',
            );
            // Update the post into the database
            wp_update_post( $my_post );

            if ( isset($store_info['gravatar']) && $store_info['gravatar'] > 0 ){

                  //_thumbnail_id
                  update_post_meta( $post_id, '_product_image_gallery', $store_info['gravatar']);

            }

  }


  $location = $store_info['address']['endereco'].', '.$store_info['address']['numero'].', '.$store_info['address']['bairro'].', '.$store_info['address']['city'].' - '.$store_info['address']['state'].', '.$store_info['address']['zip'];
  //Av. Barão do Rio Branco, 1171 - Centro, Juiz de Fora - MG, 36013-020, Brasil
  $store_info['find_address'] = $location;
  update_user_meta( $store_id, 'dokan_profile_settings', $store_info );

  //dump($store_slug);
  //dump($store_info);

  //exit;


}
?>
