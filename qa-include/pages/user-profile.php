<?php
/*
	Social Meccano by Brett Orr and Samuel Hammill
	Based on Question2Answer by Gideon Greenspan and contributors

	File: qa-include/qa-page-user-profile.php
	Description: Controller for user profile page, including wall


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
 *  This page accesses information regarding the specified user,
 *  returning it in qa_content. See individual regions below.
 *  REGIONS:
 *      - HOUSEKEEPING
 *      - PROCESS_EDIT
 *      - BONUS_POINTS
 *      - CONTENT_PREP
 *      - BASIC_INFO
 *      - ACTIVITY_INFO
 *      - WALL_POSTS
 *      - ACTIVITY_LIST
 *      - END_HOUSEKEEPING
 */ 

#region HOUSEKEEPING
	if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
		header('Location: ../');
		exit;
	}

	require_once QA_INCLUDE_DIR.'db/selects.php';
	require_once QA_INCLUDE_DIR.'app/format.php';
	require_once QA_INCLUDE_DIR.'app/limits.php';
	require_once QA_INCLUDE_DIR.'app/updates.php';


//	$handle, $userhtml are already set by qa-page-user.php - also $userid if using external user integration

//	Redirect to 'My Account' page if button clicked
	if (qa_clicked('doaccount'))
		qa_redirect('account');


//	Find the user profile and questions and answers for this handle

	$loginuserid = qa_get_logged_in_userid();
	$identifier = QA_FINAL_EXTERNAL_USERS ? $userid : $handle;

	list($useraccount, $userprofile, $userfields, $usermessages, $questions, $userpoints, $userlevels, $navcategories, $userrank, $answerqs, $commentqs, $editqs) =
		qa_db_select_with_pending(
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_account_selectspec($handle, false),
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_profile_selectspec($handle, false),
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_userfields_selectspec(),
			QA_FINAL_EXTERNAL_USERS ? null : qa_db_recent_messages_selectspec(null, null, $handle, false, qa_opt_if_loaded('page_size_wall')),
            QA_FINAL_EXTERNAL_USERS ? null : qa_db_user_recent_qs_selectspec($loginuserid, $identifier, qa_opt_if_loaded('page_size_activity')),
			qa_db_user_points_selectspec($identifier),
			qa_db_user_levels_selectspec($identifier, QA_FINAL_EXTERNAL_USERS, true),
			qa_db_category_nav_selectspec(null, true),
			qa_db_user_rank_selectspec($identifier),
            qa_db_user_recent_a_qs_selectspec($loginuserid, $identifier),
            qa_db_user_recent_c_qs_selectspec($loginuserid, $identifier),
            qa_db_user_recent_edit_qs_selectspec($loginuserid, $identifier)
        
		);

	if (!QA_FINAL_EXTERNAL_USERS) {
		foreach ($userfields as $index => $userfield) {
			if ( isset($userfield['permit']) && qa_permit_value_error($userfield['permit'], $loginuserid, qa_get_logged_in_level(), qa_get_logged_in_flags()) )
				unset($userfields[$index]); // don't pay attention to user fields we're not allowed to view
		}
	}


