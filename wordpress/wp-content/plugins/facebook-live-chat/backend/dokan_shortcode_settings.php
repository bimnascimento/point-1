<?php
function ninja_settings_chat_fb_live_shotcode( $atts ) {
     $user_id = get_current_user_id();
     $url = get_user_meta( $user_id,"ninja_chat_live_facebook",true );
     if ( ! $url ) {
        $url = get_option("live_chat_facebook_user");
     }
    $a ='<div class="setting-live-chat dokan-form-horizontal">
    <div class="dokan-form-group">
        <label class="dokan-control-label dokan-w3">Facebook Fan Page URL</label>
        <div class="dokan-text-left dokan-w5">
            <input class="dokan-form-control" required id="ninja-live-chat-url" value="'.$url.'" />
        </div>
    </div>
    <div class="dokan-form-group">
        <label class="dokan-control-label dokan-w3">'.__("Update","live_chat").'</label>
        <div class="dokan-text-left dokan-w5">
            <input type="submit"  class="dokan-btn dokan-btn-danger dokan-btn-theme ninja-update-url-facebook" value="'.__("Update Page FaceBook","live_chat").'">
            <div class="ninja_validate"></div>
        </div>
    </div>
</div>';
    return $a;
}
add_shortcode( 'settings_chat', 'ninja_settings_chat_fb_live_shotcode' );

function ninja_add_settings(){
    echo do_shortcode('[settings_chat]');
}
add_action("dokan_settings_content","ninja_add_settings",99999);
/*
* Ajax save 
*/
add_action( 'wp_footer', 'ninja_live_chat_save_url_javascript' ); 

function ninja_live_chat_save_url_javascript() { ?>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {

        $(".ninja-update-url-facebook").click(function(){
            var data = {
    			'action': 'save_live_chat_facebook',
    			'url': $("#ninja-live-chat-url").val()
    		};
    
    		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
    		jQuery.post("<?php echo  admin_url( 'admin-ajax.php' ); ?>", data, function(response) {
    			if ( response == "done" ) {
    			    $(".ninja_validate").html("<?php echo __("Update successful.","live_chat") ?>");   
    			}else{
                    $(".ninja_validate").html(response);   
    			}
    		});
        })
		
	});
	</script> <?php
}
add_action( 'wp_ajax_save_live_chat_facebook', 'save_live_chat_facebook_callback' );
function save_live_chat_facebook_callback() {
	$user_id = get_current_user_id();
    $url = $_POST["url"];
    if (!filter_var($url, FILTER_VALIDATE_URL) === false) {
         update_user_meta($user_id,"ninja_chat_live_facebook",$url);
         echo "done";
    }else{
        echo __("Validate url faceebook","live_chat");
    }
   
	wp_die();
}
