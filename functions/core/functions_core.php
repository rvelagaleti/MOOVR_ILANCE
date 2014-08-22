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
* @package      iLance\Global\Core
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to build an array of information used in ILance for debugging purposes
*
* @param       string       message
* @param       string       debug type (FUNCTION, CLASS, NOTICE, OTHER)
*/
function debug($text = '', $type = 'OTHER')
{
	if (DEBUG_FOOTER)
	{
		$mem = (MEMORY_DEBUG) ? ' | memory alloc ' . print_filesize(memory_get_usage(true)) . ' | mem peak ' .  print_filesize(memory_get_peak_usage(true)) . ' |' : '';
		$GLOBALS['DEBUG']["$type"][] = $text . $mem;
	}
}

/**
* Function to handle the construction of the registry object datastore
*
* @param       string       class name (2 parts: api.xxx) where api is folder and xxx is class filename to load
* @param       string       class argument set 1 (optional)
* @param       string       class argument set 2 (optional)
* @param       string       class argument set 3 (optional)
* @param       string       class argument set 4 (optional)
* @param       string       class argument set 5 (optional)
* 
* @return      object       Returns our registry object
*/
function construct_object($classname, $param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '')
{
	global $ilance;
	if (empty($classname))
	{
		return false;
	}
	$function = new stdClass();
	$function->timer = new timer;
	$function->timer->start();
	$parts = explode('.', $classname);
	$classtitle = $parts[1];
	$arr = explode('_', $classtitle);
	if (is_array($arr) AND count($arr) > 1)
	{
		$objmain = construct_object($parts[0] . '.' . $arr[0]);
		if ($objmain)
		{
			$ilance->$arr[0] = $objmain;
		}
	}
	if (!isset($ilance->$classtitle))
	{
		if (class_exists($classtitle))
		{
			$obj = new $classtitle($param1, $param2, $param3, $param4, $param5);
		}
		else if (file_exists(DIR_FUNCTIONS . $parts[0] . '/class.' . $classtitle . '.inc.php'))
		{
			include_once(DIR_FUNCTIONS . $parts[0] . '/class.' . $classtitle . '.inc.php');
			$obj = new $classtitle($param1, $param2, $param3, $param4, $param5);
		}
		else
		{
			$obj = false;
		}
	}
	else
	{
		$obj = $ilance->$classtitle;
	}
	$function->timer->stop();
	DEBUG("$classtitle loaded in " . $function->timer->get() . " seconds", "CLASS");
	return $obj;
}

/**
* Function to emulate a unicode version of htmlspecialchars()
* 
* @param	string	     text to be converted into unicode
* @param        bool         (optional) disable entities? (default true)
*
* @return	string
*/
function htmlspecialchars_uni($text, $entities = true)
{
	return str_replace(array ('<', '>', '"'), array ('&lt;', '&gt;', '&quot;'), preg_replace('/&(?!' . ($entities ? '#[0-9]+' : '(#[0-9]+|[a-z]+)') . ';)/si', '&amp;', $text));
}

/**
* Function to fetch all $_POST and $_GET recursively
* 
* @param	array	     array
*/
function array_recursive($array)
{
	$html = '';
	foreach ($array AS $key => $value)
	{
		if (is_array($value))
		{
			$value = array_recursive($value);
			$html .= "$key=$value&amp;";
		}
		else
		{
			$html .= "$key=$value&amp;";
		}
	}
	return $html;
}

/**
* Function to emulate an expression where the values for true and false are predefined
*
* @param	string	     expression
* @param        string       value to return if expression is true
* @param	string       value to return if expression is false
*
* @return	string
*/
function iif($exp, $rettrue, $retfalse = '')
{
	return ($exp ? $rettrue : $retfalse);
}

/**
* some hosts do not have this function which is required for the bulk uploader component.
*/
if (!function_exists('str_getcsv'))
{
	function str_getcsv($input, $delimiter = ",", $enclosure = '"', $escape = "\\")
	{
		$fiveMBs = 5 * 1024 * 1024;
		$fp = fopen("php://temp/maxmemory:$fiveMBs", 'r+');
		fputs($fp, $input);
		rewind($fp);
		$data = fgetcsv($fp, 1000, $delimiter, $enclosure);
		fclose($fp);
		return $data;
	}
}

/**
* Fetches the IP address of the current visitor
*
* @return	string
*/
function fetch_ip_address()
{
        return $_SERVER['REMOTE_ADDR'];
}

/**
* Fetches a proxy IP address of visitor, will use regular ip if proxy cannot be detected.
*
* @return	string
*/
function fetch_proxy_ip_address()
{
        $ip = $_SERVER['REMOTE_ADDR'];
        if (isset($_SERVER['HTTP_CLIENT_IP']))
        {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $match))
        {
                foreach ($match[0] AS $ipaddress)
                {
                        if (!preg_match("#^(10|172\.16|192\.168)\.#", $ipaddress))
                        {
                                $ip = $ipaddress;
                                break;
                        }
                }
        }
        else if (isset($_SERVER['HTTP_FROM']))
        {
                $ip = $_SERVER['HTTP_FROM'];
        }
        return $ip;
}

