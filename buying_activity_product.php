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
$keyw2 = isset($ilance->GPC['keyw2']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw2'])) : '';
$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
$extra2 = '';
$extra2 .= (!empty($ilance->GPC['bidsub'])) ? '&amp;bidsub=' . $ilance->GPC['bidsub'] : '';
// #### LISTING PERIOD #########################################
$ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
$periodsql2 = fetch_startend_sql(intval($ilance->GPC['period2']), 'DATE_SUB', 'p.date_added', '>=');
$extra2 .= '&amp;period2=' . intval($ilance->GPC['period2']);
// #### RESULTS ORDERING BY COLUMN NAME DEFAULTS ###############
$orderby2 = '&amp;orderby2=date_end';
$orderbysql2 = 'date_added';
$orderbyfields2 = array('project_title', 'date_added', 'date_end', 'bids');
if (isset($ilance->GPC['orderby2']) AND in_array($ilance->GPC['orderby2'], $orderbyfields2))
{
	$orderby2 = '&amp;orderby2=' . $ilance->GPC['orderby2'];
	$orderbysql2 = $ilance->GPC['orderby2'];
}
$displayorderfields2 = array('asc', 'desc');
$displayorder2 = '&amp;displayorder2=asc';
$currentdisplayorder2 = $displayorder2;
$displayordersql2 = 'DESC';
if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'asc')
{
	$displayorder2 = '&amp;displayorder2=desc';
	$currentdisplayorder2 = '&amp;displayorder2=asc';
}
else if (isset($ilance->GPC['displayorder2']) AND $ilance->GPC['displayorder2'] == 'desc')
{
	$displayorder2 = '&amp;displayorder2=asc';
	$currentdisplayorder2 = '&amp;displayorder2=desc';
}
if (isset($ilance->GPC['displayorder2']) AND in_array($ilance->GPC['displayorder2'], $displayorderfields2))
{
	$displayordersql2 = mb_strtoupper($ilance->GPC['displayorder2']);
}
$groupby = "GROUP BY b.project_id";
$orderby = "ORDER BY $orderbysql2 $displayordersql2";
$ilconfig['globalfilters_maxrowsdisplay'] = (isset($ilance->GPC['pp2'])  AND $ilance->GPC['pp2'] >= 0)  ? intval($ilance->GPC['pp2']) : $ilconfig['globalfilters_maxrowsdisplay'] ;
$limit = "LIMIT " . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . "," . $ilconfig['globalfilters_maxrowsdisplay'];                
$block_header_title = '{_items_im_bidding_on}';
$ilance->GPC['orderby2'] = (isset($ilance->GPC['orderby2']) ? $ilance->GPC['orderby2'] : 'date_end');
$ilance->GPC['displayorder2'] = (isset($ilance->GPC['displayorder2']) ? $ilance->GPC['displayorder2'] : 'desc');
$ilance->GPC['pp2'] = (isset($ilance->GPC['pp2']) ? intval($ilance->GPC['pp2']) : $ilconfig['globalfilters_maxrowsdisplay']);
$ilance->GPC['pics2'] = (isset($ilance->GPC['pics2']) ? intval($ilance->GPC['pics2']) : '1');
$period2_options = array(
	'-1' => '{_placed_any_date}',
	'1' => '{_placed_last_hour}',
	'6' => '{_placed_last_12_hours}',
	'7' => '{_placed_last_24_hours}',
	'13' => '{_placed_last_7_days}',
	'14' => '{_placed_last_14_days}',
	'15' => '{_placed_last_30_days}',
	'16' => '{_placed_last_60_days}',
	'17' => '{_placed_last_90_days}');
$period2_pulldown = construct_pulldown('period2_pull_id', 'period2', $period2_options, $ilance->GPC['period2'], 'class="select"');
$orderby2_options = array(
	'project_title' => '{_item_title}',
	'date_added' => '{_start_date}',
	'date_end' => '{_end_date}',
	'bids' => '{_bids}',
	//'insertionfee' => '{_insertion_fee}',
	//'buynow_purchases' => '{_purchases}',
	//'buynow_qty' => '{_quantity}',
	//'bids,buynow_purchases' => '{_bids}, {_purchases}',
	//'buynow_purchases,bids' => '{_purchases}, {_bids}'
	);
