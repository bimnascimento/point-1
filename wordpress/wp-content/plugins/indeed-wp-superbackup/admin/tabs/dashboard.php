<?php
$themes_size = indeed_get_dir_size(WP_CONTENT_DIR . '/themes');
$plugins_size = indeed_get_dir_size(WP_CONTENT_DIR . '/plugins');
$uploads_size = indeed_get_dir_size(WP_CONTENT_DIR . '/uploads'); 
$gen_metas = ibk_get_general_metas();
$temp_dir = $gen_metas['ibk_backup_dir'];
			if (!$temp_dir){
				$temp_dir = 'isnapshots';
			}
$upload_dir = indeed_get_dir_size(WP_CONTENT_DIR . '/uploads/'.$temp_dir);
$uploads_size = $uploads_size - $upload_dir;
$plugins_count = count(get_plugins());
$average_plugin = 0;
if ($plugins_size && $plugins_count){
	$average_plugin = indeed_from_byte_to_mb_gb($plugins_size, $plugins_count);
}
$themes_no = indeed_count_dir_subdirs(WP_CONTENT_DIR.'/themes/');
$avg_themes = 0;
if ($themes_no && $themes_size){
	$avg_themes = indeed_from_byte_to_mb_gb($themes_size, $themes_no);	
}

$total_db_tables = count(ibk_get_table_list());
$total_non_wp_tables = count (ibk_get_table_list('non_wp'));

$no_of_snapshots = ibk_return_active_snapshots_nr();

//check notifications
$this->check_for_notification();
$this->show_notification();
?>
<div class="ibk-stats-wrap">
<div class="row">
	<div class="col-xs-8">
		<div class="row  ibk-stats-main">
			<div class="col-xs-6">
				<div class="ibk-stats-main-count">
					<span class="ibk-big-cunt"><?php echo $no_of_snapshots;?></span>
					<span>Snapshots<br/>Active</span>
				</div>
				<div class="ibk-stats-last-logs">
				<h2>Last Activity:</h2>
					
					<?php 
						if (!class_exists('IndeedDoLogs')){
							require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
						}
						$logs_obj = new IndeedDoLogs();
						$process_list = $logs_obj->get_process_list('LIMIT 3');
						foreach ($process_list as $process_id){
							if (!empty($data)) unset($data);
							$data = $logs_obj->get_logs_for_process($process_id);
							$backup_meta = ibk_return_metas_from_custom_db('backups', $data[0]->action_id);
						
							end($data);
							$last_key = key($data);
							$c_time = FALSE;
							if (!empty($data[$last_key]->create_date)) $c_time = strtotime($data[$last_key]->create_date);
							$activity = ibk_formated_time_for_dashboard($c_time);
							$complete = ibk_get_complete_percetage_for_log($data);
							if (isset($data[$last_key]->status) && $data[$last_key]->status==1){
								$progress_bar = 'success';
							} else {
								$progress_bar = 'danger';
							}
							?>
					<div class="ibk-stats-last-log">
						<div class="ibk-log-title"><?php echo $backup_meta['name'];?><span class="ibk-last-activity"> - Last action <?php echo $activity;?></span></div>
						<div class="ibk-log-progress">
							<div class="progress">
								<div class="progress-bar progress-bar-<?php echo $progress_bar;?> progress-bar-striped" role="progressbar" aria-valuenow="<?php echo $complete;?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $complete . '%';?>;">
    								<?php echo $complete . '%';?>
 								</div>
							</div>
						</div>	
					</div>							
							<?php 
						}						
					?>

				</div>
			</div>
			<div class="col-xs-6">
			<h2 class="ibk-top-flot">Destinations <span style="color:#888; font-size:22px;  font-weight: 100;">(distribution)</span></h2>
			<div id="ibk-pie-1" class='ibk-flot'></div>
			</div>
		</div>
	</div>
	<div class="col-xs-4">
		<div class="row">
			<div class="col-xs-12 ibk-stats-size ibk-stats-color1">
				<div class="ibk-left-icon"><i class="fa-ibk fa-theme-ibk"></i></div>
				<div class="ibk-right-text"><div class="ibk-file-counts"><?php					
					if ($themes_size) echo indeed_from_byte_to_mb_gb($themes_size);
					else echo 'Unknown';
				?></div><div class="ibk-file-type">Themes</div></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ibk-stats-size ibk-stats-color2">
				<div class="ibk-left-icon"><i class="fa-ibk fa-plugin-ibk"></i></div>
				<div class="ibk-right-text"><div class="ibk-file-counts"><?php 					
					if ($plugins_size) echo indeed_from_byte_to_mb_gb($plugins_size);
					else echo 'Unknown';
					?></div><div class="ibk-file-type">Plugins</div></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ibk-stats-size ibk-stats-color3">
				<div class="ibk-left-icon"><i class="fa-ibk fa-uploads-ibk"></i></div>
				<div class="ibk-right-text"><div class="ibk-file-counts"><?php 					
					if ($uploads_size) echo indeed_from_byte_to_mb_gb($uploads_size);
					else echo 'Unknown';
					?></div><div class="ibk-file-type">Uploads</div></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ibk-stats-size ibk-stats-color4">
				<div class="ibk-left-icon"><i class="fa-ibk fa-stats-shanp-ibk"></i></div>
				<div class="ibk-right-text"><div class="ibk-file-counts"><?php 
					if ($upload_dir){
						echo  indeed_from_byte_to_mb_gb($upload_dir);
					}
					else{
						echo '0 MB';
					}
					?></div><div class="ibk-file-type">temporary Snapshots</div></div>
			</div>
		</div>
	</div>
