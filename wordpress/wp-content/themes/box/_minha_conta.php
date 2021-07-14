<?php

add_filter ( 'woocommerce_account_menu_items', '_minha_conta' );
function _minha_conta() {
        $endpoints = array(
    		'orders'          => get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' ),
    		'downloads'       => get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' ),
    		'edit-address'    => get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' ),
    		'payment-methods' => get_option( 'woocommerce_myaccount_payment_methods_endpoint', 'payment-methods' ),
    		'edit-account'    => get_option( 'woocommerce_myaccount_edit_account_endpoint', 'edit-account' ),
    		'customer-logout' => get_option( 'woocommerce_logout_endpoint', 'customer-logout' ),
    	);
    	$items = array(
    		'dashboard'       => __( 'Dashboard', 'woocommerce' ),
    		'orders'          => __( 'Orders', 'woocommerce' ),
    		'downloads'       => __( 'Downloads', 'woocommerce' ),
    		'edit-address'    => __( 'Addresses', 'woocommerce' ),
    		'payment-methods' => __( 'Payment Methods', 'woocommerce' ),
    		'edit-account'    => __( 'Account Details', 'woocommerce' ),
    		'customer-logout' => __( 'Logout', 'woocommerce' ),
    	);
     $myorder = array(
         //'my-custom-endpoint' => __( 'My Stuff', 'woocommerce' ),
         'dashboard' => __( 'Painel', 'woocommerce' ),
         'edit-account'    => __( 'Meus Dados', 'woocommerce' ),
         //'orders' => __( 'Meus Pedidos', 'woocommerce' ),
         'meus-pedidos' => __( 'Meus Pedidos', 'woocommerce' ),
         //'subscriptions' => __( 'Assinaturas', 'woocommerce-subscriptions' ),
         //'downloads' => __( 'Downloads', 'woocommerce' ),
         'edit-address' => __( 'Atualizar Endereços', 'woocommerce' ),
         //'payment-methods' => __( 'Payment Methods', 'woocommerce' ),
         'support-tickets' => __( 'Minhas Reclamações', 'woocommerce' ),
         'customer-logout' => __( 'Sair', 'woocommerce' ),
         //'assinaturas' => __( 'TESTE', 'porto' ),
     );
    return $myorder;
}

?>
