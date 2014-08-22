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
		'tabfx'
	),
	'footer' => array(
		'md5',
		'tooltip',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'admin');

// #### require backend ########################################################
require_once('./../functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[login]" => $ilcrumbs["$ilpage[login]"]);
$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['login'], $ilpage['login'], $_SESSION['ilancedata']['user']['slng']);
$cmd = '';

// #### ADMIN REQUESTS LOGOUT ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_logout')
{
	$area_title = '{_logging_out_of_marketplace}';
	$page_title = '{_logging_out_of_marketplace}';

	// keep last visit and last activity cookie .-)
	set_cookie('lastvisit', DATETIME24H);
	set_cookie('lastactivity', DATETIME24H);
	
	// expire member specific cookies so the marketplace doesn't re-login user in automatically
        // leave username cookie alone so the marketplace can greet the member by username (login, breadcrumb, etc)
	set_cookie('userid', '', 0, false);
	set_cookie('password', '', 0, false);
	
	// expire any checkboxes selected in this session
	set_cookie('inlineproduct', '', 0, false);
	set_cookie('inlineservice', '', 0, false);
	set_cookie('inlineprovider', '', 0, false);

	// destroy member specific sessions
	$ilance->sessions->session_destroy(session_id());
	session_destroy();

	refresh($ilpage['login'], HTTPS_SERVER_ADMIN . $ilpage['login']);
	exit();
}

// #### ADMIN RENEW PASSWORD ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_renew-password' AND isset($ilance->GPC['license']) AND isset($ilance->GPC['username']))
{
	$area_title = '{_attempting_to_reset_admin_password}';
	$page_title = SITE_NAME . ' - ' . '{_attempting_to_reset_admin_password}';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username']))
	{
		$admin_cookie = $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
	}
	if (defined('LICENSEKEY') AND isset($ilance->GPC['license']) AND $ilance->GPC['license'] == LICENSEKEY)
	{
		$sql = $ilance->db->query("
                        SELECT user_id, email, username
                        FROM " . DB_PREFIX . "users
                        WHERE username = '" . $ilance->db->escape_string($ilance->GPC['username']) . "'
				AND email = '" . $ilance->db->escape_string($ilance->GPC['email']) . "'
                                AND status = 'active'
                                AND isadmin = '1'
                        LIMIT 1
                ");
		if ($ilance->db->num_rows($sql) > 0)
		{
                        $show['error_login'] = false;
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$salt = construct_password_salt($length = 5);
			$newp = construct_password(8);
			$pass = md5(md5($newp) . $salt);
			$ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET password = '" . $ilance->db->escape_string($pass) . "',
				salt = '" . $ilance->db->escape_string($salt) . "',
				password_lastchanged = '" . DATETIME24H . "'
                                WHERE user_id = '" . intval($res['user_id']) . "'
                                LIMIT 1
                        ");
			$subject = "New password generated for " . SITE_NAME . " AdminCP";
			$message = "Administrator,\n\nYou or someone else has requested to renew the administration password for your account on " . DATETIME24H . ".  Please find the following information below to log-in:
			
Username: " . stripslashes($res['username']) . "
New Password: " . $newp . "
------------------------------------------
Changed from IP: " . IPADDRESS . "
Changed from Agent: " . USERAGENT . "

" . SITE_NAME . "
" . HTTP_SERVER;
			$ilance->email->mail = $res['email'];
			$ilance->email->from = SITE_EMAIL;
			$ilance->email->subject = $subject;
			$ilance->email->message = $message;
			$ilance->email->send();
			$show['password_renewed'] = true;
		}
		else
		{
			$show['error_login'] = true;
			$show['password_renewed'] = false;
		}
	}
	else
	{
		$show['error_login'] = true;
		$show['password_renewed'] = false;
	}
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['login'], $ilpage['login'], $_SESSION['ilancedata']['user']['slng']);
        $pprint_array = array('admin_cookie');
	
        ($apihook = $ilance->api('admincp_login_pwrenew_process_end')) ? eval($apihook) : false;
        
	$ilance->template->fetch('main', 'login_pwrenew.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### ADMIN RENEWING PASSWORD ################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_pw-renew')
{
	$area_title = '{_admin_password_renewal_menu}';
	$page_title = SITE_NAME . ' - ' . '{_admin_password_renewal_menu}';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username']))
	{
		$admin_cookie = $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
	}
	$show['error_login'] = $show['password_renewed'] = false;
	if (isset($ilance->GPC['error']) AND $ilance->GPC['error'] == '1')
	{
		$show['error_login'] = true;
	}
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['login'], $ilpage['login'], $_SESSION['ilancedata']['user']['slng']);
        $pprint_array = array('admin_cookie');
	
        ($apihook = $ilance->api('admincp_login_pwrenew_end')) ? eval($apihook) : false;
        
	$ilance->template->fetch('main', 'login_pwrenew.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### ADMIN CP LOGIN MENU ####################################################
else
{
	$area_title = '{_login_area_menu}';
	$page_title = SITE_NAME . ' - ' . '{_login_area_menu}';
	$username = isset($ilance->GPC['username']) ? handle_input_keywords($ilance->GPC['username']) : '';
	$password = isset($ilance->GPC['password']) ? handle_input_keywords($ilance->GPC['password']) : '';
        // set default redirection to dashboard area
	$redirect = HTTPS_SERVER_ADMIN . $ilpage['dashboard'];
        if (!empty($ilance->GPC['redirect']))
	{
                $redirect = trim($ilance->GPC['redirect']);
	}
        // set the input field focus to the password if we know of the user from his/her cookie
        $onload .= (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? 'document.login.password.focus();' : 'document.login.username.focus();';
        // prepopulate the admin username in the input box to avoid carpal tunnel
        $admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username']) AND empty($ilance->GPC['username']))
	{
		$admin_cookie = $ilance->crypt->three_layer_decrypt($_COOKIE[COOKIE_PREFIX . 'username'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
		$username = $admin_cookie;
	}
        // if we've already logged in and have a valid admin session key let's just avoid the login altogether
        if (!empty($_SESSION['ilancedata']['user']['userid']) AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
        {
                header("Location: " . HTTPS_SERVER_ADMIN . $ilpage['dashboard']);
                exit();
        }
        $pprint_array = array('username','password','redirect');
        
        ($apihook = $ilance->api('admincp_login_end')) ? eval($apihook) : false;
        
	$ilance->template->fetch('main', 'login.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>