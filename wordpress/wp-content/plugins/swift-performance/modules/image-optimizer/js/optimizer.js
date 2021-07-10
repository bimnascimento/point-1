(function(){
	var original_size = 0;
	var compressed_size = 0;
	var title = jQuery('title').text();
	var timer;

	jQuery(function(){
		jQuery(document).on('click', '#swift-optimize-images, .swift-optimize-single-image', function(e){
			e.preventDefault();

			if (jQuery(this).attr('data-is-running') == 'true'){
				return;
			}
			jQuery('#swift-optimize-images-ratio').empty();
			jQuery('#swift-optimize-images-progress').empty();

			original_size = compressed_size = 0;
			var image_id = (typeof jQuery(this).attr('data-image-id') != 'undefined' ? jQuery(this).attr('data-image-id') : '');
			var page_id	 = (jQuery('.image-optimizer-wrapper [name="mode"]').length > 0 && jQuery('.image-optimizer-wrapper [name="mode"]:checked').val() == 'page' ? jQuery('.image-optimizer-wrapper [name="page-id"]').val() : '');
			var url	 = (jQuery('.image-optimizer-wrapper [name="mode"]').length > 0 && jQuery('.image-optimizer-wrapper [name="mode"]:checked').val() == 'url' ? jQuery('.image-optimizer-wrapper [name="url"]').val() : '');
			var mode	 = (jQuery('.image-optimizer-wrapper [name="mode"]').length > 0 && jQuery('.image-optimizer-wrapper [name="mode"]:checked').length > 0 ? jQuery('.image-optimizer-wrapper [name="mode"]:checked').val() : 'individual');
			jQuery('#swift-optimize-images').removeClass('swift-btn-blue').addClass('swift-btn-gray').attr('data-is-running','true').text(__('Running...'));
			jQuery('#swift-optimize-images-progressbar').css('width', '0%');
			jQuery('#swift-optimize-images-progressbar-container').removeClass('swift-hidden');
			jQuery._post(swift_performance_image_optimizer.ajax_url, {'action' : 'swift_performance_image_optimizer', 'image-id' : image_id, 'page-id': page_id, 'url': url, 'mode': mode, 'nonce': swift_performance_image_optimizer.nonce}, function(response){
				var count	= response.count;
				var percent = 0;

				jQuery('#swift-optimize-images-progress').empty().text(__('Preparing...'));

				percent = compress_image(response, count, 0);

			},120000);
		});
	});

	/**
	 * Compress the images
	 * @param array images
	 * @param int count
	 * @param int c
	 */
	function compress_image(images, count, c){
		if (count > 0){
			var id = images['items'][Object.keys(images['items'])[0]]['id'];
			var size = images['items'][Object.keys(images['items'])[0]]['size'];
			var src = images['items'][Object.keys(images['items'])[0]]['src'];
			// Set current image
			jQuery('#swift-optimize-current-image').empty().append(jQuery('<img>', {'src':src}));

			// Optimize it
			timer = setTimeout(function(){c++;compress_image(images, count, c)}, 130000);
			jQuery.post(swift_performance_image_optimizer.ajax_url, {'action' : 'swift_performance_image_optimizer', 'swift_performance_action' : 'compress', 'id' : id, 'size': size, 'nonce': swift_performance_image_optimizer.nonce}, function(response){
				clearTimeout(timer);
				percent = parseInt(c/count*100);
				c++;
				delete images['items'][Object.keys(images['items'])[0]];

				try {
					original_size += response.original;
					compressed_size += response.compressed;
					current_image = response.current_file;

					// Refresh the progress
					jQuery('#swift-optimize-images-progressbar').css('width', format_number(percent, 0, '%'));
					jQuery('title').text(__('Progress:') + ' ' + c + '/' + count + ' (' + format_number(percent, 0, '%') + ')');
					jQuery('#swift-optimize-images-progress').empty().text(__('Progress:') + ' ' + c + '/' + count + ' (' + format_number(percent, 0, '%') + ')');
					jQuery('#swift-optimize-images-ratio').empty().text(__('Compression ratio:') + ' ' + format_number((1-(compressed_size/original_size))*100, 2, '% ('+format_number(original_size, 2, ' Mb') + '/' + format_number(compressed_size, 2, ' Mb')+')'));
				}
				catch(e){
					// Silent fail
				}

				// Compress next image
				if (c < count){
					compress_image(images, count, c);
				}
				else{
					jQuery('#swift-optimize-images').addClass('swift-btn-blue').removeClass('swift-btn-gray').removeAttr('data-is-running').text(__('Restart'));
					jQuery('#swift-optimize-images-progressbar').css('width', '100%');
					jQuery('#swift-optimize-images-progress').empty().text(__('Done. ') + c + '/' + count + ' (100%)');
					jQuery('#swift-optimize-current-image').empty();
					jQuery('title').text(title);
				}

			});
		}
		else{
			jQuery('#swift-optimize-images').addClass('swift-btn-blue').removeClass('swift-btn-gray').removeAttr('data-is-running').text(__('Restart'));

			jQuery('#swift-optimize-images-progressbar').css('width', '100%');
			jQuery('#swift-optimize-images-progress').empty().text(__('All of your images are already optimized'));
		}
	}

	/**
	 * Format number, add unit if necessary
	 * @param int|float number
	 * @param int decimals
	 * @param string unit
	 */
	function format_number(number, decimals, unit){
		decimals = decimals || 0;
		unit = unit || '';
		number = parseFloat(Math.round(number * 100) / 100).toFixed(decimals);
		return (isNaN(number) ? 0 : number) + unit;
	}

	/**
	 * AJAX Post wrapper
	 */
	jQuery._post = function( url, data, success, timeout ) {
	    var settings = {
	      type : "POST", //predefine request type to POST
	      'url'  : url,
	      'data' : data,
	      'success' : success,
	      'timeout' : timeout
	    };
	    jQuery.ajax(settings)
	  };

	/**
	 * Localization
	 * @param string text
	 * @return string
	 */
	function __(text){
		if (typeof swift_performance_image_optimizer.i18n[text] !== 'undefined'){
			return swift_performance_image_optimizer.i18n[text];
		}
		else {
			return text;
		}
	}
})();
