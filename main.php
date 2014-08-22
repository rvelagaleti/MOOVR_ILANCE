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
		'jquery'
	),
	'footer' => array(
		'v4',
		'autocomplete',
		'tooltip',
		'jquery_slides',
		'homepage',
		'cron'
	)
);
// #### setup script location ##################################################
define('LOCATION', 'main');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[main]" => $ilcrumbs["$ilpage[main]"]);
// #### MEMBERS CONTROL PANEL DASHBOARD ########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'cp')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'dashboard'
	);
	$show['widescreen'] = false;
	$show['leftnav'] = true;
	if (!isset($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] <= 0)
	{
		refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['main'] . print_hidden_fields(true, array(), true)));
		exit();
	}
	$area_title = '{_member_control_panel}';
	$page_title = '{_member_control_panel} | ' . SITE_NAME;
	if (TEMPLATE_NEWUI == '')
	{ // req'd for new my account menu
		$headinclude .= '<link id="v41" rel="stylesheet" href="' . DIR_FUNCT_NAME . '/' . DIR_CSS_NAME . '/v4.css" type="text/css" media="screen" />';
	}
	$navcrumb = array();
	$navcrumb[""] = '{_my_account}';
	$notices = $breminder = $sreminder = '';

	($apihook = $ilance->api('main_cp')) ? eval($apihook) : false;

	$ilance->GPC['period'] = isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : '7';
	$temp = $ilance->mycp->fetch_unread_messages($_SESSION['ilancedata']['user']['userid']);
	$messagesactivity = $temp[0]; // 4.0
	$notices .= $temp[1]; // 4.1
	unset($temp);

	// #### feedback activity ######################################
	$temp = $ilance->mycp->feedback_activity($_SESSION['ilancedata']['user']['userid']);
	$feedbackactivity = $temp[1];
	$notices .= $temp[2];
	unset($temp);

	// #### invited to jobs activity ###########################
	$temp = $ilance->mycp->invitation_activity($_SESSION['ilancedata']['user']['userid']);
	$invitationactivity = $temp[0];
	$notices .= $temp[1];
	unset($temp);

	// #### buying reminders panel #################################
	if (($breminder = $ilance->cache->fetch('breminder')) === false)
	{
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$temp = $ilance->mycp->bids_award_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'service', $ilance->GPC['period']);
			$breminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$temp = $ilance->mycp->escrow_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'service', $ilance->GPC['period']);
				$breminder .= $temp[0]; // 4.0
				$notices .= $temp[1]; // 4.1
				unset($temp);
			}
			$temp = $ilance->mycp->unpaid_p2b_activity($_SESSION['ilancedata']['user']['userid'], 'buying', $ilance->GPC['period']);
			$breminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
			$temp = $ilance->mycp->bids_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'service', $ilance->GPC['period']);
			$breminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$temp = $ilance->mycp->bids_award_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'product', $ilance->GPC['period']);
			$breminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$temp = $ilance->mycp->escrow_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'product', $ilance->GPC['period']);
				$breminder .= $temp[0]; // 4.0
				$notices .= $temp[1]; // 4.1
				unset($temp);
			}
			$temp = $ilance->mycp->bids_activity($_SESSION['ilancedata']['user']['userid'], 'buying', 'product', $ilance->GPC['period']);
			$breminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
		}
		$ilance->cache->store('breminder', $breminder);
	}
	($apihook = $ilance->api('main_mycp_buying_reminders')) ? eval($apihook) : false;

	// #### selling reminders panel ################################
	if (($sreminder = $ilance->cache->fetch('sreminder')) === false)
	{
		if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$temp = $ilance->mycp->bids_award_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'service', $ilance->GPC['period']);
			$sreminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; //4.1
			unset($temp);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$temp = $ilance->mycp->escrow_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'service', $ilance->GPC['period']);
				$sreminder .= $temp[0]; // 4.0
				$notices .= $temp[1]; // 4.1
				unset($temp);
			}
			$temp = $ilance->mycp->unpaid_p2b_activity($_SESSION['ilancedata']['user']['userid'], 'selling', $ilance->GPC['period']);
			$sreminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
			$temp = $ilance->mycp->bids_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'service', $ilance->GPC['period']);
			$sreminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
		}
		if ($ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$temp = $ilance->mycp->bids_award_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'product', $ilance->GPC['period']);
			$sreminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
			if ($ilconfig['escrowsystem_enabled'])
			{
				$temp = $ilance->mycp->escrow_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'product', $ilance->GPC['period']);
				$sreminder .= $temp[0]; // 4.0
				$notices .= $temp[1]; // 4.1
				unset($temp);
			}
			$temp = $ilance->mycp->bids_activity($_SESSION['ilancedata']['user']['userid'], 'selling', 'product', $ilance->GPC['period']);
			$sreminder .= $temp[0]; // 4.0
			$notices .= $temp[1]; // 4.1
			unset($temp);
		}
		$ilance->cache->store('sreminder', $sreminder);
	}
	$sellingreminders = (!empty($sreminder)) ? mb_substr($sreminder, 0, -50) : '{_no_activity_found}';
	$buyingreminders = (!empty($breminder)) ? mb_substr($breminder, 0, -50) : '{_no_activity_found}';
	$feedbackactivity = (!empty($feedbackactivity)) ? mb_substr($feedbackactivity, 0, -50) : '{_no_feedback_activity_found}';
	// #### bids left today ########################################
	$bidtotal = $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidlimitperday');
	$bidsleft = max(0, ($bidtotal - fetch_bidcount_today($_SESSION['ilancedata']['user']['userid'])));
	$datereset = print_date($ilance->datetimes->fetch_datetime_from_timestamp($ilance->db->fetch_field(DB_PREFIX . "cron", "varname = 'dailyrfp'", 'nextrun')), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
	// #### referral activity ######################################
	$referalactivity = $ilance->mycp->referral_activity($_SESSION['ilancedata']['user']['userid']);
	$ridlink = HTTP_SERVER . $ilpage['main'] . '?rid=' . $_SESSION['ilancedata']['user']['ridcode'];
	
	($apihook = $ilance->api('main_mycp_selling_reminders')) ? eval($apihook) : false;

	unset($sreminder, $breminder);
	$pprint_array = array('notices','buyingreminders','sellingreminders','feedbackactivity','bidsleft','messagesactivity','datereset','invitationactivity','referalactivity','ridlink','user','template_logo');

	($apihook = $ilance->api('main_mycp_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'main_mycp' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### BUYING SERVICES LANDING PAGE : CHOOSE CATEGORY ##########################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'buying')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_buying'
	);
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		$seobuy = print_seo_url($ilconfig['productcatmapidentifier']);
		$seourl = HTTP_SERVER . $seobuy;
		header('Location: ' . $seourl);
		exit();
	}
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seobuy = print_seo_url($ilconfig['servicecatmapidentifier']);
		$seourl = HTTP_SERVER . $seobuy;
		header('Location: ' . $seourl);
		exit();
	}
	$area_title = '{_buying_products_and_services}';
	$page_title = '{_award} | ' . SITE_NAME;
	$navcrumb = array();
	$navcrumb[""] = '{_award}';
	$inviteduserlist = '';
	$show['invitedusers'] = false;
	if (!empty($_SESSION['ilancedata']['tmp']['invitations']))
	{
		if (is_serialized($_SESSION['ilancedata']['tmp']['invitations']))
		{
			$invitedusers = unserialize($_SESSION['ilancedata']['tmp']['invitations']);
			$invitedcount = count($invitedusers);
			if ($invitedcount > 0 AND is_array($invitedusers))
			{
				$show['invitedusers'] = true;
				foreach ($invitedusers AS $userid)
				{
					$inviteduserlist .= '<span class="blue">' . fetch_user('username', $userid) . '</span>, ';
				}
				if (!empty($inviteduserlist))
				{
					$inviteduserlist = '<div>' . mb_substr($inviteduserlist, 0, -2) . '</div>';
				}
			}
		}
	}
	$pprint_array = array();

	($apihook = $ilance->api('main_buying')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'buying.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'wantads');
	$ilance->template->parse_loop('main', array('featuredserviceauctions', 'featuredproductauctions'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### SELLING SERVICES LANDING PAGE ##########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'selling')
{
	// #### define top header nav ##########################################
	$topnavlink = array('main_selling');
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		$seosell = print_seo_url($ilconfig['servicecatmapidentifier']);
		$seourl = HTTP_SERVER . $seosell;
		header('Location: ' . $seourl);
		exit();
	}
	$seosell = print_seo_url('Sell');
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seourl = HTTP_SERVER . $seosell;
		header('Location: ' . $seourl);
		exit();
	}
	$area_title = '{_sell_products_and_services}';
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		$page_title = '{_sell} | ' . SITE_NAME;
		$navcrumb = array();
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$navcrumb[HTTP_SERVER . "sell"] = '{_sell}';
		}
		else
		{
			$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=selling"] = '{_sell}';
		}
		$charityid = (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'] AND isset($ilance->GPC['charityid']) AND $ilance->GPC['charityid'] > 0) ? intval($ilance->GPC['charityid']) : '';
		$pprint_array = array('charityid');

		($apihook = $ilance->api('main_selling')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'selling_product.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$page_title = '{_sell} {_services} | ' . SITE_NAME;
		$navcrumb[""] = '{_services}';
		$categoryresults =  '';
		$categorycache = $ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$categoryresults = $ilance->categories_parser->print_subcategory_columns(4, 'service', 1, $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalfilters_enablecategorycount'], 1, 'font-weight: bold;', 'font-weight: normal;', $ilconfig['globalauctionsettings_catmapdepth']);
		unset($categorycache);
		$pprint_array = array('categoryresults');

		($apihook = $ilance->api('main_selling')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'selling_service.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### CATEGORIES LANDING #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'categories')
{
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		$seourl = print_seo_url($ilconfig['servicecatmapidentifier']);
	}
	else if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		$seourl = print_seo_url($ilconfig['productcatmapidentifier']);
	}
	$seourl = HTTP_SERVER . $seourl;
	header('Location: ' . $seourl);
	exit();
}
// #### CATEGORIES LANDING #####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'listings')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_listings'
	);
	$seolistings = print_seo_url($ilconfig['listingsidentifier']);
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seourl = HTTP_SERVER . $seolistings;
		header('Location: ' . $seourl);
		exit();
	}
	$show['widescreen'] = false;
	$area_title = '{_listings}<div class="smaller">{_overview}</div>';
	$page_title = '{_find_what_youre_looking_for} | ' . SITE_NAME;
	$navcrumb = array();
	$navcrumb[""] = '{_listings}';
	// #### SEO related ############################################################
	$seocategories = print_seo_url($ilconfig['categoryidentifier']);
	$seoproductcategories = print_seo_url($ilconfig['productcatmapidentifier']);
	$seoservicecategories = print_seo_url($ilconfig['servicecatmapidentifier']);
	$seolistings = print_seo_url($ilconfig['listingsidentifier']);
	$seoexperts = print_seo_url($ilconfig['expertslistingidentifier']);
	// top skills
	$sql = $ilance->db->query("
		SELECT a.cid, COUNT(*) AS count, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, s.keywords
		FROM  " . DB_PREFIX . "skills_answers a
		LEFT JOIN " . DB_PREFIX . "skills s ON (a.cid = s.cid)
		GROUP BY cid
		ORDER BY count DESC 
		LIMIT 10
	");
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$res['skillurl'] = HTTP_SERVER . $ilpage['search'] . '?mode=experts&amp;sort=102&amp;sid[' . $res['cid'] . ']=true';
		$skills[] = $res;
	}
	$pprint_array = array('seoexperts','seoservicecategories','seoproductcategories','seolistings','seocategories','trendcategories','popularcategories','categoryresults','wantads_category_pulldown');

	($apihook = $ilance->api('main_listings')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'main_listings.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('featuredserviceauctions', 'featuredproductauctions', 'skills'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### CLASSIFIEDS LANDING ####################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'classifieds')
{
	if ($ilconfig['enableclassifiedtab'] == 0)
	{
		$navcrumb = array("classifieds" => '{_classifieds}');
		print_notice('{_disabled}', '{_were_sorry_this_feature_is_currently_disabled}', HTTP_SERVER, '{_main_menu}');
		exit();
	}
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_classifieds'
	);
	$seoclassifieds = print_seo_url('Classifieds');
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seourl = HTTP_SERVER . $seoclassifieds;
		header('Location: ' . $seourl);
		exit();
	}
	$show['widescreen'] = false;
	$area_title = '{_classifieds}<div class="smaller">{_overview}</div>';
	$page_title = SITE_NAME . ' ' . $ilconfig['registrationdisplay_defaultcity'] . ' {_classifieds}: Free Local Classifieds for ' .  $ilconfig['registrationdisplay_defaultcity'] . ', ' . $ilconfig['registrationdisplay_defaultstate'];
	$metadescription = 'Visit ' . SITE_NAME . ' for ' . $ilconfig['registrationdisplay_defaultcountry'] . '\'s most visited classifieds site. Categories include antiques, autos, pets, for rent, homes, jobs and more.';
	$metakeywords = SITE_NAME . ', Ads, Cars, Personals, Apartments, Antiques, Furniture, Services, Housing, Events, Appliances';
	$navcrumb = array();
	$navcrumb[""] = '{_classifieds}';
	$country = $ilconfig['registrationdisplay_defaultcountry'];
	$state = $ilconfig['registrationdisplay_defaultstate'];
	$cities = $ilance->common_location->print_cities($country, $state, 5);
	if (isset($ilance->GPC['city']) AND !empty($ilance->GPC['city']))
	{
		$page_title = SITE_NAME . ' ' . handle_input_keywords($ilance->GPC['city'])  . ' {_classifieds}: Free Local Classifieds for ' .  handle_input_keywords($ilance->GPC['city']);
		$metadescription = 'Visit ' . SITE_NAME . ' for ' . handle_input_keywords($ilance->GPC['city']) . '\'s most visited classifieds site. Categories include antiques, autos, pets, for rent, homes, jobs and more.';
	}
	$pprint_array = array('state','cities','prevnext');

	($apihook = $ilance->api('main_listings')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'main_classifieds.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('featuredserviceauctions', 'featuredproductauctions'), false);
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### XML FEED RESOURCES LANDING PAGE ########################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'resources')
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_resources'
	);
	$area_title = '{_resources_menu}';
	$page_title = '{_resources_menu} | ' . SITE_NAME;
	$navcrumb = array();
	$navcrumb[""] = '{_resources}';
	$feedoptions = '';
	$rssfeeds = $ilance->db->query("
                SELECT rssid, rssname, rssurl, sort
                FROM " . DB_PREFIX . "rssfeeds
                ORDER BY sort
        ", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($rssfeeds) == 0)
	{
		$feedoptions = '<option value="0">{_no_feeds_currently_exist}</option>';
	}
	else
	{
		while ($feed = $ilance->db->fetch_array($rssfeeds, DB_ASSOC))
		{
			$feedoptions .= '<option value="' . $feed['rssid'] . '">' . stripslashes($feed['rssname']) . '</option>';
		}
	}
	if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$feed = $ilance->db->query_fetch("
                        SELECT rssid, rssname, rssurl, sort
                        FROM " . DB_PREFIX . "rssfeeds
                        WHERE rssid = '" . intval($ilance->GPC['id']) . "'
                ", 0, null, __FILE__, __LINE__);

		$rss = $feed['rssurl'];
		$rssname = $feed['rssname'];
		$headline_style = $description_style = $tag = $title = $description = $link = $image = $code2 = '';
		$show_detail = $insideitem = $insideimage = false;
		$max = 10;
		$count = 0;
		include_once(DIR_CORE . 'functions_feed.php');
		construct_feed($rss, true, 'news', 'news', $max);
	}
	else
	{
		$code2 = '{_please_select_a_live_rss_feed}';
	}
	$pprint_array = array('rssname','code2','feedoptions');
	
	($apihook = $ilance->api('main_resources')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'main_resources.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### HIDE ADMINCP NOTICE UNDER BREADCRUMB ###################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'hideacpnag')
{
	if (!empty($_SESSION['ilancedata']['user']['isadmin']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
		set_cookie('hideacpnag', 'true');
		$url = $ilpage['main'];
		$phr = '{_home}';
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			$url = urldecode($ilance->GPC['returnurl']);
			$phr = '{_back}';
		}
		print_notice('{_admincp_nag_notice_removed}', '{_you_have_removed_the_admincp_clientside_nag_notice}', $url, $phr);
		exit();
	}
}
// #### DYNAMIC TEMPLATE PARSER ################################################
if (isset($ilance->GPC['cmd']) AND !empty($ilance->GPC['cmd']))
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main_cms'
	);
	$accepted_db = array('terms', 'about', 'news', 'privacy');
	$accepted_files = array('contact');
	$cmd = $ilance->GPC['cmd'];

	// custom hook in the case you would like to expand your array above via array_merge()
	($apihook = $ilance->api('main_external_template')) ? eval($apihook) : false;

	if (in_array($cmd, $accepted_db))
	{
		$custom_page_title = '{_' . $cmd . '}';
		$field = $cmd;
		$sql = $ilance->db->query("
			SELECT " . $ilance->db->escape_string($field) . " AS content
			FROM " . DB_PREFIX . "cms
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$content = $res['content'];
			$pprint_array = array('custom_page_title', 'content');
			$area_title = '{_' . handle_input_keywords($ilance->GPC['cmd']) . '}';
			$page_title = '{_' . handle_input_keywords($ilance->GPC['cmd']) . '} | ' . SITE_NAME;
			$navcrumb = array();
			$navcrumb[""] = $area_title;
			$ilance->template->fetch('main', 'main_custom_page.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice('{_invalid_page_request}', '{_were_sorry_the_page_you_requested_could_not_be_found}', $ilpage['main'], '{_main_menu}');
			exit();
		}
	}
	else if (in_array($cmd, $accepted_files))
	{
		$area_title = '{_' . handle_input_keywords($ilance->GPC['cmd']) . '}';
		$page_title = '{_' . handle_input_keywords($ilance->GPC['cmd']) . '} | ' . SITE_NAME;
		$navcrumb = array();
		$navcrumb[""] = $area_title;
		$ilance->template->fetch('main', 'main_' . mb_strtolower($ilance->GPC['cmd']) . '.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', array());
		exit();
	}
	else
	{
		print_notice('{_invalid_page_request}', '{_were_sorry_the_page_you_requested_could_not_be_found}', $ilpage['main'], '{_main_menu}');
		exit();
	}
}
// #### MAIN MENU LANDING PAGE #################################################
else
{
	// #### define top header nav ##########################################
	$topnavlink = array(
		'main'
	);
	$show['widescreen'] = $show['recentkeywords'] = false;
	$show['nobreadcrumb'] = $show['categorynav'] = true;
	$area_title = '{_main_menu}';
	$page_title =  '{_template_metatitle} | ' . SITE_NAME;
	$metadescription = '{_template_metadescription}';
	$metakeywords = '{_template_metakeywords}';
	$navcrumb = array();
	$navcrumb[""] = '{_marketplace}';
	$featuredserviceauctions = $featuredproductauctions = $latestserviceauctions = $latestproductauctions = $servicesendingsoon = $productsendingsoon = $recentreviewedserviceauctions = $recentreviewedproductauctions = $itemsfromsellerwatchlist = $stats = $homepageheros = array();
	$recentkeywords = $tagcloud = $hpaurl = $heroimagemaps = '';
	if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
	{
		if (($featuredserviceauctions = $ilance->cache->fetch('featuredserviceauctions')) === false)
		{
			$featuredserviceauctions = $ilance->auction_listing->fetch_featured_auctions('service', 20, 1, 0, '', true);
			$ilance->cache->store('featuredserviceauctions', $featuredserviceauctions);
		}
		if (($latestserviceauctions = $ilance->cache->fetch('latestserviceauctions')) === false)
		{
			$latestserviceauctions = $ilance->auction_listing->fetch_latest_auctions('service', 20, 1, 0, '', true);
			$ilance->cache->store('latestserviceauctions', $latestserviceauctions);
		}
		if (($servicesendingsoon = $ilance->cache->fetch('servicesendingsoon')) === false)
		{
			$servicesendingsoon = $ilance->auction_listing->fetch_ending_soon_auctions('service', 20, 1, 0, '', true);
			$ilance->cache->store('servicesendingsoon', $servicesendingsoon);
		}
		if (($recentreviewedserviceauctions = $ilance->cache->fetch('recentreviewedserviceauctions')) === false)
		{
			$recentreviewedserviceauctions = $ilance->auction_listing->fetch_recently_viewed_auctions('service', 10, 1, 0, '', true);
			$ilance->cache->store('recentreviewedserviceauctions', $recentreviewedserviceauctions);
		}
		if ($ilconfig['trend_tab'])
		{
			if (($stats = $ilance->cache->fetch('stats')) === false)
			{
				$stats = $ilance->auction->fetch_stats_overview();
				$ilance->cache->store('stats', $stats);
			}
			$jobcount = number_format($stats['jobcount']);
			$expertcount = number_format($stats['expertcount']);
			$expertsearch = ($expertcount == 0) ? number_format(0, 1) : number_format((($stats['expertsearch'] / $expertcount)), 1);
			$expertsrevenue = $ilance->currency->format($stats['expertsrevenue']);
			
			($apihook = $ilance->api('main_stats_end')) ? eval($apihook) : false;
		}
		if (($jobcats = $ilance->cache->fetch('jobcats')) === false)
		{
			$jobcats = $ilance->categories_parser_v4->print_root_categories_ul(15, 5, 'service');
			$ilance->cache->store('jobcats', $jobcats);
		}
		if (($topskills = $ilance->cache->fetch('topskills')) === false)
		{
			$topskills = $ilance->categories_skills->print_root_categories_ul(75, 15);
			$ilance->cache->store('topskills', $topskills);
		}
	}
	if ($ilconfig['globalauctionsettings_productauctionsenabled'])
	{
		if (($featuredproductauctions = $ilance->cache->fetch('featuredproductauctions')) === false)
		{
			$featuredproductauctions = $ilance->auction_listing->fetch_featured_auctions('product', 24, 1, 0, '', true);
			$ilance->cache->store('featuredproductauctions', $featuredproductauctions);
		}
		if (($latestproductauctions = $ilance->cache->fetch('latestproductauctions')) === false)
		{
			$latestproductauctions = $ilance->auction_listing->fetch_latest_auctions('product', 24, 1, 0, '', true);
			$ilance->cache->store('latestproductauctions', $latestproductauctions);
			
		}
		if (($productsendingsoon = $ilance->cache->fetch('productsendingsoon')) === false)
		{
			$productsendingsoon = $ilance->auction_listing->fetch_ending_soon_auctions('product', 24, 1, 0, '', true);
			$ilance->cache->store('productsendingsoon', $productsendingsoon);
		}
		if (($itemsfromsellerwatchlist = $ilance->cache->fetch('itemsfromsellerwatchlist')) === false)
		{
			$itemsfromsellerwatchlist = $ilance->auction_listing->fetch_items_from_seller_watchlist('product', 24, 1, 0, '', true);
			$ilance->cache->store('itemsfromsellerwatchlist', $itemsfromsellerwatchlist);
		}
		if (($homepageheros = $ilance->cache->fetch('homepageheros')) === false)
		{
			$homepageheros = $ilance->auction_listing->fetch_heros('homepage');
			$ilance->cache->store('homepageheros', $homepageheros);
		}
		if (count($homepageheros) > 0)
		{
			foreach ($homepageheros AS $key => $value)
			{
				if (!empty($value['imagemap']))
				{
					$heroimagemaps .= str_replace('{id}', $value['id'], $value['imagemap']);
				}
			}
		}
		$hpadurl = $ilconfig['globalserversettings_homepageadurl'];
	}
	$userid = ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0);
	if (($recentkeywords = $ilance->cache->fetch('recentkeywords_' . $userid . '_' . str_replace('.', '_', IPADDRESS))) === false)
	{ // recent keywords entered by user or guest
		$recentkeywords = $ilance->auction->fetch_recently_used_keywords($userid, 1, 8, 0, '', 'product');
		$ilance->cache->store('recentkeywords_' . $userid . '_' . str_replace('.', '_', IPADDRESS), $recentkeywords);
	}
	if (count($recentkeywords) >= 1)
	{
		$show['recentkeywords'] = true;
	}
	if (($tagcloud = $ilance->cache->fetch('tagcloud')) === false)
	{ // tag cloud of recently searched terms by everyone
		$tagcloud = $ilance->cloudtags->print_tag_cloud();
		$ilance->cache->store('tagcloud', $tagcloud);
	}
	unset($userid, $stats);
	$pprint_array = array('hpadurl','scheduledcount','jobcount','expertcount','expertsrevenue','tagcloud','heroimagemaps','topskills','jobcats');

	($apihook = $ilance->api('main_start')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'main' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('homepageheros','featuredserviceauctions','featuredproductauctions','latestserviceauctions','latestproductauctions','productsendingsoon','servicesendingsoon','recentreviewedproductauctions','recentreviewedproductauctions2','recentreviewedserviceauctions','recentkeywords','itemsfromsellerwatchlist'), false);
	
	($apihook = $ilance->api('main_end')) ? eval($apihook) : false;

	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>