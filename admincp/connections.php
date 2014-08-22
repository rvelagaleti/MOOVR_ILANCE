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
		'tabfx'
	),
	'footer' => array(
		'tooltip',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'admin');
// #### require backend ########################################################
require_once('./../functions/config.php');
require_once(DIR_CORE . 'functions_connections.php');
if (file_exists(DIR_CORE . 'functions_geoip.php') AND file_exists(DIR_CORE . 'functions_geoip_city.dat') AND file_exists(DIR_CORE . 'functions_geoip_country.dat'))
{
	if (!function_exists('geoip_open'))
	{
		require_once(DIR_CORE . 'functions_geoip.php');
	}
	$geoip = geoip_open(DIR_CORE . 'functions_geoip_city.dat', GEOIP_STANDARD);
	$show['geoip'] = true;
}
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['connections'] => $ilcrumbs[$ilpage['connections']]);
if(($v3nav = $ilance->cache->fetch("print_admincp_nav_connections")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['connections']);
	$ilance->cache->store("print_admincp_nav_connections", $v3nav);
}
$area_title = '{_viewing_connection_activity}';
$page_title = SITE_NAME . ' - {_connection_activity}';
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0 OR $_SESSION['ilancedata']['user']['isadmin'] != '1')
{
	refresh($ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI), HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode(SCRIPT_URI));
	exit();	
}
// #### KICK SESSION ###########################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_kick-session' AND !empty($ilance->GPC['sid']))
{
	foreach ($ilance->GPC['sid'] AS $ipaddress)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "sessions
			WHERE ipaddress = '" . $ilance->db->escape_string($ipaddress) . "'
		", 0, null, __FILE__, __LINE__);
	}
	refresh(HTTPS_SERVER_ADMIN . $ilpage['connections']);
	exit();
}

($apihook = $ilance->api('admincp_connection_management')) ? eval($apihook) : false;

$show['nomembers'] = $show['noguests'] = $show['noadmins'] = $show['nocrawlers'] = false;
$row_count = 0;
$sqlguest = $ilance->db->query("
	SELECT sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, ipaddress, url, title, firstclick, lastclick, browser, token, sesskeyapi, siteid, COUNT(ipaddress) AS connects
	FROM " . DB_PREFIX . "sessions
	WHERE userid = '0' AND isrobot = '0'
	GROUP BY token
	ORDER BY lastclick DESC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqlguest) > 0)
{
	while ($row = $ilance->db->fetch_array($sqlguest, DB_ASSOC))
	{
		$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['ipaddress'] . '" />';
		$row['username'] = '{_guest}';
		$row['location_title'] = stripslashes($row['title']);
		$row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_url_spy}</strong></div><div class=\\\'smaller gray\\\'>' . (!empty($row['url']) ? addslashes(print_string_wrap($row['url'], 75)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
		$row['ip_address'] = $row['ipaddress'];
		$row['os'] = fetch_os_name($row['agent'], true);
		$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_browser}</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>{_browser_agent}</strong></div><div>' . stripslashes(handle_input_keywords($row['agent'])) . '</div><div style=padding-top:4px><strong>{_session_id}</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>{_token_hash}</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
		$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
		if (isset($show['geoip']) AND $show['geoip'] == true)
		{
			$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
			$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
			$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
			$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
			$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
			$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
			unset($geo);
		}
		$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
		$row['lastclick'] = sec2text(TIMESTAMPNOW - $row['lastclick']);
		$row['duration'] = sec2text(TIMESTAMPNOW - $row['firstclick']);
		$row['expiresin'] = sec2text($row['expiry'] - TIMESTAMPNOW);
		$row['class']  = ($row_count % 2) ? 'alt1' : 'alt1';
		$guest_connection_results[] = $row;
		$row_count++;
	}
	unset($row);
}
else
{
	$show['noguests'] = true;
}
$guestsonline = $row_count;
unset($sqlguest);

