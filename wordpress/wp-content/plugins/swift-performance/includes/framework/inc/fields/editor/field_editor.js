/**
 * ReduxSA Editor on change callback
 * Dependencies        : jquery
 * Feature added by    : Dovy Paukstys
 *                     : Kevin Provance (who helped)  :P
 * Date                : 07 June 2014
 */

/*global reduxsa_change, wp, tinymce, reduxsa*/
(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.editor = reduxsa.field_objects.editor || {};
    
    $( document ).ready(
        function() {
            //reduxsa.field_objects.editor.init();
        }
    );

    reduxsa.field_objects.editor.init = function( selector ) {
        setTimeout(
            function() {
                if (typeof(tinymce) !== 'undefined') {
                    for ( var i = 0; i < tinymce.editors.length; i++ ) {
                        reduxsa.field_objects.editor.onChange( i );
                    }   
                }
            }, 1000
        );
    };

    reduxsa.field_objects.editor.onChange = function( i ) {
        tinymce.editors[i].on(
            'change', function( e ) {
                var el = jQuery( e.target.contentAreaContainer );
                if ( el.parents( '.reduxsa-container-editor:first' ).length !== 0 ) {
                    reduxsa_change( $( '.wp-editor-area' ) );
                }
            }
        );
    };
})( jQuery );