//	Check the user exists and work out what can and can't be set (if not using single sign-on)

	$errors = array();

	$loginlevel = qa_get_logged_in_level();

	if (!QA_FINAL_EXTERNAL_USERS) { // if we're using integrated user management, we can know and show more
		require_once QA_INCLUDE_DIR.'app/messages.php';

		if ((!is_array($userpoints)) && !is_array($useraccount))
			return include QA_INCLUDE_DIR.'qa-page-not-found.php';

		$userid = $useraccount['userid'];
		$fieldseditable = false;
		$maxlevelassign = null;

		$maxuserlevel = $useraccount['level'];
		foreach ($userlevels as $userlevel)
			$maxuserlevel = max($maxuserlevel, $userlevel['level']);

		if (
			isset($loginuserid) &&
			($loginuserid != $userid) &&
			(($loginlevel >= QA_USER_LEVEL_SUPER) || ($loginlevel > $maxuserlevel)) &&
			(!qa_user_permit_error())
		) { // can't change self - or someone on your level (or higher, obviously) unless you're a super admin

			if ($loginlevel >= QA_USER_LEVEL_SUPER)
				$maxlevelassign = QA_USER_LEVEL_SUPER;

			elseif ($loginlevel >= QA_USER_LEVEL_ADMIN)
				$maxlevelassign = QA_USER_LEVEL_MODERATOR;

			elseif ($loginlevel >= QA_USER_LEVEL_MODERATOR)
				$maxlevelassign = QA_USER_LEVEL_EXPERT;

			if ($loginlevel >= QA_USER_LEVEL_ADMIN)
				$fieldseditable = true;

			if (isset($maxlevelassign) && ($useraccount['flags'] & QA_USER_FLAGS_USER_BLOCKED))
				$maxlevelassign = min($maxlevelassign, QA_USER_LEVEL_EDITOR); // if blocked, can't promote too high
		}

		$approvebutton = isset($maxlevelassign)
			&& $useraccount['level'] < QA_USER_LEVEL_APPROVED
			&& $maxlevelassign >= QA_USER_LEVEL_APPROVED
			&& !($useraccount['flags'] & QA_USER_FLAGS_USER_BLOCKED)
			&& qa_opt('moderate_users');
		$usereditbutton = $fieldseditable || isset($maxlevelassign);
		$userediting = $usereditbutton && (qa_get_state() == 'edit');

		$wallposterrorhtml = qa_wall_error_html($loginuserid, $useraccount['userid'], $useraccount['flags']);

  
        /* Modified code from user-wall to get the user's wall
         * messages and let the user delete them. More in WALL
         */
		$usermessages = array_slice($usermessages, 0, qa_opt('page_size_wall'));
		$usermessages = qa_wall_posts_add_rules($usermessages, 0);

		foreach ($usermessages as $message) {
			if ($message['deleteable'] && qa_clicked('m'.$message['messageid'].'_dodelete')) {
				if (!qa_check_form_security_code('wall-'.$useraccount['handle'], qa_post_text('code')))
					$errors['page'] = qa_lang_html('misc/form_security_again');
				else {
					qa_wall_delete_post($loginuserid, qa_get_logged_in_handle(), qa_cookie_get(), $message);
					qa_redirect(qa_request(), null, null, null, 'wall');
				}
			}
		}
        
        
       /* Modified code from user-activity to get the user's recent
        * questions etc. More in ACTIVITY_LIST
        */
       $questions = qa_any_sort_and_dedupe(array_merge($questions, $answerqs, $commentqs, $editqs));
	   $questions = array_slice($questions, 0, qa_opt('page_size_activity'));
	   $usershtml = qa_userids_handles_html(qa_any_get_userids_handles($questions), false);

       $htmldefaults = qa_post_html_defaults('Q');
       $htmldefaults['whoview'] = false;
       $htmldefaults['voteview'] = false;
       $htmldefaults['avatarsize'] = 0;
    
	}
#endregion
  
