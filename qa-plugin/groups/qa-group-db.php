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
	
/*
	Available Functions for brevity...
	getAllGroups()
	getMyGroups($userid)
	addUserToGroup($userid, $groupid, $is_admin)
	getGroupData($groupid)
	createNewGroup($groupName, $groupDescription, $groupAvatar, $groupInfo, $groupTags, $userid)
	updateGroupProfile($groupid, $groupName, $groupDescription, $groupInfo, $groupTags, $userid)
	removeUserFromGroup($userid, $groupid)
	makeUserGroupAdmin($userid, $groupid)
	getMemberCount($groupid)
	getGroupAvatar($blobid
	getGroupAdmins($groupid)
	getGroupMembers($groupid)
	deleteGroup($groupid)
	createPost($groupid, $userid, $title, $content, $tags, $type)
	createPost($groupid, $userid, $title, $content, $tags, $type, $parentid)
	getAllAnnoucements($groupid)
	getAllDiscussions($groupid)
	getRecentAnnoucements($groupid)
	getRecentDiscussions($groupid)
	getComments($postid)
*/	

	
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
		
		
		// double check this
		function getGroupAvatar($blobid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT content FROM ^blobs WHERE blobid = $',	$blobid)
			);
			return $result;
		}		
		
		
		function getGroupAdmins($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT userid, handle, avatarblobid FROM ^users '.
				'INNER JOIN ^group_members ON ^users.userid = ^group_members.user_id WHERE group_id = $ AND is_admin = 1', $groupid)
			);
			return $result;
		}
		
		function getGroupMembers($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT userid, handle, avatarblobid FROM ^users '.
				'INNER JOIN ^group_members ON ^users.userid = ^group_members.user_id WHERE group_id = $ AND is_admin = 0', $groupid)
			);
			return $result;
		}
		
		function deleteGroup($groupid) {
			// Delete all users from group		
			qa_db_query_sub('DELETE FROM ^group_members WHERE group_id = $)', $groupid);
			
			// Delete group posts (announcements, discussions, comments)
			qa_db_query_sub('DELETE FROM ^group_posts WHERE group_id = $)', $groupid);
			
			// Get the blobid so we can delete it
			$blobid = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT avatarblobid FROM ^groups WHERE id = $', $groupid)
			);
			
			// Delete avatar from blobs
			qa_db_query_sub('DELETE FROM ^blobs WHERE $blobid=#', $blobid);
			
			// Delete the group itself
			qa_db_query_sub('DELETE FROM ^groups WHERE group_id = $)', $groupid);
		}
		
		
		//type is enum('A', 'D', 'C') - A=Annoucement, D=Discussion, C=Comment
		function createPost($groupid, $userid, $title, $content, $tags, $type, $parentid) {
			qa_db_query_sub('INSERT INTO ^group_posts (posted_at, group_id, user_id, title, content, tags, type)'.
			'VALUES (NOW(), $, $, $, $, $, $, $)',
			$groupid, $userid, $title, $content, $tags, $type, $parentid);
		}
		
		
		function getAllAnnoucements($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, posted_at, title, content, tags from qa_group_posts '.
								'INNER JOIN qa_users ON qa_group_posts.user_id = qa_users.userid '.
								'WHERE type = "A" AND group_id = # ORDER BY posted_at DESC', $groupid)
			);
			return $result;
		}		
		
		
		function getAllDiscussions($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, posted_at, title, content, tags from qa_group_posts '.
								'INNER JOIN qa_users ON qa_group_posts.user_id = qa_users.userid '.
								'WHERE type = "D" AND group_id = # ORDER BY posted_at DESC', $groupid)
			);
			return $result;
		}
		
		
		function getRecentAnnoucements($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, posted_at, title, content, tags from qa_group_posts '.
								'INNER JOIN qa_users ON qa_group_posts.user_id = qa_users.userid '.
								'WHERE type = "A" AND group_id = # ORDER BY posted_at DESC LIMIT 2', $groupid)
			);
			return $result;
		}
		
		
		function getRecentDiscussions($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, posted_at, title, content, tags from qa_group_posts '.
								'INNER JOIN qa_users ON qa_group_posts.user_id = qa_users.userid '.
								'WHERE type = "D" AND group_id = # ORDER BY posted_at DESC LIMIT 2', $groupid)
			);
			return $result;
		}
		
		
		function getComments($postid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, posted_at, title, content, tags from qa_group_posts '.
								'INNER JOIN qa_users ON qa_group_posts.user_id = qa_users.userid '.
								'WHERE type = "C" AND parent_id = # ORDER BY posted_at ASC', $postid)
			);
			return $result;
		}


/*
	Omit PHP closing tag to help avoid accidental output
*/