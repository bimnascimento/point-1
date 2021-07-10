<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Rating_Form_Titles_Table extends WP_List_Table {

	//Prepare the items for the table to process
	public function prepare_items()
    {
		global $wpdb;

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		$query = 'SELECT * FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES;
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
            'title_id' => __( 'Title ID', 'rating-form' ),
            'position' => __( 'Position', 'rating-form' ),
            'text' => __( 'Title', 'rating-form' )
        );

        return $columns;
    }

	//Define what data to show on each column of the table
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
            case 'title_id':
            case 'text':
            case 'position':
				$item['position'] = '<strong>'. $item['position'] .'</strong> <span class="row-actions"><span class="edit"><a href="' . admin_url( ) . 'admin.php?page='.Rating_Form::PAGE_TITLES_RATING_SLUG.'&edit='. $item['title_id'] .'">'. __( 'Edit', 'rating-form' ) .'</a></span></span>';
				return $item[ $column_name ];

            default:
                return print_r( $item, true ) ;
        }
    }

	//Define which columns are hidden
    public function get_hidden_columns()
    {
        return array('title_id');
    }

	//Define the sortable columns
    public function get_sortable_columns()
    {
        return array(
			'title_id' => array('title_id', false),
			'text' => array('text', false),
			'position' => array('position', true)
			);
    }

	//Allows you to sort the data by the variables set in the $_GET
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'position';
        $order = 'asc';

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

