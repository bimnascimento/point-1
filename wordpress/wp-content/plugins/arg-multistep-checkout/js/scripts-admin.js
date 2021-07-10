(function($){
    "use strict";
	
    $('.color-field').wpColorPicker();

    ShowCheckoutStyleOptions($('.arg-checkout-option-button:checked'));

    $('.argmc-table-style').on('click', '.arg-checkout-option-button', function() {
        ShowCheckoutStyleOptions($(this));
    });

    function ShowCheckoutStyleOptions(elem) {
        if (elem.data('style') == 'theme') {
            $('.argmc-table-style').find('.checkout-form-opions').hide();
        } else {
            $('.argmc-table-style').find('.checkout-form-opions').show();
        }
    }
	
	
	ShowWizardButtonOptions($('.overwrite-wizard-buttons:checked'));

    $('.argmc-table-style').on('click', '.overwrite-wizard-buttons', function() {
        ShowWizardButtonOptions($(this));
    });

    function ShowWizardButtonOptions(elem) {
        if (elem.data('style') == 'overwrite-buttons-no') {
            $('.argmc-table-style').find('.wizard-overwrite-buttons-option').hide();
        } else {
            $('.argmc-table-style').find('.wizard-overwrite-buttons-option').show();
        }
    }
	
})(jQuery);