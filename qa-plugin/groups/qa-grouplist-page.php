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

	class qa_grouplist_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			if ($request=='groups')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']=qa_lang('groups/group_list_title');
			
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$userid = qa_get_logged_in_userid();

			
			// TWO options here, get a list of all groups, or get a list of MY groups. (maybe we could do tabs?)
			$groupList = getAllGroups();
			 //$groupList = getMyGroups($userid);

            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= '<h2>Group List</h2>';
			
			
			if (empty($groupList)) {
			
				// Simply for testing purposes
				$myBlobid = '18056448301737554770';
				$groupid = createNewGroup('Test Group Pls Ignore', 'This is our test group', $myBlobid, 'This is our group information', 'test,PHP', $userid);
				createPost($groupid, $userid, 'Test Announcement One', 'We are testing this once', 'test', 'A');
				createPost($groupid, $userid, 'Test Announcement Two', 'We are testing this twice', 'test', 'A');
				createPost($groupid, $userid, 'Test Discussion One', 'We are testing this once', 'test', 'D');
				createPost($groupid, $userid, 'Test Discussion Two', 'We are testing this twice', 'test', 'D');
				addUserToGroup(2, $groupid, 0);
				addUserToGroup(3, $groupid, 0);
				$qa_content['custom'] .= 'No Groups Found! <br> I just made one for you to test with with id#' . $groupid;
				$qa_content['custom'] .= '<br><a href="./group/' . $groupid . '">Take me there...</a>';
				$groupid = createNewGroup('The Best Test Group Ever', 'This is our other test group', $myBlobid, 'This is our group information', 'test,PHP', $userid);
				
			}
			else {
                //Even/odd wrapper color.
                $wrapper = true;
				       
				foreach ($groupList as $group) {
					$groupCreatedDate = $group["created_at"];
					$groupid = $group["id"];
					$groupName = $group["group_name"];
					$groupDescription = $group["group_description"];
					$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $group["avatarblobid"] . '&amp;qa_size=100" class="qa-avatar-image" alt=""/>';
					$groupTags = $group["tags"];
					
                    //Get our formatted tags.
                    $taglist = getGroupTags($groupTags);
                    
                    //Start the wrapper.
                    $qa_content['custom'] .= getGroupListWrapper($wrapper);
					
					//Get the Group name.
					$qa_content['custom'] .= $groupAvatarHTML . getGroupListName($groupid, $groupName, $groupDescription);
                    //The group tags...
					$qa_content['custom'] .= $taglist;
					$qa_content['custom'] .= '<br>';

                    //End the wrapper.
                    $qa_content['custom'] .= endGroupListWrapper();
				}
			}
			
			
			return $qa_content;
		}
		
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
