(function($){


	"use strict";

	var wfsm_ajax = 'notactive';

	$.WfsmUrlParameter = function( parameter, url ){
		var results = new RegExp('[\?&amp;]' + parameter + '=([^&amp;#]*)').exec(url);
		return results[1] || 0;
	}

	function getVals(formControl, controlType) {

		switch (controlType) {
			case 'text':
				var value = $(formControl).val();
			break;
			case 'number':
				var value = $(formControl).val();
			break;
			case 'textarea':
				var value = $(formControl).val();
			break;
			case 'radio':
				var value = $(formControl).val();
			break;
			case 'checkbox':
				if ($(formControl).is(":checked")) {
					value = 'yes';
				}
				else {
					value = 'no';
				}
			break;
			case 'select':
				var value = $(formControl).val();
			break;
			case 'multiselect':
				var value = $(formControl).val() || [];
			break;
			default:
				var value = $(formControl).val();
			break;
		}
		return value;
	}

	$('body').on('keyup change', 'input[name^="wfsm-regular-price"], input[name^="wfsm-sale-price"], input[name^="wfsm-weight"], input[name^="wfsm-length"], input[name^="wfsm-width"], input[name^="wfsm-height"]', function(){
		var value = $(this).val();
		var regex = new RegExp( "[^\-0-9\%.\\" + wfsm.decimal_separator + "]+", "gi" );
		var newvalue = value.replace( regex, '' );

		if ( value !== newvalue ) {
			$(this).val( newvalue );
			alert(wfsm.localization.errors.decimal);
		}
		return this;
	});


	$(document).on( 'click', '.wfsm-add-variation', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var curr = $(this);
		var el = $('.wfsm-buttons.wfsm-active');

		curr.addClass('wfsm-ajax-loading');

		var curr_data = {
			action: 'wfsm_add_variation_respond',
			wfsm_id: el.attr('data-id'),
			wfsm_order: curr.parent().find('.wfsm-variation').length
		}

		$.post(wfsm.ajax, curr_data, function(response) {
			if (response) {

				response = $(response);

				var curr_date = new Date();
				var curr_dates = response.find('input[id^="wfsm-schedule-sale-start-"], [id^="wfsm-schedule-sale-end-"]').datepicker( {
					dateFormat: 'yy/mm/dd',
					defaultDate: "+1w",
					minDate: curr_date,
					onSelect: function(curr_selected) {
						var option = this.id == /start/i.test(this) ? "minDate" : "maxDate",
						instance = $(this).data("datepicker"),
						date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, curr_selected, instance.settings);
						curr_dates.not(this).datepicker("option", option, date);
					}
				} );

				curr.before(response);
				curr.removeClass('wfsm-ajax-loading');
				wfsm_ajax = 'notactive';

			}
			else {
				alert('Error!');
				curr.removeClass('wfsm-ajax-loading');
				wfsm_ajax = 'notactive';
			}
		});

		return false;

	});

	$(document).on( 'click', '.wfsm-buttons .wfsm-button.wfsm-add-product', function() {

		var el = $(this).parent();

		if ( el.hasClass('wfsm-active') ) {
			return false;
		}
		else {

			if ( $('body').hasClass('wfsm-active') ) {
				$('body').removeClass('wfsm-active').find('#wfsm-overlay').remove();
				$('.wfsm-buttons.wfsm-active').removeClass('wfsm-active');
				$('.wfsm-quick-editor').remove();
			}

			el.addClass('wfsm-active');

		}

		return false;

	});

	$(document).on( 'click', '.wfsm-product-type', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var curr = $(this);
		var el =curr.closest('.wfsm-buttons');

		if ( $('body').hasClass('wfsm-active') ) {
			$('.wfsm-buttons.wfsm-active').removeClass('wfsm-active');
			$('.wfsm-quick-editor').remove();
		}
		else {
			$('body').addClass('wfsm-active').append('<div id="wfsm-overlay"></div>');
		}

		var curr_data = {
			action: 'wfsm_add_product_respond',
			wfsm_type: curr.attr('data-type')
		}

		$.post(wfsm.ajax, curr_data, function(response) {

			if ( response && response != 0 ) {

				el.attr('data-id', response);
				el.attr('data-loop', 'new');

				var curr_edit = el.find('.wfsm-edit');
				curr_edit.attr( 'href', curr_edit.attr('href') + response );

				var new_data = {
					action: 'wfsm_respond',
					wfsm_id: response,
					wfsm_loop: 'new'
				}

				$.post(wfsm.ajax, new_data, function(new_response) {
					if ( new_response && new_response != 0 ) {

						$('body').append(new_response);

					}
					else {
						alert('Error!');
					}
				});
				wfsm_ajax = 'notactive';
			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}
		});

		return false;

	});


	$(document).on( 'click', '.wfsm-button.wfsm-activate', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var el = $(this).parent();

		if ( el.hasClass('wfsm-active') ) {
			return false;
		}
		else {

			if ( $('body').hasClass('wfsm-active') ) {
				$('#wfsm-overlay').removeClass('wfsm-active');
				$('.wfsm-buttons.wfsm-active').removeClass('wfsm-active');
				$('.wfsm-quick-editor').remove();
			}
			else {
				$('body').addClass('wfsm-active').append('<div id="wfsm-overlay"></div>');
			}

			el.addClass('wfsm-active');

			var curr_data = {
				action: 'wfsm_respond',
				wfsm_id: el.attr('data-id')
			}

			$.post(wfsm.ajax, curr_data, function(response) {
				if (response) {

					$('body').append(response);
					$('#wfsm-overlay').addClass('wfsm-active');
					wfsm_ajax = 'notactive';

				}
				else {
					alert('Error!');
					wfsm_ajax = 'notactive';
				}
			});

		}

		return false;

	});

	$(document).on( 'click', '.wfsm-button.wfsm-save', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var el = $(this).parent();

		wfsm_save( el.attr('data-id'), el.attr('data-loop'), el );

		if ( el.hasClass('wfsm-side-buttons') ) {
			location.reload();
		}

		return false;

	});

	$(document).on( 'click', '.wfsm-button.wfsm-discard', function() {

		var el = $(this).parent();

		if ( el.hasClass('wfsm-active') ) {
			$('.wfsm-quick-editor').remove();
			el.removeClass('wfsm-active');
			$('body').removeClass('wfsm-active').find('#wfsm-overlay').remove();
		}

		if ( el.hasClass('wfsm-side-buttons') ) {
			location.reload();
		}

		return false;

	});

	$(document).on( 'click', '.wfsm-button.wfsm-clone', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		if ( !confirm( wfsm.localization.clone.question ) ) {
			wfsm_ajax = 'notactive';
			return false;
		}

		var el = $(this).parent();

		wfsm_clone( el.attr('data-id') );

		return false;

	});

	$(document).on( 'click', '.wfsm-button.wfsm-trash', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		if ( !confirm( wfsm.localization.trash.simple ) ) {
			wfsm_ajax = 'notactive';
			return false;
		}

		var el = $(this).parent();

		wfsm_trash( el.attr('data-id'), 'single' );

		return false;

	});

	$(document).on( 'click', '.wfsm-trash-variation', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';


		if ( !confirm( wfsm.localization.trash.variation ) ) {
			wfsm_ajax = 'notactive';
			return false;
		}

		var el = $(this).parent().next();

		wfsm_trash( el.attr('data-id'), 'variation' );

		el.prev().remove();
		el.remove();

		return false;

	});

	$(document).on( 'click', '.wfsm-screen > div > .wfsm-editor-button, .wfsm-variations > div > .wfsm-editor-button', function() {

		var el = $(this).prev();

		if ( el.hasClass('wfsm-hidden') ) {
			$(this).addClass('wfsm-active');
			el.removeClass('wfsm-hidden').addClass('wfsm-visible');
		}
		else {
			el.removeClass('wfsm-visible').addClass('wfsm-hidden').find('input').val('');
			$(this).removeClass('wfsm-active');
		}

		return false;
	});

	$(document).on( 'click', '.wfsm-label-checkbox', function() {

		var curr_selected = $(this).filter(':visible').attr('class');
		curr_selected = curr_selected.substr(10, curr_selected.length);

		var curr_tobe = $(this).find('span').not(':visible').attr('class');
		curr_tobe = curr_tobe.substr(10, curr_tobe.length);

		$(this).removeClass('wfsm-'+curr_selected);
		$(this).addClass('wfsm-'+curr_tobe);
		$(this).find('input').val(curr_tobe).change();

		if ( $(this).attr('data-linked') != '' ) {
			var curr_editor = $(this).closest('.wfsm-quick-editor');
			curr_editor.find('span.wfsm-headline.wfsm-headline-'+$(this).attr('data-linked')).toggleClass('wfsm-group-notvisible').toggleClass('wfsm-group-visible');
		}
		if ( $(this).attr('data-variable-linked') != '' ) {
			var curr_editor = $(this).closest('.wfsm-variation');
			curr_editor.find('.wfsm-group-variable-'+$(this).attr('data-variable-linked')).toggleClass('wfsm-group-notvisible').toggleClass('wfsm-group-visible');
		}

	});

	$(document).on( 'click', '.wfsm-headline', function() {

		var curr = $(this);

		var curr_selected = $(this).next();

		curr_selected.slideToggle( 60, function() {
			curr.toggleClass('wfsm-active');
		});

	});

	$(document).on( 'click', '.wfsm-controls .wfsm-expand', function() {

		var curr = $('.wfsm-quick-editor .wfsm-headline:not(.wfsm-active)');

		curr.each( function() {
			var curr_selected = $(this).next();

			curr_selected.slideDown( 60, function() {
				curr.addClass('wfsm-active');
			});

		});

		return false;

	});

	$(document).on( 'click', '.wfsm-controls .wfsm-contract', function() {

		var curr = $('.wfsm-quick-editor .wfsm-headline.wfsm-active');

		curr.each( function() {
			var curr_selected = $(this).next();

			curr_selected.slideUp( 60, function() {
				curr.removeClass('wfsm-active');
			});

		});

		return false;

	});

	$(document).on( 'click', '.wfsm-controls .wfsm-side-edit', function() {

		window.location.href = $('.wfsm-buttons.wfsm-active .wfsm-edit').attr('href');

		return false;

	});

	$(document).on( 'click', '.wfsm-controls .wfsm-side-save, .wfsm-editing', function() {

		$('.wfsm-buttons.wfsm-active .wfsm-save').click();

		return false;

	});

	$(document).on( 'click', '.wfsm-controls .wfsm-side-discard', function() {

		$('.wfsm-buttons.wfsm-active .wfsm-discard').click();

		return false;

	});

	$(document).on( 'click', '.wfsm-refresh-attributes', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var curr = $(this);
		var el = $('.wfsm-buttons.wfsm-active');

		curr.addClass('wfsm-ajax-loading');

		$.when( wfsm_save( el.attr('data-id'), 'update', 'update' ) ).done( function(response) {

			var curr_length = $('.wfsm-variations .wfsm-variation').length;

			if ( curr_length > 0 ) {

				var i = 0;
				var par = $('.wfsm-variations');
				var add_button = par.find('.wfsm-add-variation');
				var variations = {};

				par.find('.wfsm-variation').each( function() {

					var curr_el = $(this);

					var curr_data = {
						action: 'wfsm_add_variation_respond',
						wfsm_id: curr_el.attr('data-id'),
						wfsm_mode: 'get'
					}



					$.post(wfsm.ajax, curr_data, function(response) {
						if (response) {
							i++;
							variations[curr_el.attr('data-id')] = $(response);

							curr_el.prev().remove();
							curr_el.remove();

							if ( curr_length == i ) {
								$.each( variations, function(index, value) {
									add_button.before(value)

								});
								curr.removeClass('wfsm-ajax-loading');
							}

						}
						else {
							alert('Error!');
						}
					});

				});
				

			}
			else {
				curr.removeClass('wfsm-ajax-loading');
			}

		});

		return false;

	});

	$(document).on( 'click', '.wfsm-edit-content, .wfsm-edit-desc', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var el_mode = $(this).hasClass('wfsm-edit-content') ? 'content' : 'description' ;

		var el = $('.wfsm-buttons.wfsm-active');

		$('body').addClass('wfsm-editor-active').append('<div id="wfsm-editor-overlay"></div>');

		$.when( wfsm_editor( el.attr('data-id'), el_mode ) ).done( function(response) {

			response = $(response);

			$('#wfsm-editor-overlay').append(response);

		});

		return false;

	});

	$(document).on( 'click', '.wfsm-editor-discard', function() {

		$('body').removeClass('wfsm-editor-active');
		$('#wfsm-editor-overlay').remove();

		return false;

	});

	$(document).on( 'click', '.wfsm-editor-save', function() {
		if ( wfsm_ajax == 'active' ) {
			return false;
		}
		wfsm_ajax = 'active';

		var el = $(this).parent();

		if ( $('#wp-wfsm-tinymce-wrap').hasClass('tmce-active') ){
			var curr_content = tinyMCE.get( 'wfsm-tinymce' ).getContent();
		}else{
			var curr_content = $('#wfsm-tinymce').val();
		}

		$.when( wfsm_editor_save( el.attr('data-id'), el.attr('data-mode'), curr_content ) ).done( function(response) {

			$('body').removeClass('wfsm-editor-active');
			$('#wfsm-editor-overlay').remove();

		});

		return false;

	});

	$(document).on( 'click', '.wfsm-add-file', function() {

		var el = $(this).prev();

		var curr_id = '';
		if ( el.closest('.wfsm-variation').length > 0 ) {
			var curr_id = '-'+el.closest('.wfsm-variation').attr('data-id');
		}

		var html = '<div class="wfsm-downloads-file"><a href="#" class="wfsm-downloads-move"><i class="wfsmico-move"></i></a><span class="wfsm-downloads-file-name"><input type="text" placeholder="'+wfsm.localization.downloads.file_name+'" name="wfsm-file-names'+curr_id+'[]" value="" class="wfsm-collect-data" /></span><span class="wfsm-downloads-file-url"><input type="text" placeholder="http://" name="wfsm-file-urls'+curr_id+'[]" value="" class="wfsm-collect-data" /></span><a href="#" class="wfsm-downloads-file-choose" data-choose="'+wfsm.localization.downloads.choose_file_ui+'" data-update="Insert file URL">'+wfsm.localization.downloads.choose_file+'</a><a href="#" class="wfsm-downloads-file-discard"><i class="wfsmico-discard"></i></a></div>';

		el.append(html);

		return false;
	});

	$(document).on( 'click', '.wfsm-downloads-file-discard', function() {

		var el = $(this).parent();

		if ( !confirm( wfsm.localization.downloads.discard ) ) {
			return false;
		}

		el.remove();

		return false;
	});


