/*global reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.select = reduxsa.field_objects.select || {};

    reduxsa.field_objects.select.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( '.reduxsa-container-select:visible' );
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
                
                el.find( 'select.reduxsa-select-item' ).each(
                    function() {

                        var default_params = {
                            width: 'resolve',
                            triggerChange: true,
                            allowClear: true
                        };
                        if ( $(this).attr('multiple') == "multiple" ) {
                            default_params.width = "100%";
                        }

                        if ( $( this ).siblings( '.select2_params' ).size() > 0 ) {
                            var select2_params = $( this ).siblings( '.select2_params' ).val();
                            select2_params = JSON.parse( select2_params );
                            default_params = $.extend( {}, default_params, select2_params );
                        }

                        if ( $( this ).hasClass( 'font-icons' ) ) {
                            default_params = $.extend(
                                {}, {
                                    formatResult: reduxsa.field_objects.select.addIcon,
                                    formatSelection: reduxsa.field_objects.select.addIcon,
                                    escapeMarkup: function( m ) {
                                        return m;
                                    }
                                }, default_params
                            );
                        }

                        $( this ).select2( default_params );

                        if ( $( this ).hasClass( 'select2-sortable' ) ) {
                            default_params = {};
                            default_params.bindOrder = 'sortableStop';
                            default_params.sortableOptions = {placeholder: 'ui-state-highlight'};
                            $( this ).select2Sortable( default_params );
                        }

                        $( this ).on(
                            "change", function() {
                                reduxsa_change( $( $( this ) ) );
                                $( this ).select2SortableOrder();
                            }
                        );
                    }
                );
            }
        );
    };

    reduxsa.field_objects.select.addIcon = function( icon ) {
        if ( icon.hasOwnProperty( 'id' ) ) {
            return "<span class='elusive'><i class='" + icon.id + "'></i>" + "&nbsp;&nbsp;" + icon.text + "</span>";
        }
    };
})( jQuery );