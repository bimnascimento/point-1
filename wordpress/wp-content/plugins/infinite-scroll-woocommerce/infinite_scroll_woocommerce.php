<?php
/**
* Plugin Name: Infinite scroll for woocommerce (Shared by JOJOThemes.com)
* Plugin URI: http://bit.do/ZYMg
* Description: Infinite Scroll Plugin for your woocommerce eshop.
* Version: 1.3
* Text Domain: infinite-scroll-woocommerce
* Author: Leonidas Maroulis
* Author URI: http://www.maroulis.net.gr
**/





class InfiniteScrollWoocommerce {

    public $version = '20150721'; // Latest version release date Year-Month-Day

	public $url = ''; // URL of plugin installation

	public $path = ''; // Path of plugin installation

	public $file = ''; // Path of this file

    public $settings; // Settings object

	

	//settings variables

	public $number_of_products 			= "";

	public $icon 						= "";
	
	public $ajax_method		 			= "";//Prefered ajax method -- Infinite scroll | Load More | Simple 
	
	public $load_more_button_animate 	= "";//checkbox on - off
	
	public $load_more_button_transision = "";//animation type
	
	public $wrapper_result_count		= "";//wrapper for pagination
	
	public $wrapper_breadcrumb  		= "";//wrapper for pagination
	
	public $wrapper_products 			= "";//wrapper for products

	public $wrapper_pagination			= "";//wrapper for pagination

	public $selector_next				= "";//selector next

	public $load_more_button_text		= "";//text of load more button
	
	public $animate_to_top				= "";//animate to top on/off
	
	public $pixels_from_top				= "";//pixels from top number

	public $start_loading_x_from_end	= "";


	

