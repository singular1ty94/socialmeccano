<?php

/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/friends/qa-friends-admin.php
	Description: Responsible for managing and displaying the friends plugin in the admin control panel.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/
	class qa_friends_admin {

		function allow_template($template) {
			return ($template!='admin');
		}

		function admin_form(&$qa_content) {

			$ok = null;

			if(qa_clicked('friends_save_settings')) {
				
				qa_opt('friends_active', (bool)qa_post_text('friends_active_check'));

				if (qa_opt('friends_active')) {

					// Create friends and friend request tables.
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^friend_requests ('.
										'requsted_at DATETIME NOT NULL,'.
										'requester_id INT(11) NOT NULL,'.
										'receiver_id INT(11) NOT NULL'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);

					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^friend_list ('.
										'added_at DATETIME NOT NULL,'.
										'user_id INT(11) NOT NULL,'.
										'friend_id INT(11) NOT NULL'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);					
				}
				$ok = qa_lang('friends/friends_admin_saved');
			}

		//	Create the form for display.
			$fields = array();

			$fields[] = array(
				'label' => qa_lang('friends/friends_admin_activate'),
				'tags' => 'NAME="friends_active_check"',
				'value' => qa_opt('friends_active'),
				'type' => 'checkbox',
			);

			if(qa_opt('friends_active')) {

			}

			return array(
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang('friends/save_settings'),
						'tags' => 'NAME="friends_save_settings"',
						),
				),
			);
		}
	}
