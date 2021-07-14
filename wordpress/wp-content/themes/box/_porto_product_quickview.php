<?php
if ( ! defined( 'ABSPATH' ) ) exit;


// Quick View Html
add_action( 'wp_ajax_porto_product_quickview', 'porto_product_quickview');
add_action( 'wp_ajax_nopriv_porto_product_quickview', 'porto_product_quickview');
//add_action( 'wp_enqueue_scripts', 'porto_product_quickview' );
function porto_product_quickview() {
    global $post, $product;
    $post = get_post($_GET['pid']);
    $product = wc_get_product( $post->ID );
    if( !$product || ( $product->manage_stock == 'yes' && $product->stock_quantity == 0 ) || $product->status != 'publish' ){
      echo 'Não foi possivel carregar este item.<br/> Por favor, tente mais tarde.';
      exit;
    }
    wp_enqueue_script( 'jquery' );
    if ( post_password_required() ) {
        echo get_the_password_form();
        die();
        return;
    }
    ?>
    <script>
    var reload = !window["nav-panel"];
    if( reload ) {
      //var url = window.location.href;
      //var value = url = url.slice( 0, url.indexOf('?') );
      window.location = '<?php echo home_url(); ?>';
    }
    </script>
    <div class="quickview-wrap quickview-wrap-<?php echo $post->ID ?> single-product">
        <div class="product product-summary-wrap">
            <div class="row">
              <div class="col-md-12 summary entry-summary">
                    <?php
          do_action( 'woocommerce_before_single_product_summary' );
					remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
					add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 25 );
          do_action( 'woocommerce_single_product_summary' );
                    ?>
                    <script type="text/javascript">
                        <?php
                        $suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
                        $assets_path          = esc_url(str_replace( array( 'http:', 'https:' ), '', WC()->plugin_url() )) . '/assets/';
                        $frontend_script_path = $assets_path . 'js/frontend/';
                        ?>
                        var wc_add_to_cart_variation_params = <?php echo array2json(apply_filters( 'wc_add_to_cart_variation_params', array(
                            'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
					                  'i18n_make_a_selection_text'       => esc_attr__( 'Select product options before adding this product to your cart.', 'woocommerce' ),
					                  'i18n_unavailable_text'            => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
                        ) )) ?>;
                        jQuery(document).ready(function($) {
                            $.getScript('<?php echo $frontend_script_path . 'add-to-cart-variation' . $suffix . '.js' ?>');
                        });
                    </script>
                </div><!-- .summary -->
            </div>
        </div>
    </div>
    <?php
    die();
}

?>
