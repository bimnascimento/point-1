<?php
namespace argMC;

class WooCommerceCheckout
{
    private static $options         = array(); 
    private static $defaultOptions  = array();		


    /**
     * Plugin activation
     * @return void	 
     */
    public static function activate()
    {
        self::checkRequirements();
    }


    /**
     * Check plugin requirements
     * @return void	 
     */
    private static function checkRequirements()
    {
        delete_option('arg-mc-admin-error');

        //Detect WooCommerce plugin
        if (!is_plugin_active('woocommerce/woocommerce.php')) {
            //Load the plugin's translated strings
            load_plugin_textdomain('argMC', false, dirname(ARG_MC_BASENAME) . '/languages');

            $error = '<strong>' . sprintf(__('%s %s requires WooCommerce Plugin to be installed and activated.' , 'argMC'), ARG_MC_PLUGIN_NAME, ARG_MC_VERSION) . '</strong> ' . sprintf(__('Please <a href="%1$s" target="_blank">install WooCommerce Plugin</a>.', 'argMC'), 'https://wordpress.org/plugins/woocommerce/');

            update_option('arg-mc-admin-error', $error);	
        }
    }	


    /**
     * Initialize WordPress hooks
     * @return void	 
     */
    public static function initHooks()
    {
        //After setup theme
        add_action('after_setup_theme', array('argMC\WooCommerceCheckout', 'setup'));

        //Init
        add_action('init', array('argMC\WooCommerceCheckout', 'init'));	

        //Admin init
        add_action('admin_init', array('argMC\WooCommerceCheckout', 'adminInit'));

        //Admin notices
        add_action('admin_notices', array('argMC\WooCommerceCheckout', 'adminNotices'));		

        //Admin menu
        add_action('admin_menu', array('argMC\WooCommerceCheckout', 'adminMenu'));

        //Scripts & styles
        add_action('admin_enqueue_scripts', array('argMC\WooCommerceCheckout', 'enqueueScriptAdmin'));		
        add_action('wp_enqueue_scripts', array('argMC\WooCommerceCheckout', 'enqueueScript'));   

        add_action('wp_head', array('argMC\WooCommerceCheckout', 'loadStyle'));

        //WooCommerce
        add_filter('woocommerce_locate_template', array('argMC\WooCommerceCheckout', 'locateTemplate'), 20, 3);

        add_action('woocommerce_checkout_login_form', 'woocommerce_checkout_login_form');			
        add_action('woocommerce_checkout_coupon_form', 'woocommerce_checkout_coupon_form');	

        add_action('woocommerce_order_review', 'woocommerce_order_review');
        add_action('woocommerce_checkout_payment', 'woocommerce_checkout_payment', 20);	

        //Ajax fields validation
        add_action('wp_ajax_validate_fields', array('argMC\WooCommerceCheckout', 'validateFields'));
        add_action('wp_ajax_nopriv_validate_fields', array('argMC\WooCommerceCheckout', 'validateFields'));	

        //Ajax login
        add_action('wp_ajax_login', array('argMC\WooCommerceCheckout', 'login'));
        add_action('wp_ajax_nopriv_login', array('argMC\WooCommerceCheckout', 'login'));

        //Plugins page
        add_filter('plugin_row_meta', array('argMC\WooCommerceCheckout', 'pluginRowMeta'), 10, 2);
        add_filter('plugin_action_links_' . ARG_MC_BASENAME, array('argMC\WooCommerceCheckout', 'actionLinks'));

        //Admin page
        $page = filter_input(INPUT_GET, 'page');
        if (!empty($page) && $page == ARG_MC_MENU_SLUG) {
            add_filter('admin_footer_text', array('argMC\WooCommerceCheckout', 'adminFooter'));		
        }
    }


    /**
     * Plugin setup
     * @return void
     */	
    public static function setup()
    {
        //Avada Theme Settings
        remove_action('woocommerce_before_checkout_form', 'avada_woocommerce_checkout_coupon_form');
        remove_action('woocommerce_checkout_before_customer_details', 'avada_woocommerce_checkout_before_customer_details');		
        remove_action('woocommerce_checkout_after_customer_details', 'avada_woocommerce_checkout_after_customer_details');
        remove_action('woocommerce_checkout_billing', 'avada_woocommerce_checkout_billing', 20);
        remove_action('woocommerce_checkout_shipping', 'avada_woocommerce_checkout_shipping', 20);
		remove_action('woocommerce_checkout_after_order_review', 'avada_woocommerce_checkout_after_order_review', 20);
    }


    /**
     * Init
     * @return void	 
     */	
    public static function init()
    {	
        //Load the plugin's translated strings
        load_plugin_textdomain('argMC', false, dirname(ARG_MC_BASENAME) . '/languages');
        self::initVariables();					
    }


    /**
     * Admin init
     * @return void	 
     */
    public static function adminInit()
    {
        //Check plugin requirements
        self::checkRequirements();
    }


    /**
     * Admin notices
     * @return void	 
     */	
    public static function adminNotices()
    {
        if (get_option('arg-mc-admin-error')) {
            $class      = 'notice notice-error';
            $message    = get_option('arg-mc-admin-error');

            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        }
    }


    /**
     * Admin menu
     * @return void	 
     */ 	
    public static function adminMenu()
    {
        add_submenu_page(
            'woocommerce',
            ARG_MC_PLUGIN_NAME,
            ARG_MC_PLUGIN_NAME,
            'manage_options',
            ARG_MC_MENU_SLUG,
            array('argMC\WooCommerceCheckout', 'adminOptions')
        );		
    }	


