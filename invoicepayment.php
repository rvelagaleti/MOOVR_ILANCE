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
// #### load required javascript ###############################################
$jsinclude = array(
	'header' => array(
		'functions',
		'ajax',
		'inline',
		'jquery'
	),
	'footer' => array(
		'v4',
		'tooltip',
		'autocomplete',
		'cron'
	)
);

// #### define top header nav ##################################################
$topnavlink = array(
        'invoicepayment'
);

// #### setup script location ##################################################
define('LOCATION','invoicepayment');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[invoicepayment]" => $ilcrumbs["$ilpage[invoicepayment]"]);

// #### build our encrypted array for decoding purposes
$uncrypted = (!empty($ilance->GPC['crypted'])) ? decrypt_url($ilance->GPC['crypted']) : array();

if (empty($_SESSION['ilancedata']['user']['userid']) OR $_SESSION['ilancedata']['user']['userid'] == 0)
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['invoicepayment'] . print_hidden_fields(true, array(), true)));
	exit();
}

// #### USER WHO GENERATED AN INVOICE IS UPDATING THE STATUS ###################
if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == 'p2baction')
{
	// #### mark as paid ###########################################
	if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markaspaid' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '1',
			status = 'paid',
			paiddate = '" . DATETIME24H . "',
			paid = totalamount
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = '{_marking_provider_generated_invoice_as_paid}';
		$page_title = SITE_NAME . ' - {_marking_provider_generated_invoice_as_paid}';
		
		print_notice('{_invoice_marked_as_paid}', '{_you_have_successfully_confirmed_payment_status_on_this_generated_invoice}', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], '{_return_to_previous_menu}');
		exit(); 
	}
	
	// #### mark as unpaid #########################################
	else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markasunpaid' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '0',
			status = 'unpaid',
			paiddate = '0000-00-00 00:00:00',
			paid = '0.00'
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = '{_marking_provider_generated_invoice_as_unpaid}';
		$page_title = SITE_NAME . ' - {_marking_provider_generated_invoice_as_unpaid}';
		
		print_notice('{_invoice_marked_as_unpaid}', '{_you_have_set_the_payment_status_of_this_transaction_to_unpaid}', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], '{_return_to_previous_menu}');
		exit(); 
	}
	
	// #### mark as cancelled ######################################
	else if (isset($uncrypted['subcmd']) AND $uncrypted['subcmd'] == 'markascancelled' AND isset($uncrypted['invoiceid']) AND $uncrypted['invoiceid'] > 0 AND isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET p2b_markedaspaid = '0',
			status = 'cancelled',
			paiddate = '0000-00-00 00:00:00',
			paid = '0.00'
			WHERE invoiceid = '" . intval($uncrypted['invoiceid']). "'
				AND p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		
		$area_title = '{_marking_provider_generated_invoice_as_cancelled}';
		$page_title = SITE_NAME . ' - {_marking_provider_generated_invoice_as_cancelled}';
		
		print_notice('{_invoice_marked_as_cancelled}', '{_you_have_set_the_payment_status_of_this_transaction_as_cancelled}', HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&txn=' . $uncrypted['txn'], '{_return_to_previous_menu}');
		exit(); 
	}
}

// #### PRINTABLE INVOICE PREVIEW ##############################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'print' AND (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 OR isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != ''))
{
	// are we admin?
	if ($_SESSION['ilancedata']['user']['isadmin'] == '1' AND isset($ilance->GPC['uid']) AND $ilance->GPC['uid'] > 0)
	{
		// admin views invoice popup via admin cp
		if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
		{
			// via transaction order id
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . intval($ilance->GPC['uid']) . "'
					AND transactionid = '" . $ilance->db->escape_string($ilance->GPC['txn']) . "'
				LIMIT 1
			");
		}
		else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			// via invoice id
			$sql = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE user_id = '" . intval($ilance->GPC['uid']) . "'
					AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				LIMIT 1
			");
		}
	}
	else
	{
		// client views his own invoice popup
		if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
		{
			    // via transaction order id
			    $sql = $ilance->db->query("
				    SELECT *
				    FROM " . DB_PREFIX . "invoices
				    WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					    AND transactionid = '" . $ilance->db->escape_string($ilance->GPC['txn']) . "'
				    LIMIT 1
			    ");
		}
		else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
		{
			    // via invoice id
			    // via transaction order id
			    $sql = $ilance->db->query("
				    SELECT *
				    FROM " . DB_PREFIX . "invoices
				    WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					    AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				    LIMIT 1
			    ");
		}
	}
    
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			// #### invoice that do not show a provider or merchant
			$invoice['siteaddress'] = SITE_ADDRESS;
			$invoice['sitebusinessnumber'] = '';	
			$p2b_user_id = $res['p2b_user_id'];
			$invoice['providerfullname'] = $invoice['providerbusinessnumber'] = $invoice['providerusername'] = $invoice['providerfulladdress'] = '--';
			$invoice['sitebusinessnumber'] .= ($ilconfig['globalserversettings_registrationnumber'] != '')
				? '<br /><br /><strong>{_company_registration_number}</strong><br />' . $ilconfig['globalserversettings_registrationnumber']
				: '';
			$invoice['sitebusinessnumber'] .= ($ilconfig['globalserversettings_vatregistrationnumber'] != '')
				? '<br /><br /><strong>{_vat_registration_number}</strong><br />' . $ilconfig['globalserversettings_vatregistrationnumber']
				: '';
			// #### customer info ##########################
			$invoice['fullname'] = fetch_user('fullname', $res['user_id']);
			$invoice['username'] = fetch_user('username', $res['user_id']);
			$invoice['businessnumber'] = fetch_business_numbers($res['user_id'], 1);
			$invoice['fulladdress'] = $ilance->shipping->print_shipping_address_text($res['user_id']);
			// #### invoice info ###########################
			$invoice['purchasedate'] = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$invoice['maxpaymentdays'] = $ilconfig['invoicesystem_maximumpaymentdays'];
			$invoice['duedate'] = $res['duedate'];
			$invoice['duedate'] = ($invoice['duedate'] == '0000-00-00 00:00:00')
				? '--'
				: print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$invoice['txn'] = $res['transactionid'];
			$invoice['invoiceid'] = $res['invoiceid'];
			$invoice['comments'] = isset($res['comments'])
				? stripslashes($res['comments'])
				: '{_thank_you_for_your_business}';
			$invoice['description'] = handle_input_keywords(strip_tags($res['description']));
			/*if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product' AND fetch_auction('currencyid',$res['projectid'])!= $ilconfig['globalserverlocale_defaultcurrency'])
			{
				$invoice['totalamount'] = ($res['istaxable'])
				    ? print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'],($res['amount'] + $res['taxamount']), fetch_auction('currencyid',$res['projectid'])) : print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'],$res['amount'] , fetch_auction('currencyid',$res['projectid'])) ;
				    $invoice['amount'] =  print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'],$res['amount'] , fetch_auction('currencyid',$res['projectid'])) ;
				    $invoice['totalpaid'] = isset($res['paid'])
				    ? print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'],$res['paid'] , fetch_auction('currencyid',$res['projectid']))
				    : $ilance->currency->format(0, $res['currency_id']);
			}
			else
			{*/
				$invoice['amount'] = $ilance->currency->format($res['amount'], $res['currency_id']);
				$invoice['totalamount'] = ($res['istaxable'])
					? $ilance->currency->format($res['amount'] + $res['taxamount'], $res['currency_id'])
					: $ilance->currency->format($res['amount'], $res['currency_id']);
				$invoice['totalpaid'] = isset($res['paid'])
					? $ilance->currency->format($res['paid'], $res['currency_id'])
					: $ilance->currency->format(0, $res['currency_id']);
			/*}*/
			$invoice['notaxlogic'] = 0;
			$invoice['taxlogic'] = '';
			// #### for printable invoice view, show if overall invoice was credit or debit
			switch ($res['invoicetype'])
			{
				case 'storesubscription':
				{
					$invoice['type'] = '{_debit} / {_subscription}';
					break;
				}		
				case 'subscription':
				{
					$invoice['type'] = '{_debit} / {_subscription}';
					break;
				}		
				case 'commission':
				{
					$invoice['type'] = '{_debit} / {_commission}';
					break;
				}		
				case 'p2b':
				{
					$invoice['type'] = '{_debit} / {_provider_to_buyer_generated_invoice}';
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'buynow':
				{
					$invoice['type'] = '{_debit} / {_escrow}';
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'credential':
				{
					$invoice['type'] = '{_debit}';
					break;
				}		
				case 'debit':
				{
					$invoice['type'] = '{_debit}';
					break;
				}		
				case 'credit':
				{
					$invoice['type'] = '{_credit}';
					$invoice['notaxlogic'] = 1;
					break;
				}		
				case 'escrow':
				{
					$invoice['type'] = '{_debit} / {_escrow}';
					$invoice['notaxlogic'] = 1;
					break;
				}
			}
			if ($invoice['notaxlogic'] == 0)
			{
				$taxinfo = $res['taxinfo'];
				// create the tax bit in html
				if (!empty($taxinfo))
				{
					$invoice['taxlogic'] = '<tr>
	<td align="right" class="tablehead"> {_applicable_tax}: &nbsp;&nbsp;</td>
	<td align="left" class="tablehead" nowrap="nowrap">' . $taxinfo . '</td>
</tr>';
				}
			}
	
			($apihook = $ilance->api('invoicepayment_print_taxlogic')) ? eval($apihook) : false;
	
			// #### invoice type settings (show or not show another opponent on invoice page)
			if ($res['invoicetype'] == 'p2b')
			{
				$invoice['providerfullname'] = fetch_user('fullname', $res['p2b_user_id']);
				$invoice['providerbusinessnumber'] = fetch_business_numbers($res['p2b_user_id'], 1);
				$invoice['providerusername'] = fetch_user('username', $res['p2b_user_id']);
				$invoice['providerfulladdress'] = $ilance->shipping->print_shipping_address_text($res['p2b_user_id']);
			}
			else if ($res['invoicetype'] == 'buynow')
			{
				$invoice['providerfullname'] = fetch_user('fullname', $res['p2b_user_id']);
				$invoice['providerbusinessnumber'] = fetch_business_numbers($res['p2b_user_id'], 1);
				$invoice['providerusername'] = fetch_user('username', $res['p2b_user_id']);
				$invoice['providerfulladdress'] = $ilance->shipping->print_shipping_address_text($res['p2b_user_id']);
			}
			else if ($res['invoicetype'] == 'escrow')
			{
				if ($ilance->auction->fetch_auction_type($res['projectid']) == 'service')
				{
					$invoice['providerusername'] = $ilance->escrow->fetch_escrow_opponent($res['projectid'], $res['invoiceid'], 'service');
					$invoice['providerfullname'] = fetch_user('fullname', fetch_user('user_id', '', $invoice['providerusername']));
					$invoice['providerbusinessnumber'] = fetch_business_numbers(fetch_user('user_id', '', $invoice['providerusername']), 1);
					$invoice['providerfulladdress'] = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $invoice['providerusername']));
				}
				else if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product')
				{
					$invoice['providerusername'] = $ilance->escrow->fetch_escrow_opponent($res['projectid'], $res['invoiceid'], 'product');
					$invoice['providerfullname'] = fetch_user('fullname', fetch_user('user_id', '', $invoice['providerusername']));
					$invoice['providerbusinessnumber'] = fetch_business_numbers(fetch_user('user_id', '', $invoice['providerusername']), 1);
					$invoice['providerfulladdress'] = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $invoice['providerusername']));
				}
			}
			$invoice[] = $invoice;
		}
	}
	$owner_bank_info = '';
	$owner_bank_info .= !empty($ilconfig['owner_bank_name']) ? $ilconfig['owner_bank_name'] . "<br />" : '';
	$owner_bank_info .= !empty($ilconfig['owner_bank_account_number']) ? $ilconfig['owner_bank_account_number'] . "<br />" : '';
	$owner_bank_info .= !empty($ilconfig['owner_bank_swift']) ? $ilconfig['owner_bank_swift'] . "<br />" : '';
	$show['owner_bank_info'] = (!empty($owner_bank_info) AND $p2b_user_id == '0') ? true : false;
	$pprint_array = array('owner_bank_info');
	$ilance->template->load_popup('main', 'invoicepayment_print.html');
	$ilance->template->parse_loop('main', 'invoice');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}

