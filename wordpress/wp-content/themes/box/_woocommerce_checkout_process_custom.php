<?php
if ( ! defined( 'ABSPATH' ) ) exit;


add_action('woocommerce_checkout_process', 'woocommerce_checkout_process_custom');
function woocommerce_checkout_process_custom($this) {

        //dump($this);
        //dump('woocommerce_checkout_process_custom');
        //exit;

        $cpf = $_POST['billing_cpf'];
        $cpf = str_replace('_','',$cpf);

        $phone = $_POST['billing_phone'];
        $phone = str_replace('_','',$phone);

        $cep = $_POST['billing_postcode'];
        $cep = str_replace('_','',$cep);

        $birthdate = $_POST['billing_birthdate'];
        $birthdate = str_replace('_','',$birthdate);

        $endereco = '';
        if(!strpos($_POST['billing_address_1'],'Localizando')){
            $endereco = $_POST['billing_address_1'];
        }


        if ( isset( $cpf ) && strlen( $cpf )==14 ) {
            update_user_meta( get_current_user_id(), 'billing_cpf', sanitize_text_field( $_POST['billing_cpf'] ) );
        }
        if ( isset( $_POST['billing_rg'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_rg', sanitize_text_field( $_POST['billing_rg'] ) );
        }
        if ( isset( $birthdate )  ) {
            update_user_meta( get_current_user_id(), 'billing_birthdate', sanitize_text_field( $birthdate ) );
        }
        if ( isset( $_POST['billing_sex'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_sex', sanitize_text_field( $_POST['billing_sex'] ) );
        }
        if ( isset( $cep ) && strlen( $cep )==9 ) {
            update_user_meta( get_current_user_id(), 'billing_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
        }
        if ( isset( $_POST['billing_number'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_number', sanitize_text_field( $_POST['billing_number'] ) );
        }
        if ( isset( $endereco ) ) {
            update_user_meta( get_current_user_id(), 'billing_address_1', sanitize_text_field( $_POST['billing_address_1'] ) );
        }
        if ( isset( $_POST['billing_address_2'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_address_2', sanitize_text_field( $_POST['billing_address_2'] ) );
        }
        if ( isset( $_POST['billing_neighborhood'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_neighborhood', sanitize_text_field( $_POST['billing_neighborhood'] ) );
        }
        if ( isset( $phone ) ) {
            update_user_meta( get_current_user_id(), 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
        }
        if ( isset( $_POST['billing_cellphone'] ) ) {
            update_user_meta( get_current_user_id(), 'billing_cellphone', sanitize_text_field( $_POST['billing_cellphone'] ) );
        }


        if( isset( $_POST['pagseguro_payment_method'] ) && $_POST['pagseguro_payment_method']=='credit-card' ) {

                if( isset( $_POST['pagseguro_payment_method'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_payment_method', sanitize_text_field( $_POST['pagseguro_payment_method'] ) );
                if( isset( $_POST['pagseguro_card_holder_name'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_name', sanitize_text_field( $_POST['pagseguro_card_holder_name'] ) );
                if( isset( $_POST['pagseguro_card_holder_number'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_number', sanitize_text_field( $_POST['pagseguro_card_holder_number'] ) );
                if( isset( $_POST['pagseguro_card_holder_expiry'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_expiry', sanitize_text_field( $_POST['pagseguro_card_holder_expiry'] ) );
                if( isset( $_POST['pagseguro_card_holder_cvc'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_cvc', sanitize_text_field( $_POST['pagseguro_card_holder_cvc'] ) );
                if( isset( $_POST['pagseguro_card_installments'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_installments', sanitize_text_field( $_POST['pagseguro_card_installments'] ) );
                if( isset( $_POST['pagseguro_card_holder_cpf'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_cpf', sanitize_text_field( $_POST['pagseguro_card_holder_cpf'] ) );
                if( isset( $_POST['pagseguro_card_holder_birth_date'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_birth_date', sanitize_text_field( $_POST['pagseguro_card_holder_birth_date'] ) );
                if( isset( $_POST['pagseguro_card_holder_phone'] ) )
                    update_user_meta( get_current_user_id(), 'pagseguro_card_holder_phone', sanitize_text_field( $_POST['pagseguro_card_holder_phone'] ) );
        }

}
?>
