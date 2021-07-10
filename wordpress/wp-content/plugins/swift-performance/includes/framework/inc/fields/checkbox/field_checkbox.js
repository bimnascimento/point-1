/**
 * ReduxSA Checkbox
 * Dependencies        : jquery
 * Feature added by    : Dovy Paukstys
 * Date                : 17 June 2014
 */

/*global reduxsa_change, wp, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.checkbox = reduxsa.field_objects.checkbox || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.checkbox.init();
        }
    );

    reduxsa.field_objects.checkbox.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-checkbox:visible' );
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
                el.find( '.checkbox' ).on(
                    'click', function( e ) {
                        var val = 0;
                        if ( $( this ).is( ':checked' ) ) {
                            val = $( this ).parent().find( '.checkbox-check' ).attr( 'data-val' );
                        }
                        $( this ).parent().find( '.checkbox-check' ).val( val );
                        reduxsa_change( $( this ) );
                    }
                );
            }
        );
    };
})( jQuery );
