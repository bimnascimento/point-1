<?php

add_filter( 'dokan_settings_sections', 'dokan_verification_admin_settings' );

function dokan_verification_admin_settings( $sections ) {
    $sections[] = array(
        'id'    => 'dokan_verification',
        'title' => __( 'Seller Verification', 'dokan-verification' ),
    );
    $sections[] = array(
        'id'    => 'dokan_verification_sms_gateways',
        'title' => __( 'Verification SMS Gateways', 'dokan-verification' ),
    );
    return $sections;
}

add_filter( 'dokan_settings_fields', 'dokan_verification_admin_settings_fields' );

function dokan_verification_admin_settings_fields( $settings_fields ) {
    $settings_fields['dokan_verification'] = array(
        'facebook_app_label'    => array(
            'name'  => 'fb_app_label',
            'label' => __( 'Facebook App Settings', 'dokan-verification' ),
            'type'  => "html",
            'desc'  => '<a target="_blank" href="https://developers.facebook.com/apps/">' . __( 'Create an App', 'dokan-verification' ) . '</a> if you don\'t have one and fill App ID and Secret below.',
        ),
        'facebook_app_url'    => array(
            'name'  => 'fb_app_url',
            'label' => __( 'Site Url', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => "<input class='regular-text' type='text' disabled value=" . dokan_get_navigation_url( 'settings/verification' ).'?hauth.done=Facebook'.'>',
        ),
        'facebook_app_id'     => array(
            'name'  => 'fb_app_id',
            'label' => __( 'App Id', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'facebook_app_secret' => array(
            'name'  => 'fb_app_secret',
            'label' => __( 'App Secret', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'twitter_app_label'     => array(
            'name'  => 'twitter_app_label',
            'label' => __( 'Twitter App Settings', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => '<a target="_blank" href="https://apps.twitter.com/">' . __( 'Create an App', 'dokan-verification' ) . '</a> if you don\'t have one and fill Consumer key and Secret below.',
        ),
        'twitter_app_url'     => array(
            'name'  => 'twitter_app_url',
            'label' => __( 'Callback URL', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => "<input class='regular-text' type='text' disabled value=".dokan_get_navigation_url( 'settings/verification' ).'?hauth.done=Twitter'.'>',
        ),
        'twitter_app_id'      => array(
            'name'  => 'twitter_app_id',
            'label' => __( 'Consumer Key', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'twitter_app_secret'  => array(
            'name'  => 'twitter_app_secret',
            'label' => __( 'Consumer Secret', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'google_app_label'      => array(
            'name'  => 'google_app_label',
            'label' => __( 'Google App Settings', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => '<a target="_blank" href="https://console.developers.google.com/project">' . __( 'Create an App', 'dokan-verification' ) . '</a> if you don\'t have one and fill Client ID and Secret below.',
        ),
        'google_app_url'      => array(
            'name'  => 'google_app_url',
            'label' => __( 'Redirect URI', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => "<input class='regular-text' type='text' disabled value=".dokan_get_navigation_url( 'settings/verification' ).'?hauth.done=Google'.'>',
        ),
        'google_app_id'       => array(
            'name'  => 'google_app_id',
            'label' => __( 'Client ID', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'google_app_secret'   => array(
            'name'  => 'google_app_secret',
            'label' => __( 'Client secret', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'linkedin_app_label'    => array(
            'name'  => 'linkedin_app_label',
            'label' => __( 'Linkedin App Settings', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => '<a target="_blank" href="https://www.linkedin.com/developer/apps">' . __( 'Create an App', 'dokan-verification' ) . '</a> if you don\'t have one and fill Client ID and Secret below.',
        ),
        'linkedin_app_url'    => array(
            'name'  => 'linkedin_app_url',
            'label' => __( 'Redirect URL', 'dokan-verification' ),
            'type'  => 'html',
            'desc'  => "<input class='regular-text' type='text' disabled value=".dokan_get_navigation_url( 'settings/verification' ).'?hauth.done=LinkedIn'.'>',
        ),
        'linkedin_app_id'     => array(
            'name'  => 'linkedin_app_id',
            'label' => __( 'Client ID', 'dokan-verification' ),
            'type'  => 'text',
        ),
        'linkedin_app_secret' => array(
            'name'  => 'linkedin_app_secret',
            'label' => __( 'Client Secret', 'dokan-verification' ),
            'type'  => 'text',
        ),
    );

     //$settings_fields = array();
        $gateways = array();
        $gateway_obj = WeDevs_SMS_Gateways::instance();
        $registered_gateways = $gateway_obj->get_gateways();


        foreach ($registered_gateways as $gateway => $option) {
            $gateways[$gateway] = $option['label'];
        }

        $settings_fields['dokan_verification_sms_gateways'] = array(
            'sender_name' => array(
                'name' => 'sender_name',
                'label' => __( 'Sender Name', 'dokan-verification' ),
                'default' => 'weDevs Team'
            ),
            'sms_text' => array(
                'name' => 'sms_text',
                'label' => __( 'SMS Text', 'dokan-verification' ),
                'type' => 'textarea',
                'default' => __( 'Your verification code is: %CODE%', 'dokan-verification' ),
                'desc' => __( 'will be displayed in SMS. <strong>%CODE%</strong> will be replaced by verification code', 'dokan-verification' )
            ),
            'sms_sent_msg' => array(
                'name' => 'sms_sent_msg',
                'label' => __( 'SMS Sent Success', 'wedevs' ),
                'default' => __( 'SMS sent. Please enter your verification code', 'wedevs' )
            ),
               'sms_sent_error' => array(
                'name' => 'sms_sent_error',
                'label' => __( 'SMS Sent Error', 'wedevs' ),
                'default' => __( 'Unable to send sms. Contact admin', 'wedevs' )
            ),
            array(
                'name' => 'active_gateway',
                'label' => __( 'Active Gateway', 'dokan-verification' ),
                'type' => 'select',
                'options' => $gateways
            ),
            array(
                'name' => 'nexmo_header',
                'label' => __( 'Nexmo App Settings', 'dokan-verification' ),
                'type' => 'html',
                'desc'  => 'Configure your gateway from <a target="_blank" href="https://www.nexmo.com/">' . __( 'here', 'dokan-verification' ) . '</a> and fill the details below',
            ),
            array(
                'name' => 'nexmo_username',
                'label' => __( 'API Key', 'dokan-verification' )
            ),
            array(
                'name' => 'nexmo_pass',
                'label' => __( 'API Secret', 'dokan-verification' )
            ),
            array(
                'name' => 'twilio_header',
                'label' => __( 'Twilio App Settings', 'dokan-verification' ),
                'type' => 'html',
                'desc'  => 'Configure your gateway from <a target="_blank" href="https://www.twilio.com/">' . __( 'here', 'dokan-verification' ) . '</a>  and fill the details below',
            ),
            array(
                'name' => 'twilio_number',
                'label' => __( 'From Number', 'dokan-verification' )
            ),
            array(
                'name' => 'twilio_username',
                'label' => __( 'Account SID', 'dokan-verification' )
            ),
            array(
                'name' => 'twilio_pass',
                'label' => __( 'Auth Token', 'dokan-verification' )
            ),
        );

    return $settings_fields;
}
