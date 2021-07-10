<?php

/* --------------------------------------------------------------------
   Creamos Tipo de Post "PopupPress"
-------------------------------------------------------------------- */
add_action( 'init', 'create_post_type_popuppress_PPS' );

function create_post_type_popuppress_PPS() {
	$labels = array(
		'name' => __('PopupPress', 'PPS'),
		'singular_name' => __('PopupPress', 'PPS'),
		'add_new' => __('New Popup', 'PPS'),
		'add_new_item' => __('Add New Popup', 'PPS'),
		'edit_item' => __( 'Edit Popup', 'PPS' ),
		'new_item' => __( 'New Popup', 'PPS'),
		'view_item' => __( 'View Popup', 'PPS' ),
		'search_items' => __( 'Search Popup', 'PPS' ),
		'not_found' => __( 'No Popups found', 'PPS' ),
		'not_found_in_trash' => __( 'No Popups found in Trash', 'PPS' ),
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		//'publicly_queryable' => true,
		'show_ui' => true,
		'exclude_from_search' => true,
		'show_in_nav_menus' => false,

		'show_in_menu' => true,
		'rewrite' => false,
		'has_archive' => false,
		//'hierarchical' => false,
		'menu_position' => 20,//29
		'menu_icon' => PPS_URL.'/css/images/icon_plugin.png',

		'supports' => array('title','editor'),
	);
	register_post_type('popuppress',$args);
}


/* --------------------------------------------------------------------
  Filtro de Mensajes para el Tipo de Post "PopupPress"
-------------------------------------------------------------------- */
add_filter( 'post_updated_messages', 'messages_popuppress_PPS' );

