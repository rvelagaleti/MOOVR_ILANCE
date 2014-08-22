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
* @package      iLance\Global
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to print a viewable notice template to the web browser using the regular ILance template parsed with the header and footer
*
* @param       string       header text
* @param       string       body text
* @param       string       return url
* @param       string       return url title
* 
* @return      string       Message with domain phrases blocked
*/
function print_notice($header_text = '', $body_text = '', $return_url = '', $return_name = '', $custom = '')
{
	global $ilance, $phrase, $breadcrumb, $page_title, $area_title, $ilconfig, $ilpage, $show;        
        $header = $header_text; $body = $body_text; $return = $return_url; $returnname = $return_name;
        global $header_text, $body_text, $return_url, $return_name, $show;
        $header_text = $header; $body_text = $body; $return_url = $return; $return_name = $returnname;
        $show['widescreen'] = false;
        $area_title = $header_text;
        $page_title = SITE_NAME . ' - ' . $header_text;
        if (is_array($custom) AND !empty($custom))
        {
                $text = '<div style="padding-top:12px"><strong>{_detailed_permissions_information}</strong></div><div style="padding-top:3px"><span class="gray">{_subscription_permission_required_for_this_resource}</span> <span class="blue"><strong>' . ucwords($custom['text']) . '</strong></span></div><div><em>&quot;' . $custom['description'] . '&quot;</em></div>';
                $body_text .= $text;
        }
	$ilance->template->jsinclude = array('header' => array('functions','ajax','jquery'), 'footer' => array('v4','autocomplete','tooltip','cron'));
        $pprint_array = array('body_text','header_text','return_url','return_name');
        
        ($apihook = $ilance->api('print_notice_end')) ? eval($apihook) : false;
        
        $ilance->template->fetch('main', 'print_notice.html');
        $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
        $ilance->template->parse_if_blocks('main');
        $ilance->template->pprint('main', $pprint_array);
        exit();
}

/**
* Converts an integer into a UTF-8 string
*
* @param        integer	    Integer to be converted into utf8
*
* @return	string
*/
function convert_int2utf8($intval)
{
        $intval = intval($intval);
        switch ($intval)
        {
                // 1 byte, 7 bits
                case 0:
                return chr(0);
            
                case ($intval & 0x7F):
                return chr($intval);
        
                // 2 bytes, 11 bits
                case ($intval & 0x7FF):
                return chr(0xC0 | (($intval >> 6) & 0x1F)) . chr(0x80 | ($intval & 0x3F));
        
                // 3 bytes, 16 bits
                case ($intval & 0xFFFF):
                return chr(0xE0 | (($intval >> 12) & 0x0F)) . chr(0x80 | (($intval >> 6) & 0x3F)) . chr (0x80 | ($intval & 0x3F));
        
                // 4 bytes, 21 bits
                case ($intval & 0x1FFFFF):
                return chr(0xF0 | ($intval >> 18)) . chr(0x80 | (($intval >> 12) & 0x3F)) . chr(0x80 | (($intval >> 6) & 0x3F)) . chr(0x80 | ($intval & 0x3F));
        }
}

/**
* Function to shorten a string of characters using an argument limiter as the amount of characters to reveal
*
* @param	string	     html string
* @param	integer      limiter amount (ie: 50)
*
* @return	string       HTML representation of the shortened string
*/
function shorten($string = '', $limit)
{
        if (mb_strlen($string) > $limit)
        {
                $string = mb_substr($string, 0, $limit);
                if (($pos = mb_strrpos($string, ' ')) !== false)
                {
                        $string = mb_substr($string, 0, $pos);
                }
                return $string . '...';
        }
        return $string;
}