// Ajax Calls


	function wfsm_save( el_curr_id, el_curr_loop, el ) {
		var curr_saving = {};
		var opts = $('.wfsm-quick-editor');

		var inputs = $('.wfsm-quick-editor input.wfsm-collect-data, .wfsm-quick-editor select.wfsm-collect-data, .wfsm-quick-editor textarea.wfsm-collect-data'), tmp;
		$.each(inputs, function(i, obj) {

			var tag = ( $(obj).prop('tagName') == 'INPUT' ? $(obj).attr('type') : $(obj).prop('tagName').toLowerCase() );
			var curr_name = $(obj).attr('name').replace('[]', '');

			if ( curr_name.substring(0, 10) == 'wfsm-file-' ) {
				if ( typeof curr_saving[curr_name] != 'undefined' ) {
					curr_saving[curr_name].push(getVals($(obj), tag));
				}
				else {
					curr_saving[curr_name] = [];
					curr_saving[curr_name].push(getVals($(obj), tag));
				}
			}
			else {
				curr_saving[curr_name] = getVals($(obj), tag);
			}
			

		});

		curr_saving['wfsm-manage-stock-quantity'] = ( $('.wfsm-editor-button.wfsm-manage-stock-quantity').hasClass('wfsm-active') ? 'yes' : 'no' );

		if ( $('.wfsm-variation').length > 0 ) {
			var curr_ids = [];
			$('.wfsm-variation').each( function() {
				var curr_id = $(this).attr('data-id');
				curr_saving['wfsm-manage-stock-quantity-'+curr_id] = ( $('.wfsm-editor-button.wfsm-manage-stock-quantity-'+curr_id).hasClass('wfsm-active') ? 'yes' : 'no' );
				curr_ids.push(curr_id);
			});
			curr_saving['wfsm-variations-ids'] = curr_ids;
		}

		var curr_data = {
			action: 'wfsm_save',
			wfsm_id: el_curr_id,
			wfsm_save: JSON.stringify(curr_saving),
			wfsm_loop: el_curr_loop
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {

				if ( typeof el === 'object' ) {

					response = $(response);

					if ( el.hasClass('wfsm-active') ) {
						$('.wfsm-quick-editor').remove();
						el.removeClass('wfsm-active');
						$('body').removeClass('wfsm-active').find('#wfsm-overlay').remove();
					}

					if ( curr_data.wfsm_loop !== 'single' && curr_data.wfsm_loop !== 'new' ) {

						el.closest('.type-product').replaceWith(response);
						response.addClass('product');

					}
					else {
						location.reload();
					}

				}
				wfsm_ajax = 'notactive';
			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}

		});

	}

	function wfsm_clone( el_curr_id ) {

		var curr_data = {
			action: 'wfsm_clone',
			wfsm_id: el_curr_id
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {

				alert( wfsm.localization.clone.confirmed );
				location.reload();

			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}

		});

	}

	function wfsm_trash( el_curr_id, el_curr_mode ) {

		var curr_data = {
			action: 'wfsm_trash',
			wfsm_id: el_curr_id,
			wfsm_mode: el_curr_mode
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {

				if ( el_curr_mode != 'variation' ) {
					location.reload();
				}
				wfsm_ajax = 'notactive';

			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}

		});

	}

	function wfsm_editor( el_curr_id, el_curr_mode ) {

		var curr_data = {
			action: 'wfsm_editor',
			wfsm_id: el_curr_id,
			wfsm_mode: el_curr_mode
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {
				wfsm_ajax = 'notactive';
			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}

		});

	}

	function wfsm_editor_save( el_curr_id, el_curr_mode, content ) {

		var curr_data = {
			action: 'wfsm_editor_save',
			wfsm_id: el_curr_id,
			wfsm_mode: el_curr_mode,
			wfsm_content: content
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {
				wfsm_ajax = 'notactive';
			}
			else {
				alert('Error!');
				wfsm_ajax = 'notactive';
			}

		});

	}

})(jQuery);