#region PROCESS_EDIT
    /* Procecsses the edit button for the user profile.
     */
	if (!QA_FINAL_EXTERNAL_USERS) {
		$reloaduser = false;

		if ($usereditbutton) {
			if (qa_clicked('docancel'))
				qa_redirect(qa_request());

			elseif (qa_clicked('doedit'))
				qa_redirect(qa_request(), array('state' => 'edit'));

			elseif (qa_clicked('dosave')) {
				require_once QA_INCLUDE_DIR.'app/users-edit.php';
				require_once QA_INCLUDE_DIR.'db/users.php';

				$inemail = qa_post_text('email');

				$inprofile = array();
				foreach ($userfields as $userfield)
					$inprofile[$userfield['fieldid']] = qa_post_text('field_'.$userfield['fieldid']);

				if (!qa_check_form_security_code('user-edit-'.$handle, qa_post_text('code'))) {
					$errors['page'] = qa_lang_html('misc/form_security_again');
					$userediting = true;
				}
				else {
					if (qa_post_text('removeavatar')) {
						qa_db_user_set_flag($userid, QA_USER_FLAGS_SHOW_AVATAR, false);
						qa_db_user_set_flag($userid, QA_USER_FLAGS_SHOW_GRAVATAR, false);

						if (isset($useraccount['avatarblobid'])) {
							require_once QA_INCLUDE_DIR.'app/blobs.php';

							qa_db_user_set($userid, 'avatarblobid', null);
							qa_db_user_set($userid, 'avatarwidth', null);
							qa_db_user_set($userid, 'avatarheight', null);
							qa_delete_blob($useraccount['avatarblobid']);
						}
					}

					if ($fieldseditable) {
						$filterhandle = $handle; // we're not filtering the handle...
						$errors = qa_handle_email_filter($filterhandle, $inemail, $useraccount);
						unset($errors['handle']); // ...and we don't care about any errors in it

						if (!isset($errors['email']))
							if ($inemail != $useraccount['email']) {
								qa_db_user_set($userid, 'email', $inemail);
								qa_db_user_set_flag($userid, QA_USER_FLAGS_EMAIL_CONFIRMED, false);
							}

						if (count($inprofile)) {
							$filtermodules = qa_load_modules_with('filter', 'filter_profile');
							foreach ($filtermodules as $filtermodule)
								$filtermodule->filter_profile($inprofile, $errors, $useraccount, $userprofile);
						}

						foreach ($userfields as $userfield)
							if (!isset($errors[$userfield['fieldid']]))
								qa_db_user_profile_set($userid, $userfield['title'], $inprofile[$userfield['fieldid']]);

						if (count($errors))
							$userediting = true;

						qa_report_event('u_edit', $loginuserid, qa_get_logged_in_handle(), qa_cookie_get(), array(
							'userid' => $userid,
							'handle' => $useraccount['handle'],
						));
					}

					if (isset($maxlevelassign)) {
						$inlevel = min($maxlevelassign, (int)qa_post_text('level')); // constrain based on maximum permitted to prevent simple browser-based attack
						if ($inlevel != $useraccount['level'])
							qa_set_user_level($userid, $useraccount['handle'], $inlevel, $useraccount['level']);

						if (qa_using_categories()) {
							$inuserlevels = array();

							for ($index = 1; $index <= 999; $index++) {
								$inlevel = qa_post_text('uc_'.$index.'_level');
								if (!isset($inlevel))
									break;

								$categoryid = qa_get_category_field_value('uc_'.$index.'_cat');

								if (strlen($categoryid) && strlen($inlevel))
									$inuserlevels[] = array(
										'entitytype' => QA_ENTITY_CATEGORY,
										'entityid' => $categoryid,
										'level' => min($maxlevelassign, (int)$inlevel),
									);
							}

							qa_db_user_levels_set($userid, $inuserlevels);
						}
					}

					if (empty($errors))
						qa_redirect(qa_request());

					list($useraccount, $userprofile, $userlevels) = qa_db_select_with_pending(
						qa_db_user_account_selectspec($userid, true),
						qa_db_user_profile_selectspec($userid, true),
						qa_db_user_levels_selectspec($userid, true, true)
					);
				}
			}
		}

		if (qa_clicked('doapprove') || qa_clicked('doblock') || qa_clicked('dounblock') || qa_clicked('dohideall') || qa_clicked('dodelete')) {
			if (!qa_check_form_security_code('user-'.$handle, qa_post_text('code')))
				$errors['page'] = qa_lang_html('misc/form_security_again');

			else {
				if ($approvebutton && qa_clicked('doapprove')) {
					require_once QA_INCLUDE_DIR.'app/users-edit.php';
					qa_set_user_level($userid, $useraccount['handle'], QA_USER_LEVEL_APPROVED, $useraccount['level']);
					qa_redirect(qa_request());
				}

				if (isset($maxlevelassign) && ($maxuserlevel < QA_USER_LEVEL_MODERATOR)) {
					if (qa_clicked('doblock')) {
						require_once QA_INCLUDE_DIR.'app/users-edit.php';

						qa_set_user_blocked($userid, $useraccount['handle'], true);
						qa_redirect(qa_request());
					}

					if (qa_clicked('dounblock')) {
						require_once QA_INCLUDE_DIR.'app/users-edit.php';

						qa_set_user_blocked($userid, $useraccount['handle'], false);
						qa_redirect(qa_request());
					}

					if (qa_clicked('dohideall') && !qa_user_permit_error('permit_hide_show')) {
						require_once QA_INCLUDE_DIR.'db/admin.php';
						require_once QA_INCLUDE_DIR.'app/posts.php';

						$postids = qa_db_get_user_visible_postids($userid);

						foreach ($postids as $postid)
							qa_post_set_hidden($postid, true, $loginuserid);

						qa_redirect(qa_request());
					}

					if (qa_clicked('dodelete') && ($loginlevel >= QA_USER_LEVEL_ADMIN)) {
						require_once QA_INCLUDE_DIR.'app/users-edit.php';

						qa_delete_user($userid);

						qa_report_event('u_delete', $loginuserid, qa_get_logged_in_handle(), qa_cookie_get(), array(
							'userid' => $userid,
							'handle' => $useraccount['handle'],
						));

						qa_redirect('users');
					}
				}
			}
		}


		if (qa_clicked('dowallpost')) {
			$inmessage = qa_post_text('message');

			if (!strlen($inmessage))
				$errors['message'] = qa_lang('profile/post_wall_empty');

			elseif (!qa_check_form_security_code('wall-'.$useraccount['handle'], qa_post_text('code')))
				$errors['message'] = qa_lang_html('misc/form_security_again');

			elseif (!$wallposterrorhtml) {
				qa_wall_add_post($loginuserid, qa_get_logged_in_handle(), qa_cookie_get(), $userid, $useraccount['handle'], $inmessage, '');
				qa_redirect(qa_request(), null, null, null, 'wall');
			}
		}
	}

#endregion

