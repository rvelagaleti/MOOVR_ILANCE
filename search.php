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
		'search',
		'tabfx',
		'jquery',
		'modal',
		'yahoo-jar'
	),
	'footer' => array(
		'v4',
		'autocomplete',
		'tooltip',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
	'main_listings'
);

// #### setup script location ##################################################
define('LOCATION', 'search');

// #### require backend ########################################################
require_once('./functions/config.php');
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_search_prefs.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[search]" => $ilcrumbs["$ilpage[search]"]);
$tab = (isset($ilance->GPC['tab'])) ? intval($ilance->GPC['tab']) : '0';
$sortpulldown = print_sort_pulldown();

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

// #### SEARCH HELP : FULLTEXT BOOLEAN INFO ####################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'help')
{
	$area_title = '{_search}<div class="smaller">{_help}</div>';
	$page_title = SITE_NAME . ' - {_search} | {_help}';
	$ilance->template->fetch('main', 'search_help.html');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->pprint('main', array());
	exit();
}
// #### SEARCH OPTIONS #########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'options')
{
	$js_start = print_searchoptions_js();
	$perpage = print_perpage_searchoption();
	$colsperrow = print_colsperrow_searchoption();
	$cb_username = print_checkbox_status('username');
	$cb_latestfeedback = print_checkbox_status('latestfeedback');
	$cb_icons = print_checkbox_status('icons');
	$cb_currencyconvert = print_checkbox_status('currencyconvert');
	$cb_hidelisted = print_checkbox_status('hidelisted');
	$cb_hideverbose = print_checkbox_status('hideverbose');
	$cb_listinglocation = print_checkbox_status('listinglocation');
	$rb_showtimeas_static = print_time_static_radiobox_status();
	$rb_list_gallery = print_list_gallery_radiobox_status();
	$rb_list_list = print_list_list_radiobox_status();
	$area_title = '{_search}<div class="smaller">{_options}</div>';
	$page_title = SITE_NAME . ' - {_search} | {_display_options}';
	$returnurl = !empty($ilance->GPC['returnurl']) ? handle_input_keywords($ilance->GPC['returnurl']) : '';
	$pprint_array = array('serviceselectedhidden','expertselectedhidden','productselectedhidden','returnurl','colsperrow','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_icons','cb_currencyconvert','cb_hidelisted','cb_listinglocation','cb_hideverbose','serviceavailable','serviceselected','productavailable','productselected','expertavailable','expertselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','wantads_category_selection','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','stores_category_selection','product_category_selection','service_category_selection','search_serviceauctions_img','search_serviceauctions_collapse','search_productauctions_img','search_productauctions_collapse','search_experts_collapse','search_experts_img','pfp_category_left','rfp_category_left','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown');
	$ilance->template->fetch('main', 'search_options.html');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'promotion')
{
	$area_title = '{_search_promotion}<div class="smaller">{_overview}</div>';
	$page_title = '{_search_promotion} | ' . SITE_NAME;
	$ilance->template->jsinclude = array('header' => array('functions','jquery'), 'footer' => array('v4','tooltip','autocomplete','cron'));
	$ilance->template->fetch('main', 'search_promotion.html');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->pprint('main', array());
	exit();
}
// #### TIMER TO PREVENT SEARCH FLOODING #######################################
$show['searcherror'] = $searchwaitleft = 0;
$searchwait = $ilconfig['searchflooddelay'];
if (!empty($ilance->GPC['mode']))
{
	if ($ilconfig['searchfloodprotect'] AND isset($ilance->GPC['q']) AND $ilance->GPC['q'] != '')
	{
		if (empty($_SESSION['ilancedata']['user']['searchexpiry']))
		{
			// start timer
			$_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
		}
		else
		{
			if (($timeexpired = TIMESTAMPNOW - $_SESSION['ilancedata']['user']['searchexpiry']) < $searchwait AND $searchwait != 0)
			{
				$show['searcherror'] = 1;
				$searchwaitleft = ($searchwait - $timeexpired);
			}
			else
			{
				// restart timer
				$_SESSION['ilancedata']['user']['searchexpiry'] = TIMESTAMPNOW;
			}
		}
	}

	($apihook = $ilance->api('search_mode_condition_end')) ? eval($apihook) : false;
}
// #### SEARCH ENGINE HANDLER ##################################################
$sqlquery = array();
$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
// #### print multiple selection category menu #################################
$service_category_selection = $product_category_selection = $provider_category_selection = $search_category_pulldown = $profilebidfilters = '';
if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] AND (isset($ilance->GPC['mode']) AND ($ilance->GPC['mode'] == 'service' OR $ilance->GPC['mode'] == 'experts') OR empty($ilance->GPC['mode'])))
{
	set_cookie('inlineservice', '', false);
	set_cookie('inlineprovider', '', false);
	$service_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'service', 'cid', $_SESSION['ilancedata']['user']['slng']);
	$provider_category_selection = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'levelmultisearch', 'cid', 0, $_SESSION['ilancedata']['user']['slng'], 1, '', 0, 1, 0, '350px', 0, 0, 0, false, false, $ilance->categories->cats, true);
	if (isset($ilance->GPC['mode']) AND ($ilance->GPC['mode'] == 'service' OR $ilance->GPC['mode'] == 'experts'))
	{
		$search_category_pulldown = $service_category_selection;
	}
	$profilebidfilters = '<div id="profile_filters_text">' . $ilance->auction_post->print_profile_bid_filters($cid, 'input', 'service') . '</div>';
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product' OR empty($ilance->GPC['mode'])))
{
	set_cookie('inlineproduct', '', false);
	$product_category_selection = $ilance->categories_pulldown->print_root_category_pulldown($cid, 'product', 'cid', $_SESSION['ilancedata']['user']['slng']);
	if (isset($ilance->GPC['mode']) AND $ilance->GPC['mode'] == 'product')
	{
		$search_category_pulldown = $product_category_selection;
	}
}
if (!empty($ilance->GPC['mode']) AND $show['searcherror'] == 0)
{
	// #### PREPARE DEFAULT URLS ###########################################
	$scriptpage = HTTP_SERVER . $ilpage['search'] . print_hidden_fields(true, array('do','cmd','page','budget','searchid','list','ld','rl'), true, '', '', true, false);
	$searchid = isset($ilance->GPC['searchid']) ? intval($ilance->GPC['searchid']) : '';
	$list = isset($ilance->GPC['list']) ? $ilance->GPC['list'] : '';
	$pageurl = rewrite_url(PAGEURL, 'searchid=' . $searchid);
	$pageurl = rewrite_url($pageurl, 'list=' . $list);
	$pageurl = rewrite_url($pageurl, '', array('ld','rl'));
	$php_self = ($ilconfig['globalauctionsettings_seourls']) ? $pageurl : $scriptpage;
	$php_self_urlencoded = ($ilconfig['globalauctionsettings_seourls']) ? urlencode($pageurl) : urlencode($php_self);
	define('PHP_SELF', $php_self);
	unset($pageurl);
	$legend = print_legend('legend_tab_search_results');
	if (isset($ilance->GPC['classifieds']) AND $ilance->GPC['classifieds'])
	{
		$topnavlink = array(
			'main_classifieds'
		);
	}
	switch ($ilance->GPC['mode'])
	{
		// #### SEARCHING FOR PROJECT ID ###############################
		case 'rfpid':
		{
			if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q']))
			{
				header('Location: ' . $ilpage['search'] . '?tab=0');
				exit();
			}
			header('Location: ' . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['q']));
			exit();
		}
		// #### SEARCHING FOR ITEM ID ##################################
		case 'itemid':
		{
			if (empty($ilance->GPC['q']) OR !isset($ilance->GPC['q']))
			{
				header('Location: ' . $ilpage['search'] . '?tab=1');
				exit();
			}
			header('Location: ' . $ilpage['merch'] . '?id=' . intval($ilance->GPC['q']));
			exit();
		}
		// #### SEARCHING SERVICE AND PRODUCT AUCTIONS #################
		case 'service':
		case 'product':
		{
			if (isset($ilance->GPC['rfpid']) AND $ilance->GPC['rfpid'] > 0)
			{
				header('Location: ' . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['rfpid']));
				exit();
			}
			if (isset($ilance->GPC['itemid']) AND $ilance->GPC['itemid'] > 0)
			{
				header('Location: ' . $ilpage['merch'] . '?id=' . intval($ilance->GPC['itemid']));
				exit();
			}
			include_once(DIR_SERVER_ROOT . 'search_listings' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . strtolower(TEMPLATE_NEWUI_MODE) : '') . '.php');
			break;
		}
		// #### SEARCHING SERVICE PROVIDERS ############################
		case 'experts':
		{
			include_once(DIR_SERVER_ROOT . 'search_experts.php');
			break;
		}
		/*case 'portfolios':
		{
			include_once(DIR_SERVER_ROOT . 'portfolio.php');
			break;
		}*/
	}
}
// #### ADVANCED SEARCH MENU ###################################################
include_once(DIR_SERVER_ROOT . 'search_advanced.php');
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>