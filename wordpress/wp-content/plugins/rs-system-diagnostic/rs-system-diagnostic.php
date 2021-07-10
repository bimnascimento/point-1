<?php
/*
Plugin Name:	RS System Diagnostic
Plugin URI:		http://www.redsandmarketing.com/plugins/rs-system-diagnostic/
Description:	Easily gather all the your WordPress and site configuration data in seconds, and send it directly to tech support by email or URL.
Version:		1.0.9
Author:			Scott Allen
Author URI:		https://www.redsandmarketing.com/
License:		GPL2+
License URI:	https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:	rs-system-diagnostic
Domain Path:	/languages
*/

/*
	Copyright © 2015-2017 Scott Allen				https://www.redsandmarketing.com/contact/
	Copyright © 2015-2017 Red Sand Media Group		https://www.redsandmarketing.com/

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see
	https://www.gnu.org/licenses/gpl-2.0.html or write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/* PLUGIN - BEGIN */

/* Make sure plugin remains secure if called directly */
if( !defined( 'ABSPATH' ) ) {
	if( !headers_sent() ) { @header( $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', TRUE, 403 ); }
	die( 'ERROR: This plugin requires WordPress and will not function if called directly.' );
}
/* Do not change value unless developer asks you to - for debugging only. Change in wp-config.php. */
if( !defined( 'RSSD_DEBUG' ) ) { define( 'RSSD_DEBUG', FALSE ); }
/* Prevents unintentional error display if WP_DEBUG not enabled. */
if( TRUE !== RSSD_DEBUG && TRUE !== WP_DEBUG ) { @ini_set( 'display_errors', 0 ); @error_reporting( 0 ); }

define( 'RSSD_VERSION',					'1.0.9'					);
define( 'RSSD_WP_VERSION',				$GLOBALS['wp_version']	);
define( 'RSSD_REQUIRED_WP_VERSION',		'3.7'					);
define( 'RSSD_REQUIRED_PHP_VERSION',	'5.3'					);
define( 'RSSD_PHP_TEST_MAX',			'7.1'					); /* MAX PHP VERSION TESTED */



/**
 *  RS_System_Diagnostic
 *  The Main Class
 */
class RS_System_Diagnostic {

	/* Initialize Class Variables */
	static private		$pref				= 'RSSD_';
	static private		$debug_server		= '.redsandmarketing.com';
	static private		$dev_url			= 'https://www.redsandmarketing.com/';
	static public		$_ENV				= array();
	static protected	$ip_dns_params		= array();
	static protected	$php_version		= PHP_VERSION;
	static protected	$wp_ver				= NULL;
	static protected	$plugin_name		= NULL;
	static protected	$plugin_basename	= NULL;
	static protected	$rgx_tld			= NULL;
	static protected	$web_host			= NULL;
	static protected	$web_host_proxy		= NULL;
	static protected	$ip_addr			= NULL;
	static protected	$rev_dns_cache		= NULL;
	static protected	$fwd_dns_cache		= NULL;
	static protected	$is_remote_view		= NULL;

	function __construct() {
		/**
		 *  Do nothing...for now
		 */
	}

	/**
	 *	General Setup: Load hooks, define constants, etc.
	 *	@dependencies	RS_System_Diagnostic::getenv(), RS_System_Diagnostic::eol(), RS_System_Diagnostic::ds(), RS_System_Diagnostic::ps(), RS_System_Diagnostic::format_bytes(), RS_System_Diagnostic::get_domain(), RS_System_Diagnostic::get_server_name(), RS_System_Diagnostic::get_server_addr(), RS_System_Diagnostic::get_server_hostname(), RS_System_Diagnostic::is_remote_view(), 
	 *	@since			1.0.0
	 *	@action			plugins_loaded / priority -10000
	 *	@return			void
	 */
	static public function setup() {

		/* Setup Environment Variables */
		self::getenv();

		$pref					= self::$pref;
		$debug_server			= self::$debug_server;

		/**
		 *	Setting essential PATH, URL, and other constants required by plugin
		 */

		/* Set Variables Before Defining Constants */
		$EOL					= ( defined( 'PHP_EOL' ) && ( "\n" === PHP_EOL || "\r\n" === PHP_EOL ) ) ? PHP_EOL : self::eol();
		$DS						= ( defined( 'DIRECTORY_SEPARATOR' ) && ( '/' === DIRECTORY_SEPARATOR || '\\' === DIRECTORY_SEPARATOR ) ) ? DIRECTORY_SEPARATOR : self::ds();
		$PS						= ( defined( 'PATH_SEPARATOR' ) && ( ':' === PATH_SEPARATOR || ';' === PATH_SEPARATOR ) ) ? PATH_SEPARATOR : self::ps();
		$PHP_MEM_LIMIT			= self::format_bytes( ini_get( 'memory_limit' ) );
		$CONTENT_DIR_URL		= WP_CONTENT_URL;
		$CONTENT_DIR_PATH		= WP_CONTENT_DIR;
		$PLUGINS_DIR_URL		= WP_PLUGIN_URL;
		$PLUGINS_DIR_PATH		= WP_PLUGIN_DIR;
		$PLUGIN_BASENAME		= self::$plugin_basename = plugin_basename( __FILE__ );
		$PLUGIN_NAME			= self::$plugin_name = trim( dirname( $PLUGIN_BASENAME ), $DS );
		$PLUGIN_URL				= untrailingslashit( plugin_dir_url( __FILE__ ) );
		$PLUGIN_PATH			= untrailingslashit( plugin_dir_path( __FILE__ ) );
		$ADMIN_URL				= untrailingslashit( admin_url() );
		$SITE_URL				= untrailingslashit( strtolower( home_url() ) );
		$SITE_DOMAIN			= self::get_domain( $SITE_URL );
		$ADMIN_AJAX_URL			= $ADMIN_URL.'/admin-ajax.php';
		$PLUGIN_ADMIN_URL		= $ADMIN_URL.'/tools.php?page='.$PLUGIN_NAME;
		$PLUGIN_CSS_URL			= $PLUGIN_URL.'/css';
		$PLUGIN_IMG_URL			= $PLUGIN_URL.'/img';
		$PLUGIN_INCL_URL		= $PLUGIN_URL.'/includes';
		$PLUGIN_JS_URL			= $PLUGIN_URL.'/js';
		$PLUGIN_CSS_PATH		= $PLUGIN_PATH.$DS.'css';
		$PLUGIN_IMG_PATH		= $PLUGIN_PATH.$DS.'img';
		$PLUGIN_INCL_PATH		= $PLUGIN_PATH.$DS.'includes';
		$PLUGIN_JS_PATH			= $PLUGIN_PATH.$DS.'js';
		$PLUGIN_LANG_PATH		= $PLUGIN_PATH.$DS.'languages';
		$SERVER_NAME			= self::get_server_name();
		$SERVER_ADDR			= self::get_server_addr();
		$SERVER_NAME_REV		= strrev( $SERVER_NAME );
		$SERVER_HOSTNAME		= self::get_server_hostname();
		$DEBUG_SERVER_NAME		= $debug_server;
		$DEBUG_SERVER_NAME_REV	= strrev( $DEBUG_SERVER_NAME );
		$MDBUG_SERVER_NAME		= $debug_server;
		$MDBUG_SERVER_NAME_REV	= strrev( $MDBUG_SERVER_NAME );
		$DEV_URL				= self::$dev_url;
		$HOME_URL				= $DEV_URL.'plugins/'.$PLUGIN_NAME.'/';
		$SUPPORT_URL			= $DEV_URL.'plugins/'.$PLUGIN_NAME.'/support/';
		$WP_URL					= 'https://wordpress.org/plugins/'.$PLUGIN_NAME.'/';
		$WP_RATING_URL			= 'https://wordpress.org/support/plugin/'.$PLUGIN_NAME.'/reviews/';
		$DONATE_URL				= $DEV_URL.'/go/donate/'.$PLUGIN_NAME.'/';
		$GET_VAR				= 'system_diagnostic_'.self::get_url_id();
		$IS_REMOTE_VIEW			= self::is_remote_view( $GET_VAR, $SERVER_NAME, $SITE_URL );
		$constants_core			= compact( 'EOL', 'DS', 'PHP_MEM_LIMIT', 'WP_VERSION', 'CONTENT_DIR_URL', 'CONTENT_DIR_PATH', 'PLUGINS_DIR_URL', 'PLUGINS_DIR_PATH', 'PLUGIN_BASENAME', 'PLUGIN_NAME', 'PLUGIN_URL', 'PLUGIN_PATH', 'ADMIN_URL', 'SITE_URL', 'SITE_DOMAIN', 'ADMIN_AJAX_URL', 'PLUGIN_ADMIN_URL', 'PLUGIN_CSS_URL', 'PLUGIN_IMG_URL', 'PLUGIN_INCL_URL', 'PLUGIN_JS_URL', 'PLUGIN_CSS_PATH', 'PLUGIN_IMG_PATH', 'PLUGIN_INCL_PATH', 'PLUGIN_JS_PATH', 'PLUGIN_LANG_PATH', 'SERVER_NAME', 'SERVER_ADDR', 'SERVER_NAME_REV', 'SERVER_HOSTNAME', 'DEBUG_SERVER_NAME_REV', 'DEV_URL', 'HOME_URL', 'SUPPORT_URL', 'WP_URL', 'WP_RATING_URL', 'DONATE_URL', 'GET_VAR', 'IS_REMOTE_VIEW' );
		self::$ip_dns_params	= array( 'server_hostname' => $SERVER_HOSTNAME, 'server_addr' => $SERVER_ADDR, 'server_name' => $SERVER_NAME, 'domain' => $SITE_DOMAIN, );	/* Set Class Variables - Group 1 */
		/* Unset / free mem */	unset ( $EOL, $DS, $PHP_MEM_LIMIT, $WP_VERSION, $CONTENT_DIR_URL, $CONTENT_DIR_PATH, $PLUGINS_DIR_URL, $PLUGINS_DIR_PATH, $PLUGIN_URL, $PLUGIN_PATH, $ADMIN_URL, $SITE_URL, $SITE_DOMAIN, $ADMIN_AJAX_URL, $PLUGIN_ADMIN_URL, $PLUGIN_CSS_URL, $PLUGIN_IMG_URL, $PLUGIN_INCL_URL, $PLUGIN_JS_URL, $PLUGIN_CSS_PATH, $PLUGIN_IMG_PATH, $PLUGIN_INCL_PATH, $PLUGIN_JS_PATH, $PLUGIN_LANG_PATH, $SERVER_NAME, $SERVER_ADDR, $SERVER_NAME_REV, $SERVER_HOSTNAME, $DEBUG_SERVER_NAME_REV, $DEV_URL, $HOME_URL, $SUPPORT_URL, $WP_URL, $WP_RATING_URL, $DONATE_URL, $GET_VAR, $IS_REMOTE_VIEW );

		$DATE_BASIC				= 'Y-m-d';
		$DATE_FULL				= 'Y-m-d H:i:s';
		$DATE_LONG				= 'Y-m-d (D) H:i:s e';
		$OBSC_PHRASE			= '***** DATA HIDDEN FOR SECURITY *****';
		$constants_static		= compact( 'DATE_BASIC', 'DATE_FULL', 'DATE_LONG', 'OBSC_PHRASE' );
		/* Unset / free mem */	unset( $DATE_BASIC, $DATE_FULL, $DATE_LONG, $OBSC_PHRASE );

		$RGX_DOM				= "[a-z0-9\-]";
		$RGX_TLD				= "(\.[a-z]{2,3}){1,2}[a-z]*";
		$RGX_IPSTR				= "([0-9]{1,3}[x\.\-]){4}";
		$RGX_IPCSTR				= "([0-9]{1,3}[x\.\-]){2}[0-9]{1,3}";
		$RGX_IP					= "([0-9]{1,3}[x\.\-]){3}[0-9]{1,3}";
		$RGX_IPCVAL				= "(([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}";
		$RGX_IPVAL				= $RGX_IPCVAL."([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])";
		$RGX_FREEMAIL			= "((163|aim|aol|arcor|care2|fastmail|g(oogle)?mail|gmx|hotmail|hushmail|inbox|live|lycos|mail|myway|outlook|protonmail|rambler|rediffmail|yahoo|yandex|ymail|zoho(mail)?)".$RGX_TLD."|(150ml|(150|16|2|50)mail|cluemail|deliveredbysent|fast(\-email|em(ailer)?|imap|messaging)|fmailbox|fmgirl|fmguy|icloud|imap\-mail|india|inoutbox|internet\-e\-mail|mac|mail(\-central|\-page|andftp|as|bolt|can|ftp|haven|ite|might|new|reflect)|me|moose\-mail|my(fast|mac)mail|petml|postinbox|pro(inbox|message)|rocketmail|rushpost|sent|ssl\-mail|swift\-mail|the\-quickest|theinternetemail|wildmail|xsmail|your\-mail)\.com|(4email|airpost|allmail|email(corner|engine|groups|user)|fastmailbox|ftml|hailmail|internet(emails|mailing)|jetemail|justemail|mail(c|force|sent|up)|ml1|nospammail|ownmail|postpro|realemail|speedpost|the\-fastest|usa|veryspeedy|warpmail|yepmail)\.net|(123mail|elitemail|email(engine|plus)|fast\-mail|imapmail|internet\-mail|letterboxes|mail(ingaddress|works)|speedymail)\.org|(sent)\.as|(sent)\.at|(reallyfast|veryfast)\.biz|(eml|fastest|imap).cc|(fmail)\.co\.uk|(freenet|web)\.de|(f\-m)\.fm|(reallyfast)\.info|(mailservice)\.ms|(fea|mm)\.st|(bestmail|fastemail|h\-mail)\.us)";
		$constants_rgx			= compact( 'RGX_DOM', 'RGX_TLD', 'RGX_IPSTR', 'RGX_IPCSTR', 'RGX_IP', 'RGX_IPCVAL', 'RGX_IPVAL', 'RGX_FREEMAIL' );
		/* Unset / free mem */	unset( $RGX_DOM, $RGX_TLD, $RGX_IPSTR, $RGX_IPCSTR, $RGX_IP, $RGX_IPCVAL, $RGX_IPVAL, $RGX_FREEMAIL );

		/* Merge Arrays */
		$constants_to_set		= array_merge( $constants_core, $constants_static, $constants_rgx );
		/* Set Constants */
		self::define( $constants_to_set, $pref );

		/* Set Class Variables - Group 2 */


		/* Before Proceeding...Check This... */
		if( !is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) && isset( $_GET[RSSD_GET_VAR] ) && ( empty( $_GET[RSSD_GET_VAR] ) || 32 !== self::strlen( $_GET[RSSD_GET_VAR] ) ) ) { wp_redirect( home_url(), 301 ); exit; }

		/* Include Classes */
		$include_files = array( 'utils', 'browser', 'email', 'remote-viewer', );
		foreach( $include_files as $f  ) {
			require_once RSSD_PLUGIN_INCL_PATH.RSSD_DS.'class.'.$f.'.php';
		}
		unset( $include_files );

		/* Register hooks - Activation / Deactivation / Uninstall */
		register_activation_hook	(	__FILE__,	array(	__CLASS__,	'activation'	)	);
		register_deactivation_hook	(	__FILE__,	array(	__CLASS__,	'deactivation'	)	);
		register_uninstall_hook		(	__FILE__,	array(	__CLASS__,	'uninstall'		)	);

		/* Hooks to Load */
		$hooks = array();
		if( 0 === strpos( $_SERVER['QUERY_STRING'], 'system_diagnostic_' )  || is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
			$hooks = 
				array(
					array( 't' => 'type',	'h' => 'hook',									'c' => 'callback',												'p' => 'priority',	'n' => 'num_args',	),
					array( 't' => 'action',	'h' => 'admin_init',							'c' => array( __CLASS__,			'hide_cpn_notices'		),	'p' => -100,		'n' => 1,			),
					array( 't' => 'action',	'h' => 'admin_init',							'c' => array( __CLASS__,			'check_requirements'	),	'p' => 10,			'n' => 1,			),
					array( 't' => 'action',	'h' => 'admin_menu',							'c' => array( __CLASS__,			'create_admin_page'		),	'p' => 10,			'n' => 1,			),
					array( 't' => 'filter',	'h' => 'plugin_action_links_'.$PLUGIN_BASENAME,	'c' => array( __CLASS__,			'action_links'			),	'p' => 10,			'n' => 1,			),
					array( 't' => 'filter',	'h' => 'plugin_row_meta',						'c' => array( __CLASS__,			'meta_links'			),	'p' => 10,			'n' => 2,			),
					array( 't' => 'action',	'h' => 'send_headers',							'c' => array( __CLASS__,			'headers'				),	'p' => 100,			'n' => 1,			),
					array( 't' => 'action',	'h' => 'template_redirect',						'c' => array( __CLASS__.'_Viewer',	'remote_view'			),	'p' => 10,			'n' => 1,			),
					array( 't' => 'action',	'h' => 'wp_ajax_rssd_regenerate_url',			'c' => array( __CLASS__,			'generate_url'			),	'p' => 10,			'n' => 1,			),
					array( 't' => 'action',	'h' => 'wp_ajax_download_system_diagnostic',	'c' => array( __CLASS__,			'download_data'			),	'p' => 10,			'n' => 1,			),
					/* TO DO:
					array( 't' => 'filter',	'h' => 'plugins_loaded',						'c' => array( __CLASS__,			'load_languages'		),	'p' => 10,			'n' => 1,			),
					*/
				);
		}
		if( is_admin() && class_exists( 'WPEngine_PHPCompat' ) ) {
			$hooks[] =
					array( 't' => 'filter',	'h' => 'phpcompat_whitelist',					'c' => array( __CLASS__,			'php_compat'			),	'p' => 1000,		'n' => 1,			);
		}
		self::load_hooks( $hooks );

	}


	/* Common Functions - Required for RS_System_Diagnostic::setup() - BEGIN */

	/**
	 *	Get Environment Variables, or load a specific one.
	 *	Ensures compatibility with servers that aren't set to populate $_ENV[] automatically.
	 *	@dependencies	RS_System_Diagnostic::sanitize_ip(), 
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function getenv( $e = FALSE, $add_vars = array() ) {
		if( empty( self::$_ENV ) || !is_array( self::$_ENV ) ) { self::$_ENV = array(); }
		self::$_ENV = (array) self::$_ENV + (array) $_ENV;
		$vars = array( 'REMOTE_ADDR', 'SERVER_ADDR', 'LOCAL_ADDR', 'HTTP_HOST', 'SERVER_NAME', );
		$vars = !empty( $add_vars ) ? (array) $vars + (array) $add_vars : $vars;
		if( !empty( $e ) ) { $vars[] = $e; }
		foreach( $vars as $i => $k ) {
			if( empty( self::$_ENV[$k] ) ) {
				self::$_ENV[$k] = $_ENV[$k] = '';
				if( function_exists( 'getenv' ) ) {
					self::$_ENV[$k] = $_ENV[$k] = @getenv($k);
				}
				if( empty( self::$_ENV[$k] ) && !empty( $_SERVER[$k] ) ) {
					self::$_ENV[$k] = $_ENV[$k] = $_SERVER[$k];
				}
			}
			if( !empty( self::$_ENV[$k] ) && FALSE !== strpos( $k, '_ADDR' ) ) {
				self::$_ENV[$k] = $_ENV[$k] = self::sanitize_ip( self::$_ENV[$k] );
			}
		}
		unset( $i, $k );
		if( empty( self::$_ENV['LOCAL_ADDR'] ) && !empty( $_SERVER['SERVER_ADDR'] ) ) {
			self::$_ENV['LOCAL_ADDR'] = $_ENV['LOCAL_ADDR'] = $_SERVER['SERVER_ADDR'];
		}
		return FALSE !== $e ? self::$_ENV[$e] : self::$_ENV;
	}

	/**
	 *  Sanitize IP address input from $_SERVER[] vars, Forward DNS Lookups, etc.
	 *  Can extract IP address from a list of IP's, from forwarded data, remove port #, etc.
	 *  It is possible for $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_X_FORWARDED_FOR'], Forward DNS Lookups, etc., to return a list instead of a single IP
	 *  Run IP data through this function to sanitize before using in code
	 *	@dependencies	RS_System_Diagnostic::substr_count(), RS_System_Diagnostic::is_valid_ip()
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.4
	 *  @return			boolean
	 */
	static public function sanitize_ip( $ip_in ) {
		if( empty( $ip_in ) && NULL === $ip_in ) { return $ip_in; }
		if( FALSE === strpos( $ip_in, '.' ) && FALSE === strpos( $ip_in, ':' ) ) { return $ip_in; }
		$ip_in	= trim( (string) $ip_in );
		if( '127.0.0.1' === $ip_in || '0:0:0:0:0:0:0:1' === $ip_in || '::1' === $ip_in || (bool) @self::is_valid_ip( $ip_in, TRUE ) ) { return $ip_in; }
		$fwd	= array( 'for', '=', '"', );
		$tmp	= str_replace( $fwd, '', $ip_in );
		$tmp	= strtok( $tmp, ', ;' ); strtok('', '');
		if( @self::substr_count( $tmp, '.' ) > 2 ) { /* Only on IPv4, not IPv6 */
			$tmp = strtok( $tmp, ':' ); strtok('', '');
		}
		$ip_out	= trim( $tmp );
		$valid	= (bool) @self::is_valid_ip( $ip_out, TRUE );
		if( FALSE === $valid && TRUE === WP_DEBUG && TRUE === RSSD_DEBUG ) {
			@self::append_log_data( NULL, NULL, 'Error sanitizing IP address in method '.__CLASS__.'::'.__FUNCTION__.' | Original IP: '.$ip_in.' | Sanitized IP: '.$ip_out );
		}
		return ( TRUE === $valid ) ? $ip_out : $ip_in;
	}

	/**
	 *	Check if an IP is valid
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), RS_System_Diagnostic::sanitize_ip() and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.4
	 */
	static public function is_valid_ip( $ip, $incl_priv_res = FALSE, $ipv4_c_block = FALSE ) {
		if( empty( $ip ) ) { return FALSE; }
		if( !empty( $ipv4_c_block ) ) {
			if( self::preg_match( "~^(([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}$~", $ip ) ) { return TRUE; } /* Valid C-Block check - checking for C-block: '123.456.78.' format */
		}
		if( function_exists( 'filter_var' ) ) {
			if( empty( $incl_priv_res ) ) { if( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) { return TRUE; } }
			elseif( filter_var( $ip, FILTER_VALIDATE_IP ) ) { return TRUE; }
			/* FILTER_FLAG_IPV4,FILTER_FLAG_IPV6,FILTER_FLAG_NO_PRIV_RANGE,FILTER_FLAG_NO_RES_RANGE */
		} elseif( self::preg_match( "~^(([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])$~", $ip ) && !preg_match( "~^192\.168\.~", $ip ) ) { return TRUE; }
		return FALSE;
	}

	static public function is_google_ip( $ip ) {
		/**
		 *  Check if IP is a Google IP
		 *  @since		1.7.8
		 */
		if( self::preg_match( "~^(64\.233\.1([6-8][0-9]|9[0-1])|66\.102\.([0-9]|1[0-5])|66\.249\.(6[4-9]|[7-8][0-9]|9[0-5])|72\.14\.(19[2-9]|2[0-4][0-9]|25[0-5])|74\.125\.([0-9]|[0-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])|209\.85\.(1(2[8-9]|[3-9][0-9])|2[0-4][0-9]|25[0-5])|216\.239\.(3[2-9]|[4-5][0-9]|6[0-3]))\.~", $ip ) ) { return TRUE; }
		return FALSE;
	}

	static public function is_opera_ip( $ip ) {
		/**
		 *  Check if IP is an Opera IP
		 *  @since		1.9.8.3
		 */
		if( self::preg_match( "~^(37\.228\.1(0[4-9]|1[01])|82\.145\.2(0[89]|1[0-9]|2[0-3])|91\.203\.9[6-9]|107\.167\.(9[6-9]|1([01][0-9]|2[0-6]))|141\.0\.([89]|1[0-5])|185\.26\.18[0-3]|195\.189\.14[23])\.~", $ip ) ) { return TRUE; }
		return FALSE;
	}

	/**
	 *  Detect and set correct End of Line character
	 *  For cross-platform compatibility, especially with certain server setups where PHP_EOL constant is not always set.
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function eol() {
		global $is_IIS; return !empty( $is_IIS ) ? "\r\n" : "\n";
	}

	/**
	 *  Detect and set correct Directory Separator character
	 *  For cross-platform compatibility, especially with certain server setups where DIRECTORY_SEPARATOR constant is not always set.
	 *  Even if OS will interpret either correctly when receiving input from script,
	 *  	it is still best practice for correct processing of strings returned by OS.
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function ds() {
		global $is_IIS; return !empty( $is_IIS ) ? '\\' : '/';
	}

	/**
	 *  Detect and set correct Path Separator character for cross-platform compatibility.
	 *  PATH_SEPARATOR is used in configuration settings (php.ini, etc.) and OS environment variables to separate paths
	 *  	Ex: 'open_basedir' and 'include_path' settings in php.ini use ":" to separate paths on ~nix systems.
	 *  	Ex: 'set_include_path' function will only be cross-platform compatible if PATH_SEPARATOR is used instead of ":" or ";".
	 *  For cross-platform compatibility, especially with certain server setups where DIRECTORY_SEPARATOR constant is not always set.
	 *  Even if OS will interpret either correctly when receiving input from script,
	 *  	it is still best practice for correct processing of strings returned by OS.
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function ps() {
		global $is_IIS; return !empty( $is_IIS ) ? ';' : ':';
	}

	/**
	 *	Replacement for PHP function strlen()
	 *	Use this function instead of mb_strlen because some servers (often IIS, esp. older versions) do not have multibyte extension loaded,
	 *		or have mb_ functions disabled by default.
	 *	NOTE: mb_strlen is superior to strlen, so use it whenever possible.
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function strlen( $string ) {
		return function_exists( 'mb_strlen' ) ? mb_strlen( $string, 'UTF-8' ) : strlen( $string );
	}

	/**
	 *	Drop-in replacement for PHP function substr_count()
	 *	Use this function instead of substr_count() because this has error correction built-in, whereas the native function does not.
	 *	@reference		http://php.net/manual/en/function.substr-count.php
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::sanitize_ip(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.8
	 */
	static public function substr_count( $haystack, $needle, $offset = 0, $length = NULL ) {
		$haystack_len = self::strlen( $haystack );
		$needle_len = self::strlen( $needle );
		if( $offset >= $haystack_len || $offset < 0 ) { $offset = 0; }
		if( empty( $length ) || $length <= 0 ) { $length = $haystack_len; }
		$haystack_len_offset_diff = $haystack_len - $offset;
		if( $length > $haystack_len_offset_diff ) { $length = $haystack_len_offset_diff; }
		$needle_instances = 0;
		if( !empty( $needle ) && !empty( $haystack ) && $needle_len <= $haystack_len ) {
			$needle_instances = substr_count( $haystack, $needle, $offset, $length );
		}
		return $needle_instances;
	}

	/**
	 *  Format number of bytes into KB, MB, GB, TB
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), wp_memory_used(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 *	@return			string
	 */
	static public function format_bytes( $size, $precision = 2 ) {
		if( !is_numeric( $size ) || empty( $size ) ) { return $size; }
		$base		= log($size) / log(1024);
		$base_floor = floor($base);
		$suffixes	= array('', 'k', 'M', 'G', 'T');
		$suffix		= isset( $suffixes[$base_floor] ) ? $suffixes[$base_floor] : '';
		if( empty($suffix) ) { return $size; }
		$formatted_num = round(pow(1024, $base - $base_floor), $precision) . $suffix;
		return $formatted_num;
	}

	/**
	 *	Fix poorly formed URLs to prevent issues (throwing errors, etc.) with a number of functions and scripts that may not do proper validation.
	 *	@dependencies	none
	 *	@used by		RS_System_Diagnostic::setup(), get_domain(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function fix_url( $url = NULL, $rem_frag = FALSE, $rem_query = FALSE, $rev = FALSE ) {
		if( empty( $url ) ) { return ''; }
		$url = trim( $url );
		/* Too many forward slashes or colons after http */
		$url = preg_replace( "~^(https?)\:+/+~i", "$1://", $url);
		/* Too many dots */
		$url = preg_replace( "~\.+~i", ".", $url);
		/* Too many slashes after the domain */
		$url = preg_replace( "~([a-z0-9]+)/+([a-z0-9]+)~i", "$1/$2", $url);
		/* Remove fragments */
		if( !empty( $rem_frag ) && strpos( $url, '#' ) !== FALSE ) { $url_arr = explode( '#', $url ); $url = $url_arr[0]; }
		/* Remove query string completely */
		if( !empty( $rem_query ) && strpos( $url, '?' ) !== FALSE ) { $url_arr = explode( '?', $url ); $url = $url_arr[0]; }
		/* Reverse */
		if( !empty( $rev ) ) { $url = strrev($url); }
		return $url;
	}

	/**
	 *	Get domain from URL
	 *	@validation		Filter out URLs with nothing after http.
	 *	@dependencies	RS_System_Diagnostic::fix_url()
	 *	@used by		RS_System_Diagnostic::setup(), and others
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function get_domain( $url ) {
		if( empty( $url ) || self::preg_match( "~^https?\:*/*$~i", $url ) ) { return ''; }
		/* Fix poorly formed URLs so as not to throw errors when parsing */
		$url = self::fix_url( $url );
		/* NOW start parsing */
		$parsed = @parse_url( $url );
		/* Filter URLs with no domain */
		if( empty( $parsed['host'] ) ) { return ''; }
		$domain = strtolower( $parsed['host'] );
		return $domain;
	}

	static public function get_server_name() {
		if(		!empty( $_SERVER['HTTP_HOST'] ) )		{ $server_name = $_SERVER['HTTP_HOST']; }
		elseif(	!empty( self::$_ENV['HTTP_HOST'] ) )	{ $server_name = $_SERVER['HTTP_HOST'] = self::$_ENV['HTTP_HOST']; }
		elseif(	!empty( $_SERVER['SERVER_NAME'] ) )		{ $server_name = $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME']; }
		elseif(	!empty( self::$_ENV['SERVER_NAME'] ) )	{ $server_name = $_SERVER['HTTP_HOST'] = $_SERVER['SERVER_NAME'] = self::$_ENV['SERVER_NAME']; }
		return	!empty( $server_name ) && '.' !== trim( $server_name ) ? strtolower( $server_name ) : '';
	}

	static public function get_server_addr() {
		if(		!empty( $_SERVER['SERVER_ADDR'] ) )		{ $server_addr = $_SERVER['SERVER_ADDR']; }
		elseif(	!empty( self::$_ENV['SERVER_ADDR'] ) )	{ $server_addr = $_SERVER['SERVER_ADDR'] = self::$_ENV['SERVER_ADDR']; }
		elseif(	!empty( $_SERVER['LOCAL_ADDR'] ) )		{ $server_addr = $_SERVER['SERVER_ADDR'] = $_SERVER['LOCAL_ADDR']; }
		elseif(	!empty( self::$_ENV['LOCAL_ADDR'] ) )	{ $server_addr = $_SERVER['SERVER_ADDR'] = $_SERVER['LOCAL_ADDR'] = self::$_ENV['LOCAL_ADDR']; }
		return	!empty( $server_addr ) ? $server_addr : '';
	}

	static public function get_server_hostname( $sanitize = FALSE, $server_hostname = NULL ) {
		if( FALSE === $sanitize || empty( $server_hostname ) ) {
			$server_hostname = function_exists( 'php_uname' ) ? @php_uname('n') : @gethostname();
		}
		if( TRUE === $sanitize ) {
			$server_hostname = ( self::strlen( $server_hostname ) < 6 || FALSE === strpos( $server_hostname, '.' ) ) ? '' : $server_hostname;
		}
		return $server_hostname;
	}

	/**
	 *  Drop in replacement for PHP function preg_match(), with built-in error correction
	 *  Disables error suppression when WP_DEBUG is enabled
	 *  Can use for both general purpose and for debugging
	 *	@dependencies	RS_System_Diagnostic::append_log_data()
	 *	@used by		..., WPSS
	 *	@since			RSSD 1.0.8, WPSS 1.9.9.8.7
	 */
	static public function preg_match( $pattern, $subject, &$matches = NULL, $flags = 0, $offset = 0 ) {
		$pattern_rev = ltrim( strrev( $pattern ), "eimsxuADJSUX" ); /* trim off PCRE Regex modifier flags */
		if( !is_string( $pattern ) || !is_string( $subject ) || FALSE === strpos( $pattern, '~' ) || 0 !== strpos( $pattern, '~' )  || 0 !== strpos( $pattern_rev, '~' ) ) {
			@self::append_log_data( NULL, NULL, 'Error in regex pattern: '.$pattern );
			return FALSE;
		}
		return ( TRUE === WP_DEBUG ) ? preg_match( $pattern, $subject, $matches, $flags, $offset ) : @preg_match( $pattern, $subject, $matches, $flags, $offset );
	}

	/**
	 *	Replacement for PHP function define(), with built-in conditional check.
	 *	Define one or more named constants if not already set.
	 *	Input an associative array of $name/$value pair(s) to be defined as one or more constants.
	 *	If $pref is supplied, the constant(s) will be prefixed.
	 *	@dependencies	none
	 *	@param			array	$const	Array of name(string)/value(bool|string) pairs to define
	 *	@param			string	$pref	Prefix
	 *	@param			bool	$cond	Conditional? Default = TRUE
	 *	@used by		RS_System_Diagnostic::setup()
	 *	@since			1.0.9
	 */
	static protected function define( $const = array(), $pref = NULL ) {
		if( empty( $const ) || !is_array( $const ) ) { return; }
		foreach( $const as $name => $value ) {
			$name = trim( $pref.$name );
			if( !defined( $name ) ) {
				define( $name, $value );
			}
		}
	}

	/**
	 *	Drop in replacement for PHP function constant(), with built-in error check.
	 *	The constant will be prefixed with class $pref variable.
	 *	@dependencies	none
	 *	@param			string	$name	The constant name.
	 *	@used by		...
	 *	@since			1.0.9
	 */
	static protected function constant( $name ) {
		return ( empty( $name ) || !is_string( $name ) ) ? '' : constant( self::$pref.$name );
	}

	/**
	 *	Load hooks
	 *	@dependencies	none
	 *	@param			array	$hooks	Array of hook data to load
	 *	@used by		RS_System_Diagnostic::setup()
	 *	@since			1.0.9
	 */
	static protected function load_hooks( $hooks ) {
		if( empty( $hooks ) || !is_array( $hooks ) ) { return; }
		foreach( $hooks as $i => $v ) {
			extract( $v );
			if( 'action' !== $t && 'filter' !== $t ) { continue; }
			add_filter( $h, $c, $p, $n );
		}
	}

	/* Common Functions - Required for RS_System_Diagnostic::setup() - END */



	/* Common Functions - BEGIN */

	/**
	 *	Check if current install is a specific WordPress version or later, for compatibility checks, etc
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.3
	 */
	static public function is_wp_ver( $ver ) {
		return version_compare( self::$wp_ver, $ver, '>=' );
	}

	/**
	 *  Check if current install is a specific PHP version or later, for compatibility checks, etc
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.4
	 */
	static public function is_php_ver( $ver ) {
		return version_compare( PHP_VERSION, $ver, '>=' );
	}

	static public function load_languages() {
		load_plugin_textdomain( self::$plugin_name, FALSE, basename( dirname( __FILE__ ) ) . '/languages' );
	}

	/**
	 *  Detect https/http
	 *  Use instead of WP function is_ssl(), as this is more accurate
	 *  @dependencies	none
	 *  @used by		RS_System_Diagnostic::get_url()
	 *  @since			...
	 */
	static public function is_https() {
		if( !empty( $_SERVER['HTTPS'] )						&& 'off'	!==	$_SERVER['HTTPS'] )						{ return TRUE; }
		if( !empty( $_SERVER['SERVER_PORT'] )				&& '443'	 ==	$_SERVER['SERVER_PORT'] )				{ return TRUE; }
		if( !empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] )	&& 'https'	===	$_SERVER['HTTP_X_FORWARDED_PROTO'] )	{ return TRUE; }
		if( !empty( $_SERVER['HTTP_X_FORWARDED_SSL'] )		&& 'off'	!==	$_SERVER['HTTP_X_FORWARDED_SSL'] )		{ return TRUE; }
		return FALSE;
	}

	/**
	 *  Get the URL of current page/post/etc
	 *	@dependencies	RS_System_Diagnostic::is_https()
	 *	@used by		constant 'RSSD_THIS_URL'
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function get_url( $safe = FALSE, $server_name = RSSD_SERVER_NAME ) {
		$url  = self::is_https() ? 'https://' : 'http://';
		$url .= $server_name.$_SERVER['REQUEST_URI'];
		if( TRUE === $safe ) { $url = esc_url( $url ); }
		return $url;
	}

	/**
	 *  Make URL schemeless / protocol-relative
	 *  Use judiciously to prevent SEO + proxy issues
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.6
	 */
	static public function get_schemeless_url( $url ) {
		return str_replace( array( 'https://', 'http://' ), '//', $url );
	}

	/**
	 *	Get email domain for use in email addresses
	 *	Strip 'www.' & 'm.' from beginning of domain
	 */
	static public function get_email_domain( $domain ) {
		if( empty( $domain ) ) { return ''; }
		$domain = preg_replace( "~^(ww[w0-9]|m)\.~i", '', $domain );
		return $domain;
	}

	/**
	 *  Get query string from URL
	 *	@validation		Filter out URLs with nothing after http.
	 *	@dependencies	RS_System_Diagnostic::fix_url(), 
	 *	@used by		get_query_args(), 
	 *	@notes			Function must be defined in main class, not includes or child classes
	 *	@since			1.0.0
	 */
	static public function get_query_string( $url ) {
		if( empty( $url ) || self::preg_match( "~^https?\:*/*$~i", $url ) ) { return ''; }
		/* Fix poorly formed URLs so as not to throw errors when parsing */
		$url = self::fix_url( $url );
		/* NOW start parsing */
		$parsed = @parse_url($url);
		/* Filter URLs with no query string */
		if( empty( $parsed['query'] ) ) { return ''; }
		$query_str = $parsed['query'];
		return $query_str;
	}

	/**
	 *	Get query string array from URL
	 */
	static public function get_query_args( $url ) {
		if( empty( $url ) ) { return array(); }
		$query_str = self::get_query_string( $url );
		parse_str( $query_str, $args );
		return $args;
	}

	/**
	 *  Get IP/DNS Params
	 *	@dependencies	RS_System_Diagnostic::get_server_hostname(), RS_System_Diagnostic::get_server_addr(), RS_System_Diagnostic::get_domain(), 
	 *  @used by		RS_System_Diagnostic::get_url_id(), RS_System_Diagnostic::get_web_host(), RS_System_Diagnostic::get_web_proxy(),
	 *  @since			1.0.6
	 */
	static public function get_ip_dns_params() {
		self::$ip_dns_params =
			array(
				'server_hostname'	=> self::get_server_hostname(),
				'server_addr'		=> self::get_server_addr(),
				'server_name'		=> self::get_server_name(),
				'domain'			=> self::get_domain( untrailingslashit( strtolower( home_url() ) ) ),
			);
		return self::$ip_dns_params;
	}

	/**
	 *	Attempt to detect and identify web host
	 *	As of RSSD.20170317.01, web hosts detected: 97+
	 *	@dependencies	RS_System_Diagnostic::get_option(), RS_System_Diagnostic::update_option(), RS_System_Diagnostic::get_server_hostname(), RS_System_Diagnostic::get_ip_dns_params(), RS_System_Diagnostic::get_reverse_dns(), RS_System_Diagnostic::is_valid_ip(), RS_System_Diagnostic::get_ns(), RS_System_Diagnostic::sort_unique(), 
	 *	@used by		...
	 *	@func_ver		RSSD.20170317.01
	 *	@since			RSSD 1.0.3
	 */
	static public function get_web_host( $params = array() ) {
		if( !empty( self::$web_host ) ) { return self::$web_host; }
		self::$web_host = self::get_option( 'web_host' );
		if( !empty( self::$web_host ) ) { return self::$web_host; }
		if( empty( $params ) || !is_array( $params ) ) { $params = self::get_ip_dns_params(); }
		extract( $params );
		self::$web_host					= FALSE;
		$server_hostname				= ( !empty( $server_hostname ) ) ? self::get_server_hostname( TRUE, $server_hostname ) : '';
		/* $_SERVER and $_ENV Variables */
		$web_hosts_ev = array(
			'DreamHost'					=> array( 'slug' => 'dreamhost', 'webhost' => 'DreamHost', 'envars' => 'DH_USER', 'deps' => 'ABSPATH', ), 
			'GoDaddy'					=> array( 'slug' => 'godaddy', 'webhost' => 'GoDaddy', 'envars' => 'GD_PHP_HANDLER,GD_ERROR_DOC', ), 
			'WP Engine'					=> array( 'slug' => 'wp-engine', 'webhost' => 'WP Engine', 'envars' => 'IS_WPE', ), 
		);
		/* PHP Constants */
		$web_hosts_cn = array(
			'Pagely'					=> array( 'slug' => 'pagely', 'webhost' => 'Pagely', 'constants' => 'PAGELYBIN', ),
			'WP Engine'					=> array( 'slug' => 'wp-engine', 'webhost' => 'WP Engine', 'constants' => 'WPE_APIKEY', ),
		);
		/* Classes */
		$web_hosts_cl = array(
			'WP Engine'					=> array( 'slug' => 'wp-engine', 'webhost' => 'WP Engine', 'classes' => 'WPE_API,WpeCommon', ),
		);
		/**
		 *	Strings
		 *	Nameservers, Internal Server Names, or RevDNS of Website IP
		 *	Test $site_ns, $server_hostname, & $server_rev_dns
		 */
		$web_hosts_st = array(
			'100TB'						=> array( 'slug' => '100tb', 'webhost' => '100TB', 'domains' => '100tb.com', 'parent' => 'uk2', ), 
			'1and1 Internet'			=> array( 'slug' => '1and1', 'webhost' => '1and1 Internet', 'domains' => '1and1.co.uk,1and1-dns.biz,1and1-dns.com,1and1-dns.de,1and1-dns.org', ), 
			'A Small Orange'			=> array( 'slug' => 'a-small-orange', 'webhost' => 'A Small Orange', 'domains' => 'asmallorange.com,asodns.com,asonoc.com,asoshared.com', ), 
			'A2 Hosting'				=> array( 'slug' => 'a2-hosting', 'webhost' => 'A2 Hosting', 'domains' => 'a2hosting.com', ), 
			'Altervista'				=> array( 'slug' => 'altervista', 'webhost' => 'Altervista', 'domains' => 'altervista.com,altervista.org,altervista.it', 'tags' => 'freehost' ), 
			'Amazon Web Services (AWS)'	=> array( 'slug' => 'amazon-aws', 'webhost' => 'Amazon Web Services (AWS)', 'domains' => 'amazonaws.com', ), 
			'Amen'						=> array( 'slug' => 'amen', 'webhost' => 'Amen', 'domains' => 'amen.fr', ), 
			'Arvixe'					=> array( 'slug' => 'arvixe', 'webhost' => 'Arvixe', 'domains' => 'arvixe.com,arvixeshared.com,arvixevps.com', ), 
			'Automattic'				=> array( 'slug' => 'automattic', 'webhost' => 'Automattic', 'domains' => 'automattic.com', ), 
			'BigScoots'					=> array( 'slug' => 'bigscoots', 'webhost' => 'BigScoots', 'domains' => 'bigscoots.com', ), 
			'Bluehost'					=> array( 'slug' => 'bluehost', 'webhost' => 'Bluehost', 'domains' => 'bluehost.com', 'tags' => 'top' ), 
			'Cloudways'					=> array( 'slug' => 'cloudways', 'webhost' => 'Cloudways', 'domains' => 'cloudways.,cloudwaysapps.', ), 
			'Cogeco Peer 1'				=> array( 'slug' => 'cogeco-peer-1', 'webhost' => 'Cogeco Peer 1', 'domains' => 'peer1.net', ), 
			'ColoCrossing'				=> array( 'slug' => 'colocrossing', 'webhost' => 'ColoCrossing', 'domains' => 'colocrossing.com,vsnx.net', ), 
			'DigitalOcean'				=> array( 'slug' => 'digitalocean', 'webhost' => 'DigitalOcean', 'domains' => 'digitalocean.com', ), 
			'Doteasy'					=> array( 'slug' => 'doteasy', 'webhost' => 'Doteasy', 'domains' => 'doteasy.com', ), 
			'DreamHost'					=> array( 'slug' => 'dreamhost', 'webhost' => 'DreamHost', 'domains' => 'dreamhost.com', ), 
			'eHost'						=> array( 'slug' => 'ehost', 'webhost' => 'eHost', 'domains' => 'ehost.com', ), 
			'Enzu'						=> array( 'slug' => 'enzu', 'webhost' => 'Enzu', 'domains' => 'scalabledns.com', ), 
			'EuHost'					=> array( 'slug' => 'euhost', 'webhost' => 'EuHost', 'domains' => 'euhost.co.uk', ), 
			'eUKhost'					=> array( 'slug' => 'eukhost', 'webhost' => 'eUKhost', 'domains' => 'eukhost.com', ), 
			'Fasthosts'					=> array( 'slug' => 'fasthosts', 'webhost' => 'Fasthosts', 'domains' => 'fast-hosts.org,fasthosts.co.uk,fasthosts.net.uk', ), 
			'FatCow'					=> array( 'slug' => 'fatcow', 'webhost' => 'FatCow', 'domains' => 'fatcow.com', ), 
			'Flywheel'					=> array( 'slug' => 'flywheel', 'webhost' => 'Flywheel', 'domains' => 'flywheelsites.com', ), 
			'Gandi'						=> array( 'slug' => 'gandi', 'webhost' => 'Gandi', 'domains' => 'gandi.net', ), 
			'Globat'					=> array( 'slug' => 'globat', 'webhost' => 'Globat', 'domains' => 'dnsjunction.com,globat.com', ), 
			'GlowHost'					=> array( 'slug' => 'glowHost', 'webhost' => 'GlowHost', 'domains' => 'glowhost.com', ), 
			'GoDaddy'					=> array( 'slug' => 'godaddy', 'webhost' => 'GoDaddy', 'domains' => 'godaddy.com,secureserver.net', ), 
			'Google Cloud Platform'		=> array( 'slug' => 'google-cloud', 'webhost' => 'Google Cloud Platform', 'domains' => 'bc.googleusercontent.com,googledomains.com,googleusercontent.com', ), 
			'GreenGeeks'				=> array( 'slug' => 'greengeeks', 'webhost' => 'GreenGeeks', 'domains' => 'greengeeks.com', ), 
			'Heart Internet'			=> array( 'slug' => 'heart-internet', 'webhost' => 'Heart Internet', 'domains' => 'heartinternet.co.uk,heartinternet.uk', ), 
			'Hetzner'					=> array( 'slug' => 'hetzner', 'webhost' => 'Hetzner', 'domains' => 'hetzner.,host-h.net,your-server.de', ), 
			'HostDime'					=> array( 'slug' => 'hostdime', 'webhost' => 'HostDime', 'domains' => 'dimenoc.com', ), 
			'HostEurope'				=> array( 'slug' => 'hosteurope', 'webhost' => 'HostEurope', 'domains' => 'hosteurope.de', ), 
			'HostGator'					=> array( 'slug' => 'hostgator', 'webhost' => 'HostGator', 'domains' => 'hostgator.com,websitewelcome.com', 'tags' => 'top' ), 
			'HostIndia.net'				=> array( 'slug' => 'hostindia', 'webhost' => 'HostIndia.net', 'domains' => 'hostindia.net', ), 
			'HostingCentre'				=> array( 'slug' => 'hostingcentre', 'webhost' => 'HostingCentre', 'domains' => 'hostingcentre.in', ), 
			'HostingRaja'				=> array( 'slug' => 'hostingraja', 'webhost' => 'HostingRaja', 'domains' => 'hostingraja.in', ), 
			'HostMetro'					=> array( 'slug' => 'hostmetro', 'webhost' => 'HostMetro', 'domains' => 'hostmetro.com', ), 
			'HostMonster'				=> array( 'slug' => 'hostmonster', 'webhost' => 'HostMonster', 'domains' => 'hostmonster.com', ), 
			'HostNine'					=> array( 'slug' => 'hostnine', 'webhost' => 'HostNine', 'domains' => 'hostnine.com', ), 
			'HostPapa'					=> array( 'slug' => 'hostpapa', 'webhost' => 'HostPapa', 'domains' => 'hostpapa.com', ), 
			'Hostway'					=> array( 'slug' => 'hostway', 'webhost' => 'Hostway', 'domains' => 'hostway.net', ), 
			'Hostwinds'					=> array( 'slug' => 'hostwinds', 'webhost' => 'Hostwinds', 'domains' => 'hostwinds.com,hostwindsdns.com', ), 
			'Infomaniak'				=> array( 'slug' => 'infomaniak', 'webhost' => 'Infomaniak', 'domains' => 'infomaniak.ch', ), 
			'InMotion Hosting'			=> array( 'slug' => 'inmotion-hosting', 'webhost' => 'InMotion Hosting', 'domains' => 'inmotionhosting.com', 'tags' => 'top' ), 
			'IO Zoom'					=> array( 'slug' => 'io-zoom', 'webhost' => 'IO Zoom', 'domains' => 'iozoom.com', ), 
			'iPage'						=> array( 'slug' => 'ipage', 'webhost' => 'iPage', 'domains' => 'ipage.com', ), 
			'IPOWER'					=> array( 'slug' => 'ipower', 'webhost' => 'IPOWER', 'domains' => 'ipower.com,ipowerdns.com,ipowerweb.net', ), 
			'IX Web Hosting'			=> array( 'slug' => 'ix-web-hosting', 'webhost' => 'IX Web Hosting', 'domains' => 'cloudbyix.com,cloudix.com,ecommerce.com,hostexcellence.com,ixwebhosting.com,ixwebsites.com,opentransfer.com,webhost.biz', 'parent' => 'Ecommerce Corporation', ), 
			'JustHost'					=> array( 'slug' => 'justhost', 'webhost' => 'JustHost', 'domains' => 'justhost.com', ), 
			'LeaseWeb'					=> array( 'slug' => 'leaseweb', 'webhost' => 'LeaseWeb', 'domains' => 'leaseweb.com,leaseweb.net,leaseweb.nl,lswcdn.com', ), 
			'Linode'					=> array( 'slug' => 'linode', 'webhost' => 'Linode', 'domains' => 'linode.com', ), 
			'Liquid Web'				=> array( 'slug' => 'liquid-web', 'webhost' => 'Liquid Web', 'domains' => 'liquidweb.com', ), 
			'Lunarpages'				=> array( 'slug' => 'lunarpages', 'webhost' => 'Lunarpages', 'domains' => 'lunarfo.com,lunarpages.com,lunarservers.com', ), 
			'Media Temple'				=> array( 'slug' => 'media-temple', 'webhost' => 'Media Temple', 'domains' => 'mediatemple.com,mediatemple.net', ), 
			'Microsoft Azure'			=> array( 'slug' => 'microsoft-azure', 'webhost' => 'Microsoft Azure', 'domains' => 'azuredns-cloud.net,azurewebsites.net', ),
			'Midphase'					=> array( 'slug' => 'midphase', 'webhost' => 'Midphase', 'domains' => 'midphase.com,us2.net', 'parent' => 'uk2', ),
			'My Wealthy Affiliate'		=> array( 'slug' => 'my-wealthy-affiliate', 'webhost' => 'My Wealthy Affiliate', 'domains' => 'mywahosting.com', ), 
			'MyHosting.com'				=> array( 'slug' => 'myhosting', 'webhost' => 'MyHosting.com', 'domains' => 'myhosting.com', ), 
			'NetFirms'					=> array( 'slug' => 'netfirms', 'webhost' => 'NetFirms', 'domains' => 'netfirms.com', ), 
			'Nexcess'					=> array( 'slug' => 'nexcess', 'webhost' => 'Nexcess', 'domains' => 'nexcess.net', ), 
			'NFrance'					=> array( 'slug' => 'nfrance', 'webhost' => 'NFrance', 'domains' => 'slconseil.com', ), 
			'Omnis'						=> array( 'slug' => 'omnis', 'webhost' => 'Omnis', 'domains' => 'omnis.com,omnisdns.net', ), 
			'One.com'					=> array( 'slug' => 'one-com', 'webhost' => 'One.com', 'domains' => 'b-one.net,b-one-dns.net,one.com', ), 
			'Online.net'				=> array( 'slug' => 'online-net', 'webhost' => 'Online.net', 'domains' => 'online.net,poneytelecom.eu', ), 
			'OVH Hosting'				=> array( 'slug' => 'ovh-hosting', 'webhost' => 'OVH Hosting', 'domains' => 'anycast.me,ovh.co.uk,ovh.com,ovh.net', ), 
			'Pagely'					=> array( 'slug' => 'pagely', 'webhost' => 'Pagely', 'domains' => 'pagely.com', ), 
			'Pair Networks'				=> array( 'slug' => 'pair-networks', 'webhost' => 'Pair Networks', 'domains' => 'ns0.com,pair.com', ), 
			'PHPNET'					=> array( 'slug' => 'phpnet', 'webhost' => 'PHPNET', 'domains' => 'phpnet.org', ), 
			'PlusServer'				=> array( 'slug' => 'plusserver', 'webhost' => 'PlusServer', 'domains' => 'plusserver.com', ), 
			'PowWeb'					=> array( 'slug' => 'powweb', 'webhost' => 'PowWeb', 'domains' => 'powweb.com', ), 
			'Pressable'					=> array( 'slug' => 'pressable', 'webhost' => 'Pressable', 'domains' => 'zippykid.com', ), 
			'QuadraNet'					=> array( 'slug' => 'quadranet', 'webhost' => 'QuadraNet', 'domains' => 'quadranet.com', ), 
			'Rackspace'					=> array( 'slug' => 'rackspace', 'webhost' => 'Rackspace', 'domains' => 'hostingmatrix.net,rackspace.com,stabletransit.com', ), 
			'Register.com'				=> array( 'slug' => 'register-com', 'webhost' => 'Register.com', 'domains' => 'register.com', ), 
			'SingleHop'					=> array( 'slug' => 'singlehop', 'webhost' => 'SingleHop', 'domains' => 'singlehop.com', ), 
			'Site5'						=> array( 'slug' => 'site5', 'webhost' => 'Site5', 'domains' => 'site5.com', ), 
			'SiteGround'				=> array( 'slug' => 'siteground', 'webhost' => 'SiteGround', 'domains' => 'siteground.', 'tags' => 'top' ), 
			'SiteRubix'					=> array( 'slug' => 'siterubix', 'webhost' => 'SiteRubix', 'domains' => 'siterubix.com', 'parent' => 'my-wealthy-affiliate', ), 
			'SoftLayer'					=> array( 'slug' => 'softlayer', 'webhost' => 'SoftLayer', 'domains' => 'networklayer.com,static.sl-reverse.com,softlayer.net', ), 
			'Superb'					=> array( 'slug' => 'superb', 'webhost' => 'Superb', 'domains' => 'superb.net', ), 
			'Triple C Cloud Computing'	=> array( 'slug' => 'triple-c', 'webhost' => 'Triple C Cloud Computing', 'domains' => 'ccc.net.il,ccccloud.com', ), 
			'UK2'						=> array( 'slug' => 'uk2', 'webhost' => 'UK2', 'domains' => 'uk2.net', ), 
			'UnoEuro'					=> array( 'slug' => 'unoeuro', 'webhost' => 'UnoEuro', 'domains' => 'unoeuro.com', ), 
			'VHosting Solution'			=> array( 'slug' => 'vhosting', 'webhost' => 'VHosting Solution', 'domains' => 'vhosting-it.com', ), 
			'VPS.net'					=> array( 'slug' => 'vps-net', 'webhost' => 'VPS.net', 'domains' => 'vps.net', 'parent' => 'uk2', ), 
			'Web Hosting Hub'			=> array( 'slug' => 'web-hosting-hub', 'webhost' => 'Web Hosting Hub', 'domains' => 'webhostinghub.com', ), 
			'Web.com'					=> array( 'slug' => 'web-com', 'webhost' => 'Web.com', 'domains' => 'web.com', ), 
			'WebFaction'				=> array( 'slug' => 'webfaction', 'webhost' => 'WebFaction', 'domains' => 'webfaction.com', ), 
			'WebHostingBuzz'			=> array( 'slug' => 'webhostingbuzz', 'webhost' => 'WebHostingBuzz', 'domains' => 'fastwhb.com,webhostingbuzz.com', ), 
			'Webs'						=> array( 'slug' => 'webs', 'webhost' => 'Webs', 'domains' => 'webs.com', ), 
			'WebSynthesis'				=> array( 'slug' => 'websynthesis', 'webhost' => 'WebSynthesis', 'domains' => 'websynthesis.com,wsynth.net', ), 
			'Weebly'					=> array( 'slug' => 'weebly', 'webhost' => 'Weebly', 'domains' => 'weebly.com', 'tags' => 'diy' ), 
			'WestHost'					=> array( 'slug' => 'westhost', 'webhost' => 'WestHost', 'domains' => 'westhost.net', 'parent' => 'uk2', ), 
			'Wix'						=> array( 'slug' => 'wix', 'webhost' => 'Wix', 'domains' => 'wix.com', 'tags' => 'diy' ), 
			'WordPress.com'				=> array( 'slug' => 'wordpress-com', 'webhost' => 'WordPress.com', 'domains' => 'wordpress.com', ), 
			'WP Engine'					=> array( 'slug' => 'wp-engine', 'webhost' => 'WP Engine', 'domains' => 'wpengine.com', ), 
		);
		/* RegEx - Nameservers, Internal Server Names, or RevDNS of Website IP - Test $site_ns, $server_hostname, & $server_rev_dns */
		$web_hosts_rg = array(
			'1and1 Internet'			=> array( 'slug' => '1and1', 'webhost' => '1and1 Internet', 'domainsrgx' => "~(^|\.)(ns[0-9]*[\.\-])?(1and1([\.\-]ui)?(\-dns)?)(\.[a-z]{2,3}){1,2}[a-z]*$~i", ), 
			'Amazon Web Services (AWS)'	=> array( 'slug' => 'amazon-aws', 'webhost' => 'Amazon Web Services (AWS)', 'domainsrgx' => "~(^|\.)ns[\.\-][0-9]+\.awsdns\-[0-9]+(\.[a-z]{2,3}){1,2}[a-z]*$~i", ), 
			'Cloudways'					=> array( 'slug' => 'cloudways', 'webhost' => 'Cloudways', 'domainsrgx' => "~(^|\.)cloudways(apps)?(\.[a-z]{2,3}){1,2}[a-z]*$~i", ), 
			'HostGator'					=> array( 'slug' => 'hostgator', 'webhost' => 'HostGator', 'domainsrgx' => "~(^|\.)(hostgator|websitewelcome)\.com~i", ), 
			'Hetzner'					=> array( 'slug' => 'hetzner', 'webhost' => 'Hetzner', 'domainsrgx' => "~(^|\.)(hetzner\.|host\-h\.net|your\-server\.de)~i", ), 
			'SiteGround'				=> array( 'slug' => 'siteground', 'webhost' => 'SiteGround', 'domainsrgx' => "~(^|\.)(siteground|sg(srv|ded|vps)|clev)([0-9]+)?(\.[a-z]{2,3}){1,2}[a-z]*$~i", ), 
			'WebHostFace'				=> array( 'slug' => 'webhostface', 'webhost' => 'WebHostFace', 'domainsrgx' => "~(^|\.)(webhost(ing)?face([a-z0-9]+)?|face(ds|reseller|shared|vps)[a-z]{2,10}[0-9]|whf(star|web))(\.[a-z]{2,3}){1,2}[a-z]*$~i", ), 
		);
		$web_hosts_ns = $web_hosts_st;

		/* Start Tests*/
		foreach( $web_hosts_ev as $wh => $data ) {
			$envars = explode( ',', $data['envars'] );
			foreach( $envars as $ev ) {
				if( empty( $_SERVER[$ev] ) ) { continue; }
				if( empty( $data['deps'] ) ) {
					self::$web_host = $data['webhost'];
				} elseif( FALSE !== strpos( $data['deps'], $_SERVER[$ev] ) ) {
					self::$web_host = $data['webhost'];
				}
			}
		}
		foreach( $web_hosts_cn as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			$constants = explode( ',', $data['constants'] );
			foreach( $constants as $cn ) {
				if( defined( $cn ) ) {
					self::$web_host = $data['webhost'];
				}
			}
		}
		foreach( $web_hosts_cl as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			$classes = explode( ',', $data['classes'] );
			foreach( $classes as $cl ) {
				if( class_exists( $cl ) ) {
					self::$web_host = $data['webhost'];
				}
			}
		}
		$server_rev_dns = self::get_reverse_dns( $server_addr );
		$server_rev_dns = ( !self::is_valid_ip( $server_rev_dns ) ) ? $server_rev_dns : ''; /* If IP, will skip the check */
		foreach( $web_hosts_st as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			if( empty( $server_hostname ) && empty( $server_rev_dns ) ) { break; }
			$domains = explode( ',', $data['domains'] );
			foreach( $domains as $st ) {
				if( !empty( $server_hostname ) && FALSE !== strpos( $server_hostname, '.'.$st ) ) {
					self::$web_host = $data['webhost'];
				} elseif( !empty( $server_rev_dns ) && FALSE !== strpos( $server_rev_dns, '.'.$st ) ) {
					self::$web_host = $data['webhost'];
				}
			}
		}
		foreach( $web_hosts_rg as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			if( empty( $server_hostname ) && empty( $server_rev_dns ) ) { break; }
			$rg = $data['domainsrgx'];
			if( !empty( $server_hostname ) && self::preg_match( $rg, $server_hostname ) ) {
				self::$web_host = $data['webhost'];
			} elseif( !empty( $server_rev_dns ) && self::preg_match( $rg, $server_rev_dns ) ) {
				self::$web_host = $data['webhost'];
			}
		}
		$site_ns = self::get_ns( $domain );
		$site_ns = ( !empty( $site_ns ) && is_array( $site_ns ) ) ? implode( '  |  ', self::sort_unique( $site_ns ) ) : 'Not Detected';
		foreach( $web_hosts_ns as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			if( empty( $site_ns ) && empty( $server_hostname ) && empty( $server_rev_dns ) ) { break; }
			$domains = explode( ',', $data['domains'] );
			foreach( $domains as $st ) {
				if( !empty( $site_ns ) && FALSE !== strpos( $site_ns, '.'.$st ) ) {
					self::$web_host = $data['webhost'];
				} elseif( !empty( $server_hostname ) && FALSE !== strpos( $server_hostname, '.'.$st ) ) {
					self::$web_host = $data['webhost'];
				} elseif( !empty( $server_rev_dns ) && FALSE !== strpos( $server_rev_dns, '.'.$st ) ) {
					self::$web_host = $data['webhost'];
				}
			}
		}
		foreach( $web_hosts_rg as $wh => $data ) {
			if( !empty( self::$web_host ) ) { break; }
			if( empty( $site_ns ) ) { break; }
			$rg = $data['domainsrgx'];
			if( !empty( $site_ns ) && self::preg_match( $rg, $site_ns ) ) {
				self::$web_host = $data['webhost']; 
			}
		}
		if( !empty( self::$web_host ) ) {
			$options = array( 'web_host' => self::$web_host, );
			self::update_option( $options );
		}
		return self::$web_host;
	}

	/**
	 *	Try to identify web host proxies: Proxies, CDNs, Web Application Firewalls (WAFs), etc.
	 *	@dependencies	RS_System_Diagnostic::get_option(), RS_System_Diagnostic::update_option(), RS_System_Diagnostic::get_server_hostname(), RS_System_Diagnostic::get_ip_dns_params(), RS_System_Diagnostic::get_reverse_dns(), RS_System_Diagnostic::is_valid_ip(), RS_System_Diagnostic::get_ns(), RS_System_Diagnostic::sort_unique(), 
	 *	@used by		...
	 *	@func_ver		RSSD.20170121.01
	 *	@since			RSSD 1.0.3
	 */
	static public function get_web_proxy( $params = array() ) {
		if( NULL !== self::$web_host_proxy ) { return self::$web_host_proxy; }
		self::$web_host_proxy = self::get_option( 'web_proxy' );
		if( NULL !== self::$web_host_proxy ) { return self::$web_host_proxy; }
		if( empty( $params ) || !is_array( $params ) ) { $params = self::get_ip_dns_params(); }
		extract( $params );
		self::$web_host_proxy			= FALSE;
		$server_hostname				= ( !empty( $server_hostname ) ) ? self::get_server_hostname( TRUE, $server_hostname ) : '';
		$server_rev_dns					= self::get_reverse_dns( $server_addr );
		$server_rev_dns					= ( !self::is_valid_ip( $server_rev_dns ) ) ? $server_rev_dns : ''; /* If IP, will skip the check */
		/* $_SERVER and $_ENV Variables */
		$web_proxies_ev = array(
			'Cloudflare'				=> array( 'slug' => 'cloudflare', 'webproxy' => 'Cloudflare', 'envars' => 'HTTP_CF_IPCOUNTRY,HTTP_CF_RAY,HTTP_CF_VISITOR', ), 
			'Incapsula'					=> array( 'slug' => 'incapsula', 'webproxy' => 'Incapsula', 'envars' => 'HTTP_INCAP_CLIENT_IP', ), 
			'Sucuri CloudProxy'			=> array( 'slug' => 'sucuri-cloudproxy', 'webproxy' => 'Sucuri CloudProxy', 'envars' => 'HTTP_X_SUCURI_CLIENTIP', ), 
		);
		$web_proxies_px = array(			/* Proxies, CDNs, Web Application Firewalls (WAFs), etc. - Test $site_ns, $server_hostname, & $server_rev_dns */
			'Cloudflare'				=> array( 'slug' => 'cloudflare', 'webproxy' => 'Cloudflare', 'domains' => 'cloudflare.com,ns.cloudflare.com', ), /* HTTP Headers: HTTP:CF-Connecting-IP / $_SERVER['HTTP_CF_CONNECTING_IP'] */
			'Incapsula'					=> array( 'slug' => 'incapsula', 'webproxy' => 'Incapsula', 'domains' => 'incapdns.net', ), /* HTTP Headers: HTTP:Incap-Client-IP / $_SERVER['HTTP_INCAP_CLIENT_IP'] */
			'Sucuri CloudProxy'			=> array( 'slug' => 'sucuri-cloudproxy', 'webproxy' => 'Sucuri CloudProxy', 'domains' => 'mycloudproxy.com,sucuridns.com', ), /* HTTP Headers: HTTP:X-Sucuri-Client-IP / $_SERVER['HTTP_X_SUCURI_CLIENTIP'] */
		);
		$web_proxies_rg = array(			/* RegEx - Internal Server Names or RevDNS of Website IP - Test $server_hostname & $server_rev_dns */
			'Sucuri CloudProxy'			=> array( 'slug' => 'sucuri-cloudproxy', 'webproxy' => 'Sucuri CloudProxy', 'domainsrgx' => "~^cloudproxy[0-9]+\.sucuri\.net$~i", ), 
		);
		/* if( !empty( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) { $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_X_SUCURI_CLIENTIP']; } */
		$options = array( 'surrogate' => FALSE, );
		$site_ns = self::get_ns( $domain );
		$site_ns = ( !empty( $site_ns ) && is_array( $site_ns ) ) ? implode( '  |  ', self::sort_unique( $site_ns ) ) : $site_ns;
		foreach( $web_proxies_ev as $wp => $data ) {
			$envars = explode( ',', $data['envars'] );
			foreach( $envars as $ev ) {
				if( empty( $_SERVER[$ev] ) ) { continue; }
				if( 0 !== strpos( $ev, 'HTTP_' ) ) {
					self::$web_host_proxy = $data['webproxy'];
				} elseif( is_admin() && self::is_user_admin() ) {
					self::$web_host_proxy = $data['webproxy'];
				}
			}
		}
		foreach( $web_proxies_px as $px => $wp ) {
			if( !empty( self::$web_host_proxy ) ) { break; }
			if( empty( $site_ns ) && empty( $server_hostname ) && empty( $server_rev_dns ) ) { break; }
			if( !empty( $site_ns ) && FALSE !== strpos( $site_ns, $px ) ) {
				self::$web_host_proxy = $wp;
			} elseif( !empty( $server_hostname ) && FALSE !== strpos( $server_hostname, $px ) ) {
				self::$web_host_proxy = $wp;
			} elseif( !empty( $server_rev_dns ) && FALSE !== strpos( $server_rev_dns, $px ) ) {
				self::$web_host_proxy = $wp;
			}
		}
		foreach( $web_proxies_rg as $wp => $data ) {
			if( !empty( self::$web_host_proxy ) ) { break; }
			if( empty( $site_ns ) && empty( $server_hostname ) && empty( $server_rev_dns ) ) { break; }
			$rg = $data['domainsrgx'];
			if( !empty( $site_ns ) && self::preg_match( $rg, $site_ns ) ) {
				self::$web_host_proxy = $data['webproxy'];
			} elseif( !empty( $server_hostname ) && self::preg_match( $rg, $server_hostname ) ) {
				self::$web_host_proxy = $data['webproxy'];
			} elseif( !empty( $server_rev_dns ) && self::preg_match( $rg, $server_rev_dns ) ) {
				self::$web_host_proxy = $data['webproxy'];
			}
		}
		if( !empty( self::$web_host_proxy ) ) {
			$options = array( 'surrogate' => TRUE, 'ubl_cache_disable' => TRUE, 'web_proxy' => self::$web_host_proxy, );
			self::update_option( $options );
		}
		return self::$web_host_proxy;
	}

	/**
	 *  Check local web hosting environment for signature of surrogates
	 *  - Server Caching, Reverse Poxies, WAFs: Varnish, Cloudflare (Rocket Loader), Sucuri WAF, Incapsula, etc.
	 *	- Specific web hosts known to use Varnish: WP Engine, Dreamhost, SiteGround, Bluehost, GoDaddy...
	 *	@dependencies	RS_System_Diagnostic::get_web_host(), RS_System_Diagnostic::get_web_proxy(), RS_System_Diagnostic::is_varnish_active(), RS_System_Diagnostic::is_nginx_rp_active(), RS_System_Diagnostic::get_option(), RS_System_Diagnostic::update_uption(), 
	 *  @since			1.0.6
	 */
	static public function is_surrogate() {
		global $rssd_surrogate; if( isset( $rssd_surrogate ) && is_bool( $rssd_surrogate ) && TRUE === $rssd_surrogate ) { return TRUE; }
		$rssd_surrogate = FALSE;
		$web_host	= self::get_web_host( self::$ip_dns_params );
		$web_proxy	= self::get_web_proxy( self::$ip_dns_params );
		if( !empty( $web_proxy ) ) { $rssd_surrogate = TRUE; }
		if( FALSE === $rssd_surrogate && !empty( $web_host ) && ( $web_host === 'WP Engine' || $web_host === 'Dreamhost' || $web_host === 'SiteGround' || $web_host === 'Bluehost' || $web_host === 'GoDaddy' ) ) { $rssd_surrogate = TRUE; }
		if( FALSE === $rssd_surrogate && ( self::is_varnish_active() || self::is_nginx_rp_active() ) ) { $rssd_surrogate = TRUE; }
		if( FALSE === $rssd_surrogate ) { $rssd_surrogate = self::get_option( 'surrogate' ); }
		if( empty( $rssd_surrogate ) ) { $rssd_surrogate = FALSE; }
		self::update_option( array( 'surrogate' => $rssd_surrogate, ) );
		return $rssd_surrogate;
	}

	/**
	 *  Varnish cache detection in local web hosting environment
	 *  @dependencies	RS_System_Diagnostic::update_option(), RS_System_Diagnostic::is_plugin_active(), RS_System_Diagnostic::get_headers(), RSSD_Func::lower(), 
	 *  @since			1.0.6
	 */
	static public function is_varnish_active() {
		global $rssd_varnish_active,$rssd_surrogate; if( isset( $rssd_varnish_active ) && is_bool( $rssd_varnish_active ) && TRUE === $rssd_varnish_active ) { $rssd_surrogate = TRUE; return TRUE; }
		$rssd_varnish_active = FALSE;
		if( function_exists( 'get_loaded_extensions' ) ) { $ext_loaded = @get_loaded_extensions(); }
		$ext_loaded	= ( !empty( $ext_loaded ) && is_array( $ext_loaded ) ) ? $ext_loaded : array();
		$varnish_loaded		= $rssd_varnish_active = RSSD_PHP::in_array( 'varnish', $ext_loaded );
		$varnish_srv_var	= array( 'HTTP_X_VARNISH', );
		$varnish_env_var	= array( 'HTTP_X_VARNISH', );
		$varnish_php_const	= array( 'VARNISH_COMPAT_2', 'VARNISH_COMPAT_3', 'VARNISH_CONFIG_COMPAT', 'VARNISH_CONFIG_HOST', 'VARNISH_CONFIG_IDENT', 'VARNISH_CONFIG_PORT', 'VARNISH_CONFIG_SECRET', 'VARNISH_CONFIG_TIMEOUT', 'VARNISH_STATUS_AUTH', 'VARNISH_STATUS_CANT', 'VARNISH_STATUS_CLOSE', 'VARNISH_STATUS_COMMS', 'VARNISH_STATUS_OK', 'VARNISH_STATUS_PARAM', 'VARNISH_STATUS_SYNTAX', 'VARNISH_STATUS_TOOFEW', 'VARNISH_STATUS_TOOMANY', 'VARNISH_STATUS_UNIMPL', 'VARNISH_STATUS_UNKNOWN', );
		$varnish_plug_const	= array( 'DHDO', 'DHDO_PLUGIN_DIR', 'DREAMSPEED_VERSION', 'VHP_VARNISH_IP', );
		$varnish_constants	= array_merge( $varnish_php_const, $varnish_plug_const );
		$varnish_plugs		= array( 'dreamobjects/dreamobjects.php', 'dreamspeed-cdn/dreamspeed-cdn.php', 'varnish-http-purge/varnish-http-purge.php', );
		foreach( $varnish_srv_var as $i => $v ) {
			if( TRUE === $rssd_varnish_active ) { break; }
			if( !empty( $_SERVER[$v] ) ) { $rssd_varnish_active = TRUE; }
		}
		foreach( $varnish_env_var as $i => $v ) {
			if( TRUE === $rssd_varnish_active ) { break; }
			if( !empty( self::$_ENV[$v] ) ) { $rssd_varnish_active = TRUE; }
		}
		foreach( $varnish_constants as $i => $c ) {
			if( TRUE === $rssd_varnish_active ) { break; }
			if( defined( $c ) ) { $rssd_varnish_active = TRUE; }
		}
		foreach( $varnish_plugs as $i => $p ) {
			if( TRUE === $rssd_varnish_active ) { break; }
			if( self::is_plugin_active( $p ) ) { $rssd_varnish_active = TRUE; }
		}
		/* Fetch URL and check headers for 'varnish' */
		$headers = self::get_headers( RSSD_SITE_URL, TRUE );
		foreach( $headers as $k => $v ) {
			$k = RSSD_Func::lower( $k ); $v = RSSD_Func::lower( $v );
			if( FALSE !== strpos( $k, 'varnish' ) || FALSE !== strpos( $v, 'varnish' ) ) { $rssd_varnish_active = TRUE; }
			if( FALSE !== strpos( $k, 'netdna-cache' ) || FALSE !== strpos( $k, 'x-cache' ) || FALSE !== strpos( $k, 'x-proxy-cache' ) ) { $rssd_surrogate = TRUE; }
		}
		if( TRUE === $rssd_varnish_active || TRUE === $rssd_surrogate ) {
			$rssd_surrogate = TRUE;
			self::update_option( array( 'surrogate' => $rssd_surrogate, ) );
		}
		return $rssd_varnish_active;
	}

	/**
	 *  Nginx reverse-proxy/cache detection in local web hosting environment
	 *  Apache as primary server, and Nginx as the reverse-proxy
	 *  @dependencies	RS_System_Diagnostic::get_headers(), RSSD_Func::lower(), RS_System_Diagnostic::is_nginx_rp_active(),
	 *  @since			1.0.6
	 */
	static public function is_nginx_rp_active() {
		global $is_apache,$is_nginx,$rssd_surrogate,$rssd_nginx_rp_active;
		$rssd_nginx_rp_active = FALSE;
		if( !empty( $is_nginx ) || empty( $is_apache ) ) { $rssd_nginx_rp_active = FALSE; return FALSE; }
		if( isset( $rssd_nginx_rp_active ) && is_bool( $rssd_nginx_rp_active ) && TRUE === $rssd_nginx_rp_active ) { $rssd_nginx_rp_active = TRUE; return TRUE; }
		$rssd_nginx_rp_active = FALSE;
		/* Fetch URL and check headers for 'Server: nginx' */
		$headers = self::get_headers( RSSD_SITE_URL, TRUE );
		foreach( $headers as $k => $v ) {
			$k = RSSD_Func::lower( $k ); $v = RSSD_Func::lower( $v );
			if( 'server' === $k && '0' === strpos( $v, 'nginx' ) ) { $rssd_nginx_rp_active = TRUE; }
			if( FALSE !== strpos( $k, 'netdna-cache' ) || FALSE !== strpos( $k, 'x-cache' ) || FALSE !== strpos( $k, 'x-proxy-cache' ) ) { $rssd_surrogate = TRUE; }
		}
		if( TRUE === $rssd_nginx_rp_active || TRUE === $rssd_surrogate ) {
			$rssd_surrogate = TRUE;
			self::update_option( array( 'surrogate' => $rssd_surrogate, ) );
		}
		return $rssd_nginx_rp_active;
	}

	/**
	 *  Server-side cache detection in local web hosting environment
	 *  @dependencies	RS_System_Diagnostic::is_varnish_active(), RS_System_Diagnostic::is_nginx_rp_active(),
	 *  @since			1.0.6
	 */
	static public function get_server_cache() {
		global $rssd_server_cache,$rssd_varnish_active,$rssd_nginx_rp_active,$rssd_surrogate;
		$rssd_server_cache = array();
		if( self::is_varnish_active() ) {
			$rssd_server_cache[] = 'Varnish';
		}
		if( self::is_nginx_rp_active() ) {
			$rssd_server_cache[] = 'Nginx Reverse-Proxy';
		}
		return $rssd_server_cache;
	}

	/**
	 *  Check HTTP Status - Returns 3-digit response code
	 *	@dependencies	RS_System_Diagnostic::get_headers(), 
	 *  @since			1.0.6
	 *  @modified		1.0.8 Switched from PHP get_headers() to WP HTTP API: wp_remote_head() for compatibility
	 */
	static public function get_http_status( $url = NULL ) {
		return self::get_headers( $url, 'status' );
	}

	/**
	 *  Drop-in replacement for native PHP function get_headers(), with a few tweaks
	 *  Get HTTP Headers of a URL
	 *  Can return an array of headers, an associative array of headers, status code, or all
	 *  @usage			"RS_System_Diagnostic::get_headers( $url )" mimics behavior of native PHP "get_headers( $url )"
	 *  @params			$url, 	$type	default|assoc|status|all
	 *	@dependencies	...
	 *	@used by		...
	 *  @since			1.0.6
	 *  @modified		1.0.8 Switched from PHP get_headers() to WP HTTP API: wp_remote_head() for compatibility
	 */
	static public function get_headers( $url = NULL, $type = 'default' ) {
		$response	= wp_remote_head( $url );
		if( is_wp_error( $response ) || empty( $response['headers'] ) ) { return array(); }
		$headers	= ( self::is_wp_ver( '4.6' )					)	? $response['headers']->getAll()	: $response['headers']	;
		$code		= ( !empty( $response['response']['code'] )		)	? $response['response']['code']		: ''					;
		foreach( $headers as $k => $v ) {
			if( is_array( $v ) ) { unset( $headers[$k] ); }
		}
		if( 'assoc' === $type ) {
			/* Return associative array */
			return $headers;
		} else {
			/* Return numeric array (standard headers array) */
			$std_headers = array();
			foreach( $headers as $k => $v ) {
				$std_headers[] = $k.': '.$v;
			}
			$headers = $std_headers;
			if( 'status' === $type || 'all' === $type ) {
				$status = !empty( $code ) ? $code : 200;
				if( 'status' === $type ) { return $status; }
				$hdr_data = compact( 'headers', 'status' );
				return $hdr_data;
			}
			return $headers;
		}
	}


	/**
	 *  Convert raw HTTP headers into associative array
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.6
	 */
	static public function get_headers_array( $headers ) {
		if( empty( $headers ) ) { return array(); }
		$headers_arr = array();
		foreach( $headers as $h ) {
			$h = explode( ':', $h );
			$headers_arr[array_shift( $h )] = trim( implode( ':', $h ) );
		}
		return $headers_arr;	
	}

	/**
	 *  Get IP address of current request
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 */
	static public function get_ip_addr() {
		if( !empty( self::$ip_addr ) ) { return self::$ip_addr; }
		self::$ip_addr = $ip_addr_default = !empty( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : self::$_ENV['REMOTE_ADDR'];
		self::$ip_addr = $ip_addr_default = self::sanitize_ip( self::$ip_addr );
		if( !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ){
			$xff_addr = !empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? self::sanitize_ip( $_SERVER['HTTP_X_FORWARDED_FOR'] ) : '';
			$rem_addr = $ip_addr_default;
			/* Check for Google Chrome Data Compression Proxy (Chrome Data-Saver) and get Real IP */
			if( !empty( $_SERVER['HTTP_VIA'] ) && !empty( $rem_addr ) && !empty( $xff_addr ) && $rem_addr !== $xff_addr && '1.1 Chrome-Compression-Proxy' === $_SERVER['HTTP_VIA'] && self::is_valid_ip( $xff_addr ) && self::is_google_ip( $rem_addr ) ) { self::$ip_addr = $xff_addr; return $xff_addr; }
			/* Check for Opera Data Saver Proxy and get Real IP */
			if( !empty( $rem_addr ) && !empty( $xff_addr ) && $rem_addr !== $xff_addr && self::is_valid_ip( $xff_addr ) && self::is_opera_ip( $rem_addr ) ) { self::$ip_addr = $xff_addr; return $xff_addr; }
		}
		/* Check for web host proxies */
		$web_host_proxy = self::get_web_proxy( self::$ip_dns_params );
		if( !empty( $web_host_proxy ) && ( !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) || !empty( $_SERVER['HTTP_INCAP_CLIENT_IP'] ) || !empty( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) ) {
			if( 'Cloudflare' === $web_host_proxy && !empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
				self::$ip_addr = self::sanitize_ip( $_SERVER['HTTP_CF_CONNECTING_IP'] );
			} elseif( 'Incapsula' === $web_host_proxy && !empty( $_SERVER['HTTP_INCAP_CLIENT_IP'] ) ) {
				self::$ip_addr = self::sanitize_ip( $_SERVER['HTTP_INCAP_CLIENT_IP'] );
			} elseif( 'Sucuri CloudProxy' === $web_host_proxy && !empty( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] ) ) {
				self::$ip_addr = self::sanitize_ip( $_SERVER['HTTP_X_SUCURI_CLIENTIP'] );
			}
		} elseif( class_exists( 'wfUtils' ) && self::is_plugin_active( 'wordfence/wordfence.php' ) ) {
			self::$ip_addr = wfUtils::getIP();
			self::$ip_addr = self::sanitize_ip( self::$ip_addr );
			return !empty( self::$ip_addr ) ? self::$ip_addr : '';
		}
		self::$ip_addr = self::sanitize_ip( self::$ip_addr );
		self::$ip_addr = ( self::is_valid_ip( self::$ip_addr ) ) ? self::$ip_addr : $ip_addr_default;
		return !empty( self::$ip_addr ) ? self::$ip_addr : '';
	}

	/**
	 *  Get reverse block pattern of IP (IPv4 only)
	 *  If IP comes in AA.BB.CC.DD format, return DD.CC.BB.AA
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.6
	 */
	static public function get_ipv4_dbca( $ip ) {
		if( empty( $ip ) || !is_valid_ip( $ip ) || FALSE === strpos( $ip, '.' ) ) { return $ip; }
		$ip_blocks = explode( '.', $ip );
		$ip_dbca = implode( '.', krsort( $ip_blocks ) );
		return $ip_dbca;
	}

	/**
	 *  Get the reverse DNS ( domain / PTR record ) of an IP address
	 *  Be sure to run any IPs through self::sanitize_ip() first before sending here
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.4
	 */
	static public function get_reverse_dns( $ip ) {
		if( empty( $ip ) || $ip == '.' ) { return ''; }
		if( !empty( self::$rev_dns_cache[$ip] ) ) { return self::$rev_dns_cache[$ip]; }
		if( empty( self::$rev_dns_cache ) || !is_array( self::$rev_dns_cache ) ) { self::$rev_dns_cache = array(); }
		$rev_dns = @gethostbyaddr( $ip );
		self::$rev_dns_cache[$ip] = ( $rev_dns != '.' ) ? $rev_dns : '';
		return self::$rev_dns_cache[$ip]; /* Domain */
	}

	/**
	 *  Get the forward DNS IP of a domain
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.4
	 */
	static public function get_forward_dns( $domain ) {
		$domain = trim( $domain ); if( empty( $domain ) || $domain == '.' ) { return ''; }
		if( !empty( self::$fwd_dns_cache[$domain] ) ) { return self::$fwd_dns_cache[$domain]; }
		if( empty( self::$fwd_dns_cache ) || !is_array( self::$fwd_dns_cache ) ) { self::$fwd_dns_cache = array(); }
		$fwd_dns = @gethostbyname( $domain );
		self::$fwd_dns_cache[$domain] = self::sanitize_ip( $fwd_dns );
		return self::$fwd_dns_cache[$domain]; /* IP */
	}

	/**
	 *  Forward-Confirmed Reverse DNS (FCrDNS)
	 *  Test if resulting IP matches original - If no PTR record, then inconclusive
	 *  11.22.33.44				--- PTR Record --->		hostname.example.com
	 *  hostname.example.com	--- A Record ----->		11.22.33.44
	 *  Process:
	 *  - Get Reverse DNS (rDNS) of IP. Result should be Domain if PTR record is set.
	 *  - If rDNS is Domain, not IP (PTR record is set), then proceed. If no PTR, test is inconclusive.
	 *  - Get Forward DNS of Domain. Result should be IP.
	 *  - If resulting IP matches original input IP, then test is confirmed (Circle complete), and PASS.
	 *  - If resulting IP does not match, then something in the middle (IP or Domain) is SPOOFED, and FAIL.
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.4
	 */
	static public function check_fcrdns( $ip ) {
		if( !self::is_valid_ip( $ip ) ) { return 'INVAL'; } /* INVALID IP */
		$rev_dns = self::get_reverse_dns( $ip );
		if( $rev_dns === $ip ) { return 'NOPTR'; } /* INCONCLUSIVE */
		if( self::is_valid_ip( $rev_dns ) ) { return 'FAIL'; }
		$fwd_dns = self::get_forward_dns( $rev_dns );
		if( $fwd_dns === $ip ) { return 'PASS'; }
		return 'FAIL';
	}

	/**
	 *	Get Nameservers
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.3
	 */
	static public function get_ns( $domain ) {
		$domain = self::get_email_domain( $domain );
		global $rssd_host_ns; if( isset( $rssd_host_ns[$domain] ) && NULL !== $rssd_host_ns[$domain] ) { return $rssd_host_ns[$domain]; };
		while( !empty( $domain ) && ! self::preg_match( "~^([a-z]{2,3})(\.[a-z]{2,3})[a-z]*$~i", $domain ) && empty( $ns ) ) {
			$dns_ns = @dns_get_record( $domain, DNS_NS ); $ns = array();
			if( !empty( $dns_ns ) && is_array( $dns_ns ) ) {
				foreach( $dns_ns as $i => $a ) {
					if( empty( $a['target'] ) ) { continue; } else { $ns[] = $a['target']; }
				}
				if( !empty( $ns ) ) { break; }
			}
			$dom_els = explode( '.', $domain ); unset( $dom_els[0] );
			$domain = implode( '.', $dom_els );
		}
		if( !isset( $rssd_host_ns ) || !is_array( $rssd_host_ns ) ) { $rssd_host_ns = array(); }
		$rssd_host_ns[$domain] = !empty( $ns ) ? $ns : FALSE;
		return $rssd_host_ns[$domain];
	}

	/**
	 *	Using this because WordPress' native is_plugin_active() function only works in Admin
	 *	ex. $plug_bn = 'folder/filename.php'; // Plugin Basename
	 */
	static public function is_plugin_active( $plug_bn, $check_network = TRUE ) {
		if( empty( $plug_bn ) ){ return FALSE; }
		global $rssd_conf_active_plugins;
		/* Quick Check */
		if( !empty( $rssd_conf_active_plugins[$plug_bn] ) ) { return TRUE; }
		if( TRUE === $check_network && is_multisite() ) { if( !empty( $rssd_conf_active_network_plugins[$plug_bn] ) ) { return TRUE; } }
		$rssd_conf_active_plugins = array();
		$rssd_conf_active_network_plugins = array();
		/* Check known plugin constants and classes */
		$plug_cncl = array(
			/* Compatibility Fixes */

			/* All others */
			'wordfence/wordfence.php' => array( 'cn' => 'WORDFENCE_VERSION', 'cl' => 'wordfence' ), 
		);
		if( ( !empty( $plug_cncl[$plug_bn]['cn'] ) && defined( $plug_cncl[$plug_bn]['cn'] ) ) || ( !empty( $plug_cncl[$plug_bn]['cl'] ) && class_exists( $plug_cncl[$plug_bn]['cl'] ) ) ) { $rssd_conf_active_plugins[$plug_bn] = TRUE; return TRUE; }
		/* No match yet, so now do standard check */
		global $rssd_active_plugins; if( empty( $rssd_active_plugins ) ) { $rssd_active_plugins = self::get_active_plugins(); }
		if( RSSD_PHP::in_array( $plug_bn, $rssd_active_plugins ) ) { $rssd_conf_active_plugins[$plug_bn] = TRUE; return TRUE; }
		if( TRUE === $check_network && is_multisite() ) {
			global $rssd_active_network_plugins; if( empty( $rssd_active_network_plugins ) ) { $rssd_active_network_plugins = self::get_active_network_plugins(); }
			if( RSSD_PHP::in_array( $plug_bn, $rssd_active_network_plugins ) ) { $rssd_conf_active_network_plugins[$plug_bn] = TRUE; return TRUE; }
		}
		return FALSE;
	}

	static public function get_active_plugins( $sort = TRUE ) {
		global $rssd_active_plugins;
		if( empty( $rssd_active_plugins ) ) { $rssd_active_plugins = get_option( 'active_plugins', array() ); }
		if( TRUE === $sort ) { $rssd_active_plugins = self::sort_unique( $rssd_active_plugins ); }
		return $rssd_active_plugins;
	}

	static public function get_active_network_plugins() {
		global $rssd_active_network_plugins;
		if( empty( $rssd_active_network_plugins ) ) { 
			$rssd_active_network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			if( !empty( $rssd_active_network_plugins ) && is_array( $rssd_active_network_plugins ) ) {
				$rssd_active_network_plugins = self::sort_unique( array_flip( $rssd_active_network_plugins ) );
			}
		}
		return $rssd_active_network_plugins;
	}

	static public function casetrans( $type, $string ) {
		/**
		 *	Deprecated 1.0.6 - Moved to RSSD_Func Class
		 */
		return RSSD_PHP::casetrans( $type, $string );
	}

	static public function sanitize_string( $string ) {
		/* Sanitize a string. Much faster than sanitize_text_field() */
		$filtered = trim( addslashes( htmlentities( stripslashes( strip_tags( $string ) ) ) ) );
		return $filtered;
	}

	/**
	 *	Convert Object to Multidimensional Associative Array
	 *	@func_ver		RSSD.20170111.01
	 *	@dependencies	json_encode()
	 *	@used by		...
	 *	@since			1.0.4
	 */
	static public function obj_to_arr( $obj ) {
		if( !is_object( $obj ) && !is_array( $obj ) ) { return $obj; }
		$arr = json_decode( self::json_encode( $obj ), TRUE );
		return ( !is_array( $arr ) ) ? (array) $arr : $arr;
	}

	static public function is_array_assoc( $arr = array() ) {
	/**
	 *	Detect if Array is Associative
	 *	@dependencies	obj_to_arr()
	 *	@func_ver		RSSD.20170111.01
	 *	@since			1.0.4
	 */
		if( empty( $arr ) ) { return FALSE; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return FALSE; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		foreach( array_keys( $arr ) as $k ) {
			if( !is_int( $k ) ) { return TRUE; }
		}
		return FALSE;
	}

	static public function is_array_multi( $arr = array() ) {
	/**
	 *	Detect if Array is Multidimensional
	 *	@dependencies	obj_to_arr()
	 *	@func_ver		RSSD.20170111.01
	 *	@since			1.0.5
	 */
		if( empty( $arr ) ) { return FALSE; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return FALSE; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		foreach( array_keys( $arr ) as $k => $v ) {
			if( is_array( $v ) ) { return TRUE; }
		}
		return FALSE;
	}

	static public function is_array_num( $arr = array() ) {
	/**
	 *	Detect if Array is Numerical
	 *	@dependencies	obj_to_arr(), is_array_assoc()
	 *	@func_ver		RSSD.20170111.01
	 *	@since			1.0.5
	 */
		if( empty( $arr ) ) { return FALSE; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		if( is_array( $arr ) && FALSE === self::is_array_assoc( $arr ) ) {
			foreach( array_keys( $arr ) as $k ) {
				if( is_int( $k ) ) { return TRUE; }
			}
		}
		return FALSE;
	}

	/**
	 *  Removes duplicates and orders the array. Single-dimensional Numeric Arrays only.
	 *	@dependencies	RS_System_Diagnostic::obj_to_arr(), RS_System_Diagnostic::is_array_multi(), RS_System_Diagnostic::msort_array(), ...
	 *	@used by		...
	 *	@func_ver		RSSD.20170219.01
	 *	@since			RSSD 1.0.4
	 */
	static public function sort_unique( $arr = array() ) {
		if( empty( $arr ) ) { return array(); }
		if( is_string( $arr ) || is_numeric( $arr ) ) { return (array) $arr; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return array(); }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		$arr_tmp = array_unique( $arr );
		if( self::is_array_multi( $arr_tmp ) ) { $arr_tmp = self::msort_array( $arr_tmp ); }
		@sort( $arr_tmp, SORT_REGULAR );
		$new_arr = array_values( $arr_tmp );
		return $new_arr;
	}

	static public function vsort_array( $arr = array() ) {
	/**
	 *  Orders the array by value without removing duplicates. Numeric Arrays only.
	 *	@dependencies	obj_to_arr(), is_array_multi(), msort_array(), 
	 *	@func_ver		RSSD.20170111.01
	 *	@since			RSSD 1.0.5
	 */
		if( empty( $arr ) ) { return $arr; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return (array) $arr; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		$arr_tmp = (array) $arr;
		if( self::is_array_multi( $arr_tmp ) ) { $arr_tmp = self::msort_array( $arr_tmp ); }
		@sort( $arr_tmp, SORT_REGULAR );
		$new_arr = array_values( $arr_tmp );
		return $new_arr;
	}

	static public function ksort_array( $arr = array() ) {
	/**
	 *  Orders the array by key. Associative Arrays only.
	 *	@dependencies	obj_to_arr(), is_array_multi(), msort_array(), 
	 *	@func_ver		RSSD.20170111.01
	 *	@since			RSSD 1.0.5
	 */
		if( empty( $arr ) ) { return $arr; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return (array) $arr; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		$arr_tmp = (array) $arr;
		if( self::is_php_ver( '5.4' ) ) {
			if( self::is_array_multi( $arr_tmp ) ) { $arr_tmp = self::msort_array( $arr_tmp ); }
			@ksort( $arr_tmp, SORT_NATURAL | SORT_FLAG_CASE );
		} else {
			if( self::is_array_multi( $arr_tmp ) ) { $arr_tmp = self::msort_array( $arr_tmp ); }
			@ksort( $arr_tmp, SORT_REGULAR );
		}
		$new_arr = $arr_tmp;
		return $new_arr;
	}

	static public function msort_array( $arr = array(), $i = 0 ) {
	/**
	 *  Sorts the array, multidimensional.
	 *  Sorts Numeric arrays by Value, and Associative arrays by Key
	 *	@dependencies	obj_to_arr(), wp_memory_used(), is_array_num(), vsort_array(), ksort_array()
	 *	@func_ver		RSSD.20170111.01
	 *	@since			RSSD 1.0.4
	 */
		if( empty( $arr ) ) { return $arr; }
		if( !is_array( $arr ) && !is_object( $arr ) ) { return (array) $arr; }
		if( is_object( $arr ) ) { $arr = self::obj_to_arr( $arr ); }
		$arr_tmp = $arr;
		$i++; $m = 5; /* $m = max */
		if( $i === $m || self::wp_memory_used( FALSE, TRUE ) > 64 * MB_IN_BYTES ) {
			$new_arr = array_multisort( $arr_tmp );
		} else {
			if( self::is_array_num( $arr_tmp ) ) { /* Numeric Arrays - Orders the array, by value. */
				$arr_tmp = self::vsort_array( $arr_tmp );
				foreach( $arr_tmp as $k => $v ) {
					if( is_array( $v ) || is_object( $v ) ) {
						if( is_object( $v ) ) { $v = self::obj_to_arr( $v ); }
						$arr_tmp[$k] = self::msort_array( $v, $i );
					} else { $arr_tmp[$k] = $v; }
				}
			} else { /* Associative Arrays - Orders the array, by key. */
				$arr_tmp = self::ksort_array( $arr_tmp );
				foreach( $arr_tmp as $k => $v ) {
					if( is_array( $v ) || is_object( $v ) ) {
						if( is_object( $v ) ) { $v = self::obj_to_arr( $v ); }
						$arr_tmp[$k] = self::msort_array( $v, $i );
					} else { $arr_tmp[$k] = $v; }
				}
			}
			$new_arr = $arr_tmp;
		}
		return $new_arr;
	}

	/**
	 *  Remove null bytes from a string
	 *  Can help prevent breakage and certain security issues
	 *  @dependencies	none
	 *  @since			1.0.8
	 */
	static public function filter_null( $str ) {
		return str_replace( chr(0), '', $str );
	}

	static public function is_user_admin() {
		global $rssd_user_can_manage_options;
		if( empty( $rssd_user_can_manage_options ) ) { $rssd_user_can_manage_options = current_user_can( 'manage_options' ) ? 'YES' : 'NO'; }
		if( $rssd_user_can_manage_options === 'YES' ) { return TRUE; }
		return FALSE;
	}

	/**
	 *  Set some PHPMailer options
	 *	@dependencies	...
	 *	@used by		...
	 *  @since			1.0.6
	 */
	static public function phpmailer_config( &$phpmailer ) {
		$phpmailer->Encoding = 'base64'; /* Encode - NOTE: Using SMTP plugins may disable/override this feature */
		$phpmailer->Priority = 1;
		$phpmailer->XMailer = ' '; /* Remove X-Mailer header */
		$phpmailer->LE = "\r\n";
	}

	/**
	 *  This is a wrapper for the wp_mail() function and removes *some* data leakage that happens in the PHP mail process.
	 *  PHP-generated emails (especially on shared hosts) can leak data in the headers and potentially reveal sensitive path info, as well as the main username for a website.
	 *  For more secure mail, users should use SMTP so there isn't data leakage as with the PHP-generated emails.
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 */
	static public function mail( $to, $subject, $message, $headers = '', $attachments = NULL ) {

		/* Obscure */
		$orig_server	= $_SERVER;
		$orig_env		= $_ENV;
		$orig_ip		= $_SERVER['REMOTE_ADDR'];
		$obsc_ip		= RSSD_SERVER_ADDR;
		$orig_script	= $_SERVER['SCRIPT_FILENAME'];
		$orig_docroot	= $_SERVER['DOCUMENT_ROOT'];
		$orig_requri	= $_SERVER['REQUEST_URI'];

		$obscure = array(
			'REMOTE_ADDR' => $obsc_ip, 'HTTP_X_FORWARDED_FOR' => $obsc_ip, 'HTTP_X_FORWARDED' => $obsc_ip, 'HTTP_FORWARDED_FOR' => $obsc_ip, 'HTTP_FORWARDED' => $obsc_ip, 'HTTP_X_REAL_IP' => $obsc_ip, 'DOCUMENT_ROOT' => '/', 'PHP_SELF' => '/', 'REQUEST_URI' => '/', 'SCRIPT_FILENAME' => '/', 'SCRIPT_NAME' => '/', 'REDIRECT_URL' => '/', 'PHPRC' => '/', 'HTTP_REFERER' => '', 
		);
		foreach( $_SERVER as $k => $v )	{
			if( isset( $obscure[$k] ) )	{ $_SERVER[$k] = $obscure[$k]; }
			if( $v === $orig_ip )		{ $_SERVER[$k] = $obsc_ip; }
			if( !is_string( $v ) )		{ continue; }
			if( FALSE !== strpos( $v, $orig_script ) || FALSE !== strpos( $v, $orig_docroot ) || FALSE !== strpos( $v, $orig_requri ) )	{ $_SERVER[$k] = '/'; $_ENV[$k] = '/'; }
		}
		foreach( $_ENV as $k => $v )	{
			if( isset( $obscure[$k] ) )	{ $_ENV[$k] = $obscure[$k]; }
			if( $v === $orig_ip )		{ $_ENV[$k] = $obsc_ip; }
			if( !is_string( $v ) )		{ continue; }
			if( FALSE !== strpos( $v, $orig_script ) || FALSE !== strpos( $v, $orig_docroot ) || FALSE !== strpos( $v, $orig_requri ) )	{ $_ENV[$k] = '/'; $_SERVER[$k] = '/'; }
		}

		/* PHPMailer Options */
		add_action( 'phpmailer_init', array( __CLASS__, 'phpmailer_config' ), 999 );
		/* Mail */
		$sent = @wp_mail( $to, $subject, $message, $headers, $attachments );
		/* Restore */
		$_SERVER = $orig_server; $_ENV = $orig_env;
		return $sent;
	}

	/* Common Functions - END */



	/* Admin Functions - BEGIN */

	static public function activation() {
		self::upgrade_check();
		self::generate_url( TRUE );
	}

	static public function deactivation() {
		/* Do nothing for now */
	}

	/**
	 *	Delete plugin options on uninstall.
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@return			void
	 */
	static public function uninstall() {
		delete_option( 'rs_system_diagnostic' );
		$user_ids = get_users( array( 'blog_id' => '', 'fields' => 'ID' ) );
		foreach ( $user_ids as $user_id ) { delete_user_meta( $user_id, 'rssd_meta' ); }
	}

	static public function upgrade_check( $atts = array() ) {
		$installed_ver = self::get_option( 'version' );
		if( $installed_ver !== RSSD_VERSION ) {
			$install_date = self::get_option( 'install_date' );
			/* Options to Update */
			$upd_options = array( 
				'version'		=> RSSD_VERSION, 
				'install_date'	=> empty( $install_date ) ? date( RSSD_DATE_BASIC ) : $install_date, 
			);
			self::update_option( $upd_options, FALSE ); /* Set to FALSE so we don't write to DB twice, since this runs right before self::delete_option */
			/* Options to Delete */
			$del_options = array( 'install_status', 'warning_status', 'alert_status', );
			self::delete_option( $del_options );
		}
	}

	static public function admin_notices() {
		$admin_notices = self::get_option( 'admin_notices' );
		if( !empty( $admin_notices ) ) {
			$style 	= $admin_notices['style']; /* 'error', 'updated', 'is-dismissible', 'updated notice is-dismissible' */
			$notice	= $admin_notices['notice'];
			echo '<div class="'.$style.'"><p>'.$notice.'</p></div>';
		}
		self::delete_option( array( 'admin_notices', ) );
	}

	static public function network_admin_notices() {
		if( !is_multisite() || !is_super_admin() ) { return; }
		$admin_notices = self::get_option( 'network_admin_notices' );
		if( !empty( $admin_notices ) ) {
			$style 	= $admin_notices['style']; /* 'error', 'updated', 'is-dismissible', 'updated notice is-dismissible' */
			$notice	= $admin_notices['notice'];
			echo '<div class="'.$style.'"><p>'.$notice.'</p></div>';
		}
		self::delete_option( array( 'network_admin_notices', ) );
	}

	static public function admin_cpn_notices() {
		/* Admin Custom Plugin Notices */
		$notices = self::get_user_meta( 'cpn_notices' );
		if( !empty( $notices ) ) {
			$nid			= $notices['nid'];
			$style			= $notices['style']; /* 'error', 'updated', 'is-dismissible', 'updated notice is-dismissible' */
			$timenow		= time();
			$url			= self::get_url();
			$query_args		= self::get_query_args( $url );
			$query_str		= '?' . http_build_query( array_merge( $query_args, array( 'rssd_hide_cpn' => '1', 'nid' => $nid ) ) );
			$query_str_con	= 'QUERYSTRING';
			$notice			= str_replace( array( $query_str_con ), array( $query_str ), $notices['notice'] );
			echo '<div class="'.$style.'"><p>'.$notice.'</p></div>';
		}
	}

	static public function check_cpn_notices() {
		/* Check Custom Plugin Notices */
		$status			= self::get_user_meta( 'cpn_status' );
		if( !empty( $status['currentcpn'] ) ) { add_action( 'admin_notices', array( __CLASS__, 'admin_cpn_notices' ) ); return; }
		if( !is_array( $status ) ) { $status = array(); self::update_user_meta( array( 'cpn_status' => $status, ) ); }
		$timenow		= time();
		$num_days_inst	= self::num_days_inst();
		$query_str_con	= 'QUERYSTRING';
		/* Reminders */
		if( empty( $status['currentcpn'] ) && ( empty( $status['lastcpn'] ) || $status['lastcpn'] <= $timenow - 1209600 ) ) {
			if( empty( $status['vote'] ) && $num_days_inst >= 14 ) { /* TO DO: TRANSLATE */
				$nid = 'n01'; $style = 'updated';
				$notice_text = __( 'It looks like you\'ve been using RS System Diagnostic for a while now. That\'s great! :)', 'rs-system-diagnostic' ) .'</p><p>'. __( 'If you find this plugin useful, would you take a moment to give it a rating on WordPress.org?', 'rs-system-diagnostic' ) .'</p><p>'. sprintf( __( '<strong><a href=%1$s>%2$s</a></strong>', 'rs-system-diagnostic' ), '"'.RSSD_WP_RATING_URL.'" target="_blank" rel="external" ', __( 'Yes, I\'d like to rate it!', 'rs-system-diagnostic' ) ) .' &mdash; '.  sprintf( __( '<strong><a href=%1$s>%2$s</a></strong>', 'rs-system-diagnostic' ), '"'.$query_str_con.'" ', __( 'I already did!', 'rs-system-diagnostic' ) );
				$status['currentcpn'] = TRUE; $status['vote'] = FALSE;
			} elseif( empty( $status['donate'] ) && $num_days_inst >= 90 ) { /* TO DO: TRANSLATE */
				$nid = 'n02'; $style = 'updated';
				$notice_text = __( 'You\'ve been using RS System Diagnostic for quite a while now. Outstanding! We hope that means you like it and are finding it helpful. :)', 'rs-system-diagnostic' ) .'</p><p>'. __( 'RS System Diagnostic is provided for free.', 'rs-system-diagnostic' ) . ' ' . __( 'If you like the plugin, consider a donation to help further its development.', 'rs-system-diagnostic' ) .'</p><p>'. sprintf( __( '<strong><a href=%1$s>%2$s</a></strong>', 'rs-system-diagnostic' ), '"'.RSSD_DONATE_URL.'" target="_blank" rel="external" ', __( 'Yes, I\'d like to donate!', 'rs-system-diagnostic' ) ) .' &mdash; '. sprintf( __( '<strong><a href=%1$s>%2$s</a></strong>', 'rs-system-diagnostic' ), '"'.$query_str_con.'" ', __( 'I already did!', 'rs-system-diagnostic' ) ) .' &mdash; '. sprintf( __( '<strong><a href=%1$s>%2$s</a></strong>', 'rs-system-diagnostic' ), '"'.$query_str_con.'" ', __( 'I prefer not to.', 'rs-system-diagnostic' ) );
				$status['currentcpn'] = TRUE; $status['donate'] = FALSE;
			}
		}
		/* Warnings */
		/**
		 *  TO DO:
		 *  - Add Warnings - about plugin conflicts and missing PHP functions
		 */
		if( !empty( $status['currentcpn'] ) ) {
			add_action( 'admin_notices', array( __CLASS__, 'admin_cpn_notices' ) );
			$new_cpn_notice = array( 'nid' => $nid, 'style' => $style, 'notice' => $notice_text );
			self::update_user_meta( array( 'cpn_notices' => $new_cpn_notice, 'cpn_status' => $status, ) );
		}
	}

	static public function hide_cpn_notices() {
		/* Hide Custom Plugin Notices */
		if( !self::is_user_admin() ) { return; }
		$cpns_codes		= array( 'n01' => 'vote', 'n02' => 'donate', ); /* CPN Status Codes */
		if( !isset( $_GET['rssd_hide_cpn'], $_GET['nid'], $cpns_codes[$_GET['nid']] ) || $_GET['rssd_hide_cpn'] != '1' ) { return; }
		$status			= self::get_user_meta( 'cpn_status' );
		$timenow		= time();
		$url			= self::get_url();
		$query_args		= self::get_query_args( $url ); unset( $query_args['rssd_hide_cpn'],$query_args['nid'] );
		$query_str		= http_build_query( $query_args ); if( $query_str != '' ) { $query_str = '?'.$query_str; }
		$redirect_url	= self::fix_url( $url, TRUE, TRUE ) . $query_str;
		$status['currentcpn'] = FALSE; $status['lastcpn'] = $timenow; $status[$cpns_codes[$_GET['nid']]] = TRUE;
		self::update_user_meta( array( 'cpn_notices' => array(), 'cpn_status' => $status, ) );
		wp_redirect( $redirect_url );
		exit;
	}

	static public function check_requirements() {
		if( self::is_user_admin() ) {
			/* Check if plugin has been upgraded */
			self::upgrade_check();
			/* Check for pending admin notices */
			$prefix = '';
			$admin_notices = self::get_option( 'admin_notices' );
			if( !empty( $admin_notices ) ) { add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) ); }
			if( is_multisite() && is_super_admin() ) {
				$network_admin_notices = self::get_option( 'network_admin_notices' );
				if( !empty( $network_admin_notices ) ) { add_action( 'network_admin_notices', array( __CLASS__, 'network_admin_notices' ) ); }
				if( is_plugin_active_for_network( RSSD_PLUGIN_BASENAME ) ) { $prefix = 'network_'; }
			}
			/* Make sure user has minimum required WordPress version, in order to prevent issues */
			if( !empty( self::$wp_ver ) && version_compare( self::$wp_ver, RSSD_REQUIRED_WP_VERSION, '<' ) ) {
				deactivate_plugins( RSSD_PLUGIN_BASENAME );
				$notice_text = __('Plugin <strong>deactivated</strong>.') . ' ' . sprintf( __( 'WordPress Version %s required. Please upgrade WordPress to the latest version.', 'rs-system-diagnostic' ), RSSD_REQUIRED_WP_VERSION ); /* TO DO: NEEDS TRANSLATION */
				$new_admin_notice = array( 'style' => 'error notice is-dismissible', 'notice' => $notice_text );
				self::update_option( array( $prefix.'admin_notices' => $new_admin_notice, ) );
				add_action( $prefix.'admin_notices', array( __CLASS__, $prefix.'admin_notices' ) );
				return FALSE;
			}
			/* Make sure user has minimum required PHP version, in order to prevent issues */
			if( !empty( self::$php_version ) && version_compare( self::$php_version, RSSD_REQUIRED_PHP_VERSION, '<' ) ) {
				deactivate_plugins( RSSD_PLUGIN_BASENAME );
				$notice_text = '<p>' . __('Plugin <strong>deactivated</strong>.') . ' ' . str_replace( 'WordPress', 'RS System Diagnostic', sprintf( __('Your server is running PHP version %1$s but WordPress %2$s requires at least %3$s.'), PHP_VERSION, RSSD_VERSION, RSSD_REQUIRED_PHP_VERSION ) ) . '</p>';
				$new_admin_notice = array( 'style' => 'error notice is-dismissible', 'notice' => $notice_text );
				self::update_option( array( $prefix.'admin_notices' => $new_admin_notice, ) );
				add_action( $prefix.'admin_notices', array( __CLASS__, $prefix.'admin_notices' ) );
				return FALSE;
			}
			self::check_cpn_notices();
		}
	}

	static public function num_days_inst() {
		$current_date	= date( RSSD_DATE_BASIC );
		$install_date	= self::get_option( 'install_date' );
		$install_date	= empty( $install_date ) ? $current_date : $install_date;
		$num_days_inst	= self::date_diff($install_date, $current_date); if( $num_days_inst < 1 ) { $num_days_inst = 1; }
		return $num_days_inst;
	}

	static public function is_remote_view( $GET_VAR = RSSD_GET_VAR, $server_name = RSSD_SERVER_NAME, $site_url = RSSD_SITE_URL ) {
		if( isset( self::$is_remote_view ) && ( TRUE === self::$is_remote_view || FALSE === self::$is_remote_view ) ) { return self::$is_remote_view; }
		if( !defined( 'RSSD_GET_VAR' ) && !empty( $GET_VAR ) ) {
			define( 'RSSD_GET_VAR', $GET_VAR );
		}
		self::$is_remote_view = FALSE;
		if( empty( $_GET[$GET_VAR] ) || is_admin() ) { return FALSE; }
		$remote_url_key	= self::get_option( 'remote_url_key' );
		if( empty( $remote_url_key ) ) {
			self::generate_url( TRUE );
			return FALSE;
		}
		$current_url = self::get_url( FALSE, $server_name );
		$remote_url = $site_url.'/?'.$GET_VAR.'='.$remote_url_key;
		if( $current_url === $remote_url ) { self::$is_remote_view = TRUE; }
		return self::$is_remote_view;
	}

	static public function headers() {
		if( empty( $_GET[RSSD_GET_VAR] ) ) { return; }
		if( function_exists('header_remove') ) { @header_remove('Content-Type'); }
		header('Content-Type: text/plain; charset=UTF-8',TRUE);
	}

	/**
	 *	Append link to RS System Diagnostic page on Plugins Page in action links
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@filter			plugin_action_links_
	 *	@param			array  Array of links
	 *	@return			array  Updated Array of links
	 */
	static public function action_links( $links ) {
		$new_links = array(
			'<a href="' . RSSD_PLUGIN_ADMIN_URL . '">' . __( 'View System Diagnostic', 'rs-system-diagnostic' ) . '</a>',
		);
		$links = array_merge( $links, $new_links );
		return $links;
	}

	/**
	 *	Append link to RS System Diagnostic page on Plugins Page in meta links
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@filter			plugin_row_meta
	 *	@param			array	Array of links
	 *	@param			array	$file
	 *	@return			array	Updated Array of links
	 */
	static public function meta_links( $links, $file ) {
		if( $file === RSSD_PLUGIN_BASENAME ) {
			$new_links = array(
				'<a href="'.RSSD_HOME_URL.		'" target="_blank" rel="external" >' . __( 'Documentation' ) . '</a>', 
				'<a href="'.RSSD_SUPPORT_URL.	'" target="_blank" rel="external" >' . __( 'Tech Support', 'rs-system-diagnostic' ) . '</a>', 
				'<a href="'.RSSD_WP_RATING_URL.	'" target="_blank" rel="external" >' . __( 'Rate the Plugin', 'rs-system-diagnostic' ) . '</a>',
				'<a href="'.RSSD_DONATE_URL.	'" target="_blank" rel="external" >' . __( 'Donate', 'rs-system-diagnostic' ) . '</a>',
			);
			$links = array_merge( $links, $new_links );
		}
		return $links;
	}

	/**
	 *	Enqueue Javascript
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@action			admin_print_scripts-
	 *	@return			void
	 */
	static public function enqueue_js() {
		wp_register_script( 'rssd-script', RSSD_PLUGIN_JS_URL.'/rssd.min.js', array( 'jquery' ) );
		wp_localize_script( 'rssd-script', 'systemDiagnosticAjax', array( 'ajaxurl' => RSSD_ADMIN_AJAX_URL ) );
		wp_enqueue_script( 'rssd-script' );
	}

	/**
	 *	Enqueue CSS
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@action			admin_print_styles-
	 *	@return			void
	 */
	static public function enqueue_css() {
		wp_enqueue_style( 'rssd-style', RSSD_PLUGIN_CSS_URL.'/rssd.min.css' );
	}

	/**
	 *	Register management page (under "Tools" menu), and enqueue styles and scripts.
	 *	Only viewable by Administrators
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@action			admin_menu
	 *	@return			void
	 */
	static public function create_admin_page() {
		$page = add_management_page( __( 'RS System Diagnostic', 'rs-system-diagnostic' ), __( 'RS System Diagnostic', 'rs-system-diagnostic' ), 'manage_options', RSSD_PLUGIN_NAME, array( __CLASS__, 'plugin_admin_page' ) );
		/* Enqueue scripts and styles on the RS System Diagnostic page only */
		add_action( 'admin_print_styles-'.$page,	array( __CLASS__, 'enqueue_css' ) );
		add_action( 'admin_print_scripts-'.$page,	array( __CLASS__, 'enqueue_js' ) );
	}

	/**
	 *	THE Main Plugin Page (Under Tools Menu)
	 *	Render plugin page title, information and info textarea
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@return			void
	 */
	static public function plugin_admin_page() {
		if( !self::is_user_admin() ) { self::wp_die(); }
		$email_sent = RS_System_Diagnostic_Email::send_email();
		if( !empty( $email_sent ) && 'sent' === $email_sent ) {
			printf( '<div id="message" class="updated"><p>%s</p></div>', __( 'Email sent successfully.', 'rs-system-diagnostic' ) );
		} elseif( !empty( $email_sent ) && 'error' === $email_sent ) {
			printf( '<div id="message" class="error"><p>%s</p></div>', __( 'Error sending email.', 'rs-system-diagnostic' ) );
		}

?>

<div class="wrap" id="rssd_admin_wrap" >
	<h2 class="rssd-title">//// <?php _e( 'RS System Diagnostic', 'rs-system-diagnostic' ); ?> ////</h2>
		<div id="rssd-text-description" class="rssd-text-description">
			<p class="instructions"><?php _e( '<strong>RS System Diagnostic</strong> displays website technical data helpful to support personnel. This information can be downloaded as a text file or sent via email using the form below. ', 'rs-system-diagnostic' ) ?></p>
			<p class="instructions"><?php _e( 'Additionally, a URL can be provided to your support provider to allow them to view this information for a limited time. This access can be revoked by generating a new URL.', 'rs-system-diagnostic' ) ?></p>
			<p class="instructions"><?php _e( 'This link may be handy to use in support forums, as access to this information can be removed after you receive the help you need.', 'rs-system-diagnostic' ) ?></p>
			<?php if( is_multisite() && is_super_admin() && is_plugin_active_for_network( RSSD_PLUGIN_BASENAME ) ) { 
				echo '<p class="instructions warning">' . __( 'WARNING: This plugin is network activated. There may be potential security risks associated with network activation, so use only as needed, then disable.', 'rs-system-diagnostic' ) . '</p>';
			} ?>
		</div>
		<div id="wrap-system-data-form">
			<form action="<?php echo esc_url( RSSD_ADMIN_AJAX_URL ); ?>" method="post" enctype="multipart/form-data" id="rssd-system-data-form" >
				<?php wp_nonce_field( 'rssd_download_file_token', 'dlft_tkn' ); echo "\n"; ?>
				<input type="hidden" name="action" value="download_system_diagnostic" />
				<input type="hidden" name="option" value="<?php echo ( !empty( $_GET['option'] ) && 'advanced' === $_GET['option'] ) ? 'advanced' : ''; ?>" />
				<input type="hidden" name="page" value="<?php echo ( !empty( $_GET['page'] ) && 'advanced' === $_GET['page'] ) ? 'advanced' : ''; ?>" />
				<div>
					<textarea readonly="readonly" onclick="this.focus();this.select()" id="rssd-textarea" name="rssd-textarea" title="<?php
_e( 'To copy the System Diagnostic Data, click inside the box, and then press Ctrl + C (PC) or Cmd + C (Mac).', 'rs-system-diagnostic' );
?>"><?php
echo esc_html( self::display_data() ); /* Use esc_html() for textarea, but stripslashes() for file download. */
?></textarea>
				</div>
				<p class="submit">
					<input type="submit" class="button-secondary" value="<?php _e( 'Download System Diagnostic Data as Text File', 'rs-system-diagnostic' ) ?>" />
				</p>
			</form>
			<h3 class="rssd-email-title">//// <?php _e( 'Send via Email', 'rs-system-diagnostic' ) ?> ////</h3>
			<?php RS_System_Diagnostic_Email::email_form_section(); ?>
			<h3 class="rssd-remote-title">//// <?php _e( 'Remote Viewing', 'rs-system-diagnostic' ) ?> ////</h3>
			<?php RS_System_Diagnostic_Viewer::remote_viewing_section(); ?>
		</div>
		<div id="wrap-nav-form">

<?php
$current_url = self::get_url();
$rssd_nav_action = ( empty( $_GET['option'] ) || 'advanced' !== $_GET['option'] ) ? add_query_arg( array( 'option' => 'advanced', ), $current_url ) : remove_query_arg( array( 'option', ), $current_url );
?>

			<span style="text-align: center;"><h3>//// <?php
			if( is_super_admin() ) {
				echo ( empty( $_GET['option'] ) || 'advanced' !== $_GET['option'] ) ? __( 'BASIC VIEW', 'rs-system-diagnostic' ) : __( 'ADVANCED VIEW', 'rs-system-diagnostic' );
			}
			else {
				_e( 'RS System Diagnostic', 'rs-system-diagnostic' );
			}
			?> ////</h3><?php
			if( is_super_admin() ) { ?>
			<form action="<?php echo $rssd_nav_action; ?>" method="post" id="rssd-nav-form" >
				<p class="submit">
					<input type="submit" class="button-secondary" value="<?php echo ( empty( $_GET['option'] ) || 'advanced' !== $_GET['option'] ) ? __( 'Switch to Advanced View', 'rs-system-diagnostic' ) : __( 'Switch to Basic View', 'rs-system-diagnostic' ); ?>" id="rssd-nav-button" />
				</p>
			</form></span>
			<p><strong>Basic View</strong> is the default view. It includes just the basic data about your system.</p>
			<p><strong>Advanced View</strong> goes into a lot more depth. It also scans your system and includes important conguration files: <em>php.ini</em>, <em>.htaccess</em>, and <em>wp-config.php</em>. (WordPress Database keys and passwords are hidden for security, as it's not likely these would be needed to share these with tech support.) Advanced view can prove helpful when you're trying to solve some of the more complex site debugging issues, or if you need just to review your site's configuration and/or security settings.</p>
			<p></p><?php } ?>

			<p><strong>RS System Diagnostic</strong> is a powerful tool for web developers, site owners, and WordPress plugin/theme developers. Whether you're debugging your site yourself, or providing the tech data for a support professional, this plugin makes it easy to gather all the data you need in seconds, without having to hunt for it.</p>
		</div>
</div>

<?php
	}

	/**
	 *	Generate Text file download
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 *	@action			wp_ajax_download_system_diagnostic
	 *	@return			void
	 */
	static public function download_data() {
		if( !self::is_user_admin() || empty( $_POST['rssd-textarea'] ) || !check_admin_referer( 'rssd_download_file_token', 'dlft_tkn' ) ) { return; }
		$timenow	= time();
		$datum		= date( RSSD_DATE_BASIC, $timenow );
		$append		= ( !empty( $_POST['option'] ) && 'advanced' === $_POST['option'] ) ? '_advanced' : '';
		header( 'Content-Type: text/plain' );
		header( 'Content-Disposition: attachment; filename='.RSSD_GET_VAR.'_' . $datum . '_' . $timenow . $append . '.txt' ); /* Unix timestamp appended to filename. */
		echo stripslashes( $_POST['rssd-textarea'] );	/* Use stripslashes() on file download */
		die();
	}

	/**
	 *	Gather data, then generate System Info
	 *	@dependencies	RS_System_Diagnostic_Browser() class, RS_System_Diagnostic::get_web_host(), RS_System_Diagnostic::ns(), RS_System_Diagnostic::sort_unique(), RS_System_Diagnostic::display_output()
	 *	@used by		...
	 *	@since			1.0.0
	 *	@return			void
	 */
	static public function display_data() {
		/* Make sure this only runs once, and cache data */
		if( defined( 'RSSD_DISPLAY_OUTPUT' ) ) {
			$display_output = RSSD_DISPLAY_OUTPUT;
			if( !empty( $display_output ) ) { return RSSD_DISPLAY_OUTPUT; }
		}
		/* Gather data if this has not run yet */
		$browser	= new RS_System_Diagnostic_Browser();
		$theme_data	= wp_get_theme();
		$theme		= $theme_data->Name . ' ' . $theme_data->Version;
		$web_host	= ( !empty( self::$web_host ) ) ? self::$web_host : self::get_web_host( self::$ip_dns_params );
		$web_host	= ( !empty( $web_host ) ) ? $web_host : 'Not Detected';
		$host_ns	= self::get_ns( RSSD_SITE_DOMAIN );
		$host_ns	= ( !empty( $host_ns ) && is_array( $host_ns ) ) ? implode( '  |  ', self::sort_unique( $host_ns ) ) : 'Not Detected';
		$req['cmd']	= '_notify-validate';
		$params = array(
			'sslverify' => FALSE,
			'timeout'   => 60,
			'body'      => $req,
		);
		$res		= wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );
		if( !is_wp_error( $res ) && $res['response']['code'] >= 200 && $res['response']['code'] < 300 ) {
			$wp_remote_post = 'wp_remote_post() works' . RSSD_EOL;
		} else {
			$wp_remote_post = 'wp_remote_post() does not work' . RSSD_EOL;
		}
		return self::display_output( $browser, $theme, $web_host, $host_ns, $wp_remote_post );
	}

	/**
	 *	Render System Diagnostic Data
	 *	Originally Based on "Send System Info" plugin by John Regan - https://wordpress.org/plugins/send-system-info/
	 *	@author			Scott Allen
	 *	@dependencies	...
	 *	@since			1.0.0
	 *	@param			string	$browser			Browser information
	 *	@param			string	$theme				Theme Data
	 *	@param			string  $web_host			Web Host
	 *	@param			string  $host_ns			Host NS
	 *	@param			string  $wp_remote_post		WP Remote Post
	 *	@return			string  Output of System Diagnostic Data Display
	 */
	static public function display_output( $browser, $theme, $web_host, $host_ns, $wp_remote_post ) {
		global $wpdb,$is_apache,$is_IIS,$is_iis7,$is_nginx;
		ob_start();
		require_once RSSD_PLUGIN_INCL_PATH.RSSD_DS.'admin.output-data.php';
		$output = ob_get_clean();
		if( !defined( 'RSSD_DISPLAY_OUTPUT' ) ) { define( 'RSSD_DISPLAY_OUTPUT', $output ); }
		return $output;
	}

	static public function error_level_tostring( $intval, $separator ) {
		
		$error_levels = array(
			E_ALL					=> 'E_ALL',
			E_USER_DEPRECATED		=> 'E_USER_DEPRECATED',	/* Not understood in PHP 5.2*/
			E_DEPRECATED			=> 'E_DEPRECATED',		/* Not understood in PHP 5.2*/
			E_RECOVERABLE_ERROR		=> 'E_RECOVERABLE_ERROR',
			E_STRICT				=> 'E_STRICT',
			E_USER_NOTICE			=> 'E_USER_NOTICE',
			E_USER_WARNING			=> 'E_USER_WARNING',
			E_USER_ERROR			=> 'E_USER_ERROR',
			E_COMPILE_WARNING		=> 'E_COMPILE_WARNING',
			E_COMPILE_ERROR			=> 'E_COMPILE_ERROR',
			E_CORE_WARNING			=> 'E_CORE_WARNING',
			E_CORE_ERROR			=> 'E_CORE_ERROR',
			E_NOTICE				=> 'E_NOTICE',
			E_PARSE					=> 'E_PARSE',
			E_WARNING				=> 'E_WARNING',
			E_ERROR					=> 'E_ERROR'
		);
		$result = '';
		foreach( $error_levels as $num => $name ) {
			if( ( $intval & $num ) == $num ) {
				$result .= ( $result != '' ? $separator : '') .$name;
			}
		}
		return $result;
	}

	static public function append_log_data( $var_name = NULL, $var_val = '', $str = NULL, $line = NULL, $func = NULL, $meth = NULL, $class = NULL, $file = NULL ) {
		/**
		 *	Adds data to the log for debugging - only use when debugging, with WP_DEBUG & RSSD_DEBUG
		 *	Format:
		 * 		self::append_log_data( $var_name, $var_val, [$str = FALSE, $line = NULL, $func = NULL, $meth = NULL, $class = NULL, $file = NULL] );
		 * 		self::append_log_data( $var_name, $var_val, [$str = FALSE, $line = __LINE__, $func = __FUNCTION__, $meth = __METHOD__, $class = __CLASS__, $file = __FILE__] );
		 *	Example:
		 * 		self::append_log_data(	'$var_name',	$var_val,	$string_in_lieu_of_env_data		);
		 * 		self::append_log_data(	'$var_name',	$var_val,	FALSE,	__LINE__,	__FUNCTION__,	__METHOD__,	__CLASS__,	__FILE__	);
		 */
		if( TRUE === WP_DEBUG && TRUE === RSSD_DEBUG ) {
			$log_str  = 'RS System Diagnostic DEBUG: ['. self::get_ip_addr() .']['. self::get_url() .'] ';
			if( !empty( $var_name ) ) {
				if( is_bool( $var_val ) ) {
					$fl = '[B]'; $var_v = ( !empty( $var_val ) ) ? 'TRUE' : 'FALSE';
				} elseif( is_string( $var_val ) || is_numeric( $var_val ) || is_null( $var_val ) ) {
					$fl = '[S]'; $var_v = (string) $var_val;
				} elseif( is_array( $var_val ) ) {
					$fl = '[A]'; $var_v = print_r( $var_val, TRUE );
				} elseif( is_object( $var_val ) ) {
					$fl = '[O]'; $var_v = print_r( $var_val, TRUE );
				} else {
					$fl = '[X]'; $var_v = print_r( $var_val, TRUE );
				}
			$log_str .= $fl.$var_name.': "'.$var_v;
			} else {
				$log_str .= (string) $str;
			}
			if( !empty( $line ) && !empty( $func ) && !empty( $meth ) && !empty( $class ) && !empty( $file ) ) {
				$log_str .= '" | Line: '.$line.' | Function: '.$func.' | Method: '.$meth.' | Class: '.$class.' | File: '.$file;
			}
			$log_str .= ' | MEM USED: ' . self::wp_memory_used() . ' | VER: ' . RSSD_VERSION;
			$log_str  = trim( self::filter_null( $log_str ) ); /* Remove any null bytes to prevent breakage */
			@error_log( $log_str, 0 ); /* Logs to debug.log */
		}
	}

	/**
	 *  Get memory currently used by WordPress
	 *	@dependencies	RS_System_Diagnostic::format_bytes(), 
	 *	@func_ver		RSSD.20170111.01
	 *	@since			RSSD 1.0.4
	 */
	static public function wp_memory_used( $peak = FALSE, $raw = FALSE ) {
		$mem = 0;
		if( TRUE === $peak && function_exists( 'memory_get_peak_usage' ) ) {
			$mem = memory_get_peak_usage( TRUE );
		} elseif( function_exists( 'memory_get_usage' ) ) {
			$mem = memory_get_usage();
		}
		return ( !empty( $mem ) && FALSE === $raw ) ? self::format_bytes( $mem ) : $mem;
	}

	/**
	 *  Get the number of days between two timestamps
	 *  @dependencies	none
	 *  @used by		...
	 *  @since			1.0.0
	 */
	static public function date_diff( $start, $end ) {
		$start_ts		= strtotime( $start );
		$end_ts			= strtotime( $end );
		$diff			= ( $end_ts - $start_ts );
		$start_array	= explode( '-', $start );
		$start_year		= $start_array[0];
		$end_array		= explode( '-', $end );
		$end_year		= $end_array[0];
		$years			= ( $end_year - $start_year );
		$extra_days		= ( ( $years % 4 ) == 0 ) ? ( ( ( $end_year - $start_year ) / 4 ) - 1 ) : ( ( $end_year - $start_year ) / 4 );
		$extra_days		= round( $extra_days );
		return round( $diff / 86400 ) + $extra_days;
	}

	/**
	 *	Get Formatted PHP Memory Limit
	 *	@author			Scott Allen
	 *	@dependencies	
	 *	@since			1.0.2
	 *	@return			string
	 */
	static public function get_php_memory_limit() {
		$memory_limit = ini_get( 'memory_limit' );
		$suffixes = array('k', 'm', 'g', 't'); $suf = FALSE;
		if( is_string( $memory_limit ) ) {
			foreach( $suffixes as $i => $s ) { if( FALSE !== stripos( $memory_limit, $s ) ) { $suf = TRUE; break; } }
		}
		if( TRUE === $suf ) { return $memory_limit; }
		$php_memory_limit = self::format_bytes( $memory_limit );	
		return $php_memory_limit;
	}

	/**
	 *	Get WordPress Database Size
	 *	@author			Scott Allen
	 *	@dependencies	...
	 *	@since			1.0.0
	 *	@return			string
	 */
	static public function get_db_size() {
		global $wpdb; $db_size = 0;
		$query = $wpdb->get_results("SHOW table STATUS", ARRAY_A);
		foreach( $query as $row ) {
			$db_size += $row['Data_length'] + $row['Index_length'];
		}
		$db_size = self::format_bytes( $db_size ); 
		return $db_size;
	}

	/**
	 *	Data Size Conversions
	 *	@dependencies	...
	 *	@since			1.0.0
	 *	@param			string		$v
	 *	@return			int|string
	 */
	static public function let_to_num( $v ) {
		$l   = substr( $v, -1 );
		$ret = substr( $v, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
			case 'T':
			case 'G':
			case 'M':
			case 'K':
				$ret *= 1024; break;
			default:
				break;
		}
		return $ret;
	}

	/**
	 *	Generate Random URL for the remote view.
	 *	Saves result to options. If it's an ajax request the new query value is sent back to the JS script.
	 *	@dependencies	...
	 *	@since			1.0.0
	 *	@action			wp_ajax_rssd_regenerate_url
	 *	@return			void|$remote_url
	 */
	static public function generate_url( $regen = FALSE ) {
		$alphabet		= 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789';
		$value			= array();
		$alphaLength 	= strlen( $alphabet ) - 1;
		for ( $i = 0; $i < 32; $i++ ) {
			$n     		= self::rand( 0, $alphaLength );
			$value[] 	= $alphabet[$n];
		}
		$remote_url_key = implode( $value );
		self::update_option( array( 'remote_url_key' => $remote_url_key, ) );
		$remote_url = RSSD_SITE_URL.'/?'.RSSD_GET_VAR.'='.$remote_url_key;
		if( TRUE === $regen ) { return $remote_url; }
		if( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			wp_send_json( $remote_url );
		}
	}

	/**
	 *	Get unique URL id.
	 *	@dependencies	RS_System_Diagnostic::get_option(), RS_System_Diagnostic::get_ip_dns_params(), RS_System_Diagnostic::rand(), RS_System_Diagnostic::md5(), RS_System_Diagnostic::update_option(), 
	 *	@since			1.0.0
	 */
	static public function get_url_id( $params = array() ) {
		$remote_url_id	= self::get_option( 'remote_url_id' );
		if( !empty( $remote_url_id ) ) { return $remote_url_id; }
		if( empty( $params ) || !is_array( $params ) ) { $params = self::get_ip_dns_params(); }
		extract( $params );
		$timenow	= time();
		$r			= self::rand( 1000, 9999 );
		$new_id		= substr( self::md5( 'rssd_url_id_'.$server_name.'_'.$timenow.'_'.$r ), -12, 10 );
		self::update_option( array( 'remote_url_id' => $new_id, ) );
		return $new_id;
	}

	/**
	 *	Wrapper for wp_die() with nicer formatting.
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 */
	static public function wp_die( $error_msg = NULL, $status_code = '403' ) {
		$error_msg	= ( empty( $error_msg ) ) ? __( 'Sorry, you are not allowed to access this page.' ) : $error_msg;
		$error_txt	= __( 'ERROR', 'rs-system-diagnostic' );
		$error_str	= '<strong>'.$error_txt.':</strong> '.$error_msg.RSSD_EOL;
		$args		= array( 'response' => $status_code );
		wp_die( $error_str, '', $args );
	}

	static public function md5( $string ) {
		/* Use this function instead of hash for compatibility. BUT hash is faster than md5 for multiple iterations, so use it whenever possible. */
		return function_exists( 'hash' ) ? hash( 'md5', $string ) : md5( $string );
	}

	static public function json_encode( $data, $options = 0, $depth = 512 ) {
		/**
		 *  Use this function instead of json_encode() for compatibility, esp with non-UTF-8 data. wp_json_encode() was added in WP ver 4.1
		 *  @since			1.0.4
		 */
		return ( function_exists( 'wp_json_encode' ) && self::is_wp_ver('4.1') ) ? wp_json_encode( $data, $options, $depth ) : json_encode( $data, $options );
	}

	static public function rand( $min, $max ) {
		/* Use this function instead of mt_rand() for compatibility. BUT mt_rand() is better than rand(), so use it whenever possible. */
		return ( function_exists( 'mt_rand' ) && mt_rand( $min, $max ) != NULL ) ? mt_rand( $min, $max ) : rand( $min, $max );
	}

	/**
	 *	Functions to get, update, and delete plugin options
	 *	All RSSD plugin options are stored in array in one DB option to keep DB slimmer & faster
	 *	@dependencies	...
	 *	@used by		...
	 *	@since			1.0.0
	 */
	static public function get_option( $option = 'all', $decrypt = FALSE ) {
		global $rssd_x01_options;
		if( empty( $rssd_x01_options ) || !is_array( $rssd_x01_options ) ) {
			$rssd_x01_options = get_option('rs_system_diagnostic');
			if( empty( $rssd_x01_options ) ) { $rssd_x01_options = array(); }
		}
		if( 'all' === $option ) { return $rssd_x01_options; }
		return isset( $rssd_x01_options[$option] ) ? $rssd_x01_options[$option] : '';
	}

	static public function update_option( $arr, $update = TRUE, $params = array() ) {
		/**
		 *	Differs from update_option() in that it only takes an associative array with option/value pair(s) and can update multiple options at once.
		 *	All RSSD plugin options are stored in array in one DB option to keep DB slimmer & faster
		 */
		if( empty( $arr ) || !is_array( $arr ) ) { return; }
		global $rssd_x01_options;
		if( empty( $rssd_x01_options ) || !is_array( $rssd_x01_options ) ) {
			$rssd_x01_options = get_option('rs_system_diagnostic');
			if( empty( $rssd_x01_options ) ) { $rssd_x01_options = array(); }
		}
		foreach ( $arr as $option => $value ) { $rssd_x01_options[$option] = $value; }
		if( TRUE === $update ) { update_option( 'rs_system_diagnostic', $rssd_x01_options ); }
	}

	static public function delete_option( $arr, $update = TRUE, $params = array() ) {
		/**
		 *	Differs from delete_option() in that it only takes a numeric array with option(s) to delete, and can delete multiple items at once.
		 */
		if( empty( $arr ) || !is_array( $arr ) ) { return; }
		global $rssd_x01_options;
		if( empty( $rssd_x01_options ) || !is_array( $rssd_x01_options ) ) {
			$rssd_x01_options = get_option( 'rs_system_diagnostic' );
			if( empty( $rssd_x01_options ) ) { $rssd_x01_options = array(); }
		}
		foreach ( $arr as $i => $option ) { unset( $rssd_x01_options[$option] ); }
		if( TRUE === $update ) { update_option( 'rs_system_diagnostic', $rssd_x01_options ); }
	}

	static public function get_user_meta( $key, $user_id = NULL, $single = TRUE ) {
		global $current_user,$rssd_x2_meta;
		if( empty( $user_id ) ) { $user_id = $current_user->ID; }
		if( empty( $rssd_x2_meta ) || !is_array( $rssd_x2_meta ) ) { $rssd_x2_meta = array(); }
		if( !isset( $rssd_x2_meta[$user_id] ) ) {
			$rssd_x2_meta[$user_id] = get_user_meta( $user_id, 'rssd_meta', $single );
			if( empty( $rssd_x2_meta[$user_id] ) ) { $rssd_x2_meta[$user_id] = array(); }
		}
		return isset( $rssd_x2_meta[$user_id][$key] ) ? $rssd_x2_meta[$user_id][$key] : '';
	}

	static public function update_user_meta( $arr, $user_id = NULL, $single = TRUE ) {
		/**
		 *	Differs from update_user_meta() in that it only takes an associative array with key/value pair(s) and can update multiple meta fields at once.
		 *	All RSSD plugin meta fields stored in array in one DB option to keep DB slimmer & faster
		 */
		if( empty( $arr ) || !is_array( $arr ) ) { return; }	
		global $current_user,$rssd_x2_meta;
		if( empty( $user_id ) ) { $user_id = $current_user->ID; }
		if( empty( $rssd_x2_meta ) || !is_array( $rssd_x2_meta ) ) { $rssd_x2_meta = array(); }
		if( !isset( $rssd_x2_meta[$user_id] ) ) {
			$rssd_x2_meta[$user_id] = get_user_meta( $user_id, 'rssd_meta', $single );
			if( empty( $rssd_x2_meta[$user_id] ) ) { $rssd_x2_meta[$user_id] = array(); }
		}
		foreach ( $arr as $key => $value ) { $rssd_x2_meta[$user_id][$key] = $value; }
		update_user_meta( $user_id, 'rssd_meta', $rssd_x2_meta[$user_id] );
	}

	static public function delete_user_meta( $arr, $user_id = NULL, $single = TRUE) {
		/**
		 *	Differs from delete_user_meta() in that it only takes a numeric array with meta field(s) to delete, and can delete multiple meta_fields at once.
		 */
		if( empty( $arr ) || !is_array( $arr ) ) { return; }
		global $current_user,$rssd_x2_meta;
		if( empty( $user_id ) ) { $user_id = $current_user->ID; }
		if( empty( $rssd_x2_meta ) || !is_array( $rssd_x2_meta ) ) { $rssd_x2_meta = array(); }
		if( !isset( $rssd_x2_meta[$user_id] ) ) {
			$rssd_x2_meta[$user_id] = get_user_meta( $user_id, 'rssd_meta', $single );
			if( empty( $rssd_x2_meta[$user_id] ) ) { $rssd_x2_meta[$user_id] = array(); }
		}
		foreach ( $arr as $i => $key ) { unset( $rssd_x2_meta[$user_id][$key] ); }
		update_user_meta( $user_id, 'rssd_meta', $rssd_x2_meta[$user_id] );
	}

	static public function detect_wpconfig() {
		/**
		 *	Detect location of wp-config.php file, using the same method WordPress does
		 */
		@clearstatcache();
		if ( @file_exists( ABSPATH . 'wp-config.php') ) {
			/* The config file resides in ABSPATH */
			return( ABSPATH . 'wp-config.php' );
		}
		elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			/* The config file resides one level above ABSPATH but is not part of another install */
			return( dirname( ABSPATH ) . '/wp-config.php' );
		}
		/* wp-config.php not detected */
		return FALSE;
	}

	static public function detect_htaccess() {
		/**
		 *	Detect location of .htaccess file(s) - Apache only
		 */
		global $is_nginx; if( TRUE === $is_nginx ) { return FALSE; }
		$upload_dir = wp_upload_dir();
		$dirs_to_check = array( dirname( $_SERVER['DOCUMENT_ROOT'] ), $_SERVER['DOCUMENT_ROOT'], ABSPATH, ABSPATH.RSSD_DS.'wp-admin', RSSD_CONTENT_DIR_PATH, RSSD_PLUGINS_DIR_PATH, $upload_dir['basedir'], );
		$htaccess_files = array();
		@clearstatcache();
		foreach( $dirs_to_check as $i => $path ) {
			$path = untrailingslashit( $path );
			$file = $path.RSSD_DS.'.htaccess';
			if( @file_exists( $file ) ) { $htaccess_files[] = $file; }
		}
		if( !empty( $htaccess_files ) ) { $htaccess_files = self::sort_unique( $htaccess_files ); return $htaccess_files; }
		return FALSE;
	}

	/**
	 *	Detect location of php.ini file(s)
	 *	Check:
	 *	- $_SERVER['PHPRC'], $_ENV['PHPRC'], $_SERVER['PHP_INI_SCAN_DIR'], $_ENV['PHP_INI_SCAN_DIR']
	 *	- php_ini_loaded_file()		- http://php.net/manual/en/function.php-ini-loaded-file.php
	 *	- php_ini_scanned_files ()	- http://php.net/manual/en/function.php-ini-scanned-files.php
	 *	- ** user.ini ** 				- http://php.net/manual/en/configuration.file.per-user.php
	 *	- On Dreamhost, "phprc" instead of "php.ini":
	 *		- https://help.dreamhost.com/hc/en-us/articles/214200688-php-ini-overview
	 *		- https://help.dreamhost.com/hc/en-us/articles/214894037-How-do-I-create-a-phprc-file-via-FTP-
	 */
	static public function detect_php_ini() {
		$dirs_to_check = array( dirname( $_SERVER['DOCUMENT_ROOT'] ), $_SERVER['DOCUMENT_ROOT'], ABSPATH, );
		$web_host = self::get_web_host( self::$ip_dns_params );
		if( !empty( $web_host ) && 'DreamHost' === $web_host && defined( 'PHP_MAJOR_VERSION' ) && defined( 'PHP_MINOR_VERSION' ) ) {
			$dirs_to_check[] = dirname( $_SERVER['DOCUMENT_ROOT'] ) . RSSD_DS . '.php' . RSSD_DS . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
		}
		if( function_exists( 'php_ini_scanned_files' ) ) {
			$php_ini_scanned_files = php_ini_scanned_files();
		}
		if( function_exists( 'php_ini_loaded_file' ) ) {
			$php_ini_loaded_file = php_ini_loaded_file();
		}
		$env_vars = array( 'PHPRC', 'PHP_INI_SCAN_DIR' ); $def_vars = array();
		foreach( $_SERVER as $k => $v ) {
			if( FALSE !== strpos( $k, 'PHP_INI' ) && 'PHP_INI_SCAN_DIR' !== $k ) { $env_vars[] = $k; }
		}
		foreach( $env_vars as $k ) {
			if( !empty( $_SERVER[$k] ) ) { $def_vars[] = $_SERVER[$k]; }
			if( !empty( self::$_ENV[$k] ) ) { $def_vars[] = self::$_ENV[$k]; }
		}
		unset( $env_vars, $k );
		$def_vars[] = $php_ini_scanned_files;
		$def_vars[] = $php_ini_loaded_file;
		foreach( $def_vars as $v ) {
			if( !empty( $v ) && is_string( $v ) ) {
				$v = str_replace( array( 'php.ini', '.user.ini', 'phprc' ), '', $v );
				if( FALSE !== strpos( $v, ',' ) ) {
					$dirs_to_check = array_merge( $dirs_to_check, explode( ',', $v ) );
				} else {
					$dirs_to_check[] = $v;
				}
			}
		}
		unset( $def_vars, $v );
		$dirs_to_check = self::sort_unique( $dirs_to_check );
		$php_ini_files = array();
		@clearstatcache();
		foreach( $dirs_to_check as $i => $path ) {
			$path = str_replace( array( 'php.ini', '.user.ini', 'phprc' ), '', $path );
			$path = untrailingslashit( $path );
			$file = $path.RSSD_DS.'php.ini';
			$fusr = $path.RSSD_DS.'.user.ini';
			$fprc = $path.RSSD_DS.'phprc';
			if( @file_exists( $file ) ) { $php_ini_files[] = $file; }
			if( @file_exists( $fusr ) ) { $php_ini_files[] = $fusr; }
			if( @file_exists( $fprc ) ) { $php_ini_files[] = $fprc; }
		}
		unset( $i, $path, $file, $fusr );
		if( !empty( $php_ini_files ) ) { $php_ini_files = self::sort_unique( $php_ini_files ); return $php_ini_files; }
		return FALSE;
	}

	/**
	 *	Detect configuration settings that have been changed at runtime.
	 *	Compares global (php.ini) vs. local (runtime)
	 *	- ini_get_all()		http://php.net/manual/en/function.ini-get-all.php
	 *	@dependencies	...
	 *	@since			1.0.9
	 */
	static public function ini_diff() {
		$ini_all	= ( function_exists( 'ini_get_all' ) ) ? (array) @ini_get_all() : array();
		$ini_diff	= array();
		$ini_difr	= array();	/* Raw */
		foreach( $ini_all as $k => $v ) {
			if( $v['local_value'] === $v['global_value'] ) { continue; }
			$ini_difr[$k]	= $v;
			$ini_diff[$k]	=
				array(
					'original'	=> $v['global_value'],
					'updated'	=> $v['local_value'],
				);
		}
		return self::msort_array( $ini_diff );
	}

	/**
	 *	Add plugins to PHP Compatibility Checker Plugin whitelist to prevent false positives
	 *	All our plugins are fully PHP 7+ compatible
	 *	@dependencies	...
	 *	@since			1.0.5
	 */
	static public function php_compat( $ignored = array() ) {
		if( !is_array( $ignored ) ) { return $ignored; }
		$rsmg_plugins = array( 'rs-feedburner', 'rs-head-cleaner', 'rs-head-cleaner-lite', 'rs-nofollow-blogroll', 'rs-system-diagnostic', 'scrapebreaker', 'wp-spamshield' );
		foreach( $rsmg_plugins as $i => $p ) {
			$plugin = '*/'.$p.'/*';
			if( !RSSD_PHP::in_array( $plugin, $ignored ) ) {
				$ignored[] = $plugin;
			}
		}
		return $ignored;
	}

	/**
	 *  TO DO:
	 *	- Scan for and pull in debug.log and error_log files:
	 *  - Add detect_debug_log()
	 *  - Add detect_error_log()
	 *  - Add analyze_debug_log()
	 *  - Provide links and answers for how to solve php.ini problems, along with one-click fixes
	 *  - Tab Navigation
	 */

}


/* Apache Headers Fallback Functions - BEGIN */

/**
 *  If apache_response_headers() is not defined, then replace with fallback function
 *  apache_response_headers() may not be available on Nginx and certain other server setups
 *	@dependencies	...
 *	@used by		...
 *	@since			1.0.5
 */
if( !function_exists( 'apache_response_headers' ) ) {
	function apache_response_headers() {
		if( !defined( 'RSSD_FALLBACK_APACHE_RESPONSE_HEADERS' ) ) { define( 'RSSD_FALLBACK_APACHE_RESPONSE_HEADERS', TRUE ); }
		$headers_list = headers_list();
		if ( empty( $headers_list ) || !is_array( $headers_list ) ) { return array(); }
		$headers = array();
		foreach( $headers_list as $h ) {
			$h = explode(':',$h);
			$headers[array_shift($h)] = trim(implode(':',$h));
		}
		return $headers;
	}
}

/**
 *  If apache_request_headers() is not defined, then replace with fallback function
 *  apache_request_headers() may not be available on Nginx and certain other server setups
 *	@dependencies	...
 *	@used by		...
 *	@since			1.0.5
 */
if( !function_exists( 'apache_request_headers' ) ) {
	function apache_request_headers() {
		if( !defined( 'RSSD_FALLBACK_APACHE_REQUEST_HEADERS' ) ) { define( 'RSSD_FALLBACK_APACHE_REQUEST_HEADERS', TRUE ); }
		if ( empty( $_SERVER ) || !is_array( $_SERVER ) ) { return array(); }
		$headers = array();
		foreach ( $_SERVER as $k => $v ) {
			if ( substr( $k, 0, 5 ) === 'HTTP_' ) {
				$headers[str_replace(' ','-',ucwords(strtolower(str_replace('_',' ',substr($k,5)))))] = $v;
			}
		}
		return $headers;
	}
}

/* Apache Headers Fallback Functions - END */




/**
 * Fire up the plugin
 */
add_action( 'plugins_loaded', array( 'RS_System_Diagnostic', 'setup' ), -10000 );

