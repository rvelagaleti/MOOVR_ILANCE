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
* Auction class to perform the majority of functions dealing with anything to do with auctions within ILance.
*
* @package      iLance\Auction\Product
* @version      4.0.0.8059
* @author       ILance
*/
class auction_product extends auction
{
	/**
	* Function to fetch the buy now order count for a particular product auction event
	* 
	* @param       integer        project id
	*
	* @return      integer        Returns count
	*/
	function fetch_buynow_ordercount($projectid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT COUNT(*) AS total
			FROM " . DB_PREFIX . "buynow_orders
			WHERE project_id = '" . intval($projectid) . "'
				AND status != 'cancelled'
		", 0, null, __FILE__, __LINE__);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return (int) $res['total'];
	}
    
	/**
	* Function to print the total number of product feedback reviews for this particular user
	*
	* @param        integer       user id
	*
	* @return	string        Returns number of product feedback reviews reported
	*/
	function fetch_product_reviews_reported($userid = 0)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS reviewcount
			FROM " . DB_PREFIX . "feedback
			WHERE for_user_id = '" . intval($userid) . "'
				AND cattype = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			return $res['reviewcount'];
		}
		return 0;
	}
	
	/**
	* Function to print the total number of product auction bids awarded for this particular user
	*
	* @param        integer       user id
	* @param        bool          force an update right now?
	*
	* @return	string        Returns number of product bids awarded
	*/
	function fetch_product_bids_awarded($userid = 0, $doupdate = false)
	{
		global $ilance;
		$sql = $ilance->db->query("
			SELECT " . (MYSQL_QUERYCACHE ? "SQL_CACHE " : "") . "COUNT(*) AS bidsawarded
			FROM " . DB_PREFIX . "project_bids
			WHERE user_id = '" . intval($userid) . "'
				AND bidstatus = 'awarded'
				AND state = 'product'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$awards = $res['bidsawarded'];        
			if ($doupdate)
			{
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "users
					SET productawards = '" . intval($awards) . "'
					WHERE user_id = '" . intval($userid) . "'
				", 0, null, __FILE__, __LINE__);
			}
			return $awards;
		}
		return 0;
	}
    
	/**
	* Function to relist product auction
	*
	* @return      nothing
	*/
	function relist_product_auction($id = 0)
	{
		global $ilance, $ilconfig;
		$rfpid = $ilance->auction_rfp->construct_new_auctionid_bulk();
		$sql = $ilance->db->query("
			SELECT p.*, s.*, sd.*
			FROM " . DB_PREFIX . "projects p
			LEFT JOIN " . DB_PREFIX . "projects_shipping s ON p.project_id = s.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_destinations sd ON p.project_id = sd.project_id
			LEFT JOIN " . DB_PREFIX . "projects_shipping_regions sr ON p.project_id = sr.project_id
			WHERE p.project_id = '" . intval($id) . "'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$ilance->GPC = array_merge($ilance->GPC, $ilance->db->fetch_array($sql, DB_ASSOC));
			$shipping1 = array(
				'ship_method' => (isset($ilance->GPC['ship_method'])) ? $ilance->GPC['ship_method'] : 'flatrate',
				'ship_length' => (isset($ilance->GPC['ship_length'])) ? $ilance->GPC['ship_length'] : '12',
				'ship_width' => (isset($ilance->GPC['ship_width'])) ? $ilance->GPC['ship_width'] : '12',
				'ship_height' => (isset($ilance->GPC['ship_height'])) ? $ilance->GPC['ship_height'] : '12',
				'ship_weightlbs' => (isset($ilance->GPC['ship_weightlbs'])) ? $ilance->GPC['ship_weightlbs'] : '1',
				'ship_weightoz' => (isset($ilance->GPC['ship_weightoz'])) ? $ilance->GPC['ship_weightoz'] : '0',
				'ship_handlingtime' => (isset($ilance->GPC['ship_handlingtime'])) ? $ilance->GPC['ship_handlingtime'] : '3',
				'ship_handlingfee' => (isset($ilance->GPC['ship_handlingfee'])) ? $ilance->currency->string_to_number($ilance->GPC['ship_handlingfee']) : '0.00'
			);
			for ($i = 1; $i <= $ilconfig['maxshipservices']; $i++)
			{
				$shipping2['ship_options_' . $i] = (isset($ilance->GPC['ship_options_' . $i])) ? $ilance->GPC['ship_options_' . $i] : '';
				$shipping2['ship_service_' . $i] = (isset($ilance->GPC['ship_service_' . $i])) ? intval($ilance->GPC['ship_service_' . $i]) : '';
				$shipping2['ship_packagetype_' . $i] = (isset($ilance->GPC['ship_packagetype_' . $i])) ? $ilance->GPC['ship_packagetype_' . $i] : '';
				$shipping2['ship_pickuptype_' . $i] = (isset($ilance->GPC['ship_pickuptype_' . $i])) ? $ilance->GPC['ship_pickuptype_' . $i] : '';
				$shipping2['ship_fee_' . $i] = (isset($ilance->GPC['ship_fee_' . $i])) ? $ilance->currency->string_to_number($ilance->GPC['ship_fee_' . $i]) : '0.00';
				$shipping2['ship_fee_next_' . $i] = (isset($ilance->GPC['ship_fee_next_' . $i])) ? $ilance->currency->string_to_number($ilance->GPC['ship_fee_next_' . $i]) : '0.00';
				$shipping2['freeshipping_' . $i] = (isset($ilance->GPC['freeshipping_' . $i])) ? intval($ilance->GPC['freeshipping_' . $i]) : '0';
				$shipping2['ship_options_custom_region_' . $i] = (isset($ilance->GPC['ship_options_custom_region_' . $i])) ? $ilance->GPC['ship_options_custom_region_' . $i] : array();
			}
			$ilance->GPC['shipping'] = array_merge($shipping1, $shipping2);
			unset($shipping1, $shipping2);
			$this->rewrite_photos($id, $rfpid);
			$enhancements = array();
			$promo = array('bold', 'featured', 'highlite', 'autorelist', 'reserve', 'buynow');
			foreach ($promo AS $key)
			{
				if (isset($ilance->GPC[$key]) AND $ilance->GPC[$key] == '1')
				{
					if ($key == 'highlite')
					{
						$enhancements['highlite'] = '1';
					}
					else
					{
						$enhancements[$key] = $ilance->GPC[$key];
					}
				}
			}
			$duration = strtotime($ilance->GPC['date_end']) - strtotime($ilance->GPC['date_starts']);
			$duration_unit = 'D';
			if ($duration / 60 > 0 AND $duration / 60 <= 30)
			{
				$duration_unit = 'M';
				$duration = $duration / 60;
			}
			else if ($duration / 3600 > 0 AND $duration / 3600 <= 30)
			{
				$duration_unit = 'H';
				$duration = $duration / 3600;
			}
			else if ($duration / 86400 > 0 AND $duration / 86400 <= 30)
			{
				$duration_unit = 'D';
				$duration = $duration / 86400;
			}
			if ($ilance->GPC['filtered_auctiontype'] == 'fixed' AND $ilance->GPC['buynow'] == '1')
			{
				$ilance->GPC['buynow_qty'] = $ilance->GPC['buynow_qty'] + $ilance->GPC['buynow_purchases'];
			}
			else if ($ilance->GPC['filtered_auctiontype'] == 'regular' AND $ilance->GPC['buynow'] == '1')
			{
				$ilance->GPC['buynow_qty'] = '1';
			}
			$sql_quest = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "product_answers
				WHERE project_id = '" . intval($id) . "'
			", 0, null, __FILE__, __LINE__);  
			if ($ilance->db->num_rows($sql_quest) > 0)
			{
				while ($res_quest = $ilance->db->fetch_array($sql_quest, DB_ASSOC))
				{
					$ilance->db->query("
						INSERT INTO " . DB_PREFIX . "product_answers
						(answerid, questionid, project_id, answer, optionid, date, visible)
						VALUES(
						NULL,
						'" . intval($res_quest['questionid']) . "',
						'" . intval($rfpid) . "',
						'" . $ilance->db->escape_string($res_quest['answer']) . "',
						'" . $ilance->db->escape_string($res_quest['optionid']) . "',
						'" . DATETIME24H . "',
						'" . $ilance->db->escape_string($res_quest['visible']) . "')
					", 0, null, __FILE__, __LINE__);  
				}
			}
			$ilance->GPC['relist_id'] = $id;
			$ilance->auction_rfp->insert_product_auction(
				$_SESSION['ilancedata']['user']['userid'],
				$ilance->GPC['project_type'],
				'open',
				$ilance->GPC['project_state'],
				$ilance->GPC['cid'],
				$rfpid,
				$ilance->GPC['project_title'],
				$ilance->GPC['description'],
				$ilance->GPC['description_videourl'],
				$ilance->GPC['additional_info'],
				$ilance->GPC['keywords'],
				$custom = array(),
				$profileanswer = array(),
				$ilance->GPC['filtered_auctiontype'],
				$ilance->GPC['startprice'],
				$ilance->GPC['project_details'],
				$ilance->GPC['bid_details'],
				$ilance->GPC['filter_rating'],
				$ilance->GPC['filter_country'],
				$ilance->GPC['filter_state'],
				$ilance->GPC['filter_city'],
				$ilance->GPC['filter_zip'],
				$ilance->GPC['filter_businessnumber'],
				$ilance->GPC['filtered_rating'],
				$ilance->GPC['filtered_country'],
				$ilance->GPC['filtered_state'],
				$ilance->GPC['filtered_city'],
				$ilance->GPC['filtered_zip'],
				$ilance->GPC['city'],
				$ilance->GPC['state'],
				$ilance->GPC['zipcode'],
				$ilance->GPC['country'],
				$ilance->GPC['shipping'],
				$ilance->GPC['buynow'],
				$ilance->GPC['buynow_price'],
				$ilance->GPC['buynow_qty'],
				$ilance->GPC['buynow_qty_lot'],
				$ilance->GPC['items_in_lot'],
				$enhancements,
				$ilance->GPC['reserve'],
				$ilance->GPC['reserve_price'],
				$ilance->GPC['filter_underage'],
				$ilance->GPC['filter_escrow'],
				$ilance->GPC['filter_gateway'],
				$ilance->GPC['filter_ccgateway'],
				$ilance->GPC['filter_offline'],
				$ilance->GPC['filter_publicboard'],
				$invitelist = '',
				$invitemessage = '',
				$year = '',
				$month = '',
				$day = '',
				$hour = '',
				$min = '',
				$sec = '',
				$duration,
				$duration_unit,
				unserialize($ilance->GPC['paymethod']),
				unserialize($ilance->GPC['paymethodcc']),
				unserialize($ilance->GPC['paymethodoptions']),
				unserialize($ilance->GPC['paymethodoptionsemail']),
				$ilance->GPC['draft'] = '0',
				$ilance->GPC['returnaccepted'],
				$ilance->GPC['returnwithin'],
				$ilance->GPC['returngivenas'],
				$ilance->GPC['returnshippaidby'],
				$ilance->GPC['returnpolicy'],
				$ilance->GPC['donation'],
				$charityid = '',
				$ilance->GPC['donationpercentage'],
				$skipemailprocess = 1,
				$apihookcustom = '',
				$isbulkupload = false,
				$sample = '',
				$ilance->GPC['currencyid'],
				$ilance->GPC['classified_price'],
				$ilance->GPC['classified_phone'],
				$ilance->GPC['sku'],
				$ilance->GPC['upc'],
				$ilance->GPC['ean'],
				$ilance->GPC['partnumber'],
				$ilance->GPC['modelnumber'],
				$ilance->GPC['salestaxstate'],
				$ilance->GPC['salestaxrate'],
				$ilance->GPC['salestaxshipping']
			);
			
			($apihook = $ilance->api('relist_product_auction_end')) ? eval($apihook) : false;
			
			return fetch_auction('status', $rfpid);
		}
	}
	
	/**
	* Function to determine if a bidder is invited to an invite only project
	*
	* @param        integer     user id
	* @param        integer     project id
	*
	* @return	boolean     returns true or false if bidder is invited
	*/
	function is_bidder_invited($userid = 0, $projectid = 0)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "projects 
			WHERE project_id = '" . intval($projectid) . "'
				AND project_state = 'product'
				AND project_details = 'invite_only'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$invite = $ilance->db->query("
				SELECT id
				FROM " . DB_PREFIX . "project_invitations
				WHERE project_id = '" . $projectid . "'
					AND buyer_user_id = '" . $userid . "'
					AND seller_user_id = '" . $res['user_id'] . "'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($invite) > 0)
			{
				return true;
			}
			return false;
		}
		return true;
	}
	
	/**
	* Function to fetch the current purchase now price of a particular auction id.
	*
	* @param       integer       project id
	*
	* @return      string        HTML representation of the purchase now details
	*/
	function fetch_purchase_now($projectid = 0)
	{
		global $ilance, $ilconfig, $phrase;
		$sql = $ilance->db->query("
			SELECT buynow, buynow_price, buynow_qty
			FROM " . DB_PREFIX . "projects
			WHERE project_id = '" . intval($projectid) . "'
			    AND buynow > 0
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$html = '<div>{_purchase_now_price} <input type="text" name="buynow_price" size="10" value="' . $res['buynow_price'] . '"></div>';
			$html .= '<div>{_qty}: <input type="text" name="buynow_qty" size="2" value="' . $res['buynow_qty'] . '"></div>';
		}
		else
		{
			$html = '<div>{_this_auction_is_not_selling_any_items_using_the_purchase_now_feature}</div>';
		}
		return $html;
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>