<?php
	header("X-Robots-Tag: noindex, nofollow", true);
?>
<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['v'], ENT_QUOTES, 'UTF-8'); ?>js/view.core.js" data-cfasync="false"></script>
<style type="text/css">
	.hero_active_arrow{
		display:none; /* override default for map view */
	}
</style>

<div class="hero_maps_pro">
    
    <div class="hero_viewport"></div>

	<!--BEGIN: map specific views-->
    <div class="hero_map_content_holder">
        
        <div id="hero_map_main"></div>
        
        <div id="location_marker_coords_change_listener"></div>
        
        <div class="hero_map_search_bar">
        	<input class="location_search_input" name="location_search" id="location_search" type="text" placeholder="Search Map" />
            <div class="search_icon"></div>
        </div>
        
    </div>
    <!--END: map specific views-->

	<div class="marker_edit_panel hero_views">
    	<div class="internal_edit_content">
            <div class="edit_top">
                <div class="hero_col_12">
                    <div class="hero_col_6">
                        <div class="marker_image_container">
                            <div class="marker_img_holder">
                                <div class="marker_img"></div>
                            </div>
                        </div>
                    </div>
                    <div class="hero_col_6">
                        <div id="done_location_marker_btn" class="hero_button_auto green_button rounded_3">Close</div>
                        <div id="del_location_marker_btn" class="hero_button_auto red_button rounded_3"><img></div>
                        <script type="text/javascript" data-cfasync="false">
                            jQuery('#del_location_marker_btn img').attr('src',plugin_url +'assets/images/admin/delete_btn_img.png');
                        </script>
                        <p class="edit_note_link" id="marker_edit_img_btn">
                            Change Marker Image
                        </p>
                    </div>
                    <!--<p class="edit_note">
                        To change the marker image, navigate to the "Markers" panel and drag-and-drop a marker onto the image above.
                    </p>-->
                </div>
                <div style="clear:both;"></div>
            </div>
            <div class="marker_data_container">
                <div class="edit_holder">
                    <div class="edit_inner">
                        <div class="title">
                            <div class="marker_edit_panel_inner">
                                Location Title
                            </div>
                        </div>
                        <div class="marker_edit_panel_inner marker_edit_input">
                            <div class="marker_edit_input_inner">
                                <input data-size="lrg" type="text" id="location_title" name="location_title" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="edit_holder">
                    <div class="edit_inner">
                        <div class="title">
                            <div class="marker_edit_panel_inner">
                                Location Coordinates
                            </div>
                        </div>
                        <div class="marker_edit_panel_inner marker_edit_input">
                            <div class="marker_edit_input_inner">
                                <input data-size="lrg" type="text" id="location_coordinates" name="location_coordinates" value="" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="edit_holder">
                    <div class="edit_inner">
                        <div class="title">
                            <div class="marker_edit_panel_inner">
                                Info Window Content
                                <div class="display_switch"><input type="checkbox" data-size="sml" id="info_window_show" name="info_window_show" value=""></div>
                            </div>
                        </div>
                        <div class="marker_edit_panel_inner marker_edit_input">
                            <div class="hidden_content">
                                <div class="hidden_content_inner">
                                    <div class="marker_edit_input_inner" style="padding-bottom:0;">
                                        <textarea data-size="lrg" id="info_window_content" name="info_window_content"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="edit_holder">
                    <div class="edit_inner">
                        <div class="title">
                            <div class="marker_edit_panel_inner">
                                Link
                                <div class="display_switch"><input type="checkbox" data-size="sml" id="link_show" name="link_show" value=""></div>
                            </div>
                        </div>
                        <div class="marker_edit_panel_inner marker_edit_input">
                            <div class="hidden_content">
                                <div class="hidden_content_inner">
                                    <div class="marker_edit_input_inner">
                                        <div class="label"><div>Title:</div></div>
                                        <div class="holder">
                                            <input data-size="lrg" type="text" id="link_title" name="link_title" value="" style="margin-bottom:3px;">
                                        </div>
                                        <div class="label"><div>Link:</div></div>
                                        <div class="holder">
                                            <input data-size="lrg" type="text" id="link" name="link" value="" style="margin-bottom:3px;" placeholder="example: http://www.link.com">
                                        </div>
                                        <div class="label"><div>Color:</div></div>
                                        <div class="holder">
                                            <input data-size="lrg" type="text" id="link_colour" name="link_colour" value="#DC4551" class="color_picker" style="margin-bottom:3px;">
                                        </div>
                                        <div class="label"><div>Target:</div></div>
                                        <div class="holder">
                                            <select data-size="lrg" id="link_target" name="link_target">
                                                <option value="_blank">New Window</option>
                                                <option value="_self">Same Window</option>
                                            </select>
                                        </div>
                                        <div style="clear:both;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="edit_holder">
                    <div class="edit_inner">
                        <div class="title">
                            <div class="marker_edit_panel_inner">
                                Marker Category
                            </div>
                        </div>
                        <div class="marker_edit_panel_inner marker_edit_input">
                            <div class="marker_edit_input_inner">
                                <select data-size="lrg" data-height="85" id="marker_category" name="marker_category" >
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="custom_param_container">
                    <div class="edit_holder">
                        <div class="edit_inner">
                            <div class="title">
                                <div class="marker_edit_panel_inner">
                                    onClick Parameter
                                </div>
                            </div>
                            <div class="marker_edit_panel_inner marker_edit_input">
                                <div class="marker_edit_input_inner">
                                    <input data-size="lrg" type="text" id="custom_param" name="custom_param" value="">
                                    <p class="edit_note">
                                        This parameter will be passed to the JavaScript method, specified on the "Developers" tab, when this marker is clicked.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="internal_shape_edit_content">
        </div>
    </div>
	
    <div class="arrow_container">
        <div class="arrow_down"></div>
        <div class="arrow_right"></div>
	</div>
</div>