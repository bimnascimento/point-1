<?php

    /**
     * ReduxSAFrameworkInstances Functions
     *
     * @package     ReduxSA_Framework
     * @subpackage  Core
     */
    if ( ! function_exists( 'get_reduxsa_instance' ) ) {

        /**
         * Retreive an instance of ReduxSAFramework
         *
         * @param  string $opt_name the defined opt_name as passed in $args
         *
         * @return object                ReduxSAFramework
         */
        function get_reduxsa_instance( $opt_name ) {
            return ReduxSAFrameworkInstances::get_instance( $opt_name );
        }
    }

    if ( ! function_exists( 'get_all_reduxsa_instances' ) ) {

        /**
         * Retreive all instances of ReduxSAFramework
         * as an associative array.
         *
         * @return array        format ['opt_name' => $ReduxSAFramework]
         */
        function get_all_reduxsa_instances() {
            return ReduxSAFrameworkInstances::get_all_instances();
        }
    }