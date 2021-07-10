<?php
/**
 *  RS System Diagnostic Data Utilities
 *  File Version 1.0.9
 */

if( !defined( 'ABSPATH' ) || !defined( 'RSSD_VERSION' ) ) {
	if( !headers_sent() ) { @header( $_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden', TRUE, 403 ); @header( 'X-Robots-Tag: noindex', TRUE ); }
	die( 'ERROR: Direct access to this file is not allowed.' );
}



/**
 * Utilities
 *
 * @package     RSSD
 * @subpackage  Classes/Utilities
 * @author      Scott Allen
 * @since       1.0.6
 */

class RSSD_Utils {
	
	/**
	 *  RS System Diagnostic Utility Class
	 *  Common utility functions
	 *  @since	1.0.6
	 */

	function __construct() {
		/**
		 *  Do nothing...for now
		 */
	}

}


class RSSD_PHP extends RSSD_Utils {

	/**
	 *  RS System Diagnostic PHP Function Replacements Class
	 *  Child class of RSSD_Util
	 *  Replacements for certain PHP functions
	 *  Child classes: RSSD_Func, 
	 *  @since	1.0.6
	 */

	function __construct() {
		/**
		 *  Do nothing...for now
		 */
	}

	/**
	 *  Convert case using multibyte version (superior) if available, if not, use defaults
	 *  Replaces PHP functions strtolower(), strtoupper(), ucfirst(), ucwords()
	 *  Usage:
	 *  - RSSD_PHP::casetrans( 'lower', $string ); // Ver 1.0.6+
	 *  Replaces:
	 *  - rssd_casetrans( 'lower', $string ); // Ver 1.0 - 1.0.5
	 *  @since 1.0 as rssd_casetrans()
	 *  @moved 1.0.6 to RSSD_PHP class
	 */
	static public function casetrans( $type, $string ) {
		if( empty( $string ) || empty( $type ) || !is_string( $string ) || !is_string( $type ) ) { return $string; }
		switch( $type ) {
			case 'upper':
				return ( function_exists( 'mb_strtoupper' ) ) ? mb_strtoupper( $string, 'UTF-8' ) : strtoupper( $string );
			case 'lower':
				return ( function_exists( 'mb_strtolower' ) ) ? mb_strtolower( $string, 'UTF-8' ) : strtolower( $string );
			case 'ucfirst':
				if( function_exists( 'mb_strtoupper' ) && function_exists( 'mb_substr' ) ) {
					$strtmp = mb_strtoupper( mb_substr( $string, 0, 1, 'UTF-8' ), 'UTF-8' ) . mb_substr( $string, 1, NULL, 'UTF-8' );
					return ( parent::strlen( $string ) === parent::strlen( $strtmp ) ) ? $strtmp : ucfirst( $string );
				} else { return ucfirst( $string ); }
			case 'ucwords':
				return ( function_exists( 'mb_convert_case' ) ) ? mb_convert_case( $string, MB_CASE_TITLE, 'UTF-8' ) : ucwords( $string );
			default:
				return $string;
		}
	}

	/**
	 *  Use this function instead of in_array() as it's *much* faster.
	 *  Equivalent of 'in_array( $needle, $haystack, TRUE )' ($strict = TRUE)
	 *  @dependencies	none
	 *  @used by		...
	 *  @since			1.0.9
	 *  @reference		http://php.net/manual/en/function.in-array.php
	 *  @param			string	$needle
	 *  @param			array	$haystack
	 */
	static public function in_array( $needle, $haystack ) {
		$haystack_flip = array_flip( $haystack );
		return ( isset( $haystack_flip[$needle] ) );
	}

}


class RSSD_Func extends RSSD_PHP {

	/**
	 *  RS System Diagnostic Utility Functions Alias Class
	 *  Aliases of PHP function replacements
	 *  Child class of RSSD_PHP; Grandchild class of RSSD_Util
	 *  Child classes: ... 
	 *  @since	1.0.6
	 */

	function __construct() {
		/**
		 *  Do nothing...for now
		 */
	}

	/**
	 *  Alias of RSSD_PHP::casetrans( 'lower', $string )
	 *  Replaces PHP function strtolower()
	 *  @usage	RSSD_Func::lower( $str )
	 *  @since	1.0.6
	 */
	static public function lower( $str ) {
		return parent::casetrans( 'lower', $str );
	}

	/**
	 *  Alias of RSSD_PHP::casetrans( 'upper', $string )
	 *  Replaces PHP function strtoupper()
	 *  @usage	RSSD_Func::upper( $str )
	 *  @since	1.0.6
	 */
	static public function upper( $str ) {
		return parent::casetrans( 'upper', $str );
	}

	/**
	 *  Alias of RSSD_PHP::casetrans( 'upper', $string )
	 *  Replaces PHP function ucfirst()
	 *  @usage	RSSD_Func::ucfirst( $str )
	 *  @since	1.0.6
	 */
	static public function ucfirst( $str ) {
		return parent::casetrans( 'ucfirst', $str );
	}

	/**
	 *  Alias of RSSD_PHP::casetrans( 'upper', $string )
	 *  Replaces PHP function ucwords()
	 *  @usage	RSSD_Func::ucwords( $str )
	 *  @since	1.0.6
	 */
	static public function ucwords( $str ) {
		return parent::casetrans( 'ucwords', $str );
	}

}