/**
* Function to log an event based on a particular action engaged by a user data mined within the AdminCP > Audit Manager
*
* @param	integer      user id
* @param        string       script
* @param        string       cmd invoked
* @param        string       sub cmd invoked
* @param        string       message of action performed
*
* @return	nothing
*/
function log_event($userid = 0, $script = '', $cmd = '', $subcmd = '', $otherinfo = '')
{
        global $ilance;
        $ilance->db->query("
                INSERT INTO " . DB_PREFIX . "audit
                (logid, user_id, script, cmd, subcmd, otherinfo, datetime, ipaddress)
                VALUES
                (NULL,
                '" . intval($userid) . "',
                '" . $ilance->db->escape_string($script) . "',
                '" . $ilance->db->escape_string($cmd) . "',
                '" . $ilance->db->escape_string($subcmd) . "',
                '" . $ilance->db->escape_string($otherinfo) . "',
                '" . TIMESTAMPNOW . "',
                '" . $ilance->db->escape_string(IPADDRESS) . "')
        ", 0, null, __FILE__, __LINE__);
}

/**
* Function to hard refresh a page and to show a please wait while we direct you to the specified location message
*
* @param        string        url to send user
* @param        string        custom argument (unused)
*
* @return	void
*/
function refresh($url = '', $custom = '')
{
        global $ilance, $ilconfig, $phrase, $ilpage, $headinclude;
        
        ($apihook = $ilance->api('refresh_start')) ? eval($apihook) : false;
        
        $userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;
        $cmd = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
        $subcmd = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
        $script = mb_strcut($_SERVER['PHP_SELF'], mb_strripos($_SERVER['PHP_SELF'],'/') + 1, mb_strlen($_SERVER['PHP_SELF']));
        if (!empty($custom))
        {
                $url = urldecode($custom);
        }
        if ($ilconfig['globalfilters_refresh'] == false)
        {
                header("Location: " . $url);
                exit;
        }
	$ilance->styles->init_head_css();
        $headinclude = str_replace('{js_phrases_content}', '', $headinclude);        
        $jsurl = "\'" . urldecode($url) . "\'; return false";
        $html = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="' . $ilconfig['template_textdirection'] . '" lang="' . $ilconfig['template_languagecode'] . '">
<head>
<title>{_processing_your_request_dot_dot_dot}</title>
<meta http-equiv="Refresh" content="1; URL=' . $url . '">
<meta http-equiv="Content-Type" content="text/html; charset=' . $ilconfig['template_charset'] . '">
' . $headinclude . '
</head>
<body>
<center>
<div>
<div style="width:540px; padding-top:150px">
<form action="' . urldecode($url) . '" method="get" accept-charset="UTF-8">
<div class="block-wrapper">
        <div class="block" align="left">
                <div class="block-top">
                        <div class="block-right">
                                <div class="block-left"></div>
                        </div>
                </div>
                <div class="block-header">{_processing_your_request_dot_dot_dot}</div>
                <div class="block-content" style="padding:0px">
                        <table cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '" border="0" dir="' . $ilconfig['template_textdirection'] . '">
                        <tr>
                            <td>
                                <div style="padding-top:3px">
                                    <blockquote>
                                        
					<p style="font-size:13px"><strong>' . SITE_NAME . ' {_is_processing_your_request}</strong></p>{_if_you_do_not_wish_to_wait_any_longer}, <span class="blue"><a href="' . $url . '" target="_self">{_click_here}</a></span>.
                                        
                                    </blockquote>
                                </div>
                            </td>
                        </tr>
                        </table>
                </div>
                <div class="block-footer">
			<div class="block-right">
				<div class="block-left"></div>
			</div>
                </div>
        </div>
</div>
</form>
</div>
</div>
</center>
</body>
</html>';
        
        ($apihook = $ilance->api('refresh_end')) ? eval($apihook) : false;
        
        $ilance->template->templateregistry['refresh'] = $html;
        echo $ilance->template->parse_template_phrases('refresh');
}

/**
* Function to shorten a string based on a supplied argument length to cut off as well as a custom symbol
* to represent at the end of the string (ie: .....)
*
* @param        string        text
* @param        integer       limiter length
* @param        string        limiter symbol (ie: .....)
*
* @return	string        Returns the formatted text with the ending limiter symbol to represent more text is available
*/
function short_string($text = '', $length, $symbol = ' .....')
{
        $length_text = mb_strlen($text);
        $length_symbol = mb_strlen($symbol);
        if ($length_text <= $length OR $length_text <= $length_symbol OR $length <= $length_symbol)
        {
                return($text);
        }
        else
        {
                if ((mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") > mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 25) && (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 25))
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
                }
                else if (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".") + 25)
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".")) . $symbol);
                }
                else if (mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ") < mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ",") + 25)
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), ".")) . $symbol);
                }
                else
                {
                        return (mb_substr($text, 0, mb_strrpos(mb_substr($text, 0, $length - $length_symbol), " ")) . $symbol);
                }
        }
}

