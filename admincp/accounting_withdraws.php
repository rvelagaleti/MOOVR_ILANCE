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
// #### MARK CHECK REQUEST AS PAID IN FULL #########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-check-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
	$uname = fetch_user('username', $uid);
	$uemail = fetch_user('email', $uid);
	// 100.00 (withdraw request )
	$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
	$fee = 0;
	if ($ilconfig['check_withdraw_fee_active'] AND $ilconfig['check_withdraw_fee'])
	{
		$fee = $ilconfig['check_withdraw_fee'];
	}
	// set withdraw request as paid / sent to customer
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'paid',
		paid = amount,
		custommessage = '" . $ilance->db->escape_string('{_check_sent_to_customer_address_on}') . " " . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	// readjust user's online account balance
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET total_balance = total_balance - $amount
		WHERE user_id = '" . intval($uid) . "'
	");
	$ilance->email->mail = $uemail;
	$ilance->email->slng = fetch_user_slng($uid);
	$ilance->email->get('check_withdraw_request_paid_in_full');
	$ilance->email->set(array (
	    '{{username}}' => stripslashes($uname),
	    '{{amount}}' => $ilance->currency->format($amount),
	    '{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('check_withdraw_request_paid_in_full_admin');
	$ilance->email->set(array (
	    '{{username}}' => stripslashes($uname),
	    '{{amount}}' => $ilance->currency->format($amount),
	    '{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	print_action_success('{_the_selected_withdrawal_request_was_marked_as_being_sent_paid}', $ilpage['accounting'] . '?cmd=withdraws');
	exit();
}

// #### MARK WIRE TRANSFER AS PAID IN FULL #########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-wire-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
	$uname = fetch_user('username', $uid);
	$uemail = fetch_user('email', $uid);
	// 100.00 (withdraw request)
	$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
	$fee = 0;
	if ($ilconfig['bank_withdraw_fee_active'] AND $ilconfig['bank_withdraw_fee'] > 0)
	{
		$fee = $ilconfig['bank_withdraw_fee'];
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'paid',
		paid = amount,
		custommessage = '" . $ilance->db->escape_string('{_wire_transfer_processed_on}') . " " . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET total_balance = total_balance - $amount
		WHERE user_id = '" . intval($uid) . "'
	");
	$ilance->email->mail = $uemail;
	$ilance->email->slng = fetch_user_slng($uid);
	$ilance->email->get('wire_transfer_paid_in_full');
	$ilance->email->set(array (
	    '{{username}}' => stripslashes($uname),
	    '{{amount}}' => $ilance->currency->format($amount),
	    '{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('wire_transfer_paid_in_full_admin');
	$ilance->email->set(array (
	    '{{username}}' => stripslashes($uname),
	    '{{amount}}' => $ilance->currency->format($amount),
	    '{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	print_action_success('{_the_selected_withdrawal_request_was_marked_as_being_sent_paid}', $ilpage['accounting'] . '?cmd=withdraws');
	exit();
}
// #### MARK PAYPAL WITHDRAW REQUEST AS PAID IN FULL ###########
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-paypal-paid' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
	$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
	$uname = fetch_user('username', $uid);
	$uemail = fetch_user('email', $uid);
	$fee = ($ilconfig['paypal_withdraw_fee_active'] AND $ilconfig['paypal_withdraw_fee'] > 0) ? $ilconfig['paypal_withdraw_fee'] : 0;
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'paid',
		paid = amount,
		custommessage = '" . $ilance->db->escape_string('{_paypal_payment_sent_on}') . " " . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET total_balance = total_balance - $amount
		WHERE user_id = '" . intval($uid) . "'
	");
	$ilance->email->mail = $uemail;
	$ilance->email->slng = fetch_user_slng($uid);
	$ilance->email->get('withdraw_request_mark_paypal_paid');
	$ilance->email->set(array (
		'{{username}}' => stripslashes($uname),
		'{{amount}}' => $ilance->currency->format($amount),
		'{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('withdraw_request_mark_paypal_paid_admin');
	$ilance->email->set(array (
		'{{username}}' => stripslashes($uname),
		'{{amount}}' => $ilance->currency->format($amount),
		'{{fee}}' => $ilance->currency->format($fee),
	));
	$ilance->email->send();
	$status = '';
	if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
	{
		$status = '&amp;status=' . $ilance->GPC['status'];
	}
	print_action_success('{_the_selected_withdrawal_request_was_marked_as_being_sent_paid}', $ilpage['accounting'] . '?cmd=withdraws' . $status);
	exit();
}
// #### MARK PAYPAL REQUEST CANCELLED ##########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_mark-withdraw-cancelled' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$gateway = '';
	$fee = $feeinvoiceid = 0;
	$uid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "user_id");
	$gateway = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "paymethod");
	$gateway = ucwords($gateway);
	$fee = $ilance->db->fetch_field(DB_PREFIX . "invoices", "parentid = '" . intval($ilance->GPC['id']) . "' AND user_id = '" . intval($uid) . "' AND status = 'paid' AND invoicetype = 'debit'", "paid");
	$amount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . intval($ilance->GPC['id']) . "'", "amount");
	if (isset($fee) AND $fee > 0)
	{
		$total = ($amount + $fee);
		$feeinvoiceid = $ilance->db->fetch_field(DB_PREFIX . "invoices", "parentid = '" . intval($ilance->GPC['id']) . "' AND user_id = '" . intval($uid) . "' AND status = 'paid' AND invoicetype = 'debit'", "invoiceid");
	}
	else
	{
		$total = $amount;
		$fee = 0;
	}
	$uname = fetch_user('username', $uid);
	$uemail = fetch_user('email', $uid);
	// cancel the withdrawal request
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "invoices
		SET status = 'cancelled',
		custommessage = '" . $ilance->db->escape_string('{_paypal_withdraw_request_cancelled_on}') . " " . DATETIME24H . "'
		WHERE invoiceid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	if ($feeinvoiceid > 0)
	{
		// cancel the withdraw fee
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "invoices
			SET status = 'cancelled',
			custommessage = '" . $ilance->db->escape_string('{_paypal_withdraw_request_cancelled_on}') . " " . DATETIME24H . "'
			WHERE invoiceid = '" . intval($feeinvoiceid) . "'
			LIMIT 1
		");
	}
	// re-adjust the users account balance
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "users
		SET available_balance = available_balance + $total,
		total_balance = total_balance + $fee
		WHERE user_id = '" . intval($uid) . "'
	");
	$ilance->email->mail = $uemail;
	$ilance->email->slng = fetch_user_slng($uid);
	$ilance->email->get('withdraw_request_cancelled');
	$ilance->email->set(array (
		'{{username}}' => stripslashes($uname),
		'{{amount}}' => $ilance->currency->format($amount),
		'{{fee}}' => $ilance->currency->format($fee),
		'{{feeinvoiceid}}' => $feeinvoiceid,
		'{{total}}' => $ilance->currency->format($total),
		'{{gateway}}' => $gateway,
	));
	$ilance->email->send();
	$ilance->email->mail = SITE_EMAIL;
	$ilance->email->slng = fetch_site_slng();
	$ilance->email->get('withdraw_request_cancelled_admin');
	$ilance->email->set(array (
		'{{username}}' => stripslashes($uname),
		'{{amount}}' => $ilance->currency->format($amount),
		'{{fee}}' => $ilance->currency->format($fee),
		'{{feeinvoiceid}}' => $feeinvoiceid,
		'{{total}}' => $ilance->currency->format($total),
		'{{gateway}}' => $gateway,
		'{{staff}}' => $_SESSION['ilancedata']['user']['username'],
	));
	$ilance->email->send();
	print_action_success('{_the_selected_withdrawal_request_was_cancelled}', $ilpage['accounting'] . '?cmd=withdraws');
	exit();
}
$area_title = '{_withdrawal_management}';
$page_title = SITE_NAME . ' - {_withdrawal_management}';

($apihook = $ilance->api('admincp_withdraw_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['accounting'], $ilpage['accounting'] . '?cmd=withdraws', $_SESSION['ilancedata']['user']['slng']);
$status = '';
if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
{
	$status = '&amp;status=' . $ilance->GPC['status'];
}
// filter via invoice status
$sqlinvoicestatus = '';
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws' AND isset($ilance->GPC['status']) AND $ilance->GPC['status'] != '')
{
	if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == 'unpaid')
	{
		$sqlinvoicestatus = "AND ( i.status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "' OR i.status = 'scheduled' )";
		$searchstatus = 'pending';
	}
	else
	{
		$sqlinvoicestatus = "AND i.status = '" . $ilance->db->escape_string($ilance->GPC['status']) . "'";
		$searchstatus = $ilance->GPC['status'];
	}
}
// filter via invoiceid
$sqlinvoiceid = $invid = '';
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == 'withdraws' AND isset($ilance->GPC['invoiceid']) AND $ilance->GPC['invoiceid'] > 0)
{
	$invid = intval($ilance->GPC['invoiceid']);
	$sqlinvoiceid = "AND i.invoiceid = '" . $invid . "'";
}
// filter via transaction id
$sqlinvoicetxnid = '';
if (isset($ilance->GPC['cmd']) AND $ilance->GPC['cmd'] == "withdraws" AND isset($ilance->GPC['transactionid']) AND $ilance->GPC['transactionid'] != "")
{
	$sqlinvoicetxnid = "AND i.transactionid = '" . $ilance->db->escape_string($ilance->GPC['transactionid']) . "'";
}
// #### CHECK REQUESTS #########################################
if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
{
	$ilance->GPC['page'] = 1;
}
else
{
	$ilance->GPC['page'] = intval($ilance->GPC['page']);
}
$orderlimit = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$requesttotal1 = $feetotal1 = 0;
$sqlchecks = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid, i.currency_id
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'check'
		$sqlinvoicestatus
		$sqlinvoiceid
		$sqlinvoicetxnid
	$orderlimit
");

$sqlchecks2 = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid, i.currency_id
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'check'
		$sqlinvoicestatus
		$sqlinvoicetxnid
	$sqlinvoiceid
");
if ($ilance->db->num_rows($sqlchecks) > 0)
{
	$row_count = 0;
	while ($res = $ilance->db->fetch_array($sqlchecks, DB_ASSOC))
	{
		$res['message'] = ucfirst($res['status']);
		$res['action'] = ($res['status'] == 'cancelled') ? '-' : '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="{_click_to_cancel_this_withdrawal_request}" border="0" /></a>';
		if ($res['withdrawinvoiceid'] > 0)
		{
			$feetotal1 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
			$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
		}
		else
		{
			$feetotal1 += 0;
			$res['fee'] = $ilance->currency->format(0);
		}
		$res['paid'] = $ilance->currency->format($res['paid']);
		$res['createdate'] = print_date($res['createdate'], 'M. d, Y', 0, 0);
		$res['invoicetype'] = ucfirst($res['invoicetype']);
		$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
		$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
		$res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$requesttotal1 += ($res['amount']);
		$res['request'] = $ilance->currency->format($res['amount']);
		if ($res['status'] == 'cancelled')
		{
			$res['payout'] = '-';
			$res['fee'] = '-';
			$feetotal1 -= $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
		}
		else
		{
			$res['payout'] = $res['request'];
		}
		$res['status'] = ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled') ? '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-check-paid&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_update_check_payment_request_as_being_paid_sent}" border="0" /></a>' : '-';
		$res['amount'] = $ilance->currency->format($res['amount']);
		$check[] = $res;
		$row_count++;
	}
	$numbercheck = $ilance->db->num_rows($sqlchecks2);
	$checkprevnext = print_pagnation($numbercheck, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=withdraws&amp;viewtype=check');
}
else
{
	$show['no_check'] = true;
	$numbercheck = 0;
}
$requesttotal1 = $ilance->currency->format($requesttotal1);
$feetotal1 = $ilance->currency->format($feetotal1);
// #### WIRE REQUESTS ##################################################
if (!isset($ilance->GPC['page2']) OR isset($ilance->GPC['page2']) AND $ilance->GPC['page2'] <= 0)
{
	$ilance->GPC['page2'] = 1;
}
else
{
	$ilance->GPC['page2'] = intval($ilance->GPC['page2']);
}
$orderlimit2 = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$requesttotal2 = $feetotal2 = 0;
$sqlwire = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.withdrawinvoiceid
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'bank'
		$sqlinvoicestatus
		$sqlinvoiceid
		$sqlinvoicetxnid
	$orderlimit2
");
$sqlwire2 = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.withdrawinvoiceid
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'bank'
		$sqlinvoicestatus
		$sqlinvoiceid
		$sqlinvoicetxnid
");
if ($ilance->db->num_rows($sqlwire) > 0)
{
	$row_count2 = 0;
	while ($res = $ilance->db->fetch_array($sqlwire))
	{
		$res['message'] = ucfirst($res['status']);
		if ($res['withdrawinvoiceid'] > 0)
		{
			$feetotal2 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
			$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
		}
		else
		{
			$feetotal2 += 0;
			$res['fee'] = $ilance->currency->format(0);
		}
		$res['action'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="{_click_to_cancel_this_withdrawal_request}" border="0" /></a>';
		$res['createdate'] = print_date($res['createdate'], 'M. d, Y', 0, 0);
		$res['invoicetype'] = ucfirst($res['invoicetype']);
		$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
		$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
		$res['class'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
		$requesttotal2 += ($res['amount']);
		$res['request'] = $ilance->currency->format($res['amount']);
		if ($res['status'] == 'cancelled')
		{
			$res['payout'] = '-';
			$res['fee'] = '-';
			$res['action'] = '-';
			$feetotal2 -= $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
		}
		else
		{
			$res['payout'] = $res['request'];
		}
		$res['status'] = ($res['status'] == "unpaid" OR $res['status'] == 'scheduled') ? '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-wire-paid&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_update_wire_payment_request_as_being_processed_from_your_bank}" border="0" /></a>' : '-';
		$res['amount'] = $ilance->currency->format($res['amount']);
		$wire[] = $res;
		$row_count2++;
	}
	$numberwire = $ilance->db->num_rows($sqlwire2);
	$wireprevnext = print_pagnation($numberwire, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page2'], ($ilance->GPC['page2'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . "?cmd=withdraws&amp;viewtype=wire", 'page2');
}
else
{
	$show['no_wire'] = true;
	$numberwire = 0;
}
$requesttotal2 = $ilance->currency->format($requesttotal2);
$feetotal2 = $ilance->currency->format($feetotal2);
// #### PAYPAL REQUESTS ########################################
if (!isset($ilance->GPC['page3']) OR isset($ilance->GPC['page3']) AND $ilance->GPC['page3'] <= 0)
{
	$ilance->GPC['page3'] = 1;
}
else
{
	$ilance->GPC['page3'] = intval($ilance->GPC['page3']);
}
$orderlimit3 = ' ORDER BY i.invoiceid DESC LIMIT ' . (($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$requesttotal3 = $feetotal3 = 0;
$status = '';
if (isset($ilance->GPC['status']) AND !empty($ilance->GPC['status']))
{
	$status = '&amp;status=' . $ilance->GPC['status'];
}
$sqlpp = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'paypal'
		$sqlinvoicestatus
		$sqlinvoiceid
		$sqlinvoicetxnid
	$orderlimit3
");

$sqlpp2 = $ilance->db->query("
	SELECT c.user_id, c.username, i.invoiceid, i.description, i.amount, i.paid, i.status, i.invoicetype, i.createdate, i.duedate, i.paiddate, i.custommessage, i.withdrawinvoiceid
	FROM " . DB_PREFIX . "users AS c
	LEFT JOIN " . DB_PREFIX . "invoices AS i ON c.user_id = i.user_id
	WHERE i.invoicetype = 'debit'
		AND i.paymethod = 'paypal'
		$sqlinvoicestatus
		$sqlinvoiceid
		$sqlinvoicetxnid
");
if ($ilance->db->num_rows($sqlpp) > 0)
{
	$row_count3 = 0;
	$address = ($ilconfig['paypal_sandbox'] == '1') ? 'www.sandbox.paypal.com' : 'www.paypal.com';
	while ($res = $ilance->db->fetch_array($sqlpp))
	{
		$res['customername'] = fetch_user('fullname', $res['user_id']);
		$res['button'] = '<form action="https://' . $address . '/cgi-bin/webscr" method="post" accept-charset="UTF-8" style="margin: 0px;" target="_blank">';
		$res['button'] .= '<input type="hidden" name="cmd" value="_xclick" />';
		$res['button'] .= '<input type="hidden" name="business" value="' . $res['custommessage'] . '" />';
		$res['button'] .= '<input type="hidden" name="return" value="' . HTTPS_SERVER_ADMIN . $ilpage['accounting'] . '?cmd=withdraws" />';
		$res['button'] .= '<input type="hidden" name="undefined_quantity" value="0" />';
		$res['button'] .= '<input type="hidden" name="item_name" value="{_withdraw_funds} - ' . $res['customername'] . '" />';
		$res['button'] .= '<input type="hidden" name="amount" value="' . $res['amount'] . '" />';
		$res['button'] .= '<input type="hidden" name="currency_code" value="' . $ilance->currency->currencies[$ilconfig['globalserverlocale_defaultcurrency']]['code'] . '" />';
		$res['button'] .= '<input type="hidden" name="no_shipping" value="1" />';
		$res['button'] .= '<input type="hidden" name="cancel_return" value="' . HTTPS_SERVER_ADMIN . $ilpage['accounting'] . '?cmd=withdraws" />';
		$res['button'] .= '<input type="hidden" name="no_note" value="1" />';
		$res['button'] .= '<input type="submit" name="submit" value=" {_pay} " class="buttons_smaller" />';
		$res['button'] .= '</form>';
		$res['message'] = ucfirst($res['status']);
		$res['createdate'] = print_date($res['createdate'], 'M. d, Y', 0, 0);
		$res['invoicetype'] = ucfirst($res['invoicetype']);
		$res['remove'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_remove-invoice&amp;id=' . $res['invoiceid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="" border="0" /></a>';
		$res['customer'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . fetch_user('username', $res['user_id']) . '</a>';
		$res['customeremail'] = $res['custommessage'];
		$res['class'] = ($row_count3 % 2) ? 'alt2' : 'alt1';
		$requesttotal3 += ($res['amount']);
		$res['request'] = $ilance->currency->format($res['amount']);
		$res['payout'] = $res['request'];
		
		if ($res['withdrawinvoiceid'] > 0)
		{
			$feetotal3 += $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
			$res['fee'] = $ilance->currency->format($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount"));
		}
		else
		{
			$feetotal3 += 0;
			$res['fee'] = '-';
		}
		if ($ilconfig['paypal_withdraw_fee_active'])
		{
			if ($res['status'] == 'cancelled')
			{
				$res['action'] = '-';
				$res['button'] = '-';
				$res['payout'] = '-';
				$res['fee'] = '-';
				$feetotal3 -= $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
			}
			else
			{
				$res['action'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="{_click_to_cancel_this_withdrawal_request}" border="0" /></a>';
			}
			if ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled')
			{
				//$res['status'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . ($res['amount'] + $ilconfig['paypal_withdraw_fee']) . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_update_paypal_withdrawal_request_as_being_paid_in_full}" border="0" /></a>';
				$res['status'] = '<span class="blue"><a title="{_click_to_update_paypal_withdrawal_request_as_being_paid_in_full}" href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . ($res['amount'] + $ilconfig['paypal_withdraw_fee']) . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">' . $res['message'] . '</a></span>';
			}
			else
			{
				$res['status'] = $res['message'];
				$res['button'] = '-';
			}
		}
		else
		{
			if ($res['status'] == 'cancelled')
			{
				$res['action'] = '-';
				$res['button'] = '-';
				$res['payout'] = '-';
				$res['fee'] = '-';
				$feetotal3 -= $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res['withdrawinvoiceid'] . "'", "amount");
			}
			else
			{
				$res['action'] = '<span class="blue"><a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-withdraw-cancelled&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="{_click_to_cancel_this_withdrawal_request}" border="0" /></a></span>';
			}
			if ($res['status'] == 'unpaid' OR $res['status'] == 'scheduled')
			{
				//$res['status'] = '<a href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . $res['amount'] . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="{_click_to_update_paypal_withdrawal_request_as_being_paid_in_full}" border="0" /></a>';
				$res['status'] = '<span class="blue"><a title="{_click_to_update_paypal_withdrawal_request_as_being_paid_in_full}" href="' . $ilpage['accounting'] . '?cmd=withdraws&amp;subcmd=_mark-paypal-paid&amp;amount=' . ($res['amount'] + $ilconfig['paypal_withdraw_fee']) . '&amp;id=' . $res['invoiceid'] . $status . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">' . $res['message'] . '</a></span>';
			}
			else
			{
				$res['status'] = '-';
				$res['button'] = '-';
			}
		}
		$res['amount'] = $ilance->currency->format($res['amount']);
		$paypal[] = $res;
		$row_count3++;
	}
	$numberpaypal = $ilance->db->num_rows($sqlpp2);
	$paypalprevnext = print_pagnation($numberpaypal, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page3'], ($ilance->GPC['page3'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'], $ilpage['accounting'] . '?cmd=withdraws&amp;viewtype=paypal', 'page3');
}
else
{
	$show['no_paypal'] = true;
	$numberpaypal = 0;
}
$invoice_status_pulldown = '<select name="status" style="font-family: verdana">';
$invoice_status_pulldown .= '<optgroup label="{_select_status}">';
$invoice_status_pulldown .= '<option value="">{_all}</option>';
$invoice_status_pulldown .= '<option value="paid"';
if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "paid")
{
	$invoice_status_pulldown .= ' selected="selected"';
}
$invoice_status_pulldown .= '>{_paid_requests}</option>';
$invoice_status_pulldown .= '<option value="unpaid"';
if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "unpaid")
{
	$invoice_status_pulldown .= ' selected="selected"';
}
$invoice_status_pulldown .= '>{_pending_requests}</option>';
$invoice_status_pulldown .= '<option value="cancelled"';
if (isset($ilance->GPC['status']) AND $ilance->GPC['status'] == "cancelled")
{
	$invoice_status_pulldown .= ' selected="selected"';
}
$invoice_status_pulldown .= '>{_cancelled_requests}</option>';
$invoice_status_pulldown .= '</optgroup>';
$invoice_status_pulldown .= '</select>';
$requesttotal3 = $ilance->currency->format($requesttotal3);
$feetotal3 = $ilance->currency->format($feetotal3);
$pprint_array = array ('requesttotal1', 'requesttotal2', 'requesttotal3', 'feetotal1', 'feetotal2', 'feetotal3', 'searchstatus', 'invoice_status_pulldown', 'numberpaypal', 'paypalprevnext', 'wireprevnext', 'numberwire', 'numbercheck', 'checkprevnext', 'id');

($apihook = $ilance->api('admincp_accounting_withdraws_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'withdraws.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array ('v3nav', 'subnav_settings', 'check', 'wire', 'paypal'));
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>
