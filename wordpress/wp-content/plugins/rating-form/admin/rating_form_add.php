<?php
//Add Rating
function rating_form_add() {
	if (isset($_POST['submit'])) {
		$msg_success = null;
		$msg_error = array();
		$new_rating_id = null;
		$style_content = null;
		$type = $_POST['type'];
		$form_name = $_POST['form_name'];
		$restrict_ip = $_POST['restrict_ip'];
		$user_logged_in = $_POST['user_logged_in'];
		$txt_ty = $_POST['txt_ty'];
		$txt_rated = $_POST['txt_rated'];
		$txt_login = $_POST['txt_login'];
		$txt_limit = $_POST['txt_limit'];
		$txt_edit_rating = $_POST['txt_edit_rating'];
		$font_size = isset($_POST['font_size']) ? $_POST['font_size'] : null;
		$font_size_text = isset($_POST['font_size_text']) ? $_POST['font_size_text'] : null;
		$text = $_POST['text'];
		$max = isset($_POST['max']) ? $_POST['max'] : null;
		//Style star
		$font_color = isset($_POST['font_color']) ? $_POST['font_color'] : null;
		$font_color_text = isset($_POST['font_color_text']) ? $_POST['font_color_text'] : null;
		$font_hover_color = isset($_POST['font_hover_color']) ? $_POST['font_hover_color'] : null;
		$background_def_text = isset($_POST['background_def_text']) ? $_POST['background_def_text'] : null;
		//Style thumbs up
		$tu_font_color = isset($_POST['tu_font_color']) ? $_POST['tu_font_color'] : null;
		$tu_font_color_text = isset($_POST['tu_font_color_text']) ? $_POST['tu_font_color_text'] : null;
		$tu_font_hover_color = isset($_POST['tu_font_hover_color']) ? $_POST['tu_font_hover_color'] : null;
		$tu_background_def_text = isset($_POST['tu_background_def_text']) ? $_POST['tu_background_def_text'] : null;
		//Style thumbs down
		$td_font_color = isset($_POST['td_font_color']) ? $_POST['td_font_color'] : null;
		$td_font_color_text = isset($_POST['td_font_color_text']) ? $_POST['td_font_color_text'] : null;
		$td_font_hover_color = isset($_POST['td_font_hover_color']) ? $_POST['td_font_hover_color'] : null;
		$td_background_def_text = isset($_POST['td_background_def_text']) ? $_POST['td_background_def_text'] : null;
		//Rating form upload dir
		$upload_dir = wp_upload_dir();
		$stylefolder = $upload_dir['basedir'].'/rating-form/css/';
		//Custom Image files
		$icon_empty = empty($icon_empty['name']) ? null : $_FILES['icon_empty'];
		$icon_full = empty($icon_full['name']) ? null : $_FILES['icon_full'];
		$icon_half = empty($icon_half['name']) ? null : $_FILES['icon_half'];

		if (isset($type) && isset($restrict_ip) && isset($user_logged_in) && isset($txt_ty) && isset($txt_rated) && isset($txt_login) && isset($txt_limit) && isset($txt_edit_rating) && isset($font_size) && isset($font_size_text) && isset($text)) {

			if (!is_writable($stylefolder)) {
				$msg_error[] = sprintf( __( 'The following path is not writable: %s', 'rating-form' ), $stylefolder );
			}

			if (intval($type) == 0) {
				$icon_empty_ext = explode('.', strtolower($_FILES['icon_empty']['name']));
				if (empty($_FILES['icon_empty']['name'])) {
					$msg_error[] = __( "No 'empty icon' selected. <em>Don't forget to select all icons again.</em>", 'rating-form' );
				} else if ($_FILES['icon_empty']['error']) {
					$msg_error[] = '<strong>Icon Empty</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_empty']['error'];
				} else if ($icon_empty_ext[1] != 'png') {
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_empty_ext[0], $icon_empty_ext[1] );
				}

				$icon_full_ext = explode('.', strtolower($_FILES['icon_full']['name']));
				if (empty($_FILES['icon_full']['name'])) {
					$msg_error[] = __( "No 'full icon' selected. <em>Don't forget to select all icons again.</em>", 'rating-form' );
				} else if ($_FILES['icon_full']['error']) {
					$msg_error[] = '<strong>Icon Empty</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_full']['error'];
				} else if ($icon_full_ext[1] != 'png') {
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_full_ext[0], $icon_full_ext[1] );
				}

				$icon_half_ext = explode('.', strtolower($_FILES['icon_half']['name']));
				if (empty($_FILES['icon_half']['name'])) {
					$msg_error[] = __( "No'half icon' selected. <em>Don't forget to select all icons again.</em>", 'rating-form' );
				} else if ($_FILES['icon_half']['error']) {
					$msg_error[] = '<strong>Icon Empty</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_half']['error'];
				} else if ($icon_half_ext[1] != 'png') {
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_half_ext[0], $icon_half_ext[1] );
				}
			}

			if(strlen($form_name) > 50) {
				$msg_error[] = sprintf( __( '<strong>Name</strong> has %s characters. Allowed: 50 characters', 'rating-form' ), strlen($form_name));
			}

			if (count($msg_error) == 0) {

				global $wpdb;

				$wpdb->insert( $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM, array(
						'form_name' => $form_name,
						'type' => $type,
						'max' => intval($max),
						'restrict_ip' => intval($restrict_ip),
						'user_logged_in' => intval($user_logged_in),
						'txt_ty' => $txt_ty,
						'txt_rated' => $txt_rated,
						'txt_login' => $txt_login,
						'txt_limit' => $txt_limit,
						'txt_edit_rating' => $txt_edit_rating
				) );

				$new_rating_id .= $wpdb->insert_id;

				if (intval($type) == 0) {
					//upload custom icons
					wp_mkdir_p( $upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id );
					move_uploaded_file($_FILES['icon_empty']['tmp_name'], $upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-empty.png');
					move_uploaded_file($_FILES['icon_full']['tmp_name'], $upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-full.png');
					move_uploaded_file($_FILES['icon_half']['tmp_name'], $upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-half.png');
				}

				//titles
				if (count($text) > 0) {
					$text_i = 0;
					foreach ($text as $title_id) {
						$text_i++;
						if ($text_i <= $max) {
							$title_exist = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_id = '".$title_id."' AND title_form_id = '".$new_rating_id."'", ARRAY_A );
							$title_exist_num_rows = $wpdb->num_rows;
							// get title info
							$title_pos = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$title_id."'", ARRAY_A );
							if ($title_exist_num_rows == 0) {
								$wpdb->insert( $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES, array(
										'title_form_id' => $new_rating_id,
										'title_id' => $title_id,
										'position' => $title_pos['position']
								) );
							}
						}
					}
				}

				$stylefile = fopen($stylefolder. 'rating_form_'.$new_rating_id.'.css', 'w') or die("Unable to create file! Check folder permission.");

				// Create style for Custom forms
				if (Rating_Form::form_types(intval($type), 'type') == "star") { // Create style for Star rating and similar

					$style_content .= '.rating_form_'.$new_rating_id.' {'."\r\n";
					$style_content .= '	display: inline-block;'."\r\n";
					$style_content .= '}'."\r\n";

					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form {'."\r\n";
					$style_content .= '	margin: 0 !important;'."\r\n";
					$style_content .= '	padding: 0 !important;'."\r\n";
					$style_content .= '}'."\r\n";

					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form.cursor [class^="cyto-"],'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form.cursor [class*=" cyto-"] {'."\r\n";
					$style_content .= '	cursor: pointer;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .update {'."\r\n";
					$style_content .= '	opacity: 0.5;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"] {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	margin: 0;'."\r\n";
					$style_content .= '	padding: 0;'."\r\n";
					$style_content .= '	padding-left: 5px;'."\r\n";
					$style_content .= '	line-height: .8em;'."\r\n";
					if (intval($type) != 0) {
					$style_content .= '	color: '.$font_color.';'."\r\n";
					}
					$style_content .= '	font-size: '.$font_size.';'."\r\n";
					$style_content .= '	border: 0;'."\r\n";
					$style_content .= '	width: auto;'."\r\n";
					$style_content .= '}'."\r\n";
					if (intval($type) != 0) {
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"].hover {'."\r\n";
					$style_content .= '	color: '.$font_hover_color.';'."\r\n";
					$style_content .= '}'."\r\n";
					}
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"]:first-child {'."\r\n";
					$style_content .= '	padding-left: 0;'."\r\n";
					$style_content .= '}'."\r\n";
					if (intval($type) == 0) {
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"] * {'."\r\n";
					} else {
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"]:before {'."\r\n";
					}
					$style_content .= '	vertical-align: middle;'."\r\n";
					$style_content .= '}'."\r\n";
					if (intval($type) == 0) {
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form img {'."\r\n";
					$style_content .= '	width: '.$font_size.';'."\r\n";
					$style_content .= '	height: '.$font_size.';'."\r\n";
					$style_content .= '	border: 0;'."\r\n";
					$style_content .= '	box-shadow: none;'."\r\n";
					$style_content .= '}'."\r\n";
					}
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .def {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	background-color: '.$background_def_text.';'."\r\n";
					$style_content .= '	padding: 7px 5px;'."\r\n";
					$style_content .= '	margin: 0 0 0 5px;'."\r\n";
					$style_content .= '	border-radius: 5px;'."\r\n";
					$style_content .= '	-webkit-border-radius: 5px;'."\r\n";
					$style_content .= '	-moz-border-radius: 5px;'."\r\n";
					$style_content .= '	line-height: 1em;'."\r\n";
					$style_content .= '	color: '.$font_color_text.';'."\r\n";
					$style_content .= '	font-size: '.$font_size_text.';'."\r\n";
					$style_content .= '	width: auto;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_total:hover {'."\r\n";
					$style_content .= '	cursor: pointer;'."\r\n";
					$style_content .= '	font-weight: bold;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .thankyou {'."\r\n";
					$style_content .= '	background-color: #cffa90 !important;'."\r\n";
					$style_content .= '	color: #51711a !important;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rated {'."\r\n";
					$style_content .= '	display: none;'."\r\n";
					$style_content .= '	background-color: #faf190 !important;'."\r\n";
					$style_content .= '	color: #716d1a !important;'."\r\n";
					$style_content .= '}'."\r\n";

				} else if (Rating_Form::form_types(intval($type), 'type') == "tud") { // Create style for Thumbs Up/Down and similar

					$style_content .= '.rating_form_'.$new_rating_id.' {'."\r\n";
					$style_content .= '	display: inline-block;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form {'."\r\n";
					$style_content .= '	margin: 0 !important;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form.cursor [class^="cyto-"],'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form.cursor [class*=" cyto-"] {'."\r\n";
					$style_content .= '	cursor: pointer;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .update {'."\r\n";
					$style_content .= '	opacity: 0.5;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"] {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	padding: 0;'."\r\n";
					$style_content .= '	padding-right: 5px;'."\r\n";
					$style_content .= '	margin: 0;'."\r\n";
					$style_content .= '	line-height: .8em;'."\r\n";
					$style_content .= '	font-size: '.$font_size.'; /* default rating size */'."\r\n";
					$style_content .= '	border: 0;'."\r\n";
					$style_content .= '	width: auto;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"]:last-child {'."\r\n";
					$style_content .= '	margin-right: 0;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form [id^="rate_"]:before {'."\r\n";
					$style_content .= '	vertical-align: middle;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .def {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	padding: 7px 5px;'."\r\n";
					$style_content .= '	margin: 0;'."\r\n";
					$style_content .= '	border-radius: 5px;'."\r\n";
					$style_content .= '	line-height: 1em;'."\r\n";
					$style_content .= '	background-color: #dddddd;'."\r\n";
					$style_content .= '	color: #777777;'."\r\n";
					$style_content .= '	font-size: '.$font_size_text.'; /* default text size */'."\r\n";
					$style_content .= '	width: auto;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_total:hover {'."\r\n";
					$style_content .= '	cursor: pointer;'."\r\n";
					$style_content .= '	font-weight: bold;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .up_rated_txt {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	padding: 7px 5px;'."\r\n";
					$style_content .= '	margin: 0;'."\r\n";
					$style_content .= '	margin-right: 5px;'."\r\n";
					$style_content .= '	border-radius: 5px;'."\r\n";
					$style_content .= '	line-height: 1em;'."\r\n";
					$style_content .= '	background-color: '.$tu_background_def_text.';'."\r\n";
					$style_content .= '	color: '.$tu_font_color_text.';'."\r\n";
					$style_content .= '	font-size: '.$font_size_text.'; /* default text size */'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .down_rated_txt {'."\r\n";
					$style_content .= '	float: left;'."\r\n";
					$style_content .= '	list-style-type: none !important;'."\r\n";
					$style_content .= '	padding: 7px 5px;'."\r\n";
					$style_content .= '	margin: 0;'."\r\n";
					$style_content .= '	margin-right: 5px;'."\r\n";
					$style_content .= '	border-radius: 5px;'."\r\n";
					$style_content .= '	line-height: 1em;'."\r\n";
					$style_content .= '	background-color: '.$td_background_def_text.';'."\r\n";
					$style_content .= '	color: '.$td_font_color_text.';'."\r\n";
					$style_content .= '	font-size: '.$font_size_text.'; /* default text size */'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .up_rated {'."\r\n";
					$style_content .= '	color: '.$tu_font_color.';'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .down_rated {'."\r\n";
					$style_content .= '	color: '.$td_font_color.';'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .up_rated.hover {'."\r\n";
					$style_content .= '	color: '.$tu_font_hover_color.';'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .down_rated.hover {'."\r\n";
					$style_content .= '	color: '.$td_font_hover_color.';'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .thankyou {'."\r\n";
					$style_content .= '	background-color: #cffa90 !important;'."\r\n";
					$style_content .= '	color: #51711a !important;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rated {'."\r\n";
					$style_content .= '	display: none;'."\r\n";
					$style_content .= '	background-color: #faf190 !important;'."\r\n";
					$style_content .= '	color: #716d1a !important;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .hide {'."\r\n";
					$style_content .= '	display: none;'."\r\n";
					$style_content .= '}'."\r\n";
					$style_content .= '.rating_form_'.$new_rating_id.' .rating_form .show {'."\r\n";
					$style_content .= '	display: list-item !important;'."\r\n";
					$style_content .= '}'."\r\n";

				}

				// General style
				$style_content .= '.rating_form_'.$new_rating_id.' .tooltip {'."\r\n";
				$style_content .= '	position: absolute;'."\r\n";
				$style_content .= '	display: block;'."\r\n";
				$style_content .= '	border-radius: 5px;'."\r\n";
				$style_content .= '	-webkit-border-radius: 5px;'."\r\n";
				$style_content .= '	-moz-border-radius: 5px;'."\r\n";
				$style_content .= '	padding: 10px;'."\r\n";
				$style_content .= '	line-height: 1em;'."\r\n";
				$style_content .= '	text-align: center;'."\r\n";
				$style_content .= '	color: #ffffff;'."\r\n";
				$style_content .= '	background: #000000;'."\r\n";
				$style_content .= '	font-size: 13px;'."\r\n";
				$style_content .= '	top: 40px; /* Set height between icon and tooltip in px  */'."\r\n";
				$style_content .= '	z-index: 2;'."\r\n";
				$style_content .= '	opacity: 1;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .tooltip:after {'."\r\n";
				$style_content .= "	content: '';\r\n";
				$style_content .= '	position: absolute;'."\r\n";
				$style_content .= '	top: 100%;'."\r\n";
				$style_content .= '	left: 50%;'."\r\n";
				$style_content .= '	width: 0;'."\r\n";
				$style_content .= '	height: 0;'."\r\n";
				$style_content .= '	margin-left: -6px;'."\r\n";
				$style_content .= '	border-top: 6px solid #000000;'."\r\n";
				$style_content .= '	border-right: 6px solid transparent;'."\r\n";
				$style_content .= '	border-left: 6px solid transparent;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .title {'."\r\n";
				$style_content .= '	color: #555555;'."\r\n";
				$style_content .= '	font-size: 14px;'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '	padding-left: 5px;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats {'."\r\n";
				$style_content .= '	display: none;'."\r\n";
				$style_content .= '	position: absolute;'."\r\n";
				$style_content .= '	background-color: #ffffff;'."\r\n";
				$style_content .= '	border: 1px solid #9b9b9b;'."\r\n";
				$style_content .= '	font-size: 13px;'."\r\n";
				$style_content .= '	color: #777777;'."\r\n";
				$style_content .= '	box-shadow: 0 2px 6px rgba(100, 100, 100, 0.3);'."\r\n";
				$style_content .= '	padding: 0;'."\r\n";
				$style_content .= '	z-index: 1;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats .rf_stats_header {'."\r\n";
				$style_content .= '	background-color: #c7c7c7;'."\r\n";
				$style_content .= '	color: #595959;'."\r\n";
				$style_content .= '	padding: 5px 5px 5px 10px;'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '	border-bottom: 1px solid #9b9b9b;'."\r\n";
				$style_content .= '	line-height: 1.7;'."\r\n";
				$style_content .= '	height: auto;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats .rf_stats_close {'."\r\n";
				$style_content .= '	display: inline-block;'."\r\n";
				$style_content .= '	background-color: #ff5a5a;'."\r\n";
				$style_content .= '	color: #ffffff;'."\r\n";
				$style_content .= '	padding: 0 5px;'."\r\n";
				$style_content .= '	margin-left: 5px;'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '	float: right;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats .rf_stats_close:hover {'."\r\n";
				$style_content .= '	background-color: #ca1818;'."\r\n";
				$style_content .= '	cursor: pointer;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats table {'."\r\n";
				$style_content .= '	border-collapse: collapse;'."\r\n";
				$style_content .= '	margin: 0 !important;'."\r\n";
				$style_content .= '	padding: 0;'."\r\n";
				$style_content .= '	border: 0 !important;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats table th {'."\r\n";
				$style_content .= '	padding: 3px 10px;'."\r\n";
				$style_content .= '	background: #dfdfdf;'."\r\n";
				$style_content .= '	color: #777777;'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '	border-bottom: 1px solid #bababa;'."\r\n";
				$style_content .= '	border-right: 1px solid #bababa;'."\r\n";
				$style_content .= '	text-transform: uppercase;'."\r\n";
				$style_content .= '	font-size: 11px;'."\r\n";
				$style_content .= '	line-height: 2;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats table th:last-child {'."\r\n";
				$style_content .= '	border-right: 0;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats tbody td {'."\r\n";
				$style_content .= '	padding: 5px;'."\r\n";
				$style_content .= '	border-top: 0;'."\r\n";
				$style_content .= '	text-align: center;'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '	font-size: 1.2em;'."\r\n";
				$style_content .= '	border-right: 1px solid #e2e2e2;'."\r\n";
				$style_content .= '	line-height: 2;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats tbody td:last-child {'."\r\n";
				$style_content .= '	border-right: 0;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_rich_snippet {'."\r\n";
				$style_content .= '	display: none;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .edit_rating {'."\r\n";
				$style_content .= '	color: red;'."\r\n";
				$style_content .= '	padding-left: 5px;'."\r\n";
				$style_content .= '	vertical-align: middle;'."\r\n";
				$style_content .= '	line-height: normal;'."\r\n";
				$style_content .= '	cursor: pointer;'."\r\n";
				$style_content .= '}'."\r\n";
				$style_content .= '.rating_form_'.$new_rating_id.' .rating_stats_active .rating_total {'."\r\n";
				$style_content .= '	font-weight: bold;'."\r\n";
				$style_content .= '}';

				fwrite($stylefile, $style_content);
				fclose($stylefile);

				$msg_success .= __( 'Successfully added!', 'rating-form' );
			}

		} else {
			$msg_error[] = __( 'Error! Rating Form not added. One of these fields were not added: ', 'rating-form' ) .
			(isset($type) ? null : 'type ' . $type) . (isset($restrict_ip) ? null : 'restrict_ip ' . $restrict_ip) .
			(isset($user_logged_in) ? null : 'user_logged_in ' . $user_logged_in) . (isset($txt_ty) ? null : 'txt_ty ' . $txt_ty) .
			(isset($txt_rated) ? null : 'txt_rated ' . $txt_rated) . (isset($font_size) ? null : 'font_size ' . $font_size) .
			(isset($font_size_text) ? null : 'font_size_text ' . $font_size_text) . (isset($text) ? null : 'text ' . $text);
		}

		if ( strlen( $msg_success ) > 0) {
			echo '<div class="updated"><p>' . $msg_success . '<br>';
			echo '<strong>Shortcode:</strong> <input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;'.$new_rating_id.'&quot;]"></p></div>';
		}

		if ( count( $msg_error ) > 0) {
			foreach ($msg_error as $msg_error_txt)
				echo '<div class="error"><p>' . $msg_error_txt . '</p></div>';
		}
	}
?>
	<div class="wrap rf_wrap" id="rating_form_add_edit">
		<?php Rating_Form::admin_menus( 'Add New Rating Form' ); ?>
<?php
	//Show Add Form page if form type is selected
	if (isset($_POST['type'])) {

	//Type Names
	$type_name = Rating_Form::form_types($_POST['type'], "name");
	$type_name_one = Rating_Form::form_types($_POST['type'], "name_one");
	$type_name_two = Rating_Form::form_types($_POST['type'], "name_two");
	$type_color = Rating_Form::form_types($_POST['type'], "css_color");
	$type_color_hover = Rating_Form::form_types($_POST['type'], "css_color_hover");
?>
		<h1><strong><?php echo $type_name; ?></strong></h1>
		<form method="post" enctype="multipart/form-data">
			<input id="type" type="hidden" name="type" value="<?php echo $_POST['type']; ?>" />
			<div id="poststuff">
			<?php if (intval($_POST['type']) == 0) { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Icon Empty', 'rating-form' ); ?></span></h3>
					<p class="pb_inside">
						<input id="file_icon_full" type="file" name="icon_empty" />
						<br>
						<span class="description"><?php _e( 'Upload an empty icon e.g. empty star', 'rating-form' ); ?></span>
					</p>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Icon Full (Hover)', 'rating-form' ); ?></span></h3>
					<p class="pb_inside">
						<input id="file_icon_full" type="file" name="icon_full" />
						<br>
						<span class="description"><?php _e( 'Upload an full icon e.g. full star', 'rating-form' ); ?></span>
					</p>
				</div>
				<div class="postbox">
					<h3><span><?php _e( 'Icon Half', 'rating-form' ); ?></span></h3>
					<p class="pb_inside">
						<input id="file_icon_full" type="file" name="icon_half" />
						<br>
						<span class="description"><?php _e( 'Upload an half icon e.g. half star', 'rating-form' ); ?></span>
					</p>
				</div>
			<?php } else { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Example', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
					<?php
						$content = '';
						$hideShow = '';
						// Css Style
						$content .= '<style type="text/css" id="admin_rf_add_new_css">'."\n";
						$content .= '#rating_form_add_edit .rating_form li:not(.def) { color: ' . $type_color . '; }'."\n";
						$content .= '#rating_form_add_edit .rating_form li.hover { color: ' . $type_color_hover . '; }'."\n";
						$content .= '</style>'."\n";

						//Rating titles
						$titlesTranslate = array(
							__( 'Very bad!', 'rating-form' ),
							__( 'Bad', 'rating-form' ),
							__( 'Hmmm', 'rating-form' ),
							__( 'Oke', 'rating-form' ),
							__( 'Good!', 'rating-form' ),
							__( 'Very good!', 'rating-form' ),
							__( 'Excellent!', 'rating-form' ),
							__( 'Cool!', 'rating-form' ),
							__( 'Awesome!', 'rating-form' ),
							__( 'Spectaculair!', 'rating-form' ),
							__( 'Like!', 'rating-form' ),
							__( 'Dislike!', 'rating-form' )
						);

						if (Rating_Form::form_types($_POST['type'], 'type') == "star") {
							$content .= '<div class="admin_rf_def">'."\r\n";
								$content .= '<ul class="rating_form cursor">'."\r\n";
						} else if (Rating_Form::form_types($_POST['type'], 'type') == "tud") {
							$content .= '<div class="admin_rf_tud">'."\r\n";
								$content .= '<ul class="rating_form cursor">'."\r\n";
						}

						for ($i = 1; $i <= Rating_Form::form_types(intval($_POST['type']), 'i'); $i++) {

							if ($i > 5) {
								$hideShow = ' hide';
							}

							if (Rating_Form::form_types($_POST['type'], 'type') == "star") {

								$content .= '<li id="rate_' . $i . '" class="' . (Rating_Form::form_types($_POST['type'], '', 'int') == '' ? Rating_Form::form_types($_POST['type'], 'class') : Rating_Form::form_types($_POST['type'], '', 'int') . $i) . $hideShow . '" title="'.$titlesTranslate[$i-1].'"></li>'."\r\n";

							} else if (Rating_Form::form_types($_POST['type'], 'type') == "tud") {

								if ($i == 1) {
									$content .= '<li id="rate_1u" class="' . Rating_Form::form_types($_POST['type'], 'class') . ' up_rated" title="'.$titlesTranslate[10].'"></li>'."\r\n";
									$content .= '<li class="up_rated_txt">+0</li>'."\r\n";
								} else if ($i == 2) {
									$content .= '<li id="rate_1d" class="' . Rating_Form::form_types($_POST['type'], 'class2') . ' down_rated" title="'.$titlesTranslate[11].'"></li>'."\r\n";
									$content .= '<li class="down_rated_txt">-0</li>'."\r\n";
								}

							}

						}

								$content .= '<li class="def rating_score">0</li>'."\r\n";
								$content .= '<li class="def rating_total">0 '. __( 'ratings', 'rating-form' ) .'</li>'."\r\n";
							$content .= '</ul>'."\r\n";
						$content .= '</div>';

						echo $content
					?>
					</div>
				</div>
			<?php } ?>
				<div class="clear"></div>
				<?php if (Rating_Form::form_types(intval($_POST['type']), 'type') == "star") { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Style', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
					<?php if (intval($_POST['type']) == 0) { // Custom Style block ?>
						<table class="form-table">
							<tbody>
							<tr>
								<td>
									<strong><?php _e( 'Icon Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size" type="text" name="font_size" value="32px" /><button id="font_size_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of icon in px, em or precent', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Font-Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size_text" type="text" name="font_size_text" value="18px" /><button id="font_size_text_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of score, total votes text', 'rating-form' ); ?></span>
								</td>
							</tr>
							<tr>
								<td>
									<strong><?php _e( 'Text Color', 'rating-form' ); ?></strong>
									<br>
									<input id="font_color_text" type="text" name="font_color_text" value="#777777" />
									<br>
									<span class="description"><?php _e( 'Set color of text', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Background', 'rating-form' ); ?></strong>
									<br>
									<input id="background_def_text" type="text" name="background_def_text" value="#dddddd" />
									<br>
									<span class="description"><?php _e( 'Set background of text', 'rating-form' ); ?></span>
								</td>
							</tr>
							</tbody>
						</table>
						<?php } else { ?>
						<table class="form-table">
							<tbody>
							<tr>
								<td>
									<strong><?php echo $type_name; ?> <?php _e( 'Icon Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size" type="text" name="font_size" value="32px" /><button id="font_size_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of icon in px, em or precent', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Font-Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size_text" type="text" name="font_size_text" value="18px" /><button id="font_size_text_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of score, total votes text', 'rating-form' ); ?></span>
								</td>
							</tr>
							<tr>
								<td>
									<strong><?php echo $type_name; ?> <?php _e( 'Color', 'rating-form' ); ?></strong>
									<br>
									<input id="font_color" type="text" name="font_color" value="<?php echo $type_color; ?>" />
									<br>
									<span class="description"><?php _e( 'Set color of icon', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Color', 'rating-form' ); ?></strong>
									<br>
									<input id="font_color_text" type="text" name="font_color_text" value="#777777" />
									<br>
									<span class="description"><?php _e( 'Set color of text', 'rating-form' ); ?></span>
								</td>
							</tr>
							<tr>
								<td>
									<strong><?php echo $type_name; ?> <?php _e( 'Hover Color', 'rating-form' ); ?></strong>
									<br>
									<input id="font_hover_color" type="text" name="font_hover_color" value="<?php echo $type_color_hover; ?>" />
									<br>
									<span class="description"><?php _e( 'Set color of hover', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Background', 'rating-form' ); ?></strong>
									<br>
									<input id="background_def_text" type="text" name="background_def_text" value="#dddddd" />
									<br>
									<span class="description"><?php _e( 'Set background of text', 'rating-form' ); ?></span>
								</td>
							</tr>
							</tbody>
						</table>
					<?php } ?>
					</div>
				</div>
				<?php } else if (Rating_Form::form_types(intval($_POST['type']), 'type') == "tud") { ?>
				<div class="postbox">
					<h3><span><?php _e( 'Size', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
						<table class="form-table">
							<tbody>
							<tr>
								<td>
									<strong><?php _e( 'Icon Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size" type="text" name="font_size" value="32px" /><button id="font_size_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of icon in px, em or precent', 'rating-form' ); ?></span>
								</td>
								<td>
									<strong><?php _e( 'Text Font-Size', 'rating-form' ); ?></strong>
									<br>
									<input id="font_size_text" type="text" name="font_size_text" value="18px" /><button id="font_size_text_btn" type="button"><?php _e( 'Change', 'rating-form' ); ?></button>
									<br>
									<span class="description"><?php _e( 'Set size of score, total votes text', 'rating-form' ); ?></span>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="clear"></div>
					<div class="postbox">
						<h3><span><?php echo $type_name_one; ?></span></h3>
						<div class="pb_inside">
							<table class="form-table">
								<tbody>
								<tr>
									<td>
										<strong><?php _e( 'Color', 'rating-form' ); ?></strong>
										<br>
										<input id="tu_font_color" type="text" name="tu_font_color" value="#59d600" />
										<br>
										<span class="description"><?php _e( 'Set color of icon', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Hover Color', 'rating-form' ); ?></strong>
										<br>
										<input id="tu_font_hover_color" type="text" name="tu_font_hover_color" value="#0e8b00" />
										<br>
										<span class="description"><?php _e( 'Set color of hover', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Text Color', 'rating-form' ); ?></strong>
										<br>
										<input id="tu_font_color_text" type="text" name="tu_font_color_text" value="#0e8b00" />
										<br>
										<span class="description"><?php _e( 'Set color of text', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Text Background', 'rating-form' ); ?></strong>
										<br>
										<input id="tu_background_def_text" type="text" name="tu_background_def_text" value="#bdffaf" />
										<br>
										<span class="description"><?php _e( 'Set background of text', 'rating-form' ); ?></span>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="postbox">
						<h3><span><?php echo $type_name_two; ?></span></h3>
						<div class="pb_inside">
							<table class="form-table">
								<tbody>
								<tr>
									<td>
										<strong><?php _e( 'Color', 'rating-form' ); ?></strong>
										<br>
										<input id="td_font_color" type="text" name="td_font_color" value="#d60000" />
										<br>
										<span class="description"><?php _e( 'Set color of icon', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Hover Color', 'rating-form' ); ?></strong>
										<br>
										<input id="td_font_hover_color" type="text" name="td_font_hover_color" value="#b80000" />
										<br>
										<span class="description"><?php _e( 'Set color of hover', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Text Color', 'rating-form' ); ?></strong>
										<br>
										<input id="td_font_color_text" type="text" name="td_font_color_text" value="#b80000" />
										<br>
										<span class="description"><?php _e( 'Set color of text', 'rating-form' ); ?></span>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php _e( 'Text Background', 'rating-form' ); ?></strong>
										<br>
										<input id="td_background_def_text" type="text" name="td_background_def_text" value="#ffb0b0" />
										<br>
										<span class="description"><?php _e( 'Set background of text', 'rating-form' ); ?></span>
									</td>
								</tr>
								</tbody>
							</table>
						</div>
					</div>
				<?php } ?>
				<div class="clear"></div>
				<div id="options" class="postbox">
					<h3><span><?php _e( 'Options', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
						<table class="form-table per100">
							<tbody>
								<tr>
									<td><strong><?php _e( 'Name', 'rating-form' ); ?></strong></td>
									<td><input id="form_name" type="text" name="form_name" value="" /></td>
									<td class="description"><?php _e( 'Name of Rating Form', 'rating-form' ); ?></td>
								</tr>
								<?php if (Rating_Form::form_types(intval($_POST['type']), 'type') == "star") { ?>
								<tr>
									<td><strong><?php _e( 'Max', 'rating-form' ); ?></strong></td>
									<td><select id="max" name="max">
									<?php for ($max = 1; $max <= 10; $max++) { ?>
										<?php if ($max == 5) { ?>
										<option value="<?php echo $max; ?>" selected="selected"><?php echo $max; ?></option>
										<?php } else { ?>
										<option value="<?php echo $max; ?>"><?php echo $max; ?></option>
										<?php } ?>
									<?php } ?>
									</select></td>
									<td class="description"><?php _e( 'Set max stars', 'rating-form' ); ?></td>
								</tr>
								<?php } else if (Rating_Form::form_types(intval($_POST['type']), 'type') == "tud") { ?>
								<input id="max" type="hidden" name="max" value="2" />
								<?php } ?>
								<tr>
									<td><strong><?php _e( 'Restrict IP', 'rating-form' ); ?></strong></td>
									<td><select id="restrict_ip" name="restrict_ip">
										<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
										<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									</select></td>
									<td class="description"><?php _e( 'Restrict the same IP address to rate multiple times', 'rating-form' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php _e( 'User Login', 'rating-form' ); ?></strong></td>
									<td><select id="user_logged_in" name="user_logged_in">
										<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
										<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									</select></td>
									<td class="description"><?php _e( 'User must login to rate', 'rating-form' ); ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="clear"></div>
				<div id="messages" class="postbox">
					<h3><span><?php _e( 'Messages', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
						<table class="form-table per100">
							<tbody>
								<tr>
									<td><strong><?php _e( 'Success', 'rating-form' ); ?></strong></td>
									<td><input id="txt_ty" type="text" name="txt_ty" value="<?php _e( 'Thank you :)', 'rating-form' ); ?>" /></td>
									<td class="description"><?php _e( 'Set success message', 'rating-form' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php _e( 'Rated', 'rating-form' ); ?></strong></td>
									<td><input id="txt_rated" type="text" name="txt_rated" value="<?php _e( 'You already rated', 'rating-form' ); ?>" /></td>
									<td class="description"><?php _e( 'Set rated message', 'rating-form' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php _e( 'Login', 'rating-form' ); ?></strong></td>
									<td><input id="txt_login" type="text" name="txt_login" value="<?php _e( 'Login to rate', 'rating-form' ); ?>" /></td>
									<td class="description"><?php _e( 'Set login message', 'rating-form' ); ?></td>
								</tr>
								<tr>
									<td><strong><?php _e( 'Limit', 'rating-form' ); ?></strong></td>
									<td><input id="txt_limit" type="text" name="txt_limit" size="80" value="<?php _e( 'Sorry, rating is limited. Try again in %4$d days %3$d hours %2$d minutes %1$d seconds.', 'rating-form' ); ?>" /></td>
									<td class="description"><?php _e( 'Set limit message', 'rating-form' ); ?><br>
									<?php _e( '<strong>%1$d</strong> = value of seconds', 'rating-form' ); ?><br>
									<?php _e( '<strong>%2$d</strong> = value of minutes', 'rating-form' ); ?><br>
									<?php _e( '<strong>%3$d</strong> = value of hours', 'rating-form' ); ?><br>
									<?php _e( '<strong>%4$d</strong> = value of days', 'rating-form' ); ?><br></td>
								</tr>
								<tr>
									<td><strong><?php _e( 'Edit rating', 'rating-form' ); ?></strong></td>
									<td><input id="txt_edit_rating" type="text" name="txt_edit_rating" value="<?php _e( 'You find this post %2$s', 'rating-form' ); ?>" /></td>
									<td class="description"><?php _e( 'Set edit rating message', 'rating-form' ); ?><br>
									<?php _e( '<strong>%1$d</strong> = value of user rating', 'rating-form' ); ?><br>
									<?php _e( '<strong>%2$s</strong> = title of value', 'rating-form' ); ?><br></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="clear"></div>
				<div class="postbox">
					<h3><span><?php _e( 'Titles', 'rating-form' ); ?></span></h3>
					<div class="pb_inside">
						<table id="texts" class="form-table per100">
							<tbody>
								<?php
								global $wpdb;
								$hideorshow = null;

								if (Rating_Form::form_types(intval($_POST['type']), 'type') == "star") {
									$pos_query = $wpdb->get_results( "SELECT position FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " GROUP BY position ORDER BY position ASC", ARRAY_A );

									$i = 0;
									foreach ($pos_query as $pos_row) {
										$i++;
										if ($i > (isset($_POST['max']) ? $_POST['max'] : 5)) {
											$hideorshow = ' hide';
										}
										$text_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position = ". $pos_row['position'] ." ORDER BY title_id ASC", ARRAY_A );
								?>
								<tr class="text_select<?php echo $hideorshow; ?>">
									<td><strong><?php _e( 'Position', 'rating-form' ); ?> <?php echo $pos_row['position']; ?>.</strong></td>
									<td>
									<select name="text[]">
									<?php foreach ($text_query as $text_row) { ?>
										<option value="<?php echo $text_row['title_id']; ?>"><?php echo $text_row['text']; ?></option>
									<?php } ?>
									</select>
									</td>
								</tr>
								<?php
									}
								} else if (Rating_Form::form_types(intval($_POST['type']), 'type') == "tud") {
									$pos_query = $wpdb->get_results( "SELECT position FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position IN(1,2) GROUP BY position ORDER BY position ASC", ARRAY_A );

									$tud_text = array( __( 'Up', 'rating-form' ),  __( 'Down', 'rating-form' ));
									foreach ($pos_query as $pos_row) {
										$text_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position = ". $pos_row['position'] ." ORDER BY title_id DESC", ARRAY_A );
								?>
								<tr class="text_select">
									<td>
										<strong><?php echo $tud_text[$pos_row['position']-1]; ?></strong>
									</td>
									<td>
									<select name="text[]">
									<?php foreach ($text_query as $text_row) { ?>
										<option value="<?php echo $text_row['title_id']; ?>"><?php echo $text_row['text']; ?></option>
									<?php } ?>
									</select>
									</td>
								</tr>
								<?php
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<input type="submit" name="submit" class="button button-primary button-large" value="<?php _e( 'Add', 'rating-form' ); ?>" />
		</form>
<?php } else { ?>
		<form method="post">
			<div id="poststuff">
				<div id="rating_form_type_select" class="postbox">
					<h3><strong><?php _e( 'Choose form-wWw.GFXFree.Net', 'rating-form' ); ?></strong></h3>
					<div class="pb_inside">
						<p>
							<?php
								$i = 0;
								while ($i <= Rating_Form::$totalFormTypes) {
									// Rating Form Type
									echo '<label>';
									echo '<input id="type" type="radio" name="type" value="'. $i . '">';
									echo '<span class="' . Rating_Form::form_types($i, "class") . '">' . ($i == 0 ? '?' : '') . '</span>';
									echo Rating_Form::form_types($i, "name");
									echo '</label><br>';
									$i++;
									if ($i % 5 == 0) {
										echo '</p>';
										echo '<p>';
									}
								}
							?>
						</p>
						<div class="description"><?php _e( 'Choose a form to start Or Click Here:<a href="http://goo.gl/EZNSSi" target="_blank">goo.gl/EZNSSi</a>', 'rating-form' ); ?></div>
					</div>
				</div>
			</div>
			<div class="clear"></div>
			<input type="submit" name="start_submit" class="button button-primary button-large" value="<?php _e( 'Create', 'rating-form' ); ?>">
		</form>
<?php
	}
	?>
	</div>
	<?php
}
?>
