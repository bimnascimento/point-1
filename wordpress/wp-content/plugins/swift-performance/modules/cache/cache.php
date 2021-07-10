<?php
class Swift_Performance_Cache {

      // Current content
      public $buffer;

      // Current cache path (relative to cache folder)
      public $path;

      // Is dynamic or static page
      public $is_dynamic_cache = false;

      // No cache mode
      public $no_cache = false;

      public function __construct(){
            $device = (Swift_Performance::check_option('mobile-support', 1) && wp_is_mobile() ? 'mobile' : 'desktop');

            // Logged in path
            if (isset($_COOKIE[LOGGED_IN_COOKIE])){
                  list(,,, $hash) = explode('|', $_COOKIE[LOGGED_IN_COOKIE]);
                  $this->path = trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '/' . $device . '/authenticated/' . $hash . '/');
            }
            // Not logged in path
            else {
                  $this->path = trailingslashit(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . '/' . $device . '/unauthenticated/');
            }

            // Clear cache on post save
            add_action('save_post', array('Swift_Performance_Cache', 'clear_post_cache'));

            // Clear cache on plugin actions
            add_action('activated_plugin', array('Swift_Performance_Cache', 'clear_all_cache'));
            add_action('deactivated_plugin', array('Swift_Performance_Cache', 'clear_all_cache'));

