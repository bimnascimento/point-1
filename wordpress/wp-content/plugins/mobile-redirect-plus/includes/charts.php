<?php

add_action( 'admin_init','google_chart_api');

function google_chart_api() {
	wp_register_script( 'google_chart_api', 'https://www.google.com/jsapi');
	wp_enqueue_script('google_chart_api');
}
