jQuery(document).ready(function($){
    if( $('.menu-item').length < 1 )
    {
        $('.wpem-chk-all').hide();
        $('#remove-items').hide();
    }
    $( 'li.menu-item' ).each(function() {  
        var len = $('.temp-chkbox').length;
        len++;
        var ninput = $( '.temp-chkbox' ).clone();
        ninput.html(function(i, oHTML) {
        return oHTML.replace( /{{index}}/g, len );
    	});
        $( this ).children('.menu-item-bar').after( ninput.html() );
    });

    var removeitems = $( '.menu-items-remove' ).clone();
    $( '#save_menu_footer' ).after(  removeitems.html() );

    var removeallitems = $( '.temp-all-chkbox' ).clone();
    $( '#menu-to-edit' ).before(  removeallitems.html() );

    $('body').on('click','#remove-items', function(e){
        e.preventDefault();
        var confirmation = confirm(WPEM_OBJ.lang.are_you_sure);
        if( ! confirmation ){
            return false;
        }
	    jsonObj =[];
        $( 'li.menu-item' ).each(function() { 
        	if ($(this).children('.chkit').is(':checked')) {
                myvar = $(this).attr('id').replace(/\D/g,'');
                jsonObj.push(myvar);
        	} 
    	});
      	jQuery.ajax({
	        type : 'post',
            dataType : "json",
	        url : menu_ajax_data.ajaxurl,
	        data : {action: 'filter_menu', val : jsonObj},
            success: function(response){
                if(response.type === "success") {
                    var loadconfirmation = confirm(WPEM_OBJ.lang.load_it);
                    if( ! loadconfirmation ){
                        return false;
                    }
                   document.location.reload(true);
                }
            },
       })	   
    })

    $('body').on('click','.wpem-chk-all', function(e){
        if ($(this).children().children('.all-chkit').is(':checked')) {
           $('.chkit').prop( "checked", true ); 
        }
        else{
             $('.chkit').prop( "checked", false ); 
        }
    });
});