<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/groups/qa-plugin.php
	Description: Initiates group plugin


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
	Plugin Name: Groups
	Plugin URI:
	Plugin Description: Allows users to create and join groups.
	Plugin Version: 0.1
	Plugin Date: 2015-03-30
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
	qa_register_plugin_module('module', 'qa-group-admin.php', 'qa_group_admin', 'Group Admin');
	qa_register_plugin_module('page', 'qa-group-page.php', 'qa_group_page', 'Group Page');
	qa_register_plugin_module('page', 'qa-group-create-page.php', 'qa_group_create_page', 'Create Group Page');
	qa_register_plugin_module('page', 'qa-group-update-page.php', 'qa_group_update_page', 'Update Group Page');
	qa_register_plugin_module('page', 'qa-group-create-post.php', 'qa_group_create_post', 'Create Post Page');
	qa_register_plugin_module('page', 'qa-group-view-post.php', 'qa_group_view_post', 'View Post Page');
	qa_register_plugin_module('page', 'qa-group-edit-post.php', 'qa_group_edit_post', 'Edit Post Page');	
	qa_register_plugin_module('page', 'qa-grouplist-page.php', 'qa_grouplist_page', 'Group List');
	//qa_register_plugin_layer('qa-group-layer.php', 'Group Notification Layer');	
	qa_register_plugin_phrases('qa-group-lang-default.php', 'groups');
	
		