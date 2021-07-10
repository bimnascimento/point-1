/*
 Field Palette (color)
 */

/*global jQuery, document, reduxsa_change, reduxsa*/

(function( $ ) {
    'use strict';

    reduxsa.field_objects         = reduxsa.field_objects || {};
    reduxsa.field_objects.palette = reduxsa.field_objects.palette || {};

    reduxsa.field_objects.palette.init = function( selector ) {
        
        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-palette:visible' );
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
                
                el.find( '.buttonset' ).each(
                    function() {
                        $( this ).buttonset();
                    }
                );
        
//                el.find('.reduxsa-palette-set').click(
//                    function(){
//                        console.log($(this).val());
//                    }
//                )
            }
        );
    };
})( jQuery );