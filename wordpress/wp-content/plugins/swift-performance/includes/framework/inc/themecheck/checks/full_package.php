<?php

    class ReduxSA_Full_Package implements themecheck {
        protected $error = array();

        function check( $php_files, $css_files, $other_files ) {

            $ret = true;

            $check = ReduxSA_ThemeCheck::get_instance();
            $reduxsa = $check::get_reduxsa_details( $php_files );

            if ( $reduxsa ) {

                $blacklist = array(
                    '.tx'                    => __( 'ReduxSA localization utilities', 'themecheck' ),
                    'bin'                    => __( 'ReduxSA Resting Diles', 'themecheck' ),
                    'codestyles'             => __( 'ReduxSA Code Styles', 'themecheck' ),
                    'tests'                  => __( 'ReduxSA Unit Testing', 'themecheck' ),
                    'class.reduxsa-plugin.php' => __( 'ReduxSA Plugin File', 'themecheck' ),
                    'bootstrap_tests.php'    => __( 'ReduxSA Boostrap Tests', 'themecheck' ),
                    '.travis.yml'            => __( 'CI Testing FIle', 'themecheck' ),
                    'phpunit.xml'            => __( 'PHP Unit Testing', 'themecheck' ),
                );

                $errors = array();

                foreach ( $blacklist as $file => $reason ) {
                    checkcount();
                    if ( file_exists( $reduxsa['parent_dir'] . $file ) ) {
                        $errors[ $reduxsa['parent_dir'] . $file ] = $reason;
                    }
                }

                if ( ! empty( $errors ) ) {
                    $error = '<span class="tc-lead tc-required">REQUIRED</span> ' . __( 'It appears that you have embedded the full ReduxSA package inside your theme. You need only embed the <strong>ReduxSACore</strong> folder. Embedding anything else will get your rejected from theme submission. Suspected ReduxSA package file(s):', 'reduxsa-framework' );
                    $error .= '<ol>';
                    foreach ( $errors as $key => $e ) {
                        $error .= '<li><strong>' . $e . '</strong>: ' . $key . '</li>';
                    }
                    $error .= '</ol>';
                    $this->error[] = '<div class="reduxsa-error">' . $error . '</div>';
                    $ret           = false;
                }
            }

            return $ret;
        }

        function getError() {
            return $this->error;
        }
    }

    $themechecks[] = new ReduxSA_Full_Package();