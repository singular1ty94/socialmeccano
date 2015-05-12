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

	class qa_grouptag_page {
		
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
			if ($requestpart=='group-tag')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			if (isset($_POST['search'])) {
				$search = $_POST['search'];
			}
			else {
				$search = qa_request_part(1);
			}
			
			if (isset($search)) {
				$qa_content['title']= "Groups by Tag '" . $search . "'";
			} else {
				$qa_content['title']="Group Tags";
			}
			
			include './qa-include/app/posts.php';	
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$userid = qa_get_logged_in_userid();
            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			

            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= displayGroupListNavBar();
			$qa_content['custom'] .= '<a href="./group-create/" class="button button-creation qa-groups-button">Create Group</a>';
			
			
			$qa_content['custom'] .= '<form method="POST" action="group-tag" id="form">';
			$qa_content['custom'] .= '<input class="search-bar" required id="search" name="search" type="text" value="';
			if (isset($search)) {
				$qa_content['custom'] .= $search;
			}
			$qa_content['custom'] .= '" placeholder="Search by Tag..."/>';
			$qa_content['custom'] .= '<input class="search-button" type="submit">';
            $qa_content['custom'] .= '</form>';

			if (isset($search)) {
				$groupList = getGroupsByTag($search);
                //Even/odd wrapper color.
                $wrapper = true;
				$qa_content['raw'] = [];
                
				foreach ($groupList as $group) {
                    //Output to the raw JSON format.
                    $qa_content['raw'][$group["id"]] = $group;
                    
					$groupCreatedDate = $group["created_at"];
					$groupid = $group["id"];
					$groupName = qa_post_content_to_text($group["group_name"], 'html');
					$groupDescription = qa_post_content_to_text($group["group_description"], 'html');
					$groupAvatarHTML = '<img src="./?qa=image&amp;qa_blobid= ' . $group["avatarblobid"] . '&amp;qa_size=100" class="qa-avatar-image" alt=""/>';
					$groupTags = qa_post_content_to_text($group["tags"], 'html');
										
                    //Start the wrapper.
                    $qa_content['custom'] .= getGroupListWrapper($wrapper);
					
					//Get the Group name.
					$qa_content['custom'] .= $groupAvatarHTML . getGroupUnit($groupid, $groupName, $groupDescription, $groupTags);

					$qa_content['custom'] .= '<br>';

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
