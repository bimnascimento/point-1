/* global confirm, reduxsa, reduxsa_change */

/*global reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.image_select = reduxsa.field_objects.image_select || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.image_select.init();
        }
    );

    reduxsa.field_objects.image_select.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-image_select:visible' );
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
                // On label click, change the input and class
                el.find( '.reduxsa-image-select label img, .reduxsa-image-select label .tiles' ).click(
                    function( e ) {
                        var id = $( this ).closest( 'label' ).attr( 'for' );

                        $( this ).parents( "fieldset:first" ).find( '.reduxsa-image-select-selected' ).removeClass( 'reduxsa-image-select-selected' ).find( "input[type='radio']" ).attr(
                            "checked", false
                        );
                        $( this ).closest( 'label' ).find( 'input[type="radio"]' ).prop( 'checked' );

                        if ( $( this ).closest( 'label' ).hasClass( 'reduxsa-image-select-preset-' + id ) ) { // If they clicked on a preset, import!
                            e.preventDefault();

                            var presets = $( this ).closest( 'label' ).find( 'input' );
                            var data = presets.data( 'presets' );
                            var merge = presets.data( 'merge' );

                            if( merge !== undefined && merge !== null ) {
                                if( $.type( merge ) === 'string' ) {
                                    merge = merge.split('|');
                                }

                                $.each(data, function( index, value ) {
                                    if( ( merge === true || $.inArray( index, merge ) != -1 ) && $.type( reduxsa.options[index] ) === 'object' ) {
                                        data[index] = $.extend(reduxsa.options[index], data[index]);
                                    }
                                });
                            }

                            if ( presets !== undefined && presets !== null ) {
                                var answer = confirm( reduxsa.args.preset_confirm );

                                if ( answer ) {
                                    el.find( 'label[for="' + id + '"]' ).addClass( 'reduxsa-image-select-selected' ).find( "input[type='radio']" ).attr(
                                        "checked", true
                                    );
                                    window.onbeforeunload = null;
                                    if ( $( '#import-code-value' ).length === 0 ) {
                                        $( this ).append( '<textarea id="import-code-value" style="display:none;" name="' + reduxsa.args.opt_name + '[import_code]">' + JSON.stringify( data ) + '</textarea>' );
                                    } else {
                                        $( '#import-code-value' ).val( JSON.stringify( data ) );
                                    }
                                    if ( $( '#publishing-action #publish' ).length !== 0 ) {
                                        $( '#publish' ).click();
                                    } else {
                                        $( '#reduxsa-import' ).click();
                                    }
                                }
                            } else {
                            }

                            return false;
                        } else {
                            el.find( 'label[for="' + id + '"]' ).addClass( 'reduxsa-image-select-selected' ).find( "input[type='radio']" ).attr(
                                "checked", true
                            ).trigger('change');

                            reduxsa_change( $( this ).closest( 'label' ).find( 'input[type="radio"]' ) );
                        }
                    }
                );

                // Used to display a full image preview of a tile/pattern
                el.find( '.tiles' ).qtip(
                    {
                        content: {
                            text: function( event, api ) {
                                return "<img src='" + $( this ).attr( 'rel' ) + "' style='max-width:150px;' alt='' />";
                            },
                        },
                        style: 'qtip-tipsy',
                        position: {
                            my: 'top center', // Position my top left...
                            at: 'bottom center', // at the bottom right of...
                        }
                    }
                );
            }
        );

    };
})( jQuery );