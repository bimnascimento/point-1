<?php

//   ?reset-itens=true
//   ?delete-lavanderia=true




// DELETE TODOS PRODUTOS DA LAVANDERIA
add_action('wp', 'delete_produtos_lavanderia', 1);
function delete_produtos_lavanderia() {

        global $current_user, $wpdb;
        //dump($_GET['delete-lavanderia']);
        if( !isset($_GET['delete-lavanderia']) ) return;


        //if( $_GET['delete-lavanderia'] == 'true' ) $store_id = get_current_user_id();
        //else $store_id = $_GET['delete-lavanderia'];

        //dump($store_id,true);
        $store_id = get_current_user_id();

        //LAVANDERIA
        $current_user = get_user_by( 'id', $store_id );
        $user_role = reset( $current_user->roles );
        if ( !in_array( 'seller', $current_user->roles ) ) return;
        $store_slug = $current_user->user_nicename;

        // DELETE PRODUCTS
        $querystr = "
                    SELECT $wpdb->posts.ID
                    FROM $wpdb->posts
                    WHERE $wpdb->posts.post_type = 'product'
                    AND $wpdb->posts.post_author = '$store_id' ";

        if( isset($_GET['completo']) ) {
              // DELETE TAG - NOME DA LAVANDERIA
              $tag = get_term_by( 'slug', $store_slug, 'product_tag');
              if( !empty($tag) ){
                  $tag = $tag->term_id;
                  wp_delete_term( $tag, 'product_tag' );
                  //dump('EXCLUIU TAG: '.$tag);
              }
        }else{
              $querystr .= " AND $wpdb->posts.post_name <> '$store_slug' ";
        }

        // DELETE PRODUTOS LAVANDERIA
        $post_ids = $wpdb->get_results($querystr);
        if( count($post_ids) > 0 ){
            foreach ($post_ids as $post) {
                  // DELETE PRODUCT
                  wp_delete_post( $post->ID, true);
                  //dump('EXCLUIU PRODUTOS: '.$post->ID);

            }
            echo '<div class="debug"> DELETOU '.count($post_ids).' </div>';
            //exit;
        }



} // FIM DELETE PRODUTOS LAVANDERIAS
?>
