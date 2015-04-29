<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: /qa-friend-helper.php
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
    return '<tr><td class="qa-form-wide-data"><span class="' . $dataClass . '">' . $data . '</span><span class="'. $labelClass .'">' . $label . '</span></td></tr>';
}

/*
 * Closes the side pane.
 */
function endSidePane(){
    return '</table></div>';
}


/*
 * Make the Friend page header.
 */
function getFriendHeader($FriendName){
    return '<div class="group-header-large"><table class="qa-form-wide-table"><tbody><tr><td class="group-name">' . $FriendName . '</td></tr></table></div><br/>';
}

/*
 * Make a complete group unit for the group list.
 */
function getFriendUnit($id, $friendName){
    $html = '';
    //Add the wrapper.
    $html .= '<div class="group-unit">';
    
    //Add the group header, name and description.
    $html .= getFriendName($id, $friendName); //, $friendDescr
	
 	//$html .= '<a href="#" id="delete-btn" class="qa-form-wide-button qa-form-wide-button-save qa-groups-button">Unfriend</a>';

    //Now end the wrapper.
    $html .= '</div>';
    return $html;
    
}


function displayFriendListNavBar(){
	$html = '<div class="qa-nav-main">';
	$html .= '<ul class="qa-nav-main-list">';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=friends" class="qa-nav-main-link">My Friends</a></li>';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=friend-search" class="qa-nav-main-link">Find Friends</a></li>';
	$html .= '<li class="qa-nav-main-item qa-nav-main-custom1">	<a href="./?qa=friend-requests" class="qa-nav-main-link">Friend Requests</a></li>';

	$html .= '</ul><div class="qa-nav-main-clear"></div></div>';
	return $html;
}


/*
 * Returns the colored wrapper background.
 */
function getFriendWrapper($wrapper){
    return '<div class="group-list-wrapper ' . ($wrapper ? 'even' : 'odd') . '">';
}

/*
 * Returns the Friend's name for the friend list.
 */
function getFriendName($id, $friendName){ // , $friendDescr
    return '<h3 class="group-list-header"><a href="./user/' . $friendName . '">' . $friendName . '</a></h3>';//<span class="group-description">'. $friendDescr . '</span><br/>';
}

/*
 * Close the Friend wrapper.
 */
function endFriendWrapper(){
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