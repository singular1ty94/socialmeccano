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
			
			// TO DO: Check that the user is logged in.
			// TO DO: Check that the user is a member of the group. So they can't see all the things.
			
			include 'qa-group-db.php';
			$groupProfile = getGroupData($groupid);
			
			
			// If the DB returns an empty array, group not found, so redirect to groups page
			if (empty($groupProfile)) {
				qa_redirect('groups');
			}

			// Set vars from DB result
			$createdAt = $groupProfile[0]["created_at"];
			$groupName = $groupProfile[0]["group_name"];
			$groupDescription = $groupProfile[0]["group_description"];
			$groupInfo = $groupProfile[0]["group_information"];
			$groupTags = $groupProfile[0]["tags"];
			$groupCreator = $groupProfile[0]["created_by"];
			$groupAvatar = $groupProfile[0]["avatarblobid"];
						
			
			$memberCount = getMemberCount($groupid)[0]["COUNT(user_id)"];
			
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
			
			// Set the browser tab title for the page.
			$qa_content['title']=$groupName;

			
            $heads = '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"><script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script><link rel="stylesheet" href="/resources/demos/style.css"><script>$(function(){$( "#tabs" ).tabs();});</script>';
            
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= $groupName. '<br><br>';
			$qa_content['custom2']='<table cellspacing="20">';
			$c = 2;
			
            //Tabs Header.            
            $qa_content['custom'] .= '<div id="tabs">
                <ul>
                <li><a href="#overview">Overview</a></li>
                <li><a href="#announcements">Announcements</a></li>
                <li><a href="#discussions">Discussions</a></li>
                <li><a href="#members">Members</a></li>
                </ul>';		

            //group Tabs
            $overview = '<div id="overview">Group Information<br>' . $groupInfo .'<br>
			<br>Recent Announcements<br>
			<br>Recent Discussions<br>
			<br>Members: ' . $memberCount ;
            $groupAnnoucements = '<div id="announcements">';
            $groupDiscussions = '<div id="discussions">';
            $groupMembers = '<div id="members">'; 
            
            //Add the tabs.
            $qa_content['custom'] .= $overview .= '</div>';
            $qa_content['custom'] .= $groupAnnoucements .= '</div>';
            $qa_content['custom'] .= $groupDiscussions .= '</div>';
            $qa_content['custom'] .= $groupMembers .= '</div>';

			

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