function messages_popuppress_PPS( $messages ) {
	global $post, $post_ID;

	$messages['popuppress'] = array(
		0 => '', // Unused. Messages start at index 1.
		1 => sprintf( __('Popup updated. <a href="%s" class="pps-button-popup-'.$post_ID.'">View Popup</a>', 'PPS'), '#'),

		2 => __('Custom field updated.', 'PPS'),
		3 => __('Custom field deleted.', 'PPS'),
		4 => __('Popup updated.', 'PPS'),
		/* translators: %s: date and time of the revision */
		5 => isset($_GET['revision']) ? sprintf( __('Popup restored to revision from %s', 'PPS'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
		6 => sprintf( __('Popup published. <a href="%s">View Popup</a>', 'PPS'), esc_url( get_permalink($post_ID) )),
		7 => __('Popup saved.', 'PPS'),
		8 => sprintf( __('Popup submitted. <a target="_blank" href="%s">Preview Popup</a>', 'PPS'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		9 => sprintf( __('Popup scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview Popup</a>', 'PPS'),
		  // translators: Publish box date format, see http://php.net/date
		  date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
		10 => sprintf( __('Popup draft updated. <a target="_blank" href="%s">Preview Popup</a>', 'PPS'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
		);
	return $messages;
}

/* --------------------------------------------------------------------
   Columnas para el Tipo de Post "PopupPress"
-------------------------------------------------------------------- */
add_filter("manage_edit-popuppress_columns", "popuppress_columns_PPS");

function popuppress_columns_PPS($columns){
	$columns = array(
		"cb" => "<input type=\"checkbox\" />",
		"title" => "Title",
		"shortcode" => "Shortcode",
		"views" => "Total Views",
		"preview-popup" => "Preview",
		"date" => 'Date',
	);
	return $columns;
}

/* --------------------------------------------------------------------
   Contenido de las Columnas para Popups
-------------------------------------------------------------------- */
add_action('manage_popuppress_posts_custom_column','popuppress_custom_columns_PPS', 10 , 2);

function popuppress_custom_columns_PPS($column, $popup_id){
	$pps_data = get_post_custom($popup_id);
	$shortcode = 'Please publish popup';
	$views = 'Please publish popup';
	$preview = 'Please publish popup';
	$views_count = (int) get_post_meta($popup_id, 'pps-views', true);

	if(get_post_status($popup_id) == 'publish'){
		switch ($column) {
			case 'shortcode':
				echo '<p style="margin: 2px 0 0; font-size:13px;">[popuppress id="'.$popup_id.'"]</p>';
				break;
			case 'views':
				echo '<p style="margin: 2px 0 0 6px;"><span style="font-size:13px;">'.$views_count.'</span><span style="color: #666;"> views</span><br/><a class="restore-views" href="?popup_id='.$popup_id.'">Restore</a></p>';
				break;
			case 'preview-popup':
				echo do_shortcode('[popuppress id="'.$popup_id.'"]');
				break;
		}
	} else {
		switch ($column) {
			case 'shortcode':
			case 'views':
			case 'preview-popup':
				echo 'Please publish popup';
				break;
		}
	}
}

/* --------------------------------------------------------------------
   Hacemos Ordenable la Columna "Views" para Popups
-------------------------------------------------------------------- */

add_filter( 'manage_edit-popuppress_sortable_columns', 'popuppress_sortable_columns_PPS' );
add_action( 'pre_get_posts', 'popuppress_sortable_query_PPS' );

function popuppress_sortable_columns_PPS( $columns ) {
	$columns['views'] = 'views';
	return $columns;
}

function popuppress_sortable_query_PPS( $query ) {
	if( !is_admin() || !isset($_GET['post_type']) || $_GET['post_type']!='popuppress' )
		return;

	$orderby = $query->get('orderby');
	if( 'views' == $orderby ) {
		$query->set('meta_key','pps-views');
		$query->set('orderby','meta_value_num');
	}
}

/* --------------------------------------------------------------------
   Código Corto que Muestra el Popup
-------------------------------------------------------------------- */
add_shortcode('popuppress', 'shortcode_popuppress');
add_filter('widget_text', 'do_shortcode', 11);
function shortcode_popuppress( $atts = '', $content = null) {

	global $wpdb, $post;
	extract( shortcode_atts( array(
		'id' => 0,
	), $atts ) );

	if( ! get_post_status($id) ){
		return;
	}

	$popuppress = get_post($id);

	if( $popuppress->post_type != 'popuppress' ){
		return;
	}

	if( disable_popup_PPS($id) ){
		return;
	}

	$popup_id = $id;

	//Agregamos los popups al final de la página.
	if( ! isset( $GLOBALS['pps_ids'] ) ){
		$GLOBALS['pps_ids'] = array();
	}
	$GLOBALS['pps_ids'][] = $popup_id;

	$button_popup = get_button_popup_PPS( $popup_id );

	return $button_popup;
}

/* ----------------------------------------------------------------------
   Función que agrega los popups al final de la página
---------------------------------------------------------------------- */
add_action( 'wp_footer', 'add_popups_wp_footer_PPS', 9 );
function add_popups_wp_footer_PPS(){
	$pps_ids = isset($GLOBALS['pps_ids']) ? $GLOBALS['pps_ids'] : array();
	$popups = '';
	if( count($pps_ids) > 0 ){
		//Primero recorremos para comprobar todos los popups que hay.
		foreach ( $pps_ids as $key => $popup_id ) {
			$popups .= get_popup_PPS($popup_id);
			$popups .= get_script_popup_PPS($popup_id);
		}
		//Si ahora hay más popups quiere decir que se hay popups dentro de popups, limpiamos todo y volvemos a llamarlos.
		if( count($pps_ids) < count($GLOBALS['pps_ids']) ){
			$pps_ids = isset($GLOBALS['pps_ids']) ? $GLOBALS['pps_ids'] : array();
			$popups = '';
			foreach ($pps_ids as $key => $popup_id) {
				$popups .= get_popup_PPS($popup_id);
				$popups .= get_script_popup_PPS($popup_id);
			}
		}
	}
	unset($GLOBALS['pps_ids']);
	echo $popups;
}

/* --------------------------------------------------------------------
   Inserta cuerpo y script del popup en la página de edición
-------------------------------------------------------------------- */
add_action('admin_footer', 'add_popup_admin_footer_PPS', 9 );
function add_popup_admin_footer_PPS($popup_id = 0) {
	//Page edit popup
	global $post;
	$popup_id = isset($_GET['post']) ? $_GET['post']: 0;
	if( get_post_type($post) == 'popuppress' && $popup_id != 0 && get_post_status($popup_id) == 'publish'){
		$main_popup = get_popup_PPS($popup_id);
		$scripts_popup = get_script_popup_PPS($popup_id);
		echo $main_popup.$scripts_popup;
	}

	//Agregamos el cuerpo de todos los popups
	$pps_ids = isset($GLOBALS['pps_ids']) ? $GLOBALS['pps_ids'] : array();
	$popups = '';
	if( count($pps_ids) > 0 ){
		foreach ($pps_ids as $key => $popup_id) {
			$popups .= get_popup_PPS($popup_id);
			$popups .= get_script_popup_PPS($popup_id);
		}
	}
	echo $popups;
}

/* --------------------------------------------------------------------
   Inserta Automáticamente un Popup al Sitio
-------------------------------------------------------------------- */
add_action( 'wp_footer', 'auto_insert_popup_PPS', 9 );
function auto_insert_popup_PPS(){
	global $wp_query;
	$args = array(
		'post_type' 	=> 'popuppress',
		'posts_per_page' => -1, /* Get all popups */
		'meta_query' => array(
			array(
			   'key' => 'pps_open_in',
			   'value' => 'pages',
			   'compare' => '!=',
			)
		)
	);
	$query_pps = new WP_Query( $args );
	if( $query_pps->have_posts() ) :
		while($query_pps->have_posts()) : $query_pps->the_post();
			$popup_id = get_the_ID();
			$main_popup = get_popup_PPS($popup_id);
			$button_popup = get_button_popup_PPS($popup_id);
			$scripts_popup = get_script_popup_PPS($popup_id);
			$pps_data = get_post_custom($popup_id);
			$open_in = $pps_data['pps_open_in'][0];
			$open_in_url = isset($pps_data['pps_open_in_url'][0]) ? $pps_data['pps_open_in_url'][0] : "";
			$url_actual = "http://" . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
			$show_popup = false;

			switch($open_in){
				case 'home': //$open_in == 'home' && $_SERVER["REQUEST_URI"] == '/' || is_front_page()
					if( (is_home() && is_front_page())|| (is_front_page() && is_page(get_option( 'page_on_front'))) || $_SERVER["REQUEST_URI"] == '/' )
						$show_popup = true;
					break;

				case 'all-site':
					$show_popup = true;
					//Excluimos páginas por id
					if(!empty($pps_data['pps_exclude_pages'][0])){
						$pages_ids = explode(',', $pps_data['pps_exclude_pages'][0]);
						if ( is_page($pages_ids) || is_single($pages_ids) )
							$show_popup = false;
					}
					break;

				case 'urls':
					$open_in_url = str_replace("https", "http", $open_in_url);
					$urls = explode(',', $open_in_url);
					foreach($urls as $url){
						$url = trim($url);
						if(strlen($url) > 1){
							//Si existe el asterisco(*) al final de la url
							if( strpos($url, '*', strlen($url)-1) !== false  ){
								//El popup se ejecutará en todas las páginas hijas de la puesta en el campo URL's
								$url = str_replace('*','',$url);
								if(strpos($url_actual, $url) !== false)
									$show_popup = true;

							} else {
								if($url_actual == $url || $url_actual == $url.'/')
									$show_popup = true;
							}
						}
					}
					break;
			}

			if($show_popup && !disable_popup_PPS($popup_id)){
				echo $main_popup.$scripts_popup;
			}

		endwhile;
	endif;
	wp_reset_postdata();
}

/* --------------------------------------------------------------------
   Desactiva el Popup
-------------------------------------------------------------------- */

function disable_popup_PPS($popup_id){
	$disable = false;
	$pps_options = get_option('pps_options');
	$pps_data = get_post_custom($popup_id);

	//Desactiva el Popup en Dispositivos Moviles
	if( ! class_exists('Mobile_Detect') ){
		include_once('Mobile_Detect.php');
	}
	$m_detect = new Mobile_Detect;
	$deviceType = ($m_detect->isMobile() ? ($m_detect->isTablet() ? 'tablet' : 'phone') : 'computer');

	$disable_mobile = get_post_meta_PPS( $popup_id, 'pps_disable_mobile', $pps_options['prevent_mobile'] );
	if ( $disable_mobile == 'true' ) {
		if ( $deviceType == 'phone' || $deviceType == 'tablet' ){
			$disable = true;
		}
	}
	//Desactiva el Popup en Escritorios
	$disable_desktop = get_post_meta_PPS( $popup_id, 'pps_disable_desktop', $pps_options['prevent_desktop'] );
	if ( $disable_desktop == 'true' ) {
		if ( $deviceType == 'computer' ){
			$disable = true;
		}
	}

	//Desactiva el Popup para usuarios registrados
	$disable_logged_user = get_post_meta_PPS( $popup_id, 'pps_disable_logged_user', 'false' );
	if( $disable_logged_user == 'true' && is_user_logged_in() ){
		$disable = true;
	}

	return $disable;
}

/* --------------------------------------------------------------------
  Retorna una opción o su valor por defecto
-------------------------------------------------------------------- */
function get_post_meta_PPS( $post_id = 0, $meta_key = '', $default = '', $single = true ) {
	if ( ! is_numeric( $post_id ) ) {
    return false;
  }

  $post_id = absint( $post_id );
  if ( ! $post_id ) {
    return false;
  }

  $post_meta = get_metadata( 'post', $post_id );

	if( ! empty($post_meta[$meta_key]) ){
		if( $single ){
			return $post_meta[$meta_key][0];
		}
		return $post_meta[$meta_key];
	}

	return $default;
}

/* --------------------------------------------------------------------
   Generamos el Cuerpo del Popup
-------------------------------------------------------------------- */
function get_popup_PPS($popup_id = 0){
	$popuppress = get_post($popup_id);

	if( empty($popup_id) || $popuppress->post_type != 'popuppress' ){
		return '';
	}

	// Cuerpo del Popup
	$pps_data = get_post_custom($popup_id);

	$width = get_post_meta_PPS( $popup_id, 'pps_width', '640' );
	$width_units = get_post_meta_PPS( $popup_id, 'pps_width_units', 'px' );

	$height = get_post_meta_PPS( $popup_id, 'pps_height', '' );
	$height_units = get_post_meta_PPS( $popup_id, 'pps_height_units', 'px' );
	$pps_auto_height = get_post_meta_PPS( $popup_id, 'pps_auto_height', 'true' );

	if( $pps_auto_height == 'true') {
		$height = 'auto';
	}

	$size_popup = 'pps-w-'.$width.'-'.$width_units.' pps-h-'.$height.'-'.$height_units;

	$disclaimer_activate = get_post_meta_PPS( $popup_id, 'pps_disclaimer_activate', 'false' );

	$border_popup = get_post_meta_PPS( $popup_id, 'pps_border_popup', '0' );
	$has_border = (int) $border_popup > 0 ? ' pps-has-border' : ' pps-no-border';

	$icon_close_btn = get_post_meta_PPS( $popup_id, 'pps_icon_close_btn', 'pps-icon-close' );
	$show_close_btn = get_post_meta_PPS( $popup_id, 'pps_show_close', 'true' );

	$data = array(
		'width' => $width,
		'width_units' => $width_units,
		'height' => $height,
		'height_units' => $height_units,
		'auto_height' => $pps_auto_height,
		'position' => get_post_meta_PPS( $popup_id, 'pps_position_type', 'absolute' ),
		'position_x' => get_post_meta_PPS( $popup_id, 'pps_position_x', 'auto' ),
		'position_y' => get_post_meta_PPS( $popup_id, 'pps_position_y', 'auto' ),
		'close_mouseleave' => get_post_meta_PPS( $popup_id, 'pps_close_mouselave', 'false' ),
	);
	$data_json = json_encode($data);

	$popup = '';
	$popup .= "<div id='popuppress-$popup_id' class='pps-popup $has_border $size_popup' data-popuppress='$data_json'>";
		$popup .= "<div class='pps-wrap'>";
			if( $show_close_btn == 'true' && $disclaimer_activate == 'false' ){
				$popup .= "<div class='pps-close'><a href='#' class='pps-close-link-$popup_id pps-close-link' id='pps-close-link-$popup_id' title='Close'><i class='pps-icon $icon_close_btn'></i></a></div>";
			}

			if( get_post_meta_PPS( $popup_id, 'pps_show_title' ) == 'true' ){
				$title = get_the_title( $popup_id );
				$popup .= "<div class='pps-header'><h3 class='pps-title'>$title</h3></div>";
			}

			$content = get_content_popup_PPS( $popup_id );
			$popup .= "<div class='pps-content'>$content</div>";
		$popup .= "</div><!--.pps-wrap-->";
	$popup .= "</div><!--.pps-popup-->";

	return $popup;
}

/* --------------------------------------------------------------------
   Función que Genera el Botón del Popup
-------------------------------------------------------------------- */
function get_button_popup_PPS($popup_id = 0){
	$pps_options = get_option('pps_options');
	$pps_data = get_post_custom($popup_id);
	$button_popup = '';
	$button_type = get_post_meta_PPS( $popup_id, 'pps_button_type', 'button' );
	$class_run = get_post_meta_PPS( $popup_id, 'pps_button_class_run', '' );
	$class_style = get_post_meta_PPS( $popup_id, 'pps_button_class', '' );
	$button_img = get_post_meta_PPS( $popup_id, 'pps_button_image', '' );
	$button_img_hover = get_post_meta_PPS( $popup_id, 'pps_button_image_hover', $button_img );

	switch( $button_type ){
		case 'button':
			$button_popup = '<a href="#" class="pps-btn pps-button-popup-'.$popup_id.' '.$class_style.' '.$class_run.'"  title="'.$pps_data['pps_button_title'][0].'">'.$pps_data['pps_button_text'][0].'</a>';
			break;

		case 'plain-text':
			$button_popup = '<a href="#" class="pps-button-popup-'.$popup_id.' '.$class_style.' '.$class_run.'"  title="'.$pps_data['pps_button_title'][0].'">'.$pps_data['pps_button_text'][0].'</a>';
			break;

		case 'image':
			if( empty($button_img) ){
				$button_popup = '<p>Please add an image</p>';
			} else {
				$button_popup = '<a href="#" class="pps-btn-img pps-button-popup-'.$popup_id.' '.$class_style.' '.$class_run.'" title="'.$pps_data['pps_button_title'][0].'"><img src="'.$button_img.'" alt="'.get_the_title($popup_id).'" width="'.$pps_data['pps_img_width_button'][0].'" onmouseover="this.src=\''.$button_img_hover.'\';" onmouseout="this.src=\''.$button_img.'\';" /></a>';
			}
			break;

		case 'thumbnails':
			$button_popup = get_thumbnails_popup_PPS($popup_id);
			break;

		case 'no-button':
			$button_popup = '';
			break;
	}
	return $button_popup;
}

/* --------------------------------------------------------------------
   Función que Genera las miniaturas para el Slider Popup
-------------------------------------------------------------------- */
function get_thumbnails_popup_PPS($popup_id = 0){
	$pps_data = get_post_custom($popup_id);
	$class_img_thumb = get_post_meta_PPS( $popup_id, 'pps_class_thumbnail', 'pps-thumb-slider' );
	$num_columns = get_post_meta_PPS( $popup_id, 'pps_n_columns', 'auto' );

	$pps_rg_img = get_post_meta( $popup_id, 'pps_rg_img', true );
	$num_slides = 0;
	foreach ( $pps_rg_img as $key => $img_array ) {
		$img_file = isset($img_array['image']) ? $img_array['image'] : '';
		if( preg_match('/(^.*\.jpg|jpeg|png|gif|ico*)/i', $img_file) ){
		  $num_slides++;
		}
	}
	if( $num_slides == 0 ){
		return 'Add images';
	}
	$margin = 5;
	$param_nth_child = '1n';
	if(!is_numeric($num_columns)){
		$max_width = (100 - $margin*($num_slides - 1))/$num_slides;
		$num_columns = $num_slides;
	} else {
		$max_width = (100 - $margin*($num_columns - 1))/$num_columns;

		if($num_slides >= $num_columns){
			$param_nth_child = '1n + ' . ($num_slides - $num_columns + 1);
			if($num_slides % $num_columns > 0){
				$param_nth_child = '1n + ' . ($num_slides + 1 - ($num_slides % $num_columns) );
			}
		}
	}
	$css_thumbnails = '<style>
	#pps-wrap-thumbs-popup-'.$popup_id.' li {
		max-width: '.$max_width.'%;
		margin: 0px;
		margin-right: '.$margin.'%;
		margin-bottom: 25px;
	}
	#pps-wrap-thumbs-popup-'.$popup_id.' li.last {
		margin-right: 0;
	}
	#pps-wrap-thumbs-popup-'.$popup_id.' li:nth-child('.$param_nth_child.') {
		margin-bottom: 0px;
	}
	#pps-wrap-thumbs-popup-'.$popup_id.' li img {
		max-width: 100%;
	}</style>';

	$html_thumbnails = '<div id="pps-wrap-thumbs-popup-'.$popup_id.'" class="pps-wrap-thumbs-popup">';
	if( ! empty( $pps_rg_img ) ) {
		foreach ( $pps_rg_img as $key => $img_array ) {
			$last = (($key+1)%$num_columns == 0) ? 'last' : '';
			$img_file = isset($img_array['image']) ? $img_array['image'] : '';
			if( preg_match('/(^.*\.jpg|jpeg|png|gif|ico*)/i', $img_file) ){
				$attachment_id = get_attachment_id_by_url_PPS($img_file);
				if( is_numeric( $attachment_id ) ){
					$thumbnail = wp_get_attachment_image_src( $attachment_id, 'pps_thumbnails' );
					$img_file = !empty($thumbnail) ? $thumbnail[0] : $img_file;
				}
          $html_thumbnails .= '<li class="'.$last.'"><a href="#" class="pps-btn pps-button-popup-'.$popup_id.' pps-button-thumb pps-button-thumb-'.$key.'"><img src="'.$img_file.'" class="'.$class_img_thumb.'" /></a></li>';
			}
		}
	}
	$html_thumbnails .= '</div>';

	return $css_thumbnails.$html_thumbnails;
}

/* --------------------------------------------------------------------
   Generamos los Scripts y Estilos del Popup
-------------------------------------------------------------------- */

function get_script_popup_PPS($popup_id = 0){
	$modules_popups = '';
	$pps_options = get_option('pps_options');
	$pps_data = get_post_custom($popup_id);

	//Open On
	if( 'yes' == get_post_meta_PPS( $popup_id, 'pps_run_on_hover', 'false' ) ) {
		$run_method = 'mouseover';
	}
	$run_method = get_post_meta_PPS( $popup_id, 'pps_open_hook', 'click' );

	$leave_page_max_times = (int) get_post_meta_PPS( $popup_id, 'pps_leave_page_max_times', 1 );

	$auto_close = get_post_meta_PPS( $popup_id, 'pps_auto_close', 'false' );
	$open_delay = get_post_meta_PPS( $popup_id, 'pps_open_delay', 0 );
	$close_delay = get_post_meta_PPS( $popup_id, 'pps_delay_close', 12000 );

	if( $run_method == 'auto_open' ){
		$close_delay = (int) $close_delay + (int) $open_delay;
	}

	$use_cookie = get_post_meta_PPS( $popup_id, 'pps_first_time', 'false' );
	$cookie_popup = 'pps_cookie_'.$popup_id;
	$cookie_expire = get_post_meta_PPS( $popup_id, 'pps_cookie_expire', 'current_session' );
	$cookie_days = get_post_meta_PPS( $popup_id, 'pps_cookie_days', 1 );

	$position_x = get_post_meta_PPS( $popup_id, 'pps_position_x', 'auto' );
	$position_x = ( !is_numeric( $position_x ) ) ? '"auto"' : $position_x;
	$position_y = get_post_meta_PPS( $popup_id, 'pps_position_y', 'auto' );
	$position_y = ( !is_numeric( $position_y ) ) ? '"auto"' : $position_y;

	$popup_easing = get_post_meta_PPS( $popup_id, 'pps_popup_easing', '' );

	$follow_x = get_post_meta_PPS( $popup_id, 'pps_follow_x', 'true' );
	$follow_y = get_post_meta_PPS( $popup_id, 'pps_follow_y', 'true' );
	$follow = "[{$follow_x},{$follow_y}]";

	$opacity = get_post_meta_PPS( $popup_id, 'pps_opacity', 0.75 );

	//Slider Options
	$slider_auto = get_post_meta_PPS( $popup_id, 'pps_slider_auto', $pps_options["slider_auto"] );
	$slider_timeout = get_post_meta_PPS( $popup_id, 'pps_slider_timeout', $pps_options["slider_timeout"] );
	$slider_animation_speed = get_post_meta_PPS( $popup_id, 'pps_slider_animation_speed', 600 );
	$slider_pagination = get_post_meta_PPS( $popup_id, 'pps_slider_pagination', $pps_options["slider_pagination"] );
	$slider_arrows = get_post_meta_PPS( $popup_id, 'pps_slider_arrows', $pps_options["slider_arrows"] );
	$slider_pause = get_post_meta_PPS( $popup_id, 'pps_slider_pause', $pps_options["slider_pause"] );
	$slider_start_at = get_post_meta_PPS( $popup_id, 'pps_slider_start_at', 1 ) - 1;

	//Disclaimer Popup
	$disclaimer_activate = get_post_meta_PPS( $popup_id, 'pps_disclaimer_activate', 'false' );
	$disclaimer_cookie = 'pps_disclaimer_'.$popup_id;

	$agree_redirect = get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_redirect', 'same_page' );
	$agree_redirect_to = get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_redirect_to', '#' );
	$agree_redirect_to = ($agree_redirect_to == 'http://') ? '#' : $agree_redirect_to;

	$disagree_restriction = get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_restriction', 'close_page' );
	$disagree_redirect_to = get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_redirect_to', '#' );
	$disagree_redirect_to = ($disagree_redirect_to == 'http://') ? '#' : $disagree_redirect_to;

	$close_overlay = ($disclaimer_activate == 'true' && !is_admin()) ? 'false' : get_post_meta_PPS( $popup_id, 'pps_close_overlay', $pps_options['close_overlay'] );

	$close_esc_key = get_post_meta_PPS( $popup_id, 'pps_close_esc_key', true );

	$img_overlay = get_post_meta_PPS( $popup_id, 'pps_img_overlay', '' );

	$embed_height = get_post_meta_PPS( $popup_id, 'pps_embed_height', $pps_options['embed_height']);
	$iframe_height = get_post_meta_PPS( $popup_id, 'pps_iframe_height', $pps_options['embed_height']);
	$pdf_height = get_post_meta_PPS( $popup_id, 'pps_pdf_height', $pps_options['embed_height']);
	$popup_speed = get_post_meta_PPS( $popup_id, 'pps_speed', 300 );
	$popup_modal_color = get_post_meta_PPS( $popup_id, 'pps_bg_overlay', '#000000' );
	$popup_position_type = get_post_meta_PPS( $popup_id, 'pps_position_type', 'absolute' );
	$popup_transition = get_post_meta_PPS( $popup_id, 'pps_popup_transition', 'fadeIn' );
	$popup_zindex = ! empty( $pps_options['popup_zindex'] ) ? $pps_options['popup_zindex'] : '99999';


	$class_run = get_post_meta_PPS( $popup_id, 'pps_button_class_run' );
	$class_run = ! empty( $class_run ) ? ', .'.$class_run : '';

	$popupslider = '';

	if( isset($GLOBALS['pps-slider-'.$popup_id]) && $GLOBALS['pps-slider-'.$popup_id] == true ){
		if( get_post_meta_PPS( $popup_id, 'pps_button_type') == 'thumbnails' ){
			$popupslider = '
				if(typeof jQuery.fn.popupslider == "function") {
					lazyLoadSliderImagesPopupPress('.$popup_id.');
					jQuery("#pps-slider-'.$popup_id.'").popupslider({
						slideshow: '.$slider_auto.',
						slideshowSpeed: '.$slider_timeout.',
						animationSpeed: '.$slider_animation_speed.',
						controlNav: '.$slider_pagination.',
						directionNav: '.$slider_arrows.',
						pauseOnHover: '.$slider_pause.',
						namespace: "pps-",
						startAt: startAtNum,
						start: function(slider){
							onStartSliderPopupPress('.$popup_id.');
						},
						before: function(){
							beforeSliderPopupPress('.$popup_id.');
						},
						after: function(){
							afterSliderPopupPress('.$popup_id.', pps_popup_'.$popup_id.');
						},
						selector: "#pps-slides-'.$popup_id.' > li",
						animation: "fade",
						prevText: "",
						nextText: "",
					});
				}';
		} else {
			$popupslider = '
				if(typeof jQuery.fn.popupslider == "function") {
					lazyLoadSliderImagesPopupPress('.$popup_id.');
					jQuery("#pps-slider-'.$popup_id.'").popupslider({
						slideshow: '.$slider_auto.',
						slideshowSpeed: '.$slider_timeout.',
						animationSpeed: '.$slider_animation_speed.',
						controlNav: '.$slider_pagination.',
						directionNav: '.$slider_arrows.',
						pauseOnHover: '.$slider_pause.',
						namespace: "pps-",
						startAt: '.$slider_start_at.',
						start: function(slider){
							onStartSliderPopupPress('.$popup_id.');
						},
						before: function(slider){
							beforeSliderPopupPress('.$popup_id.');
						},
						after: function(){
							afterSliderPopupPress('.$popup_id.', pps_popup_'.$popup_id.');
						},
						selector: "#pps-slides-'.$popup_id.' > li",
						animation: "fade",
						prevText: "",
						nextText: "",
					});
				}';
		}
	}

	$bPopup = '
			pps_popup_'.$popup_id.' = jQuery("#popuppress-'.$popup_id.'").bPopup({
				closeClass: "pps-close-link-'.$popup_id.'",
				easing: "'.$popup_easing.'",
				modalClose: '.$close_overlay.',
				modalColor: "'.$popup_modal_color.'",
				opacity: '.$opacity.',
				positionStyle: "'.$popup_position_type.'",
				position: ['.$position_x.','.$position_y.'],
				speed: '.$popup_speed.',
				transition: "'.$popup_transition.'",
				zIndex: '.$popup_zindex.',
				amsl : 0,
				escClose: '.$close_esc_key.',
				follow: '.$follow.',
				onOpen: function(){
					manageSizeEmbedPopupPress('.$popup_id.', '.$embed_height.');
					manageSizeIframePopupPress('.$popup_id.', '.$iframe_height.');
					manageSizePdfPopupPress('.$popup_id.', '.$pdf_height.');
					setImageOverlayPopupPress('.$popup_id.', "'.$img_overlay.'");
					onOpenPopupPress('.$popup_id.');
					disclaimerPopupPress('.$popup_id.', "'.$disclaimer_activate.'", "'.$agree_redirect.'", "'.$disagree_restriction.'", "'.$use_cookie.'", "'.$cookie_expire.'",'.$cookie_days.');
				},
				onClose: function(){
					onClosePopupPress('.$popup_id.');
				},
			},function(){
				openedPopupPress('.$popup_id.');
			});
			';
	$function_popup = $popupslider.$bPopup;

	$close_function = '
		setTimeout(function(){
			if(jQuery("#popuppress-'.$popup_id.'").css("display") == "block")
				jQuery("#popuppress-'.$popup_id.'").bPopup().close();

		},'.$close_delay.');';

	$style_popup = '
	<style type="text/css">'
		.get_css_body_popup_PPS($popup_id, $pps_data, $pps_options)
		.get_css_close_button_PPS($popup_id, $pps_data)
	.'</style>';

	$script_popup = '';
	switch($run_method){
		case 'click':
		case 'mouseover':
			$script_popup .= '
				jQuery(document).delegate(".pps-button-popup-'.$popup_id.$class_run.', a[href=\'pps-button-popup-'.$popup_id.'\'], a[href=\'#pps-button-popup-'.$popup_id.'\']", "'.$run_method.' ", function(e) {
					e.preventDefault();

					startAtNum = (jQuery(this).hasClass("pps-button-thumb")) ? jQuery(this).attr("class").split(" ").pop().replace(/[^0-9\.]/g,"") : 0;
					setTimeout(function(){'.$function_popup.'}, '.$open_delay.');';
				if($auto_close == 'true')
					$script_popup .= $close_function;
				$script_popup .= '});';
			break;
		case 'leave_page':
			if(!is_admin_list_popuppress_PPS()){
				if($use_cookie == 'false'){
					$script_popup .= '
						leavePageCount = 0;
						jQuery(document).bind("mouseleave.pps",function(e){
							if( e.clientY < 0 && !jQuery.cookie("pps_not_show_'.$popup_id.'") ){
								startAtNum = 0;
								leavePageCount++;
								'.$function_popup.'
								if(leavePageCount >= '.$leave_page_max_times.'){
									jQuery(this).unbind("mouseleave.pps");
								}
								';
								if( $auto_close == 'true' ){
									$script_popup .= $close_function;
								}
							$script_popup .= '
							}
						});';
				} else {
					$cookie_path = (true) ? '/' : '';
					//Duracion de la cookie
					$expires = ($cookie_expire == 'number_days') ? '{ expires: '.$cookie_days.', path: "'.$cookie_path.'"}' : '{ path: "'.$cookie_path.'" }';
					$cookie_value = ($cookie_expire == 'current_session') ? 'current_session' : $cookie_days. '_days';

					if($disclaimer_activate == 'true'){
						$script_popup .= '
						var cookieValue = jQuery.cookie("'.$disclaimer_cookie.'");
						if(jQuery.cookie("'.$disclaimer_cookie.'") && cookieValue != "'.$cookie_value.'"){
							$.removeCookie("'.$disclaimer_cookie.'");
						}
						if(!jQuery.cookie("'.$disclaimer_cookie.'") || cookieValue != "'.$cookie_value.'"){
							jQuery(document).bind("mouseleave.pps",function(e){
								if( e.clientY < 0 && !jQuery.cookie("pps_not_show_'.$popup_id.'") ){
									startAtNum = 0;
									'.$function_popup.'
									jQuery(this).unbind("mouseleave.pps");';
									if( $auto_close == 'true' ){
										$script_popup .= $close_function;
									}
									$script_popup .= '
									}
							});
						}';
					} else {
						$script_popup .= '
						var cookieValue = jQuery.cookie("'.$cookie_popup.'");
						if(!jQuery.cookie("'.$cookie_popup.'") || cookieValue != "'.$cookie_value.'"){
							jQuery(document).bind("mouseleave.pps",function(e){
								if( e.clientY < 0 && !jQuery.cookie("pps_not_show_'.$popup_id.'") ){
									startAtNum = 0;
									'.$function_popup.'
									if(jQuery(location).attr("href").indexOf("/wp-admin/") < 0){
										jQuery(this).unbind("mouseleave.pps");
										jQuery.cookie("'.$cookie_popup.'", "'.$cookie_value .'", '. $expires.');
									}';
									if($auto_close == 'true'){
										$script_popup .= $close_function;
									}
									$script_popup .= '
								}
							});
						}';
					}
				}
			}
			break;
		case 'auto_open':
			//Cuando "Open on = Page load", entonces también habilitamos que se ejecute al hacer click
			$script_popup .= '
			jQuery(document).delegate(".pps-button-popup-'.$popup_id.$class_run.', a[href=pps-button-popup-'.$popup_id.']", "click", function(e) {
					e.preventDefault();
			startAtNum = 0;
			setTimeout(function(){'.$function_popup.'}, '.$open_delay.');';
			if($auto_close == 'true')
				$script_popup .= $close_function;
			$script_popup .= '});';

			if(!is_admin_list_popuppress_PPS()){
				if($use_cookie == 'false'){
					$script_popup .= '
			startAtNum = 0;
			if( !jQuery.cookie("pps_not_show_'.$popup_id.'") ){
				setTimeout( function(){'.$function_popup.'}, '.$open_delay.');';
							if($auto_close == 'true'){
								$script_popup .= $close_function;
							}
						$script_popup .= '}';
				} else {
					$cookie_path = (true) ? '/' : '';
					//Duracion de la cookie
					$expires = ($cookie_expire == 'number_days') ? '{ expires: '.$cookie_days.', path: "'.$cookie_path.'"}' : '{ path: "'.$cookie_path.'" }';
					$cookie_value = ($cookie_expire == 'current_session') ? 'current_session' : $cookie_days. '_days';

					if($disclaimer_activate == 'true'){
						$script_popup .= '
						var cookieValue = jQuery.cookie("'.$disclaimer_cookie.'");
						if(jQuery.cookie("'.$disclaimer_cookie.'") && cookieValue != "'.$cookie_value.'"){
							$.removeCookie("'.$disclaimer_cookie.'");
						}
						if(!jQuery.cookie("'.$disclaimer_cookie.'") || cookieValue != "'.$cookie_value.'"){
							startAtNum = 0;
							setTimeout( function(){'.$function_popup.'}, '.$open_delay.');';
							if($auto_close == 'true')
								$script_popup .= $close_function;
							$script_popup .= '
						}';
					} else {
						$script_popup .= '
						var cookieValue = jQuery.cookie("'.$cookie_popup.'");
						if(!jQuery.cookie("'.$cookie_popup.'") || cookieValue != "'.$cookie_value.'"){
							startAtNum = 0;
							setTimeout( function(){'.$function_popup.'}, '.$open_delay.');

							if(jQuery(location).attr("href").indexOf("/wp-admin/") < 0){
								jQuery.cookie("'.$cookie_popup.'", "'.$cookie_value .'", '. $expires.');
							}';

							if($auto_close == 'true')
								$script_popup .= $close_function;
							$script_popup .= '
						}';
					}
				}
			}
	}
	$script_popup = '
	<script type="text/javascript">
		jQuery(document).ready(function($){
			'.$script_popup.'
		});
	</script>';

	return $style_popup.$script_popup;

}

function get_css_body_popup_PPS($popup_id, $pps_data, $pps_options){
	$width = get_post_meta_PPS( $popup_id, 'pps_width', 640 );
	$width_units = get_post_meta_PPS( $popup_id, 'pps_width_units', 'px' );
	$width_css = $width.$width_units;

	$height_css = 'auto';
	$height_units = get_post_meta_PPS( $popup_id, 'pps_height_units', 'px' );
	$height = get_post_meta_PPS( $popup_id, 'pps_height', '' );

	if( get_post_meta_PPS( $popup_id, 'pps_auto_height' ) == 'false' && $height != '' ) {
		$height_css = $height.$height_units;
	}
	$embed_width = $pps_options['embed_width'];
	$embed_width = $pps_options['emded_width_unit'] == '%' ? $embed_width.'%' : $embed_width.'px';

	$embed_height = get_post_meta_PPS( $popup_id, 'pps_embed_height', $pps_options['embed_height'] );
	$iframe_height = get_post_meta_PPS( $popup_id, 'pps_iframe_height', $pps_options['embed_height'] );
	$pdf_height = get_post_meta_PPS( $popup_id, 'pps_pdf_height', $pps_options['embed_height'] );

	$pad_top = (int) get_post_meta_PPS( $popup_id, 'pps_pad_top', 15 );
	$pad_right = (int) get_post_meta_PPS( $popup_id, 'pps_pad_right', 15 );
	$pad_bottom = (int) get_post_meta_PPS( $popup_id, 'pps_pad_bottom', 15 );
	$pad_left = (int) get_post_meta_PPS( $popup_id, 'pps_pad_left', 15 );

	$spacing_content = (int) get_post_meta_PPS( $popup_id, 'pps_spacing_content', 0 );

	$border_radius = (int) get_post_meta_PPS( $popup_id, 'pps_border_radius', 0 );
	$border_radius2 = ($border_radius > 0)? $border_radius + 2 : 0;
	$border_popup = (int) get_post_meta_PPS( $popup_id, 'pps_border_popup', 8 );
	$border_color = rgb2hex2rgb_PPS( get_post_meta_PPS( $popup_id, 'pps_border_color', '#000000' ));
	$border_opacity = get_post_meta_PPS( $popup_id, 'pps_border_opacity', 0.4 );
	$border_rgba = "rgba(".$border_color['r'].",".$border_color['g'].",".$border_color['b'].",".$border_opacity.")";

	$size_text_content = (int) get_post_meta_PPS( $popup_id, 'pps_size_text_content', 16 );
	$no_font_sizes = get_post_meta_PPS( $popup_id, 'pps_no_font_sizes', '' );

	$css_font_sizes = '';
	if($no_font_sizes != 'on') {
		$css_font_sizes = '
		#popuppress-'.$popup_id.' .pps-content,
		#popuppress-'.$popup_id.' .pps-content p,
		#popuppress-'.$popup_id.' .pps-content ul,
		#popuppress-'.$popup_id.' .pps-content ol,
		#popuppress-'.$popup_id.' .pps-content em,
		#popuppress-'.$popup_id.' .pps-content span,
		#popuppress-'.$popup_id.' .pps-content a {
			font-size: '.$size_text_content.'px;
			line-height: 1.6;
		}
		#popuppress-'.$popup_id.' .pps-content h1 {
			font-size: '.($size_text_content*2).'px;
			line-height: 1.3;
			margin: 0.5em 0;
			padding: 0px;
		}
		#popuppress-'.$popup_id.' .pps-content h2 {
			font-size: '.($size_text_content*1.7).'px;
			line-height: 1.3;
			margin: 0.4em 0;
			padding: 0px;
		}
		#popuppress-'.$popup_id.' .pps-content h3 {
			font-size: '.($size_text_content*1.3).'px;
			line-height: 1.3;
			margin: 0.3em 0;
			padding: 0px;
		}
		';
	}

	$color_text_title = get_post_meta_PPS( $popup_id, 'pps_color_text_title', '#444444' );
	$bg_title = get_post_meta_PPS( $popup_id, 'pps_bg_title', '#FFFFFF' );
	$size_text_title = (int) get_post_meta_PPS( $popup_id, 'pps_size_text_title', 20 );
	$color_border_title = get_post_meta_PPS( $popup_id, 'pps_color_border_title', '#EEEEEE' );

	$align_title = get_post_meta_PPS( $popup_id, 'pps_align_title', 'left' );

	$pad_top_title = (int) get_post_meta_PPS( $popup_id, 'pps_pad_top_title', 0 );
	$pad_right_title = (int) get_post_meta_PPS( $popup_id, 'pps_pad_right_title', 0 );
	$pad_bottom_title = (int) get_post_meta_PPS( $popup_id, 'pps_pad_bottom_title', 10 );
	$pad_left_title = (int) get_post_meta_PPS( $popup_id, 'pps_pad_left_title', 0 );

	$custom_css_popup = get_post_meta_PPS( $popup_id, 'pps_custom_css_popup', '' );

	$bg_popup = get_post_meta_PPS( $popup_id, 'pps_bg_content', '#FFFFFF' );

	return '#popuppress-'.$popup_id.' {
			width: '.$width_css.';
			height: '.$height_css.';
			-webkit-border-radius: '.$border_radius2.'px;
			-moz-border-radius: '.$border_radius2.'px;
			border-radius: '.$border_radius2.'px;
			border: solid '.$border_popup.'px '.$border_rgba.';
		}
		#popuppress-'.$popup_id.' .pps-wrap {
			padding: '.$pad_top.'px '.$pad_right.'px '.$pad_bottom.'px '.$pad_left.'px;
			background-color: '.$bg_popup.';
			-webkit-border-radius: '.$border_radius.'px;
			-moz-border-radius: '.$border_radius.'px;
			border-radius: '.$border_radius.'px;
		}
		@media screen and (max-width: 600px){
			#popuppress-'.$popup_id.' .pps-wrap {
			  padding: 0.6em;
			}
		}
		#popuppress-'.$popup_id.' .pps-header {
			background-color: '.$bg_title.';
			-moz-border-radius: '.($border_radius - 3).'px '.($border_radius - 3).'px 0px 0px;
			-webkit-border-radius: '.($border_radius - 3).'px '.($border_radius - 3).'px 0px 0px;
			border-radius: '.($border_radius - 3).'px '.($border_radius - 3).'px 0px 0px;
		}
		#popuppress-'.$popup_id.' .pps-header h3 {
			margin-bottom: '.$pad_bottom_title.'px;
			padding-top: '.$pad_top_title.'px;
			padding-right: '.$pad_right_title.'px;
			padding-bottom: '.($size_text_title/2 + 4).'px;
			padding-left: '.$pad_left_title.'px;
			border-color: '.$color_border_title.';
			line-height: 1.4;
			font-size: '.$size_text_title.'px;
			color: '.$color_text_title.';
			text-align: '.$align_title.'
		}
		#popuppress-'.$popup_id.' .pps-content {
			padding: '.$spacing_content.'px;
		}

		#popuppress-'.$popup_id.' .pps-iframe iframe {
			height: '.$iframe_height.'px;
		}
		#popuppress-'.$popup_id.' .pps-pdf iframe {
			height: '.$pdf_height.'px;
		}
		#popuppress-'.$popup_id.' .pps-embed iframe {
			width: '.$embed_width.';
			height: '.$embed_height.'px;
		}
		#popuppress-'.$popup_id.' .pps-control-nav {
			bottom: -'.(30 + $border_popup).'px;
		}
		'.$css_font_sizes.$custom_css_popup;
}

