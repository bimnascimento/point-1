<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>
<?php $options = get_option( APSL_SETTINGS ); ?>
<?php $current_url = APSL_Class:: curlPageURL(); ?>
<?php
if (is_user_logged_in()){
	global $current_user;
	$user_info 	= "<span class='display-name'>{$current_user->data->display_name}</span>&nbsp;";
	$user_info  .= get_avatar( $current_user->ID, 20 );

	if(isset($options['apsl_custom_logout_redirect_options']) && $options['apsl_custom_logout_redirect_options'] !=''){
		if($options['apsl_custom_logout_redirect_options'] =='home'){
			$user_logout_url = wp_logout_url( home_url() );
		}else if($options['apsl_custom_logout_redirect_options'] =='current_page'){
			$user_logout_url = wp_logout_url( $current_url );

		}else if( $options['apsl_custom_logout_redirect_options'] == 'custom_page' ){
			if( $options['apsl_custom_logout_redirect_link'] !='' ){
				$logout_page = $options['apsl_custom_logout_redirect_link'];
				$user_logout_url = wp_logout_url($logout_page);
			}else{
				$user_logout_url = wp_logout_url( $current_url );
			}
		}

	}else{
		$user_logout_url = wp_logout_url($current_url);
	}

	?><div class="user-login avatar-login-home">Bem vindo(a) <b><?php echo $user_info; ?></b>&nbsp;|&nbsp;<a href="<?php echo $user_logout_url; ?>" title="Sair">Sair</a></div>
	<?php
}else{
?>
<?php
	//dump($current_url);
	$encoded_url = urlencode($current_url);
?>

<?php if(isset($attr['theme'])){
 $theme = $attr['theme'];
}else{
	$theme = $options['apsl_icon_theme'];
}

//$current_site_id = (int) $wpdb->blogid;

?>

<div class='apsl-login-networks theme-<?php echo $theme; ?> clearfix'>
	<?php if(isset($attr['login_text']) && $attr['login_text']!=''){ ?>
	<span class='apsl-login-new-text'><?php echo $attr['login_text']; ?></span>
	<?php } ?>
	<?php if(isset($_SESSION['apsl_login_error_flag']) && $_SESSION['apsl_login_error_flag'] == '1'){ //if( isset($_REQUEST['error']) || isset($_REQUEST['denied']) ){ ?>
			<div class='apsl-error'>
				<?php if(isset($options['apsl_login_error_message']) && $options['apsl_login_error_message'] !=''){ echo $options['apsl_login_error_message']; }else{ _e('You have Access Denied. Please authorize the app to login.', APSL_TEXT_DOMAIN ); } ?>
			</div>
	<?php
	unset($_SESSION['apsl_login_error_flag']);
	} ?>
	<div class='social-networks'>
		<?php foreach($options['network_ordering'] as $key=>$value): ?>
		<?php	if($options["apsl_{$value}_settings"]["apsl_{$value}_enable"]==='enable'){ ?>
		 <a class="login-social" href="<?php echo home_url('/'); ?>?apsl_login_id=<?php echo $value; ?>_login<?php
                    if ( $encoded_url ) {
                        echo "&state=" . base64_encode( "redirect_to=$encoded_url" );
                    }
                    ?>" title='<?php if(isset($options['apsl_each_link_title_attribute']) && $options['apsl_each_link_title_attribute'] !=''){ echo $options['apsl_each_link_title_attribute']; }else{ _e('Login with', APSL_TEXT_DOMAIN ); } echo ' '.$value; ?>'>
			 	<div class="apsl-icon-block apsl-icon-<?php echo $value; ?> <?php if($value=='buffer'){ echo "buffer"; } ?> clearfix">
					<i class="fa fa-<?php echo $value; ?>"></i>
					<span class="apsl-login-text"><?php if(isset($options['apsl_login_short_text']) && $options['apsl_login_short_text'] !=''){ echo $options['apsl_login_short_text']; }else{ _e('Login', APSL_TEXT_DOMAIN ); } ?></span>
					<span class="apsl-long-login-text"><?php if(isset($options['apsl_login_with_long_text']) && $options['apsl_login_with_long_text'] !=''){ echo $options['apsl_login_with_long_text']; }else{ _e('Login with', APSL_TEXT_DOMAIN ); } ?><?php echo ' '.$value; ?></span>
				</div>
		 </a>
			<?php } ?>
		<?php endforeach; ?>
 	</div>
</div>
<?php } ?>
