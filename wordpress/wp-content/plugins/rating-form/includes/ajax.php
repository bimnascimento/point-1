<?php
function rating_form_rating_add() {
	if(isset($_POST['form_id']) && isset($_POST['rated'])) {
		
		global $wpdb;
		
		$current_user = wp_get_current_user();
		$ip = $_SERVER['REMOTE_ADDR'];
		$user = $current_user->ID;
		preg_match("/rf_(\d*)/", $_POST['form_id'], $form_id_array);
		$form_id = empty($form_id_array[1]) ? 0 : $form_id_array[1];
		preg_match("/postid_(\d*)/", $_POST['form_id'], $post_id_array);
		$post_id = empty($post_id_array[1]) ? 0 : $post_id_array[1];
		preg_match("/commentid_(\d*)/", $_POST['form_id'], $comment_id_array);
		$comment_id = empty($comment_id_array[1]) ? 0 : $comment_id_array[1];
		preg_match("/customid_(.*)/", $_POST['form_id'], $custom_id_array);
		$custom_id = empty($custom_id_array[1]) ? 0 : $custom_id_array[1];
		preg_match("/userid_(\d*)/", $_POST['form_id'], $user_id_array);
		$user_id = empty($user_id_array[1]) ? 0 : $user_id_array[1];
		preg_match("/termid_(\d*)/", $_POST['form_id'], $term_id_array);
		$term_id = empty($term_id_array[1]) ? 0 : $term_id_array[1];
		$rated = str_replace('rate_'.$form_id.'_', '', $_POST['rated']);
		$score = $_POST['score'] == 'true' ? true : false;
		$total = $_POST['total'] == 'true' ? true : false;
		$rates = trim($_POST['rates']);
		$tooltip = $_POST['tooltip'] == 'true' ? true : false;
		$title = $_POST['title'] == 'true' ? true : false;
		$stats = $_POST['stats'] == 'true' ? true : false;
		$user_stats = $_POST['user_stats'] == 'true' ? true : false;
		$edit_rating_on = $_POST['edit_rating'] == 'false' ? false : true;
		$edit_rating_id = str_replace('edit_', '', $_POST['edit_rating']);
		$before_content = empty($_POST['before_content']) ? '' : $_POST['before_content'];
		$after_content = empty($_POST['after_content']) ? '' : $_POST['after_content'];
		
		if ($edit_rating_on == false) { 
			// Insert rating
			$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_RATED, array(
							'post_id' => $post_id,
							'comment_id' => $comment_id,
							'custom_id' => $custom_id,
							'user_id' => $user_id,
							'term_id' => $term_id,
							'ip' => $ip,
							'user' => $user,
							'rated' => $rated ));
							
			$new_rating_id = $wpdb->insert_id;
			do_action( 'rating_form_new_rating', $new_rating_id, $post_id, $user );
			// Update post meta
			$pmeta_votes = get_post_meta($post_id, 'rf_total_votes', true);
			$pmeta_up = get_post_meta($post_id, 'rf_votes_up', true);
			$pmeta_down = get_post_meta($post_id, 'rf_votes_down', true);
			if (!empty($pmeta_votes) && is_numeric($rated)) {
				update_post_meta($post_id, 'rf_total_votes', $pmeta_votes+1);
			}
			if (!empty($pmeta_up) && $rated == '1u') {
				update_post_meta($post_id, 'rf_votes_up', $pmeta_up+1);
			}
			if (!empty($pmeta_down) && $rated == '1d') {
				update_post_meta($post_id, 'rf_votes_down', $pmeta_down+1);
			}
		} else {
			//Edit rating
			$wpdb->update( $wpdb->prefix.Rating_Form::TBL_RATING_RATED, 
					array( 
						'rated' => $rated
					), 
					array( 'rate_id' => intval($edit_rating_id) ), 
					array( 
						'%s'
					), 
					array( '%d' ) 
				);
		}
		
		//Rating Form
		$atts = array(
			'id' => intval($form_id),
			'post_id' => intval($post_id),
			'comment_id' => intval($comment_id),
			'custom_id' => $custom_id,
			'user_id' => intval($user_id),
			'term_id' => intval($term_id),
			'title' => $title,
			'score' => $score,
			'total' => $total,
			'stats' => $stats,
			'user_stats' => $user_stats,
			'tooltip' => $tooltip,
			'result' => false,
			'rates' => $rates,
			'before_content' => $before_content,
			'after_content' => $after_content
		);
		
		echo wrap_rating_form( $atts, true, true, true );
	}
	die();
}
// Ajax Load
function ajax_display_rating_form() {
	$args = array();
	$args = $_POST['args'];
	$args['id'] = intval($args['id']);
	$args['post_id'] = intval($args['post_id']);
	$args['comment_id'] = intval($args['comment_id']);
	$args['custom_id'] = $args['custom_id'];
	$args['user_id'] = intval($args['user_id']);
	$args['term_id'] = intval($args['term_id']);
	$args['title'] = $args['title'] == 'true' ? true : false;
	$args['score'] = $args['score'] == 'true' ? true : false;
	$args['total'] = $args['total'] == 'true' ? true : false;
	$args['stats'] = $args['stats'] == 'true' ? true : false;
	$args['user_stats'] = $args['user_stats'] == 'true' ? true : false;
	$args['tooltip'] = $args['tooltip'] == 'true' ? true : false;
	$args['result'] = $args['result'] == 'true' ? true : false;
	$args['before_content'] = html_entity_decode($args['before_content']);
	$args['after_content'] = html_entity_decode($args['after_content']);
	
	if (isset($args)) {
		echo wrap_rating_form( $args, false, true, true );
	}
	die();
}
// Ajax Load Total Average Ratings
function ajax_display_rating_form_total() {
	$args = array();
	
	$args['post_id'] = empty($_POST['post_id']) ? 0 : intval($_POST['post_id']);
	$args['comment_id'] = empty($_POST['comment_id']) ? 0 : intval($_POST['comment_id']);
	$args['term_id'] = empty($_POST['term_id']) ? 0 : intval($_POST['term_id']);
	$args['type'] = $_POST['type'] == 'tud' ? 'tud' : 'star';
	
	if (isset($args)) {
		echo wrap_rating_form_total( true, $args );
	}
	die();
}
//Add IP
function rating_form_add_ip() {
	if(isset($_POST['ip']) && isset($_POST['reason'])) {
		global $wpdb;
		
		$ip = trim($_POST['ip']);
		$reason = trim($_POST['reason']);
		
		$exist_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '". $ip ."';", ARRAY_A );
		$exist_num_rows = $wpdb->num_rows;
		
		if ($exist_num_rows == 0) {
			if (empty($ip)) {
				echo '<div class="error"><p>' . __( 'Field IP is empty', 'rating-form' ) . '</p></div>';
			} else {
				$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_BLOCK_IP, array(
								'ip' => $ip,
								'reason' => $reason ));
				
				echo '<script>window.location.href = "' . admin_url( ) . 'admin.php?page=' . Rating_Form::PAGE_BLOCK_IP_SLUG . '&message=1"</script>';
			}
		} else {
			echo '<div class="error"><p>' . __( 'IP is already blocked', 'rating-form' ) . '</p></div>';
		}
	} else {
		echo '<div class="error"><p>' . __( 'Error! IP not added.', 'rating-form' ) . '</p></div>';
	}
	die();
}
//Edit IP
function rating_form_block_ip_edit() {
	if(isset($_POST['ip'], $_POST['edited_ip'], $_POST['reason'])) {
		global $wpdb;
		
		$ip = trim($_POST['ip']);
		$edited_ip = trim($_POST['edited_ip']);
		$reason = trim($_POST['reason']);
		
		$exist_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '". $ip ."';", ARRAY_A );
		$exist_num_rows = $wpdb->num_rows;
		$exist_query2 = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '". $edited_ip ."';", ARRAY_A );
		$exist_num_rows2 = $wpdb->num_rows;
		
		if (empty($edited_ip)) {
			echo '<span class="ip_error" style="color:red"> | ' . __( 'Field IP is empty', 'rating-form' ) . '</span>';
		} else if ($exist_num_rows2 > 0) {
			echo '<span class="ip_error" style="color:red"> | ' . __( 'Error! IP already exists.', 'rating-form' ) . '</span>';
		} else {
			if ($exist_num_rows > 0) {
				$wpdb->update( $wpdb->prefix.Rating_Form::TBL_RATING_BLOCK_IP, array(
						'ip' => $edited_ip,
						'reason' => $reason
				), array('ip' => $ip ) );
				
				echo '<span class="ip_edited" style="color:green"> | ' . __( 'Successfully updated!', 'rating-form' ) . '</span>';
			}
		}
	} else {
		echo '<span class="ip_error" style="color:red"> | ' . __( 'Error! IP not updated.', 'rating-form' ) . '</span>';
	}
	die();
}
?>