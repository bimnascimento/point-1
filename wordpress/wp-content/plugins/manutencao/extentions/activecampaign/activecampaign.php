<?php
//  * Copyright 2014 SEEDPROD LLC (email : john@seedprod.com, twitter : @seedprod)
/**
 *  Add activecampaign section
 */
$seed_cspv4 = get_option('seed_cspv4');
if($seed_cspv4['emaillist'] == 'activecampaign'){
    add_filter('seedredux/options/seed_cspv4/sections', 'seed_cspv4_activecampaign_section');
}

function seed_cspv4_activecampaign_section($sections) {

	global $seed_cspv4;
	//var_dump($seed_cspv4['emaillist']);
    //$sections = array();
    $sections[] = array(
        'title' => __('Active Campaign', 'seedprod'),
        'desc' => __('<p class="description">Configure saving subscribers to Get Response options. Save after you enter your api key to load your list. <a href="http://support.seedprod.com/article/118-collecting-emails-with-activecampaign" target="_blank">Learn More</a></p>', 'seedprod'),
        'icon' => 'el-icon-envelope',
        // Leave this as a blank section, no options just some intro text set above.
        'fields' => array(
                array(
                    'id'        => 'activecampaign_api_url',
                    'type'      => 'text',
                    'title'     => __( "API Url", 'seedprod' ),
                    'subtitle'  => __('Enter your API Url.', 'seedprod'),
                ),
                array(
                    'id'        => 'activecampaign_api_key',
                    'type'      => 'text',
                    'title'     => __( "API Key", 'seedprod' ),
                    'subtitle'  => __('Enter your API Key.', 'seedprod'),
                ),
                array(
                    'id'        => 'activecampaign_listid',
                    'type'      => 'select',
                    'title'     => __( "List", 'seedprod' ),
                    'options'   => cspv4_get_activecampaign_lists()
                ),
                array(
                    'id'        => 'refresh_activecampaign',
                    'type'      => 'checkbox',
                    'title'     => __( "Refresh Active Campaign Lists", 'seedprod' ),
                    'subtitle'  => __('Check and Save changes to have the lists refreshed above.', 'seedprod'),
                ),

        	)
    );

    return $sections;
}



/**
 *  Get List from activecampaign
 */
function cspv4_get_activecampaign_lists($apikey = null){
    if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'seed_cspv4_options'){
    global $seed_cspv4;
    extract($seed_cspv4);
    $o = $seed_cspv4;
        $lists = array();
        if($o['emaillist'] == 'activecampaign' || ( defined('DOING_AJAX') && DOING_AJAX && isset($_GET['action']) && $_GET['action'] == 'seed_cspv4_refresh_list' )){
        $lists = maybe_unserialize(get_transient('seed_cspv4_activecampaign_lists'));
        if(empty($lists)){

            if(!isset($apikey) && isset($activecampaign_api_key)){
                $apikey = $activecampaign_api_key;
            }

            if(isset($activecampaign_api_url)){
                $apiurl = $activecampaign_api_url;
            }

            if(empty($apikey) || empty($apiurl)){
                return array();
            }

            // Make request
            //$api = new seed_cspv4_activecampaign($apikey);
            $url = $apiurl.'/admin/api.php?api_action=list_list&api_key='.$apikey.'&ids=all&api_output=json';

            $response = wp_remote_get( $url );
            if ( is_wp_error( $response ) ) {
                //var_dump($response);
            }
            $response = wp_remote_retrieve_body( $response );
            if ( is_wp_error( $response ) ) {
                //var_dump($response);
            }

            if(isset($_REQUEST['debug']) && $_REQUEST['debug'] == 'true'){
                var_dump($response);
            }



            if (empty($response)){
                $lists['false'] = __("No lists Found", 'seedprod');
                return $lists;
            } else {
                $response = json_decode($response);
                //var_dump($response);
                foreach ($response as $k => $v){
                    if(is_numeric($k)){
                        $lists[$v->id] = $v->name;
                    }
                }
                if(!empty($lists)){
                   set_transient('seed_cspv4_activecampaign_lists',serialize( $lists ),86400);
                }
            }
        }}
        return $lists;
    }

}


/**
 *  Subscribe activecampaign
 */
add_action('seed_cspv4_emaillist_activecampaign', 'seed_cspv4_emaillist_activecampaign_add_subscriber');

function seed_cspv4_emaillist_activecampaign_add_subscriber($args){
    global $seed_cspv4,$seed_cspv4_post_result;
    extract($seed_cspv4);
                require_once( SEED_CSPV4_PLUGIN_PATH.'lib/nameparse.php' );

                // If tracking enabled
                if(!empty($enable_reflink)){
                    seed_cspv4_emaillist_database_add_subscriber();
                }

                $apikey = $activecampaign_api_key;
                $apiurl = $activecampaign_api_url;
                $listId = $activecampaign_listid;
                //var_dump($listId);

                $name = '';
                if(!empty($_REQUEST['name'])){
                    $name = $_REQUEST['name'];
                }
                $email = $_REQUEST['email'];
                $fname = '';
                $lname = '';

                if(!empty($name)){
                    $name = seed_cspv4_parse_name($name);
                    $fname = $name['first'];
                    $lname = $name['last'];
                }

                $fullname = $fname.' '.$lname;

                // Make Request

                $url = $apiurl.'/admin/api.php?api_action=contact_add&api_key='.$apikey.'&email='.urlencode($email).'=all&api_output=json';
                $response = wp_remote_post( $url, array(
                    'method' => 'POST',
                    'timeout' => 45,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array( 'first_name' => $fname, 'last_name' => $lname , 'email' => $email,'p' => array($listId => $listId), 'instantresponders' => array($listId => 1), 'status' => array($listId => 1) ),
                    'cookies' => array()
                    )
                );
                if ( is_wp_error( $response ) ) {
                    return;
                }
                $response = wp_remote_retrieve_body( $response );
                if ( is_wp_error( $response ) ) {
                    return;
                }
                $response = json_decode($response);
                //var_dump($response);

                
                if($response->result_code == '0'){
                    //var_dump( $response);
                    $seed_cspv4_post_result['status'] = '600';
                    $seed_cspv4_post_result['msg'] = $txt_already_subscribed_msg;
                    $seed_cspv4_post_result['msg_class'] = 'alert-danger';
                }else {
                    // if(!empty($enable_reflink)){
                    //     seed_cspv4_emaillist_database_add_subscriber();
                    // }
                    if(empty($seed_cspv4_post_result['status']))
                        $seed_cspv4_post_result['status'] ='200';
                }
}

// Hook into save

add_action('seedredux/options/seed_cspv4/saved',  'seed_csvp4_refresh_activecampaign_lists' );

function seed_csvp4_refresh_activecampaign_lists($value){
    if(!empty($value['refresh_activecampaign']) && $value['refresh_activecampaign'] == '1'){
        //Clear cache
        delete_transient('seed_cspv4_activecampaign_lists');
        cspv4_get_activecampaign_lists();
        // Reset Field
        // Set code field
        global $seed_cspv4_seedreduxConfig;
        $seed_cspv4_seedreduxConfig->SeedReduxFramework->set('refresh_activecampaign', 0);
    }

}
