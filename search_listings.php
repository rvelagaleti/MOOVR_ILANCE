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

$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
// search mode logic
$show['mode_product'] = $show['mode_providers'] = $show['mode_service'] = false;
if ($ilance->GPC['mode'] == 'service')
{
	$navcrumb[""] = '{_services}';
	$show['mode_service'] = true;
	$project_state = 'service';
	$sqlquery['projectstate'] = "AND (p.project_state = 'service')";
}
else if ($ilance->GPC['mode'] == 'product')
{
	$navcrumb[""] = '{_products}';
	$show['mode_product'] = true;
	$project_state = 'product';
	$sqlquery['projectstate'] = "AND (p.project_state = 'product')";
}
// #### ensure auctions shown in result have not yet expired..
$sqlquery['timestamp'] = "AND (UNIX_TIMESTAMP(p.date_end) > UNIX_TIMESTAMP('" . DATETIME24H . "'))";
$sqlquery['projectstatus'] = "AND (p.status = 'open')";
// here we should take the user to "all category listings" if he chose a "mode" but didn't select a category and keyword.
if (!empty($ilance->GPC['mode']) AND empty($ilance->GPC['searchuser']) AND empty($ilance->GPC['state']) AND empty($ilance->GPC['country']) AND empty($ilance->GPC['sort']) AND (empty($ilance->GPC['q']) AND (empty($cid) OR !empty($cid) AND $cid == 0)))
{
	switch ($ilance->GPC['mode'])
	{
		case 'service':
		{
			$reurl = '';
			$reurl = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['servicecatmapidentifier']) : HTTP_SERVER . $ilpage['rfp'] . '?cmd=listings';
			break;
		}
		case 'product':
		{
			$reurl = '';
			$reurl = ($ilconfig['globalauctionsettings_seourls']) ? HTTP_SERVER . print_seo_url($ilconfig['productcatmapidentifier']) : HTTP_SERVER . $ilpage['merch'] . '?cmd=listings';
			break;
		}
	}
	header("Location: " . $reurl . "");
	exit();
}
// init verbose search engine text, favorite searches text, etc
$text = $showtext = $favtext = $metatitle = '';
// #### keywords entered by user #######################
$keyword_text = (!empty($ilance->GPC['q'])) ? un_htmlspecialchars($ilance->GPC['q']) : '';

// #### BEGIN SEARCH SQL QUERY #########################
$sqlquery['relevance'] = '';
$sqlquery['groupby'] = "GROUP BY p.project_id";
$sqlquery['orderby'] = "ORDER BY p.featured_searchresults = '1' DESC, p.date_end ASC";
$sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * fetch_perpage()) . ',' . fetch_perpage();
// #### accepted display sorting orders ################
$acceptedsort = array('01','02','11','12','21','22','31','41','42','51','52','61','62','71','72','81','82','91','92','101','102','111','112');
// be sure user entered keyword before sorting by relevance
if (isset($ilance->GPC['q']) AND !empty($ilance->GPC['q']))
{
	$acceptedsort[] = '123';
	$acceptedsort[] = '124';
}
// #### build our core sql search pattern fields and store them in an array for later usage
$sqlquery['fields'] = "p.featured, p.featured_searchresults, p.reserve, p.bold, p.highlite, p.buynow_qty, p.buynow, p.buynow_price, p.buynow_purchases, p.currentprice, p.project_id, p.cid, p.description, p.date_starts, p.date_added, p.date_end, p.user_id, p.visible, p.views, p.project_title, p.additional_info, p.bids, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.startprice, p.filtered_auctiontype, p.filtered_budgetid, p.filter_budget, p.filter_escrow, p.filter_gateway, p.donation, p.charityid, p.donationpercentage, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.currencyid, p.countryid AS country, p.country AS auction_country, p.city, p.state, p.zipcode, p.description_videourl, p.paymethodoptions, p.buynow_qty_lot, p.items_in_lot, u.rating, u.score, u.city AS user_city, u.state AS user_state, u.zip_code AS user_zipcode, u.username";
$sqlquery['from'] = "FROM " . DB_PREFIX . "projects AS p";
$sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "users u ON (p.user_id = u.user_id) ";
// #### left join for shipping logic ###################
if ($show['mode_product'])
{
	$sqlquery['fields'] .= ", s.ship_method, s.ship_handlingtime, s.ship_handlingfee, ";
	for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
	{
		$sqlquery['fields'] .= "sd.ship_options_$i, sd.ship_service_$i, sd.ship_fee_$i, sd.freeshipping_$i, ";
	}
	$sqlquery['fields'] = substr($sqlquery['fields'], 0, -2);
	$sqlquery['leftjoin'] .= "LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (p.project_id = s.project_id) ";
	$sqlquery['leftjoin'] .= "LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON (p.project_id = sd.project_id) ";
	$sqlquery['leftjoin'] .= "LEFT JOIN " . DB_PREFIX . "attachment_color ac ON (p.project_id = ac.project_id) ";
}

// #### hook below is useful for changing any specifics from the above
($apihook = $ilance->api('search_query_fields')) ? eval($apihook) : false;

// #### categories #####################################
$sqlquery['categories'] = '';
if (!empty($cid))
{
	$subcategorylist = $subcatname = '';
	if ($ilance->categories->visible($cid) == 0)
	{
		$area_title = '{_category_not_available}';
		$page_title = SITE_NAME . ' - {_category_not_available}';
		print_notice('{_invalid_category}', '{_this_category_is_currently_unavailable_please_choose_a_different_category}', $ilpage['search'], '{_search}');
		exit();
	}
	if ($cid > 0)
	{
		$metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
		$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid, true, true);
		$removeurl = rewrite_url($scriptpage, 'cid=' . urlencode($cid));
		$cmode = $ilance->GPC['mode'] . 'cat';
		$categoryname = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$subcatname .= ', <span class="black">' . $categoryname . '</span>';
		$childrenids = $ilance->categories->fetch_children_ids($cid, $ilance->GPC['mode']);
		$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
		if (!empty($subcatname))
		{
			handle_search_verbose('{_in} ' . mb_substr($subcatname, 1) . '<!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_categories}: <strong>' . mb_substr($subcatname, 1) . '</strong>, ');
		}
		$sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		$ilance->categories->add_category_viewcount($cid);
	}
}
unset($cmode, $subcatname, $childrenids, $subcategorylist);
// #### popular keyword search handler #################
if (!empty($keyword_text) AND !isset($ilance->GPC['nkw']))
{
	// build's a usable database of recent search keywords
	handle_search_keywords($keyword_text, $ilance->GPC['mode'], $cid);
}
// #### search options: is user hiding their own results?
$sqlquery['hidequery'] = '';
if ($selected['hidelisted'] == 'true' AND !empty($_SESSION['ilancedata']['user']['userid']))
{
	$sqlquery['hidequery'] = "AND (u.user_id != '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
	handle_search_verbose('<span class="black">{_excluding_results_that_are_listed_by_me}</span>, ');
	handle_search_verbose_save('{_filter}: <strong>{_excluding_results_that_are_listed_by_me_uppercase}</strong>, ');
}
// #### filter search method (titles only or everything)
$titlesonly = isset($ilance->GPC['titlesonly']) ? intval($ilance->GPC['titlesonly']) : '-1';
if ($titlesonly == '-1')
{
	//handle_search_verbose_save('{_filter}: <strong>{_search_entire_auctions}</strong>, ');
}
else
{
	$removeurl = rewrite_url($scriptpage, 'titlesonly=' . $ilance->GPC['titlesonly']);
}

// #### search exact username? #########################
$sqlquery['userquery'] = $clear_searchuser = $clear_searchuser_url = '';
if (isset($ilance->GPC['searchuser']) AND !empty($ilance->GPC['searchuser']))
{
	$searchuser = $ilance->GPC['searchuser'];
	$searchuser = $ilance->common->xss_clean($searchuser);
	$removeurl = rewrite_url($scriptpage, 'searchuser=' . urlencode($searchuser));
	$clear_searchuser = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	$clear_searchuser_url = $removeurl;
	$favexactphrase = '';
	if (isset($ilance->GPC['exactname']) AND $ilance->GPC['exactname'])
	{
		$removeurl = rewrite_url($removeurl, 'exactname=' . $ilance->GPC['exactname']);
		$clear_searchuser = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
		$clear_searchuser_url = $removeurl;
		$exactphrase = '{_exactly_match}';
		$favexactphrase = '( <strong>{_exact_matches}</strong> )';
		$sqlquery['userquery'] = "AND (u.username = '" . $ilance->db->escape_string($searchuser) . "')";
	}
	else
	{
		$exactphrase = '{_match}';
		$sqlquery['userquery'] = "AND (u.username LIKE '%" . $ilance->db->escape_string($searchuser) . "%')";
	}
	handle_search_verbose('<span class="black">{_searching_all_members_that} ' . $exactphrase . ' ' . $searchuser . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_username}: <strong>' . $searchuser . '</strong> ' . $favexactphrase . ', ');
}

