<?php

class RSGiftVoucher {
    
    public function __construct() {
        
        add_action('init', array($this, 'reward_system_default_settings'),103);// call the init function to update the default settings on page load
        
        add_filter('woocommerce_rs_settings_tabs_array',array($this,'reward_system_tab_setting'));// Register a New Tab in a WooCommerce Reward System Settings        
        
        add_action('woocommerce_rs_settings_tabs_rewardsystem_giftvoucher', array($this, 'reward_system_register_admin_settings'));// Call to register the admin settings in the Reward System Submenu with general Settings tab        
        
        add_action('woocommerce_update_options_rewardsystem_giftvoucher', array($this, 'reward_system_update_settings'));// call the woocommerce_update_options_{slugname} to update the reward system                               
    }
    
    /*
     * Function to Define Name of the Tab
     */
    public static function reward_system_tab_setting($setting_tabs){
        $setting_tabs['rewardsystem_giftvoucher'] = __('Gift Voucher','rewardsystem');
        return $setting_tabs;
    }
    
     /*
     * Function label settings to Member Level Tab
     */
    public static function reward_system_admin_fields() {
        global $woocommerce;
        
        return apply_filters('woocommerce_rewardsystem_gift_voucher_settings', array(
            array(
                'name' => __('Gift Voucher Creation Setting', 'rewardsystem'),
                'type' => 'title',              
                'id' => '_rs_gift_voucher_setting'
            ),           
            array(
                'name' => 'test',
                'type' => 'point_vouchers',
            ),
            array('type' => 'sectionend', 'id' => '_rs_gift_voucher_setting'),
            
           array('type'=>'sectionend', 'id'=>'_rs_gift_voucher_setting'),
        ));     
    }
    
        /**
     * Registering Custom Field Admin Settings of SUMO Reward Points in woocommerce admin fields funtion
     */
    public static function reward_system_register_admin_settings() {
        
        woocommerce_admin_fields(RSGiftVoucher::reward_system_admin_fields());
    }

    /**
     * Update the Settings on Save Changes may happen in SUMO Reward Points
     */
    public static function reward_system_update_settings() {
        woocommerce_update_options(RSGiftVoucher::reward_system_admin_fields());
    }

    /**
     * Initialize the Default Settings by looping this function
     */
    public static function reward_system_default_settings() {
        global $woocommerce;
        foreach (RSGiftVoucher::reward_system_admin_fields() as $setting)
            if (isset($setting['newids']) && isset($setting['std'])) {
                add_option($setting['newids'], $setting['std']);
            }
    }
    
}
new RSGiftVoucher();