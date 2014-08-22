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
* AdminCP Dashboard class to fetch the information bits on the Admin Control Panel Dashboard
*
* @package      iLance\AdminCP\Dashboard
* @version      4.0.0.8059
* @author       ILance
*/
class admincp_dashboard extends admincp
{
	/**
	* Function to fetch the dashboard template variables for information and overview
	*
	* @return      array
	*/
	function fetch()
	{
		global $ilance, $phrase, $page_title, $area_title, $ilpage, $ilconfig;
		$dashboard = array();
		$version = MYSQL_VERSION;
		$dashboard['mysqlversion'] = $version;
		$dashboard['geoipversion'] = '';
		$dashboard['phptimezone'] = @date_default_timezone_get();
		if ($variables = $ilance->db->query_fetch("SHOW VARIABLES LIKE 'max_allowed_packet'"))
		{
			$dashboard['mysqlpacketsize'] = print_filesize($variables['Value']);
		}
		else
		{
			$dashboard['mysqlpacketsize'] = 'n/a';
		}
		if (preg_match('#(Apache)/([0-9\.]+)\s#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
		{
			$dashboard['webserver'] = "$wsregs[1] v$wsregs[2]";
		}
		else if (preg_match('#Microsoft-IIS/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
		{
			$dashboard['webserver'] = "IIS v$wsregs[1]";
		}
		else if (preg_match('#Zeus/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs))
		{
			$dashboard['webserver'] = "Zeus v$wsregs[1]";
		}
		else if (mb_strtoupper($_SERVER['SERVER_SOFTWARE']) == 'APACHE')
		{
			$dashboard['webserver'] = 'Apache';
		}
		else
		{
			$dashboard['webserver'] = '{_unknown}';
		}
		$info = iif(ini_get('safe_mode') == 1 OR mb_strtolower(ini_get('safe_mode')) == 'on', "<br />Safe Mode Enabled");
		$info .= iif(ini_get('file_uploads') == 0 OR mb_strtolower(ini_get('file_uploads')) == 'off', "<br />File Uploads Disabled");
		if (PHP_OS == 'WINNT')
		{
			$dashboard['servertype'] = 'Windows NT/XP';
		}
		else
		{
			$dashboard['servertype'] = PHP_OS . $info;
		}
		$dashboard['phpmaxpost'] = ini_get('post_max_size');
		$dashboard['phpversion'] = PHP_VERSION;
		$dashboard['phpmaxupload'] = ini_get('upload_max_filesize');
		$memorylimit = ini_get('memory_limit');
		if (mb_strpos($memorylimit, 'M'))
		{
			$memorylimit = (intval($memorylimit) * 1024 * 1024);
		} 
		$dashboard['phpmemorylimit'] = ($memorylimit AND $memorylimit != '-1') ? print_filesize($memorylimit, 2, true) : 'None';
		// members moderation count
		$members = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users AS users
			WHERE users.status = 'moderated'
		");
		$dashboard['members'] = (int)$members['count'];
		// auction moderation count
		$auctions = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "projects AS projects
			WHERE projects.visible = '0'
		");
		$dashboard['auctions'] = (int)$auctions['count'];
		// attachment moderation count
		$attach = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "attachment AS attachment
			WHERE attachment.visible = '0'
		");
		$dashboard['attach'] = (int)$attach['count'];
		// verifications moderation count
		$verifies = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "profile_answers AS profile_answers
			WHERE profile_answers.isverified = '0'
				AND profile_answers.invoiceid > 0
		");
		$dashboard['verifies'] = (int)$verifies['count'];
		// referral payouts pending count
		$referral = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "referral_data AS referral_data
			WHERE referral_data.paidout = '0'
				AND referral_data.invoiceid = '0'
				AND referral_data.postauction = 'yes'
				AND referral_data.awardauction = 'yes'
				AND referral_data.paysubscription = 'yes'
			");
		$dashboard['referral'] = (int)$referral['count'];
		// withdraws pending count
		$withdraws = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices AS invoices
			WHERE invoices.invoiceid > 0
				AND invoices.iswithdraw = '1'
				AND invoices.status = 'scheduled'
		");
		$dashboard['withdraws'] = (int)$withdraws['count'];
		// unpaid invoices count
		$unpaid = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users AS c
			LEFT JOIN " . DB_PREFIX . "invoices AS invoices ON c.user_id = invoices.user_id
			WHERE invoices.status = 'unpaid'
				AND invoices.archive = '0'
				AND invoices.paymethod != 'external'
				AND invoices.invoicetype != 'escrow'
				AND invoices.invoicetype != 'p2b'
				AND invoices.invoicetype != 'credit'
				AND invoices.invoicetype != 'buynow'
				AND invoices.invoiceid > 0
				AND invoices.iswithdraw = '0'
				AND invoices.isdeposit = '0'
				AND invoices.totalamount > 0
				
		");
		$dashboard['unpaid'] = (int)$unpaid['count'];
		// unpaid p2b invoices count
		$unpaid = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices AS invoices
			WHERE invoices.status = 'unpaid'
				AND invoices.paymethod != 'external'
				AND invoices.invoicetype != 'escrow'
				AND invoices.invoicetype = 'p2b'
				AND invoices.invoiceid > 0
				AND invoices.iswithdraw = '0'
				AND invoices.isdeposit = '0'
				AND invoices.totalamount > 0
				
		");
		$dashboard['unpaidp2b'] = (int)$unpaid['count'];
		// unpaid scheduled transactions count
		$scheduled = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "invoices AS invoices
			WHERE (invoices.status = 'scheduled')
				AND invoices.invoiceid > 0
				AND invoices.iswithdraw = '0'
				AND invoices.isdeposit = '0'
				AND invoicetype != 'escrow'
				AND invoicetype != 'p2b'
		");
		$dashboard['scheduled'] = (int)$scheduled['count'];
		$sql_version = $i = $ilance->db->fetch_field(DB_PREFIX . "configuration", "name = 'current_sql_version'", "value", "1");
		$sql_error = $ilance->db->fetch_field(DB_PREFIX . "error_log", "error_id > " . $sql_version, "error_id", "1");
		$dashboard['sql_error'] = empty($sql_error) ? '<div class="blue">{_no}</div>' : '<div class="red">' . $sql_error . '</div>';
		// php information
		if (extension_loaded('gd'))
		{
			$dashboard['gd'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['gd'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (extension_loaded('openssl'))
		{
			$dashboard['openssl'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['openssl'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (extension_loaded('ftp'))
		{
			$dashboard['ftp'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['ftp'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (MULTIBYTE)
		{
			$dashboard['mbstring'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['mbstring'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (ini_get('safe_mode'))
		{
			$dashboard['safemode'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['safemode'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (ini_get('register_globals'))
		{
			$dashboard['registerglobals'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['registerglobals'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		// cache server modules installed
		if (extension_loaded('apc') AND ini_get('apc.enabled'))
		{
			$dashboard['apcenabled'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['apcenabled'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		if (class_exists('Memcache'))
		{
			$dashboard['memcacheenabled'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		}
		else 
		{
			$dashboard['memcacheenabled'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_disabled}" border="0" />';
		}
		$dashboard['filecacheenabled'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="{_enabled}" border="0" />';
		// 24 hour information preview
		$members24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "users 
			WHERE date_added LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newmembers'] = intval($members24h['count']);
		$referrals24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "referral_data
			WHERE date LIKE ('%" . DATETODAY . "%')
		");
		$dashboard['newreferrals'] = intval($referrals24h['count']);
		$service24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_added LIKE ('%" . DATETODAY . "%')
				AND project_state = 'service'
		");
		$dashboard['newserviceauctions'] = intval($service24h['count']);
		$servicebids24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "project_bids
			WHERE date_added LIKE ('%" . DATETODAY . "%')
				AND state = 'service'
		");
		$dashboard['newservicebids'] = intval($servicebids24h['count']);
		$serviceexpired24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_end LIKE ('%" . DATETODAY . "%')
				AND project_state = 'service'
		");
		$dashboard['expiredserviceauctions'] = intval($serviceexpired24h['count']);
		$servicedelisted24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE close_date LIKE ('%" . DATETODAY . "%')
				AND status = 'delisted'
				AND project_state = 'service'
		");
		$dashboard['delistedserviceauctions'] = intval($servicedelisted24h['count']);
		$product24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_added LIKE ('%" . DATETODAY . "%')
				AND project_state = 'product'
		");
		$dashboard['newproductauctions'] = intval($product24h['count']);
		$productbids24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "project_bids
			WHERE date_added LIKE ('%" . DATETODAY . "%')
				AND state = 'product'
		");
		$dashboard['newproductbids'] = intval($productbids24h['count']);
		$productexpired24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE date_end LIKE ('%" . DATETODAY . "%')
				AND project_state = 'product'
		");
		$dashboard['expiredproductauctions'] = intval($productexpired24h['count']);
		$productdelisted24h = $ilance->db->query_fetch("
			SELECT COUNT(*) AS count FROM " . DB_PREFIX . "projects
			WHERE close_date LIKE ('%" . DATETODAY . "%')
				AND status = 'delisted'
				AND project_state = 'product'
		");
		$dashboard['delistedproductauctions'] = intval($productdelisted24h['count']);
		return $dashboard;
	}
	
	function print_keyword_searched_in_categories($keyword = '')
	{
		global $ilance;
		$html = '';
		$sql = $ilance->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "search
			WHERE keyword = '" . $ilance->db->escape_string($keyword) . "'
				AND cid > 0
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$count = 0;
			$html .= '<div style="padding-top:5px;line-height:1.4em" class="smaller litegray">Searched in category: ';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$c = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']);
				if ($c != 'Unknown' AND $c != '{_unknown}' AND !empty($c))
				{
					$html .= $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']) . ', ';
					$count++;
				}
			}
			if ($count > 0)
			{
				$html = substr($html, 0, -2);
				$html .= '</div>';
			}
			else
			{
				return '';
			}
		}
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>