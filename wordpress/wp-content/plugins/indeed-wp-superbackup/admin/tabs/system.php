<?php
global $wp_version;
global $wpdb;
$themes_dir = WP_CONTENT_DIR . '/themes/';
$plugin_dir = WP_CONTENT_DIR . '/plugins/';
$uploads_dir = WP_CONTENT_DIR . '/uploads/';
if(isset($_GET['subtab'])) $subtab=$_GET['subtab'];
else $subtab ='';
?>
<div class="ibk-subtab-menu">
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=server';?>"> Server Stage</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=crons';?>">Crons</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=database';?>">DataBase</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=folders';?>">Folders</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=adminlogs';?>">Admin Logs</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=system&subtab=phpinfo';?>">PHP Info</a>
</div>
<div class="ibk-settings-wrap">
	<div class="ibk-stuffbox" style="margin-top: 30px;">
	<?php switch($subtab){ 
		 case 'crons': ?> 
		 <?php
		 	if ( isset( $_GET['action']) ){
				if($_GET['action'] == 'run' ) {
					$hookname = wp_unslash( $_GET['hookname'] );
					$sig = wp_unslash( $_GET['sig'] );
					ibk_runCron( $hookname, $sig );	
				}
				if($_GET['action'] == 'delete' ) {
					$hookname = wp_unslash( $_GET['hookname'] );
					$sig = wp_unslash( $_GET['sig'] );
					$next_run = wp_unslash( $_GET['next_run'] );
					ibk_deleteCron( $hookname, $sig, $next_run  );	
				}
			}
		 ?>
		 <h3 class="ibk-h3">WordPress Cron Details</h3>
			<div class="inside">
			<div class="ibk-inside-item"> 
				<div class="row">
					<div class="col-xs-12">
					<?php $crons = indeed_get_cron_list(); ?>
					<?php $check_cron = ibk_checkCron();
					if( $check_cron !== true){
						echo '<div class="ibk_error_box">'.$check_cron.'</div>';	
					}else{
						echo '<div class="ibk_inform_box">'.__('Cron System verified!','ibk').'</div>';
					}
					?>
					<div style="width:75%; margin-bottom:20px;">
					<p><?php echo __('The CRON Jobs (WordPress Taks) can be followed into the below table. Be sure that manually Running or Deletings jobs are used with caution. WordPress will rebuild any mandatory/standard WordPress Tasks if they are missing.','ibk');?></p>
					</div>
					<h5><strong>Current Time:</strong> <?php echo date_i18n( 'Y-m-d H:i:s' );?></h5>
					<table style="width:80%;" class="wp-list-table widefat fixed striped pages">
					<thead>
					   <tr>
						<th style="width:25%;">Hook Name</th>
						<th style="width:20%;">Arguments</th>
						<th style="width:25%;">Next Run</th>
						<th style="width:20%;">Recurrence</th>
						<th style="width:10;">Actions</th>
					   </tr>
					</thead>
					<tbody>
					<?php if ( is_wp_error( $crons ) ) {
							?>
							<tr><td colspan="7"><?php echo esc_html( $crons->get_error_message() ); ?></td></tr>
							<?php
						} else {
							//echo '<pre>'.print_r($crons);
							foreach ( $crons as $id => $event ) {
								$special_style = '';
								if(strpos($event->hook,'indeed') !== false)
										$special_style = 'style="font-weight:bold; color:#069;"';
								if ( empty( $event->args ) ) {
									$args = __( 'None', 'ibk' );
								} else {
									if ( defined( 'JSON_UNESCAPED_SLASHES' ) ) {
										$args = wp_json_encode( $event->args, JSON_UNESCAPED_SLASHES );
									} else {
										$args = stripslashes( wp_json_encode( $event->args ) );
									}
								}
							echo '<tr>';
				
								if ( 'crontrol_cron_job' == $event->hook ) {
									echo '<td><em>' . __( 'PHP Cron', 'ibk' ) . '</em></td>';
									echo '<td><em>' . __( 'PHP Code', 'ibk' ) . '</em></td>';
								} else {
									echo '<td '.$special_style.'>' . $event->hook  . '</td>';
									echo '<td>' . $args . '</td>';
								}
								if($event->time+60 < time()){
									echo '<td style="color:red;">';
									echo get_date_from_gmt( date( 'Y-m-d H:i:s', $event->time ), 'Y-m-d H:i:s' );
								echo '</td>';
								}else{
								echo '<td>';
									echo get_date_from_gmt( date( 'Y-m-d H:i:s', $event->time ), 'Y-m-d H:i:s' );
									echo ' ( on '.ibk_nextTimerun($event->time).')';
								echo '</td>';
								}
								if ( $event->schedule ) {
									echo '<td>every ';
									echo ibk_timeFormat($event->interval) ;
									echo '</td>';
								} else {
									echo '<td>';
										__( 'Non-repeating', 'ibk' );
									echo '</td>';
								}
								echo '<td>';
									echo '<a href="'.$url . '&tab=system&subtab=crons&action=run&hookname=' . $event->hook.'&sig='.$event->sig.'">'. __( 'Run Now', 'ibk' ) . '</a> | ';
									echo '<a href="'.$url . '&tab=system&subtab=crons&action=delete&hookname=' . $event->hook.'&sig='.$event->sig.'&next_run='.$event->time.'">'. __( 'Delete', 'ibk' ) . '</a>';
								echo '</td>';
							echo '</tr>';
							}
						}?>
					</tbody>
					<tfoot>
					   <tr>
						<th>Hook Name</th>
						<th>Arguments</th>
						<th>Next Run</th>
						<th>Recurrence</th>
						<th>Actions</th>
					   </tr>
					</tfoot>
					</table>
					</div>
				</div>
			</div>
			</div>	
			<?php break; ?>
		 <?php case 'database': ?>
			<h3 class="ibk-h3">DataBase Info</h3>
			<div class="inside">
			<div class="ibk-inside-item"> 
				<div class="row">
					<div class="col-xs-12">
					<?php 
					$total_size = 0;
					$total_rows = 0;
					$arr = array();
					$q = "SELECT table_name,table_rows,data_length,index_length,engine FROM information_schema.tables
							WHERE table_schema = '". DB_NAME ."'";
					$data = $wpdb->get_results($q);
					if(isset($data) && count($data)>0){
						foreach($data as $k=>$table){
							$arr[$k]['tabe_name'] = $table->table_name;
							$arr[$k]['rows'] = $table->table_rows;
							$total_rows += $table->table_rows;
							$total_size += $table->data_length+$table->index_length;
							$arr[$k]['size'] = indeed_from_byte_to_mb_gb($table->data_length+$table->index_length);
							$arr[$k]['engine'] = $table->engine;
						}
					}
					?>
					<div style="width:75%; margin-bottom:20px;">
					<p><?php echo __('Those are informative details. If your DataBase cannot be backuped check your Database Tables health closer.','ibk');?></p>
					</div>
					<table style="width:80%;" class="wp-list-table widefat fixed striped pages">
					<thead>
					   <tr>
						<th style="width:40%;">DataBase Table</th>
						<th>Rows</th>
						<th>Size</th>
						<th>Engine</th>
					   </tr>
					</thead>
					<tbody>
						<?php foreach($arr as $k=>$table){
							echo '<tr>';
								echo '<td style="font-weight:bold;">'.$table['tabe_name'].'</td>';
								echo '<td>'.$table['rows'].'</td>';
								echo '<td>'.$table['size'].'</td>';
								echo '<td>'.$table['engine'].'</td>';
							echo '</tr>';
						}
						?>
						<tr style="font-size:15px;">
							<td style="font-weight:bold; color:#069;">TOTAL</td>
							<td style="font-weight:bold; color:#069;"><?php echo $total_rows; ?></td>
							<td style="font-weight:bold; color:#069;"><?php echo indeed_from_byte_to_mb_gb($total_size); ?></td>
							<td>---</td>
						</tr>
					</tbody>
					<tfoot>
					   <tr>
						<th>DataBase Table</th>
						<th>Rows</th>
						<th>Size</th>
						<th>Engine</th>
					   </tr>
					</tfoot>
					</table>
					</div>
				</div>
			</div>
			</div>	
			<?php break; ?>
		<?php case 'folders': ?>
			<h3 class="ibk-h3">Wp-Content Folders</h3>
			<div class="inside">
			<div class="ibk-inside-item"> 
				<div class="row">
					<div class="col-xs-3 ibk_system_filelist">
					<?php ibk_getFolders(WP_CONTENT_DIR . '/themes'); ?>
					</div>
					<div class="col-xs-3 ibk_system_filelist">
					<?php ibk_getFolders(WP_CONTENT_DIR . '/plugins'); ?>
					</div>
					<div class="col-xs-3 ibk_system_filelist">
					<?php ibk_getFolders(WP_CONTENT_DIR . '/uploads'); ?>
					</div>
				</div>
			</div>
			</div>	
			<?php break; ?>	
			
		<?php case 'adminlogs': ?>
			<h3 class="ibk-h3">Admin Logs</h3>
			<div class="inside">
			<div class="ibk-inside-item"> 
				<?php 
					$admin_logs = ihc_print_global_log();
					if (!empty($admin_logs)){
						//add extra clear button	
						?>
				<div style="margin-top: 20px;">
					<input type="submit" value="Clear" onClick="ibk_clear_log_debug();" class="button button-primary button-large" />
				</div>
						<?php 
					}
				?>
				<div class="row">
					<div class="col-xs-12">
						<div id="ibk_debug_log">
							<?php print $admin_logs;?>
						</div>						
					</div>
				</div>
				<div style="margin-top: 20px;">
					<input type="submit" value="Clear" onClick="ibk_clear_log_debug();" class="button button-primary button-large" />
				</div>
			</div>
			</div>	
			<?php break; ?>
			
		<?php case 'phpinfo': ?>
			<h3 class="ibk-h3">PHP Info</h3>
			<div class="inside">
			<div class="ibk-inside-item"> 
				<div class="row">
					<div class="col-xs-12">
					<?php echo '<pre>'.phpinfo().'</pre>';?>
					</div>
				</div>
			</div>
			</div>	
			<?php break; ?>
			
		<?php default: ?>		
		<h3 class="ibk-h3">Server Stage</h3>
		<?php
		$php_ver = phpversion();
		$mysql_ver = $wpdb->db_version();
		
		$wp_cron = ( defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ) ? 'Disabled' : 'Enabled';
		
		$safe_mode = (ini_get('safe_mode')) ? 'On' : 'Off';
		
		$php_zip = (class_exists('ZipArchive')) ? 'On' : 'Off';
		
		$memory_limit = ini_get('memory_limit');
		
		$max_execution_time = ini_get('max_execution_time');
		
		$open_ssl = (extension_loaded('openssl')) ? 'On' : 'Off';
		
		$curl = (function_exists('curl_version')) ? 'On' : 'Off';
		
		$wp_alternative_cron = ( defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON ) ? 'Enabled' : 'Disabled';
		
		$spl_file_object = (class_exists('SplFileObject')) ? 'On' : 'Off';
		
		$recursive_iterator = (class_exists('RecursiveIteratorIterator')) ? 'On' : 'Off';
		
		$post_max_size = ini_get('post_max_size');
		
		$upload_max_size = ini_get('upload_max_filesize'); 
		
		$max_input_time = ini_get('max_input_time');
		
		$free_space = @disk_free_space('/');
		
		$min_space_needed = ibk_get_min_space_needed();
		
		$meta_arr = ibk_get_general_metas();
		$backup_temp_dir_perm = ibk_check_dir_permission(WP_CONTENT_DIR . '/uploads/' . $meta_arr['ibk_backup_dir']);
		
		$default_backup_dir = ibk_check_dir_permission(WP_CONTENT_DIR . '/uploads/indeed-backups');
		?>
		<div class="inside">
			<div class="ibk-inside-item"> 
				<div class="row">
					<div class="col-xs-12">
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>PHP Max Execution Time:</strong> <?php echo $max_execution_time;?></div>
							<?php $icon = ($max_execution_time>300) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>	
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>WordPress Memory Limit:</strong> <?php echo WP_MEMORY_LIMIT;?></div>
							<?php $icon = (WP_MEMORY_LIMIT>64) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>		
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP Memory Limit: <?php echo $memory_limit;?></div>
							<?php $icon = ($memory_limit>=256) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>	
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">Disk Free Space Available: <?php echo indeed_from_byte_to_mb_gb($free_space);?></div>
							<?php $icon = ($free_space>$min_space_needed) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>							
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">Minimum Space Needed: <?php echo indeed_from_byte_to_mb_gb($min_space_needed);?></div>
							<?php $icon = ($free_space>$min_space_needed) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>	
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>Backup Directory Permissions:</strong> <?php echo $backup_temp_dir_perm;?></div>
							<?php $icon = ($backup_temp_dir_perm>0) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">Backup Default Local Backup Directory Permissions: <?php echo $default_backup_dir;?></div>
							<?php $icon = ($default_backup_dir>0) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>																			
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP Max POST Size: <?php echo $post_max_size;?></div>
							<?php $icon = ($post_max_size>=200) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>	
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP Max Upload Size: <?php echo $upload_max_size;?></div>
							<?php $icon = ($upload_max_size>=200) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>	
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP Max Input Time: <?php echo $max_input_time;?></div>
							<?php $icon = ($max_input_time>=200) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>																													
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">WordPress Version: <?php echo $wp_version;?></div>
							<?php $icon = ($wp_version) ? 'ok-sign' : 'alert'; ?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>WP_CRON:</strong> <?php echo $wp_cron;?></div>
							<?php $icon = ($wp_cron=='Enabled') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">WP Plugin Diretory: <?php echo $plugin_dir;?></div>
							<?php $icon = ($plugin_dir && is_writable($plugin_dir)) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">WP Themes Directory: <?php echo $themes_dir;?></div>
							<?php $icon = ($themes_dir && is_writable($themes_dir)) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">WP Uploads: <?php echo $uploads_dir;?></div>
							<?php $icon = ($uploads_dir && is_writable($uploads_dir)) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">Alternative WP Cron: <?php echo $wp_alternative_cron;?></div>
							<?php $icon = ($wp_alternative_cron=='Disabled') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>PHP Version:</strong> <?php echo $php_ver;?></div>
							<?php $icon = ($php_ver>5.2) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP Safe Mode: <?php echo $safe_mode;?></div>
							<?php $icon = ($safe_mode=='Off') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>

						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message"><strong>PHP ZipArchive Library:</strong> <?php echo $php_zip;?></div>
							<?php $icon = ($php_zip=='On') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP RecursiveIterator Class: <?php echo $recursive_iterator;?></div>
							<?php $icon = ($recursive_iterator=='On') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>						
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">PHP SplFileObject Class: <?php echo $spl_file_object;?></div>
							<?php $icon = ($spl_file_object=='On') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>

						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">OpenSSL: <?php echo $open_ssl;?></div>
							<?php $icon = ($open_ssl=='On') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">cURL: <?php echo $curl;?></div>
							<?php $icon = ($curl=='On') ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
						<div class="ibk-view-system-wrap">
							<div class="ibk-view-system-message">MySQL version:<?php echo $mysql_ver;?></div>
							<?php $icon = ($mysql_ver>5) ? 'ok-sign' : 'alert';?>
							<div class="ibk-view-system-icon"><span class="glyphicon glyphicon-<?php echo $icon;?>" aria-hidden="true"></span></div>
						</div>
					</div>
				</div>
			</div>		
		</div>
	<?php } ?>	
	</div>
</div>		

<?php 

?>