<?php
class Swift_Performance_Image_Optimizer {

	public $api_url;

	public $api_key;

	public $upload_dir;

	public $original_size = 0;

	public $compressed_size = 0;

	public $localize = array();

	/**
	 * Create Image Optimizer Object
	 */
	public function __construct(){
		$this->upload_dir = wp_upload_dir();

		// Init optimizer
		add_action('init', array($this, 'init'),11);

		// Optimize uploaded images
		add_action('wp_handle_upload', array($this, 'handle_upload'), 10);
		add_action('image_make_intermediate_size', array($this, 'handle_upload'), 10);

		// Single
		add_filter('attachment_fields_to_edit', array($this, 'optimize_single'), 10, 2);
	}


	/**
	 * Set API URL, purchase code, create admin menu
	 */
	public function init(){
		if (is_admin()){
			// Set API URL
			$this->api_url = SWIFT_PERFORMANCE_API_URL;

			// Set purchase key
			$this->api_key = Swift_Performance::get_option('purchase-key');

			// Localization
			$this->localize = array(
				'ajax_url'	=> admin_url('admin-ajax.php'),
				'nonce'	=> wp_create_nonce('swift-optimize-images'),
				'i18n'	=> array(
					'Preparing...' => esc_html__('Preparing...', 'swift_performance'),
					'Done' => esc_html__('Done', 'swift_performance'),
					'Restart' => esc_html__('Restart', 'swift_performance'),
					'Running' => esc_html__('Running', 'swift_performance'),
					'Progress:' => esc_html__('Progress:', 'swift_performance'),
					'Compression ratio:' => esc_html__('Compression ratio:', 'swift_performance'),
					'All of your images are already optimized' => esc_html__('All of your images are already optimized', 'swift_performance'),
				)
			);

			if (!empty($this->api_key)){
				add_action('admin_menu', array($this, 'admin_menu'));
				add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));

				add_action('wp_ajax_swift_performance_image_optimizer', array($this, 'ajax_handler'));
			}
		}
	}


	/**
	 * Ajax handler
	 */
	public function ajax_handler(){
		if (!current_user_can('manage_options') || !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'swift-optimize-images')){
			return;
		}
		$action = isset($_POST['swift_performance_action']) ? $_POST['swift_performance_action'] : '';
		$images = array('count' => 0);
		switch ($action){
			case 'compress':
				$id	= isset($_POST['id']) ? $_POST['id'] : 0;
				$size = isset($_POST['size']) ? $_POST['size'] : '__original';
				if (!empty($id)){
					$this->optimize($id, $size);
					wp_send_json(array(
							'original' => $this->original_size,
							'compressed' => $this->compressed_size,
					));
				}
				break;
			case 'get_file_list':
			default:
				$images = array();
				$mode = isset($_POST['mode']) ? $_POST['mode'] : '';
				if ($mode == 'page' || $mode == 'url' ){
					global $wpdb;
					$upload_dir = wp_upload_dir();
					$upload_dir_base_url = preg_replace('~https?:~', '', $upload_dir['baseurl']);
					// Page mode
					if ($mode == 'page'){
						$url_to_scan = get_permalink($_POST['page-id']);
					}
					// Url mode
					else {
						$url_to_scan = esc_url(home_url($_POST['url']));
					}
					$response	= wp_remote_get($url_to_scan, array('ssl_verify' => false, 'timeout' => 600));

					if (!is_wp_error($response)){
						$collected_images = array();

						// Src and background images
						preg_match_all('~(src=|url\s?\()("|\')?([^\)"\']*)(jpe?g|gif|png)("|\'|)(\))?~i', $response['body'], $embedded_images);
						for($i=0;$i<count($embedded_images[0]);$i++){
							$collected_images[$embedded_images[3][$i].$embedded_images[4][$i]] = $embedded_images[3][$i].$embedded_images[4][$i];
						}

						// Srcset
						preg_match_all('~srcset=("|\')([^"\']*)("|\')~i', $response['body'], $srcsets);
						foreach($srcsets[2] as $srcset){
							$srcset_urls = explode(',', $srcset);
							foreach ($srcset_urls as $srcset_url){
								$srcset_url = trim(preg_replace('~(\d+)w~', '', $srcset_url));
								$collected_images[$srcset_url] = $srcset_url;
							}
						}

						foreach ($collected_images as $collected_image){
							$image_url	= preg_replace('~https?:~', '', $collected_image);

							if (strpos($image_url, apply_filters('swift_performance_media_host', $upload_dir_base_url)) !== false){
								$file		= str_replace(trailingslashit(apply_filters('swift_performance_media_host', $upload_dir_base_url)), '', $image_url);
								$id 		= Swift_Performance::get_image_id($image_url);

								$metadata = get_post_meta($id, '_wp_attachment_metadata', true);
								foreach((array)$metadata['sizes'] as $key => $image){
									if (preg_match('~'.$image['file'].'$~', $file)){
										$size = $key;
										break;
									}
								}

								$images['items'][$id . $size]['id']		= $id;
								$images['items'][$id . $size]['size']	= $size;
								$images['items'][$id . $size]['src']	= $image_url;
							}
						}
						$images['count'] = count($images['items']);
					}
				}
				else if ($_POST['mode'] == 'individual' && isset($_POST['image-id']) && !empty($_POST['image-id'])){
					$attachments = array(get_post($_POST['image-id']));
				}
				else {
					$attachments = get_posts(array(
						'post_type' => 'attachment',
						'posts_per_page' => -1,
						'post_status' => 'any',
						'post_mime_type' => array( 'image/jpeg', 'image/gif', 'image/png' ),
						'meta_query' => array(
								array(
										'key'		=> 'swift_performance_compressed',
										'compare'	=> 'NOT EXISTS',
										'value'	=> ''
								)
							)
						)
					);
				}
				if ( $attachments ) {
					foreach ((array)$attachments as $attachment) {
						$metadata = wp_get_attachment_metadata($attachment->ID);

						$count = 1;
						if (isset($metadata['sizes'])){
							foreach ((array)$metadata['sizes'] as $key=>$sizes){
								$thumb = wp_get_attachment_image_src($attachment->ID, $key);

								$images['items'][$attachment->ID . $key]['id']		= $attachment->ID;
								$images['items'][$attachment->ID . $key]['size']	= $key;
								$images['items'][$attachment->ID . $key]['src']		= $thumb[0];
								$count++;
							}
						}

						$images['items'][$attachment->ID . '__original']['id']	= $attachment->ID;
						$images['items'][$attachment->ID . '__original']['size']	= '__original';
						$images['items'][$attachment->ID . '__original']['src']	= wp_get_attachment_url($attachment->ID);

						$images['count'] += $count;
					}
				}
				wp_send_json($images);
				break;
		}
	}

	/**
	 * Call API
	 * @param string $function
	 * @param array $args
	 */
	public function api($function = '', $args = array()){
		$response = wp_remote_post (
			$this->api_url . $function ,array(
					'timeout' => 300,
					'sslverify' => false,
					'user-agent' => 'SwiftPerformance',
					'headers' => array (
							'X-ENVATO-PURCHASE-KEY' => trim ($this->api_key)
					),
					'body' => array (
							'site' => trailingslashit(home_url()),
							'args' => $args
					)
			)
		);

		if (is_wp_error($response)){
			$this->error = esc_html__('Couldn\'t connect to API server: ', 'swift_performance') . $response->get_error_message();
		}
		else{
			$response = json_decode($response['body'], true);
			if ($response['error'] === true){
				$this->error = esc_html__('API error: ', 'swift_performance') . $response['response'];
			}
			else{
				return $response['response'];
			}
		}
	}

	/**
	 * Optimize original images and all sizes
	 * @param int $id
	 * @param string $size
	 */
	public function optimize($id, $size){
		if ($size == '__original'){
			// Get the path for the image
			$filepath = get_attached_file((int)$id);

			// Check is file exists
			if (file_exists($filepath)){

				// Get the original size
				$this->original_size = (filesize($filepath)/1024/1024);

				// Compress the original file
				$this->compress($filepath, $id);

				// Get compressed size
				$this->compressed_size = (filesize($filepath)/1024/1024);

			}
		}
		else{
			// Compress all sizes
			$metadata = wp_get_attachment_metadata($id);
			if (isset($metadata['sizes'][$size])){
				// Build file path for resized image
				$filepath = $this->upload_dir['basedir'] .'/'.dirname($metadata['file']) .'/'. $metadata['sizes'][$size]['file'];

				// Check is file exists
				if (file_exists($filepath)){

					// Get the original size
					$this->original_size = (filesize($filepath)/1024/1024);

					// Compress
					$this->compress($filepath);

					// Get compressed size
					$this->compressed_size = (filesize($filepath)/1024/1024);
				}
			}
		}
	}

	/**
	 * Compress the image using API
	 * @param string $file file path
	 */
	public function compress($file, $id = 0){
		global $wp_filesystem;
		WP_Filesystem();

		// Compress file
		$new_image = base64_decode($this->api('compress_image', array(
				'data' => $wp_filesystem->get_contents($file)
		)));

		// Create temporary file for checkings
		$test_img = $this->upload_dir['basedir'] . '/test-image_' . mt_rand(0,PHP_INT_MAX);
		$wp_filesystem->put_contents($test_img, $new_image);

		// Check the resized image
		@$check = getimagesize($test_img);

		// If image seems ok overwrite the original image
		if ($check !== false && isset($check[0]) && $check[0] > 0){
			$wp_filesystem->put_contents($file, $new_image);
			if (!empty($id)){
				update_post_meta($id, 'swift_performance_compressed', 1);
			}
		}

		// Remove temporary file
		$wp_filesystem->delete($test_img);
	}

	/**
	 * Regenerate thumbnails
	 * @param int $id
	 */
	public function regenerate_thumbnails($id){
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file($id) ) );
	}

	/**
	 * Optimize images on upload
	 */
	public function handle_upload($upload){
		if (!empty($this->api_key) && Swift_Performance::check_option('optimize-uploaded-images', 1)){
			$file = (is_array($upload) ? $upload['file'] : $upload );
			$this->compress($file);
		}
		return $upload;
	}

	/**
	 * Create menu
	 */
	public function admin_menu(){
		add_submenu_page( 'upload.php', 'Image Optimizer', 'Image Optimizer','manage_options', 'swift-performance-optimize-images', array($this, 'dashboard'));
	}

	public function optimize_single($fields, $post) {
	    $html = '<a href="#" class="button swift-optimize-single-image" data-image-id="'.$post->ID.'">'.esc_html__('Optimize image', 'swift_performance').'</a>'.
	 	    	'<div id="swift-optimize-images-progressbar-container" class="swift-hidden">'.
				'	<div id="swift-optimize-images-progress"></div>'.
				'	<div id="swift-optimize-images-ratio"></div>'.
				'	<div class="media-progress-bar">'.
				'		<div id="swift-optimize-images-progressbar" style="width: 0%"></div>'.
				'	</div>'.
				'</div>';
	    $fields["swift-performance-optimize-image"] = array(
	        "label"	=> esc_html__('Image Optimizer', 'swift_performance'),
	        "input"	=> "html",
	        "html"	=> $html
	    );
	    return $fields;
	}

	/**
	 * Display dashboard
	 */
	public function dashboard() {
		include_once 'templates/dashboard.tpl.php';
	}

	/**
	 * Enqueue assets
	 */
	public function enqueue_assets(){
		global $pagenow;
		if ( $pagenow == 'upload.php' || ($pagenow == 'post.php' && isset($_GET['action']) && $_GET['action'] == 'edit') ){
			wp_enqueue_script('swift-performance-image-optimizer', SWIFT_PERFORMANCE_URI . 'modules/image-optimizer/js/optimizer.js', array('jquery'), SWIFT_PERFORMANCE_VER, true );
			wp_localize_script('swift-performance-image-optimizer', 'swift_performance_image_optimizer', $this->localize);
			wp_enqueue_style('swift-performance-image-optimizer', SWIFT_PERFORMANCE_URI . 'modules/image-optimizer/css/admin.css',  array(), SWIFT_PERFORMANCE_VER);
		}
	}

}

return new Swift_Performance_Image_Optimizer();
?>
