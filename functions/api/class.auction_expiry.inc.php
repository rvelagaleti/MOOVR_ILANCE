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
* Class to perform the majority of functions dealing with anything to do with listings expiry within ILance.
*
* @package      iLance\Auction\Expiry
* @version      4.0.0.8059
* @author       ILance
*/
class auction_expiry extends auction
{
        function all()
        {
                $cronlog = $this->listings();
                $cronlog .= $this->other();
                return $cronlog;
        }
        
        /**
        * Function to handle the logic for updating 'expired' listings to 'finished' such as projects, jobs, escrow, etc.
        *
        * @return      string        Returns a string based on actions and events that occurred (for logging to cron log in the database)
        */
	function listings_expired_to_finished()
        {
                global $ilance, $ilconfig, $show;
                $cronlog = $customquery = '';
		
		($apihook = $ilance->api('listings_expired_to_finished_start')) ? eval($apihook) : false;
		
                // #### escrow enabled #########################################
                if ($ilconfig['escrowsystem_enabled'])
                {
                        // #### product ########################################
                        $sql_items = $ilance->db->query("
                                SELECT filter_escrow, project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_state = 'product'
					AND status = 'expired'
					$customquery
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_items) > 0)
                        {
                                while ($res_items = $ilance->db->fetch_array($sql_items, DB_ASSOC))
                                {
                                        if ($res_items['filter_escrow'] == '1')
                                        {
                                                // is this escrow finished?
                                                $sql_esc = $ilance->db->query("
                                                        SELECT project_id
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE date_paid != '0000-00-00 00:00:00'
								AND status = 'finished'
								AND project_id = '" . $res_items['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_esc) > 0)
                                                {
                                                        while ($res_esc = $ilance->db->fetch_array($sql_esc, DB_ASSOC))
                                                        {
                                                                // is feedback process finished?
                                                                if ($ilance->feedback->is_feedback_complete($res_esc['project_id']))
                                                                {
                                                                        // update listing as finished
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET status = 'finished'
                                                                                WHERE project_id = '".$res_esc['project_id']."'
                                                                                    AND project_state = 'product'
                                                                                    AND (status != 'archived' OR status != 'delisted')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $cronlog .= '';
                                                                }                                                
                                                        }
                                                }		
                                        }
                                }
                                unset($res_items);
                        }
                        // #### service ########################################
                        $sql_proj = $ilance->db->query("
                                SELECT filter_escrow, project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_state = 'service'
					AND status = 'expired'
					$customquery
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_proj) > 0)
                        {
                                while ($res_projs = $ilance->db->fetch_array($sql_proj, DB_ASSOC))
                                {
                                        if ($res_projs['filter_escrow'] == '1')
                                        {
                                                // is this escrow finished?
                                                $sql_projesc = $ilance->db->query("
                                                        SELECT project_id
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE date_paid != '0000-00-00 00:00:00'
                                                            AND status = 'finished'
                                                            AND project_id = '" . $res_projs['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_projesc) > 0)
                                                {
                                                        while ($res_projesc = $ilance->db->fetch_array($sql_projesc, DB_ASSOC))
                                                        {
                                                                // is feedback process finished?
                                                                if ($ilance->feedback->is_feedback_complete($res_projesc['project_id']))
                                                                {
                                                                        // update auction as finished
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "projects
                                                                                SET status = 'finished'
                                                                                WHERE project_id = '" . $res_projesc['project_id'] . "'
											AND project_state = 'service'
											AND (status != 'archived' OR status != 'delisted')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        
                                                                        $cronlog .= '';
                                                                }
                                                        }
                                                        unset($res_projesc);
                                                }
                                        }
                                }
                                unset($res_projs);
                        }
                }
		// #### escrow disabled ########################################
                else
                {
                        // escrows disabled: check for expired item listings so we can learn
                        // what is going on with the feedback between both members
                        $sql_items = $ilance->db->query("
                                SELECT project_id 
                                FROM " . DB_PREFIX . "projects
                                WHERE status = 'expired'
					AND project_state = 'product'
					$customquery
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_items) > 0)
                        {
                                while ($res_items = $ilance->db->fetch_array($sql_items, DB_ASSOC))
                                {
                                        // is feedback complete?
                                        if ($ilance->feedback->is_feedback_complete($res_items['project_id']))
                                        {
                                                // set listing to finished
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET status = 'finished'
                                                        WHERE project_id = '" . $res_items['project_id'] . "'
								AND project_state = 'product'
								AND (status != 'archived' OR status != 'delisted')
                                                ", 0, null, __FILE__, __LINE__);
                                                $cronlog .= '';
                                        }
                                }
                                unset($res_items);
                        }
                        // expired job listings checkup
                        $sql_proj = $ilance->db->query("
                                SELECT project_id
                                FROM " . DB_PREFIX . "projects
                                WHERE status = 'expired'
                                        AND project_state = 'service'
					$customquery
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_proj) > 0)
                        {
                                while ($res_projs = $ilance->db->fetch_array($sql_proj, DB_ASSOC))
                                {
                                        // is feedback complete?
                                        if ($ilance->feedback->is_feedback_complete($res_projs['project_id']))
                                        {
                                                // update job listing as finished
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET status = 'finished'
                                                        WHERE project_id = '" . $res_projs['project_id'] . "'
								AND project_state = 'service'
								AND (status != 'archived' OR status != 'delisted')
                                                ", 0, null, __FILE__, __LINE__);
                                                $cronlog .= '';
                                        }
                                }
                                unset($res_projs);
                        }
                }
                return $cronlog;
        }
        
        /**
        * Function to handle and process the logic from 'open' to 'expired' for all listings and inventory within the marketplace.
        *
        * @return      string        Returns a string based on actions and events that occurred (for logging to cron log in the database)
        */
	function listings()
        {
                global $ilance, $phrase, $ilconfig, $show;
                $cronlog = $customquery = '';
		
		($apihook = $ilance->api('auction_expiry_listings_start')) ? eval($apihook) : false;
                
                $sql_rfp = $ilance->db->query("
                        SELECT p.*, s.ship_method, s.ship_handlingfee, s.ship_handlingtime
                        FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON (p.project_id = s.project_id)
                        WHERE p.date_end <= '" . DATETIME24H . "' 
				AND p.status = 'open'
				$customquery
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_rfp) > 0)
                {
                	$bulk_uploads_array = array();
                        while ($res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC))
                        {
                                // #### SERVICE AUCTION EXPIRY #################
                                // this will only expire auctions that have had no winning bids awarded
                                // and will not expire "finished", "wait_approval" or "approval_accepted" ones
                                if ($res_rfp['project_state'] == 'service')
                                {
					($apihook = $ilance->api('service_auction_expired')) ? eval($apihook) : false;

					// determine if buyer is auto-relisting this job (if no bids were received)					
                                        $canrelist = $this->process_auction_relister($res_rfp['project_id'], true);
                                        if ($canrelist == false)
                                        {
                                                // #### expire this listing
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET status = 'expired'
                                                        WHERE project_id = '" . $res_rfp['project_id'] . "'
								AND gtc = '0'
							LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
						if ($res_rfp['gtc'] == '0')
						{
							$ilance->categories->build_category_count($res_rfp['cid'], 'subtract', "listings(): subtracting increment count cid $res_rfp[cid]");
						}
                                                // #### select all bidders who placed a bid
                                                $sql_bids = $ilance->db->query("
                                                        SELECT user_id
                                                        FROM " . DB_PREFIX . "project_bids
                                                        WHERE project_user_id = '" . $res_rfp['user_id'] . "'
												AND project_id = '" . $res_rfp['project_id'] . "'
                                                        GROUP BY user_id
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_bids) > 0)
                                                {
                                                        while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
                                                        {
								$existing = array(
									'{{project_title}}' => stripslashes($res_rfp['project_title']),
									'{{expiredate}}' => DATETODAY . ' ' . TIMENOW,
									'{{bidder}}' => fetch_user('username', $res_bids['user_id']),
									'{{rfpurl}}' => HTTP_SERVER . 'rfp.php?id=' . $res_rfp['project_id'],
									'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
									'{{bids}}' => $res_rfp['bids']
								);
	
								($apihook = $ilance->api('service_auction_expired_norelist_via_cron_bidders_end')) ? eval($apihook) : false;
	
								$ilance->email->mail = fetch_user('email', $res_bids['user_id']);
								$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
								$ilance->email->get('service_auction_expired_via_cron');		
								$ilance->email->set($existing);
								$ilance->email->send();
                                                        }
                                                }
                                                if ($res_rfp['bulkid'] > 0)
                                                {               
							if (!in_array($res_rfp['bulkid'], $bulk_uploads_array))
							{
								if ($res_rfp['gtc'])
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET date_end = '" . $ilance->datetimes->fetch_datetime_fromnow($ilance->datetimes->calendar_days_this_month()) . "',
										status = 'open',
										close_date = '0000-00-00 00:00:00'
										WHERE project_id = '" . intval($res_rfp['project_id']) . "'
										LIMIT 1
									", 0, null, __FILE__, __LINE__);
								}
								else
								{
									$bulk_uploads_array[] = $res_rfp['bulkid'];
									$jobs_information = '';
									$sql_bulk = $ilance->db->query("
										SELECT p.project_title, p.project_id, p.date_end
										FROM " . DB_PREFIX . "projects p
										WHERE p.bulkid = '" . $res_rfp['bulkid'] . "'
									", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($sql_bulk) > 0)
									{
										while ($res_bulk = $ilance->db->fetch_array($sql_bulk, DB_ASSOC))
										{
											$jobs_information .= "
{_project_title}: " . stripslashes($res_bulk['project_title']) . "
" . HTTP_SERVER . "rfp.php?id=" . $res_bulk['project_id'] . "
{_date_ending}: " . DATETODAY . ' ' . TIMENOW . "\n"; 
										}

										$ilance->template->templateregistry['jobs_information'] = $jobs_information;
										$jobs_information = $ilance->template->parse_template_phrases('jobs_information');
									}
									$existing = array(
										'{{owner}}' => fetch_user('username', $res_rfp['user_id']),
										'{{jobs_information}}' => $jobs_information
									);
									$ilance->email->mail = fetch_user('email', $res_rfp['user_id']);
									$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
									$ilance->email->get('service_auction_bulk_expired_via_cron_owner');		
									$ilance->email->set($existing);
									$ilance->email->send();
								}

							}
                                                }
                                                else 
                                                {
							if ($res_rfp['gtc'])
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET date_end = '" . $ilance->datetimes->fetch_datetime_fromnow($ilance->datetimes->calendar_days_this_month()) . "',
									status = 'open',
									close_date = '0000-00-00 00:00:00'
									WHERE project_id = '" . intval($res_rfp['project_id']) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
							}
							else
							{
								$existing = array(
									'{{project_title}}' => stripslashes($res_rfp['project_title']),
									'{{expiredate}}' => DATETODAY . ' ' . TIMENOW,
									'{{owner}}' => fetch_user('username', $res_rfp['user_id']),
									'{{rfpurl}}' => HTTP_SERVER . "rfp.php?id=" . $res_rfp['project_id'],
									'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
									'{{bids}}' => $res_rfp['bids']
								);
								$ilance->email->mail = fetch_user('email', $res_rfp['user_id']);
								$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
								$ilance->email->get('service_auction_expired_via_cron_owner');		
								$ilance->email->set($existing);
								$ilance->email->send();
							}
                                                }
                                                
                                                ($apihook = $ilance->api('service_auction_expired_via_cron_norelist_owner_end')) ? eval($apihook) : false;
                                                
						$cronlog .= '';
                                        }
                                }
                                // #### PRODUCT AUCTION EXPIRY #################
                                else if ($res_rfp['project_state'] == 'product')
                                {
                                        $sql_owner = $ilance->db->query("
                                                SELECT username, email
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_rfp['user_id'] . "' 
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_owner) > 0)
                                        {
                                                $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                                $canrelist = $this->process_auction_relister($res_rfp['project_id'], true);
						
                                                ($apihook = $ilance->api('product_auction_expired')) ? eval($apihook) : false;
                                                
						// #### determine if seller is auto-relisting this item (if no bids were received)
                                                if ($canrelist == false)
                                                {
                                                        // #### check for high bidders as well and if the reserve price was met
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET status = 'expired'
                                                                WHERE project_id = '" . $res_rfp['project_id'] . "'
									AND gtc = '0'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
							if ($res_rfp['gtc'] == '0')
							{
								$ilance->categories->build_category_count($res_rfp['cid'], 'subtract', "listings(): subtracting increment count cid $res_rfp[cid]");
								$this->remove_watchlist($res_rfp['project_id']);
							}
							// fetch highest bid placed
							$highbid = $ilance->db->query("
								SELECT bidamount, user_id, bid_id, date_added, buyershipcost, buyershipperid
								FROM " . DB_PREFIX . "project_bids
								WHERE project_id = '" . $res_rfp['project_id'] . "'
								ORDER BY bidamount DESC, date_added ASC
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($highbid) > 0)
							{
								$res_highest = $ilance->db->fetch_array($highbid, DB_ASSOC);
								$highestbid = $res_highest['bidamount'];
								$highbidderid = $res_highest['user_id'];
								$highbiddate = $res_highest['date_added'];
								$highbidderbidid = $res_highest['bid_id'];
								$buyershipcost = $buyershipcost_site_currency = $res_highest['buyershipcost'];
								$buyershipperid = $res_highest['buyershipperid'];
								$totalamount = $totalamount_site_currency = ($res_highest['bidamount'] + $res_highest['buyershipcost']);
								$project_currency = $res_rfp['currencyid'];
								$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
								if ($ilconfig['globalserverlocale_currencyselector'] AND $project_currency != $default_currency)
								{
									$totalamount_site_currency = convert_currency($default_currency, $totalamount, $project_currency);
									$buyershipcost_site_currency = convert_currency($default_currency, $buyershipcost, $project_currency);
								}
							}
							else
							{
								$highbidderid = $highbidderbidid = $buyershipcost = $buyershipperid = 0;
								$highestbid = $buyershipcost = '0.00';
								$highbiddate = '0000-00-00 00:00:00';
							}
							// do we have any high bid placed?
							if ($highestbid > 0 AND $highbidderid > 0)
							{
								if ($res_rfp['reserve'])
								{
									// reserve price met
									if ($res_rfp['reserve_price'] <= $highestbid)
									{
										// select all bidders other than our highest bidder so we can send expired listing notifications
										$sql_bids = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "project_bids
											WHERE project_user_id = '" . $res_rfp['user_id'] . "'
												AND project_id = '" . $res_rfp['project_id'] . "'
												AND user_id != '" . $highbidderid . "'
											GROUP BY user_id
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sql_bids) > 0)
										{
											while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
											{
												// update all bidders (except the winning bidder) bids to 'outbid'
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_bids
													SET bidstatus = 'outbid',
													bidstate = 'expired'
													WHERE user_id = '" . $res_bids['user_id'] . "'
														AND project_id = '" . $res_bids['project_id'] . "' 
												", 0, null, __FILE__, __LINE__);
												$sql_bidder = $ilance->db->query("
													SELECT username, email
													FROM " . DB_PREFIX . "users
													WHERE user_id = '" . $res_bids['user_id'] . "'
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql_bidder) > 0)
												{
													$res_bidder = $ilance->db->fetch_array($sql_bidder, DB_ASSOC);
													$existing = array(
														'{{project_title}}' => stripslashes($res_rfp['project_title']),
														'{{bidder}}' => $res_bidder['username'],
														'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
														'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
														'{{lowbiddate}}' => print_date($res_bids['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
														'{{highbiddate}}' => print_date($highbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
														'{{lowbidamount}}' => $ilance->currency->format($res_bids['bidamount'], $res_rfp['currencyid']),
														'{{highbidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
													);
													$ilance->email->mail = $res_bidder['email'];
													$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
													$ilance->email->get('product_auction_expired_another_bidder');		
													$ilance->email->set($existing);
													
													($apihook = $ilance->api('product_auction_expired_another_bidder_reserve_met')) ? eval($apihook) : false;
													
													$ilance->email->send();
												}
												$cronlog .= '';
											}
										}
										// fetch highest bidders information
										$sql_winner = $ilance->db->query("
											SELECT username, email
											FROM " . DB_PREFIX . "users
											WHERE user_id = '" . $highbidderid . "' 
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sql_winner) > 0)
										{
											$res_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
											// fetch owners information
											$sql_owner = $ilance->db->query("
												SELECT username, email
												FROM " . DB_PREFIX . "users
												WHERE user_id = '" . $res_rfp['user_id'] . "' 
												LIMIT 1
											", 0, null, __FILE__, __LINE__);
											if ($ilance->db->num_rows($sql_owner) > 0)
											{
												$res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
												$methodtype = $ilance->payment->print_payment_method_title($res_rfp['project_id']);
												$methodscount = $ilance->payment->print_payment_methods($res_rfp['project_id'], false, true);
												// using escrow payments
												if ($ilconfig['escrowsystem_enabled'] AND $res_rfp['filter_escrow'] == '1' AND $methodscount == 1 AND $methodtype == 'escrow')
												{
													// #### SELLER AND BUYER ESCROW FEES #####################################
													// also applies tax to the fees
													list($feenotax, $tax, $fee) = $ilance->escrow_fee->fetch_merchant_escrow_fee_plus_tax($res_rfp['user_id'], $totalamount_site_currency);
													list($fee2notax, $tax2, $fee2) = $ilance->escrow_fee->fetch_product_bidder_escrow_fee_plus_tax($highbidderid, $totalamount_site_currency);
													// #### create the escrow invoice to be paid by bidder
													$escrow_invoice_id = $ilance->accounting->insert_transaction(
														0,
														intval($res_rfp['project_id']),
														0,
														intval($highbidderid),
														0,
														0,
														0,
														'{_escrow_payment_forward}: ' . stripcslashes($res_rfp['project_title']) . ' #' . $res_rfp['project_id'],
														sprintf("%01.2f", $totalamount_site_currency),
														'',
														'unpaid',
														'escrow',
														'account',
														DATETIME24H,
														DATEINVOICEDUE,
														'',
														'{_additional_shipping_fees}: ' . $ilance->currency->format($buyershipcost, $project_currency),
														0,
														0,
														1
													);
													// create the product escrow account
													$ilance->db->query("
														INSERT INTO " . DB_PREFIX . "projects_escrow
														(escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
														VALUES(
														NULL,
														'" . $highbidderbidid . "',
														'" . $res_rfp['project_id'] . "',
														'" . $escrow_invoice_id . "',
														'" . $res_rfp['user_id'] . "',
														'" . $highbidderid . "',
														'" . DATETIME24H . "',
														'" . sprintf("%01.2f", $highestbid) . "',
														'" . sprintf("%01.2f", $buyershipcost) . "',
														'" . sprintf("%01.2f", $totalamount) . "', 
														'" . sprintf("%01.2f", $fee) . "', 
														'" . sprintf("%01.2f", $fee2) . "',
														'0',
														'0',
														'0',
														'0',
														'pending')
													", 0, null, __FILE__, __LINE__);
													$escrow_id = $ilance->db->insert_id();
													// tie the escrow account to the project
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "projects
														SET escrow_id = '" . $escrow_id . "',
														haswinner = '1',
														winner_user_id = '" . $highbidderid . "'
														WHERE project_id = '" . $res_rfp['project_id'] . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													// #### increase product wins for the user
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productawards = productawards + 1
														WHERE user_id = '" . $highbidderid . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													// #### increase product sold for seller
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productsold = productsold + 1
														WHERE user_id = '" . $res_rfp['user_id'] . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													// #### update winning bidders default pay method to escrow
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_bids
														SET buyerpaymethod = 'escrow',
														winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
														WHERE bid_id = '" . $highbidderbidid . "'
															AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_realtimebids
														SET buyerpaymethod = 'escrow',
														winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
														WHERE bid_id = '" . $highbidderbidid . "'
															AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);
													$existing = array(
														'{{project_title}}' => stripslashes($res_rfp['project_title']),
														'{{project_id}}' => $res_rfp['project_id'],
														'{{owner}}' => $res_owner['username'],
														'{{owneremail}}' => $res_owner['email'],
														'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
														'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
														'{{totalamount}}' => $ilance->currency->format($totalamount, $res_rfp['currencyid']),
														'{{winningbidder}}' => $res_winner['username'],
														'{{winningbidderemail}}' => $res_winner['email'],
														'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
														'{{shippingcost}}' => $ilance->currency->format($buyershipcost, $res_rfp['currencyid']),
														'{{shippingservice}}' => $ilance->shipping->print_shipping_partner($buyershipperid),
														'{{buyerfee}}' => $ilance->currency->format($fee2),
														'{{sellerfee}}' => $ilance->currency->format($fee),
														'{{paymethod}}' => SITE_NAME . ' {_escrow}',
													);
									
													($apihook = $ilance->api('product_auction_expired_reserve_met_escrow_end')) ? eval($apihook) : false;
									
													// #### email owner
													$ilance->email->mail = $res_owner['email'];
													$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
													$ilance->email->get('product_auction_expired_via_cron_owner');		
													$ilance->email->set($existing);
													$ilance->email->send();
													// #### email winning bidder
													$ilance->email->mail = $res_winner['email'];
													$ilance->email->slng = fetch_user_slng($highbidderid);
													$ilance->email->get('product_auction_expired_via_cron_winner');		
													$ilance->email->set($existing);
													$ilance->email->send();
													// #### email admin
													$ilance->email->mail = SITE_EMAIL;
													$ilance->email->slng = fetch_site_slng();
													$ilance->email->get('product_auction_expired_via_cron_admin');		
													$ilance->email->set($existing);
													$ilance->email->send();
													$cronlog .= '';
												}
												// not using escrow
												else
												{
													if ($methodscount == 1)
													{
														// update winning bidders default pay method to only method available by seller..
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_bids
															SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
																AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "project_realtimebids
															SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
															WHERE bid_id = '" . $highbidderbidid . "'
																AND project_id = '" . $res_rfp['project_id'] . "'
														", 0, null, __FILE__, __LINE__);	
													}
													// todo: include fvf to email template..
													// no escrow enabled for this product listing
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "projects
														SET haswinner = '1',
														winner_user_id = '" . $highbidderid . "'
														WHERE project_id = '" . $res_rfp['project_id'] . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													// increase product wins for the user
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productawards = productawards + 1
														WHERE user_id = '" . $highbidderid . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													// increase product sold for seller
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET productsold = productsold + 1
														WHERE user_id = '" . $res_rfp['user_id'] . "' 
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													$res_rfp['ship_method'] = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $res_rfp['project_id'] . "'", "ship_method");
													$shippingservice = $res_rfp['ship_method'] == 'flatrate' ? $ilance->shipping->print_shipping_partner($buyershipperid) : '{_local_pickup_only}';
													$shippingcost = $res_rfp['ship_method'] == 'flatrate' ? $ilance->currency->format($buyershipcost, $res_rfp['currencyid']) : '{_none}';
													$paymethod = $paymethods = '';                    
													$paymethod = (is_serialized($res_rfp['paymethod'])) ? unserialize($res_rfp['paymethod']) : $res_rfp['paymethod'];
													if (is_array($paymethod) AND count($paymethod) > 0)
													{
														foreach ($paymethod AS $key => $value)
														{
															$paymethods .= (empty($paymethods)) ? '{' . $value . '}' : ', {' . $value . '}';
														}
													}
													$paymethod = (is_serialized($res_rfp['paymethodoptions'])) ? unserialize($res_rfp['paymethodoptions']) : $res_rfp['paymethodoptions'];
													if (is_array($paymethod) AND count($paymethod) > 0)
													{
														foreach ($paymethod AS $key => $value)
														{
															$paymethods .= (empty($paymethods)) ? '{_' . $key . '}': ', {_' . $key . '}';
														}
													}
													$paymethod = $paymethods;  
													$existing = array(
														'{{project_title}}' => stripslashes($res_rfp['project_title']),
														'{{owner}}' => stripslashes($res_owner['username']),
														'{{owneremail}}' => $res_owner['email'],
														'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
														'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
														'{{totalamount}}' => $ilance->currency->format($totalamount, $res_rfp['currencyid']),
														'{{winningbidder}}' => $res_winner['username'],
														'{{winningbidderemail}}' => $res_winner['email'],
														'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
														'{{shippingcost}}' => $ilance->currency->format($buyershipcost, $res_rfp['currencyid']),
														'{{shippingservice}}' => $shippingservice,
														'{{paymethod}}' => $paymethod
													);
																					
													($apihook = $ilance->api('product_auction_expired_reserve_met_end')) ? eval($apihook) : false;
									
													// email owner
													$ilance->email->mail = $res_owner['email'];
													$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
													$ilance->email->get('product_auction_expired_via_cron_no_escrow_owner');		
													$ilance->email->set($existing);
													$ilance->email->send();
													// email winning bidder
													$ilance->email->mail = $res_winner['email'];
													$ilance->email->slng = fetch_user_slng($highbidderid);
													$ilance->email->get('product_auction_expired_via_cron_no_escrow_winner');		
													$ilance->email->set($existing);
													$ilance->email->send();
													// email admin
													$ilance->email->mail = SITE_EMAIL;
													$ilance->email->slng = fetch_site_slng();
													$ilance->email->get('product_auction_expired_via_cron_no_escrow_admin');		
													$ilance->email->set($existing);
													$ilance->email->send();
													$cronlog .= '';
												}
												unset($methodtype, $methodscount);
												// update highest bidders bid status to 'awarded' status
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_bids
													SET bidstate = '',
													bidstatus = 'awarded',
													date_awarded = '" . DATETIME24H . "'
													WHERE bid_id = '" . $highbidderbidid . "'
														AND project_id = '" . $res_rfp['project_id'] . "'
												", 0, null, __FILE__, __LINE__);
												// update all of the highest bidder's lower bids to 'outbid' status
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_bids
													SET bidstate = '',
													bidstatus = 'outbid'
													WHERE user_id = '" . $highbidderid . "'
														AND bidstatus != 'awarded'
														AND project_id = '" . $res_rfp['project_id'] . "'
												", 0, null, __FILE__, __LINE__);
												// todo: insert into order table?
												//generate final value fee to the seller and/or final value donation fee to the seller (if applicable)
												$ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['cid'], $res_rfp['project_id'], 'charge', 'product');
												$ilance->accounting_fees->construct_final_value_donation_fee($res_rfp['project_id'], $highestbid, 'charge');
												$cronlog .= '';
											}
										}
									}
									// reserve price not met
									else
									{
										$sql_bids = $ilance->db->query("
											SELECT *
											FROM " . DB_PREFIX . "project_bids
											WHERE project_user_id = '" . $res_rfp['user_id'] . "'
												AND project_id = '" . $res_rfp['project_id'] . "'
											GROUP BY user_id
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sql_bids) > 0)
										{
											while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
											{
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_bids
													SET bidstatus = 'outbid',
													bidstate = 'expired'
													WHERE user_id = '" . $res_bids['user_id'] . "'
													    AND project_id = '" . $res_bids['project_id'] . "'
												", 0, null, __FILE__, __LINE__);
												$existing = array(
													'{{project_title}}' => stripslashes($res_rfp['project_title']),
													'{{bidder}}' => fetch_user('username', $res_bids['user_id']),
													'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
													'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
												);
												$ilance->email->mail = fetch_user('email', $res_bids['user_id']);
												$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
												$ilance->email->get('product_auction_expired_reserve_not_met');		
												$ilance->email->set($existing);
												
												($apihook = $ilance->api('product_auction_expired_another_bidder_reserve_not_met')) ? eval($apihook) : false;
												
												$ilance->email->send();
												$cronlog .= '';
											}
										}
									}
								}
								else
								{
									$sql_bids = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "project_bids
										WHERE project_user_id = '" . $res_rfp['user_id'] . "'
											AND project_id = '" . $res_rfp['project_id'] . "'
											AND user_id != '" . $highbidderid . "'
										GROUP BY user_id
									", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($sql_bids) > 0)
									{
										while ($res_bids = $ilance->db->fetch_array($sql_bids, DB_ASSOC))
										{
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "project_bids
												SET bidstatus = 'outbid',
												bidstate = 'expired'
												WHERE user_id = '" . $res_bids['user_id'] . "'
													AND project_id = '" . $res_bids['project_id'] . "'
											", 0, null, __FILE__, __LINE__);
											$existing = array(
												'{{project_title}}' => stripslashes($res_rfp['project_title']),
												'{{bidder}}' => fetch_user('username', $res_bids['user_id']),
												'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
												'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
												'{{lowbiddate}}' => print_date($res_bids['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
												'{{highbiddate}}' => print_date($highbiddate, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
												'{{lowbidamount}}' => $ilance->currency->format($res_bids['bidamount'], $res_rfp['currencyid']),
												'{{highbidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
												'{{winningbidder}}' => fetch_user('username', $highbidderid),
											);
											$ilance->email->mail = fetch_user('email', $res_bids['user_id']);
											$ilance->email->slng = fetch_user_slng($res_bids['user_id']);
											$ilance->email->get('product_auction_expired_another_bidder');		
											$ilance->email->set($existing);
											
											($apihook = $ilance->api('product_auction_expired_another_bidder_no_reserve')) ? eval($apihook) : false;
											
											$ilance->email->send();
											$cronlog .= '';
										}
									}				
									$sql_winner = $ilance->db->query("
										SELECT username, email
										FROM " . DB_PREFIX . "users
										WHERE user_id = '" . $highbidderid . "'
									", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($sql_winner) > 0)
									{
										$res_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
										$sql_owner = $ilance->db->query("
											SELECT username, email
											FROM " . DB_PREFIX . "users
											WHERE user_id = '" . $res_rfp['user_id'] . "'
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sql_owner) > 0)
										{
											$res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
											$methodtype = $ilance->payment->print_payment_method_title($res_rfp['project_id']);
											$methodscount = $ilance->payment->print_payment_methods($res_rfp['project_id'], false, true);
											// using escrow payments
											if ($ilconfig['escrowsystem_enabled'] AND $res_rfp['filter_escrow'] == '1' AND $methodscount == 1 AND $methodtype == 'escrow')
											{
												// #### buyer and seller escrow fee transactions
												// also applies tax to fees
												list($feenotax, $tax, $fee) = $ilance->escrow_fee->fetch_merchant_escrow_fee_plus_tax($res_rfp['user_id'], $totalamount_site_currency);
												list($fee2notax, $tax2, $fee2) = $ilance->escrow_fee->fetch_product_bidder_escrow_fee_plus_tax($highbidderid, $totalamount_site_currency);
												// #### create escrow transaction
												$escrow_invoice_id = $ilance->accounting->insert_transaction(
													0,
													intval($res_rfp['project_id']),
													0,
													intval($highbidderid),
													0,
													0,
													0,
													'{_escrow_payment_forward}' . ' ' . '{_item_id}' . ' #' . intval($res_rfp['project_id']) . ': ' . $res_rfp['project_title'],
													sprintf("%01.2f", $totalamount_site_currency),
													'',
													'unpaid',
													'escrow',
													'account',
													DATETIME24H,
													DATEINVOICEDUE,
													'',
													'{_additional_shipping_fees}' . ': ' . $ilance->currency->format($buyershipcost, $project_currency),
													0,
													0,
													1
												);
												// create product escrow account
												$ilance->db->query("
													INSERT INTO " . DB_PREFIX . "projects_escrow
													(escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
													VALUES(
													NULL,
													'" . $highbidderbidid . "',
													'" . $res_rfp['project_id'] . "',
													'" . $escrow_invoice_id . "',
													'" . $res_rfp['user_id'] . "',
													'" . $highbidderid . "',
													'" . DATETIME24H . "',
													'" . sprintf("%01.2f", $highestbid) . "',
													'" . sprintf("%01.2f", $buyershipcost) . "',
													'" . sprintf("%01.2f", $totalamount) . "',
													'" . sprintf("%01.2f", $fee) . "',
													'" . sprintf("%01.2f", $fee2) . "',
													'0',
													'0',
													'0',
													'0',
													'pending')
												", 0, null, __FILE__, __LINE__);
												$escrow_id = $ilance->db->insert_id();
												// associate escrow to listing
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "projects
													SET escrow_id = '" . $escrow_id . "',
													haswinner = '1',
													winner_user_id = '" . $highbidderid . "'
													WHERE project_id = '" . $res_rfp['project_id'] . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												// #### increase product wins for the user
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "users
													SET productawards = productawards + 1
													WHERE user_id = '" . $highbidderid . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												// #### increase product sold for seller
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "users
													SET productsold = productsold + 1
													WHERE user_id = '" . $res_rfp['user_id'] . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												// #### update winning bidders default pay method to escrow
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_bids
													SET buyerpaymethod = 'escrow',
													winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
													WHERE bid_id = '" . $highbidderbidid . "'
													    AND project_id = '" . $res_rfp['project_id'] . "'
												", 0, null, __FILE__, __LINE__);
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "project_realtimebids
													SET buyerpaymethod = 'escrow',
													winnermarkedaspaidmethod = '" . $ilance->db->escape_string('{_escrow}') . "'
													WHERE bid_id = '" . $highbidderbidid . "'
													    AND project_id = '" . $res_rfp['project_id'] . "'
												", 0, null, __FILE__, __LINE__);
												$res_rfp['ship_method'] = $ilance->db->fetch_field(DB_PREFIX . "projects_shipping", "project_id = '" . $res_rfp['project_id'] . "'", "ship_method");
												$shippingservice = $res_rfp['ship_method'] == 'flatrate' ? $ilance->shipping->print_shipping_partner($buyershipperid) : '{_local_pickup_only}';
												$shippingcost = $res_rfp['ship_method'] == 'flatrate' ? $ilance->currency->format($buyershipcost, $res_rfp['currencyid']) : '{_none}';
												$existing = array(
													'{{project_title}}' => stripslashes($res_rfp['project_title']),
													'{{owner}}' => $res_owner['username'],
													'{{owneremail}}' => $res_owner['email'],
													'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
													'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
													'{{totalamount}}' => $ilance->currency->format($totalamount, $res_rfp['currencyid']),
													'{{winningbidder}}' => $res_winner['username'],
													'{{winningbidderemail}}' => $res_winner['email'],
													'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
													'{{shippingcost}}' => $ilance->currency->format($buyershipcost, $res_rfp['currencyid']),
													'{{shippingservice}}' => $shippingservice,
													'{{buyerfee}}' => $ilance->currency->format($fee2),
													'{{sellerfee}}' => $ilance->currency->format($fee),
													'{{paymethod}}' => SITE_NAME . ' ' . '{_escrow}',
													'{{project_id}}' => $res_rfp['project_id'],
												);
												
												($apihook = $ilance->api('product_auction_expired_winner_escrow_no_reserve')) ? eval($apihook) : false;
								
												// email owner
												$ilance->email->mail = $res_owner['email'];
												$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
												$ilance->email->get('product_auction_expired_via_cron_owner');		
												$ilance->email->set($existing);
												$ilance->email->send();
												// email winning bidder
												$ilance->email->mail = $res_winner['email'];
												$ilance->email->slng = fetch_user_slng($highbidderid);
												$ilance->email->get('product_auction_expired_via_cron_winner');		
												$ilance->email->set($existing);
												$ilance->email->send();
												// email admin
												$ilance->email->mail = SITE_EMAIL;
												$ilance->email->slng = fetch_site_slng();
												$ilance->email->get('product_auction_expired_via_cron_admin');		
												$ilance->email->set($existing);
												$ilance->email->send();
												$cronlog .= '';
											}
											// not using escrow payments
											else
											{
												if ($methodscount == 1)
												{
													// #### update winning bidders default pay method to only method available by seller..
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_bids
														SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
														WHERE bid_id = '" . $highbidderbidid . "'
															AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "project_realtimebids
														SET buyerpaymethod = '" . $ilance->db->escape_string($methodtype) . "'
														WHERE bid_id = '" . $highbidderbidid . "'
															AND project_id = '" . $res_rfp['project_id'] . "'
													", 0, null, __FILE__, __LINE__);	
												}
												// #### increase product wins for the user
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "projects
													SET haswinner = '1',
													winner_user_id = '" . $highbidderid . "'
													WHERE project_id = '" . $res_rfp['project_id'] . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "users
													SET productawards = productawards + 1
													WHERE user_id = '" . $highbidderid . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												// #### increase product sold for seller
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "users
													SET productsold = productsold + 1
													WHERE user_id = '" . $res_rfp['user_id'] . "' 
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
												$shippingservice = $res_rfp['ship_method'] == 'flatrate' ? $ilance->shipping->print_shipping_partner($buyershipperid) : '{_local_pickup_only}';
												$shippingcost = $res_rfp['ship_method'] == 'flatrate' ? $ilance->currency->format($buyershipcost, $res_rfp['currencyid']) : '{_none}';
												$paymethod = $paymethods = '';                    
												$paymethod = (is_serialized($res_rfp['paymethod'])) ? unserialize($res_rfp['paymethod']) : $res_rfp['paymethod'];
												if (is_array($paymethod) AND count($paymethod) > 0)
												{
													foreach ($paymethod AS $key => $value)
													{
														$paymethods .= (empty($paymethods)) ? '{' . $value . '}' : ', {' . $value . '}';
													}
												}
												$paymethod = (is_serialized($res_rfp['paymethodoptions'])) ? unserialize($res_rfp['paymethodoptions']) : $res_rfp['paymethodoptions'];
												if (is_array($paymethod) AND count($paymethod) > 0)
												{
													foreach ($paymethod AS $key => $value)
													{
														$paymethods .= (empty($paymethods)) ? '{_' . $key . '}' : ', {_' . $key . '}';
													}
												}
												$paymethod = $paymethods;  
												$existing = array(
													'{{project_title}}' => stripslashes($res_rfp['project_title']),
													'{{owner}}' => ucfirst(stripslashes($res_owner['username'])),
													'{{owneremail}}' => $res_owner['email'],
													'{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
													'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
													'{{totalamount}}' => $ilance->currency->format($totalamount, $res_rfp['currencyid']),
													'{{bidamount}}' => $ilance->currency->format($highestbid, $res_rfp['currencyid']),
													'{{shippingcost}}' => $ilance->currency->format($buyershipcost, $res_rfp['currencyid']),
													'{{shippingservice}}' => $shippingservice,
													'{{winningbidder}}' => $res_winner['username'],
													'{{winningbidderemail}}' => $res_winner['email'],
													'{{paymethod}}' => $paymethod
												);
								
												($apihook = $ilance->api('product_auction_expired_winner_no_escrow_no_reserve')) ? eval($apihook) : false;
								
												$ilance->email->mail = $res_owner['email'];
												$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
												$ilance->email->get('product_auction_expired_via_cron_no_escrow_owner');		
												$ilance->email->set($existing);
												$ilance->email->send();
												$ilance->email->mail = $res_winner['email'];
												$ilance->email->slng = fetch_user_slng($highbidderid);
												$ilance->email->get('product_auction_expired_via_cron_no_escrow_winner');		
												$ilance->email->set($existing);
												$ilance->email->send();
												$ilance->email->mail = SITE_EMAIL;
												$ilance->email->slng = fetch_site_slng();
												$ilance->email->get('product_auction_expired_via_cron_no_escrow_admin');		
												$ilance->email->set($existing);
												$ilance->email->send();
												$cronlog .= '';
											}
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "project_bids
												SET bidstate = '',
												bidstatus = 'awarded',
												date_awarded = '" . DATETIME24H . "'
												WHERE bid_id = '" . $highbidderbidid . "'
													AND project_id = '" . $res_rfp['project_id'] . "'
											", 0, null, __FILE__, __LINE__);
											// #### update the highest bidder's low bids to outbid..
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "project_bids
												SET bidstate = '',
												bidstatus = 'outbid'
												WHERE user_id = '" . $highbidderid . "'
													AND bidstatus != 'awarded'
													AND project_id = '" . $res_rfp['project_id'] . "'
											", 0, null, __FILE__, __LINE__);
											// #### generate final value fee to seller and/or final value donation fee to seller (if applicable)
											$ilance->accounting_fees->construct_final_value_fee($highbidderbidid, $res_rfp['cid'], $res_rfp['project_id'], 'charge', 'product');
											$ilance->accounting_fees->construct_final_value_donation_fee($res_rfp['project_id'], $highestbid, 'charge');
											$cronlog .= '';
										}
									}
								}
							}
							else 
							{
								$sql_owner = $ilance->db->query("
									SELECT username, email
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . $res_rfp['user_id'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql_owner) > 0)
								{
									$res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
									if ($res_rfp['bulkid'] > 0)
									{               
										if (!in_array($res_rfp['bulkid'], $bulk_uploads_array))
										{
											if ($res_rfp['gtc'])
											{
												$ilance->db->query("
													UPDATE " . DB_PREFIX . "projects
													SET date_end = '" . $ilance->datetimes->fetch_datetime_fromnow($ilance->datetimes->calendar_days_this_month()) . "',
													status = 'open',
													close_date = '0000-00-00 00:00:00'
													WHERE project_id = '" . intval($res_rfp['project_id']) . "'
													LIMIT 1
												", 0, null, __FILE__, __LINE__);
											}
											else
											{
												$bulk_uploads_array[] = $res_rfp['bulkid'];
												$items = '';
												$sql_bulk = $ilance->db->query("
													SELECT p.project_title, p.project_id, p.date_end
													FROM " . DB_PREFIX . "projects p
													WHERE p.bulkid = '" . $res_rfp['bulkid'] . "'
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sql_bulk) > 0)
												{
													while ($res_bulk = $ilance->db->fetch_array($sql_bulk, DB_ASSOC))
													{
														$items .= "
{_item_title}: " . stripslashes($res_bulk['project_title']) . "
" . HTTP_SERVER . "merch.php?id=" . $res_bulk['project_id'] . "
{_date_ending}: " . print_date($res_bulk['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, true) . "\n"; 
													}
													$ilance->template->templateregistry['items'] = $items;
													$items = $ilance->template->parse_template_phrases('items');
												}
												$existing = array(
													'{{items}}' => $items,
													'{{owner}}' => $res_owner['username'],
												);
												$ilance->email->mail = $res_owner['email'];
												$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
												$ilance->email->get('product_auction_bulk_expired_via_cron_no_bidder_owner');		
												$ilance->email->set($existing);
												$ilance->email->send();
												$ilance->email->mail = SITE_EMAIL;
												$ilance->email->slng = fetch_site_slng();
												$ilance->email->get('product_auction_bulk_expired_via_cron_no_bidder_admin');		
												$ilance->email->set($existing);
												$ilance->email->send();
											}
										}
									}
									else 
									{
										if ($res_rfp['gtc'])
										{
											$ilance->db->query("
												UPDATE " . DB_PREFIX . "projects
												SET date_end = '" . $ilance->datetimes->fetch_datetime_fromnow($ilance->datetimes->calendar_days_this_month()) . "',
												status = 'open',
												close_date = '0000-00-00 00:00:00'
												WHERE project_id = '" . intval($res_rfp['project_id']) . "'
												LIMIT 1
											", 0, null, __FILE__, __LINE__);
										}
										else
										{
											$existing = array(
												'{{project_title}}' => stripslashes($res_rfp['project_title']),
												'{{owner}}' => $res_owner['username'],
											);
											$ilance->email->mail = $res_owner['email'];
											$ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
											$ilance->email->get('product_auction_expired_via_cron_no_bidder_owner');		
											$ilance->email->set($existing);
											$ilance->email->send();
											$ilance->email->mail = SITE_EMAIL;
											$ilance->email->slng = fetch_site_slng();
											$ilance->email->get('product_auction_expired_via_cron_no_bidder_admin');		
											$ilance->email->set($existing);
											$ilance->email->send();
										}
									}
								}
							}
                                                }
                                        }
                                }
                        }
                        unset($res_rfp);
                }
		else
		{
			$sql_rfp = $ilance->db->query("
				SELECT p.project_id
				FROM " . DB_PREFIX . "projects p
				WHERE p.date_end <= '" . DATETIME24H . "' 
					AND p.status = 'open'
					AND p.gtc = '1'
					$customquery
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql_rfp) > 0)
			{
				while ($res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC))
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "projects
						SET date_end = '" . $ilance->datetimes->fetch_datetime_fromnow($ilance->datetimes->calendar_days_this_month()) . "',
						close_date = '0000-00-00 00:00:00'
						WHERE project_id = '" . intval($res_rfp['project_id']) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
				}
			}
		}
                // #### EXPIRE ITEM LISTINGS WITH 0 ITEM QUANTITY ##############
                // this will catch item listings that end early due to the seller having 1 qty
                // left and a buyer purchasing that item via buy now. at that point, a trigger
		// will update the project table with buynow_qty = 0 and this task will end the auction early.
                $sql_rfp = $ilance->db->query("
                        SELECT p.*
                        FROM " . DB_PREFIX . "projects p
                        WHERE p.status = 'open'
                                AND p.project_state = 'product'
                                AND p.buynow_price > 0
                                AND p.buynow_qty = '0'
                                AND p.buynow = '1'
				$customquery
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_rfp) > 0)
                {
                        $res_owner = array();
                        while ($res_rfp = $ilance->db->fetch_array($sql_rfp, DB_ASSOC))
                        {
                                $sql_owner = $ilance->db->query("
                                        SELECT username, email
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $res_rfp['user_id'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_owner) > 0)
                                {
                                        $res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'expired',
                                                close_date = '" . DATETIME24H . "'
                                                WHERE project_id = '" . $res_rfp['project_id'] . "' 
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
					$this->remove_watchlist($res_rfp['project_id']);
                                        $ilance->categories->build_category_count($res_rfp['cid'], 'subtract', "listings(): subtracting increment count cid $res_rfp[cid]");
					$existing = array(
                                                '{{project_title}}' => stripslashes($res_rfp['project_title']),
                                                '{{project_id}}' => $res_rfp['project_id'],
                                                '{{owner}}' => $res_owner['username'],
                                                '{{rfpurl}}' => HTTP_SERVER . 'merch.php?id=' . $res_rfp['project_id'],
                                                '{{close_date}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                        );
					
					($apihook = $ilance->api('product_auction_expired_no_buynow_qty')) ? eval($apihook) : false;
					
                                        $ilance->email->mail = $res_owner['email'];
                                        $ilance->email->slng = fetch_user_slng($res_rfp['user_id']);
                                        $ilance->email->get('product_auction_ended_early_via_cron_no_buynow_qty');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        $cronlog .= '';
                                }
                        }
                        unset($res_rfp, $res_owner);
                }
		
		($apihook = $ilance->api('auction_expiry_listings_end')) ? eval($apihook) : false;
		
                return $cronlog;
        }
        
	/**
        * Function to process the removal of items that have expired within users watch list (house keeping)
        *
        * @param       integer        project id
        *
        * @return      boolean        true or false based on successful remove of watchlist items
        */
	function remove_watchlist($id = 0)
	{
		global $ilance, $ilconfig;
		if ($ilconfig['automation_removewatchlist'] AND $id > 0)
		{
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "watchlist
				WHERE watching_project_id = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);
			return true;
		}
		return false;
	}
	
	/**
        * Function to perform house keeping on listings such as expiring featured items, sending email with no escrow activity for 1 day, etc)
        *
        * @return      boolean        Returns true once executed
        */
        function other()
        {
                global $ilance, $ilconfig, $ilpage;
                $cronlog = '';
                if ($ilconfig['serviceupsell_featuredlength'] > 0)
                {
	                $sql_product = $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "projects
	                        SET featured = '0'
	                        WHERE featured = '1'
					AND featured_date != '0000-00-00 00:00:00'
					AND status = 'open'
					AND (featured_date < DATE_SUB(CURDATE(), INTERVAL " . $ilconfig['serviceupsell_featuredlength'] . " DAY))
					AND project_state = 'service'
	                ", 0, null, __FILE__, __LINE__);
			// todo: email buyer asking him to pay for more featured days.. (with link to pay)
        	}
        	if ($ilconfig['productupsell_featuredlength'] > 0)
                {
	                $sql_service = $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "projects
	                        SET featured = '0'
	                        WHERE featured = '1'
	                        	AND featured_date != '0000-00-00 00:00:00'
					AND status = 'open'
					AND (featured_date < DATE_SUB(CURDATE(), INTERVAL " . $ilconfig['productupsell_featuredlength'] . " DAY))
					AND project_state = 'product'
	                ", 0, null, __FILE__, __LINE__);
			// todo: email seller asking him to pay for more featured days... (with link to pay)
                }
                // #### escrow system enabled?
                if ($ilconfig['escrowsystem_enabled'])
                {
                        // this will run a checkup on escrows that are still pending from "yesterday"
                        // meaning the payer has not forwarded funds into the account yet.
                        $sql_rfp_escrow = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "projects_escrow
                                WHERE date_awarded LIKE '%" . ONEDAYAGO . "%'
					AND status = 'pending'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_rfp_escrow) > 0)
                        {
                                while ($res_rfp_escrow = $ilance->db->fetch_array($sql_rfp_escrow, DB_ASSOC))
                                {
					if (fetch_auction('project_state', $res_rfp_escrow['project_id']) == 'service')
					{
						// #### UNPAID RFP ESCROW > OWNER HAS NOT FORWARDED FUNDS YET ######
						$sql_emaillog = $ilance->db->query("
							SELECT *
							FROM " . DB_PREFIX . "emaillog
							WHERE logtype = 'escrow'
								AND project_id = '" . $res_rfp_escrow['project_id'] . "'
								AND user_id = '" . $res_rfp_escrow['project_user_id'] . "'
								AND date LIKE '%" . DATETODAY . "%'
								AND sent = 'yes'
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql_emaillog) == 0)
						{
							$sql_owner = $ilance->db->query("
								SELECT *
								FROM " . DB_PREFIX . "users
								WHERE user_id = '" . $res_rfp_escrow['project_user_id'] . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_owner) > 0)
							{
								$res_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
								$sql_bidder = $ilance->db->query("
									SELECT *
									FROM " . DB_PREFIX . "users
									WHERE user_id = '" . $res_rfp_escrow['user_id'] . "'
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql_bidder) > 0)
								{
									$res_bidder = $ilance->db->fetch_array($sql_bidder, DB_ASSOC);
									$ilance->email->mail = $res_owner['email'];
									$ilance->email->slng = fetch_user_slng($res_rfp_escrow['project_user_id']);
									$ilance->email->get('unpaid_escrow_reminder');		
									$ilance->email->set(array(
										'{{days}}' => '1',
										'{{owner}}' => ucfirst($res_owner['first_name']) . " " . ucfirst($res_owner['last_name']) . " (" . $res_owner['username'] . ")",
										'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
										'{{winningbidder}}' => $res_bidder['username'],
										'{{winningbidderemail}}' => $res_bidder['email'],
										'{{project_title}}' => fetch_auction('project_title', $res_rfp_escrow['project_id']),
										'{{project_id}}' => $res_rfp_escrow['project_id'],
										'{{rfp_url}}' => HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($res_rfp_escrow['project_id'])
									));
									$ilance->email->send();
									$ilance->db->query("
										INSERT INTO " . DB_PREFIX . "emaillog
										(emaillogid, logtype, user_id, project_id, date, sent)
										VALUES(
										NULL,
										'escrow',
										'" . $res_owner['user_id'] . "',
										'" . $res_rfp_escrow['project_id'] . "',
										'" . DATETODAY . "',
										'yes')
									", 0, null, __FILE__, __LINE__);
									$sql_emaillog = $ilance->db->query("
										SELECT *
										FROM " . DB_PREFIX . "emaillog
										WHERE logtype = 'escrow'
											AND project_id = '" . $res_rfp_escrow['project_id'] . "'
											AND user_id = '" . $res_bidder['user_id'] . "'
											AND date LIKE '%" . DATETODAY . "%'
											AND sent = 'yes'
									", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($sql_emaillog) == 0)
									{
										// ### log email so we don't send again today
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "emaillog
											(emaillogid, logtype, user_id, project_id, date, sent)
											VALUES(
											NULL,
											'escrow',
											'" . $res_bidder['user_id'] . "',
											'" . $res_rfp_escrow['project_id'] . "',
											'" . DATETODAY . "',
											'yes')
										", 0, null, __FILE__, __LINE__);
										$ilance->email->mail = $res_bidder['email'];
										$ilance->email->slng = fetch_user_slng($res_bidder['user_id']);
										$ilance->email->get('unpaid_escrow_reminder_receiver');		
										$ilance->email->set(array(
											'{{days}}' => '1',
											'{{owner}}' => ucfirst($res_owner['first_name']) . " " . ucfirst($res_owner['last_name']) . " (" . $res_owner['username'] . ")",
											'{{owneremail}}' => $res_owner['email'],
											'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
											'{{winningbidder}}' => ucfirst($res_bidder['first_name']) . " " . ucfirst($res_bidder['last_name']) . " (" . $res_bidder['username'] . ")",
											'{{winningbidderemail}}' => $res_bidder['email'],
											'{{project_title}}' => fetch_auction('project_title', $res_rfp_escrow['project_id']),
											'{{project_id}}' => $res_rfp_escrow['project_id'],
											'{{rfp_url}}' => HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($res_rfp_escrow['project_id'])
										));
										$ilance->email->send();
										$cronlog .= '';
									}
									$ilance->email->mail = SITE_EMAIL;
									$ilance->email->slng = fetch_site_slng();
									$ilance->email->get('unpaid_escrow_reminder_admin');		
									$ilance->email->set(array(
										'{{days}}' => '1',
										'{{owner}}' => ucfirst($res_owner['first_name']) . " " . ucfirst($res_owner['last_name']) . " (" . $res_owner['username'] . ")",
										'{{owneremail}}' => $res_owner['email'],
										'{{datetime}}' => DATETODAY . ' ' . TIMENOW,
										'{{winningbidder}}' => ucfirst($res_bidder['first_name']) . " " . ucfirst($res_bidder['last_name']) . " (" . $res_bidder['username'] . ")",
										'{{winningbidderemail}}' => $res_bidder['email'],
										'{{project_title}}' => fetch_auction('project_title', $res_rfp_escrow['project_id']),
										'{{project_id}}' => $res_rfp_escrow['project_id'],
										'{{rfp_url}}' => HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($res_rfp_escrow['project_id'])
									));
									$ilance->email->send();
									$cronlog .= '';
								}
							}
						}
					}
                                }
                                unset($res_rfp_escrow);
                        }
                }
                // #### check-up customers rating and update `users` table
                $awardupdate = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "users
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($awardupdate) > 0)
                {
                        while ($res = $ilance->db->fetch_array($awardupdate, DB_ASSOC))
                        {
                                $ilance->auction_service->fetch_service_bids_awarded($res['user_id'], true); // update service proposal wins / awards
                                $ilance->auction_product->fetch_product_bids_awarded($res['user_id'], true); // update product wins / awards
                                $ilance->feedback->construct_ratings($res['user_id']); // update feedback, rating and score
                                $cronlog .= '';
                        }
                        unset($res);
                }
                // in some cases, a listing could have a winning bid, but the bid or user
                // becomes removed by an admin or staff resulting in an unlinked awarded
                // auction.  Let's prevent that via search and delist
                $sqlapproved = $ilance->db->query("
                        SELECT project_id, project_title
                        FROM " . DB_PREFIX . "projects
                        WHERE status = 'approval_accepted' OR status = 'wait_approval'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlapproved) > 0)
                {
                        while ($projects = $ilance->db->fetch_array($sqlapproved, DB_ASSOC))
                        {
                                // check for orphan awards
                                $sqlbidexist = $ilance->db->query("
                                        SELECT bid_id
                                        FROM " . DB_PREFIX . "project_bids
                                        WHERE project_id = '" . $projects['project_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sqlbidexist) == 0)
                                {
                                        // bid does not exist anymore - delist auction
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'delisted'
                                                WHERE project_id = '" . $projects['project_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        $cronlog .= ', Delisted listing ' . handle_input_keywords($projects['project_title']) . ' (#' . $projects['project_id'] . ') due to unlinked bid proposal, ';
                                }
                        }
                        unset($projects);
                }
		// in some cases, an auction could have a listing identifier that equal 0 let's remove them
		$sql = $ilance->db->query("
			DELETE FROM " . DB_PREFIX . "projects
			WHERE project_id = '0'
		", 0, null, __FILE__, __LINE__);
		$cronlog .= $this->listings_expired_to_finished();
		return $cronlog;
        }
        
        /**
        * Function to process a listing relist
        *
        * @param       integer        project id
        * @param       boolean        prevent email from sending? (default false)
        *
        * @return      boolean        true or false based on successful auto-relist of a valid listing
        */
        function process_auction_relister($projectid = 0, $dontsendemail = false)
        {
                global $ilance, $ilconfig, $ilpage;
                $array = array();
                $sql = $ilance->db->query("
                        SELECT bids, autorelist, date_starts, date_end, project_state, buynow, buynow_qty, user_id, project_title
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                                AND autorelist_date = '0000-00-00 00:00:00'
                        ORDER BY user_id ASC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($res['autorelist'] == '1' AND $res['bids'] == '0')
                        {
                                // new ending date for listing
                                if ($res['project_state'] == 'product')
                                {
                                        if ($res['buynow'])
                                        {
                                                if ($res['buynow_qty'] <= 0)
                                                {
                                                        // we cannot relist this buy now listing due to no available buy now quantity
                                                        return false;
                                                }
                                        }
                                        $rfpurl = HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid);
                                        $emailx = 'product_auction_relisted_via_cron';
                                        $duration = $ilconfig['productupsell_autorelistmaxdays'];        
                                }
                                else
                                {
                                        $rfpurl = HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($projectid);
                                        $emailx = 'service_auction_relisted_via_cron';
                                        $duration = $ilconfig['serviceupsell_autorelistmaxdays'];
                                }
                                $moffset = ($duration * 86400);
                                $start_date = DATETIME24H;
                                $date_end = date("Y-m-d H:i:s", (strtotime($start_date) + $moffset));
                                // update listing with new ending date and record the date we're auto-relisting
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects
                                        SET autorelist_date = '" . DATETIME24H . "',
                                        date_end = '" . $ilance->db->escape_string($date_end) . "',
                                        close_date = '0000-00-00 00:00:00',
                                        status = 'open'
                                        WHERE project_id = '" . intval($projectid) . "' 
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                // #### send email to owner about auto-relisting actions
				if ($dontsendemail == false)
				{
					$ilance->email->mail = fetch_user('email', $res['user_id']);
					$ilance->email->slng = fetch_user_slng($res['user_id']);
					$ilance->email->get($emailx);		
					$ilance->email->set(array(
						'{{project_title}}' => stripslashes($res['project_title']),
						'{{owner}}' => fetch_user('username', $res['user_id']),
						'{{rfpurl}}' => $rfpurl,
						'{{new_date_end}}' => $date_end,
						'{{relisted_days}}' => $duration,
					));
					$ilance->email->send();
				}
                                return true;
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