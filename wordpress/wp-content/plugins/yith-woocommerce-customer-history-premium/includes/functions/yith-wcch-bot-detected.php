<?php

defined( 'ABSPATH' ) or exit;

/*
 *  YITH Get customer total spent
 */

if ( ! function_exists( 'yith_wcch_bot_detected' ) ) {

	function yith_wcch_bot_detected() {

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT'] ) ) { return true; }
		else { return false; }

	}

}