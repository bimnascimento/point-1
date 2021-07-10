<?php 
/*
 * Admin main class
 */
if (!class_exists('IndeedAdmin')){
	class IndeedAdmin{
		public function __construct(){
			add_action( 'admin_menu', array($this, 'indeed_admin_menu') );
			add_action( "admin_enqueue_scripts", array($this, 'ibk_head') );
			add_action( 'wp_ajax_ibk_google_authorize_ajax', array($this, 'ibk_google_authorize_ajax'));
			add_action( 'wp_ajax_ibk_get_table_list_via_ajax', array($this, 'ibk_get_table_list_via_ajax'));
			add_action( 'wp_ajax_ibk_delete_item_via_ajax', array($this, 'ibk_delete_item_via_ajax'));	
			add_action( 'wp_ajax_ibk_save_destination_metas_via_ajax', array($this, 'ibk_save_destination_metas_via_ajax'));		
			add_action( 'wp_ajax_ibk_test_ftp_connection', array($this, 'ibk_test_ftp_connection'));			
			add_action( 'wp_ajax_ibk_delete_log_via_ajax', array($this, 'ibk_delete_log_via_ajax'));	
			add_action( 'wp_ajax_ibk_return_popup_via_ajax', array($this, 'ibk_return_popup_via_ajax'));
			add_action( 'wp_ajax_ibk_check_log_status_via_ajax', array($this, 'ibk_check_log_status_via_ajax'));		
			add_action( 'wp_ajax_ibk_get_dropbox_auth_url', array($this, 'ibk_get_dropbox_auth_url'));
			add_action( 'wp_ajax_ibk_get_onedrive_auth_url', array($this, 'ibk_get_onedrive_auth_url'));
			add_action( 'wp_ajax_ibk_get_copydotcom_auth_url', array($this, 'ibk_get_copydotcom_auth_url'));
			add_action( 'wp_ajax_ibk_restore_popup_box', array($this, 'ibk_restore_popup_box'));
			add_action( 'wp_ajax_ibk_download_popup_box', array($this, 'ibk_download_popup_box'));
			add_action( 'wp_ajax_ibk_check_restore_status', array($this, 'ibk_check_restore_status'));	
			add_action( 'wp_ajax_ibk_migrate_popup_box', array($this, 'ibk_migrate_popup_box'));	
			add_action( 'wp_ajax_ibk_clear_log_debug_file', array($this, 'ibk_clear_log_debug_file'));	
			add_action( 'wp_ajax_ibk_run_backup_via_ajax', array($this, 'ibk_run_backup_via_ajax'));
			add_action( 'wp_ajax_ibk_check_destination', array($this, 'ibk_check_destination'));
			add_action( 'init', array($this, 'ibk_dropbox_auth'));
			add_action( 'init', array($this, 'ibk_restore_migrate_check'));	
		}

		public function ibk_head(){
			if (isset($_GET['page']) && $_GET['page']=='ibk_admin'){
				wp_enqueue_style( 'ibk-jqueryui-min-css', IBK_URL . 'admin/assets/css/jquery-ui.min.css' );
				wp_enqueue_style( 'ibk-admin-style', IBK_URL . 'admin/assets/css/style.css' );
				wp_enqueue_style( 'ibk-font-awesome', IBK_URL . 'admin/assets/css/font-awesome.css' );
				wp_enqueue_style( 'ibk-bootstrap-style', IBK_URL . 'admin/assets/css/bootstrap.css' );
				wp_enqueue_style( 'ibk-bootstrap-theme-style', IBK_URL . 'admin/assets/css/bootstrap-theme.css' );
				wp_enqueue_style( 'ibk-fileinput-style', IBK_URL . 'admin/assets/css/fileinput.css' );
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_script( 'ibk-admin-js', IBK_URL . 'admin/assets/js/functions.js' );
				wp_enqueue_script( 'ibk-fileinput-js', IBK_URL . 'admin/assets/js/fileinput.js' );
				wp_enqueue_script( 'ibk-bootstrap-js', IBK_URL . 'admin/assets/js/bootstrap.js' );
				wp_enqueue_script( 'ibk-jquery-flot-js', IBK_URL . 'admin/assets/js/jquery.flot.js' );
				wp_enqueue_script( 'ibk-jquery-flot-pie-js', IBK_URL . 'admin/assets/js/jquery.flot.pie.js' );
				wp_localize_script( 'ibk-admin-js', 'ibk_base_url', get_site_url() );
				wp_localize_script( 'ibk-admin-js', 'ibk_admin_url', get_admin_url() . 'admin.php?page=ibk_admin' );				
			}
		}

		public function indeed_admin_menu(){
			add_menu_page ( 'Wp SuperBackup', 'Wp SuperBackup', 'manage_options', 'ibk_admin', array($this, 'ibk_admin') );
		}
		
		public function ibk_admin(){
			//current tab
			if (isset($_GET['tab'])){
				$tab = $_GET['tab'];
			} else {
				$tab = 'dashboard';
			}
			
			//url admin
			$url = get_admin_url() . 'admin.php?page=ibk_admin';

			//all tabs available
			$tabs_arr = array(
								'manage_backups' => 'Snapshots',
								'logs' => 'Snapshot Logs',
								'restore' => 'Restore',								
								'migrate' => 'Migrate',
								'cloud' => 'Cloud',
								'destinations' => 'Destinations',
								'general_settings' => 'General Settings',
								'system' => 'System',
								'help' => 'Help',								
							  );
			
			//some functions for admin dashboard
			//require_once IBK_PATH . 'admin/functions.php';
			//include dashboard header
			require_once IBK_PATH . 'admin/dashboard-head.php';
			
			switch ($tab){
				case 'manage_backups':
					require_once IBK_PATH . 'admin/tabs/manage_backups.php';
				break;
				case 'general_settings':
					require_once IBK_PATH . 'admin/tabs/general_settings.php';					
				break;
				case 'destinations':
					$status = 0;
					require_once IBK_PATH . 'admin/tabs/destinations.php';
				break;	
				case 'logs':
					require_once IBK_PATH . 'admin/tabs/logs.php';
				break;	
				case 'restore':
					//set_time_limit(2000);
					require_once IBK_PATH . 'admin/tabs/restore.php';
				break;
				case 'system':
					require_once IBK_PATH . 'admin/tabs/system.php';
				break;		
				case 'help':
					require_once IBK_PATH . 'admin/tabs/help.php';
				break;
				case 'migrate':
					//set_time_limit(2000);
					require_once IBK_PATH . 'admin/tabs/migrate.php';
				break;
				case 'cloud':
					if (isset($_GET['destinations']) && $_GET['destinations']==true){
						$status = 1;
						require_once IBK_PATH . 'admin/tabs/destinations.php';
					} else {
						require_once IBK_PATH . 'admin/tabs/cloud.php';
					}
				break;	
				case 'dashboard':
					require_once IBK_PATH . 'admin/tabs/dashboard.php';
				break;
			}
			
		}
		
		private function ibk_get_destination_next_id(){
			global $wpdb;
			$num = 1;
			$data = $wpdb->get_row("SHOW TABLE STATUS LIKE '". $wpdb->base_prefix ."indeed_destinations'");
			if (!empty($data->Auto_increment)) $num = $data->Auto_increment;
			return $num;
		}
		
		public function ibk_save_update_backup_item($arr, $run_now=TRUE){
			/*
			 * @param array (postdata), bool (save and run, only for run now)
			 * @return none
			 */
			if (empty($arr['name'])) $arr['name'] = 'My BackUp';
			if (empty($arr['description'])) $arr['description'] = 'set to backup my WordPress website';
			
			global $wpdb;
			if (isset($arr['id'])){
				//it's edit
				$id = $arr['id'];
				$wpdb->query("UPDATE ".$wpdb->base_prefix."indeed_backups SET name=\"".$arr['name']."\" WHERE id=".$id."; ");
				$wpdb->query("DELETE FROM ".$wpdb->base_prefix."indeed_backup_metas WHERE backup_id=".$id."; ");
				unset($arr['id']);
			} else {
				//creating new item
				$timestamp = time();
				$date = date('Y-m-d H:i:s', $timestamp);
				$wpdb->query('INSERT INTO '.$wpdb->base_prefix.'indeed_backups VALUES(null, "'.$arr['name'].'", "'.$date.'");');
				$id = $wpdb->insert_id;
			}
			unset($arr['name']);
			foreach ($arr as $k=>$v){
				$wpdb->query('INSERT INTO '.$wpdb->base_prefix.'indeed_backup_metas VALUES(null, "'.$id.'", "'.$k.'", "'.$v.'");');
			}
		
			if (isset($arr['backup_interval_type'])){
				if ($arr['backup_interval_type']==0 && $run_now) {					
					$time = time();//run now
				} elseif ($arr['backup_interval_type']==-1){
					$time = strtotime($arr['cron-specified_date']);
				} else {
					$time = time() + ($arr['cron-periodically']*60*60);
				}		
				if (!empty($time)){
					indeed_set_cron_job($id, $time);//set the cron job
				}					
			}
			
		}
		
		private function ibk_get_items_list($type, $asc_or_desc = 'DESC', $status=FALSE){
			global $wpdb;
			$arr = FALSE;
			if ($type=='backup') {
				$t1 = $wpdb->base_prefix . 'indeed_backups';
				$t2 = $wpdb->base_prefix . 'indeed_backup_metas';
			} elseif ($type=='destinations'){
				$t1 = $wpdb->base_prefix . 'indeed_destinations';
				$t2 = $wpdb->base_prefix . 'indeed_destination_metas';
			}
			$t1_exists = $wpdb->get_results('SHOW TABLES LIKE "'.$t1.'";');
			$t2_exists = $wpdb->get_results('SHOW TABLES LIKE "'.$t2.'";');
			if ($t1_exists && $t2_exists){
				$q = "SELECT * FROM ".$t1." WHERE 1=1";
				if ($status!==FALSE){
					$q .= " AND status=".$status;
				}
				$q .= " ORDER BY id " . $asc_or_desc;
				$arr = $wpdb->get_results($q);
			}
			return $arr;
		}
		
		public function ibk_change_connected_destination_status($id){
			global $wpdb;
			$wpdb->query('UPDATE '.$wpdb->base_prefix.'indeed_destination_metas SET meta_value=1 WHERE destination_id='.$id.' AND meta_name="connected"; ');
		}
		
		
		/******************** HTML STUFF ********************/
		
		private function ibk_create_admin_backup_box($id, $data, $url){
			//last run
			if (!class_exists('IndeedDoLogs')){
				require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
			}
			$obj = new IndeedDoLogs();
			
			$last_run = $obj->get_last_log_for_backup($id);
			if (!$last_run){
				$last_run = "- - - - / - - / - - &nbsp;&nbsp;&nbsp; - - : - - : - - ";
			} else {
				$last_run = ibk_formated_time_for_dashboard(strtotime($last_run)) . ' ago';
			}
			
			$display_files_icon = ($data['save_files']=='all' || ($data['save_files']=='custom' && $data['save_files_list'] && $data['save_files_list']!=-1) ) ? 'inline-block' : 'none';
			$display_db_icon = (!empty($data['save_db_table_list'])) ? 'inline-block' : 'none';
			?>
				<div class="ibk-admin-dashboard-backup-box-wrap">
				<div class="ibk-admin-dashboard-backup-box" id="ibk-b-item-<?php echo $id;?>" style="background-color: <?php echo '#'.$data['admin_box_color'];?>" >
					<div class="ibk-admin-dashboard-backup-box-main">
						<div class="ibk-admin-dashboard-backup-box-title"><?php echo $data['name'];?></div>
						<div class="ibk-admin-dashboard-backup-box-content"><?php echo $data['description'];?></div>
						<div class="ibk-admin-dashboard-backup-box-links-wrap">
						<div class="ibk-admin-dashboard-backup-box-links">
							<div onClick="ibk_run_backup_now(<?php echo $id;?>);" class="ibk-admin-dashboard-backup-box-link">Run Now</div>
							<a href="<?php echo  $url . '&tab=manage_backups&subtab=edit&id=' . $id;?>" class="ibk-admin-dashboard-backup-box-link">Edit</a>
							<div onClick="ibk_delete_item(<?php echo $id;?>, 'backup', '<?php echo $data['name'];?>');"  class="ibk-admin-dashboard-backup-box-link">Delete</div>					
						</div>
					</div>
					</div>
					<div class="ibk-admin-dashboard-backup-box-bottom">
						<div class="ibk-admin-dashboard-backup-box-files">
							<i title="BackUp Files" class="fa-ibk fa-files-ibk" style="display: <?php echo $display_files_icon;?>"></i>
							<i title="BackUp Database" class="fa-ibk fa-db-ibk" style="display: <?php echo $display_db_icon;?>"></i>
						
							<div class="ibk-admin-dashboard-backup-box-dest">Goes to <span> 
								<?php echo ibk_get_destination_name($data['destination']);?>
							</span>
							</div>
						</div>
						<div class="ibk-admin-dashboard-backup-box-scheduled">
						<?php if($data['backup_interval_type'] == -1) {?>
							<i title="Scheduled" class="fa-ibk fa-scheduled-ibk"></i>
						<?php }elseif($data['backup_interval_type'] == 1){?>
							<i title="Periodically" class="fa-ibk fa-periodically-ibk"></i>
						<?php } ?>
						</div>
						<div class="ibk-admin-dashboard-backup-box-date">
							<div class="date-message">Last Run</div>
							<?php echo $last_run;?>
						</div>
						<div class="clear"></div>
					</div>
				
				</div>
				</div>	
			<?php 	
		}
		
		private function ibk_restore_snapshot_box($id, $data){
			/*
			 * display a box foreach snapshot that can be restored
			 * @param int (id of snapshot), array
			 * @return print string
			 */	
			//last run
			if (!class_exists('')){
				require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
			}
			$obj = new IndeedDoLogs();
			$last_run = $obj->get_last_log_for_backup($id);
			if (!$last_run){
				$last_run = "- - - - / - - / - - &nbsp;&nbsp;&nbsp; - - : - - : - - ";
			} else {
				$last_run = ibk_formated_time_for_dashboard(strtotime($last_run)) . ' ago';
			}
				
			$display_files_icon = ($data['save_files']=='all' || ($data['save_files']=='custom' && $data['save_files_list'] && $data['save_files_list']!=-1) ) ? 'inline-block' : 'none';
			$display_db_icon = (!empty($data['save_db_table_list'])) ? 'inline-block' : 'none';
			?>
							<div class="ibk-admin-dashboard-backup-box-wrap">
					<div class="ibk-admin-dashboard-backup-box" id="ibk-b-item-<?php echo $id;?>" style="background-color: <?php echo '#'.$data['admin_box_color'];?>">
						<div class="ibk-admin-dashboard-backup-box-main">
							<div class="ibk-admin-dashboard-backup-box-title"><?php echo $data['name'];?></div>
							<div class="ibk-admin-dashboard-backup-box-content"><?php echo $data['description'];?></div>
							<div class="ibk-admin-dashboard-backup-box-links-wrap">
							<div class="ibk-admin-dashboard-backup-box-links">
								<?php 
									$single_download_link = ibk_get_single_download_link($id, $data['destination']);
									if ($single_download_link){
										echo '<a href="' . $single_download_link . '" class="ibk-admin-dashboard-backup-box-link" target="_blank">Download</a>';
									} else {
										?>
										<div class="ibk-admin-dashboard-backup-box-link" onClick="ibk_download_popup(<?php echo $id . ', ' . $data['destination'];?>);">Download</div>
										<?php 	
									}
								?>								
								<div class="ibk-admin-dashboard-backup-box-link" onClick="ibk_restore_popup(<?php echo $id . ', ' . $data['destination'];?>);">Restore</div>
							</div>
						</div>
						</div>
						<div class="ibk-admin-dashboard-backup-box-bottom">
							<div class="ibk-admin-dashboard-backup-box-files">
								<i title="BackUp Files" class="fa-ibk fa-files-ibk" style="display: <?php echo $display_files_icon;?>"></i>
								<i title="BackUp Database" class="fa-ibk fa-db-ibk" style="display: <?php echo $display_db_icon;?>"></i>
							
								<div class="ibk-admin-dashboard-backup-box-dest">Comes from <span> 
									<?php echo ibk_get_destination_name($data['destination']);?></span>
								</div>
							</div>
							<div class="ibk-admin-dashboard-backup-box-scheduled">
								<?php if($data['backup_interval_type'] == -1) {?>
									<i title="Scheduled" class="fa-ibk fa-scheduled-ibk"></i>
								<?php }elseif($data['backup_interval_type'] == 1){?>
									<i title="Periodically" class="fa-ibk fa-periodically-ibk"></i>
								<?php } ?>
							</div>
							<div class="ibk-admin-dashboard-backup-box-date">
								<div class="date-message">Last Run</div>
								<?php echo $last_run;?>						
							</div>
							<div class="clear"></div>
						</div>
					
					</div>
				</div>	
			<?php 
		}
		
		private function ibk_create_admin_destination_box($id, $data, $url, $status ){
			?>
			<div class="ibk-admin-dashboard-backup-box-wrap ibk-destination-list">	
				<div class="ibk-admin-dashboard-backup-box" id="ibk-b-item-<?php echo $id;?>" style="background-color: <?php echo '#'.$data['admin_box_color'];?>" >
					<div class="ibk-admin-dashboard-backup-box-main">
					<div class="ibk-admin-dashboard-backup-box-title ibk-destination-list-name"><?php echo $data['name'];?></div>
					<div class="ibk-admin-dashboard-backup-box-title ibk-destination-list-type"><?php echo $data['type'];?></div>
					<div class="ibk-admin-dashboard-backup-box-links-wrap">
						<div class="ibk-admin-dashboard-backup-box-links" style="bottom: -25%;">
							<a href="<?php echo $url . '&tab=destinations&subtab=edit_create&id=' . $id;?>" class="ibk-admin-dashboard-backup-box-link">Edit</a>
							<div onClick="ibk_delete_item(<?php echo $id;?>, 'destination', '<?php echo $data['name'];?>', <?php echo $status;?>);" class="ibk-admin-dashboard-backup-box-link">Delete</div>
							<?php 
								if ($data['type']!='rackspace' && $data['type']!='copy'){
									?>
									<div class="ibk-admin-dashboard-backup-box-link" style="margin: 0px 4px;" onClick="ibk_check_destination(<?php echo $id;?>);">Check Connection</div>
									<?php 									
								}
							?>

						</div>
					</div>	
					</div>
					<div class="ibk-admin-dashboard-backup-box-bottom">Created on: <?php echo $data['create_date'];?></div>
					
				</div>	
			</div>	 
			<?php 	
		}
		
		private function ibk_get_colors_for_admin_boxes($value=''){
			$color_scheme = array('0a9fd8', '38cbcb', '27bebe', '0bb586', '94c523', '6a3da3', 'f1505b', 'ee3733', 'f36510', 'f8ba01');
			if (!$value) $value = $color_scheme[rand(0,9)];
			?>
				<ul id="colors_ul" class="ibk-colors-ul">
					<?php
					$i = 0;
					foreach	($color_scheme as $color){
						if( $i==5 ){
							echo "<div class='clear'></div>";
						}
						$class = 'ibk-color-scheme-item';
						if ($value==$color) $class = 'ibk-color-scheme-item-selected';
						?>
					    	<li class="<?php echo $class;?>" onClick="ibk_change_color_scheme(this, '<?php echo $color;?>', '#ibk_admin_box_color');" style="background-color: #<?php echo $color;?>;"></li>
					    <?php
					    	$i++;
					}
					?>
				</ul>
				<input type="hidden" value="<?php echo $value;?>" name="admin_box_color" id="ibk_admin_box_color" />
			<?php 
		}
				
		
		/************************************* AJAX STUFF ***************************************/

		public function ibk_google_authorize_ajax(){
			if (!empty($_REQUEST['destination_id'])){
				require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
				$obj = new IndeedGoogle($_REQUEST['destination_id']);
				echo $obj->generate_link();
			}			
			die();
		}
		
		public function ibk_get_onedrive_auth_url(){
			if (!empty($_REQUEST['destination_id'])){
				require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
				$oneDrive = new IndeedOneDrive($_REQUEST['destination_id'], $_REQUEST['onedrive_client_id'], $_REQUEST['onedrive_client_secret']);
				echo $oneDrive->generate_auth_link();
			}
			die();
		}
		
		public function ibk_get_copydotcom_auth_url(){
			if (!empty($_REQUEST['destination_id'])){
				require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
				$object = new IndeedCopyDotCom($_REQUEST['destination_id']);
				echo $object->generate_auth_link();
			}
			die();
		}
		
		public function ibk_test_ftp_connection(){
			if (!empty($_REQUEST['destination_id'])){
				require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
				$obj = new IndeedFtp($_REQUEST['destination_id']);
				if ($obj->login()){
					//connection is ok
					$this->ibk_change_connected_destination_status($_GET['id']);
					echo 1;
				}
			}
			die();
		}
		
		public function ibk_get_table_list_via_ajax(){
			/*
			 * list backup, destination items
			 */
			if (!empty($_REQUEST['type'])){
				require_once IBK_PATH . 'utilities.php';
				$arr = ibk_get_table_list($_REQUEST['type']);
				$native = array();
				if (!empty($_REQUEST['site'])){
					$arr = ibk_only_tables_for_blog_id($arr, $_REQUEST['site']);
					foreach ($arr as $k=>$v){
						$native[$k] = ibk_is_native($k, $_REQUEST['site'] );
					}
				} else {
					foreach ($arr as $k=>$v){
						$native[$k] = ibk_is_native($k);
					}
				}

				echo json_encode(array("values" => $arr, "native" => $native));
			}
			die();
		}
		
		public function ibk_delete_item_via_ajax(){
			/*
			 * Delete backup or destination items
			 */
			if (!empty($_REQUEST['id']) && !empty($_REQUEST['type'])){
				global $wpdb;
				if ($_REQUEST['type']=='backup'){
					$wpdb->query("DELETE FROM ".$wpdb->base_prefix ."indeed_backups WHERE id=".$_REQUEST['id']."; ");
					$wpdb->query("DELETE FROM ".$wpdb->base_prefix ."indeed_backup_metas WHERE backup_id=".$_REQUEST['id']."; ");	
					$wpdb->query("DELETE FROM ".$wpdb->base_prefix ."indeed_logs WHERE action_id=".$_REQUEST['id']."; ");
					//delete cron jobs
					wp_clear_scheduled_hook( 'indeed_main_job', array("'" . $_REQUEST['id'] . "'") );
				} elseif ($_REQUEST['type']=='destination'){
					$wpdb->query("DELETE FROM ".$wpdb->base_prefix ."indeed_destinations WHERE id=".$_REQUEST['id']."; ");
					$wpdb->query("DELETE FROM ".$wpdb->base_prefix ."indeed_destination_metas WHERE destination_id=".$_REQUEST['id']."; ");					
				}
			}
			die();
		}
		
		public function ibk_delete_log_via_ajax(){
			if (!empty($_REQUEST['process_id'])){
				if (!class_exists('IndeedDoLogs')){
					require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
				}				
				$obj = new IndeedDoLogs();
				$obj->delete_logs_by_process($_REQUEST['process_id']);	
			}
		}
		
		public function ibk_save_destination_metas_via_ajax(){
			/*
			 * save destination item
			 */
			if (!empty($_REQUEST)){
				$this->ibk_save_update_destination_item($_REQUEST);// save / edit		
				echo 1;		
			}
			die();
		}
		
		public function ibk_return_popup_via_ajax(){
			if (!empty($_REQUEST['id']) && !empty($_REQUEST['id']) && !empty($_REQUEST['type'])){
				if ($_REQUEST['type']=='logs'){
					//make logs popup
					if (!class_exists('IndeedDoLogs')){
						require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
					}
					$logs_obj = new IndeedDoLogs();
					$data = $logs_obj->get_logs_for_process_for_popup($_REQUEST['id']);
					$str = '';
					$str .= '<div class="ibk-popup-wrapp" id="ibk_popup_box">
								<div class="ibk-the-popup">
									<div class="ibk-popup-top">
										<div class="title">Logs</div>
										<div class="close-bttn" onclick="ibk_close_popup();"></div>
										<div class="clear"></div>
									</div>
									<div class="ibk-popup-content" >
										<div>';
					
					if (!empty($data)){
						foreach ($data as $log){
							if (isset($log['create_date']) && isset($log['message'])){
								$str .= '<div class="ibk-view-logs-wrap"><div class="ibk-view-logs-date">' . $log['create_date'] . '</div><div class="ibk-view-logs-message">' . $log['message'] . '</div></div>';
							}
						}
					}
					
					$str .= '
										</div>
									</div>
								</div>
							</div>';
					echo $str;
				}
			}
			die();
		}
		
		public function ibk_check_log_status_via_ajax(){
			if (!empty($_REQUEST['id'])){
				if (!class_exists('IndeedDoLogs')){
					require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
				}
				$msg = '';
				$complete = '';
				$logs_obj = new IndeedDoLogs();
				$data = $logs_obj->get_logs_for_process($_REQUEST['id']);
				$status = 0;
				if ($data[0]->action_id){
					$backup_meta = ibk_return_metas_from_custom_db('backups', $data[0]->action_id);
					end($data);
					$last_key = key($data);
					$msg = $data[$last_key]->message;
					$complete = ibk_get_complete_percetage_for_log($data);
					$status = $data[$last_key]->status;
				}
				echo json_encode(array('percent'=>$complete, 'msg'=>$msg, 'status'=>$status));
				die();
			}
		}
		
		public function ibk_get_dropbox_auth_url(){
			/*
			 * @return dropbox url for redirecting
			 */	
			if (!empty($_REQUEST['destination_id'])){
				if (!class_exists('IndeedDropbox')){
					require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
				}	
				$dropbox_obj = new IndeedDropbox($_REQUEST['destination_id']);
				echo $dropbox_obj->get_authentification_link();
			}
			die();
		}
		
		public function ibk_restore_popup_box(){
			/*
			 * @param id of snapshot
			 * @return string with popup
			 */	
			if (isset($_REQUEST['snapshot_id']) && isset($_REQUEST['destination_id'])){					
					$str = '';
					$str .= '<div class="ibk-popup-wrapp" id="ibk_popup_box">
								<div class="ibk-the-popup">
									<div class="ibk-popup-top">
										<div class="title">Restore Snapshot</div>
										<div class="close-bttn" onclick="ibk_close_popup();"></div>
										<div class="clear"></div>
									</div>
									<div class="ibk-popup-content" >
										<div>';
					$destination_data = ibk_return_metas_from_custom_db('destinations', $_REQUEST['destination_id']);
					
					$data = $this->ibk_get_list_all_snapshot_instances($_REQUEST['snapshot_id'], $_REQUEST['destination_id']);
					if ($data){					
						$str .= '<form method="post" action="" id="ibk_restore_popup_form">';		
						
						$str .= '<input type="hidden" value="'.$_REQUEST['destination_id'].'" name="destination_id" />';
						$str .= '<input type="hidden" value="'.$_REQUEST['snapshot_id'].'" name="snapshot_id" />';
						if ($destination_data['type']=='google'){
							$selected_value = (!empty($data[key($data)]['fileId'])) ? $data[key($data)]['fileId'] : '';
						} else {
							$selected_value = (!empty($data[key($data)])) ? $data[key($data)] : '';
						}						
						$str .= '<input type="hidden" value="' . $selected_value . '" name="source_file" id="ibk_source_file"/>';
						$str .= '<input type="hidden" value="1" name="ibk_restore_migrate_action" />';
						
						//instances
						if (count($data)>1){
							$str .= '<div class="ibb-popup-list-snapshots-instances" style="overflow: hidden;">';
							if ($destination_data['type']=='google'){
								foreach ($data as $k=>$v){
									$class = ($selected_value==$v['fileId']) ? 'ibk-restore-snapshot-item-popup-selected' : 'ibk-restore-snapshot-item-popup';
									$str .= '<div class="' . $class . '" onClick="ibk_select_snapshot_instance(this, \''.$v['fileId'].'\');"><i class="fa-ibk fa-version-ibk"></i>SNAPSHOT<span class="ibk-from">From</span><span class="ibk-the-filename">' . date("Y-m-d H:i:s", $k) .'</span></div>';
								}
							} else {
								foreach ($data as $k=>$v){
									$class = ($selected_value==$v) ? 'ibk-restore-snapshot-item-popup-selected' : 'ibk-restore-snapshot-item-popup';
									$str .= '<div class="' . $class . '" onClick="ibk_select_snapshot_instance(this, \''.$v.'\');"><i class="fa-ibk fa-version-ibk"></i>SNAPSHOT<span class="ibk-from">From</span><span class="ibk-the-filename">' . date("Y-m-d H:i:s", $k) .'</span></div>';
								}
							}
							$str .=	'</div>';							
						}						
						//instances
						
						$logs_data = $this->get_log_content($_REQUEST['snapshot_id'], $_REQUEST['destination_id']);
						$single_site = (empty($logs_data['blog_id'])) ? 0 : 1;
						$str .= '<input type="hidden" value="' . $single_site . '" name="multisite-single_site" />';
						//MULTISITE
						if (is_multisite() && $single_site){
							$str .= '<input type="hidden" value="' . @$logs_data['native_wp_tables'] . '" name="native_wp_tables" />';//
							$str .= '<input type="hidden" value="' . @$logs_data['sites_folders'] . '" name="sites_folders" />';
							$str .= '<div class="ibk-inside-item  ibk-multisite-wrapper">';
							$str .= '<h3>MultiSite WP detected</h3>';
							$str .= '<h4>...and your Snapshot is a SingleSite.</h4><br/>';
							$str .= '<p>Select you Site destination:</p>';
							$str .= '<div class="row">';
							$str .= '<div class="col-xs-4">';
							$str .= '<div class="form-group">';
							$str .= '<select name="target_site"  class="form-control m-bot15">';
							$sites = ibk_blog_ids_list(TRUE);
							$blog_id = get_current_blog_id();
							foreach ($sites as $k=>$v){
								$selected = ($k==$blog_id) ? 'selected' : '';
								$str .= '<option value="' . $k . '" ' . $selected . '>' . $v .'</option>';
							}
							$str .= '</select>';
							$str .= '</div>
							</div>
							</div>
							</div>';
						}						
						//MULTISITE
						
						$str .= '<div class="clear"></div>';
						$meta_arr = ibk_return_metas_from_custom_db('backups', $_REQUEST['snapshot_id']);
						if (!empty($meta_arr['save_files_list']) || $meta_arr['save_files']=='all'){
							
							$str .= '<div><h3 style="margin-top:35px;">Files to Restore</h3>Select whicth files should be Restored
										<div style="margin-top:15px;">';				
										if ($meta_arr['save_files']=='all'){
											$meta_arr['save_files_list'] = 'themes,plugins,uploads,wp-config.php';
										}
										
										$arr_v = explode(',', $meta_arr['save_files_list']);
			
										$arr = array(
												'themes' => 'Themes',
												'plugins' => 'Plugins',
												'uploads' => 'Media Files',
												'wp-config.php' => 'wp-config.php',
										);
										foreach ($arr_v as $k){
											$checked = (strpos($meta_arr['save_files_list'], $k)!==FALSE ) ? 'checked' : '';
											$str .= '<label class="checkbox-inline ibk-checkbox-wrap"><input type="checkbox" onClick="ibk_make_inputh_string(this, \''.$k.'\', \'#save_files_list\');" '.$checked.'/>'.$arr[$k].'</label>';
										}
										$str .= '<input type="hidden" value="'.$meta_arr['save_files_list'].'" name="files_to_restore" id="save_files_list" />';
							  $str .= '</div>';
							$str .= '</div>';
						}
						if (!empty($meta_arr['save_db_table_list'])){
							$str .= '<div>
										<h3>DataBase to Restore</h3>
										<p>Pick Up all the Tables or just some of them and exclude those that are not necessary to be Restored</p>
										<div id="ibk-database-list-tables">';
							$table_names = ibk_get_table_list();
							$items = explode(',', $meta_arr['save_db_table_list']);
							foreach ($items as $item){
								if (!isset($table_names[$item])){
									$table_names[$item] = $wpdb->prefix . $item;
								}
								$str .= '<div id="backup-t-items-'.$item.'" class="ibk-tag-item">';
								$str .= $table_names[$item];
								$str .= '<div class="ibk-remove-tag" onClick="ibk_remove_db_tag(\''.$item.'\', \'#backup-t-items-\', \'#save_db_table_list\');" title="Removing tag">x</div>';
								$str .= '</div>';
							}
							$str .= '<input type="hidden" id="save_db_table_list" name="tables_to_restore" value="'.$meta_arr['save_db_table_list'].'" />';
							$str .= '</div>
							</div>';
						}
												
						$str .= '</form>';
						$str .= '<div class="ibk-popup-footer">';
						$str .= '<div class="ibk-restore-buttons-wrap">
									<span class="ibk-add-new" id="submit_the_popupform" onclick="jQuery( \'#ibk_restore_popup_form\' ).submit();">
									<i title="" class="fa-ibk fa-restore-btn-ibk"></i>
									<span>Restore</span>
									</span>
									<span class="ibk-close-btn" onclick="ibk_close_popup();">
									<i title="" class="fa-ibk fa-close-ibk"></i>
									<span>Close</span>
									</span>
								</div>';
						$str .= '</div>';		
					} else {
						$str .= 'No instance available!';	
					}

					$str .= '
									</div>
								</div>
							</div>';
					echo $str;
			}	
			die();
		}
		
		public function ibk_check_destination(){
			/*
			 * @param none
			 * @return int
			 */
			if (isset($_REQUEST['id'])){
				require_once IBK_PATH . 'classes/IndeedDoCheckDestination.class.php';
				$object = new IndeedDoCheckDestination($_REQUEST['id']);
				if ($object->check()){
					echo 1;
					die();	
				}
			}
			echo 0;
			die();
		}
		
		public function ibk_download_popup_box(){
			require IBK_PATH . 'admin/popups/download_snapshot.php';
			die();	
		}
		
		public function ibk_check_restore_status(){
			/*
			 * return 0 if restore process is over or out of time
			 * return current log if it's runnin
			 * @param none
			 * @return string or int
			 */
			$data = $this->ibk_get_restore_log();
			if ($data){
				//check if timeout
				$key = key($data);
				if ((int)$key+10*60>time()){
					echo $data[key($data)];
					die();
				} else {
					$log_file = WP_CONTENT_DIR . '/uploads/indeed-backups/' . md5("indeed-super-backup") . '_restore.log';
					if (file_exists($log_file)){						
						unlink($log_file);
					}
				}
			}
			echo 0;
			die();
		}
		
		public function ibk_clear_log_debug_file(){
			$file = WP_CONTENT_DIR . '/uploads/indeed-backups/ibk_global_log.log';
			$f = @fopen($file, "r+");
			if ($f !== false) {
				ftruncate($f, 0);
				fclose($f);
				echo 1;
			}	
			die();
		}
		
		public function ibk_run_backup_via_ajax(){
			if (isset($_REQUEST['id'])){
				wp_schedule_single_event( time() , 'indeed_main_job', array( $_REQUEST['id'] ) );
			}
		}
			
		////////////end of ajax
		
		
		private function ibk_get_restore_log(){
			/*
			 * get the last log from restore/migrate process
			 */
			$file_path = WP_CONTENT_DIR . '/uploads/indeed-backups/' . md5("indeed-super-backup") . '_restore.log';
			if (file_exists($file_path)){
				$file = new SplFileObject($file_path);
				$str = '';
				while (!$file->eof()) {
					$str .= $file->current();
					$file->next();
				}
				if ($str){
					return unserialize($str);
				}				
			}
			return FALSE;
		}
		
		private function ibk_get_list_all_snapshot_instances($snapshot_id, $destination_id){			
			/*
			 * @param int (id of snapshot), int (id of destination)
			 * @return array
			 */	
			$return_arr = FALSE;
			$data = ibk_return_metas_from_custom_db('destinations', $destination_id);

			switch ($data['type']){
				case 'local':									
					$source_dir = $data['local_folder_target'];
					
					//$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source_dir), RecursiveIteratorIterator::SELF_FIRST);/// old version		
					$files = scandir($source_dir);
					
					if (isset($files) && is_array($files)){
						foreach ($files as $file){
							$file = str_replace('\\', '/', $file);
							$file_h = basename($file);
							if (preg_match("#^superbackup(.*)$#i", $file_h)){
								//it contains indeed
								$is_zip_data = explode('.', $file_h);
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]	= $file;
									}
								}
							}
						}
					}
				break;
				
				case 'ftp':
					if (!class_exists('IndeedFtp')){
						require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
					}					
					$ftp = new IndeedFtp($destination_id);//destination id
					$ftp->login();
					$return_arr = $ftp->list_snapshots($snapshot_id);//snapshot id
				break;
					
				case 'google':
					if (!class_exists('IndeedGoogle')){
						require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';						
					}
					$goo = new IndeedGoogle($destination_id);
					$goo->login();
					$data = $goo->retrieveAllFiles();
					
					if(isset($data) && is_array($data)){
						foreach ($data as $file_obj){
							if (preg_match("#^superbackup(.*)$#i", $file_obj->title)){
								//it contains indeed
								$is_zip_data = explode('.', $file_obj->title);
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]['fileId'] = $file_obj->id;
										$return_arr[$file_name_data[3]]['title'] = $file_obj->title;
									}
								}
							}
						}
					}
				break;
				
				case 'dropbox':
					if (!class_exists('IndeedDropbox')){
						require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
					}					
					$obj = new IndeedDropbox($destination_id);
					$obj->login();
					$data = $obj->get_files();
					
					if(isset($data) && is_array($data)){
						foreach ($data as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								//it contains indeed
								$is_zip_data = explode('.', basename($file));
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]	= $file;
									}
								}
							}
						}					
					}
				break;
				
				case 'amazon':
					if (!class_exists('IndeedAmazonS3')){
						require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
					}					
					$obj = new IndeedAmazonS3($destination_id);
					$data = $obj->get_files_list(); 
					if(isset($data) && is_array($data)){
						foreach ($data as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								//it contains indeed
								$is_zip_data = explode('.', basename($file));
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]	= $file;
									}
								}
							}
						}
					}
				break;
				
				case 'onedrive':
					require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
					$obj = new IndeedOneDrive($destination_id);
					$files = $obj->return_all_files();
					$min_timestamp = time();
					
					if(isset($files) && is_array($files)){
						foreach ($files as $file_arr){
							$file = $file_arr['name'];
							if (preg_match("#superbackup(.*)$#i", $file)){
								//it contains indeed
								$title = basename($file);
								$is_zip_data = explode('.', $title);
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]	= $file;
									}
								}
							}
						}
					}
					break;
					
				case 'copy':
					require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
					$obj = new IndeedCopyDotCom($destination_id);
					$obj->login();
					$files = $obj->get_all_files();
					$min_timestamp = time();
					
					if(isset($files) && is_array($files)){
						foreach ($files as $file){
							if (preg_match("#superbackup(.*)$#i", $file)){
								//it contains indeed
								$title = basename($file);
								$is_zip_data = explode('.', $title);
								if (isset($is_zip_data[1]) && $is_zip_data[1]=='zip'){
									//it's a zip file
									$file_name_data = explode('_', $is_zip_data[0]);
									if ($file_name_data[2]==$snapshot_id && $file_name_data[1]==md5('superbackup_indeed') ){
										//it's a instance of our snapshot
										$return_arr[$file_name_data[3]]	= $file;
									}
								}
							}
						}	
					}
					break;
			}
			return $return_arr;	
		}
				
		
		private function ibk_save_update_destination_item($arr){
			global $wpdb;
			if (empty($arr['name'])) $arr['name'] = 'MyDest ('.$arr['type'].')';
			if ($arr['is_edit']){
				//it's edit
				$wpdb->query("UPDATE ".$wpdb->base_prefix."indeed_destinations SET name=\"".$arr['name']."\", type=\"".$arr['type']."\" WHERE id=".$arr['id']."; ");
				//$wpdb->query("DELETE FROM ".$wpdb->prefix."indeed_destination_metas WHERE destination_id=".$arr['id']."; ");
			} else {
				//creating new item
				$timestamp = time();
				$date = date('Y-m-d H:i:s', $timestamp);
				$wpdb->query('INSERT INTO '.$wpdb->base_prefix.'indeed_destinations VALUES("'.$arr['id'].'", "'.$arr['name'].'", "'.$arr['type'].'", "'.$date.'", "'.$arr['status'].'");');
				$id = $wpdb->insert_id;
			}
			$id = $arr['id'];
			$type = $arr['type'];
			unset($arr['id']);
			unset($arr['name']);
			unset($arr['type']);
			unset($arr['status']);
				
			switch ($type){
				case 'google':
					$metas = array(
									'client_id' ,
									'client_secret',
									'redirect_uri',
									'access_token',
									'refresh_token',
									'folder_id',
					);
				break;
				case 'local':
					$metas = array(
									'local_folder_target'
					);
				break;
				case 'ftp':
					$metas = array(
									'server_address',
									'username',
									'password',
									'directory',
									'protocol',
									'server_port',
									'server_timeout',
									'passive_mode',
					);
				break;
				case 'rackspace':
					$metas = array(
									'username', 
									'api_key', 
									'container', 
									'container_url', 
									'region',
									);
				break;
				case 'amazon':
					$metas = array(
									'aws_key',
									'aws_secret_key',
									'aws_region',
									'aws_ssl',
									'aws_bucket',
									'subfolder',
								);
				break;
				case 'dropbox':
					$metas = array('path');
					break;
				case 'onedrive':
					$metas = array(
									'client_id',
									'client_secret',
									'redirect_uri',
									'state',
								);
					break;
				case 'copy':
					$metas = array('path');
					break;
			}
				
			$metas[] = 'admin_box_color';
			$metas[] = 'connected';
				
			$table = $wpdb->base_prefix . 'indeed_destination_metas';
			foreach ($metas as $k){
				$data = $wpdb->get_row("SELECT meta_value FROM $table WHERE destination_id='$id' AND meta_name='$k';");
				if (!empty($data) && isset($data->meta_value)){
					//update
					$wpdb->query("UPDATE " . $table . " SET meta_value='" . $arr[$k] . "' WHERE destination_id='" . $id . "' AND meta_name='$k';");
				} else {
					//insert
					$wpdb->query('INSERT INTO ' . $table . ' VALUES(null, "'.$id.'", "'.$k.'", "'.$arr[$k].'");');
				}
			}
			return $id;
		}

		public function ibk_dropbox_auth(){
			/*
			 * After authentification on dropbox it will return to dashboard.
			 * From here we have to redirect to destination page
			 * @param none
			 * @return none
			 */
			if (!empty($_GET['page']) && $_GET['page']=='ibk_admin' && !empty($_GET['oauth_token']) && empty($_GET['tab'])){
				if (!class_exists('IndeedDropbox')){
					require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
					$dropbox_obj = new IndeedDropbox();//instatinate with no destination id, because at this point we don't have it
					$dropbox_obj->dropbox_auth(get_admin_url(). 'admin.php?page=ibk_admin&tab=destinations');//return @ destination tab after doing the job
				}
			}				
		}//end of ibk_dropbox_auth()		
		
		
		
		public function ibk_restore_migrate_check(){
			//check if we must do restore
			if (isset($_FILES['upload_file'])){
				//UPLOAD URL
				require_once IBK_PATH . 'classes/IndeedCopyFile.class.php';
				$obj = new IndeedCopyFile();
				$_POST['uploaded_zip_file'] = $obj->get_file_from_upload();
			}

			if (isset($_POST['ibk_restore_migrate_action']) && $_POST['ibk_restore_migrate_action']==1){
				//create the log file
				if (!file_exists(WP_CONTENT_DIR . '/uploads/indeed-backups/')){
					@mkdir(WP_CONTENT_DIR . '/uploads/indeed-backups/', 0777, TRUE);
				}
				
				$file_path = WP_CONTENT_DIR . '/uploads/indeed-backups/' . md5("indeed-super-backup") . '_restore.log';
				$file = fopen($file_path, 'w');
				$str = serialize(array(time()=>"Process start!"));
				fwrite($file, $str);
				//we set the intermediate cron
				wp_schedule_single_event( time()-1 , 'indeed_set_restore_job_intermediate', array( serialize($_POST) ) );
				if (isset($_POST['destination_id'])){
					$url = get_admin_url() . 'admin.php?page=ibk_admin&tab=restore';
				} else if (isset($_POST['cloud_connection_id'])){
					$url = get_admin_url() . 'admin.php?page=ibk_admin&tab=cloud';
				}else {
					$url = get_admin_url() . 'admin.php?page=ibk_admin&tab=migrate';
				}
				
				wp_safe_redirect($url);
				exit();				
			}
		}//end of ibk_restore_check
		
		
		///clouds methods
		private function get_clound_snapshots($cloud_destination_id){
			//getting type of connection
			$cloud_return = FALSE;
			$type = ibk_get_destination_type($cloud_destination_id);
			$gen_metas = ibk_get_general_metas();
			$temp_dir = get_option('ibk_backup_dir');
			if (!$temp_dir){
				$temp_dir = WP_CONTENT_DIR . '/uploads/';
			}
			switch ($type){
				case 'local':
					if (!class_exists('IndeedLocal')){
						require_once IBK_PATH . 'classes/API/IndeedLocal.class.php';
					}
					$obj = new IndeedLocal($cloud_destination_id);
					$log_files = $obj->get_log_files();
					if ($log_files){
						foreach ($log_files as $path){
							$cloud_return[$path] = file_get_contents($path);
						}
					}
				break;
				case 'ftp':
					if (!class_exists('IndeedFtp')){
						require_once IBK_PATH . 'classes/API/IndeedFtp.class.php';
					}					
					$obj = new IndeedFtp($cloud_destination_id);
					$obj->login();
					$log_files = $obj->get_log_files();
					if ($log_files){						
						foreach ($log_files as $file_name=>$full_path){
							$obj->copy_file_to_local($full_path, $temp_dir . $file_name);
							$cloud_return[$full_path] = file_get_contents($temp_dir . $file_name);
							unlink($temp_dir . $file_name);
						}						
					}
				break;
				case 'google':
					if (!class_exists('IndeedGoogle')){
						require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
					}					
					$obj = new IndeedGoogle($cloud_destination_id);
					$obj->login();
					$data = $obj->get_log_files();	
					if ($data){
						foreach ($data as $title=>$id){
							$file_name= $obj->downloadFile($id, $temp_dir);
							if ($file_name){
								$cloud_return[$title] = file_get_contents($file_name);
								unlink($file_name);								
							}
						}
					}
				break;
				case 'dropbox':
					if (!class_exists('IndeedDropbox')){
						require_once IBK_PATH . 'classes/API/IndeedDropbox.class.php';
					}
					$obj = new IndeedDropbox($cloud_destination_id);
					$obj->login();
					$data = $obj->get_logs_files();
					if ($data){
						foreach ($data as $file){
							$file_name = $obj->get_file($file, $temp_dir);
							if ($file_name){
								$cloud_return[basename($file_name)] = file_get_contents($file_name);
								unlink($file_name);
							}							
						}	
					}										
				break;
				case 'amazon':
					if (!class_exists('IndeedAmazonS3')){
						require_once IBK_PATH . 'classes/API/IndeedAmazonS3.class.php';
					}					
					$obj = new IndeedAmazonS3($cloud_destination_id);
					$data = $obj->get_logs_files();
					if ($data){
						foreach ($data as $file){
							$file_name = $obj->get_file($file, $temp_dir);
							if ($file_name){
								$cloud_return[basename($file_name)] = file_get_contents($file_name);
								unlink($file_name);								
							}
						}						
					}			
				break;
				case 'onedrive':
					if (!class_exists('IndeedOneDrive')){
						require_once IBK_PATH . 'classes/API/IndeedOneDrive.class.php';
					}
					$obj = new IndeedOneDrive($cloud_destination_id);
					$data = $obj->get_logs_files();
					if ($data){
						foreach ($data as $file){
							$file_name = $obj->get_file_by_name($file, $temp_dir . basename($file) );
							if ($file_name){
								$cloud_return[basename($file_name)] = file_get_contents($file_name);
								unlink($file_name);								
							}
						}
					}					
					break;
				case 'copy':
					if (!class_exists('IndeedCopyDotCom')){
						require_once IBK_PATH . 'classes/API/IndeedCopyDotCom.class.php';
					}
					$obj = new IndeedCopyDotCom($cloud_destination_id);	
					$obj->login();
					$data = $obj->get_logs_files();
					if ($data){
						foreach ($data as $file){
							$file_name = $obj->download_file($file, $temp_dir . basename($file) );
							if ($file_name){
								$cloud_return[basename($file_name)] = file_get_contents($file_name);
								unlink($file_name);								
							}
						}
					}					
					break;
			}
			return $cloud_return;
		}
		
		private function get_log_content($snapshot_id, $destination_id){
			/*
			 * @param snapshot id (int), destination id (int)
			 * @return array
			 */	
			$arr = array();
			$data = $this->get_clound_snapshots($destination_id);
			if ($data){
				foreach ($data as $k=>$v){
					$filename = basename($k);
					if (strpos($filename, "superbackup_" . $snapshot_id . ".log")!==FALSE){
						$arr = unserialize($v);
						continue;
					}
				}
			}
			return $arr;
		}
		
		private function create_cloud_restore_box($cloud_data, $cloud_connection_id){
			/*
			 * create the boxes that are present in cloud section
			* @param restore arr is the results from get_cloud_snapshots
			*/
			if (empty($cloud_data)){
				return FALSE;	
			}
			foreach ($cloud_data as $k=>$v){
				$arr = unserialize($v);
				$k = basename($k);
				$display_files_icon = (!empty($arr['files'])) ? 'inline-block' : 'none';
				$display_db_icon = (!empty($arr['tables'])) ? 'inline-block' : 'none';
				if (!$arr['last_run']){
					$last_run = "- - - - / - - / - - &nbsp;&nbsp;&nbsp; - - : - - : - - ";
				} else {
					$last_run = ibk_formated_time_for_dashboard($arr['last_run']) . ' ago';
				}
				$div_id_arr = explode('_', $k);
				if (isset($div_id_arr[1])){
					$div_id = str_replace('.log', '', $div_id_arr[1]);	
				}
				?>
					<div class="ibk-admin-dashboard-backup-box-wrap">
						<div class="ibk-admin-dashboard-backup-box" id="ibk-b-item-<?php echo $cloud_connection_id;?>" style="background-color: <?php echo '#'.$arr['admin_box_color'];?>">
							<div class="ibk-admin-dashboard-backup-box-main">
								<div class="ibk-admin-dashboard-backup-box-title"><?php echo $arr['snapshot_name'];?></div>
								<div class="ibk-admin-dashboard-backup-box-content"><?php echo $arr['snapshot_description'];?></div>
								<div class="ibk-admin-dashboard-backup-box-links-wrap">
								<div class="ibk-admin-dashboard-backup-box-links">
									<div class="ibk-admin-dashboard-backup-box-link" onClick="ibk_migrate_popup(<?php echo $div_id . ',' . $cloud_connection_id;?>);">Cloud Migrate</div>
									<input type="hidden" value='<?php echo $v;?>' id="ibk-cloud-<?php echo $div_id;?>" />
								</div>
							</div>
							</div>
							<div class="ibk-admin-dashboard-backup-box-bottom">
								<div class="ibk-admin-dashboard-backup-box-files">
									<i title="BackUp Files" class="fa-ibk fa-files-ibk" style="display: <?php echo $display_files_icon;?>"></i>
									<i title="BackUp Database" class="fa-ibk fa-db-ibk" style="display: <?php echo $display_db_icon;?>"></i>
								
									<div class="ibk-admin-dashboard-backup-box-dest">From <span> 
										<?php echo ibk_get_destination_name($cloud_connection_id);?></span>
									</div>
								</div>
								<div class="ibk-admin-dashboard-backup-box-date">
									<div class="date-message">Last Run</div>
									<?php echo $last_run;?>						
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>					
				<?php			
			} 
		}
		
		public function ibk_migrate_popup_box(){
			global $wpdb;
			$cloud_data = unserialize(stripslashes($_REQUEST['cloud_data']));
			$connection_metas = ibk_return_metas_from_custom_db('destinations', $_REQUEST['connection']);
			
			?>
				<div class="ibk-popup-wrapp" id="ibk_popup_box">
						<div class="ibk-the-popup">
							<div class="ibk-popup-top">
								<div class="title">Cloud Migrate Snapshot</div>
								<div class="close-bttn" onclick="ibk_close_popup();"></div>
							<div class="clear"></div>
						</div>
						<div class="ibk-popup-content" >
							
							<form method="post" action="" id="ibk_migrate_popup_form">
								<?php $this->ibk_clound_migrate_msg();?>
								<?php
									$data = $cloud_data['file_arr'];
									end($data);
									$selected_value = (!empty($data[key($data)])) ? $data[key($data)] : '';
									if ($connection_metas['type']=='ftp'){
										if (substr($connection_metas['directory'], -1, 1)!='/'){
											$connection_metas['directory'] .= '/';
										}
										$selected_value = $connection_metas['directory'] . $selected_value;
									}
									reset($data);

									$single_site = (empty($cloud_data['blog_id'])) ? 0 : 1;
								?>
																
								<input type="hidden" value="<?php echo $_REQUEST['connection'];?>" name="cloud_connection_id" />
								<input type="hidden" value="<?php echo $selected_value;?>" name="source_file" id="ibk_source_file"/>
								<input type="hidden" value="<?php echo $connection_metas['type'];?>" name="destination_type" />
								<input type="hidden" value="1" name="ibk_restore_migrate_action" />
								<input type="hidden" value="<?php echo $single_site;?>" name="multisite-single_site" /> 
								
								<?php 
								$destination_type = ibk_get_destination_type($_REQUEST['connection']);
								if (count($data)>1){
									?>
									<div class="ibb-popup-list-snapshots-instances" style="overflow: hidden;">
										<?php 
												foreach ($data as $file_name){
													$file_name_handle = str_replace('.zip', '', $file_name);
													$file_name_handle = explode('_', $file_name_handle);
													if ($connection_metas['type']=='ftp'){
														$file_name = $connection_metas['directory'] . $file_name;
													}
													$class = ($selected_value==$file_name) ? "ibk-restore-snapshot-item-popup-selected" : "ibk-restore-snapshot-item-popup";
													?>
														<div class="<?php echo $class;?>" onClick="ibk_select_snapshot_instance(this, '<?php echo $file_name;?>');"><i class="fa-ibk fa-version-ibk"></i>SNAPSHOT<span class="ibk-from">From</span><span class="ibk-the-filename"><?php echo date("Y-m-d H:i:s", $file_name_handle[3]);?></span></div>
													<?php 
												}	
										?>								
									</div>
									<?php 
								}
								?>
								<div class="clear"></div>
								
								<!-- MULTISITE -->
								<?php if (is_multisite() && $single_site){ ?>
								<input type="hidden" value="<?php echo @$cloud_data['native_wp_tables'];?>" name="native_wp_tables" />
								<input type="hidden" value="<?php echo @$cloud_data['sites_folders'];?>" name="sites_folders" />
								<div class="ibk-inside-item  ibk-multisite-wrapper">
									<h3>MultiSite WP detected</h3>
									<h4>...and your Snapshot is a SingleSite.</h4><br/>
									<p>Select you Site destination:</p>
									<div class="row">
										<div class="col-xs-4">
											<div class="form-group">
												<select name="target_site"  class="form-control m-bot15" >
													<?php 
													$sites = ibk_blog_ids_list(TRUE);
													$blog_id = get_current_blog_id();
													foreach ($sites as $k=>$v){
														$selected = ($k==$blog_id) ? 'selected' : '';
														?>
															<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
														<?php 
													}
													?>
												</select>
											</div>
										</div>
									</div>
								</div>
								<?php }//end of multisite?>
								<!-- MULTISITE -->
								
								<?php 
								if (!empty($cloud_data['files'])){										
									?>
									<div><h3 style="margin-top:35px;">Files to Restore</h3><p>Select whicth files should be Restored</p>
									 <div style="margin-top:15px;">
									<?php 
									$arr_v = explode(',', $cloud_data['files']);
								
									$arr = array(
											'themes' => 'Themes',
											'plugins' => 'Plugins',
											'uploads' => 'Media Files',
									);
									foreach ($arr_v as $k){
										$checked = (strpos($cloud_data['files'], $k)!==FALSE ) ? 'checked' : '';
										if (isset($arr[$k])){
											?>
											<label class="checkbox-inline ibk-checkbox-wrap"><input type="checkbox" onClick="ibk_make_inputh_string(this, '<?php echo $k;?>', '#save_files_list');" <?php echo $checked;?> /><?php echo $arr[$k];?></label>
											<?php 
										}
									}
									?>
									<input type="hidden" value="<?php echo $cloud_data['files'];?>" name="files_to_restore" id="save_files_list" />
									 </div>
									</div>
									<?php
								}								
								?>
																
								<div>
									<h3>DataBase to Restore</h3>
										<p>Pick Up all the Tables or just some of them and exclude those that are not necessary to be Restored</p>
									<div id="ibk-database-list-tables">
									<?php 
									$table_names = ibk_get_table_list();
									$items = explode(',', $cloud_data['tables']);
									foreach ($items as $item){
										if (!empty($item)){
											if (!isset($table_names[$item])){
												$table_names[$item] = $wpdb->prefix . $item;
											}
											?>
												<div id="backup-t-items-<?php echo $item;?>" class="ibk-tag-item">
												<?php echo $table_names[$item];?>
												<div class="ibk-remove-tag" onClick="ibk_remove_db_tag('<?php echo $item;?>', '#backup-t-items-', '#save_db_table_list');" title="Removing tag">x</div>
												</div>
											<?php 											
										}
									}
									?>
									<input type="hidden" id="save_db_table_list" name="tables_to_restore" value="<?php echo $cloud_data['tables'];?>" />
									</div>
								</div>
								
								<div class="ibk-inside-item"> 
									<h3>WordPress Options</h3>
									<p>The next WordPress common options will be <strong>excluded</strong> from Migrate Process</p>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
											<input type="checkbox" class="ibk-switch" checked disabled/>
											<div class="switch disabled" style="display:inline-block;"></div>
											</label>
											WordPress Address (URL)
										</div>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
											<input type="checkbox" class="ibk-switch"  checked disabled/>
											<div class="switch disabled" style="display:inline-block;"></div>
											</label>
											Site Address (URL)
										</div>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#exclude_site_title');" checked />
												<div class="switch" style="display:inline-block;"></div>
											 	<input type="hidden" value="1" name="exclude_site_title" id="exclude_site_title" />						
											</label>
											Site Title
										</div>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#exclude_tagline');" checked />
												<div class="switch" style="display:inline-block;"></div>
											 	<input type="hidden" value="1" name="exclude_tagline" id="exclude_tagline" />	
											</label>
											Tagline
										</div>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#exclude_email');" checked />
												<div class="switch" style="display:inline-block;"></div>
												<input type="hidden" value="1" name="exclude_email" id="exclude_email" />
											</label>
											E-mail Address
										</div>
										<div class="ibk-migrate-excluded-item">
											<label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#exclude_indeed_tables');" checked />
												<div class="switch" style="display:inline-block;"></div>
												<input type="hidden" value="1" name="exclude_indeed_tables" id="exclude_indeed_tables" />
											</label>
											WP SuperBackup Details
										</div>
								</div>
								<?php 
									if (is_multisite()){
										?>
										<div class="ibk-inside-item"> 
											<h4>WP MultiSite Options</h4>
											<div class="ibk-migrate-excluded-item">
											  <label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" checked disabled/>
												<div class="switch disabled" style="display:inline-block;"></div>
												</label>
												wp_blogs (database table)
											</div>	
											<div class="ibk-migrate-excluded-item">
											  <label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" checked disabled/>
												<div class="switch disabled" style="display:inline-block;"></div>
												</label>
												wp_blog_versions (database table)
											</div>	
												
											<div class="ibk-migrate-excluded-item">
											  <label class="ibk_lable_shiwtch">
												<input type="checkbox" class="ibk-switch" checked disabled/>
												<div class="switch disabled" style="display:inline-block;"></div>
												</label>
												wp_site (database table)
											</div>
											
											<div class="ibk-migrate-excluded-item">
												<label class="ibk_lable_shiwtch">
													<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#exclude_multisite_siteurl');" checked />
													<div class="switch" style="display:inline-block;"></div>
													<input type="hidden" value="1" name="exclude_multisite_siteurl" id="exclude_multisite_siteurl" />
												</label>
												siteurl (from 'wp_sitemeta' database table)
											</div>							
																		
										</div>
										<?php 
									}
								?>
								
							</form>
								<div class="ibk-popup-footer">
								  <div class="ibk-migrate-buttons-wrap">
									<span class="ibk-add-new" id="submit_the_popupform" onclick="jQuery( '#ibk_migrate_popup_form' ).submit();"> 
									<i title="" class="fa-ibk fa-migrate-btn-ibk"></i>
									<span>Cloud Migrate</span>
									</span>
									<span class="ibk-close-btn" onclick="ibk_close_popup();">
									<i title="" class="fa-ibk fa-close-ibk"></i>
									<span>Close</span>
									</span>
								  </div>
								</div>
						</div>
					</div>
				</div>
			<?php 
			die();
		}
		
		public function check_for_notification(){
			$notifications = array(
									'cron' => FALSE,
									'zip' => FALSE,
									'execution_time' => FALSE,
									'memory' => FALSE,
									);
			//CRON
			if (ibk_checkCron()!==TRUE){
				$notifications['cron'] = TRUE;
			}
			
			//ZIP
			if (!extension_loaded('zip')){
				$notifications['zip'] = TRUE;
			}
			
			//EXECUTION TIME
			if (ini_get('max_execution_time')<300){
				$notifications['execution_time'] = TRUE;
			}
			//MEMORY LIMIT
			if ((int)ini_get('memory_limit')<64){
				$notifications['memory'] = TRUE;
			}		

			update_option('ibk_dashboard_notifications', $notifications);
		}
		
		public function show_notification(){
			/*
			 * print the notifications
			 * @param none
			 * @return none
			 */	
			
			if (time()>get_option('ibk_dashboard_notification_time')){
				$notifications = get_option('ibk_dashboard_notifications');

				if (!empty($notifications['cron'])){
					/////////CRON NOTIFICATION MSG
					?>
					<div class="ibk-dashboard-notification-msg"><strong>SuperBackup Warning:</strong> Your Backups will not start because your Cron is not working or is disabled. <a href="?page=ibk_admin&tab=system&subtab=crons">Check here</a></div>					
					<?php 	
				}
				if (!empty($notifications['zip'])){
					/////////ZIP NOTIFICATION MSG
					?>
					<div class="ibk-dashboard-notification-msg"><strong>SuperBackup Warning:</strong> Your Backups will not work because PHP ZipArchive Library is missing or disabled. Contract your <strong>Admin System</strong>.</div>	
					<?php 	
				}
				
				//WARNINGS
				$warning = array();
				if (!empty($notifications['execution_time'])){
					$warning[] = 'Execution time is less than 5 mins;';
				}
				if (!empty($notifications['memory'])){
					$warning[] = 'Memory limit is less than 64Mb;';
				}
				
				if ($warning){
					?>
					<div class="ibk-dashboard-warning-msg"><strong>SuperBackup be aware:</strong> Your Backup/Restore processes may suddnely stops because of your server limited resources: <strong><?php echo implode(' ', $warning);?></strong>. Split your backup into several Snapshots and contact your Admin System.</div>
					<?php 		
				}				
			}
			
		}
		
		public function ibk_clound_migrate_msg(){
			/*
			 * print warning message for cloud & migrate tabs
			 * @param none
			 * @return none
			 */	
			?>
			<div class="ibk-cloud-migrate-warning-msg">
				<div style="padding-bottom: 8px;">For safety reasons before starting the Migration process be sure that you have a recent <strong>Backup</strong> done for this instance.</div>
				<div>If you migrate the <strong>"users" table</strong> the users's credentials may be changed according to your migrated Snapshot.</div>	
			</div>
			<?php 
		}
		
		
	}//end of class
}//end of if class exists