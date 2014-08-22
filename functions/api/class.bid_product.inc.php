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

if (!class_exists('bid'))
{
	exit;
}

/**
* Function to handle inserting a forward auction bid
*
* @package      iLance\Bid\Product
* @version      4.0.0.8059
* @author       ILance
*/
class bid_product extends bid
{
	/**
        * Function for inserting a new product bid on a product auction event.  Additionally, this function will
        * detect if another bidder just placed a bid before this bid is inserted.  This will allow the system to
        * generate an error message back to the current bidder informing them to bid higher due to another bidder
        * placing a bid first.  This function will also set Good until cancelled to off when one or more bids is
        * placed to ensure a winner can be declared at the final end date.
        *
        * @param       integer      higher bid notify filter (optional)
        * @param       integer      last hour notify filter (optional)
        * @param       integer      subscribed to receive email (default false)
        * @param       integer      listing id
        * @param       integer      owner id
        * @param       string       bid amount
        * @param       integer      qty
        * @param       integer      bidder id
        * @param       bool         is proxy bid?
        * @param       string       minimum bid amount
        * @param       string       reserve price amount
        * @param       string       custom argument for live bidding (future)
        * @param       boolean      show error messages (disable if you want to call this function via API to hide html error messages; this will then only return true or false) - default true
        * @param       string       buyer shipping cost (based on his selected shipping service when placing bid)
        * @param       integer      buyer selected shipping service id
        */
        function placebid($highbidnotify = 0, $lasthournotify = 0, $subscribed = 0, $id = 0, $project_user_id = 0, $bidamount = 0, $qty = 1, $bidderid = 0, $isproxy, $minimumbid, $reserveprice, $showerrormessages = true, $buyershipcost = 0, $buyershipperid = 0)
        {
                global $ilance, $ilpage, $phrase, $ilconfig;
                if ($ilance->permissions->check_access($bidderid, 'productbid') == 'no')
                {
                        $area_title = '{_buying_menu_denied_upgrade_subscription}';
                        $page_title = SITE_NAME . ' - {_buying_menu_denied_upgrade_subscription}';
                        if ($showerrormessages)
                        {
                                print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource} <a href="' . $ilpage['subscription'] . '"><strong>{_click_here}</strong></a>.', $ilpage['subscription'], ucwords('{_subscription}'), fetch_permission_name('productbid'));
                                exit();
                        }
                        else
                        {
                                return false;
                        }       
                }
                $area_title = '{_submitting_bid_proposal}';
                $page_title = SITE_NAME . ' {_submitting_bid_proposal}';
		$resexpiry = array();
                $sqlexpiry = $ilance->db->query("
                        SELECT UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, date_end, countdownresets, status, cid, bids, project_title, buynow, buynow_price, buynow_qty, reserve, reserve_price, currentprice, currencyid, gtc
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . intval($id) . "'
			LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlexpiry) > 0)
                {
                        $resexpiry = $ilance->db->fetch_array($sqlexpiry, DB_ASSOC);
                        if ($resexpiry['mytime'] < 0 OR $resexpiry['status'] != 'open')
                        {
                                $area_title = '{_this_rfp_has_expired_bidding_is_over}';
                                $page_title = SITE_NAME . ' - {_this_rfp_has_expired_bidding_is_over}';
                                if ($showerrormessages)
                                {
                                        print_notice($area_title, '{_this_rfp_has_expired_bidding_is_over}', $ilpage['main'], '{_main_menu}');
                                        exit();
                                }
                                else
                                {
                                        return false;
                                }
                        }
                }
                unset($sqlexpiry);
                $ilance->watchlist->insert_item(intval($bidderid), $id, 'auction', 'n/a', 0, $highbidnotify, $lasthournotify, $subscribed);
                
		($apihook = $ilance->api('rfp_do_bid_submit_placebid')) ? eval($apihook) : false;
	
		// #### anti-bid sniping feature ###############################
		if ($ilconfig['productbid_enablesniping'] AND $resexpiry['cid'] > 0)
		{
			// #### check if bid sniping is active in this category
			$useantisnipe = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . $resexpiry['cid'] . "'", "useantisnipe");
			if ($resexpiry['mytime'] <= $ilconfig['productbid_snipedurationcount'] AND $useantisnipe)
			{
				$otherbidders = false;
				$sql = $ilance->db->query("
					SELECT bid_id
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "'
						AND user_id != '" . ($bidderid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$otherbidders = true;
				}
				if ($otherbidders)
				{
					// find out how much time we need to increase the end date
					$datetimestampnew = (strtotime($resexpiry['date_end']) - TIMESTAMPNOW);
					$secondstoincrease = ($ilconfig['productbid_snipeduration'] - $datetimestampnew);
					// limit the amount of times this listing can have the countdown reset
					if ($resexpiry['countdownresets'] < $ilconfig['productbid_countdownresets'] AND $secondstoincrease > 0) 
					{
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "projects
							SET date_end = DATE_ADD(date_end, INTERVAL $secondstoincrease SECOND),
								countdownresets = countdownresets + 1
							WHERE project_id = '" . intval($id) . "'
						", 0, null, __FILE__, __LINE__);
					}
				}
			}
		}
                if ($ilconfig['productbid_enableproxybid'] AND isset($isproxy) AND $isproxy AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $resexpiry['cid']))
                {
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("Proxy Bid Enabled for Category ID $resexpiry[cid]", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("User ID: $bidderid is placing a proxy bid amount: $bidamount, minimum bid including category increment is currently: $minimumbid", 'NOTICE');
                        $res = $ilance->bid_proxy->fetch_first_highest_proxybid($id);
                        $highestproxy = $res[0];
                        $highestproxyuserid = $res[1];
                        unset($res);
                        $res = $ilance->bid_proxy->fetch_second_highest_proxybid($id);
                        $secondhighestproxy = $res[0];
                        $secondhighestproxyuserid = $res[1];
                        unset($res);
                        $highestproxybiduser = $ilance->bid_proxy->fetch_highest_proxy_bid($id, $bidderid);
                        $secondhighestproxybiduser = $ilance->bid_proxy->fetch_second_highest_proxy_bid($id, $bidderid);
                        DEBUG("------------------------------------------", 'NOTICE');                                
                        DEBUG("Highest proxy bid is currently: $highestproxy by user id: $highestproxyuserid", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("Second Highest proxy bid is currently $secondhighestproxy by user id: $secondhighestproxyuserid", 'NOTICE');
                        DEBUG("------------------------------------------", 'NOTICE');
                        // did this bidder already place a proxy bid for this auction?
                        $sql = $ilance->db->query("
                                SELECT id, project_id, user_id, maxamount, date_added
                                FROM " . DB_PREFIX . "proxybid
                                WHERE user_id = '" . intval($bidderid) . "'
					AND project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                // this bidder already placed a proxy bid at some point .. is this new proxy bid higher? if so, place it!
                                $resproxy = $ilance->db->fetch_array($sql, DB_ASSOC);
                                if ($resproxy['maxamount'] < $bidamount)
                                {
                                        DEBUG("SQL: UPDATE " . DB_PREFIX . "proxybid with proxy bid amount $bidamount for User ID: $bidderid", 'NOTICE');
                                        DEBUG("------------------------------------------", 'NOTICE');
                                        // update existing proxybid
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "proxybid
                                                SET maxamount = '" . sprintf("%01.2f", $bidamount) . "',
                                                date_added = '" . DATETIME24H . "'
                                                WHERE user_id = '" . intval($bidderid) . "'
							AND project_id = '" . intval($id) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        // inform bidder of proxy bid error (lower proxy bid being placed for same auction)
                                        if ($showerrormessages)
                                        {
                                                print_notice('{_cannot_bid_lower_than_original_proxy_amount} ' . $ilance->currency->format($resproxy['maxamount'], $resexpiry['currencyid']), '{_it_has_been_detected_that_you_have_already_placed_a_higher_proxy_bid}', HTTP_SERVER . $ilpage['rfp'] . '?cmd=bid&amp;id=' . $id . '&amp;state=product', '{_back}');
                                                exit();
                                        }
                                        else
                                        {
                                                return false;
                                        }
                                }
                        }
                        else
                        {
                                DEBUG("SQL: INSERT INTO " . DB_PREFIX . "proxybid with proxy bid amount $bidamount for User ID: $bidderid", 'NOTICE');
                                DEBUG("------------------------------------------", 'NOTICE');
                                // bidder wishes to enter a new maximum highest bid for proxy
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "proxybid
                                        (id, project_id, user_id, maxamount, date_added)
                                        VALUES(
                                        NULL,
                                        '" . intval($id) . "',
                                        '" . intval($bidderid) . "',
                                        '" . sprintf("%01.2f", $bidamount) . "',
                                        '" . DATETIME24H . "')
                                ", 0, null, __FILE__, __LINE__);
                                $proxybidid = $ilance->db->insert_id();
                        }
                        $sqlbids = $ilance->db->query("
                                SELECT COUNT(*) AS bids
                                FROM " . DB_PREFIX . "project_bids
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sqlbids) > 0)
                        {
                                $resbids = $ilance->db->fetch_array($sqlbids, DB_ASSOC);
				// #### no bids placed #########################
                                if ($resbids['bids'] == 0)
                                {
                                        DEBUG("No bids currently placed for this auction", 'NOTICE');
                                        // #### reserve price enabled ##########
                                        if ($reserveprice > 0)
                                        {
                                                DEBUG("Reserve price exists: $reserveprice", 'NOTICE');
                                                // when enabled, any bid placed that is lower than the reserve price
                                                // will be placed at the rate of the actual bid 
                                                // this will get the bids rolling up to the reserve amount bypassing the increment logic.
                                                // side note: in this situation, this is the first bid being placed.
                                                if ($bidamount >= $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met ($reserveprice); set the bid amount price ($bidamount) as reserve: $reserveprice", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $reserveprice", 'NOTICE');
                                                        
                                                        $bidamount = $reserveprice;
                                                }
                                                else
                                                {
                                                        DEBUG("Reserve price: $reserveprice has not been met", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                        }
                                        // #### reserve price disabled #########
                                        else 
                                        {
                                                DEBUG("Reserve price disabled", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                DEBUG("BID: $minimumbid", 'NOTICE');
                                                
                                                // when reserve price is inactive, the bid placed will adhere to the admin defined increment logic for the bids
                                                // which is basically the minimum bid amount passed to this function
                                                // side note: in this situation, this is the first bid being placed.
                                                $bidamount = $minimumbid;
                                        }
                                }
				// #### one or more bids placed ################
                                else if ($resbids['bids'] > 0)
                                {
                                        DEBUG("Auction has $resbids[bids] bids currently placed", 'NOTICE');
                                        DEBUG("------------------------------------------", 'NOTICE');
                                        // #### reserve price enabled ##########
                                        if ($reserveprice > 0)
                                        {
                                                DEBUG("Reserve price exists: $reserveprice", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                if ($bidamount == $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met: $reserveprice; set bid amount: $bidamount as reserve price: $reserveprice", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        
                                                        $bidamount = $reserveprice;
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                                else if ($bidamount > $reserveprice)
                                                {
                                                        DEBUG("Reserve price has been met; bid being placed: $bidamount is higher than our reserve price ($reserveprice)", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        // if we have a high proxy bid, and if our bid amount is higher than the proxy bid, and if the highest proxy + increment <= bid amount
                                                        //     310.01     > 0 and 360.00     > 310.01        and              320.01                           <= 360.00
                                                        //     360.00     > 0 and 330.01     > 360.00        and              370.00                           <= 330.01
                                                        if ($highestproxy > 0 AND $bidamount > $highestproxy AND $this->fetch_minimum_bid($highestproxy, $resexpiry['cid']) <= $bidamount)
                                                        {
								if ($highestproxyuserid != $bidderid)
								{
									if ($highestproxy > $reserveprice)
									{
										$nextamount = $this->fetch_minimum_bid($highestproxy, $resexpiry['cid']);
									}
									else 
									{
										$nextamount = $this->fetch_minimum_bid($reserveprice, $resexpiry['cid']);
										//$nextamount = $reserveprice;
									}
								}
                                                                else
                                                                {
                                                                	if ($highestproxy < $reserveprice)
									{
										//$nextamount = $this->fetch_minimum_bid($highestproxy, $resexpiry['cid']);
										$nextamount = $reserveprice;
									}
									else 
									{
										if ($showerrormessages)
										{
											refresh($ilpage['merch'] . '?id=' . $id);
											exit();
										}
										else
										{
											return true;
										}
									}
																	
                                                                }
                                                                DEBUG("Next Bid + Increment $nextamount is <= proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                $bidamount = $nextamount;
                                                                DEBUG("BID: $bidamount", 'NOTICE');
                                                        }
                                                        else
                                                        {
                                                                DEBUG("Higher proxy bid exists $highestproxy by user id: $highestproxyuserid, and this bid: $bidamount by user id: $bidderid", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                if ($highestproxyuserid != $bidderid)
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid without the increment logic as it's much greater and will get the bids moving faster", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                }
                                                                else
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidder ($bidamount) so we'll place the bid a minimum bid instead", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                        if ($showerrormessages)
									{
										refresh($ilpage['merch'] . '?id=' . $id);
										exit();
									}
									else
									{
										return true;
									}
                                                                }
                                                                DEBUG("BID: $bidamount", 'NOTICE');
                                                        }
                                                }
                                                else
                                                {
                                                        DEBUG("Reserve price has not been met", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        DEBUG("BID: $bidamount", 'NOTICE');
                                                }
                                        }
					// #### reserve price disabled #########
					else 
                                        {
                                                DEBUG("Reserve price is disabled", 'NOTICE');
                                                DEBUG("------------------------------------------", 'NOTICE');
                                                if ($bidamount > $highestproxy)
                                                {
                                                        DEBUG("Proxy bid amount being placed: $bidamount is greater than highest proxy: $highestproxy", 'NOTICE');
                                                        DEBUG("Category ID: #$resexpiry[cid]", 'NOTICE');
                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                        $nextamount = $this->fetch_minimum_bid($highestproxy, $resexpiry['cid']);
                                                        if ($nextamount >= $bidamount)
                                                        {
                                                                DEBUG("Next Bid + Bid Increment (in this category) $nextamount is >= to proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                              	if ($highestproxyuserid == $bidderid)
								{
									if ($showerrormessages)
									{
										refresh($ilpage['merch'] . '?id=' . $id);
										exit();
									}
									else
									{
										return true;
									}
								}
                                                        }
                                                        else
                                                        {
                                                                DEBUG("Next Bid + Increment $nextamount is < proxy bid $bidamount", 'NOTICE');
                                                                DEBUG("------------------------------------------", 'NOTICE');
                                                                if ($highestproxyuserid != $bidderid)
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid without the increment logic as it's much greater and will get the bids moving faster", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                	$bidamount = $nextamount;
                                                                }
                                                                else
                                                                {
                                                                        DEBUG("Highest proxy bid user id: $highestproxyuserid ($highestproxy) is not the same bidder as user id: $bidderid ($bidamount) so we'll place the bid a minimum bid instead", 'NOTICE');
                                                                        DEBUG("------------------------------------------", 'NOTICE');
                                                                        if ($showerrormessages)
									{
										refresh($ilpage['merch'] . '?id=' . $id);
										exit();
									}
									else
									{
										return true;
									}
                                                                }
                                                        }
                                                }
                                        }
                                }
                        }
                }
                DEBUG("SQL: INSERT INTO " . DB_PREFIX . "project_bids bid amount: $bidamount for bidder id $bidderid", 'NOTICE');
                // insert the next minimum bid for the bidder
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "project_bids
                        (bid_id, user_id, project_id, project_user_id, bidamount, qty, date_added, bidstatus, bidstate, state, buyershipcost, buyershipperid)
                        VALUES(
                        NULL,
                        '" . intval($bidderid) . "',
                        '" . intval($id) . "',
                        '" . intval($project_user_id) . "',
                        '" . sprintf("%01.2f", $bidamount) . "',
                        '" . intval($qty) . "',
                        '" . DATETIME24H . "',
                        'placed',
                        '',
                        'product',
			'" . sprintf("%01.2f", $buyershipcost) . "',
			'" . intval($buyershipperid) . "')
                ", 0, null, __FILE__, __LINE__);
                $this_bid_id = $ilance->db->insert_id();
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "project_realtimebids
                        (id, bid_id, user_id, project_id, project_user_id, bidamount, qty, date_added, bidstatus, bidstate, state, buyershipcost, buyershipperid)
                        VALUES(
                        NULL,
                        '" . intval($this_bid_id) . "',
                        '" . intval($bidderid) . "',
                        '" . intval($id) . "',
                        '" . intval($project_user_id) . "',
                        '" . sprintf("%01.2f", $bidamount) . "',
                        '" . intval($qty) . "',
                        '" . DATETIME24H . "',
                        'placed',
                        '',
                        'product',
			'" . sprintf("%01.2f", $buyershipcost) . "',
			'" . intval($buyershipperid) . "')
                ", 0, null, __FILE__, __LINE__);
                DEBUG("------------------------------------------", 'NOTICE');
                DEBUG("set_bid_counters() - Set bid counters for this bidder (increases bidstoday + bidsthismonth)", 'NOTICE');
                // will increase bidstoday and bidsthismonth
                $this->set_bid_counters($bidderid, 'increase');
                DEBUG("------------------------------------------", 'NOTICE');
                DEBUG("SQL UPDATE project table bids + 1", 'NOTICE');
                // update bid count and set good until cancelled to off since a bid is placed and counter must go to zero for a winner to be declared
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "projects
                        SET bids = bids + 1,
			currentprice = '" . sprintf("%01.2f", $bidamount) . "',
			gtc = '0'
                        WHERE project_id = '" . intval($id) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
		if ($resexpiry['gtc'] == '1')
		{
			// this will set a "date" so we know if this listing was originally using GTC or not
			// this will also let us put gtc = '1' back on this listing if a bid is retracted in the future
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET gtc_cancelled = '" . DATETIME24H . "'
				WHERE project_id = '" . intval($id) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
		// #### do we need to hide buy now price and controls? #########
		$hidebuynow = false;
		if ($resexpiry['buynow'] AND $resexpiry['buynow_price'] > 0 AND $resexpiry['buynow_qty'] > 0)
		{
			$sql = $ilance->db->query("
				SELECT hidebuynow
				FROM " . DB_PREFIX . "categories
				WHERE cid = '" . intval($resexpiry['cid']) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($bidamount >= $resexpiry['buynow_price'] OR $res['hidebuynow'])
			{
				$hidebuynow = true;
			}
			else
			{
				if ($resexpiry['reserve'] AND $resexpiry['reserve_price'] > 0)
				{
					// has reserve price been met?
					if ($bidamount >= $resexpiry['reserve_price'])
					{
						$hidebuynow = true;
					}
				}
				else
				{
					if ($bidamount >= $resexpiry['buynow_price'])
					{
						// bid amount higher than buy now price! hide buy now controls
						$hidebuynow = true;
					}
				}
			}
		}
		// #### determine if we need to hide the buynow price (based on our bids exceeding or equaling the buy now price set)
		if ($hidebuynow)
		{
			// hide buy now controls
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "projects
				SET buynow = '0'
				WHERE project_id = '" . intval($id) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
		}
                // #### was this bidder invited? ###############################
                $sql_invites = $ilance->db->query("
                        SELECT id, project_id, buyer_user_id, seller_user_id, email, name, invite_message, date_of_invite, date_of_bid, date_of_remind, bid_placed
                        FROM " . DB_PREFIX . "project_invitations
                        WHERE project_id = '" . intval($id) . "'
				AND buyer_user_id = '" . intval($bidderid) . "'
				AND bid_placed = 'no'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_invites) > 0)
                {
                        DEBUG("------------------------------------------", 'NOTICE');
                        DEBUG("It appears this bidder was invited by the seller, update invite table to bid_placed = yes", 'NOTICE');
                        // update invite table 
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "project_invitations
                                SET bid_placed = 'yes',
				    date_of_bid = '" . DATETIME24H . "'
                                WHERE buyer_user_id = '" . intval($bidderid) . "'
					AND project_id = '" . intval($id) . "'
                                LIMIT 1
                        ", 0, null, __FILE__, __LINE__);
                        $url = HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id);
                        // email owner
                        $ilance->email->mail = fetch_user('email', $project_user_id);
                        $ilance->email->slng = fetch_user_slng($project_user_id);
                        $ilance->email->get('invited_bid_placed_buyer');		
                        $ilance->email->set(array(
                                '{{buyer}}' => fetch_user('username', $project_user_id),
                                '{{vendor}}' => fetch_user('username', $bidderid),
                                '{{rfp_title}}' => strip_tags($resexpiry['project_title']),
                                '{{project_id}}' => $id,
                                '{{url}}' => $url,
                        ));
                        $ilance->email->send();
                }
                // #### AUTOMATED PROXY BIDDER ENGINE ##########################
                if ($ilconfig['productbid_enableproxybid'] AND isset($isproxy) AND $isproxy AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $resexpiry['cid']))
                {
                        DEBUG("---------------------------------------------", 'NOTICE');
                        DEBUG("PROXY: ILance Proxy Bid Engine " . ILANCEVERSION . "", 'NOTICE');
                        DEBUG("---------------------------------------------", 'NOTICE');
                        // background proxy bidder init for this last bidder
                        // this is where the proxy automation comes into action
                        $ilance->bid_proxy->do_proxy_bidder(intval($id), $bidderid, $project_user_id, 1, $bidderid);
                        DEBUG("---------------------------------------------", 'NOTICE');
                        DEBUG("PROXY: Finished", 'NOTICE');
                        DEBUG("---------------------------------------------", 'NOTICE');
                }
                // #### for debug purposes only ################################
                // print_r($GLOBALS['DEBUG']); exit;
                $ilance->watchlist->send_notification($bidderid, 'lowbidnotify', intval($id), $bidamount);
                $ilance->watchlist->send_notification($bidderid, 'highbidnotify', intval($id), $bidamount);
                
		($apihook = $ilance->api('product_sending_notifications_end')) ? eval($apihook) : false;

                $ilance->email->mail = fetch_user('email', $project_user_id);
                $ilance->email->slng = fetch_user_slng($project_user_id);
                $ilance->email->get('product_bid_notification_alert');		
                $ilance->email->set(array(
                	'{{ownername}}' => fetch_user('username', $project_user_id),
                        '{{bidder}}' => fetch_user('username', $bidderid),
                        '{{price}}' => $ilance->currency->format($bidamount, $resexpiry['currencyid']),
                        '{{p_id}}' => intval($id),
			'{{project_title}}' => strip_tags($resexpiry['project_title']),
			'{{url}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($id),
                ));
                $ilance->email->send();                
                log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['rfp'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['id']);
                if ($showerrormessages)
                {
                        refresh(HTTP_SERVER . $ilpage['merch'] . '?id=' . $id); // todo: check for seo url instead!
                        exit();
                }
                else
                {
                        return true;
                }
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>