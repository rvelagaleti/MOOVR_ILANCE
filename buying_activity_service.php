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

$keyw = isset($ilance->GPC['keyw']) ? $ilance->common->xss_clean(handle_input_keywords($ilance->GPC['keyw'])) : '';
$ilance->GPC['page'] = (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0) ? 1 : intval($ilance->GPC['page']);

// #### default listing period #################################
$ilance->GPC['period'] = (isset($ilance->GPC['period']) ? intval($ilance->GPC['period']) : -1);
$extra = '&amp;period=' . $ilance->GPC['period'];
$periodsql = fetch_startend_sql($ilance->GPC['period'], 'DATE_SUB', 'p.date_added', '>=');

($apihook = $ilance->api('buying_activity_service_condition_start')) ? eval($apihook) : false;

// #### ordering by fields defaults ############################
$orderbyfields = array('project_title', 'date_added', 'date_end', 'bids', 'insertionfee');
if (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'ended')
{
	$orderby = '&amp;orderby=date_end';
	$orderbysql = 'date_end';
}
else
{
	$orderby = '&amp;orderby=bids';
	$orderbysql = 'bids';
}
if (isset($ilance->GPC['orderby']) AND in_array($ilance->GPC['orderby'], $orderbyfields))
{
	$orderby = '&amp;orderby=' . $ilance->GPC['orderby'];
	$orderbysql = $ilance->GPC['orderby'];
}
$ilance->GPC['orderby'] = $orderbysql;

