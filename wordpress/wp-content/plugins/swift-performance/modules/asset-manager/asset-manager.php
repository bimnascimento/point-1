<?php
class Swift_Performance_Asset_Manager {

      /**
	 * Intermediate image sizes
	 */
	public $image_sizes;

      /**
       * Create Instance
       */
      public function __construct(){
            if (Swift_Performance::check_option('merge-scripts', 1) || Swift_Performance::check_option('merge-styles', 1) || Swift_Performance::check_option('lazy-load-images', 1)){
                  // Include DOM parser
                  include_once 'dom-parser.php';

                  // Do the magic
                  $this->asset_manager();

                  // Proxy 3rd party assets
                  add_action('init', array('Swift_Performance_Asset_Manager', 'proxy_3rd_party_request'));
            }

            // Remove version query string from static resources
            if (Swift_Performance::check_option('normalize-static-resources', 1) && !Swift_Performance::is_admin()){
                  add_filter('style_loader_src', array($this, 'remove_static_ver'), 10, 2);
                  add_filter('script_loader_src', array($this, 'remove_static_ver'), 10, 2);
                  add_filter('get_post_metadata', array($this, 'normalize_vc_custom_css'), 10, 4);
            }

            // Lazy load
            if (Swift_Performance::check_option('lazy-load-images', 1) && !Swift_Performance::is_admin()){
                  add_action('init', array($this, 'intermediate_image_sizes'));
                  add_action('wp_head', function(){

                        if (Swift_Performance::check_option('load-images-on-user-interaction', 1)){
                              $fire = 'var fire=function(){window.removeEventListener("devicemotion",fire);window.removeEventListener("scroll",fire);document.removeEventListener("mousemove",fire);requestAnimationFrame(ll)};window.addEventListener("devicemotion",fire,true);window.addEventListener("scroll",fire,true);document.addEventListener("mousemove",fire);setTimeout(fire,5000);';
                        }
                        else{
                              $fire = 'requestAnimationFrame(ll)';
                        }


                        echo "<script data-dont-merge=\"\">(function(){function iv(a){if(typeof a.getBoundingClientRect!=='function'){return false}var b=a.getBoundingClientRect();return(b.bottom+50>=0&&b.right+50>=0&&b.top-50<=(window.innerHeight||document.documentElement.clientHeight)&&b.left-50<=(window.innerWidth||document.documentElement.clientWidth))}function ll(){var a=document.querySelectorAll('[data-swift-lazy-load]');for(var i in a){if(iv(a[i])){a[i].onload=function(){window.dispatchEvent(new Event('resize'));};a[i].src=(typeof a[i].dataset.src != 'undefined' ? a[i].dataset.src : a[i].src);a[i].srcset=a[i].dataset.srcset;a[i].style=a[i].dataset.style;a[i].removeAttribute('data-swift-lazy-load')}}requestAnimationFrame(ll)}{$fire}})();</script>";
                  },PHP_INT_MAX);
            }

            // Merge assets in background
            if (Swift_Performance::check_option('merge-background-only', 1) && Swift_Performance::check_option('enable-caching',1) && (Swift_Performance_Cache::is_cacheable() || Swift_Performance_Cache::is_cacheable_dynamic()) ){
                  add_action('wp_footer', function(){
                        echo '<script>jQuery.ajax({url: document.location.href,headers: { "X-merge-assets": "true" }});</script>';
                  }, PHP_INT_MAX);
            }
      }

	/**
	 * Init Render Blocking module
	 */
	public function asset_manager() {
            set_time_limit(600);
		if (self::should_merge()){
			add_action('wp_head', function(){
                        if (Swift_Performance::check_option('merge-styles', 1) && self::should_merge()){
				      echo 'CSS_HEADER_PLACEHOLDER';
                        }
			}, 7);

			add_action('wp_footer', function(){
                        if (Swift_Performance::check_option('merge-styles', 1) && self::should_merge()){
                              echo 'CSS_FOOTER_PLACEHOLDER';
                        }
			}, PHP_INT_MAX);

                  add_action('wp_footer', function(){
                        if (Swift_Performance::check_option('merge-scripts', 1) && self::should_merge()){
                              echo 'JS_FOOTER_PLACEHOLDER';
                        }
                  }, PHP_INT_MAX);

			// Manage assets
			ob_start(array($this, 'asset_manager_callback'));
		}

	}

