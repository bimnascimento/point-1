<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	return;
}

if ( ! current_user_can( 'activate_plugins' ) ) {
	return;
}

if ( ! defined( 'FILETRIP_BKP_REQUIRED_WP_VERSION' ) ) {
	define( 'FILETRIP_REQUIRED_WP_VERSION', '4.2' );
}

// Don't activate on old versions of WordPress
global $wp_version;

if ( version_compare( $wp_version, FILETRIP_REQUIRED_WP_VERSION, '<' ) ) {
	return;
}

if ( ! defined( 'ITECHFILETRIPPLGUINURI' ) ) {
	define( 'ITECHFILETRIPPLGUINURI', plugin_dir_path( __FILE__ ) );
}

// Load the schedules
require_once ITECHFILETRIPPLGUINURI . 'includes/arfaly-lib/arfaly-backup/backup-core.php';
require_once ITECHFILETRIPPLGUINURI . 'includes/arfaly-lib/arfaly-backup/class-backup.php';
require_once ITECHFILETRIPPLGUINURI . 'includes/arfaly-lib/arfaly-backup/class-services.php';
require_once ITECHFILETRIPPLGUINURI . 'includes/arfaly-lib/arfaly-backup/class-schedule.php';
require_once ITECHFILETRIPPLGUINURI . 'includes/arfaly-lib/arfaly-backup/class-schedules.php';

$schedules = FILETRIP_BKP_Schedules::get_instance();

// Cancel all the schedules and delete all the backups
foreach ( $schedules->get_schedules() as $schedule ) {
	$schedule->cancel( true );
}

// Remove the backups directory
filetrip_bkp_rmdirtree( filetrip_bkp_path() );

// Remove all the options
foreach ( array( 'dropbox_setting' ,'filetrip_bkp_enable_support', 'filetrip_bkp_plugin_version', 'filetrip_bkp_path', 'filetrip_bkp_default_path', 'filetrip_bkp_upsell' ) as $option ) {
	delete_option( $option );
}

// Delete all transients
foreach ( array( Filetrip_Constants::ERROR_TRANSIENT, 'filetrip_bkp_plugin_data', 'filetrip_bkp_directory_filesizes', 'filetrip_bkp_directory_filesize_running' ) as $transient ) {
	delete_transient( $transient );
}
