<?php
/*
Plugin Name: Rating Form
Plugin URI: http://serdarg.nl/rating-form/
Description: Create rating forms
Version: 1.3.9
Author: Serdar Gürler
Author URI: http://serdarg.nl/
License: GPL
Text Domain: rating-form
Domain Path: /languages
*/

/*
* Copyright 2014 Serdar Gürler
*/

//Prevent direct access
if ( !defined('ABSPATH') ) {
	die;
}

//Plugin class
class Rating_Form
{
	private static $instance = null;
	private static $displayAdminMsg = true;

	const
	//Class
	PLUGIN_SLUG = 'rating-form',
	PLUGIN_VERSION = '1.3.9',
	//Options
	OPTION_VERSION = 'rating_form_version',
	//Tables
	TBL_RATING_ADD_FORM = 'rating_form',
	TBL_RATING_RATED = 'rating_form_rated',
	TBL_RATING_TITLES = 'rating_form_titles',
	TBL_RATING_BLOCK_IP = 'rating_form_block_ip',
	TBL_RATING_FORM_TITLES = 'rating_form_title',
	TBL_RATING_POST_TYPES = 'rating_form_post_type',
	TBL_RATING_USER_ROLES = 'rating_form_user_role',
	//Pages
	PAGE_RESULT_RATING_SLUG = 'rating_form_results',
	PAGE_FORM_RATING_SLUG = 'rating_forms',
	PAGE_TITLES_RATING_SLUG = 'rating_form_titles',
	PAGE_BLOCK_IP_SLUG = 'rating_form_block_ip',
	PAGE_NEW_RATING_SLUG = 'rating_form_add',
	PAGE_TOOLS_RATING_SLUG = 'rating_form_tools';

	//Initializes hooks
	public static function init() {
		if ( null == self::$instance ) {

            self::$instance = new Rating_Form;

			if ( is_admin() ) {
				add_action( 'admin_menu', array( self::$instance, 'add_admin_menus' ) );
				add_action( 'admin_enqueue_scripts', array( self::$instance, 'admin_header_snippets' ) );
				if (self::$displayAdminMsg) {
					add_action( 'admin_notices',  array( self::$instance, 'display_admin_messages' ) );
				}
			}

			add_action( 'init', array( self::$instance, 'load_textdomain' ) );
			add_action( 'wp_enqueue_scripts', array( self::$instance, 'assets_front' ) );

			self::$instance->ajax_actions();
			self::$instance->tools_filters();
			self::$instance->files();

        }

		return Rating_Form::$instance;
	}

	//Include files
	function files() {
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_list.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_titles.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_block_ip.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_add.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_results.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'rating_form_tools.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'shortcode.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'widget.php';
			require dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'ajax.php';
	}

	public function ajax_actions() {
		// Ajax Add Rating
		add_action( 'wp_ajax_nopriv_rating_form_rating_add', 'rating_form_rating_add' );
		add_action( 'wp_ajax_rating_form_rating_add', 'rating_form_rating_add' );
		// Ajax Load of a Rating Form
		add_action( 'wp_ajax_nopriv_ajax_display_rating_form_total', 'ajax_display_rating_form_total' );
		add_action( 'wp_ajax_ajax_display_rating_form_total', 'ajax_display_rating_form_total' );
		// Ajax Total Average Ratings
		add_action( 'wp_ajax_nopriv_ajax_display_rating_form', 'ajax_display_rating_form' );
		add_action( 'wp_ajax_ajax_display_rating_form', 'ajax_display_rating_form' );
		// Ajax Add IP
		add_action( 'wp_ajax_nopriv_rating_form_add_ip', 'rating_form_add_ip' );
		add_action( 'wp_ajax_rating_form_add_ip', 'rating_form_add_ip' );
		// Ajax Edit IP
		add_action( 'wp_ajax_nopriv_rating_form_block_ip_edit', 'rating_form_block_ip_edit' );
		add_action( 'wp_ajax_rating_form_block_ip_edit', 'rating_form_block_ip_edit' );
	}

