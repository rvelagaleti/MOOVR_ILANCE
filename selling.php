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
		'tabfx',
		'inline_edit',
		'wysiwyg',
		'ckeditor',
		'jquery',
		'modal'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'selling');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_wysiwyg.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[selling]" => $ilcrumbs["$ilpage[selling]"]);
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['selling'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)));
	exit();
}
if (!empty($ilance->GPC['crypted']))
{
	$uncrypted = decrypt_url($ilance->GPC['crypted']);
	// #### seller marking listing as being shipped ########################
	if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasshipped' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['sellerid']) AND $uncrypted['sellerid'] > 0 AND isset($uncrypted['buyerid']) AND $uncrypted['buyerid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0 AND isset($uncrypted['mode']))
	{
		$trackingnumber = isset($ilance->GPC['trackingnumber']) ? $ilance->GPC['trackingnumber'] : '';
		$ilance->shipping->mark_listing_as_shipped($uncrypted['pid'], $uncrypted['bid'], $uncrypted['sellerid'], $uncrypted['buyerid'], $uncrypted['mode'], $trackingnumber);
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh($ilance->GPC['returnurl']);
			exit();
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management');
		exit();
	}
	// #### seller marking listing as being unshipped ######################
	else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunshipped' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['sellerid']) AND $uncrypted['sellerid'] > 0 AND isset($uncrypted['buyerid']) AND $uncrypted['buyerid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0 AND isset($uncrypted['mode']))
	{
		$ilance->shipping->mark_listing_as_unshipped($uncrypted['pid'], $uncrypted['bid'], $uncrypted['sellerid'], $uncrypted['buyerid'], $uncrypted['mode']);
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh($ilance->GPC['returnurl']);
			exit();
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management');
		exit();
	}
	// #### seller marking outside direct payment listing from buyer as being paid
	else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markaspaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
	{
		$winnermarkedaspaidmethod = isset($ilance->GPC['winnermarkedaspaidmethod']) ? $ilance->GPC['winnermarkedaspaidmethod'] : '';
		$ilance->payment->mark_listing_as_paid($uncrypted['pid'], $uncrypted['bid'], $winnermarkedaspaidmethod);
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh($ilance->GPC['returnurl']);
			exit();
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management');
		exit();
	}
	// #### seller marking outside payment as unpaid
	else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunpaid' AND isset($uncrypted['pid']) AND $uncrypted['pid'] > 0 AND isset($uncrypted['bid']) AND $uncrypted['bid'] > 0)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET winnermarkedaspaid = '0',
			winnermarkedaspaidmethod = '',
			winnermarkedaspaiddate = '0000-00-00 00:00:00'
			WHERE project_id = '" . intval($uncrypted['pid']) . "'
				AND bid_id = '" . intval($uncrypted['bid']) . "'
			LIMIT 1
		");
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh($ilance->GPC['returnurl']);
			exit();
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management');
		exit();
	}
}

($apihook = $ilance->api('selling_top')) ? eval($apihook) : false;
	
// #### CREATING OR UPDATING PRODUCT AUCTION ###################################
if ((isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'new-item' AND isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0 OR isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'product-management' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($ilance->GPC['rfpid']) AND $ilance->GPC['rfpid'] > 0)))
{
	include_once(DIR_SERVER_ROOT . 'selling_newitem.php');
}
// #### BID MANAGEMENT: ACCEPT AWARD, DECLINE AWARD, ETC #######################
else if ((isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bid-management' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'bid-management'))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	if (isset($uncrypted['sub']) AND $uncrypted['sub'] == '_accept-award' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$area_title = '{_bid_service_auction_acceptance_menu}';
		$page_title = SITE_NAME . ' - {_bid_service_auction_acceptance_menu}';
		// awarding bid_id
		$bidid = intval($uncrypted['id']);
		$pid = isset($uncrypted['pid']) ? intval($uncrypted['pid']) : 0;
		// construct auction system
		// the service auction award accept feature will engage the final value fee
		// for the total amount originally awarded and accepted by both parties
		if ($ilance->auction_award->service_auction_award_accept($bidid, $_SESSION['ilancedata']['user']['userid'], $pid))
		{
			$usingescrow = fetch_auction('filter_escrow', $pid);
			$show['escrow'] = ($usingescrow == '1') ? 1 : 0;
			$pprint_array = array('remote_addr','rid','default_exchange_rate');
			$ilance->template->fetch('main', 'selling_award_accepted.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice('{_invalid_award_request_and_or_auction_state}', '{_were_sorry_your_request_cannot_be_completed_due_to_invalid_credentials_for_the_action_you_are_attempting_to_make}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['buying'], '{_buying_activity}');
			exit();
		}
	}
	else if (isset($uncrypted['sub']) AND $uncrypted['sub'] == '_decline-award' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$area_title = '{_bid_service_auction_decline_menu}';
		$page_title = SITE_NAME . ' - {_bid_service_auction_decline_menu}';
		$bidid = intval($uncrypted['id']);
		$pid = isset($uncrypted['pid']) ? intval($uncrypted['pid']) : 0;
		if ($ilance->auction_award->service_auction_award_decline($bidid, $_SESSION['ilancedata']['user']['userid'], $pid))
		{
			$usingescrow = fetch_auction('filter_escrow', intval($pid));
			$show['escrow'] = ($usingescrow == '1') ? 1 : 0;
			$ilance->template->fetch('main', 'selling_award_declined.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array('remote_addr','rid','default_exchange_rate'));
			exit();
		}
		else
		{
			print_notice('{_invalid_award_request_and_or_auction_state}', '{_were_sorry_your_request_cannot_be_completed_due_to_invalid_credentials_for_the_action_you_are_attempting_to_make}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['buying'], '{_buying_activity}');
			exit();
		}
	}
}
// #### SELLING PROFILE MENU: SKILLS ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'skills')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'profile'
	);
	$area_title = '{_skills}';
	$page_title = SITE_NAME . ' - {_skills}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_skills}';
	$show['leftnav'] = true;
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceprofile') == 'no')
	{
		$area_title = '{_access_denied_to_service_profile_menu}';
		$page_title = SITE_NAME . ' - {_access_denied_to_service_profile_menu}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceprofile'));
		exit();
	}
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update')
	{
		$selectedsids = !empty($ilance->GPC['sid']) ? count($ilance->GPC['sid']) : 0;
		if (!($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'maxskillscat') >= $selectedsids))
		{
			print_notice('{_access_denied}', '{_you_selected_more_skill_categories_than_your_current_subscription_permits}', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('maxskillscat'));
			exit();
		}
		if (!empty($ilance->GPC['sid']) AND is_array($ilance->GPC['sid']))
		{
			// delete all current skills for this user
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "skills_answers
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			// insert skills based on user selection
			foreach ($ilance->GPC['sid'] AS $cid => $value)
			{
				$ilance->db->query("
					INSERT INTO " . DB_PREFIX . "skills_answers
					(aid, cid, user_id)
					VALUES
					(NULL,
					'" . intval($cid) . "',
					'" . $_SESSION['ilancedata']['user']['userid'] . "')
				", 0, null, __FILE__, __LINE__);
			}
			// Bug fix: http://www.ilance.com/forum/project.php?issueid=874
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET displayprofile = '1'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			print_notice('{_updated}', '{_you_have_successfully_updated_your_skill_attributes}', $ilpage['selling'] . '?cmd=skills', '{_skills}');
			exit();
		}
	}
	// skills category limit (based on maxskillscat subscription permission)
	$maxskillscat = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'maxskillscat');
	$skills_selection = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], 0, true);
	$ilance->template->fetch('main', 'selling_profile_skills.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'profile_question_groups');
	$ilance->template->parse_loop('main', 'default_profile_question_groups');
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('maxskillscat','skills_selection','profilecatlimit','rateperhour','uploadlogo','profilevideourl','displayprofile_cb','displayprofile','category_pulldown','multiple_category_select','question_answer_js','attachment_style');
	
	($apihook = $ilance->api('selling_profile_skills_start')) ? eval($apihook) : false;
	
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### SELLING PROFILE MENU ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'profile')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'profile'
	);
	$area_title = '{_service_selling_profile_creation_menu}';
	$page_title = SITE_NAME . ' - {_service_selling_profile_creation_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_selling_profile}';
	$show['leftnav'] = true;
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true, '', '', 0, -1);
	$headinclude .= "<script type=\"text/javascript\">
