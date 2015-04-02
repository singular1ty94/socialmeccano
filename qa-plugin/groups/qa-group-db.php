<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: /qa-group-db.php
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
	
		function getAllGroups() {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups')
			);
			return $result;
		}
		
		
		function getMyGroups($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups '.
					'INNER JOIN ^group_members ON ^groups.id = ^group_members.group_id '.
					'WHERE ^group_members.user_id = $',
					$userid
				)
			);
			return $result;
		}
	
	
		function addUserToGroup($userid, $groupid, $is_admin) {
			qa_db_query_sub(
				'INSERT INTO ^group_members (joined_at, group_id, user_id, is_admin)'.
				'VALUES (NOW(), $, $, $)',
				$groupid, $userid, $is_admin
			);
		}
			

		function createNewGroup($groupName, $groupDescription, $groupAvatar, $groupInfo, $groupTags, $userid) {		
			qa_db_query_sub(
			'INSERT INTO ^groups (created_at, group_name, group_description, avatarblobid, group_information, tags, created_by)'.
			'VALUES (NOW(), $, $, $, $, $, $)',
			$groupName, $groupDescription, $groupAvatar, $groupInfo, $groupTags, $userid
			);
			
			// Add user to the group he just created.
			$createdGroup = qa_db_last_insert_id();
			$is_admin = 1;
			addUserToGroup($userid, $createdGroup, $is_admin);
			
			return $createdGroup;
		}	
	
	
		function getGroupData($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups WHERE id=$',	$groupid)
			);
			return $result;
		}


		function updateGroupProfile($groupid, $groupName, $groupDescription, $groupInfo, $groupTags, $userid) {		
			qa_db_query_sub(
				'UPDATE ^groups SET (group_name = $, group_description = $, avatarblobid = $, group_information = $, tags = $)'.
				'WHERE id = $)',
				$groupName, $groupDescription, $groupAvatar, $groupInfo, $groupTags, $groupid
			);
		}
		
		
		function removeUserFromGroup($userid, $groupid) {
			qa_db_query_sub(
				'DELETE FROM ^group_members WHERE user_id = $ AND group_id = $)',
				$userid, $groupid
			);
			// TODO?: Check user is the only admin, if so, make oldest member an admin
			// Alternatively make him choose a new admin, or just dont worry about it (lol).
		}
		

		function makeUserGroupAdmin($userid, $groupid) {		
			qa_db_query_sub(
				'UPDATE ^group_members SET (is_admin = 1)'.
				'WHERE user_id = $ AND group_id = $)',
				$userid, $groupid
			);
		}
		
		
		function getMemberCount($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT COUNT(user_id) FROM ^group_members WHERE group_id = $',	$groupid)
			);
			return $result;
		}
		
		
		
		// DB TODO:
		// 1. Delete Group (needs a few queries (clear group, users from group, groupAvatar, announcements and discussions...))
		// 2. Add announcement
		// 3. Add discussion
		// 4. Add comments
		// 5. Get Recent Announcements
		// 6. Get Recent Discussions
		// 7. Get group members.
		// 8. Get a COUNT on group members
		// 9. 


/*
	Omit PHP closing tag to help avoid accidental output
*/