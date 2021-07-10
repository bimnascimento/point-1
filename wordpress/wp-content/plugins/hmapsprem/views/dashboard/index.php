<?php
	header("X-Robots-Tag: noindex, nofollow", true);
?>
<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['v'], ENT_QUOTES, 'UTF-8'); ?>js/view.core.js" data-cfasync="false"></script>

<!--BEGIN: dashboard-->
<div class="hero_top_dashboard">
    <div class="hero_dashboard_intro">
        <div class="hero_dashboard_logo"><img onclick="window.open('http://heroplugins.com/plugin/hero-maps-premium/');" src="<?php echo $_GET['p']; ?>assets/images/admin/plugin_logo.png" /></div>
    </div>
</div>
<div class="hero_top_version">
    <div class="hero_version hero_white" id="plugin_version"></div>
    <div class="hero_version_date hero_white">
        <div class="hero_last"><span id="plugin_last_update"></span><br />Last Update</div>
        <div class="hero_release"><span id="plugin_release_date"></span><br />Release Date</div>
    </div>
</div>

<div class="hero_views">
    <div class="dashboard_grid">
    
    	<!--BEGIN: custom content area-->
        <h2 class="hero_red size_18 dashboard_table_name"></h2>
        <div class="hero_list_holder hero_grey size_11">
        	<div class="hero_col_12 hero_list_heading hero_white">
                <div class="hero_col_4"><span>Name</span></div>
                <div class="hero_col_5"><span>Shortcode</span></div>
            </div>
            <div id="dashboard_object_holder">
        	</div>
        </div>
        <!--END: custom content area-->
        
    </div>
    <div class="promo_expand">
        <div class="promo_holder"></div>
    </div>
</div>
<!--END: dashboard-->

<form action="" method="post" target="map_download_iframe" id="map_download_iframe_form">
	<input type="hidden" id="map_download_file" name="map_download_file" value="" />
    <input type="hidden" id="map_download_file_name" name="map_download_file_name" value="" />
</form>
<div id="map_download_iframe_holder">
	<iframe name="map_download_iframe" width="0" height="0"></iframe>
</div>

<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['p'], ENT_QUOTES, 'UTF-8'); ?>assets/js/promotions_manager.js" data-cfasync="false"></script>