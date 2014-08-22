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
* Auction award class to perform the majority of functions dealing with anything to do with auctions and awarding details within ILance.
*
* @package      iLance\Auction\Award
* @version      4.0.0.8059
* @author       ILance
*/
class auction_award extends auction
{
        /*
        * Function to award a service auction to a particular service provider based on the buyer's selected bid and project id's.
        * This function does not set `haswinner` in the projects table to 1 as it will trigger when the provider accepts the buyers award
        * based on the service_auction_award_accept() function.
        * 
        *
        * @param       integer      auction id
        * @param       integer      bid id
        * @param       integer      buyer id
        * @param       integer      service provider id
        * @param       bool         defines if we should notify bidders via email regarding awarded project
        * @param       string       short form language identifier (default is eng)
        *
        * @return      nothing
        */
        function award_service_auction($projectid = 0, $bidid = 0, $ownerid = 0, $vendorid = 0, $notifybidders = 0, $slng = 'eng')
        {
                global $ilance, $show, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $usingescrow = false;
                
                $cid = fetch_auction('cid', $projectid);
                $bidgrouping = $ilance->categories->bidgrouping($cid);
                if ($bidgrouping)
                {
                	$field = 'bid_id';
			$table = 'project_bids';
                }
                else 
                {
                	$field = 'id';
			$table = 'project_realtimebids';
                }
                
                $sel = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . $table . "
                        WHERE " . $field . " = '" . intval($bidid) . "'
				AND project_id = '" . intval($projectid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sel) > 0)
                {
                        $result = $ilance->db->fetch_array($sel, DB_ASSOC);
                        $winner_user_id = $result['user_id'];
                        $winner_bid_message = stripslashes($result['proposal']);
                        $winner_bid_date_added = print_date($result['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        $winner_bid_price = $result['bidamount'];
                        $winner_bid_estimate_days = $result['estimate_days'];
                        $winner_bidamounttype = $result['bidamounttype'];
                        $winner_bid_measure = $this->construct_measure($winner_bidamounttype);
                        $winner_bid_amount = ($winner_bidamounttype == 'entire' OR $winner_bidamounttype == 'lot' OR $winner_bidamounttype == 'weight') ? $result['bidamount'] : ($result['bidamount'] * $winner_bid_estimate_days);
                }
                
                // service provider details
                $sql_winner = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($winner_user_id) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_winner) > 0)
                {
                        $result_winner = $ilance->db->fetch_array($sql_winner, DB_ASSOC);
                        $winner_user_email = $result_winner['email'];
                        $winner_user_username = stripslashes($result_winner['username']);
                        $winner_user_first_name = $result_winner['first_name'];
                        $winner_user_last_name = $result_winner['last_name'];
                        $winner_user_address = stripslashes($result_winner['address']);
                        $winner_user_address2 = stripslashes($result_winner['address2']);
                        $winner_user_city = $result_winner['city'];
                        $winner_user_state = $result_winner['state'];
                        $winner_user_zip_code = $result_winner['zip_code'];
                        $winner_user_phone = $result_winner['phone'];
                        $winner_user_country = $ilance->common_location->print_user_country($winner_user_id, $slng);
                }
                
                // auction information
                $sql_project = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "projects
                        WHERE project_id = '" . intval($projectid) . "'
                            AND user_id = '" . intval($ownerid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_project) > 0)
                {
                        $result_project = $ilance->db->fetch_array($sql_project, DB_ASSOC);
                        $currencyid = $result_project['currencyid'];
                        $project_title = stripslashes($result_project['project_title']);
                        $project_description = stripslashes($result_project['description']);
                        $project_date_added = print_date($result_project['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        $project_date_end = print_date($result_project['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                        $project_bids = $result_project['bids'];
                        $project_budget = $this->construct_budget_overview($result_project['cid'], $result_project['filtered_budgetid']);
                        $project_user_id = $result_project['user_id'];
                        $usingescrow = ($result_project['filter_escrow'] == '1' AND $result['winnermarkedaspaidmethod'] == 'escrow') ? true : false;
                }
                
                // auction owner information
                $sql_owner = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "users
                        WHERE user_id = '" . intval($project_user_id) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql_owner) > 0)
                {
                        $result_owner = $ilance->db->fetch_array($sql_owner, DB_ASSOC);
                        $project_user_email = $result_owner['email'];
                        $project_user_username = stripslashes($result_owner['username']);
                        $project_user_first_name = stripslashes($result_owner['first_name']);
                        $project_user_last_name = stripslashes($result_owner['last_name']);
                        $project_user_address = stripslashes($result_owner['address']);
                        $project_user_address2 = stripslashes($result_owner['address2']);
                        $project_user_city = stripslashes($result_owner['city']);
                        $project_user_state = stripslashes($result_owner['state']);
                        $project_user_zip_code = mb_strtoupper($result_owner['zip_code']);
                        $project_user_phone = $result_owner['phone'];
                        $project_user_country = $ilance->common_location->print_user_country($project_user_id, $slng);
                }
                
		if ($bidgrouping)
		{
			$sql_rtbid = $ilance->db->query("
		                SELECT *
				FROM " . DB_PREFIX . "project_realtimebids 
				WHERE bid_id = '" . intval($bidid) . "' 
					AND project_id = '" . intval($projectid) . "'
					AND project_user_id = '" . intval($project_user_id) . "'
					AND user_id = '" . $result['user_id'] . "'
					AND date_added = '" . $result['date_added'] . "'
					AND date_awarded = '" . $result['date_awarded'] . "'
					AND date_updated = '" . $result['date_updated'] . "'
	            	", 0, null, __FILE__, __LINE__);
				
			$res_rb = $ilance->db->fetch_array($sql_rtbid, DB_ASSOC);
			$realtimebidid = ($ilance->db->num_rows($sql_rtbid) > 0) ? $res_rb['id'] : '0';
	        }
                
                // escrow system enabled? (and does buyer make use of escrow)?
                $escrow_id = 0;
                if ($ilconfig['escrowsystem_enabled'] AND $usingescrow AND isset($winner_bid_amount) AND $winner_bid_amount > 0)
                {
                        // #### SERVICE BUYER ESCROW FEES ######################
                        
                        // we'll populate the fee field which denotes any fees the buyer of this service auction
                        // must pay the site owner .. we'll also calculate any tax if applicable to ensure that the
                        // fee to the buyer will include the full fee amount + any applicable taxes (for commission txns)
                        $buyer_escrow_fee = $provider_escrow_fee = 0;
                        $escrowamount = $winner_bid_amount;
                        if ($ilconfig['escrowsystem_escrowcommissionfees'])
                        {
                                
                                // escrow commission fees to auction owner enabled
                                if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                                {
                                        // fixed escrow cost to buyer
                                        $buyer_escrow_fee = $ilconfig['escrowsystem_servicebuyerfixedprice'];
                                }
                                else
                                {
                                        if ($ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
                                        {
                                                // percentage rate of total winning bid amount
                                                // which would be the same as the amount being forwarded into escrow
                                                $buyer_escrow_fee = ($winner_bid_amount * $ilconfig['escrowsystem_servicebuyerpercentrate'] / 100);
                                        }
                                }
                                
				$buyer_escrow_fee_notax = $buyer_escrow_fee;
                                if ($buyer_escrow_fee > 0)
                                {
                                        $taxamount = 0;
                                        if ($ilance->tax->is_taxable($project_user_id, 'commission'))
                                        {
                                                // fetch tax amount to charge for this invoice type
                                                $taxamount = $ilance->tax->fetch_amount($project_user_id, $buyer_escrow_fee, 'commission', 0);
                                        }
                                        
                                        // amount to forward plus the buyer fee to fund escrow (including any taxes if applicable)
                                        //$escrowamount = ($winner_bid_amount + $buyer_escrow_fee + $taxamount);
                                        
                                        // exact amount to charge buyer
                                        $buyer_escrow_fee = ($buyer_escrow_fee + $taxamount);
                                }
                                
                                // #### SERVICE PROVIDER ESCROW FEES ###############################
                                // we'll populate the fee2 field with the amount that should be paid
                                // by the service provider to release funds from escrow to their online account balance
                                // additionally we'll check to see if the admin charges providers any tax on the release
                                // of funds from the buyers escrow account (fee to providers)
                                
                                // escrow commission fees to auction owner enabled
                                if ($ilconfig['escrowsystem_providerfixedprice'] > 0)
                                {
                                        // fixed escrow cost to provider for release of funds
                                        $provider_escrow_fee = $ilconfig['escrowsystem_providerfixedprice'];
                                }
                                else
                                {
                                        if ($ilconfig['escrowsystem_providerpercentrate'] > 0)
                                        {
                                                // percentage rate of total winning bid amount
                                                // which would be the same as the amount being forwarded into escrow
                                                $provider_escrow_fee = ($winner_bid_amount * $ilconfig['escrowsystem_providerpercentrate'] / 100);
                                        }
                                }
                                
				$provider_escrow_fee_notax = $provider_escrow_fee;
                                if ($provider_escrow_fee > 0)
                                {
                                        $taxamount = 0;
                                        if ($ilance->tax->is_taxable($vendorid, 'commission'))
                                        {
                                                // fetch tax amount to charge for this invoice type
                                                $taxamount = $ilance->tax->fetch_amount($vendorid, $provider_escrow_fee, 'commission', 0);
                                        }
                                        
                                        // exact amount to charge provider for release of funds
                                        $provider_escrow_fee = ($provider_escrow_fee + $taxamount);
                                }
                        }
                        
                        // #### generate unpaid escrow invoice to the project owner
                        // this invoice will be from "the site" to the "auction owner"
                        // this invoice will also include any fees to the buyer AND provider
                        
                        $escrow_invoice_id = $ilance->accounting->insert_transaction(
                                0,
                                intval($projectid),
                                0,
                                intval($project_user_id),
                                0,
                                0,
                                0,
                                '{_escrow_payment_forward}: ' . $project_title . ' #' . $projectid,
                                sprintf("%01.2f", $escrowamount),
                                '',
                                'unpaid',
                                'escrow',
                                'account',
                                DATETIME24H,
                                DATEINVOICEDUE,
                                '',
                                '',
                                0,
                                0,
                                1
                        );
                        
                        // create a new service escrow account between the service buyer and the service provider
                        // we will also log the fees we'll be charging for the buyer and provider of this auction
			// buyer and provider escrow fees recorded into escrow table will INCLUDE TAX.
                        if ($bidgrouping)
                	{
				$ilance->db->query("
	                                INSERT INTO " . DB_PREFIX . "projects_escrow
	                                (escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
	                                VALUES(
	                                NULL,
	                                '" . intval($realtimebidid) . "',
	                                '" . intval($projectid) . "',
	                                '" . intval($escrow_invoice_id) . "',
	                                '" . intval($project_user_id) . "',
	                                '" . intval($vendorid) . "',
	                                '" . DATETIME24H . "',
	                                '" . sprintf("%01.2f", $winner_bid_amount) . "',
	                                '" . sprintf("%01.2f", $winner_bid_amount) . "',
	                                '" . sprintf("%01.2f", $buyer_escrow_fee) . "',
	                                '" . sprintf("%01.2f", $provider_escrow_fee) . "',
	                                '0',
	                                '0',
	                                '0',
	                                '0',
	                                'pending')
	                        ", 0, null, __FILE__, __LINE__);
	                        $escrow_id = $ilance->db->insert_id();
                	}
                	else 
                	{
                		$ilance->db->query("
	                                INSERT INTO " . DB_PREFIX . "projects_escrow
	                                (escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, bidamount, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, fee2invoiceid, status)
	                                VALUES(
	                                NULL,
	                                '" . intval($bidid) . "',
	                                '" . intval($projectid) . "',
	                                '" . intval($escrow_invoice_id) . "',
	                                '" . intval($project_user_id) . "',
	                                '" . intval($vendorid) . "',
	                                '" . DATETIME24H . "',
	                                '" . sprintf("%01.2f", $winner_bid_amount) . "',
	                                '" . sprintf("%01.2f", $winner_bid_amount) . "',
	                                '" . sprintf("%01.2f", $buyer_escrow_fee) . "',
	                                '" . sprintf("%01.2f", $provider_escrow_fee) . "',
	                                '0',
	                                '0',
	                                '0',
	                                '0',
	                                'pending')
	                        ", 0, null, __FILE__, __LINE__);
	                        $escrow_id = $ilance->db->insert_id();
                	}
                }
                
                // change auction status from (open/expired) to wait_approval
                // this will make it so no new bids can be placed on buyers auction
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "projects
                        SET status = 'wait_approval',
                        escrow_id = '" . intval($escrow_id) . "',
                        close_date = '" . DATETIME24H . "'
                        WHERE project_id = '" . intval($projectid) . "'
                            AND user_id = '" . intval($project_user_id) . "'
                ", 0, null, __FILE__, __LINE__);
                
                // update awarded bid to wait_approval
                // this will give the provider one last chance to commit to the service
                if ($bidgrouping)
                {
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET bidstate = 'wait_approval',
				date_awarded = '" . DATETIME24H . "'
				WHERE bid_id = '" . intval($bidid) . "'
				    AND project_id = '" . intval($projectid) . "'
			", 0, null, __FILE__, __LINE__);
					
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_realtimebids
				SET bidstate = 'wait_approval',
				date_awarded = '" . DATETIME24H . "'
				WHERE id = '" . intval($realtimebidid) . "'
				    AND project_id = '" . intval($projectid) . "'
			", 0, null, __FILE__, __LINE__);
                }
                else 
                {
	                $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "project_realtimebids
	                        SET bidstate = 'wait_approval',
	                        date_awarded = '" . DATETIME24H . "'
	                        WHERE id = '" . intval($bidid) . "'
	                            AND project_id = '" . intval($projectid) . "'
	                ", 0, null, __FILE__, __LINE__);
                	
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET bidstate = 'wait_approval',
				date_awarded = '" . DATETIME24H . "'
				WHERE bid_id = '" . intval($result['bid_id']) . "'
					AND project_id = '" . intval($projectid) . "'
					AND date_added = '" . $result['date_added'] . "'
					AND date_awarded = '" . $result['date_awarded'] . "'
					AND date_updated = '" . $result['date_updated'] . "'
			", 0, null, __FILE__, __LINE__);
                }
                
                // update everybody elses bid status to reviewing
                // we put them in reviewing mode just in case something goes wrong
                // with award provided and we can send a email re-open project update
                // to all previous bidders and reset their bids back to "placed"
                if ($bidgrouping)
                {
	                $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "project_bids
	                        SET bidstate = 'reviewing'
	                        WHERE project_id = '" . intval($projectid) . "'
	                            AND bid_id != '" . intval($bidid) . "'
	                            AND bidstate != 'retracted'
	                            AND bidstatus != 'declined'
	                ", 0, null, __FILE__, __LINE__);
	                	
	                $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "project_realtimebids
	                        SET bidstate = 'reviewing'
	                        WHERE project_id = '" . intval($projectid) . "'
	                            AND id != '" . intval($realtimebidid) . "'
	                            AND bidstate != 'retracted'
	                            AND bidstatus != 'declined'
	                ", 0, null, __FILE__, __LINE__);
                }
                else 
                {
	                $ilance->db->query("
	                        UPDATE " . DB_PREFIX . "project_realtimebids
	                        SET bidstate = 'reviewing'
	                        WHERE project_id = '" . intval($projectid) . "'
	                            AND id != '" . intval($bidid) . "'
	                            AND bidstate != 'retracted'
	                            AND bidstatus != 'declined'
	                ", 0, null, __FILE__, __LINE__);
	                	
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "project_bids
				SET bidstate = 'reviewing'
				WHERE project_id = '" . intval($projectid) . "'
					AND bid_id != '" . intval($result['bid_id']) . "'
					AND bidstate != 'retracted'
					AND bidstatus != 'declined'
					AND date_added = '" . $result['date_added'] . "'
					AND date_awarded = '" . $result['date_awarded'] . "'
					AND date_updated = '" . $result['date_updated'] . "'
			", 0, null, __FILE__, __LINE__);
                }
                
                // decrease auction count in the category system so that the counters
                // do not see this auction since it's being awarded .. however if the
                // buyer unawards this provider the count will return to the category
                // system if there is any time left
                $ilance->categories->build_category_count($result_project['cid'], 'subtract', "award_service_auction(): subtracting increment count category id $result_project[cid]");
                
                // #### REFERRAL SYSTEM TRACKER ################################
                $ilance->referral->update_referral_action('awardauction', intval($project_user_id));							
        
                // #### START EMAIL ############################################
                $existing = array(
                        // #### winner details #################################
                        '{{winner_user_username}}' => $winner_user_username,
                        '{{winner_user_id}}' => $winner_user_id,
                        '{{winner_user_email}}' => $winner_user_email,
                        '{{winner_user_first_name}}' => $winner_user_first_name,
                        '{{winner_user_last_name}}' => $winner_user_last_name,
                        '{{winner_user_address}}' => $winner_user_address,
                        '{{winner_user_address2}}' => $winner_user_address2,
                        '{{winner_user_city}}' => $winner_user_city,
                        '{{winner_user_state}}' => $winner_user_state,
                        '{{winner_user_zip_code}}' => $winner_user_zip_code,
                        '{{winner_user_country}}' => $winner_user_country,
                        '{{winner_user_phone}}' => $winner_user_phone,
                        // #### owner details ##################################
                        '{{project_user_id}}' => $project_user_id,
                        '{{project_user_username}}' => $project_user_username,
                        '{{project_user_email}}' => $project_user_email,
                        '{{project_user_first_name}}' => $project_user_first_name,
                        '{{project_user_last_name}}' => $project_user_last_name,
                        '{{project_user_address}}' => $project_user_address,
                        '{{project_user_address2}}' => $project_user_address2,
                        '{{project_user_city}}' => $project_user_city,
                        '{{project_user_state}}' => $project_user_state,
                        '{{project_user_country}}' => $project_user_country,
                        '{{project_user_phone}}' => $project_user_phone,
                        '{{project_user_zip_code}}' => $project_user_zip_code,
                        // #### bid details ####################################
                        '{{bid_id}}' => intval($bidid),
                        '{{winner_bid_message}}' => $winner_bid_message,
                        '{{winner_bid_price}}' => $ilance->currency->format($winner_bid_price, $currencyid),
                        '{{winner_bid_amount}}' => $ilance->currency->format($winner_bid_amount, $currencyid),
                        '{{winner_bid_estimate_days}}' => $winner_bid_estimate_days,
                        '{{winner_bid_date_added}}' => $winner_bid_date_added,
                        '{{measure}}' => $winner_bid_measure,
                        // #### project details ################################
                        '{{p_id}}' => intval($projectid),
                        '{{project_title}}' => $project_title,
                        '{{project_description}}' => $project_description,
                        '{{project_date_added}}' => $project_date_added,
                        '{{project_date_end}}' => $project_date_end,
                        '{{project_bids}}' => $project_bids,
                        '{{project_budget}}' => $project_budget,
                );
                
                // #### email admin
                
                
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('buyer_awarded_bid_admin');		
                $ilance->email->set($existing);
                $ilance->email->send();
                
                // #### email winning bidder
                $ilance->email->mail = $winner_user_email;
                $ilance->email->slng = fetch_user_slng($winner_user_id);
                $ilance->email->get('buyer_awarded_bid_provider');		
                $ilance->email->set($existing);
                $ilance->email->send();
                
		($apihook = $ilance->api('service_auction_award')) ? eval($apihook) : false;
				
                // #### does the buyer want to notify all other vendors of this project award?
                if ($notifybidders)
                {
                	$sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table . "
                                WHERE " . $field . " != '" . intval($bidid) . "'
					AND user_id != '" . intval($vendorid) . "'
					AND bidstatus != 'awarded'
					AND bidstatus != 'declined'
					AND project_id = '" . intval($projectid) . "'
					AND bidstate != 'retracted'
                                GROUP BY user_id
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        // email other bidders
                                        $ilance->email->mail = fetch_user('email', $res['user_id']);
                                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                                        $ilance->email->get('bids_in_review_notify');		
                                        $ilance->email->set(array(
                                                '{{username}}' => fetch_user('username', $res['user_id']),
                                                '{{project_user_username}}' => $project_user_username,
                                                '{{project_title}}' => $project_title,
                                        ));
                                        $ilance->email->send();
                                }
                        }
                }
                
                // #### does buyer want to be taken to the escrow payment menu?
                if ($usingescrow)
                {
                        //refresh(HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&sub=rfp-escrow');
                        //exit();
                }
                $show['escrow'] = ($usingescrow) ? true : false;
                return true;
        }
        
        /*
        * Function to allow service buyers to unaward a previous awarded bid proposal from his/her service auction.
        * The auction will re-open if any time is left.  Additionally, this function will determine if escrow is
        * enabled (and enabled for the project) and will execute the escrow refund handler function within the
        * escrow class.  Furthermore, this function will re-credit the service provider for any final value fees
        * he or she incured during the award process from the marketplace and will be re-credited.
        *
        * Additionally added new param "slientmode" which will work in cases for cron jobs where we do not want any
        * template to load based on a successful or unsuccessful unaward.
        *
        * @param       integer      bid id
        * @param       boolean      slient mode (default false)
        *
        * @return      nothing
        */
        function unaward_service_auction($bidid = 0, $bidgrouping = true)
        {
                global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                if ($bidgrouping == true)
		{
			$field = 'bid_id';
			$table = 'project_bids';
		}
		else 
		{
			$field = 'id';
			$table = 'project_realtimebids';
		}
                // any bid award process going on?
                $bidsql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . $table . "
                        WHERE " . $field . " = '" . intval($bidid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($bidsql) > 0)
                {
                        $bidres = $ilance->db->fetch_array($bidsql, DB_ASSOC);
                        // make sure bid has wait approval OR approval_accepted status
                        // to be sure this project has actually been accepted or pending acceptance
                        $prosql = $ilance->db->query("
                                SELECT user_id
                                FROM " . DB_PREFIX . "projects
                                WHERE project_id = '" . $bidres['project_id'] . "'
                                    AND (status = 'wait_approval' OR status = 'approval_accepted')
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($prosql) > 0)
                        {
                                // select service provider making the buyer wait too long (most likely)
                                $select_bidders_wait_approval = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . $table . "
                                        WHERE " . $field . " = '" . intval($bidid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($select_bidders_wait_approval) > 0)
                                {
                                        
                                        while ($res2 = $ilance->db->fetch_array($select_bidders_wait_approval, DB_ASSOC))
                                        {
                                                // remove award count for winning service provider
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET serviceawards = serviceawards - 1
                                                        WHERE user_id = '" . $res2['user_id'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                // remove award count for buyer who chose winning service provider
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET servicesold = servicesold - 1
                                                        WHERE user_id = '" . $bidres['user_id'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                // update from wait_approval auction status to open or expired
                                                // check if project has already expired
                                                $sql_rfp = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "projects
                                                        WHERE date_end <= '" . DATETODAY . " " . TIMENOW . "'
                                                            AND project_id = '" . $res2['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_rfp) > 0)
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET status = 'expired',
                                                                close_date = '0000-00-00 00:00:00',
                                                                haswinner = '0',
                                                                winner_user_id = '0'
                                                                WHERE project_id = '" . $res2['project_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $auctionisopen = 0;
                                                }
                                                else
                                                {
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "projects
                                                                SET status = 'open',
                                                                close_date = '0000-00-00 00:00:00',
                                                                haswinner = '0',
                                                                winner_user_id = '0'
                                                                WHERE project_id = '" . $res2['project_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $auctionisopen = 1;
                                                }
                                                $sql_rfpinfo = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "projects
                                                        WHERE project_id = '" . $res2['project_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_rfpinfo) > 0)
                                                {
                                                        $res_rfpinfo = $ilance->db->fetch_array($sql_rfpinfo, DB_ASSOC);
                                                        if ($auctionisopen)
                                                        {
                                                                // add this auction back into the categories
                                                                $ilance->categories->build_category_count($res_rfpinfo['cid'], 'add', "unaward_service_auction(): adding increment count category id $res_rfpinfo[cid]");
                                                        }
                                                        // email provider with wait approval status
                                                        $sql_vendor = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . $res2['user_id'] . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        while ($res_emails2 = $ilance->db->fetch_array($sql_vendor, DB_ASSOC))
                                                        {
                                                                $existing = array(
                                                                        '{{username}}' => $res_emails2['username'],
                                                                        '{{project_user_username}}' => fetch_user('username', $res_rfpinfo['user_id']),
                                                                        '{{project_title}}' => stripslashes($res_rfpinfo['project_title']),
                                                                        '{{p_id}}' => $res_rfpinfo['project_id'],
                                                                );
                                                                $ilance->email->mail = $res_emails2['email'];
                                                                $ilance->email->slng = fetch_user_slng($res_emails2['user_id']);
                                                                $ilance->email->get('buyer_waited_response_notify');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('buyer_waited_response_notify_admin');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                        }
                                                        // #### ESCROW SYSTEM CHECKUP ######################
                                                        // escrow checkup and re-credit if applicable
                                                        if ($ilconfig['escrowsystem_enabled'] AND $res_rfpinfo['filter_escrow'] == '1' AND $res_rfpinfo['escrow_id'] > 0)
                                                        {
                                                                // since the buyer may have funded his/her escrow
                                                                // lets check that and refund the escrow funds from the account back to the
                                                                // auction owners online account because a new escrow account will be created
                                                                // once the buyer awards a new service provider
                                                                $ilance->escrow_handler->escrow_handler('buyercancel', 'service', $res_rfpinfo['escrow_id'], false);
                                                        }
                                                        // #### FINAL VALUE FEE RE-CREDIT (IF APPLIES) #####
                                                        // final value fee re-credit if applicable
                                                        // calculate final value fee to service provider
                                                        // for the bid amount that was originally awarded and accepted
                                                        $ilance->accounting_fees->construct_final_value_fee(intval($bidid), $res_rfpinfo['cid'], $res_rfpinfo['project_id'], 'refund', $res_rfpinfo['project_state'], $bidgrouping);
                                                        // set bid state back to normal from previously awarded provider
                                                        // most likely with the bidstatus of wait_approval
                                                        // this will unaward the actual awarded bid and set the award date to nil
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . $table . "
                                                                SET bidstatus = 'placed',
                                                                bidstate = '',
                                                                date_awarded = '0000-00-00 00:00:00'
                                                                WHERE " . $field . " = '" . intval($bidid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // set 'reviewing' bid states back to 'placed' for all other service providers
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . $table . "
                                                                SET bidstatus = 'placed',
                                                                bidstate = ''
                                                                WHERE project_id = '" . $res_rfpinfo['project_id'] . "'
                                                                    AND bidstate = 'reviewing'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($bidgrouping == false)
							{
								$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '" . intval($bidres['bid_id']) . "'", "date_added");
								if ($date == $bidres['date_added'])
								{
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "project_bids
										SET bidstatus = 'placed',
										bidstate = '',
										date_awarded = '0000-00-00 00:00:00'
										WHERE bid_id = '" . intval($bidres['bid_id']) . "'
										LIMIT 1
									", 0, null, __FILE__, __LINE__);
									// set 'reviewing' bid states back to 'placed' for all other service providers
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "project_bids
										SET bidstatus = 'placed',
										bidstate = ''
										WHERE project_id = '" . $res_rfpinfo['project_id'] . "'
										    AND bidstate = 'reviewing'
									", 0, null, __FILE__, __LINE__);
								}
							}
                                                        // send email to all previous bidders if the auction still has time to live
                                                        $select_vendors_reviewing = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . $table . "
                                                                WHERE project_id = '" . $res2['project_id'] . "'
                                                                    AND user_id != '" . $res2['user_id'] . "'
                                                                    AND " . $field . " != '" . intval($bidid) . "'
                                                                GROUP BY user_id
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($select_vendors_reviewing) > 0 AND $auctionisopen)
                                                        {
                                                                while ($res3 = $ilance->db->fetch_array($select_vendors_reviewing, DB_ASSOC))
                                                                {
                                                                        $run_project_query = $ilance->db->query("
                                                                                SELECT *
                                                                                FROM " . DB_PREFIX . "projects
                                                                                WHERE project_id = '" . $res3['project_id'] . "'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($run_project_query) > 0)
                                                                        {
                                                                                $run_project_results = $ilance->db->fetch_array($run_project_query, DB_ASSOC);
                                                                                $select_all_previous_bidders_info = $ilance->db->query("
                                                                                        SELECT *
                                                                                        FROM " . DB_PREFIX . "users
                                                                                        WHERE user_id = '" . $res3['user_id'] . "'
                                                                                            AND status = 'active'
                                                                                        LIMIT 1
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                if ($ilance->db->num_rows($select_all_previous_bidders_info) > 0)
                                                                                {
                                                                                        while ($res_emails = $ilance->db->fetch_array($select_all_previous_bidders_info, DB_ASSOC))
                                                                                        {
                                                                                                // email admin
                                                                                                $ilance->email->mail = $res_emails['email'];
                                                                                                $ilance->email->slng = fetch_user_slng($res_emails['user_id']);
                                                                                                $ilance->email->get('bids_reopen_notify');		
                                                                                                $ilance->email->set(array(
                                                                                                        '{{username}}' => $res_emails['username'],
                                                                                                        '{{project_user_username}}' => $_SESSION['ilancedata']['user']['username'],
                                                                                                        '{{project_title}}' => stripslashes($run_project_results['project_title']),
                                                                                                        '{{p_id}}' => $run_project_results['project_id'],
                                                                                                ));
                                                                                                $ilance->email->send();
                                                                                        }
                                                                                }
                                                                        }
                                                                }
                                                        }    
                                                }                    
                                        }
                                }
                                return true;
                        }
                }
                return false;
        }
        
        /*
        * Function to allow service providers to confirm and commit to a bid already awarded to them.
        * This function is run when the service provider was initially awarded by the buyer
        * and the provider logs-in and actually "accepts" this work/project from the buyer.
        * Additionally, this function will also calculate the final value fee for the overall project
        * and generate an unpaid invoice to the service provider (winner)
        * 
        * @param       integer      bid id
        * @param       integer      service provider id
        *
        * @return      bool         true or false if we were able to accept the awarded project
        */
        function service_auction_award_accept($bidid = 0, $userid = 0, $pid = 0)
        {
                global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilconfig;
                $cid = fetch_auction('cid', $pid);
                $bidgrouping = $ilance->categories->bidgrouping($cid);
                if($pid != 0)
                {
                	$table = ($bidgrouping == true) ? 'project_bids' : 'project_realtimebids';	
                	$field = ($bidgrouping == true) ? 'bid_id' : 'id';
                }
                else 
                {
                	$table = 'project_bids';
                	$field = 'bid_id';
                }
                
                // fetch provider / winner infos
                $sel = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . $table . "
                        WHERE " . $field . " = '" . intval($bidid) . "'
                            AND user_id = '" . intval($userid) . "'
                            AND bidstatus = 'placed'
                            AND bidstate = 'wait_approval'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sel) > 0)
                {
                        while ($result = $ilance->db->fetch_array($sel, DB_ASSOC))
                        {
                                $ownerid = $result['project_user_id'];
                                $winner_user_id = $result['user_id'];
                                $winner_project_id = $result['project_id'];
                                $winner_bid_message = stripslashes($result['proposal']);
                                $winner_bid_price = $result['bidamount'];
                                $winner_bid_estimate_days = $result['estimate_days'];
                                $winner_bidamounttype = $result['bidamounttype'];
                                $winner_bid_measure = $this->construct_measure($winner_bidamounttype);
                                $winner_bid_date_added = print_date($result['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                $winner_bid_amount = ($winner_bidamounttype == 'entire' OR $winner_bidamounttype == 'lot' OR $winner_bidamounttype == 'weight') ? $result['bidamount'] : ($result['bidamount'] * $winner_bid_estimate_days);
                                
                                $sel_winner_user_email = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($winner_user_id) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sel_winner_user_email) > 0)
                                {
                                        $result_winner_user_email = $ilance->db->fetch_array($sel_winner_user_email, DB_ASSOC);
                                        $winner_user_email = $result_winner_user_email['email'];
                                        $winner_user_username = stripslashes($result_winner_user_email['username']);
                                        $winner_user_first_name = $result_winner_user_email['first_name'];
                                        $winner_user_last_name = $result_winner_user_email['last_name'];
                                        $winner_user_address = stripslashes($result_winner_user_email['address']);
                                        $winner_user_address2 = stripslashes($result_winner_user_email['address2']);
                                        $winner_user_city = ucfirst($result_winner_user_email['city']);
                                        $winner_user_state = ucfirst($result_winner_user_email['state']);
                                        $winner_user_zip_code = mb_strtoupper($result_winner_user_email['zip_code']);
                                        $winner_user_phone = $result_winner_user_email['phone'];
                                        $winner_user_country = $ilance->common_location->print_user_country($winner_user_id, fetch_user_slng($winner_user_id));
                                        
                                        // fetch auction info
                                        $sel_project_info = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . intval($winner_project_id) . "'
                                                    AND user_id = '" . intval($ownerid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sel_project_info) > 0)
                                        {
                                                while ($result_p = $ilance->db->fetch_array($sel_project_info, DB_ASSOC))
                                                {
                                                        $project_title = stripslashes($result_p['project_title']);
                                                        $project_description = stripslashes($result_p['description']);
                                                        $project_date_added = print_date($result_p['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                        $project_date_end = print_date($result_p['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                        $project_bids = $result_p['bids'];
                                                        $project_budget = $this->construct_budget_overview($result_p['cid'], $result_p['filtered_budgetid']);
                                                        $project_user_id = $result_p['user_id'];
                                                        
                                                        // fetch auction owner info
                                                        $sel_project_email = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . intval($ownerid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sel_project_email) > 0)
                                                        {
                                                                $result_email = $ilance->db->fetch_array($sel_project_email, DB_ASSOC);
                                                                
                                                                $project_user_email = $result_email['email'];
                                                                $project_user_username = stripslashes($result_email['username']);
                                                                $project_user_first_name = $result_email['first_name'];
                                                                $project_user_last_name = $result_email['last_name'];
                                                                $project_user_address = stripslashes($result_email['address']);
                                                                $project_user_address2 = stripslashes($result_email['address2']);
                                                                $project_user_city = ucfirst($result_email['city']);
                                                                $project_user_state = ucfirst($result_email['state']);
                                                                $project_user_zip_code = mb_strtoupper($result_email['zip_code']);
                                                                $project_user_phone = $result_email['phone'];
                                                                $project_user_country_id = $result_email['country'];
                                                                $project_user_country = $ilance->common_location->print_user_country(intval($ownerid), fetch_user_slng(intval($ownerid)));
                                                                
                                                                // accept job and update project status to approval_accepted
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects
                                                                        SET status = 'approval_accepted',
                                                                        haswinner = '1',
                                                                        winner_user_id = '" . intval($winner_user_id) . "'
                                                                        WHERE project_id = '" . intval($winner_project_id) . "'
                                                                            AND user_id = '" . intval($ownerid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // update bidstatus of winner to awarded
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . $table . "
                                                                        SET bidstatus = 'awarded'
                                                                        WHERE " . $field . " = '" . intval($bidid) . "'
                                                                            AND project_id = '" . intval($winner_project_id) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // update all other bidders with choseanother status
                                                                // except bids that the buyer has already declined
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . $table . "
                                                                        SET bidstatus = 'choseanother'
                                                                        WHERE project_id = '" . intval($winner_project_id) . "'
                                                                            AND " . $field . " != '" . intval($bidid) . "'
                                                                            AND bidstatus != 'declined'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                if ($bidgrouping == false)
																{
																	$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '".intval($result['bid_id'])."'", "date_added");
																	if ($date == $result['date_added'])
																	{
																		$ilance->db->query("
																			UPDATE " . DB_PREFIX . "project_bids
																			SET bidstatus = 'awarded'
																			WHERE bid_id = '" . intval($result['bid_id']) . "'
																				AND project_id = '" . intval($winner_project_id) . "'
																			LIMIT 1
																		", 0, null, __FILE__, __LINE__);
																		                                	
																		$ilance->db->query("
		                                                                        UPDATE " . DB_PREFIX . "project_bids
		                                                                        SET bidstatus = 'choseanother'
		                                                                        WHERE project_id = '" . intval($winner_project_id) . "'
		                                                                            AND bid_id != '" . intval($result['bid_id']) . "'
		                                                                            AND bidstatus != 'declined'
		                                                                ", 0, null, __FILE__, __LINE__);
																	}
																}
																else 
																{
																	$id = $ilance->db->fetch_field(DB_PREFIX . "project_realtimebids", "date_added = '" . $result['date_added'] . "' AND project_id = '" . intval($winner_project_id) . "'", "id");
																	if (is_numeric($id))
																	{
																		$ilance->db->query("
																			UPDATE " . DB_PREFIX . "project_realtimebids
																			SET bidstatus = 'awarded'
																			WHERE id = '" . intval($id) . "'
																				AND project_id = '" . intval($winner_project_id) . "'
																			LIMIT 1
																		", 0, null, __FILE__, __LINE__);
																		                                	
																		$ilance->db->query("
		                                                                        UPDATE " . DB_PREFIX . "project_realtimebids
		                                                                        SET bidstatus = 'choseanother'
		                                                                        WHERE project_id = '" . intval($winner_project_id) . "'
		                                                                            AND id != '" . intval($id) . "'
		                                                                            AND bidstatus != 'declined'
		                                                                ", 0, null, __FILE__, __LINE__);
																	}
																}
                                                                
                                                                // add a new award count for service provider
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET serviceawards = serviceawards + 1
                                                                        WHERE user_id = '" . intval($winner_user_id) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // add a new sold services count for the buyer
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET servicesold = servicesold + 1
                                                                        WHERE user_id = '" . intval($ownerid) . "'
                                                                        LIMIT 1
                                                                ", 0, null, __FILE__, __LINE__);
                                                                
                                                                // calculate final value fee to service provider
                                                                // for the bid amount that was awarded and accepted
                                                                $ilance->accounting_fees->construct_final_value_fee(intval($bidid), $result_p['cid'], $result_p['project_id'], 'charge', 'service', $bidgrouping);
                                                                
                                                                $existing = array(
                                                                        '{{bid_id}}' => intval($bidid),
                                                                        '{{winner_bid_message}}' => $winner_bid_message,
                                                                        '{{winner_bid_price}}' => $ilance->currency->format($winner_bid_price, $result_p['currencyid']),
                                                                        '{{winner_bid_amount}}' => $ilance->currency->format($winner_bid_amount, $result_p['currencyid']),
                                                                        '{{measure}}' => $winner_bid_measure,
                                                                        '{{winner_bid_estimate_days}}' => $winner_bid_estimate_days,
                                                                        '{{winner_bid_date_added}}' => $winner_bid_date_added,
                                                                        '{{p_id}}' => $winner_project_id,
                                                                        '{{project_title}}' => $project_title,
                                                                        '{{project_description}}' => $project_description,
                                                                        '{{project_date_added}}' => $project_date_added,
                                                                        '{{project_date_end}}' => $project_date_end,
                                                                        '{{project_bids}}' => $project_bids,
                                                                        '{{project_budget}}' => $project_budget,
                                                                        '{{buyer_email}}' => $project_user_email,
                                                                        '{{provider_email}}' => $winner_user_email,
                                                                        '{{winner_user_username}}' => $winner_user_username,
                                                                        '{{project_user_username}}' => $project_user_username,
                                                                        '{{recipient}}' => $project_user_username,
                                                                );
                                                                
                                                                $ilance->email->mail = $project_user_email;
                                                                $ilance->email->slng = fetch_user_slng(intval($ownerid));
                                                                $ilance->email->get('provider_accepted_award');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                
                                                                $existing['{{recipient}}'] = $winner_user_username;
                                                                $ilance->email->mail = $winner_user_email;
                                                                $ilance->email->slng = fetch_user_slng(intval($winner_user_id));
                                                                $ilance->email->get('provider_accepted_award');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                
                                                                $existing['{{recipient}}'] = 'Admin';
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('provider_accepted_award');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                
                                                                return 1;
                                                        }
                                                }
                                        }
                                }
                        }
                }
                
                return 0;
        }
        
        /*
        * Function to allow service buyers to unaward & decline a previous awarded bid proposal from his/her service auction.
        *
        * @param       integer      bid id
        * @param       integer      service provider id
        *
        * @return      nothing
        */
        function service_auction_award_decline($bidid = 0, $userid = 0, $pid = 0)
        {
                global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                // at this point the service provider has logged in to their awarded bid tabs
                // and has chosen to not accept this buyers award due to reasons beyond
                // anyone's control.  if the buyer has already funded their escrow account
                // let's re-credit their account for the amount and reset auction to normal
                
                if ($pid != 0)
                {
                	$cid = fetch_auction('cid', $pid);
                	$bidgrouping = $ilance->categories->bidgrouping($cid);
                	$table = ($bidgrouping == true) ? 'project_bids' : 'project_realtimebids';	
                	$field = ($bidgrouping == true) ? 'bid_id' : 'id';
                }
                else 
                {
                	$table = 'project_bids';
                	$field = 'bid_id';
                }
                
                // #### fetch bids
                $sel = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . $table . "
                        WHERE " . $field . " = '" . intval($bidid) . "'
                            AND user_id = '" . intval($userid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sel) > 0)
                {
                        
                        
                        while ($result = $ilance->db->fetch_array($sel, DB_ASSOC))
                        {
                                $ownerid = $result['project_user_id'];
                                $winner_user_id = $result['user_id'];
                                $winner_user_username = fetch_user('username', $result['user_id']);
                                $winner_project_id = $result['project_id'];
                                $winner_bid_message = stripslashes($result['proposal']);
                                $winner_bid_date_added = print_date($result['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                $winner_bid_price = $result['bidamount'];
                                $winner_bid_estimate_days = $result['estimate_days'];
                                $winner_bidamounttype = $result['bidamounttype'];
                                $winner_bid_measure = $this->construct_measure($winner_bidamounttype);
                                $winner_bid_amount = ($winner_bidamounttype == 'entire' OR $winner_bidamounttype == 'lot' OR $winner_bidamounttype == 'weight') ? $result['bidamount'] : ($result['bidamount'] * $winner_bid_estimate_days);
                                
                                if ($bidgrouping)
                                {
	                                $sql_rb = $ilance->db->query("
										SELECT * FROM " . DB_PREFIX . "project_realtimebids 
										WHERE bid_id='".$bidid."' 
											AND project_id='".$result['project_id']."'
											AND project_user_id='".$result['project_user_id']."'
											AND user_id='".$result['user_id']."'
											AND date_added='".$result['date_added']."'
											AND date_awarded='".$result['date_awarded']."'
											AND date_updated='".$result['date_updated']."'
									", 0, null, __FILE__, __LINE__);
									$res_rb = $ilance->db->fetch_array($sql_rb);
									$realtimebidid = $res_rb['id'];
                                }
                                
                                // #### has auction already expired?
                                $sql_rfp = $ilance->db->query("
                                        SELECT project_id
                                        FROM " . DB_PREFIX . "projects
                                        WHERE date_end <= '" . DATETODAY . " " . TIMENOW . "'
                                            AND project_id = '" . intval($winner_project_id) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_rfp) > 0)
                                {
                                        // #### project's already expired
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'expired',
                                                close_date = '0000-00-00 00:00:00',
                                                haswinner = '0',
                                                winner_user_id = '0'
                                                WHERE user_id = '" . intval($ownerid) . "'
                                                    AND project_id = '" . $winner_project_id . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects
                                                SET status = 'open',
                                                close_date = '0000-00-00 00:00:00',
                                                bidsdeclined = bidsdeclined + 1,
                                                haswinner = '0',
                                                winner_user_id = '0'
                                                WHERE user_id = '" . intval($ownerid) . "'
                                                    AND project_id = '" . $winner_project_id . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                
                                // #### update winner bid status to declined
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . $table . "
                                        SET bidstatus = 'declined',
                                        bidstate = ''
                                        WHERE " . $field . " = '" . intval($bidid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                
                                // #### update other bidders bid status to placed from reviewing
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . $table . "
                                        SET bidstatus = 'placed',
                                        bidstate = ''
                                        WHERE project_id = '" . intval($winner_project_id) . "'
                                            AND bidstate = 'reviewing'
                                            AND user_id != '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                
                                if ($bidgrouping == false)
                                {
									$date = $ilance->db->fetch_field(DB_PREFIX . "project_bids", "bid_id = '".intval($result['bid_id'])."'", "date_added");
									if ($date == $result['date_added'])
									{
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_bids
											SET bidstatus = 'declined',
											bidstate = ''
											WHERE bid_id = '" . intval($bidid) . "'
												AND user_id = '" . intval($userid) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "project_bids
											SET bidstatus = 'placed',
											bidstate = ''
											WHERE project_id = '" . intval($winner_project_id) . "'
												AND bidstate = 'reviewing'
												AND user_id != '" . intval($userid) . "'
										", 0, null, __FILE__, __LINE__);
									}
                                }
                                
                                // #### auction owner information
                                $sel_project_email = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($ownerid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sel_project_email) > 0)
                                {
                                        $result_email = $ilance->db->fetch_array($sel_project_email, DB_ASSOC);
                                        
                                        ($apihook = $ilance->api('service_auction_award_decline_updates')) ? eval($apihook) : false;
                                        
                                        $project_user_email = $result_email['email'];
                                        $project_user_username = stripslashes($result_email['username']);
                                        $project_user_first_name = ucfirst(stripslashes($result_email['first_name']));
                                        $project_user_last_name = ucfirst(stripslashes($result_email['last_name']));
                                        $project_user_phone = $result_email['phone'];
                                        
                                        // #### auction information
                                        $sel_project_info = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "projects
                                                WHERE project_id = '" . $winner_project_id . "'
                                                    AND user_id = '" . intval($ownerid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sel_project_info) > 0)
                                        {
                                                while ($result_p = $ilance->db->fetch_array($sel_project_info, DB_ASSOC))
                                                {
                                                        // escrow checkup and re-credit to buyer if applicable
                                                        // we do this because the buyer may have instantly paid escrow account
                                                        // even BEFORE the provider accepts the award
                                                        // this function allows the provider to reject the buyers award
                                                        // and will re-credit the buyer's funds for this project in full
                                                        // we do that because when a new provider is awarded a new escrow account
                                                        // will be created once again
                                                        if ($ilconfig['escrowsystem_enabled'] AND $result_p['filter_escrow'] == '1' AND $result_p['escrow_id'] > 0)
                                                        {
                                                                $ilance->escrow_handler->escrow_handler('buyercancel', 'service', $result_p['escrow_id'], false);
                                                        }
                                                        
                                                        $project_title = stripslashes($result_p['project_title']);
                                                        $project_description = stripslashes($result_p['description']);
                                                        $project_date_added = print_date($result_p['date_added'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                        $project_date_end = print_date($result_p['date_end'], $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
                                                        $project_bids = $result_p['bids'];
                                                        $project_budget = $this->construct_budget_overview($result_p['cid'], $result_p['filtered_budgetid']);
                                                        $project_user_id = $result_p['user_id'];
                                                        
                                                        $existing = array(
                                                                '{{winner_user_username}}' => $winner_user_username,
                                                                '{{project_user_username}}' => $project_user_username,
                                                                '{{bid_id}}' => intval($bidid),
                                                                '{{winner_bid_message}}' => $winner_bid_message,
                                                                '{{winner_bid_price}}' => $ilance->currency->format($winner_bid_price, $result_p['currencyid']),
                                                                '{{winner_bid_estimate_days}}' => $winner_bid_estimate_days,
                                                                '{{winner_bid_date_added}}' => $winner_bid_date_added,
                                                                '{{p_id}}' => $result_p['project_id'],
                                                                '{{project_title}}' => $project_title,
                                                                '{{project_description}}' => $project_description,
                                                                '{{project_date_added}}' => $project_date_added,
                                                                '{{project_date_end}}' => $project_date_end,
                                                                '{{project_bids}}' => $project_bids,
                                                                '{{project_budget}}' => $project_budget
                                                        );
											 
											// #### email owner
											$ilance->email->mail = $project_user_email;
											$ilance->email->slng = fetch_user_slng(intval($userid));
											$ilance->email->get('provider_declined_award');		
											$ilance->email->set($existing);
											$ilance->email->send();	
											
											// #### email to other bidders
											$other_bidders = $ilance->db->query("
												SELECT user_id FROM " . DB_PREFIX . "project_bids
												WHERE project_id = '" . intval($winner_project_id) . "'
			                                                AND bidstatus = 'placed'
			                                                AND user_id != '" . intval($userid) . "'
											", 0, null, __FILE__, __LINE__);

											if($ilance->db->num_rows($other_bidders) > 0)
											{
												while ($result_other_bidders = $ilance->db->fetch_array($other_bidders, DB_ASSOC))
												{
													$existing1 = array(
														'{{winner_user_username}}' => $winner_user_username,
														'{{project_user_username}}' => $project_user_username,
														'{{bid_id}}' => intval($bidid),
														'{{winner_bid_message}}' => $winner_bid_message,
														'{{winner_bid_price}}' => $ilance->currency->format($winner_bid_price, $result_p['currencyid']),
														'{{winner_bid_estimate_days}}' => $winner_bid_estimate_days,
														'{{winner_bid_date_added}}' => $winner_bid_date_added,
														'{{p_id}}' => $result_p['project_id'],
														'{{project_title}}' => $project_title,
														'{{project_description}}' => $project_description,
														'{{project_date_added}}' => $project_date_added,
														'{{project_date_end}}' => $project_date_end,
														'{{project_bids}}' => $project_bids,
														'{{project_budget}}' => $project_budget,
														'{{bid_user}}' => fetch_user('username',$result_other_bidders['user_id'])
													);

													$fetch_email = fetch_user('email',$result_other_bidders['user_id']);
													$ilance->email->mail = $fetch_email;
													$ilance->email->slng = fetch_user_slng(intval($result_other_bidders['user_id']));
													$ilance->email->get('provider_declined_award_other');		
													$ilance->email->set($existing1);
													$ilance->email->send();	
												}
											}

											return 1;
                                                }
                                        }
                                }
                        }
                }
                
                return 0;
        }
        
        /*
        * Function to allow service buyers to decline an unawarded bid proposal from his/her service auction.
        *
        * @param       integer      bid id
        * @param       integer      buyer id
        *
        * @return      nothing
        */
        function service_auction_bid_decline($bidid = 0, $userid = 0, $bidgrouping = true, $slng = 'eng')
        {
                global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilconfig, $ilpage;

                if($bidgrouping)
				{
					$field = 'bid_id';
					$table = 'project_bids';
					$bid_id_field = '';
				}
				else 
				{
					$field = 'id';
					$table = 'project_realtimebids';
					$bid_id_field = ', bid_id';
				}
                
                $bidsql = $ilance->db->query("
                        SELECT project_id, user_id" . $bid_id_field . ", date_added
                        FROM " . DB_PREFIX . $table . "
                        WHERE " . $field . " = '" . intval($bidid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($bidsql) > 0)
                {
                        $bidres = $ilance->db->fetch_array($bidsql);
                        
                        // if the admin is grouping bids in this auction category
                        // we will remove all bids for that user (as the system acts like only one bid is placed)
                        // if we are not grouping bids we will only decline the single proposal.
                        if ($bidgrouping)
                        {
                                // bid grouping enabled
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "project_bids
                                        SET bidstatus = 'declined',
                                        bidstate = ''
                                        WHERE project_user_id = '" . intval($userid) . "'
                                                AND project_id = '" . $bidres['project_id'] . "'
                                                AND user_id = '" . $bidres['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                        }
                        else
                        {	
	                             $ilance->db->query("
	                                    UPDATE " . DB_PREFIX . "project_realtimebids
	                                    SET bidstatus = 'declined',
	                                    bidstate = ''
	                                    WHERE id = '" . $bidid . "'
	                            ", 0, null, __FILE__, __LINE__);
	                             
	                         	$ilance->db->query("
	                                UPDATE " . DB_PREFIX . "project_bids
	                                SET bidstatus = 'declined',
	                                bidstate = ''
	                                WHERE bid_id = '" . $bidres['bid_id'] . "'
	                                	AND date_added = '" . $bidres['date_added'] . "'
	                        	", 0, null, __FILE__, __LINE__);
                        }
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "projects
                                SET bidsdeclined = bidsdeclined + 1
                                WHERE user_id = '" . intval($userid) . "'
                                        AND project_id = '" . $bidres['project_id'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        $sqlbid = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . $table . "
                                WHERE " . $field . " = '" . intval($bidid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        
                        if ($ilance->db->num_rows($sqlbid) > 0)
                        {
                                $res_project_from_bid = $ilance->db->fetch_array($sqlbid);
                                
                                
                                
                                $existing = array(
                                        '{{vendor}}' => fetch_user('username', $res_project_from_bid['user_id']),
                                        '{{buyer}}' => fetch_user('username', $userid),
                                        '{{project_title}}' => stripslashes(fetch_auction('project_title', $res_project_from_bid['project_id'])),
                                );
                                
                                $ilance->email->mail = fetch_user('email', $res_project_from_bid['user_id']);
                                $ilance->email->slng = fetch_user_slng($res_project_from_bid['user_id']);

                                $ilance->email->get('provider_bid_declined');		
                                $ilance->email->set($existing);
                                
                                $ilance->email->send();
                                
                                $ilance->email->mail = SITE_EMAIL;
                                $ilance->email->slng = fetch_site_slng();
                                
                                $ilance->email->get('provider_bid_declined_admin');		
                                $ilance->email->set($existing);
                                
                                $ilance->email->send();
                                
                                return true;
                        }
                }
                
                return false;
        }
        
        function has_provider_accepted_award($projectid = 0, $userid = 0)
        {
                global $ilance, $ilconfig, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                
                $cid = fetch_auction('cid', $projectid);
                $bidgrouping = $ilance->categories->bidgrouping($cid);
                //$table = ($bidgrouping) ? 'project_realtimebids' : 'project_bids';
                $table = ($bidgrouping) ? 'project_bids' : 'project_realtimebids';
                
                $sql = $ilance->db->query("
                        SELECT bid_id
                        FROM " . DB_PREFIX . $table . "
                        WHERE project_id = '" . intval($projectid) . "'
                                AND user_id = '" . intval($userid) . "'
                                AND bidstatus = 'awarded'
                                AND state = 'service'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        return true;
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