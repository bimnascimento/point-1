(function( $ ) {
	"use strict";

	$(function() {

		$('#theme-check > h2').html( $('#theme-check > h2').html() + ' with ReduxSA Theme-Check' );

		if ( typeof reduxsa_check_intro !== 'undefined' ) {
			$('#theme-check .theme-check').append( reduxsa_check_intro.text );
		}
		$('#theme-check form' ).append('&nbsp;&nbsp;<input name="reduxsa_wporg" type="checkbox">  Extra WP.org Requirements.');
	});

}(jQuery));
