<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Rating_Form_Results_Table extends WP_List_Table {
	
	//Prepare the items for the table to process
	public function prepare_items()
    {
		global $wpdb;
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		$get_post_id = (isset($_GET['post_id']) ? (intval( $_GET['post_id'] ) > 0 ? ' WHERE post_id = ' . intval( $_GET['post_id'] ) : '') : ' GROUP BY post_id, term_id');
		$get_term_id = (isset($_GET['term_id']) ? (intval( $_GET['term_id'] ) > 0 ? ' WHERE term_id = ' . intval( $_GET['term_id'] ) : '') : ' GROUP BY post_id, term_id');
		$get_val = (!isset($_GET['post_id']) ? $get_term_id : $get_post_id);
		$query = 'SELECT * FROM (SELECT * FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . ' ORDER BY date DESC) as resultLatest ' . $get_val;
        $data = $wpdb->get_results($query, ARRAY_A);
        usort($data, array($this, 'sort_data'));

        $perPage = 25;
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
		$column_ip = __( 'Total Votes', 'rating-form' );
		$column_rated = __( 'Latest Rating', 'rating-form' );
		if (isset($_GET['post_id']) || isset($_GET['term_id'])) {
			$column_ip = __( 'IP', 'rating-form' );
			$column_rated = __( 'Rating', 'rating-form' );
		}
		
        $columns = array(
            'rate_id' => __( 'ID', 'rating-form' ),
            'post_id' => __( 'Post (ID)', 'rating-form' ),
            'ip' => $column_ip,
            'rated' => $column_rated,
            'date' => __( 'Date', 'rating-form' )
        );

        return $columns;
    }
	
	//Define what data to show on each column of the table
    public function column_default( $item, $column_name )
    {	
		global $wpdb;
		// Admin WP url
		$admin_url = admin_url( ) . 'admin.php';
		// Check if post id exist in WP
		$wp_post_id_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->posts . " WHERE ID = '".$item['post_id']."'", ARRAY_A );
		$wp_post_num_rows = $wpdb->num_rows;
		// Check if IP is blocked
		$block_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '". $item['ip'] ."'", ARRAY_A );
		$block_query_num_rows = $wpdb->num_rows;
		// Total votes
		$total_votes = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE post_id = '".$item['post_id']."'" );
		switch( $column_name ) {
			case 'rate_id':
			case 'post_id':
				$term = Rating_Form::get_term_by_id( $item['term_id'] );
				$item_post_term_id = '';
				if ($item['post_id'] == 0) {
					$item_post_term_id .= '<a href="'.get_term_link( $term ).'" target="_blank">'. $term->name .'</a> ('. $term->taxonomy .')';
					$item_post_term_id .= '<div class="row-actions">';
					$item_post_term_id .= (!isset($_GET['term_id']) ? '<span class="see_all"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&term_id='. $term->term_id .'">'. __( 'See All', 'rating-form' ) .'</a></span>' : '');
					$item_post_term_id .= (isset($_GET['term_id']) ? '<span class="edit"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&edit='. $item['rate_id'] .'">'. __( 'Edit', 'rating-form' ) .'</a></span> | <span class="delete"><a onclick="return confirm(&quot;'. __( 'Are you sure you want to delete this?', 'rating-form' ) .'&quot;)" href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&delete='. $item['rate_id'] .'">X</a></span>' : '');
					$item_post_term_id .= '</div>';
				} else {
					$item_post_term_id .= ($wp_post_num_rows == 0 ? __( 'Not Found', 'rating-form' ) . ' (id: ' . $item['post_id'] . ')' : '<a href="' . (isset($_GET['post_id']) ? get_permalink( $wp_post_id_query['ID'] ) . '" target="_blank"' : $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&post_id='. $item['post_id']. '"' ) . '>' . (empty($wp_post_id_query['post_title']) ? (strlen($wp_post_id_query['post_content']) > 25 ? substr($wp_post_id_query['post_content'], 0, 25) . "..." : $wp_post_id_query['post_content']) : $wp_post_id_query['post_title']) . '</a> ('.$wp_post_id_query['post_type'].')');
					$item_post_term_id .= '<div class="row-actions">';
					$item_post_term_id .= (!isset($_GET['post_id']) ? '<span class="see_all"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&post_id='. $item['post_id'] .'">'. __( 'See All', 'rating-form' ) .'</a></span>' : '');
					$item_post_term_id .= (!isset($_GET['post_id']) ? ' | <span class="settings"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&settings='. $item['post_id'] .'">'. __( 'Settings', 'rating-form' ) .'</a></span>' : '' );
					$item_post_term_id .= (isset($_GET['post_id']) ? '<span class="edit"><a href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&edit='. $item['rate_id'] .'">'. __( 'Edit', 'rating-form' ) .'</a></span> | <span class="delete"><a onclick="return confirm(&quot;'. __( 'Are you sure you want to delete this?', 'rating-form' ) .'&quot;)" href="'. $admin_url .'?page='.Rating_Form::PAGE_RESULT_RATING_SLUG.'&delete='. $item['rate_id'] .'">X</a></span>' : '');
					$item_post_term_id .= '</div>';
				}
				$item['post_id'] = $item_post_term_id;
			case 'ip':
				if (isset($_GET['post_id']) || isset($_GET['term_id'])) {
					$item['ip'] = (($block_query_num_rows > 0) ? '<s title="'. __( 'IP Blocked', 'rating-form' ) .'">' . $item['ip'] . '</s>' : '<a class="ip_block" href="'. $admin_url .'?page='.Rating_Form::PAGE_BLOCK_IP_SLUG.'&ip='. trim($item['ip']) . '" title="'. __( 'Block IP', 'rating-form' ) .'">' . $item['ip'] . ' <span class="add">(X)</span></a>');
				} else {
					$item['ip'] = $total_votes;
				}
			case 'rated':
				switch( $item['rated'] ) {
					case '1u':
						$item['rated'] = '+1';
						break;
					case '1d':
						$item['rated'] = '-1';
						break;
					default:
						$item['rated'] = $item['rated'];
				}
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
        return array('rate_id');
    }
	
	//Define the sortable columns
    public function get_sortable_columns()
    {
        return array(
			'rate_id' => array('rate_id', false),
			'post_id' => array('post_id', false),
			'ip' => array('ip', false),
			'rated' => array('rated', false),
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
function rating_form_results() {
	$rating_form_results_table = new Rating_Form_Results_Table();
	$rating_form_results_table->prepare_items();
?>
	<div class="wrap rf_wrap">
		<?php Rating_Form::admin_menus( __( 'Latest Ratings', 'rating-form' ) ); ?>
	<?php
	if (isset($_GET['edit'])) {
		global $wpdb;
		
		$edit_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rate_id = '".$_GET['edit']."'", ARRAY_A );
		$rowCount = $wpdb->num_rows;
		
		if ($rowCount > 0) {
		
			if (isset($_POST['edit'])) {
				$msg_success = null;
				$msg_error = null;
				$rated = $_POST['rated'];
				
				if (isset($rated)) {

					if (strlen($msg_error) == 0) {
						
						$wpdb->update( $wpdb->prefix.Rating_Form::TBL_RATING_RATED, array(
								'rated' => $rated
						), array('rate_id' => $edit_query['rate_id'] ) );
							
						$msg_success .= __( 'Successfully updated!', 'rating-form' );
					}
					
				} else {
					$msg_error .= '<p>'. __( 'Error! Rating not updated', 'rating-form' ) .'</p>';
				}
				
				if ( strlen( $msg_success ) > 0) {
					echo '<div class="updated"><p>' . $msg_success . '</p></div>';
				}
				
				if ( strlen( $msg_error ) > 0) {
					echo '<div class="error">' . $msg_error . '</div>';
				}
			}
		//Get user info by ID
		$user = get_userdata( $edit_query['user'] );
	?>
		<h2><strong><?php _e( 'Edit', 'rating-form' ); ?></strong> - <?php echo __( 'Rating', 'rating-form' ) . ' ' . $_GET['edit']; ?></h2>
			<p class="description"><?php printf( __( 'Rated on %1$s by %2$s.', 'rating-form' ), date('Y/m/d @ H:i', strtotime($edit_query['date'])), (($user == true) ? $user->user_login : $edit_query['ip']) ); ?></p>
			<form id="rating_form_add_edit" method="post">
				<table class="form-table">
					<tbody>
						<?php
						if ($user == true) {
						?>
						<tr>
							<th scope="row"><?php _e( 'User', 'rating-form' ); ?></th>
							<td>
								<?php echo $user->user_login; ?>
							</td>
						</tr>
						<?php } ?>
						<tr>
							<th scope="row"><?php _e( 'IP', 'rating-form' ); ?></th>
							<td>
								<?php echo $edit_query['ip']; ?>
							</td>
						</tr>
					<?php if (preg_match('/^\d+$/', (isset($_POST['rated']) ? $_POST['rated'] : $edit_query['rated']))) { ?>
						<tr>
							<th scope="row"><?php _e( 'Rating', 'rating-form' ); ?></th>
							<td>
								<select id="rated" name="rated">
								<?php for ($max = 1; $max <= 10; $max++) { ?>
									<?php if ($max == (isset($_POST['rated']) ? $_POST['rated'] : $edit_query['rated'])) { ?>
									<option value="<?php echo $max; ?>" selected="selected"><?php echo $max; ?></option>
									<?php } else { ?>
									<option value="<?php echo $max; ?>"><?php echo $max; ?></option>
									<?php } ?>
								<?php } ?>
								</select>
							</td>
						</tr>
					<?php } else { ?>
						<tr>
							<th scope="row"><?php _e( 'Rating', 'rating-form' ); ?></th>
							<td>
								<select id="rated" name="rated">
									<?php if ((isset($_POST['rated']) ? $_POST['rated'] : $edit_query['rated']) == '1u') { ?>
									<option value="1u">+1 (<?php _e( 'Up', 'rating-form' ); ?>)</option>
									<option value="1d">-1 (<?php _e( 'Down', 'rating-form' ); ?>)</option>
									<?php } else { ?>
									<option value="1d">-1 (<?php _e( 'Down', 'rating-form' ); ?>)</option>
									<option value="1u">+1 (<?php _e( 'Up', 'rating-form' ); ?>)</option>
									<?php } ?>
								</select>
							</td>
						</tr>
					<?php } ?>
					</tbody>
				</table>
				<input id="edit-rating-submit" class="button button-primary" type="submit" name="edit" value="<?php _e( 'Update', 'rating-form' ); ?>" />
			</form>
	<?php
		} else {
			echo '<div class="error"><p>'. sprintf( __( 'Rating %d not found!', 'rating-form' ), $_GET['edit'] ) .'</p></div>';
		}
		
	} else if (isset($_GET['settings'])) {
		
		if (isset($_POST['save'])) {
			
			$rf_average_result = $_POST['average_result'];
			$rf_votes_up = $_POST['votes_up'];
			$rf_votes_down = $_POST['votes_down'];
			$rf_total_votes = $_POST['total_votes'];
			update_post_meta($_GET['settings'], 'rf_average_result', $rf_average_result);
			update_post_meta($_GET['settings'], 'rf_votes_up', $rf_votes_up);
			update_post_meta($_GET['settings'], 'rf_votes_down', $rf_votes_down);
			update_post_meta($_GET['settings'], 'rf_total_votes', $rf_total_votes);
			echo '<div class="updated"><p>' . __( 'Successfully saved!', 'rating-form' ) . '</p></div>';
		}
		
	?>
		<h2><strong><?php _e( 'Settings', 'rating-form' ); ?></strong> - <a href="<?php echo get_permalink( $_GET['settings'] ); ?>" target="_blank"><?php echo get_the_title( $_GET['settings'] ); ?></a> - <?php echo __( 'Post ID', 'rating-form' ) . ' ' . $_GET['settings']; ?></h2>
			<form id="rating_form_add_edit" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Average Result', 'rating-form' ); ?></th>
							<td>
								<input type="text" name="average_result" value="<?php echo get_post_meta($_GET['settings'], 'rf_average_result', true); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Total Votes', 'rating-form' ); ?></th>
							<td>
								<input type="text" name="total_votes" value="<?php echo get_post_meta($_GET['settings'], 'rf_total_votes', true); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Up Votes', 'rating-form' ); ?></th>
							<td>
								<input type="text" name="votes_up" value="<?php echo get_post_meta($_GET['settings'], 'rf_votes_up', true); ?>" />
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Down Votes', 'rating-form' ); ?></th>
							<td>
								<input type="text" name="votes_down" value="<?php echo get_post_meta($_GET['settings'], 'rf_votes_down', true); ?>" />
							</td>
						</tr>
					</tbody>
				</table>
				<input class="button button-primary" type="submit" name="save" value="<?php _e( 'Save', 'rating-form' ); ?>" />
			</form>
	<?php
	} else {
		if (isset($_GET['delete'])) { //delete rating
			global $wpdb;
				
			$rated_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rate_id = '".$_GET['delete']."'", ARRAY_A );
			$rated_num_rows = $wpdb->num_rows;
			if ($rated_num_rows > 0) {
				//delete ratings
				$wpdb->query( 
					$wpdb->prepare( 
							"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " WHERE rate_id = %d",
							$_GET['delete'] 
						)
				);
				
				$wpdb->show_errors();
				echo '<div class="updated"><p>'. __( 'Successfully deleted!', 'rating-form' ) .'</p></div>';
			}
		}
	?>
		<form id="rating_form_results_table" method="post">
			<?php $rating_form_results_table->display(); ?>
		</form>
	<?php
	}
	?>
	</div>
<?php
}
?>