/*global jQuery, document, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.import_export = reduxsa.field_objects.import_export || {};

    reduxsa.field_objects.import_export.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( ".reduxsa-group-tab:visible" ).find( '.reduxsa-container-import_export:visible' );
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
                el.each(
                    function() {
                        $( '#reduxsa-import' ).click(
                            function( e ) {
                                if ( $( '#import-code-value' ).val() === "" && $( '#import-link-value' ).val() === "" ) {
                                    e.preventDefault();
                                    return false;
                                }
                                window.onbeforeunload = null;
                                reduxsa.args.ajax_save = false;
                            }
                        );

                        $( this ).find( '#reduxsa-import-code-button' ).click(
                            function() {
                                var $el = $( '#reduxsa-import-code-wrapper' );
                                if ( $( '#reduxsa-import-link-wrapper' ).is( ':visible' ) ) {
                                    $( '#import-link-value' ).text( '' );
                                    $( '#reduxsa-import-link-wrapper' ).slideUp(
                                        'fast', function() {
                                            $el.slideDown(
                                                'fast', function() {
                                                    $( '#import-code-value' ).focus();
                                                }
                                            );
                                        }
                                    );
                                } else {
                                    if ( $el.is( ':visible' ) ) {
                                        $el.slideUp();
                                    } else {
                                        $el.slideDown(
                                            'medium', function() {
                                                $( '#import-code-value' ).focus();
                                            }
                                        );
                                    }
                                }
                            }
                        );

                        $( this ).find( '#reduxsa-import-link-button' ).click(
                            function() {
                                var $el = $( '#reduxsa-import-link-wrapper' );
                                if ( $( '#reduxsa-import-code-wrapper' ).is( ':visible' ) ) {
                                    $( '#import-code-value' ).text( '' );
                                    $( '#reduxsa-import-code-wrapper' ).slideUp(
                                        'fast', function() {
                                            $el.slideDown(
                                                'fast', function() {
                                                    $( '#import-link-value' ).focus();
                                                }
                                            );
                                        }
                                    );
                                } else {
                                    if ( $el.is( ':visible' ) ) {
                                        $el.slideUp();
                                    } else {
                                        $el.slideDown(
                                            'medium', function() {
                                                $( '#import-link-value' ).focus();
                                            }
                                        );
                                    }
                                }
                            }
                        );

                        $( this ).find( '#reduxsa-export-code-copy' ).click(
                            function() {
                                var $el = $( '#reduxsa-export-code' );
                                if ( $( '#reduxsa-export-link-value' ).is( ':visible' ) ) {
                                    $( '#reduxsa-export-link-value' ).slideUp(
                                        'fast', function() {
                                            $el.slideDown(
                                                'medium', function() {
                                                    var options = reduxsa.options;
                                                    options['reduxsa-backup'] = 1;
                                                    $( this ).text( JSON.stringify( options ) ).focus().select();
                                                }
                                            );
                                        }
                                    );
                                } else {
                                    if ( $el.is( ':visible' ) ) {
                                        $el.slideUp().text( '' );
                                    } else {
                                        $el.slideDown(
                                            'medium', function() {
                                                var options = reduxsa.options;
                                                options['reduxsa-backup'] = 1;
                                                $( this ).text( JSON.stringify( options ) ).focus().select();
                                            }
                                        );
                                    }
                                }
                            }
                        );

                        $( this ).find( 'textarea' ).focusout(
                            function() {
                                var $id = $( this ).attr( 'id' );
                                var $el = $( this );
                                var $container = $el;
                                if ( $id == "import-link-value" || $id == "import-code-value" ) {
                                    $container = $( this ).parent();
                                }
                                $container.slideUp(
                                    'medium', function() {
                                        if ( $id != "reduxsa-export-link-value" ) {
                                            $el.text( '' );
                                        }
                                    }
                                );
                            }
                        );


                        $( this ).find( '#reduxsa-export-link' ).click(
                            function() {
                                var $el = $( '#reduxsa-export-link-value' );
                                if ( $( '#reduxsa-export-code' ).is( ':visible' ) ) {
                                    $( '#reduxsa-export-code' ).slideUp(
                                        'fast', function() {
                                            $el.slideDown().focus().select();
                                        }
                                    );
                                } else {
                                    if ( $el.is( ':visible' ) ) {
                                        $el.slideUp();
                                    } else {
                                        $el.slideDown(
                                            'medium', function() {
                                                $( this ).focus().select();
                                            }
                                        );
                                    }

                                }
                            }
                        );

                        var textBox1 = document.getElementById( "reduxsa-export-code" );
                        textBox1.onfocus = function() {
                            textBox1.select();
                            // Work around Chrome's little problem
                            textBox1.onmouseup = function() {
                                // Prevent further mouseup intervention
                                textBox1.onmouseup = null;
                                return false;
                            };
                        };
                        var textBox2 = document.getElementById( "import-code-value" );
                        textBox2.onfocus = function() {
                            textBox2.select();
                            // Work around Chrome's little problem
                            textBox2.onmouseup = function() {
                                // Prevent further mouseup intervention
                                textBox2.onmouseup = null;
                                return false;
                            };
                        };

                    }
                );
            }
        );
    };
})( jQuery );


