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
	class qa_group_view_post {
		
		var $directory;
		var $urltoroot;
		
		function load_module($directory, $urltoroot)
		{
			$this->directory=$directory;
			$this->urltoroot=$urltoroot;
		}
		
		function match_request($request)
		{
			if (qa_request_part(0)=='view-post')
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
			$postComments = getComments($postid);
			
			$groupid = $postContent['group_id'];
			$groupProfile = getGroupData($groupid);
			$currentUserIsMember = isUserGroupMember($userid, $groupid);
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
			
			if(isset($_POST["comment"]) && $currentUserIsMember) {
				createPost($groupid, $userid, '', $_POST["comment"], '', 'C', $postid);
				qa_redirect('view-post/'.$postid);
			}
			
			//If the user is not a member redirect to groups page.
			// If the DB returns an empty group array, redirect to groups page			
            if(!$currentUserIsMember || empty($groupProfile)){
				qa_redirect('groups');
            }
			
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
            
            //Check to see if we are the admin, and if we had any commands.
            if($currentUserIsAdmin){
                if(isset($_GET['lock'])){
                    //Lock the discussion.
                    makePostLocked($postContent['id']);
                    header('Location: ../view-post/' . $postContent['id']);
                }
                
                if(isset($_GET['sticky'])){
                    //Sticky the discussion.
                    makePostSticky($postContent['id']);
                    header('Location: ../view-post/' . $postContent['id']);
                }
                
                if(isset($_GET['unlock'])){
                    //Unlock the discussion.
                    makePostUnlocked($postContent['id']);
                    header('Location: ../view-post/' . $postContent['id']);
                }
                
                if(isset($_GET['unsticky'])){
                    //Unsticky the discussion.
                    makePostNotSticky($postContent['id']);
                    header('Location: ../view-post/' . $postContent['id']);
                }
                
                if(isset($_GET['delete'])){
                    //Delete the discussion.
                    deletePost($postContent['id']);
                    header('Location: ../group/' . $groupid['id']);
                }
            }
			
			
			$viewer=qa_load_viewer('', '');
            $postContent['title'] = $viewer->get_html($postContent['title'], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'],
                'linksnewwindow' => @$options['linksnewwindow'],
            ));
			$postContent['content'] = $viewer->get_html($postContent['content'], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'] = 1,
                'linksnewwindow' => @$options['linksnewwindow'] = 1,
            ));
			
			
			// Set the browser tab title for the page.
			$qa_content['title']= $postContent['title'];

            $heads = getJQueryUITabs('group-tabs');
			$qa_content['custom']= $heads;
			
			$qa_content['custom'] .=  '<div class="view-post-wrapper"><h3 class="group-list-header"><a href="../group/' . $groupProfile["id"] . '">' . $groupProfile["group_name"] . '</a> -> '.$postContent['title'].' </h3>';
            
		
			// Display Pinned, Locked icons if needed.
            $qa_content['custom'] .= '<div class="group-post-icons">';
            
            if(@$postContent['is_sticky'] == '1'){
                $qa_content['custom'] .= '<img src="../qa-plugin/groups/images/pin.png" />';
            }
            if(@$postContent['is_locked'] == '1'){
                $qa_content['custom'] .= '<img src="../qa-plugin/groups/images/padlock.png" />';
            }
            $qa_content['custom'] .= '</div></div>';
            
			
			// Display the root post
            $qa_content['custom'] .= '<div class="group-view-content">' . $postContent['content'] .  ' </div>';
            //Get the date this was posted at.
            $date = get_time(qa_when_to_html($postContent["posted_at"], @$options['fulldatedays']));
            
            $qa_content['custom'] .= '<div class="group-post-avatar-meta">';
			$qa_content['custom'] .= $date . ' by ' . displayGroupMember($postContent["handle"], $postContent["avatarblobid"]).'</div>';
            $qa_content['custom'] .= getGroupTags($postContent['tags']);
            
			//Provisions for Admin.
            if($currentUserIsAdmin){
                if(!isset($postContent['is_locked']) || @$postContent['is_locked'] == '0'){
                    $qa_content['custom'] .= '<a href="../view-post/'.$postid.'?lock" class="groups-btns groups-lock-btn">Lock Discussion</a>';
                }else{
                    $qa_content['custom'] .= '<a href="../view-post/'.$postid.'?unlock" class="groups-btns groups-lock-btn">Unlock Discussion</a>';
                }
                if(!isset($postContent['is_sticky']) || @$postContent['is_sticky'] == '0'){
                    $qa_content['custom'] .= '<a href="../view-post/'.$postid.'?sticky" class="groups-sticky-btn groups-btns">Sticky Discussion</a>';
                }else{
                    $qa_content['custom'] .= '<a href="../view-post/'.$postid.'?unsticky" class="groups-sticky-btn groups-btns">Unsticky Discussion</a>';
                }
                
                $qa_content['custom'] .= '<a href="?delete" class="groups-delete-btn groups-btns">Delete Thread</a>';
            }
			
			// Display post comments.
			foreach ($postComments as $comment) {
				$qa_content['custom'] .= '<br><br><div class="group-view-content">' . $comment['content'] . ' </div>';
				//Get the date this was posted at.
				$date = get_time(qa_when_to_html($comment["posted_at"], @$options['fulldatedays']));
				
				$qa_content['custom'] .= '<div class="group-post-avatar-meta">';
				$qa_content['custom'] .= $date . ' by ' . displayGroupMember($comment["handle"], $comment["avatarblobid"]). ' </div><br>';			
			}
			
			// Display the 'Leave a comment' section if the post is not locked.			
			if(@$postContent['is_locked'] == '0'){
				$qa_content['custom'] .= '<br><form method="post" enctype="multipart/form-data" action="../view-post/'.$postid.'/post-comment" id="form">';
				$qa_content['custom'] .= '<br><label for="postContent">Leave a comment: </label><br>';
				$qa_content['custom'] .= '<textarea required rows="10" cols="74" name="comment" form="form" maxlength="8000"></textarea><br>';
				$qa_content['custom'] .= '<input type="submit" class="qa-form-wide-button qa-form-wide-button-save" value="Post"/>';
				$qa_content['custom'] .= '</form>';	
			} 
			else {
				$qa_content['custom'] .= '<br><h3>This post is locked and is no longer taking comments.</h3>';
			}
			
			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
