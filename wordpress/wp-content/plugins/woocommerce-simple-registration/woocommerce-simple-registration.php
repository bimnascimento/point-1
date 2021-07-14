<?php
/**
 * Plugin Name: Simple Registration for WooCommerce
 * Plugin URI: https://astoundify.com/products/woocommerce-simple-registration/
 * Description: A simple plugin to add a [woocommerce_simple_registration] shortcode to display the registration form on a separate page.
 * Version: 1.4.0
 * Author: Astoundify
 * Author URI: https://astoundify.com/
 * Text Domain: woocommerce-simple-registration
 * Domain Path: /languages
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class WooCommerce_Simple_Registration.
 *
 * Main WooCommerce_Simple_Registration class initializes the plugin.
 *
 * @class		WooCommerce_Simple_Registration
 * @version		1.0.0
 * @author		Jeroen Sormani
 */
class WooCommerce_Simple_Registration {
	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 * @var string $version Plugin version number.
	 */
	public $version = '1.4.0';
	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 * @var string $file Plugin file path.
	 */
	public $file = __FILE__;
	/**
	 * Instace of WooCommerce_Simple_Registration.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var object $instance The instance of WooCommerce_Simple_Registration.
	 */
	private static $instance;
	/**
	 * Construct.
	 *
	 * Initialize the class and plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		// Initialize plugin parts
		$this->init();
		// woocommerce_simple_registration shortcode
		add_shortcode( 'woocommerce_simple_registration', array( $this, 'registration_template' ) );
		// add a body class on this page
		add_filter( 'body_class', array( $this, 'body_class' ) );
		// add first name and last name to register form
		add_action( 'woocommerce_register_form_start', array( $this, 'add_name_input' ) );
		add_action( 'woocommerce_register_form', array( $this, 'add_contato_input' ) );
		add_action( 'woocommerce_created_customer', array( $this, 'save_name_input' ) );
		/**
		 * WooCommerce Social Login Support
		 * @link http://www.woothemes.com/products/woocommerce-social-login/
		 * @since 1.3.0
		 */
		if( function_exists( 'init_woocommerce_social_login' ) ){
			require_once( trailingslashit( plugin_dir_path( __FILE__ ) ) . 'includes/wc-social-login.php' );
		}
	}
	/**
	 * Instance.
	 *
	 * An global instance of the class. Used to retrieve the instance
	 * to use on other files/plugins/themes.
	 *
	 * @since 1.0.0
	 * @return object Instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) )  {
			self::$instance = new self();
		}
		return self::$instance;
	}
	/**
	 * init.
	 *
	 * Initialize plugin parts.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->load_textdomain();
	}
	/**
	 * Textdomain.
	 *
	 * Load the textdomain based on WP language.
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'woocommerce-simple-registration', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	/**
	 * Registartion template.
	 *
	 * Return the registration template contents.
	 *
	 * @return string HTML registration form template.
	 */
	public function registration_template() {
		ob_start();
			if ( ! is_user_logged_in() ) :
				$message = apply_filters( 'woocommerce_registration_message', '' );
				if ( ! empty( $message ) ) :
					wc_add_notice( $message );
				endif;
				wc_get_template( 'registration-form.php', array(), 'woocommerce-simple-registration/', plugin_dir_path( __FILE__ ) . 'templates/' );
			else :
				echo do_shortcode( '[woocommerce_my_account]' );
			endif;
			$return = ob_get_contents();
		ob_end_clean();
		return $return;
	}
	/**
	* Add body classes for WC Simple Register page.
	*
	* @since 1.2.0
	* @param  array $classes
	* @return array
	*/
	public function body_class( $classes ) {
		if( is_singular() && $post_data = get_post( get_queried_object_id() ) ){
			if ( isset( $post_data->post_content ) && has_shortcode( $post_data->post_content, 'woocommerce_simple_registration' ) ) {
				$classes[] = 'woocommerce-register';
				$classes[] = 'woocommerce-account';
				$classes[] = 'woocommerce-page';
			}
		}
		return $classes;
	}
	/**
	 * Add First Name & Last Name
	 * To disable this simply use this code:
	 * `add_filter( 'woocommerce_simple_registration_name_fields', '__return_false' );`
	 * @since 1.3.0
	 */
	public function add_name_input(){
		/* Filter to disable this feature. */
		if( ! apply_filters( 'woocommerce_simple_registration_name_fields', true ) ){
			return;
		}
		?>
		<h4><i class="fa fa-address-card-o" aria-hidden="true"></i> <?php _e( 'Informações Pessoais', 'porto' ); ?></h4>
		<p class="woocommerce-FormRow woocommerce-FormRow--first form-row form-row-first">
			<label for="account_first_name"><?php _e( 'First Name', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
			<input required placeholder="<?php _e( 'First Name', 'woocommerce-simple-registration' ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" value="<?php if ( ! empty( $_POST['account_first_name'] ) ) echo esc_attr( $_POST['account_first_name'] ); ?>" />
		</p>
		<p class="woocommerce-FormRow woocommerce-FormRow--last form-row form-row-last">
			<label for="account_last_name"><?php _e( 'Last Name', 'woocommerce-simple-registration' ); ?> <span class="required">*</span></label>
			<input Required placeholder="<?php _e( 'Last Name', 'woocommerce-simple-registration' ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" value="<?php if ( ! empty( $_POST['account_last_name'] ) ) echo esc_attr( $_POST['account_last_name'] ); ?>" />
		</p>
		<?php
	}
	public function add_contato_input(){
		/* Filter to disable this feature. */
		if( ! apply_filters( 'woocommerce_simple_registration_name_fields', true ) ){
			return;
		}
		?>
		<div class="clear"></div>
		<h4><a href="#account_contato" data-toggle="collapse" class="accordion-toggle"><i class="fa fa-phone" aria-hidden="true"></i> <?php _e( 'Informações Contato', 'porto' ); ?></a> </h4>
		<div  class="clear"></div>
		<div id="account_contato" class="collapse in ">
				<p class="woocommerce-FormRow woocommerce-FormRow--first form-row form-row-first">
					<label for="account_cep"><?php _e( 'CEP', 'woocommerce-simple-registration' ); ?> </label>
					<input Required placeholder="<?php _e( 'CEP', 'woocommerce-simple-registration' ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_cep" id="account_cep" value="<?php if ( ! empty( $_POST['account_cep'] ) ) echo esc_attr( $_POST['account_cep'] ); ?>" />
					<input type="hidden" name="endereco_cep" id="endereco_cep" value="<?php if ( ! empty( $_POST['endereco_cep'] ) ) echo esc_attr( $_POST['endereco_cep'] ); ?>" />
					<input type="hidden" name="bairro_cep" id="bairro_cep" value="<?php if ( ! empty( $_POST['bairro_cep'] ) ) echo esc_attr( $_POST['bairro_cep'] ); ?>" />
					<input type="hidden" name="cidade_cep" id="cidade_cep" value="<?php if ( ! empty( $_POST['cidade_cep'] ) ) echo esc_attr( $_POST['cidade_cep'] ); ?>" />
					<input type="hidden" name="estado_cep" id="estado_cep" value="<?php if ( ! empty( $_POST['estado_cep'] ) ) echo esc_attr( $_POST['estado_cep'] ); ?>" />
				</p>
				<p class="woocommerce-FormRow woocommerce-FormRow--last form-row form-row-last">
					<label for="account_telefone"><?php _e( 'Telefone ou Celular', 'woocommerce-simple-registration' ); ?> </label>
					<input Required placeholder="<?php _e( 'Telefone ou Celular', 'woocommerce-simple-registration' ); ?>" type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_telefone" id="account_telefone" value="<?php if ( ! empty( $_POST['account_telefone'] ) ) echo esc_attr( $_POST['account_telefone'] ); ?>" />
				</p>
		</div>
		<div class="clear"></div>
		<?php
	}
	/**
	 * Save First Name and Last Name
	 * @since 1.3.0
	 * @see WC/includes/wc-user-functions.php line 114
	 */
	public function save_name_input( $customer_id ){
		/* Filter to disable this feature. */
		if( ! apply_filters( 'woocommerce_simple_registration_name_fields', true ) ){
			return;
		}
		/* Strip slash everything */
		$request = stripslashes_deep( $_POST );
		/* Save First Name */
		if ( isset( $request['sr_firstname'] ) && !empty( $request['sr_firstname'] ) ) {
			update_user_meta( $customer_id, 'first_name', sanitize_text_field( $request['sr_firstname'] ) );
		}
		/* Save Last Name */
		if ( isset( $request['sr_lastname'] ) && !empty( $request['sr_lastname'] ) ) {
			update_user_meta( $customer_id, 'last_name', sanitize_text_field( $request['sr_lastname'] ) );
		}
	}
}
/**
 * The main function responsible for returning the WooCommerce_Simple_Registration object.
 *
 * Use this function like you would a global variable, except without needing to declare the global.
 *
 * Example: <?php WooCommerce_Simple_Registration()->method_name(); ?>
 *
 * @since 1.0.0
 *
 * @return object WooCommerce_Simple_Registration class object.
 */
if ( ! function_exists( 'WooCommerce_Simple_Registration' ) ) :
 	function WooCommerce_Simple_Registration() {
		return WooCommerce_Simple_Registration::instance();
	}
endif;
add_action( 'plugins_loaded', 'WooCommerce_Simple_Registration' );
