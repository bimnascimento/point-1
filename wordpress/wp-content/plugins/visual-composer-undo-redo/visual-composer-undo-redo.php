<?php
/*
Plugin Name: Visual Composer Undo/Redo
Plugin URI: http://codecanyon.net/user/ERROPiX/portfolio?ref=ERROPiX
Description: An addon that allow you to Undo or Redo your most recent changes while you are editting your post and pages.
Version: 1.2.2
Author: ERROPiX
Author URI: http://codecanyon.net/user/ERROPiX/portfolio?ref=ERROPiX
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) die( '-1' );

define('VCUR_VERSION', '1.2.2');

// Plugin Main class
class EPX_Visual_Composer_UndoRedo {
	private $url;
	
	function __construct() {
		$this->url = plugin_dir_url( __FILE__, '/' );
		
		add_action( 'wp_ajax_get_editable_content', array($this,'get_editable_content') );
		add_action( 'admin_init', array($this,'admin_init') );
		add_action( 'epxpu_register_plugin', array($this,'epxpu_register_plugin') );
	}
	
	function get_editable_content() {
		global $post;
		header("Content-Type: application/json");
		$editor = vc_frontend_editor();
		$content = stripcslashes(vc_post_param('content'));
		$post = get_post(vc_post_param('post_id'));
		
		if( $post ) {
			status_header(200);
			
			if( !defined( 'VC_LOADING_EDITABLE_CONTENT' ) ) {
				define( 'VC_LOADING_EDITABLE_CONTENT', true );
			}
			remove_filter( 'the_content', 'wpautop' );
			
			// Prepare VC Editable Content
			$post->post_content = $content;
			ob_start();
			$editor->getPageShortcodesByContent( $content );
			vc_include_template( 'editors/partials/vc_welcome_block.tpl.php' );
			$post_content = rawurlencode( apply_filters('the_content', ob_get_clean()) );
			
			// Prepare VC Shortcodes
			$post_shortcodes = rawurlencode(json_encode($editor->post_shortcodes));
			
			$result = array(
				'post_content' => $post_content,
				'post_shortcodes' => $post_shortcodes,
			);
		} else {
			status_header(404);
			$result = array(
				'error' => "post_not_found"
			);
		}
		
		echo json_encode($result);
		die;
	}
	
	function admin_init() {
		if( defined( 'WPB_VC_VERSION' ) ) {
			add_action( 'admin_print_scripts-post.php', array($this,'assets') );
			add_action( 'admin_print_scripts-post-new.php', array($this,'assets') );
			
			add_filter( 'vc_nav_controls', array( &$this, 'createButtons' ), 9 );
			add_filter( 'vc_nav_front_controls', array( &$this, 'createButtons' ), 9 );
		} else {
			add_action( 'admin_notices', array($this,'admin_notices') );
		}
	}
	
	// Plugin button
	function createButtons($buttons) {
		$new_buttons = array();
		$classes = 'vc_icon-btn disabled '.vc_mode();
		$added = false;
		// xdebug($buttons);
		
		foreach( $buttons as $button ) {
			$new_buttons[] = $button;
			if( !$added && $button[0] == 'templates' ) {
				$new_buttons[] = array(
					'vc_undo',
					'<li><a _title="Undo (ctrl+z)" id="vc_undo" class="'. $classes .'"></a></li>'
				);
				$new_buttons[] = array(
					'vc_redo',
					'<li><a _title="Redo (ctrl+z)" id="vc_redo" class="'. $classes .'"></a></li>'
				);
				$added = true;
			}
		}
		return $new_buttons;
	}
	
	// Plugin Update
	function epxpu_register_plugin($instance) {
		$instance->add(__FILE__, 'envato');
	}
	
	// Plugin Assets 
	function assets() {
		wp_enqueue_style ('vcur_admin',       $this->url . 'assets/css/admin.css', null, VCUR_VERSION);
		wp_enqueue_script('vcur_undomanager', $this->url . 'assets/js/undomanager.js', null, VCUR_VERSION);
		wp_enqueue_script('vcur_admin',       $this->url . 'assets/js/admin.js', null, VCUR_VERSION);
	}
	
	// Plugin Dependdencies 
	function admin_notices() {
		global $current_screen;
		if( $current_screen->id == 'plugins' ) {
			$plugin_data = get_plugin_data( __FILE__ );
			echo '<div class="updated"><p>'.sprintf('<strong>%s</strong> requires <strong><a href="http://codecanyon.net/item/visual-composer-page-builder-for-wordpress/242431" target="_blank">Visual Composer</a></strong> plugin to be installed and activated on your site.', $plugin_data['Name']).'</p></div>';
		}
	}
}

new EPX_Visual_Composer_UndoRedo();