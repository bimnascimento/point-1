<?php 
if (isset($_GET['subtab']) && $_GET['subtab']){
	$id = false;
	if (!empty($_GET['id'])){		
		$id = $_GET['id'];
		$meta_arr = ibk_return_metas_from_custom_db('destinations', $id, FALSE, $status);//func available in utilities.php
		$status = $meta_arr['status'];
	} else {
		if (isset($_GET['status'])){
			$status = $_GET['status'];
		}
		$id = $this->ibk_get_destination_next_id();
		$meta_arr = ibk_return_metas_from_custom_db('destinations', FALSE, FALSE, $status);// no metas available for next id
	}

	$this->show_notification();
	?>
	
	<form action="" method="post">
		<?php $is_edit = (empty($_GET['id'])) ? 0 : 1; ?>
		<input type="hidden" value="<?php echo $is_edit;?>" name="is_edit" id="ibk_destination-is_edit"/>
		<input type="hidden" value="<?php echo $id;?>" name="ibk-id" id="ibk_destination-id"/>
		<input type="hidden" value="<?php echo $meta_arr['connected'];?>" name="connected" id="ibk_connected"/>
		<div class="ibk-stuffbox" style="margin-top: 50px;">
			<h3 class="ibk-h3">Add/Edit Destination</h3>
			<div class="inside">
				<div class="ibk-inside-item"> 
						<div class="input-group input-group-lg">
  							<span class="input-group-addon" id="basic-addon1">Destination Name</span>
 							 <input type="text" class="form-control" placeholder="My Destination" name="name" value="<?php echo $meta_arr['name'];?>" id="ibk_destination-name" aria-describedby="basic-addon1">
						</div>
					</div>
				<div class="ibk-inside-item"> 
					<h4>Destination Color</h4>
					<div>
						<?php 
							$this->ibk_get_colors_for_admin_boxes($meta_arr['admin_box_color']);//print the select color for box 
						?>
					</div>
				</div>	
				

				<div class="ibk-inside-item"> 
				<h3>Destination Type</h3>
						<p>Choose one of the Destinations available. Be sure that all the settings and credentials are made properly.</p>
						<div class="row">
							<div class="col-xs-12">
								<?php 
									if ($status){
										//cloud destinations
										$arr = array(
												'ftp' => 'FTP',
												'google' => 'Google Drive',
												'dropbox' => 'DropBox',
												'amazon' => 'Amazon S3',
												'onedrive' => 'OneDrive',
												'copy' => 'Copy.com',
										);										
									} else {
										$arr = array(
												'local' => 'Local',
												'ftp' => 'FTP',
												'google' => 'Google Drive',
												'dropbox' => 'DropBox',
												'amazon' => 'Amazon S3',
												'rackspace' => 'RackSpace',
												'onedrive' => 'OneDrive',
												'copy' => 'Copy.com',
										);										
									}
								?>
								<div class="btn-group" data-toggle="buttons" style="margin:10px 0 15px 0">
									<?php foreach ($arr as $k=>$v){ ?>
										<label class="btn btn-primary btn-info <?php if ($k==$meta_arr['type'])echo 'active';?> ">		
											<?php $checked = ($k==$meta_arr['type']) ? 'checked' : '';?>	
											<input type="radio" name="type"  id="" value="<?php echo $k;?>" <?php echo $checked;?> onChange="ibk_show_destination_type(this.value);"> <?php echo $v;?>
										</label>
									<?php } ?>	
								</div>
							</div>
						</div>	
											
				</div>
			<!-- LOCAL STUFF HERE -->
				<?php $display = ($meta_arr['type']=='local') ? 'block' : 'none';?>
				<div id="ibk-local" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>Local Settings</h3>
						<p>Set the full path of your desire destination folder. Be sure that the selected Folder all the Write Permissions</p>			
							<div class="ibk-inside-item"> 
							<div class="row">
								<div class="col-xs-6">
									<div class="input-group">
										<span class="input-group-addon" id="basic-addon1">Target Folder:</span>										
										<?php 
										if (empty($meta_arr['local_folder_target'])){
											$meta_arr['local_folder_target'] = str_replace('plugins/indeed-wp-superbackup', 'uploads/indeed-wp-superbackup', IBK_PATH);
										}								
										?>
										<input type="text"  class="form-control" name="local_folder_target" value="<?php echo $meta_arr['local_folder_target'];?>" id="local_folder_target"/>
									</div>		
								</div>
							</div>
						</div>				
				</div>	
				
			<!-- AMAZON STUFF HERE -->						
				<?php $display = ($meta_arr['type']=='amazon') ? 'block' : 'none';?>
				<div id="ibk-amazon" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>Amazon S3 Settings</h3>
						<p>Connect your Amazon S3 account to your WP SuperBackup Instance.</p>							
						<div class="ibk-inside-item"> 
							<div class="row">
								<div class="col-xs-7">
								   <div class="input-group">
										<?php if (!isset($meta_arr['aws_key'])) $meta_arr['aws_key'] = '';?>
										<span class="input-group-addon">AWS Access Key:</span> 
										<input class="form-control" type="text" value="<?php echo $meta_arr['aws_key'];?>" name="aws_key" id="aws_key" />
								   </div>	
								   <br/>								   
								   <div class="input-group">
										<?php if (!isset($meta_arr['aws_secret_key'])) $meta_arr['aws_secret_key'] = '';?>
										<span class="input-group-addon">AWS Secret Key:</span> 
										<input class="form-control" type="text" value="<?php echo $meta_arr['aws_secret_key'];?>" name="aws_secret_key" id="aws_secret_key" />
								   </div>
								   <br/>
								   <div class="form-group"> 
								   		<?php 
								   			if (empty($meta_arr['aws_ssl'])) $meta_arr['aws_ssl'] = 0;
								   			$checked = ($meta_arr['aws_ssl']) ? 'checked' : '';
								   		?>
								   		<div style="display: inline-block;vertical-align: top;">
								   			<input type="checkbox" <?php echo $checked;?> onClick="ibk_check_and_h(this, '#aws_ssl');" style="margin: 0px !important;"/>
								   			<input type="hidden" value="<?php echo $meta_arr['aws_ssl'];?>" name="aws_ssl" id="aws_ssl" />
								   		</div>			   		
								   		<label>Use SSL Connection </label>
								   </div>
								   <br/>
								   <div class="form-group"> 
								   		<label>AWS Region: </label>
								   		<select id="aws_region" class="form-control">
									   		<?php 
									   			if (empty($meta_arr['aws_region'])) $meta_arr['aws_region'] = '';
									   			$arr = array(					
											   					's3.amazonaws.com' => 'US Standard (s3.amazonaws.com)',
											   					's3-us-west-2.amazonaws.com' => 'US West (Oregon) Region (s3-us-west-2.amazonaws.com)',
											   					's3-us-west-1.amazonaws.com' => 'US West (Northern California) Region (s3-us-west-1.amazonaws.com)',
											   					's3-eu-west-1.amazonaws.com' => 'EU (Ireland) Region (s3-eu-west-1.amazonaws.com)',
											   					's3-ap-southeast-1.amazonaws.com' => 'Asia Pacific (Singapore) Region (s3-ap-southeast-1.amazonaws.com)',
											   					's3-ap-southeast-2.amazonaws.com' => 'Asia Pacific (Sydney) Region (s3-ap-southeast-2.amazonaws.com)', 
											   					's3-ap-northeast-1.amazonaws.com' => 'Asia Pacific (Tokyo) Region (s3-ap-northeast-1.amazonaws.com)', 
											   					's3-sa-east-1.amazonaws.com' => 'South America (Sao Paulo) Region (s3-sa-east-1.amazonaws.com)', 
											   					'other' => 'other',
									   					);
									   			foreach ($arr as $k=>$v){
									   				$selected = ($meta_arr['aws_region']==$k) ? 'selected' : '';
									   				?>
									   				<option value="<?php echo $k;?>" <?php echo $selected;?> ><?php echo $v;?></option>
									   				<?php 	
									   			}
									   		?>								   		
								   		</select>
								   </div> 	
								   <br/>
								   <div class="input-group">
										<?php if (!isset($meta_arr['aws_bucket'])) $meta_arr['aws_bucket'] = '';?>
										<span class="input-group-addon">AWS Bucket Name:</span> 
										<input class="form-control" type="text" value="<?php echo $meta_arr['aws_bucket'];?>" name="aws_bucket" id="aws_bucket" />
								   </div>	
								   <div style="margin: 20px 0px 0px 0px;">
								   		<label>Optional Destination Folder</label>								   
									   <div class="input-group">
									   		<?php if (!isset($meta_arr['subfolder'])) $meta_arr['subfolder'] = '';?>
											<span class="input-group-addon" id="basic-addon1">Name:</span> <input type="text" class="form-control" value="<?php echo $meta_arr['subfolder'];?>" name="subfolder" id="amazon_subfolder" />
									   </div>	
								   </div>								   				   							
								</div>								
							</div>
							
						</div>
				</div>
				
												
			<!-- GOOGLE STUFF HERE -->
				<?php $display = ($meta_arr['type']=='google')?'block':'none';?>
				<div id="ibk-google" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>Google Settings</h3>
						<p>Set your Google Drive App that will provide the way to connect WP SuperBackup to your Google Drive account.</p>	
							<div class="ibk-inside-item"> 
							  <div class="row">
							   <div class="col-xs-5">
								   <div class="input-group">
									<?php if (!isset($meta_arr['client_id'])) $meta_arr['client_id'] = '';?>
									<span class="input-group-addon" id="basic-addon1">Client ID:</span> <input class="form-control" type="text" value="<?php echo $meta_arr['client_id'];?>" name="client_id" id="client_id" />
								   </div>
								   <br/>
								   <div class="input-group">
								   <?php if (!isset($meta_arr['client_secret'])) $meta_arr['client_secret'] = '';?>
									<span class="input-group-addon" id="basic-addon1">Client Secret:</span> <input type="text" class="form-control" value="<?php echo $meta_arr['client_secret'];?>" name="client_secret" id="client_secret" />
								   </div>
								   <br/>
								   <div style="margin: 10px 0px 15px 0px;">
								   		<label>Optional Destination Folder</label>
								   
									   <div class="input-group">
									   		<?php if (!isset($meta_arr['folder_id'])) $meta_arr['folder_id'] = '';?>
											<span class="input-group-addon" id="basic-addon1">Folder ID:</span> <input type="text" class="form-control" value="<?php echo $meta_arr['folder_id'];?>" name="folder_id" id="folder_id" />
									   </div>								   
								   		<div style="font-size: 10px; color: #444;">
								   			In order to get the 'Folder ID' you must go to <a href="https://drive.google.com/drive/my-drive" target="_blank">Your Google Drive Account</a>.<br/>
											 Right Click on Your destination directory and select 'Get Link'.<br/>
											 A PopUp will show up with a URL like this: https://drive.google.com/open?id=0BwFtiEtPOrxfghdSWDhHOGdza34<br/>
											 In this case Your Folder ID will be : 0BwFtiEtPOrxfghdSWDhHOGdza34
										</div>
									</div>
								   <div class="form-group">
									   	<?php									
											if (empty($meta_arr['redirect_uri'])){
												$meta_arr['redirect_uri'] = $url . '&tab=destinations&id='.$id;
											}
										?>
										<label>Redirect URI:</label> <div style="color:#0a9fd8;"><?php echo $meta_arr['redirect_uri'];?></div> 
										<input type="hidden" value="<?php echo $meta_arr['redirect_uri'];?>" name="redirect_uri" id="redirect_uri" />
										<input type="hidden" value="<?php echo str_replace('"', "&quot;", $meta_arr['access_token']);?>" name="access_token" id="access_token" />
										<input type="hidden" value="<?php echo $meta_arr['refresh_token'];?>" name="refresh_token" id="refresh_token" />				
								   </div>						  
							   </div>
							   	   <div class="clear"></div>
								   <div style="font-size: 12px; color: #444;margin-left: 15px">
								   		Google Drive provides 15 gb of free storage space. Be sure your snapshots does't reach this limit.
								   </div>
							  </div>
							</div>
					<div class="ibk-line-break"></div>
				</div>
			<!-- FTP STUFF HERE -->		
				<?php $display = ($meta_arr['type']=='ftp')?'block':'none';?>
				<div id="ibk-ftp" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>FTP Settings</h3>
						<p>Your local server or any other server or available space with a FTP connection</p>
							<div class="ibk-inside-item">
							 <div class="row">
							   <div class="col-xs-5"> 
							   <div class="form-group">
								<?php if (empty($meta_arr['server_address'])) $meta_arr['server_address'] = '';?>
								<label>Server Address:</label>
								<input type="text" class="form-control" value="<?php echo $meta_arr['server_address'];?>" name="server_address" id="ftp-server_address" />
							   	</div>
								<div class="form-group">
								<?php if (empty($meta_arr['username'])) $meta_arr['username'] = '';?>
								<label>Username:</label>
								<input type="text"  class="form-control" value="<?php echo $meta_arr['username'];?>" name="username" id="ftp-username" />
								</div>		
							<div class="form-group">
								<?php if (empty($meta_arr['password'])) $meta_arr['password'] = '';?>
								<label>Password:</label>
								<input type="password" class="form-control" value="<?php echo $meta_arr['password'];?>" name="password" id="ftp-password" />
							</div>		
							<div class="form-group"> 
								<?php if (empty($meta_arr['directory'])) $meta_arr['directory'] = '';?>
								<label>Remote Path:</label>
								<input type="text" class="form-control" value="<?php echo $meta_arr['directory'];?>" name="directory" id="ftp-directory" />
							</div>
							<div class="form-group">
								<?php if (empty($meta_arr['protocol'])) $meta_arr['protocol'] = 'ftp';?>
								<label>Protocol:</label>
								<select name="protocol" class="form-control" id="ftp-protocol">
									<?php 
										$arr = array('ftp'=>'FTP', 'sftp'=>'SFTP');
										foreach ($arr as $k=>$v){
											$selected = ($meta_arr['protocol']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
											<?php 	
										}
									?>
								</select>
							</div>
							<div class="form-group"> 
								<?php if (empty($meta_arr['server_port'])) $meta_arr['server_port'] = '';?>
								<label>Server Port:</label>
								<input type="text" class="form-control" value="<?php echo $meta_arr['server_port'];?>" name="server_port" id="ftp-server_port" />
							</div>	
							<div class="form-group">
								<?php if (empty($meta_arr['server_timeout'])) $meta_arr['server_timeout'] = '';?>
								<label>Server Timeout:</label>
								<input type="text" class="form-control" value="<?php echo $meta_arr['server_timeout'];?>" name="server_timeout" id="ftp-server_timeout" />
							</div>		
							<div class="form-group"> 
								<?php if (empty($meta_arr['passive_mode'])) $meta_arr['passive_mode'] = 0;?>
								<label>Passive Mode:</label>
								<select class="form-control" name="passive_mode" id="ftp-passive_mode">
									<?php 
										$arr = array(0=>'No', 1=>'Yes');
										foreach ($arr as $k=>$v){
											$selected = ($meta_arr['passive_mode']==$k) ? 'selected' : '';
											?>
												<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
											<?php 	
										}
									?>								
								</select>
							</div>
							</div>
						   </div>  	
						  </div>
				</div>
			<!-- DROPBOX STUFF HERE -->	
				<?php $display = ($meta_arr['type']=='dropbox')?'block':'none';?>
				<div id="ibk-dropbox" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>DropBox</h3>
						<p>Connect your DropBox account to your WP SuperBackup Instance.</p>	
							<div class="ibk-inside-item">
							 <div class="row">
							   <div class="col-xs-5"> 		
							   	<div class="form-group">
									<?php if (empty($meta_arr['path'])) $meta_arr['path'] = '';?>
									<label>Optional Destination Folder (full path):</label>
									<input type="text"  class="form-control" value="<?php echo $meta_arr['path'];?>" name="path" id="dropbox_path" />
								</div>						   				   								   
							   </div>
							 </div>
							   <div class="clear"></div>
							   <div style="font-size: 12px; color: #444;">
							   		DropBox provides 2 gb of free storage space. Be sure your snapshots does't reach this limit.
								</div>
							</div>   
				</div>

			<!-- RackSpace STUFF HERE -->	
				<?php $display = ($meta_arr['type']=='rackspace')?'block':'none';?>
				<div id="ibk-rackspace" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>RackSpace</h3>
						<p>Connect your RackSpace account to your WP SuperBackup Instance.</p>	
							<div class="ibk-inside-item">
							 <div class="row">
							   <div class="col-xs-7"> 
							   
								   <div class="form-group">
										<?php if (empty($meta_arr['username'])) $meta_arr['username'] = '';?>
										<label>Username: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['username'];?>" name="rs_username" id="rs_username" />								   
								   </div>
									<br/>
								   <div class="form-group">
										<?php if (empty($meta_arr['api_key'])) $meta_arr['api_key'] = '';?>
										<label>API Key: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['api_key'];?>" name="rs_api_key" id="rs_api_key" />								   
								   </div>
									<br/>
								   <div class="form-group">
										<?php if (empty($meta_arr['container'])) $meta_arr['container'] = '';?>
										<label>Container: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['container'];?>" name="rs_container" id="rs_container" />								   
								   </div>	
								   <br/>								   
								   <div class="form-group">
										<?php if (empty($meta_arr['container_url'])) $meta_arr['container_url'] = '';?>
										<label>Container Public URL: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['container_url'];?>" name="rs_container_url" id="rs_container_url" />								   
								   </div>		
								   <br/>
								   <div class="form-group">
										<?php if (empty($meta_arr['region'])) $meta_arr['region'] = '';?>
										<label>Region: </label>
										<select name="rs_region" id="rs_region" class="form-control">
											<?php 
												$arr = array(	'SYD'=>'Sydney (SYD)', 
																'ORD'=>'Chicago (ORD)', 
																'IAD'=>'Northern Virginia (IAD)', 
																'HKG'=>'Hong Kong (HKG)',
																'LON' => 'London (LON)',
															);
												foreach ($arr as $k=>$v){
													$selected = ($k==$meta_arr['region']) ? 'selected' : '';
													?>
														<option value="<?php echo $k;?>" <?php echo $selected;?>><?php echo $v;?></option>
													<?php
												}
											?>
										</select>						   
								   </div>
								   
							   </div>
							 </div>
							</div>   
				</div>				
				
			<!-- OneDrive STUFF HERE -->	
				<?php $display = ($meta_arr['type']=='onedrive') ? 'block' : 'none'; ?>
				<div id="ibk-onedrive" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>OneDrive</h3>
						<p>Connect your Microsoft OneDrive account to your WP SuperBackup Instance.</p>	
						<div class="ibk-inside-item">
							<div class="row">
								<div class="col-xs-7"> 
								    <div class="form-group">
										<?php if (empty($meta_arr['client_id'])) $meta_arr['client_id'] = '';?>
										<label>Client ID: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['client_id'];?>" name="client_id" id="onedrive_client_id" />								   
									</div>
								    <div class="form-group">
										<?php if (empty($meta_arr['client_secret'])) $meta_arr['client_secret'] = '';?>
										<label>Client Secret: </label>
										<input type="text" class="form-control" value="<?php echo $meta_arr['client_secret'];?>" name="client_secret" id="onedrive_client_secret" />								   
									</div>	
									<div class="form-group">
									   	<?php									
											if (empty($meta_arr['onedrive_redirect_uri'])){
												$meta_arr['onedrive_redirect_uri'] = IBK_URL . 'admin/onedrive_landing_page.php';												
											}
										?>
										<label>Redirect URI:</label> <div style="color:#0a9fd8;"><?php echo $meta_arr['onedrive_redirect_uri'];?></div> 
										<input type="hidden" value="<?php echo $meta_arr['onedrive_redirect_uri'];?>" name="onedrive_redirect_uri" id="onedrive_redirect_uri" />
										<input type="hidden" value="<?php echo @$meta_arr['state'];?>" name="state" id="state" />			
								   </div>	
								   								
								</div>
							</div>
						</div>	
				</div>		
				
			<!-- COPY.COM STUFF HERE -->	
				<?php $display = ($meta_arr['type']=='copy')?'block':'none';?>
				<div id="ibk-copy" style="display: <?php echo $display;?>;">
					<div class="ibk-line-break"></div>
						<h3>Copy.com</h3>
						<p>Connect your Copy.com account to your WP SuperBackup Instance.</p>	
							<div class="ibk-inside-item">
							 <div class="row">
							   <div class="col-xs-5"> 		
							   	<div class="form-group">
									<?php if (empty($meta_arr['path'])) $meta_arr['path'] = '';?>
									<label>Optional Destination Folder (full path):</label>
									<input type="text"  class="form-control" value="<?php echo $meta_arr['path'];?>" name="path" id="copy_path" />
								</div>						   				   								   
							   </div>
							 </div>
							</div>   
				</div>				
				
												
				<div class="ibk-bttn-wrapp"> 
					<?php 
						$auth = (empty($meta_arr['connected'])) ? 1 : 0;
					?>
					 <input type="button" value="Save" name="save-bttn" onClick="ibk_save_destination_metas(<?php echo $auth;?>, <?php echo $status;?>);" class="button button-primary button-large"/>
				</div>
				
				<div id="ibk_saving_message"></div>
					
			</div>
		</div>		
	</form>
	<?php 
} else {
	/***************************  LISTING  *************************/
	if (!empty($_GET['id'])){
		$type = ibk_get_destination_type($_GET['id']);//func available in utilities.php
		if ($type=='google'){
			if (!empty($_GET['code'])){
				//google authorize
				require_once IBK_PATH . 'classes/API/IndeedGoogle.class.php';
				$obj = new IndeedGoogle($_GET['id']);
				$authorize = $obj->authorize();	
				$this->ibk_change_connected_destination_status($_GET['id']);
			}	
		}
	} 
	$this->show_notification();
	?>
		<div>
			<a href="<?php echo $url.'&tab=destinations&subtab=edit_create&status='.$status;?>"  class="ibk-add-new"><i title="" class="fa-ibk fa-add-backup-ibk"></i><span>Add Destination</span></a>
			<span class="ibk-top-message">...customize your multiple Destinations!</span>
		</div>
		<div class="ibk-backup-items-wrap">
			<?php 
				$data = $this->ibk_get_items_list('destinations', 'DESC', $status);
				if ($data){
					foreach ($data as $obj){
						$meta = ibk_return_metas_from_custom_db('destinations', $obj->id);//func available in utilities.php
						$this->ibk_create_admin_destination_box($obj->id, $meta, $url, $status);
					}
				}else{ ?>
					<div class="ibk-nodata-wrapper">
						<img src="<?php echo IBK_URL;?>admin/assets/images/nodestinations.png"/>
						<a href="<?php echo $url.'&tab=destinations&subtab=edit_create&status='.$status;?>"  class="ibk-add-new"><i title="" class="fa-ibk fa-add-backup-ibk"></i><span>Add Destination</span></a>
					</div>
				<?php } ?>
		</div>
	<?php 
}