    /**
     * Enqueue scripts and styles for the admin
     * @return void
     */
    public static function enqueueScriptAdmin()
    {
        //Color picker styles
        wp_enqueue_style('wp-color-picker');

        //Plugin admin styles
        wp_enqueue_style('arg-mc-styles-admin', ARG_MC_DIR_URL . 'css/styles-admin.css', array(), ARG_MC_VERSION);           

        //Color picker script
        wp_enqueue_script('wp-color-picker');

        //Plugin admin script
        wp_enqueue_script('arg-mc-scripts-admin', ARG_MC_DIR_URL . 'js/scripts-admin.js', array('jquery'), ARG_MC_VERSION, true);
    }


    /**
     * Enqueue scripts and styles for the front end
     * @return void
     */     
    public static function enqueueScript()
    {
        //Check if is checkout page
        if (is_checkout()) {
            //Custom fonts
            wp_enqueue_style('arg-mc-icons', ARG_MC_DIR_URL . 'icons/css/arg-mc-icons.css', array(), ARG_MC_VERSION);

            //jQuery Validation Engine styles
            wp_enqueue_style('arg-mc-jquery-validation-engine-css', ARG_MC_DIR_URL . 'css/validationEngine.jquery.css', array(), 'v2.6.2');

            //Plugin styles
            wp_enqueue_style('arg-mc-styles', ARG_MC_DIR_URL . 'css/styles.css', array(), ARG_MC_VERSION);

            //Tabs layout styles
            if (!empty(self::$options['tabs_layout']) && self::$options['tabs_layout'] == 'tabs-square') {
                wp_enqueue_style('arg-mc-styles-tabs', ARG_MC_DIR_URL . 'css/styles-tabs-square.css', array(), ARG_MC_VERSION);
            } else {
                wp_enqueue_style('arg-mc-styles-tabs', ARG_MC_DIR_URL . 'css/styles-tabs-arrow.css', array(), ARG_MC_VERSION);
            }

            //Woocommerce styles
            if (!empty(self::$options['overwrite_woo_styles'])) {
                wp_enqueue_style('arg-mc-styles-woocommerce', ARG_MC_DIR_URL . 'css/styles-woocommerce.css', array(), ARG_MC_VERSION);
            }

            //jQuery Validation Engine script
            wp_register_script('arg-mc-jquery-validation-engine-en-js', ARG_MC_DIR_URL . 'js/jquery.validationEngine-en.js', array('jquery'), 'v2.6.2', true);

            wp_localize_script('arg-mc-jquery-validation-engine-en-js', 'argmcJsVars', array(
                'errorRequiredText'     => self::$options['error_required_text'],
                'errorRequiredCheckbox' => self::$options['error_required_checkbox'],
                'errorEmail'            => self::$options['error_email']				
            ));		

            wp_enqueue_script('arg-mc-jquery-validation-engine-en');
            wp_enqueue_script('arg-mc-jquery-validation-engine-js', ARG_MC_DIR_URL . 'js/jquery.validationEngine.js', array('jquery', 'arg-mc-jquery-validation-engine-en-js'), 'v2.6.2', true);	

            //Plugin script
            wp_register_script('arg-mc-scripts', ARG_MC_DIR_URL . 'js/scripts.js', array('jquery', 'wc-checkout', 'arg-mc-jquery-validation-engine-js', 'select2'), ARG_MC_VERSION, true);

            wp_localize_script('arg-mc-scripts', 'argmcJsVars', array(
                'ajaxURL'       => admin_url('admin-ajax.php'),
                'loginNonce'    => wp_create_nonce('login-nonce')
            ));
            wp_enqueue_script('arg-mc-scripts');	
        }
    }


    /**
     * Load custom styles
     * @return void	 
     */
    public static function loadStyle()
    {
       //Check if is checkout page
        if (is_checkout()) {
            global $options;

            $options = self::$options;
            include_once(ARG_MC_DIR_PATH . 'inc/style.php');
        }	
    }	


    /**
     * Load WooCommerce checkout form template file.s
     * @param mixed $template required
     * @param mixed $templateName optional
     * @param mixed $templatePath optional
     * @return mixed
     */      
    public static function locateTemplate($template, $templateName, $templatePath)
    {
        if ($templateName == 'checkout/review-order.php' && empty(self::$options['show_product_image'])) {
            return $template;
        }
        
        if (file_exists(ARG_MC_DIR_PATH . 'woocommerce/' . $templateName)) {
            $template = ARG_MC_DIR_PATH . 'woocommerce/' . $templateName;
            return $template;
        }

        return $template;               
    }


