<?php

    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    if (!class_exists('reduxsaDashboardWidget')) {
        class reduxsaDashboardWidget {

            public function __construct ($parent) {
                $fname = ReduxSA_Functions::dat( 'add_reduxsa_dashboard', $parent->args['opt_name'] );

                add_action('wp_dashboard_setup', array($this, $fname));
            }

            public function add_reduxsa_dashboard() {
                add_meta_box('reduxsa_dashboard_widget', 'ReduxSA Framework News', array($this,'reduxsa_dashboard_widget'), 'dashboard', 'side', 'high');
            }

            public function dat() {
                return;
            }

            public function reduxsa_dashboard_widget() {
                echo '<div class="rss-widget">';
                wp_widget_rss_output(array(
                     'url'          => 'http://reduxsaframework.com/feed/',
                     'title'        => 'REDUX_NEWS',
                     'items'        => 3,
                     'show_summary' => 1,
                     'show_author'  => 0,
                     'show_date'    => 1
                ));
                echo '</div>';
            }
        }
    }