#region BONUS_POINTS
    /*
     * Processes the user (as an admin) assigning
     * bonus points to the specified user.
     */
	if ( ($loginlevel >= QA_USER_LEVEL_ADMIN) && qa_clicked('dosetbonus') ) {
		require_once QA_INCLUDE_DIR.'db/points.php';

		$inbonus = (int)qa_post_text('bonus');

		if (!qa_check_form_security_code('user-activity-'.$handle, qa_post_text('code')))
			$errors['page'] = qa_lang_html('misc/form_security_again');

		else {
			qa_db_points_set_bonus($userid, $inbonus);
			qa_db_points_update_ifuser($userid, null);
			qa_redirect(qa_request(), null, null, null, 'activity');
		}
	}
#endregion

#region CONTENT_PREP
    /* Starts preparing the nested array,
     * setting the page title and some basic stuff.
     */
	$qa_content = qa_content_prepare();

	$qa_content['title'] = qa_lang_html_sub('profile/user_x', $userhtml);
	$qa_content['error'] = @$errors['page'];

	if (isset($loginuserid) && $loginuserid != $useraccount['userid'] && !QA_FINAL_EXTERNAL_USERS) {
		$favoritemap = qa_get_favorite_non_qs_map();
		$favorite = @$favoritemap['user'][$useraccount['userid']];

		$qa_content['favorite'] = qa_favorite_form(QA_ENTITY_USER, $useraccount['userid'], $favorite,
			qa_lang_sub($favorite ? 'main/remove_x_favorites' : 'users/add_user_x_favorites', $handle));
	}

	$qa_content['script_rel'][] = 'qa-content/qa-user.js?'.QA_VERSION;
#endregion

