/*global reduxsa_change, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.select_image = reduxsa.field_objects.select_image || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.select_image.init();
        }
    );

    reduxsa.field_objects.select_image.init = function( selector ) {

        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-select_image:visible' );
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
                var default_params = {
                    width: 'resolve',
                    triggerChange: true,
                    allowClear: true
                };

                var select2_handle = el.find( '.reduxsa-container-select_image' ).find( '.select2_params' );

                if ( select2_handle.size() > 0 ) {
                    var select2_params = select2_handle.val();

                    select2_params = JSON.parse( select2_params );
                    default_params = $.extend( {}, default_params, select2_params );
                }

                el.find( 'select.reduxsa-select-images' ).select2( default_params );

                el.find( '.reduxsa-select-images' ).on(
                    'change', function() {
                        var preview = $( this ).parents( '.reduxsa-field:first' ).find( '.reduxsa-preview-image' );

                        if ( $( this ).val() === "" ) {
                            preview.fadeOut(
                                'medium', function() {
                                    preview.attr( 'src', '' );
                                }
                            );
                        } else {
                            preview.attr( 'src', $( this ).val() );
                            preview.fadeIn().css( 'visibility', 'visible' );
                        }
                    }
                );
            }
        );
    };
})( jQuery );