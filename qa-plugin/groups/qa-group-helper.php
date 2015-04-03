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
    return '<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css"><script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script><link rel="stylesheet" href="/resources/demos/style.css"><script>$(function(){$("#' . $id . '").tabs();});</script>';
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
    return '<tr><td class="qa-form-wide-data"><span class="' . $dataClass . '">' . $data . '</span><span class="'. $labelClass .'">' . $label . '</span></td></tr>';
}

/*
 * Closes the side pane.
 */
function endSidePane(){
    return '</table></div>';
}


/*
 * Make the group page header.
 */
function getGroupHeader($groupName){
    return '<div class="group-header-large"><table class="qa-form-wide-table"><tbody><tr><td class="group-name">' . $groupName . '</td></tr></table></div><br/>';
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
        $taglist .= '<li><a href="./?qa=tag/' . $tag . '" class="qa-tag-link">' . $tag . '</a></li>';
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

/*
 * Returns the group's name for the grouplist.
 */
function getGroupListName($id, $groupName, $groupDescr){
    return '<h3 class="group-list-header"><a href="./group/' . $id . '">' . $groupName . '</a></h3><span class="group-description">'. $groupDescr . '</span><br/>';
}


/*
 * Displays an individual user profile on the members tab of a group.
 */
function displayGroupMember($userName, $avatarid) {
	$avatarHTML = '<a href="/user/' . $userName . '"><img src="./?qa=image&amp;qa_blobid= ' . $avatarid . '&amp;qa_size=50" class="qa-avatar-image" alt=""/></a>';
	return ($avatarHTML . '<br><a href="/user/' . $userName . '">' . $userName . '</a><br>');
}

/*
 * Close the group wrapper.
 */
function endGroupListWrapper(){
    return '</div>';
}


/*
	Omit PHP closing tag to help avoid accidental output
*/