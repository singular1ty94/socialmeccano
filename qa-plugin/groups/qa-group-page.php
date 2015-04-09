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
			
			include '././qa-include/app/posts.php';	
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$viewer=qa_load_viewer('', '');
			
			$userid = qa_get_logged_in_userid();
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
            
            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			
			
            //If the user is the admin, and wants to delete.
            if($currentUserIsAdmin && @isset($_GET['delete'])){
                deleteGroup(intval($groupid));
                header('Location: ../?qa=groups');
            }
			
			//If the user wants to join the group.
            if(!$currentUserIsMember && @isset($_GET['join_group'])){
				$is_admin = 0;
				AddUserToGroup(intval($userid), intval($groupid), $is_admin);
				$currentUserIsMember = true;
                header('Location: ../group/'.$groupid);
            }
			
			//If the user wants to leave the group.
            if($currentUserIsMember && @isset($_GET['leave_group'])){
				removeUserFromGroup(intval($userid), intval($groupid));
                header('Location: ../?qa=groups');
            }
			
			$groupProfile = getGroupData($groupid);

			// If the DB returns an empty array, group not found, so redirect to groups page
			if (empty($groupProfile)) {
				qa_redirect('groups');
			}

			// Set vars from DB result
			$createdAt = $groupProfile["created_at"];
			$groupName = qa_post_content_to_text($groupProfile["group_name"], 'html');
			$groupDescription = qa_post_content_to_text($groupProfile["group_description"], 'html');
			$groupInfo = $viewer->get_html($groupProfile["group_information"], '', array(
				'blockwordspreg' => @$options['blockwordspreg'] = 1,
				'showurllinks' => @$options['showurllinks'] = 1,
				'linksnewwindow' => @$options['linksnewwindow'] = 1,
			));
			$groupTags= qa_post_content_to_text($groupProfile["tags"], 'html');
			$groupCreator = $groupProfile["created_by"];
			$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $groupProfile["avatarblobid"] . '&amp;qa_size=200" class="qa-avatar-image" alt=""/>';
			$groupLocation = qa_post_content_to_text($groupProfile["group_location"], 'html');
			$groupWebsite  = $viewer->get_html($groupProfile["group_website"], '', array(
				'blockwordspreg' => @$options['blockwordspreg'] = 0,
				'showurllinks' => @$options['showurllinks'] = 1,
				'linksnewwindow' => @$options['linksnewwindow'] = 1,
			));
			
			
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
			
			// Set the browser tab title for the page.
			$qa_content['title']=$groupName;

			//Get the JQueryUI script fragment.
            $heads = getJQueryUITabs('group-tabs');
			$qa_content['custom']= $heads;
            
            //Vex.
            $vex = getVex();
			$qa_content['custom'] .= $vex;
            
            //Left-hand pane.
            $qa_content['custom'] .= getSidePane() . $groupAvatarHTML . makeSidePaneFieldWithLabel($memberCount, 'group-member-count', 'Members', 'group-member-count-label');
            $qa_content['custom'] .= makeSidePaneField($groupDescription, 'group-desc-field') . makeSidePaneField($groupLocation, 'group-location-field');
			$qa_content['custom'] .= makeSidePaneField($groupWebsite, 'group-website-field') . makeSidePaneRaw(getGroupTags($groupTags));
			
			
			$qa_content['custom'] .= endSidePane($currentUserIsMember);
			
			
            //Group header.
			$qa_content['custom'] .= getGroupHeader($groupid, $groupName, $currentUserIsMember);

			
			//If the user is not a member END here and don't render tabs and group content.
            if(!$currentUserIsMember){
				$qa_content['custom'] .= "You are not a member of this group. Please join to see the group content.";
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
					$groupAnnouncementsTab .= '<a href="../create-post/'.$groupid.'" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">Create Post</a>';
				}
				
				
				if (empty($announcements)) {
					$groupAnnouncementsTab .=  'No announcements to display.';
				}
				$groupAnnouncementsTab .= displayGroupPosts($announcements);

				
				/*
				*	Discussions Tab
				*/
				$groupDiscussionsTab = '<div class="group-tabs" id="discussions">';
				$groupDiscussionsTab .= '<a href="../create-post/'.$groupid.'?type=d" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">Create Post</a>';
				if (empty($discussions)) {
					$groupDiscussionsTab .= '<div class="">No discussions to display.</div>';
				}
				$groupDiscussionsTab .= displayGroupPosts($discussions);			

				
				/*
				*	Members Tab
				*/
				$groupMembersTab = '<div class="group-tabs" id="members">';
				
				// Loop through all admins and display them at the top
				$groupMembersTab .= 'Administrators: <br>';
				foreach ($groupAdmins as $admin) {
					$groupMembersTab .= displayGroupListMember($admin["handle"], $admin["avatarblobid"]);
				}
				
				// Loop through all group members display them next
				$groupMembersTab .= '<br> Members: <br>';
				if (empty($groupMembers)) {
					$groupMembersTab .= 'There are no group members to display.';
				} else {
					foreach ($groupMembers as $member) {
						$groupMembersTab .= displayGroupListMember($member["handle"], $member["avatarblobid"]);
					}
				}
				
				/*
				*	Admin Tab
				*/
				$groupAdminTab = '<div class="group-tabs" id="admin"><br>';
				$groupAdminTab .= '<a href="#" id="delete-btn" class="groups-btns groups-delete-btn">Delete Group</a>';
				//Edit Button.
                $groupAdminTab .= '<a href="../group-update/'. $groupid .'" id="update-btn" class="groups-btns groups-update-btn">Update Group</a>';
				
				
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
