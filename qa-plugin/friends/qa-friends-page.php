<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/friends/qa-friends-page.php
	Description: Displays a list of friends.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/

	class qa_friends_page {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			if ($request=='friends')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']=qa_lang('friends/friends_list_title');
			
			include 'qa-friends-db.php';
			include 'qa-friends-helper.php';
			
			
			$friendsList = getMyFriends(1); //TODO get current user.

			
            $heads = getJQueryUITabs('tabs');
			
			$qa_content['custom']= $heads;
			$qa_content['custom'] .= '<h2>Friends List</h2>';
			
			
			if (empty($friendsList)) {
				$qa_content['custom'] .= 'You Dont Have Any Friends. Nobody Likes You!';
				
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
