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
            
        }
    }
