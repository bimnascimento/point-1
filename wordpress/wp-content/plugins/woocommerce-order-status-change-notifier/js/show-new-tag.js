jQuery( document ).ready( function( $ ) {
    $( '#custom_info' ).next().append( '<br> + {order_status_comment}, {order_status_comment_formatted}' )
} )
jQuery( document ).ready( function( $ ) {
    $( '#additional_shortcodes' ).append( '<option value="{order_status_comment}">order_status_comment</option>' )
    $( '#additional_shortcodes' ).append( '<option value="{order_status_comment_formatted}">order_status_comment_formatted</option>' )
	
} )