$orderby2_pulldown = construct_pulldown('orderby2_pull_id', 'orderby2', $orderby2_options, $ilance->GPC['orderby2'], 'class="select"');
$pics2_pulldown = construct_pulldown('pics2_pull_id', 'pics2', array('1' => '{_include_pictures}', '0' => '{_exclude_pictures}'), $ilance->GPC['pics2'], 'class="smaller" style="font-family: verdana; width:105px;"');
$displayorder2_pulldown = construct_pulldown('displayorder2_pull_id', 'displayorder2', array('desc' => '{_descending}', 'asc' => '{_ascending}'), $ilance->GPC['displayorder2'], 'class="select" style="width:90px;"');
$pp2_pulldown = construct_pulldown('pp2_pull_id', 'pp2', array('10' => '10', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000'), $ilance->GPC['pp2'], 'class="select-75" style="width:60px;"');
$bids_retracted = $bids_awarded = $bids_invited = $bids_expired = $bids_active = $row_count2 = 0;
$query = array();
$php_self2 = $ilpage['buying'] . '?cmd=management' . $displayorder2 . $extra2;
$scriptpage2 = $ilpage['buying'] . '?cmd=management' . $currentdisplayorder2 . $orderby2 . $extra2;
$counter = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$condition = $condition2 = '';
$show['datetime'] = false;
$show['canretractbid'] = false;
if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'bidretracts') > 0)
{
	$show['canretractbid'] = true;
}

($apihook = $ilance->api('buying_activity_bidsub_condition_start')) ? eval($apihook) : false;

