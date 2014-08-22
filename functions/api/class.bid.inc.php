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

/**
* Bid class to perform the majority of bidding functions within ILance
*
* @package      iLance\Bid
* @version      4.0.0.8059
* @author       ILance
*/
class bid
{
	/**
	* Function to determine the actual "win type" for a listing.  For example, if we're viewing feedback for this entry, and the user won a product auction,
	* show the win type as "bid", if for example the product listing had buy now available, and the user won via buy now the win type would show "buynow".
	* Additionally, if the winning type was a service auction, the wintype would be "awarded".
	* 
	* This function accepts both service and product logic.
	*
	* @param      integer      project id
	* @param      string       project state (service or product)
	* @param      string       auction type (regular or fixed)
	* @param      integer      seller id
	* @param      integer      buyer id
	*
	* @return     string       win type (highestbid, buynow, awarded or unknown)
	*/
	function fetch_auction_win_type($project_id = 0, $project_state = '', $auctiontype = '', $project_details = '', $seller_id = 0, $buyer_id = 0)
	{
		global $ilance, $ilconfig, $phrase, $show;
		$html = '{_unknown}';
		if ($project_state == 'service')
		{
			$show['wintype'] = 'awarded';
			$html = '{_awarded}';
		}
		else if ($project_state == 'product')
		{
			if ($auctiontype == 'regular')
			{
				$sql = $ilance->db->query("
					SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "orderid
					FROM " . DB_PREFIX . "buynow_orders
					WHERE project_id = '" . intval($project_id) . "'
					LIMIT 1
				");
				if ($ilance->db->num_rows($sql) > 0)
				{
					$show['wintype'] = 'buynow';
					$html = '{_buy_now}';
				}
				else
				{
					$show['wintype'] = 'highbid';
					$html = '{_highest_bid}';
				}
			}
			else if ($auctiontype == 'fixed')
			{
				$show['wintype'] = 'buynow';
				$html = '{_buy_now}';
			}
		}
		return $html;
	}
	
	/**
	* Function for returning the raw winning amount from an auction that has ended with a winner.  This function accepts both service and product logic
	* and will calculate service, product and buy now winnings.  Even if shipping is used, it will not be added within this function.
	*
	* @param      integer      project id
	* @param      integer      seller id
	* @param      integer      buyer id
	*
	* @return     string       raw auction winning cost
	*/
	function fetch_auction_win_amount($project_id = 0, $seller_id = 0, $buyer_id = 0)
	{
		global $ilance, $ilconfig, $show;
		$totalamount = 0;
		$project_state = fetch_auction('project_state', $project_id);
		$filtered_auctiontype = fetch_auction('filtered_auctiontype', $project_id);
		if ($project_state == 'product' AND ($filtered_auctiontype == 'fixed' OR $filtered_auctiontype == 'regular'))
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "qty
				FROM " . DB_PREFIX . "buynow_orders
				WHERE project_id = '" . intval($project_id) . "'
					AND (owner_id = '" . intval($seller_id) . "' AND buyer_id = '" . intval($buyer_id) . "' OR owner_id = '" . intval($buyer_id) . "' AND buyer_id = '" . intval($seller_id) . "')
				ORDER BY orderid DESC
				LIMIT 1
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				// we do so lets find out the actual cost of this item for the qty ordered .. and do not include shipping!
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if ($res['qty'] <= 0)
				{
					$res['qty'] = 1;
				}
				$buynowprice = fetch_auction('buynow_price', $project_id);
				$totalamount = sprintf("%01.2f", ($buynowprice * $res['qty']));
			}
			else
			{
				$totalamount = $this->fetch_awarded_bid_amount($project_id);
			}
		}
		else
		{
			$totalamount = $this->fetch_awarded_bid_amount($project_id);
		}
		return $totalamount;
	}
	
	/**
	* Function for returning awarded bid amount for a particular auction event.
	*
	* @param      integer      project id
	*
	* @return     string       awarded bid amount
	*/
	function fetch_awarded_bid_amount($projectid = 0)
	{
		global $ilance;
		$project_details = fetch_auction('project_details', $projectid);
		$amount = 0;
		$sql = $ilance->db->query("
			SELECT bidamount, bidamounttype, estimate_days
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
				AND bidstatus = 'awarded'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['bidamounttype'] == 'entire' OR $res['bidamounttype'] == 'lot' OR $res['bidamounttype'] == 'weight')
			{
				$amount = sprintf("%.02f", $res['bidamount']);
			}
			else
			{
				$amount = sprintf("%.02f", ($res['bidamount'] * $res['estimate_days']));
			}
		}
		return $amount;
	}
	
	/**
	* Function for determining if a particular auction event has any bids placed.
	*
	* @param      integer      project id
	*
	* @return     bool         true or false
	*/
	function has_bids($projectid = 0)
	{
		global $ilance, $ilconfig;
		$bidders = $ilance->db->query("
			SELECT COUNT(*) AS bids
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($bidders, DB_ASSOC);
		return $res['bids'];
	}
	
	/**
	* Function to fetch the highest bidder "user_id" for a reverse or forward auction event.
	*
	* @param       integer      project id
	* 
	* @return      integer      bidder id
	*/
	function fetch_highest_bidder($projectid = 0)
	{
		global $ilance, $ilconfig;
		$project_state = fetch_auction('project_state', $projectid);
		if ($project_state == 'product')
		{
			$proxycid = fetch_auction('cid', $projectid);
			if ($ilconfig['productbid_enableproxybid'] AND $ilance->categories->useproxybid($_SESSION['ilancedata']['user']['slng'], $proxycid))
			{
				$highbid = $ilance->db->query("
					SELECT b.user_id
					FROM " . DB_PREFIX . "project_bids AS b,
					" . DB_PREFIX . "proxybid AS p
					WHERE b.project_id = '" . intval($projectid) . "'
						AND b.project_id = p.project_id
						AND b.user_id = p.user_id
						AND b.bidstatus != 'declined'
						AND b.bidstate != 'retracted'
					ORDER BY b.bidamount DESC, p.date_added ASC
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($highbid) > 0)
				{
					$res = $ilance->db->fetch_array($highbid, DB_ASSOC);
					return $res['user_id'];
				}
				else
				{
					$highbid = $ilance->db->query("
						SELECT user_id
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . intval($projectid) . "'
							AND bidstatus != 'declined'
							AND bidstate != 'retracted'
						ORDER BY bidamount DESC, date_added ASC
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($highbid) > 0)
					{
						$res = $ilance->db->fetch_array($highbid, DB_ASSOC);
						return $res['user_id'];
					}
					return 0;
				}
			}
			else
			{
				$highbid = $ilance->db->query("
					SELECT user_id
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($projectid) . "'
						AND bidstatus != 'declined'
						AND bidstate != 'retracted'
					ORDER BY bidamount DESC, date_added ASC
					LIMIT 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($highbid) > 0)
				{
					    $res = $ilance->db->fetch_array($highbid, DB_ASSOC);
					    return $res['user_id'];
				}
				return 0;
			}
		}
		else
		{
			$highbid = $ilance->db->query("
				SELECT user_id
				FROM " . DB_PREFIX . "project_bids
				WHERE project_id = '" . intval($projectid) . "'
					AND bidstatus != 'declined'
					AND bidstate != 'retracted'
				ORDER BY bidamount DESC, date_added ASC
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($highbid) > 0)
			{
				$res = $ilance->db->fetch_array($highbid, DB_ASSOC);
				return $res['user_id'];
			}
			return 0;
		}
	}
	
	/**
	* Function to fetch the highest bid amount for a particular auction event.
	* This function does not care about the highest proxy bid, only current bids within the bids table.
	*
	* @param       integer      project id
	* 
	* @return      string       bid amount
	*/
	function fetch_highest_bid($projectid = 0)
	{
		global $ilance;
		$highestbid = 0;
		$sql = $ilance->db->query("
			SELECT bidamount
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
				AND bidstate != 'retracted'
			ORDER BY bidamount DESC, date_added ASC
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$highestbid = sprintf("%.02f", $res['bidamount']);
		}
		DEBUG("fetch_highest_bid() return: $highestbid", 'NOTICE');
		return $highestbid;
	}
	
	/**
	* Function to fetch the second highest bid for a particular auction event.
	*
	* @param       integer      project id
	* @param       integer      user id
	* 
	* @return      string       bid amount
	*/
	function fetch_second_highest_bid($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT bidamount, user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
			ORDER BY bidamount DESC, date_added DESC
			LIMIT 2
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) == 2)
		{
			$count = 0;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$count++;
				if ($count == 2)
				{
					return array ($res['bidamount'], $res['user_id']);
				}
			}
		}
		return array (0, 0);
	}
	
	/**
	* Function to fetch the minimum bid amount to place for a particular auction event.  This function take into consideration any increments to apply within a specific category.
	*
	* @param       string       highest bid amount
	* @param       integer      category id
	* 
	* @return      string       bid amount
	*/
	function fetch_minimum_bid($highestbid = 0, $cid = 0)
	{
		global $ilance, $ilconfig;
		$minbidamount = sprintf("%.02f", $highestbid);
		$incrementgroup = $ilance->db->fetch_field(DB_PREFIX . "categories", "cid = '" . intval($cid) . "'", "incrementgroup");
		$sql = $ilance->db->query("
			SELECT amount
			FROM " . DB_PREFIX . "increments
			WHERE ((increment_from <= $highestbid AND increment_to >= $highestbid) OR (increment_from < $highestbid AND increment_to < $highestbid))
				AND groupname = '" . $ilance->db->escape_string($incrementgroup) . "'
			ORDER BY amount DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$minbidamount = sprintf("%.02f", $highestbid + $res['amount']);
			DEBUG("fetch_minimum_bid() category increment amount: $res[amount]", 'NOTICE');
		}
		DEBUG("fetch_minimum_bid() return: $minbidamount", 'NOTICE');
		return $minbidamount;
	}
	
	/**
	* Function to determine if the viewing bidder is the highest bidder for a particular auction event.
	*
	* @param       integer      bidder id
	* @param       integer      project id
	* 
	* @return      bool         true or false
	*/
	function is_highest_bidder($bidderid = 0, $projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
			ORDER BY bidamount DESC, date_added DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['user_id'] == $bidderid)
			{
				return 1;
			}
		}
		return 0;
	}
	
	/**
	* Function to determine if a particular auction event has an active highest bidder present
	*
	* @param       integer      project id
	* 
	* @return      bool         true or false
	*/
	function has_highest_bidder($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
		    SELECT user_id
		    FROM " . DB_PREFIX . "project_bids
		    WHERE project_id = '" . intval($projectid) . "'
		    ORDER BY bidamount DESC, date_added DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
	
	/**
	* Function to determine if the viewing bidder is the winner for a particular auction event.
	* This function will scan both, project bids (for awarded bids) or buynow_orders to determine if
	* a buy now purchase order was made for the item.
	*
	* @param       integer      bidder id
	* @param       integer      project id
	* 
	* @return      bool         true or false
	*/
	function is_winner($bidderid = 0, $projectid = 0)
	{
		global $ilance, $show;
		$show['wonbyauction'] = $show['wonbypurchase'] = false;
		// was item won by a high bidder?
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
			    AND bidstatus = 'awarded'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['user_id'] == $bidderid)
			{
				$show['wonbyauction'] = true;
				return true;
			}
		}
		unset($res);
		// was item won via buy now option?
		$sql = $ilance->db->query("
			SELECT buyer_id
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($projectid) . "'
				AND status != 'cancelled'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['buyer_id'] == $bidderid)
				{
					$show['wonbypurchase'] = true;
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	* Function to determine if a particular product listing has a winning bidder.
	*
	* @param       integer      project id
	* 
	* @return      bool         true or false
	*/
	function has_winning_bidder($projectid = 0)
	{
		global $ilance, $show;
		$show['soldbyauction'] = false;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
				AND bidstatus = 'awarded'
				AND bidstate != 'retracted'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql, DB_ASSOC) > 0)
		{
			$show['soldbyauction'] = true;
			return true;
		}
		return false;
	}
	
	/**
	* Function to determine if the viewing bidder has been outbid for a particular auction event.
	*
	* @param       integer      bidder id
	* @param       integer      project id
	* 
	* @return      bool         true or false
	*/
	function is_outbid($bidderid = 0, $projectid = 0)
	{
		global $ilance;
		$exist = $ilance->db->query("
			SELECT bidamount
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
				AND user_id = '" . intval($bidderid) . "'
				AND bidstate != 'retracted'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($exist) > 0)
		{
			$sql = $ilance->db->query("
				SELECT bidamount, user_id
				FROM " . DB_PREFIX . "project_bids
				WHERE project_id = '" . intval($projectid) . "'
					AND bidstate != 'retracted'
				ORDER BY bidamount DESC, bid_id DESC
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				// user has placed a bid in the auction.  have we been outbid?
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					if ($res['user_id'] == $bidderid)
					{
						return 0;
					}
					else
					{
						// we are outbid: double check if the auction is expired
						$sql2 = $ilance->db->query("
							SELECT status
							FROM " . DB_PREFIX . "projects
							WHERE project_id = '" . intval($projectid) . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql2) > 0)
						{
							$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
							if ($res2['status'] != 'open')
							{
								// auction not open: hide outbid html bit
								return 0;
							}
							else
							{
								// auction still open
								return 1;
							}
						}
					}
				}
			}
		}
		return 3;
	}
	
	/**
	* Function for determining if a particular auction event has any bid filters and if so this
	* function will print out the appropriate error response based on the credentials of the logged in
	* member.  Additionally, this function has been updated to also detect the new profile auction filter
	* option where admin can define say "weight" and the auction poster defines a "weight" range for the
	* auction (from: 100 lbs to: 200 lbs) type format (question is ultimately answered by the bidder from
	* his/her profile menu)
	*
	* @param       integer      project id
	*
	* @return     string       HTML representation of a particular bid permission error (if applicable)
	*/
	function bid_filter_checkup($projectid = 0)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$sql = $ilance->db->query("
			SELECT filter_rating, filtered_rating, filter_country, filtered_country, filter_state, filtered_state, filter_city, filtered_city, filter_zip, filtered_zip, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		$row = $ilance->db->fetch_array($sql, DB_ASSOC);
	    
		($apihook = $ilance->api('bid_filter_checkup_start')) ? eval($apihook) : false;
	    
		if ($row['mytime'] < 0)
		{
			$row['mytime'] = - $row['mytime'];
			$sign = '-';
		}
		else
		{
			$sign = '+';
		}
		if ($sign == '-')
		{
			$area_title = '{_this_rfp_has_expired_bidding_is_over}';
			$page_title = SITE_NAME . ' - {_this_rfp_has_expired_bidding_is_over}';
			$filtered_message = '{_this_rfp_has_expired_bidding_is_over}';
			$return_url = $ilpage['main'];
			print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
			exit();
		}
		if ($row['filter_rating'])
		{
			$memberinfo = array ();
			$memberinfo = $ilance->feedback->datastore($_SESSION['ilancedata']['user']['userid']);
			if ($memberinfo['rating'] < intval($row['filtered_rating']))
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_requires_rating_of_at_least}' . " " . $row['filtered_rating'] . " " . '{_stars}';
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_country'])
		{
			$cfiltered = mb_strtolower(stripslashes($row['filtered_country']));
			$countryname = mb_strtolower(stripslashes($ilance->common_location->print_user_country($_SESSION['ilancedata']['user']['userid'], $_SESSION['ilancedata']['user']['slng'])));
			if (!empty($_SESSION['ilancedata']['user']['countryid']) AND $countryname != $cfiltered)
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_country_must_be_located_in}' . " " . stripslashes($row['filtered_country']);
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $ilpage['main'], '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_state'])
		{
			$sfiltered = mb_strtolower(stripslashes($row['filtered_state']));
			$cstate = mb_strtolower(stripslashes($_SESSION['ilancedata']['user']['state']));
			if ($cstate != $sfiltered)
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_state_province_must_be_located_in}' . " " . ucfirst($sfiltered);
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_city'])
		{
			$cityfiltered = mb_strtolower(stripslashes($row['filtered_city']));
			$ccity = mb_strtolower(stripslashes($_SESSION['ilancedata']['user']['city']));
			if ($ccity != $cityfiltered AND !empty($cityfiltered))
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_city_must_be_located_in}' . " " . ucfirst($cityfiltered);
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_zip'])
		{
			$zipfiltered = mb_strtolower($row['filtered_zip']);
			$czip = mb_strtolower($_SESSION['ilancedata']['user']['postalzip']);
			if ($czip != $zipfiltered)
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_zip_postal_code_must_be_located_in}' . " " . mb_strtoupper($zipfiltered);
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $ilpage['main'], '{_main_menu}');
				exit();
			}
		}
	}
	
	/**
	* Function to determine if the viewing auction has any bid filters.
	*
	* @param       integer      project id
	* 
	* @return      string       error message if any
	*/
	function product_bid_filter_checkup($id = 0)
	{
		global $ilance, $ilpage, $phrase, $page_title, $area_title, $ilconfig;
		$sql_filterprojects = $ilance->db->query("
			SELECT filter_rating, filtered_rating, filter_country, filtered_country, filter_state, filtered_state, filter_city, filtered_city, filter_zip, filtered_zip, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($id) . "'
				AND project_state = 'product'
				AND visible = '1'
		", 0, null, __FILE__, __LINE__);
		$row = $ilance->db->fetch_array($sql_filterprojects, DB_ASSOC);
	    
		($apihook = $ilance->api('product_bid_filter_checkup_start')) ? eval($apihook) : false;
	    
		$sign = '+';
		if ($row['mytime'] < 0)
		{
			$row['mytime'] = - $row['mytime'];
			$sign = '-';
		}
		if ($sign == '-')
		{
			$area_title = '{_this_rfp_has_expired_bidding_is_over}';
			$page_title = SITE_NAME . ' - {_this_rfp_has_expired_bidding_is_over}';
			$filtered_message = '{_this_rfp_has_expired_bidding_is_over}';
			print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $ilpage['main'], '{_main_menu}');
			exit();
		}
		if ($row['filter_rating'])
		{
			$memberinfo = array ();
			$memberinfo = $ilance->feedback->datastore($_SESSION['ilancedata']['user']['userid']);
			if ($memberinfo['rating'] < $row['filtered_rating'])
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_messageT = '{_requires_rating_of_at_least}' . " " . $row['filtered_rating'] . " " . '{_stars}';
				global $filtered_message;
				$filtered_message = $filtered_messageT;
				print_notice('{_bid_filter_restriction}' . " " . $filtered_message, "" . '{_sorry_this_merchant_has_set_bid_filtering_permissions_on_their_auction}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\")>" . '{_click_here}' . "</a>.", 'javascript:history.back(1);', '{_back}');
				exit();
			}
		}
		if ($row['filter_country'])
		{
			$cfiltered = fetch_country_id($row['filtered_country'], $_SESSION['ilancedata']['user']['slng']);
			if ($_SESSION['ilancedata']['user']['countryid'] == $cfiltered)
			{
				$sql_cname = $ilance->db->query("SELECT location_" . $_SESSION['ilancedata']['user']['slng'] . " FROM " . DB_PREFIX . "locations WHERE locationid = '" . $cfiltered . "'", 0, null, __FILE__, __LINE__);
				$res_cname = $ilance->db->fetch_array($sql_cname);
			}
			else
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_country_must_be_located_in}' . " " . stripslashes($res_cname['location_' . $_SESSION['ilancedata']['user']['slng']]);
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $ilpage['main'], '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_state'])
		{
			$sfiltered = mb_strtolower($row['filtered_state']);
			$sql_state = $ilance->db->query("SELECT state FROM " . DB_PREFIX . "users WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'", 0, null, __FILE__, __LINE__);
			$res_state = $ilance->db->fetch_array($sql_state);
			$cstate = mb_strtolower($res_state['state']);
			if ($cstate != $sfiltered)
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_state_province_must_be_located_in}' . " " . ucfirst($sfiltered);
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_city'])
		{
			$cityfiltered = mb_strtolower($row['filtered_city']);
			$sql_city = $ilance->db->query("SELECT city FROM " . DB_PREFIX . "users WHERE user_id='" . $_SESSION['ilancedata']['user']['userid'] . "'", 0, null, __FILE__, __LINE__);
			$res_city = $ilance->db->fetch_array($sql_city);
			$ccity = mb_strtolower($res_city['city']);
			if ($ccity != $cityfiltered AND !empty($cityfiltered))
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_city_must_be_located_in}' . " " . ucfirst($cityfiltered);
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
		if ($row['filter_zip'])
		{
			$zipfiltered = mb_strtolower($row['filtered_zip']);
			$sql_zip = $ilance->db->query("SELECT zip_code FROM " . DB_PREFIX . "users WHERE user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'", 0, null, __FILE__, __LINE__);
			$res_zip = $ilance->db->fetch_array($sql_zip);
			$czip = mb_strtolower($res_zip['zip_code']);
			if ($czip != $zipfiltered)
			{
				$area_title = '{_placing_bid_invalid_bid_filter_permissions}';
				$page_title = SITE_NAME . ' - {_placing_bid_invalid_bid_filter_permissions}';
				$filtered_message = '{_bidders_zip_postal_code_must_be_located_in}' . " " . mb_strtoupper($zipfiltered);
				$return_url = $ilpage['main'];
				print_notice('{_bid_filter_restriction}' . "&nbsp;" . $filtered_message, '{_were_sorry_this_project_owner_has_set_bid_filtering_permissions_on_their_project}' . "<br /><br />" . '{_bid_filtering_allows_the_buyer_to_filter_various_aspects_of_their_project}' . "<br /><br />" . '{_to_learn_more_about_bid_filter_permissions}' . " <a href=\"javascript:void(0)\" onClick=Attach(\"" . $ilpage['rfp'] . "?msg=bid-permissions\") >" . '{_click_here}' . "</a>", $return_url, '{_main_menu}');
				exit();
			}
		}
	}
	
	/**
	* Function for printing a bid privacy details pulldown menu.
	*
	* @param       string       selected value (optional)
	*
	* @return     string       HTML representation of the pulldown menu
	*/
	function construct_bid_details_pulldown($selected = '')
	{
		global $ilance, $phrase;
		$html = '<select name="bid_details" style="font-family: verdana">';
		$html .= '<option value="open"';
		if ($selected == "open")
		{
			$html .= ' selected="selected"';
		}
		$html .= '>{_open}</option>';
		$html .= '<option value="sealed"';
		if ($selected == "sealed")
		{
			$html .= ' selected="selected"';
		}
		$html .= '>{_sealed}</option>';
		$html .= '<option value="blind"';
		if ($selected == "blind")
		{
			$html .= ' selected="selected"';
		}
		$html .= '>{_blind}</option>';
		$html .= '<option value="full"';
		if ($selected == "full")
		{
			$html .= ' selected="selected"';
		}
		$html .= '>' . $phrasr['_full'] . '</option>';
		$html .= '</select>';
		return $html;
	}
	
	/**
	* Function for returning a boolean value if a particular bid amount range for a project is valid
	*
	* @param       integer      project id
	* @param       string       bid amount
	* @param       string       filtered bid type id
	*
	* @return     string       HTML representation of the bidder and their bid
	*/
	function is_valid_bid_range($projectid = 0, $bidamount = 0, $filtered_bidtype = '', $delivery = 0)
	{
		global $ilance;
		$bidamount = $ilance->currency->string_to_number($bidamount);
		$sql = $ilance->db->query("
			SELECT filter_budget, filtered_budgetid
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($res['filter_budget'] == 1)
			{
				// buyer chooses a bid amount range
				// select the budget id range from the budget table
				$sql2 = $ilance->db->query("
					SELECT budgetfrom, budgetto
					FROM " . DB_PREFIX . "budget
					WHERE budgetid = '" . $res['filtered_budgetid'] . "'
				");
				if ($ilance->db->num_rows($sql2) > 0)
				{
					if ($filtered_bidtype == 'hourly')
					{
						$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
						// are we bidding to low or too high?
						if (($bidamount * $delivery) < $res2['budgetfrom'] OR ($bidamount * $delivery) > $res2['budgetto'])
						{
							return 0;
						}
						// we've made it past the budget restrictions
						return 1;
					}
					else
					{
						$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
						// are we bidding to low or too high?
						if ($bidamount < $res2['budgetfrom'] OR ($res2['budgetto'] == -1 ? $bidamount < $res2['budgetto'] : $bidamount > $res2['budgetto']))
						{
							return 0;
						}
						// we've made it past the budget restrictions
						return 1;
					}
				}
				else
				{
					// admin or someone else removed the budgetid associated
					// with this project .. treat as an okay bid.. (just in case)
					return 1;
				}
			}
			else
			{
				//  buyer wishes to let any bid amount type
				return 1;
			}
		}
		return 1;
	}
	
	/**
	* Function for setting member bid count history (bidstoday and bidsthismonth) within the user database table.
	*
	* @param       integer      user id
	* @param       string       action to perform (increase or decrease)
	*
	* @return      nothing
	*/
	function set_bid_counters($userid = 0, $dowhat = '')
	{
		global $ilance;
		if (!empty($dowhat))
		{
			switch ($dowhat)
			{
				case 'increase':
				{
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET bidstoday = bidstoday + 1,
						bidsthismonth = bidsthismonth + 1
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					break;
				}
				case 'decrease':
				{
					$sql = $ilance->db->query("
						SELECT bidstoday, bidsthismonth
						FROM " . DB_PREFIX . "users
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					$res = $ilance->db->fetch_array($sql);
					$bidstoday = $res['bidstoday'];
					$bidsthismonth = $res['bidsthismonth'];
					$newtoday = ($bidstoday - 1);
					$newthismonth = ($bidsthismonth - 1);
					if ($newtoday < 0)
					{
					    $newtoday = 0;
					}
					if ($newthismonth < 0)
					{
					    $newthismonth = 0;
					}
					$ilance->db->query("
						UPDATE " . DB_PREFIX . "users
						SET bidstoday = '" . $newtoday . "',
						bidsthismonth = '" . $newthismonth . "'
						WHERE user_id = '" . intval($userid) . "'
					", 0, null, __FILE__, __LINE__);
					break;
				}
			}
		}
	}
	
	/**
	* Function to fetch the average bid amount price for a particular service auction event.
	*
	* @param       integer        project id
	* @param       boolean        force no privacy (ie: will not show sealed if it is sealed)
	* @param       string         bid details to do the checkup on
	*
	* @return      string         Returns formatted average bid price amount
	*/
	function fetch_average_bid($projectid = 0, $noprivacy = true, $bid_details = '', $noformatting = false)
	{
		global $ilance, $phrase;
		$table = ($ilance->categories->bidgrouping(fetch_auction('cid', $projectid)) == '0') ? 'project_realtimebids' : 'project_bids';
		$currencyid = $noformatting ? '1' : fetch_auction('currencyid', $projectid);
		$sql = $ilance->db->query("
			SELECT AVG(bidamount) AS average
			FROM " . DB_PREFIX . $table . "
			WHERE project_id = '" . intval($projectid) . "'
				AND bidstate != 'retracted'
				AND bidstatus != 'declined'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			if ($noprivacy)
			{
				if ($noformatting)
				{
					return $res['average'];
				}
				else
				{
					return print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['average'], $currencyid);
				}
			}
			else
			{
				if (!empty($bid_details) AND $bid_details == 'open' OR $bid_details == 'blind')
				{
					if ($noformatting)
					{
						return $res['average'];
					}
					else
					{
						return print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['average'], $currencyid);
					}
				}
				else
				{
					return '= {_sealed} =';
				}
			}
		}
		if ($noformatting)
		{
			return 0;
		}
		else
		{
			return print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], 0, $currencyid);
		}
	}
	
	/**
	* Function to fetch the declined bids count for a particular service auction event.
	*
	* @param       integer        project id
	*
	* @return      string         Returns delicned bids count
	*/
	function fetch_declined_bids($projectid = 0)
	{
		global $ilance;
		$declined = 0;
		$sql = $ilance->db->query("
			SELECT bidsdeclined
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "' LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$declined = $res['bidsdeclined'];
		}
		return (int) $declined;
	}
	
	/**
	* Function to fetch the declined bids count for a particular service auction event.
	*
	* @param       integer        project id
	*
	* @return      string         Returns delicned bids count
	*/
	function fetch_retracted_bids($projectid = 0)
	{
		global $ilance;
		$retracted = 0;
		$sql = $ilance->db->query("
			SELECT bidsretracted
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "' LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$retracted = $res['bidsretracted'];
		}
		return (int) $retracted;
	}
	
	/**
	* Function to fetch the declined bids count for a particular service auction event.
	*
	* @param       integer        project id
	* @param       integer        buyer id
	*
	* @return      string         Returns delicned bids count
	*/
	function fetch_shortlisted_bids($projectid = 0, $buyerid = 0)
	{
		global $ilance;
		$shortlisted = 0;
		if (isset($buyerid) AND $buyerid > 0)
		{
			$sql = $ilance->db->query("
				SELECT bidsshortlisted
				FROM " . DB_PREFIX . "projects
				WHERE project_id = '" . intval($projectid) . "'
					AND user_id = '" . intval($buyerid) . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				$shortlisted = $res['bidsshortlisted'];
			}
		}
		return (int) $shortlisted;
	}
	
	/**
	* Function to find any awarded projects and unaward them due to expiry of awarding set by admin
	*
	* @param       integer        days to determine if the expiry has  been met
	*
	* @return      string         Returns true or false
	*/
	function wait_approval_unaward_cron()
	{
		global $ilance, $ilconfig, $phrase;
		$html = '';
		$sql = $ilance->db->query("
			SELECT project_id, user_id, close_date
			FROM " . DB_PREFIX . "projects
			WHERE status = 'wait_approval'
				AND close_date != '0000-00-00 00:00:00'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				// determine if this project has waited more than "admin defined days" if so, unaward the providers bid automatically
				$date1split = explode(' ', $res['close_date']);
				$date2split = explode('-', $date1split[0]);
				$duration = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
				if ($duration == 0)
				{
					$duration = 1;
				}
				// we'll only unaward if for example the day this logic is running on: Monday through Friday.
				// if the function cannot run on Friday (6 day of the 7 total for example) then it will be caught up again on the following Monday
				// because this will be 9 days of 7 total and it will expire if it hasn't been awarded over the weekend.
				$temp = $ilance->datetimes->is_business_day();
				$isbusinessday = $temp[0];
				unset($temp);
				if ($duration >= $ilconfig['servicebid_awardwaitperiod'] AND $isbusinessday)
				{
					// we're ready to unaward this provider's bid
					// find out who's taking so long.....
					$sqlbid = $ilance->db->query("
						SELECT bid_id
						FROM " . DB_PREFIX . "project_bids
						WHERE project_id = '" . $res['project_id'] . "'
							AND bidstatus = 'placed'
							AND bidstate = 'wait_approval'
							AND state = 'service'
							AND date_awarded != '0000-00-00 00:00:00'
							AND project_user_id = '" . $res['user_id'] . "'
						LIMIT 1
					");
					if ($ilance->db->num_rows($sqlbid) > 0)
					{
						$resbid = $ilance->db->fetch_array($sqlbid, DB_ASSOC);
						$ilance->auction_award->unaward_service_auction($resbid['bid_id']);
						$html .= 'Automatic provider unaward for project ' . $res['project_id'] . ' - provider did not accept buyers award in ' . $days . ' days time, ';
					}
				}
			}
		}
		return $html;
	}
	
	/**
	* Function for determining if a user can bid based on the auction posters filtered profile answer logic.
	* For example, if a profile question is "Hair Color" and the user placing a bid answered this previously saying "Red"
	* then the auction poster can also filter his auction bids based on users only having the hair color of "Red" which they can place a bid.
	* If the bidder has "Black" hair he / she will not be able to bid.
	*
	* @param       integer      user id
	* @param       integer      project id
	* @param       boolean      show stop template? (default true)
	*
	* @return      string       Returns true or false
	*/
	function user_can_bid($userid = 0, $projectid = 0, $showtemplate = true)
	{
		global $ilance, $phrase, $ilpage;
		$note = '';
		$sql = $ilance->db->query("
			SELECT answerid, questionid, project_id, user_id, answer, filtertype, date, visible
			FROM " . DB_PREFIX . "profile_filter_auction_answers
			WHERE project_id = '" . intval($projectid) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$n = 1;
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$sql1 = $ilance->db->query("
					SELECT answerid, questionid, user_id, answer, date, visible, isverified, verifyexpiry, invoiceid, contactname, contactnumber, contactnotes
					FROM " . DB_PREFIX . "profile_answers
					WHERE user_id = '" . intval($userid) . "'
						AND questionid = '" . intval($res['questionid']) . "'
						AND visible = 1
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql1) > 0)
				{
					// #### RANGE (from/to) ########################
					if ($res['filtertype'] == 'range')
					{
						$res1 = $ilance->db->fetch_array($sql1, DB_ASSOC);
						// answer would be formatted like 1|5
						$choices = explode('|', $res['answer']);
						if (!($res1['answer'] >= $choices[0] AND $res1['answer'] <= $choices[1]))
						{
							$title = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res1['questionid'] . "'", "question");
							$desc = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res1['questionid'] . "'", "description");
							$gid = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res1['questionid'] . "'", "groupid");
							$cid = $ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $gid . "'", "cid");
							$note .= '<div>' . $n . '. <strong>' . $title . '</strong> (' . $desc . '): <span class="gray">{_between_upper} ' . $choices[0] . ' {_and} ' . $choices[1] . '.</span></div>';
							$n++;
						}
					}
					// #### CHECKBOX (pulldown, multiple choice ####
					else if ($res['filtertype'] == 'checkbox')
					{
						$res1 = $ilance->db->fetch_array($sql1, DB_ASSOC);
						$choices = explode('|', trim($res['answer'])); // Array ( [0] => 1-5 years )
						if (!in_array(trim($res1['answer']), $choices)) // 1-5 years
						{
							$title = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res1['questionid'] . "'", "question");
							$gid = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res1['questionid'] . "'", "groupid");
							$cid = $ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $gid . "'", "cid");
							$note .= '<div>' . $n . '. <strong>' . $title . '</strong>: <span class="gray">' . str_replace('|', ', ', trim($res['answer'])) . '.</span></div>';
							$n++;
						}
					}
				}
				else
				{
					$title = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res['questionid'] . "'", "question");
					$gid = $ilance->db->fetch_field(DB_PREFIX . "profile_questions", "questionid = '" . $res['questionid'] . "'", "groupid");
					$cid = $ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '" . $gid . "'", "cid");
					$note .= '<div>{_question}: <strong>' . $title . '</strong></div>';
				}
			}
			if (!empty($note))
			{
				if ($showtemplate)
				{
					$area_title = '{_access_to_bid_is_denied}';
					$page_title = SITE_NAME . ' - {_access_to_bid_is_denied}';
					print_notice('{_access_denied}', '{_sorry_but_you_did_not_answer_specific_profile_questions_or_your_answer_does_not_match_with_poster_requirements}<br />' . $note . '<br />{_please_visit_your_selling_profile_area_to_answer_various_questions_relating_to_this_posters_requirement}', $ilpage['selling'] . '?cmd=profile&cid=' . $cid . '#categories', '{_selling_profile}');
					exit();
				}
				else
				{
					return false;
				}
			}
			return true;
		}
	}
	
	/**
	* Function to print out the bid transaction status for a listing that is won by a winning bidder.
	*
	* @param      integer      project id
	* @param      boolean      show short form phrase only? (default false)
	* @param      boolean      show icon only (default false)
	* @param      boolean      show invoice id with link to transaction page (to make payment for invoice IF unpaid)? (default false)
	*
	* @return     string       HTML formatted representation of the response
	*/
	function fetch_transaction_status($projectid = 0, $shortform = false, $showicononly = false, $showlinktopayment = false)
	{
		global $ilance, $ilpage, $phrase, $show, $ilconfig;
		$show['directpay'] = $show['markpaidcompleted'] = $show['directpaycompleted'] = false;
		$html = '';
		$orderids = array ();
		$buyerid = $orders = 0;
		$userid = !empty($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;
		$sql = $ilance->db->query("
			SELECT user_id, project_details, haswinner, filter_escrow, escrow_id, winner_user_id, hasbuynowwinner
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) == 0)
		{
			return $html;
		}
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		$owner_id = $res['user_id'];
		$project_details = $res['project_details'];
		$haswinner = $res['haswinner'];
		$filter_escrow = $res['filter_escrow'];
		$escrow_id = $res['escrow_id'];
		$bidderid = $res['winner_user_id'];
		$hasbuynowwinner = $res['hasbuynowwinner'];
		// count how many buy now orders exist for this item
		$sql = $ilance->db->query("
			SELECT orderid, buyer_id
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($projectid) . "'
				AND owner_id = '" . intval($owner_id) . "'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				if ($res['buyer_id'] == $userid)
				{
					$orderids[] = $res['orderid'];
					$buyerids[] = $res['buyer_id'];
					$orders++;
				}
				else if ($owner_id == $userid)
				{
					$orderids[] = $res['orderid'];
					$buyerids[] = $res['buyer_id'];
					$orders++;
				}
			}
		}
		if ($orders == 1)
		{
			$buyerid = $buyerids[0];
		}
		else if ($orders > 1)
		{
			foreach ($buyerids AS $buyeridtemp)
			{
				if ($buyeridtemp == $userid)
				{
					$buyerid = $buyeridtemp;
					break;
				}
			}
		}
		// #### see if we're a winning bidder (regular auction
		if ($bidderid > 0 AND $haswinner > 0 AND ($project_details == 'public' OR $project_details == 'realtime'))
		{
			$sql = $ilance->db->query("
				SELECT winnermarkedaspaid, winnermarkedaspaiddate, winnermarkedaspaidmethod
				FROM " . DB_PREFIX . "project_bids
				WHERE project_id = '" . intval($projectid) . "'
					AND user_id = '" . $bidderid . "'
					AND bidstatus = 'awarded'
					AND state = 'product'
			");
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$winnermarkedaspaid = $res['winnermarkedaspaid'];
			$winnermarkedaspaiddate = $res['winnermarkedaspaiddate'];
			$winnermarkedaspaidmethod = $res['winnermarkedaspaidmethod'];
			// #### using escrow ###################################
			if ($filter_escrow == 1 AND $escrow_id > 0)
			{
				if ($winnermarkedaspaiddate != '0000-00-00 00:00:00')
				{
					if ($showicononly)
					{
						$html .= '<span title="{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></span>';
					}
					else if ($shortform)
					{
						$html .= '{_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span>';
					}
					else
					{
						$html .= '{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span>';
					}
					$show['markpaidcompleted'] = true;
				}
				else
				{
					if ($showicononly)
					{
						$html .= '<span title="{_the_transaction_associated_with_this_listing_has_not_been_paid}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></span>';
					}
					else if ($shortform)
					{
						$html .= '{_pending_payment}';
					}
					else
					{
						$html .= '{_the_transaction_associated_with_this_listing_has_not_been_paid}';
					}
					$show['markpaidcompleted'] = false;
				}
			}
			// #### not using escrow ###############################
			else
			{
				if ($winnermarkedaspaiddate != '0000-00-00 00:00:00')
				{
					if ($showicononly)
					{
						$html .= '<span title="{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></span>';
					}
					else if ($shortform)
					{
						$html .= '{_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span>';
					}
					else
					{
						$html .= '{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span>';
					}
					$show['markpaidcompleted'] = true;
				}
				else
				{
					if ($showicononly)
					{
						$html .= '<span title="{_the_transaction_associated_with_this_listing_has_not_been_paid}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></span>';
					}
					else if ($shortform)
					{
						$html .= '{_pending_payment}';
					}
					else
					{
						$html .= '{_the_transaction_associated_with_this_listing_has_not_been_paid}';
					}
					$show['markpaidcompleted'] = false;
				}
			}
			unset($winnermarkedaspaid, $winnermarkedaspaiddate, $winnermarkedaspaidmethod);
		}
		// #### see if we're a buy now purchaser
		if ($orders > 0 AND $hasbuynowwinner > 0)
		{
			if ($orders > 0)
			{
				if ($orders == 1)
				{
					$sql = $ilance->db->query("
						SELECT winnermarkedaspaid, winnermarkedaspaiddate, winnermarkedaspaidmethod, buyerpaymethod
						FROM " . DB_PREFIX . "buynow_orders
						WHERE project_id = '" . intval($projectid) . "'
							AND buyer_id = '" . $buyerid . "'
							AND orderid = '" . intval($orderids[0]) . "'
					");
					$res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$winnermarkedaspaid = $res['winnermarkedaspaid'];
					$winnermarkedaspaiddate = $res['winnermarkedaspaiddate'];
					$winnermarkedaspaidmethod = $res['winnermarkedaspaidmethod'];
					$buyerpaymethod = $res['buyerpaymethod'];
					if ($winnermarkedaspaiddate != '0000-00-00 00:00:00')
					{
						if ($showicononly)
						{
							$html .= '<span title="{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></span>';
						}
						else if ($shortform)
						{
							$html .= '{_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate);
						}
						else
						{
							$html .= '{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span>';
						}
						if (strrchr($buyerpaymethod, 'gateway'))
						{
							$show['directpay'] = true;
							$show['directpaycompleted'] = true;
							$show['markpaidcompleted'] = true;
						}
						else if (strrchr($buyerpaymethod, 'offline'))
						{
							$show['markpaidcompleted'] = true;
						}
						else if ($buyerpaymethod == 'escrow')
						{
							$show['markpaidcompleted'] = true;
						}
					}
					else
					{
						if ($showicononly)
						{
							$html .= '<span title="{_the_transaction_associated_with_this_listing_has_not_been_paid}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></span>';
						}
						else if ($shortform)
						{
							$html .= '{_pending_payment}';
						}
						else
						{
							$html .= '{_the_transaction_associated_with_this_listing_has_not_been_paid}';
						}
						if (strrchr($buyerpaymethod, 'gateway'))
						{
							$show['directpay'] = false;
							$show['directpaycompleted'] = false;
							$show['markpaidcompleted'] = false;
						}
						else if (strrchr($buyerpaymethod, 'offline'))
						{
							$show['markpaidcompleted'] = false;
						}
						else if ($buyerpaymethod == 'escrow')
						{
							$show['markpaidcompleted'] = false;
						}
					}
				}
				else
				{
					// handle multiple orders for viewing buyer or seller
					$x = 1;
					for ($i = 0; $i < count($orderids); $i++)
					{
						$sql = $ilance->db->query("
							SELECT winnermarkedaspaid, winnermarkedaspaiddate, winnermarkedaspaidmethod, buyerpaymethod
							FROM " . DB_PREFIX . "buynow_orders
							WHERE project_id = '" . intval($projectid) . "'
								AND buyer_id = '" . intval($buyerids[$i]) . "'
								AND orderid = '" . intval($orderids[$i]) . "'
						");
						$res = $ilance->db->fetch_array($sql, DB_ASSOC);
						$winnermarkedaspaid = $res['winnermarkedaspaid'];
						$winnermarkedaspaiddate = $res['winnermarkedaspaiddate'];
						$winnermarkedaspaidmethod = $res['winnermarkedaspaidmethod'];
						$buyerpaymethod = $res['buyerpaymethod'];
						if ($winnermarkedaspaiddate != '0000-00-00 00:00:00')
						{
							if ($showicononly)
							{
								$html = '<span title="{_the_transaction_associated_with_this_listing_was_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate) . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/checkmark.gif" border="0" alt="" /></span>';
							}
							else if ($shortform)
							{
								$html = '{_marked_as_paid_on} ' . print_date($winnermarkedaspaiddate);
							}
							else
							{
								$html .= '<div style="padding-left:12px"><span class="black">' . $x . '.</span> <span class="blue">{_order} ID #' . $orderids[$i] . ':</span> {_the_transaction_associated_with_this_listing_was_marked_as_paid_on} <span>' . print_date($winnermarkedaspaiddate) . '</span></div>';
							}
							if (strrchr($buyerpaymethod, 'gateway'))
							{
								$show['directpay'] = true;
								$show['directpaycompleted'] = true;
								$show['markpaidcompleted'] = true;
							}
							else if (strrchr($buyerpaymethod, 'offline'))
							{
								$show['markpaidcompleted'] = true;
							}
							else if ($buyerpaymethod == 'escrow')
							{
								$show['markpaidcompleted'] = true;
							}
							$x++;
						}
						else
						{
							if ($showicononly)
							{
								$html = '<span title="{_the_transaction_associated_with_this_listing_has_not_been_paid}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></span>';
							}
							else if ($shortform)
							{
								$html = '{_pending_payment}';
							}
							else
							{
								if ($owner_id == $_SESSION['ilancedata']['user']['userid'])
								{
									$html .= '<div style="padding-left:12px"><span class="black">' . $x . '.</span> <span class="blue">{_order} ID #' . $orderids[$i] . ':</span> {_the_transaction_associated_with_this_listing_has_not_been_paid}</div>';
								}
								else
								{
									$html .= '<div style="padding-left:12px"><span class="black">' . $x . '.</span> <span class="blue">{_order} ID #' . $orderids[$i] . ':</span> {_the_transaction_associated_with_this_listing_has_not_been_paid} : <input type="button" value=" {_pay_now} " onclick="location.href=\'' . HTTPS_SERVER . $ilpage['merch'] . '?cmd=directpay&amp;subcmd=choose&amp;id=' . intval($projectid) . '&amp;orderid=' . intval($orderids[$i]) . '\'" class="buttons_smaller" style="font-size:9px" /></div>';
								}
							}
							if (strrchr($buyerpaymethod, 'gateway'))
							{
								$show['directpay'] = false;
								$show['directpaycompleted'] = false;
								$show['markpaidcompleted'] = false;
							}
							else if (strrchr($buyerpaymethod, 'offline'))
							{
								$show['markpaidcompleted'] = false;
							}
							else if ($buyerpaymethod == 'escrow')
							{
								$show['markpaidcompleted'] = false;
							}
							$x++;
						}
					}
				}
				unset($winnermarkedaspaid, $winnermarkedaspaiddate, $winnermarkedaspaidmethod, $buyerpaymethod);
			}
		}
		return $html;
	}
	
	/**
	* Function to print out the bid amount taking into consideration sealed and blind bidding privacy filters
	*
	* @param      string       bid privacy details
	* @param      integer      bid amount
	* @param      integer      bidder user id
	* @param      integer      owner user id
	* @param      integer      currency id of listing
	*
	* @return     string       HTML formatted representation of the amount
	*/
	function fetch_bid_amount($bid_details = '', $bidamount = 0, $bidderid = 0, $ownerid = 0, $currencyid = 0)
	{
		global $ilance, $phrase, $ilconfig;
		if ($currencyid == 0)
		{
			$currencyid = $ilconfig['globalserverlocale_defaultcurrency'];
		}
		// no bidding privacy
		if ($bid_details == 'open' OR $bid_details == 'blind')
		{
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
			{
				$bidamount = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $bidamount, $currencyid);
			}
			else
			{
				$bidamount = $ilance->currency->format($bidamount, $currencyid);
			}
		}
		// sealed or full bidding privacy
		else if ($bid_details == 'sealed' OR $bid_details == 'full')
		{
			// guest
			if (empty($_SESSION['ilancedata']['user']['userid']))
			{
				$bidamount = '= {_sealed} =';
			}
			// member
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] != $bidderid AND $_SESSION['ilancedata']['user']['userid'] != $ownerid)
			{
				$bidamount = '= {_sealed} =';
			}
			// bidder
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $bidderid AND $_SESSION['ilancedata']['user']['userid'] != $ownerid)
			{
				if ($ilance->permissions->check_access($_SESSION['ilancedata']['user']['userid'], 'enablecurrencyconversion') == 'yes')
				{
					$bidamount = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $bidamount, $currencyid);
				}
				else
				{
					$bidamount = $ilance->currency->format($bidamount, $currencyid);
				}
			}
			// owner
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $ownerid AND $_SESSION['ilancedata']['user']['userid'] != $bidderid)
			{
				$bidamount = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $bidamount, $currencyid);
			}
			// admin
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				$bidamount = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $bidamount, $currencyid);
			}
		}
		return $bidamount;
	}
	
	/**
	* Function to fetch and return an array of the average, lowest and highest bid amounts placed on a listing event
	*
	* @param      string       bid privacy details
	* @param      integer      listing id
	* @param      integer      owner user id
	*
	* @return     array        Mixed array of amounts requested
	*/
	function fetch_average_lowest_highest_bid_amounts($bid_details = '', $project_id = 0, $ownerid = 0)
	{
		global $ilance, $phrase;
		$currencyid = fetch_auction('currencyid', $project_id);
		$sql = $ilance->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($project_id) . "'
				AND project_state = 'service'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$bidgrouping = $ilance->categories->bidgrouping($res['cid']);
			$bids_table = ($bidgrouping) ? 'project_bids' : 'project_realtimebids';
		}
		else
		{
			$bids_table = 'project_bids';
		}
		// format average bid amount
		$sel_bids_av = $ilance->db->query("
			SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest 
			FROM " . DB_PREFIX . $bids_table . "
			WHERE project_id = '" . intval($project_id) . "'
				AND bidstate != 'retracted'
				AND bidstatus != 'declined'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		$res_bids_av = $ilance->db->fetch_array($sel_bids_av, DB_ASSOC);
		$bidprivacy = $average = $lowest = $highest = '';
		if ($bid_details == 'open')
		{
			$bidprivacy = '{_no_privacy}';
			$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
			$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
			$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
		}
		// #### BLIND BIDS #####################################
		else if ($bid_details == 'blind')
		{
			$bidprivacy = '{_blind_bidding}';
			$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
			$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
			$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
		}
		// #### SEALED BIDS ####################################
		else if ($bid_details == 'sealed')
		{
			$bidprivacy = '{_sealed_bidding}';
			// #### OWNER VIEWING ##########################
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $ownerid)
			{
				$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
				$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
				$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
			}
			// #### ADMIN VIEWING ##########################
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
				$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
				$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
			}
			// #### EVERYONE ELSE ##########################
			else
			{
				$average = '= {_sealed} =';
				$lowest = '= {_sealed} =';
				$highest = '= {_sealed} =';
			}
		}
		// #### FULL PRIVACY (SEALED + BLIND) ##################
		else if ($bid_details == 'full')
		{
			$bidprivacy = '{_full_privacy}';
			// #### OWNER VIEWING ##########################
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] == $ownerid)
			{
				$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
				$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
				$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
			}
			// #### ADMIN VIEWING ##########################
			else if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['isadmin'] == '1')
			{
				$average = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['average'], $currencyid);
				$lowest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['lowest'], $currencyid);
				$highest = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res_bids_av['highest'], $currencyid);
			}
			// #### EVERYONE ELSE ##########################
			else
			{
				$average = '= {_sealed} =';
				$lowest = '= {_sealed} =';
				$highest = '= {_sealed} =';
			}
		}
		return array (
			'bidprivacy' => $bidprivacy,
			'average' => $average,
			'lowest' => $lowest,
			'highest' => $highest
		);
	}
	
	/**
	* Function to fetch and return an array of the lowest bidder details for a particular listing event
	*
	* @param      integer      listing id
	* @param      integer      owner user id
	* @param      string       bid privacy details
	*
	* @return     array        Mixed array of amounts requested
	*/
	function fetch_lowest_bidder_info($project_id = 0, $user_id = 0, $bid_details = '')
	{
		global $ilance, $ilconfig, $show, $phrase;
		$lowbidder = $lowbidderid = '';
		$show['lowbidder_active'] = false;
		$sql = $ilance->db->query("
			SELECT cid
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($project_id) . "'
				AND project_state = 'service'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$bidgrouping = $ilance->categories->bidgrouping($res['cid']);
			$bids_table = ($bidgrouping) ? 'project_bids' : 'project_realtimebids';
		}
		else
		{
			$bids_table = 'project_bids';
		}
		$sql_lowest_bidder = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . $bids_table . "
			WHERE project_id = '" . intval($project_id) . "'
			ORDER BY bidamount ASC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql_lowest_bidder) > 0)
		{
			$res_lowest_bidder = $ilance->db->fetch_array($sql_lowest_bidder, DB_ASSOC);
			$sel_bids_av = $ilance->db->query("
				SELECT AVG(bidamount) AS average, MIN(bidamount) AS lowest, MAX(bidamount) AS highest 
				FROM " . DB_PREFIX . $bids_table . "
				WHERE project_id = '" . intval($project_id) . "'
					AND bidstate != 'retracted'
					AND bidstatus != 'declined'
				ORDER BY lowest 
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sel_bids_av) > 0)
			{
				$res_bids_av = $ilance->db->fetch_array($sel_bids_av, DB_ASSOC);
				$show['lowbidder_active'] = true;
				$lowbidderid = $res_lowest_bidder['user_id'];
				$lowbidder = fetch_user('username', $lowbidderid);
				// fetch lowest bidder name
				if ($bid_details == 'blind' OR $bid_details == 'full')
				{
					if (!empty($_SESSION['ilancedata']['user']['userid']))
					{
						if ($user_id != $_SESSION['ilancedata']['user']['userid'])
						{
							$lowbidder = '= {_blind} =';
						}
					}
					else
					{
						$lowbidder = '= {_blind} =';
					}
				}
				return array (
					'lowbidderid' => $lowbidderid,
					'lowbidder' => $lowbidder
				);
			}
		}
		return array (
			'lowbidderid' => '',
			'lowbidder' => ''
		);
	}
	
	/**
	* Function to determine if a user id for a particular project id is awarded
	*
	* @param	integer         project id
	* @param        integer         user id
	*
	* @return	boolean         Returns true if awarded, false if not
	*/
	function is_awarded($projectid, $userid)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($projectid) . "'
				AND user_id = '" . intval($userid) . "'
				AND bidstatus = 'awarded'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return 1;
		}
		return 0;
	}
	
	/**
	* Function to fetch bid information to be stored in a array for usage
	*
	* @param	integer         bid id
	* @param        string          return what? (measure, totalamount, totalamountinput)
	* @param        string          bid amount type
	* @param        integer         bid amount
	* @param        integer         estimate
	*
	* @return	boolean         Returns true if awarded, false if not
	*/
	function fetch_bid_info($bidid = 0, $what, $bidamounttype = 'entire', $bidamount = 0, $estimate = 0)
	{
		global $ilance;
		if (isset($what))
		{
			if ($what == 'measure')
			{
				$sql = $ilance->db->query("
					SELECT bidamounttype
					FROM " . DB_PREFIX . "project_bids
					WHERE bid_id = '" . intval($bidid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					$measure = $ilance->auction->construct_measure($res['bidamounttype']);
					return $measure;
				}
			}
			else if ($what == 'totalamount')
			{
				$sql = $ilance->db->query("
					SELECT bidamounttype, bidamount, estimate_days
					FROM " . DB_PREFIX . "project_bids
					WHERE bid_id = '" . intval($bidid) . "'
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$res = $ilance->db->fetch_array($sql);
					if ($res['bidamounttype'] == 'entire' OR $res['bidamounttype'] == 'lot' OR $res['bidamounttype'] == 'weight')
					{
						$total = $res['bidamount'];
					}
					else
					{
						$total = ($res['bidamount'] * $res['estimate_days']);
					}
					return $total;
				}
			}
			else if ($what == 'totalamountinput' AND !empty($bidamounttype) AND !empty($bidamount) AND !empty($estimate))
			{
				if ($bidamounttype == 'entire' OR $bidamounttype == 'lot' OR $bidamounttype == 'weight')
				{
					$total = $bidamount;
				}
				else
				{
					$total = ($bidamount * $estimate);
				}
				return $total;
			}
			else
			{
				$canquery = array ('user_id', 'bidamount', 'estimate_days', 'bidstatus', 'bidstate', 'bidamounttype', 'date_added');
				if (in_array($what, $canquery) AND $bidid > 0)
				{
					$sql = $ilance->db->query("
						SELECT $what
						FROM " . DB_PREFIX . "project_bids
						WHERE bid_id = '" . intval($bidid) . "'
						LIMIT 1
					", 0, null, __FILE__, __LINE__);
					if ($ilance->db->num_rows($sql) > 0)
					{
						$res = $ilance->db->fetch_array($sql);
						return $res["$what"];
					}
				}
			}
		}
	}
	
	/**
	* Function to determine if a particular user has placed a bid on a specific auction id
	*
	* @param       integer        project id
	* @param       integer        user id
	*
	* @return      bool           Returns true for yes, false for no
	*/
	function is_bid_placed($projectid = 0, $userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE user_id = '" . intval($userid) . "'
				AND project_id = '" . intval($projectid) . "'
				AND bidstate != 'retracted'
				AND bidstatus != 'declined'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			return true;
		}
		return false;
	}
	/**
	* Function to determine if a particular user owner a specific bid id
	*
	* @param       integer        bid id
	*
	* @return      bool           Returns true for yes, false for no
	*/
	function fetch_bid_owner($bidid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE bid_id = '" . intval($bidid) . "'
				AND bidstate != 'retracted'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['user_id'];
		}
		return 0;
	}
	/**
	* Function to display either "There are x other bidders in this auction" or "You are the only bidder with x bids placed"
	*
	* @param       integer        user id
	* @param       integer        project id
	*
	* @return      bool           Returns an HTML formatted string
	*/
	function print_other_bidders_verbose($userid = 0, $pid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_bids
			WHERE project_id = '" . intval($pid) . "'
				AND bidstate != 'retracted'
			GROUP BY user_id
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			if ($ilance->db->num_rows($sql) == 1)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				if ($res['user_id'] == $userid)
				{
					$bids = fetch_auction('bids', $pid);
					$p = $ilance->language->construct_phrase('{_you_are_only_bidder_x_placed}', $bids);
					return $p;
				}
			}
			else if ($ilance->db->num_rows($sql) > 1)
			{
				$c = ($ilance->db->num_rows($sql) - 1);
				if ($c == 1)
				{
					$p = '{_there_is_one_other_bidder}';
				}
				else if ($c > 1)
				{
					$p = $ilance->language->construct_phrase('{_there_are_x_other_bidders}', $ilance->db->num_rows($sql));
				}
				return $p;
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