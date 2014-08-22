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
		'jquery',
		'inline_edit'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array (
    'preferences'
);

// #### setup script location ##################################################
define('LOCATION', 'preferences');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';

($apihook = $ilance->api('preferences_start')) ? eval($apihook) : false;

if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'favorites')
{
	// #### RECENTLY REVIEWED AUCTION BIT ##################################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'recentlyreviewed')
	{
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearproductlist')
		{
			$_SESSION['ilancedata']['product']['list'] = '';
			unset($_SESSION['ilancedata']['product']['list']);
			$query = "ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$query = "user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search_users
				SET uservisible = '0'
				WHERE $query
					AND project_id > 0
					AND searchmode = 'product'
			", 0, null, __FILE__, __LINE__);
			if (isset($ilance->GPC['returnurl']))
			{
				refresh(handle_input_keywords($ilance->GPC['returnurl']));
			}
			else
			{
				refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			}
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearservicelist')
		{
			$_SESSION['ilancedata']['service']['list'] = '';
			unset($_SESSION['ilancedata']['service']['list']);
			$query = "ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$query = "user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search_users
				SET uservisible = '0'
				WHERE $query
					AND project_id > 0
					AND searchmode = 'service'
			", 0, null, __FILE__, __LINE__);
			if (isset($ilance->GPC['returnurl']))
			{
				refresh(handle_input_keywords($ilance->GPC['returnurl']));
			}
			else
			{
				refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			}
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearexpertslist')
		{
			$_SESSION['ilancedata']['experts']['list'] = '';
			unset($_SESSION['ilancedata']['experts']['list']);
			$query = "ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$query = "user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search_users
				SET uservisible = '0'
				WHERE $query
					AND searchmode = 'experts'
			", 0, null, __FILE__, __LINE__);
			if (isset($ilance->GPC['returnurl']))
			{
				refresh(handle_input_keywords($ilance->GPC['returnurl']));
			}
			else
			{
				refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			}
			exit();
		}
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'clearkeywordlist')
		{
			$query = "ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$query = "user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "search_users
				SET uservisible = '0'
				WHERE $query
					AND keyword != ''
			", 0, null, __FILE__, __LINE__);
			if (isset($ilance->GPC['returnurl']))
			{
				refresh(handle_input_keywords($ilance->GPC['returnurl']));
			}
			else
			{
				refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			}
			exit();
		}
	}
}
// #### handle opt-out of email without being signed-in ########################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'email' AND isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'unsubscribe' AND isset($ilance->GPC['id']) AND !empty($ilance->GPC['id']) AND isset($ilance->GPC['e']) AND !empty($ilance->GPC['e']))
{
	$emailid = urldecode($ilance->GPC['id']);
	$email = base64_decode(urldecode($ilance->GPC['e']));

	// who are we? and are we an active member? (we don't want old or deleted users getting through from past emails)
	$userid = $ilance->db->fetch_field(DB_PREFIX . "users", "email = '" . $ilance->db->escape_string($email) . "' AND status = 'active'", "user_id");
	if ($userid > 0)
	{
		require_once(DIR_CORE . 'functions_email.php');   
		// unsubscribe this user from receiving this notification
		if (unsubscribe_notification($email, $emailid))
		{
			$username = fetch_user('first_name', $userid);
			print_notice("{_youve_unsubscribed_to_a_notification}", "$username, {_youve_just_unsubscribed_to_a_notification}", $ilpage['preferences'] . '?cmd=email', '{_email_preferences}');
			exit();
		}
	}
}

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	$uncrypted = array();
	if (!empty($ilance->GPC['crypted']))
	{
		$uncrypted = decrypt_url($ilance->GPC['crypted']);
	}
	// #### REMOVE ATTACHMENTS #############################################
	if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_attachment-action' AND isset($ilance->GPC['attachcmd']))
	{
		if ($ilance->GPC['attachcmd'] == 'delete' AND isset($ilance->GPC['attachid']))
		{
			$area_title = '{_attachment_removal_process}';
			$page_title = SITE_NAME . ' - {_attachment_removal_process}';
			
			$attachids = $ilance->GPC['attachid'];
			if (isset($attachids) AND is_array($attachids))
			{
				foreach ($attachids AS $value)
				{
					$ilance->attachment->remove_attachment($value, $_SESSION['ilancedata']['user']['userid']);
				}
			}
			print_notice('{_attachments_successfully_removed}', '{_you_have_successfully_removed_specified_attachments_from_your_account}', $ilpage['preferences'] . '?cmd=attachments', '{_return_to_the_previous_menu}');
			exit();
		}
		else if ($ilance->GPC['attachcmd'] == 'deleteall')
		{
			$area_title = '{_attachment_removal_process}';
			$page_title = SITE_NAME . ' - {_attachment_removal_process}';
			
			$sql = $ilance->db->query("
				SELECT attachid
                                FROM " . DB_PREFIX . "attachment
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$attachids[] = $res['attachid'];
				}
			}
			if (isset($attachids) AND is_array($attachids))
			{
				foreach ($attachids AS $value)
				{
					$ilance->attachment->remove_attachment($value, $_SESSION['ilancedata']['user']['userid']);
				}
			}
			print_notice('{_attachments_successfully_removed}', '{_you_have_successfully_removed_specified_attachments_from_your_account}', $ilpage['preferences'] . '?cmd=attachments', '{_return_to_the_previous_menu}');
			exit();
		}
		else if ($ilance->GPC['attachcmd'] == 'deleteexpireditemphoto')
		{
			$area_title = '{_attachment_removal_process}';
			$page_title = SITE_NAME . ' - {_attachment_removal_process}';
			
			$sql = $ilance->db->query("
				SELECT attachid
                                FROM " . DB_PREFIX . "attachment a
				LEFT JOIN " . DB_PREFIX . "projects p ON (a.project_id = p.project_id)
                                WHERE a.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND p.status != 'open'
					AND p.date_end < '" . DATETIME24H . "'
					AND (a.attachtype = 'itemphoto' OR a.attachtype = 'slideshow')
                                        AND a.visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$attachids[] = $res['attachid'];
				}
			}
			if (isset($attachids) AND is_array($attachids))
			{
				foreach ($attachids AS $value)
				{
					$ilance->attachment->remove_attachment($value, $_SESSION['ilancedata']['user']['userid']);
				}
			}
			print_notice('{_attachments_successfully_removed}', '{_you_have_successfully_removed_specified_attachments_from_your_account}', $ilpage['preferences'] . '?cmd=attachments', '{_return_to_the_previous_menu}');
			exit();
		}
	}
	// #### RENEW PASSWORD #################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-renewal')
	{
		$area_title = '{_password_renewal_menu}';
		$page_title = SITE_NAME . ' - {_password_renewal_menu}';
		$navcrumb[""] = '{_renew_password}';
		$show['leftnav'] = true;
		$headinclude .= '
<script type="text/javascript">
<!--
function validateSAForm(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>
';
		$sql = $ilance->db->query("
                        SELECT secretquestion, secretanswer
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                AND secretquestion != '' AND secretanswer != ''
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$secret_question = handle_input_keywords(stripslashes($res['secretquestion']));
			$ilance->template->fetch('main', 'preferences_password_renewal.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ('secret_question'));
			exit();
		}
		else
		{
			// skip right to the password change template (no secret password to answer found)..
			$area_title = '{_create_new_password}';
			$page_title = SITE_NAME . ' - {_create_new_password}';
			$headinclude .= '
<script type="text/javascript">
<!--
function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) 
        ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true)
        : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1)
        ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true)
        : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>
