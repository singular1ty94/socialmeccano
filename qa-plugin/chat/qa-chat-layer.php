<?php

	class qa_html_theme_layer extends qa_html_theme_base {

	// init before start

		function doctype() {
			qa_html_theme_base::doctype();            
        }
        
        function head_custom() {
			qa_html_theme_base::head_custom();
            $this->output('<script type="application/javascript" src="' . qa_path_to_root(). 'qa-plugin/chat/fancybox/jquery.fancybox.js?v=2.1.5"></script>');
            //$this->output('<script type="application/javascript" src="' . qa_path_to_root(). 'qa-plugin/chat/chat-engine/js/querystring.js"></script>');
            $this->output('<link rel="stylesheet" href="' . qa_path_to_root(). 'qa-plugin/chat/fancybox/jquery.fancybox.css?v=2.1.5"/>');
            $this->output('<link rel="stylesheet" href="' . qa_path_to_root(). 'qa-plugin/chat/chat.css"/>');
            
            //Prepare the chat script.
            $this->output('<script type="application/javascript" src="' . qa_path_to_root(). 'qa-plugin/chat/chat.js"></script>');
        }
        
        function body_custom() {
			qa_html_theme_base::body_custom();
            
            require_once './qa-plugin/notifications/qa-notifications-db.php';

            //Create a flag.
            $toread = false;

            //This gets all channels that the user can belong to.
            $channels = qa_db_read_all_assoc(
				qa_db_query_sub('SELECT * FROM ajax_chat_users WHERE handle = $ AND channelID > 0',
					qa_get_logged_in_user_field('handle')
				)
			);

            //Get the last message in the channels.
            $notifies = array();
            foreach($channels as $channel){
                $last = qa_db_read_one_assoc(
                    qa_db_query_sub('SELECT userID, channel, dateTime FROM ajax_chat_messages WHERE channel = '.  $channel["channelID"] . ' AND NOT userRole = 4 ORDER BY dateTime DESC'), true
                );

                //It wasn't our message.
                if($last["userID"] != qa_get_logged_in_userid()){
                    //Get the MESSAGE TIME.
                    $messageTime = strtotime($last['dateTime']);

                    //Get the LAST TIME the user LOGGED INTO this channel.
                    $logoutTime = strtotime($channel['dateTime']);

                    $diff = round(($messageTime - $logoutTime) / 60, 2);

                    //WAIT THREE WHOLE MINUTES
                    if($diff >= 3.0){
                        $toread = true;
                        $notifies[] = $last['channel'];
                    }
                }
            }


            //And if the flag was true, make a notification -
            //UNLESS, we've ALREADY notified the user...
            if($diff = true){
                foreach($notifies as $n){
                    $result = qa_db_read_one_assoc(
                        qa_db_query_sub('SELECT * FROM qa_notifications WHERE type = "ChatMessages" AND target_id = $ AND user_id = $', $n, qa_get_logged_in_userid()), true
                    );
                    if(empty($result) || !isset($result)){
                        $name = qa_db_read_one_assoc(
                            qa_db_query_sub('SELECT channelName FROM ajax_chat_channels WHERE channelID = $', $n), true
                        );
                        createNotification(qa_get_logged_in_userid(), 'ChatMessages', $n, $name, qa_get_logged_in_user_field("handle"));
                    }
                }
            }
        }
    }
