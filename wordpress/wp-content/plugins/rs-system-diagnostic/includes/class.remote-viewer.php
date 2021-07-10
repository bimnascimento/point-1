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
 *	Handles Remote Viewing of System Diagnostic Data
 *
 *	@package		RSSD
 *	@subpackage		Classes/Viewer
 *	@author			Scott Allen
 *	@since			1.0
 */

class RS_System_Diagnostic_Viewer {

	/**
	 *	Renders Remote Viewing portion of Plugin Settings Page
	 *	@dependencies	RS_System_Diagnostic::get_option(), 
	 *	@since			1.0
	 *	@return			void
	 */
	static public function remote_viewing_section() {
		$remote_url_key	= RS_System_Diagnostic::get_option( 'remote_url_key' );
		$remote_url		= RSSD_SITE_URL.'/?'.RSSD_GET_VAR.'='.$remote_url_key;
		?>
		<p><?php 
		if( !empty( $_GET['option'] ) && 'advanced' === $_GET['option'] && is_super_admin() ) {
			echo __( 'The additional data contained in the Advanced View will not be available via the Remote Viewing URL, since it may contain potentially sensitive site configuration data.', 'rs-system-diagnostic' ) . '</p><p>' .__( 'Should you need to provide this to tech support personnel, such as a plugin or theme developer, email is the best method.', 'rs-system-diagnostic' ) . '</p><p>' .__( 'Advanced View data should not be posted to any public forums, even temporarily.', 'rs-system-diagnostic' ) . '</p>';
		} else {
			echo __( 'Users with this URL can view a plain-text version of your System Diagnostic Data.', 'rs-system-diagnostic' ) . '</p><p>' .__( 'This link can be handy in support forums, as access to this information can be removed after you receive the help you need.', 'rs-system-diagnostic' ) . '</p><p>' .__( 'For security, is best not to post to public forums, however, if you need to, the option is available.', 'rs-system-diagnostic' ) . '</p><p>' .__( 'Generating a new URL will safely void access for anyone who has the existing URL.', 'rs-system-diagnostic' ); ?></p>
			<p><input type="text" readonly="readonly" class="rssd-url rssd-url-text" onclick="this.focus();this.select()" value="<?php echo esc_url( $remote_url ) ?>" title="<?php _e( 'To copy the System Info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'rs-system-diagnostic' ); ?>" />&nbsp;&nbsp;<a href="<?php echo esc_url( $remote_url ) ?>" target="_blank" class="rssd-tiny rssd-url-text-link"><?php _e( 'Test the URL', 'rs-system-diagnostic' ) ?></a></p>
			<p class="submit">
				<input type="submit" onClick="return false;" class="button-secondary" name="generate-new-url" value="<?php _e( 'Generate New URL', 'rs-system-diagnostic' ); ?>" />
			</p>
			<?php
		}
	}

	/**
	 *	Renders Remote View using $_GET value
	 *	@dependencies	RS_System_Diagnostic::is_remote_view(), RS_System_Diagnostic::display_data(), RS_System_Diagnostic::wp_die(), 
	 *	@since			1.0
	 *	@action			template_redirect
	 *	@return			void
	 */
	static public function remote_view() {
		if( empty( $_GET[RSSD_GET_VAR] ) || is_admin() ) { return; }
		if( RS_System_Diagnostic::is_remote_view() ) {
			echo esc_html( RS_System_Diagnostic::display_data() );
			exit();
		} else {
			$error = 'Invalid System Diagnostic Data URL. [Code E001]';
			RS_System_Diagnostic::wp_die( $error, '404' );
		}
	}

}

