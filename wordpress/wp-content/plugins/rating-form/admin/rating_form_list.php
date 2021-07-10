<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Rating_Form_Table extends WP_List_Table {

	//Prepare the items for the table to process
	public function prepare_items()
    {
		global $wpdb;

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		$query = 'SELECT * FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM;
        $data = $wpdb->get_results($query, ARRAY_A);
        usort($data, array($this, 'sort_data'));

        $perPage = 10;
        $currentPage = $this->get_pagenum();
        $totalItems = count($data);

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $data = array_slice($data,(($currentPage-1)*$perPage),$perPage);

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

	//Override the parent columns method.
	//Defines the columns to use in your listing table
	public function get_columns()
    {
        $columns = array(
            'form_id' => __( 'ID', 'rating-form' ),
            'type' => __( 'Type', 'rating-form' ),
            'restrict_ip' => __( 'Restrict IP', 'rating-form' ),
            'shortcode' => 'Shortcode',
            'date' => __( 'Created On', 'rating-form' )
        );

        return $columns;
    }

	//Define what data to show on each column of the table
    public function column_default( $item, $column_name )
    {
		$tools_options = get_option(Rating_Form::PAGE_TOOLS_RATING_SLUG);
        switch( $column_name ) {
            case 'form_id':
				$admin_url = admin_url( ) . 'admin.php';
				$item_form_id = '';
				$item_form_id .= '<strong>' . $item['form_id'] . '</strong>' . (empty($item['form_name']) ? '' : ' (' . $item['form_name'] . ')');
				$item_form_id .= '<div class="row-actions">';
				$item_form_id .= '<span class="settings"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_FORM_RATING_SLUG.'&settings='. $item['form_id'] .'">'. __( 'Settings', 'rating-form' ) .'</a> | </span>';
				$item_form_id .= '<span class="style"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_FORM_RATING_SLUG.'&style='. $item['form_id'] .'">'. __( 'Style', 'rating-form' ) .'</a></span>';
				$item_form_id .= '<span class="duplicate"> | <a href="'. $admin_url .'?page='.Rating_Form::PAGE_FORM_RATING_SLUG.'&duplicate='. $item['form_id'] .'">'. __( 'Duplicate', 'rating-form' ) .'</a> | </span>';
				$item_form_id .= '<span class="delete"><a onclick="return confirm(&quot;'. __( 'Are you sure you want to delete this?', 'rating-form' ) .'&quot;)" href="'. $admin_url .'?page='.Rating_Form::PAGE_FORM_RATING_SLUG.'&delete='. $item['form_id'] .'"><strong>X</strong></a></span>';
				$item_form_id .= '</div>';
				$item['form_id'] = $item_form_id;
            case 'type':
				$item['type'] = '<span class="' . Rating_Form::form_types( $item['type'], "class" ) . '"></span>' . Rating_Form::form_types( $item['type'], "name" );
            case 'restrict_ip':
				$item['restrict_ip'] = $item['restrict_ip'] == 1 ? 'Yes' : 'No';
            case 'shortcode':
				$item['shortcode'] = '<input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;'.$item['form_id'].'&quot;]">';
			case 'date':
				$item['date'] = date('Y/m/d @ H:i', strtotime($item['date']));
                return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

	//Define which columns are hidden
    public function get_hidden_columns()
    {
        return array();
    }

	//Define the sortable columns
    public function get_sortable_columns()
    {
        return array(
			'form_id' => array('form_id', true),
			'type' => array('type', false),
			'restrict_ip' => array('restrict_ip', false),
			'date' => array('date', false)
			);
    }

	//Allows you to sort the data by the variables set in the $_GET
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'date';
        $order = 'desc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strnatcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }
}

// Rating list table
function Rating_Forms() {
?>
	<div class="wrap rf_wrap" id="rating_form_add_edit">
	<?php Rating_Form::admin_menus( 'Rating Forms' ); ?>
<?php
	$upload_dir = wp_upload_dir();
	$stylefolder = $upload_dir['basedir'].'/rating-form/css/';
	$new_rating_id = null;

	global $wpdb;
	$wpdb->show_errors();

	// Get settings or style
	if (isset($_GET['settings'])) {
		$getFormID = $_GET['settings'];
	} else if (isset($_GET['style'])) {
		$getFormID = $_GET['style'];
	} else {
		$getFormID = 0;
	}

	$id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = '".$getFormID."'", ARRAY_A );
	$rowCount = $wpdb->num_rows;

	// Get rating form upload dir
	$thisFormUploadDir = $upload_dir['basedir'].'/rating-form/icons/' . $id_query['form_id'];

	if ($rowCount > 0) {
	//Rating titles
	$titlesID = array();
	$titlesText = array();
	if (isset($_POST['text'])) {
		$form_titles_query = $_POST['text'];
	} else {
		$form_titles_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_form_id = '".$id_query['form_id']."'", ARRAY_A );
	}

	foreach ($form_titles_query as $form_title) {
		$form_title_w = isset($_POST['text']) ? $form_title : $form_title['title_id'];
		$titles_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$form_title_w."'", ARRAY_A );
		$titles_num_rows = $wpdb->num_rows;
		foreach ($titles_query as $title_text) {
			$titlesID[] = $title_text['title_id'];
			$titlesText[$title_text['position']] = $title_text['text'];
		}
	}

	//Type Names
	$type_name = '';
	$type_color = '';
	if (intval($id_query['type']) == 0) {
		$type_name = 'Custom';
	} else if (intval($id_query['type']) == 1) {
		$type_name = 'Star';
		$type_color = '#ffd700';
	} else if (intval($id_query['type']) == 2) {
		$type_name = 'Thumbs Up and Down';
	} else if (intval($id_query['type']) == 3) {
		$type_name = 'Smiley';
		$type_color = '#0074a2';
	} else if (intval($id_query['type']) == 4) {
		$type_name = 'Heart';
		$type_color = '#ff0000';
	} else if (intval($id_query['type']) == 5) {
		$type_name = 'Plus and Min';
		$type_color = '';
	} else if (intval($id_query['type']) == 6) {
		$type_name = 'Circle';
		$type_color = '#0094FF';
	}

	if (isset($_GET['settings'])) {

	if (isset($_POST['submit'])) {
		$msg_success = null;
		$msg_error = array();
		$style_content = null;
		$type = $_POST['type'];
		$form_name = $_POST['form_name'];
		$active = $_POST['active'];
		$max = $_POST['max'];
		$restrict_ip = $_POST['restrict_ip'];
		$user_logged_in = $_POST['user_logged_in'];
		$ajax_load = $_POST['ajax_load'];
		$rich_snippet = $_POST['rich_snippet'];
		$spinner = $_POST['spinner'];
		$round = $_POST['round'];
		$rtl = $_POST['rtl'];
		$limitation = $_POST['limitation'];
		$time = $_POST['time'];
		$post_ids = $_POST['post_ids'];
		$ex_post_ids = $_POST['ex_post_ids'];
		$ustats_row = $_POST['ustats_row'];
		$ustats_per_row = $_POST['ustats_per_row'];
		$ustats_av_size = $_POST['ustats_av_size'];
		$redirect_url = $_POST['redirect_url'];
		$redirect_target = $_POST['redirect_target'];
		$display = isset($_POST['display']) ? $_POST['display'] : '';
		$txt_ty = $_POST['txt_ty'];
		$txt_rated = $_POST['txt_rated'];
		$txt_login = $_POST['txt_login'];
		$txt_limit = $_POST['txt_limit'];
		$txt_edit_rating = $_POST['txt_edit_rating'];
		$text = $_POST['text'];
		//Post Types
		$add_post_type = isset($_POST['add_post_type']) ? $_POST['add_post_type'] : null;
		$del_post_type = isset($_POST['del_post_type']) ? $_POST['del_post_type'] : null;
		$post_types_merged = null;
		//User Roles
		$add_user_role = isset($_POST['add_user_role']) ? $_POST['add_user_role'] : null;
		$del_user_role = isset($_POST['del_user_role']) ? $_POST['del_user_role'] : null;
		$user_roles_merged = null;

		if (isset($type) && isset($form_name) && isset($active) && isset($max) && isset($restrict_ip) && isset($user_logged_in) && isset($ajax_load) && isset($rich_snippet) && isset($spinner) && isset($round)
				&& isset($rtl) && isset($limitation) && isset($time) && isset($txt_ty) && isset($txt_rated) && isset($txt_login) && isset($txt_limit) && isset($txt_edit_rating)
				&& isset($text)) {

			if(strlen($form_name) > 50) {
				$msg_error[] = sprintf( __( '<strong>Name</strong> has %s characters. Allowed: 50 characters', 'rating-form' ), strlen($form_name));
			}

			if (!empty($_FILES['icon_empty']['name'])) {
				$icon_empty_ext = explode('.', strtolower($_FILES['icon_empty']['name']));
				if($_FILES['icon_empty']['error']) {
					$msg_error[] = '<strong>Icon Empty</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_empty']['error'];
				} else if ($icon_empty_ext[1] != 'png') {
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_empty_ext[0], $icon_empty_ext[1] );
				}
			}

			if (!empty($_FILES['icon_full']['name'])) {
				$icon_full_ext = explode('.', strtolower($_FILES['icon_full']['name']));
				if($_FILES['icon_full']['error']) {
					$msg_error[] = '<strong>Icon Ful</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_full']['error'];
				} else if ($icon_full_ext[1] != 'png') {
					$msg_error[] = 'Only <strong>.png</strong> extension allowed. E.g. for transparency of full icon.<br>You uploaded: '.$icon_full_ext[0].'.<strong>'.$icon_full_ext[1].'</strong>';
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_full_ext[0], $icon_full_ext[1] );
				}
			}

			if (!empty($_FILES['icon_half']['name'])) {
				$icon_half_ext = explode('.', strtolower($_FILES['icon_half']['name']));
				if($_FILES['icon_half']['error']) {
					$msg_error[] = '<strong>Icon Half</strong>: ' . __( 'The image you uploaded triggered the following error: ', 'rating-form' ) . $_FILES['icon_half']['error'];
				} else if ($icon_half_ext[1] != 'png') {
					$msg_error[] = sprintf( __( 'Only <strong>.png</strong> extension allowed. E.g. for transparency of image.<br>You uploaded: %1$s.<strong>%2$s</strong>', 'rating-form' ), $icon_half_ext[0], $icon_half_ext[1] );
				}
			}

			if ($restrict_ip >= 0 && $limitation == 0) {
				$msg_error[] = sprintf( __( '<strong>Limit</strong> is set to %1$d and <strong>Time</strong> set to %2$d seconds. To rate atleast 1 time in %2$s seconds, set <strong>Limit</strong> to 1.', 'rating-form' ), $limitation, $time );
			}

			if ($restrict_ip == 1 && $time > 0) {
				$msg_error[] = sprintf( __( '<strong>Time</strong> is set to %d seconds. This will not work, because <strong>Restrict IP</strong> is enabled.', 'rating-form' ), $limitation, $time );
			}

			if ($restrict_ip == 0 && $time == 0 && $limitation > 1) {
				$msg_error[] = sprintf( __( '<strong>Limit</strong> is set to %1$d and <strong>Time</strong> set to %2$s seconds. You have two options:<br>- Enable <strong>Restrict IP</strong> to rate %1$d times with no time duration<br>- Set <strong>Time</strong> duration above 0 seconds', 'rating-form' ), $limitation, $time );
			}

			if (count($msg_error) == 0) {

				global $wpdb;

				if (isset($post_ids) && !empty($post_ids)) {
					$display[] = "post_ids-" . trim($post_ids);
				}

				if (isset($ex_post_ids) && !empty($ex_post_ids)) {
					$display[] = "ex_post_ids-" . trim($ex_post_ids);
				}

				if (isset($ustats_row) && !empty($ustats_row)) {
					$display[] = "ustats_row-" . trim($ustats_row);
				}

				if (isset($ustats_per_row) && !empty($ustats_per_row)) {
					$display[] = "ustats_per_row-" . trim($ustats_per_row);
				}

				if (isset($ustats_av_size) && !empty($ustats_av_size)) {
					$display[] = "ustats_av_size-" . trim($ustats_av_size);
				}

				if (isset($redirect_url) && !empty($redirect_url)) {
					$display[] = "redirect_url-" . trim($redirect_url);
				}

				if (isset($redirect_target) && !empty($redirect_target)) {
					$display[] = "redirect_target-" . trim($redirect_target);
				}

				if (count($display) > 0 && !empty($display)) {
          // Display[] Attribute
          // Remove Empty values
          for ($i = 0 ; $i < count($display); $i++) {
            if (empty($display[$i])) {
              unset($display[$i]);
            }
            // Remove spinner(digit) if spinner is set to 0
            if ($spinner == 0 && preg_match("/^spinner(\d)/", $display[$i])) {
              unset($display[$i]);
            }
          }
          if (count($display) > 0) {
					  $display = json_encode(array_values($display));
          } else {
            $display = '';
          }
				}

				$wpdb->update( $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM, array(
						'form_name' => $form_name,
						'type' => intval($type),
						'active' => intval($active),
						'max' => intval($max),
						'restrict_ip' => intval($restrict_ip),
						'user_logged_in' => intval($user_logged_in),
						'ajax_load' => intval($ajax_load),
						'rich_snippet' => intval($rich_snippet),
						'spinner' => intval($spinner),
						'round' => intval($round),
						'rtl' => intval($rtl),
						'limitation' => intval($limitation),
						'time' => intval($time),
						'display' => $display,
						'txt_ty' => $txt_ty,
						'txt_rated' => $txt_rated,
						'txt_login' => $txt_login,
						'txt_limit' => $txt_limit,
						'txt_edit_rating' => $txt_edit_rating
				), array('form_id' => $id_query['form_id'] ) );

				if (intval($type) == 0) {
					//upload custom icons
					//create folder for icons if it doesn't exists
					if (!file_exists($thisFormUploadDir)) {
						wp_mkdir_p( $thisFormUploadDir );
					} else {
						if (!empty($_FILES['icon_empty']['name'])) {
							if (file_exists( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-empty.png' )) {
								unlink( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-empty.png' );
							}
							move_uploaded_file($_FILES['icon_empty']['tmp_name'], $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-empty.png');
						}
						if (!empty($_FILES['icon_full']['name'])) {
							if (file_exists( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-full.png' )) {
								unlink( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-full.png' );
							}
							move_uploaded_file($_FILES['icon_full']['tmp_name'], $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-full.png');
						}
						if (!empty($_FILES['icon_half']['name'])) {
							if (file_exists( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-half.png' )) {
								unlink( $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-half.png' );
							}
							move_uploaded_file($_FILES['icon_half']['tmp_name'], $thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-half.png');
						}
					}
				}

				//titles
				if (count($text) > 0) {
					$text_i = 0;
					foreach ($text as $title_id) {
						$text_i++;
						$check_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_id = '".$title_id."' AND title_form_id = '".$id_query['form_id']."'", ARRAY_A );
						$check_num_rows = $wpdb->num_rows;
						// get title info
						$title_pos = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$title_id."'", ARRAY_A );
						//print_r($title_pos);
						$title_form_post = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_id != '".$title_id."' AND title_form_id = '".$id_query['form_id']."'", ARRAY_A );
						//print_r($title_form_post);
						if ($check_num_rows == 0 && $text_i <= $max) {
							// delete title from form_titles if position already in use
							foreach ($title_form_post as $title_form) {
								if ($title_form['position'] == $title_pos['position']) {
									$wpdb->query(
										$wpdb->prepare(
												"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_id = '".$title_form['title_id']."' AND title_form_id = %d",
												$id_query['form_id']
											)
									);
								}
							}
							$wpdb->insert( $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES, array(
									'title_form_id' => $id_query['form_id'],
									'title_id' => $title_id,
									'position' => $title_pos['position']
							) );
						}
					}
				}

				//add post types
				if (count($add_post_type) > 0) {
					foreach ($add_post_type as $add_post_type_q) {
						$post_type_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type = '".$add_post_type_q."' AND post_type_form_id = '".$id_query['form_id']."'", ARRAY_A );
						$post_type_num_rows = $wpdb->num_rows;
						if ($post_type_num_rows == 0) {
							$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_POST_TYPES, array(
									'post_type_form_id' => $id_query['form_id'],
									'post_type' => $add_post_type_q
							) );
						}
					}
				}

				//del post types
				if (count($del_post_type) >= 0) {
					if (count($add_post_type) > 0) {
						if (count($del_post_type) == 0) {
							$post_types_merged = join("','", $add_post_type);
						} else {
							$post_types_merged = join("','", array_merge($del_post_type, $add_post_type));
						}
					} else if (count($del_post_type) == 0) {
						if ($add_post_type == 0) {
							$post_types_merged = null;
						} else {
							$post_types_merged = join("','", $add_post_type);
						}
					} else {
						$post_types_merged = join("','", $del_post_type);
					}

					$wpdb->query(
						$wpdb->prepare(
								"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type NOT IN ('".$post_types_merged."') AND post_type_form_id = %d",
								$id_query['form_id']
							)
					);
				}

				//add user roles
				if (count($add_user_role) > 0) {
					foreach ($add_user_role as $add_user_role_q) {
						$user_role_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role = '".$add_user_role_q."' AND user_role_form_id = '".$id_query['form_id']."'", ARRAY_A );
						$user_role_num_rows = $wpdb->num_rows;
						if ($user_role_num_rows == 0) {
							$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_USER_ROLES, array(
									'user_role_form_id' => $id_query['form_id'],
									'user_role' => $add_user_role_q
							) );
						}
					}
				}

				//del user roles
				if (count($del_user_role) >= 0) {
					if (count($add_user_role) > 0) {
						if (count($del_user_role) == 0) {
							$user_roles_merged = join("','", $add_user_role);
						} else {
							$user_roles_merged = join("','", array_merge($del_user_role, $add_user_role));
						}
					} else if (count($del_user_role) == 0) {
						if ($add_user_role == 0) {
							$user_roles_merged = null;
						} else {
							$user_roles_merged = join("','", $add_user_role);
						}
					} else {
						$user_roles_merged = join("','", $del_user_role);
					}

					$wpdb->query(
						$wpdb->prepare(
								"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role NOT IN ('".$user_roles_merged."') AND user_role_form_id = %d",
								$id_query['form_id']
							)
					);
				}

				$msg_success .= __( 'Successfully updated!', 'rating-form' );

			}

		} else {
			$msg_error[] = __( 'Error! Rating form not updated.', 'rating-form' );
		}

		if ( strlen( $msg_success ) > 0) {
			echo '<div class="updated"><p>' . $msg_success . '<br>';
			echo '<strong>Shortcode:</strong> <input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;'.$id_query['form_id'].'&quot;]"></p></div>';
		}

		if ( count( $msg_error ) > 0) {
			foreach ($msg_error as $msg_error_txt)
				echo '<div class="error"><p>' . $msg_error_txt . '</p></div>';
		}
	}
	$qJsonDisplay = empty($id_query['display']) ? array() : json_decode($id_query['display']);
?>
		<form method="post" enctype="multipart/form-data">
		<div class="rf_header_title">
			<strong><?php _e( 'Settings', 'rating-form' ); ?></strong>: Rating Form <?php echo $id_query['form_id']; ?>
			<a class="button" href="admin.php?page=<?php echo Rating_Form::PAGE_FORM_RATING_SLUG . '&style=' . $id_query['form_id']; ?>"><?php _e( 'Edit Style', 'rating-form' ); ?></a>
			<input id="save_from" type="submit" name="submit" class="button button-primary" value="<?php _e( 'Save', 'rating-form' ); ?>">
		</div>
		<div id="poststuff">
			<?php if ((isset($_POST['type']) ? $_POST['type'] : $id_query['type']) == 0) { ?>
			<div class="postbox">
				<h3><span><?php _e( 'Change Icon Empty', 'rating-form' ); ?></span></h3>
				<p class="pb_inside">
					<img src="<?php echo $upload_dir['baseurl'].'/rating-form/icons/'.$id_query['form_id']; ?>/custom-empty.png" alt="Custom Empty" /><br>
					<?php
						if ( !file_exists($thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-empty.png') ) {
							echo '<span class="description" style="color: red">'. __( 'Image not found! To fix this error, upload below.', 'rating-form' ) .'</span><br>';
						}
					?>
					<span class="description"><?php _e( 'Choose an image to change icon', 'rating-form' ); ?></span>
					<br>
					<input id="file_icon_full" type="file" name="icon_empty" />
				</p>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Change Icon Full (Hover)', 'rating-form' ); ?></span></h3>
				<p class="pb_inside">
					<img src="<?php echo $upload_dir['baseurl'].'/rating-form/icons/'.$id_query['form_id']; ?>/custom-full.png" alt="Custom Full" /><br>
					<?php
						if ( !file_exists($thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-full.png') ) {
							echo '<span class="description" style="color: red">'. __( 'Image not found! To fix this error, upload below.', 'rating-form' ) .'</span><br>';
						}
					?>
					<span class="description"><?php _e( 'Choose an image to change icon', 'rating-form' ); ?></span>
					<br>
					<input id="file_icon_full" type="file" name="icon_full" />
				</p>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Change Icon Half', 'rating-form' ); ?></span></h3>
				<p class="pb_inside">
					<img src="<?php echo $upload_dir['baseurl'].'/rating-form/icons/'.$id_query['form_id']; ?>/custom-half.png" alt="Custom Half" /><br>
					<?php
						if ( !file_exists($thisFormUploadDir . DIRECTORY_SEPARATOR . 'custom-half.png') ) {
							echo '<span class="description" style="color: red">'. __( 'Image not found! To fix this error, upload below.', 'rating-form' ) .'</span><br>';
						}
					?>
					<span class="description"><?php _e( 'Choose an image to change icon', 'rating-form' ); ?></span>
					<br>
					<input id="file_icon_full" type="file" name="icon_half" />
				</p>
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
								<td><input id="form_name" type="text" name="form_name" value="<?php echo isset($_POST['form_name']) ? $_POST['form_name'] : $id_query['form_name']; ?>" /></td>
								<td class="description"><?php _e( 'Name of Rating Form', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Type', 'rating-form' ); ?></strong></td>
								<td><select id="type" name="type">
  							<?php
                  $StarOrTud = Rating_Form::form_types($id_query['type'], 'type') == "star" ? "star" : "tud";
                  for ($iTypes = 0; $iTypes <= Rating_Form::$totalFormTypes; $iTypes++) {
                    if (Rating_Form::form_types($iTypes, 'type') == $StarOrTud) {
  									  echo '<option value="'. $iTypes . '" ' . selected( (isset($_POST['type']) ? $_POST['type'] : $id_query['type']), $iTypes ) . '>' . Rating_Form::form_types($iTypes, 'name') .'</option>';
                    }
                  }
                ?>
								</select></td>
								<td class="description"><?php _e( 'Change type', 'rating-form' ); ?><?php if ((isset($_POST['type']) ? $_POST['type'] : $id_query['type']) != 0) { ?><br><span class="switchToCustom description" style="display: none; color: red"><?php _e( 'Do not forget to upload images after saving settings.', 'rating-form' ); ?></span><?php } ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Active', 'rating-form' ); ?></strong></td>
								<td><select id="active" name="active">
									<?php if ((isset($_POST['active']) ? $_POST['active'] : $id_query['active']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Disable or enable Rating Form', 'rating-form' ); ?></td>
							</tr>
							<?php if (Rating_Form::form_types($id_query['type'], 'type') == "star") { ?>
							<tr>
								<td><strong><?php _e( 'Max', 'rating-form' ); ?></strong></td>
								<td><select id="max" name="max">
								<?php for ($max = 1; $max <= ($id_query['type'] == 3 ? 5 : 10); $max++) { ?>
									<?php if ($max == (isset($_POST['max']) ? $_POST['max'] : $id_query['max'])) { ?>
									<option value="<?php echo $max; ?>" selected="selected"><?php echo $max; ?></option>
									<?php } else { ?>
									<option value="<?php echo $max; ?>"><?php echo $max; ?></option>
									<?php } ?>
								<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Set max icons', 'rating-form' ); ?></td>
							</tr>
							<?php } else if (Rating_Form::form_types($id_query['type'], 'type') == "tud") { ?>
								<input id="max" type="hidden" value="<?php echo (isset($_POST['max']) ? $_POST['max'] : $id_query['max']); ?>" name="max" />
							<?php } ?>
							<tr>
								<td><strong><?php _e( 'Restrict IP', 'rating-form' ); ?></strong></td>
								<td><select id="restrict_ip" name="restrict_ip">
									<?php if ((isset($_POST['restrict_ip']) ? $_POST['restrict_ip'] : $id_query['restrict_ip']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Restrict the same IP address to rate multiple times', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'User Login', 'rating-form' ); ?></strong></td>
								<td><select id="user_logged_in" name="user_logged_in">
									<?php if ((isset($_POST['user_logged_in']) ? $_POST['user_logged_in'] : $id_query['user_logged_in']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'User must login to rate', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Ajax Load', 'rating-form' ); ?></strong></td>
								<td><select id="ajax_load" name="ajax_load">
									<?php if ((isset($_POST['ajax_load']) ? $_POST['ajax_load'] : $id_query['ajax_load']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Useful when using cache plugins', 'rating-form' ); ?></td>
							</tr>
						</tbody>
					</table>
					<p class="alignleft">
					</p>
				</div>
			</div>
			<div class="clear"></div>
			<div id="optional" class="postbox">
				<h3><span><?php _e( 'Optional', 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
					<table class="form-table per100">
						<tbody>
							<tr>
								<td><strong><?php _e( 'Rich Snippet', 'rating-form' ); ?></strong></td>
								<td><select id="rich_snippet" name="rich_snippet">
									<?php if ((isset($_POST['rich_snippet']) ? $_POST['rich_snippet'] : $id_query['rich_snippet']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Show rating result in search engines', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Spinner', 'rating-form' ); ?></strong></td>
								<td>
                  <select id="spinner" name="spinner">
  									<?php if ((isset($_POST['spinner']) ? $_POST['spinner'] : $id_query['spinner']) == 1) { ?>
  									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
  									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
  									<?php } else { ?>
  									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
  									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
  									<?php } ?>
					        </select>
                  <?php if ((isset($_POST['spinner']) ? $_POST['spinner'] : $id_query['spinner']) == 1) { ?>
                  <div class="cyto-spinners">
                    <ul>
                  <?php for ($iSpinners = 1; $iSpinners <= 3; $iSpinners++) { ?>
                    <li>
                      <label>
                        <?php
                          $getSpinner_gp = preg_grep("/^spinner(\d)/", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay);
                          if ($iSpinners == 1 && empty($getSpinner_gp)) {
                          ?>
                          <input type="radio" name="display[]" value="" checked="checked" />
                        <?php } else {?>
                        <input type="radio" name="display[]" value="spinner<?php echo ($iSpinners == 1 ? '' : $iSpinners); ?>" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("spinner" . ($iSpinners == 1 ? '' : $iSpinners), isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> />
                        <?php } ?>
                        <span class="cyto-spin cyto-spinner<?php echo ($iSpinners == 1 ? '' : $iSpinners); ?>"></span>
                      </label>
                    </li>
	                 <?php
                    }
                    ?>
                    </ul>
                  </div>
                  <?php
                  }
                ?>
                </td>
								<td class="description"><?php _e( 'Set spinner animation on update rating', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'RTL', 'rating-form' ); ?></strong></td>
								<td><select id="rtl" name="rtl">
									<?php if ((isset($_POST['rtl']) ? $_POST['rtl'] : $id_query['rtl']) == 1) { ?>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<?php } else { ?>
									<option value="0"><?php _e( 'No', 'rating-form' ); ?></option>
									<option value="1"><?php _e( 'Yes', 'rating-form' ); ?></option>
									<?php } ?>
								</select></td>
								<td class="description"><?php _e( 'Set form direction to rtl (right to left)', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Round', 'rating-form' ); ?></strong></td>
								<td><input id="round" type="text" name="round" value="<?php echo isset($_POST['round']) ? intval($_POST['round']) : $id_query['round']; ?>" /></td>
								<td class="description"><?php _e( 'Set decimal of average rounding', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Limit', 'rating-form' ); ?></strong></td>
								<td><input id="limitation" type="text" name="limitation" value="<?php echo isset($_POST['limitation']) ? intval($_POST['limitation']) : $id_query['limitation']; ?>" /></td>
								<td class="description"><?php _e( 'Set rating limit for each User or IP', 'rating-form' ); ?></td>
							</tr>
							<?php
								$time_custom = false;
								$time_custom2 = false;
								if (!($id_query['time'] == 0 || $id_query['time'] == 3600 || $id_query['time'] == 86400 || $id_query['time'] == 604800 ||
										$id_query['time'] == 2629744 || $id_query['time'] == 31556926)) {
									$time_custom = true;
								}
								if (isset($_POST['time'])) {
									if (!(intval($_POST['time']) == 0 || intval($_POST['time']) == 3600 || intval($_POST['time']) == 86400 || intval($_POST['time']) == 604800 ||
											intval($_POST['time']) == 2629744 || intval($_POST['time']) == 31556926)) {
										$time_custom2 = true;
									}
								}
							?>
							<tr>
								<td><strong><?php _e( 'Time', 'rating-form' ); ?></strong></td>
								<td><select id="time" name="time">
									<option value="0" <?php selected( isset($_POST['time']) ? intval($_POST['time']) : $id_query['time'], 0 ); ?>><?php _e( 'All Time', 'rating-form' ); ?></option>
									<option value="<?php echo isset($_POST['time']) ? intval($_POST['time']) : $id_query['time']; ?>"<?php echo (isset($_POST['time']) ? $time_custom2 == true : $time_custom == true) ? ' selected="selected"' : ''; ?>><?php _e( 'Custom', 'rating-form' ); ?></option>
									<option value="3600" <?php selected( isset($_POST['time']) ? intval($_POST['time']) : $id_query['time'], 3600 ); ?>><?php _e( '1 Hour', 'rating-form' ); ?></option>
									<option value="86400" <?php selected( isset($_POST['time']) ? intval($_POST['time']) : $id_query['time'], 86400 ); ?>><?php _e( '1 Day', 'rating-form' ); ?></option>
									<option value="604800" <?php selected( isset($_POST['time']) ? intval($_POST['time']) : $id_query['time'], 604800 ); ?>><?php _e( '1 Week', 'rating-form' ); ?></option>
									<option value="2629744" <?php selected( isset($_POST['time']) ? intval($_POST['time']) : $id_query['time'], 2629744 ); ?>><?php _e( '1 Month', 'rating-form' ); ?></option>
								</select><span id="time_custom"<?php echo (isset($_POST['time']) ? $time_custom2 == true : $time_custom == true) ? '' : ' style="display: none;"'; ?>><br><input type="text" name="time_custom" value="<?php echo isset($_POST['time']) ? intval($_POST['time']) : $id_query['time']; ?>" /></span></td>
								<td class="description"><?php _e( 'Set time duration for limit in seconds', 'rating-form' ); ?></td>
							</tr>
							<?php
								// Include Post Ids
								$qDisplay_inpIds_val = preg_grep("/^post_ids-(.*)/", $qJsonDisplay);
								$qDisplay_inpIds_reset = reset($qDisplay_inpIds_val);
								// Exclude Post Ids
								$qDisplay_expIds_val = preg_grep("/^ex_post_ids-(.*)/", $qJsonDisplay);
								$qDisplay_expIds_reset = reset($qDisplay_expIds_val);
							?>
							<tr>
								<td><strong><?php _e( 'Include Post Ids', 'rating-form' ); ?></strong></td>
								<td><input id="post_ids" type="text" name="post_ids" value="<?php echo isset($_POST['post_ids']) ? $_POST['post_ids'] : (count(preg_grep("/^post_ids-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('post_ids-', '', $qDisplay_inpIds_reset))); ?>" /></td>
								<td class="description"><?php _e( 'Set which post ids are allowed.<br>Multiple posts possible like 1,5,34', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Exclude Post Ids', 'rating-form' ); ?></strong></td>
								<td><input id="ex_post_ids" type="text" name="ex_post_ids" value="<?php echo isset($_POST['ex_post_ids']) ? $_POST['ex_post_ids'] : (count(preg_grep("/^ex_post_ids-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('ex_post_ids-', '', $qDisplay_expIds_reset))); ?>" /></td>
								<td class="description"><?php _e( 'Set which post ids are not allowed.<br>Multiple posts possible like 1,5,34', 'rating-form' ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="clear"></div>
			<div id="display" class="postbox">
				<h3><span><?php _e( "Extra's", 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
					<table class="form-table">
						<tbody>
							<tr>
								<td>
									<?php if (Rating_Form::form_types($id_query['type'], 'type') == "tud") { ?>
									<label><input type="checkbox" name="display[]" value="up_down_total" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("up_down_total", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Up (3) - Down (1) = Difference (2)', 'rating-form' ); ?></label><br>
									<?php } else if (Rating_Form::form_types($id_query['type'], 'type') == "star") { ?>
									<?php if (intval((isset($_POST['type']) ? $_POST['type'] : $id_query['type'])) != 0 && Rating_Form::form_types((isset($_POST['type']) ? $_POST['type'] : $id_query['type']), '', 'int') == '') { // custom forms do not need "empty shapes" option, cus images can be edited ?>
									<label><input type="checkbox" name="display[]" value="empty" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("empty", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Display Empty shapes', 'rating-form' ); ?></label><br>
									<?php } ?>
									<?php } ?>
									<label><input type="checkbox" class="radio" name="display[]" value="edit_rating" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("edit_rating", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Allow to edit rating (through button)', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" class="radio" name="display[]" value="edit_rating_direct" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("edit_rating_direct", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Allow to edit rating (straight)', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="edit_rating_text" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("edit_rating_text", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Show "Edit Rating" message', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="remove_bip_votes" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("remove_bip_votes", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Remove Blocked IP Votes', 'rating-form' ); ?></label><br>
                  <label><input type="checkbox" name="display[]" value="live_top_ratings" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("live_top_ratings", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Update top ratings on vote', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="stylesheet_load_not" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("stylesheet_load_not", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( "Don't load stylesheet", 'rating-form' ); ?></label>
									<p class="description"><?php _e( 'Multiple selection possible', 'rating-form' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div id="display" class="postbox">
				<h3><span><?php _e( "Show / Hide", 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
					<table class="form-table">
						<tbody>
							<tr>
								<td>
									<?php if (Rating_Form::form_types($id_query['type'], 'type') == "tud") { ?>
									<label><input type="checkbox" name="display[]" value="up" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("up", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Display only Up', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="down" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("down", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Display only Down', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="hide_up_total" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("hide_up_total", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Hide total Up ratings', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="hide_down_total" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("hide_down_total", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Hide total Down ratings', 'rating-form' ); ?></label><br>
                  <?php } ?>
									<label><input type="checkbox" name="display[]" value="remove_home" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("remove_home", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Remove Rating Form from Home', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="remove_feed" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("remove_feed", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Remove Rating Form from RSS', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="hide_success_msg" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("hide_success_msg", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Hide "Success" message', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" class="radio" name="display[]" value="only_single" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("only_single", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Show Rating Form only in posts', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" class="radio" name="display[]" value="only_page" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("only_page", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Show Rating Form only in pages', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" class="radio" name="display[]" value="only_category" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("only_category", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Show Rating Form only in categories', 'rating-form' ); ?></label>
									<p class="description"><?php _e( 'Multiple selection possible', 'rating-form' ); ?></p>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div id="display" class="postbox">
				<h3><span><?php _e( "Redirect", 'rating-form' ); ?></span></h3>
				<?php
					// Redirect Url
					$qDisplay_rU_val = preg_grep("/^redirect_url-(.*)/", $qJsonDisplay);
					$qDisplay_rU_reset = reset($qDisplay_rU_val);
					// Redirect Target
					$qDisplay_rT_val = preg_grep("/^redirect_target-(.*)/", $qJsonDisplay);
					$qDisplay_rT_reset = reset($qDisplay_rT_val);
				?>
				<div class="pb_inside">
					<table class="form-table rf-form-top">
						<tbody>
							<tr>
								<td><strong><?php _e( 'Enable', 'rating-form' ); ?></strong></td>
								<td><input type="checkbox" name="display[]" value="redirect_enable" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("redirect_enable", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'URL', 'rating-form' ); ?></strong></td>
								<td><input id="redirect_url" type="text" name="redirect_url" value="<?php echo isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (count(preg_grep("/^redirect_url-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('redirect_url-', '', $qDisplay_rU_reset))); ?>" /></td>
								<td class="description"><?php _e( 'After voting go to URL', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Target', 'rating-form' ); ?></strong></td>
								<td><input id="redirect_target" type="text" name="redirect_target" value="<?php echo isset($_POST['redirect_target']) ? $_POST['redirect_target'] : (count(preg_grep("/^redirect_target-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('redirect_target-', '', $qDisplay_rT_reset))); ?>" /></td>
								<td class="description"><?php _e( 'Choose between:<br>_blank (default)<br>_parent<br>_self<br>_top', 'rating-form' ); ?></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
			<div class="clear"></div>
			<div id="user_stats" class="postbox">
				<h3><span><?php _e( "User Stats", 'rating-form' ); ?></span></h3>
				<?php
					// UserStats Row
					$qDisplay_usR_val = preg_grep("/^ustats_row-(.*)/", $qJsonDisplay);
					$qDisplay_usR_reset = reset($qDisplay_usR_val);
					// UserStats Per Row
					$qDisplay_usPR_val = preg_grep("/^ustats_per_row-(.*)/", $qJsonDisplay);
					$qDisplay_usPR_reset = reset($qDisplay_usPR_val);
					// UserStats Avatar Size
					$qDisplay_usAS_val = preg_grep("/^ustats_av_size-(.*)/", $qJsonDisplay);
					$qDisplay_usAS_reset = reset($qDisplay_usAS_val);
				?>
				<div class="pb_inside">
					<table class="form-table">
						<tbody>
							<tr>
								<td><strong><?php _e( 'Enable', 'rating-form' ); ?></strong></td>
								<td><input type="checkbox" name="display[]" value="ustats_enable" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("ustats_enable", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Row', 'rating-form' ); ?></strong></td>
								<td><input id="ustats_row" type="text" name="ustats_row" value="<?php echo isset($_POST['ustats_row']) ? $_POST['ustats_row'] : (count(preg_grep("/^ustats_row-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('ustats_row-', '', $qDisplay_usR_reset))); ?>" /></td>
								<td class="description"><?php _e( 'Number of rows', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Per row', 'rating-form' ); ?></strong></td>
								<td><input id="ustats_per_row" type="text" name="ustats_per_row" value="<?php echo isset($_POST['ustats_per_row']) ? $_POST['ustats_per_row'] : (count(preg_grep("/^ustats_per_row-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('ustats_per_row-', '', $qDisplay_usPR_reset))); ?>" /></td>
								<td class="description"><?php _e( 'Items per row', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Avatar Size', 'rating-form' ); ?></strong></td>
								<td><input id="ustats_av_size" type="text" name="ustats_av_size" value="<?php echo isset($_POST['ustats_av_size']) ? $_POST['ustats_av_size'] : (count(preg_grep("/^ustats_av_size-(.*)/", $qJsonDisplay)) == 0 ? '' : trim(str_replace('ustats_av_size-', '', $qDisplay_usAS_reset))); ?>" /></td>
								<td class="description">px</td>
							</tr>
							<tr>
								<td>
									<label><input type="checkbox" name="display[]" value="ustats_av_remove" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("ustats_av_remove", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Remove avatar', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="ustats_rating_show" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("ustats_rating_show", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'Display rating', 'rating-form' ); ?></label><br>
									<label><input type="checkbox" name="display[]" value="ustats_login" <?php echo isset($_POST['submit']) && empty($_POST['display']) ? array() : in_array("ustats_login", isset($_POST['display']) ? $_POST['display'] : $qJsonDisplay) ? ' checked="checked"' : ''; ?> /><?php _e( 'User must login', 'rating-form' ); ?></label>
								</td>
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
								<td><input id="txt_ty" type="text" name="txt_ty" value="<?php echo isset($_POST['txt_ty']) ? $_POST['txt_ty'] : $id_query['txt_ty']; ?>"<?php echo isset($_POST['txt_ty']) ? (empty($_POST['txt_ty']) ? ' placeholder="'.  __( 'Thank you :)', 'rating-form' ) .'" style="border: 1px solid red;"' : null) : (empty($id_query['txt_ty']) ? ' placeholder="'.  __( 'Thank you :)', 'rating-form' ) .'" style="border: 1px solid red;"' : null); ?> /></td>
								<td class="description"><?php _e( 'Set success message', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Rated', 'rating-form' ); ?></strong></td>
								<td><input id="txt_rated" type="text" name="txt_rated" value="<?php echo isset($_POST['txt_rated']) ? $_POST['txt_rated'] : $id_query['txt_rated']; ?>"<?php echo isset($_POST['txt_rated']) ? (empty($_POST['txt_rated']) ? ' placeholder="'.  __( 'You already rated', 'rating-form' ) .'" style="border: 1px solid red;"' : null) : (empty($id_query['txt_rated']) ? ' placeholder="'.  __( 'You already rated', 'rating-form' ) .'" style="border: 1px solid red;"' : null); ?> /></td>
								<td class="description"><?php _e( 'Set rated message', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Login', 'rating-form' ); ?></strong></td>
								<td><input id="txt_login" type="text" name="txt_login" value="<?php echo isset($_POST['txt_login']) ? $_POST['txt_login'] : $id_query['txt_login']; ?>"<?php echo isset($_POST['txt_login']) ? (empty($_POST['txt_login']) ? ' placeholder="'.  __( 'Login to rate', 'rating-form' ) .'" style="border: 1px solid red;"' : null) : (empty($id_query['txt_login']) ? ' placeholder="'.  __( 'Login to rate', 'rating-form' ) .'" style="border: 1px solid red;"' : null); ?> /></td>
								<td class="description"><?php _e( 'Set login message', 'rating-form' ); ?></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Limit', 'rating-form' ); ?></strong></td>
								<td><input id="txt_limit" type="text" name="txt_limit" size="80" value="<?php echo isset($_POST['txt_limit']) ? $_POST['txt_limit'] : $id_query['txt_limit']; ?>"<?php echo isset($_POST['txt_limit']) ? (empty($_POST['txt_limit']) ? ' placeholder="'.  __( 'Sorry, rating is limited. Try again in %4$d days %3$d hours %2$d minutes %1$d seconds.', 'rating-form' ) .'" style="border: 1px solid red;"' : null) : (empty($id_query['txt_limit']) ? ' placeholder="'.  __( 'Sorry, rating is limited. Try again in %4$d days %3$d hours %2$d minutes %1$d seconds.', 'rating-form' ) .'" style="border: 1px solid red;"' : null); ?> /></td>
								<td class="description"><?php _e( 'Set limit message', 'rating-form' ); ?><br>
								<?php _e( '<strong>%1$d</strong> = value of seconds', 'rating-form' ); ?><br>
								<?php _e( '<strong>%2$d</strong> = value of minutes', 'rating-form' ); ?><br>
								<?php _e( '<strong>%3$d</strong> = value of hours', 'rating-form' ); ?><br>
								<?php _e( '<strong>%4$d</strong> = value of days', 'rating-form' ); ?><br></td>
							</tr>
							<tr>
								<td><strong><?php _e( 'Edit rating', 'rating-form' ); ?></strong></td>
								<td><input id="txt_edit_rating" type="text" name="txt_edit_rating" value="<?php echo isset($_POST['txt_edit_rating']) ? $_POST['txt_edit_rating'] : $id_query['txt_edit_rating']; ?>"<?php echo isset($_POST['txt_edit_rating']) ? (empty($_POST['txt_edit_rating']) ? ' placeholder="'.  __( 'You find this post %2$s', 'rating-form' ) .'" style="border: 1px solid red;"' : null) : (empty($id_query['txt_edit_rating']) ? ' placeholder="'.  __( 'You find this post %2$s', 'rating-form' ) .'" style="border: 1px solid red;"' : null); ?> /></td>
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

							if (Rating_Form::form_types(intval($id_query['type']), 'type') == "star") {
								$pos_query = $wpdb->get_results( "SELECT position FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " GROUP BY position ORDER BY position ASC", ARRAY_A );

								$i = 0;
								foreach ($pos_query as $pos_row) {
								$i++;
								if ($i > (isset($_POST['max']) ? $_POST['max'] : $id_query['max'])) {
									$hideorshow = ' hide';
								}
								$text_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position = ". $pos_row['position'] ." ORDER BY title_id ASC", ARRAY_A );
								$text_query_notin = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id NOT IN ('".join("','", $titlesID)."') AND position = ". $pos_row['position'] ." ORDER BY title_id DESC", ARRAY_A );
								?>
								<tr class="text_select<?php echo $hideorshow; ?>">
									<td><strong><?php _e( 'Position', 'rating-form' ); ?> <?php echo $pos_row['position']; ?>.</strong></td>
									<td>
									<select name="text[]">
									<?php foreach ($text_query as $text_row) { ?>
										<?php if (in_array($text_row['title_id'], $titlesID)) { ?>
										<option value="<?php echo $text_row['title_id']; ?>"><?php echo $text_row['text']; ?></option>
										<?php } ?>
									<?php } ?>
									<?php foreach ($text_query_notin as $text_row_notin) { ?>
										<option value="<?php echo $text_row_notin['title_id']; ?>"><?php echo $text_row_notin['text']; ?></option>
									<?php } ?>
									</select>
									</td>
								</tr>
								<?php
								}
							} else if (Rating_Form::form_types(intval($id_query['type']), 'type') == "tud") {
								$pos_query_tud = $wpdb->get_results( "SELECT position FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position IN(1,2) GROUP BY position ORDER BY position ASC", ARRAY_A );

								$tud_text = array( __( 'Up', 'rating-form' ),  __( 'Down', 'rating-form' ));
								foreach ($pos_query_tud as $pos_row_tud) {

								$text_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE position = ". $pos_row_tud['position'] ." ORDER BY title_id DESC", ARRAY_A );
								$text_query_notin = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id NOT IN ('".join("','", $titlesID)."') AND position = ". $pos_row_tud['position'] ." ORDER BY title_id ASC", ARRAY_A );
								?>
								<tr class="text_select">
									<td>
										<strong><?php echo $tud_text[$pos_row_tud['position']-1]; ?></strong>
									</td>
									<td>
									<select name="text[]">
									<?php foreach ($text_query as $text_row) { ?>
										<?php if (in_array($text_row['title_id'], $titlesID)) { ?>
										<option value="<?php echo $text_row['title_id']; ?>"><?php echo $text_row['text']; ?></option>
										<?php } ?>
									<?php } ?>
									<?php foreach ($text_query_notin as $text_row_notin) { ?>
										<option value="<?php echo $text_row_notin['title_id']; ?>"><?php echo $text_row_notin['text']; ?></option>
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
					<p class="description"><?php _e( 'Change titles', 'rating-form' ); ?></p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'Post Type', 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
					<p class="alignleft">
					<?php
					global $wpdb;

					$post_types_query = $wpdb->get_results( "SELECT post_type FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type_form_id = '".$id_query['form_id']."'", ARRAY_A );
					$db_post_types = array();

					foreach ($post_types_query as $post_type_query) {
						$db_post_types[] = $post_type_query['post_type'];
					}

					$post_types = get_post_types( '', 'names' );
					foreach ( $post_types as $post_type ) {
							if (in_array($post_type, $db_post_types)) {
					?>
							<label><input id="post_type" type="checkbox" name="del_post_type[]" value="<?php echo $post_type; ?>" checked="checked"> <?php echo $post_type; ?></label><br>
					<?php
							} else {
					?>
							<label><input id="post_type" type="checkbox" name="add_post_type[]" value="<?php echo $post_type; ?>"> <?php echo $post_type; ?></label><br>
					<?php
							}
					}
					?>
						<span class="description"><?php _e( 'Allowed post types', 'rating-form' ); ?></span>
					</p>
				</div>
			</div>
			<div class="postbox">
				<h3><span><?php _e( 'User Role', 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
					<p class="alignleft">
					<?php
					global $wpdb;
					global $wp_roles;
					$roles_array = $wp_roles->get_names();

					$user_roles_query = $wpdb->get_results( "SELECT user_role FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role_form_id = '".$id_query['form_id']."'", ARRAY_A );
					$db_user_roles = array();

					foreach ($user_roles_query as $user_role_query) {
						$db_user_roles[] = $user_role_query['user_role'];
					}


					foreach ( $roles_array as $roles_key=>$roles_value ) {
							if (in_array($roles_key, $db_user_roles)) {
					?>
							<label><input id="role" type="checkbox" name="del_user_role[]" value="<?php echo $roles_key; ?>" checked="checked"> <?php echo $roles_value; ?></label><br>
					<?php
							} else {
					?>
							<label><input id="role" type="checkbox" name="add_user_role[]" value="<?php echo $roles_key; ?>"> <?php echo $roles_value; ?></label><br>
					<?php
							}
					}
					?>
						<span class="description"><?php _e( 'Allowed user roles', 'rating-form' ); ?></span>
					</p>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<input id="save_from" type="submit" name="submit" class="button button-primary button-large" value="<?php _e( 'Save', 'rating-form' ); ?>">
		</form>
<?php

	} else if (isset($_GET['style'])) {
		// CSS Style
		$editstyle = $stylefolder. 'rating_form_'.$id_query['form_id'].'.css';
		// Type Names
		$type_name = '';
		$type_name_one = '';
		$type_name_two = '';
		$type_color = '';
		$type_color_hover = '';
		if (intval($id_query['type']) == 0) {
			$type_name = 'Custom';
		} else if (intval($id_query['type']) == 1) {
			$type_name = 'Star';
			$type_color = '#ffd700';
			$type_color_hover = '#ff7f00';
		} else if (intval($id_query['type']) == 2) {
			$type_name = 'Thumbs Up and Down';
			$type_name_one = 'Thumbs Up';
			$type_name_two = 'Thumbs Down';
		} else if (intval($id_query['type']) == 3) {
			$type_name = 'Smiley';
			$type_color = '#0074a2';
			$type_color_hover = '#224e66';
		} else if (intval($id_query['type']) == 4) {
			$type_name = 'Heart';
			$type_color = '#ff0000';
			$type_color_hover = '#af0000';
		} else if (intval($id_query['type']) == 5) {
			$type_name = 'Plus and Min';
			$type_name_one = 'Plus';
			$type_name_two = 'Min';
		} else if (intval($id_query['type']) == 6) {
			$type_name = 'Circle';
			$type_color = '#0094FF';
			$type_color_hover = '#2ac400';
		}
		if (isset($_POST['submit'])) {
			$msg_success = null;
			$msg_error = null;
			$edit_style = $_POST['edit_style'];

			if (is_writable($stylefolder)) {
				if (isset($edit_style)) {

					if (strlen($msg_error) == 0) {

						//custom stylesheet
						$stylefile = fopen($stylefolder. 'rating_form_'.$id_query['form_id'].'.css', 'w') or die( __( 'Unable to open file for writing! Check file permission.', 'rating-form' ) );
						fwrite($stylefile, (empty($edit_style) ? "\n" : stripslashes($edit_style))) or die( __( 'Unable to write file! Check folder permission.', 'rating-form' ) );
						fclose($stylefile);

						$msg_success .= __( 'Successfully updated!', 'rating-form' );

					}
				} else {

					$msg_error .=  __( 'Error! Style not updated.', 'rating-form' );
				}
			} else {
				echo '<div class="error"><p>'. sprintf( __( 'The following path is not writable: %s', 'rating-form' ), $stylefolder ) .'</div>';
			}

			if ( strlen( $msg_success ) > 0) {
				echo '<div class="updated"><p>' . $msg_success . '<br>';
				echo '<strong>Shortcode:</strong> <input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;'.$id_query['form_id'].'&quot;]"></p></div>';
			}

			if ( strlen( $msg_error ) > 0) {
				echo '<div class="error"><p>' . $msg_error . '</p></div>';
			}
		}
?>
	<form method="post">
	<div class="rf_header_title">
		<strong><?php _e( 'Style', 'rating-form' ); ?></strong>: Rating Form <?php echo $id_query['form_id']; ?>
		<a class="button" href="admin.php?page=<?php echo Rating_Form::PAGE_FORM_RATING_SLUG . '&settings=' . $id_query['form_id']; ?>">Edit Settings</a>
		<input id="save_from" type="submit" name="submit" class="button button-primary" value="Save">
	</div>
		<div id="poststuff">
			<div class="postbox">
				<h3><span><?php _e( 'Example', 'rating-form' ); ?></span></h3>
				<div class="pb_inside">
				<?php
					// Css Style
					echo '<style type="text/css" id="admin_rf_add_new_css"></style>';
					echo do_shortcode('[rating_form id="' . $id_query['form_id'] . '" stats="false"]');
				?>
					<?php if (count($titlesText) == 0) { ?>
					<p class="description"><i><?php _e( 'No titles assigned to this form.', 'rating-form' ); ?></i></p>
					<?php } ?>
					<p class="created_on"><i><?php printf( __( 'Created on %s', 'rating-form' ), date('Y/m/d @ H:i', strtotime($id_query['date'])) ); ?></i></p>
				</div>
			</div>
			<div class="clear"></div>
			<?php
			$lastmodified = null;
			if (file_exists($editstyle)) {
				$lastmodified = '<span class="description alignright">'. sprintf( __( 'Last edited on %s', 'rating-form' ), date('Y/m/d @ H:i', filemtime($editstyle)) ) .'</span>';
			}
			?>
			<div class="postbox rf_edit_style">
				<h3><span><?php _e( 'Style', 'rating-form' ); ?></span><?php echo $lastmodified; ?></h3>
				<?php if (Rating_Form::form_types(intval($id_query['type']), 'type') == "star") { ?>
				<div class="pb_inside">
				<?php if (intval($id_query['type']) == 0) { // Custom Style block ?>
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
			<?php } else if (Rating_Form::form_types(intval($id_query['type']), 'type') == "tud") { ?>
				<div class="pb_inside">
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
								<strong><?php echo $type_name_one; ?> <?php _e( 'Color', 'rating-form' ); ?></strong>
								<br>
								<input id="tu_font_color" type="text" name="tu_font_color" value="#59d600" />
								<br>
								<span class="description"><?php _e( 'Set color of icon', 'rating-form' ); ?></span>
							</td>
							<td>
								<strong><?php echo $type_name_two; ?> <?php _e( 'Color', 'rating-form' ); ?></strong>
								<br>
								<input id="td_font_color" type="text" name="td_font_color" value="#d60000" />
								<br>
								<span class="description"><?php _e( 'Set color of icon', 'rating-form' ); ?></span>
							</td>
						</tr>
						<tr>
							<td>
								<strong><?php echo $type_name_one; ?> <?php _e( 'Hover Color', 'rating-form' ); ?></strong>
								<br>
								<input id="tu_font_hover_color" type="text" name="tu_font_hover_color" value="#0e8b00" />
								<br>
								<span class="description"><?php _e( 'Set color of hover', 'rating-form' ); ?></span>
							</td>
							<td>
								<strong><?php echo $type_name_two; ?> <?php _e( 'Hover Color', 'rating-form' ); ?></strong>
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
								<input id="tu_font_color_text" type="text" name="tu_font_color_text" value="#0e8b00" />
								<br>
								<span class="description"><?php _e( 'Set color of text', 'rating-form' ); ?></span>
							</td>
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
								<input id="tu_background_def_text" type="text" name="tu_background_def_text" value="#bdffaf" />
								<br>
								<span class="description"><?php _e( 'Set background of text', 'rating-form' ); ?></span>
							</td>
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
			<?php } ?>
				<div class="clear"></div>
				<div id="admin_rf_new_style"><h3 class="description"><?php _e( 'Search for class name and replace CSS values', 'rating-form' ); ?></h3><div class="admin_rf_style_inner"></div></div>
				<div class="clear"></div>
				<div class="pb_inside">
					<textarea class="style_code" cols="80" rows="25" name="edit_style" id="edit_style"><?php echo file_get_contents($editstyle); ?></textarea>
				</div>
			</div>
		</div>
		<div class="clear"></div>
		<input id="save_from" type="submit" name="submit" class="button button-primary button-large" value="<?php _e( 'Save', 'rating-form' ); ?>">
	</form>
<?php
	}

	// go to list if Rating Form doesn't exist
	} else {
		if (isset($_GET['duplicate'])) { //duplicate rating
			if (is_writable($stylefolder)) {
				global $wpdb;

				$form_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = '".$_GET['duplicate']."'", ARRAY_A );
				$rowCount = $wpdb->num_rows;

				if ($rowCount > 0) {
					$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_ADD_FORM, array(
								'form_name' => $form_query['form_name'],
								'active' => $form_query['active'],
								'type' => $form_query['type'],
								'max' => $form_query['max'],
								'restrict_ip' => $form_query['restrict_ip'],
								'user_logged_in' => $form_query['user_logged_in'],
								'ajax_load' => $form_query['ajax_load'],
								'rich_snippet' => $form_query['rich_snippet'],
								'spinner' => $form_query['spinner'],
								'round' => $form_query['round'],
								'rtl' => $form_query['rtl'],
								'limitation' => $form_query['limitation'],
								'time' => $form_query['time'],
								'display' => $form_query['display'],
								'txt_ty' => $form_query['txt_ty'],
								'txt_rated' => $form_query['txt_rated'],
								'txt_login' => $form_query['txt_login'],
								'txt_limit' => $form_query['txt_limit'],
								'txt_edit_rating' => $form_query['txt_edit_rating']
						));
					$new_rating_id .= $wpdb->insert_id;
					//duplicate assigned titles to the new added form
					$title_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_form_id = '".$form_query['form_id']."'", ARRAY_A );
					foreach ($title_query as $title_row) {
						$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_FORM_TITLES, array(
									'title_form_id' => $new_rating_id,
									'title_id' => $title_row['title_id'],
									'position' => $title_row['position']
							));
					}
					//duplicate post types to the new added form
					$post_type_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type_form_id = '".$form_query['form_id']."'", ARRAY_A );
					foreach ($post_type_query as $post_type_row) {
						$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_POST_TYPES, array(
									'post_type_form_id' => $new_rating_id,
									'post_type' => $post_type_row['post_type']
							));
					}
					//duplicate user roles to the new added form
					$user_role_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role_form_id = '".$form_query['form_id']."'", ARRAY_A );
					foreach ($user_role_query as $user_role_row) {
						$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_USER_ROLES, array(
									'user_role_form_id' => $new_rating_id,
									'user_role' => $user_role_row['user_role']
							));
					}
					//duplicate custom css
					$current_stylefile = $stylefolder. 'rating_form_'.$_GET['duplicate'].'.css';
					$new_stylefile = str_replace("rating_form_".$_GET['duplicate'], "rating_form_".$new_rating_id, file_get_contents($current_stylefile));
					$stylefile = fopen($stylefolder. 'rating_form_'.$new_rating_id.'.css', 'w') or die("Unable to open file! Check file permission.");
					fwrite($stylefile, $new_stylefile);
					fclose($stylefile);
					//duplicate custom images
					if ($form_query['type'] == 0) {
						wp_mkdir_p( $upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id );
						copy($upload_dir['basedir'].'/rating-form/icons/' . $form_query['form_id'] . DIRECTORY_SEPARATOR . 'custom-empty.png',
								$upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-empty.png');
						copy($upload_dir['basedir'].'/rating-form/icons/' . $form_query['form_id'] . DIRECTORY_SEPARATOR . 'custom-full.png',
								$upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-full.png');
						copy($upload_dir['basedir'].'/rating-form/icons/' . $form_query['form_id'] . DIRECTORY_SEPARATOR . 'custom-half.png',
								$upload_dir['basedir'].'/rating-form/icons/' . $new_rating_id . DIRECTORY_SEPARATOR . 'custom-half.png');
					}
					echo '<div class="updated"><p>'. __( 'Successfully duplicated!', 'rating-form' ) .'<br>';
					echo '<strong>Shortcode:</strong> <input onclick="this.select()" type="text" readonly="" value="[rating_form id=&quot;'.$new_rating_id.'&quot;]"></p></div>';
				} else {
					echo '<div class="error"><p>'. sprintf( __( 'Rating Form ID <strong>%d</strong> not found!', 'rating-form' ), $_GET['duplicate'] ) .'</p></div>';
				}
			} else {
				echo '<div class="error"><p>'. sprintf( __( 'The following path is not writable: %s', 'rating-form' ), $stylefolder ) .'</p></div>';
			}
		} else if (isset($_GET['delete'])) { //delete rating
			if (is_writable($stylefolder)) {
				global $wpdb;

				$del_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = '".$_GET['delete']."'", ARRAY_A );
				$rowCount = $wpdb->num_rows;

				if ($rowCount == 0) {
					echo '<div class="error"><p>'. sprintf( __( 'Rating Form ID <strong>%d</strong> not found!', 'rating-form' ), $_GET['delete'] ) .'</p></div>';
				} else {
					//delete form
					$wpdb->query(
						$wpdb->prepare(
								"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . " WHERE form_id = %d",
								$_GET['delete']
							)
					);

					//delete assigned titles to the deleted form (this will not delete created titles)
					$title_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_form_id = '".$_GET['delete']."'", ARRAY_A );
					$title_num_rows = $wpdb->num_rows;

					if ($title_num_rows > 0) {
						$wpdb->query(
							$wpdb->prepare(
									"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . " WHERE title_form_id = %d",
									$_GET['delete']
								)
						);
					}

					//delete post type to the deleted form
					$post_type_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type_form_id = '".$_GET['delete']."'", ARRAY_A );
					$post_type_num_rows = $wpdb->num_rows;

					if ($post_type_num_rows > 0) {
						$wpdb->query(
							$wpdb->prepare(
									"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . " WHERE post_type_form_id = %d",
									$_GET['delete']
								)
						);
					}

					//delete user role to the deleted form
					$user_role_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role_form_id = '".$_GET['delete']."'", ARRAY_A );
					$user_role_num_rows = $wpdb->num_rows;

					if ($user_role_num_rows > 0) {
						$wpdb->query(
							$wpdb->prepare(
									"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . " WHERE user_role_form_id = %d",
									$_GET['delete']
								)
						);
					}

					// delete custom css
					$current_stylefile = $stylefolder. 'rating_form_'.$_GET['delete'].'.css';
					unlink($current_stylefile) or die(sprintf( __("Unable to delete this file: %s<br>Check folder permission or delete manually.", 'rating-form' ), $current_stylefile ));

					if ($del_query[0]['type'] == 0) {
						// delete custom folder with files
						$form_dir = $upload_dir['basedir'].'/rating-form/icons/' . $del_query[0]['form_id'];
						array_map('unlink', glob("$form_dir/*.*"));
						rmdir($form_dir) or die(sprintf( __("Unable to delete this folder: %s<br>Check folder permission or delete manually.", 'rating-form' ), $form_dir ));
					}

					$wpdb->show_errors();
					echo '<div class="updated"><p>'. __( 'Succesfully deleted!', 'rating-form' ) .'</p></div>';
				}
			} else {
				echo '<div class="error"><p>'. sprintf( __( 'The following path is not writable: %s', 'rating-form' ), $stylefolder ) .'</div>';
			}
		}
	$rating_form_table = new Rating_Form_Table();
	$rating_form_table->prepare_items();
?>
		<form id="rating_form_table" method="post">
			<?php $rating_form_table->display(); ?>
		</form>
<?php
	}
?>
	</div>
<?php
}
?>
