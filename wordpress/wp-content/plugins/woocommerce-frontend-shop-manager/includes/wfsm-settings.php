<?php

class WC_Wfsm_Settings {

	public static function init() {

		add_filter( 'woocommerce_settings_tabs_array', __CLASS__ . '::wfsm_add_settings_tab', 50 );
		add_action( 'woocommerce_settings_tabs_wfsm', __CLASS__ . '::wfsm_settings_tab' );
		add_action( 'woocommerce_update_options_wfsm', __CLASS__ . '::wfsm_save_settings' );
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::wfsm_settings_scripts' );
		add_action( 'woocommerce_admin_field_wfsm_groups_manager', __CLASS__ . '::wfsm_groups_manager', 10 );

		add_action( 'wp_ajax_wfsm_add_vendor_group', __CLASS__ . '::wfsm_add_vendor_group' );

	}

	public static function wfsm_add_vendor_group() {

		$curr_group = ( isset( $_POST['wfsm_group'] ) ? $_POST['wfsm_group'] : 0 );

		$out = self::wfsm_get_vendor_group( 'ajax', $_POST['wfsm_group'] );

		die($out);
		exit;
	}

	public static function wfsm_get_vendor_group( $mode, $args) {

		if ( $mode == 'ajax' ) {
			$curr_group = $args;
			$curr_group_options = array(
				'name' => '',
				'users' => array(),
				'permissions' => array()
			);
		}
		else {
			$curr_group = $args['id'];
			$curr_group_options = array(
				'name' => $args['name'],
				'users' => $args['users'],
				'permissions' => $args['permissions']
			);
		}

		ob_start();

		?>
		<span class="wfsm-vendor-group">
			<span class="wfsm-vendor-group-ui">REMOVE</span>
			<span class="wfsm-vendor-group-option">
				<span class="wfsm-vendor-group-title"><?php _e( 'Group name', 'wfsm'); ?></span>
				<input name="wfsm-vendor-group-name[<?php echo $curr_group; ?>]" class="wfsm-vendor-group-name"<?php echo ( $curr_group_options['name'] !== '' ? ' value="' . $curr_group_options['name'] . '"' : '' ); ?> />
			</span>
			<span class="wfsm-vendor-group-option" <?php echo ( !empty( $curr_group_options['users'] ) ? ' data-selected="' . esc_attr( json_encode( $curr_group_options['users'] ) ) . '"' : '' ); ?>">
				<span class="wfsm-vendor-group-title"><?php _e( 'Select users', 'wfsm'); ?></span>
				<?php
					$curr_args = array(
						'name' => 'wfsm-vendor-group-users[' . $curr_group . '][]',
						'class' => 'wfsm-vendor-group-users',
						'multi' => true,
						'selected' => false
					);
					wp_dropdown_users( $curr_args );
				?>
			</span>
			<span class="wfsm-vendor-group-option" <?php echo ( !empty( $curr_group_options['users'] ) ? ' data-selected="' . esc_attr( json_encode( $curr_group_options['permissions'] ) ) . '"' : '' ); ?>>
				<span class="wfsm-vendor-group-title"><?php _e( 'Select options that this group of vendors will not be able to edit', 'wfsm'); ?></span>
				<?php
					$curr_permissions = array(
						'create_simple_product' => __( 'Create Simple Products', 'wfsm' ),
						'create_grouped_product' => __( 'Create Grouped Products', 'wfsm' ),
						'create_external_product' => __( 'Create External Products', 'wfsm' ),
						'create_variable_product' => __( 'Create Variable Products', 'wfsm' ),
						'create_custom_product' => __( 'Create Custom Products', 'wfsm' ),
						'product_status' => __( 'Product Status', 'wfsm' ),
						'product_feature' => __( 'Feature Product', 'wfsm' ),
						'product_content' => __( 'Product Content and Description', 'wfsm' ),
						'product_featured_image' => __( 'Featured Image', 'wfsm' ),
						'product_gallery' => __( 'Product Gallery', 'wfsm' ),
						'product_downloadable' => __( 'Downloadable Products', 'wfsm' ),
						'product_virtual' => __( 'Virtual Products', 'wfsm' ),
						'product_name' => __( 'Product Name', 'wfsm' ),
						'product_slug' => __( 'Product Slug', 'wfsm' ),
						'external_product_url' => __( 'Product External URL (External/Affilate)', 'wfsm' ),
						'external_button_text' => __( 'Product External Button Text', 'wfsm' ),
						'product_sku' => __( 'Product SKU', 'wfsm' ),
						'product_taxes' => __( 'Product Tax', 'wfsm' ),
						'product_prices' => __( 'Product Prices', 'wfsm' ),
						'product_sold_individually' => __( 'Sold Individually', 'wfsm' ),
						'product_stock' => __( 'Product Stock', 'wfsm' ),
						'product_schedule_sale' => __( 'Product Schedule Sale', 'wfsm' ),
						'product_grouping' => __( 'Product Grouping', 'wfsm' ),
						'product_note' => __( 'Product Purchase Note', 'wfsm' ),
						'product_shipping' => __( 'Product Shipping', 'wfsm' ),
						'product_downloads' => __( 'Manage Downloads', 'wfsm' ),
						'product_download_settings' => __( 'Manage Download Extended Settings', 'wfsm' ),
						'product_cat' => __( 'Edit Product Categories', 'wfsm' ),
						'product_tag' => __( 'Edit Product Tags', 'wfsm' ),
						'product_attributes' => __( 'Edit Product Attributes', 'wfsm' ),
						'product_new_terms' => __( 'Add New Taxonomy Terms', 'wfsm' ),
						'variable_add_variations' => __( 'Add Variation (Variable)', 'wfsm' ),
						'variable_edit_variations' => __( 'Edit Variations (Variable)', 'wfsm' ),
						'variable_delete' => __( 'Delete Variation (Variable)', 'wfsm' ),
						'variable_product_attributes' => __( 'Edit Product Attributes (Variable)', 'wfsm' ),
						'product_clone' => __( 'Duplicate Products', 'wfsm' ),
						'product_delete' => __( 'Delete Products', 'wfsm' ),
					);
				?>
				<select name="wfsm-vendor-user-permissions[<?php echo $curr_group; ?>][]" class="wfsm-vendor-user-permissions">
				<?php
					foreach ( $curr_permissions as $k => $v ) {
						printf( '<option value="%1$s">%2$s</option>', $k, $v);
					}
				?>
				</select>
			</span>
		</span>
		<?php

		$out = ob_get_clean();

		return $out;
	}

