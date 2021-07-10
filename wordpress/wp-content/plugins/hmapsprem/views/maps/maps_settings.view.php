<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_settings.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Map Settings<br />
            <strong class="size_11 hero_grey">Change the basic settings for your map</strong>
        </h1>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    Map Style
                </h2>
            </div>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="map_type" style="cursor:default;">
                        <h2 class="size_14 hero_darkgrey">Map Type</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <select data-size="sml" id="map_type" name="map_type" data-node_val="map_settings/map_type">
                    </select>
                </div>
            </div>
            <div class="hide_container map_theme_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="map_theme" style="cursor:default;">
                                <h2 class="size_14 hero_darkgrey">Map Theme</h2>
                                <p class="size_12 hero_grey"></p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <select data-size="lrg" data-height="200" id="map_theme" name="map_theme" data-node_val="map_settings/map_theme">
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
                    <label for="autofit">
                        <h2 class="size_14 hero_darkgrey">Auto Fit</h2>
                        <p class="size_12 hero_grey">Auto-scale the map to the maximum zoom level where all markers are visible</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="autofit" name="autofit" class="show_switch" data-node_val="map_settings/auto_fit">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_9">
                            <div class="hero_col_5">
                                <label for="map_center">Map Center</label><br>
                                <input type="text" data-size="lrg" id="map_center" name="map_center" onclick="jQuery(this).select();" readonly data-node_val="map_settings/map_center">
                            </div>
                            <div class="hero_col_1">&nbsp;</div>
                            <div class="hero_col_5">
                                <label for="rest_zoom">Rest Zoom</label><br>
                                <input type="text" data-size="lrg" id="rest_zoom" name="rest_zoom" readonly data-node_val="map_settings/rest_zoom">
                            </div>
                        </div>
                        <div class="hero_col_12">
                            <div id="get_map_center_zoom_btn" class="hero_button_auto red_button rounded_3">Get map center and zoom</div>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="mouse_wheel_zoom">
                        <h2 class="size_14 hero_darkgrey">Mouse Wheel Zoom</h2>
                        <p class="size_12 hero_grey">Zoom in and out of the map with the mouse wheel</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="mouse_wheel_zoom" name="mouse_wheel_zoom" data-node_val="map_settings/mouse_wheel_zoom">
                </div>
            </div>
        </div>

		<div class="hero_section_holder hero_grey size_14">
			<div class="hero_col_12">
				<div class="hero_col_6">
					<label for="mobile_pan_lock">
						<h2 class="size_14 hero_darkgrey">Mobile Pan Lock</h2>
						<p class="size_12 hero_grey">Lock the map pan function on mobile devices</p>
					</label>
				</div>
				<div class="hero_col_6">
					<input type="checkbox" data-size="lrg" class="hide_switch"  id="mobile_pan_lock" name="mobile_pan_lock" data-node_val="map_settings/mobile_pan_lock">
				</div>
			</div>
			<div class="hide_container mobile_lock_holder">
				<div class="internal">
					<div class="hero_col_12">
						<div class="hero_col_6">
							<label for="mobile_pan_lock_width">
								<h2 class="size_14 hero_darkgrey">Mobile Pan Lock Width</h2>
								<p class="size_12 hero_grey">Set the pan lock width</p>
							</label>
						</div>
						<div class="hero_col_6">
							<input type="text" data-hero_type="px" data-size="sml" id="mobile_pan_lock_width" name="mobile_pan_lock_width" data-node_val="map_settings/mobile_pan_lock_width">
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
		</div>

		<div class="hero_section_holder hero_grey size_14">
			<div class="hero_col_12">
				<div class="hero_col_6">
					<label for="frontend_search">
						<h2 class="size_14 hero_darkgrey">Front-end Search</h2>
						<p class="size_12 hero_grey">Enable front-end map search</p>
					</label>
				</div>
				<div class="hero_col_6">
					<input type="checkbox" data-size="lrg" id="frontend_search" name="frontend_search" data-node_val="map_settings/frontend_search">
				</div>
			</div>
		</div>
        
        <div class="hero_section_holder hero_grey size_14">
        	<h2 class="hero_green size_18">
                Marker Category Selector
            </h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="show_category_selector">
                        <h2 class="size_14 hero_darkgrey">Show Marker Category Selector</h2>
                        <p class="size_12 hero_grey">Display the category selector on the output map</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="show_category_selector" name="show_category_selector" class="hide_switch" data-node_val="map_settings/show_category_selector">
                </div>
            </div>
			<div class="hide_container show_category_select_options">
				<div class="internal">
					<div class="hero_col_12" style="border-top: 1px solid #efefef; padding-top:25px; margin-top:15px;">
						<div class="hero_col_12" style="width:100%; padding:0;">
							<div class="hero_col_6">
								<label for="category_selector_select">
									<h2 class="size_14 hero_darkgrey">Use DropDown Selector</h2>
									<p class="size_12 hero_grey">Select active category from a dropdown list</p>
								</label>
							</div>
							<div class="hero_col_6">
								<input type="radio" data-size="lrg" value="false" id="category_selector_select" name="category_selector_tabs" class="" data-node_val="map_settings/category_selector_tabs">
							</div>
						</div>
						<!--BEGIN: select content-->
						<div class="hide_container category_selector_select_content">
							<div class="internal">
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="default_display_category" style="cursor:default;">
											<h2 class="size_14 hero_darkgrey">Default 'Show All Markers' Copy</h2>
											<p class="size_12 hero_grey">Define the copy for the "Show All" option</p>
										</label>
									</div>
									<div class="hero_col_6">
										<input type="text" data-size="sml" id="show_category_selector_copy" name="show_category_selector_copy" data-node_val="map_settings/show_category_selector_copy">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="default_display_category" style="cursor:default;">
											<h2 class="size_14 hero_darkgrey">Default Category</h2>
											<p class="size_12 hero_grey">Choose the default category to display on map load</p>
										</label>
									</div>
									<div class="hero_col_6">
										<select data-size="sml" id="default_display_category" name="default_display_category" data-node_val="map_settings/default_display_category">
										</select>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="default_category_component_position" style="cursor:default;">
											<h2 class="size_14 hero_darkgrey">Default Selector Position</h2>
											<p class="size_12 hero_grey">Choose the position to display the category selector</p>
										</label>
									</div>
									<div class="hero_col_6">
										<select data-size="sml" id="default_category_component_position" name="default_category_component_position" data-node_val="map_settings/default_category_component_position">
											<option value="TOP_LEFT">Top Left</option>
											<option value="TOP_RIGHT">Top Right</option>
											<option value="BOTTOM_LEFT">Bottom Left</option>
											<option value="BOTTOM_RIGHT">Bottom Right</option>
										</select>
									</div>
								</div>
								<div class="hero_col_12" style="border-top:1px dashed #DDD; border-bottom:1px dashed #DDD;">
									<div class="hero_col_12">
										<h2 class="hero_green size_18">
											Marker Category Selector Styles
										</h2>
									</div>
									<div class="hero_col_12">
										<label for="category_selector_border_weight">
											<h2 class="size_14 hero_darkgrey">Output Selector Example</h2>
											<p class="size_12 hero_grey">Make use of the options below to style the category selector</p>
										</label>
									</div>
									<div class="hero_col_12" style="width:70%; background-color:#DDD; text-align:center;">
										<select class="example_category_selector" data-bound="set" style="display:inline-block; outline:none !important; height:auto; line-height:none;">
											<option>Category 1</option>
											<option>Category 2</option>
											<option>Category 3</option>
											<option>Category 4</option>
										</select>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_font_size">
											<h2 class="size_14 hero_darkgrey">Category Selector Font Size</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_selector_font_size" name="category_selector_font_size" data-node_val="map_settings/category_selector_font_size" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="10" data-max="50" data-step="1" data-bind_link="category_selector_font_size" id="category_selector_font_size_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_font_weight">
											<h2 class="size_14 hero_darkgrey">Category Selector Font Weight</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:165px;">
										<select data-size="lrg" id="category_selector_font_weight" name="category_selector_font_weight" data-node_val="map_settings/category_selector_font_weight">
											<option value="normal">Normal</option>
											<option value="bold">Bold</option>
										</select>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_font_colour">
											<h2 class="size_14 hero_darkgrey">Category Selector Font Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_selector_font_colour" name="category_selector_font_colour" data-node_val="map_settings/category_selector_font_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_fill_colour">
											<h2 class="size_14 hero_darkgrey">Category Selector Fill Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_selector_fill_colour" name="category_selector_fill_colour" data-node_val="map_settings/category_selector_fill_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_border_colour">
											<h2 class="size_14 hero_darkgrey">Category Selector Border Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_selector_border_colour" name="category_selector_border_colour" data-node_val="map_settings/category_selector_border_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_border_weight">
											<h2 class="size_14 hero_darkgrey">Category Selector Border Weight</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_selector_border_weight" name="category_selector_border_weight" data-node_val="map_settings/category_selector_border_weight" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="5" data-step="1" data-bind_link="category_selector_border_weight" id="category_selector_border_weight_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_border_radius">
											<h2 class="size_14 hero_darkgrey">Category Selector Border Radius</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_selector_border_radius" name="category_selector_border_radius" data-node_val="map_settings/category_selector_border_radius" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="10" data-step="1" data-bind_link="category_selector_border_radius" id="category_selector_border_radius_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_vert_padding">
											<h2 class="size_14 hero_darkgrey">Category Selector Vertical Padding</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_selector_vert_padding" name="category_selector_vert_padding" data-node_val="map_settings/category_selector_vert_padding" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_selector_vert_padding" id="category_selector_vert_padding_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_selector_hor_padding">
											<h2 class="size_14 hero_darkgrey">Category Selector Horizontal Padding</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_selector_hor_padding" name="category_selector_hor_padding" data-node_val="map_settings/category_selector_hor_padding" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_selector_hor_padding" id="category_selector_hor_padding_slider"></div>
											</div>
										</div>
									</div>
									<div style="clear:both; padding-top:20px;"></div>
									<div class="hero_note" style="font-weight:normal; margin-bottom:10px;">
										<p class="size_12">
											Note: Additional component styling can be achieved by adding a Category Selector CSS class, under the "Developers" tab.
											Once added, you can make <br> use of the themes' CSS to add styles to the selector (e.g. font-family, etc...)
										</p>
									</div>
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>
						<!--END: select content-->
					</div>
					<div class="hero_col_12" style="border-top: 1px solid #efefef; padding-top:25px; margin-top: 15px;">
						<div class="hero_col_12" style="width:100%; padding:0;">
							<div class="hero_col_6">
								<label for="category_selector_tabs">
									<h2 class="size_14 hero_darkgrey">Use Tab Selector</h2>
									<p class="size_12 hero_grey">Select active categories from a tab system</p>
								</label>
							</div>
							<div class="hero_col_6">
								<input type="radio" data-size="lrg" value="true" id="category_selector_tabs" name="category_selector_tabs" class="" data-node_val="map_settings/category_selector_tabs">
							</div>
						</div>
						<!--BEGIN: tab content-->
						<div class="hide_container category_selector_tab_content">
							<div class="internal">
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="default_category_tab_position" style="cursor:default;">
											<h2 class="size_14 hero_darkgrey">Default Tab Holder Position</h2>
											<p class="size_12 hero_grey">Choose the position to display the category selector</p>
										</label>
									</div>
									<div class="hero_col_6">
										<select data-size="sml" id="default_category_tab_position" name="default_category_tab_position" data-node_val="map_settings/default_category_tab_position">
											<option value="TOP">Top</option>
											<option value="BOTTOM">Bottom</option>
										</select>
									</div>
								</div>
								<div class="hero_col_12" style="border-top:1px dashed #DDD; border-bottom:1px dashed #DDD;">
									<div class="hero_col_12">
										<h2 class="hero_green size_18">
											Marker Category Tab Selector Styles
										</h2>
									</div>
									<div class="hero_col_12">
										<label for="">
											<h2 class="size_14 hero_darkgrey">Output Tab Selector Example</h2>
											<p class="size_12 hero_grey">Make use of the options below to style the category selector</p>
										</label>
										<p class="size_12 hero_grey" style="width:70%;">
											<br>
											In order to select which category tabs will be active when the front-end output map loads, set the
											required category tab to active in the below output.
										</p>
									</div>
									<div style="clear:both; padding-top:20px;"></div>
									<div class="hero_note" style="font-weight:normal; margin-bottom:10px; width:70%;">
										<p class="size_12">
											Note: All markers must be assigned a category (i.e. not "uncategorized"). Any category that does not contain at least one marker,
											will not be displayed on the front-end map.
										</p>
									</div>
									<div class="hero_col_12 hmapsprem_cat_tab_container" style="width:70%; background-color:#DDD;"></div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_font_size">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Font Size</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_font_size" name="category_tab_font_size" data-node_val="map_settings/category_tab_font_size" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="10" data-max="50" data-step="1" data-bind_link="category_tab_font_size" id="category_tab_font_size_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_bg_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Container Background Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_bg_colour" name="category_tab_bg_colour" data-node_val="map_settings/category_tab_bg_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_font_weight">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Font Weight</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:165px;">
										<select data-size="lrg" id="category_tab_font_weight" name="category_tab_font_weight" data-node_val="map_settings/category_tab_font_weight">
											<option value="normal">Normal</option>
											<option value="bold">Bold</option>
										</select>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_font_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Font Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_font_colour" name="category_tab_font_colour" data-node_val="map_settings/category_tab_font_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_font_active_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Font Active Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_font_active_colour" name="category_tab_font_active_colour" data-node_val="map_settings/category_tab_font_active_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_fill_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Fill Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_fill_colour" name="category_tab_fill_colour" data-node_val="map_settings/category_tab_fill_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_active_fill_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Active Fill Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_active_fill_colour" name="category_tab_active_fill_colour" data-node_val="map_settings/category_tab_active_fill_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_border_bottom_only">
											<h2 class="size_14 hero_darkgrey">Category Tab Border Bottom Only</h2>
											<p class="size_12 hero_grey">If "off", a full border will be applied</p>
										</label>
									</div>
									<div class="hero_col_6">
										<input type="checkbox" data-size="sml" id="category_tab_border_bottom_only" name="category_tab_border_bottom_only" data-node_val="map_settings/category_tab_border_bottom_only">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_border_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Border Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_border_colour" name="category_tab_border_colour" data-node_val="map_settings/category_tab_border_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_border_active_colour">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Border Active Color</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_3" style="width:166px;">
										<input style="width:40%;" type="text" class="color_picker" id="category_tab_border_active_colour" name="category_tab_border_active_colour" data-node_val="map_settings/category_tab_border_active_colour">
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_border_weight">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Border Weight</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_border_weight" name="category_tab_border_weight" data-node_val="map_settings/category_tab_border_weight" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="5" data-step="1" data-bind_link="category_tab_border_weight" id="category_tab_border_weight_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_border_radius">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Border Radius</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_border_radius" name="category_tab_border_radius" data-node_val="map_settings/category_tab_border_radius" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="10" data-step="1" data-bind_link="category_tab_border_radius" id="category_tab_border_radius_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_vert_padding">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Vertical Padding</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_vert_padding" name="category_tab_vert_padding" data-node_val="map_settings/category_tab_vert_padding" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_tab_vert_padding" id="category_tab_vert_padding_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_hor_padding">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Horizontal Padding</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_hor_padding" name="category_tab_hor_padding" data-node_val="map_settings/category_tab_hor_padding" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_tab_hor_padding" id="category_tab_hor_padding_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_vert_margin">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Vertical Margin</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_vert_margin" name="category_tab_vert_margin" data-node_val="map_settings/category_tab_vert_margin" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_tab_vert_margin" id="category_tab_vert_margin_slider"></div>
											</div>
										</div>
									</div>
								</div>
								<div class="hero_col_12">
									<div class="hero_col_6">
										<label for="category_tab_hor_margin">
											<h2 class="size_14 hero_darkgrey">Category Tab Selector Horizontal Margin</h2>
											<p class="size_12 hero_grey"></p>
										</label>
									</div>
									<div class="hero_col_5">
										<div class="hero_col_12" style="padding:0;">
											<div class="hero_col_2">
												<input type="text" data-size="lrg" data-hero_type="px" id="category_tab_hor_margin" name="category_tab_hor_margin" data-node_val="map_settings/category_tab_hor_margin" readonly>
											</div>
											<div class="hero_col_4">
												<div class="hero_slider" data-min="0" data-max="30" data-step="1" data-bind_link="category_tab_hor_margin" id="category_tab_hor_margin_slider"></div>
											</div>
										</div>
									</div>
									<div style="clear:both; padding-top:20px;"></div>
									<div class="hero_note" style="font-weight:normal; margin-bottom:10px;">
										<p class="size_12">
											Note: Additional component styling can be achieved by adding a Category Selector CSS class, under the "Developers" tab.
											Once added, you can make <br> use of the themes' CSS to add styles to the selector (e.g. font-family, etc...)
										</p>
									</div>
								</div>
								<div style="clear:both;"></div>
							</div>
						</div>
						<!--END: tab content-->
					</div>
					<div style="clear:both;"></div>
				</div>
			</div>
        </div>

        <div class="hero_section_holder hero_grey size_14">
			<h2 class="hero_green size_18">
				Directions
			</h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="get_diretions">
                        <h2 class="size_14 hero_darkgrey">Get Directions</h2>
                        <p class="size_12 hero_grey">Enable the user to get directions to location markers</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="get_diretions" name="get_diretions" data-node_val="map_settings/get_directions">
                </div>
            </div>
        </div>
        
	</div>
</div>