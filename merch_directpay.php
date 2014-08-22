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
// #### are we logged in ? #############################################
if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	$area_title = '{_access_denied}';
	$page_title = SITE_NAME . ' - {_access_denied}';
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['merch'] . print_hidden_fields(true, array(), true)));
	exit();
}
$ilance->GPC['sellerid'] = fetch_auction('user_id', intval($ilance->GPC['id']));
$ilance->GPC['orderid'] = ((isset($ilance->GPC['orderid']) AND $ilance->GPC['orderid'] > 0) ? intval($ilance->GPC['orderid']) : 0);
if (isset($ilance->GPC['sellerid']) AND $ilance->GPC['sellerid'] > 0 AND $ilance->GPC['sellerid'] == $_SESSION['ilancedata']['user']['userid'])
{
	$area_title = '{_access_denied}';
	$page_title = SITE_NAME . ' - {_access_denied}';
	print_notice($area_title, '{_it_appears_you_are_the_seller_of_this_listing_in_this_case_you_cannot_bid_or_purchase_items_from_your_own_listing}', 'javascript:history.back(1);', '{_back}');
	exit();
}
// #### direct pay handler default for ipn challenge response ##########
$customencrypted = 'ITEMWIN|' . $ilance->GPC['orderid'] . '|' . intval($ilance->GPC['id']);
// #### winning bid amount #############################################
$total = $ilance->bid->fetch_auction_win_amount(intval($ilance->GPC['id']), $ilance->GPC['sellerid'], $_SESSION['ilancedata']['user']['userid']);
// #### listing details ################################################
$itemid = intval($ilance->GPC['id']);
$title = fetch_auction('project_title', intval($ilance->GPC['id']));
$sample = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $itemid, 'thumb', $itemid, 1, '#ccc');
$qty = isset($ilance->GPC['qty']) ? intval($ilance->GPC['qty']) : 1;
$currencyid = fetch_auction('currencyid', intval($ilance->GPC['id']));
// populate $show['multipleorders']
$orderidradios = $ilance->payment->print_orderid_methods($itemid, $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['orderid']);
$hiddenfields = '';
$returnurl = HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded';
// #### build nav crumb ################################################
$navcrumb = array();
$navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management&bidsub=awarded"] = '{_buying_activity}';
$navcrumb[HTTP_SERVER . "$ilpage[merch]?id=" . intval($ilance->GPC['id'])] = $title;
$navcrumb[""] = '{_complete_your_payment_to_seller}';
// #### buy now order details for this payment to seller ###############
if ($ilance->GPC['orderid'] > 0)
{
	$hiddenfields = '<input type="hidden" name="cmd" value="purchase-confirm" /><input type="hidden" name="pid" value="' . $itemid . '" /><input type="hidden" name="qty" value="' . $qty . '" /><input type="hidden" name="orderid" value="' . intval($ilance->GPC['orderid']) . '" />';
	// #### direct pay gateway ipn response challenge ##############
	$customencrypted = 'BUYNOW|' . intval($ilance->GPC['orderid']) . '|' . intval($ilance->GPC['id']);
	// #### set default url to send ipn handler ############################
	$returnurl = HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow';
	$sql = $ilance->db->query("
		SELECT status, buyerpaymethod, buyershipcost, buyershipperid, qty, amount
		FROM " . DB_PREFIX . "buynow_orders
		WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
			AND project_id = '" . intval($ilance->GPC['id']) . "'
			AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$buyerpaymethod = $res['buyerpaymethod'];
		$qty = $res['qty'];
		$buyershipcost['total'] = $buyershipcost['amount'] = $res['buyershipcost'];
		if (empty($ilance->GPC['paymethod']) AND !empty($res['buyerpaymethod']))
		{
			$ilance->GPC['paymethod'] = $res['buyerpaymethod'];
		}
		if ($res['status'] != 'offline')
		{
			$area_title = '{_your_order_for_this_purchase_is_complete}';
			$page_title = SITE_NAME . ' - {_your_order_for_this_purchase_is_complete}';
			print_notice('{_your_order_for_this_purchase_is_complete}', '{_the_order_id_for_this_purchase_has_been_completed}', "javascript: history.go(-1)", '{_back}');
			exit();
		}
	}
	// #### no buy now order information found #############################
	else
	{
		$area_title = '{_your_order_for_this_purchase_is_complete}';
		$page_title = SITE_NAME . ' - {_your_order_for_this_purchase_is_complete}';
		print_notice('{_your_order_for_this_purchase_is_complete}', '{_the_order_id_for_this_purchase_has_been_completed}', "javascript: history.go(-1)", '{_back}');
		exit();
	}

	// #### build nav crumb ################################################
	$navcrumb = array();
	$navcrumb[HTTP_SERVER . "$ilpage[buying]?cmd=management"] = '{_buying_activity}';
	$navcrumb[HTTPS_SERVER . "$ilpage[escrow]?cmd=management&bidsub=buynow-escrow"] = '{_buy_now_manager}';
	$navcrumb[HTTP_SERVER . "$ilpage[merch]?id=" . intval($ilance->GPC['id'])] = $title;
	$navcrumb[""] = '{_complete_your_payment_to_seller}';
	$buyershipperid = 0;
	if (!empty($ilance->GPC['paymethod']))
	{
		$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0) ? intval($ilance->GPC['shipperid']) : 0);
		if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
		{
			$buyershipperid = $res['buyershipperid'];
		}
		$buyershipcost = $ilance->shipping->fetch_ship_cost_by_shipperid($ilance->GPC['id'], $buyershipperid, $qty);
		$newtotal = $total + $buyershipcost['total'];
		if ($buyershipperid > 0)
		{
			$show['shippingcharges'] = true;
		}
		else
		{
			$show['shippingcharges'] = false;
		}
		if ($buyershipperid > 0 AND $buyershipcost['total'] > 0)
		{
			$total = $newtotal;
		}
	}
	$buyerpaymethod = isset($ilance->GPC['paymethod']) ? $ilance->GPC['paymethod'] : '';
	if (empty($buyerpaymethod) AND $buyershipperid == 0)
	{
		refresh(HTTP_SERVER . $ilpage['merch'] . '?cmd=directpay&subcmd=choose&id=' . intval($ilance->GPC['id']));
		exit();
	}
	if ($buyerpaymethod == 'escrow')
	{
		refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
		exit();
	}
	else
	{
		if (strchr($buyerpaymethod, 'offline'))
		{
			refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow');
			exit();
		}
	}
}
// #### winning bid details for this payment to seller #################
else
{
	$orderids = array();
	$pid = intval($ilance->GPC['id']);
	$methodscount = $ilance->payment->print_payment_methods($pid, false, true);
	$shippercount = $ilance->shipping->print_shipping_methods($pid, $qty, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
	$hiddenfields = '<input type="hidden" name="cmd" value="directpay" /><input type="hidden" name="id" value="' . $pid . '" />';
	$hiddenfields .= ((isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl'])) ? '<input type="hidden" name="returnurl" value="' . urlencode($ilance->GPC['returnurl']) . '" />' : '<input type="hidden" name="returnurl" value="' . urlencode($returnurl) . '" />');
	// #### check if our bid exists within the bids table ##########
	$sql = $ilance->db->query("
		SELECT b.bid_id, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, b.bidamount, s.ship_handlingtime, s.ship_method
		FROM " . DB_PREFIX . "project_bids b
		LEFT JOIN " . DB_PREFIX . "projects_shipping s ON b.project_id = s.project_id
		WHERE b.state = 'product'
			AND b.bidstatus = 'awarded'
			AND b.project_id = '" . $pid . "'
			AND b.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			AND b.project_user_id = '" . intval($ilance->GPC['sellerid']) . "'
	", 0, null, __FILE__, __LINE__);
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
		$buyerpaymethod = $res['buyerpaymethod'];
		if (!empty($buyerpaymethod) AND !isset($ilance->GPC['paymethod']))
		{
			$ilance->GPC['paymethod'] = $buyerpaymethod;
		}
		if ($res['buyershipperid'] > 0 AND $res['buyershipcost'] > 0)
		{
			$total = ($total + $res['buyershipcost']);
		}
		if (!empty($ilance->GPC['paymethod']))
		{
			$buyerpaymethod = $ilance->GPC['paymethod'];
			$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0) ? intval($ilance->GPC['shipperid']) : 0);
			if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
			{
				 $buyershipperid = $res['buyershipperid'];
			}
			$buyershipcost = $ilance->shipping->fetch_ship_cost_by_shipperid($pid, $buyershipperid, $qty);
			if ($buyerpaymethod == 'escrow')
			{
				// #### check if escrow account was already created
				$sql = $ilance->db->query("
					SELECT escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, date_paid, date_released, date_cancelled, escrowamount, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, qty, buyerfeedback, sellerfeedback, status, sellermarkedasshipped, sellermarkedasshippeddate
					FROM " . DB_PREFIX . "projects_escrow
					WHERE project_id = '" . $pid . "'
						AND bid_id = '" . $res['bid_id'] . "'
				");
				if ($ilance->db->num_rows($sql) == 0)
				{
					 // #### do shipping fees apply?
					$highestbid = $res['bidamount'];
					$totalescrowamount = $highestbid;
					$shippinginformation = $ilance->currency->format($res['buyershipcost'], $currencyid);
					// #### create new item escrow account for this winning bidder
					list($feenotax, $tax, $fee) = $ilance->escrow_fee->fetch_merchant_escrow_fee_plus_tax($ilance->GPC['sellerid'], $totalescrowamount);
					list($fee2notax, $tax2, $fee2) = $ilance->escrow_fee->fetch_product_bidder_escrow_fee_plus_tax($_SESSION['ilancedata']['user']['userid'], $totalescrowamount);
					// amount to forward plus the merchant fee to fund escrow (including any taxes if applicable)
					$totalescrowamount = ($totalescrowamount + $res['buyershipcost']);
					// #### create escrow invoice #####################################
					$escrow_invoice_id = $ilance->accounting->insert_transaction(
						0,
						$pid,
						0,
						$_SESSION['ilancedata']['user']['userid'],
						0,
						0,
						0,
						'{_escrow_payment_forward}' . ': {_item_id} #' . $pid . ': ' . $title,
						sprintf("%01.2f", $totalescrowamount),
						'',
						'unpaid',
						'escrow',
						'account',
						DATETIME24H,
						DATEINVOICEDUE,
						'',
						'{_additional_shipping_fees}' . ': ' . $shippinginformation,
						0,
						0,
						1
					);

					// create product escrow account
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "projects_escrow
						(escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
						VALUES(
						NULL,
						'" . $res['bid_id'] . "',
						'" . $pid . "',
						'" . $escrow_invoice_id . "',
						'" . intval($ilance->GPC['sellerid']) . "',
						'" . $_SESSION['ilancedata']['user']['userid'] . "',
						'" . DATETIME24H . "',
						'" . sprintf("%01.2f", $highestbid) . "',
						'" . sprintf("%01.2f", $res['buyershipcost']) . "',
						'" . sprintf("%01.2f", $totalescrowamount) . "',
						'" . sprintf("%01.2f", $fee) . "',
						'" . sprintf("%01.2f", $fee2) . "',
						'0',
						'0',
						'0',
						'0',
						'pending')
					", 0, null, __FILE__, __LINE__);
					$escrow_id = $ilance->db->insert_id();
					// associate escrow to listing
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET escrow_id = '" . $escrow_id . "',
						haswinner = '1',
						winner_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						WHERE project_id = '" . $pid . "'
					", 0, null, __FILE__, __LINE__);
					// track products purchased
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productawards = productawards + 1
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					");
					// track products sold
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productsold = productsold + 1
						WHERE user_id = '" . intval($ilance->GPC['sellerid']) . "'
					");
					// #### update winning bidders default pay method to escrow
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET buyerpaymethod = 'escrow',
						winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
						WHERE bid_id = '" . $res['bid_id'] . "'
							 AND project_id = '" . $pid . "'
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_realtimebids
						SET buyerpaymethod = 'escrow',
						winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
						WHERE bid_id = '" . $res['bid_id'] . "'
							 AND project_id = '" . $pid . "'
					", 0, null, __FILE__, __LINE__);
					$existing = array(
						'{{project_title}}' => $title,
						'{{project_id}}' => $res_rfp['project_id'],
						'{{owner}}' => fetch_user('username', $ilance->GPC['sellerid']),
						'{{owneremail}}' => fetch_user('email', $ilance->GPC['sellerid']),
						'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $pid,
						'{{bidamount}}' => $ilance->currency->format($bidamount, $currencyid),
						'{{shippingcost}}' => $ilance->currency->format($res['buyershipcost'], $currencyid),
						'{{shippingservice}}' => $ilance->shipping->print_shipping_partner($res['buyershipperid']),
						'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
						'{{totalamount}}' => $ilance->currency->format($totalescrowamount, $currencyid),
						'{{winningbidder}}' => $_SESSION['ilancedata']['user']['username'],
						'{{winningbidderemail}}' => $_SESSION['ilancedata']['user']['email'],
						'{{paymethod}}' => SITE_NAME . ' ' . '{_escrow}',
						'{{buyerfee}}' => $ilance->currency->format($fee2),
						'{{sellerfee}}' => $ilance->currency->format($fee),
					);
					// email owner
					$ilance->email->mail = fetch_user('email', $ilance->GPC['sellerid']);
					$ilance->email->slng = fetch_user_slng($ilance->GPC['sellerid']);
					$ilance->email->get('product_auction_expired_via_cron_owner');
					$ilance->email->set($existing);
					$ilance->email->send();
					// email winning bidder
					$ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
					$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
					$ilance->email->get('product_auction_expired_via_cron_winner');
					$ilance->email->set($existing);
					$ilance->email->send();
					// email admin
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('product_auction_expired_via_cron_admin');
					$ilance->email->set($existing);
					$ilance->email->send();
				}
				// #### winning bidder redirect to item escrow activity
				refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
				exit();
			 }
			else
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_bids
					SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
					buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
					buyershipperid = '" . intval($buyershipperid) . "'
					WHERE bid_id = '" . $res['bid_id'] . "'
					LIMIT 1
				");
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_realtimebids
					SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
					buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
					buyershipperid = '" . intval($buyershipperid) . "'
					WHERE bid_id = '" . $res['bid_id'] . "'
					LIMIT 1
				");
				// #### winning bidder redirected to i've won items activity menu
				if ($methodscount == 1 AND $shippercount == 1)
				{
					if (strchr($ilance->GPC['paymethod'], 'offline') OR strchr($ilance->GPC['paymethod'], 'ccgateway'))
					{
						refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
						exit();
					}
				}
			}
		}
		if (empty($res['buyerpaymethod']))
		{
			if ($methodscount == 1)
			{
				$ilance->GPC['paymethod'] = $ilance->payment->print_payment_method_title($pid);
				$hiddenfields .= '<input type="hidden" name="paymethod" value="' . handle_input_keywords($ilance->GPC['paymethod']) . '" />';
				if (strchr($ilance->GPC['paymethod'], 'offline') OR strchr($ilance->GPC['paymethod'], 'gateway'))
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "'
						WHERE project_id = '" . $pid . "'
							AND bid_id = '" . $res['bid_id'] . "'
							AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND project_user_id = '" . $ilance->GPC['sellerid'] . "'
					");
					if (strchr($ilance->GPC['paymethod'], 'offline'))
					{
						refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
						exit();
					}
				}
				else if ($ilance->GPC['paymethod'] == 'escrow')
				{
				       // #### winning bidder redirect to item escrow activity
				       refresh(HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
				       exit();
				}
			}
			else if ($methodscount > 1)
			{
				$area_title = '{_confirm_payment_method}';
				$page_title = SITE_NAME . ' - {_confirm_payment_method}';
				$navcrumb = array();
				if ($ilconfig['globalauctionsettings_seourls'])
				{
					$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = '{_buy}';
				}
				else
				{
					$navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
				}
				$navcrumb["$ilpage[merch]?id=" . $pid] = fetch_auction('project_title', $pid);
				$navcrumb[""] = '{_confirm_payment_method}';
				// #### radio input for buyers payment decision ################
				$paymethodsradios = $ilance->payment->print_payment_methods($pid, true);
				// #### radio input for buyers payment decision ################
				$shippingradios = $ilance->shipping->print_shipping_methods($pid, $qty, true, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
				$shippingradioscount = $ilance->shipping->print_shipping_methods($pid, $qty, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
				$shippingservice = '';
				$shipperid = 0;
				$days = 3;
				$ilance->shipping->print_shipping_methods($pid, $qty, false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
				if ($shippingradioscount == 1 AND $shipperidrow > 0)
				{
					$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '$pid'", "ship_service_$shipperidrow");
					$shippingservice = $ilance->shipping->print_shipping_partner($shipperid);
					$days = $res['ship_handlingtime'];
				}
				$pprint_array = array('onsubmit','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','category','subcategory');

				($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;

				$ilance->template->fetch('main', 'listing_payment_selection.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_loop('main', 'paymentoptions');
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
		else
		{
			if ($res['buyerpaymethod'] == 'escrow')
			{
				refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
				exit();
			}
			else
			{
				if ($methodscount == 1 AND $shippercount == 1)
				{
					if (strchr($res['buyerpaymethod'], 'offline'))
					{
						refresh(HTTP_SERVER . $ilpage['buying'] . '?cmd=management&bidsub=awarded');
						exit();
					}
				}
			}
		}
	}
	// #### check if our purchase was made via buy now #############
	else
	{
		$sql = $ilance->db->query("
			SELECT b.orderid, b.status, b.buyerpaymethod, b.buyershipcost, b.buyershipperid, s.ship_handlingtime, s.ship_method
			FROM " . DB_PREFIX . "buynow_orders b
			LEFT JOIN " . DB_PREFIX ."projects_shipping s ON b.project_id = s.project_id
			WHERE b.project_id = '" . $pid . "'
				AND b.buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND b.owner_id = '" . intval($ilance->GPC['sellerid']) . "'
				AND b.paiddate = '0000-00-00 00:00:00'
		 ", 0, null, __FILE__, __LINE__);
		 if ($ilance->db->num_rows($sql) > 0)
		 {
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$show['localpickuponly'] = ($res['ship_method'] == 'localpickup') ? true : false;
				if (!empty($res['buyerpaymethod']))
				{
					$ilance->GPC['paymethod'] = $res['buyerpaymethod'];
					$buyershipperid = ((isset($ilance->GPC['shipperid']) AND $ilance->GPC['shipperid'] > 0) ? intval($ilance->GPC['shipperid']) : $res['buyershipperid']);
					$ilance->GPC['shipperid'] = $buyershipperid;
					if ($buyershipperid == 0 AND $res['buyershipperid'] > 0)
					{
						$buyershipperid = $res['buyershipperid'];
					}
					$buyershipcost = $ilance->shipping->fetch_ship_cost_by_shipperid($pid, $buyershipperid, $qty);
					if ($show['localpickuponly'])
					{
						$buyershipperid = 0;
						$buyershipcost['total'] = 0;
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "buynow_orders
						SET buyerpaymethod = '" . $ilance->db->escape_string($ilance->GPC['paymethod']) . "',
						buyershipcost = '" . sprintf("%01.2f", $buyershipcost['total']) . "',
						buyershipperid = '" . intval($buyershipperid) . "'
						WHERE project_id = '" . intval($pid) . "'
							AND buyer_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND owner_id = '" . intval($ilance->GPC['sellerid']) . "'
							AND orderid = '" . $res['orderid'] . "'
					", 0, null, __FILE__, __LINE__);
					$orderids[] = $res['orderid'];
				}
				$buyerpaymethod = $res['buyerpaymethod'];
				if ($res['buyershipperid'] > 0 AND $res['buyershipcost'] > 0 AND $show['localpickuponly'] == false)
				{
					$total = ($total + $res['buyershipcost']);
				}
			}
		 }
		 else
		 {
			// check for seo?
			refresh(HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($ilance->GPC['id']));
			exit();
		 }
	}
	// #### we've made it this far because the buyer wishes to pay the seller via major gateway (Paypal, Moneybookers, etc)
	if (count($orderids) == 1)
	{
		$ilance->GPC['orderid'] = $orderids[0];
		$hiddenfields .= '<input type="hidden" name="orderid" value="' . $ilance->GPC['orderid'] . '" />';
	}
	// #### buyer choosing payment method ##########################
	if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'choose')
	{
		$area_title = '{_confirm_payment_method}';
		$page_title = SITE_NAME . ' - {_confirm_payment_method}';
		$navcrumb = array();
		if ($ilconfig['globalauctionsettings_seourls'])
		{
			$navcrumb[HTTP_SERVER . print_seo_url($ilconfig['listingsidentifier'])] = '{_buy}';
		}
		else
		{
			$navcrumb["$ilpage[merch]?cmd=listings"] = '{_buy}';
		}
		$navcrumb["$ilpage[merch]?id=" . $pid] = fetch_auction('project_title', $pid);
		$navcrumb[""] = '{_confirm_payment_method}';
                $sql = $ilance->db->query("
                        SELECT s.ship_method 
                        FROM " . DB_PREFIX . "projects p
                        LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
                        WHERE p.project_id = '" . intval($pid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $show['digital_download_delivery'] = ($res['ship_method'] == 'digital') ? true : false;
		$paymentmethodradios_js = $shippingradios_js = '';
		$paymethodsradios = $ilance->payment->print_payment_methods($pid, true);
		$shippingradios = $ilance->shipping->print_shipping_methods($pid, $qty, true, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		$shippingradioscount = $ilance->shipping->print_shipping_methods($pid, $qty, false, true, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
		if ($shippingradioscount == 0)
		{
			$shippingradios_js = 'haveerror = false;';
		}
		$days = 3;
		$shipperid = 0;
		$shippingservice = '';
		$orderidradios = $ilance->payment->print_orderid_methods($pid, $_SESSION['ilancedata']['user']['userid']);
		if (isset($show['multipleorders']) AND $show['multipleorders'] == true)
		{
			$onsubmit = 'return validate_all(this);';
			$headinclude .= '
<script type="text/javascript">
<!--
function validate_order_id()
{
';
			for ($x = 1; $x < $orderidradiocount; $x++)
			{
				$headinclude .= '
	if (fetch_js_object(\'orderid_' . $x . '\').checked == true)
	{
		return(true);	
	}
';
			}
			$headinclude .= '
	alert_js(phrase[\'_you_forgot_to_select_an_order_to_update_this_is_required\']);
	return(false);					
}
function validate_paymethod()
{
	return(true);
}
function validate_ship_service()
{
	return(true);
}
function validate_all(formobj)
{
	return validate_order_id() && validate_paymethod() && validate_ship_service();
}
//-->
</script>';
		}
		else
		{
			$onsubmit = 'return validate_all()';
			$headinclude .= '<script type="text/javascript">
<!--
function validate_paymethod()
{
	var haveerror = true;
	' . $paymentmethodradios_js . '
	if (haveerror == true)
	{
		alert_js(phrase[\'_please_select_one_payment_method_radio\']);
		return(false);
	}
	return(true);
}
function validate_ship_service()
{
	var haveerror = true;
	' . $shippingradios_js . '
	if (haveerror == true)
	{
		alert_js(phrase[\'_please_select_one_shipping_method_radio\']);
		return(false);
	}
	return(true);
}
function validate_all()
{
	return validate_paymethod() && validate_ship_service();
}
//-->
</script>';
		}
		if ($shippercount == 1)
		{
			$ilance->shipping->print_shipping_methods($pid, $qty, false, false, false, $_SESSION['ilancedata']['user']['countryid'], $_SESSION['ilancedata']['user']['slng']);
			if ($shipperidrow > 0)
			{
				$shipperid = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping_destinations", "project_id = '" . $pid . "'", "ship_service_$shipperidrow");
				$shippingservice = $ilance->shipping->print_shipping_partner($shipperid);
				$days = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $pid . "'", "ship_handlingtime");
			}
		}
		$pprint_array = array('onsubmit','orderidradios','shipperid','days','shippingservice','shippingradios','hiddenfields','pid','qty','paymethodsradios','paymethods','returnurl','tax','paymethod','fees','digitalfile','cb_shipping_address_required1','cb_shipping_address_required0','encrypted','samount','amount_formatted','total','shipping_address_pulldown','forceredirect','payment_method_pulldown','attachment','project_id','seller_id','buyer_id','user_cookie','project_title','seller','qty','topay','amount','remote_addr','rid','category','subcategory');

		($apihook = $ilance->api('listing_payment_selection_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'listing_payment_selection.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'paymentoptions');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	if ($buyerpaymethod == 'escrow')
	{
		refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=product-escrow');
		exit();
	}
	else if (strchr($buyerpaymethod, 'offline'))
	{
		if (isset($ilance->GPC['returnurl']) AND !empty($ilance->GPC['returnurl']))
		{
			refresh(urldecode($ilance->GPC['returnurl']));
			exit();
		}
		if (isset($returnurl) AND !empty($returnurl))
		{
			refresh(urldecode($returnurl));
			exit();
		}
		else
		{
			refresh(HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&bidsub=buynow-escrow');
			exit();
		}
	}
}
$area_title = '{_complete_your_payment_to_seller}';
$page_title = SITE_NAME . ' - {_complete_your_payment_to_seller}';
$totalformatted = $ilance->currency->format($total, $currencyid);
$totalshippingformatted = $ilance->currency->format($buyershipcost['total'], $currencyid);
if (empty($buyerpaymethod) AND isset($ilance->GPC['paymethod']) AND !empty($ilance->GPC['paymethod']))
{
	$buyerpaymethod = $ilance->GPC['paymethod'];
}

($apihook = $ilance->api('print_notice_direct_payment_start')) ? eval($apihook) : false;

$cc_type_pulldown = '';
$ship_method_val = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $itemid . "'", "ship_method");
$no_shipping = (isset($ship_method_val) AND $ship_method_val == 'flatrate') ? 0 : 1;
// #### buyer's selected payment method ################################
switch ($buyerpaymethod)
{
	case 'ccgateway_paypal_pro':
	{
		$conf = array(
			'api_username' => trim($ilconfig['paypal_pro_username']), 
			'api_password' => trim($ilconfig['paypal_pro_password']), 
			'api_signature' => trim($ilconfig['paypal_pro_signature']), 
			'use_proxy' => '', 
			'proxy_host' => '', 
			'proxy_port' => '', 
			'return_url' => HTTP_SERVER . $ilpage['accounting'], 
			'cancel_url' => HTTP_SERVER . $ilpage['accounting']
		);
		$cc_type_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'creditcard_type');
		list($currency, $total) = $ilance->accounting->check_currency('paypal_pro', $currencyid, $total);
		$totalformatted = $ilance->currency->format($total, $ilance->currency->currencies[$currency]['currency_id']);
		$ilance->paypal_pro = construct_object('api.paypal_pro', $conf, $ilconfig['paypal_pro_sandbox']);
		$formstart = $ilance->paypal_pro->print_direct_payment_form($itemid, $total, $qty, $title, $ilance->GPC['orderid'], $buyershipperid, $customencrypted);
		$paymenticon = '';
		$show['no_icon'] = true;
		$show['ccform'] = true;
		break;
	}
	case 'gateway_platnosci':
	{
		list($currency, $total) = $ilance->accounting->check_currency('platnosci', $currencyid, $total);
		$totalformatted = $ilance->currency->format($total, $ilance->currency->currencies[$currency]['currency_id']);
		$title = fetch_auction('project_title', $itemid);
		$ilance->platnosci = construct_object('api.platnosci');
		$formstart = $ilance->platnosci->print_direct_payment_form($total, $title, $currency, $customencrypted, $returnurl);
		$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/platnosci.gif" border="0" alt="" id="" />';
		break;
	}
	case 'gateway_paypal':
	{
		$vars['pid'] = $itemid;
		list($currency, $total) = $ilance->accounting->check_currency('paypal', $currencyid, $total);
		$totalformatted = $ilance->currency->format($total, $ilance->currency->currencies[$currency]['currency_id']);
		$ilance->paypal = construct_object('api.paypal');
		$paye = $ilance->payment->fetch_payment_method_email($itemid, 'paypal');
		$formstart = $ilance->paypal->print_direct_payment_form($total, $title, $paye, $currency, $customencrypted, $returnurl, $vars, $no_shipping);
		$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/paypal.gif" border="0" alt="" id="" />';
		break;
	}
	case 'gateway_moneybookers':
	{
		list($currency, $total) = $ilance->accounting->check_currency('moneybookers', $currencyid, $total);
		$totalformatted = $ilance->currency->format($total, $ilance->currency->currencies[$currency]['currency_id']);
		$ilance->moneybookers = construct_object('api.moneybookers');
		$paye = $ilance->payment->fetch_payment_method_email($itemid, 'moneybookers');
		$formstart = $ilance->moneybookers->print_direct_payment_form($total, $title, $paye, $currency, $customencrypted, $returnurl);
		$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/moneybookers.gif" border="0" alt="" id="" />';
		break;
	}
	case 'gateway_cashu':
	{
		list($currency, $total) = $ilance->accounting->check_currency('cashu', $currencyid, $total);
		$totalformatted = $ilance->currency->format($total, $ilance->currency->currencies[$currency]['currency_id']);
		$ilance->cashu = construct_object('api.cashu');
		$paye = $ilance->payment->fetch_payment_method_email($itemid, 'cashu');
		$formstart = $ilance->cashu->print_direct_payment_form($total, $title, $paye, $currency, $customencrypted, $returnurl);
		$paymenticon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'payment/cashu.gif" border="0" alt="" id="" />';
		break;
	}
}

($apihook = $ilance->api('print_notice_direct_payment_options_end')) ? eval($apihook) : false;

$formend = '</form>';
$pprint_array = array('totalshippingformatted','cc_type_pulldown','sample','itemid','title','totalformatted','paymenticon','orderid','formstart','formend','url','country_pulldown','category','subcategory','filehash','max_filesize','attachment_style','user_id','state','catid','subcatid','currency','datetime_now','project_id','category_id');

($apihook = $ilance->api('print_notice_direct_payment_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'print_notice_direct_payment.html');
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>