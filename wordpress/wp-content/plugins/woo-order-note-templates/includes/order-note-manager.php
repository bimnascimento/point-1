<?php
/*
Hook list
*/
class wont_gyrix_order_note_manager
{
	private $my_plugin_screen_name;
    
    private static $instance;

    static function wont_get_instance()
	{	
		if(current_user_can('edit_shop_orders') ) {      
		    if (!isset(self::$instance))
		    {
		        self::$instance = new self();
		    }
		    return self::$instance;
		}	
	}

	public function __construct() {
		if(current_user_can('edit_shop_orders') )
		{
			$this->wont_init_plugin();
		}

	}

	public function wont_load_files()
	{
		if(current_user_can('edit_shop_orders') ) {
		   	include_once (WONT_GYRIXTEMPLATEPATH.'includes/order-note-loader.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH.'admin/inc/order-note-save.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH.'admin/inc/order-note-view.php');
		   	include_once(WONT_GYRIXTEMPLATEPATH."templates/order-note-popup.php");
		   	include_once(WONT_GYRIXTEMPLATEPATH."templates/order-note-templates.php");
		}
	} 
	public function wont_init_plugin()
	{
		if(current_user_can('edit_shop_orders') )
		{
			$this->wont_load_files();
			$gyrixhook = new wont_gyrix_order_note_manager_load;
			add_action('admin_notices', array($gyrixhook,'wont_gyrix_admin_notices'));
			add_action('admin_menu', array($gyrixhook, 'wont_gyrixcallhooks'));
	    	add_action( 'admin_enqueue_scripts', array($gyrixhook, 'wont_gyrixenqueue_styles' ));
	    	add_action( 'admin_enqueue_scripts', array($gyrixhook, 'wont_gyrixenqueue_jscript' ));
	    	add_filter( 'woocommerce_admin_order_actions',array($gyrixhook, 'wont_gyrix_order_actions'), 10, 3 );
	    	add_action( 'admin_head',array($gyrixhook, 'wont_gyrix_admin_order_actions_end'), 10, 1 );
	    	add_filter ('the_content',  'wpautop');				
	    	$gyrixnote = new wont_GyrixSaveNoteTemplate;
			add_action('wp_ajax_wont_save_templates',array($gyrixnote , 'wont_gyrix_save_templates' ));
			add_action('wp_ajax_wont_gyrix_customer_name',array($gyrixnote , 'wont_gyrix_customer_name' ));
			add_action('wp_ajax_wont_gyrix_add_note',array($gyrixnote , 'wont_gyrix_add_note' ));
		}
	}
	public function wont_gyrix_register_cpt()
	{
		if(current_user_can('edit_shop_orders') ) {
			$labels = array(
		        'name' => "Gyrix Note Template",
		        'singular_name' => "Gyrix Note Template"
		         );

		        $args = array(
		        'labels' => $labels,
		        'show_in_menu' => false,
		        'capability_type'    => 'edit_shop_orders',
		    );  
		    register_post_type( "wont_gyrix_templates", $args );
		}
	}
}
