<?php
/**
 * Seller setup wizard class
 */
class Dokan_Seller_Setup_Wizard extends Dokan_Setup_Wizard {
    /** @var string Currenct Step */
    protected $step        = '';

    /** @var array Steps for the setup wizard */
    protected $steps       = array();

    /** @var string custom logo url of the theme */
    protected $custom_logo = '';

    /**
     * Hook in tabs.
     */
    public function __construct() {
        add_filter( 'woocommerce_registration_redirect', array( $this, 'filter_woocommerce_registration_redirect' ), 10, 1 );
        add_action( 'init', array( $this, 'setup_wizard' ) );
    }

    // define the woocommerce_registration_redirect callback
    public function filter_woocommerce_registration_redirect( $var ) {
        $url  = $var;
        $user = wp_get_current_user();

        if ( in_array( 'seller', $user->roles ) ) {
            $url = site_url( '?page=configuracao-lavanderia' );
        }

        return $url;
    }

    /**
     * Show the setup wizard.
     */
    public function setup_wizard() {
        if ( empty( $_GET['page'] ) || 'configuracao-lavanderia' !== $_GET['page'] ) {
            return;
        }

        if ( ! is_user_logged_in() ) {
            return;
        }

        $this->custom_logo = null;
        $dokan_appearance  = get_option( 'dokan_appearance', [] );

        if ( isset( $dokan_appearance['setup_wizard_logo_url'] ) && ! empty( $dokan_appearance['setup_wizard_logo_url'] ) ) {
            $this->custom_logo = $dokan_appearance['setup_wizard_logo_url'];
        }

        $this->store_id   = get_current_user_id();
        $this->store_info = dokan_get_store_info( $this->store_id );

        $this->steps = array(
            'inicio' => array(
                'name'    =>  __( 'Introduction', 'dokan' ),
                'view'    => array( $this, 'dokan_setup_introduction' ),
                'handler' => ''
            ),
            'endereco' => array(
                'name'    =>  __( 'Store', 'dokan' ),
                'view'    => array( $this, 'dokan_setup_store' ),
                'handler' => array( $this, 'dokan_setup_store_save' ),
            ),
            'pagamento' => array(
                'name'    =>  __( 'Payment', 'dokan' ),
                'view'    => array( $this, 'dokan_setup_payment' ),
                'handler' => array( $this, 'dokan_setup_payment_save' ),
            ),
            'administracao' => array(
                'name'    =>  __( 'Ready!', 'dokan' ),
                'view'    => array( $this, 'dokan_setup_ready' ),
                'handler' => ''
            )
        );
        $this->step = isset( $_GET['passo'] ) ? sanitize_key( $_GET['passo'] ) : current( array_keys( $this->steps ) );

        $this->enqueue_scripts();

        if ( ! empty( $_POST['save_step'] ) && isset( $this->steps[ $this->step ]['handler'] ) ) {
            call_user_func( $this->steps[ $this->step ]['handler'] );
        }

        ob_start();
        $this->setup_wizard_header();
        $this->setup_wizard_steps();
        $this->setup_wizard_content();
        $this->setup_wizard_footer();
        exit;
    }

