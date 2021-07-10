<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_setup.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Map Setup<br />
            <strong class="size_11 hero_grey">Change the setup options for your map</strong>
        </h1>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_4">
                    <h2 class="size_18 hero_red weight_600">Shortcode</h2>
                    <input type="text" data-size="lrg" id="shortcode" name="shortcode" onclick="jQuery(this).select();" readonly>
                </div>
            </div>
        </div>
		<div class="hero_section_holder hero_grey size_14">
			<div class="hero_col_12">
				<div class="hero_col_12">
					<h2 class="size_18 hero_red weight_600">Google Maps API</h2>
					<p class="size_12 hero_grey">All Google Maps JavaScript API applications require authentication</p>
				</div>
				<div class="hero_col_12">
					<div class="hero_col_5">
						<label for="font_family">
							<h2 class="size_14 hero_darkgrey">Key</h2>
							<p class="size_12 hero_grey">Please <a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">click here</a> to obtain a Google Maps API key.</p>
						</label>
					</div>
					<div class="hero_col_4">
						<input type="text" data-size="lrg" id="api_key" name="api_key" data-node_val="map_setup/api_key">
					</div>
				</div>
				<div class="hero_col_12">
					<div class="hero_note">
						<p class="size_12">
							Note: To avoid loading the Google Maps JavaScript API multiple times where a page contains more than one map, we will make use of the API Key of the first map on the page.
						</p>
					</div>
				</div>
			</div>
		</div>
		<div class="hero_section_holder hero_grey size_14">
			<div class="hero_col_12">
				<div class="hero_col_12">
					<h2 class="size_18 hero_red weight_600">Map Font</h2>
					<p class="size_12 hero_grey">Select a font to use throughout this map</p>
				</div>
				<div class="hero_col_12">
					<div class="hero_col_5">
						<label for="font_family">
							<h2 class="size_14 hero_darkgrey">Font Family</h2>
							<p class="size_12 hero_grey"></p>
						</label>
					</div>
					<div class="hero_col_4">
						<select data-size="lrg" data-height="200" id="font_family" name="font_family" data-node_val="map_setup/font_family">
							<option value="inherit">Inherit</option>
						</select>
					</div>
				</div>
			</div>
		</div>
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_12">
                    <h2 class="size_18 hero_red weight_600">Dimensions</h2>
                    <p class="size_12 hero_grey">A responsive map will alter its width to fit the container that it is placed into</p>
                </div>
                <div class="hero_col_10">
                    <div class="hero_col_3 h_cust_component comp_right_sep">
                        <div class="hero_col_8">
                            <label for="fixed_width">
                                <h2 class="size_14 hero_darkgrey">Fixed Width</h2>
                            </label>
                        </div>
                        <div class="hero_col_4">
                            <input type="radio" data-size="sml" id="fixed_width" class="responsive_switch" name="responsive_switch" value="false" data-node_val="map_setup/responsive">
                        </div>
                    </div>
                    <div class="hero_col_3 h_cust_component">
                        <div class="hero_col_8">
                            <label for="responsive">
                                <h2 class="size_14 hero_darkgrey">Responsive</h2>
                            </label>
                        </div>
                        <div class="hero_col_4">
                            <input type="radio" data-size="sml" id="responsive" class="responsive_switch" name="responsive_switch" value="true" data-node_val="map_setup/responsive">
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_4">
                    <label for="map_width">Map Width</label>
                    <input type="text" data-size="lrg" id="map_width" name="map_width" maxlength="4" data-node_val="map_setup/map_width">
                </div>
                <div class="hero_col_4">
                    <label for="map_height">Map Height</label>
                    <input type="text" data-size="lrg" data-hero_type="px" id="map_height" name="map_height" maxlength="4" data-node_val="map_setup/map_height">
                </div>
            </div>
        </div>
        <div class="hero_note">
            <p class="size_12">
                Note: Changes to the above values will only alter the final map output on the front-end.
            </p>
        </div>
	</div>
</div>