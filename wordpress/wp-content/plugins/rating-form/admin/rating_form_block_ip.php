<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Rating_Form_Block_IP_Table extends WP_List_Table {
	
	//Prepare the items for the table to process
	public function prepare_items()
    {
		global $wpdb;
		
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

		$query = 'SELECT * FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP;
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
        $columns = array(
            'ip' => 'IP',
            'date' => __( 'Date', 'rating-form' ),
            'reason' => __( 'Reason', 'rating-form' )
        );

        return $columns;
    }
	
	//Define what data to show on each column of the table
    public function column_default( $item, $column_name )
    {
        switch( $column_name ) {
			case 'ip':
				$admin_url = admin_url( ) . 'admin.php';
				$item['ip'] = '<span class="block_span">' . $item['ip'] . '</span><input type="text" value="' . $item['ip'] . '" /><div class="row-actions"><input class="block_ip_edit_submit" type="submit" name="edit" value="' . _( 'Save' ) . '" /> <span class="edit"><a class="block_ip_edit" href="#">'. __( 'Edit', 'rating-form' ) .'</a></span> | <span class="delete"><a onclick="return confirm(&quot;'. __( 'Are you sure you want to delete this?', 'rating-form' ) .'&quot;)" href="'. $admin_url .'?page='.Rating_Form::PAGE_BLOCK_IP_SLUG.'&delete='. $item['ip'] .'">X</a></span></div>';
			case 'date':
				$item['date'] = date('Y/m/d @ H:i', strtotime($item['date']));
			case 'reason':
				$item['reason'] = '<span class="block_span">' . (empty($item['reason']) ? __( 'None', 'rating-form' ) . '</span><input type="text" value="" placeholder="' . __( 'None', 'rating-form' ) . '" />' : $item['reason'] . '</span><input type="text" value="' . $item['reason'] . '" />');
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
function rating_form_block_ip() {
	$rating_form_block_ip_table = new Rating_Form_Block_IP_Table();
	$rating_form_block_ip_table->prepare_items();
?>
	<div class="wrap rf_wrap">
	<?php
		Rating_Form::admin_menus( 'Block List' );
		
		global $wpdb;
		if (isset($_GET['ip'])) { //add rating if not exist
			$add_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '". $_GET['ip'] ."';", ARRAY_A );
			$add_num_rows = $wpdb->num_rows;
			
			if ($add_num_rows == 0) {
				$wpdb->insert( $wpdb->prefix.Rating_Form::TBL_RATING_BLOCK_IP, array(
							'ip' => trim($_GET['ip'])
					));
			}
		}
		
		if (isset($_GET['delete'])) { //delete ip
				
			$delete_query = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '".$_GET['delete']."'", ARRAY_A );
			$delete_num_rows = $wpdb->num_rows;
			if ($delete_num_rows > 0) {
				//delete ratings
				$wpdb->query( 
					$wpdb->prepare( 
							"DELETE FROM " . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . " WHERE ip = '%s'",
							$_GET['delete']
						)
				);
				
				echo '<script>window.location.href = "' . admin_url( ) . 'admin.php?page=' . Rating_Form::PAGE_BLOCK_IP_SLUG . '&message=2"</script>';
			}
		}
		
		if (isset($_GET['message'])) {
			if ($_GET['message'] == "1") {
				echo '<div class="updated"><p>'. __( 'Successfully blocked!', 'rating-form' ) .'</p></div>';
			} else if ($_GET['message'] == "2") {
				echo '<div class="updated"><p>'. __( 'Successfully deleted!', 'rating-form' ) .'</p></div>';
			}
		}
	?>
		<form method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" id="block_ip_new">
			<input type="hidden" name="action" value="rating_form_add_ip" />
			<input type="text" name="ip" id="block_ip_new_list" placeholder="<?php echo _('IP'); ?>" />
			<input type="text" name="reason" placeholder="<?php echo _('Reason'); ?>" />
			<input class="button" type="submit" name="add" value="<?php echo _( 'Add' ); ?>" />
		</form>
		<form method="post" id="block_ip_edit">
			<?php $rating_form_block_ip_table->display(); ?>
		</form>
	</div>
<?php
}
?>