/**
* Function to determine if a string being supplied is already php serailzed() or not
*
* @return	boolean     returns true or false
*/
function is_serialized($data = '') 
{ 
        return (@unserialize($data) !== false); 
}

/**
* Function to determine if the current user is a search engine or real user based on the crawlers.xml robot file
*
* @return	boolean     returns true or false if server is overloaded
*/
function is_search_crawler()
{
        global $show, $ilance;
	if (($xml = $ilance->cachecore->fetch("crawlers_xml")) === false)
	{
		$xml = array();
		$handle = opendir(DIR_XML);
		while (($file = readdir($handle)) !== false)
		{
			if (!preg_match('#^crawlers.xml$#i', $file, $matches))
			{
				continue;
			}
			$xml = $ilance->xml->construct_xml_array('UTF-8', 1, $file);
		}
		ksort($xml);
		$ilance->cachecore->store("crawlers_xml", $xml);
	}
	if (is_array($xml['crawler']))
	{
		foreach ($xml['crawler'] AS $crawler)
		{
			if (defined('USERAGENT') AND USERAGENT != '' AND preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", USERAGENT))
			{
                                $show['searchenginename'] = $crawler['title'];
				return true;
			}
		}
        }
        unset($handle, $xml);
        return false;
}

/**
* Function to init server overload checkup on Linux/Unix machines
*
* @return	boolean     returns true or false if server is overloaded
*/
function init_server_overload_checkup($returnbool = false)
{
        global $ilconfig, $loadaverage;
        $serveroverloaded = false;
	$loadaverage = '';
	if (PHP_OS == 'Linux')
	{
		$loadaverageArray = @sys_getloadavg();
		$loadaverage  = $loadaverageArray[0];
		if (isset($ilconfig['serveroverloadlimit']) AND  $ilconfig['serveroverloadlimit'] > 0 AND $loadaverage >  $ilconfig['serveroverloadlimit'])
		{
			 $serveroverloaded = true;
		}
	}
	if (empty($loadaverage))
	{
		$loadaverage = 'n/a';
	}
	if ($returnbool)
	{
		return $serveroverloaded;
	}
	if ($serveroverloaded)
	{
		$template = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title>' . SITE_NAME . ' - Too many connections</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css">
<!--	
body { background-color: white; color: black; }
#container { width: 400px; }
#message   { width: 400px; color: black; background-color: #FFFFCC; }
#bodytitle { font: 13pt/15pt verdana, arial, sans-serif; height: 35px; vertical-align: top; }
.bodytext  { font: 8pt/11pt verdana, arial, sans-serif; }
a:link     { font: 8pt/11pt verdana, arial, sans-serif; color: red; }
a:visited  { font: 8pt/11pt verdana, arial, sans-serif; color: #4e4e4e; }
-->
</style>
</head>
<body>
<table cellpadding="3" cellspacing="5" id="container">
<tr>
        <td id="bodytitle" width="100%">This application has too many connections</td>
</tr>
<tr>
        <td class="bodytext" colspan="2">Please try again in a few minutes. Thank you.</td>
</tr>
<tr>
        <td colspan="2"><hr /></td>
</tr>
<tr>
        <td class="bodytext" colspan="2">
                You can also:
                <ul>
                        <li>Load the page again by clicking the <a href="#" onclick="window.location = window.location;">Refresh</a> button in your web browser.</li>
                        <li>Click the <a href="javascript:history.back(1)">Back</a> button to try another link.</li>
                </ul>
        </td>
</tr>
<tr>
        <td class="bodytext" colspan="2">We apologise for any inconvenience.</td>
</tr>
</table>
</body>
</html>';
		// tell the search engines that our service is temporarily unavailable to prevent indexing db errors
		header('HTTP/1.1 503 Service Temporarily Unavailable');
		header('Status: 503 Service Temporarily Unavailable');
		header('Retry-After: 3600');
		echo $template;
		exit();
	}
}

/**
* Function to handle displaying the date and time within ILance.  This function has been enhanced to display the actual
* time in any given timezone id passed to it.  If no time zone id is supplied the default time display will be that
* of the site marketplace.
*
* @param        string      date and time string
* @param        string      format of string (optional)
* @param        bool        should we show the time zone abbreviation in the string?
* @param        bool        should we treat the date display with "Yesterday and Today" instead of the actual date?
* @param        string      time zone id (ie: America/New_York)
*
* @return	string      Returns the formatted strftime() date and time string including a timezone identifier if requested
*/
function print_date($datetime = '', $format = '', $showtimezone = false, $yesterdaytoday = false, $timezone = '')
{
        global $ilance, $ilconfig, $phrase;
        if (empty($format))
        {
                $format = $ilconfig['globalserverlocale_globaltimeformat']; //D, M d, Y h:i A
        }
        if (empty($timezone) AND isset($_SESSION['ilancedata']['user']['timezone']) AND !empty($_SESSION['ilancedata']['user']['timezone']))
        {
                $timezone = $_SESSION['ilancedata']['user']['timezone'];
        }
        if ($yesterdaytoday AND $ilconfig['globalserverlocale_yesterdaytodayformat'])
        {
		$tempdate = date('Y-m-d', $ilance->datetimes->fetch_timestamp_from_datetime($datetime));
		$difference = $ilance->datetimes->fetch_timestamp_from_datetime(DATETIME24H) - $ilance->datetimes->fetch_timestamp_from_datetime($datetime);
		if ($difference < 3600)
		{
			if ($difference < 120)
			{
				$result = '{_less_an_a_minute_ago}';
			}
			else
			{
				$result = $ilance->language->construct_phrase("{_x_minutes_ago}", intval($difference / 60));
			}
		}
		else if ($difference < 7200)
		{
			$result = '{_one_hour_ago}';
		}
		else if ($difference < 86400)
		{
			$result = $ilance->language->construct_phrase("{_x_hours_ago}", intval($difference / 3600));
		}                        
        }
        if (empty($result))
        {
                $datetime = empty($datetime) ? null : $datetime;
                $datetimezone = new DateTimeZone($ilconfig['globalserverlocale_sitetimezone']);
                $date = new DateTime($datetime, $datetimezone);
                if (!empty($timezone))
                {
                    $datetimezone = new DateTimeZone($timezone);
                    $date->setTimezone($datetimezone);
                }
                $result = $date->format($format);
                if ($showtimezone)
                {
                    $result .= ' ' . $date->format('T');
                }
        }
        $result = $ilance->common->entities_to_numeric($result);
        return $result;
}

/**
* Function to return a string where HTML entities have been converted to their original characters
*
* @param	string	     html string to parse
* @param	bool         convert unicode string back from HTML entities?
*
* @return	string
*/
function un_htmlspecialchars($text = '', $parseunicode = false)
{
        if ($parseunicode)
        {
                $text = preg_replace('/&#([0-9]+);/esiU', "convert_int2utf8('\\1')", $text);
        }
        return str_replace(array('&lt;', '&gt;', '&quot;', '&amp;'), array('<', '>', '"', '&'), $text);
}

/**
* Encodes HTML safely for UTF-8. Use instead of htmlentities.
*
* @param        string          $var
* @return       string          Returns a valid UTF-8 string
*/
function ilance_htmlentities($text = '')
{
        if (phpversion() < '4.0.3')
        {
                return htmlentities($text);
        }
        else if (phpversion() < '4.1.0')
        {
                return htmlentities($text, ENT_QUOTES);
        }
        else
        {
                return htmlentities($text, ENT_QUOTES, 'UTF-8');
        }
}

/**
* Function to initialize the referral code tracking system
*
* @return	void
*/
function init_referral_tracker()
{
        global $ilance;
        $refrid = (isset($_REQUEST['rid']) AND !empty($_REQUEST['rid'])) ? $_REQUEST['rid'] : '';
        if (!empty($refrid))
        {
		$remote = IPADDRESS;
		$ragent = USERAGENT;
		$rrefer = REFERRER;
                $sql = $ilance->db->query("
                        SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "rid
                        FROM " . DB_PREFIX . "users
                        WHERE rid = '" . $ilance->db->escape_string($refrid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        set_cookie('rid', $refrid);
                        $ilance->referral->verify_referral_clickthrough($remote, $ragent, $rrefer, $refrid);
                }
		unset($sql);
        }
}

/**
* Function to convert javascript tags to entities
*
* @return      string       HTML formatted string
*/
function handle_input_keywords($text = '', $entities = false)
{
        $text = htmlspecialchars_uni($text, $entities);
        return $text;
}

/**
* Function to break up a long string with no spaces based on a supplied character limit
*
* @param       string         text
* @param       integer        chracter limit to break up
*
* @return      string         Formatted text
*/
function print_string_wrap($text = '', $width = 65, $break = ' ')
{
        return preg_replace('#(\S{' . $width . ',})#e', "mb_chunk_split('$1', $width, '$break')", $text); 
}

/**
* Function to cut a string of characters apart using an argument limiter as the amount of characters to cut between
*
* @param	string	     html string
* @param	integer      limiter amount (ie: 50)
*
* @return	string       HTML representation of the string which has been cut
*/
function cutstring($string = '', $limit)
{
	if (mb_strlen($string) > $limit)
	{
		$string = mb_substr($string, 0, $limit);
		if (($pos = mb_strrpos($string, ' ')) !== false)
		{
			$string = mb_substr($string, 0, $pos);
		}
		return $string;
	}
	return $string;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>