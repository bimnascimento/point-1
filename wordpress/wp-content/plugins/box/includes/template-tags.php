<?php
/**
 * Custom template tags for this theme.
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package dokan
 */

if ( ! function_exists( 'dokan_content_nav' ) ) :

/**
 * Display navigation to next/previous pages when applicable
 */
function dokan_content_nav( $nav_id, $query = null ) {
    global $wp_query, $post;

    if ( $query ) {
        $wp_query = $query;
    }

    // Don't print empty markup on single pages if there's nowhere to navigate.
    if ( is_single() ) {
        $previous = ( is_attachment() ) ? get_post( $post->post_parent ) : get_adjacent_post( false, '', true );
        $next = get_adjacent_post( false, '', false );

        if ( !$next && !$previous )
            return;
    }

    // Don't print empty markup in archives if there's only one page.
    if ( $wp_query->max_num_pages < 2 && ( is_home() || is_archive() || is_search() ) )
        return;

    $nav_class = 'site-navigation paging-navigation';
    if ( is_single() )
        $nav_class = 'site-navigation post-navigation';
    ?>
    <nav role="navigation" id="<?php echo $nav_id; ?>" class="<?php echo $nav_class; ?>">

        <ul class="pager">
        <?php if ( is_single() ) : // navigation links for single posts  ?>

            <li class="previous">
                <?php previous_post_link( '%link', _x( '&larr;', 'Previous post link', 'dokan' ) . ' %title' ); ?>
            </li>
            <li class="next">
                <?php next_post_link( '%link', '%title ' . _x( '&rarr;', 'Next post link', 'dokan' ) ); ?>
            </li>

        <?php endif; ?>
        </ul>


        <?php if ( $wp_query->max_num_pages > 1 && ( is_home() || is_archive() || is_search() ) ) : // navigation links for home, archive, and search pages ?>
            <?php dokan_page_navi( '', '', $wp_query ); ?>
        <?php endif; ?>

    </nav><!-- #<?php echo $nav_id; ?> -->
    <?php
}

endif;

if ( ! function_exists( 'dokan_page_navi' ) ) :

function dokan_page_navi( $before = '', $after = '', $wp_query ) {

    $posts_per_page = intval( get_query_var( 'posts_per_page' ) );
    $paged = intval( get_query_var( 'paged' ) );
    $numposts = $wp_query->found_posts;
    $max_page = $wp_query->max_num_pages;
    if ( $numposts <= $posts_per_page ) {
        return;
    }
    if ( empty( $paged ) || $paged == 0 ) {
        $paged = 1;
    }
    $pages_to_show = 7;
    $pages_to_show_minus_1 = $pages_to_show - 1;
    $half_page_start = floor( $pages_to_show_minus_1 / 2 );
    $half_page_end = ceil( $pages_to_show_minus_1 / 2 );
    $start_page = $paged - $half_page_start;
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }
    $end_page = $paged + $half_page_end;
    if ( ($end_page - $start_page) != $pages_to_show_minus_1 ) {
        $end_page = $start_page + $pages_to_show_minus_1;
    }
    if ( $end_page > $max_page ) {
        $start_page = $max_page - $pages_to_show_minus_1;
        $end_page = $max_page;
    }
    if ( $start_page <= 0 ) {
        $start_page = 1;
    }

    echo $before . '<div class="dokan-pagination-container"><ul class="dokan-pagination">' . "";
    if ( $paged > 1 ) {
        $first_page_text = "&laquo;";
        echo '<li class="prev"><a href="' . get_pagenum_link() . '" title="First">' . $first_page_text . '</a></li>';
    }

    $prevposts = get_previous_posts_link( '&larr; Previous' );
    if ( $prevposts ) {
        echo '<li>' . $prevposts . '</li>';
    } else {
        echo '<li class="disabled"><a href="#">' . __( '&larr; Previous', 'dokan' ) . '</a></li>';
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        if ( $i == $paged ) {
            echo '<li class="active"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . get_pagenum_link( $i ) . '">' . number_format_i18n( $i ) . '</a></li>';
        }
    }
    echo '<li class="">';
    next_posts_link( __('Next &rarr;', 'dokan') );
    echo '</li>';
    if ( $end_page < $max_page ) {
        $last_page_text = "&rarr;";
        echo '<li class="next"><a href="' . get_pagenum_link( $max_page ) . '" title="Last">' . $last_page_text . '</a></li>';
    }
    echo '</ul></div>' . $after . "";
}

