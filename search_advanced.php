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
if (!defined('LOCATION') OR defined('LOCATION') != 'search')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
$area_title = '{_search}<div class="smaller">{_advanced_search}</div>';
$page_title = SITE_NAME . ' - {_search_the_marketplace}';
$search_bidrange_pulldown_service = print_bid_range_pulldown('', 'bidrange', 'servicebidrange', 'pulldown');
$search_bidrange_pulldown_product = print_bid_range_pulldown('', 'bidrange', 'productbidrange', 'pulldown');
$search_awardrange_pulldown = print_award_range_pulldown('', 'projectrange', 'projectrange', 'pulldown');
$search_ratingrange_pulldown = print_rating_range_pulldown('', 'rating', 'rating');
if (isset($ilance->GPC['country']))
{
	$country = $ilance->GPC['country'];
}
else
{
	$country = !empty($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : 'all';
}
$search_country_pulldown_experts = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountry', true);
$availableto_pulldown_experts = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryto', true);
$locatedin_pulldown_experts = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertcountryin', true);
$region_pulldown_experts = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'expertregionin', 'pulldown', $onchange = true, '3');
$search_country_pulldown_service = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountry', true);
$availableto_pulldown_service = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryto', true);
$locatedin_pulldown_service = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'servicecountryin', true);
$region_pulldown_service = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'serviceregionin', 'pulldown', $onchange = true, '1');
$search_country_pulldown_product = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountry', true);
$availableto_pulldown_product = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryto', true);
$locatedin_pulldown_product = print_active_countries_pulldown('country', $country, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'productcountryin', true);
$region_pulldown_product = print_regions('region', '', $_SESSION['ilancedata']['user']['slng'], 'productregionin', 'pulldown', $onchange = true, '2');
if (isset($ilance->GPC['radiuscountry']) AND $ilance->GPC['radiuscountry'] > 0)
{
	$radiuscountry = $ilance->GPC['radiuscountry'];
}
else
{
	$radiuscountry = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all';
}
if (isset($ilance->GPC['q']))
{
	if (!empty($ilance->GPC['q']))
	{
		$q = ilance_htmlentities($ilance->GPC['q']);
	}
}

($apihook = $ilance->api('search_menu_start')) ? eval($apihook) : false;

$searcherror = $ilance->language->construct_phrase('{_we_require_that_you_wait_x_seconds_between_searches_please_try_again_in_x_seconds}', array($searchwait, $searchwaitleft));

