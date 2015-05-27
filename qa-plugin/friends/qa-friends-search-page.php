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

	class qa_friend_search_page {
		
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
			if ($requestpart=='friend-search')
				return true;

			return false;
		}

		function process_request($request)
		{
			$qa_content=qa_content_prepare();

			$qa_content['title']="Find Friends";
			
			include 'qa-friends-db.php';
			include 'qa-friends-helper.php';
			
			$userid = qa_get_logged_in_userid();

            //If the user is not logged in redirect to main.		
            if(!isset($userid)){
               header('Location: ../');
            }			

            $heads = getJQueryUITabs('tabs');

			$qa_content['custom']= $heads;
			
            //Vex.
            $vex = getVex();
			$qa_content['custom'] .= $vex;			
			
			$qa_content['custom'] .= displayFriendListNavBar();


			$qa_content['custom'] .= '<form method="POST" action="" id="form">';
			$qa_content['custom'] .= '<input class="search-bar" required id="search" name="search" type="text" value="';
			if (isset($_POST['search'])) {
				$qa_content['custom'] .= $_POST['search'];
			}
			$qa_content['custom'] .= '" placeholder="Enter username here..."/>';
			$qa_content['custom'] .= '<input class="search-button" type="submit">';
            $qa_content['custom'] .= '</form>';

		
			if (isset($_POST['search'])) {
				$friendList = getUsersByHandle($_POST['search']);
                //Even/odd wrapper color.
                $wrapper = true;

				foreach ($friendList as $friend) {
				
					if ($friend["avatarblobid"] == null) {
						$friend["avatarblobid"] = qa_opt('avatar_default_blobid');
					}
				
                    //Start the wrapper.
                    $qa_content['custom'] .= getFriendWrapper($wrapper);

					$qa_content['custom'] .= '<a href="/user/' . $friend["handle"] . '"><img src="./?qa=image&amp;qa_blobid= ' . $friend["avatarblobid"]. '&amp;qa_size=80" class="qa-avatar-image" alt=""/></a>';

					$qa_content['custom'] .= getFriendUnit($friend["userid"], $friend["handle"]);

					$qa_content['custom'] .= '<br>';

                    //End the wrapper.
                    $qa_content['custom'] .= endFriendWrapper();
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
