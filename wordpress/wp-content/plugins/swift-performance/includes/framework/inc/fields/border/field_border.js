/*
 Field Border (border)
 */

/*global reduxsa_change, wp, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.border = reduxsa.field_objects.border || {};

    reduxsa.field_objects.border.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-border:visible' );
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
                el.find( ".reduxsa-border-top, .reduxsa-border-right, .reduxsa-border-bottom, .reduxsa-border-left, .reduxsa-border-all" ).numeric(
                    {
                        allowMinus: false
                    }
                );

                var default_params = {
                    triggerChange: true,
                    allowClear: true
                };

                var select2_handle = el.find( '.reduxsa-container-border' ).find( '.select2_params' );

                if ( select2_handle.size() > 0 ) {
                    var select2_params = select2_handle.val();

                    select2_params = JSON.parse( select2_params );
                    default_params = $.extend( {}, default_params, select2_params );
                }

                el.find( ".reduxsa-border-style" ).select2( default_params );

                el.find( '.reduxsa-border-input' ).on(
                    'change', function() {
                        var units = $( this ).parents( '.reduxsa-field:first' ).find( '.field-units' ).val();
                        if ( $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-border-units' ).length !== 0 ) {
                            units = $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-border-units option:selected' ).val();
                        }
                        var value = $( this ).val();
                        if ( typeof units !== 'undefined' && value ) {
                            value += units;
                        }
                        if ( $( this ).hasClass( 'reduxsa-border-all' ) ) {
                            $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-border-value' ).each(
                                function() {
                                    $( this ).val( value );
                                }
                            );
                        } else {
                            $( '#' + $( this ).attr( 'rel' ) ).val( value );
                        }
                    }
                );

                el.find( '.reduxsa-border-units' ).on(
                    'change', function() {
                        $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-border-input' ).change();
                    }
                );

                el.find( '.reduxsa-color-init' ).wpColorPicker(
                    {
                        change: function( e, ui ) {
                            $( this ).val( ui.color.toString() );
                            reduxsa_change( $( this ) );
                            el.find( '#' + e.target.getAttribute( 'data-id' ) + '-transparency' ).removeAttr( 'checked' );
                        },

                        clear: function( e, ui ) {
                            $( this ).val( ui.color.toString() );
                            reduxsa_change( $( this ).parent().find( '.reduxsa-color-init' ) );
                        }
                    }
                );

                el.find( '.reduxsa-color' ).on(
                    'keyup', function() {
                        var color = colorValidate( this );

                        if ( color && color !== $( this ).val() ) {
                            $( this ).val( color );
                        }
                    }
                );

                // Replace and validate field on blur
                el.find( '.reduxsa-color' ).on(
                    'blur', function() {
                        var value = $( this ).val();

                        if ( colorValidate( this ) === value ) {
                            if ( value.indexOf( "#" ) !== 0 ) {
                                $( this ).val( $( this ).data( 'oldcolor' ) );
                            }
                        }
                    }
                );

                // Store the old valid color on keydown
                el.find( '.reduxsa-color' ).on(
                    'keydown', function() {
                        $( this ).data( 'oldkeypress', $( this ).val() );
                    }
                );
            }
        );
    };
})( jQuery );