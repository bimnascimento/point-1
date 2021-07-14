<?php
/*
Plugin Name: WooCommerce Frontend Shop Manager
Plugin URI: http://www.mihajlovicnenad.com/woocommerce-frontend-shop-manager
Description:  WooCommerce Frontend Shop Manager! - mihajlovicnenad.com
Author: Mihajlovic Nenad
Version: 3.1.1
Author URI: http://www.mihajlovicnenad.com
*/

class WC_Frontnend_Shop_Manager {

	public static $path;
	public static $url_path;
	public static $settings;

	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	function __construct() {

		if ( !class_exists('Woocommerce') || !current_user_can('edit_products') ) {
			return;
		}

		self::$settings['mode'] = get_option( 'wc_settings_wfsm_mode', 'wfsm_mode_logo' );
		self::$settings['style'] = get_option( 'wc_settings_wfsm_style', 'wfsm_style_default' );
		self::$settings['logo'] = get_option( 'wc_settings_wfsm_logo', '' );
		self::$settings['hidden'] = get_option( 'wc_settings_wfsm_show_hidden_products', 'yes' );
		self::$settings['groups'] = get_option( 'wc_settings_wfsm_vendor_groups', array() );
		self::$settings['user'] = wp_get_current_user();
		self::$settings['woocommerce']['decimal_sep'] = get_option( 'woocommerce_price_decimal_sep' );

		if ( self::$settings['user']->has_cap( 'administrator' ) || self::$settings['user']->has_cap( 'manage_woocommerce' ) ) {
			self::$settings['user_permissions'] = array();
			self::$settings['admin_mode'] = true;
		}
		else {

			if ( !empty( self::$settings['groups'] ) ) {

				$curr_group = get_user_meta( self::$settings['user']->ID, 'wfsm_group', true );

				if ( $curr_group !== '' && array_key_exists( $curr_group, self::$settings['groups'] ) ) {
					self::$settings['user_permissions'] = self::$settings['groups'][$curr_group]['permissions'];
				}
				else {
					self::$settings['user_permissions'] = get_option( 'wc_settings_wfsm_default_permissions', array() );
				}

			}
			else {
				self::$settings['user_permissions'] = get_option( 'wc_settings_wfsm_default_permissions', array() );
			}

		}

		self::$path = plugin_dir_path( __FILE__ );
		self::$url_path = plugins_url( __FILE__ );

		add_action( 'init', array(&$this, 'wfsm_textdomain'), 1000 );
		add_action( 'wp_enqueue_scripts', array(&$this, 'wfsm_scripts') );

		self::$settings['wc_settings_wfsm_archive_action'] = get_option( 'wc_settings_wfsm_archive_action', '' );
		if ( self::$settings['wc_settings_wfsm_archive_action'] == '' ) {
			self::$settings['wc_settings_wfsm_archive_action'] = 'woocommerce_before_shop_loop_item';
		}

		self::$settings['wc_settings_wfsm_single_action'] = get_option( 'wc_settings_wfsm_single_action', '' );
		if ( self::$settings['wc_settings_wfsm_single_action'] == '' ) {
			self::$settings['wc_settings_wfsm_single_action'] = 'woocommerce_before_single_product_summary';
		}

		add_action( self::$settings['wc_settings_wfsm_archive_action'], array(&$this, 'wfsm_content') );
		add_action( self::$settings['wc_settings_wfsm_single_action'], array(&$this, 'wfsm_content'), 5 );

		add_action( 'wp_ajax_wfsm_respond', array(&$this, 'wfsm_respond') );
		add_action( 'wp_ajax_wfsm_save', array(&$this, 'wfsm_save') );

		if ( self::$settings['hidden'] == 'yes' ) {
			add_filter( 'pre_get_posts', array(&$this, 'wfsm_query' ), 1000000, 1);
		}

		$wfsm_permission = array( 'create_simple_product', 'create_grouped_product', 'create_external_product', 'create_variable_product', );
		if ( count ( array_intersect( self::$settings['user_permissions'], $wfsm_permission ) ) < 4 ) {
			add_action( 'wp_footer', array(&$this, 'wfsm_add_product') );
			add_action( 'wp_ajax_wfsm_add_product_respond', array(&$this, 'wfsm_add_product_respond') );
		}

		if ( !in_array( 'product_content' , self::$settings['user_permissions'] ) ) {
			add_action( 'wp_ajax_wfsm_editor', array(&$this, 'wfsm_editor') );
			add_action( 'wp_ajax_wfsm_editor_save', array(&$this, 'wfsm_editor_save') );
		}

		if ( !in_array( 'variable_add_variations' , self::$settings['user_permissions'] ) ) {
			add_action( 'wp_ajax_wfsm_add_variation_respond', array(&$this, 'wfsm_add_variation_respond') );
		}

		if ( !in_array( 'product_clone' , self::$settings['user_permissions'] ) ) {
			add_action( 'wp_ajax_wfsm_clone', array(&$this, 'wfsm_clone') );
		}
		
		if ( !in_array( 'product_delete' , self::$settings['user_permissions'] ) || !in_array( 'variable_delete' , self::$settings['user_permissions'] ) ) {
			add_action( 'wp_ajax_wfsm_trash', array(&$this, 'wfsm_trash') );
		}

		if ( !in_array( 'product_attributes' , self::$settings['user_permissions'] ) ) {
			add_action( 'wp_ajax_wfsm_create_attribute', array(&$this, 'wfsm_create_attribute') );
		}

	}

	public static function wfsm_get_path() {
		return plugin_dir_path( __FILE__ );
	}

	function wfsm_textdomain() {
		$dir = trailingslashit( WP_LANG_DIR );
		load_plugin_textdomain( 'wfsm', false, $dir . 'plugins' );
	}

	function wfsm_scripts() {
		wp_enqueue_style( 'jquery-ui', plugins_url( 'assets/css/jquery_ui.css', __FILE__) );
		wp_enqueue_style( 'wfsm-style', plugins_url( 'assets/css/' . self::$settings['style'] . '.css', __FILE__) );

		wp_register_script( 'wfsm-selectize', plugins_url( 'assets/js/selectize.min.js', __FILE__), array( 'jquery' ), '3.1.1', true );
		wp_register_script( 'wfsm-scripts', plugins_url( 'assets/js/scripts.js', __FILE__), array( 'jquery' ), '3.1.1', true );
		wp_register_script( 'wfsm-init', plugins_url( 'assets/js/scripts-init.js', __FILE__), array( 'jquery' ), '3.1.1', false );
		wp_enqueue_media();
		wp_enqueue_script( array( 'wfsm-init', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'wfsm-selectize', 'wfsm-scripts' ) );

		$curr_args = array(
			'ajax' => admin_url( 'admin-ajax.php' ),
			'decimal_separator' => self::$settings['woocommerce']['decimal_sep'],
			'localization' => array(
				'downloads' => array(
					'discard' => __( 'Delete File?', 'wfsm'),
					'file_name' => __( 'File Name', 'wfsm'),
					'choose_file_ui' => __( 'Choose file', 'wfsm'),
					'choose_file' => __( 'Choose File', 'wfsm')
				),
				'clone' => array(
					'question' => __( 'Duplicate product?', 'wfsm' ),
					'confirmed' => __( 'Product duplicated!', 'wfsm' )
				),
				'trash' => array(
					'simple' => __( 'Trash product?', 'wfsm' ),
					'variation' => __( 'Trash variation?', 'wfsm' )
				),
				'errors' => array(
					'decimal' => __( 'Use only numbers and the decimal separator!', 'wfsm' ) . ' ( ' . self::$settings['woocommerce']['decimal_sep'] . ' )',
				)
			)
		);

		wp_localize_script( 'wfsm-scripts', 'wfsm', $curr_args );
	}

	function wfsm_query( $query ) {

		global $prdctfltr_global;

		if ( is_admin() && ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			return;
		}
		else if ( !is_admin() && ( isset( $query->query['prdctfltr'] ) && $query->query['prdctfltr'] == 'active' ) !== false ) {
			$pf_mode = 'shortcode';
		}
		else if ( !is_admin() && $query->is_main_query() && ( $query->is_tax( get_object_taxonomies( 'product' ) ) || $query->is_post_type_archive( 'product' ) || ( isset( $query->query_vars['page_id'] ) && $query->query_vars['page_id'] == ( self::wfsm_wpml_get_id( wc_get_page_id( 'shop' ) ) ) ) ) ) {
			$pf_mode = 'archive';
		}
		else if ( isset( $prdctfltr_global['ajax'] ) && ( defined('DOING_AJAX') && DOING_AJAX ) ) {
			$pf_mode = 'shortcode_ajax';
		}
		else { 
			return;
		}

		$query->set( 'post_status', apply_filters( 'wfsm_archive_post_statuses', array( 'publish', 'pending', 'draft' ) ) );

	}

	function wfsm_wpml_get_id( $post_id = 0, $type = 'post_product' ){
		global $post, $sitepress;

		if ( empty( $post ) || empty( $sitepress ) ) {
			return 0;
		}

		$output = array();

		$p_ID = $post_id == 0 ? $post->ID : $post_id;

		$el_trid = $sitepress->get_element_trid( $p_ID, $type );

		$el_translations = $sitepress->get_element_translations( $el_trid, $type );

		if( !empty( $el_translations ) ) {
			$is_original = FALSE;
			foreach( $el_translations as $lang => $details ) {
				if( $details->original == 1 && $details->element_id == $p_ID ) {
					$is_original = TRUE;
				}
				if( $details->original == 1 ) {
					$original_ID = $details->element_id;
				}
			}
			$output['is_original'] = $is_original;
			$output['original_ID'] = $original_ID;
		}
		else {
			$output['original_ID'] = $p_ID;
		}

		
		return isset( $output['original_ID'] ) ? absint( $output['original_ID'] ) : $p_ID;
	}

	function wfsm_add_product() {

		if ( !is_woocommerce() ) {
			return;
		}

	?>
		<div class="wfsm-buttons wfsm-side-buttons">
			<a href="#" class="wfsm-button wfsm-add-product" title="<?php _e( 'Add New Product', 'wfsm' ); ?>"><i class="wfsmico-activate"></i></a>
			<a href="<?php echo esc_url( admin_url( 'post.php?action=edit&post=' ) ); ?>" class="wfsm-button wfsm-edit" title="<?php _e( 'Edit product in the backend', 'wfsm' ); ?>"><i class="wfsmico-edit"></i></a>
			<a href="#" class="wfsm-button wfsm-save" title="<?php _e( 'Save changes', 'wfsm' ); ?>"><i class="wfsmico-save"></i></a>
			<a href="#" class="wfsm-button wfsm-discard" title="<?php _e( 'Discard changes', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a>
			<div class="wfsm-add-product-type">
		<?php
			if ( !in_array( 'create_simple_product' , self::$settings['user_permissions'] ) ) {
			?>
				<a href="#" class="wfsm-product-type" data-type="simple"><?php _e( 'Simple', 'wfsm' ); ?></a>
			<?php
			}

			if ( !in_array( 'create_grouped_product' , self::$settings['user_permissions'] ) ) {
			?>
				<a href="#" class="wfsm-product-type" data-type="grouped"><?php _e( 'Grouped', 'wfsm' ); ?></a>
			<?php
			}

			if ( !in_array( 'create_external_product' , self::$settings['user_permissions'] ) ) {
			?>
				<a href="#" class="wfsm-product-type" data-type="external"><?php _e( 'External', 'wfsm' ); ?></a>
			<?php
			}

			if ( !in_array( 'create_variable_product' , self::$settings['user_permissions'] ) ) {
			?>
				<a href="#" class="wfsm-product-type" data-type="variable"><?php _e( 'Variable', 'wfsm' ); ?></a>
			<?php
			}

			if ( !in_array( 'create_custom_product' , self::$settings['user_permissions'] ) ) {
				$curr_terms = get_terms( 'product_type');
				foreach ( $curr_terms as $k => $v ) {
					if ( !in_array( $v->slug, array( 'simple', 'grouped', 'external', 'variable' ) ) ) {
					?>
						<a href="#" class="wfsm-product-type"  data-type="<?php echo $v->slug; ?>"><?php echo ucfirst( $v->name ); ?></a>
					<?php
					}
				}
			}
		?>
			</div>
		</div>
	<?php
	}