';
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ());
			exit();
		}
	}
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-change' AND !empty($ilance->GPC['secretanswer']))
	{
		$area_title = '{_renewing_password}';
		$page_title = SITE_NAME . ' - {_renewing_password}';
		$navcrumb[""] = '{_renew_password}';
		$sql_answer = $ilance->db->query("
                        SELECT secretanswer
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
		$sql_answer_result = $ilance->db->fetch_array($sql_answer, DB_ASSOC);
		$secret_answer = $sql_answer_result['secretanswer'];
		$md5_secret_answer = md5($ilance->GPC['secretanswer']);
		if ($md5_secret_answer == $secret_answer)
		{
			$area_title = '{_create_new_password}';
			$page_title = SITE_NAME . ' - {_create_new_password}';
			$headinclude .= '
<script type="text/javascript">
<!--
function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>
';
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ());
			exit();
		}
		else
		{
			$area_title = '{_renew_password_bad_secret_answer}';
			$page_title = SITE_NAME . ' - {_renew_password_bad_secret_answer}';
			print_notice('{_bad_secret_answer}', '{_you_have_supplied_the_wrong_answer_for_your_secret_question}', 'javascript:history.back()', '{_back}');
		}
	}
	// #### RENEW PASSWORD HANDLER #########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-create' AND !empty($ilance->GPC['password']) AND !empty($ilance->GPC['password2']))
	{
		$area_title = '{_renewing_password}';
		$page_title = SITE_NAME . ' - {_renewing_password}';
		$navcrumb[""] = '{_renew_password}';
		$md5_secret_answer = md5($ilance->GPC['secretanswer']);
		if ($md5_secret_answer == $secret_answer)
		{
			$area_title = '{_create_new_password}';
			$page_title = SITE_NAME . ' - {_create_new_password}';
			$headinclude .= '<script type="text/javascript">
<!--
function validateNewPWForm(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.password2.value.length < 1) ? showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("password2error", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
// -->
</script>
';
			$ilance->template->fetch('main', 'preferences_password_create.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ());
			exit();
		}
	}
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-password-create' AND !empty($ilance->GPC['password']) AND !empty($ilance->GPC['password2']))
	{
		$area_title = '{_renewing_password}';
		$page_title = SITE_NAME . ' - {_renewing_password}';
		$navcrumb[""] = '{_renew_password}';
		$password1 = $ilance->GPC['password'];
		$password2 = $ilance->GPC['password2'];
		if (isset($password1) AND isset($password2) AND $password1 == $password2)
		{
			$area_title = '{_creating_new_password}';
			$page_title = SITE_NAME . ' - {_creating_new_password}';
			$salt = construct_password_salt($length = 5);
			$newpassword = md5(md5($password1) . $salt);
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET password = '" . $ilance->db->escape_string($newpassword) . "',
				    salt = '" . $ilance->db->escape_string($salt) . "',
				    password_lastchanged = '" . DATETIME24H . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
			$_SESSION['ilancedata']['user']['password'] = "";
			$_SESSION['ilancedata']['user']['password'] = $newpassword;
			print_notice('{_password_sucessfully_changed}', '{_you_have_successfully_changed_your_profile_password_information}', $ilpage['preferences'], '{_preferences}');
			exit();
		}
		else
		{
			$area_title = '{_bad_repassword_detected}';
			$page_title = SITE_NAME . ' - {_bad_repassword_detected}';
			print_notice('{_bad_repassword_detected}', '{_in_order_to_successfully_change_your_password}', 'javascript:history.back(1);', '{_click_back_on_your_browser_to_retry_your_password_actions}');
			exit();
		}
	}
	// #### TIME ZONE MANAGEMENT ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'timezone')
	{
		$area_title = '{_time_zone_management}';
		$page_title = '{_time_zone_management} | ' . SITE_NAME;
		$navcrumb[""] = '{_time_zone_management}';
		$show['leftnav'] = true;
		$user_time = $ilance->datetimes->fetch_local_time($_SESSION['ilancedata']['user']['timezone'], 'D, M d, Y h:i A', true, true, true, 'litegray');
		$mrkt_time = $ilance->datetimes->fetch_local_time($ilconfig['globalserverlocale_sitetimezone'], 'D, M d, Y h:i A', true, true, true, 'litegray');
		$timezone_pulldown = $ilance->datetimes->timezone_pulldown('usertimezone', $_SESSION['ilancedata']['user']['timezone'], true, true);
		$pprint_array = array ('user_timezone', 'user_time', 'official_time', 'timezone', 'timezone_pulldown', 'mrkt_time');
		$ilance->template->fetch('main', 'preferences_timezone.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### TIME ZONE HANDLER ##############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-timezone-change' AND !empty($ilance->GPC['usertimezone']))
	{
		$area_title = '{_updating_time_zone_preference}';
		$page_title = '{_updating_time_zone_preference} | ' . SITE_NAME;
		$_SESSION['ilancedata']['user']['timezone'] = handle_input_keywords($ilance->GPC['usertimezone']);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET timezone = '" . $ilance->db->escape_string($ilance->GPC['usertimezone']) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=timezone', HTTPS_SERVER . $ilpage['preferences'] . '?cmd=timezone');
		exit();
	}
	// #### NEWSLETTER #####################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'notifications')
	{
		$area_title = '{_newsletter_preferences_menu}';
		$page_title = SITE_NAME . ' - {_newsletter_preferences_menu}';
		$navcrumb[""] = '{_category_notifications}';
		// #### define top header nav ##################################################
		$topnavlink = array (
		    'mycp',
		    'newsletters'
		);
		$show['leftnav'] = true;
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'newsletteropt_in') == 'no')
		{
			$area_title = '{_access_denied_to_newsletter_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_newsletter_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . ' <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('newsletteropt_in'));
			exit();
		}
		$headinclude .= "
