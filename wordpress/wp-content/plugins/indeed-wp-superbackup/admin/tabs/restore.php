<?php 
$this->show_notification();
?>
<div class="ibk-dashboard-wrap">
 <div class="ibk-restore-box-wrap">
 	<form action="" enctype="multipart/form-data" method="post" id="ibk_restore_form">
 		<input type="hidden" value="1" name="ibk_restore_migrate_action" />
		<h2>Direct Restore</h2>
		<p>Straight Restore based on File URL or File Upload</p>
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
				<p>Add the URL file with the desired Snapshot Version. You will not be able to select between versions later.</p>
				<div class="row">
					<div class="col-xs-8">
						<div class="input-group input-group-lg">
							<span class="input-group-addon" id="basic-addon1">URL</span>
							<input type="text" class="form-control" name="restore_url" />
						</div>		
					</div>
				</div>	
			</div>	
		</div>
		<div id="restore_from_file_upload" style="display:none;">
			<div class="ibk-inside-item"> 
				<h4>History Versions</h4>
				<p>Upload the file with the desired Snapshot Version. You will not be able to select between versions later.</p>
				<div class="row">
					<div class="col-xs-8">
						<div class="input-group input-group-lg" style="display: block;">
							<input id="file-0a" class="file" type="file" data-show-preview="false" name="upload_file" >
							<!--  input type="file" class="form-control" name="upload_file" / -->
						</div>		
					</div>
				</div>	
			</div>	
		</div>
		<div class="ibk-restore-buttons-wrap">
			<span class="ibk-add-new" id="submit_the_form" onClick='jQuery( "#ibk_restore_form" ).submit();'>
				<i title="" class="fa-ibk fa-restore-btn-ibk"></i>
				<span>Restore</span>
			</span>
		</div> 
 	</form>
 </div>
 
 <div class="ibk-backup-items-wrap ibk-restore-list-wrap" style="margin-top: 0px;">
    <h2>Stored Snapshots</h2>
	<p>Restore directly from your Destinations the stored Snapshots.</p>
		<?php 
		$data = $this->ibk_get_items_list('backup');
		if ($data){
			foreach ($data as $obj){
				$meta = ibk_return_metas_from_custom_db('backups',$obj->id);//func available in utilities.php
				$instances = $this->ibk_get_list_all_snapshot_instances($obj->id, $meta['destination']);
				if ($instances){
					$this->ibk_restore_snapshot_box($obj->id, $meta);
				}				
			}
		}
		?>
	<div class="clear"></div>
</div>
 
 <div id="snapshot_list_versions"></div>

</div>
<?php 



