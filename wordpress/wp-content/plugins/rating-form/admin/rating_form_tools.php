<?php
function rating_form_tools() {
	$tools_options = isset($_POST['tools']) ? $_POST['tools'] : get_option(Rating_Form::PAGE_TOOLS_RATING_SLUG);
	if (isset($_POST['submit'])) {
		update_option(Rating_Form::PAGE_TOOLS_RATING_SLUG, $_POST['tools']);
		echo '<div class="updated"><p>'. __( 'Successfully saved.', 'rating-form' ) .'</p></div>';
	}
	
	if (isset($_POST['export'])) {
		$exportArray = array();
		$type = $_POST['type'];
		$dateFrom = empty($_POST['date-from']) ? '1970-01-01' : $_POST['date-from'];
		$dateTo = empty($_POST['date-to']) ? date('Y-m-d') : $_POST['date-to'];
		$postID = (empty($_POST['post-id']) ? '' : "post_id = '". $_POST['post-id'] . "' AND ");
		
		global $wpdb;
		if ($type == "star") {
			$sql = "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated REGEXP '^[0-9]+$' AND " . $postID . "date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";
		} else if ($type == "tud") {
			$sql = "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE (rated = '1u' OR rated = '1d') AND " . $postID . "date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "'";
		}
		$dbFromTo = $wpdb->get_results( $sql, ARRAY_A );
		$dbFromTo_num_rows = $wpdb->num_rows;
		if ($dbFromTo_num_rows > 0) {
			$headerRow = __( 'Rating ID', 'rating-form' ) . ',' . 
					__( 'Post ID', 'rating-form' ) . ',' . 
					__( 'Comment ID', 'rating-form' ) . ',' . 
					__( 'Custom ID', 'rating-form' ) . ',' . 
					__( 'Term ID', 'rating-form' ) . ',' . 
					__( 'IP', 'rating-form' ) . ',' . 
					__( 'Rating', 'rating-form' ) . ',' . 
					__( 'User', 'rating-form' ) . ',' . 
					__( 'Date', 'rating-form' );
			
			$exportArray = array( $headerRow );
			foreach($dbFromTo as $rowDb) {
				$currentRow = $rowDb['rate_id'] . ',' . 
								$rowDb['post_id'] . ',' . 
								$rowDb['comment_id'] . ',' . 
								$rowDb['custom_id'] . ',' . 
								$rowDb['term_id'] . ',' . 
								$rowDb['ip'] . ',' . 
								$rowDb['rated'] . ',' . 
								$rowDb['user'] . ',' . 
								$rowDb['date'];
				array_push( $exportArray, $currentRow );
			}
			$fp = fopen('php://output', 'w'); 
			if ($fp && count($dbFromTo) > 0) 
			{
				ob_end_clean();
				header('Content-Type: text/csv; charset=utf-8');
				header('Content-Disposition: attachment; filename=rating-form-results-' . $dateFrom . '-' . $dateTo . '.csv');
				header('Pragma: no-cache');    
				header('Expires: 0');
				foreach ($exportArray as $row) {
					fputcsv($fp, explode(',', $row ));
				}
				fpassthru($fp);
				fclose($fp);
				die();
			}
		} else {
			echo '<div class="error"><p>'. sprintf( __( 'No records found from %1$s to %2$s', 'rating-form' ), $dateFrom, $dateTo ) .'</p></div>';
		}
	}
	
	if ( isset($_POST["import"]) ) {
		$importSFL = $_POST['import-sfl']; // SFL = skip first line
		$importColumn = isset($_POST['importColumn']) ? $_POST['importColumn'] : array();
		$totalColumns = count(array_unique($importColumn));
		
		if (empty($_FILES["import-file"]["name"])) {
			 echo '<div class="error"><p>'. __( 'No import file found', 'rating-form' ) .'</p></div>';
		} else {
			$ext = pathinfo($_FILES["import-file"]["name"], PATHINFO_EXTENSION);
			if ($_FILES["import-file"]["error"] > 0) {
				echo '<div class="error"><p>'. sprintf( __( 'Import file returned error code: %d', 'rating-form' ), $_FILES["import-file"]["error"]) .'</p></div>';
			} else if ($ext != "csv") {
				echo '<div class="error"><p>'. __( 'Wrong extension. Allowed extension: .csv', 'rating-form' ) .'</p></div>';
			} else {
				$fh = fopen($_FILES["import-file"]["tmp_name"], 'r+');
				$lineRow = array();
				$iR = 0;
				while (($row = fgetcsv($fh, 1000)) !== FALSE) {
					if ($iR > 0 && $importSFL == 1) {
						$lineRow[] = $row;
					} else if ($iR >= 0 && $importSFL == 0) {
						$lineRow[] = $row;
					}
					$iR++;
				}
				if ($totalColumns != count($lineRow[0])) {
					echo '<div class="error"><p>'. sprintf( __( 'Columns you have selected does not match from the imported file.<br><strong>Columns (selected):</strong> %1$s<br><strong>Columns (imported):</strong> %2$s', 'rating-form' ), $totalColumns, count($lineRow[0])) .'</p></div>';
				} else {
					global $wpdb;
					// Rows
					for ($i = 0; $i < count($lineRow); $i++) {
						//echo '<pre>';
						// Columns + rows combined
						$arrCRCombine = array_combine($importColumn, $lineRow[$i]);
						// Insert rating
						$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_RATED, $arrCRCombine );
						//echo '</pre>';
					}
					echo '<div class="updated"><p>'. sprintf( __( 'Successfully imported %d rows.', 'rating-form' ), count($lineRow)) .'</p></div>';
				}
			}
		}
	}
