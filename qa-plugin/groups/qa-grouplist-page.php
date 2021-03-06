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

			$qa_content['title']="My Groups";

			include './qa-include/app/posts.php';
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			
			$userid = qa_get_logged_in_userid();
            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }			

			$groupList = getMyGroups($userid);

            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= displayGroupListNavBar();
			$qa_content['custom'] .= '<a href="./group-create/" class="qa-groups-button button button-creation">Create Group</a></div>';
			
			if (empty($groupList)) {
				$qa_content['custom'] .= '<br />There\'s nothing here!<br />You can help the community grow by <a href="./group-create/">creating your own group.</a>';
			}
			else {
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
					$qa_content['custom'] .= $groupAvatarHTML;
				
					$qa_content['custom'] .= getGroupUnit($groupid, $groupName, $groupDescription, $groupTags);
					
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
