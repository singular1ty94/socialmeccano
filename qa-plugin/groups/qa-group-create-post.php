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
	class qa_group_create_post {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			if (qa_request_part(0)=='create-post')
				return true;

			return false;
		}

		function process_request($request)
		{
			//Includes.
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
			include '.\qa-include\plugins\qa-viewer-basic.php';
            
			$userid = qa_get_logged_in_userid();
			//If the user is not logged in redirect to main.		
            if(!isset($userid)){
                header('Location: ../');
            }

			$groupid = intval(qa_request_part(1));
			$groupProfile = getGroupData($groupid);
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
			
	
		

			
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
			
			// Set the browser tab title for the page.
			$qa_content['title']= 'Create Post';
			
			$qa_content['custom'] =  '<h3 class="group-list-header"><a href="../group/' . $groupProfile["id"] . '">' . $groupProfile["group_name"] . '</a> -> Create Post </h3>';
            
            //Let's generate a nice set of fields.
            //Did we already submit?
            if(!isset($_POST["postTitle"])){
			
				//If the user is not a member redirect to groups page.
				// If the DB returns an empty group array, redirect to groups page		
				if(!$currentUserIsMember || empty($groupProfile)){
					qa_redirect('groups');
				}
                if(isset($_GET["type"])){
                    //Output a form.
                    $qa_content['custom'] = '<form method="post" action="create-post/?type=d&g_id=' . $groupid. '" id="form">';
                    $qa_content['custom'] .= '<label for="postTitle">Post Title: </label>';
                    $qa_content['custom'] .= '<input required id="postTitle" name="postTitle" type="text"/><br/>';		
                   $qa_content['custom'] .= '<label for="postContent">Content: </label><br>';
                    $qa_content['custom'] .= '<textarea required rows="4" cols="50" name="postContent" form="form"></textarea><br>';	

                    $qa_content['custom'] .= '<label for="postTags">Post Tags: </label>';
                    $qa_content['custom'] .= '<input required id="postTags" name="postTags" type="text"/><br/>';

                    $qa_content['custom'] .= '<input type="submit" class="qa-form-wide-button qa-form-wide-button-save" value="Post"/>';
                    $qa_content['custom'] .= '</form>';	
                }else{
                   header('Location: ../group/' . $groupid);
                }
            }else{
                //Must've submitted already. Submit to the database.
                if($_GET["type"] === 'D' || 
                   $_GET["type"] === 'd'){
				   
				   // Double check that the user is a member of the group
				   	if (isUserGroupMember($userid, $groupid = $_GET["g_id"])) {
						createPost($_GET["g_id"], $userid, $_POST["postTitle"], $_POST["postContent"], $_POST["postTags"], $_GET["type"], 0);
                    }
                    header('Location: ../../group/' . $_GET["g_id"]);
                }else{
                    header('Location: ../../group/' . $_GET["g_id"]);
                }   
                    
            }

            return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
