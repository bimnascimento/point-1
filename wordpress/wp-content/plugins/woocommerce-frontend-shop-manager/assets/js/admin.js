(function($){

	"use strict";

	function wfsm_add_vendor_group(curr_group) {

		var curr_data = {
			action: 'wfsm_add_vendor_group',
			wfsm_group: curr_group
		}

		return $.post(wfsm.ajax, curr_data, function(response) {
			if (response) {

			}
			else {
				alert('Error!');
			}

		});

	}

	$('#wc_settings_wfsm_default_permissions').selectize({
		plugins: ['remove_button'],
		delimiter: ',',
		persist: false
	});

	$('.wfsm-vendor-group-users, .wfsm-vendor-user-permissions').each( function() {

		var curr = $(this);

		var curr_selected = $.parseJSON( curr.parent().attr('data-selected') );

		var curr_sel = curr.attr('multiple', 'multiple').selectize({
			items: [curr_selected],
			plugins: ['remove_button'],
			delimiter: ',',
			persist: false
		});

		curr_sel[0].selectize.setValue( curr_selected );

	});

	$(document).on( 'click', '#wfsm-add-vendor-group', function() {

		var curr = $(this);

		var curr_group = curr.parent().find('.wfsm-vendor-group').length;
	
		$.when( wfsm_add_vendor_group(curr_group) ).done( function(response) {

			response = $(response);
			response.find('select').each( function() {
				var curr_sel = $(this).attr('multiple', 'multiple').selectize({
					plugins: ['remove_button'],
					delimiter: ',',
					persist: false
				});
				curr_sel[0].selectize.clear();
			});

			curr.before(response);

		});
	
		return false;

	});

	$(document).on('click', '.wfsm-vendor-group-ui', function() {

		if ( !confirm( wfsm.localization.delete_group ) ) {
			return false;
		}

		$(this).parent().remove();

		return false;

	});


})(jQuery);