    /**
     * Setup Wizard Header.
     */
    public function setup_wizard_header() {
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php _e( 'Dokan &rsaquo; Setup Wizard', 'dokan' ); ?></title>
            <?php wp_print_scripts( 'wc-setup' ); ?>
            <?php do_action( 'admin_print_styles' ); ?>
            <?php do_action( 'admin_head' ); ?>
            <style type="text/css">
                .wc-setup-steps {
                    justify-content: center;
                }
                .wc-setup-content a {
                    color: #f39132;
                }
                .wc-setup-steps li.active:before {
                    border-color: #f39132;
                }
                .wc-setup-steps li.active {
                    border-color: #f39132;
                    color: #f39132;
                }
                .wc-setup-steps li.done:before {
                    border-color: #f39132;
                }
                .wc-setup-steps li.done {
                    border-color: #f39132;
                    color: #f39132;
                }
                .wc-setup .wc-setup-actions .button-primary, .wc-setup .wc-setup-actions .button-primary, .wc-setup .wc-setup-actions .button-primary {
                    background: #f39132 !important;
                }
                .wc-setup .wc-setup-actions .button-primary:active, .wc-setup .wc-setup-actions .button-primary:focus, .wc-setup .wc-setup-actions .button-primary:hover {
                    background: #ff6b00 !important;
                    border-color: #ff6b00 !important;
                }
                .wc-setup-content .wc-setup-next-steps ul .setup-product a, .wc-setup-content .wc-setup-next-steps ul .setup-product a, .wc-setup-content .wc-setup-next-steps ul .setup-product a {
                    background: #f39132 !important;
                    box-shadow: inset 0 1px 0 rgba(255,255,255,.25),0 1px 0 #f39132;
                }
                .wc-setup-content .wc-setup-next-steps ul .setup-product a:active, .wc-setup-content .wc-setup-next-steps ul .setup-product a:focus, .wc-setup-content .wc-setup-next-steps ul .setup-product a:hover {
                    background: #ff6b00 !important;
                    border-color: #ff6b00 !important;
                    box-shadow: inset 0 1px 0 rgba(255,255,255,.25),0 1px 0 #ff6b00;
                }
                .wc-setup .wc-setup-actions .button-primary {
                    border-color: #f39132 !important;
                }
                .wc-setup-content .wc-setup-next-steps ul .setup-product a {
                    border-color: #f39132 !important;
                }
                ul.wc-wizard-payment-gateways li.wc-wizard-gateway .wc-wizard-gateway-enable input:checked+label:before {
                    background: #f39132 !important;
                    border-color: #f39132 !important;
                }
                .form-table th, .form-table td {
                    padding: 3px 0 !important;
                    margin: 0;
                    border: 0;
                }
                body {
                    margin: 10px auto 24px !important;
                    box-shadow: none;
                    background: #f1f1f1;
                    padding: 0;
                }
                :focus {
                    outline: none !important;
                }
                a, a:hover, a:focus, a:active{
                  outline: none !important;
                }
                html {
                    background: top 130% right 100% no-repeat,linear-gradient(hsl(215, 100%, 15%),#1561a4 33.19%,rgb(16, 86, 154) 81.51%,hsl(212, 61%, 27%));
                    margin: 0 20px;
                    height: 100%;
                }
                .wc-setup-content {
                    box-shadow: 6px 5px 12px 1px rgba(0, 0, 0, 0.17);
                    padding: 24px 24px 0;
                    background: ;
                    overflow: hidden;
                    zoom: 1;
                    border-radius: 20px;
                }
                .button {
                    border-radius: 20px !important;
                    box-shadow: 6px 5px 12px 1px rgba(0, 0, 0, 0.17) !important;
                    margin-bottom: 25px !important;
                    margin-right: 17px !important;
                }
                .button:hover {
                    box-shadow: 6px 5px 12px 1px rgba(0, 0, 0, 0.30) !important;
                }
                body{
                  background: transparent;
                  max-width: 800px !important;
                }
                .form-table input, textarea {
                    line-height: 20px;
                    font-size: 15px;
                    padding: 3px 5px;
                    border: 1px solid #cecece;
                    -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
                    box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                    border-radius: 10px;
                    background: #fffef7;
                }

                @media (min-width: 767px) and (max-width: 768px) {
                  .form-table input, textarea {
                      line-height: 20px !important;
                      font-size: 15px !important;
                      padding: 3px 5px !important;
                      border: 1px solid #cecece;
                      -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
                      box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                      border-radius: 10px !important;
                      background: #fffef7;
                  }
                  .form-table td, .form-table th {
                    display: table-cell;
                  }
                }
                .dokan-form-group{
                  margin-bottom: 1px;
                }
                h2{
                  font-size: 25px;
                }

            </style>

        </head>
        <body class="wc-setup wp-core-ui">
            <div class="loading-site"><span>Aguarde...</span></div>
            <?php
                if ( ! empty( $this->custom_logo ) ) {
            ?>
                <h1 id="wc-logo"><a class="click-loading" href="https://www.pointlave.com.br/"><img src="https://www.pointlave.com.br/logo/logo_black.png" alt="PointLave" /></a></h1>
            <?php
                } else {
                    echo '<h1 id="wc-logo">' . get_bloginfo( 'name' ) . '</h1>';
                }
    }



    /**
     * Introduction step.
     */
    public function dokan_setup_introduction() {
        //global $current_user, $wpdb;
        //dump($current_user->user_nicename);
        //$url = 'item/' . $current_user->user_nicename;
        $url = home_url('/lavanderias/area-administrativa/settings/store/');
        ?>
        <h1><?php _e( 'Welcome to the Marketplace!', 'dokan' ); ?></h1>
        <p><?php _e( 'Thank you for choosing The Marketplace to power your online store! This quick setup wizard will help you configure the basic settings. <strong>It’s completely optional and shouldn’t take longer than two minutes.</strong>', 'dokan' ); ?></p>
        <p><?php _e( 'No time right now? If you don’t want to go through the wizard, you can skip and return to the Store!', 'dokan' ); ?></p>
        <p class="wc-setup-actions step">
            <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button-primary button button-large button-next"><?php _e( 'Let\'s Go!', 'dokan' ); ?></a>
            <a href="<?php echo esc_url( $url ); ?>" class="button button-large click-loading"><?php _e( 'Not right now', 'dokan' ); ?></a>
        </p>
        <?php
    }

    /**
     * Store step.
     */
    public function dokan_setup_store() {
        $store_info      = $this->store_info;

        $user_id = get_current_user_id();
        $endereco = get_user_meta( $user_id,'address_1',true);
        $enderecoBairro = get_user_meta( $user_id,'neighborhood',true);
        $enderecoCidade = get_user_meta( $user_id,'city',true);
        $enderecoCEP = get_user_meta( $user_id,'postcode',true);
        $enderecoPais = get_user_meta( $user_id,'country',true);
        $enderecoEstado = get_user_meta( $user_id,'state',true);
        $location = $endereco.', '.$enderecoCidade;

        //dump($store_info);

        $store_ppp       = isset( $store_info['store_ppp'] ) ? esc_attr( $store_info['store_ppp'] ) : 10;
        $show_email      = isset( $store_info['show_email'] ) ? esc_attr( $store_info['show_email'] ) : 'no';
        $address_street1 = isset( $store_info['address']['endereco'] ) ? $store_info['address']['endereco'] : $endereco;
        //$address_street2 = isset( $store_info['address']['street_2'] ) ? $store_info['address']['street_2'] : $enderecoBairro;
        $address_city    = isset( $store_info['address']['city'] ) ? $store_info['address']['city'] : $enderecoCidade;
        $address_zip     = isset( $store_info['address']['zip'] ) ? $store_info['address']['zip'] : $enderecoCEP;
        $address_country = isset( $store_info['address']['country'] ) ? $store_info['address']['country'] : $enderecoPais;
        $address_state   = isset( $store_info['address']['state'] ) ? $store_info['address']['state'] : $enderecoEstado;
        $address_endereco   = isset( $store_info['address']['endereco'] ) ? $store_info['address']['endereco'] : $endereco;
        $address_numero   = isset( $store_info['address']['numero'] ) ? $store_info['address']['numero'] : '';
        $address_complemento   = isset( $store_info['address']['complemento'] ) ? $store_info['address']['complemento'] : '';
        $address_bairro   = isset( $store_info['address']['bairro'] ) ? $store_info['address']['bairro'] : $enderecoBairro;
        $telefone   = isset( $store_info['phone'] ) ? $store_info['phone'] : '';
        $lavanderia   = isset( $store_info['store_name'] ) ? $store_info['store_name'] : '';


        //dump($user_id);
        //dump($store_info);
        //exit;

        $country_obj   = new WC_Countries();
        $countries     = $country_obj->countries;
        $states        = $country_obj->states;

        ?>
        <h1><?php _e( 'Store Setup', 'dokan' ); ?></h1>
        <form method="post" id="form-endereco">
            <table class="form-table">
                <?php /* ?>
                <tr>
                    <th scope="row"><label for="store_ppp"><?php _e( 'Store Product Per Page', 'dokan' ); ?></label></th>
                    <td>
                        <input type="text" id="store_ppp" name="store_ppp" value="<?php echo $store_ppp; ?>" />
                    </td>
                </tr>
                <?php */ ?>
                <input type="hidden" id="store_ppp" name="store_ppp" value="<?php echo $store_ppp; ?>" />
                <tr>
                    <th scope="row"><label for="address[numero]"><?php _e( 'Número', 'dokan' ); ?></label> (Obrigatório)</th>
                    <td>
                        <input type="text" placeholder="<?php _e( 'Informe o número!', 'porto' ); ?>" id="address[numero]" name="address[numero]" value="<?php echo $address_numero; ?>" />
                    </td>
                </tr>
                <tr style="display:none;">
                    <th scope="row"><label for="address[complemento]"><?php _e( 'Complemento', 'dokan' ); ?></label></th>
                    <td>
                        <input type="text" id="address[complemento]" name="address[complemento]" value="<?php echo $address_complemento; ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="lavanderia"><?php _e( 'Nome da Lavanderia ', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" readonly="false" type="text" id="lavanderia" name="lavanderia" value="<?php echo $lavanderia; ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="telefone"><?php _e( 'Telefone ', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="telefone" name="telefone" value="<?php echo $telefone; ?>" readonly="false" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <?php _e( 'Preenchimento Automático.<br/> As informações podem ser alteradas em seu painel de administração.', 'dokan' ); ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="address[zip]"><?php _e( 'CEP', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="address[zip]" name="address[zip]" value="<?php echo $address_zip; ?>" readonly="false" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="address[endereco]"><?php _e( 'Endereço', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="address[endereco]" name="address[endereco]" value="<?php echo $address_endereco; ?>" readonly="false" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="address[bairro]"><?php _e( 'Bairro', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="address[bairro]" name="address[bairro]" value="<?php echo $address_bairro; ?>" readonly="false" />
                    </td>
                </tr>
                <?php ///* ?>
                <tr>
                    <th scope="row"><label for="address[city]"><?php _e( 'City', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="address[city]" name="address[city]" value="<?php echo $address_city; ?>" readonly="false"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="address[state]"><?php _e( 'State', 'dokan' ); ?></label></th>
                    <td>
                        <input class="disabled" type="text" id="address[state]" name="address[state]" value="<?php echo $address_state; ?>" readonly="false" />
                    </td>
                </tr>
                <?php //*/ ?>
                <?php /* ?>
                <tr>
                    <th scope="row"><label for="address[country]"><?php _e( 'Country', 'dokan' ); ?></label></th>
                    <td>
                        <select name="address[country]" class="wc-enhanced-select" id="address[country]">
                            <?php dokan_country_dropdown( $countries, $address_country, false ); ?>
                        </select>
                    </td>
                </tr>
                <?php /// ?>
                <tr>
                    <th scope="row"><label for="show_email"><?php _e( 'Email', 'dokan' ); ?></label></th>
                    <td>
                        <input type="checkbox" name="show_email" id="show_email" class="input-checkbox" value="1" <?php echo ( $show_email == 'yes' ) ? 'checked="checked"' : ''; ?>/>
                        <label for="show_email"><?php _e( 'Show email address in store', 'dokan' ); ?></label>
                    </td>
                </tr>
                <?php */ ?>

            </table>
            <p class="wc-setup-actions step">

                <input type="hidden" id="address[country]" name="address[country]" value="<?php echo $address_country; ?>" />

                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'dokan' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'dokan' ); ?></a>
                <?php wp_nonce_field( 'configuracao-lavanderia' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save store options.
     */
    public function dokan_setup_store_save() {

        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'configuracao-lavanderia' ) ) {
            return;
        }

        if( empty($_POST['address']['numero']) || empty($_POST['telefone']) || empty($_POST['lavanderia']) ){
          wp_redirect( esc_url_raw( $this->get_reload_step_link() ) );
          exit;
        }

        $dokan_settings = $this->store_info;

        $dokan_settings['store_ppp']  = absint( $_POST['store_ppp'] );
        $dokan_settings['address']    = isset( $_POST['address'] ) ? $_POST['address'] : [];
        $dokan_settings['show_email'] = isset( $_POST['show_email'] ) ? 'yes' : 'no';
        $dokan_settings['phone'] = $_POST['telefone'];
        $dokan_settings['store_name'] = $_POST['lavanderia'];

        $dokan_settings['profile_completion']['store_name'] = 10;
        $dokan_settings['profile_completion']['address'] = 10;
        $dokan_settings['profile_completion']['progress'] = 20;
        $dokan_settings['profile_completion']['next_todo'] = 'Adicione sua "Logo Marca" para completar 30% do seu perfil.';

        $user_id = get_current_user_id();
        //$address_city = get_user_meta( $user_id,'city',true);
        //$address_zip = get_user_meta( $user_id,'postcode',true);
        //$address_country = get_user_meta( $user_id,'country',true);
        //$address_state = get_user_meta( $user_id,'state',true);
        //$location = get_user_meta( $user_id,'location',true);

        $location = $_POST['address']['endereco'].','.$_POST['address']['numero'].' - '.$_POST['address']['bairro'].','.$_POST['address']['city'].' - '.$_POST['address']['state'].', '.$_POST['address']['zip'];
        //Av. Barão do Rio Branco, 1171 - Centro, Juiz de Fora - MG, 36013-020, Brasil
        $dokan_settings['find_address'] = $location;

        update_user_meta( $this->store_id, 'dokan_profile_settings', $dokan_settings );

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * payment step.
     */
    public function dokan_setup_payment() {
        $methods    = dokan_withdraw_get_active_methods();
        $store_info = $this->store_info;
        ?>
        <h1><?php _e( 'Payment Setup', 'dokan' ); ?></h1>
        <form method="post">
            <table class="form-table">
                <?php
                    foreach ( $methods as $method_key ) {
                        $method = dokan_withdraw_get_method( $method_key );
                ?>
                    <tr>
                        <th scope="row"><label><?php echo $method['title']; ?></label></th>
                        <td>
                            <?php
                                if ( is_callable( $method['callback'] ) ) {
                                    call_user_func( $method['callback'], $store_info );
                                }
                            ?>
                        </td>
                    </tr>
                <?php
                    }
                ?>
            </table>
            <p class="wc-setup-actions step">
                <input type="submit" class="button-primary button button-large button-next" value="<?php esc_attr_e( 'Continue', 'dokan' ); ?>" name="save_step" />
                <a href="<?php echo esc_url( $this->get_next_step_link() ); ?>" class="button button-large button-next"><?php _e( 'Skip this step', 'dokan' ); ?></a>
                <?php wp_nonce_field( 'configuracao-lavanderia' ); ?>
            </p>
        </form>
        <?php
    }

    /**
     * Save payment options.
     */
    public function dokan_setup_payment_save() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'configuracao-lavanderia' ) ) {
            return;
        }

