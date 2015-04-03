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
			
			// TWO options here, get a list of all groups, or get a list of MY groups. (maybe we could do tabs?)
			$groupList = getAllGroups();
			 //$groupList = getMyGroups(1);  //TODO, get current user_id, substitute.
			
            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= '<h2>Group List</h2>';
			
			
			if (empty($groupList)) {
				$groupid = createNewGroup('Test Group Pls Ignore', 'This is our test group', 14306193309127865143, 'This is our group information', 'test,PHP', 1);
				$qa_content['custom'] .= 'No Groups Found! <br> I just made one for you to test with with id#' . $groupid;
				$qa_content['custom'] .= '<br><a href="./group/' . $groupid . '">Take me there...</a>';
				
			}
			else {
                //Even/odd wrapper color.
                $wrapper = true;
                
				foreach ($groupList as $group) {
                    //Get our formatted tags.
                    $taglist = getGroupTags($group["tags"]);
                    
                    //Start the wrapper.
                    $qa_content['custom'] .= getGroupListWrapper($wrapper);
                    
					//Get the Group name.
					$qa_content['custom'] .= getGroupListName($group["id"], $group["group_name"], $group["group_description"]);
                    //The group tags...
					$qa_content['custom'] .= $taglist;
					$qa_content['custom'] .= '<br>'. $group["avatarblobid"]; //obviously we want the actual image, not the id. TODO
                    
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