/**
* Function to print the online status of a particular user.  This function is also LanceAlert ready
* where if the user is online and logged into the app it will show the online status of the IM user status
* (away, busy, online, dnd, etc) vs the status of online or offline
*
* @param        integer       user id
* @param        string        offline user color (example: gray)
* @param        string        online user color (example: green)
*
* @return	string        Returns the HTML representation of the online status
*/
function print_online_status($userid = 0, $offlinecolor = '', $onlinecolor = '')
{
        global $ilance, $phrase, $ilconfig, $show;
        $isonline = '<span class="' . $offlinecolor . '">{_offline}</span>';
        if (isset($show['lancealert']) AND $show['lancealert'])
        {
                // we don't appear to be online the web site, are we connected via lancealert?
                $sqlla = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.username, s.userID, s.status
                        FROM " . DB_PREFIX . "alert_sessions s,
                        " . DB_PREFIX . "users u
                        WHERE u.username = s.userID
                                AND u.user_id = '" . intval($userid) . "'
                                AND u.status = 'active'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlla) > 0)
                {
                        $resla = $ilance->db->fetch_array($sqlla, DB_ASSOC);
                        switch ($resla['status'])
                        {
                                case '0':
                                {
                                        // online
                                        $isonline = '<a href="lamsgr:SendIM?' . $resla['userID'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Online .. Click to Chat" /></a>';
                                        break;
                                }                            
                                case '1':
                                {
                                        // busy
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Busy" />';
                                        break;
                                }                            
                                case '2':
                                {
                                        // do not disturb
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Do Not Disturb" />';
                                        break;
                                }                            
                                case '3':
                                {
                                        // away
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_online.gif" border="0" alt="IM Status: Away" />';
                                        break;
                                }                            
                                case '4':
                                {
                                        // offline / invisible
                                        $isonline = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'la_offline.gif" border="0" alt="IM Status: '.'{_offline}'.'" />';
                                        break;
                                }
                        }
                }
                else 
                {
                        $sql = $ilance->db->query("
                                SELECT u.user_id, s.title
                                FROM " . DB_PREFIX . "sessions s,
                                " . DB_PREFIX . "users u
                                WHERE u.user_id = '" . intval($userid) . "'
                                        AND u.user_id = s.userid
                                        AND isuser = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $isonline = '<span class="' . $onlinecolor . '">{_online}</span>';
                        }	
                }
        }
        else 
        {
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "u.user_id
                        FROM " . DB_PREFIX . "sessions s,
                        " . DB_PREFIX . "users u
                        WHERE u.user_id = '" . intval($userid) . "'
                                AND u.user_id = s.userid
                                AND isuser = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $isonline = '<span class="' . $onlinecolor . '">{_online}</span>';
                }
        }
        return $isonline;	
}

/**
* Function to generate a random form field name based on a supplied character length limit
*
* @param        integer       length (default 10)
*
* @return	string        Returns the random form field name
*/
function construct_form_name($length = 10)
{
        $formname = mb_substr(mb_ereg_replace("[^a-zA-Z]", "", crypt(time())) . mb_ereg_replace("[^0-9]", "", crypt(time())) . mb_ereg_replace("[^a-zA-Z]", "", crypt(time())), 0, $length);
        return $formname;
}

/**
* Function to generate a unique user account number for the billing and payments system
*
* @return	string        Returns a formatted account number
*/
function construct_account_number()
{
        $first = rand(100, 999);
        $second = rand(100, 999);
        $third = rand(100, 999);
        $fourth = rand(100, 999);
        $fifth = rand(0, 9);
        return $first . $second . $third . $fourth . $fifth;
}

