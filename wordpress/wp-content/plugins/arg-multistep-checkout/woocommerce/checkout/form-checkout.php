<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     2.3.0
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WooCommerce')) {
    exit;
}

//Define tabs class
$tabsClass = array(
    '2'	=> 'two-tabs',
    '3'	=> 'three-tabs',
    '4'	=> 'four-tabs',
    '5'	=> 'five-tabs',
    '6'	=> 'six-tabs'
);

//Get admin options
$options = get_option('arg-mc-options');

if (empty($options)) :
    $options = array();
endif;

//Show / hide form steps
$showLogin 	    = !empty($options['show_login']) ? true : false;
$showCoupon     = !empty($options['show_coupon']) ? true : false;
$showOrder 	    = !empty($options['show_order']) ? true : false;
$showShipping 	= false;

if (!empty($options['show_additional_information'])) :
    $showShipping = true;
else :
    add_filter('woocommerce_enable_order_notes_field', '__return_false');
endif;

if (true === WC()->cart->needs_shipping_address()) :
    $showShipping = true;
endif;


if ($showLogin === false) :
    unset($options['steps']['login']);
endif;

if ($showCoupon === false) :
    unset($options['steps']['coupon']);
endif;

if ($showOrder === false) :
    unset($options['steps']['order']);
endif;

if ($showShipping === false) :
    unset($options['steps']['shipping']);
endif;


//Merge Billing and Shipping
if (!empty($options['merge_billing_shipping'])) :
    unset($options['steps']['billing']);
    unset($options['steps']['shipping']);
else :
    unset($options['steps']['billing_shipping']);
endif;


//Merge Order and Payment
if (!empty($options['merge_order_payment'])) :
    unset($options['steps']['order']);
    unset($options['steps']['payment']);
else :
    unset($options['steps']['order_payment']);
endif;

$options = apply_filters('arg-mc-init-options', $options);

if (is_user_logged_in()) :
    $showLogin = false;
    unset($options['steps']['login']);
endif;

?>

