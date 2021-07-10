<?php
global $wpie_init, $wpie_product, $wpie_user, $wpie_order, $wpie_coupon, $wpie_product_category;

$product_count = $wpie_product->wpie_get_product_count();

$product_category_count = $wpie_product_category->wpie_get_product_category_count();

$order_count = count($wpie_order->get_order_list());

$user_count = $wpie_user->wpie_get_user_count();

$coupon_count = $wpie_coupon->wpie_get_coupon_count();

$plugin_all_data = get_option('wpie_current_site_plugin_date_format', 1);


if ($plugin_all_data == "" || $plugin_all_data == 1) {
    $unlock_style = 'display:none;';
    $lock_style = '';
} else {
    $unlock_style = '';
    $lock_style = 'display:none;';
}
?>
<div class="wpie_success_msg" wpie_wait_msg="<?php _e('Please Wait...', WPIE_TEXTDOMAIN) ?>"><?php _e('Please Wait...', WPIE_TEXTDOMAIN) ?></div>
<div class="wpie-dashboard-page-wrapper">
    <div class="container-fluid offset-10">
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <a class="wpie_dashboard_links" href="<?php echo admin_url('admin.php?page=wpie-products'); ?>"> 
                <div class="wpie-dashboard-section-header wpie-red-section">
                    <div class="wpie-header-title-count"><?php echo $product_count; ?></div>
                    <div class="wpie-header-title-content"><?php _e('Products', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-subtitle"><?php _e('Available product in store.', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-image wpie-product-image"></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <a class="wpie_dashboard_links" href="<?php echo admin_url('admin.php?page=wpie-product-categories'); ?>"> 
                <div class="wpie-dashboard-section-header wpie-green-section">
                    <div class="wpie-header-title-count"><?php echo $product_category_count; ?></div>
                    <div class="wpie-header-title-content"><?php _e('Categories', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-subtitle"><?php _e('Available Categories for products', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-image wpie-category-image"></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <a class="wpie_dashboard_links" href="<?php echo admin_url('admin.php?page=wpie-orders'); ?>"> 
                <div class="wpie-dashboard-section-header wpie-aqua-section">
                    <div class="wpie-header-title-count"><?php echo $order_count; ?></div>
                    <div class="wpie-header-title-content"><?php _e('Orders', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-subtitle"><?php _e('Available orders in store', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-image wpie-order-image"></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <a class="wpie_dashboard_links" href="<?php echo admin_url('admin.php?page=wpie-users'); ?>"> 
                <div class="wpie-dashboard-section-header wpie-blue-section">
                    <div class="wpie-header-title-count"><?php echo $user_count; ?></div>
                    <div class="wpie-header-title-content"><?php _e('Users', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-subtitle"><?php _e('Available users in store', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-image wpie-user-image"></div>
                </div>
            </a>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
            <a class="wpie_dashboard_links" href="<?php echo admin_url('admin.php?page=wpie-coupons'); ?>"> 
                <div class="wpie-dashboard-section-header wpie-color-section1">
                    <div class="wpie-header-title-count"><?php echo $coupon_count; ?></div>
                    <div class="wpie-header-title-content"><?php _e('Coupons', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-subtitle"><?php _e('Available coupons in store', WPIE_TEXTDOMAIN); ?></div>
                    <div class="wpie-header-title-image wpie-coupon-image"></div>
                </div>
            </a>
        </div>
    </div>
    <div class="wpie_license_wrapper" style="<?php echo $lock_style; ?>">
        <div class="wpie_license_ativate_wrapper">
            <div class="wpie_license_active_title"><?php _e("You're almost finished!", WPIE_TEXTDOMAIN); ?></div>
            <form class="wpie_license_activation_frm" method="post">
                <div class="wpie_license_activate_notice"><?php _e('Please notice that purchase code is required to enable automatic updates notification and download.', WPIE_TEXTDOMAIN); ?></div>
                <input class="wpie_license_code" placeholder="<?php _e('Enter Code - Hit ENTER', WPIE_TEXTDOMAIN); ?>" type="text">
            </form>
        </div>
    </div>
    <div class="wpie_deactivate_license_wrapper" style="<?php echo $unlock_style; ?>">
        <div class="wpie_license_ativate_wrapper">
            <div class="wpie_deactivate_loader"></div>
            <div class="wpie_license_active_title"><?php _e("You're Done! Enjoy", WPIE_TEXTDOMAIN); ?></div>
            <form class="wpie_license_deactivation_frm">
                <button class="wpie-general-btn wpie_license_deativate"><?php _e("Deactivate License", WPIE_TEXTDOMAIN); ?></button>
            </form>
        </div>
    </div>
</div>
<div class="wpie-documantation-links-wrapper">
    <div class="wpie-documantation-links-outer">
        <a class="wpie-documantation-links" target="_blank" href="<?php echo "http://www.vjinfotech.com/products/woo-imp-exp/documentation/"; ?>"><?php _e('Documentation', WPIE_TEXTDOMAIN); ?></a> |  <a class="wpie-documantation-links" target="_blank" href="http://www.vjinfotech.com/support"><?php _e('Support', WPIE_TEXTDOMAIN); ?></a>
    </div>
</div>