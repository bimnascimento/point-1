<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_markers.view.js" data-cfasync="false"></script>
<div class="marker_selection hero_col_12">
    <div class="hero_col_2 marker_pack">
    	<div class="hero_grey size_14">
            <div class="selection_holder hero_col_12">
                <p class="hero_green size_14">Marker Pack</p>
                <div class="hero_col_12">
                    <select data-size="med" id="map_marker_category" name="map_marker_category">
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="hero_col_9 marker_selector">
        <div class="hero_grey size_14">
            <div class="hero_col_12 colour_choice">
                <div class="hero_col_1"><p class="hero_green size_14">Color</p></div>
                <div class="hero_col_11">
                	<div class="hero_preset_holder"></div>
                </div>
            </div>
            <div class="hero_col_12 drag_copy">
	            <div class="hero_col_1">&nbsp;</div>
	            <div class="hero_col_11"><p class="size_12" style="transition-duration:1s;"></p></div>
                <script type="text/javascript" data-cfasync="false">
					jQuery('.drag_copy p').html(marker_help_copy);
				</script>
            </div>
            <div class="hero_col_12 marker_choice">
                <div class="hero_col_1"><p class="hero_green size_14">Markers</p></div>
                <div class="hero_col_11">
                	<div id="marker_display_holder"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Map Markers<br />
            <strong class="size_11 hero_grey">Manage map location markers and categories</strong><br><br>
        </h1>
        <div class="mass_marker_update_panel">
            <div class="mass_marker_update_panel_inner">
            	<div class="mass_marker_update_drop_holder">
	                <div class="mass_marker_update_drop">
                    	<p class="size_12 hero_grey">Select your new icon above and drag and drop it here to update all markers in this category</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="hero_section_holder hero_grey" style="margin-top:5px;">
            <div class="hero_col_12" style="width:100%; padding-bottom:14px;">
            	<div class="hero_col_4">
                	<div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_3">
                            <p class="size_12 hero_grey" style="padding:7px 0;">Category:</p>
                        </div>
                        <div class="hero_col_9">
                            <select data-size="lrg" data-height="85" id="marker_table_category_selector">
                    		</select>
                        </div>
                    </div>
                </div>
                <div class="hero_col_8" style="padding-right:0;">
                	<div style="float:left; margin-left:-10px;" class="hero_button_auto red_button rounded_3" onClick="launch_hero_popup('maps/html_snippets/manage_categories.html','populate_categories_table','','',''); hide_marker_mass_update_panel();">Manage Categories</div>
					<div style="float:right; margin-right:0;" onClick="show_marker_mass_update_panel();" class="hero_button_auto red_button rounded_3">Set Category Marker</div>
                </div>
            </div>
            <div class="hero_section_holder hero_grey hero_map_filters" style="padding-top:4px; padding-bottom:5px;">
                <div class="hero_col_12">
                    <div style="position:absolute;width:100%;text-align:center;">
                        <div class="hero_paging_holder">
                            <div class="hero_page_btn"><a class="hero_paging_prev">prev</a></div>
                            <span style="display:inline-block;"></span>
                            <div class="hero_page_btn"><a class="hero_paging_next">next</a></div>
                        </div>
                    </div>
                    <div style="float:right; padding-right:3px;">
                        <div style="float:left; width:102px;">
                            <p class="size_12 hero_grey" style="padding:7px 0;">Items per page:</p>
                        </div>
                        <div style="float:left; width:68px;">
                            <select data-size="lrg" id="hero_items_per_page1" class="hero_items_per_page">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="hero_list_holder hero_grey size_11">
                <div class="hero_col_12 hero_list_heading hero_white">
                    <div class="hero_col_1"><span>Marker</span></div>
                    <div class="hero_col_4"><span>Coordinates</span></div>
                    <div class="hero_col_3"><span>Location Title</span></div>
                    <div class="hero_col_2"><span>Category</span></div>
                    <div class="hero_col_2"><span></span></div>
                </div>
                <div id="category_content_holder">
                </div>
            </div>
            <div class="hero_section_holder hero_grey hero_map_filters" style="padding-top:4px; padding-bottom:5px;">
                <div class="hero_col_12">
                    <div style="position:absolute;width:100%;text-align:center;">
                        <div class="hero_paging_holder">
                            <div class="hero_page_btn"><a class="hero_paging_prev"><img class="paging_left_arrow"> prev</a></div>
                            <span style="display:inline-block;"></span>
                            <div class="hero_page_btn"><a class="hero_paging_next">next <img class="paging_right_arrow"></a></div>
                        </div>
                    </div>
                    <div style="float:right; padding-right:3px;">
                        <div style="float:left; width:102px;">
                            <p class="size_12 hero_grey" style="padding:7px 0;">Items per page:</p>
                        </div>
                        <div style="float:left; width:68px;">
                            <select data-size="lrg" id="hero_items_per_page2" class="hero_items_per_page">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="40">40</option>
                                <option value="50">50</option>
                            </select>
                        </div>
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
            <div class="hero_col_12" style="width:98%;">
                <div class="hero_note">
                    <p class="size_12">Note: If you would like to display the Marker Category Selector on the output map, please ensure that it is turned on in "Settings".</p>
                </div>
            </div>
            <script type="text/javascript" data-cfasync="false">
                //jQuery('.paging_left_arrow').attr('src',plugin_url +'assets/images/admin/paging_left_arrow.png');
                //jQuery('.paging_right_arrow').attr('src',plugin_url +'assets/images/admin/paging_right_arrow.png');
            </script>
        </div>
	</div>
</div>



