/**
* Function to print a human-readable filesize based on bytes being sent as an argument
*
* @param        integer     size in bytes
*
* @return	string      Returns formatted filesize like 1.3KB, 2.5MB, 1.7GB, etc
*/
function print_filesize($bytes = 0)
{
	if ($bytes < 0)
	{
		$format = '0.1 KB';
	}
        else if (mb_strlen($bytes) <= 9 AND mb_strlen($bytes) >= 7)
        {
                $format = number_format($bytes / 1048576, 1) . ' MB';
        }
        else if (mb_strlen($bytes) >= 10)
        {
                $format = number_format($bytes / 1073741824, 1) . ' GB';
        }
        else
        {
                $format = number_format($bytes / 1024, 1) . ' KB';
        }
        return $format;
}

/**
* Function to print a valid date and time string based on a unix timestamp
*
* @param        integer     unix timestamp
*
* @return	string      Returns formatted date time string (YYYY-MM-DD HH:MM:SS)
*/
function print_datetime_from_timestamp($time)
{
        return date("Y-m-d H:i:s", $time);
}

/**
* Function wrapper for strtotime
*
* @param        string      date range
*
* @return	string      Returns strtotime formatted string
*/
function print_convert_to_timestamp($str)
{
        return strtotime($str);
}

/**
* Function that will take an date array and rebuild into a valid date time string
*
* @param        array       date array
*
* @return	string      Returns valid date and time string
*/
function print_array_to_datetime($date, $time = '')
{
        if (empty($time))
        {
                $time = '00:00:00';
        }
        return $date[2] . '-' . $date[0] . '-' . $date[1] . ' ' . $time;
}

/**
* Function to handle parsing PHP code internally for add-on and product support in ILance
* and accepts code with or without <?php and ?> tags
*
* @param        string      php code to parse
*
* @return	mixed       Returns mixed output
*/
function parse_php_in_html($html_str = '')
{
        global $ilance;
        preg_match_all("/(<\?php|<\?)(.*?)\?>/si", $html_str, $raw_php_matches);
        $php_idx = 0;
        while (isset($raw_php_matches[0][$php_idx]))
        {
                $raw_php_str = $raw_php_matches[0][$php_idx];
                $raw_php_str = str_replace("<?php", "", $raw_php_str);
                $raw_php_str = str_replace("?>", "", $raw_php_str);
                ob_start();
                eval("$raw_php_str;");
                $exec_php_str = ob_get_contents();
                ob_end_clean();
                $exec_php_str = str_replace("\$", "\\$", $exec_php_str);
                $html_str = preg_replace("/(<\?php|<\?)(.*?)\?>/si", $exec_php_str, $html_str, 1);
                $php_idx++;
        }
        return $html_str;
}

/**
* Function to encrypt a url
*
* @param       array          url array
* 
* @return      string         encoded url
*/
function encrypt_url($array = array())
{
        $encoded = serialize($array);
        $encoded = base64_encode($encoded);
        $encoded = urlencode($encoded);
        return $encoded;
}

/**
* Function to decrypt a url
*
* @param       string         encoded url
* 
* @return      array          decoded url array
*/
function decrypt_url($encrypted = '')
{
        if (empty($encrypted))
        {
                $uncrypted = array();
                if ($_GET)
                {
                        foreach ($_GET as $key => $value)
                        {
                                $uncrypted["$key"] = $value;
                        }
                }
                else if ($_POST)
                {
                        foreach ($_POST as $key => $value)
                        {
                                $uncrypted["$key"] = $value;
                        }
                }
                return $uncrypted;
        }
        else
        {
		$uncrypted = urldecode($encrypted);
                $uncrypted = base64_decode($uncrypted);
                $uncrypted = unserialize($uncrypted);
                return $uncrypted;
        }
}

function mb_chunk_split($str, $l = 65, $e = "\r\n")
{
	$tmp = array_chunk(preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY), $l);
	$str = "";
	foreach ($tmp AS $t)
	{
		$str .= join("", $t) . $e;
	}
	return $str;
}

