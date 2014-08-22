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

$show['mode_service'] = false;
$show['mode_providers'] = true;
$mode = 'experts';
$text = $favtext = $subcatname = $keyword_text = '';
$mode_buynow = 0;

// #### page we're on ##################################
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
$sqlquery['relevance'] = '';
$sqlquery['groupby'] = "GROUP BY user_id";
$sqlquery['orderby'] = "ORDER BY u.rating DESC";
$sqlquery['limit'] = 'LIMIT ' . (($ilance->GPC['page'] - 1) * fetch_perpage()) . ',' . fetch_perpage();

// #### accepted display order sorting #################
$acceptedsort = array('41','42','51','52','61','62','71','72','81','82','91','92','101','102','111','112');
// be sure user entered keyword before sorting by relevance
if (isset($ilance->GPC['q']) AND !empty($ilance->GPC['q']))
{
	$acceptedsort[] = '123';
	$acceptedsort[] = '124';
}

// #### put our keyword text in a temporary variable ###
if (!empty($ilance->GPC['q']))
{
	$keyword_text = $ilance->GPC['q'];
	$keyword_text = $ilance->common->xss_clean($keyword_text);
}

// #### EXCLUDED EXPERTS SQL QUERY BUILDER #############
// subscription permissions checkup for 'searchresults'
// this will build a list of user id's not to include in the search
// this will also build a list of user id's that do not wish to be listed in search
// the 'u.' represents the table field identifier (example: u.user_id vs no identifier: user_id) - useful for TABLE JOINS
//$sqlquery['userquery'] = build_expert_search_exclusion_sql('u.', 'searchresults');
$sqlquery['userquery'] = '';

// #### BEGIN SEARCH SQL QUERY #########################
$sqlquery['timestamp'] = $sqlquery['projectstatus'] = $sqlquery['projectdetails'] = $sqlquery['projectstate'] = $sqlquery['pricerange'] = '';
$sqlquery['fields'] = "u.user_id, u.username, u.city, u.state, u.zip_code, u.country, u.status, u.serviceawards, u.rating, u.score, u.profileintro, p.cid";
$sqlquery['from'] = "FROM " . DB_PREFIX . "users u";
$sqlquery['leftjoin'] = "LEFT JOIN " . DB_PREFIX . "profile_categories p ON u.user_id = p.user_id
LEFT JOIN " . DB_PREFIX . "portfolio o ON p.user_id = o.user_id
LEFT JOIN " . DB_PREFIX . "attachment l ON u.user_id = l.user_id
LEFT JOIN " . DB_PREFIX . "locations c ON u.country = c.locationid";

// hook below is useful for changing any specifics from the above
($apihook = $ilance->api('search_experts_query_fields')) ? eval($apihook) : false;

$sqlquery['genrequery'] = $sqlquery['keywords'] = $keyword_formatted = '';

