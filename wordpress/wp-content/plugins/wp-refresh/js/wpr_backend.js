
(function( $ ) {

	$(function() {
		var $s = $('#wpr_options_activate'),
			$t = $s.closest( 'tr' );

		$s.on(
			'change.msg',
			function( e )
			{
				$t[ $s.val() == 'always' ? 'addClass' : 'removeClass' ]( 'msg warning' );
			}
		)
		.trigger( 'change.msg' );
	});

	$(function() {
		var $s = $('#wpr_options_responsive'),
			$t = $s.closest( 'tr' );

		$s.on(
			'change.msg',
			function( e )
			{
				$t[ $s.val() == 'yes' ? 'addClass' : 'removeClass' ]( 'msg message' );
			}
		)
		.trigger( 'change.msg' );
	});

})( jQuery );