// #### INVOICE DOWNLOAD ACTION ################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-action' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['invcmd']))
{
	$csv = $txt = $pdf = '';
	switch ($ilance->GPC['invcmd'])
	{
		// todo: finish rest of export options.
		case 'csv':
		{
			$csv = '"{_invoice_pound}"' . "," . '"{_userid}"' . "," . '"{_description}"' . "," . '"{_currency}"' . "," . '"{_rate}"' . "," . '"{_amount}"' . "," . '"{_tax}"' . "," . '"{_paid}"' . "," . '"{_invoice_status}"' . "," . '"{_invoice_type}"' . "," . '"{_pay_method}"' . "," . '"{_create_date}"' . "," . '"{_due_date}"' . "," . '"{_paid_date}"' . "," . '"{_invoice_notes}"' . LINEBREAK;
			$csv_results = array();
			
			$csv_query = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
					AND invoiceid = '" . intval($ilance->GPC['id']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($csv_query) > 0)
			{
				while ($csv_results = $ilance->db->fetch_array($csv_query, DB_ASSOC))
				{
					$currencyabbrev = $ilance->currency->currencies[$ilance->currency->fetch_default_currencyid()]['currency_abbrev'];
					$currencyrate = $ilance->currency->currencies[$ilance->currency->fetch_default_currencyid()]['rate'];
					if (!empty($ilance->currency->currencies[$csv_results['currency_id']]['currency_abbrev']))
					{
						$currencyabbrev = $ilance->currency->currencies[$csv_results['currency_id']]['currency_abbrev'];
						$currencyrate = $csv_results['currency_rate'];
					}
					
					$csv .= "\"" . $csv_results['invoiceid'] . "\",\"" . fetch_user('username', $csv_results['user_id']) . "\",\"" . handle_input_keywords($csv_results['description']) . "\",\"" . $currencyabbrev . "\",\"" . $currencyrate . "\",\"" . $ilance->currency->format($csv_results['amount'], $csv_results['currency_id']) . "\",\"" . $ilance->currency->format($csv_results['taxamount'], $csv_results['currency_id']) . "\",\"" . $ilance->currency->format($csv_results['paid'], $csv_results['currency_id']) . "\",\"" . mb_strtoupper($csv_results['status']) . "\",\"" . $ilance->accounting_print->print_transaction_type($csv_results['invoicetype']) . "\",\"" . mb_strtoupper($csv_results['paymethod']) . "\",\"" . $csv_results['createdate'] . "\",\"" . $csv_results['duedate'] . "\",\"" . $csv_results['paiddate'] . "\",\"" . handle_input_keywords($csv_results['custommessage']) . "\"" . LINEBREAK;
				}
			}
			
			$area_title = '{_downloading_csv_invoice_reports}<div class="smaller">CSV</div>';
			$page_title = SITE_NAME . ' - {_downloading_csv_invoice_reports}';
			
			$ilance->template->templateregistry['csvtxn'] = $csv;
			$ilance->common->download_file($ilance->template->parse_template_phrases('csvtxn'), "invoice_" . intval($ilance->GPC['id']) . "-" . date('Y') . "-" . date('m') . "-" . date('d') . ".csv", "text/plain");
			break;
		}
		case 'tsv':
		{
			break;
		}
		case 'txt':
		{
			break;
		}
		case 'pdf':
		{
			break;
		} 
	}
	exit();
}

