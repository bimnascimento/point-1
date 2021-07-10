<?php
if (isset($_REQUEST['save-bttn'])){
	ibk_save_general_metas($_REQUEST);
}
$meta_arr = ibk_get_general_metas();
 
if (isset($_GET['delete_logs'])){
	$one_month_ago = strtotime( $_GET['older_than'], time() );
	if (!class_exists('IndeedDoLogs')){
		require_once IBK_PATH . 'classes/IndeedDoLogs.class.php';
	}
	$obj = new IndeedDoLogs();
	$obj->clean_db($one_month_ago);
} else if (isset($_GET['delete_temp_files'])){
	$dir = WP_CONTENT_DIR . '/uploads/' . $meta_arr['ibk_backup_dir'];
	indeed_rmdir_recursive($dir, TRUE);
} 

$this->show_notification();
?>

<div class="ibk-settings-wrap">
	<div class="ibk-stuffbox" style="margin-top: 50px;">
		<h3 class="ibk-h3">General Settings</h3>
		<div class="inside">
			<form method="post" action="">
			
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-6">
							<h2>Backup Directory</h2>
							<p>The Temporary directory where the snapshots are saved first before are send it to the last Destination</p>
							<div class="input-group input-group-lg">
								 <span class="input-group-addon" id="basic-addon1">Directory</span>
								 <input type="text" class="form-control" placeholder="isnapshots" name="ibk_backup_dir" value="<?php echo $meta_arr['ibk_backup_dir'];?>" aria-describedby="basic-addon1">
							</div>
							<br/>
							<h4>Keep Temporary Files</h4>
							<p>By Default, those temporary files are deleted after are sent to the Destionation</p>
							<label class="ibk_lable_shiwtch" style="margin:0px 0 0px -10px;">
								<?php $checked = ($meta_arr['ibk_backup_files']) ? 'checked' : '';?>
								<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#ibk_backup_files');" <?php echo $checked;?> />
								<div class="switch" style="display:inline-block;"></div>
							</label>
							<input type="hidden" value="<?php echo $meta_arr['ibk_backup_files'];?>" name="ibk_backup_files" id="ibk_backup_files" /> 
						</div>
					</div>
				</div>
				
				<div class="ibk-line-break"></div>
				
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-6">
							<h2>Email Notification</h2>
							
							<label class="ibk_lable_shiwtch" style="margin:10px 0 10px -10px;">
								<?php $checked = ($meta_arr['ibk_email_sent']) ? 'checked' : '';?>
								<input type="checkbox" class="ibk-switch" onClick="ibk_check_and_h(this, '#ibk_email_sent');" <?php echo $checked;?> />
								<div class="switch" style="display:inline-block;"></div>
							</label>
							<input type="hidden" value="<?php echo $meta_arr['ibk_email_sent'];?>" name="ibk_email_sent" id="ibk_email_sent" /> 
							<p>The WP SuperBackup system may notify via Email for cetain activity!</p>
							<div class="input-group">
								<span class="input-group-addon" id="basic-addon1">@</span>
								<input type="text" class="form-control" placeholder="wordpress@email.com" name="ibk_email" value="<?php echo $meta_arr['ibk_email'];?>" aria-describedby="basic-addon1">
							</div>
							<div style="margin:15px 0;">
							<h4>Action notified</h4>
							
							<label class="checkbox-inline ibk-checkbox-wrap">
								<?php $checked = ($meta_arr['ibk_email_sent_1']) ? 'checked' : '';?>
	  							<input type="checkbox" id="inlineCheckbox1" value="ibk_email_sent_1" onClick="ibk_check_and_h(this, '#ibk_email_sent_1');" <?php echo $checked;?> > Backup Finished
	  							<input type="hidden" value="<?php echo $meta_arr['ibk_email_sent_1'];?>" name="ibk_email_sent_1" id="ibk_email_sent_1" />
							</label>
							<label class="checkbox-inline ibk-checkbox-wrap">
								<?php $checked = ($meta_arr['ibk_email_sent_2']) ? 'checked' : '';?>
	  							<input type="checkbox" id="inlineCheckbox1" value="ibk_email_sent_2" onClick="ibk_check_and_h(this, '#ibk_email_sent_2');" <?php echo $checked;?> > Backup Started
	  							<input type="hidden" value="<?php echo $meta_arr['ibk_email_sent_2'];?>" name="ibk_email_sent_2" id="ibk_email_sent_2" />
							</label>
							<label class="checkbox-inline ibk-checkbox-wrap">
								<?php $checked = ($meta_arr['ibk_email_sent_3']) ? 'checked' : '';?>
	  							<input type="checkbox" id="inlineCheckbox1" value="ibk_email_sent_3" onClick="ibk_check_and_h(this, '#ibk_email_sent_3');" <?php echo $checked;?> > Error occuring
	  							<input type="hidden" value="<?php echo $meta_arr['ibk_email_sent_3'];?>" name="ibk_email_sent_3" id="ibk_email_sent_3" />
							</label>
							</div>
						</div>
					</div>
				</div>
				
				<div class="ibk-line-break"></div>
				
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-6">
							<h2>BackUp WorkFlow</h2>
							<p>Some settings may be adjusted for a better backup job or based on Server limitation</p>
							<h4 style="margin-top:25px;">Memory Limit</h4>
							<p>A bigger limit lets the BackUp system to works better and avoid crashing. The smallest allowed limit is 256M</p>
							<div class="input-group" style="  max-width: 400px;">
								<span class="input-group-addon" id="basic-addon1">Memory</span>
								 <input type="number" class="form-control" placeholder="set a bigger limit in M (megabytes)" name="ibk_memory_limit" value="<?php echo $meta_arr['ibk_memory_limit'];?>" min="256" aria-describedby="basic-addon1">
								 <div class="input-group-addon">M</div>
							</div>
							<h4 style="margin-top:25px;">DataBase Segmentations</h4>
							<p>For Bigger DataBase tables, to avoid a crashing Process, a segmentation is requires based on a amount of entries</p>
							<p>You have <?php echo $total_entries = ibk_get_total_entries();?> entries in Your Database, so we suggest to use at least <b><?php 
							echo ibk_segmentation_sugestion($total_entries);?></b> as Segmentation value.</p>
							<div class="input-group" style="  max-width: 400px;">
								<span class="input-group-addon" id="basic-addon1">Segmentation</span>
								 <input type="number" class="form-control" placeholder="by default is 100" min="1" name="ibk_db_segmentation" value="<?php echo $meta_arr['ibk_db_segmentation'];?>" aria-describedby="basic-addon1">
							</div>
						</div>
					</div>
				</div>
				
				
				<div class="ibk-line-break"></div>
				
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-6">
							<h2>Clean Up</h2>
							<p>To avoid server space to be overloaded, a clean up action is requested time to time.</p>
							<h4 style="margin-top:25px;">BackUp Logs</h4>
							<p>Erase Logs older than: <select id="ibk_older_than"><?php 
								foreach (array('-1 hours' => 'one hour', '-12 hours' => '12 hours', '-1 day' => 'one day', '-1 week'=>'one week', '-1 month' => 'one month') as $k=>$v){
									?>
										<option value="<?php echo $k;?>"><?php echo $v;?></option>
									<?php 
								}
							?></select></p>
							<button type="button" class="btn btn-danger" onClick="erase_backup_logs('<?php echo $url.'&tab=general_settings&delete_logs=true';?>');">Clean Up</button>
							<h4 style="margin-top:25px;">Temporary BackUp Files</h4>
							<p>Erase the BackUp temporary files that where kept there or leave it based on a unfinished Process.</p>
							<button type="button" class="btn btn-danger" onClick="window.location='<?php echo $url.'&tab=general_settings&delete_temp_files=true';?>'">Clean Up</button>
						</div>
					</div>
				</div>
				
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-3">
							<h2>Debug</h2>
							<p>Select how much debug info do you want to record.</p>
							<select name="ibk_global_debug_value" class="form-control m-bot15"><?php 
								$arr = array(1 => "Only main stuff", 2 => "Many actions", 3 => "Almost everything");
								foreach ($arr as $k=>$v){
									$selected = ($meta_arr['ibk_global_debug_value']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
									<?php 
								}
							?></select>
						 </div>
					</div>
				</div>	
				
				<div class="ibk-inside-item"> 
					<div class="row">
						 <div class="col-xs-3">
							<h2>Notification</h2>
							<p>Remind notifications after: </p>
							<select name="ibk_notification_time" class="form-control m-bot15"><?php 
								$arr = array(0=>'...' , 900 => "15 Minutes", 3600 => "One Hour", 86400 => "One Day", -1=>"Don't remind me");
								foreach ($arr as $k=>$v){
									$selected = ($meta_arr['ibk_notification_time']==$k) ? 'selected' : '';
									?>
									<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
									<?php 
								}
							?></select>
						 </div>
					</div>
				</div>								
				
				<input type="submit" value="Save" name="save-bttn" class="button button-primary button-large">
			</form>
		</div>			
	</div>
</div>		 
