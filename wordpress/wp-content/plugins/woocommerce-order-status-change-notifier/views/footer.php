<?php
$statuses = wc_get_order_statuses();
asort($statuses);
?>

<div id="dialog-status-change" data-order-id="" style="display: none;">
	<form id="form_status_comment">
		<div class="group">
			<label for="status"><?php _e('Select status:', 'woocommerce-order-status-change-notifier'); ?></label>
			<select name="status" id="status">
				<?php
				foreach ($statuses as $v_status => $p_status) {
					?>
					<option value="<?php echo $v_status ?>"><?php echo $p_status ?></option>
					<?php
				}
				?>
			</select>
		</div>
		<div class="group">
			<label for="status_comment"><?php _e('Status comment:', 'woocommerce-order-status-change-notifier'); ?></label>
			<textarea name="oscn_status_comment" id="status_comment"></textarea>
		</div>
		<div class="group">
			<input type="hidden" name="oscn_status_comment_notify_customer" value="No">
			<label for="status_comment_notify_customer">
				<input type="checkbox" name="oscn_status_comment_notify_customer" id="status_comment_notify_customer" value="Yes">&nbsp;<?php _e('Notify Customer', 'woocommerce-order-status-change-notifier'); ?>
			</label>
		</div>
		<hr class="">
		<div class="" style="margin:0 0 0 125px;">
			<a class="button cancel-button" id="cancel">Cancel</a>
			<a class="button choice-button" id="change" >Change</a>
		</div>
		<div id="reponse" style="display: none"></div>
	</form>

</div>
<script>
	var ajax = '<?php echo $ajax ?>';
	jQuery( document ).ready( function( $ ) {
		$( '#change' ).click( function( ) {
			var order_id = $( '#dialog-status-change' ).attr( 'data-order-id' )
			var action = 'action=wc_osc_update_order_status_comment&method=change_status&order_id=' + order_id + '&' + $( '#form_status_comment' ).serialize( );
			$.post( ajax, action, function( data ) {
				$( '.reponse' ).html( data )
//				setTimeout( function( ) {
				$( '.reponse' ).html( '' );
				$( '#dialog-status-change' ).hide();
				window.location.reload( );
//				}, 1000 )
			}, 'HTML' )
		} )
		$( '#cancel' ).click( function() {
			tb_remove()
		} )
		$( 'a.change-status' ).click( function( ) {
			var order_id = $( this ).attr( 'data-order-id' )
			var status = $( this ).attr( 'data-current-status' )
			$( '#dialog-status-change' ).attr( 'data-order-id', order_id )
			$( '#dialog-status-change option[value="' + status + '"]' ).attr( 'selected', 1 )
			console.log( $( '#dialog-status-change option[value="' + status + '"]' ) )
			tb_show( "", "#TB_inline?height=275&width=380&inlineId=dialog-status-change" );
			$( '#TB_window' ).width( 414 )
			$( '#TB_window' ).height( 325 )

		} )

	} )
</script>
