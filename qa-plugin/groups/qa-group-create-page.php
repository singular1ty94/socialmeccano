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
	class qa_group_create_page {
		
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
			if ($requestpart=='group-create')
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
			
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
			
			// Set the browser tab title for the page.
			$qa_content['title']= 'Create Group';

            //Did we already submit?
            if(@$_POST["groupName"] == ""){
                //Output form.
                $qa_content['custom'] = '<form method="post" enctype="multipart/form-data" action="group-create" id="form">';
                $qa_content['custom'] .= '<label for="groupName">Group Name: </label>';
                $qa_content['custom'] .= '<input required id="groupName" name="groupName" type="text" maxlength="64" size="60"/><br/>';
                $qa_content['custom'] .= '<label for="groupDescr">Group Description: </label><br>';
                //$qa_content['custom'] .= '<input required id="groupDescr" name="groupDescr" type="text" /><br/>';
				$qa_content['custom'] .= '<textarea required rows="3" cols="70" name="groupDescr" form="form" maxlength="200" placeholder="Type a brief description here..."></textarea><br>';			
                $qa_content['custom'] .= '<label for="groupInfo">Group Info: </label><br>';
				$qa_content['custom'] .= '<textarea required rows="5" cols="70" name="groupInfo" form="form" maxlength="8000" placeholder="Type important group information here..."></textarea><br>';	
                //$qa_content['custom'] .= '<input required id="groupInfo" name="groupInfo" type="text" /><br/>';                
				$qa_content['custom'] .= '<label for="groupLocation">Group Location: </label>';
                $qa_content['custom'] .= '<input id="groupLocation" name="groupLocation" type="text" maxlength="50" size="58"/><br/>';
                $qa_content['custom'] .= '<label for="groupWebsite">Group Website: </label>';
                $qa_content['custom'] .= '<input id="groupWebsite" name="groupWebsite" type="text" maxlength="100"/ size="58"><br/>';
                $qa_content['custom'] .= '<label for="groupTags">Group Tags: </label>';
                $qa_content['custom'] .= '<input required id="groupTags" name="groupTags" type="text" maxlength="100" size="32"/><br />';				
                $qa_content['custom'] .= '<label for="avatar">Group Image: </label>';
                $qa_content['custom'] .= '<input required id="avatar" name="avatar" type="file" /><br />';
				$qa_content['custom'] .= '<label for="privacy_setting">Privacy Setting: </label>';
				$qa_content['custom'] .= '<select name="privacy_setting" form="form">';
                $qa_content['custom'] .= '<option value="O">Open - Anyone can find and join your group.</option>';
				$qa_content['custom'] .= '<option value="C">Closed - Anyone can find your group, members must be approved.</option>';
				$qa_content['custom'] .= '<option value="S">Secret - No one can find or join your group unless invited.</option>';
				$qa_content['custom'] .= '</select><br />';

                $qa_content['custom'] .= '<input type="submit" class="qa-form-wide-button qa-form-wide-button-save" value="Create Group"/>';
                $qa_content['custom'] .= '</form>';	
            }else{
                $blobId = '18056448301737554770';                
                
                //Black magic below. Don't touch.
                if(isset($_FILES['avatar'])){
                    require_once QA_INCLUDE_DIR.'util/image.php';
                
                    $imagedata=qa_image_constrain_data(file_get_contents($_FILES['avatar']['tmp_name']), $width, $height, qa_opt('avatar_store_size'));

                    require_once QA_INCLUDE_DIR.'app/blobs.php';

                    $blobId = qa_create_blob($imagedata, 'jpeg', null, $userid, null, qa_remote_ip_address());
                }
                //Make the group.
				require_once QA_INCLUDE_DIR.'./app/format.php';	
				require_once QA_INCLUDE_DIR.'./app/posts.php';

				$tags = qa_string_to_words($_POST['groupTags'], $tolowercase=true, $delimiters=false, $splitideographs=true, $splithyphens=false);
				$tags = qa_post_tags_to_tagstring($tags);

				
                $groupid = createNewGroup($_POST['groupName'], $_POST['groupDescr'], $blobId, $_POST['groupLocation'], $_POST['groupWebsite'], $_POST['groupInfo'], $tags, $userid, $_POST['privacy_setting']);
                
                //Add generic announcement
                createPost($groupid, $userid, 'Welcome to ' . $_POST['groupName'], 'Welcome to your new group.', 'admin', 'A');
                //Redirect.
                header('Location: ../group/' . $groupid);
            }
			

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
