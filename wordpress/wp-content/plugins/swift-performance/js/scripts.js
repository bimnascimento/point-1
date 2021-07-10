jQuery(function(){
      // Clear cache
      jQuery(document).on('click', '#swift-performance-clear-cache', function(e){
            jQuery('.swift-performance-actions-message').removeClass('critical').removeClass('info').removeClass('success').empty();
            jQuery.post(ajaxurl, {action: 'swift_performance_clear_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  jQuery('.swift-performance-actions-message').addClass(response.type).text(response.text);
                  setTimeout(function(){
                        jQuery('.swift-performance-actions-message').empty();
                  },2000);
            });
            e.preventDefault();
      });

      // Clear assets cache
      jQuery(document).on('click', '#swift-performance-clear-assets-cache', function(e){
            jQuery('.swift-performance-actions-message').removeClass('critical').removeClass('info').removeClass('success').empty();
            jQuery.post(ajaxurl, {action: 'swift_performance_clear_assets_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  jQuery('.swift-performance-actions-message').addClass(response.type).text(response.text);
                  setTimeout(function(){
                        jQuery('.swift-performance-actions-message').empty();
                  },2000);
            });
            e.preventDefault();
      });

      // Prebuild cache
      jQuery(document).on('click', '#swift-performance-prebuild-cache', function(e){
            jQuery('.swift-performance-actions-message').removeClass('critical').removeClass('info').removeClass('success').empty();
            jQuery.post(ajaxurl, {action: 'swift_performance_prebuild_cache', '_wpnonce' : swift_performance.nonce}, function(response){
                  jQuery('.swift-performance-actions-message').addClass(response.type).text(response.text);
                  setTimeout(function(){
                        jQuery('.swift-performance-actions-message').empty();
                  },2000);
            });
            e.preventDefault();
      });

      // Show Rewrite Rules
      jQuery(document).on('click', '#swift-performance-show-rewrite', function(e){
            jQuery('.swift-performance-actions-message').removeClass('critical').removeClass('info').removeClass('success').empty();
            jQuery('#swift-performance-rewrites').remove();
            jQuery.post(ajaxurl, {action: 'swift_performance_show_rewrites', '_wpnonce' : swift_performance.nonce}, function(response){
                  jQuery('.swift-performance-actions-message').addClass(response.type).text(response.text);
                  jQuery('<textarea>', {'id' : 'swift-performance-rewrites','readonly' : true, 'text' : response.rewrites}).insertAfter(jQuery('.swift-performance-actions-message'));
            });
            e.preventDefault();
      });
});
