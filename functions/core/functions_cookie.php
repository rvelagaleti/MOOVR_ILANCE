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
* Global functions to handle cookie operations in iLance.
*
* @package      iLance\Global\Cookie
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to create a cookie variable name
* Note: Internet Explorer for Mac does not support httponly
*
* @param	string	        cookie name
* @param	mixed	        cookie value
* @param	boolean	        is permanent for 1 year? (default true)
* @param	boolean	        enable secure cookies over SSL
* @param	boolean	        enable httponly cookies in supported browsers? (default false)
* @param        integer         (optional) force cookie to expiry in x days (default 365)
*/
function set_cookie($name = '', $value = '', $permanent = true, $allowsecure = true, $httponly = false, $expiredays = 365)
{
	if (empty($name) OR (!empty($name) AND stristr($name, 'COOKIE_PREFIX')))
	{
		return false;
	}
	$expire = ($permanent) ? TIMESTAMPNOW + 60 * 60 * 24 * $expiredays : 0;
	if ($expire <= 0 AND $expiredays > 0)
	{
		$expire = TIMESTAMPNOW + 60 * 60 * 24 * $expiredays;
	}
	$httponly = (($httponly AND ($ilance->common->is_browser('ie') AND $ilance->common->is_browser('mac'))) ? false : $httponly);
	$secure = ((PROTOCOL_REQUEST === 'https' AND $allowsecure) ? true : false);
	do_set_cookie(COOKIE_PREFIX . $name, $value, $expire, '/', '', $secure, $httponly);
}

/**
* Callback function to actually set the cookie called from set_cookie()
*
* @param	string	        cookie name
* @param	string	        cookie value
* @param	int		cookie expire time
* @param	string	        cookie path
* @param	string	        cookie domain
* @param	boolean	        cookie secure via SSL
* @param	boolean	        cookie is http only
*
* @return	boolean	        Returns true on success
*/
function do_set_cookie($name, $value, $expires, $path = '', $domain = '', $secure = false, $httponly = false)
{
	if ($value AND $httponly)
	{
		foreach (array("\014", "\013", ",", ";", " ", "\t", "\r", "\n") AS $badcharacter)
		{
			if (mb_strpos($name, $badcharacter) !== false OR mb_strpos($value, $badcharacter) !== false)
			{
				return false;
			}
		}
		$setcookie = "Set-Cookie: $name=" . urlencode($value);
		$setcookie .= ($expires > 0 ? '; expires=' . date('D, d-M-Y H:i:s', $expires) . ' GMT' : '');
		$setcookie .= ($path ? "; path=$path" : '');
		$setcookie .= ($domain ? "; domain=$domain" : '');
		$setcookie .= ($secure ? '; secure' : '');
		$setcookie .= ($httponly ? '; HttpOnly' : '');
		header($setcookie, false);
		return true;
	}
	else
	{
		return setcookie($name, $value, $expires, $path, $domain, $secure);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>