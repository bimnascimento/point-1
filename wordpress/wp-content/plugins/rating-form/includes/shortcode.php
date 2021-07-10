<?php
// Rating Form Wrapper
function wrap_rating_form( $args, $ajax_add_rating, $ajax_load, $ajax_loaded ) {
	$content = '';
	$rating_form_result = '';
	$cursor = ' cursor';
	$rated = '';
	$thankyou = '';
	$display_empty = '';
	$upload_dir = wp_upload_dir();
	$post_or_comment_id = '';
	$comment_id_sql = '';
	$current_user = wp_get_current_user();
	$msg_edit_rating = '';
	$redirect_enable = '';
	$redirect_url = '';
	$redirect_target = '';

	global $wpdb;

	// Check Rating ID
	$this_form_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = '".$args['id']."'", ARRAY_A );
	$this_form_num = $wpdb->num_rows;

	// Rating titles
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
	$titlesText = array();
	$form_titles_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_form_id = '".$this_form_query['form_id']."'", ARRAY_A );
	foreach ($form_titles_query as $form_title) {
		$titles_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$form_title['title_id']."'", ARRAY_A );
		$titles_num_rows = $wpdb->num_rows;
		foreach ($titles_query as $title_text) {
			if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'very bad') {
				$titlesText[$title_text['position']] = $titlesTranslate[0];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'bad') {
				$titlesText[$title_text['position']] = $titlesTranslate[1];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'hmmm') {
				$titlesText[$title_text['position']] = $titlesTranslate[2];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'oke') {
				$titlesText[$title_text['position']] = $titlesTranslate[3];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'good') {
				$titlesText[$title_text['position']] = $titlesTranslate[4];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'very good') {
				$titlesText[$title_text['position']] = $titlesTranslate[5];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'cool') {
				$titlesText[$title_text['position']] = $titlesTranslate[6];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'excellent') {
				$titlesText[$title_text['position']] = $titlesTranslate[7];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'awesome') {
				$titlesText[$title_text['position']] = $titlesTranslate[8];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'spectaculair') {
				$titlesText[$title_text['position']] = $titlesTranslate[9];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'like') {
				$titlesText[$title_text['position']] = $titlesTranslate[10];
			} else if (preg_replace("/[^a-zA-Z0-9\s]+/", "", strtolower($title_text['text'])) == 'dislike') {
				$titlesText[$title_text['position']] = $titlesTranslate[11];
			} else {
				$titlesText[$title_text['position']] = $title_text['text'];
			}
		}
	}

	// Post Meta
	$pmeta_average = get_post_meta($args['post_id'], 'rf_average_result', true);
	$pmeta_up = get_post_meta($args['post_id'], 'rf_votes_up', true);
	$pmeta_down = get_post_meta($args['post_id'], 'rf_votes_down', true);
	$pmeta_votes = get_post_meta($args['post_id'], 'rf_total_votes', true);

	// Comment ID available
	if (empty($args['comment_id'])) {
		$comment_id_sql .= "comment_id = 0 AND ";
	} else {
		$comment_id_sql .= "comment_id = " . $args['comment_id'] . " AND ";
	}
	// Custom ID available
	if (empty($args['custom_id'])) {
		$comment_id_sql .= "custom_id = '0' AND ";
	} else {
		$comment_id_sql .= "custom_id = '" . $args['custom_id'] . "' AND ";
	}
	// User ID available
	if (empty($args['user_id'])) {
		$comment_id_sql .= "user_id = 0 AND ";
	} else {
		$comment_id_sql .= "user_id = " . $args['user_id'] . " AND ";
	}

	$argsUsed = false;
	if (!empty($args['comment_id']) || !empty($args['custom_id']) || !empty($args['user_id'])) {
		$argsUsed = true;
	}

	// Term ID available
	if (empty($args['term_id'])) {
		$comment_id_sql .= "term_id = 0 AND ";
	} else {
		$argsUsed = true;
		$args['post_id'] = 0;
		$args['comment_id'] = 0;

		$comment_id_sql .= "term_id = " . $args['term_id'] . " AND ";
	}

	// Displays
	$jsonDisplay = empty($this_form_query['display']) ? array() : json_decode($this_form_query['display']);
	$display_half = '-half hover';
	$display_up = empty($this_form_query['display']) ? '' : in_array("up", $jsonDisplay) ? true : false;
	$display_down = empty($this_form_query['display']) ? '' : in_array("down", $jsonDisplay) ? true : false;
	$display_empty = empty($this_form_query['display']) ? '' : in_array("empty", $jsonDisplay) ? '-empty' : null;
	$display_edit_rating = empty($this_form_query['display']) ? '' : in_array("edit_rating", $jsonDisplay) ? true : false;
	$display_edit_rating_direct = empty($this_form_query['display']) ? '' : in_array("edit_rating_direct", $jsonDisplay) ? true : false;
	$display_edit_rating_text = empty($this_form_query['display']) ? '' : in_array("edit_rating_text", $jsonDisplay) ? true : false;
	$display_ajax_loading_text = empty($this_form_query['display']) ? '' : in_array("ajax_loading_text", $jsonDisplay) ? true : false;
	$display_up_minus_down_total = empty($this_form_query['display']) ? '' : in_array("up_down_total", $jsonDisplay) ? true : false;
	$display_redirect_enable = empty($this_form_query['display']) ? '' : in_array("redirect_enable", $jsonDisplay) ? true : false;
	$display_ustats_enable = empty($this_form_query['display']) ? '' : in_array("ustats_enable", $jsonDisplay) ? true : false;
	$display_remove_bip_votes = empty($this_form_query['display']) ? '' : in_array("remove_bip_votes", $jsonDisplay) ? true : false;
	$display_stylesheet_load_not = empty($this_form_query['display']) ? '' : in_array("stylesheet_load_not", $jsonDisplay) ? true : false;
	$display_hide_success_msg = empty($this_form_query['display']) ? '' : in_array("hide_success_msg", $jsonDisplay) ? true : false;
	$display_hide_up_total = empty($this_form_query['display']) ? '' : in_array("hide_up_total", $jsonDisplay) ? true : false;
	$display_hide_down_total = empty($this_form_query['display']) ? '' : in_array("hide_down_total", $jsonDisplay) ? true : false;
	$getSpinner_gp = preg_grep("/^spinner(\d)/", $jsonDisplay);
	$display_get_spinner = empty($getSpinner_gp) ? 'spinner' : $getSpinner_gp[0];

	// Check IP or User ID
	$ip = $_SERVER['REMOTE_ADDR'];
	$user = $current_user->ID;
	$user_or_ip = ($this_form_query['user_logged_in'] == 1) ? 'user' : 'ip';
	$user_or_ip_val = ($this_form_query['user_logged_in'] == 1) ? $user : $ip;

	// Check if post id exist in WP
	$wp_post_id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->posts . " WHERE ID = '".$args['post_id']."'", ARRAY_A );
	$wp_post_id_query_num = $wpdb->num_rows;

	// Check if comment id exist in WP
	$wp_comment_id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->comments . " WHERE comment_ID = '".$args['comment_id']."'", ARRAY_A );
	$wp_comment_id_query_num = $wpdb->num_rows;

	// Check if post id is rated
	$post_id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE ".$comment_id_sql."post_id = '".$args['post_id']."' GROUP BY post_id", ARRAY_A );
	$post_id_query_num = $wpdb->num_rows;

	// Count ratings
	$display_remove_bip_votes = $display_remove_bip_votes ? "ip NOT IN (SELECT ip FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . ") AND " : "";
	$ipAll = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(" . $user_or_ip . ") FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE ".$display_remove_bip_votes."rated REGEXP '^[0-9]+$' AND ".$comment_id_sql."post_id = %s", $args['post_id'] ) );

	$ratedAll = $wpdb->get_var( $wpdb->prepare( "SELECT SUM(rated) FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE ".$display_remove_bip_votes."rated REGEXP '^[0-9]+$' AND ".$comment_id_sql."post_id = %s", $args['post_id'] ) );

	if (!empty($pmeta_votes) && !$argsUsed) {
		$ipAll = $pmeta_votes;
	}

	// Average Rating
	if ($ratedAll == 0) {
		$ratedAllcount = 0;
	} else {
		$ratedAllcount = $post_id_query_num == 0 ? 0 : ($ratedAll / $ipAll);
	}

	if (!empty($pmeta_average) && !$argsUsed) {
		$ratedAllcount = $pmeta_average;
	}

	$floororceil = (round_to_half($ratedAllcount) >= ceil($ratedAllcount)) ? ceil($ratedAllcount) : floor($ratedAllcount);
	$floororceilhalf = (round_to_half($ratedAllcount) > floor($ratedAllcount)) ? ceil($ratedAllcount) : floor($ratedAllcount);

	// Thumbs up rated
	$thumbsUpq = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(rated) FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated = '1u' AND ".$comment_id_sql."post_id = %s", $args['post_id'] ) );
	$thumbsUp_num_rows = $wpdb->num_rows;
	$thumbsUp = $thumbsUp_num_rows == 0 ? 0 : $thumbsUpq;
	if (!empty($pmeta_up) && !$argsUsed) {
		$thumbsUp = $pmeta_up;
	}

	// Thumbs down rated
	$thumbsDownq = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(rated) FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated = '1d' AND ".$comment_id_sql."post_id = %s", $args['post_id'] ) );
	$thumbsDown_num_rows = $wpdb->num_rows;
	$thumbsDown = $thumbsDown_num_rows == 0 ? 0 : $thumbsDownq;
	if (!empty($pmeta_down) && !$argsUsed) {
		$thumbsDown = $pmeta_down;
	}

	// Thumbs Up and Down = Total votes
	$tudTotal = ($thumbsUp + $thumbsDown);
	// Check if user exists by ID
	$userExists = get_userdata( $args['user_id'] );
	if ($this_form_num == 0) { // Rating ID doesn't exist

		$content .= sprintf( __( 'Rating Form ID %d does not exist!', 'rating-form' ), $args['id'] );

	} else if ($wp_post_id_query_num == 0 && !is_admin() && empty($args['term_id'])) {

		if (empty($args['post_id'])) {
			$content .= sprintf( __( 'Post ID is empty in Rating Form ID %d', 'rating-form' ), $args['id'] );
		} else {
			$content .= sprintf( __( 'Post ID %1$d not found in Rating Form ID %2$d', 'rating-form' ), $args['post_id'], $args['id'] );
		}

	} else if (!$userExists && !empty($args['user_id'])) {
		$content .= sprintf( __( 'User ID %1$d in Rating Form ID %2$d does not exists', 'rating-form' ), $args['user_id'], $args['id'] );
	} else {
		$ip_query = $wpdb->get_results( "SELECT " . $user_or_ip . ", date, rated, rate_id FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated REGEXP '^[0-9]+$' AND ".$comment_id_sql."post_id = '".$args['post_id']."' AND " . $user_or_ip . " = '".$user_or_ip_val."' ORDER BY date DESC LIMIT " . $this_form_query['limitation'], ARRAY_A );
		$ip_num = $wpdb->num_rows;

		$ip_query_ud = $wpdb->get_results( "SELECT " . $user_or_ip . ", date, rated, rate_id FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE (rated = '1u' OR rated = '1d') AND ".$comment_id_sql."post_id = '".$args['post_id']."' AND " . $user_or_ip . " = '".$user_or_ip_val."' ORDER BY date DESC LIMIT " . $this_form_query['limitation'], ARRAY_A );
		$ip_num_ud = $wpdb->num_rows;

		$limit_ip_query = $wpdb->get_results( "SELECT " . $user_or_ip . ", date FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated REGEXP '^[0-9]+$' AND ".$comment_id_sql."post_id = '".$args['post_id']."' AND " . $user_or_ip . " = '".$user_or_ip_val."' AND date >= DATE_SUB(NOW(), INTERVAL '".  $this_form_query['time'] ."' SECOND) ORDER BY date DESC LIMIT " . $this_form_query['limitation'], ARRAY_A );
		$limit_ip_num = $wpdb->num_rows;

		$limit_ip_query_ud = $wpdb->get_results( "SELECT " . $user_or_ip . ", date FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE (rated = '1u' OR rated = '1d') AND ".$comment_id_sql."post_id = '".$args['post_id']."' AND " . $user_or_ip . " = '".$user_or_ip_val."' AND date >= DATE_SUB(NOW(), INTERVAL '".  $this_form_query['time'] ."' SECOND) ORDER BY date DESC LIMIT " . $this_form_query['limitation'], ARRAY_A );
		$limit_ip_num_ud = $wpdb->num_rows;
		$mysql_now = $wpdb->get_row( "SELECT NOW() as dbNow", ARRAY_A );

		// Options Enabled
		$rich_snippet_enabled = ($this_form_query['rich_snippet'] == 1) ? ' itemscope itemtype="http://schema.org/Article"' : null;
		$empty_enabled = empty($this_form_query['display']) ? '' : in_array("empty", $jsonDisplay) ? ' empty_on' : null;
		$spinner_enabled = ($this_form_query['spinner'] == 1) ? ' ' . $display_get_spinner . '_on' : null;
		$rtl_enabled = ($this_form_query['rtl'] == 1) ? ' dir="rtl"' : null;
		$user_rated = ((Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $ip_num_ud > 0 :  $ip_num > 0)) ? ' user_rating' : null;
		$stylesheetNotLoad = ($display_stylesheet_load_not) ? ' stylesheet_off' : null;

		if ($display_up == true && $display_down == true || $display_up == false && $display_down == false) {
			$tudTotal = $thumbsUp + $thumbsDown;
		} else if ($display_up == true) {
			$tudTotal = $thumbsUp;
		} else if ($display_down == true) {
			$tudTotal = $thumbsDown;
		}

		if ($display_up_minus_down_total) {
			$tudTotal = ($thumbsUp - $thumbsDown) == 0 ? 0 : $thumbsUp - $thumbsDown;
		}

		if ($ajax_add_rating == true) {
			if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
				$rf_real_average = round($ratedAllcount, $this_form_query['round']);
				// update (add) post meta current average rating
				update_post_meta($args['post_id'], 'rf_real_average', $rf_real_average);
			} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {
				update_post_meta($args['post_id'], 'rf_real_up', $thumbsUp);
				update_post_meta($args['post_id'], 'rf_real_down', $thumbsDown);
			}
			if (!$display_hide_success_msg) {
				$thankyou = '<li class="def thankyou">' .(empty($this_form_query['txt_ty']) ? __( 'Thank you :)', 'rating-form' ) : $this_form_query['txt_ty']). '</li>'."\r\n";
			}
		}
			// Redirect after voting if not empty
			if (count($jsonDisplay) > 0) {
				if ((count(preg_grep("/^redirect_url-(.*)/", $jsonDisplay)) > 0) && $display_redirect_enable) {
					$redirect_enable = ' redirect_on';
					// Redirect Target
					$display_rT_val = preg_grep("/^redirect_target-(.*)/", $jsonDisplay);
					$display_rT_reset = reset($display_rT_val);
					$display_redirect_target = trim(str_replace('redirect_target-', '', $display_rT_reset));
					// Redirect Url
					$display_rU_val = preg_grep("/^redirect_url-(.*)/", $jsonDisplay);
					$display_rT_reset = reset($display_rU_val);
					$display_redirect_url = trim(str_replace('redirect_url-', '', $display_rT_reset));
					$redirect_url = ' data-redirect-url="' . $display_redirect_url . '"';
					$redirect_target = ' data-redirect-target="' . (empty($display_redirect_target) ? '_blank' : $display_redirect_target) . '"';
				}
			}

		if (($ip_num_ud == $this_form_query['limitation'] && Rating_Form::form_types($this_form_query['type'], 'type') == "tud" || $ip_num == $this_form_query['limitation'] && Rating_Form::form_types($this_form_query['type'], 'type') == "star") && $this_form_query['restrict_ip'] == 1) {
			$rating_form_result = ' rating_form_check';
			$rated = '<li class="def rated">'.(empty($this_form_query['txt_rated']) ? __( 'You already rated', 'rating-form' ) : $this_form_query['txt_rated']).'</li>'."\r\n";
		}

		if ($this_form_query['user_logged_in'] == 1 && is_user_logged_in() == false) { // User must login to rate && if user not logged in; warning msg
			$rating_form_result = ' rating_form_check';
			$rated = '<li class="def rated login">' .(empty($this_form_query['txt_login']) ? __( 'Login to rate', 'rating-form' ) : $this_form_query['txt_login']). '</li>'."\r\n";
		}

		if ($this_form_query['limitation'] >= 1 && $this_form_query['time'] > 0) {
			if (($limit_ip_num == $this_form_query['limitation'] && Rating_Form::form_types($this_form_query['type'], 'type') == "star") && $this_form_query['restrict_ip'] == 0) {
				$rating_form_result = ' rating_form_check';
				$rated = '<li class="def rated limit">' . secondsFormat((empty($this_form_query['txt_limit']) ? __( 'Sorry, rating is limited. Try again in %4$d days %3$d hours %2$d minutes %1$d seconds.', 'rating-form' ) : $this_form_query['txt_limit']), ((strtotime($limit_ip_query[0]['date'])+$this_form_query['time'])-strtotime($mysql_now['dbNow']))) . '</li>'."\r\n";
			} else if (($limit_ip_num_ud == $this_form_query['limitation'] && Rating_Form::form_types($this_form_query['type'], 'type') == "tud") && $this_form_query['restrict_ip'] == 0) {
				$rating_form_result = ' rating_form_check';
				$rated = '<li class="def rated limit">' . secondsFormat((empty($this_form_query['txt_limit']) ? __( 'Sorry, rating is limited. Try again in %4$d days %3$d hours %2$d minutes %1$d seconds.', 'rating-form' ) : $this_form_query['txt_limit']), ((strtotime($limit_ip_query_ud[0]['date'])+$this_form_query['time'])-strtotime($mysql_now['dbNow']))) . '</li>'."\r\n";
			}
		}

		if ($args['result'] == true) {
			$rating_form_result = ' rating_form_result';
			$args['title'] = false;
			$cursor = null;
		}

		// Class Fields IDs
		if (empty($args['term_id'])) {
			$post_or_comment_id = 'postid_' . $wp_post_id_query['ID'];
		} else {
			$post_or_comment_id = 'termid_' . $args['term_id'];
		}

		if ($wp_comment_id_query_num > 0) {
			$post_or_comment_id .= '-commentid_' . $wp_comment_id_query['comment_ID'];
		}

		if (!empty($args['user_id'])) {
			$post_or_comment_id .= '-userid_' . $args['user_id'];
		}

		// Use custom_id last cus regex: /customid_(.*)/
		if (!empty($args['custom_id'])) {
			$post_or_comment_id .= '-customid_' . $args['custom_id'];
		}

		$edit_rating_id = '';
		if ((Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $ip_num_ud > 0 :  $ip_num > 0)) {
			$edit_rating_id = ($display_edit_rating == true || $display_edit_rating_direct == true) ? ' id="edit_'. (Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? intval($ip_query_ud[0]['rate_id']) : intval($ip_query[0]['rate_id'])) .'"' : '';

			if ($display_edit_rating == true && $args['result'] == false) {
				$rating_form_result = ' rating_form_result';
				$rated = '';
				$floororceil = (Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? intval($ip_query_ud[0]['rated']) : intval($ip_query[0]['rated']));
				$cursor = null;
				$display_half = $this_form_query['type'] == 0 ? '-empty' : $display_empty;
				$msg_edit_rating_btn = '<span class="cyto-min edit_rating"></span>';
			}

			if ($display_edit_rating_direct == true && $args['result'] == false) {
				$rating_form_result = '';
				$rated = '';
				$floororceil = (Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? intval($ip_query_ud[0]['rated']) : intval($ip_query[0]['rated']));
				$display_half = $this_form_query['type'] == 0 ? '-empty' : $display_empty;
			}
			if ($args['result'] == false) {
				$msg_edit_rating = ($display_edit_rating_text == true ? '<span class="edit_rating_text">' . sprintf((empty($this_form_query['txt_edit_rating']) ? __( 'You find this post %2$s', 'rating-form' ) : $this_form_query['txt_edit_rating']), $floororceil, $titlesText[$floororceil]) . '</span>' : '') . ($display_edit_rating == true ? $msg_edit_rating_btn : '');
			}
		}

		if ($ajax_loaded == false) {
			$content .= '<div id="rf_'.($args['result'] == true ? 'result_' : '').$this_form_query['form_id'].'-' . $post_or_comment_id  . '" class="rating_form_'.$this_form_query['form_id'].'"'. $rich_snippet_enabled . $rtl_enabled . ' style="display: none;">'."\r\n"; // START div rating_form
		}

		if (!empty($args['before_content'])) {
			$content .= '<div class="rating_before_content">' . html_entity_decode($args['before_content']) . '</div>';
		}

		// Reset cus Admin
		if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
			$rating_form_result = ' rating_form_check';
			$rated = '<li class="def rated">This is an example.</li>'."\r\n";
			$tudTotal = 0;
			$ipAll = 0;
			$ratedAllcount = 0;
			$floororceil = 0;
			$floororceilhalf = 0;
		}

		// Ajax Load Enabled
		if ($this_form_query['ajax_load'] == 1 && $ajax_loaded == false && !is_admin()) {

			if ($display_ajax_loading_text == false) {
				$content .= __( 'Loading...', 'rating-form' );
			}

			$content .= '<script type="text/javascript">';
			$content .= 'jQuery(document).ready(function() {';
				$content .= 'jQuery.ajax({';
					$content .= 'type: "POST",';
					$content .= 'url : rating_form_script.ajaxurl,';
					$content .= 'data : { action : "ajax_display_rating_form", args : ' . json_encode($args) . ' }, ';
					$content .= 'success : function(data) { jQuery("#rf_'.($args['result'] == true ? 'result_' : '').$this_form_query['form_id'].'-' . $post_or_comment_id  . '").html(data); }';
				$content .= '});';
			$content .= '});';
			$content .= '</script>';

		} else {

		$content .= '<ul ' . $redirect_url . $redirect_target . 'class="rating_form' . $rating_form_result . $cursor . $spinner_enabled . $empty_enabled . $user_rated . $redirect_enable . $stylesheetNotLoad . '"' . $edit_rating_id . '>'."\r\n";

			$exp_rates = explode(',', $args['rates']);
			// RTL True; show on the left side
			if ($this_form_query['rtl'] == 1) {
				$content .= $thankyou;
				$content .= $rated;
				if ($args['total'] == true) {
					$content .= '<li class="def rating_total"><span class="votes">'.(Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll) . (empty($args['rates']) ? '' : ' ') . '</span>' . ((strpos($args['rates'], ',') !== false) ? ((Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll) == 1) ? $exp_rates[0] : $exp_rates[1] : $args['rates']) .'</li>'."\r\n";
				}
				if ($this_form_query['max'] > 1) {
					if ($args['score'] == true) {
						if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
							//$content .= '<li class="def rating_score">'.round($ratedAllcount, $this_form_query['round']).'/'.$this_form_query['max'].'</li>'."\r\n";
							$content .= '<li class="def rating_score">'.round($ratedAllcount, $this_form_query['round']).'</li>'."\r\n";
						}
					}
				}
			}

			// small things
			$max_is_one = $this_form_query['max'] == 1 ? null : ' hover';
			$custom_max_is_one = $this_form_query['max'] == 1 ? 'empty' : 'full';
			$rtl_half = $this_form_query['rtl'] == 1 ? '-rtl' : '';
			for ($i = ($this_form_query['rtl'] == 1 ? $this_form_query['max'] : 1); ($this_form_query['rtl'] == 1 ? $i >= 1 : $i <= $this_form_query['max']); ($this_form_query['rtl'] == 1 ? $i-- : $i++)) {

				$title_show = ($args['tooltip'] == true) ? ' title="'.$titlesText[$i].'"' : null;
				$title_text = ($args['title'] == true) ? '<span class="title">'.$titlesText[$i].'</span>' : null;

				if ($this_form_query['type'] == 0) {

					if ($i <= $floororceil) {
						$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="cyto-custom' . $max_is_one . '"' . $title_show . '><img src="'.$upload_dir['baseurl'].'/rating-form/icons/'.$this_form_query['form_id'].'/custom-' . $custom_max_is_one . '.png" alt="Custom ' . ucfirst($custom_max_is_one) . '" />' . $title_text . '</li>'."\r\n";
					} elseif ($i >= round_to_half($ratedAllcount) && $i <= $floororceilhalf) {
						$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="cyto-custom'. $display_half .'"' . $title_show . '><img src="'.$upload_dir['baseurl'].'/rating-form/icons/'.$this_form_query['form_id'].'/custom'. str_replace(' hover','',$display_half).'.png" alt="Custom Half" />' . $title_text . '</li>'."\r\n";
					} else {
						$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="cyto-custom"' . $title_show . '><img src="'.$upload_dir['baseurl'].'/rating-form/icons/'.$this_form_query['form_id'].'/custom-empty.png" alt="Custom Full" />' . $title_text . '</li>'."\r\n";
					}

				} else {

					if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {

						if ($i <= $floororceil) {
							$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="' . (Rating_Form::form_types($this_form_query['type'], '', 'int') == '' ? Rating_Form::form_types($this_form_query['type'], 'class') : Rating_Form::form_types($this_form_query['type'], '', 'int') . $i) . $max_is_one . '"' . $title_show . '>' . $title_text . '</li>'."\r\n";
						} elseif ($i >= round_to_half($ratedAllcount) && $i <= $floororceilhalf) {
							$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="' . (Rating_Form::form_types($this_form_query['type'], '', 'int') == '' ? Rating_Form::form_types($this_form_query['type'], 'class') . ($display_half == "-empty" ? '' : $rtl_half).$display_half : Rating_Form::form_types($this_form_query['type'], '', 'int') . $i . str_replace('-half','',$display_half)) . '"' . $title_show . '>' . $title_text . '</li>'."\r\n";
						} else {
							$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_' . $i . '" class="' . (Rating_Form::form_types($this_form_query['type'], '', 'int') == '' ? Rating_Form::form_types($this_form_query['type'], 'class') . $display_empty : Rating_Form::form_types($this_form_query['type'], '', 'int') . $i) . '"' . $title_show . '>' . $title_text . '</li>'."\r\n";
						}

					} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {

						if ($display_up == true && $display_down == true || $display_up == false && $display_down == false) {

							if ($i == 1) {
								$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_1u" class="' . Rating_Form::form_types($this_form_query['type'], 'class') . ' up_rated"' . $title_show . '>' . $title_text . '</li>'."\r\n";
								if (!$display_hide_up_total) {
									$content .= '<li class="up_rated_txt">+' . $thumbsUp . '</li>'."\r\n";
								}
							} else if ($i == 2) {
								$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_1d" class="' . Rating_Form::form_types($this_form_query['type'], 'class2') . ' down_rated"' . $title_show . '>' . $title_text . '</li>'."\r\n";
								if (!$display_hide_down_total) {
									$content .= '<li class="down_rated_txt">-' . $thumbsDown . '</li>'."\r\n";
								}
							}

						} else if ($display_up == true) {

							if ($i == 1) {
								$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_1u" class="' . Rating_Form::form_types($this_form_query['type'], 'class') . ' up_rated"' . $title_show . '>' . $title_text . '</li>'."\r\n";
								if (!$display_hide_up_total) {
									$content .= '<li class="up_rated_txt">+' . $thumbsUp . '</li>'."\r\n";
								}
							}

						} else if ($display_down == true) {

							if ($i == 2) {
								$content .= '<li id="rate_' . ($args['result'] == true ? 'result_' : '') . $this_form_query['form_id'].'_1d" class="' . Rating_Form::form_types($this_form_query['type'], 'class2') . ' down_rated"' . $title_show . '>' . $title_text . '</li>'."\r\n";
								if (!$display_hide_down_total) {
									$content .= '<li class="down_rated_txt">-' . $thumbsDown . '</li>'."\r\n";
								}
							}

						}

					}

				}

			} // END loop

			// RTL False; show on the right side
			if ($this_form_query['rtl'] == 0) {
				if ($this_form_query['max'] > 1) {
					if ($args['score'] == true) {
						if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
							//$content .= '<li class="def rating_score">'.round($ratedAllcount, $this_form_query['round']).'/'.$this_form_query['max'].'</li>'."\r\n";
							$content .= '<li class="def rating_score">'.round($ratedAllcount, $this_form_query['round']).'</li>'."\r\n";
						}
					}
				}
				if ($args['total'] == true && ($display_up == true && $display_down == true || $display_up == false && $display_down == false)) {
					$content .= '<li class="def rating_total"><span class="votes">' . (Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll) . (empty($args['rates']) ? '' : ' ') . '</span>' . ((strpos($args['rates'], ',') !== false) ? ((Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll) == 1) ? $exp_rates[0] : $exp_rates[1] : $args['rates']) .'</li>'."\r\n";
				}
				$content .= $rated;
				$content .= $thankyou;
			}

		$content .= '</ul>'."\r\n";
		$content .= $msg_edit_rating;

		// Stats or User Stats
		if ($args['stats'] == true || $args['user_stats'] == true) {
			// Thumbs Up and Down = Total votes reset
			if ($display_up_minus_down_total) {
				$tudTotal = ($thumbsUp + $thumbsDown);
			}
			$content .= '<div class="rating_stats">'."\r\n";

			// Statics
			if ($args['stats'] == true) {
				$content .= '<div class="rf_stats_header">'.(Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll).' '. ((strpos($args['rates'], ',') !== false) ? ((Rating_Form::form_types($this_form_query['type'], 'type') == "tud" ? $tudTotal : $ipAll) == 1) ? $exp_rates[0] : $exp_rates[1] : $args['rates']) .'<span class="rf_stats_close">X</span></div>'."\r\n";
				$content .= '<table>'."\r\n";
				$content .= '<thead>'."\r\n";
					$content .= '<tr>'."\r\n";
				for ($i = 1; $i <= $this_form_query['max']; $i++) {
					if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {
						if ($display_up == true && $display_down == true || $display_up == false && $display_down == false) {
							$content .= '<th>'.$titlesText[$i].'</th>'."\r\n";
						} else if ($display_up == true) {
							if ($i == 1) {
								$content .= '<th>'.$titlesText[$i].'</th>'."\r\n";
							}
						} else if ($display_down == true) {
							if ($i == 2) {
								$content .= '<th>'.$titlesText[$i].'</th>'."\r\n";
							}
						}
					} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
						$content .= '<th>'.$titlesText[$i].'</th>'."\r\n";
					}
				}
					$content .= '</tr>'."\r\n";
				$content .= '</thead>'."\r\n";
				$content .= '<tbody>'."\r\n";
					$content .= '<tr>'."\r\n";
				for ($i = 1; $i <= $this_form_query['max']; $i++) {
					if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
						$stats_i = $wpdb->get_var( $wpdb->prepare( " SELECT COUNT(rated) FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated = '". $i ."' AND ".$comment_id_sql."post_id = %s", $args['post_id'] ) );
					} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {
						if ($i == 1) {
							$stats_i = $thumbsUp;
						} else if ($i == 2) {
							$stats_i = $thumbsDown;
						}
					}

					if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
						$content .= '<td>0%</td>'."\r\n";
					} else {
						if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {
							if ($display_up == true && $display_down == true || $display_up == false && $display_down == false) {
								$content .= '<td>'. round(($stats_i == 0 ? 0 : ($stats_i/$tudTotal)*100), 1) .'%</td>'."\r\n";
							} else if ($display_up == true) {
								if ($i == 1) {
									$content .= '<td>'. round(($stats_i == 0 ? 0 : ($stats_i/$tudTotal)*100), 1) .'%</td>'."\r\n";
								}
							} else if ($display_down == true) {
								if ($i == 2) {
									$content .= '<td>'. round(($stats_i == 0 ? 0 : ($stats_i/$tudTotal)*100), 1) .'%</td>'."\r\n";
								}
							}
						} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
							$content .= '<td>'. round(($stats_i == 0 ? 0 : ($stats_i/$ipAll)*100), 1) .'%</td>'."\r\n";
						}
					}
				}
				$content .= '</tr>'."\r\n";
				$content .= '</tbody>'."\r\n";
				$content .= '</table>'."\r\n";
			}

			// User Stats
			$ustats_login = (count($jsonDisplay) > 0 ? (in_array("ustats_login", $jsonDisplay) ? is_user_logged_in() : true) : true);
			if (($args['user_stats'] || $display_ustats_enable) && $ustats_login) {
				$users_voted = $wpdb->get_results( "SELECT * FROM (SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " ORDER BY date DESC) as userLatest WHERE ".$comment_id_sql."post_id = '".$args['post_id']."' GROUP BY user", ARRAY_A );
				//$users_voted = $wpdb->get_results( "SELECT * FROM " . $wpdb->users . " GROUP BY ID ORDER BY user_registered DESC", ARRAY_A );
				// UserStats Row
				$display_usR_val = preg_grep("/^ustats_row-(.*)/", $jsonDisplay);
				$display_usR_reset = reset($display_usR_val);
				$ustats_row = (count($jsonDisplay) > 0 ? (count(preg_grep("/^ustats_row-(.*)/", $jsonDisplay)) > 0 ? trim(str_replace('ustats_row-', '', $display_usR_reset)) : 2) : 2);
				// UserStats Per Row
				$display_usPR_val = preg_grep("/^ustats_per_row-(.*)/", $jsonDisplay);
				$display_usPR_reset = reset($display_usPR_val);
				$ustats_per_row = (count($jsonDisplay) > 0 ? (count(preg_grep("/^ustats_per_row-(.*)/", $jsonDisplay)) > 0 ? trim(str_replace('ustats_per_row-', '', $display_usPR_reset)) : 4) : 4);
				// UserStats Avatar Size
				$display_usAS_val = preg_grep("/^ustats_av_size-(.*)/", $jsonDisplay);
				$display_usAS_reset = reset($display_usAS_val);
				$ustats_av_size = (count($jsonDisplay) > 0 ? (count(preg_grep("/^ustats_av_size-(.*)/", $jsonDisplay)) > 0 ? trim(str_replace('ustats_av_size-', '', $display_usAS_reset)) : 48) : 48);
				$ustats_av_remove = (count($jsonDisplay) > 0 ? (in_array("ustats_av_remove", $jsonDisplay) ? true : false) : false);
				$user_stats_show_rating = (count($jsonDisplay) > 0 ? (in_array("ustats_rating_show", $jsonDisplay) ? true : false) : false);

				$user_stats_row = (count($users_voted) <= $ustats_per_row ? 1 : $ustats_row);
				$user_stats_per_row = (count($users_voted) == 1 ? 1 : $ustats_per_row);
				$user_stats_scroll_height = ((!get_option('show_avatars') || $ustats_av_remove) ? ($user_stats_row*34)+1 : ($user_stats_row*(43+$ustats_av_size))+1);

				$content .= '<div class="rf_user_stats">'."\r\n";
				$content .= '<div class="rf_user_stats_header">' . sprintf ( __( '%s users has voted', 'rating-form' ), count($users_voted) ) . ($args['stats'] == false ? '<span class="rf_stats_close">x</span>' : '') .'</div>'."\r\n";
				$content .= '<div style="overflow:auto; clear:both; height:'.$user_stats_scroll_height.'px;">'."\r\n";
				$content .= '<table>'."\r\n";
				$content .= '<tbody>'."\r\n";
				foreach (array_chunk($users_voted, $user_stats_per_row) as $row_set) {
					$content .= '<tr>'."\r\n";
					foreach ($row_set as $row_user) {
						$row_user_rating = 0;
						switch( $row_user['rated'] ) {
							case '1u':
								$row_user_rating = '+1';
								break;
							case '1d':
								$row_user_rating = '-1';
								break;
							default:
								$row_user_rating = $row_user['rated'];
						}
						$content .= '<td>' . ((!get_option('show_avatars') || $ustats_av_remove) ? '' : get_avatar( $row_user['user'], $ustats_av_size ) . '<br>') . '<span class="user_stats_name'.($user_stats_per_row == 1 && (!get_option('show_avatars') || $ustats_av_remove) ? ' name_left' : '').'">' . get_the_author_meta('user_login', $row_user['user']) . ' <span class="user_stats_rating">' . ($user_stats_show_rating ? '(' . $row_user_rating . ')' : '') . '</span></span></td>'."\r\n";
					}
					$content .= '</tr>'."\r\n";
				}
				$content .= '</tbody>'."\r\n";
				$content .= '</table>'."\r\n";
				$content .= '</div>'."\r\n";
				$content .= '</div>'."\r\n";

			}
			$content .= '</div>'."\r\n";

		}

		} // Ajax Load Disabled

		if ($this_form_query['rich_snippet'] == 1) {

			$post_info = get_post( $args['post_id'] );
			$post_image = wp_get_attachment_image_src( get_post_thumbnail_id( $args['post_id'] ) );
			$itemprop_image = '';
			if ( isset( $post_image[0] ) ) {
				$itemprop_image = $post_image[0];
			}

			if (!empty($post_info)) {
				$content .= '<span itemprop="datePublished" class="rating_rich_snippet" content="' . date( 'Y-m-d', strtotime( $post_info->post_date ) ) . '"></span>';
				$content .= '<span itemprop="headline" class="rating_rich_snippet" content="' . $post_info->post_title . '"></span>';
				$content .= '<img itemprop="image" class="rating_rich_snippet" src="' . $itemprop_image . '" alt="' . (isset( $post_image[0] ) ? $post_info->post_title : '') . '" />';
			}

			$content .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" class="rating_rich_snippet">';

			if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {

				$content .= '<span itemprop="ratingValue">' . round($ratedAllcount, 1).'</span>/<span itemprop="bestRating">' . $this_form_query['max'] . '</span>';
				$content .= '<span itemprop="ratingCount">' . $ipAll . '</span>';

			} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud" && $tudTotal > 0) {

				if ($display_up == true && $display_down == true || $display_up == false && $display_down == false) {
					$content .= '<span itemprop="ratingValue">' . round((($thumbsUp*5+$thumbsDown) / $tudTotal), 1) . '</span>/<span itemprop="bestRating">5</span>';
					$content .= '<span itemprop="ratingCount">' . $tudTotal . '</span>';
				}

			}

			$content .= '</span>';

		}

		if (!empty($args['after_content'])) {
			$content .= '<div class="rating_after_content">' . html_entity_decode($args['after_content']) . '</div>';
		}

		if ($ajax_loaded == false) {
			$content .= '</div>'."\r\n"; // END div rating form
		}

	}

	// Live Top Ratings
	if (!empty($this_form_query['display'])) {
		if (in_array("live_top_ratings", $jsonDisplay)) {
			if ( $ajax_add_rating == true && empty($args['custom_id']) && empty($args['comment_id']) && empty($args['term_id']) ) {

				$args_widget = $args;
				$args_widget['result'] = true;
				$args_widget['score'] = false;
				$args_widget['total'] = false;
				$args_widget['title'] = false;
				$args_widget['tooltip'] = false;
				$args_widget['stats'] = false;
				$args_widget['before_content'] = $args['result'] == false ? "" : html_entity_decode($args['before_content']);
				$args_widget['after_content'] = $args['result'] == false ? "" : html_entity_decode($args['after_content']);

				$content .= '<script type="text/javascript">'."\r\n";
				$content .= 'jQuery(document).ready(function() {'."\r\n";
					$content .= 'jQuery.ajax({'."\r\n";
						$content .= 'type: "POST",'."\r\n";
						$content .= 'url : rating_form_script.ajaxurl,'."\r\n";
						$content .= 'data : { action : "ajax_display_rating_form", args : ' . json_encode($args_widget) . ' },'."\r\n";
						$content .= 'success : function(data) {'."\r\n";
						$content .= 'jQuery(".rf_top_ratings_widget #rf_'.($args_widget['result'] == true ? 'result_' : '').$this_form_query['form_id'].'-' . $post_or_comment_id  . '").html(data);'."\r\n";
						if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
							$content .= 'jQuery(".rf_top_ratings_widget #rf_'.($args_widget['result'] == true ? 'result_' : '').$this_form_query['form_id'].'-' . $post_or_comment_id  . '").closest("li").find(".rf_avg_rating").text("(' . round($ratedAllcount, $this_form_query['round']) . ')");'."\r\n";
						}
						$content .= '}'."\r\n";
					$content .= '});'."\r\n";
				$content .= '});'."\r\n";
				$content .= '</script>'."\r\n";

			}
		}
	}

	// Blocked IP Check
	if ( $args['result'] == false ) {
		$block_ip = $_SERVER['REMOTE_ADDR'];
		$block_ip_row = $wpdb->get_row( "SELECT ip FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '" . $block_ip . "';", ARRAY_A );
		$block_ip_check = $wpdb->num_rows;

		if ($block_ip_check > 0) {
			$content = sprintf ( __( 'Your IP <strong>%s</strong> is banned.', 'rating-form' ), $block_ip_row['ip'] );
		}
	}

	// Defined post type(s)
	$db_post_types = array();
	$post_types_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type_form_id = '".$this_form_query['form_id']."'", ARRAY_A );
	foreach ($post_types_query as $post_type) {
		$db_post_types[] = $post_type['post_type'];
	}
	if ( !is_admin() ) {
		if (!in_array($wp_post_id_query['post_type'], $db_post_types) && count($db_post_types) > 0) {
			$content = null;
		}
	}

	// Defined user role(s)
	$allowed_roles = array();
	$user_roles_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role_form_id = '".$this_form_query['form_id']."'", ARRAY_A );
	foreach ($user_roles_query as $user_role) {
		$allowed_roles[] = $user_role['user_role'];
	}
	if ( !is_admin() ) {
		if (!array_intersect($allowed_roles, $current_user->roles) && count($allowed_roles) > 0) {
			$content = null;
		}
	}

	// Remove form if posts
	if (!empty($this_form_query['display'])) {
		if (in_array("only_single", $jsonDisplay)) {
			if ( !is_single() && !is_admin() ) {
				$content = null;
			} else if ( is_single() && !in_the_loop() && !is_admin() ) {
				$content = null;
			}
		}
	}

	// Remove form if pages
	if (!empty($this_form_query['display'])) {
		if (in_array("only_page", $jsonDisplay)) {
			if ( !is_page() && !is_admin() ) {
				$content = null;
			} else if ( is_page() && !in_the_loop() && !is_admin() ) {
				$content = null;
			}
		}
	}

	// Remove form if categories
	if (!empty($this_form_query['display'])) {
		if (in_array("only_category", $jsonDisplay)) {
			if ( !is_category() && !is_admin() ) {
				$content = null;
			} else if ( is_category() && !in_the_loop() && !is_admin() ) {
				$content = null;
			}
		}
	}

	// Remove form if home page
	if (!empty($this_form_query['display'])) {
		if (in_array("remove_home", $jsonDisplay)) {
			if ( is_home() ) {
				$content = null;
			}
		}
	}

	// Remove form if RSS
	if (!empty($this_form_query['display'])) {
		if (in_array("remove_feed", $jsonDisplay)) {
			if ( is_feed() ) {
				$content = null;
			}
		}
	}

	// Include post_ids
	if (count($jsonDisplay) > 0) {
		if (count(preg_grep("/^post_ids-(.*)/", $jsonDisplay)) > 0) {
			$display_pIds_val = preg_grep("/^post_ids-(.*)/", $jsonDisplay);
			$display_pIds_reset = reset($display_pIds_val);
			$display_post_ids_array = explode(',', trim(str_replace('post_ids-', '', $display_pIds_reset)));
			if (!in_array($args['post_id'], $display_post_ids_array)) {
				$content = null;
			}
		}
	}

	// Exclude post_ids
	if (count($jsonDisplay) > 0) {
		if (count(preg_grep("/^ex_post_ids-(.*)/", $jsonDisplay)) > 0) {
			$display_pIds_val = preg_grep("/^ex_post_ids-(.*)/", $jsonDisplay);
			$display_pIds_reset = reset($display_pIds_val);
			$display_ex_post_ids_array = explode(',', trim(str_replace('ex_post_ids-', '', $display_pIds_reset)));
			if (in_array($args['post_id'], $display_ex_post_ids_array)) {
				$content = null;
			}
		}
	}

	// Rating Form Inactive
	if ($this_form_query['active'] == 0 && $this_form_num > 0) {
		$content = null;
	}

	return $content;
}
// Round to half
function round_to_half($num)
{
	if($num >= ($half = ($ceil = ceil($num))- 0.5) + 0.25) return $ceil;
	else if($num < $half - 0.25) return floor($num);
	else return $half;
}
// Seconds to Format
function secondsFormat($text, $seconds)
{
    $days = intval(intval($seconds) / (3600*24));
    $hours = (intval($seconds) / 3600) % 24;
    $minutes = (intval($seconds) / 60) % 60;
    $seconds = intval($seconds) % 60;

    return sprintf($text, $seconds, $minutes, $hours, $days);
}
// Display rating form
function display_rating_form( $atts ) {

	$args = array();

	global $post;

	// Attributes
	$args = shortcode_atts(
		array(
			'id' => '',
			'post_id' => '',
			'comment_id' => '0',
			'custom_id' => '0',
			'user_id' => '0',
			'term_id' => '0',
			'title' => 'false',
			'score' => 'true',
			'total' => 'true',
			'stats' => 'true',
			'user_stats' => 'false',
			'tooltip' => 'true',
			'result' => 'false',
			'before_content' => '',
			'after_content' => '',
			'rates' => __( 'rating', 'rating-form' ) . ',' . __( 'ratings', 'rating-form' ),
		), $atts );

	// Attr values to boolean
	if (is_string($args['title'])) {
		$args['title'] = $args['title'] == 'true' ? true : false;
	}
	if (is_string($args['score'])) {
		$args['score'] = $args['score'] == 'true' ? true : false;
	}
	if (is_string($args['total'])) {
		$args['total'] = $args['total'] == 'true' ? true : false;
	}
	if (is_string($args['stats'])) {
		$args['stats'] = $args['stats'] == 'true' ? true : false;
	}
	if (is_string($args['user_stats'])) {
		$args['user_stats'] = $args['user_stats'] == 'true' ? true : false;
	}
	if (is_string($args['tooltip'])) {
		$args['tooltip'] = $args['tooltip'] == 'true' ? true : false;
	}
	if (is_string($args['result'])) {
		$args['result'] = $args['result'] == 'true' ? true : false;
	}
	$args['id'] = intval($args['id']);
	$args['comment_id'] = intval($args['comment_id']);
	$args['custom_id'] = strtolower($args['custom_id']);
	$args['user_id'] = intval($args['user_id']);
	$args['term_id'] = intval($args['term_id']);

	if (empty($args['id'])) {
		$args['id'] = 1;
	}
	if (empty($args['post_id'])) {
		if (isset($post)) {
			$args['post_id'] = $post->ID;
		}
	}
	if (have_comments() && empty($args['comment_id']) && !$args['result']) {
		$args['comment_id'] = get_comment_ID();
	}

	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		$args['post_id'] = 1;
	}

	if (!preg_match('/^[a-zA-Z0-9\-\_]+$/i', $args['custom_id']) && !empty($args['custom_id'])) {
		// Custom ID: only letters and numbers allowed
		printf( __( 'Custom ID has invalid characters. Allowed: a-z, A-Z, 0-9, - (dash), _ (underscore)<br><strong>Invalid:</strong> %1$s<br><strong>Valid:</strong> %2$s', 'rating-form' ) . "<br>", $args['custom_id'], preg_replace('/[^a-zA-Z0-9\-\_]/', '', $args['custom_id']));
	} else {
		return wrap_rating_form( $args, false, false, false );
	}
}
add_shortcode( 'rating_form', 'display_rating_form' );

