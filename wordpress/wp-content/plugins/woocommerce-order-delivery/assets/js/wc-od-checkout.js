/**
 * WC_OD Checkout scripts
 *
 * @author  WooThemes
 * @package WC_OD
 * @since   1.0.0
 */

/**
 * Checkout function.
 *
 * @param {jQuery} $       The jQuery instance.
 * @param {Object} options The WC_OD checkout options.
 */
;(function( $, options ) {

	'use strict';

	var WC_OD = function( options ) {
		this.options = options;

		this.init();
	};

	WC_OD.prototype = {

		init: function() {
			this.$deliveryDate = null;

			// Bind events.
			this._bindEvents();
		},

		_bindEvents: function() {
			var that = this;

			// Update the calendar when the checkout form changes.
			$( 'body' ).on( 'updated_checkout', function() {
				that.updateDeliveryDateCalendar();
			});
		},

		updateDeliveryDateCalendar: function() {
			// Refresh the options.
			this.options = $.extend( {}, this.options, window.wc_od_checkout_l10n );

			if ( this.$deliveryDate ) {
				// Set the calendar options and refresh.
				this.$deliveryDate.wc_od_datepicker( 'option', this.options );
				this.$deliveryDate.wc_od_datepicker( 'update' );
			} else {
				// Create the calendar.
				this.$deliveryDate = $( '#delivery_date' ).wc_od_datepicker( this.options ).on( 'changeDate', function() {
					$( 'body' ).trigger( 'update_checkout' );
				});
			}
		}
	};

	$(function() {
		new WC_OD( options );
	});
})( jQuery, wc_od_checkout_l10n );