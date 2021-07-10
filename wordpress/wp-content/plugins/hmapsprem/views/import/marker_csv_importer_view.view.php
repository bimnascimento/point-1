<script type="text/javascript" src="<?php echo htmlspecialchars($_GET['vp'], ENT_QUOTES, 'UTF-8'); ?>js/marker_csv_importer_view.view.js" data-cfasync="false"></script>
<div class="hero_views">
    <div class="hero_col_12">
    	<h1 class="hero_red size_18">
            Import Map<br />
            <strong class="size_11 hero_grey">Upload a map marker CSV file</strong>
        </h1>
        
        <div class="hero_section_holder hero_grey size_14"> 
            <div class="hero_col_12">
                <h3 class="hero_grey">Select Destination Map</h3>
                <div class="hero_col_12">
                    <select data-size="sml" id="destination_map_selection" name="destination_map_selection" data-height="200">
                    </select>
                </div>
            </div>
        </div>
        
        <div class="hero_section_holder hero_grey size_14" id="upload_holding_container" style="display:none;">
            <div class="hero_col_12">
                <h3 class="hero_grey">Upload</h3>
                <p>
                    Click "Choose File" and select your CSV file. The markers will be automatically added to the selected map.<br>
                </p>
            </div>
            <div class="marker_csv_import_upload_holder"></div>
            <div class="hero_col_12" style="padding-top:0; display:none;" id="upload_results_holder">
                <h3 class="hero_grey">Results</h3>
                <div class="hero_col_12" style="padding-bottom:0;">
                    <div class="hero_col_6">
                        <p>Total Rows Processed:</p>
                    </div>
                    <div class="hero_col_6">
                        <p id="importer_total_processed">100</p>
                    </div>
                </div>
                <div class="hero_col_12 hero_green" style="padding-bottom:0;">
                    <div class="hero_col_6">
                        <p>Rows Successfully Imported:</p>
                    </div>
                    <div class="hero_col_6">
                        <p id="importer_success_processed">100</p>
                    </div>
                </div>
                <div class="hero_col_12 hero_red">
                    <div class="hero_col_6">
                        <p>Rows Containing Errors:</p>
                    </div>
                    <div class="hero_col_6">
                        <p id="importer_errors_processed">100</p>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <div class="hero_note" style="display:none;" id="upload_errors_holder">
                    <p class="size_12">
                        Click <a id="download_error_file_link" target="_blank">here</a> to download a CSV file with all rows containing errors.
                        <script type="text/javascript" data-cfasync="false">
                            jQuery('#download_error_file_link').attr('href', plugin_url +'_marker_csv_import_uploads/import_errors.csv');
                        </script>
                    </p>
                </div>
            </div>
        </div>
        
	</div>
</div>