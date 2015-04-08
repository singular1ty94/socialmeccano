<?php

	class qa_html_theme_layer extends qa_html_theme_base {

	// init before start

		function doctype() {
				
			qa_html_theme_base::doctype();
        }
        
        function head_custom() {
			qa_html_theme_base::head_custom();
            $this->output('<link rel="stylesheet" href="' . qa_path_to_root(). 'qa-plugin/groups/groups.css"/>');
        }
    }