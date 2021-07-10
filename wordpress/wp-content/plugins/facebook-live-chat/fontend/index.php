<?php
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}
/*
* Add css
*/
add_action( 'wp_print_styles', 'fb_live_chat_add_styles' );
function fb_live_chat_add_styles() {
    wp_enqueue_style( 'livechatfacebook',NJ_LIVE_CHAT__PLUGIN_URL."css/live_chat_facbook.css",array(),"1.0.0" );
}
/*
* Add scripts
*/
add_action( 'wp_enqueue_scripts', 'fb_live_chat_add_scripts' );
function fb_live_chat_add_scripts() {
   wp_enqueue_script('jquery');
   wp_register_script( 'livechatfacebook', NJ_LIVE_CHAT__PLUGIN_URL . 'js/live_chat_facbook.js', array(), '1.0.0', true );
   wp_localize_script('livechatfacebook', 'fb_path', array(
        'live_chat_path' => NJ_LIVE_CHAT__PLUGIN_URL,
    ));
    wp_enqueue_script( 'livechatfacebook' );
}
/*
* Set Options box chat
*/
add_action("wp_head","fb_live_chat_set_option");
function fb_live_chat_set_option(){
    ?>
    <style type="text/css">
        #b-c-facebook .chat-f-b, #chat_f_b_smal, #f_bt_start_chat {
            background: <?php echo get_option("live_chat_facebook_backgroud") ?>;
        }
        <?php if ( get_option("live_chat_check_update1") ) {
            if ( !get_option("live_chat_facebook_enable_hello") ) {
                ?>
                #fb_chat_start {
                    display: none !important;
                }
                <?php
            }
        }else{

        } ?>
    </style>
    <?php
}
/*
* Add check visit website
*/
add_action("wp_footer","live_chat_facbook_check_visit");
function live_chat_facbook_check_visit(){
    ?>
    <script type="text/javascript">
         jQuery(document).ready(function($) {
              $(window).scroll(function() {
                  var e = $(window).width();
                  //680 >= e ? f_create_cki('f_chat_open', '0', 1) : f_create_cki('f_chat_open', '1', 1)
              })
          }), setTimeout(function() {
                var visit = f_read_cki("check_fist_vist_f");
                if ( visit == "1" ) {
                    f_ck_chat();
                }else{
                    f_ck_chat();
                    <?php
                    if ( wp_is_mobile() ) {
                        if ( get_option("live_chat_facebook_start_mobile") != 1 ) {
                            ?>
                            fb_eshow("f-chat-conent");
                            <?php
                        }else{
                            ?>
                            fb_ehide("f-chat-conent");
                            f_bt_start_chat();
                            f_create_cki('f_chat_open', '0', 1);
                            <?php
                        }
                    }else{
                        if ( get_option("live_chat_facebook_start_disktop") != 1 ) {
                            ?>
                            fb_eshow("f-chat-conent");
                            <?php
                        }
                        else{
                            ?>
                            fb_ehide("f-chat-conent");
                            f_create_cki('f_chat_open', '0', 1);
                            f_bt_start_chat();
                            <?php
                        }
                    }
                    ?>
                }
          }, 1000);

    </script>
    <?php
}
/*
* Add box chat bottom
*/
add_action("wp_footer","fb_live_chat_add_box_chat");
function fb_live_chat_add_box_chat() {
    global $wp_query,$post;
    $show = true;
    if ( get_option("live_chat_facebook_hide_display") == 1 ) {
        if ( is_page() || is_home() ) {
            $page_id = $post->ID;
            $array_hide = get_option( "live_chat_facebook_hide");
            if( ! $array_hide ) {
                $array_hide = array();
            }
            if ( in_array($page_id, $array_hide )) {
                $show = false;
           }
        }else{
          $show = true;
        }

    }else{
         if ( get_option("live_chat_check_update") && ( is_page() || is_home() ) ) {
            if ( is_page() ) {
                    $page_id = $post->ID;
                    $array_show = get_option( "live_chat_facebook_show");
                    if ( !$array_show ) {
                        $array_show = array();
                    }
                   if ( in_array($page_id, $array_show )) {
                     $show = true;
                   }
                   else{
                     $show = false;
                   }

            }else{

               $show = false;
            }
        }else{
           $show = false;
    }
    }




    if ( $show && get_option("live_chat_facebook_enable") ) {
        if ( wp_is_mobile() && get_option("live_chat_facebook_disable_moblie") == "ok" ) {
            //to do
        }else{
    ?>
<a title="<?php echo get_option("live_chat_facebook_open");  ?>" id="chat_f_b_smal" onclick="chat_f_show()" class="chat_f_vt"><?php echo get_option("live_chat_facebook_open"); ?></a>
<div id="b-c-facebook" class="chat_f_vt">
	<div id="chat-f-b" onclick="b_f_chat()" class="chat-f-b">
        <?php if( get_option("live_chat_facebook_logo") ) {
            $logo = get_option("live_chat_facebook_logo");
        }else{
            $logo= NJ_LIVE_CHAT__PLUGIN_URL ."images/facebook.png";
        } ?>
		<img class="chat-logo" src="<?php echo $logo ?>" alt="logo chat" />
		<label>
			<?php echo get_option("live_chat_facebook_title"); ?>
		</label>
		<span id="fb_alert_num">
			1
		</span>
        <?php
        /*
        * Close chat
        */
         if ( get_option("live_chat_facebook_close") != "ok" ) {
        ?>
		<div id="t_f_chat">
			<a title="Close Chat" href="javascript:;" onclick="chat_f_close()" id="chat_f_close" class="chat-left-5"><img src="<?php echo NJ_LIVE_CHAT__PLUGIN_URL ?>images/close.png" alt="Close chat" title="Close chat" /></a>
		</div>
        <?php } ?>
	</div>
	<div id="f-chat-conent" class="f-chat-conent">
		<div class="fb-page" data-tabs="messages" data-href="<?php echo get_option("live_chat_facebook_user") ?>" data-width="250" data-height="310" data-small-header="true" data-adapt-container-width="true" data-hide-cover="true"
		data-show-facepile="false" data-show-posts="true">
		</div>
		<div id="fb_chat_start">
			<div id="f_enter_1" class="msg_b fb_hide">
				<?php echo get_option("live_chat_facebook_text"); ?>
			</div>

			<p id="f_enter_3" class="fb_hide" align="center">
				<a href="javascript:;" onclick="f_bt_start_chat()" id="f_bt_start_chat"><?php echo get_option("live_chat_facebook_start"); ?></a>
			</p>

		</div>

	</div>
</div>

<?php
$lang = get_option("live_chat_facebook_lang");
if ( !$lang ) {
    $lang = "en_US";
}
?>

<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/<?php echo $lang ?>/sdk.js#xfbml=1&version=v2.5";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));</script>

<?php } }
}