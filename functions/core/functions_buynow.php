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
* Global buy now payment functions for iLance
*
* @package      iLance\Global\BuyNow
* @version      4.0.0.8059
* @author       ILance
*/
if (!defined('LOCATION'))
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

/*
* Function to process a buy now payment
*
* @param       array          encrypted url with key value pairs
* @param       string         short language identifier (default eng)
* @param       boolean        silent mode (default false)
* @param       boolean        only return order id (default false)
*
* @return      string         Returns HTML formatted notice of buy now payment
*/
function process_buy_now_payment($uncrypted = array(), $slng = 'eng', $silentmode = false, $returnorderid = false)
{
	global $ilance, $ilconfig, $show, $ilpage;
	$ilance->categories->build_array('product', $slng, 0, true);
	$success = 0;
	$sql = $ilance->db->query("SELECT status, buynow_qty FROM " . DB_PREFIX . "projects WHERE project_id = '" . $uncrypted['project_id'] . "'");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	if ($res['status'] != 'open' AND $res['buynow_qty'] > 0)
	{
		if ($silentmode)
		{
			if ($returnorderid)
			{
				return false . '|0';
			}
			return false . '|{_this_listing_has_ended}';
		}
		$area_title = '{_access_denied}';
		$page_title = SITE_NAME . ' - {_access_denied}';
		print_notice('{_access_denied}', '{_this_listing_has_ended}', 'javascript:history.back(2);', '{_back}');
		exit();
	}
	// #### escrow payment method mode #####################################
	if ($ilconfig['escrowsystem_enabled'] AND isset($uncrypted['paymethod']) AND $uncrypted['paymethod'] == 'escrow')
	{
		// #### instant purchase via online account balance only #######
		if (isset($uncrypted['account_id']) AND $uncrypted['account_id'] == 'account')
		{
			$uncrypted['shipping_address_id'] = isset($uncrypted['shipping_address_id']) ? intval($uncrypted['shipping_address_id']) : '';
			$uncrypted['shipperid'] = isset($uncrypted['shipperid']) ? intval($uncrypted['shipperid']) : '';
			$uncrypted['buyershipcost'] = isset($uncrypted['buyershipcost']) ? sprintf("%01.2f", $uncrypted['buyershipcost']) : '';
			$success = $ilance->escrow_buynow->instant_purchase_now('account', $uncrypted['project_id'], $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $_SESSION['ilancedata']['user']['userid'], $uncrypted['shipping_address_required'], $uncrypted['shipping_address_id'], $uncrypted['account_id'], '{_account_balance}', $uncrypted['buyershipcost'], $uncrypted['shipperid']);
			if ($success[0])
			{
				($apihook = $ilance->api('merch_process_buy_now_payment_online_balance_success')) ? eval($apihook) : false;
				
				if ($silentmode)
				{
					if ($returnorderid)
					{
						return true . '|' . $success[1];
					}
					return true . '|' . $success[1];
				}
				// #### increase product sold for buyer
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET productawards = productawards + '" . $uncrypted['qty'] . "'
					WHERE user_id = '" . $uncrypted['buyer_id'] . "' 
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				// #### increase product sold for seller
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET productsold = productsold + '" . $uncrypted['qty'] . "'
					WHERE user_id = '" .  $uncrypted['seller_id'] . "' 
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				refresh(HTTPS_SERVER . $ilpage['merch'] . '?cmd=_escrow_paid_return');
				exit();
			}
			else
			{
				if ($silentmode)
				{
					if ($returnorderid)
					{
						return false . '|0';
					}
					return false . '|{_invoice_payment_warning_insufficient_funds}';
				}
				$area_title = '{_no_funds_available_in_online_account}';
				$page_title = SITE_NAME . ' - {_no_funds_available_in_online_account}';
				print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}', $ilpage['accounting'], '{_my_account}');
				exit();
			}
		}
		// #### some error has occured #################################
		else
		{
			if ($silentmode)
			{
				if ($returnorderid)
				{
					return false . '|0';
				}
				return false . '|Payment method error';
			}
			refresh($ilpage['merch'] . '?id=' . $uncrypted['project_id'], $ilpage['merch'] . '?id=' . $uncrypted['project_id']);
			exit();
		}
	}
	else
	{
		// #### credit card payment from buyer to site admin directly ##
		if (isset($uncrypted['ccsubmit']) AND $uncrypted['ccsubmit'] == '1')
		{
			$uncrypted['buyershipcost'] = $uncrypted['shipperid'] = '';
			$uncrypted['project_id'] = $uncrypted['project_id'];
			$buyershipcost = $ilance->shipping->fetch_ship_cost_by_shipperid($uncrypted['project_id'], $uncrypted['buyershipperid'], $uncrypted['qty']);
			$auction_currency = fetch_auction('currencyid', $uncrypted['project_id']);
			$custom = isset($uncrypted['custom']) ? urldecode($uncrypted['custom']) : '';
			$custom = explode('|', $custom);
			$uncrypted['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
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
			$ilance->paypal_pro = construct_object('api.paypal_pro', $conf, $ilconfig['paypal_pro_sandbox']);
			$ilance->paypal_pro->ip_address = $_SERVER['REMOTE_ADDR'];
			$total = floatval($_POST['total']);
			$fee = round(($total * $ilconfig['paypal_pro_transaction_fee']) + $ilconfig['paypal_pro_transaction_fee2'], 2);
			$total = $total + $fee;
			// totals
			$ilance->paypal_pro->amount_total = $total;
			$ilance->paypal_pro->amount_shipping = $buyershipcost['total'];
			//$ilance->paypal_pro->currency_code = $ilance->currency->currencies[$auction_currency]['code'];
			// card
			$ilance->paypal_pro->credit_card_number = $_POST['creditcard_number'];
			$ilance->paypal_pro->credit_card_type = ucfirst($_POST['creditcard_type']);
			$ilance->paypal_pro->cvv2_code = $_POST['creditcard_cvv2'];
			$ilance->paypal_pro->expire_date = $_POST['creditcard_month'] . $_POST['creditcard_year'];
			// billing
			$ilance->paypal_pro->first_name = $_POST['creditcard_firstname'];
			$ilance->paypal_pro->last_name = $_POST['creditcard_lastname'];
			$ilance->paypal_pro->address1 = $_POST['creditcard_billing'];
			$ilance->paypal_pro->address2 = $_POST['creditcard_billing'];
			$ilance->paypal_pro->city = !empty($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : $_POST['creditcard_billing'];
			$ilance->paypal_pro->state = $_SESSION['ilancedata']['user']['state'];
			$ilance->paypal_pro->postal_code = $_POST['creditcard_postal'];
			$ilance->paypal_pro->phone_number = $_SESSION['ilancedata']['user']['phone'];
			$ilance->paypal_pro->country_code = $_SESSION['ilancedata']['user']['countryshort'];
			// shipping
			$ilance->paypal_pro->email = $_SESSION['ilancedata']['user']['email'];
			$ilance->paypal_pro->shipping_name = $_SESSION['ilancedata']['user']['firstname'] . ' ' . $_SESSION['ilancedata']['user']['lastname'];
			$ilance->paypal_pro->shipping_address1 = !empty($_SESSION['ilancedata']['user']['address']) ? $_SESSION['ilancedata']['user']['address'] : $_POST['creditcard_billing'];
			$ilance->paypal_pro->shipping_address2 = !empty($_SESSION['ilancedata']['user']['address2']) ? $_SESSION['ilancedata']['user']['address2'] : $_POST['creditcard_billing'];
			$ilance->paypal_pro->shipping_city = !empty($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : $_POST['creditcard_billing'];
			$ilance->paypal_pro->shipping_state = $_SESSION['ilancedata']['user']['state'];
			$ilance->paypal_pro->shipping_postal_code = $_SESSION['ilancedata']['user']['postalzip'];
			$ilance->paypal_pro->shipping_phone_number = $_SESSION['ilancedata']['user']['phone'];
			$ilance->paypal_pro->shipping_country_code = $_SESSION['ilancedata']['user']['countryshort'];
			// Add Order Items (NOT required) - Name, Number, Qty, Tax, Amt
			// Repeat for each item needing to be added
			$amount = round(($total - $ilance->paypal_pro->amount_shipping) / intval($_POST['qty']), 2);
			$ilance->paypal_pro->addItem($_POST['title'], $_POST['project_id'], $_POST['qty'], 0, $amount);
			$response = $ilance->paypal_pro->DoDirectPayment();
			if ($response AND isset($ilance->paypal_pro->Response['ACK']) AND strtoupper($ilance->paypal_pro->Response['ACK']) == 'SUCCESS')
			{
				$uncrypted['orderid'] = isset($uncrypted['orderid']) ? intval($uncrypted['orderid']) : 0;
				if (!empty($uncrypted['paymentlogic']) AND $uncrypted['paymentlogic'] == 'BUYNOW')
				{
					// update our buy now purchase as being paid in full
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "buynow_orders
						SET paiddate = '" . DATETIME24H . "',
						winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = '{_paypal_pro}'
						WHERE orderid = '" . intval($uncrypted['orderid']) . "'
							AND project_id = '" . intval($uncrypted['project_id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase purchases for buyer
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productawards = productawards + '".$uncrypted['qty']."'
						WHERE user_id = '" . $uncrypted['buyer_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase sales for seller
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productsold = productsold + '".$uncrypted['qty']."'
						WHERE user_id = '" .  $uncrypted['seller_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				else 
				{
					// update our listing as the buyer paying the seller in full
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = '{_paypal_pro}'
						WHERE project_id = '" . intval($uncrypted['project_id']) . "'
							AND bidstatus = 'awarded'
							AND state = 'product'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_realtimebids
						SET winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = '{_paypal_pro}'
						WHERE project_id = '" . intval($uncrypted['project_id']) . "'
							AND bidstatus = 'awarded'
							AND state = 'product'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase buys for buyer
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productawards = productawards + '".$uncrypted['qty']."'
						WHERE user_id = '" . $uncrypted['buyer_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase sales for seller
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productsold = productsold + '".$uncrypted['qty']."'
						WHERE user_id = '" .  $uncrypted['seller_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
				if ($silentmode)
				{
					if ($returnorderid)
					{
						return true . '|' . $uncrypted['orderid'];
					}
					return true . '|' . $uncrypted['orderid'];
				}
				$area_title = '{_congratulations_you_purchased_this_item}';
				$page_title = SITE_NAME . ' - {_congratulations_you_purchased_this_item}';
				refresh(HTTP_SERVER . $ilpage['merch'] . '?cmd=ordercomplete&pid=' . intval($uncrypted['project_id']) . '&oid=' . $uncrypted['orderid']);
				exit();
			}
			else 
			{
				$transaction_message = $ilance->paypal_pro->Response['L_LONGMESSAGE0'];
				$error_code = isset($ilance->paypal_pro->Response['L_ERRORCODE0']) ? $ilance->paypal_pro->Response['L_ERRORCODE0'] : 'n/a';
				if ($silentmode)
				{
					if ($returnorderid)
					{
						return false . '|0';
					}
					return false . '|' . $transaction_message;
				}
				$date_time = DATETIME24H;
				
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('creditcard_processing_error');		
				$ilance->email->set(array(
					'{{gatewayresponse}}' => $ilance->paypal_pro->Response['L_SHORTMESSAGE0'],
					'{{gatewaymessage}}' => $ilance->paypal_pro->Response['L_LONGMESSAGE0'],
					'{{gatewayerrorcode}}' => $error_code,
					'{{ipaddress}}' =>IPADDRESS,
					'{{location}}' => LOCATION,
					'{{scripturi}}' => SCRIPT_URI,
					'{{gateway}}' => $ilconfig['use_internal_gateway'],
					'{{member}}' => $_SESSION['ilancedata']['user']['username'],
					'{{memberemail}}' => $_SESSION['ilancedata']['user']['email'],
				));
				$ilance->email->send();
				$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', array('error_code', 'date_time','transaction_message','transaction_code'));
				exit();
			}
		}
		// #### payment from buyer via direct or offline payment #######
		else
		{
			// no escrow enabled: assuming outside payment communications
			// because of this we'll make sure that the seller has funds to cover this "FVF" if applicable in this category
			// if he does we'll debit it right away
			// if he does not we'll generate unpaid invoice as a last alternative to get that commission fee!
			$uncrypted['shipping_address_id'] = (isset($uncrypted['shipping_address_id']) ? intval($uncrypted['shipping_address_id']) : '');
			$uncrypted['shipperid'] = (isset($uncrypted['shipperid']) ? intval($uncrypted['shipperid']) : '');
			$uncrypted['buyershipcost'] = (isset($uncrypted['buyershipcost']) ? sprintf("%01.2f", $uncrypted['buyershipcost']) : '');
			$success = $ilance->escrow_buynow->instant_purchase_now('offline', $uncrypted['project_id'], $uncrypted['qty'], $uncrypted['amount'], $uncrypted['total'], $uncrypted['seller_id'], $uncrypted['buyer_id'], $uncrypted['shipping_address_required'], $uncrypted['shipping_address_id'], 0, $uncrypted['paymethod'], $uncrypted['buyershipcost'], $uncrypted['shipperid']);
			if ($success[0])
			{
				$ilance->auction_expiry->listings();
				$orderid = $success[1];
				
				($apihook = $ilance->api('merch_process_buy_now_payment_direct_or_offline_success')) ? eval($apihook) : false;
				
				if (mb_substr($uncrypted['paymethod'], 0, 8) == 'gateway_' OR mb_substr($uncrypted['paymethod'], 0, 10) == 'ccgateway_')
				{
					if ($silentmode)
					{
						if ($returnorderid)
						{
							return true . '|' . $orderid;
						}
						return true . '|' . $orderid;
					}
					$shipserviceurlbit = '';
					if (isset($uncrypted['shipping_address_required']) AND $uncrypted['shipping_address_required'] AND isset($uncrypted['shipperid']) AND $uncrypted['shipperid'] > 0)
					{
						$shipserviceurlbit = '&shipperid=' . $uncrypted['shipperid'];
					}
					// increase purchases for buyer
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productawards = productawards + '" . $uncrypted['qty'] . "'
						WHERE user_id = '" . $uncrypted['buyer_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase sales for seller
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productsold = productsold + '" . $uncrypted['qty'] . "'
						WHERE user_id = '" .  $uncrypted['seller_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					refresh(HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&id=' . $uncrypted['project_id'] . '&orderid=' . $orderid . '&paymethod=' . $uncrypted['paymethod'] . $shipserviceurlbit);
					exit();
				}
				else
				{
					if ($silentmode)
					{
						if ($returnorderid)
						{
							return true . '|' . $orderid;
						}
						return true . '|' . $orderid;
					}
					// increase purchases for buyer
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productawards = productawards + '" . $uncrypted['qty'] . "'
						WHERE user_id = '" . $uncrypted['buyer_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					// increase sales for seller
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET productsold = productsold + '" . $uncrypted['qty'] . "'
						WHERE user_id = '" .  $uncrypted['seller_id'] . "' 
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$area_title = '{_congratulations_you_purchased_this_item}';
					$page_title = SITE_NAME . ' - {_congratulations_you_purchased_this_item}';
					refresh(HTTP_SERVER . $ilpage['merch'] . '?cmd=ordercomplete&pid=' . $uncrypted['project_id'] . '&oid=' . $orderid);
					exit();
				}
			}
			else
			{
				if ($silentmode)
				{
					if ($returnorderid)
					{
						return false . '|0';
					}
					return false . '|{_there_was_a_problem_confirming_your_order}';
				}
				$area_title = '{_there_was_a_problem_confirming_your_order}';
				$page_title = SITE_NAME . ' - {_there_was_a_problem_confirming_your_order}';
				print_notice('{_there_was_a_problem_confirming_your_order}', '{_we_are_sorry_but_there_appears_to_be_a_problem_with_the_purchase_of_this_item}', "javascript: history.go(-1)", '{_back}');
				exit();
			}
		}
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>