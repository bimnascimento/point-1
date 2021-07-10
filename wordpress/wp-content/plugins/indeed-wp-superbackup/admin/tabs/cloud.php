<div class="ibk-subtab-menu">
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=cloud&destinations=true';?>"> Cloud Destinations</a>
	<a class="ibk-subtab-menu-item" href="<?php echo $url.'&tab=cloud&subtab=list_snapshots';?>">Cloud Snapshots</a>
</div>
<div class="ibk-dashboard-wrap">
<?php 
$this->show_notification();
?>
 <div class="ibk-backup-items-wrap ibk-restore-list-wrap" style="margin-top: 0px;">
    <h2>Connected Snapshots</h2>
	<p>Based on your Cloud Destinations sets to provides connected snapshots.</p>
		<?php 
		$data = $this->ibk_get_items_list('destinations', 'DESC', 1);
		if (count($data) && is_array($data)){
			//let's search into clound to get some snapshots
			foreach ($data as $destination){
				$cloud_results = $this->get_clound_snapshots($destination->id);
				$this->create_cloud_restore_box($cloud_results, $destination->id);
			}
		} else {?>
					<div class="ibk-nodata-wrapper">
						<img src="<?php echo IBK_URL;?>admin/assets/images/noclouds.png"/>
						<a href="<?php echo $url.'&tab=destinations&subtab=edit_create&status=1';?>"  class="ibk-add-new"><i title="" class="fa-ibk fa-add-backup-ibk"></i><span>Add Cloud Destination</span></a>
					</div>
		<?php } ?>
	<div class="clear"></div>
</div>
 
<div id="snapshot_list_versions"></div>
 
</div>
