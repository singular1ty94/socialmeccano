<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: /qa-notifications-db.php
	Description: Database connections wrapped neatly in individual functions for reuse.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}
	
	
	/*
	*	Get notifications List Functions
	*/
		function getMyNotifications($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^notifications WHERE user_id = $ ORDER BY time ASC',
					$userid
				)
			);
			return $result;
		}
		
		function getNotificationsByType($userid, $type) {
			$formattedType = '%'.$type.'%';
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^notifications WHERE type LIKE $ AND user_id = $',
					$formattedType, $userid
				)
			);
			return $result;
		}
										
		// NOTE: userid refers to the receiver of the notification, target is who or what it's about such as a user or group id.
		// Info fields are optional extras. An announcement for example might use all fields as you might want to communicate group, user, and postid.
		// We can generate URLS from the info when displaying the notifications.
		function createNotification($userid, $type, $target_id, $info1 = '', $info2 = '') {
			qa_db_query_sub(
				'INSERT INTO ^notifications (time, user_id, type, target_id, info1, info2) '.
				'VALUES (NOW(), $, $, $, $, $)',
				$userid, $type, $target_id, $info1, $info2
			);

		}

		function makeNotificationSeen($id) {
				qa_db_query_sub('UPDATE ^notifications SET seen = 1 WHERE id = #', $id);
		}
		
		function makeNotificationActioned($id) {
				qa_db_query_sub('UPDATE ^notifications SET actioned = 1 WHERE id = #', $id);
		}		
		
		function removeNotification($id) {
			qa_db_query_sub(
				'DELETE FROM ^notifications WHERE id = $',
				$id
			);
		}


        function getUser($id){
            $result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT handle, avatarblobid FROM qa_users WHERE userid = $',
				    $id
				)
			);
            return $result;
        }
		
		
		
		
/*
	Omit PHP closing tag to help avoid accidental output
*/