	function wfsm_content() {

		global $post, $woocommerce_loop;

		$curr_id = absint( ( class_exists('SitePress') ? self::wfsm_wpml_get_id() : $post->ID ) );

		if ( !isset( self::$settings['admin_mode'] ) && absint( $post->post_author ) !== self::$settings['user']->ID ) {
			return;
		}

		$add_loop = ( ( isset( $woocommerce_loop['columns'] ) && empty( $woocommerce_loop['columns'] ) ) || !isset( $woocommerce_loop['columns'] ) || !isset( $woocommerce_loop['loop'] ) ? 'single' : $woocommerce_loop['loop'] . '|' . $woocommerce_loop['columns'] );

		$buttons_class = ( !in_array( 'product_clone' , self::$settings['user_permissions'] ) ? '' : 'wfsm-clone-disabled' );
		$buttons_class .= ( !in_array( 'product_delete' , self::$settings['user_permissions'] ) ? '' : ' wfsm-delete-disabled' );

	?>
		<div class="wfsm-buttons wfsm-top-buttons <?php echo $buttons_class; ?>" data-id="<?php echo $curr_id; ?>" data-loop="<?php echo $add_loop; ?>">
			<a href="#" class="wfsm-button wfsm-activate" title="<?php _e( 'Quick edit product', 'wfsm' ); ?>"><i class="wfsmico-activate"></i></a>
			<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $curr_id . '&action=edit' ) ); ?>" class="wfsm-button wfsm-edit" title="<?php _e( 'Edit product in the backend', 'wfsm' ); ?>"><i class="wfsmico-edit"></i></a>
		<?php
			if ( !in_array( 'product_clone' , self::$settings['user_permissions'] ) ) {
		?>
			<a href="#" class="wfsm-button wfsm-clone" title="<?php _e( 'Duplicate product', 'wfsm' ); ?>"><i class="wfsmico-clone"></i></a>
		<?php
			}
			if ( !in_array( 'product_delete' , self::$settings['user_permissions'] ) ) {
		?>
			<a href="#" class="wfsm-button wfsm-trash" title="<?php _e( 'Delete product', 'wfsm' ); ?>"><i class="wfsmico-trash"></i></a>
		<?php
			}
		?>
			<a href="#" class="wfsm-button wfsm-save" title="<?php _e( 'Save changes', 'wfsm' ); ?>"><i class="wfsmico-save"></i></a>
			<a href="#" class="wfsm-button wfsm-discard" title="<?php _e( 'Discard changes', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a>
			<span class="wfsm-show-post-status">
				<span>
				<?php
					switch ( $post_status = get_post_status( $curr_id ) ) :
					case 'publish' :
						_e( 'Published', 'wfsm' );
					break;
					case 'draft' :
						_e( 'Draft', 'wfsm' );
					break;
					case 'pending' :
						_e( 'Pending', 'wfsm' );
					break;
					default :
						echo $post_status;
					break;
					endswitch;
				?>
				</span>
			</span>
			<span class="wfsm-editing">
				<img width="64" height="64" src="<?php echo esc_url( plugins_url( 'assets/images/editing.png', __FILE__ ) ); ?>" />
				<small>
					<?php _e( 'Currently Editing', 'wfsm' ) ; ?><br/>
					<?php _e( 'Tap to Save', 'wfsm' ) ; ?>
				</small>
			</span>
		</div>
	<?php

	}

	function wfsm_add_product_respond() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		$curr_create = array(
			'post_title'     => __( 'New Product', 'wfsm' ),
			'post_content'   => '',
			'post_name'      => 'new-product',
			'post_status'    => get_option( 'wc_settings_wfsm_create_status', 'pending' ),
			'post_type'      => 'product',
			'comment_status' => get_option('default_comment_status'),
			'ping_status'    => get_option('default_ping_status'),
			'post_author'    => get_current_user_id()
		);

		$curr_dummy = wp_insert_post( $curr_create );

		$product_type = empty( $_POST['wfsm_type'] ) ? 'simple' : sanitize_title( stripslashes( $_POST['wfsm_type'] ) );

		wp_set_object_terms( $curr_dummy, $product_type, 'product_type' );
		update_post_meta( $curr_dummy, '_visibility', 'visible' );
		update_post_meta( $curr_dummy, '_sku', '' );
		update_post_meta( $curr_dummy, '_stock_status', 'instock' );
		update_post_meta( $curr_dummy, '_total_sales', '0' );

		$is_virtual = get_option( 'wc_settings_wfsm_create_virtual', 'no' ) == 'yes' ? 'yes' : 'no';
		update_post_meta( $curr_dummy, '_virtual', $is_virtual );

		$is_downloadable = get_option( 'wc_settings_wfsm_create_downloadable', 'no' ) == 'yes' ? 'yes' : 'no';;
		update_post_meta( $curr_dummy, '_downloadable', $is_downloadable );

		update_post_meta( $curr_dummy, '_featured', 'no' );

		die( (string) $curr_dummy );
		exit;
	}

	function wfsm_respond() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = absint( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$product = wc_get_product( $curr_post_id );

		if ( $product->is_type( 'simple' ) ) {
			$product_type_class = ' wfsm-simple-product';
			$product_information = __( 'Simple Product', 'wfsm' ) . '<br/> #ID ' . $curr_post_id;
		}
		else if ( $product->is_type( 'variable' ) ) {
			$product_type_class = ' wfsm-variable-product';
			$product_information = __( 'Variable Product', 'wfsm' ) . '<br/> #ID ' . $curr_post_id;
		}
		else if ( $product->is_type( 'external' ) ) {
			$product_type_class = ' wfsm-external-product';
			$product_information = __( 'External Product', 'wfsm' ) . '<br/> #ID ' . $curr_post_id;
		}
		else if ( $product->is_type( 'grouped' ) ) {
			$product_type_class = ' wfsm-grouped-product';
			$product_information = __( 'Grouped Product', 'wfsm' ) . '<br/> #ID ' . $curr_post_id;
		}
		else {
			$product_type_class = ' wfsm-' . $product->product_type() . '-product';
			$product_information = __( 'Product', 'wfsm' ) . ' #ID ' . $curr_post_id;
		}

		ob_start();

	?>
		<div class="wfsm-quick-editor">
			<div class="wfsm-screen<?php echo $product_type_class; ?>">
				<div class="wfsm-controls">
					<div class="wfsm-about">
				<?php
					if ( self::$settings['mode'] == 'wfsm_mode_user' ) {
						global $current_user; get_currentuserinfo();
						echo get_avatar( $current_user->user_email, 100 ); ?>
						<?php echo $current_user->user_login . ', ' . __('welcome back!', 'wfsm' ); ?>
						<small><?php echo $product_information; ?></small>
					<?php
					}
					else {
						if ( self::$settings['logo'] == '' ) {
					?>
						<img width="50" height="50" src="<?php echo plugins_url( 'assets/images/about.png', __FILE__ ); ?>" />
					<?php
						}
						else {
					?>
						<img width="50" height="50" src="<?php echo esc_url( self::$settings['logo'] ); ?>" />
					<?php
						}
					?>

						<?php bloginfo('name'); ?>
						<small><?php echo $product_information; ?></small>
					<?php
					}
				?>
					</div>
					<span class="wfsm-expand"><i class="wfsmico-expand"></i></span>
					<span class="wfsm-contract"><i class="wfsmico-contract"></i></span>
					<span class="wfsm-side-edit"><i class="wfsmico-edit"></i></span>
					<span class="wfsm-side-save"><i class="wfsmico-save"></i></span>
					<span class="wfsm-side-discard"><i class="wfsmico-discard"></i></span>
					<div class="wfsm-clear"></div>
				</div>
				<span class="wfsm-headline wfsm-headline-content"><?php _e( 'Product Content', 'wfsm' ); ?></span>
				<div class="wfsm-group wfsm-group-content">
				<?php
					if ( !in_array( 'product_status' , self::$settings['user_permissions'] ) ) {
				?>
					<label for="wfsm-product-status" class="wfsm-label-status">
					<?php
						$wfsm_selected = ( ( $post_status = get_post_status( $curr_post_id ) ) ? $post_status : '' );
					?>
						<span><?php _e( 'Product Status', 'wfsm' ); ?></span>
						<select id="wfsm-product-status" name="wfsm-product-status" class="wfsm-collect-data">
						<?php
							$wfsm_select_options = apply_filters( 'wfsm_edit_post_status', array(
								'publish' => __( 'Published', 'wfsm' ),
								'draft'   => __( 'Draft', 'wfsm' ),
								'pending' => __( 'Pending', 'wfsm' ),
								'trash'   => __( 'Trash', 'wfsm' )
							) );
							foreach ( $wfsm_select_options as $wk => $wv ) {
						?>
								<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
						<?php
							}
						?>
						</select>
					</label>
				<?php
					}
					if ( !in_array( 'product_feature' , self::$settings['user_permissions'] ) ) {
						$is_featured = ( $featured = get_post_meta( $curr_post_id, '_featured', true ) ) ? $featured : 'no';
					?>
						<label for="wfsm-featured" class="wfsm-label-checkbox wfsm-<?php echo $is_featured; ?>">
							<span class="wfsm-show-yes"><?php _e( 'Product is Featured', 'wfsm' ); ?></span>
							<span class="wfsm-show-no"><?php _e( 'Product is not Featured', 'wfsm' ); ?></span>
							<input id="wfsm-featured" name="wfsm-featured" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_featured; ?>"/>
						</label>
				<?php
					}
					if ( !in_array( 'product_name' , self::$settings['user_permissions'] ) ) {
				?>
					<label for="wfsm-product-name">
						<span><?php _e( 'Product Name', 'wfsm' ); ?></span>
						<input id="wfsm-product-name" name="wfsm-product-name" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo get_the_title($curr_post_id); ?>"/>
					</label>
				<?php
					}
					if ( !in_array( 'product_slug' , self::$settings['user_permissions'] ) ) {
				?>
					<label for="wfsm-product-slug">
						<span><?php _e( 'Product Slug', 'wfsm' ); ?></span>
						<input id="wfsm-product-slug" name="wfsm-product-slug" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo self::utf8_urldecode( $product->post->post_name ); ?>"/>
					</label>
				<?php
					}
					if ( !in_array( 'product_content' , self::$settings['user_permissions'] ) ) {
				?>
					<span class="wfsm-dummy-title"><?php _e( 'Edit Product', 'wfsm' ); ?></span>
					<a href="#" class="wfms-plain-button wfsm-edit-content"><?php _e( 'Product Content', 'wfsm' ); ?></a>
					<a href="#" class="wfms-plain-button wfsm-edit-desc"><?php _e( 'Product Description', 'wfsm' ); ?></a>
				<?php
					}
					if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) ) {
				?>
					<span class="wfsm-dummy-title"><?php _e( 'Featured Image', 'wfsm' ); ?></span>
					<label for="wfsm-featured-image" class="wfsm-featured-image">
						<a href="#" class="wfsm-featured-image-trigger">
						<?php
							if ( has_post_thumbnail( $curr_post_id ) ) {
								$curr_image = wp_get_attachment_image_src( $curr_image_id = get_post_thumbnail_id( $curr_post_id ), 'thumbnail' );
							?>
								<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
							<?php
							}
							else {
								$curr_image_id = 0;
							?>
								<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>" />
						<?php
							}
						?>
						</a>
						<input id="wfsm-featured-image" name="wfsm-featured-image" class="wfsm-collect-data" type="hidden" value="<?php echo $curr_image_id; ?>" />
					</label>
					<div class="wfsm-featured-image-controls">
						<a href="#" class="wfsm-editor-button wfsm-change-image"><?php _e( 'Change Image', 'wfsm' ); ?></a>
						<a href="#" class="wfsm-editor-button wfsm-remove-image"><?php _e( 'Discard Image', 'wfsm' ); ?></a>
					</div>
					<div class="wfsm-clear"></div>
				<?php
					}

					if ( !in_array( 'product_gallery' , self::$settings['user_permissions'] ) ) {
						$product_gallery = ( $gallery = get_post_meta( $curr_post_id, '_product_image_gallery', true ) ) ? $gallery : '';
					?>
						<span class="wfsm-dummy-title"><?php _e( 'Product Gallery', 'wfsm' ); ?></span>
						<div class="wfsm-product-gallery-images">
						<?php
							$curr_gallery = ( strpos( $product_gallery , ',' ) !== false ? explode( ',', $product_gallery ) : array( $product_gallery ) );
							foreach( $curr_gallery as $img_id ) {
								if ( $img_id == '' ) continue;
								$curr_image = wp_get_attachment_image_src( $img_id, 'thumbnail' );
							?>
								<span class="wfsm-product-gallery-image" data-id="<?php echo $img_id; ?>">
									<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
									<a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a>
								</span>
						<?php
							}
						?>
							<div class="wfsm-clear"></div>
						</div>
						<label for="wfsm-product-gallery" class="wfsm-product-gallery">
							<a href="#" class="wfsm-editor-button wfsm-add-gallery-image"><?php _e( 'Add Image', 'wfsm' ); ?></a>
							<select id="wfsm-product-gallery" name="wfsm-product-gallery" class="wfsm-reset-this wfsm-collect-data" multiple="multiple">
						<?php
								foreach( $curr_gallery as $img_id ) {
							?>
									<option value="<?php echo $img_id; ?>" selected="selected"><?php echo $img_id; ?></option>
							<?php
								}
						?>
							</select>
						</label>
					<?php
					}
				?>
				</div>
				<span class="wfsm-headline wfsm-headline-data"><?php _e( 'Product Data', 'wfsm' ); ?></span>
				<div class="wfsm-group wfsm-group-data">
				<?php
					if ( !in_array( 'external_product_url' , self::$settings['user_permissions'] ) && $product->is_type( 'external' ) ) {
						$product_http = ( $product_http_meta = get_post_meta( $curr_post_id, '_product_url', true ) ) ? $product_http_meta : '';
				?>
						<label for="wfsm-product-http">
							<span><?php _e( 'Product External URL', 'wfsm' ); ?></span>
							<input id="wfsm-product-http" name="wfsm-product-http" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_http; ?>"/>
						</label>
				<?php
					}
					if ( !in_array( 'external_button_text' , self::$settings['user_permissions'] ) && $product->is_type( 'external' ) ) {
						$product_button = ( $product_button_text = get_post_meta( $curr_post_id, '_button_text', true ) ) ? $product_button_text : '';
				?>
						<label for="wfsm-button-text">
							<span><?php _e( 'Product Button Text', 'wfsm' ); ?></span>
							<input id="wfsm-button-text" name="wfsm-button-text" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_button; ?>"/>
						</label>
				<?php
					}
					if ( $product->is_type( 'simple' ) ) {
						if ( !in_array( 'product_virtual' , self::$settings['user_permissions'] ) ) {
							$is_virtual = ( $virtual = get_post_meta( $curr_post_id, '_virtual', true ) ) ? $virtual : 'no';
						?>
							<label for="wfsm-virtual" class="wfsm-label-checkbox wfsm-<?php echo $is_virtual; ?>" data-linked="shipping">
								<span class="wfsm-show-yes"><?php _e( 'Product is Virtual', 'wfsm' ); ?></span>
								<span class="wfsm-show-no"><?php _e( 'Product is not Virtual', 'wfsm' ); ?></span>
								<input id="wfsm-virtual" name="wfsm-virtual" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_virtual; ?>"/>
							</label>
						<?php
						}
						if ( !in_array( 'product_downloadable' , self::$settings['user_permissions'] ) ) {
							$is_downloadable = ( $downloadable = get_post_meta( $curr_post_id, '_downloadable', true ) ) ? $downloadable : 'no';
						?>
							<label for="wfsm-downloadable" class="wfsm-label-checkbox wfsm-<?php echo $is_downloadable; ?>" data-linked="downloads">
								<span class="wfsm-show-yes"><?php _e( 'Product is Downloadable', 'wfsm' ); ?></span>
								<span class="wfsm-show-no"><?php _e( 'Product is not Downloadable', 'wfsm' ); ?></span>
								<input id="wfsm-downloadable" name="wfsm-downloadable" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_downloadable; ?>"/>
							</label>
						<?php
						}
					}
					if ( !in_array( 'product_sku' , self::$settings['user_permissions'] ) && wc_product_sku_enabled() && !$product->is_type( 'grouped' ) ) {
				?>
					<label for="wfsm-sku">
						<span><?php _e( 'SKU', 'wfsm' ); ?></span>
						<input id="wfsm-sku" name="wfsm-sku" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_sku(); ?>" />
					</label>
				<?php
					}
					if ( !in_array( 'product_prices' , self::$settings['user_permissions'] ) && !$product->is_type( 'variable' ) && !$product->is_type( 'grouped' ) ) {
				?>
					<label for="wfsm-regular-price" class="wfsm-label-half wfsm-label-first">
						<span><?php _e( 'Regular Price', 'wfsm' ); ?></span>
						<input id="wfsm-regular-price" name="wfsm-regular-price" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_regular_price(); ?>"/>
					</label>
					<label for="wfsm-sale-price" class="wfsm-label-half">
						<span><?php _e( 'Sale Price', 'wfsm' ); ?></span>
						<input id="wfsm-sale-price" name="wfsm-sale-price" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product->get_sale_price(); ?>"/>
					</label>
					<div class="wfsm-clear"></div>
				<?php
					}
					if ( !in_array( 'product_taxes' , self::$settings['user_permissions'] ) && !$product->is_type( 'grouped' ) ) {

						$tax_status = array(
							'taxable' => __( 'Taxable', 'wfsm' ),
							'shipping' => __( 'Shipping', 'wfsm' ),
							'class' => __( 'None', 'wfsm' )
						);

						if ( !empty( $tax_status ) ) {
						?>
							<label for="wfsm-tax-status" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $status = get_post_meta( $curr_post_id, '_tax_status', true ) ) !== '' ? $status : '' );
							?>
								<span><?php _e( 'Tax Status', 'wfsm' ); ?></span>
								<select id="wfsm-tax-status" name="wfsm-tax-status" class="wfsm-collect-data">
								<?php
									foreach ( $tax_status as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						<?php
						}


						$tax_classes = WC_Tax::get_tax_classes();
						$classes_options = array();
						$classes_options[''] = __( 'Standard', 'wfsm' );

						if ( $tax_classes ) {
							foreach ( $tax_classes as $class ) {
								$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
							}
						}

						if ( !empty( $classes_options ) ) {
						?>
							<label for="wfsm-tax-class" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $class = get_post_meta( $curr_post_id, '_tax_class', true ) ) !== '' ? $class : '' );
							?>
								<span><?php _e( 'Tax Class', 'wfsm' ); ?></span>
								<select id="wfsm-tax-class" name="wfsm-tax-class" class="wfsm-collect-data">
								<?php
									foreach ( $classes_options as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						<?php
						}

					}

					if ( !in_array( 'product_sold_individually' , self::$settings['user_permissions'] ) && !$product->is_type( 'external' ) && !$product->is_type( 'grouped' ) ) {
						$sold_individually = ( $individually = get_post_meta( $curr_post_id, '_sold_individually', true ) ) ? $individually : 'no';
					?>
						<label for="wfsm-sold-individually" class="wfsm-label-checkbox wfsm-<?php echo $sold_individually; ?>">
							<span class="wfsm-show-yes"><?php _e( 'Product is Sold Individually', 'wfsm' ); ?></span>
							<span class="wfsm-show-no"><?php _e( 'Product is not Sold Individually', 'wfsm' ); ?></span>
							<input id="wfsm-sold-individually" name="wfsm-sold-individually" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $sold_individually; ?>"/>
						</label>
					<?php
					}

					$option_manage_stock = get_option( 'woocommerce_manage_stock' );
					if ( 'yes' == $option_manage_stock && !in_array( 'product_stock' , self::$settings['user_permissions'] ) && !$product->is_type( 'external' ) && !$product->is_type( 'grouped' ) ) {
						if ( !$product->is_type( 'variable' ) ) {
							$in_stock_status = ( $stock = get_post_meta( $curr_post_id, '_stock_status', true ) ) ? $stock : 'instock';
						?>
								<label for="wfsm-product-stock" class="wfsm-label-checkbox wfsm-<?php echo $in_stock_status; ?>">
									<span class="wfsm-show-instock"><?php _e( 'Product is In Stock', 'wfsm' ); ?></span>
									<span class="wfsm-show-outofstock"><?php _e( 'Product is currently Out of Stock', 'wfsm' ); ?></span>
									<input id="wfsm-product-stock" name="wfsm-product-stock" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $in_stock_status; ?>"/>
								</label>
							<?php
						}
						$wfsm_class = ( ( get_post_meta( $curr_post_id, '_manage_stock', true ) ) == 'yes' ? ' wfsm-visible' : ' wfsm-hidden' );
					?>
						<div class="wfsm-manage-stock-quantity<?php echo $wfsm_class; ?>">
							<label for="wfsm-stock-quantity" class="wfsm-label-quantity">
								<span><?php _e( 'Stock Quantity', 'wfsm' ); ?></span>
							<?php
								$stock_count = get_post_meta( $curr_post_id, '_stock', true );
							?>
								<input id="wfsm-stock-quantity" name="wfsm-stock-quantity" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo wc_stock_amount( $stock_count ); ?>" />
							</label>
							<label for="wfsm-backorders" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $backorders = get_post_meta( $curr_post_id, '_backorders', true ) ) ? $backorders : '' );
							?>
								<span><?php _e( 'Allow Backorders', 'wfsm' ); ?></span>
								<select id="wfsm-backorders" name="wfsm-backorders" class="wfsm-collect-data">
								<?php
									$wfsm_select_options = array(
										'no'     => __( 'Do not allow', 'wfsm' ),
										'notify' => __( 'Allow, but notify customer', 'wfsm' ),
										'yes'    => __( 'Allow', 'wfsm' )
									);
									foreach ( $wfsm_select_options as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						</div>
					<?php
						$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
					?>
						<a href="#" class="wfsm-editor-button wfsm-manage-stock-quantity<?php echo $wfsm_button_class; ?>"><?php _e( 'Manage Stock', 'wfsm' ); ?></a>
				<?php
					}
					if ( !in_array( 'product_schedule_sale' , self::$settings['user_permissions'] ) && !$product->is_type( 'variable' ) && !$product->is_type( 'grouped' ) ) {

						$sale_price_dates_from = ( $date_from = get_post_meta( $curr_post_id, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date_from ) : '';
						$sale_price_dates_to = ( $date_to = get_post_meta( $curr_post_id, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date_to ) : '';
						$wfsm_class = ( $sale_price_dates_from !== '' || $sale_price_dates_to !== '' ? ' wfsm-visible' : ' wfsm-hidden' );
					?>
						<div class="wfsm-schedule-sale<?php echo $wfsm_class; ?>">
							<label for="wfsm-schedule-sale-start" class="wfsm-label-half wfsm-label-first">
								<span><?php _e( 'Start Sale', 'wfsm' ); ?></span>
								<input id="wfsm-schedule-sale-start" name="wfsm-schedule-sale-start" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</label>
							<label for="wfsm-schedule-sale-end" class="wfsm-label-half">
								<span><?php _e( 'End Sale', 'wfsm' ); ?></span>
								<input id="wfsm-schedule-sale-end" name="wfsm-schedule-sale-end" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
							</label>
							<div class="wfsm-clear"></div>
							<script type="text/javascript">
								(function($){
									"use strict";
									var curr_date = new Date();
									var curr_dates = $('#wfsm-schedule-sale-start, #wfsm-schedule-sale-end').datepicker( {
										dateFormat: 'yy/mm/dd',
										defaultDate: "+1w",
										minDate: curr_date,
										onSelect: function(curr_selected) {
											var option = this.id == "wfsm-schedule-sale-start" ? "minDate" : "maxDate",
											instance = $(this).data("datepicker"),
											date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, curr_selected, instance.settings);
											curr_dates.not(this).datepicker("option", option, date);
										}
									} );
								})(jQuery);
							</script>
						</div>
					<?php
						$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
					?>
						<a href="#" class="wfsm-editor-button wfsm-schedule-sale<?php echo $wfsm_button_class; ?>"><?php _e( 'Schedule Sale', 'wfsm' ); ?></a>
				<?php
					}

					if ( !in_array( 'product_grouping' , self::$settings['user_permissions'] ) ) {

						$post_parents = array();
						$post_parents[''] = __( 'Choose a grouped product&hellip;', 'wfsm' );

						if ( $grouped_term = get_term_by( 'slug', 'grouped', 'product_type' ) ) {

							$posts_in = array_unique( (array) get_objects_in_term( $grouped_term->term_id, 'product_type' ) );

							if ( sizeof( $posts_in ) > 0 ) {

								$args = array(
									'post_type'        => 'product',
									'post_status'      => 'any',
									'numberposts'      => -1,
									'orderby'          => 'title',
									'order'            => 'asc',
									'post_parent'      => 0,
									'suppress_filters' => 0,
									'include'          => $posts_in,
								);

								$grouped_products = get_posts( $args );

								if ( $grouped_products ) {

									foreach ( $grouped_products as $sel_product ) {

										if ( $sel_product->ID == $curr_post_id ) {
											continue;
										}

										$post_parents[ $sel_product->ID ] = $sel_product->post_title;
									}
								}
							}

						}
						if ( !empty($post_parents) ) {
						?>
							<label for="wfsm-grouping" class="wfsm-selectize">
							<?php
								$wfsm_selected = ( ( $grouping = wp_get_post_parent_id( $curr_post_id ) ) !== '' ? $grouping : '' );
							?>
								<span><?php _e( 'Grouping', 'wfsm' ); ?></span>
								<select id="wfsm-grouping" name="wfsm-grouping" class="wfsm-collect-data">
								<?php
									foreach ( $post_parents as $wk => $wv ) {
								?>
										<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
								<?php
									}
								?>
								</select>
							</label>
						<?php
						}

					}

					if ( !in_array( 'product_note' , self::$settings['user_permissions'] ) ) {

						$purchase_note = ( $note = get_post_meta( $curr_post_id, '_purchase_note', true ) ) ? $note : '';
					?>
						<label for="wfsm-purchase-note" class="wfsm-label-textarea">
							<span><?php _e( 'Purchase Note', 'wfsm' ); ?></span>
							<textarea id="wfsm-purchase-note" name="wfsm-purchase-note" class="wfsm-reset-this wfsm-collect-data"><?php echo $purchase_note; ?></textarea>
						</label>
				<?php
					}
				?>
						<div class="wfsm-clear"></div>
				</div>
			<?php
				if ( !in_array( 'product_shipping' , self::$settings['user_permissions'] ) && !$product->is_type( 'external' ) && !$product->is_type( 'grouped' ) ) {
				?>
					<span class="wfsm-headline wfsm-headline-shipping<?php echo !isset( $is_virtual ) && !$product->is_type( 'variable' ) || isset( $is_virtual ) && $is_virtual == 'yes' ? ' wfsm-group-notvisible' : ' wfsm-group-visible'; ?>"><?php _e( 'Product Shipping', 'wfsm' ); ?></span>
					<div class="wfsm-group wfsm-group-shipping">
					
					<?php
						$product_weight = ( $weight = get_post_meta( $curr_post_id, '_weight', true ) ) ? $weight : '';
					?>
						<label for="wfsm-weight">
							<span><?php _e( 'Weight', 'wfsm' ); ?></span>
							<input id="wfsm-weight" name="wfsm-weight" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_weight; ?>" />
						</label>

					<?php
						$product_length = ( $length = get_post_meta( $curr_post_id, '_length', true ) ) ? $length : '';
					?>
						<label for="wfsm-length" class="wfsm-label-third wfsm-label-first">
							<span><?php _e( 'Length', 'wfsm' ); ?></span>
							<input id="wfsm-length" name="wfsm-length" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_length; ?>"/>
						</label>

					<?php
						$product_width = ( $width = get_post_meta( $curr_post_id, '_width', true ) ) ? $width : '';
					?>
						<label for="wfsm-width" class="wfsm-label-third">
							<span><?php _e( 'Width', 'wfsm' ); ?></span>
							<input id="wfsm-width" name="wfsm-width" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_width; ?>"/>
						</label>

					<?php
						$product_height = ( $height = get_post_meta( $curr_post_id, '_height', true ) ) ? $height : '';
					?>
						<label for="wfsm-height" class="wfsm-label-third">
							<span><?php _e( 'Height', 'wfsm' ); ?></span>
							<input id="wfsm-height" name="wfsm-height" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_height; ?>"/>
						</label>
						<div class="wfsm-clear"></div>


						<label for="wfsm-shipping-class" class="wfsm-selectize">
						<?php

							$shipping_classes = get_the_terms( $curr_post_id, 'product_shipping_class' );
							if ( $shipping_classes && ! is_wp_error( $shipping_classes ) ) {
								$current_shipping_class = current( $shipping_classes )->term_id;
							}
							else {
								$current_shipping_class = '';
							}

							$args = array(
								'taxonomy'         => 'product_shipping_class',
								'hide_empty'       => 0,
								'show_option_none' => __( 'No shipping class', 'wfsm' ),
								'name'             => 'wfsm-shipping-class',
								'id'               => 'wfsm-shipping-class',
								'selected'         => $current_shipping_class,
								'class'            => 'wfsm-collect-data'
							);

						?>
							<span><?php _e( 'Shipping Class', 'wfsm' ); ?></span>
							<?php wp_dropdown_categories( $args ); ?>
						</label>


					</div>
			<?php
				}
				if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) && $product->is_type( 'simple' ) ) {
				?>
					<span class="wfsm-headline wfsm-headline-downloads<?php echo !isset( $is_downloadable ) ||  $is_downloadable == 'no' ? ' wfsm-group-notvisible' : ' wfsm-group-visible'; ?>"><?php _e( 'Product Downloads', 'wfsm' ); ?></span>
					<div class="wfsm-group wfsm-group-downloads">
						<div class="wfsm-downloads-add-files">
						<?php
							$downloadable_files = get_post_meta( $curr_post_id, '_downloadable_files', true );
							if ( $downloadable_files ) {
								foreach ( $downloadable_files as $key => $file ) {
								?>
									<div class="wfsm-downloads-file">
										<a href="#" class="wfsm-downloads-move"><i class="wfsmico-move"></i></a>
										<span class="wfsm-downloads-file-name"><input type="text" placeholder="<?php _e( 'File Name', 'wfsm' ); ?>" name="wfsm-file-names[]" value="<?php echo esc_attr( $file['name'] ); ?>" class="wfsm-collect-data" /></span>
										<span class="wfsm-downloads-file-url"><input type="text" placeholder="<?php _e( "http://", 'wfsm' ); ?>" name="wfsm-file-urls[]" value="<?php echo esc_attr( $file['file'] ); ?>" class="wfsm-collect-data" /></span>
										<a href="#" class="wfsm-downloads-file-choose" data-choose="<?php _e( 'Choose file', 'wfsm' ); ?>" data-update="<?php _e( 'Insert file URL', 'wfsm' ); ?>"><?php _e( 'Choose File', 'wfsm' ); ?></a>
										<a href="#" class="wfsm-downloads-file-discard"><i class="wfsmico-discard"></i></a>
									</div>
								<?php
								}
							}
						?>

						</div>
						<a href="#" class="wfms-plain-button wfsm-add-file"><?php _e( 'Add File', 'wfsm'); ?></a>

					<?php
						if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
							$product_limit = ( $limit = get_post_meta( $curr_post_id, '_download_limit', true ) ) ? $limit : '';
						?>
							<label for="wfsm-download-limit" class="wfsm-label">
								<span><?php _e( 'Download Limit', 'wfsm' ); ?></span>
								<input id="wfsm-download-limit" name="wfsm-download-limit" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_limit; ?>"/>
							</label>

						<?php
							$product_expiry = ( $expiry = get_post_meta( $curr_post_id, '_download_expiry', true ) ) ? $expiry : '';
						?>
							<label for="wfsm-download-expiry" class="wfsm-label">
								<span><?php _e( 'Download Expiry', 'wfsm' ); ?></span>
								<input id="wfsm-download-expiry" name="wfsm-download-expiry" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_expiry; ?>"/>
							</label>

						<?php
							$download_type = array(
								'' => __( 'Standard Product', 'wfsm' ),
								'application' => __( 'Application/Software', 'wfsm' ),
								'music' => __( 'Music', 'wfsm' )
							);

							if ( !empty( $download_type ) ) {
							?>
								<label for="wfsm-download-type" class="wfsm-selectize">
								<?php
									$wfsm_selected = ( ( $curr_download_type = get_post_meta( $curr_post_id, '_tax_status', true ) ) !== '' ? $curr_download_type : '' );
								?>
									<span><?php _e( 'Download Type', 'wfsm' ); ?></span>
									<select id="wfsm-download-type" name="wfsm-download-type" class="wfsm-collect-data">
									<?php
										foreach ( $download_type as $wk => $wv ) {
									?>
											<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
									<?php
										}
									?>
									</select>
								</label>
							<?php
							}
						}
					?>

					</div>
			<?php
				}
			?>
				<span class="wfsm-headline wfsm-headline-taxonomies"><?php _e( 'Product Taxnonomies and Terms', 'wfsm' ); ?></span>
				<div class="wfsm-group wfsm-group-taxonomies">
				<?php
					if ( !in_array( 'product_cat' , self::$settings['user_permissions'] ) ) {
					?>
						<label for="wfsm-select-product_cat" class="wfsm-selectize">
							<span><?php _e( 'Product Categories', 'wfsm' ); ?></span>
							<select id="wfsm-select-product_cat" name="wfsm-select-product_cat" class="wfsm-collect-data" multiple="multiple">
							<?php
								$product_cats = wp_get_post_terms( $curr_post_id, 'product_cat', array( 'fields' => 'slugs' ) );
								foreach( get_terms('product_cat','hide_empty=0') as $term ) {
									$wfsm_selected = in_array( $term->slug , $product_cats ) ? 'added' : 'notadded' ;
								?>
									<option class="si" <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
								<?php
								}
							?>
							</select>
						</label>
					<?php
					}

					if ( !in_array( 'product_tag' , self::$settings['user_permissions'] ) ) {
					?>
						<label for="wfsm-select-product_tag" class="wfsm-selectize">
							<span><?php _e( 'Product tags', 'wfsm' ); ?></span>
							<select id="wfsm-select-product_tag" name="wfsm-select-product_tag" class="wfsm-collect-data" multiple="multiple">
							<?php
								$product_tags = wp_get_post_terms($curr_post_id, 'product_tag', array( 'fields' => 'slugs' ) );
								foreach( get_terms('product_tag','hide_empty=0') as $term ) {
									$wfsm_selected = in_array( $term->slug , $product_tags ) ? 'added' : 'notadded' ;
								?>
									<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
								<?php
								}
							?>
							</select>
						</label>
					<?php
					}

					if ( !in_array( 'product_attributes' , self::$settings['user_permissions'] ) ) {

						$attribute_taxonomies = wc_get_attribute_taxonomies();
						$product_attributes = $product->get_attributes();

					?>
						<label for="wfsm-select-attributes" class="wfsm-selectize">
							<span><?php _e( 'Product Attributes', 'wfsm' ); ?></span>
							<select id="wfsm-select-attributes" name="wfsm-select-attributes" class="wfsm-collect-data" multiple="multiple">
							<?php
								foreach ($attribute_taxonomies as $tax) {
									$tax_name_sanitized = sanitize_title( $tax->attribute_name );
									$wfsm_selected = array_key_exists( 'pa_' . $tax_name_sanitized , $product_attributes ) ? 'added' : 'notadded' ;
							?>
									<option value="<?php echo 'pa_' . $tax_name_sanitized;?>"<?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?>><?php echo wc_attribute_label( $tax->attribute_name ); ?></option>
							<?php
								}
							?>
							</select>
						</label>
					<?php
						$curr_atts = array();

						if ( !empty( $attribute_taxonomies ) && !is_wp_error( $attribute_taxonomies ) ){
							foreach ($attribute_taxonomies as $tax) {
								if ( !array_key_exists( 'pa_' . sanitize_title( $tax->attribute_name ), $product_attributes ) ) {
									continue;
								}
								$curr_name = sanitize_title($tax->attribute_name);
								$curr_paname = 'pa_' . $tax->attribute_name;
								$curr_paname_sanitized = 'pa_' . sanitize_title( $tax->attribute_name );
						?>
							<div class="wfsm-attribute-<?php echo $curr_paname_sanitized; ?>">
								<label for="wfsm-select-<?php echo $curr_paname_sanitized; ?>" class="wfsm-selectize">
									<span><?php echo __( 'Product', 'wfsm' ) . ' ' . ucfirst( $tax->attribute_label); ?></span>
									<select id="wfsm-select-<?php echo $curr_paname_sanitized; ?>" name="wfsm-select-<?php echo $curr_paname_sanitized; ?>" class="wfsm-collect-data" multiple="multiple">
									<?php
										$product_atts = wp_get_post_terms($curr_post_id, $curr_paname, array( 'fields' => 'slugs' ) );
										foreach( get_terms($curr_paname,'hide_empty=0') as $term ) {
											$wfsm_selected = in_array( $term->slug , $product_atts ) ? 'added' : 'notadded' ;
										?>
											<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
										<?php
										}
									?>
									</select>
								</label>
							<?php
								$curr_value = ( ( isset( $product_attributes[$curr_paname_sanitized]) && $product_attributes[$curr_paname_sanitized]['is_visible'] == 1 ) ? 'isvisible' : 'notvisible' );
							?>
								<label for="wfsm-visible-<?php echo $curr_paname_sanitized; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
									<span class="wfsm-show-isvisible"><?php _e( 'Attribute is visible on product page', 'wfsm' ); ?></span>
									<span class="wfsm-show-notvisible"><?php _e( 'Attribute is not visible on product page', 'wfsm' ); ?></span>
									<input id="wfsm-visible-<?php echo $curr_paname_sanitized; ?>" name="wfsm-visible-<?php echo $curr_paname_sanitized; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
								</label>
							<?php
								if ( $product->is_type( 'variable' ) ) {
									$curr_value = ( ( isset( $product_attributes[$curr_paname_sanitized]) && $product_attributes[$curr_paname_sanitized]['is_variation'] == 1 ) ? 'isvariation' : 'notvariation' );
							?>
								<label for="wfsm-variation-<?php echo $curr_paname_sanitized; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
									<span class="wfsm-show-isvariation"><?php _e( 'Attribute is used in variations', 'wfsm' ); ?></span>
									<span class="wfsm-show-notvariation"><?php _e( 'Attribute is not used in variations', 'wfsm' ); ?></span>
									<input id="wfsm-variation-<?php echo $curr_paname_sanitized; ?>" name="wfsm-variation-<?php echo $curr_paname_sanitized; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
								</label>
							<?php
								}
							?>
							</div>
						<?php
							}
						}
					}
			?>
				</div>
			<?php
				if ( !in_array( 'variable_edit_variations' , self::$settings['user_permissions'] ) && $product->is_type( 'variable' ) ) {
					$available_variations = $product->get_available_variations();
				?>
					<div class="wfsm-variations">
				<?php
					$curr_variable_attributes = get_post_meta( $curr_post_id, '_product_attributes', true );

					foreach ( $available_variations as $var ) {
						$curr_product[$var['variation_id']] = new WC_Product_Variation( $var['variation_id'] );
					?>
						<span class="wfsm-headline"><?php echo __( 'Product Variation #ID', 'wfsm' ) . ' ' . $var['variation_id']; ?><?php if ( !in_array( 'variable_delete' , self::$settings['user_permissions'] ) ) { ?><a href="#" class="wfsm-trash-variation" title="<?php _e( 'Delete variation', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a><?php } ?></span>
						<div class="wfsm-variation" data-id="<?php echo $var['variation_id']; ?>">
						<?php
							if ( !in_array( 'variable_product_attributes' , self::$settings['user_permissions'] ) ) {
							?>
								<div class="wfsm-variation-attributes">
								<?php
									foreach ( $curr_variable_attributes as $ak => $av ) {

										$curr_term = $av['name'];
										$curr_term_sanitized = sanitize_title( $curr_term );

										$curr_attributes = get_terms( $curr_term, array(
											'hide_empty' => 0
										) );

										$curr_product_atts = wp_get_post_terms($curr_post_id, $curr_term, array( 'fields' => 'slugs' ) );

										$variation_meta = get_post_meta( $var['variation_id'] );
										foreach ( $variation_meta as $key => $value ) {
											if ( false !== strpos( $key, 'attribute_' ) ) {
												$variation_data[ $key ] = $value;
											}
										}

									?>
										<label for="wfsm-attribute-<?php echo $var['variation_id']; ?>-<?php echo $curr_term_sanitized; ?>" class="wfsm-selectize">
									<?php
										$wfsm_selected = $variation_data['attribute_' . $curr_term_sanitized][0];
									?>
										<span><?php echo $curr_label = wc_attribute_label( $curr_term ); ?></span>
										<select id="wfsm-attribute-<?php echo $var['variation_id']; ?>-<?php echo $curr_term_sanitized; ?>" name="wfsm-attribute-<?php echo $var['variation_id']; ?>-<?php echo $curr_term_sanitized; ?>" class="wfsm-collect-data">
											<option value=""><?php echo __( 'Any', 'wfsm' ) . ' ' . $curr_label . '...'; ?></option>
										<?php
											foreach ( $curr_attributes as $wk => $wv ) {
												if ( !in_array( $wv->slug, $curr_product_atts ) ) {
													continue;
												}

										?>
												<option value="<?php echo $wv->slug;?>"<?php echo ( $wfsm_selected == $wv->slug ? ' selected="selected"' : '' ); ?>><?php echo $wv->name; ?></option>
										<?php
											}
										?>
										</select>
									</label>
								<?php
									}
								?>
								</div>
							<?php
							}

							if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) ) {
								?>
								<span class="wfsm-dummy-title"><?php _e( 'Featured Image', 'wfsm' ); ?></span>
								<label for="wfsm-featured-image-<?php echo $var['variation_id']; ?>" class="wfsm-featured-image">
									<a href="#" class="wfsm-featured-image-trigger">
									<?php
										if ( has_post_thumbnail( $var['variation_id'] ) ) {
											$curr_image = wp_get_attachment_image_src( $curr_image_id = get_post_thumbnail_id( $var['variation_id'] ), 'thumbnail' );
										?>
											<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
										<?php
										}
										else {
											$curr_image_id = 0;
										?>
											<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>" />
									<?php
										}
									?>
									</a>
									<input id="wfsm-featured-image-<?php echo $var['variation_id']; ?>" name="wfsm-featured-image-<?php echo $var['variation_id']; ?>" class="wfsm-collect-data" type="hidden" value="<?php echo $curr_image_id; ?>" />
								</label>
								<div class="wfsm-featured-image-controls">
									<a href="#" class="wfsm-editor-button wfsm-change-image"><?php _e( 'Change Image', 'wfsm' ); ?></a>
									<a href="#" class="wfsm-editor-button wfsm-remove-image"><?php _e( 'Discard Image', 'wfsm' ); ?></a>
								</div>
								<div class="wfsm-clear"></div>
						<?php
							}

							if ( !in_array( 'product_sku' , self::$settings['user_permissions'] ) && wc_product_sku_enabled() ) {
							?>
								<label for="wfsm-sku-<?php echo $var['variation_id']; ?>">
									<span><?php _e( 'SKU', 'wfsm' ); ?></span>
									<input id="wfsm-sku-<?php echo $var['variation_id']; ?>" name="wfsm-sku-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_sku(); ?>" />
								</label>
							<?php
							}

							if ( !in_array( 'product_prices' , self::$settings['user_permissions'] ) ) {
							?>
								<label for="wfsm-regular-price-<?php echo $var['variation_id']; ?>" class="wfsm-label-half wfsm-label-first">
									<span><?php _e( 'Regular Price', 'wfsm' ); ?></span>
									<input id="wfsm-regular-price-<?php echo $var['variation_id']; ?>" name="wfsm-regular-price-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_regular_price(); ?>"/>
								</label>
								<label for="wfsm-sale-price-<?php echo $var['variation_id']; ?>" class="wfsm-label-half">
									<span><?php _e( 'Sale Price', 'wfsm' ); ?></span>
									<input id="wfsm-sale-price-<?php echo $var['variation_id']; ?>" name="wfsm-sale-price-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$var['variation_id']]->get_sale_price(); ?>"/>
								</label>
								<div class="wfsm-clear"></div>
							<?php
							}

							if ( !in_array( 'product_stock' , self::$settings['user_permissions'] ) ) {
								$in_stock_status = ( $stock = get_post_meta( $var['variation_id'], '_stock_status', true ) ) ? $stock : 'instock';
							?>
								<label for="wfsm-product-stock-<?php echo $var['variation_id']; ?>" class="wfsm-label-checkbox wfsm-<?php echo $in_stock_status; ?>">
									<span class="wfsm-show-instock"><?php _e( 'Product is In Stock', 'wfsm' ); ?></span>
									<span class="wfsm-show-outofstock"><?php _e( 'Product is currently Out of Stock', 'wfsm' ); ?></span>
									<input id="wfsm-product-stock-<?php echo $var['variation_id']; ?>" name="wfsm-product-stock-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $in_stock_status; ?>"/>
								</label>
							<?php
								if ( 'yes' == $option_manage_stock ) {
									$wfsm_class = ( ( get_post_meta( $var['variation_id'], '_manage_stock', true ) ) == 'yes' ? ' wfsm-visible' : ' wfsm-hidden' );
								?>
									<div class="wfsm-manage-stock-quantity-<?php echo $var['variation_id']; ?><?php echo $wfsm_class; ?>">
										<label for="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" class="wfsm-label-quantity">
											<span><?php _e( 'Stock Quantity', 'wfsm' ); ?></span>
										<?php
											$stock_count = get_post_meta( $var['variation_id'], '_stock', true );
										?>
											<input id="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" name="wfsm-stock-quantity-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $stock_count; ?>" />
										</label>
										<label for="wfsm-backorders-<?php echo $var['variation_id']; ?>" class="wfsm-selectize">
										<?php
											$wfsm_selected = ( ( $backorders = get_post_meta( $var['variation_id'], '_backorders', true ) ) ? $backorders : '' );
										?>
											<span><?php _e( 'Allow Backorders', 'wfsm' ); ?></span>
											<select id="wfsm-backorders-<?php echo $var['variation_id']; ?>" name="wfsm-backorders-<?php echo $var['variation_id']; ?>" class="wfsm-collect-data">
											<?php
												$wfsm_select_options = array(
													'no'     => __( 'Do not allow', 'wfsm' ),
													'notify' => __( 'Allow, but notify customer', 'wfsm' ),
													'yes'    => __( 'Allow', 'wfsm' )
												);
												foreach ( $wfsm_select_options as $wk => $wv ) {
											?>
													<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
											<?php
												}
											?>
											</select>
										</label>
									</div>
								<?php
									$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
								?>
									<a href="#" class="wfsm-editor-button wfsm-manage-stock-quantity-<?php echo $var['variation_id']; ?><?php echo $wfsm_button_class; ?>"><?php _e( 'Manage Stock', 'wfsm' ); ?></a>
								<?php
								}
							}

							if ( !in_array( 'product_schedule_sale' , self::$settings['user_permissions'] ) ) {
								$sale_price_dates_from = ( $date = get_post_meta( $var['variation_id'], '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
								$sale_price_dates_to = ( $date = get_post_meta( $var['variation_id'], '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
								$wfsm_class = ( $sale_price_dates_from !== '' || $sale_price_dates_to !== '' ? ' wfsm-visible' : ' wfsm-hidden' );
							?>
								<div class="wfsm-schedule-sale<?php echo $wfsm_class; ?>">
									<label for="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" class="wfsm-label-half wfsm-label-first">
										<span><?php _e( 'Start Sale', 'wfsm' ); ?></span>
										<input id="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" name="wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
									</label>
									<label for="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" class="wfsm-label-half">
										<span><?php _e( 'End Sale', 'wfsm' ); ?></span>
										<input id="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" name="wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
									</label>
									<div class="wfsm-clear"></div>
									<script type="text/javascript">
										(function($){
											"use strict";
											var curr_date = new Date();
											var curr_dates = $('#wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>, #wfsm-schedule-sale-end-<?php echo $var['variation_id']; ?>').datepicker( {
												dateFormat: 'yy/mm/dd',
												defaultDate: "+1w",
												minDate: curr_date,
												onSelect: function(curr_selected) {
													var option = this.id == "wfsm-schedule-sale-start-<?php echo $var['variation_id']; ?>" ? "minDate" : "maxDate",
													instance = $(this).data("datepicker"),
													date = $.datepicker.parseDate(instance.settings.dateFormat || $.datepicker._defaults.dateFormat, curr_selected, instance.settings);
													curr_dates.not(this).datepicker("option", option, date);
												}
											} );
										})(jQuery);
									</script>
								</div>
							<?php
								$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
							?>
								<a href="#" class="wfsm-editor-button wfsm-schedule-sale<?php echo $wfsm_button_class; ?>"><?php _e( 'Schedule Sale', 'wfsm' ); ?></a>
							<?php
							}

							if ( !in_array( 'product_taxes' , self::$settings['user_permissions'] ) ) {

								$tax_classes = WC_Tax::get_tax_classes();
								$classes_options = array();
								$classes_options['parent'] = __( 'Same as parent', 'wfsm' );
								$classes_options[''] = __( 'Standard', 'wfsm' );

								if ( $tax_classes ) {
									foreach ( $tax_classes as $class ) {
										$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
									}
								}

								if ( !empty( $classes_options ) ) {
								?>
									<label for="wfsm-tax-class-<?php echo $var['variation_id']; ?>" class="wfsm-selectize">
									<?php
										$wfsm_selected = ( ( $class = get_post_meta( $var['variation_id'], '_tax_class', true ) ) !== '' ? $class : '' );
									?>
										<span><?php _e( 'Tax Class', 'wfsm' ); ?></span>
										<select id="wfsm-tax-class-<?php echo $var['variation_id']; ?>" name="wfsm-tax-class" class="wfsm-collect-data">
										<?php
											foreach ( $classes_options as $wk => $wv ) {
										?>
												<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
										<?php
											}
										?>
										</select>
									</label>
								<?php
								}

							}

							if ( !in_array( 'product_virtual' , self::$settings['user_permissions'] ) ) {
								$is_virtual = ( $virtual = get_post_meta( $var['variation_id'], '_virtual', true ) ) ? $virtual : 'no';
							?>
								<label for="wfsm-virtual-<?php echo $var['variation_id']; ?>" class="wfsm-label-checkbox wfsm-<?php echo $is_virtual; ?>" data-variable-linked="shipping">
									<span class="wfsm-show-yes"><?php _e( 'Product is Virtual', 'wfsm' ); ?></span>
									<span class="wfsm-show-no"><?php _e( 'Product is not Virtual', 'wfsm' ); ?></span>
									<input id="wfsm-virtual-<?php echo $var['variation_id']; ?>" name="wfsm-virtual-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_virtual; ?>"/>
								</label>
							<?php
							}

							if ( !in_array( 'product_shipping' , self::$settings['user_permissions'] ) ) {
							?>
								<div class="wfsm-variable-group wfsm-group-variable-shipping wfsm-group-variable-shipping<?php echo $is_virtual == 'no' ? ' wfsm-group-visible' : ' wfsm-group-notvisible' ;?>">
								
								<?php
									$product_weight = ( $weight = get_post_meta( $var['variation_id'], '_weight', true ) ) ? $weight : '';
								?>
									<label for="wfsm-weight-<?php echo $var['variation_id']; ?>">
										<span><?php _e( 'Weight', 'wfsm' ); ?></span>
										<input id="wfsm-weight-<?php echo $var['variation_id']; ?>" name="wfsm-weight-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_weight; ?>" />
									</label>

								<?php
									$product_length = ( $length = get_post_meta( $var['variation_id'], '_length', true ) ) ? $length : '';
								?>
									<label for="wfsm-length-<?php echo $var['variation_id']; ?>" class="wfsm-label-third wfsm-label-first">
										<span><?php _e( 'Length', 'wfsm' ); ?></span>
										<input id="wfsm-length-<?php echo $var['variation_id']; ?>" name="wfsm-length-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_length; ?>"/>
									</label>

								<?php
									$product_width = ( $width = get_post_meta( $var['variation_id'], '_width', true ) ) ? $width : '';
								?>
									<label for="wfsm-width-<?php echo $var['variation_id']; ?>" class="wfsm-label-third">
										<span><?php _e( 'Width', 'wfsm' ); ?></span>
										<input id="wfsm-width-<?php echo $var['variation_id']; ?>" name="wfsm-width-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_width; ?>"/>
									</label>

								<?php
									$product_height = ( $height = get_post_meta( $var['variation_id'], '_height', true ) ) ? $height : '';
								?>
									<label for="wfsm-height-<?php echo $var['variation_id']; ?>" class="wfsm-label-third">
										<span><?php _e( 'Height', 'wfsm' ); ?></span>
										<input id="wfsm-height-<?php echo $var['variation_id']; ?>" name="wfsm-height-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_height; ?>"/>
									</label>
									<div class="wfsm-clear"></div>


									<label for="wfsm-shipping-class-<?php echo $var['variation_id']; ?>" class="wfsm-selectize">
									<?php

										$args = array(
											'taxonomy'         => 'product_shipping_class',
											'hide_empty'       => 0,
											'show_option_none' => __( 'Same as parent', 'wfsm' ),
											'name'             => 'wfsm-shipping-class-' . $var['variation_id'],
											'id'               => 'wfsm-shipping-class-' . $var['variation_id'],
											'selected'         => $current_shipping_class,
											'class'            => 'wfsm-collect-data'
										);

									?>
										<span><?php _e( 'Shipping Class', 'wfsm' ); ?></span>
										<?php wp_dropdown_categories( $args ); ?>
									</label>

								</div>
						<?php
							}

							if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) {
								$is_downloadable = ( $downloadable = get_post_meta( $var['variation_id'], '_downloadable', true ) ) ? $downloadable : 'no';
							?>
								<label for="wfsm-downloadable-<?php echo $var['variation_id']; ?>" class="wfsm-label-checkbox wfsm-<?php echo $is_downloadable; ?>" data-variable-linked="downloads">
									<span class="wfsm-show-yes"><?php _e( 'Product is Downloadable', 'wfsm' ); ?></span>
									<span class="wfsm-show-no"><?php _e( 'Product is not Downloadable', 'wfsm' ); ?></span>
									<input id="wfsm-downloadable-<?php echo $var['variation_id']; ?>" name="wfsm-downloadable-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_downloadable; ?>"/>
								</label>
							<?php
							}

							if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) {
							?>
								<div class="wfsm-variable-group wfsm-group-variable-downloads wfsm-group-variable-downloads<?php echo $is_downloadable == 'yes' ? ' wfsm-group-visible' : ' wfsm-group-notvisible' ;?>">
									<div class="wfsm-downloads-add-files">
									<?php
										$downloadable_files = get_post_meta( $var['variation_id'], '_downloadable_files', true );
										if ( $downloadable_files ) {
											foreach ( $downloadable_files as $key => $file ) {
											?>
												<div class="wfsm-downloads-file">
													<a href="#" class="wfsm-downloads-move"><i class="wfsmico-move"></i></a>
													<span class="wfsm-downloads-file-name"><input type="text" placeholder="<?php _e( 'File Name', 'wfsm' ); ?>" name="wfsm-file-names-<?php echo $var['variation_id']; ?>[]" value="<?php echo esc_attr( $file['name'] ); ?>" class="wfsm-collect-data" /></span>
													<span class="wfsm-downloads-file-url"><input type="text" placeholder="<?php _e( "http://", 'wfsm' ); ?>" name="wfsm-file-urls-<?php echo $var['variation_id']; ?>[]" value="<?php echo esc_attr( $file['file'] ); ?>" class="wfsm-collect-data" /></span>
													<a href="#" class="wfsm-downloads-file-choose" data-choose="<?php _e( 'Choose file', 'wfsm' ); ?>" data-update="<?php _e( 'Insert file URL', 'wfsm' ); ?>"><?php _e( 'Choose File', 'wfsm' ); ?></a>
													<a href="#" class="wfsm-downloads-file-discard"><i class="wfsmico-discard"></i></a>
												</div>
											<?php
											}
										}
									?>

									</div>
									<a href="#" class="wfms-plain-button wfsm-add-file"><?php _e( 'Add File', 'wfsm'); ?></a>

								<?php
									if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
										$product_limit = ( $limit = get_post_meta( $var['variation_id'], '_download_limit', true ) ) ? $limit : '';
									?>
										<label for="wfsm-download-limit-<?php echo $var['variation_id']; ?>" class="wfsm-label">
											<span><?php _e( 'Download Limit', 'wfsm' ); ?></span>
											<input id="wfsm-download-limit-<?php echo $var['variation_id']; ?>" name="wfsm-download-limit-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_limit; ?>"/>
										</label>

									<?php
										$product_expiry = ( $expiry = get_post_meta( $var['variation_id'], '_download_expiry', true ) ) ? $expiry : '';
									?>
										<label for="wfsm-download-expiry-<?php echo $var['variation_id']; ?>" class="wfsm-label">
											<span><?php _e( 'Download Expiry', 'wfsm' ); ?></span>
											<input id="wfsm-download-expiry-<?php echo $var['variation_id']; ?>" name="wfsm-download-expiry-<?php echo $var['variation_id']; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_expiry; ?>"/>
										</label>

									<?php
									}
								?>

								</div>
						<?php
							}
						?>
						</div>
					<?php
						}

						if ( !in_array( 'variable_add_variations' , self::$settings['user_permissions'] ) ) {
						?>
							<span class="wfsm-add-variation"><?php echo __( 'Add New Variation', 'wfsm' ); ?></span>
						<?php
						}

						if ( !in_array( 'variable_product_attributes' , self::$settings['user_permissions'] ) ) {
						?>
							<span class="wfsm-refresh-attributes"><?php _e( 'Update Attributes and Variations', 'wfsm' ); ?></span>
						<?php
						}
					?>
					</div>
				<?php
				}
			?>
				<div class="wfsm-info">
					<?php echo 'WooCommerce Frontend Shop Manager v3.1.1' . '<br/><a href="http://codecanyon.net/user/dzeriho/portfolio?ref=dzeriho">' . __('Get more premium plugins at this link!', 'wfsm' ) . '</a>'; ?>
				</div>
				<div class="wfsm-clear"></div>
				<script type="text/javascript">
					(function($){
						"use strict";

						<?php if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) ) { ?>
						$(document).on('click', '.wfsm-quick-editor label.wfsm-featured-image > a.wfsm-featured-image-trigger, .wfsm-change-image', function () {

							if ( $(this).hasClass('wfsm-change-image') ) {
								var el = $(this).parent().prev().find('.wfsm-featured-image-trigger');
							}
							else {
								var el = $(this);
							}

							var curr = el.parent();

							if ( $.isEmptyObject(window.wfsm_frame) == false ) {

								window.wfsm_frame.off('select');

								window.wfsm_frame.on( 'select', function() {

									var attachment = window.wfsm_frame.state().get('selection').first();
									window.wfsm_frame.close();

									curr.find('input:hidden').val(attachment.id);
									if ( attachment.attributes.type == 'image' ) {
										el.html('<img width="64" height="64" src="'+attachment.attributes.sizes.thumbnail.url+'" />');
									}

								});

								window.wfsm_frame.open();

								return false;
							}


							window.wfsm_frame = wp.media({
								title: '<?php echo esc_attr( __('Set Featured Image','wfsm') ); ?>',
								button: {
									text: el.data("update"),
									close: false
								},
								multiple: false,
								default_tab: 'upload',
								tabs: 'upload, library',
								returned_image_size: 'thumbnail'
							});

							window.wfsm_frame.off('select');

							window.wfsm_frame.on( 'select', function() {

								var attachment = window.wfsm_frame.state().get('selection').first();
								window.wfsm_frame.close();

								curr.find('input:hidden').val(attachment.id);
								if ( attachment.attributes.type == 'image' ) {
									el.html('<img width="64" height="64" src="'+attachment.attributes.sizes.thumbnail.url+'" />');
								}

							});

							window.wfsm_frame.open();

							return false;

						});
						<?php } ?>

						<?php if ( !in_array( 'product_gallery' , self::$settings['user_permissions'] ) ) { ?>
						$(document).on('click', '.wfsm-quick-editor label.wfsm-product-gallery > .wfsm-add-gallery-image', function () {

							var curr_input = $(this).next();
							var curr = $(this).parent().prev();

							if ( $.isEmptyObject(window.wfsm_frame_gallery) == false ) {

								window.wfsm_frame_gallery.off("select");

								window.wfsm_frame_gallery.on( 'select', function() {

									var attachment = window.wfsm_frame_gallery.state().get('selection');
									window.wfsm_frame_gallery.close();

									attachment.each( function(curr_att) {
										curr_input.prepend('<option value="'+curr_att.id+'" selected="selected">'+curr_att.id+'</option>');
										if ( curr_att.attributes.type == 'image' ) {
											curr.prepend('<span class="wfsm-product-gallery-image" data-id="'+curr_att.id+'"><img width="64" height="64" src="'+curr_att.attributes.sizes.thumbnail.url+'" /><a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a></span>');
										}
									});

								});

								window.wfsm_frame_gallery.open();

								return false;
							}


							window.wfsm_frame_gallery = wp.media({
								title: '<?php echo esc_attr( __('Select Product Images','wfsm') ); ?>',
								button: {
									text: '<?php echo esc_attr( __( 'Add Images', 'wfsm' ) ); ?>',
									close: false
								},
								multiple: true,
								default_tab: 'upload',
								tabs: 'upload, library',
								returned_image_size: 'thumbnail'
							});

							window.wfsm_frame_gallery.off('select');

							window.wfsm_frame_gallery.on( 'select', function() {

								var attachment = window.wfsm_frame_gallery.state().get('selection');
								window.wfsm_frame_gallery.close();

								attachment.each( function(curr_att) {
									curr_input.prepend('<option value="'+curr_att.id+'" selected="selected">'+curr_att.id+'</option>');
									if ( curr_att.attributes.type == 'image' ) {
										curr.prepend('<span class="wfsm-product-gallery-image" data-id="'+curr_att.id+'"><img width="64" height="64" src="'+curr_att.attributes.sizes.thumbnail.url+'" /><a href="#" class="wfsm-remove-gallery-image"><i class="wfsmico-discard"></i></a></span>');
									}
								});

							});

							window.wfsm_frame_gallery.open();

							return false;

						});

						$(document).on('click', '.wfsm-remove-gallery-image', function () {

							var el = $(this).parent();
							var curr = el.parent().next();
							var el_id = el.attr('data-id');

							el.remove();
							curr.find('select option[value="'+el_id+'"]').remove();

							return false;

						});

						$(document).on('click', '.wfsm-remove-image', function () {

							var el = $(this).parent().prev().find('.wfsm-featured-image-trigger');
							var curr = el.parent();

							el.html('<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>">');
							curr.find('input').val('0');

							return false;

						});
						<?php } ?>

						$('.wfsm-group-taxonomies .wfsm-selectize select').each( function() {
							var curr = $(this);

							curr.selectize({
								plugins: ['remove_button'],
								delimiter: ',',
								persist: false<?php if ( !in_array( 'product_new_terms' , self::$settings['user_permissions'] ) ) { ?>,
								onItemAdd: function(input) {
									if ( curr.closest('label').attr('for') == 'wfsm-select-attributes' ) {

										var el = $('.wfsm-buttons.wfsm-active');

										var curr_data = {
											action: 'wfsm_create_attribute',
											wfsm_id: el.attr('data-id'),
											wfsm_add: input
										}

										$.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', curr_data, function(response) {
											if (response) {

												curr.closest('.wfsm-screen').find('.wfsm-group-taxonomies').append(response);
												curr.closest('.wfsm-screen').find('.wfsm-group-taxonomies').find('div:last select:first').selectize({
													plugins: ['remove_button'],
													delimiter: ',',
													persist: false,
													create: function(input) {
														return {
															value: input,
															text: input
														}
													}
												});

											}
											else {
												alert('Error!');
											}
										});

									}
								},
								onItemRemove: function(input) {
									if ( curr.closest('label').attr('for') == 'wfsm-select-attributes' ) {
										var curr_attribute = $(document.getElementsByClassName('wfsm-attribute-'+input)[0]);
										curr_attribute.remove();
									}
								},
								create: function(input) {
									return {
										value: input,
										text: input
									}
								}<?php } ?>
							});
						});

						<?php if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) { ?>
						$('.wfsm-downloads-add-files').sortable({
							cursor:'move'
						});

						$(document).on('click', '.wfsm-downloads-file-choose', function () {

							var el = $(this);

							var curr = el.parent();

							if ( $.isEmptyObject(window.wfsm_frame_files) == false ) {

								window.wfsm_frame_files.off('select');

								window.wfsm_frame_files.on( 'select', function() {

									var attachment = window.wfsm_frame_files.state().get('selection').first();
									window.wfsm_frame_files.close();

									curr.find('.wfsm-downloads-file-url input').val(attachment.attributes.url);

								});

								window.wfsm_frame_files.open();

								return false;
							}


							window.wfsm_frame_files = wp.media({
								title: '<?php echo esc_attr( __('Choose File','wfsm') ); ?>',
								button: {
									text: el.data('update'),
									close: false
								},
								multiple: false,
								default_tab: 'upload',
								tabs: 'upload, library'
							});

							window.wfsm_frame_files.off('select');

							window.wfsm_frame_files.on( 'select', function() {

								var attachment = window.wfsm_frame_files.state().get('selection').first();
								window.wfsm_frame_files.close();

								curr.find('.wfsm-downloads-file-url input').val(attachment.attributes.url);

							});

							window.wfsm_frame_files.open();

							return false;

						});
						<?php } ?>

					})(jQuery);
				</script>
			</div>
		</div>
	<?php
		$out = ob_get_clean();

		die($out);
		exit;

	}

	function wfsm_add_variation_respond() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = absint( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		if ( ( isset( $_POST['wfsm_mode'] ) && $_POST['wfsm_mode'] == 'get' ) === false ) {

			$curr_create = array(
				'post_title'     => '',
				'post_content'   => '',
				'post_name'      => 'product-' . $curr_post_id . '-variation',
				'post_status'    => 'publish',
				'post_type'      => 'product_variation',
				'post_parent'    => $curr_post_id,
				'comment_status' => get_option('default_comment_status'),
				'ping_status'    => get_option('default_ping_status'),
				'menu_order'     => ( isset( $_POST['wfsm_order'] ) ? $_POST['wfsm_order'] : 0 ),
				'post_author'    => $curr_post_author
			);

			$curr_dummy = wp_insert_post( $curr_create );

			$args = array(
				'post_type'   => 'product_variation',
				'post_status' => array( 'private', 'publish' ),
				'numberposts' => -1,
				'orderby'     => 'menu_order',
				'order'       => 'ASC',
				'fields'      => 'ids',
				'post_parent' => $curr_post_id
			);

			$variations = get_posts( $args );

			$transient_name = 'wc_product_children_ids_' . $curr_post_id . WC_Cache_Helper::get_transient_version( 'product' );

			set_transient( $transient_name, $variations, DAY_IN_SECONDS * 30 );

			$curr_parent = new WC_Product( $curr_post_id );
			$curr_parent_id = $curr_post_id;

			$add_attributes = $curr_parent->get_attributes();

			foreach( $add_attributes as $k => $v ) {
				update_post_meta( $curr_dummy, 'attribute_' . $k, '');
			}

			update_post_meta( $curr_dummy, '_visibility', 'visible' );
			update_post_meta( $curr_dummy, '_sku', '' );
			update_post_meta( $curr_dummy, '_stock_status', 'instock' );
			update_post_meta( $curr_dummy, '_total_sales', '0' );

			$is_virtual = get_option( 'wc_settings_wfsm_create_virtual', 'no' ) == 'yes' ? 'yes' : 'no';
			update_post_meta( $curr_dummy, '_virtual', $is_virtual );

			$is_downloadable = get_option( 'wc_settings_wfsm_create_downloadable', 'no' ) == 'yes' ? 'yes' : 'no';;
			update_post_meta( $curr_dummy, '_downloadable', $is_downloadable );

			update_post_meta( $curr_dummy, '_featured', 'no' );

		}
		else {
			$curr_dummy = $curr_post_id;
		}

		do_action( 'woocommerce_update_product_variation', $curr_dummy );

		if ( !isset( $curr_parent_id ) ) {
			$curr_parent_id = wp_get_post_parent_id( $curr_dummy );
		}

		$curr_product[$curr_dummy] = new WC_Product_Variation( $curr_dummy );
		$curr_variable_attributes = get_post_meta( $curr_parent_id, '_product_attributes', true );

		ob_start();

	?>
		<span class="wfsm-headline"><?php echo __( 'Product Variation #ID', 'wfsm' ) . ' ' . $curr_dummy; ?><?php if ( !in_array( 'variable_delete' , self::$settings['user_permissions'] ) ) { ?><a href="#" class="wfsm-trash-variation" title="<?php _e( 'Delete variation', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a><?php } ?></span>
		<div class="wfsm-variation" data-id="<?php echo $curr_dummy; ?>">
		<?php
			if ( !in_array( 'variable_product_attributes' , self::$settings['user_permissions'] ) && is_array( $curr_variable_attributes ) ) {
			?>
				<div class="wfsm-variation-attributes">
				<?php
					foreach ( $curr_variable_attributes as $ak => $av ) {

						$curr_term = $av['name'];
						$curr_term_sanitized = sanitize_title( $curr_term );

						$curr_attributes = get_terms( $curr_term, array(
							'hide_empty' => 0
						) );

						$curr_product_atts = wp_get_post_terms( $curr_parent_id, $curr_term, array( 'fields' => 'slugs' ) );

						$variation_meta = get_post_meta( $curr_dummy );
						foreach ( $variation_meta as $key => $value ) {
							if ( false !== strpos( $key, 'attribute_' ) ) {
								$variation_data[ $key ] = $value;
							}
						}
					?>
						<label for="wfsm-attribute-<?php echo $curr_dummy; ?>-<?php echo $curr_term_sanitized; ?>" class="wfsm-selectize">
					<?php
						$wfsm_selected = $variation_data['attribute_' . $curr_term_sanitized][0];
					?>
						<span><?php echo $curr_label = wc_attribute_label( $curr_term ); ?></span>
						<select id="wfsm-attribute-<?php echo $curr_dummy; ?>-<?php echo $curr_term_sanitized; ?>" name="wfsm-attribute-<?php echo $curr_dummy; ?>-<?php echo $curr_term_sanitized; ?>" class="wfsm-collect-data">
							<option value=""><?php echo __( 'Any', 'wfsm' ) . ' ' . $curr_label . '...'; ?></option>
						<?php
							foreach ( $curr_attributes as $wk => $wv ) {
								if ( !in_array( $wv->slug, $curr_product_atts ) ) {
									continue;
								}

						?>
								<option value="<?php echo $wv->slug;?>"<?php echo ( $wfsm_selected == $wv->slug ? ' selected="selected"' : '' ); ?>><?php echo $wv->name; ?></option>
						<?php
							}
						?>
						</select>
					</label>
				<?php
					}
				?>
				</div>
			<?php
			}

			if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) ) {
				?>
				<span class="wfsm-dummy-title"><?php _e( 'Featured Image', 'wfsm' ); ?></span>
				<label for="wfsm-featured-image-<?php echo $curr_dummy; ?>" class="wfsm-featured-image">
					<a href="#" class="wfsm-featured-image-trigger">
					<?php
						if ( has_post_thumbnail( $curr_dummy ) ) {
							$curr_image = wp_get_attachment_image_src( $curr_image_id = get_post_thumbnail_id( $curr_dummy ), 'thumbnail' );
						?>
							<img width="64" height="64" src="<?php echo $curr_image[0]; ?>" />
						<?php
						}
						else {
							$curr_image_id = 0;
						?>
							<img width="64" height="64" src="<?php echo plugins_url( 'assets/images/placeholder.gif', __FILE__ ); ?>" />
					<?php
						}
					?>
					</a>
					<input id="wfsm-featured-image-<?php echo $curr_dummy; ?>" name="wfsm-featured-image-<?php echo $curr_dummy; ?>" class="wfsm-collect-data" type="hidden" value="<?php echo $curr_image_id; ?>" />
				</label>
				<div class="wfsm-featured-image-controls">
					<a href="#" class="wfsm-editor-button wfsm-change-image"><?php _e( 'Change Image', 'wfsm' ); ?></a>
					<a href="#" class="wfsm-editor-button wfsm-remove-image"><?php _e( 'Discard Image', 'wfsm' ); ?></a>
				</div>
				<div class="wfsm-clear"></div>
		<?php
			}

			if ( !in_array( 'product_sku' , self::$settings['user_permissions'] ) && wc_product_sku_enabled() ) {
			?>
				<label for="wfsm-sku-<?php echo $curr_dummy; ?>">
					<span><?php _e( 'SKU', 'wfsm' ); ?></span>
					<input id="wfsm-sku-<?php echo $curr_dummy; ?>" name="wfsm-sku-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$curr_dummy]->get_sku(); ?>" />
				</label>
			<?php
			}

			if ( !in_array( 'product_prices' , self::$settings['user_permissions'] ) ) {
			?>
				<label for="wfsm-regular-price-<?php echo $curr_dummy; ?>" class="wfsm-label-half wfsm-label-first">
					<span><?php _e( 'Regular Price', 'wfsm' ); ?></span>
					<input id="wfsm-regular-price-<?php echo $curr_dummy; ?>" name="wfsm-regular-price-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$curr_dummy]->get_regular_price(); ?>"/>
				</label>
				<label for="wfsm-sale-price-<?php echo $curr_dummy; ?>" class="wfsm-label-half">
					<span><?php _e( 'Sale Price', 'wfsm' ); ?></span>
					<input id="wfsm-sale-price-<?php echo $curr_dummy; ?>" name="wfsm-sale-price-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $curr_product[$curr_dummy]->get_sale_price(); ?>"/>
				</label>
				<div class="wfsm-clear"></div>
			<?php
			}

			if ( !in_array( 'product_stock' , self::$settings['user_permissions'] ) ) {
				$in_stock_status = ( $stock = get_post_meta( $curr_dummy, '_stock_status', true ) ) ? $stock : 'instock';
			?>
				<label for="wfsm-product-stock-<?php echo $curr_dummy; ?>" class="wfsm-label-checkbox wfsm-<?php echo $in_stock_status; ?>">
					<span class="wfsm-show-instock"><?php _e( 'Product is In Stock', 'wfsm' ); ?></span>
					<span class="wfsm-show-outofstock"><?php _e( 'Product is currently Out of Stock', 'wfsm' ); ?></span>
					<input id="wfsm-product-stock-<?php echo $curr_dummy; ?>" name="wfsm-product-stock-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $in_stock_status; ?>"/>
				</label>
			<?php
				$option_manage_stock = get_option( 'woocommerce_manage_stock' );
				if ( 'yes' == $option_manage_stock ) {
					$wfsm_class = ( ( get_post_meta( $curr_dummy, '_manage_stock', true ) ) == 'yes' ? ' wfsm-visible' : ' wfsm-hidden' );
				?>
					<div class="wfsm-manage-stock-quantity-<?php echo $curr_dummy; ?><?php echo $wfsm_class; ?>">
						<label for="wfsm-stock-quantity-<?php echo $curr_dummy; ?>" class="wfsm-label-quantity">
							<span><?php _e( 'Stock Quantity', 'wfsm' ); ?></span>
						<?php
							$stock_count = get_post_meta( $curr_dummy, '_stock', true );
						?>
							<input id="wfsm-stock-quantity-<?php echo $curr_dummy; ?>" name="wfsm-stock-quantity-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $stock_count; ?>" />
						</label>
						<label for="wfsm-backorders-<?php echo $curr_dummy; ?>" class="wfsm-selectize">
						<?php
							$wfsm_selected = ( ( $backorders = get_post_meta( $curr_dummy, '_backorders', true ) ) ? $backorders : '' );
						?>
							<span><?php _e( 'Allow Backorders', 'wfsm' ); ?></span>
							<select id="wfsm-backorders-<?php echo $curr_dummy; ?>" name="wfsm-backorders-<?php echo $curr_dummy; ?>" class="wfsm-collect-data">
							<?php
								$wfsm_select_options = array(
									'no'     => __( 'Do not allow', 'wfsm' ),
									'notify' => __( 'Allow, but notify customer', 'wfsm' ),
									'yes'    => __( 'Allow', 'wfsm' )
								);
								foreach ( $wfsm_select_options as $wk => $wv ) {
							?>
									<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
							<?php
								}
							?>
							</select>
						</label>
					</div>
				<?php
					$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
				?>
					<a href="#" class="wfsm-editor-button wfsm-manage-stock-quantity-<?php echo $curr_dummy; ?><?php echo $wfsm_button_class; ?>"><?php _e( 'Manage Stock', 'wfsm' ); ?></a>
				<?php
				}
			}

			if ( !in_array( 'product_schedule_sale' , self::$settings['user_permissions'] ) ) {
				$sale_price_dates_from = ( $date = get_post_meta( $curr_dummy, '_sale_price_dates_from', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
				$sale_price_dates_to = ( $date = get_post_meta( $curr_dummy, '_sale_price_dates_to', true ) ) ? date_i18n( 'Y-m-d', $date ) : '';
				$wfsm_class = ( $sale_price_dates_from !== '' || $sale_price_dates_to !== '' ? ' wfsm-visible' : ' wfsm-hidden' );
			?>
				<div class="wfsm-schedule-sale<?php echo $wfsm_class; ?>">
					<label for="wfsm-schedule-sale-start-<?php echo $curr_dummy; ?>" class="wfsm-label-half wfsm-label-first">
						<span><?php _e( 'Start Sale', 'wfsm' ); ?></span>
						<input id="wfsm-schedule-sale-start-<?php echo $curr_dummy; ?>" name="wfsm-schedule-sale-start-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_from ); ?>" placeholder="<?php _e( 'From&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
					</label>
					<label for="wfsm-schedule-sale-end-<?php echo $curr_dummy; ?>" class="wfsm-label-half">
						<span><?php _e( 'End Sale', 'wfsm' ); ?></span>
						<input id="wfsm-schedule-sale-end-<?php echo $curr_dummy; ?>" name="wfsm-schedule-sale-end-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-date-picker wfsm-collect-data" type="text" value="<?php echo esc_attr( $sale_price_dates_to ); ?>" placeholder="<?php _e( 'To&hellip; YYYY-MM-DD', 'wfsm' ); ?>" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />
					</label>
					<div class="wfsm-clear"></div>
				</div>
			<?php
				$wfsm_button_class = ( $wfsm_class == ' wfsm-visible' ? ' wfsm-active' : '' );
			?>
				<a href="#" class="wfsm-editor-button wfsm-schedule-sale<?php echo $wfsm_button_class; ?>"><?php _e( 'Schedule Sale', 'wfsm' ); ?></a>
			<?php
			}

			if ( !in_array( 'product_taxes' , self::$settings['user_permissions'] ) ) {

				$tax_classes = WC_Tax::get_tax_classes();
				$classes_options = array();
				$classes_options['parent'] = __( 'Same as parent', 'wfsm' );
				$classes_options[''] = __( 'Standard', 'wfsm' );

				if ( $tax_classes ) {
					foreach ( $tax_classes as $class ) {
						$classes_options[ sanitize_title( $class ) ] = esc_html( $class );
					}
				}

				if ( !empty( $classes_options ) ) {
				?>
					<label for="wfsm-tax-class-<?php echo $curr_dummy; ?>" class="wfsm-selectize">
					<?php
						$wfsm_selected = ( ( $class = get_post_meta( $curr_dummy, '_tax_class', true ) ) !== '' ? $class : '' );
					?>
						<span><?php _e( 'Tax Class', 'wfsm' ); ?></span>
						<select id="wfsm-tax-class-<?php echo $curr_dummy; ?>" name="wfsm-tax-class" class="wfsm-collect-data">
						<?php
							foreach ( $classes_options as $wk => $wv ) {
						?>
								<option value="<?php echo $wk;?>"<?php echo ( $wfsm_selected == $wk ? ' selected="selected"' : '' ); ?>><?php echo $wv; ?></option>
						<?php
							}
						?>
						</select>
					</label>
				<?php
				}

			}

			if ( !in_array( 'product_virtual' , self::$settings['user_permissions'] ) ) {
				$is_virtual = ( $virtual = get_post_meta( $curr_dummy, '_virtual', true ) ) ? $virtual : 'no';
			?>
				<label for="wfsm-virtual-<?php echo $curr_dummy; ?>" class="wfsm-label-checkbox wfsm-<?php echo $is_virtual; ?>" data-variable-linked="shipping">
					<span class="wfsm-show-yes"><?php _e( 'Product is Virtual', 'wfsm' ); ?></span>
					<span class="wfsm-show-no"><?php _e( 'Product is not Virtual', 'wfsm' ); ?></span>
					<input id="wfsm-virtual-<?php echo $curr_dummy; ?>" name="wfsm-virtual-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_virtual; ?>"/>
				</label>
			<?php
			}

			if ( !in_array( 'product_shipping' , self::$settings['user_permissions'] ) ) {
			?>
				<div class="wfsm-variable-group wfsm-group-variable-shipping wfsm-group-variable-shipping<?php echo $is_virtual == 'no' ? ' wfsm-group-visible' : ' wfsm-group-notvisible' ;?>">
				
				<?php
					$product_weight = ( $weight = get_post_meta( $curr_dummy, '_weight', true ) ) ? $weight : '';
				?>
					<label for="wfsm-weight-<?php echo $curr_dummy; ?>">
						<span><?php _e( 'Weight', 'wfsm' ); ?></span>
						<input id="wfsm-weight-<?php echo $curr_dummy; ?>" name="wfsm-weight-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_weight; ?>" />
					</label>

				<?php
					$product_length = ( $length = get_post_meta( $curr_dummy, '_length', true ) ) ? $length : '';
				?>
					<label for="wfsm-length-<?php echo $curr_dummy; ?>" class="wfsm-label-third wfsm-label-first">
						<span><?php _e( 'Length', 'wfsm' ); ?></span>
						<input id="wfsm-length-<?php echo $curr_dummy; ?>" name="wfsm-length-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_length; ?>"/>
					</label>

				<?php
					$product_width = ( $width = get_post_meta( $curr_dummy, '_width', true ) ) ? $width : '';
				?>
					<label for="wfsm-width-<?php echo $curr_dummy; ?>" class="wfsm-label-third">
						<span><?php _e( 'Width', 'wfsm' ); ?></span>
						<input id="wfsm-width-<?php echo $curr_dummy; ?>" name="wfsm-width-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_width; ?>"/>
					</label>

				<?php
					$product_height = ( $height = get_post_meta( $curr_dummy, '_height', true ) ) ? $height : '';
				?>
					<label for="wfsm-height-<?php echo $curr_dummy; ?>" class="wfsm-label-third">
						<span><?php _e( 'Height', 'wfsm' ); ?></span>
						<input id="wfsm-height-<?php echo $curr_dummy; ?>" name="wfsm-height-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="text" value="<?php echo $product_height; ?>"/>
					</label>
					<div class="wfsm-clear"></div>


					<label for="wfsm-shipping-class-<?php echo $curr_dummy; ?>" class="wfsm-selectize">
					<?php

						$shipping_classes = get_the_terms( $curr_post_id, 'product_shipping_class' );
						if ( $shipping_classes && ! is_wp_error( $shipping_classes ) ) {
							$current_shipping_class = current( $shipping_classes )->term_id;
						}
						else {
							$current_shipping_class = '';
						}

						$args = array(
							'taxonomy'         => 'product_shipping_class',
							'hide_empty'       => 0,
							'show_option_none' => __( 'Same as parent', 'wfsm' ),
							'name'             => 'wfsm-shipping-class-' . $curr_dummy,
							'id'               => 'wfsm-shipping-class-' . $curr_dummy,
							'selected'         => $current_shipping_class,
							'class'            => 'wfsm-collect-data'
						);

					?>
						<span><?php _e( 'Shipping Class', 'wfsm' ); ?></span>
						<?php wp_dropdown_categories( $args ); ?>
					</label>

				</div>
		<?php
			}

			if ( !in_array( 'product_downloadable' , self::$settings['user_permissions'] ) ) {
				$is_downloadable = ( $downloadable = get_post_meta( $curr_dummy, '_downloadable', true ) ) ? $downloadable : 'no';
			?>
				<label for="wfsm-downloadable-<?php echo $curr_dummy; ?>" class="wfsm-label-checkbox wfsm-<?php echo $is_downloadable; ?>" data-variable-linked="downloads">
					<span class="wfsm-show-yes"><?php _e( 'Product is Downloadable', 'wfsm' ); ?></span>
					<span class="wfsm-show-no"><?php _e( 'Product is not Downloadable', 'wfsm' ); ?></span>
					<input id="wfsm-downloadable-<?php echo $curr_dummy; ?>" name="wfsm-downloadable-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $is_downloadable; ?>"/>
				</label>
			<?php
			}

			if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) {
			?>
				<div class="wfsm-variable-group wfsm-group-variable-downloads wfsm-group-variable-downloads<?php echo $is_downloadable == 'yes' ? ' wfsm-group-visible' : ' wfsm-group-notvisible' ;?>">
					<div class="wfsm-downloads-add-files">
					<?php
						$downloadable_files = get_post_meta( $curr_dummy, '_downloadable_files', true );
						if ( $downloadable_files ) {
							foreach ( $downloadable_files as $key => $file ) {
							?>
								<div class="wfsm-downloads-file">
									<a href="#" class="wfsm-downloads-move"><i class="wfsmico-move"></i></a>
									<span class="wfsm-downloads-file-name"><input type="text" placeholder="<?php _e( 'File Name', 'wfsm' ); ?>" name="wfsm-file-names-<?php echo $curr_dummy; ?>[]" value="<?php echo esc_attr( $file['name'] ); ?>" class="wfsm-collect-data" /></span>
									<span class="wfsm-downloads-file-url"><input type="text" placeholder="<?php _e( "http://", 'wfsm' ); ?>" name="wfsm-file-urls-<?php echo $curr_dummy; ?>[]" value="<?php echo esc_attr( $file['file'] ); ?>" class="wfsm-collect-data" /></span>
									<a href="#" class="wfsm-downloads-file-choose" data-choose="<?php _e( 'Choose file', 'wfsm' ); ?>" data-update="<?php _e( 'Insert file URL', 'wfsm' ); ?>"><?php _e( 'Choose File', 'wfsm' ); ?></a>
									<a href="#" class="wfsm-downloads-file-discard"><i class="wfsmico-discard"></i></a>
								</div>
							<?php
							}
						}
					?>

					</div>
					<a href="#" class="wfms-plain-button wfsm-add-file"><?php _e( 'Add File', 'wfsm'); ?></a>

				<?php
					if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
						$product_limit = ( $limit = get_post_meta( $curr_dummy, '_download_limit', true ) ) ? $limit : '';
					?>
						<label for="wfsm-download-limit-<?php echo $curr_dummy; ?>" class="wfsm-label">
							<span><?php _e( 'Download Limit', 'wfsm' ); ?></span>
							<input id="wfsm-download-limit-<?php echo $curr_dummy; ?>" name="wfsm-download-limit-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_limit; ?>"/>
						</label>

					<?php
						$product_expiry = ( $expiry = get_post_meta( $curr_dummy, '_download_expiry', true ) ) ? $expiry : '';
					?>
						<label for="wfsm-download-expiry-<?php echo $curr_dummy; ?>" class="wfsm-label">
							<span><?php _e( 'Download Expiry', 'wfsm' ); ?></span>
							<input id="wfsm-download-expiry-<?php echo $curr_dummy; ?>" name="wfsm-download-expiry-<?php echo $curr_dummy; ?>" class="wfsm-reset-this wfsm-collect-data" type="number" value="<?php echo $product_expiry; ?>"/>
						</label>

					<?php
					}
				?>

				</div>
		<?php
			}

		?>
		</div>
	<?php

		$out = ob_get_clean();

		die( $out );
		exit;
	}

	function wfsm_save() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$curr_data = array();
		$curr_data = json_decode( stripslashes( $_POST['wfsm_save'] ), true );

		$curr_post['ID'] = $curr_post_id;

		if ( !in_array( 'product_name' , self::$settings['user_permissions'] ) && isset($curr_data['wfsm-product-name']) && $curr_data['wfsm-product-name'] !== '' ) {
			$curr_post['post_title'] = $curr_data['wfsm-product-name'];
		}

		if ( !in_array( 'product_status' , self::$settings['user_permissions'] ) && isset($curr_data['wfsm-product-status']) && $curr_data['wfsm-product-status'] !== '' ) {
			$curr_post['post_status'] = $curr_data['wfsm-product-status'];
		}

		if ( !in_array( 'product_slug' , self::$settings['user_permissions'] ) && isset($curr_data['wfsm-product-slug']) && $curr_data['wfsm-product-slug'] !== '' ) {
			$curr_post['post_name'] = sanitize_title( $curr_data['wfsm-product-slug'] );
		}

		wp_update_post( $curr_post );

		if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) && isset($curr_data['wfsm-featured-image']) ) {
			update_post_meta( $curr_post_id, '_thumbnail_id', $curr_data['wfsm-featured-image'] );
		}

		if ( !in_array( 'product_gallery' , self::$settings['user_permissions'] ) ) {
			if ( isset($curr_data['wfsm-product-gallery']) && $curr_data['wfsm-product-gallery'] !== null && is_array( $curr_data['wfsm-product-gallery'] ) ) {
				if ( count($curr_data['wfsm-product-gallery']) > 1 ) {
					$curr_gallery_images = implode( $curr_data['wfsm-product-gallery'], ',' );
				}
				else {
					$curr_gallery_images = $curr_data['wfsm-product-gallery'][0];
				}
				update_post_meta( $curr_post_id, '_product_image_gallery', $curr_gallery_images );
			}
			else if ( isset($curr_data['wfsm-product-gallery']) && $curr_data['wfsm-product-gallery'] == null ) {
				update_post_meta( $curr_post_id, '_product_image_gallery', '' );
			}
		}

		$save_post = get_post( $curr_post_id );

		if ( $terms = wp_get_object_terms( $curr_post_id, 'product_type' ) ) {
			$product_type = sanitize_title( current( $terms )->name );
		} else {
			$product_type = apply_filters( 'default_product_type', 'simple' );
		}

		if ( !in_array( 'product_cat' , self::$settings['user_permissions'] ) ) {
			if ( isset($curr_data['wfsm-select-product_cat']) && $curr_data['wfsm-select-product_cat'] !== null && is_array( $curr_data['wfsm-select-product_cat'] ) ) {

				$add_terms = array();

				foreach ( $curr_data['wfsm-select-product_cat'] as $curr_tax ) {

					$curr_slug = sanitize_title( $curr_tax );

					if ( in_array( 'product_new_terms' , self::$settings['user_permissions'] ) && !get_term_by( 'slug', $curr_slug, 'product_cat' ) ) {
						continue;
					}

					if ( !get_term_by( 'slug', $curr_slug, 'product_cat' ) ) {
						wp_insert_term( $curr_tax, 'product_cat', array( 'slug' => $curr_tax ) );
					}
					$add_terms[] = $curr_slug;
				}
				wp_set_object_terms( $curr_post_id, $add_terms, 'product_cat' );

			}
			else {
				wp_set_object_terms( $curr_post_id, array(), 'product_cat' );
			}
		}

		if ( !in_array( 'product_tag' , self::$settings['user_permissions'] ) ) {
			if ( isset($curr_data['wfsm-select-product_tag']) && $curr_data['wfsm-select-product_tag'] !== null && is_array( $curr_data['wfsm-select-product_tag'] ) ) {

				$add_terms = array();

				foreach ( $curr_data['wfsm-select-product_tag'] as $curr_tax ) {

					$curr_slug = sanitize_title( $curr_tax );

					if ( in_array( 'product_new_terms' , self::$settings['user_permissions'] ) && !get_term_by( 'slug', $curr_slug, 'product_tag' ) ) {
						continue;
					}

					if ( !get_term_by( 'slug', $curr_slug, 'product_tag' ) ) {
						wp_insert_term( $curr_tax, 'product_tag', array( 'slug' => $curr_tax ) );
					}
					$add_terms[] = $curr_slug;
				}
				wp_set_object_terms( $curr_post_id, $add_terms, 'product_tag' );

			}
			else {
				wp_set_object_terms( $curr_post_id, array(), 'product_tag' );
			}
		}

		if ( !in_array( 'product_attributes' , self::$settings['user_permissions'] ) ) {
			if ( isset($curr_data['wfsm-select-attributes']) && $curr_data['wfsm-select-attributes'] !== null && is_array( $curr_data['wfsm-select-attributes'] ) ) {

				global $wpdb;

				$add_terms = array();

				$i = 0;
				foreach ( $curr_data['wfsm-select-attributes'] as $curr_tax ) {

					$curr_slug = wc_sanitize_taxonomy_name( self::utf8_urldecode( $curr_tax ) );

					if ( in_array( 'product_new_terms' , self::$settings['user_permissions'] ) && !taxonomy_exists( $curr_slug ) ) {
						continue;
					}

					if ( substr($curr_slug, 0, 3) !== 'pa_' && !taxonomy_exists( $curr_slug ) ) {

						$curr_slug_sanitized = sanitize_title( wc_sanitize_taxonomy_name( $curr_tax ) );

						$curr_attribute = array(
							'attribute_label'   => ucfirst( $curr_tax ),
							'attribute_name'    => $curr_slug,
							'attribute_type'    => 'select',
							'attribute_orderby' => 'menu_order',
							'attribute_public'  => 0
						);

						$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $curr_attribute );

						$add_terms['pa_' . $curr_slug_sanitized] = array(
							'name' => 'pa_' . $curr_slug,
							'value' => '',
							'position' => $i,
							'is_visible' => ( isset( $curr_data['wfsm-visible-pa_' . $curr_slug_sanitized] ) && $curr_data['wfsm-visible-pa_' . $curr_slug_sanitized] == 'isvisible' ? '1' : '0' ),
							'is_variation' => ( isset( $curr_data['wfsm-variation-pa_' . $curr_slug_sanitized] ) && $curr_data['wfsm-variation-pa_' . $curr_slug_sanitized] == 'isvariation' ? '1' : '0' ),
							'is_taxonomy' => '1'
						);

						$curr_tax_args = array(
							'label' => ucfirst($curr_tax),
							'rewrite' => array( 'slug' => 'pa_' . $curr_slug ),
							'hierarchical' => true
						);
						register_taxonomy( 'pa_' . $curr_slug, 'product', $curr_tax_args );

						$refresh = 'added';
						$i++;
					}
					else if ( substr($curr_slug, 0, 3) == 'pa_' && taxonomy_exists( $curr_slug ) ) {

						$curr_slug_sanitized = $curr_tax;

						$add_terms[$curr_slug_sanitized] = array(
							'name' => $curr_slug,
							'value' => '',
							'position' => $i,
							'is_visible' => ( isset( $curr_data['wfsm-visible-' . $curr_slug_sanitized] ) && $curr_data['wfsm-visible-' . $curr_slug_sanitized] == 'isvisible' ? '1' : '0' ),
							'is_variation' => ( isset( $curr_data['wfsm-variation-' . $curr_slug_sanitized] ) && $curr_data['wfsm-variation-' . $curr_slug_sanitized] == 'isvariation' ? '1' : '0' ),
							'is_taxonomy' => '1'
						);
						$i++;
					}

				}

				if ( isset($refresh) ) {
					delete_transient( 'wc_attribute_taxonomies' );
				}

				update_post_meta( $curr_post_id, '_product_attributes', $add_terms );

				foreach ($add_terms as $tax_key => $tax) {

					$curr_paname = $tax['name'];
					$curr_paname_sanitized = $tax_key;

					if ( $curr_data['wfsm-select-' . $curr_paname_sanitized] !== null && is_array( $curr_data['wfsm-select-' . $curr_paname_sanitized] ) ) {
						$add_terms = array();

						foreach ( $curr_data['wfsm-select-' . $curr_paname_sanitized] as $curr_tax ) {

							$curr_slug = sanitize_title( $curr_tax );

							if ( in_array( 'product_new_terms' , self::$settings['user_permissions'] ) && !get_term_by( 'slug', $curr_slug, $curr_paname_sanitized ) ) {
								continue;
							}

							if ( !get_term_by( 'slug', $curr_slug, $curr_paname ) ) {
								wp_insert_term( $curr_tax, $curr_paname, array( 'slug' => $curr_slug ) );
							}
							$add_terms[] = $curr_slug;
						}
						wp_set_object_terms( $curr_post_id, $add_terms, $curr_paname );

					}
					else {
						wp_set_object_terms( $curr_post_id, array(), $curr_paname );
					}

				}

			}
			else if ( isset($curr_data['wfsm-select-attributes']) && $curr_data['wfsm-select-attributes'] === null ) {
				update_post_meta( $curr_post_id, '_product_attributes', array() );
			}
		}


		if ( !in_array( 'variable_edit_variations' , self::$settings['user_permissions'] ) && isset($curr_data['wfsm-variations-ids']) && is_array($curr_data['wfsm-variations-ids']) ) {

			$curr_variable_count = 0;
			global $wpdb;

			foreach ( $curr_data['wfsm-variations-ids'] as $curr_variation ) {

				$variation_post_title = sprintf( __( 'Variation #%s of %s', 'wfsm' ), absint( $curr_variation ), esc_html( get_the_title( $curr_post_id ) ) );

				$wpdb->update( $wpdb->posts, array( 'post_title' => $variation_post_title, 'menu_order' => $curr_variable_count ), array( 'ID' => $curr_variation ) );

				do_action( 'woocommerce_update_product_variation', $curr_variation );

				if ( !in_array( 'variable_product_attributes' , self::$settings['user_permissions'] ) ) {

					$attributes = (array) maybe_unserialize( get_post_meta( $curr_post_id, '_product_attributes', true ) );

					$updated_attribute_keys = array();
					foreach ( $attributes as $attribute ) {

						if ( $attribute['is_variation'] ) {

							$attribute_key = 'attribute_' . sanitize_title( $attribute['name'] );

							$value = isset( $curr_data[ 'wfsm-attribute-' . $curr_variation . '-' . sanitize_title( $attribute['name'] ) ] ) ? $curr_data[ 'wfsm-attribute-' . $curr_variation . '-' . sanitize_title( stripslashes( $attribute['name'] ) ) ] : '';

							$updated_attribute_keys[] = $attribute_key;

							update_post_meta( $curr_variation, $attribute_key, $value );

						}
					}

					$delete_attribute_keys = $wpdb->get_col( $wpdb->prepare( "SELECT meta_key FROM {$wpdb->postmeta} WHERE meta_key LIKE 'attribute_%%' AND meta_key NOT IN ( '" . implode( "','", $updated_attribute_keys ) . "' ) AND post_id = %d;", $curr_variation ) );

					foreach ( $delete_attribute_keys as $key ) {
						delete_post_meta( $curr_variation, $key );
					}

				}

				$curr_variable_count++;

			}

		}

		self::wfsm_save_product_data( $curr_post_id, $save_post , $curr_data, $product_type );

		$wfsm_settings = explode( '|', $_POST['wfsm_loop'] );

		$out = '1';

		if ( $_POST['wfsm_loop'] !== 'single' && is_array( $wfsm_settings ) ) {

			global $woocommerce_loop;

			$woocommerce_loop = array(
				'loop' => $wfsm_settings[0]-1,
				'columns' => $wfsm_settings[1]
			);

			$curr_products = new WP_Query( array( 'post_type' => 'product', 'post__in' => array( $curr_post_id ) ) );

			ob_start();

			if ( $curr_products->have_posts() ) {

				while ( $curr_products->have_posts() ) : $curr_products->the_post();

					if ( $_POST['wfsm_loop'] !== 'single' ) {
						wc_get_template_part( 'content', 'product' );
					}
					else {
						$out = 'single';
					}

				endwhile;

			}

			$out = ob_get_clean();

		}

		die($out);
		exit;
	}

	function wfsm_clone() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$post = self::wfsm_get_product_to_duplicate( $curr_post_id );

		$duplicate = self::wfsm_duplicate_product( $post );

		die('1');
		exit;

	}

	function wfsm_trash() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset( $_POST['wfsm_mode'] ) && $_POST['wfsm_mode'] == 'variation' ) {
			wp_delete_post($curr_post_id);
		}
		else {
			wp_trash_post($curr_post_id);
		}

		die('1');
		exit;

	}

	function wfsm_editor() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$post = get_post( $curr_post_id, OBJECT, 'edit' );

		if ( $_POST['wfsm_mode'] == 'content' ) {
			$content = $post->post_content;
		}
		else {
			$content = $post->post_excerpt;
		}

		ob_start();

	?>
		<div class="wfsm-editor-buttons" data-id="<?php echo $curr_post_id; ?>" data-mode="<?php echo $_POST['wfsm_mode']; ?>">
			<a href="#" class="wfsm-editor-save" title="<?php _e( 'Save changes', 'wfsm' ); ?>"><i class="wfsmico-save"></i></a>
			<a href="#" class="wfsm-editor-discard" title="<?php _e( 'Discard changes', 'wfsm' ); ?>"><i class="wfsmico-discard"></i></a>
		</div>
		<script type="text/javascript">
			if(typeof ajaxurl === 'undefined') {
				var ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>';
			}
		</script>
	<?php

		wp_editor( $content, 'wfsm-tinymce' );

		$out = ob_get_clean();

		die($out);
		exit;

	}

	function wfsm_editor_save() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$curr_args = array(
			'ID' => $curr_post_id
		);

		if ( $_POST['wfsm_mode'] == 'content' ) {
			$curr_args['post_content'] = $_POST['wfsm_content'];
		}
		else {
			$curr_args['post_excerpt'] = $_POST['wfsm_content'];
		}

		wp_update_post($curr_args);

		die('1');
		exit;

	}

	function wfsm_create_attribute() {

		if ( ( defined('DOING_AJAX') && DOING_AJAX ) === false ) {
			die( 'Error!' );
			exit;
		}

		if ( isset($_POST) && isset( $_POST['wfsm_id'] ) ) {
			$curr_post_id = intval( stripslashes( $_POST['wfsm_id'] ) );
			if ( get_post_status( $curr_post_id ) === false ) {
				die( 'Error!' );
				exit;
			}
		}
		else {
			die( 'Error!' );
			exit;
		}

		$curr_post_author = self::wfsm_check_premissions( $curr_post_id );

		if ( $curr_post_author === false ) {
			die( 'Error!' );
			exit;
		}

		$product = wc_get_product( $curr_post_id );

			ob_start();

			$curr_slug = wc_sanitize_taxonomy_name( $_POST['wfsm_add'] );

			if ( substr($curr_slug, 0, 3) !== 'pa_' && !taxonomy_exists( $curr_slug ) ) {
				$curr_slug_sanitized = sanitize_title ( $curr_slug );
				$curr_name = $curr_slug;
				$curr_paname = 'pa_' . $curr_slug;
				$curr_paname_desanitized = 'pa_' . sanitize_title ( $curr_slug );
			}
			else if ( substr($curr_slug, 0, 3) == 'pa_' && taxonomy_exists( $curr_slug ) ) {
				$tax = get_taxonomy( $curr_slug );
				$curr_name = $tax->label;
				$curr_paname = $curr_slug;
				$curr_paname_desanitized = sanitize_title( $tax->name );
			}
		?>
			<div class="wfsm-attribute-<?php echo $curr_paname_desanitized; ?>">
				<label for="wfsm-select-<?php echo $curr_paname_desanitized; ?>" class="wfsm-selectize">
					<span><?php echo __( 'Product', 'wfsm' ) . ' ' . ucfirst( $curr_name ); ?></span>
					<select id="wfsm-select-<?php echo $curr_paname_desanitized; ?>" name="wfsm-select-<?php echo $curr_paname_desanitized; ?>" class="wfsm-collect-data" multiple="multiple">
					<?php
							if ( isset($tax) ) {
							$product_atts = wp_get_post_terms( $curr_post_id, $curr_paname, array( 'fields' => 'slugs' ) );
							foreach( get_terms($curr_paname,'hide_empty=0') as $term ) {
								$wfsm_selected = in_array( $term->slug , $product_atts ) ? 'added' : 'notadded' ;
							?>
								<option <?php echo ( $wfsm_selected == 'added' ? ' selected="selected"' : '' ); ?> value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
							<?php
							}
						}

					?>
					</select>
				</label>
			<?php
				$product_attributes = $product->get_attributes();
				$curr_value = ( ( isset( $product_attributes[$curr_paname_desanitized]) && $product_attributes[$curr_paname_desanitized]['is_visible'] == 1 ) ? 'isvisible' : 'notvisible' );
			?>
				<label for="wfsm-visible-<?php echo $curr_paname_desanitized; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
					<span class="wfsm-show-isvisible"><?php _e( 'Attribute is visible on product page', 'wfsm' ); ?></span>
					<span class="wfsm-show-notvisible"><?php _e( 'Attribute is not visible on product page', 'wfsm' ); ?></span>
					<input id="wfsm-visible-<?php echo $curr_paname_desanitized; ?>" name="wfsm-visible-<?php echo $curr_paname_desanitized; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
				</label>
			<?php
				if ( $product->is_type( 'variable' ) ) {
					$curr_value = ( ( isset( $product_attributes[$curr_paname_desanitized]) && $product_attributes[$curr_paname_desanitized]['is_variation'] == 1 ) ? 'isvariation' : 'notvariation' );
			?>
				<label for="wfsm-variation-<?php echo $curr_paname_desanitized; ?>" class="wfsm-label-checkbox wfsm-<?php echo $curr_value; ?>">
					<span class="wfsm-show-isvariation"><?php _e( 'Attribute is used in variations', 'wfsm' ); ?></span>
					<span class="wfsm-show-notvariation"><?php _e( 'Attribute is not used in variations', 'wfsm' ); ?></span>
					<input id="wfsm-variation-<?php echo $curr_paname_desanitized; ?>" name="wfsm-variation-<?php echo $curr_paname_desanitized; ?>" class="wfsm-reset-this wfsm-collect-data" type="hidden" value="<?php echo $curr_value; ?>"/>
				</label>
			<?php
				}
		?>
			</div>
		<?php

			$out = ob_get_clean();

			die($out);
			exit;

	}

	public static function wfsm_duplicate_product( $post, $parent = 0, $post_status = '' ) {

		global $wpdb;

		$new_post_author    = get_current_user_id();
		$new_post_date      = current_time( 'mysql' );
		$new_post_date_gmt  = get_gmt_from_date( $new_post_date );

		if ( $parent > 0 ) {
			$post_parent        = $parent;
			$post_status        = $post_status ? $post_status : 'publish';
			$suffix             = '';
		} else {
			$post_parent        = $post->post_parent;
			$post_status        = $post_status ? $post_status : 'draft';
			$suffix             = ' ' . __( '(Copy)', 'wfsm' );
		}

		$wpdb->insert(
			$wpdb->posts,
			array(
				'post_author'               => $new_post_author,
				'post_date'                 => $new_post_date,
				'post_date_gmt'             => $new_post_date_gmt,
				'post_content'              => $post->post_content,
				'post_content_filtered'     => $post->post_content_filtered,
				'post_title'                => $post->post_title . $suffix,
				'post_excerpt'              => $post->post_excerpt,
				'post_status'               => $post_status,
				'post_type'                 => $post->post_type,
				'comment_status'            => $post->comment_status,
				'ping_status'               => $post->ping_status,
				'post_password'             => $post->post_password,
				'to_ping'                   => $post->to_ping,
				'pinged'                    => $post->pinged,
				'post_modified'             => $new_post_date,
				'post_modified_gmt'         => $new_post_date_gmt,
				'post_parent'               => $post_parent,
				'menu_order'                => $post->menu_order,
				'post_mime_type'            => $post->post_mime_type
			)
		);

		$new_post_id = $wpdb->insert_id;

		$post_type = $post->post_type;

		$taxonomies = get_object_taxonomies( $post_type );

		foreach ( $taxonomies as $taxonomy ) {

			$post_terms = wp_get_object_terms( $post->ID, $taxonomy );
			$post_terms_count = sizeof( $post_terms );

			for ( $i=0; $i<$post_terms_count; $i++ ) {
				wp_set_object_terms( $new_post_id, $post_terms[$i]->slug, $taxonomy, true );
			}
		}

		$post_meta_infos = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=%d AND meta_key NOT IN ( 'total_sales' );", absint( $post->ID ) ) );

		if ( count( $post_meta_infos ) != 0 ) {

			$sql_query_sel = array();
			$sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";

			foreach ( $post_meta_infos as $meta_info ) {
				$meta_key = $meta_info->meta_key;
				$meta_value = addslashes( $meta_info->meta_value );
				$sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
			}

			$sql_query.= implode( " UNION ALL ", $sql_query_sel );
			$wpdb->query($sql_query);
		}

		if ( $children_products = get_children( 'post_parent='.$post->ID.'&post_type=product_variation' ) ) {

			if ( $children_products ) {

				foreach ( $children_products as $child ) {
					self::wfsm_duplicate_product( self::wfsm_get_product_to_duplicate( $child->ID ), $new_post_id, $child->post_status );
				}
			}
		}

		update_post_meta( $new_post_id, '_visibility', 'visible' );

		do_action( 'woocommerce_duplicate_product', $new_post_id, $post );

		return $new_post_id;
	}

	public static function wfsm_get_product_to_duplicate( $id ) {
		global $wpdb;

		$id = absint( $id );

		if ( ! $id ) {
			return false;
		}

		$post = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE ID=$id" );

		if ( isset( $post->post_type ) && $post->post_type == "revision" ) {
			$id   = $post->post_parent;
			$post = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE ID=$id" );
		}

		return $post[0];
	}

	public static function wfsm_check_premissions( $curr_post_id ) {

		$curr_post_author = absint( get_post_field( 'post_author', $curr_post_id ) );

		$curr_logged_user = get_current_user_id();

		$curr_user = get_user_by( 'id', $curr_logged_user );

		if ( $curr_user->has_cap( 'administrator' ) || $curr_user->has_cap( 'manage_woocommerce' ) ) {
			$curr_admin = true;
		}

		if ( !isset( $curr_admin ) && absint( $curr_post_author ) !== $curr_logged_user ) {
			return false;
		}
		else {
			return $curr_post_author;
		}

	}

	public static function product_exist( $sku ) {
		global $wpdb;
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value= %s LIMIT 1", $sku ) );
		return $product_id;
	}
	
	public static function utf8_urldecode($str) {
		$str = preg_replace("/%u([0-9a-f]{3,4})/i","&#x\\1;",urldecode($str));
		return html_entity_decode($str,null,'UTF-8');
	}

	function wfsm_save_product_data( $post_id, $post, $curr_data, $product_type ) {

		global $wpdb;

		add_post_meta( $post_id, 'total_sales', '0', true );

		if ( !in_array( 'product_prices' , self::$settings['user_permissions'] ) ) {
			if ( isset( $curr_data['wfsm-regular-price'] ) ) {
				update_post_meta( $post_id, '_regular_price', ( $curr_data['wfsm-regular-price'] === '' ) ? '' : wc_format_decimal( $curr_data['wfsm-regular-price'] ) );
			}

			if ( isset( $curr_data['wfsm-sale-price'] ) ) {
				update_post_meta( $post_id, '_sale_price', ( $curr_data['wfsm-sale-price'] === '' ? '' : wc_format_decimal( $curr_data['wfsm-sale-price'] ) ) );
			}

			if ( '' !== $curr_data['wfsm-sale-price'] ) {
				update_post_meta( $post_id, '_price', wc_format_decimal( $curr_data['wfsm-sale-price'] ) );
			} else {
				update_post_meta( $post_id, '_price', ( $curr_data['wfsm-regular-price'] === '' ) ? '' : wc_format_decimal( $curr_data['wfsm-regular-price'] ) );
			}

		}


		if ( !in_array( 'product_taxes' , self::$settings['user_permissions'] ) ) {
			if ( isset( $curr_data['wfsm-tax-status'] ) ) {
				update_post_meta( $post_id, '_tax_status', wc_clean( $curr_data['wfsm-tax-status'] ) );
			}

			if ( isset( $curr_data['wfsm-tax-class'] ) ) {
				update_post_meta( $post_id, '_tax_class', wc_clean( $curr_data['wfsm-tax-class'] ) );
			}
		}

		if ( !in_array( 'product_note' , self::$settings['user_permissions'] ) ) {
			if ( isset( $curr_data['wfsm-purchase-note'] ) ) {
				update_post_meta( $post_id, '_purchase_note', wp_kses_post( stripslashes( $curr_data['wfsm-purchase-note'] ) ) );
			}
		}

		if ( !in_array( 'product_feature' , self::$settings['user_permissions'] ) ) {
			if ( update_post_meta( $post_id, '_featured', isset( $curr_data['wfsm-featured'] ) && $curr_data['wfsm-featured'] == 'yes' ? 'yes' : 'no' ) ) {
				delete_transient( 'wc_featured_products' );
			}
		}

		$is_virtual = isset( $curr_data['wfsm-virtual'] ) && $curr_data['wfsm-virtual'] == 'yes' ? 'yes' : 'no';

		if ( !in_array( 'product_virtual' , self::$settings['user_permissions'] ) ) {
			update_post_meta( $post_id, '_virtual', $is_virtual );
		}

		if ( !in_array( 'product_shipping' , self::$settings['user_permissions'] ) ) {
			if ( 'no' == $is_virtual ) {

				if ( isset( $curr_data['wfsm-weight'] ) ) {
					update_post_meta( $post_id, '_weight', ( '' === $curr_data['wfsm-weight'] ) ? '' : wc_format_decimal( $curr_data['wfsm-weight'] ) );
				}

				if ( isset( $curr_data['wfsm-length'] ) ) {
					update_post_meta( $post_id, '_length', ( '' === $curr_data['wfsm-length'] ) ? '' : wc_format_decimal( $curr_data['wfsm-length'] ) );
				}

				if ( isset( $curr_data['wfsm-width'] ) ) {
					update_post_meta( $post_id, '_width', ( '' === $curr_data['wfsm-width'] ) ? '' : wc_format_decimal( $curr_data['wfsm-width'] ) );
				}

				if ( isset( $curr_data['wfsm-height'] ) ) {
					update_post_meta( $post_id, '_height', ( '' === $curr_data['wfsm-height'] ) ? '' : wc_format_decimal( $curr_data['wfsm-height'] ) );
				}

			} else {
				update_post_meta( $post_id, '_weight', '' );
				update_post_meta( $post_id, '_length', '' );
				update_post_meta( $post_id, '_width', '' );
				update_post_meta( $post_id, '_height', '' );
			}

			$product_shipping_class = $curr_data['wfsm-shipping-class'] > 0 && $product_type != 'external' ? absint( $curr_data['wfsm-shipping-class'] ) : '';
			wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class');
		}

		if ( !in_array( 'product_sku' , self::$settings['user_permissions'] ) ) {
			$sku     = get_post_meta( $post_id, '_sku', true );
			$new_sku = wc_clean( stripslashes( $curr_data['wfsm-sku'] ) );

			if ( '' == $new_sku ) {
				update_post_meta( $post_id, '_sku', '' );
			} elseif ( $new_sku !== $sku ) {

				if ( ! empty( $new_sku ) ) {

					$unique_sku = wc_product_has_unique_sku( $post_id, $new_sku );

					if ( ! $unique_sku ) {
						//WC_Admin_Meta_Boxes::add_error( __( 'Product SKU must be unique.', 'wfsm' ) );
					} else {
						update_post_meta( $post_id, '_sku', $new_sku );
					}

					if ( $unique_sku ) {
						update_post_meta( $post_id, '_sku', $new_sku );
					}

				} else {
					update_post_meta( $post_id, '_sku', '' );
				}
			}
		}

		if ( !in_array( 'product_schedule_sale' , self::$settings['user_permissions'] ) ) {
			if ( in_array( $product_type, array( 'variable', 'grouped' ) ) ) {

				update_post_meta( $post_id, '_regular_price', '' );
				update_post_meta( $post_id, '_sale_price', '' );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
				update_post_meta( $post_id, '_price', '' );

			} else {

				$date_from = isset( $curr_data['wfsm-schedule-sale-start'] ) ? wc_clean( $curr_data['wfsm-schedule-sale-start'] ) : '';
				$date_to   = isset( $curr_data['wfsm-schedule-sale-end'] ) ? wc_clean( $curr_data['wfsm-schedule-sale-end'] ) : '';

				if ( $date_from ) {
					update_post_meta( $post_id, '_sale_price_dates_from', strtotime( $date_from ) );
				} else {
					update_post_meta( $post_id, '_sale_price_dates_from', '' );
				}

				if ( $date_to ) {
					update_post_meta( $post_id, '_sale_price_dates_to', strtotime( $date_to ) );
				} else {
					update_post_meta( $post_id, '_sale_price_dates_to', '' );
				}

				if ( $date_to && ! $date_from ) {
					update_post_meta( $post_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
				}

				if ( '' !== $curr_data['wfsm-sale-price'] && '' == $date_to && '' == $date_from ) {
					update_post_meta( $post_id, '_price', wc_format_decimal( $curr_data['wfsm-sale-price'] ) );
				} else {
					update_post_meta( $post_id, '_price', ( $curr_data['wfsm-regular-price'] === '' ) ? '' : wc_format_decimal( $curr_data['wfsm-regular-price'] ) );
				}

				if ( '' !== $curr_data['wfsm-sale-price'] && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					update_post_meta( $post_id, '_price', wc_format_decimal( $curr_data['wfsm-sale-price'] ) );
				}

				if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
					update_post_meta( $post_id, '_price', ( $curr_data['wfsm-regular-price'] === '' ) ? '' : wc_format_decimal( $curr_data['wfsm-regular-price'] ) );
					update_post_meta( $post_id, '_sale_price_dates_from', '' );
					update_post_meta( $post_id, '_sale_price_dates_to', '' );
				}
			}
		}

		if ( !in_array( 'product_grouping' , self::$settings['user_permissions'] ) ) {
			if ( $post->post_parent > 0 || 'grouped' == $product_type || $curr_data['wfsm-grouping'] > 0 ) {

				$clear_parent_ids = array();

				if ( $post->post_parent > 0 ) {
					$clear_parent_ids[] = $post->post_parent;
				}

				if ( 'grouped' == $product_type ) {
					$clear_parent_ids[] = $post_id;
				}

				if ( $curr_data['wfsm-grouping'] > 0 ) {
					$clear_parent_ids[] = absint( $curr_data['wfsm-grouping'] );
				}

				if ( $clear_parent_ids ) {
					foreach ( $clear_parent_ids as $clear_id ) {
						$children_by_price = get_posts( array(
							'post_parent'    => $clear_id,
							'orderby'        => 'meta_value_num',
							'order'          => 'asc',
							'meta_key'       => '_price',
							'posts_per_page' => 1,
							'post_type'      => 'product',
							'fields'         => 'ids'
						) );

						if ( $children_by_price ) {
							foreach ( $children_by_price as $child ) {
								$child_price = get_post_meta( $child, '_price', true );
								update_post_meta( $clear_id, '_price', $child_price );
							}
						}

						wc_delete_product_transients( $clear_id );
					}
				}
			}
		}

		if ( !in_array( 'product_grouping' , self::$settings['user_permissions'] ) ) {
			if ( isset( $curr_data['wfsm-sold-individually'] ) && $curr_data['wfsm-sold-individually'] == 'yes' ) {
				update_post_meta( $post_id, '_sold_individually', 'yes' );
			} else {
				update_post_meta( $post_id, '_sold_individually', '' );
			}
		}

		if ( !in_array( 'product_stock' , self::$settings['user_permissions'] ) ) {

			if ( !isset( $curr_data['wfsm-product-stock'] ) ) {
				$curr_data['wfsm-product-stock'] = '';
			}
			if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {

				$manage_stock = 'no';
				$backorders   = 'no';
				$stock_status = wc_clean( $curr_data['wfsm-product-stock'] );

				if ( 'external' === $product_type ) {

					$stock_status = 'instock';

				} elseif ( 'variable' === $product_type ) {

					$stock_status = '';

					if ( $curr_data['wfsm-manage-stock-quantity'] == 'yes' ) {
						$manage_stock = 'yes';
						$backorders   = wc_clean( $curr_data['wfsm-backorders'] );
					}

				} elseif ( 'grouped' !== $product_type && $curr_data['wfsm-manage-stock-quantity'] == 'yes' ) {
					$manage_stock = 'yes';
					$backorders   = wc_clean( $curr_data['wfsm-backorders'] );
				}

				update_post_meta( $post_id, '_manage_stock', $manage_stock );
				update_post_meta( $post_id, '_backorders', $backorders );

				if ( $stock_status ) {
					wc_update_product_stock_status( $post_id, $stock_status );
				}

				if ( $curr_data['wfsm-manage-stock-quantity'] == 'yes' ) {
					wc_update_product_stock( $post_id, wc_stock_amount( $curr_data['wfsm-stock-quantity'] ) );
				} else {
					update_post_meta( $post_id, '_stock', '' );
				}

			} else {
				wc_update_product_stock_status( $post_id, wc_clean( $curr_data['wfsm-product-stock'] ) );
			}
		}

		$is_downloadable = isset( $curr_data['wfsm-downloadable'] ) && $curr_data['wfsm-downloadable'] == 'yes' ? 'yes' : 'no';

		if ( !in_array( 'product_downloadable' , self::$settings['user_permissions'] ) ) {
			update_post_meta( $post_id, '_downloadable', $is_downloadable );
		}

		if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) {

			if ( 'yes' == $is_downloadable ) {

				$_download_limit = isset( $curr_data['wfsm-download-limit'] ) ? absint( $curr_data['wfsm-download-limit'] ) : null;
				if ( ! $_download_limit ) {
					$_download_limit = '';
				}

				$_download_expiry = isset( $curr_data['wfsm-download-expiry'] ) ? absint( $curr_data['wfsm-download-expiry'] ) : null;
				if ( ! $_download_expiry ) {
					$_download_expiry = '';
				}

				$files = array();

				if ( isset( $curr_data['wfsm-file-urls'] ) ) {
					$file_names         = isset( $curr_data['wfsm-file-names'] ) ? $curr_data['wfsm-file-names'] : array();
					$file_urls          = isset( $curr_data['wfsm-file-urls'] )  ? array_map( 'trim', $curr_data['wfsm-file-urls'] ) : array();
					$file_url_size      = sizeof( $file_urls );
					$allowed_file_types = get_allowed_mime_types();

					for ( $i = 0; $i < $file_url_size; $i ++ ) {
						if ( ! empty( $file_urls[ $i ] ) ) {

							if ( 0 === strpos( $file_urls[ $i ], 'http' ) ) {
								$file_is  = 'absolute';
								$file_url = esc_url_raw( $file_urls[ $i ] );
							} elseif ( '[' === substr( $file_urls[ $i ], 0, 1 ) && ']' === substr( $file_urls[ $i ], -1 ) ) {
								$file_is  = 'shortcode';
								$file_url = wc_clean( $file_urls[ $i ] );
							} else {
								$file_is = 'relative';
								$file_url = wc_clean( $file_urls[ $i ] );
							}

							$file_name = wc_clean( $file_names[ $i ] );
							$file_hash = md5( $file_url );

							if ( in_array( $file_is, array( 'absolute', 'relative' ) ) ) {
								$file_type  = wp_check_filetype( strtok( $file_url, '?' ) );
								$parsed_url = parse_url( $file_url, PHP_URL_PATH );
								$extension  = pathinfo( $parsed_url, PATHINFO_EXTENSION );

								if ( ! empty( $extension ) && ! in_array( $file_type['type'], $allowed_file_types ) ) {
									//WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The downloadable file %s cannot be used as it does not have an allowed file type. Allowed types include: %s', 'wfsm' ), '<code>' . basename( $file_url ) . '</code>', '<code>' . implode( ', ', array_keys( $allowed_file_types ) ) . '</code>' ) );
									continue;
								}
							}

							if ( 'relative' === $file_is ) {
								$_file_url = '..' === substr( $file_url, 0, 2 ) ? realpath( ABSPATH . $file_url ) : $file_url;

								if ( ! apply_filters( 'woocommerce_downloadable_file_exists', file_exists( $_file_url ), $file_url ) ) {
									//WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The downloadable file %s cannot be used as it does not exist on the server.', 'wfsm' ), '<code>' . $file_url . '</code>' ) );
									continue;
								}
							}

							$files[ $file_hash ] = array(
								'name' => $file_name,
								'file' => $file_url
							);
						}
					}
				}

				do_action( 'woocommerce_process_product_file_download_paths', $post_id, 0, $files );

				update_post_meta( $post_id, '_downloadable_files', $files );

				if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
					update_post_meta( $post_id, '_download_limit', $_download_limit );
					update_post_meta( $post_id, '_download_expiry', $_download_expiry );

					if ( isset( $curr_data['wfsm-download-type'] ) ) {
						update_post_meta( $post_id, '_download_type', wc_clean( $curr_data['wfsm-download-type'] ) );
					}
				}
			}
		}

		if ( 'external' == $product_type ) {

			if ( !in_array( 'external_product_url' , self::$settings['user_permissions'] ) ) {
				if ( isset( $curr_data['wfsm-product-http'] ) ) {
					update_post_meta( $post_id, '_product_url', esc_url_raw( $curr_data['wfsm-product-http'] ) );
				}
			}

			if ( !in_array( 'external_button_text' , self::$settings['user_permissions'] ) ) {
				if ( isset( $curr_data['wfsm-button-text'] ) ) {
					update_post_meta( $post_id, '_button_text', wc_clean( $curr_data['wfsm-button-text'] ) );
				}
			}
		}

		if ( 'variable' == $product_type ) {
			self::save_variation_product_data( $post_id, $post, $curr_data );
		}

		do_action( 'woocommerce_process_product_meta_' . $product_type, $post_id );

		wc_delete_product_transients( $post_id );

	}

	public static function save_variation_product_data( $post_id, $post, $curr_data ) {

		global $wpdb;

		$attributes = (array) maybe_unserialize( get_post_meta( $post_id, '_product_attributes', true ) );

		if ( isset( $curr_data['wfsm-variations-ids'] ) ) {

			$variable_post_id               = $curr_data['wfsm-variations-ids'];

			$max_loop = max( array_keys( $variable_post_id ) );

			for ( $i = 0; $i <= $max_loop; $i ++ ) {
				if ( ! isset( $variable_post_id[ $i ] ) ) {
					continue;
				}

				$variation_id = absint( $variable_post_id[ $i ] );

				$variable_sku[$i]                   = isset( $curr_data['wfsm-sku-' . $variation_id] ) ? $curr_data['wfsm-sku-' . $variation_id] : '';
				$variable_regular_price[$i]         = isset( $curr_data['wfsm-regular-price-' . $variation_id] ) ? $curr_data['wfsm-regular-price-' . $variation_id] : '';
				$variable_sale_price[$i]            = isset( $curr_data['wfsm-sale-price-' . $variation_id] ) ? $curr_data['wfsm-sale-price-' . $variation_id] : '';
				$upload_image_id[$i]                = isset( $curr_data['wfsm-featured-image-' . $variation_id] ) ? $curr_data['wfsm-featured-image-' . $variation_id] : '';
				$variable_download_limit[$i]        = isset( $curr_data['wfsm-download-limit-' . $variation_id] ) ? $curr_data['wfsm-download-limit-' . $variation_id] : '';
				$variable_download_expiry[$i]       = isset( $curr_data['wfsm-download-expiry-' . $variation_id] ) ? : '';
				$variable_shipping_class[$i]        = isset( $curr_data['wfsm-shipping-class-' . $variation_id] ) ? $curr_data['wfsm-shipping-class-' . $variation_id] : '';
				$variable_tax_class[$i]             = isset( $curr_data['wfsm-tax-class-' . $variation_id] ) ? $curr_data['wfsm-tax-class-' . $variation_id] : '';
				$variable_sale_price_dates_from[$i] = isset( $curr_data['wfsm-schedule-sale-start-' . $variation_id] ) ? $curr_data['wfsm-schedule-sale-start-' . $variation_id] : '';
				$variable_sale_price_dates_to[$i]   = isset( $curr_data['wfsm-schedule-sale-end-' . $variation_id] ) ? $curr_data['wfsm-schedule-sale-end-' . $variation_id] : '';

				$variable_weight[$i]                = isset( $curr_data['wfsm-weight-' . $variation_id] ) ? $curr_data['wfsm-weight-' . $variation_id] : '';
				$variable_length[$i]                = isset( $curr_data['wfsm-length-' . $variation_id] ) ? $curr_data['wfsm-length-' . $variation_id] : '';
				$variable_width[$i]                 = isset( $curr_data['wfsm-width-' . $variation_id] ) ? $curr_data['wfsm-width-' . $variation_id] : '';
				$variable_height[$i]                = isset( $curr_data['wfsm-height-' . $variation_id] ) ? $curr_data['wfsm-height-' . $variation_id] : '';
				$variable_is_virtual[$i]            = isset( $curr_data['wfsm-virtual-' . $variation_id] ) ? $curr_data['wfsm-virtual-' . $variation_id] : '';
				$variable_is_downloadable[$i]       = isset( $curr_data['wfsm-downloadable-' . $variation_id] ) ? $curr_data['wfsm-downloadable-' . $variation_id] : '';

				$variable_manage_stock[$i]          = isset( $curr_data['wfsm-manage-stock-quantity-' . $variation_id] ) ? 'wfsm-manage-stock-quantity-' . $variation_id : '';
				$variable_stock[$i]                 = isset( $curr_data['wfsm-stock-quantity-' . $variation_id] ) ? $curr_data['wfsm-stock-quantity-' . $variation_id] : '';
				$variable_backorders[$i]            = isset( $curr_data['wfsm-backorders-' . $variation_id] ) ? $curr_data['wfsm-backorders-' . $variation_id] : '';
				$variable_stock_status[$i]          = isset( $curr_data['wfsm-product-stock-' . $variation_id] ) ? $curr_data['wfsm-product-stock-' . $variation_id] : '';

			}


			for ( $i = 0; $i <= $max_loop; $i ++ ) {

				if ( ! isset( $variable_post_id[ $i ] ) ) {
					continue;
				}

				$variation_id = absint( $variable_post_id[ $i ] );

				if ( ! $variation_id ) {
					continue;
				}


				if ( !in_array( 'product_sku' , self::$settings['user_permissions'] ) ) {
					$sku     = get_post_meta( $variation_id, '_sku', true );
					$new_sku = wc_clean( stripslashes( $variable_sku[ $i ] ) );

					if ( '' == $new_sku ) {
						update_post_meta( $variation_id, '_sku', '' );
					} elseif ( $new_sku !== $sku ) {

						if ( ! empty( $new_sku ) ) {
							$unique_sku = wc_product_has_unique_sku( $variation_id, $new_sku );

							if ( ! $unique_sku ) {
								//WC_Admin_Meta_Boxes::add_error( __( 'Variation SKU must be unique.', 'wfsm' ) );
							} else {
								update_post_meta( $variation_id, '_sku', $new_sku );
							}
						} else {
							update_post_meta( $variation_id, '_sku', '' );
						}
					}
				}

				if ( !in_array( 'product_featured_image' , self::$settings['user_permissions'] ) ) {
					update_post_meta( $variation_id, '_thumbnail_id', absint( $upload_image_id[ $i ] ) );
				}

				$is_virtual          = isset( $variable_is_virtual[ $i ] ) && $variable_is_virtual[ $i ] == 'yes' ? 'yes' : 'no';
				if ( !in_array( 'product_virtual' , self::$settings['user_permissions'] ) ) {
					update_post_meta( $variation_id, '_virtual', wc_clean( $is_virtual ) );
				}

				if ( !in_array( 'product_shipping' , self::$settings['user_permissions'] ) ) {
					if ( isset( $variable_weight[ $i ] ) ) {
						update_post_meta( $variation_id, '_weight', ( '' === $variable_weight[ $i ] ) ? '' : wc_format_decimal( $variable_weight[ $i ] ) );
					}

					if ( isset( $variable_length[ $i ] ) ) {
						update_post_meta( $variation_id, '_length', ( '' === $variable_length[ $i ] ) ? '' : wc_format_decimal( $variable_length[ $i ] ) );
					}

					if ( isset( $variable_width[ $i ] ) ) {
						update_post_meta( $variation_id, '_width', ( '' === $variable_width[ $i ] ) ? '' : wc_format_decimal( $variable_width[ $i ] ) );
					}

					if ( isset( $variable_height[ $i ] ) ) {
						update_post_meta( $variation_id, '_height', ( '' === $variable_height[ $i ] ) ? '' : wc_format_decimal( $variable_height[ $i ] ) );
					}

					$variable_shipping_class[ $i ] = ! empty( $variable_shipping_class[ $i ] ) ? (int) $variable_shipping_class[ $i ] : '';
					wp_set_object_terms( $variation_id, $variable_shipping_class[ $i ], 'product_shipping_class');
				}

				if ( !in_array( 'product_stock' , self::$settings['user_permissions'] ) ) {

					$manage_stock        = isset( $variable_manage_stock[ $i ] ) && $variable_manage_stock[ $i ] == 'yes' ? 'yes' : 'no';
					update_post_meta( $variation_id, '_manage_stock', $manage_stock );

					if ( ! empty( $variable_stock_status[ $i ] ) ) {
						wc_update_product_stock_status( $variation_id, $variable_stock_status[ $i ] );
					}

					if ( 'yes' === $manage_stock ) {
						update_post_meta( $variation_id, '_backorders', wc_clean( $variable_backorders[ $i ] ) );
						wc_update_product_stock( $variation_id, wc_stock_amount( $variable_stock[ $i ] ) );
					} else {
						delete_post_meta( $variation_id, '_backorders' );
						delete_post_meta( $variation_id, '_stock' );
					}
				}

				$regular_price = wc_format_decimal( $variable_regular_price[ $i ] );
				$sale_price    = $variable_sale_price[ $i ] === '' ? '' : wc_format_decimal( $variable_sale_price[ $i ] );

				if ( !in_array( 'product_prices' , self::$settings['user_permissions'] ) ) {

					update_post_meta( $variation_id, '_regular_price', $regular_price );
					update_post_meta( $variation_id, '_sale_price', $sale_price );

					if ( '' !== $sale_price ) {
						update_post_meta( $variation_id, '_price', $sale_price );
					} else {
						update_post_meta( $variation_id, '_price', $regular_price );
					}
				}

				if ( !in_array( 'product_schedule_sale' , self::$settings['user_permissions'] ) ) {

					$date_from     = wc_clean( $variable_sale_price_dates_from[ $i ] );
					$date_to       = wc_clean( $variable_sale_price_dates_to[ $i ] );

					update_post_meta( $variation_id, '_sale_price_dates_from', $date_from ? strtotime( $date_from ) : '' );
					update_post_meta( $variation_id, '_sale_price_dates_to', $date_to ? strtotime( $date_to ) : '' );

					if ( $date_to && ! $date_from ) {
						update_post_meta( $variation_id, '_sale_price_dates_from', strtotime( 'NOW', current_time( 'timestamp' ) ) );
					}

					if ( '' !== $sale_price && '' === $date_to && '' === $date_from ) {
						update_post_meta( $variation_id, '_price', $sale_price );
					} else {
						update_post_meta( $variation_id, '_price', $regular_price );
					}

					if ( '' !== $sale_price && $date_from && strtotime( $date_from ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						update_post_meta( $variation_id, '_price', $sale_price );
					}

					if ( $date_to && strtotime( $date_to ) < strtotime( 'NOW', current_time( 'timestamp' ) ) ) {
						update_post_meta( $variation_id, '_price', $regular_price );
						update_post_meta( $variation_id, '_sale_price_dates_from', '' );
						update_post_meta( $variation_id, '_sale_price_dates_to', '' );
					}

					if ( isset( $variable_tax_class[ $i ] ) && $variable_tax_class[ $i ] !== 'parent' ) {
						update_post_meta( $variation_id, '_tax_class', wc_clean( $variable_tax_class[ $i ] ) );
					} else {
						delete_post_meta( $variation_id, '_tax_class' );
					}
				}


				$is_downloadable     = isset( $variable_is_downloadable[ $i ] ) && $variable_is_downloadable[ $i ] == 'yes' ? 'yes' : 'no';

				if ( !in_array( 'product_downloadable' , self::$settings['user_permissions'] ) ) {
					update_post_meta( $variation_id, '_downloadable', wc_clean( $is_downloadable ) );
				}

				if ( !in_array( 'product_downloads' , self::$settings['user_permissions'] ) ) {
					if ( 'yes' == $is_downloadable ) {
						if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
							update_post_meta( $variation_id, '_download_limit', wc_clean( $variable_download_limit[ $i ] ) );
							update_post_meta( $variation_id, '_download_expiry', wc_clean( $variable_download_expiry[ $i ] ) );
						}

						$files              = array();
						$file_names         = isset( $curr_data['wfsm-file-names-' . $variation_id] ) ? array_map( 'wc_clean', $curr_data['wfsm-file-names-' . $variation_id] ) : array();
						$file_urls          = isset( $curr_data['wfsm-file-urls-' . $variation_id] ) ? array_map( 'wc_clean', $curr_data['wfsm-file-urls-' . $variation_id] ) : array();
						$file_url_size      = sizeof( $file_urls );
						$allowed_file_types = get_allowed_mime_types();

						for ( $ii = 0; $ii < $file_url_size; $ii ++ ) {
							if ( ! empty( $file_urls[ $ii ] ) ) {

								if ( 0 === strpos( $file_urls[ $ii ], 'http' ) ) {
									$file_is  = 'absolute';
									$file_url = esc_url_raw( $file_urls[ $ii ] );
								} elseif ( '[' === substr( $file_urls[ $ii ], 0, 1 ) && ']' === substr( $file_urls[ $ii ], -1 ) ) {
									$file_is  = 'shortcode';
									$file_url = wc_clean( $file_urls[ $ii ] );
								} else {
									$file_is = 'relative';
									$file_url = wc_clean( $file_urls[ $ii ] );
								}

								$file_name = wc_clean( $file_names[ $ii ] );
								$file_hash = md5( $file_url );

								if ( in_array( $file_is, array( 'absolute', 'relative' ) ) ) {
									$file_type  = wp_check_filetype( strtok( $file_url, '?' ) );
									$parsed_url = parse_url( $file_url, PHP_URL_PATH );
									$extension  = pathinfo( $parsed_url, PATHINFO_EXTENSION );

									if ( ! empty( $extension ) && ! in_array( $file_type['type'], $allowed_file_types ) ) {
										//WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The downloadable file %s cannot be used as it does not have an allowed file type. Allowed types include: %s', 'wfsm' ), '<code>' . basename( $file_url ) . '</code>', '<code>' . implode( ', ', array_keys( $allowed_file_types ) ) . '</code>' ) );
										continue;
									}
								}

								if ( 'relative' === $file_is && ! apply_filters( 'woocommerce_downloadable_file_exists', file_exists( $file_url ), $file_url ) ) {
									//WC_Admin_Meta_Boxes::add_error( sprintf( __( 'The downloadable file %s cannot be used as it does not exist on the server.', 'wfsm' ), '<code>' . $file_url . '</code>' ) );
									continue;
								}

								$files[ $file_hash ] = array(
									'name' => $file_name,
									'file' => $file_url
								);
							}
						}

						do_action( 'woocommerce_process_product_file_download_paths', $post_id, $variation_id, $files );

						update_post_meta( $variation_id, '_downloadable_files', $files );
					} else {
						if ( !in_array( 'product_download_settings' , self::$settings['user_permissions'] ) ) {
							update_post_meta( $variation_id, '_download_limit', '' );
							update_post_meta( $variation_id, '_download_expiry', '' );
						}
						update_post_meta( $variation_id, '_downloadable_files', '' );
					}
				}
			}

			do_action( 'woocommerce_save_product_variation', $variation_id, $i );

		}

		WC_Product_Variable::sync( $post_id );

	}

}

add_action( 'init', array( 'WC_Frontnend_Shop_Manager', 'init' ), 998 );

if ( is_admin() ) {
	include_once ( plugin_dir_path( __FILE__ ) . '/includes/wfsm-settings.php' );
}

?>