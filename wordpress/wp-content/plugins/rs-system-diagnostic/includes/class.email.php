<?php
/**
 *  RS System Diagnostic Data Remote Viewer
 *  File Version 1.0.9
 */

if( !defined( 'ABSPATH' ) || !defined( 'RSSD_VERSION' ) ) {
	if( !headers_sent() ) { @header( $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', TRUE, 403 ); @header( 'X-Robots-Tag: noindex', TRUE ); }
	die( 'ERROR: Direct access to this file is not allowed.' );
}



/**
 *	Handles Sending of System Diagnostic Data via Email
 *
 *	@package		RSSD
 *	@subpackage		Classes/Email
 *	@author			Scott Allen
 *	@since			1.0.0
 */

class RS_System_Diagnostic_Email {

	/**
	 *	Renders Email section of Plugin Settings Page
	 *	@since		1.0.0
	 *	@return		void
	 */
	static public function email_form_section() {

?>
<form name="rssd-send-data-email-form" action="" method="post" enctype="multipart/form-data" autocomplete="off">
<?php wp_nonce_field( 'rssd_send_data_email_token', 'sdet_tkn' ); echo "\n"; ?>

	<table class="form-table rssd-email-form">
		<tr>
			<th scope="row">
				<label for="rssd-email-address"><?php _e( 'Send to Email Address', 'rs-system-diagnostic' ); ?>*</label>
			</th>
			<td>
				<input type="email" name="rssd-email-address" id="rssd-email-address" placeholder="user@website.com" required="required" minlength="6" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="rssd-email-subject"><?php _e( 'Subject', 'rs-system-diagnostic' ); ?>*</label>
			</th>
			<td>
				<input type="text" name="rssd-email-subject" id="rssd-email-subject" placeholder="<?php _e( 'Subject', 'rs-system-diagnostic' ); ?>" required="required" maxlength="400" minlength="6" />
			</td>
		</tr>
		<tr>
			<th scope="row">
				<label for="rssd-email-message"><?php _e( 'Additional Message', 'rs-system-diagnostic' ); ?></label>
				<p class="description"><?php _e( 'Your System Diagnostic Data will be attached automatically to this email form.', 'rs-system-diagnostic' ); ?></p>
			</th>
			<td>
				<textarea class="rssd-email-textarea" name="rssd-email-message" id="rssd-email-message" placeholder="//// RS System Diagnostic Message ////"></textarea>
			</td>
		</tr>
	</table>
	<?php
	submit_button( __( 'Send Email', 'rs-system-diagnostic' ) , 'secondary' );
	?>

</form>

<?php
	}

	/**
	 *	Sends email with the System Diagnostic Data
	 *	@since		1.0.0
	 *	@return		void
	 */
	static public function send_email() {
		global $current_user;
		if( isset( $_POST['rssd-email-address'], $_POST['rssd-email-subject'], $_POST['rssd-email-message'] ) ) {
			if( !RS_System_Diagnostic::is_user_admin() || !check_admin_referer( 'rssd_send_data_email_token', 'sdet_tkn' ) ) { return 'error'; }
			if( !empty( $_POST['rssd-email-address'] ) ) { $address = sanitize_email( stripslashes( $_POST['rssd-email-address'] ) ); } else { return 'error'; }
			if( !empty( $_POST['rssd-email-subject'] ) ) { $subject = 'RSSD: '. sanitize_text_field( stripslashes( $_POST['rssd-email-subject'] ) ); } else { return 'error'; }
			if( !empty( $_POST['rssd-email-message'] ) ) { $message = self::prepare_email_form_data( self::sanitize_textarea( $_POST['rssd-email-message'] ) ); } else { $message = '//// RS System Diagnostic Message ////////'; }
			$email_domain	= RS_System_Diagnostic::get_email_domain( RSSD_SERVER_NAME );
			$sender_email	= 'rssd.noreply@'.$email_domain;
			$current_user	= wp_get_current_user();
			$headers = array(
				'From: '			. $current_user->display_name	. ' <' . $sender_email				. '>', 
				'Reply-To: '		. $current_user->display_name	. ' <' . $current_user->user_email	. '>', 
				'Content-Type: '	. 'text/plain', 
			);
			/* Insert System Diagnostic Data into email */
			$message .= "\r\n\r\n---------------\r\n\r\n". str_replace( array( RSSD_EOL, ), array( "\r\n", ), RS_System_Diagnostic::display_data() );
			$sent = RS_System_Diagnostic::mail( $address, $subject, $message, $headers );
			return !empty( $sent ) ? 'sent' : 'error';
		}
		return FALSE;
	}

	/**
	 *	Prepare form data before emailing.
	 *	TO DO: Possibly integrate htmlspecialchars_decode() & nl2br()
	 *  @since		1.0.6
	 */
	static public function prepare_email_form_data( $string ) {
		$string = str_replace( array( "\r\n", "\n" ), array( "\n", "\r\n" ), $string );
		$string = preg_replace( "~(\\*['’]|&(apos|#x0+27|#0?39|rsquo);)~i", '’', $string );
		$string = str_replace( array( "\\'", '\’', "'", '’', '&apos;', '&rsquo;', '&#x00027;', '&#039;', '&#39;', ), '’', $string );
		return $string;
	}

	/**
	 *	Sanitize input from a form textarea
	 *	Like sanitize_text_field() but this preserves the line breaks
	 *  Use for contact forms or similar processes
	 *  @since		RSSD 1.0.6, WPSS 1.9.7.8
	 */
	static public function sanitize_textarea( $string ) {
		$string = str_replace( array( "\r\n" ), array( "\n" ), $string );
		return implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $string ) ) );
	}

}

