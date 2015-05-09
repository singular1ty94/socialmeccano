<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/notifications/qa-plugin.php
	Description:  Responsible for managing and displaying the notifications plugin in the admin control panel.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

	class qa_notifications_admin {

		function allow_template($template) {
			return ($template!='admin');
		}

		function admin_form(&$qa_content) {
			$ok = null;

			if(qa_clicked('notifications_save_settings')) {
				
				qa_opt('notifications_active', (bool)qa_post_text('notifications_active_check'));

				if (qa_opt('notifications_active')) {

					// Create notifications table.
					qa_db_query_sub(
									'CREATE TABLE IF NOT EXISTS ^notifications ('.
										'id INT(11) NOT NULL AUTO_INCREMENT,'.
										'time DATETIME NOT NULL,'.
										'user_id INT(11) NOT NULL,'.
										'type VARCHAR (50) DEFAULT \'\','.
										'target_id INT(11) NOT NULL,'.
										'info1 VARCHAR(100) DEFAULT \'\','.
										'info2 VARCHAR(100) DEFAULT \'\','.
										'seen INT(1) DEFAULT 0,'.										
										'actioned INT(1) DEFAULT 0,'.
										'PRIMARY KEY (id)'.
									') ENGINE=MyISAM DEFAULT CHARSET=utf8'
								);
				}
				$ok = qa_lang('notifications/notifications_admin_saved');
			}

		//	Create the form for display.
			$fields = array();

			$fields[] = array(
				'label' => qa_lang('notifications/notifications_admin_activate'),
				'tags' => 'NAME="notifications_active_check"',
				'value' => qa_opt('notifications_active'),
				'type' => 'checkbox',
			);

			if(qa_opt('notifications_active')) {

			}

			return array(
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang('notifications/save_settings'),
						'tags' => 'NAME="notifications_save_settings"',
						),
				),
			);
		}
	}