// #### SAVING SEARCH OPTIONS ##################################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'saveoptions')
{
	if (is_array($ilance->GPC))
	{
		$options = array();
		foreach ($ilance->GPC AS $search => $option)
		{
			if (!in_array($search, array('defaultupdate','membersupdate','tab','search','cmd','returnurl','redirect')))
			{
				$options["$search"] = $option;
			}
		}
		if (empty($options['latestfeedback']))
		{
			$options['latestfeedback'] = 'false';
		}
		if (empty($options['username']))
		{
			$options['username'] = 'false';
		}
		if (empty($options['icons']))
		{
			$options['icons'] = 'false';
		}
		if (empty($options['currencyconvert']))
		{
			$options['currencyconvert'] = 'false';
		}
		if (empty($options['hidelisted']))
		{
			$options['hidelisted'] = 'false';
		}
		if (empty($options['hideverbose']))
		{
			$options['hideverbose'] = 'false';
		}
		if (empty($options['listinglocation']))
		{
			$options['listinglocation'] = 'false';
		}

		($apihook = $ilance->api('search_saveoptions_submit_end')) ? eval($apihook) : false;

		$searchoptions = serialize($options);
		$uid = (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) ? $_SESSION['ilancedata']['user']['userid'] : 0;
		update_default_searchoptions($uid, $searchoptions);
		if (isset($ilance->GPC['defaultupdate']) AND $ilance->GPC['defaultupdate'] == 'true')
		{
			update_default_searchoptions_guests($searchoptions);
		}
		if (isset($ilance->GPC['membersupdate']) AND $ilance->GPC['membersupdate'] == 'true')
		{
			update_default_searchoptions_users($searchoptions);
		}
		if (!empty($ilance->GPC['returnurl']))
		{
			refresh($ilance->GPC['returnurl']);
			exit();
		}
		refresh($ilpage['search'] . '?tab=3');
		exit();
	}
	else
	{
		refresh($ilpage['login']);
		exit();
	}
}
$show['widescreen'] = $show['leftnav'] = false;
$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
$sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'experts');
$returnurl = !empty($ilance->GPC['returnurl']) ? handle_input_keywords($ilance->GPC['returnurl']) : '';
$currency_symbol_left = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
$currency_symbol_right = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_right'];
// #### advanced search skills selector for experts ############################
$skills_selection = $ilance->categories_skills->print_skills_columns($_SESSION['ilancedata']['user']['slng'], 1, false);
$headinclude .= '<script type="text/javascript">
<!-- 
function print_profile_filters()
{
	var ajaxRequest;
	try
	{
		ajaxRequest = new XMLHttpRequest();
	} 
	catch (e)
	{
		try
		{
			ajaxRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} 
		catch (e) 
		{
			try
			{
				ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
			} 
			catch (e)
			{
				return false;
			}
		}
	}
	ajaxRequest.onreadystatechange = function()
	{
		if (ajaxRequest.readyState == 4)
		{
			var ajaxDisplay = fetch_js_object(\'profile_filters_text\');
			ajaxDisplay.innerHTML = ajaxRequest.responseText;
		}
	}
        var selected_cid = fetch_js_object(\'cid_list\').options[fetch_js_object(\'cid_list\').selectedIndex].value;
	var queryString = "&cid=" + selected_cid + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open(\'GET\', AJAXURL + \'?do=profilefilters\' + queryString, true);
	ajaxRequest.send(null); 
}
//-->
</script>';
$values = array('-1' => '{_any_date}', '1' => '1 {_hour}', '2' => '2 {_hours}', '3' => '3 {_hours}', '4' => '4 {_hours}', '5' => '5 {_hours}', '6' => '12 {_hours}', '7' => '24 {_hours}', '8' => '2 {_days}', '9' => '3 {_days}', '10' => '4 {_days}', '11' => '5 {_days}', '12' => '6 {_days}', '13' => '7 {_days}', '14' => '2 {_weeks}', '15' => '1 {_month}');
$productendstart_filter_pulldown = construct_pulldown('productendstart_filter', 'endstart_filter', $values, '-1', 'class="select"');
$serviceendstart_filter_pulldown = construct_pulldown('serviceendstart_filter', 'endstart_filter', $values, '-1', 'class="select"');
$values = array('' => '-', '5' => '5', '10' => '10', '20' => '20', '50' => '50', '100' => '100', '250' => '250', '500' => '500', '1000' => '1000', '2000' => '2000', '5000' => '5000', '10000' => '10000');
$expertradius_pulldown = construct_pulldown('expertradius', 'radius', $values, '', 'class="select-75" title="{_radius}"');
$serviceradius_pulldown = construct_pulldown('serviceradius', 'radius', $values, '', 'class="select-75" title="{_radius}"');
$productradius_pulldown = construct_pulldown('productradius', 'radius', $values, '', 'class="select-75" title="{_radius}"');

($apihook = $ilance->api('search_start')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'search.html');
$ilance->template->parse_if_blocks('main');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$pprint_array = array('productradius_pulldown','serviceradius_pulldown','expertradius_pulldown','serviceendstart_filter_pulldown','productendstart_filter_pulldown','currency_symbol_right','currency_symbol_left','colsperrow','region_pulldown_experts','region_pulldown_service','region_pulldown_product','locatedin_pulldown_experts','locatedin_pulldown_service','locatedin_pulldown_product','availableto_pulldown_experts','availableto_pulldown_service','availableto_pulldown_product','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_radius_country_pulldown_service','search_radius_country_pulldown_product','search_country_pulldown_service','search_country_pulldown_product','search_country_pulldown_experts','search_radius_country_pulldown_experts','provider_category_selection','profilebidfilters','skills_selection','returnurl','js_start','perpage','sortpulldown','sortpulldown2','rb_list_gallery','rb_list_list','rb_showtimeas_flash','rb_showtimeas_static','cb_username','cb_latestfeedback','cb_online','cb_description','cb_icons','cb_currencyconvert','cb_hidelisted','cb_proxybit','cb_listinglocation','cb_hideverbose','serviceavailable','serviceselected','productavailable','productselected','expertavailable','expertselected','keywords','searcherror','fromprice','toprice','budgetfilter','tab','search_offersrange_pulldown','search_wantedsincerange_pulldown','wantads_category_selection','search_country_pulldown2','search_soldrange_pulldown','search_itemsrange_pulldown','search_opensincerange_pulldown','stores_category_selection','product_category_selection','service_category_selection','search_serviceauctions_img','search_serviceauctions_collapse','search_productauctions_img','search_productauctions_collapse','search_experts_collapse','search_experts_img','pfp_category_left','rfp_category_left','search_country_pulldown','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown');

($apihook = $ilance->api('search_start_template')) ? eval($apihook) : false;

$ilance->template->pprint('main', $pprint_array);

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>