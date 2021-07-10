/*global reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.spinner = reduxsa.field_objects.spinner || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.spinner.init();
        }
    );

    reduxsa.field_objects.spinner.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-spinner:visible' );
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
                el.find( '.reduxsa_spinner' ).each(
                    function() {
                        //slider init
                        var spinner = $( this ).find( '.spinner-input' ).data();
                        spinner.id = $( this ).find( '.spinner-input' ).attr( 'id' );

                        el.find( "#" + spinner.id ).spinner(
                            {
                                value: parseFloat( spinner.val, null ),
                                min: parseFloat( spinner.min, null ),
                                max: parseFloat( spinner.max, null ),
                                step: parseFloat( spinner.step, null ),
                                range: "min",

                                slide: function( event, ui ) {
                                    var input = $( "#" + spinner.id );
                                    input.val( ui.value );
                                    reduxsa_change( input );
                                }
                            }
                        );

                        // Limit input for negative
                        var neg = false;
                        if ( parseInt( spinner.min, null ) < 0 ) {
                            neg = true;
                        }

                        //el.find( "#" + spinner.id ).numeric(
                        //    {
                        //        allowMinus: neg,
                        //        min: spinner.min,
                        //        max: spinner.max
                        //    }
                        //);

                    }
                );

                // Update the slider from the input and vice versa
                el.find( ".spinner-input" ).keyup(
                    function() {
                        $( this ).addClass( 'spinnerInputChange' );
                    }
                );

                el.find( ".spinner-input" ).focus(
                    function() {
                        reduxsa.field_objects.spinner.clean(
                            $( this ).val(), $( this )
                        );
                    }
                );

                el.find( '.spinner-input' ).typeWatch(
                    {
                        callback: function( value ) {
                            reduxsa.field_objects.spinner.clean(
                                value, $( this )
                            );
                        },

                        wait: 500,
                        highlight: false,
                        captureLength: 1
                    }
                );
            }
        );
    };

    reduxsa.field_objects.spinner.clean = function( value, selector ) {

        if ( !selector.hasClass( 'spinnerInputChange' ) ) {
            return;
        }
        selector.removeClass( 'spinnerInputChange' );

        var spinner = selector.data();
        value = parseFloat( value );

        if ( value === "" || value === null ) {
            value = spinner.min;
        } else if ( value >= parseInt( spinner.max ) ) {
            value = spinner.max;
        } else if ( value <= parseInt( spinner.min ) ) {
            value = spinner.min;
        } else {
            value = Math.round( value / spinner.step ) * spinner.step;
        }
        selector.val( value ).trigger( 'change' );
    };

})( jQuery );