// #### display order defaults #################################
$displayorderfields = array('asc', 'desc');
$displayorder = '&amp;displayorder=asc';
$currentdisplayorder = $displayorder;
$displayordersql = 'DESC';
$service_header_block_title = '{_jobs_ive_posted}';
$ilance->GPC['displayorder'] = (isset($ilance->GPC['displayorder']) ? $ilance->GPC['displayorder'] : 'desc');
$ilance->GPC['pp'] = (isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay']);
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
$extra .= (!empty($ilance->GPC['bidsub'])) ? '&amp;bidsub=' . $ilance->GPC['bidsub'] : '';
$extra .= (!empty($ilance->GPC['sub'])) ? '&amp;sub=' . $ilance->GPC['sub'] : '';

$period_options = array(
	'-1' => '{_any_date}',
	'1' => '{_last_hour}',
	'6' => '{_last_12_hours}',
	'7' => '{_last_24_hours}',
	'13' => '{_last_7_days}',
	'14' => '{_last_14_days}',
	'15' => '{_last_30_days}',
	'16' => '{_last_60_days}',
	'17' => '{_last_90_days}');
$period_pulldown = construct_pulldown('period_pull_id', 'period', $period_options, $ilance->GPC['period'], 'class="smaller" style="font-family: verdana"');
$orderby_options = array(
	'date_end' => '{_date_ending}',
	'project_title' => '{_title}',
	'insertionfee' => '{_insertion_fee}',
	'bids' => '{_bids}');
$orderby_pulldown = construct_pulldown('orderby_pull_id', 'orderby', $orderby_options, $ilance->GPC['orderby'], 'class="smaller" style="font-family: verdana"');
$displayorder_pulldown = construct_pulldown('displayorder_pull_id', 'displayorder', array('desc' => '{_descending}', 'asc' => '{_ascending}'), $ilance->GPC['displayorder'], 'class="smaller" style="font-family: verdana"');
$pp_pulldown = construct_pulldown('pp_pull_id', 'pp', array('10' => '10', '50' => '50', '100' => '100', '500' => '500', '1000' => '1000'), $ilance->GPC['pp'], 'class="smaller" style="font-family: verdana"');
// #### header tabs ############################################
if (!empty($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'ended')
{
	$service_header_block_title = '{_ended}';
	$servicetabs = print_buying_activity_tabs('ended', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$projectstatussql =  "AND p.status = 'expired'";
}
else if (!empty($ilance->GPC['sub']) AND $ilance->GPC['sub'] == 'awarded')
{
	// #### define top header nav ##################################
	$topnavlink = array(
		'mycp',
		'buying_awarded'
	);
	$service_header_block_title = '{_awarded}';
	$servicetabs = print_buying_activity_tabs('awarded', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$projectstatussql = "AND (p.status = 'wait_approval' OR p.status = 'approval_accepted' OR p.status = 'finished') AND p.bids > 0";
}
else
{
	$servicetabs = print_buying_activity_tabs('active', 'service', $_SESSION['ilancedata']['user']['userid'], $periodsql);
	$projectstatussql = "AND p.status = 'open'";
}

$pp = (isset($ilance->GPC['pp']) AND $ilance->GPC['pp'] >= 0)  ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
$limit = ' ORDER BY ' . $orderbysql . ' ' . $displayordersql . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $pp) . ',' . $pp;
$php_self = $ilpage['buying'] . '?cmd=management' . $displayorder . $extra;
$keywx = '&keyw=' . $keyw;
$scriptpage = $ilpage['buying'] . '?cmd=management' . $currentdisplayorder . $orderby . $extra . $keywx;
$numberrows = $ilance->db->query("
	SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
	FROM " . DB_PREFIX . "projects AS p
	WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		$periodsql
		AND p.project_state = 'service'
		$projectstatussql
		" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1')) AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1'))" : "AND p.visible = '1'") . " 
		AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
", 0, null, __FILE__, __LINE__);
$number = $ilance->db->num_rows($numberrows);
$area_title = '{_buying_activity}';
$page_title = SITE_NAME . ' - {_buying_activity}';
$navcrumb = array();
$navcrumb["$ilpage[main]?cmd=cp"] = '{_my_cp}';
$navcrumb[""] = '{_buying_activity}';
$counter = ($ilance->GPC['page'] - 1) * $pp;
$row_count = 0;
$result = $ilance->db->query("
	SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.*, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
	FROM " . DB_PREFIX . "projects AS p
	WHERE p.user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		$periodsql
		AND p.project_state = 'service'
		$projectstatussql
		" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND p.visible = '1' AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1')) AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1'))" : "AND p.visible = '1'") . " 
		AND p.project_title LIKE '%" . $ilance->db->escape_string($keyw) . "%'
	$limit 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($result) > 0)
{
	while ($row = $ilance->db->fetch_array($result, DB_ASSOC))
	{
		$bidgrouping = $ilance->categories->bidgrouping($row['cid']);
		$bids_table = ($bidgrouping) ? "project_bids" : "project_realtimebids";
		$row['paymethod'] = '-';
		$sqlattachments2 = $ilance->db->query("
			SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
			FROM " . DB_PREFIX . "attachment
			WHERE attachtype = 'project'
				AND project_id = '" . $row['project_id'] . "'
				AND user_id = '" . $row['user_id'] . "'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sqlattachments2) > 0)
		{
			while ($res = $ilance->db->fetch_array($sqlattachments2, DB_ASSOC))
			{
				if (isset($row['attach']))
				{
					$row['attach'] .= '<span class="smaller blue"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
				else
				{
					$row['attach'] = '<span class="smaller blue"><a href="' . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . $res['filename'] . '</a></span> ';
				}
			}
		}
		else
		{
			$row['attach'] = '<span class="smaller" align="center">-</span>';
		}

		// auction status
		if ($row['status'] == 'draft')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['statusmsg'] = '{_draft_not_public}';
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}
		else if ($row['status'] == 'expired')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['statusmsg'] = '<span class="gray">{_ended}</span>';
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}
		else if ($row['status'] == 'open')
		{
			if ($row['date_starts'] < DATETIME24H)
			{
				$row['statusmsg'] = '{_open}';
			}
			else
			{
				$dif = $row['starttime'];
				$ndays = floor($dif / 86400);
				$dif -= $ndays * 86400;
				$nhours = floor($dif / 3600);
				$dif -= $nhours * 3600;
				$nminutes = floor($dif / 60);
				$dif -= $nminutes * 60;
				$nseconds = $dif;
				$sign = '+';
				if ($row['starttime'] < 0)
				{
					$row['starttime'] = - $row['starttime'];
					$sign = '-';
				}
				if ($sign == '-')
				{
					$project_time_left = '-';
				}
				else
				{
					if ($ndays != '0')
					{
						$project_time_left = $ndays . '{_d_shortform}' . ', ';
						$project_time_left .= $nhours . '{_h_shortform}' . '+';
					}
					else if ($nhours != '0')
					{
						$project_time_left = $nhours . '{_h_shortform}' . ', ';
						$project_time_left .= $nminutes . '{_m_shortform}' . '+';
					}
					else
					{
						$project_time_left = $nminutes . '{_m_shortform}' . ', ';
						$project_time_left .= $nseconds . '{_s_shortform}' . '+';
					}
				}

				$row['timeleft'] = $project_time_left;
				$row['statusmsg'] = '{_starts}' . ': ' . $row['timeleft'];
			}

			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}
		else if ($row['status'] == 'closed')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['statusmsg'] = '{_bidding_closed}';
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}
		else if ($row['status'] == 'delisted')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['statusmsg'] = '{_delisted}';
			$row['provider'] = $row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}
		else if ($row['status'] == 'wait_approval')
		{
			// fetch days since the provider has been awarded giving more direction to the buyer
			$date1split = explode(' ', $row['close_date']);
			$date2split = explode('-', $date1split[0]);
			$days = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
			if ($days == 0)
			{
				$days = 1;
			}
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['statusmsg'] = '{_pending_approval}' . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pending_approval\'] + phrase[\'_day\'] + \' ' . $days . ' \' + phrase[\'_of\'] + \' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>\' + phrase[\'_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
			// service provider information
			$sql_provider = $ilance->db->query("
				SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
				FROM " . DB_PREFIX . $bids_table . "
				WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					AND bidstatus = 'placed'
					AND bidstate = 'wait_approval'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
			$sql_biddername = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res_provider['user_id'] . "'
			", 0, null, __FILE__, __LINE__);
			$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
			$row['provider_id'] = $res_biddername['user_id'];
			$row['provider'] = print_username($res_biddername['user_id']);
			$row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$crypted = array(
				'project_id' => $row['project_id'],
				'from_id' => $_SESSION['ilancedata']['user']['userid'],
				'to_id' => $res_biddername['user_id']
			);
			$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
			unset($crypted);
		}
		else if ($row['status'] == 'approval_accepted')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['statusmsg'] = '{_approval_accepted}';

			$sql_provider = $ilance->db->query("
				SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
				FROM " . DB_PREFIX . $bids_table . "
				WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					AND bidstatus = 'awarded'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				$sql_biddername = $ilance->db->query("
					SELECT user_id, username
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . $res_provider['user_id'] . "'
				", 0, null, __FILE__, __LINE__);
				$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
				$row['provider_id'] = $res_biddername['user_id'];
				$row['provider'] = print_username($res_biddername['user_id']);
				$crypted = array(
					'project_id' => $row['project_id'],
					'from_id' => $_SESSION['ilancedata']['user']['userid'],
					'to_id' => $res_biddername['user_id']
				);
				$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
				unset($crypted);
				$crypted = array(
					'project_id' => $row['project_id'],
					'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
					'seller_id' => $row['provider_id']
				);
				$row['work'] = $ilance->auction->construct_mediashare_icon($_SESSION['ilancedata']['user']['userid'], $row['provider_id'], $row['project_id'], true);
				// next step is to leave feedback if escrow is paid
				$sql_escrowchk = $ilance->db->query("
					SELECT escrow_id
					FROM " . DB_PREFIX . "projects_escrow
					WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
						AND user_id = '" . $row['provider_id'] . "'
						AND project_id = '" . $row['project_id'] . "'
						AND status = 'pending'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_escrowchk) > 0 AND $ilconfig['escrowsystem_enabled'])
				{
					// wait for escrow payment
					$row['statusmsg'] = '<span class="gray">{_next}: <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow"><strong>{_pay_escrow}</strong></a></span> <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pay_escrow\'] + \'</strong></div><div>\' + phrase[\'_in_order_to_ensure_your_service_provider_will_have_funds_available_to_them\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a>';
					$row['feedback'] = $row['invoice'] = '-';
				}
				else
				{
					// does auction have pending provider to buyer invoices that are unpaid?
					$sql_invchk = $ilance->db->query("
						SELECT invoiceid
						FROM " . DB_PREFIX . "invoices
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND p2b_user_id = '" . $row['provider_id'] . "'
							AND projectid = '" . $row['project_id'] . "'
							AND status = 'unpaid'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql_invchk) > 0)
					{
						$pendinvoices = '';
						while ($res_inv = $ilance->db->fetch_array($sql_invchk, DB_ASSOC))
						{
							$crypted = array('id' => $res_inv['invoiceid']);
							$pendinvoices .= '<span class="blue"><a href="' . $ilpage['invoicepayment'] . '?crypted=' . encrypt_url($crypted) . '" title="' . $ilance->language->construct_phrase('{_invoice_x_was_generated_by_x_and_requires_payment}', array($res_inv['invoiceid'], stripslashes($res_biddername['username']))) . '">#' . $res_inv['invoiceid'] . '</a></span>, ';
						}
						if (!empty($pendinvoices))
						{
							$pendinvoices = mb_substr($pendinvoices, 0, -2);
						}

						$row['statusmsg'] = '{_pay_invoices}';
						$row['invoice'] = '<span class="gray">{_pay}:</span> ' . $pendinvoices;
						$unpaid_invoices = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($row['project_id']) . "' AND user_id = '" . intval($row['user_id']) . "' AND status = 'unpaid'", "transactionid");
						$row['feedback'] = '<div><span title="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}"><a href="invoicepayment.php?cmd=view&txn=' . $unpaid_invoices . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
					}
					else
					{
						$row['feedback'] = '-';
						$row['invoice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/invoice_gray.gif" border="0" alt="" />';        
						$provider_rated_buyer = $buyer_rated_provider = 0;

						if ($ilance->feedback->has_left_feedback($_SESSION['ilancedata']['user']['userid'], $row['provider_id'], $row['project_id'], 'buyer'))
						{
							// service provider already rated buyer
							$provider_rated_buyer = 1;
							$row['feedback'] = '<div align="center"><span title="{_feedback_already_submitted__thank_you}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_already_submitted__thank_you}" /></span></div>';        
						}
						else
						{
							// service provider did not rate this buyer!
							$row['statusmsg'] = '<span class="smaller">{_pending_feedback} {_from} ' . fetch_user('username', $row['provider_id']) . '</span>';
						}

						// did this buyer give feedback to the awarded service provider?
						if ($ilance->feedback->has_left_feedback($row['provider_id'], $_SESSION['ilancedata']['user']['userid'], $row['project_id'], 'seller'))
						{
							// buyer already rated awarded service provider
							$buyer_rated_provider = 1;
							$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_complete.gif" border="0" alt="{_feedback_already_submitted__thank_you}" /></div>';
						}
						else
						{
							//echo '1';
							// this buyer did not rate seller
							$row['feedback'] = '<div align="center"><span title="{_submit_feedback_for} ' . fetch_user('username', $row['provider_id']) . '"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback&amp;view=1&amp;returnurl={pageurl_urlencoded}" onmouseover="rollovericon(\'' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_rate.gif\')" onmouseout="rollovericon(\'' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '\', \'' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback.gif" border="0" alt="{_submit_feedback_for} ' . fetch_user('username', $row['provider_id']) . '" name="' . md5($row['provider_id'] . ':' . $_SESSION['ilancedata']['user']['userid'] . ':' . $row['project_id'] . ':feedback') . '" /></a></span></div>';
							$row['statusmsg'] = '<span class="gray">{_leave_feedback}</span>';
						}

						if ($provider_rated_buyer AND $buyer_rated_provider)
						{
							// buyer and service provider both left feedback and ratings
							$crypted = array(
								'cmd' => '_do-auction-action',
								'sub' => 'finished',
								'project_id' => $row['project_id'],
								'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
								'seller_id' => $row['provider_id']
							);
							$row['statusmsg'] = '<span class="gray">{_next}: </span><span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '" onclick="return confirm_js(\'{_please_confirm_setting_this_service_auction_to_finished}\')"><strong>{_set_as_finished}</strong></a></span>';
						}
					}
				}
			}
			else
			{
				$row['work'] = $row['feedback'] = $row['invoice'] = $row['paymethod'] = '-';
				$row['pmb'] = '';
			}
		}
		else if ($row['status'] == 'finished')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" />';
			$row['statusmsg'] = '{_finished}';
			$sql_provider = $ilance->db->query("
				SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
				FROM " . DB_PREFIX . "project_bids
				WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					AND bidstatus = 'awarded'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_provider) > 0)
			{
				$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
				$sql_biddername = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . $res_provider['user_id'] . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_biddername) > 0)
				{
					$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
					$row['provider_id'] = $res_biddername['user_id'];
					$row['provider'] = print_username($res_biddername['user_id']);
					$row['pmb'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $res_biddername['user_id'], $row['project_id']);
				}
				$sqlms = $ilance->db->query("
					SELECT id, name, p_id, project_id, user_id, buyer_id, seller_id, folder_size, folder_type, create_date
					FROM " . DB_PREFIX . "attachment_folder
					WHERE project_id = '" . $row['project_id'] . "'
						AND folder_type = '2'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlms) > 0)
				{
					$crypted = array(
						'project_id' => $row['project_id'],
						'buyer_id' => $_SESSION['ilancedata']['user']['userid'],
						'seller_id' => $res_biddername['user_id']
					);
					$row['work'] = $ilance->auction->construct_mediashare_icon($res_biddername['user_id'], $_SESSION['ilancedata']['user']['userid'], $row['project_id'], true);
				}
				else
				{
					$row['work'] = '-';
				}
				if ($ilconfig['escrowsystem_enabled'] AND $row['filter_escrow'] == '1')
				{
					$do_invoices_exist = $ilance->db->query("
						SELECT invoiceid
						FROM " . DB_PREFIX . "invoices
						WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
							AND invoicetype = 'escrow'
							AND projectid = '" . $row['project_id'] . "'
							AND status = 'unpaid'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($do_invoices_exist) > 0)
					{
						$row['invoice'] = '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '"?cmd=management&amp;sub=rfp-escrow"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></a>';
						$unpaid_invoices = $ilance->db->fetch_field(DB_PREFIX . "invoices", "projectid = '" . intval($row['project_id']) . "' AND user_id = '" . intval($row['user_id']) . "' AND status = 'unpaid'", "transactionid");
						$row['feedback'] = '<div><span title="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}"><a href="invoicepayment.php?cmd=view&txn=' . $unpaid_invoices . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/feedback_gray.gif" border="0" alt="{_please_pay_your_fvf_for_this_listing_before_leaving_feedback}" /></a></span></div>';
					}
					else
					{
						$row['invoice'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" />';
					}
					$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_service_auction_is_finished_and_feedback_has_been_submitted_by_both_parties}" /></div>';
				}
				else
				{
					// escrow system disabled and auction is finished
					$row['feedback'] = '<div align="center"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="{_feedback_submitted__thank_you}" /></div>';
					$row['invoice'] = '-';
				}
			}
			else
			{
				$row['pmb'] = '';       
			}
		}
		else if ($row['status'] == 'archived')
		{
			$row['actions'] = '<input type="checkbox" name="rfp[]" value="' . $row['project_id'] . '" id="' . $row['project_state'] . '_' . $row['project_id'] . '" disabled="disabled" />';
			$row['statusmsg'] = '{_archived}';
			$sql_provider = $ilance->db->query("
				SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
				FROM " . DB_PREFIX . $bids_table . "
				WHERE project_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND project_id = '" . $row['project_id'] . "'
					AND bidstatus = 'awarded'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$res_provider = $ilance->db->fetch_array($sql_provider, DB_ASSOC);
			$sql_biddername = $ilance->db->query("
				SELECT user_id, username
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . $res_provider['user_id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_biddername) > 0)
			{
				$res_biddername = $ilance->db->fetch_array($sql_biddername, DB_ASSOC);
				$row['provider_id'] = $res_biddername['user_id'];
				$row['provider'] = print_username($res_biddername['user_id']);
			}
			else
			{
				$row['provider'] = '-';
				$row['provider_id'] = 0;
			}
			$row['work'] = $row['invoice'] = $row['feedback'] = '-';
			$row['pmb'] = '';
		}

		// transfer project ownership in progress?
		if ($row['transfer_status'] == 'pending')
		{
			$transfername = fetch_user('username', $row['transfer_to_userid']);
			$row['transfer'] = '{_transfer_in_progress}' . ': <strong>' . $transfername . '</strong>';
		}
		else
		{
			$row['transfer'] = '';
		}

		$row['job_title'] = print_string_wrap(handle_input_keywords($row['project_title']), '25');

		// fetch 3 lowest bids placed
		$sel_lowbid = $ilance->db->query("
			SELECT bidamount
			FROM " . DB_PREFIX . $bids_table . "
			WHERE project_id = '" . $row['project_id'] . "'
				AND bidstatus != 'declined'
				AND bidstate != 'retracted'
			ORDER BY bidamount DESC
			LIMIT 3
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sel_lowbid) > 0)
		{
			$row['lowest_bid'] = '';
			while ($res_lowbid = $ilance->db->fetch_array($sel_lowbid, DB_ASSOC))
			{
				if ($res_lowbid['bidamount'] > 0)
				{
					// todo: check for seo urls
					$row['lowest_bid'] .= '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $row['project_id'] . '#bids">' . $ilance->currency->format($res_lowbid['bidamount'], $row['currencyid']) . '</a>, ';
				}
				else
				{
					$row['lowest_bid'] = '-, ';       
				}
			}
			$row['lowest_bid'] = mb_substr($row['lowest_bid'], 0, -2);
		}
		else
		{
			$row['lowest_bid'] = '-';
		}

		// is buyer using escrow?
		$row['escrow'] = ($row['filter_escrow'] == '1')
			? '<a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">{_manage}</a>'
			: '-';
		// auction time left
		if ($row['status'] != 'closed')
		{
			$dif = $row['mytime'];
			$ndays = floor($dif / 86400);
			$dif -= $ndays * 86400;
			$nhours = floor($dif / 3600);
			$dif -= $nhours * 3600;
			$nminutes = floor($dif / 60);
			$dif -= $nminutes * 60;
			$nseconds = $dif;
			$sign = '+';
			if ($row['mytime'] < 0)
			{
				$row['mytime'] = - $row['mytime'];
				$sign = '-';
			}
			if ($sign == '-')
			{
				$project_time_left = '-';
			}
			else
			{
				if ($ndays != '0')
				{
					$project_time_left = $ndays . '{_d_shortform}'.', ';
					$project_time_left .= $nhours . '{_h_shortform}' . '+';
				}
				else if ($nhours != '0')
				{
					$project_time_left = $nhours . '{_h_shortform}' . ', ';
					$project_time_left .= $nminutes . '{_m_shortform}' . '+';
				}
				else
				{
					$project_time_left = $nminutes . '{_m_shortform}' . ', ';
					$project_time_left .= $nseconds . '{_s_shortform}' . '+';
				}
			}

			$row['timeleft'] = $project_time_left;
		}
		else
		{
			$project_time_left = '-';
		}

			    if ($row['status'] == 'expired')
			    {
				    $row['timeleft'] = print_date($row['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			    }
			    else
			    {
					$row['timeleft'] = $row['date_starts'] < DATETIME24H ? $project_time_left : '-';
					$row['timeleft'] = ($row['close_date'] != '0000-00-00 00:00:00' AND $row['close_date'] < DATETIME24H) ? '{_ended}' : $row['timeleft'];
			    }
		$row['icons'] = $ilance->auction->auction_icons($row);

		// #### INVITATION LIST FOR THIS EXPANDED AUCTION
		$sql_invites = $ilance->db->query("
			SELECT id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
			FROM " . DB_PREFIX . "project_invitations
			WHERE project_id = '" . $row['project_id'] . "'
				AND buyer_user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_invites) > 0)
		{
			$row_count_invites = 0;
			while ($rowinvites = $ilance->db->fetch_array($sql_invites, DB_ASSOC))
			{
				if ($rowinvites['seller_user_id'] != '-1')
				{
					$rowinvites['vendor'] = print_username($rowinvites['seller_user_id'], 'href', 0, '&amp;feedback=1', '');
					$rowinvites['lastseen'] = print_date(fetch_user('lastseen', $rowinvites['seller_user_id']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$rowinvites['invitedate'] = print_date($rowinvites['date_of_invite'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					if ($rowinvites['date_of_bid'] == '0000-00-00 00:00:00')
					{
						$rowinvites['biddate'] = '-';
						$rowinvites['bidcheck'] = '0';
					}
					else
					{
						$rowinvites['biddate'] = print_date($rowinvites['date_of_bid'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$rowinvites['bidcheck'] = '1';
					}
					if ($rowinvites['date_of_remind'] == '0000-00-00 00:00:00')
					{
						$rowinvites['reminddate'] = '-';
					}
					else
					{
						$rowinvites['reminddate'] = print_date($rowinvites['date_of_remind'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					}
					$rowinvites['action'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remove-invite&amp;id='.$rowinvites['id'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_uninvite_this_provider_from_bidding_on_your_auction_continue\'])"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="'.'{_remove_invitation}'.'" border="0" /></a>';
					switch ($rowinvites['bidcheck'])
					{
						case '1':
						{
							$rowinvites['bidplaced'] = '{_yes}';
							$rowinvites['remind'] = '-';
							break;
						}                                                        
						case '0':
						{
							$rowinvites['bidplaced'] = '{_not_yet}';
							if ($row['status'] == 'open')
							{
								$rowinvites['remind'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remind&amp;id='.$rowinvites['id'].'" title="'.'{_remind}'.'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_send_a_notification_reminder_to_this_provider_regarding_invitation_to_your_auction\'])">'.'{_remind}'.'</a>';
							}
							else
							{
								$rowinvites['remind'] = '-';
							}
							break;
						}
					}        
				}
				else
				{
					$rowinvites['vendor'] = ucfirst(stripslashes($rowinvites['name'])). ' ('.$rowinvites['email'].')';
					$rowinvites['lastseen'] = ''; //print_date(fetch_user('lastseen', $rowinvites['seller_user_id']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$rowinvites['invitedate'] = print_date($rowinvites['date_of_invite'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					if ($rowinvites['date_of_bid'] == '0000-00-00 00:00:00')
					{
						$rowinvites['biddate'] = '-';
						$rowinvites['bidcheck'] = '0';
					}
					else
					{
						$rowinvites['biddate'] = print_date($rowinvites['date_of_bid'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
						$rowinvites['bidcheck'] = '1';
					}
					if ($rowinvites['date_of_remind'] == '0000-00-00 00:00:00')
					{
						$rowinvites['reminddate'] = '-';
					}
					else
					{
						$rowinvites['reminddate'] = print_date($rowinvites['date_of_remind'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					}
					$rowinvites['action'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remove-invite&amp;id='.$rowinvites['id'].'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_uninvite_this_provider_from_bidding_on_your_auction_continue\'])"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" alt="'.'{_remove_invitation}'.'" border="0" /></a>';
					switch ($rowinvites['bidcheck'])
					{
						case '1':
						{
							$rowinvites['bidplaced'] = '{_yes}';
							$rowinvites['remind'] = '-';
							break;
						}
						case '0':
						{
							$rowinvites['bidplaced'] = '{_not_yet}';
							if ($row['status'] == 'open')
							{
								$rowinvites['remind'] = '<a href="'.$ilpage['buying'].'?cmd=management&amp;subcmd=invitations&amp;action=remind&amp;id='.$rowinvites['id'].'" title="'.'{_remind}'.'" onclick="return confirm_js(phrase[\'_confirmation_you_are_about_to_send_a_notification_reminder_to_this_provider_regarding_invitation_to_your_auction\'])">'.'{_remind}'.'</a>';
							}
							else
							{
								$rowinvites['remind'] = '-';
							}
							break;
						}
					}       
				}
				$rowinvites['bgclass'] = ($row_count_invites % 2) ? 'alt2' : 'alt1';
				$GLOBALS['invitationlist' . $row['project_id']][] = $rowinvites;
				$row_count_invites++;
			}
		}
		else
		{
			$GLOBALS['no_invitationlist' . $row['project_id']][] = 1;
		}
		// #### BIDS ###################################
		$query['bids'] = array();
		$groupby = '';
		if ($bidgrouping)
		{
			$groupby = 'GROUP BY b.bid_id ';
		}
		$inbidgroup = '';
		$cangroupbids = (version_compare(MYSQL_VERSION, '4.1.22', '>')) ? true : false;
		if ($bidgrouping AND $cangroupbids)
		{
			$table = "project_bids";
			$id_field = "bid_id";
			if ($ilance->categories->bidgroupdisplay($row['cid']) == 'lowest')
			{
				// group each bidders bid by lowest placed
				$query['bids'] = "SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MIN(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
				$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $row['project_id'] . "' ORDER BY bidamount ASC LIMIT 1) ";
			}
			else
			{
				// group each bidders bid by highest placed
				$query['bids'] = "SELECT b.bid_id, b.user_id, b.project_id, b.project_user_id, b.proposal, MAX(b.bidamount) AS bidamount, b.bidamounttype, b.bidcustom, b.estimate_days, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
				$inbidgroup = "AND b.bid_id = (SELECT bid_id FROM " . DB_PREFIX . "project_bids WHERE user_id = b.user_id AND project_id = '" . $row['project_id'] . "' ORDER BY bidamount DESC LIMIT 1) ";
			}
		}
		else
		{
			$table = "project_realtimebids";
			$id_field = "id";
			// no bid grouping enabled or supported by installed mysql server
			$query['bids'] = "SELECT b.id, b.bid_id, b.estimate_days, b.user_id, b.project_id, b.project_user_id, b.proposal, b.bidamount, b.bidamounttype, b.bidcustom, b.date_added, b.date_updated, b.date_awarded, b.bidstatus, b.bidstate, ";
		}
		$type_bids = (isset($ilance->GPC['type']) AND $ilance->GPC['type'] != '') ? $ilance->GPC['type'] : 'bidamount';
		$orderby_bids = (isset($ilance->GPC['order']) AND $ilance->GPC['order'] != '') ? $ilance->GPC['order'] : 'ASC';
		$row['sub'] = (isset($ilance->GPC['sub']) AND $ilance->GPC['sub'] != '') ? $ilance->GPC['sub'] : ' ';
		$type_bids = ($table == "project_realtimebids" AND $type_bids == 'bid_id') ? 'id' : $type_bids ;
		$row['order'] = ($orderby_bids == 'ASC') ? 'DESC' : 'ASC';
		$select_table = ($type_bids == 'username' OR $type_bids == 'displayfinancials' ) ? 'u' : 'b';
		$orderby_bids = $select_table. ".".$type_bids." ".$orderby_bids;
		$row['style'] = (isset($ilance->GPC['display']) AND $ilance->GPC['display'] != '' AND $ilance->GPC['display']==$row['project_id'] ) ? ' ' : 'none';
		$query['bids'] .= "b.winnermarkedaspaidmethod, p.status AS project_status, DATE_ADD(b.date_added, INTERVAL - '2' MINUTE) AS date1, p.escrow_id, p.cid, p.description, p.views, p.project_title, p.bids, p.additional_info, p.status, p.close_date, p.project_details, p.project_type, p.bid_details, p.filtered_budgetid, p.currencyid
			FROM " . DB_PREFIX . $table . " AS b,
			" . DB_PREFIX . "projects AS p,
			" . DB_PREFIX . "users AS u
			WHERE b.project_id = '" . $row['project_id'] . "'
			AND p.project_id = b.project_id
			AND b.bidstatus != 'declined'
			AND b.bidstate != 'retracted'
			AND b.user_id = u.user_id
			$inbidgroup
			$groupby
			ORDER BY ". $orderby_bids . "
		";
		$result2 = $ilance->db->query($query['bids'], 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($result2) > 0)
		{
			$row_count_bids = 0;
			
			while ($rows = $ilance->db->fetch_array($result2, DB_ASSOC))
			{
				$project_status = $rows['project_status'];
				$p_id = $row['project_id'];
				$rows['bid_id'] = ($table == "project_realtimebids") ? $rows['id'] : $rows['bid_id'];
				$rows['bidamount'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='".$rows['bid_id']."'", "bidamount"), $rows['currencyid']);
				$rows['delivery'] = $rows['estimate_days'] . ' ' . $ilance->auction->construct_measure($rows['bidamounttype']);
				$proposal = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field . "= '" . $rows['bid_id'] . "'", "proposal");
				if (!empty($proposal))
				{
					$rows['proposal'] = $ilance->bbcode->bbcode_to_html($proposal);
					$rows['proposal'] = strip_vulgar_words($rows['proposal']);
				}
				else
				{
					$rows['proposal'] = '{_no_bid_proposal_was_provided}';
				}
				$rows['isonline'] = print_online_status($rows['user_id']);
				$rows['verified'] = $ilance->profile->fetch_verified_credentials($rows['user_id']);
				$rows['bidder'] = print_username($rows['user_id'], 'href', 0, '', '');
				$rows['city'] = ucfirst(fetch_user('city', $rows['user_id']));
				$rows['state'] = ucfirst(fetch_user('state', $rows['user_id']));
				$rows['zip'] = trim(mb_strtoupper(fetch_user('zip_code', $rows['user_id'])));
				$rows['location'] = $ilance->common_location->print_user_location($rows['user_id']);
				$rows['awarded'] = print_username($rows['user_id'], 'custom', 0, '', '', fetch_user('serviceawards', $rows['user_id']) . ' ' . '{_awards}');
				$rows['reviews'] = print_username($rows['user_id'], 'custom', 0, '', '', $ilance->auction_service->fetch_service_reviews_reported($rows['user_id']) . ' ' . '{_reviews}');
				$rows['earnings'] = print_username($rows['user_id'], 'custom', 0, '', '', $ilance->accounting_print->print_income_reported($rows['user_id']));
				$rows['portfolio'] = '<span class="blue"><a href="' . $ilpage['portfolio'] . '?id=' . $rows['user_id'] .'">{_review}</a></span>';
				$rows['bidattach'] = '-';
				$rows['paymethod2'] = $ilance->payment->print_fixed_payment_method($rows['winnermarkedaspaidmethod'], false);
				if ($rows['paymethod2'] == '')
				{
					$rows['paymethod2'] = '<span class="smaller">-</span>';
				}
				$sql_attachments = $ilance->db->query("
					SELECT attachid, attachtype, user_id, portfolio_id, project_id, pmb_id, category_id, date, filename, filetype, visible, counter, filesize, filehash, ipaddress, tblfolder_ref
					FROM " . DB_PREFIX . "attachment
					WHERE attachtype = 'bid'
						AND project_id = '" . $row['project_id'] . "'
						AND user_id = '" . $rows['user_id'] . "'
						AND visible = '1'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql_attachments) > 0)
				{
					$bidattach = '';
					$c = 1;
					while ($res = $ilance->db->fetch_array($sql_attachments, DB_ASSOC))
					{
						if ($res['date'] < $rows['date_added'] AND $rows['date1'] < $res['date'])
						{
							$bidattach .= '<div class="smaller blue" style="padding-bottom:3px" title="' . handle_input_keywords($res['filename']) . '">' . $c . '. <a href="' . HTTP_SERVER . $ilpage['attachment'] . '?id=' . $res['filehash'] . '" target="_blank">' . print_string_wrap(handle_input_keywords($res['filename'])) . '</a></div>';
							$c++;
						}
					}
					$rows['bidattach'] = $bidattach;
				}

				$rows['actionclass'] = 'award';
				$rows['pmb2'] = '-';

				// #### custom bid field answers #######
				$rows['custom_bid_fields'] = $ilance->bid_fields->construct_bid_fields($rows['cid'], $rows['project_id'], 'output1', 'service', $rows['bid_id'], false);
				$row['awardedbidsbit'] = '';

				// open or expired actions
				switch ($project_status)
				{
					// #### open or expired
					case 'open':
					case 'expired':
					{
						$bidstatus = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field . " = '" . $rows['bid_id'] . "'", "bidstatus");
						if ($bidstatus == 'declined')
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
							$rows['bidaction'] = '{_this_bid_has_been_declined}';
							$rows['actionclass'] = 'declined';
						}
						else
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" style="font-size:15px" value=" {_accept} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />&nbsp;&nbsp;';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] .= '<input type="button" style="font-size:15px" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />';
						}
						$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						break;
					}                                                
					// #### provider accepted buyers award
					case 'approval_accepted':
					{
						$row['awardedbidsbit'] = ', 1 ' . '{_awarded_lower}';
						$bidstatus = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstatus");
						$bidstate = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstate");
						if ($bidstatus == 'declined')
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
							$rows['bidaction'] = '{_this_bid_has_been_declined}';
							$rows['actionclass'] = 'declined';
						}
						else if ($bidstatus == 'awarded' AND ($bidstate != 'reviewing' OR $bidstate != 'wait_approval'))
						{
							$awarded_vendor = stripslashes($rows['bidder']);
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$buttonvisible = 'disabled="disabled"';
							if ($ilconfig['servicebid_buyerunaward'])
							{
								$buttonvisible = '';        
							}
							$rows['bidaction'] = '<input type="button" style="font-size:15px" value=" {_unaward} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" ' . $buttonvisible . ' />';
							$rows['actionclass'] = 'unaward';
							$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing' OR $bidstatus == 'choseanother' AND $bidstate == 'reviewing')
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$rows['pmb2'] = '-';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							$awarded_vendor = stripslashes($rows['bidder']);
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_unaward} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
							$rows['actionclass'] = 'unaward';
							$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						}
						else if ($bidstatus == 'placed' AND empty($bidstate))
						{
							$rows['pmb2'] = '-';
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else
						{
							$rows['pmb2'] = '-';
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						break;
					}                                                        
					// #### buyer waiting for provider's acceptance to award
					case 'wait_approval':
					{
						$row['awardedbidsbit'] = ', 1 ' . '{_awarded_lower}';
						// buyer awarded provider :: enable radio icons :: create additional award cancellation button
						$bidstatus = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstatus");
						$bidstate = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstate");
						if ($bidstatus == 'declined')
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
							$rows['bidaction'] = '{_this_bid_has_been_declined}';
							$rows['actionclass'] = 'declined';
							$rows['pmb2'] = '-';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							// buyer pending approval from service provider (provider did not confirm acceptance to project)
							$awarded_vendor = stripslashes($rows['bidder']);
							$rows['award'] = '{_pending_approval}' . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pending_approval\'] + phrase[\'_day\'] + \'' . $days . '\' + phrase[\'_of\'] + \' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>\' + phrase[\'_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_unaward} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
							$rows['actionclass'] = 'unaward';
							$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_accept} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" />&nbsp;';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] .= '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}        
						break;
					}                                                
					// #### listing is finished/completed
					case 'finished':
					{
						$bidstatus = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstatus");
						$bidstate = $ilance->db->fetch_field(DB_PREFIX . $table, $id_field."='". $rows['bid_id'] . "'", "bidstate");
						// project in a phase to not allow any bid controls
						if ($bidstatus == 'declined')
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/declined.gif" border="0" alt="" id="" />';
							$rows['bidaction'] = '{_this_bid_has_been_declined}';
							$rows['actionclass'] = 'declined';
							$rows['pmb2'] = '-';
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'wait_approval')
						{
							$row['awardedbidsbit'] = ', 1 ' . '{_awarded_lower}';
							$rows['award'] = '{_pending_approval}' . ' <a href="javascript:void(0)" onmouseover="Tip(\'<div><strong>\' + phrase[\'_pending_approval\'] + phrase[\'_day\'] + \'' . $days . '\' + phrase[\'_of\'] + \' ' . $ilconfig['servicebid_awardwaitperiod'] . '</strong></div><div>\' +phrase[\'_pending_approval_allows_the_awarded_service_provider_to_accept_or_reject_the_service_auction\'] + \'</div>\', BALLOON, true, ABOVE, true, OFFSETX, -17, FADEIN, 600, FADEOUT, 600, PADDING, 8)" onmouseout="UnTip()"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/tip.gif" border="0" alt="" /></a><div class="smaller gray">({_day} ' . $days . ' {_of} ' . $ilconfig['servicebid_awardwaitperiod'] . ')</div>';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'unawardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_unaward} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
							$rows['actionclass'] = 'unaward';
							$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						}
						else if ($bidstatus == 'placed' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'choseanother' AND $bidstate == 'reviewing')
						{
							// service provider in review mode - 90% change will not become awarded
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" style="font-size:15px" />';
						}
						else if ($bidstatus == 'awarded')
						{
							$row['awardedbidsbit'] = ', 1 ' . '{_awarded_lower}';
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded.gif" border="0" alt="" id="" />';
							$rows['bidaction'] = '<input type="button" value=" {_unaward} " class="buttons" disabled="disabled" style="font-size:15px" />';
							$rows['actionclass'] = 'unaward';
							$rows['pmb2'] = $ilance->auction->construct_pmb_icon($_SESSION['ilancedata']['user']['userid'], $rows['user_id'], $rows['project_id']);
						}
						else
						{
							$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'awardbid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] = '<input type="button" value=" {_accept} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" />&nbsp;';
							$crypted = array(
								'cmd' => '_do-rfp-action',
								'bidcmd' => 'declinebid',
								'bid_id' => $rows['bid_id'],
								'bid_grouping' => $bidgrouping
							);
							$rows['bidaction'] .= '<input type="button" value=" {_decline} " onclick="if (confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')) location.href=\'' . $ilpage['buying'] . '?crypted=' . encrypt_url($crypted) . '\'" class="buttons" disabled="disabled" style="font-size:15px" />';
						}
						break;
					}                                                
					// #### listing is closed or delisted
					case 'closed':
					case 'delisted':
					{
						// project in a phase to not allow any bid controls
						$rows['pmb2'] = '-';
						$rows['award'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'bid_awarded_gray.gif" border="0" alt="" id="" />';
						$rows['bidaction'] = '&nbsp;';
						break;
					}
				}
				// bid amount type
				switch ($rows['bidamounttype'])
				{
					case 'entire':
					{
						$rows['bidamounttype'] = '{_for_entire_project}';
						break;
					}
					case 'hourly':
					{
						$rows['bidamounttype'] = '{_per_hour}';
						break;
					}                                                
					case 'daily':
					{
						$rows['bidamounttype'] = '{_per_day}';
						break;
					}                                                
					case 'weekly':
					{
						$rows['bidamounttype'] = '{_weekly}';
						break;
					}                                                
					case 'monthly':
					{
						$rows['bidamounttype'] = '{_monthly}';
						break;
					}                                                
					case 'lot':
					{
						$rows['bidamounttype'] = '{_per_lot}';
						break;
					}                                                
					case 'weight':
					{
						$rows['bidamounttype'] = '{_per_weight}' . ' ' . stripslashes($rows['bidcustom']);
						break;
					}
					case 'item':
					{
						$rows['bidamounttype'] = '{_per_item}';
						break;
					}
				}
				$rows['bid_datetime'] = print_date($rows['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$rows['class2'] = ($row_count_bids % 2) ? 'alt2' : 'alt1';
				$GLOBALS['service_buying_bidding_activity' . $row['project_id']][] = $rows;
				$row_count_bids++;
			}
		}
		else
		{
			$GLOBALS['no_service_buying_bidding_activity' . $row['project_id']] = 1;
			$row['awardedbidsbit'] = '';
			$row_count_bids = 0;
		}
		$row['bids'] = isset($row_count_bids) ? $row_count_bids : 0;
		// insertion fee in this category
		if ($row['insertionfee'] > 0 AND $row['ifinvoiceid'] > 0)
		{
			$row['insfee'] = ($row['isifpaid'])
				? '<div class="smaller"><span class="blue" title="{_unpaid}"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></span></div>'
				: '<div class="smaller"><span class="red" title="{_unpaid}"><a href="' . HTTPS_SERVER . $ilpage['invoicepayment'] . '?cmd=view&amp;id=' . $row['ifinvoiceid'] . '">(' . $ilance->currency->format($row['insertionfee']) . ')</a></span></div>';
		}
		else
		{
			$row['insfee'] = '-';
		}
		if ($row['highlite'])
		{
			$row['class'] = 'featured_highlight';
		}
		else
		{
			$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		}
		$service_buying_activity[] = $row;
		$row_count++;
	}

	$show['no_service_buying_activity'] = false;
	$show['rfppulldownmenu'] = true;
}
else
{
	$show['no_service_buying_activity'] = true;
	$show['rfppulldownmenu'] = false;
}
$prevnext = print_pagnation($number, $pp, intval($ilance->GPC['page']), $counter, $scriptpage, 'page');

($apihook = $ilance->api('buying_activity_service_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>