<div class="argmc-wrapper wrapper-no-bkg <?php echo $options['tabs_template']; ?>">
	<?php
	wc_print_notices();

	//If checkout registration is disabled and not logged in, the user cannot checkout
	if (!$checkout->enable_signup && ! $checkout->enable_guest_checkout && ! is_user_logged_in()) {

            //echo apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce'));
            echo do_shortcode('[vc_row full_width="stretch_row" full_height="yes" equal_height="yes" content_placement="middle" css_animation="none" is_section="yes" section_skin="parallax" remove_margin_top="yes" remove_margin_bottom="yes" remove_padding_top="yes" remove_padding_bottom="yes" remove_border="yes" css=".vc_custom_1495840038053{margin-top: -50px !important;}"][vc_column][porto_block id="bloco-login-page" name="bloco-login" el_class="access-panel access-panel-login"][/vc_column][/vc_row]');


	}else{

	remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);
	remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

	do_action('woocommerce_before_checkout_form', $checkout);

	if (!empty($options['steps'])) :
            $class      = $options['tabs_width'];
            $countSteps = count($options['steps']);
            $class     .= $countSteps >= 2 && $countSteps <= 6 ? ' '. $tabsClass[$countSteps] : '';
            ?>
            <ul class="argmc-tabs-list <?php echo $class; ?>">
                <?php
                $i = 1;
                //Display tabs
                foreach ($options['steps'] as $template => $step) :
                    ?>
                    <li class="argmc-tab-item<?php echo $i == 1 ? ' current visited' : ''; ?>">

                        <div class="argmc-tab-item-outer">
                            <div class="argmc-tab-item-inner">
                                <div class="argmc-tab-number-wrapper">
                                    <div class="argmc-tab-number">
                                        <span class="number-text"><?php echo $i.'.'; ?></span>
                                        <span class="tab-completed-icon"></span>
                                    </div>
                                </div>

                                <div class="argmc-tab-text"><?php echo $step['text']; ?></div>
                            </div>
                        </div>

                    </li>
                    <?php
                    $i++;
                endforeach;
                ?>
            </ul><!--argmc-tabs-list-->

            <div class="argmc-form-steps-wrapper">

                    <?php
                    $i                              = 1;

                    $displayCheckoutForm            = true;
                    $checkoutOpenFormHtml           = '<form name="checkout" method="post" class="checkout argmc-form" action="' . esc_url(wc_get_checkout_url()) .'" enctype="multipart/form-data">';

                    $displayOrderReview             = true;
                    $checkoutOpenOrderReviewHtml    = '<div id="order_review" class="woocommerce-checkout-review-order">';

                    $firstStep                      = current(array_keys($options['steps']));

                    foreach ($options['steps'] as $template => $step) :
                        switch ($template) :
                            //Login step
                            case 'login':
                                ?>
                                 <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php do_action('woocommerce_checkout_login_form', $checkout); ?>
                                </div>
                                <?php

                                break;

                            //Coupon step
                            case 'coupon':
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php do_action('woocommerce_checkout_coupon_form', $checkout); ?>
                                </div>
                                <?php

                                break;

                            //Billing step
                            case 'billing':
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php
                                    if (sizeof($checkout->checkout_fields['billing']) > 0) :
                                        do_action('woocommerce_checkout_before_customer_details');
                                        do_action('woocommerce_checkout_billing');
                                    endif;
                                    ?>
                                </div>
                                <?php

                                break;

                            //Shipping step
                            case 'shipping':
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php
                                    if (sizeof($checkout->checkout_fields['shipping']) > 0) :
                                        do_action('woocommerce_checkout_shipping');
                                        do_action('woocommerce_checkout_after_customer_details');
                                    endif;
                                    ?>
                                </div>
                                <?php

                                break;

                            //Order step
                            case 'order' :
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;

                                if ($displayOrderReview == true) :
                                    do_action('woocommerce_checkout_before_order_review');
                                    echo $checkoutOpenOrderReviewHtml;

                                    $displayOrderReview = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php do_action('woocommerce_order_review'); ?>
                                </div>
                                <?php

                                break;

                            //Billing & shipping step
                            case 'billing_shipping' :
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php
                                    if (sizeof($checkout->checkout_fields['billing']) > 0) :
                                        do_action('woocommerce_checkout_before_customer_details');
                                        do_action('woocommerce_checkout_billing');
                                    endif;

                                    if (sizeof( $checkout->checkout_fields['shipping']) > 0) :
                                        do_action('woocommerce_checkout_shipping');
                                        do_action('woocommerce_checkout_after_customer_details');
                                    endif;
                                    ?>
                                </div>
                                <?php

                                break;

                            //Order & payment step
                            case 'order_payment' :
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php
                                    do_action('woocommerce_checkout_before_order_review');
                                    echo $checkoutOpenOrderReviewHtml;

                                    if ($showOrder) :
                                        do_action('woocommerce_order_review');
                                    endif;

                                    do_action('woocommerce_checkout_payment');
                                    ?>

                                    </div><!--order_review-->
                                    <?php do_action('woocommerce_checkout_after_order_review'); ?>
                                </div>
                                <?php

                                break;

                            //Payment step
                            case 'payment' :
                                if ($displayCheckoutForm === true) :
                                    echo $checkoutOpenFormHtml;
                                    $displayCheckoutForm = false;
                                endif;

                                if ($displayOrderReview == true) :
                                    do_action('woocommerce_checkout_before_order_review');
                                    echo $checkoutOpenOrderReviewHtml;

                                    $displayOrderReview = false;
                                endif;
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php do_action('woocommerce_checkout_payment'); ?>
                                </div>

                                </div><!--order_review-->
                                <?php
                                do_action('woocommerce_checkout_after_order_review');

                                break;

                            //Custom step
                            default:
                                ?>
                                <div class="argmc-form-steps argmc-form-step-<?php echo $i; ?><?php echo $i == 1 ? ' first current' : ''; ?><?php echo !empty($step['class']) ? ' ' . $step['class'] : ''?>">
                                    <?php do_action('arg-mc-checkout-step', $template); ?>
                                </div>
                                <?php
                        endswitch;
                        $i++;
                    endforeach;
                    ?>
                    </form><!--checkout argmc-form-->

            <?php
	endif;

	?>
	<div class="argmc-nav">
            <div class="argmc-nav-text"><?php echo $options['footer_text']; ?></div>
            <div class="argmc-nav-buttons">
                <button id="argmc-prev" class="button argmc-previous" type="button" style="display: none"><span><?php echo $options['btn_prev_text']; ?></span></button>
                <button id="argmc-next"<?php echo $showLogin && $firstStep == 'login' ? ' style="display:none" ' : ''; ?> class="button argmc-next" type="button"><span><?php echo $options['btn_next_text']; ?></span></button>
                <?php
                if ($showLogin) :
                    ?>
                    <button id="argmc-skip-login" class="button argmc-next" type="button"<?php echo $firstStep != 'login' ? 'style="display:none"' : ''; ?>><span><?php echo $options['btn_skip_login_text']; ?></span></button>
                    <?php
                endif;
                ?>
                <button id="argmc-submit" class="button argmc-submit" type="submit" style="display: none"><span><?php echo $options['btn_submit_text']; ?></span></button>
            </div>
	</div><!--argmc-nav-->

    </div><!--argmc-form-steps-wrapper-->


	<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
	<span class="tab-completed-icon preload-icon"></span>

<?php } ?>

</div><!--argmc-wrapper-->
