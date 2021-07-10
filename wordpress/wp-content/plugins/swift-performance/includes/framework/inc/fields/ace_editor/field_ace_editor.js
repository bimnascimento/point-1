/*global jQuery, document, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.ace_editor = reduxsa.field_objects.ace_editor || {};

    reduxsa.field_objects.ace_editor.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-ace_editor:visible' );
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
                el.find( '.ace-editor' ).each(
                    function( index, element ) {
                        var area = element;
                        var params = JSON.parse( $( this ).parent().find( '.localize_data' ).val() );
                        var editor = $( element ).attr( 'data-editor' );

                        var aceeditor = ace.edit( editor );
                        aceeditor.setTheme( "ace/theme/" + jQuery( element ).attr( 'data-theme' ) );
                        aceeditor.getSession().setMode( "ace/mode/" + $( element ).attr( 'data-mode' ) );
                        var parent = '';
                        if ( el.hasClass( 'reduxsa-field-container' ) ) {
                            parent = el.attr( 'data-id' );
                        } else {
                            parent = el.parents( '.reduxsa-field-container:first' ).attr( 'data-id' );
                        }

                        aceeditor.setOptions( params );
                        aceeditor.on(
                            'change', function( e ) {
                                $( '#' + area.id ).val( aceeditor.getSession().getValue() );
                                reduxsa_change( $( element ) );
                                aceeditor.resize();
                            }
                        );
                    }
                );
            }
        );
    };
})( jQuery );