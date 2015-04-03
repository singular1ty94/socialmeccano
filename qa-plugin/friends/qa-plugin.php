<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/friends/qa-plugin.php
	Description: Initiates friends plugin


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/

/*
	Plugin Name: Friends
	Plugin URI:
	Plugin Description: Allows users to add and remove friends.
	Plugin Version: 0.1
	Plugin Date: 2015-04-03
	Plugin Author: Samuel Hammill
	Plugin Author URI:
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.5
	Plugin Minimum PHP Version: 5
	Plugin Update Check URI:
*/


	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../../');
		exit;
	}

	// Register a plugin module
	qa_register_plugin_module('module', 'qa-friends-admin.php', 'qa_friends_admin', 'Friends Admin');
	qa_register_plugin_module('page', 'qa-friends-page.php', 'qa_friends_page', 'Friends Page');
	//qa_register_plugin_layer('qa-friends-layer.php', 'friends layer');	// Will need to do this to make a 'add friend button' on user profile
	qa_register_plugin_phrases('qa-friends-lang-default.php', 'friends');
	
		