endif;

function dokan_product_dashboard_errors() {
    $type = isset( $_GET['message'] ) ? $_GET['message'] : '';

    switch ($type) {
        case 'product_deleted':
            dokan_get_template_part( 'global/dokan-success', '', array( 'deleted' => true, 'message' => __( 'Product succesfully deleted', 'dokan' ) ) );
            break;

        case 'error':
            dokan_get_template_part( 'global/dokan-error', '', array( 'deleted' => false, 'message' =>  __( 'Something went wrong!', 'dokan' ) ) );
            break;
    }
}

function dokan_product_listing_status_filter() {
    $permalink = dokan_get_navigation_url( 'products' );
    $status_class = isset( $_GET['post_status'] ) ? $_GET['post_status'] : 'all';
    $post_counts = dokan_count_posts( 'product', get_current_user_id() );

    dokan_get_template_part( 'products/listing-status-filter', '', array(
        'permalink'    => $permalink,
        'status_class' => $status_class,
        'post_counts'  => $post_counts,
    ) );
}

function dokan_order_listing_status_filter() {
    $orders_url = dokan_get_navigation_url( 'orders' );

    $status_class         = isset( $_GET['order_status'] ) ? $_GET['order_status'] : 'all';
    $orders_counts        = dokan_count_orders( get_current_user_id() );
    $order_date           = ( isset( $_GET['order_date'] ) ) ? $_GET['order_date'] : '';
    $date_filter          = array();
    $all_order_url        = array();
    $complete_order_url   = array();
    $processing_order_url = array();
    $pending_order_url    = array();
    $on_hold_order_url    = array();
    $canceled_order_url   = array();
    $refund_order_url     = array();
    $failed_order_url     = array();

    $pagamento_confirmado_order_url     = array();
    $saiu_para_entrega_order_url     = array();
    $saiu_para_coleta_order_url     = array();
    $lavando_pecas_order_url     = array();
    ?>

    <ul class="list-inline order-statuses-filter">
        <li<?php echo $status_class == 'all' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $all_order_url = array_merge( $date_filter, array( 'order_status' => 'all' ) );
            ?>
            <a href="<?php echo ( empty( $all_order_url ) ) ? $orders_url : add_query_arg( $complete_order_url, $orders_url ); ?>">
                <?php printf( __( 'All (%d)', 'dokan' ), $orders_counts->total ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-completed' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $complete_order_url = array_merge( array( 'order_status' => 'wc-completed' ), $date_filter );
            ?>
            <a href="<?php echo add_query_arg( $complete_order_url, $orders_url ); ?>">
                <?php printf( __( 'Completed (%d)', 'dokan' ), $orders_counts->{'wc-completed'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-processing' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $processing_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-processing' ) );
            ?>
            <a href="<?php echo add_query_arg( $processing_order_url, $orders_url ); ?>">
                <?php printf( __( 'Processing (%d)', 'dokan' ), $orders_counts->{'wc-processing'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-on-hold' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $on_hold_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-on-hold' ) );
            ?>
            <a href="<?php echo add_query_arg( $on_hold_order_url, $orders_url ); ?>">
                <?php printf( __( 'On-hold (%d)', 'dokan' ), $orders_counts->{'wc-on-hold'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-pending' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $pending_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-pending' ) );
            ?>
            <a href="<?php echo add_query_arg( $pending_order_url, $orders_url ); ?>">
                <?php printf( __( 'Pending (%d)', 'dokan' ), $orders_counts->{'wc-pending'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-canceled' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $canceled_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-cancelled' ) );
            ?>
            <a href="<?php echo add_query_arg( $canceled_order_url, $orders_url ); ?>">
                <?php printf( __( 'Cancelled (%d)', 'dokan' ), $orders_counts->{'wc-cancelled'} ); ?></span>
            </a>
        </li>

        <li<?php echo $status_class == 'wc-refunded' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $refund_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-refunded' ) );
            ?>
            <a href="<?php echo add_query_arg( $refund_order_url, $orders_url ); ?>">
                <?php printf( __( 'Refunded (%d)', 'dokan' ), $orders_counts->{'wc-refunded'} ); ?></span>
            </a>
        </li>
        <li<?php echo $status_class == 'wc-failed' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $failed_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-failed' ) );
            ?>
            <a href="<?php echo add_query_arg( $failed_order_url, $orders_url ); ?>">
                <?php printf( __( 'Falhado (%d)', 'dokan' ), $orders_counts->{'wc-failed'} ); ?></span>
            </a>
        </li>




        <li<?php echo $status_class == 'wc-pagamento-ok' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $pagamento_confirmado_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-pagamento-ok' ) );
            ?>
            <a href="<?php echo add_query_arg( $pagamento_confirmado_order_url, $orders_url ); ?>">
                <?php printf( __( 'Pgto Confirmado (%d)', 'dokan' ), $orders_counts->{'wc-pagamento-ok'} ); ?></span>
            </a>
        </li>

        <li<?php echo $status_class == 'wc-saiu-para-entrega' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $saiu_para_entrega_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-saiu-para-entrega' ) );
            ?>
            <a href="<?php echo add_query_arg( $saiu_para_entrega_order_url, $orders_url ); ?>">
                <?php printf( __( 'Saiu para Entrega (%d)', 'dokan' ), $orders_counts->{'wc-saiu-para-entrega'} ); ?></span>
            </a>
        </li>

        <li<?php echo $status_class == 'wc-saiu-para-coleta' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $saiu_para_coleta_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-saiu-para-coleta' ) );
            ?>
            <a href="<?php echo add_query_arg( $saiu_para_coleta_order_url, $orders_url ); ?>">
                <?php printf( __( 'Saiu Para Coleta (%d)', 'dokan' ), $orders_counts->{'wc-saiu-para-coleta'} ); ?></span>
            </a>
        </li>

        <li<?php echo $status_class == 'wc-lavando-pecas' ? ' class="active"' : ''; ?>>
            <?php
                if( $order_date ) {
                    $date_filter = array(
                        'order_date' => $order_date,
                        'dokan_order_filter' => 'Filter',
                    );
                }
                $lavando_pecas_order_url = array_merge( $date_filter, array( 'order_status' => 'wc-lavando-pecas' ) );
            ?>
            <a href="<?php echo add_query_arg( $lavando_pecas_order_url, $orders_url ); ?>">
                <?php printf( __( 'Lavando Peças (%d)', 'dokan' ), $orders_counts->{'wc-lavando-pecas'} ); ?></span>
            </a>
        </li>



        <?php do_action( 'dokan_status_listing_item', $orders_counts ); ?>
    </ul>
    <?php
}

function dokan_nav_sort_by_pos( $a, $b ) {
    if ( isset( $a['pos'] ) && isset( $b['pos'] ) ) {
        return $a['pos'] - $b['pos'];
    } else {
        return 199;
    }
}

/**
 * Dashboard Navigation menus
 *
 * @return array
 */
function dokan_get_dashboard_nav() {

    $urls = array(
        'dashboard' => array(
            'title' => __( 'Dashboard', 'dokan'),
            'icon'  => '<i class="fa fa-tachometer"></i>',
            'url'   => dokan_get_navigation_url(),
            'pos'   => 10
        ),
        'products' => array(
            'title' => __( 'Products', 'dokan'),
            'icon'  => '<i class="fa fa-briefcase"></i>',
            'url'   => dokan_get_navigation_url( 'products' ),
            'pos'   => 30
        ),
        'orders' => array(
            'title' => __( 'Orders', 'dokan'),
            'icon'  => '<i class="fa fa-shopping-cart"></i>',
            'url'   => dokan_get_navigation_url( 'orders' ),
            'pos'   => 50
        ),

        'withdraw' => array(
            'title' => __( 'Withdraw', 'dokan'),
            'icon'  => '<i class="fa fa-upload"></i>',
            'url'   => dokan_get_navigation_url( 'withdraw' ),
            'pos'   => 70
        ),
    );

    $settings = array(
        'title' => __( 'Settings <i class="fa fa-angle-right pull-right"></i>', 'dokan'),
        'icon'  => '<i class="fa fa-cog"></i>',
        'url'   => dokan_get_navigation_url( 'settings/store' ),
        'pos'   => 200,
    );

    $settings_sub = array(
        'back' => array(
            'title' => __( 'Back to Dashboard', 'dokan'),
            'icon'  => '<i class="fa fa-long-arrow-left"></i>',
            'url'   => dokan_get_navigation_url(),
            'pos'   => 10
        ),
        'store' => array(
            'title' => __( 'Store', 'dokan'),
            'icon'  => '<i class="icon icon-wh-custom-washing-machine"></i>',
            'url'   => dokan_get_navigation_url( 'settings/store' ),
            'pos'   => 30
        ),
        'payment' => array(
            'title' => __( 'Payment', 'dokan'),
            'icon'  => '<i class="fa fa-credit-card"></i>',
            'url'   => dokan_get_navigation_url( 'settings/payment' ),
            'pos'   => 50
        )
    );


    /**
     * Filter to get the seller dashboard settings navigation.
     *
     * @since 2.2
     *
     * @param array.
     */
    $settings['sub']  = apply_filters( 'dokan_get_dashboard_settings_nav', $settings_sub );


    uasort( $settings['sub'], 'dokan_nav_sort_by_pos' );

    $urls['settings'] = $settings;

    $nav_urls = apply_filters( 'dokan_get_dashboard_nav', $urls );

    uasort( $nav_urls, 'dokan_nav_sort_by_pos' );

    /**
     * Filter to get the final seller dashboard navigation.
     *
     * @since 2.2
     *
     * @param array $urls.
     */
    return $nav_urls;
}

/**
 * Renders the Dokan dashboard menu
 *
 * For settings menu, the active menu format is `settings/menu_key_name`.
 * The active menu will be splitted at `/` and the `menu_key_name` will be matched
 * with settings sub menu array. If it's a match, the settings menu will be shown
 * only. Otherwise the main navigation menu will be shown.
 *
 * @param  string  $active_menu
 *
 * @return string rendered menu HTML
 */
function dokan_dashboard_nav( $active_menu = '' ) {

    $nav_menu          = dokan_get_dashboard_nav();
    $active_menu_parts = explode( '/', $active_menu );

    //dump( isset( $active_menu_parts[1] ) );
    //dump( $active_menu_parts[0] );
    //dump( array_key_exists( $active_menu_parts[1], $nav_menu['settings']['sub'] ) );
    //dump( $active_menu_parts[1] );
    //dump( $nav_menu['settings']['sub'] );

    //if ( isset( $active_menu_parts[1] ) && $active_menu_parts[0] == 'settings' && array_key_exists( $active_menu_parts[1], $nav_menu['settings']['sub'] ) ) {
    if ( isset( $active_menu_parts[1] ) && $active_menu_parts[0] == 'area-administrativa' && $active_menu_parts[1] == 'settings' ) {
        $urls        = $nav_menu['settings']['sub'];
        $active_menu = $active_menu_parts[1];
    } else {
        $urls = $nav_menu;
    }

    $menu = '<ul class="dokan-dashboard-menu">';

    foreach ($urls as $key => $item) {
        $class = ( $active_menu == $key ) ? 'active ' . $key : $key;
        $menu .= sprintf( '<li class="%s"><a href="%s">%s %s</a></li>', $class, $item['url'], $item['icon'], $item['title'] );
    }

    /*
    $menu .= '<li class="dokan-common-links dokan-clearfix">
            <a title="' . __( 'Visit Store', 'dokan' ) . '" class="tips" data-placement="top" href="' . dokan_get_store_url( get_current_user_id()) .'" target="_self"><i class="fa fa-external-link"></i></a>
            <a title="' . __( 'Edit Account', 'dokan' ) . '" class="tips" data-placement="top" href="' . dokan_get_navigation_url( 'edit-account' ) . '"><i class="fa fa-user"></i></a>
            <a title="' . __( 'Log out', 'dokan' ) . '" class="tips" data-placement="top" href="' . wp_logout_url( site_url() ) . '"><i class="fa fa-power-off"></i></a>
        </li>';
    */

    $current_user = wp_get_current_user();
    $loja = $current_user->user_nicename;
    $url = home_url('/item/'.$loja);
    //dump($url);

    $editar_conta = home_url('/minha-conta/editar-conta/');

    $menu .= '<li class="dokan-common-links dokan-clearfix">
          <a title="' . __( 'Visit Store', 'dokan' ) . '" class="tips" data-placement="top" href="' . $url .'" target="_self"><i class="fa fa-external-link"></i></a>
          <a title="' . __( 'Edit Account', 'dokan' ) . '" class="tips" data-placement="top" href="' . $editar_conta . '"><i class="fa fa-user"></i></a>
          <a title="' . __( 'Log out', 'dokan' ) . '" class="tips" data-placement="top" href="' . wp_logout_url( site_url() ) . '"><i class="fa fa-power-off"></i></a>
      </li>';

    $menu .= '</ul>';

    return $menu;
}


if ( ! function_exists( 'dokan_store_category_menu' ) ) :

/**
 * Store category menu for a store
 *
 * @param  int $seller_id
 * @return void
 */
function dokan_store_category_menu( $seller_id, $title = '' ) { ?>
    <aside class="widget dokan-category-menu">
        <h3 class="widget-title"><?php echo $title; ?></h3>
        <div id="cat-drop-stack">
            <?php
            global $wpdb;

            $categories = get_transient( 'dokan-store-category-'.$seller_id );

            if ( false === $categories ) {
                $sql = "SELECT t.term_id,t.name, tt.parent FROM $wpdb->terms as t
                        LEFT JOIN $wpdb->term_taxonomy as tt on t.term_id = tt.term_id
                        LEFT JOIN $wpdb->term_relationships AS tr on tt.term_taxonomy_id = tr.term_taxonomy_id
                        LEFT JOIN $wpdb->posts AS p on tr.object_id = p.ID
                        WHERE tt.taxonomy = 'product_cat'
                        AND p.post_type = 'product'
                        AND p.post_status = 'publish'
                        AND p.post_author = $seller_id GROUP BY t.term_id";

                $categories = $wpdb->get_results( $sql );
                set_transient( 'dokan-store-category-'.$seller_id , $categories );
            }

            $args = array(
                'taxonomy'      => 'product_cat',
                'selected_cats' => ''
            );

            $walker = new Dokan_Store_Category_Walker( $seller_id );
            echo "<ul>";
            echo call_user_func_array( array(&$walker, 'walk'), array($categories, 0, array()) );
            echo "</ul>";
            ?>
        </div>
    </aside>
<?php
}

endif;

/**
 * Clear transient once a product is saved or deleted
 *
 * @param  int $post_id
 *
 * @return void
 */
function dokan_store_category_delete_transient( $post_id ) {

    $post_tmp = get_post( $post_id );
    $seller_id = $post_tmp->post_author;

    //delete store category transient
    delete_transient( 'dokan-store-category-'.$seller_id );
}

add_action( 'delete_post', 'dokan_store_category_delete_transient' );
add_action( 'save_post', 'dokan_store_category_delete_transient' );



function dokan_seller_reg_form_fields() {
    $postdata = $_POST;
    $role = isset( $postdata['role'] ) ? $postdata['role'] : 'customer';
    $role_style = ( $role == 'customer' ) ? ' style="display:none"' : '';

    dokan_get_template_part( 'global/seller-registration-form', '', array(
        'postdata' => $postdata,
        'role' => $role,
        'role_style' => $role_style
    ) );
}

add_action( 'register_form', 'dokan_seller_reg_form_fields' );

if ( !function_exists( 'dokan_seller_not_enabled_notice' ) ) :

    function dokan_seller_not_enabled_notice() {
        dokan_get_template_part( 'global/seller-warning' );
    }

endif;

if ( !function_exists( 'dokan_header_user_menu' ) ) :

/**
 * User top navigation menu
 *
 * @return void
 */
function dokan_header_user_menu() {
    global $current_user;
    $user_id = $current_user->ID;
    $nav_urls = dokan_get_dashboard_nav();

    dokan_get_template_part( 'global/header-menu', '', array( 'current_user' => $current_user, 'user_id' => $user_id, 'nav_urls' => $nav_urls ) );
}

endif;


add_action( 'template_redirect', 'dokan_myorder_login_check');

/**
 * Redirect My order in Login page without user logged login
 *
 * @since 2.4
 *
 * @return [redirect]
 */
function dokan_myorder_login_check(){
    global $post;

    if ( !$post ) {
        return;
    }

    if ( !isset( $post->ID ) ) {
        return;
    }

    $my_order_page_id = dokan_get_option( 'my_orders', 'dokan_pages' );

    if ( $my_order_page_id == $post->ID ) {
        dokan_redirect_login();
    }
}


 /**
 * Displays the store lists
 *
 * @since 2.4
 *
 * @param  array $atts
 *
 * @return string
 */
function dokan_store_listing( $atts ) {
//    global $post;

    /**
     * Filter return the number of store listing number per page.
     *
     * @since 2.2
     *
     * @param array
     */
    $attr = shortcode_atts( apply_filters( 'dokan_store_listing_per_page', array(
        'per_page' => 10,
        'search'   => 'yes',
        'per_row'  => 3,
        'featured'  => 'no'
    ) ), $atts );
    $paged   = max( 1, get_query_var( 'paged' ) );
    $limit   = $attr['per_page'];
    $offset  = ( $paged - 1 ) * $limit;

    $seller_args = array(
        'number' => $limit,
        'offset' => $offset
    );

    // if search is enabled, perform a search
    if ( 'yes' == $attr['search'] ) {
        $search_term = isset( $_GET['dokan_seller_search'] ) ? sanitize_text_field( $_GET['dokan_seller_search'] ) : '';
        if ( '' != $search_term ) {
            $seller_args['search']         = "*{$search_term}*";
            $seller_args['search_columns'] = array( 'display_name' );

            $seller_args['meta_query'] = array(
                array(
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'yes',
                    'compare' => '='
                )
            );
        }
    }

    if ( $attr['featured'] == 'yes' ) {
        $seller_args['meta_query'][] = array(
                                        'key'     => 'dokan_feature_seller',
                                        'value'   => 'yes',
                                        'compare' => '='
                                    );
    }

    $sellers = dokan_get_sellers( apply_filters( 'dokan_seller_listing_args', $seller_args ) );

    /**
     * Filter for store listing args
     *
     * @since 2.4.9
     */
    $template_args = apply_filters( 'dokan_store_list_args', array(
        'sellers'    => $sellers,
        'limit'      => $limit,
        'offset'     => $offset,
        'paged'      => $paged,
        'image_size' => 'medium',
        'search'     => $attr['search'],
        'per_row'    => $attr['per_row']
    ) );
    ob_start();
    dokan_get_template_part( 'store-lists', false, $template_args );
    $content = ob_get_clean();

    return apply_filters( 'dokan_seller_listing', $content, $attr );
}

add_shortcode( 'dokan-stores', 'dokan_store_listing' );