<!--
function customImage(imagename, imageurl, errors)
{
	document[imagename].src = imageurl;
	if (!haveerrors && errors)
	{
		haveerrors = errors;
		alert_js(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
	}
}
//-->
</script>
";
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceprofile') == 'no')
	{
		$area_title = '{_access_denied_to_service_profile_menu}';
		$page_title = SITE_NAME . ' - {_access_denied_to_service_profile_menu}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceprofile'));
		exit();
	}
	$headinclude .= "<script type=\"text/javascript\">
<!--
function validatecustomform(f)
{
	haveerrors = 0;
";
	$cid = 0;
	if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
	{
		$cid = intval($ilance->GPC['cid']);
	}
	$gender = fetch_user('gender', $_SESSION['ilancedata']['user']['userid'], '', '', false);
	if ($gender == '' OR $gender == 'male')
	{
		$profile_logo = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif';
	}
	else if ($gender == 'female')
	{
		$profile_logo = $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif';
	}
	$sql_attach = $ilance->db->query("
		SELECT attachid, filehash
		FROM " . DB_PREFIX . "attachment
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND visible = '1'
			AND attachtype = 'profile'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_attach) > 0)
	{
		$res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
		$profile_logo = $ilpage['attachment'] . '?cmd=profile&amp;id=' . $res_attach['filehash'];
	}
	// default profile questions defined by admin (required fields)
	$sqlprofilegroups2 = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_groups
		WHERE canremove = '0' OR cid = '-1'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlprofilegroups2) > 0)
	{
		$row_count2 = 0;
		while ($row2 = $ilance->db->fetch_array($sqlprofilegroups2, DB_ASSOC))
		{
			$sqlquestions2 = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE groupid = '" . $row2['groupid'] . "'
					AND visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlquestions2) > 0)
			{
				$show['defaultprofilequestions'] = true;
				$row_count2 = 0;
				while ($rows2 = $ilance->db->fetch_array($sqlquestions2, DB_ASSOC))
				{
					$rows2['question_description'] = '<div style="padding-bottom:3px"><strong>' . handle_input_keywords(stripslashes($rows2['question'])) . '</strong></div><div style="padding-bottom:3px" class="gray">' . handle_input_keywords(stripslashes($rows2['description'])) . '</div>';
					$rows2['question_answer_error'] = '<img name="question' . $rows2['questionid'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="'.'{_this_form_field_is_required}'.'" />';
					$headinclude .= ($rows2['required']) ? "(fetch_js_object('question" . $rows2['questionid'] . "').value.length < 1) ? customImage(\"question".$rows2['questionid']."error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"question".$rows2['questionid']."error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false); " : '';
					// fetch answer
					$sqlqa = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "profile_answers
						WHERE questionid = '" . $rows2['questionid'] . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND visible = '1'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sqlqa) > 0)
					{
						$resqa = $ilance->db->fetch_array($sqlqa);
						$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
					}
					else
					{
						$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
					}
					// can question be verified?
					if ($rows2['canverify'])
					{
						// fetch answer to the question
						$sqla = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "profile_answers
							WHERE questionid = '" . $rows2['questionid'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqla) > 0)
						{
							$resq = $ilance->db->fetch_array($sqla, DB_ASSOC);
							$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
							if ($resq['isverified'] AND $resq['invoiceid'] > 0)
							{
								$date1split = explode(' ', $resq['verifyexpiry']);
								$date2split = explode('-', $date1split[0]);
								$expiredays = $ilance->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
								$rows2['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="" border="0" />';
								$rows2['action'] = '';
								$rows2['renew'] = $ilance->language->construct_phrase('{_expires_in_x_days}', $expiredays);
							}
							else if ($resq['isverified'] == 0 AND $resq['invoiceid'] > 0)
							{
								$rows2['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows2['action'] = '{_pending}';
								$rows2['renew'] = '';
							}
							else if ($rows2['canverify'] AND $resq['isverified'] == 0 AND !empty($resq['answer']))
							{
								$rows2['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows2['action'] = '<input type="submit" name="verifyprofile[' . $rows2['questionid'] . '|' . $rows2['verifycost'] . ']" value="' . $ilance->currency->format($rows2['verifycost'], 0, false, false, false) . '" class="buttons_smaller" />';
								$rows2['renew'] = '';
							}
							else if ($rows2['canverify'] AND $resq['isverified'] == 0 AND empty($resq['answer']))
							{
								$rows2['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows2['action'] = $ilance->currency->format($rows2['verifycost']);
								$rows2['renew'] = '';
							}
							$rows2['verify1'] = '<fieldset class="fieldset" style="width:150px; padding:9px"><legend>' . $rows2['is_verified'] . '</legend><div align="left" class="gray">' . $rows2['action'] . '</div><div class="gray" align="left">' . $rows2['renew'] . '</div></fieldset>';
						}
						else
						{
							$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
							$rows2['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
							$rows2['action'] = '<input type="submit" name="verifyprofile[' . $rows2['questionid'] . '|' . $rows2['verifycost'] . ']" value="' . $ilance->currency->format($rows2['verifycost'], 0, false, false, false) . '" class="buttons_smaller" disabled="disabled" />';
							$rows2['verify1'] = '<fieldset class="fieldset" style="width:150px; padding:9px"><legend>' . $rows2['is_verified'] . '</legend><div align="left" style="padding:3px">' . $rows2['action'] . '</div></fieldset>';
						}
					}
					else
					{
						$sqla = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "profile_answers
							WHERE questionid = '" . $rows2['questionid'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqla) > 0)
						{
							$resq = $ilance->db->fetch_array($sqla, DB_ASSOC);
							$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
							$rows2['action'] = $rows2['is_verified'] = $rows2['verify1'] = '';
						}
						else
						{
							$rows2['is_verified'] = $rows2['action'] = $rows2['verify1'] = '';
							$rows2['question_answer_input'] = $ilance->mycp->construct_profile_input($rows2['questionid']);
						}
					}
					$rows2['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS['default_profile_questions' . $row2['groupid']][] = $rows2;
					$row_count2++;
				}
			}
			$row2['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $row2['groupid'] . "'", "name"));
			$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
			$default_profile_question_groups[] = $row2;
			$row_count2++;
		}
	}
	else
	{
		$no_profile_question_groups2 = 1;
		$show['defaultprofilequestions'] = false;
	}
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$multiple_category_select = $ilance->categories_pulldown->print_cat_pulldown($cid, 'service', 'levelmulti', 'subcategories', 0, $_SESSION['ilancedata']['user']['slng'], 1, 'sellingprofile', 0, 0, 0, '540px', 0, 0, 0, false, false, $ilance->categories->cats);
	$attachment_style = '';
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'yes')
	{
		$hiddeninput = array(
			'attachtype' => 'profile',
			'project_id' => 0,
			'user_id' => $_SESSION['ilancedata']['user']['userid'],
			'category_id' => 0,
			'filehash' => md5(time()),
			'max_filesize' => $ilconfig['attachmentlimit_profilemaxsize'],
			//'attachmentlist' => 'attachmentlist',
			'attachmentlist_hide' => 'current_picture'
		);
		$uploadlogo = '<input name="attachment" onclick=Attach("' . $ilpage['upload'] . '?crypted=' . encrypt_url($hiddeninput) . '") type="button" value="{_upload_logo}" class="buttons" ' . $attachment_style . ' style="font-size:15px" />';
	}
	else
	{
		$attachment_style = 'disabled="disabled"';
		$uploadlogo = '<a href="' . HTTP_SERVER . $ilpage['subscription'] . '">{_upgrade_subscription_to_upload}</a>';
	}
	$headinclude .= "
	return (!haveerrors);
}
//-->
</script>
";
	$attachmentlimit_profilemaxwidth = $ilconfig['attachmentlimit_profilemaxwidth'];
	$attachmentlimit_profilemaxheight = $ilconfig['attachmentlimit_profilemaxheight'];
	$cid = (!empty($ilance->GPC['cid'])) ? intval($ilance->GPC['cid']) : 0;
	$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($cid, 'service', 'levelprofile', 'cid', 1, $_SESSION['ilancedata']['user']['slng'], 1, 0, 0, 1, 1, '', $_SESSION['ilancedata']['user']['userid'], 0, 0, false, false, $ilance->categories->cats);
	$displayprofile = fetch_user('displayprofile', $_SESSION['ilancedata']['user']['userid']);
	$displayprofile_cb = ($displayprofile > 0) ? 'checked="checked"' : '';
	$profilevideourl = fetch_user('profilevideourl', $_SESSION['ilancedata']['user']['userid']);
	$profilevideourl = ilance_htmlentities($profilevideourl);
	$rateperhour = fetch_user('rateperhour', $_SESSION['ilancedata']['user']['userid']);
	$profileintro = fetch_user('profileintro', $_SESSION['ilancedata']['user']['userid'], '', '', false);
	$profileintro_wysiwyg = ($ilconfig['default_profileintro_wysiwyg'] == 'ckeditor') 
		? print_wysiwyg_editor('profileintro', $profileintro, 'bbeditor', $ilconfig['globalfilters_enablewysiwyg'], $ilconfig['globalfilters_enablewysiwyg'], false, '754', '200', '', 'ckeditor', $ilconfig['ckeditor_profileintrotoolbar'])
		: '<textarea name="profileintro" style="width:754px; height:200px; padding:4px" wrap="physical" class="wysiwyg input">' . strip_tags(htmlspecialchars_decode($profileintro)) . '</textarea>';
	$profilecatlimit = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'maxprofilegroups');
	$ilance->template->fetch('main', 'selling_profile.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'profile_question_groups');
	$ilance->template->parse_loop('main', 'default_profile_question_groups');
	if (!isset($default_profile_question_groups))
	{
		$default_profile_question_groups = array();
	}
	@reset($default_profile_question_groups);
	while ($i = @each($default_profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'default_profile_questions' . $i['value']['groupid']);
	}
	if (!isset($profile_question_groups))
	{
		$profile_question_groups = array();
	}
	@reset($profile_question_groups);
	while ($i = @each($profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
	}
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('profileintro_wysiwyg','attachmentlimit_profilemaxwidth','attachmentlimit_profilemaxheight','profileintro','profilecatlimit','rateperhour','uploadlogo','profilevideourl','displayprofile_cb','displayprofile','category_pulldown','profile_logo','multiple_category_select','question_answer_js','attachment_style');
	
	($apihook = $ilance->api('selling_profile_start')) ? eval($apihook) : false;
	
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### SELLING PROFILE MENU ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'profile-specifics')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'profile'
	);
	$area_title = '{_service_selling_profile_creation_menu}';
	$page_title = SITE_NAME . ' - {_service_selling_profile_creation_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_profile_specifics}';
	$show['leftnav'] = true;
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
	$headinclude .= "<script type=\"text/javascript\">
<!--
function customImage(imagename, imageurl, errors)
{
	document[imagename].src = imageurl;
	if (!haveerrors && errors)
	{
		haveerrors = errors;
		alert_js(phrase['_please_fix_the_fields_marked_with_a_warning_icon_and_retry_your_action']);
	}
}
//-->
</script>
";
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'createserviceprofile') == 'no')
	{
		$area_title = '{_access_denied_to_service_profile_menu}';
		$page_title = SITE_NAME . ' - {_access_denied_to_service_profile_menu}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('createserviceprofile'));
		exit();
	}
	$headinclude .= "<script type=\"text/javascript\">
<!--
function validatecustomform(f)
{
	haveerrors = 0;
";                            
	$cid = (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0) ? intval($ilance->GPC['cid']) : 0;
	$sqlprofilegroups = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_groups
		WHERE canremove = '1'
			AND cid = '" . intval($cid) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sqlprofilegroups) > 0)
	{
		$row_count = 0;
		while ($row = $ilance->db->fetch_array($sqlprofilegroups, DB_ASSOC))
		{
			$sqlquestions = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_questions
				WHERE groupid = '" . $row['groupid'] . "'
					AND visible = '1'
				ORDER BY sort ASC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlquestions) > 0)
			{
				$row_count2 = 0;
				while ($rows = $ilance->db->fetch_array($sqlquestions, DB_ASSOC))
				{
					$rows['question'] = stripslashes($rows['question']);
					$rows['question_description'] = stripslashes($rows['description']);
					$rows['question_answer_error'] = '<span title="{_this_form_field_is_required}"><img name="question' . $rows['questionid'] . 'error" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif" width="21" height="13" border="0" alt="" /></span>';
					$headinclude .= ($rows['required']) ? "(fetch_js_object('question" . $rows['questionid'] . "').value.length < 1) ? customImage(\"question" . $rows['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/fieldempty.gif\", true) : customImage(\"question" . $rows['questionid'] . "error\", \"" . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . "icons/blankimage.gif\", false); " : '';
					if ($rows['canverify'])
					{
						$rows['action'] = '<input type="submit" name="verifyprofile[' . $rows['questionid'] . '|' . $rows['verifycost'] . ']" value="' . $ilance->currency->format($rows['verifycost'], 0, false, false, false) . '" class="buttons_smaller" />';
						$sqla = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "profile_answers
							WHERE questionid = '" . $rows['questionid'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqla) > 0)
						{
							$resq = $ilance->db->fetch_array($sqla, DB_ASSOC);
							$rows['question_answer_input'] = $ilance->mycp->construct_profile_input($rows['questionid']);
							if ($resq['isverified'] AND $resq['invoiceid'] > 0)
							{
								$date1split = explode(' ', $resq['verifyexpiry']);
								$date2split = explode('-', $date1split[0]);
								$expiredays = $ilance->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
								$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="" border="0" />';
								$rows['action'] = '';
								$rows['renew'] = $ilance->language->construct_phrase('{_expires_in_x_days}', $expiredays);
							}
							else if ($resq['isverified'] AND $resq['invoiceid'] == 0)
							{
								$date1split = explode(' ', $resq['verifyexpiry']);
								$date2split = explode('-', $date1split[0]);
								$expiredays = $ilance->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
								$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'verified_icon.gif" alt="" border="0" />';
								$rows['action'] = '';
								$rows['renew'] = $ilance->language->construct_phrase('{_expires_in_x_days}', $expiredays);
							}
							else if ($resq['isverified'] == 0 AND $resq['invoiceid'] == 0  AND !empty($resq['answer']))
							{
								$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows['action'] = '<input type="submit" name="verifyprofile[' . $rows['questionid'] . '|' . $rows['verifycost'] . ']" value="' . $ilance->currency->format($rows['verifycost'], 0, false, false, false) . '" class="buttons" />';
								$rows['renew'] = '';
							}
							else if ($resq['isverified'] == 0 AND $resq['invoiceid'] > 0 AND !empty($resq['answer']))
							{
								$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows['action'] = '{_pending}';
								$rows['renew'] = '';
							}
							else if ($resq['isverified'] == 0 AND empty($resq['answer']))
							{
								$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
								$rows['action'] = $ilance->currency->format($rows['verifycost']);
								$rows['renew'] = '';
							}
							$rows['verify2'] = '<fieldset class="fieldset" style="width:150px; padding:9px"><legend>' . $rows['is_verified'] . '</legend><div align="left" style="padding:3px">' . $rows['action'] . '</div><div align="left" class="gray">' . $rows['renew'] . '</div></fieldset>';
						}
						else
						{
							$rows['is_verified'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'not_verified.gif" alt="" border="0" />';
							$rows['question_answer_input'] = $ilance->mycp->construct_profile_input($rows['questionid']);
							$rows['action'] = $ilance->currency->format($rows['verifycost']);
							$rows['verify2'] = '<fieldset class="fieldset" style="width:150px; padding:9px"><legend>' . $rows['is_verified'] . '</legend><div align="left" style="padding:3px">' . $rows['action'] . '</div></fieldset>';
						}
					}
					else
					{
						// fetch answer
						$sqla = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "profile_answers
							WHERE questionid = '" . $rows['questionid'] . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
								AND visible = '1'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sqla) > 0)
						{
							$resq = $ilance->db->fetch_array($sqla);
							$rows['question_answer_input'] = $ilance->mycp->construct_profile_input($rows['questionid']);
							$rows['action'] = $rows['is_verified'] = $rows['verify2'] = '';
						}
						else
						{
							$rows['is_verified'] = $rows['action'] = $rows['verify2'] = '';
							$rows['question_answer_input'] = $ilance->mycp->construct_profile_input($rows['questionid']);
						}
					}
					$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
					$GLOBALS['profile_questions' . $row['groupid']][] = $rows;
					$row_count2++;
				}
			}
			else
			{
				$GLOBALS['no_profile_questions' . $row['groupid']][] = 1;
			}
			$row['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $row['groupid'] . "'", "name"));
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$profile_question_groups[] = $row;
			$row_count++;
		}
	}
	else
	{
		$no_profile_question_groups = 1;
	}
	$headinclude .= "
	return (!haveerrors);
}
//-->
</script>
";                        
	$cid = 0;
	if (!empty($ilance->GPC['cid']))
	{
	     $cid = intval($ilance->GPC['cid']);
	}
	$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($cid, 'service', 'levelprofile', 'cid', 1, $_SESSION['ilancedata']['user']['slng'], 1, 0, 0, 1, 1, '', $_SESSION['ilancedata']['user']['userid'], 0, 0, false, false, $ilance->categories->cats);
	$ilance->template->fetch('main', 'selling_profile_specifics.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'profile_question_groups');
	$ilance->template->parse_loop('main', 'default_profile_question_groups');
	if (!isset($profile_question_groups))
	{
		$profile_question_groups = array();
	}
	@reset($profile_question_groups);
	while ($i = @each($profile_question_groups))
	{
		$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
	}
	$ilance->template->parse_if_blocks('main');
	
	$pprint_array = array('profileintro','profilecatlimit','rateperhour','uploadlogo','profilevideourl','displayprofile_cb','displayprofile','category_pulldown','profile_logo','multiple_category_select','question_answer_js','attachment_style');
	
	($apihook = $ilance->api('selling_profile_specifics_start')) ? eval($apihook) : false;
	
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### SELLER PROFILE PREVIEW MENU ############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-profile-preview')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	
	$area_title = '{_service_selling_profile_creation_menu}';
	$page_title = SITE_NAME . ' - {_service_selling_profile_creation_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[selling]?cmd=profile"] = '{_selling_profile}';
	$headinclude .= '<script type="text/javascript">
<!--
function validateprofileverification(f)
{
	haveerrors = 0;
	(f.contactname.value.length < 1) ? showImage("contactnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("contactnameerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
	(f.contactnumber.value.length < 1) ? showImage("contactnumbererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("contactnumbererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false); 
	(f.contactnotes.value.length < 1) ? showImage("contactnoteserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true)  : showImage("contactnoteserror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false); 
	return (!haveerrors);
}
//-->
</script>
';
	if (isset($ilance->GPC['verifyprofile']) AND !empty($ilance->GPC['verifyprofile']) AND is_array($ilance->GPC['verifyprofile']))
	{
		$navcrumb[""] = '{_profile_verification_payment}';
		foreach ($ilance->GPC['verifyprofile'] AS $key => $value)
		{
			$options = explode('|', $key);
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "profile_answers
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND questionid = '" . intval($options[0]) . "' 
					AND visible = '1'
					AND isverified = '0'
					AND invoiceid = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$questionid = $res['questionid'];
				$answerid = $res['answerid'];
				$profile_item = "<em><strong>" . stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . intval($options[0]) . "'", "question")) . "</strong></em>: ";
				if ($ilance->auction_questions->is_question_multiplechoice($questionid, 'profile'))
				{
					$answers = unserialize($res['answer']);
					foreach ($answers AS $ans)
					{
						$profile_item .= stripslashes($ans) . ', ';
					}
					$profile_item = mb_substr($profile_item, 0, -2);
				}
				else
				{
					$answer = stripslashes($res['answer']);
					$profile_item .= stripslashes($res['answer']);
				}
				$payment_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('account', 'account_id', $_SESSION['ilancedata']['user']['userid'], $javascript = '');
				$amount_formatted = $value;
				$total_formatted = $value;
				$amount = $options[1];
				$show['istaxable'] = 0;
				
				// find out if portfolios are taxable
				if ($ilance->tax->is_taxable($_SESSION['ilancedata']['user']['userid'], 'credential'))
				{
					$show['istaxable'] = 1;
					$taxamount = $ilance->tax->fetch_amount($_SESSION['ilancedata']['user']['userid'], $options[1], 'credential', 0);
					$tax_formatted = $ilance->currency->format($taxamount);
					$total_formatted = $ilance->currency->format($amount + $taxamount);
				}
				$show['need_to_pay'] = ($amount > 0) ? true : false;
				$days = $ilconfig['verificationlength'];
				$canupdate = ($ilconfig['verificationupdateafter'] == 1) ? '{_can}' : '{_cannot}';
				$notes = $ilance->language->construct_phrase('{_once_this_profile_answer_is_paid_and_verified_you_x_update_it}', $canupdate);
				$ilance->template->fetch('main', 'selling_profile_verification.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('notes','days','tax_formatted','amount_formatted','answerid','questionid','answer','profile_item','total_formatted','amount_total','payment_method_pulldown'));
				exit();
			}
			else
			{
				print_notice('{_access_denied}', '{_were_sorry_your_profile_verification_process_could_not_be_completed}', 'javascript:history.back(1);', '{_back}');
				exit();
			}
		}
	}
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'specifics')
	{
		// handle profile answers to questions
		if (isset($ilance->GPC['question']) AND !empty($ilance->GPC['question']) AND is_array($ilance->GPC['question']))
		{
			$ilance->profile_questions->process_profile_questions($ilance->GPC['question'], $_SESSION['ilancedata']['user']['userid']);
		}
		//print_notice('{_service_profile_was_updated}', '{_thank_you_for_taking_the_time_to_create_your_service_profile_on_our_marketplace}', $ilpage['selling'] . '?cmd=profile-specifics', '{_return_to_the_previous_menu}');
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=profile-specifics&note=updated', HTTPS_SERVER . $ilpage['selling'] . '?cmd=profile-specifics');
		exit();
	}
	// handle profile answers to questions
	if (isset($ilance->GPC['question']) AND !empty($ilance->GPC['question']) AND is_array($ilance->GPC['question']))
	{
		$ilance->profile_questions->process_profile_questions($ilance->GPC['question'], $_SESSION['ilancedata']['user']['userid']);
	}
	                
	$max_user_profiles = isset($ilance->GPC['subcategories']) ? count($ilance->GPC['subcategories']) : 0;
	if (!($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'maxprofilegroups') >= $max_user_profiles))
	{
		print_notice('{_access_denied}', '{_you_selected_more_categories_than_your_current_subscription_permits_click_back_on_your_browser_to_change}' . ': <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('maxprofilegroups'));
		exit();
	}
	$profilevideourl = isset($ilance->GPC['profilevideourl']) ? $ilance->GPC['profilevideourl'] : '';
	$displayprofile = isset($ilance->GPC['displayprofile']) ? 1 : 0;
	$rateperhour = isset($ilance->GPC['rateperhour']) ? sprintf("%01.2f", $ilance->GPC['rateperhour']) : '0.00';
	$profileintro = '';
	if (isset($ilance->GPC['profileintro']))
	{
		$profileintro = strip_vulgar_words($ilance->GPC['profileintro'], false);
		if ($ilconfig['globalfilters_emailfilterpsp'])
		{
			$profileintro = strip_email_words($profileintro);
		}
		if ($ilconfig['globalfilters_domainfilterpsp'])
		{
			$profileintro = strip_domain_words($profileintro);
		}
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET displayprofile = '" . $displayprofile . "',
		profilevideourl = '" . $ilance->db->escape_string($profilevideourl) . "',
		rateperhour = '" . $rateperhour . "',
		profileintro = '" . $ilance->db->escape_string($profileintro) . "'
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	
	($apihook = $ilance->api('selling_profile_update_start')) ? eval($apihook) : false;
	
	// did provider click any of the categories on the profile page?
	// if so, let's update and/or add new entries
	// if no, we should delete all searchable categories for this user
	if (!empty($ilance->GPC['subcategories']))
	{
		// provider is updating categories they will be searchable within
		$count = count($ilance->GPC['subcategories']);
		if (isset($count) AND $count > 0)
		{
			// we are updating searchable categories
			// so let's remove our current entries
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "profile_categories
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			for ($i = 0; $i < $count; $i++)
			{
				$cid = intval($ilance->GPC['subcategories'][$i]);
				if (isset($cid) AND $cid > 0)
				{
					$sql = $ilance->db->query("
						SELECT *
						FROM " . DB_PREFIX . "profile_categories
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND cid = '" . intval($cid) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) == 0)
					{
						// adding themselves to a new searchable category
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "profile_categories
							(user_id, cid) VALUES (
							'" . $_SESSION['ilancedata']['user']['userid'] . "',
							'" . intval($cid) . "')
						", 0, null, __FILE__, __LINE__);
					}
				}
			}        
		}
	}
	else
	{
		// user is either de-selecting all opt-in searchable categories
		// or the user has not chosen to be listed at all within any category
		// so what we'll do is remove any existing entries just in case
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "profile_categories
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
	}
	$headertxt = '{_service_profile_was_updated}';
	if (!empty($_SESSION['ilancedata']['user']['invited']) AND $_SESSION['ilancedata']['user']['invited'])
	{
		print_notice($headertxt, '{_thank_you_for_taking_the_time_to_create_your_service_profile_on_our_marketplace}', HTTP_SERVER . $ilpage['rfp'] . '?id=' . $_SESSION['ilancedata']['user']['invitedid'], '{_proceed_to_bid_on_invited_project}');
		exit();
	}
	else
	{
		//print_notice($headertxt, '{_thank_you_for_taking_the_time_to_create_your_service_profile_on_our_marketplace}', $ilpage['selling'] . '?cmd=profile', '{_return_to_the_previous_menu}');
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=profile&note=updated', HTTPS_SERVER . $ilpage['selling'] . '?cmd=profile&note=updated');
		exit();
	}
}
/// #### PROFILE VERIFICATION PAYMENT HANDLER ##################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_profile-verification-process' AND isset($ilance->GPC['answerid']) AND $ilance->GPC['answerid'] > 0 AND isset($ilance->GPC['questionid']) AND $ilance->GPC['questionid'] > 0 AND !empty($ilance->GPC['answer']) AND isset($ilance->GPC['contactname']) AND isset($ilance->GPC['contactnumber']) AND isset($ilance->GPC['contactnotes']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	$ilance->accounting->process_credential_payment($_SESSION['ilancedata']['user']['userid'], 'account', $ilance->GPC['answerid'], $ilance->GPC['questionid'], $ilance->GPC['answer'], $ilance->GPC['contactname'], $ilance->GPC['contactnumber'], $ilance->GPC['contactnotes']);
	exit();
}	
// #### SELLER LISTING HANDLER #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-item-action' AND isset($ilance->GPC['itemcmd']) AND isset($ilance->GPC['rfp']) OR isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-item-action' AND isset($ilance->GPC['itemcmd']) AND $ilance->GPC['itemcmd'] == 'relist_all')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp'
	);
	// #### ARCHIVE PRODUCT AUCTIONS ###############################
	if ($ilance->GPC['itemcmd'] == 'archive')
	{
		set_cookie('inlineproduct', '', false);
		if (count($ilance->GPC['rfp']) > 0)
		{
			for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'archived'
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND (status = 'closed' OR status = 'expired' OR status = 'finished')
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				$sql = $ilance->db->query("
					SELECT id
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'archived'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) == 0)
				{
					$area_title = '{_archive_rfp_error_rfp_in_progress}' . ' (' . $ilance->GPC['rfp'][$i] . ')';
					$page_title = SITE_NAME . ' - {_archive_rfp_error_rfp_in_progress}';
					print_notice($area_title, '{_one_of_the_requested_rfps_you_are_trying_to_archive}', $ilpage['selling'], '{_return_to_the_previous_menu}');
				}
				$ilance->workspace->remove_mediashare_data(intval($ilance->GPC['rfp'][$i]));
			}
			$area_title = '{_rfps_archive_display}';
			$page_title = SITE_NAME . ' - {_rfps_archive_display}';
			print_notice('{_requested_rfps_have_been_archived}', '{_you_will_now_be_able_to_review_these_rfps_from_your_archived_rfps_menu}', $ilpage['selling'].'?cmd=management', '{_return_to_the_previous_menu}');
		}
		else
		{
			$area_title = '{_archive_rfp_error_rfp_in_progress}';
			$page_title = SITE_NAME . ' - {_archive_rfp_error_rfp_in_progress}';
			print_notice('{_invalid_items_selected}', '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}'.'</p><p>'.'{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}'.'</p>', $ilpage['selling'], '{_return_to_the_previous_menu}');
		}
	}
	// #### RELIST PRODUCT AUCTIONS ################################
	else if ($ilance->GPC['itemcmd'] == 'relist' OR $ilance->GPC['itemcmd'] == 'relist_all')
	{
		$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%">';
		$sum_insertion_fees = $insertion_fees = $insertion_fees_tax = $sum_insertion_fees_tax = $i = $sum_enhancement_fees = $enhancement_fees = $enhancement_fees_tax = $sum_enhancement_fees_tax = 0;
		$hidden = '';
		if ($ilance->GPC['itemcmd'] == 'relist_all')
		{
			$ilance->GPC['rfp'] = array();
			$sql_all = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects 
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
					AND visible = '1' 
					AND status = 'expired' 
					AND project_state = 'product'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_all) > 0)
			{
				$j = 0;
				while($res_all = $ilance->db->fetch_array($sql_all, DB_ASSOC))
				{
					$ilance->GPC['rfp'][$j] = $res_all['project_id'];
					$j++;
				}
			}
		}
		else 
		{
			$col .= '<tr class="featured_highlight" valign="top">';
			$col .= '<td width="35%"><div>{_title}</div></td>';
			$col .= '<td width="20%"><div>{_category}</div></td>';
			$col .= '<td width="15%"><div>{_insertion_fee}</div></td>';
			$col .= '<td width="15%"><div>{_upsell_fee}</div></td>';
			$col .= '<td width="15%"><div>{_total}</div></td>';
			$col .= '</tr>';
		}
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$col .= '<tr>';
			$rfpid = $value;
			$sql = $ilance->db->query("
				SELECT startprice, buynow_price, buynow_qty, reserve_price, project_title, cid, featured, bold, autorelist, highlite, project_details, buynow, reserve, description_videourl, user_id, project_state 	 
				FROM " . DB_PREFIX . "projects 
				WHERE project_id = '" . intval($rfpid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$ilance->GPC = array_merge($ilance->GPC, $res);
				$enhancement_fees = $enhancement_fees_notax = $enhancement_fees_tax = $insertion_fees = $insertion_fees_notax = $insertion_fees_tax = 0;
				if ($ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], 'product', $res['cid']))
				{
					$ifbaseamount = 0;
					if ($res['startprice'] > 0)
					{
						$ifbaseamount = $res['startprice'];
						if ($res['reserve'] AND $res['reserve_price'] > 0)
						{
							if ($res['reserve_price'] > $res['startprice'])
							{
								$ifbaseamount = $res['reserve_price'];
							}
						}
					}
					// if seller is supplying a buy now price, check to see if it's higher than our current
					// insertion fee amount, if so, use this value for the insertion fee base amount
					if ($res['buynow'] AND $res['buynow_price'] > 0 AND $res['buynow_qty'] > 0)
					{
						$totalbuynow = ($res['buynow_price'] * $res['buynow_qty']);
						if ($totalbuynow > $ifbaseamount)
						{
							$ifbaseamount = $totalbuynow;
						}
					}
					$insertion_fees_notax = $insertion_fees_inctax = $insertion_fees = $insertion_fees_tax = 0;
					$insertion_fees_notax = $ilance->accounting_fees->calculate_insertion_fee($res['cid'], 'product', $ifbaseamount, $rfpid, $_SESSION['ilancedata']['user']['userid'], 0, 0, false);
					$insertion_fees_inctax = $ilance->accounting_fees->calculate_insertion_fee($res['cid'], 'product', $ifbaseamount, $rfpid, $_SESSION['ilancedata']['user']['userid'], 0, 0);
					$insertion_fees = $insertion_fees_inctax;
					$insertion_fees_tax = $insertion_fees_inctax - $insertion_fees_notax;
					$sum_insertion_fees_tax += $insertion_fees_tax;
					$sum_insertion_fees += $insertion_fees;
					$enhancements = array();
					$promo = array('bold', 'featured', 'highlite', 'autorelist', 'reserve', 'buynow');
					foreach($promo AS $key)
					{
						if (isset($ilance->GPC[$key]) AND $ilance->GPC[$key] == '1')
						{
							if($key == 'highlite')
							{
								$enhancements['highlite'] = '1';
							}
							else 
							{
								$enhancements[$key] = $ilance->GPC[$key];
							}
						}
					}
					$enhancement_fees = $ilance->auction_fee->process_listing_enhancements_transaction($enhancements, $_SESSION['ilancedata']['user']['userid'], $rfpid, 'insert', $res['project_state'], true);
					$enhancement_fees_notax = $enhancement_fees;
					if ($ilance->tax->is_taxable(intval($_SESSION['ilancedata']['user']['userid']), 'enhancements') AND $enhancement_fees > 0)
					{
						// #### fetch tax amount to charge for this invoice type
						$enhancement_fees_tax = $ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), $enhancement_fees, 'enhancements', 0);
						$enhancement_fees = $enhancement_fees + $enhancement_fees_tax;
						$sum_enhancement_fees_tax += $enhancement_fees_tax;
					}	
					$sum_enhancement_fees += $enhancement_fees;
					if ($ilance->GPC['itemcmd'] != 'relist_all')
					{
						$col .= '<td class="alt1" valign="top"><div class="blue">' . handle_input_keywords($res['project_title']) . '</div></td>';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']) . '</div></td>';			
						$tax1 = $insertion_fees_tax > 0 ? '<br /><div class="smaller gray"><span>{_tax}: ' . $ilance->currency->format($insertion_fees_tax) . '</span></div>' : '';		
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($insertion_fees) . $tax1 . '</div></td>';
						$tax2 = $enhancement_fees_tax > 0 ? '<br /><div class="smaller gray"><span>{_tax}: ' . $ilance->currency->format($enhancement_fees_tax) . '</span></div>' : '';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($enhancement_fees) . $tax2 . '</div></td>';
						$tax3 = (($enhancement_fees_tax + $insertion_fees_tax) > 0) ? '<br /><div class="smaller gray"><span>{_tax}: ' . $ilance->currency->format($enhancement_fees_tax + $insertion_fees_tax) . '</span></div>' : '';
						$col .= '<td class="alt1" valign="top"><div class="blue">' . $ilance->currency->format($enhancement_fees + $insertion_fees) . $tax3 . '</div></td>';
					}

					$hidden .= '<input type="hidden" name="rfp[' . $i . ']" value="' . $value . '" />' . "\n";
					$i++;
				}
			}
			$col .= '</tr>';
		}
		$total = $sum_insertion_fees + $sum_enhancement_fees;
		$total_tax = $sum_insertion_fees_tax + $sum_enhancement_fees_tax; 
		if (isset($ilance->GPC['itemcmd']) AND $ilance->GPC['itemcmd'] == 'relist_all')
		{
			$col = '<table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" width="100%"><td><div>'.'{_you_are_trying_to_relist}'.' '.$i.' '.'{_auctions}'.'</div></td><td><div> </div></td><td><div>'.'{_total}'.' '.'{_fees}'.'  : ' . $ilance->currency->format($total) . '</div></td>';
		}
		else
		{
			$tax4 = ($total_tax > 0) ? '<br /><div class="smaller gray">{_tax}: ' . $ilance->currency->format($total_tax) . '</div>' : '';
			$col .= '<td><div></div></td><td><div></div></td><td></td><td></td><td><div><strong>{_total}</strong>: <span class="blue">' . $ilance->currency->format($total) . '</span> ' . $tax4 . '</div></td>';
		}
		$col .= '</table>';
		if ($i > 0)
		{
			$col .= '<form name="relist" method="post" action="' . HTTP_SERVER . $ilpage['selling'] . '" accept-charset="UTF-8" style="margin:0px">';
			$col .= '<input type="hidden" name="cmd" value="_do-item-action" />';
			$col .= '<input type="hidden" name="itemcmd" value="relist-do" />';
			$col .= $hidden;
			$col .= '<div style="clear:both"></div><div style="padding-top:9px; padding-bottom:25px"><span><input type="submit" value=" {_continue} " style="font-size:15px" class="buttons" /></span></div></form>';
		}
		$area_title = '{_relist_auctions}';
		$page_title = SITE_NAME . ' - {_relist_auctions}';
		print_notice($area_title, $col, $ilpage['selling'], '{_return_to_the_previous_menu}');
		exit();
	}
	// #### RELIST PRODUCT AUCTIONS ################################
	else if ($ilance->GPC['itemcmd'] == 'relist-do')
	{
		$status = array();
		$pending = '';
		foreach ($ilance->GPC['rfp'] AS $key => $value)
		{
			$status[] = $ilance->auction_product->relist_product_auction($value);
		}
		if (in_array("frozen", $status))
		{
			$pending = "&pending=1";
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=_do-item-action&itemcmd=relist-do-notice&rfp=0' . $pending);
		exit();
	}
	else if ($ilance->GPC['itemcmd'] == 'relist-do-notice')
	{
		$area_title = '{_relist_auctions}';
		$page_title = SITE_NAME . ' - {_relist_auctions}';
		
		if (isset($ilance->GPC['pending']) AND $ilance->GPC['pending'] == '1')
		{
			$pprint_array = array('url','login_include');
			$ilance->template->fetch('main', 'listing_forward_auction_complete_frozen.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice('{_action_completed}', '{_auctions_successfully_relisted}', $ilpage['selling'] . '?cmd=management&sub=expired&displayorder=desc', '{_back}');
			exit();
		}
	}
	// #### CANCEL PRODUCT AUCTIONS ################################
	else if ($ilance->GPC['itemcmd'] == 'cancel')
	{
		// does admin allow auction cancellation?
		if ($ilconfig['globalfilters_enablerfpcancellation'])
		{
			// empty inline cookie
			set_cookie('inlineproduct', '', false);
			if (count($ilance->GPC['rfp']) > 0)
			{
				for ($i = 0; $i < count($ilance->GPC['rfp']); $i++)
				{ 
					$delist_msg = '{_delisted_by}' . ' ' . $_SESSION['ilancedata']['user']['username'] . ' {_on} ' . print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$presql = $ilance->db->query("
						SELECT cid
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($presql) > 0)
					{
						$auctioninfo = $ilance->db->fetch_array($presql);
						$ilance->categories->build_category_count($auctioninfo['cid'], 'subtract', "seller closing multiple listings from selling activity: subtracting increment count category id $auctioninfo[cid]");
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET status = 'delisted',
							additional_info = '" . $ilance->db->escape_string($delist_msg) . "',
							close_date = '" . DATETIME24H . "'
							WHERE project_id = '" . intval($ilance->GPC['rfp'][$i]) . "'
								AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						", 0, null, __FILE__, __LINE__);
						$ilance->workspace->remove_mediashare_data($ilance->GPC['rfp'][$i]);                                                        
					}
					else
					{
						$area_title = '{_cancel_rfp_error_rfp_in_progress}';
						$page_title = SITE_NAME . ' - {_cancel_rfp_error_rfp_in_progress}';
						print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}'.'</p><p>'.'{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}'.'</p>', $ilpage['selling'], '{_return_to_the_previous_menu}');
					}
				}
				$area_title = '{_requested_rfps_have_been_cancelled}';
				$page_title = SITE_NAME . ' - {_auctions_have_been_cancelled}';
				print_notice($area_title, '{_you_have_successfully_delisted_cancelled_one_or_more_auctions_from_your_buying_activity_menu_no_more_bids_can_be_placed}' . '</p><p>{_you_will_now_be_able_to_review_these_delisted_rfps_from_your} <a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=delisted">{_delisted_auctions}</a> {_menu}</p>', HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=delisted', '{_delisted_auctions}');
			}
			else
			{
				$area_title = '{_invalid_items_selected}';
				$page_title = SITE_NAME . ' - {_cancel_rfp_error_rfp_in_progress}';
				print_notice($area_title, '{_your_requested_rfp_control_action_cannot_be_completed_because_one_or_more_rfps}'.'</p><p>'.'{_when_an_rfp_is_in_award_phase_rfp_control_features_such_as_archive}'.'</p>', $ilpage['selling'], '{_return_to_the_previous_menu}');
			}
		}
		else
		{
			$area_title = '{_access_denied_cancel_rfp_feature_currently_disabled}';
			$page_title = SITE_NAME . ' - {_access_denied_cancel_rfp_feature_currently_disabled}';
			print_notice('{_access_to_feature_denied}', '{_were_sorry_this_feature_is_currently_disabled}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['main'], '{_main_menu}');
			exit();
		}
	}
	// #### CANCEL PRODUCT AUCTIONS ################################
	else if (isset($ilance->GPC['itemcmd']) AND $ilance->GPC['itemcmd'] == 'delete_auction')
	{
		$count = 0;
		if (isset($ilance->GPC['rfp']) AND is_array($ilance->GPC['rfp']) AND count($ilance->GPC['rfp']) > 0 AND isset($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $ilconfig['globalauctionsettings_deletearchivedlistings'])
		{
			foreach ($ilance->GPC['rfp'] AS $value)
			{
				$sql = $ilance->db->query("
					SELECT user_id, cid, status, project_state, project_title
					FROM " . DB_PREFIX . "projects
					WHERE project_id = '" . intval($value) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND status = 'archived'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$ilance->common_listing->physically_remove_listing(intval($value));
					$count++;
				}
			}
			print_notice('{_action_completed}', '{_the_selected_listings_were_removed}', $ilpage['selling'] . '?cmd=management&sub=archived&displayorder=desc', '{_return_to_the_previous_menu}');
			exit();	
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&sub=archived&displayorder=desc');
		exit();
	}
}
// #### PERFORM BID ACTION FROM SERVICE AUCTION BIDDING ACTIVITY ###############
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-bid-action' AND isset($ilance->GPC['bidcmd']) AND empty($ilance->GPC['itemcmd']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'mycp'
	);
	// #### ARCHIVING BIDS #################################################
	if ($ilance->GPC['bidcmd'] == 'archive')
	{
		// empty inline cookie
		set_cookie('inlineservice', '', false);
		if (isset($ilance->GPC['bidid']) AND count($ilance->GPC['bidid']) > 0)
		{
			foreach ($ilance->GPC['bidid'] AS $key => $value)
			{
				$vars = explode('|', $value);
				$bid = $vars['0'];
				$pid = isset($vars['1']) ? $vars['1'] : 0;
				$cid = fetch_auction('cid', $pid);
				$bidgrouping = ($cid != '0') ? $ilance->categories->bidgrouping($cid) : '1';
				$bids_table = ($bidgrouping == true) ? 'project_bids' : 'project_realtimebids';
				$bids_field = ($bidgrouping == true) ? 'bid_id' : 'id';
				$ilance->db->query("
					UPDATE " . DB_PREFIX . $bids_table . "
					SET bidstate = 'archived'
					WHERE " . $bids_field . " = '" . intval($bid) . "'
						AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->workspace->remove_mediashare_data_bidid(intval($bid));
			}
			$area_title = '{_requested_bids_have_been_archived}';
			$page_title = SITE_NAME . ' - {_requested_bids_have_been_archived}';
			print_notice($area_title, '{_you_have_successfully_archived_one_or_more_bid_proposals_from_your_service_auction_bidding_activity}', $ilpage['selling'].'?cmd=management', '{_return_to_the_previous_menu}');
			exit();
		}
		else
		{
			$area_title = '{_there_was_an_error_archiving_your_bids}';
			$page_title = SITE_NAME . ' - {_there_was_an_error_archiving_your_bids}';
			print_notice('{_invalid_items_selected}', '{_in_order_to_successfully_archive_bids_you_must_first_select_the_checkbox}', $ilpage['selling'].'?cmd=management', '{_return_to_the_previous_menu}');
			exit();
		}
	}
	// #### RETRACT BIDS ###################################################
	else if ($ilance->GPC['bidcmd'] == 'retract')
	{
		// #### empty inline cookie ####################################
		set_cookie('inlineservice', '', false);
		set_cookie('inlineproduct', '', false);
		if (isset($ilance->GPC['bidid']) AND count($ilance->GPC['bidid']) > 0)
		{
			foreach ($ilance->GPC['bidid'] AS $key => $value)
			{
				$vars = explode('|', $value);
				$bid = $vars['0'];
				$pid = isset($vars['1']) ? $vars['1'] : 0;
				$cid = fetch_auction('cid', $pid);
				$bidgrouping = ($cid != '0') ? $ilance->categories->bidgrouping($cid) : true;
				$bids_table = ($bidgrouping == true) ? 'project_bids' : 'project_realtimebids';
				$bids_field = ($bidgrouping == true) ? 'bid_id' : 'id';
				$sql = $ilance->db->query("
					SELECT project_id, bidstatus, state
					FROM " . DB_PREFIX . $bids_table . "
					WHERE " . $bids_field . " = '" . intval($bid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$res['isawarded'] = ($res['bidstatus'] == 'awarded') ? true : false;
					$res['reason'] = (!empty($ilance->GPC['bidretractreason'])) ? handle_input_keywords($ilance->GPC['bidretractreason']) : '{_no_reason_provided}';
					if ($res['state'] == 'service')
					{
						if ($ilconfig['servicebid_bidretract'])
						{
							$ilance->bid_retract->construct_service_bid_retraction($_SESSION['ilancedata']['user']['userid'], intval($bid), $res['project_id'], $res['reason'], $res['isawarded'], true, $bidgrouping);
						}
						else
						{
							print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
							exit();
						}
					}
					else if ($res['state'] == 'product')
					{
						if ($ilconfig['productbid_bidretract'])
						{
							$ilance->bid_retract->construct_product_bid_retraction($_SESSION['ilancedata']['user']['userid'], intval($bid), $res['project_id'], $res['reason'], $res['isawarded'], true);
						}
						else
						{
							print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', $ilpage['main'], '{_main_menu}');
							exit();
						}
					}	
				}
			}
			$totalretracts = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidretracts');
			print_notice('{_bid_retracted}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts}', $totalretracts), "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
			exit();
		}
		else
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			print_notice('{_access_denied}', '{_you_did_not_select_a_valid_bid_to_retract_please_try_again_from_the_previous_menu}', 'javascript:history.back()', '{_retry}');
			exit();
		}
	}
}
// #### SELLING ACTIVITY MENU ##################################################
else
{
	include_once(DIR_SERVER_ROOT . 'selling_activity.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>