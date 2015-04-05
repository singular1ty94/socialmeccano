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
	class qa_group_update_page {
		
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
			if ($requestpart=='group-update')
				return true;

			return false;
		}

		function process_request($request)
		{
			//Includes.
			include 'qa-group-db.php';
			include 'qa-group-helper.php';
            
			// Get the group id from the page request, redirect to groups if no group is set.
			$groupid = qa_request_part(1);
			if (!strlen($groupid)) {
				qa_redirect(isset($groupid) ? '/group-update/'.$groupid : 'groups');
			}
			
			$userid = qa_get_logged_in_userid();
			$currentUserIsAdmin = isUserGroupAdmin($userid, $groupid);
			
			// If user isn't an admin of the group, leave the page as they aren't allowed to update the profile.
            //if(!$currentUserIsAdmin){
            //    header('Location: ../?qa=groups');
            //}
			
			$groupProfile = getGroupData($groupid);
			// If the DB returns an empty array, group not found, so redirect to groups page
			//if (empty($groupProfile)) {
			//	qa_redirect('groups');
			//}
			
		
			// UI Generation below this.
			
			$qa_content=qa_content_prepare();
			
			// Set the browser tab title for the page.
			$qa_content['title']= 'Update Group';

            //Did we already submit?
            if(@$_POST["groupName"] == ""){
                //Output form.
                $qa_content['custom'] = '<form method="post" enctype="multipart/form-data" action="" id="form">';
                $qa_content['custom'] .= '<label for="groupName">Group Name: </label>';
                $qa_content['custom'] .= '<input required id="groupName" name="groupName" type="text" value="'. $groupProfile["group_name"] .'"/><br/>';
                $qa_content['custom'] .= '<label for="groupDescr">Group Description: </label><br>';
                //$qa_content['custom'] .= '<input required id="groupDescr" name="groupDescr" type="text" /><br/>';
				$qa_content['custom'] .= '<textarea required rows="4" cols="50" name="groupDescr" form="form">'. $groupProfile["group_description"] .'</textarea><br>';			
               $qa_content['custom'] .= '<label for="groupInfo">Group Info: </label><br>';
				$qa_content['custom'] .= '<textarea required rows="4" cols="50" name="groupInfo" form="form">'. $groupProfile["group_information"] .'</textarea><br>';	
                //$qa_content['custom'] .= '<input required id="groupInfo" name="groupInfo" type="text" /><br/>';                
				$qa_content['custom'] .= '<label for="groupLocation">Group Location: </label>';
                $qa_content['custom'] .= '<input id="groupLocation" name="groupLocation" type="text" value="'. $groupProfile["group_location"] .'"/><br/>';
                $qa_content['custom'] .= '<label for="groupWebsite">Group Website: </label>';
                $qa_content['custom'] .= '<input id="groupWebsite" name="groupWebsite" type="text" value="'. $groupProfile["group_website"] .'"/><br/>';
                $qa_content['custom'] .= '<label for="groupTags">Group Tags: </label>';
                $qa_content['custom'] .= '<input required id="groupTags" name="groupTags" type="text" value="'. $groupProfile["tags"] .'"/><br />';				
                $qa_content['custom'] .= '<label for="avatar">Group Image: </label>';
                $qa_content['custom'] .= '<input id="avatar" name="avatar" type="file" /><br />';

                $qa_content['custom'] .= '<input type="submit" class="qa-form-wide-button qa-form-wide-button-save" value="Update Group"/>';
                $qa_content['custom'] .= '</form>';	
            }else{

				
				if (empty($_FILES['avatar']['name'])) {
					updateGroupProfileNoBlob($groupid, $_POST['groupName'], $_POST['groupDescr'], $_POST['groupLocation'], $_POST['groupWebsite'], $_POST['groupInfo'], $_POST['groupTags']);
				}
				else {
					//Black magic below. Don't touch.
					require_once QA_INCLUDE_DIR.'util/image.php';
					
					$imagedata=qa_image_constrain_data(file_get_contents($_FILES['avatar']['tmp_name']), $width, $height, qa_opt('avatar_store_size'));

					require_once QA_INCLUDE_DIR.'app/blobs.php';

					$blobId = qa_create_blob($imagedata, 'jpeg', null, $userid, null, qa_remote_ip_address());
						
					updateGroupProfile($groupid, $_POST['groupName'], $_POST['groupDescr'], $blobId, $_POST['groupLocation'], $_POST['groupWebsite'], $_POST['groupInfo'], $_POST['groupTags']);
				}
				
                header('Location: ../group/' . $groupid);
            }
			

			return $qa_content;
		}
	
	};
	

/*
	Omit PHP closing tag to help avoid accidental output
*/
