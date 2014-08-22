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
if (!class_exists('auction'))
{
	exit;
}

/**
* Class to handle auction fees.
*
* @package      iLance\Auction\Fees
* @version      4.0.0.8059
* @author       ILance
*/
class auction_fee extends auction
{
	function fetch_duration_unit_fee($duration, $unittype = '', $cid = 0)
	{
		global $ilance, $ilconfig;
		$fee = 0;
		switch ($unittype)
		{
			case 'D':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $ilance->auction_post->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationdays']));
				foreach ($unitnumbers AS $days)
				{
					if (strchr($days, ':'))
					{
						$daybits = explode(':', $days);
						if ($daybits[0] == 'GTC')
						{
							if (isset($duration) AND $duration == $daybits[0])
							{
								$fee = $daybits[1];
							}
						}
						else
						{
							if (isset($duration) AND $duration == $daybits[0])
							{
								$fee = $daybits[1];
							}
						}
					}
				}
				break;
			}
			case 'H':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $ilance->auction_post->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationhours']));
				foreach ($unitnumbers AS $hours)
				{
					if (strchr($hours, ':'))
					{
						$hourbits = explode(':', $hours);
						if (isset($duration) AND $duration == $hourbits[0])
						{
							$fee = $hourbits[1];
						}
					}
				}
				break;
			}
			case 'M':
			{
				$unitnumbers = ($cid > 0 ? explode(',', $ilance->auction_post->fetch_category_duration($cid, $unittype)) : explode(',', $ilconfig['durationminutes']));
				foreach ($unitnumbers AS $minutes)
				{
					if (strchr($minutes, ':'))
					{
						$minutebits = explode(':', $minutes);
						if (isset($duration) AND $duration == $minutebits[0])
						{
							$fee = $minutebits[1];
						}
					}
				}
				break;
			}
		}
		return $fee;
	}

	/**
	* Function to process selected auction enhancements which also creates necessary transactions.
	* Additionally, this function cooperates with the admin defined tax logic for listing enhancements.
	* This function will also update the invoice to isenhancementfee = '1' on successful transaction.
	*
	* @param       integer      category id
	* @param       integer      duration
	* @param       string       duration unit
	* @param       integer      user id posting auction
	* @param       integer      listing id
	* @param       string       category type (service or product)
	* @param       boolean      only calculate duration transaction fee? (default false)
	*
	* @return      boolean      Returns true or false if invoice was generated (paid or unpaid)
	*/
	function process_listing_duration_transaction($cid = 0, $duration = '', $duration_unit = '', $userid = 0, $rfpid = 0, $cattype = '', $only_calculate = false, $gtc = '0')
	{
		global $ilance, $ilconfig, $phrase;
		$sumfees = $this->fetch_duration_unit_fee($duration, $duration_unit, $cid);
		$unit_txt = '';
		switch ($duration_unit)
		{
			case 'D':
			{
				$unit_txt = '{_days}';
				break;
			}
			case 'H':
			{
				$unit_txt = '{_hours}';
				break;
			}
			case 'M':
			{
				$unit_txt = '{_minutes}';
				break;
			}
		}
		$unit_txt = ($gtc == '1') ? '{_gtc}' : $duration . ' ' . $unit_txt;
		$htmlenhancements = $unit_txt . ' - ' . $ilance->currency->format($sumfees);
		if ($only_calculate == true)
		{
			return $sumfees;
		}
		else if ($sumfees > 0)
		{
			// does owner have sufficient funds?
			$sql = $ilance->db->query("
			    SELECT available_balance, total_balance, autopayment
			    FROM " . DB_PREFIX . "users
			    WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resaccount = $ilance->db->fetch_array($sql, DB_ASSOC);
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $sumfees) . "',";
				$sumfeesnotax = $sumfees;
				if ($ilance->tax->is_taxable($userid, 'enhancements'))
				{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 0);
					$totalamount = ($sumfees + $taxamount);
					$taxinfo = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 1);
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					$sumfees = $totalamount;
				}
				$transactionid = $ilance->accounting_payment->construct_transaction_id();
				// #### CREATE PAID TRANSACTION ################
				if ($resaccount['available_balance'] >= $sumfees AND $resaccount['autopayment'])
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, intval($rfpid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($rfpid)) . ' #' . intval($rfpid) . ' - ' . $htmlenhancements, sprintf("%01.2f", $sumfeesnotax), sprintf("%01.2f", $sumfees), 'paid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 1, $transactionid, 0, 0
					);
					// debit funds from online account balance
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = available_balance - " . sprintf("%01.2f", $sumfees) . ",
						total_balance = total_balance - " . sprintf("%01.2f", $sumfees) . "
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1',
						isautopayment = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					// update auction with enhancements fee
					// set enhancements fee invoice flag as paid in full so this project doesn't show in the pending queue
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $sumfees) . ",
						isenhancementfeepaid = '1',
						enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
					// track spending habits
					$ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $sumfees), 'credit');
					// track this user as paid for enhancements so his/her referral can see this from their my cp
					$ilance->referral->update_referral_action('enhancements', intval($userid));
				}
				// #### CREATE UNPAID TRANSACTION ##############
				else
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, intval($rfpid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($rfpid)) . ' #' . intval($rfpid) . ' - ' . $htmlenhancements, sprintf("%01.2f", $sumfeesnotax), 0, 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '', 0, 0, 1, $transactionid, 0, 0
					);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					$status = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'payasgoexempt') == 'no' AND $ilconfig['globalauctionsettings_payperpost']) ? ", status = 'frozen' " : " ";
					// set enhancements fee invoice flag as NOT PAID so this project DOES show in the pending queue
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $sumfees) . ",
						isenhancementfeepaid = '0',
						enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
						$status
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
				}
				return true;
			}
		}
		return false;
	}

	/**
	* Function to process selected auction enhancements which also creates necessary transactions.
	* Additionally, this function cooperates with the admin defined tax logic for listing enhancements.
	* This function will also update the invoice to isenhancementfee = '1' on successful transaction.
	*
	* @param       array        enhancements array selected during the posting of the auction
	* @param       integer      user id posting auction
	* @param       integer      auction id
	* @param       string       mode (insert or update) for letting this work when updating auction as well
	* @param       string       category type (service or product)
	*
	* @return      nothing
	*/
	function process_listing_enhancements_transaction($enhancements = array (), $userid = 0, $rfpid = 0, $mode = 'insert', $cattype = '', $only_calculate = false)
	{
		global $ilance, $ilconfig, $phrase;
		if ($mode == 'insert')
		{
			$options = array (
				'bold' => 0,
				'highlite' => 0,
				'featured' => 0,
				'autorelist' => 0,
				'featured_searchresults' => 0,
				'buynow' => 0,
				'reserve' => 0,
				'video' => 0,
			);
		}
		else if ($mode == 'update')
		{
			$options = array (
				'bold' => (isset($ilance->GPC['old']['bold']) ? $ilance->GPC['old']['bold'] : '0'),
				'highlite' => (isset($ilance->GPC['old']['highlite']) ? $ilance->GPC['old']['highlite'] : '0'),
				'featured' => (isset($ilance->GPC['old']['featured']) ? $ilance->GPC['old']['featured'] : '0'),
				'autorelist' => (isset($ilance->GPC['old']['autorelist']) ? $ilance->GPC['old']['autorelist'] : '0'),
				'buynow' => (isset($ilance->GPC['old']['buynow']) ? $ilance->GPC['old']['buynow'] : '0'),
				'reserve' => (isset($ilance->GPC['old']['reserve']) ? $ilance->GPC['old']['reserve'] : '0'),
				'video' => ((isset($ilance->GPC['old']['description_videourl']) AND !empty($ilance->GPC['old']['description_videourl'])) ? '1' : '0'),
				'featured_searchresults' => (isset($ilance->GPC['old']['featured_searchresults']) ? $ilance->GPC['old']['featured_searchresults'] : '0'),
			);
		}
		$sumfees = 0;
		$htmlenhancements = '';
		foreach ($enhancements AS $enhancement => $value)
		{
			// #### bold ###########################################
			if (isset($enhancement) AND $enhancement == 'bold')
			{
				if ($ilconfig["{$cattype}upsell_boldactive"])
				{
					$options['bold'] = 1;
					if ($ilconfig["{$cattype}upsell_boldfees"])
					{
						$sumfees += $ilconfig["{$cattype}upsell_boldfee"];
						$htmlenhancements .= '{_bold} - ' . $ilance->currency->format($ilconfig["{$cattype}upsell_boldfee"]) . ', ';
					}
				}
			}
			// #### highlight ######################################
			else if (isset($enhancement) AND $enhancement == 'highlite')
			{
				if ($ilconfig["{$cattype}upsell_highlightactive"])
				{
					$options['highlite'] = 1;
					if ($ilconfig["{$cattype}upsell_highlightfees"])
					{
						$sumfees += $ilconfig["{$cattype}upsell_highlightfee"];
						$htmlenhancements .= '{_listing_highlight} - ' . $ilance->currency->format($ilconfig["{$cattype}upsell_highlightfee"]) . ', ';
					}
				}
			}
			// #### featured #######################################
			else if (isset($enhancement) AND $enhancement == 'featured')
			{
				if ($ilconfig["{$cattype}upsell_featuredactive"])
				{
					$options['featured'] = 1;
					if ($ilconfig["{$cattype}upsell_featuredfees"])
					{
						$sumfees += $ilconfig["{$cattype}upsell_featuredfee"];
						$htmlenhancements .= '{_featured_homepage} (' . $ilconfig["{$cattype}upsell_featuredlength"] . ' {_days_lower}) - ' . $ilance->currency->format($ilconfig["{$cattype}upsell_featuredfee"]) . ', ';
					}
				}
			}
			// #### featured on search results #####################
			else if (isset($enhancement) AND $enhancement == 'featured_searchresults')
			{
				if ($ilconfig["{$cattype}upsell_featured_searchresultsactive"])
				{
					$options['featured_searchresults'] = 1;
					if ($ilconfig["{$cattype}upsell_featured_searchresultsfees"])
					{
						$sumfees += $ilconfig["{$cattype}upsell_featured_searchresultsfee"];
						$htmlenhancements .= '{_featured_in_search_results} - ' . $ilance->currency->format($ilconfig["{$cattype}upsell_featured_searchresultsfee"]) . ', ';
					}
				}
			}
			// #### auto-relist ####################################
			else if (isset($enhancement) AND $enhancement == 'autorelist')
			{
				if ($ilconfig["{$cattype}upsell_autorelistactive"])
				{
					$options['autorelist'] = 1;
					if ($ilconfig["{$cattype}upsell_autorelistfees"])
					{
						$sumfees += $ilconfig["{$cattype}upsell_autorelistfee"];
						$htmlenhancements .= '{_autorelist} - ' . $ilance->currency->format($ilconfig["{$cattype}upsell_autorelistfee"]) . ', ';
					}
				}
			}
			// #### buy now price (product) ########################
			else if (isset($enhancement) AND $enhancement == 'buynow')
			{
				$options['buynow'] = 0;
				if ($cattype == 'product')
				{
					if ($ilconfig['productupsell_buynowcost'] > 0)
					{
						$htmlenhancements .= '{_buy_now_price} - ' . $ilance->currency->format($ilconfig['productupsell_buynowcost']) . ', ';
						$sumfees += $ilconfig['productupsell_buynowcost'];
						$options['buynow'] = 1;
					}
				}
			}
			// #### reserve price (product) ########################
			else if (isset($enhancement) AND $enhancement == 'reserve')
			{
				$options['reserve'] = 0;
				if ($cattype == 'product')
				{
					if ($ilconfig['productupsell_reservepricecost'] > 0)
					{
						$htmlenhancements .= '{_reserve_price} - ' . $ilance->currency->format($ilconfig['productupsell_reservepricecost']) . ', ';
						$sumfees += $ilconfig['productupsell_reservepricecost'];
						$options['reserve'] = 1;
					}
				}
			}
		}
		// determine how many additional slideshow pictures were uploaded
		$pictures = 0;
		if ($ilconfig['productupsell_slideshowcost'] > 0 AND $cattype == 'product')
		{
			$and = ($only_calculate == true) ? "" : " AND invoiceid = '0'";
			$sql = $ilance->db->query("
				SELECT COUNT(*) AS pictures
				FROM " . DB_PREFIX . "attachment
				WHERE attachtype = 'slideshow'
					AND project_id = '" . intval($rfpid) . "'
					$and
			");
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$pictures = intval(($res['pictures'] > 0) ?  $res['pictures'] - 1 : $res['pictures']);
			if ($pictures > 0)
			{
				$sumfees += ($ilconfig['productupsell_slideshowcost'] * $pictures);
				$htmlenhancements .= '{_photo_slideshow_media} (' . $pictures . ' {_pictures_lower}) - ' . $ilance->currency->format($ilconfig['productupsell_slideshowcost'] * $pictures) . ', ';
			}
		}
		if (($ilconfig['serviceupsell_videodescriptioncost'] > 0 OR $ilconfig['productupsell_videodescriptioncost'] > 0) AND (isset($ilance->GPC['old']['description_videourl']) AND !empty($ilance->GPC['old']['description_videourl']) OR isset($ilance->GPC['description_videourl']) AND !empty($ilance->GPC['description_videourl'])))
		{
			$options['video'] = 0;
			if ($cattype == 'service')
			{
				if ($ilconfig['serviceupsell_videodescriptioncost'] > 0)
				{
					$sumfees += $ilconfig['serviceupsell_videodescriptioncost'];
					$options['video'] = 1;
					$htmlenhancements .= '{_video} - ' . $ilance->currency->format($ilconfig['serviceupsell_videodescriptioncost']) . ', ';
				}
			}
			else if ($cattype == 'product')
			{
				if ($ilconfig['productupsell_videodescriptioncost'] > 0)
				{
					$sumfees += $ilconfig['productupsell_videodescriptioncost'];
					$options['video'] = 1;
					$htmlenhancements .= '{_video} - ' . $ilance->currency->format($ilconfig['productupsell_videodescriptioncost']) . ', ';
				}
			}
		}
		if (!empty($htmlenhancements))
		{
			$htmlenhancements = mb_substr($htmlenhancements, 0, -2);
		}
		if ($only_calculate == true)
		{
			return $sumfees;
		}
		else if ($sumfees > 0)
		{
			// does owner have sufficient funds?
			$sql = $ilance->db->query("
				SELECT available_balance, total_balance, autopayment
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resaccount = $ilance->db->fetch_array($sql, DB_ASSOC);
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $sumfees) . "',";
				$sumfeesnotax = $sumfees;
				if ($ilance->tax->is_taxable($userid, 'enhancements') AND $sumfees > 0)
				{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 0);
					// fetch total amount to hold within the "totalamount" field
					$totalamount = ($sumfees + $taxamount);
					// fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 1);
					// member is taxable for this invoice type
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					$sumfees = $totalamount;
				}
				$transactionid = $ilance->accounting_payment->construct_transaction_id();
				// #### CREATE PAID TRANSACTION ################
				if ($resaccount['available_balance'] >= $sumfees AND $resaccount['autopayment'])
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, intval($rfpid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($rfpid)) . ' #' . intval($rfpid) . ' - ' . $htmlenhancements, sprintf("%01.2f", $sumfeesnotax), sprintf("%01.2f", $sumfees), 'paid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 1, $transactionid, 0, 0
					);
					// debit funds from online account balance
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = available_balance - " . sprintf("%01.2f", $sumfees) . ",
						total_balance = total_balance - " . sprintf("%01.2f", $sumfees) . "
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1',
						isautopayment = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($pictures > 0)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET invoiceid = '" . intval($invoiceid) . "'
							WHERE attachtype = 'slideshow'
								AND project_id = '" . intval($rfpid) . "'
								AND invoiceid = '0'
						", 0, null, __FILE__, __LINE__);
					}
					// update auction with enhancements fee
					// set enhancements fee invoice flag as paid in full so this project doesn't show in the pending queue
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $sumfees) . ",
						isenhancementfeepaid = '1',
						enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
					// track spending habits
					$ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $sumfees), 'credit');
					// track this user as paid for enhancements so his/her referral can see this from their my cp
					$ilance->referral->update_referral_action('enhancements', intval($userid));
				}
				// #### CREATE UNPAID TRANSACTION ##############
				else
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, intval($rfpid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($rfpid)) . ' #' . intval($rfpid) . ' - ' . $htmlenhancements, sprintf("%01.2f", $sumfeesnotax), 0, 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '', 0, 0, 1, $transactionid, 0, 0
					);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($pictures > 0)
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "attachment
							SET invoiceid = '" . intval($invoiceid) . "'
							WHERE attachtype = 'slideshow'
								AND project_id = '" . intval($rfpid) . "'
								AND invoiceid = '0'
						", 0, null, __FILE__, __LINE__);
					}
					$status = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'payasgoexempt') == 'no' AND $ilconfig['globalauctionsettings_payperpost']) ? ", status = 'frozen' " : " ";
					// update auction with enhancements fee
					// set enhancements fee invoice flag as NOT PAID so this project DOES show in the pending queue
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $sumfees) . ",
						isenhancementfeepaid = '0',
						enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
						$status
						WHERE project_id = '" . intval($rfpid) . "'
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
		return $options;
	}

	/**
	* Function for creating a new insertion fee transaction which is usually executed during the initial posting
	* of a service or product auction.  This function will attempt to debit the amount owing from the user's
	* account balance (if funds available) otherwise it will create an unpaid transaction and force the auction to be
	* hidden until payment is completed.  This function takes into consideration a user with insertion fees exemption
	* as well as taxes.
	*
	* @param       integer      category id
	* @param       string       category type (service or product) default is service
	* @param       string       amount to charge
	* @param       integer      project id
	* @param       integer      user id
	* @param       bool         is a budget range type insertion group (true or false)
	* @param       integer      budget range id that is selected
	* @param       bool         is being called via bulk upload (default false)
	* @param       array        bulk uploaded items listings ids
	*/
	function process_insertion_fee_transaction($cid = 0, $cattype = 'service', $amount = 0, $pid = 0, $userid = 0, $isbudgetrange = 0, $filtered_budgetid = 0, $isbulkupload = false, $pids = array (), $currencyid = 0)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$fee = $fee2 = 0;
		$feetitle = '';
		$currencyid = ($currencyid == '0') ? $ilconfig['globalserverlocale_defaultcurrency'] : $currencyid;
		if ($ilconfig['globalserverlocale_defaultcurrency'] != $currencyid AND $pid != 0)
		{
			$amount = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $amount, $currencyid);
		}
		// #### process single fee transaction #########################
		if ($isbulkupload == false)
		{
			// #### PRODUCT INSERTION FEE ##################################
			if ($cattype == 'product')
			{
				$ifgroupname = $ilance->categories->insertiongroup($cid);
				if ($userid > 0)
				{
					$forceifgroupid = $ilance->permissions->check_access($userid, "{$cattype}insgroup");
					if ($forceifgroupid > 0)
					{
						$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
					}
				}
				$sql = $ilance->db->query("
					SELECT insertion_to, insertion_from, amount
					FROM " . DB_PREFIX . "insertion_fees
					WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
					    AND state = '" . $ilance->db->escape_string($cattype) . "'
					ORDER BY sort ASC
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$found = 0;
					while ($rows = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						if ($rows['insertion_to'] == '-1')
						{
							if ($amount >= $rows['insertion_from'] AND $rows['insertion_to'] == '-1')
							{
								$found = 1;
								$fee += $rows['amount'];
							}
						}
						else
						{
							if ($amount >= $rows['insertion_from'] AND $amount <= $rows['insertion_to'])
							{
								$found = 1;
								$fee += $rows['amount'];
							}
						}
					}
					if ($found == 0)
					{
						$fee = 0;
					}
				}
				else
				{
					$fee = 0;
				}
			}
			// #### SERVICE INSERTION FEE ##################################
			else if ($cattype == 'service')
			{
				// #### BUDGET RANGE INSERTION FEES ####################
				if ($isbudgetrange AND $filtered_budgetid > 0)
				{
					$insertiongroup = $ilance->db->fetch_field(DB_PREFIX . "budget", "budgetid = '" . intval($filtered_budgetid) . "'", "insertiongroup");
					$ifgroupname = $insertiongroup;
					if ($userid > 0)
					{
						$forceifgroupid = $ilance->permissions->check_access($userid, "{$cattype}insgroup");
						if ($forceifgroupid > 0)
						{
							$ifgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($forceifgroupid) . "'", "groupname");
						}
					}
					$sql = $ilance->db->query("
						SELECT amount
						FROM " . DB_PREFIX . "insertion_fees
						WHERE groupname = '" . $ilance->db->escape_string($ifgroupname) . "'
						    AND state = '" . $ilance->db->escape_string($cattype) . "'
						ORDER BY sort ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						// our budget range has some insertion group defined ..
						while ($rows = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$fee += $rows['amount'];
						}
						$feetitle .= '{_budget_range}: ' . $ilance->auction_service->fetch_rfp_budget($pid, false) . ' - {_insertion_fee}: ' . $ilance->currency->format($fee) . ', ';
					}
				}
				else
				{
					// buyer decides to set project as budget non-disclosed (does not select a pre-defined budget range)
					// is admin charging fees in this category for non-disclosed auctions?
					$ndfee = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "nondisclosefeeamount");
					if ($ndfee > 0)
					{
						$fee = $ndfee;
						$feetitle .= '{_budget_range}: {_non_disclosed} - {_insertion_fee}: ' . $ilance->currency->format($fee) . ', ';
					}
					unset($ndfee);
				}
				// #### CATEGORY BASE INSERTION FEES ###################
				$insertiongr = $ilance->categories->insertiongroup($cid);
				if (!empty($insertiongr))
				{
					$sql = $ilance->db->query("
						SELECT amount
						FROM " . DB_PREFIX . "insertion_fees
						WHERE groupname = '" . $ilance->db->escape_string($insertiongr) . "'
						    AND state = '" . $ilance->db->escape_string($cattype) . "'
						ORDER BY sort ASC
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						while ($rows = $ilance->db->fetch_array($sql, DB_ASSOC))
						{
							$fee += $rows['amount'];
							$fee2 += $rows['amount'];
						}
						$feetitle .= '{_category}: ' . $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $cid) . ' - {_insertion_fee}: ' . $ilance->currency->format($fee2) . ', ';
					}
				}
				unset($insertiongr, $fee2);
			}
			// chop trailing ", " from the ending of the generated fee title
			if (!empty($feetitle))
			{
				$feetitle = mb_substr($feetitle, 0, -2);
			}
			else if (empty($feetitle))
			{
				$feetitle = '{_insertion_fee}';
			}
			// check if we're exempt from insertion fees
			if ($userid > 0 AND $ilance->permissions->check_access($userid, 'insexempt') == 'yes')
			{
				$fee = 0;
			}
			// try to debit the account of this user
			if ($fee > 0)
			{
				// #### taxes on insertion fees ################
				$extrainvoicesql = '';
				$feenotax = $fee;
				if ($ilance->tax->is_taxable(intval($userid), 'insertionfee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 0);
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($fee + $taxamount);
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($userid), $fee, 'insertionfee', 1);
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					// ensure the fee we use below contains the taxes added also
					$fee = $totalamount;
				}
				// does owner have sufficient funds?
				$sqlaccount = $ilance->db->query("
					SELECT available_balance, autopayment
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlaccount) > 0)
				{
					$resaccount = $ilance->db->fetch_array($sqlaccount, DB_ASSOC);
					if ($resaccount['available_balance'] >= $fee AND $resaccount['autopayment'])
					{
						$invoiceid = $ilance->accounting->insert_transaction(
							0, intval($pid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid) . ' : ' . $feetitle, sprintf("%01.2f", $feenotax), sprintf("%01.2f", $fee), 'paid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 1
						);
						// update invoice mark as insertion fee invoice type
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET $extrainvoicesql
							isif = '1',
							isautopayment = '1'
							WHERE invoiceid = '" . intval($invoiceid) . "'
						", 0, null, __FILE__, __LINE__);
						// update auction with insertion fee
						// set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET insertionfee = '" . sprintf("%01.2f", $fee) . "',
							isifpaid = '1',
							ifinvoiceid = '" . intval($invoiceid) . "'
							WHERE project_id = '" . intval($pid) . "'
						", 0, null, __FILE__, __LINE__);
						// adjust account balance
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET available_balance = available_balance - $fee,
							total_balance = total_balance - $fee
							WHERE user_id = '" . intval($userid) . "'
						", 0, null, __FILE__, __LINE__);
						// track spending habits
						$ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $fee), 'credit');
						// #### REFERRAL SYSTEM TRACKER ############################
						$ilance->referral->update_referral_action('ins', intval($userid));
					}
					else
					{
						$invoiceid = $ilance->accounting->insert_transaction(
							0, intval($pid), 0, intval($userid), 0, 0, 0, '{_auction} - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid) . ' : ' . $feetitle, sprintf("%01.2f", $feenotax), '', 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '', 0, 0, 1
						);
						// update invoice mark as insertion fee invoice type
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET $extrainvoicesql
							isif = '1'
							WHERE invoiceid = '" . intval($invoiceid) . "'
						", 0, null, __FILE__, __LINE__);
						$status = ($ilance->permissions->check_access($userid, 'payasgoexempt') == 'no' AND $ilconfig['globalauctionsettings_payperpost']) ? ", status = 'frozen' " : " ";
						// update auction with insertion fee
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET insertionfee = '" . sprintf("%01.2f", $fee) . "',
							isifpaid = '0',
							ifinvoiceid = '" . intval($invoiceid) . "'
							$status
							WHERE project_id = '" . intval($pid) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
		// #### process bulk upload fee transaction ####################
		else
		{
			// #### try to debit the account of this user
			if ($amount > 0)
			{
				// #### taxes on insertion fees ################
				$extrainvoicesql = '';
				$amountnotax = $amount;
				if ($ilance->tax->is_taxable(intval($userid), 'insertionfee'))
				{
					// #### fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($userid), $amount, 'insertionfee', 0);
					// #### fetch total amount to hold within the "totalamount" field
					$totalamount = ($amount + $taxamount);
					// #### fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($userid), $amount, 'insertionfee', 1);
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					// ensure the fee we use below contains the taxes added also
					$amount = $totalamount;
				}
				// #### does owner have sufficient funds?
				$sqlaccount = $ilance->db->query("
					SELECT available_balance, autopayment
					FROM " . DB_PREFIX . "users
					WHERE user_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sqlaccount) > 0)
				{
					$resaccount = $ilance->db->fetch_array($sqlaccount, DB_ASSOC);
					$comments = '';
					if ($resaccount['available_balance'] >= $amount AND $resaccount['autopayment'])
					{
						$invoiceid = $ilance->accounting->insert_transaction(
							0, 0, 0, intval($userid), 0, 0, 0, '{_bulk_upload_fee} - {_insertion_fee}', sprintf("%01.2f", $amountnotax), sprintf("%01.2f", $amount), 'paid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 1
						);
						// update invoice mark as insertion fee invoice type
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET isif = '1',
							$extrainvoicesql
							isautopayment = '1'
							WHERE invoiceid = '" . intval($invoiceid) . "'
						", 0, null, __FILE__, __LINE__);
						if (isset($pids) AND is_array($pids) AND count($pids) > 0)
						{
							foreach ($pids AS $pid)
							{
								if (isset($pid) AND $pid > 0)
								{
									$amountsplit = ($amount / count($pids));
									$comments .= "<div style=\"background-color:#cccccc;height:1px;width:100%;margin-top:12px;margin-bottom:12px\"></div><div style=\"padding-top:3px\" class=\"blue\">" . '{_item}' . " # $pid: <a href=\"" . HTTP_SERVER . $ilpage['merch'] . "?id=" . $pid . "\">" . fetch_auction('project_title', $pid) . "</a> (" . $ilance->currency->format($amountsplit) . ")</div>";
									// update auction with insertion fee
									// set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET insertionfee = '" . sprintf("%01.2f", $amountsplit) . "',
										isifpaid = '1',
										ifinvoiceid = '" . intval($invoiceid) . "'
										WHERE project_id = '" . intval($pid) . "'
									", 0, null, __FILE__, __LINE__);
								}
							}
						}
						// adjust account balance
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "users
							SET available_balance = available_balance - $amount,
							total_balance = total_balance - $amount
							WHERE user_id = '" . intval($userid) . "'
						", 0, null, __FILE__, __LINE__);
						// track spending habits
						$ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
						// #### REFERRAL SYSTEM TRACKER ############################
						$ilance->referral->update_referral_action('ins', intval($userid));
					}
					else
					{
						$invoiceid = $ilance->accounting->insert_transaction(
							0, 0, 0, intval($userid), 0, 0, 0, '{_bulk_upload_fee} - {_insertion_fee}', sprintf("%01.2f", $amountnotax), '', 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '', 0, 0, 1
						);
						// update invoice mark as insertion fee invoice type
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET $extrainvoicesql
							isif = '1'
							WHERE invoiceid = '" . intval($invoiceid) . "'
						", 0, null, __FILE__, __LINE__);
						$status = ($ilance->permissions->check_access($userid, 'payasgoexempt') == 'no' AND $ilconfig['globalauctionsettings_payperpost']) ? ", status = 'frozen' " : " ";
						if (isset($pids) AND is_array($pids) AND count($pids) > 0)
						{
							foreach ($pids AS $pid)
							{
								if (isset($pid) AND $pid > 0)
								{
									$amountsplit = ($amount / count($pids));
									$comments .= "<div style=\"background-color:#cccccc;height:1px;width:100%;margin-top:12px;margin-bottom:12px\"></div><div style=\"padding-top:3px\" class=\"blue\">" . '{_item}' . " # $pid: <a href=\"" . HTTP_SERVER . $ilpage['merch'] . "?id=" . $pid . "\">" . fetch_auction('project_title', $pid) . "</a> (" . $ilance->currency->format($amountsplit) . ")</div>";
									// update auction with insertion fee
									// set insertion fee invoice flag as paid in full so this project doesn't show in the pending queue
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET insertionfee = '" . sprintf("%01.2f", $amountsplit) . "',
										isifpaid = '0',
										ifinvoiceid = '" . intval($invoiceid) . "'
										$status
										WHERE project_id = '" . intval($pid) . "'
									", 0, null, __FILE__, __LINE__);
								}
							}
						}
					}
					// #### update transaction showing split payment details in comment area
					if (!empty($comments))
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET custommessage = '" . $ilance->db->escape_string($comments) . "'
							WHERE invoiceid = '" . intval($invoiceid) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
	}

	function process_listing_bulk_enhancements_transaction($pids = array (), $sumfees = 0, $userid = 0)
	{
		global $ilance, $ilconfig;
		if ($sumfees > 0 AND $userid > 0)
		{
			// does owner have sufficient funds?
			$sql = $ilance->db->query("
				SELECT available_balance, total_balance, autopayment
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
			    ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$resaccount = $ilance->db->fetch_array($sql, DB_ASSOC);
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $sumfees) . "',";
				$sumfeesnotax = $sumfees;
				if ($ilance->tax->is_taxable($userid, 'enhancements') AND $sumfees > 0)
				{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 0);
					// fetch total amount to hold within the "totalamount" field
					$totalamount = ($sumfees + $taxamount);
					// fetch tax bit to display when outputing tax infos
					$taxinfo = $ilance->tax->fetch_amount($userid, $sumfees, 'enhancements', 1);
					// member is taxable for this invoice type
					$extrainvoicesql = "
					    istaxable = '1',
					    totalamount = '" . sprintf("%01.2f", $totalamount) . "',
					    taxamount = '" . sprintf("%01.2f", $taxamount) . "',
					    taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					$sumfees = $totalamount;
				}
				$transactionid = $ilance->accounting_payment->construct_transaction_id();
				// #### CREATE PAID TRANSACTION ################
				if ($resaccount['available_balance'] >= $sumfees AND $resaccount['autopayment'])
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, 0, 0, intval($userid), 0, 0, 0, '{_bulk_upload_fee} - {_auction_enhancement_fees}', sprintf("%01.2f", $sumfeesnotax), sprintf("%01.2f", $sumfees), 'paid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, DATETIME24H, '', 0, 0, 1, $transactionid, 0, 0
					);
					// debit funds from online account balance
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET available_balance = available_balance - " . sprintf("%01.2f", $sumfees) . ",
						total_balance = total_balance - " . sprintf("%01.2f", $sumfees) . "
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1',
						isautopayment = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					// update auction with enhancements fee
					// set enhancements fee invoice flag as paid in full so this project doesn't show in the pending queue
					if (isset($pids) AND is_array($pids) AND count($pids) > 0)
					{
						foreach ($pids AS $pid)
						{
							if (isset($pid) AND $pid > 0)
							{
								$amountsplit = ($sumfees / count($pids));
								// update auction with enhancements fee
								// set enhancements fee invoice flag as paid in full so this project doesn't show in the pending queue
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $amountsplit) . ",
									isenhancementfeepaid = '1',
									enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
									WHERE project_id = '" . intval($pid) . "'
								", 0, null, __FILE__, __LINE__);
							}
						}
					}
					// track spending habits
					$ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $sumfees), 'credit');
					// track this user as paid for enhancements so his/her referral can see this from their my cp
					$ilance->referral->update_referral_action('enhancements', intval($userid));
				}
				// #### CREATE UNPAID TRANSACTION ##############
				else
				{
					$invoiceid = $ilance->accounting->insert_transaction(
						0, 0, 0, intval($userid), 0, 0, 0, '{_bulk_upload_fee} - {_auction_enhancement_fees}', sprintf("%01.2f", $sumfeesnotax), 0, 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '', 0, 0, 1, $transactionid, 0, 0
					);
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "invoices
						SET $extrainvoicesql
						isenhancementfee = '1'
						WHERE invoiceid = '" . intval($invoiceid) . "'
					", 0, null, __FILE__, __LINE__);
					$status = ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'payasgoexempt') == 'no' AND $ilconfig['globalauctionsettings_payperpost']) ? ", status = 'frozen' " : " ";
					if (isset($pids) AND is_array($pids) AND count($pids) > 0)
					{
						foreach ($pids AS $pid)
						{
							if (isset($pid) AND $pid > 0)
							{
								$amountsplit = ($sumfees / count($pids));
								// update auction with enhancements fee
								// set enhancements fee invoice flag as paid in full so this project doesn't show in the pending queue
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET enhancementfee = enhancementfee + " . sprintf("%01.2f", $amountsplit) . ",
									isenhancementfeepaid = '0',
									enhancementfeeinvoiceid = '" . intval($invoiceid) . "'
									$status
									WHERE project_id = '" . intval($pid) . "'
								", 0, null, __FILE__, __LINE__);
							}
						}
					}
				}
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