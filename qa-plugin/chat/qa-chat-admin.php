<?php

/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/chats/qa-chat-admin.php
	Description: Responsible for managing and displaying the chats plugin in the admin control panel.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/
	class qa_chat_admin {

		function allow_template($template) {
			return ($template!='admin');
		}

		function admin_form(&$qa_content) {

			$ok = null;

			if(qa_clicked('chat_save_settings')) {
				
				qa_opt('chat_active', (bool)qa_post_text('chat_active_check'));

				if (qa_opt('chat_active')) {
			
				}
				$ok = qa_lang('chat/chat_admin_saved');
			}

		//	Create the form for display.
			$fields = array();

			$fields[] = array(
				'label' => qa_lang('chat/chat_admin_activate'),
				'tags' => 'NAME="chat_active_check"',
				'value' => qa_opt('chat_active'),
				'type' => 'checkbox',
			);

			if(qa_opt('chat_active')) {

			}

			return array(
				'ok' => ($ok && !isset($error)) ? $ok : null,

				'fields' => $fields,

				'buttons' => array(
					array(
						'label' => qa_lang('chat/save_settings'),
						'tags' => 'NAME="chat_save_settings"',
						),
				),
			);
		}
	}