/**
* Function to process and print out a username bit with icons based on various bits of information
*
* @param       integer        user id
*
* @return      string         Formatted text
*/
function construct_username_bits($userid = 0)
{
	global $ilance, $phrase, $ilconfig, $show;
	$html = $pattern = $extraleftjoin = $extrafields = '';
	
	if (($html = $ilance->cache->fetch('construct_username_bits_' . $userid)) === false)
	{
		($apihook = $ilance->api('construct_username_bits_start')) ? eval($apihook) : false;
	
		$roles = $ilance->db->query("
			    SELECT r.custom
			    FROM " . DB_PREFIX . "subscription_user s
			    LEFT JOIN " . DB_PREFIX . "subscription_roles r ON (r.roleid = s.roleid)
			    WHERE s.user_id = '" . intval($userid) . "'
			    LIMIT 1
		    ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($roles) > 0)
		{
			$role = $ilance->db->fetch_array($roles, DB_ASSOC);
			if (!empty($role['custom']))
			{
				$pattern = $role['custom'];
			}
		}
		if (!empty($pattern))
		{
			$feedback_memberinfo = array ();
			$feedback_memberinfo = $ilance->feedback->datastore(intval($userid));
			$username_url = print_username(intval($userid), 'url', 0, '', '');
			$pattern = str_replace('[fbscore]', $feedback_memberinfo['score'], $pattern);
			$pattern = str_replace('[fbpercent]', '<a href="' . $username_url . '" title="{_total_positive_feedback_percentile}">' . $feedback_memberinfo['pcnt'] . '%</a>', $pattern);
			$pattern = str_replace('[rating]', '<a href="' . $username_url . '" title="{_total_feedback_rating_out_of_500}">' . $feedback_memberinfo['rating'] . '</a>', $pattern);
			$pattern = str_replace('[stars]', $ilance->feedback->print_feedback_icon($feedback_memberinfo['score']), $pattern);
			$pattern = str_replace('[verified]', '', $pattern);
			$pattern = str_replace('[subscription]', $ilance->subscription->print_subscription_icon(intval($userid)), $pattern);
			$pattern = str_replace('[fbimport]', $ilance->feedback_import->print_imported_feedback(intval($userid), 'userbit'), $pattern);
	    
			($apihook = $ilance->api('construct_username_bits_end')) ? eval($apihook) : false;
	    
			$html .= $pattern;
			unset($feedback_memberinfo);
		}
		$ilance->cache->store('construct_username_bits_' . $userid, $html);
	}
	return $html;
}

/**
* Function to print a user's username based on seo and other elements such as icons, subscription info, etc
*
* @param       integer        user id
* @param       string         mode
* @param       boolean        is bold? (default false)
* @param       string         extra info
* @param       string         extra seo info
* @param       string         display name
*
* @return      string         Returns a formatted version of the user's username
*/
function print_username($userid = 0, $mode = 'href', $bold = 0, $extra = '', $extraseo = '', $displayname = '')
{
        global $ilance, $ilpage, $ilconfig;
        $username = fetch_user('username', intval($userid), '', '', false);
        $html = '';
        if ($mode == 'href')
        {
                $displayname = !empty($username) ? $username : $displayname;
                if (!empty($bold) AND $bold)
                {
                        $displayname = '<strong>' . $username . '</strong>';
                }
		if (!empty($username))
		{
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<span class="blue"><a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($username) . $extraseo . '" rel="nofollow">' . $displayname . '</a></span> ' . construct_username_bits(intval($userid));
			}
			else
			{
				$html .= '<span class="blue"><a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra . '" rel="nofollow">' . $displayname . '</a></span> ' . construct_username_bits(intval($userid));
			}    
		}
		else
		{
			$html .= $displayname;
		}
        }
        else if ($mode == 'plain')
        {
                $displayname = !empty($username) ? $username : $displayname;
                if (!empty($bold) AND $bold)
                {
                        $username = '<strong>' . $displayname . '</strong>';
                }
                $html = $username;
        }
        else if ($mode == 'custom')
        {
                if (!empty($bold) AND $bold)
                {
                        $displayname = '<strong>'.$displayname.'</strong>';
                }
		if (!empty($username))
		{
			// does admin use SEO urls?
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($username) . $extraseo . '" rel="nofollow">' . $displayname . '</a>';
			}
			else
			{
				$html .= '<a href="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra . '" rel="nofollow">' . $displayname . '</a>';
			}
		}
		else
		{
			$html .= $displayname;
		}
        }
        else if ($mode == 'url')
        {
		$displayname = !empty($username) ? $username : $displayname;
                // does admin use SEO urls?
		if (!empty($username))
		{
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$html .= ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . print_seo_url($ilconfig['memberslistingidentifier']) . '/' . construct_seo_url_name($displayname) . $extraseo;
			}
			else
			{
				$html .= ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['members'] . '?id=' . intval($userid) . $extra;
			}
		}
		else
		{
			$html .= $displayname;
		}
        }
        return $html;
}