	/**
	 * Remove render blocking assets
	 * @param string $buffer
	 * @return string
	 */
	public function asset_manager_callback($buffer){
            // Don't play with assets if the current page is not cacheable
            if (!self::should_merge()){
                  return $buffer;
            }
            $critical_css = $js = '';
            $css = array();
		$html = swift_performance_str_get_html($buffer);

            // Stop here if something really bad happened
            if ($html === false){
                  return $buffer;
            }

		foreach ($html->find('link[rel="stylesheet"], style, script, img') as $node){
                  // Exclude data-dont-merge
                  if (isset($node->{'data-dont-merge'})){
                        continue;
                  }

                  $media            = (isset($node->media) && !empty($node->media) ? $node->media : 'all');
                  $css[$media]      = (isset($css[$media]) ? $css[$media] : '');
                  $remove_tag       = false;
                  if (Swift_Performance::check_option('merge-styles', 1)){
      			if ($node->tag == 'link'){
                              $src_parts = parse_url(preg_replace('~^//~', 'http://', $node->href));
                              $src = apply_filters('swift_performance_style_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

                              // Exclude styles
                              $exclude_strings = array_filter(Swift_Performance::get_option('exclude-styles'));
                              if (!empty($exclude_strings)){
                                    if (preg_match('~('.implode('|', $exclude_strings).')~', $src)){
                                          continue;
                                    }
                              }

                              $_css = '';
                              if (strpos($src, apply_filters('style_loader_src', home_url(), 'dummy-handle')) !== false){
                                    if (strpos($src, '.php') !== false){
                                          $response = wp_remote_get(preg_replace('~^//~', 'http://', $node->href), array('sslverify' => false, 'timeout' => 15));
                                          if (!is_wp_error($response)){
                                                if(in_array($response['response']['code'], array(200,304))){
                                                      $_css = $response['body'];
      							}
                                          }
                                    }
                                    else {
                                          $_css = file_get_contents(str_replace(apply_filters('style_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src));
                                    }
                                    $remove_tag = true;
                              }
                              else if (Swift_Performance::check_option('merge-styles-exclude-3rd-party', 1, '!=')){
                                    $response = wp_remote_get(preg_replace('~^//~', 'http://', $node->href), array('sslverify' => false, 'timeout' => 15));
                                    if (!is_wp_error($response)){
                                          if(in_array($response['response']['code'], array(200,304))){
                                                $_css = $response['body'];
							}

                                          // Remove merged and missing CSS files
                                          if (in_array($response['response']['code'], array(200, 304, 404, 500, 403))){
								 $remove_tag = true;
							}
                                    }
                              }

                              // Avoid mixed content (fonts, etc)
                              $_css = preg_replace('~https?:~', '', $_css);

      				$GLOBALS['swift_css_realpath_basepath'] = $node->href;
      				$_css = preg_replace_callback('~@import url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'bypass_css_import'), $_css);
      				$_css = preg_replace_callback('~url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'css_realpath_url'), $_css);
      				$_css = preg_replace_callback('~(\.\.?/)+~', array($this, 'css_realpath'), $_css);

                              if (Swift_Performance::check_option('minify-css', 1)){
            				$_css = preg_replace('~/\*.*?\*/~s', '', $_css);
            				$_css = preg_replace('~\r?\n~', '', $_css);
            				$_css = preg_replace('~(\s{2}|\t)~', ' ', $_css);
                              }

      				$css[$media] .= $_css;
      			}
      			else if ($node->tag == 'style'){
      				$css[$media] .= $node->innertext;
                              $remove_tag = true;
      			}
                  }
                  if (Swift_Performance::check_option('merge-scripts', 1)){
      			if($node->tag == 'script' && (!isset($node->type) || strpos($node->type, 'javascript') !== false)){
      				if (isset($node->src) && !empty($node->src)){

                                    $src_parts = parse_url(preg_replace('~^//~', 'http://', $node->src));
                                    $src = apply_filters('swift_performance_script_src', (isset($src_parts['scheme']) && !empty($src_parts['scheme']) ? $src_parts['scheme'] : 'http') . '://' . $src_parts['host'] . $src_parts['path']);

                                    // Exclude scripts
                                    $exclude_strings = array_filter(Swift_Performance::get_option('exclude-scripts'));
                                    if (!empty($exclude_strings)){
                                          if (preg_match('~('.implode('|', $exclude_strings).')~', $src)){
                                                continue;
                                          }
                                    }

                                    if (strpos($src, apply_filters('script_loader_src', home_url(), 'dummy-handle')) !== false){
                                          if (strpos($src, '.php') != false){
                                                $response = wp_remote_get(preg_replace('~^//~', 'http://', $node->src), array('sslverify' => false, 'timeout' => 15));
                                                if (!is_wp_error($response)){
                                                      if (in_array($response['response']['code'], array(200, 304))){
                                                            $js .= "\n" . 'try{' . self::minify_js($response['body']) . '}catch(e){/*silent fail*/}' . "\n";
                                                      }
                                                }
                                          }
                                          else {
                                                $js .= "\n" . 'try{' . self::minify_js(file_get_contents(str_replace(apply_filters('script_loader_src', home_url(), 'dummy-handle'), ABSPATH, $src))) . '}catch(e){/*silent fail*/}' . "\n";
                                          }
                                          $remove_tag = true;
                                    }
                                    else if (Swift_Performance::check_option('merge-scripts-exclude-3rd-party', 1, '!=')){
                                          $response = wp_remote_get(preg_replace('~^//~', 'http://', $node->src), array('sslverify' => false, 'timeout' => 15));
                                          if (!is_wp_error($response)){
                                                if (in_array($response['response']['code'], array(200, 304))){
                                                      $js .= "\n" . 'try{' . self::minify_js($response['body']) . '}catch(e){/*silent fail*/}' . "\n";
                                                }

                                                // Remove merged and missing js files
                                                if (in_array($response['response']['code'], array(200, 304, 404, 500, 403))){
                                                       $remove_tag = true;
                                                }
                                          }
                                    }
      				}
      				else if (Swift_Performance::check_option('exclude-script-localizations', 1, '!=') || strpos($node->innertext, '<![CDATA[') === false){
                                          $js .= "\n".'try{' . self::minify_js($node->innertext) . '}catch(e){/*silent fail*/}'."\n";
                                          $remove_tag = true;
      				}
      			}
                  }
                  if($node->tag == 'img'){
                        $id = '';
                        if (Swift_Performance::check_option('force-responsive-images', 1) && !isset($node->srcset)){
                              // Get image id
                              $id = Swift_Performance::get_image_id($node->src);

                              if (!empty($id)){
                                    $size = (isset($node->width) && isset($node->height) ? array($node->width, $node->height) : 'full');
                                    $node->outertext = wp_get_attachment_image($id, $size);
                                    preg_match('~srcset="([^"]*)"~', $node->outertext, $_srcset);
                                    preg_match('~sizes="([^"]*)"~', $node->outertext, $_sizes);
                                    $node->srcset = $_srcset[1];
                                    $node->sizes = $_sizes[1];
                              }
                        }
                        if (Swift_Performance::check_option('lazy-load-images', 1)){
                              $attachment = new stdClass;
                              $attributes = '';

                              // Get image id
                              if (empty($id)){
                                    $id = Swift_Performance::get_image_id($node->src);
                              }

                              // Collect original attributes
                              $args = array();
                              foreach ($node->attr as $key => $value) {
                                    $args[$key] = $value;
                              }

                              // Change src and srcset
                              $args = $this->lazy_load_images($args, $id);

                              // Change image tag
                              if ($args !== false){
                                    foreach($args as $key=>$value){
                                          $attributes .= $key . '="' . $value . '" ';
                                    }

                                    $node->outertext = '<img '.$attributes.'>';
                              }
                        }
                        if (Swift_Performance::check_option('base64-small-images', 1)){
                              // Exclude images
                              $exclude_strings = array_filter(Swift_Performance::get_option('exclude-base64-small-images'));
                              if (!empty($exclude_strings)){
                                    if (preg_match('~('.implode('|', $exclude_strings).')~', $node->src)){
                                          continue;
                                    }
                              }
                              $attribute  = (isset($node->{'data-src'}) ? 'data-src' : 'src');
                              $img_path   = str_replace(apply_filters('swift_performance_media_host', home_url()), ABSPATH, $node->$attribute);
                              if (file_exists($img_path) && filesize($img_path) <= Swift_Performance::get_option('base64-small-images-size')){
                                    $mime             = preg_match('~\.jpg$~', $img_path) ? 'jpeg' : 'png';
                                    $node->$attribute = 'data:image/'.$mime.';base64,' . base64_encode(file_get_contents($img_path));
                              }
                        }

                  }

                  // Remove tag
                  if ($remove_tag){
                        $node->outertext = '';
                  }
		}

            $_html = $html;

            // Create critical css
            if (Swift_Performance::check_option('merge-styles', 1) && isset($css['all'])){

                  // Move screen CSS to "all" inside media query
                  if (isset($css['screen']) && !empty($css['screen'])){
                        $css['all'] .= "@media screen {\n{$css['screen']}\n}";
                        unset($css['screen']);
                  }

                  // Collect classes from the document and js
      		preg_match_all('~class=(\'|")([^\'"]+)(\'|")~', $_html . $js, $class_attributes);

                  // Collect ids from the document and js
      		preg_match_all('~id=(\'|")([^\'"]+)(\'|")~', $_html . $js, $id_attributes);

                  // Compress class names
                  $should_compress_css = Swift_Performance::check_option('compress-css',1) && Swift_Performance::check_option('merge-scripts', 1) && Swift_Performance::check_option('merge-styles', 1);
                  if ($should_compress_css){
                        // Add padding to class tags
                        $_html = preg_replace('~class=("|\')([^"\']*)("|\')~', 'class=$1 $2 $3', $_html);

                        // Don't make short classes classnames which were used in regex
                        preg_match_all('~class([\^\*\$])?=("|\')?([^"\'\)]*)?("|\')?~', $_html, $dont_short_classes);
                  }

                  // Use API if available
                  $api_args = array(
                        'css'                   => $css['all'],
                        'class_attributes'      => $class_attributes,
                        'id_attributes'         => $id_attributes,
                        'dont_short_classes'    => $dont_short_classes,
                        'settings'              => array(
                              'compress-css'          => $should_compress_css,
                              'remove-keyframes'      => Swift_Performance::check_option('remove-keyframes',1)
                        )
                  );

                  $response = Swift_Performance::compute_api($api_args);

                  if (!empty($response)){
                        $api                    = json_decode($response, true);
                        $critical_css           = $api['critical_css'];
                        $shortened_classes      = $api['shortened_classes'];
                  }
                  else{
                        $critical_css = $css['all'];

                        // Encode content attribute for pseudo elements before parsing
            		$critical_css = preg_replace_callback('~content\s?:\s?(\'|")([^\'"]*)(\'|")~', function($matches){
            			return 'content: ' . $matches[1] . base64_encode($matches[2]) . $matches[1];
            		}, $critical_css);

                        // Encode URLS
            		$critical_css = preg_replace_callback('~//([^"\'\)]*)~i', function($matches){
            			return 'safeencodedurl_o' . base64_encode($matches[0]) . '!safeencodedurl_c';
            		}, $critical_css);

                        // Found classes
            		$found_classes = array();
            		$not_found_classes = array();
            		foreach($class_attributes[2] as $class_attribute){
            			$classes = explode(' ', $class_attribute);
            			foreach ($classes as $class){
            				$class = trim($class);
            				$found_classes[$class] = $class;
            			}
            		}

                        // Parse css rules
            		preg_match_all('~([^@%\{\}]+)\{([^\{\}]+)\}~', $css['all'], $parsed_css);

                        // Iterate through css rules, and remove unused instances
            		for ($i=0; $i<count($parsed_css[1]); $i++){
            			$_selector = explode(',', $parsed_css[1][$i]);
            			foreach ($_selector as $key => $selector){
                                    if (preg_match('~:(hover|active|focus|visited)~', $selector)){
                                          unset($_selector[$key]);
                                          preg_match_all('~\.([a-zA-Z0-9-_]+)~', $selector, $selector_classes);
                                          foreach($selector_classes[1] as $selecor_class){
                                                $not_found_classes[$selecor_class] = $selecor_class;
                                          }
                                    }
            				else if (strpos($selector, ':not') == false){
            					preg_match_all('~\.([a-zA-Z0-9-_]+)~', $selector, $selector_classes);

            					foreach ($selector_classes[1] as $selector_class){
            						$selector_class = trim($selector_class);
            						if (isset($not_found_classes[$selector]) || !isset($found_classes[$selector_class])){
            							unset($_selector[$key]);
            							$not_found_classes[$selector] = $selector;
            							break;
            						}
            					}
            				}
            			}


            			$_selector = array_filter($_selector);
            			if (empty($_selector)){
            				$critical_css = str_replace($parsed_css[1][$i] . "{" . $parsed_css[2][$i] . '}', '', $critical_css);
            			}
            		}

                        // Found ids
            		$found_ids = array();
            		$not_found_ids = array();
            		foreach($id_attributes[2] as $id_attribute){
            			$found_ids[$id_attribute] = $id_attribute;
            		}

                        // Iterate through css rules, and remove unused instances
            		for ($i=0; $i<count($parsed_css[1]); $i++){
            			$_selector = explode(',', $parsed_css[1][$i]);
            			foreach ($_selector as $key => $selector){

            				preg_match_all('~#([a-zA-Z0-9-_]+)~', $selector, $selector_ids);

            				foreach ($selector_ids[1] as $selector_id){
            					$selector_id = trim($selector_id);
            					if (isset($not_found_ids[$selector]) || !isset($found_ids[$selector_id])){
            						unset($_selector[$key]);
            						$not_found_ids[$selector] = $selector;
            						break;
            					}
            				}

            			}


            			$_selector = array_filter($_selector);
            			if (empty($_selector)){
            				$critical_css = str_replace($parsed_css[1][$i] . "{" . $parsed_css[2][$i] . '}', '', $critical_css);
            			}
            		}

                        // Remove non existent classes
                        foreach ($not_found_classes as $not_found_class){
                              $critical_css = str_replace('.' . $not_found_class . ',', '', $critical_css);
                        }

                        // Remove non existent ids
                        foreach ($not_found_ids as $not_found_id){
                              $critical_css = str_replace('#' . $not_found_id . ',', '', $critical_css);
                        }

                        // Decode content attribute for pseudo elements
                        $critical_css = preg_replace_callback('~content\s?:\s?(\'|")([^\'"]*)(\'|")~', function($matches){
                              return 'content: ' . $matches[1] . base64_decode($matches[2]) . $matches[1];
                        }, $critical_css);

                        // Decode URLS
                        $critical_css = preg_replace_callback('~safeencodedurl_o([^\!]*)\!safeencodedurl_c~i', function($matches){
            			return base64_decode($matches[1]);
            		}, $critical_css);

                        // Remove emptied media queries
            		$critical_css = preg_replace('~@media([^\{]+)\{\}~','',$critical_css);

                        // Remove empty rules
            		$critical_css = preg_replace('~([^\s\}\)]+)\{\}~','',$critical_css);

                        // Remove keyframes
                        if (Swift_Performance::check_option('remove-keyframes',1)){
                              $critical_css = preg_replace('~@([^\{]*)keyframes([^\{]*){((?!\}\}).)*\}\}~','',$critical_css);
                              $critical_css = preg_replace('~(-webkit-|-moz-|-o|-ms-)?animation:([^;]*);~','',$critical_css);
                        }

                        // Remove leading semicolon in ruleset
                        $critical_css = str_replace(';}', '}', $critical_css);

                        // Remove unnecessary whitespaces
                        $critical_css = preg_replace('~(;|\)|\{|\}|"|\'|:|,)\s+~', '$1', $critical_css);
                        $critical_css = preg_replace('~\s+(;|\)|\{|\}|"|\'|:|,)~', '$1', $critical_css);

                        // Remove apostrophes and quotes
                        $critical_css = preg_replace('~\(("|\')~', '(', $critical_css);
                        $critical_css = preg_replace('~("|\')\)~', ')', $critical_css);

                        // Compress colors
                        $critical_css = str_replace(array(
                              '#000000',
                              '#111111',
                              '#222222',
                              '#333333',
                              '#444444',
                              '#555555',
                              '#666666',
                              '#777777',
                              '#888888',
                              '#999999',
                              '#aaaaaa',
                              '#bbbbbb',
                              '#cccccc',
                              '#dddddd',
                              '#eeeeee',
                              '#ffffff',
                        ), array(
                              '#000',
                              '#111',
                              '#222',
                              '#333',
                              '#444',
                              '#555',
                              '#666',
                              '#777',
                              '#888',
                              '#999',
                              '#aaa',
                              '#bbb',
                              '#ccc',
                              '#ddd',
                              '#eee',
                              '#fff',
                        ), $critical_css);

                        // Compress class names
                        if ($should_compress_css){
                              $sc_count = 0;
                              $shortened_classes = array();
                              preg_match_all('~\.(([a-zA-Z]+)([a-zA-Z0-9-_]+))~', $critical_css, $all_classes);
                              foreach(array_unique($all_classes[1]) as $fc){
                                    if (!empty($fc)){
                                          // Skip classes which were used in regex
                                          foreach ($dont_short_classes[3] as $ds){
                                                if (!empty($ds) && strpos($fc, $ds) !== false){
                                                      continue;
                                                }
                                          }
                                          $sc = '_' .base_convert($sc_count,10,35);

                                          // Prevent using existing classes
                                          while (isset($found_classes[$sc])){
                                                $sc_count++;
                                                $sc = '_' .base_convert($sc_count,10,35);
                                          }

                                          // Avoid longer "short" names
                                          if (strlen($sc) > strlen($fc)){
                                                continue;
                                          }

                                          $shortened_classes[$fc] = $sc;

                                          $critical_css = preg_replace('~\.'.preg_quote($fc).'(\s|,|\.|\[|\{|\+|>|:+)~', '.'.$sc.'$1', $critical_css);
                                          $sc_count++;
                                    }
                              }
                        }
                  }
            }

            // Add compressed classes to HTML
            if ($should_compress_css){
                  foreach((array)$shortened_classes as $fc => $sc){
                        $_html = preg_replace('~class=("|\')([^"\']*)?\s+'.preg_quote($fc).'\s+([^"\']*)?("|\')~', 'class=$1$2 '. $fc .' ' . $sc.' $3$4', $_html);
                  }
            }

            // Convert absolute paths to relative
            $critical_css = str_replace(home_url(), parse_url(home_url(), PHP_URL_PATH), $critical_css);
            $critical_css = str_replace(preg_replace('~https?:~', '', home_url()), parse_url(home_url(), PHP_URL_PATH), $critical_css);


            // Save CSS
            $defered_styles = '';
            foreach ((array)$css as $key => $content){
                  if (!empty($content)){
                        if ($key == 'all' && Swift_Performance::check_option('inline_full_css', 1)){
                              $defered_styles .= '<style>'.$content.'</style>';
                        }
                        else {
                              $defered_styles .= '<link rel="stylesheet" href="'.apply_filters('style_loader_src', Swift_Performance_Cache::write_file('css/' . md5($content) . '.css', $content), 'swift-performance-full-'.$key).'" media="'.$key.'">';
                        }
                  }
            }

            // Proxy some 3rdparty assets
            if (Swift_Performance::check_option('proxy-3rd-party-assets', 1)){
                  $js = preg_replace_callback('~(https?:)?//([\.a-z0-9_-]*)\.(xn--clchc0ea0b2g2a9gcd|xn--hlcj6aya9esc7a|xn--hgbk6aj7f53bba|xn--xkc2dl3a5ee0h|xn--mgberp4a5d4ar|xn--11b5bs3a9aj6g|xn--xkc2al3hye2a|xn--80akhbyknj4f|xn--mgbc0a9azcg|xn--lgbbat1ad8j|xn--mgbx4cd0ab|xn--mgbbh1a71e|xn--mgbayh7gpa|xn--mgbaam7a8h|xn--9t4b11yi5a|xn--ygbi2ammx|xn--yfro4i67o|xn--fzc2c9e2c|xn--fpcrj9c3d|xn--ogbpf8fl|xn--mgb9awbf|xn--kgbechtv|xn--jxalpdlp|xn--3e0b707e|xn--s9brj9c|xn--pgbs0dh|xn--kpry57d|xn--kprw13d|xn--j6w193g|xn--h2brj9c|xn--gecrj9c|xn--g6w251d|xn--deba0ad|xn--80ao21a|xn--45brj9c|xn--0zwm56d|xn--zckzah|xn--wgbl6a|xn--wgbh1c|xn--o3cw4h|xn--fiqz9s|xn--fiqs8s|xn--90a3ac|xn--p1ai|travel|museum|post|name|mobi|jobs|info|coop|asia|arpa|aero|xxx|tel|pro|org|net|mil|int|gov|edu|com|cat|biz|zw|zm|za|yt|ye|ws|wf|vu|vn|vi|vg|ve|vc|va|uz|uy|us|uk|ug|ua|tz|tw|tv|tt|tr|tp|to|tn|tm|tl|tk|tj|th|tg|tf|td|tc|sz|sy|sx|sv|su|st|sr|so|sn|sm|sl|sk|sj|si|sh|sg|se|sd|sc|sb|sa|rw|ru|rs|ro|re|qa|py|pw|pt|ps|pr|pn|pm|pl|pk|ph|pg|pf|pe|pa|om|nz|nu|nr|np|no|nl|ni|ng|nf|ne|nc|na|mz|my|mx|mw|mv|mu|mt|ms|mr|mq|mp|mo|mn|mm|ml|mk|mh|mg|me|md|mc|ma|ly|lv|lu|lt|ls|lr|lk|li|lc|lb|la|kz|ky|kw|kr|kp|kn|km|ki|kh|kg|ke|jp|jo|jm|je|it|is|ir|iq|io|in|im|il|ie|id|hu|ht|hr|hn|hm|hk|gy|gw|gu|gt|gs|gr|gq|gp|gn|gm|gl|gi|gh|gg|gf|ge|gd|gb|ga|fr|fo|fm|fk|fj|fi|eu|et|es|er|eg|ee|ec|dz|do|dm|dk|dj|de|cz|cy|cx|cw|cv|cu|cr|co|cn|cm|cl|ck|ci|ch|cg|cf|cd|cc|ca|bz|by|bw|bv|bt|bs|br|bo|bn|bm|bj|bi|bh|bg|bf|be|bd|bb|ba|az|ax|aw|au|at|as|ar|aq|ao|an|am|al|ai|ag|af|ae|ad|ac)([\.\/a-z0-9-_]*)~i', array('Swift_Performance_Asset_Manager', 'asset_proxy_callback') ,$js);
            }

            // Javascript to remove critical CSS after window.load
            $js = 'document.getElementById("critical-css").remove();' . $js;

            // Write critical CSS
            if (Swift_Performance::check_option('inline_critical_css', 1)){
		                $_html = str_replace('CSS_HEADER_PLACEHOLDER', '<style id="critical-css">'.$critical_css.'</style>',$_html);
            }
            else {
                  $_html = str_replace('CSS_HEADER_PLACEHOLDER', '<link id="critical-css" rel="stylesheet" href="'.apply_filters('style_loader_src', Swift_Performance_Cache::write_file('css/' . md5($critical_css) . '.css', $critical_css), 'swift-performance-critical').'" media="all">', $_html);
            }

            // Merged Javascripts
		$_html = str_replace('JS_FOOTER_PLACEHOLDER', '<script src="'.apply_filters('script_loader_src', Swift_Performance_Cache::write_file('js/' . md5($js) . '.js', $js), 'swift-performance-merged').'" type="text/javascript" defer></script>',$_html);

            // Merged CSS
		$_html = str_replace('CSS_FOOTER_PLACEHOLDER', $defered_styles, $_html);

            if (Swift_Performance::check_option('minify-html',1)){
                  // Thanks for ridgerunner (http://stackoverflow.com/questions/5312349/minifying-final-html-output-using-regular-expressions-with-codeigniter)
                  $re = '%# Collapse whitespace everywhere but in blacklisted elements.
                          (?>             # Match all whitespans other than single space.
                            [^\S ]\s*     # Either one [\t\r\n\f\v] and zero or more ws,
                          | \s{2,}        # or two or more consecutive-any-whitespace.
                          ) # Note: The remaining regex consumes no text at all...
                          (?=             # Ensure we are not in a blacklist tag.
                            [^<]*+        # Either zero or more non-"<" {normal*}
                            (?:           # Begin {(special normal*)*} construct
                              <           # or a < starting a non-blacklist tag.
                              (?!/?(?:textarea|pre|script)\b)
                              [^<]*+      # more non-"<" {normal*}
                            )*+           # Finish "unrolling-the-loop"
                            (?:           # Begin alternation group.
                              <           # Either a blacklist start tag.
                              (?>textarea|pre|script)\b
                            | \z          # or end of file.
                            )             # End alternation group.
                          )  # If we made it here, we are not in a blacklist tag.
                          %Six';
                      $_html = preg_replace($re, ' ', $_html);
            }

		return $_html;
	}

	/**
	 * Change relative paths to absolute one
	 */
	public function css_realpath($matches){
		$url = parse_url($GLOBALS['swift_css_realpath_basepath']);
		return (isset($url['scheme']) ? $url['scheme'] .':' : '') . '//' . $url['host'] . trailingslashit(dirname($url['path'])) . $matches[0];
	}

	/**
	 * Change relative paths to absolute one for urls
	 */
	public function css_realpath_url($matches){
		if (preg_match('~^(http|//|\.|data)~',$matches[2])){
			return $matches[0];
		}
		$url = parse_url($GLOBALS['swift_css_realpath_basepath']);
		return 'url(' . $matches[1] . (isset($url['scheme']) ? $url['scheme'] .':' : '') . '//' . $url['host'] . trailingslashit(dirname($url['path'])) . trim($matches[2],"'") . $matches[1] . ')';
	}

	/**
	 * Include imported CSS
	 */
	public function bypass_css_import($matches){
		if (preg_match('~^(http|//|\.|data)~',$matches[2])){
			return $matches[0];
		}
		$url = parse_url($GLOBALS['swift_css_realpath_basepath']);

		$response 	= wp_remote_get($url['scheme'] . '://' . $url['host'] . trailingslashit(dirname($url['path'])) . trim($matches[2],"'"), array('sslverify' => false));
		if (!is_wp_error($response)){
			$response['body'] = preg_replace_callback('~@import url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'bypass_css_import'), $response['body']);
			$response['body'] = preg_replace_callback('~url\((\'|")?([^\("\']*)(\'|")?\)~', array($this, 'css_realpath_url'), $response['body']);
			$response['body'] = preg_replace_callback('~(\.\.?/)+~', array($this, 'css_realpath'), $response['body']);
                  if (Swift_Performance::check_option('minify-css', 1)){
      			$response['body'] = preg_replace('~/\*.*?\*/~s', '', $response['body']);
      			$response['body'] = preg_replace('~\r?\n~', '', $response['body']);
      			$response['body'] = preg_replace('~(\s{2,}|\t)~', ' ', $response['body']);
                  }
			return $response['body'];
		}
	}

      /**
       * Remove query string from JS/CSS
       * @param string $tag
       * @param srting $handle
       * @return string
       */
      public function remove_static_ver( $src ) {
            if( strpos( $src, '?ver=' ) ){
                  $src = remove_query_arg( 'ver', $src );
            }
            return $src;
      }

      /**
       * Remove query string from images
       * @param string $css
       * @return string
       */
      public function normalize_vc_custom_css($meta_value, $object_id, $meta_key, $single ){
            global $swift_performance_get_metadata_filtering;
            if ($swift_performance_get_metadata_filtering !== true && ($meta_key == '_wpb_shortcodes_custom_css' || $meta_key == '_wpb_post_custom_css')){
                  $swift_performance_get_metadata_filtering = true;
                  $meta_value = preg_replace('~\.(jpe?g|gif|png)\?id=(\d*)~',".$1", get_post_meta( $object_id, $meta_key, true ));
                  $swift_performance_get_metadata_filtering = false;
                  return $meta_value;
            }
            return $meta_value;
      }

      /**
       * Lazy load images
       * @param array $args
       * @return array
       */
      public function lazy_load_images($args, $id){
            $upload_dir = wp_upload_dir();
            // Is lazy load image exists already?
            $intermediate = image_get_intermediate_size($id, 'swift_performance_lazyload');
            if (!empty($intermediate)) {
                  $lazy_load_src[0] = str_replace(basename($args['src']), $intermediate['file'], $args['src']);
            }
            else {
                  require_once(ABSPATH . 'wp-admin/includes/image.php');
                  require_once(ABSPATH . 'wp-admin/includes/file.php');
                  require_once(ABSPATH . 'wp-admin/includes/media.php');
                  // Regenerate thumbnails
                  wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, get_attached_file($id) ) );
                  // Second try
                  $intermediate = image_get_intermediate_size($id, 'swift_performance_lazyload');
                  if (!empty($intermediate)) {
                        $lazy_load_src[0] = str_replace(basename($args['src']), $intermediate['file'], $args['src']);
                  }
                  // Give it up if we can't generate new size (eg: disk is full)
                  else{
                        return $args;
                  }
            }

            if (!file_exists(str_replace(apply_filters('swift_performance_media_host',$upload_dir['baseurl']), $upload_dir['basedir'], $lazy_load_src[0]))){
                  return $args;
            }

            // Force sizes
            $width = (isset($args['width']) ? $args['width'] .'px' : '');
            $height = (isset($args['height']) ? $args['height'] .'px' : '');

            if (empty($width) || empty($height)){
                  $metadata = get_post_meta($id, '_wp_attachment_metadata', true);
                  foreach((array)$metadata['sizes'] as $is){
                        if (preg_match('~'.$is['file'].'$~', $args['src'])){
                              $width = $is['width'] . 'px';
                              $height = $is['height'] . 'px';
                        }
                  }
            }

            if (Swift_Performance::check_option('base64-lazy-load-images',1) || Swift_Performance::check_option('base64-small-images',1)){
                  $mime              = preg_match('~\.jpg$~', $lazy_load_src[0]) ? 'jpeg' : 'png';
                  $lazy_load_src[0] = 'data:image/'.$mime.';base64,' . base64_encode(file_get_contents(str_replace(apply_filters('swift_performance_media_host',$upload_dir['baseurl']), $upload_dir['basedir'], $lazy_load_src[0])));
            }

            // Override arguments
            $args['data-src'] = $args['src'];
            $args['data-srcset'] = (isset($args['srcset']) ? $args['srcset'] : '');
            $args['data-sizes'] = (isset($args['sizes']) ? $args['sizes'] : '');
            $args['src'] = $lazy_load_src[0];
            $args['data-swift-lazy-load'] = 'true';
            $args['data-style'] = isset($args['style']) ? $args['style'] : '';
            $args['style'] = (isset($args['style']) ? trim($args['style'], ';') . ';' : '') . ' ' . 'width:' . $width.';height:' . $height;
            unset($args['srcset']);
            unset($args['sizes']);
            return $args;
      }

      /**
       * Get image sizes
       */
      public function intermediate_image_sizes() {
            // Add lazy load
            add_image_size( 'swift_performance_lazyload', 20, 20 );
      }

      /**
       * Check should merge assets
       * @return boolean
       */
      public static function should_merge(){
            return ((Swift_Performance::check_option('merge-background-only', 1, '!=') || isset($_SERVER['HTTP_X_MERGE_ASSETS'])) && (Swift_Performance_Cache::is_cacheable() || Swift_Performance_Cache::is_cacheable_dynamic()));
      }

      /**
       * Minify given javascript
       * @param string $js
       * @return string
       */
      public static function minify_js($js){
            try {
                  //Minify it
                  require_once 'JSMin.class.php';
                  return \Swift_Performance_JSMin::minify($js);
            } catch (Exception $e) {
                  //Silent fail
            }
            return $js;
      }

      /**
       * Proxy 3rd party requests and cache results
       * @return string
       */
      public static function proxy_3rd_party_request(){
            $cache_path = str_replace(ABSPATH, '', SWIFT_PERFORMANCE_CACHE_DIR);

            if (strpos($_SERVER['REQUEST_URI'], $cache_path . 'assetproxy') !== false){
                  $asset_path = preg_replace('~^/([abcdef0-9]*)/~', '', str_replace($cache_path . 'assetproxy/', '', $_SERVER['REQUEST_URI']));
                  $asset_path = str_replace(array('.assetproxy.js', '.assetproxy.swift.css'), '', $asset_path);

                  $response = wp_remote_get('http://' . $asset_path);
                  if (!is_wp_error($response)){

                        // Find 3rd party assets recursively
                        $response['body'] = preg_replace_callback('~(https?:)?//([\.a-z0-9_-]*)\.(xn--clchc0ea0b2g2a9gcd|xn--hlcj6aya9esc7a|xn--hgbk6aj7f53bba|xn--xkc2dl3a5ee0h|xn--mgberp4a5d4ar|xn--11b5bs3a9aj6g|xn--xkc2al3hye2a|xn--80akhbyknj4f|xn--mgbc0a9azcg|xn--lgbbat1ad8j|xn--mgbx4cd0ab|xn--mgbbh1a71e|xn--mgbayh7gpa|xn--mgbaam7a8h|xn--9t4b11yi5a|xn--ygbi2ammx|xn--yfro4i67o|xn--fzc2c9e2c|xn--fpcrj9c3d|xn--ogbpf8fl|xn--mgb9awbf|xn--kgbechtv|xn--jxalpdlp|xn--3e0b707e|xn--s9brj9c|xn--pgbs0dh|xn--kpry57d|xn--kprw13d|xn--j6w193g|xn--h2brj9c|xn--gecrj9c|xn--g6w251d|xn--deba0ad|xn--80ao21a|xn--45brj9c|xn--0zwm56d|xn--zckzah|xn--wgbl6a|xn--wgbh1c|xn--o3cw4h|xn--fiqz9s|xn--fiqs8s|xn--90a3ac|xn--p1ai|travel|museum|post|name|mobi|jobs|info|coop|asia|arpa|aero|xxx|tel|pro|org|net|mil|int|gov|edu|com|cat|biz|zw|zm|za|yt|ye|ws|wf|vu|vn|vi|vg|ve|vc|va|uz|uy|us|uk|ug|ua|tz|tw|tv|tt|tr|tp|to|tn|tm|tl|tk|tj|th|tg|tf|td|tc|sz|sy|sx|sv|su|st|sr|so|sn|sm|sl|sk|sj|si|sh|sg|se|sd|sc|sb|sa|rw|ru|rs|ro|re|qa|py|pw|pt|ps|pr|pn|pm|pl|pk|ph|pg|pf|pe|pa|om|nz|nu|nr|np|no|nl|ni|ng|nf|ne|nc|na|mz|my|mx|mw|mv|mu|mt|ms|mr|mq|mp|mo|mn|mm|ml|mk|mh|mg|me|md|mc|ma|ly|lv|lu|lt|ls|lr|lk|li|lc|lb|la|kz|ky|kw|kr|kp|kn|km|ki|kh|kg|ke|jp|jo|jm|je|it|is|ir|iq|io|in|im|il|ie|id|hu|ht|hr|hn|hm|hk|gy|gw|gu|gt|gs|gr|gq|gp|gn|gm|gl|gi|gh|gg|gf|ge|gd|gb|ga|fr|fo|fm|fk|fj|fi|eu|et|es|er|eg|ee|ec|dz|do|dm|dk|dj|de|cz|cy|cx|cw|cv|cu|cr|co|cn|cm|cl|ck|ci|ch|cg|cf|cd|cc|ca|bz|by|bw|bv|bt|bs|br|bo|bn|bm|bj|bi|bh|bg|bf|be|bd|bb|ba|az|ax|aw|au|at|as|ar|aq|ao|an|am|al|ai|ag|af|ae|ad|ac)([\.\/a-z0-9-_]*)~i', array('Swift_Performance_Asset_Manager', 'asset_proxy_callback'), $response['body']);

                        $prefix = hash('crc32',date('Y-m-d H')) . '/';
                        Swift_Performance_Cache::write_file('assetproxy/' . $prefix . parse_url($asset_path, PHP_URL_PATH), $response['body']);
                        header('Content-Type: ' . (preg_match('~\.js$~', parse_url($asset_path, PHP_URL_PATH)) ? 'text/javascript' : 'text/css'));
                        echo $response['body'];
                  }
                  die;
            }
      }

      /**
       * Clear assets cache
       */
      public static function clear_assets_cache(){
            Swift_Performance_Cache::recursive_rmdir('assetproxy');
      }

      /**
       * Get rid 3rd party js/css files and pass them to proxy
       * @param array $matches
       * @return string
       */
      public static function asset_proxy_callback($matches){
            // Skip excluded assets
            foreach (array_filter((array)Swift_Performance::get_option('exclude-3rd-party-assets')) as $exclude){
                  if (strpos($matches[0], $exclude) !== false){
                        return $matches[0];
                  }
            }

            $test = false;
            // Is it js/css file?
            if (preg_match('~(\.((?!json)js|css))$~',$matches[4])){
                  $test = true;
            }
            // Really?
            if (!$test){
                  $response = wp_remote_get(preg_replace('~^//~', 'http://', $matches[0]));
                  if (!is_wp_error($response)){
                        if (preg_match('~(text|application)/javascript~', $response['headers']['content-type'])){
                              if (!preg_match('~\.js$~', $matches[4])){
                                    $matches[4] .= '.assetproxy.js';
                              }
                              $test = true;
                        }
                        else if (strpos($response['headers']['content-type'], 'text/css') !== false){
                              if (!preg_match('~\.css$~', $matches[4])){
                                    $matches[4] .= '.assetproxy.css';
                              }
                              $test = true;
                        }
                  }
            }
            if ($test){
                  $prefix = hash('crc32',date('Y-m-d H')) . '/';
                  return preg_replace('~https?:~','',SWIFT_PERFORMANCE_CACHE_URL) . 'assetproxy/' . $prefix . $matches[2] . '.' . $matches[3] . $matches[4];
            }
            return $matches[0];
      }
}

return new Swift_Performance_Asset_Manager();

?>
