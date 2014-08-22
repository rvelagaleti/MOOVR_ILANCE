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

/**
* Global functions to perform the majority of operations within the Front and Admin Control Panel in iLance.
*
* @package      iLance\Global\AdminCP
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print an action was successful used mainly within the AdminCP
* 
* @param        string      success message to display
* @param        string      redirect to url location
*
* @return	string      Returns the HTML representation of the action success template
*/
function print_action_success($notice = '', $admurl = '')
{
	global $ilance, $login_include_admin, $iltemplate, $ilanceversion, $phrase, $v3nav, $page_title, $area_title, $ilconfig, $ilpage, $show;
        $notice_temp = $notice; $admurl_temp = $admurl;
        global $notice, $admurl;
        $userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        $subcmd = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
        $script = mb_strcut($_SERVER['PHP_SELF'], mb_strripos($_SERVER['PHP_SELF'], '/') + 1, mb_strlen($_SERVER['PHP_SELF']));
        $details = '';
        foreach ($ilance->GPC AS $key => $value)
        {
        	if ($key != 'cmd' AND $key != 'subcmd')
        	{
        		if (is_array($value))
	        	{	
	        		$details .= ', ' . $key . '=(';
	        		foreach ($value AS $key2 => $value2)
	        		{
					if (is_array($value2))
					{
						$details .= ', ' . $key2 . '=(';
						foreach ($value2 AS $key3 => $value3)
						{
							if (is_array($value3))
							{
								$details .= ', ' . $key3 . '=(';
								foreach ($value3 AS $key4 => $value4)
								{
									if (is_array($value4))
									{
										$details .= ', ' . $key4 . '=(';
										foreach ($value4 AS $key5 => $value5)
										{
											$details .= ', ' . $key5 . '=' . $value5;
										}
									}
									else
									{
										$details .= ', ' . $key4 . '=' . $value4;
									}
									
								}
							}
							else
							{
								$details .= ', ' . $key3 . '=' . $value3;
							}
						}
					}
					else
					{
						$details .= ', ' . $key2 . '=' . $value2;
					}
	        		}
	        		$details .= ')';
	        	}	
        		else 
        		{
        			$details .= ', ' . $key . '=' . $value;
        		}
        	}
        }
        if (!empty($cmd))
        {
        	log_event($userid, $script, $cmd, $subcmd, 'success' . $details);
        }
        $area_title = 'AdminCP - {_administrative_action_complete} {_for} ' . $_SESSION['ilancedata']['user']['username'];
        $page_title = SITE_NAME . ' - AdminCP - {_administrative_action_complete}';
        
        ($apihook = $ilance->api('print_action_success')) ? eval($apihook) : false;
        
        $admurl = $admurl_temp; $notice = $notice_temp;
	$ilance->template->jsinclude = array('header' => array('functions','tabfx'), 'footer' => array('tooltip','cron'));
        $pprint_array = array('notice','admurl');
        
        ($apihook = $ilance->api('admincp_access_success_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'action_success.html', 1);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'v3nav', false);
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

/**
* Function to print an action continue the process or cancel the process  used mainly within the AdminCP
*
* @param        string      success message to display
* @param        string      redirect to url location
*
* @return	string      Returns the HTML representation of the action success template
*/
function continue_action_success($notice = '', $confirmurl = '', $cancelurl = '' )
{
	global $ilance, $login_include_admin, $iltemplate, $ilanceversion, $phrase, $v3nav, $page_title, $area_title, $ilconfig, $ilpage, $show;
        $notice_temp = $notice; $confirmurl_temp = $confirmurl; $cancelurl_temp = $cancelurl;
        global $notice, $confirmurl, $cancelurl;
	$confirmurl = $confirmurl_temp; $notice = $notice_temp; $cancelurl = $cancelurl_temp;
        $userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;        
        $content= '';
        $area_title = 'AdminCP - ' . '{_administrative_action_process}' . ' ' . '{_for}' . ' ' . $_SESSION['ilancedata']['user']['username'];
        $page_title = SITE_NAME . ' - AdminCP - ' . '{_administrative_action_process}';    
	$ilance->template->jsinclude = array('header' => array('functions','tabfx'), 'footer' => array('tooltip','cron'));
        $pprint_array = array('notice','admurl','confirmurl','cancelurl','notice','content');
        $ilance->template->fetch('main', 'action_process.html', 1);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'v3nav', false);
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

/**
* Function to print an action failed used mainly within the AdminCP
*
* @param        string      error message to display
* @param        string      redirect to url location
*
* @return	string      Returns the HTML representation of the action failed template
*/
function print_action_failed($error = '', $admurl = '')
{
        global $ilance, $login_include_admin, $ilanceversion, $phrase, $v3nav, $page_title, $area_title, $ilconfig, $ilpage;
        $admurl_temp = $admurl; $error_temp = $error;
        global $admurl, $error, $ilance;
        $admurl = $admurl_temp; $error = $error_temp;
        $userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        $subcmd = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
        $script = mb_strcut($_SERVER['PHP_SELF'], mb_strripos($_SERVER['PHP_SELF'] + 1, '/'), mb_strlen($_SERVER['PHP_SELF']));
        $details = '';
        foreach ($ilance->GPC AS $key => $value)
        {
        	if ($key != 'cmd' AND $key != 'subcmd')
        	{
        		if (is_array($value))
	        	{	
	        		$details .= ', ' . $key . '=(';
	        		foreach ($value AS $key2 => $value2)
	        		{
					if (is_array($value2))
					{
						$details .= ', ' . $key2 . '=(';
						foreach ($value2 AS $key3 => $value3)
						{
							if (is_array($value3))
							{
								$details .= ', ' . $key3 . '=(';
								foreach ($value3 AS $key4 => $value4)
								{
									if (is_array($value4))
									{
										$details .= ', ' . $key4 . '=(';
										foreach ($value4 AS $key5 => $value5)
										{
											$details .= ', ' . $key5 . '=' . $value5;
										}
									}
									else
									{
										$details .= ', ' . $key4 . '=' . $value4;
									}
									
								}
							}
							else
							{
								$details .= ', ' . $key3 . '=' . $value3;
							}
						}
					}
					else
					{
						$details .= ', ' . $key2 . '=' . $value2;
					}
	        		}
	        		$details .= ')';
	        	}	
        		else 
        		{
        			$details .= ', ' . $key . '=' . $value;
        		}
        	}
        }
        if (!empty($cmd))
        {
        	log_event($userid, $script, $cmd, $subcmd, 'failed' . $details);
        }
        $pprint_array = array('error','admurl');
	$ilance->template->jsinclude = array('header' => array('functions','tabfx'), 'footer' => array('tooltip','cron'));
	
        ($apihook = $ilance->api('admincp_action_failed_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'action_failed.html', 1);
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_loop('main', 'v3nav', false);
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

/**
* Function to fetch sites/users limit
*
* @return      string        Returns formatted number (3,201) or string (Unlimited)
*/
function usersitelimits()
{
	$userlimit = USER_LIMIT;
	$sitelimit = SITE_LIMIT;
	$ul = 'WypddXNlcmxpbWl0Wypd';
	$sl = 'Wypdc2l0ZWxpbWl0Wypd';
	if ($userlimit == base64_decode($ul))
	{
		$userlimit = 5000;
	}
	if ($sitelimit == base64_decode($sl))
	{
		$sitelimit = 1;
	}
	if ($userlimit == -1 OR $userlimit == '-1')
	{
		$userlimit = '{_unlimited}';
	}
	else
	{
		$userlimit = number_format($userlimit);
	}
	if ($sitelimit == -1 OR $sitelimit == '-1')
	{
		$sitelimit = '{_unlimited}';
	}
	else
	{
		$sitelimit = number_format($sitelimit);
	}
	return array('userlimit' => $userlimit, 'sitelimit' => $sitelimit);
}

/**
* Function to fetch the latest version of ILance
*
* @return      string        HTML representation of the latest version of ILance
*/
function print_version()
{
	global $ilconfig, $phrase;
	return '{_version}: <strong>' . $ilconfig['current_version'] . '</strong>&nbsp;&nbsp;|&nbsp;&nbsp;SQL: <strong>' . $ilconfig['current_sql_version'] . '</strong>';
}

/**
* Function to calculate the sum of the total users logged into the marketplace
*
* @param       integer        user id
*
* @return      string         Returns total members online count in phrase format (ie: 3 members online)
*/
function members_online()
{
        global $ilance, $phrase;
        $sql = $ilance->db->query("
                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "token
                FROM " . DB_PREFIX . "sessions
                GROUP BY token
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql) > 0)
        {
                if ($ilance->db->num_rows($sql) == 1)
                {
                        return '<span id="usersonlinecount">' . (int)$ilance->db->num_rows($sql) . ' {_member_online}</span>';
                }
                else
                {
                        return '<span id="usersonlinecount">' . (int)$ilance->db->num_rows($sql) . ' {_members_online}</span>';
                }
        }
        return '<span id="usersonlinecount">{_one_member_online}</span>';
}


/**
* Function to display the login information bar for admins
*
* @param       string        text
*
* @return      string
*/
function admin_login_include()
{
	global $ilance, $ilpage, $ilconfig, $phrase;
	$hour = date('H');
	$ampm = date('A');
	if ($hour < 12 AND $ampm == 'AM')
	{
		$greeting = '{_good_morning} ';
	}
	else if ($hour < 12 AND $ampm == 'PM')
	{
		$greeting = '{_good_evening} ';
	}
	else if ($hour < 18 AND $ampm == 'AM')
	{
		$greeting = '{_good_morning} ';
	}
	else if ($hour < 18 AND $ampm == 'PM')
	{
		$greeting = '{_good_afternoon} ';
	}
	else
	{
		$greeting = '{_good_evening} ';
	}
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
		if (!empty($_SESSION['ilancedata']['user']['username']))
		{
			$greetuser = $_SESSION['ilancedata']['user']['username'];
		}
		$membersonline = members_online();
		$login_include = $greeting . " $greetuser - $membersonline. {_you_are_logged_in}, <span class=\"blue\"><a href=\"" . HTTPS_SERVER_ADMIN . $ilpage['login'] . "?cmd=_logout\" target=\"_self\" onclick=\"return log_out();\">{_log_out}</a></span>";
	}
	else
	{
		$login_include = $greeting . " {_guest} - {_you_are_logged_out}.";
	}
	return $login_include;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>