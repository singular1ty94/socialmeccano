<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/groups/qa-group-page.php
	Description: Displays an individual group page/profile.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/
	class qa_group_page {
		
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
			if ($requestpart=='group')
				return true;

			return false;
		}

		function process_request($request)
		{
            		
			// Get the group id from the page request, redirect to groups if no group is set.
			$groupid = qa_request_part(1);
			if (!strlen($groupid)) {
				qa_redirect(isset($groupid) ? 'group/'.$groupid : 'groups');
			}
			
			include './qa-include/app/posts.php';	
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
            include './qa-plugin/notifications/qa-notifications-db.php';
			
			$viewer=qa_load_viewer('', '');
			
			$userid = qa_get_logged_in_userid();
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
			$groupType = getGroupType($groupid)["privacy_setting"];
			$userInvited = isUserInvitedOrRequested($userid, $groupid, "I");
            $userRequestedJoin = isUserInvitedOrRequested($userid, $groupid, "R");
			
            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			
			
            $groupProfile = getGroupData($groupid);

			// If the DB returns an empty array, group not found, so redirect to groups page
			if (empty($groupProfile)) {
				qa_redirect('groups');
			}

            //If the user is the admin, and wants to delete.
            if($currentUserIsAdmin && @isset($_GET['delete'])){
                deleteGroup(intval($groupid));
                header('Location: ../?qa=groups');
            }
			
            //If the user is trying to view a secret group, but isn't a member and hasn't been invited.
            if($groupType == 'S' && !$currentUserIsMember && !$userInvited) {
                header('Location: ../?qa=groups');
            }
			
			//If the user wants to join the group.
            if(!$currentUserIsMember && @isset($_GET['join_group'])){
				$is_admin = 0;
				
				// If the group is open, add them immediately.
				if ($groupType == 'O') {
					AddUserToGroup(intval($userid), intval($groupid), $is_admin);
					
					$allAdmins = getGroupAdmins($groupid);
					foreach($allAdmins as $admin){
						createNotification($admin["userid"], 'NewGroupUser', $userid, qa_post_content_to_text($groupProfile["group_name"], 'html'));
					}					
				}
				
				// If the group is closed, create a join request.
				else if ($groupType == 'C') {
					sendGroupRequest($userid, $groupid, "R");
					
					$allAdmins = getGroupAdmins($groupid);
					foreach($allAdmins as $admin){
						createNotification($admin["userid"], 'NewGroupUserRequest', $userid, qa_post_content_to_text($groupProfile["group_name"], 'html'), $groupid);
					}					
				}
				
				// If the group is secret, and the user has been invited.				
				else if ($groupType == 'S' && $userInvited) {
					AddUserToGroup(intval($userid), intval($groupid), $is_admin);
					
					$allAdmins = getGroupAdmins($groupid);
					foreach($allAdmins as $admin){
						createNotification($admin["userid"], 'NewGroupUser', $userid, qa_post_content_to_text($groupProfile["group_name"], 'html'));
					}
				}		
                header('Location: ../group/'.$groupid);
            }
			
			//If the user wants to leave the group.
            if($currentUserIsMember && @isset($_GET['leave_group'])){
				removeUserFromGroup(intval($userid), intval($groupid));
                header('Location: ../?qa=groups');
            }

            //If the admin wants to remove a user.
            if($currentUserIsAdmin && @isset($_GET['remove'])){
				removeUserFromGroup(intval($_GET['remove']), intval($groupid));
                header('Location: ../group/'.$groupid.'#members');
            }

			
            //If the admin wants to deny a membership request.
            if($currentUserIsAdmin && @isset($_GET['deny_request'])){
				removeGroupRequest($_GET['deny_request'], $groupid, "R");
                header('Location: ../group/'.$groupid.'#members');
            }

            //If the admin wants to approve a membership request
            if($currentUserIsAdmin && @isset($_GET['approve_request'])){
				removeGroupRequest($_GET['approve_request'], $groupid, "R");
				AddUserToGroup(intval($_GET['approve_request']), $groupid, 0);
				$groupname = getGroupName($groupid);
				createNotification($_GET['approve_request'], 'NewGroupInviteApproval', $userid, qa_post_content_to_text($groupProfile["group_name"], 'html'), $groupid);
                header('Location: ../group/'.$groupid.'#members');
            }			
			

			// Set vars from DB result
			$createdAt = $groupProfile["created_at"];
			$groupName = qa_post_content_to_text($groupProfile["group_name"], 'html');
			$groupDescription = qa_post_content_to_text($groupProfile["group_description"], 'html');
			$groupInfo = $viewer->get_html($groupProfile["group_information"], '', array(
				'blockwordspreg' => @$options['blockwordspreg'],
				'showurllinks' => @$options['showurllinks'] = 1,
				'linksnewwindow' => @$options['linksnewwindow'] = 1,
			));
			$groupTags= qa_post_content_to_text($groupProfile["tags"], 'html');
			$groupCreator = $groupProfile["created_by"];
			$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $groupProfile["avatarblobid"] . '&amp;qa_size=200" class="qa-avatar-image" alt=""/>';
			$groupLocation = qa_post_content_to_text($groupProfile["group_location"], 'html');
			$groupWebsite  = $groupProfile["group_website"];
			$memberCount = getMemberCount($groupid)["COUNT(user_id)"];
		
			
		
		
			//Overview Tab Info
			$recentAnnouncements = getRecentAnnouncements($groupid);
			$recentDiscussions = getRecentDiscussions($groupid);
			
			// Announcements Tab Info
			$announcements = getAllannouncements($groupid);
			
			// Discussions Tab Info
			$discussions = getAllDiscussions($groupid);
			
			//Members Tab Info
			$groupAdmins = getGroupAdmins($groupid);
			$groupMembers = getGroupMembers($groupid);
	
	
			// UI Generation below this.			
			$qa_content=qa_content_prepare();
            
            //JSON prepare
            $qa_content['raw']['group'] = $groupProfile;
			
			// Set the browser tab title for the page.
			$qa_content['title'] = $groupName;

			
			//Get the JQueryUI script fragment.
            $heads = getJQueryUITabs('group-tabs');
			$qa_content['custom']= $heads;
            
            //Vex.
            $vex = getVex();
			$qa_content['custom'] .= $vex;
            
            //Left-hand pane.
            $qa_content['custom'] .= getSidePane() . $groupAvatarHTML . makeSidePaneFieldWithLabel($memberCount, 'group-member-count', $memberCount == 1 ? ' Member' : ' Members', 'group-member-count-label');
            $qa_content['custom'] .= makeSidePaneField($groupDescription, 'group-desc-field') . makeSidePaneField($groupLocation, 'group-location-field');
			$qa_content['custom'] .= makeSidePaneURL($groupWebsite, 'group-website-field');
			
			if (!empty($groupTags)) {
				$qa_content['custom'] .= makeSidePaneRaw(getGroupTags($groupTags));
			}
			
			
			$qa_content['custom'] .= endSidePane($currentUserIsMember, $groupName);
			
			
            //Group header.
			$qa_content['custom'] .= getGroupHeader($groupid, $groupName, $currentUserIsMember, $userInvited, $userRequestedJoin);

			
			//If the user is not a member END here and don't render tabs and group content.
            if(!$currentUserIsMember && !$userRequestedJoin){
				$qa_content['custom'] .= "You are not a member of this group. Please join to see the group content.";
            }
            else if (!$currentUserIsMember && $userRequestedJoin){
				$qa_content['custom'] .= "Your membership request is pending admin approval.";
            }			
			else {
			
				//Tabs Header.            
				$qa_content['custom'] .= '<div id="group-tabs">
					<ul>
					<li><a href="#overview">Overview</a></li>
					<li><a href="#announcements">Announcements</a></li>
					<li><a href="#discussions">Discussions</a></li>
					<li><a href="#members">Members</a></li>';
				
				// Only display admin tab if the current user is one.			
				if ($currentUserIsAdmin) {
					$qa_content['custom'] .= '<li><a href="#admin">Admin Tools</a></li>';
				}
				
				$qa_content['custom'] .= '</ul>';		

				//group Tabs
				
				/*
				*	Overview Tab
				*/	
				$overviewTab = '<div id="overview" class="group-tabs">Group Information<br/><span class="group-info">' . $groupInfo .'</span><hr/>';
				
				$overviewTab .= 'Recent Announcements';
				if (empty($recentAnnouncements)) {
					$overviewTab .= 'No recent announcements to display.';
				}
				$overviewTab .= displayGroupPosts($recentAnnouncements);
                
				
				$overviewTab .= '<hr/><br>Recent Discussions<br>';
				if (empty($recentDiscussions)) {
					$overviewTab .= 'No recent discussions to display.';
				}
				$overviewTab .= displayGroupPosts($recentDiscussions);
				
				
				/*
				*	Announcements Tab
				*/
				$groupAnnouncementsTab = '<div class="group-tabs" id="announcements">';
				
				//if the user is an admin, show a create post button on the announcements tab as well
				if ($currentUserIsAdmin) {
					$groupAnnouncementsTab .= '<a href="../create-post/'.$groupid.'?type=a" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">Create Post</a>';
				}
				
				
				if (empty($announcements)) {
					$groupAnnouncementsTab .=  'No announcements to display.';
				}else{
                    $groupAnnouncementsTab .= displayGroupPosts($announcements);
                    //JSON
                    $qa_content['raw']['group']['announcements'] = $announcements;
                }

				
				/*
				*	Discussions Tab
				*/
				$groupDiscussionsTab = '<div class="group-tabs" id="discussions">';
				$groupDiscussionsTab .= '<a href="../create-post/'.$groupid.'?type=d" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">Create Post</a>';
				if (empty($discussions)) {
					$groupDiscussionsTab .= '<div class="">No discussions to display.</div>';
				}else{
                    if(hasPinned($discussions)){
                        $groupDiscussionsTab .= '<img class="group-sticky" src="../qa-plugin/groups/images/pin.png" /><h2 class="group-sticky">Pinned Discussions</h2>';
                        $groupDiscussionsTab .= displayStickyPosts($discussions);
                    }
                    $groupDiscussionsTab .= '<h2 class="group-sticky">Discussions</h2>';
                    $groupDiscussionsTab .= displayGroupPosts($discussions);	
                    
                    //JSON
                    $qa_content['raw']['group']['discussions'] = $discussions;
                }

				
				/*
				*	Members Tab
				*/
				$groupMembersTab = '<div class="group-tabs" id="members">';
				
				
				// Loop through all join requests and show them first. Only to Admins.
				if ($currentUserIsAdmin) {
					$pendingRequests = displayGroupJoinRequests($groupid);
					
					if (!empty($pendingRequests)) {
						$groupMembersTab .= '<h3 class="MemberHeader">Pending Membership Requests:</h3>';
						foreach ($pendingRequests as $request) {
							if ($request["avatarblobid"] == null) {
								$request["avatarblobid"] = qa_opt('avatar_default_blobid');
							}
							$groupMembersTab .= displayGroupListRequest($request["handle"], $request["avatarblobid"], $request["userid"], $groupid);
						}
					}
				}

				// Loop through all admins and display them at the top
				$groupMembersTab .= '<h3 class="MemberHeader">Administrators:</h3>';
				foreach ($groupAdmins as $admin) {
						if ($admin["avatarblobid"] == null) {
							$admin["avatarblobid"] = qa_opt('avatar_default_blobid');
						}
					$groupMembersTab .= displayGroupListMember($admin["handle"], $admin["avatarblobid"]);
				}
				
				// Loop through all group members display them next
				$groupMembersTab .= '<h3 class="MemberHeader">Members:</h3>';
				if (empty($groupMembers)) {
					$groupMembersTab .= 'There are no group members to display.';
				} else {
					foreach ($groupMembers as $member) {
						if ($member["avatarblobid"] == null) {
							$member["avatarblobid"] = qa_opt('avatar_default_blobid');
						}
                        if($currentUserIsAdmin){
                            $groupMembersTab .= displayGroupListMember($member["handle"], $member["avatarblobid"], $member["userid"]);
                        }else{
                            $groupMembersTab .= displayGroupListMember($member["handle"], $member["avatarblobid"]);
                        }
					}
                    //JSON
                    $qa_content['raw']['group']['members'] = $groupMembers;
				}
				
				/*
				*	Admin Tab
				*/
				$groupAdminTab = '<div class="group-tabs" id="admin">';
				$groupAdminTab .= 'Administrative Tools:<br>';
				$groupAdminTab .= '<a href="#" id="delete-btn" class="button button-negative " style="margin-right:10px;">Delete Group</a>';
				//Edit Button.
                $groupAdminTab .= '<a href="../group-update/'. $groupid .'" id="update-btn" class="button button-primary">Update Group</a>';
				
				
				//Add the tabs.
				$qa_content['custom'] .= $overviewTab .= '</div>';
				$qa_content['custom'] .= $groupAnnouncementsTab .= '</div>';
				$qa_content['custom'] .= $groupDiscussionsTab .= '</div>';
				$qa_content['custom'] .= $groupMembersTab .= '</div>';
				
				// Only display admin tab if the current user is one.
				if ($currentUserIsAdmin) {
					$qa_content['custom'] .= $groupAdminTab .= '</div>';
				}
			}

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