        $dokan_settings = $this->store_info;

        if ( isset( $_POST['settings']['bank'] ) ) {
            $bank = $_POST['settings']['bank'];

            $dokan_settings['payment']['bank'] = array(
                'razao_social'   => sanitize_text_field( $bank['razao_social'] ),
                'cnpj'   => sanitize_text_field( $bank['cnpj'] ),
                'ie'   => sanitize_text_field( $bank['ie'] ),
                'ac_name'   => sanitize_text_field( $bank['ac_name'] ),
                'ac_number' => sanitize_text_field( $bank['ac_number'] ),
                'bank_name' => sanitize_text_field( $bank['bank_name'] ),
                'bank_addr' => sanitize_text_field( $bank['bank_addr'] ),
                'swift'     => sanitize_text_field( $bank['swift'] ),
            );
        }

        if ( isset( $_POST['settings']['paypal'] ) ) {
            $dokan_settings['payment']['paypal'] = array(
                'email' => filter_var( $_POST['settings']['paypal']['email'], FILTER_VALIDATE_EMAIL )
            );
        }

        if ( isset( $_POST['settings']['skrill'] ) ) {
            $dokan_settings['payment']['skrill'] = array(
                'email' => filter_var( $_POST['settings']['skrill']['email'], FILTER_VALIDATE_EMAIL )
            );
        }

