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
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
    die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

$area_title = '{_escrow_management}';
$page_title = SITE_NAME . ' - {_escrow_management}';

($apihook = $ilance->api('admincp_escrow_settings')) ? eval($apihook) : false;

// #### build our subnav menu ##################################
$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=escrow', $_SESSION['ilancedata']['user']['slng']);

// #### FORCE CONFIRMATION OF DELIVERY OF BUY NOW ITEMS
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'confirm-buynow-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'buynow', intval($ilance->GPC['id']), false);
	if ($success)
	{
		print_action_success('{_confirmation_of_delivery_has_been_completed_for_this_buy_now_order}', $ilpage['accounting'] . '?cmd=escrow');
		exit();
	}
}
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancel-buynow-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', intval($ilance->GPC['id']), false);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET refund_date = '" . DATETIME24H . "'
		WHERE buynowid = '" . intval($ilance->GPC['id']) . "'
	");
	if ($success)
	{
		print_action_success('{_funds_for_this_buy_now_order_have_been_refunded_to_the_buyer}', $ilance->GPC['return']);
		exit();
	}
}

// #### FORCE RELEASE OF BUYNOW FUNDS FROM ESCROW TO PROVIDER
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'force-buynow-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'buynow', intval($ilance->GPC['id']), false);
	if ($success)
	{
		print_action_success('{_funds_for_this_buy_now_order_have_been_released_to_the_seller}', $ilance->GPC['return']);
		exit();
	}
}

