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
* Global who's online connections functions for iLance.
*
* @package      iLance\Global\AdminCP\Connections
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to convert seconds into text format based on a supplied number of seconds argument
*
* @param        integer      number of seconds
*
* @return	string       Returns text
*/
function sec2text($num_secs)
{
	$htmlx = '';
	$days = intval(intval($num_secs) / 86400);
	if ($days >= 1)
	{
		$htmlx .= $days;
		$htmlx .= '{_d_shortform}, ';
		$num_secs = $num_secs - ($days * 86400);
	}
	$hours = intval(intval($num_secs) / 3600);
	if ($hours >= 1)
	{
		$htmlx .= $hours;
		$htmlx .= '{_h_shortform}, ';
		$num_secs = $num_secs - ($hours * 3600);
	}
	$minutes = intval(intval($num_secs) / 60);
	if ($minutes >= 1)
	{
		$htmlx .= $minutes;
		$htmlx .= '{_m_shortform}, ';
		$num_secs = $num_secs - ($minutes * 60);
	}
	$htmlx .= $num_secs . '{_s_shortform}';
	return $htmlx;
}

/**
* Function to fetch the title of the operating system found from the crawler agent ident
*
* @param        string       browser agent string
* @param        boolean      force icon mode (default false)
*
* @return	string       Returns name of connected crawler
*/
function fetch_os_name($agent = '', $forceicon = false)
{
	global $ilconfig;
	if ($forceicon)
	{
		$oses = array (
			'<span title="Windows 7"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'windows nt 6.1',
			'<span title="Windows Vista"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windowsvista.png" border="0" alt="" /></span>' => 'windows nt 6.0',
			'<span title="Windows Server 2003"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'windows nt 5.2',
			'<span title="Windows XP"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windowsxp.png" border="0" alt="" /></span>' => '(windows nt 5.1)|(windows xp)',
			'<span title="Windows 2000 sp1"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'windows nt 5.01',
			'<span title="Windows 2000"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => '(windows nt 5.0)|(windows 2000)',
			'<span title="Windows NT"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => '(windows nt 4.0)|(winnt4.0)|(winnt)|(windows nt)',
			'<span title="Windows Me"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => '(windows 98)|(win 9x 4.90)|(windows me)',
			'<span title="Windows 98"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => '(windows 98)|(win98)',
			'<span title="Windows 95"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows95.png" border="0" alt="" /></span>' => '(windows 95)|(win95)|(windows_95)',
			'<span title="Windows Ce"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'windows ce',
			'<span title="Windows 3.11"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'win16',
			'<span title="Windows (version unknown)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/windows.png" border="0" alt="" /></span>' => 'windows',
			'<span title="OpenBSD"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/openbsd.png" border="0" alt="" /></span>' => 'openbsd',
			'<span title="SunOS"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/sunos.png" border="0" alt="" /></span>' => 'sunos',
			'<span title="Ubuntu"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/ubuntu.png" border="0" alt="" /></span>' => 'ubuntu',
			'<span title="Linux"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/linux.png" border="0" alt="" /></span>' => '(linux)|(x11)|(red hat)',
			'<span title="Mac OS X Beta (Kodiak)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x beta',
			'<span title="Mac OS X Cheetah"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.0',
			'<span title="Mac OS X Puma"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.1',
			'<span title="Mac OS X Jaguar"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.2',
			'<span title="Mac OS X Panther"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.3',
			'<span title="Mac OS X Tiger"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.4',
			'<span title="Mac OS X Leopard"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.5',
			'<span title="Mac OS X Snow Leopard"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.6',
			'<span title="Mac OS X Lion"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x 10.7',
			'<span title="Mac OS X (version unknown)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => 'mac os x',
			'<span title="Mac OS (classic)"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/mac.png" border="0" alt="" /></span>' => '(mac_powerpc)|(macintosh)',
			'<span title="QNX"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/qnx.png" border="0" alt="" /></span>' => 'qnx',
			'<span title="BeOS"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/beos.png" border="0" alt="" /></span>' => 'beos',
			'<span title="OS2"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/os2.png" border="0" alt="" /></span>' => 'os/2'
		);
	}
	else
	{
		$oses = array (
			'Windows 7' => 'windows nt 6.1',
			'Windows Vista' => 'windows nt 6.0',
			'Windows Server 2003' => 'windows nt 5.2',
			'Windows XP' => '(windows nt 5.1)|(windows xp)',
			'Windows 2000 sp1' => 'windows nt 5.01',
			'Windows 2000' => '(windows nt 5.0)|(windows 2000)',
			'Windows NT' => '(windows nt 4.0)|(winnt4.0)|(winnt)|(windows nt)',
			'Windows Me' => '(windows 98)|(win 9x 4.90)|(windows me)',
			'Windows 98' => '(windows 98)|(win98)',
			'Windows 95' => '(windows 95)|(win95)|(windows_95)',
			'Windows CE' => 'windows ce',
			'Windows 3.11' => 'win16',
			'Windows (version unknown)' => 'windows',
			'OpenBSD' => 'openbsd',
			'SunOS' => 'sunos',
			'Ubuntu' => 'ubuntu',
			'Linux' => '(linux)|(x11)|(red hat)',
			'Mac OS X Beta (Kodiak)' => 'mac os x beta',
			'Mac OS X Cheetah' => 'mac os x 10.0',
			'Mac OS X Puma' => 'mac os x 10.1',
			'Mac OS X Jaguar' => 'mac os x 10.2',
			'Mac OS X Panther' => 'mac os x 10.3',
			'Mac OS X Tiger' => 'mac os x 10.4',
			'Mac OS X Leopard' => 'mac os x 10.5',
			'Mac OS X Snow Leopard' => 'mac os x 10.6',
			'Mac OS X Lion' => 'mac os x 10.7',
			'Mac OS X (version unknown)' => 'mac os x',
			'Mac OS (classic)' => '(mac_powerpc)|(macintosh)',
			'QNX' => 'qnx',
			'BeOS' => 'beos',
			'OS2' => 'os/2'
		);
	}
	$agent = strtolower($agent ? $agent : $_SERVER['HTTP_USER_AGENT']);
	foreach ($oses AS $os => $pattern)
	{
		if (preg_match('/' . str_replace('/', '\/', $pattern) . '/i', $agent))
		{
			return $os;
		}
	}
	return 'Unknown';
}

/**
* Function to fetch the title of the crawler found within the robot file
*
* @return	string     Returns name of connected crawler
*/
function fetch_search_crawler_title($agent)
{
	global $ilance;
	if(($xml = $ilance->cachecore->fetch("crawlers_xml")) === false)
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
	if (is_array($xml['crawler']) AND isset($agent) AND $agent != '')
	{
		foreach ($xml['crawler'] AS $crawler)
		{
			if (preg_match("#" . preg_quote($crawler['agent'], '#') . "#si", $agent))
			{
				return handle_input_keywords($crawler['title']);
			}
		}
        }
        unset($handle, $xml);
        return 'Crawler';
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>