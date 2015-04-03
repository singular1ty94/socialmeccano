<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: /qa-friends-db.php
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
	
getMyFriends($userid)
removeFriendFromList($userid, $friendid)
removeFriendRequest($requesterid, $requesteeid)
createFriendRequest($requesterid, $requesteeid)
approveFriendRequest($requesterid, $requesteeid)
checkForExistingFriendship($userid, $friendid)
checkForExistingRequest($userid, $friendid)

*/	




		function getMyFriends($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT userid, handle, avatarblobid FROM ^users '.
					'INNER JOIN ^friend_list ON ^friend_list.friend_id = ^users.userid '.
					'WHERE user_id = $',
					$userid
				)
			);
			return $result;
		}

		
		function addFriendToList($userid, $friendid) {
			$currentTime = NOW();
		
			// Add you to my list.
			qa_db_query_sub('INSERT INTO ^friend_list (added_at, user_id, friend_id)'.
				'VALUES ($, $, $)',
				$currentTime, $userid, $friendid
			);
			
			// Add me to your list.
			qa_db_query_sub('INSERT INTO ^friend_list (added_at, user_id, friend_id)'.
				'VALUES ($, $, $)',
				$currentTime, $friendid, $userid 
			);		

		}
		
		function removeFriendFromList($userid, $friendid) {
			// Remove you from my list.
			qa_db_query_sub('DELETE FROM ^friend_list WHERE user_id = $ AND friend_id = $',
				$userid, $friendid
			);
			
			// Remove me from your list.
			qa_db_query_sub('DELETE FROM ^friend_list WHERE user_id = $ AND friend_id = $',
				$friendid, $userid 
			);			
			
		}
		

		// Used for both approval AND deny of request.
		function removeFriendRequest($requesterid, $requesteeid) {
			qa_db_query_sub('DELETE FROM ^friend_requests WHERE requester_id = $ AND friend_id = $',
				$requesterid, $requesteeid
			);
		}
		
		function createFriendRequest($requesterid, $requesteeid) {
			// Send someone a friend request
			qa_db_query_sub('INSERT INTO ^friend_requests (requsted_at, requester_id, requestee_id)'.
				'VALUES (NOW(), $, $)',
				$requesterid, $requesteeid
			);
		}
		
		
		function approveFriendRequest($requesterid, $requesteeid) {
			removeFriendRequest ($requesterid, $requesteeid);
			removeFriendRequest ($requesteeid, $requesterid);
			addFriendToList ($requesterid, $requesteeid);	
		}
		
		function checkForExistingFriendship($userid, $friendid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT userid FROM ^friend_list '.
					'WHERE user_id = $ AND friend_id = $' ,
					$userid, $friendid
				)
			);
			if (empty($result)) {
				return false;
			}
			else {
				return true;
			}
		}
		
		function checkForExistingRequest($userid, $friendid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT userid FROM ^friend_requests '.
					'WHERE requester_id = $ AND requestee_id = $' ,
					$userid, $friendid
				)
			);
			if (empty($result)) {
				return false;
			}
			else {
				return true;
			}			
		}


/*
	Omit PHP closing tag to help avoid accidental output
*/