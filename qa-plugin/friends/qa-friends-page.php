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

	class qa_friends_page {
		
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
			if ($requestpart=='friends')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']="My Friends";
			
			include 'qa-friends-db.php';
			include 'qa-friends-helper.php';
			
			$userid = qa_get_logged_in_userid();

            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
               header('Location: ../');
            }			
			
			
			//If the user wants to remove a friend.
            if(isset($_GET['unfriend'])){
                header('Location: ../?qa=friends');
            }


			
			// Get my friends from DB.
			$friendList = getMyFriends($userid);


            $heads = getJQueryUITabs('tabs');

			$qa_content['custom']= $heads;
			
            //Vex.
            $vex = getVex();
			$qa_content['custom'] .= $vex;			
			
			$qa_content['custom'] .= displayFriendListNavBar();


			if (empty($friendList)) {
				$qa_content['custom'] .= "<br>You haven't added any friends yet.";
			}
			else {
                //Even/odd wrapper color.
                $wrapper = true;

				foreach ($friendList as $friend) {

                    //Start the wrapper.
                    $qa_content['custom'] .= getFriendWrapper($wrapper);

					$qa_content['custom'] .= '<a href="/user/' . $friend["handle"] . '"><img src="./?qa=image&amp;qa_blobid= ' . $friend["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/></a>';

					$qa_content['custom'] .= getFriendUnit($friend["userid"], $friend["handle"]);
					
                    $arr = array($friend["handle"], qa_get_logged_in_user_field('handle'));
                    sort($arr);

                    $qa_content['custom'] .= '<div class="chat-button chat-open" data-user="' .  qa_get_logged_in_user_field('handle') . '" data-channel="' . $arr[0] . $arr[1] . '">Chat</div>';

					$removeRequestButton = 'class="qa-form-wide-button qa-form-tall-button-cancel" type="button" onclick="window.location.href=\'/friend-functions/removeFriend/'.$friend["userid"].'/myFriends/\';"';
					$qa_content['custom'] .= '<input value="Remove Friend" '.$removeRequestButton.'>';

					$qa_content['custom'] .= '<br>';

                    //End the wrapper.
                    $qa_content['custom'] .= endFriendWrapper();
                    //Alternate the wrapper.
                    $wrapper = !$wrapper;
				}
			}
			
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
