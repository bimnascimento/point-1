<?php 
require_once '../../../../wp-load.php';
require_once '../utilities.php';

if (isset($_REQUEST['oauth_verifier'])){
	//STEP 2.
	$destination_id = ibk_get_last_destination_instance('copy');
	if ($destination_id){
		require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
		$object = new IndeedCopyDotCom($destination_id);
		$object->auth();		
	}
}

$url = get_admin_url() . 'admin.php?page=ibk_admin&tab=destinations';
wp_redirect($url);
exit;