    /**
     * Initialize global variables
     * @return void	 
     */   
    private static function initVariables()
    {			
        self::$defaultOptions = array(
            'btn_next_text'             => __('Next', 'argMC'),
            'btn_prev_text'             => __('Previous', 'argMC'),
            'btn_submit_text'			=> __('Place Order', 'argMC'),
            'btn_skip_login_text'		=> __('Skip Login', 'argMC'),
            'error_required_text'		=> __('This field is required', 'argMC'),
            'error_required_checkbox'   => __('You must accept the terms and conditions', 'argMC'),
            'error_email'               => __('Invalid email address', 'argMC'),
            //Important - Do not change steps order
            'steps'                     => array(
                'login' => array(
                    'text'  => __('Login', 'argMC'), 
                    'class' => 'argmc-login-step argmc-skip-validation'
                ),
                'coupon' => array(
                    'text'  => __('Coupon', 'argMC'),
                    'class' => 'argmc-coupon-step argmc-skip-validation'
                ),
                'billing_shipping' => array(
                    'text'  => __('Billing & Shipping', 'argMC'),
                    'class' => 'argmc-billing-shipping-step'
                ),			
                'billing' => array(
                    'text'  => __('Billing', 'argMC'),
                    'class' => 'argmc-billing-step'
                ),
                'shipping' => array(
                    'text'  => __('Shipping', 'argMC'),
                    'class' => 'argmc-shipping-step'
                ),
                'order_payment' => array(
                    'text'  => __('Order & Payment', 'argMC'),
                    'class' => 'argmc-order-payment-step'
                ),					
                'order' => array(
                    'text'  => __('Order', 'argMC'),
                    'class' => 'argmc-order-step'
                ),
                'payment' => array(
                    'text'  => __('Payment', 'argMC'),
                    'class' => 'argmc-payment-step'
                ),
            ),
            'footer_text'                       => __('Far far away, behind the word mountains, far from the countries Vokalia and Consonantia.', 'argMC'),
            'wizard_max_width'                  => '900px',
            'secondary_font'                    => '',
            'secondary_font_weight'             => '600',
            'show_login'                        => 1,
            'show_coupon'                       => 1,
            'show_order'                        => 1,
            'show_additional_information'       => 1,
            'show_product_image'                => 1,
            'merge_billing_shipping'            => 1,
            'merge_order_payment'               => 1,
            'tabs_layout'                       => 'tabs-square',			
            'tabs_template'                     => 'tabs-default',
            'tabs_width'                        => 'tabs-equal-width',
            'wizard_color'                      => '#555',
            'accent_color'                      => '#e23636',
            'border_color'                      => '#d9d9d9',
			'overwrite_wizard_buttons'           => 0, 
			'wizard_text_errors_color'          => '#e23636',
			'wizard_button_text_color'          => '#fff',
			'wizard_button_text_opacity'        => '0.7',  
			'next_button_bkg'                   => '#e23636',
			'prev_button_bkg'                   => '#b4b4b4',
			'place_order_button_bkg'             => '#96c457',
            'tab_text_color'                    => '#bbb',
            'tab_bkg_color'                     => '#eee',
            'tab_border_left_color'             => '#dcdcdc',
            'tab_border_bottom_color'           => '#c9c9c9',
            'number_text_color'                 => '#999',
            'tab_text_color_hover'		        => '#000',
            'tab_bkg_color_hover'               => '#f8f8f8',
            'tab_before_arrow_color'            => '#fff',
            'overwrite_woo_styles'		        => 0,
            'woo_text_color'                    => '#555',
            'woo_label_color'			        => '#4b4b4b',
            'woo_input_border_color'            => '#ddd',
            'woo_input_bkg_color'               => '#f9f9f9',
            'woo_invalid_required_field_border'	=> '#e23636',
            'woo_invalid_required_field_bkg'	=> '#ffefee',
            'woo_validated_field_border'        => "#ddd",
            'woo_button_bkg_color'		        => '',
            'woo_button_bkg_color_login'        => '#444',
            'woo_field_border_radius'           => '2px',
        );

        $options        = get_option('arg-mc-options'); 
        $defaultOptions = self::$defaultOptions;

        if (!empty($options)) {
            //Merge default options array with options array
            self::setOptions($options, $defaultOptions);
            
            if ($defaultOptions !== $options) {           
                update_option('arg-mc-options', $defaultOptions);
            }
        } else { 		
            update_option('arg-mc-options', self::$defaultOptions);
        } 

        //Set options
        self::$options = $defaultOptions;        
    }

    public static function setOptions($options, &$defaultOptions) 
    {
        foreach ($options as $key => $value) {
            if (is_array($value)) {
                self::setOptions($options[$key], $defaultOptions[$key]);
            } else { 
                if (array_key_exists($key, $defaultOptions)) { 
                    $defaultOptions[$key] = $value;
                }
            }
        }
    }    


