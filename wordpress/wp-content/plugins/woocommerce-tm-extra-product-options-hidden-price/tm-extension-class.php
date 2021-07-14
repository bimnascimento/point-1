<?php

if (!defined('ABSPATH')) die();

if (class_exists('TM_EPO_FIELDS')){
    add_filter( 'woocommerce_get_price', 'TM_Extension_Hidden_Price', 9999,2 );

    function TM_Extension_Hidden_Price($price=0,$product=false){

        if($product){
            $cpf_price_array=TM_EPO()->get_product_tm_epos($product->id);
            if ($cpf_price_array){
                $global_price_array = $cpf_price_array['global'];
                $local_price_array  = $cpf_price_array['local'];
                if ( empty($global_price_array)  ){
                    return $price;
                }
                $add_price=0;
                foreach ( $global_price_array as $priority=>$priorities ) {
                    foreach ( $priorities as $pid=>$field ) {
                        if (isset($field['sections']) && is_array($field['sections'])){
                            foreach ( $field['sections'] as $section_id=>$section ) {
                                if ( isset( $section['elements'] ) ) {
                                    foreach ( $section['elements'] as $elid=>$el ) {                                    
                                        if($el['type']=='textfield_hidden_price'){
                                            $add_price=$add_price+ floatval($el['price']);
                                        }                                    
                                    }
                                    
                                }
                            }
                        }
                    }
                }
                $price= floatval($price)+$add_price;
            }
        }
        

        return $price;
    }


    class TM_EPO_FIELDS_hidden_price extends TM_EPO_FIELDS {

        public function display_field( $element=array(), $args=array() ) {
            return array();
        }

        public function validate() {
            return array('passed'=>true,'message'=>false);
        }
        
    }
}