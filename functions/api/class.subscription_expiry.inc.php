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

if (!class_exists('subscription'))
{
	exit;
}

/**
* Subscription expiry class to perform the majority of subscription expiration functionality in ILance.
*
* @package      iLance\Membership\Expiry
* @version      4.0.0.8059
* @author       ILance
*/
class subscription_expiry extends subscription
{
        /**
        * Function to expire user subscription plans if required (parsed from cron.subscriptions.php)
        */
        function user_subscription_plans()
        {
                global $ilance, $phrase, $ilconfig;
                
                $notice = $failedrenewalusernames = $noautopayrenewalusernames = $paidrenewalusernames = $freerenewalusernames = '';
                $failedrenewalcount = $noautopayrenewalcount = $paidrenewalcount = $freerenewalcount = 0;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng'; 
                
		// find all plans that have expired- don't include recurring subscriptions..
                $subscriptioncheck = $ilance->db->query("
                        SELECT u.*, s.id, s.subscriptionid, s.user_id, s.paymethod, s.startdate, s.renewdate, s.autopayment as subscription_autopayment, s.active, s.cancelled, s.migrateto, s.migratelogic, s.recurring, s.invoiceid, s.roleid, s.autorenewal
                        FROM " . DB_PREFIX . "subscription_user AS s,
                        " . DB_PREFIX . "users AS u
                        WHERE u.user_id = s.user_id
                            AND s.renewdate <= '" . DATETODAY . " " . TIMENOW . "'
                            AND s.cancelled = '0'
                            AND s.recurring = '0'
                            AND u.status = 'active'
                        GROUP BY u.user_id
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($subscriptioncheck) > 0)
                {
                	
			
                        while ($res_subscription_check = $ilance->db->fetch_array($subscriptioncheck, DB_ASSOC))
                        {
                                // #### AUTO SUBSCRIPTION MIGRATION ############
                                // did admin specify this subscription plan will migrate the user to another?
                                if ($res_subscription_check['migrateto'] > 0)
                                {
                                        $sql_subscription_plan = $ilance->db->query("
                                                SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                                FROM " . DB_PREFIX . "subscription
                                                WHERE subscriptionid = '" . $res_subscription_check['migrateto'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_subscription_plan) > 0)
                                        {
                                                $subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                                $sql_user = $ilance->db->query("
                                                        SELECT user_id, email, username
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '" . $res_subscription_check['user_id'] . "'
                                                        LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_user) > 0)
                                                {
                                                        $res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
                                                        switch ($res_subscription_check['migratelogic'])
                                                        {
								// no transaction will be created
                                                                case 'none':
                                                                {
                                                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                                SET active = 'yes',
                                                                                renewdate = '" . $subscription_renew_date . "',
                                                                                startdate = '" . DATETIME24H . "',
                                                                                subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
                                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                                invoiceid = '0'
                                                                                WHERE user_id = '" . $res_user['user_id'] . "'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $freerenewalusernames .= $res_user['username'] . ', ';
                                                                        $freerenewalcount++;
                                                                        break;
                                                                }                    
                                                                // insert waived transaction & activate new subscription plan
								case 'waived':
                                                                {
                                                                        $renewed_invoice_id = $ilance->accounting->insert_transaction(
                                                                                intval($res_subscription_check['subscriptionid']),
                                                                                0,
                                                                                0,
                                                                                intval($res_user['user_id']),
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                '{_subscription_payment_for} ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                                '0.00',
                                                                                '0.00',
                                                                                'paid',
                                                                                'subscription',
                                                                                $res_subscription_check['paymethod'],
                                                                                DATETIME24H,
                                                                                DATEINVOICEDUE,
                                                                                DATETIME24H,
                                                                                '{_subscription_plan_migrated_to} ' . $subscription_plan_result['title'],
                                                                                0,
                                                                                0,
                                                                                1
                                                                        );
                                                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                                SET active = 'yes',
                                                                                renewdate = '" . $subscription_renew_date . "',
                                                                                startdate = '" . DATETIME24H . "',
                                                                                subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
                                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                                invoiceid = '" . $renewed_invoice_id."'
                                                                                WHERE user_id = '" . $res_user['user_id']."'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $freerenewalusernames .= $res_user['username'] . ', ';
                                                                        $freerenewalcount++;
                                                                        break;
                                                                }                    
                                                                // insert unpaid transaction & deactivate new subscription plan
								case 'unpaid':
                                                                {
									if ($res_subscription_check['active'] == 'yes')
									{
										// customer may log-in and make payment via online account
										$renewed_invoice_id = $ilance->accounting->insert_transaction(
											$res_subscription_check['subscriptionid'],
											0,
											0,
											$res_user['user_id'],
											0,
											0,
											0,
											'{_subscription_payment_for} ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
											sprintf("%01.2f", $subscription_plan_result['cost']),
											'',
											'scheduled',
											'subscription',
											$res_subscription_check['paymethod'],
											DATETIME24H,
											DATEINVOICEDUE,
											'',
											'{_subscription_plan_migrated_to} ' . $subscription_plan_result['title'],
											0,
											0,
											1
										);
										// update subscription table
										$ilance->db->query("
											UPDATE " . DB_PREFIX . "subscription_user
											SET active = 'no',
											subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
											migrateto = '" . $subscription_plan_result['migrateto'] . "',
											migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
											invoiceid = '" . $renewed_invoice_id . "'
											WHERE user_id = '" . $res_user['user_id'] . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										// log subscription email for today so we do not resend
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "subscriptionlog
											(subscriptionlogid, user_id, date_sent)
											VALUES(
											NULL,
											'" . $res_user['user_id'] . "',
											'" . DATETODAY . "')
										", 0, null, __FILE__, __LINE__);
										// insert subscription invoice reminder so we don't resend again today
										$dateremind = $ilance->datetimes->fetch_date_fromnow($ilconfig['invoicesystem_daysafterfirstreminder']);
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoicelog
											(invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
											VALUES(
											NULL,
											'" . $res_subscription_check['user_id'] . "',
											'" . $renewed_invoice_id . "',
											'subscription',
											'" . DATETODAY . "',
											'" . $dateremind . "')
										", 0, null, __FILE__, __LINE__);
									}
                                                                        $failedrenewalusernames .= $res_user['username'] . ', ';
                                                                        $failedrenewalcount++;
                                                                        break;
                                                                }                    
                                                                // create paid transaction
								case 'paid':
                                                                {
                                                                        $renewed_invoice_id = $ilance->accounting->insert_transaction(
                                                                                intval($res_subscription_check['subscriptionid']),
                                                                                0,
                                                                                0,
                                                                                intval($res_user['user_id']),
                                                                                0,
                                                                                0,
                                                                                0,
                                                                                '{_subscription_payment_for} ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                                sprintf("%01.2f", $subscription_plan_result['cost']),
                                                                                sprintf("%01.2f", $subscription_plan_result['cost']),
                                                                                'paid',
                                                                                'subscription',
                                                                                $res_subscription_check['paymethod'],
                                                                                DATETIME24H,
                                                                                DATEINVOICEDUE,
                                                                                DATETIME24H,
                                                                                '{_subscription_plan_migrated_to} ' . $subscription_plan_result['title'],
                                                                                0,
                                                                                0,
                                                                                1
                                                                        );
                                                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                                SET active = 'yes',
                                                                                renewdate = '" . $subscription_renew_date . "',
                                                                                startdate = '" . DATETIME24H . "',
                                                                                subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
                                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                                invoiceid = '" . $renewed_invoice_id . "'
                                                                                WHERE user_id = '" . $res_user['user_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $paidrenewalusernames .= $res_user['username'] . ', ';
                                                                        $paidrenewalcount++;
                                                                        break;
                                                                }
                                                        }
                                                        if ($res_subscription_check['migratelogic'] != 'none' AND $res_subscription_check['active'] == 'yes')
                                                        {
								// obtain any unpaid subscription migration invoice
								$sql_new_invoice = $ilance->db->query("
									SELECT amount, invoiceid, description
									FROM " . DB_PREFIX . "invoices
									WHERE invoiceid = '" . intval($renewed_invoice_id) . "'
										AND (status = 'unpaid' OR status = 'scheduled')
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								if ($ilance->db->num_rows($sql_new_invoice) > 0)
								{
									$res_new_invoice = $ilance->db->fetch_array($sql_new_invoice, DB_ASSOC);
									if ($res_subscription_check['subscription_autopayment'] == '1')
									{
										// subscription log > did we already sent an email to this customer?
										$senttoday = $ilance->db->query("
											SELECT subscriptionlogid
											FROM " . DB_PREFIX . "subscriptionlog
											WHERE user_id = '" . $res_user['user_id'] . "'
											    AND date_sent = '" . DATETODAY . "'
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($senttoday) == 0)
										{
											// log subscription email for today and send email to customer
											$ilance->db->query("
												INSERT INTO " . DB_PREFIX . "subscriptionlog
												(subscriptionlogid, user_id, date_sent)
												VALUES(
												NULL,
												'" . $res_user['user_id'] . "',
												'" . DATETODAY . "')
											", 0, null, __FILE__, __LINE__);
											// subscription renewal via online account balance
											$sq1_account_balance = $ilance->db->query("
												SELECT available_balance, total_balance
												FROM " . DB_PREFIX . "users
												WHERE user_id = '" . $res_user['user_id'] . "'
											", 0, null, __FILE__, __LINE__);
											if ($ilance->db->num_rows($sq1_account_balance) > 0)
											{
												$get_account_array = $ilance->db->fetch_array($sq1_account_balance, DB_ASSOC);
												if ($get_account_array['available_balance'] >= $res_new_invoice['amount'])
												{
													$now_total = $get_account_array['total_balance'];
													$now_avail = $get_account_array['available_balance'];
													$new_total = ($now_total - $res_new_invoice['amount']);
													$new_avail = ($now_avail - $res_new_invoice['amount']);
													// re-adjust customers online account balance (minus subscription fee amount)
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "users
														SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
														total_balance = '" . sprintf("%01.2f", $new_total) . "'
														WHERE user_id = '" . $res_user['user_id'] . "'
													", 0, null, __FILE__, __LINE__);
													// pay existing subscription invoice via online account
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "invoices
														SET status = 'paid',
														paid = '" . sprintf("%01.2f", $res_new_invoice['amount']) . "',
														paiddate = '" . DATETIME24H . "'
														WHERE user_id = '" . $res_user['user_id'] . "'
														    AND invoiceid = '" . $res_new_invoice['invoiceid'] . "'
													", 0, null, __FILE__, __LINE__);
													// adjust members total amount received for referral payments from admin
													$ilance->accounting_payment->insert_income_reported($res_user['user_id'], sprintf("%01.2f", $res_new_invoice['amount']), 'credit');
													// update customer subscription table with new subscription information
													$subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
													$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
													// update subscription table
													$ilance->db->query("
														UPDATE " . DB_PREFIX . "subscription_user
														SET active = 'yes',
														renewdate = '" . $subscription_renew_date . "',
														startdate = '" . DATETIME24H . "',
														subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
														migrateto = '" . $subscription_plan_result['migrateto'] . "',
														migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
														invoiceid = '" . $res_new_invoice['invoiceid'] . "'
														WHERE user_id = '" . $res_user['user_id'] . "'
														LIMIT 1
													", 0, null, __FILE__, __LINE__);
													$ilance->email->mail = $res_user['email'];
													$ilance->email->slng = fetch_user_slng($res_user['user_id']);
													$ilance->email->get('subscription_payment_renewed');		
													$ilance->email->set(array(
														'{{customer}}' => $res_user['username'],
														'{{amount}}' => $ilance->currency->format($res_new_invoice['amount']),
														'{{description}}' => $res_new_invoice['description'],
													));
													$ilance->email->send();
													$paidrenewalusernames .= $res_user['username'] . ', ';
													$paidrenewalcount++;  
												}
											}
										}                                                        
									}
								}
                                                        }
                                                }
                                        }
                                }
                                // #### REGULAR SUBSCRIPTION RENEWAL [NO AUTO-MIGRATION] #######
                                else
                                {
                                        $sql_user = $ilance->db->query("
                                                SELECT first_name, last_name, username, email, user_id
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $res_subscription_check['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_user) > 0)
                                        {
                                                $res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
                                                $ilance->subscription_plan->deactivate_subscription_plan($res_subscription_check['user_id']);
                                                if ($res_subscription_check['autorenewal'] > 0)
                                                {
							// obtain customer subscription plan information
							$sql_subscription_plan = $ilance->db->query("
								SELECT cost, title_" . $slng . " AS title, length, units, migrateto, migratelogic, subscriptionid
								FROM " . DB_PREFIX . "subscription
								WHERE subscriptionid = '" . $res_subscription_check['subscriptionid'] . "'
							", 0, null, __FILE__, __LINE__);
							if ($ilance->db->num_rows($sql_subscription_plan) > 0)
							{
								$subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan, DB_ASSOC);
								// if the subscription plan's cost is free, auto-renew subscription for this user
								if ($subscription_plan_result['cost'] > 0)
								{
									$senttoday = $ilance->db->query("
										SELECT user_id
										FROM " . DB_PREFIX . "subscriptionlog
										WHERE user_id = '" . $res_subscription_check['user_id'] . "'
										    AND date_sent = '" . DATETODAY . "'
									", 0, null, __FILE__, __LINE__);
									if ($ilance->db->num_rows($senttoday) == 0)
									{
										// log subscription email for today and send email to customer
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "subscriptionlog
											(subscriptionlogid, user_id, date_sent)
											VALUES(
											NULL,
											'" . $res_subscription_check['user_id'] . "',
											'" . DATETODAY . "')
										", 0, null, __FILE__, __LINE__);
										// do we already have a scheduled subscription invoice for this customer?
										$sqlpaidchk = $ilance->db->query("
											SELECT invoiceid
											FROM " . DB_PREFIX . "invoices
											WHERE user_id = '" . $res_user['user_id'] . "'
												AND subscriptionid = '" . $res_subscription_check['subscriptionid'] . "'
												AND (status = 'scheduled' OR status = 'unpaid')
												AND invoicetype = 'subscription'
												AND (paid = '0.00' OR paid = '' OR paid = '0')
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sqlpaidchk) > 0)
										{
											// yes customer already has pending subscription transaction associated to this subscription id so use this instead
											$respaid = $ilance->db->fetch_array($sqlpaidchk, DB_ASSOC);
											$renewed_invoice_id = $respaid['invoiceid'];
										}
										else
										{
											$renewed_invoice_id = $ilance->accounting->insert_transaction(
												intval($res_subscription_check['subscriptionid']),
												0,
												0,
												intval($res_user['user_id']),
												0,
												0,
												0,
												'{_subscription_payment_for}' . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
												sprintf("%01.2f", $subscription_plan_result['cost']),
												'',
												'scheduled',
												'subscription',
												$res_subscription_check['paymethod'],
												DATETIME24H,
												DATEINVOICEDUE,
												'',
												'',
												0,
												0,
												1
											);
										}
										// insert subscription invoice reminder so we don't resend again today
										$dateremind = $ilance->datetimes->fetch_date_fromnow($ilconfig['invoicesystem_daysafterfirstreminder']);
										$ilance->db->query("
											INSERT INTO " . DB_PREFIX . "invoicelog
											(invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
											VALUES(
											NULL,
											'" . $res_subscription_check['user_id'] . "',
											'" . intval($renewed_invoice_id) . "',
											'subscription',
											'" . DATETODAY . "',
											'" . $dateremind . "')
										", 0, null, __FILE__, __LINE__);
										// obtain invoice information once again
										$sql_new_invoice = $ilance->db->query("
											SELECT totalamount, invoiceid, amount, description
											FROM " . DB_PREFIX . "invoices
											WHERE invoiceid = '" . intval($renewed_invoice_id) . "'
											LIMIT 1
										", 0, null, __FILE__, __LINE__);
										if ($ilance->db->num_rows($sql_new_invoice) > 0)
										{
											$res_new_invoice = $ilance->db->fetch_array($sql_new_invoice, DB_ASSOC);
											// auto-payments checkup (user sets this option via subscription menu)
											if ($res_subscription_check['subscription_autopayment'] == '1')
											{
												// subscription renewal via online account balance
												$sq1_account_balance = $ilance->db->query("
													SELECT available_balance, total_balance
													FROM " . DB_PREFIX . "users
													WHERE user_id = '" . $res_user['user_id'] . "'
												", 0, null, __FILE__, __LINE__);
												if ($ilance->db->num_rows($sq1_account_balance) > 0)
												{
													$get_account_array = $ilance->db->fetch_array($sq1_account_balance, DB_ASSOC);
													// #### ONLINE ACCOUNT BALANCE CHECK UP ####################################
													if ($get_account_array['available_balance'] >= $res_new_invoice['totalamount'])
													{
														$now_total = $get_account_array['total_balance'];
														$now_avail = $get_account_array['available_balance'];
														$new_total = ($now_total - $res_new_invoice['totalamount']);
														$new_avail = ($now_avail - $res_new_invoice['totalamount']);
														// re-adjust customers online account balance (minus subscription fee amount)
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "users
															SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
															total_balance = '" . sprintf("%01.2f", $new_total) . "'
															WHERE user_id = '" . $res_user['user_id'] . "'
														", 0, null, __FILE__, __LINE__);
														// pay existing subscription invoice via online account
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "invoices
															SET status = 'paid',
															paid = '" . sprintf("%01.2f", $res_new_invoice['totalamount']) . "',
															paiddate = '" . DATETIME24H . "'
															WHERE user_id = '" . $res_user['user_id'] . "'
															    AND invoiceid = '" . $res_new_invoice['invoiceid'] . "'
														", 0, null, __FILE__, __LINE__);
														// record spending habits for this user
														$ilance->accounting_payment->insert_income_spent($res_user['user_id'], sprintf("%01.2f", $res_new_invoice['totalamount']), 'credit');
														// update customer subscription table with new subscription information
														$subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
														$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
														$ilance->db->query("
															UPDATE " . DB_PREFIX . "subscription_user
															SET active = 'yes',
															renewdate = '" . $subscription_renew_date . "',
															startdate = '" . DATETIME24H . "',
															migrateto = '" . $subscription_plan_result['migrateto'] . "',
															migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
															invoiceid = '" . $res_new_invoice['invoiceid'] . "'
															WHERE user_id = '" . $res_user['user_id'] . "'
															    AND subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "'
														", 0, null, __FILE__, __LINE__);
														
														$ilance->email->mail = $res_user['email'];
														$ilance->email->slng = fetch_user_slng($res_user['user_id']);
														$ilance->email->get('subscription_payment_renewed');		
														$ilance->email->set(array(
															'{{customer}}' => $res_user['username'],
															'{{amount}}' => $ilance->currency->format($res_new_invoice['amount']),
															'{{description}}' => $res_new_invoice['description'],
														));
														$ilance->email->send();
														$paidrenewalusernames .= $res_user['username'] . ', ';
														$paidrenewalcount++;                                                                                        
													}
												}                                                                        
											}
										}
									}    
								}
								// create waived transaction
								else
								{
									$renewed_invoice_id = $ilance->accounting->insert_transaction(
										intval($res_subscription_check['subscriptionid']),
										0,
										0,
										intval($res_user['user_id']),
										0,
										0,
										0,
										'{_subscription_payment_for}' . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
										'0.00',
										'0.00',
										'paid',
										'subscription',
										'account',
										DATETIME24H,
										DATEINVOICEDUE,
										DATETIME24H,
										'{_subscription_plan_was_renewed}',
										0,
										0,
										1
									);
									// update subscription table
									$subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
									$subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
									$ilance->db->query("
										UPDATE " . DB_PREFIX . "subscription_user
										SET active = 'yes',
										renewdate = '" . $subscription_renew_date . "',
										startdate = '" . DATETIME24H . "',
										subscriptionid = '" . $subscription_plan_result['subscriptionid'] . "',
										migrateto = '" . $subscription_plan_result['migrateto'] . "',
										migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
										invoiceid = '" . $renewed_invoice_id . "'
										WHERE user_id = '" . $res_user['user_id'] . "'
										LIMIT 1
									", 0, null, __FILE__, __LINE__);
									$freerenewalusernames .= $res_subscription_check['username'] . ', ';
									$freerenewalcount++;
								}
							}
                                    		}   
	                                }
                                }
                        }
                        if (!empty($paidrenewalusernames))
                        {
                                $paidrenewalusernames = mb_substr($paidrenewalusernames, 0, -2);
                        }
                        else
                        {
                                $paidrenewalusernames = 'None';
                        }
                        $notice .= "Renewed $paidrenewalcount paid subscription plans for the following users: $paidrenewalusernames. ";
                        if (!empty($freerenewalusernames))
                        {
                                $freerenewalusernames = mb_substr($freerenewalusernames, 0, -2);
                        }
                        else
                        {
                                $freerenewalusernames = 'None';
                        }
                        $notice .= "Renewed $freerenewalcount free subscription plans for the following users: $freerenewalusernames. ";
                }
                else
                {
                        $notice .= "No user subscription plans to expire at this time.";    
                }
                return $notice;
        }
        
        /**
        * Function to expire user subscription plan exemptions (parsed from cron.subscriptions.php)
        */
        function user_subscription_exemptions()
        {
                global $ilance, $phrase, $ilconfig;

                $notice = $expiredusernames = '';
                $expiredexemptions = 0;
                $exemptionscheck = $ilance->db->query("
                        SELECT exemptid, user_id, accessname, value, exemptfrom, exemptto, comments, invoiceid, active
                        FROM " . DB_PREFIX . "subscription_user_exempt
                        WHERE exemptto <= '" . DATETODAY . " " . TIMENOW . "'
                            AND active = '1'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($exemptionscheck) > 0)
                {        
                        while ($exemptions = $ilance->db->fetch_array($exemptionscheck, DB_ASSOC))
                        {
                                // expire subscription exemptions
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user_exempt
                                        SET active = '0'
                                        WHERE exemptid = '" . $exemptions['exemptid'] . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                $expiredusernames .= fetch_user('username', $exemptions['user_id']) . ', ';
                                $expiredexemptions++;
                                // send email to notify about subscription permission exemption expiry and renewal details
                                // >> this will be added at a later date <<
                        }
                        if (!empty($expiredusernames))
                        {
                                $expiredusernames = mb_substr($expiredusernames, 0, -2);
                        }
                        else
                        {
                                $expiredusernames = 'None';
                        }
                        $notice .= "Expired $expiredexemptions subscription exemptions for the following users: $expiredusernames. ";
                }
                return $notice;
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>