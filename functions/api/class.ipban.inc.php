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
* IP address banning class
*
* @package      iLance\IPBan
* @version	4.0.0.8059
* @author       ILance
*/
class ipban
{
	/**
	* Constructor determines if the visitor's IP address is within the blacklist, if so, it bans that IP/visitor from seeing any pages.
	*
	*/
	function ipban()
	{
		global $ilance, $ilcrumbs, $navcrumb, $phrase, $ilconfig, $ilpage, $show, $maintenancemessage, $login_include;
	
		($apihook = $ilance->api('ipban_constructor_start')) ? eval($apihook) : false;
	
		// #### IP BLOCKER #############################################
		$ipaddress = IPADDRESS;
		if ($this->ip_address_banned($ipaddress))
		{
			die($phrase['_you_have_been_banned_from_the_marketplace']);
		}
		// #### MAINTENANCE MODE #######################################
		if ($ilconfig['maintenance_mode'] AND (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '0'))
		{
			// supress maintenance mode if we're viewing admin cp, logging into site or payment notifications are being called
			if (defined('LOCATION') AND (LOCATION == 'admin' OR LOCATION == 'login' OR LOCATION == 'ipn'))
			{
				return;
			}
			if (ip_address_excluded($ipaddress))
			{
				return;
			}
			if (strrchr($ilconfig['maintenance_excludeurls'], ', '))
			{
				$scripts = explode(', ', $ilconfig['maintenance_excludeurls']);
			}
			else
			{
				$scripts = preg_split('#\s+#', $ilconfig['maintenance_excludeurls'], -1, PREG_SPLIT_NO_EMPTY);
			}
			if (count($scripts) > 0)
			{
				$currentscript = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';
				foreach ($scripts AS $script)
				{
					if (preg_match("/$script/i", $currentscript))
					{
						// found a script that should be excluded while we're still in maintenance mode...
						return;
					}
				}
			}
			$area_title = '{_maintenance_mode_temporarily_unavailable}';
			$page_title = '{_template_metatitle} | ' . SITE_NAME;
			$navcrumb = array ("$ilpage[main]" => '{_maintenance_mode_temporarily_unavailable}');
			print_notice('{_maintenance_mode}', '{' . $ilconfig['maintenance_message'] . '}', HTTP_SERVER . $ilpage['main'], '{_main_menu}');
			exit();
		}
		// #### PUBLIC OR PRIVATE FACING MARKETPLACE ###################
		if ($ilconfig['publicfacing'] == 0 AND (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0) AND (empty($_SESSION['ilancedata']['user']['isadmin']) OR $_SESSION['ilancedata']['user']['isadmin'] == '0'))
		{
			if (defined('LOCATION') AND (LOCATION == 'admin' OR LOCATION == 'login' OR LOCATION == 'ipn' OR LOCATION == 'registration' OR LOCATION == 'ajax' OR LOCATION == 'attachment'))
			{
				return;
			}
			if (ip_address_excluded($ipaddress))
			{
				return;
			}
			$area_title = SITE_NAME;
			$page_title = '{_template_metatitle} | ' . SITE_NAME;
			$navcrumb = array ("$ilpage[main]" => '{_private_marketplace}');
			print_notice('{_private_marketplace}', "{_please} <span class=\"blue\"><a href=\"" . HTTPS_SERVER . $ilpage['login'] . "\">{_sign_in}</a></span> {_or} <span class=\"blue\"><a href=\"" . HTTPS_SERVER . $ilpage['registration'] . "\">{_register}</a></span>. {_this_is_a_private_marketplace}.", HTTPS_SERVER . $ilpage['login'], '{_sign_in}');
			exit();
		}
	}
	
	/**
	* Function to determine if a particular ip address being supplied is banned.  This function takes the ip blacklist from the admin cp into consideration.
	*
	* @param       integer        ip address
	*
	* @return      string         Returns true or false
	*/
	function ip_address_banned($ipaddress = '')
	{
		global $ilconfig;
		$isblocked = false;
		if (!empty($ilconfig['globalfilters_blockips']))
		{
			$addresses = array ();
			$user_ipaddress = $ipaddress . '.';
			if (strrchr($ilconfig['globalfilters_blockips'], ', '))
			{
				$addresses = explode(', ', $ilconfig['globalfilters_blockips']);
			}
			else
			{
				$addresses = preg_split('#\s+#', $ilconfig['globalfilters_blockips'], -1, PREG_SPLIT_NO_EMPTY);
			}
			if (count($addresses) > 0)
			{
				foreach ($addresses AS $banned_ip)
				{
					if (strpos($banned_ip, '*') === false AND $banned_ip{strlen($banned_ip) - 1} != '.')
					{
						$banned_ip .= '.';
					}
					$banned_ip_regex = str_replace('\*', '(.*)', preg_quote($banned_ip, '#'));
					if (preg_match('#^' . $banned_ip_regex . '#U', $user_ipaddress))
					{
						$isblocked = true;
						break;
					}
				}
			}
		}
		return $isblocked;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>