function get_css_close_button_PPS($popup_id, $pps_data){
	$pad_close_btn = 6;
	$show_close_btn = false;

	$border_popup = (int) get_post_meta_PPS( $popup_id, 'pps_border_popup', 8 );
	$border_color = rgb2hex2rgb_PPS( get_post_meta_PPS( $popup_id, 'pps_border_color', '#000000' ));
	$border_opacity = get_post_meta_PPS( $popup_id, 'pps_border_opacity', 0.4 );
	$border_rgba = "rgba(".$border_color['r'].",".$border_color['g'].",".$border_color['b'].",".$border_opacity.")";

	if( get_post_meta_PPS( $popup_id, 'pps_show_close', 'true' ) == 'true'
		&& get_post_meta_PPS( $popup_id, 'pps_disclaimer_activate', 'false' ) == 'false'){
		$show_close_btn = true;
	}
	$size_close_btn = get_post_meta_PPS( $popup_id, 'pps_size_close_btn', 18 );
	$color_close_btn = get_post_meta_PPS( $popup_id, 'pps_color_close_btn', '#999999' );
	$color_close_btn_hover = get_post_meta_PPS( $popup_id, 'pps_color_close_btn_hover', '#222222' );
	$bg_close_btn = get_post_meta_PPS( $popup_id, 'pps_bg_close_btn', '#FFFFFF' );
	$no_bg_close_btn = get_post_meta_PPS( $popup_id, 'pps_no_bg_close_btn', '' );
	$bg_close_btn = ($no_bg_close_btn == 'on') ? 'transparent' : $bg_close_btn;
	$close_btn_border_radius = get_post_meta_PPS( $popup_id, 'pps_close_btn_border_radius', 18 );
	$margin_top_close_btn = (int) get_post_meta_PPS( $popup_id, 'pps_margin_top_close_btn', -14 );
	$margin_right_close_btn = (int) get_post_meta_PPS( $popup_id, 'pps_margin_right_close_btn', -14 );
	$ref_pos_close_btn = get_post_meta_PPS( $popup_id, 'pps_ref_pos_close_btn', '' );
	$pos_type_close_btn = $ref_pos_close_btn == 'popup' ? 'absolute' : 'fixed';

	$show_wrap_close_btn = get_post_meta_PPS( $popup_id, 'pps_show_wrap_close_btn', 'true' );

	if($show_close_btn && $show_wrap_close_btn == 'true' && $border_popup > 0){
		$size_wrap_close_btn = ($size_close_btn + 2*$pad_close_btn + 2*$border_popup);
		$css_border_close_btn = '
			top: '.($margin_top_close_btn - $border_popup ).'px;
			right: '.($margin_right_close_btn - $border_popup) .'px;
			width: '.$size_wrap_close_btn.'px;
			height: '.$size_wrap_close_btn.'px;
			-webkit-border-radius: '.$size_wrap_close_btn.'px;
			-moz-border-radius: '.$size_wrap_close_btn.'px;
			border-radius: '.$size_wrap_close_btn.'px;
			background-color: '.$border_rgba.';
		';
	} else {
		$css_border_close_btn = '
			width: 0px;
			height: 0px;
			visibility: hidden;
		';
	}


	$css_close_btn = '
		#popuppress-'.$popup_id.'.pps-popup.pps-has-border:before {
			'.$css_border_close_btn.'
		}
		#popuppress-'.$popup_id.' .pps-close {
			position: '.$pos_type_close_btn.';
			top: '.$margin_top_close_btn.'px;
			right: '.$margin_right_close_btn.'px;
		}
		#popuppress-'.$popup_id.' .pps-close a.pps-close-link {
			width: '.$size_close_btn.'px;
			height: '.$size_close_btn.'px;
			line-height: 1;
			background-color: '.$bg_close_btn.';
			-webkit-border-radius: '.$close_btn_border_radius.'px;
			-moz-border-radius: '.$close_btn_border_radius.'px;
			border-radius: '.$close_btn_border_radius.'px;
		}
		#popuppress-'.$popup_id.' .pps-close a.pps-close-link i.pps-icon:before{
			font-size: '.$size_close_btn.'px;
			line-height: 1;
			color: '.$color_close_btn.'
		}
		#popuppress-'.$popup_id.' .pps-close a.pps-close-link:hover i.pps-icon:before{
			color: '.$color_close_btn_hover.'
		}
	';
	return $css_close_btn;
}

