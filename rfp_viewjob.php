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
if (!defined('LOCATION') OR defined('LOCATION') != 'rfp')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
// #### define top header nav ##########################################
$topnavlink = array (
	'main_listings'
);
if (empty($ilance->GPC['id']))
{
	$area_title = '{_bad_rfp_warning_menu}';
	$page_title = SITE_NAME . ' - {_bad_rfp_warning_menu}';
	print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
	exit();
}
else
{
	$show['widescreen'] = false;
	// #### SHORTLIST BID ##########################################
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'shortlist' AND isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if ($ilance->categories->bidgrouping(fetch_auction('cid', $ilance->GPC['id'])) == '0')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET isshortlisted = '1'
				WHERE id = '" . intval($ilance->GPC['bid']) . "'
					AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
			$ilance->GPC['bid'] = $ilance->db->fetch_field(DB_PREFIX . "project_realtimebids", "id='" . intval($ilance->GPC['bid']) . "'", "bid_id");
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET isshortlisted = '1'
			WHERE bid_id = '" . intval($ilance->GPC['bid']) . "'
				AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		");
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET bidsshortlisted = bidsshortlisted + 1
			WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		");
		if ($ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id='" . intval($ilance->GPC['bid']) . "' AND bidstate='wait_approval'", "COUNT(*)"))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'open'
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
		}
		if (!empty($ilance->GPC['returnurl']))
		{
			refresh(handle_input_keywords($ilance->GPC['returnurl']));
			exit();
		}
		else
		{
		    	fresh(HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['id']));
			exit();
		}
	}
	// #### UNSHORTLIST BID ##########################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'unshortlist' AND isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		if ($ilance->categories->bidgrouping(fetch_auction('cid', $ilance->GPC['id'])) == '0')
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET isshortlisted = '0'
				WHERE id = '" . intval($ilance->GPC['bid']) . "'
					AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
			$ilance->GPC['bid'] = $ilance->db->fetch_field(DB_PREFIX . "project_realtimebids", "id='" . intval($ilance->GPC['bid']) . "'", "bid_id");
		}
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "project_bids
			SET isshortlisted = '0'
			WHERE bid_id = '" . intval($ilance->GPC['bid']) . "'
				AND project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		");
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "projects
			SET bidsshortlisted = bidsshortlisted - 1
			WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			LIMIT 1
		");
		if ($ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id='" . intval($ilance->GPC['bid']) . "' AND bidstate='wait_approval'", "COUNT(*)"))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET status = 'open'
				WHERE project_id = '" . intval($ilance->GPC['id']) . "'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
		}
		if (!empty($ilance->GPC['returnurl']))
		{
			refresh(handle_input_keywords($ilance->GPC['returnurl']));
			exit();
		}
		else
		{
			refresh(HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($ilance->GPC['id']));
			exit();
		}
	}
	$id = intval($ilance->GPC['id']);
	$customquery = '';
    
	($apihook = $ilance->api('rfp_listing_top_start')) ? eval($apihook) : false;
    
	if (isset($_SESSION['ilancedata']['user']['isadmin']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
		$sql = $ilance->db->query("
			    SELECT *, UNIX_TIMESTAMP(date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				AND project_state = 'service'
				$customquery
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	else
	{
		$sql = $ilance->db->query("
			SELECT *, UNIX_TIMESTAMP(date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime 
			FROM " . DB_PREFIX . "projects AS p
			WHERE project_id = '" . intval($ilance->GPC['id']) . "'
				AND project_state = 'service'
				AND visible = '1'
				$customquery
				" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "") . "
				LIMIT 1
		", 0, null, __FILE__, __LINE__);
	}
	if ($ilance->db->num_rows($sql) == 0)
	{
		$area_title = '{_bad_rfp_warning_menu}';
		$page_title = SITE_NAME . ' - {_bad_rfp_warning_menu}';
		print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
		exit();
	}
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	// #### prevent duplicate content from search engines
	if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
	{
		$seourl = construct_seo_url('serviceauctionplain', $res['cid'], $res['project_id'], stripslashes($res['project_title']), $customlink = '', $bold = 0, $searchquestion = '', $questionid = 0, $answerid = 0, $removevar = '');
		$view = '';
		if (isset($ilance->GPC['invited']) AND $ilance->GPC['invited'] AND isset($ilance->GPC['e']) AND !isset($ilance->GPC['view']))
		{
			if (isset($ilance->GPC['rid']))
			{
				$view = '?rid=' . handle_input_keywords($ilance->GPC['rid']) . '&invited=1&e=' . handle_input_keywords($ilance->GPC['e']);
			}
			else
			{
				$view = '?invited=1&e=' . handle_input_keywords($ilance->GPC['e']);
			}
		}
		else if (isset($ilance->GPC['view']) AND !isset($ilance->GPC['invited']))
		{
			$view = '?view=' . handle_input_keywords($ilance->GPC['view']) . '#bids';
		}
		else if (isset($ilance->GPC['invited']) AND $ilance->GPC['invited'] AND isset($ilance->GPC['e']) AND isset($ilance->GPC['view']))
		{
			if (isset($ilance->GPC['rid']))
			{
				$view = '?rid=' . handle_input_keywords($ilance->GPC['rid']) . '&invited=1&e=' . handle_input_keywords($ilance->GPC['e']) . '&view=' . handle_input_keywords($ilance->GPC['view']) . '#bids';
			}
			else
			{
				$view = '?invited=1&e=' . handle_input_keywords($ilance->GPC['e']) . '&view=' . handle_input_keywords($ilance->GPC['view']) . '#bids';
			}
		}
		header('Location: ' . $seourl . $view);
		unset($seourl);
		exit();
	}
	// recently reviewed session saver
	$ilance->auction->recently_viewed_handler(intval($ilance->GPC['id']), 'service');
	$row_count = 0;
	$serviceauction = 1;
	$productauction = 0;
	$area_title = '{_viewing_detailed_rfp}<div class="smaller">' . handle_input_keywords(stripslashes($res['project_title'])) . ' (#' . intval($ilance->GPC['id']) . ')</div>';
	$cid = $res['cid'];
	$var2 = $ilance->categories->fetch_cat_ids($cid, '', 'service');
	$var_val = explode(',', $var2);
	sort($var_val);
	$count = count($var_val);
	$res_title_new = '';
	for ($i = 0; $i < $count; $i++)
	{
		$sql1 = $ilance->db->query("
			SELECT title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title
			FROM " . DB_PREFIX . "categories
			WHERE cid = '" . intval($var_val[$i]) . "'
		");
		if ($ilance->db->num_rows($sql1) > 0)
		{
			$res_title = $ilance->db->fetch_array($sql1, DB_ASSOC);
			$res_title_new .= $res_title['title'] . ' | ';
		}
	}
	$page_title = handle_input_keywords(stripslashes($res_title_new . $res['project_title'])) . ' | ' . SITE_NAME;
	// revision details
	$updateid = $res['updateid'];
	$show['revision'] = false;
	if ($updateid > 0)
	{
		$show['revision'] = true;
		$updateid = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=revisionlog&amp;id=' . $res['project_id'] . '">' . $res['updateid'] . '</a>';
	}
	// bidding type filter
	$bidtypefilter = '{_buyer_accepts_various_bid_amount_types}';
	if ($res['filter_bidtype'])
	{
		$bidtypefilter = $ilance->auction->construct_bidamounttype($res['filtered_bidtype']);
	}
	// does buyer use escrow payment control to ensure his funds are secure?
	$show['filter_escrow'] = false;
	$escrowbit = '';
	if ($res['filter_escrow'] == '1' AND $ilconfig['escrowsystem_enabled'])
	{
		$show['filter_escrow'] = true;
		$escrowbit = '{_funds_will_be_secured_via_escrow_after_award} <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="" id="" />';
	}
	// select project attachments
	$project_attachment = '';
	$show['has_attachments'] = false;
	$sql_attachments = $ilance->db->query("
		SELECT attachid, filename, filesize, filehash
		FROM " . DB_PREFIX . "attachment
		WHERE attachtype = 'project' 
			AND project_id = '" . intval($ilance->GPC['id']) . "' 
			AND visible = '1'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_attachments) > 0)
	{
		while ($res_attachments = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
		{
			switch (fetch_extension($res_attachments['filename']))
			{
				case 'gif':
				{
					$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					break;
				}
				case 'jpg':
				{
					$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					break;
				}
				case 'png':
				{
					$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank"><img src="' . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attachments['filehash'] . '" border="0" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
					break;
				}
				default:
				{
					$project_attachment .= '<a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank">' . $res_attachments['filename'] . '</a> (' . $res_attachments['filesize'] . ' {_bytes})<br />';
					break;
				}
			}
		}
		$show['has_attachments'] = true;
	}
	if (empty($project_attachment))
	{
		$show['has_attachments'] = true;
		$project_attachment = '{_no_attachments_available}';
	}
	// buyers start date
	$memberstart = print_date(fetch_user('date_added', $res['user_id']), $ilconfig['globalserverlocale_globaldateformat']);
	$state = fetch_user('state', $res['user_id']);
	$buyerstate = $state;
	$city = fetch_user('city', $res['user_id']);
	$buyercity = $city;
	$location = $ilance->common_location->print_user_location($res['user_id']);
	$countryname = $ilance->common_location->print_user_country($res['user_id']);
	$buyercountry = $countryname;
	$listingcategory = $categoryname = $category = $ilance->categories->recursive($res['cid'], 'service', $_SESSION['ilancedata']['user']['slng'], $nourls = 0, '', $ilconfig['globalauctionsettings_seourls']);
	$lowbidtemp = $ilance->bid->fetch_lowest_bidder_info($res['project_id'], $res['user_id'], $res['bid_details']);
	$lowbidder = $lowbidtemp['lowbidder'];
	$lowbidderid = $lowbidtemp['lowbidderid'];
	unset($lowbidtemp);
	$project_id = intval($ilance->GPC['id']);
	$show['has_additional_info'] = (!empty($res['additional_info'])) ? true : false;
	$show['description_html'] = ($res['ishtml'] == '1') ? true : false;
	// #### OWNER VIEWING OWN DESCRIPTION ##################
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
	{
		// owner viewing own description
		if (isset($show['description_html']) AND $show['description_html'] == true)
		{
			$description = $res['description'];
		}
		else
		{
			$description = strip_vulgar_words($res['description']);
			$description = $ilance->bbcode->bbcode_to_html($description);
			$description = print_string_wrap($description, 100);
		}
		$additional_info = strip_vulgar_words($res['additional_info']);
		$additional_info = print_string_wrap($additional_info);
	}
	// #### ADMIN VIEWING DESCRIPTION ######################
	else if (isset($_SESSION['ilancedata']['user']['isadmin']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
		// admin viewing description
		if (isset($show['description_html']) AND $show['description_html'] == true)
		{
			$description = $res['description'];
		}
		else
		{
			$description = strip_vulgar_words($res['description']);
			$description = $ilance->bbcode->bbcode_to_html($description);
			$description = print_string_wrap($description, 100);
		}
		$additional_info = strip_vulgar_words($res['additional_info']);
		$additional_info = print_string_wrap($additional_info);
	}
	// #### EVERYONE ELSE ##################################
	else
	{
		switch ($res['project_details'])
		{
			// #### PUBLIC AUCTION #################
			case 'public':
			{
				$project_details = '{_public_event}';
				if (isset($show['description_html']) AND $show['description_html'] == true)
				{
					$description = $res['description'];
				}
				else
				{
					$description = strip_vulgar_words($res['description']);
					$description = $ilance->bbcode->bbcode_to_html($description);
					$description = print_string_wrap($description, 100);
				}
				$additional_info = strip_vulgar_words($res['additional_info']);
				$additional_info = print_string_wrap($additional_info);
				break;
			}
			// #### INVITE ONLY AUCTION ############
			case 'invite_only':
			{
				$project_details = '{_by_invitation_only}';
				$description = "[" . '{_full_description_available_to_invited_providers_only}' . "]";
				$additional_info = "[" . '{_full_description_available_to_invited_providers_only}' . "]";
				$invited_vendors = 1;
				$show['bidderuninvited'] = 1;
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
				{
					$sql_invites = $ilance->db->query("
						SELECT id
						FROM " . DB_PREFIX . "project_invitations
						WHERE project_id = '" . $res['project_id'] . "'
							AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_invites) > 0)
					{
						// member invited
						if (isset($show['description_html']) AND $show['description_html'] == true)
						{
							$description = $res['description'];
						}
						else
						{
							$description = strip_vulgar_words($res['description']);
							$description = $ilance->bbcode->bbcode_to_html($description);
							$description = print_string_wrap($description, 100);
						}
						$additional_info = strip_vulgar_words($res['additional_info']);
						$additional_info = print_string_wrap($additional_info);
						$invited_vendors = 1;
						$show['bidderuninvited'] = 0;
					}
				}
				break;
			}
			// #### REALTIME AUCTION ###############
			case 'realtime':
			{
				$project_details = '{_realtime_event}';
				// vulgar censor
				if (isset($show['description_html']) AND $show['description_html'] == true)
				{
					$description = $res['description'];
				}
				else
				{
					$description = strip_vulgar_words($res['description']);
					$description = $ilance->bbcode->bbcode_to_html($description);
					$description = print_string_wrap($description, 100);
				}
				$additional_info = strip_vulgar_words($res['additional_info']);
				$additional_info = print_string_wrap($additional_info);
				break;
			}
		}
	}
	// buyer details
	$sql_user_results = $ilance->db->query("
		SELECT user_id, country, zip_code, username
		FROM " . DB_PREFIX . "users
		WHERE user_id = '" . $res['user_id'] . "'
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql_user_results) > 0)
	{
		$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
	}
	else
	{
		print_notice('{_owner_delisted}', '{_sorry_the_owner_of_this_auction_has_been_delisted}', $ilpage['main'], '{_main_menu}');
		exit();
	}
	$project_buyer = print_username($res_project_user['user_id'], 'href', 1, '', '', '');
	$buyername = $res_project_user['username'];
	$project_user_id = $res_project_user['user_id'];
	$memberinfo = $ilance->feedback->datastore($project_user_id);
	$feed1 = $memberinfo['rating'];
	$buyerscore = $memberinfo['pcnt'];
	$project_title = print_string_wrap(strip_vulgar_words($res['project_title']), 35);
	$icons = $ilance->auction->auction_icons($res);
	$views = $res['views'];
	// prevent the top cats in breadcrumb to contain any fields from this form
	$show['nourlbit'] = true;
	$navcrumb = array ();
	if ($ilconfig['globalauctionsettings_seourls'])
	{
		$catmap = print_seo_url($ilconfig['servicecatmapidentifier']);
		$navcrumb["$catmap"] = '{_browse}';
		unset($catmap);
	}
	else
	{
		$navcrumb["$ilpage[rfp]?cmd=listings"] = '{_browse}';
	}
	$ilance->categories->breadcrumb($res['cid'], 'service', $_SESSION['ilancedata']['user']['slng']);
	$navcrumb[""] = $project_title;
	$distance = '-';
	if (!empty($_SESSION['ilancedata']['user']['userid']))
	{
		if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'distance') == 'yes')
		{
			$distance = $ilance->distance->print_distance_results($res_project_user['country'], $res_project_user['zip_code'], $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['postalzip']);
		}
	}
	// bid system permission display
	$filter_permissions = $ilance->bid_permissions->print_filters('service', $id);
	$fetchbidstuff = $ilance->bid->fetch_average_lowest_highest_bid_amounts($res['bid_details'], $res['project_id'], $res['user_id']);
	$bidprivacy = $fetchbidstuff['bidprivacy'];
	$average = $fetchbidstuff['average'];
	$lowest = $fetchbidstuff['lowest'];
	$highest = $fetchbidstuff['highest'];
	unset($fetchbidstuff);
	$show['ended'] = false;
	if ($res['date_starts'] > DATETIME24H)
	{
		$show['can_bid'] = false;
		// auction event has not started
		$dif = $res['starttime'];
		$ndays = floor($dif / 86400);
		$dif -= $ndays * 86400;
		$nhours = floor($dif / 3600);
		$dif -= $nhours * 3600;
		$nminutes = floor($dif / 60);
		$dif -= $nminutes * 60;
		$nseconds = $dif;
		$sign = '+';
		if ($res['starttime'] < 0)
		{
			$res['starttime'] = - $res['starttime'];
			$sign = '-';
		}
		if ($sign != '-')
		{
			if ($ndays != '0')
			{
				$project_time_left = $ndays . '{_d_shortform}, ';
				$project_time_left .= $nhours . '{_h_shortform}+';
			}
			else if ($nhours != '0')
			{
				$project_time_left = $nhours . '{_h_shortform}, ';
				$project_time_left .= $nminutes . '{_m_shortform}+';
			}
			else
			{
				$project_time_left = $nminutes . '{_m_shortform}, ';
				$project_time_left .= $nseconds . '{_s_shortform}+';
			}
		}
		$res['timetostart'] = $project_time_left;
		$started = '{_starts}: ' . $res['timetostart'];
		$project_status = $started;
		$ends = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
		$timeleft = "--";
	}
	else
	{
		$show['can_bid'] = true;
		// auction has already started!
		$started = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
		$ends = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
		$dif = $res['mytime'];
		$ndays = floor($dif / 86400);
		$dif -= $ndays * 86400;
		$nhours = floor($dif / 3600);
		$dif -= $nhours * 3600;
		$nminutes = floor($dif / 60);
		$dif -= $nminutes * 60;
		$nseconds = $dif;
		$sign = '+';
		if ($res['mytime'] < 0)
		{
			$res['mytime'] = - $res['mytime'];
			$sign = '-';
		}
		if ($sign == '-')
		{
			$project_time_left = '{_ended}';
		}
		else
		{
			if ($ndays != '0')
			{
				$project_time_left = $ndays . '{_d_shortform}, ';
				$project_time_left .= $nhours . '{_h_shortform}+';
			}
			else if ($nhours != '0')
			{
				$project_time_left = $nhours . '{_h_shortform}, ';
				$project_time_left .= $nminutes . '{_m_shortform}+';
			}
			else
			{
				$project_time_left = $nminutes . '{_m_shortform}, ';
				$project_time_left .= $nseconds . '{_s_shortform}+';
			}
		}
		$timeleft = $project_time_left;
		if ($res['status'] == 'open')
		{
			$show['ended'] = false;
			$project_status = '{_event_open_for_bids}';
		}
		else
		{
			$show['ended'] = true;
			$show['can_bid'] = false;
		}
	}
	$bid_limit = (fetch_auction('filter_bidlimit', $id) == 1 AND fetch_auction('filtered_bidlimit', $res['project_id']) > 0) ? 1 : 0;
	$maximum_bids = fetch_auction('filtered_bidlimit', $res['project_id']);
	switch ($res['status'])
	{
		case 'closed':
		{
			$project_status = '{_closed_since} ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			break;
		}
		case 'expired':
		{
			$project_status = '{_expired_since} ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			break;
		}
		case 'delisted':
		{
			$project_status = '{_delisted_since}' . " " . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			break;
		}
		case 'approval_accepted':
		{
			$project_status = '{_vendor_awarded_bidding_for_event_closed}';
			break;
		}
		case 'wait_approval':
		{
			// fetch days since the provider has been awarded giving more direction to the viewer
			$date1split = explode(' ', $res['close_date']);
			$date2split = explode('-', $date1split[0]);
			$days = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
			if ($days == 0)
			{
				$days = 1;
			}
			$project_status = '{_waiting_for_awarded_provider_to_accept_the_project} <span class="gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</span>';
			break;
		}
		case 'frozen':
		{
			$project_status = '{_frozen_event_temporarily_closed}';
			break;
		}
		case 'finished':
		{
			$project_status = '{_vendor_awarded_event_is_finished}';
			break;
		}
		case 'archived':
		{
			$project_status = '{_archived_event}';
			break;
		}
		case 'draft':
		{
			$project_status = '{_draft_mode_pending_post_by_owner}';
			break;
		}
	}
	// number of bids placed
	$declinedbids = $ilance->bid->fetch_declined_bids($res['project_id']);
	$retractedbids = $ilance->bid->fetch_retracted_bids($res['project_id']);
	$shortlistedbids = $ilance->bid->fetch_shortlisted_bids($res['project_id'], $res['user_id']);
	$table = ($ilance->categories->bidgrouping($res['cid'])) ? 'project_bids' : 'project_realtimebids';
	$sql = $ilance->db->query("SELECT bid_id FROM " . DB_PREFIX . $table . " WHERE project_id='" . $res['project_id'] . "'");
	$bids = $ilance->db->num_rows($sql);
	if ($bids <= 0)
	{
		$bids = 0;
		$bidsactive = 0;
	}
	else
	{
		$bidsactive = $bids - $retractedbids - $declinedbids;
	}
	// invited vendors listings
	$invite_list = $ilance->auction_rfp->print_invited_users($id, $res['user_id'], $res['bid_details']);
	// payment methods accepted (if user has escrow enabled)
	if ($ilconfig['escrowsystem_enabled'] AND $res['filter_escrow'] == '1')
	{
		// set feetype as service provider since he is the one looking at the page
		$feetype = 'service';
	}
	$paymentmethods = $ilance->payment->print_payment_methods($res['project_id']);
	// awarded vendor row (under bid id)
	$row['award'] = '';
	if (isset($row['bidstatus']) AND $row['bidstatus'] == 'awarded')
	{
		$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" />';
		$awarded_vendor = stripslashes($row['username']);
	}
	$show['awarded_vendors'] = true;
	if (!isset($awarded_vendor))
	{
		$show['awarded_vendors'] = false;
	}
	$show['filters_vendors'] = true;
	if (!isset($filter_permissions))
	{
		$show['filters_vendors'] = false;
	}
	// are we viewing page as admin?
	if (isset($_SESSION['ilancedata']['user']['isadmin']) AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
	{
		$show['is_owner'] = false;
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
		{
			$show['is_owner'] = true;
		}
		$show['can_bid'] = false;
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
		{
			$show['can_bid'] = true;
		}
	}
	else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$show['is_owner'] = false;
		$show['can_bid'] = true;
		if ($_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
		{
			$show['is_owner'] = true;
			$show['can_bid'] = false;
		}
	}
	else
	{
		$show['is_owner'] = false;
		$show['can_bid'] = false;
	}
    
	($apihook = $ilance->api('rfp_custom_auction_questions')) ? eval($apihook) : false;
    
	$project_questions = $ilance->auction_questions->construct_auction_questions($res['cid'], $res['project_id'], 'output', 'service', $columns = 4);
	$project_budget = $ilance->auction_service->fetch_rfp_budget($res['project_id'], false);
	$cid = $res['cid'];
	$tab = 'b';
	$bidgrouping = true;
	$bidhistoryinfo = '';
	$show['bidhistoryinfo'] = 0;
	if ($ilance->categories->bidgrouping($res['cid']))
	{
		$from = '';
		$groupby = "GROUP BY b.user_id ";
		$show['bidhistoryinfo'] = 1;
		$bidgrouping = true;
		if ($ilance->categories->bidgroupdisplay($res['cid']) == 'lowest')
		{
			// group each bidders bid by lowest placed
			$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MIN(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
			$inbidgroup = '';
			$bidhistoryinfo = '{_grouping_bidders_by_lowest_bids_placed}';
		}
		else
		{
			// group each bidders bid by highest placed
			$SQL = "SELECT b.winnermarkedaspaidmethod, b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MAX(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.isshortlisted, ";
			$inbidgroup = '';
			$bidhistoryinfo = '{_grouping_bidders_by_highest_bids_placed}';
		}
	}
	else
	{
		// no bid grouping
		$bidgrouping = false;
		$groupby = "";
		$show['bidhistoryinfo'] = 1;
		$SQL = "SELECT rb.winnermarkedaspaidmethod, rb.bid_id, rb.id, rb.estimate_days, rb.user_id, rb.project_id, rb.project_user_id, rb.proposal, rb.bidamount, rb.bidamounttype, rb.bidcustom, rb.date_added, rb.date_updated, rb.date_awarded, rb.bidstatus, rb.bidstate, rb.isshortlisted, ";
		$from = ", " . DB_PREFIX . "project_realtimebids as rb ";
		$inbidgroup = "AND b.bid_id = rb.bid_id ";
		$bidhistoryinfo = '{_displaying_all_bids}';
		$tab = 'rb';
	}
	$SQL .= "
		p.status AS project_status, p.escrow_id, p.cid, p.description, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.username, u.city, u.state, u.zip_code
		FROM " . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u
		$from
		WHERE b.project_id = '" . $res['project_id'] . "'
		AND b.project_id = p.project_id
		AND u.user_id = b.user_id
		$inbidgroup
	";
	if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'declined' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$show['active_bids'] = $show['retracted_bids'] = $show['shortlist_bids'] = false;
		$show['declined_bids'] = true;
		$SQL .= " AND " . $tab . ".bidstatus = 'declined' AND " . $tab . ".bidstate != 'retracted' ";
	}
	else if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$show['active_bids'] = $show['declined_bids'] = $show['shortlist_bids'] = false;
		$show['retracted_bids'] = true;
		$SQL .= " AND " . $tab . ".bidstate = 'retracted' ";
	}
	else if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'shortlist' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$show['active_bids'] = $show['declined_bids'] = $show['retracted_bids'] = false;
		$show['shortlist_bids'] = true;
		$SQL .= " AND " . $tab . ".isshortlisted = '1' ";
	}
	else
	{
		$show['active_bids'] = true;
		$show['declined_bids'] = $show['retracted_bids'] = $show['shortlist_bids'] = false;
		$SQL .= " AND " . $tab . ".bidstatus != 'declined' AND " . $tab . ".bidstate != 'retracted' ";
	}
	$type_bids = (isset($ilance->GPC['type']) AND $ilance->GPC['type'] != '' AND ($ilance->GPC['type'] == 'bid_id' OR $ilance->GPC['type'] == 'id' OR $ilance->GPC['type'] == 'username' OR $ilance->GPC['type'] == 'estimate_days' OR $ilance->GPC['type'] == 'date_added' OR $ilance->GPC['type'] == 'displayfinancials')) ? $ilance->db->escape_string($ilance->GPC['type']) : 'bidamount';
	$orderby_bids = (isset($ilance->GPC['order']) AND $ilance->GPC['order'] != '') ? $ilance->GPC['order'] : 'ASC';
	$view_type = (isset($ilance->GPC['view']) AND $ilance->GPC['view'] != '') ? $ilance->GPC['view'] : ' ';
	$type_bids = ($table == "project_realtimebids" AND $type_bids == 'bid_id') ? 'id' : $type_bids;
	$order_type = ($orderby_bids == 'ASC') ? 'DESC' : 'ASC';
	$select_table = ($type_bids == 'username' OR $type_bids == 'displayfinancials' ) ? 'u' : 'b';
	$select_table = (($table == "project_realtimebids" AND $type_bids == 'id') OR ($table == "project_realtimebids" AND $type_bids == 'bidamount') OR ($table == "project_realtimebids" AND $type_bids == 'date_added')) ? 'rb' : $select_table;
	$type_bids = empty($type_bids) ? 'bidamount' : $type_bids;
	$orderby_bids = $select_table . "." . $type_bids . " " . $orderby_bids;
	$rfpid1 = $ilance->GPC['id'];
	$urlname = $lnkname = $project_title1 = fetch_auction('project_title', $rfpid1);
	$schema = $ilconfig['servicelistingschema'];
	$schema = str_replace('{HTTP_SERVER}', HTTP_SERVER, $schema);
	$schema = str_replace('{DOMAIN}', str_replace('http://', '', HTTP_SERVER), $schema);
	$schema = str_replace('{KEYWORDS}', '', $schema);
	$schema = str_replace('{CATEGORY}', construct_seo_url_name($urlname), $schema);
	$schema = str_replace('{CATEGORYLOWERCASE}', construct_seo_url_name(mb_strtolower($urlname)), $schema);
	$schema = str_replace('{IDENTIFIER}', print_seo_url($ilconfig['servicelistingidentifier']), $schema);
	$schema = str_replace('{CID}', 0, $schema);
	$schema = str_replace('{ID}', $rfpid1, $schema);
	$urlbit = (isset($show['nourlbit']) AND $show['nourlbit']) ? '' : '';
	$schema = str_replace('{URLBIT}', $urlbit, $schema);
	$schema = str_replace('{LINKNAME}', $lnkname, $schema);
	$schema .= '?';
	$url_rfp = ($ilconfig['globalauctionsettings_seourls']) ? $schema : $ilpage['rfp'] . "?id=" . $ilance->GPC['id'] . "&";
	$SQL .= $groupby . "ORDER BY " . $orderby_bids . "";
	$result = $ilance->db->query($SQL);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			if ($ilance->categories->bidgrouping($res['cid']) == '0')
			{
				$row['bid_id'] = $row['id'];
				$row['bids'] = $bids;
				$grouping = 0;
			}
			$row['delivery'] = '<div style="font-size:15px"><strong>' . $row['estimate_days'] . '</strong></div><div class="smaller gray" style="padding-top:3px">' . $ilance->auction->construct_measure($row['bidamounttype']) . '</div>';
			switch ($row['bidamounttype'])
			{
				case 'entire':
				{
					$row['bidamounttype'] = '{_for_entire_project}';
					break;
				}
				case 'hourly':
				{
					$row['bidamounttype'] = '{_per_hour}';
					break;
				}
				case 'daily':
				{
					$row['bidamounttype'] = '{_per_day}';
					break;
				}
				case 'weekly':
				{
					$row['bidamounttype'] = '{_weekly}';
					break;
				}
				case 'monthly':
				    {
					$row['bidamounttype'] = '{_monthly}';
					break;
				    }
				case 'lot':
				{
					$row['bidamounttype'] = '{_per_lot}';
					break;
				}
				case 'weight':
				{
					$row['bidamounttype'] = '{_per_weight} ' . stripslashes($row['bidcustom']);
					break;
				}
				case 'item':
				{
					$row['bidamounttype'] = '{_per_item}';
					break;
				}
			}
			$row['bid_datetime'] = print_date($row['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['bidamount'] = $ilance->bid->fetch_bid_amount($row['bid_details'], $row['bidamount'], $row['user_id'], $res_project_user['user_id'], $row['currencyid']);
			$row['proposal'] = strip_vulgar_words($row['proposal']);
			$row['proposal'] = (is_numeric(strpos($row['proposal'], '<p>'))) ? $row['proposal'] : $ilance->bbcode->bbcode_to_html($row['proposal']);
			$row['proposal'] = print_string_wrap($row['proposal'], 100);
			$row['isonline'] = print_online_status($row['user_id']);
			$row['verified'] = $ilance->profile->fetch_verified_credentials($row['user_id']);
			$row['provider'] = print_username($row['user_id']);
			$row['city'] = ucfirst($row['city']);
			$row['state'] = ucfirst($row['state']);
			$row['zip'] = trim(mb_strtoupper($row['zip_code']));
			$row['location'] = $ilance->common_location->print_user_location($row['user_id']);
			$row['awarded'] = print_username($row['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $row['user_id']) . ' {_awards}');
			$row['reviews'] = $ilance->auction_service->fetch_service_reviews_reported($row['user_id']);
			$row['earnings'] = $ilance->accounting_print->print_income_reported($row['user_id']);
			$row['portfolio'] = '<a href="' . HTTP_SERVER . $ilpage['portfolio'] . '?id=' . $row['user_id'] . '">{_view}</a>';
			if (mb_substr($row['winnermarkedaspaidmethod'], 0, 8) == 'offline_')
			{
				$row['paymethod'] = '{' . mb_substr(handle_input_keywords($row['winnermarkedaspaidmethod']), 8) . '}';
			}
			else if ($row['winnermarkedaspaidmethod'] == 'escrow')
			{
				$row['paymethod'] = '<span title="' . SITE_NAME . ' {_escrow_service}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" id="" /></span>';
			}
			else
			{
				$row['paymethod'] = handle_input_keywords($row['winnermarkedaspaidmethod']);
			}
			$showbidattachment = 0;
			$row['bidattach'] = '';
			$sql_attachments = $ilance->db->query("
				SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref,DATE_ADD(date, INTERVAL + '2' MINUTE) as date1
				FROM " . DB_PREFIX . "attachment
				WHERE attachtype = 'bid'
					AND project_id = '" . $row['project_id'] . "'
					AND user_id = '" . $row['user_id'] . "'
					AND visible = '1'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_attachments) > 0)
			{
				$row['bidattach'] .= '<div><strong>{_attachments}</strong></div>';
				while ($res_attachments = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
				{
					$row['bidattach'] .= '<div style="padding-top:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" border="0" alt="" id="" /> <span class="blue"><a href="' . $ilpage['attachment'] . '?id=' . $res_attachments['filehash'] . '" target="_blank">' . $res_attachments['filename'] . '</a></span></div>';
				}
			}
			// is blind bidding enabled?
			if ($row['bid_details'] == 'blind' OR $row['bid_details'] == 'full')
			{
				if (!empty($_SESSION['ilancedata']['user']['userid']))
				{
					// hide this service provider row if:
					// 1. current logged in user is not the bidder
					// 2. current logged in user is not the owner
					// 3. current logged in user is not the admin
					if ($_SESSION['ilancedata']['user']['userid'] != $row['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $row['project_user_id'] AND $_SESSION['ilancedata']['user']['isadmin'] == '0')
					{
						// hide all bidder information (we are not project owner)
						$row['provider'] = '= {_blind_bidder} =';
						$row['level'] = $row['city'] = $row['state'] = $row['zip'] = $row['location'] = $row['bidattach'] = '';
						$row['awarded'] = $row['reviews'] = $row['stars'] = $row['portfolio'] = $row['delivery'] = '-';
						$row['proposal'] = '<em>{_bid_proposal_hidden_due_to_blind_bidding}</em>';
					}
				}
				else
				{
					$row['provider'] = '= {_blind_bidder} =';
					$row['level'] = $row['city'] = $row['state'] = $row['zip'] = $row['location'] = $row['bidattach'] = '';
					$row['awarded'] = $row['reviews'] = $row['stars'] = $row['portfolio'] = $row['delivery'] = '-';
					$row['proposal'] = '<em>{_bid_proposal_hidden_due_to_blind_bidding}</em>';
				}
			}
			// awarded row (under bid id)
			$declinedbit = '';
			if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'declined')
			{
				$declinedbit = 'disabled="disabled"';
			}
			$retractedbit = '';
			if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted')
			{
				$retractedbit = 'disabled="disabled"';
			}
			$shortlistedbit = '';
			if (isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'shortlist')
			{
				$shortlistedbit = 'disabled="disabled"';
			}
			// #### handle shortlist button logic
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $row['isshortlisted'] AND $row['project_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$row['shortlist'] = '<input type="button" value=" {_unshortlist} " onclick="location.href=\'' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=unshortlist&amp;id=' . $row['project_id'] . '&amp;bid=' . $row['bid_id'] . '&amp;returnurl=' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids\'" class="buttons" style="font-size:12px" ' . $declinedbit . $retractedbit . ' />&nbsp;';
			}
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $row['isshortlisted'] == 0 AND $row['project_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$row['shortlist'] = '<input type="button" value=" {_shortlist} " onclick="location.href=\'' . HTTP_SERVER . $ilpage['rfp'] . '?cmd=shortlist&amp;id=' . $row['project_id'] . '&amp;bid=' . $row['bid_id'] . '&amp;returnurl=' . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids\'" class="buttons" style="font-size:12px" ' . $declinedbit . $retractedbit . ' />&nbsp;';
			}
			else
			{
				$row['shortlist'] = '';
			}
			$row['award'] = $row['unawardbutton'] = $row['declinebutton'] = $row['awardbutton'] = '';
			$bidstatus = $row['bidstatus'];
			$bidstate = $row['bidstate'];
			// #### handle bidder buttons logic
			switch ($row['project_status'])
			{
				// #### open or expired
				case 'open':
				case 'expired':
				{
					$disabled = ($bidstate == 'retracted') ? 'disabled="disabled"' : '';
					if ($bidstatus == 'declined')
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
					}
					else
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'awardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['awardbutton'] = '<input type="button" style="font-size:12px" value=" {_award} " onclick="confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\'); location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $disabled . ' />&nbsp;&nbsp;';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['awardbutton'] .= '<input type="button" style="font-size:12px" value=" {_decline} " onclick="confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\'); location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $disabled . ' />';
					}
					break;
				}
				// #### provider accepted buyers award
				case 'approval_accepted':
				{
					if ($bidstatus == 'declined')
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
					}
					else if ($bidstatus == 'awarded' AND ($bidstate != 'reviewing' OR $bidstate != 'wait_approval'))
					{
						$awarded_vendor = stripslashes($row['username']);
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'unawardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$buttonvisible = 'disabled="disabled"';
						if ($ilconfig['servicebid_buyerunaward'])
						{
							$buttonvisible = '';
						}
						$row['unawardbutton'] = '<input type="button" style="font-size:12px" value=" {_unaward} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $buttonvisible . ' />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'reviewing' OR $bidstatus == 'choseanother' AND $bidstate == 'reviewing')
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " style="font-size:12px" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
					{
						$awarded_vendor = stripslashes($row['username']);
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'unawardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['unawardbutton'] = '<input type="button" value="{_unaward}" onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else if ($bidstatus == 'placed' AND empty($bidstate))
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					break;
				}
				// #### buyer waiting for provider's acceptance to award
				case 'wait_approval':
				{
					// buyer awarded provider :: enable radio icons :: create additional award cancellation button
					if ($bidstatus == 'declined')
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
					{
						// buyer pending approval from service provider (provider did not confirm acceptance to project)
						$awarded_vendor = stripslashes($row['username']);
						$row['award'] = '{_pending_approval} <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pending_approval\'] + phrase[\'_day\'] +  ' . $days . '  + phrase[\'_of\'] + \' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>\' + phrase[\'_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'unawardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['unawardbutton'] = '<input type="button" value="{_unaward}" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
					{
						// service provider in review mode - 90% change will not become awarded
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'awardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['awardbutton'] = '<input type="button" value=" {_award} " onclick="location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />&nbsp;';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['awardbutton'] .= '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					break;
				}
				// #### listing is finished/completed
				case 'finished':
				{
					// project in a phase to not allow any bid controls
					if ($bidstatus == 'declined')
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
					{
						// buyer pending approval from service provider (provider did not confirm acceptance to project)
						$awarded_vendor = stripslashes($row['username']);
						$row['award'] = '{_pending_approval} <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pending_approval\'] + phrase[\'_day\'] + ' . $days . ' phrase[\'_of\'] + \' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>{_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction}</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'unawardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['unawardbutton'] = '<input type="button" value="{_unaward}" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:12px" />';
					}
					else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
					{
						// service provider in review mode - 90% change will not become awarded
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else if ($bidstatus == 'choseanother' AND $bidstate == 'reviewing')
					{
						// service provider in review mode - 90% change will not become awarded
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['declinebutton'] = '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:12px" />';
					}
					else if ($bidstatus == 'awarded')
					{
						$awarded_vendor = stripslashes($row['username']);
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
						$row['bidaction'] = '<input type="button" value=" {_unaward} " class="buttons" disabled="disabled" style="font-size:12px" />';
					}
					else
					{
						$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'awardbid',
							'bid_id' => $row['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$row['awardbutton'] = '<input type="button" value=" {_award} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:12px" />&nbsp;';
						$crypted = array (
							'cmd' => '_do-rfp-action',
							'bidcmd' => 'declinebid',
							'bid_id' => $rows['bid_id'],
							'bid_grouping' => $bidgrouping
						);
						$rows['awardbutton'] .= '<input type="button" value=" {_decline} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:12px" />';
					}
					break;
				}
				// #### listing is closed or delisted
				case 'closed':
				case 'delisted':
				{
					// project in a phase to not allow any bid controls
					$row['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
					break;
				}
			}
			$row['bidretraction'] = $row['bidretractdate'] = $row['bidretractreason'] = '';
			if ($bidstatus == 'retracted' AND isset($ilance->GPC['view']) AND $ilance->GPC['view'] == 'retracted')
			{
				$retractdate = $ilance->db->fetch_field(DB_PREFIX . "project_bid_retracts", "project_id = '" . $row['project_id'] . "' AND bid_id = '" . $row['bid_id'] . "' AND user_id = '" . $row['user_id'] . "'", "date");
				$retractrson = handle_input_keywords($ilance->db->fetch_field(DB_PREFIX . "project_bid_retracts", "project_id = '" . $row['project_id'] . "' AND bid_id = '" . $row['bid_id'] . "' AND user_id = '" . $row['user_id'] . "'", "reason"));
				$row['bidretractdate'] = print_date($retractdate);
				$row['bidretractreason'] = $retractrson;
				$row['bidretraction'] = '<div><blockquote><div><strong>{_bid_retraction_on} <span class="black">' . $row['bidretractdate'] . '</span></strong></div><div class="gray" style="padding-top:2px">' . $row['bidretractreason'] . '</div></blockquote></div>';
				unset($retractdate, $retractrson);
				// disable award & decline tools as this bid is retracted
				$row['awardbutton'] = '<input type="button" style="font-size:12px" value=" {_award} " class="buttons" disabled="disabled" />&nbsp;&nbsp;<input type="button" style="font-size:12px" value=" {_decline} " class="buttons" disabled="disabled" />';
			}
			// determine if we are not the owner
			if (empty($_SESSION['ilancedata']['user']['userid']) OR !empty($_SESSION['ilancedata']['user']['userid']) AND $row['project_user_id'] != $_SESSION['ilancedata']['user']['userid'])
			{
				// we are not the owner so disable buttons
				$row['shortlist'] = $row['awardbutton'] = $row['unawardbutton'] = $row['declinebutton'] = '';
				$row['bottombubbleclass'] = '';
			}
			else
			{
				$row['bottombubbleclass'] = 'bubble_b';
			}
			// #### custom bid field answers
			$row['custom_bid_fields'] = $ilance->bid_fields->construct_bid_fields($row['cid'], $row['project_id'], 'output1', 'service', $row['bid_id'], false);
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$row['classcolor'] = ($row_count % 2) ? '#eeeeee' : '#fff';
			$bid_results_rows[] = $row;
			$row_count++;
		}
		$show['no_bid_rows_returned'] = 0;
	}
	else
	{
		$show['no_bid_rows_returned'] = 1;
	}
	
	// #### PUBLIC MESSAGE BOARD ON LISTING ################
	$show['publicboard'] = 0;
	$boardcount = 0;
	if ($res['filter_publicboard'])
	{
		$show['publicboard'] = 1;
		$sqlmessages = $ilance->db->query("
			SELECT messageid, date, message, project_id, user_id, username
			FROM " . DB_PREFIX . "messages
			WHERE project_id = '" . $res['project_id'] . "'
			ORDER BY messageid ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlmessages) > 0)
		{
			$msgcount = 0;
			while ($message = $ilance->db->fetch_array($sqlmessages))
			{
				$message['date'] = print_date($message['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$message['message'] = ($message['user_id'] == $res['user_id']) ? '<span class="green">[{_buyer}]</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '' : '<span class="blue">[{_bidder}]</span> ' . strip_vulgar_words(ilance_htmlentities($message['message'])) . '';
				$message['class'] = ($msgcount % 2) ? 'alt1' : 'alt2';
				$messages[] = $message;
				$msgcount++;
			}
			$boardcount = $msgcount;
		}
	}
	$transactionstatus = $ilance->bid->fetch_transaction_status($id);
	$metakeywords = $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $cid);
	$metadescription = $ilance->categories->description($_SESSION['ilancedata']['user']['slng'], $cid);
	$videodescription = $ilance->auction->print_listing_video($id, $res['description_videourl'], '360', '280');
	$ilance->categories->add_category_viewcount($cid);
	$pageurl = HTTP_SERVER . $ilpage['rfp'] . '?id=' . $id;
	$purl = PAGEURL;
	if (!empty($purl))
	{
		$pageurl = $purl;
	}
	unset($purl);
	if ($res['project_details'] == 'public')
	{
		$project_details = '{_public_viewing}';
	}
	if ($res['project_details'] == 'invite_only')
	{
		$project_details = '{_by_invitation_only}';
	}
	if ($res['project_details'] == 'realtime')
	{
		$project_details = '{_realtime}';
	}
	// #### purchasers other listings ######################
	$otherlistings = $ilance->auction_listing->fetch_users_other_listings($res['user_id'], 'service', 24, $excludelist = array ($id), true);
	// #### last viewed items ##############################
	if (($lastviewedlistings = $ilance->cache->fetch('recentreviewedserviceauctions')) === false)
	{
		$lastviewedlistings = $ilance->auction_listing->fetch_recently_viewed_auctions('service', 10, 1, 0, '', true);
		$ilance->cache->store('recentreviewedserviceauctions', $lastviewedlistings);
	}
	if ($ilconfig['globalfilters_ajaxrefresh'])
	{
		$onload .= (isset($show['ended']) AND $show['ended']) ? 'refresh_project_details();' : 'window.setInterval(\'refresh_project_details()\', \'' . $ilconfig['globalfilters_countdowndelayms'] . '\');';
	}
	else
	{
		$onload .= (isset($show['ended']) AND $show['ended']) ? 'refresh_project_details();' : 'refresh_project_details();';
	}
	$jsend = '';
	if (defined('SUB_FOLDER_ROOT') AND SUB_FOLDER_ROOT != '')
	{
		$jsurl = SUB_FOLDER_ROOT;
	}
	else
	{
		$jsurl = ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER);
	}
	$headinclude .= '<script type="text/javascript">
<!--
ilance_date_format1 = \'%%D%%{_d_shortform}, %%H%%{_h_shortform}, %%M%%{_m_shortform}, %%S%%{_s_shortform}\';
ilance_date_format2 = \'%%H%%{_h_shortform}, %%M%%{_m_shortform}, %%S%%{_s_shortform}\';
ilance_date_format3 = \'%%M%%{_m_shortform}, %%S%%{_s_shortform}\';
ilance_date_format4 = \'%%S%%{_s_shortform}\';
StartMessage = \'{_starts}:\';
var dnow = mysql_datetime_to_js_date(\'' . DATETIME24H . '\');
var dstart = mysql_datetime_to_js_date(\'' . $res['date_starts'] . '\');
var dthen = mysql_datetime_to_js_date(\'' . $res['date_end'] . '\');
//-->
</script><script type="text/javascript" src="' . $jsurl . 'functions/javascript/functions_countdown' . (($ilconfig['globalfilters_jsminify']) ? '.min' : '') . '.js"></script>
';
	$jsend = (isset($show['ended']) AND $show['ended']) ? '' : '<script type="text/javascript">
<!--
refresh_item_countdown(isecs, \'service\');
refresh_project_details();
//-->
</script>';
    $headinclude .= '<script type="text/javascript">
<!--
';
	// #### admin is using AJAX refresh? ###########################
	if ($ilconfig['globalfilters_ajaxrefresh'])
	{
		$headinclude .= '
if (!window.XMLHttpRequest)
{
var reqObj = 
[
	function() {return new ActiveXObject("Msxml2.XMLHTTP");},
	function() {return new ActiveXObject("Microsoft.XMLHTTP");},
	function() {return window.createRequest();}
];
for(a = 0, z = reqObj.length; a < z; a++)
{
	try
	{
		window.XMLHttpRequest = reqObj[a];
		break;
	}
	catch(e)
	{
		window.XMLHttpRequest = null;
	}
}
} 
var req = new XMLHttpRequest();
function refresh_project_details()
{
req.abort();
req.open(\'GET\', \'' . AJAXURL . '?do=refreshprojectdetails&id=' . $res['project_id'] . '\');
req.onreadystatechange = function()
{
	if (req.readyState == 4 && req.status == 200)
	{
		var myString;
		myString = req.responseText;';
	}
	else
	{
		include_once(DIR_CORE . 'functions_ajax_service_response.php');
		$headinclude .= '
function refresh_project_details()
{
		var myString;
		myString = \'' . fetch_service_response($res['project_id']) . '\';
';
	}
	$headinclude .= '
		myString = myString.split("|");
		//fetch_js_object(\'timelefttext\').innerHTML = myString[0];
		fetch_js_object(\'bidstext\').innerHTML = myString[1];                        
		fetch_js_object(\'lowestbiddertext\').innerHTML = myString[2];
		fetch_js_object(\'highestbiddertext\').innerHTML = myString[3];
		fetch_js_object(\'awardedbiddertext\').innerHTML = myString[4];                        
		fetch_js_object(\'averagebidtext\').innerHTML = myString[5];
		fetch_js_object(\'projectstatustext\').innerHTML = myString[6];                        
		fetch_js_object(\'declinedbidstext\').innerHTML = myString[7];
		fetch_js_object(\'retractedbidstext\').innerHTML = myString[8];
		if (myString[10] == \'1\')
		{
			fetch_js_object(\'awardedbiddertext\').innerHTML = myString[4];
			toggle_show(\'awardedbidderrow\');
		}
		else
		{
			toggle_hide(\'awardedbidderrow\');
		}
		if (myString[11] == \'1\')
		{
			toggle_show(\'endedlistingrow\');
			toggle_hide(\'placebidrow\');
			toggle_hide(\'listingrevisionrow\');
		}
		else
		{
			toggle_hide(\'endedlistingrow\');
			toggle_show(\'placebidrow\');
			toggle_show(\'listingrevisionrow\');
			toggle_hide(\'awardedbidderrow\');
		}
		if (myString[14] == \'1\')
		{
			toggle_show(\'lowestbiddertextrow\');
			toggle_show(\'lowestbidderrow\');
			
			fetch_js_object(\'lowbiddertext\').innerHTML = myString[2];
			fetch_js_object(\'lowestbiddertext\').innerHTML = myString[12];
		}
		else
		{
			toggle_hide(\'lowestbiddertextrow\');
			toggle_hide(\'lowestbidderrow\');
		}
		';
	if ($ilconfig['globalfilters_ajaxrefresh'])
	{
		$headinclude .= '}
}        
req.send(null);
';
	}
	$headinclude .= '
}
//-->
</script>';

	// #### item watchlist logic ###################################
	if (!empty($_SESSION['ilancedata']['user']['userid']))
	{
		$show['addedtowatchlist'] = $ilance->watchlist->is_listing_added_to_watchlist($res['project_id']);
		$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($res['user_id']);
	}
	// #### seller tools: enhancements #############################
	$show['disableselectedenhancements'] = true;
	if ($res['featured'])
	{
		$ilance->GPC['enhancements']['featured'] = 1;
	}
	if ($res['featured_searchresults'])
	{
		$ilance->GPC['enhancements']['featured_searchresults'] = 1;
	}
	if ($res['highlite'])
	{
		$ilance->GPC['enhancements']['highlite'] = 1;
	}
	if ($res['bold'])
	{
		$ilance->GPC['enhancements']['bold'] = 1;
	}
	if ($res['autorelist'])
	{
		$ilance->GPC['enhancements']['autorelist'] = 1;
	}
	$enhancements = $ilance->auction_post->print_listing_enhancements('service');
	$featured = $res['featured'];
	$featured_searchresults = $res['featured_searchresults'];
	$featured_date = $res['featured_date'];
	$highlite = $res['highlite'];
	$bold = $res['bold'];
	$autorelist = $res['autorelist'];
	// #### buyer facts ############################################
	$facts = $ilance->auction_service->fetch_buyer_facts($project_user_id, 'service');
	$jobsposted = number_format($facts['jobsposted']);
	$jobsawarded = number_format($facts['jobsawarded']);
	$jobsdelisted = number_format($facts['jobsdelisted']);
	$awardratio = $facts['awardratio'];
    
	($apihook = $ilance->api('rfp_detailed_end')) ? eval($apihook) : false;
    
	$disabled_button = ($res['featured'] AND $res['highlite'] AND $res['bold'] AND $res['autorelist']) ? 'disabled="disabled"' : '';
	$ilance->template->fetch('main', 'listing_reverse_auction.html');
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('bid_results_rows','messages'));
	$ilance->template->parse_loop('main', array('otherlistings','lastviewedlistings'), false);
	$ilance->template->parse_if_blocks('main');
	$pprint_array = array ('jobsdelisted', 'listingcategory', 'maximum_bids', 'jobsposted', 'disabled_button', 'jobsawarded', 'awardratio', 'featured_searchresults', 'featured', 'featured_date', 'highlite', 'bold', 'autorelist', 'enhancements', 'videodescription', 'transactionstatus', 'jsend', 'otherlistings', 'lastviewedlistings', 'pageurl', 'bidsactive', 'shortlistedbids', 'retractedbids', 'declinedbids', 'updateid', 'lastrevision', 'buyerscore', 'buyername', 'bidprivacy', 'cid', 'bidhistoryinfo', 'category2', 'userbits', 'buyercity', 'buyerstate', 'buyercountry', 'paymentmethods', 'boardcount', 'views', 'bidtypefilter', 'escrowbit', 'icons', 'average', 'lowest', 'highest', 'project_questions','feed1', 'feed6', 'feed12', 'buyerstars', 'location', 'bids', 'started', 'ends', 'timeleft', 'placeabid', 'lowbidderid', 'lowbidder', 'cid', 'featured', 'realtime', 'category', 'subcategory', 'memberstart', 'countryname', 'collapserfpinfo_id', 'collapseimgrfpinfo_id', 'invite_list', 'rfpposted', 'rfpawards', 'fbcount', 'additional_info', 'project_user_id', 'lowest_bidder', 'highest_bidder', 'filter_permissions', 'awarded_vendor', 'project_status', 'bid_controls', 'buyer_incomespent', 'buyer_stars', 'project_title', 'description', 'project_type', 'project_details', 'project_budget', 'project_distance', 'project_id', 'bid_details', 'pmb', 'project_buyer', 'projects_posted', 'projects_awarded', 'project_currency', 'project_attachment', 'distance', 'subcategory_name', 'text', 'prevnext', 'prevnext2', 'remote_addr', 'rid',  'view_type', 'order_type', 'url_rfp');
    
	($apihook = $ilance->api('rfp_detailed_loop')) ? eval($apihook) : false;
    
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>