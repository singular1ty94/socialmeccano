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

	class qa_friend_requests_page {
		
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
			if ($requestpart=='friend-requests')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']="Friend Requests";
			
			include 'qa-friends-db.php';
			include 'qa-friends-helper.php';
			
			$userid = qa_get_logged_in_userid();

            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
               header('Location: ../');
            }			

			
			// Get my friends from DB.
			$incomingRequestList = displayIncomingFriendRequests($userid);
			$outgoingRequestList = displayOutgoingFriendRequests($userid);	

            $heads = getJQueryUITabs('tabs');

			$qa_content['custom']= $heads;
			
            //Vex.
            $vex = getVex();
			$qa_content['custom'] .= $vex;			
			
			$qa_content['custom'] .= displayFriendListNavBar();


			if (!empty($incomingRequestList)) {
				$qa_content['custom'] .= "<h3>Incoming Requests</h3>";
                //Even/odd wrapper color.
                $wrapper = true;
				foreach ($incomingRequestList as $friend) {
				
					if ($friend["avatarblobid"] == null) {
						$friend["avatarblobid"] = qa_opt('avatar_default_blobid');
					}
					
                    //Start the wrapper.
                    $qa_content['custom'] .= getFriendWrapper($wrapper);

					$qa_content['custom'] .= '<a href="/user/' . $friend["handle"] . '"><img src="./?qa=image&amp;qa_blobid= ' . $friend["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/></a>';

					$qa_content['custom'] .= getFriendUnit($friend["userid"], $friend["handle"]);

                    $qa_content['custom'] .= '<div class="friends-btn-wrapper">';
					
					$approveRequestButton = 'class="friends-btns button button-creation" type="button" onclick="window.location.href=\'/friend-functions/approveRequest/'.$friend["userid"].'/requests/\';"';
					$qa_content['custom'] .= '<input value="Approve Request" '.$approveRequestButton.'>';
					
					$denyRequestButton = 'class="friends-btns button button-negative" type="button" onclick="window.location.href=\'/friend-functions/removeRequestI/'.$friend["userid"].'/requests/\';"';
					$qa_content['custom'] .= '<input value="Deny Request" '.$denyRequestButton.'>';
					
					$qa_content['custom'] .= '</div><br>';

                    //End the wrapper.
                    $qa_content['custom'] .= endFriendWrapper();
                    //Alternate the wrapper.
                    $wrapper = !$wrapper;
				}
			}		

			if (!empty($outgoingRequestList)) {
				$qa_content['custom'] .= "<h3>Outgoing Requests</h3>";
                //Even/odd wrapper color.
                $wrapper = true;
				foreach ($outgoingRequestList as $friend) {
				
					if ($friend["avatarblobid"] == null) {
						$friend["avatarblobid"] = qa_opt('avatar_default_blobid');
					}
				
                    //Start the wrapper.
                    $qa_content['custom'] .= getFriendWrapper($wrapper);

					$qa_content['custom'] .= '<a href="/user/' . $friend["handle"] . '"><img src="./?qa=image&amp;qa_blobid= ' . $friend["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/></a>';

					$qa_content['custom'] .= getFriendUnit($friend["userid"], $friend["handle"]);
					
                    $qa_content['custom'] .= '<div class="friends-btn-wrapper">';

					$removeRequestButton = 'class="friends-btns button button-primary" type="button" onclick="window.location.href=\'/friend-functions/removeRequest/'.$friend["userid"].'/requests/\';"';
					$qa_content['custom'] .= '<input value="Remove Request" '.$removeRequestButton.'>';
				
					$qa_content['custom'] .= '</div><br>';
					
                    //End the wrapper.
                    $qa_content['custom'] .= endFriendWrapper();
                    //Alternate the wrapper.
                    $wrapper = !$wrapper;
				}
			}			
			
			if ((empty($outgoingRequestList)) && (empty($incomingRequestList))) {
				$qa_content['custom'] .= "<br>You have no incoming or outgoing friend requests.";
			}
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