// Display total average ratings
function wrap_rating_form_total( $ajax, $atts ) {

	$args = array();
	$content = null;

	global $post;
	global $wpdb;

	// Attributes
	$args = shortcode_atts(
		array(
			'post_id' => '',
			'comment_id' => '',
			'term_id' => '',
			'round' => '1',
			'ajax' => 'false',
			'type' => 'star',
			'limit' => '',
			'text' => ''
		), $atts );

	if (empty($args['post_id'])) {
		if (isset($post)) {
			$args['post_id'] = $post->ID;
		}
	}

	if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
		$args['post_id'] = 1;
	}

	if (is_string($args['ajax'])) {
		$args['ajax'] = $args['ajax'] == 'true' ? true : false;
	}

	$args['post_id'] = intval($args['post_id']);
	$args['comment_id'] = intval($args['comment_id']);
	$args['term_id'] = intval($args['term_id']);
	$args['round'] = intval($args['round']);

	// Total average rating
	// Check if post id exist in WP
	$wp_post_id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->posts . " WHERE ID = '".$args['post_id']."'", ARRAY_A );
	$wp_post_id_query_num = $wpdb->num_rows;

	if ($wp_post_id_query_num == 0 && !is_admin() && empty($args['term_id'])) {

		if (empty($args['post_id'])) {
			$content .= __( 'Post ID is empty', 'rating-form' );
		} else {
			$content .= sprintf( __( 'Post ID %d not found', 'rating-form' ), $args['post_id'] );
		}

	} else {
		$divID = 'rf_total_'. $args['post_id'];

		// Select fields
		$field = '';

		// If comments found
		if (have_comments() && empty($args['comment_id'])) {
			$commentID = get_comment_ID();
			$field = "comment_id = '" . $commentID ."' AND";
			$divID = 'rf_total_'. $args['post_id'] .'_commentid-' . $commentID;
		}
		if (!empty($args['comment_id'])) {
			$commentID = $args['comment_id'];
			$field = "comment_id = '" . $commentID ."' AND";
			$divID = 'rf_total_'. $args['post_id'] .'_commentid-' . $commentID;
		}

		// Term ID available
		if (!empty($args['term_id'])) {
			$args['post_id'] = 0;
			$commentIDdiv = empty($args['comment_id']) ? '' : '_commentid-' . $args['comment_id'];
			$field = "term_id = '" . $args['term_id'] ."' AND";
			$divID = 'rf_total_termid-' . $args['term_id'] . $commentIDdiv;
		}

		$sltField = $field;

		if (!empty($args['limit'])) {
			$beginEndLimit = explode('-', $args['limit']);
			if (strpos($args['limit'],'-') == false) {
				$sltField = $field . " = '".$args['limit']."'";
			} else {
				$join_limit = '';
				for ($i = $beginEndLimit[0]; $i <= $beginEndLimit[1]; $i++) {
					if ($i == $beginEndLimit[0]) {
						$join_limit .=  "'" . $i ."','";
					} else if ($i == $beginEndLimit[1]) {
						$join_limit .= $i ."'";
					} else {
						$join_limit .= $i ."','";
					}
				}
				$sltField = $field . ' IN('.$join_limit.')';
			}
		}

		// if type = tud Div
		$typeDiv = '';
		if (trim($args['type']) == 'tud') {
			$typeDiv = '_type-tud';
		}

		//begin ajax
		if ($ajax == false) {
			$content .= '<div id="'.$divID.$typeDiv.'">'."\r\n";
		}

		// Ajax load on start
		if ($args['ajax']) {

			$content .= '<script type="text/javascript">'."\r\n";
			$content .= 'jQuery(document).ready(function() {'."\r\n";
				$content .= 'jQuery.ajax({'."\r\n";
					$content .= 'type: "POST",'."\r\n";
					$content .= 'url : rating_form_script.ajaxurl,'."\r\n";
					$content .= 'data : { '."\r\n";
					$content .= 'action : "ajax_display_rating_form_total", '."\r\n";
					$content .= 'post_id : "' . $args['post_id'] . '", '."\r\n";
					$content .= 'comment_id : "' . $args['comment_id'] . '", '."\r\n";
					$content .= 'term_id : "' . $args['term_id'] . '", '."\r\n";
					$content .= 'type : "' . $args['type'] . '" '."\r\n";
					$content .= '}, '."\r\n";
					$content .= 'success : function(data) { jQuery("body").find("#' . $divID . '").html(data) }'."\r\n";
				$content .= '});'."\r\n";
			$content .= '});'."\r\n";
			$content .= '</script>'."\r\n";

		}

		$content .= '<div class="rf_total">'."\r\n";

		// Count ratings
		if (trim($args['type']) == 'tud') {
			$total_tud_query_up = $wpdb->get_row( "SELECT COUNT(*) AS avgTotal FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated = '1u' AND post_id = '" . $args['post_id'] . "' AND ". $sltField ." custom_id != '0'", ARRAY_A );
			$total_tud_query_down = $wpdb->get_row( "SELECT COUNT(*) AS avgTotal FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated = '1d' AND post_id = '" . $args['post_id'] . "' AND ". $sltField ." custom_id != '0'", ARRAY_A );
			$total_tud_query_total = $wpdb->get_row( "SELECT COUNT(*) AS avgTotal FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE (rated = '1u' OR rated = '1d') AND post_id = '" . $args['post_id'] . "' AND ". $sltField ." custom_id != '0'", ARRAY_A );
		} else {
			$total_average_query = $wpdb->get_row( "SELECT COUNT(*) AS avgTotal, (SUM(rated)/COUNT(post_id)) AS avgRated FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rated REGEXP '^[0-9]+$' AND post_id = '" . $args['post_id'] . "' AND ". $sltField ." custom_id != '0'", ARRAY_A );
		}

		if (empty($args['text'])) {
			if (trim($args['type']) == 'tud') {
				$args['text'] = __( 'Up: %1$s Down: %3$s based on %2$s ratings', 'rating-form' );
			} else {
				$args['text'] = __( '%1$s average based on %2$s ratings', 'rating-form' );
			}
		}
		if (trim($args['type']) == 'tud') {
			$args['text'] = sprintf( htmlentities($args['text']), $total_tud_query_up['avgTotal'], $total_tud_query_total['avgTotal'], $total_tud_query_down['avgTotal'] );
		} else {
			$args['text'] = sprintf( htmlentities($args['text']), round( $total_average_query['avgRated'], $args['round'] ), $total_average_query['avgTotal'] );
		}
		$content .= '<div class="rf_total_content">' . html_entity_decode($args['text']) . '</div>'."\r\n";

		$content .= '</div>'."\r\n";

		// end ajax
		if ($ajax == false) {
			$content .= '</div>'."\r\n";
		}
	}
	return $content;
}
add_shortcode( 'rating_form_total', 'display_rating_form_total' );