#region BASIC_INFO
    /*
     * General information about the user, only available if we're using internal user management
     * sets a top-level array of ['profile-form'] in qa_content, containing
     * ['fields'] that can be looped through.
     */
	if (!QA_FINAL_EXTERNAL_USERS) {
		$qa_content['profile-form'] = array(
			'tags' => 'method="post" action="'.qa_self_html().'"',

			'style' => 'wide',

			'fields' => array(
				'avatar' => array(
					'type' => 'image',
					'style' => 'tall',
					'label' => '',
					'html' => qa_get_user_avatar_html($useraccount['flags'], $useraccount['email'], $useraccount['handle'],$useraccount['avatarblobid'], $useraccount['avatarwidth'], $useraccount['avatarheight'], qa_opt('avatar_profile_size')),
					'id' => 'avatar',
				),
                

				'removeavatar' => null,
			),
		);

		if (empty($qa_content['profile-form']['fields']['avatar']['html']))
			unset($qa_content['profile-form']['fields']['avatar']);


	   //Private message link
        /*
		if ( qa_opt('allow_private_messages') && isset($loginuserid) && ($loginuserid != $userid) && !($useraccount['flags'] & QA_USER_FLAGS_NO_MESSAGES) && !$userediting ) {
			$qa_content['profile-form']['fields']['level']['value'] .= strtr(qa_lang_html('profile/send_private_message'), array(
				'^1' => '<a href="'.qa_path_html('message/'.$handle).'">',
				'^2' => '</a>',
			));
		}*/

	   //Levels editing or viewing (add category-specific levels)
		if ($userediting) {

			if (isset($maxlevelassign)) {
				$qa_content['profile-form']['fields']['level']['type'] = 'select';

				$showlevels = array(QA_USER_LEVEL_BASIC);
				if (qa_opt('moderate_users'))
					$showlevels[] = QA_USER_LEVEL_APPROVED;

				array_push($showlevels, QA_USER_LEVEL_EXPERT, QA_USER_LEVEL_EDITOR, QA_USER_LEVEL_MODERATOR, QA_USER_LEVEL_ADMIN, QA_USER_LEVEL_SUPER);

				$leveloptions = array();
				$catleveloptions = array('' => qa_lang_html('users/category_level_none'));

				foreach ($showlevels as $showlevel) {
					if ($showlevel <= $maxlevelassign) {
						$leveloptions[$showlevel] = qa_html(qa_user_level_string($showlevel));
						if ($showlevel > QA_USER_LEVEL_BASIC)
							$catleveloptions[$showlevel] = $leveloptions[$showlevel];
					}
				}

				$qa_content['profile-form']['fields']['level']['options'] = $leveloptions;


			//	Category-specific levels

				if (qa_using_categories()) {
					$catleveladd = strlen(qa_get('catleveladd')) > 0;

					if ((!$catleveladd) && !count($userlevels)) {
						$qa_content['profile-form']['fields']['level']['suffix'] = strtr(qa_lang_html('users/category_level_add'), array(
							'^1' => '<a href="'.qa_path_html(qa_request(), array('state' => 'edit', 'catleveladd' => 1)).'">',
							'^2' => '</a>',
						));
					}
					else
						$qa_content['profile-form']['fields']['level']['suffix'] = qa_lang_html('users/level_in_general');

					if ($catleveladd || count($userlevels))
						$userlevels[] = array('entitytype' => QA_ENTITY_CATEGORY);

					$index = 0;
					foreach ($userlevels as $userlevel) {
						if ($userlevel['entitytype'] == QA_ENTITY_CATEGORY) {
							$index++;
							$id = 'ls_'.+$index;

							$qa_content['profile-form']['fields']['uc_'.$index.'_level'] = array(
								'label' => qa_lang_html('users/category_level_label'),
								'type' => 'select',
								'tags' => 'name="uc_'.$index.'_level" id="'.qa_html($id).'" onchange="this.qa_prev=this.options[this.selectedIndex].value;"',
								'options' => $catleveloptions,
								'value' => isset($userlevel['level']) ? qa_html(qa_user_level_string($userlevel['level'])) : '',
								'suffix' => qa_lang_html('users/category_level_in'),
							);

							$qa_content['profile-form']['fields']['uc_'.$index.'_cat'] = array();

							if (isset($userlevel['entityid']))
								$fieldnavcategories = qa_db_select_with_pending(qa_db_category_nav_selectspec($userlevel['entityid'], true));
							else
								$fieldnavcategories = $navcategories;

							qa_set_up_category_field($qa_content, $qa_content['profile-form']['fields']['uc_'.$index.'_cat'],
								'uc_'.$index.'_cat', $fieldnavcategories, @$userlevel['entityid'], true, true);

							unset($qa_content['profile-form']['fields']['uc_'.$index.'_cat']['note']);
						}
					}

					$qa_content['script_lines'][] = array(
						"function qa_update_category_levels()",
						"{",
						"\tglob=document.getElementById('level_select');",
						"\tif (!glob)",
						"\t\treturn;",
						"\tvar opts=glob.options;",
						"\tvar lev=parseInt(opts[glob.selectedIndex].value);",
						"\tfor (var i=1; i<9999; i++) {",
						"\t\tvar sel=document.getElementById('ls_'+i);",
						"\t\tif (!sel)",
						"\t\t\tbreak;",
						"\t\tsel.qa_prev=sel.qa_prev || sel.options[sel.selectedIndex].value;",
						"\t\tsel.options.length=1;", // just leaves "no upgrade" element
						"\t\tfor (var j=0; j<opts.length; j++)",
						"\t\t\tif (parseInt(opts[j].value)>lev)",
						"\t\t\t\tsel.options[sel.options.length]=new Option(opts[j].text, opts[j].value, false, (opts[j].value==sel.qa_prev));",
						"\t}",
						"}",
					);

					$qa_content['script_onloads'][] = array(
						"qa_update_category_levels();",
					);

					@$qa_content['profile-form']['fields']['level']['tags'] .= ' id="level_select" onchange="qa_update_category_levels();"';

				}
			}

		}
		else {
			foreach ($userlevels as $userlevel) {
				if ( $userlevel['entitytype'] == QA_ENTITY_CATEGORY && $userlevel['level'] > $useraccount['level'] ) {
					$qa_content['profile-form']['fields']['level']['value'] .= '<br/>'.
						strtr(qa_lang_html('users/level_for_category'), array(
							'^1' => qa_html(qa_user_level_string($userlevel['level'])),
							'^2' => '<a href="'.qa_path_html(implode('/', array_reverse(explode('/', $userlevel['backpath'])))).'">'.qa_html($userlevel['title']).'</a>',
						));
				}
			}
		}

	   /*
        * Show email address if we're the admin.
        */
		if (($loginlevel >= QA_USER_LEVEL_ADMIN) && !qa_user_permit_error()) {
			$doconfirms = qa_opt('confirm_user_emails') && $useraccount['level'] < QA_USER_LEVEL_EXPERT;
			$isconfirmed = ($useraccount['flags'] & QA_USER_FLAGS_EMAIL_CONFIRMED) > 0;
			$htmlemail = qa_html(isset($inemail) ? $inemail : $useraccount['email']);

			$qa_content['profile-form']['fields']['email'] = array(
				'type' => $userediting ? 'text' : 'static',
				'label' => qa_lang_html('users/email_label'),
				'tags' => 'name="email"',
				'value' => $userediting ? $htmlemail : ('<a href="mailto:'.$htmlemail.'">'.$htmlemail.'</a>'),
				'error' => qa_html(@$errors['email']),
				'note' => ($doconfirms ? (qa_lang_html($isconfirmed ? 'users/email_confirmed' : 'users/email_not_confirmed').' ') : '').
					($userediting ? '' : qa_lang_html('users/only_shown_admins')),
				'id' => 'email',
			);
		}
        
        
        /* 
         * Show some additional fields.
         */
		$fieldsediting = $fieldseditable && $userediting;

		foreach ($userfields as $userfield) {
			if (($userfield['flags'] & QA_FIELD_FLAGS_LINK_URL) && !$fieldsediting)
				$valuehtml = qa_url_to_html_link(@$userprofile[$userfield['title']], qa_opt('links_in_new_window'));

			else {
				$value = @$inprofile[$userfield['fieldid']];
				if (!isset($value))
					$value = @$userprofile[$userfield['title']];

				$valuehtml = qa_html($value, (($userfield['flags'] & QA_FIELD_FLAGS_MULTI_LINE) && !$fieldsediting));
			}
            
            //Check that there is actually a value here.
            if(strlen($valuehtml) > 0){

                $label = trim(qa_user_userfield_label($userfield), ':');
                if (strlen($label))
                    $label .= ':';

                $notehtml = null;
                if (isset($userfield['permit']) && !$userediting) {
                    if ($userfield['permit'] <= QA_PERMIT_ADMINS)
                        $notehtml = qa_lang_html('users/only_shown_admins');
                    elseif ($userfield['permit'] <= QA_PERMIT_MODERATORS)
                        $notehtml = qa_lang_html('users/only_shown_moderators');
                    elseif ($userfield['permit'] <= QA_PERMIT_EDITORS)
                        $notehtml = qa_lang_html('users/only_shown_editors');
                    elseif ($userfield['permit'] <= QA_PERMIT_EXPERTS)
                        $notehtml = qa_lang_html('users/only_shown_experts');
                }

                $qa_content['profile-form']['fields'][$userfield['title']] = array(
                    'type' => $fieldsediting ? 'text' : 'static',
                    'label' => qa_html($label),
                    'tags' => 'name="field_'.$userfield['fieldid'].'"',
                    'value' => $valuehtml,
                    'error' => qa_html(@$errors[$userfield['fieldid']]),
                    'note' => $notehtml,
                    'rows' => ($userfield['flags'] & QA_FIELD_FLAGS_MULTI_LINE) ? 8 : null,
                    'id' => 'userfield-'.$userfield['fieldid'],
                );
            }
		}
        
        /*
         * Show the user's username.
         */
        $sha['username'] = array(
            'type' => 'static',
            'label' => qa_lang_html_sub('profile/x_user', $userhtml),
            'value' => '<span class="qa-uf-user-name">'. qa_lang_html_sub('profile/x_user', $userhtml) .'</span>',
            'id' => 'username',
        );
        
        /*
         * Show the user's points (rep).
         */
        $sha['points'] = array(
            'type' => 'static',
            'label' => qa_lang_html('profile/x_user'),
            'value' => (@$userpoints['points'] == 1)
                ? qa_lang_html_sub('main/1_point', '<span class="qa-uf-user-points">1</span>', '1')
                : qa_lang_html_sub('main/x_points', '<span class="qa-uf-user-points">'.qa_html(number_format(@$userpoints['points'])).'</span>'),
            'id' => 'points',
        );
        
        //Copes with the extra field (email addres) that appears on an admin's view
        if ($loginlevel < QA_USER_LEVEL_ADMIN){
            array_splice($qa_content['profile-form']['fields'], 3, 0, $sha);
        }else{
            array_splice($qa_content['profile-form']['fields'], 4, 0, $sha);
        }
        

        /*
         * Edit button/form as appropriate.
         */
		if ($userediting) {
			if (
				(qa_opt('avatar_allow_gravatar') && ($useraccount['flags'] & QA_USER_FLAGS_SHOW_GRAVATAR)) ||
				(qa_opt('avatar_allow_upload') && ($useraccount['flags'] & QA_USER_FLAGS_SHOW_AVATAR) && isset($useraccount['avatarblobid']))
			) {
				$qa_content['profile-form']['fields']['removeavatar'] = array(
					'type' => 'checkbox',
					'label' => qa_lang_html('users/remove_avatar'),
					'tags' => 'name="removeavatar"',
				);
			}

			$qa_content['profile-form']['buttons'] = array(
				'save' => array(
					'tags' => 'onclick="qa_show_waiting_after(this, false);"',
					'label' => qa_lang_html('users/save_user'),
				),

				'cancel' => array(
					'tags' => 'name="docancel"',
					'label' => qa_lang_html('main/cancel_button'),
				),
			);

			$qa_content['profile-form']['hidden'] = array(
				'dosave' => '1',
				'code' => qa_get_form_security_code('user-edit-'.$handle),
			);

		}
		elseif ($usereditbutton) {
			$qa_content['profile-form']['buttons'] = array();

			if ($approvebutton) {
				$qa_content['profile-form']['buttons']['approve'] = array(
					'tags' => 'name="doapprove"',
					'label' => qa_lang_html('users/approve_user_button'),
				);
			}

			$qa_content['profile-form']['buttons']['edit'] = array(
				'tags' => 'name="doedit"',
				'label' => qa_lang_html('users/edit_user_button'),
			);

			if (isset($maxlevelassign) && $useraccount['level'] < QA_USER_LEVEL_MODERATOR) {
				if ($useraccount['flags'] & QA_USER_FLAGS_USER_BLOCKED) {
					$qa_content['profile-form']['buttons']['unblock'] = array(
						'tags' => 'name="dounblock"',
						'label' => qa_lang_html('users/unblock_user_button'),
					);

					if (!qa_user_permit_error('permit_hide_show')) {
						$qa_content['profile-form']['buttons']['hideall'] = array(
							'tags' => 'name="dohideall" onclick="qa_show_waiting_after(this, false);"',
							'label' => qa_lang_html('users/hide_all_user_button'),
						);
					}

					if ($loginlevel >= QA_USER_LEVEL_ADMIN) {
						$qa_content['profile-form']['buttons']['delete'] = array(
							'tags' => 'name="dodelete" onclick="qa_show_waiting_after(this, false);"',
							'label' => qa_lang_html('users/delete_user_button'),
						);
					}

				}
				else {
					$qa_content['profile-form']['buttons']['block'] = array(
						'tags' => 'name="doblock"',
						'label' => qa_lang_html('users/block_user_button'),
					);
				}

				$qa_content['profile-form']['hidden'] = array(
					'code' => qa_get_form_security_code('user-'.$handle),
				);
			}

		}
		elseif (isset($loginuserid) && ($loginuserid == $userid)) {
			$qa_content['profile-form']['buttons'] = array(
				'account' => array(
					'tags' => 'name="doaccount"',
					'label' => qa_lang_html('users/edit_profile'),
				),
			);
		}


		if (!is_array($qa_content['profile-form']['fields']['removeavatar']))
			unset($qa_content['profile-form']['fields']['removeavatar']);

		$qa_content['raw']['account'] = $useraccount; // for plugin layers to access
		$qa_content['raw']['profile'] = $userprofile;
	}
