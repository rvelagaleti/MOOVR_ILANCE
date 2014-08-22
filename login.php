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
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'md5',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array('main');

// #### setup script location ##################################################
define('LOCATION', 'login');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[login]" => $ilcrumbs["$ilpage[login]"]);

// #### MEMBER LOGIN PROCESS ###################################################
$redirect = isset($ilance->GPC['redirect']) ? strip_tags($ilance->GPC['redirect']) : '';

($apihook = $ilance->api('login_start')) ? eval($apihook) : false;

// #### LOGIN AUTHENTICATION PROCESS ###########################################
if (isset($ilance->GPC['login_process']) AND $ilance->GPC['login_process'] > 0)
{
	$area_title = '{_sign_in}<div class="smaller">{_submitting_login_information}</div>';
	$page_title = SITE_NAME . ' - {_submitting_login_information}';
	$badusername = $badpassword = true;
	$userinfo = array();
	$ilance->GPC['username'] = trim($ilance->GPC['username']);
	$unicode_name = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $ilance->GPC['username']);
	$username_banned = ($ilance->common->is_username_banned($ilance->GPC['username']) OR $ilance->common->is_username_banned($unicode_name)) ? true : false;
	if (!empty($ilance->GPC['username']) AND $username_banned == false)
	{
		// default subscription params
		$userinfo['roleid'] = -1;
		$userinfo['subscriptionid'] = $userinfo['cost'] = 0;
		$userinfo['active'] = 'no';
		
		$sql = $ilance->db->query("
			SELECT u.*, su.roleid, su.subscriptionid, su.active, sp.cost, c.currency_name, c.currency_abbrev, l.languagecode
			FROM " . DB_PREFIX . "users AS u
			LEFT JOIN " . DB_PREFIX . "subscription_user su ON u.user_id = su.user_id
			LEFT JOIN " . DB_PREFIX . "subscription sp ON su.subscriptionid = sp.subscriptionid
			LEFT JOIN " . DB_PREFIX . "currency c ON u.currencyid = c.currency_id
			LEFT JOIN " . DB_PREFIX . "language l ON u.languageid = l.languageid
			WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
			GROUP BY username
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$userinfo = $ilance->db->fetch_array($sql, DB_ASSOC);
			$badusername = $badpassword = false;
			if ($userinfo['password'] != iif($ilance->GPC['password'] AND !$ilance->GPC['md5pass'], md5(md5($ilance->GPC['password']) . $userinfo['salt']), '') AND $userinfo['password'] != md5($ilance->GPC['md5pass'] . $userinfo['salt']) AND $userinfo['password'] != iif($ilance->GPC['md5pass_utf'], md5($ilance->GPC['md5pass_utf'] . $userinfo['salt']), ''))
			{
				$badpassword = true;
			}
		}
		else
		{
			($apihook = $ilance->api('login_process_start_external_authentication')) ? eval($apihook) : false;
		}
		
		($apihook = $ilance->api('login_process_start')) ? eval($apihook) : false;
		
		if ($badusername == false AND $badpassword == false)
		{
			// update last seen for this member
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET lastseen = '" . DATETIME24H . "'
				WHERE user_id = '" . $userinfo['user_id'] . "'
			");
			if ($userinfo['status'] == 'active')
			{
				// ip restriction
				if ($userinfo['iprestrict'] AND !empty($userinfo['ipaddress']))
				{
					if (IPADDRESS != $userinfo['ipaddress'])
					{
						refresh(HTTPS_SERVER . $ilpage['login'] . '?error=iprestrict');	
						exit();	
					}
				}
				// create valid user session
				$ilance->sessions->build_user_session($userinfo);
				
				($apihook = $ilance->api('login_globalize_session')) ? eval($apihook) : false;

				if (isset($ilance->GPC['remember']) AND $ilance->GPC['remember'])
				{
					// user has chosen the marketplace to remember them
					set_cookie('userid', $ilance->crypt->three_layer_encrypt($userinfo['user_id'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
					set_cookie('password', $ilance->crypt->three_layer_encrypt($userinfo['password'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
					set_cookie('username', $ilance->crypt->three_layer_encrypt($userinfo['username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']));
				}
				// remember users last visit and last hit activity regardless of remember me preference
				set_cookie('lastvisit', DATETIME24H);
				set_cookie('lastactivity', DATETIME24H);
				set_cookie('radiuszip', handle_input_keywords(format_zipcode($userinfo['zip_code'])));
                                if (!empty($redirect))
				{
					$landing = $redirect;
				}
				else if (!empty($userinfo['startpage']) AND $ilance->GPC['login_process'] == '1')
				{
					$landing = $userinfo['startpage'] . '.php';
				}
				else
				{
					$landing = $ilpage['main'] . '?cmd=cp';
				}
				// message of the day redirector
                                $motd = $ilance->db->fetch_field(DB_PREFIX . "motd", "date = '" . DATETODAY . "'", "content");
                                if ((!empty($_COOKIE[COOKIE_PREFIX . 'motd']) AND $_COOKIE[COOKIE_PREFIX . 'motd'] != DATETODAY AND !empty($motd) AND $motd != '') OR (empty($_COOKIE[COOKIE_PREFIX . 'motd']) AND !empty($motd) AND $motd != ''))
                                {
                                        set_cookie('motd', DATETODAY);
                                        $navcrumb = array();
                                        $navcrumb[''] = '{_message_of_the_day}';
                                        $motd = stripslashes($motd);
                                        $motd = $ilance->bbcode->bbcode_to_html($motd);
					$pprint_array = array('motd','landing');
                                        $ilance->template->fetch('main', 'main_motd.html');
					$ilance->template->parse_if_blocks('main');
                                        $ilance->template->pprint('main', $pprint_array);
                                        exit();
                                }
                                refresh($landing);
                                exit();
			}
			else if ($userinfo['status'] == 'banned')
			{
				($apihook = $ilance->api('login_status_banned')) ? eval($apihook) : false;
				
				print_notice('{_you_have_been_banned_from_the_marketplace}', '{_you_have_been_banned_from_the_marketplace}' . '.<br />' . '{_if_you_would_like_to_dispute_this_ban_contact_our_staff}', $ilpage['main'] . '?cmd=contact&amp;subcmd=banned', '{_contact_customer_support}');
				exit();
			}
			else
			{
				refresh(HTTPS_SERVER . $ilpage['login'] . '?error=' . $userinfo['status']);	
				exit();	
			}
		}
		// #### incorrect username and/or password entered by the user
		else
		{
			$ilance->GPC['username'] = isset($ilance->GPC['username']) ? $ilance->GPC['username'] : '';
			$ilance->GPC['password'] = isset($ilance->GPC['password']) ? $ilance->GPC['password'] : '';
			
			$ilance->db->query("
				INSERT INTO " . DB_PREFIX . "failed_logins
				(id, attempted_username, attempted_password, referrer_page, ip_address, datetime_failed)
				VALUES(
				NULL,
				'" . $ilance->db->escape_string($ilance->GPC['username']) . "',
				'" . $ilance->db->escape_string($ilance->GPC['password']) . "',
				'" . $ilance->db->escape_string(REFERRER) . "',
				'" . $ilance->db->escape_string(IPADDRESS) . "',
				'" . DATETIME24H . "')
			");
			if ($ilconfig['globalsecurity_emailonfailedlogins'])
			{
				// count number of login attempts
				$sql = $ilance->db->query("
					SELECT COUNT(*) AS num_attempts
					FROM " . DB_PREFIX . "failed_logins
					WHERE attempted_username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
				");
				$res = $ilance->db->fetch_array($sql);
				if ($res['num_attempts'] >= $ilconfig['globalsecurity_numfailedloginattempts'])
				{
					// to be added: check if this user is actually a user, if so
					// send them an email also informing them of a suspicious hack attempt
				
					
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('failed_login_attempt_admin');		
					$ilance->email->set(array(
						'{{remote_addr}}' =>IPADDRESS,
						'{{num_attempts}}' => $res['num_attempts'],
						'{{date_time}}' => DATETIME24H,
						'{{referrer}}' => REFERRER,
						'{{username}}' => $ilance->GPC['username'],
						'{{password}}' => $ilance->GPC['password'],
					));
					$ilance->email->send();
					
					($apihook = $ilance->api('login_failed_attempts_exceeded')) ? eval($apihook) : false;
				}
				
				$landing = '';
				if (!empty($redirect))
				{
				    $landing = '&redirect=' . urlencode($redirect);
				}
				if ($ilance->GPC['login_process'] == '2')
				{
					refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?error=1' . $landing);	
					exit();		
				}
				else
				{
					refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1' . $landing);	
					exit();	
				}				
			}
			else
			{
				$landing = '';
				if (!empty($redirect))
				{
					$landing = '&redirect=' . urlencode($redirect);
				}          
				if ($ilance->GPC['login_process'] == '2')
				{
					refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?error=1' . $landing);	
					exit();		
				}
				else
				{
					refresh(HTTPS_SERVER . $ilpage['login'] . '?error=1' . $landing);	
					exit();	
				}
			}
		}
	}
	else
	{
		$landing = '';
		if (!empty($redirect))
		{
			$landing = '&redirect=' . urlencode($redirect);
		}
		refresh(HTTPS_SERVER . $ilpage['login'] . '?error=' . (($username_banned) ? 'specialcharacters' : '1') . $landing);
		exit();	
	}
}
// #### MEMBER LOGOUT REQUEST ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_logout')
{
	$area_title = '{_sign_out}<div class="smaller">{_logging_out_of_marketplace}</div>';
	$page_title = '{_logging_out_of_marketplace}';

	($apihook = $ilance->api('logout_process_start')) ? eval($apihook) : false;

	set_cookie('lastvisit', DATETIME24H);
	set_cookie('lastactivity', DATETIME24H);
	set_cookie('userid', '', false);
	set_cookie('password', '', false);
	set_cookie('inlineproduct', '', false);
	set_cookie('inlineservice', '', false);
	set_cookie('inlineprovider', '', false);
	set_cookie('collapse', '', false);
	set_cookie('hideacpnag', '', false);
	
	// for addons like kb, this hook should be used to clear any cookies created, etc
	($apihook = $ilance->api('logout_process_end')) ? eval($apihook) : false;

	// destroy entire member session
	session_unset();
	$ilance->sessions->session_destroy(session_id());
	session_destroy();
	if (isset($ilance->GPC['nc']) AND $ilance->GPC['nc'] > 0)
	{
		refresh(HTTP_SERVER . '?' . intval($ilance->GPC['nc']));
		exit();
	}
	refresh(HTTPS_SERVER . $ilpage['login']);
	exit();
}
// #### RENEW PASSWORD #########################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_pw-renew')
{
	$area_title = '{_sign_in}<div class="smaller">{_request_account_password}</div>';
	$page_title = SITE_NAME . ' - {_request_account_password}';
	$headinclude .= '
<script type="text/javascript">
<!--
function validate_input(f)
{
        haveerrors = 0;
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
	$pprint_array = array('userid','input_style');
	
	$ilance->template->fetch('main', 'login_password_renewal.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### USER REQUESTING PASSWORD ###############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-pw-request' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
{
        $area_title = '{_sign_in}<div class="smaller">{_change_account_password_verification}</div>';
	$page_title = SITE_NAME . ' - {_change_account_password_verification}';
                
	$ilance->GPC['email'] = trim($ilance->GPC['email']);
	if ($ilance->common->is_email_valid($ilance->GPC['email']))
	{
		$sql = $ilance->db->query("
			SELECT username, secretquestion, secretanswer
			FROM " . DB_PREFIX . "users
			WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$email = $ilance->GPC['email'];
			$username = stripslashes($res['username']);
				
			if ($res['secretquestion'] != '' AND $res['secretanswer'] != '')
			{
				$headinclude .= '
<script type="text/javascript">
<!--
function validate_secret_answer(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
				$secret_question = stripslashes($res['secretquestion']);
				$pprint_array = array('email','username','secret_question','userid','input_style');
				
				$ilance->template->fetch('main', 'login_password_change.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
			
			// #### secret question and answer blank!! #####################
			else
			{
				print_notice('{_could_not_find_your_secret_question_and_answer}', '{_we_are_sorry_but_after_recent_site_security_upgrades}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', '{_retry}');
				exit();
			}
		}
	}

	$area_title = '{_request_account_password_denied}';
	$page_title = SITE_NAME . ' - {_request_account_password_denied}';

	print_notice('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', '{_retry}');
	exit();
	
}
// #### USER CHANGING PASSWORD #################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'password-change' AND isset($ilance->GPC['secretanswer']) AND isset($ilance->GPC['email']) AND isset($ilance->GPC['username']))
{
	$secretanswer = strip_tags($ilance->GPC['secretanswer']);
	$secretanswermd5 = md5($secretanswer);
	$email = strip_tags($ilance->GPC['email']);
	$username = strip_tags($ilance->GPC['username']);
        
	$sql = $ilance->db->query("
		SELECT user_id, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($email) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);                
		$userid = $res['user_id'];
		$secretanswerdb = stripslashes($res['secretanswer']);
	}
	else
	{
		$area_title = '{_sign_in}<div class="smaller">{_request_account_password_denied}</div>';
                $page_title = SITE_NAME . ' - {_request_account_password_denied}';
                print_notice('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_pw-renew', '{_retry}');
                exit();
	}
	if ($secretanswermd5 == $secretanswerdb)
	{
                $salt = construct_password_salt(5);
		$newpassword = construct_password(8);
		$newpasswordmd5 = md5(md5($newpassword) . $salt);
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET password = '" . $ilance->db->escape_string($newpasswordmd5) . "',
			salt = '" . $ilance->db->escape_string($salt) . "',
			password_lastchanged = '" . DATETIME24H . "'
			WHERE user_id = '" . intval($userid) . "'
		");
		
                $ilance->email->mail = $email;
                $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
		$ilance->email->get('password_renewed');		
		$ilance->email->set(array(
			'{{username}}' => $username,
			'{{password}}' => $newpassword,
		));
		$ilance->email->send();
		$area_title = '{_sign_in}<div class="smaller">{_account_password_renewal_success}</div>';
		$page_title = SITE_NAME . ' - {_account_password_renewal_success}';
		print_notice('{_your_account_password_was_changed}', '{_you_have_successfully_renewed_the_password_for_your_online_account}', HTTPS_SERVER . $ilpage['login'], '{_login_to_your_account}');
		exit();
	}
	else
	{
		$sql = $ilance->db->query("
			SELECT email
			FROM " . DB_PREFIX . "users
			WHERE username = '" . $ilance->db->escape_string($username) . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			
			$ilance->email->mail = $res['email'];
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('password_recovery_denied');		
			$ilance->email->set(array(
				'{{username}}' => $username,
				'{{ipaddress}}' =>IPADDRESS,
				'{{agent}}' => USERAGENT
			));
			$ilance->email->send();
			
			$area_title = '{_sign_in}<div class="smaller">{_request_account_password_denied}</div>';
			$page_title = SITE_NAME . ' - {_request_account_password_denied}';
			print_notice('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . $ilpage['login'], '{_sign_in}');
			exit();
		}
		else
		{
			$area_title = '{_sign_in}<div class="smaller">{_request_account_password_denied}</div>';
			$page_title = SITE_NAME . ' - {_request_account_password_denied}';
			print_notice('{_request_account_password_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_password_renewal}', HTTPS_SERVER . $ilpage['login'], '{_sign_in}');
			exit();
		}
	}
}
// #### CHANGE IP ADDRESS PREFERENCE ###########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_ip-reset')
{
	$area_title = '{_sign_in}<div class="smaller">{_change_ip_preference}</div>';
	$page_title = SITE_NAME . ' - {_change_ip_preference}';

	// javascript header includes
	$headinclude .= '
<script type="text/javascript">
<!--
function validate_input(f)
{
        haveerrors = 0;
        (f.email.value.search("@") == -1 || f.email.value.search("[.*]") == -1) ? showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("emailerror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
	$pprint_array = array('userid','input_style');
	
	$ilance->template->fetch('main', 'login_ipaddress_renewal.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### USER REQUESTING IP PREFERENCE CHANGE ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-ip-change' AND isset($ilance->GPC['email']) AND !empty($ilance->GPC['email']))
{
        $area_title = '{_sign_in}<div class="smaller">{_change_ip_preference}</div>';
	$page_title = SITE_NAME . ' - {_change_ip_preference}';
                
	$sql = $ilance->db->query("
		SELECT username, secretquestion
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql);
		$email = $ilance->GPC['email'];
		$secret_question = stripslashes($res['secretquestion']);
		$username = stripslashes($res['username']);
		
		$headinclude .= '
<script type="text/javascript">
<!--
function validate_secret_answer(f)
{
        haveerrors = 0;
        (f.secretanswer.value.length < 1) ? showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("secretanswererror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
		$pprint_array = array('email','username','secret_question','userid','input_style');
		
		$ilance->template->fetch('main', 'login_ipaddress_change.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = '{_change_ip_preference_denied}';
                $page_title = SITE_NAME . ' - {_change_ip_preference_denied}';
                print_notice('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', '{_retry}');
                exit();
	}
}
// #### USER CHANGING IP PREFERENCE ############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'ipaddress-change' AND isset($ilance->GPC['secretanswer']) AND isset($ilance->GPC['email']) AND isset($ilance->GPC['username']))
{
	$secretanswer = strip_tags($ilance->GPC['secretanswer']);
	$secretanswermd5 = md5($secretanswer);
	$email = strip_tags($ilance->GPC['email']);
	$username = strip_tags($ilance->GPC['username']);
        
	$sql = $ilance->db->query("
		SELECT user_id, secretanswer
		FROM " . DB_PREFIX . "users
		WHERE email = '" . $ilance->db->escape_string($email) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$userid = $res['user_id'];
		$secretanswerdb = stripslashes($res['secretanswer']);
	}
	else
	{
		$area_title = '{_sign_in}<div class="smaller">{_change_ip_preference_denied}</div>';
                $page_title = SITE_NAME . ' - {_change_ip_preference_denied}';
                print_notice('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', '{_retry}');
                exit();
	}
	if ($secretanswermd5 == $secretanswerdb)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "users
			SET iprestrict = '0'
			WHERE user_id = '" . intval($userid) . "'
		");
		
		$area_title = '{_sign_in}<div class="smaller">{_ip_address_preference_changed}</div>';
		$page_title = SITE_NAME . ' - {_ip_address_preference_changed}';
		print_notice('{_ip_address_preference_changed}', '{_you_successfully_reset_the_ip_address_preference_for_your_account}', HTTPS_SERVER . $ilpage['login'], '{_login_to_your_account}');
		exit();
	}
	else
	{
		$area_title = '{_sign_in}<div class="smaller">{_change_ip_preference_denied}</div>';
                $page_title = SITE_NAME . ' - {_change_ip_preference_denied}';
                print_notice('{_change_ip_preference_denied}', '{_were_sorry_we_were_unable_to_find_the_information_required_to_continue_with_ip_address_preference_changes}', HTTPS_SERVER . $ilpage['login'] . '?cmd=_ip-reset', '{_retry}');
                exit();
	}
}
// #### LOGIN AREA MENU ########################################################
else
{
	// use slim header and footer so user can focus on signing in without many links
	$show['slimheader'] = true;
	$show['slimfooter'] = true;
 	$area_title = '{_sign_in}<div class="smaller">{_login_area_menu}</div>';
	$page_title = SITE_NAME . ' - {_login_area_menu}';
	$onload .= (empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? 'document.login.username.focus();' : 'document.login.password.focus();';
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		refresh($ilpage['main']);
		exit();
	}
	else
	{
		$rid = (!empty($_COOKIE[COOKIE_PREFIX . 'rid'])) ? trim($_COOKIE[COOKIE_PREFIX . 'rid']) : '';
		$user_cookie = (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']) : '';
		$lastvisit = (!empty($_COOKIE[COOKIE_PREFIX . 'lastvisit'])) ? print_date($_COOKIE[COOKIE_PREFIX . 'lastvisit'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : '{_never}';
		$username = (isset($ilance->GPC['username'])) ? handle_input_keywords($ilance->GPC['username']) : $user_cookie;
		$password = isset($ilance->GPC['password']) ? handle_input_keywords($ilance->GPC['password']) : '';
		$redirect_page = isset($ilance->GPC['redirect']) ? '?redirect='.urlencode($ilance->GPC['redirect']) : '';
		$pprint_array = array('username','password','redirect_page','lastvisit','remember_checked','redirect','referer','user_cookie');
		$ilance->template->fetch('main', 'login.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>