if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'retracted')
{
	$block_header_title = '{_retracted_items}';
	$page_title = SITE_NAME . ' - ' . $block_header_title;
	$area_title = $block_header_title;
	$bids_retracted = 1;
	$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('retracted', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$producttabs = print_buying_activity_tabs('retracted', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
}
else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'buying_won'
	);
	$block_header_title = '{_items_ive_won}';
	$page_title = SITE_NAME . ' - ' . $block_header_title;
	$area_title = $block_header_title;
	$bids_awarded = 1;
	$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('awarded', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$producttabs = print_buying_activity_tabs('awarded', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
}
else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'invited')
{
	$block_header_title = '{_invited_items}';
	$page_title = SITE_NAME . ' - ' . $block_header_title;
	$area_title = $block_header_title;
	$bids_invited = 1;
	$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('invited', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$producttabs = print_buying_activity_tabs('invited', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
}
else if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'expired')
{
	$block_header_title = '{_items_i_didnt_win}';
	$page_title = SITE_NAME . ' - ' . $block_header_title;
	$area_title = $block_header_title;
	$bids_expired = 1;
	$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('expired', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$producttabs = print_buying_activity_tabs('expired', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
}
else
{
	$bids_active = 1;
	$page_title = SITE_NAME . ' - {_buying_activity}';
	$area_title = $block_header_title;
	$query['1'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, $limit, $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$query['2'] = $ilance->bid_tabs->fetch_product_bidtab_sql('active', 'string', $groupby, $orderby, '', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
	$producttabs = print_buying_activity_tabs('active', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql2);
}
$numberrows = $ilance->db->query($query['2'], 0, null, __FILE__, __LINE__);
$number = $ilance->db->num_rows($numberrows);
$result2 = $ilance->db->query($query['1'], 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($result2) > 0)
{
	while ($row2 = $ilance->db->fetch_array($result2, DB_ASSOC))
	{
		$row2['paystatus'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span>';
		$row2['shipstatus'] = '<span title="{_item_not_shipped}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
		$row2['feedback'] = '<span title="{_feedback_not_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span>';
		$row2['feedbackreceived'] = '<span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span>';
		$row2['escrowtotal'] = '<div><span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span></div>';
		$row2['merchant'] = print_username($row2['user_id'], 'href', 0, '', '');
		$row2['merchantplain'] = print_username($row2['user_id'], 'plain', 0, '', '');
		$row2['icons'] = $ilance->auction->auction_icons($row2);
		$row2['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], (isset($highest) ? $highest : 0), $row2['currencyid']);
		$row2['item_title'] = print_string_wrap(handle_input_keywords(stripslashes($row2['project_title'])), '45');
		$row2['actions'] = (isset($bids_retracted) AND $bids_retracted OR isset($bids_expired) AND $bids_expired OR isset($bids_invited) AND $bids_invited) ? '<input type="checkbox" id="bidid_' . $row2['bid_id'] . '" name="bidid[]" value="' . $row2['bid_id'] . '" disabled="disabled" />' : '<input type="checkbox" id="bidid_' . $row2['bid_id'] . '" name="bidid[]" value="' . $row2['bid_id'] . '" />';
		$row2['wondate'] = '';
		$row2['awarded'] = '';
		$row2['work'] = '';
		$row2['pmb'] = '';
		$row2['payment'] = '-';
		$row2['bid_id'] = ((isset($bids_invited) AND $bids_invited) ? '-' : $row2['bid_id']);
		$row2['timeleft'] = $ilance->auction->auction_timeleft(true, $row2['date_starts'], $row2['mytime'], $row2['starttime']);
		$row2['ends'] = print_date($row2['date_end'], 'l M d Y g:i:s A', 0, 0); // l M d Y h:i:s A | Monday sep 25 2013 at 5:
		$row2['bidretractdate'] = print_date($row2['date_retracted'], 'l M d Y g:i:s A', 0, 0);
		$row2['bidplacedate'] = print_date($row2['date_added'], 'M j Y g:i:s A', 0, 0);
		$row2['photo'] = $ilance->auction->print_item_photo('', 'thumbgallery', $row2['project_id'], 1);
		//$row2['photo'] = $ilance->auction->print_item_photo('', 'thumb', $row2['project_id'], 1);
		$row2['currentbid'] = $ilance->currency->format($row2['currentprice'], $row2['currencyid']);
		$row2['highestbidderid'] = $ilance->bid->fetch_highest_bidder($row2['project_id']);
		$row2['highestbid'] = $ilance->currency->format($row2['currentprice'], $row2['currencyid']);
		$row2['winningbid'] = $ilance->currency->format($row2['currentprice'], $row2['currencyid']);
		$row2['contactseller'] = '';
		$GLOBALS['show_highestbidder_' . $row2['project_id']] = (($row2['highestbidderid'] == $_SESSION['ilancedata']['user']['userid']) ? 1 : 0);
		$GLOBALS['show_bidretracted_' . $row2['project_id']] = (($row2['bidstate'] == 'retracted') ? 1 : 0);
		$GLOBALS['show_blockedbidding_' . $row2['project_id']] = 0;
		$GLOBALS['show_bannedbidding_' . $row2['project_id']] = 0;
		$GLOBALS['show_ended_' . $row2['project_id']] = (($row2['mytime'] < 0) ? 1 : 0);
		$GLOBALS['show_endedtowinner_' . $row2['project_id']] = $ilance->bid->has_winning_bidder($row2['project_id']);
		$GLOBALS['show_sellerinfavorites_' . $row2['project_id']] = $ilance->watchlist->is_seller_added_to_watchlist($row2['user_id']);
		$GLOBALS['show_iteminwatchlist_' . $row2['project_id']] = $ilance->watchlist->is_listing_added_to_watchlist($row2['project_id']);
		$GLOBALS['show_endedearlytopurchase_' . $row2['project_id']] = (($row2['close_date'] == '0000-00-00 00:00:00') ? 0 : 1);
		$GLOBALS['show_canproxycategory_' . $row2['project_id']] = (($ilconfig['productbid_enableproxybid'] == 0 OR $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $row2['cid']) == 0) ? 0 : 1);
		$GLOBALS['show_reservepricenotmet_' . $row2['project_id']] = 0;
		$GLOBALS['show_reserve_' . $row2['project_id']] = $row2['reserve'];
		$row2['close_date'] = print_date($row2['close_date'], 'l M d Y g:i:s A', 0, 0); // l M d Y h:i:s A | Monday sep 25 2013 at 5:30pm
		if ($row2['bidderid'] > 0)
		{
			if ($row2['reserve'])
			{
				if ($row2['reserve_price'] <= $row2['currentprice'])
				{
					// reserve price met
					$GLOBALS['show_reservepricenotmet_' . $row2['project_id']] = 0;
					if ($row2['mytime'] < 0)
					{
						if ($bids_retracted OR $bids_expired)
						{
							$row2['timeleft'] = '{_ended}';
						}
						else
						{
							$row2['awarded'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="{_winner}" />';
							$row2['timeleft'] = '{_ended}';
						}
					}
					$row2['pmb'] = ($bids_retracted OR $bids_expired OR $bids_invited)
						? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pmb_gray.gif" border="0" alt="" /></div>'
						: $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
					// feedback left?
					if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller', $row2['bid_id']))
					{
						$row2['feedback'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
					}
					else
					{
						$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
							? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="" /></div>'
							: '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
					}
					// feedback received?
					if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], 'buyer', $row2['bid_id']))
					{
						$row2['feedbackreceived'] = '<div><span title="{_feedback_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received.gif" border="0" alt="" /></span></div>';
					}
					else
					{
						$row2['feedbackreceived'] = '<div><span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span></div>';
					}
					// mediashare
					$row2['work'] = ($bids_retracted OR $bids_expired OR $bids_invited) ? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" /></div>' : $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], $active = true);
				}
				else
				{
					// reserve price not met
					$GLOBALS['show_reservepricenotmet_' . $row2['project_id']] = 1;
					if ($row2['mytime'] < 0)
					{
						$row2['timeleft'] = '{_ended}';
						$row2['shipstatus'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
						$row2['feedback'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span>';
						$row2['feedbackreceived'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span>';
						$row2['work'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_litegray.gif" border="0" alt="" /></span>';
						$row2['escrowtotal'] = '<div><span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span></div>';
						$row2['pmb'] = '';
					}
				}
			}
			else
			{
				if ($row2['mytime'] < 0)
				{
					if ($bids_retracted OR $bids_expired)
					{
						$row2['timeleft'] = '{_ended}';
					}
					else
					{
						$row2['awarded'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_small.gif" border="0" alt="{_winner}" />';
						$row2['timeleft'] = '{_ended}';
					}
				}
				$row2['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
				// #### feedback experience with buyer
				if ($ilance->feedback->has_left_feedback($row2['user_id'], $_SESSION['ilancedata']['user']['userid'], $row2['project_id'], 'seller', $row2['bid_id']))
				{
					$row2['feedback'] = '<div align="center"><span title="{_feedback_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_submitted__thank_you}" /></span></div>';
				}
				else
				{
					$row2['feedback'] = ($bids_retracted OR $bids_expired OR $bids_invited)
						? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></div>'
						: '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '"><a href="' . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $row2['user_id']) . '" /></a></span></div>';
				}
				// feedback received?
				if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], 'buyer', $row2['bid_id']))
				{
					$row2['feedbackreceived'] = '<div><span title="{_feedback_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received.gif" border="0" alt="" /></span></div>';
				}
				else
				{
					$row2['feedbackreceived'] = '<div><span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span></div>';
				}
				// ### can we show mediashare icon?
				$row2['work'] = ($bids_retracted OR $bids_expired OR $bids_invited)
					? '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/share_gray.gif" border="0" alt="" /></div>'
					: $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id'], $active = true);
			}
		}
		$show['datetime'] = false;
		$row2['datetime'] = print_date($row2['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$sql_highest = $ilance->db->query("
			SELECT bidamount AS highest, user_id, date_added, date_awarded
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . $row2['project_id'] . "'
			ORDER BY highest
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_highest) > 0)
		{
			$res_highest = $ilance->db->fetch_array($sql_highest, DB_ASSOC);
			$row2['highest'] = $ilance->currency->format($res_highest['highest'], $row2['currencyid']);
			if ($res_highest['user_id'] == $_SESSION['ilancedata']['user']['userid'] AND $row2['status'] == 'expired')
			{
				$show['datetime'] = true;
				if ($row2['buynow'] == '1')
				{
					$row2['datetime'] = print_date($res_highest['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
			}
		}
		else
		{
			$row2['highest'] = '-';
		}
		// #### viewing items i've won tab #####
		if (isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'awarded')
		{
			$row2['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $row2['user_id'], $row2['project_id']);
			$row2['winningbid'] = $ilance->currency->format($row2['currentprice'], $row2['currencyid']);
			$show['datetime'] = true;
			// #### populate shipping field actions
			if ($row2['ship_method'] == 'flatrate' OR $row2['ship_method'] == 'calculated')
			{
				$shippercount = $ilance->shipping->print_shipping_methods($row2['project_id'], $row2['qty'], false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
				if ($row2['sellermarkedasshipped'] AND $row2['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
				{
					$row2['orderlocation'] = '';
					$row2['shipping'] = '+' . $ilance->currency->format($row2['buyershipcost'], $row2['currencyid']);
					$row2['shipstatus'] = '<span title="{_marked_as_shipped_on} ' . print_date($row2['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span>';
				}
				else
				{
					$row2['shipstatus'] = '<span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span>';
					// did buyer select a ship service yet?
					// he would have chosen a method when placing a bid.. unless only 1 service was available
					if ($row2['buyershipperid'] > 0)
					{
						$row2['orderlocation'] = '';
						$shipperid = $row2['buyershipperid'];
						$shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty'], $row2['buyershipcost']);
						$row2['buyershipcost'] = $shippingcosts['total'];
						$row2['shipping'] = '+' . $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
						unset($shipperid, $shippingcosts);
					}
					else
					{
						if ($shippercount == 1)
						{
							$row2['orderlocation'] = '';
							$ilance->shipping->print_shipping_methods($row2['project_id'], $row2['qty'], false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
							$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $row2['project_id'] . "'", "ship_service_$shipperidrow");
							$shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty'], $row2['buyershipcost']);
							$row2['buyershipcost'] = $shippingcosts['total'];
							$row2['shipping'] = '+' . $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
							unset($shipperid, $shippingcosts);
						}
						else
						{
							if ($shippercount > 1)
							{
								$row2['orderlocation'] = '<div><strong>{_ship_service}:</strong> <span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">{_choose_shipping_service}</a></span></div>';
								$row2['shipping'] = '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=" style="text-decoration:underline">{_choose}</a></span>';
								$row2['buyershipcost'] = 0;
							}
						}
					}
				}
			}
			// #### local pickup only
			else
			{
				$row2['buyershipcost'] = 0;
				$row2['shipping'] = '{_local_pickup_only}';
				$row2['shippingpartner'] = '{_none}';
				$row2['delivery'] = '';
				$row2['orderlocation'] = '';
				$row2['shipservice'] = '{_local_pickup}';
				$sql_digital = $ilance->db->query("
					SELECT attachid
					FROM " . DB_PREFIX . "attachment
					WHERE project_id = '" . $row2['project_id'] . "'
						AND attachtype = 'digital'
				");
				if ($ilance->db->num_rows($sql_digital) > 0)
				{
					// digital download
					$digitalfile = '{_contact_seller}';
					$dquery = $ilance->db->query("
						SELECT filename, counter, filesize, attachid
						FROM " . DB_PREFIX . "attachment
						WHERE project_id = '" . intval($row2['project_id']) . "'
							AND attachtype = 'digital'
							AND user_id = '" . $row2['user_id'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($dquery) > 0)
					{
						$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
						$markedasshipped = 0;
						if (fetch_auction('filtered_auctiontype', $row2['project_id']) == 'fixed')
						{
							$sql_bo = $ilance->db->query("
								SELECT status
								FROM " . DB_PREFIX . "buynow_orders
								WHERE project_id = '" . $row2['project_id'] . "'
									AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							");
							if ($ilance->db->num_rows($sql_bo))
							{
								$res_bo = $ilance->db->fetch_array($sql_bo);
								if ($res_bo['status'] == 'pending_delivery' OR $res_bo['status'] == 'delivered' OR $res_bo['status'] == 'offline_delivered')
								{
									$markedasshipped = 1;
								}
							}
						}
						else 
						{
							if (intval($row2['filter_escrow']) == 1)
							{
								$sql_escrow = $ilance->db->query("
									SELECT sellermarkedasshipped
									FROM " . DB_PREFIX . "projects_escrow
									WHERE project_id = '" . $row2['project_id'] . "'
								");
								if ($ilance->db->num_rows($sql_escrow))
								{
									$res_escrow = $ilance->db->fetch_array($sql_escrow);
									$markedasshipped = $res_escrow['sellermarkedasshipped'];
								}
							}
							else 
							{
								$markedasshipped = $row2['sellermarkedasshipped'];
							}
						}
						if ($markedasshipped)
						{
							$crypted = array('id' => $dfile['attachid']);
							$digitalfile = '<strong><a href="' . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">' . stripslashes($dfile['filename']) . '</a></strong> (' . print_filesize($dfile['filesize']) . ')';
						}
						else
						{
							$digitalfile = '<strong>' . stripslashes($dfile['filename']) . '</strong> (' . print_filesize($dfile['filesize']) . ')<div class="smaller gray">{_waiting_for_seller_to_confirm_delivery}</div>';
						}
						$row2['orderlocation'] = '<strong>{_digital_delivery}:</strong> ' . $digitalfile;
					}
				}
			}
			$methodscount = $ilance->payment->print_payment_methods($row2['project_id'], false, true);
			// #### single payment method offered by seller
			if ($methodscount == 1)
			{
				$row2['buyerpaymethod'] = $ilance->payment->print_payment_method_title($row2['project_id']);
				// #### gateway ########
				if (strchr($row2['buyerpaymethod'], 'gateway'))
				{
					if ($row2['winnermarkedaspaid'] AND $row2['winnermarkedaspaiddate'] != '0000-00-00 00:00:00')
					{
						$row2['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($row2['winnermarkedaspaiddate']) . ' {_using} ' . $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
					}
					else
					{
						$row2['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
					}
					$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
						? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>{_pay_now}</strong></a></span>'
						: '-';	
				}
				// #### offline ########
				else if (strchr($row2['buyerpaymethod'], 'offline'))
				{
					if ($row2['winnermarkedaspaid'] == '0')
					{
						$crypted = array(
							'cmd' => 'management',
							'subcmd' => 'markaspaid',
							'pid' => $row2['project_id'],
							'bid' => $row2['bid_id']
						);
						$row2['payment'] = '<span class="blue"><a href="javascript:;" onclick="return show_prompt_payment_buyer(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')" style="text-decoration:underline"><strong>{_mark_payment_as_sent}</strong></a></span>';
						$row2['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
						unset($crypted);
					}
					else
					{
						$crypted = array(
							'cmd' => 'management',
							'subcmd' => 'markasunpaid',
							'pid' => $row2['project_id'],
							'bid' => $row2['bid_id']
						);
						// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
						// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
						// to the seller (who already would have received the funds).. this is just a quick hack to prevent
						// abuse to payment details when the admin is finally reviewing details from the admincp
						$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
						$date2split = explode('-', $date1split[0]);
						$totaldays = 14;
						$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
						$daysleft = ($totaldays - $elapsed);
						if ($elapsed <= 14)
						{
							$row2['payment'] = '<div class="smaller blue" style="padding-top:7px"><span class="gray">{_optional}:</span> <span title="(' . $daysleft . ' {_days_left})"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(\'{_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid}\')" style="text-decoration:underline">{_mark_as_unpaid}</a></span></div>';
						}
						$row2['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($row2['winnermarkedaspaiddate']) . ' {_using} ' . handle_input_keywords($row2['winnermarkedaspaidmethod']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
						unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
					}	
				}
				// #### escrow #########
				else if ($row2['buyerpaymethod'] == 'escrow')
				{
					$row2['payment'] = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow" style="text-decoration:underline">' . $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>';
				}
			}
			// #### multiple payment methods offered by seller
			else
			{
				if (!empty($row2['buyerpaymethod']))
				{
					$row2['paymethod'] = $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false);
					// #### gateway ########
					if (strchr($row2['buyerpaymethod'], 'gateway'))
					{
						if ($row2['winnermarkedaspaid'] AND $row2['winnermarkedaspaiddate'] != '0000-00-00 00:00:00')
						{
							$row2['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($row2['winnermarkedaspaiddate']) . ' {_using} ' . $row2['paymethod'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
						}
						else
						{
							$row2['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
						}
						$row2['payment'] = ($row2['winnermarkedaspaid'] == '0')
							? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $row2['project_id'] . '" style="text-decoration:underline"><strong>{_pay_now}</strong></a></span>'
							: '<div class="smaller black">{_marked_as_paid_on} <span class="blue">' . print_date($row2['winnermarkedaspaiddate']) . '</span> {_using} <span class="blue">' . $row2['paymethod'] . '</span></div>';	
					}
					// #### offline ########
					else if (strchr($row2['buyerpaymethod'], 'offline'))
					{
						if ($row2['winnermarkedaspaid'] == '0')
						{
							$crypted = array(
								'cmd' => 'management',
								'subcmd' => 'markaspaid',
								'pid' => $row2['project_id'],
								'bid' => $row2['bid_id']
							);
							$row2['payment'] = '<span class="gray"><span class="blue"><a href="javascript:void(0)" onclick="return show_prompt_payment_buyer(\'' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')" style="text-decoration:underline"><strong>{_mark_payment_as_sent}</strong></a></span>';
							$row2['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
							unset($crypted);
						}
						else
						{
							$crypted = array(
								'cmd' => 'management',
								'subcmd' => 'markasunpaid',
								'pid' => $row2['project_id'],
								'bid' => $row2['bid_id']
							);
							// before we let the "buyer" mark as "unpaid" we should check when he last "marked as paid" and if it's
							// like more than 7 days we do not allow the buyer to play god with payment status avoiding future confusion
							// to the seller (who already would have received the funds).. this is just a quick hack to prevent
							// abuse to payment details when the admin is finally reviewing details from the admincp
							$date1split = explode(' ', $row2['winnermarkedaspaiddate']);
							$date2split = explode('-', $date1split[0]);
							$totaldays = 14;
							$elapsed = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
							$daysleft = ($totaldays - $elapsed);
							if ($elapsed <= 14)
							{
								$row2['payment'] = '<div class="smaller blue"><span title="(' . $daysleft . ' {_days_left})"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}" onclick="return confirm_js(\'{_you_are_about_to_change_the_status_for_the_payment_on_this_item_to_unpaid}\')" style="text-decoration:underline">{_mark_as_unpaid}</a></div>';
							}
							$row2['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($row2['winnermarkedaspaiddate']) . ' {_using} ' . $row2['winnermarkedaspaidmethod'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
							unset($crypted, $date1split, $date2split, $totaldays, $elapsed, $daysleft);
						}	
					}
					// #### escrow #########
					else if ($row2['buyerpaymethod'] == 'escrow')
					{
						$row2['payment'] = '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow" style="text-decoration:underline">' . $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>';
					}
				}
				else
				{
					$row2['payment'] = '<span class="smaller blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=" style="text-decoration:underline">{_choose_payment_method}</a></span>';
				}
			}
		}
		// #### all other tabs #################
		else
		{
			$row2['orderlocation'] = $row2['payment'] = $row2['shipping'] = $row2['shipservice'] = '-';
			$shipperid = $row2['buyershipperid'];
			$shippingcosts = $ilance->shipping->fetch_ship_cost_by_shipperid($row2['project_id'], $shipperid, $row2['qty'], $row2['buyershipcost']);
			$row2['buyershipcost'] = $shippingcosts['total'];
			$shippercount = $ilance->shipping->print_shipping_methods($row2['project_id'], $row2['qty'], false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
			if ($shippercount == 1)
			{
				$row2['shipservice'] = '<span class="smaller" title="' . $ilance->shipping->print_shipping_partner($shipperid) . '">' . shorten($ilance->shipping->print_shipping_partner($shipperid), 28) . '</span>';
			}
			else
			{
				if ($shippercount > 1)
				{
					$row2['shipservice'] = '<span class="smaller" title="' . $ilance->shipping->print_shipping_partner($shipperid) . '"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id='. $row2['project_id'] . '&amp;shipperid=' . $shipperid . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration:underline">' . shorten($ilance->shipping->print_shipping_partner($shipperid), 28) . '</a></span>';	
				}
			}
			$row2['shipping'] = '+' . $ilance->currency->format($shippingcosts['total'], $row2['currencyid']);
		}
		
		$row2['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
		$row2['total'] = $ilance->currency->format(($row2['bidamount'] + $row2['buyershipcost']), $row2['currencyid']);
		$methodscount = $ilance->payment->print_payment_methods($row2['project_id'], false, true);
		if (empty($row2['buyerpaymethod']))
		{
			$row2['paymethod'] = ($methodscount == 1)
				? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">' . $ilance->payment->print_fixed_payment_method($ilance->payment->print_payment_method_title($row2['project_id']), false) . '</a></span>'
				: '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;shipperid=' . $row2['buyershipperid'] . '" style="text-decoration: underline">{_choose}...</a></span>';
		}
		else
		{
			if ($row2['buyerpaymethod'] == 'escrow')
			{
				$row2['paymethod'] = '{_escrow}';	
			}
			else
			{
				if ($methodscount == 1)
				{
					$row2['paymethod'] = $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false);
				}
				else
				{
					$row2['paymethod'] = ($row2['winnermarkedaspaid'] == '0')
						? '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . $row2['project_id'] . '&amp;paymethod=' . $row2['buyerpaymethod'] . '&amp;shipperid=' . $row2['buyershipperid'] . '&amp;returnurl={pageurl_urlencoded}" style="text-decoration: underline">' . $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false) . '</a></span>'
						: $ilance->payment->print_fixed_payment_method($row2['buyerpaymethod'], false);
				}
			}
		}
		$row2['startprice'] = fetch_auction('startprice',$row2['project_id']);
		$row2['started'] = $ilance->currency->format($row2['startprice'], $row2['currencyid']);
		$max_your_bid_query = $ilance->db->query("SELECT maxamount, user_id FROM " . DB_PREFIX . "proxybid WHERE project_id = '" . $row2['project_id'] . "' AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' LIMIT 1");
		$fetch_your_max_bid = $ilance->db->fetch_array($max_your_bid_query, DB_ASSOC);
		$max_bid_query = $ilance->db->query("SELECT MAX(bidamount) AS max_bid, user_id FROM " . DB_PREFIX . "project_bids WHERE project_id = '" . $row2['project_id'] . "' AND user_id != '" . $_SESSION['ilancedata']['user']['userid'] . "' LIMIT 1");
		$fetch_max_bid = $ilance->db->fetch_array($max_bid_query, DB_ASSOC);
		$GLOBALS['show_maxproxybid_' . $row2['project_id']] = (($fetch_your_max_bid['maxamount'] > 0) ? 1 : 0);
		$row2['youmaxbid'] = $ilance->currency->format($fetch_your_max_bid['maxamount'], $row2['currencyid']);
		$row2['bidamount'] = $ilance->currency->format($row2['bidamount'], $row2['currencyid']);
		$show['no_product_bidding_activity'] = false;
		$show['bidpulldownmenu'] = true;
		$product_bidding_activity[] = $row2;
		$row_count2++;
	}
}
else
{
	$show['no_product_bidding_activity'] = true;
	$show['bidpulldownmenu'] = false;
}        
$sub = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
$bidsub = isset($ilance->GPC['bidsub']) ? $ilance->GPC['bidsub'] : '';
$prevnext2 = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page2']), $counter, $scriptpage2, 'page2');

($apihook = $ilance->api('buying_activity_product_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>