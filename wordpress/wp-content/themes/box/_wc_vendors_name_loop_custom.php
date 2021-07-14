<?php
if ( ! defined( 'ABSPATH' ) ) exit;


// INFO LAVANDERIA - LISTA LAVANDERIAS
function wc_vendors_name_loop_custom() {
    global $product;

    //$author     = get_user_by( 'id', $product->post->post_author );
    //$store_info = dokan_get_store_info( $author->ID );

    //$cliente = $product->slug;
    //dump($product->id);
    //exit;
    $product = wc_get_product( get_the_ID() );
    //$slug = get_post_field( 'post_name', get_post() );
    //dump($slug);
    $author = get_the_author_meta('ID');
    //dump( $author );
    //$author = get_user_by( 'id', $product->post->post_author );
    $store_info = dokan_get_store_info( $author );
    //$store_tabs    = dokan_get_store_tabs( $store_user->ID );
    //$social_fields = dokan_get_social_profile_fields();
    //echo '<div class="suporte-lavanderia">';
    //do_action( 'dokan_after_store_tabs', $author->ID );
    //echo '</div>';
    //dump( $store_info['gravatar'] );

    $setting_go_vacation = !empty($store_info['setting_go_vacation']) ? $store_info['setting_go_vacation'] : false;
    $settings_closing_style = !empty($store_info['settings_closing_style']) ? $store_info['settings_closing_style'] : false;

    //dump($setting_go_vacation);

		if ( !empty( $store_info['store_name'] ) ) { ?>
                <span class="details-lavanderia">
                	<?php
                  //dump($store_info);
                  //echo $store_info['price'];
                  //echo $store_info['store_name'];
                  ?>
                  <?php //printf( '<a href="%s">%s</a>', dokan_get_store_url( $author->ID ), $store_info['store_name'] ); ?>
                  <?php /* ?>
                  <div class="profile-img">
                        <?php echo get_avatar( $author->ID , 30 ); ?>
                  </div>
                  <?php */ ?>

                  <?php if ( !empty( $store_info['address']['endereco'] ) ) { ?>
                        <div class="details-endereco">
                          <i class="fa fa-map-marker"></i>
                          <?php
                              $endereco = $store_info['address']['endereco'].', '.$store_info['address']['numero'].', ';
                              $complemento = (!empty($store_info['address']['complemento'])) ? $store_info['address']['complemento'].'' : '';
                              $bairro = $store_info['address']['bairro'];
                              $endereco = $endereco.$bairro.$complemento;
                              //$bairro = $store_info['address']['bairro'].', '.$store_info['address']['city'].' - '.$store_info['address']['state'];
                              //$endereco = $endereco.$complemento.$bairro;
                              //echo $endereco;
                              echo substr($endereco,0,34).'...';
                          ?>
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

                  <?php echo do_shortcode('[rating_form id="1"]'); ?>
                </span>
                <?php
                if( $setting_go_vacation == 'no' ) {
                ?>
                  <a href="<?php echo dokan_get_store_url( $author ); ?>" title="<?php echo $store_info['store_name']; ?>" class="click-radar button btn-pedido-lavanderia"> <i class="fa fa-calendar"></i> Agende Coleta!</a>
                <?php
                }
                ?>
 		<?php }
}
add_action( 'woocommerce_after_shop_loop_item_title', 'wc_vendors_name_loop_custom', 20 );
?>