// #### FORCE REFUND OF FUNDS FROM ESCROW BACK TO PAYER ########
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-refund' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
{
	$success = $ilance->escrow_handler->escrow_handler('refund', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
	if ($success)
	{
		print_action_success('{_funds_were_debitted_from_the_providers_account_back_into_the_buyers_account_the_escrow_status_for_this_auction_is_pending}', $ilpage['accounting'] . '?cmd=escrow');
		exit();
	}
}

// #### FORCE ESCROW ACCOUNT CANCELLATION ######################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-cancel' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
{
	$success = $ilance->escrow_handler->escrow_handler('buyercancelescrow', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
	if ($success)
	{
		print_action_success('{_funds_were_debitted_from_the_providers_account_back_into_the_buyers_account_the_escrow_status_for_this_auction_is_pending}', $ilpage['accounting'] . '?cmd=escrow');
		exit();
	}
}

// #### FORCE ESCROW ACCOUNT RELEASE FROM ESCROW TO RECEIVER ###
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_force-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['mode']))
{
	$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', $ilance->GPC['mode'], intval($ilance->GPC['id']), false);
	if ($success)
	{
		print_action_success('{_from_were_forcefully_moved_from_within_this_escrow_account_to_the_sellers_online_account_balance}', $ilpage['accounting'] . '?cmd=escrow');
		exit();
	}
}
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
$limit = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$numberrows = $ilance->db->query("
	SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.isfee2paid, e.feeinvoiceid, e.fee2invoiceid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
	FROM " . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u,
	" . DB_PREFIX . "projects_escrow AS e,
	" . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "invoices AS i
	WHERE e.user_id = u.user_id
		AND e.status != 'cancelled'
		AND e.bid_id = b.bid_id
		AND e.user_id = b.user_id
		AND e.project_id = p.project_id
		AND e.invoiceid = i.invoiceid
		AND i.invoicetype = 'escrow'
		AND p.project_state = 'service'
		AND i.projectid = e.project_id
");
$numberservice = $ilance->db->num_rows($numberrows);
$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$row_count = 0;
$result = $ilance->db->query("
	SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.fee, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.isfee2paid, e.feeinvoiceid, e.fee2invoiceid, b.bid_id, b.user_id as bidder_id, b.bidstatus, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
	FROM " . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u,
	" . DB_PREFIX . "projects_escrow AS e,
	" . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "invoices AS i
	WHERE e.user_id = u.user_id
		AND e.status != 'cancelled'
		AND e.bid_id = b.bid_id
		AND e.user_id = b.user_id
		AND e.project_id = p.project_id
		AND e.invoiceid = i.invoiceid
		AND i.invoicetype = 'escrow'
		AND p.project_state = 'service'
		AND i.projectid = e.project_id
	$limit
");
if ($ilance->db->num_rows($result) > 0)
{
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{
		$row['fees'] = ($row['fee'] > 0) ? $ilance->escrow_fee->print_escrow_fees('as_admin', $row['fee'], $row['project_id']) : '{_none}';
		$row['fees2'] = ($row['fee2'] > 0) ? $ilance->escrow_fee->print_escrow_fees('as_admin', $row['fee2'], $row['project_id']) : '{_none}';
		$row['job_title'] = stripslashes($row['project_title']);
		$row['description'] = short_string(stripslashes($row['description']), 100);
		$row['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['project_user_id'] . '">' . fetch_user('username', $row['project_user_id']) . '</a>';
		$row['buyer_id'] = $row['project_user_id'];
		$row['provider'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['user_id'] . '">' . fetch_user('username', $row['user_id']) . '</a>';
		$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$row['bidamount'] = $ilance->currency->format($row['bidamount'], $row['currencyid']);
		$row['escrowamount'] = $ilance->currency->format($row['escrowamount'], $row['currencyid']);
		if ($row['status'] == 'pending')
		{
			$row['status'] = '{_pending_escrow}';
			$row['actions'] = '<div><input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($row['status'] == 'started')
		{
			$row['status'] = '<div class="green">{_funds_secured}</div>';
			$row['actions'] = '<div><input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($row['status'] == 'confirmed')
		{
			$row['status'] = '<span style="float:left; padding-right:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="{_funds_secured_in_escrow}" /></span>{_pending_release}';
			if ($ilance->auction_award->has_provider_accepted_award($row['project_id'], $row['user_id']))
			{
				$row['actions'] = '<div><input type="button" value="{_release_funds}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
			else
			{
				$row['actions'] = '<div><span title="{_awarded_provider_has_not_accepted_their_bid_award}"><input type="button" value="{_release_funds}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" disabled="disabled" /></span> <input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
			}
		}
		else if ($row['status'] == 'finished')
		{
			$row['status'] = '{_funds_released}';
			// todo: if more than 30 days finished, hide forcable control from admin..
			$row['actions'] = '<div><span style="float:left; padding-right:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="{_funds_released}" /></span><input type="button" value="{_refund}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-refund&amp;mode=service&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$serviceescrows[] = $row;
		$row_count++;
	}
}
else
{
	$show['no_serviceescrows'] = true;
}
$serviceprevnext = print_pagnation($numberservice, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['accounting'] . '?cmd=escrow');
// #### PERFORM PRODUCT ESCROW SEARCH ##########################
$ilance->GPC['page2'] = (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0) ? 1 : intval($ilance->GPC['page2']);
$limit2 = ' ORDER BY p.date_added DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$SQL = "
	SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
	FROM " . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u,
	" . DB_PREFIX . "projects_escrow AS e,
	" . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "invoices AS i
	WHERE e.user_id = u.user_id
		AND e.status != 'cancelled'
		AND e.bid_id = b.bid_id
		AND e.user_id = b.user_id
		AND e.project_id = p.project_id
		AND e.invoiceid = i.invoiceid
		AND i.invoicetype = 'escrow'
		AND p.project_state = 'product'
		AND i.projectid = e.project_id
	$limit2
";
$SQL2 = "
	SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, u.username, e.project_user_id, e.user_id, e.escrowamount, e.bidamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, b.bid_id, b.user_id as bidder_id, b.bidamount, b.bidstatus, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.paid, i.invoicetype, i.paiddate
	FROM " . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "users AS u,
	" . DB_PREFIX . "projects_escrow AS e,
	" . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "invoices AS i
	WHERE e.user_id = u.user_id
		AND e.status != 'cancelled'
		AND e.bid_id = b.bid_id
		AND e.user_id = b.user_id
		AND e.project_id = p.project_id
		AND e.invoiceid = i.invoiceid
		AND i.invoicetype = 'escrow'
		AND p.project_state = 'product'
		AND i.projectid = e.project_id
";
$condition = $condition2 = '';
$numberrows2 = $ilance->db->query($SQL2);
$numberproduct = $ilance->db->num_rows($numberrows2);
$counter2 = ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$row_count2 = 0;
$result2 = $ilance->db->query($SQL);
if ($ilance->db->num_rows($result2) > 0)
{
	$altrows2 = 0;
	while ($row = $ilance->db->fetch_array($result2, DB_ASSOC))
	{
		$altrows2++;
		$row['class2'] = (floor($altrows2 / 2) == ($altrows2 / 2)) ? 'alt2' : 'alt1';
		$row['fees'] = ($row['fee'] > 0) ? $ilance->escrow_fee->print_escrow_fees('as_admin', $row['fee'], $row['project_id']) : '{_none}';
		$row['fees2'] = ($row['fee2'] > 0) ? $ilance->escrow_fee->print_escrow_fees('as_admin', $row['fee2'], $row['project_id']) : '{_none}';
		$row['job_title'] = stripslashes($row['project_title']);
		$row['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['bidder_id'] . '">' . fetch_user('username', $row['bidder_id']) . '</a>';
		$row['merchant'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $row['owner_id'] . '">' . fetch_user('username', $row['owner_id']) . '</a>';
		$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$row['bidamount'] = print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], $row['bidamount'], $row['currencyid']);
		$row['escrowamount'] = $ilance->currency->format($row['escrowamount'], $ilconfig['globalserverlocale_defaultcurrency']);
		$row['shipfees'] = print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], $row['buyershipcost'], $row['currencyid']);
		if ($row['status'] == 'pending')
		{
			// pending - waiting for buyer to forward funds into escrow account
			$row['status'] = '<div class="red">{_do_not_ship}</div>';
			$row['actions'] = '<div><input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($row['status'] == 'started')
		{
			// started - funds forwarded by bidder into escrow account
			$row['status'] = '<div class="green">{_funds_secured}</div>';
			$row['actions'] = '<div><input type="button" value="{_release}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="{_cancel}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-cancel&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($row['status'] == 'confirmed')
		{
			$row['status'] = '{_pending_release}';
			$row['actions'] = '<div><input type="button" value="{_release}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-release&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /> <input type="button" value="{_refund}" onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=_force-refund&amp;mode=product&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($row['status'] == 'finished')
		{
			$row['status'] = '{_funds_released}';
			// todo: if more than 30 days finished, hide forcable control from admin..
			$row['actions'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="{_funds_released}" />';
		}
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$productescrows[] = $row;
		$row_count2++;
	}
}
else
{
	$show['no_productescrows'] = true;
}
$productprevnext = print_pagnation($numberproduct, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], $counter2, $ilpage['accounting'] . '?cmd=escrow', 'page2');
// does admin want to see cancelled orders?
$extrasql = (isset($ilance->GPC['cancelled']) AND $ilance->GPC['cancelled']) ? "WHERE status != ''" : "WHERE status != 'cancelled'";
// #### PURCHASE NOW ESCROW BUYING ACTIVITY ####################
$ilance->GPC['page3'] = (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0) ? 1 : intval($ilance->GPC['page3']);
$orderby3 = ' ORDER BY orderdate DESC';
$limit3 = ' LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$apiextrasql = '';

($apihook = $ilance->api('accounting_escrow_buynow_extra_sql')) ? eval($apihook) : false;

// #### PURCHASE NOW ACTIVITY FOR THIS EXPANDED AUCTION ########
$sql_orders = "
	SELECT orderid, project_id, buyer_id, owner_id, invoiceid, qty, amount, ship_required, ship_location, orderdate, canceldate, arrivedate, paiddate, status, fvf, isfvfpaid, fvfinvoiceid, escrowfee, escrowfeeinvoiceid, escrowfeebuyer, escrowfeebuyerinvoiceid, isescrowfeepaid, isescrowfeebuyerpaid
	FROM " . DB_PREFIX . "buynow_orders
	$extrasql
	$apiextrasql
	$orderby3
	$limit3
";
$numberrows3 = $ilance->db->query($sql_orders);
$numberpurchasenow = $ilance->db->num_rows($numberrows3);
$result_orders = $ilance->db->query($sql_orders);
if ($ilance->db->num_rows($result_orders) > 0)
{
	$order_count = 0;
	$altrows3 = 0;
	while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
	{
		$altrows3++;
		$currencyid = fetch_auction('currencyid', $orderrows['project_id']);
		$orderrows['class3'] = (floor($altrows3 / 2) == ($altrows3 / 2)) ? 'alt2' : 'alt1';
		$orderrows['item'] = fetch_auction('project_title', $orderrows['project_id']);
		$orderrows['merchant'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $orderrows['owner_id'] . '">' . fetch_user('username', $orderrows['owner_id']) . '</a>';
		$orderrows['merchant_id'] = $orderrows['owner_id'];
		$orderrows['buyer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $orderrows['buyer_id'] . '">' . fetch_user('username', $orderrows['buyer_id']) . '</a>';
		$orderrows['orderphone'] = fetch_user('phone', $orderrows['owner_id']);
		$orderrows['orderemail'] = fetch_user('email', $orderrows['owner_id']);
		$orderrows['orderamount'] = $ilance->currency->format($orderrows['amount'], $ilconfig['globalserverlocale_defaultcurrency']);
		if ($orderrows['fvf'] > 0)
		{
			$orderrows['fvf'] = ($orderrows['isfvfpaid']) ? '<div class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></div>' : '<div class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['fvfinvoiceid'] . '">' . $ilance->currency->format($orderrows['fvf']) . '</a></div>';
			$orderrows['fvf'] .= '<div class="smaller gray" style="padding-top:3px">{_commission} {_fee}</div>';
		}
		else
		{
			$orderrows['fvf'] = '{_none}';
		}
		if ($orderrows['escrowfee'] > 0)
		{
			$orderrows['escrowfee'] = ($orderrows['isescrowfeepaid']) ? '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></span>' : '<span class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeeinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfee']) . '</a></span>';
		}
		else
		{
			$orderrows['escrowfee'] = '{_none}';
		}
		if ($orderrows['escrowfeebuyer'] > 0)
		{
			$orderrows['escrowfeebuyer'] = ($orderrows['isescrowfeebuyerpaid']) ? '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeebuyerinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfeebuyer']) . '</a></span>' : '<span class="red"><a href="' . $ilpage['accounting'] . '?cmd=invoices&amp;invoiceid=' . $orderrows['escrowfeebuyerinvoiceid'] . '">' . $ilance->currency->format($orderrows['escrowfeebuyer']) . '</a></span>';
		}
		else
		{
			$orderrows['escrowfeebuyer'] = '{_none}';
		}
		$orderrows['orderdate'] = print_date($orderrows['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$orderrows['orderqty'] = $orderrows['qty'];
		$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
		$orderrows['orderid'] = $orderrows['orderid'];
		if ($orderrows['status'] == 'paid')
		{
			$orderrows['orderstatus'] = '{_funds_secured}, {_pending_shipment}';
			$orderrows['orderactions'] = '<div><span title="{_let_the_buyer_know_the_item_was_shipped}"><input type="button" value="{_confirm_delivery}" onclick="if (confirm_js(\'{_let_the_buyer_know_the_item_was_shipped}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=confirm-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></span></div><div style="padding-top:3px"><span title="{_advise_the_buyer_there_was_a_problem}"><input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_let_the_buyer_know_the_item_was_shipped}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=cancel-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></span></div>';
		}
		else if ($orderrows['status'] == 'pending_delivery')
		{
			$orderrows['orderstatus'] = '{_shipped_pending_buyer_release}';
			$orderrows['orderactions'] = '<div><input type="button" value="{_force_refund}" onclick="if (confirm_js(\'{_forcing_a_refund_will_recredit_the_buyers_online_account_for_the_amount_forwarded_into_escrow}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=cancel-buynow-delivery&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></div><div style="padding-top:3px"><input type="button" value="{_force_release}" onclick="if (confirm_js(\'{_forcing_the_release_of_funds_will_credit_the_sellers_online_account_for_the_amount_paid_into_escrow}\')) location.href=\'' . $ilpage['accounting'] . '?cmd=escrow&amp;subcmd=force-buynow-release&amp;id=' . $orderrows['orderid'] . '\'" class="buttons" style="font-size:10px" /></div>';
		}
		else if ($orderrows['status'] == 'delivered')
		{
			$orderrows['orderactions'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="{_funds_released}" id="" />';
			$orderrows['orderstatus'] = '{_funds_released}';
		}
		else if ($orderrows['status'] == 'cancelled')
		{
			$orderrows['orderactions'] = '-';
			$orderrows['orderstatus'] = '{_cancelled}';
		}
		// #### OFFLINE PAYMENT MODE ###################
		else if ($orderrows['status'] == 'offline')
		{
			$orderrows['orderamount'] = '-';
			$orderrows['orderactions'] = '-';
			$orderrows['orderstatus'] = '<span>{_offline_payment_pending}<div class="smaller gray">{_seller_waiting_for_payment}</div></span>';
		}
		else if ($orderrows['status'] == 'offline_delivered')
		{
			$orderrows['orderamount'] = '-';
			$orderrows['orderactions'] = '-';
			$orderrows['orderstatus'] = '<span class="gray">{_offline_payment_completed}</span>';
		}
		$orderrows['orderlocation'] = ($orderrows['ship_required']) ? $orderrows['ship_location'] : '{_digital_delivery}';
		$buynowescrows[] = $orderrows;
		$order_count++;
	}
}
else
{
	$show['no_buynowescrows'] = true;
}
// escrow settings tab
$configuration_escrowsystem = $ilance->admincp->construct_admin_input('escrowsystem', $ilpage['accounting'] . '?cmd=escrow');
$cancelled = isset($ilance->GPC['cancelled']) ? '&amp;cancelled=1' : '&amp;cancelled=0';
$purchasenowprevnext = print_pagnation($numberpurchasenow, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=escrow' . $cancelled, 'page3');
$income['jan'] = 0;
$totalincome = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-01-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['jan'] = + $res['sum'];
	$totalincome = + $income['jan'];
}
$income['jan'] = $ilance->currency->format($income['jan']);
$income['feb'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-02-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['feb'] = + $res['sum'];
	$totalincome = + $income['feb'];
}
$income['feb'] = $ilance->currency->format($income['feb']);
$income['mar'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-03-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['mar'] = + $res['sum'];
	$totalincome = + $income['mar'];
}
$income['mar'] = $ilance->currency->format($income['mar']);
$income['apr'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-04-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['apr'] = + $res['sum'];
	$totalincome = + $income['apr'];
}
$income['apr'] = $ilance->currency->format($income['apr']);
$income['may'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-05-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['may'] = + $res['sum'];
	$totalincome = + $income['may'];
}
$income['may'] = $ilance->currency->format($income['may']);
$income['jun'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-06-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['jun'] = + $res['sum'];
	$totalincome = + $income['jun'];
}
$income['jun'] = $ilance->currency->format($income['jun']);
$income['jul'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-07-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['jul'] = + $res['sum'];
	$totalincome = + $income['jul'];
}
$income['jul'] = $ilance->currency->format($income['jul']);
$income['aug'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-08-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['aug'] = + $res['sum'];
	$totalincome = + $income['aug'];
}
$income['aug'] = $ilance->currency->format($income['aug']);
$income['sep'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-09-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['sep'] = + $res['sum'];
	$totalincome = + $income['sep'];
}
$income['sep'] = $ilance->currency->format($income['sep']);
$income['oct'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-10-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['oct'] = + $res['sum'];
	$totalincome = + $income['oct'];
}
$income['oct'] = $ilance->currency->format($income['oct']);
$income['nov'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-11-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['nov'] = + $res['sum'];
	$totalincome = + $income['nov'];
}
$income['nov'] = $ilance->currency->format($income['nov']);
$income['dec'] = 0;
$sql = $ilance->db->query("SELECT (fee+fee2) AS sum FROM " . DB_PREFIX . "projects_escrow WHERE date_released LIKE '%" . date('Y') . "-12-%'");
while ($res = $ilance->db->fetch_array($sql))
{
	$income['dec'] = + $res['sum'];
	$totalincome = + $income['dec'];
}
$income['dec'] = $ilance->currency->format($income['dec']);
$totalincome = $ilance->currency->format($totalincome);
$escrowincome[] = $income;
$escrowbalance[] = $ilance->admincp->construct_escrow_balance();
$pprint_array = array ('totalincome', 'configuration_escrowsystem', 'numberservice', 'numberproduct', 'serviceprevnext', 'productprevnext', 'purchasenowprevnext', 'numberpurchasenow');

($apihook = $ilance->api('admincp_accounting_escrows_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'escrows.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings', 'escrowincome', 'serviceescrows', 'productescrows', 'buynowescrows', 'escrowbalance'));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>