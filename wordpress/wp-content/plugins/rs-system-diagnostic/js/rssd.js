(function($) {
	$( document ).ready( function() {
		/**
		 * Generate new Remote View URL
		 * and display it on the admin page
		 */
		$( 'input[name="generate-new-url"]' ).on( 'click', function( event ) {
			event.preventDefault();
			$.ajax({
				type : 'post',
				dataType : 'json',
				url : systemDiagnosticAjax.ajaxurl,
				data : { action : 'rssd_regenerate_url' },
				success : function( response ) {
					$( '.rssd-url-text' ).val( response );
					$( '.rssd-url-text-link' ).attr( 'href', response );
				},
				error : function( j, t, e ) {
					console.log( "RS System Diagnostic Error: " + j.responseText );
				}
			});
		});
	});
})(jQuery);