$row_count = 0;
$sqlmember = $ilance->db->query("
	SELECT sess.*, COUNT(sess.ipaddress) AS connects
	FROM " . DB_PREFIX . "users AS user,
	" . DB_PREFIX . "sessions AS sess
	WHERE sess.userid = user.user_id
		AND sess.isuser = '1'
		AND sess.userid > 0
	GROUP BY sess.token
	ORDER BY sess.lastclick DESC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqlmember) > 0)
{
	while ($row = $ilance->db->fetch_array($sqlmember, DB_ASSOC))
	{
		$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['ipaddress'] . '" />';
		$row['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['userid'] . '">' . fetch_user('username', $row['userid']) . '</a>';
		$row['location_title'] = stripslashes($row['title']);
		$row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_url_spy}</strong></div><div class=\\\'smaller gray\\\'>' . (!empty($row['url']) ? addslashes(print_string_wrap($row['url'], 75)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
		$row['ip_address'] = $row['ipaddress'];
		$row['os'] = fetch_os_name($row['agent'], true);
		$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_browser}</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>{_browser_agent}</strong></div><div>' . stripslashes(handle_input_keywords($row['agent'])) . '</div><div style=padding-top:4px><strong>{_session_id}</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>{_token_hash}</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
		$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
		if (isset($show['geoip']) AND $show['geoip'] == true)
		{
			$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
			$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
			$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
			$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
			$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
			$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
			unset($geo);
		}
		$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
		$row['lastclick'] = sec2text(TIMESTAMPNOW - $row['lastclick']);
		$row['duration'] = sec2text(TIMESTAMPNOW - $row['firstclick']);
		$row['expiresin'] = sec2text($row['expiry'] - TIMESTAMPNOW);
		$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
		$member_connection_results[] = $row;
		$row_count++;
	}
	unset($row);
}
else
{
	$show['nomembers'] = true;
}
$membersonline = $row_count;

$row_count = 0;
$sqladmin = $ilance->db->query("
	SELECT sess.*, COUNT(sess.ipaddress) AS connects
	FROM " . DB_PREFIX . "users AS user,
	" . DB_PREFIX . "sessions AS sess
	WHERE sess.userid = user.user_id
		AND sess.userid > 0
		AND sess.isadmin = '1'
	GROUP BY sess.token
	ORDER BY sess.lastclick DESC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqladmin) > 0)
{
	while ($row = $ilance->db->fetch_array($sqladmin, DB_ASSOC))
	{
		$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['ipaddress'] . '" />';
		$row['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['userid'] . '">' . fetch_user('username', $row['userid']) . '</a>';
		$row['location_title'] = stripslashes($row['title']);
		$row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_url_spy}</strong></div><div class=\\\'smaller gray\\\'>' . (!empty($row['url']) ? addslashes(print_string_wrap($row['url'], 75)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
		$row['os'] = fetch_os_name($row['agent'], true);
		$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_browser}</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>{_browser_agent}</strong></div><div>' . stripslashes(handle_input_keywords($row['agent'])) . '</div><div style=padding-top:4px><strong>{_session_id}</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>{_token_hash}</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
		$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
		if (isset($show['geoip']) AND $show['geoip'] == true)
		{
			$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
			$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
			$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
			$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
			$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
			$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
			unset($geo);
		}
		$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
		$row['ip_address'] = $row['ipaddress'];
		$row['lastclick'] = sec2text(TIMESTAMPNOW - $row['lastclick']);
		$row['duration'] = sec2text(TIMESTAMPNOW - $row['firstclick']);
		$row['expiresin'] = sec2text($row['expiry'] - TIMESTAMPNOW);
		$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
		$admin_connection_results[] = $row;
		$row_count++;
	}
	unset($row);
}
else
{
	$show['noadmins'] = true;
}
$staffonline = $row_count;
unset($sqlmember);
$row_count = 0;
$sqlcrawlers = $ilance->db->query("
	SELECT sesskey, expiry, value, userid, isuser, isadmin, isrobot, iserror, languageid, styleid, agent, ipaddress, url, title, firstclick, lastclick, browser, token, sesskeyapi, siteid, COUNT(ipaddress) AS connects
	FROM " . DB_PREFIX . "sessions
	WHERE userid = '0'
		AND isrobot = '1'
	GROUP BY token
	ORDER BY lastclick DESC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqlcrawlers) > 0)
{
	while ($row = $ilance->db->fetch_array($sqlcrawlers, DB_ASSOC))
	{
		$row['checkbox'] = '<input type="checkbox" name="sid[]" value="' . $row['ipaddress'] . '" />';
		$row['username'] = fetch_search_crawler_title($row['agent']);
		$row['location_title'] = !empty($row['title']) ? stripslashes($row['title']) : '{_unknown}';
		$row['location'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_url_spy}</strong></div><div class=\\\'smaller gray\\\'>' . (!empty($row['url']) ? addslashes(print_string_wrap($row['url'], 75)) : HTTP_SERVER) . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $row['location_title'] . '</a>';
		$row['browser'] = '<a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>{_browser}</strong></div><div>' . $ilance->common->fetch_browser_name(0, $row['browser']) . '</div><div style=padding-top:4px><strong>{_browser_agent}</strong></div><div>' . stripslashes(handle_input_keywords($row['agent'])) . '</div><div style=padding-top:4px><strong>{_session_id}</strong></div><div>' . $row['sesskey'] . '</div><div style=padding-top:4px><strong>{_token_hash}</strong> <em>md5(agent:ip:ip_alt)</em></div><div>' . $row['token'] . '</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()">' . $ilance->common->fetch_browser_name(1, $row['browser']) . '</a>';
		$row['geoipcity'] = $row['geoipcountry'] = $row['geoipstate'] = $row['geoipzip'] = $row['countrycode'] = '';
		if (isset($show['geoip']) AND $show['geoip'] == true)
		{
			$geo = geoip_record_by_addr($geoip, $row['ipaddress']);
			$row['geoipcity'] = (!empty($geo->city) ? $geo->city . ', ' : '');
			$row['geoipcountry'] = (!empty($geo->country_name) ? $geo->country_name : '');
			$row['geoipstate'] = (!empty($geo->region) ? (!empty($GEOIP_REGION_NAME[$geo->country_code][$geo->region]) ? $GEOIP_REGION_NAME[$geo->country_code][$geo->region] . ', ' : '') : '');
			$row['geoipzip'] = (!empty($geo->postal_code) ? $geo->postal_code . ', ' : '');
			$row['countrycode'] = (!empty($geo->country_code) ? $geo->country_code : '');
			unset($geo);
		}
		$row['country'] = (!empty($row['countrycode']) ? '<span style="float:left;padding-right:5px;margin-top:1px" title="' . $row['geoipcity'] . $row['geoipstate'] . $row['geoipcountry'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'flags/' . strtolower($row['countrycode']) . '.png" border="0" alt="" id="" /></span>' : '');
		$row['ip_address'] = $row['ipaddress'];
		$row['lastclick'] = sec2text(TIMESTAMPNOW - $row['lastclick']);
		$row['duration'] = sec2text(TIMESTAMPNOW - $row['firstclick']);
		$row['expiresin'] = sec2text($row['expiry'] - TIMESTAMPNOW);
		$row['class'] = ($row_count % 2) ? 'alt1' : 'alt1';                                
		$crawler_connection_results[] = $row;
		$row_count++;
	}
	unset($row);
}
else
{
	$show['nocrawlers'] = true;
}
$robotsonline = $row_count;
unset($sqlcrawlers);
if (isset($show['geoip']) AND $show['geoip'] == true)
{
	geoip_close($geoip);
}
$pprint_array = array('guestsonline','membersonline','staffonline','robotsonline','global_connectionsettings');

($apihook = $ilance->api('admincp_connections_end')) ? eval($apihook) : false;
	
$ilance->template->fetch('main', 'connections.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'v3nav', false);
$ilance->template->parse_loop('main', array('guest_connection_results', 'member_connection_results', 'admin_connection_results', 'crawler_connection_results'));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>