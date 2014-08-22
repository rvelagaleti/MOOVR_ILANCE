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

if (!class_exists('escrow'))
{
	exit;
}

/**
* Function to handle escrow payments for buy now items
*
* @package      iLance\Escrow\BuyNow
* @version      4.0.0.8059
* @author       ILance
*/
class escrow_buynow extends escrow
{
	/**
        * Function to process a purchase now payment for a particular user for a specific amount.  This function takes final value fee and insertion
        * fee permission exemptions into consideration.  If the item is sold in a currency other than site default, the amount will be automatically
        * converted from the listing currency into the site default currency.  For example, EUR 35,00 will be recorded into the amount field as USD 44.17.
        * Shipping and other costs will also be converted into the site default via currency rates calculation.
        *
        * This function is also responsible for updating `buynow_purchases` field in the listings table so members can sort their listings based on most/least item sales.
        *
        * @param       string       payment method (offline or account)
        * @param       integer      project id
        * @param       integer      order qty
        * @param       integer      order amount
        * @param       integer      order total amount
        * @param       integer      seller id
        * @param       integer      buyer id
        * @param       bool         is shipping address required?
        * @param       integer      shipping address id profile for buyers location
        * @param       integer      account id
        * @param       string       buyers selected payment method string (just the title to show on buying/selling act)
        * @param       integer      buyers selected shipping cost
        * @param       integer      buyers selected shipping service id
        *
        * @return      array        Returns true or false if payment could be completed including order id
        */
	function instant_purchase_now($method = '', $projectid = 0, $qty = 0, $amount = 0, $total = 0, $seller_id = 0, $buyer_id = 0, $shipping_address_required = 1, $shipping_address_id = 0, $accountid = 0, $buyerpaymethod = 'Unknown', $buyershipcost = 0, $buyershipperid = 0)
	{
		global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
		$cid = fetch_auction('cid', $projectid);
		$rawamount = sprintf("%01.2f", ($amount * $qty));
		$rawtotal = ($buyershipcost > 0)
			? sprintf("%01.2f", ($amount * $qty) + $buyershipcost)
			: sprintf("%01.2f", ($amount * $qty));
		$project_currency = $currencyid = intval(fetch_auction('currencyid', $projectid));
		$project_currency_rate = $ilance->currency->currencies[$project_currency]['rate'];
		$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
		$default_currency_rate = $ilance->currency->currencies[$default_currency]['rate'];
		if ($ilconfig['globalserverlocale_currencyselector'] AND $project_currency != $default_currency)
		{
			$amount = convert_currency($default_currency, $amount, $project_currency);
			$buyershipcost = convert_currency($default_currency, $buyershipcost, $project_currency);
			$total = convert_currency($default_currency, $total, $project_currency);
			$currencyid = $default_currency;
			$rawamount = sprintf("%01.2f", ($amount * $qty));
			$rawtotal = ($buyershipcost > 0)
				? sprintf("%01.2f", ($amount * $qty) + $buyershipcost)
				: sprintf("%01.2f", ($amount * $qty));
		}
		$shippingaddress = ($shipping_address_required) ? $ilance->shipping->print_shipping_address_text(intval($buyer_id)) : '{_no_shipping_address_required_assuming_digital_item_delivery_please_communicate_with_customer}';
		if ($method == 'offline')
		{
			// #### FINAL VALUE FEE ################################
			// calculate final value fee to seller for the total amount passed to this function
			// we will not include shipping in the final value fee amount to calculate
			$invoiceid = $isfvfpaid = $avail_bal = $total_bal = 0;
			$paymentstatus = '{_no_charge}';
			$fvf = $ilance->accounting_fees->calculate_final_value_fee(($amount * $qty), $cid, 'product', '', $seller_id);
			if ($fvf > 0)
			{
				$fvf = sprintf("%01.2f", $fvf);
				$fvfnotax = $fvf;
				
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $fvf) . "',";
				if ($ilance->tax->is_taxable(intval($seller_id), 'finalvaluefee'))
				{
					$taxamount = $ilance->tax->fetch_amount(intval($seller_id), $fvf, 'finalvaluefee', 0);
					$totalamount = ($fvf + $taxamount);
					$taxinfo = $ilance->tax->fetch_amount(intval($seller_id), $fvf, 'finalvaluefee', 1);
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					$fvf = $totalamount;
				}
				$avail_bal = $total_bal = 0;
				$account = $ilance->db->query("
					SELECT available_balance, total_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($seller_id) . "'
				");
				if ($ilance->db->num_rows($account) > 0)
				{
					$res = $ilance->db->fetch_array($account, DB_ASSOC);
					$avail_bal = $res['available_balance'];
					$total_bal = $res['total_balance'];
				}
				// #### does user have autopayments active?
				$autopayments = fetch_user('autopayment', $seller_id);
				if ($avail_bal >= $fvf AND $autopayments)
				{
					$paymentstatus = '{_paid_and_processed_via_online_account_balance}';
					$isfvfpaid = '1';
					// we have enough funds to cover the fee!
					// pay fvf and generate paid transaction
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = available_balance - $fvf,
						total_balance = total_balance - $fvf
						WHERE user_id = '" . intval($seller_id) . "'
					");
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						intval($projectid),
						0,
						intval($seller_id),
						0,
						0,
						0,
						'{_purchase_now_seller_final_value_fee} - ' . fetch_auction('project_title', intval($projectid)) . ' #' . intval($projectid),
						sprintf("%01.2f", $fvfnotax),
						sprintf("%01.2f", $fvf),
						'paid',
						'debit',
						'account',
						DATETIME24H,
						DATETIME24H,
						DATETIME24H,
						'',
						0,
						0,
						1
					);
					$ilance->accounting_payment->insert_income_spent(intval($seller_id), $fvf, 'credit');
				}
				// #### seller does not have enough funds to cover fee!
				else
				{
					// generate fvf unpaid transaction!
					$paymentstatus = '{_pending_immediate_payment}';
					$isfvfpaid = '0';
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						intval($projectid),
						0,
						intval($seller_id),
						0,
						0,
						0,
						'{_purchase_now_seller_final_value_fee} - ' . fetch_auction('project_title', intval($projectid)) . ' #' . intval($projectid),
						sprintf("%01.2f", $fvfnotax),
						'',
						'unpaid',
						'debit',
						'account',
						DATETIME24H,
						DATEINVOICEDUE,
						'',
						'',
						0,
						0,
						1
					);
				}
				// update invoice to isfvf = 1
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET
					$extrainvoicesql
					isfvf = '1'
					WHERE invoiceid = '" . intval($invoiceid) . "'
					LIMIT 1
				");
			}
			// #### generate final value donation fee to the seller (if applicable)
			$ilance->accounting_fees->construct_final_value_donation_fee($projectid, $total, 'charge');
			// #### create buy now order ###########################
			// in this case, since escrow is disabled the fee column
			// will still show a fee based on the "final value fee" vs the escrow commission.
			// and the fee2 column will be just 0.00 (fee to buyer which should be nill)
			$ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "buynow_orders
                                (orderid, project_id, buyer_id, owner_id, qty, amount, originalcurrencyid, originalcurrencyidrate, convertedtocurrencyid, convertedtocurrencyidrate, fvf, fvfinvoiceid, isfvfpaid, ship_required, ship_location, orderdate, buyerpaymethod, buyershipcost, buyershipperid, status)
                                VALUES(
                                NULL,
                                '" . intval($projectid) . "',
                                '" . intval($buyer_id) . "',
                                '" . intval($seller_id) . "',
                                '" . intval($qty) . "',
                                '" . sprintf("%01.2f", $rawtotal) . "',
				'" . intval($project_currency) . "',
				'" . $ilance->db->escape_string($project_currency_rate) ."',
				'" . intval($default_currency) . "',
				'" . $ilance->db->escape_string($default_currency_rate) ."',
                                '" . sprintf("%01.2f", $fvf) . "',
                                '" . intval($invoiceid) . "',
                                '" . intval($isfvfpaid) . "',
                                '" . intval($shipping_address_required) . "',
                                '" . $ilance->db->escape_string($shippingaddress) . "',
                                '" . DATETIME24H . "',
				'" . $ilance->db->escape_string($buyerpaymethod) . "',
				'" . sprintf("%01.2f", $buyershipcost) . "',
				'" . intval($buyershipperid) . "',
                                'offline')
                        ");
			$neworderid = $ilance->db->insert_id();
			// #### associate this fvf to a buynow order id ########
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET buynowid = '" . $neworderid . "'
				WHERE invoiceid = '" . intval($invoiceid) . "'
			");
			$bqty = fetch_auction('buynow_qty', $projectid); // 10
			$buynowqty = ($bqty - $qty); // 10 - 5 = 5
			if ($buynowqty <= 0)
			{
				$buynowqty = 0;
			}
			// #### update the qty available for this item #########
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET hasbuynowwinner = '1',
				buynow_qty = " . intval($buynowqty) . ",
				buynow_purchases = buynow_purchases + " . intval($qty) . "
				WHERE project_id = '" . intval($projectid) . "'
				LIMIT 1
			");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET bidstatus = 'outbid'
				WHERE project_id = '" . intval($projectid) . "'
					AND user_id != '" . intval($buyer_id) . "'
			");
			// #### if there are no qty available then change open status to expired
			$buynow_qtyleft = $bqty;
			$filtered_auctiontype = fetch_auction('filtered_auctiontype', $projectid);
			$projectstatus = fetch_auction('status', $projectid);
			$buyerpaymethod = $ilance->payment->print_fixed_payment_method($buyerpaymethod);
			$buyerpaymethod = empty($buyerpaymethod) ? '{_not_specified}' : $buyerpaymethod;
			$pay_url = HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=orderdetail&pid=' . $projectid . '&oid=' . $neworderid;
			$existing = array(
				'{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
				'{{buyer_fullname}}' => $_SESSION['ilancedata']['user']['fullname'],
				'{{buyer_email}}' => $_SESSION['ilancedata']['user']['email'],
				'{{seller}}' => fetch_user('username', intval($seller_id)),
				'{{project_title}}' => stripslashes(fetch_auction('project_title', $projectid)),
				'{{project_id}}' => $projectid,
				'{{qty}}' => $qty,
				'{{project_url}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . $projectid,
				'{{amount_formatted}}' => $ilance->currency->format($amount, $currencyid),
				'{{rawamount_formatted}}' => $ilance->currency->format($rawamount, $currencyid),
				'{{rawtotal_formatted}}' => $ilance->currency->format($rawtotal, $currencyid),
				'{{total_amount_formatted}}' => $ilance->currency->format($total, $currencyid),
				'{{ship_costs}}' => $ilance->currency->format($buyershipcost, $currencyid),
				'{{shippingaddress}}' => $shippingaddress,
				'{{shippingservice}}' => $ilance->shipping->print_shipping_partner($buyershipperid),
				'{{fvf}}' => $ilance->currency->format($fvf),
				'{{fvftotal}}' => $ilance->currency->format($fvf),
				'{{paymentstatus}}' => $paymentstatus,
				'{{paymentmethod}}' => $buyerpaymethod,
				'{{pay_url}}' => $pay_url
			);
			$ilance->email->mail = fetch_user('email', intval($buyer_id));
			$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
			$ilance->email->get('buynow_offline_purchase_buyer');
			$ilance->email->set($existing);
			$ilance->email->send();
			$ilance->email->mail = fetch_user('email', intval($seller_id));
			$ilance->email->slng = fetch_user_slng($seller_id);
			$ilance->email->get('buynow_offline_purchase_seller');
			$ilance->email->set($existing);
			$ilance->email->send();
			$ilance->email->mail = SITE_EMAIL;
			$ilance->email->slng = fetch_site_slng();
			$ilance->email->get('buynow_offline_purchase_admin');
			$ilance->email->set($existing);
			$ilance->email->send();
			return array(true, $neworderid);
		}
		// #### INSTANT PURCHASE TO ESCROW VIA ACCOUNT BALANCE #########
		else if ($method == 'account')
		{
			$sel_balance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($buyer_id) . "'
                        ");
			if ($ilance->db->num_rows($sel_balance) > 0)
			{
				$res_balance = $ilance->db->fetch_array($sel_balance, DB_ASSOC);
				if ($res_balance['available_balance'] >= $total)
				{
					$area_title = '{_instant_purchase_to_escrow_via_online_account}';
					$page_title = SITE_NAME . ' - {_instant_purchase_to_escrow_via_online_account}';
					// $fee = seller escrow fee
					// $escrowfeebuyer = buyer escrow fee
					$escrowfee = $escrowfeebuyer = 0;
					$escrowfeebuyerinvoiceid = $isescrowfeebuyerpaid = $escrowfeeinvoiceid = $isescrowfeepaid = '0';
					if ($ilconfig['escrowsystem_escrowcommissionfees'])
					{
						
						if ($ilconfig['escrowsystem_merchantfixedprice'] > 0)
						{
							// fixed escrow cost to provider for release of funds
							$escrowfee = sprintf("%01.2f", $ilconfig['escrowsystem_merchantfixedprice']);
						}
						else
						{
							if ($ilconfig['escrowsystem_merchantpercentrate'] > 0)
							{
								// percentage rate of total winning bid amount
								// which would be the same as the amount being forwarded into escrow
								$escrowfee = sprintf("%01.2f", ($rawtotal * $ilconfig['escrowsystem_merchantpercentrate'] / 100));
							}
						}
						if ($escrowfee > 0)
						{
							$taxamount = 0;
							$istaxable = '0';
							$taxinfo = '';
							if ($ilance->tax->is_taxable(intval($seller_id), 'commission'))
							{
								$taxamount = $ilance->tax->fetch_amount(intval($seller_id), $escrowfee, 'commission', 0);
								$taxinfo = $ilance->tax->fetch_amount(intval($seller_id), $escrowfee, 'commission', 1);
								$istaxable = '1';
							}
							$escrowfeenotax = $escrowfee;
							$escrowfee = sprintf("%01.2f", ($escrowfee + $taxamount));
							// charge escrow fee to seller as a separate transaction
							// this creates a transaction history item for the buyer of item
							$txn = $ilance->accounting_payment->construct_transaction_id();
							$availablebalance = fetch_user('available_balance', $seller_id);
							$totalbalance = fetch_user('total_balance', $seller_id);
							$autopayment = fetch_user('autopayment', $seller_id);
							if ($availablebalance >= $escrowfee AND $autopayment)
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "invoices
									(invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
									VALUES(
									NULL,
									'" . intval($projectid) . "',
									'" . intval($seller_id) . "',
									'{_purchase_now_seller_escrow_fee} - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
									'" . $ilance->db->escape_string($escrowfeenotax) . "',
									'" . $ilance->db->escape_string($escrowfee) . "',
									'" . $ilance->db->escape_string($escrowfee) . "',
									'" . $istaxable . "',
									'" . $ilance->db->escape_string($taxamount) . "',
									'" . $ilance->db->escape_string($taxinfo) . "',
									'paid',
									'commission',
									'account',
									'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'{_may_include_applicable_taxes}',
									'" . $ilance->db->escape_string($txn) . "',
									'1')
								");
								$escrowfeeinvoiceid = $ilance->db->insert_id();
								$new_total = ($totalbalance - $escrowfee);
								$new_avail = ($availablebalance - $escrowfee);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
									total_balance = '" . sprintf("%01.2f", $new_total) . "'
									WHERE user_id = '" . intval($seller_id) . "'
								");
								$ilance->accounting_payment->insert_income_spent(intval($seller_id), $escrowfee, 'credit');
								$isescrowfeepaid = '1';
							}
							else
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "invoices
									(invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
									VALUES(
									NULL,
									'" . intval($projectid) . "',
									'" . intval($seller_id) . "',
									'{_purchase_now_seller_escrow_fee} - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
									'" . $ilance->db->escape_string($escrowfeenotax) . "',
									'" . $ilance->db->escape_string($escrowfee) . "',
									'" . $istaxable . "',
									'" . $ilance->db->escape_string($taxamount) . "',
									'" . $ilance->db->escape_string($taxinfo) . "',
									'unpaid',
									'commission',
									'account',
									'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'{_may_include_applicable_taxes}',
									'" . $ilance->db->escape_string($txn) . "',
									'1')
								");
								$escrowfeeinvoiceid = $ilance->db->insert_id();
								$isescrowfeepaid = '0';
							}
						}
						// #### PRODUCT BUYER ESCROW FEES ##########################
						// we'll populate the fee2 field which denotes any fees the buyer of this buynow purchase
						// must pay the site owner .. we'll also calculate any tax if applicable to ensure that the
						// fee to the buyer will include the full fee amount + any applicable taxes (for commission txns)
						// escrow commission fees to auction owner enabled
						if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
						{
							$escrowfeebuyer = sprintf("%01.2f", $ilconfig['escrowsystem_bidderfixedprice']);
						}
						else
						{
							if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
							{
								// percentage rate of total winning bid amount
								// which would be the same as the amount being forwarded into escrow
								$escrowfeebuyer = sprintf("%01.2f", ($rawtotal * $ilconfig['escrowsystem_bidderpercentrate'] / 100));
							}
						}
						if ($escrowfeebuyer > 0)
						{
							$taxamount = 0;
							$istaxable = '0';
							$taxinfo = '';
							if ($ilance->tax->is_taxable(intval($buyer_id), 'commission'))
							{
								$taxamount = $ilance->tax->fetch_amount(intval($buyer_id), $escrowfeebuyer, 'commission', 0);
								$taxinfo = $ilance->tax->fetch_amount(intval($buyer_id), $escrowfeebuyer, 'commission', 1);
								$istaxable = '1';
							}
							// exact amount to charge buyer
							$escrowfeebuyernotax = $escrowfeebuyer;
							$escrowfeebuyer = sprintf("%01.2f", ($escrowfeebuyer + $taxamount));

							// charge escrow fee to buyer as a separate transaction
							// this creates a transaction history item for the buyer of item
							$txn = $ilance->accounting_payment->construct_transaction_id();
							$autopayment = fetch_user('autopayment', $buyer_id);
							if ($autopayment)
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "invoices
									(invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
									VALUES(
									NULL,
									'" . intval($projectid) . "',
									'" . intval($buyer_id) . "',
									'" . $ilance->db->escape_string('{_purchase_now_buyer_escrow_fee}') . " - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
									'" . $ilance->db->escape_string($escrowfeebuyernotax) . "',
									'" . $ilance->db->escape_string($escrowfeebuyer) . "',
									'" . $ilance->db->escape_string($escrowfeebuyer) . "',
									'" . $istaxable . "',
									'" . $ilance->db->escape_string($taxamount) . "',
									'" . $ilance->db->escape_string($taxinfo) . "',
									'paid',
									'commission',
									'account',
									'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
									'" . $ilance->db->escape_string($txn) . "',
									'1')
								");
								$escrowfeebuyerinvoiceid = $ilance->db->insert_id();
								$isescrowfeebuyerpaid = '1';
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET available_balance = available_balance - " . sprintf("%01.2f", $escrowfeebuyer) . ",
									total_balance = total_balance - " . sprintf("%01.2f", $escrowfeebuyer) . "
									WHERE user_id = '" . intval($buyer_id) . "'
								");
								$ilance->accounting_payment->insert_income_spent(intval($buyer_id), $escrowfeebuyer, 'credit');
							}
							else
							{
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "invoices
									(invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
									VALUES(
									NULL,
									'" . intval($projectid) . "',
									'" . intval($buyer_id) . "',
									'" . $ilance->db->escape_string('{_purchase_now_buyer_escrow_fee}') . " - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
									'" . $ilance->db->escape_string($escrowfeebuyernotax) . "',
									'" . $ilance->db->escape_string($escrowfeebuyer) . "',
									'" . $istaxable . "',
									'" . $ilance->db->escape_string($taxamount) . "',
									'" . $ilance->db->escape_string($taxinfo) . "',
									'unpaid',
									'commission',
									'account',
									'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
									'" . DATETIME24H . "',
									'" . DATETIME24H . "',
									'" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
									'" . $ilance->db->escape_string($txn) . "',
									'1')
								");
								$escrowfeebuyerinvoiceid = $ilance->db->insert_id();
								$isescrowfeebuyerpaid = '0';
							}
						}
					}
					// #### generate final value donation fee to the seller (if applicable)
					$ilance->accounting_fees->construct_final_value_donation_fee($projectid, $rawtotal, 'charge');
					$transactionid = $ilance->accounting_payment->construct_transaction_id();
					$ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "invoices
                                                (invoiceid, projectid, user_id, p2b_user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid)
                                                VALUES(
                                                NULL,
                                                '" . intval($projectid) . "',
                                                '" . intval($buyer_id) . "',
                                                '" . intval($seller_id) . "',
                                                '{_purchase_now} {_escrow_payment_forward} - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
                                                '" . $ilance->db->escape_string($rawtotal) . "',
                                                '" . $ilance->db->escape_string($rawtotal) . "',
                                                '" . $ilance->db->escape_string($rawtotal) . "',
                                                'paid',
                                                'buynow',
                                                'account',
                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                '" . DATETIME24H . "',
                                                '" . DATETIME24H . "',
                                                '" . DATETIME24H . "',
                                                '{_funds_held_within_escrow_until_item_has_been_delivered}',
                                                '" . $ilance->db->escape_string($transactionid) . "')
                                        ");
					$newinvoiceid = $ilance->db->insert_id();
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = available_balance - " . sprintf("%01.2f", $rawtotal) . ",
						total_balance = total_balance - " . sprintf("%01.2f", $rawtotal) . "
						WHERE user_id = '" . intval($buyer_id) . "'
					");
					$ilance->accounting_payment->insert_income_spent(intval($buyer_id), $rawtotal, 'credit');
					// #### CALCULATE FVF TO SELLER FOR THIS ORDER
					// this is a separate final value fee if applicable which is separate from the escrow fee logic above
					$cid = fetch_auction('cid', $projectid);
					$fvfbuyer = '0.00'; // not in use
					$fvfinvoiceid = $isfvfpaid = '0';
					$fvf = $ilance->accounting_fees->calculate_final_value_fee($rawtotal, $cid, 'product', '', $seller_id);
					if ($fvf > 0)
					{
						$fvf = sprintf("%01.2f", $fvf);
						$fvfnotax = $fvf;
						
						$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $fvf) . "',";
						if ($ilance->tax->is_taxable(intval($seller_id), 'finalvaluefee'))
						{
							$taxamount = $ilance->tax->fetch_amount(intval($seller_id), $fvf, 'finalvaluefee', 0);
							$totalamount = ($fvf + $taxamount);
							$taxinfo = $ilance->tax->fetch_amount(intval($seller_id), $fvf, 'finalvaluefee', 1);
							$extrainvoicesql = "
								istaxable = '1',
								totalamount = '" . sprintf("%01.2f", $totalamount) . "',
								taxamount = '" . sprintf("%01.2f", $taxamount) . "',
								taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
							";
							// ensure our new tax is applied to the current fvf..
							$fvf = $totalamount;
						}
						// charge escrow fee to seller as a separate transaction
						// this creates a transaction history item for the buyer of item
						$txn = $ilance->accounting_payment->construct_transaction_id();
						$availablebalance = fetch_user('available_balance', $seller_id);
						$totalbalance = fetch_user('total_balance', $seller_id);
						$autopayment = fetch_user('autopayment', $seller_id);
						if ($availablebalance >= $fvf AND $autopayment)
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "invoices
								(invoiceid, projectid, user_id, description, amount, paid, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isfvf)
								VALUES(
								NULL,
								'" . intval($projectid) . "',
								'" . intval($seller_id) . "',
								'{_purchase_now_seller_final_value_fee} - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
								'" . $ilance->db->escape_string($fvfnotax) . "',
								'" . $ilance->db->escape_string($fvf) . "',
								'" . $ilance->db->escape_string($fvf) . "',
								'paid',
								'debit',
								'account',
								'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
								'" . DATETIME24H . "',
								'" . DATETIME24H . "',
								'" . DATETIME24H . "',
								'{_may_include_applicable_taxes}',
								'" . $ilance->db->escape_string($txn) . "',
								'1')
							");
							$fvfinvoiceid = $ilance->db->insert_id();
							// update invoice mark as final value fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET
								$extrainvoicesql
								isfvf = '1'
								WHERE invoiceid = '" . intval($fvfinvoiceid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// debit funds from seller's account
							$new_total = ($availablebalance - $fvf);
							$new_avail = ($totalbalance - $fvf);
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
								total_balance = '" . sprintf("%01.2f", $new_total) . "'
								WHERE user_id = '" . intval($seller_id) . "'
							");
							$ilance->accounting_payment->insert_income_spent(intval($seller_id), $fvf, 'credit');
							$isfvfpaid = '1';
						}
						else
						{
							$ilance->db->query("
								INSERT INTO " . DB_PREFIX . "invoices
								(invoiceid, projectid, user_id, description, amount, totalamount, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isfvf)
								VALUES(
								NULL,
								'" . intval($projectid) . "',
								'" . intval($seller_id) . "',
								'{_purchase_now_seller_final_value_fee} - " . $ilance->db->escape_string(fetch_auction('project_title', intval($projectid))) . " #" . intval($projectid) . "',
								'" . $ilance->db->escape_string($fvfnotax) . "',
								'" . $ilance->db->escape_string($fvf) . "',
								'unpaid',
								'debit',
								'account',
								'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
								'" . DATETIME24H . "',
								'" . DATETIME24H . "',
								'" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
								'" . $ilance->db->escape_string($txn) . "',
								'1')
							");
							$fvfinvoiceid = $ilance->db->insert_id();
							// update invoice mark as final value fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET
								$extrainvoicesql
								isfvf = '1'
								WHERE invoiceid = '" . intval($fvfinvoiceid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							$isfvfpaid = '0';
						}
					}
					// this creates the buy now order and any applicable invoicing association
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "buynow_orders
						(orderid, project_id, buyer_id, owner_id, invoiceid, qty, amount, originalcurrencyid, originalcurrencyidrate, convertedtocurrencyid, convertedtocurrencyidrate, escrowfee, isescrowfeepaid, escrowfeeinvoiceid, escrowfeebuyer, isescrowfeebuyerpaid, escrowfeebuyerinvoiceid, fvf, isfvfpaid, fvfinvoiceid, fvfbuyer, isfvfbuyerpaid, ship_required, ship_location, orderdate, paiddate, winnermarkedaspaid, winnermarkedaspaiddate, buyerpaymethod, buyershipcost, buyershipperid, status)
						VALUES(
						NULL,
						'" . intval($projectid) . "',
						'" . intval($buyer_id) . "',
						'" . intval($seller_id) . "',
						'" . intval($newinvoiceid) . "',
						'" . intval($qty) . "',
						'" . $ilance->db->escape_string($rawtotal) . "',
						'" . intval($project_currency) . "',
						'" . $ilance->db->escape_string($project_currency_rate) . "',
						'" . intval($default_currency) . "',
						'" . $ilance->db->escape_string($default_currency_rate) . "',
						'" . $ilance->db->escape_string($escrowfee) . "',
						'" . intval($isescrowfeepaid) . "',
						'" . intval($escrowfeeinvoiceid) . "',
						'" . $ilance->db->escape_string($escrowfeebuyer) . "',
						'" . intval($isescrowfeebuyerpaid) . "',
						'" . intval($escrowfeebuyerinvoiceid) . "',
						'" . $ilance->db->escape_string($fvf) . "',
						'" . intval($isfvfpaid) . "',
						'" . intval($fvfinvoiceid) . "',
						'" . $ilance->db->escape_string($fvfbuyer) . "',
						'0',
						'" . intval($shipping_address_required) . "',
						'" . $ilance->db->escape_string($shippingaddress) . "',
						'" . DATETIME24H . "',
						'" . DATETIME24H . "',
						'1',
						'" . DATETIME24H . "',
						'escrow',
						'" . sprintf("%01.2f", $buyershipcost) . "',
						'" . intval($buyershipperid) . "',
						'pending_delivery')
					");
					$neworderid = $ilance->db->insert_id();
					// tie fvf invoice to seller to this buy now order so we have some association
					if ($fvfinvoiceid > 0)
					{
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($fvfinvoiceid) . "'
                                                ");
					}
					// tie escrow fee invoice to seller so we can update isescrowfeepaid used by other cancel or paid invoice actions
					if ($escrowfeeinvoiceid > 0)
					{
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($escrowfeeinvoiceid) . "'
                                                ");        
					}
					if ($escrowfeebuyerinvoiceid > 0)
					{
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "invoices
                                                        SET buynowid = '" . intval($neworderid) . "'
                                                        WHERE invoiceid = '" . intval($escrowfeebuyerinvoiceid) . "'
                                                ");       
					}
					$bqty = fetch_auction('buynow_qty', $projectid);
					$buynowqty = $bqty - $qty;
					if ($buynowqty <= 0)
					{
						$buynowqty = 0;
					}
					// update the qty available for this item
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET hasbuynowwinner = '1',
						buynow_qty = " . intval($buynowqty) . ",
						buynow_purchases = buynow_purchases + " . intval($qty) . "
						WHERE project_id = '" . intval($projectid) . "'
					");
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET bidstatus = 'outbid'
						WHERE project_id = '" . intval($projectid) . "'
							AND user_id != '" . intval($buyer_id) . "'
					");
					$buynow_qtyleft = fetch_auction('buynow_qty', $projectid);
					$filtered_auctiontype = fetch_auction('filtered_auctiontype', $projectid);
					$projectstatus = fetch_auction('status', $projectid);
					$project_title = fetch_auction('project_title', $projectid);
					$existing = array(
						'{{customer}}' => $_SESSION['ilancedata']['user']['username'],
						'{{transactionid}}' => $transactionid,
						'{{merchant}}' => fetch_user('username', intval($seller_id)),
						'{{fullname}}' => fetch_user('fullname', intval($buyer_id)),
						'{{emailaddress}}' => fetch_user('email', intval($buyer_id)),
						'{{phone}}' => fetch_user('phone', intval($buyer_id)),
						'{{projectid}}' => $projectid,
						'{{description}}' => '{_escrow_payment_forward}: ' . $project_title . ' (' . $projectid . ')',
						'{{qty}}' => $qty,
						'{{amount_formatted}}' => $ilance->currency->format($amount, $currencyid),
						'{{rawamount_formatted}}' => $ilance->currency->format($rawamount, $currencyid),
						'{{rawtotal_formatted}}' => $ilance->currency->format($rawtotal, $currencyid),
						'{{total_amount_formatted}}' => $ilance->currency->format($total, $currencyid),
						'{{invoiceid}}' => $newinvoiceid,
						'{{shippingaddress}}' => $shippingaddress,
						'{{ship_costs}}' => $ilance->currency->format($buyershipcost, $currencyid),
						'{{shippingservice}}' => $ilance->shipping->print_shipping_partner($buyershipperid),
						'{{escrowfee}}' => $ilance->currency->format($escrowfeebuyer, $currencyid),	
						'{{paymentmethod}}' => '{_escrow}',
						'{{project_title}}' => $project_title
					);
					$ilance->email->mail = fetch_user('email', intval($buyer_id));
					$ilance->email->slng = fetch_user_slng(intval($buyer_id));
					$ilance->email->get('escrow_buynow_payment_confirmation');
					$ilance->email->set($existing);
					$ilance->email->send();
					$ilance->email->mail = fetch_user('email', intval($seller_id));
					$ilance->email->slng = fetch_user_slng(intval($seller_id));
					$ilance->email->get('product_escrow_payment_foward_merchant');
					$ilance->email->set($existing);
					$ilance->email->send();
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('escrow_buynow_payment_confirmation_admin');
					$ilance->email->set($existing);
					$ilance->email->send();
					return array(true, $neworderid);
				}
			}
		}
		return false;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>