    /**
     * Admin options
     */ 	
    public static function adminOptions()
    {	
        $data = filter_input_array(INPUT_POST);
        
        //Form submit
        if (!empty($data)) { 
           
            $data = array_map('stripslashes_deep', $data);

            if (!empty($data['reset'])) {
                self::$options = self::$defaultOptions;
            } else {				
                foreach ($data as $fieldName => $fieldValue) {
                    if ($fieldName == 'save' || $fieldName == 'reset') {
                        continue;	
                    }

                    if (!array_key_exists($fieldName, self::$options)) {
                        continue;
                    }
                    
                    if ($fieldName == 'steps') {
                        foreach ($fieldValue as $stepName => $stepValue) {
                            self::$options[$fieldName][$stepName]['text'] = $stepValue['text'];
                        }
                    } else {
                        self::$options[$fieldName] = $fieldValue;
                    }
                }
            }

            self::$options = apply_filters('arg-mc-update-options', self::$options);

            update_option('arg-mc-options', self::$options);				
        }

        //Set options
        $options = self::$options;


        //Admin options
        $selectedTab    = 'general';
        $tab            = filter_input(INPUT_GET, 'tab');
        
        if (!empty($tab) && in_array($tab, array('general', 'steps', 'styles'))) {
            $selectedTab = $tab;
        }
        ?>

        <div class="argmc-wrapper">

            <div class="nav-tab-wrapper argmc-tab-wrapper">
                <a href="?page=<?php echo ARG_MC_MENU_SLUG; ?>&tab=general" class="nav-tab<?php echo $selectedTab == 'general' ? ' nav-tab-active' : ''; ?>"><?php _e('General Settings', 'argMC'); ?></a>
                <a href="?page=<?php echo ARG_MC_MENU_SLUG; ?>&tab=steps" class="nav-tab<?php echo $selectedTab == 'steps' ? ' nav-tab-active' : ''; ?>"><?php _e('Wizard Steps', 'argMC'); ?></a>
                <a href="?page=<?php echo ARG_MC_MENU_SLUG; ?>&tab=styles" class="nav-tab<?php echo $selectedTab == 'styles' ? ' nav-tab-active' : ''; ?>"><?php _e('Wizard Styles', 'argMC'); ?></a>
            </div>

            <form method="post" class="argmc-form">

                <?php
                switch ($selectedTab) {
                    case 'general':
                        ?>
                        <h2 class="argmc-top-heading"><?php _e('General Settings', 'argMC'); ?></h2>
                        <p class="argmc-top-text argmc-text-general-settings"><?php _e('Under the General Settings tab you\'ll find options like: changing buttons text, custom text, wizard width, secondary font family and error messages.', 'argMC'); ?></p>

                        <h3><?php _e('Buttons and Custom Text', 'argMC') ?></h3>

                        <table class="form-table argmc-table-buttons">
                            <tbody>
                                <tr>
                                    <th>
                                        <?php _e('Skip Login Button Text', 'argMC') ?>
                                        <span class="argmc-description"><?php _e('Change the text of login button.', 'argMC'); ?></span>	
                                    </th>
                                    <td><input type="text" name="btn_skip_login_text" value="<?php echo $options['btn_skip_login_text']; ?>" /></td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e('Next Button Text', 'argMC') ?>
                                        <span class="argmc-description"><?php _e('Change the text of next button.', 'argMC'); ?></span>
                                    </th>
                                    <td><input type="text" name="btn_next_text" value="<?php echo $options['btn_next_text']; ?>" /></td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e('Previous Button Text', 'argMC') ?>
                                        <span class="argmc-description"><?php _e('Change the text of the previous button.', 'argMC'); ?></span>	
                                    </th>
                                    <td><input type="text" name="btn_prev_text" value="<?php echo $options['btn_prev_text']; ?>" /></td>
                                </tr>
                                <tr>
                                    <th>
                                        <?php _e('Place Order Button Text', 'argMC') ?>
                                        <span class="argmc-description"><?php _e('Change the text of the place order button.', 'argMC'); ?></span>	
                                    </th>
                                    <td><input type="text" name="btn_submit_text" value="<?php echo $options['btn_submit_text']; ?>" /></td>
                                </tr>									
                                <tr>
                                    <th>
                                        <?php _e('Custom Text', 'argMC') ?>
                                        <span class="argmc-description"><?php _e('Use this option to add an extra text to footer.', 'argMC'); ?></span>
                                    </th>
                                    <td><textarea name="footer_text"><?php echo $options['footer_text']; ?></textarea></td>
                                </tr>
                            </tbody>
                    </table>		

                    <h3><?php _e('Wizard width and secondary font family', 'argMC') ?></h3>

                    <table class="form-table argmc-table-buttons">
                        <tbody>	
                            <tr>
                                <th>
                                    <?php _e('Wizard Maximum Width', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to set the maximum width of the wizard layout. Set input value to <strong>none</strong> if you want the wizard to expand to the entire layout width.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="wizard_max_width" class="input-field" value="<?php echo $options['wizard_max_width']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Wizard Secondary Font Family', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to set the font family for wizard tabs, headings, lables, buttons, payment metods labels (example: \'Poppins\',sans-serif). Leave it empty if your theme has only one font family.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="secondary_font" class="input-field" value="<?php echo $options['secondary_font']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Wizard Secondary Font Weight', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to set the font weight for wizard tabs, headings, buttons, payment metods labels.', 'argMC'); ?></span>
                                </th>
                                <td>
                                    <select name="secondary_font_weight">
                                        <option value="400" <?php selected($options['secondary_font_weight'], '400', true); ?>><?php _e('400', 'argMC') ?></option>
                                        <option value="500" <?php selected($options['secondary_font_weight'], '500', true); ?>><?php _e('500', 'argMC') ?></option>
                                        <option value="600" <?php selected($options['secondary_font_weight'], '600', true); ?>><?php _e('600', 'argMC') ?></option>
                                        <option value="700" <?php selected($options['secondary_font_weight'], '700', true); ?>><?php _e('700', 'argMC') ?></option>
                                        <option value="800" <?php selected($options['secondary_font_weight'], '800', true); ?>><?php _e('800', 'argMC') ?></option>
                                        <option value="900" <?php selected($options['secondary_font_weight'], '900', true); ?>><?php _e('900', 'argMC') ?></option>
                                    </select>										
                                </td>										
                            </tr>									
                        </tbody>
                    </table>

                    <h3><?php _e('Validation Error Messages', 'argMC') ?></h3>

                    <table class="form-table argmc-table-buttons">
                        <tbody>
                            <tr>
                                <th>
                                   <?php _e('Required Field', 'argMC') ?>
                                   <span class="argmc-description"><?php _e('Change the text of the required field error message.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="error_required_text" class="input-field" value="<?php echo $options['error_required_text']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                   <?php _e('Required Checkbox', 'argMC') ?>
                                   <span class="argmc-description"><?php _e('Change the text of the required checkbox error message.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="error_required_checkbox" class="input-field" value="<?php echo $options['error_required_checkbox']; ?>" /></td>
                            </tr>																	
                            <tr>
                                <th>
                                    <?php _e('Invalid Email Address', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the text of the invalid email address error message.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="error_email" class="input-field" value="<?php echo $options['error_email']; ?>" /></td>
                            </tr>									
                        </tbody>
                    </table>

                    <?php
                    break;

            case 'steps':
                    ?>
                    <h2 class="argmc-top-heading"><?php _e('Wizard Steps Options', 'argMC'); ?></h2>
                    <p class="argmc-top-text"><?php _e('These options refer to your checkout steps and all their content can be found here:', 'argMC'); ?></p>

                    <table class="form-table  argmc-table-steps">
                        <thead>
                            <tr>
                                <th><?php _e('Step Name', 'argMC'); ?></th>
                                <th><?php _e('Template', 'argMC'); ?></th>
                                <th><?php _e('Show/Hide Step', 'argMC'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><input type="text" name="steps[login][text]" value="<?php echo $options['steps']['login']['text']; ?>" /></td>
                                <td><input type="text" name="steps[login][template]" readonly value="{login_form}" /></td>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="show-login" class="input-radio-button" type="radio" name="show_login" value="1" <?php checked($options['show_login'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="show-login">
                                            <span class="label-button-text"><?php _e('Show', 'argMC'); ?></span>
                                        </label>

                                        <input id="hide-login" class="input-radio-button" type="radio" name="show_login" value="0" <?php checked($options['show_login'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="hide-login">
                                            <span class="label-button-text"><?php _e('Hide', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="text" name="steps[coupon][text]" value="<?php echo $options['steps']['coupon']['text']; ?>" /></td>
                                <td><input type="text" name="steps[coupon][template]" readonly value="{coupon_form}" /></td>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="show-coupon" class="input-radio-button" type="radio" name="show_coupon" value="1" <?php checked($options['show_coupon'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="show-coupon">
                                             <span class="label-button-text"><?php _e('Show', 'argMC'); ?></span>
                                        </label>

                                        <input id="hide-coupon" class="input-radio-button" type="radio" name="show_coupon" value="0" <?php checked($options['show_coupon'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="hide-coupon">
                                            <span class="label-button-text"><?php _e('Hide', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="text" name="steps[billing][text]" value="<?php echo $options['steps']['billing']['text']; ?>" /></td>
                                <td><input type="text" name="steps[billing][template]" readonly value="{billing_form}" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="steps[shipping][text]" value="<?php echo $options['steps']['shipping']['text']; ?>" /></td>
                                <td><input type="text" name="steps[shipping][template]" readonly value="{shipping_form}" /></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td><input type="text" name="steps[order][text]" value="<?php echo $options['steps']['order']['text']; ?>" /></td>
                                <td><input type="text" name="steps[order][template]" readonly value="{order_details}" /></td>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="show-order" class="input-radio-button" type="radio" name="show_order" value="1" <?php checked($options['show_order'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="show-order">
                                            <span class="label-button-text"><?php _e('Show', 'argMC'); ?></span>
                                        </label>

                                        <input id="hide-order" class="input-radio-button" type="radio" name="show_order" value="0" <?php checked($options['show_order'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="hide-order">
                                            <span class="label-button-text"><?php _e('Hide', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><input type="text" name="steps[payment][text]" value="<?php echo $options['steps']['payment']['text']; ?>" /></td>
                                <td><input type="text" name="steps[payment][template]" readonly value="{payment_details}" /></td>
                                <td></td>
                            </tr>
									
																							
                        </tbody>
                    </table>

                    <table class="form-table combine-tabs-table">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e('Additional Information', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to hide additional information block from <br> the shipping section.</br>', 'argMC'); ?></span>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="show-additional-information" class="input-radio-button" type="radio" name="show_additional_information" value="1" <?php checked($options['show_additional_information'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="show-additional-information">
                                            <span class="label-button-text"><?php _e('Show', 'argMC'); ?></span>
                                        </label>

                                        <input id="hide-additional-information" class="input-radio-button" type="radio" name="show_additional_information" value="0" <?php checked($options['show_additional_information'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="hide-additional-information">
                                            <span class="label-button-text"><?php _e('Hide', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>	
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Show/Hide Product Thumbnail', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to show/hide product thumbnail in the checkout order review table.', 'argMC'); ?></span>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="show-product-image" class="input-radio-button" type="radio" name="show_product_image" value="1" <?php checked($options['show_product_image'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="show-product-image">
                                            <span class="label-button-text"><?php _e('Show', 'argMC'); ?></span>
                                        </label>

                                        <input id="hide-product-image" class="input-radio-button" type="radio" name="show_product_image" value="0" <?php checked($options['show_product_image'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="hide-product-image">
                                            <span class="label-button-text"><?php _e('Hide', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>	
                            </tr>
                            									
                        </tbody>
                    </table>

                    <table class="form-table combine-tabs-table">
                        <tbody>
                            <tr class="first-row">
                                <th>
                                    <?php _e('Combine Billing and Shipping Steps?', 'argMC') ?>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="merge-billing-shipping-yes" class="input-radio-button" type="radio" name="merge_billing_shipping" value="1" <?php checked($options['merge_billing_shipping'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="merge-billing-shipping-yes">
                                           <span class="label-button-text"><?php _e('Yes', 'argMC'); ?></span>
                                        </label>

                                        <input id="merge-billing-shipping-no" class="input-radio-button" type="radio" name="merge_billing_shipping" value="0" <?php checked($options['merge_billing_shipping'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="merge-billing-shipping-no">
                                            <span class="label-button-text"><?php _e('No', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>	
                            </tr>									
                            <tr class="second-row">
                                <td colspan="2">
                                    <div class="combine-tables-step-name"><?php _e('If so, define your new step name:', 'argMC'); ?></div>
                                    <input type="text" name="steps[billing_shipping][text]" value="<?php echo $options['steps']['billing_shipping']['text']; ?>" />
                                    <input type="text" name="steps[billing_shipping][template]" readonly value="{billing_form} {shipping_form}" />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <table class="form-table combine-tabs-table">
                        <tbody>
                            <tr class="firs-row">
                                <th>
                                    <?php _e('Combine Payment and Order Details Steps?', 'argMC') ?>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="merge-order-payment-yes" class="input-radio-button" type="radio" name="merge_order_payment" value="1" <?php checked($options['merge_order_payment'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="merge-order-payment-yes">
                                           <span class="label-button-text"><?php _e('Yes', 'argMC'); ?></span>
                                        </label>

                                        <input id="merge-order-payment-no" class="input-radio-button" type="radio" name="merge_order_payment" value="0" <?php checked($options['merge_order_payment'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="merge-order-payment-no">
                                            <span class="label-button-text"><?php _e('No', 'argMC'); ?></span>
                                        </label>
                                    </div>
                                </td>	
                            </tr>									
                            <tr class="second-row">
                                <td colspan="2">
                                    <div class="combine-tables-step-name"><?php _e('If so, define your new step name:', 'argMC'); ?></div>
                                    <input type="text" name="steps[order_payment][text]" value="<?php echo $options['steps']['order_payment']['text']; ?>" />
                                    <input type="text" name="steps[order_payment][template]" readonly value="{order_details} {payment_details}" />
                                </td>
                            </tr>				
                        </tbody>
                    </table>
                    <?php						
                    break;

            case 'styles':
                    ?>

                    <h2 class="argmc-top-heading"><?php _e('Multistep Checkout Styles', 'argMC') ?></h2>
                    <p class="argmc-top-text"><?php _e('Here you can find the options to change your checkout steps styles:', 'argMC'); ?></p>


                    <h3><?php _e('Wizard styles', 'argMC') ?></h3>

                    <table class="form-table argmc-table-style">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e('Wizard Text Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Change the color of wizard footer custom text.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="wizard_color" class="color-field" value="<?php echo $options['wizard_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Wizard Accent Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the accent color of the wizard.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="accent_color" class="color-field" value="<?php echo $options['accent_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Wizard Border Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the color of the wizard footer border line.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="border_color" class="color-field" value="<?php echo $options['border_color']; ?>" /></td>
                            </tr>
							
							<tr>
                                <th>
                                    <?php _e('Wizard Error Text Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the color of the wizard text error.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="wizard_text_errors_color" class="color-field" value="<?php echo $options['wizard_text_errors_color']; ?>" /></td>
                            </tr>
							
							<tr>
                                <th>
                                    <?php _e('Change Wizard Buttons Styles (skip login, next, previous, place order)', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('By default your theme buttons styles will be applied. Enable this option if you want to change the text/background color of these buttons.', 'argMC'); ?></span>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="overwrite-theme-buttons-yes" class="input-radio-button overwrite-wizard-buttons" data-style="overwrite-buttons" type="radio" name="overwrite_wizard_buttons" value="1" <?php checked($options['overwrite_wizard_buttons'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="overwrite-theme-buttons-yes">
                                          	<span class="label-button-text"><?php _e('On', 'argMC'); ?></span>
                                        </label>

                                        <input id="overwrite-theme-buttons-no" class="input-radio-button overwrite-wizard-buttons" type="radio" data-style="overwrite-buttons-no" name="overwrite_wizard_buttons" value="0" <?php checked($options['overwrite_wizard_buttons'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="overwrite-theme-buttons-no">
                                          	<span class="label-button-text"><?php _e('Off', 'argMC'); ?></span>
                                        </label>																						
                                    </div>			
                                </td>
                            </tr>	

							<tr class="wizard-overwrite-buttons-option">
                                <th>
                                    <?php _e('Wizard Button Text Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the color of the button text. The theme button text color will be inherited if you leave the input empty.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="wizard_button_text_color" class="color-field" value="<?php echo $options['wizard_button_text_color']; ?>" /></td>
                            </tr>
							<tr class="wizard-overwrite-buttons-option">
                                <th>
                                    <?php _e('Wizard Button Text Transparency on Hover', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the opacity of the text button on hover. The opacity-level describes the transparency-level, where 1 is not transparent at all, 0.5 is 50% see-through, and 0 is completely transparent.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="wizard_button_text_opacity" class="input-field" value="<?php echo $options['wizard_button_text_opacity']; ?>" /></td>
                            </tr>
							<tr class="wizard-overwrite-buttons-option">
                                <th>
                                    <?php _e('Wizard Next/Skip Login Buttons Background Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the background color of the next/skip login buttons.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="next_button_bkg" class="color-field" value="<?php echo $options['next_button_bkg']; ?>" /></td>
                            </tr>
							<tr class="wizard-overwrite-buttons-option">
                                <th>
                                    <?php _e('Wizard Previous Button Background Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the background color of the previous button.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="prev_button_bkg" class="color-field" value="<?php echo $options['prev_button_bkg']; ?>" /></td>
                            </tr>
							<tr class="wizard-overwrite-buttons-option">
                                <th>
                                    <?php _e('Wizard Place Order Button Background Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the background color of the place order button.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="place_order_button_bkg" class="color-field" value="<?php echo $options['place_order_button_bkg']; ?>" /></td>
                            </tr>
                        </tbody>
                    </table>


                    <h3><?php _e('Tabs Styles', 'argMC') ?></h3>

                    <table class="form-table argmc-table-style">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e('Tabs Layouts', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the layout of your tabs.', 'argMC') ?></span>
                                </th>
                                <td>
                                    <select name="tabs_layout">
                                        <option value="tabs-square" <?php selected($options['tabs_layout'], 'tabs-square', true); ?>><?php _e('Square', 'argMC') ?></option>
                                        <option value="tabs-arrow" <?php selected($options['tabs_layout'], 'tabs-arrow', true); ?>><?php _e('Arrow', 'argMC') ?></option>
                                    </select>
                                </td>	
                            </tr>								
                            <tr>
                                <th>
                                    <?php _e('Tabs Text Styles', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the text styles of your tabs.', 'argMC') ?></span>
                                </th>
                                <td>
                                    <select name="tabs_template">
                                        <option value="tabs-default" <?php selected($options['tabs_template'], 'tabs-default', true); ?>><?php _e('Text Inline', 'argMC') ?></option>
                                        <option value="tabs-text-under" <?php selected($options['tabs_template'], 'tabs-text-under', true); ?>><?php _e('Text Under Number', 'argMC') ?></option>
                                        <option value="tabs-hide-numbers" <?php selected($options['tabs_template'], 'tabs-hide-numbers', true); ?>><?php _e('Hide Number on Tab', 'argMC') ?></option>
                                    </select>
                                </td>	
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Tabs Width', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to change your tabs width.', 'argMC') ?></span>
                                </th>
                                <td>
                                    <select name="tabs_width">
                                        <option value="tabs-equal-width" <?php selected($options['tabs_width'], 'tabs-equal-width', true); ?>><?php _e('Equals', 'argMC') ?></option>
                                        <option value="tabs-width-auto" <?php selected($options['tabs_width'], 'tabs-width-auto', true); ?>><?php _e('Auto', 'argMC') ?></option>
                                    </select>
                                </td>	
                            </tr>							
                            <tr>
                                <th>
                                    <?php _e('Tab Text Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change tabs font color.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="tab_text_color" class="color-field" value="<?php echo $options['tab_text_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Tab Number Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to change tab number color.', 'argMC') ?></span>																				
                                </th>
                                <td><input type="text" name="number_text_color" class="color-field" value="<?php echo $options['number_text_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Tab Background Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change tabs background color.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="tab_bkg_color" class="color-field" value="<?php echo $options['tab_bkg_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Tab Border Left Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the border color between the tabs.', 'argMC') ?></span>
                                </th>
                                <td><input type="text" name="tab_border_left_color" class="color-field" value="<?php echo $options['tab_border_left_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Tab Border Bottom Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Change the border color under the tabs.', 'argMC') ?></span>										
                                </th>
                                <td><input type="text" name="tab_border_bottom_color" class="color-field" value="<?php echo $options['tab_border_bottom_color']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Current / Completed / On Hover Tab Text Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to change the text color of the completed/current/hovered tab.', 'argMC') ?></span>																																								
                                </th>
                                <td><input type="text" name="tab_text_color_hover" class="color-field" value="<?php echo $options['tab_text_color_hover']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Current / Completed / On Hover Tab Background Color', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to change the background color of the completed/current/hovered tab.', 'argMC') ?></span>																																																		
                                </th>
                                <td><input type="text" name="tab_bkg_color_hover" class="color-field" value="<?php echo $options['tab_bkg_color_hover']; ?>" /></td>
                            </tr>
                            <tr>
                                <th>
                                    <?php _e('Arrow Right Color (option applied only for the "Arrow" Tab Layout)', 'argMC') ?>
                                    <span class="argmc-description"><?php _e('Use this option to change the arrow right color. Usually it is the color of your page background, but you can choose any color.', 'argMC'); ?></span>																																																		
                                </th>
                                <td><input type="text" name="tab_before_arrow_color" class="color-field" value="<?php echo $options['tab_before_arrow_color']; ?>" /></td>
                            </tr>
                        </tbody>
                    </table>


                    <h3><?php _e('Checkout Forms Styles', 'argMC'); ?></h3>

                    <table class="form-table argmc-table-style">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e('Inherit Checkout Forms Styles From Your Theme or Plugin', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('If you prefer the plugin checkout forms styles then select the "Plugin" option (the styles will be inherited from the plugin).
                                    <br>If not, your theme checkout form styles will be inherited.', 'argMC'); ?></span>
                                </th>
                                <td>
                                    <div class="radio-buttons-wrapper">
                                        <input id="overwrite-woo-styles-yes" class="input-radio-button arg-checkout-option-button" data-style="plugin" type="radio" name="overwrite_woo_styles" value="1" <?php checked($options['overwrite_woo_styles'], 1); ?>>
                                        <label class="input-label-button label-button-left" for="overwrite-woo-styles-yes">
                                          	<span class="label-button-text"><?php _e('Plugin', 'argMC'); ?></span>
                                        </label>

                                        <input id="overwrite-woo-styles-no" class="input-radio-button arg-checkout-option-button" type="radio" data-style="theme" name="overwrite_woo_styles" value="0" <?php checked($options['overwrite_woo_styles'], 0); ?>>
                                        <label class="input-label-button label-button-right" for="overwrite-woo-styles-no">
                                          	<span class="label-button-text"><?php _e('Theme', 'argMC'); ?></span>
                                        </label>																						
                                    </div>			
                                </td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Forms Text Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to change forms text color.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_text_color" class="color-field" value="<?php echo $options['woo_text_color']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                   <?php _e('Forms Headings/Table Headings/Labels Color', 'argMC'); ?>
                                   <span class="argmc-description"><?php _e('Change the color of the labels(used on form fields)/form headings/table headings.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_label_color" class="color-field" value="<?php echo $options['woo_label_color']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Form Fields Border Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to change form fields border colors.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_input_border_color" class="color-field" value="<?php echo $options['woo_input_border_color']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Form Fields Background Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to change form fields background colors.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_input_bkg_color" class="color-field" value="<?php echo $options['woo_input_bkg_color']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Form Fields Border Radius', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('With this option you can give any form field "rounded corners".', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_field_border_radius" class="input-field" value="<?php echo $options['woo_field_border_radius']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Invalid Form Fields Border Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Change border color for invalid form fields.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_invalid_required_field_border" class="color-field" value="<?php echo $options['woo_invalid_required_field_border']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Invalid Form Fields Background', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Change background color for invalid form fields.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_invalid_required_field_bkg" class="color-field" value="<?php echo $options['woo_invalid_required_field_bkg']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                            		<?php _e('Validated Form Fields Border', 'argMC'); ?>
                                 	<span class="argmc-description"><?php _e('Change border color for validated form fields.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_validated_field_border" class="color-field" value="<?php echo $options['woo_validated_field_border']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Buttons Background Color', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Change the background color of the wizard buttons. Starting with version 1.8 this option is deprecated. Please use "Wizard styles" options instead to change buttons background colors.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_button_bkg_color" class="color-field" value="<?php echo $options['woo_button_bkg_color']; ?>" /></td>
                            </tr>
                            <tr class="checkout-form-opions">
                                <th>
                                    <?php _e('Button Background Color on Login and Coupon Forms', 'argMC'); ?>
                                    <span class="argmc-description"><?php _e('Use this option to change the background color of Login and Coupon buttons.', 'argMC'); ?></span>
                                </th>
                                <td><input type="text" name="woo_button_bkg_color_login" class="color-field" value="<?php echo $options['woo_button_bkg_color_login']; ?>" /></td>
                            </tr>
                        </tbody>
                    </table>

                    <?php
                    break;
                }			
                ?>
                <input type="submit" name="save" class="button button-primary" value="<?php _e('Save Changes', 'argMC'); ?>">
                <input type="submit" name="reset" class="button" value="<?php _e('Reset All', 'argMC'); ?>">
            </form>
        </div>
        <?php	
    }


    /**
     * Validate fields
     * @return Json	 
     */ 	
    public static function validateFields()
    {
        if (!class_exists('WooCommerce')) {
            $error = sprintf(__('%s %s requires WooCommerce Plugin to be installed and activated.' , 'argMC'), ARG_MC_PLUGIN_NAME, ARG_MC_VERSION);

            echo json_encode(array(
                'success'   => false,
                'error'     => $error
            ));

            exit;		
        }

        $rule 		= filter_input(INPUT_POST, 'rule'); 
        $fieldset_key   = filter_input(INPUT_POST, 'fieldset_key');

        /*
        //Validation rules
        switch ($rule) {
            case 'postcode' :
                $postcodePost   = filter_input(INPUT_POST, 'postcode'); 
                $postcode       = strtoupper(str_replace(' ', '', $postcodePost));
                $country        = filter_input(INPUT_POST, 'country'); 

                if (!\WC_Validation::is_postcode($postcode, $country)) :
                    echo json_encode(array(
                        'success'   => false,
                        'error'     => __('Please enter a valid postcode/ZIP.', 'woocommerce')
                    ));

                    exit;
                endif;
            break;

            case 'phone' :
                $phonePost  = filter_input(INPUT_POST, 'phone'); 
                $phone      = wc_format_phone_number($phonePost);

                if (!\WC_Validation::is_phone($phone)) {
                    echo json_encode(array(
                        'success'   => false,
                        'error'     => '<strong>' . $phonePost . '</strong> ' . __('is not a valid phone number.', 'woocommerce')
                    ));

                    exit;

                }
            break;

            case 'email' :
                $emailPost  = filter_input(INPUT_POST, 'email'); 
                $email      = strtolower($emailPost);

                if (!is_email($email)) {
                    echo json_encode(array(
                       'success'    => false,
                       'error'      => '<strong>' . $emailPost . '</strong> ' . __('is not a valid email address.', 'woocommerce')
                    ));

                    exit;					
                }
            break;

            case 'state' :
                $state      = filter_input(INPUT_POST, 'state'); 
                $country    = filter_input(INPUT_POST, $fieldset_key . '_country'); 
                
                //Get valid states
                $valid_states = \WC()->countries->get_states(isset($country) ? $country : ('billing' === $fieldset_key ? \WC()->customer->get_country() : \WC()->customer->get_shipping_country()));

                if (! empty($valid_states) && is_array($valid_states)) {
                    $valid_state_values = array_flip(array_map('strtolower', $valid_states));

                    //Convert value to key if set
                    if (isset($valid_state_values[strtolower($state)])) {
                        $state = $valid_state_values[strtolower($state)];
                    }
                }

                //Only validate if the country has specific state options
                if (!empty($valid_states) && is_array($valid_states) && sizeof($valid_states) > 0) {
                    if (!in_array($state, array_keys($valid_states))) {
                        echo json_encode(array(
                            'success'   => false,
                            'error'	=> '<strong>' . $state . '</strong> ' . __('is not valid. Please enter one of the following:', 'woocommerce') . ' ' . implode(', ', $valid_states)
                        ));

                        exit;						
                    }
                }
            break;
        }
        */

        echo json_encode(array('success' => true));

        exit;
    }


    /**
     * Login
     * @return Json		 
     */ 		
    public static function login()
    {
        check_ajax_referer('login-nonce', 'security');

        $info                   = array();
        $info['user_login'] 	= filter_input(INPUT_POST, 'username');
        $info['user_password'] 	= filter_input(INPUT_POST, 'password');
        $info['remember']       = true;

        $user = wp_signon($info, false);

        if (is_wp_error($user)) {
            echo json_encode(array(
                'success'   => false,
                'error'     => __('Incorrect username/password', 'argMC')
            ));

            exit;
        } 

        echo json_encode(array(
            'success' => true
        ));		

        exit;
    }	


    /**
     * Plugins page
     * @return array		  
     */ 	
    public static function pluginRowMeta($links, $file)
    {
        if ($file == ARG_MC_BASENAME) {
            unset($links[2]);

            $customLinks = array(
                'documentation'     => '<a href="' . ARG_MC_DOCUMENTATION_URL . '" target="_blank">' . __('Documentation', 'argMC') . '</a>',
                'visit-plugin-site' => '<a href="' . ARG_MC_PLUGIN_URL . '" target="_blank">' . __('Visit plugin site', 'argMC') . '</a>'
            );

            $links = array_merge($links, $customLinks);
        }

        return $links;
    }


    /**
     * Plugins page
     * @return array	 
     */ 
    public static function actionLinks($links)
    {
        $customLinks = array_merge(array('settings' => '<a href="' . admin_url('admin.php?page='. ARG_MC_MENU_SLUG) . '">' . __('Settings', 'argMC') . '</a>'), $links);

        return $customLinks;
    }


    /**
     * Admin footer
     * @return void	 
     */ 		
    public static function adminFooter()
    {
       /* ?>
        <p><a href="https://codecanyon.net/item/arg-multistep-checkout-for-woocommerce/reviews/18036216" class="arg-review-link" target="_blank"><?php echo sprintf(__('If you like <strong> %s </strong> please leave us a ★★★★★ rating.', 'argMC'), ARG_MC_PLUGIN_NAME); ?></a> <?php _e('Thank you.', 'argMC'); ?></p>
        <?php
        */        
    }
}