/**
* Function to fetch a user's email address from the datastore based on an actual user id
*
* @param       string         unit type (D, M or Y) 
*
* @return      string         Returns the actual unit type phrase in the appropriate language
*/
function print_unit($unit = '')
{
        if (!empty($unit))
        {
                switch ($unit)
                {
                        case 'D':
                        {
                                return '{_unit_d}';
                                break;
                        }                    
                        case 'M':
                        {                        
                                return '{_unit_m}';
                                break;
                        }                    
                        case 'Y':
                        {
                                return '{_unit_y}';
                                break;
                        }
                }
        }
}

/**
* Function to print the subscription renewal date/timestamp based on days.
*
* @param       integer        days
*
* @return      string         Returns datetime stamp (ie: 2007-02-01 22:00:00)
*/
function print_subscription_renewal_datetime($days)
{
	return date('Y-m-d H:i:s', (TIMESTAMPNOW + intval($days) * 24 * 3600));
}

/**
* Callback function for convert_urlencoded_unicode() which will also use iconv library if installed
*
* @param       string         hexidecimal character
* @param       string         character set
*
* @return      string         Returns a numeric entity
*/
function convert_unicode_char_to_charset($unicodeint, $charset)
{
        $isutf8 = (mb_strtolower($charset) == 'utf-8');
        if ($isutf8)
        {
                return convert_int2utf8($unicodeint);
        }
        if (function_exists('iconv'))
        {
                // convert this character -- if unrepresentable, it should fail
                $output = @iconv('UTF-8', $charset, convert_int2utf8($unicodeint));
                if ($output !== false AND $output !== '')
                {
                        return $output;
                }
        }
        return "&#$unicodeint;";
}

/**
* Function to conver a urlencoded string into unicode for formatting purposes
*
* @param       string         text
*
* @return      string         Returns a formatted unicode string
*/
function convert_urlencoded_unicode($text)
{
        global $ilconfig;
        $isutf8 = (mb_strtolower($ilconfig['template_charset']) == 'utf-8' OR mb_strtolower($ilconfig['template_charset']) == 'utf8');
        $return = preg_replace('#%u([0-9A-F]{1,4})#ie', "convert_unicode_char_to_charset(hexdec('\\1'), \$ilconfig['template_charset'])", $text);
        if (!$isutf8)
        {
                $return = preg_replace('#&([a-z]+);#i', '&amp;$1;', $return);
                $return = @html_entity_decode($return, ENT_NOQUOTES, $ilconfig['template_charset']);
        }
        return $return;
}

/**
* Function to determine if a supplied email address is valid based on it's apperence
*
* @param       string         email address
*
* @return      string         Returns true or false if email address is valid
*/
function is_valid_email($email = '')
{
        return preg_match('#^[a-z0-9.!\#$%&\'*+-/=?^_`{|}~]+@([0-9.]+|([^\s\'"<>]+\.+[a-z]{2,6}))$#si', $email);
}

/**
* Produces the phrase enabled or disabled based on a supplied boolean value
*
* @return	string
*/
function print_boolean($value)
{
        return ($value == 1 ? '{_enabled}' : '{_disabled}');
}

