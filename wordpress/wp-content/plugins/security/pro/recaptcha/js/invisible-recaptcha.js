var itsecRecaptchaForm;

function itsecRecaptchaCallback( token ) {
	jQuery(document).off( 'submit', 'form', itsecRecaptchaHandleSubmit );

	jQuery('#g-recaptcha-response').val( token );

	jQuery(itsecRecaptchaForm).submit();
};

var itsecRecaptchaHandleSubmit = function( e ) {
	if ( 0 === jQuery( '#g-recaptcha-response', this ).length ) {
		// Only handle forms that have the reCAPTCHA modifications.
		return true;
	}

	if ( 0 !== jQuery( '.grecaptcha-user-facing-error' ).length && '' !== jQuery( '.grecaptcha-user-facing-error' ).first().html() ) {
		return;
	}

	e.preventDefault();

	itsecRecaptchaForm = this;

	grecaptcha.execute();
}

jQuery(document).on( 'submit', 'form', itsecRecaptchaHandleSubmit );