</div>
<?php 

?>
<div class="row">
	<div class="col-xs-3">
		<div class="ibk-stats-bottom-box ibk-stats-color5">
			<div class="ibk-stats-bottom-top"><div class="ibk-big-number-top">Avg. <?php echo $average_plugin;?>/plugin</div><div class="ibk-big-number"><?php echo $plugins_count;?></div></div>
			<div class="ibk-stats-bottom-bt"><div class="ibk-bottom-label">WP Plugins Installed</div></div>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="ibk-stats-bottom-box ibk-stats-color7">
			<div class="ibk-stats-bottom-top"><div class="ibk-big-number-top">Avg. <?php echo $avg_themes;?>/theme</div><div class="ibk-big-number"><?php echo $themes_no;?></div></div>
			<div class="ibk-stats-bottom-bt"><div class="ibk-bottom-label">WP Themes Installed</div></div>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="ibk-stats-bottom-box ibk-stats-color6">
			<div class="ibk-stats-bottom-top"><div class="ibk-big-number-top">Including standard WP Tables</div><div class="ibk-big-number"><?php echo $total_db_tables;?></div></div>
			<div class="ibk-stats-bottom-bt"><div class="ibk-bottom-label">Total DataBase Tables</div></div>
		</div>
	</div>
	<div class="col-xs-3">
		<div class="ibk-stats-bottom-box ibk-stats-color8">
			<div class="ibk-stats-bottom-top"><div class="ibk-big-number-top">From Plugins and Additional Themes</div><div class="ibk-big-number"><?php echo $total_non_wp_tables;?></div></div>
			<div class="ibk-stats-bottom-bt"><div class="ibk-bottom-label">Non WP DataBase Tables</div></div>
		</div>
	</div>
</div>
</div>

<?php 
$logs_obj = new IndeedDoLogs();
$process_list = $logs_obj->get_process_list();
$posible_destinations = ibk_return_destination_types();
$destination_counts = array();
foreach ($process_list as $process_id){
	$data = $logs_obj->get_logs_for_process($process_id);
	$backup_meta = ibk_return_metas_from_custom_db('backups', $data[0]->action_id);
	$type = ibk_get_destination_type($backup_meta['destination']);	
	if ($type){
		if (empty($destination_counts[$type])) $destination_counts[$type] = 1;
		else $destination_counts[$type]++;		
	}
}

?>

<script>
			if (jQuery("#ibk-pie-1").length > 0) {
				var d = [];
				<?php 
					$colors_arr = array('#fa8564', '#9972b5', '#1fb5ac', '#ffc333', '#607d8b', '#00858f', '#c9c9c0', '#333', '#c0c0c0', '#02c92b' );
					if ($destination_counts){
						$i = 0;
						foreach ($destination_counts as $k=>$v){
							echo "d[".$i."] = { label: '".$posible_destinations[$k]."', data: '".$v."',  color: '".$colors_arr[$i]."'};";
							$i++;
							
						}						
					}
					echo 'console.log(d);';
				?>
		
				jQuery.plot(jQuery("#ibk-pie-1"), d, {
					series: {
				        pie: {
							innerRadius: 0.4,
				            show: true,
							label: {
								show: true,
								radius: 3/4,
								threshold: 0.1,
								formatter: function(label, series){
	                        		return '<div style="font-size:8pt;text-align:center;padding:2px 8px;color:white;">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
	                    		},
								background: { 
									opacity: 0.5,
									color: '#000'
								}
							}			              
				        },
				    },
					legend: {
	        			show: false
	    			},
					grid: {
	        			hoverable: true,
	        			clickable: true
	    			} 			    	
				});		
			}

</script>
<?php 