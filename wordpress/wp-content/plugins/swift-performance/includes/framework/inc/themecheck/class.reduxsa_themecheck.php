<?php
    /**
     * ReduxSA ThemeCheck
     *
     * @package   ReduxSAFramework
     * @author    Dovy <dovy@reduxsa.io>
     * @license   GPL-3.0+
     * @link      http://reduxsa.op
     * @copyright 2015 ReduxSAFramework
     */

    /**
     * ReduxSA-ThemeCheck class
     *
     * @package ReduxSA_ThemeCheck
     * @author  Dovy <dovy@reduxsa.io>
     */
    // Don't duplicate me!
    if ( ! class_exists( 'ReduxSA_ThemeCheck' ) ) {
        class ReduxSA_ThemeCheck {

            /**
             * Plugin version, used for cache-busting of style and script file references.
             *
             * @since   1.0.0
             * @var     string
             */
            protected $version = '1.0.0';

            /**
             * Instance of this class.
             *
             * @since    1.0.0
             * @var      object
             */
            protected static $instance = null;

            /**
             * Instance of the ReduxSA class.
             *
             * @since    1.0.0
             * @var      object
             */
            protected static $reduxsa = null;

            /**
             * Details of the embedded ReduxSA class.
             *
             * @since    1.0.0
             * @var      object
             */
            protected static $reduxsa_details = null;

            /**
             * Slug for various elements.
             *
             * @since   1.0.0
             * @var     string
             */
            protected $slug = 'reduxsa_themecheck';

            /**
             * Initialize the plugin by setting localization, filters, and administration functions.
             *
             * @since     1.0.0
             */
            private function __construct() {

                if ( ! class_exists( 'ThemeCheckMain' ) ) {
                    return;
                }

                // Load admin style sheet and JavaScript.
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
                add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

                add_action( 'themecheck_checks_loaded', array( $this, 'disable_checks' ) );
                add_action( 'themecheck_checks_loaded', array( $this, 'add_checks' ) );

            }

            /**
             * Return an instance of this class.
             *
             * @since     1.0.0
             * @return    object    A single instance of this class.
             */
            public static function get_instance() {

                // If the single instance hasn't been set, set it now.
                if ( null == self::$instance ) {
                    self::$instance = new self;
                }

                return self::$instance;
            }

            /**
             * Return an instance of this class.
             *
             * @since     1.0.0
             * @return    object    A single instance of this class.
             */
            public static function get_reduxsa_instance() {

                // If the single instance hasn't been set, set it now.
                if ( null == self::$reduxsa && ReduxSAFramework::$_as_plugin ) {
                    self::$reduxsa = new ReduxSAFramework();
                    self::$reduxsa->init();
                }

                return self::$reduxsa;
            }

            /**
             * Return the ReduxSA path info, if had.
             *
             * @since     1.0.0
             * @return    object    A single instance of this class.
             */
            public static function get_reduxsa_details( $php_files = array() ) {
                if ( self::$reduxsa_details === null ) {
                    foreach ( $php_files as $php_key => $phpfile ) {
                        if ( strpos( $phpfile, 'class' . ' ReduxSAFramework {' ) !== false ) {
                            self::$reduxsa_details               = array(
                                'filename' => strtolower( basename( $php_key ) ),
                                'path'     => $php_key,
                            );
                            self::$reduxsa_details['dir']        = str_replace( basename( $php_key ), '', $php_key );
                            self::$reduxsa_details['parent_dir'] = str_replace( basename( self::$reduxsa_details['dir'] ) . '/', '', self::$reduxsa_details['dir'] );
                        }
                    }
                }
                if ( self::$reduxsa_details === null ) {
                    self::$reduxsa_details = false;
                }

                return self::$reduxsa_details;
            }

            /**
             * Disable Theme-Check checks that aren't relevant for ThemeForest themes
             *
             * @since    1.0.0
             */
            function disable_checks() {
                global $themechecks;

                //$checks_to_disable = array(
                //	'IncludeCheck',
                //	'I18NCheck',
                //	'AdminMenu',
                //	'Bad_Checks',
                //	'MalwareCheck',
                //	'Theme_Support',
                //	'CustomCheck',
                //	'EditorStyleCheck',
                //	'IframeCheck',
                //);
                //
                //foreach ( $themechecks as $keyindex => $check ) {
                //	if ( $check instanceof themecheck ) {
                //		$check_class = get_class( $check );
                //		if ( in_array( $check_class, $checks_to_disable ) ) {
                //			unset( $themechecks[$keyindex] );
                //		}
                //	}
                //}
            }

            /**
             * Disable Theme-Check checks that aren't relevant for ThemeForest themes
             *
             * @since    1.0.0
             */
            function add_checks() {
                global $themechecks;

                // load all the checks in the checks directory
                $dir = 'checks';
                foreach ( glob( dirname( __FILE__ ) . '/' . $dir . '/*.php' ) as $file ) {
                    require_once $file;
                }
            }

            /**
             * Register and enqueue admin-specific style sheet.
             *
             * @since     1.0.1
             */
            public function enqueue_admin_styles() {
                $screen = get_current_screen();
                if ( 'appearance_page_themecheck' == $screen->id ) {
                    wp_enqueue_style( $this->slug . '-admin-styles', ReduxSAFramework::$_url . 'inc/themecheck/css/admin.css', array(), $this->version );
                }
            }

            /**
             * Register and enqueue admin-specific JavaScript.
             *
             * @since     1.0.1
             */
            public function enqueue_admin_scripts() {

                $screen = get_current_screen();

                if ( 'appearance_page_themecheck' == $screen->id ) {
                    wp_enqueue_script( $this->slug . '-admin-script', ReduxSAFramework::$_url . 'inc/themecheck/js/admin.js', array( 'jquery' ), $this->version );

                    if ( ! isset( $_POST['themename'] ) ) {

                        $intro = '';
                        $intro .= '<h2>ReduxSA Theme-Check</h2>';
                        $intro .= '<p>Extra checks for ReduxSA to ensure you\'re ready for marketplace submission to marketplaces.</p>';

                        $reduxsa_check_intro['text'] = $intro;

                        wp_localize_script( $this->slug . '-admin-script', 'reduxsa_check_intro', $reduxsa_check_intro );

                    }
                }

            }
        }

        ReduxSA_ThemeCheck::get_instance();
    }