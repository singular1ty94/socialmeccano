<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: /qa-group-helper.php
	Description: Helper functions for outputting HTML wrappers.


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/

/*
 * Generates the header link for jQueryUI tabs.
 */
function getJQueryUITabs($id){
    return '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"><script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script><script>$(function(){$("#' . $id . '").tabs();});</script>';
}

/*
 * Left-Pane Header and Table Head.
 */
function getSidePane(){
    return '<div class="group-sidepane"><table class="qa-form-wide-table">';
}

/*
 * Makes a new row for the side pane,
 * with the provided data and custom data-class
 * for the span.
 */
function makeSidePaneFieldWithLabel($data, $dataClass, $label, $labelClass){
    //Check that there is actually a value here.
    return '<tr><td class="qa-form-wide-data"><span class="' . $dataClass . '">' . $data . '</span><span class="'. $labelClass .'">' . $label . '</span></td></tr>';
}

/*
 * Makes a new row for the side pane,
 * with the provided data.
 */
function makeSidePaneField($data, $dataClass){
    if(strlen($data) > 0){
        return '<tr><td class="qa-form-wide-data"><span class="' . $dataClass . '">' . $data . '</span></td></tr>';
    }
}

/*
 * Raw output.
 */
function makeSidePaneRaw($html){
    return '<tr><td class="qa-form-wide-data">' . $html . '</td></tr>';
}

/*
 * Closes the side pane.
 */
function endSidePane($currentUserIsMember, $groupName){
	$endSidePane = '</table>';
	if ($currentUserIsMember) {
		 $endSidePane .= '<div id="group-chat" 
         class="button button-creation chat-open"
         data-user="' . qa_get_logged_in_user_field('handle') .  '" 
         data-channel="' . preg_replace('/\s+/', '', $groupName) . '">Group Chat</div>';
        $endSidePane .= '<br><a href="#" id="leave-group-btn" class="button button-negative groups-delete-btn">Leave Group</a>';
	}
	$endSidePane .= '</div>';
    return $endSidePane;
}


/*
 * Make the group page header.
 */
function getGroupHeader($groupid, $groupName, $currentUserIsMember){
	$groupHeader = '<div class="group-header-large"><table class="qa-form-wide-table"><tbody><tr><td class="group-name">' . $groupName;
	//if not a member display a button to join the group
	if (!$currentUserIsMember) {
			$groupHeader .= '<a href="./'. $groupid .'?join_group" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">+Join Group</a>'; 
	}
    $groupHeader .= '</td></tr></table></div><br/>';
	return $groupHeader;
}

/*
 * Make a complete group unit for the group list.
 */
function getGroupUnit($id, $groupName, $groupDescr, $taglist){
    $html = '';
    //Add the wrapper.
    $html .= '<div class="group-unit">';
    
    //Add the group header, name and description.
    $html .= getGroupListName($id, $groupName, $groupDescr);
    
	if (!empty($taglist)) {
		//Now add the tags.
		$html .= getGroupTags($taglist);
    }
    //Now end the wrapper.
    $html .= '</div>';
    return $html;
    
}

/*
 * Make tags for Group List.
 */
function getGroupTags($tags){
    //Format the tags.
    $arr = explode(',', $tags); //Get a new array
    
    //Header of the tag list.
    $taglist = '<ul class="qa-group-tag-list">';
    foreach($arr as $tag){
        $taglist .= '<li><a href="../../?qa=group-tag/' . trim($tag) . '" class="qa-tag-link">' . trim($tag) . '</a></li>';
    }
    $taglist .= '</ul>';
    
    return $taglist;
}

/*
 * Returns the colored wrapper background.
 */
function getGroupListWrapper($wrapper){
    return '<div class="group-list-wrapper ' . ($wrapper ? 'even' : 'odd') . '">';
}

function displayGroupListNavBar(){
	$html = '<div class="qa-nav-main">';
	$html .= '<ul class="qa-nav-main-list">';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=groups" class="qa-nav-main-link">My Groups</a></li>';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=group-tag" class="qa-nav-main-link">Groups by Tag</a></li>';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=group-search" class="qa-nav-main-link">Group Search</a></li>';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=group-invites" class="qa-nav-main-link">Group Invitations</a></li>';
	$html .= '</ul><div class="qa-nav-main-clear"></div></div>';
	return $html;
}

/*
 * Returns the group's name for the grouplist.
 */
function getGroupListName($id, $groupName, $groupDescr){
    return '<h3 class="group-list-header"><a href="./group/' . $id . '">' . $groupName . '</a></h3><span class="group-description">'. $groupDescr . '</span><br/>';
}


/*
 * Displays an individual user profile on the members tab of a group.
 */
function displayGroupListMember($userName, $avatarid) {
    $html = '<span class="group-member-list">';
    
	$html .= '<a href="/user/' . $userName . '"><img src="./?qa=image&amp;qa_blobid= ' . $avatarid . '&amp;qa_size=50" class="qa-avatar-image" alt=""/></a>';
    
    $html .= '<a href="/user/' . $userName . '">' . $userName . '</a></span>';
    return $html;
}

/*
 * Displays an individual user profile next to a post.
 */
