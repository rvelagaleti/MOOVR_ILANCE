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
if (!defined('LOCATION') OR defined('LOCATION') != 'rfp')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_moderation_tools}';
$page_title = SITE_NAME . ' - {_moderation_tools}';
$navcrumb = array();
$navcrumb[""] = '{_moderation_tools}';
// #### hold return url ########################################
$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
$show['movecategory'] = $show['sendemail'] = false;
// #### flag for delist ########################################
if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'delist' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'subtract', "admin delisting multiple listings from search results: subtracting increment count category id $res[cid]");
					}

					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET status = 'delisted',
						close_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### physically remove listings
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'physically_remove' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					if ($res['status'] == 'open')
					{
						$ilance->categories->build_category_count($res['cid'], 'subtract', "admin removing multiple listings from inline moderation tools: subtracting increment count category id $res[cid]");
					}
					$ilance->common_listing->physically_remove_listing(intval($value));
				}
				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for featured homepage ####################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'featured' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured = '1',
						featured_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for unfeatured homepage ####################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unfeatured' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured = '0',
						featured_date = '0000-00-00 00:00:00'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for featured in search results (top placement) ####################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'featured_search' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured_searchresults = '1',
						featured_date = '" . DATETIME24H . "'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for unfeatured homepage ####################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unfeatured_search' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET featured_searchresults = '0',
						featured_date = '0000-00-00 00:00:00'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for highlight background #################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'highlite' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET highlite = '1'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for unhighlight background #################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unhighlite' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET highlite = '0'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for bold title ###########################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'bold' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET bold = '1'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for unbold title ###########################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'unbold' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET bold = '0'
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for relist title #########################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'relist' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->auction_expiry->process_auction_relister(intval($value), true);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for time extend ##########################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'extend' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET date_end = DATE_ADD(date_end, INTERVAL 1 DAY)
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for time retract #########################
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'deextend' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET date_end = DATE_SUB(date_end, INTERVAL 1 DAY)
						WHERE project_id = '" . intval($value) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for sending bulk message to listing owners
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'email' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = $selecteditems = $returnurlback = '';
	$show['sendemail'] = true;
	$emailsduplicateprevention = $uids = array();
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . $value . '" />';
				$selecteditems .= "$res[project_title] (#$res[project_id]) : {_category_id} (#$res[cid])\n";
				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					if (!in_array($res['user_id'], $emailsduplicateprevention))
					{
						$uids[] = $res;
						$emailsduplicateprevention[] = $res['user_id'];
					}
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			if (!isset($ilance->GPC['message']) OR empty($ilance->GPC['message']))
			{
				print_notice('{_email_message_cannot_be_blank}', '{_please_compose_a_message_to_sent_to_the_owners_of_selected_listings}', 'javascript:history.back(-1)', '{_try_again}');
				exit();
			}
			if (!empty($uids) AND is_array($uids))
			{
				
				foreach ($uids AS $user_id)
				{
					$ilance->email->mail = fetch_user('email', $user_id);
					$ilance->email->slng = fetch_user_slng($user_id);
					$ilance->email->get('notice_to_owners_of_selected_listings');		
					$ilance->email->set(array(
						'{{message}}' => handle_input_keywords($ilance->GPC['message']),
						'{{selecteditems}}' => $selecteditems,
						'{{site_name}}' => SITE_NAME,
						'{{http_server}}' => HTTP_SERVER));
					$ilance->email->send();
				}
			}
			refresh($returnurlback);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
// #### flag listings for sending bulk message to listing owners
else if (isset($ilance->GPC['action']) AND $ilance->GPC['action'] == 'movecategory' AND isset($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$hiddenfields = '';
	$cid = 0;
	$show['movecategory'] = true;
	$mode = isset($ilance->GPC['mode']) ? $ilance->GPC['mode'] : 'service';
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
	{
		if (!isset($ilance->GPC['cid']) OR isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] <= 0)
		{
			print_notice('{_nothing_to_do}', '{_please_select_the_new_category_you_wish_to_move_selected_listings_into}', 'javascript:history.back(-1)', '{_try_again}');
			exit();
		}
	}
	if (!empty($ilance->GPC['project_id']) AND is_array($ilance->GPC['project_id']))
	{
		foreach ($ilance->GPC['project_id'] AS $value)
		{
			$sql = $ilance->db->query("
				SELECT user_id, cid, status, project_state, project_title, project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($value) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$hiddenfields .= '<input type="hidden" name="project_id[]" value="' . intval($value) . '" />';

				if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
				{
					// #### move category
					$ilance->categories->move_listing_category_from_to(intval($value), $res['cid'], $ilance->GPC['cid'], $res['project_state'], $res['status'], $res['status']);

					// #### setup new category questions
					if (isset($ilance->GPC['custom']) AND is_array($ilance->GPC['custom']))
					{
						$ilance->auction_post->process_custom_questions($ilance->GPC['custom'], intval($value), $res['project_state']);
					}
				}

				$res['value'] = $value;
				$returnurlback = urldecode($returnurl);
				$results[] = $res;
			}
		}
		if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'process')
		{
			refresh('', $ilance->GPC['returnurl']);
			exit();
		}
	}
	else
	{
		print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
		exit();
	}
}
else
{
	print_notice('{_nothing_to_do}', '{_you_did_not_select_any_listings_from_the_previous_page_please_retry}', 'javascript:history.back(-1)', '{_try_again}');
	exit();
}
$pprint_array = array('mode','cid','returnurlback','returnurl');

($apihook = $ilance->api('main_mycp_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'search_moderation.html');
$ilance->template->parse_loop('main', 'results');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
		
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>