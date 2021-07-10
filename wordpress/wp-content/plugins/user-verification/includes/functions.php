<?php
/*
* @Author 		pickplugins
* Copyright: 	pickplugins.com
*/

if ( ! defined('ABSPATH')) exit;  // if direct access 



function user_verification_admin_notices(){

    $html= '';

    $uv_option_update = get_option('uv_option_update');

    if($uv_option_update=='done'):

    else:

        if(isset($_GET['_wpnonce']) && wp_verify_nonce( sanitize_text_field($_GET['_wpnonce']), 'uv_option_update' ) ){

            update_option('uv_option_update','done');

        }

        $html.= '<div class="update-nag">';

        $html.= "We have update plugin, please review <a href='".wp_nonce_url(admin_url('admin.php?page=user-verification'), 'uv_option_update')."'>User Verification - Settings</a>";


        $html.= '</div>';


    endif;




   // if(wp_verify_nonce( $nonce, 'plugin_slug_license' )) {}






    echo $html;
}

add_action('admin_notices', 'user_verification_admin_notices');








 function user_verification_get_pages_list() {
    $array_pages[''] = __('None', UV_TEXTDOMAIN);

    foreach( get_pages() as $page )
        if ( $page->post_title ) $array_pages[$page->ID] = $page->post_title;

    return $array_pages;
}







	
	
	function user_verification_reset_email_templates( ) {
		
		if(current_user_can('manage_options')){
			
			delete_option('uv_email_templates_data');
			
			}
		
		
		}	
	
	
	add_action('wp_ajax_user_verification_reset_email_templates', 'user_verification_reset_email_templates');
	add_action('wp_ajax_nopriv_user_verification_reset_email_templates', 'user_verification_reset_email_templates');
	
	
	
	add_shortcode('user_verification_check', 'uv_filter_check_activation');

	function uv_filter_check_activation() {

	    $html = '<div class="user-verification check">';


		if( isset( $_GET['activation_key'] ) ){
            $activation_key = sanitize_text_field($_GET['activation_key']);



            global $wpdb;
            $table = $wpdb->prefix . "usermeta";

            $meta_data	= $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE meta_value = %s", $activation_key ) );

            if( empty( $meta_data ) ) {

                $html.= '<div class="wrong-key"><i class="fa fa-times" aria-hidden="true"></i> Wrong activation key.</div>';

                //uv_show_box_key_error();
               // return;
                }
            else{
                //echo '<pre>'.var_export($activation_key, true).'</pre>';

                    $user_activation_status = get_user_meta( $meta_data->user_id, 'user_activation_status', true );

                    if( $user_activation_status != 0 ) {

                        $html.= '<div class="expired"><i class="fa fa-calendar-times-o" aria-hidden="true"></i> Your key is expired.</div>';
                        //uv_show_box_key_expired();
                        // return;
                        }
                    else {

                            $user_verification_redirect_verified = get_option('user_verification_redirect_verified');
                            $redirect_page_url = get_permalink($user_verification_redirect_verified);

                        $html.= '<div class="verified"><i class="fa fa-check-square-o" aria-hidden="true"></i> Your account is now verified.</div>';
                        // uv_show_box_finished();
                        update_user_meta( $meta_data->user_id, 'user_activation_status', 1 );


                            if(!empty($user_verification_redirect_verified)):

                                $html.= "<script>
    
                                    jQuery(document).ready(function($){
                                    //console.log('$redirect_page_url');
                                    window.location.href = '$redirect_page_url';
                                        
                                    })
                                    
                                    
                                    </script>";

                            else:

                            endif;

                    }

                }
            }

        elseif (isset( $_GET['uv_action']) && isset($_GET['id'])){

            $uv_action = sanitize_text_field($_GET['uv_action']);
            $user_id = (int) sanitize_text_field($_GET['id']);

            if($uv_action=='resend'):

                $user_activation_key = md5(uniqid('', true) );

                update_user_meta( $user_id, 'user_activation_key', $user_activation_key );

                $user_verification_verification_page = get_option('user_verification_verification_page');
                $verification_page_url = get_permalink($user_verification_verification_page);

                $user_data 	= get_userdata( $user_id );
                $link 		= $verification_page_url.'?activation_key='.$user_activation_key;
                // $message 	= "<h3>Please verify your account by clicking the link below</h3>";
                // $message   .= "<a href='$link' style='padding:10px 25px; background:#16A05C; color:#fff;font-size:17px;text-decoration:none;'>Activate</a>";
                // $headers 	= array('Content-Type: text/html; charset=UTF-8');

                uv_mail(
                    $user_data->user_email,
                    array(
                        'action' 	=> 'email_confirmed',
                        'user_id' 	=> $user_id,
                        'link'		=> $link
                    )
                );


                $html.= '<div class="resend"><i class="fa fa-paper-plane" aria-hidden="true"></i> Activation email sent.</div>';

            endif;

        }


        else{
            $html.= '<i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Sorry! invalid activation key.';

            //return $html;
        }



















        $html.= '</div>';

		return $html;


	}			 

	//add_action( 'wp_footer', 'uv_filter_check_activation', 100 );

	
	
	
	
	function uv_filter_resend_activation_link( ) {
		
		if( isset( $_GET['uv_action'] ) ) $uv_action = $_GET['uv_action'];
		else return;
		
		if( isset( $_GET['id'] ) ) $user_id = $_GET['id'];
		else return;
		
		$user_activation_key = md5(uniqid('', true) );
		
		update_user_meta( $user_id, 'user_activation_key', $user_activation_key );

        $user_verification_verification_page = get_option('user_verification_verification_page');
        $verification_page_url = get_permalink($user_verification_verification_page);

		$user_data 	= get_userdata( $user_id );
		$link 		= $verification_page_url.'?activation_key='.$user_activation_key;
		// $message 	= "<h3>Please verify your account by clicking the link below</h3>";
		// $message   .= "<a href='$link' style='padding:10px 25px; background:#16A05C; color:#fff;font-size:17px;text-decoration:none;'>Activate</a>";
		// $headers 	= array('Content-Type: text/html; charset=UTF-8');
	  
		uv_mail( 
			$user_data->user_email,
			array( 
				'action' 	=> 'email_confirmed',
				'user_id' 	=> $user_id,
				'link'		=> $link
			)
		);
			
			
		// uv_mail( $user_data->user_email, 'Verify Your Account', $message );
		
		uv_show_box_resend_email();
	}

	//add_action( 'wp_footer', 'uv_filter_resend_activation_link', 101 );

	
	
	// Login Check
	add_action( 'authenticate', 'uv_user_authentication', 9999, 3 );
	function uv_user_authentication( $errors, $username, $passwords ) { 
		
		
		
		
		if( isset( $errors->errors['incorrect_password'] ) ) return $errors;
		
		if( ! $username ) return $errors;
		$user = get_user_by( 'email', $username );
		if( empty( $user ) ) $user = get_user_by( 'login', $username );
		if( empty( $user ) ) return $errors;
		
		
		// echo '<pre>';  print_r( $errors ); echo '</pre>';
		// echo '<pre>';  print_r( $user ); echo '</pre>';
		
		$user_activation_status = get_user_meta( $user->ID, 'user_activation_status', true ); 
		
		if( $user_activation_status == 0 && $user->ID != 1 ) {

            $user_verification_verification_page = get_option('user_verification_verification_page');
            $verification_page_url = get_permalink($user_verification_verification_page);


			$resend_link = $verification_page_url.'?uv_action=resend&id='. $user->ID;
			
			$message = apply_Filters(
				'account_lock_message', 
				sprintf(
					'<strong>%s</strong> %s <a href="%s">%s</a>', 
					'Error:', 
					'Verify your email first !',
					$resend_link,
					'Resend verification email'
				), 
				$username
			);
			
            return new \WP_Error('authentication_failed', $message);
		}		
        return $errors;
    }


	
	
	
	
	
	function uv_mail( $email_to_add = '', $args = array() ) {
		
		if( empty( $email_to_add ) ) return false;
		
		$action 	= isset( $args['action'] ) ? $args['action'] : '';
		$user_id 	= isset( $args['user_id'] ) ? $args['user_id'] : 1;
		$link 		= isset( $args['link'] ) ? $args['link'] : '';
		$user_info 	= get_userdata( $user_id );
		
		//update_option( 'uv_check_data', $action );

		if( empty( $action ) ) return false; 
		
		$parametar_vars = array(
			'{site_name}'			=> get_bloginfo('name'),
			'{site_description}' 	=> get_bloginfo('description'),
			'{site_url}' 			=>  get_bloginfo('url'),						
			// '{site_logo_url}'		=> $logo_url,

			'{user_name}' 			=> $user_info->user_login,						  
			'{user_avatar}' 		=> get_avatar( $user_id, 60 ),
							
			'{ac_activaton_url}'	=> $link
		);
		
		
		$uv_email_templates_data = get_option( 'uv_email_templates_data' );
		if(empty($uv_email_templates_data)){
				
			$class_uv_emails = new class_uv_emails();
			$templates_data = $class_uv_emails->uv_email_templates_data();
		
		} else {

			$class_uv_emails = new class_uv_emails();
			$templates_data = $class_uv_emails->uv_email_templates_data();
				
			$templates_data = array_merge($templates_data, $uv_email_templates_data);
		}
		
		
		$message_data = isset( $templates_data[$action] ) ? $templates_data[$action] : '';
		if( empty( $message_data ) ) return false; 
		
		
		$email_to 			= strtr( $message_data['email_to'], $parametar_vars );	
		$email_subject 		= strtr( $message_data['subject'], $parametar_vars );
		$email_body 		= strtr( $message_data['html'], $parametar_vars );
		$email_from 		= strtr( $message_data['email_from'], $parametar_vars );	
		$email_from_name 	= strtr( $message_data['email_from_name'], $parametar_vars );				
		$enable 			= strtr( $message_data['enable'], $parametar_vars );	
		
		
		//$emails = array();
		//$emails[] = $email_to;
		//$emails[] = $email_to_add;
		
		$headers = "";
		$headers .= "From: ".$email_from_name." <".$email_from."> \r\n";
		$headers .= "Bcc: ".$email_to." \r\n";		
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

		$attachments = '';	
		
		$status = wp_mail( $email_to_add, $email_subject, $email_body, $headers, $attachments );
		
		//$status = wp_mail($email_to, $subject, $email_body, $headers, $attachments);
		
		//update_option( 'uv_check_data', $status );
		
		return $status;
	}
	
	
	
	
	
	
	
	
	function uv_show_box_resend_email() {
	
		echo '
		<div class="uv_popup_box_container">
			<div class="uv_popup_box_content">
				<span class="uv_popup_box_close"><i class="fa fa-times-circle-o"></i></span>
				<i class="fa fa-check-square"></i>
				<h3 class="uv_popup_box_data">Activation Email Sent.</h3>
			</div>
		</div>';
	}
	
	function uv_show_box_key_error() {
	
		echo '
		<div class="uv_popup_box_container">
			<div class="uv_popup_box_content">
				<span class="uv_popup_box_close"><i class="fa fa-times-circle-o"></i></span>
				<i class="fa fa-exclamation-triangle"></i>
				<h3 class="uv_popup_box_data">Wrong activation Key !!!.</h3>
			</div>
		</div>';
	}
	
	function uv_show_box_finished() {
	
		echo '
		<div class="uv_popup_box_container">
			<div class="uv_popup_box_content">
				<span class="uv_popup_box_close"><i class="fa fa-times-circle-o"></i></span>
				<i class="fa fa-check-square"></i>
				<h3 class="uv_popup_box_data">Your account is now verified.</h3>
			</div>
		</div>';
	}
	
	function uv_show_box_key_expired() {
	
		echo '
		<div class="uv_popup_box_container">
			<div class="uv_popup_box_content">
				<span class="uv_popup_box_close"><i class="fa fa-times-circle-o"></i></span>
				<i class="fa fa-exclamation-triangle"></i>
				<h3 class="uv_popup_box_data">Your key is Expired !!!</h3>
			</div>
		</div>';
	}
	
	
		