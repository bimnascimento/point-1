<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/maps_developers.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Developers<br />
            <strong class="size_11 hero_grey">Enable advanced functionality</strong>
        </h1>
        
        <div class="hero_section_holder hero_grey size_14"> 
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    Events
                </h2>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="map_initialised_method">
                        <h2 class="size_14 hero_darkgrey">Map Initialized Method</h2>
                        <p class="size_12 hero_grey">Specify the method that will be called when a the "tiles_loaded" event is triggered (e.g. "map_initialised").</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" id="map_initialised_method" name="map_initialised_method" maxlength="50" data-node_val="map_developers/map_initialised_method">
                </div>
            </div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14"> 
			<div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="javascript_callback">
                        <h2 class="size_14 hero_darkgrey">Marker "onclick" Event</h2>
                        <p class="size_12 hero_grey">
                        	With this feature enabled, you will be able to add a JavaScript method that will be called onclick of a location marker.
                        </p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="checkbox" data-size="lrg" id="javascript_callback" name="javascript_callback" value="1" class="hide_switch" data-node_val="map_developers/javascript_callback">
                </div>
            </div>
            <div class="hide_container">
            	<div class="internal">
                    <div class="hero_col_12">
                        <div class="hero_col_6">
                            <label for="callback_method">
                                <h2 class="size_14 hero_darkgrey">Javascript Method</h2>
                                <p class="size_12 hero_grey">Specify the method that will be called when a location marker is clicked</p>
                            </label>
                        </div>
                        <div class="hero_col_6">
                            <input type="text" data-size="sml" id="callback_method" name="callback_method" maxlength="30" data-node_val="map_developers/callback_method">
                        </div>
                    </div>
                    <div class="hero_col_12">
<!--BEGIN: sample code-->
<span class="hero_red size_12">Example method</span>
<pre class="size_12">
//show location marker data
function show_location_marker_data(marker_data){
    //log location marker data to the console
    console.log(marker_data);
    //example output
    {"marker_id": 123, "location_title": "my location marker", "custom_param": "my location"}
}
</pre>
<!--END: sample code-->
                    </div>
                    <div class="hero_col_12">
                        <p>
                            In the above example, you would enter "show_location_marker_data" into the "JavaScript Method" input. The JavaScript method will be passed a JSON object containing 3 parameters when called - 
                            The "marker_id" (INT: unique to hmapspro), the "location_title" (STRING: pulled from "Edit Location Marker") and a "custom_param" (STRING: pulled from "Custom Parameter" in "Edit Location Marker"). 
                        </p>
                        <br>
                        <p>
                        	If the "Location Title" or the "Custom Parameter" are left blank, a <b>null</b> value will be supplied.
                        </p>
                    </div>
                    <div class="hero_col_12">
<!--BEGIN: sample code-->
<span class="hero_red size_12">Example output without "Location Title" or "Custom Parameter"</span>
<pre class="size_12">
//example output
{"marker_id": 123, "location_title": null, "custom_param": null}
</pre>
<!--END: sample code-->
                    </div>
                    <div style="clear:both;"></div>
                </div>
            </div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14">
        	<div class="hero_col_12">
                <h2 class="hero_green size_18">
                    CSS
                </h2>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="map_css_class">
                        <h2 class="size_14 hero_darkgrey">Map CSS Class</h2>
                        <p class="size_12 hero_grey">Add a CSS class to the map container to assist with advanced customization options</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" id="map_css_class" name="map_css_class" maxlength="50" data-node_val="map_developers/map_css_class">
                </div>
            </div>
            <div class="hero_col_12">
                <div class="hero_col_6">
                    <label for="category_selector_css_class">
                        <h2 class="size_14 hero_darkgrey">Category Selector CSS Class</h2>
                        <p class="size_12 hero_grey">Add a CSS class to the category selector to assist with advanced customization options</p>
                    </label>
                </div>
                <div class="hero_col_6">
                    <input type="text" data-size="sml" id="category_selector_css_class" name="category_selector_css_class" maxlength="50" data-node_val="map_developers/category_selector_css_class">
                </div>
            </div>
        </div>
	</div>
</div>