// #### search via auction type ########################
$show['allbuyingformats'] = false;
$sqlquery['projectdetails'] = $buyingformats = '';
if (empty($ilance->GPC['buynow']) AND empty($ilance->GPC['auction']) AND empty($ilance->GPC['scheduled']) AND empty($ilance->GPC['inviteonly']))
{
	$show['allbuyingformats'] = true;
}
else
{
	$removeurl = $scriptpage;
	// #### include auctions #######################
	if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
	{
		$extraquery = '';
		
		($apihook = $ilance->api('search_filter_auction')) ? eval($apihook) : false;
		
		$removeurl = rewrite_url($scriptpage, 'auction=' . $ilance->GPC['auction']);
		if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
		{
			$sqlquery['projectdetails'] .= "AND (p.filtered_auctiontype = 'regular' OR p.filtered_auctiontype = 'fixed') $extraquery ";
		}
		else
		{
			$sqlquery['projectdetails'] .= "AND (p.filtered_auctiontype = 'regular') $extraquery ";
		}
		$buyingformats .= ($ilance->GPC['mode'] == 'product') ? '{_auction}, ' : '{_reverse_auction}, ';
	}
	// #### filter auctions with buynow available ##########
	if (isset($ilance->GPC['buynow']) AND $ilance->GPC['buynow'])
	{
		$removeurl = rewrite_url($scriptpage, 'buynow=' . $ilance->GPC['buynow']);
		if (isset($ilance->GPC['auction']) AND $ilance->GPC['auction'])
		{
			$sqlquery['projectdetails'] .= "AND (p.buynow = '1' AND (p.filtered_auctiontype = 'fixed' OR p.filtered_auctiontype = 'regular')) ";
		}
		else
		{
			$sqlquery['projectdetails'] .= "AND (p.buynow = '1' OR p.filtered_auctiontype = 'fixed') ";
		}
		$buyingformats .= '{_fixed_price}, ';
	}
	// #### include invite only auctions ###########
	if (isset($ilance->GPC['inviteonly']) AND $ilance->GPC['inviteonly'])
	{
		$removeurl = rewrite_url($scriptpage, 'inviteonly=' . $ilance->GPC['inviteonly']);
		$sqlquery['projectdetails'] .= "AND (p.project_details = 'invite_only') ";
		$buyingformats .= '{_invite_only}, ';
	}
	// #### include upcoming scheduled events ######
	if (isset($ilance->GPC['scheduled']) AND $ilance->GPC['scheduled'])
	{
		$removeurl = rewrite_url($scriptpage, 'scheduled=' . $ilance->GPC['scheduled']);
		$sqlquery['projectdetails'] .= "AND (p.project_details = 'realtime') ";
		$buyingformats .= '{_scheduled}, ';
	}
	// #### include classified ads #################
	if (isset($ilance->GPC['classified']) AND $ilance->GPC['classified'])
	{
		$removeurl = rewrite_url($scriptpage, 'classified=' . $ilance->GPC['classified']);
		$sqlquery['projectdetails'] .= "AND (p.filtered_auctiontype = 'classified') ";
		$buyingformats .= '{_classified_ads}, ';
	}
	if (!empty($buyingformats))
	{
		$buyingformats = substr($buyingformats, 0, -2);
		if ($ilance->GPC['mode'] == 'product')
		{
			handle_search_verbose('<span class="black"><!--<span class="gray">{_buying_formats}:</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_buying_formats}: <strong>' . $buyingformats . '</strong>, ');
		}
		else
		{
			handle_search_verbose('<span class="black"><!--<span class="gray">{_hiring_formats}:</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_hiring_formats}: <strong>' . $buyingformats . '</strong>, ');
		}
	}
}

// #### buying formats (auction type) selector #########
// also generates variable: $clear_listtype_url for usage below
$leftnav_buyingformats = print_buying_formats();
$clear_listtype = ($show['allbuyingformats']) ? '' : '<a href="' . $clear_listtype_url . '" rel="nofollow">{_clear}</a>';
unset($buynow, $clear_listtype_url);
// #### handle keyword input ###########################
$sqlquery['keywords'] = $keyword_formatted = '';
$keywords_array = array();
// #### build our sql state based on keyword input #####
if (isset($keyword_text) AND !empty($keyword_text))
{
	// #### fulltext mode ##########################
	if ($ilconfig['fulltextsearch'])
	{
		$sqlquery['fields'] .= ($titlesonly == '-1')
			? ", MATCH (p.project_title,p.description,p.additional_info,p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (p.project_title) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$sqlquery['relevance'] = ($titlesonly == '-1')
			? ", MATCH (p.project_title,p.description,p.additional_info,p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (p.project_title) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$keyword_formatted .= '<strong><span title="' . handle_input_keywords($keyword_text) . '">' . shorten(handle_input_keywords($keyword_text), 20) . '</span></strong>, ';
		$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
		$keyword_formatted_favtext = $keyword_formatted;
		$sqlquery['keywords'] .= ($titlesonly == '-1')
			? "AND MATCH (p.project_title,p.description,p.additional_info,p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)"
			: "AND MATCH (p.project_title) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)";
	}
	// #### non-fulltext mode ######################
	else
	{
		// splits spaces and commas into array
		$keyword_text_array = preg_split("/[\s,]+/", trim($keyword_text));
		if (sizeof($keyword_text_array ) > 1)
		{
			$sqlquery['keywords'] .= 'AND (';
			for ($i = 0; $i < sizeof($keyword_text_array); $i++)
			{
				$keyword_formatted .= '<strong><span title="' . handle_input_keywords($keyword_text_array[$i]) . '">' . shorten(handle_input_keywords($keyword_text_array[$i]), 20) . '</span></strong>, ';
				$sqlquery['keywords'] .= "p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR ";
				$keywords_array[] = $keyword_text_array[$i];
				if ($titlesonly == '-1')
				{
					// search everything
					$sqlquery['keywords'] .= "p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR ";
				}
			}
			$sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
			$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
			$keyword_formatted_favtext = $keyword_formatted;
		}
		else
		{
			$keyword_formatted = '<strong><span title="' . handle_input_keywords($keyword_text_array[0]) . '">' . shorten(handle_input_keywords($keyword_text_array[0]), 20) . '</span></strong>';
			$keyword_formatted_favtext = '<strong>' . handle_input_keywords($keyword_text_array[0]) . '</strong>';
			$keywords_array[] = $keyword_text_array[0];
			$sqlquery['keywords'] .= ($titlesonly == '-1') ? "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR p.additional_info LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%')" : "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR p.keywords LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%')";
		}
		$sqlquery['fields'] .= ", p.project_title AS relevance";
	}
}

$show['allowlisting'] = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], $cid);
$sqlquery['options'] = '';
// #### filter nonprofit assigned listings #############
if (isset($ilance->GPC['donation']) AND $ilance->GPC['donation'])
{
	$removeurl = rewrite_url($scriptpage, 'donation=' . $ilance->GPC['donation']);
	$sqlquery['options'] .= "AND (p.donation = '1') ";
	handle_search_verbose('<span class="black">{_including_nonprofits}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_including_nonprofits}</strong>, ');
}
if (isset($ilance->GPC['charityid']) AND $ilance->GPC['charityid'] > 0)
{
	$removeurl = rewrite_url($scriptpage, 'charityid=' . intval($ilance->GPC['charityid']));
	$sqlquery['options'] .= "AND (p.charityid = '" . intval($ilance->GPC['charityid']) . "') ";
	$tmp = fetch_charity_details(intval($ilance->GPC['charityid']));
	handle_search_verbose('<span class="black">{_nonprofit}:</span> <span class="gray"><strong>' . $tmp['title'] . '</strong></span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>, ');
	handle_search_verbose_save('{_filter}: <strong>{_nonprofit}: ' . $tmp['title'] . '</strong>, ');
	unset($tmp);
}
// #### filter escrow secured listings #################
if (isset($ilance->GPC['escrow']) AND $ilance->GPC['escrow'])
{
	$removeurl = rewrite_url($scriptpage, 'escrow=' . $ilance->GPC['escrow']);
	$sqlquery['options'] .= "AND (p.filter_escrow = '1') ";
	handle_search_verbose('<span class="black">{_showing_services_that_require_secure_escrow}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_services_that_require_secure_escrow}</strong>, ');
}
// #### filter auctions with public message boards #####
if (isset($ilance->GPC['publicboard']) AND $ilance->GPC['publicboard'])
{
	$removeurl = rewrite_url($scriptpage, 'publicboard=' . $ilance->GPC['publicboard']);
	$sqlquery['options'] .= "AND (p.filter_publicboard = '1') ";
	handle_search_verbose('<span class="black">{_showing_listings_that_allow_public_message_board}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_listings_that_allow_public_message_board}</strong>, ');
}
// #### filter auctions with free shipping #############
if (isset($ilance->GPC['freeshipping']) AND $ilance->GPC['freeshipping'] AND $show['mode_product'])
{
	$removeurl = rewrite_url($scriptpage, 'freeshipping=' . $ilance->GPC['freeshipping']);
	$sqlquery['options'] .= "AND (sd.freeshipping_1 = '1' OR sd.freeshipping_2 = '1' OR sd.freeshipping_3 = '1' OR sd.freeshipping_4 = '1' OR sd.freeshipping_5 = '1') ";
	handle_search_verbose('<span class="black">{_listing_items_with_free_shipping}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_listing_items_with_free_shipping}</strong>, ');
}
// #### filter auctions listed as lots format ##########
if (isset($ilance->GPC['listedaslots']) AND $ilance->GPC['listedaslots'])
{
	$removeurl = rewrite_url($scriptpage, 'listedaslots=' . $ilance->GPC['listedaslots']);
	$sqlquery['options'] .= "AND (p.buynow_qty_lot = '1') ";
	handle_search_verbose('<span class="black">{_showing_items_listed_as_lots}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_items_listed_as_lots}</strong>, ');
}
// #### filter auctions only with images? ##############
if (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '1')
{
	$removeurl = rewrite_url($scriptpage, 'images=' . $ilance->GPC['images']);
	$sqlquery['options'] .= "AND (p.hasimage = '1' OR p.hasimageslideshow = '1') ";
	handle_search_verbose('<span class="black">{_showing_only_items_with_images}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_only_items_with_images}</strong>, ');
}
else if (isset($ilance->GPC['images']) AND $ilance->GPC['images'] == '-1')
{
	$removeurl = rewrite_url($scriptpage, 'images=' . $ilance->GPC['images']);
	$sqlquery['options'] .= "AND (p.hasimage = '0') ";
	handle_search_verbose('<span class="black">{_showing_only_items_with_no_images}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_only_items_with_no_images}</strong>, ');
}
// #### include completed events ###############
if (isset($ilance->GPC['completed']) AND $ilance->GPC['completed'])
{
	$removeurl = rewrite_url($scriptpage, 'completed=' . $ilance->GPC['completed']);
	$sqlquery['projectdetails'] .= "AND (p.haswinner = '1' OR p.hasbuynowwinner = '1') ";
	$sqlquery['timestamp'] = "";
	$sqlquery['projectstatus'] = "AND (p.status != 'open')";
	handle_search_verbose('<span class="black">{_show_only_completed_listings}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_show_only_completed_listings}</strong>, ');
}
// #### include classified ads ###############
if (isset($ilance->GPC['classifieds']) AND $ilance->GPC['classifieds'])
{
	$removeurl = rewrite_url($scriptpage, 'classifieds=' . $ilance->GPC['classifieds']);
	$sqlquery['projectdetails'] .= "AND (p.filtered_auctiontype = 'classified') ";
	handle_search_verbose('<span class="black">{_classified_ads}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_classified_ads}</strong>, ');
}
// #### include flagged as urgent ###############
if (isset($ilance->GPC['urgent']) AND $ilance->GPC['urgent'])
{
	$removeurl = rewrite_url($scriptpage, 'urgent=' . $ilance->GPC['urgent']);
	$sqlquery['projectdetails'] .= "AND (p.urgent = '1') ";
	handle_search_verbose('<span class="black">{_urgent}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_show_only_flagged_urgent}</strong>, ');
}

