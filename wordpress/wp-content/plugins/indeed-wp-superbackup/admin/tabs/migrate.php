<script>
	var ibk_wp_db_prefix = "<?php global $wpdb;echo $wpdb->prefix;?>";
</script>
<?php 
$this->show_notification();
?>

<div class="ibk-dashboard-wrap">
<div class="ibk-migrate-box-wrap">
 	<form action="" enctype="multipart/form-data" method="post" id="ibk_migrate_form">
 		<input type="hidden" value="1" name="ibk_restore_migrate_action" />
		<h1>Migrate Snapshot</h1>
		<p>Straight Migrate based on File URL or File Upload</p>
	 	<div class="btn-group" data-toggle="buttons" style="margin:10px 0 15px 0">
			<label class="btn btn-primary btn-info active">
				<input type="radio" name="restore_type"  value="restore_url" onChange="jQuery('#restore_from_file_upload').fadeOut(200, function(){jQuery('#restore_from_url').css('display', 'block');});" > Direct URL
			</label>
			<label class="btn btn-primary btn-info ">
				<input type="radio" name="restore_type"  value="restore_file" onChange="jQuery('#restore_from_url').fadeOut(200, function(){jQuery('#restore_from_file_upload').css('display', 'block');});" > File Upload
			</label>
		</div>
		<div id="restore_from_url">
			<div class="ibk-inside-item"> 
				<h4>History Versions</h4>
				<p>Select the desired version of your Saved Snapshot based on the Timestamp included into the file name.</p>
				<div class="row">
					<div class="col-xs-8">
						<div class="input-group input-group-lg">
							<span class="input-group-addon" id="basic-addon1">URL</span>
							<input type="text" class="form-control" name="migrate_url" />
						</div>		
					</div>
				</div>	
			</div>	
		</div>
		<div id="restore_from_file_upload" style="display:none;">
			<div class="ibk-inside-item"> 
				<h4>History Versions</h4>
				<p>Select the desired version of your Saved Snapshot based on the Timestamp included into the file name.</p>
				<div class="row">
					<div class="col-xs-8">
						<div class="input-group input-group-lg" style="display: block;">
							<input id="file-0a" class="file" type="file" data-show-preview="false" name="upload_file">
						</div>		
					</div>
				</div>	
			</div>	
		</div>
		<div style="margin-top:20px;margin-bottom: 20px;">
			<div class="ibk-inside-item"> 
				<h4>Verified Snapshot!</h4>
				<p style="font-weight:bold;">Be sure that you select a Backup made with WP SuperBackup system and no altered zip file!</p>
			</div>
		</div>
		<?php $this->ibk_clound_migrate_msg();?>
		<div class="ibk-inside-item"  style="margin-top:40px;"> 
			<h2>Customized Migration</h2>
			<p>Set whatever needs to be migrated or not based on BackUp assigned. Be sure that your BackUp has the desired section included.</p>
			<h3 style="margin-top:35px;">Files to Migrate</h3>
			<p>Select whicth files should be Migrated</p>
			<div class="btn-group" data-toggle="buttons" style="margin:10px 0 15px 0">
				<?php 
						$arr = array(
										'all'=>'All',
										'custom'=>'Custom',
										'none'=>'None',
									);
						foreach ($arr as $k=>$v){
							?>
								<label class="btn btn-primary btn-info <?php if ('all'==$k) echo 'active';?> ">
									<input type="radio" name="save_files" <?php if('all'==$k)echo 'checked';?> id="<?php echo $k;?>" value="<?php echo $k;?>"  onChange="indeed_select_show_div(this, 'custom', '#ibk-save_files-custom_option');"> <?php echo $v;?>
								</label>
							<?php 	
						}
					?>				
			</div>						
			<div id="ibk-save_files-custom_option" style="display: none;">
					<?php 
							$arr = array(
											'themes' => 'Themes',
											'plugins' => 'Plugins',
											'uploads' => 'Media Files'
										);
							foreach ($arr as $k=>$v){
								?>
								<label class="checkbox-inline ibk-checkbox-wrap"><input type="checkbox" onClick="ibk_make_inputh_string(this, '<?php echo $k;?>', '#save_files_list');" /><?php echo $v;?></label>
								<?php 	
							}
						?>
					<input type="hidden" value="themes,plugins,uploads" name="files_to_restore" id="save_files_list" />
			</div>	
		</div>
		<div class="ibk-line-break"></div>
		<div class="ibk-inside-item"> 
			<h3>DataBase to Migrate</h3>
			<p>Pick Up all the Tables or just some of them and exclude those that are not necessary to be Migrated</p>
			<div class="row">
				<div class="col-xs-4">
					<div class="form-group">
					<label class="control-label">Tables</label>
					<select class="form-control m-bot15" onChange="ibk_write_tag_value_migrate(this, '#migrate_wp_table_list', '#ibk-database-list-tables', 'backup-t-items-');">
						<option value="0">...</option>
						<option value="-1">None</option>
						<option value="all" selected>All Tables</option>
						<option value="wp">+ all WP Native Tables</option>
						<option value="non_wp">+ all Non-WP Tables</option>
					</select>
					<?php 
						$tables = ibk_get_table_list('wp');
						$tables_to_restore = implode(',',array_keys($tables));
					?>
					<input type="hidden" id="migrate_wp_table_list" name="migrate_wp_table_list" value="<?php echo $tables_to_restore;?>" />
					<input type="hidden" id="migrate_non_wp_tables" name="migrate_non_wp_tables" value="1" />
					</div>
				</div> 
				</div>
				<div id="ibk-database-list-tables"><?php 
				if ($tables){
					foreach ($tables as $k=>$v){
						?>
							<div id="<?php echo "backup-t-items-" . $k;?>" class="ibk-tag-item"><?php
							echo $v;
							?><div class="ibk-remove-tag" onclick="ibk_remove_db_tag('<?php echo $k;?>', '#backup-t-items-', '#save_db_table_list');" title="Removing tag">x</div>
							</div>									
						<?php 
					}
				}
			?>
				<div id="ibk_migrate_all_non_wp" class="ibk_migrate_all_non_wp">All Non Wp<div class="ibk-remove-tag" onclick="ibk_remove_all_none_wp_Table_opt();" title="Removing tag">x</div></div>
			</div>	
		</div>
		<div class="ibk-line-break"></div>
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
						siteurl (from 'sitemeta' database table)
					</div>							
												
				</div>
				<?php 
			}
		?>
		
		<div class="ibk-restore-buttons-wrap ibk-migrate-buttons-wrap">
			<span class="ibk-add-new" id="submit_the_form" onClick='jQuery( "#ibk_migrate_form" ).submit();'>
				<i title="" class="fa-ibk fa-migrate-btn-ibk"></i>
				<span>Migrate</span>
			</span>
		</div> 
 	</form>
</div>
 
 
<div id="snapshot_list_versions"></div>
 
</div>

<?php 
