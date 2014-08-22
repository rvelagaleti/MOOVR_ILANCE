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
* Auction listing class to perform the majority of printing and displaying of auction listing page in the front end.
* This extended class deals with AJAX and non-AJAX printing and displaying to the JS response scripts for display of
* the appropriate data. It was also added out of the need to handle better CPU resource management for larger sites
* with full ability for AJAX or not
*
* @package      iLance\Auction\Listing
* @version	4.0.0.8059
* @author       ILance
*/
class auction_listing extends auction
{	
	/**
	* Function to return template array data for a the buyer or seller currently being viewed.  This function is
	* usually called from the actual listing or profile page of the marketplace letting users see other related
	* listings from the same seller (product listings) or buyer (service auction listings)
	*
	* @param       integer       user id
	* @param       string        auction type (service or product)
	* @param       integer       auction result limit (default 5)
	* @param       array         array of project id's not to include in sellers listings
	* @param       boolean       force no-flash for auction timers (default no force)
	*
	* @return      string        Returns template array data for use with parse_loop() function
	*/
	function fetch_users_other_listings($userid = 0, $auctiontype = '', $limit = 5, $excludelist = array(), $forcenoflash = false)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		$otherlistings = array();
		$show['otherproductlistings'] = $show['otherservicelistings'] = false;
		$query_fields = $exclude = $excluded = $customquery = '';
		if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0)
		{
			foreach ($excludelist AS $projectid)
			{
				if (!empty($projectid) AND $projectid > 0)
				{
					$excluded .= " AND project_id != '" . intval($projectid) . "'";
				}
			}
		}

		($apihook = $ilance->api('fetch_users_other_listings_start')) ? eval($apihook) : false;

		if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$rowcount = 0;
			$default_row = array('photo' => '', 'photoplain' => '', 'title' => '', 'lowestprice' => '', 'price' => '', 'buynow' => '', 'timeleft' => '');
			$query = $ilance->db->query("
                                SELECT user_id, project_id, cid, project_title, views, $query_fields bids, currentprice, buynow_price, buynow_purchases, currencyid, buynow, date_starts, project_state, donation, charityid, description_videourl, filter_budget, featured, reserve, project_details, filtered_auctiontype, bid_details, filter_escrow, filter_gateway, paymethodoptions, buynow_qty_lot, items_in_lot, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE visible = '1'
					$excluded
					AND user_id = '" . intval($userid) . "'
					AND status = 'open'
					AND project_state = 'product'
					AND cid > 0
					$customquery
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (enhancementfee = 0 OR (enhancementfee > 0 AND enhancementfeeinvoiceid > 0 AND isenhancementfeepaid = '1')) AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "") . "
                                GROUP BY project_id
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				$show['otherproductlistings'] = true;
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['class'] = 'alt1';
					$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
					$res['title_plain'] = $res['project_title'];
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['title_plain'] . '">' . $res['project_title'] . '</a>';
					$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
					$res['photo'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['icons'] = $this->auction_icons($res);
					if ($res['buynow_price'] > 0 AND $res['buynow'])
					{
						if ($res['filtered_auctiontype'] == 'regular')
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							if ($res['buynow_price'] > $res['currentprice'])
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							}
							else
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							}
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
						}
						else 
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
						}
					}
					else
					{
						$res['buynow'] = '';
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
					}
					$rowcount++;
					$otherlistings[] = array('photo' => $res['photo'], 'photoplain' => $res['photoplain'], 'title' => $res['title'], 'lowestprice' => $res['lowestprice'], 'price' => $res['price'], 'buynow' => $res['buynow'], 'timeleft' => $res['timeleft']);
				}
			}
			else
			{
				$show['otherproductlistings'] = false;
			}
		}
		else if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$default_row = array('title' => '', 'description' => '', 'average' => '', 'timeleft' => '');
			$rowcount2 = $show['otherservicelistingcount'] = 0;
			$query2 = $ilance->db->query("
                                SELECT project_id, cid, project_title, description, views, bids, $query_fields user_id, filtered_budgetid, date_starts, project_state, donation, charityid, description_videourl, filter_budget, featured, reserve, project_details, filtered_auctiontype, bid_details, filter_escrow, filter_gateway, paymethodoptions, buynow_qty_lot, items_in_lot, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE visible = '1'
					$excluded    
					AND user_id = '" . intval($userid) . "'
					AND status = 'open'
					AND project_state = 'service'
					AND project_details = 'public'
					AND cid > 0
					$customquery
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (enhancementfee = 0 OR (enhancementfee > 0 AND enhancementfeeinvoiceid > 0 AND isenhancementfeepaid = '1')) AND (insertionfee = 0 OR (insertionfee > 0 AND ifinvoiceid > 0 AND isifpaid = '1'))" : "") . "
                                GROUP BY project_id
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);    
			if ($ilance->db->num_rows($query2) > 0)
			{
				$show['otherservicelistings'] = true;
				while ($resscook = $ilance->db->fetch_array($query2, DB_ASSOC))
				{
					$resscook['class'] = ($rowcount2 % 2) ? 'alt2' : 'alt1';
					$temp_title = $resscook['project_title'] = print_string_wrap($resscook['project_title'], '25');
					if ($ilconfig['globalfilters_auctiontitlecutoff'] != '0')
					{
						$temp_title = cutstring($resscook['project_title'], $ilconfig['globalfilters_auctiontitlecutoff']);
						if (strcmp($temp_title, $resscook['project_title']) != '0')
						{
							$temp_title = $temp_title . '...';
						}
					}
					$resscook['title'] = $ilconfig['globalauctionsettings_seourls'] ? construct_seo_url('serviceauction', 0, $resscook['project_id'], $resscook['project_title'], '', 0, '', 0, 0, '', '', $temp_title) : '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $resscook['project_id'] . '">' . $temp_title . '</a>';
					$resscook['description'] = $ilance->bbcode->strip_bb_tags(strip_tags($resscook['description']));
					$resscook['description'] = ($ilconfig['globalfilters_vulgarpostfilter']) ? strip_vulgar_words($resscook['description']) : $resscook['description'];
					$resscook['description'] = short_string($resscook['description'], $ilconfig['globalfilters_auctiondescriptioncutoff']);
					$resscook['description'] = handle_input_keywords($resscook['description']);
					$resscook['timeleft'] = $this->auction_timeleft(false, $resscook['date_starts'], $resscook['mytime'], $resscook['starttime']);
					$resscook['icons'] = $this->auction_icons($resscook);
					$resscook['budget'] = $this->construct_budget_overview($resscook['cid'], $resscook['filtered_budgetid'], true, true, true);
					$resscook['average'] = $ilance->bid->fetch_average_bid($resscook['project_id'], false, $resscook['bid_details'], false);
					$otherlistings[] = array('title' => $resscook['title'], 'description' => $resscook['description'], 'average' => $resscook['average'], 'timeleft' => $resscook['timeleft']);
					$rowcount2++;
				}
				$show['otherservicelistingcount'] = $rowcount2;
			}
			else
			{
				$show['otherservicelistings'] = false;
			}
		}
		$ilance->timer->stop();
		DEBUG("fetch_users_other_listings(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $otherlistings;
	}
	
	/**
        * Function to return template array data for recently viewed auctions stored in the users cookie.
        * This function additionally provides {photo} variable to let the designer show the related photo for the item being shown
        * within the HTML template.
        *
        * Function now takes into consideration if a member is not active (don't display the listing).  Additionally, this function
        * will no longer show "ended" listings so only active open listings will appear in the recently viewed blocks.
        *
        * @param       string        auction type
        * @param       integer       auction result limit (default 5)
        * @param       integer       auction rows limit (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_recently_viewed_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		$recentreviewedauctions = array();
		$query_fields = $cidcondition = $kwcondition = $subcategorylist = $leftjoin = $extracondition = '';
		$cidcondition = "AND p.cid > 0";
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
			$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
			$cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		}
		$kwcondition = !empty($keywords) ? "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')" : '';
		$limit = $columns * $rows;

		($apihook = $ilance->api('fetch_recently_viewed_auctions_start')) ? eval($apihook) : false;

		if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$leftjoin = "LEFT JOIN " . DB_PREFIX . "search_users su ON(p.project_id = su.project_id)";
				$pcookiesql = "AND su.uservisible = '1' AND su.user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			else if (defined('IPADDRESS') AND IPADDRESS != '')
			{
				$leftjoin = "LEFT JOIN " . DB_PREFIX . "search_users su ON(p.project_id = su.project_id)";
				$pcookiesql = "AND su.uservisible = '1' AND su.ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			}
			$query = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.user_id, p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.buynow_price, p.date_added, p.highlite, p.project_details, p.views, $query_fields p.bids, p.currentprice, p.currencyid, p.buynow, p.filtered_auctiontype, p.buynow_purchases, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.donation, p.filter_budget, p.reserve, p.project_state, p.bid_details, p.filter_escrow, p.filter_gateway, p.charityid
				FROM " . DB_PREFIX . "projects AS p
				LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
				$leftjoin
				WHERE p.visible = '1'
					$pcookiesql
					$cidcondition
					$kwcondition
					$extracondition
					AND p.status = 'open'
					AND project_state = 'product'
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
				ORDER BY su.id DESC, p.date_end DESC
				LIMIT $limit
			", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($query);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['class'] = 'alt1';
					$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
					$res['title_plain'] = $res['project_title'];
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['title_plain'] . '">' . $res['project_title'] . '</a>';
					$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
					$res['photo'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['icons'] = $this->auction_icons($res);
					if ($res['buynow_price'] > 0 AND $res['buynow'])
					{
						if ($res['filtered_auctiontype'] == 'regular')
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							if ($res['buynow_price'] > $res['currentprice'])
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							}
							else
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							}
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
						}
						else 
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
						}
					}
					else
					{
						$res['buynow'] = '';
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
					}
					$resrows++;
					$recentreviewedauctions[] = $res;
				}
			}
		}
		if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			if (!empty($_SESSION['ilancedata']['user']['userid']) AND $_SESSION['ilancedata']['user']['userid'] > 0)
			{
				$leftjoin = "LEFT JOIN " . DB_PREFIX . "search_users su ON(p.project_id = su.project_id)";
				$scookiesql = "AND su.uservisible = '1' AND su.user_id = '" . $ilance->db->escape_string($_SESSION['ilancedata']['user']['userid']) . "'";
			}
			else if (defined('IPADDRESS') AND IPADDRESS != '')
			{
				$leftjoin = "LEFT JOIN " . DB_PREFIX . "search_users su ON(p.project_id = su.project_id)";
				$scookiesql = "AND su.uservisible = '1' AND su.ipaddress = '" . $ilance->db->escape_string(IPADDRESS) . "'";
			}
			$query2 = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.highlite, p.views, p.bids, $query_fields p.user_id, p.bid_details, p.project_details, p.currencyid, p.filtered_budgetid, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, u.status
				FROM " . DB_PREFIX . "projects AS p
				LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
				$leftjoin
				WHERE p.visible = '1'
					$scookiesql
					$cidcondition
					$kwcondition
					$extracondition
					AND p.status = 'open'
					AND project_state = 'service'
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
				ORDER BY su.id DESC, p.date_end DESC
				LIMIT $limit
			", 0, null, __FILE__, __LINE__);    
			$rowstotal = $ilance->db->num_rows($query2);
			$resrows = 0;
			if ($rowstotal > 0)
			{
				$user_id = (isset($_SESSION['ilancedata']['user']['userid'])) ? $_SESSION['ilancedata']['user']['userid']  : '0';
				while ($res = $ilance->db->fetch_array($query2, DB_ASSOC))
				{
					if ($res['project_details'] == 'invite_only')
					{
						$invite_query = $ilance->db->query("SELECT seller_user_id FROM " . DB_PREFIX ."project_invitations WHERE project_id = '" . $res['project_id'] . "' AND seller_user_id = '" . $user_id ."'", 0, null, __FILE__, __LINE__);
						$fetch_invite_query = $ilance->db->num_rows($invite_query);
						$allow_access = ($fetch_invite_query > 0) ? '1' : '0';
					}
					else
					{
						$allow_access = 1;
					}
					$temp_title = $res['project_title'] = print_string_wrap($res['project_title'], '25');
					if ($ilconfig['globalfilters_auctiontitlecutoff'] != '0')
					{
						$temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_auctiontitlecutoff']);
						if (strcmp($temp_title, $res['project_title']) != '0')
						{
							$temp_title = $temp_title . '...';
						}
					}
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? '<span title="' . handle_input_keywords($res['project_title']) . '">' . construct_seo_url('serviceauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $temp_title) . '</span>' : '<span title="' . handle_input_keywords($res['project_title']) . '"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . $temp_title . '</a></span>';
					$res['description'] = $ilance->bbcode->strip_bb_tags(strip_tags($res['description']));
					$res['description'] = ($ilconfig['globalfilters_vulgarpostfilter']) ? strip_vulgar_words($res['description']) : $res['description'];
					$res['description'] = short_string($res['description'], $ilconfig['globalfilters_auctiondescriptioncutoff']);
					$res['description'] = handle_input_keywords($res['description']);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['timeleft'] = ($allow_access == 0) ? '' : $res['timeleft'];
					$res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], false);
					$res['average'] = ($allow_access == 0) ? '{_sealed}' : $res['average'];
					$res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
					$res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], true, true, true);
					$res['budget'] = ($allow_access == 0) ? '{_sealed}' : $res['budget'];
					$resrows++;
					$res['bids'] = ($allow_access == 0) ?  '{_sealed}' : $res['bids'];
					$recentreviewedauctions[] = $res;
				}
			}
		}
		$ilance->timer->stop();
		DEBUG("fetch_recently_viewed_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $recentreviewedauctions;
	}
	
	/**
        * Function to return template array data for recently viewed auctions stored in the users cookie.
        *
        * Function now takes into consideration if a member is not active (don't display the listing).
        *
        * @param       string        auction type
        * @param       integer       auction result columns (default 5)
        * @param       integer       auction result rows (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_ending_soon_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		if ($ilconfig['showendingsoonlistings'] == false)
		{
			return array();
		}
		$query_fields = $cidcondition = $kwcondition = $subcategorylist = $extracondition = '';
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
			$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
			$cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		}
		else
		{
			$cidcondition = "AND p.cid > 0";
		}
		$kwcondition = !empty($keywords) ? "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')" : '';
		$limit = $columns * $rows;

		($apihook = $ilance->api('fetch_ending_soon_auctions_start')) ? eval($apihook) : false;

		require_once(DIR_CORE . 'functions_search.php');

		/**
                -1 = any date, 1 = 1 hour, 2 = 2 hours, 3 = 3 hours, 4 = 4 hours, 5 = 5 hours, 6 = 12 hours, 7 = 24 hours, 8 = 2 days, 9 = 3 days, 10 = 4 days, 11 = 5 days, 12 = 6 days, 13 = 7 days, 14 = 2 weeks, 15 = 1 month
                */
		$endingsoon = array();
		$timeid = $ilconfig['globalauctionsettings_endsoondays'];
		if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$default_row = array('photo' => '', 'photoplain' => '', 'title' => '', 'lowestprice' => '', 'price' => '', 'buynow' => '', 'timeleft' => '');
			$query = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.user_id, p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.buynow_price, p.date_added, p.highlite, p.project_details, p.views, $query_fields p.bids, p.currentprice, p.currencyid, p.buynow, p.filtered_auctiontype, p.buynow_purchases, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.donation, p.filter_budget, p.reserve, p.project_state, p.bid_details, p.filter_escrow, p.filter_gateway, p.charityid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.visible = '1'
					AND p.status = 'open'
					AND p.project_state = 'product'
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . fetch_startend_sql($timeid, 'DATE_ADD', 'p.date_end', '<=') . "
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.date_end ASC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($query);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['class'] = 'alt1';
					$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
					$res['plain_title'] = $res['project_title'];
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['plain_title'] . '">' . $res['project_title'] . '</a>';
					$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
					$res['photo'] = ($ilconfig['globalauctionsettings_seourls']) ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['icons'] = $this->auction_icons($res);
					if ($res['buynow_price'] > 0 AND $res['buynow'])
					{
						if ($res['filtered_auctiontype'] == 'regular')
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']) ;
							$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							if ($res['buynow_price'] > $res['currentprice'])
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							}
							else
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							}
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
						}
						else 
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
						}
					}
					else
					{
						$res['buynow'] = '';
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
					}
					$resrows++;
					$endingsoon[] = array('photo' => $res['photo'], 'photoplain' => $res['photoplain'], 'title' => $res['title'], 'lowestprice' => $res['lowestprice'], 'price' => $res['price'], 'buynow' => $res['buynow'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$default_row = array('title' => '', 'description' => '', 'average' => '', 'timeleft' => '');
			$query = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.project_id, p.cid, p.project_title, p.description, p.additional_info, p.highlite, p.views, p.bids, $query_fields p.user_id, p.bid_details, p.currencyid, p.filtered_budgetid, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.visible = '1'
					AND p.status = 'open'
					AND p.project_state = 'service'
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . fetch_startend_sql($timeid, 'DATE_ADD', 'p.date_end', '<=') . "
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.date_end ASC
                                LIMIT $columns
                        ", 0, null, __FILE__, __LINE__);  
			$rowstotal = $ilance->db->num_rows($query);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$temp_title = $res['project_title'] = print_string_wrap($res['project_title'], '25');
					if ($ilconfig['globalfilters_auctiontitlecutoff'] != '0')
					{
						$temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_auctiontitlecutoff']);
						if (strcmp($temp_title, $res['project_title']) != '0')
						{
							$temp_title = $temp_title . '...';
						}
					}
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $temp_title) : '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . $temp_title . '</a>';
					$res['description'] = $ilance->bbcode->strip_bb_tags(strip_tags($res['description']));
					$res['description'] = ($ilconfig['globalfilters_vulgarpostfilter']) ? strip_vulgar_words($res['description']) : $res['description'];
					$res['description'] = short_string($res['description'], $ilconfig['globalfilters_auctiondescriptioncutoff']);
					$res['description'] = handle_input_keywords($res['description']);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], false);
					$res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
					$res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], true, true, true);
					$resrows++;
					$endingsoon[] = array('title' => $res['title'], 'description' => $res['description'], 'average' => $res['average'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		$ilance->timer->stop();
		DEBUG("fetch_ending_soon_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $endingsoon;
	}
	
	/**
        * Function to return template array data for featured auctions in picture grid mode.
        * This function also takes into consideration if a member is not active (don't display the listing).
        * 
        * @param       string        auction type
        * @param       integer       number of columns to display (default 4)
        * @param       integer       number of rows to display (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        * @param       array         excluded project ids (to prevent search results showing both featured and regular listings simultaneously)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_featured_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false, $excludelist = array())
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		if ($ilconfig['showfeaturedlistings'] == false)
		{
			return array();
		}
		$query_fields = $cidcondition = $kwcondition = $subcategorylist = $extracondition = '';
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
			$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
			$cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		}
		else
		{
			$cidcondition = "AND p.cid > 0";
		}
		$kwcondition = !empty($keywords) ? "AND (p.project_title LIKE '%" . $ilance->db->escape_string($keywords) . "%' OR p.description LIKE '%" . $ilance->db->escape_string($keywords) . "%')" : '';
		$limit = $columns * $rows;
		// build exclusion query bit to prevent the same listings as the one being viewed to show up
		$excluded = '';
		if (isset($excludelist) AND !empty($excludelist) AND is_array($excludelist) AND count($excludelist) > 0)
		{
			if (count($excludelist) == 1)
			{
				$excluded .= "AND p.project_id != '" . intval($excludelist[0]) . "'";
			}
			else if (count($excludelist) > 1)
			{
				$excluded .= "AND (";
				foreach ($excludelist AS $projectid)
				{
					if (!empty($projectid) AND $projectid > 0)
					{
						$excluded .= "p.project_id != '" . intval($projectid) . "' OR ";
					}
				}
				$excluded = substr($excluded, 0, -4);
				$excluded .= ")";
			}
		}

		($apihook = $ilance->api('fetch_featured_auctions_start')) ? eval($apihook) : false;

		$featuredauctions = array();
		if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$default_row = array('photo' => '', 'photoplain' => '', 'title' => '', 'lowestprice' => '', 'price' => '', 'buynow' => '', 'timeleft' => '', 'photoplain' => '');
			$sqlproductauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_id, p.project_title, p.description, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, p.buynow, p.buynow_qty, p.buynow_price, p.currentprice, $query_fields p.highlite, p.currencyid, p.buynow_purchases, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.donation, p.filter_budget, p.reserve, p.project_state, p.bid_details, p.filter_escrow, p.filter_gateway, p.charityid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'product'
					AND p.status = 'open'
					AND p.featured = '1'
					AND p.visible = '1'
					$excluded
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                GROUP BY p.project_id
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($sqlproductauctions);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				while ($res = $ilance->db->fetch_array($sqlproductauctions, DB_ASSOC))
				{
					$res['class'] = 'alt1';
					$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
					$res['title_plain'] = $res['project_title'];
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['project_title'] . '">' . $res['project_title'] . '</a>';
					$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
					$res['photo'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['icons'] = $this->auction_icons($res);
					if ($res['buynow_price'] > 0 AND $res['buynow'])
					{
						if ($res['filtered_auctiontype'] == 'regular')
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid'])  : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							if ($res['buynow_price'] > $res['currentprice'])
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							}
							else
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							}
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
						}
						else 
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
						}
					}
					else
					{
						$res['buynow'] = '';
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
					}
					$resrows++;
					$featuredauctions[] = array('photo' => $res['photo'], 'photoplain' => $res['photoplain'], 'title' => $res['title'], 'lowestprice' => $res['lowestprice'], 'price' => $res['price'], 'buynow' => $res['buynow'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		else if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$default_row = array('title' => '', 'description' => '', 'average' => '', 'timeleft' => '');
			$sqlserviceauctions = $ilance->db->query("
                                SELECT p.user_id, p.project_title, p.description, p.project_id, p.cid, p.bids, p.additional_info, $query_fields p.highlite, p.bid_details, p.currencyid, p.filtered_budgetid, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'service'
					AND p.status = 'open'
					AND p.featured = '1'
					AND p.visible = '1'
					$excluded
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                GROUP BY p.project_id
                                ORDER BY RAND()
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($sqlserviceauctions);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				while ($res = $ilance->db->fetch_array($sqlserviceauctions, DB_ASSOC))
				{
					$temp_title = $res['project_title'] = print_string_wrap($res['project_title'], '25');
					if ($ilconfig['globalfilters_auctiontitlecutoff'] != '0')
					{
						$temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_auctiontitlecutoff']);
						if (strcmp($temp_title, $res['project_title']) != '0')
						{
							$temp_title = $temp_title . '...';
						}
					}
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? '<span title="' . handle_input_keywords($res['project_title']) . '">' . construct_seo_url('serviceauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $temp_title) . '</span>' : '<span title="' . handle_input_keywords($res['project_title']) . '"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . $temp_title . '</a></span>';
					$res['description'] = $ilance->bbcode->strip_bb_tags(strip_tags($res['description']));
					$res['description'] = ($ilconfig['globalfilters_vulgarpostfilter']) ? strip_vulgar_words($res['description']) : $res['description'];
					$res['description'] = short_string($res['description'], $ilconfig['globalfilters_auctiondescriptioncutoff']);
					$res['description'] = handle_input_keywords($res['description']);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], false);
					$res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
					$res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], true, true, true);
					$resrows++;
					$featuredauctions[] = array('title' => $res['title'], 'description' => $res['description'], 'average' => $res['average'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		$ilance->timer->stop();
		DEBUG("fetch_featured_auctions(\$auctiontype = $auctiontype, \$columns = $columns, \$rows = $rows, \$cid = $cid, \$keywords = $keywords, \$forcenoflash = $forcenoflash, \$excludelist = " . serialize($excludelist). ") in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $featuredauctions;
	}
	
	/**
        * Function to return template array data for featured auctions in a picture grid.
        * This function also takes into consideration if a member is not active (don't display the listing).
        *
        * @param       string        auction type
        * @param       integer       auction limit (default 5)
        * @param       integer       auction rows limit (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_latest_auctions($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		if ($ilconfig['showlatestlistings'] == false)
		{
			return array();
		}
		$latestauctions = array();
		$query_fields = $cidcondition = $kwcondition = $subcategorylist = $extracondition = '';
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
			$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
			$cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		}
		else
		{
			$cidcondition = "AND p.cid > 0";
		}
		$limit = $columns * $rows;
		
		($apihook = $ilance->api('fetch_latest_auctions_start')) ? eval($apihook) : false;

		if ($auctiontype == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			$default_row = array('photo' => '', 'photoplain' => '', 'title' => '', 'lowestprice' => '', 'price' => '', 'buynow' => '', 'timeleft' => '');
			$sql = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.user_id, p.project_id, p.project_title, p.description, p.highlite, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, $query_fields p.buynow_price, p.currentprice, p.currencyid, p.buynow, p.buynow_purchases, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.donation, p.filter_budget, p.reserve, p.project_state, p.bid_details, p.filter_escrow, p.filter_gateway, p.charityid
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'product'
					AND p.status = 'open'
					AND p.visible = '1'
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.date_added DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($sql);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				$latestauctions = array();
				while ($res = $ilance->db->fetch_assoc($sql))
				{
					$res['class'] = 'alt1';
					$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
					$res['plain_title'] = $res['project_title'];
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['plain_title'] . '">' . $res['project_title'] . '</a>';
					$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
					$res['photo'] = ($ilconfig['globalauctionsettings_seourls']) ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['icons'] = $this->auction_icons($res);
					if ($res['buynow_price'] > 0 AND $res['buynow'])
					{
						if ($res['filtered_auctiontype'] == 'regular')
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							if ($res['buynow_price'] > $res['currentprice'])
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
							}
							else
							{
								$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							}
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
						}
						else 
						{
							$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
							$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
						}
					}
					else
					{
						$res['buynow'] = '';
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
					}
					$resrows++;
					$latestauctions[] = array('photo' => $res['photo'], 'photoplain' => $res['photoplain'], 'title' => $res['title'], 'lowestprice' => $res['lowestprice'], 'price' => $res['price'], 'buynow' => $res['buynow'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		else if ($auctiontype == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			$default_row = array('title' => '', 'description' => '', 'average' => '', 'timeleft' => '');
			$sql = $ilance->db->query("
                                SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "p.user_id, p.project_title, p.description, p.additional_info, p.highlite, p.project_id, p.cid, $query_fields p.bids, p.bid_details, p.currencyid, p.filtered_budgetid, p.project_details, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects AS p
                                LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
                                WHERE p.project_state = 'service'
					AND p.status = 'open'
					AND p.visible = '1'
					$cidcondition
					$kwcondition
					$extracondition
					AND u.status = 'active'
					" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
                                ORDER BY p.date_added DESC
                                LIMIT $limit
                        ", 0, null, __FILE__, __LINE__);
			$rowstotal = $ilance->db->num_rows($sql);
			if ($rowstotal > 0)
			{
				$resrows = 0;
				$latestauctions = array();
				$user_id = (isset($_SESSION['ilancedata']['user']['userid'])) ? $_SESSION['ilancedata']['user']['userid']  : '0';
				while ($res = $ilance->db->fetch_assoc($sql))
				{
					if ($res['project_details'] == 'invite_only')
					{
						$invite_query = $ilance->db->query("SELECT seller_user_id FROM " . DB_PREFIX . "project_invitations WHERE project_id = '" . $res['project_id'] . "' AND seller_user_id = '" . $user_id ."'", 0, null, __FILE__, __LINE__);
						$fetch_invite_query = $ilance->db->num_rows($invite_query);
						$allow_access = ($fetch_invite_query > 0) ? '1' : '0';
					}
					else
					{
						$allow_access = 1;
					}
					$temp_title = $res['project_title'] = print_string_wrap($res['project_title'], '25');
					if ($ilconfig['globalfilters_auctiontitlecutoff'] != '0')
					{
						$temp_title = cutstring($res['project_title'], $ilconfig['globalfilters_auctiontitlecutoff']);
						if (strcmp($temp_title, $res['project_title']) != '0')
						{
							$temp_title = $temp_title . '...';
						}
					}
					$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('serviceauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $temp_title) : '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $res['project_id'] . '">' . $temp_title . '</a>';	
					$res['description'] = $ilance->bbcode->strip_bb_tags(strip_tags($res['description']));
					$res['description'] = ($ilconfig['globalfilters_vulgarpostfilter']) ? strip_vulgar_words($res['description']) : $res['description'];
					$res['description'] = short_string($res['description'], $ilconfig['globalfilters_auctiondescriptioncutoff']);
					$res['description'] = handle_input_keywords($res['description']);
					$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
					$res['timeleft'] = ($allow_access == 0) ?  '' : $res['timeleft'];
					$res['average'] = $ilance->bid->fetch_average_bid($res['project_id'], false, $res['bid_details'], false);
					$res['average'] = ($allow_access == 0) ?  '{_sealed}' : $res['average'];
					$res['class'] = ($res['highlite']) ? 'featured_highlight' : (($resrows % 2) ? 'alt1' : 'alt1');
					$res['budget'] = $this->construct_budget_overview($res['cid'], $res['filtered_budgetid'], true, true, true);
					$res['budget'] = ($allow_access == 0) ?  '{_sealed}' : $res['budget'];
					$resrows++;
					$res['bids'] = ($allow_access == 0) ? '{_sealed}' : $res['bids'];
					$latestauctions[] = array('title' => $res['title'], 'description' => $res['description'], 'average' => $res['average'], 'timeleft' => $res['timeleft']);
				}
			}
		}
		
		($apihook = $ilance->api('fetch_latest_auctions_end')) ? eval($apihook) : false;

		$ilance->timer->stop();
		DEBUG("fetch_latest_auctions(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $latestauctions;
	}
	
	/**
        * Function to return template array data for items in seller watchlist.
        *
        * @param       string        auction type
        * @param       integer       auction limit (default 5)
        * @param       integer       auction rows limit (default 1)
        * @param       integer       (optional) category id to pull listings from if specified
        * @param       string        (optional) keywords to search (titles & descriptions) when pulling listing results if specified
        * @param       boolean       force no-flash for auction timers (default no force)
        *
        * @return      string        Returns template array data for use with parse_loop() function
        */
	function fetch_items_from_seller_watchlist($auctiontype = '', $columns = 5, $rows = 1, $cid = 0, $keywords = '', $forcenoflash = false)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		$latestauctions = array();
		$query_fields = $cidcondition = $kwcondition = $subcategorylist = '';
		if ($cid > 0)
		{
			$childrenids = $ilance->categories->fetch_children_ids($cid, $auctiontype);
			$subcategorylist .= (!empty($childrenids)) ? $cid . ',' . $childrenids : $cid . ',';
			$cidcondition = "AND (FIND_IN_SET(p.cid, '$subcategorylist'))";
		}
		else
		{
			$cidcondition = "AND p.cid > 0";
		}
		$limit = $columns * $rows;
		$userid = isset($_SESSION['ilancedata']['user']['userid']) ? $_SESSION['ilancedata']['user']['userid'] : 0;
		$extracondition = $usercondition = '';
		
		($apihook = $ilance->api('fetch_items_from_seller_watchlist_start')) ? eval($apihook) : false;

		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "watching_user_id 
			FROM " . DB_PREFIX . "watchlist
			WHERE user_id = '" . $userid . "'
				AND state = 'mprovider'
				AND watching_user_id > 0
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$usercondition = "AND p.user_id IN(";
			$tempgenrequery = '';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$tempgenrequery .= $res['watching_user_id'] . ',';
			}
			$tempgenrequery = (strrchr($tempgenrequery, ',')) ? substr($tempgenrequery, 0, -1) : $tempgenrequery;
			$usercondition .= $tempgenrequery . ")";
		}
		else
		{
			$sql = $ilance->db->query("
				SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "watching_project_id 
				FROM " . DB_PREFIX . "watchlist
				WHERE user_id = '" . $userid . "'
					AND state = 'auction'
					AND watching_project_id > 0
			");
			if ($ilance->db->num_rows($sql) > 0)
			{
				$usercondition = "AND p.project_id IN(";
				$tempgenrequery = '';
				while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
				{
					$tempgenrequery .= $res['watching_project_id'] . ',';
				}
				$tempgenrequery = (strrchr($tempgenrequery, ',')) ? substr($tempgenrequery, 0, -1) : $tempgenrequery;
				$usercondition .= $tempgenrequery . ")";
			}
			else
			{
			    $usercondition = "AND p.project_id IN('')";
			}
		}
		$sql = $ilance->db->query("
			SELECT p.user_id, p.project_id, p.project_title, p.description, p.highlite, p.additional_info, p.bids, p.views, p.cid, p.filtered_auctiontype, p.date_added, p.project_details, $query_fields p.buynow_price, p.currentprice, p.currencyid, p.buynow, p.buynow_purchases, p.date_starts, p.buynow_qty_lot, p.items_in_lot, UNIX_TIMESTAMP(p.date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(p.date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime, p.donation, p.filter_budget, p.reserve, p.project_state, p.bid_details, p.filter_escrow, p.filter_gateway, p.charityid
			FROM " . DB_PREFIX . "projects AS p
			LEFT JOIN " . DB_PREFIX . "users u ON(p.user_id = u.user_id)
			WHERE p.project_state = 'product'
				AND p.status = 'open'
				AND p.visible = '1'
				$cidcondition
				$kwcondition
				$usercondition
				$extracondition
				AND u.status = 'active'
				" . (($ilconfig['globalauctionsettings_payperpost']) ? "AND (p.enhancementfee = 0 OR (p.enhancementfee > 0 AND p.enhancementfeeinvoiceid > 0 AND p.isenhancementfeepaid = '1')) AND (p.insertionfee = 0 OR (p.insertionfee > 0 AND p.ifinvoiceid > 0 AND p.isifpaid = '1'))" : "") . "
			ORDER BY RAND()
			LIMIT $limit
		", 0, null, __FILE__, __LINE__);
		$rowstotal = $ilance->db->num_rows($sql);
		if ($rowstotal > 0)
		{
			$resrows = 0;
			$latestauctions = array();
			while ($res = $ilance->db->fetch_assoc($sql))
			{
				$res['class'] = 'alt1';
				$res['project_title'] = print_string_wrap(handle_input_keywords($res['project_title']), $ilconfig['globalfilters_auctiontitlecutoff']);
				$res['plain_title'] = $res['project_title'];
				$res['title'] = ($ilconfig['globalauctionsettings_seourls']) ? construct_seo_url('productauction', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0, '', '', $res['project_title']) : '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $res['project_id'] . '" title="' . $res['plain_title'] . '">' . $res['project_title'] . '</a>';
				$res['photoplain'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 0, '', 0, '', false, 1, false, true);
				$res['photo'] = $ilconfig['globalauctionsettings_seourls'] ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $res['project_id'], $res['project_title'], '', 0, '', 0, 0), 'thumb', $res['project_id'], 1) : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id'], 1);
				$res['timeleft'] = $this->auction_timeleft(false, $res['date_starts'], $res['mytime'], $res['starttime']);
				$res['icons'] = $this->auction_icons($res);
				if ($res['buynow_price'] > 0 AND $res['buynow'])
				{
					if ($res['filtered_auctiontype'] == 'regular')
					{
						$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
						$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						if ($res['buynow_price'] > $res['currentprice'])
						{
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
						}
						else
						{
							$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
						}
						$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['bids'] . ' {_bids_lower}, ' . $res['buynow_purchases'] . ' {_sold_lower})' : '(' . $res['bids'] . ' {_bids_lower})';
					}
					else 
					{
						$res['buynow'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
						$res['price'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
						$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['buynow_price'], $res['currencyid']);
						$res['bids'] = ($res['buynow_purchases'] > 0) ? '(' . $res['buynow_purchases'] . ' {_sold_lower})' : '';
					}
				}
				else
				{
					$res['buynow'] = '';
					$res['price'] = ($res['bids'] > 0) ? print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']) : print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
					$res['lowestprice'] = print_currency_conversion($_SESSION['ilancedata']['user']['currencyid'], $res['currentprice'], $res['currencyid']);
					$res['bids'] = '(' . $res['bids'] . ' {_bids_lower})';
				}
				$resrows++;
				$latestauctions[] = $res;
			}
		}

		($apihook = $ilance->api('fetch_items_from_seller_watchlist_end')) ? eval($apihook) : false;

		$ilance->timer->stop();
		DEBUG("fetch_items_from_seller_watchlist(\$auctiontype = $auctiontype) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $latestauctions;
	}
	
	/**
        * Function to fetch homepage heros
        *
        * @param       string        hero display mode
        * @param       integer       category id (optional)
        *
        * @return      array         Returns array with hero data
        */
	function fetch_heros($mode = '', $cid = 0)
	{
		global $ilance, $ilconfig, $show, $phrase, $ilpage;
		$ilance->timer->start();
		$cidcondition = "";
		$heros = array();
		if ($cid > 0)
		{
			$cidcondition = "AND cid = '" . intval($cid). "'";
		}
		
		($apihook = $ilance->api('fetch_heros_start')) ? eval($apihook) : false;

		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "id, filename, imagemap
			FROM " . DB_PREFIX . "hero
			WHERE mode = '" . $ilance->db->escape_string($mode) . "'
			$cidcondition
			ORDER BY sort ASC
		", 0, null, __FILE__, __LINE__);
		$rowstotal = $ilance->db->num_rows($sql);
		if ($rowstotal > 0)
		{
			while ($res = $ilance->db->fetch_assoc($sql))
			{
				$heros[] = $res;
			}
		}
		
		($apihook = $ilance->api('fetch_heros_end')) ? eval($apihook) : false;

		$ilance->timer->stop();
		DEBUG("fetch_heros(\$mode = $mode, \$cid = $cid) in " . $ilance->timer->get() . " seconds", 'FUNCTION');
		return $heros;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>