/* --------------------------------------------------------------------
   Generamos el Contenido del Popup
-------------------------------------------------------------------- */
function get_content_popup_PPS($popup_id = 0){
	$pps_data = get_post_custom($popup_id);
	$pps_options = get_option('pps_options');
	$numItems = 0;

	$content_oembed = maybe_unserialize( get_post_meta_PPS( $popup_id, 'pps_oembed_repeatable', '' ) );
	$content_oembed = is_array($content_oembed) ? array_filter($content_oembed) : '';

	$content_fields = array(
		"mbox_editor" => get_post_meta_PPS( $popup_id, 'pps_mbox_editor_order', 1 ),
		"mbox_file" => get_post_meta_PPS( $popup_id, 'pps_mbox_file_order', 2 ),
		"mbox_oembed" => get_post_meta_PPS( $popup_id, 'pps_mbox_oembed_order', 3 ),
		"mbox_iframe" => get_post_meta_PPS( $popup_id, 'pps_mbox_iframe_order', 4 ),
		"mbox_pdf" => get_post_meta_PPS( $popup_id, 'pps_mbox_pdf_order', 5 ),
	);

	$content_pps = "";

	asort($content_fields); // Ordenamos el Contenido del Popup
	foreach ($content_fields as $key => $val) {
		if($key == 'mbox_editor'){
			$content_pps .= get_wpeditor_content_PPS( $pps_data, $numItems, $popup_id );
		}

		if($key == 'mbox_file'){
			$content_pps .= get_image_content_PPS( $numItems, $popup_id, $pps_options );
		}

		if($key == 'mbox_oembed'){
			$content_pps .= get_oembed_content_PPS( $pps_data, $numItems, $popup_id, $pps_options );
		}
		if($key == 'mbox_iframe'){
			$content_pps .= get_iframe_content_PPS( $pps_data, $numItems, $popup_id );
		}

		if($key == 'mbox_pdf'){
			$content_pps .= get_pdf_content_PPS( $pps_data, $numItems, $popup_id );
		}
	}

	if( $numItems <= 1 ){
		$content_popup = '<div class="pps-single-popup">'.$content_pps.'</div>';
		if( isset($GLOBALS['pps-slider-'.$popup_id]) ){
			unset($GLOBALS['pps-slider-'.$popup_id]);
		}
	} else {
		$content_popup = '<div class="popupslider" id="pps-slider-'.$popup_id.'"><ul id="pps-slides-'.$popup_id.'" class="pps-slides">'.$content_pps.'</ul></div>';
		$GLOBALS['pps-slider-'.$popup_id] = true;
	}
	//Popup as Disclaimer
	$disclaimer_popup = get_disclaimer_popup_PPS($popup_id);

	$not_show_again = get_post_meta_PPS( $popup_id, 'pps_not_show_again' ) == 'true' ? "<a href='?popup_id=$popup_id' class='pps-not-show-again'>Not show again</a>" : '';


	return $content_popup.$disclaimer_popup.$not_show_again;
}

