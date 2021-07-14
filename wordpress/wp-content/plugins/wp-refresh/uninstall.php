<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
{
    exit();
}

$options = array(
	'wpr_options'
);
foreach( $options as $option )
{
	delete_option( $option );
}