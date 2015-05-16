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
 * Returns the colored wrapper background.
 */
function getWrapper($wrapper, $seenStatus){
    $seen = ($seenStatus == 0 ? '' : 'seen');
    return '<div class="friend-list-wrapper ' . $seen . ' ' . ($wrapper ? 'even' : 'odd') . '">';
}

function makeURL($url, $message){
    return '<a href="' . $url .'">' . $message . '</a>';
}


function makeChatURL($url, $message, $channel, $user){
    return '<a href="' . $url .'" class="chat-open" data-user="' . $user . '" data-channel="' . $channel .'">' . $message . '</a>';
}

function closeNotify($id){
    return '<a class="button button-chat close-button" href="./?qa=notifications&close='. $id . '">Dismiss</a>';
}

function silenceNotify($id){
    return '<a class="notify-silence-button" href="./?qa=notifications&silence='. $id . '">Read</a>';
}

/*
 * Close the Friend wrapper.
 */
function endWrapper(){
    return '</div>';
}

/*
	Omit PHP closing tag to help avoid accidental output
*/
