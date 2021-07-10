/*global jQuery, document, reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.sortable = reduxsa.field_objects.sortable || {};

    var scroll = '';

    reduxsa.field_objects.sortable.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-sortable:visible' );
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
                el.find( ".reduxsa-sortable" ).sortable(
                    {
                        handle: ".drag",
                        placeholder: "placeholder",
                        opacity: 0.7,
                        scroll: false,
                        out: function( event, ui ) {
                            if ( !ui.helper ) return;
                            if ( ui.offset.top > 0 ) {
                                scroll = 'down';
                            } else {
                                scroll = 'up';
                            }
                            reduxsa.field_objects.sortable.scrolling( $( this ).parents( '.reduxsa-field-container:first' ) );
                        },

                        over: function( event, ui ) {
                            scroll = '';
                        },

                        deactivate: function( event, ui ) {
                            scroll = '';
                        },

                        update: function(event, ui) {
                            // console.log('here');
                            reduxsa_change( $( this ) );
                        }
                    }
                );

                el.find( '.checkbox_sortable' ).on(
                    'click', function() {
                        if ( $( this ).is( ":checked" ) ) {
                            el.find( '#' + $( this ).attr( 'rel' ) ).val( 1 );
                        } else {
                            el.find( '#' + $( this ).attr( 'rel' ) ).val( '' );
                        }
                    }
                );
            }
        );
    };

    reduxsa.field_objects.sortable.scrolling = function( selector ) {
        if (selector === undefined) {
            return;
        }
        
        var $scrollable = selector.find( ".reduxsa-sorter" );

        if ( scroll == 'up' ) {
            $scrollable.scrollTop( $scrollable.scrollTop() - 20 );
            setTimeout( reduxsa.field_objects.sortable.scrolling, 50 );
        } else if ( scroll == 'down' ) {
            $scrollable.scrollTop( $scrollable.scrollTop() + 20 );
            setTimeout( reduxsa.field_objects.sortable.scrolling, 50 );
        }
    };

})( jQuery );