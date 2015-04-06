<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/groups/qa-group-create-page.php
	Description: Create a group.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/
	class qa_group_edit_post {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			if (qa_request_part(0)=='edit-post')
				return true;

			return false;
		}

		function process_request($request)
		{
			//Includes.
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
            
			$userid = qa_get_logged_in_userid();
			//If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }
			
			$postid = intval(qa_request_part(1));
			$postContent = getPost($postid);
			$groupid = $postContent['group_id'];

			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
			$groupProfile = getGroupData($groupid);

	
			//If the user is not a member redirect to groups page.
			// If the DB returns an empty group array, redirect to groups page			
            if(!$currentUserIsMember || empty($groupProfile)){
				qa_redirect('groups');
            }
			
			// If the user didn't create this post OR they aren't an admin, redirect back to the post.
			if ($postContent['user_id'] != $userid || !$currentUserIsAdmin) {
				qa_redirect('view-post/'.$postid);			
			}

			

			
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
			
			// Set the browser tab title for the page.
			$qa_content['title']= 'Edit Post';

			
            $heads = getJQueryUITabs('group-tabs');
			$qa_content['custom']= $heads;
			
			$qa_content['custom'] .=  '<h3 class="group-list-header"><a href="../group/' . $groupProfile["id"] . '">' . $groupProfile["group_name"] . '</a> -> '.$postContent['title'].' </h3>';
			

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
