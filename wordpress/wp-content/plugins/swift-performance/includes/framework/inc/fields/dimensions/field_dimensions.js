
/*global jQuery, document, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.dimensions = reduxsa.field_objects.dimensions || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.dimensions.init();
        }
    );

    reduxsa.field_objects.dimensions.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( '.reduxsa-container-dimensions:visible' );
        }
        $( selector ).each(
            function() {
                var el = $( this );
                var parent = el;
                if ( !el.hasClass( 'reduxsa-field-container' ) ) {
                    parent = el.parents( '.reduxsa-field-container:first' );
                }
                if ( parent.is( ":hidden" ) ) { // Skip hidden fields
                    return;
                }
                if ( parent.hasClass( 'reduxsa-field-init' ) ) {
                    parent.removeClass( 'reduxsa-field-init' );
                } else {
                    return;
                }
                var default_params = {
                    width: 'resolve',
                    triggerChange: true,
                    allowClear: true
                };

                var select2_handle = el.find( '.select2_params' );
                if ( select2_handle.size() > 0 ) {
                    var select2_params = select2_handle.val();

                    select2_params = JSON.parse( select2_params );
                    default_params = $.extend( {}, default_params, select2_params );
                }

                el.find( ".reduxsa-dimensions-units" ).select2( default_params );

                el.find( '.reduxsa-dimensions-input' ).on(
                    'change', function() {
                        var units = $( this ).parents( '.reduxsa-field:first' ).find( '.field-units' ).val();
                        if ( $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-dimensions-units' ).length !== 0 ) {
                            units = $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-dimensions-units option:selected' ).val();
                        }
                        if ( typeof units !== 'undefined' ) {
                            el.find( '#' + $( this ).attr( 'rel' ) ).val( $( this ).val() + units );
                        } else {
                            el.find( '#' + $( this ).attr( 'rel' ) ).val( $( this ).val() );
                        }
                    }
                );

                el.find( '.reduxsa-dimensions-units' ).on(
                    'change', function() {
                        $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-dimensions-input' ).change();
                    }
                );
            }
        );


    };
})( jQuery );