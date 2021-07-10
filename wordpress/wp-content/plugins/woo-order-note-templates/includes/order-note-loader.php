<?php
/*
* Include css and js files
*/
class wont_gyrix_order_note_manager_load
{
    protected $note_template;
     
	public function wont_gyrixenqueue_styles() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            wp_enqueue_style(
                'wont_templatecss',
                WONT_GYRIXTEMPLATEURL . 'admin/css/templatecss.min.css',
                array(), 
                '1.0.0'
            );
        }
    }

    public function wont_gyrixenqueue_jscript() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            $userId = get_current_user_id();
            $ajaxSend = array(
                'ajaxSave'=> wp_create_nonce('saveGyrixTemplates'. $userId ),
                'ajaxAdd'=> wp_create_nonce('addGyrinotes'. $userId ),
                'ajaxGet'=> wp_create_nonce('getCustomerName'. $userId ),
                );
            wp_register_script(
                    'wont_templatejs',
                    WONT_GYRIXTEMPLATEURL . 'admin/js/template-script.min.js',
                    array(), 
                    '1.0.0' 
                );

            wp_localize_script( 'wont_templatejs', 'gyrixnonce', $ajaxSend );
            wp_enqueue_script('wont_templatejs');
        }
    }

    public function wont_gyrix_admin_notices() {
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            echo "<div class='updated'><p>Please activate woocommerce plugin to use Woo Order Note Templates plugin.</p></div>";
            return;
        }
    }

    // Add submenu to the woocommerce
	public function wont_gyrixcallhooks()
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            add_submenu_page(
                                            'woocommerce', 
                                            'Order Note Template', 
                                            'Order Note Template', 
                                            'edit_shop_orders', 
                                            'wont_gyrix_note_settings',
                                            array($this, 'wont_gyrix_load_template_page')
                                            );
        }
    }
    // Add "Add note" icon to the action column in order note
    function wont_gyrix_order_actions( $add_globalpay_requery_button) 
    {
        if(current_user_can('edit_shop_orders') ) {
            global $woocommerce;

            $add_globalpay_requery_button['note'] = array('url' => '',
                                                          'name'      => "Add Notes",
                                                          'action'    => "shop_order-note"
                                                      );
            return $add_globalpay_requery_button;
        }
    }

    public function wont_gyrix_admin_order_actions_end( $instance ) 
    {
        $note = new wont_gyrix_order_note_view;
        $templates = $note->wont_gyrix_get_note_template();      
        $note->wont_gyrix_order_add_note_on_view();
        $hook = new wont_gyrix_display_popup;
        $hook->wont_gyrix_display_popup($templates);
    }

    public function wont_gyrix_load_template_page() 
    {
        if ( current_user_can( 'edit_shop_orders' ) ) {
            $note = new wont_gyrix_order_note_view;
            $templates = $note->wont_gyrix_get_note_template();
            $note->wont_gyrix_order_add_note_on_view();
            $html = new Wont_gyrix_get_template_html;
            $html->wont_gyrix_header_template();
            if($templates)
                $html->wont_gyrix_show_template($templates);
            else
                $html->wont_gyrix_add_new_template();
            $html->wont_gyrix_footer_template();
        }
    }   
    
}