	//Plugin assets for front
	public function assets_front() {
		$upload_dir = wp_upload_dir();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'rating-form-icons', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'icons.css', __FILE__ ), array(), Rating_Form::PLUGIN_VERSION );
		wp_enqueue_style( 'rating-form-front', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'front.css', __FILE__ ), array(), Rating_Form::PLUGIN_VERSION );
		wp_enqueue_script( 'rating-form-front-script', plugins_url('assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'front.js', __FILE__), array('jquery'), Rating_Form::PLUGIN_VERSION, true );
		wp_localize_script( 'rating-form-front-script', 'rating_form_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'uploadurl' => $upload_dir['baseurl'], 'pluginversion' => Rating_Form::PLUGIN_VERSION ) );
	}

	//Admin css
	function admin_header_snippets() {
		$upload_dir = wp_upload_dir();
		wp_enqueue_script( 'jquery' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/smoothness/jquery-ui.css' );
		wp_enqueue_style( 'rating-form-icons', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'icons.css', __FILE__ ), array(), Rating_Form::PLUGIN_VERSION );
		wp_enqueue_style( 'rating-form-admin', plugins_url( 'assets' . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'admin.css', __FILE__ ), array(), Rating_Form::PLUGIN_VERSION );
		wp_enqueue_script( 'rating-form-admin-script', plugins_url('assets' . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR . 'admin.js', __FILE__), array('jquery'), Rating_Form::PLUGIN_VERSION, true );
		wp_localize_script( 'rating-form-admin-script', 'rating_form_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 'uploadurl' => $upload_dir['baseurl'], 'pluginversion' => Rating_Form::PLUGIN_VERSION ) );
	}

	//Admin menus
	function add_admin_menus() {
		global $wp_version;
		if ( $wp_version >= 3.8 ) {
			add_menu_page( __( 'Rating Form Results', 'rating-form' ), 'Rating Form', 'manage_options', Rating_Form::PAGE_RESULT_RATING_SLUG, Rating_Form::PAGE_RESULT_RATING_SLUG, 'dashicons-star-filled');
		} else {
			add_menu_page( __( 'Rating Form Results', 'rating-form' ), 'Rating Form', 'manage_options', Rating_Form::PAGE_RESULT_RATING_SLUG, Rating_Form::PAGE_RESULT_RATING_SLUG);
		}
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Rating Form Results', 'rating-form' ), __( 'Results', 'rating-form' ), 'manage_options', Rating_Form::PAGE_RESULT_RATING_SLUG, Rating_Form::PAGE_RESULT_RATING_SLUG);
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Rating Forms', 'rating-form' ), __( 'Forms', 'rating-form' ), 'manage_options', Rating_Form::PAGE_FORM_RATING_SLUG, Rating_Form::PAGE_FORM_RATING_SLUG);
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Rating Form Titles', 'rating-form' ), __( 'Titles', 'rating-form' ), 'manage_options', Rating_Form::PAGE_TITLES_RATING_SLUG, Rating_Form::PAGE_TITLES_RATING_SLUG);
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Rating Form Block IP List', 'rating-form' ), __( 'Block IP', 'rating-form' ), 'manage_options', Rating_Form::PAGE_BLOCK_IP_SLUG, Rating_Form::PAGE_BLOCK_IP_SLUG);
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Add New Rating Form', 'rating-form' ), __( 'Add New', 'rating-form' ), 'manage_options', Rating_Form::PAGE_NEW_RATING_SLUG, Rating_Form::PAGE_NEW_RATING_SLUG);
		add_submenu_page( Rating_Form::PAGE_RESULT_RATING_SLUG, __( 'Rating Form Tools', 'rating-form' ), __( 'Tools', 'rating-form' ), 'manage_options', Rating_Form::PAGE_TOOLS_RATING_SLUG, Rating_Form::PAGE_TOOLS_RATING_SLUG);
	}

	//Admin menus array
	public static function admin_menus($curpagename = '') {
		$menu = '';

		$pages = array (
			Rating_Form::PAGE_RESULT_RATING_SLUG => __( 'Results', 'rating-form' ),
			Rating_Form::PAGE_FORM_RATING_SLUG => __( 'Forms', 'rating-form' ),
			Rating_Form::PAGE_TITLES_RATING_SLUG => __( 'Titles', 'rating-form' ),
			Rating_Form::PAGE_BLOCK_IP_SLUG => __( 'Block IP', 'rating-form' ),
			Rating_Form::PAGE_NEW_RATING_SLUG => __( 'Add New', 'rating-form' ),
			Rating_Form::PAGE_TOOLS_RATING_SLUG => __( 'Tools', 'rating-form' )
		);

		$menu .= '<h2 class="rf_header_nav nav-tab-wrapper">';
			$menu .= '<span class="rf_logo"><strong>Rating</strong> Form</span>';

			$current_page = __( $curpagename, 'rating-form' );

			foreach ( $pages as $page_slug => $page_name ) {
				$active = isset( $_GET['page'] ) && $_GET['page'] == $page_slug ? ' nav-tab-active' : '';

				$menu .= '<a class="nav-tab' . $active . '" href="?page=' . $page_slug . '">' . (empty($active) || empty($curpagename) ? $page_name : $current_page) . '</a>';
			}

		$menu .= '</h2>';

		echo $menu;
	}

	//Tools filters
	public function tools_filters() {
		$tools_options = get_option(Rating_Form::PAGE_TOOLS_RATING_SLUG);

		if (!empty($tools_options['before_content']['content'])) {
			function rf_before_content_add($content){
				$tools_options = get_option(Rating_Form::PAGE_TOOLS_RATING_SLUG);

				if (empty($tools_options['before_content']['paragraph'])) {
					if (!empty($tools_options['before_content']['content'])) {
						return do_shortcode(stripslashes($tools_options['before_content']['content'])) . $content;
					}
				} else {
					$paragraphAfter = intval($tools_options['before_content']['paragraph']); //Display shortcode after paragraph
					$content = explode("</p>", $content);
					for ($i = 0; $i <count($content); $i++) {
						if ($i == $paragraphAfter) {
							echo do_shortcode(stripslashes($tools_options['before_content']['content']));
						}
						echo $content[$i] . "</p>";
					}
				}
			}
			add_filter('the_content', 'rf_before_content_add');
		}

		if (!empty($tools_options['after_content']['content'])) {
			function rf_after_content_add($content){
				$tools_options = get_option(Rating_Form::PAGE_TOOLS_RATING_SLUG);

				if (empty($tools_options['after_content']['paragraph'])) {
					if (!empty($tools_options['after_content']['content'])) {
						return $content . do_shortcode(stripslashes($tools_options['after_content']['content']));
					}
				} else {
					$paragraphAfter = intval($tools_options['after_content']['paragraph']); //Display shortcode after paragraph
					$content = explode("</p>", $content);
					for ($i = 0; $i <count($content); $i++) {
						if ($i == $paragraphAfter) {
							echo do_shortcode(stripslashes($tools_options['after_content']['content']));
						}
						echo $content[$i] . "</p>";
					}
				}
			}
			add_filter('the_content', 'rf_after_content_add');
		}

	}

	/**
	 * Get term without knowing it's taxonomy
	 *
	 * @uses type $wpdb
	 * @uses get_term()
	 * @param int|object $term
	 * @param string $output
	 * @param string $filter
	 */
	public static function get_term_by_id($term, $output = OBJECT, $filter = 'raw') {
		global $wpdb;
		$null = null;

		if ( empty($term) ) {
			$error = new WP_Error('invalid_term', __('Empty Term'));
			return $error;
		}

		$_tax = $wpdb->get_row( $wpdb->prepare( "SELECT t.* FROM $wpdb->term_taxonomy AS t WHERE t.term_id = %s LIMIT 1", $term) );
		$taxonomy = $_tax->taxonomy;

		return get_term($term, $taxonomy, $output, $filter);

	}

	//Form Types
	public static $totalFormTypes = 8;
	public static function form_types($type, $val, $diff = '') {
		if ($type == 0) {
			if ($val == 'i') {
				return 10;
			}

			if ($val == 'type') {
				return "star";
			}

			if ($val == 'class') {
				return "cyto-custom";
			}

			if ($val == 'name') {
				return __( 'Custom', 'rating-form' );
			}
		} else if ($type == 1) {
			if ($val == 'i') {
				return 10;
			}

			if ($val == 'type') {
				return "star";
			}

			if ($val == 'class') {
				return "cyto-star";
			}

			if ($val == 'name') {
				return __( 'Star', 'rating-form' );
			}

			if ($val == 'css_color') {
				return "#ffd700";
			}

			if ($val == 'css_color_hover') {
				return "#ff7f00";
			}
		} else if ($type == 2) {
			if ($val == 'i') {
				return 2;
			}

			if ($val == 'type') {
				return "tud";
			}

			if ($val == 'class') {
				return "cyto-thumbs-up";
			}

			if ($val == 'class2') {
				return "cyto-thumbs-down";
			}

			if ($val == 'name') {
				return __( 'Thumbs Up &amp; Down', 'rating-form' );
			}

			if ($val == 'name_one') {
				return __( 'Thumbs Up', 'rating-form' );
			}

			if ($val == 'name_two') {
				return __( 'Thumbs Down', 'rating-form' );
			}
		} else if ($type == 3) {
			if ($val == 'i') {
				return 5;
			}

			if ($val == 'type') {
				return "star";
			}

			if ($val == 'class') {
				return "cyto-smiley-4";
			}

			if ($diff == 'int') {
				return "cyto-smiley-";
			}

			if ($val == 'name') {
				return __( 'Smiley', 'rating-form' );
			}

			if ($val == 'css_color') {
				return "#0074a2";
			}

			if ($val == 'css_color_hover') {
				return "#224e66";
			}
		} else if ($type == 4) {
			if ($val == 'i') {
				return 10;
			}

			if ($val == 'type') {
				return "star";
			}

			if ($val == 'class') {
				return "cyto-heart";
			}

			if ($val == 'name') {
				return __( 'Heart', 'rating-form' );
			}

			if ($val == 'css_color') {
				return "#ff0000";
			}

			if ($val == 'css_color_hover') {
				return "#af0000";
			}
		} else if ($type == 5) {
			if ($val == 'i') {
				return 2;
			}

			if ($val == 'type') {
				return "tud";
			}

			if ($val == 'class') {
				return "cyto-plus";
			}

			if ($val == 'class2') {
				return "cyto-min";
			}

			if ($val == 'name') {
				return __( 'Plus and Min', 'rating-form' );
			}

			if ($val == 'name_one') {
				return __( 'Plus', 'rating-form' );
			}

			if ($val == 'name_two') {
				return __( 'Min', 'rating-form' );
			}
		} else if ($type == 6) {
			if ($val == 'i') {
				return 10;
			}

			if ($val == 'type') {
				return "star";
			}

			if ($val == 'class') {
				return "cyto-circle";
			}

			if ($val == 'name') {
				return __( 'Circle', 'rating-form' );
			}

			if ($val == 'css_color') {
				return "#0094FF";
			}

			if ($val == 'css_color_hover') {
				return "#2ac400";
			}
		} else if ($type == 7) {
			if ($val == 'i') {
				return 2;
			}

			if ($val == 'type') {
				return "tud";
			}

			if ($val == 'class') {
				return "cyto-thumbs-up-2";
			}

			if ($val == 'class2') {
				return "cyto-thumbs-down-2";
			}

			if ($val == 'name') {
				return __( 'Thumbs Up &amp; Down 2', 'rating-form' );
			}

			if ($val == 'name_one') {
				return __( 'Thumbs Up', 'rating-form' );
			}

			if ($val == 'name_two') {
				return __( 'Thumbs Down', 'rating-form' );
			}
		} else if ($type == 8) {
			if ($val == 'i') {
				return 2;
			}

			if ($val == 'type') {
				return "tud";
			}

			if ($val == 'class') {
				return "cyto-plus-2";
			}

			if ($val == 'class2') {
				return "cyto-min-2";
			}

			if ($val == 'name') {
				return __( 'Plus and Min 2', 'rating-form' );
			}

			if ($val == 'name_one') {
				return __( 'Plus', 'rating-form' );
			}

			if ($val == 'name_two') {
				return __( 'Min', 'rating-form' );
			}
		} else {
			return;
		}
	}

	//Load plugin textdomain.
	function load_textdomain() {
		load_plugin_textdomain( 'rating-form', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	//Get Plugin Version
	function db_version_check() {
		$current_version = get_option( Rating_Form::OPTION_VERSION );
		if ($current_version == Rating_Form::PLUGIN_VERSION) {
			return true;
		} else {
			return false;
		}
	}

	//Admin Important Messages
	function display_admin_messages() {
		if (Rating_Form::db_version_check() == false) {
	?>
		<div class="error">
			<p><?php _e( 'Rating Form needs to be reactivated! Database must be updated.', 'rating-form' ); ?></p>
		</div>
	<?php
		}
	}

	// Activate the plugin
	public static function activate() {
		global $wpdb;

		// Create rating form table
		$sql_rating_form = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM . ' (
			form_id bigint(20) NOT NULL AUTO_INCREMENT,
			form_name varchar(50) NOT NULL,
			active int(11) NOT NULL DEFAULT 1,
			date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			type int(11) NOT NULL DEFAULT 1,
			max int(11) NOT NULL DEFAULT 5,
			restrict_ip int(11) NOT NULL DEFAULT 1,
			user_logged_in int(11) NOT NULL,
			ajax_load int(11) NOT NULL,
			rich_snippet int(11) NOT NULL,
			spinner int(11) NOT NULL DEFAULT 1,
			round int(11) NOT NULL DEFAULT 1,
			rtl int(11) NOT NULL,
			limitation int(11) NOT NULL DEFAULT 1,
			time bigint(20) NOT NULL,
			display text NOT NULL,
			txt_ty text NOT NULL,
			txt_rated text NOT NULL,
			txt_login text NOT NULL,
			txt_limit text NOT NULL,
			txt_edit_rating text NOT NULL,
			PRIMARY KEY  (form_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

		// Create rating table
		$sql_rating_rated = "CREATE TABLE " . $wpdb->prefix . Rating_Form::TBL_RATING_RATED . " (
			rate_id bigint(20) NOT NULL AUTO_INCREMENT,
			post_id bigint(20) NOT NULL,
			comment_id bigint(20) NOT NULL,
			custom_id varchar(20) NOT NULL DEFAULT '0',
			user_id bigint(20) NOT NULL,
			term_id bigint(20) NOT NULL,
			ip varchar(50) NOT NULL,
			rated varchar(11) NOT NULL,
			user bigint(20) NOT NULL,
			date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (rate_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		// Create titles table
		$sql_rating_titles = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . ' (
			title_id bigint(20) NOT NULL AUTO_INCREMENT,
			text text NOT NULL,
			position int(11) NOT NULL,
			PRIMARY KEY  (title_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

		// Create form block ip table
		$sql_rating_block_ip = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP . ' (
			ip varchar(50) NOT NULL,
			date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
			reason text NOT NULL,
			PRIMARY KEY  (ip)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		// Create form titles table
		$sql_rating_titles_form = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES . ' (
			title_form_id bigint(20) NOT NULL,
			title_id bigint(20) NOT NULL,
			position int(11) NOT NULL,
			PRIMARY KEY  (title_form_id,title_id)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		// Create post types table
		$sql_rating_post_types = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES . ' (
			post_type_form_id bigint(20) NOT NULL,
			post_type varchar(20) NOT NULL,
			PRIMARY KEY  (post_type_form_id,post_type)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		// Create user roles table
		$sql_rating_user_roles = 'CREATE TABLE '. $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES . ' (
			user_role_form_id bigint(20) NOT NULL,
			user_role varchar(50) NOT NULL,
			PRIMARY KEY  (user_role_form_id,user_role)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;';

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql_rating_form );
		dbDelta( $sql_rating_rated );
		dbDelta( $sql_rating_titles );
		dbDelta( $sql_rating_block_ip );
		dbDelta( $sql_rating_titles_form );
		dbDelta( $sql_rating_post_types );
		dbDelta( $sql_rating_user_roles );

		// Update Plugin Version (Add if not exists)
		update_option( Rating_Form::OPTION_VERSION, Rating_Form::PLUGIN_VERSION );

		// Insert titles table
		$wpdb->query("INSERT IGNORE INTO ". $wpdb->prefix . Rating_Form::TBL_RATING_TITLES . " (title_id, text, position) VALUES
			(1, 'Very bad!', 1),
			(2, 'Bad', 2),
			(3, 'Hmmm', 3),
			(4, 'Oke', 4),
			(5, 'Good!', 5),
			(6, 'Very good!', 6),
			(7, 'Cool!', 7),
			(8, 'Excellent!', 8),
			(9, 'Awesome!', 9),
			(10, 'Spectaculair!', 10),
			(11, 'Like!', 1),
			(12, 'Dislike!', 2);");

		// Create plugin folder inside uploads
		$upload_dir = wp_upload_dir();
		wp_mkdir_p( $upload_dir['basedir'].'/rating-form/icons/' );
		$iconsFile = fopen($upload_dir['basedir'].'/rating-form/icons/index.html', "w");
		fclose($iconsFile);
		wp_mkdir_p( $upload_dir['basedir'].'/rating-form/css/' );
		$cssFile = fopen($upload_dir['basedir'].'/rating-form/css/index.html', "w");
		fclose($cssFile);
	}

	//Deactivate the plugin
	public static function uninstall() {
		// Drop tables
		global $wpdb;

		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_ADD_FORM );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_RATED );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_TITLES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_BLOCK_IP );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_FORM_TITLES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_POST_TYPES );
		$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . Rating_Form::TBL_RATING_USER_ROLES );

		// Delete post meta
		delete_post_meta_by_key( 'rf_average_result' );
		delete_post_meta_by_key( 'rf_votes_up' );
		delete_post_meta_by_key( 'rf_votes_down' );
		delete_post_meta_by_key( 'rf_total_votes' );
		delete_post_meta_by_key( 'rf_real_average' );
		delete_post_meta_by_key( 'rf_real_up' );
		delete_post_meta_by_key( 'rf_real_down' );

		// Delete options
		delete_option( Rating_Form::OPTION_VERSION );
		delete_option( Rating_Form::PAGE_TOOLS_RATING_SLUG );
	}

}

//Activate/Install
register_activation_hook(__FILE__, array( 'Rating_Form', 'activate'));
//Deactivate/Uninstall
register_uninstall_hook(__FILE__, array( 'Rating_Form', 'uninstall'));
//Initialize Plugin
Rating_Form::init();
