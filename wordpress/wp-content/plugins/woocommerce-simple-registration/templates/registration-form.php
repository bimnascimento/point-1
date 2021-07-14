<?php
/**
 * Registration form.
 *
 * @author 	Jeroen Sormani
 * @package 	WooCommerce-Simple-Registration
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;
wp_enqueue_script( 'wc-password-strength-meter' );

?><div class="registration-form woocommerce">

	<?php wc_print_notices(); ?>

	<h2 class="hidden"><?php _e( 'Register', 'woocommerce-simple-registration' ); ?></h2>

	<form method="post" class="register" id="formcadastro">

		<?php do_action( 'woocommerce_register_form_start' ); ?>

		<div class="clear"></div>

		<h4><i class="fa fa-user-circle-o" aria-hidden="true"></i> <?php _e( 'Informações Acesso', 'porto' ); ?><span class="msg-verificacao-email"> * Enviaremos um e-mail para verificação!</span></h4>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>

			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
				<label for="reg_username"><?php _e( 'Username', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
				<input Required placeholder="<?php _e( 'Username', 'woocommerce-simple-registration' ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" value="<?php if ( ! empty( $_POST['username'] ) ) echo esc_attr( $_POST['username'] ); ?>" />
			</p>

		<?php endif; ?>

		<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
			<label for="reg_email"><?php _e( 'Email address', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
			<input Required placeholder="<?php _e( 'E-mail para verificação', 'woocommerce-simple-registration' ); ?>" type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" value="<?php if ( ! empty( $_POST['email'] ) ) echo esc_attr( $_POST['email'] ); ?>" />
		</p>

		<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>

			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-first">
				<label for="reg_password"><?php _e( 'Password', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
				<input Required placeholder="<?php _e( 'Password', 'woocommerce-simple-registration' ); ?>" type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" />
			</p>

			<p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-last">
				<label for="reg_password2"><?php _e( 'Confirme a Senha', 'porto' ); ?> <span class="required">*</span></label>
				<input Required type="password" placeholder="<?php _e( 'Confirme a Senha', 'porto' ); ?>" class="woocommerce-Input woocommerce-Input--text input-text" name="confirm_password" id="reg_password2" />
			</p>

		<?php endif; ?>

		<!-- Spam Trap -->
		<div style="<?php echo ( ( is_rtl() ) ? 'right' : 'left' ); ?>: -999em; position: absolute;"><label for="trap"><?php _e( 'Anti-spam', 'woocommerce-simple-registration' ); ?></label><input type="text" name="email_2" id="trap" tabindex="-1" /></div>

<div class="clear"></div>
		<?php do_action( 'woocommerce_register_form' ); ?>
		<div class="clear"></div>
		<?php do_action( 'register_form' ); ?>
		<div class="clear"></div>

		<p class="woocomerce-FormRow form-row">
			<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
			<input type="submit" class="woocommerce-Button button pt-right btn-criar-conta" name="register" value="<?php esc_attr_e( 'Criar Conta', 'woocommerce-simple-registration' ); ?>" />
		</p>

		<?php do_action( 'woocommerce_register_form_end' ); ?>

	</form>

</div>

<script>
jQuery(document).ready(function($) {

		jQuery('#formcadastro').on('keypress', function(e) {

        if (e.which === 13) {
            e.preventDefault();
            //jQuery('#yith-s').val('');
            return false;
        }

        return true;
    });

	jQuery('#formcadastro').validate();
});
</script>