// Display total average ratings
function display_rating_form_total( $atts ) {
	// Total average rating
	return wrap_rating_form_total( false, $atts );
}
add_shortcode( 'rating_form_total', 'display_rating_form_total' );

// Widget - Wrap Top rating results
function wrap_rating_form_top_results( $instance, $widget, $args = array() ) {
	$content = '';
	global $wpdb;

	if ($widget) {
		$content .= $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			$content .= $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
	} else {
		if (!empty($instance['before_content'])) {
			$content .= html_entity_decode($instance['before_content']);
		}
		$content .= '<div class="rf_top_ratings_widget'. (empty($args['class']) ? '' : ' '.$args['class']) .'">';
		if ( ! empty( $instance['title'] ) ) {
			$content .= html_entity_decode($instance['before_title']) . apply_filters( 'widget_title', $instance['title'] ). html_entity_decode($instance['after_title']);
		}
	}
	$instance['limit'] = empty($instance['limit']) ? 0 : $instance['limit'];
	$instance['form_id'] = empty($instance['form_id']) ? 1 : $instance['form_id'];
	$instance['content_length'] = empty($instance['content_length']) ? 10 : $instance['content_length'];
	$instance['image_size'] = empty($instance['image_size']) ? 48 : $instance['image_size'];
	$instance['author_active'] = empty($instance['author_active']) ? 0 : 1;
	$instance['time'] = empty($instance['time']) ? 0 : $instance['time'];
	$instance['type'] = empty($instance['type']) ? 'post_pages' : $instance['type'];
	$instance['content_active'] = empty($instance['content_active']) ? 0 : 1;
	$instance['image_active'] = empty($instance['image_active']) ? 0 : 1;
	$instance['post_type'] = count($instance['post_type']) == 0 ? array('post','page') : $instance['post_type'];
	if ($instance['time'] > 0) {
		if ($instance['time_field'] == 'hour') {
			$instance['time'] = $instance['time'] * 3600;
		} else if ($instance['time_field'] == 'day') {
			$instance['time'] = $instance['time'] * 86400;
		} else if ($instance['time_field'] == 'week') {
			$instance['time'] = $instance['time'] * 604800;
		} else if ($instance['time_field'] == 'month') {
			$instance['time'] = $instance['time'] * 2629744;
		}
	}

	//Rating Form Settings
	$this_form_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = '".$instance['form_id']."'", ARRAY_A );

	// Type
	$queryStarType = '';
	$queryTudType = '';
	if ($instance['type'] == 'post_pages') {
		$queryStarType = "SELECT post_id, (SUM(rated)/COUNT(post_id)) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND rated REGEXP '^[0-9]+$' " .
		"AND post_id <> 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id = 0 " .
		"GROUP BY post_id " .
		"ORDER BY avgRated DESC";

		$queryTudType = "SELECT post_id, (SUM(IF(rated = '1u', rated, 0))-SUM(IF(rated = '1d', rated, 0))) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND (rated = '1u' || rated = '1d') " .
		"AND post_id <> 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id = 0 " .
		"GROUP BY post_id " .
		"ORDER BY avgRated DESC";
	}
	else if ($instance['type'] == 'comments') {
		$queryStarType = "SELECT comment_id, (SUM(rated)/COUNT(comment_id)) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND rated REGEXP '^[0-9]+$' " .
		"AND post_id <> 0 " .
		"AND comment_id <> 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id = 0 " .
		"GROUP BY comment_id " .
		"ORDER BY avgRated DESC";

		$queryTudType = "SELECT comment_id, (SUM(IF(rated = '1u', rated, 0))-SUM(IF(rated = '1d', rated, 0))) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND (rated = '1u' || rated = '1d') " .
		"AND post_id <> 0 " .
		"AND comment_id <> 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id = 0 " .
		"GROUP BY comment_id " .
		"ORDER BY avgRated DESC";
	}
	else if ($instance['type'] == 'users') {
		$queryStarType = "SELECT post_id, user_id, (SUM(rated)/COUNT(comment_id)) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND rated REGEXP '^[0-9]+$' " .
		"AND post_id <> 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id <> 0 " .
		"AND term_id = 0 " .
		"GROUP BY comment_id " .
		"ORDER BY avgRated DESC";

		$queryTudType = "SELECT post_id, user_id, (SUM(IF(rated = '1u', rated, 0))-SUM(IF(rated = '1d', rated, 0))) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " rf, " . $wpdb->posts . " wp " .
		"WHERE rf.post_id = wp.ID " .
		"AND wp.post_status = 'publish' " .
		"AND wp.post_type IN('" . implode("','", $instance['post_type']) . "') " .
		"AND (rated = '1u' || rated = '1d') " .
		"AND post_id <> 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id <> 0 " .
		"AND term_id = 0 " .
		"GROUP BY comment_id " .
		"ORDER BY avgRated DESC";
	}
	else if ($instance['type'] == 'taxonomies') {
		$queryStarType = "SELECT term_id, (SUM(rated)/COUNT(term_id)) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " " .
		"WHERE rated REGEXP '^[0-9]+$' " .
		"AND post_id = 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id <> 0 " .
		"GROUP BY term_id " .
		"ORDER BY avgRated DESC";

		$queryTudType = "SELECT term_id, (SUM(IF(rated = '1u', rated, 0))-SUM(IF(rated = '1d', rated, 0))) AS avgRated " .
		"FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " " .
		"WHERE (rated = '1u' || rated = '1d') " .
		"AND post_id = 0 " .
		"AND comment_id = 0 " .
		"AND custom_id = '0' " .
		"AND user_id = 0 " .
		"AND term_id <> 0 " .
		"GROUP BY term_id " .
		"ORDER BY avgRated DESC";
	}

	//Ratings Top Results
	if (Rating_Form::form_types($this_form_query['type'], 'type') == "star") {
		$top_ratings_query = $wpdb->get_results( $queryStarType, ARRAY_A );
	} else if (Rating_Form::form_types($this_form_query['type'], 'type') == "tud") {
		$top_ratings_query = $wpdb->get_results( $queryTudType, ARRAY_A );
	}
	$top_ratings_num_rows = $wpdb->num_rows;

	$content .= '<ul class="rf_widget_list">'."\r\n";
	if ($top_ratings_num_rows > 0) {
		$i = 1;
		foreach ($top_ratings_query as $top_ratings_row) {
			if ($instance['type'] == 'post_pages') {
				$post_args = get_post($top_ratings_row['post_id'], ARRAY_A);
				$post_content = strip_shortcodes( $post_args['post_content'] );
				$show_ratings = false;
				if ($instance['time'] == 0) {
					$show_ratings = true;
				} else if (strtotime($post_args['post_date']) >= (time()-$instance['time'])) {
					$show_ratings = true;
				} else {
					$show_ratings = false;
				}
				if ($show_ratings) {
					if ($i <= $instance['limit']) {
						$content .= '<li>';
						$content .= '<a href="' . get_permalink( $post_args['ID'] ) . '">';
						$content .= '<span class="alignleft">';
						$content .= '<span class="rf_place">' . $i . '</span>';
						$content .= '<span class="rf_post_title">' . get_the_title( $post_args['ID'] ) . '</span>';
						if ((Rating_Form::form_types($this_form_query['type'], 'type') == "star")) {
							$content .= '<span class="rf_avg_rating">(' . round($top_ratings_row['avgRated'], $this_form_query['round']) . ')</span>';
						}
						$content .= '</span>';
						$content .= '<span class="alignright">';
						$content .= '<span class="rf_form">' . do_shortcode('[rating_form id="' . $instance['form_id'] . '" post_id="'.$post_args['ID'].'" comment_id="0" result="true" score="false" total="false" title="false" tooltip="false" stats="false"]') . '</span>';
						$content .= '</span>';
						$content .= '</a>';
						if (!empty($post_content)) {
							$content .= (isset($instance['image_active']) && $instance['image_active'] == 1 && has_post_thumbnail($post_args['ID']) ? '<div class="rf_post_image">' . get_the_post_thumbnail( $post_args['ID'], array($instance['image_size'], $instance['image_size'])) . '</div>' : '');
							$content .= ($instance['content_active'] == 1 ? '<div class="rf_post_content">' . wp_trim_words( $post_content, $instance['content_length'] ) . '</div>' : '');
						}
						$content .= '</li>'."\r\n";
					}
				}
			} else if ($instance['type'] == 'comments') {
				$post_args = get_comment($top_ratings_row['comment_id'], ARRAY_A);
				$post_content = strip_shortcodes( $post_args['comment_content'] );
				if ($i <= $instance['limit']) {
					$content .= '<li>';
					$content .= '<a href="' . get_permalink( $post_args['comment_post_ID'] ) . '#comment-' . $post_args['comment_ID'] . '">';
					$content .= '<span class="alignleft">';
					$content .= '<span class="rf_place">' . $i . '</span>';
					$content .= ($instance['author_active'] == 1 ? '<span class="rf_post_title">' . $post_args['comment_author'] . '</span>' : wp_trim_words( $post_content, $instance['content_length']));
					$content .= '<span class="rf_avg_rating">' . ((Rating_Form::form_types($this_form_query['type'], 'type') == "star") ? '(' . round($top_ratings_row['avgRated'], $this_form_query['round']) . ')' : '').'</span>';
					$content .= '</span>';
					$content .= '<span class="alignright">';
					$content .= '<span class="rf_form">' . do_shortcode('[rating_form id="' . $instance['form_id'] . '" post_id="'.$post_args['comment_post_ID'].'" comment_id="'.$post_args['comment_ID'].'" result="true" score="false" total="false" title="false" tooltip="false" stats="false"]') . '</span>';
					$content .= '</span>';
					$content .= '</a>';
					if (!empty($post_content)) {
						$content .= ($instance['content_active'] == 1 && $instance['author_active'] == 1 ? '<div class="rf_post_content">' . wp_trim_words( $post_content, $instance['content_length'] ) . '</div>' : '');
					}
					$content .= '</li>'."\r\n";
				}
			} else if ($instance['type'] == 'taxonomies') {
				$post_args = Rating_Form::get_term_by_id( $top_ratings_row['term_id'], 'ARRAY_A' );
				$post_content = strip_shortcodes( $post_args['description'] );
				if ($i <= $instance['limit']) {
					$content .= '<li>';
					$content .= '<a href="' . get_term_link( $post_args['term_id'], $post_args['taxonomy'] ) . '">';
					$content .= '<span class="alignleft">';
					$content .= '<span class="rf_place">' . $i . '</span>';
					$content .= '<span class="rf_post_title">' . $post_args['name'] . '</span>';
					$content .= ((Rating_Form::form_types($this_form_query['type'], 'type') == "star") ? '<span class="rf_avg_rating">(' . round($top_ratings_row['avgRated'], $this_form_query['round']) . ')</span>' : '');
					$content .= '</span>';
					$content .= '<span class="alignright">';
					$content .= '<span class="rf_form">' . do_shortcode('[rating_form id="' . $instance['form_id'] . '" term_id="'.$post_args['term_id'].'" result="true" score="false" total="false" title="false" tooltip="false" stats="false"]') . '</span>';
					$content .= '</span>';
					$content .= '</a>';
					if (!empty($post_content)) {
						$content .= ($instance['content_active'] == 1 ? '<div class="rf_post_content">' . wp_trim_words( $post_content, $instance['content_length'] ) . '</div>' : '');
					}
					$content .= '</li>'."\r\n";
				}
			} else if ($instance['type'] == 'users') {
				$post_args = get_userdata($top_ratings_row['user_id']);
				if ($i <= $instance['limit']) {
					$content .= '<li>';
					$content .= '<span class="alignleft">';
					$content .= '<span class="rf_place">' . $i . '</span>';
					$content .= '<span class="rf_post_title">' . $post_args->display_name . '</span>';
					$content .= '<span class="rf_avg_rating">' . ((Rating_Form::form_types($this_form_query['type'], 'type') == "star") ? '(' . round($top_ratings_row['avgRated'], $this_form_query['round']) . ')' : '').'</span>';
					$content .= '</span>';
					$content .= '<span class="alignright">';
					$content .= '<span class="rf_form">' . do_shortcode('[rating_form id="' . $instance['form_id'] . '" post_id="'.$top_ratings_row['post_id'].'" user_id="'.$post_args->ID.'" result="true" score="false" total="false" title="false" tooltip="false" stats="false"]') . '</span>';
					$content .= '</span>';
					$content .= '</li>'."\r\n";
				}
			}
			$i++;
		}
	} else {
		$content .= '<li>' . __( 'No results found.', 'rating-form' ) . '</li>';
	}
	$content .= '</ul>'."\r\n";
	if ($widget) {
		$content .= $args['after_widget'];
	} else {
		$content .= '</div>';
		if (!empty($instance['after_content'])) {
			$content .= html_entity_decode($instance['after_content']);
		}
	}

	return $content;
}

