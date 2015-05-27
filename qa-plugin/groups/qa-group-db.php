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
	*	Get Group List Functions
	*/
		function getAllGroups() {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups WHERE privacy_setting != "S"')
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
		
		
		function getGroupsByTag($tag) {
			$formattedTag = '%'.$tag.'%';
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups '.
					'WHERE tags LIKE $ AND privacy_setting != "S"',
					$formattedTag
				)
			);
			return $result;
		}
		
		function getGroupsByTitle($name) {
			$formattedName = '%'.$name.'%';
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups '.
					'WHERE group_name LIKE $ AND privacy_setting != "S"',
					$formattedName
				)
			);
			return $result;
		}
		
		
	
	/*
	*	Group Member Functions
	*/	

		// Removes a join group request or invite.
		function removeGroupRequest($userid, $groupid, $type) {
			qa_db_query_sub(
				'DELETE FROM ^group_requests WHERE user_id = $ AND group_id = $ AND type = $',
				$userid, $groupid, $type
			);
		}	
	
		function addUserToGroup($userid, $groupid, $is_admin) {
			qa_db_query_sub(
				'INSERT INTO ^group_members (joined_at, group_id, user_id, is_admin)'.
				'VALUES (NOW(), $, $, $)',
				$groupid, $userid, $is_admin
			);
			
			removeGroupRequest($userid, $groupid, "I");
			removeGroupRequest($userid, $groupid, "R");
            
            //Register user.
            registerChannelUserByGID($groupid, $userid);
		}
			
		function removeUserFromGroup($userid, $groupid) {
			qa_db_query_sub(
				'DELETE FROM ^group_members WHERE user_id = $ AND group_id = $',
				$userid, $groupid
			);
			
            //Remove user from associated chat channel. 
            removeUserFromChannel($userid, findChannel($groupid));
            
            // TODO?: Check user is the only admin, if so, make oldest member an admin
			// Alternatively make him choose a new admin, or just dont worry about it (lol). 
		}
		
		
		// Creates a join group request or invite.		
		function sendGroupRequest($userid, $groupid, $type) {
			qa_db_query_sub(
				'INSERT INTO ^group_requests (sent_at, group_id, user_id, type)'.
				'VALUES (NOW(), $, $, $)',
				$groupid, $userid, $type
			);
		}	



		
		// Displays my group invitations.
		function displayMyGroupInvitations($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups INNER JOIN ^group_requests ON ^groups.id = ^group_requests.group_id WHERE user_id = $ AND type = "I"',
				$userid)
			);
			return $result;
		}
	
		// Displays my group invitations.
		function displayMyGroupJoinRequests($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^groups INNER JOIN ^group_requests ON ^groups.id = ^group_requests.group_id WHERE user_id = $ AND type = "R"',
				$userid)
			);
			return $result;
		}
		
		
		// Displays group invitations.
		function displayGroupInvitations($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^users INNER JOIN ^group_requests ON ^users.userid = ^group_requests.user_id WHERE group_id = $ AND type = "I"',
				$groupid)
			);
			return $result;
		}
	
		// Displays group invitations.
		function displayGroupJoinRequests($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^users INNER JOIN ^group_requests ON ^users.userid = ^group_requests.user_id WHERE group_id = $ AND type = "R"',
				$groupid)
			);
			return $result;
		}		
			
			
		function isUserGroupAdmin($userid, $groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT is_admin FROM ^group_members '.
					'WHERE user_id = $ AND group_id = $ and is_admin = 1',
					$userid, $groupid
				), true
			);
			if (empty($result)) {
				return false;
				}
			else {
				return true;
			}
		}
		
		
		function isUserGroupMember($userid, $groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT user_id FROM ^group_members '.
					'WHERE user_id = $ AND group_id = $',
					$userid, $groupid
				), true
			);
			if (empty($result)) {
				return false;
				}
			else {
				return true;
			}
		}
		
		
		function makeUserGroupAdmin($userid, $groupid) {		
			qa_db_query_sub(
				'UPDATE ^group_members SET (is_admin = 1)'.
				'WHERE user_id = $ AND group_id = $)',
				$userid, $groupid
			);
		}	
		
		
		function isUserInvitedOrRequested($userid, $groupid, $type) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT user_id FROM ^group_requests '.
					'WHERE user_id = $ AND group_id = $ AND type = $',
					$userid, $groupid, $type
				), true
			);
			if (empty($result)) {
				return false;
				}
			else {
				return true;
			}
		}

		
			
			
	/*
	*	Group Creation Functions
	*/				
		function createNewGroup($groupName, $groupDescription, $groupAvatar, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $userid, $privacy_setting) {		
			qa_db_query_sub(
				'INSERT INTO ^groups '.
				'(created_at, group_name, group_description, avatarblobid, group_location, group_website, group_information, tags, created_by, privacy_setting)'.
				'VALUES (NOW(), $, $, #, $, $, $, $, $, $)',
				$groupName, $groupDescription, $groupAvatar, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $userid, $privacy_setting
			);
			
			// Add user to the group he just created.
			$createdGroup = qa_db_last_insert_id();
			$is_admin = 1;
            
            registerChatChannel($groupName);
			addUserToGroup($userid, $createdGroup, $is_admin);
			return $createdGroup;
		}	

    /*
    ** Register a chat channel.
    */
    function registerChatChannel($groupName){
        qa_db_query_sub(
            'INSERT INTO ajax_chat_channels'.
            '(channelName)'.
            'VALUES ($)',
            (preg_replace('/\s+/', '', $groupName)));
    }

    /*
    ** Register a user for a channel.
    */
    function registerChannelUser($groupName, $userid){
        $cID = qa_db_read_one_assoc(
            qa_db_query_sub('SELECT channelID FROM ajax_chat_channels WHERE channelName=$', (preg_replace('/\s+/', '', $groupName)))
        );
        
        qa_db_query_sub(
            'INSERT INTO ajax_chat_users'.
            '(userID, handle, channelID)'.
            'VALUES ($, $, $)',
            $userid, qa_get_logged_in_user_field('handle'), (preg_replace('/\s+/', '', $cID)));
    }

    /*
    ** Remove a user from a channel.
    */
    function removeUserFromChannel($userid, $channelID){
        qa_db_query_sub(
            'DELETE FROM ajax_chat_users WHERE userID=$ AND channelID=$',
            $userid, $channelID);
    }

    /*
    ** Helper functions.
    */
    function findChannel($groupID){
        $groupName = qa_db_read_one_assoc(
            qa_db_query_sub('SELECT group_name FROM qa_groups WHERE id=$', $groupID)
        );
        
        //Sanitize group name.
        $groupName = (preg_replace('/\s+/', '', $groupName));
        return qa_db_read_one_assoc(
            qa_db_query_sub('SELECT channelID FROM ajax_chat_channels WHERE channelName=$', $groupName)
        );
    }

    /*
    ** Register a user for a channel.
    */
    function registerChannelUserByGID($groupid, $userid){
        $gName = qa_db_read_one_assoc(
            qa_db_query_sub('SELECT group_name FROM ^groups WHERE id=$', $groupid)
        );
        
        
        $cID = qa_db_read_one_assoc(
            qa_db_query_sub('SELECT channelID FROM ajax_chat_channels WHERE channelName=$', (preg_replace('/\s+/', '', $gName)))
        );
        
        qa_db_query_sub(
            'INSERT INTO ajax_chat_users'.
            '(userID, handle, channelID)'.
            'VALUES ($, $, $)',
            $userid, qa_get_logged_in_user_field('handle'), (preg_replace('/\s+/', '', $cID)));
    }
	

	/*
	*	Display Group Functions
	*/		
		function getGroupData($groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT * FROM ^groups WHERE id=$',	$groupid), true
			);
			return $result;
		}

		function getGroupName($groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT group_name FROM ^groups WHERE id=$', $groupid), true
			);
			return $result;
		}
		
		function getGroupType($groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT privacy_setting FROM ^groups WHERE id=$',	$groupid), true
			);
			return $result;
		}	
		
		function getMemberCount($groupid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT COUNT(user_id) FROM ^group_members WHERE group_id = $',	$groupid), true
			);
			return $result;
		}		

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
	

	
	/*
	*	Group Management Functions
	*/	
		
		function updateGroupProfile($groupid, $groupName, $groupDescription, $groupAvatar, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $privacy_setting) {		
			qa_db_query_sub(
				'UPDATE ^groups SET group_name = $, group_description = $, avatarblobid = #, group_location = $, group_website = $, group_information = $, tags = $, privacy_setting = $ '.
				'WHERE id = $',
				$groupName, $groupDescription, $groupAvatar, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $privacy_setting, $groupid
			);		
		}
		
		function updateGroupProfileNoBlob($groupid, $groupName, $groupDescription, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $privacy_setting) {		
			qa_db_query_sub(
				'UPDATE ^groups SET group_name = $, group_description = $, group_location = $, group_website = $, group_information = $, tags = $, privacy_setting = $ '.
				'WHERE id = $',
				$groupName, $groupDescription, $groupLocation, $groupWebsite, $groupInfo, $groupTags, $privacy_setting,  $groupid
			);		
		}
		
		
		
		function deleteGroup($groupid) {
            //Delete the users from the channel.
            qa_db_query_sub('DELETE FROM ajax_chat_users WHERE channelID = $', findChannel($groupid));
            
            //Delete the chat channel.
            qa_db_query_sub('DELETE FROM ajax_chat_channels WHERE channelID = $', findChannel($groupid));
                    
			// Delete all users from group		
			qa_db_query_sub('DELETE FROM ^group_members WHERE group_id = $', $groupid);
			
			// Delete group posts (announcements, discussions, comments)
			qa_db_query_sub('DELETE FROM ^group_posts WHERE group_id = $', $groupid);
			
			// Get the blobid so we can delete it
			$blobid = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT avatarblobid FROM ^groups WHERE id = $', $groupid), true
			);
			
			// Delete avatar from blobs
			qa_db_query_sub('DELETE FROM ^blobs WHERE blobid=#', $blobid);
			
			// Delete the group itself
			qa_db_query_sub('DELETE FROM ^groups WHERE id = $', $groupid);
		}
		


	/*
	*	Group Post and Comment Functions
	*/	
		
		//type is enum('A', 'D', 'C') - A=announcement, D=Discussion, C=Comment
		function createPost($groupid, $userid, $title, $content, $tags, $type, $parentid=0) {
			qa_db_query_sub('INSERT INTO ^group_posts (posted_at, group_id, user_id, title, content, tags, type, parent_id, is_sticky, is_locked) '.
			'VALUES (NOW(), $, $, $, $, $, $, $, 0, 0)',
			$groupid, $userid, $title, $content, $tags, $type, $parentid);
			$createdPost = qa_db_last_insert_id();
			return $createdPost;
		}
		
		
		function editPost($postid, $editorid, $title, $content, $tags) {
		qa_db_query_sub('UPDATE ^group_posts SET (editor_id = $, edited_at = $, title = $, content = $, tags = $) WHERE id = $)'.
			'VALUES ($, NOW(), $, $, $, $)',
		$editorid, $title, $content, $tags, $postid);
		}		

		function deletePost($postid) {
			qa_db_query_sub('DELETE FROM ^group_posts WHERE id = $ OR parent_id = $', $postid, $postid);
		}
		
		function getPost($postid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT id, group_id, user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, '.
								'title, content, tags, is_sticky, is_locked from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE id = #', $postid), true
			);
			return $result;	
		}
		

		function getAllAnnouncements($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT id, user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, '.
								'title, content, tags, is_sticky, is_locked from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE type = "A" AND group_id = # ORDER BY is_sticky DESC, posted_at DESC', $groupid)
			);
			return $result;
		}		
		
		
		function getAllDiscussions($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT id, user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, '.
								'title, content, tags, is_sticky, is_locked from ^group_posts '.
								'INNER JOIN ^users ON qa_group_posts.user_id = ^users.userid '.
								'WHERE type = "D" AND group_id = # ORDER BY is_sticky, posted_at DESC', $groupid)
			);
			return $result;
		}
		
		
		function getRecentAnnouncements($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT id, user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, title, content, tags, is_sticky, is_locked from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE type = "A" AND group_id = # ORDER BY posted_at DESC LIMIT 2', $groupid)
			);
			return $result;
		}
		
		
		function getRecentDiscussions($groupid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT id, user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, title, content, tags, is_sticky, is_locked from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE type = "D" AND group_id = # ORDER BY posted_at DESC LIMIT 2', $groupid)
			);
			return $result;
		}
		
		
		function getComments($postid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT user_id, handle, avatarblobid, UNIX_TIMESTAMP(posted_at) AS posted_at, title, content, tags from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE type = "C" AND parent_id = # ORDER BY posted_at ASC', $postid)
			);
			return $result;
		}
		
		function getCommentCount($postid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT COUNT(id) from ^group_posts '.
								'INNER JOIN ^users ON ^group_posts.user_id = ^users.userid '.
								'WHERE type = "C" AND parent_id = #', $postid), true
			);
			return $result;
		}
		
		
		function makePostSticky($postid) {
				qa_db_query_sub('UPDATE ^group_posts SET is_sticky = 1 WHERE id = #', $postid);
		}
		
		function makePostNotSticky($postid) {
				qa_db_query_sub('UPDATE ^group_posts SET is_sticky = 0 WHERE id = #', $postid);
		}
		
		function makePostLocked($postid) {
				qa_db_query_sub('UPDATE ^group_posts SET is_locked = 1 WHERE id = #', $postid);
		}
		
		function makePostUnlocked($postid) {
				qa_db_query_sub('UPDATE ^group_posts SET is_locked = 0 WHERE id = #', $postid);
		}
		
		
		
		
		
/*
	Omit PHP closing tag to help avoid accidental output
*/