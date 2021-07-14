/**
 * WC_OD_Datepicker script
 *
 * @author  WooThemes
 * @package WC_OD
 * @since   1.1.0
 */

/**
 * Datepicker function.
 *
 * Adds a layer over the Bootstrap Datepicker library. It makes the code more abstract an reusable.
 *
 * @param {jQuery} $ The jQuery instance.
 */
;(function( $, undefined ) {

	'use strict';

	// Bootstrap Datepicker no conflict.
	$.fn.bootstrapDP = $.fn.datepicker.noConflict();

	$.WC_OD_Datepicker = function( element, options ) {
		var defaults = {
			language: 'en',
			format: 'yyyy-mm-dd',
			weekStart: 0,
			startDate: null,
			endDate: null,
			daysOfWeekDisabled: [],
			datesDisabled: [],
			clearBtn: true,
			autoclose: true
		};

		$.data( element, 'wc_od_datepicker', this );

		this.options = $.extend( {}, defaults, options );
		this.element = $( element );

		this.init();
	};

	$.WC_OD_Datepicker.prototype = {

		init: function() {
			this.element.bootstrapDP( this.options );
		},

		option: function( name, value ) {
			var options, that = this;

			// Return all options.
			if ( 0 === arguments.length ) {
				return this.options;
			}

			if ( 1 === arguments.length && 'string' === typeof name ) {
				return this._getOption( name );
			}

			options = name || {};
			if ( 'string' === typeof name ) {
				options = {};
				options[ name ] = value;
			}

			$.each( options, function( name, value ) {
				that._setOption( name, value );
			});
		},

		update: function() {
			var datepicker = this.element.data( 'datepicker' );

			datepicker['update'].apply( datepicker, arguments );
		},

		_getOption: function( name ) {
			return this.options[ name ];
		},

		_setOption: function( name , value ) {
			var optionName = 'set' + name.charAt( 0 ).toUpperCase() + name.slice( 1 );

			this.element.bootstrapDP( optionName, value );
			this.options[ name ] = value;
		}
	};

	$.fn.wc_od_datepicker = function( options ) {
		var args, instance;

		// Verify an empty collection wasn't passed.
		if ( ! this.length ) {
			return this;
		}

		args = Array.prototype.slice.call( arguments, 1 );

		// Return the option(s) value.
		if ( 'option' === options && 2 > args.length  && ( 'string' === typeof args[1] || undefined === args[1] ) ) {
			instance = $( this ).data( 'wc_od_datepicker' );

			return instance[ options ].apply( instance, args );
		}

		return this.each(function() {
			var $this = $( this ),
			    instance = $this.data( 'wc_od_datepicker' );

			if ( ! instance ) {
				instance = new $.WC_OD_Datepicker( this, options );
				$this.data( 'wc_od_datepicker', instance );
			}

			if ( 'string' === typeof options && 'function' === typeof instance[ options ] ) {
				return instance[ options ].apply( instance, args );
			}
		});
	};
})( jQuery );