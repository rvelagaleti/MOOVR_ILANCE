<?php
/* ==========================================================================*\
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
\*========================================================================== */
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
if (isset($ilance->GPC['subcmd']))
{
	include_once(DIR_ADMIN . 'distribution_categories_subcmd.php');
}
$area_title = '{_category_manager}';
$page_title = SITE_NAME . ' - {_category_manager}';

($apihook = $ilance->api('admincp_category_management')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=categories', $_SESSION['ilancedata']['user']['slng']);
$prevnext = $prevnext2 = '';
if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
{
	$ilance->GPC['level'] = (!isset($ilance->GPC['level']) OR isset($ilance->GPC['level']) AND $ilance->GPC['level'] <= 0) ? 10 : intval($ilance->GPC['level']);
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilance->GPC['title'] = !empty($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
	$ilance->GPC['visible'] = !empty($ilance->GPC['visible']) ? $ilance->GPC['visible'] : '';
	$ilance->GPC['cid'] = !empty($ilance->GPC['cid']) ? intval($ilance->GPC['cid']) : 0;
	$ilance->GPC['pp'] = !empty($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : 10;
	$level_pulldown = construct_pulldown('level', 'level', array ('1' => '1', '2' => '2', '3' => '3', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'), $ilance->GPC['level'], 'style="font-family: verdana"');
	$visible_pulldown = construct_pulldown('visible', 'visible', array ('0' => '{_inactive}', '1' => '{_active}'), $ilance->GPC['visible'], 'style="font-family: verdana"');
	$pp_pulldown = construct_pulldown('pp', 'pp', array ('10' => '10', '25' => '25', '50' => '50', '100' => '100'), $ilance->GPC['pp'], 'style="font-family: verdana"');
	$cid = ($ilance->GPC['cid'] > 0) ? $ilance->GPC['cid'] : '';
	$page = $ilance->GPC['page'];
	$title = handle_input_keywords($ilance->GPC['title']);
	$counter = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
	$limit = " LIMIT " . ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'] . ", " . $ilance->GPC['pp'];
	$cidsql = '';
	$row_count = 0;
	if (isset($ilance->GPC['cid']) AND $ilance->GPC['cid'] > 0)
	{
		$ids = $ilance->categories->fetch_children_ids($ilance->GPC['cid'], 'service', ' AND level <= \'' . $ilance->GPC['level'] . '\'');
		$cidsql = (!empty($ids)) ? "AND cid IN (" . $ilance->GPC['cid'] . ', ' . $ids . ")" : "AND cid = '" . $ilance->GPC['cid'] . "'";
	}
	$query = "
	SELECT level, cid, parentid, level, rgt, lft, visible, title_" . fetch_site_slng() . " AS title, description_" . fetch_site_slng() . " AS description, seourl_" . fetch_site_slng() . " AS seourl, canpost, insertiongroup, finalvaluegroup, incrementgroup, canpostclassifieds, useproxybid, usereserveprice, useantisnipe, auctioncount, views, budgetgroup, portfolio
	FROM " . DB_PREFIX . "categories
	WHERE cattype = 'service'
		AND level <= '" . intval($ilance->GPC['level']) . "'
		" . ((isset($ilance->GPC['title']) AND !empty($ilance->GPC['title'])) ? "AND title_" . $_SESSION['ilancedata']['user']['slng'] . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['title']) . "%'" : "") . "
		" . $cidsql . "
	ORDER BY lft ASC, title ASC
	";
	$sqlnum = $ilance->db->query($query);
	$count = $ilance->db->num_rows($sqlnum);
	$sql = $ilance->db->query($query . $limit);
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$row = $res;
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		if ($res['level'] == 1)
		{
			$row['title'] = ($res['canpost'] == 0) ? '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page=' . intval($ilance->GPC['page']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '"><strong>' . handle_input_keywords($res['title']) . '</strong></span></a>' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page=' . intval($ilance->GPC['page']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '">' . handle_input_keywords($res['title']) . '</span></a>';
		}
		else if ($res['level'] > 1)
		{
			$row['title'] = ($res['canpost'] == 0) ? str_repeat('<span class="gray">--</span> ', $res['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page=' . intval($ilance->GPC['page']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '"><strong>' . handle_input_keywords($res['title']) . '</strong></span></a>' : str_repeat('<span class="gray">--</span> ', $res['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editservicecat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page=' . intval($ilance->GPC['page']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '">' . handle_input_keywords($res['title']) . '</span></a>';
		}
		$row['insertiongroup'] = ($res['insertiongroup'] == '0' OR $res['insertiongroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['insertiongroup']) . '">' . shorten($res['insertiongroup'], 10) . '</span>';
		$row['finalvaluegroup'] = ($res['finalvaluegroup'] == '0' OR $res['finalvaluegroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['finalvaluegroup']) . '">' . shorten($res['finalvaluegroup'], 10) . '</span>';
		$row['budgetgroup'] = ($res['budgetgroup'] == '0' OR $res['budgetgroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['budgetgroup']) . '">' . shorten($res['budgetgroup'], 10) . '</span>';
		$questions = $ilance->admincp_category->fetch_category_listing_question_count($res['cid'], 'service');
		$row['questions'] = ($questions > 0) ? $questions : '-';
		$bfields = $ilance->bid_fields->print_bid_field_count_in_category($res['cid']);
		$row['bfields'] = ($bfields > 0) ? $bfields : '-';
		$row['views'] = number_format($res['views']);
		$row['isportfolio'] = ($res['portfolio']) ? '<span title="{_this_category_also_acts_as_a_portfolio_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_full.gif" border="0" alt="{_this_category_also_acts_as_a_portfolio_category}" /></span>' : $row['isportfolio'] = '<span title="{_this_category_does_not_act_as_a_portfolio_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'star_empty.gif" border="0" alt="{_this_category_does_not_act_as_a_portfolio_category}" /></span>';
		$row['auctioncount'] = number_format($res['auctioncount']);
		$row['skills'] = $ilance->db->fetch_field(DB_PREFIX . "skills", "rootcid = '" . intval($res['cid']) . "'", "cid", 1);
		if ($row['skills'] > 0)
		{
			$stitle = $ilance->db->fetch_field(DB_PREFIX . "skills", "rootcid = '" . intval($res['cid']) . "' AND level = '1' AND parentid = '0'", "title_" . $_SESSION['ilancedata']['user']['slng'], 1);
			$row['skills'] = '<span title="{_this_category_is_associated_to} ' . handle_input_keywords($stitle) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="" border="0" /></span>';
		}
		else
		{
			$row['skills'] = '-';
		}
		unset($questions, $bfields);
		
		($apihook = $ilance->api('admincp_service_category_loop_end')) ? eval($apihook) : false;
	
		$servicecategories[] = $row;
		$row_count++;
	}
	$urlbit = '&amp;level=' . $ilance->GPC['level'];
	if (!empty($ilance->GPC['title']))
	{
		$urlbit .= '&amp;title=' . $ilance->GPC['title'];
	}
	if (isset($ilance->GPC['visible']) AND !empty($ilance->GPC['visible']))
	{
		$urlbit .= '&amp;visible=' . $ilance->GPC['visible'];
	}
	if (isset($ilance->GPC['cid']) AND !empty($ilance->GPC['cid']))
	{
		$urlbit .= '&amp;cid=' . $ilance->GPC['cid'];
	}
	$prevnext = print_pagnation($count, $ilance->GPC['pp'], $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=categories' . $urlbit);
}
if ($ilconfig['globalauctionsettings_productauctionsenabled'])
{
	$ilance->GPC['level2'] = (!isset($ilance->GPC['level2']) OR isset($ilance->GPC['level2']) AND $ilance->GPC['level2'] <= 0) ? 10 : intval($ilance->GPC['level2']);
	$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
	$ilance->GPC['title2'] = !empty($ilance->GPC['title2']) ? $ilance->GPC['title2'] : '';
	$ilance->GPC['visible2'] = !empty($ilance->GPC['visible2']) ? intval($ilance->GPC['visible2']) : '';
	$ilance->GPC['cid2'] = !empty($ilance->GPC['cid2']) ? intval($ilance->GPC['cid2']) : 0;
	$ilance->GPC['pp2'] = !empty($ilance->GPC['pp2']) ? intval($ilance->GPC['pp2']) : 10;
	$level2_pulldown = construct_pulldown('level2', 'level2', array ('1' => '1', '2' => '2', '3' => '3', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7', '8' => '8', '9' => '9', '10' => '10'), $ilance->GPC['level2'], 'style="font-family: verdana"');
	$visible2_pulldown = construct_pulldown('visible2', 'visible2', array ('0' => '{_inactive}', '1' => '{_active}'), $ilance->GPC['visible2'], 'style="font-family: verdana"');
	$pp2_pulldown = construct_pulldown('pp2', 'pp2', array ('10' => '10', '25' => '25', '50' => '50', '100' => '100'), $ilance->GPC['pp2'], 'style="font-family: verdana"');
	$cid2 = ($ilance->GPC['cid2'] > 0) ? $ilance->GPC['cid2'] : '';
	$page2 = $ilance->GPC['page2'];
	$title2 = handle_input_keywords($ilance->GPC['title2']);
	$counter = ($ilance->GPC['page2'] - 1) * $ilance->GPC['pp2'];
	$limit = " LIMIT " . ($ilance->GPC['page2'] - 1) * $ilance->GPC['pp2'] . ", " . $ilance->GPC['pp2'];
	$cidsql = '';
	$row_count = 0;
	if (isset($ilance->GPC['cid2']) AND $ilance->GPC['cid2'] > 0)
	{
		$ids = $ilance->categories->fetch_children_ids($ilance->GPC['cid2'], 'product', ' AND level <= \'' . $ilance->GPC['level2'] . '\'');
		$cidsql = (!empty($ids)) ? "AND cid IN (" . $ilance->GPC['cid2'] . ', ' . $ids . ")" : "AND cid = '" . $ilance->GPC['cid2'] . "'";
	}
	$query = "
	SELECT level, cid, parentid, level, rgt, lft, visible, title_" . fetch_site_slng() . " AS title, description_" . fetch_site_slng() . " AS description, seourl_" . fetch_site_slng() . " AS seourl, canpost, insertiongroup, finalvaluegroup, incrementgroup, canpostclassifieds, useproxybid, usereserveprice, useantisnipe, auctioncount, views
	FROM " . DB_PREFIX . "categories
	WHERE cattype = 'product'
		AND level <= '" . intval($ilance->GPC['level2']) . "'
		" . ((isset($ilance->GPC['title2']) AND !empty($ilance->GPC['title2'])) ? "AND title_" . $_SESSION['ilancedata']['user']['slng'] . " LIKE '%" . $ilance->db->escape_string($ilance->GPC['title2']) . "%'" : "") . "
		" . $cidsql . "
	ORDER BY lft ASC, title ASC
	";
	$sqlnum = $ilance->db->query($query);
	$count = $ilance->db->num_rows($sqlnum);
	$sql = $ilance->db->query($query . $limit);
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$row = $res;
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		if ($res['level'] == 1)
		{
			$row['title'] = ($res['canpost'] == 0) ? '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '"><strong>' . handle_input_keywords($res['title']) . '</strong></span></a>' : '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '">' . handle_input_keywords($res['title']) . '</span></a>';
		}
		else if ($res['level'] > 1)
		{
			$row['title'] = ($res['canpost'] == 0) ? str_repeat('<span class="gray">--</span> ', $res['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '"><strong>' . handle_input_keywords($res['title']) . '</strong></span></a>' : str_repeat('<span class="gray">--</span> ', $res['level']) . '<a href="' . $ilpage['distribution'] . '?cmd=categories&amp;subcmd=editproductcat&amp;cid=' . $res['cid'] . '&amp;pid=' . $res['parentid'] . '&amp;level=' . $res['level'] . '&amp;lft=' . $res['lft'] . '&amp;rgt=' . $res['rgt'] . '&amp;page2=' . intval($ilance->GPC['page2']) . '" title="' . handle_input_keywords($res['description']) . '"><span style="' . (($res['visible']) ? '' : 'color:red') . '">' . handle_input_keywords($res['title']) . '</span></a>';
		}
		$row['insertiongroup'] = ($res['insertiongroup'] == '0' OR $res['insertiongroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['insertiongroup']) . '">' . shorten($res['insertiongroup'], 10) . '</span>';
		$row['finalvaluegroup'] = ($res['finalvaluegroup'] == '0' OR $res['finalvaluegroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['finalvaluegroup']) . '">' . shorten($res['finalvaluegroup'], 10) . '</span>';
		$row['incrementgroup'] = ($res['incrementgroup'] == '0' OR $res['incrementgroup'] == '') ? '-' : '<span title="' . handle_input_keywords($res['incrementgroup']) . '">' . shorten($res['incrementgroup'], 10) . '</span>';
		$row['useclassifieds'] = ($res['canpostclassifieds'] AND $ilconfig['enableclassifiedtab']) ? '<span title="{_classified_ads_can_be_posted_in_this_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_phone.png" border="0" alt="{_classified_ads_can_be_posted_in_this_category}" /></span>' : '';
		$row['useproxybid'] = ($res['useproxybid']) ? '<span title="{_proxy_bidding_enabled_for_this_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'proxy_gray.gif" border="0" alt="{_proxy_bidding_enabled_for_this_category}" /></span>' : '';
		$row['usereserveprice'] = ($res['usereserveprice']) ? '<span title="{_reserve_price_is_available_for_usage_in_this_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'reserve_gray.gif" border="0" alt="{_reserve_price_is_available_for_usage_in_this_category}" /></span>' : '';
		$row['useantisnipe'] = ($res['useantisnipe']) ? '<span title="{_antibid_sniping_enabled_for_this_category}"><img align="absmiddle" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/antisnipe.gif" border="0" alt="{_antibid_sniping_enabled_for_this_category}" /></span>' : '';
		$questions = $ilance->admincp_category->fetch_category_listing_question_count($res['cid'], 'product');
		$row['questions'] = ($questions > 0) ? $questions : '-';
		$row['views'] = number_format($res['views']);
		$row['auctioncount'] = number_format($res['auctioncount']);
		unset($questions);
		
		($apihook = $ilance->api('admincp_product_category_loop_end')) ? eval($apihook) : false;
	
		$productcategories[] = $row;
		$row_count++;
	}
	$urlbit = '&amp;level2=' . $ilance->GPC['level2'] . '&amp;pp2=' . $ilance->GPC['pp2'];
	if (!empty($ilance->GPC['title2']))
	{
		$urlbit .= '&amp;title2=' . $ilance->GPC['title2'];
	}
	if (isset($ilance->GPC['visible2']) AND !empty($ilance->GPC['visible2']))
	{
		$urlbit .= '&amp;visible2=' . $ilance->GPC['visible2'];
	}
	if (isset($ilance->GPC['cid2']) AND !empty($ilance->GPC['cid2']))
	{
		$urlbit .= '&amp;cid2=' . $ilance->GPC['cid2'];
	}
	$prevnext2 = print_pagnation($count, $ilance->GPC['pp2'], $ilance->GPC['page2'], $counter, $ilpage['distribution'] . '?cmd=categories' . $urlbit, 'page2');
}
// #### head javascript include ################################
$page = isset($ilance->GPC['page']) ? $ilance->GPC['page'] : $ilance->GPC['page2'];
$headinclude .= $ilance->categories_manager->print_category_jump_js('ilform', 'ilform2', 'cid', $page);
$global_categoryoptions = $ilance->admincp->construct_admin_input('globalcategorysettings', $ilpage['distribution'] . '?cmd=categories');
$csv_sep = $ilconfig['globalfilters_bulkuploadcolsep'];
$csv_encap = $ilconfig['globalfilters_bulkuploadcolencap'];
$pprint_array = array ('csv_encap','csv_sep','pp_pulldown', 'visible_pulldown', 'level_pulldown', 'pp2_pulldown', 'visible2_pulldown', 'level2_pulldown', 'js_start', 'duration', 'durationbits', 'hidden', 'disabled', 'saveasdraft', 'bidfilters', 'publicboard', 'escrowfilter', 'bulk_id', 'preview_count', 'response', 'col', 'cid', 'importcategory', 'cid', 'cid2', 'global_categoryoptions', 'title', 'title2', 'page', 'page2', 'pp', 'prevnext', 'prevnext2', 'language_pulldown', 'slng', 'checked_question_cansearch', 'checked_question_active', 'checked_question_required', 'subcategory_pulldown', 'formdefault', 'multiplechoice', 'question', 'description', 'formname', 'sort', 'submit_category_question', 'question_id_hidden', 'question_subcmd', 'question_inputtype_pulldown', 'subcatid', 'subcatname', 'catname', 'service_subcategories', 'product_categories', 'subcmd', 'id', 'submit', 'description', 'name', 'checked_profile_group_active');

($apihook = $ilance->api('admincp_categories_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'categories.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'servicecategories');
$ilance->template->parse_loop('main', 'productcategories');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/* ======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*====================================================================== */
?>