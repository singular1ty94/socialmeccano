<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-include/qa-install.php
	Description: User interface for installing, upgrading and fixing the database


	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	 
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../');
	exit;
}

require_once QA_INCLUDE_DIR.'db/install.php';

qa_report_process_stage('init_install');


// Define database failure handler for install process, if not defined already (file could be included more than once)

if (!function_exists('qa_install_db_fail_handler')) {
	/**
	 * Handler function for database failures during the installation process
	 */
	function qa_install_db_fail_handler($type, $errno=null, $error=null, $query=null)
	{
		global $pass_failure_from_install;

		$pass_failure_type = $type;
		$pass_failure_errno = $errno;
		$pass_failure_error = $error;
		$pass_failure_query = $query;
		$pass_failure_from_install = true;

		require QA_INCLUDE_DIR.'qa-install.php';

		qa_exit('error');
	}
}


$success = '';
$errorhtml = '';
$suggest = '';
$buttons = array();
$fields = array();
$fielderrors = array();
$hidden = array();


// Process user handling higher up to avoid 'headers already sent' warning

if (!isset($pass_failure_type) && qa_clicked('super')) {
	require_once QA_INCLUDE_DIR.'db/users.php';
	require_once QA_INCLUDE_DIR.'app/users-edit.php';

	$inemail = qa_post_text('email');
	$inpassword = qa_post_text('password');
	$inhandle = qa_post_text('handle');

	$fielderrors = array_merge(
		qa_handle_email_filter($inhandle, $inemail),
		qa_password_validate($inpassword)
	);

	if (empty($fielderrors)) {
		require_once QA_INCLUDE_DIR.'app/users.php';

		$userid = qa_create_new_user($inemail, $inpassword, $inhandle, QA_USER_LEVEL_SUPER);

        //Now go an register this for chat.
        qa_db_query_sub('INSERT INTO ajax_chat_users(userID, handle, channelID)
            VALUES ($, $, 0)', $userid, $inhandle);

		qa_set_logged_in_user($userid, $inhandle);

		qa_set_option('feedback_email', $inemail);

        //Redirect to the toolbox so they can change the colors.
        header('Location: ./toolbox/default.php?install');
    }
}


//	Output start of HTML early, so we can see a nicely-formatted list of database queries when upgrading

?><!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Social Meccano Toolbox</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="../toolbox/css/normalize.css">
  <link rel="stylesheet" href="../toolbox/css/skeleton.css">
  <link type="text/css" rel="stylesheet" href="../toolbox/style.css" />
  <script type="application/javascript" src="j./toolbox/jquery-2.1.3.min.js"></script>

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="icon" type="image/png" href="images/favicon.png">

</head>
<body>

  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <div class="container">
    <div class="row install">
        <h2>Social Meccano Installation</h2>
<?php


if (isset($pass_failure_type)) {
	// this page was requested due to query failure, via the fail handler
	switch ($pass_failure_type) {
		case 'connect':
			$errorhtml .= 'Could not establish database connection. Please check the username, password and hostname in the config file, and if necessary set up the appropriate MySQL user and privileges.';
			break;

		case 'select':
			$errorhtml .= 'Could not switch to the Social Meccano database. Please check the database name in the config file, and if necessary create the database in MySQL and grant appropriate user privileges.';
			break;

		case 'query':
			global $pass_failure_from_install;

			if (@$pass_failure_from_install)
				$errorhtml .= "Social Meccano was unable to perform the installation query below. Please check the user in the config file has CREATE and ALTER permissions:\n\n".qa_html($pass_failure_query."\n\nError ".$pass_failure_errno.": ".$pass_failure_error."\n\n");
			else
				$errorhtml .= "A Social Meccano database query failed when generating this page.\n\nA full description of the failure is available in the web server's error log file.";
			break;
	}
}
else {
	// this page was requested by user GET/POST, so handle any incoming clicks on buttons
	qa_db_connect('qa_install_db_fail_handler');

	if (qa_clicked('create')) {
		qa_db_install_tables();

        //Install the chat-engine.
        require('./qa-plugin/chat/chat-engine/install.php');

        //Install the Social Meccano base.
        require('social-meccano-install.php');

		if (QA_FINAL_EXTERNAL_USERS) {
			if (defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH')) {
				require_once QA_INCLUDE_DIR.'db/admin.php';
				require_once QA_INCLUDE_DIR.'app/format.php';

				// create link back to WordPress home page
				qa_db_page_move(qa_db_page_create(get_option('blogname'), QA_PAGE_FLAGS_EXTERNAL, get_option('home'), null, null, null), 'O', 1);

				$success .= 'Your Social Meccano database has been created and integrated with your WordPress site.';

			}
			else {
				$success .= 'Your Social Meccano database has been created for external user identity management. Please read the online documentation to complete integration.';
			}
		}
		else {
			$success .= 'Your Social Meccano database has been created.';
		}
	}

	if (qa_clicked('nonuser')) {
		qa_db_install_tables();
		$success .= 'The additional Social Meccano database tables have been created.';
	}

	if (qa_clicked('upgrade')) {
		qa_db_upgrade_tables();
		$success .= 'Your Social Meccano database has been updated.';
	}

	if (qa_clicked('repair')) {
		qa_db_install_tables();
		$success .= 'The Social Meccano database tables have been repaired.';
	}

	if (qa_clicked('module')) {
		$moduletype = qa_post_text('moduletype');
		$modulename = qa_post_text('modulename');

		$module = qa_load_module($moduletype, $modulename);

		$queries = $module->init_queries(qa_db_list_tables());

		if (!empty($queries)) {
			if (!is_array($queries))
				$queries = array($queries);

			foreach ($queries as $query)
				qa_db_upgrade_query($query);
		}

		$success .= 'The '.$modulename.' '.$moduletype.' module has completed database initialization.';
	}

}

if (qa_db_connection(false) !== null && !@$pass_failure_from_install) {
	$check = qa_db_check_tables(); // see where the database is at

	switch ($check) {
		case 'none':
			if (@$pass_failure_errno == 1146) // don't show error if we're in installation process
				$errorhtml = '';

			$errorhtml .= 'Welcome to Social Meccano. It\'s time to set up your database!';

			if (QA_FINAL_EXTERNAL_USERS) {
				if (defined('QA_FINAL_WORDPRESS_INTEGRATE_PATH')) {
					$errorhtml .= "\n\nWhen you click below, your Social Meccano site will be set up to integrate with the users of your WordPress site <a href=\"".qa_html(get_option('home'))."\" target=\"_blank\">".qa_html(get_option('blogname'))."</a>. Please consult the online documentation for more information.";
				}
				else {
					$errorhtml .= "\n\nWhen you click below, your Social Meccano site will be set up to integrate with your existing user database and management. Users will be referenced with database column type ".qa_html(qa_get_mysql_user_column_type()).". Please consult the online documentation for more information.";
				}

				$buttons = array('create' => 'Continue');
			}
			else {
				$errorhtml .= "\n\nWhen you click below, your Social Meccano database will be set up to manage user identities and logins internally.";
				$buttons = array('create' => 'Continue');
			}
			break;

		case 'old-version':
			// don't show error if we need to upgrade
			if (!@$pass_failure_from_install)
				$errorhtml = '';

			// don't show error before this
			$errorhtml .= 'Your Social Meccano database needs to be upgraded for this version of the software.';
			$buttons = array('upgrade' => 'Upgrade the Database');
			break;

		case 'non-users-missing':
			$errorhtml = 'This Social Meccano site is sharing its users with another Q2A site, but it needs some additional database tables for its own content. Please click below to create them.';
			$buttons = array('nonuser' => 'Set up the Tables');
			break;

		case 'table-missing':
			$errorhtml .= 'One or more tables are missing from your Social Meccano database.';
			$buttons = array('repair' => 'Repair the Database');
			break;

		case 'column-missing':
			$errorhtml .= 'One or more Social Meccano database tables are missing a column.';
			$buttons = array('repair' => 'Repair the Database');
			break;

		default:
			require_once QA_INCLUDE_DIR.'db/admin.php';

			if (!QA_FINAL_EXTERNAL_USERS && qa_db_count_users() == 0) {
				$errorhtml .= "There are currently no users in the Social Meccano database.\n\nPlease enter your details below to create the super administrator:";
				$fields = array(
					'handle' => array('label' => 'Username:', 'type' => 'text'),
					'password' => array('label' => 'Password:', 'type' => 'password'),
					'email' => array('label' => 'Email address:', 'type' => 'text'),
				);
				$buttons = array('super' => 'Set up the Super Administrator');
			}
			else {
				$tables = qa_db_list_tables();

				$moduletypes = qa_list_module_types();

				foreach ($moduletypes as $moduletype) {
					$modules = qa_load_modules_with($moduletype, 'init_queries');

					foreach ($modules as $modulename => $module) {
						$queries = $module->init_queries($tables);
						if (!empty($queries)) {
							// also allows single query to be returned
							$errorhtml = strtr(qa_lang_html('admin/module_x_database_init'), array(
								'^1' => qa_html($modulename),
								'^2' => qa_html($moduletype),
								'^3' => '',
								'^4' => '',
							));

							$buttons = array('module' => 'Initialize the Database');

							$hidden['moduletype'] = $moduletype;
							$hidden['modulename'] = $modulename;
							break;
						}
					}
				}
			}
			break;
	}
}

if (empty($errorhtml)) {
	if (empty($success))
		$success = 'Your Social Meccano database has been checked with no problems.';

	$suggest = '<a href="'.qa_path_html('admin', null, null, QA_URL_FORMAT_SAFEST).'">Go to admin center</a>';
}

?>

		<form method="post" action="<?php echo qa_path_html('install', null, null, QA_URL_FORMAT_SAFEST)?>">

<?php

if (strlen($success))
	echo '<p class="msg-success">'.nl2br(qa_html($success)).'</p>';

if (strlen($errorhtml))
	echo '<p class="msg-error">'.nl2br($errorhtml).'</p>';

if (strlen($suggest))
	echo '<p>'.$suggest.'</p>';


//	Very simple general form display logic (we don't use theme since it depends on tons of DB options)

if (count($fields)) {
	echo '<table>';

	foreach ($fields as $name => $field) {
		echo '<tr>';
		echo '<th>'.qa_html($field['label']).'</th>';
		echo '<td><input type="'.qa_html($field['type']).'" size="24" name="'.qa_html($name).'" value="'.qa_html(@${'in'.$name}).'"></td>';
		if (isset($fielderrors[$name]))
			echo '<td class="msg-error"><small>'.qa_html($fielderrors[$name]).'</small></td>';
		else
			echo '<td></td>';
		echo '</tr>';
	}

	echo '</table>';
}

foreach ($buttons as $name => $value)
	echo '<input type="submit" name="'.qa_html($name).'" value="'.qa_html($value).'">';

foreach ($hidden as $name => $value)
	echo '<input type="hidden" name="'.qa_html($name).'" value="'.qa_html($value).'">';

qa_db_disconnect();

?>

		</form>
      </div>
    </div>
	</body>
</html>
