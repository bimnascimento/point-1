jQuery( document ).ready( function( $ ) {
    $( '#order_status' ).parent().after( '<p class="form-field form-field-wide p-status-comment" style="display:none"><label for="oscn_status_comment">Status Comment:</label><textarea name="oscn_status_comment" id="oscn_status_comment"></textarea></p>' )

	$( '.p-status-comment' ).after( '<p class="p-status-comment-notify-customer" style="display:none">\n\
                <input type=hidden name="oscn_status_comment_notify_customer" value="No">\n\
                <input type=checkbox name="oscn_status_comment_notify_customer" id="oscn_status_comment_notify_customer" class="check-column" value="Yes"><label for="oscn_status_comment_notify_customer">Notify Customer</label>\n\
            </p>' 
        )
	
    $( '#order_status' ).change( function() {
        $( '.p-status-comment' ).show()
        $( '.p-status-comment-notify-customer' ).show()
    } )
} )
