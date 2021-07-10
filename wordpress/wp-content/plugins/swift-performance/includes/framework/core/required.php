<?php

	if ( !defined ( 'ABSPATH' ) ) {
		exit;
	}

	if (!class_exists('reduxsaCoreRequired')){
		class reduxsaCoreRequired {
			public $parent      = null;

			public function __construct ($parent) {
				$this->parent = $parent;
				ReduxSA_Functions::$_parent = $parent;


				/**
				 * action 'reduxsa/page/{opt_name}/'
				 */
				do_action( "reduxsa/page/{$parent->args['opt_name']}/" );

			}


		}
	}