($apihook = $ilance->api('search_leftnav_options_end')) ? eval($apihook) : false;

// #### currency selector ##############################
if ($ilconfig['globalserverlocale_currencyselector'])
{
	$ilance->GPC['cur'] = isset($ilance->GPC['cur']) ? handle_input_keywords($ilance->GPC['cur']) : '';
	$extrasql = $ilconfig['globalauctionsettings_payperpost'] ? " AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "";
	$extrasql .= $sqlquery['categories'];
	$leftnav_currencies = print_currencies('projects AS p', 'p.currencyid', $ilance->GPC['cur'], $ilconfig['globalserverlocale_currencycatcutoff'], "AND p.status = 'open' " . $extrasql, '');
	$clear_currencies = !empty($clear_currencies_all) ? '<a href="' . $clear_currencies_all . '" rel="nofollow">{_clear}</a>' : '';
	$removeurl = rewrite_url($scriptpage, 'cur=' . $ilance->GPC['cur']);
	$sqlquery['options'] .= (!empty($ilance->GPC['cur'])) ? "AND (FIND_IN_SET(p.currencyid, '" . $ilance->db->escape_string($ilance->GPC['cur']) . "')) " : '';
	if (isset($ilance->GPC['cur']) AND $ilance->GPC['cur'] != '')
	{
		$curs = '';
		if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == true)
		{
			$temp = explode(',', $ilance->GPC['cur']);
			foreach ($temp AS $key => $value)
			{
				if ($value != '')
				{
					$curs .= $ilance->currency->currencies[$value]['currency_abbrev'] . ', ';
				}
			}
			if (!empty($curs))
			{
				$curs = substr($curs, 0, -2);
			}
			unset($temp);
		}
		else if ($ilance->GPC['cur'] != '' AND strrchr($ilance->GPC['cur'], ',') == false)
		{
			$ilance->GPC['cur'] = intval($ilance->GPC['cur']);
			$curs .= $ilance->currency->currencies[$ilance->GPC['cur']]['currency_abbrev'];
		}
		handle_search_verbose('<!--<span class="gray">{_currency}:</span> --><span class="black">' . $curs . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
		handle_search_verbose_save('{_currency}: <strong>' . $curs . '</strong>, ');
		unset($curs);
	}
}
// #### options selector ###############################
$leftnav_options = print_options($ilance->GPC['mode']);
$clear_options = !empty($clear_options_all) ? '<a href="' . $clear_options_all . '" rel="nofollow">{_clear}</a>' : '';