// #### handle keywords entered by user ################
$keywords_array = array();
if (isset($keyword_text) AND !empty($keyword_text))
{
	// #### FULLTEXT MODE ##########################
	if ($ilconfig['fulltextsearch'])
	{
		$sqlquery['fields'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios'])
			? ", MATCH (u.username, u.profileintro, o.description, o.caption, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (u.username) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$sqlquery['relevance'] = (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios'])
			? ", MATCH (u.username, u.profileintro, o.description, o.caption, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance"
			: ", MATCH (u.username) AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE) AS relevance";
		$keyword_formatted .= '<strong><span title="' . $keyword_text . '">' . shorten($keyword_text, 20) . '</span></strong>, ';
		$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
		$keyword_formatted_favtext = $keyword_formatted;
		$sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios'])
			? "AND MATCH (u.username, u.profileintro, o.description, o.caption, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)"
			: "AND MATCH (u.username, u.profileintro, c.location_" . $_SESSION['ilancedata']['user']['slng'] . ") AGAINST ('" . $ilance->db->escape_string($keyword_text) . "' IN BOOLEAN MODE)";
	}
	// #### NON-FULLTEXT MODE ######################
	else
	{
		// #### splits spaces and commas into array
		$keyword_text_array = preg_split("/[\s,]+/", trim($keyword_text));

		// #### multiple keywords detected #####
		if (sizeof($keyword_text_array) > 1)
		{
			$sqlquery['keywords'] .= 'AND (';
			for ($i = 0; $i < sizeof($keyword_text_array); $i++)
			{
				$keyword_formatted .= '<strong><span title="' . $keyword_text_array[$i] . '">' . shorten($keyword_text_array[$i], 20) . '</span></strong>, ';
				$sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios']) ? " u.username LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR o.description LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR o.caption LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR " : " u.username LIKE '%" . $ilance->db->escape_string($keyword_text_array[$i]) . "%' OR ";
			}
			$sqlquery['keywords'] = mb_substr($sqlquery['keywords'], 0, -4) . ')';
			$keyword_formatted = mb_substr($keyword_formatted, 0, -2) . '';
			$keyword_formatted_favtext = $keyword_formatted;
		}

		// #### single keyword #################
		else
		{
			$keyword_formatted = '<strong><span title="' . $keyword_text_array[0] . '">' . shorten($keyword_text_array[0], 20) . '</span></strong>';
			$keyword_formatted_favtext = '<strong>' . $keyword_text_array[0] . '</strong>';
			$sqlquery['keywords'] .= (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios']) ? "AND (u.username LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR o.description LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%' OR o.caption LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%') " : "AND (u.username LIKE '%" . $ilance->db->escape_string($keyword_text_array[0]) . "%') ";
		}
	}
}

// #### categories #####################################
$sqlquery['categories'] = '';
if (empty($cid) OR (!empty($cid) AND $cid == 0))
{
	// we are here because the searcher chose no categories to search within
	$cid = 0;
	$subcategorylist = $ilance->categories->fetch_children_ids('all', 'service');
	$count = count(explode(',', $subcategorylist));
	if (!empty($subcategorylist) AND $count > 1)
	{
		$sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
	}
	handle_search_verbose('<strong><span class="gray">{_in}</span>&nbsp;<span class="black">{_best_matching_categories}</span></strong>, ');
	handle_search_verbose_save('{_category}: <strong>{_best_matching}</strong>, ');
}
else
{
	$subcategorylist = $subcatname = '';
	$cid = isset($cid) ? intval($cid) : '0';
	// category visibility checkup
	if ($ilance->categories->visible($cid) == 0)
	{
		$area_title = '{_category_not_available}';
		$page_title = SITE_NAME . ' - {_category_not_available}';
		print_notice('{_invalid_category}', '{_this_category_is_currently_unavailable_please_choose_a_different_category}', $ilpage['search'], '{_search}');
		exit();
	}
	if ($cid > 0)
	{
		$subcatname .= ', <span class="black">' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid) . '</span>';
		$ilance->categories->fetch = $ilance->categories->catservicefetch;
		$childrenids = $ilance->categories->fetch_children_ids($cid, 'service');
		$subcategorylist .= $cid . ',' . $childrenids;
		if (!empty($subcatname))
		{
			$removeurl = rewrite_url($scriptpage, $remove = 'cid=' . $cid);

			handle_search_verbose('<strong><span class="gray">{_in}</span> <span class="black">' . mb_substr($subcatname, 1) . '</span></strong>, ');
			handle_search_verbose_save('{_categories}: <strong>' . mb_substr($subcatname, 1) . '</strong>, ');
		}

		$sqlquery['categories'] .= "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		$ilance->categories->add_category_viewcount($cid);
	}
}
// #### handle keywords and add new words/phrases to our search index table
if (!empty($keyword_text))
{
	handle_search_keywords($keyword_text, 'experts', $cid);
}
if (isset($ilance->GPC['sort']) AND !empty($ilance->GPC['sort']) AND in_array($ilance->GPC['sort'], $acceptedsort, true))
{
	$sphrase = fetch_sort_options('experts');
	$tphrase = $sphrase[$ilance->GPC['sort']];
	$sortconditions = sortable_array_handler('experts');
	$sqlquery['orderby'] = 'ORDER BY ' . $sortconditions[$ilance->GPC['sort']]['field'] . ' ' . $sortconditions[$ilance->GPC['sort']]['sort'] . ' ' . $sortconditions[$ilance->GPC['sort']]['extra'];
	unset($sphrase, $tphrase);
}

// #### default sort display order if none selected ####
else
{
	$ilance->GPC['sort'] = '52';
	$sqlquery['orderby'] = "ORDER BY u.rating DESC";
	$sphrase = fetch_sort_options('experts');
	$tphrase = $sphrase['52'];
	unset($sphrase, $tphrase);
}

// #### hold display order for modals as sort is removed due to main search bar above listings
$sort = $ilance->GPC['sort'];
// #### search options: is user hiding their own results?
$sqlquery['hidequery'] = '';
if ($selected['hidelisted'] == 'true')
{
	if (!empty($_SESSION['ilancedata']['user']['userid']))
	{
		$sqlquery['hidequery'] = "AND (u.user_id != '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
	}
	handle_search_verbose('<span class="black"><strong>{_excluding_results_that_are_listed_by_me}</strong></span>, ');
	handle_search_verbose_save('{_filter}: <strong>{_excluding_results_that_are_listed_by_me_uppercase}</strong>, ');
}
// #### user searching keywords within portfolio titles and descriptions?
if (isset($ilance->GPC['portfolios']) AND $ilance->GPC['portfolios'])
{
	$removeurl = rewrite_url($scriptpage, $remove = 'portfolios=' . $ilance->GPC['portfolios']);

	handle_search_verbose('<span class="black"><strong>{_including_service_experts_portfolios}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_titles_and_descriptions_within_portfolios}</strong>, ');
}
$navcrumb = array();
$ilance->categories->breadcrumb($cid, 'experts', $_SESSION['ilancedata']['user']['slng']);
if (empty($cid) OR $cid == 0 OR $cid == '')
{
	$navcrumb[""] = '{_providers}';
}
// #### filtering search via number of service auction awards
$sqlquery['options'] = '';
if (!empty($ilance->GPC['projectrange']) AND $ilance->GPC['projectrange'] != '-1')
{
	$removeurl = rewrite_url($scriptpage, $remove = 'projectrange=' . $ilance->GPC['projectrange']);
	switch ($ilance->GPC['projectrange'])
	{
		case '1':
		{
			$sqlquery['options'] .= "AND (u.serviceawards < 10) ";
			handle_search_verbose('<span class="black"><strong>{_with_less_than_ten_awards}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_awards}: <strong>{_with_less_than_ten_awards}</strong>, ');
			break;
		}
		case '2':
		{
			$sqlquery['options'] .= "AND (u.serviceawards BETWEEN 10 AND 20) ";
			handle_search_verbose('<span class="black"><strong>{_between_ten_and_twenty_awards}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_awards}: <strong>{_between_ten_and_twenty_awards}</strong>, ');
			break;
		}
		case '3':
		{
			$sqlquery['options'] .= "AND (u.serviceawards > 20) ";
			handle_search_verbose('<span class="black"><strong>{_with_more_than_twenty_awards}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_awards}: <strong>{_with_more_than_twenty_awards}</strong>, ');
			break;
		}
	}
}

$ilance->GPC['projectrange'] = isset($ilance->GPC['projectrange']) ? $ilance->GPC['projectrange'] : '';
$clear_award = '';
$leftnav_awardrange = print_award_range_pulldown($ilance->GPC['projectrange'], 'projectrange', 'projectrange', 'links');

// #### search filter via rating? ######################
if (!empty($ilance->GPC['rating']) AND $ilance->GPC['rating'] != '0')
{
	$removeurl = rewrite_url($scriptpage, $remove = 'rating=' . $ilance->GPC['rating']);
	switch ($ilance->GPC['rating'])
	{
		case '5':
		{
			$sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_5_stars_value'] . "') ";
			handle_search_verbose('<span class="black"><strong>{_with_at_least_a_five_star_rating}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_rating}: <strong>{_with_at_least_a_five_star_rating}</strong>, ');
			break;
		}
		case '4':
		{
			$sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_4_stars_value'] . "') ";
			handle_search_verbose('<span class="black"><strong>{_with_at_least_a_four_star_rating}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_rating}: <strong>{_with_at_least_a_four_star_rating}</strong>, ');
			break;
		}
		case '3':
		{
			$sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_3_stars_value'] . "') ";
			handle_search_verbose('<span class="black"><strong>{_with_at_least_a_three_star_rating}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_rating}: <strong>{_with_at_least_a_three_star_rating}</strong>, ');
			break;
		}
		case '2':
		{
			$sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_2_stars_value'] . "') ";
			handle_search_verbose('<span class="black"><strong>{_with_at_least_a_two_star_rating}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_rating}: <strong>{_with_at_least_a_two_star_rating}</strong>, ');
			break;
		}
		case '1':
		{
			$sqlquery['options'] .= "AND (u.rating >= '" . $ilconfig['min_1_stars_value'] . "') ";
			handle_search_verbose('<span class="black"><strong>{_with_at_least_a_one_star_rating}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_rating}: <strong>{_with_at_least_a_one_star_rating}</strong>, ');
			break;
		}
	}
}