function rating_form_titles() {
?>
	<div class="wrap rf_wrap">
		<?php Rating_Form::admin_menus(); ?>
<?php
	if (isset($_GET['edit'])) {

		global $wpdb;

		$edit_query = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$_GET['edit']."'", ARRAY_A );
		$rowCount = $wpdb->num_rows;

		if ($rowCount > 0) {

			if (isset($_POST['edit'])) {
				$msg_success = null;
				$msg_error = null;
				$text = $_POST['title'];
				$position = $_POST['position'];

				if (isset($text) && isset($position)) {

					if (strlen($msg_error) == 0) {

						$wpdb->update( $wpdb->prefix.Rating_Form::TBL_RATING_TITLES, array(
								'text' => $text,
								'position' => $position
						), array('title_id' => $edit_query['title_id'] ) );

						$msg_success .= __( 'Successfully updated!', 'rating-form' );
					}

				} else {
					$msg_error .= '<p>'. __( 'Error! Title not updated.', 'rating-form' ) .'</p>';
				}

				if ( strlen( $msg_success ) > 0) {
					echo '<div class="updated"><p>' . $msg_success . '</p></div>';
				}

				if ( strlen( $msg_error ) > 0) {
					echo '<div class="error">' . $msg_error . '</div>';
				}
			}

		} else {
			echo '<div class="error"><p>'. sprintf( __( 'Title ID %d not found!', 'rating-form' ), $_GET['edit'] ) .'</p></div>';
		}
?>
		<div class="alignleft">
			<h1><?php _e( 'Edit Title', 'rating-form' ); ?> <strong><?php echo isset($_POST['title']) ? $_POST['title'] : $edit_query['text']; ?></strong></h1>
			<form id="rating_form_add_edit" action="<?php echo admin_url( ) . 'admin.php?page='.Rating_Form::PAGE_TITLES_RATING_SLUG . '&edit=' . $edit_query['title_id']; ?>" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Text', 'rating-form' ); ?></th>
							<td>
								<input id="title" type="text" name="title" value="<?php echo isset($_POST['title']) ? $_POST['title'] : $edit_query['text']; ?>">
								<p class="description"><?php _e( 'Type a new title, example: Wow, So bad, Cool, Super!', 'rating-form' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Position', 'rating-form' ); ?></th>
							<td>
								<?php for ($i = 1; $i <= 10; $i++) { ?>
									<?php if ($i == (isset($_POST['position']) ? $_POST['position'] : $edit_query['position'])) { ?>
									<label><input class="position" type="radio" name="position" value="<?php echo $i; ?>" checked="checked" /> <?php echo $i; ?></label><br>
									<?php } else { ?>
									<label><input class="position" type="radio" name="position" value="<?php echo $i; ?>" /> <?php echo $i; ?></label><br>
									<?php } ?>
								<?php } ?>
								<p class="description"><?php _e( 'Set position of title<br>Thumbs Up = position 1<br>Thumbs Down = position 2', 'rating-form' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<input id="edit-title-submit" class="button button-primary" type="submit" name="edit" value="<?php _e( 'Update', 'rating-form' ); ?>" />
			</form>
		</div>
<?php
	} else {

		/*if (isset($_GET['delete'])) {
			global $wpdb;
			//$wpdb->show_errors();
			$wpdb->query(
				$wpdb->prepare(
						"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = %d",
						$_GET['delete']
					)
			);

			$query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " WHERE title_id = '".$_GET['delete']."'", ARRAY_A );
			$rowCount = $wpdb->num_rows;
			?>
			<div class="alignleft">
			<?php
			if ($rowCount == 0) {
				echo '<div class="updated"><p>'. __( 'Successfully deleted!', 'rating-form' ) .'</p></div>';
			} else {
				echo '<div class="error"><p>'. sprintf( __( 'Title ID %d not deleted! Still exist!', 'rating-form' ), $_GET['delete'] ) .'</p></div>';
			}
			?>
			</div>
			<?php
		}*/

		if (isset($_POST['add'])) {
			$msg_success = null;
			$msg_error = null;
			$text = $_POST['title'];
			$position = $_POST['position'];

			if (isset($text) && isset($position)) {

				if (empty($text)) {
					$msg_error .= '<p>'. __( 'Field <strong>Text</strong> is empty', 'rating-form' ) .'</p>';
				}

				if (strlen($msg_error) == 0) {

					global $wpdb;

					$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_TITLES, array(
								'text' => $text,
								'position' => intval($position)
						));
					
					$msg_success .= sprintf( __( 'Successfully added title: <strong>%s</strong>', 'rating-form' ), $text );
				}

			} else {
				$msg_error .= '<p>'. __( 'Error! Title not added. Forgot to fill a field?', 'rating-form' ) .'</p>';
			}

			if ( strlen( $msg_success ) > 0) {
				echo '<div class="updated"><p>' . $msg_success . '</p></div>';
			}

			if ( strlen( $msg_error ) > 0) {
				echo '<div class="error">' . $msg_error . '</div>';
			}
		}
?>
		<div class="alignleft">
			<h1><?php _e( 'Add Title', 'rating-form' ); ?></h1>
			<form id="rating_form_add_edit" action="<?php echo admin_url( ) . 'admin.php?page='.Rating_Form::PAGE_TITLES_RATING_SLUG; ?>" method="post">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row"><?php _e( 'Text', 'rating-form' ); ?></th>
							<td>
								<input id="title" type="text" name="title" value="">
								<p class="description"><?php _e( 'Type a new title, example: Wow, So bad, Cool, Super!', 'rating-form' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e( 'Position', 'rating-form' ); ?></th>
							<td>
								<?php for ($i = 1; $i <= 10; $i++) { ?>
									<?php if ($i == 1) { ?>
									<label><input class="position" type="radio" name="position" value="<?php echo $i; ?>" checked="checked" /> <?php echo $i; ?></label><br>
									<?php } else { ?>
									<label><input class="position" type="radio" name="position" value="<?php echo $i; ?>" /> <?php echo $i; ?></label><br>
									<?php } ?>
								<?php } ?>
								<p class="description"><?php _e( 'Set position of title<br>Thumbs Up = position 1<br>Thumbs Down = position 2', 'rating-form' ); ?></p>
							</td>
						</tr>
					</tbody>
				</table>
				<input id="add-title-submit" class="button button-primary" type="submit" name="add" value="<?php _e( 'Add', 'rating-form' ); ?>" />
			</form>
		</div>
<?php
	}

	$rating_form_titles_table = new Rating_Form_Titles_Table();
	$rating_form_titles_table->prepare_items();
?>
	<div class="alignright">
		<form id="rating_form_titles_table" method="post">
			<?php $rating_form_titles_table->display(); ?>
		</form>
	</div>
	</div>
<?php
}
?>
