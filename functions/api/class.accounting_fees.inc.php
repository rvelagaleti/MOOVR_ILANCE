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
\*========================================================================== */
if (!class_exists('accounting'))
{
    exit;
}

/**
* Function to handle accounting fee logic
*
* @package      iLance\Accounting\Fees
* @version      4.0.0.8059
* @author       ILance
*/
class accounting_fees extends accounting
{
	/**
	* Functions for creating a final value fee based on a bid id, category id and project id.
	* 
	* This function will take the bid id, cat id and project id to determine the final value
	* fee to charge (or refund) to a user based on an awarded auction within this particular category.
	* Note: The buyer has ability to unaward this awarded bid, if this happens the service provider or
	* merchant still owes the final value fee even if the project is unawarded.  Note 2: This function
	* will now check to see if funds exist in online account and will auto-debit the fvf if possible.
	*
	* @param       integer          bid id
	* @param       integer          category id
	* @param       integer          project id
	* @param       string           final value fee creation mode (charge or refund)
	* @param       string           category type (service or product)
	*
	* @return      bool             Returns true or false based on the creation of the final value fee or refund
	*/
	function construct_final_value_fee($bidid = 0, $cid = 0, $pid = 0, $mode = '', $cattype = '', $bidgrouping = true)
	{
		global $ilance, $ilconfig, $phrase;
		if ($bidgrouping OR $cattype == 'product')
		{
			$field = 'bid_id';
			$table = 'project_bids';
		}
		else
		{
			$field = 'id';
			$table = 'project_realtimebids';
		}
		$tiers = $price = $total = $remaining = $fvf = 0;
	
		($apihook = $ilance->api('construct_final_value_fee_start')) ? eval($apihook) : false;
	
		// fetch awarded bid amount
		$project = $ilance->db->query("
				SELECT bid_id, user_id, project_id, project_user_id, proposal, bidamount, qty, estimate_days, date_added, date_updated, date_awarded, bidstatus, bidstate, bidamounttype, bidcustom, fvf, state, isproxybid, isshortlisted, winnermarkedaspaidmethod, winnermarkedaspaiddate, winnermarkedaspaid, buyerpaymethod, sellermarkedasshipped, sellermarkedasshippeddate, buyershipperid, buyershipcost
				FROM " . DB_PREFIX . $table . "
				WHERE project_id = '" . intval($pid) . "'
				    AND " . $field . " = '" . intval($bidid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($project) > 0)
		{
			$resproject = $ilance->db->fetch_array($project, DB_ASSOC);
			if ($resproject['bidamounttype'] == 'entire' OR $resproject['bidamounttype'] == 'item' OR $resproject['bidamounttype'] == 'lot' OR $resproject['bidamounttype'] == 'weight')
			{
				if ($resproject['qty'] <= 0)
				{
					$resproject['qty'] = 1;
				}
				$bidamount = ($resproject['bidamount'] * $resproject['qty']);
			}
			else
			{
				if ($resproject['estimate_days'] <= 0)
				{
					$resproject['estimate_days'] = 1;
				}
				$bidamount = ($resproject['bidamount'] * $resproject['estimate_days']);
			}
			$project_currency = fetch_auction('currencyid', $pid);
			if ($ilconfig['globalserverlocale_currencyselector'] AND intval($project_currency) != intval($ilconfig['globalserverlocale_defaultcurrency']))
			{
				$bidamount = convert_currency($ilconfig['globalserverlocale_defaultcurrency'], $bidamount, $project_currency);
			}
			// #### fvf commission logic : who gets charged? #######
			$bidderid = ($cattype == 'product') ? $resproject['project_user_id'] : $resproject['user_id'];
	    
			// #### ARE WE USING FIXED CATEGORY FEES? ##############
			// first check if admin uses fixed fees in this category
			// admin defines fixed fees within AdminCP > Distribution > Categories > (edit mode)
			if ($ilance->categories->usefixedfees($cid) AND !empty($resproject['bidamounttype']))
			{
				// #### let's output our fixed commission amount
				$fvf = $ilance->categories->fixedfeeamount($cid);
			}
			// #### NO FIXED CATEGORIES FEES > CHECK FINAL VALUE GROUP #############
			else
			{
				// fetch final value group for this category
				// we are at this point because the admin has not defined fixed fees
				$categories = $ilance->db->query("
				    SELECT finalvaluegroup
				    FROM " . DB_PREFIX . "categories
				    WHERE cid = '" . intval($cid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($categories) > 0)
				{
					$cats = $ilance->db->fetch_array($categories, DB_ASSOC);
					if (!empty($cats['finalvaluegroup']))
					{
						$fvfgroupname = $cats['finalvaluegroup'];
						$forcefvfgroupid = $ilance->permissions->check_access($bidderid, "{$cattype}fvfgroup");
						if ($forcefvfgroupid > 0)
						{
							$fvfgroupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($forcefvfgroupid) . "'", "groupname");
						}
						$finalvalues = $ilance->db->query("
							SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
							FROM " . DB_PREFIX . "finalvalue
							WHERE groupname = '" . $ilance->db->escape_string($fvfgroupname) . "'
								AND state = '" . $ilance->db->escape_string($cattype) . "'
							ORDER BY finalvalue_from ASC
						", 0, null, __FILE__, __LINE__);
						$totaltiers = (int) $ilance->db->num_rows($finalvalues);
						if ($totaltiers == 1)
						{
							// #### SINGLE FVF TIER LOGIC ##############################
							$fees = $ilance->db->fetch_array($finalvalues, DB_ASSOC);
							if ($bidamount >= $fees['finalvalue_from'])
							{
								if ($fees['amountfixed'] > 0)
								{
									$fvf += $fees['amountfixed'];
									$fv = $fees['amountfixed'];
								}
								else
								{
									$fvf += ($bidamount * $fees['amountpercent'] / 100);
									$fv = ($bidamount * $fees['amountpercent'] / 100);
								}
							}
						}
						else
						{
							// #### MULTIPLE FVF TIER LOGIC ############################
							if ($totaltiers > 0)
							{
								while ($fees = $ilance->db->fetch_array($finalvalues, DB_ASSOC))
								{
									$tiers++;
									if ($fees['finalvalue_to'] != '-1')
									{
										if ($bidamount >= $fees['finalvalue_from'] AND $bidamount <= $fees['finalvalue_to'])
										{
											$bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
											if ($tiers == 1)
											{
												if ($fees['amountfixed'] > 0)
												{
													// fixed
													$fvf += $fees['amountfixed'];
													$fv = $fees['amountfixed'];
												}
												else
												{
													// percentage
													$fvf += ($bidamount * $fees['amountpercent'] / 100);
													$fv = ($bidamount * $fees['amountpercent'] / 100);
												}
											}
											else
											{
												if ($fees['amountfixed'] > 0)
												{
													// fixed
													$fvf += $fees['amountfixed'];
													$fv = $fees['amountfixed'];
												}
												else
												{
													// percent
													$fvf += ($remaining * $fees['amountpercent'] / 100);
													$fv = ($remaining * $fees['amountpercent'] / 100);
												}
											}
											break;
										}
										else
										{
											// the fees must go on! .-)
											if ($fees['amountfixed'] > 0)
											{
												// fixed
												$fvf += $fees['amountfixed'];
												$fv = $fees['amountfixed'];
											}
											else
											{
												// percent
												$fvf += (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
												$fv = (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
											}
											// calculate remaining bid amount for next tier
											$bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
											$remaining = ($bid - $fees['finalvalue_from']);
										}
									}
									else
									{
										// ie: 1000.01 to -1 denotes 1000.01 - (and above)
										if ($bidamount >= $fees['finalvalue_from'])
										{
											if ($fees['amountfixed'] > 0)
											{
												$fvf += $fees['amountfixed'];
												$fv = $fees['amountfixed'];
											}
											else
											{
												$fvf += ($remaining * $fees['amountpercent'] / 100);
												$fv = ($remaining * $fees['amountpercent'] / 100);
											}
											// calculate remaining bid amount for next tier
											$bid = ($bidamount - $fees['finalvalue_from']);
											$remaining = ($bid - $fees['finalvalue_from']);
										}
									}
								}
							}
						}
					}
				}
			}
			// check if we're exempt from final value fees
			if (!empty($bidderid) AND $bidderid > 0 AND $ilance->permissions->check_access($bidderid, 'fvfexempt') == 'yes')
			{
				$fvf = 0;
	    
				($apihook = $ilance->api('construct_final_value_fee_exempt_condition')) ? eval($apihook) : false;
			}
			if ($fvf > 0)
			{
				($apihook = $ilance->api('construct_final_value_fee_end')) ? eval($apihook) : false;
		
				// #### taxes on final value fees ##############
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $fvf) . "',";
				$fvfnotax = $fvf;
				if ($ilance->tax->is_taxable(intval($bidderid), 'finalvaluefee'))
				{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount(intval($bidderid), $fvf, 'finalvaluefee', 0);
					// fetch total amount to hold within the "totalamount" field
					$totalamount = ($fvf + $taxamount);
					// fetch tax bit to display when we display tax infos
					$taxinfo = $ilance->tax->fetch_amount(intval($bidderid), $fvf, 'finalvaluefee', 1);
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					// ensure our new tax is applied to the current fvf..
					$fvf = $totalamount;
				}
				// #### CHARGE FVF LOGIC #######################
				if ($mode == 'charge')
				{
					// do we have funds in online account?
					$account = $ilance->db->query("
					    SELECT available_balance, total_balance, autopayment
					    FROM " . DB_PREFIX . "users
					    WHERE user_id = '" . intval($bidderid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($account) > 0)
					{
						$res = $ilance->db->fetch_array($account, DB_ASSOC);
						$avail = $res['available_balance'];
						$total = $res['total_balance'];
						if ($avail >= $fvf AND $res['autopayment'])
						{
							// create a paid final value fee
							$invoiceid = $this->insert_transaction(
								0, intval($pid), 0, intval($bidderid), 0, 0, 0, '{_final_value_fee_for_auction}' . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $fvfnotax), sprintf("%01.2f", $fvf), 'paid', 'debit', 'account', DATETIME24H, DATETIME24H, DATETIME24H, '{_auto_debit_from_online_account_balance}', 0, 0, 1
							);
							// update invoice mark as final value fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET
								$extrainvoicesql
								isfvf = '1'
								WHERE invoiceid = '" . intval($invoiceid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// update final value fee field in bid table & project table for awarded amount
							$ilance->db->query("
								UPDATE " . DB_PREFIX . $table . "
								SET fvf = '" . sprintf("%01.2f", $fvf) . "'
								WHERE " . $field . " = '" . intval($bidid) . "'
									AND project_id = '" . intval($pid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($cattype == 'service')
							{
								if ($bidgrouping == false)
								{
									$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $resproject['bid_id'] . "'", "date_added");
									if ($date == $resproject['date_added'])
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_bids
											SET fvf = '" . sprintf("%01.2f", $fvf) . "'
											WHERE bid_id = '" . $resproject['bid_id'] . "'
												AND project_id = '" . intval($pid) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
								}
								else
								{
									$pr_id = $ilance->db->fetch_field(DB_PREFIX . "project_realtimebids", "date_added = '" . $resproject['date_added'] . "' AND project_id = '" . intval($pid) . "'", "id");
									if (is_numeric($pr_id))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_realtimebids
											SET fvf = '" . sprintf("%01.2f", $fvf) . "'
											WHERE id = '" . $pr_id . "'
												AND project_id = '" . intval($pid) . "'
											LIMIT 1
									    ", 0, null, __FILE__, __LINE__);
									}
								}
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET fvf = '" . sprintf("%01.2f", $fvf) . "',
								isfvfpaid = '1',
								fvfinvoiceid = '" . intval($invoiceid) . "'
								WHERE project_id = '" . intval($pid) . "'
							", 0, null, __FILE__, __LINE__);
							// update account balance
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = available_balance - " . sprintf("%01.2f", $fvf) . ",
								total_balance = total_balance - " . sprintf("%01.2f", $fvf) . "
								WHERE user_id = '" . intval($bidderid) . "'
							", 0, null, __FILE__, __LINE__);
							$ilance->accounting_payment->insert_income_spent(intval($bidderid), sprintf("%01.2f", $fvf), 'credit');
							$ilance->referral->update_referral_action('fvf', intval($bidderid));
						}
						else
						{
							// create an unpaid final value fee
							$invoiceid = $this->insert_transaction(
								0, intval($pid), 0, intval($bidderid), 0, 0, 0, '{_final_value_fee_for_auction}' . ' - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $fvfnotax), '', 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '{_please_pay_this_invoice_soon_as_possible}', 0, 0, 1
							);
							// update invoice mark as final value fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET
								$extrainvoicesql
								isfvf = '1'
								WHERE invoiceid = '" . intval($invoiceid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// update final value fee field in bid & project table for awarded amount
							$ilance->db->query("
								UPDATE " . DB_PREFIX . $table . "
								SET fvf = '" . sprintf("%01.2f", $fvf) . "'
								WHERE " . $field . " = '" . intval($bidid) . "'
									AND project_id = '" . intval($pid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($cattype == 'service')
							{
								if ($bidgrouping == false)
								{
									$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $resproject['bid_id'] . "'", "date_added");
									if ($date == $resproject['date_added'])
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_bids
											SET fvf = '" . sprintf("%01.2f", $fvf) . "'
											WHERE bid_id = '" . $resproject['bid_id'] . "'
											    AND project_id = '" . intval($pid) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
								}
								else
								{
									$pr_id = $ilance->db->fetch_field(DB_PREFIX . "project_realtimebids", "date_added = '" . $resproject['date_added'] . "' AND project_id = '" . intval($pid) . "'", "id");
									if (is_numeric($pr_id))
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_realtimebids
											SET fvf = '" . sprintf("%01.2f", $fvf) . "'
											WHERE id = '" . $pr_id . "'
												AND project_id = '" . intval($pid) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
									}
								}
							}
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET fvf = '" . sprintf("%01.2f", $fvf) . "',
								isfvfpaid = '0',
								fvfinvoiceid = '" . intval($invoiceid) . "'
								WHERE project_id = '" . intval($pid) . "'
							", 0, null, __FILE__, __LINE__);
						}
					}
				}
				// #### REFUND FVF LOGIC #######################
				else if ($mode == 'refund')
				{
					// let's refund this final value fee due to an unaward by the buyer
					// find out if the provider paid this fvf or learn if it's still unpaid
					// fetch the most recent fvf for this particular project id
					$maxinvoicesql = $ilance->db->query("
						SELECT MAX(invoiceid) AS maxinvoiceid
						FROM " . DB_PREFIX . "invoices
						WHERE projectid = '" . intval($pid) . "'
							AND user_id = '" . intval($bidderid) . "'
							AND isfvf = '1'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($maxinvoicesql) > 0)
					{
						$maxid = $ilance->db->fetch_array($maxinvoicesql, DB_ASSOC);
						$invsql = $ilance->db->query("
							SELECT invoiceid, status, paid
							FROM " . DB_PREFIX . "invoices
							WHERE projectid = '" . intval($pid) . "'
								AND user_id = '" . intval($bidderid) . "'
								AND isfvf = '1'
								AND invoiceid = '" . $maxid['maxinvoiceid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($invsql) > 0)
						{
							$invres = $ilance->db->fetch_array($invsql, DB_ASSOC);
							// #### UNPAID FVF HANDLER #####################
							if ($invres['status'] == 'unpaid')
							{
								// provider hasn't paid final value fee yet! let's cancel this invoice
								// so they do not see any pending/unpaid invoices for this listing
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "invoices
									SET status = 'cancelled',
									custommessage = '" . $ilance->db->escape_string('{_awarded_bid_was_unawarded_by_the_owner_of_this_project_invoice_cancelled}') . "',
									canceldate = '" . DATETIME24H . "',
									canceluserid = '-1'
									WHERE invoiceid = '" . $invres['invoiceid'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								// reset final value fees for this project
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET fvf = '0.00',
									isfvfpaid = '0',
									fvfinvoiceid = '0'
									WHERE project_id = '" . intval($pid) . "'
								", 0, null, __FILE__, __LINE__);
								// perhaps an email gets dispatched informing the provider
								// that the final value fee has been cancelled
							}
							// #### PAID FVF HANDLER #######################
							else if ($invres['status'] == 'paid')
							{
								// provider already paid the site for the final value fee 
								// so let's refund (credit) this amount and update the providers account balance
								if ($invres['paid'] > 0)
								{
									// create a final value fee credit to the service provider
									$refundinvoiceid = $this->insert_transaction(
										0, intval($pid), 0, intval($bidderid), 0, 0, 0, '{_final_value_fee_refund_credit_for_auction} - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $invres['paid']), sprintf("%01.2f", $invres['paid']), 'paid', 'credit', 'account', DATETIME24H, DATETIME24H, DATETIME24H, '{_auto_credited_to_online_account_balance}', 0, 0, 1
									);
									// track income history
									$ilance->accounting_payment->insert_income_spent($bidderid, sprintf("%01.2f", $invres['paid']), 'debit');
									// re credit the provider online account
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "users
										SET available_balance = available_balance + $invres[paid],
										total_balance = total_balance + $invres[paid]
										WHERE user_id = '" . intval($bidderid) . "'
									", 0, null, __FILE__, __LINE__);
									// we should also update the bid and project table fvf fields back to 0.00
									$ilance->db->query("
										UPDATE " . DB_PREFIX . $table . "
										SET fvf = '0.00'
										WHERE project_id = '" . intval($pid) . "'
											AND user_id = '" . intval($bidderid) . "'
											AND fvf > 0
									", 0, null, __FILE__, __LINE__);
									if ($bidgrouping == false AND $cattype == 'service')
									{
										$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . $resproject['bid_id'] . "'", "date_added");
										if ($date == $resproject['date_added'])
										{
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "project_bids
												SET fvf = '0.00'
												WHERE project_id = '" . intval($pid) . "'
													AND user_id = '" . intval($bidderid) . "'
													AND fvf > 0
											", 0, null, __FILE__, __LINE__);
										}
									}
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET fvf = '0.00',
										isfvfpaid = '0',
										fvfinvoiceid = '0'
										WHERE project_id = '" . intval($pid) . "'
									", 0, null, __FILE__, __LINE__);
									// perhaps an email gets dispatched informing the provider
									// that the final value fee has been refunded
									// additionally, another email to admin advising the loss of FVF funds.
								}
							}
						}
					}
				}
				return 1;
			}
		}
		return 0;
	}
	
	/**
	* Function for creating a final value donation fee based on particular donation setup.  Additionally,
	* this function can be used to charge or refund the seller based on the final value fee donation amount
	* originally generated.
	* 
	* @param       integer          project id
	* @param       integer          winning bid amount or buy now price
	* @param       string           fee creation mode (charge or refund)
	*
	* @return      bool             Returns true or false based on the creation of the final value fee or refund
	*/
	function construct_final_value_donation_fee($pid = 0, $amount = 0, $mode = 'charge')
	{
		global $ilance, $ilconfig, $phrase;
		$fvf = $fvfnotax = 0;
		// fetch awarded bid amount
		$project = $ilance->db->query("
			SELECT user_id, donation, charityid, donationpercentage
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($pid) . "' 
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($project) > 0)
		{
			$resproject = $ilance->db->fetch_array($project, DB_ASSOC);
			if ($resproject['donation'] AND $resproject['charityid'] > 0 AND $resproject['donationpercentage'] > 0)
			{
				$fvf = ($amount * $resproject['donationpercentage'] / 100);
				$fvfnotax = $fvf;
			}
			if ($fvf > 0)
			{
				// #### taxes on final value fees ##############
				$extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $fvf) . "',";
				$fvfnotax = $fvf;
				if ($ilance->tax->is_taxable($resproject['user_id'], 'finalvaluefee'))
				{
					// fetch tax amount to charge for this invoice type
					$taxamount = $ilance->tax->fetch_amount($resproject['user_id'], $fvf, 'finalvaluefee', 0);
					// fetch total amount to hold within the "totalamount" field
					$totalamount = ($fvf + $taxamount);
					// fetch tax bit to display when we display tax infos
					$taxinfo = $ilance->tax->fetch_amount($resproject['user_id'], $fvf, 'finalvaluefee', 1);
					// #### extra bit to assign tax logic to the transaction 
					$extrainvoicesql = "
						istaxable = '1',
						totalamount = '" . sprintf("%01.2f", $totalamount) . "',
						taxamount = '" . sprintf("%01.2f", $taxamount) . "',
						taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
					";
					// ensure our new tax is applied to the current fvf..
					$fvf = $totalamount;
				}
				// #### CHARGE FVF LOGIC #######################################
				if ($mode == 'charge')
				{
					// do we have funds in online account?
					$account = $ilance->db->query("
						SELECT available_balance, total_balance, autopayment
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . $resproject['user_id'] . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($account) > 0)
					{
						$res = $ilance->db->fetch_array($account, DB_ASSOC);
						$avail = $res['available_balance'];
						$total = $res['total_balance'];
						// #### suffificent funds to cover transaction
						if ($avail >= $fvf AND $res['autopayment'])
						{
							// #### create a paid final value donation fee
							$invoiceid = $this->insert_transaction(
								0, intval($pid), 0, $resproject['user_id'], 0, 0, 0, '{_final_value_donation_fee} (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $fvfnotax), sprintf("%01.2f", $fvf), 'paid', 'debit', 'account', DATETIME24H, DATETIME24H, DATETIME24H, '{_auto_debit_from_online_account_balance}', 0, 0, 1
							);
							// #### update invoice mark as final value fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET
								$extrainvoicesql
								isdonationfee = '1',
								charityid = '" . $resproject['charityid'] . "',
								isautopayment = '1'
								WHERE invoiceid = '" . intval($invoiceid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// #### update donation details in listing table
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET donermarkedaspaid = '1',
								donermarkedaspaiddate = '" . DATETIME24H . "',
								donationinvoiceid = '" . intval($invoiceid) . "'
								WHERE project_id = '" . intval($pid) . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// #### update account balance
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = available_balance - " . sprintf("%01.2f", $fvf) . ",
								total_balance = total_balance - " . sprintf("%01.2f", $fvf) . "
								WHERE user_id = '" . $resproject['user_id'] . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "charities
								SET donations = donations + 1,
								earnings = earnings + $fvfnotax
								WHERE charityid = '" . $resproject['charityid'] . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// #### track income history
							$ilance->accounting_payment->insert_income_spent($resproject['user_id'], sprintf("%01.2f", $fvf), 'credit');
							// #### referral tracker
							$ilance->referral->update_referral_action('fvf', $resproject['user_id']);
						}
						// #### insufficient funds to cover transaction
						else
						{
							$invoiceid = $this->insert_transaction(
								0, intval($pid), 0, $resproject['user_id'], 0, 0, 0, '{_final_value_donation_fee} (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $fvfnotax), '', 'unpaid', 'debit', 'account', DATETIME24H, DATEINVOICEDUE, '', '{_please_pay_this_invoice_soon_as_possible}', 0, 0, 1
							);
							// update invoice mark as final value donation fee invoice type
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET isdonationfee = '1',
								charityid = '" . $resproject['charityid'] . "'
								WHERE invoiceid = '" . intval($invoiceid) . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET donermarkedaspaid = '0',
								donationinvoiceid = '" . intval($invoiceid) . "'
								WHERE project_id = '" . intval($pid) . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
						}
					}
				}
				else if ($mode == 'refund')
				{
					// do we have funds in online account?
					$sql = $ilance->db->query("
						SELECT donationinvoiceid, donermarkedaspaid
						FROM " . DB_PREFIX . "projects
						WHERE project_id = '" . intval($pid) . "'
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						// #### reset listing table
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET donermarkedaspaid = '0',
							donermarkedaspaiddate = '0000-00-00 00:00:00',
							donationinvoiceid = '0'
							WHERE project_id = '" . intval($pid) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// #### remove old invoice
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "invoices
							WHERE invoiceid = '" . $res['donationinvoiceid'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						// #### refund donation associated invoice
						if ($res['donermarkedaspaid'])
						{
							// #### create a paid final value donation fee refund credit
							$invoiceid = $this->insert_transaction(
								0, intval($pid), 0, $resproject['user_id'], 0, 0, 0, '{_final_value_donation_fee_refund_credit} (' . $resproject['donationpercentage'] . '%) - ' . fetch_auction('project_title', intval($pid)) . ' #' . intval($pid), sprintf("%01.2f", $fvf), sprintf("%01.2f", $fvf), 'paid', 'credit', 'account', DATETIME24H, DATETIME24H, DATETIME24H, '{_auto_credited_to_online_account_balance}', 0, 0, 1
							);
							// #### update account balance
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET available_balance = available_balance + " . sprintf("%01.2f", $fvf) . ",
								total_balance = total_balance + " . sprintf("%01.2f", $fvf) . "
								WHERE user_id = '" . $resproject['user_id'] . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							// #### track income history
							$ilance->accounting_payment->insert_income_spent($resproject['user_id'], sprintf("%01.2f", $fvf), 'debit');
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "charities
								SET donations = donations - 1,
								earnings = earnings - $fvfnotax
								WHERE charityid = '" . $resproject['charityid'] . "' 
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
						}
					}
				}
				return true;
			}
		}
		return false;
	}
    
	/**
	* Function to calculate the final value fee based on an amount, category id, category type and bid amount type
	*
	* @param       string         amount
	* @param       integer        category id
	* @param       string         category type (servicebuyer or serviceprovider)
	* @param       string         bid amount type
	* @param       integer        user id
	*
	* @return      string         Returns formatted final value feee (if applicable)
	*/
	function calculate_final_value_fee($bidamount = 0, $cid = 0, $cattype = '', $bidamounttype = '', $userid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$cid = intval($cid);
		$tiers = $price = $total = $remaining = $fvf = 0;
		$bidamount = $ilance->currency->string_to_number($bidamount);
		if (isset($cattype) AND ($cattype == 'servicebuyer' OR $cattype == 'serviceprovider'))
		{
			$cattype = 'service';
		}
		// first check if admin uses fixed fees in this category
		if ($ilance->categories->usefixedfees($cid) AND !empty($bidamounttype))
		{
			// admin charges a fixed fee within this category to service providers
			// let's determine if the bid amount type logic is configured
			if ($bidamounttype != 'entire' AND $bidamounttype != 'item' AND $bidamounttype != 'lot')
			{
				// bid amount type passes accepted commission types
				// let's output our fixed commission amount
				$fvf = $ilance->categories->fixedfeeamount($cid);
				$fvf = sprintf("%01.2f", $fvf);
				return $fvf;
			}
		}
		else
		{
			// fetch final value group for this category
			$categories = $ilance->db->query("
				SELECT finalvaluegroup
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . $cid . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($categories) > 0)
			{
				$cats = $ilance->db->fetch_array($categories, DB_ASSOC);
				if (!empty($cats['finalvaluegroup']))
				{
					$fvfgroupname = $cats['finalvaluegroup'];
					$forcefvfgroupid = $ilance->permissions->check_access($userid, "{$cattype}fvfgroup");
					if ($forcefvfgroupid > 0)
					{
						$fvfgroupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($forcefvfgroupid) . "'", "groupname");
					}
					$finalvalues = $ilance->db->query("
						SELECT tierid, groupname, finalvalue_from, finalvalue_to, amountfixed, amountpercent, state, sort
						FROM " . DB_PREFIX . "finalvalue
						WHERE groupname = '" . $ilance->db->escape_string(trim($fvfgroupname)) . "'
							AND state = '" . $ilance->db->escape_string($cattype) . "'
						ORDER BY finalvalue_from ASC
					", 0, null, __FILE__, __LINE__);
					$totaltiers = (int) $ilance->db->num_rows($finalvalues);
					if ($totaltiers == 1)
					{
						// #### SINGLE FVF TIER LOGIC ##########
						$fees = $ilance->db->fetch_array($finalvalues, DB_ASSOC);
						if ($bidamount >= $fees['finalvalue_from'])
						{
							if ($fees['amountfixed'] > 0)
							{
								$fvf += $fees['amountfixed'];
								$fv = $fees['amountfixed'];
							}
							else
							{
								$fvf += ($bidamount * $fees['amountpercent'] / 100);
								$fv = ($bidamount * $fees['amountpercent'] / 100);
							}
						}
						if (isset($fvf))
						{
							$fvf = sprintf("%01.2f", $fvf);
							if ($userid > 0 AND $ilance->permissions->check_access($userid, 'fvfexempt') == 'yes')
							{
								$fvf = 0;
							}
							return $fvf;
						}
					}
					else
					{
						// #### MULTIPLE FVF TIER LOGIC ########
						if ($totaltiers > 0)
						{
							while ($fees = $ilance->db->fetch_array($finalvalues, DB_ASSOC))
							{
								$tiers++;
								if ($fees['finalvalue_to'] != '-1')
								{
									if ($bidamount >= $fees['finalvalue_from'] AND $bidamount <= $fees['finalvalue_to'])
									{
										$bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
										if ($tiers == 1)
										{
											if ($fees['amountfixed'] > 0)
											{
												$fvf += $fees['amountfixed'];
												$fv = $fees['amountfixed'];
											}
											else
											{
												$fvf += ($bidamount * $fees['amountpercent'] / 100);
												$fv = ($bidamount * $fees['amountpercent'] / 100);
											}
										}
										else
										{
											if ($fees['amountfixed'] > 0)
											{
												$fvf += $fees['amountfixed'];
												$fv = $fees['amountfixed'];
											}
											else
											{
												$fvf += ($remaining * $fees['amountpercent'] / 100);
												$fv = ($remaining * $fees['amountpercent'] / 100);
											}
										}
										break;
									}
									else
									{
										if ($fees['amountfixed'] > 0)
										{
											$fvf += $fees['amountfixed'];
											$fv = $fees['amountfixed'];
										}
										else
										{
											$fvf += (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
											$fv = (($fees['finalvalue_to'] - $fees['finalvalue_from']) * $fees['amountpercent'] / 100);
										}
										$bid = ($bidamount - ($fees['finalvalue_to'] - $fees['finalvalue_from']));
										$remaining = ($bid - $fees['finalvalue_from']);
									}
								}
								else
								{
									if ($bidamount >= $fees['finalvalue_from'])
									{
										if ($fees['amountfixed'] > 0)
										{
											$fvf += $fees['amountfixed'];
											$fv = $fees['amountfixed'];
										}
										else
										{
											$fvf += ($remaining * $fees['amountpercent'] / 100);
											$fv = ($remaining * $fees['amountpercent'] / 100);
										}
									}
								}
							}
							if (isset($fvf))
							{
								$fvf = sprintf("%01.2f", $fvf);
								if ($userid > 0 AND $ilance->permissions->check_access($userid, 'fvfexempt') == 'yes')
								{
									$fvf = 0;
								}
								return $fvf;
							}
						}
					}
				}
			}
		}
		return 0;
	}
    
	/**
	* Function for creating a new insertion fee transaction which is usually executed during the initial posting
	* of a service or product auction.  This function will attempt to debit the amount owing from the user's
	* account balance (if funds available) otherwise it will create an unpaid transaction and force the auction to be
	* hidden until payment is completed.  This function takes into consideration a user with insertion fees exemption.
	*
	* @param       integer      category id
	* @param       string       category type (service or product) default is service
	* @param       string       amount to process
	* @param       integer      project id
	* @param       integer      user id
	* @param       boolean      is a budget range type insertion group (true or false)
	* @param       integer      budget range id that is selected
	* @param       boolean      include applicable taxes for this user
	*/
	function calculate_insertion_fee($cid = 0, $cattype = 'service', $amount = 0, $pid = 0, $userid = 0, $isbudgetrange = 0, $filtered_budgetid = 0, $applytax = true)
	{
		global $ilance, $phrase;
		$fee = 0;
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
					}
				}
			}
			unset($insertiongr);
		}
		// check if we're exempt from insertion fees
		if ($userid > 0 AND $ilance->permissions->check_access($userid, 'insexempt') == 'yes')
		{
			$fee = 0;
		}
		else
		{
			// #### taxes on insertion fees ################
			$feenotax = $fee;
			if ($userid > 0 AND $ilance->tax->is_taxable($userid, 'insertionfee') AND $applytax)
			{
				// #### fetch tax amount to charge for this invoice type
				$taxamount = $ilance->tax->fetch_amount($userid, $fee, 'insertionfee', 0);
				// #### fetch total amount to hold within the "totalamount" field
				$totalamount = ($fee + $taxamount);
				// ensure the fee we use below contains the taxes added also
				$fee = $totalamount;
			}
		}
		return $fee;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>