$ilance->GPC['rating'] = isset($ilance->GPC['rating']) ? $ilance->GPC['rating'] : '';
$clear_rating = '';
$leftnav_ratingrange = print_rating_range_pulldown($ilance->GPC['rating'], 'rating', 'rating', 'links');

// #### search filter via feedback rating? #############
if (!empty($ilance->GPC['feedback']) AND $ilance->GPC['feedback'] != '0')
{
	$removeurl = rewrite_url($scriptpage, 'feedback=' . $ilance->GPC['feedback']);
	switch ($ilance->GPC['feedback'])
	{
		case '5':
		{
			$sqlquery['options'] .= "AND (u.feedback >= '95') ";
			handle_search_verbose('<span class="black"><strong><span class="gray">{_feedback}:</span> {_above_95_positive}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_feedback}: <strong>{_above_95_positive}</strong>, ');
			break;
		}
		case '4':
		{
			$sqlquery['options'] .= "AND (u.feedback >= '90') ";
			handle_search_verbose('<span class="black"><strong><span class="gray">{_feedback}:</span> {_above_90_positive}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_feedback}: <strong>{_above_90_positive}</strong>, ');
			break;
		}
		case '3':
		{
			$sqlquery['options'] .= "AND (u.feedback >= '85') ";
			handle_search_verbose('<span class="black"><strong><span class="gray">{_feedback}:</span> {_above_85_positive}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_feedback}: <strong>{_above_85_positive}</strong>, ');
			break;
		}
		case '2':
		{
			$sqlquery['options'] .= "AND (u.feedback >= '75') ";
			handle_search_verbose('<span class="black"><strong><span class="gray">{_feedback}:</span> {_above_75_positive}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_feedback}: <strong>{_above_75_positive}</strong>, ');
			break;
		}
		case '1':
		{
			$sqlquery['options'] .= "AND (u.feedback >= '50') ";
			handle_search_verbose('<span class="black"><strong><span class="gray">{_feedback}:</span> {_above_50_positive}</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_feedback}: <strong>{_above_50_positive}</strong>, ');
			break;
		}
	}
}

$ilance->GPC['feedback'] = isset($ilance->GPC['feedback']) ? $ilance->GPC['feedback'] : '';
$clear_feedback = '';
$leftnav_feedbackrange = print_feedback_range_pulldown($ilance->GPC['feedback'], 'feedback', 'feedback', 'links');

// #### search via country #############################
$sqlquery['location'] = $country = $countryid = $countryids = '';
$removeurlcountry = $php_self;
if (!empty($ilance->GPC['country']))
{
	// #### populate proper country id #############
	$countryid = fetch_country_id($ilance->GPC['country'], $_SESSION['ilancedata']['user']['slng']);
	$country = $ilance->GPC['country'];

	// #### populate regional information ##########
	$ilance->GPC['region'] = $ilance->shipping->fetch_region_by_countryid($countryid, false);
	$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
	$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;
	$removeurlcountry = rewrite_url($php_self, 'country=' . urlencode($ilance->GPC['country']));
	$sqlquery['location'] .= "AND (u.country = '" . intval($countryid) . "') ";
}
else if (!empty($ilance->GPC['countryid']))
{
	// #### populate proper country name ###########
	$countryid = $ilance->GPC['countryid'];
	$ilance->GPC['country'] = $ilance->common_location->print_country_name(intval($countryid), $_SESSION['ilancedata']['user']['slng'], $shortform = false);
	$country = $ilance->GPC['country'];

	// #### populate regional information ##########
	$ilance->GPC['region'] = $ilance->shipping->fetch_region_by_countryid($countryid, false);
	$ilance->GPC['region'] = mb_strtolower(str_replace(' ', '_', $ilance->GPC['region']));
	$ilance->GPC['region'] = $ilance->GPC['region'] . '.' . $countryid;

	$removeurlcountry = rewrite_url($php_self, 'countryid=' . urlencode($countryid));
	$sqlquery['location'] .= "AND (u.country = '" . intval($countryid) . "') ";
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

		// #### populate our selected country via special region type url
		$countryid = $regtemp[1];
		$ilance->GPC['country'] = $ilance->common_location->print_country_name(intval($countryid), $_SESSION['ilancedata']['user']['slng'], false);

		// #### build our sql country region query
		$sqlquery['location'] = "AND (u.country = '" . intval($countryid) . "') ";
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
	$sqlquery['location'] = (!empty($countryids)) ? "AND (FIND_IN_SET(u.country, '" . $countryids . "')) " : "";
}

// #### link to clear region from left nav menu header
$clear_region = '';
if (!empty($regionname))
{
	$removeurl = rewrite_url($php_self, 'region=' . $region);
	$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
	$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
	$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
	$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
	$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
	$clear_region = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	handle_search_verbose('<span class="gray"><!--<strong>{_region}: --><span class="black"><strong>' . $regionname . '</strong></span></strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_region}: <strong>' . $regionname . '</strong>, ');
}
$leftnav_regions = print_regions('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');

