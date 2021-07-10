<div class="ibk-dashboard-wrap">
	<div class="ibk-admin-header">
		<div class="ibk-top-menu-section">
			<div class="ibk-dashboard-logo">
			<a href="<?php echo $url.'&tab=dashboard';?>">
				<img src="<?php echo IBK_URL;?>admin/assets/images/dashboard-logo.jpg"/>
			</a>
			</div>
			<div class="ibk-dashboard-menu">
				<ul>
				<?php 
					foreach($tabs_arr as $k=>$v){
						$selected = '';
						if($tab==$k) $selected = 'selected';						
						?>
							<li class="<?php echo $selected;?>">
								<a href="<?php echo $url.'&tab='.$k;?>">
									<div class="ibk-page-title">
										<i class="fa-ibk fa-ibk-menu fa-<?php echo $k;?>-ibk"></i>
										<div><?php echo $v;?></div>								
									</div>						
								</a>
							</li>	
						<?php 	
					}
				?>		
				</ul>
			</div>
		</div>
	</div>
	
	<script>
		jQuery( window ).load(function(){
			ibk_check_restore_status();
		});
	</script>
<?php 

$themes_dir = str_replace('plugins/indeed-backup','themes', IBK_PATH);
