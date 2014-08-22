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
	'subscription'
);

// #### setup script location ##################################################
define('LOCATION', 'subscription');

// #### require backend ########################################################
require_once('./functions/config.php');

// #### setup default breadcrumb ###############################################
$navcrumb = array("$ilpage[subscription]" => $ilcrumbs["$ilpage[subscription]"]);

// #### SUBSCRIPTION PERMISSIONS POPUP #########################################
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'access')
{
	if (empty($ilance->GPC['gid']) OR isset($ilance->GPC['gid']) AND $ilance->GPC['gid'] == 0)
	{
		$area_title = '{_bad_subscription_group_id}';
		$page_title = SITE_NAME . ' - ' . '{_bad_subscription_group_id}';
		$pprint_array = array('input_style');
		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_denied.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
		$ilance->template->pprint('popupmain', $pprint_array);
		$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
		exit();
	}
	else
	{
		$area_title = '{_viewing_subscription_group_access_rights}';
		$page_title = SITE_NAME . ' - ' . '{_viewing_subscription_group_access_rights}';
		$subscriptiongroupid = intval($ilance->GPC['gid']);
		$subscriptionid = intval($ilance->GPC['id']);
		// #### do not reference any credit card information if disabled
		$extrasql = ($ilconfig['use_internal_gateway'] == 'none') ? "AND accessname != 'addcreditcard' AND accessname != 'delcreditcard' AND accessname != 'usecreditcard'" : '';
		// #### do not reference any service auction information if disabled
		$extrasql2 = ($ilconfig['globalauctionsettings_serviceauctionsenabled'] == 0) ? "AND accessname != 'searchresults' AND accessname != 'generateinvoices' AND accessname != 'servicebid' AND accessname != 'createserviceprofile' AND accessname != 'addportfolio' AND accessname != 'inviteprovider'" : '';
		// #### do not reference any product auction information if disabled
		$extrasql3 = ($ilconfig['globalauctionsettings_productauctionsenabled'] == 0) ? "AND accessname != 'buynow' AND accessname != 'productbid' AND accessname != 'buynow'" : '';
		$sqlitems = $ilance->db->query("
			SELECT id, accessname, subscriptiongroupid, value, visible
			FROM " . DB_PREFIX . "subscription_permissions
			WHERE subscriptiongroupid = '" . intval($subscriptiongroupid) . "'
			$extrasql
			$extrasql2
			$extrasql3
		");
		if ($ilance->db->num_rows($sqlitems) > 0)
		{
			$row_count2 = 0;
			while ($resitems = $ilance->db->fetch_array($sqlitems, DB_ASSOC))
			{
				if ($resitems['visible'] != 0)
				{                        	
					if ($resitems['value'] == 'yes' OR $resitems['value'] == 'no')
					{
						$userinput = ($resitems['value'] == 'yes') ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="{_yes}" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="{_no}" />';
					}
					else
					{
						$userinput = ($resitems['accessname'] == 'attachlimit' OR $resitems['accessname'] == 'uploadlimit' OR $resitems['accessname'] == 'bulkattachlimit') ? print_filesize($resitems['value']) : $resitems['value'];
					}
					$resitems['accesstext'] = stripslashes("{_" . $resitems['accessname'] . "_text}");
					$resitems['userinput'] = $userinput;
					$row_count2++;
					$resitems['class2'] = ($row_count2 % 2) ? 'alt1' : 'alt2';
					$access_permission_items[] = $resitems;
				}
			}
		}
		// #### setup some defaults just in case #######################
		$plan_title = $title = $description = '{_unknown}';
		// #### select subscription information ########################
		$sql_sub = $ilance->db->query("
			SELECT title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, description_" . $_SESSION['ilancedata']['user']['slng'] . " AS description
			FROM " . DB_PREFIX . "subscription
			WHERE subscriptionid = '" . intval($ilance->GPC['id']) . "'
		");
		if ($ilance->db->num_rows($sql_sub) > 0)
		{
			$res_sub = $ilance->db->fetch_array($sql_sub, DB_ASSOC);
			$plan_title = handle_input_keywords(stripslashes($res_sub['title']));
			$title = handle_input_keywords(stripslashes($res_sub['title']));
			$description = handle_input_keywords(stripslashes($res_sub['description']));
		}
		$pprint_array = array('plan_title','title','description','attachlimit','buynow','workshare','pmb','deposit','withdraw','addcreditcard','addbankaccount','distance','bid','attachments','addportfolio','inviteprovider','addtowatchlist','enablecurrencyconversion');
		$ilance->template->load_popup('popupheader', 'popup_header.html');
		$ilance->template->load_popup('popupmain', 'popup_subscription_permissions.html');
		$ilance->template->load_popup('popupfooter', 'popup_footer.html');
		$ilance->template->parse_hash('popupheader', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupmain', array('ilpage' => $ilpage));
		$ilance->template->parse_hash('popupfooter', array('ilpage' => $ilpage));                
		$ilance->template->parse_loop('popupmain', 'access_permission_items');
		$ilance->template->parse_if_blocks('popupheader');
		$ilance->template->parse_if_blocks('popupmain');
		$ilance->template->parse_if_blocks('popupfooter');
		$ilance->template->pprint('popupheader', array('headinclude','onload','onbeforeunload','meta_desc','meta_keyw','official_time') );
		$ilance->template->pprint('popupmain', $pprint_array);
		$ilance->template->pprint('popupfooter', array('finaltime','finalqueries'));
		exit();
	}
}
else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'process_recurring_payment' AND isset($_POST['a3']) AND isset($_POST['p3']) AND isset($_POST['t3']) AND isset($_POST['item_name']) AND isset($_POST['item_number']))
{
	$_POST['creditcard_number'] = isset($_POST['creditcard_number']) ? $_POST['creditcard_number'] : '';
	$_POST['creditcard_type'] = isset($_POST['creditcard_type']) ? $_POST['creditcard_type'] : '';
	$_POST['creditcard_cvv2'] = isset($_POST['creditcard_cvv2']) ? $_POST['creditcard_cvv2'] : '';
	$_POST['creditcard_month'] = isset($_POST['creditcard_month']) ? $_POST['creditcard_month'] : '';
	$_POST['creditcard_year'] = isset($_POST['creditcard_year']) ? $_POST['creditcard_year'] : '';
	$_POST['creditcard_firstname'] = isset($_POST['creditcard_firstname']) ? $_POST['creditcard_firstname'] : '';
	$_POST['creditcard_lastname'] = isset($_POST['creditcard_lastname']) ? $_POST['creditcard_lastname'] : '';
	$_POST['creditcard_billing'] = isset($_POST['creditcard_billing']) ? $_POST['creditcard_billing'] : '';
	$_POST['creditcard_postal'] = isset($_POST['creditcard_postal']) ? $_POST['creditcard_postal'] : '';
	$custom = isset($_POST['customencrypted']) ? urldecode($_POST['customencrypted']) : '';
	if (isset($custom) AND !empty($custom))
	{
		$custom = explode('|', $custom);
	}
	else
	{
		echo 'This script requires well-formed parameters.  Operation aborted.';
		exit();
	}
	$roleid = isset($custom[8])  ? intval($custom[8]) : '-1';
	$conf = array(
		'api_username' => trim($ilconfig['paypal_pro_username']), 
		'api_password' => trim($ilconfig['paypal_pro_password']), 
		'api_signature' => trim($ilconfig['paypal_pro_signature']), 
		'use_proxy' => '', 
		'proxy_host' => '', 
		'proxy_port' => '', 
		'return_url' => '', 
		'cancel_url' => ''
	);
	$ilance->paypal_pro = construct_object('api.paypal_pro', $conf, $ilconfig['paypal_pro_sandbox']);
	$ilance->paypal_pro->ip_address = $_SERVER['REMOTE_ADDR'];
	$ilance->paypal_pro->billing_frequency = $_POST['p3'];
	$ilance->paypal_pro->billing_period = $_POST['t3'];
	$ilance->paypal_pro->billing_period = 'Month';
	if($_POST['t3'] == 'M')
	{
		$ilance->paypal_pro->billing_period = 'Month';
	}	
	else if ($_POST['t3'] == 'D')
	{
		$ilance->paypal_pro->billing_period = 'Day';
	}
	else if ($_POST['t3'] == 'Y')
	{
		$ilance->paypal_pro->billing_period = 'Year';
	}
	$ilance->paypal_pro->billing_amount = ($_POST['a3']);
	$ilance->paypal_pro->profile_start_date = DATETIME24H;
	$ilance->paypal_pro->amount = $_POST['a3'];
	$ilance->paypal_pro->amt = $_POST['a3'];
	// Credit Card Information (required)
	$ilance->paypal_pro->credit_card_number = $_POST['creditcard_number'];
	$ilance->paypal_pro->credit_card_type = ucfirst($_POST['creditcard_type']);
	$ilance->paypal_pro->cvv2_code = $_POST['creditcard_cvv2'];
	$ilance->paypal_pro->expire_date = $_POST['creditcard_month'] . $_POST['creditcard_year'];
	// Billing Details (required)
	$ilance->paypal_pro->first_name = $_POST['creditcard_firstname'];
	$ilance->paypal_pro->last_name = $_POST['creditcard_lastname'];
	$ilance->paypal_pro->address1 = $_POST['creditcard_billing'];
	$ilance->paypal_pro->address2 = $_POST['creditcard_billing'];
	$ilance->paypal_pro->city = !empty($_SESSION['ilancedata']['user']['city']) ? $_SESSION['ilancedata']['user']['city'] : $_POST['creditcard_billing'];
	$ilance->paypal_pro->state = $_SESSION['ilancedata']['user']['state'];
	$ilance->paypal_pro->postal_code = $_POST['creditcard_postal'];
	$ilance->paypal_pro->phone_number = $_SESSION['ilancedata']['user']['phone'];
	$ilance->paypal_pro->country_code = $_SESSION['ilancedata']['user']['countryshort'];
	
	$ilance->template->templateregistry['item_name'] = $_POST['item_name'];
     $_POST['item_name'] = $ilance->template->parse_template_phrases('item_name');
	$ilance->paypal_pro->addItem($_POST['item_name'], $_POST['item_number'], '1', 0, $_POST['a3']);
	$ilance->paypal_pro->description = $_POST['item_name'];
	// Perform the payment
	$response = $ilance->paypal_pro->create_recurring_payments_profile();
	if ($response AND isset($ilance->paypal_pro->Response['ACK']) AND strtoupper($ilance->paypal_pro->Response['ACK']) == 'SUCCESS')
	{
		$startdate = DATETIME24H;
		$renewdate = print_subscription_renewal_datetime($ilance->subscription->subscription_length($_POST['t3'], $_POST['p3']));
		$recurring = 1;
		$paymethod = mb_strtoupper($_POST['creditcard_type']) . ' : #' . $_POST['creditcard_cvv2'];
		// create new invoice associated with this paypal subscription transaction
		$invoiceid = $ilance->accounting->insert_transaction(
			$_POST['item_number'],
			0,
			0,
			intval($_SESSION['ilancedata']['user']['userid']),
			0,
			0,
			0,
			$_POST['item_name'] . ' [SUBSCR_ID: ' . $_POST['item_number'] . ']',
			sprintf("%01.2f", $_POST['a3']),
			sprintf("%01.2f", $_POST['a3']),
			'paid',
			'debit',
			$_POST['creditcard_type'],
			DATETIME24H,
			DATEINVOICEDUE,
			DATETIME24H,
			$_POST['item_number'],
			0,
			0,
			1,
			'',
			0,
			0
		);
		// activate subscription plan
		$ilance->subscription_plan->activate_subscription_plan($_SESSION['ilancedata']['user']['userid'], $startdate, $renewdate, $recurring, $invoiceid, $_POST['item_number'], $paymethod, $roleid, $_POST['a3']);
		$ilance->referral->update_referral_action('subscription', $_SESSION['ilancedata']['user']['userid']);
		$area_title = '{_subscription_menu}';
		$page_title = SITE_NAME . ' - ' . '{_subscription_menu}';
		print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['subscription'], '{_subscription_menu}');
		exit();
	}
	else 
	{
		$transaction_message = $ilance->paypal_pro->Response['L_LONGMESSAGE0'];
		$error_code = isset($ilance->paypal_pro->Response['L_ERRORCODE0']) ? $ilance->paypal_pro->Response['L_ERRORCODE0'] : '';
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
			'{{gateway}}' => '{_' . $ilconfig['use_internal_gateway'] . '}',
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

if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
{
	// user requesting subscription auto-payment change..
	$show['autopaymentchanged'] = $show['autorenewalchanged'] = false;
	if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-autopayment-change')
	{
		if (isset($ilance->GPC['autopayment']) AND $ilance->GPC['autopayment'] != '')
		{
			$show['autopaymentchanged'] = true;
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET autopayment = '" . intval($ilance->GPC['autopayment']) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
		}
		refresh('', $ilance->GPC['returnurl']);
		exit();
	}
	// user requesting subscription auto-renewal change..
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-autorenewal-change')
	{
		if (isset($ilance->GPC['autorenewal']) AND $ilance->GPC['autorenewal'] != '')
		{
			$show['autorenewalchanged'] = true;
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "subscription_user
				SET autorenewal = '" . intval($ilance->GPC['autorenewal']) . "'
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
				LIMIT 1
			");
		}
		refresh('', $ilance->GPC['returnurl']);
		exit();
	}
	// #### SUBSCRIPTION UPGRADE PREVIEW ######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-upgrade-preview' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'upgrade')
	{          
		$area_title = '{_subscription_upgrade_preview}';
		$page_title = SITE_NAME . ' - ' . '{_subscription_upgrade_preview}';
		$navcrumb[""] = '{_subscription_upgrade_preview}';
		$subscriptionid = isset($ilance->GPC['subscriptionid']) ? intval($ilance->GPC['subscriptionid']) : 0;
		$returnurl = isset($ilance->GPC['returnurl']) ? urlencode($ilance->GPC['returnurl']) : '';
		// first off, let's find out if this user already has an active subscription plan
		$sqlactive = $ilance->db->query("
			SELECT id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, autorenewal, active, cancelled, migrateto, migratelogic, recurring, invoiceid, roleid
			FROM " . DB_PREFIX . "subscription_user
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		$show['hassubscription'] = ($ilance->db->num_rows($sqlactive) > 0) ? 1 : 0;
		$sql_sub = $ilance->db->query("
			SELECT subscriptionid, title_" . $_SESSION['ilancedata']['user']['slng'] . " as title, description_" . $_SESSION['ilancedata']['user']['slng'] . " as description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
			FROM " . DB_PREFIX . "subscription
			WHERE subscriptionid = '" . $subscriptionid . "'
		");
		if ($ilance->db->num_rows($sql_sub) > 0)
		{
			$subscription_plan_result = $ilance->db->fetch_array($sql_sub, DB_ASSOC);
			$title = stripslashes($subscription_plan_result['title']);
			$description = stripslashes($subscription_plan_result['description']);
			$units = print_unit($subscription_plan_result['units']);
			$length = $subscription_plan_result['length'];
			$startdate = '{_after_payment}';
			$renewdate = $length . "&nbsp;" . $units;
			$sqlrenewdate = $ilance->db->query("
				SELECT renewdate
				FROM " . DB_PREFIX . "subscription_user
				WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			");
			if ($ilance->db->num_rows($sqlrenewdate) > 0)
			{
				$resrenew = $ilance->db->fetch_array($sqlrenewdate, DB_ASSOC);
				$workwith_date = $resrenew['renewdate'];
			}
			else
			{
				$workwith_date = DATETIME24H;
			}
			$future_date = cutstring($workwith_date, 10);
			$maxdate = $future_date;
			$mindate = DATETODAY;
			$maxdate = getdate(strtotime($maxdate));
			$mindate = getdate(strtotime($mindate));
			$difference = $maxdate[0] - $mindate[0];
			$difference = $difference / 24;
			$difference = $difference / 60;
			$difference = $difference / 60;
			$subscription_length = $ilance->subscription->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
			$subscription_plan_result['cost'] =  sprintf("%01.2f", $subscription_plan_result['cost']);
			$tax = 0;
			$taxformatted = $ilance->currency->format(0);
			if ($ilance->tax->is_taxable(intval($_SESSION['ilancedata']['user']['userid']), 'subscription'))
			{
				$tax = $ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), sprintf("%01.2f", $subscription_plan_result['cost']), 'subscription', 0);
				$taxformatted = $ilance->currency->format($ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), sprintf("%01.2f", $subscription_plan_result['cost']), 'subscription', 0));
			}
			$total = $ilance->currency->format($subscription_plan_result['cost'] + $tax);
			if ($subscription_plan_result['cost'] > 0)
			{
				$cost = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $subscription_plan_result['cost']);
				$show['paid_subscription_plan'] = true;
				$show['free_subscription_plan'] = false;
			}
			else
			{
				$cost = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $subscription_plan_result['cost']);
				$show['paid_subscription_plan'] = false;
				$show['free_subscription_plan'] = true;
			}
			// check for existing scheduled subscription transactions before we let user upgrade to a new plan
			$sql = $ilance->db->query("
                                SELECT subscriptionid, transactionid, amount
                                FROM " . DB_PREFIX . "invoices
                                WHERE subscriptionid > 0
                                        AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                                        AND (status = 'scheduled' OR status = 'pending') AND amount != '0.00'
                                ORDER BY createdate DESC
                                LIMIT 1
                        ");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$show['unpaidsubscription'] = true;
				$tablestyle = 'display: none;';
				$previousplantitle = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . intval($res['subscriptionid']) . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
				$previousinvoiceamount = $ilance->currency->format($res['amount']);
				$pendingtransactionid = $res['transactionid'];
			}
			else
			{
				$show['unpaidsubscription'] = false;
				$tablestyle = '';
			}
			$registration1 = $ilance->db->fetch_field(DB_PREFIX . "cms", "", "registrationterms");
			$pprint_array = array ('total','tax','taxformatted','registration1','returnurl', 'pendingtransactionid', 'previousinvoiceamount', 'previousplantitle', 'tablestyle', 'subscriptionid', 'length', 'subscription_length', 'units','title','cost');

			($apihook = $ilance->api('do_upgrade_preview_end')) ? eval($apihook) : false;

			$ilance->template->fetch('main', 'subscription_upgrade.html');
			$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
			$ilance->template->parse_if_blocks('main');
			$ilance->template->pprint('main', $pprint_array);
			exit();
		}
		else
		{
			print_notice('{_invalid_subscription_plan_selected}', '{_were_sorry_the_subscription_plan_selected_does_not_exist_or_is_temporarly_out_of_service}', $ilpage['subscription'], '{_subscription_menu}');
			exit();
		}
	}
	// #### SUBSCRIPTION UPGRADE PROCESS ######################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-upgrade-process' AND isset($ilance->GPC['subscriptionid']) AND $ilance->GPC['subscriptionid'] > 0)
	{
		$subscriptionid = intval($ilance->GPC['subscriptionid']);
		$agreecheck = (isset($ilance->GPC['agreecheck']) AND $ilance->GPC['agreecheck']) ? 1 : 0;
		$instantpay = (isset($ilance->GPC['instantpay']) AND $ilance->GPC['instantpay']) ? 1 : 0;
		$nocost = (isset($ilance->GPC['isfreeplan']) AND $ilance->GPC['isfreeplan']) ? 1 : 0;
		$recurring = 0;
		$paymethod = '';
		if (isset($ilance->GPC['recurring']) AND is_array($ilance->GPC['recurring']) AND !empty($ilance->GPC['recurring']['method']))
		{
			$recurring = 1;
			$paymethod = $ilance->GPC['recurring']['method'];
		}
		// this is used if the user is modifying their existing subscription plan
		// a flag will be sent to paypal (if used) which will allow that user to view current
		// subscription and modify it accordingly to the new settings we will pass it.
		$ismodify = (isset($ilance->GPC['subscriptionmodify']) AND $ilance->GPC['subscriptionmodify']) ? 1 : 0;
		// previous subscription transaction logic
		$removepending = (isset($ilance->GPC['removeprevioustransaction']) AND $ilance->GPC['removeprevioustransaction'] == 'true') ? true : false;
		$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
		// #### handle the subscription upgrade ########################
		$ilance->subscription->subscription_upgrade_process($_SESSION['ilancedata']['user']['userid'], $subscriptionid, $agreecheck, $instantpay, $nocost, $recurring, $paymethod, $ismodify, $removepending, $returnurl);
		exit();
	}
	// #### SUBSCRIPTION CANCELLATION PREVIEW #################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-subscription-manage' AND isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'cancel')
        {
		// subscription cancellation preview menu
		$area_title = '{_subscription_cancellation_preview_menu}';
		$page_title = SITE_NAME . ' - ' . '{_subscription_cancellation_preview_menu}';
                $navcrumb = array();
                $navcrumb["$ilpage[subscription]"] = '{_subscription}';
                $navcrumb[""] = '{_cancel}';
                $show['leftnav'] = true;
                $userid = $_SESSION['ilancedata']['user']['userid'];
                
                $sql = $ilance->db->query("
                        SELECT subscriptionid, renewdate, paymethod, invoiceid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
                        LIMIT 1
                ");
                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                $title = $ilance->db->fetch_field(DB_PREFIX . "subscription", "subscriptionid = '" . $res['subscriptionid'] . "'", "title_" . $_SESSION['ilancedata']['user']['slng']);
                $renewdate = print_date($res['renewdate']);
                $gateway = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['invoiceid'] . "'", "paymentgateway");
                $invoiceid = $res['invoiceid'];
                $hiddenfields = (!empty($gateway)) ? '<input type="hidden" name="paymentgateway" value="' . $gateway . '" />' : '';
                $hiddenfields .= (!empty($invoiceid) AND $invoiceid > 0) ? '<input type="hidden" name="invoiceid" value="' . $invoiceid . '" />' : '';
		$headinclude .= '<script type="text/javascript">
<!--
function validateSubscriptionCancel(f)
{
        haveerrors = 0;
        (f.password.value.length < 1) ? showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("passworderror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        (f.comment.value.length < 1) ? showImage("commenterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/fieldempty.gif", true) : showImage("commenterror", "' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/blankimage.gif", false);
        return (!haveerrors);
}
//-->
</script>';
		$pprint_array = array('title','renewdate','hiddenfields','userid','input_style');
		$ilance->template->fetch('main', 'subscription_cancellation.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'subscription_rows');
		$ilance->template->parse_loop('main', 'commission_rows');
		$ilance->template->parse_loop('main', 'upgrade_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
	// #### SUBSCRIPTION CANCELLATION HANDLER #################################
	else if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == '_do-subscription-cancellation' AND isset($ilance->GPC['password']) AND $ilance->GPC['password'] != '' AND isset($ilance->GPC['comment']) AND $ilance->GPC['comment'] != '')
	{
		$area_title = '{_subscription_cancellation_preview_menu}';
		$page_title = SITE_NAME . ' - ' . '{_subscription_cancellation_preview_menu}';
		$userid = $_SESSION['ilancedata']['user']['userid'];
		$salt = $ilance->db->fetch_field(DB_PREFIX . 'users', 'user_id = ' . $_SESSION['ilancedata']['user']['userid'], 'salt', '1');
		$pass = md5(md5($ilance->GPC['password']) . $salt);
		$paymentgateway = (isset($ilance->GPC['paymentgateway']) AND !empty($ilance->GPC['paymentgateway'])) ? $ilance->GPC['paymentgateway'] : '';
		$invoiceid = (isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] > 0) ? intval($ilance->GPC['invoiceid']) : 0;
		if ($pass == $_SESSION['ilancedata']['user']['password'])
		{
			$success = $ilance->subscription_plan->cancel_subscription_plan($_SESSION['ilancedata']['user']['userid'], $invoiceid, $paymentgateway);
			if ($success)
			{
				$area_title = '{_subscription_cancellation_request_complete}';
				$page_title = SITE_NAME . ' - ' . '{_subscription_cancellation_request_complete}';
				print_notice('{_your_subscription_plan_is_cancelled}', '{_you_have_requested_and_confirmed_cancellation_of_your_existing_subscription_plan}', $ilpage['subscription'], '{_subscription_menu}');
				exit();         
			}
			else
			{
				$area_title = '{_error_with_subscription_cancellation_request}';
				$page_title = SITE_NAME . ' - ' . '{_error_with_subscription_cancellation_request}';
				print_notice('{_subscription_cancellation_error}', '{_there_appears_to_be_an_error_related_to_the_cancellation_of_this_request}<br /><br />{_please_contact_customer_support}', 'javascript:history.back(1);', '{_retry}');
				exit();        
			}
		}
		else
		{
			$area_title = '{_subscription_cancellation_denied_bad_password}';
			$page_title = SITE_NAME . ' - ' . '{_subscription_cancellation_denied_bad_password}';
			print_notice('{_wrong_password_detected}', '{_you_have_entered_a_wrong_password_for_the_requested_action_please_retry_your_action_again}' . '<br /><li>{_click_back_on_your_browser_and_retype_your_password}</li>' . '{_please_contact_customer_support}', 'javascript:history.back(1);', '{_retry}');
			exit();
		}
	}
	else
	{
		$area_title = '{_subscription_menu}';
		$page_title = SITE_NAME . ' - ' . '{_subscription_menu}';
		$navcrumb = array();
		$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
		$navcrumb[""] = '{_subscription}';
		$show['leftnav'] = true;
		// show red header message if current subscription is cancelled
		$show['subscriptioncancelled'] = $ilance->subscription_plan->is_subscription_cancelled($_SESSION['ilancedata']['user']['userid']);
		$returnurl = isset($ilance->GPC['returnurl']) ? urlencode($ilance->GPC['returnurl']) : '';
		$returnbit = !empty($returnurl) ? '&amp;returnurl=' . $returnurl : '';
		$paidplan = $hasinvoice = false;
		$row_count = 0;
		
		($apihook = $ilance->api('subscription_rows_start')) ? eval($apihook) : false;  
                
		$sql = $ilance->db->query("
			SELECT subscriptionid, invoiceid
			FROM " . DB_PREFIX . "subscription_user
			WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $ilance->db->query("
				SELECT cost
				FROM " . DB_PREFIX . "subscription
				WHERE subscriptionid = '" . $res['subscriptionid'] . "'
			");
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
				if ($res2['cost'] > 0)
				{
					$paidplan = true;
				}
			}
			$sql3 = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "invoices
				WHERE subscriptionid = '" . $res['subscriptionid'] . "'
					AND invoiceid = '" . $res['invoiceid'] . "'
			");
			if ($ilance->db->num_rows($sql3) > 0)
			{
				$res3 = $ilance->db->fetch_array($sql3, DB_ASSOC);
				if ($res3['user_id'] > 0)
				{
					$hasinvoice = true;
				}
			}
		}
                unset($sql, $sql2, $sql3, $res, $res2, $res3);
		$show['freeplan'] = false;
		$res = $ilance->db->query("
			SELECT UNIX_TIMESTAMP(u.renewdate) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS countdown, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " AS title, s.description_" . $_SESSION['ilancedata']['user']['slng'] . " AS description, s.cost, s.length, s.units, u.recurring, u.user_id, u.paymethod, u.startdate, u.renewdate, u.active, u.subscriptionid, u.autorenewal, u.autopayment" . (($paidplan AND $hasinvoice) ? ", i.invoiceid, i.paiddate, i.status AS invoicestatus" : "") . "
			FROM " . DB_PREFIX . "subscription AS s
			LEFT JOIN " . DB_PREFIX . "subscription_user AS u ON s.subscriptionid = u.subscriptionid
			" . (($paidplan AND $hasinvoice) ? "LEFT JOIN " . DB_PREFIX . "invoices AS i ON u.user_id = i.user_id" : "") . "
			WHERE u.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
			" . (($paidplan AND $hasinvoice) ? "AND i.invoicetype = 'subscription' AND i.user_id = u.user_id ORDER BY i.invoiceid DESC" : "ORDER BY u.renewdate DESC") . "
			LIMIT 1
                ");
		if ($ilance->db->num_rows($res) > 0)
		{
			while ($row = $ilance->db->fetch_array($res, DB_ASSOC))
			{
				$clock_js = '';
				$row['subscriptionid'] = $row['subscriptionid'];
				$row['title'] = handle_input_keywords(stripslashes($row['title']));
				$row['description'] = handle_input_keywords(stripslashes($row['description']));
				$row['startdate'] = print_date($row['startdate']);
				$row['renewdate'] = print_date($row['renewdate']);
				$raw_cost = $row['cost'];
				if ($ilance->tax->is_taxable(intval($_SESSION['ilancedata']['user']['userid']), 'subscription'))
				{
					$row['cost'] = ($row['cost'] + $ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), sprintf("%01.2f", $row['cost']), 'subscription', 0));
				}
				$row['cost'] = ($raw_cost > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['cost']) : '{_free}';
				$show['freeplan'] = ($raw_cost > 0) ? false : true;
				$row['length'] = $row['length'];
				$row['units'] = print_unit($row['units']);
				if ($row['recurring'])
				{
					$row['recurring'] = '{_yes}';
					$show['onlyupgrade'] = 1;
				}
				else
				{
					$row['recurring'] = '{_no}';
					$show['onlyupgrade'] = 0;
				}
				$row['action'] = '-';
				if ($row['active'] == 'yes')
				{
					$show['renewal_countdown'] = true;
					$row['status'] = '{_active_custom}';
				}
				else
				{
					$show['renewal_countdown'] = false;
					$row['startdate'] = '-';
					$row['renewdate'] = '-';
					$row['status'] = '{_inactive}';
					if ($paidplan AND $hasinvoice)
					{
						$row['action'] = '-';
						if ($row['invoicestatus'] != 'cancelled')
						{
							$row['action'] = '<a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $row['invoiceid'] . $returnbit . '">{_pay_invoice} #' . $row['invoiceid'] . '</a>';
						}
					}
					else 
					{
						$row['action'] = '{_upgrade_subscription}';	
					}
				}
				$autopayment = $row['autopayment'];
				$autorenewal = $row['autorenewal'];
				$dif = $row['countdown'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;		
				$sign = '+';
				if ($row['countdown'] < 0)
				{
					$row['countdown'] = - $row['countdown'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$timeleft = '{_subscription_expired}';
				}
				else
				{
					if ($ndays != '0')
					{
						$timeleft = $ndays . '{_d_shortform}' . ', ';	
						$timeleft .= $nhours . '{_h_shortform}' . ', ';
						$timeleft .= $nminutes . '{_m_shortform}' . ', ';
						$timeleft .= $nseconds . '{_s_shortform}' . '+';
					}
					else if ($nhours != '0')
					{
						$timeleft = $nhours . '{_h_shortform}' . ', ';
						$timeleft .= $nminutes . '{_m_shortform}' . ', ';
						$timeleft .= $nseconds . '{_s_shortform}' . '+';
					}
					else
					{
						$timeleft = $nminutes . '{_m_shortform}' . ', ';
						$timeleft .= $nseconds . '{_s_shortform}' . '+';
					}
				}
				$subscription_countdown = $timeleft;
				if ($row['paymethod'] == 'account')
                                {
					$row['paymethod'] = '{_account}';
				}
				else if ($row['paymethod'] == 'bank')
                                {
					$row['paymethod'] = '{_bank_slash_wire}';
				}
				else if ($row['paymethod'] == 'visa')
                                {
					$row['paymethod'] = 'Visa';
				}
				else if ($row['paymethod'] == 'amex')
                                {
					$row['paymethod'] = 'AmEX';
				}
				else if ($row['paymethod'] == 'mc')
                                {
					$row['paymethod'] = 'MasterCard';
				}
				else if ($row['paymethod'] == 'disc')
                                {
					$row['paymethod'] = 'Discover';
				}
				else if ($row['paymethod'] == 'paypal')
                                {
					$row['paymethod'] = 'PayPal';
				}
                                else if ($row['paymethod'] == 'cashu')
                                {
					$row['paymethod'] = 'CashU';
				}
                                else if ($row['paymethod'] == 'moneybookers')
                                {
					$row['paymethod'] = 'MoneyBookers';
				}
                                else if ($row['paymethod'] == 'eway')
                                {
					$row['paymethod'] = 'eWAY';
				}
				else if ($row['paymethod'] == 'check')
                                {
					$row['paymethod'] = '{_check_slash_mo}';
				}
				$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
				$subscription_rows[] = $row;
				$row_count++;
			}
			$show['no_subscription_rows'] = false;
		}
		else
		{
			$show['no_subscription_rows'] = true;
		}
                
                ($apihook = $ilance->api('subscription_rows_end')) ? eval($apihook) : false;
		
                // #### SUBSCRIPTION UPGRADE ROWS ##############################
		$res2 = $ilance->db->query("
			SELECT s.subscriptionid, s.title_" . $_SESSION['ilancedata']['user']['slng'] . " as title, s.description_" . $_SESSION['ilancedata']['user']['slng'] . " as description, s.cost, s.length, s.units, s.subscriptiongroupid, s.roleid, s.icon, s.sort
			FROM " . DB_PREFIX . "subscription AS s
			WHERE s.visible_upgrade = '1' ORDER BY sort
		");
		if ($ilance->db->num_rows($res2) > 0)
		{
			while ($row = $ilance->db->fetch_array($res2, DB_ASSOC))
			{
				$row['subscriptionid'] = $row['subscriptionid'];
				$row['title'] = stripslashes($row['title']);
				$row['description'] = stripslashes($row['description']);
				$raw_cost = $row['cost'];
				$row['cost'] = sprintf("%01.2f", $row['cost']);
				if ($ilance->tax->is_taxable(intval($_SESSION['ilancedata']['user']['userid']), 'subscription'))
				{
					$row['tax'] = $ilance->tax->fetch_amount(intval($_SESSION['ilancedata']['user']['userid']), sprintf("%01.2f", $row['cost']), 'subscription', 0);
				}
				$row['cost'] = ($raw_cost > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $row['cost']) : '{_free}';
				$row['access'] = '<a href="javascript:void(0)" onclick=Attach("' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['subscription'] . '?cmd=access&gid=' . $row['subscriptiongroupid'] . '&id=' . $row['subscriptionid'] . '&s=' . session_id() . '")>{_view_access}</a>';
				$row['units'] = print_unit($row['units']);
				$row['roletype'] = $ilance->subscription_role->print_role($row['roleid']);
				$row['action'] = '<input type="radio" name="subscriptionid" id="subscriptionid" value="' . $row['subscriptionid'] . '" />';
				$row['badge'] = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $row['icon'] . '" border="0" alt="" />';
				if ($row['subscriptionid'] == $_SESSION['ilancedata']['user']['subscriptionid'])
				{
					$row['class'] = 'featured_highlight';
				}
				else
				{
					$row['class'] = ($row_count % 2) ? 'alt1' : 'alt2';
				}
				$upgrade_rows[] = $row;
				$row_count++;
			}
			$show['no_upgrade_rows'] = false;
		}
		else
		{
			$show['no_upgrade_rows'] = true;
		}
		$subscription_role = $ilance->subscription_role->print_role($_SESSION['ilancedata']['user']['roleid']);
		// specific javascript includes
		$headinclude .= '<script type="text/javascript">
<!--
function subscription_upgcheck()
{
        var radio_choice = false;
        if (window.document.ilform.subscriptionid.length != undefined)
        {
                for (counter = 0; counter < window.document.ilform.subscriptionid.length; counter++)
                {
                        if (window.document.ilform.subscriptionid[counter].checked)
                        {
                                radio_choice = true;
                        }
                }
        }
        else
        {
                radio_choice = true;
        }
        if (!radio_choice)
        {
                alert_js(phrase[\'_you_did_not_select_a_subscription_plan_to_upgrade\'])
                grayscale[0].style.filter = "";
                return (false);
        }
        return (true);
}
//-->
</script>';
		$cb1 = (isset($autopayment) AND $autopayment == '1') ? 1 : 0;
		$ar1 = (isset($autorenewal) AND $autorenewal == '1') ? 1 : 0;
		$autopayments_pulldown = construct_pulldown('autopayment', 'autopayment', array('1' => '{_yes}', '0' => '{_no}'), $cb1, 'style="font-family: verdana"');
		$autorenewal_pulldown = construct_pulldown('autorenewal', 'autorenewal', array('1' => '{_yes}', '0' => '{_no}'), $ar1, 'style="font-family: verdana"');
		$returnurl = isset($ilance->GPC['returnurl']) ? $ilance->GPC['returnurl'] : '';
		$pprint_array = array('autorenewal_pulldown','returnurl','autopayments_pulldown','subscription_role','subscription_countdown','cost_daily','clock_js','attach_user_max','attach_usage_total','prevnext','input_style');
		$ilance->template->fetch('main', 'subscription.html');
		$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', 'subscription_rows');
		$ilance->template->parse_loop('main', 'commission_rows');
		$ilance->template->parse_loop('main', 'upgrade_rows');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
else
{
	refresh(HTTPS_SERVER . $ilpage['login'] . '?redirect=' . urlencode($ilpage['subscription'] . print_hidden_fields(true, array(), true)));
	exit();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>