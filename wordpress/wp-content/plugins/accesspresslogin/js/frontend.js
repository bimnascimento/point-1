jQuery(document).ready(function($){
	$('.show-apsl-container').on('click', function(e){
        e.preventDefault();
        $('.apsl-container').slideToggle();
    });

    $('.apsl-link-account-button').click(function(){
    	$('.apsl-buttons-wrapper').hide();
    	$('.apsl-login-form').show();
        $('.apsl-registration-wrapper').addClass('apsl-login-registration-form');
    });

    $('.apsl-create-account-button').click(function(){
    	$('.apsl-buttons-wrapper').hide();
    	$('.apsl-registration-form').show();
        $('.apsl-registration-wrapper').addClass('apsl-login-registration-form');
    });

    $('.apsl-back-button').click(function(){
    	$('.apsl-buttons-wrapper').show();
    	$('.apsl-login-form').hide();
		$('.apsl-registration-form').hide();
        $('.apsl-registration-wrapper').removeClass('apsl-login-registration-form');
        


    });
});