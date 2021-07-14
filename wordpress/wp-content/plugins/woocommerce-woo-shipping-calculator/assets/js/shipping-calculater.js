(function ($) {
    $(document).ready(function () {

        //jQuery(".rp_shiiping_form").hide();

        //$(document).on("change","#calc_shipping_method",function(){
            //$('.rp_calc_shipping').trigger('click');
        //});
        jQuery(".rp_shiiping_form").slideToggle();
        $(".btn_shipping_postcode").click(function () {
            /*
            if($(".rp_shiiping_form").css("display")=="none"){
                jQuery(".rp_shiiping_form").slideToggle('slow');
            }else{
                jQuery(".rp_shiiping_form").slideToggle('hide');
            }
            //$(".rp_shipping_button").toggle("hide");
            */
            jQuery(".rp_shiiping_form").slideToggle();
        });
        /*
        $(".single_variation_wrap").on("show_variation", function (event, variation) {
            $(".loaderimage").show();
            element=$('.country_to_state,.shipping_state select');
            var datastring = element.closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: rp_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });
        });
        */
        /*
        $('.rp_calc_shipping').click(function () {
            $(".loaderimage").show();
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: rp_ajax_url+"?action=ajax_calc_shipping",
                data: datastring,
                dataType: 'json',
                success: function (data) {
                    $(".loaderimage").hide();
                    $(".rp_message").removeClass("rp_error").removeClass("rp_success");
                    if (data.code == "error") {
                        $(".rp_message").html(data.message).addClass("rp_error");
                    } else if (data.code == "success") {
                        $(".rp_message").html(data.message).addClass("rp_success");
                    } else {
                        return true;
                    }
                }
            });
            return false;
        });
        */
        /*
        $('.country_to_state,.shipping_state select').change(function () {
            $(".loaderimage").show();
            element=$(this);
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: rp_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });

        });
        */
        /*$('.shipping_postcode input,.shipping_state input').blur(function () {
            $(".loaderimage").show();
            element=$(this);
            var datastring = $(this).closest(".woocommerce-shipping-calculator").serialize();
            if($("input.variation_id").length>0){
                datastring=datastring+"&variation_id="+$("input.variation_id").val();
            }
            if($("input[name=quantity]").length>0){
                datastring=datastring+"&current_qty="+$("input[name=quantity]").val();
            }
            $.ajax({
                type: "POST",
                url: rp_ajax_url+"?action=update_shipping_method",
                data: datastring,
                success: function (data) {
                    $(".loaderimage").hide();
                    element.parent().parent().find('.shippingmethod_container').html(data);
                }
            });
            return false;
        });*/
    });
})(jQuery);
