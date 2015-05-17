<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/groups/qa-grouplist-page.php
	Description: Displays a list of joined groups.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/

	class qa_group_send_invites_page {
		
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
			if ($requestpart=='group-send-invites')
				return true;

			return false;
		}

		function process_request($request)
		{
		
			include './qa-include/app/posts.php';	
			include 'qa-group-db.php';
            include './qa-plugin/friends/qa-friends-db.php';
            include './qa-plugin/friends/qa-friends-helper.php';	
            include './qa-plugin/notifications/qa-notifications-db.php';		
		
			// Get the group id from the page request, redirect to groups if no group is set.
			$groupid = qa_request_part(1);
			if (!strlen($groupid)) {
				qa_redirect(isset($groupid) ? 'group/'.$groupid : 'groups');
			}
			
			$friendid = qa_request_part(2);
			if (strlen($friendid)) {
				sendGroupRequest($friendid, $groupid, "I");
				qa_redirect('group-send-invites/'.$groupid.'');
			}
			
            $groupProfile = getGroupData($groupid);
			$userid = qa_get_logged_in_userid();
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			
			
			// If the DB returns an empty array, group not found, so redirect to groups page
			if (empty($groupProfile)) {
				qa_redirect('groups');
			}

            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			

			if(!$currentUserIsMember) {
				qa_redirect('groups');
			}
			
			
			$qa_content=qa_content_prepare();
			$qa_content['title']="Invite friends to ".$groupProfile["group_name"];
			
			$friendsList = getMyFriends($userid);

            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			
			if (empty($friendsList)) {
				$qa_content['custom'] .= '<br/>You have no friends to invite.';
			}
			else {
                //Even/odd wrapper color.
                $wrapper = true;
				$qa_content['raw'] = [];
               	$count = 0;
				
				foreach ($friendsList as $friend) {
					
					$isMember = isUserGroupMember($friend["userid"], $groupid);
					$isInvited = isUserInvitedOrRequested($friend["userid"], $groupid, "I");
					$hasRequested = isUserInvitedOrRequested($friend["userid"], $groupid, "R");
					
					if (!$isMember && !$isInvited && !$hasRequested) {
						//Start the wrapper.
						$qa_content['custom'] .= getFriendWrapper($wrapper);

						$qa_content['custom'] .= '<a href="/user/' . $friend["handle"] . '"><img src="./?qa=image&amp;qa_blobid= ' . $friend["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/></a>';

						$qa_content['custom'] .= getFriendUnit($friend["userid"], $friend["handle"]);
						
						$arr = array($friend["handle"], qa_get_logged_in_user_field('handle'));
						sort($arr);


						$qa_content['custom'] .= '<div class="friends-btn-wrapper">';

						$sendInviteButton = 'class="button button-creation" type="button" onclick="window.location.href=\'/group-send-invites/'.$groupid.'/'.$friend["userid"].'\';"';
						$qa_content['custom'] .= '<input value="Send Invite" '.$sendInviteButton.'>';

						$qa_content['custom'] .= '</div><br>';

						//End the wrapper.
						$qa_content['custom'] .= endFriendWrapper();
						//Alternate the wrapper.
						$wrapper = !$wrapper;
						$count++;
					}			
				}
				if ($count == 0) {
					$qa_content['custom'] .= '<br/>All of your friends are already members or invited.';
				}
			}
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
