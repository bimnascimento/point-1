<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_controls.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Map Controls<br />
            <strong class="size_11 hero_grey">Define the controls for your map</strong>
        </h1>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="street_view">
                        <h2 class="size_14 hero_green">Street View</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="street_view" name="street_view" class="hide_switch" data-node_val="map_controls/street_view">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="street_view_position" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Street View Position</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="sml" id="street_view_position" name="street_view_position" data-node_val="map_controls/street_view_position">
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="map_type">
                        <h2 class="size_14 hero_green">Map Type</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="map_type" name="map_type" value="" class="hide_switch" data-node_val="map_controls/map_type">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="map_type_position" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Map Type Position</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="sml" id="map_type_position" name="map_type_position" data-node_val="map_controls/map_type_position">
                            </select>
                        </div>
                    </div>
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="map_type_style" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Map Type Style</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="sml" id="map_type_style" name="map_type_style" data-node_val="map_controls/map_type_style">
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
            	</div>
            </div>
        </div>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rotate">
                        <h2 class="size_14 hero_green">Rotate</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="rotate" name="rotate" class="hide_switch" data-node_val="map_controls/rotate">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="rotate_position" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Rotate Position</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="sml" id="rotate_position" name="rotate_position" data-node_val="map_controls/rotate_position">
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
            	</div>
            </div>
        </div>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="zoom">
                        <h2 class="size_14 hero_green">Zoom</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="zoom" name="zoom" class="hide_switch" data-node_val="map_controls/zoom">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="zoom_position" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Zoom Position</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="sml" id="zoom_position" data-height="120" name="zoom_position" data-node_val="map_controls/zoom_position">
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
            	</div>
            </div>
        </div>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="scale">
                        <h2 class="size_14 hero_green">Scale</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="scale" name="scale" data-node_val="map_controls/scale">
                </div>
            </div>
        </div>
		<div class="hero_section_holder hero_grey size_14">
			<div class="hero_col_12">
				<div class="hero_col_6">
					<label for="show_location">
						<h2 class="size_14 hero_green">Show Location</h2>
						<p class="size_12 hero_grey"></p>
					</label>
				</div>
				<div class="hero_col_6">
					<input type="checkbox" data-size="lrg" id="show_location" name="show_location" data-node_val="map_controls/show_location">
				</div>
			</div>
		</div>
	</div>
</div>