jQuery(document).ready(function() {
    jQuery('#bis_error_msg_effect').change(function() {
        if (jQuery(this).val() == 'fadein') {
            jQuery('#instock_error_fadein_time').parent().parent().css('display', 'table-row');
        } else {
            jQuery('#instock_error_fadein_time').parent().parent().css('display', 'none');
        }
    });
    jQuery('#bis_success_msg_effect').change(function() {
        if (jQuery(this).val() == 'fadein') {
            jQuery('#instock_sucess_fadein_time').parent().parent().css('display', 'table-row');
        } else {
            jQuery('#instock_sucess_fadein_time').parent().parent().css('display', 'none');
        }
    });

    //on start
    if (jQuery('#bis_error_msg_effect').val() == 'fadein') {
        jQuery('#instock_error_fadein_time').parent().parent().css('display', 'table-row');
    } else {
        jQuery('#instock_error_fadein_time').parent().parent().css('display', 'none');
    }

    if (jQuery('#bis_success_msg_effect').val() == 'fadein') {
        jQuery('#instock_sucess_fadein_time').parent().parent().css('display', 'table-row');
    } else {
        jQuery('#instock_sucess_fadein_time').parent().parent().css('display', 'none');
    }

});