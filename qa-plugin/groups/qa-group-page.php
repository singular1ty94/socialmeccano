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
			
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$userid = qa_get_logged_in_userid();
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
            
            //If the user is the admin, and wants to delete.
            if($currentUserIsAdmin && @isset($_GET['delete'])){
                deleteGroup(intval($groupid));
                header('Location: ../?qa=groups');
            }
			
			$groupProfile = getGroupData($groupid);

			// If the DB returns an empty array, group not found, so redirect to groups page
			if (empty($groupProfile)) {
				qa_redirect('groups');
			}

			// Set vars from DB result
			$createdAt = $groupProfile["created_at"];
			$groupName = $groupProfile["group_name"];
			$groupDescription = $groupProfile["group_description"];
			$groupInfo = $groupProfile["group_information"];
			$groupTags = $groupProfile["tags"];
			$groupCreator = $groupProfile["created_by"];
			$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $groupProfile["avatarblobid"] . '&amp;qa_size=200" class="qa-avatar-image" alt=""/>';
			$groupLocation = $groupProfile["group_location"];
			$groupWebsite = $groupProfile["group_website"];
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
            $qa_content['custom'] .= getSidePane() . $groupAvatarHTML . makeSidePaneFieldWithLabel($memberCount, 'group-member-count', 'Members', 'group-member-count-label') . makeSidePaneField($groupInfo, 'group-info-field') . makeSidePaneRaw(getGroupTags($groupTags)) . endSidePane();
            
            //Group header.
			$qa_content['custom'] .= getGroupHeader($groupName);
			
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
            $wrapper = true;
			foreach ($recentAnnouncements as $annoucement) {
				$postid = $annoucement["id"];
				$userName = $annoucement["handle"];
				$userAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $annoucement["avatarblobid"] . '&amp;qa_size=50" class="qa-avatar-image" alt=""/>';
				$postDate = get_time(qa_when_to_html($annoucement["posted_at"], @$options['fulldatedays']));
				$postTitle = $annoucement["title"];
				$postContent = $annoucement["content"];
				$postTags = $annoucement["tags"];
				$postRepliesCount = getCommentCount($postid)["COUNT(id)"];

                $overviewTab .= makeGroupPost($postTitle, $postContent, $postDate, $userName, $userAvatarHTML, $wrapper);
                $wrapper = !$wrapper;
				//$overviewTab .= '<br>' . $userName . $userAvatarHTML . $postDate . $postTitle . $postContent . $postTags . $postRepliesCount . '<br>';
			}
			
			$overviewTab .= '<hr/><br>Recent Discussions<br>';
			foreach ($recentDiscussions as $discussion) {
				$postid = $discussion["id"];
				$userName = $discussion["handle"];
				$userAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $discussion["avatarblobid"] . '&amp;qa_size=50" class="qa-avatar-image" alt=""/>';
				$postDate = get_time(qa_when_to_html($discussion["posted_at"], @$options['fulldatedays']));
				$postTitle = $discussion["title"];
				$postContent = $discussion["content"];
				$postTags = $discussion["tags"];
				$postRepliesCount = getCommentCount($postid)["COUNT(id)"];
				
				$overviewTab .= makeGroupPost($postTitle, $postContent, $postDate, $userName, $userAvatarHTML, $wrapper);
                $wrapper = !$wrapper;				
			}
			
			
			/*
			*	Announcements Tab
			*/
            $groupAnnouncementsTab = '<div class="group-tabs" id="announcements">';
			foreach ($announcements as $annoucement) {
				$postid = $annoucement["id"];
				$userName = $annoucement["handle"];
				$userAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $annoucement["avatarblobid"] . '&amp;qa_size=50" class="qa-avatar-image" alt=""/>';
				$postDate = $annoucement["posted_at"];
				$postTitle = $annoucement["title"];
				$postContent = $annoucement["content"];
				$postTags = $annoucement["tags"];
				$postRepliesCount = getCommentCount($postid)["COUNT(id)"];
				
				$groupAnnouncementsTab .= makeGroupPost($postTitle, $postContent, $postDate, $userName, $userAvatarHTML, $wrapper);
			}
			
			
			
			/*
			*	Discussions Tab
			*/
            $groupDiscussionsTab = '<div class="group-tabs" id="discussions">';
			foreach ($discussions as $discussion) {
				$postid = $discussion["id"];
				$userName = $discussion["handle"];
				$userAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $discussion["avatarblobid"] . '&amp;qa_size=50" class="qa-avatar-image" alt=""/>';
				$postDate = $discussion["posted_at"];
				$postTitle = $discussion["title"];
				$postContent = $discussion["content"];
				$postTags = $discussion["tags"];
				$postRepliesCount = getCommentCount($postid)["COUNT(id)"];
				
				$groupDiscussionsTab .= makeGroupPost($postTitle, $postContent, $postDate, $userName, $userAvatarHTML, $wrapper);
			}
			
			/*
			*	Members Tab
			*/
            $groupMembersTab = '<div class="group-tabs" id="members">';
			
			// Loop through all admins and display them at the top
			$groupMembersTab .= 'Administrators: <br>';
			foreach ($groupAdmins as $admin) {
				$groupMembersTab .= displayGroupMember($admin["handle"], $admin["avatarblobid"]);
			}
			
			// Loop through all group members display them next
			$groupMembersTab .= '<br> Members: <br>';
			foreach ($groupMembers as $member) {
				$groupMembersTab .= displayGroupMember($member["handle"], $member["avatarblobid"]);
			}

			
			/*
			*	Admin Tab
			*/
			$groupAdminTab = '<div class="group-tabs" id="admin">';
            $groupAdminTab .= '<a href="#" id="delete-btn" class="groups-delete-btn">Delete Group</a>';
			
            
            //Add the tabs.
            $qa_content['custom'] .= $overviewTab .= '</div>';
            $qa_content['custom'] .= $groupAnnouncementsTab .= '</div>';
            $qa_content['custom'] .= $groupDiscussionsTab .= '</div>';
            $qa_content['custom'] .= $groupMembersTab .= '</div>';
			
			// Only display admin tab if the current user is one.
			if ($currentUserIsAdmin) {
				$qa_content['custom'] .= $groupAdminTab .= '</div>';
            }
			

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