function get_wpeditor_content_PPS($pps_data, &$numItems, $popup_id){
	$content = '';
	$popup = get_post($popup_id);
	if(strlen($popup->post_content) < 2 || $popup->post_content == '&nbsp;')
		return;

	$query_pps = new WP_Query( array('post_type' => 'popuppress', 'p'=> $popup_id) );
	if($query_pps->have_posts()):
		while($query_pps->have_posts()) : $query_pps->the_post();
			ob_start();
			the_content();
			$wpeditor_content = ob_get_contents();
			ob_end_clean();
		endwhile;
	endif;
	wp_reset_postdata();
	//$wpeditor_content = do_shortcode(get_post_field('post_content', $popup_id));
	$use_editor = get_post_meta_PPS( $popup_id, 'pps_use_wp_editor', 'true' );

	if( ! empty($wpeditor_content) && $use_editor != 'false' ){
		$content = '<li class="pps-item-content"><div class="pps-content-wp-editor entry-content post-content">'.$wpeditor_content.'</div></li>';
		$numItems++;
	}
	return $content;
}

function get_image_content_PPS(&$numItems, $popup_id, $pps_options){
	$content = '';
	$pps_rg_img = get_post_meta( $popup_id, 'pps_rg_img', true );

	if(!empty($pps_rg_img)){
		foreach ($pps_rg_img as $key => $img_array) {
			$img_file = isset($img_array['image']) ? $img_array['image'] : '';
			if(preg_match('/(^.*\.jpg|jpeg|png|gif|ico*)/i', $img_file)){
				$img_url = isset($img_array['image_link']) ? $img_array['image_link'] : '';
				$link_star = $link_end = '';
            if (filter_var($img_url, FILTER_VALIDATE_URL)) {
            	$link_star = $link_end = '';
            	$link_star = '<a href="'.$img_url.'" target="'.$pps_options['where_open_link'].'">';
					$link_end = '</a>';
            }
            $img_caption = isset($img_array['image_caption']) && !empty($img_array['image_caption']) ? '<p class="pps-caption">'.$img_array['image_caption'].'</p>' : '';
            if( $numItems > 0 ){
            	$content .= '<li class="pps-item-content" data-src="'.$img_file.'">'.$link_star.'<img src="" class="pps-img-slider" />'.$link_end . $img_caption .'</li>';
            } else {
            	$content .= '<li class="pps-item-content" data-src="'.$img_file.'">'.$link_star.'<img src="'.$img_file.'" class="pps-img-slider" />'.$link_end . $img_caption .'</li>';
            }

					$numItems++;
			}
		}
	}
	return $content;
}