// #### PROVIDER GENERATING BUYER INVOICE ######################################
else if (isset($uncrypted['cmd']) AND $uncrypted['cmd'] == '_generate-invoice' AND isset($uncrypted['buyer_id']) AND $uncrypted['buyer_id'] > 0 AND isset($uncrypted['seller_id']) AND $uncrypted['seller_id'] > 0 AND isset($uncrypted['project_id']) AND $uncrypted['project_id'] > 0)
{
	
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'generateinvoices') == 'no')
	{
		$area_title = '{_access_denied_to_invoice_generation}';
		$page_title = SITE_NAME . ' - {_access_denied_to_invoice_generation}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', HTTP_SERVER . $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('generateinvoices'));
		exit();        
	}
	
	// verify that the provider generating the invoice to the buyer is actually him!
	if ($uncrypted['seller_id'] != $_SESSION['ilancedata']['user']['userid'])
	{
		$area_title = '{_access_denied_to_invoice_generation}';
		$page_title = SITE_NAME . ' - {_access_denied_to_invoice_generation}';
		
		print_notice('{_access_denied}', '{_this_action_can_only_be_executed_by_the_awarded_service_provider}', HTTP_SERVER . $ilpage['main'], '{_main_menu}');
		exit();        
	}
	
	$area_title = '{_generating_new_invoice_to_service_buyer}';
	$page_title = SITE_NAME . ' - {_generating_new_invoice_to_service_buyer}';
	
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[accounting]?cmd=management"] = '{_accounting}';
	$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
	$navcrumb[] = '{_invoice_generation_to_buyer}';

	$currency = print_left_currency_symbol();
	
	if ($ilconfig['invoicesystem_enablep2btransactionfees'])
	{
		$show['p2b_transaction_fee'] = true;
		$commissionfee = ($ilconfig['invoicesystem_p2bfeesfixed'])
			? $ilance->currency->format($ilconfig['invoicesystem_p2bfee'])
			: $ilconfig['invoicesystem_p2bfee'] . '%';
		
		$txnfee = (!empty($commissionfee)) ? $commissionfee : 0;
	}
	else
	{
		$show['p2b_transaction_fee'] = false;
	}
	
	// auction information
	$sql_auction = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . $uncrypted['project_id'] . "' 
			AND user_id = '" . $uncrypted['buyer_id'] . "'
	");
	if ($ilance->db->num_rows($sql_auction) > 0)
	{
		$result_auction = $ilance->db->fetch_array($sql_auction, DB_ASSOC);
		
		$project_id = $result_auction['project_id'];
		$project_title = stripslashes($result_auction['project_title']);
		
		// auction owner
		$sql_owner = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . $uncrypted['buyer_id'] . "'
		");
		if ($ilance->db->num_rows($sql_owner) > 0)
		{
			$result_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
			$customer = stripslashes($result_owner['username']);
			$buyer_id = $uncrypted['buyer_id'];
			
			// service provider info
			$sql_provider = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $uncrypted['seller_id'] . "'
			");
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$result_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				$seller_id = $uncrypted['seller_id'];
				
				// related invoices association and links to view them
				$inv_assoc = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "invoices
					WHERE projectid = '" . $uncrypted['project_id'] . "' 
						AND p2b_user_id = '" . $uncrypted['seller_id'] . "' 
						AND user_id = '" . $uncrypted['buyer_id'] . "'
				");
				if ($ilance->db->num_rows($inv_assoc) > 0)
				{
					$show['other_invoices'] = true;
					
					$invoiceassociation = '';
					while ($inv_results = $ilance->db->fetch_array($inv_assoc, DB_ASSOC))
					{
						if ($inv_results['status'] == 'paid')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>{_paid}</strong></div>';
						}
						else if ($inv_results['status'] == 'unpaid')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <span style="color:red"><strong>{_unpaid}</strong></span></div>';
						}
						else if ($inv_results['status'] == 'scheduled')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>{_scheduled}</strong></div>';
						}
						else if ($inv_results['status'] == 'complete')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>{_paid}</strong></div>';
						}
						else if ($inv_results['status'] == 'cancelled')
						{
							$invoiceassociation .= '<div style="padding-top:3px"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $inv_results['transactionid'] . '">' . $ilance->currency->format($inv_results['amount'], $inv_results['currency_id']) . ' : ' . $inv_results['transactionid'] . '</a> : <strong>{_cancelled}</strong></div>';
						}
					}
				}
				else
				{
					$show['other_invoices'] = false;
				}
				
				// latest last bid amount placed
				$sql_bidamount = $ilance->db->query("
					SELECT *
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . $uncrypted['seller_id'] . "'
						AND project_id = '" . $uncrypted['project_id'] . "'
					ORDER BY bid_id DESC
				");
				if ($ilance->db->num_rows($sql_bidamount) > 0)
				{
					$result_bidamount = $ilance->db->fetch_array($sql_bidamount, DB_ASSOC);
					$bidamount = $ilance->currency->format($result_bidamount['bidamount'], fetch_auction('currencyid', $result_bidamount['project_id']));
				}
				
				$paymentstatuspulldown = '<select name="paymentstatus" style="font-family: verdana"><option value="unpaid" selected="selected">{_mark_as_unpaid}</option><option value="paid">{_mark_as_paid}</option></select>';
				$paymentmethodpulldown = $ilance->auction_post->print_payment_method('p2b_paymethod', 'p2b_paymethod');
				
				// specific javascript includes
				$headinclude .= '
<script type="text/javascript">
<!--
function validatep2binvoice(f)
{
var Chars = "0123456789.,";
haveerrors = 0;
(f.amount.value.length < 1) ? showImage("amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("amounterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
for (var i = 0; i < f.amount.value.length; i++)
{
    if (Chars.indexOf(f.amount.value.charAt(i)) == -1)
    {
	    alert_js(phrase[\'_invalid_currency_characters_only_numbers_and_a_period_are_allowed_in_this_field\']);
	    haveerrors = 1;
    }
}
if (f.amount.value == "0.00" || f.amount.value == "0")
{
    alert_js(phrase[\'_cannot_place_value_for_your_bid_amount_your_bid_amount_must_be_greater_than_the_minimum_bid_amount\']);
    haveerrors = 1;
}				    
return (!haveerrors);
}
//-->
</script>';
				$pprint_array = array('paymentstatuspulldown','paymentmethodpulldown','bidamount','invoiceassociation','project_title','project_id','customer','txnfee','session_amount','session_comments','currency','buyer_id','seller_id');
				
				$ilance->template->fetch('main', 'invoicepayment_p2b.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
	}
}

// #### PROVIDER INVOICE TO BUYER PREVIEW ######################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-generate-preview' AND isset($ilance->GPC['amount']) AND $ilance->GPC['amount'] != "" AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$area_title = '{_preview_generation_of_new_invoice_to_buyer}';
	$page_title = SITE_NAME . ' - {_preview_generation_of_new_invoice_to_buyer}';

	$navcrumb = array();
	$navcrumb["$ilpage[accounting]?cmd=management"] = '{_accounting}';
	$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
	$navcrumb[] = '{_invoice_generation_to_buyer}';

	
	
	// can this service provider generate invoices to their buyers?
	if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'generateinvoices') == 'no')
	{
		$area_title = '{_access_denied_to_invoice_generation}';
		$page_title = SITE_NAME . ' - {_access_denied_to_invoice_generation}';
		print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . HTTP_SERVER . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>', HTTP_SERVER . $ilpage['subscription'], '{_click_here}', fetch_permission_name('generateinvoices'));
		exit();        
	}
	
	$ilance->GPC['amount'] = $ilance->currency->string_to_number($ilance->GPC['amount']);
	
	if ($ilconfig['invoicesystem_enablep2btransactionfees'])
	{
		$show['p2b_transaction_fee'] = true;
		$commissionfee = ($ilconfig['invoicesystem_p2bfeesfixed'])
			? $ilconfig['invoicesystem_p2bfee']
			: ($ilance->GPC['amount'] * $ilconfig['invoicesystem_p2bfee'] / 100);
		
		if (!empty($commissionfee))
		{
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $commissionfee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($commissionfee);
		}
		else 
		{
			$txn_fee_hidden = '';
			$transaction_fee_formatted = $ilance->currency->format(0);	
		}
	}
	else
	{
		$show['p2b_transaction_fee'] = false;
		$txn_fee_hidden = '';
	}
	
	$amount = $ilance->GPC['amount'];
	$amount_formatted = $ilance->currency->format($ilance->GPC['amount']);
	
	// fetch auction information
	$sql_auction = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "projects
		WHERE project_id = '" . intval($ilance->GPC['project_id']) . "'
			AND user_id = '" . intval($ilance->GPC['buyer_id']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql_auction) > 0)
	{
		$result_auction = $ilance->db->fetch_array($sql_auction, DB_ASSOC);
		
		$project_id = $result_auction['project_id'];
		$project_title = stripslashes($result_auction['project_title']);
		
		// fetch service auction buyer
		$sql_owner = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "users
			WHERE user_id = '" . intval($ilance->GPC['buyer_id']) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql_owner) > 0)
		{
			$result_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
			$customer = stripslashes($result_owner['username']);
			$buyer_id = $result_owner['user_id'];
			
			// service provider information
			$sql_provider = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($ilance->GPC['seller_id']) . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$result_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				
				$seller_id = intval($ilance->GPC['seller_id']);
				$instantpay = (isset($ilance->GPC['instantpay']) AND $ilance->GPC['instantpay'] > 0) ? 1 : 0;
				$comments = (isset($ilance->GPC['comments']) AND !empty($ilance->GPC['comments'])) ? ilance_htmlentities($ilance->GPC['comments']) : '{_no_comments_available}';
				$paymentstatus = $ilance->GPC['paymentstatus'];
				$paymentstatus_formatted = ucwords($ilance->GPC['paymentstatus']);
				$paymentmethod = $ilance->GPC['p2b_paymethod'];
				$paymentmethod_formatted = (mb_substr($ilance->GPC['p2b_paymethod'], 0, 1) == '_') ? '{' . $ilance->GPC['p2b_paymethod'] . '}' : $ilance->GPC['p2b_paymethod'];
				
				$pprint_array = array('paymentmethod_formatted','paymentstatus_formatted','paymentstatus','paymentmethod','comments','instantpay','description','amount_formatted','txn_fee_hidden','amount','transaction_fee_formatted','transaction_amount','project_title','project_id','customer','txnfee','currency','buyer_id','seller_id');
				
				$ilance->template->fetch('main', 'invoicepayment_p2b_preview.html');
				$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
				$ilance->template->parse_if_blocks('main');
				$ilance->template->pprint('main', $pprint_array);
				exit();
			}
		}
	}
}

// #### SERVICE PROVIDER INVOICE TO BUYER HANDLER ##############################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-generate-submit' AND isset($ilance->GPC['amount']) AND !empty($ilance->GPC['amount']) AND isset($ilance->GPC['seller_id']) AND $ilance->GPC['seller_id'] > 0 AND isset($ilance->GPC['buyer_id']) AND $ilance->GPC['buyer_id'] > 0 AND isset($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
{
	$area_title = '{_new_invoice_was_generated_to_buyer}';
	$page_title = SITE_NAME . ' - {_new_invoice_was_generated_to_buyer}';
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb["$ilpage[accounting]?cmd=management"] = '{_accounting}';
	$navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
	$navcrumb[] = '{_invoice_generation_to_buyer}';
	
	$txnfee = 0;
	if (isset($ilance->GPC['transaction_fee']) AND $ilance->GPC['transaction_fee'] != '' AND $ilance->GPC['transaction_fee'] != '0' AND $ilance->GPC['transaction_fee'] != '0.00' AND $ilance->GPC['transaction_fee'] != '0.0')
	{
		$txnfee = $ilance->GPC['transaction_fee'];
		if ($txnfee < 0)
		{
			$txnfee = 0;
		}
	}

	$comments = '{_no_comments_available}';		
	if (isset($ilance->GPC['comments']) AND !empty($ilance->GPC['comments']))
	{
		$comments = $ilance->GPC['comments'];
	}
	
	$instantpay = 0;
	if (isset($ilance->GPC['instantpay']) AND $ilance->GPC['instantpay'] > 0)
	{
		$instantpay = 1;
	}
	
	$ilance->GPC['paymentstatus'] = isset($ilance->GPC['paymentstatus']) ? $ilance->GPC['paymentstatus'] : 'unpaid';
	$ilance->GPC['paymentmethod'] = isset($ilance->GPC['paymentmethod']) ? $ilance->GPC['paymentmethod'] : '';
	
	$ilance->accounting_p2b->construct_p2b_transaction($ilance->GPC['amount'], intval($ilance->GPC['seller_id']), (int)$ilance->GPC['buyer_id'], (int)$ilance->GPC['project_id'], $comments, $txnfee, $instantpay, $ilance->GPC['paymentstatus'], $ilance->GPC['paymentmethod']);
	
	print_notice('{_invoice_generation_complete}', '{_you_have_successfully_generated_an_invoice_to_your_customer}<br /><br />{_please_remember_if_you_have_not_paid_your_transaction_fee_for_the_generation_of_this_invoice_your_account_may_become_inactive_after_a_specific_period_of_time}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
	exit();
}

// #### INVOICE PREVIEW ########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-preview' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$area_title = '{_invoice_payment_preview_menu}';
	$page_title = SITE_NAME . ' - {_invoice_payment_preview_menu}';
	if (!isset($ilance->GPC['account_id']) OR empty($ilance->GPC['account_id']))
	{
		$area_title = '{_invoice_payment_menu_denied_payment}';
		$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
		print_notice('{_invoice_error}', '{_no_payment_method_was_selected}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = '{_accounting}';
	$navcrumb["$ilpage[invoicepayment]?id=" . intval($ilance->GPC['id'])] = '{_invoice_payment_preview_menu}';
	$navcrumb[""] = '{_invoice} #' . intval($ilance->GPC['id']);
	$id = isset($ilance->GPC['id']) ? intval($ilance->GPC['id']) : 0;
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "invoices
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "' 
			AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$res['currency_id'] = ($res['currency_id'] == '0') ? $ilconfig['globalserverlocale_defaultcurrency'] : $res['currency_id'];
		$invoicetype = $res['invoicetype'];
		$description = $res['description'];
		$amount = $res['amount'];
		$totalpreviewamount = $ilance->currency->format($amount);
		$previewamount = $ilance->currency->format($res['amount'], $res['currency_id']);
		$taxlogic = '';
		// do we pay taxes?
		if ($res['istaxable'] > 0 AND $res['totalamount'] > 0)
		{
			$taxinfo = $res['taxinfo'];
			// change regular amount to total amount (including added taxes)
			$res['totalamount'] = $amount + $res['taxamount'];
			$totalpreviewamount = $ilance->currency->format($res['totalamount'], $res['currency_id']);
			// create the tax bit in html
			if (!empty($taxinfo))
			{
				$taxlogic = '<tr class="alt1">
       <td align="right"><span class="gray">{_applicable_tax}:</span></td>
       <td align="left"><span class="black">' . $taxinfo . '</span></td>
</tr>';
			}
			$amount = $res['totalamount'];
		}
		$createdate = print_date($res['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$duedate = ($res['duedate'] == '0000-00-00 00:00:00') ? '-' : print_date($res['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		$account_id = $ilance->GPC['account_id'];
		$custommessage = handle_input_keywords(stripslashes($res['custommessage']));
		$invoiceid = intval($ilance->GPC['id']);
	}
	// #### INVOICE PAYMENT PREVIEW VIA ONLINE ACCOUNT BALANCE #####
	if ($ilance->GPC['account_id'] == 'account')
	{
		$show['transactionfees'] = $show['directpayment'] = 0;
		$payment_method = '{_online_account_instant_payment}';
		$directpaymentform = '';
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	
	// #### INVOICE PAYMENT VIA PAYPAL #############################
	else if ($ilance->GPC['account_id'] == 'paypal')
	{
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = '{_paypal}';
		// gateway transaction fees
		if ($ilconfig['paypal_transaction_fee'] > 0 OR $ilconfig['paypal_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['paypal_transaction_fee'];
			$fee_b = $ilconfig['paypal_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product' AND fetch_auction('currencyid',$res['projectid'])!= $ilconfig['globalserverlocale_defaultcurrency'])
		{
			$transaction_fee = convert_currency(fetch_auction('currencyid', $res['projectid']), $transaction_fee,$ilconfig['globalserverlocale_defaultcurrency']);
			$totalamount = sprintf("%01.2f", convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee),fetch_auction('currencyid',$res['projectid'])));
			$totalpreviewamount = print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee), fetch_auction('currencyid',$res['projectid']));
			$previewamount  =print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], $amount, fetch_auction('currencyid',$res['projectid']));		
		}
		else
		{
			$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
			$previewamount = $ilance->currency->format($amount);
		}
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		$ilance->paypal = construct_object('api.paypal', $ilance->GPC);
		$directpaymentform = $ilance->paypal->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['paypal_business_email'], $ilconfig['paypal_master_currency'], '', $customencrypted, 0);
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### INVOICE PAYMENT VIA CASHU ##############################
	else if ($ilance->GPC['account_id'] == 'cashu')
	{
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = '{_cashu}';
		// gateway transaction fees
		if ($ilconfig['cashu_transaction_fee'] > 0 OR $ilconfig['cashu_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['cashu_transaction_fee'];
			$fee_b = $ilconfig['cashu_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product' AND fetch_auction('currencyid',$res['projectid'])!= $ilconfig['globalserverlocale_defaultcurrency'])
		{
			$transaction_fee = convert_currency(fetch_auction('currencyid',$res['projectid']), $transaction_fee,$ilconfig['globalserverlocale_defaultcurrency']);
			$totalamount = sprintf("%01.2f", convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee),fetch_auction('currencyid',$res['projectid'])));
			$totalpreviewamount =print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee), fetch_auction('currencyid',$res['projectid']));
			$previewamount =print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], $amount, fetch_auction('currencyid',$res['projectid']));		
		}
		else
		{
			$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
			$previewamount = $ilance->currency->format($amount);
		}
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		$ilance->cashu = construct_object('api.cashu', $ilance->GPC);
		$directpaymentform = $ilance->cashu->print_payment_form($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['email'], $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['cashu_business_email'], $ilconfig['cashu_master_currency'], $ilconfig['cashu_secret_code'], $customencrypted, $ilconfig['cashu_testmode']);
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### INVOICE PAYMENT VIA MONEYBOOKERS #######################
	else if ($ilance->GPC['account_id'] == 'moneybookers')
	{
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = '{_moneybookers}';
		// gateway transaction fees
		if ($ilconfig['moneybookers_transaction_fee'] > 0 OR $ilconfig['moneybookers_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			
			$fee_a = $ilconfig['moneybookers_transaction_fee'];
			$fee_b = $ilconfig['moneybookers_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product' AND fetch_auction('currencyid',$res['projectid'])!= $ilconfig['globalserverlocale_defaultcurrency'])
		{
			$transaction_fee = convert_currency(fetch_auction('currencyid',$res['projectid']), $transaction_fee,$ilconfig['globalserverlocale_defaultcurrency']);
			$totalamount = sprintf("%01.2f", convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee),fetch_auction('currencyid',$res['projectid'])));
			$totalpreviewamount =print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee), fetch_auction('currencyid',$res['projectid']));
			$previewamount =print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], $amount, fetch_auction('currencyid',$res['projectid']));		
		}
		else
		{
			$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
			$previewamount = $ilance->currency->format($amount);
		}
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		$ilance->moneybookers = construct_object('api.moneybookers', $ilance->GPC);
		$directpaymentform = $ilance->moneybookers->print_payment_form($_SESSION['ilancedata']['user']['userid'], '', $totalamount, intval($ilance->GPC['id']), 0, $description, $ilconfig['moneybookers_business_email'], $ilconfig['moneybookers_master_currency'], $ilconfig['moneybookers_secret_code'], $customencrypted, 0);
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### INVOICE PAYMENT VIA PLATNOSCI.PL #######################
	else if ($ilance->GPC['account_id'] == 'platnosci')
	{
		$show['transactionfees'] = $transaction_fee = 0;
		$show['directpayment'] = 1;
		$txn_fee_hidden = '';
		$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		$payment_method = '{_platnosci}';
		// gateway transaction fees
		if ($ilconfig['platnosci_transaction_fee'] > 0 OR $ilconfig['platnosci_transaction_fee2'] > 0)
		{
			$show['transactionfees'] = 1;
			$fee_a = $ilconfig['platnosci_transaction_fee'];
			$fee_b = $ilconfig['platnosci_transaction_fee2'];
			$transaction_fee = ($amount * $fee_a) + $fee_b;
			$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="' . $transaction_fee . '" />';
			$transaction_fee_formatted = $ilance->currency->format($transaction_fee);
		}
		$totalpreviewamount = ($amount + $transaction_fee);
		$totalamount = sprintf("%01.2f", $totalpreviewamount);
		if ($ilance->auction->fetch_auction_type($res['projectid']) == 'product' AND fetch_auction('currencyid',$res['projectid'])!= $ilconfig['globalserverlocale_defaultcurrency'])
		{
			$transaction_fee = convert_currency(fetch_auction('currencyid',$res['projectid']), $transaction_fee,$ilconfig['globalserverlocale_defaultcurrency']);
			$totalamount = sprintf("%01.2f", convert_currency($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee),fetch_auction('currencyid',$res['projectid'])));
			$totalpreviewamount = print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], ($amount + $transaction_fee), fetch_auction('currencyid',$res['projectid']));
			$previewamount = print_currency_conversion_invoice($ilconfig['globalserverlocale_defaultcurrency'], $amount, fetch_auction('currencyid',$res['projectid']));		
		}
		else
		{
			$totalpreviewamount = $ilance->currency->format($totalpreviewamount);
			$previewamount = $ilance->currency->format($amount);
		}
		$customencrypted = 'DIRECT|' . $_SESSION['ilancedata']['user']['userid'] . '|' . intval($ilance->GPC['id']) . '|' . $invoicetype . '|0|0|0|0|0';
		$ilance->platnosci = construct_object('api.platnosci', $ilance->GPC);
		$directpaymentform = $ilance->platnosci->print_direct_payment_form($totalamount, $description, '', $customencrypted);
		$pprint_array = array('directpaymentform','totalpreviewamount','taxlogic','account_id','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
		$ilance->template->fetch('main', 'invoicepayment_preview.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### INVOICE PAYMENT VIA CREDIT CARD ########################
	else
	{
		$show['directpayment'] = 0;
		$invoiceid = intval($ilance->GPC['id']);
		$result_active_cards = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "creditcards
			WHERE cc_id = '" . intval($ilance->GPC['account_id']) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				AND creditcard_status = 'active'
				AND authorized = 'yes'
			LIMIT 1
		");
		if ($ilance->db->num_rows($result_active_cards) > 0)
		{
			$res_cc = $ilance->db->fetch_array($result_active_cards, DB_ASSOC);
			$dec_CardNumber = $ilance->crypt->three_layer_decrypt($res_cc['creditcard_number'], $ilconfig['key1'], $ilconfig['key2'], $ilconfig['key3']);
			$dec_CardNumber = str_replace(' ', '', $dec_CardNumber);
			$ccnum_hidden = substr_replace($dec_CardNumber, 'XX XXXX XXXX ', 2, (mb_strlen($dec_CardNumber) - 6));
			$payment_method = mb_strtoupper($res_cc['creditcard_type']) . '# ' . $ccnum_hidden;
			$sql_invoice = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "invoices
				WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_invoice) > 0)
			{
				$res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
				$invoicetype = $res_invoice['invoicetype'];
				$description = stripslashes($res_invoice['description']);
				$transaction_fee_formatted = '';
				$transaction_fee = 0;
				$show['transactionfees'] = false;
				if ($ilconfig['cc_transaction_fee'] > 0 OR $ilconfig['cc_transaction_fee2'] > 0)
				{
					$txn_fee_hidden = '<input type="hidden" name="transaction_fee" value="'.sprintf("%01.2f", (($res_invoice['amount'] * $ilconfig['cc_transaction_fee']) +  $ilconfig['cc_transaction_fee2'])).'" />';
					$transaction_fee = sprintf("%01.2f", (($res_invoice['amount'] * $ilconfig['cc_transaction_fee']) + $ilconfig['cc_transaction_fee2']));
					$transaction_fee_formatted = ($ilconfig['cc_transaction_fee'] * 100) . '% + ' . $ilance->currency->format($ilconfig['cc_transaction_fee2']);
					
					$show['transactionfees'] = true;
				}
				$previewamount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
				$totalpreviewamount = $ilance->currency->format(($res_invoice['amount'] + $transaction_fee), $res_invoice['currency_id']);
				$taxlogic = '';
				// do we pay taxes?
				if ($res_invoice['istaxable'] > 0 AND $res_invoice['totalamount'] > 0)
				{
					$taxinfo = $res_invoice['taxinfo'];
					// change regular amount to total amount (including added taxes)
					$amount = ($res_invoice['totalamount'] + $transaction_fee);
					$totalpreviewamount = $ilance->currency->format(($res_invoice['totalamount'] + $transaction_fee), $res_invoice['currency_id']);
					// create the tax bit in html
					if (!empty($taxinfo))
					{
						$taxlogic = '<tr><td align="right"> '.'{_applicable_tax}'.':</td><td align="left">'.$taxinfo.'</td></tr>';
					}
				}
				else
				{
					$amount = $res_invoice['amount'];
				}
				$createdate = print_date($res_invoice['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$duedate = '-';
				if ($res_invoice['duedate'] != '0000-00-00 00:00:00')
				{
					$duedate = print_date($res_invoice['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
				$paiddate = '-';
				if ($res_invoice['paiddate'] != '0000-00-00 00:00:00')
				{
					$paiddate = print_date($res_invoice['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				}
			}				
			$pprint_array = array('taxlogic','directpaymentform','txn_fee_hidden','totalpreviewamount','previewamount','payment_method','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','transaction_fee_formatted','account_id','ip','referer','transaction_fee_notice');
			$ilance->template->fetch('main', 'invoicepayment_preview.html');
			$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
	}
}
// #### INVOICE PAYMENT HANDLER ################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-invoice-payment' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['account_id']) AND isset($ilance->GPC['invoicetype']))
{
	($apihook = $ilance->api('invoicepayment_process_start')) ? eval($apihook) : false;
	
	$sql = $ilance->db->query("
		SELECT active 
		FROM " . DB_PREFIX . "subscription_user 
		WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' 
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		if ($res['active'] == 'no')
		{
			$sql_inv = $ilance->db->query("
				SELECT totalamount 
				FROM " . DB_PREFIX . "invoices 
				WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
					AND (isfvf = '1' OR isif = '1' OR isportfoliofee = '1' OR isenhancementfee = '1' OR isescrowfee = '1' OR iswithdrawfee = '1')
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_inv) > 0)
			{
				$area_title = '{_invoice_error}';
				$page_title = SITE_NAME . ' - {_invoice_error}';
				print_notice('{_invoice_error}', '{_before_you_can_pay_for_fees_and_enhancements_your_subscription_payment_invoice_must_be_paid}', HTTPS_SERVER . $ilpage['subscription'], '{_subscription}');
				exit();
			}
		}
	}
	$success = $ilance->accounting_payment->invoice_payment_handler($ilance->GPC['id'], $ilance->GPC['invoicetype'], $ilance->GPC['amount'], $_SESSION['ilancedata']['user']['userid'], $ilance->GPC['account_id'], '', '', false, '', false);		
	if ($success == false)
	{
		$area_title = '{_invoice_error}';
		$page_title = SITE_NAME . ' - {_invoice_error}';
		print_notice('{_invoice_error}', '{_were_sorry_your_transaction_has_encountered_an_error_and_can_not_be_processed_at_the_moment}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
	
	($apihook = $ilance->api('invoicepayment_process_end')) ? eval($apihook) : false;
}

// #### ORDER DETAILS ##########################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'orderdetail' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	$area_title = '{_order_details}';
	$page_title = SITE_NAME . ' - {_order_details}';
	// build our breadcrumb nav #######################
	$navcrumb = array();
	$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
	$navcrumb[""] = '{_order_details}';
	$show['cancelled'] = false;
	
	($apihook = $ilance->api('orderdetails_start')) ? eval($apihook) : false;
	
	$wonbybid = $show['shipping'] = false;
	$ordernumber = $shippinginfo = $shippingpartner = $shipping = $taxamount = $taxpulldown = '';
	$taxamount_plain = 0;
	$oid = isset($ilance->GPC['oid']) ? $ilance->GPC['oid'] : 0;
	$bid = isset($ilance->GPC['bid']) ? $ilance->GPC['bid'] : 0;
	if (isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0)
	{
		$wonbybid = true;
	}
	if ($wonbybid)
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND bid_id = '" . intval($ilance->GPC['bid']) . "'
				AND (user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "' OR project_user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "')
		");
	}
	else
	{
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND (buyer_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "' OR owner_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "')
				AND orderid = '" . intval($ilance->GPC['oid']) . "'
		");
	}
	if ($ilance->db->num_rows($sql) == 0)
	{
		$area_title = '{_invoice_payment_menu_denied_payment}';
		$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
		print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
	$currencyinfo = '';
	$currencyid = fetch_auction('currencyid', intval($ilance->GPC['pid']));
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	if (isset($res['originalcurrencyid']) AND isset($res['convertedtocurrencyid']))
	{
		if ($res['originalcurrencyid'] != $res['convertedtocurrencyid'])
		{
			$currencyid = $res['convertedtocurrencyid'];
			$currencyinfo = '<div style="padding-bottom:3px" class="smaller"><strong>{_currency}:</strong> <span title="' . $ilance->currency->currencies[$res['convertedtocurrencyid']]['code'] . ' {_on} ' . print_date($res['orderdate']) . ' = ' . $res['convertedtocurrencyidrate'] . ', ' . $ilance->currency->currencies[$res['originalcurrencyid']]['code'] . ' = ' . $res['originalcurrencyidrate'] . '">' . $ilance->currency->currencies[$res['originalcurrencyid']]['code'] . ' {_to} ' . $ilance->currency->currencies[$res['convertedtocurrencyid']]['code'] . '</span></div>';
		}
	}
	if ($wonbybid)
	{
		$ordernumber = '{_order_number}: #BID-' . $res['bid_id'];
	}
	else
	{
		$ordernumber = '{_order_number}: #' . $res['orderid'];
		if ($res['status'] == 'cancelled')
		{
			$show['cancelled'] = true;
		}
	}
	$currencysymbol2 = $ilance->currency->currencies[$currencyid]['symbol_left'];
	$qty = $res['qty'];
	if (isset($res['buyershipperid']) AND $res['buyershipperid'] > 0)
	{
		$show['shipping'] = true;
		if (isset($res['ship_location']))
		{
			$shippinginfo = handle_input_keywords($res['ship_location']);
			$buyerfullname = fetch_user('fullname', $res['buyer_id']);
			$buyerusername = fetch_user('username', $res['buyer_id']);
			$buyercountryid = fetch_user('country', $res['buyer_id']);
			$buyerstate = fetch_user('state', $res['buyer_id']);
		}
		else
		{
			if ($wonbybid)
			{
				$shippinginfo = handle_input_keywords($ilance->shipping->print_shipping_address_text($res['user_id']));
				$buyerfullname = fetch_user('fullname', $res['user_id']);
				$buyerusername = fetch_user('username', $res['user_id']);
				$buyercountryid = fetch_user('country', $res['user_id']);
				$buyerstate = fetch_user('state', $res['user_id']);
			}
			else
			{
				$shippinginfo = handle_input_keywords($ilance->shipping->print_shipping_address_text($res['buyer_id']));
				$buyerfullname = fetch_user('fullname', $res['buyer_id']);
				$buyerusername = fetch_user('username', $res['buyer_id']);
				$buyercountryid = fetch_user('country', $res['buyer_id']);
				$buyerstate = fetch_user('state', $res['buyer_id']);
			}
		}
		$shippingservice = $ilance->shipping->print_shipping_methods($res['project_id'], $qty, false, false, true, $buyercountryid, $_SESSION['ilancedata']['user']['slng']);
		$shippingpartner = '<!--<span style="float:left; padding-right:8px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'shiptruck.gif" border="0" alt="" /></span>-->' . $ilance->shipping->print_shipping_partner($res['buyershipperid']);
		$shiptemp = $ilance->shipping->fetch_ship_cost_by_shipperid(intval($ilance->GPC['pid']), $res['buyershipperid'], $qty, $res['buyershipcost']);
		if ($shiptemp['total'] > 0)
		{
			$shipping = $ilance->currency->format($shiptemp['total'], $currencyid);
		}
		else
		{
			$shipping = '{_none}';
		}
		$shipping_plain = sprintf("%01.2f", $shiptemp['total']);
	}
	else
	{
		if ($wonbybid)
		{
			$buyerfullname = fetch_user('fullname', $res['user_id']);
			$buyerusername = fetch_user('username', $res['user_id']);
			$buyerstate = fetch_user('state', $res['user_id']);
		}
		else
		{
			$buyerfullname = fetch_user('fullname', $res['buyer_id']);
			$buyerusername = fetch_user('username', $res['buyer_id']);
			$buyerstate = fetch_user('state', $res['buyer_id']);
		}
		$shippinginfo = '{_local_pickup_only}';
		$shippingpartner = $shippingservice = '';
		$shipping = '{_local_pickup_only}';
		$shiptemp['total'] = $shipping_plain = sprintf("%01.2f", 0);
	}
	if ($wonbybid)
	{
		$paymentinfo = $ilance->payment->print_fixed_payment_method($res['buyerpaymethod'], false);
		$ordermerchant = fetch_user('username', $res['project_user_id']);
		$orderamount = $res['bidamount'];
		$orderdate = print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
	}
	else
	{
		$paymentinfo = $ilance->payment->print_fixed_payment_method($res['buyerpaymethod'], false);
		$paymentstatus = '';
		$res2['amount'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($res['invoiceid']) . "'", "amount"));
		if ($res['status'] == 'delivered')
		{
			$orderamount = $res2['amount'] = $res2['amount'] - $shipping_plain;
		}
		else
		{
			$orderamount = $res['amount'] = $res['amount'] - $shipping_plain;
		}
		$ordermerchant = fetch_user('username', $res['owner_id']);
		$orderdate = print_date($res['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
	}
	if ($res['winnermarkedaspaid'])
	{
		if ($res['buyerpaymethod'] == 'escrow')
		{
			if ($res['releasedate'] == '0000-00-00 00:00:00' AND $res['status'] == 'pending_delivery')
			{
				$paymentstatus = '{_escrow_account_funded_on} ' . print_date($res['winnermarkedaspaiddate']);
				$paymentstatus2 = '{_paid}';
			}
			else if ($res['releasedate'] != '0000-00-00 00:00:00')
			{
				$paymentstatus = '{_escrow_funds_released_on} ' . print_date($res['releasedate']);
				$paymentstatus2 = '{_paid}';
			}
			else
			{
				$paymentstatus = '{_marked_as_paid_on} ' . print_date($res['winnermarkedaspaiddate']);
				$paymentstatus2 = '{_paid}';
			}
		}
		else
		{
			$paymentstatus = '{_marked_as_paid_on} ' . print_date($res['winnermarkedaspaiddate']);
			$paymentstatus2 = '{_paid}';
		}
	}
	else
	{
		$paymentstatus = '{_unpaid}';
		$paymentstatus2 = '{_unpaid}';
	}
	$shippingstatus = '';
	if ($res['sellermarkedasshipped'] AND $res['sellermarkedasshippeddate'] != '0000-00-00 00:00:00')
	{
		$shippingstatus = '{_marked_as_shipped_on} ' . print_date($res['sellermarkedasshippeddate']);
	}
	if (!empty($res['salestaxstate']) AND $res['salestaxrate'] > 0)
	{
		$salestaxstate = $res['salestaxstate'];
		$salestaxrate = number_format(floatval($res['salestaxrate']), 1);
		$salestaxshipping = $res['salestaxshipping'];
	}
	else
	{
		$salestaxstate = fetch_auction('salestaxstate', $res['project_id']);
		$salestaxrate = number_format(floatval(fetch_auction('salestaxrate', $res['project_id'])), 1);
		$salestaxshipping = fetch_auction('salestaxshipping', $res['project_id']);
	}
	$price = $ilance->currency->format(($orderamount), $currencyid);
	$priceperitem = $ilance->currency->format(($orderamount / $qty), $currencyid);
	$amount = $ilance->currency->format(($orderamount), $currencyid);
	if (mb_strtolower($buyerstate) == mb_strtolower($salestaxstate) AND $salestaxrate > 0)
	{
		$show['taxes'] = true;
		if ($salestaxshipping)
		{
			$taxamount = $ilance->currency->format(((($orderamount) + $shiptemp['total']) * $salestaxrate / 100), $currencyid);
			$taxamount_plain = ((($orderamount) + $shiptemp['total']) * $salestaxrate / 100);
			$totalamount = $ilance->currency->format(($orderamount) + $shiptemp['total'] + $taxamount_plain, $currencyid);
			$cbsalestaxshipping = 'checked="checked"';
		}
		else
		{
			$taxamount = $ilance->currency->format((($orderamount) * $salestaxrate / 100), $currencyid);
			$taxamount_plain = (($orderamount) * $salestaxrate / 100);
			$totalamount = $ilance->currency->format(($orderamount) + $shiptemp['total'] + $taxamount_plain, $currencyid);
			$cbsalestaxshipping = '';
		}
		$taxpulldown = $ilance->common_location->construct_state_pulldown($buyercountryid, $buyerstate, 'salestaxstate', false, true);
	}
	else
	{
		$show['taxes'] = false;
		$taxamount = $taxamount_plain = 0;
		$totalamount = $ilance->currency->format(($orderamount) + $shiptemp['total'], $currencyid);
	}
	if (isset($ilance->GPC['send']) AND !empty($ilance->GPC['send']))
	{
		die('Invoice sending will be ready soon.  Thanks for your patience.');
	}
	else if (isset($ilance->GPC['update']) AND !empty($ilance->GPC['update']))
	{
		$ilance->GPC['salestaxshipping'] = isset($ilance->GPC['salestaxshipping']) ? $ilance->GPC['salestaxshipping'] : 0;
		$ilance->GPC['salestaxstate'] = isset($ilance->GPC['salestaxstate']) ? $ilance->GPC['salestaxstate'] : '';
		$ilance->GPC['salestaxrate'] = isset($ilance->GPC['salestaxrate']) ? $ilance->GPC['salestaxrate'] : 0;
		if ($wonbybid)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET buyershipperid = '" . intval($ilance->GPC['shipperid']) . "',
				buyershipcost = '" . $ilance->db->escape_string($ilance->GPC['buyershipcost']) . "',
				salestax = '" . $ilance->db->escape_string($taxamount_plain) . "',
				salestaxstate = '" . $ilance->db->escape_string($ilance->GPC['salestaxstate']) . "',
				salestaxrate = '" . $ilance->db->escape_string($ilance->GPC['salestaxrate']) . "',
				salestaxshipping = '" . intval($ilance->GPC['salestaxshipping']) . "'
				WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
					AND bid_id = '" . intval($ilance->GPC['bid']) . "'
			");
		}
		else
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "buynow_orders
				SET buyershipperid = '" . intval($ilance->GPC['shipperid']) . "',
				buyershipcost = '" . $ilance->db->escape_string($ilance->GPC['buyershipcost']) . "',
				salestax = '" . $ilance->db->escape_string($taxamount_plain) . "',
				salestaxstate = '" . $ilance->db->escape_string($ilance->GPC['salestaxstate']) . "',
				salestaxrate = '" . $ilance->db->escape_string($ilance->GPC['salestaxrate']) . "',
				salestaxshipping = '" . intval($ilance->GPC['salestaxshipping']) . "'
				WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
					AND orderid = '" . intval($ilance->GPC['oid']) . "'
			");
		}
		refresh(HTTP_SERVER . $ilpage['selling'] . '?cmd=management&sub=sold');
		exit();
	}
	$description = '<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . handle_input_keywords(fetch_auction('project_title', $res['project_id'])) . '</a></span> <span class="smaller">(' . $res['project_id'] . ')</span>';
	$project_id = $res['project_id'];
	$bid = isset($ilance->GPC['bid']) ? intval($ilance->GPC['bid']) : 0;
	$cancelstatus = '';
	$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
	$pprint_array = array('currencyinfo','currencysymbol2','returnurl','oid','bid','cbsalestaxshipping','taxpulldown','taxamount','salestaxrate','shippingservice','shipping_plain','ordernumber','priceperitem','cancelstatus','shippingstatus','buyerusername','paymentstatus','paymentstatus2','buyerfullname','orderdate','ordermerchant','qty','price','amount','taxamount','totalamount','shipping','paymentinfo','shippingpartner','shippinginfo','project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','redirect','referer');
		
	($apihook = $ilance->api('orderdetails_end')) ? eval($apihook) : false;
	
	$ilance->template->fetch('main', 'invoicepayment_orderstatus.html');
	$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
// #### ITEM PACKING SLIP ######################################################
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'packingslip' AND isset($ilance->GPC['pid']) AND $ilance->GPC['pid'] > 0)
{
	$area_title = '{_packing_slip}';
	$page_title = SITE_NAME . ' - {_packing_slip}';
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = '{_accounting}';
	$navcrumb[""] = '{_packing_slip}';
	$wonbybid = false;
	
	($apihook = $ilance->api('packingslipdetails_start')) ? eval($apihook) : false;
	
	if (isset($ilance->GPC['bid']) AND $ilance->GPC['bid'] > 0)
	{
		$wonbybid = true;
	}
	if ($wonbybid)
	{
		$query = "
			SELECT *
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND bid_id = '" . intval($ilance->GPC['bid']) . "'
				AND (user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "' OR project_user_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
	}
	else
	{
		$query = "
			SELECT *
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($ilance->GPC['pid']) . "'
				AND (buyer_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "' OR owner_id = '" . intval($_SESSION['ilancedata']['user']['userid']) . "')";
	}
	$sql = $ilance->db->query($query);
	if ($ilance->db->num_rows($sql) == 0)
	{
		$area_title = '{_invoice_payment_menu_denied_payment}';
		$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
		print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$buyer_id = ($wonbybid) ? $res['user_id'] : $res['buyer_id'];
	$owner_id = ($wonbybid) ? $res['project_user_id'] : $res['owner_id'];
	$sql_buyer = $ilance->db->query("SELECT username, first_name, last_name, city, state, zip_code, email, phone, address FROM " . DB_PREFIX . "users WHERE user_id = '" . $buyer_id . "'");
	$buyer = $ilance->db->fetch_array($sql_buyer, DB_ASSOC);
	$sql_owner = $ilance->db->query("SELECT username, first_name, last_name, city, state, zip_code, email, phone FROM " . DB_PREFIX . "users WHERE user_id = '" . $owner_id . "'");
	$owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
	$sql_project = $ilance->db->query("SELECT currencyid, project_title, project_id FROM " . DB_PREFIX . "projects WHERE project_id = '" . $res['project_id'] . "'");
	$project = $ilance->db->fetch_array($sql_project, DB_ASSOC);

	$show['shipping'] = false;
	$shippinginfo = $shippingpartner = $shipping = '';
	if (isset($res['ship_required']) AND $res['ship_required'] OR isset($res['buyershipperid']) AND $res['buyershipperid'] > 0)
	{
		$show['shipping'] = true;
		$shippinginfo = (isset($res['ship_location'])) ? handle_input_keywords($res['ship_location']) : handle_input_keywords($ilance->shipping->print_shipping_address_text($buyer_id));
		$shippingpartner = '<span style="float:left; padding-right:8px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'shiptruck.gif" border="0" alt="" /></span>' . $ilance->shipping->print_shipping_partner($res['buyershipperid']);
		$shipping = $ilance->currency->format($res['buyershipcost'], $project['currencyid']);
	}
	else
	{
		$shippinginfo = '{_local_pickup_only}';
		$shippingpartner = '';
		$shipping = '{_local_pickup_only}';
	}
	$price = $ilance->GPC['amount'];
	$qty = $res['qty'];
	$taxamount = '';
	$paymentinfo = $ilance->payment->print_fixed_payment_method($res['buyerpaymethod'], false);
	$orderamount = $price;
	$orderdate = ($wonbybid) ? print_date($res['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) : print_date($res['orderdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);

	// shipping location
	$shiptolocation = $shippinginfo;
	
	$orderqty = $quantity = $res['qty'];
	$totalamount = $ilance->currency->format(($orderamount * $res['qty']) + $res['buyershipcost'], $project['currencyid']);
	$amount = $ilance->currency->format($orderamount, $project['currencyid']);
	$description = '<span class="blueonly"><a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . $project['project_title'] . '</a></span> <span class="smaller">(' . $res['project_id'] . ')</span>';
	$ilance->template->jsinclude = array('header' => array(), 'footer' => array());
	
	$pprint_array = array('orderdate','quantity','orderqty', 'shiptolocation','orderdate','qty','price','amount','taxamount','totalamount','shipping','paymentinfo','shippingpartner','shippinginfo');
		
	($apihook = $ilance->api('packingslip_end')) ? eval($apihook) : false;
	
	$ilance->template->load_popup('popupmain', 'invoicepayment_packingslip.html');
	$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage, 'buyer' => $buyer, 'owner' => $owner, 'project' => $project));
	$ilance->template->parse_if_blocks('popupmain');
	$ilance->template->pprint('popupmain', $pprint_array);
	exit();
}
// #### DETAILED TRANSACTION VIEW ##############################
else
{
	($apihook = $ilance->api('invoicepayment_view_start')) ? eval($apihook) : false;
	
	$txn = $securekey_hidden = '';
	$id  = 0;
	if (isset($uncrypted['id']) AND $uncrypted['id'] > 0)
	{
		$id  = intval($uncrypted['id']);
	}
	else if (isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
	{
		$id  = intval($ilance->GPC['id']);
	}
	else if (isset($uncrypted['txn']) AND $uncrypted['txn'] != '')
	{
		$txn = $uncrypted['txn'];
	}
	else if (isset($ilance->GPC['txn']) AND $ilance->GPC['txn'] != '')
	{
		$txn  = $ilance->GPC['txn'];
	}
	else
	{
		$area_title = '{_invoice_payment_menu_denied_payment}';
		$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
		print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
	
	($apihook = $ilance->api('invoicepayment_start')) ? eval($apihook) : false;

	if (!empty($txn))
	{
		$sql_invoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE transactionid = '" . $ilance->db->escape_string($txn) . "'
				AND (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
		");
		$sqlinvoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE transactionid = '" . $ilance->db->escape_string($txn) . "'
				AND (user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "' OR p2b_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "')
		");
		if ($ilance->db->num_rows($sqlinvoice) > 0)
		{
			$res = $ilance->db->fetch_array($sqlinvoice);
			$id = $res['invoiceid'];
		}
		$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	}
	else if (isset($id) AND $id > 0)
	{
		$sql_invoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($id) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		$sqlinvoice = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "invoices
			WHERE invoiceid = '" . intval($id) . "'
				AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		if ($ilance->db->num_rows($sqlinvoice) > 0)
		{
			$res = $ilance->db->fetch_array($sqlinvoice, DB_ASSOC);
			$txn = $res['transactionid'];
		}
		$securekey_hidden .= '<input type="hidden" name="id" value="' . $id . '" /><input type="hidden" name="txn" value="' . $txn . '" />';
	}
	$headtitle = (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view') ? '{_review_invoice_details} (' . $id . ')' : '{_secure_payment_preview}';
	$headmessage = (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'view') ? '{_you_can_review_download_and_print_this_invoice_page_for_your_records}' : '{_make_a_secure_payment_using_our_billing}';
	$area_title = '{_invoice_payment_menu} #' . $txn;
	$page_title = SITE_NAME . ' - {_invoice_payment_menu}';
	$navcrumb = array();
	$navcrumb["$ilpage[accounting]"] = '{_accounting}';
	$navcrumb[""] = '{_transaction} #' . $txn;
	if ($ilance->db->num_rows($sql_invoice) > 0)
	{
		$show['invoicecancelled'] = 0;
		$res_invoice = $ilance->db->fetch_array($sql_invoice,  DB_ASSOC);
		$project_currency = fetch_auction('currencyid', $res_invoice['projectid']);
		if ($res_invoice['status'] == 'unpaid' OR $res_invoice['status'] == 'scheduled')
		{
			if ($res_invoice['p2b_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['paymentpulldown'] = 0;
				$cmd = '_do-invoice-action';
			}
			else if ($res_invoice['user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['paymentpulldown'] = 1;
				$cmd = '_do-invoice-preview';
			}
		}
		else if ($res_invoice['status'] == 'cancelled')
		{
			$show['invoicecancelled'] = 1;
		}
		else
		{
			$show['paymentpulldown'] = 0;
			$cmd = '_do-invoice-action';
		}
		$paymethod = '{_' . $res_invoice['paymethod'] . '}';
		$paystatus = '{_' . mb_strtolower($res_invoice['status']) . '}';
		$providername = '{_billing_and_payments}';
		$provider = SITE_NAME;
		$providerinfo = SITE_ADDRESS;
		$show['viewingasprovider'] = $show['escrowblock'] = false;
		if ($res_invoice['invoicetype'] == 'subscription')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'commission')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'p2b')
		{
			$show['providerblock'] = true;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$provider = fetch_user('username', $res_invoice['p2b_user_id']);
			$invoicetype = '{_generated_invoice}';
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$providerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['p2b_user_id']) . fetch_business_numbers($res_invoice['p2b_user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
			$providername = fetch_user('fullname', $res_invoice['p2b_user_id']);
			$paymethod = (mb_substr($res_invoice['p2b_paymethod'], 0, 1) == '_') ? '{' . $res_invoice['p2b_paymethod'] . '}' : $res_invoice['p2b_paymethod'];
			if ($res_invoice['p2b_user_id'] == $_SESSION['ilancedata']['user']['userid'])
			{
				$show['viewingasprovider'] = true;
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markaspaid',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				$markedaspaidurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markasunpaid',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				$markedasunpaidurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
				
				$crypted = array(
					'cmd' => 'p2baction',
					'subcmd' => 'markascancelled',
					'invoiceid' => $res_invoice['invoiceid'],
					'txn' => $res_invoice['transactionid'],
				);
				$markedascancelledurl = HTTPS_SERVER . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted);
				unset($crypted);
			}
			if (empty($res_invoice['p2b_paymethod']) OR $res_invoice['p2b_paymethod'] == '')
			{
				$paymethod = '{_contact_trading_partner}';
			}
		}
		else if ($res_invoice['invoicetype'] == 'buynow')
		{
			$show['providerblock'] = true;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$provider = fetch_user('username', $res_invoice['p2b_user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$providerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['p2b_user_id']) . fetch_business_numbers($res_invoice['p2b_user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
			$providername = fetch_user('fullname', $res_invoice['p2b_user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'credential')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'debit')
		{
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'credit')
		{
			$show['paymentpulldown'] = 0;
			$cmd = '_do-invoice-action';
			$show['providerblock'] = false;
			$customer = fetch_user('username', $res_invoice['user_id']);
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
			$customerinfo = $ilance->shipping->print_shipping_address_text($res_invoice['user_id']) . fetch_business_numbers($res_invoice['user_id']);
			$customername = fetch_user('fullname', $res_invoice['user_id']);
		}
		else if ($res_invoice['invoicetype'] == 'escrow')
		{
			// escrow handling
			$show['providerblock'] = true;
			$show['escrowblock'] = true;
			// quick auction checkup
			$sql_auction = $ilance->db->query("
				SELECT project_id
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . $res_invoice['projectid'] . "'
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql_auction) == 0)
			{
				$area_title = '{_invoice_payment_menu_denied_payment}';
				$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
				print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
				exit();
			}
			if ($ilance->auction->fetch_auction_type($res_invoice['projectid']) == 'service')
			{
				// buyer is about to pay service escrow invoice to service provider escrow account held by site owner
				$customer = $ilance->escrow->fetch_escrow_owner($res_invoice['projectid'], $res_invoice['invoiceid'], 'service');
				$provider = $ilance->escrow->fetch_escrow_opponent($res_invoice['projectid'], $res_invoice['invoiceid'], 'service');
				$customerinfo = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $customer)) . fetch_business_numbers(fetch_user('user_id', '', $customer));
				$providerinfo = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $provider)) . fetch_business_numbers(fetch_user('user_id', '', $provider));
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));
				$providername = fetch_user('fullname', fetch_user('user_id', '', $provider));
			}
			else if ($ilance->auction->fetch_auction_type($res_invoice['projectid']) == 'product')
			{
				// bidder/winner is about to pay product escrow invoice to merchant provider held by site owner
				$customer = $ilance->escrow->fetch_escrow_opponent($res_invoice['projectid'], $res_invoice['invoiceid'], 'product');
				$provider = $ilance->escrow->fetch_escrow_owner($res_invoice['projectid'], $res_invoice['invoiceid'], 'product');
				$customerinfo = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $customer)) . fetch_business_numbers(fetch_user('user_id', '', $customer));
				$providerinfo = $ilance->shipping->print_shipping_address_text(fetch_user('user_id', '', $provider)) . fetch_business_numbers(fetch_user('user_id', '', $provider));
				$customername = fetch_user('fullname', fetch_user('user_id', '', $customer));
				$providername = fetch_user('fullname', fetch_user('user_id', '', $provider));
			}
			// display invoice type on invoice payment form
			$invoicetype = $ilance->accounting_print->print_transaction_type($res_invoice['invoicetype']);
		}
		
		($apihook = $ilance->api('invoicepayment_transaction_view_condition_end')) ? eval($apihook) : false;
		
		// transaction description
		$description = stripslashes($res_invoice['description']);
		// transaction identifier
		$transactionid = $res_invoice['transactionid'];
		// comments left by invoicer / receiver
		$comments = stripslashes($res_invoice['custommessage']);
		// invoice amount
		$res_invoice['currency_id'] = ($res_invoice['currency_id'] == '0') ? $ilconfig['globalserverlocale_defaultcurrency'] : $res_invoice['currency_id'];
		$amount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
		// total invoice amount (after taxes what customer will pay)
		$show['taxes'] = 0;
		if ($res_invoice['istaxable'])
		{
			$totalamount = $ilance->currency->format(($res_invoice['amount'] + $res_invoice['taxamount']), $res_invoice['currency_id']);
			$show['taxes'] = 1;
		}
		else
		{
			$totalamount = $ilance->currency->format($res_invoice['amount'], $res_invoice['currency_id']);
		}
		// total amount paid for this invoice
		$amountpaid = $ilance->currency->format($res_invoice['paid'], $res_invoice['currency_id']);
		// invoice creation date
		$createdate = print_date($res_invoice['createdate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		// invoice due date
		if ($res_invoice['duedate'] == "0000-00-00 00:00:00")
		{
			$duedate = '--';		
		}
		else
		{
			$duedate = print_date($res_invoice['duedate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		// invoice paid date
		if ($res_invoice['paiddate'] == "0000-00-00 00:00:00")
		{
			$paiddate = '--';
		}
		else
		{
			$paiddate = print_date($res_invoice['paiddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
		}
		// custom invoice message
		$custommessage = stripslashes($res_invoice['custommessage']);
		$show['comments'] = 1;
		if (empty($custommessage))
		{
			$show['comments'] = 0;
		}
		$show['listing'] = 0;
		$project_id = 0;
		if ($res_invoice['projectid'] > 0)
		{
			$show['listing'] = 1;
			$listing = fetch_auction('project_title', $res_invoice['projectid']);
			$project_id = $res_invoice['projectid'];
		}
		// invoice identifier
		$invoiceid = $id;
		// payment method pulldown
		$payment_method_pulldown = $ilance->accounting_print->print_paymethod_pulldown('invoicepayment', 'account_id', $_SESSION['ilancedata']['user']['userid']);
		// tax information
		$taxinfo = substr($res_invoice['taxinfo'], strripos($res_invoice['taxinfo'], '@')+2, 3) . ' {_tax}:';
		$taxamount = $ilance->currency->format($res_invoice['taxamount'], $res_invoice['currency_id']);
		$show['ispaid'] = $show['isunpaid'] = $show['isscheduled'] = $show['iscomplete'] = $show['iscancelled'] = 0;
		if ($res_invoice['status'] == 'paid')
		{
			$show['ispaid'] = 1;
		}
		else if ($res_invoice['status'] == 'unpaid')
		{
			$show['isunpaid'] = 1;
		}
		else if ($res_invoice['status'] == 'scheduled')
		{
			$show['isscheduled'] = 1;
		}
		else if ($res_invoice['status'] == 'complete')
		{
			$show['iscomplete'] = 1;
		}
		else if ($res_invoice['status'] == 'cancelled')
		{
			$show['iscancelled'] = 1;
		}
		if ($res_invoice['invoicetype'] == 'subscription')
		{
			$show['subscriptionpayment'] = true;
		}
		else
		{
			$show['subscriptionpayment'] = false;
		}
		$pprint_array = array('project_id','paystatus','markedascancelledurl','markedaspaidurl','markedasunpaidurl','paymethod','listing','headtitle','headmessage','cmd','customername','providername','customerinfo','providerinfo','totalamount','taxinfo','taxamount','transactionid','comments','provider','customer','payment_method_pulldown','invoiceid','invoicetype','description','amount','amountpaid','createdate','duedate','paiddate','custommessage','securekey_hidden','countdrafts','countarchived','rfpescrow','rfpvisible','countdelisted','prevnext','redirect','referer');
		
		($apihook = $ilance->api('invoicepayment_end')) ? eval($apihook) : false;
		
		$ilance->template->fetch('main', 'invoicepayment.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	else
	{
		$area_title = '{_invoice_payment_menu_denied_payment}';
		$page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
		print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', HTTPS_SERVER . $ilpage['accounting'], '{_my_account}');
		exit();
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>