#endregion

#region ACTIVITY_INFO

    /*
     * Returns ['activity-form'] array, which contains the 
     * user's activity (questions, comments, answered).
     */
	$qa_content['activity-form'] = array(
		'style' => 'wide',

		'fields' => array(
			'bonus' => array(
				'label' => qa_lang_html('profile/bonus_points'),
				'tags' => 'name="bonus"',
				'value' => qa_html(isset($inbonus) ? $inbonus : $userpoints['bonus']),
				'type' => 'number',
				'note' => qa_lang_html('users/only_shown_admins'),
				'id' => 'bonus',
			),

			'title' => array(
				'type' => 'static',
				'label' => qa_lang_html('profile/title'),
				'value' => qa_get_points_title_html(@$userpoints['points'], qa_get_points_to_titles()),
				'id' => 'title',
			),

			'questions' => array(
				'type' => 'static',
				'label' => qa_lang_html('profile/questions'),
				'value' => '<span class="qa-uf-user-q-posts">'.qa_html(number_format(@$userpoints['qposts'])).'</span>',
				'id' => 'questions',
			),

			'answers' => array(
				'type' => 'static',
				'label' => qa_lang_html('profile/answers'),
				'value' => '<span class="qa-uf-user-a-posts">'.qa_html(number_format(@$userpoints['aposts'])).'</span>',
				'id' => 'answers',
			),
		),
	);

    /*
    * If we're the admin, place the bonus button here.
    */
	if ($loginlevel >= QA_USER_LEVEL_ADMIN) {
		$qa_content['activity-form']['tags'] = 'method="post" action="'.qa_self_html().'"';

		$qa_content['activity-form']['buttons'] = array(
			'setbonus' => array(
				'tags' => 'name="dosetbonus"',
				'label' => qa_lang_html('profile/set_bonus_button'),
			),
		);

		$qa_content['activity-form']['hidden'] = array(
			'code' => qa_get_form_security_code('user-activity-'.$handle),
		);

	}
	else
		unset($qa_content['activity-form']['fields']['bonus']);

	if (!isset($qa_content['activity-form']['fields']['title']['value']))
		unset($qa_content['activity-form']['fields']['title']);

	if (qa_opt('comment_on_qs') || qa_opt('comment_on_as')) { // only show comment count if comments are enabled
		$qa_content['activity-form']['fields']['comments'] = array(
			'type' => 'static',
			'label' => qa_lang_html('profile/comments'),
			'value' => '<span class="qa-uf-user-c-posts">'.qa_html(number_format(@$userpoints['cposts'])).'</span>',
			'id' => 'comments',
		);
	}

	/*if (@$userpoints['aselects']) {
		$qa_content['activity-form']['fields']['questions']['value'] .= ($userpoints['aselects'] == 1)
			? qa_lang_html_sub('profile/1_with_best_chosen', '<span class="qa-uf-user-q-selects">1</span>', '1')
			: qa_lang_html_sub('profile/x_with_best_chosen', '<span class="qa-uf-user-q-selects">'.number_format($userpoints['aselects']).'</span>');
	}*/

	/*if (@$userpoints['aselecteds']) {
		$qa_content['activity-form']['fields']['answers']['value'] .= ($userpoints['aselecteds'] == 1)
			? qa_lang_html_sub('profile/1_chosen_as_best', '<span class="qa-uf-user-a-selecteds">1</span>', '1')
			: qa_lang_html_sub('profile/x_chosen_as_best', '<span class="qa-uf-user-a-selecteds">'.number_format($userpoints['aselecteds']).'</span>');
	}*/



