/*global reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.spacing = reduxsa.field_objects.spacing || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.spacing.init();
        }
    );

    reduxsa.field_objects.spacing.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-spacing:visible' );
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

                el.find( ".reduxsa-spacing-units" ).select2( default_params );

                el.find( '.reduxsa-spacing-input' ).on(
                    'change', function() {
                        var units = $( this ).parents( '.reduxsa-field:first' ).find( '.field-units' ).val();

                        if ( $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-spacing-units' ).length !== 0 ) {
                            units = $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-spacing-units option:selected' ).val();
                        }

                        var value = $( this ).val();

                        if ( typeof units !== 'undefined' && value ) {
                            value += units;
                        }

                        if ( $( this ).hasClass( 'reduxsa-spacing-all' ) ) {
                            $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-spacing-value' ).each(
                                function() {
                                    $( this ).val( value );
                                }
                            );
                        } else {
                            $( '#' + $( this ).attr( 'rel' ) ).val( value );
                        }
                    }
                );

                el.find( '.reduxsa-spacing-units' ).on(
                    'change', function() {
                        $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-spacing-input' ).change();
                    }
                );
            }
        );
    };
})( jQuery );