<script type=\"text/javascript\">
<!--
function move_from_merge_to(divfrom, divto)
{
	// fetch the blocks
        var fromdiv = fetch_js_object(divfrom).innerHTML;
	var todiv = fetch_js_object(divto).innerHTML;
	
	// merge the blocks
	mergediv = fromdiv + todiv;
	fetch_js_object(divto).innerHTML = mergediv;
	fetch_js_object(divfrom).innerHTML = '';
	
	// reset the categories
	document.frames['category_iframe'].location.reload(true);
}
//-->
</script>
";
		$sql = $ilance->db->query("
			SELECT notifyservices, notifyproducts, notifyservicescats, notifyproductscats, lastemailservicecats, lastemailproductcats
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			// #### last email date ################################
			$lastitemsentdate = $res['lastemailproductcats'];
			$lastservicesentdate = $res['lastemailservicecats'];
			if ($lastitemsentdate == '0000-00-00')
			{
				$lastitemsentdate = '{_never}';
			}
			else
			{
				$lastitemsentdate = print_date($lastitemsentdate, 'M. d, Y', 0, 0);
			}
			if ($lastservicesentdate == '0000-00-00')
			{
				$lastservicesentdate = '{_never}';
			}
			else
			{
				$lastservicesentdate = print_date($lastservicesentdate, 'M. d, Y', 0, 0);
			}
			// #### defaults for email opt-in confirmation #########			
			$notifyservices_cb = ($res['notifyservices']) ? '<input type="checkbox" name="notifyservices" value="1" checked="checked" />' : '<input type="checkbox" name="notifyservices" value="1" />';
			$notifyproducts_cb = ($res['notifyproducts']) ? '<input type="checkbox" name="notifyproducts" value="1" checked="checked" />' : '<input type="checkbox" name="notifyproducts" value="1" />';
			// #### existing category selections ###################
			$existingproduct = $existingservice = '';
			if (!empty($res['notifyproductscats']))
			{
				$notifyproductscats = '';
				$temp = explode(',', $res['notifyproductscats']);
				foreach ($temp AS $cid)
				{
					if ($cid > 0 AND $ilance->categories->visible($cid) AND $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "newsletter", "1"))
					{
						$existingproduct .= '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories2_' . $cid . '" name="subcategories2[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive(intval($cid), 'product', $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\'subcategories2_' . $cid . '\').disabled=true;toggle_hide(\'hiderow_' . $cid . '\')" style="text-decoration:underline">{_remove}</a>)</span></div>';
						$notifyproductscats .= empty($notifyproductscats) ? ',' . $cid . ',' : $cid . ',';
					}
				}
				if ($res['notifyproductscats'] != $notifyproductscats)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET notifyproductscats = '" . $ilance->db->escape_string($notifyproductscats) . "'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
			if (!empty($res['notifyservicescats']))
			{
				$notifyservicescats = '';
				$temp = explode(',', $res['notifyservicescats']);
				foreach ($temp AS $cid)
				{
					if ($cid > 0 AND $ilance->categories->visible($cid) AND $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "newsletter", "1"))
					{
						$existingservice .= '<div style="padding-top:3px" id="hiderow_' . $cid . '"><input type="hidden" id="subcategories_' . $cid . '" name="subcategories[]" value="' . intval($cid) . '" /><span class="blue">' . $ilance->categories->recursive(intval($cid), 'service', $_SESSION['ilancedata']['user']['slng'], 1, '', 0) . '</span>&nbsp;&nbsp;&nbsp;&nbsp;<span class="smaller blue">(<a href="javascript:void(0)" onclick="fetch_js_object(\'subcategories_' . $cid . '\').disabled=true;toggle_hide(\'hiderow_' . $cid . '\')" style="text-decoration:underline">{_remove}</a>)</span></div>';
						$notifyservicescats .= empty($notifyservicescats) ? ',' . $cid . ',' : $cid . ',';
					}
				}
				if ($res['notifyservicescats'] != $notifyservicescats)
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET notifyservicescats = '" . $ilance->db->escape_string($notifyservicescats) . "'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
		$pprint_array = array ('lastitemsentdate', 'lastservicesentdate', 'existingproduct', 'existingservice', 'service_newsletter', 'product_newsletter', 'notifyservices_cb', 'notifyproducts_cb', 'dynamic_newsletter_unselect', 'dynamic_newsletter_select', 'dynamic_newsletter_unselect2', 'dynamic_newsletter_select2', 'newsletter_category_select', 'newsletter_pulldown');

		($apihook = $ilance->api('end_newsletter_preferences')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'preferences_newsletter.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### NEWSLETTER HANDLER #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-notifications-change')
	{
		$area_title = '{_updating_newsletter_preference}';
		$page_title = SITE_NAME . ' - {_updating_newsletter_preference}';
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'newsletteropt_in') == 'no')
		{
			$area_title = '{_access_denied_to_newsletter_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_newsletter_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('newsletteropt_in'));
			exit();
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'product')
		{
			$subcats2 = '';
			if (!empty($ilance->GPC['subcategories2']))
			{
				for ($i = 0; $i < sizeof($ilance->GPC['subcategories2']); $i++)
				{
					if ($ilance->GPC['subcategories2'][$i] > 0)
					{
						$subcats2 .= intval($ilance->GPC['subcategories2'][$i]) . ',';
					}
				}
				$subcats2 = ',' . $subcats2;
			}
			$notifyproducts = '0';
			if (isset($ilance->GPC['notifyproducts']) AND $ilance->GPC['notifyproducts'])
			{
				$notifyproducts = '1';
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET notifyproductscats = '" . $ilance->db->escape_string($subcats2) . "',
				    notifyproducts = '" . intval($notifyproducts) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'service')
		{
			$subcats = '';
			if (!empty($ilance->GPC['subcategories']))
			{
				for ($i = 0; $i < sizeof($ilance->GPC['subcategories']); $i++)
				{
					if ($ilance->GPC['subcategories'][$i] > 0)
					{
						$subcats .= intval($ilance->GPC['subcategories'][$i]) . ',';
					}
				}
				$subcats = ',' . $subcats;
			}
			$notifyservices = '0';
			if (isset($ilance->GPC['notifyservices']) AND $ilance->GPC['notifyservices'])
			{
				$notifyservices = '1';
			}
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET notifyservicescats = '" . $ilance->db->escape_string($subcats) . "',
				    notifyservices = '" . intval($notifyservices) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
		}

		($apihook = $ilance->api('newsletter_preferences_submit')) ? eval($apihook) : false;

		print_notice('{_vendor_newsletter_options_changed}', '{_you_have_successfully_changed_options_for_the_daily_vendor_newsletter_list}', HTTP_SERVER . $ilpage['preferences'] . '?cmd=notifications', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### DISTANCE CALCULATION ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'distance')
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'no')
		{
			$area_title = '{_access_denied_to_distance_calculation_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_distance_calculation_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('distance'));
			exit();
		}
		$area_title = '{_distance_calculation_menu}';
		$page_title = SITE_NAME . ' - {_distance_calculation_menu}';
		$navcrumb[""] = '{_distance_management}';
		$show['leftnav'] = true;
		$distance_pulldown = construct_pulldown('distance', 'distance', array('0' => '{_no}', '1' => '{_yes}'), $_SESSION['ilancedata']['user']['distance'], 'class="select"');
		$ilance->template->fetch('main', 'preferences_distance.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array ('distance_pulldown'));
		exit();
	}
	// #### DISTANCE CALCULATION HANDLER ###################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-distance-change' AND isset($ilance->GPC['distance']) AND $ilance->GPC['distance'] != '')
	{
		$area_title = '{_updating_distance_preference}';
		$page_title = SITE_NAME . ' - {_updating_distance_preference}';
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'no')
		{
			$area_title = '{_access_denied_to_distance_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_distance_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('distance'));
			exit();
		}
		$distance_calculation_enabled = $ilance->GPC['distance'];
		$_SESSION['ilancedata']['user']['distance'] = $distance_calculation_enabled;
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET project_distance = '" . intval($ilance->GPC['distance']) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		print_notice('{_distance_calculation_options_changed}', '{_you_have_successfully_changed_options_for_the_distance_calculation_profile_settings}', $ilpage['preferences'] . '?cmd=distance', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### EMAIL ##########################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'email')
	{
		$area_title = '{_email_preference_menu}';
		$page_title = SITE_NAME . ' - {_email_preference_menu}';
		$navcrumb[""] = '{_email_preferences}';
		$topnavlink = array (
			'mycp',
			'preferencesemail'
		);
		$show['leftnav'] = true;
		include_once (DIR_CORE . 'functions_email.php');
		$emailnotify = fetch_user('emailnotify', $_SESSION['ilancedata']['user']['userid']);
		$email_pulldown = construct_pulldown('notify', 'notify', array('0' => '{_no}', '1' => '{_yes}'), $emailnotify, 'style="font-family: verdana"');
		$session_email = $_SESSION['ilancedata']['user']['email'];
		$emailnotifications = array();
		$extrasql = "AND admin = '0'";
		if ($_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$extrasql = '';
		}
		if (isset($ilance->GPC['t']) AND !empty($ilance->GPC['t']))
		{
			if ($ilance->GPC['t'] == 'buyer' OR $ilance->GPC['t'] == 'seller' OR $ilance->GPC['t'] == 'admin')
			{
				$extrasql .= " AND `" . $ilance->db->escape_string($ilance->GPC['t']) . "` = '1'";
			}
		}
		$sql_rows = $ilance->db->query("
			SELECT name_" . $_SESSION['ilancedata']['user']['slng'] . " AS name, varname, buyer, seller, admin
			FROM " . DB_PREFIX . "email
			WHERE varname != ''
				AND (buyer != '0' OR seller != '0' OR admin != '0')
				AND (type = 'global' OR type = '" . $ilconfig['globalauctionsettings_auctionstypeenabled'] . "')
			$extrasql
			ORDER BY name ASC 
		", 0, null, __FILE__, __LINE__);
		$number = $ilance->db->num_rows($sql_rows);
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['preferences'] . '?cmd=email');
		$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		$sql = $ilance->db->query("
			SELECT name_" . $_SESSION['ilancedata']['user']['slng'] . " AS name, varname, buyer, seller, admin
			FROM " . DB_PREFIX . "email
			WHERE varname != ''
				AND (buyer != '0' OR seller != '0' OR admin != '0')
				AND (type = 'global' OR type = '" . $ilconfig['globalauctionsettings_auctionstypeenabled'] . "')
			$extrasql
			ORDER BY name ASC
			$limit
		", 0, null, __FILE__, __LINE__);
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$optout = is_notification_unsubscribed($session_email, $res['varname']);
			$res['emailcb'] = '<input type="hidden" name="varnames[]" value="' . $res['varname'] . '" /><input type="checkbox" name="cbvarname[' . $res['varname'] . ']" value="1" ' . (($optout) ? '' : 'checked="checked"' ) . ' />';
			$res['smscb'] = '';
			$res['ebuyer'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$res['eseller'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			$res['eadmin'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
			if ($res['buyer'])
			{
				$res['ebuyer'] = '<span title="{_email_is_buyer_specific}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked_green.gif" border="0" alt="" /></span>';
			}
			if ($res['seller'])
			{
				$res['eseller'] = '<span title="{_email_is_seller_specific}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked_green.gif" border="0" alt="" /></span>';
			}
			if ($res['admin'])
			{
				$res['eadmin'] = '<span title="{_email_is_admin_specific}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked_green.gif" border="0" alt="" /></span>';
			}
			$emailnotifications[] = $res;
		}
		$pprint_array = array ('session_email','prevnext');

		($apihook = $ilance->api('preferences_email_template_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'preferences_email.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'emailnotifications');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### NOTIFICATONS HANDLER ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-notification-change')
	{
		$area_title = '{_updating_email_preference}';
		$page_title = SITE_NAME . ' - {_updating_email_preference}';

		($apihook = $ilance->api('preferences_notifications_change_start')) ? eval($apihook) : false;

		if (isset($ilance->GPC['varnames']) AND is_array($ilance->GPC['varnames']))
		{
			foreach ($ilance->GPC['varnames'] AS $key => $varname)
			{
				if (!empty($varname))
				{
					// remove existing opt out for checkboxes on this page
					$ilance->db->query("
						DELETE FROM " . DB_PREFIX . "email_optout
						WHERE email = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['email']) . "'
							AND varname = '" . $ilance->db->escape_string($varname) . "'
					", 0, null, __FILE__, __LINE__);
					if (isset($ilance->GPC['cbvarname']) AND !isset($ilance->GPC['cbvarname']["$varname"]) OR empty($ilance->GPC['cbvarname']["$varname"]))
					{
						$ilance->db->query("
							INSERT INTO " . DB_PREFIX . "email_optout
							(id, email, varname)
							VALUES (
							NULL,
							'" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['email']) . "',
							'" . $ilance->db->escape_string($varname) . "'
							)
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
			if (!empty($ilance->GPC['returnurl']))
			{
				refresh(urldecode($ilance->GPC['returnurl']) . '&note=updated');
			}
			else
			{
				refresh(HTTPS_SERVER . $ilpage['preferences'] . '?cmd=email&note=updated');
			}
			exit();
		}
	}
	// #### EMAIL HANDLER ##################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-email-change' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
	{
		$area_title = '{_updating_email_preference}';
		$page_title = SITE_NAME . ' - {_updating_email_preference}';
		$user_email = trim($ilance->GPC['email']);
		$actual_email = $_SESSION['ilancedata']['user']['email'];

		($apihook = $ilance->api('preferences_email_change_start')) ? eval($apihook) : false;

		if ($user_email != $actual_email)
		{
			if ($ilance->common->is_email_banned($user_email))
			{
				print_notice('{_email_is_banned}', '{_it_appears_this_email_address_is_banned_from_the_marketplace_please_try_another_email_address}', $ilpage['preferences'] . '?cmd=email', '{_return_to_the_previous_menu}');
				exit();
			}
			else
			{
				// email is good check if it's duplicate
				$sql = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "users
					WHERE email = '" . $ilance->db->escape_string($user_email) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					print_notice('{_duplicate_email_found}', '{_it_appears_this_email_address_is_being_used_by_another_member_on_this_marketplace_please_try_another_email_address}', $ilpage['preferences'] . '?cmd=email', '{_return_to_the_previous_menu}');
					exit();
				}
				else
				{
					$_SESSION['ilancedata']['user']['email'] = $user_email;
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET email = '" . $ilance->db->escape_string($user_email) . "'
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "email_optout
						SET email = '" . $ilance->db->escape_string($user_email) . "'
						WHERE email = '" . $ilance->db->escape_string($actual_email) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
		// dispatch a test email?
		if (isset($ilance->GPC['testnotify']) AND $ilance->GPC['testnotify'])
		{
			$categories = '';
			if ($ilconfig['globalauctionsettings_productauctionsenabled'])
			{
				$getcats = $ilance->db->query("
					SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
					FROM " . DB_PREFIX . "categories
					WHERE parentid = '0'
						AND cattype = 'product'
						AND visible = '1'
					ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
					LIMIT 10
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($getcats) > 0)
				{
					while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
					{
						$categories .= $res['title'] . LINEBREAK;
					}
				}
			}
			if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
			{
				$getcats = $ilance->db->query("
					SELECT cid, title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
					FROM " . DB_PREFIX . "categories
					WHERE parentid = '0'
						AND cattype = 'service'
						AND visible = '1'
					ORDER BY title_" . $_SESSION['ilancedata']['user']['slng'] . " ASC
					LIMIT 10
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($getcats) > 0)
				{
					while ($res = $ilance->db->fetch_array($getcats, DB_ASSOC))
					{
						$categories .= $res['title'] . LINEBREAK;
					}
				}
			}
			
			$ilance->email->mail = $user_email;
			$ilance->email->slng = fetch_user_slng($_SESSION['ilancedata']['user']['userid']);
			$ilance->email->get('register_welcome_email');
			$ilance->email->set(array (
			    '{{username}}' => $_SESSION['ilancedata']['user']['username'],
			    '{{user_id}}' => $_SESSION['ilancedata']['user']['userid'],
			    '{{first_name}}' => $_SESSION['ilancedata']['user']['firstname'],
			    '{{last_name}}' => $_SESSION['ilancedata']['user']['lastname'],
			    '{{categories}}' => $categories
			));
			$ilance->email->send();
		}

		($apihook = $ilance->api('preferences_email_change_end')) ? eval($apihook) : false;

		print_notice('{_email_preferences_changed}', '{_you_have_successfully_changed_email_options_for_your_profile}', $ilpage['preferences'] . '?cmd=email', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### START PAGE #####################################################
	else if (isset($ilance->GPC['cmd']) && $ilance->GPC['cmd'] == 'login')
	{
		$area_title = '{_login_preference_menu}';
		$page_title = SITE_NAME . ' - {_login_preference_menu}';
		$navcrumb[""] = '{_start_page_management}';
		$show['leftnav'] = true;
		$sql_prefs = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
		$res_prefs = $ilance->db->fetch_array($sql_prefs);
		
		$arr = array('main' => '{_main_menu}', 'accounting' => '{_accounting}', 'buying' => '{_buying_activity}', 'selling' => '{_selling_activity}', 'watchlist' => '{_watchlist}');
		$startpage_pulldown = construct_pulldown('startpage', 'startpage', $arr, $res_prefs['startpage'], 'style="font-family: verdana"');

		$ilance->template->fetch('main', 'preferences_startpage.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array ('session_email', 'startpage_pulldown'));
		exit();
	}
	// #### START PAGE HANDLER #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-startpage-change')
	{
		$area_title = '{_updating_start_page_preference}';
		$page_title = SITE_NAME . ' - {_updating_start_page_preference}';
		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET startpage = '" . $ilance->db->escape_string(mb_strtolower($ilance->GPC['startpage'])) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
		print_notice('{_login_preferences_changed}', '{_you_have_successfully_changed_start_page_login_preferences}', $ilpage['preferences'] . '?cmd=login', '{_return_to_the_previous_menu}');
		exit();
	}
	// ### IP RESTRICTION ##################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ip-restrict')
	{
		$area_title = '{_ip_address_restriction_menu}';
		$page_title = SITE_NAME . ' - {_ip_address_restriction_menu}';
		$navcrumb[""] = '{_ip_restrict_management}';
		$show['leftnav'] = true;
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'iprestrict') == 'yes')
		{
			$sql_prefs = $ilance->db->query("
                                SELECT iprestrict, ipaddress
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
			$res_prefs = $ilance->db->fetch_array($sql_prefs);
			$iprestrict_pulldown = construct_pulldown('iprestrict_pulldown', 'iprestrict_pulldown', array('0' => '{_no}', '1' => '{_yes}'), $res_prefs['iprestrict'], 'style="font-family: verdana"');
			$restrict_ipaddress = (empty($res_prefs['ipaddress'])) ? getenv("REMOTE_ADDR") : $res_prefs['ipaddress'];
			$ilance->template->fetch('main', 'preferences_iprestrict.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ('restrict_ipaddress', 'session_email', 'iprestrict_pulldown'));
			exit();
		}
		else
		{
			$area_title = '{_access_denied_to_ip_restriction_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_ip_restriction_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('iprestrict'));
			exit();
		}
	}
	// ### IP RESTRICTION HANDLER ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-iprestrict-change')
	{
		$area_title = '{_updating_ip_restriction_preference}';
		$page_title = SITE_NAME . ' - {_updating_ip_restriction_preference}';
		$ilance->GPC['iprestrict_pulldown'] = $ilance->GPC['iprestrict_pulldown'] == "yes" ? 1 : 0;
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET iprestrict = '" . intval($ilance->GPC['iprestrict_pulldown']) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'iprestrict') == 'yes')
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET ipaddress = '" . $ilance->db->escape_string($ilance->GPC['ipaddress']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
			print_notice('{_login_preferences_changed}', '{_you_have_successfully_changed_ip_address_restriction_login_preferences_for_your_profile}', $ilpage['preferences'] . '?cmd=ip-restrict', '{_return_to_the_previous_menu}');
			exit();
		}
		else
		{
			$area_title = '{_access_denied_to_ip_restriction_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_ip_restriction_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('iprestrict'));
			exit();
		}
	}
	// #### LANGUAGE #######################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'language')
	{
		$area_title = '{_language_preference}';
		$page_title = SITE_NAME . ' - {_language_preference}';
		$navcrumb[""] = '{_language_preference}';
		$show['leftnav'] = true;
		$values = array();
		$sql_langs = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
		while ($res_langs = $ilance->db->fetch_array($sql_langs, DB_ASSOC))
		{
			$values[$res_langs['languageid']] = stripslashes($res_langs['title']);
		}
		$language_pulldown = construct_pulldown('languageid', 'languageid', $values, $_SESSION['ilancedata']['user']['languageid'], 'style="font-family: verdana"');
		$ilance->template->fetch('main', 'preferences_language.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array ('language_pulldown'));
		exit();
	}
	// #### LANGUAGE HANDLER ###############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-language-change')
	{
		$area_title = '{_updating_language_preference}';
		$page_title = SITE_NAME . ' - {_updating_language_preference}';
		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                        SET languageid = '" . intval($ilance->GPC['languageid']) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
		$langdata = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "language
                        WHERE languageid = '" . intval($ilance->GPC['languageid']) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($langdata) > 0)
		{
			$langinfo = $ilance->db->fetch_array($langdata);
			$_SESSION['ilancedata']['user']['languageid'] = $langinfo['languageid'];
			$_SESSION['ilancedata']['user']['languagecode'] = $langinfo['languagecode'];
			$_SESSION['ilancedata']['user']['slng'] = mb_substr($_SESSION['ilancedata']['user']['languagecode'] ? $_SESSION['ilancedata']['user']['languagecode'] : 'english', 0, 3);
		}
		print_notice('{_language_preferences_changed}', '{_you_have_successfully_changed_the_language_preference_for_your_profile}', $ilpage['preferences'] . '?cmd=language', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### CURRENCY #######################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'currency')
	{
		$area_title = '{_currency_preference}';
		$page_title = SITE_NAME . ' - {_currency_preference}';
		$navcrumb[""] = '{_currency_preference}';
		$show['leftnav'] = true;
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
		{
			$sql_prefs = $ilance->db->query("
				SELECT currencyid
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			$res_prefs = $ilance->db->fetch_array($sql_prefs, DB_ASSOC);
			$default_user_currencyid = $res_prefs['currencyid'];
			$arr = array();
			$sql_cur = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "currency_id, currency_name, currency_abbrev, symbol_left, symbol_right
                                FROM " . DB_PREFIX . "currency
                        ", 0, null, __FILE__, __LINE__);
			while ($res_cur = $ilance->db->fetch_array($sql_cur, DB_ASSOC))
			{
				$arr[$res_cur['currency_id']] = $res_cur['currency_abbrev'] . ' - ' . $res_cur['currency_name'] . ' (' . (empty($res_cur['symbol_left']) ? $res_cur['symbol_right'] : $res_cur['symbol_left']) . ')';
			}
			$currency_pulldown = construct_pulldown('currencyid', 'currencyid', $arr, $default_user_currencyid, 'class="select-250"');
			$ilance->template->fetch('main', 'preferences_currency.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', array ('currency_pulldown'));
			exit();
		}
		else
		{
			$area_title = '{_access_denied_to_currency_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_currency_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('enablecurrencyconversion'));
			exit();
		}
	}
	// #### CURRENCY HANDLER ###############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-currency-change')
	{
		$area_title = '{_updating_currency_preference}';
		$page_title = SITE_NAME . ' - {_updating_currency_preference}';
		
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'no')
		{
			$area_title = '{_access_denied_to_currency_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_currency_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('enablecurrencyconversion'));
			exit();
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET currencyid = '" . intval($ilance->GPC['currencyid']) . "'
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		$sql = $ilance->db->query("
			SELECT currency_name, symbol_left, currency_abbrev
			FROM " . DB_PREFIX . "currency
			WHERE currency_id = '" . intval($ilance->GPC['currencyid']) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$_SESSION['ilancedata']['user']['currencyid'] = intval($ilance->GPC['currencyid']);
		$_SESSION['ilancedata']['user']['currencyname'] = $res['currency_name'];
		$_SESSION['ilancedata']['user']['currencysymbol'] = $res['symbol_left'];
		$_SESSION['ilancedata']['user']['currency_abbrev'] = $res['currency_abbrev'];
		print_notice('{_currency_preferences_changed}', '{_you_have_successfully_changed_the_currency_preference_for_your_profile}', $ilpage['preferences'] . '?cmd=currency', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### ATTACHMENTS ####################################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'attachments')
	{
		$show['widescreen'] = false;
		$show['leftnav'] = true;
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachments') == 'no')
		{
			$area_title = '{_access_denied_to_attachment_resources}';
			$page_title = SITE_NAME . ' - {_access_denied_to_attachment_resources}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('attachments'));
			exit();
		}
		$area_title = '{_attachments_manager}';
		$page_title = SITE_NAME . ' - {_attachments_manager}';
		$navcrumb[""] = '{_attachments_manager}';
		$topnavlink = array ('attachments');
		$array = array('cmd' => '_attachment-action');
		$inputcrypted = encrypt_url($array);
		$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
		$queryextra = '';
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$queryextra = "AND (attachtype != 'portfolio' AND attachtype != 'project' AND attachtype != 'bid' AND attachtype != 'digital')";
		}
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$queryextra = "AND (attachtype != 'itemphoto' AND attachtype != 'slideshow' AND attachtype != 'stores' AND attachtype != 'storesitemphoto' AND attachtype != 'storesdigital' AND attachtype != 'storesbackground')";
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$queryextra = '';
		}
		$limit = ' ORDER BY date DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		$cntexe = $ilance->db->query("
			SELECT COUNT(*) AS number
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND visible = '1'
				$queryextra
		", 0, null, __FILE__, __LINE__);
		$cntarr = $ilance->db->fetch_array($cntexe, DB_ASSOC);
		$number = $cntarr['number'];
		$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count = 0;
		$attach_usage_total = 0;
		$sql_file_sum = $ilance->db->query("
			SELECT SUM(filesize) AS attach_usage_total
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_file_sum) > 0)
		{
			$res_file_sum = $ilance->db->fetch_array($sql_file_sum, DB_ASSOC);
			$attach_usage_total = print_filesize($res_file_sum['attach_usage_total']);
		}
		$ilance->categories->build_array('product', $_SESSION['ilancedata']['user']['slng'], 0, true);
		$res = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filesize_original, filesize_full, filesize_search, filesize_gallery, filesize_snapshot, filesize_mini, filehash, ipaddress, tblfolder_ref, invoiceid, width_mini, height_mini, width_full, height_full, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, width_original, height_original
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND visible = '1'
				$queryextra
			$limit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($res) > 0)
		{
			while ($row = $ilance->db->fetch_array($res, DB_ASSOC))
			{
				$row['attach_id'] = $row['attachid'];
				$row['attach_filename'] = $row['filename'];
				$row['attach_cat'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['category_id']);
				$row['attach_size'] = print_filesize($row['filesize_original']);
				$row['attach_type'] = $ilance->attachment_tools->fetch_attachment_type($row['attachtype'], $row['project_id'], $row['attachid']);
				$row['attach_views'] = number_format($row['counter']);
				$row['attach_date'] = print_date($row['date'], $ilconfig['globalserverlocale_globaldateformat'], 0, 0);
				$row['attach_action'] = '<input type="checkbox" name="attachid[]" value="' . $row['attachid'] . '" />';
				if ($row['invoiceid'] == '0')
				{
					$row['invoiceid'] = '<span title="{_none}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_litegray.png" /></span>';
				}
				else
				{
					$row['invoiceid'] = '<span title="{_invoice} #' . $row['invoiceid'] . '"><a href="' . HTTP_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['invoiceid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice.png" /></a></span>';
				}
				$attachextension = fetch_extension($row['filename']) . '.gif';
				if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
				{
					$attachextension = fetch_extension($row['filename']) . '.gif';
				}
				else
				{
					$attachextension = 'attach.gif';
				}
				$row['attachextension'] = '<span title="' . $row['filetype'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" border="0" alt="" /></span>';
				$row['sizes'] = $ilance->attachment_tools->fetch_attachment_dimensions($row);
				$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';
				$attachment_rows[] = $row;
				$row_count++;
			}
			$show['no_attachment_rows'] = false;
		}
		else
		{
			$show['no_attachment_rows'] = true;
		}
		$scriptpage = $ilpage['preferences'] . '?cmd=attachments';
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $scriptpage);
		$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
		$limit2 = ' ORDER BY date_added DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
		$SQL3 = "
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filesize_original, filesize_full, filesize_search, filesize_gallery, filesize_snapshot, filesize_mini, filehash, ipaddress, tblfolder_ref, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, width_original, height_original, width_full, height_full
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND visible = '0'
				$queryextra
			$limit
		";
		$SQL4 = "
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filesize_original, filesize_full, filesize_search, filesize_gallery, filesize_snapshot, filesize_mini, filehash, ipaddress, tblfolder_ref, width_mini, height_mini, width_search, height_search, width_gallery, height_gallery, width_snapshot, height_snapshot, width_original, height_original, width_full, height_full
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND visible = '0'
				$queryextra
		";
		$numberrows2 = $ilance->db->query($SQL4);
		$number2 = $ilance->db->num_rows($numberrows2);
		$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
		$row_count2 = 0;
		$result2 = $ilance->db->query($SQL3);
		if ($ilance->db->num_rows($result2) > 0)
		{
			while ($row2 = $ilance->db->fetch_array($result2, DB_ASSOC))
			{
				$row2['attach_id'] = $row2['attachid'];
				$row2['attach_filename'] = $row2['filename'];
				$row2['attach_type'] = $ilance->attachment_tools->fetch_attachment_type($row2['attachtype'], $row2['project_id'], $row2['attachid']);
				$attachextension = fetch_extension($row2['filename']) . '.gif';
				if (file_exists(DIR_SERVER_ROOT . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension))
				{
					$attachextension = fetch_extension($row2['filename']) . '.gif';
				}
				else
				{
					$attachextension = 'attach.gif';
				}
				$row2['attachextension'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/' . $attachextension . '" border="0" alt="" />';
				$row2['status'] = '{_review_in_progress}';
				$row2['actions'] = '<input type="checkbox" name="attachid[]" value="' . $row2['attachid'] . '" />';
				$row2['sizes'] = $ilance->attachment_tools->fetch_attachment_dimensions($row2);
				$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$attachment_pending_rows[] = $row2;
				$row_count2++;
			}
			$show['no_attachment_pending_rows'] = false;
		}
		else
		{
			$show['no_attachment_pending_rows'] = true;
		}
		$attach_user_max = print_filesize($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'attachlimit'));
		$scriptpage2 = $ilpage['preferences'] . '?cmd=attachments';
		$prevnext2 = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $scriptpage2, 'page2');
		$ilance->template->fetch('main', 'preferences_attachments.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'attachment_rows');
		$ilance->template->parse_loop('main', 'attachment_pending_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array ('inputcrypted', 'attach_user_max', 'attach_usage_total', 'prevnext', 'prevnext2'));
		exit();
	}
	// #### PROFILE MANAGEMENT #############################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'profile')
	{
		$area_title = '{_profile_update_menu}';
		$page_title = SITE_NAME . ' - {_profile_update_menu}';
		$navcrumb[""] = '{_personal_profile}';
		$topnavlink = array (
		    'mycp',
		    'preferencesprofile'
		);
		$show['leftnav'] = true;
		/*if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'updateprofile') == 'no')
		{
			$area_title = '{_access_denied}';
			$page_title = SITE_NAME . ' - {_access_denied}';
			print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}' . " <a href='" . $ilpage['subscription'] . "'><strong>" . '{_click_here}' . "</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('updateprofile'));
			exit();
		}*/
		$sql_user = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_user) > 0)
		{
			$res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
			$user_countryid = $res_user['country'];
			$first_name = handle_input_keywords($res_user['first_name']);
			$last_name = handle_input_keywords($res_user['last_name']);
			$phone = handle_input_keywords($res_user['phone']);
			$address = handle_input_keywords($res_user['address']);
			$address2 = handle_input_keywords($res_user['address2']);
			$city = handle_input_keywords($res_user['city']);
			$zipcode = handle_input_keywords($res_user['zip_code']);
			$user_state = handle_input_keywords($res_user['state']);
			// current role (via session)
			$roleid = intval($_SESSION['ilancedata']['user']['roleid']);
			$regnumber = $vatnumber = $dnbnumber = $companyname = '';
			$sqlreg = $ilance->db->query("
                                SELECT regnumber, vatnumber, companyname, dnbnumber
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlreg) > 0)
			{
				$resreg = $ilance->db->fetch_array($sqlreg, DB_ASSOC);
				$regnumber = handle_input_keywords($resreg['regnumber']);
				$vatnumber = handle_input_keywords($resreg['vatnumber']);
				$dnbnumber = handle_input_keywords($resreg['dnbnumber']);
				$companyname = handle_input_keywords($resreg['companyname']);
			}
			$sql_loc = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "locations
                                WHERE locationid = '" . intval($user_countryid) . "'
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_loc) > 0)
			{
				$res_loc = $ilance->db->fetch_array($sql_loc, DB_ASSOC);
				$_SESSION['ilancedata']['user']['countryid'] = $res_loc['locationid'];
			}
			if ($ilconfig['registrationdisplay_dob'])
			{
				$dateofbirth = $_SESSION['ilancedata']['user']['dob'];
				$dobsplit = explode('-', $dateofbirth);
				$dob_year = $dobsplit[0];
				$dobmonth = $dobsplit[1];
				$dobday = $dobsplit[2];
				$month = '<option value="01" ';
				if ($dobmonth == '01')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_january}</option>';
				$month .= '<option value="02" ';
				if ($dobmonth == '02')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_february}</option>';
				$month .= '<option value="03" ';
				if ($dobmonth == '03')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_march}</option>';
				$month .= '<option value="04" ';
				if ($dobmonth == '04')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_april}</option>';
				$month .= '<option value="05" ';
				if ($dobmonth == '05')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_may}</option>';
				$month .= '<option value="06" ';
				if ($dobmonth == '06')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_june}</option>';
				$month .= '<option value="07" ';
				if ($dobmonth == '07')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_july}</option>';
				$month .= '<option value="08" ';
				if ($dobmonth == '08')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_august}</option>';
				$month .= '<option value="09" ';
				if ($dobmonth == '09')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_september}</option>';
				$month .= '<option value="10" ';
				if ($dobmonth == '10')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_october}</option>';
				$month .= '<option value="11" ';
				if ($dobmonth == '11')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_november}</option>';
				$month .= '<option value="12" ';
				if ($dobmonth == '12')
				{
					$month .= 'selected="selected"';
				} $month .= '>{_december}</option>';
				$days = 1;
				$day = '';
				while ($days <= 31)
				{
					if ($days < 10)
					{
						$day .= '<option value="0' . $days . '" ';
						if ($dobday == $days)
						{
							$day .= 'selected="selected"';
						} $day .= '>' . $days . '</option>';
					}
					else
					{
						$day .= '<option value="' . $days . '" ';
						if ($dobday == $days)
						{
							$day .= 'selected="selected"';
						} $day .= '>' . $days . '</option>';
					}
					$days++;
				}
			}
			// construct countries / states pulldown
			$jscity = $city;
			$formid = 'forms[1]';
			$countryid = fetch_country_id($_SESSION['ilancedata']['user']['country'], $_SESSION['ilancedata']['user']['slng']);
			$country_js_pulldown = $ilance->common_location->construct_country_pulldown($countryid, $res_loc['location_' . $_SESSION['ilancedata']['user']['slng']], 'country', false, 'state');
			$state_js_pulldown = '<div id="stateid" style="height:20px">' . $ilance->common_location->construct_state_pulldown($countryid, $user_state, 'state') . '</div>';
			// custom registration questions
			$customquestions = $ilance->registration_questions->construct_register_questions(0, 'updateprofile', $_SESSION['ilancedata']['user']['userid'], $roleid);
			// redirection?
			$redirect = '';
			if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
			{
				$redirect = handle_input_keywords($ilance->GPC['returnurl']);
			}
			else
			{
				if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
				{
					$redirect = handle_input_keywords($ilance->GPC['redirect']);
				}
			}
			// #### gender #########################################
			if ($ilconfig['genderactive'])
			{
				if ($res_user['gender'] == '')
				{
					$cb_gender_undecided = 'checked="checked"';
					$cb_gender_male = '';
					$cb_gender_female = '';
				}
				else
				{
					if ($res_user['gender'] == 'male')
					{
						$cb_gender_undecided = '';
						$cb_gender_male = 'checked="checked"';
						$cb_gender_female = '';
					}
					else if ($res_user['gender'] == 'female')
					{
						$cb_gender_undecided = '';
						$cb_gender_male = '';
						$cb_gender_female = 'checked="checked"';
					}
				}
			}
			$pprint_array = array ('cb_gender_undecided', 'cb_gender_male', 'cb_gender_female', 'dnbnumber', 'companyname', 'customquestions', 'redirect', 'dynamic_js_bodyend', 'regnumber', 'vatnumber', 'month', 'day', 'dob_year', 'zipcode', 'first_name', 'last_name', 'phone', 'address', 'address2', 'city', 'dynamic_js_bodyend', 'state_js_pulldown', 'country_js_pulldown', 'language_pulldown');

			($apihook = $ilance->api('start_edit_personal_profile')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'preferences_profile.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	// #### PROFILE UPDATE HANDLER #########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-profile-change')
	{
		$area_title = '{_updating_profile_information}';
		$page_title = SITE_NAME . ' - {_updating_profile_information}';

		($apihook = $ilance->api('preferences_profile_change_start')) ? eval($apihook) : false;

		$sql_loc = $ilance->db->query("
                        SELECT locationid
                        FROM " . DB_PREFIX . "locations
                        WHERE location_" . $_SESSION['ilancedata']['user']['slng'] . " = '" . $ilance->db->escape_string($ilance->GPC['country']) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		$res_loc = $ilance->db->fetch_array($sql_loc, DB_ASSOC);
		$extraquery = '';
		// #### date of birth ##########################################
		if ($ilconfig['registrationdisplay_dob'] AND isset($ilance->GPC['year']) AND isset($ilance->GPC['month']) AND isset($ilance->GPC['day']))
		{
			$year = intval($ilance->GPC['year']);
			$month = intval($ilance->GPC['month']);
			$day = intval($ilance->GPC['day']);

			$_SESSION['ilancedata']['user']['dob'] = $year . '-' . $month . '-' . $day;
			$extraquery .= "dob = '" . $year . "-" . $month . "-" . $day . "',";
		}
		// #### gender #################################################
		if ($ilconfig['genderactive'] AND isset($ilance->GPC['gender']) AND !empty($ilance->GPC['gender']))
		{
			$extraquery .= "gender = '" . $ilance->db->escape_string($ilance->GPC['gender']) . "',";
		}
		$ilance->GPC['zipcode'] = handle_input_keywords(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zipcode']))));
		// #### update user current session ############################
		$_SESSION['ilancedata']['user']['address'] = ucwords(stripslashes($ilance->GPC['address']));
		$_SESSION['ilancedata']['user']['address2'] = ucwords(stripslashes($ilance->GPC['address2']));
		$_SESSION['ilancedata']['user']['fulladdress'] = ucwords(stripslashes($ilance->GPC['address'])) . ' ' . ucwords(stripslashes($ilance->GPC['address2']));
		$_SESSION['ilancedata']['user']['postalzip'] = format_zipcode($ilance->GPC['zipcode']);
		$_SESSION['ilancedata']['user']['city'] = ucwords($ilance->GPC['city']);
		$_SESSION['ilancedata']['user']['state'] = ucwords($ilance->GPC['state']);
		$_SESSION['ilancedata']['user']['country'] = $ilance->common_location->print_country_name($res_loc['locationid']);
		$_SESSION['ilancedata']['user']['countryid'] = $res_loc['locationid'];
		$_SESSION['ilancedata']['user']['countryshort'] = $ilance->common_location->print_country_name($res_loc['locationid'], $_SESSION['ilancedata']['user']['slng'], true);
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['zipcode'])));

		($apihook = $ilance->api('start_update_personal_profile')) ? eval($apihook) : false;

		$ilance->db->query("
                        UPDATE " . DB_PREFIX . "users
                	SET first_name = '" . $ilance->db->escape_string($ilance->GPC['first_name']) . "',
			    last_name = '" . $ilance->db->escape_string($ilance->GPC['last_name']) . "',
			    phone = '" . $ilance->db->escape_string($ilance->GPC['phone']) . "',
			    address = '" . $ilance->db->escape_string($ilance->GPC['address']) . "',
			    address2 = '" . $ilance->db->escape_string($ilance->GPC['address2']) . "',
			    country = '" . $res_loc['locationid'] . "',
			    city = '" . $ilance->db->escape_string(ucwords($ilance->GPC['city'])) . "',
			    state = '" . $ilance->db->escape_string(ucwords($ilance->GPC['state'])) . "',
			    $extraquery
			    zip_code = '" . $ilance->db->escape_string($ilance->GPC['zipcode']) . "'
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                ", 0, null, __FILE__, __LINE__);
		// business registration numbers
		if (isset($ilance->GPC['companyname']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET companyname = '" . $ilance->db->escape_string($ilance->GPC['companyname']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		}
		if (isset($ilance->GPC['regnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET regnumber = '" . $ilance->db->escape_string($ilance->GPC['regnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		}
		if (isset($ilance->GPC['vatnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET vatnumber = '" . $ilance->db->escape_string($ilance->GPC['vatnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		}
		// dnb number
		if (isset($ilance->GPC['dnbnumber']))
		{
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET dnbnumber = '" . $ilance->db->escape_string($ilance->GPC['dnbnumber']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		}
		// are we changing roles?
		if (!empty($ilance->GPC['roleid']))
		{
			// member is changing roles ..
			$_SESSION['ilancedata']['user']['roleid'] = intval($ilance->GPC['roleid']);

			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "subscription_user
                                SET roleid = '" . intval($ilance->GPC['roleid']) . "'
                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ", 0, null, __FILE__, __LINE__);
		}
		// process custom registration questions
		if (!empty($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
		{
			$ilance->registration->process_custom_register_questions($ilance->GPC['custom'], intval($_SESSION['ilancedata']['user']['userid']));
		}
		if (isset($ilance->GPC['redirect']) AND !empty($ilance->GPC['redirect']))
		{
			refresh($ilance->GPC['redirect']);
			exit();
		}
		print_notice('{_profile_information_updated}', '{_you_have_successfully_updated_information_for_your_personal_profile}', $ilpage['preferences'] . '?cmd=profile', '{_return_to_the_previous_menu}');
		exit();
	}
	// #### MY FAVORITE SEARCHES ###########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'favorites')
	{
		$show['widescreen'] = false;
		$show['leftnav'] = true;
		$topnavlink = array ('saved_searches');
		// #### DELETE SAVED SEARCHES ##################################
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'deletesearches')
		{
			if (!empty($ilance->GPC['searchid']) AND is_array($ilance->GPC['searchid']))
			{
				foreach ($ilance->GPC['searchid'] AS $searchid)
				{
					$ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "search_favorites
                                                WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                                        AND searchid = '" . intval($searchid) . "'
                                        ", 0, null, __FILE__, __LINE__);
				}
			}
			refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			exit();
		}
		// #### SAVE NEW FAVORITE SEARCH ###############################
		else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'save' AND !empty($ilance->GPC['fav']))
		{
			$unc = urldecode($ilance->GPC['fav']);
			$unc = unserialize($unc);
			if (!empty($unc) AND is_array($unc))
			{
				$url = '';
				foreach ($unc AS $value)
				{
					if (is_array($value))
					{
						foreach ($value AS $search => $option)
						{
							if ($search == 'sid')
							{
								if (is_array($option))
								{
									foreach ($option AS $searchkey => $searchsel)
									{
										if (!empty($searchsel))
										{
											$url .= '&amp;sid[' . $searchkey . ']=' . $searchsel;
										}
									}
								}
							}
							else
							{
								if (!empty($search) AND !empty($option))
								{
									if ($search == 'q')
									{
										$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
										$unc['keywords'] = $option;
									}
									else if ($search == 'mode')
									{
										$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
										$unc['cattype'] = $option;
									}
									else
									{
										$url .= '&amp;' . $search . '=' . urlencode(html_entity_decode(urldecode($option)));
									}
								}
							}
						}
					}
				}
				if (empty($ilance->GPC['title']))
				{
					$unc['keywords'] = '{_custom_search}';
				}
				else
				{
					$unc['keywords'] = $ilance->GPC['title'];
				}
				if (empty($unc['cattype']))
				{
					$unc['keywords'] = 'service';
				}
				$ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "search_favorites
					    (searchid, user_id, searchoptions, searchoptionstext, title, cattype, subscribed, added)
                                        VALUES
					    (NULL,
					    '" . $_SESSION['ilancedata']['user']['userid'] . "',
					    '" . $ilance->db->escape_string($url) . "',
					    '" . $ilance->db->escape_string($ilance->GPC['verbose']) . "',
					    '" . $ilance->db->escape_string($unc['keywords']) . "',
					    '" . $ilance->db->escape_string($unc['cattype']) . "',
					    '0',
					    '" . DATETIME24H . "')
                                ", 0, null, __FILE__, __LINE__);
			}
			refresh(HTTP_SERVER . $ilpage['preferences'] . '?cmd=favorites');
			exit;
		}
		$area_title = '{_my_favorite_searches}';
		$page_title = SITE_NAME . ' - {_my_favorite_searches}';
		$navcrumb[""] = '{_favorite_searches}';
		$headinclude .= "<script type=\"text/javascript\">
<!--
var searchid = 0;
var value = '';
var imgtag = '';
var favoriteicon = '';
var status = '';
function fetch_response()
{
        if (xmldata.handler.readyState == 4 && xmldata.handler.status == 200 && xmldata.handler.responseXML)
        {
                response = fetch_tags(xmldata.handler.responseXML, 'status')[0];
                phpstatus = xmldata.fetch_data(response);
                searchiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                status = searchiconsrc.match(/\/unchecked.gif/gi);
                if (status == '/unchecked.gif')
                {
                       status = 'unchecked';
                }
                else
                {
                       status = 'checked';
                }                                
                if (status == 'unchecked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                favoriteiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                                imgtag = fetch_js_object('inline_favorite_' + xmldata.searchid);
                                favoriteicon2 = favoriteiconsrc.replace(/unchecked.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
                                favoriteicon = favoriteiconsrc.replace(/unchecked.gif/gi, 'checked.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert_js(phpstatus);
                        }
                }
                else if (status == 'checked')
                {
                        if (phpstatus == 'on' || phpstatus == 'off')
                        {
                                favoriteiconsrc = fetch_js_object('inline_favorite_' + xmldata.searchid).src;
                                imgtag = fetch_js_object('inline_favorite_' + xmldata.searchid);
                                favoriteicon2 = favoriteiconsrc.replace(/checked.gif/gi, 'working.gif');
                                imgtag.src = favoriteicon2;
                                favoriteicon = favoriteiconsrc.replace(/checked.gif/gi, 'unchecked.gif');
                                var t = window.setTimeout('reset_image()', 700);
                        }
                        else
                        {
                                alert_js(phpstatus); 
                        }
                }
                xmldata.handler.abort();
        }
}
function update_favorite(searchid)
{                        
        xmldata = new AJAX_Handler(true);
        searchid = urlencode(searchid);
        xmldata.searchid = searchid;
        searchiconsrc = fetch_js_object('inline_favorite_' + searchid).src;
        status = searchiconsrc.match(/\/unchecked.gif/gi);
        if (status == '/unchecked.gif')
        {
               value = 'on';
        }
        else
        {
               value = 'off';
        }
        xmldata.onreadystatechange(fetch_response);
        xmldata.send(AJAXURL, 'do=searchfavorites&value=' + value + '&searchid=' + searchid + '&s=' + ILSESSION + '&token=' + ILTOKEN);                        
}
var urlBase = AJAXURL + '?do=inlineedit&action=favsearchtitle&id=';
//-->
</script>
";
		$show['no_favorites'] = false;
		$sql = $ilance->db->query("
                        SELECT searchid, user_id, searchoptions, searchoptionstext, title, cattype, subscribed, added, lastsent, lastseenids
                        FROM " . DB_PREFIX . "search_favorites
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        ORDER BY searchid DESC
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$row_count = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$searchoptions = stripslashes($res['searchoptions']);
				$searchoptions = mb_substr($searchoptions, 5);
				$res['searchoptionstext'] = '<span>' . stripslashes($res['searchoptionstext']) . '</span>';
				$res['action'] = '<input type="checkbox" name="searchid[]" value="' . $res['searchid'] . '" />';
				if ($res['subscribed'])
				{
					$res['ajax_subscribed'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_click_to_enable_disable}' . '" border="0" id="inline_favorite_' . $res['searchid'] . '" onclick="update_favorite(' . $res['searchid'] . ');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />';
				}
				else
				{
					$res['ajax_subscribed'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_click_to_enable_disable}' . '" border="0" id="inline_favorite_' . $res['searchid'] . '" onclick="update_favorite(' . $res['searchid'] . ');" style="cursor:hand" onmouseover="this.style.cursor=\'pointer\'" />';
				}
				if ($res['cattype'] == 'service')
				{
					$res['cattype'] = '{_service}';
				}
				else
				{
					$res['cattype'] = '{_product}';
				}
				$date1split = explode(' ', $res['added']);
				$date2split = explode('-', $date1split[0]);
				$totaldays = 30;
				$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
				$days = ($totaldays - $elapsed);
				if ($days < 0)
				{
					// somehow the cron job did not expire the save search subscription for this member
					$ilance->db->query("
                                                UPDATE " . DB_PREFIX . "search_favorites
                                                SET subscribed = '0'
                                                WHERE searchid = '" . $res['searchid'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					$res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '"></span>';
					if ($res['lastsent'] == '0000-00-00 00:00:00')
					{
						$res['lastsent'] = '{_never}';
					}
					else
					{
						$res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					}
				}
				else
				{
					if ($res['subscribed'])
					{
						$res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '">' . $days . ' {_days_left}</span>';
						if ($res['lastsent'] == '0000-00-00 00:00:00')
						{
							$res['lastsent'] = '{_never}';
						}
						else
						{
							$res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						}
					}
					else
					{
						$res['daysleft'] = '<span id="daysleft_' . $res['searchid'] . '"></span>';
						if ($res['lastsent'] == '0000-00-00 00:00:00')
						{
							$res['lastsent'] = '{_never}';
						}
						else
						{
							$res['lastsent'] = print_date($res['lastsent'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						}
					}
				}
				$res['title'] = str_replace('"', "&#34;", $res['title']);
				$res['title'] = str_replace("'", "&#39;", $res['title']);
				$res['title'] = str_replace("<", "&#60;", $res['title']);
				$res['title'] = str_replace(">", "&#61;", $res['title']);
				$res['title'] = '<strong><span id="phrase' . $res['searchid'] . 'inline" title="{_doubleclick_to_edit}"><span ondblclick="do_inline_edit(' . $res['searchid'] . ', this);">' . handle_input_keywords($res['title']) . '</span></span></strong>';
				$res['edit'] = '<div class="smaller gray" style="padding-top:3px">{_added} ' . print_date($res['added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</div>';
				$res['goto'] = '<a href="' . HTTP_SERVER . $ilpage['search'] . '?' . $searchoptions . '&amp;searchid=' . $res['searchid'] . '">{_go_to_search_results}</a>';
				$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$row_count++;
				$favorites[] = $res;
			}
		}
		else
		{
			$show['no_favorites'] = true;
		}
		$returnurl = '';
		if (!empty($ilance->GPC['returnurl']))
		{
			$returnurl = $ilance->GPC['returnurl'];
		}
		$pprint_array = array ('returnurl', 'distance', 'subcategory_name', 'text', 'prevnext', 'prevnext2');
		$ilance->template->fetch('main', 'preferences_favorites.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'favorites');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = '{_preferences_menu}';
		$page_title = SITE_NAME . ' - {_preferences_menu}';
		$show['leftnav'] = true;	
		$sql_language = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "language
		", 0, null, __FILE__, __LINE__);	
		$langselect = $ilance->db->num_rows($sql_language);		
		$show['language_diasble'] = (isset($langselect) AND $langselect > 1) ? true : false;	
		$ilance->template->jsinclude = array('header' => array('functions'), 'footer' => array('v4','tooltip','autocomplete','cron'));
		$ilance->template->fetch('main', 'preferences.html');
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array());
		exit();
	}
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['preferences'] . print_hidden_fields(true, array(), true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>