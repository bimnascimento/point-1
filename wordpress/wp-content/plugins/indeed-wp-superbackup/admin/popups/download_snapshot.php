<div class="ibk-popup-wrapp" id="ibk_popup_box">
	<div class="ibk-the-popup">
		<div class="ibk-popup-top">
			<div class="title">Download Snapshot</div>
			<div class="close-bttn" onclick="ibk_close_popup();"></div>
			<div class="clear"></div>
		</div>
		<div class="ibk-popup-content">
			<?php 
				$backup_id = $_REQUEST['snapshot_id'];
				$destination_id = $_REQUEST['destination_id'];
				
				
				$destination_type = ibk_get_destination_type($destination_id);
				switch ($destination_type){
					case 'google':
						if (!class_exists('IndeedGoogle')){
							require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
						}
						$goo = new IndeedGoogle($destination_id);
						$goo->login();
						$arr = ibk_get_google_files_for_backup_destination_id($goo, $backup_id);
						if (count($arr)>1){
							foreach ($arr as $inside_arr){
								$print_arr[$inside_arr['title']] = $goo->get_file_url($inside_arr['fileId']);
							}
						}
						break;
							
					case 'local':
						$return_arr = ibk_return_local_files_for_destination_backup($destination_id, $backup_id);
						if (count($return_arr)>1){
							foreach ($return_arr as $path){
								$print_arr[$path] = ibk_make_url_for_local_snapshot($destination_id, $path);						
							}
						}
						break;
							
					case 'ftp':
						if (!class_exists('IndeedFtp')){
							require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
						}
						$ftp = new IndeedFtp($destination_id);//destination id
						$ftp->login();
						$return_arr = $ftp->list_snapshots($backup_id);//snapshot id
						print_r($return_arr);
						if (count($return_arr)>1){
							foreach ($return_arr as $path){	
								$print_arr[$path] = ibk_return_ftp_link_to_file($destination_id, $path);
							}
						}
						break;
					
					case 'dropbox':
						if (!class_exists('IndeedDropbox')){
							require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
						}
						$obj = new IndeedDropbox($destination_id);
						$obj->login();
						$arr = ibk_return_dropbox_files_arr_for_backup_id($obj, $backup_id);
						if (count($arr)>1){
							foreach ($arr as $value){
								$print_arr[$value] = $obj->get_url_for_file($value);				
							}
						}
						break;
						
					case 'amazon':
						if (!class_exists('IndeedAmazonS3')){
							require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
						}
						$obj = new IndeedAmazonS3($destination_id);
						$arr = ibk_return_amazon_files_arr_for_backup_id($obj, $backup_id);
						if (count($arr)>1){
							foreach ($arr as $value){
								$print_arr[$value] = $obj->get_url_for_file($value);
							}
						}					
						break;
					case 'onedrive':
						if (!class_exists('IndeedOneDrive')){
							require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
						}
						$obj = new IndeedOneDrive($destination_id);
						$arr = ibk_return_onedrive_files_for_backup_id($obj, $backup_id);
						if (count($arr)>1){
							foreach ($arr as $value){
								$print_arr[$value] = $obj->get_url_for_file($value);
							}
						}	
						break;
						
					case 'copy':
						require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
						$obj = new IndeedCopyDotCom($destination_id);
						$obj->login();
						$arr = ibk_return_copydotcom_files_for_backup_id($obj, $backup_id);
						if (count($arr)>1){
							foreach ($arr as $value){
								$print_arr[$value] = $obj->get_download_link($value);
							}
						}		
						break;
							
				}
				if (!empty($print_arr) && count($print_arr)>1){
					?>
					<div class="ibb-popup-list-snapshots-instances" style="overflow: hidden;">
					<?php 
					foreach ($print_arr as $title=>$url){						
						?>						
							<a href="<?php echo $url?>" target="_blank" class="ibk-download-snapshot-item-popup" style="display: block; line-height: 16px;">
								<div style="display: block;"><i class="fa-ibk fa-download-ibk"></i></div>
								<div style="display: block;line-height: 1px;">SNAPSHOT</div>
								<div style="display: block;line-height: 1px;line-height: 7px; font-size: 12px;">From
								<?php 
									$title = basename($title);
									$title = str_replace('.zip', '', $title);
									$snapshot_date = explode('_', $title);
									if (!empty($snapshot_date[3])){
										echo date("Y-m-d H:i:s", $snapshot_date[3]);
									}
								?></div>							
							</a>						
						<?php 
					}
					?>
					</div>
					<?php 
				}		
			?>
		</div>
	</div>
</div>
