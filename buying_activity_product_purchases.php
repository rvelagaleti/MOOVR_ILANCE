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
// #### setup default breadcrumb ###############################################
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();
$ilance->GPC['cmd'] = isset($ilance->GPC['cmd']) ? $ilance->GPC['cmd'] : '';
$ilance->GPC['subcmd'] = isset($ilance->GPC['subcmd']) ? $ilance->GPC['subcmd'] : '';
$ilconfig['globalfilters_maxrowsdisplay'] = (!isset($ilance->GPC['pp']) OR (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] <= 0)) ? 10 : intval($ilance->GPC['pp']);
$keyw = (isset($ilance->GPC['keyw'])) ? handle_input_keywords($ilance->GPC['keyw']) : '';

// #### PRODUCT BUYER ESCROW MANAGEMENT & HANDLER ######################
if (($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['bidsub']) AND $ilance->GPC['bidsub'] == 'product-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['bidsub']) AND $uncrypted['bidsub'] == 'product-escrow' AND $ilconfig['escrowsystem_enabled']))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'productbuyingescrow'
	);

	$show['widescreen'] = false;

	// #### does bidder confirm release of funds to merchant? ######
	if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-release' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = isset($uncrypted['id']) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'product', $uncrypted['id'], false);
		if ($success)
		{
			$area_title = '{_product_escrow_release_of_funds_complete}';
			$page_title = SITE_NAME . ' - {_product_escrow_release_of_funds_complete}';

			print_notice('{_funds_released_escrow_process_complete}', '{_you_have_successfully_released_funds_within_the_escrow}', HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow', '{_product_escrow_payments_out}');
			exit();
		}
	}

	// #### PRODUCT ESCROW MANAGEMENT: BUYER CANCELS FUNDS #########
	else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-release' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND $ilconfig['escrowsystem_payercancancelfunds'] AND $ilconfig['escrowsystem_enabled'] OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-release' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0 AND $ilconfig['escrowsystem_payercancancelfunds'] AND $ilconfig['escrowsystem_enabled']))
	{
		$id = isset($uncrypted['id']) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('buyercancelescrow', 'product', $id, false);
		if ($success)
		{
			$area_title = '{_bidder_cancelled_release_of_funds}';
			$page_title = SITE_NAME . ' - {_bidder_cancelled_release_of_funds}';

			print_notice('{_release_of_funds_cancelled_funds_returned}', '{_you_have_successfully_cancelled_release_of_funds_to}', $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow', '{_product_escrow_payments_out}');
			exit();
		}
	}

	// #### PRODUCT ESCROW MANAGEMENT ##############################
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

	$area_title = '{_product_escrow_management}';
	$page_title = SITE_NAME . ' - {_product_escrow_management}';

	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_product_escrow_management}';

	$orderby = ' ORDER BY p.date_added DESC';
	$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');

	// #### LISTING PERIOD #########################################
	$extra = '';
	$ilance->GPC['period2'] = (isset($ilance->GPC['period2']) ? intval($ilance->GPC['period2']) : -1);
	$periodsql = fetch_startend_sql($ilance->GPC['period2'], 'DATE_SUB', 'p.date_added', '>=');
	$extra .= '&amp;period2=' . $ilance->GPC['period2'];

	$producttabs = print_buying_activity_tabs('product-escrow', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;
	$condition = $condition2 = '';

	$numberrows = $ilance->db->query("
		SELECT p.project_id, p.project_state, p.user_id as owner_id, p.project_title, p.description, p.currencyid, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.escrow_id, e.fee, e.fee2, e.total, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.buynowid, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND e.status != 'cancelled'
			AND e.bid_id = b.bid_id
			AND e.user_id = b.user_id
			AND e.project_id = p.project_id
			AND e.invoiceid = i.invoiceid
			AND i.invoicetype = 'escrow'
			AND i.projectid = e.project_id
			AND p.project_state = 'product'
			AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);

	$result = $ilance->db->query("
		SELECT p.project_id, p.project_state, p.user_id AS owner_id, p.project_title, p.description, p.currencyid, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.escrow_id, e.fee, e.fee2, e.total, e.fee2invoiceid, e.isfee2paid, b.bid_id, b.user_id AS bidder_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.buynowid, i.paid, i.invoicetype, i.paiddate, i.status AS invoicestatus
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE e.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND e.status != 'cancelled'
			AND e.bid_id = b.bid_id
			AND e.user_id = b.user_id
			AND e.project_id = p.project_id
			AND e.invoiceid = i.invoiceid
			AND i.invoicetype = 'escrow'
			AND i.projectid = e.project_id
			AND p.project_state = 'product'
			AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
		$orderby
		$limit
	", 0, null, __FILE__, __LINE__);

	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['job_title'] = stripslashes($row['project_title']);
			$row['merchant'] = fetch_user('username', $row['owner_id']);
			$row['merchant_id'] = $row['owner_id'];
			$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$row['orderlocation'] = $ilance->shipping->print_shipping_address_text($row['bidder_id']);
			$escrowamount = $row['escrowamount'];
			$bidamount = $row['bidamount'];

			if ($ilconfig['globalserverlocale_currencyselector'] AND intval($row['currencyid']) != intval($ilconfig['globalserverlocale_defaultcurrency']))
			{
				$row['bidamount_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $row['bidamount'], $row['currencyid']);
				$row['buyershipcost_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $row['buyershipcost'], $row['currencyid']);
				$row['total_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($row['buyershipcost'] + $row['bidamount']), $row['currencyid']);
				$row['escrowamount'] = '<div class="smaller">' . print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], $escrowamount, $row['currencyid']) . '</div>';
			}
			else
			{
				$row['bidamount_site_currency'] = $row['bidamount'];
				$row['buyershipcost_site_currency'] = $row['buyershipcost'];
				$row['total_site_currency'] = ($row['buyershipcost'] + $row['bidamount']);
				$row['escrowamount'] = '<div class="smaller">' . print_currency_conversion($row['currencyid'], $escrowamount, $ilconfig['globalserverlocale_defaultcurrency']) . '</div>';
			}
			
			
			//unset($escrowamount);
			$noshippingfees = 1;
			if ($row['buyershipcost'] > 0)
			{
				$noshippingfees = 0;
				$row['shipfees'] = $ilance->currency->format($row['buyershipcost'], $row['currencyid']);
			}
			else
			{
				$row['shipfees'] = '{_none}';
			}

			// is this escrow account pending payment?
			if ($row['status'] == 'pending')
			{
				// advise to forward funds into escrow account
				$crypted = array('id' => $row['invoiceid']);
				$row['status'] = '{_forward_funds}';
				$row['actions'] = '<input type="button" value="{_pay_now}" onclick="location.href=\'' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
			}
			else if ($row['status'] == 'started')
			{
				$row['status'] = '{_funds_secured}';
				$row['actions'] = ($ilconfig['escrowsystem_payercancancelfunds'])
				? '<input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_cancel_release_of_funds_and_forward_entire_amount}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow&amp;subcmd=_cancel-release&amp;id=' . $row['escrow_id'] . '\'" class="buttons" style="font-size:10px" />'
				: '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow_funded.gif" border="0" alt="{_funds_secured_in_escrow}" />';
			}
			else if ($row['status'] == 'confirmed')
			{
				$crypted = array(
					'cmd' => 'management',
					'bidsub' => 'product-escrow',
					'subcmd' => '_confirm-release',
					'id' => $row['escrow_id']
				);

				$row['status'] = '{_confirm_release}';
				$row['actions'] = '<input type="button" value="{_release_funds}" onclick="if (confirm_js(\'{_confirm_you_are_about_to_release_funds_within_this_escrow_account_to} ' . $row['merchant'] . '. {_continue_questionmark}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" />';

				// does admin allow bidder to cancel funds within escrow? (default = no)
				if ($ilconfig['escrowsystem_payercancancelfunds'])
				{
					$crypted = array(
					'cmd' => 'management',
					'bidsub' => 'product-escrow',
					'subcmd' => '_cancel-release',
					'id' => $row['escrow_id']
					);

					$row['actions'] .= '<div style="padding-top:3px"><input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_cancel_release_of_funds_and_forward_entire_amount}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:10px" /></div>';
				}
			}
			else if ($row['status'] == 'finished')
			{
				$row['status'] = $row['actions'] = '-';
				$row['escrowamount'] = '<div class="smaller">{_funds_released}</div>';
			}

			$row['ispaid'] = '';
			$row['taxinfo'] = $ilance->escrow_fee->fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], $ilance->escrow_fee->fetch_merchant_escrow_fee($_SESSION['ilancedata']['user']['userid'], $row['total_site_currency']), $row['project_id']);

			if ($row['fee2invoiceid'] > 0)
			{
				$row['fee'] = ($row['isfee2paid'])
				? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span>'
				: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['fee2invoiceid'] . '">(' . $ilance->currency->format($row['fee2']) . ')</a></span>';
			}
			else
			{
				$row['fee'] = ($row['isfee2paid'])
				? '<span class="blue">(' . $ilance->currency->format($row['fee2']) . ')</span>'
				: '<span class="red">(' . $ilance->currency->format($row['fee2']) . ')</span>';
			}

			
			$row['total'] = $ilance->currency->format(($row['buyershipcost'] + $row['bidamount']), $row['currencyid']);
			$row['bidamount'] = $ilance->currency->format($row['bidamount'], $row['currencyid']);
			
			//$row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
			$row['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id'], 1);
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}

	if(isset($ilance->GPC['bidsub']))
	{
		$sub = '&amp;bidsub=' . $ilance->GPC['bidsub'];
	}
	else if(isset($ilance->GPC['sub']))
	{
		$sub = '&amp;sub=' . $ilance->GPC['sub'];
	}
	else 
	{
		$sub = '&amp;sub=product-escrow';
	}
	
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management' . $sub . '&amp;keyw=' . $keyw);

	$pprint_array = array('producttabs','prevnext','redirect','referer','keyw');

	$ilance->template->fetch('main', 'buying_product_escrow.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_loop('main', 'purchase_now_activity');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### PRODUCT SELLER BUY NOW ESCROW HANDLER ##########################
else if (($ilance->GPC['cmd'] == 'management' AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'buynow-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'buynow-escrow'))
{
	// #### define top header nav ##################################
	$topnavlink = array(
	'mycp'
	);

	// #### SELLER CONFIRMS MANUALLY OFFLINE PAYMENT RECEIVED FOR BUYER
	if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-offline-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-offline-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = ($uncrypted['id'] != '' AND $uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('sellerconfirmofflinedelivery', 'buynow', $id, false);
		refresh($ilance->GPC['returnurl']);
		exit();
	}

	// #### SELLER CONFIRMS DELIVERY ###############################
	else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = ($uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'buynow', $id, false);
		refresh($ilance->GPC['returnurl']);
		exit();
	}

	// #### MERCHANT CANCELS DELIVERY - FUNDS RETURN TO BUYER ######
	// this returns all fees as well (escrow fee, fvf fee and buyer escrow fee)
	// fees unpaid will be cancelled
	else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = ($uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', $id, false);
		if ($success)
		{
			$area_title = '{_merchant_product_cancelled_delivery}';
			$page_title = SITE_NAME . ' - {_merchant_product_cancelled_delivery}';
			print_notice('{_escrow_account_cancelled_funds_returned}', '{_you_have_successfully_cancelled_delivery_for_this_particular_auctions_escrow_account}', HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold', '{_items_ive_sold}');
			exit();
		}
	}

	refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&sub=sold' . '&page=' . $ilance->GPC['page']);
	exit();
}

// #### PRODUCT SELLER BUY NOW ESCROW MANAGEMENT & HANDLER #############
else if (($ilance->GPC['cmd'] == 'management' AND $ilconfig['escrowsystem_enabled'] AND isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'product-escrow' OR isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'management' AND isset($uncrypted['sub']) AND $uncrypted['sub'] == 'product-escrow'))
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'productsellingescrow'
	);

	$show['widescreen'] = false;

	$area_title = '{_product_escrow_management}';
	$page_title = SITE_NAME . ' - {_product_escrow_management}';

	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[HTTP_SERVER . "$ilpage[selling]?cmd=management"] = '{_selling_activity}';
	$navcrumb[""] = '{_product_escrow_management}';

	// #### SELLER CONFIRMS BUYERS SHIPMENT AND DELIVERY (ESCROW)
	if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_confirm-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_confirm-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = ($uncrypted['id'] != '' AND $uncrypted['id'] > 0) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('sellerconfirmdelivery', 'product', $id, false);
		refresh($ilance->GPC['returnurl']);
		exit();
	}

	// #### SELLER CANCEL BUYERS SHIPMENT AND DELIVERY (ESCROW)
	else if ((isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_cancel-delivery' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0))
	{
		$id = isset($uncrypted['id']) ? intval($uncrypted['id']) : intval($ilance->GPC['id']);
		$success = $ilance->escrow_handler->escrow_handler('sellercancelescrow', 'product', $id, false);
		if ($success)
		{
			$area_title = '{_merchant_product_cancelled_delivery}';
			$page_title = SITE_NAME . ' - {_merchant_product_cancelled_delivery}';

			print_notice('{_escrow_account_cancelled_funds_returned}', '{_you_have_successfully_cancelled_delivery_for_this_particular_auctions_escrow_account}', HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&sub=product-escrow', '{_escrow_management}');
			exit();
		}
	}

	// #### PRODUCT ESCROW SELLER MANAGEMENT #######################
	$number = $number2 = $number3 = $number4 = $counter = $counter2 = $counter3 = $counter4 = 0;

	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilance->GPC['p2'] = (!isset($ilance->GPC['p2']) OR isset($ilance->GPC['p2']) AND $ilance->GPC['p2'] <= 0) ? 1 : intval($ilance->GPC['p2']);
	$ilance->GPC['p3'] = (!isset($ilance->GPC['p3']) OR isset($ilance->GPC['p3']) AND $ilance->GPC['p3'] <= 0) ? 1 : intval($ilance->GPC['p3']);
	$ilance->GPC['p4'] = (!isset($ilance->GPC['p4']) OR isset($ilance->GPC['p4']) AND $ilance->GPC['p4'] <= 0) ? 1 : intval($ilance->GPC['p4']);

	$counter3 = ($ilance->GPC['p3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$counter4 = ($ilance->GPC['p4'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	// #### listing period #########################################
	require_once(DIR_CORE . 'functions_search.php');

	// #### listing period #########################################
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$extra = '&amp;period=' . $ilance->GPC['period'];
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'e.date_awarded', '>=');

	// #### ordering by fields defaults ############################
	$orderbyfields = array('date_awarded');
	$orderby = '&amp;orderby=date_awarded';
	$orderbysql = 'e.date_awarded';
	if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
	{
		$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
		$orderbysql = 'e.' . $ilance->GPC['orderby'];
	}

	// #### display order defaults #################################
	$displayorderfields = array('asc', 'desc');
	$displayorder = '&amp;displayorder=asc';
	$currentdisplayorder = $displayorder;
	$displayordersql = 'DESC';
	if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
	{
		$displayorder = '&amp;displayorder=desc';
		$currentdisplayorder = '&amp;displayorder=asc';
	}
	else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
	{
		$displayorder = '&amp;displayorder=asc';
		$currentdisplayorder = '&amp;displayorder=desc';
	}
	if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
	{
		$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
	}
	if (!empty($ilance->GPC['sub']))
	{
		$extra .= '&amp;sub=' . $ilance->GPC['sub'];
	}


	$limit = ' ORDER BY ' . $orderbysql . ' ' . $displayordersql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];

	// used within templates
	$php_self = HTTP_SERVER . $ilpage['escrow'] . '?cmd=management' . $displayorder . $extra;

	// used within prev / next page nav
	$scriptpage = HTTP_SERVER . $ilpage['escrow'] . '?cmd=management' . $currentdisplayorder . $orderby . $extra;

	require_once(DIR_CORE . 'functions_tabs.php');
	$producttabs = print_selling_activity_tabs('escrow', 'product', $_SESSION['ilancedata']['user']['userid']);

	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$row_count = 0;

	$SQL = "
		SELECT p.project_id, p.user_id, p.project_title, p.description, p.currencyid, u.username, u.user_id, e.project_user_id, e.user_id, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id, b.project_user_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.user_id, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND e.status != 'cancelled'
			AND e.bid_id = b.bid_id
			AND e.user_id = b.user_id
			AND e.project_id = p.project_id
			AND e.invoiceid = i.invoiceid
			AND i.invoicetype = 'escrow'
			AND i.projectid = e.project_id 
			AND p.project_state = 'product'
			AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
		$limit
	";

	$SQL2 = "
		SELECT p.project_id, p.user_id, p.project_title, p.description, p.currencyid, u.username, u.user_id, e.project_user_id, e.user_id, e.escrowamount, e.date_awarded, e.date_paid, e.status, e.bid_id, e.project_id, e.invoiceid, e.escrow_id, e.fee, e.fee2, e.isfeepaid, e.feeinvoiceid, b.bid_id, b.user_id, b.project_user_id, b.bidstatus, b.bidamount, b.buyershipcost, i.invoiceid, i.projectid, i.buynowid, i.user_id, i.paid, i.invoicetype, i.paiddate
		FROM " . DB_PREFIX . "projects AS p,
		" . DB_PREFIX . "users AS u,
		" . DB_PREFIX . "projects_escrow AS e,
		" . DB_PREFIX . "project_bids AS b,
		" . DB_PREFIX . "invoices AS i
		WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND e.project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			$periodsql
			AND e.status != 'cancelled'
			AND e.bid_id = b.bid_id
			AND e.user_id = b.user_id
			AND e.project_id = p.project_id
			AND e.invoiceid = i.invoiceid
			AND i.invoicetype = 'escrow'
			AND i.projectid = e.project_id
			AND p.project_state = 'product'
			AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
	";

	$condition = $condition2 = '';

	$numberrows = $ilance->db->query($SQL2, 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);

	$result = $ilance->db->query($SQL, 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result) > 0)
	{
		while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
		{
			$row['job_title'] = strip_vulgar_words(stripslashes($row['project_title']));
			$row['buyer'] = fetch_user('username', $row['user_id']);
			$row['provider'] = stripslashes($row['username']);
			$row['awarddate'] = print_date($row['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$escrowamount = $row['escrowamount'];
			
			if ($ilconfig['globalserverlocale_currencyselector'] AND intval($row['currencyid']) != intval($ilconfig['globalserverlocale_defaultcurrency']))
			{
				$row['bidamount_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $row['bidamount'], $row['currencyid']);
				$row['buyershipcost_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $row['buyershipcost'], $row['currencyid']);
				$row['total_site_currency'] = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($row['buyershipcost'] + $row['bidamount']), $row['currencyid']);
				$row['escrowamount'] = '<div class="smaller">' . print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], $escrowamount, $row['currencyid']) . '</div>';
			}
			else
			{
				$row['bidamount_site_currency'] = $row['bidamount'];
				$row['buyershipcost_site_currency'] = $row['buyershipcost'];
				$row['total_site_currency'] = ($row['buyershipcost'] + $row['bidamount']);
				$row['escrowamount'] = '<div class="smaller">' . print_currency_conversion($row['currencyid'], $escrowamount, $ilconfig['globalserverlocale_defaultcurrency']) . '</div>';
			}
			
			unset($escrowamount);

			// does bidder pay shipping?
			$noshippingfees = 1;
			if ($row['buyershipcost'] > 0)
			{
				$noshippingfees = 0;
				$row['shipfees'] = $ilance->currency->format($row['buyershipcost'], $row['currencyid']);
			}
			else
			{
				$row['shipfees'] = '{_none}';
			}

			// merchant viewing escrow information
			if ($row['status'] == 'pending')
			{
				// pending - waiting for buyer to forward funds
				$row['status'] = '<strong>{_do_not_ship_upper}</strong>: ' . '{_waiting_for_buyer_to_fund_this_escrow_account}';
				$row['actions'] = '{_pending}';
			}
			else if ($row['status'] == 'started')
			{
				// started - funds forwarded by buyer into escrow
				$row['status'] = '{_funds_secured}';

				// funds secured - show release funds back to customer or confirm delivery
				$crypted = array(
					'cmd' => 'management',
					'sub' => 'product-escrow',
					'subcmd' => '_confirm-delivery',
					'id' => $row['escrow_id']
				);
				$row['actions'] = '<div><input type="button" value="{_mark_as_shipped}" onclick="if (confirm_js(\'{_confirm_the_product_has_been_shipped_or_delivered_to_the_highest_bidder}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'" class="buttons" style="font-size:10px" /></div>';

				$crypted = array(
					'cmd' => 'management',
					'sub' => 'product-escrow',
					'subcmd' => '_cancel-delivery',
					'id' => $row['escrow_id']
				);
				$row['actions'] .= '<!--<div style="padding-top:3px; padding-bottom:3px">{_or_upper}</div>--><div style="padding-top:3px"><input type="button" value="{_return_funds}" onclick="if (confirm_js(\'{_return_funds_in_escrow_back_to_highest_bidder}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'" class="buttons" style="font-size:10px" /></div>';
			}
			else if ($row['status'] == 'confirmed')
			{
				$row['status'] = '{_pending_release}';
				$row['actions'] = '-';
			}
			else if ($row['status'] == 'finished')
			{
				$row['status'] = $row['actions'] = '-';
				$row['escrowamount'] = '<div class="smaller">{_funds_released}</div>';
			}

			$row['taxinfo'] = $ilance->escrow_fee->fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], $ilance->escrow_fee->fetch_merchant_escrow_fee(floatval($row['bidamount_site_currency'] + $row['buyershipcost_site_currency'])), $row['project_id']);

			// fee to seller
			//$row['total'] = ($noshippingfees == 0 AND $row['buyershipcost'] > 0) ? ($row['bidamount'] + $row['buyershipcost']) : $row['bidamount'];
			//$row['total'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['total'], $row['currencyid']);

			if ($row['feeinvoiceid'] > 0)
			{
				$row['fee'] = ($row['isfeepaid'])
				? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">(' . $ilance->currency->format($row['fee']) . ')</a></span>'
				: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['feeinvoiceid'] . '">(' . $ilance->currency->format($row['fee']) . ')</a></span>';
			}
			else
			{
				$row['fee'] = ($row['isfeepaid'])
				? '<span class="blue">(' . $ilance->currency->format($row['fee']) . ')</span>'
				: '<span class="red">(' . $ilance->currency->format($row['fee']) . ')</span>';
			}

			$row['orderlocation'] = $ilance->shipping->print_shipping_address_text($row['user_id']);
			
			$row['total'] = $ilance->currency->format(($row['buyershipcost'] + $row['bidamount']), $row['currencyid']);
			$row['bidamount'] = $ilance->currency->format($row['bidamount'], $row['currencyid']);
			
			//$row['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['bidamount'], $row['currencyid']);
			$row['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $row['project_id'], 'thumb', $row['project_id'], 1);
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
			$project_results_rows[] = $row;
			$row_count++;
		}
	}
	else
	{
		$show['no_project_rows_returned'] = true;
	}

	if (!empty($ilance->GPC) AND is_array($ilance->GPC))
	{
		foreach ($ilance->GPC as $key => $value)
		{
			if ($key != 'page' AND $key != 'pp')
			{
				if (!isset($searchquery))
				{
					$searchquery = '?' . $key . '=' . $value;
				}
				else
				{
					$searchquery .= '&amp;' . $key . '=' . $value;
				}
			}
		}
		$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $searchquery);
	}
	if (!empty($ilance->GPC) AND is_array($ilance->GPC))
	{
		foreach ($ilance->GPC as $key => $value)
		{
			if ($key != 'p2' AND $key != 'pp')
			{
				if (!isset($searchquery))
				{
					$searchquery = '?' . $key . '=' . $value;
				}
				else
				{
					$searchquery .= '&amp;' . $key . '=' . $value;
				}
			}
		}

		$prevnext2 = print_pagnation($number2, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['p2']), $counter2, $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow' . $searchquery, 'p2');
	}



	$pprint_array = array('php_self','producttabs','rfpvisible','countdelisted','prevnext','redirect','referer','keyw');

	$ilance->template->fetch('main', 'selling_product_escrow.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', 'project_results_rows');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
else
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'buynowbuyingescrow'
	);
	$show['widescreen'] = false;
	$customsqlquery = '';
	
	($apihook = $ilance->api('buying_buynow_escrow_start')) ? eval($apihook) : false;

	// #### buyer release funds in escrow to seller handler [pending_delivery -> delivered]
	if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_release-buynow-funds' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$success = $ilance->escrow_handler->escrow_handler('buyerconfirmrelease', 'buynow', intval($uncrypted['id']), false);
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	// #### buyer refunds account balance with funds held in escrow
	else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == '_cancel-buynow-delivery' AND isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$success = $ilance->escrow_handler->escrow_handler('reversal', 'buynow', intval($uncrypted['id']), false);
		refresh($ilance->GPC['returnurl']);
		exit();
	}
	$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);
	$ilance->GPC['sub'] = isset($ilance->GPC['sub']) ? $ilance->GPC['sub'] : '';
	$limit = ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
	require_once(DIR_CORE . 'functions_search.php');
	require_once(DIR_CORE . 'functions_tabs.php');

	$area_title = '{_purchase_now_escrow_buying_activity}';
	$page_title = SITE_NAME . ' - {_purchase_now_escrow_buying_activity}';

	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[""] = '{_purchases}';
	$block_header_title = '{_purchases}';
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$ilance->GPC['orderby'] = (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'date_end');
	$ilance->GPC['displayorder'] = (isset($ilance->GPC['displayorder']) ? $ilance->GPC['displayorder'] : 'desc');
	$ilance->GPC['pp'] = (isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay']);
	$period_options = array(
		'-1' => '{_placed_any_date}',
		'1' => '{_placed_last_hour}',
		'6' => '{_placed_last_12_hours}',
		'7' => '{_placed_last_24_hours}',
		'13' => '{_placed_last_7_days}',
		'14' => '{_placed_last_14_days}',
		'15' => '{_placed_last_30_days}',
		'16' => '{_placed_last_60_days}',
		'17' => '{_placed_last_90_days}'
	);
	$period_pulldown = construct_pulldown('period_pull_id', 'period', $period_options, $ilance->GPC['period'], 'class="select"');
	$orderby_options = array(
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
	$orderby_pulldown = construct_pulldown('orderby_pull_id', 'orderby', $orderby_options, $ilance->GPC['orderby'], 'class="select"');
	$displayorder_pulldown = construct_pulldown('displayorder_pull_id', 'displayorder', array('desc' => '{_descending}', 'asc' => '{_ascending}'), $ilance->GPC['displayorder'], 'class="select"');
	$pp_pulldown = construct_pulldown('pp_pull_id', 'pp', array('10' => '10', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000'), $ilance->GPC['pp'], 'class="select-75"');

	// #### does buyer want to see their cancelled orders? #########
	$extrasql = (isset($ilance->GPC['cancelled']) AND $ilance->GPC['cancelled']) ? "" : "AND b.status != 'cancelled'";
	$extra = '';

	// #### ordering by fields defaults ############################
	$orderbyfields = array('project_id', 'amount', 'orderdate', 'paiddate', 'qty', 'escrowfeebuyer');
	$orderby = '&amp;orderby=orderdate';
	$orderbysql = 'ORDER BY orderdate';
	$ilance->GPC['orderby'] = (isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'orderdate');
	if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
	{
		$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
		$orderbysql = 'ORDER BY ' . $ilance->GPC['orderby'];
	}

	// #### display order defaults #################################
	$displayorderfields = array('asc', 'desc');
	$displayorder = '&amp;displayorder=desc';
	$currentdisplayorder = $displayorder;
	$displayordersql = 'DESC';
	if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'asc')
	{
		$displayorder = '&amp;displayorder=desc';
		$currentdisplayorder = '&amp;displayorder=asc';
	}
	else if (isset($ilance->GPC['displayorder']) AND $ilance->GPC['displayorder'] == 'desc')
	{
		$displayorder = '&amp;displayorder=asc';
		$currentdisplayorder = '&amp;displayorder=desc';
	}
	if (isset($ilance->GPC['displayorder']) AND in_array($ilance->GPC['displayorder'], $displayorderfields))
	{
		$displayordersql = mb_strtoupper($ilance->GPC['displayorder']);
	}
	$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
	$extrakeyw = isset($ilance->GPC['keyw']) ? "AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'" : '';
	$keywx = '&keyw=' . $keyw;

	// #### used within templates for sorting ######################
	$php_self = HTTPS_SERVER . $ilpage['buying'] . '?cmd=purchases' . $displayorder . $extra . $keywx;
	// #### default listing period #################################
	$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
	$period = '&amp;period=' . $ilance->GPC['period'];
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');
	$producttabs = print_buying_activity_tabs('buynow-escrow', 'product', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'b.orderdate', '>=');
	
	/*SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "b.bid_id, b.user_id AS bidderid, b.bidamount, b.date_added, b.date_retracted, b.bidstatus, b.bidstate, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.qty, b.winnermarkedaspaid, b.winnermarkedaspaiddate, b.winnermarkedaspaidmethod, UNIX_TIMESTAMP(p.date_end)-UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.project_id, p.cid, p.close_date, p.project_title, p.bids, p.status, p.project_details, p.project_type, p.project_state, p.date_starts, p.date_end, p.currentprice, p.bid_details, p.buynow, p.reserve, p.featured, p.user_id, p.filter_escrow, p.reserve_price, p.isifpaid, p.ifinvoiceid, p.isfvfpaid, p.fvfinvoiceid, p.bidsshortlisted, p.bidsretracted, p.bidsdeclined, p.haswinner, p.hasbuynowwinner, p.winner_user_id, p.charityid, p.donationpercentage, p.donation, p.donermarkedaspaid, p.donermarkedaspaiddate, p.escrow_id, p.paymethodoptions, p.currencyid, p.description_videourl, p.filter_budget, p.filter_gateway, p.filtered_auctiontype, p.buynow_qty_lot, p.items_in_lot, s.ship_method, s.ship_handlingtime, s.ship_handlingfee$query_field_info
	FROM " . DB_PREFIX . "project_bids AS b,
	" . DB_PREFIX . "projects AS p,
	" . DB_PREFIX . "projects_shipping AS s
	WHERE b.project_id = p.project_id
	    $extra
	    AND p.project_id = s.project_id
	    AND b.bidstate != 'retracted'
	    AND b.user_id = '" . intval($userid) . "'
	    AND p.project_state = 'product'
	    AND b.bidstatus = 'awarded'
	    AND (p.status = 'open' OR p.status = 'archived' OR p.status = 'finished' OR p.status = 'expired')*/
	$numberrows = $ilance->db->query("
		SELECT b.orderid
		FROM " . DB_PREFIX . "buynow_orders b
		LEFT JOIN " . DB_PREFIX . "projects p ON (b.project_id = p.project_id)
		WHERE buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		$extrakeyw
		$periodsql
		$extrasql
		$customsqlquery
		$orderbysql
		$displayordersql
	", 0, null, __FILE__, __LINE__);
	$number = $ilance->db->num_rows($numberrows);
	$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
	$result_orders = $ilance->db->query("
		SELECT b.orderid, b.project_id, b.buyer_id, b.owner_id, b.invoiceid, b.attachid, b.qty, b.amount, b.escrowfee, b.escrowfeebuyer, b.fvf, b.fvfbuyer, b.isescrowfeepaid, b.isescrowfeebuyerpaid, b.isfvfpaid, b.isfvfbuyerpaid, b.escrowfeeinvoiceid, b.escrowfeebuyerinvoiceid, b.fvfinvoiceid, b.fvfbuyerinvoiceid, b.ship_required, b.ship_location, b.orderdate, b.canceldate, b.arrivedate, b.paiddate, b.releasedate, b.winnermarkedaspaidmethod, b.winnermarkedaspaiddate, b.winnermarkedaspaid, b.buyerpaymethod, b.sellermarkedasshipped, b.sellermarkedasshippeddate, b.buyershipperid, b.buyershipcost, b.buyerfeedback, b.sellerfeedback, b.status, b.shiptracknumber, p.buynow_price, b.originalcurrencyid, b.originalcurrencyidrate, b.convertedtocurrencyid, b.convertedtocurrencyidrate, p.project_title, p.cid
		FROM " . DB_PREFIX . "buynow_orders b
		LEFT JOIN " . DB_PREFIX . "projects p ON (b.project_id = p.project_id)
		WHERE buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		$extrakeyw
		$periodsql
		$extrasql
		$customsqlquery
		$orderbysql
		$displayordersql
		$limit
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($result_orders) > 0)
	{
		$order_count = 0;
		while ($orderrows = $ilance->db->fetch_array($result_orders, DB_ASSOC))
		{
			$orderrows['currencyid'] = fetch_auction('currencyid', $orderrows['project_id']);
			$orderrows['taxinfo'] = $ilance->escrow_fee->fetch_escrow_taxinfo_bit($_SESSION['ilancedata']['user']['userid'], $ilance->escrow_fee->fetch_merchant_escrow_fee($_SESSION['ilancedata']['user']['userid'], $orderrows['amount']), $orderrows['project_id']);
			$orderrows['itemid'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $orderrows['project_id'] . '">' . $orderrows['project_id'] . '</a>';
			$orderrows['merchant'] = print_username($orderrows['owner_id'], 'href', 0, '', '');
			$orderrows['merchantplain'] = print_username($orderrows['owner_id'], 'plain', 0, '', '');
			$orderrows['ordermerchant'] = print_username($orderrows['owner_id'], 'href');
			$orderrows['ordermerchant_id'] = $orderrows['owner_id'];
			$orderrows['orderphone'] = fetch_user('phone', $orderrows['owner_id']);
			$orderrows['orderemail'] = fetch_user('email', $orderrows['owner_id']);
			$orderrows['contactseller'] = '';
			$title = handle_input_keywords($orderrows['project_title']);
			$orderrows['item_title'] = handle_input_keywords($orderrows['project_title']);
			$orderrows['item_name'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $orderrows['project_id'] . '">' . handle_input_keywords($title) . '</a>';
			$orderrows['itemsorlots'] = '{_items_lower}'; // todo: check lots logic
			
			$res2['amount'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($orderrows['invoiceid']) . "'", "amount"));
			if ($orderrows['status'] == 'delivered')
			{
				//$orderamount = $res2['amount'];    
				$orderamount = $res2['amount'] = $res2['amount'] - $orderrows['buyershipcost'];
			}
			else
			{
				//$orderamount = $orderrows['amount'];
				$orderamount = $orderrows['amount'] = $orderrows['amount'] - $orderrows['buyershipcost'];
			}
			if ($ilconfig['globalauctionsettings_seourls'])
			{
				$orderrows['photo'] = $ilance->auction->print_item_photo('', 'thumbgallery', $orderrows['project_id'], 1);
				unset($url);
			}
			else
			{
				$orderrows['photo'] = $ilance->auction->print_item_photo('', 'thumbgallery', $orderrows['project_id'], 1);
			}
			$orderrows['orderamount'] = $ilance->currency->format($orderamount);
			$orderrows['price'] = $orderrows['amount'] = $orderrows['orderamount'] =  $ilance->currency->format($orderamount);
			$orderrows['orderqty'] = $orderrows['qty'];
			$orderrows['deliveryestimate'] = $ilance->shipping->print_delivery_estimate($orderrows['project_id'], $orderrows['orderdate']);
			$orderrows['orderdate'] = print_date($orderrows['orderdate'], 'M j Y g:i:s A', 0, 0);
			$orderrows['orderinvoiceid'] = $orderrows['invoiceid'];
			$orderrows['orderid'] = $orderrows['orderid'];
			$orderrows['escrowfees'] = 0.00;
			$orderrows['shipping'] = '';
			$orderrows['shiptracking'] = !empty($orderrows['shiptracknumber']) ? '<div style="padding-bottom:3px;padding-left:25px" class="smaller"><strong>{_ship_tracking}:</strong> ' . $ilance->shipping->print_tracking_url($orderrows['buyershipperid'], $orderrows['shiptracknumber']) . '</div>' : '';
			$orderrows['delivery'] = '';
			$orderrows['escrowfeebuyer'] = '';
			$orderrows['orderinfo'] = '';
			if ($orderrows['escrowfeebuyer'] > 0)
			{
				$orderrows['escrowfees'] = $orderrows['escrowfeebuyer']; 
				$orderrows['escrowfeebuyer'] = ($orderrows['isescrowfeebuyerpaid'])
					? '<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeebuyerinvoiceid'] . '">(' . $ilance->currency->format($orderrows['escrowfeebuyer']) . ')</a></span>'
					: '<span class="red"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $orderrows['escrowfeebuyerinvoiceid'] . '">(' . $ilance->currency->format($orderrows['escrowfeebuyer']) . ')</a></span>';
			}

			($apihook = $ilance->api('buying_management_buynow_escrow_end')) ? eval($apihook) : false;

			$escrowamount = $orderrows['amount'];
			$orderrows['escrowtotal'] = '<div class="smaller">' . print_currency_conversion($ilconfig['globalserverlocale_defaultcurrency'], ($escrowamount)) . '</div>';
			$orderrows['qtylot'] = '';
			$arr = $ilance->shipping->fetch_ship_cost_by_shipperid($orderrows['project_id'], $orderrows['buyershipperid'], $orderrows['orderqty'], $orderrows['buyershipcost']);
			$orderrows['buyershipcost'] = isset($arr['total']) ? $arr['total'] : '0.00';
			$orderrows['total'] = $ilance->currency->format(($orderamount) + $orderrows['buyershipcost'] + $orderrows['escrowfeebuyer'], $orderrows['currencyid']);
			
			$show['selleraddedtowatchlist'] = $ilance->watchlist->is_seller_added_to_watchlist($orderrows['owner_id']);
			
			// does buyer need to give feedback to seller for this order?
			$leftfeedback = 0;
			$feedbackaction = '';
			$digitaldownloadaction = '';
			if ($ilance->feedback->has_left_feedback($orderrows['owner_id'], $_SESSION['ilancedata']['user']['userid'], $orderrows['project_id'], 'seller', $orderrows['orderid']))
			{
				$leftfeedback = 1;
				$orderrows['feedback'] = '<div><span title="{_feedback_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="" /></span></div>';
			}
			else
			{
				$orderrows['feedback'] = '<div><span title="{_feedback_not_left}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="" /></span></div>';
				$feedbackaction = '<div style="padding-top:3px"><span title="{_submit_feedback_for} ' . fetch_user('username', $orderrows['ordermerchant_id']) . '" class="blueonly"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}&amp;pid=' . $orderrows['project_id'] . '#' . $orderrows['project_id'] . '_' . $_SESSION['ilancedata']['user']['userid'] . '_' . $orderrows['owner_id'] . '_seller' . $orderrows['orderid'] . '">{_leave_feedback}</a></span></div>';
			}
			
			if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 'buyer', $orderrows['orderid']))
			{
				$orderrows['feedbackreceived'] = '<div><span title="{_feedback_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received.gif" border="0" alt="" /></span></div>';
			}
			else
			{
				$orderrows['feedbackreceived'] = '<div><span title="{_feedback_not_received}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_received_litegray.gif" border="0" alt="" /></span></div>';
			}
			// shipping or digital download?
			if ($orderrows['ship_required'] OR ($orderrows['buyershipperid'] > 0 AND $orderrows['buyershipcost']) > 0)
			{
				if ($orderrows['buyershipcost'] > 0)
				{
					$buyershipcost = $orderrows['buyershipcost'];
					$orderrows['shipping'] = '<span title="{_shipping}">+ ' . $ilance->currency->format($buyershipcost) . '</span>';
				}
				if ($orderrows['sellermarkedasshipped'] AND $orderrows['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
				{
					$orderrows['shipstatus'] = '<div><span title="{_marked_as_shipped_on} ' . print_date($orderrows['sellermarkedasshippeddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
				}
				else
				{
					$orderrows['shipstatus'] = '<div><span title="{_the_seller_has_not_yet_marked_your_shipment_as_delivered}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
				}
			}
			else
			{
				$orderrows['buyershipcost'] = 0;
				$orderrows['shippingpartner'] = '{_none}';
				
				// digital download
				$digitalfile = '{_contact_seller}';
				$dquery = $ilance->db->query("
					SELECT filename, counter, filesize, attachid
					FROM " . DB_PREFIX . "attachment
					WHERE project_id = '" . $orderrows['project_id'] . "'
						AND attachtype = 'digital'
						AND user_id = '" . $orderrows['owner_id'] . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($dquery) > 0)
				{
					$dfile = $ilance->db->fetch_array($dquery, DB_ASSOC);
					if ($orderrows['status'] == 'paid' OR $orderrows['status'] == 'delivered' OR $orderrows['status'] == 'offline_delivered')
					{
						$crypted = array('id' => $dfile['attachid']);
						$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ')';
						$digitaldownloadaction = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
							<td><div><span class="blueonly" title="' . $digitalfile . '"><span style="float:left;margin-top:-3px;padding-right:6px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'paperclip.gif" alt="" border="0" /></span><a href="' . HTTPS_SERVER . $ilpage['attachment'] . '?crypted=' . encrypt_url($crypted) . '">{_download_digital_attachment}</a></span></div></td>
						</tr>';
					}
					else
					{
						$digitalfile = handle_input_keywords($dfile['filename']) . ' (' . print_filesize($dfile['filesize']) . ') ({_waiting_for_seller_to_confirm_delivery})';
						$digitaldownloadaction = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
							<td><div><span class="gray" title="{_waiting_for_seller_to_confirm_delivery}">{_download_digital_attachment}</span></div></td>
						</tr>';
					}
					$orderrows['shipstatus'] = '<div><span title="{_digital_delivery}: ' . handle_input_keywords($digitalfile) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox.png" border="0" alt="" /></span></div>';
					$orderrows['shipping'] = '{_digital_delivery}';
				}

				// no shipping local pickup only
				else
				{
					$orderrows['shipping'] = '{_local_pickup_only}';
					$orderrows['shipstatus'] = '<div><span title="{_not_in_use} ({_local_pickup_only})"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/shipbox_litegray.png" border="0" alt="" /></span></div>';
				}
			}
			// #### order escrow account paid
			if ($orderrows['status'] == 'paid')
			{
				// does admin allow bidder to cancel funds? (default = no)
				$orderactions = '';
				if ($ilconfig['escrowsystem_payercancancelfunds'])
				{
					$crypted = array(
						'cmd' => 'management',
						'bidsub' => 'buynow-escrow',
						'subcmd' => '_cancel-buynow-delivery',
						'id' => $orderrows['orderid']
					);
					$orderactions = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly" title="{_this_will_cancel_the_order}"><a href="javascript:void(0)" onclick="if (confirm_js(\'{_cancel_payment_return_funds_in_escrow_back_to_my_online_account}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'">{_return_funds_from_escrow}</a></span></div></td></tr>';
				}
				$orderrows['orderactions'] = '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
					<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
' . $orderactions . '
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
				$orderrows['escrowtotal'] = '<div><span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_green.png" border="0" alt="" id="" /></span></div>';
				$orderrows['paystatus'] = '<div><span title="{_escrow_account_funded_on} ' . print_date($orderrows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
				$orderrows['buyerpaymethod'] = (mb_substr($orderrows['buyerpaymethod'], 1) == '_') ? '{' . mb_substr($orderrows['buyerpaymethod'], 8) . '}' : '{_' . $orderrows['buyerpaymethod'] . '}';
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				$orderrows['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
			}
			// #### buy now escrow funded- seller can ship items
			else if ($orderrows['status'] == 'pending_delivery')
			{
				$orderactions = '';
				if ($ilconfig['escrowsystem_payercancancelfunds'])
				{
					$crypted2 = array(
						'cmd' => 'management',
						'bidsub' => 'buynow-escrow',
						'subcmd' => '_cancel-buynow-delivery',
						'id' => $orderrows['orderid']
					);
					$orderactions = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly" title="{_this_will_cancel_the_order}"><a href="javascript:void(0)" onclick="if (confirm_js(\'{_cancel_payment_return_funds_in_escrow_back_to_my_online_account}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted2) . '&amp;returnurl={pageurl_urlencoded}\'">{_return_funds_from_escrow}</a></span></div></td></tr>';
					unset($crypted2);
				}
				$crypted = array(
					'cmd' => 'management',
					'bidsub' => 'buynow-escrow',
					'subcmd' => '_release-buynow-funds',
					'id' => $orderrows['orderid']
				);
				$orderrows['orderactions'] = '<div class="blue"><a href="javascript:void(0)" onclick="if (confirm_js(\'{_confirm_you_are_about_to_release_funds_within_this_escrow_account_to_the_merchant_continue}\')) location.href=\'' . HTTPS_SERVER . $ilpage['escrow'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\'"><strong>{_release_escrow_funds}</strong></a></div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
					<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
' . $orderactions . '
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
				$orderrows['escrowtotal'] = '<div><span title="{_funds_secured_in_escrow}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_green.png" border="0" alt="" id="" /></span></div>';
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				$orderrows['buyerpaymethod'] = (mb_substr($orderrows['buyerpaymethod'], 1) == '_') ? '{' . mb_substr($orderrows['buyerpaymethod'], 8) . '}' : '{_' . $orderrows['buyerpaymethod'] . '}';
				$orderrows['paystatus'] = '<div><span title="{_escrow_account_funded_on} ' . print_date($orderrows['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
				$orderrows['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
			}

			// #### buyer has released funds in escrow to seller
			else if ($orderrows['status'] == 'delivered')
			{
				$orderrows['orderactions'] = '<div class="blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
				$orderrows['escrowtotal'] = '<div><span title="{_funds_released_to_seller}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_blue.png" border="0" alt="" id="" /></span></div>';
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				$orderrows['paystatus'] = '<div><span title="{_escrow_funds_released_on} ' . print_date($orderrows['releasedate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
				$orderrows['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
			}
			// #### buy now escrow cancelled
			else if ($orderrows['status'] == 'cancelled')
			{
				$orderrows['orderactions'] = '<div class="blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div><div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>';
				$orderrows['escrowtotal'] = '<div><span title="{_funds_returned}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_reversed.png" border="0" alt="" id="" /></span></div>';
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 0);
				$orderrows['payment'] = '';
				$orderrows['paystatus'] = '<div><span title="{_funds_returned}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
			}
			// #### offline payment started
			else if ($orderrows['status'] == 'offline')
			{
				$orderrows['escrowtotal'] = '<div><span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span></div>';
				if (strchr($orderrows['buyerpaymethod'], 'gateway'))
				{
					$orderrows['buyerpaymethod'] = '{_' . mb_substr($orderrows['buyerpaymethod'], 8) . '}';
					if ($orderrows['winnermarkedaspaiddate'] == '0000-00-00 00:00:00')
					{
						$orderrows['orderactions'] = '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;id=' . $orderrows['project_id'] . '&amp;orderid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_pay_now}</strong></a></div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td>
</tr>
' . $digitaldownloadaction . '
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
						$orderrows['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
						
					}
					else 
					{
						$orderrows['orderactions'] = '<div class="blue"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a></div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
						$orderrows['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($orderrows['winnermarkedaspaiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
					}
				}
				else
				{
					$orderaction = $orderaction2 = '';
					$orderrows['buyerpaymethod'] = '{' . mb_substr($orderrows['buyerpaymethod'], 8) . '}';
					if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
					{
						$crypted = array(
							'cmd' => 'management',
							'subcmd' => 'markorderaspaid',
							'id' => $orderrows['project_id'],
							'orderid' => $orderrows['orderid']
						);
						$orderrows['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
						$orderaction = '<a href="javascript:void(0)" onclick="return show_prompt_payment_buyer(\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_payment_as_sent}</strong></a>';
						$orderaction2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td></tr>';
					}
					else
					{
						$crypted = array(
							'cmd' => 'management',
							'subcmd' => 'markorderasunpaid',
							'id' => $orderrows['project_id'],
							'orderid' => $orderrows['orderid']
						);
						$orderrows['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($orderrows['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
						$orderaction = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a>';
						$orderaction2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_sent}</a></span></div></td></tr>';
					}
					
					$orderrows['orderactions'] = '<div class="blue">' . $orderaction . '</div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
						<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
' . $orderaction2 . '
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
					unset($crypted);
					$orderrows['orderinfo'] = '<div class="smaller black"><span style="float:left;padding-right:4px;margin-top:-2px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'info_16.png" border="0" alt="" /></span>{_waiting_for_seller_to_confirm_payment}</div>';
				}
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				$orderrows['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
			}
			// #### offline payment completed
			else if ($orderrows['status'] == 'offline_delivered')
			{
				$crypted = array();
				$orderrows['buyerpaymethod'] = '{' . mb_substr($orderrows['buyerpaymethod'], 8) . '}';
				$orderrows['escrowtotal'] = '<span title="{_not_in_use}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/escrow_litegray.png" border="0" alt="" id="" /></span>';
				if ($orderrows['paiddate'] == '0000-00-00 00:00:00')
				{
					$crypted = array(
						'cmd' => 'management',
						'subcmd' => 'markorderaspaid',
						'id' => $orderrows['project_id'],
						'orderid' => $orderrows['orderid']
					);
					$orderrows['paystatus'] = '<div><span title="{_payment} {_unpaid_lower}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy_gray.gif" border="0" alt="" /></span></div>';
					$orderaction = '<a href="javascript:void(0)" onclick="return show_prompt_payment_buyer(\'' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}\')"><strong>{_mark_payment_as_sent}</strong></a>';
					$orderaction2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}">{_view_order_details}</a></span></div></td></tr>';
				}
				else
				{
					$crypted = array(
						'cmd' => 'management',
						'subcmd' => 'markorderasunpaid',
						'id' => $orderrows['project_id'],
						'orderid' => $orderrows['orderid']
					);
					$orderrows['paystatus'] = '<div><span title="{_marked_as_paid_on} ' . print_date($orderrows['paiddate']) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'buy.gif" border="0" alt="" /></span></div>';
					$orderaction = '<a href="' . $ilpage['invoicepayment'] . '?cmd=orderdetail&amp;pid=' . $orderrows['project_id'] . '&amp;oid=' . $orderrows['orderid'] . '&amp;returnurl={pageurl_urlencoded}"><strong>{_view_order_details}</strong></a>';
					$orderaction2 = '<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'"><td><div><span class="blueonly"><a href="' . HTTPS_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '&amp;returnurl={pageurl_urlencoded}">{_unmark_payment_as_sent}</a></span></div></td></tr>';
				}
				$orderrows['orderactions'] = '<div class="blue">' . $orderaction . '</div>' . $feedbackaction . '<div style="padding-top:3px" class="blue"><a href="javascript:void(0)" onmouseover="show_actions_popup(\'' . $orderrows['orderid'] . '\')" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\')">{_more_actions}</a> <span style="padding-left:3px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/expand_hover.png" border="0" alt="" /></span></div>
<div style="display:none;position:absolute;z-index:5000;background-color:#fff;margin-top:1px;margin-left:-90px;background-color:#fff;border:1px solid #ccc;-webkit-box-shadow: #ddd 3px 3px 6px;-moz-box-shadow: #ddd 3px 3px 6px" id="actions_popup_' . $orderrows['orderid'] . '" onmouseover="show_actions_popup_links(\'' . $orderrows['orderid'] . '\');" onmouseout="hide_actions_popup(\'' . $orderrows['orderid'] . '\');"><div class="n"><div class="e"><div class="w"></div></div></div><div>
<table border="0" cellpadding="9" cellspacing="0">
' . $orderaction2 . '
' . $digitaldownloadaction . '
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . $ilpage['selling'] . '?cmd=new-item&amp;cid=' . fetch_auction('cid', $orderrows['project_id']) . '">{_sell_a_similar_item}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div id="sellerwatchlistresponse' . $orderrows['owner_id'] . '"><span class="blueonly">' . (($show['selleraddedtowatchlist']) ? '<a href="' . HTTPS_SERVER . $ilpage['watchlist'] . '?tab=2">{_seller_is_in_favorites}</a>' : '<a href="javascript:void(0)" onclick="add_seller_to_watchlist(\'' . $orderrows['owner_id'] . '\', \'' . $_SESSION['ilancedata']['user']['userid'] . '\', \'' . $orderrows['owner_id'] . '\')">{_add_seller_to_favorites}</a>') . '</span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;searchuser=' . handle_input_keywords(fetch_user('username', $orderrows['owner_id'])) . '&amp;exactname=1">{_view_sellers_other_items}</a></span></div></td>
</tr>
<tr class="alt1" onmouseover="this.className=\'alt2\'" onmouseout="this.className=\'alt1\'">
	<td><div><span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['search'] . '?mode=product&amp;sort=01&amp;q=' . handle_input_keywords($title) . '">{_view_similar_items}</a></span></div></td>
</tr>
</table></div><div class="s"><div class="e"><div class="w"></div></div></div>';
				unset($crypted);
				$orderrows['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
				$orderrows['share'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id'], 1);
				$orderrows['contactseller'] = $ilance->auction->construct_pmb_link($_SESSION['ilancedata']['user']['userid'], $orderrows['owner_id'], $orderrows['project_id']);
			}
			$orderrows['currencyinfo'] = '';
			if ($orderrows['originalcurrencyid'] != $orderrows['convertedtocurrencyid'])
			{
				$orderrows['currencyinfo'] = '<div style="padding-bottom:3px" class="smaller"><strong>{_currency}:</strong> <span title="' . $ilance->currency->currencies[$orderrows['convertedtocurrencyid']]['code'] . ' {_on} ' . $orderrows['orderdate'] . ' = ' . $orderrows['convertedtocurrencyidrate'] . ', ' . $ilance->currency->currencies[$orderrows['originalcurrencyid']]['code'] . ' = ' . $orderrows['originalcurrencyidrate'] . '">' . $ilance->currency->currencies[$orderrows['originalcurrencyid']]['code'] . ' {_to} ' . $ilance->currency->currencies[$orderrows['convertedtocurrencyid']]['code'] . '</span></div>';
			}
			$orderrows['class'] = ($order_count % 2) ? 'alt2' : 'alt1';
			$GLOBALS['show_sellerinfavorites_' . $orderrows['orderid']] = $ilance->watchlist->is_seller_added_to_watchlist($orderrows['owner_id']);
			$GLOBALS['show_digitaldownload_' . $orderrows['orderid']] = false;
			$GLOBALS['show_localpickuponly_' . $orderrows['orderid']] = false;
			$GLOBALS['show_delivered_' . $orderrows['orderid']] = false;
			$GLOBALS['show_shipped_' . $orderrows['orderid']] = false;
			$GLOBALS['show_paymentrequired_' . $orderrows['orderid']] = false;
			$GLOBALS['show_wintype_' . $orderrows['orderid']] = 'fixed';
			$orderrows['format'] = '{_fixed_price}';
			
			($apihook = $ilance->api('buying_management_purchases_end')) ? eval($apihook) : false;
			
			$product_purchases_activity[] = $orderrows;
			$order_count++;
		}
		$show['no_purchase_now_activity'] = false;
	}
	else
	{
		$show['no_purchase_now_activity'] = true;
	}
	$scriptpage = HTTPS_SERVER . $ilpage['buying'] . '?cmd=purchases' . $orderby . $currentdisplayorder . $period;
	$prevnext = print_pagnation($number, $ilconfig['globalfilters_maxrowsdisplay'], intval($ilance->GPC['page']), $counter, $scriptpage);
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>