<?php 
require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
$logs_obj = new IndeedDoLogs();
$process_list = $logs_obj->get_process_list();

$this->show_notification();
?>
<div class="ibk-logs-items-wrap">
	<div class="ibk-stuffbox" style="margin-top: 50px;">
		<h3 class="ibk-h3">Snapshot Logs</h3>
<?php
$incomplete_arr = FALSE;


foreach ($process_list as $process_id){
	if (!empty($data)) unset($data);
	$data = $logs_obj->get_logs_for_process($process_id);
	$backup_meta = ibk_return_metas_from_custom_db('backups', $data[0]->action_id, true);

	end($data);
	$last_key = key($data);
	$c_time = FALSE;
	if (!empty($data[$last_key]->create_date)) $c_time = strtotime($data[$last_key]->create_date);
	$activity = ibk_formated_time_for_dashboard($c_time);
	

	$display_files_icon = 'none';
	$display_db_icon = 'none';
	if ($backup_meta){
		$display_files_icon = ($backup_meta['save_files']=='all' || ($backup_meta['save_files']=='custom' && $backup_meta['save_files_list'] && $backup_meta['save_files_list']!=-1) ) ? 'inline-block' : 'none';
		$display_db_icon = (isset($backup_meta['save_db_table_list']) && $backup_meta['save_db_table_list']) ? 'inline-block' : 'none';		
	}	
	
	/************************ progress bar ******************/
	$icon = '';
	$complete = ibk_get_complete_percetage_for_log($data);
	
	if ($data[$last_key]->status==1){
		if ($complete==100){
			$progress_bar = 'success';
			$icon = '<i class="fa-ibk fa-check-circle-bk"></i>';
		} else {
			if (strtotime($data[$last_key]->create_date)+60*60<time()){
				//stopped process
				$progress_bar = 'progress-bar-info';
				$icon = '<i class="fa-ibk fa-stopped-circle-bk"></i>';				
			} else {
				$progress_bar = 'success';
				$incomplete_arr[] = $process_id;
			}	
		}
	} else {
		$progress_bar = 'danger';
		if ($complete==100){
			$icon = '<i class="fa-ibk fa-error-circle-bk"></i>';
		} else {
			if (strtotime($data[$last_key]->create_date)+60*60<time()){
				//stopped process
				$icon = '<i class="fa-ibk fa-error-circle-bk"></i>';
			}			
		}
	}		
	
	$backup_name = (isset($backup_meta['name']) && $backup_meta['name']) ? $backup_meta['name'] : '';
	?>
	
   <div class="ibk-log-wrap" id="log_no_<?php echo $process_id;?>">
	<div class="ibk-log-left">
		<div class="ibk-log-title"><?php echo $backup_name;?> <span class="ibk-last-activity"> - Last activity <?php if ($activity) echo $activity . ' ago'; else echo 'Unknown'?></span></div>
		<div class="ibk-log-progress">
			<div class="progress">
  				<div class="progress-bar progress-bar-<?php echo $progress_bar;?> progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $complete;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $complete . '%';?>;">
    			<?php echo $complete . '%';?>
 				</div>
			</div>
			<div class="ibk-log-message"><i class="fa-ibk fa-info-circle-ibk"></i><span id="ibk_log_msg_<?php echo $process_id;?>"><?php 
				if ($progress_bar=='progress-bar-info'){
					echo "Process Stopped!";	
				} else {
					echo $data[$last_key]->message;
				}
			?></span></div>
		</div>
		<div class="ibk-log-bottom">
			<div class="ibk-log-bottom-files">
				<i title="BackUp Files" class="fa-ibk fa-files-ibk" style="display: <?php echo $display_files_icon;?>;"></i>
				<i title="BackUp Database" class="fa-ibk fa-db-ibk" style="display: <?php echo $display_db_icon;?>;"></i>
				<?php if (isset($backup_meta['destination']) && $backup_meta['destination']){ ?>	
					<div class="ibk-log-bottom-dest">Goes to <span> 
								<?php echo ibk_get_destination_name($backup_meta['destination']);?>
							</span>
					</div>
				<?php } ?>	
			</div>
			<div class="ibk-log-bottom-scheduled">
					<?php
						if (isset($backup_meta['backup_interval_type'])){
							if($backup_meta['backup_interval_type'] == -1) {?>
								<i title="Scheduled" class="fa-ibk fa-scheduled-ibk"></i>
							<?php }elseif($backup_meta['backup_interval_type'] == 1){?>
								<i title="Periodically" class="fa-ibk fa-periodically-ibk"></i>
							<?php 
							}
						}
					?>
			</div>
		</div>

	</div>
	<div class="ibk-log-right">
		<div class="ibk-log-status"><?php echo $icon;?></div>
		<div class="ibk-log-view" onClick="ibk_open_popup('logs', <?php echo $process_id;?>);"><span>View Logs</span></div>
		<div class="" style="margin-top:3px;font-size: 11px;color:red;text-align: center;padding-left: 8px;cursor: pointer;" onClick="ibk_delete_log(<?php echo $process_id;?>, '#log_no_<?php echo $process_id;?>');"><span>Delete This Log!</span></div>
	</div>
	<div class="clear"></div>
  </div>

<?php } ?>
	
	</div>
</div>
<div id="indeed_popup_wrapp"></div>
<?php 
if ($incomplete_arr){
	$incomplete_str = implode(',', $incomplete_arr);
	?>
		<script>
			jQuery(document).ready(function(){
				var str = '<?php echo $incomplete_str;?>';
				var arr = str.split(',');
				ibk_progress_bar_update(arr, 0);
			});
		</script>	
	<?php 
} 
?>