/**
* Function to determine if a particular ip address being supplied is excluded from being blocked during maintenance mode.
*
* @param       integer        ip address
*
* @return      string         Returns true or false
*/
function ip_address_excluded($ipaddress = '')
{
	global $ilconfig;
	$addresses = array();
	$user_ipaddress = $ipaddress . '.';
	$isexcluded = false;
	// #### comma and space formatting #####################
	if (strrchr($ilconfig['maintenance_excludeips'], ', '))
	{
		$addresses = explode(', ', $ilconfig['maintenance_excludeips']);
	}
	// #### new line formatting ############################
	else
	{
		$addresses = preg_split('#\s+#', $ilconfig['maintenance_excludeips'], -1, PREG_SPLIT_NO_EMPTY);
	}
	if (count($addresses) > 0)
	{
		foreach ($addresses AS $excluded_ip)
		{
			if (strpos($excluded_ip, '*') === false AND $excluded_ip{strlen($excluded_ip) - 1} != '.')
			{
				$excluded_ip .= '.';
			}
			$excluded_ip_regex = str_replace('\*', '(.*)', preg_quote($excluded_ip, '#'));
			if (preg_match('#^' . $excluded_ip_regex . '#U', $user_ipaddress))
			{
				// found an ip that should be excluded...
				$isexcluded = true;
				break;
			}
		}
	}
	return $isexcluded;
}

/**
* Function to parse and recreate a user-supplied youtube cut n' paste url
*
* @param        string      youtube video url
*
* @return	boolean     returns true or false
*/
function parse_youtube_video_url($url = '', $onlyvideoid = false)
{
	if (empty($url))
	{
		return false;
	}
	if ($onlyvideoid)
	{
		$t = explode('=', $url);
		$url = $t[1];
	}
	else
	{
		$url = str_replace('/watch?v=', '/v/', $url);
	}
        return $url;
}

/**
* Function to format a zipcode for ILance by removing spaces and dashes
*
* @param       string         zip code to format
*
* @return      string         Returns HTML formatted string
*/
function format_zipcode($zipcode = '')
{
	$zipcode = str_replace(' ', '', $zipcode);
	//$zipcode = str_replace('-', '', $zipcode);
	$zipcode = strtoupper($zipcode);
	return $zipcode;
}

/**
* Function to validate telephone numbers based on specific phone number patterns pre-defined
*
* @param       string         telephone number to verify
*
* @return      boolean        Returns true
*/
function validate_telephone_number($number = '')
{
	$formats = array(
		'###-###-####',
		'####-###-###',
		'(###) ###-###',
		'(###)###-####',
		'####-####-####',
		'##-###-####-####',
		'####-####',
		'###-###-###',
		'#####-###-###',
		'##########'
	);
	$format = trim(ereg_replace("[0-9]", "#", $number));
	return (in_array($format, $formats)) ? true : false;
}

// functions using for google checkout split response xml value
function get_arr_result($child_node) 
{
	$result = array();
	if (isset($child_node))
	{
		if (is_associative_array($child_node))
		{
			$result[] = $child_node;
		}
		else
		{
			foreach ($child_node AS $curr_node)
			{
				$result[] = $curr_node;
			}
		}
	}
	return $result;
}
  
function is_associative_array($var) 
{
	return is_array($var) && !is_numeric(implode('', array_keys($var)));
}

function autolink($text = '', $target = '_blank', $nofollow = true)
{
	$urls = autolink_find_urls($text);
	if (!empty($urls))
	{
		array_walk($urls, 'autolink_create_html_tags', array('target' => $target, 'nofollow' => $nofollow));
		$text = strtr($text, $urls);
	}
	return $text;
}