function displayGroupMember($userName, $avatarid) {
	$avatarHTML = '<a href="/user/' . $userName . '"><img src="./?qa=image&amp;qa_blobid= ' . $avatarid . '&amp;qa_size=50" class="qa-avatar-image" alt=""/></a>';
	return ('<a href="/user/' . $userName . '">' . $userName . '</a>   ' . $avatarHTML);
}

//Return formatted time stamp.["prefix"]=> string(0) "" ["data"]=> string(8) "45 years" ["suffix"]=> string(4) " ago" 
function get_time($arr){
	//var_dump($arr);
    return '<span>' . $arr['data'] .$arr['suffix']  . '</span>';
}

/*
 * Displays formatted Announcement or Discussion box.
 */
function displayGroupPosts($postArray, $wrapper = true) {
	$html = '';
	foreach ($postArray as $post) {
        if(@$post['is_sticky'] == '0'){
            $date = get_time(qa_when_to_html($post["posted_at"], @$options['fulldatedays']));
            $postRepliesCount = getCommentCount($post["id"])["COUNT(id)"];

            $viewer=qa_load_viewer('', '');
            $post["title"] = $viewer->get_html($post["title"], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'] = 1,
                'linksnewwindow' => @$options['linksnewwindow'] = 1,
            ));
            $post["content"] = $viewer->get_html($post["content"], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'] = 1,
                'linksnewwindow' => @$options['linksnewwindow'] = 1,
            ));

            //Sanity checks.
            if(strlen($post["content"]) > 350){
                $post["content"] = substr($post["content"], 0, 345) . '...';
            }

            if(strlen($post["title"]) > 80){
                $post["title"] = substr($post["title"], 0, 75) . '...';
            }


            $html .= '<div class="group-post '. ($wrapper ? 'even' : 'odd'). '">'; //Start with the div.
            //Next, we have the header
            if(@$post['is_locked'] == '1'){
                $html .= '<img class="group-locked" src="../qa-plugin/groups/images/tiny_padlock.png" />';
            }
            $html .= '<a href="../view-post/' . $post["id"] . '"><h6 class="group-post-header">' . $post["title"]  . "</h6>"  . '</a>';
            //Now the content.
            $html .= '<span class="group-post-content">' . $post["content"] . ' (Replies: ' . $postRepliesCount . ')' . "</span>";		
            //And the avatar box.	
            $html .= '<span class="group-post-meta">' . $date . ' by ' . displayGroupMember($post["handle"], $post["avatarblobid"]) . "</span>";
            //End.
            $html .= '</div>';
            //Return.
            $wrapper = !$wrapper;
        }
	
	}
	return $html;	
}

/*
 * Displays STICKIED discussionsor annoucements.
 */
function displayStickyPosts($postArray, $wrapper = true) {
	$html = '';
	foreach ($postArray as $post) {
        if(@$post['is_sticky'] == '1'){
            $date = get_time(qa_when_to_html($post["posted_at"], @$options['fulldatedays']));
            $postRepliesCount = getCommentCount($post["id"])["COUNT(id)"];

            $viewer=qa_load_viewer('', '');
            $post["title"] = $viewer->get_html($post["title"], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'] = 1,
                'linksnewwindow' => @$options['linksnewwindow'] = 1,
            ));
            $post["content"] = $viewer->get_html($post["content"], '', array(
                'blockwordspreg' => @$options['blockwordspreg'],
                'showurllinks' => @$options['showurllinks'] = 1,
                'linksnewwindow' => @$options['linksnewwindow'] = 1,
            ));


            $html .= '<div class="group-post '. ($wrapper ? 'even' : 'odd'). '">'; //Start with the div.
            //Next, we have the header.
            if(@$post['is_locked'] == '1'){
                $html .= '<img class="group-locked" src="../qa-plugin/groups/images/tiny_padlock.png" />';
            }
            $html .= '<a href="../view-post/' . $post["id"] . '"><h6 class="group-post-header">' . $post["title"]  . "</h6>"  . '</a>';
            //Now the content.
            $html .= '<span class="group-post-content">' . $post["content"] . ' (Replies: ' . $postRepliesCount . ')' . "</span>";		
            //And the avatar box.	
            $html .= '<span class="group-post-meta">' . $date . ' by ' . displayGroupMember($post["handle"], $post["avatarblobid"]) . "</span>";
            //End.
            $html .= '</div>';
            //Return.
            $wrapper = !$wrapper;
        }
	
	}
	return $html;	
}

/*
** A utility function to check if we have any pinned posts.
*/
function hasPinned($postArray){
    foreach ($postArray as $post) {
        if(@$post['is_sticky'] == '1'){
            return true;
        }
    }
    return false;
}


/*
 * Close the group wrapper.
 */
function endGroupListWrapper(){
    return '</div>';
}
 
function getVex(){
    return "<script src='../qa-theme/Carbon/js/vex.combined.min.js'></script>
    <script>vex.defaultOptions.className = 'vex-theme-plain';</script>
    <link rel='stylesheet' href='../qa-theme/Carbon/vex.css' />
    <link rel='stylesheet' href='../qa-theme/Carbon/vex-theme-plain.css' />";
}


/*
	Omit PHP closing tag to help avoid accidental output
*/