function get_oembed_content_PPS($pps_data, &$numItems, $popup_id, $pps_options){
	$content = '';
	$pps_rg_oembed = get_post_meta( $popup_id, 'pps_rg_oEmbed', true );
	$embed_autoplay = '';
	$embed_normal = '';
	$autoplay = get_post_meta_PPS( $popup_id, 'pps_autoplay_embed', 'false' );
	$embed_width = get_width_content_popup_PPS( $popup_id );
	$embed_height = get_post_meta_PPS( $popup_id, 'pps_embed_height', $pps_options['embed_height'] );

	if( ! empty( $pps_rg_oembed ) ) {
		foreach ($pps_rg_oembed as $key => $oembed_array) {
			$embed_url = $oembed_array['embed'];
			$args = array(
				'width' => $embed_width,
				'height' => $embed_height
			);
			if( $key == 0  && $autoplay == 'true' ){
				$args['autoplay'] = 1;
			}

			$embed_code = wp_oembed_get( $embed_url, $args );

			//Aveces wp_oembed_get falla, con esto damos soporte para algunos embeds
			if ( ! $embed_code ){
				$embed_code = get_iframe_from_url_PPS( $embed_url, $args);
			}

			if( $embed_code ){
				//preg_match('/(?<!_)src=([\'"])?(.*?)\\1/', $embed_code, $matches);//src = $matches[0]
				//preg_match('|<iframe [^>]*(src="[^"]+")[^>]*|', $embed_code, $matches);//src = $matches[1]
				//preg_match('/<*iframe[^>]*src *= *["\']?([^"\']*)/i', $embed_code, $matches);//src = $matches[1]
				///preg_match('/src="([^"]+)"/');//src = $matches[1], funciona sólo para iframes con atributos doble comilla

				//For Youtube videos, Hide related videos and set Autoplay videos.
				if ( strpos( $embed_url, 'youtu.be' ) !== false || strpos( $embed_url, 'youtube.com' ) !== false ) {
					if( strpos( $embed_code , 'rel=0' ) === false ){
						$embed_code = str_replace("feature=oembed", "feature=oembed&rel=0", $embed_code);
					}
					if ( $key == 0  && $autoplay == 'true' ) {
						if ( strpos( $embed_code , 'autoplay=1' ) === false ){
							$embed_code = str_replace("feature=oembed&rel=0", "feature=oembed&rel=0&autoplay=1", $embed_code);
						}
					}
				}

				$embed_src = 'about:blank';
				if( preg_match('/<*iframe[^>]*src *= *["\']?([^"\']*)/i', $embed_code, $matches) ) {
					if( isset( $matches[1] ) ){
						$embed_src = $matches[1];
					}
				}
				//Remove src attribute for lazy load.
				$embed_code = preg_replace('/(?<!_)src=([\'"])?(.*?)\\1/', 'src="about:blank"', $embed_code);

				$content .= "<li class='pps-item-content'>";
					$content .= "<div class='pps-embed' data-src='$embed_src'>";
						$content .= $embed_code;
					$content .= "</div>";
					if( ! empty( $oembed_array['pps_html_bellow_media'] ) ){
						$content .= "<div class='pps-embed-bellow'>";
							$content .= $oembed_array['pps_html_bellow_media'];
						$content .= "</div>";
					}
				$content .= "</li>";
				$numItems++;
			}
		}
	}
	return $content;
}

function get_iframe_content_PPS($pps_data, &$numItems, $popup_id){
	$content = '';
	$iframe_content = get_post_meta_PPS( $popup_id, 'pps_iframe', '' );
	$iframe_height = get_post_meta_PPS( $popup_id, 'pps_iframe_height', 460 );
	$iframe_bellow = get_post_meta_PPS( $popup_id, 'pps_html_bellow_iframe', '' );
	if( !empty($iframe_content)) {
		$content .= "<li class='pps-item-content'>";
			$content .= '<div class="pps-iframe" data-src="'.$iframe_content.'" data-height="'.$iframe_height.'">';
				$content .= '<iframe src="about:blank" height="'.$iframe_height.'"></iframe>';
			$content .= "</div>";
			if( ! empty( $iframe_bellow ) ){
				$content .= "<div class='pps-iframe-bellow'>";
					$content .= $iframe_bellow;
				$content .= "</div>";
			}
		$content .= "</li>";
		$numItems++;
	}
	return $content;
}

function get_pdf_content_PPS($pps_data, &$numItems, $popup_id){
	$content = '';
	$pdf_content = get_post_meta_PPS( $popup_id, 'pps_pdf', '' );
	$pdf_height = get_post_meta_PPS( $popup_id, 'pps_pdf_height', 460 );
	$filetype = wp_check_filetype($pdf_content);
	if( !empty($pdf_content) and $filetype['ext'] == 'pdf') {
		$download_pdf = get_post_meta_PPS( $popup_id, 'pps_pdf_download_link' ) == 'true' ? '<a href="'.$pdf_content.'" class="pps-download-pdf">Download</a>' : '';
		$protocol = get_protocol_PPS();
		$content = '<li class="pps-item-content"><div class="pps-pdf" data-src="'.$pdf_content.'" data-height="'.$pdf_height.'" data-protocol="'.$protocol.'"><iframe src="about:blank" height="'.$pdf_height.'" frameborder="0"></iframe>'.$download_pdf.'</div></li>';
		$numItems++;
	}
	return $content;
}

