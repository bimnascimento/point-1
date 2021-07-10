<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_drawing.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Map Shapes<br />
            <strong class="size_11 hero_grey">Manage the default styles for map shapes</strong><br><br>
            <div class="hero_note" style="font-weight:normal; margin-bottom:10px;">
                <p class="size_12">Note: Altering the below values will not have any effect on existing map shapes.</p>
            </div>
        </h1>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<h2 class="hero_green size_18">
                Polyline
            </h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polyline_stroke_colour">
                        <h2 class="size_14 hero_darkgrey">Polyline Stroke Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="polyline_stroke_colour" name="polyline_stroke_colour" data-node_val="map_drawing/polylineOptions/strokeColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polyline_stroke_opacity">
                        <h2 class="size_14 hero_darkgrey">Polyline Stroke Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="polyline_stroke_opacity" name="polyline_stroke_opacity" data-node_val="map_drawing/polylineOptions/strokeOpacity">
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polyline_stroke_opacity" id="polyline_stroke_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polyline_stroke_weight">
                        <h2 class="size_14 hero_darkgrey">Polyline Stroke Weight</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                	<div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="px" id="polyline_stroke_weight" name="polyline_stroke_weight" data-node_val="map_drawing/polylineOptions/strokeWeight" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="polyline_stroke_weight" id="polyline_stroke_weight_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polyline_geodesic">
                        <h2 class="size_14 hero_darkgrey">Polyline Geodesic</h2>
                        <p class="size_12 hero_grey">Set polyline to follow the curvature of the earth</p>
                    </label>
                </div>
                <div class="hero_col_6">
                	<input type="checkbox" data-node_val="map_drawing/polylineOptions/geodesic" data-size="lrg" id="polyline_geodesic" name="polyline_geodesic" value="true">
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14">
        	<h2 class="hero_green size_18">
                Circle
            </h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="circle_fill_colour">
                        <h2 class="size_14 hero_darkgrey">Circle Fill Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="circle_fill_colour" name="circle_fill_colour" data-node_val="map_drawing/circleOptions/fillColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="circle_fill_opacity">
                        <h2 class="size_14 hero_darkgrey">Cirlce Fill Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="circle_fill_opacity" name="circle_fill_opacity" data-node_val="map_drawing/circleOptions/fillOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="circle_fill_opacity" id="circle_fill_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="circle_stroke_colour">
                        <h2 class="size_14 hero_darkgrey">Circle Stroke Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="circle_stroke_colour" name="circle_stroke_colour" data-node_val="map_drawing/circleOptions/strokeColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="circle_stroke_opacity">
                        <h2 class="size_14 hero_darkgrey">Circle Stroke Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="circle_stroke_opacity" name="circle_stroke_opacity" data-node_val="map_drawing/circleOptions/strokeOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="circle_stroke_opacity" id="circle_stroke_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="circle_stroke_weight">
                        <h2 class="size_14 hero_darkgrey">Circle Stroke Weight</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                	<div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="px" id="circle_stroke_weight" name="circle_stroke_weight" data-node_val="map_drawing/circleOptions/strokeWeight" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="circle_stroke_weight" id="circle_stroke_weight_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<h2 class="hero_green size_18">
                Polygon
            </h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polygon_fill_colour">
                        <h2 class="size_14 hero_darkgrey">Polygon Fill Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="polygon_fill_colour" name="polygon_fill_colour" data-node_val="map_drawing/polygonOptions/fillColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polygon_fill_opacity">
                        <h2 class="size_14 hero_darkgrey">Polygon Fill Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="polygon_fill_opacity" name="polygon_fill_opacity" data-node_val="map_drawing/polygonOptions/fillOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polygon_fill_opacity" id="polygon_fill_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polygon_stroke_colour">
                        <h2 class="size_14 hero_darkgrey">Polygon Stroke Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="polygon_stroke_colour" name="polygon_stroke_colour" data-node_val="map_drawing/polygonOptions/strokeColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polygon_stroke_opacity">
                        <h2 class="size_14 hero_darkgrey">Polygon Stroke Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="polygon_stroke_opacity" name="polygon_stroke_opacity" data-node_val="map_drawing/polygonOptions/strokeOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="polygon_stroke_opacity" id="polygon_stroke_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="polygon_stroke_weight">
                        <h2 class="size_14 hero_darkgrey">Polygon Stroke Weight</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                	<div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="px" id="polygon_stroke_weight" name="polygon_stroke_weight" data-node_val="map_drawing/polygonOptions/strokeWeight" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="polygon_stroke_weight" id="polygon_stroke_weight_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<h2 class="hero_green size_18">
                Rectangle
            </h2>
        	<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rectangle_fill_colour">
                        <h2 class="size_14 hero_darkgrey">Rectangle Fill Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="rectangle_fill_colour" name="rectangle_fill_colour" data-node_val="map_drawing/rectangleOptions/fillColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rectangle_fill_opacity">
                        <h2 class="size_14 hero_darkgrey">Rectangle Fill Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="rectangle_fill_opacity" name="rectangle_fill_opacity" data-node_val="map_drawing/rectangleOptions/fillOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="rectangle_fill_opacity" id="rectangle_fill_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rectangle_stroke_colour">
                        <h2 class="size_14 hero_darkgrey">Rectangle Stroke Color</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_3">
                    <input type="text" class="color_picker" id="rectangle_stroke_colour" name="rectangle_stroke_colour" data-node_val="map_drawing/rectangleOptions/strokeColor">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rectangle_stroke_opacity">
                        <h2 class="size_14 hero_darkgrey">Rectangle Stroke Opacity</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
	                <div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="dec" id="rectangle_stroke_opacity" name="rectangle_stroke_opacity" data-node_val="map_drawing/rectangleOptions/strokeOpacity" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="1" data-step="0.1" data-bind_link="rectangle_stroke_opacity" id="rectangle_stroke_opacity_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="rectangle_stroke_weight">
                        <h2 class="size_14 hero_darkgrey">Rectangle Stroke Weight</h2>
                        <p class="size_12 hero_grey"></p>
                    </label>
                </div>
                <div class="hero_col_6">
                	<div class="hero_col_12" style="padding:0;">
                        <div class="hero_col_2">
                            <input type="text" data-size="lrg" data-hero_type="px" id="rectangle_stroke_weight" name="rectangle_stroke_weight" data-node_val="map_drawing/rectangleOptions/strokeWeight" readonly>
                        </div>
                        <div class="hero_col_4">
                            <div class="hero_slider" data-min="0" data-max="20" data-step="1" data-bind_link="rectangle_stroke_weight" id="rectangle_stroke_weight_slider"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="clear:both;"></div>
        </div>
        
	</div>
</div>