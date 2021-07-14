<?php
/*
Plugin Name: WooCommerce TM Extra Product Options Hidden Price
Plugin URI: http://epo.themecomplete.com/
Description: addon for WooCommerce TM Extra Product Options
Version: 1.0
Author: themecomplete
Author URI: http://themecomplete.com/
*/
// don't load directly
if (!defined('ABSPATH')) die();

class TM_Extension_Class_Hidden_Price {
    
    var $addon_name = "textfield_hidden_price";

    var $addon_namespace = "Hidden price";

    function __construct() {
        // Register addon hook
        add_action( 'tm_epo_register_addons', array( $this, 'register_addon' ) );
 
        // Register CSS and JS
        add_action( 'tm_epo_register_addons_scripts', array( $this, 'register_css_js' ) );
        
        // Display the addon
        add_action( 'tm_epo_display_addons', array( $this, 'display_addon' ), 10, 4 );
        add_action( 'init', array( $this, 'set_display_addon_class' ), 10 );
    }

    /*
     * Register addon to EPO
     */
    public function register_addon(){
        
        TM_EPO_BUILDER()->register_addon(
            array(
                "namespace" => $this->addon_namespace,

                "name" => $this->addon_name,
                
                "options" => array(
                                "no_frontend_display"   => 1,
                                "name"                  => __( "Hidden price", TM_EPO_TRANSLATION ),
                                "description"           => __( "Increase or decrease the product's regular price", TM_EPO_TRANSLATION ),
                                "width"                 => "w100",
                                "width_display"         => "1/1",
                                "icon"                  => "tcfa-terminal",
                                "is_post"               => "display",
                                "type"                  => "single",
                                "post_name_prefix"      => $this->addon_name,
                                "fee_type"              => false,
                                "subscription_fee_type" => false),   
                
                "settings"=>array(
                    "price",
                    array(
                        "id"        => $this->addon_name."_price_type",
                        "wpmldisable"=>1,
                        "default"   => "",
                        "type"      => "select",
                        "tags"      => array( "id"=>"builder_".$this->addon_name."_price_type", "name"=>"tm_meta[tmfbuilder][".$this->addon_name."_price_type][]" ),
                        "options"   => array(
                            array( "text"=> __( "Fixed amount", TM_EPO_TRANSLATION ), "value"=>"" ),
                            array( "text"=> __( "Percent of the original price", TM_EPO_TRANSLATION ), "value"=>"percent" ),
                        ),
                        "label"     => __( 'Price type', TM_EPO_TRANSLATION )
                    )
                ),

                "tabs_override"=>array("label_options"=>1,"conditional_logic"=>1,"css_settings"=>1)

            )
        );
    }

    /*
     * Load required addon CSS and JavaScript files only when EPO is loaded
     */
    public function register_css_js() {

    }    

    /*
     * Display the addon
     */
    public function display_addon( $element = array(), $args = array(), $internal_args=array(), $namespace="" ) {
        if($this->addon_namespace!==$namespace){
            return;
        }
    }

    public function set_display_addon_class(){
        require('tm-extension-class.php');
    }

}

// Initialize addon class
new TM_Extension_Class_Hidden_Price();


?>