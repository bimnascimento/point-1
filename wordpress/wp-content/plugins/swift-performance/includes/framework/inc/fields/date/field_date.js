/*global jQuery, document, reduxsa*/

(function( $ ) {
    "use strict";

    reduxsa.field_objects = reduxsa.field_objects || {};
    reduxsa.field_objects.date = reduxsa.field_objects.date || {};

    $( document ).ready(
        function() {
            //reduxsa.field_objects.date.init();
        }
    );

    reduxsa.field_objects.date.init = function( selector ) {
        if ( !selector ) {
            selector = $( document ).find( '.reduxsa-container-date:visible' );
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
//                        var someArr = []
//                        someArr = i;
//                        console.log(someArr);
                
//                var str = JSON.parse('{"reduxsa_demo[opt-multi-check]":{"reduxsa_demo[opt-multi-check][1]":"1","reduxsa_demo[opt-multi-check][2]":"","reduxsa_demo[opt-multi-check][3]":""}}');
//                console.log (str);
//                
//                $.each(str, function(idx, val){
//                    var tmpArr = new Object();
//                    var count = 1;
//                    
//                    $.each(val, function (i, v){
//                        
//                        tmpArr[count] = v;
//                        count++;
//                    });
//
//                    var newArr = {};
//                    newArr[idx] = tmpArr;
//                    var newJSON = JSON.stringify(newArr)
//                    //console.log(newJSON);
//                });
                
                el.find( '.reduxsa-datepicker' ).each( function() {
                    $( this ).datepicker({
                        "dateFormat":"mm/dd/yy",
                        beforeShow: function(input, instance){
                            var el = $('#ui-datepicker-div');
                            //$.datepicker._pos = $.datepicker._findPos(input); //this is the default position
                            var popover = instance.dpDiv;
                            $('.reduxsa-container:first').append(el);
                            $('#ui-datepicker-div').hide();
                            setTimeout(function() {
                                popover.position({
                                    my: 'left top',
                                    at: 'left bottom',
                                    collision: 'none',
                                    of: input
                                });
                            }, 1);
                        } 
                    });
                });
            }
        );


    };
})( jQuery );