// #### finalize country verbose text so it's placed after the region
if ($countryid > 0)
{
	handle_search_verbose('<span class="black"><!--<span class="gray">{_country}:</span> --><strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_country}: <strong>' . handle_input_keywords($ilance->GPC['country']) . '</strong>, ');
}
// #### search via radius ##############################
$show['radiussearch'] = false;
$sqlquery['radius'] = $clear_distance = '';
if ($ilconfig['globalserver_enabledistanceradius'] AND !empty($ilance->GPC['radiuszip']) AND $countryid > 0)
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
		$radiusresult = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = true, $radiusjoin = false);
		if (!empty($radiusresult) AND is_array($radiusresult) AND count($radiusresult) > 1)
		{
			// the proper zipcode + country id was selected..
			$sqlquery['leftjoin'] .= $radiusresult['leftjoin'];
			$sqlquery['fields'] .= $radiusresult['fields'];
			$sqlquery['radius'] = $radiusresult['condition'];
			$zipcodesrange = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = true);
			$sqlquery['radius'] .= (isset($zipcodesrange) AND is_array($zipcodesrange)) ? $zipcodesrange['condition'] : '';
			$zipcodecityname = $ilance->distance->fetch_zips_in_range('users u', 'u.zip_code', $ilance->GPC['radiuszip'], $ilance->GPC['radius'], $radiuscountryid, $includedistance = false, $leftjoinonly = false, $radiusjoin = false, $fetchcityonly = true);
			handle_search_verbose('<span class="black"><!--<span class="gray">{_radius}:</span> --><strong>' . number_format($ilance->GPC['radius']) . '</strong> {_mile_radius_from} <strong>' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : (!empty($zipcodecityname) ? $zipcodecityname . ', ' : '')) . handle_input_keywords($ilance->GPC['radiuszip']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
			handle_search_verbose_save('{_radius}: <strong>' . number_format($ilance->GPC['radius']) . '</strong> {_mile_radius_from} <strong>' . (!empty($ilance->GPC['city']) ? ucwords(handle_input_keywords($ilance->GPC['city'])) . ', ' : '') . handle_input_keywords($ilance->GPC['radiuszip']) . ', ') . '</strong>';
		}
	}
	$clear_distance = ((!empty($ilance->GPC['radius']) AND $ilance->GPC['radius'] > 0)
	? '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>'
	: '');

	if ($ilconfig['globalserver_enabledistanceradius'])
	{
		$acceptedsort2 = array('121','122');
		$acceptedsort = array_merge($acceptedsort, $acceptedsort2);
		unset($acceptedsort2);
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
	handle_search_verbose('<span class="black"><!--<span class="gray">{_state_or_province}:</span> --><strong>' . ucwords(handle_input_keywords($ilance->GPC['state'])) . '</strong></span> <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_state}: <strong>' . ucwords($ilance->GPC['state']) . '</strong>, ');
}
// #### does user search in zip codes? #################
if (!empty($ilance->GPC['zip_code']) AND !empty($ilance->GPC['country']))
{
	$removeurl = rewrite_url($scriptpage, 'zip_code=' . $ilance->GPC['zip_code']);
	$removeurl_local = rewrite_url($removeurl_local, 'zip_code=' . $ilance->GPC['zip_code']);
	$ilance->GPC['zip_code'] = mb_strtoupper(trim($ilance->GPC['zip_code']));
	$distanceresult = $ilance->distance->fetch_sql_as_distance($ilance->GPC['zip_code'], $ilance->GPC['country'], 'u.zip_code');
	if (is_array($distanceresult))
	{
		$sqlquery['leftjoin'] .= $distanceresult['leftjoin'];
		$sqlquery['fields'] .= $distanceresult['fields'];
	}
	$sqlquery['location'] .= "AND (u.zip_code LIKE '%" . $ilance->db->escape_string(mb_strtoupper(trim(str_replace(' ', '', $ilance->GPC['zip_code'])))) . "%') ";
	handle_search_verbose('<span class="black"><!--{_zip_code}: --><strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_zip_slash_postal_code}: <strong>' . handle_input_keywords($ilance->GPC['zip_code']) . '</strong>, ');
}
$clear_local = (!empty($removeurl_local)) ? '<a href="' . $removeurl_local . '" rel="nofollow">{_clear}</a>' : '';
unset($removeurl_local);
if (is_array($selected['expertselected']) AND (!empty($_SESSION['ilancedata']['user']['postalzip']) OR !empty($ilance->GPC['zip_code']) OR !empty($ilance->GPC['radiuszip'])) AND in_array('distance', $selected["expertselected"]))
{
	$show['distancecolumn'] = 1;
}
// #### show only with active profile logos ############
if (isset($ilance->GPC['images']) AND $ilance->GPC['images'])
{
	$removeurl = rewrite_url($scriptpage, $remove = 'images=' . $ilance->GPC['images']);
	$sqlquery['options'] .= "AND (l.user_id = u.user_id AND l.visible = '1' AND l.attachtype = 'profile') ";
	handle_search_verbose('<span class="black">{_with_active_profile_logos_only}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_with_active_profile_logos_only}</strong>, ');
}
// #### show only online logged in members #############
if (isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline'])
{
	$removeurl = rewrite_url($scriptpage, $remove = 'isonline=' . $ilance->GPC['isonline']);
	$sqlquery['options'] .= "AND (s.userid = u.user_id) ";
	handle_search_verbose('<span class="black">{_showing_members_online_and_logged_in}</span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_filter}: <strong>{_showing_members_online_and_logged_in}</strong>, ');
}
// #### currency selector ##############################
$clear_currencies = '';
if ($ilconfig['globalserverlocale_currencyselector'])
{
	$ilance->GPC['cur'] = isset($ilance->GPC['cur']) ? handle_input_keywords($ilance->GPC['cur']) : '';
	$joinextra = ", " . DB_PREFIX . "profile_categories AS p";
	$leftnav_currencies = print_currencies('users AS u', 'u.currencyid', $ilance->GPC['cur'], $ilconfig['globalserverlocale_currencycatcutoff'], "AND p.user_id = u.user_id AND u.status = 'active' $sqlquery[categories]", $joinextra);
	$clear_currencies = !empty($clear_currencies_all) ? '<a href="' . $clear_currencies_all . '" rel="nofollow">{_clear}</a>' : '';
	$sqlquery['options'] .= (!empty($ilance->GPC['cur'])) ? "AND (FIND_IN_SET(u.currencyid, '" . $ilance->db->escape_string($ilance->GPC['cur']) . "')) " : '';
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
// #### handle hourly rate price range #################
$clear_price = '';
$removeurl = $scriptpage;
if (!empty($ilance->GPC['fromprice']) AND $ilance->GPC['fromprice'] > 0)
{
	$sqlquery['pricerange'] .= "AND (u.rateperhour >= " . intval($ilance->GPC['fromprice']) . " ";
	$removeurl = rewrite_url($removeurl, 'fromprice=' . urldecode($ilance->GPC['fromprice']));
	handle_search_verbose('<span class="black">{_min_hourly_rate}: ' . $ilance->currency->format($ilance->GPC['fromprice']) . '</span>, ');
	handle_search_verbose_save('{_min_hourly_rate}: <strong>' . $ilance->currency->format($ilance->GPC['fromprice']) . '</strong>, ');
}
else
{
	$sqlquery['pricerange'] .= "AND (u.rateperhour >= 0 ";
}
if (!empty($ilance->GPC['toprice']) AND $ilance->GPC['toprice'] > 0)
{
	$sqlquery['pricerange'] .= "AND u.rateperhour <= " . intval($ilance->GPC['toprice']) . ") ";
	$removeurl = rewrite_url($removeurl, 'toprice=' . urldecode($ilance->GPC['toprice']));
	$clear_price = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	handle_search_verbose('<span class="black">{_max_hourly_rate}: ' . $ilance->currency->format($ilance->GPC['toprice']) . '</span> <!--<a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_max_hourly_rate}: <strong>' . $ilance->currency->format($ilance->GPC['toprice']) . '</strong>, ');
}
else
{
	$sqlquery['pricerange'] .= ")";
}
unset($removeurl);
$fromprice = (isset($ilance->GPC['fromprice']) AND !empty($ilance->GPC['fromprice'])) ? sprintf("%01.2f", $ilance->GPC['fromprice']) : '';
$toprice = (isset($ilance->GPC['toprice']) AND !empty($ilance->GPC['toprice'])) ? sprintf("%01.2f", $ilance->GPC['toprice']) : '';
// #### multiple skills being searched #################
$sqlquery['skillsquery'] = "";
if (isset($ilance->GPC['sid']) AND is_array($ilance->GPC['sid']))
{
	$sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
	$findinset = $showtextskills = $favtextskills = '';
	foreach ($ilance->GPC['sid'] AS $sid => $value)
	{
		if (isset($sid) AND $sid > 0 AND isset($value) AND !empty($value))
		{
			$answertitle = print_skill_title($sid);
			$showqidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
			$showqidurl = rewrite_url($showqidurl, $remove = 'sid[' . $sid . ']=' . $value);
			$showtextskills .= '<span class="black"><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ';
			$favtextskills .= '<strong>' . $answertitle . '</strong>, ';
			$findinset .= "$sid,";
		}
	}
	$sqlquery['skillsquery'] = "AND (FIND_IN_SET(a.cid, '$findinset'))";
	handle_search_verbose_filters('<span class="black"><strong>{_matching_skills}</strong></span>&nbsp;' . $showtextskills . '');
	handle_search_verbose_save('{_skills}: ' . $favtextskills);
}
// #### single skill category being searched ###########
else if (isset($ilance->GPC['sid']) AND $ilance->GPC['sid'] > 0 AND !is_array($ilance->GPC['sid']))
{
	$answertitle = print_skill_title($ilance->GPC['sid']);
	$showqidurl = $ilpage['search'] . print_hidden_fields(true, array('page','sid','searchid'), true, '', '', true, true);
	$showqidurl = rewrite_url($showqidurl, $remove = 'sid=' . $ilance->GPC['sid']);
	$sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
	$sqlquery['skillsquery'] = "AND (a.cid = '" . intval($ilance->GPC['sid']) . "')";
	handle_search_verbose_filters('<span class="black"><strong>{_matching_skills}</strong></span> <span class="black"><strong>' . $answertitle . '</strong></span><!-- <a href="' . $showqidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_skills}: <strong>' . $answertitle . '</strong>, ');
}

// #### match any skills we can based on user any supplied keywords
else
{
	if (isset($keyword_text) AND !empty($keyword_text))
	{
		$sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "skills_answers a ON u.user_id = a.user_id ";
		$sqlquery['skillsquery'] = build_skills_inclusion_sql('a.', $keyword_text);
		if (!empty($sqlquery['keywords']) AND !empty($sqlquery['skillsquery']))
		{
			$sqlquery['keywords'] = substr($sqlquery['keywords'], 4);
			$sqlquery['keywords'] = "AND (" . $sqlquery['keywords'] . " OR ";
			$sqlquery['skillsquery'] = substr($sqlquery['skillsquery'], 4);
			$sqlquery['skillsquery'] = $sqlquery['skillsquery'] . ")";
			// #### build a special keywords query
			$sqlquery['keywords'] = $sqlquery['keywords'] . $sqlquery['skillsquery'];
			$sqlquery['skillsquery'] = '';
		}
	}
}
// #### link to clear skills from left nav menu header
$clear_skills = '';
if (!empty($regionname))
{
	$removeurl = rewrite_url($php_self, 'region=' . $region);
	$removeurl = rewrite_url($removeurl, 'regiontype=' . $regiontype);
	$removeurl = ($countryid > 0) ? rewrite_url($removeurl, 'countryid=' . $countryid) : $removeurl;
	$removeurl = (isset($ilance->GPC['country'])) ? rewrite_url($removeurl, 'country=' . $ilance->GPC['country']) : $removeurl;
	$removeurl = (isset($ilance->GPC['radiuszip'])) ? rewrite_url($removeurl, 'radiuszip=' . urlencode($ilance->GPC['radiuszip'])) : $removeurl;
	$removeurl = (isset($ilance->GPC['radius'])) ? rewrite_url($removeurl, 'radius=' . $ilance->GPC['radius']) : $removeurl;
	$removeurl = (strrchr($removeurl, "?") == false) ? $removeurl . '?mode=' . $ilance->GPC['mode'] : $removeurl;
	$clear_skills = '<a href="' . $removeurl . '" rel="nofollow">{_clear}</a>';
	handle_search_verbose('<span class="gray"><!--<strong>{_skills}: --><span class="black"><strong>' . $regionname . '</strong></span></strong></span><!-- <a href="' . $removeurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ');
	handle_search_verbose_save('{_skills}: <strong>' . $regionname . '</strong>, ');
}
$leftnav_skills = ''; //print_skills('', $region, $_SESSION['ilancedata']['user']['slng'], '', 'links');

// #### profile answers logic ##########################
$sqlquery['profileanswersquery'] = $sqlquery['profileanswersqueryextra'] = "";
if (isset($ilance->GPC['pa']) AND is_array($ilance->GPC['pa']))
{
	$showtextprofiles = $favtextprofiles = $profiletitle = '';
	$showtextpro = $emptypa = array();
	$qs = 0;
	foreach ($ilance->GPC['pa'] AS $profileqid)
	{
		if (isset($profileqid) AND is_array($profileqid))
		{
			foreach ($profileqid AS $profileid => $profileoptions)
			{
				$emptypa[$profileid] = 'false';
				$profiletitle = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . intval($profileid) . "'", "question");
				$showtextpro[$profileid]['title'] = $profiletitle;
				if (isset($profileoptions) AND is_array($profileoptions))
				{
					$pass = false;
					foreach ($profileoptions AS $profilekey => $profilevalue)
					{
						if (isset($profilevalue) AND !empty($profilevalue))
						{
							if ($profilekey == 'from')
							{
								$fromrange = $profilevalue;
							}
							if ($profilekey == 'to')
							{
								$torange = $profilevalue;
							}
							if ($profilekey == 'custom')
							{
								$custom = $profilevalue;

								$showpidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
								$showpidurl = rewrite_url($showpidurl, $remove = 'pa[choice_' . str_replace(' ', '_', mb_strtolower($custom)) . '][' . $profileid . '][custom]=' . $custom);
								$showtextpro[$profileid]['options'][] = '<strong>' . $custom . '</strong><!-- <a href="' . $showpidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ';
							}
							$pass = true;
						}
					}
				}
				// range integer type
				if (!empty($fromrange) AND !empty($torange) AND !empty($profileid) AND empty($custom) AND $pass)
				{
					$sqlquery['profileanswersquery'] .= "(pa.user_id = u.user_id AND pa.questionid = '" . intval($profileid) . "' AND pa.answer BETWEEN $fromrange AND $torange) OR ";

					$showpidurl = $ilpage['search'] . print_hidden_fields(true, array('page','searchid'), true, '', '', true, true, true);
					$showpidurl = rewrite_url($showpidurl, $remove = '&pa[range][' . $profileid . '][from]=' . $fromrange . '&pa[range][' . $profileid . '][to]=' . $torange);

					$showtextpro[$profileid]['title'] = $profiletitle;
					$showtextpro[$profileid]['options'][] = '<strong>{_between_upper} ' . $fromrange . ' {_and} ' . $torange . '</strong><!-- <a href="' . $showpidurl . '" rel="nofollow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>-->, ';
				}
				// multiple choices
				else if (!empty($custom) AND $pass)
				{
					$sqlquery['profileanswersquery'] .= "(pa.user_id = u.user_id AND pa.questionid = '" . intval($profileid) . "' AND pa.answer LIKE '%" . $ilance->db->escape_string($custom) . "%') OR ";
				}
				else
				{
					$emptypa[$profileid] = 'true';
				}
				$qs++;
			}
		}
	}
	// #############################################
	// handle display of custom profile questions used on the advanced search form
	if (!empty($showtextpro) AND is_array($showtextpro))
	{
		foreach ($showtextpro AS $profilequestionid => $profilearray)
		{
			if ($emptypa[$profilequestionid] == 'false')
			{
				$showtextprofiles .= $showtextpro[$profilequestionid]['title'] . ': ';
				foreach ($profilearray AS $vv => $values)
				{
					if (isset($values) AND is_array($values))
					{
						foreach ($values AS $choice)
						{
							$showtextprofiles .= $choice;
						}
					}
				}
			}
		}
	}
	if (!empty($sqlquery['profileanswersquery']) AND !empty($profiletitle))
	{
		$sqlquery['fields'] .= ", pa.questionid, pa.answer";
		$sqlquery['leftjoin'] .= " LEFT JOIN " . DB_PREFIX . "profile_answers pa ON u.user_id = pa.user_id ";
		$sqlquery['profileanswersquery'] = "AND (" . mb_substr($sqlquery['profileanswersquery'], 0, -4) . ')';

		//handle_search_verbose_filters('{_other}: <strong>' . $profiletitle . '</strong> <a href="' . $showpidurl . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_small.gif" border="0" alt="{_remove_this_item_specific_from_your_search}" /></a>, ');
		//handle_search_verbose_save(', <strong>{_other}</strong>: <strong>' . $profiletitle . '</strong>, ');
	}
	else
	{
		$sqlquery['profileanswersquery'] = '';
	}
}
// #### options selector ###############################
$leftnav_options = print_options('experts');
$clear_options = !empty($clear_options_all) ? '<a href="' . $clear_options_all . '" rel="nofollow">{_clear}</a>' : '';
// #### BUILD MAIN SEARCH SQL QUERY ####################
$sqlquery['select'] = "SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "$sqlquery[fields] $sqlquery[from] $sqlquery[leftjoin] " . ((isset($ilance->GPC['isonline']) AND $ilance->GPC['isonline']) ? "LEFT JOIN " . DB_PREFIX . "sessions s ON u.user_id = s.userid" : "") . " WHERE u.user_id = p.user_id AND u.status = 'active' ";
$SQL  = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby] $sqlquery[limit]";
$SQL2 = "$sqlquery[select] $sqlquery[keywords] $sqlquery[categories] $sqlquery[options] $sqlquery[location] $sqlquery[radius] $sqlquery[userquery] $sqlquery[hidequery] $sqlquery[pricerange] $sqlquery[genrequery] $sqlquery[profileanswersquery] $sqlquery[skillsquery] $sqlquery[groupby] $sqlquery[orderby]";
$row_count = 0;
$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
$number = $ilance->db->num_rows($numberrows);
$counter = ($ilance->GPC['page'] - 1) * fetch_perpage();
// #### build our search engine verbose output #########
if (!empty($keyword_text))
{
	$favtext = '<div>{_keywords}: <strong>' . stripslashes($keyword_formatted_favtext) . '</strong></div>' . print_search_verbose_saved('verbose_save');
	$favtext = mb_substr($favtext, 0, -2) . '';
	if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
	{
		$text = '<span style="font-size:16px" class="blueonly"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_keywords} <span class="black">' . stripslashes($keyword_formatted) . '</span>';
	}
	else
	{
		$text = '<span style="font-size:16px" class="blueonly"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_keywords} <span class="black">' . stripslashes($keyword_formatted) . '</span> ' . print_search_verbose_saved('verbose');
		$text = mb_substr($text, 0, -2) . '';
	}
	$text = '<span id="verbosetext">' . $text . '</span>';
}
else
{
	// favorite search text results
	$favtext = print_search_verbose_saved('verbose_save');
	$favtext = mb_substr($favtext, 0, -2);
	if (!empty($selected['hideverbose']) AND $selected['hideverbose'] == 'true')
	{
		$text = '<span style="font-size:16px" class="blueonly"><strong>' . number_format($number) . '</strong></span> {_listings_found_with_no_keywords}';
	}
	else
	{
		$text = '<span style="font-size:16px" class="blueonly""><strong>' . number_format($number) . '</strong></span> {_listings_found_with_no_keywords} ' . print_search_verbose_saved('verbose');
		$text = mb_substr($text, 0, -2) . '';
	}
	$text = '<span id="verbosetext">' . $text . '</span>';
}
$showallurl = $ilpage['search'] . print_hidden_fields(true, array('page','qid','q','sid','pa','searchid'), true, '', '', true, false);
define('PHP_SELF_NOQID', $showallurl);
$showtext = print_search_verbose_saved('verbose_filter');
if (!empty($showtext))
{
	$showtext = mb_substr($showtext, 0, -2) . ' &nbsp;&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">{_show_all}</a></span> ]</span>';
	$text .= ', <span>' . $showtext . '</span>';
}

if (!empty($showtextprofiles))
{
	$showtextprofiles = mb_substr($showtextprofiles, 0, -2) . ' &nbsp;&nbsp;&nbsp;<span class="smaller gray">[ <span class="blue"><a href="' . $showallurl . '" rel="nofollow">{_show_all}</a></span> ]</span>';
	$text .= ', <!--<span><strong>{_profile_filters}:</strong></span> --><span class="black"><strong>' . $showtextprofiles . '</strong></span>';
}
$metatitle = '';
if ($cid > 0)
{
	$metatitle = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid);
	$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
	$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid, $commaafter = true, $showinputkeywords = true);
}
$area_title = '{_search_results_display}<div class="smaller">' . $metatitle . '</div>';
$page_title = ((isset($keyword_text) AND !empty($keyword_text)) ? $keyword_text . ', ' : '') . '{_find} {_experts} ' . ((!empty($metadescription)) ? '{_in} ' . $metatitle . ', ' . $metadescription : (!empty($metatitle) ? '{_in} ' . $metatitle : '' )) . ' | ' . SITE_NAME;
$search_results_rows = array();
$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($result) > 0)
{
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{
		$memberinfo = array();
		$memberinfo = $ilance->feedback->datastore($row['user_id']);
		$td['sel'] = '<input type="checkbox" name="vendor_id[]" value="' . $row['user_id'] . '" id="experts_' . $row['user_id'] . '" />';
		$td['user_id'] = $row['user_id'];
		$td['profileintro'] = short_string(print_string_wrap($row['profileintro'], 50), 65);
		$td['latestfeedback'] = short_string(print_string_wrap($ilance->feedback->print_latest_feedback_received($row['user_id'], 'provider', $shownone = true), 50), 50);
		$td['isonline'] = print_online_status($row['user_id'], 'litegray', 'blue');
		$td['expert'] = print_username($row['user_id'], 'href');
		$td['username'] = $td['expert'];
		$td['user_id'] = $row['user_id'];
		$td['city'] = ucfirst(fetch_user('city', $row['user_id']));
		$td['zipcode'] = mb_strtoupper(fetch_user('zip_code', $row['user_id']));
		$td['country'] = $ilance->common_location->print_user_country($row['user_id'], $_SESSION['ilancedata']['user']['slng']);
		$td['state'] = fetch_user('state', $row['user_id']);
		$td['rated'] = ($memberinfo['rating'] == 0) ? '-' : '<span class="smaller">' . number_format($memberinfo['rating'], 2, '.', '') . '&nbsp;/&nbsp;5.00</span>';
		$td['feedback'] = '<span class="smaller">' . $memberinfo['pcnt'] . '%<!--&nbsp;{_positive}--></span>';
		$td['credentials'] = $ilance->profile->fetch_verified_credentials($row['user_id']);
		$td['reviews'] = print_username($row['user_id'], 'custom', 0, '', '', $ilance->auction_service->fetch_service_reviews_reported($row['user_id']) . '&nbsp;{_reviews}');
		$td['awards'] = fetch_user('serviceawards', $row['user_id']);
		$td['awards'] = '<div class="smaller">' . (($td['awards'] == 0) ? '-' : $td['awards']) . '</div>';
		$td['earnings'] = '<span class="smaller">' . $ilance->accounting_print->print_income_reported($row['user_id']) . '</span>';
		$td['portfolio'] = ($ilance->portfolio->has_portfolio($row['user_id']) > 0) ? '<span class="smaller blueonly"><a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?id=' . $row['user_id'] . '" rel="nofollow">{_portfolio}</a></span>' : '-';
		// only fetch distance between point a to b in the distance column
		$row['distance'] = (!isset($row['distance'])) ? 0 : $row['distance'];
		$td['distance'] = (isset($show['distancecolumn']) AND $show['distancecolumn'] AND !empty($ilance->GPC['radiuszip']))
		? '<div class="smaller gray">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . '</div>'
		: '-';

		// display the location under the title
		//$countryrowname = (isset($ilance->GPC['country'])) ? '' : ', ' . $ilance->common_location->print_country_name($row['country'], $_SESSION['ilancedata']['user']['slng'], false);
		$td['location'] = $ilance->common_location->print_user_location($row['user_id']);

		// show the distance bit after the location
		$td['location'] .= ($td['distance'] != '-' AND !empty($countryid))
		? '&nbsp;&nbsp;(<span class="black">' . $ilance->distance->print_distance_results($row['country'], $row['zip_code'], $countryid, $ilance->GPC['radiuszip'], $row['distance']) . ' {_from_lowercase}</span> <span class="blue"><a href="javascript:void(0)" onclick="javascript:jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();">' . handle_input_keywords($ilance->GPC['radiuszip']) . '<!--, ' . handle_input_keywords($ilance->GPC['country']) . '--></a></span>)'
		: '';
		//unset($countryrowname);
		// gender
		$gender = fetch_user('gender', $row['user_id'], '', '', false);
		$sqlattach = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, filehash, filename, width, height
			FROM " . DB_PREFIX . "attachment
			WHERE user_id = '" . $row['user_id'] . "' 
			    AND visible = '1'
			    AND attachtype = 'profile'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlattach) > 0)
		{
			$resattach = $ilance->db->fetch_array($sqlattach, DB_ASSOC);
			$td['profilelogo'] = '<a href="' . print_username($row['user_id'], 'url') . '"><img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . (($ilconfig['globalauctionsettings_seourls'])
				? 'i/thumb/results/' . $resattach['filehash'] . '/' . ($resattach['width'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxwidth'] : $resattach['width']) . 'x' . ($resattach['height'] <= 0 ? $ilconfig['attachmentlimit_searchresultsmaxheight'] : $resattach['height']) . '_' . $resattach['filename']
				: $ilpage['attachment'] . '?cmd=profile&amp;id=' . $resattach['filehash']) . '" border="0" id="' . $resattach['filehash'] . '" alt="' . handle_input_keywords($row['username']) . '" /></a>';
		}
		else
		{
			if ($gender == '' OR $gender == 'male')
			{
				$td['profilelogo'] = '<a href="' . print_username($row['user_id'], 'url') . '" onmouseover="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2_sel.gif\')" onmouseout="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto2.gif" border="0" alt="" width="80" height="80" name="nophoto_experts_' . $row['user_id'] . '" /></a>';
			}
			else if ($gender == 'female')
			{
				$td['profilelogo'] = '<a href="' . print_username($row['user_id'], 'url') . '" onmouseover="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3_sel.gif\')" onmouseout="rollovericon(\'nophoto_experts_' . $row['user_id'] . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto3.gif" border="0" alt="" width="80" height="80" name="nophoto_experts_' . $row['user_id'] . '" /></a>';
			}
		}
		unset($sqlattach, $resattach);
		// rate per hour
		$hourlyrate = fetch_user('rateperhour', $row['user_id']);
		$td['rateperhour'] = ($hourlyrate > 0) ? '<strong>' . print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $hourlyrate, $ilconfig['globalserverlocale_defaultcurrency']) . '</strong>' : '-';
		$td['skills'] = print_skills($row['user_id'], 5);
		$td['level'] = $ilance->subscription->print_subscription_icon($row['user_id']);
		$td['class'] = ($row_count % 2) ? 'alt1' : 'alt1';

		($apihook = $ilance->api('search_results_providers_loop')) ? eval($apihook) : false;

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
	// #### BUILD OUR PAGNATOR #############################
	$prevnext = print_pagnation($number, fetch_perpage(), intval($ilance->GPC['page']), $counter, $scriptpage);
	// #### PRINT OUR SEARCH RESULTS TABLE #################
	$search_results_table = print_search_results_table($search_results_rows, 'experts', $prevnext);
	// #### SEARCH FORM ELEMENTS: CATEGORY PULLDOWN ########
	$radiuscountry = (isset($ilance->GPC['radiuscountry']) AND $ilance->GPC['radiuscountry'] > 0) ? $ilance->GPC['radiuscountry'] : (!empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : 'all');
	//$search_radius_country_pulldown_experts = print_active_countries_pulldown('radiuscountry', $radiuscountry, $_SESSION['ilancedata']['user']['slng'], $showworldwide = false, 'expertradiuscountry');
	if (!empty($ilance->GPC['q']))
	{
		$keywords = htmlspecialchars($ilance->GPC['q']);
	}
	// fewer keywords search
	$fewer_keywords = print_fewer_keywords_search($keywords_array, 'experts', $number);
	// #### SAVE AS FAVORITE SEARCH OPTION #################
	if ($ilconfig['savedsearches'])
	{
		// build search request parameters
		$favorites = array();
		foreach ($ilance->GPC AS $search => $option)
		{
			if ($search != 'submit' AND $search != 'search' AND $search != 'page')
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
		$favtext = ilance_htmlentities($favtext);
	}
	else
	{
		$favtext = '';
	}
	$v3left_nav = $ilance->template_nav->print_left_nav('serviceprovider', $cid, 1, 0, $ilconfig['globalfilters_enablecategorycount'], true);
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : '';
	$sortpulldown2 = print_sort_pulldown($ilance->GPC['sort'], 'sort', 'experts');
	$hiddenfields = print_hidden_fields(false, array('searchid','cid','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','q','sort','page'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
	$hiddenfields_leftnav = print_hidden_fields(false, array('searchid','feedback','country','isonline','images','portfolios','city','state','zip_code','endstart','endstart_filter','page','radius','radiuscountry','radiuszip','fromprice','toprice'), $questionmarkfirst = false, $prepend_text = '', $append_text = '', $htmlentities = true, $urldecode = true);
	$city = isset($ilance->GPC['city']) ? strip_tags($ilance->GPC['city']) : '';
	$state = isset($ilance->GPC['state']) ? strip_tags($ilance->GPC['state']) : '';
	$zip_code = isset($ilance->GPC['zip_code']) ? strip_tags($ilance->GPC['zip_code']) : '';
	$radiuszip = isset($ilance->GPC['radiuszip']) ? strip_tags($ilance->GPC['radiuszip']) : '';
}

($apihook = $ilance->api('search_results_providers_end')) ? eval($apihook) : false;

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
	// #### save this search ###############################
	if (isset($ilance->GPC['searchid']) AND $ilance->GPC['searchid'] > 0)
	{
		// todo: add hit tracker to show hit count of saved search
		$savesearchlink = '';
	}
	else
	{
		$savesearchlink = '<span class="smaller blueonly" style="padding-left:12px"><a href="javascript:void(0)" rel="nofollow" onclick="javascript:jQuery(\'#saved_search_modal\').jqm({modal: false}).jqmShow()">{_save_as_favorite_search}</a></span>&nbsp;&nbsp;&nbsp; <span class="smaller gray">|</span> &nbsp;&nbsp;&nbsp;';
	}
	// attempt to correct the spelling for the user if applicable (not in use at the moment)
	$didyoumean = print_did_you_mean($keyword_text, 'experts');
	// if we're a guest and we don't have the region modal cookie let's ask for it
	$country_user_id = (isset($_SESSION['ilancedata']['user']['countryid'])) ? $_SESSION['ilancedata']['user']['countryid'] : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'],fetch_site_slng());
	$full_country_pulldown = $ilance->common_location->construct_country_pulldown($country_user_id, '', 'region', true, '', false, true, true);
	if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']) AND $ilconfig['globalfilters_regionmodal'])
	{
		$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();';

		// don't ask this guest for region info via popup modal for 3 days
		set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
	}
	$onload .= "init_budgetSlider();";
	$onload .= (isset($ilance->GPC['fromprice'])) ? " set_budgetSlider('" . intval($ilance->GPC['fromprice']) . "'); " : "";
	$budgettemp = $ilance->auction_post->print_price_logic_type_js();
	$budget_slider_1 = $budgettemp[0];
	$budget_slider_2 = $budgettemp[1];
	$redirect_not_login =  (!isset($_SESSION['ilancedata']['user']['userid'])) ? '?redirect=' . urlencode($ilpage['search'] . print_hidden_fields($string = true, $excluded = array(), $questionmarkfirst = true)) : '';
	unset($budgettemp);
	
	$ilance->template->fetch('main', 'search_results.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array('savesearchlink','legend','leftnav_skills','clear_skills','clear_budgetrange','redirect_not_login','clear_currencies','leftnav_currencies','clear_local','clear_feedback','leftnav_feedbackrange','leftnav_ratingrange','clear_rating','clear_award','leftnav_awardrange','sort','country','clear_price','clear_options','leftnav_options','leftnav_options','showallurl','clear_region','leftnav_regions','full_country_pulldown','didyoumean','search_radius_country_pulldown_experts','search_country_pulldown_experts','favtext','favoritesearchurl','profilebidfilters','fewer_keywords','fromprice','toprice','hiddenfields_leftnav','city','state','zip_code','radiuszip','mode','search_country_pulldown2','hiddenfields','search_results_table','sortpulldown2','keywords','two_column_category_vendors','keywords','php_self','php_self_urlencoded','pfp_category_left','pfp_category_js','rfp_category_left','rfp_category_js','search_country_pulldown','search_jobtype_pulldown','five_last_keywords_buynow','five_last_keywords_projects','five_last_keywords_providers','search_ratingrange_pulldown','search_awardrange_pulldown','search_bidrange_pulldown','search_listed_pulldown','search_closing_pulldown','search_category_pulldown','distance','subcategory_name','text','prevnext','prevnext2','remote_addr','budgettemp','budget_slider_1','budget_slider_2');

	($apihook = $ilance->api('search_results_providers_template')) ? eval($apihook) : false;

	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>