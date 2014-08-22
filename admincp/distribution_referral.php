<?php

if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
    die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}

if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'referral-manage')
{
    if (isset($ilance->GPC['delete']) AND !empty($ilance->GPC['delete']))
    {
	foreach ($ilance->GPC['id'] as $value)
	{
	    $ilance->db->query("DELETE FROM " . DB_PREFIX . "referral_data WHERE id = '" . intval($value) . "' LIMIT 1");
	}

	print_action_success('{_the_selected_referral_entries_have_been_removed_from_the_datastore}', $ilance->GPC['return']);
	exit();
    }
    else
    {
	if (isset($ilance->GPC['payout']) AND !empty($ilance->GPC['payout']))
	{
	    foreach ($ilance->GPC['payout'] as $key => $value)
	    {
		$sql = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "referral_data WHERE id = '" . intval($key) . "'", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
		    $res = $ilance->db->fetch_array($sql);
		    $sql2 = $ilance->db->query("SELECT available_balance, total_balance FROM " . DB_PREFIX . "users WHERE user_id = '" . $res['referred_by'] . "'", 0, null, __FILE__, __LINE__);
		    if ($ilance->db->num_rows($sql2) > 0)
		    {
			$res2 = $ilance->db->fetch_array($sql2);
			$new_credit_amount = trim($ilance->GPC['amount']);

			$total_now = $res2['total_balance'];
			$avail_now = $res2['available_balance'];

			$new_total_now = ($total_now + $new_credit_amount);
			$new_avail_now = ($avail_now + $new_credit_amount);

			$ilance->db->query("UPDATE " . DB_PREFIX . "users SET total_balance = '" . $new_total_now . "', available_balance = '" . $new_avail_now . "' WHERE user_id = '" . $res['referred_by'] . "'", 0, null, __FILE__, __LINE__);

			// adjust members total amount received for referral payments from admin
			$ilance->accounting_payment->insert_income_reported($res['referred_by'], sprintf("%01.2f", $new_credit_amount), 'credit');
			$ilance->accounting->insert_transaction(
				0, 0, 0, $res['referred_by'], 0, 0, 0, '{_referral_account_bonus}', sprintf("%01.2f", $new_credit_amount), sprintf("%01.2f", $new_credit_amount), 'paid', 'credit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 0);

			$ilance->db->query("
				UPDATE " . DB_PREFIX . "referral_data
				SET paidout = '1'
				WHERE id = '" . intval($key) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);

			$sqlemail = $ilance->db->query("
				SELECT email, username, first_name, last_name
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res['referred_by'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sqlemail) > 0)
			{
			    $resemail = $ilance->db->fetch_array($sqlemail, DB_ASSOC);

			    
			    $ilance->email->mail = $resemail['email'];
			    $ilance->email->slng = fetch_user_slng($res['referred_by']);
			    $ilance->email->get('referral_account_credit');
			    $ilance->email->set(array (
				'{{customer}}' => stripslashes($resemail['username']),
				'{{amount}}' => $ilance->currency->format($ilance->GPC['amount']),
				'{{datetime}}' => DATETODAY . " " . TIMENOW,
				'{{from}}' => $_SESSION['ilancedata']['user']['username']
			    ));
			    $ilance->email->send();
			}
		    }
		}

		print_action_success('{_the_selected_referring_user_was_credited_funds_to_their_online_account}', $ilance->GPC['return']);
		exit();
	    }
	}
    }
}

// #### REFERRAL MANAGEMENT ####################################
else
{
    $area_title = '{_referral_management}';
    $page_title = SITE_NAME . ' - {_referral_management}';

    ($apihook = $ilance->api('admincp_referral_management')) ? eval($apihook) : false;

    $subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['distribution'], $ilpage['distribution'] . '?cmd=referrals', $_SESSION['ilancedata']['user']['slng']);

    $ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

    $limit = ' ORDER BY r.id DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
    $fields = "r.id, r.user_id, r.referred_by, r.date, r.postauction, r.awardauction, r.paysubscription, r.payfvf, r.payins, r.payportfolio, r.paycredentials, r.payenhancements, r.invoiceid, r.paidout, u.email, u.username, u.lastseen";

    ($apihook = $ilance->api('admincp_referral_before_query')) ? eval($apihook) : false;

    $sql = $ilance->db->query("
	    SELECT $fields
	    FROM " . DB_PREFIX . "referral_data r
	    LEFT JOIN " . DB_PREFIX . "users u ON (r.user_id = u.user_id)
	    $limit
    ", 0, null, __FILE__, __LINE__);

    $sqltmp = $ilance->db->query("
	    SELECT r.id
	    FROM " . DB_PREFIX . "referral_data r
	    LEFT JOIN " . DB_PREFIX . "users u ON (r.user_id = u.user_id)
    ", 0, null, __FILE__, __LINE__);

    $totalcount = $ilance->db->num_rows($sqltmp);
    $counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
    if ($ilance->db->num_rows($sql) > 0)
    {
	$row_count = 0;
	while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
	    $res['username'] = $res['username'];
	    if ($res['username'] == '{_unknown}')
	    {
		$res['username'] = "[{_removed}]";
		$res['email'] = "[{_removed}]";
	    }
	    else
	    {
		$res['username'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['user_id'] . '">' . $res['username'] . '</a>';
		$res['email'] = '<a href="mailto:' . $res['email'] . '">' . $res['email'] . '</a>';
	    }

	    $res['referredby'] = fetch_user('username', $res['referred_by']);
	    if ($res['referredby'] == '{_unknown}')
	    {
		$res['referredby'] = "[{_removed}]";
	    }
	    else
	    {
		$res['referredby'] = '<a href="' . $ilpage['subscribers'] . '?subcmd=_update-customer&amp;id=' . $res['referred_by'] . '">' . $res['referredby'] . '</a>';
	    }

	    $res['ridcode'] = fetch_user('rid', $res['referred_by']);
	    $res['date'] = print_date($res['date'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
	    $res['lastvisit'] = print_date($res['lastseen'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);

	    // awarded any auction?
	    if ($res['awardauction'] == 0)
	    {
		$res['awardedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['awardedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // posted auction?
	    if ($res['postauction'] == 0)
	    {
		$res['postedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['postedauction'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any subscription fee?
	    if ($res['paysubscription'] == 0)
	    {
		$res['invoicepaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['invoicepaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any final value fee?
	    if ($res['payfvf'] == 0)
	    {
		$res['fvfpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['fvfpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any insertion fee?
	    if ($res['payins'] == 0)
	    {
		$res['inspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['inspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any portfolio upsell fee?
	    if ($res['payportfolio'] == 0)
	    {
		$res['portfoliopaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['portfoliopaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any credential verification fee?
	    if ($res['paycredentials'] == 0)
	    {
		$res['credentialpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['credentialpaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // paid any auction upsell enhancement fee?
	    if ($res['payenhancements'] == 0)
	    {
		$res['enhancementspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }
	    else
	    {
		$res['enhancementspaid'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }

	    // has already been paid out by admin?
	    if ($res['paidout'])
	    {
		$res['payout'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" alt="' . '{_yes}' . '" border="0" />';
	    }
	    else
	    {
		$res['payout'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" alt="' . '{_no}' . '" border="0" />';
	    }

	    ($apihook = $ilance->api('distribution_referral_while_loop_end')) ? eval($apihook) : false;

	    $res['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
	    $referrals[] = $res;
	    $row_count++;
	}

	$show['no_referrals'] = false;
    }
    else
    {
	$show['no_referrals'] = true;
    }

    $prevnext = print_pagnation($totalcount, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['distribution'] . '?cmd=referrals');
}

$payout_amount = $ilconfig['referalsystem_payout'];
$currency = print_left_currency_symbol();
$configuration_referalsystem = $ilance->admincp->construct_admin_input('referalsystem', $ilpage['distribution'] . '?cmd=referrals');
$pprint_array = array ('payout_amount', 'configuration_referalsystem', 'prevnext', 'currency', 'id');

($apihook = $ilance->api('admincp_referrals_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'referrals.html', 1);
$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'referrals');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
?>