/* ----------------------------------------------------------------------
   Función que retornaun Popup Disclaimer
---------------------------------------------------------------------- */
function get_disclaimer_popup_PPS($popup_id = 0){
	$pps_data = get_post_custom($popup_id);

	$disclaimer_popup = "";

	if( 'true' == get_post_meta_PPS( $popup_id, 'pps_disclaimer_activate' ) ){
		$agree_redirect_to = get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_redirect_to', '#' );
		$agree_redirect_to = ($agree_redirect_to == 'http://') ? '#' : $agree_redirect_to;
		$agree_redirect_target = get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_target', '_self' );

		$disagree_redirect_to = get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_redirect_to', '#' );
		$disagree_redirect_to = ($disagree_redirect_to == 'http://') ? '#' : $disagree_redirect_to;
		$disagree_redirect_target = get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_target', '_self' );


		$disclaimer_popup .= '<div class="pps-disclaimer">';
			$disclaimer_popup .= '<a href="'.$agree_redirect_to.'" id="pps-btn-agree-'.$popup_id.'" class="'.get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_button_css' ).'" target="'.$agree_redirect_target.'">'.get_post_meta_PPS( $popup_id, 'pps_disclaimer_agree_button_text' ).'</a>';

			$disclaimer_popup .= '<a href="'.$disagree_redirect_to.'" id="pps-btn-disagree-'.$popup_id.'" class="'.get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_button_css' ).'" target="'.$disagree_redirect_target.'">'.get_post_meta_PPS( $popup_id, 'pps_disclaimer_disagree_button_text' ).'</a>';
			$disclaimer_popup .= '</div>';
	}
	return $disclaimer_popup;
}

/* ----------------------------------------------------------------------
  Función que retorna el ancho del contenido de un Popup
---------------------------------------------------------------------- */
function get_width_content_popup_PPS( $popup_id ){
	$content_width = '';
	$width = get_post_meta_PPS( $popup_id, 'pps_width', 640 );
	$width_units = get_post_meta_PPS( $popup_id, 'pps_width_units', 'px' );
	$pad_left = get_post_meta_PPS( $popup_id, 'pps_pad_left', 20 );
	$pad_right = get_post_meta_PPS( $popup_id, 'pps_pad_right', 20 );

	if( $width_units == 'px' ){
		$content_width = $width - $pad_left - $pad_right;
	} else {
		$content_width = $width . '%';
	}
	return $content_width;
}

/* --------------------------------------------------------------------
   Función que Actualiza el Número de Vistas de un Popup
-------------------------------------------------------------------- */

add_action('wp_ajax_update_views_popups', 'update_views_PPS');
add_action('wp_ajax_nopriv_update_views_popups', 'update_views_PPS');

function update_views_PPS(){
	$popup_id = $_POST['id'];
	$plugin = $_POST['plugin'];
	$restore = $_POST['restore'];
	// Seguridad
	if( empty($popup_id) || $plugin != 'popuppress' ){
		return;
	}

	//Si la accion es para restaurar valores
	if($restore == 'restore'){
		$views_count = 0;
		update_post_meta($popup_id, 'pps-views', 0);
	}
	else {
		//Sumamos una 'vista' al Popup
		$views_count = (int) get_post_meta($popup_id, 'pps-views', true);
		update_post_meta($popup_id, 'pps-views', ++$views_count);
	}

	$result = array(
		'success' => true,
		'views' => $views_count,
	);

	echo json_encode($result);
	wp_die();
}

/* --------------------------------------------------------------------
   Función que inserta el cuerpo de un popup a la página mediante AJAX
-------------------------------------------------------------------- */
add_action('wp_ajax_load_popup', 'load_popup_by_ajax_PPS');
add_action('wp_ajax_nopriv_load_popup', 'load_popup_by_ajax_PPS');
function load_popup_by_ajax_PPS(){
	$popup_id = $_POST['id'];
	$plugin = $_POST['plugin'];
	$pps_nonce = $_POST['pps_nonce'];

	// Seguridad
	if(empty($popup_id) || $plugin != 'popuppress' || !wp_verify_nonce($pps_nonce, 'load_popup')){
		return;
	}
	$result = array(
		'success' => true,
		'popup' => get_popup_PPS($popup_id)
	);
	echo json_encode($result);
	wp_die();
}


/* --------------------------------------------------------------------
   Función que comprueba la compatibilidad de las nuevas opciones mediante AJAX
-------------------------------------------------------------------- */
add_action('wp_ajax_check_compatibility_popups', 'check_compatibility_popups_PPS');
add_action('wp_ajax_nopriv_check_compatibility_popups', 'check_compatibility_popups_PPS');
function check_compatibility_popups_PPS(){
    $pps_nonce = $_POST['pps_nonce'];

    // Seguridad
    if(!wp_verify_nonce($pps_nonce, 'pps_nonce_compatibility')){
        return;
    }
    $updated_popups = update_options_popup_to_new_version_PPS();
    $result = array(
        'success' => true,
        'popups' => $updated_popups,
    );
    echo json_encode($result);
    wp_die();
}

/* --------------------------------------------------------------------
   Actualiza los campos de versiones anteriores a la nueva versión
-------------------------------------------------------------------- */
function update_options_popup_to_new_version_PPS(){
	//Default Options
	$pps_options = get_option('pps_options');
	$args = array(
		'post_type' => 'popuppress',
		'post_status' => 'publish',
		'posts_per_page' => -1,
	);
	$pps_popups = get_posts($args);
	$updated_popups = '';
	foreach ( $pps_popups as $post ){
		setup_postdata($post);
		$post_id = $post->ID;

		# Imágenes ---------------------------------------------------------------
		if(!is_array(get_post_meta( $post_id, 'pps_rg_img', true ))){
			//Campos de versiones anteriores
			$file_repeatable = get_post_meta( $post_id, 'pps_file_repeatable', true );
			$file_repeatable_link = get_post_meta( $post_id, 'pps_file_repeatable_link', true );
			$file_repeatable_caption = get_post_meta( $post_id, 'pps_file_repeatable_caption', true );

			//Nuevo campo
			$pps_rg_img = get_post_meta( $post_id, 'pps_rg_img', true );

			if(is_array($file_repeatable) && !empty($file_repeatable[0])){
				foreach ($file_repeatable as $key => $img_url) {
					$img_url = trim($img_url);
					if(!empty($img_url)){
						//Actualizamos a los nuevos campos
						$pps_rg_img[$key]['image'] = $img_url;
						$pps_rg_img[$key]['image_link'] = $file_repeatable_link[$key];
						$pps_rg_img[$key]['image_caption'] = $file_repeatable_caption[$key];
						$pps_rg_img[$key]['image_id'] = get_attachment_id_by_url_PPS($img_url);
						update_post_meta($post_id, 'pps_rg_img', $pps_rg_img);
					}
			   }
			}
		}

		# Medios (oEmbed) -------------------------------------------------------
		if(!is_array(get_post_meta( $post_id, 'pps_rg_oEmbed', true ))){
			//Campos de versiones anteriores
			$oembed_repeatable = get_post_meta( $post_id, 'pps_oembed_repeatable', true );

			//Nuevo campo
			$pps_rg_oembed = get_post_meta( $post_id, 'pps_rg_oEmbed', true );

			if(is_array($oembed_repeatable) && !empty($oembed_repeatable[0])){
				foreach ($oembed_repeatable as $key => $embed_url) {
					$embed_url = trim($embed_url);
					if(!empty($embed_url)){
						//Actualizamos a los nuevos campos
						$pps_rg_oembed[$key]['embed'] = $embed_url;
						update_post_meta($post_id, 'pps_rg_oEmbed', $pps_rg_oembed);
					}
			   }
			}
		}

		#Open delay
		$pps_data = get_post_custom($post_id);
		$auto_open = isset($pps_data['pps_auto_open'][0]) ? $pps_data['pps_auto_open'][0] : 'false';
		$run_method = isset($pps_data['pps_open_hook'][0]) ? $pps_data['pps_open_hook'][0] : 'click';
		if($auto_open == 'true' || $run_method == 'auto_open'){
			update_post_meta($post_id, 'pps_open_hook', 'auto_open');
			update_post_meta($post_id, 'pps_open_delay', $pps_data['pps_delay'][0]);
		}

		#Content background
		$bg_content = $pps_data['pps_popup_style'][0];
		if ($pps_data['pps_popup_style'][0] == 'light') {
			$bg_content = '#FFFFFF';
		} else if($pps_data['pps_popup_style'][0] == 'dark'){
			$bg_content = '#000000';
		} else {
			$bg_content = $pps_data['pps_bg_content'][0];
		}
		update_post_meta($post_id, 'pps_bg_content', $bg_content);

		#Transparent border
		$pps_border_popup = ($pps_data['pps_transparent_border'][0] == 'true') ? 8 : '00';
		update_post_meta($post_id, 'pps_border_popup', $pps_border_popup);

		#Slider
		$slider_speed = !empty($pps_data["pps_slider_speed"][0]) ? $pps_data["pps_slider_speed"][0] :  $pps_options["slider_animation_speed"];

		update_post_meta($post_id, 'pps_slider_animation_speed', $slider_speed);

		$updated_popups .= '<li>'.get_the_title($post_id).'</li>';
	}
	wp_reset_postdata();

	//Actualizamos opción para no volver a mostrar aviso de actualización
	update_option('pps_compatibility_option', true );

	return $updated_popups;
}

/* --------------------------------------------------------------------
   Permite actualizar algunos campos para las nuevas versiones, se
   ejecuta al cargar la página de edición de algún popup en específico.
-------------------------------------------------------------------- */
add_action( 'admin_head', 'update_fields_metabox_PPS' );
function update_fields_metabox_PPS() {
	global $post;
	$post_id = isset($_GET['post']) ? $_GET['post']: 0;
	if(get_post_type($post) == 'popuppress' && $post_id != 0){
		//nada por ahora
	}
}

/* ----------------------------------------------------------------------------------
 * Return an ID of an attachment by searching the database with the file URL.
 * @link http://frankiejarrett.com/get-an-attachment-id-by-url-in-wordpress/
 * @link http://philipnewcomer.net/2012/11/get-the-attachment-id-from-an-image-url-in-wordpress/
 * -------------------------------------------------------------------------------- */

