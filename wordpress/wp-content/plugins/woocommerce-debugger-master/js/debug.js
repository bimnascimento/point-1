jQuery(document).ready(function($) {

	jQuery(function(){

		$('.wc_template_path_hint').each( function(){

			var $this    = $(this);
			var $el      = $this.next();

			if ( $el.size() > 0 ) {

				if ( $el.is(':visible') ) {
					var position = $el.position();

					$this.width( $el.width() );
					$this.height( $el.height() );
					$this.css( 'left', position.left );
					$this.css( 'top', position.top );
				} else {
					$el.addClass('template_hint_hidden');
					$this.hide();
				}
			}
		});

		$(document).click(function() {

			$('.wc_template_path_hint', $(this).parent() ).each( function(){

				var $this    = $(this);
				var $el      = $this.next();

				if ( $el.size() > 0 ) {

					if ( $el.is(':visible') ) {
						var position = $el.position();

						$this.width( $el.width() );
						$this.height( $el.height() );
						$this.css( 'left', position.left );
						$this.css( 'top', position.top );

						$el.removeClass('template_hint_hidden');
						$this.show();
					} else {
						$el.addClass('template_hint_hidden');
						$this.hide();
					}
				}
			});

		});

		$('.wc_template_path_hint').click(function(){
			$(this).remove();
			$('#tiptip_holder').hide();
		});

	});

	$(".wc_template_file_name").tipTip({
    	'attribute' : 'data-tip',
    	'fadeIn' : 50,
    	'fadeOut' : 50,
    	'delay' : 200,
    	'maxWidth' : ""
    });

    $('a.disable_plugins').live( 'click', function() {
	    var answer = confirm( wc_debug_params.i18n_disable )
		if ( answer ) {
			return true;
		}
	    return false;
    } );

    $('a.debug_report').live( 'click', function() {

    	$link = $(this);

	    var report = 'data:text/plain;charset=utf-8,';

	    $('#wc_debugger').block( {message: null, opacity: 0.6 } );

	    $.ajax({
	    	async: false,
	    	url: wc_debug_params.report_url,
	    	success: function(data) {

		    	$('body').append('<div id="wc_debug_report"><table>' + $(data).find('.wc_status_table').html() + '</table></div>');

				jQuery('thead:not(".tools"), tbody:not(".tools")', '#wc_debug_report').each(function(){

					$this = jQuery( this );

					if ( $this.is('thead') ) {

						report = report + "\n=============================================================================================\n";
						report = report + " " + jQuery.trim( $this.text() ) + "\n";
						report = report + "=============================================================================================\n";

					} else {

						jQuery('tr', $this).each(function(){

							$this = jQuery( this );

							report = report + $this.find('td:eq(0)').text() + ": \t";
							report = report + $this.find('td:eq(1)').text() + "\n";

						});

					}

				});

				$('#wc_debugger').unblock();
				$('#wc_debug_report').remove();
				$link.attr( 'href', encodeURI( report ) );
			}
		});
		return true;
    } );
});