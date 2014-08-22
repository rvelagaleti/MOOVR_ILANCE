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
		'jquery',
		'tabfx',
		'wysiwyg',
		'flashfix',
		'ckeditor'
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
require_once(DIR_CORE . 'functions_wysiwyg.php');
require_once(DIR_CORE . 'functions_email.php');
// #### setup default breadcrumb ###############################################
$navcrumb = array($ilpage['distribution'] => $ilcrumbs["$ilpage[distribution]"]);
if(($v3nav = $ilance->cache->fetch("print_admincp_nav_distribution")) === false)
{
	$v3nav = $ilance->admincp->print_admincp_nav($_SESSION['ilancedata']['user']['slng'], $ilpage['distribution']);
	$ilance->cache->store("print_admincp_nav_distribution", $v3nav);
}
if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
{
	refresh(HTTPS_SERVER_ADMIN . $ilpage['login'] . '?redirect=' . urlencode($ilpage['distribution'] . print_hidden_fields(true, array(), true)), $ilpage['login'] . '?redirect=' . HTTPS_SERVER_ADMIN . urlencode($ilpage['distribution'] . print_hidden_fields(true, array(), true)));
        exit();
}

($apihook = $ilance->api('admincp_distribution_start')) ? eval($apihook) : false;

// #### CATEGORY MANAGEMENT ####################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'categories')
{
	include_once(DIR_ADMIN . 'distribution_categories.php');	
}
// #### CURRENCY MANAGEMENT ############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'currencies')
{
	$area_title = '{_currency_distribution}';
	$page_title = SITE_NAME . ' - {_currency_distribution}';
	
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=currencies', $_SESSION['ilancedata']['user']['slng']);
	
	$pprint_array = array('id');
	
	($apihook = $ilance->api('admincp_currencies_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'currencies.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'escrows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### BULK EMAIL MANAGER #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bulkemail')
{
	include_once(DIR_ADMIN . 'distribution_bulkemail.php');
}
// #### RSS FEEDS MANAGER ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'rssfeeds')
{
	// #### UPDATE RSS FEED SORTING ########################################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-sort')
	{
		foreach ($ilance->GPC['sort'] AS $rssid => $sort)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "rssfeeds
				SET sort = '" . intval($sort) . "'
				WHERE rssid = '" . intval($rssid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
	}
	// #### INSERT NEW RSS FEED RESOURCE ###################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-feed')
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "rssfeeds
			(rssid, rssname, rssurl, sort)
			VALUES (
			NULL,
			'" . $ilance->db->escape_string($ilance->GPC['rssname']) . "',
			'" . $ilance->db->escape_string($ilance->GPC['rssurl']) . "',
			'" . intval($ilance->GPC['sort']) . "')
		", 0, null, __FILE__, __LINE__);
	}
	// #### REMOVE RSS FEED RESOURCE #######################################
	else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-feed')
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "rssfeeds
			WHERE rssid = '" . intval($ilance->GPC['rssid']) . "'
		", 0, null, __FILE__, __LINE__);
	}
	// #### RSS FEEDS MANAGER ##############################################
	$area_title = '{_rss_feeds}';
	$page_title = SITE_NAME . ' - {_rss_feeds_manager}';
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=rssfeeds', $_SESSION['ilancedata']['user']['slng']);
	
	($apihook = $ilance->api('admincp_rssfeed_management')) ? eval($apihook) : false;
		
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "rssfeeds
		ORDER BY sort ASC
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($feeds = $ilance->db->fetch_array($sql))
		{
			if ($show['ADMINCP_TEST_MODE'])
			{
				$feeds['remove'] = '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="" /></div>';
			}
			else
			{
				$feeds['remove'] = '<a href="' . $ilpage['distribution'] . '?cmd=rssfeeds&amp;subcmd=remove-feed&amp;rssid=' . $feeds['rssid'] . '" onclick="return confirm_js(\'' . '{_please_take_a_moment_to_confirm_your_action}' . '\');"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="' . '{_remove}' . '" /></a>';
			}
			$rssfeeds[] = $feeds;
		}
	}
	$pprint_array = array('emailcount','emailmethod_pulldown','subject','message','subscription_pulldown','site_email','numberpaid','numberunpaid','id');
	
	($apihook = $ilance->api('admincp_rssfeeds_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'rssfeeds.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'rssfeeds');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}    
// #### ATTACHMENT MANAGER #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'attachments')
{
	include_once(DIR_ADMIN . 'distribution_attachments.php');
}
// #### PORTFOLIO LISTINGS #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'portfolios')
{
	$area_title = '{_portfolio_management}';
	$page_title = SITE_NAME . ' - {_portfolio_management}';
	$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=portfolios', $_SESSION['ilancedata']['user']['slng']);
	$pprint_array = array('numberpaid','numberunpaid','paidprevnext','unpaidprevnext','id');
	
	($apihook = $ilance->api('admincp_portfolios_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'portfolios.html', 1);
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'escrows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### CREDENTIAL VERIFICATION SETTINGS ###############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'verifications')
{
	include_once(DIR_ADMIN . 'distribution_verifications.php');
}
// #### REFERRAL LISTINGS ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'referrals')
{
	include_once(DIR_ADMIN . 'distribution_referral.php');
} 
// #### BIDS MANAGER ###################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'bids')
{
	include_once(DIR_ADMIN . 'distribution_bids.php');
}
// #### AUCTIONS DISTRIBUTION ##########################################
else
{
	include_once(DIR_ADMIN . 'distribution_auctions.php');
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>