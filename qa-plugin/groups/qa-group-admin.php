<?php

/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/groups/qa-group-admin.php
	Description: Responsible for managing and displaying the groups plugin in the admin control panel.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/
	class qa_group_admin {

		function allow_template($template) {
			return ($template!='admin');
		}

		function admin_form(&$qa_content) {

			$ok = null;

			if(qa_clicked('group_save_settings')) {
				
				qa_opt('group_active', (bool)qa_post_text('group_active_check'));

				if (qa_opt('group_active')) {

					// Create groups table.
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^groups ('.
										'created_at DATETIME NOT NULL,'.
										'id INT(11) NOT NULL AUTO_INCREMENT,'.
										'group_name VARCHAR (64) NOT NULL,'.
										'group_description VARCHAR (200) DEFAULT \'\','.
										'avatarblobid bigint(20) unsigned DEFAULT NULL,'.
										'group_information VARCHAR(8000) DEFAULT \'\','.
										'tags VARCHAR(100) DEFAULT NULL,'.
										'group_location VARCHAR (50) DEFAULT \'\','.
										'group_website VARCHAR (100) DEFAULT \'\','.
										'created_by INT(11) NOT NULL,'.
										'privacy_setting enum("S", "C", "O") DEFAULT \'O\','.
										'PRIMARY KEY (id)'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);

					// Create group member table
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^group_members ('.
										'joined_at DATETIME NOT NULL,'.
										'group_id INT(11) NOT NULL,'.
										'user_id INT(11) NOT NULL,'.
										'is_admin INT(1) NOT NULL DEFAULT \'0\''.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);
								
								
					// Create group invite/request table
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^group_requests ('.
										'sent_at DATETIME NOT NULL,'.
										'group_id INT(11) NOT NULL,'.
										'user_id INT(11) NOT NULL,'.
										'type enum("R", "I") NOT NULL'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);
								
					
					// Create group post table
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^group_posts ('.
										'id INT(11) NOT NULL AUTO_INCREMENT,'.
										'posted_at DATETIME NOT NULL,'.
										'group_id INT(11) NOT NULL,'.
										'user_id INT(11) NOT NULL,'.
										'title VARCHAR (200) ,'.
										'content VARCHAR (8000) DEFAULT NULL,'.
										'tags VARCHAR(100) DEFAULT NULL,'.
										'type enum("A", "D", "C") NOT NULL,'.
										'is_locked INT(1) DEFAULT 0,'.
										'is_sticky INT(1) DEFAULT 0,'.
										'edited_at DATETIME DEFAULT NULL,'.
										'editor_id INT(11) DEFAULT NULL,'.
										'parent_id INT(11) DEFAULT NULL,'.
										'PRIMARY KEY (id)'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);							
				}
				$ok = qa_lang('groups/group_admin_saved');
			}

		//	Create the form for display.
			$fields = array();

			$fields[] = array(
				'label' => qa_lang('groups/group_admin_activate'),
				'tags' => 'NAME="group_active_check"',
				'value' => qa_opt('group_active'),
				'type' => 'checkbox',
			);

			if(qa_opt('group_active')) {

			}

			return array(
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang('groups/save_settings'),
						'tags' => 'NAME="group_save_settings"',
						),
				),
			);
		}
	}