
(function( $ ) {

	//	Iframes need none of this.
	if ( window.top != window.self )
	{
		return;
	}

	//	The admin bar button
	var $btn = $();

	$(function() {
		$btn = $('#wp-admin-bar-wprefresh').children( 'a' );
		$btn.on(
			'click.wpr',
			function( e )
			{
				e.preventDefault();

				if ( WPR.running )
				{
					WPR.stop();
				}
				else
				{
					WPR.start();
				}

				$btn.blur();
			}
		);

		if ( $_GET[ 'wpr-reload' ] || WPR.autostart )
		{
			$(window).load(function() {
				WPR.start();
			});
		}
	});

	//	Put all get-arguments in an object, like PHP $_GET
	function get_get( s )
	{
		var g = {};

		if ( s )
		{
			s = s.slice( 1 ).split( '&' );
			for ( var i = 0; i < s.length; i++ )
			{
				var a = s[ i ].split( '=' );
				g[ a[ 0 ] ] = a[ 1 ];
			}
		}

		return g;
	}

	//	Strip the trailing slash for iframe
	function trim_href( href )
	{
		href = href.split( '/?' ).join( '?' );
		if ( href.slice( -1 ) == '/' )
		{
			href = href.slice( 0, -1 );
		}
		return href;
	}

	//	Add or increment the "wpr-reload" get-argument
	function replace_reload_argument( href, args )
	{
		href = trim_href( href );

		if ( args[ 'wpr-reload' ] )
		{
			href = href.split( 'wpr-reload=' + args[ 'wpr-reload' ] )
						.join( 'wpr-reload=' + ( parseInt( args[ 'wpr-reload' ], 10 ) + 1 ) );
		}
		else
		{
			if ( $.isEmptyObject( args ) )
			{
				href += '?';
			}
			else
			{
				href += '&';
			}
			href += 'wpr-reload=1';
		}
		return href;
	}

	//	get-arguments
	var $_GET = get_get( window.location.search );


	window.WPR = {

		running: false,
		calling: false,

		start: function()
		{

			WPR.running = true;
			WPR.openMobileWindow();


			//	Update admin bar button
			if ( $btn.length )
			{
				$btn.attr( 'title', $btn.attr( 'title' ).split( 'Enable' ).join( 'Disable' ) )
					.addClass( 'monitoring' );
			}


			//	Prevent double AJAX calls
			if ( WPR.calling )
			{
				return;
			}
			WPR.calling = true;


			setTimeout(
				function()
				{

					$.ajax({ 
						url		: WPR.scripturl,
						type	: "POST",
				        cache	: false,
				        data	: {
				        	themepath : WPR.themepath,
							filetypes : WPR.filetypes
				        }
					})
					.done(
						function( file )
						{
							WPR.calling = false;

							if ( WPR.running )
							{
								switch( file.split( '.' ).pop() )
								{
									case 'css':
										WPR.reloadCSS( file );
										WPR.start();
										break;

									case 'js':
									case 'php':
										WPR.reloadPage();
										break;

									default:
										WPR.start();
										break;
								}
							}
						}
					);

				}, 1000
			);
		},

		stop: function()
		{
			WPR.running = false;
			WPR.closeMobileWindow();


			//	Update admin bar button
			if ( $btn.length )
			{
				$btn.attr( 'title', $btn.attr( 'title' ).split( 'Disable' ).join( 'Enable' ) )
					.removeClass( 'monitoring' );
			}
		},

		openMobileWindow: function()
		{
			if ( !WPR.responsive )
			{
				return;
			}

			$('html').addClass( 'wpr-responsive-wrapper' );

			if ( !WPR.mobileWindow )
			{
				WPR.mobileWindow = $('<iframe id="wpr-mobile-window" src="' + trim_href( location.href ) + '" />').prependTo( 'body' );
			}
		},

		closeMobileWindow: function()
		{
			if ( !WPR.responsive )
			{
				return;
			}
			if ( WPR.mobileWindow )
			{
				$('html').removeClass( 'wpr-responsive-wrapper' );
				WPR.mobileWindow.remove();
				WPR.mobileWindow = false;
			}
		},

		reloadCSS: function( file )
		{
			file = file.split( '/wp-content/themes/' );
			if ( file.length == 1 )
			{
				return;
			}

			WPR._reloadCSS( file[ 1 ], $('html') )

			//	Reload mobile window
			if ( WPR.mobileWindow )
			{
				WPR._reloadCSS( file[ 1 ], WPR.mobileWindow.contents() )
			}
		},
		_reloadCSS: function( file, $wrpr )
		{
			//	Find the <link />
			var $link = $wrpr.find( 'link[href*="/wp-content/themes/' + file + '"]' );
			if ( $link.length == 0 )
			{
				return;
			}

			//	Add or increment the "wpr-reload" get-argument in the <link href />
			var href = $link.attr( 'href' );
			$link.attr( 'href', replace_reload_argument( href, get_get( '?' + href.split( '?' )[ 1 ] ) ) );
		},

		reloadPage: function()
		{
			//	Add or increment "wpr-reload" get-argument in the window
			window.location.href = replace_reload_argument( location.href, $_GET );
		}
	};

})( jQuery );