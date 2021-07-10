<?php
/**
  * Adds Top Rating Results Widget
  */
class Rating_Form_Top_Ratings_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'rf_top_ratings_widget', 'description' => 'Display Top Rating Results.' );
		parent::__construct(
			'rating_form_top_ratings_widget', // Class Name
			'Rating Form: Top Rating Results', // Widget Title
			$widget_ops // Args
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		echo wrap_rating_form_top_results($instance, true, $args);
	}

	/**
	 * Ouputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		global $wpdb;
		
		$limit = empty( $instance['limit'] ) ? 5 : intval( $instance['limit'] );
		$title = empty( $instance['title'] ) ? "Top 5 Ratings" : $instance['title'];
		$form_id = empty( $instance['form_id'] ) ? 0 : $instance['form_id'];
		$content_active = empty( $instance['content_active'] ) ? 0 : 1;
		$image_active = empty( $instance['image_active'] ) ? 0 : 1;
		$author_active = empty( $instance['author_active'] ) ? 0 : 1;
		$content_length = empty( $instance['content_length'] ) ? 10 : intval( $instance['content_length'] );
		$image_size = empty( $instance['image_size'] ) ? 48 : intval( $instance['image_size'] );
		$time = empty( $instance['time'] ) ? 0 : intval( $instance['time'] );
		$time_field = empty( $instance['time_field'] ) ? '' : $instance['time_field'];
		$type = empty( $instance['type'] ) ? '' : $instance['type'];
		$post_type = empty( $instance['post_type'] ) ? array('post','page') : $instance['post_type'];
		
		$forms_query = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . ' ORDER BY date DESC', ARRAY_A);
		$forms_query_num_rows = $wpdb->num_rows;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'rating-form' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Limit', 'rating-form' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>">
		</p>
		<p>
			<strong><?php _e( 'Show / Hide', 'rating-form' ); ?></strong><br />
			<label for="<?php echo $this->get_field_id( 'content_active' ); ?>"><?php _e( 'Content', 'rating-form' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'content_active' ); ?>" name="<?php echo $this->get_field_name( 'content_active' ); ?>" type="checkbox" value="1" <?php checked( $content_active, 1 ); ?>>
			<?php if ($type == 'comments') { ?>
			<label for="<?php echo $this->get_field_id( 'author_active' ); ?>"><?php _e( 'Author', 'rating-form' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'author_active' ); ?>" name="<?php echo $this->get_field_name( 'author_active' ); ?>" type="checkbox" value="1" <?php checked( $author_active, 1 ); ?>>
			<?php } ?>
			<?php if ($type == 'post_pages') { ?>
			<label for="<?php echo $this->get_field_id( 'image_active' ); ?>"><?php _e( 'Image', 'rating-form' ); ?></label>
			<input id="<?php echo $this->get_field_id( 'image_active' ); ?>" name="<?php echo $this->get_field_name( 'image_active' ); ?>" type="checkbox" value="1" <?php checked( $image_active, 1 ); ?>>
			<?php } ?>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'type' ); ?>"><?php _e( 'Type', 'rating-form' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" class="widefat" style="width:100%;">
				<option <?php selected( $type, 'post_pages' ); ?> value="post_pages"><?php _e( 'Posts / Pages', 'rating-form' ); ?></option>
				<option <?php selected( $type, 'comments' ); ?> value="comments"><?php _e( 'Comments', 'rating-form' ); ?></option>
				<option <?php selected( $type, 'taxonomies' ); ?> value="taxonomies"><?php _e( 'Taxonomies', 'rating-form' ); ?></option>
				<option <?php selected( $type, 'users' ); ?> value="users"><?php _e( 'Users', 'rating-form' ); ?></option>
			</select>
			</p>
		<?php if ($content_active == 1) { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'content_length' ); ?>"><?php _e( 'Length', 'rating-form' ); ?></label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'content_length' ); ?>" name="<?php echo $this->get_field_name( 'content_length' ); ?>" type="text" value="<?php echo esc_attr( $content_length ); ?>">
		</p>
		<?php } ?>
		<?php if ($image_active == 1 && $type == 'post_pages') { ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size', 'rating-form' ); ?>(px)</label> 
			<input class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>" type="text" value="<?php echo esc_attr( $image_size ); ?>">
		</p>
		<?php } ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'form_id' ); ?>"><?php _e( 'Style', 'rating-form' ); ?></label>
			<?php if ($forms_query_num_rows > 0) { ?>
			<select id="<?php echo $this->get_field_id( 'form_id' ); ?>" name="<?php echo $this->get_field_name( 'form_id' ); ?>" class="widefat" style="width:100%;">
				<?php
				foreach($forms_query as $form_row) {
				?>
				<option <?php selected( $form_id, $form_row['form_id'] ); ?> value="<?php echo $form_row['form_id']; ?>">Rating Form <?php echo $form_row['form_id']; ?></option>
				<?php
				}
				?>      
			</select>
			<?php
			} else {
				_e( 'No Rating Form was found. Create one.', 'rating-form' );
			}
			?>
		</p>
		<p>
			
			<table style="width:100%">
				<tr>
					<td><label for="<?php echo $this->get_field_id( 'time' ); ?>"><?php _e( 'Latest Posts', 'rating-form' ); ?></label></td>
				</tr>
				<tr>
					<td><input class="widefat" id="<?php echo $this->get_field_id( 'time' ); ?>" name="<?php echo $this->get_field_name( 'time' ); ?>" type="text" value="<?php echo esc_attr( $time ); ?>"></td>
					<td>
					<select class="widefat" id="<?php echo $this->get_field_id( 'time_field' ); ?>" name="<?php echo $this->get_field_name( 'time_field' ); ?>">
						<option <?php selected( $time_field, 'sec' ); ?> value="sec"><?php _e( 'Seconds', 'rating-form' ); ?></option>
						<option <?php selected( $time_field, 'hour' ); ?> value="hour"><?php _e( 'Hours', 'rating-form' ); ?></option>
						<option <?php selected( $time_field, 'day' ); ?> value="day"><?php _e( 'Days', 'rating-form' ); ?></option>
						<option <?php selected( $time_field, 'week' ); ?> value="week"><?php _e( 'Weeks', 'rating-form' ); ?></option>
						<option <?php selected( $time_field, 'month' ); ?> value="month"><?php _e( 'Months', 'rating-form' ); ?></option>
					</select>
					</td>
				</tr>
			</table>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post Type', 'rating-form' ); ?></label>
			<select id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>[]" size="5" multiple="multiple" style="width:100%;">
				<?php
				$post_types = get_post_types( '', 'names' );
				foreach ( $post_types as $post_type_row ) {
				?>
					<option <?php echo in_array($post_type_row, $post_type) ? 'selected="selected"' : ''; ?> value="<?php echo $post_type_row; ?>"><?php echo $post_type_row; ?></option>
				<?php
				}
				?>      
			</select>
		</p>
		<?php 
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['limit'] = intval( $new_instance['limit'] );
		$instance['form_id'] = intval( $new_instance['form_id'] );
		$instance['content_active'] = intval( $new_instance['content_active'] );
		$instance['image_active'] = intval( $new_instance['image_active'] );
		$instance['author_active'] = intval( $new_instance['author_active'] );
		$instance['content_length'] = intval( $new_instance['content_length'] );
		$instance['image_size'] = intval( $new_instance['image_size'] );
		$instance['time'] = intval( $new_instance['time'] );
		$instance['time_field'] = strval( $new_instance['time_field'] );
		$instance['type'] = strval( $new_instance['type'] );
		$instance['post_type'] = $new_instance['post_type'];
		
		return $instance;
	}
}

/**
 * Register Widgets
 */
function register_rating_form_widgets() {
    register_widget( 'Rating_Form_Top_Ratings_Widget' );
}
add_action( 'widgets_init', 'register_rating_form_widgets' );
?>