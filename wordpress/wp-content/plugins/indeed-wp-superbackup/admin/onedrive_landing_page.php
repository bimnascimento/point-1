<?php 
require_once '../../../../wp-load.php';
require_once '../utilities.php';

if (!empty($_GET['code'])){
	$destination_id = ibk_get_last_destination_instance('onedrive');
	if ($destination_id){
		require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
		$obj = new IndeedOneDrive($destination_id);
		$obj->set_state($_GET['code']);		
	}	
}
$url = get_admin_url() . 'admin.php?page=ibk_admin&tab=destinations';
wp_redirect($url);
exit;

