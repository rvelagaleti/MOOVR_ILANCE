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
* MyCP class to perform the majority of functions found on the MyCP Control Panel Dashboard
*
* @package      iLance\MyCP
* @version      4.0.0.8059
* @author       ILance
*/
class mycp
{
	/*
	* Function to fetch the referral activity menu block
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 1 year)
	* @param      integer      result limit (default 5)
	*
	* @return     string       Returns HTML formatted details of referral activity   
	*/
	function referral_activity($userid = 0, $daysago = 365, $userlimit = 5)
	{
		global $ilance, $ilpage, $phrase, $show, $ilconfig;
		$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "referral_data
			WHERE referred_by = '" . intval($userid) . "'
			ORDER BY date DESC
			LIMIT $userlimit
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$htmlbit = $html = $sep = '';
			$count = 0;
			while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$count++;
				/*$htmlbit .= '<hr size="1" width="100%" style="color:#cccccc" /><div style="padding-top:9px; padding-bottom:9px"><span style="float:left; padding-right:5px"><a href="javascript:void(0)" onclick="return toggle(\'ref' . $row['id'] . '\');"><div style="padding-right:5px"><img id="collapseimg_ref' . $row['id'] . '" src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'expand_collapsed.gif" border="0" alt="" /></div></a></span><strong>' . fetch_user('username', $row['user_id']) . '</strong> <span class="smaller gray">&nbsp;&nbsp;&nbsp;( ' . '{_date}' . ': ' . print_date($row['date'], $ilconfig['globalserverlocale_globaldateformat']) . ' )</span></div>';
				$htmlbit .= '<div id="collapseobj_ref' . $row['id'] . '" style="display:none">';
				// #### valid listing posting ##################
				$htmlbit .= '<div>';
				if ($row['postauction'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_posted_any_valid_auction}</div>';
				// #### awarded a valid bid ####################
				$htmlbit .= '<div>';
				if ($row['awardauction'] > 0)
				{
					 $htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';	
				}
				$htmlbit .= '{_awarded_any_valid_bid}</div>';
				$htmlbit .= '<div>';
				if ($row['paysubscription'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_paid_subscription}</div>';
				$htmlbit .= '<div>';
				if ($row['payfvf'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_paid_final_value_fee}</div>';
				$htmlbit .= '<div>';
				if ($row['payins'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_paid_insertion_fee}</div>';
				$htmlbit .= '<div>';
				if ($row['payportfolio'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';	
				}
				$htmlbit .= '{_paid_portfolio_fee}</div>';
				$htmlbit .= '<div>';
				if ($row['paycredentials'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_paid_any_credential_fee}</div>';
				$htmlbit .= '<div>';
				if ($row['payenhancements'] > 0)
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" />&nbsp;&nbsp;';
				}
				else
				{
					$htmlbit .= '&nbsp;&nbsp;<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" />&nbsp;&nbsp;';
				}
				$htmlbit .= '{_paid_auction_upsell_fee}</div>';
				
				($apihook = $ilance->api('referal_activity_while_loop_end')) ? eval($apihook) : false;
				
				$htmlbit .= '</div>';*/
			}
			$html .= '<strong>' . $count . '</strong> {_referrals_found}. ' . $ilance->language->construct_phrase('_earn_x_valid_link_code', '<strong>' . $ilance->currency->format($ilconfig['referalsystem_payout']) . '</strong>');
		}
		else
		{
			$html = '0 {_referrals_found}. ' . $ilance->language->construct_phrase('_earn_x_valid_link_code', '<strong>' . $ilance->currency->format($ilconfig['referalsystem_payout']) . '</strong>');
		} 
		return $html;
	}
    
	/*
	* Function to fetch the invitation activity for a particular bidder
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 7 days)   
	*
	* @return     string       Returns HTML formatted details of invitation activity    
	*/
	function invitation_activity($userid = 0, $daysago = 7)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$html = $htmlv4 = '';
		$sql = $ilance->db->query("
			SELECT p.project_id, p.project_title, p.bids, p.status
			FROM " . DB_PREFIX . "projects AS p,
			" . DB_PREFIX . "project_invitations AS i
			WHERE i.project_id = p.project_id
				AND i.seller_user_id = '" . intval($userid) . "'
				AND p.project_state = 'service'
				AND i.bid_placed = 'no'
				AND p.status = 'open'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$invites = $ilance->db->num_rows($sql);
			$html = '<div><span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited#servicebidding">{_you_currently_have} ' . $invites . '</a></span> {_buyers_waiting_for_you_to_place_a_bid_on_their_auctions}</div>';
			$htmlv4 = '<h2>{_you_currently_have} ' . $invites . ' {_buyers_waiting_for_you_to_place_a_bid_on_their_auctions}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management&amp;bidsub=invited#servicebidding">{_invitations}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
		}
		else
		{
			$html = '{_you_have_not_been_invited_to_any_recent_auctions}';
			$htmlv4 = '';
		}
		return array($html, $htmlv4);
	}
	
	/*
	* Function to fetch the unread message count for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of the unread message count   
	*/
	function fetch_unread_messages($userid = 0)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$sql = $ilance->db->query("
			SELECT id
			FROM " . DB_PREFIX . "pmb_alerts
			WHERE to_id = '" . intval($userid) . "'
			    AND to_status = 'new'
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$unreadcount = $ilance->db->num_rows($sql);
			$html = '<div>{_you_currently_have} <span class="blue"><a href="' . $ilpage['messages'] . '"><strong>' . $unreadcount . ' ' . '{_unread}</strong></a></span> {_messages_waiting_in_your_inbox}</div>';
			$htmlv4 = '<h2>{_you_currently_have} <strong>' . $unreadcount . ' </strong> {_unread} {_messages_waiting_in_your_inbox}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['messages'] . '">{_messages}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
		}
		else
		{
			$html = '{_you_currently_have_no_new_messages_waiting}';
			$htmlv4 = '';
		}
		return array($html, $htmlv4);
	}
    
	/*
	* Function to fetch the scheduled transactions for a particular member
	*
	* @param      integer      user id
	* @param      integer      days ago range (default 7 days)        
	*
	* @return     string       Returns HTML formatted details of scheduled transactions up and coming      
	*/
	function scheduled_transactions($userid = 0, $daysago = 7)
	{
		global $ilance, $ilpage, $phrase;
		$sql = $ilance->db->query("
			SELECT invoiceid, description, amount, currency_id
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			    AND status = 'scheduled'
			ORDER BY invoiceid DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$html = '';
			while ($res = $ilance->db->fetch_array($sql, DB_ASSOC));
			{
				$html .= '<div>{_scheduled_transaction}' . ': <a href="' . $ilpage['invoicepayment'] . '?id=' . $res['invoiceid'] . '">{_invoice_id}' . ' (' . $res['invoiceid'] . ')</a> - ' . stripslashes($res['description']) . ' - ' . $ilance->currency->format($res['amount'], $res['currency_id']) . '.</div>';
			}
		}
		else
		{
			$html = '{_no_scheduled_transactions_have_been_recorded}';
		}
		return $html;
	}
    
	/*
	* Function to fetch any unpaid transactions for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of unpaid transactions up and coming      
	*/
	function unpaid_transactions($userid = 0)
	{
		global $ilance, $ilpage, $phrase;
		$html = '';
		$sql = $ilance->db->query("
			SELECT invoiceid, description
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			    AND status = 'unpaid'
			    AND invoicetype != 'escrow'
			    AND isdeposit = '0'
			    AND iswithdraw = '0'
			ORDER BY invoiceid DESC
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$html .= '<strong>{_unpaid}</strong><br />';
			while ($res3 = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				$html .= '<a href="' . $ilpage['invoicepayment'] . '?id=' . $res3['invoiceid'] . '"><span style="color:#990000"><strong>{_invoice_id} (' . $res3['invoiceid'] . ')</strong></span></a> - ' . stripslashes($res3['description']) . '<br />';
			}
		}
		else
		{
			$html = '<strong>{_unpaid}</strong><br />{_no_unpaid_transactions_have_been_recorded}';
		}
		return $html;
	}
    
	/*
	* Function to fetch and print the accounting activity block for a particular member
	*
	* @param      integer      user id
	*
	* @return     string       Returns HTML formatted details of accounting information
	*/
	function accounting_activity($userid = 0)
	{
		global $ilance, $ilpage, $phrase;
		$html = '';
		$sql = $ilance->db->query("
			SELECT invoiceid, description
			FROM " . DB_PREFIX . "invoices
			WHERE user_id = '" . intval($userid) . "'
			ORDER BY invoiceid DESC
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql);
			$html = '<div><strong>{_last_transaction_recorded}</strong><br /><a href="' . $ilpage['invoicepayment'] . '?id=' . $res['invoiceid'] . '" title="' . stripslashes($res['description']) . '"><strong>{_invoice_id}' . ' (' . $res['invoiceid'] . ')</strong></a> - ' . stripslashes($res['description']) . '</div>';
			$html .= '<hr size="1" width="100%">';
		}
		else
		{
			$html = '<strong>{_last_transaction_recorded}</strong><br />{_no_transactions_have_been_recorded}';
			$html .= '<hr size="1" width="100%">';
		}
		$html .= $this->unpaid_transactions($userid);
		return $html;
	}
    
	/*
	* Function to fetch any related escrow activity and notifications for the currently logged in member
	* Function now groups all escrow activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (default buyer)
	* @param      integer       days ago range (default 7)
	*
	* @return     string       Returns HTML formatted details of escrow activity or actions required by member
	*/
	function escrow_activity($userid = 0, $viewtype = 'buying', $type = 'service', $daysago = 7)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$html = $htmlv4 = '';
		if ($ilconfig['escrowsystem_enabled'])
		{
			switch ($viewtype)
			{
				case 'buying':
				{
					// #### BUYING SERVICES ESCROW NOTIFICATIONS ###################
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] AND $type == 'service')
					{
						// AS A BUYER: do we need to PAY any escrow account to any service providers?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'pending'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_providers_waiting_for_you_to_fund_escrow}', $count) . '&nbsp;&nbsp;<span class="smaller gray">( <span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">{_pay_now}</a></span> )</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_providers_waiting_for_you_to_fund_escrow}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">{_pay_now}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### BUYING SERVICES ESCROW NOTIFICATIONS ###################
						// AS A BUYER: do we need to RELEASE any funds in escrow to a service provider?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_providers_waiting_for_release_of_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_providers_waiting_for_release_of_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
					}
					// #### BUYING PRODUCTS ESCROW NOTIFICATIONS ###################
					if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND $type == 'product')
					{
						// AS A BUYER: do we need to PAY any seller escrow account?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'pending'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_buyers_waiting_for_you_to_fund_escrow}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_pay_now}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_buyers_waiting_for_you_to_fund_escrow}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_pay_now}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### BUYING PRODUCTS ESCROW NOTIFICATIONS ###################
						// AS A BUYER: do we need to RELEASE any funds in escrow to a seller?
						$count = 0;
						$sql5 = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql5) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql5))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_sellers_waiting_for_release_of_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_sellers_waiting_for_release_of_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### BUYING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A BUYER (we've already paid the cost): do we need to HOUND any merchants for delivery status?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.buyer_id = '" . intval($userid) . "'
								AND e.status = 'paid'
								AND e.project_id = p.project_id
								AND e.orderdate >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_waiting_on_x_sellers_to_update_my_delivery_status}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_waiting_on_x_sellers_to_update_my_delivery_status}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### BUYING ITEMS VIA REGULAR ESCROW
						// AS A BUYER (we've already paid the cost): do we need to HOUND any sellers for delivery status?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow AS e,
							" . DB_PREFIX . "projects AS p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'started'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'product'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_buyers_waiting_for_a_delivery_update}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_buyers_waiting_for_a_delivery_update}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### BUYING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A BUYER (already paid, and assumed shipped): do we need to RELEASE any funds in escrow to merchants?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders AS e,
							" . DB_PREFIX . "projects as p
							WHERE e.buyer_id = '" . intval($userid) . "'
								AND e.status = 'pending_delivery'
								AND e.project_id = p.project_id
								AND e.orderdate >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'product'
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_sellers_waiting_for_release_of_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_sellers_waiting_for_release_of_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						break;
					}
				}				
				case 'selling':
				{
					// #### SELLING SERVICES ESCROW NOTIFICATIONS ##################
					if ($ilconfig['globalauctionsettings_serviceauctionsenabled'] AND $type == 'service')
					{
						// AS A PROVIDER: are we waiting for buyers to release any funds to us as a service provider?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND e.date_awarded >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
								AND p.project_state = 'service'
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### SELLING SERVICES ESCROW NOTIFICATIONS ##################
						// AS A PROVIDER: did any buyers release funds to finish the project *within last 7 days?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.user_id = '" . intval($userid) . "'
								AND e.status = 'finished'
								AND e.project_id = p.project_id
								AND p.project_state = 'service'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_received_funds_from_x_buyers_into_my_account_balance}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_received_funds_from_x_buyers_into_my_account_balance}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=rfp-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
					}
					// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
					if ($ilconfig['globalauctionsettings_productauctionsenabled'] AND $type == 'product')
					{
						// AS A MERCHANT: are we waiting for bidders to release any funds to us as a merchant?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'confirmed'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
						// AS A MERCHANT: did any bidders release funds to finish the escrow purchase *within last 7 days?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'finished'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.escrow_id DESC", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_received_funds_from_x_buyers_into_my_account_balance}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_received_funds_from_x_buyers_into_my_account_balance}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### SELLING PRODUCTS ESCROW NOTIFICATIONS ##################
						// AS A MERCHANT: do we need to confirm any shipment of products or downloads via escrow?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.escrow_id, e.project_id, p.project_title
							FROM " . DB_PREFIX . "projects_escrow as e,
							" . DB_PREFIX . "projects as p
							WHERE e.project_user_id = '" . intval($userid) . "'
								AND e.status = 'started'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.escrow_id DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_x_buyers_waiting_for_a_delivery_update}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_buyers_waiting_for_a_delivery_update}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
						// #### SELLING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A PURCHASE NOW MERCHANT (bidder already paid the cost): do we need to set any deliveries to sent/delivered?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders AS e,
							" . DB_PREFIX . "projects as p
							WHERE e.owner_id = '" . intval($userid) . "'
								AND e.status = 'paid'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.orderdate >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								//$html .= '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'escrow.gif" border="0" alt="' . '{_escrow}' . '" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/cart.gif" border="0" alt="" /> <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'freeshipping.gif" border="0" alt="" /> {_item}' . ' <span class="blue"><a href="' . $ilpage['merch'] . '?id=' . $res['project_id'] . '">' . $res['project_title'] . '</a></span> - ' . '{_waiting_for_you_to_update_order_as_delivered}' . ' [ <span class="blue"><a href="' . $ilpage['selling'] . '?cmd=management">{_click_here_to_view_details}</a></span> ]</div><hr size="1" width="100%" style="color:#cccccc" />';
								$count++;
							}
							//$html .= $ilance->language->construct_phrase('{_x_buyers_waiting_for_a_delivery_update}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="'.$ilpage['selling'].'?cmd=management">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
						}
						unset($count);
						// #### SELLING ITEMS VIA PURCHASE NOW - ESCROW NOTIFICATIONS ##
						// AS A PURCHASE NOW MERCHANT (already paid, and assumed shipped): do we need to HOUND any bidders for funds in escrow?
						$count = 0;
						$sql = $ilance->db->query("
							SELECT e.orderid, e.project_id, p.project_title
							FROM " . DB_PREFIX . "buynow_orders AS e,
							" . DB_PREFIX . "projects as p
							WHERE e.owner_id = '" . intval($userid) . "'
								AND e.status = 'pending_delivery'
								AND e.project_id = p.project_id
								AND p.project_state = 'product'
								AND e.orderdate >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
							ORDER BY e.orderid DESC
						", 0, null, __FILE__, __LINE__);
						if ($ilance->db->num_rows($sql) > 0)
						{
							while ($res = $ilance->db->fetch_array($sql))
							{
								$count++;
							}
							$html .= $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
							$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_waiting_for_x_buyers_to_release_escrow_funds}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=product-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
						}
						unset($count);
					}
					break;
				}
			}
			return array($html, $htmlv4);
		}
	}
    
	/*
	* Function to fetch any bids or bidding activity and notifications for the currently logged in member
	* Function now groups all bidding activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (default buying)
	* @param      integer       days ago range (default 7)
	*
	* @return     string       Returns HTML formatted details of bids activity or actions required by member
	*/
	function bids_activity($userid = 0, $viewtype = 'buying', $type = 'service', $daysago = 7)
	{
		global $ilconfig, $ilance, $ilpage, $phrase;
		$html = $htmlv4 = '';
		$count1 = $count2 = 0;
		$daysago = intval($daysago);
		if ($type == 'product' AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			if ($viewtype == 'buying')
			{
				// maybe alert informing buyer x hours left on items you're bidding on?
			}
			else if ($viewtype == 'selling')
			{
				// collect all item auctions posted by this member
				$sql = $ilance->db->query("
					SELECT pb.bid_id 
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "project_bids pb ON (p.project_id = pb.project_id)
					WHERE p.user_id = '" . intval($userid) . "'
						AND p.status != 'expired'
						AND p.status != 'finished'
						AND p.status != 'approval_accepted'
						AND p.status != 'archived'
						AND p.project_state = 'product'
						AND pb.bidstatus != 'declined'
						AND pb.bidstate != 'retracted'
						AND pb.date_added >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				$rows = $ilance->db->num_rows($sql);
				if ($rows > 0)
				{
					$count1 += intval($rows);
				}
				unset($sql);
			}
		}
		else if ($type == 'service' AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			if ($viewtype == 'buying')
			{
				$sql = $ilance->db->query("
					SELECT pb.id
					FROM " . DB_PREFIX . "projects p
					LEFT JOIN " . DB_PREFIX . "project_realtimebids pb ON (p.project_id = pb.project_id)
					WHERE p.user_id = '" . intval($userid) . "'
						AND p.status != 'expired'
						AND p.status != 'finished'
						AND p.status != 'approval_accepted'
						AND p.status != 'archived'
						AND p.project_state = 'service'
						AND pb.bidstatus != 'declined'
						AND pb.bidstate != 'retracted'
						AND pb.date_added >= DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				$rows = $ilance->db->num_rows($sql);
				if ($rows > 0)
				{
					$count2 += intval($rows);
				}
				unset($sql);
			}
			else if ($viewtype == 'selling')
			{
				// maybe alert informing provider about x hours left on jobs you're bidding on?
			}
		}
		if ($count1 > 0 AND $ilconfig['globalauctionsettings_productauctionsenabled'])
		{
			if ($viewtype == 'buying')
			{
				$html .= '';
				$htmlv4 .= '';
			}
			else if ($viewtype == 'selling')
			{
				$html .= $ilance->language->construct_phrase('{_x_bids_placed_on_items_youre_selling}', $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
				$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_bids_placed_on_items_youre_selling}', $count1) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
		}
		if ($count2 > 0 AND $ilconfig['globalauctionsettings_serviceauctionsenabled'])
		{
			if ($viewtype == 'buying')
			{
				$html .= $ilance->language->construct_phrase('{_x_bids_placed_on_services_youre_buying}', $count2) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;orderby=bids&amp;displayorder=desc">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';	
				$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_x_bids_placed_on_services_youre_buying}', $count2) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;orderby=bids&amp;displayorder=desc">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
			else if ($viewtype == 'selling')
			{
				$html .= '';
				$htmlv4 .= '';
			}
		}
		return array($html, $htmlv4);
	}
	
	/*
	* Function to fetch any bids award activity and notifications for the currently logged in member.
	* Function now groups all activity for a more general overview without dashboard clutter.
	*
	* @param      integer       user id
	* @param      string        viewing type (buying or selling)
	* @param      string        category type (service or product)
	* @param      integer       days ago range (default 7)
	*
	* @return     string        Returns HTML formatted details of bidding award activity or actions required by member
	*/
	function bids_award_activity($userid = 0, $viewtype = 'buying', $cattype = 'service', $daysago = 7)
	{
		global $ilance, $phrase, $ilconfig, $ilpage;
		$html = $htmlv4 = '';
		$count = $count1 = $count2 = 0;
		if ($viewtype == 'buying')
		{
			// #### buying a product as a buyer
			if ($cattype == 'product')
			{
				$sql = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . intval($userid) . "'
						AND state = '" . $cattype . "'
						AND (bidstatus = 'awarded' AND bidstate = 'wait_approval'
							OR bidstatus = 'placed' AND bidstate = 'wait_approval'
							OR bidstatus = 'awarded' AND bidstate = '')
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						$count1++;
					}
					$html .= $ilance->language->construct_phrase('{_congrats_youve_placed_x_winning_bids}', $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded&amp;orderby=bids&amp;displayorder=desc#product">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
					$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_congrats_youve_placed_x_winning_bids}', $count1) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['buying'] . '?cmd=management&amp;bidsub=awarded&amp;orderby=bids&amp;displayorder=desc#product">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
				$sql = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "buynow_orders
					WHERE buyer_id = '" . intval($userid) . "'
						AND paiddate = '0000-00-00 00:00:00'
						AND orderdate > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql) > 0)
				{
					$count1 = $ilance->db->num_rows($sql);
					$html .= $ilance->language->construct_phrase('{_congrats_you_buy_products}', $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
					$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_congrats_you_buy_products}', $count1) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['escrow'] . '?cmd=management&amp;bidsub=buynow-escrow">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
		}
		else if ($viewtype == 'selling')
		{
			// #### selling products as a seller
			if ($cattype == 'product')
			{
				// #### awarded bids from buyers ###############
				$sql2 = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE project_user_id = '" . intval($userid) . "'
						AND state = '" . $ilance->db->escape_string($cattype) . "'
						AND (bidstatus = 'awarded' AND bidstate = 'wait_approval'
							OR bidstatus = 'placed' AND bidstate = 'wait_approval'
							OR bidstatus = 'awarded' AND bidstate = '')
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						$count1++;
					}
					$html .= $ilance->language->construct_phrase('{_congrats_youve_sold_x_items}', $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
					$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_congrats_youve_sold_x_items}', $count1) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;orderby=bids,buynow_purchases&amp;displayorder=desc">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
				$sql2 = $ilance->db->query("
					SELECT project_id
					FROM " . DB_PREFIX . "buynow_orders
					WHERE owner_id = '" . intval($userid) . "'
						AND orderdate > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					$count1 = $ilance->db->num_rows($sql2);
					$html .= $ilance->language->construct_phrase('{_congrats_youve_sold_x_items}', $count1) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;displayorder=desc">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
					$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_congrats_youve_sold_x_items}', $count1) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;sub=sold&amp;displayorder=desc">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
			// #### inform a provider that one or more bids have been awarded and waiting for response via accept or reject
			else if ($cattype == 'service')
			{
				$sqlextra = "AND (bidstatus = 'awarded' AND bidstate = 'wait_approval' OR bidstatus = 'placed'  AND bidstate = 'wait_approval' OR bidstatus = 'awarded' AND bidstate = '')";
				$sql2 = $ilance->db->query("
					SELECT bid_id, project_id, bidamount, qty, date_awarded
					FROM " . DB_PREFIX . "project_bids
					WHERE user_id = '" . $userid . "'
						AND state = '" . $cattype . "'
						$sqlextra
						AND date_awarded > DATE_SUB('" . DATETIME24H . "', INTERVAL $daysago DAY)
				", 0, null, __FILE__, __LINE__);
				if ($ilance->db->num_rows($sql2) > 0)
				{
					while ($res = $ilance->db->fetch_array($sql2, DB_ASSOC))
					{
						$currencyid = fetch_auction('currencyid', $res['project_id']);
						$date = '<strong>' . print_date($res['date_awarded'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0) . '</strong>';
						$cost = '<strong>' . $ilance->currency->format($res['bidamount'], $currencyid) . '</strong>';
						$name = fetch_auction('project_title', $res['project_id']);
						$bidid = $res['bid_id'];
						$count++;
					}
					$html .= $ilance->language->construct_phrase('{_congrats_x_bid_proposals_have_been_awarded}', $count) . '&nbsp;&nbsp;<span class="smaller gray">(<span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded&amp;displayorder=desc">{_take_me_there}</a></span>)</span><hr size="1" width="100%" style="color:#cccccc" />';
					$htmlv4 .= '<h2>' . $ilance->language->construct_phrase('{_congrats_x_bid_proposals_have_been_awarded}', $count) . '<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bigcheckbox.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['selling'] . '?cmd=management&amp;bidsub=awarded&amp;displayorder=desc">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
				}
			}
		}
		return array($html, $htmlv4);
	}
	
	function fetch_feedback_comment($projectid = 0, $fromuserid = 0, $foruserid = 0)
	{
		global $ilance, $ilconfig;
		$sql = $ilance->db->query("
			SELECT comments, response
			FROM " . DB_PREFIX . "feedback
			WHERE from_user_id = '" . intval($fromuserid) . "'
				AND for_user_id = '" . intval($foruserid) . "'
				AND project_id = '" . intval($projectid) . "'
			LIMIT 1
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			switch ($res['response'])
			{
				case 'positive':
				{
					$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/positive.png" align="absmiddle" alt="{_positive}" width="20" />';
					break;
				}                                        
				case 'neutral':
				{
					$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/neutral.png" align="absmiddle" alt="{_neutral}" width="20" />';
					break;
				}                                        
				case 'negative':
				{
					$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/negative.png" align="absmiddle" alt="{_negative}" width="20" />';
					break;
				}                                        
				default:
				{
					$icon = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/neutral.png" align="absmiddle" alt="{_pending}" width="20" />';
					break;
				}
			}
			return $icon . '&nbsp;&nbsp;' . ((empty($res['comments']) ? '{_no_comment}' : stripslashes(handle_input_keywords($res['comments']))));
		}
		return '{_feedback_has_not_yet}';
	}
	
	/*
	* Function to fetch any feedback actions or related activity and notifications for the currently logged in member
	*
	* @param      integer       user id
	* @param      string        show view type (default all; optional: bought or sold)
	*
	* @return     string        Returns HTML formatted details of feedback activity actions required by member
	*/
	function feedback_activity($userid = 0, $showview = 'all')
	{
		global $ilance, $show, $ilpage, $phrase, $ilconfig;
		$html1 = $html2 = $query_field_condition = '';
		$final = array();
		$count = 0;
		
		($apihook = $ilance->api('feedback_activity_start')) ? eval($apihook) : false;
		
		if ($showview == 'all' OR $showview == 'bought')
		{
			// #### AS A BUYER BUYING ITEMS VIA BUY NOW ############
			// does the buyer need to leave feedback for any seller purchased via buy now?
			$query = $ilance->db->query("
				SELECT b.orderid, b.project_id, b.owner_id AS seller_id, b.buyer_id, b.orderdate AS enddate, p.project_title, p.project_details, p.filtered_auctiontype, p.buynow, u.username
				FROM " . DB_PREFIX . "buynow_orders b
				LEFT JOIN " . DB_PREFIX . "projects AS p ON (p.project_id = b.project_id)
				LEFT JOIN " . DB_PREFIX . "users u ON (u.user_id = b.owner_id)
				WHERE b.buyer_id = '" . intval($userid) . "'
					AND p.project_state = 'product'
					AND b.buyerfeedback = '0'
					AND p.project_title != ''
					$query_field_condition
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_title'] = handle_input_keywords($res['project_title']);
					$res['project_state'] = 'product';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_seller}';
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] = $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '1')
					{				  
						$res['format'] = '{_fixed_price} + {_auction}';
					}
					else if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '0')
					{
						$res['format'] = '{_auction}';
					}
					else
					{				  
						$res['format'] = '{_fixed_price}';
					}
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['seller_id'], $res['buyer_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			// #### AS A SERVICE BUYER #############################
			// does this buyer need to leave feedback for any provider?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS buyer_id, b.user_id AS seller_id, date_end AS enddate, u.username, b.bid_id
				FROM " . DB_PREFIX . "projects AS p
				INNER JOIN " . DB_PREFIX . "project_realtimebids b ON b.project_id = p.project_id
				INNER JOIN " . DB_PREFIX . "users u ON u.user_id = b.user_id
				WHERE p.user_id = '" . intval($userid) . "'
				AND p.user_id = b.project_user_id
				AND b.bidstatus = 'awarded'
				AND p.project_state = 'service'
				AND p.status = 'approval_accepted'
				GROUP BY b.project_id
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_title'] = handle_input_keywords($res['project_title']);
					$res['project_state'] = 'service';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['rfp'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_provider}';
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] =  $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$res['orderid'] = $res['bid_id'];
					$res['format'] = '{_job}';
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['seller_id'], $res['buyer_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			// #### AS A PRODUCT BUYER #############################
			// does this buyer need to leave feedback for any seller?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS seller_id, p.filtered_auctiontype, p.buynow, b.user_id AS buyer_id, b.bid_id, date_end AS enddate, u.username
				FROM " . DB_PREFIX . "projects AS p
				LEFT JOIN " . DB_PREFIX . "project_bids AS b ON b.project_user_id = p.user_id
				LEFT JOIN " . DB_PREFIX . "users u ON u.user_id = p.user_id
				WHERE b.user_id = '" . intval($userid) . "'
					AND p.project_state = 'product'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
					AND p.buyerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_title'] = handle_input_keywords($res['project_title']);
					$res['project_state'] = 'product';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_seller}';
					$res['fromtype'] = 'seller';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] =  $res['seller_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$res['orderid'] = $res['bid_id'];
					if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '1')
					{				  
						$res['format'] = '{_fixed_price} + {_auction}';
					}
					else if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '0')
					{
						$res['format'] = '{_auction}';
					}
					else
					{				  
						$res['format'] = '{_fixed_price}';
					}
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['seller_id'], $res['buyer_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 1;
					$final[] = $res;
					$count++;
				}
			}
			
			($apihook = $ilance->api('feedback_activity_show_all_or_bought_end')) ? eval($apihook) : false;
		}
		if ($showview == 'all' OR $showview == 'sold')
		{
			// #### AS A PRODUCT SELLER ############################
			// does this seller need to leave feedback for any winning bidders or buy now purchasers?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS seller_id, p.project_details, p.filtered_auctiontype, p.buynow, b.user_id AS buyer_id, b.bid_id, date_end AS enddate, u.username
				FROM " . DB_PREFIX . "projects AS p,
				" . DB_PREFIX . "project_bids AS b
				LEFT JOIN " . DB_PREFIX . "users u ON u.user_id = b.user_id
				WHERE p.user_id = '" . intval($userid) . "'
					AND b.project_user_id = p.user_id
					AND p.project_state = 'product'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
					AND p.sellerfeedback = '0'
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_title'] = handle_input_keywords($res['project_title']);
					$res['project_state'] = 'product';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_buyer}';
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$res['orderid'] = $res['bid_id'];
					if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '1')
					{				  
						$res['format'] = '{_fixed_price} + {_auction}';
					}
					else if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '0')
					{
						$res['format'] = '{_auction}';
					}
					else
					{				  
						$res['format'] = '{_fixed_price}';
					}
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['buyer_id'], $res['seller_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			// #### AS A SELLER SELLING ITEMS VIA BUY NOW ##########
			// does the seller need to leave feedback for any buyer purchased via buy now?
			$query = $ilance->db->query("
				SELECT b.orderid, b.project_id, b.buyer_id, b.owner_id AS seller_id, b.orderdate AS enddate, u.username, p.project_title, p.status, p.project_details, p.filtered_auctiontype, p.buynow
				FROM " . DB_PREFIX . "buynow_orders b
				LEFT JOIN " . DB_PREFIX . "projects AS p ON (p.project_id = b.project_id)
				LEFT JOIN " . DB_PREFIX . "users u ON (u.user_id = b.buyer_id)
				WHERE b.owner_id = '" . intval($userid) . "'
					AND p.project_state = 'product'
					AND b.sellerfeedback = '0'
					AND p.project_title != ''
					AND p.status != ''
					$query_field_condition
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$status = $res['status'];
					$url = ($status == 'open')
						? $ilpage['selling'] . '?cmd=management'
						: $ilpage['selling'] . '?cmd=management&amp;sub=sold';
					unset($status, $url);
					$res['project_title'] = handle_input_keywords($res['project_title']);
					$res['project_state'] = 'product';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_buyer}';
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '1')
					{				  
						$res['format'] = '{_fixed_price} + {_auction}';
					}
					else if ($res['filtered_auctiontype'] == 'regular' AND $res['buynow'] == '0')
					{
						$res['format'] = '{_auction}';
					}
					else
					{				  
						$res['format'] = '{_fixed_price}';
					}
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['buyer_id'], $res['seller_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			// #### AS A SERVICE PROVIDER ##################################
			// does this provider need to leave feedback for any buyer?
			$query = $ilance->db->query("
				SELECT p.project_title, p.project_id, p.currencyid, p.user_id AS buyer_id, b.user_id AS seller_id, b.bid_id, date_end AS enddate, u.username
				FROM " . DB_PREFIX . "projects AS p
				LEFT JOIN " . DB_PREFIX . "project_realtimebids AS b ON b.project_user_id = p.user_id
				LEFT JOIN " . DB_PREFIX . "users u ON u.user_id = p.user_id
				WHERE b.user_id = '" . intval($userid) . "'
					AND p.project_state = 'service'
					AND b.bidstatus = 'awarded'
					AND b.project_id = p.project_id
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($query) > 0)
			{
				while ($res = $ilance->db->fetch_array($query, DB_ASSOC))
				{
					$res['project_state'] = 'service';
					$res['photo'] = $ilance->auction->print_item_photo($ilpage['rfp'] . '?id=' . $res['project_id'], 'thumb', $res['project_id']);
					$res['customer'] = $res['username'];
					$res['usertype'] = '{_buyer}';
					$res['fromtype'] = 'buyer';
					$res['enddate'] = print_date($res['enddate'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
					$res['for_user_id'] =  $res['buyer_id'];
					$res['from_user_id'] = intval($userid);
					$res['md5'] = md5($res['customer'] . $res['enddate'] . rand(1, 9999));
					$res['orderid'] = $res['bid_id'];
					$res['format'] = '{_job}';
					$res['customercomment'] = $this->fetch_feedback_comment($res['project_id'], $res['buyer_id'], $res['seller_id']);
					$GLOBALS['show_stars' . $res['project_id']] = 0;
					$final[] = $res;
					$count++;
				}
			}
			
			($apihook = $ilance->api('feedback_activity_show_all_or_sold_end')) ? eval($apihook) : false;
		}
		if ($count > 0)
		{
			$html1 = '<span class="blue"><a href="' . HTTP_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">{_leave_feedback}</a></span> {_for} ' . $count . ' {_transactions_lower}.<hr size="1" width="100%" style="color:#cccccc" />';
			$html2 = '<h2>{_leave_feedback} {_for} ' . $count . ' {_transactions_lower}<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTPS_SERVER . $ilpage['feedback'] . '?cmd=_leave-feedback">{_leave_feedback}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
		}
		return array($final, $html1, $html2);
	}
    
	/*
	* Function to construct a profile input form based on a particular question id being supplied as the argument
	*
	* @param      integer       question id       
	*
	* @return     string 
	*/
	function construct_profile_input($qid = 0)
	{
		global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilpage;
		$sql = $ilance->db->query("
			SELECT questionid, inputtype, multiplechoice
			FROM " . DB_PREFIX . "profile_questions
			WHERE questionid = '" . intval($qid) . "'
				AND visible = '1'
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$sql2 = $ilance->db->query("
				SELECT answerid, questionid, user_id, answer, date, visible, isverified, verifyexpiry, invoiceid, contactname, contactnumber, contactnotes
				FROM " . DB_PREFIX . "profile_answers
				WHERE questionid = '" . $res['questionid'] . "'
					AND user_id = '" . $_SESSION['ilancedata']['user']['userid'] . "'
					AND visible = '1'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql2) > 0)
			{
				$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
				if ($res2['isverified'])
				{
					// does admin allow answer updates after verification?
					$res2['disabled'] = ($ilconfig['verificationupdateafter']) ? '' : 'disabled="disabled"';    
				}
				else
				{
					$res2['disabled'] = '';
				}
				switch ($res['inputtype'])
				{
					case 'yesno':
					{
						$selected1 = $selected2 = '';
						if ($res2['answer'] == '1')
						{
							$selected1 = 'checked="checked"';
						}
						else if ($res2['answer'] == '')
						{
							$selected1 = 'checked="checked"';
						}
						$input = '{_yes}'.' <input type="radio" name="question[' . $res['questionid'] . ']" value="1" ' . $selected1 . ' ' . $res2['disabled'] . ' />&nbsp;&nbsp;';
						if ($res2['answer'] == '0')
						{
							$selected2 = 'checked="checked"';
						}
						else if ($res2['answer'] == '')
						{
							$selected1 = 'checked="checked"';
						}
						$input .= '{_no}' .'<input type="radio" name="question[' . $res['questionid'] . ']" value="0" ' . $selected2 . ' ' . $res2['disabled'] . ' />';
						break;
					}					 
					case 'int':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="' . stripslashes($res2['answer']) . '" class="input" style="width:60px" ' . $res2['disabled'] . ' />';
						break;
					}					 
					case 'textarea':
					{
						$input = '<textarea id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" class="input" style="width: 425px; height: 84px" wrap="physical" ' . $res2['disabled'] . '>' . stripslashes($res2['answer']) . '</textarea>';
						break;
					}					 
					case 'text':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="' . stripslashes($res2['answer']) . '" class="input" ' . $res2['disabled'] . ' />';
						break;
					}				    
					case 'multiplechoice':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:425px; height:90px; font-family: verdana" multiple name="question[' . $res['questionid'] . '][]" id="question' . $res['questionid'] . ' ' . $res2['disabled'] . '">';
							$input .= '<optgroup label="{_select}:">';
							$answers = (empty($res2['answer']))
								? array()
								: ((is_serialized($res2['answer']))
									? unserialize(stripslashes(trim($res2['answer'])))
									: array(stripslashes(trim($res2['answer']))));
							foreach ($choices AS $choice)
							{
								$input .= (in_array(trim($choice), $answers)) ? '<option value="' . trim($choice) . '" selected="selected">' . $choice . '</option>' : '<option value="' . trim($choice) . '">' . $choice . '</option>';
							}
							$input .= '</optgroup>';
							$input .= '</select>';
						}
						else
						{
							$input .= '{_not_available}';    
						}
						break;
					}		    
					case 'pulldown':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '<select name="question[' . $res['questionid'] . ']" id="question' . $res['questionid'] . '" class="select" ' . $res2['disabled'] . '>';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= (trim($res2['answer']) == trim($choice)) ? '<option value="' . trim($choice) . '" selected="selected">' . trim($choice) . '</option>' : '<option value="' . trim($choice) . '">' . trim($choice) . '</option>';
								}
							}
							$input .= '</select>';
						}
						break;
					}
				}
			}
			else
			{
				// #### answer type input ######################
				switch ($res['inputtype'])
				{
					case 'yesno':
					{
						$input = '<label for="yes">{_yes}</label><input type="radio" id="yes" name="question[' . $res['questionid'] . ']" value="1" checked="checked" />&nbsp;&nbsp;';
						$input .= '<label for="no">{_no}</label><input type="radio" id="no" name="question[' . $res['questionid'] . ']" value="0" />';
						break;
					}					
					case 'int':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="" class="input" />';
						break;
					}					
					case 'textarea':
					{
						$input = '<textarea id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" class="input" style="width: 425px; height: 84px" wrap="physical"></textarea>';
						break;
					}					
					case 'text':
					{
						$input = '<input type="text" id="question' . $res['questionid'] . '" name="question[' . $res['questionid'] . ']" value="" class="input" />';
						break;
					}				    
					case 'multiplechoice':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '{_hold_down_the_ctrl_key_on_your_keyboard_to_select_multiple_choices}<br /><select style="width:425px; height:90px" multiple name="question[' . $res['questionid'] . '][]" id="question' . $res['questionid'] . '" class="select">';
							$input .= '<optgroup label="{_select}:">';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= '<option value="' . trim($choice) . '">' . trim($choice) . '</option>';
								}
							}
							$input .= '</optgroup>';
							$input .= '</select>';
						}
						else
						{
							$input .= '{_not_available}';  
						}
						break;
					}		    
					case 'pulldown':
					{
						if (!empty($res['multiplechoice']))
						{
							$choices = explode('|', $res['multiplechoice']);
							$input = '<select name="question[' . $res['questionid'] . ']" id="question' . $res['questionid'] . '" style="font-family: verdana">';
							foreach ($choices AS $choice)
							{
								if (!empty($choice))
								{
									$input .= '<option value="' . trim($choice) . '">' . trim($choice) . '</option>';
								}
							}
							$input .= '</select>';
						}
						break;
					}
				}
			}
			return $input;
		}
	}
	
	/*
	* Function to fetch and display any unpaid transactions left between the viewing user and other trading partners.
	*
	* @param      integer       user id
	* @param      string        type (buying or selling)
	* @param      integer       date range period (default -1)
	*
	* @return     string        HTML formatted response.
	*/
	function unpaid_p2b_activity($userid = 0, $type = '', $period = -1)
	{
		global $ilance, $ilpage, $phrase, $ilconfig;
		$html = $htmlv4 = '';
		if ($type == 'buying')
		{
			$sql = $ilance->db->query("
				SELECT i.projectid, i.p2b_user_id, i.user_id, i.invoiceid, i.transactionid, p.project_title
				FROM " . DB_PREFIX . "invoices i
				LEFT JOIN " . DB_PREFIX . "projects p ON (p.project_id = i.projectid)
				WHERE i.user_id = '" . intval($userid) . "'
					AND i.invoicetype = 'p2b'
					AND i.status = 'unpaid'
			", 0, null, __FILE__, __LINE__);
		}
		else if ($type == 'selling')
		{
			$sql = $ilance->db->query("
				SELECT i.projectid, i.p2b_user_id, i.user_id, i.invoiceid, i.transactionid, p.project_title
				FROM " . DB_PREFIX . "invoices i
				LEFT JOIN " . DB_PREFIX . "projects p ON (p.project_id = i.projectid)
				WHERE i.p2b_user_id = '" . intval($userid) . "'
					AND i.invoicetype = 'p2b'
					AND i.status = 'unpaid'
			", 0, null, __FILE__, __LINE__);	
		}
		// #### does listing have pending p2b invoices unpaid? #########
		if ($ilance->db->num_rows($sql) > 0)
		{
			$pendinvoices = '';
			$invoices = 0;
			while ($res_inv = $ilance->db->fetch_array($sql, DB_ASSOC))
			{
				//$title = fetch_auction('project_title', $res_inv['projectid']);
				$title = $res_inv['project_title'];
				$projectid = $res_inv['projectid'];
				$providerid = $res_inv['p2b_user_id'];
				$buyerid = $res_inv['user_id'];
				$crypted = array('id' => $res_inv['invoiceid']);
				$pendinvoices .= '<span class="blue"><a href="' . $ilpage['invoicepayment'] . '?cmd=view&amp;txn=' . $res_inv['transactionid'] . '" title="#' . $res_inv['invoiceid'] . '">#' . $res_inv['invoiceid'] . '</a></span>, ';
				$invoices++;
			}
			$pendinvoices = (!empty($pendinvoices)) ? mb_substr($pendinvoices, 0, -2) : '';
			$invoicephrase = ($invoices == 1) ? '{_invoice_lower}' : '{_invoices_lower}';
			if ($type == 'buying')
			{
				$html = $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' {_generated_by} <span class="blue"><a href="' . $ilpage['members'] . '?id=' . $providerid . '">' . fetch_user('username', $providerid) . '</a></span> {_is_waiting_on_payment_for_the} <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> {_listing_lower}.<hr size="1" width="100%" style="color:#cccccc" />';
				$htmlv4 = '<h2>' . $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' {_generated_by} ' . fetch_user('username', $providerid) . ' {_is_waiting_on_payment_for_the} <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> {_listing_lower}.<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['accounting'] . '">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
			else if ($type == 'selling')
			{
				$html = $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' {_generated_to} <span class="blue"><a href="' . $ilpage['members'] . '?id=' . $buyerid . '">' . fetch_user('username', $buyerid) . '</a></span> {_for_the} <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> {_listing_still_pending_payment}.<hr size="1" width="100%" style="color:#cccccc" />';
				$htmlv4 = '<h2>' . $invoices . ' ' . $invoicephrase . ': ' . $pendinvoices . ' {_generated_to} ' . fetch_user('username', $buyerid) . ' {_for_the} <span class="blue"><a href="' . $ilpage['rfp'] . '?id=' . $projectid . '">' . $title . '</a></span> {_listing_still_pending_payment}.<span style="float:left;margin-right:10px"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'v4/bignotice.png" height="36" /></span></h2><p style="margin-top:4px"><span class="blue"><a href="' . HTTP_SERVER . $ilpage['accounting'] . '">{_take_me_there}</a></span></p><div style="height:2px;width:100%;background-color:#ccc;margin:20px 0 20px 0"></div>';
			}
		}
		return array($html, $htmlv4);
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>