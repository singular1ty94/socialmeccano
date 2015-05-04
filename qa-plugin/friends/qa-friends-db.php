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
		
			// Add you to my list.
			qa_db_query_sub('INSERT INTO ^friend_list (added_at, user_id, friend_id)'.
				'VALUES (NOW(), $, $)',
				$userid, $friendid
			);
			
			// Add me to your list.
			qa_db_query_sub('INSERT INTO ^friend_list (added_at, user_id, friend_id)'.
				'VALUES (NOW(), $, $)',
				$friendid, $userid 
			);

            //Make a chat channel.
            establishChatChannel($userid, $friendid);
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

            //Destroy our channel.
            destroyChannel(findChanelByID($userid, $friendid));
		}

		// Used for both approval AND deny of request.
		function removeFriendRequest($requesterid, $requesteeid) {
			qa_db_query_sub('DELETE FROM ^friend_requests WHERE requester_id = $ AND receiver_id = $',
				$requesterid, $requesteeid
			);
		}
		
		function createFriendRequest($requesterid, $requesteeid) {
			// Send someone a friend request
			qa_db_query_sub('INSERT INTO ^friend_requests (requsted_at, requester_id, receiver_id)'.
				'VALUES (NOW(), $, $)',
				$requesterid, $requesteeid
			);
		}
		
		
		// Displays incoming friend requests.
		function displayIncomingFriendRequests($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^users INNER JOIN ^friend_requests ON ^users.userid = ^friend_requests.requester_id WHERE receiver_id = $',
				$userid)
			);
			return $result;
		}
		
		// Displays outgoing friend requests.
		function displayOutgoingFriendRequests($userid) {
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^users INNER JOIN ^friend_requests ON ^users.userid = ^friend_requests.receiver_id WHERE requester_id = $',
				$userid)
			);
			return $result;
		}		
		
		function approveFriendRequest($requesterid, $requesteeid) {
			removeFriendRequest ($requesterid, $requesteeid);
			removeFriendRequest ($requesteeid, $requesterid);
			addFriendToList ($requesterid, $requesteeid);	
		}
		
		function checkForExistingFriendship($userid, $friendid) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT user_id FROM ^friend_list '.
					'WHERE user_id = $ AND friend_id = $' ,
					$userid, $friendid
				), true
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
				qa_db_query_sub('SELECT requester_id FROM ^friend_requests '.
					'WHERE requester_id = $ AND receiver_id = $' ,
					$userid, $friendid
				), true
			);
			if (empty($result)) {
				return false;
			}
			else {
				return true;
			}			
		}

		function getUseridFromHandle($handle) {
			$result = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT userid FROM ^users WHERE handle = $' ,
					$handle
				), true
			);
			return $result;
		}

			
		function getUsersByHandle($name) {
			$formattedName = '%'.$name.'%';
			$result = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ^users '.
					'WHERE handle LIKE $',
					$formattedName
				)
			);
			return $result;
		}

        function establishChatChannel($userID, $friendID){
            $username = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT handle FROM ^users WHERE userid = $' , $userID)
            );

            $friendname = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT handle FROM ^users WHERE userid = $' , $friendID)
            );


            $arr = array("username" => $username["handle"],
                    "friendname" => $friendname["handle"]
                   );
            sort($arr);

            $channel = $arr[0] . '' . $arr[1];

            qa_db_query_sub(
            'INSERT INTO ajax_chat_channels'.
            '(channelName)'.
            'VALUES ($)',
            $channel);

            registerChannelUser($userID, $channel, $username);
            registerChannelUser($friendID, $channel, $friendname);
        }

        /*
        ** Register a user for a channel.
        */
        function registerChannelUser($userID, $channelID, $handle){
            $cID = qa_db_read_one_assoc(
                qa_db_query_sub('SELECT channelID FROM ajax_chat_channels WHERE channelName=$', $channelID)
            );

            qa_db_query_sub(
                'INSERT INTO ajax_chat_users'.
                '(userID, handle, channelID)'.
                'VALUES ($, $, $)',
                $userID, $handle, $cID);
        }

        function destroyChannel($channelID){
            //Delete the users from the channel.
            qa_db_query_sub('DELETE FROM ajax_chat_users WHERE channelID = $', $channelID);

            //Delete the chat channel.
            qa_db_query_sub('DELETE FROM ajax_chat_channels WHERE channelID = $', $channelID);
        }

        //Helper feature.
        function findChanelByID($userID, $friendID){
            $username = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT handle FROM ^users WHERE userid = $' , $userID)
            );

            $friendname = qa_db_read_one_assoc(
				qa_db_query_sub('SELECT handle FROM ^users WHERE userid = $' , $friendID)
            );


            $arr = array("username" => $username["handle"],
                    "friendname" => $friendname["handle"]
                   );
            sort($arr);

            $channel = $arr[0] . '' . $arr[1];

            return qa_db_read_one_assoc(
                qa_db_query_sub(
                    'SELECT channelID FROM ajax_chat_channels WHERE channelName = $', $channel)
            );
        }




/*
	Omit PHP closing tag to help avoid accidental output
*/
