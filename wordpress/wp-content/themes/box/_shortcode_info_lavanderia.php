<?php
if ( ! defined( 'ABSPATH' ) ) exit;


// INFO LAVANDERIA - PAGE LAVANDERIA
//add_action( 'get_suporte', 'generate_support_button' );
function shortcode_info_lavanderia($atts, $content, $tag) {
      $return = '';
      ob_start();
      global $product, $post;
      //$cliente = $product->slug;
      //dump($product->id);
      //exit;
      $product_id = get_the_ID();
      $product = wc_get_product( $product_id );
      $slug = get_post_field( 'post_name', get_post() );
      //dump($slug);
      $author = get_the_author_meta('ID');
      //dump( $author );
      //$author = get_user_by( 'id', $product->post->post_author );
      $store_info = dokan_get_store_info( $author );
      //$store_tabs    = dokan_get_store_tabs( $store_user->ID );
      $social_fields = dokan_get_social_profile_fields();
      //echo '<div class="suporte-lavanderia">';
      //do_action( 'dokan_after_store_tabs', $author->ID );
      //echo '</div>';
      //dump( $store_info['gravatar'] );
      //if( !empty($store_info["social"]) ){ $classCol='col-sm-4'; }else{ $classCol='col-sm-6'; };

      //do_shortcode('[get_ip]');
      //echo do_shortcode('[get_ip]');
      //get_ip();
      //echo get_ip();

      $setting_go_vacation = !empty($store_info['setting_go_vacation']) ? $store_info['setting_go_vacation'] : false;
  	  $settings_closing_style = !empty($store_info['settings_closing_style']) ? $store_info['settings_closing_style'] : false;
      //dump($setting_go_vacation);
      //dump($settings_closing_style);

      $post_thumbnail_id = get_post_thumbnail_id( get_the_ID() );

      ?>
      <div class="row page-lavanderia-info">
        <div class="col-sm-12"></div>
        <?php if ( isset($store_info['gravatar']) &&  $store_info['gravatar'] > 0 ){ ?>
              <div class="col-sm-2 logo-page-lavanderia zoom">
                <a href="<?php echo dokan_get_store_url( $author ); ?>" title="<?php echo $store_info['store_name']; ?>" class="click-radar">
                  <?php echo get_avatar( $author , 100 ); ?>
                </a>
              </div>
        <?php }

        if( !empty($post_thumbnail_id) ){
          echo '<div class="col-sm-6 info">';
        }else{
          echo '<div class="col-sm-10 info">';
        }
        ?>
            <div class="title"><?php echo do_shortcode('[dhvc_woo_product_page_title el_class="title-lavanderia"]'); ?></div>
            <div class="vote"><?php echo do_shortcode('[rating_form id="1"]'); ?></div>
            <?php if ( !empty( $store_info['address']['endereco'] ) ) { ?>
                  <div class="details-endereco">
                    <i class="fa fa-map-marker"></i> <b>Endereço:</b>
                    <a class="click-radar" href="<?php echo home_url('../regioes-atendidas'); ?>"><?php
                        $endereco = $store_info['address']['endereco'].', '.$store_info['address']['numero'].', <br/>';
                        $complemento = (!empty($store_info['address']['complemento'])) ? $store_info['address']['complemento'].'<br/>' : '';
                        $bairro = $store_info['address']['bairro'].', '.$store_info['address']['city'].' - '.$store_info['address']['state'];
                        $endereco = $endereco.$complemento.$bairro;
                        echo $endereco;
                    ?>
                    </a>
                  </div>
            <?php } ?>
            <?php if ( !empty( $store_info['find_address'] ) ) { ?>
              <div class="calcula-cep">
                <?php
                 echo ( $store_info['find_address'] );
                 ?>
              </div>
              <div class="distancia-cep"></div>
            <?php } ?>
            <?php if ( isset( $store_info['phone'] ) && !empty( $store_info['phone'] ) ) { ?>
                <div class="dokan-store-phone">
                    <i class="fa fa-mobile"></i>
                    <?php if(is_user_logged_in()): ?>
                    <a href="tel:<?php echo esc_html( $store_info['phone'] ); ?>"><?php echo esc_html( $store_info['phone'] ); ?></a>
                  <?php else: ?>
                    <a href="<?php echo home_url('/login/')?>" class="click-login">ver telefone</a>
                  <?php endif; ?>
                </div>
            <?php } ?>
            <?php /* if ( isset( $store_info['show_email'] ) && $store_info['show_email'] == 'yes' ) { ?>
                <li class="dokan-store-email">
                    <i class="fa fa-envelope-o"></i>
                    <a href="mailto:<?php echo antispambot( $store_user->user_email ); ?>"><?php echo antispambot( $store_user->user_email ); ?></a>
                </li>
            <?php } */ ?>
            <?php if ( !empty( $store_info['social'] ) ){ ?>
                <div class="store-social-wrapper">
                    <ul class="store-social">
                        <?php foreach( $social_fields as $key => $field ) { ?>
                            <?php if ( isset( $store_info['social'][ $key ] ) && !empty( $store_info['social'][ $key ] ) ) { ?>
                                <li>
                                    <a href="<?php echo esc_url( $store_info['social'][ $key ] ); ?>" target="_blank"><i class="fa fa-<?php echo $field['icon']; ?>"></i></a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                    </ul>
                </div>
            <?php } ?>
            <?php
            //dump($setting_go_vacation);
            if( isset($store_info['show_support_btn']) && $store_info['show_support_btn'] == 'yes' && $setting_go_vacation=='no' ){
                echo do_shortcode('[shortcode_get_suporte]');
            }
            ?>
        </div>


        <?php

        //dump($post_thumbnail_id);
        if( !empty($post_thumbnail_id) ){ ?>
            <div class="col-sm-4 foto">
                <?php
                //dump($product->image_id);
                echo do_shortcode('[vc_single_image image="'.$post_thumbnail_id.'" img_size="full" alignment="center" style="vc_box_shadow_3d" onclick="link_image" label="'.$store_info['store_name'].'" el_class="zoom foto-lavanderia"]');
                ?>
            </div>
        <?php } ?>
      </div>

      <?php
      $return = ob_get_contents();
      ob_get_clean();
      return $return;
}
add_shortcode('shortcode_info_lavanderia', 'shortcode_info_lavanderia');
?>
