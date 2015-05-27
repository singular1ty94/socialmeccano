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

	class qa_group_invites_page {
		
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
			if ($requestpart=='group-invites')
				return true;

			return false;
		}		

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']="Group Invitations";

			include './qa-include/app/posts.php';	
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$userid = qa_get_logged_in_userid();
            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			

			$intent = qa_request_part(1);
			$groupid = qa_request_part(2);
			$groupname = getGroupName($groupid);
			
			if (strlen($intent) && strlen($groupid)) {
				if ($intent == approve_invite && isUserInvitedOrRequested($userid, $groupid, "I")) {
					addUserToGroup($userid, $groupid, 0);
				} else if ($intent == remove_invite) {
					removeGroupRequest($userid, $groupid, "I");
				}
				qa_redirect('group-invites/');
			}
			
			
			$invitationList = displayMyGroupInvitations($userid);

            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= displayGroupListNavBar();
			
			
			if (empty($invitationList)) {
				$qa_content['custom'] .= '<br />You have no outstanding invitations.';
			}
			else {
                //Even/odd wrapper color.
                $wrapper = true;
				$qa_content['raw'] = [];
                
				foreach ($invitationList as $group) {
                    //Output to the raw JSON format.
                    $qa_content['raw'][$group["id"]] = $group;
                    
					$groupCreatedDate = $group["created_at"];
					$groupid = $group["id"];
					$groupName = qa_post_content_to_text($group["group_name"], 'html');
					$groupDescription = qa_post_content_to_text($group["group_description"], 'html');
					if ($group["avatarblobid"] == null) {
						$group["avatarblobid"] = qa_opt('avatar_default_blobid');
					}						
					$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $group["avatarblobid"] . '&amp;qa_size=100" class="qa-avatar-image" alt=""/>';
					$groupTags = qa_post_content_to_text($group["tags"], 'html');


					
                    //Start the wrapper.
                    $qa_content['custom'] .= getGroupListWrapper($wrapper);
					
					//Get the Group name.
					$qa_content['custom'] .= $groupAvatarHTML . getGroupUnit($groupid, $groupName, $groupDescription, $groupTags, 'invites');

				
					
						$qa_content['custom'] .= '<div class="friends-btn-wrapper">';
						
						$sendInviteButton = 'class="button button-creation" type="button" onclick="window.location.href=\'/group-invites/approve_invite/'.$groupid.'\';"';
						$qa_content['custom'] .= '<input value="Accept Invite" '.$sendInviteButton.'>';

						$removeInviteButton = 'class="button button-negative" type="button" onclick="window.location.href=\'/group-invites/remove_invite/'.$groupid.'\';"';
						$qa_content['custom'] .= '<input value="Remove Invite" '.$removeInviteButton.'>';

						$qa_content['custom'] .= '</div><br>';

                    //End the wrapper.
                    $qa_content['custom'] .= endGroupListWrapper();
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
