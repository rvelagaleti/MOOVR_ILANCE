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
if (!defined('LOCATION') OR defined('LOCATION') != 'merch')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
if (empty($ilance->GPC['id']) OR $ilance->GPC['id'] == 0)
{
	$area_title = '{_bad_rfp_warning_menu}';
	$page_title = SITE_NAME . ' - {_bad_rfp_warning_menu}';
	print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
	exit();
}
// #### DETAILED AUCTION LISTING #######################################
$show['widescreen'] = true;
$project_id = intval($ilance->GPC['id']);
$customquery = '';
$topnavlink = array(
	'merch_viewitem'
);
      
($apihook = $ilance->api('merch_listing_top_start')) ? eval($apihook) : false;
      
$sql = $ilance->db->query("
	SELECT p.*, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, s.ship_method, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS starttime
	FROM " . DB_PREFIX . "projects p
	LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
	WHERE p.project_id = '" . $project_id . "'
		AND p.project_state = 'product'
		AND p.visible = '1'
		$customquery
		" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.status != 'frozen' AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) == 0)
{
	($apihook = $ilance->api('merch_invalid_listing_id')) ? eval($apihook) : false;
	
	$area_title = '{_bad_rfp_warning_menu}<div class="smaller">' . (isset($_SERVER['HTTP_REFERER']) ? '{_referrer}: ' . handle_input_keywords($_SERVER['HTTP_REFERER']) : '{_referrer}: {_none}') . '</div>';
	$page_title = SITE_NAME . ' - {_bad_rfp_warning_menu}';
	print_notice('{_invalid_rfp_specified}', '{_your_request_to_review_or_place_a_bid_on_a_valid_request_for_proposal}', $ilpage['search'], '{_search_rfps}');
	exit();
}
$res = $ilance->db->fetch_array($sql, DB_ASSOC);
$auction_start = $res['starttime'];
$owner_id = intval($res['user_id']);
// #### prevent duplicate content from search engines
if ($ilconfig['globalauctionsettings_seourls'] AND (!isset($ilance->GPC['sef']) OR empty($ilance->GPC['sef'])))
{
	$seourl = construct_seo_url('productauctionplain', $res['cid'], $project_id, stripslashes($res['project_title']), '', 0, '', 0, 0, '');
	$view = isset($ilance->GPC['view']) ? '?view=' . $ilance->GPC['view'] : '';
	if (empty($view))
	{
		$view = isset($ilance->GPC['note']) ? '?note=' . $ilance->GPC['note'] : '';
	}
	else
	{
		$view .= isset($ilance->GPC['note']) ? '&note=' . $ilance->GPC['note'] : '';
	}
	header('Location: ' . $seourl . urldecode($view));
	exit();
}

($apihook = $ilance->api('merch_detailed_start')) ? eval($apihook) : false;

// #### revision details ###############################
$views = $res['views'];
$updateid = $res['updateid'];
$show['revision'] = false;
if ($res['updateid'] > 0)
{
	$show['revision'] = true;
	$updateid = '<a href="' . $ilpage['merch'] . '?cmd=revisionlog&amp;id=' . $project_id . '">' . $res['updateid'] . '</a>';
}
// #### bid increments in this category ################
$increment = '{_none}';
$cbid = $res['currentprice'];
$incrementgroup = $ilance->categories->incrementgroup($res['cid']);
$sql = $ilance->db->query("
       SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "amount
       FROM " . DB_PREFIX . "increments
       WHERE ((increment_from <= $cbid
	       AND increment_to >= $cbid)
		       OR (increment_from < $cbid
	       AND increment_to < $cbid))
	       AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
       ORDER BY amount DESC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
       $resincrement = $ilance->db->fetch_array($sql, DB_ASSOC);
       $increment = $ilance->currency->format($resincrement['amount'], $res['currencyid']);
}
// #### payment methods accepted #######################
$paymentmethods = $ilance->payment->print_payment_methods($project_id);
// #### public message board ###########################
$show['publicboard'] = $msgcount = 0;
if ($res['filter_publicboard'])
{
	$temp = $ilance->auction->fetch_public_messages($project_id);
	$messages = $temp[0];
	$msgcount = $temp[1];
	unset($temp);
}
// recently reviewed session saver
$ilance->auction->recently_viewed_handler($project_id, 'product');
$row_count = 0;
$invited = ((isset($ilance->GPC['invited']) AND $ilance->GPC['invited']) ? 1 : 0);
// photo slideshow
$show['slideshow'] = false;
$productimage = HTTP_SERVER . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'nophoto.gif';
$imagecount = 0;
$jspictures = "var pictures = {";
$jspictureso = "var originalpictures = {";
$jspicturet = $jspicturett = '';
$sql = $ilance->db->query("
	SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref, width_full, height_full, width_mini, height_mini, width_original, height_original
	FROM " . DB_PREFIX . "attachment
	WHERE project_id = '" . $project_id . "'
		AND tblfolder_ref != '-5'
		AND attachtype != 'digital'
		AND visible = '1'
	ORDER BY attachid ASC
", 0, null, __FILE__, __LINE__);
$total = $ilance->db->num_rows($sql);
if ($ilance->db->num_rows($sql) > 0)
{
	while ($res2 = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$imagecount++;
		$res['imgsrc'] = (($ilconfig['globalauctionsettings_seourls']) ? ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'i/thumb/itemphoto/' . $res2['filehash'] . '/' . $res2['width_full'] . 'x' . $res2['height_full'] . '_' . $res2['filename'] : ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $res2['width_full'] . '&h=' . $res2['height_full']);
		$jspicturet .= '"picture' . $imagecount . '": {"src": "' . $res['imgsrc'] . '", "width": "' . $res2['width_full'] . '", "height": "' . $res2['height_full'] . '"},';
		$res['imgsrc_original'] = (($ilconfig['globalauctionsettings_seourls']) ? ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'i/thumb/itemphoto/original/' . $res2['filehash'] . '/' . $res2['width_original'] . 'x' . $res2['height_original'] . '_' . $res2['filename'] : ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&original=1&id=' . $res2['filehash'] . '&w=' . $res2['width_original'] . '&h=' . $res2['height_original']);
		$jspicturett .= '"picture' . $imagecount . '": {"src": "' . $res['imgsrc_original'] . '?t=' . time() . '", "width": "' . $res2['width_original'] . '", "height": "' . $res2['height_original'] . '"},';
		if ($imagecount == 1)
		{
			$res['img'] = (($ilconfig['globalauctionsettings_seourls']) ? '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'i/thumb/itemphoto/' . $res2['filehash'] . '/' . $res2['width_full'] . 'x' . $res2['height_full'] . '_' . $res2['filename'] . '" width="' . $res2['width_full'] . '" height="' . $res2['height_full'] . '" alt="" id="big-picture" />' : '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $res2['width_full'] . '&h=' . $res2['height_full'] . '" width="' . $res2['width_full'] . '" height="' . $res2['height_full'] . '" alt="" id="big-picture" />');
			$productimage = (($ilconfig['globalauctionsettings_seourls']) ? ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'i/thumb/itemphoto/' . $res2['filehash'] . '/' . $res2['width_full'] . 'x' . $res2['height_full'] . '_' . $res2['filename'] : ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphoto&id=' . $res2['filehash'] . '&w=' . $res2['width_full'] . '&h=' . $res2['height_full']);
			$pictures[] = $res;
		}
		else
		{
			$res['img'] = '';
		}
		$res2['img'] = (($ilconfig['globalauctionsettings_seourls']) ? '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . 'i/thumb/itemphotomini/' . $res2['filehash'] . '/' . $res2['width_mini'] . 'x' . $res2['height_mini'] . '_' . $res2['filename'] . '" width="' . $res2['width_mini'] . '" height="' . $res2['height_mini'] . '" alt="" />' : '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&subcmd=itemphotomini&id=' . $res2['filehash'] . '&w=' . $res2['width_mini'] . '&h=' . $res2['height_mini'] . '" width="' . $res2['width_mini'] . '" height="' . $res2['height_mini'] . '" alt="" />');
		$res2['tab'] = $imagecount;
		$thumbnails[] = $res2;
		$thumbnails_modal[] = $res2;
	}						
}
if (!empty($jspicturet))
{
	$jspicturet = mb_substr($jspicturet, 0, -1);
}
$jspictures = $jspictures . $jspicturet . "};";
if (!empty($jspicturett))
{
	$jspicturett = mb_substr($jspicturett, 0, -1);
}
$jspictureso = $jspictureso . $jspicturett . "};";
if ($imagecount <= 1)
{
	$show['slideshow'] = false;
}
else if ($imagecount > 1)
{
	$show['slideshow'] = true;
}
if ($imagecount > 0)
{
	$show['productimage'] = true;
}
else
{
	$show['productimage'] = false;
}
if (!isset($pictures) OR (isset($pictures) AND !is_array($pictures)) OR (isset($pictures) AND count($pictures) <= 0))
{
	$pictures[0]['img'] = '<img src="' . (((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER)) . $ilconfig['template_imagesfolder'] . 'nophoto.gif" alt="" border="0" />';
}
$date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
$additional_info = stripslashes($res['additional_info']);
$show['bidderuninvited'] = false;
// #### seller information #############################
$sql_user_results = $ilance->db->query("
	SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id, country, zip_code, username, posthtml
	FROM " . DB_PREFIX . "users
	WHERE user_id = '" . $owner_id . "'
	LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql_user_results) > 0)
{
	$res_project_user = $ilance->db->fetch_array($sql_user_results, DB_ASSOC);
	unset($sql_user_results);
}
else
{
	print_notice('{_owner_delisted}', '{_sorry_the_owner_of_this_auction_has_been_delisted}', $ilpage['main'], '{_main_menu}');
	exit();
}
$show['description_html'] = ($res['ishtml'] == '1') ? true : false;
switch ($res['project_details'])
{
	case 'public':
	{
		// does admin require members to be logged in before viewing full description?
		if ($show['description_html'] == true)
		{
			$description = '<!-- start html description -->' . LINEBREAK . $res['description'] . LINEBREAK . '<!-- end html description -->';
		}
		else 
		{
			$description = '<!-- start bbcode description -->' . LINEBREAK . $ilance->bbcode->bbcode_to_html(strip_vulgar_words($res['description'], false)) . LINEBREAK . '<!-- end bbcode description -->';
		}
		break;
	}
	case 'invite_only':
	{
		// does admin require members to be logged in before viewing full description?
		$show['bidderuninvited'] = true;
		if (empty($_SESSION['ilancedata']['user']['userid']))
		{
			if ($show['description_html'] == true)
			{
				$description = '<!-- start html description -->' . LINEBREAK . $res['description'] . LINEBREAK . '<!-- end html description -->';
			}
			else 
			{
				$description = '<!-- start bbcode description -->' . LINEBREAK . $ilance->bbcode->bbcode_to_html(strip_vulgar_words($res['description'], false)) . LINEBREAK . '<!-- end bbcode description -->';
			}
		}
		else
		{
			// fetch invites
			$sql_invites = $ilance->db->query("
				SELECT id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '" . $project_id . "'
				    AND seller_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_invites) > 0)
			{
				$show['bidderuninvited'] = false;
				if ($show['description_html'] == true)
				{
					$description = '<!-- start html description -->' . LINEBREAK . $res['description'] . LINEBREAK . '<!-- end html description -->';
				}
				else 
				{
					$description = '<!-- start bbcode description -->' . LINEBREAK . $ilance->bbcode->bbcode_to_html(strip_vulgar_words($res['description'], false)) . LINEBREAK . '<!-- end bbcode description -->';
				}
			}
			else
			{
				if ($show['description_html'] == true)
				{
					$description = '<!-- start html description -->' . LINEBREAK . $res['description'] . LINEBREAK . '<!-- end html description -->';
				}
				else 
				{
					$description = '<!-- start bbcode description -->' . LINEBREAK . $ilance->bbcode->bbcode_to_html(strip_vulgar_words($res['description'], false)) . LINEBREAK . '<!-- end bbcode description -->';
					
				}
			}
			unset($sql_invites);
		}
		break;
	}
	case 'realtime':
	{
		if ($show['description_html'] == true)
		{
			$description = '<!-- start html description -->' . LINEBREAK . $res['description'] . LINEBREAK . '<!-- end html description -->';
		}
		else 
		{
			$description = '<!-- start bbcode description -->' . LINEBREAK . $ilance->bbcode->bbcode_to_html(strip_vulgar_words($res['description'], false)) . LINEBREAK . '<!-- end bbcode description -->';
		}
		break;
	}
}
// vulgar censor for description
$description = isset($description) ? $description : '{_no_description}';
// filtered auction type specified by seller
// used as an template if condition
if ($res['filtered_auctiontype'] == 'fixed')
{
	$auctiontype = 'fixed';
	$transactionstatus = $ilance->bid->fetch_transaction_status($project_id);
}
else if ($res['filtered_auctiontype'] == 'regular')
{
	$auctiontype = 'regular';
	$transactionstatus = $ilance->bid->fetch_transaction_status($project_id);
}
else if ($res['filtered_auctiontype'] == 'classified')
{
	$auctiontype = 'classified';
	$transactionstatus = '';
}
// does seller require bidders to use escrow to purchase item?
$show['seller_using_escrow'] = $show['filter_escrow'] = false;
$show['filter_offline'] = $res['filter_offline'];
$show['filter_gateway'] = $res['filter_gateway'];
$escrowbit = '';
if ($res['filter_escrow'] == '1' AND $ilconfig['escrowsystem_enabled'])
{
       $show['filter_escrow'] = $show['seller_using_escrow'] = true;
       $escrowbit = '{_seller_ships_item_after_secure_payment_via_escrow}' . ' <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/ico_escrow.png" border="0" alt="" />';
}
// #### has reserve price ##############################
$show['reserve_auction'] = false;
$show['reserve_met'] = true;
if ($res['reserve'])
{
	$show['reserve_auction'] = true;
	$highest_amount = '';
	$sql_highest = $ilance->db->query("
		SELECT MAX(bidamount) AS highest
		FROM " . DB_PREFIX . "project_bids
		WHERE project_id = '" . $project_id . "'
		ORDER BY highest
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_highest) > 0)
	{
		$res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
		if ($res_highest['highest'] >= $res['reserve_price'])
		{
			$show['reserve_met'] = true;
			$reserve_met = '{_yes_reserve_price_met}';
		}
		else
		{
			$show['reserve_met'] = false;
			$reserve_met = '{_no_reserve_price_not_met}';
		}
	}
	else
	{
		 $show['reserve_met'] = false;
		 $reserve_met = '{_no_reserve_price_not_met}';
	}
	unset($sql_highest, $highest_amount);
}
// #### is buynowable? #################################
$show['buynow_available'] = $show['buynow'] = $show['multipleqty'] = $show['lot'] = false;
$buynow_qty = $buynow_price = $buynow_price_plain = $items_in_lot = 0;
$show['lot'] = ($res['buynow_qty_lot'] == '1') ? true : false;
if ($res['buynow'] AND $res['buynow_price'] > 0)
{
	$show['buynow'] = true;
	$buynow_price = $ilance->currency->format($res['buynow_price'], $res['currencyid']);
	$buynow_price_plain = $res['buynow_price'];
	$buynow_qty = intval($res['buynow_qty']);
	if ($res['buynow_qty'] >= 1)
	{
		$show['buynow_available'] = true;
		$qty_pulldown = $apihook_output = '';
		$amount = $res['buynow_price'];
		if ($buynow_qty == 1)
		{
			$qty_pulldown = '<input type="hidden" name="qty" id="qty" value="1" />';
		}
		else
		{
			($apihook = $ilance->api('merch_qty_pulldown_multiple_items_onchange_js')) ? eval($apihook) : false;
			
			$show['multipleqty'] = true;
			$maxqty = $buynow_qty;
			$arr['optgroupstart'] = '{_qty}';
			$arr['1'] = ($show['lot']) ? '1 {_lot}' : '1 {_item_lower}';
			$lot = ($show['lot']) ? ' {_lots}' : ' {_items_lower}';
			for ($i = 2; $i <= $buynow_qty; $i++)
			{
			    $arr[$i] = $i . $lot;
			}
			$arr['optgroupend'] = '';
			$qty_pulldown = construct_pulldown('qty', 'qty', $arr, '', 'onChange="show_listing_shipping_rows(\'qty\', false);' . $apihook_output . '" style="font-family: verdana"');
		}
	}
}
$items_in_lot = intval($res['items_in_lot']);
$project_user_id = $res_project_user['user_id'];
$seller = print_username($res_project_user['user_id'], 'href', 0, '', '');
$sellerplain = $res_project_user['username'];
if (($memberinfo = $ilance->cache->fetch("memberinfo_" . $owner_id)) === false)
{
	$memberinfo = $ilance->feedback->datastore($owner_id);
	$ilance->cache->store("memberinfo_" . $owner_id, $memberinfo);
}
$merchantscore = $memberinfo['pcnt'];
$project_title = strip_vulgar_words(stripslashes($res['project_title']));
$project_title_plain = handle_input_keywords($project_title);
$icons = $ilance->auction->auction_icons($res);
if ($res['project_type'] == 'reverse')
{
       $project_type = '{_reverse_auction}';
}
else if ($res['project_type'] == 'forward')
{
       $project_type = '{_standard_auction}';
}
else if ($res['project_type'] == 'dutch')
{
       $project_type = '{_dutch_auction}';
}
else if ($res['project_type'] == 'quote')
{
       $project_type = '{_rfp_quote_only}';
}
else if ($res['project_type'] == 'trade')
{
       $project_type = '{_trade_only}';
}
else if ($res['project_type'] == 'resume')
{
       $project_type = '{_resume_listing}';
}
if ($res['project_details'] == 'public')
{
       $project_details = '{_public_viewing}';
}
else if ($res['project_details'] == 'invite_only')
{
       $project_details = '{_by_invitation_only}';
}
else if ($res['project_details'] == 'realtime')
{
       $project_details = '{_realtime}';
}
if ($res['bid_details'] == 'sealed')
{
       $bid_details = '{_sealed_bidding_hidden}';
}
else if ($res['bid_details'] == 'open')
{
       $bid_details = '{_public_bidding}';
}
else if ($res['bid_details'] == 'blind')
{
       $bid_details = '{_blind_bidding}';
}
else if ($res['bid_details'] == 'full')
{
       $bid_details = '{_full_bidding_privacy}';
}
$bids = $res['bids'];
$filter_permissions = $ilance->bid_permissions->print_filters('product', $project_id);
// seller information
$res_project_user['user_id'] = $owner_id;
// fetch highest bidder info
$sql_highest_bidder = $ilance->db->query("
       SELECT user_id
       FROM " . DB_PREFIX . "project_bids
       WHERE project_id = '" . $project_id . "'
       ORDER BY bidamount DESC, date_added ASC
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql_highest_bidder) > 0)
{
	$res_highest_bidder = $ilance->db->fetch_array($sql_highest_bidder, DB_ASSOC);
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$sql_bidplaced = $ilance->db->query("
			SELECT bid_id
			FROM " . DB_PREFIX . "project_bids
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND project_id = '" . $project_id . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_bidplaced) > 0)
		{
			$res['bidplaced'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid.gif" border="0" alt="{_you_have_placed_a_bid_on_this_auction}" />';
		}
		else
		{
			$res['bidplaced'] = '';
		}
	}
	else
	{
	       $res['bidplaced'] = '';
	}
	// format average, lowest and highest amounts
	$sel_bids_av = $ilance->db->query("
		SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest
		FROM " . DB_PREFIX . "project_bids
		WHERE project_id = '" . $project_id . "'
			AND bidstate != 'retracted'
			AND bidstatus != 'declined'
		ORDER BY highest
		LIMIT 1
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sel_bids_av) > 0)
	{
		$res_bids_av = $ilance->db->fetch_array($sel_bids_av);
		$highbidderid = $ilance->bid->fetch_highest_bidder($project_id);
		$highbidder = (isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'] == 1)) ? fetch_user('username', $highbidderid) : '**********';
	}
	else
	{
		$res_bids_av['average'] = $res_bids_av['lowest'] = $res_bids_av['highest'] = '';
		$highbidder = $highbidderid = $highbidderscore = $merchantstars = '';
	}
}
else
{
	$res_bids_av['average'] = $res_bids_av['lowest'] = $res_bids_av['highest'] = $res['bidplaced'] = '';
	$highbidder = $highbidderid = $highbidderscore = $merchantstars = '';
}
unset($sql_highest_bidder);
$cid = $res['cid'];
if ($res['bid_details'] == 'open')
{
	if (!empty($_SESSION['ilancedata']['user']['currencyid']))
	{
		$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $res['currencyid']);
		$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $res['currencyid']);
		$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $res['currencyid']);
	}
	else
	{
		$average = print_currency_conversion(0, $res_bids_av['average'], $res['currencyid']);
		$lowest  = print_currency_conversion(0, $res_bids_av['lowest'], $res['currencyid']);
		$highest = print_currency_conversion(0, $res_bids_av['highest'], $res['currencyid']);
	}
}
else if ($res['bid_details'] == 'sealed')
{
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res_project_user['user_id'])
	{
		$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $res['currencyid']);
		$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $res['currencyid']);
		$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $res['currencyid']);
	}
	else
	{
		// auction owner not viewing
		$average = '= {_sealed} =';
		$lowest = '= {_sealed} =';
		$highest = '= {_sealed} =';
	}
}
$show['ended'] = false;
$started = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
$winningbidder = $winningbid = '';
$winningbidderid = $ilance->bid->fetch_highest_bidder($project_id);
if ($winningbidderid > 0)
{
	$winningbidder = (isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname']))
		? fetch_user('username', $winningbidderid)
		: ((!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $winningbidderid) ? fetch_user('username', $winningbidderid) : '*******');
	//$winningbidder = fetch_user('username', $winningbidderid);
	$winningbid = $ilance->bid->fetch_awarded_bid_amount($project_id);
	$winningbid = $ilance->currency->format($winningbid, $res['currencyid']);
}
if ($res['status'] == 'finished')
{
	$ends = $ilance->auction->print_auction_status($res['status']) . ': ' . print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
	$show['ended'] = true;
}
else if ($res['status'] == 'expired' OR $res['status'] == 'archived')
{
	$show['ended'] = true;
}
else if ($res['status'] != 'open' AND $res['close_date'] != '0000-00-00 00:00:00')
{
	$ends = $ilance->auction->print_auction_status($res['status']) . ': ' . print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
	$timeleft = '{_ended_early}';
	$show['ended'] = true;
}
if ($res['close_date'] != '0000-00-00 00:00:00')
{
	if ($res['close_date'] < $res['date_end'])
	{
	       $ends = print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
	       $timeleft = '{_ended_early}';
	}
}
// invited buyers listings
$invite_list = '';
$externalbidders = $registeredbidders = 0;
$sql_invitations = $ilance->db->query("
	SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
	FROM " . DB_PREFIX . "project_invitations
	WHERE project_id = '" . $project_id . "'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql_invitations) > 0)
{
	while ($res_invitations = $ilance->db->fetch_array($sql_invitations, DB_ASSOC))
	{
		if ($res_invitations['buyer_user_id'] != '-1')
		{
			$sql_vendor = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "user_id
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res_invitations['buyer_user_id'] . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_vendor) > 0)
			{
				$registeredbidders++;
				$res_vendor = $ilance->db->fetch_array($sql_vendor, DB_ASSOC);
				if ($res_invitations['bid_placed'] == '0')
				{
					$invite_list .= print_username($res_vendor['user_id'], 'href');
					$invite_list .= ' [ <em>{_not_placed}</em> ], ';
				}
				else if ($res_invitations['bid_placed'] == '1')
				{
					$invite_list .= print_username($res_vendor['user_id'], 'href');
					$invite_list .= ' [ <strong>{_placed}</strong> ], ';
				}
			}
		}
		else
		{
			$externalbidders++;
		}
	}
}
unset($sql_invitations);
if ($externalbidders > 0 OR $registeredbidders > 0)
{
	if ($res['bid_details'] == 'blind' OR $res['bid_details'] == 'full')
	{
		if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
		{
			$invite_list = mb_substr($invite_list, 0, -2);
		}
		else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $owner_id)
		{
			$invite_list = mb_substr($invite_list, 0, -2);
		}
		else if (empty($_SESSION['ilancedata']['user']['userid']))
		{
			$invite_list = '= {_sealed} =';
		}
		else if (!empty($_SESSION['ilancedata']['user']['userid']))
		{
			$invite_list = '= {_sealed} =';
		}
	}
	else
	{
	       $invite_list = mb_substr($invite_list, 0, -2);
	}
	$invite_list = $invite_list . '<ul style="margin:18px; padding:0px;"><li>' . $externalbidders . ' {_bidders_invited_via_email}</li><li>' . $registeredbidders . ' {_registered_members_invited}</li></ul>';
}
else
{
	$invite_list = '{_no_bidders_invited}';
}
$checkup = $ilance->auction->print_item_photo('', 'checkup', $project_id);
$product_image = $product_image_thumb = '';
if ($checkup == '1')
{
	$show['productimage'] = 1;
	$product_image_thumb = $ilance->auction->print_item_photo('javascript:void(0)', 'thumb', $project_id, '0', '');
}
$feedback_score = $positive = $negative = '';
$memberstart = print_date(fetch_user('date_added', $owner_id), $ilconfig['globalserverlocale_globaldateformat'], 0, 0);
$city = $res['city'];
$state = $res['state'];
$location = $ilance->common_location->print_auction_location($project_id, '', $res['country'], $res['state'], $res['city'], $res['zipcode']);
// template if conditionals: admin viewing
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
{
	$show['is_owner'] = false;
	$show['cannot_bid'] = true;
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $project_user_id)
	{
		$show['is_owner'] = true;
	}
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
	{
		$show['cannot_bid'] = false;
	}
	$buyer_country = isset($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : '';
	$buyer_state = isset($_SESSION['ilancedata']['user']['state']) ? $_SESSION['ilancedata']['user']['state'] : '';
	$buyer_city = isset($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : '';
	$buyer_zipcode = isset($_SESSION['ilancedata']['user']['postalzip']) ? $_SESSION['ilancedata']['user']['postalzip'] : '';
}
// template if conditionals: registered member viewing
else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	$show['is_owner'] = false;
	$show['cannot_bid'] = false;
	if ($_SESSION['ilancedata']['user']['userid'] == $project_user_id)
	{
		$show['is_owner'] = true;
		$show['cannot_bid'] = false;
	}
	$buyer_country = isset($_SESSION['ilancedata']['user']['country']) ? $_SESSION['ilancedata']['user']['country'] : '';
	$buyer_state = isset($_SESSION['ilancedata']['user']['state']) ? $_SESSION['ilancedata']['user']['state'] : '';
	$buyer_city = isset($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : '';
	$buyer_zipcode = isset($_SESSION['ilancedata']['user']['postalzip']) ? $_SESSION['ilancedata']['user']['postalzip'] : '';
}
// template if conditionals: guest viewing
else
{
	$show['is_owner'] = false;
	$show['cannot_bid'] = true;
	$buyer_country = isset($_COOKIE[COOKIE_PREFIX . 'country']) ? $_COOKIE[COOKIE_PREFIX . 'country'] : '';
	$buyer_state = isset($_COOKIE[COOKIE_PREFIX . 'state']) ? $_COOKIE[COOKIE_PREFIX . 'state'] : '';
	$buyer_city = isset($_COOKIE[COOKIE_PREFIX . 'city']) ? $_COOKIE[COOKIE_PREFIX . 'city'] : '';
	$buyer_zipcode = isset($_COOKIE[COOKIE_PREFIX . 'radiuszip']) ? $_COOKIE[COOKIE_PREFIX . 'radiuszip'] : '';
}
$purchases = $ilance->auction_product->fetch_buynow_ordercount($project_id);
$auctionridcode = fetch_user('rid', $owner_id);
// purchase now logic
if (isset($show['buynow_available']) AND $show['buynow_available'] AND isset($amount) AND $amount > 0)
{
	// is there a highest bid placed?
	if ($res_bids_av['highest'] > 0)
	{
		// is the highest bid placed greater than the purchase now price?
		// make sure there is 1 or less qty available to purchase also.. we don't want to remove buy now option
		// if the seller has 2 or more items being sold via fixed price...
		if ($res_bids_av['highest'] > $amount AND $res['buynow_qty'] <= 1)
		{
			// it is.. so let's remove buy now option!
			$show['buynow_available'] = false;
		}
	}
}
else
{
	$show['buynow_available'] = false;
}
$categoryname = $ilance->categories->recursive($cid, 'product', $_SESSION['ilancedata']['user']['slng'], 0, '', $ilconfig['globalauctionsettings_seourls']);
$listingcategory = $categoryname;
// prevent the top cats in breadcrumb to contain any fields from this form
$show['nourlbit'] = true;
$navcrumb = array();
if ($ilconfig['globalauctionsettings_seourls'])
{
	$catmap = print_seo_url($ilconfig['productcatmapidentifier']);
	$navcrumb["$catmap"] = '{_buy}';
	unset($catmap);
}
else
{
	$navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
}
$ilance->categories->breadcrumb($cid, 'product', $_SESSION['ilancedata']['user']['slng']);
$navcrumb[""] = $project_title;
// custom category questions
if (($project_questions = $ilance->cache->fetch("project_questions_" . $project_id . "_output_4")) === false)
{
	$project_questions = $ilance->auction_questions->construct_auction_questions($cid, $project_id, 'output', 'product', 4);
	$ilance->cache->store("project_questions_" . $project_id . "_output_4", $project_questions);
}
if (!empty($project_questions))
{
	$show['itemspecifics'] = true;
}
// template if conditionals
$show['is_winner'] = $show['is_high_bidder'] = $show['is_outbid'] = $show['directpay'] = $show['directpaycompleted'] = false;
$directpayurl = $directpaybit = '';
$buynoworderid = 0;
if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->bid->fetch_highest_bidder($project_id) == $_SESSION['ilancedata']['user']['userid'])
{
	$show['is_high_bidder'] = true;
}
if (!empty($_SESSION['ilancedata']['user']['userid']))
{
	$show['is_outbid'] = $ilance->bid->is_outbid($_SESSION['ilancedata']['user']['userid'], $project_id);
	// this will also populate $show['wonbyauction'] and/or $show['wonbypurchase'] so we can present proper url link to user
	$show['is_winner'] = $ilance->bid->is_winner($_SESSION['ilancedata']['user']['userid'], $project_id);
	//$show['directpay'] = true;
	$buynoworderid = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($project_id) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "orderid");
	$winnermarkedaspaid = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($project_id) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "winnermarkedaspaid");
	$winnermarkedaspaiddate = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "project_id = '" . intval($project_id) . "' AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' AND amount > 0 AND status = 'offline'", "winnermarkedaspaiddate");
	$directpayurl = ($buynoworderid > 0) ? HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . intval($project_id) . '&amp;orderid=' . $buynoworderid : HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $project_id;
	if ($buynoworderid > 0 AND $winnermarkedaspaid == 1)
	{
		$show['directpaycompleted'] = true;
		$directpaybit = '{_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate) . '';
	}
}
$returnpolicy = $returnsaccepted = $returnwithin = $returngivenas = $returnshippingpaidby = $additional_info = '';
$returnpolicy = (!empty($res['returnpolicy'])) ? handle_input_keywords($res['returnpolicy']) : '';
$show['returnpolicy'] = false;
$returnsaccepted = '{_no}';
if ($res['returnaccepted'])
{
	$show['returnpolicy'] = true;
	$returnsaccepted = '{_yes}';
	$returnwithin = intval($res['returnwithin']);
	$returngivenas = '{_' . $res['returngivenas'] . '}';
	$returnshippingpaidby = '{_' . $res['returnshippaidby'] . '}';
}
$min_bidamount = sprintf("%.02f", '0.01');
$min_bidamountformatted = $ilance->currency->format('0.01', $res['currencyid']);
$highestbid = 0;
if ($res['bids'] <= 0)
{
	// do we have starting price?
	if ($res['startprice'] > 0)
	{
		$min_bidamount = sprintf("%.02f", $res['startprice']);
		$min_bidamountformatted = $ilance->currency->format($res['startprice'], $res['currencyid']);
	}
}
else if ($res['bids'] > 0)
{
	// highest bid amount placed for this auction
	$highestbid = $ilance->bid->fetch_highest_bid($project_id);
	// if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
	if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0)
	{
		$min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
		$min_bidamountformatted = $ilance->currency->format(($highestbid + $resincrement['amount']), $res['currencyid']);
	}
	else
	{	
		$min_bidamount = sprintf("%.02f", $highestbid);
		$min_bidamountformatted = $ilance->currency->format($highestbid, $res['currencyid']);
	}
}
// #### sellers other items ############################
if (($otherlistings = $ilance->cache->fetch("user_other_listings_" . $owner_id . "_" . $project_id . "_24")) === false)
{
	$otherlistings = $ilance->auction_listing->fetch_users_other_listings($owner_id, 'product', 24, array($project_id), true);
	$ilance->cache->store("user_other_listings_" . $owner_id . "_" . $project_id . "_24", $otherlistings);
}
$show['categoryuseproxybid'] = false;
if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $res['cid']))
{
	$show['categoryuseproxybid'] = true;
}
$show['startprice'] = $show['currentbid'] = $show['proxybit'] = 0;
$show['is_bid_owner'] = false;
$startprice = $ilance->currency->format($res['startprice'], $res['currencyid']);
$proxybit = '';
if ($res['bids'] > 0)
{
	$show['currentbid'] = 1;
	$currentbid = '<strong>' . $ilance->currency->format($res['currentprice'], $res['currencyid']) . '</strong>';
	if (!empty($_SESSION['ilancedata']['user']['userid']))
	{
		$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($project_id, $_SESSION['ilancedata']['user']['userid']);
		if ($pbit > 0)
		{
			$show['proxybit'] = 1;
			$proxybit = '{_your_maximum_bid}: ' . $ilance->currency->format($pbit, $res['currencyid']);
			$proxybit2 = $ilance->currency->format($pbit, $res['currencyid']);
			if ($pbit > $min_bidamount)
			{
				$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
				$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res['currencyid']);
			}
		}
		$show['is_bid_owner'] = $ilance->bid->is_bid_placed($project_id, $_SESSION['ilancedata']['user']['userid']);
	}
}
else
{
       $show['startprice'] = true;
}
$show['donation'] = false;
$donationtransaction = '{_the_donation_associated_with_this_nonprofit_has_not_been_marked}';
if ($res['donation'] AND $res['charityid'] > 0)
{
	$show['donation'] = true;
	$charity = fetch_charity_details($res['charityid']);
	$donationto = '<a href="' . $ilpage['nonprofits'] . '?id=' . $res['charityid'] . '">' . $charity['title'] . '</a>';
	$donationurl = $charity['url'];
	$donationpercentage = intval($res['donationpercentage']);
	if ($res['donermarkedaspaid'] AND $res['donermarkedaspaiddate'] != '0000-00-00 00:00:00')
	{
		 $donationtransaction = '{_the_donation_assoicated_with_this_nonprofit_was_marked_as_paid_on} <strong>' . print_date($res['donermarkedaspaiddate']) . '</strong>';
	}
}
// page url
$pageurl = urlencode($ilpage['merch'] . '?id=' . $project_id);
// video description
$videodescription = $ilance->auction->print_listing_video($project_id, $res['description_videourl'], '490', '364');
$ship_handlingtime = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $project_id . "'", "ship_handlingtime");
// update category view count
$ilance->categories->add_category_viewcount($cid);
$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
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
var dnow = mysql_datetime_to_js_date(\'' . DATETIME24H . '\');
var dstart = mysql_datetime_to_js_date(\'' . $res['date_starts'] . '\');
var dthen = mysql_datetime_to_js_date(\'' . $res['date_end'] . '\');
//-->
</script>
<script type="text/javascript" src="' . $jsurl . 'functions/javascript/functions_countdown' . (($ilconfig['globalfilters_jsminify']) ? '.min' : '') . '.js"></script>';

	$jsend = (isset($show['ended']) AND $show['ended'])
	? '<script type="text/javascript">
<!--
refresh_item_details(\'' . $auctiontype . '\');
//-->
</script>'
	: '<script type="text/javascript">
<!--
refresh_item_countdown(isecs, \'' . $auctiontype . '\');
refresh_item_details(\'' . $auctiontype . '\');
window.setInterval(\'refresh_item_details(\\\'' . $auctiontype . '\\\')\', \'' . $ilconfig['globalfilters_countdowndelayms'] . '\');
//-->
</script>';

	$headinclude .= '<script type="text/javascript">
<!--
';
	// #### admin is using AJAX refresh? ###################
	if ($ilconfig['globalfilters_ajaxrefresh'])
	{
		$headinclude .= 'if (!window.XMLHttpRequest)
{
        var reqObj = 
        [
                function() {return new ActiveXObject("Msxml2.XMLHTTP");},
                function() {return new ActiveXObject("Microsoft.XMLHTTP");},
                function() {return window.createRequest();}
        ];
        for (a = 0, z = reqObj.length; a < z; a++)
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
function refresh_item_details(type)
{
        req.open(\'GET\', \'' . AJAXURL . '?do=refreshitemdetailsv4&id=' . $project_id . '&type=' . $auctiontype . '&s=' . session_id() . '&token=' . TOKEN . '\');
	req.onreadystatechange = function()
	{
		if (req.readyState == 4 && req.status == 200)
		{
                        var myString;
                        myString = req.responseText;';
	}
	else
	{
		include_once(DIR_CORE . 'functions_ajax_product_response.php');
		$headinclude .= '
function refresh_item_details(type)
{
			var myString;
			myString = \'' . fetch_product_response_v4($project_id, $auctiontype, $invited) . '\';
';
	}
	$headinclude .= '
			myString = myString.split(\'|\');
                        if (type == \'regular\')
                        {
				fetch_js_object(\'dnow\').value = myString[22];
                                fetch_js_object(\'dstart\').value = myString[20];
				fetch_js_object(\'dthen\').value = myString[21];
				fetch_js_object(\'endstext\').innerHTML = myString[11];
                                fetch_js_object(\'bidstext\').innerHTML = myString[1];
                                fetch_js_object(\'bidstext_modal\').innerHTML = myString[1];
				fetch_js_object(\'timeleftphrase\').innerHTML = myString[13];
				if (myString[10] != \'\')
				{
					fetch_js_object(\'listing-notices\').innerHTML = myString[10];
				}
				if (myString[15] > 0)
				{
					toggle_show(\'bidretractsrow\');
					fetch_js_object(\'retractcount\').innerHTML = myString[15];
				}
                                if (myString[2] != \'\')
                                {
                                        fetch_js_object(\'startbidtext\').innerHTML = myString[2];
                                        toggle_show(\'startpricerow\');
                                        toggle_show(\'placebidrow\');
                                        toggle_hide(\'currentpricerow\');
                                        if (myString[9] == \'1\')
                                        {
                                                toggle_hide(\'buynowrow\');
';

		($apihook = $ilance->api('refresh_item_details_js_condition_6')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_show(\'buynowrow\');
';
		  
		($apihook = $ilance->api('refresh_item_details_js_condition_7')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                }
                                else if (myString[3] != \'\' && myString[1] > 0)
                                {
                                        fetch_js_object(\'currentbidtext\').innerHTML = myString[3];
                                        toggle_hide(\'startpricerow\');
                                        toggle_show(\'placebidrow\');
                                        toggle_show(\'currentpricerow\');
					toggle_show(\'bidsrow\');
                                        if (myString[9] == \'1\')
                                        {
';
	 
		($apihook = $ilance->api('refresh_item_details_js_condition_8')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_show(\'buynowrow\');
';

		($apihook = $ilance->api('refresh_item_details_js_condition_9')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                }
                                if (myString[4] != \'\')
                                {
                                        fetch_js_object(\'reservemettext\').innerHTML = myString[4];
                                        if (myString[8] != \'\')
                                        {
                                                toggle_hide(\'winningbidderrow\');
                                        }
                                        if (myString[9] == \'1\')
                                        {
';

		($apihook = $ilance->api('refresh_item_details_js_condition_10')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                        if (myString[9] == \'0\')
                                        {
                                                toggle_hide(\'winningbidderrow\');
';

		($apihook = $ilance->api('refresh_item_details_js_condition_11')) ? eval($apihook) : false;
		
		$headinclude .= '
                                        }
                                }
                                if (myString[5] != \'\' && myString[6] != \'\')
                                {
                                        fetch_js_object(\'minimumbidtext\').innerHTML = myString[5];
                                        fetch_js_object(\'minimumbidtext_modal\').innerHTML = myString[5];
                                        fetch_js_object(\'hiddenfieldminimum\').value = myString[6];
                                }
                                if (myString[7] != \'\')
                                {
                                        fetch_js_object(\'purchasestext\').innerHTML = myString[7];
                                }
                                if (myString[14] == \'1\')
                                {
                                        fetch_js_object(\'winningbidamounttext\').innerHTML = myString[17];
                                        toggle_show(\'winningbidderrow\');
                                        toggle_show(\'winningbidrow\');
                                        toggle_hide(\'currentpricerow\');
                                        toggle_hide(\'startpricerow\');
                                        toggle_hide(\'placebidrow\');
';

		($apihook = $ilance->api('refresh_item_details_js_condition_12')) ? eval($apihook) : false;
		
		$headinclude .= '
                                }
                                if (myString[14] == \'0\')
                                {
                                        toggle_hide(\'winningbidderrow\');
                                        toggle_hide(\'winningbidrow\');
					if (myString[16] == \'1\' && myString[1] > 0)
					{
						toggle_show(\'highestbidrow\');
					}
                                }
                                if (myString[12] == \'1\')
                                {
                                        toggle_hide(\'currentpricerow\');
                                        toggle_hide(\'startpricerow\');
                                        toggle_hide(\'placebidrow\');
					toggle_hide(\'bidincrementsrow\');
                                }
				else
				{
					toggle_show(\'bidincrementsrow\');
				}
				if (myString[16] == \'1\')
				{
					toggle_hide(\'shippinginforow\');
					toggle_hide(\'timeleftrow\');
				}
				else
				{
					toggle_show(\'shippinginforow\');
					toggle_show(\'timeleftrow\');
				}
				if (myString[19] == \'0\' || myString[23] == \'1\')
				{
					fetch_js_object(\'place_bid_button\').disabled = true;
					fetch_js_object(\'purchase_now_button\').disabled = true;
';
		($apihook = $ilance->api('refresh_item_details_js_condition_1')) ? eval($apihook) : false;
		
		$headinclude .= '
				}
				else
				{
					fetch_js_object(\'place_bid_button\').disabled = false;
					fetch_js_object(\'purchase_now_button\').disabled = false;
';
		($apihook = $ilance->api('refresh_item_details_js_condition_2')) ? eval($apihook) : false;
		
		$headinclude .= '
				}
				';
		$headinclude .= ((isset($show['ended']) AND $show['ended'])
			   ? ''
			   : '
				if (myString[14] == \'1\' || myString[16] == \'1\')
				{
					//timed_refresh(300);
				}');

		$headinclude .= '
			}
                        else if (type == \'fixed\')
                        {
				fetch_js_object(\'dnow\').value = myString[9];
                                fetch_js_object(\'dstart\').value = myString[7];
				fetch_js_object(\'dthen\').value = myString[8];
				fetch_js_object(\'endstext\').innerHTML = myString[5];
                                fetch_js_object(\'purchasestext\').innerHTML = myString[1];
				fetch_js_object(\'timeleftphrase\').innerHTML = myString[12];
                                toggle_show(\'endsrow\');
                                toggle_show(\'buynowrow\');
';

		($apihook = $ilance->api('refresh_item_details_js_condition_5')) ? eval($apihook) : false;
		
		$headinclude .= '
                                toggle_hide(\'placebidrow\');
                                toggle_hide(\'winningbidderrow\');
                                toggle_hide(\'currentpricerow\');
                                toggle_hide(\'startpricerow\');
                                toggle_hide(\'bidsrow\');
				if (myString[11] != \'\')
				{
					fetch_js_object(\'listing-notices\').innerHTML = myString[11];
				}
				if (myString[3] == \'1\')
				{
					toggle_hide(\'shippinginforow\');
					toggle_hide(\'timeleftrow\');
				}
				else
				{
					toggle_show(\'timeleftrow\');
					if (myString[4] == \'1\')
					{
						toggle_show(\'shippinginforow\');
					}
				}
				if (myString[3] == \'1\')
				{
					toggle_hide(\'buynowcontrol_row\');
				}
				else
				{
					toggle_show(\'buynowcontrol_row\');
				}
				if (myString[6] == \'0\' || myString[10] == \'1\')
				{
					fetch_js_object(\'purchase_now_button\').disabled = true;
';
		($apihook = $ilance->api('refresh_item_details_js_condition_3')) ? eval($apihook) : false;
		
		$headinclude .= '
				}
				else
				{
					fetch_js_object(\'purchase_now_button\').disabled = false;
';

		($apihook = $ilance->api('refresh_item_details_js_condition_4')) ? eval($apihook) : false;
		
		$headinclude .= '
				}
				';
		$headinclude .= ((isset($show['ended']) AND $show['ended'])
		? ''
		: '
				if (myString[3] == \'1\')
				{
					//timed_refresh(300);
				}');

		  $headinclude .= '
                        }
			';
		if ($ilconfig['globalfilters_ajaxrefresh'])
		{
			$headinclude .= '
                }
        }
        req.send(null);';
		}
		$headinclude .= '
}
';
		$headinclude .= '
function show_listing_shipping_rows(qtyfield, checkcity)
{
';
		$headinclude .= (isset($show['localpickuponly']) AND $show['localpickuponly'])
		? 'return false;
		'
		: 'cookieexpire = new Date();
	cookieexpire.setTime(cookieexpire.getTime() + (500 * 86400 * 3));	
	var countryid = fetch_js_object(\'showshippingdestinations\').options[fetch_js_object(\'showshippingdestinations\').selectedIndex].value;
	if (countryid == \'\')
	{
		return false;
	}
	var state = fetch_js_object(\'showshippingdestinationsstate\').options[fetch_js_object(\'showshippingdestinationsstate\').selectedIndex].value;
	var tcity = fetch_js_object(\'showshippingdestinationscity\').selectedIndex;
	if (typeof tcity === \'undefined\')
	{
		var city = fetch_js_object(\'showshippingdestinationscity\').value;
		if (city == \'\')
		{
			if (checkcity)
			{
				alert_js(\'{_please_enter_a_city_item_shipped}\');
				return false;
			}
		}
	}
	else
	{
		var city = fetch_js_object(\'showshippingdestinationscity\').options[fetch_js_object(\'showshippingdestinationscity\').selectedIndex].value;
	}
	var shipradiuszip = fetch_js_object(\'shipradiuszip\').value;
	';
		$headinclude .= '
	// hide services rows temporarily so we can redraw
	for (var i = 1; i <= ' . $ilconfig['maxshipservices'] . '; i++)
	{
		var o = fetch_js_object(\'ship_options_\' + i);
		if (o)
		{
			toggle_hide(\'ship_options_\' + i);
		}
		var z = fetch_js_object(\'shippinginfobit_\' + i);
		if (z)
		{
			fetch_js_object(\'shippinginfobit_\' + i).innerHTML = \'\';
		}
	}
	var countrytitle = fetch_js_object(\'showshippingdestinations\').options[fetch_js_object(\'showshippingdestinations\').selectedIndex].text;
	if (fetch_js_object(qtyfield))
	{
		var qty = fetch_js_object(qtyfield).value;
	}
	else
	{
		var qty = \'1\';
	}
	var radiuszip = fetch_js_object(\'shipradiuszip\').value;
	fetch_js_object(\'showshippingdestinations\').disabled = true;
	fetch_js_object(\'showshippingdestinationsstate\').disabled = true;
	fetch_js_object(\'ship_getratesbutton\').disabled = true;
	fetch_js_object(\'ship_qty\').disabled = true;
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
		if (ajaxRequest.readyState == 4 && ajaxRequest.status == 200)
		{
			var myString, myString2;
			toggle_show(\'shippinginforow\');
                        myString = ajaxRequest.responseText;
                        myString = myString.split(\'|\');
			for (var i = 1; i <= myString[0]; i++)
			{
				myString2 = myString[i].split("~~~~");
				var b = fetch_js_object(\'ship_amount_\' + i)
				if (b)
				{
					if (myString2[2] != \'\')
					{
						toggle_show(\'ship_options_\' + i);
						fetch_js_object(\'ship_amount_\' + i).innerHTML = \'\' + myString2[0] + \'\';
						fetch_js_object(\'ship_countries_\' + i).innerHTML = \'\' + myString2[1] + \'\';
						fetch_js_object(\'ship_service_\' + i).innerHTML = \'\' + myString2[2] + \'\';
						fetch_js_object(\'ship_estimate_\' + i).innerHTML = myString2[3];
						fetch_js_object(\'shippinginfobit_\' + i).innerHTML = \'\' + myString2[0] + \' {_via} \' + myString2[2] + \' {_to} \' + countrytitle + \'\';
					}
				}
			}			
			fetch_js_object(\'showshippingdestinations\').disabled = false;
			fetch_js_object(\'showshippingdestinationsstate\').disabled = false;
			fetch_js_object(\'ship_getratesbutton\').disabled = false;
			fetch_js_object(\'ship_qty\').disabled = false;
		}
	}	
	var querystring = "&countryid=" + countryid + "&state=" + state + "&city=" + city + "&pid=' . intval($project_id) . '&qty=" + qty + "&radiuszip=" + radiuszip + "&s=" + ILSESSION + "&token=" + ILTOKEN;
	ajaxRequest.open(\'GET\', \'' . AJAXURL . '?do=showshipservicerows\' + querystring, true);
	ajaxRequest.send(null);
}
//-->
</script>';
// #### item watchlist logic ###########################
if (!empty($_SESSION['ilancedata']['user']['userid']))
{
	$show['addedtowatchlist'] = $ilance->watchlist->is_listing_added_to_watchlist($project_id);
	$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($owner_id);
}
// #### seller tools: enhancements #####################
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
$enhancements = $ilance->auction_post->print_listing_enhancements('product');
$featured = $res['featured'];
$featured_searchresults = $res['featured_searchresults'];
$featured_date = $res['featured_date'];
$highlite = $res['highlite'];
$bold = $res['bold'];
$autorelist = $res['autorelist'];
$area_title = '{_viewing_detailed_item}<div class="smaller">' . handle_input_keywords(stripslashes($res['project_title'])) . ' (#' . $project_id . ')</div>';
$var2 = $ilance->categories->fetch_cat_ids($cid, '', 'product');
$var_val = explode(',', $var2);
sort($var_val);
$count = count($var_val);
$res_title_new = '';
for ($i = 0; $i < $count; $i++)
{
	$res_title_new .= ' | ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], intval($var_val[$i]));
}
$page_title = handle_input_keywords(stripslashes($res['project_title'] . $res_title_new)) . ' | ' . SITE_NAME;
$metakeywords = handle_input_keywords(stripslashes($res['project_title'])) . ', ' . (!empty($res['keywords']) ? handle_input_keywords($res['keywords']) . ', ' : '') . $ilance->categories->keywords($_SESSION['ilancedata']['user']['slng'], $res['cid']);
$metadescription = handle_input_keywords(strip_tags($ilance->bbcode->strip_bb_tags(trim(preg_replace("/[\\n\\r\\t]+/", ' ', $res['description'])))));
// #### shipping logic controls ########################
// if we're a guest and we don't have the region modal cookie let's ask for it
$cookieregion = (!empty($_COOKIE[COOKIE_PREFIX . 'region'])) ? $_COOKIE[COOKIE_PREFIX . 'region'] : '';
$cookiecountry = (!empty($_COOKIE[COOKIE_PREFIX . 'country'])) ? $_COOKIE[COOKIE_PREFIX . 'country'] : $ilconfig['registrationdisplay_defaultcountry'];
$country_user_id = (isset($_SESSION['ilancedata']['user']['countryid'])) ? $_SESSION['ilancedata']['user']['countryid'] : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], fetch_site_slng());
$full_country_pulldown = $ilance->common_location->construct_country_pulldown($country_user_id , $cookiecountry, 'country', true, '', false, false, false);
if (empty($_COOKIE[COOKIE_PREFIX . 'regionmodal']) AND $ilconfig['globalfilters_regionmodal'])
{
	$onload .= 'jQuery(\'#zipcode_nag_modal\').jqm({modal: false}).jqmShow();';
	// don't ask this guest for region info via popup modal for 3 days
	set_cookie('regionmodal', DATETIME24H, true, true, false, 3);
}
$shippinginfobit_1 = $shippinginfobit_2 = $shippinginfobit_3 = $shippinginfobit_4 = $shippinginfobit_5 = '';
if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $project_id]))
{
	$shippinginfobit_1 = $_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $project_id];
}
if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_2_' . $project_id]))
{
	$shippinginfobit_2 = $_COOKIE[COOKIE_PREFIX . 'shipping_2_' . $project_id];
}
if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_3_' . $project_id]))
{
	$shippinginfobit_3 = $_COOKIE[COOKIE_PREFIX . 'shipping_3_' . $project_id];
}
if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_4_' . $project_id]))
{
	$shippinginfobit_4 = $_COOKIE[COOKIE_PREFIX . 'shipping_4_' . $project_id];
}
if (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_5_' . $project_id]))
{
	$shippinginfobit_5 = $_COOKIE[COOKIE_PREFIX . 'shipping_5_' . $project_id];
}
$show['localpickuponly'] = $show['digital_download_delivery'] = false;
$countryid = 0;
if (!empty($_SESSION['ilancedata']['user']['countryid']))
{
	$countryid = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : '';
}
else
{
	if (!empty($_COOKIE[COOKIE_PREFIX . 'country']))
	{
		$countryid = fetch_country_id($_COOKIE[COOKIE_PREFIX . 'country'], fetch_site_slng());
	}
	else if (!empty($_COOKIE[COOKIE_PREFIX . 'region']) AND strrchr($_COOKIE[COOKIE_PREFIX . 'region'], '.'))
	{
		
		$c = explode('.', $_COOKIE[COOKIE_PREFIX . 'region']);
		$countryid = $c[1];
		unset($c);
	}
	else
	{
		$countryid = fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], fetch_site_slng());
	}
}
$shiptocountries = $ilance->shipping->print_item_shipping_countries_string($project_id);
$changelocationpulldown = $ilance->shipping->print_item_shipping_countries_pulldown($project_id, false, false, $show['shipsworldwide'], $countryid, true, 'showshippingdestinations', 'showshippingdestinationsstate', 'showshippingdestinationsstateid', false, 'showshippingdestinationscity', 'showshippingdestinationscityid');
$changelocationstatepulldown = '<span id="showshippingdestinationsstateid">' . $ilance->common_location->construct_state_pulldown($countryid, (!empty($_COOKIE[COOKIE_PREFIX . 'state']) ? $_COOKIE[COOKIE_PREFIX . 'state'] : ''), 'showshippingdestinationsstate', false, true, 0, 'width:140px', 0, 'showshippingdestinationscity', 'showshippingdestinationscityid') . '</span>';
$changelocationcitypulldown = '<div id="showshippingdestinationscityid">' . $ilance->common_location->construct_city_pulldown((!empty($_COOKIE[COOKIE_PREFIX . 'state']) ? $_COOKIE[COOKIE_PREFIX . 'state'] : ''), 'showshippingdestinationscity', (!empty($_COOKIE[COOKIE_PREFIX . 'city']) ? $_COOKIE[COOKIE_PREFIX . 'city'] : ''), false, true, 'width:140px') . '</div>';
$shipservicepulldown = $ilance->shipping->print_shipping_methods($project_id, 1, false, false, true, $countryid, $_SESSION['ilancedata']['user']['slng']);
$shippercount = $ilance->shipping->print_shipping_methods($project_id, 1, false, true, false, $countryid, $_SESSION['ilancedata']['user']['slng']);
$jsend .= (isset($countryid) AND $res['ship_method'] != 'localpickup' AND $res['ship_method'] != 'digital' AND $countryid > 0 AND $ilance->shipping->can_item_ship_to_countryid($project_id, $countryid) AND isset($show['ended']) AND $show['ended'] == false)
	? '<script type="text/javascript">
<!--
show_listing_shipping_rows(\'qty\', false);
//-->
</script>
'
	: '';
if ($res['ship_method'] == 'localpickup')
{
	$show['localpickuponly'] = true;
	$shippinginfobit_1 = '{_local_pickup_only}';
	$shiptocountries = '{_local_pickup_only}';
	$changelocationpulldown = '';
}
else if ($res['ship_method'] == 'digital')
{
	$show['digital_download_delivery'] = true;
	$shippinginfobit_1 = '{_digital_download_delivery}';
	$shiptocountries = '{_digital_download_delivery}';
	$changelocationpulldown = '';
}
if ($shippercount == 1)
{
	$ilance->shipping->print_shipping_methods($project_id, 1, false, false, false, $countryid, $_SESSION['ilancedata']['user']['slng']);
	$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . intval($project_id) . "'", "ship_service_$shipperidrow");
}
$currency = $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['symbol_left'];
if ($res['currencyid'] > 0)
{
	$currency = $ilance->currency->currencies[$res['currencyid']]['symbol_left'];
}
$disabled_button = ($res['featured'] AND $res['highlite'] AND $res['bold'] AND $res['autorelist']) ? 'disabled="disabled"' : '';
$format = $ilance->auction->print_auction_bit($project_id, $res['filtered_auctiontype'], $res['project_details'], $res['project_state'], $res['buynow'], $res['reserve'], $res['cid']);
$jsend .= '<script type="text/javascript">
<!--
var picturecount = ' . $imagecount . ';
var maxbigpicturewidth = ' . $ilconfig['attachmentlimit_productphotomaxwidth'] . ';
var maxbigpictureheight = ' . $ilconfig['attachmentlimit_productphotomaxheight'] . ';
jQuery(".tabs_content").hide();
jQuery("ul.tabs_nav li a:first").addClass("active").show(); //Activate first tab
jQuery("ul.thumbs_nav li:first").addClass("on").show(); //Activate first tab in modal
jQuery(".tabs_content:first").show(); //Show first tab content
jQuery("ul.tabs_nav li").click(function()
{
	jQuery("ul.tabs_nav li a").removeClass("active");
	jQuery(this).find("a").addClass("active");
	jQuery(".tabs_content").hide();
	var activeTab = jQuery(this).find("a").attr("id");
	fetch_js_object("currentpicture").value = activeTab; // picture1, picture2, etc
	jQuery("ul.thumbs_nav li").removeClass("on");
	jQuery("ul.thumbs_nav li#" + activeTab + "modal").addClass("on");
	jQuery("#big-picture").attr("src", pictures[activeTab]["src"]);
	jQuery("#big-picture").attr("width", pictures[activeTab]["width"]);
	jQuery("#big-picture").attr("height", pictures[activeTab]["height"]);
	jQuery(".tabs_content").fadeIn();
	return false;
});
jQuery("ul.thumbs_nav li").click(function()
{
	jQuery(".tabs_content").hide();
	var activeTab = jQuery(this).attr("id").slice(0, -5); // picture1
	var increment = activeTab.slice(-1);
	fetch_js_object("currentpicture").value = activeTab; // picture1, picture2, etc
	jQuery("ul.tabs_nav li a").removeClass("active");
	jQuery("ul.tabs_nav li a#" + activeTab).addClass("active");
	jQuery("ul.thumbs_nav li").removeClass("on");
	jQuery("ul.thumbs_nav li#" + activeTab + "modal").addClass("on");
	enlarge_listing_picture(\'' . $project_id . '\');
	jQuery("#big-picture").attr("src", pictures[activeTab]["src"]);
	jQuery("#big-picture").attr("width", pictures[activeTab]["width"]);
	jQuery("#big-picture").attr("height", pictures[activeTab]["height"]);
	jQuery(".tabs_content").fadeIn();
	return false;
});
' . $jspictures . '
' . $jspictureso . '
$(document).ready(change_modal_width_height(\'enlarge_picture_modal\'));
window.onresize = function() {
    change_modal_width_height(\'enlarge_picture_modal\');
}
//-->
</script>';
$pprint_array = array('buyer_zipcode','changelocationstatepulldown','changelocationcitypulldown','project_title_plain','format','items_in_lot','currency', 'slideshowpulldown1', 'disabled_button', 'proxybit2','shipperid','shipservicepulldown','shippinginfobit_1','shippinginfobit_2','shippinginfobit_3','shippinginfobit_4','shippinginfobit_5','full_country_pulldown','changelocationpulldown','shiptocountries','ship_handlingtime','featured_searchresults','featured','featured_date','highlite','bold','autorelist','enhancements','directpaybit','directpayurl','buynoworderid','donationtransaction','donationto','donationurl','donationpercentage','transactionstatus','winningbid','winningbidder','videodescription','pageurl','jsend','purchases','trackbacks','min_bidamountformatted','min_bidamount','date_starts','returnsaccepted','returnwithin','returngivenas','returnshippingpaidby','additional_info','returnpolicy','updateid','ship_partner','msgcount','product_image_thumb','increment','views','bidsleft','buynow_qty','paymentmethods','maincid','proxybit','escrowbit','icons','merchantstars','merchantscore','highbidderscore','currentbid','startprice','listingcategory','slideshowpulldown','productimage','project_attachment','project_questions','auctionridcode','countdownapplet','questionhiddeninput','questionsubmit','questions','questionpulldown','collapseimg_merch_shipping','collapseobj_merch_shipping','collapseobj_askquestion','collapseimg_askquestion','product_image','merchantstars','realtime','qty_pulldown','placeabid','cid','reserve_met','buynow','buynow_price','buynow_price_plain','amount','reserve','featured','highest','timeleft','started','ends','average','bids','highbidder','highbidderid','location','memberstart','countryname','collapserfpinfo_id','invite_list','rfpposted','rfpawards','additional_info','project_user_id','lowest_bidder','highest_bidder','filter_permissions','awarded_vendor','project_title','description','project_id','seller','sellerplain');

($apihook = $ilance->api('merch_detailed_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'listing_forward_auction_PRODUCT.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('messages','pictures','thumbnails','thumbnails_modal'));
$ilance->template->parse_loop('main', array('otherlistings'), false);

($apihook = $ilance->api('merch_detailed_loop')) ? eval($apihook) : false;

$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>