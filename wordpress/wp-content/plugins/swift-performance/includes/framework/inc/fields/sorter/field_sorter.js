/*global reduxsa, reduxsa_opts*/
/*
 * Field Sorter jquery function
 * Based on
 * [SMOF - Slightly Modded Options Framework](http://aquagraphite.com/2011/09/slightly-modded-options-framework/)
 * Version 1.4.2
 */

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.sorter = reduxsa.field_objects.sorter || {};

    var scroll = '';

    $( document ).ready(
        function() {
            //reduxsa.field_objects.sorter.init();
        }
    );

    reduxsa.field_objects.sorter.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-sorter:visible' );
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
                
                /**    Sorter (Layout Manager) */
                el.find( '.reduxsa-sorter' ).each(
                    function() {
                        var id = $( this ).attr( 'id' );

                        el.find( '#' + id ).find( 'ul' ).sortable(
                            {
                                items: 'li',
                                placeholder: "placeholder",
                                connectWith: '.sortlist_' + id,
                                opacity: 0.8,
                                scroll: false,
                                out: function( event, ui ) {
                                    if ( !ui.helper ) return;
                                    if ( ui.offset.top > 0 ) {
                                        scroll = 'down';
                                    } else {
                                        scroll = 'up';
                                    }
                                    reduxsa.field_objects.sorter.scrolling( $( this ).parents( '.reduxsa-field-container:first' ) );

                                },
                                over: function( event, ui ) {
                                    scroll = '';
                                },

                                deactivate: function( event, ui ) {
                                    scroll = '';
                                },

                                stop: function( event, ui ) {
                                    var sorter = reduxsa.sorter[$( this ).attr( 'data-id' )];
                                    var id = $( this ).find( 'h3' ).text();

                                    if ( sorter.limits && id && sorter.limits[id] ) {
                                        if ( $( this ).children( 'li' ).length >= sorter.limits[id] ) {
                                            $( this ).addClass( 'filled' );
                                            if ( $( this ).children( 'li' ).length > sorter.limits[id] ) {
                                                $( ui.sender ).sortable( 'cancel' );
                                            }
                                        } else {
                                            $( this ).removeClass( 'filled' );
                                        }
                                    }
                                },

                                update: function( event, ui ) {
                                    var sorter = reduxsa.sorter[$( this ).attr( 'data-id' )];
                                    var id = $( this ).find( 'h3' ).text();

                                    if ( sorter.limits && id && sorter.limits[id] ) {
                                        if ( $( this ).children( 'li' ).length >= sorter.limits[id] ) {
                                            $( this ).addClass( 'filled' );
                                            if ( $( this ).children( 'li' ).length > sorter.limits[id] ) {
                                                $( ui.sender ).sortable( 'cancel' );
                                            }
                                        } else {
                                            $( this ).removeClass( 'filled' );
                                        }
                                    }

                                    $( this ).find( '.position' ).each(
                                        function() {
                                            //var listID = $( this ).parent().attr( 'id' );
                                            var listID = $( this ).parent().attr( 'data-id' );
                                            var parentID = $( this ).parent().parent().attr( 'data-group-id' );

                                            reduxsa_change( $( this ) );

                                            var optionID = $( this ).parent().parent().parent().attr( 'id' );

                                            $( this ).prop(
                                                "name",
                                                reduxsa.args.opt_name + '[' + optionID + '][' + parentID + '][' + listID + ']'
                                            );
                                        }
                                    );
                                }
                            }
                        );
                        el.find( ".reduxsa-sorter" ).disableSelection();
                    }
                );
            }
        );
    };

    reduxsa.field_objects.sorter.scrolling = function( selector ) {
        if (selector === undefined) {
            return;
        }
        
        var scrollable = selector.find( ".reduxsa-sorter" );

        if ( scroll == 'up' ) {
            scrollable.scrollTop( scrollable.scrollTop() - 20 );
            setTimeout( reduxsa.field_objects.sorter.scrolling, 50 );
        } else if ( scroll == 'down' ) {
            scrollable.scrollTop( scrollable.scrollTop() + 20 );
            setTimeout( reduxsa.field_objects.sorter.scrolling, 50 );
        }
    };

})( jQuery );