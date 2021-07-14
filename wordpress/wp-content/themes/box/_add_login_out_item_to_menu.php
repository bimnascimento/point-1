<?php
if ( ! defined( 'ABSPATH' ) ) exit;


//Add login/logout link to naviagation menu
function add_login_out_item_to_menu( $items, $args ){
	//change theme location with your them location name
  //if( is_admin() ||  $args->theme_location != 'primary' )
	if( is_admin() )
		return $items;
	$redirect = ( is_home() ) ? false : get_permalink();
	if( is_user_logged_in( ) ){
    //<i class="avatar"><img alt="" src="http://2.gravatar.com/avatar/2470bd8b7808cffdaecb70a7602d23e5?s=24&amp;d=mm&amp;r=g" srcset="http://2.gravatar.com/avatar/2470bd8b7808cffdaecb70a7602d23e5?s=48&amp;d=mm&amp;r=g 2x" class="avatar avatar-24 photo" height="24" width="24"></i>
    global $current_user, $wpdb;
    $current_site_id = (int) $wpdb->blogid;
    $user_sites = get_blogs_of_user($current_user->ID);

		if ( count($user_sites) == 1 ){
      $user_target_site = array_values($user_sites)[0];
      $customer_id = get_current_user_id();
      $nome = get_user_meta( $customer_id, 'first_name', true );
      $sobrenome = get_user_meta( $customer_id, 'last_name', true );
      $nomeCompleto = $nome.' '.$sobrenome;

			/*
			$url_link = home_url('/minha-conta/');
			if ( (int) $user_target_site->userblog_id !== $current_site_id ){
					$url_link = $user_target_site->siteurl.'/';
					dump($url_link);
			}
			*/


      if ( (int) $user_target_site->userblog_id === $current_site_id ){
          $avatar = '<span class="avatar-top"><a href="'.home_url('/minha-conta/').'" title="'.$nomeCompleto.'" class="click-loading">'.get_avatar( $current_user->ID , 40).'</a></span>';
      }
    }
    $link = ' '.$avatar.' <a class="click-loading btn-sair" href="' . wp_logout_url( $redirect ) . '" title="' .  __( 'Sair' ) .'">  <i class="fa fa-sign-out"></i> ' . __( 'Sair' ) . '</a>';
		$items_login = '<li id="log-in-out-link" class="menu-item menu-type-link">'. $link . '</li>';
		$items = $items_login.$items;
		return $items;
  }else{
    return $items;
  }
	//else  $link = '<a class="click-loading" href="' . wp_login_url( $redirect  ) . '" title="' .  __( 'Login' ) .'">' . __( 'Login' ) . '</a>';
  //menu-item menu-item-type-custom menu-item-object-custom  narrow
}
add_filter( 'wp_nav_menu_items', 'add_login_out_item_to_menu', 50, 2 );
?>
