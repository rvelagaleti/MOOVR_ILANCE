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

/**
* Global AJAX product response functions for iLance
*
* @package      iLance\Global\AJAX\Product
* @version      4.0.0.8059
* @author       ILance
*/

/*
* Function to fetch a javascript response for AJAX output
*
* @param        integer        	listing id
* @param        string         	listing type (regular, fixed)
*
* @return       string		Returns HTML formatted output for AJAX response
*/
function fetch_product_response($id = 0, $type = 'regular')
{
	global $ilance, $ilconfig;
	$ilance->auction_expiry->listings();
	$sql = $ilance->db->query("
		SELECT p.cid, p.user_id, p.status, p.bids, p.startprice, p.currentprice, p.reserve, p.reserve_price, p.buynow, p.buynow_price, p.buynow_qty, p.date_end, p.close_date, p.currencyid, p.date_starts, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, s.ship_method, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS start
		FROM " . DB_PREFIX . "projects p
		LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
		WHERE p.project_id = '" . intval($id) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$timeleft = $ilance->auction->auction_timeleft(true, $res['date_starts'], $res['mytime'], $res['starttime']);
	$purchases = $ilance->auction_product->fetch_buynow_ordercount(intval($id));
	$endstext = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
	$realtime_open = ($res['date_starts'] > DATETIME24H) ? '0' : '1';
	if ($res['ship_method'] == 'localpickup' OR $res['ship_method'] == 'digital')
	{
		$showshippingrow = 1;
	}
	else
	{
		$showshippingrow = (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $id])) ? 1 : 0;
	}
	switch ($type)
	{
		case 'regular':
		{
			$winningbidder = $winningbid = $refreshbidders = $reservetext = $highest_amount = '';
			$showwinningbidderrow = $hidebuynowrow = $hideplacebidrow = $showblockheaderended = $hidebuynowactionrow = '0';
			if ($res['status'] != 'open')
			{
				$showblockheaderended = $hideplacebidrow = $hidebuynowactionrow = '1';
				$timeleft = '<span class="black">{_ended}</span>';
				if ($ilance->bid->has_winning_bidder(intval($id)))
				{
					$winningbidder = '';
					$winningbidderid = $ilance->bid->fetch_highest_bidder(intval($id));
					if ($winningbidderid > 0)
					{
						$showwinningbidderrow = '1';
						$winningbidder = fetch_user('username', $winningbidderid);
						$winningbid = $ilance->bid->fetch_awarded_bid_amount(intval($id));
						$winningbid = $ilance->currency->format($winningbid, $res['currencyid']);
					}
				}
			}
			$date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$startprice = $res['startprice'];
			$startprice_temp = $startprice;
			$currentbid = $res['currentprice'];
			$isreserve = $res['reserve'];
			$reserve_price = $res['reserve_price'];
			$buynow = $res['buynow'];
			$buynow_price = $res['buynow_price'];
			$buynow_qty = $res['buynow_qty'];
			if ($res['bids'] > 0 AND $currentbid > $startprice)
			{
				$startprice = '';
				$currentbid = $ilance->currency->format($currentbid, $res['currencyid']);
			}
			else if ($res['bids'] > 0 AND $currentbid == $startprice)
			{
				$startprice = '';
				$currentbid = $ilance->currency->format($currentbid, $res['currencyid']);
			}
			else
			{
				$startprice = $ilance->currency->format($startprice, $res['currencyid']);
				$currentbid = '';
			}
			// fetch highest bidder username
			$highbidderid = $ilance->bid->fetch_highest_bidder(intval($id));
			$highbidder = ($highbidderid > 0) ? ((isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'] == 1)) ? fetch_user('username', $highbidderid) : '**********') : '';
			if ($isreserve)
			{
				$sql = $ilance->db->query("
					SELECT MAX(bidamount) AS highest
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "'
					ORDER BY highest
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$resbids = $ilance->db->fetch_array($sql, DB_ASSOC);
					$reservetext = (($resbids['highest'] >= $reserve_price) ? '{_yes_reserve_price_met}' : '{_no_reserve_price_not_met}');
				}
				else
				{
					$reservetext = '{_no_reserve_price_not_met}';
				}
				$ilance->template->templateregistry['reservetext'] = $reservetext;
				$reservetext = $ilance->template->parse_template_phrases('reservetext');
			}
			if ($buynow == 0 OR $buynow_qty <= 0)
			{
				$hidebuynowrow = '1';
			}
			// #### bid increments in this category ################
			$increment = '';
			$cbid = !empty($res['currentprice']) ? $ilance->db->escape_string($res['currentprice']) : 0.00;
			$slng = fetch_user_slng($res['user_id']);
			$incrementgroup = $ilance->categories->incrementgroup($res['cid']);
			$sqlincrements = $ilance->db->query("
				SELECT amount
				FROM " . DB_PREFIX . "increments
				WHERE ((increment_from <= $cbid
					AND increment_to >= $cbid)
						OR (increment_from < $cbid
					AND increment_to < $cbid))
					AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
				ORDER BY amount DESC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlincrements) > 0)
			{
			    $resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);
			}
			$min_bidamount = sprintf("%.02f", '0.01');
			$min_bidamountformatted = $ilance->currency->format('0.01', $res['currencyid']);
			$highestbid = 0;
			if ($res['bids'] <= 0)
			{
				// do we have starting price?
				if ($startprice_temp > 0)
				{
					$min_bidamount = sprintf("%.02f", $startprice_temp);
					$min_bidamountformatted = $ilance->currency->format($startprice_temp, $res['currencyid']);
				}
			}
			else if ($res['bids'] > 0)
			{
				// highest bid amount placed for this auction
				$highbid = $ilance->db->query("
					SELECT MAX(bidamount) AS maxbidamount
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "'
						AND bidstate != 'retracted'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($highbid) > 0)
				{
					$reshighbid = $ilance->db->fetch_array($highbid, DB_ASSOC);
					$highestbid = sprintf("%.02f", $reshighbid['maxbidamount']);
				}
	
				// if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
				if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0)
				{
					$min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
					$min_bidamountformatted = $ilance->currency->format($highestbid + $resincrement['amount'], $res['currencyid']);
				}
				else
				{
					$min_bidamount = sprintf("%.02f", $highestbid);
					$min_bidamountformatted = $ilance->currency->format($highestbid, $res['currencyid']);
				}
			}
			// adjust proxy details for logged in bidder if they already have a max proxy bid placed
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($id, $_SESSION['ilancedata']['user']['userid']);
				if ($pbit > 0)
				{
					if ($pbit > $min_bidamount)
					{
						$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
						$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res['currencyid']);
					}
				}
			}
			if ($res['close_date'] != '0000-00-00 00:00:00')
			{
				if ($res['close_date'] < $res['date_end'])
				{
					$ends = print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$timeleft = '<span style="font-size:12px" class="black">{_ended_early}</span>';
				}
			}
			// #### realtime bidder refresh list ###########
			$refreshbidders = '<table width="100%" border="0" align="center" cellpadding="' . $ilconfig['table_cellpadding'] . '" cellspacing="' . $ilconfig['table_cellspacing'] . '"><tr class="alt2"><td width="33.3%" nowrap="nowrap"><div class="smaller">{_bidder}</div></td><td width="33.3%"><div class="smaller">{_bid_amount}</div></td><td width="33.3%"><div class="smaller">{_bid_placed}</div></td></tr>';
			$result = $ilance->db->query("
				SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.estimate_days, b.date_added AS bidadded, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, b.qty, p.project_id, p.escrow_id, p.cid, p.description, p.date_added, p.buynow_qty, p.date_end, p.user_id, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.currencyid, u.user_id, u.username, u.city, u.state, u.zip_code
				FROM " . DB_PREFIX . "project_bids AS b,
				" . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "users AS u
				WHERE b.project_id = '" . intval($id) . "'
				    AND b.project_id = p.project_id
				    AND u.user_id = b.user_id
				    AND b.bidstatus != 'declined'
				    AND b.bidstate != 'retracted'
				ORDER by b.bidamount DESC, b.date_added DESC
				LIMIT 5
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($result) > 0)
			{
				$row_count = 0;
				while ($resbids = $ilance->db->fetch_array($result, DB_ASSOC))
				{
					$resbids['bid_datetime'] = print_date($resbids['bidadded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$resbids['provider'] = fetch_user('username', $resbids['user_id']);
					if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'])
					{
						$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
					}
					else if ($resbids['bid_details'] == 'open' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
					{
						if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
						{
							$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
						}
						else
						{
							$resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $res['currencyid']);
						}
					}
					else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $resbids['user_id'] AND $_SESSION['ilancedata']['user']['userid'] != $resbids['project_user_id'])
					{
						$resbids['bidamount'] = '= {_sealed} =';
					}
					else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['user_id'])
					{
						if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
						{
							$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
						}
						else
						{
							$resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $res['currencyid']);
						}
					}
					else if ($resbids['bid_details'] == 'sealed' AND !empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $resbids['project_user_id'])
					{
						$resbids['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $resbids['bidamount'], $resbids['currencyid']);
					}
					else
					{
						$resbids['bidamount'] = $ilance->currency->format($resbids['bidamount'], $res['currencyid']);
					}
					if ($resbids['bidstatus'] == 'awarded' AND $resbids['status'] != 'open')
					{
						$resbids['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="" />';
						$awarded_vendor = handle_input_keywords($resbids['username']);
						$resbids['bidamount'] = '<span><strong>' . $resbids['bidamount'] . '</strong></span>';
					}
					else
					{
						$resbids['award'] = '';
					}
					if (!empty($resbids['proposal']))
					{
						// proxy bid
						$resbids['class'] = 'featured_highlight';
						$resbids['provider'] = $resbids['provider'];
						$resbids['bidamount'] = $resbids['bidamount'];
						$resbids['bid_datetime'] = $resbids['bid_datetime'];
					}
					else
					{
						// user bid
						$resbids['class'] = ($row_count % 2) ? 'alt1' : 'alt1';
					}
					$bid_provider = (isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'] == 1)) ? $resbids['provider'] : '**********';
					$row_count++;
					$refreshbidders .= '<tr class="' . $resbids['class'] . '" valign="top"><td width="33.3%"><span style="float:right">' . $resbids['award'] . '</span><div><span style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '" class="smaller black">' . $bid_provider . '</span></div></td><td width="33.3%"><div class="smaller black" style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '">' . $resbids['bidamount'] . '</div></td><td width="33.3%"><div class="smaller black" style="font-weight:' . ($row_count == 1 ? 'bold' : 'normal') . '">' . $resbids['bid_datetime'] . '</div></td></tr>';
					$bid_results_rows[] = $resbids;
				}
			}
			$refreshbidders .= '<tr><td valign="middle" width="33.3%"><div><span class="smaller gray">{_starting_bid}</span></div></td><td valign="middle" width="33.3%"><span class="smaller gray">' . $ilance->currency->format($startprice_temp, $res['currencyid']) . '</span></td><td valign="middle" width="33.3%"><span class="smaller gray">' . $date_starts . '</span></td></tr></table>';
			$is_owner = 0;
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
			{
				$is_owner = 1;
			}
			//   timeleft            |       bids         |US$ 5.00           |US$ 15.00          |Reserve Price Met   |US$ 18.50                      |18.50                 |purchases         |highest bidder     |1 or 0                |1 or 0                      |Thu, Apr 08, 2010 10:04 PM|   1 or 0      |<html>                 |1 or 0                       |0|1 or 0                       |US$10.00           |1 or 0		|1 or 0
			$response = $timeleft . '|' . $res['bids'] . '|' . $startprice . '|' . $currentbid . '|' . $reservetext . '|' . $min_bidamountformatted . '|' . $min_bidamount . '|' . $purchases . '|' . $highbidder . '|' . $hidebuynowrow . '|' . $hidebuynowactionrow . '|' . $endstext . '|' . $hideplacebidrow . '|' . $refreshbidders . '|' . $showwinningbidderrow . '|0|' . $showblockheaderended . '|' . $winningbid . '|' . $showshippingrow . '|' . $realtime_open . '|' . $res['date_starts'] . '|' . $res['date_end'] . '|' . DATETIME24H . '|' . $is_owner;
			$ilance->template->templateregistry['response'] = $response;
			$response = $ilance->template->parse_template_phrases('response');
			return $response;
		}
		case 'fixed':
		{
			$res['status'] = fetch_auction('status', intval($id));
			$hidebuynowrow = $showblockheaderended = 0;
			if ($res['status'] != 'open')
			{
				$timeleft = '<span class="black"><strong>{_ended}</strong></span>';
				$hidebuynowrow = $showblockheaderended = '1';
			}
			$is_owner = 0;
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
			{
				$is_owner = 1;
			}
			$response = $timeleft . '|' . $purchases . '|' . $hidebuynowrow . '|' . $showblockheaderended . '|' . $showshippingrow . '|' . $endstext . '|' . $realtime_open . '|' . $res['date_starts'] . '|' . $res['date_end'] . '|' . DATETIME24H . '|' . $is_owner;
			$ilance->template->templateregistry['response'] = $response;
			$response = $ilance->template->parse_template_phrases('response');
			return $response;
		}
	}
}