function get_attachment_id_by_url_PPS($url) {
	global $wpdb;

	// Get the upload directory paths
	$upload_dir_paths = wp_upload_dir();

	// Split the $url into two parts with the wp-content directory as the separator
	$parsed_url = explode( parse_url( $upload_dir_paths['baseurl'], PHP_URL_PATH ), $url );
	// Get the host of the current site and the host of the $url, ignoring www
	$this_host = str_ireplace( 'www.', '', parse_url( home_url(), PHP_URL_HOST ) );
	$file_host = str_ireplace( 'www.', '', parse_url( $url, PHP_URL_HOST ) );

	// Return nothing if there aren't any $url parts or if the current host and $url host do not match
	if ( !isset( $parsed_url[1] ) || empty( $parsed_url[1] ) || ( $this_host != $file_host ) || false === strpos($url, $upload_dir_paths['baseurl']) ) {
		return;
	}
	$parsed_url[1] = preg_replace('/-[0-9]{1,4}x[0-9]{1,4}\.(jpg|jpeg|png|gif|bmp)$/i', '.$1', $parsed_url[1]);
	$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM {$wpdb->prefix}posts WHERE guid RLIKE %s;", $parsed_url[1] ) );
	if(isset($attachment[0])){
		return $attachment[0];
	}

	return '';
}

function rgb2hex2rgb_PPS($color){
   if(!$color) return false;
   $color = trim($color);
   $result = false;
  if(preg_match("/^[0-9ABCDEFabcdef\#]+$/i", $color)){
      $hex = str_replace('#','', $color);
      if(!$hex) return false;
      if(strlen($hex) == 3):
         $result['r'] = hexdec(substr($hex,0,1).substr($hex,0,1));
         $result['g'] = hexdec(substr($hex,1,1).substr($hex,1,1));
         $result['b'] = hexdec(substr($hex,2,1).substr($hex,2,1));
      else:
         $result['r'] = hexdec(substr($hex,0,2));
         $result['g'] = hexdec(substr($hex,2,2));
         $result['b'] = hexdec(substr($hex,4,2));
      endif;
   }elseif (preg_match("/^[0-9]+(,| |.)+[0-9]+(,| |.)+[0-9]+$/i", $color)){
      $rgbstr = str_replace(array(',',' ','.'), ':', $color);
      $rgbarr = explode(":", $rgbstr);
      $result = '#';
      $result .= str_pad(dechex($rgbarr[0]), 2, "0", STR_PAD_LEFT);
      $result .= str_pad(dechex($rgbarr[1]), 2, "0", STR_PAD_LEFT);
      $result .= str_pad(dechex($rgbarr[2]), 2, "0", STR_PAD_LEFT);
      $result = strtoupper($result);
   }else{
      $result = false;
   }

   return $result;
}


/* --------------------------------------------------------------------
   Comprueba si estamos en la lista de popups del área de administración
-------------------------------------------------------------------- */

function is_admin_list_popuppress_PPS(){
	if(strpos($_SERVER['REQUEST_URI'],'/edit.php?post_type=popuppress') !== false)
		return true;
	return false;
}

/* --------------------------------------------------------------------
   Get Site URL Protocol "HTTP or HTTPS"
-------------------------------------------------------------------- */
function get_protocol_PPS(){
	if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
	  return "https";
	}
	return "http";
}

/* --------------------------------------------------------------------
  Soporte del parámetro autoplay a oEmbed
-------------------------------------------------------------------- */
add_filter('oembed_fetch_url', 'set_autoplay_to_oembed_PPS', 10, 3);
function set_autoplay_to_oembed_PPS( $provider, $url, $args ) {
	$autoplay = 0;
	if ( isset( $args['autoplay'] ) ){
		$autoplay = $args['autoplay'];
	}
  if ( strpos( $provider, 'youtube' ) !== false ) {
    $provider = add_query_arg( 'autoplay', $autoplay, $provider );//No funciona
  }
  if ( strpos( $provider, 'vimeo' ) !== false ) {
    $provider = add_query_arg( 'autoplay', $autoplay, $provider );
  }
  if ( strpos( $provider, 'soundcloud' ) !== false ) {
  	$autoplay = $autoplay == 0 ? 'false' : 'true';
    $provider = add_query_arg( 'auto_play', $autoplay, $provider );
  }
  return $provider;
}


/* --------------------------------------------------------------------
  Manage embeds: support for youtube, vimeo, dailymotion and soundcloud
-------------------------------------------------------------------- */
function get_iframe_from_url_PPS( $url = '', $args = array() ){
	$iframe = '';
	$defaults = array(
		'width' => 600,
		'height' => 338,
		'autoplay' => 0
	);
	$args = array_merge( $defaults, $args );

	if( is_youtube_url_PPS( $url ) ){
		$video_id = get_youtube_id_PPS( $url );
		$parameters = "feature=oembed&rel=0";
		if ( $args['autoplay'] == 1 ){
			$parameters .= "&autoplay=1";
		}
		$iframe = "<iframe src='//www.youtube.com/embed/{$video_id}?{$parameters}' frameborder='0' allowfullscreen></iframe>";
	}
	else if( is_vimeo_url_PPS( $url ) ){
		$video_id = get_vimeo_id_PPS( $url );
		$parameters = "byline=0&portrait=1&badge=0";
		if ( $args['autoplay'] == 1 ){
			$parameters .= "&autoplay=1";
		}
		$iframe = "<iframe src='//player.vimeo.com/video/{$video_id}?{$parameters}' frameborder='0' allowfullscreen></iframe>";
	}
	else if( is_dailymotion_url_PPS( $url ) ){
		$video_id = get_dailymotion_id_PPS( $url );
		$parameters = "sharing-enable=0&ui-logo=0";
		if ( $args['autoplay'] == 1 ){
			$parameters .= "&autoplay=1";
		}
		$iframe = "<iframe src='//www.dailymotion.com/embed/video/{$video_id}?{$parameters}' frameborder='0' allowfullscreen></iframe>";
	}
	else if( is_soundcloud_url_PPS( $url ) ){
		$parameters = "url=$url&format=js&iframe=true";
		if ( $args['autoplay'] == 1 ){
			$parameters .= "&auto_play=true";
		}
		//Get the JSON data of song details with embed code from SoundCloud oEmbed
		//https://developers.soundcloud.com/docs/api/reference#oembed
		$data = file_get_contents("http://soundcloud.com/oembed?$parameters");
		if( $data !== false ){
			//Clean the Json to decode, remove: ( and );
			$decode_iframe = substr( $data, 1, -2 );
			//json decode to convert it as an array
			$json_obj = json_decode( $decode_iframe, true );
			if( isset( $json_obj['html'] ) ){
				$iframe = $json_obj['html'];
			}
		}
	}
	return $iframe;
}

function get_youtube_id_PPS( $url ) {
  $pattern =
    '~(?#!js YouTubeId Rev:20160125_1800)
    # Match non-linked youtube URL in the wild. (Rev:20130823)
    https?://          # Required scheme. Either http or https.
    (?:[0-9A-Z-]+\.)?  # Optional subdomain.
    (?:                # Group host alternatives.
      youtu\.be/       # Either youtu.be,
    | youtube          # or youtube.com or
      (?:-nocookie)?   # youtube-nocookie.com
      \.com            # followed by
      \S*?             # Allow anything up to VIDEO_ID,
      [^\w\s-]         # but char before ID is non-ID char.
    )                  # End host alternatives.
    ([\w-]{11})        # $1: VIDEO_ID is exactly 11 chars.
    (?=[^\w-]|$)       # Assert next char is non-ID or EOS.
    (?!                # Assert URL is not pre-linked.
      [?=&+%\w.-]*     # Allow URL (query) remainder.
      (?:              # Group pre-linked alternatives.
        [\'"][^<>]*>   # Either inside a start tag,
      | </a>           # or inside <a> element text contents.
      )                # End recognized pre-linked alts.
    )                  # End negative lookahead assertion.
    [?=&+%\w.-]*       # Consume any URL (query) remainder.
    ~ix'
    ;
  $result = preg_match($pattern, $url, $matches);
  if ( $result && isset( $matches[1] ) ) {
    return $matches[1];
  }
  return false;
}

function get_vimeo_id_PPS( $url ){
  $pattern = '/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/';
  $result = preg_match($pattern, $url, $matches);
  if ( $result && isset( $matches[5] ) ) {
    return $matches[5];
  }
  return false;
}

function get_dailymotion_id_PPS( $url ){
  $pattern = '!^.+dailymotion\.com/(video|hub)/([^_]+)[^#]*(#video=([^_&]+))?|(dai\.ly/([^_]+))!';
  $result = preg_match($pattern, $url, $matches);
  if ( $result ) {
    if (isset($matches[6])) {
      return $matches[6];
    }
    if (isset($matches[4])) {
      return $matches[4];
    }
    return $matches[2];
  }
  return false;
}

function is_youtube_url_PPS( $url ){
  if( false !== get_youtube_id_PPS( $url ) ){
    return true;
  }
  return false;
}

function is_vimeo_url_PPS( $url ){
  if( false !== get_vimeo_id_PPS( $url ) ){
    return true;
  }
  return false;
}

function is_dailymotion_url_PPS( $url ){
  if( false !== get_dailymotion_id_PPS( $url ) ){
    return true;
  }
  return false;
}

function is_soundcloud_url_PPS( $url ){
	$pattern = '/^https?:\/\/(soundcloud.com|snd.sc)\/(.*)$/';
  $result = preg_match($pattern, $url, $matches);
  if ( $result ) {
  	return true;
  }
  return false;
}

add_filter( 'wp_video_shortcode', 'remove_mediaelement_video_PPS', 10, 5 );
function remove_mediaelement_video_PPS( $output, $atts, $video, $post_id, $library ){
	if( 'popuppress' != get_post_type($post_id) ){
		return $output;
	}
	$output = str_replace ( 'wp-video-shortcode' ,'pps-video-shortcode' , $output );
	return $output;
}


/* --------------------------------------------------------------------
  Debug
-------------------------------------------------------------------- */
function d_pps($var){
	echo "<br/>";
	echo "<code>";
	var_dump($var);
	echo "</code>";
	echo "<br/>";
}


?>