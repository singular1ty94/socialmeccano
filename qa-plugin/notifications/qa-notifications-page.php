<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-plugin/notifications/qa-notifications-db.php
	Description: Displays a list of notifications.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.


*/

	class qa_notifications_page {

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
			if ($requestpart=='notifications')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']="My Notifications";

			include_once 'qa-notifications-db.php';
			include 'qa-notifications-helper.php';

			$userid = qa_get_logged_in_userid();

            //If the user is not logged in redirect to main.
            if(!isset($userid)){
               header('Location: ../');
            }

            $qa_content['custom'] ='';

            if(isset($_GET["close"])){
                removeNotification($_GET["close"]);
            }

            $notifies = getMyNotifications(qa_get_logged_in_userid());

			if (empty($notifies)) {
				$qa_content['custom'] = "<br>You're all up-to-date!";
			}
			else {
                $wrapper = true;
				foreach ($notifies as $notify) {
                    //Set them all to read.
                    if($notify["seen"] == 0){
                        makeNotificationSeen($notify["id"]);
                    }

                    switch($notify["type"]){
                        case "FriendOffer":
                            $friendHandle = getUser($notify["target_id"]);
							if ($friendHandle["avatarblobid"] == null) {
								$friendHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $friendHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=friend-requests', $friendHandle["handle"] . ' wants to be your friend!');
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "FriendAccept":
                            $friendHandle = getUser($notify["target_id"]);
							if ($friendHandle["avatarblobid"] == null) {
								$friendHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $friendHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=user/' . $friendHandle["handle"], $friendHandle["handle"] . ' accepted your friend request!');

                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "NewGroupUser":
                            $userHandle = getUser($notify["target_id"]);
							if ($userHandle["avatarblobid"] == null) {
								$userHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}								
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $userHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=user/' . $userHandle["handle"], $userHandle["handle"] . ' has joined your group, ' . $notify["info1"]);

                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "NewGroupUserRequest":
                            $userHandle = getUser($notify["target_id"]);
							if ($userHandle["avatarblobid"] == null) {
								$userHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $userHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=user/' . $userHandle["handle"], $userHandle["handle"] . ' has requested to join your group, ' . $notify["info1"]);

                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "NewGroupInvite":
                            $userHandle = getUser($notify["target_id"]);
							if ($userHandle["avatarblobid"] == null) {
								$userHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $userHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=user/' . $userHandle["handle"], $userHandle["handle"] . ' has invited you to join ' . $notify["info1"]);
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "NewGroupInviteApproval":
                            $userHandle = getUser($notify["target_id"]);
							if ($userHandle["avatarblobid"] == null) {
								$userHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $userHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./?qa=user/' . $userHandle["handle"], $userHandle["handle"] . ' has approved your request to join ' . $notify["info1"]);
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;								
                        case "NewGroupPost":
                            $userHandle = getUser($notify["target_id"]);
							if ($userHandle["avatarblobid"] == null) {
								$userHandle["avatarblobid"] = qa_opt('avatar_default_blobid');
							}							
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= '<img src="./?qa=image&amp;qa_blobid= ' . $userHandle["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/>';
                            $qa_content['custom'] .= makeURL('./group/' . $notify["info1"], $userHandle["handle"] . ' has made a new post in your group, ' . $notify["info2"]);

                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "ChatMessages":
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= makeChatURL('#', 'Click to read your new chat messages!', $notify["info1"], $notify["info2"]);
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "NewBadge":
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= makeURL('./index.php?qa=user', 'You earned the ' .$notify["info1"] . ' badge! Click to view all your badges.' , $notify["info1"]);
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                        case "PostComment":
                            $seenStatus = $notify["seen"];
                            $qa_content['custom'] .= getWrapper($wrapper, $seenStatus);
                            $qa_content['custom'] .= makeURL($notify["info1"], $notify["info2"] . ' has left a comment on your group post. Click to view.');
                            $qa_content['custom'] .= closeNotify($notify["id"]);
                            $qa_content['custom'] .= endWrapper();
                            break;
                    }

                    $wrapper = !$wrapper;
                }
			}


			return $qa_content;
		}

	};


/*
	Omit PHP closing tag to help avoid accidental output
*/
