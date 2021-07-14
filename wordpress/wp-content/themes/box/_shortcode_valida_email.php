<?php
if ( ! defined( 'ABSPATH' ) ) exit;


function shortcode_valida_email($atts, $content, $tag) {
      $return = '';

      $user = isset($_SESSION["verify_email"]) ? $_SESSION["verify_email"] : false;

      if( $user ){

            ob_start();

            global $current_user, $wpdb, $wp;

            //reenviado
            //ativacao
            //verificado
            //verificado-true

              if( $user["tipo"] == 'verificado' || $user["tipo"] == 'verificado-true' ){
                  $author_obj = get_user_by('id',$user["verify_email_user"]);
                  unset($_SESSION["verify_email"]);
                  ?>
                    <div class="woocommerce-info woocommerce-msg">
                        <ul>
                            <?php
                            if ($user["tipo"]=='verificado-true') echo '<li>E-mail já está verificado!</li>';
                            else echo '<li>E-mail verificado!</li>';
                            echo '<li><b>'.$author_obj->user_email.'</b></li>';
                            echo '<li><b>Usuário:</b> '.$author_obj->user_nicename.'</li>';
                            ?>
                            <li>Por favor, faça seu login.</li>
                            <?php if($wp->request!='login') echo '<li><a href="'.home_url('/login/').'" class="click-loading">Entrar!</a></li>'; ?>
                        </ul>
                        <span class="close-button">X</span>
                    </div>
                  <?php

            }

            if( $user["tipo"] == 'reenviado' ){

                  $author_obj = get_user_by('id',$user["verify_email_user"]);
                  $_SESSION["verify_email"] = array(
                    "verify_email_user" => $user["verify_email_user"],
                    "tipo"=>'ativacao',
                  );

                  ?>
                    <div class="woocommerce-message woocommerce-msg">
                        <ul>
                            <li>E-mail reenviado com sucesso para <b><?php echo $author_obj->user_email; ?></b>.</li>
                            <li>Aguarde alguns minutos e verifique sua caixa de spam.</li>
                            <li>Se precisar de ajuda, <a href="/fale-conosco" class="click-loading">clique aqui</a> e fale conosco.</li>
                        </ul>
                        <span class="close-button">X</span>
                    </div>
                  <?php

            }

            if( $user["tipo"] == 'ativacao' ){

                  $current_site_id = (int) $wpdb->blogid;
                  $user_sites = get_blogs_of_user( $user["verify_email_user"] );

                  $url_link = home_url('/');
                  if ( count($user_sites) == 1 ){
                    $user_target_site = array_values($user_sites)[0];
                    if ( (int) $user_target_site->userblog_id !== $current_site_id ){
                        $url_link = $user_target_site->siteurl.'/';
                    }
                  }
                  $link = add_query_arg(array("ativacao_reenviar" => base64_encode('ativacao_reenviar-'.$user["verify_email_user"])), $url_link );
                  //$linkIndex = add_query_arg(array("ok" => 1), 'index.php');
                  ?>
                      <div class="woocommerce-error woocommerce-msg">
                          <ul>
                              <li>Por favor, verifique seu email com link para ativação. <a href="<?php echo $link ?>" class="click-loading"> Clique aqui, para reenviar email de confirmação.</a></li>
                          </ul>
                          <span class="close-button">X</span>
                      </div>
                  <?php

            }


            $return = ob_get_contents();
          ob_get_clean();
      }
      return $return;
}
add_shortcode('shortcode_valida_email', 'shortcode_valida_email');
?>