/*
* Function to fetch a javascript response for AJAX output
*
* @param        integer        	listing id
* @param        string         	listing type (regular, fixed)
*
* @return       string		Returns HTML formatted output for AJAX response
*/
function fetch_product_response_v4($id = 0, $type = 'regular', $invited = false)
{
	global $ilance, $ilpage, $ilconfig;
	$ilance->auction_expiry->listings();
	$sql = $ilance->db->query("
		SELECT p.cid, p.user_id, p.status, p.bids, p.startprice, p.currentprice, p.reserve, p.reserve_price, p.buynow, p.buynow_price, p.buynow_qty, p.date_end, p.close_date, p.currencyid, p.date_starts, p.project_title, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, s.ship_method, UNIX_TIMESTAMP('" . DATETIME24H . "') - UNIX_TIMESTAMP(p.date_starts) AS start
		FROM " . DB_PREFIX . "projects p
		LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
		WHERE p.project_id = '" . intval($id) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$timeleft = $ilance->auction->auction_timeleft(true, $res['date_starts'], $res['mytime'], $res['starttime'], true);
	$timeleftphrase = '{_time_left}:';
	if ($res['date_starts'] > DATETIME24H)
	{
		$timeleftphrase = '{_starts}:';
	}
	$purchases = $ilance->auction_product->fetch_buynow_ordercount(intval($id));
	$endstext = print_date($res['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0);
	$realtime_open = ($res['date_starts'] > DATETIME24H) ? '0' : '1';
	$is_owner = $countryid = 0;
	$retracts = $ilance->bid_retract->fetch_retracts_count(intval($id));
	if ($res['ship_method'] == 'localpickup' OR $res['ship_method'] == 'digital')
	{
		$showshippingrow = 1;
	}
	else
	{
		$showshippingrow = (!empty($_COOKIE[COOKIE_PREFIX . 'shipping_1_' . $id])) ? 1 : 0;
	}
	if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
	{
		$is_owner = 1;
	}
	if (!empty($_SESSION['ilancedata']['user']['countryid']))
	{
		$countryid = !empty($_SESSION['ilancedata']['user']['countryid']) ? $_SESSION['ilancedata']['user']['countryid'] : fetch_country_id($ilconfig['registrationdisplay_defaultcountry'], fetch_site_slng());
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
	switch ($type)
	{
		case 'regular':
		{
			$winningbidder = $winningbid = $reservetext = $highest_amount = $increment = $listingnotices = $transactionstatus = '';
			$showwinningbidderrow = $hidebuynowrow = $hideplacebidrow = $showblockheaderended = $show['soldbyauction'] = $isbidwinner = 0;
			$show['soldbypurchase'] = $ilance->auction_product->fetch_buynow_ordercount(intval($id));
			if ($res['status'] != 'open')
			{
				$showblockheaderended = $hideplacebidrow = $hidebuynowrow = 1;
				$timeleft = '{_ended}';
				if ($ilance->bid->has_winning_bidder(intval($id)))
				{
					$winningbidder = '';
					$winningbidderid = $ilance->bid->fetch_highest_bidder(intval($id));
					if ($winningbidderid > 0)
					{
						$show['soldbyauction'] = 1;
						$showwinningbidderrow = 1;
						$winningbidder = fetch_user('username', $winningbidderid);
						$winningbid = $ilance->bid->fetch_awarded_bid_amount(intval($id));
						$winningbid = $ilance->currency->format($winningbid, $res['currencyid']);
						// winning bidder message to bidder
						if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->bid->is_winner($_SESSION['ilancedata']['user']['userid'], $id))
						{
							$isbidwinner = 1;
							$listingnotices .= '<h2>{_congratulations_this_item_is_yours}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px">{_visit}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded#' . $id . '">{_buying_activity}</a></span> {_to_pay_for_this_item}</p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
					}
				}
				if ($isbidwinner == 0) // don't show ended notice header if winning bidder is viewing (overkill imo)
				{
					$listingnotices .= '<h2>{_the_bidding_has_ended_for_this_item}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '">{_no_more_bids_can_be_placed_at_this_time_find_similar_items_in_this_category}</a></span>&nbsp;&nbsp;<span class="gray">&#124;</span>&nbsp;&nbsp;<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . $res['cid'] . '">{_sell_a_similar_item_in_this_category}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			else
			{
				// high bidder message
				if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->bid->fetch_highest_bidder($id) == $_SESSION['ilancedata']['user']['userid'])
				{
					if ($res['bids'] == 1)
					{
						$listingnotices .= '<h2>{_congratulations} ' . $_SESSION['ilancedata']['user']['username'] . ', {_youre_the_first_bidder_good_luck}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2>';					}
					else
					{
						$listingnotices .= '<h2>{_congratulations_you_are_currently_the_highest_bidder_for_this_auction}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2>';
					}
					$listingnotices .= '<p style="margin-top:4px">{_however_another_bidder_might_place_a_higher_bid}</p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
				// outbid message
				if (!empty($_SESSION['ilancedata']['user']['userid']))
				{
					$isoutbid = $ilance->bid->is_outbid($_SESSION['ilancedata']['user']['userid'], $id);
					if (!empty($_SESSION['ilancedata']['user']['userid']) AND ($isoutbid != 3 AND $isoutbid != 0))
					{
						$listingnotices .= '<h2>{_you_were_outbid_by_another_bidder_place_a_new_maximum_bid}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_it_appears_another_bidder_has_placed_a_higher_bid_than_your_last_one}</p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
					}
					unset($isoutbid);
				}
				// can't ship to country message
				$ilance->shipping->can_item_ship_to_countryid($id, $countryid);
				if (isset($show['itemcanshiptouser']) AND $show['itemcanshiptouser'] == false AND !empty($_SESSION['ilancedata']['user']['userid']) AND $is_owner == 0)
				{
					$listingnotices .= '<h2>{_were_sorry_it_appears_this_item_does_not_ship_to_your_location_in} ' . $_SESSION['ilancedata']['user']['country'] . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '">{_no_more_bids_can_be_placed_at_this_time_find_similar_items_in_this_category}</a></span>&nbsp;&nbsp;<span class="gray">&#124;</span>&nbsp;&nbsp;<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '&amp;title=' . handle_input_keywords($res['project_title']) . '">{_see_similar_items_offered_by_other_sellers}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
				// invited to bid message
				if ($invited)
				{
					$listingnotices .= '<h2>{_congratulations_you_have_been_selected_to_place_a_bid_on_this_auction}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['registration'] . '?invited=1&amp;id=' . $id . '">{_please_register_here}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			if ($is_owner)
			{
				if ((isset($show['soldbypurchase']) AND $show['soldbypurchase'] > 0) OR (isset($show['soldbyauction']) AND $show['soldbyauction'] > 0))
				{
					$listingnotices .= '<h2>{_congratulations_youve_sold_this_item}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px">{_visit}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold">{_selling_activity}</a></span> - ' . $ilance->bid->fetch_transaction_status(intval($id)) . '</p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			$date_starts = print_date($res['date_starts'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			if ($res['date_starts'] > DATETIME24H)
			{
				if ($ilconfig['globalauctionsettings_seourls'] AND $res['cid'] > 0)
				{
					$show['nourlbit'] = true;
					$ilance->GPC['type'] = '';
					$ilance->GPC['s'] = '';
					$categoryname = $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $res['cid']);
					$categoryurl = construct_seo_url("productcatplain", $res['cid'], 0, $categoryname, '', 0, '', 0, 0, 'qid') . '?q=' . handle_input_keywords($res['project_title']);
					unset($categoryname);
				}
				else
				{
					$categoryurl = $ilpage['search'] . '?mode=product&amp;cid=' . $res['cid'] . '&amp;q=' . handle_input_keywords($res['project_title']);
				}
				$listingnotices .= '<h2>This auction event has not started yet and will open for bidding <span class="green">' . $date_starts . '</span><span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . $categoryurl . '">{_see_similar_items_offered_by_other_sellers}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
			$startprice = $res['startprice'];
			$startprice_temp = $startprice;
			$currentbid = $res['currentprice'];
			$isreserve = $res['reserve'];
			$reserve_price = $res['reserve_price'];
			$buynow = $res['buynow'];
			$buynow_price = $res['buynow_price'];
			$buynow_qty = $res['buynow_qty'];
			if ($res['bids'] > 0 AND $currentbid > $startprice)
			{
				$startprice = '';
				$currentbid = $ilance->currency->format($currentbid, $res['currencyid']);
			}
			else if ($res['bids'] > 0 AND $currentbid == $startprice)
			{
				$startprice = '';
				$currentbid = $ilance->currency->format($currentbid, $res['currencyid']);
			}
			else
			{
				$startprice = $ilance->currency->format($startprice, $res['currencyid']);
				$currentbid = '';
			}
			// fetch highest bidder username
			$highbidderid = $ilance->bid->fetch_highest_bidder(intval($id));
			$highbidder = ($highbidderid > 0) ? ((isset($ilconfig['productbid_displaybidname']) AND ($ilconfig['productbid_displaybidname'] == 1)) ? fetch_user('username', $highbidderid) : '**********') : '';
			if ($isreserve)
			{
				$sql = $ilance->db->query("
					SELECT MAX(bidamount) AS highest
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "'
					ORDER BY highest
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$resbids = $ilance->db->fetch_array($sql, DB_ASSOC);
					$reservetext = (($resbids['highest'] >= $reserve_price) ? '{_yes_reserve_price_met}' : '{_no_reserve_price_not_met}');
				}
				else
				{
					$reservetext = '{_no_reserve_price_not_met}';
				}
				$ilance->template->templateregistry['reservetext'] = $reservetext;
				$reservetext = $ilance->template->parse_template_phrases('reservetext');
			}
			if ($buynow == 0 OR $buynow_qty <= 0)
			{
				$hidebuynowrow = '1';
			}
			// #### bid increments in this category ################
			$cbid = !empty($res['currentprice']) ? $ilance->db->escape_string($res['currentprice']) : 0.00;
			$slng = fetch_user_slng($res['user_id']);
			$incrementgroup = $ilance->categories->incrementgroup($res['cid']);
			$sqlincrements = $ilance->db->query("
				SELECT amount
				FROM " . DB_PREFIX . "increments
				WHERE ((increment_from <= $cbid
					AND increment_to >= $cbid)
						OR (increment_from < $cbid
					AND increment_to < $cbid))
					AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
				ORDER BY amount DESC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlincrements) > 0)
			{
				$resincrement = $ilance->db->fetch_array($sqlincrements, DB_ASSOC);
			}
			$min_bidamount = sprintf("%.02f", '0.01');
			$min_bidamountformatted = $ilance->currency->format('0.01', $res['currencyid']);
			$highestbid = 0;
			if ($res['bids'] <= 0)
			{
				// do we have starting price?
				if ($startprice_temp > 0)
				{
					$min_bidamount = sprintf("%.02f", $startprice_temp);
					$min_bidamountformatted = $ilance->currency->format($startprice_temp, $res['currencyid']);
				}
			}
			else if ($res['bids'] > 0)
			{
				// highest bid amount placed for this auction
				$highbid = $ilance->db->query("
					SELECT MAX(bidamount) AS maxbidamount
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "'
						AND bidstate != 'retracted'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($highbid) > 0)
				{
					$reshighbid = $ilance->db->fetch_array($highbid, DB_ASSOC);
					$highestbid = sprintf("%.02f", $reshighbid['maxbidamount']);
				}
				// if we have more than 1 bid start the bid increments since the first bidder cannot bid against the opening bid
				if (isset($resincrement['amount']) AND !empty($resincrement['amount']) AND $resincrement['amount'] > 0)
				{
					$min_bidamount = sprintf("%.02f", $highestbid + $resincrement['amount']);
					$min_bidamountformatted = $ilance->currency->format($highestbid + $resincrement['amount'], $res['currencyid']);
				}
				else
				{
					$min_bidamount = sprintf("%.02f", $highestbid);
					$min_bidamountformatted = $ilance->currency->format($highestbid, $res['currencyid']);
				}
			}
			// adjust proxy details for logged in bidder if they already have a max proxy bid placed
			if (!empty($_SESSION['ilancedata']['user']['userid']))
			{
				$pbit = $ilance->bid_proxy->fetch_user_proxy_bid($id, $_SESSION['ilancedata']['user']['userid']);
				if ($pbit > 0)
				{
					if ($pbit > $min_bidamount)
					{
						$min_bidamount = sprintf("%.02f", $pbit) + 0.01;
						$min_bidamountformatted = $ilance->currency->format($min_bidamount, $res['currencyid']);
					}
				}
			}
			if ($res['close_date'] != '0000-00-00 00:00:00')
			{
				if ($res['close_date'] < $res['date_end'])
				{
					$ends = print_date($res['close_date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$timeleft = '{_ended_early}';
				}
			}
			//              0                   1                   2                    3                   4                        5                           6                     7                  8                    9                        10                  11                    12                       13                          14                    15                        16                      17                     18                      19                        20                        21                    22                23         
			//          timeleft     |         bids       |     US$ 5.00      |      US$ 15.00    |  Reserve Price Met |          US$ 18.50            |        18.50         |     purchases    |   highest bidder  |        1 or 0        |          <html>        |  Thu, Apr 08... |         1 or 0         |   time left phrase    |             1 or 0          |  bid retracts   |             1 or 0          |      US$10.00     |          1 or 0        |         1 or 0       |                           |                        |                   |
			$response = $timeleft . '|' . $res['bids'] . '|' . $startprice . '|' . $currentbid . '|' . $reservetext . '|' . $min_bidamountformatted . '|' . $min_bidamount . '|' . $purchases . '|' . $highbidder . '|' . $hidebuynowrow . '| ' . $listingnotices . '|' . $endstext . '|' . $hideplacebidrow . '|' . $timeleftphrase . '|' . $showwinningbidderrow . '|' . $retracts . '|' . $showblockheaderended . '|' . $winningbid . '|' . $showshippingrow . '|' . $realtime_open . '|' . $res['date_starts'] . '|' . $res['date_end'] . '|' . DATETIME24H . '|' . $is_owner;
			break;
		}
		case 'fixed':
		{
			$listingnotices = '';
			$res['status'] = fetch_auction('status', intval($id));
			$hidebuynowrow = $ended = $is_owner = 0;
			if ($res['status'] != 'open')
			{
				$timeleft = '{_ended}';
				$hidebuynowrow = $ended = '1';
				$listingnotices .= '<h2>{_the_bidding_has_ended_for_this_item}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '">{_no_more_bids_can_be_placed_at_this_time_find_similar_items_in_this_category}</a></span>&nbsp;&nbsp;<span class="gray">&#124;</span>&nbsp;&nbsp;<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . $res['cid'] . '">{_sell_a_similar_item_in_this_category}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
			else
			{
				$ilance->shipping->can_item_ship_to_countryid($id, $countryid);
				if (isset($show['itemcanshiptouser']) AND $show['itemcanshiptouser'] == false AND !empty($_SESSION['ilancedata']['user']['userid']) AND $is_owner == 0)
				{
					$listingnotices .= '<h2>{_were_sorry_it_appears_this_item_does_not_ship_to_your_location_in} ' . $_SESSION['ilancedata']['user']['country'] . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px">{_you_can}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '">{_no_more_bids_can_be_placed_at_this_time_find_similar_items_in_this_category}</a></span>&nbsp;&nbsp;<span class="gray">&#124;</span>&nbsp;&nbsp;<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cid=' . $res['cid'] . '&amp;title=' . handle_input_keywords($res['project_title']) . '">{_see_similar_items_offered_by_other_sellers}</a></span></p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0 AND $_SESSION['ilancedata']['user']['userid'] == $res['user_id'])
			{
				$is_owner = 1;
			}
			if ($is_owner)
			{
				$show['soldbypurchase'] = $ilance->auction_product->fetch_buynow_ordercount(intval($id));
				if (isset($show['soldbypurchase']) AND $show['soldbypurchase'] > 0)
				{
					$listingnotices .= '<h2>{_congratulations_youve_sold_this_item}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px">{_visit}: <span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold">{_selling_activity}</a></span> - ' . $ilance->bid->fetch_transaction_status(intval($id)) . '</p><div style="height:4px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			//              0                  1                     2                 3                  4                     5                     6                        7                          8                     9                10                   11                      12
			$response = $timeleft . '|' . $purchases . '|' . $hidebuynowrow . '|' . $ended . '|' . $showshippingrow . '|' . $endstext . '|' . $realtime_open . '|' . $res['date_starts'] . '|' . $res['date_end'] . '|' . DATETIME24H . '|' . $is_owner . '|' . $listingnotices . '|' . $timeleftphrase;
			break;
		}
	}
	$ilance->template->templateregistry['response'] = $response;
	$response = $ilance->template->parse_template_phrases('response');
	return $response;
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>