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
* Function to handle inserting a service reverse auction bid
*
* @package      iLance\Bid\Service
* @version      4.0.0.8059
* @author       ILance
*/
class bid_service extends bid
{
	/**
        * Function for inserting a new service bid proposal on a service auction event.
        * If a service provider has already placed a bid on this particular project
        * this function will update that previous bid to the new amount specified.
        * Additionally, if the previous bid was declined this new bid will be inserted
        * vs being updated.
        *
        * @param       integer      bidder id
        * @param       string       bid proposal message
        * @param       integer      low bid notify filter (optional)
        * @param       integer      last hour notify filter (optional)
        * @param       integer      project id
        * @param       integer      project owner id
        * @param       string       bid amount
        * @param       integer      estimated number of days
        * @param       string       bid state status
        * @param       string       bid amount type
        * @param       string       custom argument
        * @param       array        bid field answers
        * @param       string       payment method chosen by the provider during bid
        * @param       boolean      show error messages (disable if you want to call this function via API to hide html error messages; this will then only return true or false) - default true
        */
        function placebid($bidderid = 0, $proposal = '', $lowbidnotify = 0, $lasthournotify = 0, $subscribed = 0, $id = 0, $project_user_id = 0, $bidamount = 0, $estimate_days = 0, $bidstate = '', $bidamounttype = '', $bidcustom = '', $bidfieldanswers = '', $paymethod = '', $showerrormessages = true)
        {
                global $ilance, $ilpage, $phrase, $ilconfig, $area_title, $page_title;

                if ($ilance->permissions->check_access(intval($bidderid), 'servicebid') == 'no')
                {
                        $area_title = '{_buying_menu_denied_upgrade_subscription}';
                        $page_title = SITE_NAME . ' - ' . '{_buying_menu_denied_upgrade_subscription}';
                        
                        if ($showerrormessages)
                        {
                                print_notice('{_access_denied}', '{_your_current_subscription_level_does_not_permit_you_to_use_this_marketplace_resource}'." <a href='".$ilpage['subscription']."'><strong>".'{_click_here}'."</strong></a>", $ilpage['subscription'], ucwords('{_click_here}'), fetch_permission_name('servicebid'));
                                exit();
                        }
                        else
                        {
                                return false;
                        }        
                }
                
                $area_title = '{_submitting_bid_proposal}';
                $page_title = SITE_NAME . ' ' . '{_submitting_bid_proposal}';
                
		$currencyid = fetch_auction('currencyid', $id);
		
                $sqlexpiry = $ilance->db->query("
                        SELECT UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, status, bids, project_title
                        FROM " . DB_PREFIX . "projects 
                        WHERE project_id = '" . intval($id) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sqlexpiry) > 0)
                {
                        $resexpiry = $ilance->db->fetch_array($sqlexpiry, DB_ASSOC);
                        if ($resexpiry['mytime'] < 0 OR $resexpiry['status'] != 'open')
                        {
                                $area_title = '{_this_rfp_has_expired_bidding_is_over}';
                                $page_title = SITE_NAME . ' - ' . '{_this_rfp_has_expired_bidding_is_over}';
                                
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
                unset($sqlexpiry, $resexpiry);
                
                // #### add project to watchlist if applicable #################
                $ilance->watchlist->insert_item(intval($bidderid), $id, 'auction', 'n/a', $lowbidnotify, 0, $lasthournotify, $subscribed);
                
                if (empty($bidcustom))
                {
                        $bidcustom = '';
                }
    
                if (empty($proposal))
                {
                        $proposal = '';	
                }
    
                // #### determine if listing is realtime auction ###############
                $project_details = fetch_auction('project_details', intval($id));
    
                // #### did we already place a bid on this project? ############
                $sql = $ilance->db->query("
                        SELECT bid_id, bidstatus, bidstate
                        FROM " . DB_PREFIX . "project_bids
                        WHERE user_id = '" . intval($bidderid) . "'
                            AND project_id = '" . intval($id) . "'
                        ORDER BY bid_id DESC
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
		                        if ($res['bidstatus'] == 'declined' OR $res['bidstate'] == 'retracted')
		                        {
		                                // #### insert bid proposal ####################
		                                $ilance->db->query("
		                                        INSERT INTO " . DB_PREFIX . "project_bids
		                                        (bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
		                                        VALUES(
		                                        NULL,
		                                        '" . intval($bidderid) . "',
		                                        '" . intval($id) . "',
		                                        '" . intval($project_user_id) . "',
		                                        '" . $ilance->db->escape_string($proposal) . "',
		                                        '" . sprintf("%01.2f", $bidamount) . "',
		                                        '" . intval($estimate_days) . "',
		                                        '" . DATETIME24H . "',
		                                        'placed',
		                                        '" . $ilance->db->escape_string($bidstate) . "',
		                                        '" . $ilance->db->escape_string($bidamounttype) . "',
		                                        '" . $ilance->db->escape_string($bidcustom) . "',
		                                        'service',
												'" . $ilance->db->escape_string($paymethod) . "')
		                                ", 0, null, __FILE__, __LINE__);
		                                $this_bid_id = $ilance->db->insert_id();
		                                
		                                // insert realtime bid proposal
		                                $ilance->db->query("
		                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
		                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
		                                        VALUES(
		                                        NULL,
		                                        '" . intval($this_bid_id) . "',
		                                        '" . intval($bidderid) . "',
		                                        '" . intval($id) . "',
		                                        '" . intval($project_user_id) . "',
		                                        '" . $ilance->db->escape_string($proposal) . "',
		                                        '" . sprintf("%01.2f", $bidamount) . "',
		                                        '" . intval($estimate_days) . "',
		                                        '" . DATETIME24H . "',
		                                        'placed',
		                                        '" . $ilance->db->escape_string($bidstate) . "',
		                                        '" . $ilance->db->escape_string($bidamounttype) . "',
		                                        '" . $ilance->db->escape_string($bidcustom) . "',
		                                        'service',
												'" . $ilance->db->escape_string($paymethod) . "')
		                                ", 0, null, __FILE__, __LINE__);   
		                                
		                                $this_id = $ilance->db->insert_id(); 
		                                
		                                // update bid count for auction
		                                $ilance->db->query("
		                                        UPDATE " . DB_PREFIX . "projects
		                                        SET bids = bids + 1
		                                        WHERE project_id = '" . intval($id) . "'
		                                ", 0, null, __FILE__, __LINE__);
		                                
		                                // will increase bidstoday and bidsthismonth for the user placing a bid
		                                $this->set_bid_counters(intval($bidderid), 'increase');
		                        }
		                        else
		                        {
		                                // update/revise existing bid amount placed
		                                $ilance->db->query("
		                                        UPDATE " . DB_PREFIX . "project_bids
		                                        SET proposal = '" . $ilance->db->escape_string($proposal) . "',
		                                        bidamount = '" . sprintf("%01.2f", $bidamount) . "',
		                                        estimate_days = '" . intval($estimate_days) . "',
		                                        bidamounttype = '" . $ilance->db->escape_string($bidamounttype) . "',
		                                        bidcustom = '" . $ilance->db->escape_string($bidcustom) . "',
												winnermarkedaspaidmethod = '" . $ilance->db->escape_string($paymethod) . "',
												date_updated = '" . DATETIME24H . "'
		                                        WHERE bid_id = '" . $res['bid_id'] . "'
		                                ", 0, null, __FILE__, __LINE__);
		                                
		                                // make sure our realtime applet has some live bid history info
		                                $ilance->db->query("
		                                        INSERT INTO " . DB_PREFIX . "project_realtimebids
		                                        (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
		                                        VALUES(
		                                        NULL,
		                                        '" . $res['bid_id'] . "',
		                                        '" . intval($bidderid) . "',
		                                        '" . intval($id) . "',
		                                        '" . intval($project_user_id) . "',
		                                        '" . $ilance->db->escape_string($proposal) . "',
		                                        '" . sprintf("%01.2f", $bidamount) . "',
		                                        '" . intval($estimate_days) . "',
		                                        '" . DATETIME24H . "',
		                                        'placed',
		                                        '".$ilance->db->escape_string($bidstate) . "',
		                                        '".$ilance->db->escape_string($bidamounttype) . "',
		                                        '".$ilance->db->escape_string($bidcustom) . "',
		                                        'service',
												'" . $ilance->db->escape_string($paymethod) . "')
		                                ", 0, null, __FILE__, __LINE__);    
		                                
		                                $this_bid_id = $res['bid_id'];
		                                $this_id = $ilance->db->insert_id();
		                                
		                                if($ilance->categories->bidgrouping(fetch_auction('cid', $id)) == 0)
		                                {
		                                	// update bid count for auction
			                                $ilance->db->query("
			                                        UPDATE " . DB_PREFIX . "projects
			                                        SET bids = bids + 1
			                                        WHERE project_id = '" . intval($id) . "'
			                                ", 0, null, __FILE__, __LINE__);
			                                
		                                	// will increase bidstoday and bidsthismonth for the user placing a bid
		                                	$this->set_bid_counters(intval($bidderid), 'increase');
		                                }
		                        }
								
                        }
                }
                
		// #### brand new bid proposal #################################
		else
                {               
                        // #### insert bid proposal ############################
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "project_bids
                                (bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                VALUES(
                                NULL,
                                '" . intval($bidderid) . "',
                                '" . intval($id) . "',
                                '" . intval($project_user_id) . "',
                                '" . $ilance->db->escape_string($proposal) . "',
                                '" . sprintf("%01.2f", $bidamount) . "',
                                '" . intval($estimate_days) . "',
                                '" . DATETIME24H . "',
                                'placed',
                                '" . $ilance->db->escape_string($bidstate) . "',
                                '" . $ilance->db->escape_string($bidamounttype) . "',
                                '" . $ilance->db->escape_string($bidcustom) . "',
                                'service',
				'" . $ilance->db->escape_string($paymethod) . "')
                        ", 0, null, __FILE__, __LINE__);
                        $this_bid_id = $ilance->db->insert_id();
                        
                        // #### make sure our realtime applet has some live bid history info
                        $ilance->db->query("
                                INSERT INTO " . DB_PREFIX . "project_realtimebids
                                (id, bid_id, user_id, project_id, project_user_id, proposal, bidamount, estimate_days, date_added, bidstatus, bidstate, bidamounttype, bidcustom, state, winnermarkedaspaidmethod)
                                VALUES(
                                NULL,
                                '" . $this_bid_id . "',
                                '" . intval($bidderid) . "',
                                '" . intval($id) . "',
                                '" . intval($project_user_id) . "',
                                '" . $ilance->db->escape_string($proposal) . "',
                                '" . sprintf("%01.2f", $bidamount) . "',
                                '" . intval($estimate_days) . "',
                                '" . DATETIME24H . "',
                                'placed',
                                '" . $ilance->db->escape_string($bidstate) . "',
                                '" . $ilance->db->escape_string($bidamounttype) . "',
                                '" . $ilance->db->escape_string($bidcustom) . "',
                                'service',
								'" . $ilance->db->escape_string($paymethod) . "')
                        ", 0, null, __FILE__, __LINE__);    
                        
                        $this_id = $ilance->db->insert_id();
                        
                        // #### update bid count for auction
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bids = bids + 1
                                WHERE project_id = '" . intval($id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        // will increase bidstoday and bidsthismonth
                        $this->set_bid_counters(intval($bidderid), 'increase');
                        
                        // was this service provider invited?
                        $sql_invites = $ilance->db->query("
                                SELECT id
                                FROM " . DB_PREFIX . "project_invitations
                                WHERE project_id = '" . intval($id) . "'
                                    AND seller_user_id = '" . intval($bidderid) . "'
                                    AND bid_placed = 'no'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_invites) > 0)
                        {
                                // update invitations with bid placed for invited service provider
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_invitations
                                        SET bid_placed = 'yes',
                                        date_of_bid = '" . DATETIME24H . "'
                                        WHERE seller_user_id = '" . intval($bidderid) . "'
                                            AND project_id = '" . intval($id) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                
                                $url = HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($id);
                                
                                // email user
                                $ilance->email->mail = fetch_user('email', $project_user_id);
                                $ilance->email->slng = fetch_user_slng($project_user_id);
                                $ilance->email->get('invited_bid_placed_buyer');		
                                $ilance->email->set(array(
                                        '{{buyer}}' => fetch_user('username', $project_user_id),
                                        '{{vendor}}' => fetch_user('username', $bidderid),
                                        '{{rfp_title}}' => fetch_auction('project_title', intval($id)),
                                        '{{project_id}}' => intval($id),
                                        '{{url}}' => $url,
                                ));                                
                                $ilance->email->send();
                        }
                }
    
                // #### capture custom bid fields ######################
                $ilance->bid_fields->process_custom_bid_fields($bidfieldanswers, intval($id), $this_bid_id, $this_id);
                
                // #### lower bid notification bulk email sender #######
                $ilance->watchlist->send_notification(intval($bidderid), 'lowbidnotify', intval($id), $bidamount);
    
		($apihook = $ilance->api('service_sending_notifications_end')) ? eval($apihook) : false;
	
                $ilance->email->mail = fetch_user('email', $project_user_id);
                $ilance->email->slng = fetch_user_slng($project_user_id);
                $ilance->email->get('service_bid_notification_alert');		
                $ilance->email->set(array(
                	'{{ownername}}' => fetch_user('username', $project_user_id),
                        '{{provider}}' => fetch_user('username', $bidderid),
                        '{{price}}' => $ilance->currency->format($bidamount, $currencyid),
                        '{{p_id}}' => intval($id),
			'{{project_title}}' => strip_tags(fetch_auction('project_title', intval($id))),
			'{{url}}' => HTTP_SERVER . $ilpage['rfp'] . '?id=' . intval($id),
                ));
                $ilance->email->send();
		if (fetch_auction('filter_bidlimit', $id) == 1 AND fetch_auction('filtered_bidlimit', $id) > 0)
		{
			$count_bids = $ilance->db->query("
				SELECT bid_id, bidamount, proposal, user_id
				FROM " . DB_PREFIX . "project_bids
				WHERE project_id = '" . intval($id) . "' GROUP BY user_id
			", 0, null, __FILE__, __LINE__);						
			if ($ilance->db->num_rows($count_bids) >= fetch_auction('filtered_bidlimit',$id))
			{
				$bids = $ilance->db->query("
					SELECT AVG(bidamount) AS avg, MAX(bidamount) AS max, MIN(bidamount) AS min
					FROM " . DB_PREFIX . "project_bids
					WHERE project_id = '" . intval($id) . "' GROUP BY user_id
				", 0, null, __FILE__, __LINE__);
				$fetch_bids =  $ilance->db->fetch_array($bids, DB_ASSOC);
				$contractors_overview = '';
				while ($fetch_query = $ilance->db->fetch_array($count_bids, DB_ASSOC))
				{
					$contractors_overview .= '<br/><br/>*************************************<br/>{_contractor} : ' . fetch_user('username',$fetch_query['user_id']) . '<br/>{_bid_amount} : ' . $ilance->currency->format($fetch_query['bidamount']) . '<br/>{_bid_proposal} : ' . $fetch_query['proposal']. '<br/>*************************************<br/><br/>';   
				}
				$ilance->db->query("
					UPDATE " . DB_PREFIX . "projects
					SET status = 'expired',
					close_date = '" . DATETIME24H . "' 
					WHERE project_id = '" . intval($id) . "'
				", 0, null, __FILE__, __LINE__);
				$ilance->email->mail = fetch_user('email', $project_user_id);
				$ilance->email->slng = fetch_user_slng($project_user_id);
				$ilance->email->get('bid_limit_ended');		
				$ilance->email->set(array(
						'{{buyer}}' => fetch_user('username', $project_user_id),
						'{{project_title}}' => fetch_auction('project_title',$id),
						'{{bidslimit}}' => fetch_auction('filter_bidlimit',$id),
						'{{averagebid}}' => $ilance->currency->format($fetch_bids['avg']),
						'{{lowestbid}}' => $ilance->currency->format($fetch_bids['min']),
						'{{highestbid}}' => $ilance->currency->format($fetch_bids['max']),
						'{{contractors_overview}}' => $contractors_overview,
				));
				$ilance->email->send();
			}
		}
                log_event($_SESSION['ilancedata']['user']['userid'], $ilpage['rfp'], $ilance->GPC['cmd'], $ilance->GPC['subcmd'], $ilance->GPC['id']);
                if ($showerrormessages)
                {
                        // todo: detect seo
                        refresh($ilpage['rfp'] . '?id=' . intval($id));
                        exit();
                }
                return true;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>