	function __construct() {

        $this->file = __file__;

        $this->path = dirname($this->file) . '/';

        $this->url = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__file__)) . '/';

		

		require_once ($this->path . 'include/php/settings.php');

		$this->settings = new InfiniteWoocommerceScrollSettings($this->file);

		

		$this->number_of_products = get_option('isw_number_of_products', '8');

		

		$preloader= wp_get_attachment_thumb_url(get_option('isw_preloader_icon'))==""?$this->url."include/icons/ajax-loader.gif":wp_get_attachment_thumb_url(get_option('isw_preloader_icon'));

		$this->icon 						= $preloader;
		$this->ajax_method					= get_option('isw_ajax_method');
		
		$this->wrapper_result_count 		= get_option('isw_wrapper_result_count')==""?".woocommerce-result-count":get_option('isw_wrapper_result_count');		
		$this->wrapper_breadcrumb	 		= get_option('isw_wrapper_breadcrumb')==""?".woocommerce-breadcrumb":get_option('isw_wrapper_breadcrumb');

		$this->wrapper_products 			= get_option('isw_products_selector')==""?"ul.products":get_option('isw_products_selector');
		$this->wrapper_pagination 			= get_option('isw_pagination_selector')==""?".pagination, .woo-pagination, .woocommerce-pagination, .emm-paginate, .wp-pagenavi, .pagination-wrapper":get_option('isw_pagination_selector');

		$this->selector_next 				= get_option('isw_next_page_selector')==""?".next":get_option('isw_next_page_selector');		
		$this->load_more_button_animate 	= get_option('isw_load_more_button_animate');		
		$this->load_more_button_transision  = get_option('isw_animation_method_load_more_button');		
		
		$this->animate_to_top				= get_option('isw_animate_to_top');	
		$this->pixels_from_top				= get_option('isw_pixels_from_top')==""?"0":get_option('isw_pixels_from_top');
		
		$this->start_loading_x_from_end		= get_option('isw_start_loading_x_from_end')==""?"0":get_option('isw_start_loading_x_from_end');


		

		add_action('woocommerce_before_shop_loop', array($this, 'before_products'), 3);
		//add_action('woocommerce_after_shop_loop', array($this, 'after_products'), 40);



		// Wrap shop pagination 

		add_action('woocommerce_pagination', array($this, 'before_pagination'), 3);

		add_action('woocommerce_pagination', array($this, 'after_pagination'), 40);

		
		add_action('plugins_loaded', array($this,'configLang'));
		

		// Register frontend scripts and styles

		add_action('wp_enqueue_scripts', array($this,'register_frontend_assets'));
		add_action('wp_enqueue_scripts', array($this, 'load_frontend_assets'));
		add_action('wp_enqueue_scripts', array($this, 'localize_frontend_script_config'));


		
    }

	

	public function version() {

        return $this->version;

    }



	public function register_frontend_assets() {

        // Add frontend assets in footer

        wp_register_script('custom-isw', $this->url . 'include/js/custom.js', array('jquery'), false, true);
		
		wp_register_style('ias-animate-css', $this->url . 'include/css/animate.min.css');

		wp_register_style('ias-frontend-style', $this->url . 'include/css/style.css');

		wp_register_style('ias-frontend-custom-style', $this->url . 'include/css/style.php');

		

    }

	

	public function load_frontend_assets() {

		//load all scripts

        wp_enqueue_script( 'custom-isw' );
		
		wp_enqueue_style( 'ias-animate-css' );

		wp_enqueue_style( 'ias-frontend-style' );

		wp_enqueue_style( 'ias-frontend-custom-style' );

    }

	

	public function localize_frontend_script_config() {

        $handle = 'custom-isw';

        $object_name = 'options_isw';

	    $error = __('There was a problem.Please try again.', "infinite-scroll-woocommerce");
		
		$this->load_more_button_text		= get_option('isw_button_text')==""?__("More", "infinite-scroll-woocommerce"):get_option('isw_button_text');
		

        $l10n = array(
			'error' 						=>	$error,		
			'ajax_method'					=>  $this->ajax_method,
            'number_of_products' 			=>	$this->number_of_products,		
			'wrapper_result_count'	 		=>	$this->wrapper_result_count,			
			'wrapper_breadcrumb'	 		=>	$this->wrapper_breadcrumb,
			'wrapper_products'	 			=>	$this->wrapper_products,
			'wrapper_pagination'	 		=>	$this->wrapper_pagination,
			'selector_next'	 				=>	$this->selector_next,
			'icon' 							=>	$this->icon,
			'load_more_button_text' 		=>	$this->load_more_button_text,
			'load_more_button_animate'		=>  $this->load_more_button_animate,
			'load_more_transition'			=>  $this->load_more_button_transision,
			'animate_to_top'				=>  $this->animate_to_top,
			'pixels_from_top'				=>  $this-> pixels_from_top, 
			'start_loading_x_from_end'		=>  $this-> start_loading_x_from_end,
            'paged' 						=> (get_query_var('paged')) ? get_query_var('paged') : 1
        );

        wp_localize_script($handle, $object_name, $l10n);

    }

		

	

	public function before_products() {

		if ($this->ajax_method!='method_simple_ajax_pagination'){
			//remove Result Count
			remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
		}
        //echo '<div class="isw-shop-loop-wrapper">';

    }



    public function after_products() {

        $html = '</div>';

		echo $html;

    }

	
	public function configLang(){
		$lang_dir = basename(dirname(__FILE__)). '/languages';
		load_plugin_textdomain( 'infinite-scroll-woocommerce', false, $lang_dir );
	}
	

	public function before_pagination($template_name = '', $template_path = '', $located = '') {

        echo '<div class="isw-shop-pagination-wrapper">';

    }



    public function after_pagination($template_name = '', $template_path = '', $located = '') {

        echo '</div>';

    }

	



	public function set_number_of_product_items_per_page(){

	

		add_filter( 'loop_shop_per_page', create_function( '$cols', "return $this->number_of_products;" ), $this->number_of_products );

		

	}



}





$woocommerce_isw = new InfiniteScrollWoocommerce();



$woocommerce_isw -> set_number_of_product_items_per_page();



 ?>