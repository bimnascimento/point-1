<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>
<?php
$options = get_option( APSL_SETTINGS ); ?>
<?php
$current_url = APSL_Class::curlPageURL(); ?>
<?php
if( is_user_logged_in() ) {
    global $current_user;
    $user_info = "<span class='display-name'>{$current_user->data->display_name}</span>&nbsp;";
    $user_info.= get_avatar( $current_user->ID, 20 );
    
    if( isset( $options['apsl_custom_logout_redirect_options'] ) && $options['apsl_custom_logout_redirect_options'] != '' ) {
        if( $options['apsl_custom_logout_redirect_options'] == 'home' ) {
            $user_logout_url = wp_logout_url( home_url() );
        } 
        else if( $options['apsl_custom_logout_redirect_options'] == 'current_page' ) {
            $user_logout_url = wp_logout_url( $current_url );
        } 
        else if( $options['apsl_custom_logout_redirect_options'] == 'custom_page' ) {
            if( $options['apsl_custom_logout_redirect_link'] != '' ) {
                $logout_page = $options['apsl_custom_logout_redirect_link'];
                $user_logout_url = wp_logout_url( $logout_page );
            } 
            else {
                $user_logout_url = wp_logout_url( $current_url );
            }
        }
    } 
    else {
        $user_logout_url = wp_logout_url( $current_url );
    }
?><div class="user-login avatar-login-home">Bem vindo(a) <b><?php
    echo $user_info; ?></b>&nbsp;|&nbsp;<a href="<?php
    echo $user_logout_url; ?>" title="Logout">Sair</a></div>
	<?php
} 
else {
?>
<?php
    $encoded_url = urlencode( $current_url );
?>

<?php
    $template = isset( $attr['template'] ) ? $attr['template'] : '1'; ?>

<div class='apsl-login-with-login-form-networks template-<?php
    echo $template; ?> clearfix'>
	<div class='apsl-login-form clearfix'>
		<?php echo $this->custom_login_form(); ?>
	</div>
	<div class='apsl-seperator'><hr><span><?php echo _e('OR', APSL_TEXT_DOMAIN ); ?></span></div>
	<?php
?>
<?php
    $theme = isset( $attr['theme'] ) ? $attr['theme'] : $options['apsl_icon_theme']; ?>
<?php
    $login_text = isset( $attr['login_text'] ) ? $attr['login_text'] : ''; ?>

	<?php
    echo do_shortcode( "[apsl-login theme='$theme' login_text='$login_text']" ); ?>
</div>
<?php
} ?>