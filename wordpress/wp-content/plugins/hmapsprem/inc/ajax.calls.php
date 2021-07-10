<?php

	/*
		notes:
		------
		- All actions are prefixed by the plugin prefix
			e.g. if the plugin prefix is "hplugin_" and the action name is "get_data", the actions, as referenced by the ajax call, will be "hplugin_get_data"
		- Ensure that all "actions" are unique
		- User registrations are registered for administrators as well to ensure that functionslity remains the same if logged in
	*/

	#ADMIN AJAX CALLS
	$backend_ajax_calls = array( //all methods must be contained by the backend class
		array('action' => 'insert_object','method' => 'insert_object'),
		array('action' => 'get_object_list','method' => 'get_object_list'),
		array('action' => 'get_object','method' => 'get_object'),
		array('action' => 'rename_object','method' => 'rename_object'),
		array('action' => 'update_object','method' => 'update_object'),
		array('action' => 'delete_object','method' => 'delete_object'),
		array('action' => 'duplicate_object','method' => 'duplicate_object'),
		array('action' => 'get_custom_markers', 'method' => 'get_custom_markers'), //get custom markers
		array('action' => 'update_custom_marker_offset', 'method' => 'update_custom_marker_offset'), //update custom marker offset(s)
		array('action' => 'remove_custom_marker', 'method' => 'remove_custom_marker'), //remove custom marker
		array('action' => 'get_markers', 'method' => 'get_markers'), //get markers
		array('action' => 'get_object_export','method' => 'get_object_export'), //get object export
		array('action' => 'delete_marker_pack','method' => 'delete_marker_pack') //delete marker pack
	);
	
	#USER AJAX CALLS
	$frontend_ajax_calls = array( //all methods must be contained by the frontend class
	);