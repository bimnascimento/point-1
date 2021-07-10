<h1><?php esc_html_e('Swift Performance', 'swift_performance');?></h1>
<?php if(Swift_Performance::check_option('enable-caching', 1) || Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1)):?>
<div class="swift-performance-actions-header">
      <h2><?php esc_html_e('Actions', 'swift_performance');?></h2>
</div>
<div class="swift-performance-actions-message"></div>
<ul class="swift-performance-actions">
      <?php if(Swift_Performance::check_option('enable-caching', 1)):?>
            <li><a href="#" id="swift-performance-clear-cache" class="button button-primary"><?php esc_html_e('Clear Cache', 'swift_performance');?></a></li>
      <?php elseif(Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1)):?>
            <li><a href="#" id="swift-performance-clear-assets-cache" class="button button-primary"><?php esc_html_e('Clear Assets Cache', 'swift_performance');?></a></li>
      <?php endif;?>
      <?php if(Swift_Performance::check_option('enable-caching', 1) || Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1)):?>
            <li><a href="#" id="swift-performance-prebuild-cache" class="button button-primary"><?php esc_html_e('Prebuild Cache', 'swift_performance');?></a></li>
      <?php endif;?>
      <?php if(get_option('swift_performance_rewrites') != ''):?>
            <li><a href="#" id="swift-performance-show-rewrite" class="button button-primary"><?php esc_html_e('Show Rewrite Rules', 'swift_performance');?></a></li>
      <?php endif;?>
</ul>
<?php endif;?>