//	For plugin layers to access

	$qa_content['raw']['userid'] = $userid;
	$qa_content['raw']['points'] = $userpoints;
	$qa_content['raw']['rank'] = $userrank;
#endregion

#region WALL_POSTS
	if (!QA_FINAL_EXTERNAL_USERS && qa_opt('allow_user_walls')) {
		$qa_content['message_list'] = array(
			'title' => '<a name="wall">'.qa_lang_html_sub('profile/wall_for_x', $userhtml).'</a>',

			'tags' => 'id="wallmessages"',

			'form' => array(
				'tags' => 'name="wallpost" method="post" action="'.qa_self_html().'#wall"',
				'style' => 'tall',
				'hidden' => array(
					'qa_click' => '', // for simulating clicks in Javascript
					'handle' => qa_html($useraccount['handle']),
					'start' => 0,
					'code' => qa_get_form_security_code('wall-'.$useraccount['handle']),
				),
			),

			'messages' => array(),
		);

		if ($wallposterrorhtml)
			$qa_content['message_list']['error'] = $wallposterrorhtml; // an error that means we are not allowed to post

		else {
			$qa_content['message_list']['form']['fields'] = array(
				'message' => array(
					'tags' => 'name="message" id="message"',
					'value' => qa_html(@$inmessage, false),
					'rows' => 2,
					'error' => qa_html(@$errors['message']),
				),
			);

			$qa_content['message_list']['form']['buttons'] = array(
				'post' => array(
					'tags' => 'name="dowallpost" onclick="return qa_submit_wall_post(this, true);"',
					'label' => qa_lang_html('profile/post_wall_button'),
				),
			);
		}

		foreach ($usermessages as $message)
			$qa_content['message_list']['messages'][] = qa_wall_post_view($message);

		if ($useraccount['wallposts'] > count($usermessages))
			$qa_content['message_list']['messages'][] = qa_wall_view_more_link($handle, count($usermessages));
	}
