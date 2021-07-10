/*global reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects                 = reduxsa.field_objects || {};
    reduxsa.field_objects.options_object  = reduxsa.field_objects.options_object || {};

//    $( document ).ready(
//        function() {
//            reduxsa.field_objects.import_export.init();
//        }
//    );

    reduxsa.field_objects.options_object.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( '.reduxsa-container-options_object' );
        }

        var parent = selector;

        if ( !selector.hasClass( 'reduxsa-field-container' ) ) {
            parent = selector.parents( '.reduxsa-field-container:first' );
        }

        if ( parent.hasClass( 'reduxsa-field-init' ) ) {
            parent.removeClass( 'reduxsa-field-init' );
        } else {
            return;
        }

        $( '#consolePrintObject' ).on(
            'click', function( e ) {
                e.preventDefault();
                console.log( $.parseJSON( $( "#reduxsa-object-json" ).html() ) );
            }
        );

        if ( typeof jsonView === 'function' ) {
            jsonView( '#reduxsa-object-json', '#reduxsa-object-browser' );
        }        
    };
})( jQuery );