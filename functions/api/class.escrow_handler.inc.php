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
if (!class_exists('escrow'))
{
	exit;
}

/**
* Function to handle escrow activity
*
* @package      iLance\Escrow\Handler
* @version      4.0.0.8059
* @author       ILance
*/
class escrow_handler extends escrow
{
	/**
        * Function to process a service escrow function within the admin control panel
        *
        * @param       string       mode (cancel, refund or confirmrelease)
        * @param       string       type (service or product)
        * @param       integer      escrow id
        * @param       boolean      is silent mode? (default false) silent mode returns only true/false from this function when set to true.
        *
        * @return      bool         Returns true or false if refund could be completed
        */
        function escrow_handler($mode = '', $type = '', $escrowid = 0, $silentmode = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage, $show;
		
                if ($type == 'service' AND $escrowid > 0)
                {
			// #### run from buyer unawarding bid (or admin from admincp)
			// cancel this entire escrow account & reopen status if time is left
			// escrow will not be cancelled IF admin option $ilconfig['escrowsystem_payercancancelfundsafterrelease'] == false ...
                        if ($mode == 'buyercancel')
                        {
                                // #### ensure buyers escrow payment is paid
                                $escrow_check = $ilance->db->query("
                                        SELECT escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, date_paid, date_released, date_cancelled, escrowamount, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, qty, buyerfeedback, sellerfeedback, status, sellermarkedasshipped, sellermarkedasshippeddate
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ");
                                if ($ilance->db->num_rows($escrow_check) > 0)
                                {
                                        // #### escrow account exists so recredit account & create invoice trail
                                        $resultescrow = $ilance->db->fetch_array($escrow_check, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $resultescrow['project_id']);
					
                                        $sql_rfp = $ilance->db->query("
                                                SELECT id
                                                FROM " . DB_PREFIX . "projects
                                                WHERE date_end <= '" . DATETODAY . " " . TIMENOW . "'
                                                    AND user_id = '" . $resultescrow['project_user_id'] . "'
                                                    AND project_id = '" . $resultescrow['project_id'] . "'
                                        ");
					$new_project_status = ($ilance->db->num_rows($sql_rfp) > 0) ? 'expired' : 'open';
                                        
					// #### determine if this escrow account is finished
					$canremove = true;
					$escrowfinished = (($resultescrow['date_released'] != '0000-00-00 00:00:00' AND $resultescrow['status'] == 'finished') ? true : false);					
					if ($escrowfinished)
					{
						$canremove = (($ilconfig['escrowsystem_payercancancelfundsafterrelease'] == 0) ? false : true);
					}
					
					if ($canremove)
					{
						if ($resultescrow['escrowamount'] > 0)
						{
							// #### update account balances and credit account service buyer for amount held in escrow
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "users
								SET total_balance = total_balance + $resultescrow[escrowamount],
								available_balance = available_balance + $resultescrow[escrowamount]
								WHERE user_id = '" . $resultescrow['project_user_id'] . "'
							");
							
							// #### subtract escrow payment from total income spent
							$ilance->accounting_payment->insert_income_spent($resultescrow['project_user_id'], sprintf("%01.2f", $resultescrow['escrowamount']), 'debit');
							
							// #### created refund invoice trail
							
							$ilance->accounting->insert_transaction(
								0,
								0,
								0,
								$resultescrow['project_user_id'],
								0,
								0,
								0,
								'{_escrow_refund_credit}: ' . stripslashes($ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $resultescrow['invoiceid'] . "'", "description")),
								sprintf("%01.2f", $resultescrow['escrowamount']),
								sprintf("%01.2f", $resultescrow['escrowamount']),
								'paid',
								'credit',
								'account',
								DATETIME24H,
								DATEINVOICEDUE,
								DATETIME24H,
								'',
								0,
								0,
								0
							);
							
							// #### reopen auction if time is left
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "projects
								SET escrow_id = '0',
								status = '" . $new_project_status . "'
								WHERE user_id = '" . $resultescrow['project_user_id'] . "'
								    AND project_id = '" . $resultescrow['project_id'] . "'
							");
						}
						
						// #### remove the escrow account for this auction
						$ilance->db->query("
							DELETE FROM " . DB_PREFIX . "projects_escrow
							WHERE project_user_id = '" . $resultescrow['project_user_id'] . "'
							    AND project_id = '" . $resultescrow['project_id'] . "'
							    AND bid_id = '" . $resultescrow['bid_id'] . "'
						");
						
						// #### dispatch some email
						
						$existing = array(
							'{{owner}}' => fetch_user('username', $resultescrow['project_user_id']),
							'{{provider}}' => fetch_user('username', $resultescrow['user_id']),
							'{{project_title}}' => fetch_auction('project_title', $resultescrow['project_id']),
							'{{escrowamount}}' => $ilance->currency->format($resultescrow['escrowamount'], $currencyid),
						);
					
						$ilance->email->mail = SITE_EMAIL;
						$ilance->email->slng = fetch_site_slng();
						$ilance->email->get('escrow_cancelled_autorefund_admin');		
						$ilance->email->set($existing);
						$ilance->email->send();
						
						$ilance->email->mail = fetch_user('email', $resultescrow['project_user_id']);
						$ilance->email->slng = fetch_user_slng($resultescrow['project_user_id']);
						$ilance->email->get('service_escrow_cancelled_autorefund');		
						$ilance->email->set($existing);
						$ilance->email->send();
						
						$ilance->email->mail = fetch_user('email', $resultescrow['user_id']);
						$ilance->email->slng = fetch_user_slng($resultescrow['user_id']);
						$ilance->email->get('service_escrow_cancelled_autorefund');		
						$ilance->email->set($existing);
						$ilance->email->send();
						return true;	
					}
                                }
                                return false;
                        }
                        // #### return buyers funds to his account balance, don't cancel but just refund money as buyer can pay this escrow again later
			// also refund escrow fee if paid, if unpaid, delete it..
			else if ($mode == 'buyercancelescrow')
                        {
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ");
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" .  $res_escrow['project_user_id'] . "'
                                        ");
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                if ($res_escrow['escrowamount'] > 0)
                                                {
                                                        $new_avail_balance = ($avail_balance + $res_escrow['escrowamount']);
                                                        $new_total_balance = ($total_balance + $res_escrow['escrowamount']);
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET total_balance = '" . $new_total_balance . "',
                                                                available_balance = '" . $new_avail_balance . "'
                                                                WHERE user_id = '" .  $res_escrow['project_user_id'] . "'
                                                        ");
                                                        
                                                        // #### track income spent
                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['project_user_id'], sprintf("%01.2f", $res_escrow['escrowamount']), 'debit');
							
							// #### set main escrow invoice as unpaid again
							if ($res_escrow['invoiceid'] > 0)
							{
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "invoices
									SET paid = '0.00',
									status = 'unpaid',
									paiddate = '0000-00-00 00:00:00'
									WHERE invoiceid = '" .  $res_escrow['invoiceid'] . "'
								");
							}
                                                }
                                                
                                                // update escrow table and set as "pending" since the buyer still awarded someone
						// so we should only be returning his funds with provider still awarded
						// (to perhaps refund at a later date by re-clicking Pay Now)
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                        SET status = 'pending',
                                                        escrowamount = '0.00'
                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                ");
                                                
                                                $res_escrow['project_title'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res_escrow['project_id'] . "'", "project_title"));
                                                
                                                
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                        '{{project_title}}' => $res_escrow['project_title'],
                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['escrowamount'], $currencyid),
                                                );
                        
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                $ilance->email->get('service_escrow_release_cancelled_provider');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                $ilance->email->slng = fetch_user_slng( $res_escrow['project_user_id']);
                                                $ilance->email->get('service_escrow_release_cancelled_buyer');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('service_escrow_release_cancelled_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                return true;
                                        }
                                }
                                return false;
                        }
                        else if ($mode == 'refund')
                        {
                                // #### refunding escrow allows the admin to take back the money originally forwarded
                                // into the service providers account back to the buyer
                                // this function assumes the escrowamount field is 0.00 since the funds should
                                // have been already released from this escrow account to the provider
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT escrow_id, bid_id, project_id, invoiceid, project_user_id, user_id, date_awarded, date_paid, date_released, date_cancelled, escrowamount, bidamount, shipping, total, fee, fee2, isfeepaid, isfee2paid, feeinvoiceid, qty, buyerfeedback, sellerfeedback, status, sellermarkedasshipped, sellermarkedasshippeddate
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                            AND escrowamount = '0.00'
                                ");
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $escrow['commissionfee'] = 0;
                                        $escrow['totalamount'] = sprintf("%01.2f", $res_escrow['bidamount']);
                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']);
                                        
                                        // #### commission fee for escrow release (paid by service provider)
                                        if ($ilconfig['escrowsystem_escrowcommissionfees'])
                                        {
                                                $logic = $this->fetch_escrow_commission_logic('serviceprovider');
                                                if ($logic == 'fixed')
                                                {
                                                        // #### fixed service commission logic
                                                        $escrow['commissionfee'] = sprintf("%01.2f", $commissionfee);
                                                        $escrow['totalamount']  = sprintf("%01.2f", $res_escrow['bidamount']);
                                                        
                                                        // #### service provider paid a fee to obtain this escrow amount
                                                        // so take the amount he made and add the fee he/she paid
                                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']) - $escrow['commissionfee'];
                                                }
                                                else
                                                {
                                                        // #### percentage of the overall cost logic
                                                        $escrow['commissionfee'] = sprintf("%01.2f", ($res_escrow['bidamount'] * $this->fetch_escrow_commission('serviceprovider') / 100));
                                                        $escrow['totalamount']  = sprintf("%01.2f", $res_escrow['bidamount']);
                                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']) - $escrow['commissionfee'];
                                                }
                                        }
                                        
                                        // #### let's finish the refund process	
                                        $sql_account2 = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['user_id'] . "'
                                        ");
                                        if ($ilance->db->num_rows($sql_account2) > 0)
                                        {
                                                // #### debit provider account 
                                                $res_account2 = $ilance->db->fetch_array($sql_account2, DB_ASSOC);
                                                $avail_balance2 = $res_account2['available_balance'];
                                                $total_balance2 = $res_account2['total_balance'];
                                                
                                                if ($escrow['accountamount'] > 0)
                                                {
                                                        // #### subtract amount released to provider
                                                        $new_avail_balance2 = ($avail_balance2 - $escrow['accountamount']);
                                                        $new_total_balance2 = ($total_balance2 - $escrow['accountamount']);
                                    
                                                        // #### update account balances
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET total_balance = '" . $new_total_balance2 . "',
                                                                available_balance = '" . $new_avail_balance2 . "'
                                                                WHERE user_id = '" . $res_escrow['user_id'] . "'
                                                        ");
                                    
                                                        // #### track income spent
                                                        $ilance->accounting_payment->insert_income_reported($res_escrow['user_id'], sprintf("%01.2f", $escrow['accountamount']), 'debit');
                                    
                                                        // #### create transaction trail
                                                        
                                                        $ilance->accounting->insert_transaction(
                                                                0,
                                                                0,
                                                                0,
                                                                $res_escrow['user_id'],
                                                                0,
                                                                0,
                                                                0,
                                                                '{_refund_from_online_account}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_back_to}' . ': ' . fetch_user('username', $res_escrow['project_user_id']),
                                                                sprintf("%01.2f", $escrow['accountamount']),
                                                                sprintf("%01.2f", $escrow['accountamount']),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                DATETIME24H,
                                                                '{_refund_forced_by_administration_please_note_this_refund_would_have_included_any_fees_you_paid_during_the_escrow_process}',
                                                                0,
                                                                0,
                                                                0
                                                        );
                                                }
                                
                                                // #### recredit buyer #################
                                                $sql_account = $ilance->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . intval($res_escrow['project_user_id']) . "'
                                                ");
                                                if ($ilance->db->num_rows($sql_account) > 0)
                                                {
                                                        $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                        $avail_balance = $res_account['available_balance'];
                                                        $total_balance = $res_account['total_balance'];
                                                        if ($res_escrow['bidamount'] > 0)
                                                        {
                                                                $new_avail_balance = ($avail_balance + $res_escrow['bidamount']);
                                                                $new_total_balance = ($total_balance + $res_escrow['bidamount']);
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET total_balance = '" . $new_total_balance . "',
                                                                        available_balance = '" . $new_avail_balance . "'
                                                                        WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                                                ");
                                        
                                                                // #### track income spent
                                                                $ilance->accounting_payment->insert_income_spent($res_escrow['project_user_id'], sprintf("%01.2f", $res_escrow['bidamount']), 'debit');
                                                
                                                                // #### create buyer transaction credit trail
                                                                
                                                                $ilance->accounting->insert_transaction(
                                                                        0,
                                                                        0,
                                                                        0,
                                                                        $res_escrow['project_user_id'],
                                                                        0,
                                                                        0,
                                                                        0,
                                                                        '{_refund_to_online_account}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_from}' . ': ' . fetch_user('username', $res_escrow['user_id']),
                                                                        sprintf("%01.2f", $res_escrow['bidamount']),
                                                                        sprintf("%01.2f", $res_escrow['bidamount']),
                                                                        'paid',
                                                                        'credit',
                                                                        'account',
                                                                        DATETIME24H,
                                                                        DATEINVOICEDUE,
                                                                        DATETIME24H,
                                                                        '{_refund_forced_by_administration_please_note_this_refund_would_have_included_any_fees_you_paid_during_the_escrow_process}',
                                                                        0,
                                                                        0,
                                                                        0
                                                                );
                                        
                                                                // #### update escrow set as pending
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET status = 'pending'
                                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                                ");
                                                
                                                                // #### dispatch some email
                                                                
                                                                $existing = array(
                                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                                        '{{project_title}}' => fetch_auction('project_title', $res_escrow['project_id']),
                                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['bidamount'], $currencyid),
                                                                );
                                
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                                $ilance->email->get('service_escrow_release_cancelled_provider');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                        
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);
                                                                $ilance->email->get('service_escrow_release_cancelled_buyer');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('service_escrow_release_cancelled_admin');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                return true;
                                                        }
                                                }
                                        }
                                }
                                return false;
                        }
			
			// #### buyer releases funds to service provider ########
                        else if ($mode == 'buyerconfirmrelease')
                        {
                                // admin (or buyer of project) forces funds from escrow into provider's account
                                // escrow fees will be generated to service provider if applicable based on the amount held within the 'fee2' column
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                            AND status = 'confirmed'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res['project_id']);
                                        $escrow = array();
                                        
                                        // #### does this escrow account hold a fee to the service provider?
                                        $escrow['commissionfee'] = ($res['fee2'] > 0) ? sprintf("%01.2f", $res['fee2']) : 0;
                                        $escrow['totalamount'] = sprintf("%01.2f", $res['escrowamount']);
                                        $fee2invoiceid = $isfee2paid = 0;
                                        
                                        
					
					
                                        if ($escrow['commissionfee'] > 0)
                                        {
						$avail_balance = fetch_user('available_balance', $res['user_id']);
                                                $total_balance = fetch_user('total_balance', $res['user_id']);
						$autopayment = fetch_user('autopayment', $res['user_id']);

						// #### auto-payments enabled
						if ($escrow['commissionfee'] <= $avail_balance AND $autopayment)
						{
							// create a paid escrow commission fee based on the release of this escrow payment
							// do not apply any tax because this fee already has tax applied!
							// the ending 1 tells the system to NOT calculate any tax for this entry!
							$fee2invoiceid = $ilance->accounting->insert_transaction(
								0,
								$res['project_id'],
								0,
								$res['user_id'],
								0,
								0,
								0,
								'{_commission_fee_for_escrow_deposit} ' . fetch_auction('project_title', $res['project_id']) . ' #' . $res['project_id'] . ' {_from} ' . fetch_user('username', $res['project_user_id']),
								sprintf("%01.2f", $escrow['commissionfee']),
								sprintf("%01.2f", $escrow['commissionfee']),
								'paid',
								'debit',
								'account',
								DATETIME24H,
								DATEINVOICEDUE,
								DATETIME24H,
								'',
								0,
								0,
								1,
								'',
								0,
								0,
								1 
							);
													
							$ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = available_balance - " . sprintf("%01.2f", $escrow['commissionfee']) . ",
                                                                total_balance = total_balance - " . sprintf("%01.2f", $escrow['commissionfee']) . "
                                                                WHERE user_id = '" . intval($res['user_id']) . "'
                                                        ", 0, null, __FILE__, __LINE__);
													
							$ilance->accounting_payment->insert_income_spent($res['user_id'], sprintf("%01.2f", $escrow['commissionfee']), 'credit');
							$isfee2paid = 1;
							
							// since we've auto-paid the fee to the service provider let's re-adjust
							// the total amount we'll show as the release to the provider's online account balance
							//$escrow['totalamount'] = ($escrow['totalamount'] - $escrow['commissionfee']);
						}
						
						// #### auto-payments disabled 
						else
						{
							// create unpaid escrow fee based on the release of this escrow payment
							$fee2invoiceid = $ilance->accounting->insert_transaction(
								0,
								$res['project_id'],
								0,
								$res['user_id'],
								0,
								0,
								0,
								'{_commission_fee_for_escrow_deposit} ' . fetch_auction('project_title', $res['project_id']) . ' #' . $res['project_id'] . ' {_from} ' . fetch_user('username', $res['project_user_id']),
								sprintf("%01.2f", $escrow['commissionfee']),
								sprintf("%01.2f", $escrow['commissionfee']),
								'unpaid',
								'debit',
								'account',
								DATETIME24H,
								DATEINVOICEDUE,
								DATETIME24H,
								'',
								0,
								0,
								1,
								'',
								0,
								0,
								1 
							);
							
							$isfee2paid = 0;
						}
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET isescrowfee = '1'
							WHERE invoiceid = '" . $fee2invoiceid . "'
						", 0, null, __FILE__, __LINE__);
                                        }
                                        
                                        // #### release funds from escrow; set as finished
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "projects_escrow
                                                SET escrowamount = '0.00',
                                                status = 'finished',
                                                date_released = '" . DATETIME24H . "',
                                                fee2invoiceid = '" . $fee2invoiceid . "',
                                                isfee2paid = '" . $isfee2paid . "'
                                                WHERE escrow_id = '" . intval($escrowid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // #### show transaction for released buyer funds to provider
                                        if ($escrow['totalamount'] > 0)
                                        {
                                                $ilance->accounting->insert_transaction(
                                                        0,
                                                        $res['project_id'],
                                                        0,
                                                        $res['user_id'],
                                                        0,
                                                        0,
                                                        0,
                                                        '{_escrow_payment_release_for} ' . fetch_auction('project_title', $res['project_id']) . ' #' . $res['project_id'] . ' {_from} ' . fetch_user('username', $res['project_user_id']),
                                                        sprintf("%01.2f", $escrow['totalamount']),
                                                        sprintf("%01.2f", $escrow['totalamount']),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        0
                                                );
                                                                   
                                                // track income reported for provider (release to account)
                                                $ilance->accounting_payment->insert_income_reported($res['user_id'], sprintf("%01.2f", $escrow['totalamount']), 'credit');
                                                
                                                // fetch providers account balance and deposit escrowed funds!
                                                $sqlacc = $ilance->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . $res['user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlacc) > 0)
                                                {
                                                        $resacc = $ilance->db->fetch_array($sqlacc, DB_ASSOC);
                                                        $available_balance_before = $resacc['available_balance'];
                                                        $total_balance_before = $resacc['total_balance'];
                                                        $available_balance_after = ($available_balance_before + $escrow['totalamount']);
                                                        $total_balance_after = ($total_balance_before + $escrow['totalamount']);
                                                        
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = '" . $available_balance_after . "',
                                                                total_balance = '" . $total_balance_after . "'
                                                                WHERE user_id = '" . $res['user_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
							$existing = array(
                                                                '{{project_id}}' => $res['project_id'],
                                                                '{{biddername}}' => stripslashes($ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . $res['user_id'] . "'", "username")),
                                                                '{{ownername}}' => stripslashes($ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . $res['project_user_id'] . "'", "username")),
                                                                '{{project_title}}' => stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res['project_id'] . "'", "project_title")),
                                                                '{{escrowamount}}' => $ilance->currency->format($res['escrowamount'], $currencyid),
                                                                '{{commissionfee}}' => $ilance->currency->format($escrow['commissionfee']),
                                                        );
			
                                                        $ilance->email->mail = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . $res['project_user_id'] . "'", "email");
                                                        $ilance->email->slng = fetch_user_slng($res['project_user_id']);
                                                        $ilance->email->get('service_escrow_release_funds_buyer');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        $ilance->email->mail = $ilance->db->fetch_field(DB_PREFIX . "users", "user_id = '" . $res['user_id'] . "'", "email");
                                                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                        $ilance->email->get('service_escrow_release_funds_provider');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        
                                                        $ilance->email->mail = SITE_EMAIL;
                                                        $ilance->email->slng = fetch_site_slng();
                                                        $ilance->email->get('service_escrow_release_funds_admin');		
                                                        $ilance->email->set($existing);
                                                        $ilance->email->send();
                                                        return true;
                                                }
                                        }
                                        return false;
                                }
                                else
                                {
					if ($silentmode)
					{
						return false;
					}
                                        $area_title = '{_rfp_escrow_management} - {_release_of_funds_error}';
                                        $page_title = SITE_NAME . ' - {_rfp_escrow_management} - {_release_of_funds_error}';
                                        if (isset($adminmode) AND $adminmode)
                                        {
                                                print_action_failed('{_were_sorry_there_was_a_problem_releasing_the_funds_from_within_this_escrow_account_to_your_service_provider}', $ilpage['accounting'] . '?cmd=escrows');
                                                exit();
                                        }
                                        print_notice($area_title, '{_were_sorry_there_was_a_problem_releasing_the_funds_from_within_this_escrow_account_to_your_service_provider}', HTTPS_SERVER . $ilpage['escrow'] . '?cmd=management&amp;sub=rfp-escrow', '{_return_to_the_previous_menu}');
                                        exit();
                                }
                        }        
                }
                else if ($type == 'product' AND $escrowid > 0)
                {
                        if ($mode == 'sellercancelescrow')
                        {
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                { 
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                $new_avail_balance = ($avail_balance + $res_escrow['escrowamount']);
                                                $new_total_balance = ($total_balance + $res_escrow['escrowamount']);
                                                
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET total_balance = '" . $new_total_balance . "',
                                                        available_balance = '" . $new_avail_balance . "'
                                                        WHERE user_id = '" . $res_escrow['user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                
                                                // #### adjust bidders total amount spent to this merchant > debit the spendings for this refund
                                                $ilance->accounting_payment->insert_income_spent($res_escrow['user_id'], sprintf("%01.2f", $res_escrow['escrowamount']), 'debit');
                                                
                                                // #### adjust seller total amount received from this bidder > debit the spendings for this refund
                                                //$ilance->accounting_payment->insert_income_reported($res_escrow['project_user_id'], sprintf("%01.2f", $res_escrow['escrowamount']), 'debit');
                                                
                                                // #### create transaction credit
                                                
                                                        $ilance->accounting->insert_transaction(
                                                        0,
                                                        $res_escrow['project_id'],
                                                        0,
                                                        $res_escrow['user_id'],
                                                        0,
                                                        0,
                                                        0,
                                                        '{_product_escrow_delivery_cancellation}' . ': ' . '{_item_id}' . ' #' . $res_escrow['project_id'] . ': ' . '{_refund_from_escrow_to_online_account}',
                                                        sprintf("%01.2f", $res_escrow['escrowamount']),
                                                        sprintf("%01.2f", $res_escrow['escrowamount']),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATETIME24H,
                                                        DATETIME24H,
                                                        '{_merchant_cancelled_delivery_on}' . ' ' . DATETIME24H,
                                                        0,
                                                        0,
                                                        0
                                                );
                                                
                                                // #### update escrow as cancelled
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                        SET status = 'pending',
                                                        escrowamount = '0.00',
                                                        date_cancelled = '" . DATETIME24H . "'
                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                ", 0, null, __FILE__, __LINE__);
						
						// #### mark associated invoice used for buyers escrow payment process back to unpaid
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "invoices
							SET status = 'unpaid',
							paid = '0.00',
							paiddate = '0000-00-00 00:00:00',
							custommessage = '" . $ilance->db->escape_string('{_merchant_cancelled_delivery_on}' . ' ' . DATETIME24H) . "'
							WHERE invoiceid = '" . $res_escrow['invoiceid'] . "'
						", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "project_bids
                                                        SET winnermarkedaspaid = '0',
							winnermarkedaspaiddate = '0000-00-00 00:00:00',
							winnermarkedaspaidmethod = ''
                                                        WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_realtimebids
							SET winnermarkedaspaid = '0',
							winnermarkedaspaiddate = '0000-00-00 00:00:00',
							winnermarkedaspaidmethod = ''
                                                        WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
                                                
                                                // #### refund escrow seller fee if already paid
                                                if ($res_escrow['isfeepaid'] AND $res_escrow['feeinvoiceid'] > 0)
                                                {
                                                        //..........
                                                }
                                                
                                                // #### refund escrow buyer fee is already paid
                                                if ($res_escrow['isfee2paid'] AND $res_escrow['fee2invoiceid'] > 0)
                                                {
                                                        //...........
                                                }
                                                
                                                // #### mark as unshipped (listing table)
                                                $ilance->shipping->mark_listing_as_unshipped($res_escrow['project_id'], $escrowid, $res_escrow['project_user_id'], $res_escrow['user_id'], 'escrow');
                                                
                                                
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                        '{{project_title}}' => stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res_escrow['project_id'] . "'", "project_title")),
                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['escrowamount'], $currencyid),
                                                );
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                $ilance->email->get('product_escrow_delivery_cancelled_bidder');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);
                                                $ilance->email->get('product_escrow_delivery_cancelled_merchant');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('product_escrow_delivery_cancelled_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                return true;
                                        }
                                }
                                return false;
                        }
                        else if ($mode == 'buyercancelescrow')
                        {
                                // #### bidder cancelling funds released to seller because admin allows it
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
					if($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
					{
						$res_escrow['escrowamount'] = convert_currency($default_currency, $res_escrow['escrowamount'], $currencyid);
					}
																
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                $new_avail_balance = ($avail_balance + $res_escrow['escrowamount']);
                                                $new_total_balance = ($total_balance + $res_escrow['escrowamount']);
                                                if ($res_escrow['escrowamount'] > 0)
                                                {
                                                        // #### update bidders online account
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET total_balance = '" . $new_total_balance . "',
                                                                available_balance = '" . $new_avail_balance . "'
                                                                WHERE user_id = '" . $res_escrow['user_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
							
							// #### create transaction credit
							
								$ilance->accounting->insert_transaction(
								0,
								$res_escrow['project_id'],
								0,
								$res_escrow['user_id'],
								0,
								0,
								0,
								'{_product_escrow_delivery_cancellation}' . ': ' . '{_item_id}' . ' #' . $res_escrow['project_id'] . ': ' . '{_refund_from_escrow_to_online_account}',
								sprintf("%01.2f", $res_escrow['escrowamount']),
								sprintf("%01.2f", $res_escrow['escrowamount']),
								'paid',
								'credit',
								'account',
								DATETIME24H,
								DATETIME24H,
								DATETIME24H,
								'{_buyer_cancelled_escrow_and_returned_funds_back_to_account_balance_on}' . ' ' . DATETIME24H,
								0,
								0,
								0
							);
                                                        
                                                        // #### adjust bidders total amount spent to this merchant > debit the spendings for this cancellation
                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['user_id'], sprintf("%01.2f", $res_escrow['escrowamount']), 'debit');
							
							// #### mark associated invoice used for buyers escrow payment process back to unpaid
							$ilance->db->query("
								UPDATE " . DB_PREFIX . "invoices
								SET status = 'unpaid',
								paid = '0.00',
								paiddate = '0000-00-00 00:00:00',
								custommessage = '" . $ilance->db->escape_string('{_buyer_cancelled_escrow_and_returned_funds_back_to_account_balance_on}' . ' ' . DATETIME24H) . "'
								WHERE invoiceid = '" . $res_escrow['invoiceid'] . "'
							", 0, null, __FILE__, __LINE__);
                                                }
                        
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                        SET status = 'pending',
                                                        escrowamount = '0.00',
							date_paid = '0000-00-00 00:00:00'
                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                ", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "project_bids
                                                        SET winnermarkedaspaid = '0',
							winnermarkedaspaiddate = '0000-00-00 00:00:00',
							winnermarkedaspaidmethod = ''
                                                        WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
						
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_realtimebids
							SET winnermarkedaspaid = '0',
							winnermarkedaspaiddate = '0000-00-00 00:00:00',
							winnermarkedaspaidmethod = ''
							WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
                                                
                                                $res_escrow['project_title'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res_escrow['project_id'] . "'", "project_title"));
                        
                                                
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                        '{{project_title}}' => $res_escrow['project_title'],
                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['escrowamount']),
                                                );
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                $ilance->email->get('product_escrow_delivery_cancelled_bidder');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                // #### email seller
						$ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);
                                                $ilance->email->get('product_escrow_delivery_cancelled_merchant');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                
                                                // #### email admin
						$ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('product_escrow_release_cancelled_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                return true;
                                        }
                                }
                                return false;
                        }
                        else if ($mode == 'refund')
                        {
                                // #### refunding escrow allows the admin to take back the money originally forwarded
                                // into the seller account back to the buyer
                                // this function assumes the escrowamount field is 0.00 since the funds should
                                // have been already released from this escrow account to the seller
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                            AND escrowamount = '0.00'
                                ");
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $escrow['commissionfee'] = 0;
                                        $escrow['totalamount'] = sprintf("%01.2f", $res_escrow['bidamount']);
                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']);
                                        
                                        // #### commission fee for escrow release (paid by seller)
                                        if ($ilconfig['escrowsystem_escrowcommissionfees'])
                                        {
                                                $logic = $this->fetch_escrow_commission_logic('productmerchant');
                                                if ($logic == 'fixed')
                                                {
                                                        // #### fixed service commission logic
                                                        $escrow['commissionfee'] = sprintf("%01.2f", $commissionfee);
                                                        $escrow['totalamount']  = sprintf("%01.2f", $res_escrow['bidamount']);
                                                        
                                                        // #### seller paid a fee to obtain this escrow amount
                                                        // so take the amount he made and add the fee he/she paid
                                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']) - $escrow['commissionfee'];
                                                }
                                                else
                                                {
                                                        // #### percentage of the overall cost logic
                                                        $escrow['commissionfee'] = sprintf("%01.2f", ($res_escrow['bidamount'] * $this->fetch_escrow_commission('productmerchant') / 100));
                                                        $escrow['totalamount']  = sprintf("%01.2f", $res_escrow['bidamount']);
                                                        $escrow['accountamount'] = sprintf("%01.2f", $res_escrow['bidamount']) - $escrow['commissionfee'];
                                                }
                                        }
                                        
                                        // #### let's finish the refund process	
                                        $sql_account2 = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                        ");
                                        if ($ilance->db->num_rows($sql_account2) > 0)
                                        {
                                                // #### debit sellers account 
                                                $res_account2 = $ilance->db->fetch_array($sql_account2, DB_ASSOC);
                                                $avail_balance2 = $res_account2['available_balance'];
                                                $total_balance2 = $res_account2['total_balance'];
                                                
                                                if ($escrow['accountamount'] > 0)
                                                {
                                                        // #### subtract amount released to provider
                                                        $new_avail_balance2 = ($avail_balance2 - $escrow['accountamount']);
                                                        $new_total_balance2 = ($total_balance2 - $escrow['accountamount']);
                                    
                                                        // #### update account balances
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET total_balance = '" . $new_total_balance2 . "',
                                                                available_balance = '" . $new_avail_balance2 . "'
                                                                WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                                        ");
                                    
                                                        // #### track income spent
                                                        $ilance->accounting_payment->insert_income_reported($res_escrow['project_user_id'], sprintf("%01.2f", $escrow['accountamount']), 'debit');
                                    
                                                        // #### create transaction trail
                                                        
                                                        $ilance->accounting->insert_transaction(
                                                                0,
                                                                0,
                                                                0,
                                                                $res_escrow['project_user_id'],
                                                                0,
                                                                0,
                                                                0,
                                                                '{_refund_from_online_account}' . ' ' . '{_item_id}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_back_to}' . ': ' . fetch_user('username', $res_escrow['user_id']),
                                                                sprintf("%01.2f", $escrow['accountamount']),
                                                                sprintf("%01.2f", $escrow['accountamount']),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                DATETIME24H,
                                                                '{_refund_forced_by_administration_please_note_this_refund_would_have_included_any_fees_you_paid_during_the_escrow_process}',
                                                                0,
                                                                0,
                                                                0
                                                        );
                                                }
                                
                                                // #### recredit buyer #################
                                                $sql_account = $ilance->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . $res_escrow['user_id'] . "'
                                                ");
                                                if ($ilance->db->num_rows($sql_account) > 0)
                                                {
                                                        $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                        $avail_balance = $res_account['available_balance'];
                                                        $total_balance = $res_account['total_balance'];
                                                        if ($res_escrow['bidamount'] > 0)
                                                        {
                                                                $new_avail_balance = ($avail_balance + $res_escrow['bidamount']);
                                                                $new_total_balance = ($total_balance + $res_escrow['bidamount']);
                                        
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET total_balance = '" . $new_total_balance . "',
                                                                        available_balance = '" . $new_avail_balance . "'
                                                                        WHERE user_id = '" . $res_escrow['user_id'] . "'
                                                                ");
                                        
                                                                // #### track income spent
                                                                $ilance->accounting_payment->insert_income_spent($res_escrow['user_id'], sprintf("%01.2f", $res_escrow['bidamount']), 'debit');
                                                
                                                                // #### create buyer transaction credit trail
                                                                
                                                                $ilance->accounting->insert_transaction(
                                                                        0,
                                                                        0,
                                                                        0,
                                                                        $res_escrow['user_id'],
                                                                        0,
                                                                        0,
                                                                        0,
                                                                        '{_refund_to_online_account}' . ' ' . '{_item_id}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_from}' . ': ' . fetch_user('username', $res_escrow['project_user_id']),
                                                                        sprintf("%01.2f", $res_escrow['bidamount']),
                                                                        sprintf("%01.2f", $res_escrow['bidamount']),
                                                                        'paid',
                                                                        'credit',
                                                                        'account',
                                                                        DATETIME24H,
                                                                        DATEINVOICEDUE,
                                                                        DATETIME24H,
                                                                        '{_refund_forced_by_administration_please_note_this_refund_would_have_included_any_fees_you_paid_during_the_escrow_process}',
                                                                        0,
                                                                        0,
                                                                        0
                                                                );
                                        
                                                                // #### update escrow set as pending
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET status = 'pending'
                                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                                ");
                                                
                                                                // #### dispatch some email
                                                                
                                                                
                                                                $existing = array(
                                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                                        '{{project_title}}' => fetch_auction('project_title', $res_escrow['project_id']),
                                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['bidamount'], $currencyid),
                                                                );
                                
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);
                                                                $ilance->email->get('product_escrow_delivery_cancelled_merchant');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                        
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                                $ilance->email->get('product_escrow_delivery_cancelled_bidder');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('product_escrow_release_cancelled_admin');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                return true;
                                                        }
                                                }
                                        }
                                }
                                return false;
                        }
                        else if ($mode == 'sellerconfirmdelivery')
                        {
                                // #### set escrow phase to confirmed
                                
                                // the final phase will allow the bidder to release
                                // funds within escrow to this merchant
                                // at that point any escrow fees will be deducted
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "projects_escrow
                                        SET status = 'confirmed'
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                    
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        $escrowamount = $escrowamount_site_currency = $res_escrow['escrowamount'];
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
										if ($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
										{
											$escrowamount_site_currency = convert_currency($default_currency, $res_escrow['escrowamount'], $currencyid);
										}
										$trackingnumber = isset($ilance->GPC['trackingnumber']) ? $ilance->GPC['trackingnumber'] : '';
										
                                        // #### update as shipped (listing table)
                                        $ilance->shipping->mark_listing_as_shipped($res_escrow['project_id'], $escrowid, $res_escrow['project_user_id'], $res_escrow['user_id'], 'escrow', $trackingnumber);
                                        
                                        $existing = array(
                                                '{{project_id}}' => $res_escrow['project_id'],
                                                '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                '{{project_title}}' => stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res_escrow['project_id'] . "'", "project_title")),
                                                '{{escrowamount}}' => $ilance->currency->format($escrowamount_site_currency),
                                        );
                                        
                                        $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                        $ilance->email->get('product_escrow_delivery_confirmed_bidder');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        
                                        $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                        $ilance->email->get('product_escrow_delivery_confirmed_merchant');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        return true;
                                }
                                return false;
                        }
                        else if ($mode == 'buyerconfirmrelease')
                        {
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                AND status != 'finished'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
					if($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
					{
						$res_escrow['total'] = convert_currency($default_currency, $res_escrow['total'], $currencyid);
					}
					
                                        // #### fetch merchant account balance
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                
                                                // #### calculate new balances for merchant (bid amount + shipping if any)
                                                $new_avail_balance = ($avail_balance + $res_escrow['total']);
                                                $new_total_balance = ($total_balance + $res_escrow['total']);
                        
                                                // #### update mechant online account balance with funds from escrow
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET total_balance = '" . $new_total_balance . "',
                                                        available_balance = '" . $new_avail_balance . "'
                                                        WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                        
                                                // #### adjust merchant total income received from bidder
                                                $ilance->accounting_payment->insert_income_reported($res_escrow['project_user_id'], sprintf("%01.2f", $res_escrow['total']), 'credit');
                                                
                                                // #### set escrow status to finished and clear escrow amount field (as funds are now released)
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow 
                                                        SET status = 'finished',
                                                        escrowamount = '0.00',
                                                        date_released = '" . DATETIME24H . "'
                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                        
                                                // #### create new invoice/receipt to merchant (full amount from bidder including shipping fees)
                                                
                                                $new_escrowcredit_id = $ilance->accounting->insert_transaction(
                                                        0,
                                                        $res_escrow['project_id'],
                                                        0,
                                                        $res_escrow['project_user_id'],
                                                        0,
                                                        0,
                                                        0,
                                                        '{_escrow_payment_release_for_rfp}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_from_bidder}' . ': ' . fetch_user('username', $res_escrow['user_id']),
                                                        sprintf("%01.2f", $res_escrow['total']),
                                                        sprintf("%01.2f", $res_escrow['total']),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                
                                                // #### has admin defined any commission fees for the release of this escrow from a bidder to the merchant?
                                                list($feenotax, $tax, $fee) = $ilance->escrow_fee->fetch_merchant_escrow_fee_plus_tax($res_escrow['project_user_id'], $res_escrow['total']);
                                                if ($fee > 0 AND $res_escrow['isfeepaid'] == 0)
                                                //if ($res_escrow['fee'] > 0 AND $res_escrow['isfeepaid'] == 0)
                                                {
                                                		$res_escrow['fee'] = $fee;
                                                        $sql_account2 = $ilance->db->query("
                                                                SELECT available_balance, total_balance, autopayment
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sql_account2) > 0)
                                                        {
                                                                $res_account2 = $ilance->db->fetch_array($sql_account2, DB_ASSOC);
                                                                $avail_balance = $res_account2['available_balance'];
                                                                $total_balance = $res_account2['total_balance'];
                                                                
                                                                
                                                                $taxinfo = $ilance->tax->fetch_amount($res_escrow['project_user_id'], $fee, 'commission', 1);
                                                                $txn = $ilance->accounting_payment->construct_transaction_id();
                                                                $istaxable = $tax > 0 ? 1 : 0;
                                                                
                                                                if ($avail_balance >= $res_escrow['fee'] AND $res_account2['autopayment'])
                                                                {
                                                                        $new_avail_balance = ($avail_balance - $res_escrow['fee']);
                                                                        $new_total_balance = ($total_balance - $res_escrow['fee']);
                                
                                                                        // #### update auction owners account info
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET total_balance = '" . $new_total_balance . "',
                                                                                available_balance = '" . $new_avail_balance . "'
                                                                                WHERE user_id = '" . $res_escrow['project_user_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                
                                                                        // #### adjust auction owners total amount spent
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['project_user_id'], sprintf("%01.2f", $res_escrow['fee']), 'credit');
                                
                                                                        $ilance->db->query("
																					INSERT INTO " . DB_PREFIX . "invoices
																					(invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
																					VALUES(
																					NULL,
																					'" . $res_escrow['project_id'] . "',
																					'" . $res_escrow['project_user_id'] . "',
																					'" . $ilance->db->escape_string('{_commission_fee_for_rfp}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_from_bidder}' . ': ' . fetch_user('username', $res_escrow['user_id']) ) . "',
																					'" . $ilance->db->escape_string($feenotax) . "',
																					'" . $ilance->db->escape_string($fee) . "',
																					'" . $ilance->db->escape_string($fee) . "',
																					'" . $istaxable . "',
																					'" . $ilance->db->escape_string($tax) . "',
																					'" . $ilance->db->escape_string($taxinfo) . "',
																					'paid',
																					'commission',
																					'account',
																					'" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
																					'" . DATETIME24H . "',
																					'" . DATETIME24H . "',
																					'" . DATETIME24H . "',
																					'" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
																					'" . $txn . "',
																					'1')
				                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $feeinvoiceid = $ilance->db->insert_id();
                                                                        $isfeepaid = 1;
                                                                }
								
								// #### create new unpaid invoice to seller showing escrow commission fee debit
                                                                else
                                                                {
                                                                    	$ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res_escrow['project_id'] . "',
                                                                                '" . $res_escrow['project_user_id'] . "',
                                                                                '" . $ilance->db->escape_string('{_commission_fee_for_rfp}' . ' #' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': ' . '{_from_bidder}' . ': ' . fetch_user('username', $res_escrow['user_id']) ) . "',
                                                                                '" . $ilance->db->escape_string($feenotax) . "',
                                                                                '" . $ilance->db->escape_string($fee) . "',
                                                                                '" . $istaxable . "',
                                                                                '" . $ilance->db->escape_string($taxamount) . "',
                                                                                '" . $ilance->db->escape_string($taxinfo) . "',
                                                                                'unpaid',
                                                                                'commission',
                                                                                'account',
                                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
                                                                                '" . $txn . "',
                                                                                '1')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $feeinvoiceid = $ilance->db->insert_id();
                                                                        $isfeepaid = 0;
                                                                }
                                                                
                                                                // #### set escrow fee invoice id association
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow 
                                                                        SET feeinvoiceid = '" . $feeinvoiceid . "',
                                                                        isfeepaid = '" . $isfeepaid . "'
                                                                        WHERE escrow_id = '" . intval($escrowid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
								
																// #### set escrow fee invoice id association
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET isescrowfee = '1'
                                                                        WHERE invoiceid = '" . $feeinvoiceid . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                }
                                                
                                                $res_escrow['project_title'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "projects", "project_id = '" . $res_escrow['project_id'] . "'", "project_title"));
                        
                                                
												$existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                        '{{project_title}}' => $res_escrow['project_title'],
                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['total']),
                                                        '{{commission}}' => $ilance->currency->format($res_escrow['fee']),
                                                        '{{commissionfee}}' => $ilance->currency->format($res_escrow['fee']),
                                                );
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);                                                
                                                $ilance->email->get('product_escrow_release_funds_bidder');		
                                                $ilance->email->set($existing);                                                
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);                                                
                                                $ilance->email->get('product_escrow_release_funds_merchant');		
                                                $ilance->email->set($existing);                                                
                                                $ilance->email->send();
                                                
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();                                                
                                                $ilance->email->get('product_escrow_release_funds_admin');		
                                                $ilance->email->set($existing);                                                
                                                $ilance->email->send();                                                
                                                return true;
                                        }
                                }
                                return false;
                        }
                }
                else if ($type == 'buynow' AND $escrowid > 0)
                {
                        if ($mode == 'sellerconfirmdelivery')
                        {
                                // #### set escrow account to confirmed phase
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "buynow_orders 
                                        SET status = 'pending_delivery'
                                        WHERE orderid = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "buynow_orders
                                        WHERE orderid = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $trackingnumber = isset($ilance->GPC['trackingnumber']) ? $ilance->GPC['trackingnumber'] : '';
                                        // #### update listing as shipped (listings table)
                                        $ilance->shipping->mark_listing_as_shipped($res_escrow['project_id'], $res_escrow['orderid'], $res_escrow['owner_id'], $res_escrow['buyer_id'], 'buynow', $trackingnumber);
                                        $ilance->GPC['countryname'] = isset($ilance->GPC['countryname']) ? $ilance->GPC['countryname'] : '';
                                        // #### dispatch some email
                                        $existing = array(
                                                '{{project_id}}' => $res_escrow['project_id'],
                                                '{{biddername}}' => fetch_user('username', $res_escrow['buyer_id']),
                                                '{{ownername}}' => fetch_user('username', $res_escrow['owner_id']),
                                                '{{project_title}}' => stripslashes(fetch_auction('project_title', $res_escrow['project_id'])),
                                                '{{escrowamount}}' => $ilance->currency->format($res_escrow['amount']),
                                                '{{countryname}}' => $ilance->GPC['countryname'],
                                                '{{trackingnumber}}' => $trackingnumber,
                                        );
                                        $ilance->email->mail = fetch_user('email', $res_escrow['buyer_id']);
                                        $ilance->email->slng = fetch_user_slng($res_escrow['buyer_id']);
                                        $ilance->email->get('purchase_now_delivery_confirmed_buyer');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        $ilance->email->mail = fetch_user('email', $res_escrow['owner_id']);
                                        $ilance->email->slng = $_SESSION['ilancedata']['user']['slng'];
                                        $ilance->email->get('purchase_now_delivery_confirmed_merchant');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('purchase_now_delivery_confirmed_admin');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        return true;
                                }
                                return false;        
                        }
                        else if ($mode == 'sellerconfirmofflinedelivery')
                        {
                                // #### fetch the project id ###################################
                                $sql = $ilance->db->query("
                                        SELECT project_id, owner_id, buyer_id, buyershipperid, buyerpaymethod, buyershipcost, sellermarkedasshipped, sellermarkedasshippeddate
                                        FROM " . DB_PREFIX . "buynow_orders
                                        WHERE orderid = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res['project_id']);
					$winnermarkedaspaidmethod = $ilance->payment->print_fixed_payment_method($res['buyerpaymethod'], false);
                                        // #### set paiddate if we can #########################
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "buynow_orders 
                                                SET status = 'offline_delivered',
						paiddate = '" . DATETIME24H . "',
						winnermarkedaspaid = '1',
						winnermarkedaspaiddate = '" . DATETIME24H . "',
						winnermarkedaspaidmethod = '" . $ilance->db->escape_string($winnermarkedaspaidmethod) . "'
                                                WHERE orderid = '" . intval($escrowid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        // $ilance->shipping->mark_listing_as_shipped($res['project_id'], $escrowid, $res['owner_id'], $res['buyer_id'], 'buynow');
                                        // todo: send email advising buyer that seller has confirmed offline delivery
                                        return true;
                                }
                                return false;
                        }
                        else if ($mode == 'buyerconfirmrelease')
                        {
                                // #### buyer releasing funds to merchant for purchase now item
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "buynow_orders
                                        WHERE orderid = '" . intval($escrowid) . "'
                                                AND invoiceid > 0
                                                AND qty > 0
                                                AND amount > 0
                                                AND paiddate != '0000-00-00 00:00:00'
                                                AND status = 'pending_delivery'
                                ");
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
					$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
					if ($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
					{
						$currencyid = $default_currency;
					}
                                        // #### fetch current seller account balance
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                        ");
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                $new_avail_balance = ($avail_balance + $res_escrow['amount']);
                                                $new_total_balance = ($total_balance + $res_escrow['amount']);
                                                // #### update seller online account balance with funds from escrow
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET total_balance = '" . $new_total_balance . "',
							available_balance = '" . $new_avail_balance . "'
                                                        WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                ");
                                                // #### adjust sellers total income received from buyers
                                                $ilance->accounting_payment->insert_income_reported($res_escrow['owner_id'], sprintf("%01.2f", $res_escrow['amount']), 'credit');
                                                // #### set escrow status to finished and clear escrow amount field (as funds are now released)
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET status = 'delivered',
                                                        releasedate = '" . DATETIME24H . "'
                                                        WHERE orderid = '" . intval($escrowid) . "'
                                                                AND buyer_id = '" . $res_escrow['buyer_id'] . "'
                                                ");
                                                // #### create new invoice/receipt to seller (full amount from bidder)
                                                // this allows the seller to review past history of any payments received from escrow
                                                $ilance->accounting->insert_transaction(
                                                        0,
                                                        $res_escrow['project_id'],
                                                        0,
                                                        $res_escrow['owner_id'],
                                                        0,
                                                        0,
                                                        0,
                                                        '{_escrow_payment_release_for_purchase_now_item} ' . $res_escrow['project_id'] . ': ' . fetch_auction('project_title', $res_escrow['project_id']) . ': {_from_bidder}: ' . fetch_user('username', $res_escrow['buyer_id']),
                                                        sprintf("%01.2f", $res_escrow['amount']),
                                                        sprintf("%01.2f", $res_escrow['amount']),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '{_purchase_now_escrow_credit_to_online_account_balance} - ' . print_date(DATETIME24H),
                                                        0,
                                                        0,
                                                        0
                                                );
                                                $escrowfeeamount = $fvfamount = 0;
                                                // here we will detect if any fees for this escrow process are still unpaid by the seller.
                                                // because we've just released funds, the seller now has some money to pay any pending invoices related to this escrow.
                                                // let's do that now
                                                // #### REGULAR ESCROW FEE CHECKUP #############
                                                if ($res_escrow['isescrowfeepaid'] == '0' AND $res_escrow['escrowfeeinvoiceid'] > 0)
                                                {
                                                        if ($res_escrow['escrowfee'] > 0)
                                                        {
                                                                // #### get total amount (because this fee might be taxable) depending on admin settings and tax logic
                                                                $escrowfeeamount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res_escrow['escrowfeeinvoiceid'] . "'", "totalamount");
                                                                
                                                                // #### automatically pay the debt so the site owner doens't have to track anyone down for payment
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET total_balance = total_balance - $escrowfeeamount,
									available_balance = available_balance - $escrowfeeamount
                                                                        WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                ");
								// #### set transaction as paid and show paid date along with amount paid
								$ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET paid = totalamount,
									paiddate = '" . DATETIME24H . "',
									status = 'paid'
									WHERE invoiceid = '" . $res_escrow['escrowfeeinvoiceid'] . "'
                                                                ");
								// #### set field in buy now order table to reflect the buyer escrow fee as being paid
								$ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isescrowfeepaid = '1'
									WHERE escrowfeeinvoiceid = '" . $res_escrow['escrowfeeinvoiceid'] . "'
                                                                ");
								// #### track income spending
                                                                $ilance->accounting_payment->insert_income_spent($res_escrow['owner_id'], sprintf("%01.2f", $escrowfeeamount), 'credit');
                                                        }
                                                }
                                                // #### FINAL VALUE FEE FOR ITEM POSTED IN THIS CATEGORY 
                                                if ($res_escrow['isfvfpaid'] == '0' AND $res_escrow['fvfinvoiceid'] > 0)
                                                {
                                                        if ($res_escrow['fvf'] > 0)
                                                        {
                                                                // #### get total amount (because this fee might be taxable) depending on admin settings and tax logic
                                                                $fvfamount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res_escrow['fvfinvoiceid'] . "'", "totalamount");
                                                                // #### automatically pay the debt so the site doens't have to track anyone down for payment
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET total_balance = total_balance - $fvfamount,
									available_balance = available_balance - $fvfamount
                                                                        WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                ");
								// #### set transaction as paid and show paid date along with amount paid
								$ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET paid = totalamount,
									paiddate = '" . DATETIME24H . "',
									status = 'paid'
									WHERE invoiceid = '" . $res_escrow['fvfinvoiceid'] . "'
                                                                ");
								// #### set field in buy now order table to reflect the buyer fvf fee as being paid
								$ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                                        SET isfvfpaid = '1'
									WHERE fvfinvoiceid = '" . $res_escrow['fvfinvoiceid'] . "'
                                                                ");
								// #### track income spending
                                                                $ilance->accounting_payment->insert_income_spent($res_escrow['owner_id'], sprintf("%01.2f", $fvfamount), 'credit');
                                                        }
                                                }
                                                // #### dispatch some email
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['buyer_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['owner_id']),
                                                        '{{project_title}}' => stripslashes(fetch_auction('project_title', $res_escrow['project_id'])),
                                                        '{{escrowamount}}' => $ilance->currency->format($res_escrow['amount'], $currencyid),
                                                        '{{commissionfee}}' => $ilance->currency->format($escrowfeeamount + $fvfamount),
                                                );
                                                $ilance->email->mail = fetch_user('email', $res_escrow['buyer_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['buyer_id']);
                                                $ilance->email->get('product_escrow_release_funds_bidder');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', $res_escrow['owner_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['owner_id']);
                                                $ilance->email->get('product_escrow_release_funds_merchant');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('product_escrow_release_funds_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                return true;
                                        }
                                }
                                return false;        
                        }
                        else if ($mode == 'reversal')
                        {
                                $sql_escrow_amount = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "buynow_orders
                                        WHERE orderid = '" . intval($escrowid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_escrow_amount) > 0)
                                { 
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow_amount, DB_ASSOC);
					$currencyid = fetch_auction('currencyid', $res_escrow['project_id']);
                                        $escrowamount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res_escrow['invoiceid'] . "'", "totalamount");
                                        // #### buyer account details
                                        $sql_account = $ilance->db->query("
                                                SELECT available_balance, total_balance
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_escrow['buyer_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_account) > 0)
                                        {
                                                $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                $avail_balance = $res_account['available_balance'];
                                                $total_balance = $res_account['total_balance'];
                                                $new_avail_balance = ($avail_balance + $escrowamount);
                                                $new_total_balance = ($total_balance + $escrowamount);
                                                // #### update bidder account balance
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET total_balance = '" . $new_total_balance . "',
                                                        available_balance = '" . $new_avail_balance . "'
                                                        WHERE user_id = '" . $res_escrow['buyer_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                // #### create transaction credit
                                                $ilance->accounting->insert_transaction(
                                                        0,
                                                        $res_escrow['project_id'],
                                                        $res_escrow['orderid'],
                                                        $res_escrow['buyer_id'],
                                                        0,
                                                        0,
                                                        0,
                                                        '{_purchase_now_delivery_cancellation}' . ' - ' . '{_order_id}' . ' #' . $res_escrow['orderid'] . ': ' . '{_refund_from_escrow_to_online_account}',
                                                        sprintf("%01.2f", $escrowamount),
                                                        sprintf("%01.2f", $escrowamount),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATEINVOICEDUE,
                                                        DATETIME24H,
                                                        '{_merchant_cancelled_purchase_now_delivery_on}' . ' ' . DATETIME24H,
                                                        0,
                                                        0,
                                                        0
                                                );
                                                // #### adjust buyers escrow fund payment by debitting amount spent (so income_spent) goes back down due to refund
                                                $ilance->accounting_payment->insert_income_spent($res_escrow['buyer_id'], sprintf("%01.2f", $escrowamount), 'debit');
                                                // #### REFUND BUYER ESCROW FEES ###############
                                                // refund buyer any amount paid for escrow fees taken during purchase now process
                                                // if the buyer escrow fee has not been paid, cancel it.
                                                $escrowfeebuyeramount = 0;
                                                if ($res_escrow['escrowfeebuyer'] > 0 AND $res_escrow['escrowfeebuyerinvoiceid'] > 0)
                                                {
                                                        // #### was the invoice paid?
                                                        if ($res_escrow['isescrowfeebuyerpaid'])
                                                        {
                                                                // #### buyer already paid it - recredit buyer's account.
                                                                $escrowfeebuyeramount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res_escrow['escrowfeebuyerinvoiceid'] . "'", "totalamount");
                                                                // #### buyer account details
                                                                $sql_account = $ilance->db->query("
                                                                        SELECT available_balance, total_balance
                                                                        FROM " . DB_PREFIX . "users
                                                                        WHERE user_id = '" . $res_escrow['buyer_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($sql_account) > 0)
                                                                {
                                                                        $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                                        $avail_balance = $res_account['available_balance'];
                                                                        $total_balance = $res_account['total_balance'];
                                                                        $new_avail_balance = ($avail_balance + $escrowfeebuyeramount);
                                                                        $new_total_balance = ($total_balance + $escrowfeebuyeramount);
                                                                        // #### update bidder account balance
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET total_balance = '" . $new_total_balance . "',
                                                                                available_balance = '" . $new_avail_balance . "'
                                                                                WHERE user_id = '" . $res_escrow['buyer_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // #### adjust bidders total amount spent to this merchant > debit the spendings for this refund
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['buyer_id'], sprintf("%01.2f", $escrowfeebuyeramount), 'debit');
                                                                        $ilance->accounting->insert_transaction(
                                                                                0,
                                                                                $res_escrow['project_id'],
                                                                                $res_escrow['orderid'],
                                                                                $res_escrow['buyer_id'],
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                '{_purchase_now_delivery_cancellation}' . " - " . '{_order_id}' . " #" . $res_escrow['orderid'] . ": " . '{_purchase_now_buyer_escrow_fee_refund}',
                                                                                sprintf("%01.2f", $escrowfeebuyeramount),
                                                                                sprintf("%01.2f", $escrowfeebuyeramount),
                                                                                'paid',
                                                                                'credit',
                                                                                'account',
                                                                                DATETIME24H,
                                                                                DATEINVOICEDUE,
                                                                                DATETIME24H,
                                                                                '{_purchase_now_escrow_fee_refund_completed_on}' . " " . DATETIME24H,
                                                                                0,
                                                                                0,
                                                                                0
                                                                        );
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // #### buyer hasn't paid it - cancel it!
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET status = 'cancelled',
                                                                        custommessage = '" . $ilance->db->escape_string('{_fee_was_cancelled_due_to_buy_now_order_cancellation}') . "'
                                                                        WHERE invoiceid = '" . $res_escrow['escrowfeebuyerinvoiceid'] . "'
                                                                        LIMIT 1
                                                                ");
                                                        }
                                                }
                                                // #### REFUND SELLER ESCROW FEES ##############
                                                // refund seller any amount paid for escrow fees taken during purchase now process
                                                $escrowfeeselleramount = 0;
                                                if ($res_escrow['escrowfee'] > 0 AND $res_escrow['escrowfeeinvoiceid'] > 0)
                                                {
                                                        // #### was the invoice paid?
                                                        if ($res_escrow['isescrowfeepaid'])
                                                        {
                                                                // #### seller already paid it - recredit seller's account.
                                                                $escrowfeeselleramount = $ilance->db->fetch_field(DB_PREFIX."invoices", "invoiceid = '" . $res_escrow['escrowfeeinvoiceid'] . "'", "totalamount");
                                                                // #### seller account details
                                                                $sql_account = $ilance->db->query("
                                                                        SELECT available_balance, total_balance
                                                                        FROM " . DB_PREFIX . "users
                                                                        WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($sql_account) > 0)
                                                                {
                                                                        $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                                        $avail_balance = $res_account['available_balance'];
                                                                        $total_balance = $res_account['total_balance'];
                                                                        $new_avail_balance = ($avail_balance + $escrowfeeselleramount);
                                                                        $new_total_balance = ($total_balance + $escrowfeeselleramount);
                                                                        // #### update bidder account balance
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET total_balance = '" . $new_total_balance . "',
                                                                                available_balance = '" . $new_avail_balance . "'
                                                                                WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // #### adjust bidders total amount spent to this merchant > debit the spendings for this refund
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['owner_id'], sprintf("%01.2f", $escrowfeeselleramount), 'debit');
                                                                        $ilance->accounting->insert_transaction(
                                                                                0,
                                                                                $res_escrow['project_id'],
                                                                                $res_escrow['orderid'],
                                                                                $res_escrow['owner_id'],
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                '{_purchase_now_delivery_cancellation}' . " - " . '{_order_id}' . " #" . $res_escrow['orderid'] . ": " . '{_purchase_now_seller_escrow_fee_refund}',
                                                                                sprintf("%01.2f", $escrowfeeselleramount),
                                                                                sprintf("%01.2f", $escrowfeeselleramount),
                                                                                'paid',
                                                                                'credit',
                                                                                'account',
                                                                                DATETIME24H,
                                                                                DATEINVOICEDUE,
                                                                                DATETIME24H,
                                                                                '{_purchase_now_escrow_fee_refund_completed_on}' . " " . DATETIME24H,
                                                                                0,
                                                                                0,
                                                                                0
                                                                        );
                                                                }
                                                        }
                                                        else
                                                        {
                                                                // #### seller hasn't paid it - cancel it!
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET status = 'cancelled',
                                                                        custommessage = '" . $ilance->db->escape_string('{_fee_was_cancelled_due_to_buy_now_order_cancellation}') . "'
                                                                        WHERE invoiceid = '" . $res_escrow['escrowfeeinvoiceid'] . "'
                                                                        LIMIT 1
                                                                ");
                                                        }
                                                }
                                                // #### REFUND SELLER FINAL VALUE FEES #########
                                                $fvfselleramount = 0;
                                                if ($res_escrow['fvf'] > 0 AND $res_escrow['fvfinvoiceid'] > 0)
                                                {
                                                        // #### was the invoice paid?
                                                        if ($res_escrow['isfvfpaid'])
                                                        {
                                                                // #### marketplace took a fvf from seller, let's refund it
                                                                $fvfselleramount = $ilance->db->fetch_field(DB_PREFIX . "invoices", "invoiceid = '" . $res_escrow['fvfinvoiceid'] . "'", "totalamount");
                                                                // #### seller account details
                                                                $sql_account = $ilance->db->query("
                                                                        SELECT available_balance, total_balance
                                                                        FROM " . DB_PREFIX . "users
                                                                        WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($sql_account) > 0)
                                                                {
                                                                        $res_account = $ilance->db->fetch_array($sql_account, DB_ASSOC);
                                                                        $avail_balance = $res_account['available_balance'];
                                                                        $total_balance = $res_account['total_balance'];
                                                                        $new_avail_balance = ($avail_balance + $fvfselleramount);
                                                                        $new_total_balance = ($total_balance + $fvfselleramount);
                                                                        // #### update bidder account balance
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET total_balance = '" . $new_total_balance . "',
                                                                                available_balance = '" . $new_avail_balance . "'
                                                                                WHERE user_id = '" . $res_escrow['owner_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // #### adjust bidders total amount spent to this merchant > debit the spendings for this refund
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['owner_id'], sprintf("%01.2f", $fvfselleramount), 'debit');
                                                                        $ilance->accounting->insert_transaction(
                                                                                0,
                                                                                $res_escrow['project_id'],
                                                                                $res_escrow['orderid'],
                                                                                $res_escrow['owner_id'],
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                '{_purchase_now_delivery_cancellation}' . " - " . '{_order_id}' . " #" . $res_escrow['orderid'] . ": Purchase Now Seller Final Value Fee Refund",
                                                                                sprintf("%01.2f", $fvfselleramount),
                                                                                sprintf("%01.2f", $fvfselleramount),
                                                                                'paid',
                                                                                'credit',
                                                                                'account',
                                                                                DATETIME24H,
                                                                                DATEINVOICEDUE,
                                                                                DATETIME24H,
                                                                                '{_purchase_now_final_value_fee_refund_completed_on}' . " " . DATETIME24H,
                                                                                0,
                                                                                0,
                                                                                0
                                                                        );
                                                                }        
                                                        }
                                                        else
                                                        {
                                                                // #### seller hasn't paid it - cancel it!
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET status = 'cancelled',
                                                                        custommessage = '" . $ilance->db->escape_string('{_fee_was_cancelled_due_to_buy_now_order_cancellation}') . "'
                                                                        WHERE invoiceid = '" . $res_escrow['fvfinvoiceid'] . "'
                                                                        LIMIT 1
                                                                ");        
                                                        }
                                                }
                                                // #### set escrow account to cancelled
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "buynow_orders
                                                        SET status = 'cancelled',
                                                        amount = '0.00',
                                                        escrowfee = '0.00',
                                                        escrowfeebuyer = '0.00',
                                                        fvf = '0.00',
                                                        fvfbuyer = '0.00',
                                                        isescrowfeepaid = '0',
                                                        isescrowfeebuyerpaid = '0',
                                                        isfvfpaid = '0',
                                                        isfvfbuyerpaid = '0',
                                                        canceldate = '" . DATETIME24H . "'
                                                        WHERE orderid = '" . intval($escrowid) . "'
                                                                AND buyer_id = '" . intval($res_escrow['buyer_id']) . "'
                                                ");
                                                // #### increase qty back to auction listing
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects
                                                        SET buynow_qty = buynow_qty + " . $res_escrow['qty'] . "
                                                        WHERE project_id = '" . $res_escrow['project_id'] . "'
                                                ");
                                                // #### mark as unshipped (listing table)
                                                $ilance->shipping->mark_listing_as_unshipped($res_escrow['project_id'], $escrowid, $res_escrow['owner_id'], $res_escrow['buyer_id'], 'buynow');
                                                $lostfees = 'Seller FVF: ' . $ilance->currency->format($fvfselleramount) . ', Seller Escrow Fee: ' . $ilance->currency->format($escrowfeeselleramount) . ', Buyer Escrow Fee: ' . $ilance->currency->format($escrowfeebuyeramount) . ', Total Loss: ' . $ilance->currency->format($fvfselleramount + $escrowfeeselleramount + $escrowfeebuyeramount);
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['buyer_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['owner_id']),
                                                        '{{project_title}}' => stripslashes(fetch_auction('project_title', $res_escrow['project_id'])),
                                                        '{{escrowamount}}' => $ilance->currency->format($escrowamount, $currencyid),
                                                        '{{escrowfeeamount}}' => $ilance->currency->format($escrowfeeselleramount),
                                                        '{{fvfamount}}' => $ilance->currency->format($fvfselleramount),
                                                        '{{lostfees}}' => $lostfees
                                                );
                                                $ilance->email->mail = fetch_user('email', $res_escrow['buyer_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['buyer_id']);
                                                $ilance->email->get('purchase_now_delivery_cancelled_buyer');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', $res_escrow['owner_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['owner_id']);
                                                $ilance->email->get('purchase_now_delivery_cancelled_merchant');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('purchase_now_delivery_cancelled_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                // here we could also send email to any bidders previously that bid on this item
                                                return true;
                                        }
                                }
                                return false;        
                        }
                }
        }				
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>