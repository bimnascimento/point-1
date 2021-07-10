<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_advanced.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Advanced Map Settings<br />
            <strong class="size_11 hero_grey">Change the advanced settings for your map</strong>
        </h1>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    Markers
                </h2>
            </div>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_drop_delay">
                        <h2 class="size_14 hero_darkgrey">Marker Drop Delay</h2>
                        <p class="size_12 hero_grey">Set the time, after map load, before location markers are placed</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" data-hero_type="ms" id="marker_drop_delay" name="marker_drop_delay" maxlength="5" data-node_val="map_advanced/marker_drop_delay">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_animation" style="cursor:default;">
                        <h2 class="size_14 hero_darkgrey">Marker Animation</h2>
                        <p class="size_12 hero_grey">Select a marker animation</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <select data-size="sml" id="marker_animation" name="marker_animation" data-node_val="map_advanced/marker_animation">
                    </select>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_animation_timer">
                        <h2 class="size_14 hero_darkgrey">Marker Animation Timer</h2>
                        <p class="size_12 hero_grey">Set the time delay between placing each location marker</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" data-hero_type="ms" id="marker_animation_timer" name="marker_animation_timer" maxlength="5" data-node_val="map_advanced/marker_animation_timer">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_tooltip">
                        <h2 class="size_14 hero_darkgrey">Tooltips</h2>
                        <p class="size_12 hero_grey">Location marker tooltips on mouseover</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="marker_tooltip" name="marker_tooltip" value="" data-node_val="map_advanced/tooltips">
                </div>
            </div>
			<div class="hero_col_12">
				<div class="hero_col_6">
					<label for="marker_clustering">
						<h2 class="size_14 hero_darkgrey">Marker Clustering</h2>
						<p class="size_12 hero_grey">Enable marker clustering on the map</p>
					</label>
				</div>
				<div class="hero_col_6">
					<input type="checkbox" data-size="lrg" id="marker_clustering" name="marker_clustering" value="" data-node_val="map_advanced/marker_clustering">
				</div>
			</div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14">
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    Shapes
                </h2>
            </div>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_drop_delay">
                        <h2 class="size_14 hero_darkgrey">Shape Drop Delay</h2>
                        <p class="size_12 hero_grey">Set the time, after map load, before shapes are placed</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" data-hero_type="ms" id="shape_drop_delay" name="shape_drop_delay" maxlength="5" data-node_val="map_advanced/shape_drop_delay">
                </div>
            </div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14">
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    Map Zoom
                </h2>
            </div>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="map_load_zoom">
                        <h2 class="size_14 hero_darkgrey">Map Load Zoom</h2>
                        <p class="size_12 hero_grey">Map zoom on load (before marker placement)</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" id="map_load_zoom" name="map_load_zoom" readonly data-node_val="map_advanced/map_load_zoom">
                </div>
            </div>
            <div class="hero_col_6 button-pull-right">
                <div id="get_load_zoom_btn" style="margin:-10px 0 10px -10px;" class="hero_button_auto red_button rounded_3">Get current zoom</div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="marker_click_zoom">
                        <h2 class="size_14 hero_darkgrey">Marker Click Zoom</h2>
                        <p class="size_12 hero_grey">Map zoom when a location marker is clicked</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" id="marker_click_zoom" name="marker_click_zoom" readonly data-node_val="map_advanced/marker_click_zoom">
                </div>
            </div>
            <div class="hero_col_6 button-pull-right">
                <div id="get_marker_click_zoom_btn" style="margin:-10px 0 10px -10px;" class="hero_button_auto red_button rounded_3">Get current zoom</div>
            </div>       
        </div>
        
	</div>
</div>