function autolink_find_urls($text)
{
	$scheme = '(http:\/\/|https:\/\/)';
	$www = 'www\.';
	$ip = '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
	$subdomain = '[-a-z0-9_]+\.';
	$name = '[a-z][-a-z0-9]+\.';
	$tld = '[a-z]+(\.[a-z]{2,2})?';
	$the_rest = '\/?[a-z0-9._\/~#&=;%+?-]+[a-z0-9\/#=?]{1,1}';            
	$pattern = "$scheme?(?(1)($ip|($subdomain)?$name$tld)|($www$name$tld))$the_rest";
	$pattern = '/' . $pattern . '/is';
	$c = preg_match_all($pattern, $text, $m);
	unset($text, $scheme, $www, $ip, $subdomain, $name, $tld, $the_rest, $pattern);
	if ($c)
	{
		return(array_flip($m[0]));
	}
	return(array());
}

function autolink_create_html_tags(&$value, $key, $other = null)
{
	$target = $nofollow = null;
	if (is_array($other))
	{
		$target = ($other['target'] ? ' target="' . $other['target'] . '"' : null);
		$nofollow = ($other['nofollow'] ? ' rel="nofollow"' : null);     
	}
	$value = '<span class="blueonly"><a href="' . urldecode($key) . '"' . $target . '' . $nofollow . '>' . print_string_wrap(urldecode($key), 75) . '</a></span>';
}

function custom_number_format($n, $precision = 1)
{
	if ($n < 1000000)
	{
	    $n_format = number_format($n / 1000, $precision) . 'K';
	}
	else if ($n < 1000000000)
	{
	    $n_format = number_format($n / 1000000, $precision) . 'M';
	}
	else
	{
	    // At least a billion
	    $n_format = number_format($n / 1000000000, $precision) . 'B';
	}
	return $n_format;
}

function can_post_html($userid)
{
	global $ilance;
	return (($ilance->permissions->check_access($userid, 'posthtml') == "yes") OR fetch_user('posthtml', $userid)) ? true : false;
}

function remove_newline($str)
{
	return str_replace(array("\n", "\r\n", "\r"), "", $str);
}

function pulldown_year()
{
	$html = '<select name="year" class="input"><option value="">-</option>';
	$years = range (date("Y"), 1900);
	foreach ($years AS $value)
	{ 
		$html .= '<option value="' . $value . '">' . $value . '</option>'; 
	} 
	$html .= '</select>';
	return $html;
}

function print_legend($key)
{
	global $ilance, $ilconfig;
	$legend = '';
	if (isset($ilconfig[$key]) AND $ilconfig[$key] == '1')
	{
		$slng = $_SESSION['ilancedata']['user']['slng'];
		if (($legend = $ilance->cache->fetch('main_legend_' . $slng)) === false)
		{
			$ilance->template->load_popup('legend', 'legend.html');
			$ilance->template->parse_if_blocks('legend');
			$ilance->template->handle_template_hooks('legend');
			$ilance->template->parse_template('legend');
			$ilance->template->parse_template_collapsables('legend');
			$legend = $ilance->template->templateregistry['legend'];
			$ilance->cache->store('main_legend_' . $slng, $legend);
		}
	}
	return $legend;
}

function construct_pulldown($id = '', $name = '', $values = array(), $default = '', $extra = '')
{	
	$html = '<select id="' . $id . '" name="' . $name . '" ' . $extra . '>';
	foreach ($values AS $key => $value)
	{
		if (strtolower($key) == "optgroupstart")
		{
			$html .= '<optgroup label="' . $value . '">';
		}
		else if (strtolower($key) == "optgroupend")
		{
			$html .= '</optgroup>';
		}
		else
		{
			$sel = ($key == $default) ? ' selected="selected"' : '';
			$html .= '<option value="' . $key . '"' . $sel . '>' . $value . '</option>';
		}
	}
	$html .= '</select>';
	return $html;
}

/**
* Function to fetch and return an array with the overall savings total (amount and percentage) from the original price vs. the discounted price
*
* @param      integer      original price
* @param      integer      discount price
*
* @return     array        Mixed array of amounts requested
*/
function fetch_savings_total($original_price = 0, $discount_price = 0)
{
	$savings = $original_price - $discount_price;
	$savingspercentage = number_format(($savings / $original_price) * 100, 1);
	return array('savings' => $savings, 'savingspercentage' => $savingspercentage);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>