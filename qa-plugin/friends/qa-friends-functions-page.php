<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/friends/qa-friends-page.php
	Description: Displays a list of friends.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/

	class qa_friend_functions_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			$requestpart = qa_request_part(0);
			if ($requestpart=='friend-functions')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();
			
			include 'qa-friends-db.php';
            include 'qa-plugin/notifications/qa-notifications-db.php';
			
			$userid = qa_get_logged_in_userid();
			$requestType = qa_request_part(1);	
			$target = qa_request_part(2);
			$redirect = qa_request_part(3);
			$redirectTarget = qa_request_part(4);

            //If the user is not logged in redirect to main.		
            if(!isset($target)){
               header('Location: ../');
            }			
			
			//If the user wants to remove a friend request.
            if($requestType == "addFriend"){
				createFriendRequest($userid, $target);
                //Notify the target user.
                createNotification($target, 'FriendOffer', $userid);
            }
			
			//If the user wants to remove a friend.
            if($requestType == "removeFriend"){
				removeFriendFromList($userid, $target);
            }

			//If the user wants to remove a outgoing friend request.
            if($requestType == "removeRequest"){
				removeFriendRequest($userid, $target);
            }
			
			//If the user wants to remove a friend request.
            if($requestType == "removeRequestI"){
				removeFriendRequest($target, $userid);
            }			
			
			//If the user wants to approve a friend.
            if($requestType == "approveRequest"){
				approveFriendRequest($userid, $target);
                //Notify the target user.
                createNotification($target, 'FriendAccept', $userid);
            }
			

			if 	($redirect == 'user') {
				header('Location: /?qa=user/'.$redirectTarget);
			}
			else if ($redirect == 'requests'){
				header('Location: /?qa=friend-requests');
			}
			else if ($redirect == 'myFriends'){
				header('Location: /?qa=friends');
			}			

		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
