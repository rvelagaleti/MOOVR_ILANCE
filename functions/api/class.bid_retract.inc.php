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
* Retract Bid class to perform the majority of bid retraction functions within ILance.
*
* @package      iLance\Bid\Retract
* @version      4.0.0.8059
* @author       ILance
*/
class bid_retract extends bid
{
        /**
        * Function for retracting all bids for a user due to an admin user "deleting" a member from the AdminCP.
        *
        * @param       integer      user id
        */
        function retract_all_bids($userid = 0)
        {
                global $ilance, $ilconfig, $ilpage;
        }
	
	/**
        * Function for determining how many times bids were retracted from a specific listing.
        *
        * @param       integer      listing id
        */
        function fetch_retracts_count($pid = 0)
        {
                global $ilance, $ilconfig, $ilpage;
		$sql = $ilance->db->query("
			SELECT COUNT(retractid) AS count
			FROM " . DB_PREFIX . "project_bid_retracts
			WHERE project_id = '" . intval($pid) . "'
		");
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
		return $res['count'];
        }
        
        /**
        * Function for creating a new bid retract on a particular auction event.  In the case where a bidder has placed more than a single bid, all bid(s) for that user for the particular auction will be retracted.
        *
        * @param       integer      user id
        * @param       integer      bid id
        * @param       integer      project id
        * @param       string       reason
        * @param       boolean      is bid awarded? (default false)
        * @param       boolean      run in silent mode (no template notice) (default false)
        */
        function construct_product_bid_retraction($userid = 0, $bidid = 0, $projectid = 0, $reason = '', $awarded = false, $silentmode = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $navcrumb;
                $totalretracts = $ilance->permissions->check_access($userid, 'bidretracts');
                $project_state = fetch_auction('project_state', intval($projectid));
                $filter_escrow = fetch_auction('filter_escrow', intval($projectid));
                $canretract = ($project_state == 'product') ? $ilconfig['productbid_bidretract'] : $ilconfig['servicebid_bidretract'];
                $canretractaward = ($project_state == 'product') ? $ilconfig['productbid_awardbidretract'] : $ilconfig['servicebid_awardbidretract'];
		$retractbidamount = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidid) . "'", 'bidamount');
		$sql = $ilance->db->query("
			SELECT user_id
			FROM " . DB_PREFIX . "project_realtimebids
			WHERE project_id = '" . intval($projectid) . "'
			    AND bidstatus = 'placed'
			    AND bidstate != 'retracted'
			ORDER BY bidamount DESC, date_added ASC
			LIMIT 1
		", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res = $ilance->db->fetch_array($sql, DB_ASSOC);
			$highest_bidderid = $res['user_id'];
			$sql = $ilance->db->query("
				SELECT bidretracts
				FROM " . DB_PREFIX . "users
				WHERE user_id = '" . intval($userid) . "'
				LIMIT 1
			", 0, null, __FILE__, __LINE__);
			if ($ilance->db->num_rows($sql) > 0)
			{
				$res = $ilance->db->fetch_array($sql, DB_ASSOC);
				// #### can we retract our bid? ########################
				if ($totalretracts > 0 AND $res['bidretracts'] < $totalretracts)
				{
					($apihook = $ilance->api('construct_bid_retraction_good_permissions_start')) ? eval($apihook) : false;
					
					// #### auction won to bid already
					if ($awarded)
					{
						($apihook = $ilance->api('construct_bid_retraction_awarded_start')) ? eval($apihook) : false;
	
						if ($canretract AND $canretractaward)
						{
							($apihook = $ilance->api('construct_bid_retraction_awarded_can_retract_start')) ? eval($apihook) : false;
							
							// is escrow enabled and does owner use escrow?
							if ($ilconfig['escrowsystem_enabled'] AND $filter_escrow == '1')
							{
								// remove pending escrow account for the auction
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "projects_escrow
									WHERE project_id = '" . intval($projectid) . "'
									    AND user_id = '" . intval($userid) . "'
									    AND status = 'pending'
								", 0, null, __FILE__, __LINE__);
								// remove related unpaid escrow transaction invoice
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "invoices
									WHERE projectid = '" . intval($projectid) . "'
									    AND invoicetype = 'escrow'
									    AND status = 'unpaid'
								", 0, null, __FILE__, __LINE__);
							}
							// determine if a bid retract for this user for this listing already exists
							$sqlcheck = $ilance->db->query("
								SELECT retractid
								FROM " . DB_PREFIX . "project_bid_retracts
								WHERE user_id = '" . intval($userid) . "'
									AND project_id = '" . intval($projectid) . "'
									AND bid_id = '" . intval($bidid) . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sqlcheck) == 0)
							{
								($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_start')) ? eval($apihook) : false;
								
								// #### insert new bid retraction
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "project_bid_retracts
									(retractid, user_id, bid_id, project_id, reason, date)
									VALUES(
									NULL,
									'" . intval($userid) . "',
									'" . intval($bidid) . "',
									'" . intval($projectid) . "',
									'" . $ilance->db->escape_string($reason) . "',
									'" . DATETODAY . "')
								", 0, null, __FILE__, __LINE__);
								// #### retract the bid
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_bids
									SET bidstate = 'retracted'
									WHERE project_id = '" . intval($projectid) . "'
									    AND user_id = '" . intval($userid) . "'
									    AND bid_id = '" . intval($bidid) . "'
								", 0, null, __FILE__, __LINE__);
								// #### retract realtime bids for flash and other applets to act properly
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_realtimebids
									SET bidstate = 'retracted'
									WHERE project_id = '" . intval($projectid) . "'
									     AND user_id = '" . intval($userid) . "'
									     AND bid_id = '" . intval($bidid) . "'
								", 0, null, __FILE__, __LINE__);
								// #### delete any proxy bids placed
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "proxybid
									WHERE project_id = '" . intval($projectid) . "'
									     AND user_id = '" . intval($userid) . "'
								", 0, null, __FILE__, __LINE__);
								// #### update total retract count for bidder
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET bidretracts = bidretracts + 1
									WHERE user_id = '" . intval($userid) . "'
								", 0, null, __FILE__, __LINE__);
								// winning bidder retracting bid no 2nd highest bidder logic available
								$newcurrentprice = '0.00';
								// #### update listing details
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET bids = bids - 1,
									bidsretracted = bidsretracted + 1,
									haswinner = '0',
									winner_user_id = '0',
									buyerfeedback = '0',
									sellerfeedback = '0',
									winnermarkedaspaid = '0',
									winnermarkedaspaiddate = '0000-00-00 00:00:00',
									winnermarkedaspaidmethod = '',
									currentprice = '" . $ilance->db->escape_string($newcurrentprice) . "'                                                                
									WHERE project_id = '" . intval($projectid) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								$existing = array(
									'{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
									'{{username}}' => fetch_user('username', intval($userid)),					  
									'{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
									'{{reason}}' => $reason,
								);
								
								($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_end')) ? eval($apihook) : false;
								
								// #### email auction owner
								$ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
								$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
								$ilance->email->get('bids_retraction');		
								$ilance->email->set($existing);
								$ilance->email->send();
								// email administrator
								$ilance->email->mail = SITE_EMAIL;
								$ilance->email->slng = fetch_site_slng();
								$ilance->email->get('bids_retraction_admin');		
								$ilance->email->set($existing);
								$ilance->email->send();
								$sql = $ilance->db->query("
									SELECT user_id, bidamount
									FROM " . DB_PREFIX . "project_realtimebids
									WHERE project_id = '" . intval($projectid) . "'
									    AND bidstatus = 'placed'
									    AND bidstate != 'retracted'
									ORDER BY bidamount DESC
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$res = $ilance->db->fetch_array($sql, DB_ASSOC);
									if ($res['user_id'] != $highest_bidderid)
									{
										$sql2 = $ilance->db->query("
											SELECT project_title, date_end, bids, currentprice
											FROM " . DB_PREFIX . "projects
											WHERE project_id = '" . intval($projectid) . "'
										", 0, null, __FILE__, __LINE__);
										$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
										$bids = intval($res2['bids']) - 1;
										$ilance->email->mail = fetch_user('email', $res['user_id']);
										$ilance->email->slng = fetch_user_slng($res['user_id']);
										$ilance->email->get('bids_retraction_other_user');		
										$ilance->email->set(array(
											'{{projectid}}' => $projectid,
											'{{project_title}}' => stripslashes($res2['project_title']),
											'{{firstname}}' => fetch_user('first_name', intval($res['user_id'])),	
											'{{url}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid),
											'{{bidcount}}' => $bids,
											'{{date_end}}' => print_date($res2['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0),
											'{{old_bidamount}}' => $res2['currentprice'],
											'{{new_bidamount}}' => $res['bidamount'],
										));
										$ilance->email->send();
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "projects
											SET currentprice = '" . $res['bidamount'] . "'
											WHERE project_id = '" . intval($projectid) . "'
										", 0, null, __FILE__, __LINE__);
									}
								}
								else
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET currentprice = startprice
										WHERE project_id = '" . intval($projectid) . "'
									", 0, null, __FILE__, __LINE__);
								}
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET bids = bids - 1,
									    bidsretracted = bidsretracted + 1
									WHERE project_id = '" . intval($projectid) . "'
								", 0, null, __FILE__, __LINE__);
								if ($silentmode)
								{
									return true;
								}
								else 
								{
									$area_title = '{_bid_retracted}';
									$page_title = SITE_NAME . ' - {_bid_retracted}';
									print_notice('{_bid_retracted_from_awarded_bid}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts}', $totalretracts), $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
									exit();
								}
							}
							else 
							{
								if ($silentmode)
								{
									return true;
								}
								else 
								{
									$area_title = '{_bid_retracted}';
									$page_title = SITE_NAME . ' - {_bid_retracted}';
									print_notice('{_bid_retracted}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts), $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
									exit();
								}
							}
						}
						else
						{
							if ($silentmode)
							{
								return true;      
							}
							else 
							{
								($apihook = $ilance->api('construct_bid_retraction_awarded_cannot_retract_start')) ? eval($apihook) : false;
								
								$area_title = '{_cannot_retract_bid}';
								$page_title = SITE_NAME . ' - {_cannot_retract_bid}';
								print_notice('{_cannot_retract_bid}', $ilance->language->construct_phrase('{_you_cannot_retract_this_bid_because_it_was_determined_as_the_winning_awarded_bid}', $totalretracts), $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
								exit(); 
							}
						}
					}
					// #### auction not won to bid yet
					else
					{
						($apihook = $ilance->api('construct_bid_retraction_start')) ? eval($apihook) : false;
						
						// #### can retract bids ###############
						if ($canretract)
						{
							($apihook = $ilance->api('construct_bid_retraction_can_retract_start')) ? eval($apihook) : false;
							
							// determine if a bid retract for this user for this listing already exists
							$sqlcheck = $ilance->db->query("
								SELECT retractid
								FROM " . DB_PREFIX . "project_bid_retracts
								WHERE user_id = '" . intval($userid) . "'
									AND project_id = '" . intval($projectid) . "'
									AND bid_id = '" . intval($bidid) . "'
								LIMIT 1
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sqlcheck) == 0)
							{
								($apihook = $ilance->api('construct_bid_retraction_do_retraction_start')) ? eval($apihook) : false;
								
								// #### insert new retract
								$ilance->db->query("
									INSERT INTO " . DB_PREFIX . "project_bid_retracts
									(retractid, user_id, bid_id, project_id, bidamount, reason, date)
									VALUES(
									NULL,
									'" . intval($userid) . "',
									'" . intval($bidid) . "',
									'" . intval($projectid) . "',
									'" . $ilance->db->escape_string($retractbidamount) . "',
									'" . $ilance->db->escape_string($reason) . "',
									'" . DATETIME24H . "')
								", 0, null, __FILE__, __LINE__);
								// #### retract this bid placed by bidder
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_bids
									SET bidstate = 'retracted',
									date_retracted = '" . DATETIME24H . "'
									WHERE project_id = '" . intval($projectid) . "'
									     AND user_id = '" . intval($userid) . "'
									     AND bid_id = '" . intval($bidid) . "'
								", 0, null, __FILE__, __LINE__);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_realtimebids
									SET bidstate = 'retracted',
									date_retracted = '" . DATETIME24H . "'
									WHERE project_id = '" . intval($projectid) . "'
									     AND user_id = '" . intval($userid) . "'
									     AND bid_id = '" . intval($bidid) . "'
								", 0, null, __FILE__, __LINE__);
								// #### delete any proxy bids placed by the bidder
								$ilance->db->query("
									DELETE FROM " . DB_PREFIX . "proxybid
									WHERE project_id = '" . intval($projectid) . "'
									     AND user_id = '" . intval($userid) . "'
								", 0, null, __FILE__, __LINE__);
								// #### update retract count for member
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "users
									SET bidretracts = bidretracts + 1
									WHERE user_id = '" . intval($userid) . "'
								", 0, null, __FILE__, __LINE__);
								$existing = array(
									'{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
									'{{username}}' => fetch_user('username', intval($userid)),					  
									'{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
									'{{reason}}' => $reason,
								);
								
								($apihook = $ilance->api('construct_bid_retraction_do_retraction_end')) ? eval($apihook) : false;
								
								// #### email auction owner
								$ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
								$ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
								$ilance->email->get('bids_retraction');		
								$ilance->email->set($existing);
								$ilance->email->send();
								// #### email administrator
								$ilance->email->mail = SITE_EMAIL;
								$ilance->email->slng = fetch_site_slng();
								$ilance->email->get('bids_retraction_admin');		
								$ilance->email->set($existing);
								$ilance->email->send();
								$sql = $ilance->db->query("
									SELECT user_id, bidamount
									FROM " . DB_PREFIX . "project_realtimebids
									WHERE project_id = '" . intval($projectid) . "'
									    AND bidstatus = 'placed'
									    AND bidstate != 'retracted'
									ORDER BY bidamount DESC, date_added ASC
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql) > 0)
								{
									$res = $ilance->db->fetch_array($sql, DB_ASSOC);
									if ($res['user_id'] != $highest_bidderid)
									{
										$sql2 = $ilance->db->query("
											SELECT project_title, date_end, bids, currentprice
											FROM " . DB_PREFIX . "projects
											WHERE project_id = '" . intval($projectid) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										$res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
										$bids = intval($res2['bids']) - 1;
										$ilance->email->mail = fetch_user('email', $res['user_id']);
										$ilance->email->slng = fetch_user_slng($res['user_id']);
										$ilance->email->get('bids_retraction_other_user');		
										$ilance->email->set(array(
											'{{projectid}}' => $projectid,
											'{{project_title}}' => stripslashes($res2['project_title']),
											'{{firstname}}' => fetch_user('first_name', intval($res['user_id'])),	
											'{{url}}' => HTTP_SERVER . $ilpage['merch'] . '?id=' . intval($projectid),
											'{{bidcount}}' => $bids,
											'{{date_end}}' => print_date($res2['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], true, 0),
											'{{old_bidamount}}' => $res2['currentprice'],
											'{{new_bidamount}}' => $res['bidamount'],
										));
										$ilance->email->send();
									}
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET currentprice = '" . $res['bidamount'] . "'
										WHERE project_id = '" . intval($projectid) . "'
									", 0, null, __FILE__, __LINE__);
								}
								else
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "projects
										SET currentprice = startprice
										WHERE project_id = '" . intval($projectid) . "'
									", 0, null, __FILE__, __LINE__);
								}
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "projects
									SET bids = bids - 1,
									    bidsretracted = bidsretracted + 1
									WHERE project_id = '" . intval($projectid) . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($silentmode)
								{
									return true;
								}
								else 
								{
									$area_title = '{_bid_retracted}';
									$page_title = SITE_NAME . ' - {_bid_retracted}';
									print_notice('{_bid_retracted}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts),  $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
									exit();
								}
							}
							else 
							{
								if ($silentmode)
								{
									return true;
								}
								else 
								{
									$area_title = '{_bid_retracted}';
									$page_title = SITE_NAME . ' - {_bid_retracted}';
									print_notice('{_bid_retracted}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts), $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
									exit();
								}
							}
						}
						// #### cannot retract bids ############
						else
						{
							($apihook = $ilance->api('construct_bid_retraction_cannot_retract_start')) ? eval($apihook) : false;
							
							if ($silentmode)
							{
								return true;
							}
							else 
							{
								$area_title = '{_cannot_retract_bid}';
								$page_title = SITE_NAME . ' - {_cannot_retract_bid}';
								print_notice('{_cannot_retract_bid}', '{_you_cannot_retract_your_bid_at_this_time_this_action_is_currently_unavailable}', $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
								exit();
							}
						}
					}
				}
				// #### no retractions left or subscription level does not permit bid retractions
				else
				{
					($apihook = $ilance->api('construct_bid_retraction_bad_permissions_start')) ? eval($apihook) : false;
					
					if ($silentmode)
					{
						return true;
					}
					else 
					{
						$area_title = '{_cannot_retract_bid}';
						$page_title = SITE_NAME . ' - {_cannot_retract_bid}';
						print_notice('{_maximum_bid_retracts_used_this_month}', $ilance->language->construct_phrase('{_sorry_you_have_used_the_total_number_of_bid_retractions_for_your_subscription}', $totalretracts), $ilance->GPC['returnurl'], '{_return_to_the_previous_menu}');
						exit();
					}
				}
			}
			
		}
		return false;
        }
        
        /**
        * Function for creating a new bid retract on a particular auction event.  In the case where a bidder has placed more than a single bid,
        * all bid(s) for that user for the particular auction will be retracted.
        *
        * @param       integer      user id
        * @param       integer      bid id
        * @param       integer      listing id
        * @param       string       reason
        * @param       boolean      is bid awarded? (default false)
        * @param       boolean      run in silent mode (no template notice) (default false)
        * @param       boolean      are bids being groupped (default false)
        */
        function construct_service_bid_retraction($userid = 0, $id = 0, $projectid = 0, $reason = '', $awarded = false, $silentmode = false, $bidgrouping = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $navcrumb;
                // #### setup our defaults for the user ########################
                $totalretracts = $ilance->permissions->check_access($userid, 'bidretracts');
                $project_state = fetch_auction('project_state', intval($projectid));
                $filter_escrow = fetch_auction('filter_escrow', intval($projectid));
                $canretract = ($project_state == 'product') ? $ilconfig['productbid_bidretract'] : $ilconfig['servicebid_bidretract'];
                $canretractaward = ($project_state == 'product') ? $ilconfig['productbid_awardbidretract'] : $ilconfig['servicebid_awardbidretract'];
                $winning_bid = 0;
                if ($bidgrouping == false)
                {
	                $sql_rb = $ilance->db->query("
                                SELECT * 
                                FROM " . DB_PREFIX . "project_realtimebids 
                                WHERE id = '" . $id . "'
	                ", 0, null, __FILE__, __LINE__);
	                $res_rb = $ilance->db->fetch_array($sql_rb, DB_ASSOC);
	                $sql_b = $ilance->db->query("
	                	SELECT *
                                FROM " . DB_PREFIX . "project_bids 
                                WHERE bid_id = '" . $res_rb['bid_id'] . "' 
                                        AND project_id = '" . $res_rb['project_id'] . "'
                                        AND project_user_id = '" . $res_rb['project_user_id'] . "'
                                        AND user_id = '" . $res_rb['user_id'] . "'
                                        AND date_added = '" . $res_rb['date_added'] . "'
                                        AND date_awarded = '" . $res_rb['date_awarded'] . "'
                                        AND date_updated = '" . $res_rb['date_updated'] . "'
                        ", 0, null, __FILE__, __LINE__);
	                if ($ilance->db->num_rows($sql_b) > 0)
	                {
	                	$winning_bid = 1;
	                	$res_b = $ilance->db->fetch_array($sql_b, DB_ASSOC);
	                }
                }
                else 
                {
                	$sql_b = $ilance->db->query("
                                SELECT * 
                                FROM " . DB_PREFIX . "project_bids 
                                WHERE bid_id='".$id."'
	                ", 0, null, __FILE__, __LINE__);
	                $res_b = $ilance->db->fetch_array($sql_b, DB_ASSOC);
	                $sql_rb = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "project_realtimebids 
                                WHERE bid_id = '" . $res_b['bid_id'] . "' 
                                        AND project_id = '" . $res_b['project_id'] . "'
                                        AND project_user_id = '" . $res_b['project_user_id'] . "'
                                        AND user_id = '" . $res_b['user_id'] . "'
                                        AND date_added = '" . $res_b['date_added'] . "'
                                        AND date_awarded = '" . $res_b['date_awarded'] . "'
                                        AND date_updated = '" . $res_b['date_updated'] . "'
			", 0, null, __FILE__, __LINE__);
	                $res_rb = $ilance->db->fetch_array($sql_rb, DB_ASSOC);
	                $id = $res_rb['id'];
                }
                $sql = $ilance->db->query("
                        SELECT bidretracts
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        if ($totalretracts > 0 AND $res['bidretracts'] < $totalretracts)
                        {
                                ($apihook = $ilance->api('construct_bid_retraction_good_permissions_start')) ? eval($apihook) : false;
                                
                                // #### awarded already ########################
                                if ($awarded)
                                {
                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_start')) ? eval($apihook) : false;

                                        if ($canretract AND $canretractaward)
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_awarded_can_retract_start')) ? eval($apihook) : false;
                                                
                                                if ($winning_bid)
                                                {
                                                        // is escrow enabled and does owner use escrow?
                                                        if ($ilconfig['escrowsystem_enabled'] AND $filter_escrow == '1')
                                                        {
                                                                // remove pending escrow account for the auction
                                                                $ilance->db->query("
                                                                        DELETE FROM " . DB_PREFIX . "projects_escrow
                                                                        WHERE project_id = '" . intval($projectid) . "'
                                                                            AND user_id = '" . intval($userid) . "'
                                                                            AND status = 'pending'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // remove related unpaid escrow transaction invoice
                                                                $ilance->db->query("
                                                                        DELETE FROM " . DB_PREFIX . "invoices
                                                                        WHERE projectid = '" . intval($projectid) . "'
                                                                            AND invoicetype = 'escrow'
                                                                            AND status = 'unpaid'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                                // determine if a bid retract for this user for this listing already exists
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT retractid
                                                        FROM " . DB_PREFIX . "project_bid_retracts
                                                        WHERE user_id = '" . intval($userid) . "'
                                                                AND project_id = '" . intval($projectid) . "'
                                                                AND bid_id = '" . intval($id) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) == 0)
                                                {
                                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_start')) ? eval($apihook) : false;
                                                        
                                                        // #### insert new bid retraction
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "project_bid_retracts
                                                                (retractid, user_id, bid_id, project_id, reason, date)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($userid) . "',
                                                                '" . intval($id) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . $ilance->db->escape_string($reason) . "',
                                                                '" . DATETODAY . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                        		if ($winning_bid OR $bidgrouping)
                                        		{
	                                                        // #### retract the bid
	                                                        $ilance->db->query("
			                                                UPDATE " . DB_PREFIX . "project_bids
		                                                        SET bidstate = 'retracted'
		                                                        WHERE project_id = '" . intval($projectid) . "'
		                                                            	AND user_id = '" . intval($userid) . "'
		                                                                AND bid_id = '" . intval($res_b['bid_id']) . "'
		                                                ", 0, null, __FILE__, __LINE__);
                                        		}
                                                        // #### retract realtime bids for flash and other applets to act properly
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_realtimebids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                                     AND id = '" . intval($id) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // #### delete any proxy bids placed
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "proxybid
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // #### update total retract count for bidder
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET bidretracts = bidretracts + 1
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // winning bidder retracting bid no 2nd highest bidder logic available
                                                        $newcurrentprice = '0.00';
                                                        $sql_second = $ilance->db->query("
                                                                SELECT bidamount
                                                                FROM " . DB_PREFIX . "project_realtimebids
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                        AND bidstate != 'retracted'
                                                                ORDER BY bidamount DESC, date_added ASC
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sql_second) > 0)
                                                        {
                                                                $res_second = $ilance->db->fetch_array($sql_second, DB_ASSOC);
                                                                $newcurrentprice = $res_second['bidamount'];
                                                        }
                                                        // #### update listing details
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET bids = bids - 1,
                                                                bidsretracted = bidsretracted + 1,
                                                                haswinner = '0',
                                                                winner_user_id = '0',
                                                                buyerfeedback = '0',
                                                                sellerfeedback = '0',
                                                                currentprice = '" . $ilance->db->escape_string($newcurrentprice) . "'                                                                
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $existing = array(
                                                                '{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
                                                                '{{username}}' => fetch_user('username', intval($userid)),					  
                                                                '{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
                                                                '{{reason}}' => $reason,
                                                        );
                                                        
                                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_do_retraction_end')) ? eval($apihook) : false;
                                                        
                                                        // #### email auction owner
                                                        $ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
                                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                        $ilance->email->get('bids_retraction');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        // email administrator
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        $ilance->email->get('bids_retraction_admin');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        if ($silentmode == false)
                                                        {
                                                                print_notice('{_bid_retracted_from_awarded_bid}', $ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts}', $totalretracts), "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
                                                                exit();
                                                        }
                                                        else 
                                                        {
                                                        	print_action_success($ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction_please_remember_your_current_subscription_level_provides_x_bid_retracts}', $totalretracts), $ilance->GPC['return']);
                                                                exit();
                                                        }
                                                }
                                                else 
                                                {
                                                	if ($silentmode == true)
                                                        {
                                                        	print_action_success($ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts), $ilance->GPC['return']);
								exit();
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                if ($silentmode == false)
                                                {
                                                        ($apihook = $ilance->api('construct_bid_retraction_awarded_cannot_retract_start')) ? eval($apihook) : false;
                                                        
                                                        $area_title = '{_cannot_retract_bid}';
                                                        $page_title = SITE_NAME . ' - ' . '{_cannot_retract_bid}';
                                                        print_notice('{_cannot_retract_bid}', $ilance->language->construct_phrase('{_you_cannot_retract_this_bid_because_it_was_determined_as_the_winning_awarded_bid}', $totalretracts), "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
                                                        exit();       
                                                }
                                                else 
                                                {
                                                        print_action_failed($ilance->language->construct_phrase('{_you_cannot_retract_this_bid_because_it_was_determined_as_the_winning_awarded_bid}', $totalretracts), $ilance->GPC['return']);
                                                        exit();
                                                }
                                        }
                                }
                                
                                // #### not awarded ############################
                                else
                                {
                                        ($apihook = $ilance->api('construct_bid_retraction_start')) ? eval($apihook) : false;
                                        
                                        // #### can retract bids ###############
                                        if ($canretract)
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_can_retract_start')) ? eval($apihook) : false;
                                                
                                                // determine if a bid retract for this user for this listing already exists
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT retractid
                                                        FROM " . DB_PREFIX . "project_bid_retracts
                                                        WHERE user_id = '" . intval($userid) . "'
                                                                AND project_id = '" . intval($projectid) . "'
                                                                AND bid_id = '" . intval($id) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) == 0)
                                                {
                                                        ($apihook = $ilance->api('construct_bid_retraction_do_retraction_start')) ? eval($apihook) : false;
                                                        
                                                        // #### insert new retract
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "project_bid_retracts
                                                                (retractid, user_id, bid_id, project_id, reason, date)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($userid) . "',
                                                                '" . intval($id) . "',
                                                                '" . intval($projectid) . "',
                                                                '" . $ilance->db->escape_string($reason) . "',
                                                                '" . DATETODAY . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($winning_bid OR $bidgrouping)
                                                        {
                                                                // #### retract all bids placed by bidder
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "project_bids
                                                                        SET bidstate = 'retracted'
                                                                        WHERE project_id = '" . intval($projectid) . "'
                                                                             AND user_id = '" . intval($userid) . "'
                                                                             AND bid_id = '" . intval($res_b['bid_id']) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "project_realtimebids
                                                                SET bidstate = 'retracted'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                                     AND id = '" . intval($id) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // #### delete any proxy bids placed by the bidder
                                                        $ilance->db->query("
                                                                DELETE FROM " . DB_PREFIX . "proxybid
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                     AND user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // #### update retract count for member
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET bidretracts = bidretracts + 1
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $newcurrentprice = '0.00';
                                                        $sql_second = $ilance->db->query("
                                                                SELECT bidamount
                                                                FROM " . DB_PREFIX . "project_realtimebids
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                                        AND bidstate != 'retracted'
                                                                ORDER BY bidamount DESC, date_added ASC
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sql_second))
                                                        {
                                                                $res_second = $ilance->db->fetch_array($sql_second, DB_ASSOC);
                                                                $newcurrentprice = $res_second['bidamount'];
                                                        }
                                                        // #### update listing bids quantity
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET bids = bids - 1,
                                                                bidsretracted = bidsretracted + 1,
                                                                currentprice = '" . $ilance->db->escape_string($newcurrentprice) . "'
                                                                WHERE project_id = '" . intval($projectid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $existing = array(
                                                                '{{buyer}}' => fetch_user('username', fetch_auction('user_id', intval($projectid))),
                                                                '{{username}}' => fetch_user('username', intval($userid)),					  
                                                                '{{project_title}}' => stripslashes(fetch_auction('project_title', intval($projectid))),
                                                                '{{reason}}' => $reason,
                                                        );
                                                        
                                                        ($apihook = $ilance->api('construct_bid_retraction_do_retraction_end')) ? eval($apihook) : false;
                                                        
                                                        // #### email auction owner
                                                        $ilance->email->mail = fetch_user('email', fetch_auction('user_id', intval($projectid)));
                                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                                        $ilance->email->get('bids_retraction');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        // #### email administrator
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        $ilance->email->get('bids_retraction_admin');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        if ($silentmode == true)
                                                        {
                                                        	print_action_success($ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts), $ilance->GPC['return']);
								exit();
                                                        }
                                                }
                                                else 
                                                {
                                                	if ($silentmode == true)
                                                        {
                                                        	print_action_success($ilance->language->construct_phrase('{_you_have_retracted_your_bid_from_this_auction}', $totalretracts), $ilance->GPC['return']);
								exit();
                                                        }
                                                }
                                        }
                                        
                                        // #### cannot retract bids ############
                                        else
                                        {
                                                ($apihook = $ilance->api('construct_bid_retraction_cannot_retract_start')) ? eval($apihook) : false;
                                                
                                                if ($silentmode == false)
                                                {
                                                        $area_title = '{_cannot_retract_bid}';
                                                        $page_title = SITE_NAME . ' - ' . '{_cannot_retract_bid}';
                                                        $navcrumb = array();
                                                        $navcrumb["$ilpage[selling]?cmd=management"] = '{_selling_activity}';
                                                        $navcrumb[""] = '{_cannot_retract_bid}';
                                                        print_notice('{_cannot_retract_bid}', '{_you_cannot_retract_your_bid_at_this_time_this_action_is_currently_unavailable}', "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
                                                        exit();
                                                }
                                                else 
                                                {
	                                                print_action_failed('{_you_cannot_retract_your_bid_at_this_time_this_action_is_currently_unavailable}', $ilance->GPC['return']);
							exit();
                                                }
                                        }
                                }
                        }
                        // #### no retractions left or subscription level does not permit bid retractions
                        else
                        {
                                ($apihook = $ilance->api('construct_bid_retraction_bad_permissions_start')) ? eval($apihook) : false;
                                
                                if ($silentmode == false)
                                {
                                        $area_title = '{_cannot_retract_bid}';
                                        $page_title = SITE_NAME . ' - ' . '{_cannot_retract_bid}';
                                        print_notice('{_maximum_bid_retracts_used_this_month}', $ilance->language->construct_phrase('{_sorry_you_have_used_the_total_number_of_bid_retractions_for_your_subscription}', $totalretracts), "javascript: history.go(-1)", '{_return_to_the_previous_menu}');
                                        exit();
                                }
                                else 
                                {
	                                print_action_failed($ilance->language->construct_phrase('{_sorry_you_have_used_the_total_number_of_bid_retractions_for_your_subscription}', $totalretracts), $ilance->GPC['return']);
					exit();
                                }
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