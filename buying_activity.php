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
$topnavlink = array(
        'mycp',
        'buying'
);
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
$navcrumb["$ilpage[buying]?cmd=management"] = '{_buying_activity}';
$show['widescreen'] = false;
// #### LISTING PERIOD #################################################
require_once(DIR_CORE . 'functions_search.php');
require_once(DIR_CORE . 'functions_tabs.php');
// #### service auction buying activity ########################
if ($show['service_buying_activity'])
{
	include_once(DIR_SERVER_ROOT . 'buying_activity_service.php');
}
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'purchases')
{
	// #### product auction buying/bidding activity ########################
	if ($show['product_buying_activity'])
	{
		include_once(DIR_SERVER_ROOT . 'buying_activity_product_purchases.php');
	}
}
else
{
	// #### product auction buying/bidding activity ########################
	if ($show['product_buying_activity'])
	{
		include_once(DIR_SERVER_ROOT . 'buying_activity_product.php');
	}
}

$pprint_array = array('keyw2','keyw','pp2_pulldown','displayorder2_pulldown','orderby2_pulldown','period2_pulldown','pp_pulldown','period_pulldown','displayorder_pulldown','pics2_pulldown','orderby_pulldown','service_header_block_title','block_header_title','block_header_title2','php_self2','sub','bidsub','servicetabs','producttabs','activebids','awardedbids','archivedbids','invitedbids','expiredbids','retractedbids','productescrow','buynowproductescrow','activerfps','draftrfps','archivedrfps','delistedrfps','pendingrfps','serviceescrow','highbidder','highbidderid','highest','php_self','searchquery','p_id','rfpescrow','rfpvisible','countdelisted','prevnext','prevnext2','input_style');
$ilance->template->fetch('main', 'buying_activity' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '' AND IPADDRESS == '74.14.233.174') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', 'service_buying_activity', true);
$ilance->template->parse_loop('main', 'product_bidding_activity', true);
$ilance->template->parse_loop('main', 'product_purchases_activity', true);
if (!isset($service_buying_activity))
{
        $service_buying_activity = array();
}
@reset($service_buying_activity);
while ($i = @each($service_buying_activity))
{
        $ilance->template->parse_loop('main', 'invitationlist' . $i['value']['project_id'], true);
        $ilance->template->parse_loop('main', 'service_buying_bidding_activity' . $i['value']['project_id'], true);
}

($apihook = $ilance->api('buying_activity_parse_loop_end')) ? eval($apihook) : false;

$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>