?>
	<div class="wrap rf_wrap">
		<?php Rating_Form::admin_menus(); ?>
		<form id="rating_form_tools" method="post" class="rf_content_inner" enctype="multipart/form-data">
			<div class="rf_content">
				<h3 class="rf_content_title"><?php _e( 'Quick Add - GFXFree.Net', 'rating-form' ); ?></h3>
				<div class="rf_content_inner">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="tools[before_content]"><?php _e( 'Before Content', 'rating-form' ); ?></label></th>
								<td><textarea name="tools[before_content][content]" rows="5" cols="50" placeholder="<?php _e( 'Rating Form Shortcode', 'rating-form'); ?>"><?php echo empty($tools_options['before_content']['content']) ? '' : htmlentities(stripslashes($tools_options['before_content']['content'])); ?></textarea>
								<br /><input type="text" name="tools[before_content][paragraph]" value="<?php echo empty($tools_options['before_content']['paragraph']) ? '' : intval($tools_options['before_content']['paragraph']); ?>" placeholder="<?php _e( 'Set paragraph position', 'rating-form' ); ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="tools[after_content]"><?php _e( 'After Content', 'rating-form' ); ?></label></th>
								<td><textarea name="tools[after_content][content]" rows="5" cols="50" placeholder="<?php _e( 'Rating Form Shortcode', 'rating-form'); ?>"><?php echo empty($tools_options['after_content']['content']) ? '' : htmlentities(stripslashes($tools_options['after_content']['content'])); ?></textarea>
								<br /><input type="text" name="tools[after_content][paragraph]" value="<?php echo empty($tools_options['after_content']['paragraph']) ? '' : intval($tools_options['after_content']['paragraph']); ?>" placeholder="<?php _e( 'Set paragraph position', 'rating-form' ); ?>" /></td>
							</tr>
						</tbody>
					</table>
					<p class="description"><?php _e( '<strong>Tip!</strong><br />You can select which post types are allowed on settings page.<br />Use before_content or after_content attribute to add custom before or after a rating form.', 'rating-form' ); ?></p>
					<p class="description"><?php _e( 'Example shortcode: ', 'rating-form' ); ?><input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;1&quot;]" /></p>
					<?php
						submit_button( __( 'Save', 'rating-form' ), 'primary' );
					?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="rf_content">
				<h3 class="rf_content_title"><?php _e( 'Import', 'rating-form' ); ?></h3>
				<div class="rf_content_inner">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="import-file"><?php _e( 'CSV File', 'rating-form' ); ?></label></th>
								<td>
									<input type="file" name="import-file" />
									<p class="description"><?php _e( 'Select a .csv file', 'rating-form' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="columns"><?php _e( 'Columns', 'rating-form' ); ?></label></th>
								<td>
									<table>
										<tbody>
											<tr>
												<td id="rfToolsIC">
													<label><input type="checkbox" name="importColumn[]" value="post_id" />Post ID</label><br>
													<label><input type="checkbox" name="importColumn[]" value="comment_id" />Comment ID</label><br>
													<label><input type="checkbox" name="importColumn[]" value="custom_id" />Custom ID</label><br>
													<label><input type="checkbox" name="importColumn[]" value="term_id" />Term ID</label><br>
													<label><input type="checkbox" name="importColumn[]" value="ip" />IP</label><br>
													<label><input type="checkbox" name="importColumn[]" value="rated" />Rating</label><br>
													<label><input type="checkbox" name="importColumn[]" value="user" />User ID</label><br>
													<label><input type="checkbox" name="importColumn[]" value="date" />Date</label><br>
													<input type="button" name="importColumnAll" value="<?php _e( 'Select All', 'rating-form'); ?>" />
												</td>
											</tr>
										</tbody>
									</table>
									<p class="description"><?php _e( 'Rating ID will be auto incremented (no need to add)<br>Keep the columns in the same order as above<br>Select the columns you want to import', 'rating-form' ); ?></p>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="import-sfl"><?php _e( 'Skip first line', 'rating-form' ); ?></label></th>
								<td>
									<select name="import-sfl">
										<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
										<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									</select>
									<p class="description"><?php _e( 'Do you want to skip first line? e.g. column titles', 'rating-form' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
					<?php 
						submit_button( __( 'Import', 'rating-form' ), 'primary', 'import' );
					?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="rf_content">
				<h3 class="rf_content_title"><?php _e( 'Export', 'rating-form' ); ?></h3>
				<div class="rf_content_inner">
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row"><label for="date-from"><?php _e( 'From', 'rating-form' ); ?></label></th>
								<td><input type="text" class="datePicker" autocomplete="off" name="date-from" value="<?php echo (isset($_POST['date-from']) ? $_POST['date-from'] : ''); ?>" placeholder="<?php _e( 'yyyy-mm-dd', 'rating-form' ); ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="date-to"><?php _e( 'To', 'rating-form' ); ?></label></th>
								<td><input type="text" class="datePicker" autocomplete="off" name="date-to" value="<?php echo (isset($_POST['date-to']) ? $_POST['date-to'] : ''); ?>" placeholder="<?php _e( 'yyyy-mm-dd', 'rating-form' ); ?>" /></td>
							</tr>
							<tr>
								<th scope="row"><label for="type"><?php _e( 'Rating Value', 'rating-form' ); ?></label></th>
								<td>
									<select name="type">
										<option value="star"><?php _e( '1 to 10', 'rating-form' ); ?></option>
										<option value="tud"><?php _e( '1 Up and 1 Down', 'rating-form' ); ?></option>
									</select>
								</td>
							</tr>
							<tr>
								<th scope="row"><label for="post-id"><?php _e( 'Post / Pages', 'rating-form' ); ?></label></th>
								<td>
									<select name="post-id">
										<option value="0"><?php _e( 'All Post / Pages', 'rating-form' ); ?></option>
										<?php	
										global $wpdb;
										
										$rows = $wpdb->get_results( 'SELECT DISTINCT post_id FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_RATED, ARRAY_A );
					
										foreach ( $rows as $row ) {
											$post = get_post( $row['post_id'] );
											if (!empty($post)) {
											?>
											<option value="<?php echo $post->ID; ?>" <?php isset($_POST['post-id']) ? selected($_POST['post-id'], $post->ID) : ''; ?>><?php echo get_the_title( $post->ID ); ?></option>
										<?php
											}
										}
										?>
									</select>
								</td>
							</tr>
						</tbody>
					</table>
					<p class="description"><?php _e( 'Select a date to export rating results to .csv', 'rating-form' ); ?></p>
					<?php 
						submit_button( __( 'Export', 'rating-form' ), 'primary', 'export' );
					?>
				</div>
			</div>
		</form>
	</div>
<?php 
}
?>