// #### start / end date range filter ##################
if (isset($ilance->GPC['endstart']))
{
	$removeurl = rewrite_url($scriptpage, 'endstart=' . $ilance->GPC['endstart']);
	switch ($ilance->GPC['endstart'])
	{
		case '1':
		{
			// ending within
			if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
			{
				$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '<=');
			}
			handle_search_verbose('<span class="black">{_ending_within_lower} ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span>' . (($ilance->GPC['endstart_filter'] != '-1') ? ' <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>, ' : ', '));
			handle_search_verbose_save('{_listings}: {_ending_within_lower} <strong>' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
			break;
		}
		case '2':
		{
			// ending in more than
			if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
			{
				$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_ADD', 'p.date_end', '>=');
			}
			handle_search_verbose('<span class="black">{_ending_in_more_than_lower} ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>, ');
			handle_search_verbose_save('{_listings}: {_ending_in_more_than_lower} <strong>'.fetch_startend_phrase($ilance->GPC['endstart_filter']).'</strong>, ');
			break;
		}
		case '3':
		{
			// started within
			if (isset($ilance->GPC['endstart_filter']) AND $ilance->GPC['endstart_filter'] != '-1')
			{
				$sqlquery['options'] .= " " . fetch_startend_sql($ilance->GPC['endstart_filter'], 'DATE_SUB', 'p.date_added', '>=');
			}
			handle_search_verbose('<span class="black">{_started_within_lower} ' . fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>, ');
			handle_search_verbose_save('{_listings}: {_started_within_lower} <strong>'.fetch_startend_phrase($ilance->GPC['endstart_filter']) . '</strong>, ');
			break;
		}
	}
}

// #### filter listings with non-disclosed budgets #####
if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] == '-1' AND isset($cid) AND $cid > 0)
{
	$removeurl = rewrite_url($scriptpage, 'budget=' . $ilance->GPC['budget']);
	$clear_budgetrange = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	$sqlquery['options'] .= "AND (p.filter_budget = '0') ";
	handle_search_verbose('<span class="black">{_showing_services_with_nondisclosed_budgets}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter} <strong>{_showing_services_with_nondisclosed_budgets}</strong>, ');
}
else if (isset($ilance->GPC['budget']) AND $ilance->GPC['budget'] > 0 AND isset($cid) AND $cid > 0)
{
	$overview = $ilance->auction->construct_budget_overview(intval($cid), intval($budget));
	$removeurl = rewrite_url($scriptpage, 'budget=' . intval($ilance->GPC['budget']));
	$sqlquery['options'] .= "AND (p.filter_budget = '1' AND p.filtered_budgetid = '" . intval($ilance->GPC['budget']) . "') ";
	handle_search_verbose('<span class="black">{_budget_range}: ' . $overview . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter} <strong>{_budget}: ' . $overview . '</strong>, ');
	unset($overview);
}
else
{
	$ilance->GPC['budget'] = '';
}
// #### search number of bids range ####################
if (!empty($ilance->GPC['bidrange']) AND $ilance->GPC['bidrange'] != '-1')
{
	$removeurl = rewrite_url($php_self, 'bidrange=' . $ilance->GPC['bidrange']);
	$clear_bidrange = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	switch ($ilance->GPC['bidrange'])
	{
		case '1':
		{
			$sqlquery['options'] .= "AND (p.bids BETWEEN 1 AND 10) ";
			handle_search_verbose('<span class="black">{_with_less_than_ten_bids_placed}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_filter}: <strong>{_with_less_than_ten_bids_placed}</strong>, ');
			break;
		}
		case '2':
		{
			$sqlquery['options'] .= "AND (p.bids BETWEEN 10 AND 20) ";
			handle_search_verbose('<span class="black">{_between_ten_and_twenty_bids_placed}</span>, ');
			handle_search_verbose_save('{_filter}: <strong>{_between_ten_and_twenty_bids_placed}</strong><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			break;
		}
		case '3':
		{
			$sqlquery['options'] .= "AND (p.bids > 20) ";
			handle_search_verbose('<span class="black">{_with_more_than_twenty_bids_placed}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_filter}: <strong>{_with_more_than_twenty_bids_placed}</strong>, ');
			break;
		}
		case '4':
		{
			$sqlquery['options'] .= "AND (p.bids = 0) ";
			handle_search_verbose('<span class="black">{_no_bids_placed}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_filter}: <strong>{_no_bids_placed}</strong>, ');
			break;
		}
	}
}
else
{
	$clear_bidrange = '';
}
$ilance->GPC['bidrange'] = !empty($ilance->GPC['bidrange']) ? $ilance->GPC['bidrange'] : '';
// #### left nav bid range link presentation ###########
$search_bidrange_pulldown_product = print_bid_range_pulldown($ilance->GPC['bidrange'], 'bidrange', 'productbidrange', 'links');
$search_bidrange_pulldown_service = print_bid_range_pulldown($ilance->GPC['bidrange'], 'bidrange', 'servicebidrange', 'links');
// #### left nav color search ##########################
if (!empty($ilance->GPC['color']) AND $ilance->GPC['color'] != '-1')
{
	$removeurl = rewrite_url($php_self, 'color=' . $ilance->GPC['color']);
	$clear_color = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	// #### colors selected : &color=Red+Green+Blue
	$colors = explode(' ', $ilance->GPC['color']);
	$tempcolorquery = '';
	$sqlquery['options'] .= "AND ((ac.relativefont = (SELECT relativefont FROM " . DB_PREFIX . "attachment_color WHERE ";
	foreach ($colors AS $color)
	{
		if (isset($color) AND !empty($color))
		{
			$tempcolorquery .= "relativefont LIKE '%" . $ilance->db->escape_string($color) . "%' OR ";
			handle_search_verbose('<span class="black">' . handle_input_keywords($color) . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_colour}: <strong>' . handle_input_keywords($color) . '</strong>, ');
		}
	}
	if (!empty($tempcolorquery))
	{
		$tempcolorquery = substr($tempcolorquery, 0, -4);
		$sqlquery['options'] .= $tempcolorquery;
		$sqlquery['options'] .= " GROUP BY relativefont))) ";
	}
}
else
{
	$clear_color = '';
}
$ilance->GPC['color'] = !empty($ilance->GPC['color']) ? $ilance->GPC['color'] : '';
$search_color_pulldown = print_color_pulldown($ilance->GPC['color'], 'color', 'color', 'links');
// #### search via country #############################
$sqlquery['location'] = $country = $countryid = $countryids = '';
$removeurlcountry = $php_self;
// #### searching via country name #####################
if (!empty($ilance->GPC['country']))
{
	$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
	$country = $ilance->GPC['country'];
	$ilance->GPC['region'] = $ilance->shipping->fetch_region_by_countryid($countryid, false);
	$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
	$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
	$removeurlcountry = rewrite_url($php_self, 'country=' . urlencode($ilance->GPC['country']));
	$sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
}
// #### searching via country identifier ###############
else if (!empty($ilance->GPC['countryid']) AND $ilance->GPC['countryid'] > 0)
{
	$countryid = intval($ilance->GPC['countryid']);
	$ilance->GPC['country'] = $ilance->common_location->print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false);
	$country = $ilance->GPC['country'];
	$ilance->GPC['region'] = $ilance->shipping->fetch_region_by_countryid($countryid, false);
	$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
	$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
	$removeurlcountry = rewrite_url($php_self, 'countryid=' . urlencode($countryid));
	$sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
}
// #### region selector ################################
$region = (isset($ilance->GPC['region']) AND !empty($ilance->GPC['region'])) ? $ilance->GPC['region'] : '';
$regiontype = isset($ilance->GPC['regiontype']) AND !empty($ilance->GPC['regiontype']) ? intval($ilance->GPC['regiontype']) : '';
$regionname = '';
// #### check if our selected region contains a country id
if (strrchr($region, '.'))
{
	$regtemp = explode('.', $region);
	if (!empty($regtemp[0]) AND !empty($regtemp[1]))
	{
		$regionname = fetch_region_title($regtemp[0]);
		$countryid = $regtemp[1];
		$ilance->GPC['country'] = $ilance->common_location->print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false);
		$country = $ilance->GPC['country'];
		$sqlquery['location'] = "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
	}
	else if (!empty($regtemp[0]))
	{
		$regionname = fetch_region_title($regtemp[0]);
	}
	unset($regtemp);
}
else
{
	$regionname = fetch_region_title($region);
	$countryids = fetch_country_ids_by_region($regionname);
	$sqlquery['location'] = (!empty($countryids)) ? "AND (FIND_IN_SET(p.countryid, '" . $countryids . "')) " : "";
}
// #### link to clear region from left nav menu header
$clear_region = '';
if (!empty($regionname))
{
	$removeurl = rewrite_url($php_self, 'region=' . $region);
	$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
	$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
	$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
	$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
	$clear_region = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	handle_search_verbose('<span class="gray"><!--<strong>{_region}: --><span class="black">' . $regionname . '</span></strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_region}: <strong>' . $regionname . '</strong>, ');
}
$leftnav_regions = print_regions('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');
// #### finalize country verbose text so it's placed after the region
if ($countryid > 0)
{
	handle_search_verbose('<span class="black"><!--<span class="gray">{_country}:</span> --><strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_country}: <strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong>, ');
}
// #### search via price range #########################
$sqlquery['pricerange'] = $clear_price = '';
if ($show['mode_product'])
{
	if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
	{
		$removeurl = rewrite_url($scriptpage, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
		if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
		{
			$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
		}
		$clear_price = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
		$sqlquery['pricerange'] .= " AND ((p.currentprice >= " . floatval($ilance->GPC['fromprice']) . " OR p.buynow_price >= " . floatval($ilance->GPC['fromprice']) . ") ";
		$frompriceformatted = ((isset($ilance->GPC['cur']) AND !empty($ilance->GPC['cur'])) ? '$' . $ilance->common->xss_clean(sprintf("%.02f", $ilance->GPC['fromprice'])) : $ilance->currency->format($ilance->common->xss_clean(sprintf("%.02f", $ilance->GPC['fromprice']))));
		handle_search_verbose('<span class="black"><strong>' . $frompriceformatted . ' ' . ((!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0) ? '&ndash;' : '{_or_more}') . '</strong></span> ');
		handle_search_verbose_save('{_min_price}: <strong>' . $frompriceformatted . ' ' . ((!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0) ? '&ndash;' : '{_or_more}') . '</strong>, ');
		unset($frompriceformatted);
	}
	else
	{
		$sqlquery['pricerange'] .= "AND ((p.currentprice >= 0 OR p.buynow_price >= 0)";
	}
	if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
	{
		$removeurl = rewrite_url($scriptpage, 'toprice=' . urldecode($ilance->GPC['toprice']));
		if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
		{
			$removeurl = rewrite_url($removeurl, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
		}
		$clear_price = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
		$sqlquery['pricerange'] .= " AND (p.currentprice <= " . floatval($ilance->GPC['toprice']) . " OR p.buynow_price <= " . floatval($ilance->GPC['toprice']) . "))";
		$topriceformatted = ((isset($ilance->GPC['cur']) AND !empty($ilance->GPC['cur'])) ? '$' . $ilance->common->xss_clean(sprintf("%.02f", $ilance->GPC['toprice'])) : $ilance->currency->format($ilance->common->xss_clean(sprintf("%.02f", $ilance->GPC['toprice']))));
		handle_search_verbose('<span class="black">' . ((!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0) ? '' : '{_up_to} ') . '<strong>' . $topriceformatted . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
		handle_search_verbose_save('{_max_price}: <strong>' . ((!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0) ? '' : '{_up_to} ') . '' . $topriceformatted . '</strong>, ');
		unset($topriceformatted);
	}
	else
	{
		$sqlquery['pricerange'] .= ") ";
		handle_search_verbose_save('{_max_price}: <strong>{_unlimited}</strong>, ');
	}
}
// #### radius searching ###############################
// are we guest and do we have a zip code from the location modal nag popup?
// this is useful for users to get a rough estimate on shipping
if (empty($_SESSION['ilancedata']['user']['userid']))
{
	// user not searching by zip so check cookie
	if (empty($ilance->GPC['radiuszip']))
	{
		// cookie appears to have a zip.. we'll use this
		if (!empty($_COOKIE[COOKIE_PREFIX . 'radiuszip']))
		{
			$ilance->GPC['radiuszip'] = $_COOKIE[COOKIE_PREFIX . 'radiuszip'];
		}
	}
}
else
{
	// member not searching by zip so check profile
	if (empty($ilance->GPC['radiuszip']))
	{
		if (!empty($_SESSION['ilancedata']['user']['postalzip']))
		{
			$ilance->GPC['radiuszip'] = $_SESSION['ilancedata']['user']['postalzip'];
		}
	}
	// default zip
	else
	{
		// check if cookie exist and is the same as the entered zipcode
		// if not, use entered zipcode
		if (!empty($_COOKIE[COOKIE_PREFIX . 'radiuszip']) AND $_COOKIE[COOKIE_PREFIX . 'radiuszip'] == $ilance->GPC['radiuszip'])
		{
			$ilance->GPC['radiuszip'] = $_COOKIE[COOKIE_PREFIX . 'radiuszip'];
		}
	}
}
$show['radiussearch'] = false;
$sqlquery['radius'] = $clear_distance = '';
if ($ilconfig['globalserver_enabledistanceradius'])
{    
    if (!empty($ilance->GPC['radiuszip']) AND $countryid > 0)
    {
            $show['radiussearch'] = true;
            // user supplied a radius.  which country are we trying to do a radius search on?
            $radiuscountryid = intval($countryid);
            $removeurl = rewrite_url($php_self, 'radiuszip=' . urlencode($ilance->GPC['radiuszip']));
            $ilance->GPC['radiusstate'] = '';
            $ilance->GPC['radiuszip'] = mb_strtoupper(trim($ilance->GPC['radiuszip']));
            $ilance->GPC['radius'] = (isset($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0) ? intval($ilance->GPC['radius']) : '';
            $removeurl = rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']);
            // #### build sql to fetch zips in range of zip code entered by user for the viewing region
            if (!empty($ilance->GPC['radius']))
            {
                    $radiusresult = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = true, $radiusjoin = false);
                    if (!empty($radiusresult) AND is_array($radiusresult) AND count($radiusresult) > 1)
                    {
                            // the proper zipcode + country id was selected..
                            $sqlquery['leftjoin'] .= $radiusresult['leftjoin'];
                            $sqlquery['fields'] .= $radiusresult['fields'];
                            $sqlquery['radius'] = $radiusresult['condition'];
                            $zipcodesrange = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = true);
                            $sqlquery['radius'] .= (isset($zipcodesrange) AND is_array($zipcodesrange)) ? $zipcodesrange['condition'] : '';
                            $zipcodecityname = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = true);
                            handle_search_verbose('<span class="black"><!--<span class="gray">{_radius}:</span> --><strong>' . number_format(intval($ilance->GPC['radius'])) . '</strong> {_mile_radius_from} <strong>' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : (!empty($zipcodecityname) ? $zipcodecityname . ', ' : '')) . handle_input_keywords($ilance->GPC['radiuszip']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
                            handle_search_verbose_save('{_radius}: <strong>' . number_format($ilance->GPC['radius']) . '</strong> {_mile_radius_from} <strong>' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : '') . handle_input_keywords($ilance->GPC['radiuszip']) . ', ') . '</strong>';
                    }
            }
            $clear_distance = (!empty($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0) ? '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>' : '';
    }
    
    // #### enable distance column ordering ################
    if (isset($ilance->GPC['sort']) AND ($ilance->GPC['sort'] == '121' OR $ilance->GPC['sort'] == '122'))
    {  
        $show['radiussearch'] = true;
        $postalzip = isset($ilance->GPC['radiuszip']) ? $ilance->GPC['radiuszip'] : (isset($_SESSION['ilancedata']['user']['postalzip']) ? $_SESSION['ilancedata']['user']['postalzip'] : '');
        $usercountryid = isset($ilance->GPC['country']) ? fetch_country_id($ilance->GPC['country']) : (isset($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : fetch_country_id($ilconfig['registrationdisplay_defaultcountry']));
        $distanceresult = $ilance->distance->fetch_sql_as_distance($postalzip, $usercountryid, 'p.zipcode');
        if (is_array($distanceresult))
        {
                $sqlquery['leftjoin'] .= isset($radiusresult['leftjoin']) ? '' : $distanceresult['leftjoin'];
                $sqlquery['fields'] .= $distanceresult['fields'];

                $acceptedsort2 = array('121','122');
                $acceptedsort = array_merge($acceptedsort, $acceptedsort2);
                unset($acceptedsort2);
        }
    }
}
// #### does user search in cities? ####################
$clear_local = $removeurl_local = '';
$removeurl = $php_self;
if (!empty($ilance->GPC['city']) AND !empty($ilance->GPC['country']))
{
	// does user enter a city in search?
	$removeurl = rewrite_url($scriptpage, 'city=' . $ilance->GPC['city']);
	$removeurl_local = rewrite_url($removeurl, 'city=' . $ilance->GPC['city']);
	$ilance->GPC['city'] = ucfirst(trim($ilance->GPC['city']));
	$sqlquery['location'] .= "AND (u.city LIKE '%" . $ilance->db->escape_string($ilance->GPC['city']) . "%') ";
	handle_search_verbose('<span class="black"><!--<span class="gray">{_city}:</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_city}: <strong>' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</strong>, ');
}
// #### does user search in state or provinces? ########
if (!empty($ilance->GPC['state']) AND !empty($ilance->GPC['country']))
{
	// does user enter a city in search?
	$removeurl = rewrite_url($scriptpage, 'state=' . $ilance->GPC['state']);
	$removeurl_local = rewrite_url($removeurl_local, 'state=' . $ilance->GPC['state']);
	$ilance->GPC['state'] = ucfirst(trim($ilance->GPC['state']));
	$sqlquery['location'] .= "AND (u.state LIKE '%" . $ilance->db->escape_string($ilance->GPC['state']) . "%') ";
	handle_search_verbose('<span class="black"><!--<span class="gray">{_state_or_province}:</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_state}: <strong>' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</strong>, ');
}
// #### does user search in zip codes? #################
if (!empty($ilance->GPC['zip_code']) AND !empty($ilance->GPC['country']))
{
	$ilance->GPC['zip_code'] = mb_strtoupper(trim($ilance->GPC['zip_code']));
	$distanceresult = $ilance->distance->fetch_sql_as_distance($ilance->GPC['zip_code'], $ilance->GPC['country'], 'p.zipcode');
	if (is_array($distanceresult))
	{
		$sqlquery['leftjoin'] .= $distanceresult['leftjoin'];
		$sqlquery['fields'] .= $distanceresult['fields'];
	}
	$removeurl = rewrite_url($scriptpage, 'zip_code=' . $ilance->GPC['zip_code']);
	$removeurl_local = rewrite_url($removeurl_local, 'zip_code=' . $ilance->GPC['zip_code']);
	$sqlquery['location'] .= "AND (u.zip_code LIKE '%" . $ilance->db->escape_string(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zip_code'])))) . "%') ";
	handle_search_verbose('<span class="black"><!--{_zip_code}: --><strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_zip_slash_postal_code}: <strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong>, ');
}
$clear_local = (!empty($removeurl_local)) ? '<a href="' . $removeurl_local . '" rel="nofollow">{_clear}</a>' : '';
unset($removeurl_local);
// #### confirm or reject the ability to see the distance column based on user search preferences
if ($show['mode_service'])
{
	if (isset($selected['serviceselected']) AND is_array($selected['serviceselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['serviceselected']))
	{
		$show['distancecolumn'] = 1;
	}
}
else if ($show['mode_product'])
{
	if (isset($selected['productselected']) AND is_array($selected['productselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['productselected']))
	{
		$show['distancecolumn'] = 1;
	}
}
// #### searchable category questions ##################
$sqlquery['genrequery'] = $sqlquery['leftjoinextra'] = '';
$groupcount = 0;
if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
{
	if ($show['mode_service'])
	{
		$sqlquery['leftjoinextra'] = "LEFT JOIN " . DB_PREFIX . "project_answers pans ON (p.project_id = pans.project_id) ";
	}
	else if ($show['mode_product'])
	{
		$sqlquery['leftjoinextra'] = "LEFT JOIN " . DB_PREFIX . "product_answers pans ON (p.project_id = pans.project_id)";
	}
	// #### question groups selected : &qid=9.1,8.1,etc
	$qids = explode(',', $ilance->GPC['qid']);
	$sqlquery['fields'] .= ", COUNT(*) AS filtergroups";
	$tempgenrequery = '';
	$sqlquery['genrequery'] .= "AND (";
	$gcounter = array();
	foreach ($qids AS $keyquestionid => $keyanswerid)
	{
		$aids = explode('.', $keyanswerid);
		$gcounter[] = $aids[0];
		if (isset($aids[1]) AND !empty($aids[1]))
		{
			$questiontitle = fetch_searchable_question_title($aids[0], $project_state);
			if ($questiontitle != '')
			{
				$answertitle = '<span class="black">' . fetch_searchable_answer_title($aids[0], $aids[1], $project_state) . '</span>';
				$showqidurl = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url("{$project_state}catplain", $cid, 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid), '', 0, '', 0, 0) : $ilpage['search'] . print_hidden_fields(true, array('page','searchid','list'), true, '', '', $htmlentities = true, $urldecode = true);
				$showqidurl = urldecode($showqidurl);
				$showqidurl = rewrite_url($showqidurl, '' . $aids[0] . '.' . $aids[1] . ',');
				$showqidurl = rewrite_url($showqidurl, ',' . $aids[0] . '.' . $aids[1]);
				$showqidurl = rewrite_url($showqidurl, '' . $aids[0] . '.' . $aids[1]);
				$tempgenrequery .= "pans.questionid = '" . $aids[0] . "' AND pans.optionid = '" . $aids[1] . "' OR ";
				handle_search_verbose_filters('<span class="gray"><!--' . $questiontitle . ': --><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
				handle_search_verbose_save($questiontitle . ': <strong>' . $answertitle . '</strong>, ');
			}
		}
	}
	$groupcount = count(array_count_values($gcounter));
	if ($groupcount > 0)
	{
		$sqlquery['groupby'] .= " HAVING filtergroups >= '$groupcount'";
	}
	if (!empty($tempgenrequery))
	{
		$tempgenrequery = substr($tempgenrequery, 0, -4);
		$sqlquery['genrequery'] .= $tempgenrequery;
		$sqlquery['genrequery'] .= ") ";
	}
	else
	{
		$sqlquery['genrequery'] = '';
	}
	unset($questiontitle, $answertitle, $tempgenrequery, $qids);
}
// #### finalize our display order for search results ##
if (isset($ilance->GPC['sort']) AND !empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort, true))
{
	$sphrase = fetch_sort_options($project_state);
	$tphrase = $sphrase[$ilance->GPC['sort']];
	$sortconditions = sortable_array_handler('listings');
	$sqlquery['orderby'] = "ORDER BY p.featured_searchresults = '1' DESC, " . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
	unset($sphrase, $tphrase);
}
// #### default sort display order if none selected ####
else
{
	$ilance->GPC['sort'] = '01';
	$sqlquery['orderby'] = "ORDER BY p.featured_searchresults = '1' DESC, p.date_end ASC";
	$sphrase = fetch_sort_options($project_state);
	$tphrase = $sphrase['01'];
	unset($sphrase, $tphrase);
}
// #### hold display order for modals as sort is removed due to main search bar above listings
$sort = $ilance->GPC['sort'];
// #### build sql query ################################
$sqlquery['select'] = "SELECT $sqlquery[fields] $sqlquery[from] $sqlquery[leftjoin] $sqlquery[leftjoinextra] WHERE p.user_id = u.user_id AND u.status = 'active' AND p.visible = '1' " . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "");
$SQL =  "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby] $sqlquery[limit]";
$SQL2 = "$sqlquery[select] $sqlquery[timestamp] $sqlquery[projectstatus] $sqlquery[keywords] $sqlquery[categories] $sqlquery[projectdetails] $sqlquery[projectstate] $sqlquery[options] $sqlquery[pricerange] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[genrequery] $sqlquery[groupby] $sqlquery[orderby]";
$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
$number = $ilance->db->num_rows($numberrows);
$counter = (intval($ilance->GPC['page']) - 1) * fetch_perpage();
$row_count = 0;
$search_results_rows = $excludelist = array();
$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($result) > 0)
{
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{
		$excludelist[] = $row['project_id'];
		$row['distance'] = (!isset($row['distance'])) ? 0 : $row['distance'];
		$td['featured'] = $row['featured'];
		$td['featured_searchresults'] = $row['featured_searchresults'];
		$td['bold'] = $row['bold'];
		$td['highlite'] = $row['highlite'];
		$td['project_id'] = $row['project_id'];
		$td['distance'] = (isset($show['distancecolumn']) AND $show['distancecolumn'] AND !empty($ilance->GPC['radiuszip']))
			? '<div class="smaller">' . $ilance->distance->print_distance_results($row['country'], $row['zipcode'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . '</div>'
			: '-';
		$td['distance_plain'] = (isset($show['distancecolumn']) AND $show['distancecolumn'] AND !empty($ilance->GPC['radiuszip']))
			? $ilance->distance->print_distance_results($row['country'], $row['zipcode'], $countryid, $ilance->GPC['radiuszip'], $row['distance'])
			: 'n/a';
                $td['username'] = $row['username'];
		$td['city'] = ucfirst($row['city']);
		$td['zipcode'] = $row['zipcode'];//mb_strtoupper($row['zipcode']);
		$td['state'] = ucfirst($row['state']);
		$td['country'] = $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $row['country'] . "'", "location_" . $_SESSION['ilancedata']['user']['slng'], "1");
		$td['location'] = (isset($selected['listinglocation']) AND $selected['listinglocation'] != 'false') ? $ilance->common_location->print_auction_location($row['project_id'], $_SESSION['ilancedata']['user']['slng'], $row['auction_country'], $td['state'], $td['city'], $td['zipcode']) : '';
		
		// show the distance bit after the location - Note by Peter - distance has own column, no need to attach to location of item!
		$td['location'] .= (!empty($countryid) AND !empty($ilance->GPC['radiuszip']) AND (isset($show['distancecolumn']) AND $show['distancecolumn'] == false OR !isset($show['distancecolumn'])))
			? '<span>&nbsp;~&nbsp;<span class="black">' . $ilance->distance->print_distance_results($row['country'], $row['zipcode'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . ' {_from_lowercase}</span> <span class="blue"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();">' . handle_input_keywords($ilance->GPC['radiuszip']) . '<!--, ' . handle_input_keywords($ilance->GPC['country']) . '--></a></span></span>'
			: '';
		$td['views'] = number_format($row['views']);
		$td['date_starts'] = $row['date_starts'];
		// #### SERVICE AUCTION LOGIC ##########
		if ($show['mode_service'])
		{
			$row['project_state'] = 'service';
			$td['project_state'] = $row['project_state'];
			$td['project_details'] = $row['project_details'];
			if ($row['project_details'] == 'invite_only')
			{
				$userid = (isset($_SESSION['ilancedata']['user']['userid'])) ? $_SESSION['ilancedata']['user']['userid'] : '0';
				$sqlinvites = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "*
					FROM " . DB_PREFIX . "project_invitations
					WHERE project_id = '" . $row['project_id'] . "'
					    AND (buyer_user_id = '" . $userid . "' OR seller_user_id = '" . $userid . "')
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlinvites) > 0 OR $row['user_id'] == $userid)
				{
					$td['show'] = '1';
				}
				else
				{
					$td['show'] = '0';
				}
			}
			else
			{
				$td['show'] = '1';
			}
			if ($row['bold'])
			{
				$td['title'] = ($ilconfig['globalauctionsettings_seourls']) 
					? construct_seo_url('serviceauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']), $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0)
					: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '"><strong>' . shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']) . '</strong></a>';
			}
			else
			{
				$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
					? construct_seo_url('serviceauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
					: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '">' . shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']) . '</a>';
			}
			$td['title_plain'] = print_string_wrap($row['project_title'], 25);
			// auction description (may contain bbcode)
			switch ($row['project_details'])
			{
				case 'public':
				case 'realtime':
				{
					$td['description'] = handle_input_keywords(strip_vulgar_words($row['description']));
					$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
					$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
					$td['additional_info'] = short_string($row['additional_info'], 75);
					break;
				}
				case 'invite_only':
				{
					$td['description'] = "= {_full_description_available_to_invited_providers_only} =";
					$userid = (isset($_SESSION['ilancedata']['user']['userid'])) ? $_SESSION['ilancedata']['user']['userid'] : '0';
					$sqlinvites = $ilance->db->query("
						SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id
						FROM " . DB_PREFIX . "project_invitations
						WHERE project_id = '" . $row['project_id'] . "'
						    AND (buyer_user_id = '" . $userid . "' OR seller_user_id = '" . $userid . "')
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sqlinvites) > 0 OR $row['user_id'] == $userid)
					{
						$td['description'] = handle_input_keywords(strip_vulgar_words($row['description']));
						$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
						$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
						$td['additional_info'] = short_string(print_string_wrap($row['additional_info'], 50), 50);
					}
					unset($sqlinvites);
					break;
				}
			}

			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$td['category'] = construct_seo_url('servicecat', $row['cid'], $auctionid = 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
				$td['category'] = '<span class="blue">' . $td['category'] . '</span>';
			}
			else
			{
				$td['category'] = '<a href="' . $ilpage['rfp'] . '?cid=' . $row['cid'] . '">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']) . '</a>';
				$td['category'] = '<span class="blue">' . $td['category'] . '</span>';
			}

			$td['username'] = ($td['show'] == 1) ? $td['username'] : '{_sealed}';
			$td['city'] = ($td['show'] == 1) ? $td['city'] : '{_sealed}';
			$td['zipcode'] = ($td['show'] == 1) ? $td['zipcode'] : '{_sealed}';
			$td['state'] = ($td['show'] == 1) ? $td['state'] : '{_sealed}';
			$td['country'] =  ($td['show'] == 1) ? $td['country'] : '{_sealed}';
			$td['location'] = ($td['show'] == 1) ? $td['location'] : '{_sealed}';

			// hide average bid amount on results page if auction is "sealed"
			if ($row['bid_details'] == 'open' OR $row['bid_details'] == 'blind')
			{
				$avg = $ilance->bid->fetch_average_bid($row['project_id'], true, $row['bid_details'], true);
				if ($avg > 0)
				{
					$td['averagebid'] =  print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $avg, $row['currencyid']);
					$td['averagebid_plain'] = $ilance->currency->format($avg, $row['currencyid']);
				}
				else
				{
					$td['averagebid'] = $td['averagebid_plain'] = '-';
				}
				unset($avg);
			}
			else
			{
				$td['averagebid'] = $td['averagebid_plain'] = '= {_sealed} =';
			}

			$td['averagebid']  = ($td['show'] == 1) ? $td['averagebid'] : '{_sealed}';
			$td['averagebid_plain']  = ($td['show'] == 1) ? $td['averagebid_plain'] : '{_sealed}';
			$td['sel'] = '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$td['class'] = ($row['highlite']) ? $ilconfig['serviceupsell_highlightcolor'] : (($row_count % 2) ? 'alt1' : 'alt1');
			$td['timeleft'] = '<strong>' . $ilance->auction->auction_timeleft(false, $row['date_starts'], $row['mytime'], $row['starttime']) . '</strong>';
			$td['icons'] =  ($td['show'] == 1) ?  $ilance->auction->auction_icons($row) : '';
			$bids = ($ilconfig['globalauctionsettings_seourls'])
				? construct_seo_url('serviceauction', 0, $row['project_id'], $row['project_title'], $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, '', '', $row['bids'] . '&nbsp;{_bids_lower}')
				: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '">' . $row['bids'] . '&nbsp;{_bids_lower}</a>';
			$td['bids'] = ($td['show'] == 1) ? (($row['bids'] > 0) ? '<div class="smaller">' . $row['bids'] . ' {_bids_lower}</div>' : '<div class="smaller">0&nbsp;{_bids_lower}</div>') : '{_sealed}';
			$td['views'] = ($td['show'] == 1) ? $row['views'] : '{_sealed}';
			$td['budget'] = ($td['show'] == 1) ?  '<div>' . $ilance->auction->construct_budget_overview($row['cid'], $row['filtered_budgetid'], $notext = true, $nobrackets = true, $forcenocategory = true) . '</div>' : '{_sealed}';

			($apihook = $ilance->api('search_results_services_loop')) ? eval($apihook) : false;

			$search_results_rows[] = $td;
			$row_count++;
		}

		// #### PRODUCT AUCTION LOGIC ##########
		else if ($show['mode_product'])
		{
			if (isset($ilance->GPC['list']) )
			{
				$selected['list'] = $ilance->GPC['list'];
			}
			$row['project_state'] = 'product';
			$td['project_state'] = $row['project_state'];
			$td['project_details'] = $row['project_details'];
			// auction description (may contain bbcode)
			switch ($row['project_details'])
			{
				case 'public':
				case 'realtime':
				{
					$td['description'] = handle_input_keywords($ilance->bbcode->strip_bb_tags($row['description']));
					$td['description'] = strip_vulgar_words($td['description']);
					$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
					break;
				}
				case 'invite_only':
				{
					$td['description'] = '= {_full_description_available_to_invited_providers_only} =';
					if ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0) OR ($row['user_id'] == $_SESSION['ilancedata']['user']['userid']))
					{
						$sql_invites = $ilance->db->query("
							SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id
							FROM " . DB_PREFIX . "project_invitations
							WHERE project_id = '" . $row['project_id'] . "'
							    AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_invites) > 0 OR $row['user_id'] == $_SESSION['ilancedata']['user']['userid'])
						{
							$td['description'] = handle_input_keywords($ilance->bbcode->strip_bb_tags($row['description']));
							$td['description'] = strip_vulgar_words($td['description']);
							$td['description'] = short_string(print_string_wrap($td['description'], 50), 50);
						}
					}
					break;
				}
			}
			$td['category'] = ($ilconfig['globalauctionsettings_seourls'])
				? construct_seo_url('productcat', $row['cid'], 0, $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']), '', 0, '', 0, 0)
				: '<a href="' . $ilpage['merch'] . '?cid=' . $row['cid'] . '">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']) . '</a>';
			$td['category'] = '<span class="blue">' . $td['category'] . '</span>';
			$td['category_plain'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']);
			$td['proxybit'] = $td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';
			$td['filtered_auctiontype'] = $row['filtered_auctiontype'];
			if ($row['bold'])
			{
				$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
					? construct_seo_url('productauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']), '', 1, '', 0, 0)
					: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '"><strong>' . shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']) . '</strong></a>';
			}
			else
			{
				$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
					? construct_seo_url('productauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']), '', 0, '', 0, 0)
					: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '">' . shorten(print_string_wrap($row['project_title'], 25), $ilconfig['globalfilters_maxcharacterstitle']) . '</a>';
			}
			$td['title_plain'] = print_string_wrap($row['project_title'], 25);
			$td['sel'] = '<input type="checkbox" name="project_id[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$td['class'] = ($row['highlite']) ? $ilconfig['productupsell_highlightcolor'] : (($row_count % 2) ? 'alt1' : 'alt1');
			$td['timeleft'] = '<strong>' . $ilance->auction->auction_timeleft(false, $row['date_starts'], $row['mytime'], $row['starttime']) . '</strong>';
			$td['timeleft_verbose'] = print_date($row['date_end']);
			$td['icons'] = $ilance->auction->auction_icons($row);
			if ($row['ship_method'] == 'localpickup')
			{
				$td['shipping'] = '<div class="smaller">{_local_pickup}</div>';
				$td['shipping_plain'] = '{_local_pickup}';
			}
			else if ($row['ship_method'] == 'digital')
			{
				$td['shipping'] = '<div class="smaller">{_download}</div>';
				$td['shipping_plain'] = '{_download}';
			}
			else if ($row['ship_method'] == 'calculated')
			{
				$td['shipping'] = '<div class="smaller blue"><a href="javascript:void(0)">{_calculate_shipping}</a></div>';
				$td['shipping_plain'] = '{_calculate_shipping}';
			}
			else
			{
				$shipping = array();
				for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
				{
					if ($row['freeshipping_' . $i] == 0 AND $row['ship_fee_' . $i] > 0)
					{
						$shipping[] = ($row['ship_fee_' . $i] + $row['ship_handlingfee']);
					}
				}
				$td['shipping_plain'] = $ilance->shipping->fetch_lowest_shipping_cost($shipping, true, $row['project_id'], $row['currencyid']);
				$td['shipping'] = '<div class="smaller">' . $td['shipping_plain'] . '</div>';
				unset($shipping);
			}
			$td['mytime'] = $row['mytime'];
			$td['starttime'] = $row['starttime'];
			$td['endtime'] = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			if ($selected['list'] == 'list' OR isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'list')
			{
				$td['buynow'] = $td['buynowimg'] = $td['buynowtxt'] = '';
				$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
				$td['sample'] = ($ilconfig['globalauctionsettings_seourls']) ? $ilance->auction->print_item_photo($url, 'thumb', $row['project_id'], '0', '#ffffff', 0, '', false, 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id'], '0', '#ffffff', 0, '', false, 1);
				$td['sample_plain'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id'], '0', '#ffffff', 0, '', true);
				unset($url);
				if ($row['buynow'] AND $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow'] AND $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular')
				{
					$td['price'] = '';
					if ($row['filtered_auctiontype'] == 'regular')
					{
						// current price & buy now price
						if ($row['bids'] > 0)
						{
							$td['price'] = ($selected['currencyconvert'] == 'true')
								? '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
								: '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
							$td['price'] .= ($selected['currencyconvert'] == 'true')
								? '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</div>'
								: '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</div>';
							$td['currentbid_plain'] = $ilance->currency->format($row['currentprice'], $row['currencyid']);
							$td['price_plain'] = $ilance->currency->format($row['buynow_price'], $row['currencyid']);
						}
						else
						{
							$td['price'] = ($selected['currencyconvert'] == 'true')
								? '<div class="black" style="height:20px"><span title="{_no_bids_placed}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
								: '<div class="black" style="height:20px"><span title="{_no_bids_placed}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
							$td['price'] .= ($selected['currencyconvert'] == 'true')
								? '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</div>'
								: '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</div>';
							$td['currentbid_plain'] = $ilance->currency->format($row['currentprice'], $row['currencyid']);
							$td['price_plain'] = $ilance->currency->format($row['buynow_price'], $row['currencyid']);
						}
						$td['sold'] = $row['buynow_purchases'];	
						$td['bids'] = ($row['bids'] > 0) ? '<div class="smaller black">' . $row['bids'] . '&nbsp;{_bids_lower}</div>' : '<div class="smaller black">0&nbsp;{_bids_lower}</div>';
						$td['bids_plain'] = $row['bids'];
						// proxy bid information
						if (!empty($_SESSION['ilancedata']['user']['userid']))
						{
							$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
							if ($pbit > 0)
							{
								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true') ? '<div class="smaller green" style="padding-top:2px" title="{_invisible}">{_your_maximum_bid}: ' . $ilance->currency->format($pbit, $row['currencyid']) . '</div>' : '';
							}
							unset($pbit);
						}
					}
					else if ($row['filtered_auctiontype'] == 'fixed')
					{
						// buy now price
						$td['price'] = ($selected['currencyconvert'] == 'true')
							? '<div class="black" style="padding-top:1px;height:20px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</strong></div>'
							: '<div class="black" style="padding-top:1px;height:20px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></div>';
						$td['currentprice_plain'] = $ilance->currency->format($row['buynow_price'], $row['currencyid']);
						$td['price_plain'] = $ilance->currency->format($row['buynow_price'], $row['currencyid']);
						$td['sold'] = $row['buynow_purchases'];
						$td['bids'] = '<div class="smaller black">' . number_format($td['sold']) . '&nbsp;{_sold_lower}</div>';
						$td['bids_plain'] = 'n/a';
					}
				}
				// #### no buy now
				else
				{
					if ($row['bids'] > 0)
					{
						$currentbid = $row['currentprice'];
						$td['price'] = ($selected['currencyconvert'] == 'true')
							? '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $currentbid, $row['currencyid']) . '</strong></div>'
							: '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($currentbid, $row['currencyid']) . '</strong></div>';
						$td['currentbid_plain'] = $ilance->currency->format($currentbid, $row['currencyid']);
						$td['price_plain'] = 'n/a';
						if (!empty($_SESSION['ilancedata']['user']['userid']))
						{
							$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($row['project_id'], $_SESSION['ilancedata']['user']['userid']);
							if ($pbit > 0)
							{
								$td['proxybit'] = (!empty($selected['proxybit']) AND $selected['proxybit'] == 'true') ? '<div class="smaller green" style="padding-top:2px" title="{_invisible}">{_your_maximum_bid}: ' . $ilance->currency->format($pbit, $row['currencyid']) . '</div>' : '';
							}
							unset($pbit);
						}
						$td['sold'] = '';
						$td['bids'] = ($row['bids'] > 0) ? '<div class="smaller black">' . $row['bids'] . '&nbsp;{_bids_lower}</div>' : '<div class="smaller black">0&nbsp;{_bids_lower}</div>';
						$td['bids_plain'] = $row['bids'];
					}
					else
					{
						// starting bid price
						$td['price'] = ($selected['currencyconvert'] == 'true')
							? '<div class="black" style="height:20px"><span title="{_no_bids_placed}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['startprice'], $row['currencyid']) . '</strong></div>'
							: '<div class="black" style="height:20px"><span title="{_no_bids_placed}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_gray.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['startprice'], $row['currencyid']) . '</strong></div>';
						$td['currentbid_plain'] = $ilance->currency->format($row['startprice'], $row['currencyid']);
						$td['price_plain'] = 'n/a';
						$td['sold'] = '';
						$td['bids'] = ($row['bids'] > 0) ? '<div class="smaller black">' . $row['bids'] . '&nbsp;{_bids_lower}</div>' : '<div class="smaller black">0&nbsp;{_bids_lower}</div>';
					}
				}
			}
			else if ($selected['list'] == 'gallery' OR isset($ilance->GPC['list']) AND $ilance->GPC['list'] == 'gallery')
			{
				// display thumbnail
				$url = construct_seo_url('productauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0);
				$td['sample'] = ($ilconfig['globalauctionsettings_seourls'])
					? $ilance->auction->print_item_photo($url, 'thumbgallery', $row['project_id'], '0', '#ffffff', 0, '', false, 1)
					: $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumbgallery', $row['project_id'], '0', '#ffffff', 0, '', false, 1);
				unset($url);
				if ($row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'fixed' OR $row['buynow_price'] > 0 AND $row['filtered_auctiontype'] == 'regular')
				{
					$td['sold'] = $row['buynow_purchases'];
					if ($row['filtered_auctiontype'] == 'regular')
					{
						$bids = ($ilconfig['globalauctionsettings_seourls'])
							? construct_seo_url('productauction', 0, $row['project_id'], $row['project_title'], '', 0, '', 0, 0, '', '', $row['bids'])
							: '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '">' . $row['bids'] . '</a>';
						$td['bids'] = ($row['bids'] > 0) ? '<span>' . $row['bids'] . '</span>' : '<span>0</span>';
						$td['bids_plain'] = $row['bids'];
						$td['price'] = ($selected['currencyconvert'] == 'true')
							? '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid' . (($row['bids'] > 0) ? '' : '_gray') . '.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
							: '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-2px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid' . (($row['bids'] > 0) ? '' : '_gray') . '.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
						$td['buynow'] = ($selected['currencyconvert'] == 'true')
							? '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</div>'
							: '<div class="gray" style="height:20px;padding-top:2px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</div>';
					}
					else if ($row['filtered_auctiontype'] == 'fixed')
					{
						$td['bids'] = '';
						$td['buynow'] = ($selected['currencyconvert'] == 'true')
							? '<div class="black" style="height:20px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['buynow_price'], $row['currencyid']) . '</strong></div>'
							: '<div class="black" style="height:20px"><span title="{_buy_now_price}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['buynow_price'], $row['currencyid']) . '</strong></div>';
					}
				}
				// #### no buy now
				else
				{
					$td['sold'] = '';	
					$td['bids'] = ($row['bids'] > 0) ? '<span>' . $row['bids'] . '</span>' : '<span>0</span>';
					$td['price'] = ($selected['currencyconvert'] == 'true')
						? '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid' . (($row['bids'] > 0) ? '' : '_gray') . '.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['currentprice'], $row['currencyid']) . '</strong></div>'
						: '<div class="black" style="height:20px"><span title="' . $row['bids'] . ' {_bids_lower}" style="margin-top:-1px;float:' . (($ilconfig['template_textalignment'] == 'left') ? 'left' : 'right') . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid' . (($row['bids'] > 0) ? '' : '_gray') . '.gif" border="0" alt="" id="" /></span>&nbsp;<strong>' . $ilance->currency->format($row['currentprice'], $row['currencyid']) . '</strong></div>';
				}
			}
			
			($apihook = $ilance->api('search_results_products_loop')) ? eval($apihook) : false;

			$search_results_rows[] = $td;
			$row_count++;
		}
	}
	$show['no_rows_returned'] = false;
}
else
{
	$show['no_rows_returned'] = true;
}
if (empty($ilance->GPC['do']) OR isset($ilance->GPC['do']) AND ($ilance->GPC['do'] != 'xml' OR $ilance->GPC['do'] != 'array'))
{
	// #### featured spotlight auction listings ############
	$featuredserviceauctions = $featuredproductauctions = array();
	$kws = !empty($keyword_text) ? '_' . str_replace(' ', '_', $keyword_text) : '';
	if ($show['mode_service'])
	{
		if (($featuredserviceauctions = $ilance->cache->fetch('featuredserviceauctions_' . $cid . $kws)) === false)
		{
			$featuredserviceauctions = $ilance->auction_listing->fetch_featured_auctions('service', 20, 1, $cid, $keyword_text, false, $excludelist);
			$ilance->cache->store('featuredserviceauctions_' . $cid . $kws, $featuredserviceauctions);
		}
	}
	if ($show['mode_product'])
	{
		if (($featuredproductauctions = $ilance->cache->fetch('featuredproductauctions_' . $cid . $kws)) === false)
		{
			$featuredproductauctions = $ilance->auction_listing->fetch_featured_auctions('product', 20, 1, $cid, $keyword_text, false, $excludelist);
			$ilance->cache->store('featuredproductauctions_' . $cid . $kws, $featuredproductauctions);
		}
	}
	
	($apihook = $ilance->api('search_results_do_start')) ? eval($apihook) : false;
	
	// #### BUILD OUR PAGNATOR #############################
	$prevnext = print_pagnation($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $scriptpage);

	// #### PRINT OUR SEARCH RESULTS TABLE #################
	$search_results_table = print_search_results_table($search_results_rows, $project_state, $prevnext);
	$keywords = (!empty($ilance->GPC['q'])) ? un_htmlspecialchars($ilance->GPC['q']) : '';

	// #### fewer keywords search ##########################
	$fewer_keywords = print_fewer_keywords_search($keywords_array, $ilance->GPC['mode'], $number);

	// #### category budget ################################
	$budget = isset($ilance->GPC['budget']) ? intval($ilance->GPC['budget']) : '';
	$budgettemp = $ilance->auction_post->print_budget_logic_type_js($cid, $ilance->GPC['mode'], $budget);
	if (isset($show['budgetgroups']) AND $show['budgetgroups'] AND is_array($budgettemp))
	{
		$budget_slider_1 = $budgettemp[0];
		$budget_slider_2 = $budgettemp[1];
	}
	unset($budgettemp);
	if (isset($show['mode_service']) AND $show['mode_service'] OR isset($show['mode_providers']) AND $show['mode_providers'])
	{
		$v3left_nav = $ilance->template_nav->print_left_nav('service', $cid, 1, 0, $ilconfig['globalfilters_enablecategorycount'], true);
	}
	else
	{
		// pre-populate from price and to price field inputs for left nav search menu
		$fromprice = isset($ilance->GPC['fromprice']) ? sprintf("%01.2f", $ilance->GPC['fromprice']) : '';
		$toprice = isset($ilance->GPC['toprice']) ? sprintf("%01.2f", $ilance->GPC['toprice']) : '';
		$v3left_nav = $ilance->template_nav->print_left_nav('product', $cid, 1, 0, $ilconfig['globalfilters_enablecategorycount'], true);
	}
	// #### SAVE AS FAVORITE SEARCH OPTION #################
	if ($ilconfig['savedsearches'])
	{
		// build search request parameters
		$favorites = array();
		foreach ($ilance->GPC AS $search => $option)
		{
			if ($search != 'submit' AND $search != 'search' AND $search != 'page' AND $search != 'sef')
			{
				$favorites[] = array($search => $option);
			}
		}
		if (!empty($favorites) AND is_array($favorites))
		{
			$encrypt = serialize($favorites);
			$encrypt = urlencode($encrypt);
		}
		$favoritesearchurl = $encrypt;
		if (empty($favtext))
		{
			$vebsave = print_search_verbose_saved('verbose_save');
			$favtext = $vebsave;
			$favtext = !empty($vebsave) ? mb_substr($favtext, 0, -2) : $favtext;
		}
		$favtext = ilance_htmlentities($favtext);
	}
	else 
	{
		$favtext = '';
	}
	// ####  build our category breadcrumb navigator
	if ($show['mode_service'])
	{
		$sortmode = $mode = 'service';
		$navcrumb = array();
		$ilance->categories->breadcrumb($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng']);
		if (empty($cid) OR $cid == 0 OR $cid == '')
		{
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['categoryidentifier'])] = '{_categories}';
				$navcrumb[HTTP_SERVER . "$ilpage[search]?tab=0"] = '{_search}';
				$navcrumb[""] = '{_services}';
			}
			else
			{
				$navcrumb["$ilpage[main]?cmd=categories"] = '{_categories}';
				$navcrumb["$ilpage[search]?tab=0"] = '{_search}';
				$navcrumb[""] = '{_services}';
			}
		}
	}
	else if ($show['mode_product'])
	{
		$sortmode = $mode = 'product';
		$navcrumb = array();
		$ilance->categories->breadcrumb($cid, 'productcatmap', $_SESSION['ilancedata']['user']['slng']);
		if (empty($cid) OR $cid == 0 OR $cid == '')
		{
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['categoryidentifier'])] = '{_categories}';
				$navcrumb[HTTP_SERVER . "$ilpage[search]?tab=1"] = '{_search}';
				$navcrumb[""] = '{_products}';
			}
			else
			{
				$navcrumb["$ilpage[main]?cmd=categories"] = '{_categories}';
				$navcrumb["$ilpage[search]?tab=1"] = '{_search}';
				$navcrumb[""] = '{_products}';
			}
		}
	}
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
	$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', $sortmode);
	$city = isset($ilance->GPC['city']) ? handle_input_keywords($ilance->GPC['city']) : '';
	$state = isset($ilance->GPC['state']) ? handle_input_keywords($ilance->GPC['state']) : '';
	$zip_code = isset($ilance->GPC['zip_code']) ? handle_input_keywords($ilance->GPC['zip_code']) : '';
	$radiuszip = isset($ilance->GPC['radiuszip']) ? handle_input_keywords($ilance->GPC['radiuszip']) : '';
	$hiddenfields = print_hidden_fields(false, array('searchid','sef','cid','buynow','sort','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','q','page'), false, '', '', true, true);
	$hiddenfields_leftnav = print_hidden_fields(false, array('city','state','searchid','sef','exactname','searchuser','budget','country','auctiontype','buynow','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','page','radius','radiuscountry','radiuszip','fromprice','toprice'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
}

($apihook = $ilance->api('search_results_auctions_end')) ? eval($apihook) : false;

// #### DISPLAY SEARCH RESULTS VIA XML #################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'xml')
{
	if (!empty($search_results_rows) AND is_array($search_results_rows))
	{
		$xml = $ilance->xml->search_to_xml($search_results_rows, false);
		echo $xml;
	}
	exit();
}
// #### DISPLAY SEARCH RESULTS VIA SERIALIZED ARRAY ####
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'array')
{
	if (!empty($search_results_rows) AND is_array($search_results_rows))
	{
		echo urlencode(serialize($search_results_rows));
	}
	exit();
}
// #### DISPLAY SEARCH RESULTS TEMPLATE ################
else
{
	// #### init our budget range slider ###################
	if (!empty($budgetfilter) OR isset($show['budgetgroups']) AND $show['budgetgroups'])
	{
		$onload .= "init_budgetSlider(); ";
		$onload .= (isset($ilance->GPC['budget'])) ? "set_budgetSlider('" . intval($ilance->GPC['budget']) . "'); " : "";
	}
	// attempt to correct the spelling for the user if applicable
	$didyoumean = print_did_you_mean($keyword_text, $mode);
	// if we're a guest and we don't have the region modal cookie let's ask for it
	$cookieregion = (!empty($_COOKIE[COOKIE_PREFIX . 'region'])) ? $_COOKIE[COOKIE_PREFIX . 'region'] : '';
	$country_user_id = (isset($_SESSION['ilancedata']['user']['countryid'])) ? $_SESSION['ilancedata']['user']['countryid'] : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'],fetch_site_slng());
	$full_country_pulldown = $ilance->common_location->construct_country_pulldown($country_user_id, $cookieregion, 'region', true, '', false, true, true);
	$redirect_not_login =  (!isset($_SESSION['ilancedata']['user']['userid'])) ? '?redirect=' . urlencode($ilpage['search'] . print_hidden_fields(true, array(), true)) : '';
	if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']) AND $ilconfig['globalfilters_regionmodal'])
	{
		$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow(); ';
		// don't ask this guest for region info via popup modal for 3 days
		set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
	}
	// #### build our search engine verbose output #########
	if (!empty($keyword_text))
	{
		$vebsave = print_search_verbose_saved('verbose_save');
		$favtext = '<div>{_keywords}: <strong>' . stripslashes($keyword_formatted_favtext) . '</strong></div>' . $vebsave;
		$favtext = !empty($vebsave) ? handle_input_keywords(mb_substr($favtext, 0, -2)) : handle_input_keywords($favtext);
		$vebsave = print_search_verbose_saved('verbose');
		if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
		{
			$text = '<span style="font-size:15px" class="black"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_keywords} <span class="black">' . stripslashes($keyword_formatted) . '</span>';
		}
		else
		{
			$text = '<span style="font-size:15px" class="black"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_keywords} <span class="black">' . stripslashes($keyword_formatted) . '</span> ' . $vebsave;
			$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
		}
		unset($vebsave);
		$text = '<span id="verbosetext">' . $text . '</span>';
	}
	else
	{
		// favorite search text results
		$vebsave = print_search_verbose_saved('verbose_save');
		$favtext = $vebsave;
		$favtext = !empty($vebsave) ? handle_input_keywords(mb_substr($favtext, 0, -2)) : handle_input_keywords($favtext);
		$vebsave = print_search_verbose_saved('verbose');
		if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
		{
			$text = '<span style="font-size:16px" class="black"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_no_keywords}';
		}
		else
		{
			$text = '<span style="font-size:16px" class="black"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_no_keywords} ' . (!empty($vebsave) ? ' ' . $vebsave : $vebsave);
			$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
		}
		unset($vebsave);
		$text = '<span id="verbosetext">' . $text . '</span>';
	}
	if ($ilconfig['globalauctionsettings_seourls'] AND $cid > 0)
	{
		$categoryname = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
		$showallurl = construct_seo_url("{$project_state}catplain", $cid, 0, $categoryname, '', 0, '', 0, 0, 'qid');
	}
	else
	{
		$showallurl = $ilpage['search'] . '?mode=' . $ilance->GPC['mode'] . '&amp;sort=' . intval($ilance->GPC['sort']) . '&amp;page=' . intval($ilance->GPC['page']);
	}
	define('PHP_SELF_NOQID', $showallurl);
	$showtext = print_search_verbose_saved('verbose_filter');
	if (!empty($showtext))
	{
		$showtext = mb_substr($showtext, 0, -2) . '&nbsp;&nbsp;<span class="blue"><a href="' . $showallurl . '" rel="nofollow">{_show_all}</a></span>';
		$text .= ', <span><strong>' . $showtext . '</strong></span>';
	}
	// #### save this search ###############################
	if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0)
	{
		// todo: add hit tracker to show hit count of saved search
		$savesearchlink = '';
	}
	else
	{
		$savesearchlink = '<span class="smaller blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow()">{_save_as_favorite_search}</a></span>&nbsp;&nbsp;&nbsp; <span class="smaller gray">|</span> &nbsp;&nbsp;&nbsp;';
	}
	$var2 = $ilance->categories->fetch_parent_ids($cid);
	$var_val = explode(',', $var2);
	$count = count($var_val);
	$res_title_new = '';
	for ($i = 0; $i < $count; $i++)
	{
		$sql1 = $ilance->db->query("
			SELECT title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($var_val[$i]) . "'
				AND cattype = '" . $ilance->db->escape_string($mode) . "'
		");
		if ($ilance->db->num_rows($sql1) > 0)
		{
			$res_title = $ilance->db->fetch_array($sql1, DB_ASSOC);
			$res_title_new .= $res_title['title'] . ' | ';
		}
	}
	if (!empty($res_title_new))
	{
		$res_title_new = substr($res_title_new, 0, -3);
	}
	$area_title = '{_search_results_display}<div class="smaller">' . $metatitle . '</div>' . ((isset($keyword_text) AND !empty($keyword_text)) ? '<div class="smaller">{_keywords}: ' . handle_input_keywords($keyword_text) . '</div>' : '');
	$page_title = ((isset($keyword_text) AND !empty($keyword_text)) ? handle_input_keywords(str_replace('"', '', $keyword_text)) : ((!empty($metatitle)) ? handle_input_keywords($res_title_new) : '{_search}')) . ' | ' . SITE_NAME;
	$ilance->template->fetch('main', 'search_results' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('featuredproductauctions','featuredserviceauctions'));
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('search_category_pulldown','categoryname','savesearchlink','legend','search_color_pulldown','clear_budgetrange','clear_local','redirect_not_login','clear_distance','clear_searchuser_url','country','leftnav_options','leftnav_currencies','clear_currencies','clear_options','sort','clear_bidrange','clear_color','clear_listtype','leftnav_buyingformats','showallurl','clear_searchuser','clear_price','clear_region','leftnav_regions','full_country_pulldown','didyoumean','searchuser','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_country_pulldown_product','search_country_pulldown_service','search_radius_country_pulldown_product','search_radius_country_pulldown_service','budget_slider_1','budget_slider_2','favtext','profilebidfilters','fewer_keywords','budgetfilter','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','hiddenfields','fromprice','toprice','search_results_table','sortpulldown','keywords','favoritesearchurl','keywords','search_product_category_pulldown','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','search_country_pulldown','search_country_pulldown2','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','distance','subcategory_name','text','prevnext','prevnext2','default_exchange_rate');

	($apihook = $ilance->api('search_results_auctions_template')) ? eval($apihook) : false;

	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>