	public static function wfsm_groups_manager($field) {

	global $woocommerce;
?>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo esc_html( $field['title'] ); ?></label>
			<?php echo '<img class="help_tip" data-tip="' . esc_attr( $field['desc'] ) . '" src="' . $woocommerce->plugin_url() . '/assets/images/help.png" height="16" width="16" />'; ?>
		</th>
		<td class="forminp forminp-<?php echo sanitize_title( $field['type'] ) ?>">
		<?php
			
			
			$curr_groups = get_option( 'wc_settings_wfsm_vendor_groups', array() );
			$i=0;
			foreach ( $curr_groups as $curr_group ) {
				$curr_group_options = array(
					'id' => $i
				) + $curr_group;
				echo self::wfsm_get_vendor_group( 'get', $curr_group_options );
				$i++;
			}
		?>
			<a href="#" id="wfsm-add-vendor-group" class="button-primary"><?php _e( 'Add Vendor Permission Group', 'wfsm' ); ?></a>
		</td>
	</tr><?php
	}


	public static function wfsm_settings_scripts( $settings_tabs ) {

		if ( isset($_GET['page'], $_GET['tab']) && ($_GET['page'] == 'wc-settings' || $_GET['page'] == 'woocommerce_settings') && $_GET['tab'] == 'wfsm' ) {

			wp_enqueue_style( 'wfsm-style-admin', plugins_url( 'assets/css/admin.css', dirname(__FILE__) ), '3.1.1' );
			wp_enqueue_style( 'wfsm-selectize-style-admin', plugins_url( 'assets/css/selectize_admin.css', dirname(__FILE__) ), '3.1.1' );
			wp_register_script( 'wfsm-selectize-admin', plugins_url( 'assets/js/selectize.min.js', dirname(__FILE__) ), array( 'jquery' ), '3.1.1', true );
			wp_register_script( 'wfsm-admin', plugins_url( 'assets/js/admin.js', dirname(__FILE__) ), array( 'jquery' ), '3.1.1', true );

			wp_enqueue_script( array( 'wfsm-selectize-admin', 'wfsm-admin' ) );

			$curr_args = array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'localization' => array(
					'delete_group' => __( 'Delete user group?', 'wfsm' )
				)
			);

			wp_localize_script( 'wfsm-admin', 'wfsm', $curr_args );

			if ( function_exists( 'wp_enqueue_media' ) ) {
				wp_enqueue_media();
			}

		}

	}

	public static function wfsm_add_settings_tab( $settings_tabs ) {

		$settings_tabs['wfsm'] = __( 'WFSM', 'wfsm' );

		return $settings_tabs;

	}

	public static function wfsm_settings_tab() {
		woocommerce_admin_fields( self::wfsm_get_settings() );
	}

	public static function wfsm_save_settings() {

		if ( isset($_POST['wfsm-vendor-group-name'] ) ) {

			if ( !is_array( $_POST['wfsm-vendor-group-name'] ) || !is_array( $_POST['wfsm-vendor-group-users'] ) || !is_array( $_POST['wfsm-vendor-user-permissions'] ) ) {
				return;
			}

			$curr_group['name'] = array_values( $_POST['wfsm-vendor-group-name'] );
			$curr_group['users'] = array_values( $_POST['wfsm-vendor-group-users'] );
			$curr_group['permissions'] = array_values( $_POST['wfsm-vendor-user-permissions'] );

			$vendor_group_settings = array();

			for($i = 0; $i < count( $curr_group['name'] ); $i++ ) {

				$group_name = sanitize_title( $curr_group['name'][$i] );

				$vendor_group_settings[$group_name]['name'] = $curr_group['name'][$i];
				$vendor_group_settings[$group_name]['users'] = $curr_group['users'][$i];
				$vendor_group_settings[$group_name]['permissions'] = $curr_group['permissions'][$i];

				foreach( $curr_group['users'][$i] as $curr_user ) {
					update_user_meta( $curr_user, 'wfsm_group', $group_name );
				}
			}

			update_option( 'wc_settings_wfsm_vendor_groups', $vendor_group_settings );

		}
		else {
			update_option( 'wc_settings_wfsm_vendor_groups', array() );
		}

		woocommerce_update_options( self::wfsm_get_settings() );

	}

	public static function wfsm_get_settings() {

		$wfsm_styles = apply_filters( 'wfsm_editor_styles', array(
			'wfsm_style_default' => __( 'Default', 'wfsm' ),
			'wfsm_style_flat' => __( 'Flat', 'wfsm' ),
			'wfsm_style_dark' => __( 'Dark', 'wfsm' )
		) );

		$settings = array();

		$settings = array(
			'section_intro_title' => array(
				'name' => __( 'WooCommerce Frontend Shop Manager', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'Settings page!', 'wfsm' ) . ' WooCommerce Frontend Shop Manager v3.1.1 ' . '<a href="http://codecanyon.net/user/dzeriho/portfolio?ref=dzeriho">' . __('Get more premium plugins at this link!', 'wfsm' ) . '</a>'
			),
			'wfsm_show_hidden_products' => array(
				'name' => __( 'Enable/Disable Hidden Products On Archives', 'wfsm' ),
				'type' => 'checkbox',
				'desc' => __( 'Check this option to enable pending and draft posts on archives.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_show_hidden_products',
				'default' => 'yes'
			),
			'section_intro_end' => array(
				'type' => 'sectionend'
			),
			'section_new_title' => array(
				'name' => __( 'New Product Settings', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'Setup new product default settings.', 'wfsm' )
			),
			'wfsm_create_status' => array(
				'name' => __( 'New Product Status', 'wfsm' ),
				'type' => 'select',
				'desc' => __( 'Select the default status for newly created products.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_create_status',
				'options' => array(
					'publish' => __( 'Published', 'wfsm' ),
					'pending' => __( 'Pending', 'wfsm' ),
					'draft' => __( 'Draft', 'wfsm' )
				),
				'default' => 'pending',
				'css' => 'width:300px;margin-right:12px;'
			),
			'wfsm_create_virtual' => array(
				'name' => __( 'New Product is Virtual', 'wfsm' ),
				'type' => 'checkbox',
				'desc' => __( 'Check this option to set a virtual product by default (not shipped) for newly created products.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_create_virtual',
				'default' => 'no'
			),
			'wfsm_create_downloadable' => array(
				'name' => __( 'New Product is Downloadable', 'wfsm' ),
				'type' => 'checkbox',
				'desc' => __( 'Check this option to set a downloadable product by default for newly created products.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_create_downloadable',
				'default' => 'no'
			),
			'section_new_end' => array(
				'type' => 'sectionend'
			),
			'section_style_title' => array(
				'name' => __( 'Design Settings', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'Plugin design settings. Setup your WooCommerce Frontend Shop Manager appearance.', 'wfsm' )
			),
			'wfsm_logo' => array(
				'name' => __( 'Custom Logo', 'wfsm' ),
				'type' => 'text',
				'desc' => __( 'Use custom logo. Paste in the logo URL. Use square images (200x200px)!', 'wfsm' ),
				'id'   => 'wc_settings_wfsm_logo',
				'default' => '',
				'css' => 'width:300px;margin-right:12px;'
			),
			'wfsm_mode' => array(
				'name' => __( 'Manager General Mode', 'wfsm' ),
				'type' => 'select',
				'desc' => __( 'Select general plugin appearance mode.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_mode',
				'options' => array(
					'wfsm_mode_logo' => __( 'Show Logo', 'wfsm' ),
					'wfsm_mode_user' => __( 'Show Logged User', 'wfsm' )
				),
				'default' => 'wfsm_logo',
				'css' => 'width:300px;margin-right:12px;'
			),
			'wfsm_style' => array(
				'name' => __( 'Manager Editor Style', 'wfsm' ),
				'type' => 'select',
				'desc' => __( 'Select editor style/skin.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_style',
				'options' => $wfsm_styles,
				'default' => 'wfsm_default',
				'css' => 'width:300px;margin-right:12px;'
			),
			'section_style_end' => array(
				'type' => 'sectionend'
			),
			'section_vendors_title' => array(
				'name' => __( 'Vendor Settings', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'WooCommerce Frontend Shop Manager supports vendor plugins. Configure your vendor settings in this section.', 'wfsm' )
			),
			'wfsm_default_vendor' => array(
				'name' => __( 'Default Restricted Vendor Editable Product Settings', 'wfsm' ),
				'type' => 'multiselect',
				'desc' => __( 'Select options that your vendors will not be able to edit.', 'wfsm' ),
				'id' => 'wc_settings_wfsm_default_permissions',
				'options' => array(
						'create_simple_product' => __( 'Create Simple Products', 'wfsm' ),
						'create_grouped_product' => __( 'Create Grouped Products', 'wfsm' ),
						'create_external_product' => __( 'Create External Products', 'wfsm' ),
						'create_variable_product' => __( 'Create Variable Products', 'wfsm' ),
						'create_custom_product' => __( 'Create Custom Products', 'wfsm' ),
						'product_status' => __( 'Product Status', 'wfsm' ),
						'product_feature' => __( 'Feature Product', 'wfsm' ),
						'product_content' => __( 'Product Content and Description', 'wfsm' ),
						'product_featured_image' => __( 'Featured Image', 'wfsm' ),
						'product_gallery' => __( 'Product Gallery', 'wfsm' ),
						'product_virtual' => __( 'Virtual Products', 'wfsm' ),
						'product_downloadable' => __( 'Downloadable Products', 'wfsm' ),
						'product_name' => __( 'Product Name', 'wfsm' ),
						'product_slug' => __( 'Product Slug', 'wfsm' ),
						'external_product_url' => __( 'Product External URL (External/Affilate)', 'wfsm' ),
						'external_button_text' => __( 'Product External Button Text', 'wfsm' ),
						'product_sku' => __( 'Product SKU', 'wfsm' ),
						'product_taxes' => __( 'Product Tax', 'wfsm' ),
						'product_prices' => __( 'Product Prices', 'wfsm' ),
						'product_sold_individually' => __( 'Sold Individually', 'wfsm' ),
						'product_stock' => __( 'Product Stock', 'wfsm' ),
						'product_schedule_sale' => __( 'Product Schedule Sale', 'wfsm' ),
						'product_grouping' => __( 'Product Grouping', 'wfsm' ),
						'product_note' => __( 'Product Purchase Note', 'wfsm' ),
						'product_shipping' => __( 'Product Shipping', 'wfsm' ),
						'product_downloads' => __( 'Manage Downloads', 'wfsm' ),
						'product_download_settings' => __( 'Manage Download Extended Settings', 'wfsm' ),
						'product_cat' => __( 'Edit Product Categories', 'wfsm' ),
						'product_tag' => __( 'Edit Product Tags', 'wfsm' ),
						'product_attributes' => __( 'Edit Product Attributes', 'wfsm' ),
						'variable_add_variations' => __( 'Add Variation (Variable)', 'wfsm' ),
						'variable_edit_variations' => __( 'Edit Variations (Variable)', 'wfsm' ),
						'variable_delete' => __( 'Delete Variation (Variable)', 'wfsm' ),
						'variable_product_attributes' => __( 'Edit Product Attributes (Variable)', 'wfsm' ),
						'product_clone' => __( 'Duplicate Products', 'wfsm' ),
						'product_delete' => __( 'Delete Products', 'wfsm' ),
				),
				'default' => array(),
				'css' => 'width:480px;height:200px;margin-right:12px;'
			),
			'section_vendors_end' => array(
				'type' => 'sectionend'
			),

			'section_vendor_groups_title' => array(
				'name' => __( 'Vendor Group Settings', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'Use the Vendor Group Manger to create groups of users that have different permissions.', 'wfsm' )
			),
			'wfsm_groups_manager' => array(
				'name' => __( 'Vendor Groups Manager', 'wfsm' ),
				'type' => 'wfsm_groups_manager',
				'desc' => __( 'Click Add Vendor Premission Group button to customize user editing permissions for specified users.', 'wfsm' )
			),
			'section_vendor_groups_end' => array(
				'type' => 'sectionend'
			),

			'section_advanced_title' => array(
				'name' => __( 'Advanced Settings', 'wfsm' ),
				'type' => 'title',
				'desc' => __( 'Set the custom actions and hooks for WFSM appearance.', 'wfsm' )
			),
			'wfsm_archive_action' => array(
				'name' => __( 'Override Archive Hook', 'wfsm' ),
				'type' => 'text',
				'desc' => __( 'Change default init action on product archives. Use actions initiated in your content-product.php file.', 'wfsm' ) . ' (default: woocommerce_before_shop_loop_item )',
				'id' => 'wc_settings_wfsm_archive_action'
			),
			'wfsm_single_action' => array(
				'name' => __( 'Override Single Hook', 'wfsm' ),
				'type' => 'text',
				'desc' => __( 'Change default init action on single product pages. Use actions initiated in your content-single-product.php file.', 'wfsm' ) . ' (default: woocommerce_before_single_product_summary )',
				'id' => 'wc_settings_wfsm_single_action'
			),
			'section_advanced_end' => array(
				'type' => 'sectionend'
			),
		);

		return apply_filters( 'wc_wfsm_settings', $settings );

	}

}

add_action( 'init', 'WC_Wfsm_Settings::init');

?>