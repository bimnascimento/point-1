<?php 
if (isset($_GET['subtab']) && $_GET['subtab']){
	$id = false;
	if (isset($_GET['id']) && $_GET['id']){
		$id = $_GET['id'];
	}
	$meta_arr = ibk_return_metas_from_custom_db('backups', $id);//func available in utilities.php
	
	$this->show_notification();
	?>
	<script>
		var ibk_wp_db_prefix = "<?php global $wpdb;echo $wpdb->prefix;?>";
	</script>
		<form action="<?php echo $url.'&tab=manage_backups';?>" method="post">
			<?php 
				if ($id){
					?>
					<input type="hidden" value="<?php echo $id;?>" name="id" />
					<?php 	
				}
			?>
			<div class="ibk-stuffbox" style="margin-top: 50px;">
				<h3 class="ibk-h3">Add/Edit Snapshot</h3>
				<div class="inside">
					<div class="ibk-inside-item"> 
						<div class="input-group input-group-lg">
  							<span class="input-group-addon" id="basic-addon1">Snapshot Name</span>
 							 <input type="text" class="form-control" placeholder="My Backup" name="name" value="<?php echo $meta_arr['name'];?>" aria-describedby="basic-addon1">
						</div>
					</div>
					
					<div class="ibk-line-break"></div>
					<div class="ibk-inside-item">
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group">
									<label class="control-label">Description :</label>
									<textarea name="description" class="form-control text-area" cols="30" rows="5" placeholder="Some details..."><?php echo $meta_arr['description'];?></textarea>
								</div>
							</div>
						</div>
					</div>
					
					<?php if (is_multisite()){?>
					<div class="ibk-inside-item ibk-multisite-wrapper"> 
						<h3>MultiSite WP detected</h3>
						<p>You can choose to Backup the entire MultiSite system or just one SingleSite</p>
						<div class="ibk-multi-switch-wrapper">
							<div class="ibk-option-text1"><h3>MultiSite Backup</h3></div>
							<div class="ibk-option-text2"><h3>SingleSite Backup</h3></div>
							
							<div class="ibk-multi-switch">
								<?php $checked = (empty($meta_arr['blog_id'])) ? "" : "checked";?>
								<input type="checkbox" id="control" class="control" <?php echo $checked;?> onClick="ibk_display_site_to_bk_selection(this);">
								<label for="control" class="checkbox"></label>
							</div>
							<?php $name = (empty($meta_arr['blog_id'])) ? "blog_id" : "";?>
							<input type="hidden" id="ibk_backup_all_sites" name="<?php echo $name;?>" value="0" />
						</div>
						
						<?php $display = (empty($meta_arr['blog_id'])) ? "none" : "block"; ?>
						<div style="display: <?php echo $display;?>" id="the_select_of_blog_id">
							<p>Select the desired SingleSite:</p>
							<div class="row">
								<div class="col-xs-4">
									<div class="form-group">
										<?php 
											$name = '';
											$disabled = 'disabled';
											if (!empty($meta_arr["blog_id"])){
												$name = "blog_id";
												$disabled = "";	
											}
										?>
										<select name="<?php echo $name;?>" <?php echo $name;?> class="form-control m-bot15" onChange="jQuery('#ibk_select_field_db_tables').val(-1);ibk_write_tag_value('#ibk_select_field_db_tables', '#save_db_table_list', '#ibk-database-list-tables', 'backup-t-items-');" id="ibk_backup_site_id" ><?php 
											$sites = ibk_blog_ids_list(TRUE);
											foreach ($sites as $k=>$v){
												$selected = '';
												if (isset($meta_arr["blog_id"]) && $meta_arr["blog_id"]==$k){
													$selected = 'selected';
												}
												?>
												<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
												<?php 	
											}
										?></select>
									</div>
								</div>
							</div>	
						</div>					
					</div>
					<?php } else { ?>
					<input type="hidden" name="blog_id" value="1" />
					<?php }?>
					
					<div class="ibk-line-break"></div>
					
					<div class="ibk-inside-item"> 
						<h3>Files to Backup</h3>
						<p>Select whicth files should be included into the Snapshot</p>
						<div class="btn-group" data-toggle="buttons" style="margin:10px 0 15px 0">
						<?php 
								$arr = array(
												'all'=>'All',
												'custom'=>'Custom',
												'none'=>'None',
											);
								foreach ($arr as $k=>$v){
									?>
										<label class="btn btn-primary btn-info <?php if ($meta_arr['save_files']==$k) echo 'active';?> ">
											<?php $checked = ($meta_arr['save_files']==$k) ? 'checked' : '';?>
											<input type="radio" name="save_files" <?php echo $checked;?> id="<?php echo $k;?>" value="<?php echo $k;?>"  onChange="indeed_select_show_div(this, 'custom', '#ibk-save_files-custom_option');"> <?php echo $v;?>
										</label>
									<?php 	
								}
							?>		
							
						</div>					
							
					
					<?php $display = ($meta_arr['save_files']=='custom') ? 'block' : 'none';?>
					<div id="ibk-save_files-custom_option" style="display: <?php echo $display;?>;">
							<?php 
									$arr = array(
													'themes' => 'Themes',
													'plugins' => 'Plugins',
													'uploads' => 'Media Files',
													'wp-config.php' => 'wp-config.php',	
												);
									foreach ($arr as $k=>$v){
										?>
										<label class="checkbox-inline ibk-checkbox-wrap"><input type="checkbox" onClick="ibk_make_inputh_string(this, '<?php echo $k;?>', '#save_files_list');" <?php if (strpos($meta_arr['save_files_list'], $k)!==FALSE ) echo 'checked';?>/><?php echo $v;?></label>
										<?php 	
									}
								?>
								<input type="hidden" value="<?php echo $meta_arr['save_files_list'];?>" name="save_files_list" id="save_files_list" />
					</div>	
					</div>
					<div class="ibk-inside-item">
						<div class="row">
							<div class="col-xs-4">
								<div class="form-group">
								<label class="control-label">Excluded Files:</label>
								<textarea name="excluded_files" class="form-control text-area" placeholder="exemple.php,image.png,style.css..."><?php echo $meta_arr['excluded_files'];?></textarea>
								</div>
							</div> 
						</div>	
					</div>	
					
					<div class="ibk-inside-item">
						<div class="row">
							<div class="col-xs-4">
								<div class="form-group">
								<label class="control-label">Excluded Folders:</label>
								<div style="font-size: 11px;"><?php echo WP_CONTENT_DIR;?></div>
								<textarea name="excluded_folders" class="form-control text-area" placeholder="<?php echo '/themes/exemple_1,/plugins/akismet,/uploads/images-2015';?>"><?php echo $meta_arr['excluded_folders'];?></textarea>
								</div>
							</div> 
							<div class="clear"></div>
							<div style="font-size: 11px; padding: 0px 5px; color: #333; margin-left: 10px;">
								 <div>Ex.: If You want to exclude the 'indeed-wp-superbackup' folder that has the full path : <?php echo WP_CONTENT_DIR . '/plugins/'. '<b>indeed-wp-superbackup/</b>';?>, You only have to add : '/plugins/indeed-wp-superbackup' ! </div>
								 <div>Values separated by comma.</div>
							</div>
						</div>	
					</div>					
					
					
					<div class="ibk-line-break"></div>
					
					<div class="ibk-inside-item"> 
						<h3>DataBase to Backup</h3>
						<p>Pick Up all the Tables or just some of them and exclude those that are not necessary to be included into this Snapshot</p>
						<div class="row">
							<div class="col-xs-4">
								<div class="form-group">
								<label class="control-label">Tables</label>
								<select class="form-control m-bot15" id="ibk_select_field_db_tables" onChange="ibk_write_tag_value(this, '#save_db_table_list', '#ibk-database-list-tables', 'backup-t-items-');">
									<option value="0">...</option>
									<option value="-1">None</option>
									<?php $selected = ($meta_arr['save_db_table_list']===FALSE) ? 'selected' : '';?>									
									<option value="all" <?php echo $selected;?> >All Tables</option>
									<option value="wp">+ all WP Native Tables</option>
									<option value="non_wp">+ all Non-WP Tables</option>
								</select>
								<?php 
									if ($meta_arr['save_db_table_list']===FALSE){
										$meta_arr['save_db_table_list'] = ibk_get_table_list();		
										$meta_arr['save_db_table_list'] = implode(',', array_keys($meta_arr['save_db_table_list']) );
									}
								?>
								<input type="hidden" id="save_db_table_list" name="save_db_table_list" value="<?php echo $meta_arr['save_db_table_list'];?>" />
								</div>
							</div> 
							</div>
							<div id="ibk-database-list-tables"><?php 
							if ($meta_arr['save_db_table_list']){
								$table_names = ibk_get_table_list();
								if (is_multisite() && !empty($meta_arr["blog_id"]) ){
									$table_names = ibk_only_tables_for_blog_id(ibk_get_table_list(), $meta_arr["blog_id"]);
								}
								$items = explode(',', $meta_arr['save_db_table_list']);
								foreach ($items as $v){	
									if (!empty($meta_arr["blog_id"])){
										$is_native = ibk_is_native($v, $meta_arr['blog_id']);
									} else {
										$is_native = ibk_is_native($v);
									}
									$class = ($is_native) ? "ibk-tag-item-native" : "ibk-tag-item";																
									?>
										<div id="<?php echo "backup-t-items-" . $v;?>" class="<?php echo $class;?>"><?php
										if (!empty($table_names[$v])) {
											echo $table_names[$v];
										} else {
											echo $v;
										}
										?><div class="ibk-remove-tag" onclick="ibk_remove_db_tag('<?php echo $v;?>', '#backup-t-items-', '#save_db_table_list');" title="Removing tag">x</div>
										</div>									
									<?php 
								}
							}
						?></div>	
					</div>	
					
					<div class="ibk-line-break"></div>
					
					<div class="ibk-inside-item"> 
						<h3>When to BackUp</h3>
						<p>The Snapshot can run instantly, on a specific date or periodically.</p>
						<div class="btn-group" data-toggle="buttons" style="margin:10px 0 15px 0">
						<?php 
								$arr = array(
												'0' => 'Right Now',
												'-1' => 'Scheduled',
												'1' => 'Periodically',
											);
								foreach ($arr as $k=>$v){
									?>
										<label class="btn btn-primary btn-info <?php if ($meta_arr['backup_interval_type']==$k) echo 'active';?> ">
											<?php $checked = ($meta_arr['backup_interval_type']==$k) ? 'checked' : '';?>
											<input type="radio" name="backup_interval_type" id="<?php echo $k;?>" value="<?php echo $k;?>" <?php echo $checked;?>  onChange="ibk_backup_interval(this.value);" > <?php echo $v;?>
										</label>
									<?php 	
								}
							?>		
							
						</div>		
					</div>
					<?php $display = ($meta_arr['backup_interval_type']==-1) ? 'block' : 'none';?>
					<div class="ibk-inside-item" id="cron-specified_date" style="display: <?php echo $display;?>;" >
						<div class="row">
							<div class="col-xs-3">
								<div class="input-group">
  									<span class="input-group-addon" id="basic-addon1">Date</span>
 							 		<input type="text" class="form-control" placeholder="pick a date" id="specified_date" name="cron-specified_date" value="<?php echo $meta_arr['specified_date'];?>" aria-describedby="basic-addon1">
								</div>		
							</div>
						</div>	
					</div>
					<?php $display = ($meta_arr['backup_interval_type']==1) ? 'block' : 'none';?>
					<div class="ibk-inside-item" id="cron-periodically" style="display: <?php echo $display;?>">
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">
									<select name="cron-periodically" class="form-control m-bot15" >
										<?php 
											$arr = array(
															'0.25' => 'Every 15 minutes',
															'1' => 'On every hour',
															'12' => 'On every 12 hours',
															'24' => 'Once a day',	
															'168' => 'Once a week',
															'720' => 'Once a month',
														);
											foreach ($arr as $k=>$v){
												?>
												<option value="<?php echo $k;?>" <?php if ($meta_arr['cron-periodically']==$k) echo 'selected';?> ><?php echo $v;?></option>
												<?php 	
											}
										?>
									</select>		
								</div>
							</div>
						</div>			
					</div>
					
					<div class="ibk-line-break"></div>
					
					<div class="ibk-inside-item"> 
						<h4>History Versions</h4>
						<div class="row">
							<div class="col-xs-3">
								<div class="input-group">
									<span class="input-group-addon" id="basic-addon1">Max</span>
									<input type="number" class="form-control" min="1" name="max_archives" value="<?php echo $meta_arr['max_archives'];?>" />
								</div>		
							</div>
						</div>	
					</div>		
					
					<div class="ibk-line-break"></div>
										
					<div class="ibk-inside-item"> 
					<h3>Snapshot Destination</h3>
						<p>Select one of your preset Destination</p>
						<div class="row">
							<div class="col-xs-3">
								<div class="form-group">
									<?php
									$data = $this->ibk_get_items_list('destinations', 'ASC', 0);
									if ($data){?>
									<select class="form-control m-bot15" name="destination"><?php 
										unset($arr);
										foreach ($data as $obj){
											$selected = ($meta_arr['destination']==$obj->id) ? 'selected' : '';
											if ($obj->type){											
												$arr[$obj->type][] = '<option value="' . $obj->id .'" ' . $selected . ' >' . $obj->name . '</option>'; 	
											}
										}
										$str = '';
										foreach ($arr as $type=>$string_arr){
											if ($type=='copy'){
												$type = 'Copy.com';
											}
											$str .= '<optgroup label="' . $type . '">';
												foreach ($string_arr as $string){
													$str .= $string;	
												}
											$str .= '</optgroup>';
										}
										echo $str;
									?></select>								
									<?php } else {?>
										No destinations available. <a href="<?php echo $url.'&tab=destinations&subtab=edit_create'?>">Add your first Destination!</a>
									<?php } ?>
								</div>		
							</div>
						</div>
						
					</div>	
					
					<div class="ibk-line-break"></div>
					
					<div class="ibk-inside-item"> 
					<h3>Snapshot Color</h3>
						<div>
							<?php 
								$this->ibk_get_colors_for_admin_boxes($meta_arr['admin_box_color']);//print the select color for box 
							?>
						</div>
					</div>					
					<div class="ibk-line-break"></div>
					<div class="ibk-bttn-wrapp"> 
						<?php
							//$bttn = "Create";
							//if ($_GET['subtab']=='edit') $bttn = "Update";
						?>
						 <input type="submit" value="Save" name="save-bttn" class="button button-primary button-large"/>
						 <input type="submit" value="Save and Run" style="margin-left: 10px;display: <?php if ($meta_arr['backup_interval_type']==0) echo 'inline-block'; else echo 'none';?>" id="bttn_save_and_run" name="save-and-run-bttn" class="button button-primary button-large"/>
						 <input type="button" style="margin-left: 10px;" value="Cancel" class="button button-primary button-large" onClick="window.location='<?php echo $url . '&tab=manage_backups';?>';" />
					</div>	
																																		
				</div>
			</div>		
		</form>
	<?php 	
} else {
	/***************************  LISTING  *************************/
	$this->show_notification();
	?>
		<div>
			<a href="<?php echo $url.'&tab=manage_backups&subtab=add_new'?>" class="ibk-add-new"><i title="" class="fa-ibk fa-add-backup-ibk"></i><span>Add SnapShot</span></a>
			<span class="ibk-top-message">...create your Snapshot and personalize it!</span>
		</div>
		<div class="ibk-backup-items-wrap">
	<?php 
	/************* create/edit ************/
	if (isset($_POST['save-bttn'])){
 		$this->ibk_save_update_backup_item($_POST, FALSE);	
	} else if (isset($_POST['save-and-run-bttn'])){
 		$this->ibk_save_update_backup_item($_POST);	
	}
	
	/************** LIST *****************/
	$data = $this->ibk_get_items_list('backup');
	if ($data ){
		foreach ($data as $obj){
			$meta = ibk_return_metas_from_custom_db('backups',$obj->id);//func available in utilities.php
			$this->ibk_create_admin_backup_box($obj->id, $meta, $url);
		}	
	}else{ ?>
		<div class="ibk-nodata-wrapper">
			<img src="<?php echo IBK_URL;?>admin/assets/images/nosnapshots.png"/>
			<a href="<?php echo $url.'&tab=manage_backups&subtab=add_new'?>" class="ibk-add-new"><i title="" class="fa-ibk fa-add-backup-ibk"></i><span>Add SnapShot</span></a>
		</div>
	<?php } ?>
		<div class="clear"></div>
		</div>
	<?php 
}

?>