#endregion

#region ACTIVITY_LIST
    /*
     * Modified form of user-activity page. Gets the most
     * recent user activty and returns it in an array
     * named 'activity_list', with an internal array
     * of 'activitymessages'
     */
    $qa_content['activity_list'] = array(
        'title' => '<a name="activity">'.qa_lang_html_sub('profile/activity_by_x', $userhtml).'</a>',

        'tags' => 'id="activitymessages"',

        'form' => array(
            'tags' => 'name="activitypost" method="post" action="'.qa_self_html().'#activity"',
            'style' => 'tall',
            'hidden' => array(
                'qa_click' => '', // for simulating clicks in Javascript
                'handle' => qa_html($useraccount['handle']),
                'start' => 0,
                'code' => qa_get_form_security_code('activity-'.$useraccount['handle']),
            ),
        ),

        'activitymessages' => array(),
    );

    foreach ($questions as $question){
        $qa_content['activity_list']['activitymessages'][] = ($question);
    }
#endregion

#region END_HOUSEKEEPING
//Sub menu for navigation in user pages
	$ismyuser = isset($loginuserid) && $loginuserid == (QA_FINAL_EXTERNAL_USERS ? $userid : $useraccount['userid']);
	$qa_content['navigation']['sub'] = qa_user_sub_navigation($handle, 'profile', $ismyuser);

#endregion
	return $qa_content;


/*
	Omit PHP closing tag to help avoid accidental output
*/
