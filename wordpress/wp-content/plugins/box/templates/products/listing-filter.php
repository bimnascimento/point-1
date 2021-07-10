<?php
/**
 * Dokan Dahsboard Product Listing
 * filter template
 *
 * @since 2.4
 *
 * @package dokan
 */
?>
<?php do_action( 'dokan_product_listing_filter_before_form' ); ?>

    <form class="dokan-form-inline dokan-w6" method="get" >

        <div class="dokan-form-group">
            <?php dokan_product_listing_filter_months_dropdown( get_current_user_id() ); ?>
        </div>

        <div class="dokan-form-group" style="float: none!important;">
            <?php

                //BUSCA CATEGORIA ITENS
                $category = get_term_by( 'slug', 'itens', 'product_cat' );
                $cat_id = $category->term_id;

                $argsCat = array(
                    'number'       => 0,
                    'orderby'      => 'name',
                    'order'        => 'ASC',
                    //'hide_empty'   => 1,
                    'parent'       => $cat_id,
                );
                $lista_categories_not = get_terms( 'product_cat', $argsCat );
                $lista_categories_not_array = array();
                foreach ($lista_categories_not as $cat) {
                    if( strpos($cat->slug, 'servcliente') === false ) continue;
                    array_push($lista_categories_not_array,$cat->term_id);
                    //dump('<b>ID:</b> '.$cat->term_id.'   --   <b>Nome:</b> '.$cat->name.'    --   <b>Slug:</b> '.$cat->slug.'<br/>');
                }
                //exit;
                //dump($_GET['product_cat']);
                ///*


                class WPSE_Cat_Slug_Walker extends Walker_Category{

                  public $cliente = 1;

                  function start_el(&$output, $category, $depth = 0, $args = array(), $id = 0) {

                        $category = get_term_by( 'id', $category->term_id, 'product_cat');
                        //dump($category->slug);
                        //dump($_GET['post_status']);
                        $status = array('publish','draft','publish','closed','open','private','pending');
                        if( isset($_GET['post_status']) ) $status = array($_GET['post_status']);
                        $argsItem = array(
                            'posts_per_page' => -1,
                            'post_type' => 'product',
                            'post_status' => $status,
                            'orderby' => 'title',
                            'order' => 'ASC',
                            'ignore_sticky_posts' =>  true,
                            'no_found_rows'  => true
                        );
                        $argsItem['tax_query'] = array(
                                'relation' => 'AND',
                                array(
                                    'taxonomy' => 'product_cat',
                                    'field' => 'slug',
                                    'terms' => $category->slug,
                                ),
                                array(
                                    'taxonomy' => 'product_tag',
                                    //'field' => 'slug',
                                    'field' => 'id',
                                    'terms' => $this->cliente,
                                ),
                        );
                        $products = new WP_Query( $argsItem );
                        $total = $products->post_count;
                        //dump($category->slug);
                        //dump($total);
                        //dump($products,true);
                        //wp_reset_postdata();
                        if( $total == 0 ) return;


                        $pad = str_repeat('&nbsp;', $depth * 3);
                        $output .= "\t<option class=\"level-$depth\" value=\"".$category->term_id."\"";
                        if ( $category->term_id == $args['selected'] )
                            $output .= ' selected="selected"';
                        $output .= '>';
                        $output .= $pad.$category->name; // The Slug!
                        //$output .= $this->cliente;
                        if ( $args['show_count'] )
                            $output .= '&nbsp;&nbsp;('. $total .')';
                        $output .= "</option>\n";

                    }
                }
                $wpse_cat_slug_walker = new WPSE_Cat_Slug_Walker;

                $customer_user = wp_get_current_user();
                $cliente_slug = $customer_user->user_nicename;
                //$cliente_slug = get_post_field( 'post_name', get_post() );
                //dump($cliente_slug,true);
                $cliente = get_term_by('slug', $cliente_slug, 'product_tag');
                $cliente = $cliente->term_id;
                //dump($cliente,true);
                $wpse_cat_slug_walker->cliente = $cliente;



                //*/
                //dump($lista_categories_not_array);

                wp_dropdown_categories( array(
                    'show_option_none' => __( '- Select a category -', 'dokan' ),
                    'hierarchical'     => 1,
                    'hide_empty'       => 0,
                      //'show_option_all' => All,
                    //'hide_if_empty' => 1,
                    'name'             => 'product_cat',
                    'id'               => 'product_cat',
                    'taxonomy'         => 'product_cat',
                    'title_li'         => '',
                    'class'            => 'product_cat dokan-form-control chosen combo-cat',
                    'exclude'          => $lista_categories_not_array,
                    'order'            => 'ASC',
                    'orderby'          => 'name',
                    //'echo'             => 1,
                    'show_count'       => 1,
                    'parent'           => $cat_id,
                    'selected'         => isset( $_GET['product_cat'] ) ? $_GET['product_cat'] : '-1',
                    'walker'           => $wpse_cat_slug_walker // the walker
                ) );

                //global $wp_query;
                //echo $wp_query->post_count;
                $status = '';
                if( isset($_GET['post_status']) ) $status = $_GET['post_status'];
            ?>
        </div>
        <script type="text/javascript">
      		<!--
      		var dropdown = document.getElementById("product_cat");
      		function onCatChange() {
      			if ( dropdown.options[dropdown.selectedIndex].value > 0 ) {
      				location.href = "<?php echo esc_url( home_url( '/lavanderias/area-administrativa/products/' ) ); ?>?date=0&product_listing_filter=ok&product_cat="+dropdown.options[dropdown.selectedIndex].value+"&post_status=<?php echo $status; ?>";
              MyObject2.loadingSite();
      			}
      		}
      		dropdown.onchange = onCatChange;
      		-->
      	</script>

        <?php
        if ( isset( $_GET['product_search_name'] ) ) { ?>
            <input type="hidden" name="product_search_name" value="<?php echo $_GET['product_search_name']; ?>">
        <?php }
        ?>

        <button type="submit" name="product_listing_filter" value="ok" class="dokan-btn dokan-btn-theme"><?php _e( 'Filter', 'dokan'); ?></button>

    </form>

    <?php do_action( 'dokan_product_listing_filter_before_search_form' ); ?>

    <form method="get" class="dokan-form-inline dokan-w6">



        <?php wp_nonce_field( 'dokan_product_search', 'dokan_product_search_nonce' ); ?>

        <div class="dokan-form-group dokan-right">
            <input type="text" class="dokan-form-control" name="product_search_name" placeholder="<?php _e( 'Search Products', 'dokan' ) ?>" value="<?php echo isset( $_GET['product_search_name'] ) ? $_GET['product_search_name'] : '' ?>">
        </div>

        <?php
        if ( isset( $_GET['product_cat'] ) ) { ?>
            <input type="hidden" name="product_cat" value="<?php echo $_GET['product_cat']; ?>">
        <?php }

        if ( isset( $_GET['date'] ) ) { ?>
            <input type="hidden" name="date" value="<?php echo $_GET['date']; ?>">
        <?php }
        ?>
        <button type="submit" name="product_listing_search" value="ok" class="dokan-btn dokan-btn-theme dokan-right"><?php _e( 'Search', 'dokan'); ?></button>
    </form>

    <?php do_action( 'dokan_product_listing_filter_after_form' ); ?>
