<div class="wrap">
	<h2><?php esc_html_e('Image Optimizer', 'swift_performance')?></h2>
	<div class="image-optimizer-wrapper">
		<h4><?php esc_html_e('Optimize images', 'swift_performance')?></h4>
		<p>
			<label for="mode-all"><?php esc_html_e('All images', 'swift-perforrmance')?></label><input type="radio" name="mode" value="all" id="mode-all" checked>
			<label for="mode-page"><?php esc_html_e('Specific page', 'swift-perforrmance')?></label><input type="radio" name="mode" value="page" id="mode-page">
			<label for="mode-url"><?php esc_html_e('Specific URL', 'swift-perforrmance')?></label><input type="radio" name="mode" value="url" id="mode-url">
			<select name="page-id">
				<?php foreach(get_pages(array(
					'posts_per_page'   => 5,
					'orderby'          => 'name',
					'order'            => 'ASC',
					'post_type'        => 'page',
					'post_status'      => 'publish')
				) as $page):?>
				<option value="<?php echo (int)$page->ID?>"><?php echo (!empty($page->post_title) ? esc_html($page->post_title) : esc_html_e('No title', 'swift-perforrmance'))?></option>
			<?php endforeach;?>
			</select>
			<span class="url-container"><?php echo esc_html(home_url('/'))?><input type="text" name="url"></span>
		</p>
		<p>
			<a class="button button-primary" id="swift-optimize-images" href="#"><?php esc_html_e('Start', 'swift_performance')?></a>
		</p>
		<div id="swift-optimize-images-progressbar-container" class="swift-hidden">
			<div id="swift-optimize-images-progress"></div>
			<div id="swift-optimize-images-ratio"></div>
			<div class="media-progress-bar">
				<div id="swift-optimize-images-progressbar" style="width: 0%"></div>
			</div>
			<div id="swift-optimize-current-image"></div>
		</div>
	</div>
</div>