        update_user_meta( $this->store_id, 'dokan_profile_settings', $dokan_settings );

        wp_redirect( esc_url_raw( $this->get_next_step_link() ) );
        exit;
    }

    /**
     * Final step.
     */
    public function dokan_setup_ready() {
        $options   = get_option( 'dokan_pages', [] );
        $page_id   = intval( $options['dashboard'] );
        $page      = get_post( $page_id );
        ?>
        <h2><?php _e( 'Your Store is Ready!', 'dokan' ); ?></h2>
        <h4><?php _e( 'Next Steps', 'dokan' ); ?></h4>
        <div class="wc-setup-next-steps">
            <div class="wc-setup-next-steps-first">

                <ul>
                    <li class="setup-product"><a class="button button-primary button-large click-loading" href="<?php echo esc_url( home_url('/lavanderias/area-administrativa/settings/store/') ); ?>"><?php _e( 'Go to your Store Dashboard!', 'dokan' ); ?></a></li>
                </ul>
            </div>
        </div>
        <?php
    }



    /**
     * Setup Wizard Footer.
     */
    public function setup_wizard_footer() {
        ?>
            <?php if ( 'next_steps' === $this->step ) : ?>
                <a class="wc-return-to-dashboard click-loading" href="<?php echo esc_url( site_url() ); ?>"><?php _e( 'Return to the Marketplace', 'dokan' ); ?></a>
            <?php endif; ?>
            <style>
            .dokan-loading {
                position: absolute !important;
                width: 100% !important;
                height: 100% !important;
                background: #222 url(assets/images/loader3.gif) 50% 130px no-repeat !important;
                left: -5px !important;
                top: 0 !important;
                opacity: 0.7 !important;
                -webkit-opacity: 0.7 !important;
                -ms-opacity: 0.7 !important;
                -o-opacity: 0.7 !important;
                -moz-opacity: 0.7 !important;
                filter: alpha(opacity=70) !important;
                z-index: 8 !important;
                filter: invert(65%) saturate(1) hue-rotate(214deg) contrast(250%) brightness(238%);
                float: none;
            }
            .loading-site {
                position: fixed;
                width: 100%;
                height: 100%;
                background: #222 url(wp-content/themes/box/img/loader4.gif) 50% 10% no-repeat;
                /* http://192.168.1.25/lava/juiz-de-fora/wp-content/themes/box/img/loader4.gif */
                left: 0;
                top: 0;
                filter: invert(1%) saturate(17) hue-rotate(-654deg) contrast(195%) brightness(103%);
                z-index: 8;
                display: none;
            }
            .loading-site span{
                color: #fff;
                display: block;
                position: relative;
                top:85px;
                text-align: center;
                font-size: 0.8em;
            }

            input.disabled {
              opacity: 0.6 !important;
              -webkit-opacity: 0.6 !important;
              -ms-opacity: 0.6 !important;
              -o-opacity: 0.6 !important;
              -moz-opacity: 0.6 !important;
              filter: alpha(opacity=60);
            }

            </style>
            <script>

              jQuery('.click-loading').on('click', function(e) {
                jQuery(".loading-site").css("height",jQuery( document ).height());
                jQuery('html,body').animate({scrollTop:0}, 500,'swing');
                jQuery(window).scrollTop(0);
                document.body.scrollTop = document.documentElement.scrollTop = 0;
                jQuery(".loading-site").delay(0).fadeTo(3500,0.90,'linear');
              });
              /*
              function verificaCep(cep){
                    var busca = jQuery.ajax({
                          url: '//correiosapi.apphb.com/cep/' + cep,
                          dataType: 'jsonp',
                          crossDomain: true,
                          contentType: 'application/json',
                          success:function(data, status, retorno){

                            //enderecoDistancia = data.tipoDeLogradouro + ' ' + data.logradouro + ', ' + data.cidade;
                            //enderecoCidade = data.cidade;

                            //MyObject.salvaEnderecoDistancia(data);
                            //jQuery('#address[street_1]').val(data.cidade);
                            //jQuery('#address[street_2]').val(data.cidade);
                            //jQuery('#address[city]').val(data.cidade);
                            jQuery('#address[state]').val(data.cidade);
                            console.log(data.cidade);
                            return;
                          }
                    });
                    //enderecoCidade = '';
                    //MyObject.salvaEnderecoDistancia();
                    //console.log('ok1');
                    return;
              };
              */


              //console.log(ir_adm);

            </script>
            </body>
        </html>
        <?php
    }




}

new Dokan_Seller_Setup_Wizard();
