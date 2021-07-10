<?php 
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) exit();
global $wpdb;
$tables = array('indeed_backups', 'indeed_backup_metas', 'indeed_destinations', 'indeed_destination_metas', 'indeed_logs');
foreach ($tables as $table){
	$wpdb->query("DROP TABLE `" . $wpdb->base_prefix . $table . "`;");
}

//delete temporary dir
$gen_metas = get_option('ibk_general_metas');
$temp_dir = $gen_metas['ibk_backup_dir'];
$tempDir = ($temp_dir) ? WP_CONTENT_DIR . '/uploads/' . $temp_dir : WP_CONTENT_DIR . '/uploads/isnapshots';

require_once plugin_dir_path(__FILE__) . 'utilities.php';
$dirs = array($tempDir, WP_CONTENT_DIR . '/uploads/indeed-backups');
foreach ($dirs as $dir){
	indeed_delete_dir_recursive($dir);
}