            // Clear intelligent cache based on request
            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && Swift_Performance::check_option('resource-saving-mode', 1)){
                  add_action('shutdown', array($this, 'clear_intelligent_cache'));
            }

            // Force no cache mode
            if (isset($_GET['swift-no-cache'])){
                  $this->no_cache = true;
                  unset($_GET['swift-no-cache']);
                  unset($_REQUEST['swift-no-cache']);
                  $_SERVER['REQUEST_URI'] = preg_replace('~(&|\?)swift-no-cache=(\d+)~','',$_SERVER['REQUEST_URI']);
            }


            // Load cache
            if (!$this->no_cache){
                  $this->load_cache();
            }

            // Set cache
            if (self::is_cacheable() || self::is_cacheable_dynamic()){
                  $this->is_dynamic_cache = (self::is_cacheable_dynamic() ? true : false);

                  ob_start(array($this, 'build_cache'));
                  add_action('shutdown', array($this, 'set_cache'), PHP_INT_MAX);
            }
            else if (self::is_cacheable_ajax()){
                  $this->set_ajax_cache();
            }

            // Print intelligent cache js in head
            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && (self::is_cacheable() || self::is_cacheable_dynamic())){
                  add_action('wp_head', array('Swift_Performance_Cache', 'intelligent_cache_xhr'), PHP_INT_MAX);
            }
      }

      /**
       * Catch the content
       */
      public function build_cache($buffer){
            $this->buffer = $buffer;

            if (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && $this->no_cache){
                  $this->check_integrity(self::avoid_mixed_content($this->buffer));
            }

            return $buffer;
      }


      /**
       * Save current page to cache
       */
      public function set_cache(){
            // We have wp_query, so we re-check is it cacheable
            if (!self::is_cacheable() && !self::is_cacheable_dynamic()){
                  return;
            }

            // Don't write cache for common request if background assets merging enabled
            if (Swift_Performance::check_option('merge-background-only', 1) && !isset($_SERVER['HTTP_X_MERGE_ASSETS'])){
                  return;
            }

            $this->buffer = self::avoid_mixed_content($this->buffer);

            if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                  $this->buffer = str_replace('</body>',"<!--Cached with Swift Performance-->\n</body>", $this->buffer);
            }

            // Disk cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  // Dynamic cache
                  if ($this->is_dynamic_cache){
                        set_transient('swift_performance_dynamic_' . md5(serialize($_REQUEST)), array('time' => time(), 'content' => $this->buffer), 3600);
                  }
                  // General cache
                  else {
                        self::write_file($this->path . 'index.html', $this->buffer, true);
                        if (Swift_Performance::check_option('enable-gzip', 1) && function_exists('gzencode')){
                              self::write_file($this->path . 'index.html.gz', gzencode($this->buffer), true);
                        }
                  }
            }
            // Memcached
            else {
                  //TODO memcached
            }
      }

      /**
       * Set AJAX object transient
       */
      public function set_ajax_cache(){
            // Prevent loop
            if (isset($_SERVER['HTTP_X_PROXY'])){
                  return;
            }

            // Set AJAX object expiry
            $expiry = (Swift_Performance::check_option('cache-expiry-mode', 'timebased') ? Swift_Performance::get_option('ajax-cache-expiry-time') : 3600);

            // Store AJAX object in db if we are using disk cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  $response = self::proxy_request();
                  set_transient('swift_performance_ajax_' . md5(serialize($_REQUEST)), array('time' => time(), 'content' => $response), $expiry);
                  echo $response;
                  die;
            }
            // Memcached
            else {
                  //TODO memcached
            }
      }



      /**
       * Load cached file and stop if file exists
       * TODO memcached
       */
      public function load_cache(){
            // Add Swift Performance header
            if (!Swift_Performance::is_admin() && Swift_Performance::check_option('whitelabel', 1, '!=')){
                  header('Swift-Performance: MISS');
            }

            // Serve cached AJAX requests
            if (self::is_cacheable_dynamic() || self::is_cacheable_ajax()){
                  if (self::is_cacheable_dynamic()){
                        $cached_request = get_transient('swift_performance_dynamic_' . md5(serialize($_REQUEST)));
                  }
                  else {
                        $cached_request = get_transient('swift_performance_ajax_' . md5(serialize($_REQUEST)));
                  }

                  if ($cached_request !== false){
                        if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                              header('Swift-Performance: HIT');
                        }

                        $content                = $cached_request['content'];
                        $last_modified_time     = $cached_request['time'];
                        $etag                   = md5($content);
                        if (Swift_Performance::check_option('304-header', 1) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)){
                              header("HTTP/1.1 304 Not Modified");
                        }

                        // Send headers
                        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                        header("Etag: $etag");
                        header("Content-Encoding: none");
                        header('Connection: close');

                        ob_start();
                        echo $content;
                        $size = ob_get_length();
                        header("Content-Length: $size");
                        // Close connection here, but script will still running in background
                        ob_end_flush();
                        flush();
                        @ob_end_clean();

                        // Check identical for ajax requests
                        if (self::is_cacheable_ajax() && !self::is_identical()){
                              $this->set_ajax_cache();
                        }
                        die;
                  }
            }

            // Skip if requested page isn't cacheable
            if (!self::is_cacheable()){
                  return;
            }
            $cached                 = false;
            $is_disk_cache          = (strpos(Swift_Performance::get_option('caching-mode'), 'disk_cache') !== false);
            $last_modified_time     = time();
            $etag                   = '';
            $path                   = SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html';
            if ($is_disk_cache && file_exists($path) && filesize($path) > 0){
                  if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                        header('Swift-Performance: HIT');
                  }

                  $last_modified_time = filemtime($path);
                  $etag = md5_file($path);
                  $cached = true;
            }

            if (Swift_Performance::check_option('304-header', 1) && (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time || @trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag)){
                  header("HTTP/1.1 304 Not Modified");
            }
            if (!empty($etag)){
                  header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
                  header("Etag: $etag");
            }

            if ($is_disk_cache && $cached){
                  if ( file_exists($path . '.gz') && strpos( $_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip' ) !== false ) {
                        header('Content-Encoding: gzip');
                        readfile($path . '.gz');
                  }
                  else{
                        readfile($path);
                  }
                  exit;
            }
      }

      /**
       * Check is dynamic content and cache identical, return dynamic content if not.
       * @return string
       */
      public function check_integrity($buffer){
            // Cache is identical
            if (self::is_identical()){
                  header('X-Cache-Status: identical');
                  die;
            }

            if (Swift_Performance::check_option('whitelabel', 1, '!=')){
                  $buffer = str_replace('</body>', "<!--Cached with Swift Performance-->\n</body>", $buffer);
            }

            $checksum = false;
            // Disk cache
            if(strpos(Swift_Performance::get_option('caching-mode'), 'disk_cache') !== false){
                  if (self::is_cacheable()){
                        if (!file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html')){
                              header('X-Cache-Status: miss');
                              return;
                        }

                        $clean_cached = self::clean_buffer(file_get_contents(SWIFT_PERFORMANCE_CACHE_DIR . $this->path . 'index.html'));
                        $clean_buffer = self::clean_buffer($buffer);

                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }
                  else if (self::is_cacheable_dynamic()){
                        $cached     = get_transient('swift_performance_dynamic_' . md5(serialize($_REQUEST)));
                        $clean_cached = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $cached['content']);
                        $clean_buffer = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $buffer);
                        $checksum = (md5($clean_buffer) != md5($clean_cached));
                  }

                  if ($checksum){
                        header("Last-Modified: ".gmdate("D, d M Y H:i:s", time())." GMT");
                        header("Etag: " . md5($buffer));
                        header('X-Cache-Status: changed');
                        return;
                  }
            }

            // TODO memcached

            header('X-Cache-Status: not-modified');

      }

      /**
       * Clear intelligent cache if request is POST or query string isn't empty
       */
      public function clear_intelligent_cache(){
            // Exceptions

            // Swift check cache
            if ($this->no_cache){
                  return;
            }

            // Don't clear cache on login/register
            if (@in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ))){
                  return;
            }

            // Remove empty elements
            $_GET = array_filter($_GET);
            $_POST = array_filter($_POST);

            // Clear transients if necessary
            if (!empty($_POST) || !empty($_GET)){
                  self::clear_transients();
            }
      }

      /**
       * Clean buffer to be able to compare integrity for logged in users
       * @param string buffer
       * @return string
       */
      public static function clean_buffer($buffer){
            $buffer = preg_replace('~swift-no-cache(=|%3D)(\d*)~', '', $buffer);
            $buffer = preg_replace_callback('~([0-9abcdef]{10})~',array('Swift_Performance_Cache', 'clean_nonce_buffer'), $buffer);
            return $buffer;
      }

      /**
       * Remove valid nonces from the cache/buffer to be able to compare integrity for logged in users
       * @param array $matches
       * @return string
       */
      public static function clean_nonce_buffer($matches){
            if (wp_verify_nonce($matches[0])){
                  return '';
            }
            return $matches[0];
      }

      /**
       * Print cache check XHR request in head
       */
      public static function intelligent_cache_xhr(){
            echo "\n<script data-dont-merge='1'>(function() {var request = new XMLHttpRequest();request.open('GET', document.location.href + (document.location.href.match(/\?/) ? '&' : '?')  + 'swift-no-cache=' + parseInt(Math.random()*100000000) , true);request.onreadystatechange = function () { if(request.readyState === 4 && request.getResponseHeader('X-Cache-Status') == 'changed' && '1' != '".Swift_Performance::get_option('disable-instant-reload')."'){ document.open();document.write(request.responseText.replace('<html', '<html data-swift-performance-refreshed'));document.close();}};if(document.querySelector('[data-swift-performance-refreshed]') == null){request.send();};document.getElementsByTagName('html')[0].addEventListener('click', function() {request.abort()}, false);document.getElementsByTagName('html')[0].addEventListener('keypress', function() {request.abort()}, false);})();</script>";
      }

      /**
       * Clear all cache
       */
      public static function clear_all_cache(){
            // Clear cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  self::recursive_rmdir();
            }

            // Prebuild cache
            if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                  wp_schedule_single_event(time(), 'swift_performance_prebuild_cache');
            }
            // TODO memcached

            // Clear object cache
            self::clear_transients();
      }

      /**
       * Clear expired cache
       */
      public static function clear_expired(){
            // Clear cache
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  self::recursive_rmdir('', true);
            }

            // Prebuild cache
            if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                  wp_schedule_single_event(time(), 'swift_performance_prebuild_cache');
            }
            // TODO memcached
      }

      /**
       * Delete cached post on save
       */
      public static function clear_post_cache($post_id){
            // Don't clear cache on autosave
            if (defined('DOING_AUTOSAVE')){
                  return;
            }

            // Clear cached version
            if (Swift_Performance::check_option('caching-mode', array('disk_cache_rewrite', 'disk_cache_php'), 'IN')){
                  $permalink = get_permalink($post_id);
                  self::recursive_rmdir(str_replace(home_url(), '', $permalink));

                  // Prebuild cache
                  if (Swift_Performance::check_option('automated_prebuild_cache',1)){
                        wp_remote_get($permalink);
                  }
            }
            // TODO memcached
      }

      /**
       * Clear object cache transients
       */
      public static function clear_transients($type = 'all'){
            global $wpdb;
            switch ($type) {
                  case 'all':
                  default:
                        $wpdb->query('DELETE FROM ' . $wpdb->options . ' WHERE option_name LIKE "%swift_performance_is_identical_%" OR option_name LIKE "%swift_performance_ajax_%" OR option_name LIKE "%swift_performance_dynamic_%"');
                        break;
            }
      }

      /**
       * Is current page cacheable
       * @return boolean
       */
      public static function is_cacheable(){
            $ajax_cache = !defined('DOING_AJAX');
            $logged_in  = (!Swift_Performance::is_user_logged_in() || (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && Swift_Performance::check_option('enable-caching-logged-in-users', 1)));
            $is_404     = (Swift_Performance::check_option('cache-404',1) || !Swift_Performance::is_404());
            return apply_filters('swift_performance_is_cacheable', !self::is_exluded() && $is_404 && !Swift_Performance::is_admin() && empty($_POST) && empty($_GET) && $logged_in && $ajax_cache && !self::is_crawler() && !Swift_Performance::is_feed());
      }

      /**
       * Is current AJAX request cacheable
       * @return boolean
       */
      public static function is_cacheable_dynamic(){
            // Is there any dynamic parameter?
            $_GET = array_filter($_GET);
            $_POST = array_filter($_POST);
            if (empty($_GET) && empty($_POST)){
                  return apply_filters('swift_performance_is_cacheable_dynamic', false);
            }

            if (Swift_Performance::check_option('dynamic-caching', 1)){
                  $cacheable = false;
                  foreach ((array)array_filter(Swift_Performance::get_option('cacheable-dynamic-requests')) as $key){
                        if (isset($_REQUEST[$key])){
                              $cacheable = true;
                              break;
                        }
                  }
                  $logged_in  = (!Swift_Performance::is_user_logged_in() || (Swift_Performance::check_option('cache-expiry-mode', 'intelligent') && Swift_Performance::check_option('enable-caching-logged-in-users', 1)));
                  $is_404     = (Swift_Performance::check_option('cache-404',1) || !Swift_Performance::is_404());
                  return apply_filters('swift_performance_is_cacheable_dynamic', !self::is_exluded() && $is_404 && $cacheable && $logged_in && !Swift_Performance::is_admin() && !self::is_crawler() && !Swift_Performance::is_feed());
            }
            return apply_filters('swift_performance_is_cacheable_dynamic', false);
      }

      /**
       * Is current AJAX request cacheable
       * @return boolean
       */
      public static function is_cacheable_ajax(){
            return apply_filters('swift_performance_is_cacheable_ajax', ((!Swift_Performance::is_user_logged_in() || Swift_Performance::check_option('enable-caching-logged-in-users', 1)) && defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['action']) && in_array($_REQUEST['action'], array_filter(Swift_Performance::get_option('cacheable-ajax-actions')))));
      }

      /**
       * Is current request excluded from cache
       * @return boolean
       */
      public static function is_exluded(){
            $excluded = false;

            // Excluded strings
            foreach (array_filter((array)Swift_Performance::get_option('exclude-strings')) as $exclude_string){
                  if (!empty($exclude_string)){
                        if(substr($exclude_string,0,1) == '#' && substr($exclude_string,strlen($exclude_string)-1) == '#'){
                              $excluded = preg_match($exclude_string, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                        }
                        else {
                              $excluded = strpos(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), $exclude_string) !== false;
                        }
                  }
            }

            // Excluded user agents
            foreach (array_filter((array)Swift_Performance::get_option('exclude-useragents')) as $exclude_ua){
                  if (!empty($exclude_ua)){
                        if(substr($exclude_ua,0,1) == '#' && substr($exclude_ua,strlen($exclude_ua)-1) == '#'){
                              $excluded = preg_match($exclude_ua, $_SERVER['HTTP_USER_AGENT']);
                        }
                        else {
                              $excluded = strpos($_SERVER['HTTP_USER_AGENT'], $exclude_ua) !== false;
                        }
                  }
            }

            // Excluded pages
            if (!$excluded){
                  // Manually excluded pages
                  $excluded = (get_the_ID() != 0 && in_array(get_the_ID(), (array)Swift_Performance::get_option('exclude-pages')) );

                  // WooCommerce
                  if(class_exists('woocommerce')){
                        global $wpdb;
                        $results = $wpdb->get_results("SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'woocommerce_%_page_id' AND option_name NOT IN ('woocommerce_shop_page_id', 'woocommerce_terms_page_id')", ARRAY_A);
                        foreach((array)$results as $result){
                              if (!empty($result['option_value']) && $result['option_value'] == get_the_ID()){
                                    $excluded = true;
                              }
                        }
                  }

            }

            return apply_filters('swift_performance_is_excluded', $excluded);

      }

      /**
       * Detect crawlers
       */
      public static function is_crawler(){
            $crawlers = array( '.*Java.*outbrain', '008\/', '192\.comAgent', '2ip\.ru', '404checker', '^bluefish ', '^Calypso v\/', '^COMODO DCV', '^DangDang', '^DavClnt', '^FDM ', '^git\/', '^Goose\/', '^HTTPClient\/', '^Java\/', '^Jeode\/', '^Jetty\/', '^Mget', '^Microsoft URL Control', '^NG\/[0-9\.]', '^NING\/', '^PHP\/[0-9]', '^RMA\/', '^Ruby|Ruby\/[0-9]', '^scrutiny\/', '^VSE\/[0-9]', '^WordPress\.com', '^XRL\/[0-9]', '^ZmEu', 'a3logics\.in', 'A6-Indexer', 'a\.pr-cy\.ru', 'Aboundex', 'aboutthedomain', 'Accoona-AI-Agent', 'acoon', 'acrylicapps\.com\/pulp', 'adbeat', 'AddThis', 'ADmantX', 'adressendeutschland', 'Advanced Email Extractor v', 'agentslug', 'AHC', 'aihit', 'aiohttp\/', 'Airmail', 'akula\/', 'alertra', 'alexa site audit', 'Alibaba\.Security\.Heimdall', 'alyze\.info', 'amagit', 'AndroidDownloadManager', 'Anemone', 'Ant\.com', 'Anturis Agent', 'AnyEvent-HTTP\/', 'Apache-HttpClient\/', 'AportWorm\/[0-9]', 'AppEngine-Google', 'Arachmo', 'arachnode', 'Arachnophilia', 'aria2', 'asafaweb.com', 'AskQuickly', 'Astute', 'asynchttp', 'autocite', 'Autonomy', 'B-l-i-t-z-B-O-T', 'Backlink-Ceck\.de', 'Bad-Neighborhood', 'baidu\.com', 'baypup\/[0-9]', 'baypup\/colbert', 'BazQux', 'BCKLINKS', 'BDFetch', 'BegunAdvertising\/', 'BigBozz', 'biglotron', 'BingLocalSearch', 'BingPreview', 'binlar', 'biNu image cacher', 'biz_Directory', 'Blackboard Safeassign', 'Bloglovin', 'BlogPulseLive', 'BlogSearch', 'Blogtrottr', 'boitho\.com-dc', 'BPImageWalker', 'Braintree-Webhooks', 'Branch Metrics API', 'Branch-Passthrough', 'Browsershots', 'BUbiNG', 'Butterfly\/', 'BuzzSumo', 'CAAM\/[0-9]', 'CakePHP', 'CapsuleChecker', 'CaretNail', 'catexplorador', 'cb crawl', 'CC Metadata Scaper', 'Cerberian Drtrs', 'CERT\.at-Statistics-Survey', 'cg-eye', 'changedetection', 'Charlotte', 'CheckHost', 'checkprivacy', 'chkme\.com', 'CirrusExplorer\/', 'CISPA Vulnerability Notification', 'CJNetworkQuality', 'clips\.ua\.ac\.be', 'Cloud mapping experiment', 'CloudFlare-AlwaysOnline', 'Cloudinary\/[0-9]', 'cmcm\.com', 'coccoc', 'CommaFeed', 'Commons-HttpClient', 'Comodo SSL Checker', 'contactbigdatafr', 'convera', 'copyright sheriff', 'Covario-IDS', 'CrawlForMe\/[0-9]', 'cron-job\.org', 'Crowsnest', 'curb', 'Curious George', 'curl', 'cuwhois\/[0-9]', 'cybo\.com', 'DareBoost', 'DataparkSearch', 'dataprovider', 'Daum(oa)?[ \/][0-9]', 'DeuSu', 'developers\.google\.com\/\+\/web\/snippet\/', 'Digg', 'Dispatch\/', 'dlvr', 'DMBrowser-UV', 'DNS-Tools Header-Analyzer', 'DNSPod-reporting', 'docoloc', 'Dolphin http client\/', 'DomainAppender', 'dotSemantic', 'downforeveryoneorjustme', 'downnotifier\.com', 'DowntimeDetector', 'Dragonfly File Reader', 'drupact', 'Drupal \(\+http:\/\/drupal\.org\/\)', 'dubaiindex', 'EARTHCOM', 'Easy-Thumb', 'ec2linkfinder', 'eCairn-Grabber', 'ECCP', 'ElectricMonk', 'elefent', 'EMail Exractor', 'EmailWolf', 'Embed PHP Library', 'Embedly', 'europarchive\.org', 'evc-batch\/[0-9]', 'EventMachine HttpClient', 'Evidon', 'Evrinid', 'ExactSearch', 'ExaleadCloudview', 'Excel\/', 'Exif Viewer', 'Exploratodo', 'ezooms', 'facebookexternalhit', 'facebookplatform', 'fairshare', 'Faraday v', 'Faveeo', 'Favicon downloader', 'FavOrg', 'Feed Wrangler', 'Feedbin', 'FeedBooster', 'FeedBucket', 'FeedBunch\/[0-9]', 'FeedBurner', 'FeedChecker', 'Feedly', 'Feedspot', 'Feedwind\/[0-9]', 'feeltiptop', 'Fetch API', 'Fetch\/[0-9]', 'Fever\/[0-9]', 'findlink', 'findthatfile', 'FlipboardBrowserProxy', 'FlipboardProxy', 'FlipboardRSS', 'fluffy', 'flynxapp', 'forensiq', 'FoundSeoTool\/[0-9]', 'free thumbnails', 'FreeWebMonitoring SiteChecker', 'Funnelback', 'g00g1e\.net', 'GAChecker', 'ganarvisitas\/[0-9]', 'geek-tools', 'Genderanalyzer', 'Genieo', 'GentleSource', 'GetLinkInfo', 'getprismatic\.com', 'GetURLInfo\/[0-9]', 'GigablastOpenSource', 'github\.com\/', 'Go [\d\.]* package http', 'Go-http-client', 'gofetch', 'GomezAgent', 'gooblog', 'Goodzer\/[0-9]', 'Google favicon', 'Google Keyword Suggestion', 'Google Keyword Tool', 'Google Page Speed Insights', 'Google PP Default', 'Google Search Console', 'Google Web Preview', 'Google-Adwords', 'Google-Apps-Script', 'Google-Calendar-Importer', 'Google-HTTP-Java-Client', 'Google-Publisher-Plugin', 'Google-SearchByImage', 'Google-Site-Verification', 'Google-Structured-Data-Testing-Tool', 'Google-Youtube-Links', 'google_partner_monitoring', 'GoogleDocs', 'GoogleHC\/', 'GoogleProducer', 'GoScraper', 'GoSpotCheck', 'GoSquared-Status-Checker', 'gosquared-thumbnailer', 'GotSiteMonitor', 'grabify', 'Grammarly', 'grouphigh', 'grub-client', 'GTmetrix', 'gvfs\/', 'HAA(A)?RTLAND http client', 'Hatena', 'hawkReader', 'HEADMasterSEO', 'HeartRails_Capture', 'heritrix', 'hledejLevne\.cz\/[0-9]', 'Holmes', 'HootSuite Image proxy', 'Hootsuite-WebFeed\/[0-9]', 'HostTracker', 'ht:\/\/check', 'htdig', 'HTMLParser\/', 'HTTP-Header-Abfrage', 'http-kit', 'HTTP-Tiny', 'HTTP_Compression_Test', 'http_request2', 'http_requester', 'HttpComponents', 'httphr', 'HTTPMon', 'PEAR HTTPRequest', 'httpscheck', 'httpssites_power', 'httpunit', 'HttpUrlConnection', 'httrack', 'hosterstats', 'huaweisymantec', 'HubPages.*crawlingpolicy', 'HubSpot Connect', 'HubSpot Marketing Grader', 'HyperZbozi.cz Feeder', 'i2kconnect\/', 'ichiro', 'IdeelaborPlagiaat', 'IDG Twitter Links Resolver', 'IDwhois\/[0-9]', 'Iframely', 'igdeSpyder', 'IlTrovatore', 'ImageEngine\/', 'Imagga', 'InAGist', 'inbound\.li parser', 'InDesign%20CC', 'infegy', 'infohelfer', 'InfoWizards Reciprocal Link System PRO', 'Instapaper', 'inpwrd\.com', 'Integrity', 'integromedb', 'internet_archive', 'InternetSeer', 'internetVista monitor', 'IODC', 'IOI', 'iplabel', 'IPS\/[0-9]', 'ips-agent', 'IPWorks HTTP\/S Component', 'iqdb\/', 'Irokez', 'isitup\.org', 'iskanie', 'iZSearch', 'janforman', 'Jigsaw', 'Jobboerse', 'jobo', 'Jobrapido', 'JS-Kit', 'KeepRight OpenStreetMap Checker', 'KeyCDN Perf Test', 'Keywords Research', 'KickFire', 'KimonoLabs\/', 'Kml-Google', 'knows\.is', 'kouio', 'KrOWLer', 'kulturarw3', 'KumKie', 'L\.webis', 'Larbin', 'LayeredExtractor', 'LibVLC', 'libwww', 'Licorne Image Snapshot', 'Liferea\/', 'link checker', 'Link Valet', 'link_thumbnailer', 'LinkAlarm\/', 'linkCheck', 'linkdex', 'LinkExaminer', 'linkfluence', 'linkpeek', 'LinkTiger', 'LinkWalker', 'Lipperhey', 'livedoor ScreenShot', 'LoadImpactPageAnalyzer', 'LoadImpactRload', 'LongURL API', 'looksystems\.net', 'ltx71', 'lwp-trivial', 'lycos', 'LYT\.SR', 'mabontland', 'MagpieRSS', 'Mail.Ru', 'MailChimp\.com', 'Mandrill', 'MapperCmd', 'marketinggrader', 'Mediapartners-Google', 'MegaIndex\.ru', 'Melvil Rawi\/', 'MergeFlow-PageReader', 'Metaspinner', 'MetaURI', 'Microsearch', 'Microsoft-WebDAV-MiniRedir', 'Microsoft Data Access Internet Publishing Provider Protocol', 'Microsoft Office ', 'Microsoft Windows Network Diagnostics', 'Mindjet', 'Miniflux', 'mixdata dot com', 'mixed-content-scan', 'Mnogosearch', 'mogimogi', 'Mojolicious \(Perl\)', 'monitis', 'Monitority\/[0-9]', 'montastic', 'MonTools', 'Moreover', 'Morning Paper', 'mowser', 'Mrcgiguy', 'mShots', 'MVAClient', 'nagios', 'Najdi\.si\/', 'Needle\/', 'NETCRAFT', 'NetLyzer FastProbe', 'netresearch', 'NetShelter ContentScan', 'NetTrack', 'Netvibes', 'Neustar WPM', 'NeutrinoAPI', 'NewsBlur .*Finder', 'NewsGator', 'newsme', 'newspaper\/', 'NG-Search', 'nineconnections\.com', 'NLNZ_IAHarvester', 'Nmap Scripting Engine', 'node-superagent', 'node\.io', 'nominet\.org\.uk', 'Norton-Safeweb', 'Notifixious', 'notifyninja', 'nuhk', 'nutch', 'Nuzzel', 'nWormFeedFinder', 'Nymesis', 'Ocelli\/[0-9]', 'oegp', 'okhttp', 'Omea Reader', 'omgili', 'Online Domain Tools', 'OpenCalaisSemanticProxy', 'Openstat\/', 'OpenVAS', 'Optimizer', 'Orbiter', 'OrgProbe\/[0-9]', 'ow\.ly', 'ownCloud News', 'OxfordCloudService\/[0-9]', 'Page Analyzer', 'Page Valet', 'page2rss', 'page_verifier', 'PagePeeker', 'Pagespeed\/[0-9]', 'Panopta', 'panscient', 'parsijoo', 'PayPal IPN', 'Pcore-HTTP', 'Pearltrees', 'peerindex', 'Peew', 'PhantomJS\/', 'Photon\/', 'phpcrawl', 'phpservermon', 'Pi-Monster', 'ping\.blo\.gs\/', 'Pingdom', 'Pingoscope', 'PingSpot', 'pinterest\.com', 'Pizilla', 'Ploetz \+ Zeller', 'Plukkie', 'PocketParser', 'Pompos', 'Porkbun', 'Port Monitor', 'postano', 'PostPost', 'postrank', 'PowerPoint\/', 'Priceonomics Analysis Engine', 'PritTorrent\/[0-9]', 'Prlog', 'probethenet', 'Project 25499', 'Promotion_Tools_www.searchenginepromotionhelp.com', 'prospectb2b', 'Protopage', 'proximic', 'pshtt, https scanning', 'PTST ', 'PTST\/[0-9]+', 'Pulsepoint XT3 web scraper', 'Python-httplib2', 'python-requests', 'Python-urllib', 'Qirina Hurdler', 'QQDownload', 'Qseero', 'Qualidator.com SiteAnalyzer', 'Quora Link Preview', 'Qwantify', 'Radian6', 'RankSonicSiteAuditor', 'Readability', 'RealPlayer%20Downloader', 'RebelMouse', 'redback\/', 'Redirect Checker Tool', 'ReederForMac', 'request\.js', 'ResponseCodeTest\/[0-9]', 'RestSharp', 'RetrevoPageAnalyzer', 'Riddler', 'Rival IQ', 'Robosourcer', 'Robozilla\/[0-9]', 'ROI Hunter', 'RPT-HTTPClient', 'RSSOwl', 'safe-agent-scanner', 'SalesIntelligent', 'SauceNAO', 'SBIder', 'Scoop', 'scooter', 'ScoutJet', 'ScoutURLMonitor', 'Scrapy', 'ScreenShotService\/[0-9]', 'Scrubby', 'search\.thunderstone', 'SearchSight', 'Seeker', 'semanticdiscovery', 'semanticjuice', 'Semiocast HTTP client', 'SEO Browser', 'Seo Servis', 'seo-nastroj.cz', 'Seobility', 'SEOCentro', 'SeoCheck', 'SeopultContentAnalyzer', 'Server Density Service Monitoring', 'servernfo\.com', 'Seznam screenshot-generator', 'Shelob', 'Shoppimon Analyzer', 'ShoppimonAgent\/[0-9]', 'ShopWiki', 'ShortLinkTranslate', 'shrinktheweb', 'SilverReader', 'SimplePie', 'SimplyFast', 'Site-Shot\/', 'Site24x7', 'SiteBar', 'SiteCondor', 'siteexplorer\.info', 'SiteGuardian', 'Siteimprove\.com', 'Sitemap(s)? Generator', 'Siteshooter B0t', 'SiteTruth', 'sitexy\.com', 'SkypeUriPreview', 'slider\.com', 'slurp', 'SMRF URL Expander', 'SMUrlExpander', 'Snappy', 'SniffRSS', 'sniptracker', 'Snoopy', 'sogou web', 'SortSite', 'spaziodati', 'Specificfeeds', 'speedy', 'SPEng', 'Spinn3r', 'spray-can', 'Sprinklr ', 'spyonweb', 'Sqworm', 'SSL Labs', 'StackRambler', 'Statastico\/', 'StatusCake', 'Stratagems Kumo', 'Stroke.cz', 'StudioFACA', 'suchen', 'summify', 'Super Monitoring', 'Surphace Scout', 'SwiteScraper', 'Symfony2 BrowserKit', 'SynHttpClient-Built', 'Sysomos', 'T0PHackTeam', 'Tarantula\/', 'Taringa UGC', 'teoma', 'terrainformatica\.com', 'Test Certificate Info', 'Tetrahedron\/[0-9]', 'The Drop Reaper', 'The Expert HTML Source Viewer', 'theinternetrules', 'theoldreader\.com', 'Thumbshots', 'ThumbSniper', 'TinEye', 'Tiny Tiny RSS', 'topster', 'touche.com', 'Traackr.com', 'truwoGPS', 'tweetedtimes\.com', 'Tweetminster', 'Tweezler\/', 'Twikle', 'Twingly', 'ubermetrics-technologies', 'uclassify', 'UdmSearch', 'Untiny', 'UnwindFetchor', 'updated', 'Upflow', 'URLChecker', 'URLitor.com', 'urlresolver', 'Urlstat', 'UrlTrends Ranking Updater', 'Vagabondo', 'vBSEO', 'via ggpht\.com GoogleImageProxy', 'VidibleScraper\/', 'visionutils', 'vkShare', 'voltron', 'voyager\/', 'VSAgent\/[0-9]', 'VSB-TUO\/[0-9]', 'VYU2', 'w3af\.org', 'W3C-checklink', 'W3C-mobileOK', 'W3C_I18n-Checker', 'W3C_Unicorn', 'wangling', 'WatchMouse', 'WbSrch\/', 'web-capture\.net', 'Web-Monitoring', 'Web-sniffer', 'Webauskunft', 'WebCapture', 'WebClient\/', 'webcollage', 'WebCookies', 'WebCorp', 'WebDoc', 'WebFetch', 'WebImages', 'WebIndex', 'webkit2png', 'webmastercoffee', 'webmon ', 'webscreenie', 'Webshot', 'Website Analyzer\/', 'websitepulse[+ ]checker', 'Websnapr\/', 'Webthumb\/[0-9]', 'WebThumbnail', 'WeCrawlForThePeace', 'WeLikeLinks', 'WEPA', 'WeSEE', 'wf84', 'wget', 'WhatsApp', 'WhatsMyIP', 'WhatWeb', 'WhereGoes\?', 'Whibse', 'Whynder Magnet', 'Windows-RSS-Platform', 'WinHttpRequest', 'wkhtmlto', 'wmtips', 'Woko', 'Word\/', 'WordPress\/', 'wotbox', 'WP Engine Install Performance API', 'wprecon\.com survey', 'WPScan', 'wscheck', 'WWW-Mechanize', 'www\.monitor\.us', 'XaxisSemanticsClassifier', 'Xenu Link Sleuth', 'XING-contenttabreceiver\/[0-9]', 'XmlSitemapGenerator', 'xpymep([0-9]?)\.exe', 'Y!J-(ASR|BSC)', 'Yaanb', 'yacy', 'Yahoo Ad monitoring', 'Yahoo Link Preview', 'YahooCacheSystem', 'YahooYSMcm', 'YandeG', 'yandex', 'yanga', 'yeti', ' YLT', 'Yo-yo', 'Yoleo Consumer', 'yoogliFetchAgent', 'YottaaMonitor', 'yourls\.org', 'Zao', 'Zemanta Aggregator', 'Zend\\\\Http\\\\Client', 'Zend_Http_Client', 'zgrab', 'ZnajdzFoto', 'ZyBorg', '[a-z0-9\-_]*((?<!cu)bot|crawler|archiver|transcoder|spider|uptime|validator|fetcher)');
            if (Swift_Performance::check_option('exclude-crawlers',1, '!=')){
                  return false;
            }

            return preg_match('~('.implode('|', $crawlers).')~', $_SERVER['HTTP_USER_AGENT']);
      }

      /**
       * Is cached version identical for the request
       * @return boolean
       */
      public static function is_identical($key = ''){
            if (Swift_Performance::check_option('resource-saving-mode', 1)){
                  $request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                  $key = (empty($key) && self::is_cacheable_ajax() || self::is_cacheable_dynamic() ? $request_uri . serialize($_REQUEST) : (empty($key) ? $request_uri : $key));
                  $identical = get_transient('swift_performance_is_identical_' . md5($key));
                  if ($identical !== false){
                        return true;
                  }
                  else {
                        set_transient('swift_performance_is_identical_' . md5($key), true, 3600);
                  }
            }

            return false;
      }
      /**
       * Write cached file
       * @param string $file file name
       * @param string $content file content (default empty)
       * @param boolean $force override existing (default false)
       * @return string public URL
       */
      public static function write_file($file, $content = '', $force = false){
            // Play in the sandbox only...
            if (strpos($file, './')){
                  return;
            }

            $dir = SWIFT_PERFORMANCE_CACHE_DIR . dirname($file);
            // Write cached file if file doesn't exists or we use force mode
            if ($force || !file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $file)){
                  if (!file_exists($dir)){
                        @mkdir($dir, 0777, true);
                  }
                  @file_put_contents(SWIFT_PERFORMANCE_CACHE_DIR . $file, $content);

            }

            // Create empty index.html in every folder recursively to prevent directory index in cache
            $current = SWIFT_PERFORMANCE_CACHE_DIR;
            $folders = explode('/', dirname($file));
            for ($i=0;$i<count($folders);$i++){
                  $current .= $folders[$i] . '/';
                  if (in_array($folders[$i], array('js', 'authenticated', 'mobile', 'desktop')) && !isset($folders[$i + 1]) && !file_exists($current . 'index.html')){
                        @touch($current . 'index.html');
                  }
            }

            // Return with cached file URL
            return SWIFT_PERFORMANCE_CACHE_URL . $file;
      }

      /**
       * Clear cache folder recursively
       * @param string $dir
       * @return boolean
       */
      public static function recursive_rmdir($dir = '', $check_filetime = false){
            if (!file_exists(SWIFT_PERFORMANCE_CACHE_DIR . $dir)){
      		return false;
      	}

            if (strpos(realpath(SWIFT_PERFORMANCE_CACHE_DIR . $dir), trim(SWIFT_PERFORMANCE_CACHE_DIR, '/')) === false){
                  return;
            }

      	$files = array_diff(scandir(SWIFT_PERFORMANCE_CACHE_DIR . $dir), array('.','..'));
      	foreach ($files as $file) {
                  if (!$check_filetime || time() - filectime(SWIFT_PERFORMANCE_CACHE_DIR . $dir . '/'. $file) >= Swift_Performance::get_option('cache-expiry-time') ){
      		      is_dir(SWIFT_PERFORMANCE_CACHE_DIR . $dir . '/'. $file) ? self::recursive_rmdir($dir . '/'. $file) : unlink(SWIFT_PERFORMANCE_CACHE_DIR . $dir . '/'. $file);
                  }
      	}
      	return @rmdir(SWIFT_PERFORMANCE_CACHE_DIR . $dir);
      }

      /**
       * Proxy the current request and return the results
       * @return string
       */
      public static function proxy_request(){
            // Headers
            $headers = array('X-Proxy' => 'Swift_Performance');
            foreach ($_SERVER as $key => $value) {
                  $headers[preg_replace('~^http_~i', '', $key)] = $value;
            }

            // Cookies
            $cookies = array();
            foreach ( $_COOKIE as $name => $value ) {
                  $cookies[] = new WP_Http_Cookie( array( 'name' => $name, 'value' => $value ) );
            }

            // Proxy the original request
            $response = wp_remote_post(home_url($_SERVER['REQUEST_URI']), array(
                  'method' => 'POST',
                  'timeout' => 45,
                  'blocking' => true,
                  'headers' => $headers,
                  'body' => $_POST,
                  'cookies' => $cookies
                )
            );

            if (!is_wp_error($response)){
                  return $response['body'];
            }

            return false;
      }

      public static function avoid_mixed_content($html){
            // Avoid mixed content issues
            $html = preg_replace('~src=(\'|")https?:~', 'src=$1', $html);
            $html = preg_replace('~<link rel=\'stylesheet\'((?!href=).)*href=(\'|")https?:~', '<link rel=\'stylesheet\'$1href=$2', $html);
            return $html;
      }

}

// Create instance
if (Swift_Performance::check_option('enable-caching', 1)){
      return new Swift_Performance_Cache();
}
else {
      return false;
}

?>