// Display rating form top results
function display_rating_form_top_results( $atts ) {

	// Attributes
	$args = shortcode_atts(
		array(
			'form_id' => '1',
			'before_title' => '<h3>',
			'title' => 'Top 5 Ratings',
			'after_title' => '</h3>',
			'limit' => '5',
			'content_length' => '10',
			'content_active' => 'false',
			'image_size' => '48',
			'image_active' => 'false',
			'time' => '',
			'time_field' => '',
			'type' => 'post_pages',
			'post_type' => 'post,page',
			'before_content' => '',
			'after_content' => '',
			'class' => '',
		), $atts );

	$args['form_id'] = intval( $args['form_id'] );
	$args['before_title'] = htmlentities($args['before_title']);
	$args['title'] = ( ! empty( $args['title'] ) ) ? strip_tags( $args['title'] ) : '';
	$args['after_title'] = htmlentities($args['after_title']);
	$args['limit'] = intval( $args['limit'] );
	$args['content_length'] = intval( $args['content_length'] );
	$args['content_active'] = $args['content_active'] == 'true' ? 1 : 0;
	$args['image_size'] = intval( $args['image_size'] );
	$args['image_active'] = $args['image_active'] == 'true' ? 1 : 0;
	$args['time'] = intval($args['time']);
	$args['time_field'] = $args['time_field'];
	$args['type'] = empty($args['type']) ? 'post_pages' : $args['type'];
	$splitPostType = explode(',', $args['post_type']); // split post types to array
	$args['post_type'] = count( $splitPostType ) == 0 ? array('post,page') : $splitPostType;

	return wrap_rating_form_top_results($args, false);
}
add_shortcode( 'rating_form_top_rating_results', 'display_rating_form_top_results' );
?>
