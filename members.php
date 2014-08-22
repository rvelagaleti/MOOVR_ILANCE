<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software 4.0.0 Build 8059
|| # -------------------------------------------------------------------- # ||
|| # Customer License # H7LhzCqPSNEABnY
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2014 ILance Inc. All Rights Reserved.                # ||
|| # This file may not be redistributed in whole or significant part.     # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline',
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
        'main'
);

// #### setup script location ##################################################
define('LOCATION','vendors');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[members]" => $ilcrumbs["$ilpage[members]"]);

// #### PROVIDER LISTINGS VIA CATEGORY #########################################
if (!empty($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 AND empty($ilance->GPC['id']))
{        
        $urlbit = print_hidden_fields(true, array('mode'));
        header('Location: ' . HTTP_SERVER . $ilpage['search'] . '?mode=experts' . $urlbit);
        exit();
}

$show['widescreen'] = false;

// #### PREPARE DEFAULT URLS ###########################################
$scriptpage = $ilpage['members'] . print_hidden_fields(true, array('feedback','page'), true);

$php_self = $scriptpage;
$php_self_urlencoded = urlencode($php_self);

define('PHP_SELF', HTTP_SERVER . $php_self);

// specifying a particular category?
$cid = 0;
if (isset($ilance->GPC['cid']) AND !empty($ilance->GPC['cid']))
{
        $cid = intval($ilance->GPC['cid']);
}

if (!isset($ilance->GPC['id']))
{
        $area_title = '{_invalid_vendor_id_warning_menu}';
        $page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
        print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
        exit();
}

$sql_username = $ilance->db->query("
        SELECT *
        FROM " . DB_PREFIX . "users
        WHERE username = '" . $ilance->db->escape_string($ilance->GPC['id']) . "'
	LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql_username) == 0)
{
	$sql_user_id = $ilance->db->query("
	        SELECT *
	        FROM " . DB_PREFIX . "users
	        WHERE user_id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	$sql_vendor = $sql_user_id;
}
else 
{
	$sql_vendor = $sql_username;
}

// #### MEMBER JOB HISTORY REVIEW ##############################################
if (isset($ilance->GPC['jobhistory']) AND $ilance->GPC['jobhistory'])
{
	if ($ilance->db->num_rows($sql_vendor) <= 0)
	{
		$area_title = '{_invalid_vendor_id_warning_menu}';
		$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
		print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	
	$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
	
	($apihook = $ilance->api('member_profile_review_start')) ? eval($apihook) : false;
	
	$vendorname_plain = stripslashes($res_vendor['username']);
	$vendorname = print_username($res_vendor['user_id'], 'href');
	$uid = $res_vendor['user_id'];
	$area_title = '{_viewing_job_history} <div class="smaller">' . $vendorname_plain . '</div>';
	$page_title = SITE_NAME . ' - {_viewing_profile_for_vendor} ' . $vendorname_plain;
	$navcrumb = array();
	$navcrumb[""] = $vendorname_plain;
	if (!$ilance->subscription->has_active_subscription($res_vendor['user_id']) OR $res_vendor['status'] != 'active')
	{
		print_notice('{_profile_temporarily_inactive}', '{_sorry_the_enhanced_profile_page_for_this_member_is_temporarily_inactive}', 'javascript:history.back(1);', '{_back}');
		exit();        
	}
	$subscription = $ilance->subscription->print_subscription_title($res_vendor['user_id']);
	$rolename = $ilance->subscription_role->print_role($ilance->subscription_role->fetch_user_roleid($res_vendor['user_id']));
	$membersince = print_date(fetch_user('date_added', $uid), $ilconfig['globalserverlocale_globaldateformat']);
	$show['portfoliolink'] = 0;
	if ($ilance->portfolio->has_portfolio($res_vendor['user_id']) > 0)
	{
		$show['portfoliolink'] = 1;
	}
	$show['categorypulldown'] = 0;
	if ($ilance->profile->has_answered_profile_questions($uid))
	{
		$show['categorypulldown'] = 1;
	}
	$gender = fetch_user('gender', $res_vendor['user_id'], '', '', false);
	if ($gender == '' OR $gender == 'male')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	else if ($gender == 'female')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	$sql_attach = $ilance->db->query("
		SELECT attachid, filehash, filename, width, height
		FROM " . DB_PREFIX . "attachment
		WHERE user_id = '" . intval($res_vendor['user_id']) . "'
			AND visible = '1'
			AND attachtype = 'profile'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_attach) > 0)
	{
		$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
		$profile_logo = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
			? 'i/profile/' . $res_attach['filehash'] . '/' . ($res_attach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $res_attach['width']) . 'x' . ($res_attach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $res_attach['height']) . '_' . $res_attach['filename']
			: $ilpage['attachment'] . '?cmd=profile&amp;id=' . $res_attach['filehash']) . '" border="0" id="' . $res_attach['filehash'] . '" alt="' . handle_input_keywords($vendorname_plain) . '" />';
	}
	$vendorname_plain = construct_seo_url_name($vendorname_plain);
	$fullprofileurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid;
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$fullprofileurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain . '/profile';
	}
	$feedbackprofileurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid;
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$feedbackprofileurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain;
	}
	$show['openingstatement'] = false;
	$openingstatement = fetch_user('profileintro', $uid);
	if (!empty($openingstatement))
	{
		$show['openingstatement'] = true;
		$openingstatement = htmlspecialchars_decode($openingstatement);
	}
	
	// #### stats dashboard ################################################
	$cid = (isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0);
	$jobhistory = $ilance->auction_service->fetch_job_history_info($uid, $cid);
	$jobs = $jobhistory['jobs'];
	$milestones = $jobhistory['milestones'];
	$hours = $jobhistory['hours'];
	$rating = $jobhistory['rating'];
	$reviews = $jobhistory['reviews'];
	$scorepercent = $jobhistory['scorepercent'];
	$clients = $jobhistory['clients'];
	$repeatclientspercent = $jobhistory['repeatclientspercent'];
	$earnings = $jobhistory['earnings'];
	$earningsaverage = $jobhistory['earningsaverage'];
	unset($jobhistory);
	$jobcategories = $ilance->auction_service->fetch_job_categories_link_menu($uid, $cid);
	
	$pprint_array = array('jobcategories','jobs','milestones','hours','rating','reviews','scorepercent','clients','repeatclientspercent','earnings','earningspercent','earningsaverage','feedbackprofileurl','jobhistoryurl','uid','fullprofileurl','openingstatement','vendorname_plain','skills','columnas','feedbacktabs','membersince','rateperhour','profilevideo','city_state','feedback_pulldown2','allprofilesurl','rolename','dba','dba_pulldown','feedbackviewtype','feedback_pulldown','category_pulldown','subscription','overall_rating','contact_number','profile_logo','credentials_verified','title','firstname','lastname','prevnext','reviews','currency','profile_summary','profile_category','vendorname','vendor_id','isonline','lastseen','country','revenue','stars','id');
	
	($apihook = $ilance->api('members_feedback_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'member_jobhistory_profile.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('feedback', 'registration_questions', 'memberinfo', 'profile_question_groups', 'default_profile_question_groups'), false);
	@reset($default_profile_question_groups);
	while ($i = @each($default_profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'default_profile_questions' . $i['value']['groupid']);
	}                        
	@reset($profile_question_groups);
	while ($i = @each($profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
	}
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### MEMBER PROFILE REVIEW ##################################################
else if (isset($ilance->GPC['profile']) AND $ilance->GPC['profile'])
{
        if ($ilance->db->num_rows($sql_vendor) <= 0)
        {
		$area_title = '{_invalid_vendor_id_warning_menu}';
                $page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
                print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
                exit();
	}
	$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
	
	($apihook = $ilance->api('member_profile_review_start')) ? eval($apihook) : false;
	
	$vendorname_plain = stripslashes($res_vendor['username']);
	$vendorname = print_username($res_vendor['user_id'], 'href');
	$uid = $res_vendor['user_id'];
	$area_title = '{_viewing_profile}<div class="smaller">' . $vendorname_plain . '</div>';
	$page_title = SITE_NAME . ' - {_viewing_profile_for_vendor} ' . $vendorname_plain;
	$navcrumb = array();
	$navcrumb[""] = $vendorname_plain;
	
	// let's determine if this member has an active profile and active subscription
	if (!$ilance->subscription->has_active_subscription($res_vendor['user_id']) OR $res_vendor['status'] != 'active')
	{
		print_notice('{_profile_temporarily_inactive}', '{_sorry_the_enhanced_profile_page_for_this_member_is_temporarily_inactive}', 'javascript:history.back(1);', '{_back}');
		exit();        
	}
	
	// subscription title and role name
	$subscription = $ilance->subscription->print_subscription_title($res_vendor['user_id']);
	$rolename = $ilance->subscription_role->print_role($ilance->subscription_role->fetch_user_roleid($res_vendor['user_id']));
	$membersince = print_date(fetch_user('date_added', $uid), $ilconfig['globalserverlocale_globaldateformat'], 0, 0);
	// do we display "view portfolio" link under logo?
	$show['portfoliolink'] = 0;
	if ($ilance->portfolio->has_portfolio($res_vendor['user_id']) > 0)
	{
		$show['portfoliolink'] = 1;
	}
	$show['categorypulldown'] = 0;
	if ($ilance->profile->has_answered_profile_questions($uid))
	{
		$show['categorypulldown'] = 1;
	}
	// selling (or buying) profile logo / image
	$gender = fetch_user('gender', $res_vendor['user_id'], '', '', false);
	if ($gender == '' OR $gender == 'male')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	else if ($gender == 'female')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	$sql_attach = $ilance->db->query("
		SELECT attachid, filehash, filename, width, height
		FROM " . DB_PREFIX . "attachment
		WHERE user_id = '" . intval($res_vendor['user_id']) . "'
			AND visible = '1'
			AND attachtype = 'profile'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_attach) > 0)
	{
		$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
		$profile_logo = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
			? 'i/profile/' . $res_attach['filehash'] . '/' . ($res_attach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $res_attach['width']) . 'x' . ($res_attach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $res_attach['height']) . '_' . $res_attach['filename']
			: $ilpage['attachment'] . '?cmd=profile&amp;id=' . $res_attach['filehash']) . '" border="0" id="' . $res_attach['filehash'] . '" alt="' . handle_input_keywords($res_vendor['username']) . '" />';
	}
	// display city & state to members only
	$city_state = '';
	if (!empty($res_vendor['city']))
	{
		$city_state = ucfirst(stripslashes($res_vendor['city']));
	}
	if (!empty($res_vendor['state']))
	{
		if (!empty($res_vendor['city']))
		{
			$city_state .=  ', ' . ucfirst(stripslashes($res_vendor['state']));
		}
		else
		{
			$city_state .= ucfirst(stripslashes($res_vendor['state']));
		}
	}
	else
	{
		$city_state = '{_unknown}';
	}
	$country = $ilance->common_location->print_country_name($res_vendor['country'], $_SESSION['ilancedata']['user']['slng']);
	$lastseen = $ilance->common->last_seen($res_vendor['user_id']);
	$isonline = print_online_status($res_vendor['user_id']);
	$credentials_verified = $ilance->profile->fetch_verified_credentials($res_vendor['user_id']);
	$default_profile_question_groups = array();
	$sqlprofilegroups2 = $ilance->db->query("
		SELECT groupid,	name as groupname, description as groupdescription
		FROM " . DB_PREFIX . "profile_groups
		WHERE canremove = '0'
			AND visible = '1'
	");
	if ($ilance->db->num_rows($sqlprofilegroups2) > 0)
	{
		$row_count2 = 0;
		while ($row2 = $ilance->db->fetch_array($sqlprofilegroups2, DB_ASSOC))
		{
			if (isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$sqlquestions2 = $ilance->db->query("
					SELECT q.question, q.description, q.canverify, q.sort, q.inputtype, a.answer, a.isverified
					FROM " . DB_PREFIX . "profile_questions q
					LEFT JOIN " . DB_PREFIX . "profile_answers a ON (q.questionid = a.questionid)
					WHERE q.groupid = '" . $row2['groupid'] . "'
						AND a.user_id = '" . $res_vendor['user_id'] . "'
						AND a.answer != ''
						AND a.visible = '1'
					ORDER BY q.sort ASC
				");
			}
			else
			{
			      $sqlquestions2 = $ilance->db->query("
					SELECT q.question, q.description, q.canverify, q.sort, q.inputtype, a.answer, a.isverified
					FROM " . DB_PREFIX . "profile_questions q
					LEFT JOIN " . DB_PREFIX . "profile_answers a ON (q.questionid = a.questionid)
					WHERE q.groupid = '" . $row2['groupid'] . "'
						AND a.user_id = '" . $res_vendor['user_id'] . "'
						AND a.answer != ''
						AND a.visible = '1'
						AND q.guests = '1'
					ORDER BY q.sort ASC
				");
			}
			if ($ilance->db->num_rows($sqlquestions2) > 0)
			{
				while ($resqa = $ilance->db->fetch_array($sqlquestions2, DB_ASSOC))
				{
					switch ($resqa['inputtype'])
					{
						case 'yesno':
						{
							if ($resqa['answer'])
							{
								$resqa['answer'] = '{_yes}';
							}
							else
							{
								$resqa['answer'] = '{_no}';
							}
							break;
						}							
						case 'int':
						{
							$resqa['answer'] = print_string_wrap(stripslashes($resqa['answer']), 50);
							break;
						}							
						case 'textarea':
						{
							$resqa['answer'] = print_string_wrap(stripslashes($resqa['answer']), 50);
							break;
						}							
						case 'text':
						{
							$resqa['answer'] = print_string_wrap(stripslashes($resqa['answer']), 50);
							break;
						}							
						case 'pulldown':
						{
							$answers = $resqa['answer'];
							if (isset($answers) AND is_serialized($resqa['answer']))
							{
								$answers = unserialize($resqa['answer']);
							}
							$ansval = '';
							if (isset($answers) AND is_array($answers))
							{
								foreach ($answers AS $ans)
								{
									$ansval .= print_string_wrap(stripslashes($ans), 50) . ', ';
								}
							}
							else
							{
								$ansval .= print_string_wrap($answers, 50) . ', ';
							}
							$resqa['answer'] = mb_substr($ansval, 0, -2);
							break;
						}							
						case 'multiplechoice':
						{
							$answers = $resqa['answer'];
							if (isset($answers) AND is_serialized($resqa['answer']))
							{
								$answers = unserialize($resqa['answer']);
							}
							
							$ansval = '';
							if (isset($answers) AND is_array($answers))
							{
								foreach ($answers AS $ans)
								{
									$ansval .= print_string_wrap(stripslashes($ans), 50) . ', ';
								}
							}
							else
							{
								$ansval .= print_string_wrap($answers, 50) . ', ';       
							}
							$resqa['answer'] = mb_substr($ansval, 0, -2);
							break;
						}
					}
					
					$resqa['question'] = stripslashes($resqa['question']);
					$resqa['description'] = stripslashes($resqa['description']);
					if ($resqa['canverify'] AND $resqa['isverified'])
					{
						$resqa['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="{_verified}" border="0" />';
					}
					else if ($resqa['canverify'] AND $resqa['isverified'] == 0)
					{
						$resqa['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="{_not_verified}" border="0" />';
					}
					else if ($resqa['canverify'] == 0)
					{
						$resqa['is_verified'] = '';
					}
					$resqa['daysago'] = '3';
					$resqa['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					//$resqa['description'] = '{_get_an_in_depth_look_based_on_questions_answered_by_this_user_below}';
					$GLOBALS['default_profile_questions' . $row2['groupid']][] = $resqa;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS['no_default_profile_questions' . $row2['groupid']][] = 1;        
			}
			$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$default_profile_question_groups[] = $row2;
			$row_count2++;
		}
	}
	else
	{
		$no_profile_question_groups2 = 1;
	}
	$row_count = 0;
	$profile_question_groups = array();
	$sqlprofilegroups = $ilance->db->query("
		SELECT groupid,	name as groupname, description as groupdescription
		FROM " . DB_PREFIX . "profile_groups
		WHERE canremove = '1'
			AND visible = '1'
	");
	if ($ilance->db->num_rows($sqlprofilegroups) > 0)
	{
		while ($row = $ilance->db->fetch_array($sqlprofilegroups, DB_ASSOC))
		{
			$row_count2 = 0;
			$sqlquestions = $ilance->db->query("
				SELECT
				q.question,
				q.description,
				q.canverify,
				q.sort,
				q.inputtype,
				a.answer,
				a.isverified
				FROM " . DB_PREFIX . "profile_questions q
				LEFT JOIN " . DB_PREFIX . "profile_answers a ON (q.questionid = a.questionid)
				WHERE q.groupid = '" . $row['groupid'] . "'
					AND a.user_id = '" . $res_vendor['user_id'] . "'
					AND a.answer != ''
					AND a.visible = '1'
				ORDER BY q.sort ASC
			");
			if ($ilance->db->num_rows($sqlquestions) > 0)
			{
				while ($rows = $ilance->db->fetch_array($sqlquestions))
				{
					$rows['question'] = stripslashes($rows['question']);
					$rows['description'] = stripslashes($rows['description']);
					if ($rows['canverify'])
					{
						switch ($rows['inputtype'])
						{
							case 'yesno':
							{
								if ($rows['answer'])
								{
									$rows['answer'] = '{_yes}';
								}
								else
								{
									$rows['answer'] = '{_no}';
								}
								break;
							}                            
							case 'int':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}
							case 'textarea':
							{
								$rows['answer'] = stripslashes($rows['answer']);
								$rows['answer'] = print_string_wrap(nl2br($rows['answer']), 50);
								break;
							}                                
							case 'text':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}							
							case 'pulldown':
							{
								$answers = unserialize($rows['answer']);
								$ansval = '';
								foreach ($answers AS $ans)
								{
									$ansval .= print_string_wrap(stripslashes($ans), 50) . ', ';
								}
								$ansval = mb_substr($ansval, 0, -2);
								$rows['answer'] = $ansval;
								break;
							}							
							case 'multiplechoice':
							{
								$answers = unserialize($rows['answer']);
								$ansval = '';
								foreach ($answers AS $ans)
								{
									$ansval .= print_string_wrap(stripslashes($ans), 50) . ', ';
								}
								$ansval = mb_substr($ansval, 0, -2);
								$rows['answer'] = $ansval;
								break;
							}
						}
						if ($rows['canverify'] AND $rows['isverified'])
						{
							$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="{_verified}" border="0" />';
						}
						else if ($rows['canverify'] AND $rows['isverified'] == 0)
						{
							$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="{_not_verified}" border="0" />';
						}
						else if ($rows['canverify'] == 0 AND $rows['isverified'] == 0)
						{
							$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="{_not_verified}" border="0" />';
						}
					}
					else
					{
						$rows['is_verified'] = '';
						switch ($rows['inputtype'])
						{
							case 'yesno':
							{
								if ($rows['answer'])
								{
									$rows['answer'] = '{_yes}';
								}
								else
								{
									$rows['answer'] = '{_no}';
								}
								break;
							}							
							case 'int':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}
							case 'textarea':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}							
							case 'text':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}							
							case 'pulldown':
							{
								$rows['answer'] = print_string_wrap(stripslashes($rows['answer']), 50);
								break;
							}							
							case 'multiplechoice':
							{
								$answers = unserialize($rows['answer']);
								$ansval = '';
								foreach ($answers AS $ans)
								{
									$ansval .= print_string_wrap(stripslashes($ans), 50) . ', ';
								}
								$rows['answer'] = mb_substr($ansval, 0, -2);
								break;
							}
						}
					}
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$rows['daysago'] = '3';
					$GLOBALS['profile_questions' . $row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS['no_profile_questions' . $row['groupid']][] = 1;
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			//$row['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $row['groupid'] . "'", "name"));
			$profile_question_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$no_profile_question_groups = 1;
	}
	
	$memberinfo[] = $ilance->feedback->datastore($uid);
	
	// #### SKILLS #################################################
	require_once(DIR_CORE . 'functions_search.php');
	
	$skills = print_skills($uid, $showmaxskills = 500, $nourls = true);        

	// #### REGISTRATION QUESTIONS AND ANSWERS #####################
	$apihook_query_fields = $apihook_query_joins = $apihook_query_where = '';
	
	($apihook = $ilance->api('vendors_registration_questions_start')) ? eval($apihook) : false;
	
	$sqlreg = $ilance->db->query("
		SELECT q.guests, q.inputtype, q.question_" . $_SESSION['ilancedata']['user']['slng'] . " AS question, a.answer
		$apihook_query_fields
		FROM " . DB_PREFIX . "register_answers AS a,
		" . DB_PREFIX . "register_questions AS q
		$apihook_query_joins
		WHERE a.user_id = '".intval($uid)."'
			AND a.questionid = q.questionid
			AND q.visible = '1'
			AND q.profile = '1'
			$apihook_query_where
	");
	if ($ilance->db->num_rows($sqlreg) > 0)
	{
		while ($reg = $ilance->db->fetch_array($sqlreg, DB_ASSOC))
		{
			// show this answer to guests?
			if ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 OR $reg['guests'] == '1' AND empty($_SESSION['ilancedata']['user']['userid'])))
			{
				$reg['question'] = stripslashes($reg['question']);
				switch ($reg['inputtype'])
				{
					case 'yesno':
					{
						if ($reg['answer'])
						{
							$reg['answer'] = '{_yes}';
						}
						else
						{
							$reg['answer'] = '{_no}';
						}
						break;
					}                                       
					case 'int':
					{
						$reg['answer'] = print_string_wrap(stripslashes($reg['answer']), 50);
						break;
					}                                        
					case 'textarea':
					{
						$reg['answer'] = print_string_wrap(stripslashes($reg['answer']), 50);
						break;
					}                                        
					case 'text':
					{
						$reg['answer'] = print_string_wrap(stripslashes($reg['answer']), 50);
						break;
					}                                        
					case 'pulldown':
					{
						if (!empty($reg['answer']))
						{
							$answers = unserialize($reg['answer']);
							$answer = '';
							foreach ($answers AS $answered)
							{
								$answer .= print_string_wrap(stripslashes($answered), 50) . ', ';
							}
							$reg['answer'] = mb_substr($answer, 0, -2);
						}
						else
						{
							$reg['answer'] = '&nbsp;';
						}
						break;
					}                                        
					case 'multiplechoice':
					{
						if (!empty($reg['answer']) AND mb_strlen($reg['answer']) > 5)
						{
							$answers = unserialize($reg['answer']);
							$answer = '';
							foreach ($answers AS $answered)
							{
								$answer .= print_string_wrap(stripslashes($answered), 50) . ', ';
							}
							$reg['answer'] = mb_substr($answer, 0, -2);
						}
						else
						{
							$reg['answer'] = '&nbsp;';
						}
						break;
					}
				}
			}
			else
			{
				$reg = array();
			}
			$registration_questions[] = $reg;
		}
	}
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$allprofilesurl = print_username($uid, 'url') . '?cid=-1#categories';
	}
	else
	{
		$allprofilesurl = print_username($uid, 'url') . '&amp;cid=-1#categories';
	}
	$profilevideo = $ilance->profile->print_profile_video($uid);
	$rateperhour = $ilance->currency->format(fetch_user('rateperhour', $uid), fetch_user('currencyid', $uid));
	$show['openingstatement'] = false;
	$openingstatement = fetch_user('profileintro', $uid);
	if (!empty($openingstatement))
	{
		$show['openingstatement'] = true;
		$openingstatement = htmlspecialchars_decode($openingstatement);
	}
	$vendorname_plain = construct_seo_url_name($vendorname_plain);
	$fullprofileurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid;
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$fullprofileurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain;
	}
	$jobhistoryurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid . '&amp;jobhistory=1';
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$jobhistoryurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain . '/job-history';
	}
	$show['invite_to_bid'] = (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'servicebid') == 'yes') ? true : false;
	$localtime = print_date(DATETIME24H, 'h:i A', true, false, fetch_user('timezone', $uid));
	$pprint_array = array('localtime','jobhistoryurl','uid','fullprofileurl','openingstatement','vendorname_plain','skills','columnas','feedbacktabs','membersince','rateperhour','profilevideo','city_state','feedback_pulldown2','allprofilesurl','rolename','dba','dba_pulldown','feedbackviewtype','feedback_pulldown','category_pulldown','subscription','overall_rating','contact_number','profile_logo','credentials_verified','title','firstname','lastname','prevnext','reviews','currency','profile_summary','profile_category','vendorname','vendor_id','isonline','lastseen','country','revenue','stars','id');
	
	($apihook = $ilance->api('members_feedback_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'member_full_profile.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'feedback');
	$ilance->template->parse_loop('main', 'registration_questions');
	$ilance->template->parse_loop('main', 'memberinfo');
	$ilance->template->parse_loop('main', 'profile_question_groups');
	$ilance->template->parse_loop('main', 'default_profile_question_groups');
	@reset($default_profile_question_groups);
	while ($i = @each($default_profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'default_profile_questions' . $i['value']['groupid']);
	}                        
	@reset($profile_question_groups);
	while ($i = @each($profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
	}
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### MEMBER FEEDBACK REVIEW #################################################
else if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] > 0 OR !isset($ilance->GPC['feedback']))
{
        $ilance->GPC['feedback'] = isset($ilance->GPC['feedback']) ? $ilance->GPC['feedback'] : '1';        
	if ($ilance->db->num_rows($sql_vendor) <= 0)
        {
		$area_title = '{_invalid_vendor_id_warning_menu}';
		$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
                print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	require_once(DIR_CORE . 'functions_search.php');
	$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
	$vendorname_plain = stripslashes($res_vendor['username']);
	$vendorname = print_username($res_vendor['user_id'], 'href');
	$uid = $res_vendor['user_id'];
	$area_title = '{_viewing_feedback_profile}<div class="smaller">' . $vendorname_plain . '</div>';
	$page_title = SITE_NAME . ' - {_viewing_feedback_profile_for} ' . $vendorname_plain;
	$navcrumb = array();
	$navcrumb[""] = $vendorname_plain;
	
	// let's determine if this member has an active profile and active subscription
	if (!$ilance->subscription->has_active_subscription($res_vendor['user_id']) OR $res_vendor['status'] != 'active')
	{
		print_notice('{_profile_temporarily_inactive}', '{_sorry_the_enhanced_profile_page_for_this_member_is_temporarily_inactive}', 'javascript:history.back(1);', '{_back}');
		exit();        
	}
	// do we display "view portfolio" link under logo?
	$show['portfoliolink'] = (($ilance->portfolio->has_portfolio($res_vendor['user_id']) > 0) AND ($ilconfig['portfoliodisplay_enabled'] == 1)) ? 1 : 0;
	$gender = fetch_user('gender', $res_vendor['user_id'], '', '', false);
	if ($gender == '' OR $gender == 'male')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	else if ($gender == 'female')
	{
		$profile_logo = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif' . '" border="0" alt="" width="80" height="80" />';
	}
	$sql_attach = $ilance->db->query("
		SELECT attachid, filehash, filename, width, height
		FROM " . DB_PREFIX . "attachment
		WHERE user_id = '" . intval($res_vendor['user_id']) . "'
			AND visible = '1'
			AND attachtype = 'profile'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_attach) > 0)
	{
		$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
		$profile_logo = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
			? 'i/profile/' . $res_attach['filehash'] . '/' . ($res_attach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $res_attach['width']) . 'x' . ($res_attach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $res_attach['height']) . '_' . $res_attach['filename']
			: $ilpage['attachment'] . '?cmd=profile&amp;id=' . $res_attach['filehash']) . '" border="0" id="' . $res_attach['filehash'] . '" alt="' . handle_input_keywords($vendorname_plain) . '" />';
	}
	$membersince = print_date(fetch_user('date_added', $uid), $ilconfig['globalserverlocale_globaldateformat']);
	$page = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$memberinfo[] = $ilance->feedback->datastore($uid);
	$criteria = $ilance->feedback->criteria($uid, $_SESSION['ilancedata']['user']['slng']);
	$limit = ' ORDER BY id DESC LIMIT ' . (($page - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	$groupby = '';//' GROUP BY project_id';
	$counter = ($page - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$show['no_feedback_results'] = true;
	if (isset($ilance->GPC['feedback']))
	{
		switch ($ilance->GPC['feedback'])
		{
			// All feedback
			default:
			case 1:
			{
				$sqlquery['fields'] = "from_user_id AS uid, response, project_id, comments, date_added, type";
				$sqlquery['where'] = "for_user_id = '" . intval($uid) . "'";
				break;
			}
			// Feedback as a seller
			case 2:
			{
				$sqlquery['fields'] = "from_user_id AS uid, response, project_id, comments, date_added, type";
				$sqlquery['where'] = "for_user_id = '" . intval($uid) . "' AND type = 'seller'";
				break;
			}
			// Feedback as a buyer
			case 3:
			{
				$sqlquery['fields'] = "from_user_id AS uid, response, project_id, comments, date_added, type";
				$sqlquery['where'] = "for_user_id = '" . intval($uid) . "' AND type = 'buyer'";
				break;
			}
			// Feedback left for others
			case 4:
			{
				$sqlquery['fields'] = "for_user_id AS uid, response, project_id, comments, date_added, type";
				$sqlquery['where'] = "from_user_id = '" . intval($uid) . "'";
				break;
			}
		}
	}
	$ilance->GPC['period'] = isset($ilance->GPC['period']) ? $ilance->GPC['period'] : '';
	$periodx = $ilance->GPC['period'];
	if ($periodx == '-1')
	{
		$periodx = '19';
	}
	$pos_type = $neu_type = $neg_type = '';
	
	if ((isset($ilance->GPC['period']) AND $ilance->GPC['period'] == -1))
	{
		$pos_type = $memberinfo['pos365'];
		$neu_type = $memberinfo['neu365'];
		$neg_type = $memberinfo['neg365'];
		$sqlquery['where'] .= " AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 365";
	}
	else if (isset($ilance->GPC['period']) AND $ilance->GPC['period'] == 15)
	{
		$pos_type = $memberinfo['pos30'];
		$neu_type = $memberinfo['neu30'];
		$neg_type = $memberinfo['neg30'];
		$sqlquery['where'] .= " AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 30";
	}
	else if (isset($ilance->GPC['period']) AND $ilance->GPC['period'] == 18)
	{
		$pos_type = $memberinfo['pos180'];
		$neu_type = $memberinfo['neu180'];
		$neg_type = $memberinfo['neg180'];
		$sqlquery['where'] .= " AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 180";
	}
	else if (isset($ilance->GPC['period']) AND $ilance->GPC['period'] == 19)
	{
		$pos_type = $memberinfo['pos365'];
		$neu_type = $memberinfo['neu365'];
		$neg_type = $memberinfo['neg365'];
		$sqlquery['where'] .= " AND TO_DAYS('" . DATETIME24H . "') - TO_DAYS(date_added) <= 365";
	}
	else
	{
		$pos_type = $memberinfo['pos'];
		$neu_type = $memberinfo['neu'];
		$neg_type = $memberinfo['neg'];
	}
	$periodsql = ''; // show all history
	$typesql = (isset($ilance->GPC['type']) AND $ilance->GPC['type'] != 'all') ? "AND response = '" . $ilance->db->escape_string($ilance->GPC['type']) . "'" : '';
	$result2 = $ilance->db->query("
		SELECT $sqlquery[fields]
		FROM " . DB_PREFIX . "feedback
		WHERE $sqlquery[where]
		$periodsql
		$typesql
		");
	$number = $ilance->db->num_rows($result2);
	$sqlres = $ilance->db->query("
		SELECT $sqlquery[fields]
		FROM " . DB_PREFIX . "feedback
		WHERE $sqlquery[where]
		$periodsql
		$typesql
		$limit
	");
	if ($ilance->db->num_rows($sqlres) > 0)
	{
		$row_count = 0;
		$feedback = array();
		$api_hook_query_fields = '';
		
		($apihook = $ilance->api('members_feedback_history_sql_init')) ? eval($apihook) : false;
		
		while ($row = $ilance->db->fetch_array($sqlres, DB_ASSOC))
		{
			$sql_project = $ilance->db->query("
				SELECT project_title, project_state, project_details, user_id, filtered_auctiontype, buynow, buynow_price, buynow_qty, reserve, cid, currencyid$api_hook_query_fields
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $row['project_id'] . "'
			");
			if ($ilance->db->num_rows($sql_project) > 0)
			{
				$res_project = $ilance->db->fetch_array($sql_project, DB_ASSOC);
				if ($res_project['project_state'] == 'service')
				{
					$pagetype = $ilpage['rfp'];
					$row['listingtype'] = '<div>{_service}</div><div class="gray" style="padding-top:10px">' . $ilance->auction->print_auction_bit($row['project_id'], $res_project['filtered_auctiontype'], $res_project['project_details'], $res_project['project_state'], $res_project['buynow'], $res_project['reserve'], $res_project['cid']) . '</div>';
				}
				else
				{
					$pagetype = $ilpage['merch'];
					$row['listingtype'] = '<div>{_item}</div><div class="gray" style="padding-top:10px">' . $ilance->auction->print_auction_bit($row['project_id'], $res_project['filtered_auctiontype'], $res_project['project_details'], $res_project['project_state'], $res_project['buynow'], $res_project['reserve'], $res_project['cid']) . '</div>';
				}
				$row['project_title'] = stripslashes(handle_input_keywords($res_project['project_title'])) . '&nbsp;(#' . $row['project_id'] . ')';
				$row['viewitem'] = '<a href="' . HTTP_SERVER .  $pagetype . '?id=' . $row['project_id'] . '">{_view_listing}</a>';
				switch ($ilance->GPC['feedback'])
				{
					// all feedback
					default:
					case 1:
					{
						if ($res_project['project_state'] == 'product')
						{
							$seller_id = intval($uid);
							$buyer_id = $row['uid'];
						}
						else
						{
							$seller_id = $row['uid'];
							$buyer_id = intval($uid);
						}
						$row['wintype'] = $ilance->bid->fetch_auction_win_type($row['project_id'], $res_project['project_state'], $res_project['filtered_auctiontype'], $res_project['project_details'], $seller_id, $buyer_id);
						break;
					}
					// feedback as a seller
					case 2:
					{
						if ($res_project['project_state'] == 'product')
						{
							$seller_id = intval($uid);
							$buyer_id = $row['uid'];
						}
						else
						{
							$seller_id = $row['uid'];
							$buyer_id = intval($uid);
						}
						$row['wintype'] = $ilance->bid->fetch_auction_win_type($row['project_id'], $res_project['project_state'], $res_project['filtered_auctiontype'], $res_project['project_details'], $seller_id, $buyer_id);
						break;
					}
					// feedback as a buyer
					case 3:
					{
						if ($res_project['project_state'] == 'product')
						{
							$seller_id = $row['uid'];
							$buyer_id = intval($uid);
						}
						else
						{
							$seller_id = intval($uid);
							$buyer_id = $row['uid'];
						}
						$row['wintype'] = $ilance->bid->fetch_auction_win_type($row['project_id'], $res_project['project_state'], $res_project['filtered_auctiontype'], $res_project['project_details'], $seller_id, $buyer_id);
						break;
					}
					// feedback left for others
					case 4:
					{
						if ($res_project['project_state'] == 'product')
						{
							$seller_id = intval($uid);
							$buyer_id = $row['uid'];
						}
						else
						{
							$seller_id = $row['uid'];
							$buyer_id = intval($uid);
						}
						$row['wintype'] = $ilance->bid->fetch_auction_win_type($row['project_id'], $res_project['project_state'], $res_project['filtered_auctiontype'], $res_project['project_details'], $seller_id, $buyer_id);
						break;
					}
				}
				$row['membertype'] = $ilance->feedback->print_from_column_bit($ilance->GPC['feedback'], $res_project['project_state'], $row['type']);
				$row['member'] = print_username($row['uid'], 'href');
				$row['commentresponse'] = '';
				$row['mutuallywithdrawresponse'] = '';
				if ($res_project['project_state'] == 'product')
				{
					switch ($show['wintype'])
					{
						case 'buynow':
						{
							$row['buyershipcost'] = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . $row['project_id'] . "' AND buyer_id = '" . intval($buyer_id) . "' AND owner_id = '" . intval($seller_id) . "'", "buyershipcost");
							break;
						}
						case 'highbid':
						{
							$row['buyershipcost'] = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "project_id = '" . $row['project_id'] . "' AND user_id = '" . intval($buyer_id) . "' AND project_user_id = '" . intval($seller_id) . "'", "buyershipcost");
							break;
						}
					}
					if ($row['buyershipcost'] > 0)
					{
						$row['shippingtotal'] = $ilance->currency->format($row['buyershipcost'], $res_project['currencyid']);
					}
					else
					{
						$row['shippingtotal'] = '-';	
					}
				}
				else
				{
					$row['shippingtotal'] = '-';
				}
				$amount = $ilance->bid->fetch_auction_win_amount($row['project_id'], $seller_id, $buyer_id);
				$row['total'] = $ilance->currency->format($amount, $res_project['currencyid']);
				
				($apihook = $ilance->api('members_feedback_history_sql_while_loop')) ? eval($apihook) : false;
			}
			else
			{
				$res_project = array();
				$row['date'] = $row['listingtype'] = $row['icon'] = $row['wintype'] = $row['member'] = $row['shippingtotal'] = '-';
				$row['commentresponse'] = $row['mutuallywithdrawresponse'] = $row['membertype'] = '';
				$row['project_title'] = '{_delisted}';
				$row['viewitem'] = '<span class="gray">{_delisted}</span>';
				$row['total'] = '';
				
				($apihook = $ilance->api('members_feedback_else_end')) ? eval($apihook) : false;
			}
			$row['comment'] = (!empty($row['comments']) ? stripslashes(handle_input_keywords(print_string_wrap($row['comments'], 50))) : '<span class="litegray">{_no_comment}</span>');
			$row['date'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			switch ($row['response'])
			{
				case 'positive':
				{
					$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/positive.png" align="absmiddle" alt="{_positive}" width="20" />';
					break;
				}                                        
				case 'neutral':
				{
					$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/neutral.png" align="absmiddle" alt="{_neutral}" width="20" />';
					break;
				}                                        
				case 'negative':
				{
					$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/negative.png" align="absmiddle" alt="{_negative}" width="20" />';
					break;
				}                                        
				default:
				{
					$row['icon'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/neutral.png" align="absmiddle" alt="{_pending}" width="20" />';
					break;
				}
			}
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';                                
			$feedback[] = $row;
			$row_count++;
		}
		$show['no_feedback_results'] = false;
	}
	$typebit = !empty($ilance->GPC['type']) ? '&amp;type=' . $ilance->GPC['type'] : '';
	$periodbit = (isset($ilance->GPC['period']) AND !empty($ilance->GPC['period'])) ? '&amp;period=' . $ilance->GPC['period'] : '';
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$feedbackbit = '';
		$cidbit = '?cid=0';
		if (isset($cid) AND $cid > 0)
		{
			$cidbit = '?cid=' . $cid;
		}
		if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] > 1)
		{
			$feedbackbit = '-feedback-' . intval($ilance->GPC['feedback']);
		}
		$scriptpage = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $ilance->GPC['id'] . $feedbackbit . $cidbit . $typebit . $periodbit;    
	}
	else
	{
		$cidbit = $feedbackbit = '';
		if (isset($cid) AND $cid > 0)
		{
			$cidbit = '&amp;cid=' . $cid;
		}
		if (isset($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] > 1)
		{
			$feedbackbit = '&amp;feedback=' . $ilance->GPC['feedback'];
		}
		$scriptpage = $ilpage['members'] . '?id=' . $ilance->GPC['id'] . $feedbackbit . $cidbit . $typebit . $periodbit;    
	}
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $page, $counter, $scriptpage); 
	
	$feedbackviewtype = '{_all_feedback_received}';
	$tab = intval($ilance->GPC['feedback']);
	if ($tab == 0)
	{
		$tab = 1;
	}
	$feedbacktabs = $ilance->feedback->print_feedback_tabs($tab, $vendorname_plain);
	$columnas = $ilance->feedback->print_feedback_columnbit($tab);
	$show['openingstatement'] = false;
	$openingstatement = fetch_user('profileintro', $uid);
	if (!empty($openingstatement))
	{
		$show['openingstatement'] = true;
		$openingstatement = htmlspecialchars_decode($openingstatement);
	}
	$vendorname_plain = construct_seo_url_name($vendorname_plain);
	$fullprofileurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid . '&amp;profile=1';
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$fullprofileurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain . '/profile';
	}
	$jobhistoryurl = HTTP_SERVER . $ilpage['members'] . '?id=' . $uid . '&amp;jobhistory=1';
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$jobhistoryurl = HTTP_SERVER . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . $vendorname_plain . '/job-history';
	}
	// #### item watchlist and bid retract view logic ###########################
	$show['buyercanretractbid'] = false;
	$show['sellercanretractbid'] = false;
	if (!empty($_SESSION['ilancedata']['user']['userid']))
	{
		$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($uid);
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidretracts') > 0)
		{
			$show['buyercanretractbid'] = true;
		}
	}
	if ($ilance->permissions->check_access($uid, 'bidretracts') > 0)
	{
		$show['sellercanretractbid'] = true;
	}
	$project_user_id = $uid;
	$number = number_format($number);
	$importedfeedback = $ilance->feedback_import->print_imported_feedback($uid, 'feedback');
	$pprint_array = array('importedfeedback','periodx','scriptpage','pos_type','neu_type','neg_type','number','jobhistoryurl','project_user_id', 'uid','fullprofileurl','openingstatement','vendorname_plain','skills','columnas','feedbacktabs','membersince','rateperhour','profilevideo','city_state','feedback_pulldown2','allprofilesurl','rolename','dba','dba_pulldown','feedbackviewtype','feedback_pulldown','category_pulldown','subscription','overall_rating','contact_number','profile_logo','credentials_verified','title','firstname','lastname','prevnext','reviews','currency','profile_summary','profile_category','vendorname','vendor_id','isonline','lastseen','country','revenue','stars','id');
	
	($apihook = $ilance->api('members_feedback_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'member_feedback_profile.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'criteria');
	$ilance->template->parse_loop('main', 'memberinfo');
	$ilance->template->parse_loop('main', 'feedback');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	$area_title = '{_invalid_vendor_id_warning_menu}';
	$page_title = SITE_NAME . ' - {_invalid_vendor_id_warning_menu}';
        print_notice('{_invalid_vendor_profile_id}', '{_your_requested_action_cannot_be_completed_due_to_an_invalid_vendors_id}' . "<br /><br />" . '{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>