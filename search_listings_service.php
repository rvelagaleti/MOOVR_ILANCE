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
$show['widescreen'] = true;
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
// search mode logic
$show['mode_product'] = $show['mode_providers'] = $show['mode_service'] = false;
$navcrumb[""] = '{_services}';
$show['mode_service'] = true;
$project_state = 'service';
$sqlquery['projectstate'] = "AND (p.project_state = 'service')";
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
$sqlquery['relevance'] = $sqlquery['pricerange'] = '';
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
			handle_search_verbose('{_in} ' . mb_substr($subcatname, 1) . ', ');
			handle_search_verbose_save('{_categories}: ' . mb_substr($subcatname, 1) . ', ');
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
		$buyingformats .= '{_reverse_auction}, ';
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
	if (!empty($buyingformats))
	{
		$buyingformats = substr($buyingformats, 0, -2);
		handle_search_verbose('<span class="black"><!--<span class="gray">{_hiring_formats}:</span> -->' . $buyingformats . '</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
		handle_search_verbose_save('{_hiring_formats}: <strong>' . $buyingformats . '</strong>, ');
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
			? ", MATCH (p.project_title, p.description, p.additional_info, p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (p.project_title) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$sqlquery['relevance'] = ($titlesonly == '-1')
			? ", MATCH (p.project_title, p.description, p.additional_info, p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (p.project_title) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$keyword_formatted .= '<strong><span title="' . handle_input_keywords($keyword_text) . '">' . shorten(handle_input_keywords($keyword_text), 20) . '</span></strong>, ';
		$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
		$keyword_formatted_favtext = $keyword_formatted;
		$sqlquery['keywords'] .= ($titlesonly == '-1')
			? "AND MATCH (p.project_title, p.description, p.additional_info, p.keywords) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)"
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
	}
}
$show['allowlisting'] = $ilance->categories->can_post($_SESSION['ilancedata']['user']['slng'], $ilance->GPC['mode'], $cid);
$sqlquery['options'] = '';
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
	$overview = $ilance->auction->construct_budget_overview(intval($cid), intval($ilance->GPC['budget']));
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
// #### search via country #############################
$sqlquery['location'] = $country = $countryid = $countryids = '';
$removeurlcountry = $php_self;
// #### searching via country name #####################
if (!empty($ilance->GPC['country']))
{
	$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
	$country = $ilance->GPC['country'];
	$removeurlcountry = rewrite_url($php_self, 'country=' . urlencode($ilance->GPC['country']));
	$sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
}
// #### searching via country identifier ###############
else if (!empty($ilance->GPC['countryid']) AND $ilance->GPC['countryid'] > 0)
{
	$countryid = intval($ilance->GPC['countryid']);
	$ilance->GPC['country'] = $ilance->common_location->print_country_name($countryid, $_SESSION['ilancedata']['user']['slng'], false);
	$country = $ilance->GPC['country'];
	$removeurlcountry = rewrite_url($php_self, 'countryid=' . urlencode($countryid));
	$sqlquery['location'] .= "AND (p.countryid = '" . intval($countryid) . "' OR p.country = '" . $ilance->db->escape_string($country) . "') ";
}
// #### region selector ################################
$region = (isset($ilance->GPC['region']) AND !empty($ilance->GPC['region'])) ? $ilance->GPC['region'] : '';
$regionname = '';
$regionname = fetch_region_title($region);
if (empty($sqlquery['location']))
{
	$countryids = fetch_country_ids_by_region($regionname);
	$sqlquery['location'] = (!empty($countryids)) ? "AND (FIND_IN_SET(p.countryid, '" . $countryids . "')) " : $sqlquery['location'];
}
// #### link to clear region from left nav menu header
$clear_region = '';
if (!empty($regionname))
{ // region selected
	$removeurl = rewrite_url($php_self, 'region=' . $region);
	$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
	$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['state'])) ? rewrite_url($removeurl, 'state=' . urlencode($ilance->GPC['state'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['city'])) ? rewrite_url($removeurl, 'city=' . urlencode($ilance->GPC['city'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
	$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
	$clear_region = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_region}" rel="nofollow">' . $regionname . '</a>', true);
	handle_search_verbose_save('{_region}: ' . $regionname . ', ');
}
$leftnav_regions = print_regions('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');
// #### finalize country verbose text so it's placed after the region
if ($countryid > 0)
{
	$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . urlencode($ilance->GPC['country'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
	handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_country}" rel="nofollow">' . handle_input_keywords($ilance->GPC['country']) . '</a>', true);
	handle_search_verbose_save('{_country}: ' . handle_input_keywords($ilance->GPC['country']) . ', ');
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
	else
	{
		set_cookie('radiuszip', handle_input_keywords(format_zipcode($ilance->GPC['radiuszip'])), false, true, false, 7);
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
	if (!empty($ilance->GPC['radiuszip']) AND $countryid > 0 AND in_array($countryid, $ilance->distance->accepted_countries))
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
			$radiusresult = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, false, true, false);
			if (!empty($radiusresult) AND is_array($radiusresult) AND count($radiusresult) > 1)
			{
				// the proper zipcode + country id was selected..
				$sqlquery['leftjoin'] .= $radiusresult['leftjoin'];
				$sqlquery['fields'] .= $radiusresult['fields'];
				$sqlquery['radius'] = $radiusresult['condition'];
				$zipcodesrange = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, false, false, true);
				$sqlquery['radius'] .= (isset($zipcodesrange) AND is_array($zipcodesrange)) ? $zipcodesrange['condition'] : '';
				$zipcodecityname = $ilance->distance->fetch_zips_in_range('projects p', 'p.zipcode', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, false, false, false, true);
				handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_radius}" rel="nofollow">' . number_format($ilance->GPC['radius']) . ' ' . $ilconfig['globalserver_distanceresults'] . ' {_from_lower} ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : (!empty($zipcodecityname) ? $zipcodecityname . ', ' : '')) . handle_input_keywords($ilance->GPC['radiuszip']) . '</a>', true);
				handle_search_verbose_save('{_radius}: ' . number_format($ilance->GPC['radius']) . ' ' . $ilconfig['globalserver_distanceresults'] . ' {_from_lower} ' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : '') . handle_input_keywords($ilance->GPC['radiuszip']) . ', ') . '';
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
$clear_local = $removeurl_local = $state = $city = '';
$removeurl = $php_self;
if (!empty($ilance->GPC['city']) AND !empty($ilance->GPC['country']))
{
	// does user enter a city in search?
	$removeurl = rewrite_url($php_self, 'city=' . $ilance->GPC['city']);
	$removeurl_local = rewrite_url($removeurl, 'city=' . $ilance->GPC['city']);
	$ilance->GPC['city'] = ucfirst(trim($ilance->GPC['city']));
	$city = $ilance->GPC['city'];
	$sqlquery['location'] .= "AND (p.city LIKE '%" . $ilance->db->escape_string($ilance->GPC['city']) . "%') ";
	handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_city}" rel="nofollow">' . ucwords(handle_input_keywords($ilance->GPC['city'])) . '</a>', true);
	handle_search_verbose_save('{_city}: ' . ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ');
}
// #### does user search in state or provinces? ########
if (!empty($ilance->GPC['state']) AND !empty($ilance->GPC['country']))
{
	// does user enter a city in search?
	$removeurl = rewrite_url($php_self, 'state=' . $ilance->GPC['state']);
	$removeurl_local = rewrite_url($removeurl_local, 'state=' . $ilance->GPC['state']);
	$ilance->GPC['state'] = ucfirst(trim($ilance->GPC['state']));
	$state = $ilance->GPC['state'];
	$sqlquery['location'] .= "AND (p.state LIKE '%" . $ilance->db->escape_string($ilance->GPC['state']) . "%') ";
	handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_state_or_province}" rel="nofollow">' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</a>', true);
	handle_search_verbose_save('{_state}: ' . ucwords(handle_input_keywords($ilance->GPC['state'])) . ', ');
}
// #### does user search in countries ########
if (!empty($ilance->GPC['country']))
{
	$removeurl = rewrite_url($php_self, 'country=' . $ilance->GPC['country']);
	$removeurl_local = rewrite_url($removeurl_local, 'country=' . $ilance->GPC['country']);
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
	$removeurl = rewrite_url($php_self, 'zip_code=' . $ilance->GPC['zip_code']);
	$removeurl_local = rewrite_url($removeurl_local, 'zip_code=' . $ilance->GPC['zip_code']);
	$sqlquery['location'] .= "AND (p.zip_code LIKE '%" . $ilance->db->escape_string(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zip_code'])))) . "%') ";
	handle_search_verbose_filters('<a href="' . $removeurl . '" title="{_zip_code}" rel="nofollow">' . handle_input_keywords($ilance->GPC['zip_code']) . '</a>', true);
	handle_search_verbose_save('{_zip_slash_postal_code}: ' . handle_input_keywords($ilance->GPC['zip_code']) . ', ');
}
$clear_local = (!empty($removeurl_local)) ? '<a href="' . $removeurl_local . '" rel="nofollow">{_clear}</a>' : '';
unset($removeurl_local);
if (!empty($regionname))
{ // show countries in this region
	$countrypulldown = $ilance->common_location->construct_country_pulldown($countryid,  (!empty($country) ? $country : ''), 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'width:140px', true, true, $regionname, 0, 'city', 'cityid');
	$statepulldown = '<div id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, (!empty($state) ? $state : ''), 'state', ((isset($ilance->GPC['state']) OR isset($ilance->GPC['country'])) ? false : true), true, 0, 'width:140px', 0, 'city', 'cityid') . '</div>';
	$citypulldown = '<div id="cityid">' . $ilance->common_location->construct_city_pulldown($state, 'city', (!empty($city) ? $city : ''),  ((isset($ilance->GPC['city']) OR isset($ilance->GPC['state'])) ? false : true), true, 'width:140px') . '</div>'; //$countryid, $state, 'state', false, false, 0, 'width:140px') . '</div>';
}
else
{ // show countries in all regions
	$countrypulldown = $ilance->common_location->construct_country_pulldown($countryid, (!empty($country) ? $country : ''), 'country', false, 'state', false, false, false, 'stateid', false, '', '', '', 'width:140px', true, false, '', 0, 'city', 'cityid');
	$statepulldown = '<div id="stateid">' . $ilance->common_location->construct_state_pulldown($countryid, (!empty($state) ? $state : ''), 'state', ((isset($ilance->GPC['state']) OR isset($ilance->GPC['country'])) ? false : true), true, 0, 'width:140px', 0, 'city', 'cityid') . '</div>';
	$citypulldown = '<div id="cityid">' . $ilance->common_location->construct_city_pulldown($state, 'city', (!empty($city) ? $city : ''), ((isset($ilance->GPC['city']) OR isset($ilance->GPC['state'])) ? false : true), true, 'width:140px') . '</div>';
}
// #### confirm or reject the ability to see the distance column based on user search preferences
if (is_array($selected['serviceselected']) AND !empty($ilance->GPC['radiuszip']) AND in_array('distance', $selected['serviceselected']))
{
	$show['distancecolumn'] = 1;
}
// #### searchable category questions ##################
$sqlquery['genrequery'] = $sqlquery['leftjoinextra'] = '';
$groupcount = 0;
if (isset($ilance->GPC['qid']) AND !empty($ilance->GPC['qid']))
{
	$sqlquery['leftjoinextra'] = "LEFT JOIN " . DB_PREFIX . "project_answers pans ON (p.project_id = pans.project_id) ";
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
		$row['project_state'] = 'service';
		$td['featured'] = $row['featured'];
		$td['featured_searchresults'] = $row['featured_searchresults'];
		$td['bold'] = $row['bold'];
		$td['highlite'] = $row['highlite'];
		$td['project_id'] = $row['project_id'];
		$td['distance'] = (!empty($countryid) AND !empty($ilance->GPC['radiuszip']) AND (isset($show['distancecolumn']) AND $show['distancecolumn'] == false OR !isset($show['distancecolumn']))) ? $ilance->distance->print_distance_results($row['country'], $row['zipcode'], $countryid, $ilance->GPC['radiuszip'], 0) : '';
		$td['distance_verbose'] = (!empty($countryid) AND !empty($ilance->GPC['radiuszip']) AND (isset($show['distancecolumn']) AND $show['distancecolumn'] == false OR !isset($show['distancecolumn']))) ? '{_from_lowercase} ' . handle_input_keywords($ilance->GPC['radiuszip']) : '';
                $td['username'] = $row['username'];
		$td['city'] = ucfirst($row['city']);
		$td['zipcode'] = $row['zipcode'];//mb_strtoupper($row['zipcode']);
		$td['state'] = ucfirst($row['state']);
		$td['country'] = $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $row['country'] . "'", "location_" . $_SESSION['ilancedata']['user']['slng'], "1");
		$td['countrycode'] = $ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $row['country'] . "'", "cc", "1");
		$td['location'] = $ilance->common_location->print_auction_location($row['project_id'], $_SESSION['ilancedata']['user']['slng'], $row['auction_country'], $td['state'], $td['city'], $td['zipcode']);
		$td['location_distance'] = $td['location'] . ((!empty($countryid) AND !empty($ilance->GPC['radiuszip']) AND (isset($show['distancecolumn']) AND $show['distancecolumn'] == false OR !isset($show['distancecolumn']))) ? ' ' . $ilance->distance->print_distance_results($row['country'], $row['zipcode'], $countryid, $ilance->GPC['radiuszip'], 0) . ' {_from_lowercase} <span class="blue" style="display:inline"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();">' . handle_input_keywords($ilance->GPC['radiuszip']) . '</a></span>' : '');
		$td['location_preferred'] = $td['country'];
		$td['views'] = number_format($row['views']);
		$td['date_starts'] = $row['date_starts'];
		$td['posted'] = print_date($row['date_starts'], 'M d, Y', 0, 0);
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
				? construct_seo_url('serviceauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), 65), $customlink = '', $bold = 1, $searchquestion = '', $questionid = 0, $answerid = 0)
				: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '"><strong>' . shorten(print_string_wrap($row['project_title'], 25), 65) . '</strong></a>';
		}
		else
		{
			$td['title'] = ($ilconfig['globalauctionsettings_seourls'])
				? construct_seo_url('serviceauction', 0, $row['project_id'], shorten(print_string_wrap($row['project_title'], 25), 65), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0)
				: '<a href="' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '">' . shorten(print_string_wrap($row['project_title'], 25), 65) . '</a>';
		}
		$td['title_plain'] = print_string_wrap($row['project_title'], 25);
		// auction description (may contain bbcode)
		switch ($row['project_details'])
		{
			case 'public':
			case 'realtime':
			{
				$td['description'] = strip_tags($row['description']);
				$td['description'] = handle_input_keywords(strip_vulgar_words($td['description']));
				$td['description'] = $ilance->bbcode->strip_bb_tags($td['description']);
				$td['description'] = short_string(print_string_wrap($td['description'], 50), 200);
				$td['additional_info'] = short_string($row['additional_info'], 200);
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
					$td['description'] = short_string(print_string_wrap($td['description'], 50), 200);
					$td['additional_info'] = short_string(print_string_wrap($row['additional_info'], 50), 200);
				}
				unset($sqlinvites);
				break;
			}
		}
		$td['category'] = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']);
		$td['username'] = ($td['show'] == 1) ? $td['username'] : '{_sealed}';
		$td['city'] = ($td['show'] == 1) ? $td['city'] : '{_sealed}';
		$td['zipcode'] = ($td['show'] == 1) ? $td['zipcode'] : '{_sealed}';
		$td['state'] = ($td['show'] == 1) ? $td['state'] : '{_sealed}';
		$td['country'] =  ($td['show'] == 1) ? $td['country'] : '{_sealed}';
		$td['location'] = ($td['show'] == 1) ? $td['location'] : '{_sealed}';
		$td['location_preferred'] = ($td['show'] == 1) ? $td['location_preferred'] : '{_sealed}';
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
		$td['timeleft'] = $ilance->auction->auction_timeleft(false, $row['date_starts'], $row['mytime'], $row['starttime']);
		$td['timeleft_clean'] = $ilance->auction->auction_timeleft(false, $row['date_starts'], $row['mytime'], $row['starttime']);
		$td['timeleft_plain'] = $ilance->auction->auction_timeleft(false, $row['date_starts'], $row['mytime'], $row['starttime'], true);
		$td['timeleft_verbose'] = print_date($row['date_end'], 'M-d-Y h:i:s', 1, 0);
		$td['icons'] =  ($td['show'] == 1) ?  $ilance->auction->auction_icons($row) : '';
		$url = construct_seo_url('serviceauctionplain', 0, $row['project_id'], stripslashes($row['project_title']), '',  0, '', 0, 0);
		$td['url'] = $url;
		unset($url);
		$td['bids'] = ($td['show'] == 1) ? (($row['bids'] > 0) ? $row['bids'] . ' {_bids_lower}' : '0&nbsp;{_bids_lower}') : '{_sealed_bidding}';
		$td['views'] = ($td['show'] == 1) ? number_format($row['views']) : '{_sealed}';
		$td['budget'] = ($td['show'] == 1) ?  $ilance->auction->construct_budget_overview($row['cid'], $row['filtered_budgetid'], true, true,  true) : '{_sealed}';
		$td['skills'] = '{_none}';

		($apihook = $ilance->api('search_results_services_loop')) ? eval($apihook) : false;

		$search_results_rows[] = $td;
		$row_count++;
	}
	$show['no_rows_returned'] = false;
}
else
{
	$show['no_rows_returned'] = true;
}
if (empty($ilance->GPC['do']) OR isset($ilance->GPC['do']) AND ($ilance->GPC['do'] != 'xml' OR $ilance->GPC['do'] != 'array'))
{
	($apihook = $ilance->api('search_results_do_start')) ? eval($apihook) : false;
	
	// #### featured spotlight auction listings ############
	$featuredserviceauctions = $featuredproductauctions = array();
	$kws = !empty($keyword_text) ? '_' . str_replace(' ', '_', $keyword_text) : '';
	$keywords = (!empty($ilance->GPC['q'])) ? un_htmlspecialchars($ilance->GPC['q']) : '';
	$fewer_keywords = print_fewer_keywords_search($keywords_array, $ilance->GPC['mode'], $number);
	$categories = $ilance->categories_parser->print_subcategory_columns(1, 'service', 1, $_SESSION['ilancedata']['user']['slng'], $cid, '', $ilconfig['globalfilters_enablecategorycount'], 0, '', '', 1, '', true, true);
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
	$sortmode = $mode = 'service';
	$navcrumb = array();
	$ilance->categories->breadcrumb($cid, 'servicecatmap', $_SESSION['ilancedata']['user']['slng']);
	if (empty($cid) OR $cid == 0 OR $cid == '')
	{
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$navcrumb[HTTP_SERVER . "search?tab=1"] = '{_search}';
			$navcrumb[""] = '{_services}';
		}
		else
		{
			$navcrumb["$ilpage[search]?tab=1"] = '{_search}';
			$navcrumb[""] = '{_services}';
		}
	}
	// #### category budget ################################
	$budget = isset($ilance->GPC['budget']) ? intval($ilance->GPC['budget']) : '';
	$budgettemp = $ilance->auction_post->print_budget_logic_type_js($cid, $ilance->GPC['mode'], $budget);
	if (isset($show['budgetgroups']) AND $show['budgetgroups'] AND is_array($budgettemp))
	{
		$budget_slider_1 = $budgettemp[0];
		$budget_slider_2 = $budgettemp[1];
	}
	unset($budgettemp);
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
	$sortpulldown = print_sort_pulldown($ilance->GPC['sort'], 'sort', $sortmode);
	$city = isset($ilance->GPC['city']) ? handle_input_keywords($ilance->GPC['city']) : '';
	$state = isset($ilance->GPC['state']) ? handle_input_keywords($ilance->GPC['state']) : '';
	$zip_code = isset($ilance->GPC['zip_code']) ? handle_input_keywords($ilance->GPC['zip_code']) : '';
	$radiuszip = isset($ilance->GPC['radiuszip']) ? handle_input_keywords($ilance->GPC['radiuszip']) : '';
	$hiddenfields = print_hidden_fields(false, array('searchid','sef','cid','buynow','sort','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','q','page'), false, '', '', true, true);
	$hiddenfields_leftnav = print_hidden_fields(false, array('city','state','searchid','sef','exactname','searchuser','budget','country','auctiontype','buynow','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','page','radius','radiuscountry','radiuszip','fromprice','toprice'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
	$hiddenfields_radius_leftnav = print_hidden_fields(false, array('searchid','sef','exactname','searchuser','budget','auctiontype','buynow','images','freeshipping','listedaslots','budget','publicboard','escrow','underage','endstart','endstart_filter','page','radius','radiuscountry','radiuszip','fromprice','toprice'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
	$temp = print_pagnation_v4($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $php_self, 'page', false, true, true);
	//$prevnext = print_pagnation($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $scriptpage);
	$prevnext = $temp[0];
	$prevnextmini = $temp[1];
	$search_results_table = print_search_results_table_v4($search_results_rows, $project_state, $prevnext);
	//$search_results_table = print_search_results_table($search_results_rows, $project_state, $prevnext);
	unset($temp);
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
		$favtext = '{_keywords}: ' . strip_tags($keyword_formatted_favtext) . ', ' . strip_tags($vebsave);
		$favtext = handle_input_keywords(mb_substr($favtext, 0, -2));
		$vebsave = print_search_verbose_saved('verbose'); // {_in} Mobile & Accessories,
		if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
		{
			$text = '<strong>' . number_format($number) . '</strong> ' . (($number <= 1) ? '{_listing_found_with_keywords}' : '{_listings_found_with_keywords}') . ' ' . stripslashes($keyword_formatted) . '';
		}
		else
		{
			$text = '<strong>' . number_format($number) . '</strong> ' . (($number <= 1) ? '{_listing_found_with_keywords}' : '{_listings_found_with_keywords}') . ' ' . stripslashes($keyword_formatted) . ' ' . $vebsave;
			$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
		}
		unset($vebsave);
		$text = '<span id="verbosetext" class="black">' . $text . '</span>';
	}
	else
	{
		$vebsave = print_search_verbose_saved('verbose_save');
		$favtext = strip_tags($vebsave);
		$favtext = handle_input_keywords(mb_substr($favtext, 0, -2));
		$vebsave = print_search_verbose_saved('verbose'); // {_in} Mobile & Accessories,
		if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
		{
			$text = '<strong>' . number_format($number) . '</strong> ' . (($number <= 1) ? '{_listing_found_with_no_keywords}' : '{_listings_found_with_no_keywords}');
		}
		else
		{
			$text = '<strong>' . number_format($number) . '</strong> ' . (($number <= 1) ? '{_listing_found_with_no_keywords}' : '{_listings_found_with_no_keywords}') . ' ' . $vebsave;
			$text = !empty($vebsave) ? mb_substr($text, 0, -2) : $text;
		}
		unset($vebsave);
		$text = '<span id="verbosetext" class="black">' . $text . '</span>';
	}
	// #### build our search filters logic
	$searchfilters = print_search_verbose_saved('verbose_filter', true);
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
	// #### save this search ###############################
	if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0)
	{
		// todo: add hit tracker to show hit count of saved search
		$savesearchlink = '';
	}
	else
	{
		$savesearchlink = '<span class="smaller blueonly"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow()">{_save_as_favorite_search}</a><span style="float:left;margin-right:3px;margin-top:5px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_bookmark.png" border="0" alt="" /></span></span>&nbsp;&nbsp;&nbsp; <span class="smaller gray">|</span> &nbsp;&nbsp;&nbsp;';
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
	$distancesymbol = $ilconfig['globalserver_distanceresults'];
	$area_title = '{_search_results_display}<div class="smaller">' . $metatitle . '</div>' . ((isset($keyword_text) AND !empty($keyword_text)) ? '<div class="smaller">{_keywords}: ' . handle_input_keywords($keyword_text) . '</div>' : '');
	$page_title = ((isset($keyword_text) AND !empty($keyword_text))
		? ((!empty($metatitle))
			? handle_input_keywords($res_title_new) . ' | ' . handle_input_keywords(str_replace('"', '', $keyword_text))
			: handle_input_keywords(str_replace('"', '', $keyword_text)))
		: ((!empty($metatitle))
			? handle_input_keywords($res_title_new)
			: '{_search}')) . ' | ' . SITE_NAME;
	$ilance->template->fetch('main', 'search_results' . ((defined('TEMPLATE_NEWUI') AND defined('TEMPLATE_NEWUI_MODE') AND TEMPLATE_NEWUI AND TEMPLATE_NEWUI_MODE != '') ? '_' . TEMPLATE_NEWUI_MODE : '') . '.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('featuredproductauctions','featuredserviceauctions'));
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('categoryfinderhtml','searchfilters','searchlistactive','searchlisturl','searchgalleryactive','searchgalleryurl','searchlisturl','distancesymbol','hiddenfields_radius_leftnav','prevnextmini','countrypulldown','statepulldown','citypulldown','categories','categoryname','savesearchlink','legend','search_color_pulldown','clear_budgetrange','clear_local','redirect_not_login','clear_distance','clear_searchuser_url','country','leftnav_options','leftnav_currencies','clear_currencies','clear_options','sort','clear_bidrange','clear_color','clear_listtype','leftnav_buyingformats','showallurl','clear_searchuser','clear_price','clear_region','leftnav_regions','full_country_pulldown','didyoumean','searchuser','search_bidrange_pulldown_service','search_bidrange_pulldown_product','search_country_pulldown_product','search_country_pulldown_service','search_radius_country_pulldown_product','search_radius_country_pulldown_service','budget_slider_1','budget_slider_2','favtext','profilebidfilters','fewer_keywords','budgetfilter','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','hiddenfields','fromprice','toprice','search_results_table','sortpulldown','keywords','favoritesearchurl','keywords','search_product_category_pulldown','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','search_country_pulldown','search_country_pulldown2','search_jobtype_pulldown','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','distance','subcategory_name','text','prevnext','prevnext2','default_exchange_rate');

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