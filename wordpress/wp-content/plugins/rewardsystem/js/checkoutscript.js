jQuery(function ($) {
    
    var wc_checkout_coupons = {
        init: function () {
            $(document.body).on('click', '.woocommerce-remove-coupon', this.remove_coupon);

      },
        remove_coupon: function (e) {
            e.preventDefault();
            $form = $('table.shop_table.cart').closest('form');
            var $datacoupon = $(e.target).data('coupon');
            //console.log($datacoupon);
            var data = {
                action: 'sumo_remove_coupon',
                coupon: $datacoupon,
            };
            $.ajax({
                url: checkoutscript_variable_js.wp_ajax_url,
                data: data,
                dataType: 'html',
                type: 'post',
                success: function (response) {
                   
                    common_syntax(response);

                }
            });

        }
    };


    var common_syntax = function ($node) {
      console.log($node);
        var $html = $.parseHTML($node, true);
        var $load_script = $($html).filter('div.sumo_reward_points_checkout_apply_discount');
        var $apply_discount1 = $('div.sumo_reward_points_checkout_apply_discount', $html).closest('form');
        console.log($load_script);
        $('div.woocommerce').prepend($apply_discount1);
    };

    wc_checkout_coupons.init();
});


