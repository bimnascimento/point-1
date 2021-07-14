
<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
if ( !is_user_logged_in() ) :
?>

    <div class="rp_shipping_button"><a class="btn_shipping click-register transicao">Quero me cadastrar!</a></div>

<?php endif;
if ( is_user_logged_in() ) :
*/
?>
<div id="rp_shipping_calculator">

    <?php //if ($this->get_setting("shipping_type") != 2): ?>
    <div class="rp_shipping_button">
        <a class="btn_shipping btn_shipping_postcode">
          <?php echo __("Verificar meu endereço!", "rpship"); ?>
        </a>
        <div class="cep-pesquisado"></div>
    </div>
    <?php //endif; ?>

    <div class="rp_shiiping_form">
        <form class="woocommerce-shipping-calculator" action="" method="post" id='verifica-cep-form'>
            <section class="shipping-calculator-form">


                <?php
                if (is_product()) {
                    global $post;
                    ?>
                    <input type="hidden" name="product_id" value="<?php echo $post->ID; ?>" />
                <?php } ?>



                <?php /* ?>
                <p class="form-row form-row-wide css_shipping_country hidden2">
                    <select name="calc_shipping_country" id="calc_shipping_country" class="country_to_state" rel="calc_shipping_state">
                        <option value=""><?php _e('Select a country', 'woocommerce'); ?></option>
                        <?php
                        foreach (WC()->countries->get_shipping_countries() as $key => $value)
                            echo '<option value="' . esc_attr($key) . '"' . selected(WC()->customer->get_shipping_country(), esc_attr($key), false) . '>' . esc_html($value) . '</option>';
                        ?>
                    </select>
                </p>
                <?php */ ?>




                <?php /* ?>
                <p class="form-row form-row-wide shipping_state css_shipping_state hidden2">
                    <?php
                    $current_cc = WC()->customer->get_shipping_country();
                    $current_r = WC()->customer->get_shipping_state();
                    $states = WC()->countries->get_states($current_cc);
                    if (is_array($states) && empty($states)) {
                        ?>
                        <input type="hidden" name="calc_shipping_state" class="text-state" id="calc_shipping_state" placeholder="<?php _e('State / county', 'woocommerce'); ?>" />
                        <?php
                    } elseif (is_array($states)) {
                        ?>
                        <select name="calc_shipping_state" id="calc_shipping_state" placeholder="<?php _e('State / county', 'woocommerce'); ?>">
                            <option value=""><?php _e('Select a state&hellip;', 'woocommerce'); ?></option>
                            <?php
                            foreach ($states as $ckey => $cvalue)
                                echo '<option value="' . esc_attr($ckey) . '" ' . selected($current_r, $ckey, false) . '>' . __(esc_html($cvalue), 'woocommerce') . '</option>';
                            ?>
                        </select>
                        <?php
                    } else {
                        ?>
                        <input type="text" class="input-text" value="<?php echo esc_attr($current_r); ?>" placeholder="<?php _e('State / county', 'woocommerce'); ?>" name="calc_shipping_state" id="calc_shipping_state" />
                        <?php
                    }
                    ?>
                </p>
                <?php */ ?>




                <?php /* ?>
                <?php if (apply_filters('woocommerce_shipping_calculator_enable_city', true)) : ?>
                <p class="form-row form-row-wide">
                    <input type="text" class="input-text" value="<?php echo esc_attr(WC()->customer->get_shipping_city()); ?>" placeholder="<?php _e('City', 'woocommerce'); ?>" name="calc_shipping_city" id="calc_shipping_city" />
                </p>
                <?php endif; ?>
                <?php */ ?>


                <?php ///* ?>
                <?php if (apply_filters('woocommerce_shipping_calculator_enable_postcode', true)) : ?>
                <p class="form-row form-row-wide shipping_postcode">
                    <input type="text" class="input-text" value="<?php //echo esc_attr(WC()->customer->get_shipping_postcode()); ?>" placeholder="<?php _e('Informe o CEP!', 'woocommerce'); ?>" name="calc_shipping_postcode" id="calc_shipping_postcode" />
                    <button type="submit"  name="rp_calc_shipping" value="1" class="rp_calc_shipping button"><?php _e('Pesquisar', 'rpship'); ?></button>
                </p>
                <?php endif; ?>
                <?php ///* ?>




                <?php ///* ?>
                <p class="form-row form-row-wide shippingmethod_container">
                    <?php
                    /*
                    $packages = WC()->cart->get_shipping_packages();
                    $packages = WC()->shipping->calculate_shipping($packages);
                    $available_methods = WC()->shipping->get_packages();
                    //dump($available_methods[0]["rates"]);
                    if ($this->get_setting('shipping_type') == 1) {
                        if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                            foreach ($available_methods[0]["rates"] as $key => $method) {
                                echo '<input name="calc_shipping_method" class="shipping_method" type="radio" ' . checked($key, WC()->session->chosen_shipping_method, false) . ' value="' . esc_attr($key) . '">&nbsp;' . wp_kses_post($method->label) . "<br>";
                            }
                        }
                    } else if( $this->get_setting('shipping_type') == 0 ) {
                        ?>
                        <select name="calc_shipping_method" id="calc_shipping_method" class="shipping_method">
                            <option value=""><?php _e('Select a Shipping Method ', 'woocommerce'); ?></option>
                            <?php
                            if (isset($available_methods[0]["rates"]) && count($available_methods[0]["rates"]) > 0) {
                                foreach ($available_methods[0]["rates"] as $key => $method) {
                                    echo '<option value="' . esc_attr($key) . '" ' . selected($key, WC()->session->chosen_shipping_method, false) . '>' . wp_kses_post($method->label) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <?php
                    }
                    */
                    ?>
                </p>
                <?php //*/ ?>






                <?php
                /*
                if ($this->get_setting("shipping_type") == 2): ?>
                <p style="text-align: center;">
                    <button type="submit" name="rp_calc_shipping" value="1" class="rp_calc_shipping button"><?php _e('Pesquisar', 'rpship'); ?></button>
                    <span class="loaderimage"><img src="<?php echo self::$plugin_url ?>assets/images/rp-loader.gif" alt=""></span>
                </p>
                <?php elseif ($this->get_setting("shipping_type") != 2): ?>
                <p style="text-align: center;">
                    <button type="submit"  name="rp_calc_shipping" value="1" class="rp_calc_shipping button"><?php _e('Pesquisar', 'rpship'); ?></button>
                    <span class="loaderimage"><img src="<?php echo self::$plugin_url ?>assets/images/rp-loader.gif" alt=""></span>
                </p>
                <?php endif;
                */
                ?>


                <?php if ($this->get_setting("display_message") == 1): ?>
                    <div class="rp_message"></div>
                <?php endif; ?>

                <?php wp_nonce_field('woocommerce-cart'); ?>


            </section>
        </form>
        <script>
        //jQuery(document).ready(function($) {
        	//jQuery('#verifica-cep-form').validate();
        //});
        </script>
    </div>

</div>
<?php
//endif;
?>
