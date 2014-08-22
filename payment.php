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
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### setup script location ##################################################
define('LOCATION', 'ipn');
// #### require backend ########################################################
require_once('./functions/config.php');
if (empty($ilance->GPC['do']))
{
	echo 'This script cannot be parsed indirectly.  Operation aborted.';
	exit();
}

($apihook = $ilance->api('payment_start')) ? eval($apihook) : false;

// #### PAYPAL RESPONSE HANLDER ################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_paypal')
{
	$area_title = 'PayPal IPN';
	if ($ilconfig['paypal_active'] == false)
	{
		$area_title = 'PayPal IPN<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
	$ilance->paypal->error_email = SITE_EMAIL;
	$ilance->paypal->timeout = 60;
	$ilance->paypal->send_response();
	// #### ILANCE RESPONSE VERIFICATION ###################################
	if ((isset($ilance->GPC['business']) AND mb_strtolower(urldecode($ilance->GPC['business'])) == mb_strtolower($ilconfig['paypal_business_email']) OR isset($ilance->GPC['receiver_email']) AND mb_strtolower(urldecode($ilance->GPC['receiver_email'])) == mb_strtolower($ilconfig['paypal_business_email'])))
	{
		$custom = isset($ilance->GPC['custom']) ? urldecode($ilance->GPC['custom']) : '';
		if (isset($custom) AND !empty($custom))
		{
			$custom = explode('|', $custom);
		}
		else
		{
			$area_title = 'PayPal IPN<div class="smaller">This script requires well-formed parameters.  Operation aborted.</div>';
			echo 'This script requires well-formed parameters.  Operation aborted.';
			exit();
		}
		$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
		$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
		$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
		$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : 0;
		$ilance->GPC['length'] = isset($custom[4])  ? intval($custom[4]) : 0;
		$ilance->GPC['units'] = isset($custom[5])  ? $custom[5] : 0;
		$ilance->GPC['subscriptionid'] = isset($custom[6])  ? intval($custom[6]) : 0;
		$ilance->GPC['cost'] = isset($custom[7])  ? $custom[7] : 0;
		$ilance->GPC['roleid'] = isset($custom[8])  ? intval($custom[8]) : '-1';
		// #### PAYPAL RECURRING SUBSCRIPTION PAYMENT HANDLER ##########
		if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'RECURRINGSUBSCRIPTION' AND $ilconfig['paypal_subscriptions'])
		{
			($apihook = $ilance->api('payment_paypal_recurring')) ? eval($apihook) : false;
			
			if ($ilance->paypal->is_verified())
			{
				$area_title = 'PayPal IPN<div class="smaller">RECURRINGSUBSCRIPTION : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_transaction_type() == 'subscr_payment')
				{
					if ($ilance->GPC['userid'] > 0)
					{
						$startdate = DATETIME24H;
						$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
						$recurring = 1;
						$paymethod = 'paypal';
						$invoiceid = $ilance->accounting->insert_transaction(
							$ilance->GPC['subscriptionid'],
							0,
							0,
							intval($ilance->GPC['userid']),
							0,
							0,
							0,
							$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->GPC['subscr_id'] . ']',
							sprintf("%01.2f", $ilance->GPC['cost']),
							sprintf("%01.2f", $ilance->GPC['cost']),
							'paid',
							'debit',
							'paypal',
							DATETIME24H,
							DATEINVOICEDUE,
							DATETIME24H,
							$ilance->paypal->get_transaction_id(),
							0,
							0,
							1,
							'',
							0,
							0
						);
						$ilance->subscription_plan->activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
						$ilance->referral->update_referral_action('subscription', $ilance->GPC['userid']);
						
						($apihook = $ilance->api('payment_paypal_recurring_is_verified')) ? eval($apihook) : false;
					}
				}
				else if ($ilance->paypal->get_transaction_type() == 'subscr_modify')
				{
					($apihook = $ilance->api('payment_paypal_recurring_subscr_modify')) ? eval($apihook) : false;
					
					// update new subscription
					unset($ilance->GPC['units']);
					unset($ilance->GPC['length']);
					unset($ilance->GPC['subscriptionid']);
					unset($ilance->GPC['roleid']);
					unset($ilance->GPC['cost']);
					$ilance->GPC['subscriptionid'] = $ilance->GPC['item_number'];
					$ilance->GPC['units'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "units");
					$ilance->GPC['length'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "length");
					$ilance->GPC['roleid'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "roleid");
					$ilance->GPC['cost'] = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($ilance->GPC['subscriptionid']) . "'", "cost");
					$startdate = DATETIME24H;
					$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
					$recurring = 1;
					$paymethod = 'paypal';
					$invoiceid = $ilance->accounting->insert_transaction(
						$ilance->GPC['subscriptionid'],
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->GPC['subscr_id'] . ']',
						sprintf("%01.2f", $ilance->GPC['cost']),
						sprintf("%01.2f", $ilance->GPC['cost']),
						'paid',
						'debit',
						'paypal',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->paypal->get_transaction_id(),
						0,
						0,
						1,
						'',
						0,
						0
					);
					$ilance->subscription_plan->activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
				}
				else if ($ilance->paypal->get_transaction_type() == 'subscr_cancel' OR $ilance->paypal->get_transaction_type() == 'subscr_eot')
				{
					($apihook = $ilance->api('payment_paypal_recurring_subscr_cancel_eot')) ? eval($apihook) : false;
					
					$ilance->subscription_plan->deactivate_subscription_plan($ilance->GPC['userid']);
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('recurring_subscription_cancelled_admin');		
					$ilance->email->set(array(
						'{{username}}' => fetch_user('username', $ilance->GPC['userid']),
						'{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
						'{{gateway}}' => 'Paypal',
						'{{txn_type}}' => $ilance->paypal->get_transaction_type(),
					));
					$ilance->email->send();
					$ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
					$ilance->email->slng = fetch_user_slng($ilance->GPC['userid']);
					$ilance->email->get('recurring_subscription_cancelled');		
					$ilance->email->set(array(
						'{{username}}' => fetch_user('username', $ilance->GPC['userid']),
						'{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
						'{{gateway}}' => 'Paypal',
						'{{txn_type}}' => $ilance->paypal->get_transaction_type(),
					));
					$ilance->email->send();
				}			
				else if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_recurring_reversed_refunded')) ? eval($apihook) : false;
					
					$ilance->subscription_plan->deactivate_subscription_plan($ilance->GPC['userid']);
				}
			}
		}
		// #### PAYPAL SUBSCRIPTION PAYMENT (REGULAR) ##################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
		{
			($apihook = $ilance->api('payment_paypal_subscription')) ? eval($apihook) : false;
			
			// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
				$area_title = 'PayPal IPN<div class="smaller">SUBSCRIPTION : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_subscription_reversed_refunded')) ? eval($apihook) : false;
					
					$ilance->subscription_plan->deactivate_subscription_plan($ilance->GPC['userid']);
				}
				else
				{
					// payment has been completed
					if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
					{
						$sql = $ilance->db->query("
							SELECT invoiceid
							FROM " . DB_PREFIX . "invoices
							WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
								AND user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoicetype = 'subscription'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							$sql_invoice_array = $ilance->db->fetch_array($sql, DB_ASSOC);
							$sql_user = $ilance->db->query("
								SELECT username, email
								FROM " . DB_PREFIX . "users
								WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_user) > 0)
							{
								$res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "invoices
									SET paid = '" . sprintf("%01.2f", $ilance->paypal->get_transaction_amount()) . "',
									status = 'paid',
									paymethod = 'paypal',
									paiddate = '" . DATETIME24H . "',
									referer = '" . $ilance->db->escape_string(REFERRER) . "',
									custommessage = '" . $ilance->db->escape_string($ilance->paypal->get_transaction_id()) . "'
									WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
										AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								$ilance->accounting_payment->insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->paypal->get_transaction_amount()), 'credit');
								$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '" . intval($ilance->GPC['userid']) . "'", "subscriptionid");
								$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "units");
								$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "length");
								$subscription_length = $ilance->subscription->subscription_length($units, $length);
								$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "subscription_user
									SET active = 'yes',
									paymethod = 'paypal',
									startdate = '" . DATETIME24H . "',
									renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
									invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
									WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								$ilance->referral->update_referral_action('subscription', intval($ilance->GPC['userid']));
								$ilance->email->mail = SITE_EMAIL;
								$ilance->email->slng = fetch_site_slng();
								$ilance->email->get('subscription_paid_via_paypal_admin');		
								$ilance->email->set(array(
									'{{provider}}' => ucfirst($res_user['username']),
									'{{invoice_id}}' => $ilance->GPC['invoiceid'],
									'{{invoice_amount}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
									'{{paymethod}}' => 'Paypal',
								));
								$ilance->email->send();
								$ilance->email->mail = $res_user['email'];
								$ilance->email->slng = fetch_user_slng($res_user['user_id']);
								$ilance->email->get('subscription_paid_via_paypal');		
								$ilance->email->set(array(
									'{{provider}}' => ucfirst($res_user['username']),
									'{{invoice_id}}' => $ilance->GPC['invoiceid'],
									'{{invoice_amount}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
									'{{paymethod}}' => 'Paypal',
								));
								$ilance->email->send();
								
								($apihook = $ilance->api('payment_paypal_subscription_is_verified')) ? eval($apihook) : false;
							}
						}	
					}
				}
			}
		}
		// #### HANDLE DEPOSIT PAYMENTS ################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
		{
			($apihook = $ilance->api('payment_paypal_deposit')) ? eval($apihook) : false;
			
			// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
				$area_title = 'PayPal IPN<div class="smaller">DEPOSIT : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_deposit_reversed_refunded')) ? eval($apihook) : false;
					
					// changes -19.95 to 19.95
					$chargeback['amount'] = preg_replace("#^-#", "", $ilance->paypal->get_transaction_amount());
					$sql = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "invoices
						WHERE custommessage = '" . $ilance->db->escape_string($ilance->GPC['parent_txn_id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$accountbal = $ilance->db->query("
							SELECT total_balance, available_balance
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . $res['user_id'] . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($accountbal) > 0)
						{
							$sel_account_result = $ilance->db->fetch_array($accountbal, DB_ASSOC);
							$new_credit_total_balance = ($sel_account_result['total_balance'] - $chargeback['amount']);
							$new_credit_avail_balance = ($sel_account_result['available_balance'] - $chargeback['amount']);
							$deposit_invoice_id = $ilance->accounting->insert_transaction(
								0,
								0,
								0,
								intval($res['user_id']),
								0,
								0,
								0,
								'PayPal [' . $ilance->paypal->get_payment_status() . '] Trigger Received [TXN_ID: ' . $ilance->paypal->get_transaction_id() . ']',
								sprintf("%01.2f", $chargeback['amount']),
								sprintf("%01.2f", $chargeback['amount']),
								'paid',
								'debit',
								'account',
								DATETIME24H,
								DATEINVOICEDUE,
								DATETIME24H,
								$ilance->paypal->get_transaction_id(),
								0,
								0,
								1,
								'',
								1,
								0
							);
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
								total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
								WHERE user_id = '" . intval($res['user_id']) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
						}
					}	
				}
				else
				{
					// just in case paypal decides to ping the site twice or more with this payment
					// let's do a quick txn_id checkup so we don't double credit the member
					$validtransaction = true;
					if ($ilance->accounting_payment->is_duplicate_txn_id($ilance->paypal->get_transaction_id()))
					{
						$validtransaction = false;
					}
					if ($validtransaction)
					{
						// deposit amount variables ex: 150.00
						$deposit['echeck'] = 0;
						#### PAYPAL E-CHECK SUPPORT ########################
						// using echeck limits the total fees to 5.00 vs 2.9% of total amount
						if ($ilance->paypal->get_payment_type() == 'echeck')
						{
							$deposit['echeck'] = 1;
						}
						else if ($ilance->paypal->get_payment_type() == 'instant')
						{
							$deposit['echeck'] = 0;
						}
						// select amount for existing user
						$accountbal = $ilance->db->query("
							SELECT total_balance, available_balance
							FROM " . DB_PREFIX . "users
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($accountbal) > 0)
						{
							$sel_account_result = $ilance->db->fetch_array($accountbal, DB_ASSOC);
							$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
							$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
							$deposit_invoice_id = $ilance->accounting->insert_transaction(
								0,
								0,
								0,
								intval($ilance->GPC['userid']),
								0,
								0,
								0,
								'{_account_deposit_credit_via} PayPal [TXN_ID: ' . $ilance->paypal->get_transaction_id() . '] {_into_online_account}: ' . $ilance->currency->format($ilance->GPC['creditamount']),
								sprintf("%01.2f", $ilance->paypal->get_transaction_amount()),
								sprintf("%01.2f", $ilance->paypal->get_transaction_amount()),
								'paid',
								'credit',
								'paypal',
								DATETIME24H,
								DATEINVOICEDUE,
								DATETIME24H,
								$ilance->paypal->get_transaction_id(),
								0,
								0,
								1,
								'',
								1,
								0
							);
							// update the transaction with the acual amount we're crediting this user for
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
								WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
									AND invoiceid = '" . intval($deposit_invoice_id) . "'
							", 0, null, __FILE__, __LINE__);
							// update customers online account balance information
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
								total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
								WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							", 0, null, __FILE__, __LINE__);
							$existing = array(
								'{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
								'{{ip}}' => IPADDRESS,
								'{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
								'{{cost}}' => $ilance->currency->format($ilance->paypal->get_transaction_amount()),
								'{{invoiceid}}' => $deposit_invoice_id,
								'{{paymethod}}' => 'PayPal',
								'{{gateway}}' => 'PayPal',
								'{{txn_id}}' => $ilance->cashu->get_transaction_id()
							);
							$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
							$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
							$ilance->email->get('member_deposit_funds_creditcard');		
							$ilance->email->set($existing);
							$ilance->email->send();
							$ilance->email->mail = SITE_EMAIL;
							$ilance->email->slng = fetch_site_slng();
							$ilance->email->get('member_deposit_funds_creditcard_admin');		
							$ilance->email->set($existing);
							$ilance->email->send();
							
							($apihook = $ilance->api('payment_paypal_deposit_is_verified')) ? eval($apihook) : false;
						}		
					}
				}
			}
		}
		// #### HANDLE DIRECT PAYMENTS #################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
		{
			($apihook = $ilance->api('payment_paypal_direct')) ? eval($apihook) : false;
			
			// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
			if ($ilance->paypal->is_verified())
			{
				$area_title = 'PayPal IPN<div class="smaller">DIRECT : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_direct_reversed_refunded')) ? eval($apihook) : false;
				}
				else
				{
					$ilance->accounting_payment->invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->paypal->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'paypal', $ilance->paypal->get_transaction_id(), false, '', true);
				}
				
				($apihook = $ilance->api('payment_paypal_direct_is_verified')) ? eval($apihook) : false;
				
			}
		}
		// #### HANDLE ADMIN SELLER PAYMENTS FOR BUY NOW ITEMS #########
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'BUYNOW')
		{
			$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
			if ($ilance->paypal->is_verified() AND isset($ilance->GPC['orderid']) AND $ilance->GPC['orderid'] > 0 AND isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
			{
				$area_title = 'PayPal IPN<div class="smaller">BUYNOW : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "buynow_orders
					SET paiddate = '" . DATETIME24H . "',
					winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
						AND project_id = '" . intval($ilance->GPC['projectid']) . "'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				
				($apihook = $ilance->api('payment_paypal_buynow_win')) ? eval($apihook) : false;
			}
		}
		// #### HANDLE ADMIN SELLER PAYMENTS FOR WON ITEMS #############
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'ITEMWIN')
		{
			$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
			if ($ilance->paypal->is_verified() AND isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
			{
				$area_title = 'PayPal IPN<div class="smaller">ITEMWIN : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_bids
					SET winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
						AND bidstatus = 'awarded'
						AND state = 'product'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "project_realtimebids
					SET winnermarkedaspaid = '1',
					winnermarkedaspaiddate = '" . DATETIME24H . "',
					winnermarkedaspaidmethod = 'PayPal'
					WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
						AND bidstatus = 'awarded'
						AND state = 'product'
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				
				($apihook = $ilance->api('payment_paypal_item_win')) ? eval($apihook) : false;
			}
		}
	}
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER EXTERNAL PAYMENTS FROM BUYERS
	// this type of payment would be a direct buyer to seller payment for an item purchased through buy now
	// this area below will focus on simply updating the transaction as "paid" for the purchased items if applicable
	else if (isset($ilance->GPC['custom']) AND !empty($ilance->GPC['custom']))
	{
		$custom = isset($ilance->GPC['custom']) ? urldecode($ilance->GPC['custom']) : '';
		$custom = explode('|', $custom);
		$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
		$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
		if ($ilance->paypal->is_verified())
		{
			if (!empty($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'BUYNOW' AND isset($ilance->GPC['orderid']) AND $ilance->GPC['orderid'] > 0 AND isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
			{
				$area_title = 'PayPal IPN<div class="smaller">BUYNOW : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_buynow_win_refunded')) ? eval($apihook) : false;
				}
				else
				{
					// #### update our buy now purchase as being paid in full
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "buynow_orders
						SET paiddate = '" . DATETIME24H . "',
						winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = 'PayPal'
						WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
							AND project_id = '" . intval($ilance->GPC['projectid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					
					($apihook = $ilance->api('payment_paypal_buynow_win')) ? eval($apihook) : false;
				}
			}
			else if (!empty($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'ITEMWIN' AND isset($ilance->GPC['projectid']) AND $ilance->GPC['projectid'] > 0)
			{
				$area_title = 'PayPal IPN<div class="smaller">ITEMWIN : ' . $ilance->paypal->get_transaction_type() . ' (' . $ilance->paypal->get_payment_status() . ')</div>';
				if ($ilance->paypal->get_payment_status() == 'Reversed' OR $ilance->paypal->get_payment_status() == 'Refunded')
				{
					($apihook = $ilance->api('payment_paypal_item_win_refunded')) ? eval($apihook) : false;
				}
				else
				{
					// #### update our listing as the buyer paying the seller in full
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_bids
						SET winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = 'PayPal'
						WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
							AND bidstatus = 'awarded'
							AND state = 'product'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "project_realtimebids
						SET winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = 'PayPal'
						WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
							AND bidstatus = 'awarded'
							AND state = 'product'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					
					($apihook = $ilance->api('payment_paypal_item_win')) ? eval($apihook) : false;
				}
			}
		}
	}
	if (empty($ilance->paypal->paypal_post_vars))
	{
		$ilance->paypal->paypal_post_vars = array();
	}
	@reset($ilance->paypal->paypal_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->paypal->paypal_post_vars))
	{
		// skip our do=_paypal query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('paypal_external_payment_received_admin');		
	$ilance->email->set(array(
		'{{response}}' => $responsecodes,
		'{{gateway}}' => 'PayPal',
	));
	$ilance->email->send();
	
	($apihook = $ilance->api('payment_paypal_end')) ? eval($apihook) : false;
}
// #### CASHU RESPONSE HANDLER #################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_cashu')
{
	$area_title = 'CashU IPN';
	if ($ilconfig['cashu_active'] == false)
	{
		$area_title = 'CashU IPN<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
	$ilance->cashu->error_email = SITE_EMAIL;
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	if (isset($ilance->GPC['txt2']) AND !empty($ilance->GPC['txt2']))
	{
		$custom = isset($ilance->GPC['txt2']) ? urldecode($ilance->GPC['txt2']) : '';
		$custom = explode('|', $custom);
		$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
		$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_cashu_buynow_win')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'CashU'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_cashu_item_win')) ? eval($apihook) : false;
		}
	}
	// break down custom response
	$custom = isset($ilance->GPC['txt2']) ? urldecode($ilance->GPC['txt2']) : '';
	// decrypt our custom response originally sent to cashu regarding our transaction details
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		$area_title = 'CashU IPN<div class="smaller">This script requires well-formed parameters.  Operation aborted.</div>';
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
	$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
	$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
	$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
	$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : '';
	$ilance->GPC['length'] = isset($custom[4]) ? intval($custom[4]) : 0;
	$ilance->GPC['units'] = isset($custom[5]) ? $custom[5] : 0;
	$ilance->GPC['subscriptionid'] = isset($custom[6]) ? intval($custom[6]) : 0;
	$ilance->GPC['cost'] = isset($custom[7]) ? $custom[7] : 0;
	$ilance->GPC['roleid'] = isset($custom[8]) ? intval($custom[8]) : '-1';
	// #### CASHU SUBSCRIPTION PAYMENT (REGULAR) ###########################
	// note: cashU does not support recurring subscription payment
	if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
	{
		($apihook = $ilance->api('payment_cashu_subscription')) ? eval($apihook) : false;
		
		// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			$area_title = 'CashU IPN<div class="smaller">SUBSCRIPTION : ' . $this->cashu->get_transaction_id() . ')</div>';
			// #### COMPLETED SUBSCRIPTION PAYMENT #################
			// this IPN will trigger when the member received email via cron
			// regarding unpaid invoice so they click the link in email
			// go to cashu and make payment .. the ipn handler is told to come here
			// and verify/update account to active
			if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
			{
				$sql = $ilance->db->query("
					SELECT invoiceid
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						AND user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoicetype = 'subscription'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$sql_invoice_array = $ilance->db->fetch_array($sql, DB_ASSOC);
					$sql_user = $ilance->db->query("
						SELECT username, email
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_user) > 0)
					{
						$res_user = $ilance->db->fetch_array($sql_user);
						// update subscription invoice as paid in full
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET paid = '" . sprintf("%01.2f", $ilance->cashu->get_transaction_amount()) . "',
							status = 'paid',
							paymethod = 'cashu',
							paiddate = '" . DATETIME24H . "',
							referer = '" . $ilance->db->escape_string(REFERRER) . "',
							custommessage = '" . $ilance->db->escape_string($ilance->cashu->get_transaction_id()) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						", 0, null, __FILE__, __LINE__);
						// adjust members total amount paid for subscription plan
						$ilance->accounting_payment->insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->cashu->get_transaction_amount()), 'credit');
						// update customers subscription to active
						$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '".intval($ilance->GPC['userid'])."'", "subscriptionid");
						$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '".$subscriptionid."'", "units");
						$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '".$subscriptionid."'", "length");
						$subscription_length = $ilance->subscription->subscription_length($units, $length);
						$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							paymethod = 'cashu',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $subscription_renew_date . "',
							invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// #### REFERRAL SYSTEM TRACKER ############################
						$ilance->referral->update_referral_action('subscription', intval($ilance->GPC['userid']));
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('subscription_paid_via_paypal_admin');		
						$ilance->email->set(array(
							'{{provider}}' => ucfirst($res_user['username']),
							'{{invoice_id}}' => $ilance->GPC['invoiceid'],
							'{{invoice_amount}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
							'{{paymethod}}' => 'CashU',
						));
						$ilance->email->send();
						$ilance->email->mail = $res_user['email'];
						$ilance->email->slng = fetch_user_slng($res_user['user_id']);
						$ilance->email->get('subscription_paid_via_paypal');		
						$ilance->email->set(array(
							'{{provider}}' => ucfirst($res_user['username']),
							'{{invoice_id}}' => $ilance->GPC['invoiceid'],
							'{{invoice_amount}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
							'{{paymethod}}' => 'CashU',
						));
						$ilance->email->send();
						
						($apihook = $ilance->api('payment_cashu_subscription_is_verified')) ? eval($apihook) : false;
					}
				}	
			}
		}
	}
	// #### HANDLE DEPOSIT PAYMENTS ################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
	{
		($apihook = $ilance->api('payment_cashu_deposit')) ? eval($apihook) : false;
		
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			$area_title = 'CashU IPN<div class="smaller">DEPOSIT : ' . $this->cashu->get_transaction_id() . ')</div>';
			// just in case cashu decides to ping the site twice or more with this payment
			// let's do a quick txn_id checkup so we don't double credit the member!
			// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
			$validtransaction = 1;
			if ($ilance->accounting_payment->is_duplicate_txn_id($ilance->cashu->get_transaction_id()))
			{
				$validtransaction = 0;
			}
			if ($validtransaction)
			{
				// select amount for existing user
				$accountbal = $ilance->db->query("
					SELECT total_balance, available_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($accountbal) > 0)
				{
					$sel_account_result = $ilance->db->fetch_array($accountbal);
					$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
					$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
					// construct new deposit transaction
					$deposit_invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						'{_account_deposit_credit_via}' . ' CashU [TXN_ID: ' . $ilance->cashu->get_transaction_id() . '] {_into_online_account}: ' . $ilance->currency->format($ilance->GPC['creditamount']),
						$ilance->cashu->get_transaction_amount(),
						$ilance->cashu->get_transaction_amount(),
						'paid',
						'credit',
						'cashu',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->cashu->get_transaction_id(),
						0,
						0,
						1,
						'',
						1,
						0
					);
					// update the transaction with the acual amount we're crediting this user for
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoiceid = '" . intval($deposit_invoice_id) . "'
					", 0, null, __FILE__, __LINE__);
					// update customers online account balance information
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
						total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					$existing = array(
						'{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						'{{ip}}' => IPADDRESS,
						'{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						'{{cost}}' => $ilance->currency->format($ilance->cashu->get_transaction_amount()),
						'{{invoiceid}}' => $deposit_invoice_id,
						'{{paymethod}}' => 'CashU',
						'{{gateway}}' => 'CashU',
                                                '{{txn_id}}' => $ilance->cashu->get_transaction_id()
					);
					
					$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
					$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
					$ilance->email->get('member_deposit_funds_creditcard');		
					$ilance->email->set($existing);
					$ilance->email->send();
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('member_deposit_funds_creditcard_admin');		
					$ilance->email->set($existing);
					$ilance->email->send();
					
					($apihook = $ilance->api('payment_cashu_deposit_isverified')) ? eval($apihook) : false;
				}		
			}
		}
	}
	// #### HANDLE DIRECT PAYMENTS #########################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
	{
		($apihook = $ilance->api('payment_cashu_direct')) ? eval($apihook) : false;
		
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		if ($ilance->cashu->is_verified())
		{
			$area_title = 'CashU IPN<div class="smaller">DIRECT : ' . $this->cashu->get_transaction_id() . ')</div>';
			$ilance->accounting_payment->invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->cashu->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'cashu', $ilance->cashu->get_transaction_id(), false, '', true);
			
			($apihook = $ilance->api('payment_cashu_direct_is_verified')) ? eval($apihook) : false;
		}
	}
	if (empty($ilance->cashu->cashu_post_vars))
	{
		$ilance->cashu->cashu_post_vars = array();
	}
	@reset($ilance->cashu->cashu_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->cashu->cashu_post_vars))
	{
		// skip our do=_cashu query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('paypal_external_payment_received_admin');		
	$ilance->email->set(array(
		'{{response}}' => $responsecodes,
		'{{gateway}}' => 'CashU',
	));
	$ilance->email->send();
	
	($apihook = $ilance->api('payment_cashu_end')) ? eval($apihook) : false;
}
// #### MONEYBOOKERS RESPONSE HANDLER ##########################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_moneybookers')
{
	$area_title = 'Skrill IPN';
	if ($ilconfig['moneybookers_active'] == false)
	{
		$area_title = 'Skrill IPN<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
	$ilance->moneybookers->error_email = SITE_EMAIL;
	// #### HANDLE BUY NOW ITEM PURCHASE FOR SELLER AUTOMATION #############
	if (isset($ilance->GPC['merchant_fields']) AND !empty($ilance->GPC['merchant_fields']))
	{
		$custom = isset($ilance->GPC['merchant_fields']) ? urldecode($ilance->GPC['merchant_fields']) : '';
		$custom = explode('|', $custom);
		$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] 	  : '';
		$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
		$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_moneybookers_buynow_win')) ? eval($apihook) : false;
		}
		else if ($ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'MoneyBookers'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			
			($apihook = $ilance->api('payment_moneybookers_item_win')) ? eval($apihook) : false;
		}
	}
	$custom = isset($ilance->GPC['merchant_fields']) ? urldecode($ilance->GPC['merchant_fields']) : '';
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		$area_title = 'Skrill IPN<div class="smaller">This script requires well-formed parameters.  Operation aborted.</div>';
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
	$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
	$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
	$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
	$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
	$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : '';
	$ilance->GPC['length'] = isset($custom[4]) ? intval($custom[4]) : 0;
	$ilance->GPC['units'] = isset($custom[5]) ? $custom[5] : 0;
	$ilance->GPC['subscriptionid'] = isset($custom[6]) ? intval($custom[6]) : 0;
	$ilance->GPC['cost'] = isset($custom[7]) ? $custom[7] : 0;
	$ilance->GPC['roleid'] = isset($custom[8]) ? intval($custom[8]) : '-1';
	// #### MONEYBOOKERS RECURRING SUBSCRIPTION PAYMENT HANDLER ##########
	if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'RECURRINGSUBSCRIPTION' AND $ilconfig['moneybookers_subscriptions'])
	{
		($apihook = $ilance->api('payment_moneybookers_recurring')) ? eval($apihook) : false;
		
		// #### RECURRINGSUBSCRIPTION|USERID|0|0|LENGTH|UNITS|SUBSCRIPTIONID|COST|ROLEID #########
		if ($ilance->moneybookers->get_recurring_transaction_type() == 'recurring')
		{
			if ($ilance->moneybookers->is_verified())
			{
				$area_title = 'Skrill IPN<div class="smaller">RECURRINGSUBSCRIPTION : ' . $this->moneybookers->get_transaction_id() . ' (' . $ilance->moneybookers->get_recurring_transaction_type() . ')</div>';
				// moneybookers tells us this is a subscription payment notification
				if ($ilance->GPC['userid'] > 0)
				{
					// #### COMPLETED SUBSCRIPTION PAYMENT
					// update new subscription
					
					$startdate = DATETIME24H;
					$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
					$recurring = 1;
					$paymethod = 'moneybookers';
					// create new invoice associated with this paypal subscription transaction
					$invoiceid = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						$ilance->GPC['item_name'] . ' [SUBSCR_ID: ' . $ilance->moneybookers->get_transaction_id() . ']',
						sprintf("%01.2f", $ilance->GPC['cost']),
						sprintf("%01.2f", $ilance->GPC['cost']),
						'paid',
						'debit',
						'moneybookers',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->moneybookers->get_transaction_id(),
						0,
						0,
						1,
						'',
						0,
						0
					);
					// activate subscription plan
					$ilance->subscription_plan->activate_subscription_plan($ilance->GPC['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['cost']);
					// #### REFERRAL SYSTEM TRACKER ############################
					$ilance->referral->update_referral_action('subscription', $ilance->GPC['userid']);
					
					($apihook = $ilance->api('payment_moneybookers_recurring_is_verified')) ? eval($apihook) : false;
				}
			}
			else if ($ilance->moneybookers->get_payment_status() == 'CANCEL' OR $ilance->moneybookers->get_payment_status() == 'CHARGEBACK' OR $ilance->moneybookers->get_payment_status() == 'REFUND')
			{
				($apihook = $ilance->api('payment_moneybookers_recurring_cancel_chargeback_refund')) ? eval($apihook) : false;
				
				// #### deactivate members subscription ################
				$ilance->subscription_plan->deactivate_subscription_plan($ilance->GPC['userid']);
				// #### send email #####################################
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('recurring_subscription_cancelled_admin');		
				$ilance->email->set(array(
					'{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					'{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					'{{gateway}}' => 'MoneyBookers',
					'{{txn_type}}' => $ilance->moneybookers->get_payment_status(),
				));
				$ilance->email->send();
				$ilance->email->mail = fetch_user('email', $ilance->GPC['userid']);
				$ilance->email->slng = fetch_user_slng($ilance->GPC['userid']);
				$ilance->email->get('recurring_subscription_cancelled');		
				$ilance->email->set(array(
					'{{username}}' => fetch_user('username', $ilance->GPC['userid']),
					'{{memberemail}}' => fetch_user('email', $ilance->GPC['userid']),
					'{{gateway}}' => 'MoneyBookers',
					'{{txn_type}}' => $ilance->moneybookers->get_payment_status(),
				));
				$ilance->email->send();
			}
		}
	}
	// #### MONEYBOOKERS SUBSCRIPTION PAYMENT (REGULAR) ####################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'SUBSCRIPTION')
	{
		($apihook = $ilance->api('payment_moneybookers_subscription')) ? eval($apihook) : false;
		
		// SUBSCRIPTION|USERID|INVOICEID|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			$area_title = 'Skrill IPN<div class="smaller">SUBSCRIPTION : ' . $this->moneybookers->get_transaction_id() . '</div>';
			#### COMPLETED SUBSCRIPTION PAYMENT ##############
			
			// this IPN will trigger when the member received email via cron
			// regarding unpaid invoice so they click the link in email
			// go to moneybookers and make payment .. the ipn handler is told to come here
			// and verify/update account to active
			if ($ilance->GPC['userid'] > 0 AND $ilance->GPC['invoiceid'] > 0)
			{
				$sql = $ilance->db->query("
					SELECT invoiceid
					FROM " . DB_PREFIX . "invoices
					WHERE invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
						AND user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoicetype = 'subscription'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$sql_invoice_array = $ilance->db->fetch_array($sql, DB_ASSOC);
					$sql_user = $ilance->db->query("
						SELECT username, email
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_user) > 0)
					{
						$res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET paid = '" . sprintf("%01.2f", $ilance->moneybookers->get_transaction_amount()) . "',
							status = 'paid',
							paymethod = 'moneybookers',
							paiddate = '" . DATETIME24H . "',
							referer = '" . $ilance->db->escape_string(REFERRER) . "',
							custommessage = '" . $ilance->db->escape_string($ilance->moneybookers->get_transaction_id()) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
								AND invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// adjust members total amount paid for subscription plan
						$ilance->accounting_payment->insert_income_spent(intval($ilance->GPC['userid']), sprintf("%01.2f", $ilance->moneybookers->get_transaction_amount()), 'credit');
						// update customers subscription to active
						$subscriptionid = $ilance->db->fetch_field(DB_PREFIX . "subscription_user", "user_id = '" . intval($ilance->GPC['userid']) . "'", "subscriptionid");
						$units = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "units");
						$length = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $subscriptionid . "'", "length");
						$subscription_length = $ilance->subscription->subscription_length($units, $length);
						$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "subscription_user
							SET active = 'yes',
							paymethod = 'moneybookers',
							startdate = '" . DATETIME24H . "',
							renewdate = '" . $subscription_renew_date . "',
							invoiceid = '" . intval($ilance->GPC['invoiceid']) . "'
							WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// #### REFERRAL SYSTEM TRACKER ############################
						$ilance->referral->update_referral_action('subscription', intval($ilance->GPC['userid']));
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('subscription_paid_via_paypal_admin');		
						$ilance->email->set(array(
							'{{provider}}' => ucfirst($res_user['username']),
							'{{invoice_id}}' => $ilance->GPC['invoiceid'],
							'{{invoice_amount}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
							'{{paymethod}}' => 'MoneyBookers',
						));
						$ilance->email->send();
						$ilance->email->mail = $res_user['email'];
						$ilance->email->slng = fetch_user_slng($res_user['user_id']);
						$ilance->email->get('subscription_paid_via_paypal');		
						$ilance->email->set(array(
							'{{provider}}' => ucfirst($res_user['username']),
							'{{invoice_id}}' => $ilance->GPC['invoiceid'],
							'{{invoice_amount}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
							'{{paymethod}}' => 'MoneyBookers',
						));
						$ilance->email->send();
						
						($apihook = $ilance->api('payment_moneybookers_subscription_is_verified')) ? eval($apihook) : false;
					}
				}
			}
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			$area_title = 'Skrill IPN<div class="smaller">SUBSCRIPTION : ' . $this->moneybookers->get_transaction_id() . ' (CHARGEBACK)</div>';
			
			($apihook = $ilance->api('payment_moneybookers_subscription_chargeback')) ? eval($apihook) : false;
			
			// #### deactivate members subscription ########
			$ilance->subscription_plan->deactivate_subscription_plan($ilance->GPC['userid']);
		}
	}
	// #### HANDLE DEPOSIT PAYMENTS ################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
	{
		($apihook = $ilance->api('payment_moneybookers_deposit')) ? eval($apihook) : false;
		
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			$area_title = 'Skrill IPN<div class="smaller">DEPOSIT : ' . $this->moneybookers->get_transaction_id() . '</div>';
			// just in case moneybookers decides to ping the site twice or more with this payment
			// let's do a quick txn_id checkup so we don't double credit the member!
			// based on report: http://www.ilance.ca/forum/showthread.php?t=2429
			$validtransaction = 1;
			if ($ilance->accounting_payment->is_duplicate_txn_id($ilance->moneybookers->get_transaction_id()))
			{
				$validtransaction = 0;
			}
			if ($validtransaction)
			{
				// select amount for existing user
				$accountbal = $ilance->db->query("
					SELECT total_balance, available_balance
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($accountbal) > 0)
				{
					$sel_account_result = $ilance->db->fetch_array($accountbal);
					$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
					$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
					// construct new deposit transaction
					$deposit_invoice_id = $ilance->accounting->insert_transaction(
						0,
						0,
						0,
						intval($ilance->GPC['userid']),
						0,
						0,
						0,
						'{_account_deposit_credit_via}' . ' MoneyBookers [TXN_ID: ' . $ilance->moneybookers->get_transaction_id() . '] {_into_online_account}: ' . $ilance->currency->format($ilance->GPC['creditamount']),
						$ilance->moneybookers->get_transaction_amount(),
						$ilance->moneybookers->get_transaction_amount(),
						'paid',
						'credit',
						'moneybookers',
						DATETIME24H,
						DATEINVOICEDUE,
						DATETIME24H,
						$ilance->moneybookers->get_transaction_id(),
						0,
						0,
						1,
						'',
						1,
						0
					);
					// update the transaction with the acual amount we're crediting this user for
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
							AND invoiceid = '" . intval($deposit_invoice_id) . "'
					", 0, null, __FILE__, __LINE__);
					// update customers online account balance information
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
						total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
						WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
					", 0, null, __FILE__, __LINE__);
					$existing = array(
                                                '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
						'{{ip}}' => IPADDRESS,
						'{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
						'{{cost}}' => $ilance->currency->format($ilance->moneybookers->get_transaction_amount()),
						'{{invoiceid}}' => $deposit_invoice_id,
						'{{paymethod}}' => 'MoneyBookers',
						'{{gateway}}' => 'MoneyBookers',
                                                '{{txn_id}}' => $ilance->moneybookers->get_transaction_id()
                                        );
					$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
					$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
					$ilance->email->get('member_deposit_funds_creditcard');		
					$ilance->email->set($existing);
					$ilance->email->send();
					$ilance->email->mail = SITE_EMAIL;
					$ilance->email->slng = fetch_site_slng();
					$ilance->email->get('member_deposit_funds_creditcard_admin');		
					$ilance->email->set($existing);
					$ilance->email->send();
					
					($apihook = $ilance->api('payment_moneybookers_deposit_is_verified')) ? eval($apihook) : false;
				}
			}
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			// unused
			($apihook = $ilance->api('payment_moneybookers_deposit_chargeback')) ? eval($apihook) : false;
		}
	}
	// #### HANDLE DIRECT PAYMENTS #########################################
	else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
	{
		($apihook = $ilance->api('payment_moneybookers_direct')) ? eval($apihook) : false;
		
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		if ($ilance->moneybookers->is_verified())
		{
			$area_title = 'Skrill IPN<div class="smaller">DIRECT : ' . $this->moneybookers->get_transaction_id() . '</div>';
			$ilance->accounting_payment->invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $ilance->moneybookers->get_transaction_amount(), $ilance->GPC['userid'], 'ipn', 'moneybookers', $ilance->moneybookers->get_transaction_id(), false, '', true);
			
			($apihook = $ilance->api('payment_moneybookers_direct_is_verified')) ? eval($apihook) : false;
		}
		else if ($ilance->moneybookers->get_payment_status() == 'CHARGEBACK')
		{
			$area_title = 'Skrill IPN<div class="smaller">DIRECT : ' . $this->moneybookers->get_transaction_id() . ' (CHARGEBACK)</div>';
			
			($apihook = $ilance->api('payment_moneybookers_direct_chargeback')) ? eval($apihook) : false;
		}
	}
	if (empty($ilance->moneybookers->moneybookers_post_vars))
	{
		$ilance->moneybookers->moneybookers_post_vars = array();
	}
	@reset($ilance->moneybookers->moneybookers_post_vars);
	$responsecodes = '';
	while (@list($key, $value) = @each($ilance->moneybookers->moneybookers_post_vars))
	{
		// skip our do=_moneybookers query
		if (!empty($key) AND $key != 'do')
		{
			$responsecodes .= $key . ':' . " \t$value\n";
		}
	}
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('paypal_external_payment_received_admin');		
	$ilance->email->set(array(
		'{{response}}' => $responsecodes,
		'{{gateway}}' => 'MoneyBookers/Skrill',
	));
	$ilance->email->send();
	
	($apihook = $ilance->api('payment_moneybookers_end')) ? eval($apihook) : false;
}
// #### AUTHORIZE.NET ABR RESPONSE HANDLER #####################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_authorizenet')
{
	$area_title = 'Authorize.Net Gateway';
	if ($ilconfig['authnet_enabled'] == false)
	{
		$area_title = 'Authorize.Net Gateway<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->authorizenet = construct_object('api.authorizenet', $ilance->GPC);
	$ilance->authorizenet->error_email = SITE_EMAIL;
	$ilance->authorizenet->timeout = 120;
	$custom = isset($ilance->GPC['refId']) ? urldecode($ilance->GPC['refId']) : '1';
	$subscriptionid = isset($ilance->GPC['subscriptionid']) ? $ilance->GPC['subscriptionid'] : '0';
	$ilance->GPC['roleid'] = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '-1';
	$ilance->GPC['amount'] = $ilance->GPC['amount'];
	$ilance->GPC['name'] = $ilance->GPC['name'];
	$ilance->GPC['length'] = $ilance->GPC['length'];
	$ilance->GPC['unit'] = $ilance->GPC['unit'];
	$ilance->GPC['units'] = $ilance->GPC['units'];
	$ilance->GPC['startDate'] = $ilance->GPC['startDate'];
	$ilance->GPC['totalOccurrences'] = $ilance->GPC['totalOccurrences'];
	$ilance->GPC['trialOccurrences'] = $ilance->GPC['trialOccurrences'];
	$ilance->GPC['trialAmount'] = $ilance->GPC['trialAmount'];
	$ilance->GPC['cardNumber'] = $ilance->GPC['cardNumber'];
	$ilance->GPC['expirationDate'] = $ilance->GPC['creditcard_year'] . '-' . $ilance->GPC['creditcard_month'];
	$ilance->GPC['firstName'] = $ilance->GPC['firstName'];
	$ilance->GPC['lastName'] = $ilance->GPC['lastName'];
	$ilance->GPC['cardType'] = $ilance->GPC['cardType'];
	// #### build our special recurring subscription xml data ##############
	$xml = $ilance->authorizenet->build_recurring_subscription_xml($ilance->GPC['mode'], $ilance->GPC, 'authnet');
	$method = 'curl'; // curl or fsockopen can be used
	// #### post and fetch gateway response ################################
	if (empty($xml))
	{
		$area_title = 'Authorize.Net Gateway<div class="smaller">{_payment_gateway_communication_error}</div>';
		$page_title = SITE_NAME . ' - {_payment}';
		$navcrumb = array();
		$navcrumb["$ilpage[subscription]"] = '{_payment}';
		$navcrumb[""] = '{_notice}';
		$transaction_message = '{_could_not_build_a_valid_payment_gateway_response}';
		$date_time = DATETIME24H;
		$pprint_array = array('date_time','transaction_message','transaction_code');
		$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	$url = ($ilconfig['authnet_test'] == '1') ? 'https://apitest.authorize.net' : 'https://api.authorize.net';
	$gatewayresponse = $ilance->authorizenet->send_response($method, $xml, $url, '/xml/v1/request.api');
	if (!empty($gatewayresponse) AND $gatewayresponse != false)
	{
		$refId = $resultCode = $code = $text = $subscriptionId = '';
		list ($refId, $resultCode, $code, $text, $subscriptionId) = $ilance->authorizenet->parse_return($gatewayresponse);
		if (strtolower($resultCode) == 'ok')
		{
			$startdate = DATETIME24H;
			$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
			$recurring = 1;
			$paymethod = $ilance->GPC['cardType'];
			$invoiceid = $ilance->accounting->insert_transaction(
				0,
				0,
				0,
				$_SESSION['ilancedata']['user']['userid'],
				0,
				0,
				0,
				$ilance->GPC['name'] . ' [SUBSCR_ID: ' . $subscriptionid . ']',
				sprintf("%01.2f", $ilance->GPC['amount']),
				sprintf("%01.2f", $ilance->GPC['amount']),
				'paid',
				'debit',
				$paymethod,
				DATETIME24H,
				DATEINVOICEDUE,
				DATETIME24H,
				$refId,
				0,
				0,
				1,
				'',
				0,
				0
			);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET paymentgateway = 'authnet'
				WHERE invoiceid = '" . intval($invoiceid) . "'
			", 0, null, __FILE__, __LINE__);
			$ilance->subscription_plan->activate_subscription_plan($_SESSION['ilancedata']['user']['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['amount']);
			$ilance->referral->update_referral_action('subscription', $_SESSION['ilancedata']['user']['userid']);
			refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=completed');
			exit();
		}
		else
		{
			$area_title = 'Authorize.Net Gateway<div class="smaller">{_payment_gateway_communication_error}</div>';
			$page_title = SITE_NAME . ' - {_payment}';
			$navcrumb = array();
			$navcrumb["$ilpage[subscription]"] = '{_payment}';
			$navcrumb[""] = '{_notice}';
			$transaction_message = $text;
			$date_time = DATETIME24H;
			$pprint_array = array('date_time','transaction_message','transaction_code');
			$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	else
	{
		$area_title = 'Authorize.Net Gateway<div class="smaller">{_payment_gateway_communication_error}</div>';
		$page_title = SITE_NAME . ' - {_payment}';
		$navcrumb = array();
		$navcrumb["$ilpage[subscription]"] = '{_payment}';
		$navcrumb[""] = '{_notice}';
		$transaction_message = '{_could_not_communicate_with_payment_gateway}';
		$date_time = DATETIME24H;
		$pprint_array = array('date_time','transaction_message','transaction_code');
		$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### BLUEPAY 2.0 RESPONSE HANDLER ###########################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_bluepay')
{
	$area_title = 'BluePay Gateway';
	if ($ilconfig['bluepay_enabled'] == false)
	{
		$area_title = 'BluePay Gateway<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->bluepay = construct_object('api.bluepay', $ilance->GPC);
	$ilance->bluepay->error_email = SITE_EMAIL;
	$ilance->bluepay->timeout = 120;
	$ilance->GPC['subscriptionid'] = $ilance->GPC['subscriptionid'];
	$ilance->GPC['roleid'] = isset($ilance->GPC['roleid']) ? intval($ilance->GPC['roleid']) : '-1';
	$ilance->GPC['amount'] = $ilance->GPC['amount'];
	$ilance->GPC['name'] = $ilance->GPC['name'];
	$ilance->GPC['length'] = $ilance->GPC['length'];
	$ilance->GPC['unit'] = $ilance->GPC['unit'];
	$ilance->GPC['units'] = $ilance->GPC['units'];
	$ilance->GPC['startDate'] = $ilance->GPC['startDate'];
	$ilance->GPC['totalOccurrences'] = $ilance->GPC['totalOccurrences'];
	$ilance->GPC['trialOccurrences'] = $ilance->GPC['trialOccurrences'];
	$ilance->GPC['trialAmount'] = $ilance->GPC['trialAmount'];
	$ilance->GPC['cardNumber'] = $ilance->GPC['cardNumber'];
	$ilance->GPC['expirationDate'] = $ilance->GPC['creditcard_year'] . '-' . $ilance->GPC['creditcard_month'];
	$ilance->GPC['firstName'] = $ilance->GPC['firstName'];
	$ilance->GPC['lastName'] = $ilance->GPC['lastName'];
	$ilance->GPC['cardType'] = $ilance->GPC['cardType'];
	$ilance->bluepay->rebAdd();
	$ilance->bluepay->setCustInfo();
	$ilance->bluepay->process();
	$array = $ilance->bluepay->parseResponse();
	if (isset($array['ORDER_ID']) AND $ilance->bluepay->orderId == $array['ORDER_ID'])
	{
		if ($array['Result'] == 'APPROVED')
		{
			$startdate = DATETIME24H;
			$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($ilance->GPC['units'], $ilance->GPC['length']));
			$recurring = 1;
			$paymethod = $ilance->GPC['cardType'];
			$invoiceid = $ilance->accounting->insert_transaction(
				$ilance->GPC['subscriptionid'],
				0,
				0,
				$_SESSION['ilancedata']['user']['userid'],
				0,
				0,
				0,
				$ilance->GPC['name'] . ' [SUBSCR_ID: ' . $array['ORDER_ID'] . ']',
				sprintf("%01.2f", $ilance->GPC['amount']),
				sprintf("%01.2f", $ilance->GPC['amount']),
				'paid',
				'debit',
				$paymethod,
				DATETIME24H,
				DATEINVOICEDUE,
				DATETIME24H,
				$array['RRNO'] . '|' . $array['REBID'] . '|' . $array['AUTH_CODE'],
				0,
				0,
				1,
				'',
				0,
				0
			);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "invoices
				SET paymentgateway = 'BluePay'
				WHERE invoiceid = '" . intval($invoiceid) . "'
			", 0, null, __FILE__, __LINE__);
			$ilance->subscription_plan->activate_subscription_plan($_SESSION['ilancedata']['user']['userid'], $startdate, $renewdate, $recurring, $invoiceid, $ilance->GPC['subscriptionid'], $paymethod, $ilance->GPC['roleid'], $ilance->GPC['amount']);
			$ilance->referral->update_referral_action('subscription', $_SESSION['ilancedata']['user']['userid']);
			refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=completed');
			exit();
		}
		else
		{
			$area_title = 'BluePay Gateway<div class="smaller">{_payment_gateway_communication_error}</div>';
			$page_title = SITE_NAME . ' - {_payment}';
			$navcrumb = array();
			$navcrumb["$ilpage[subscription]"] = '{_payment}';
			$navcrumb[""] = '{_notice}';
			$transaction_message = $array['MESSAGE'];
			$date_time = DATETIME24H;
			$pprint_array = array('date_time','transaction_message','transaction_code');
			$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
	else
	{
		$area_title = 'BluePay Gateway<div class="smaller">{_payment_gateway_communication_error}</div>';
		$page_title = SITE_NAME . ' - {_payment}';
		$navcrumb = array();
		$navcrumb["$ilpage[subscription]"] = '{_payment}';
		$navcrumb[""] = '{_notice}';
		$transaction_message = $array['MESSAGE'];
		$date_time = DATETIME24H;
		$pprint_array = array('date_time','transaction_message','transaction_code');
		$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### PLATNOSCI ##############################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_platnosci_online')
{
	$area_title = 'Platnosci IPN';
	if ($ilconfig['platnosci_active'] == false)
	{
		$area_title = 'Platnosci IPN<div class="smaller">This payment module is inactive.  Operation aborted.</div>';
		echo 'This payment module is inactive.  Operation aborted.';
		exit();
	}
	$ilance->platnosci = construct_object('api.platnosci', $ilance->GPC);
	$ilance->platnosci->error_email = SITE_EMAIL;
	$ilance->platnosci->timeout = 120;
	if (!isset($ilance->GPC['pos_id']) OR !isset($ilance->GPC['session_id']) OR !isset($ilance->GPC['ts']) OR !isset($ilance->GPC['sig']))
	{
		$area_title = 'Platnosci IPN<div class="smaller">ERROR: EMPTY PARAMETERS</div>';
		die('ERROR: EMPTY PARAMETERS');
	}
	if (isset($ilance->GPC['pos_id']) AND $ilance->GPC['pos_id'] != $ilconfig['platnosci_pos_id']) 
	{
		$area_title = 'Platnosci IPN<div class="smaller">ERROR: WRONG POS ID</div>';
		die('ERROR: WRONG POS ID');
	}
	$sig = md5($ilance->GPC['pos_id'] . $ilance->GPC['session_id'] . $ilance->GPC['ts'] . trim($ilconfig['platnosci_pos_key2']));
	if ($ilance->GPC['sig'] != $sig)
	{
		$area_title = 'Platnosci IPN<div class="smaller">ERROR: WRONG SIGNATURE</div>';
		die('ERROR: WRONG SIGNATURE');
	}
	$response = $ilance->platnosci->send_response($ilance->GPC['session_id']);
	if (is_array($response) AND $response[5] == '1')
	{
		$custom = isset($response[3]) ? urldecode($response[3]) : '';
		$custom = explode('|', $custom);
		$ilance->GPC['paymentlogic'] = !empty($custom[0]) ? $custom[0] : '';
		if ($ilance->GPC['paymentlogic'] == 'BUYNOW' OR $ilance->GPC['paymentlogic'] == 'ITEMWIN')
		{
			$ilance->GPC['orderid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['projectid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
			$amount = $response[4] / 100;
		}
		// #### DIRECT|USERID|INVOICEID|INVOICETYPE|0|0|0|0|0
		else if ($ilance->GPC['paymentlogic'] == 'DIRECT')
		{
			$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['invoiceid'] = !empty($custom[2]) ? intval($custom[2]) : 0;
			$ilance->GPC['invoicetype'] = !empty($custom[3]) ? $custom[3] : 0;
			$amount = $response[4] / 100;
		}
		// #### DEPOSIT|USERID|0|CREDITAMOUNT|0|0|0|0|0
		else if ($ilance->GPC['paymentlogic'] == 'DEPOSIT')
		{
			$ilance->GPC['userid'] = !empty($custom[1]) ? intval($custom[1]) : 0;
			$ilance->GPC['creditamount'] = !empty($custom[3]) ? $custom[3] : 0;
		}
		if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'BUYNOW' AND $ilance->GPC['orderid'] > 0 AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our buy now purchase as being paid in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET paiddate = '" . DATETIME24H . "',
				winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'Platnosci'
				WHERE orderid = '" . intval($ilance->GPC['orderid']) . "'
					AND project_id = '" . intval($ilance->GPC['projectid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$owner_id = $ilance->db->fetch_field(DB_PREFIX . "buynow_orders", "orderid = '" . intval($ilance->GPC['orderid']) . "'", "owner_id", "1");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET available_balance = available_balance + $amount,
				total_balance = total_balance + $amount
				WHERE user_id = '" . intval($owner_id) . "'
			");
			
			($apihook = $ilance->api('payment_platnosci_buynow_win')) ? eval($apihook) : false;
			
			$area_title = 'Platnosci IPN<div class="smaller">BUYNOW : {_invoice_payment_complete}</div>';
		}
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'ITEMWIN' AND $ilance->GPC['projectid'] > 0)
		{
			// #### update our listing as the buyer paying the seller in full
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'Platnosci'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET winnermarkedaspaid = '1',
				winnermarkedaspaiddate = '" . DATETIME24H . "',
				winnermarkedaspaidmethod = 'Platnosci'
				WHERE project_id = '" . intval($ilance->GPC['projectid']) . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$owner_id = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "project_id = '" . intval($ilance->GPC['projectid']) . "'", "project_user_id", "1");
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "users
				SET available_balance = available_balance + $amount,
				total_balance = total_balance + $amount
				WHERE user_id = '" . intval($owner_id) . "'
			");
			
			($apihook = $ilance->api('payment_platnosci_item_win')) ? eval($apihook) : false;
			
			$area_title = 'Platnosci IPN<div class="smaller">ITEMWIN : {_invoice_payment_complete}</div>';
		}
		// #### HANDLE DIRECT PAYMENTS #################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DIRECT')
		{
			($apihook = $ilance->api('payment_platnosci_direct')) ? eval($apihook) : false;
			
			$area_title = 'Platnosci IPN<div class="smaller">DIRECT : {_invoice_payment_complete}</div>';
			$ilance->accounting_payment->invoice_payment_handler($ilance->GPC['invoiceid'], $ilance->GPC['invoicetype'], $amount, $ilance->GPC['userid'], 'ipn', 'platnosci', '', false, '', true);
			
			($apihook = $ilance->api('payment_platnosci_direct_is_verified')) ? eval($apihook) : false;
		}
		// #### HANDLE DEPOSIT PAYMENTS ################################
		else if (isset($ilance->GPC['paymentlogic']) AND $ilance->GPC['paymentlogic'] == 'DEPOSIT')
		{
			($apihook = $ilance->api('payment_platnosci_deposit')) ? eval($apihook) : false;
					
			$accountbal = $ilance->db->query("
				SELECT total_balance, available_balance
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($accountbal) > 0)
			{
				$sel_account_result = $ilance->db->fetch_array($accountbal, DB_ASSOC);
				$new_credit_total_balance = ($sel_account_result['total_balance'] + $ilance->GPC['creditamount']);
				$new_credit_avail_balance = ($sel_account_result['available_balance'] + $ilance->GPC['creditamount']);
				$deposit_invoice_id = $ilance->accounting->insert_transaction(
					0,
					0,
					0,
					intval($ilance->GPC['userid']),
					0,
					0,
					0,
					'{_account_deposit_credit_via} {_platnosci} {_into_online_account}: ' . $ilance->currency->format($ilance->GPC['creditamount']),
					sprintf("%01.2f", $ilance->GPC['creditamount']),
					sprintf("%01.2f", $ilance->GPC['creditamount']),
					'paid',
					'credit',
					'platnosci',
					DATETIME24H,
					DATEINVOICEDUE,
					DATETIME24H,
					'',
					0,
					0,
					1,
					'',
					1,
					0
				);
				// update the transaction with the acual amount we're crediting this user for
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "invoices
					SET depositcreditamount = '" . sprintf("%01.2f", $ilance->GPC['creditamount']) . "'
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
						AND invoiceid = '" . intval($deposit_invoice_id) . "'
				", 0, null, __FILE__, __LINE__);
				// update customers online account balance information
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET available_balance = '" . sprintf("%01.2f", $new_credit_avail_balance) . "',
					total_balance = '" . sprintf("%01.2f", $new_credit_total_balance) . "'
					WHERE user_id = '" . intval($ilance->GPC['userid']) . "'
				", 0, null, __FILE__, __LINE__);
                                $existing = array(
                                        '{{username}}' => fetch_user('username', intval($ilance->GPC['userid'])),
					'{{ip}}' => IPADDRESS,
					'{{amount}}' => $ilance->currency->format(sprintf("%01.2f", $ilance->GPC['creditamount'])),
					'{{cost}}' => $ilance->currency->format($ilance->GPC['creditamount']),
					'{{invoiceid}}' => $deposit_invoice_id,
					'{{paymethod}}' => '{_platnosci}',
					'{{gateway}}' => '{_platnosci}',
                                        '{{txn_id}}' => '-'
                                );
				$area_title = 'Platnosci IPN<div class="smaller">DEPOSIT : {_invoice_payment_complete}</div>';
				$ilance->email->mail = fetch_user('email', intval($ilance->GPC['userid']));
				$ilance->email->slng = fetch_user_slng(intval($ilance->GPC['userid']));
				$ilance->email->get('member_deposit_funds_creditcard');		
				$ilance->email->set($existing);
				$ilance->email->send();
				$ilance->email->mail = SITE_EMAIL;
				$ilance->email->slng = fetch_site_slng();
				$ilance->email->get('member_deposit_funds_creditcard_admin');		
				$ilance->email->set($existing);
				$ilance->email->send();
				
				($apihook = $ilance->api('payment_platnosci_deposit_is_verified')) ? eval($apihook) : false;
			}
		}
	}
	else if (is_array($response) AND $response[5] == '99')
	{
		echo "OK";
		exit();
	}
}
// #### PLATNOSCI ##############################################################
else if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == '_platnosci')
{		
	if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == 'ok')
	{
		refresh(HTTPS_SERVER . $ilpage['accounting'] . '?note=completed');
		exit();
	}
	else 
	{
		$response = '';
		foreach ($ilance->GPC AS $key => $value)
		{
			$response .= $key . " => " . $value . "\n";
		}
		$show['error'] = true;
		$area_title = 'Platnosci IPN<div class="smaller">{_payment_gateway_communication_error}</div>';
		$page_title = SITE_NAME . ' - {_payment}';
		$transaction_message = '{_payment_gateway_communication_error}';
		$error_code = $response;
		$navcrumb = array();
		$navcrumb["$ilpage[subscription]"] = '{_payment}';
		$navcrumb[""] = '{_notice}';
		$date_time = DATETIME24H;
		@reset($ilance->GPC);
		$responsecodes = '';
		while (@list($key, $value) = @each($ilance->GPC))
		{
			// skip our do=_platnosci query
			if (!empty($key) AND $key != 'do')
			{
				$responsecodes .= $key . ':' . " \t$value\n";
			}
		}
		$ilance->email->mail = SITE_EMAIL;
		$ilance->email->slng = fetch_site_slng();
		$ilance->email->get('paypal_external_payment_received_admin');		
		$ilance->email->set(array(
			'{{response}}' => $responsecodes,
			'{{gateway}}' => 'Platnosci',
		));
		$ilance->email->send();
		$pprint_array = array('error_code','date_time','transaction_message','transaction_code');
		$ilance->template->fetch('main', 'print_notice_payment_gateway.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}

($apihook = $ilance->api('payment_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>