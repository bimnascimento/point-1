<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Miscellaneous variables for descriptions
$volumetric_operand = get_option( $this->get_instance_option_key(), null )[ 'volumetric_operand' ];
$operand = ( isset( $volumetric_operand ) && $volumetric_operand == 'multiply' ) ? 'x' : '/';
$roles = ( is_admin() ) ? get_editable_roles() : array();
$user_roles = array();
foreach( $roles as $key => $role )
	$user_roles[ $key ] = $role[ 'name' ];
$user_roles['guest'] = "Users Not Logged In";
$blogusers = get_users( 'blog_id=1&orderby=nicename' );
$users = array();
foreach( $blogusers as $key => $user )
	$users[ $key ] = $user->user_nicename;

/**
 * Settings for table rate shipping.
 */
$settings['general'] = array(
	'title'		=> __( 'General Settings', 'be-table-ship' ),
	'settings'	=> array(
		'title' => array(
			'title' 		=> __( 'Method Title', 'woocommerce' ),
			'type' 			=> 'text',
			'description' 	=> __( 'For your reference', 'be-table-ship' ),
			'default' 		=> __( 'Table Rate Shipping', 'be-table-ship' ),
			'css'			=> 'min-width:350px;',
			),
		'tax_status' => array(
			'title' 		=> __( 'Tax Status', 'woocommerce' ),
			'type' 			=> 'select',
			'class'         => 'wc-enhanced-select',
			'default' 		=> 'taxable',
			'options'		=> array(
				'taxable' 	=> __( 'Taxable', 'woocommerce' ),
				'none' 		=> _x( 'None', 'Tax status', 'woocommerce' )
			)
		),
		'condition' => array(
			'title' 		=> __( 'Base Table Rules', 'be-table-ship' ),
			'type' 			=> 'select',
			'class'         => 'wc-enhanced-select',
			'default' 		=> 'per-order',
			'options' 		=> apply_filters( 'betrs_conditional_types', array(
				'per-order' 	=> __( 'Per Order', 'be-table-ship' ),
				'per-item' 		=> __( 'Per Item', 'be-table-ship' ),
				'per-line-item' => __( 'Per Line Item', 'be-table-ship' ),
				'per-class' 	=> __( 'Per Class', 'be-table-ship' ),
				) ),
			),
		),
	'priority'	=> 0,
);
$settings['user_permissions'] = array(
	'title'		=> __( 'User Permissions', 'be-table-ship' ),
	'settings'	=> array(
		'user_limitation' => array(
			'title' 		=> __( 'Shipping options appear for', 'be-table-ship' ),
			'type' 			=> 'select',
			'class'         => 'wc-enhanced-select',
			'default' 		=> 'everyone',
			'options' 		=> apply_filters( 'betrs_user_restriction_types', array(
				'everyone' 			=> __( 'Everyone', 'be-table-ship' ),
				'specific-roles'	=> __( 'Specific Roles', 'be-table-ship' ),
				) ),
			),
		'user_limitation_roles' => array(
			'title' 		=> __( 'Ship to roles', 'be-table-ship' ),
			'type' 			=> 'multiselect',
			'class'         => 'wc-enhanced-select',
			'default' 		=> '',
			'options' 		=> $user_roles,
			),
		'user_modification' => array(
			'title' 		=> __( 'Settings can be modified by', 'be-table-ship' ),
			'type' 			=> 'select',
			'class'         => 'wc-enhanced-select',
			'default' 		=> 'admins',
			'options' 		=> apply_filters( 'betrs_user_restriction_types', array(
				'admins' 			=> __( 'Admins Only', 'be-table-ship' ),
				//'specific-users'	=> __( 'Specific Users', 'be-table-ship' ),
				//'specific-roles'	=> __( 'Specific Roles', 'be-table-ship' ),
				) ),
			),
		'user_modification_users' => array(
			'title' 		=> __( 'Users', 'be-table-ship' ),
			'type' 			=> 'multiselect',
			'class'         => 'wc-enhanced-select',
			'default' 		=> '',
			'options' 		=> $users,
			),
		'user_modification_roles' => array(
			'title' 		=> __( 'User Roles', 'be-table-ship' ),
			'type' 			=> 'multiselect',
			'class'         => 'wc-enhanced-select',
			'default' 		=> '',
			'options' 		=> $user_roles,
			),
		),
	'priority'	=> 10,
);
$settings['volumetric'] = array(
	'title'		=> __( 'Volumetric Settings', 'be-table-ship' ),
	'settings'	=> array(
		'volumetric_number' => array(
			'id'			=> 'volumetric_number',
			'title' 		=> __( 'Volumetric Number', 'be-table-ship' ),
			'type' 			=> 'text',
			'description'		=> __( 'Equation', 'be-table-ship' ) . ': ( L x W x H ) <span>' . $operand  . '</span> ' . __( 'Volumetric Number', 'be-table-ship' ),
			'default' 		=> '',
			'css'			=> 'min-width:350px;',
			'class'			=> 'volumetric_number',
		),
		'volumetric_operand' => array(
			'id'			=> 'volumetric_operand',
			'title' 		=> __( 'Operand', 'be-table-ship' ),
			'type' 			=> 'select',
			'default' 		=> 'divide',
			'class'			=> 'wc-enhanced-select operand_selector',
			'css'			=> 'min-width:350px;',
			'options' 		=> array(
				'divide'	=> __( 'Divide', 'be-table-ship' ),
				'multiply' 	=> __( 'Multiply', 'be-table-ship' ),
				)
		),
	),
	'priority'	=> 20,
);
$settings['other'] = array(
	'title'		=> __( 'Additional Options', 'be-table-ship' ),
	'settings'	=> array(
		'includetax' => array(
			'id'			=> 'includetax',
			'title' 		=> __( 'Include Tax', 'be-table-ship' ),
			'type' 			=> 'checkbox',
			'description'	=> __( 'Calculate shipping based on prices AFTER tax', 'be-table-ship' ),
			'default' 		=> 'no',
		),
		'include_coupons' => array(
			'id'			=> 'include_coupons',
			'title' 		=> __( 'Include Coupons', 'be-table-ship' ),
			'type' 			=> 'checkbox',
			'description'	=> __( 'Subtotal is calculated based on cart value after coupons', 'be-table-ship' ),
			'default' 		=> 'no',
		),
		'single_class' => array(
			'id'			=> 'single_class',
			'title' 		=> __( 'Single Class Only', 'be-table-ship' ),
			'type' 			=> 'select',
			'default' 		=> 'disabled',
			'options' 		=> array(
				'disabled'	=> __( 'Disabled', 'be-table-ship' ),
				'priority' 	=> __( 'Highest Priority', 'be-table-ship' ),
				'cost_high' => __( 'Highest Costing Class', 'be-table-ship' ),
				),
			'class'			=> 'per-class-only',
			//'description'	=> __( 'When enabled, only items of the highest priority shipping class will be counted towards the shipping cost', 'be-table-ship' ),
		),
		'round_weight' => array(
			'id'			=> 'round_weight',
			'title' 		=> __( 'Round Weight', 'be-table-ship' ),
			'type' 			=> 'checkbox',
			'description'	=> __( 'Rounds weight value up to the next whole number', 'be-table-ship' ),
			'default' 		=> 'no',
		),
		'hide_method' => array(
			'id'			=> 'hide_method',
			'title' 		=> __( 'Hide This Method', 'be-table-ship' ),
			'type' 			=> 'checkbox',
			'description'	=> __( 'Hide This Shipping Method When the Free Shipping Method is Available', 'be-table-ship' ),
			'default' 		=> 'no',
		),
	),
	'priority'	=> 50,
);

return $settings;
