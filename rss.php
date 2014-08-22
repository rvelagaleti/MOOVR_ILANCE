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
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##########################################
$topnavlink = array('rss');

// #### setup script location ##################################################
define('LOCATION', 'rss');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[rss]" => $ilcrumbs["$ilpage[rss]"]);

($apihook = $ilance->api('rss_start')) ? eval($apihook) : false;

// #### SYNDICATION SERVICE AUCTIONS ###########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'syndication' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'serviceauctions')
{
	$area_title = '{_syndicating_service_auctions}';
	$page_title = SITE_NAME . ' - {_syndicating_service_auctions}';
	$ilance->GPC['limit'] = isset($ilance->GPC['limit']) ? intval($ilance->GPC['limit']) : 15;
	$myrss = construct_object('api.myrss');
	$myrss->feedVersion = $ilance->GPC['version'];
	$myrss->channelTitle = SITE_NAME . ' ' . '{_service_auctions}';
	$myrss->channelLink = HTTPS_SERVER;
	$myrss->channelDesc = '{_service_auctions_open_for_bid}';	
	$myrss->imageTitle = SITE_NAME . ' ' . '{_service_auctions}';
	$myrss->imageLink = HTTPS_SERVER;
	$myrss->imageURL = HTTPS_SERVER . $ilconfig['template_logo'];
	if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] == 'all')
	{
		$extraqueryclause = "AND p.cid > 0 AND xml = '1'";
	}
	else if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] != '' AND is_int($ilance->GPC['sid']) AND $ilance->GPC['sid'] != '0')
	{
		$cats = $ilance->db->escape_string($ilance->GPC['sid']);
		$childrenids = $ilance->categories->fetch_children_ids($cats, 'service', "AND xml = '1'");
		$subcategorylist = $cats . (!empty($childrenids) ? ',' . $childrenids : '');
		$extraqueryclause = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
	}
	else
	{
		$extraqueryclause = "AND p.cid > 0 AND xml = '1'";
	}
	$extrawhereclause = "WHERE p.project_state = 'service' AND p.status = 'open' AND p.visible = '1' " . (($ilconfig['globalauctionsettings_payperpost'])
		? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
		: "") . " " . $extraqueryclause;
	$extralimitclause = "LIMIT " . intval($ilance->GPC['limit']);
	$rssData = $myrss->GetRSS(
		DB_PREFIX . 'projects AS p,' . DB_PREFIX . 'categories AS c',
		'project_title', 
		'description', 
		'project_id, date_starts', 
		HTTPS_SERVER . $ilpage['rfp'] . '?id={linkId}',
		$extrawhereclause, 
		$extralimitclause,
		'project_id'
	);
	header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
	$ilance->template->templateregistry['rssData'] = $rssData;
	echo $ilance->template->parse_template_phrases('rssData');
}

// #### SYNDICATE PRODUCT AUCTIONS #############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'syndication' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'productauctions')
{
	$area_title = '{_syndicating_product_auctions}';
	$page_title = SITE_NAME . ' - {_syndicating_product_auctions}';
	$myrss = construct_object('api.myrss');
	$myrss->feedVersion = $ilance->GPC['version'];
	$myrss->channelTitle = SITE_NAME . ' ' . '{_product_auctions}';
	$myrss->channelLink = HTTPS_SERVER;
	$myrss->channelDesc = '{_product_auctions_open_for_bid}';
	$myrss->imageTitle = SITE_NAME . ' ' . '{_product_auctions}';
	$myrss->imageLink = HTTPS_SERVER;
	$myrss->imageURL = HTTPS_SERVER . $ilconfig['template_logo'];
	if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] == 'all')
	{
		$extraqueryclause = "AND p.cid > 0 AND xml = '1'";
	}
	else if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] != '' AND is_int($ilance->GPC['sid']) AND $ilance->GPC['sid'] != '0')
	{
		$cats = $ilance->db->escape_string($ilance->GPC['sid']);
		$childrenids = $ilance->categories->fetch_children_ids($cats, 'product', "AND xml = '1'");
		$subcategorylist = $cats . (!empty($childrenids) ? ',' . $childrenids : '');
		$extraqueryclause = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
	}
	else
	{
		$extraqueryclause = "AND p.cid > 0 AND xml = '1'";
	}
	$extrawhereclause = "WHERE p.project_state = 'product' AND p.status = 'open' AND p.visible = '1' " . (($ilconfig['globalauctionsettings_payperpost'])
		? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))"
		: "") . " " . $extraqueryclause;
	$extralimitclause = "LIMIT " . intval($ilance->GPC['limit']);
	$rssData = $myrss->GetRSS(
		DB_PREFIX . 'projects AS p,' . DB_PREFIX . 'categories AS c',
		'project_title',
		'description',
		'project_id, date_starts',
		HTTPS_SERVER . $ilpage['merch'] . '?id={linkId}',
		$extrawhereclause,
		$extralimitclause,
		'project_id'
	);
	header('Content-type: application/xml; charset="' . $ilconfig['template_charset'] . '"');
	$ilance->template->templateregistry['rssData'] = $rssData;
	echo $ilance->template->parse_template_phrases('rssData');
}
else
{
	$area_title = '{_syndication_generation_menu}';
	$page_title = SITE_NAME . ' - {_syndication_generation_menu}';
	$show['generated_feed'] = false;
	$syndicate_url = '';
	$cid = isset($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'generate')
	{
		$show['generated_feed'] = true;
		$syndicate_url = HTTPS_SERVER . $ilpage['rss'] . '?cmd=syndication&subcmd=' . $ilance->GPC['subcmd'] . '&sid=' . $cid . '&version=' . $ilance->GPC['version'] . '&limit=' . intval($ilance->GPC['limit']);
	}
	$pprint_array = array('cid','syndicate_url');
	
	($apihook = $ilance->api('rss_main')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'main_rss.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
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