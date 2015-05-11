<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-include/social-meccano-install.php
	Description: Responsible for initializing all standard Social Meccano
                 database features as part of the base install.

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
*/


//---------- NOTIFICATIONS ------------//
// Create notifications table.
qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^notifications ('.
        'id INT(11) NOT NULL AUTO_INCREMENT,'.
        'time DATETIME NOT NULL,'.
        'user_id INT(11) NOT NULL,'.
        'type VARCHAR (50) DEFAULT \'\','.
        'target_id INT(11) NOT NULL,'.
        'info1 VARCHAR(100) DEFAULT \'\','.
        'info2 VARCHAR(100) DEFAULT \'\','.
        'seen INT(1) DEFAULT 0,'.
        'actioned INT(1) DEFAULT 0,'.
        'PRIMARY KEY (id)'.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);

//---------- GROUPS  ------------//
// Create groups table.
qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^groups ('.
        'created_at DATETIME NOT NULL,'.
        'id INT(11) NOT NULL AUTO_INCREMENT,'.
        'group_name VARCHAR (64) NOT NULL,'.
        'group_description VARCHAR (200) DEFAULT \'\','.
        'avatarblobid bigint(20) unsigned DEFAULT NULL,'.
        'group_information VARCHAR(8000) DEFAULT \'\','.
        'tags VARCHAR(100) DEFAULT NULL,'.
        'group_location VARCHAR (50) DEFAULT \'\','.
        'group_website VARCHAR (100) DEFAULT \'\','.
        'created_by INT(11) NOT NULL,'.
        'privacy_setting enum("S", "C", "O") DEFAULT \'O\','.
        'PRIMARY KEY (id)'.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);

// Create group member table
qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^group_members ('.
        'joined_at DATETIME NOT NULL,'.
        'group_id INT(11) NOT NULL,'.
        'user_id INT(11) NOT NULL,'.
        'is_admin INT(1) NOT NULL DEFAULT \'0\''.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);


// Create group invite/request table
qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^group_requests ('.
        'sent_at DATETIME NOT NULL,'.
        'group_id INT(11) NOT NULL,'.
        'user_id INT(11) NOT NULL,'.
        'type enum("R", "I") NOT NULL'.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);


// Create group post table
qa_db_query_sub(
'CREATE TABLE IF NOT EXISTS ^group_posts ('.
    'id INT(11) NOT NULL AUTO_INCREMENT,'.
    'posted_at DATETIME NOT NULL,'.
    'group_id INT(11) NOT NULL,'.
    'user_id INT(11) NOT NULL,'.
    'title VARCHAR (200) ,'.
    'content VARCHAR (8000) DEFAULT NULL,'.
    'tags VARCHAR(100) DEFAULT NULL,'.
    'type enum("A", "D", "C") NOT NULL,'.
    'is_locked INT(1) DEFAULT 0,'.
    'is_sticky INT(1) DEFAULT 0,'.
    'edited_at DATETIME DEFAULT NULL,'.
    'editor_id INT(11) DEFAULT NULL,'.
    'parent_id INT(11) DEFAULT NULL,'.
    'PRIMARY KEY (id)'.
') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);

//---------- FRIENDS  ------------//
// Create friends and friend request tables.
qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^friend_requests ('.
        'requsted_at DATETIME NOT NULL,'.
        'requester_id INT(11) NOT NULL,'.
        'receiver_id INT(11) NOT NULL'.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);

qa_db_query_sub(
    'CREATE TABLE IF NOT EXISTS ^friend_list ('.
        'added_at DATETIME NOT NULL,'.
        'user_id INT(11) NOT NULL,'.
        'friend_id INT(11) NOT NULL'.
    ') ENGINE=MyISAM DEFAULT CHARSET=utf8'
);
