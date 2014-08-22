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
* Function to handle escrow payments
*
* @package      iLance\Escrow\Payment
* @version      4.0.0.8059
* @author       ILance
*/
class escrow_payment extends escrow
{
	/**
        * Function to process an escrow payment for a particular user for a specific invoice id
        *
        * @param       integer      user id
        * @param       integer      invoice id
        * @param       string       invoice type
        * @param       integer      invoice amount
        * @param       string       payment method
        * @param       string       payment gateway to use
        * @param       string       payment gateway txn
        * @param       boolean      is refunded payment? (default false)
        * @param       string       gateway original transaction id (if payment is refunded by gateway)
        * @param       boolean      silent mode (return only true or false; default false)
        *
        * @return      bool         Returns true or false if reversal could be completed
        */
        function payment($userid = 0, $invoiceid = 0, $invoicetype = 'escrow', $amount = 0, $method = 'account', $gateway = '', $gatewaytxn = '', $isrefund = false, $originalgatewaytxn = '', $silentmode = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                // #### IPN MODE ###############################################
                if ($method == 'ipn')
                {
                        if ($ilconfig['escrowsystem_enabled'] == 0)
                        {
                                return false;
                        }
                        $sql_invoice = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($invoiceid) . "'
					AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_invoice) > 0)
                        {
                                $res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
                                $userid = $res_invoice['user_id'];
                                $sql_escrow = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "projects_escrow
                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                            AND status = 'pending'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_escrow) > 0)
                                {
                                        $res_escrow = $ilance->db->fetch_array($sql_escrow, DB_ASSOC);
                                        // #### SERVICE BUYER FUNDING ESCROW ACCOUNT #######
                                        // first of all, if we are the buyer (owner) of a service auction,
                                        // let's determine if we had any fees associated with this escrow deposit..
                                        // if we do, generate a paid invoice showing the fee as complete just to allow
                                        // admin and user to review from transaction history
                                        if ($res_escrow['project_user_id'] == $userid)
                                        {
                                                // we are the actual service buyer (owner of auction) funding our escrow account
                                                // buyer escrow fees
                                                $fee = 0;
                                                $bidamount = $res_escrow['total'];
                                                if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $res_escrow['feeinvoiceid'] == 0)
                                                {
                                                        // escrow commission fees to auction owner enabled
                                                        // find out how the admin charges and generate unpaid invoice
                                                        if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                                                        {
                                                                // fixed escrow cost to buyer
                                                                $fee = $ilconfig['escrowsystem_servicebuyerfixedprice'];
                                                        }
                                                        else
                                                        {
                                                                if ($ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
                                                                {
                                                                        // percentage rate of total winning bid amount
                                                                        // which would be the same as the amount being forwarded into escrow
                                                                        $fee = ($bidamount * $ilconfig['escrowsystem_servicebuyerpercentrate'] / 100);
                                                                }
                                                        }
                                                        if ($fee > 0)
                                                        {
                                                                // generate paid escrow transaction to the buyer of the project
                                                                // this will allow the admin and buyer to see the commission fee invoice
                                                                // separate for this escrow transaction and since we've already applied
                                                                // any applicable taxes, we'll send a 0 to the ending of this accounting
                                                                // function to disable any tax support
                                                                $taxamount = 0;
                                                                $istaxable = '0';
                                                                $taxinfo = '';
                                                                if ($ilance->tax->is_taxable($res_escrow['project_user_id'], 'commission'))
                                                                {
                                                                        // fetch tax amount to charge for this invoice type
                                                                        $taxamount = $ilance->tax->fetch_amount($res_escrow['project_user_id'], $fee, 'commission', 0);
                                                                        $taxinfo = $ilance->tax->fetch_amount($res_escrow['project_user_id'], $fee, 'commission', 1);
                                                                        $istaxable = '1';
                                                                }
                                                                // exact amount to charge buyer
                                                                $escrowfeenotax = $fee;
                                                                $escrowfee = sprintf("%01.2f", ($fee + $taxamount));
                                                                $availablebalance = fetch_user('available_balance', $res_escrow['project_user_id']);
                                                                $totalbalance = fetch_user('total_balance', $res_escrow['project_user_id']);
                                                                $autopayment = fetch_user('autopayment', $res_escrow['project_user_id']);
                                                                if ($availablebalance >= $escrowfee AND $autopayment)
                                                                {
                                                                        // generate paid escrow fee invoice to the buyer of the project
                                                                        // this will allow the admin and buyer to see the commission fee invoice
                                                                        // separate for this escrow transaction and since we've already applied
                                                                        // any applicable taxes, we'll send a 0 to the ending of this accounting
                                                                        // function to disable any tax support
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                (invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res_escrow['project_id'] . "',
                                                                                '" . $res_escrow['project_user_id'] . "',
                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $istaxable . "',
                                                                                '" . $ilance->db->escape_string($taxamount) . "',
                                                                                '" . $ilance->db->escape_string($taxinfo) . "',
                                                                                'paid',
                                                                                'debit',
                                                                                '" . $ilance->db->escape_string($gateway) . "',
                                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
                                                                                '" . $gatewaytxn . "',
                                                                                '1')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                        $isfeepaid = 1;
                                                                        // additionally we'll track this fee payment for the total spending amount for the user
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['project_user_id'], sprintf("%01.2f", $escrowfee), 'credit');
                                                                }
                                                                else
                                                                {
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res_escrow['project_id'] . "',
                                                                                '" . $res_escrow['project_user_id'] . "',
                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $istaxable . "',
                                                                                '" . $ilance->db->escape_string($taxamount) . "',
                                                                                '" . $ilance->db->escape_string($taxinfo) . "',
                                                                                'unpaid',
                                                                                'debit',
                                                                                '" . $ilance->db->escape_string($gateway) . "',
                                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
                                                                                '" . $gatewaytxn . "',
                                                                                '1')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                        $isfeepaid = 0;
                                                                }
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET isescrowfee = '1'
                                                                        WHERE invoiceid = '" . intval($escrowfee_invoice_id) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET isfeepaid = '" . $isfeepaid . "',
                                                                        feeinvoiceid = '" . $escrowfee_invoice_id . "',
                                                                        fee = '" . $ilance->db->escape_string($escrowfee) . "'
                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // because we've inserted a separate fee for the escrow transaction
                                                                // let's set our $amount to the bid amount value only
                                                                // since both members will have access to this transaction
                                                                // and it would appear confusing to the "provider" showing
                                                                // total including the buyer fees.
                                                                // (however the buyer will see his fee as a separate transaction)
                                                                $amount = $res_escrow['bidamount'];
                                                        }
                                                }
                                        }
                                        // #### PRODUCT BIDDER FUNDING ESCROW ACCOUNT BALANCE ######
                                        else if ($res_escrow['user_id'] == $userid)
                                        {
                                                // we are the actual product bidder (winner of auction) funding our escrow account
                                                // bidder escrow fees
                                                $fee = 0;
                                                $bidamount = $res_escrow['total'];
                                                if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $res_escrow['fee2invoiceid'] == 0)
                                                {
                                                        // escrow commission fees to bidder enabled
                                                        // find out how the admin charges and generate unpaid invoice
                                                        if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                                                        {
                                                                // fixed escrow cost to buyer
                                                                $fee = $ilconfig['escrowsystem_bidderfixedprice'];
                                                        }
                                                        else
                                                        {
                                                                if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
                                                                {
                                                                        // percentage rate of total winning bid amount
                                                                        // which would be the same as the amount being forwarded into escrow
                                                                        $fee = ($bidamount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
                                                                }
                                                        }
                                                        if ($fee > 0)
                                                        {
                                                                $taxamount = 0;
                                                                $istaxable = '0';
                                                                $taxinfo = '';
                                                                if ($ilance->tax->is_taxable($res_escrow['user_id'], 'commission'))
                                                                {
                                                                        // fetch tax amount to charge for this invoice type
                                                                        $taxamount = $ilance->tax->fetch_amount($res_escrow['user_id'], $fee, 'commission', 0);
                                                                        $taxinfo = $ilance->tax->fetch_amount($res_escrow['user_id'], $fee, 'commission', 1);
                                                                        $istaxable = '1';
                                                                }
                                                                // exact amount to charge buyer
                                                                $escrowfeenotax = $fee;
                                                                $escrowfee = sprintf("%01.2f", ($fee + $taxamount));
                                                                $availablebalance = fetch_user('available_balance', $res_escrow['user_id']);
                                                                $totalbalance = fetch_user('total_balance', $res_escrow['user_id']);
                                                                $autopayment = fetch_user('autopayment', $res_escrow['user_id']);
                                                                if ($availablebalance >= $escrowfee AND $autopayment)
                                                                {
                                                                        // generate paid escrow fee invoice to the winner of the auction
                                                                        // this will allow the admin and winner to see the commission fee invoice
                                                                        // separate for this escrow transaction and since we've already applied
                                                                        // any applicable taxes, we'll send a 0 to the ending of this accounting
                                                                        // function to disable any tax support
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                (invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res_escrow['project_id'] . "',
                                                                                '" . $res_escrow['user_id'] . "',
                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $istaxable . "',
                                                                                '" . $ilance->db->escape_string($taxamount) . "',
                                                                                '" . $ilance->db->escape_string($taxinfo) . "',
                                                                                'paid',
                                                                                'debit',
                                                                                '" . $ilance->db->escape_string($gateway) . "',
                                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
                                                                                '" . $ilance->db->escape_string($gatewaytxn) . "',
                                                                                '1')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                        $isfee2paid = 1;
                                                                        // additionally we'll track this fee payment for the total spending amount for the user
                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['user_id'], sprintf("%01.2f", $escrowfee), 'credit');
                                                                }
                                                                else
                                                                {
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                                VALUES(
                                                                                NULL,
                                                                                '" . $res_escrow['project_id'] . "',
                                                                                '" . $res_escrow['user_id'] . "',
                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                '" . $istaxable . "',
                                                                                '" . $ilance->db->escape_string($taxamount) . "',
                                                                                '" . $ilance->db->escape_string($taxinfo) . "',
                                                                                'unpaid',
                                                                                'debit',
                                                                                '" . $ilance->db->escape_string($gateway) . "',
                                                                                '" . $ilance->db->escape_string($_SERVER['REMOTE_ADDR']) . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . DATETIME24H . "',
                                                                                '" . $ilance->db->escape_string('{_may_include_applicable_taxes}') . "',
                                                                                '" . $ilance->db->escape_string($gatewaytxn) . "',
                                                                                '1')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                        $isfee2paid = 0;        
                                                                }
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                        SET isescrowfee = '1'
                                                                        WHERE invoiceid = '" . intval($escrowfee_invoice_id) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET isfee2paid = '" . $isfee2paid . "',
                                                                        fee2invoiceid = '" . $escrowfee_invoice_id . "',
                                                                        fee2 = '" . $ilance->db->escape_string($escrowfee) . "'
                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                // because we've inserted a separate fee for the escrow transaction
                                                                // let's set our $amount to the bid amount + shipping value only
                                                                // since both members will have access to this escrow transaction
                                                                // and it would appear confusing to the "merchant" showing
                                                                // total including the winning bidders fees.
                                                                // (however the bidder will see his fee as a separate transaction)
                                                                $amount = ($res_escrow['bidamount'] + $res_escrow['shipping']);
                                                        }
                                                }
                                        }
                                        // because we've generated a separate transaction fee for the bidder above (if applicable)
                                        // let's adjust the main escrow transaction to amounts that make sense to both
                                        // parties while viewing the transaction.  ultimately, the bidder will have another
                                        // separate transaction to view the fee and related taxes
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET paid = '" . $ilance->db->escape_string($amount) . "',
                                                amount = '" . $ilance->db->escape_string($amount) . "',
                                                totalamount = '" . $ilance->db->escape_string($amount) . "',
                                                status = 'paid',
                                                paiddate = '" . DATETIME24H . "',
                                                paymethod = '" . $ilance->db->escape_string($gateway) . "',
                                                custommessage = '" . $ilance->db->escape_string($gatewaytxn) . "'
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                        AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        // track income spent
                                        $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                        $escrowprojectid = $ilance->db->fetch_field(DB_PREFIX."projects_escrow", "escrow_id = '" . $res_escrow['escrow_id'] . "'", "project_id");
                                        if (fetch_auction('project_state', $escrowprojectid) == 'service')
                                        {
                                                // remember - we update the "amount in escrow" as the original bid amount
                                                // since the buyer had to pay the full amount + fee so if this was for example
                                                // an awarded bid for $20.00 and the fee was $5.00 the amount the buyer pays is $25.00
                                                // however the amount that the held in escrow is $20.00 to the provider
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                        SET escrowamount = '" . $ilance->db->escape_string($amount) . "',
                                                        status = 'confirmed',
                                                        date_paid = '" . DATETIME24H . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
								AND escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
						$currencyid = intval(fetch_auction('currencyid', $escrowprojectid));
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{invoice_id}}' => intval($invoiceid),
							'{{paymethod}}' => $gateway,
							'{{gateway}}' => $gateway,
							'{{project_title}}' => fetch_auction('project_title', $escrowprojectid),
							'{{winner}}' => fetch_user('username', intval($userid)),
							'{{owner}}' => fetch_user('username', intval($res_escrow['user_id'])),
							'{{amount}}' => $ilance->currency->format($amount, $currencyid),
                                                );
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('escrow_payment_forward_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', intval($userid));
                                                $ilance->email->slng = fetch_user_slng($userid);
                                                $ilance->email->get('escrow_payment_forward_provider');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                $ilance->email->get('escrow_payment_forward_buyer');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                        }
                                        else if (fetch_auction('project_state', $escrowprojectid) == 'product')
                                        {
						$currencyid = intval(fetch_auction('currencyid', $escrowprojectid));
						$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
						if ($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
						{
							$escrow_amount = convert_currency($default_currency, $res_escrow['bidamount'] + $res_escrow['shipping'], $currencyid);
							$currencyid = $default_currency;
						}
						else 
						{
							$escrow_amount = $res_escrow['bidamount'] + $res_escrow['shipping'];
						}
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                        SET escrowamount = '" . ($amount + $res_escrow['shipping']) . "',
                                                        status = 'started',
                                                        date_paid = '" . DATETIME24H . "'
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                                AND escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
						$ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "project_bids
                                                        SET winnermarkedaspaid = '1',
							winnermarkedaspaiddate = '" . DATETIME24H . "',
							winnermarkedaspaidmethod = '" . $ilance->db->escape_string($gateway) . "'
                                                        WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
                                                ", 0, null, __FILE__, __LINE__);
						$ilance->db->query("
							UPDATE " . DB_PREFIX . "project_realtimebids
							SET winnermarkedaspaid = '1',
							winnermarkedaspaiddate = '" . DATETIME24H . "',
							winnermarkedaspaidmethod = '" . $ilance->db->escape_string($gateway) . "'
							WHERE bid_id = '" . $res_escrow['bid_id'] . "'
							LIMIT 1
						", 0, null, __FILE__, __LINE__);
                                                $existing = array(
                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                        '{{invoice_id}}' => intval($invoiceid),
                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                        '{{project_title}}' => fetch_auction('project_title', $res_escrow['project_id']),
                                                        '{{escrow_amount}}' => $ilance->currency->format(($escrow_amount), $currencyid),
														'{{paymethod}}' => $gateway
                                                );
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('product_escrow_payment_foward_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                $ilance->email->slng = fetch_user_slng($userid);
                                                $ilance->email->get('product_escrow_payment_forward_merchant');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                $ilance->email->get('product_escrow_payment_forward_bidder');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                        }
                                        return true;
                                }
                        }
                        return false;
                }
                // #### ACCOUNT BALANCE MODE ###################################
                else if ($method == 'account')
                {
                        $sql_balance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_balance) > 0)
                        {
                                $res_balance = $ilance->db->fetch_array($sql_balance, DB_ASSOC);
                                if ($res_balance['available_balance'] >= $amount)
                                {
                                        $avail_balance = $res_balance['available_balance'];
                                        $total_balance = $res_balance['total_balance'];
                                        $avail_balance_after = ($avail_balance - $amount);
                                        $total_balance_after = ($total_balance - $amount);
                                        $sql_invoice = $ilance->db->query("
                                                SELECT *
                                                FROM " . DB_PREFIX . "invoices
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                    AND user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_invoice) > 0)
                                        {
                                                $res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
                                                $sql_escrow = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "projects_escrow
                                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                                            AND status = 'pending'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_escrow) > 0)
                                                {
                                                        $res_escrow = $ilance->db->fetch_array($sql_escrow, DB_ASSOC);
                                                        // #### SERVICE BUYER FUNDING ESCROW ACCOUNT #######
                                                        // first of all, if we are the buyer (owner) of a service auction,
                                                        // let's determine if we had any fees associated with this escrow deposit..
                                                        // if we do, generate a paid invoice showing the fee as complete just to allow
                                                        // admin and user to review from transaction history
                                                        if ($res_escrow['project_user_id'] == $userid)
                                                        {
                                                                // we are the actual service buyer (owner of auction) funding our escrow account
                                                                // buyer escrow fees
                                                                $fee = 0;
                                                                $currencyid = intval(fetch_auction('currencyid', $res_escrow['project_id']));
								$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
								if($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
								{
									$bidamount = convert_currency($default_currency, $res_escrow['total'], $currencyid);	
									$currencyid = $default_currency;
								}
								else 
								{
									$bidamount = $res_escrow['total'];
								}
                                                                if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $res_escrow['feeinvoiceid'] == 0)
                                                                {
                                                                        // escrow commission fees to auction owner enabled
                                                                        // find out how the admin charges and generate unpaid invoice
                                                                        if ($ilconfig['escrowsystem_servicebuyerfixedprice'] > 0)
                                                                        {
                                                                                // fixed escrow cost to buyer
                                                                                $fee = $ilconfig['escrowsystem_servicebuyerfixedprice'];
                                                                        }
                                                                        else
                                                                        {
                                                                                if ($ilconfig['escrowsystem_servicebuyerpercentrate'] > 0)
                                                                                {
                                                                                        // percentage rate of total winning bid amount
                                                                                        // which would be the same as the amount being forwarded into escrow
                                                                                        $fee = ($bidamount * $ilconfig['escrowsystem_servicebuyerpercentrate'] / 100);
                                                                                }
                                                                        }
                                                                        if ($fee > 0)
                                                                        {
                                                                                $taxamount = 0;
                                                                                $istaxable = '0';
                                                                                $taxinfo = '';
                                                                                if ($ilance->tax->is_taxable($res_escrow['project_user_id'], 'commission'))
                                                                                {
                                                                                        // fetch tax amount to charge for this invoice type
                                                                                        $taxamount = $ilance->tax->fetch_amount($res_escrow['project_user_id'], $fee, 'commission', 0);
                                                                                        $taxinfo = $ilance->tax->fetch_amount($res_escrow['project_user_id'], $fee, 'commission', 1);
                                                                                        $istaxable = '1';
                                                                                }
                                                                                // exact amount to charge buyer
                                                                                $escrowfeenotax = $fee;
                                                                                $escrowfee = sprintf("%01.2f", ($fee + $taxamount));
                                                                                // charge escrow fee to buyer as a separate transaction
                                                                                // this creates a transaction history item for the buyer of item
                                                                                $txn = $ilance->accounting_payment->construct_transaction_id();
                                                                                //$availablebalance = fetch_user('available_balance', $res_escrow['project_user_id']);
                                                                                //$totalbalance = fetch_user('total_balance', $res_escrow['project_user_id']);
                                                                                $autopayment = fetch_user('autopayment', $res_escrow['project_user_id']);
                                                                                if ($avail_balance_after >= $escrowfee AND $autopayment)
                                                                                {
                                                                                        // generate paid escrow fee invoice to the buyer of the project
                                                                                        // this will allow the admin and buyer to see the commission fee invoice
                                                                                        // separate for this escrow transaction and since we've already applied
                                                                                        // any applicable taxes, we'll send a 0 to the ending of this accounting
                                                                                        // function to disable any tax support
                                                                                        $ilance->db->query("
                                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                                (invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
                                                                                                VALUES(
                                                                                                NULL,
                                                                                                '" . $res_escrow['project_id'] . "',
                                                                                                '" . $res_escrow['project_user_id'] . "',
                                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                                '" . $istaxable . "',
                                                                                                '" . $ilance->db->escape_string($taxamount) . "',
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
                                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                                        $isfeepaid = 1;
                                                                                        $ilance->db->query("
												UPDATE " . DB_PREFIX . "users
												SET available_balance = available_balance - " . sprintf("%01.2f", $escrowfee) . ",
												total_balance = total_balance - " . sprintf("%01.2f", $escrowfee) . "
												WHERE user_id = '" . intval($res_escrow['project_user_id']) . "'
											", 0, null, __FILE__, __LINE__);
                                                                                        // additionally we'll track this fee payment for the total spending amount for the user
                                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['project_user_id'], sprintf("%01.2f", $escrowfee), 'credit');
                                                                                }
                                                                                else
                                                                                {
                                                                                        $ilance->db->query("
                                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                                (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                                                VALUES(
                                                                                                NULL,
                                                                                                '" . $res_escrow['project_id'] . "',
                                                                                                '" . $res_escrow['project_user_id'] . "',
                                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
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
                                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                                        $isfeepaid = 0;
                                                                                }
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                                        SET isescrowfee = '1'
                                                                                        WHERE invoiceid = '" . intval($escrowfee_invoice_id) . "'
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                                        SET isfeepaid = '" . $isfeepaid . "',
                                                                                        feeinvoiceid = '" . $escrowfee_invoice_id . "',
                                                                                        fee = '" . $ilance->db->escape_string($escrowfee) . "'
                                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                // because we've inserted a separate fee for the escrow transaction
                                                                                // let's set our $amount to the bid amount value only
                                                                                // since both members will have access to this transaction
                                                                                // and it would appear confusing to the "provider" showing
                                                                                // total including the buyer fees.
                                                                                // (however the buyer will see his fee as a separate transaction)
                                                                                //$amount = $res_escrow['bidamount'];
                                                                        }
                                                                }
                                                        }
                                                        // #### PRODUCT BIDDER FUNDING ESCROW ACCOUNT ######
                                                        else if ($res_escrow['user_id'] == $userid)
                                                        {
                                                                // we are the actual product bidder (winner of auction) funding our escrow account
                                                                // bidder escrow fees
                                                                $fee = 0;
                                                                $currencyid = intval(fetch_auction('currencyid', $res_escrow['project_id']));
								$default_currency = intval($ilconfig['globalserverlocale_defaultcurrency']);
								if($ilconfig['globalserverlocale_currencyselector'] AND $currencyid != $default_currency)
								{
									$bidamount = convert_currency($default_currency, $res_escrow['total'], $currencyid);	
									$currencyid = $default_currency;
								}
								else 
								{
									$bidamount = $res_escrow['total'];
								}
                                                                if ($ilconfig['escrowsystem_escrowcommissionfees'] AND $res_escrow['fee2invoiceid'] == 0)
                                                                {
                                                                        // escrow commission fees to bidder enabled
                                                                        // find out how the admin charges and generate unpaid invoice
                                                                        if ($ilconfig['escrowsystem_bidderfixedprice'] > 0)
                                                                        {
                                                                                // fixed escrow cost to buyer
                                                                                $fee = $ilconfig['escrowsystem_bidderfixedprice'];
                                                                        }
                                                                        else
                                                                        {
                                                                                if ($ilconfig['escrowsystem_bidderpercentrate'] > 0)
                                                                                {
                                                                                        // percentage rate of total winning bid amount
                                                                                        // which would be the same as the amount being forwarded into escrow
                                                                                        $fee = ($bidamount * $ilconfig['escrowsystem_bidderpercentrate'] / 100);
                                                                                }
                                                                        }
                                                                        if ($fee > 0)
                                                                        {
                                                                                $taxamount = 0;
                                                                                $istaxable = '0';
                                                                                $taxinfo = '';
                                                                                if ($ilance->tax->is_taxable($res_escrow['user_id'], 'commission'))
                                                                                {
                                                                                        // fetch tax amount to charge for this invoice type
                                                                                        $taxamount = $ilance->tax->fetch_amount($res_escrow['user_id'], $fee, 'commission', 0);
                                                                                        $taxinfo = $ilance->tax->fetch_amount($res_escrow['user_id'], $fee, 'commission', 1);
                                                                                        $istaxable = '1';
                                                                                }
                                                                                // exact amount to charge buyer
                                                                                $escrowfeenotax = $fee;
                                                                                $escrowfee = sprintf("%01.2f", ($fee + $taxamount));
                                                                                // charge escrow fee to buyer as a separate transaction
                                                                                // this creates a transaction history item for the buyer of item
                                                                                $txn = $ilance->accounting_payment->construct_transaction_id();
                                                                                //$availablebalance = fetch_user('available_balance', $res_escrow['user_id']);
                                                                                //$totalbalance = fetch_user('total_balance', $res_escrow['user_id']);
                                                                                $autopayment = fetch_user('autopayment', $res_escrow['user_id']);
                                                                                if ($total_balance_after >= $escrowfee AND $autopayment)
                                                                                {
                                                                                        // generate paid escrow fee invoice to the winner of the auction
                                                                                        // this will allow the admin and winner to see the commission fee invoice
                                                                                        // separate for this escrow transaction and since we've already applied
                                                                                        // any applicable taxes, we'll send a 0 to the ending of this accounting
                                                                                        // function to disable any tax support
                                                                                        $ilance->db->query("
                                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                                (invoiceid, projectid, user_id, description, amount, paid, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, paiddate, custommessage, transactionid, isescrowfee)
                                                                                                VALUES(
                                                                                                NULL,
                                                                                                '" . $res_escrow['project_id'] . "',
                                                                                                '" . $res_escrow['user_id'] . "',
                                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
                                                                                                '" . $istaxable . "',
                                                                                                '" . $ilance->db->escape_string($taxamount) . "',
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
                                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                                        $isfee2paid = 1;
                                                                                        $ilance->db->query("
												UPDATE " . DB_PREFIX . "users
												SET available_balance = available_balance - " . sprintf("%01.2f", $escrowfee) . ",
												total_balance = total_balance - " . sprintf("%01.2f", $escrowfee) . "
												WHERE user_id = '" . intval($res_escrow['user_id']) . "'
											", 0, null, __FILE__, __LINE__);
                                                                                        // additionally we'll track this fee payment for the total spending amount for the user
                                                                                        $ilance->accounting_payment->insert_income_spent($res_escrow['user_id'], sprintf("%01.2f", $escrowfee), 'credit');
                                                                                }
                                                                                else
                                                                                {
                                                                                        $ilance->db->query("
                                                                                                INSERT INTO " . DB_PREFIX . "invoices
                                                                                                (invoiceid, projectid, user_id, description, amount, totalamount, istaxable, taxamount, taxinfo, status, invoicetype, paymethod, ipaddress, createdate, duedate, custommessage, transactionid, isescrowfee)
                                                                                                VALUES(
                                                                                                NULL,
                                                                                                '" . $res_escrow['project_id'] . "',
                                                                                                '" . $res_escrow['user_id'] . "',
                                                                                                '" . $ilance->db->escape_string('{_escrow_transaction_fee_securing_funds_for_auction}') . ': ' . $ilance->db->escape_string(fetch_auction('project_title', $res_escrow['project_id'])) . ' #' . $res_escrow['project_id'] . "',
                                                                                                '" . $ilance->db->escape_string($escrowfeenotax) . "',
                                                                                                '" . $ilance->db->escape_string($escrowfee) . "',
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
                                                                                        $escrowfee_invoice_id = $ilance->db->insert_id();
                                                                                        $isfee2paid = 0;        
                                                                                }
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "invoices
                                                                                        SET isescrowfee = '1'
                                                                                        WHERE invoiceid = '" . intval($escrowfee_invoice_id) . "'
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                $ilance->db->query("
                                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                                        SET isfee2paid = '" . $isfee2paid . "',
                                                                                        fee2invoiceid = '" . $escrowfee_invoice_id . "',
                                                                                        fee2 = '" . $ilance->db->escape_string($escrowfee) . "'
                                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                                ", 0, null, __FILE__, __LINE__);
                                                                                // because we've inserted a separate fee for the escrow transaction
                                                                                // let's set our $amount to the bid amount + shipping value only
                                                                                // since both members will have access to this escrow transaction
                                                                                // and it would appear confusing to the "merchant" showing
                                                                                // total including the winning bidders fees.
                                                                                // (however the bidder will see his fee as a separate transaction)
                                                                                //$amount = ($res_escrow['bidamount'] + $res_escrow['shipping']);
                                                                        }
                                                                }
                                                        }
                                                        // because we've generated a separate transaction fee for the bidder above (if applicable)
                                                        // let's adjust the main escrow transaction to amounts that make sense to both
                                                        // parties while viewing the transaction.  ultimately, the bidder will have another
                                                        // separate transaction to view the fee and related taxes
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET paid = '" . $bidamount . "',
                                                                amount = '" . $bidamount . "',
                                                                totalamount = '" . $bidamount . "',
                                                                status = 'paid',
                                                                paiddate = '" . DATETIME24H . "'
                                                                WHERE invoiceid = '" . intval($invoiceid) . "'
									AND user_id = '" . intval($userid) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "users
                                                                SET available_balance = available_balance - " . sprintf("%01.2f", $bidamount) . ",
                                                                total_balance = total_balance - " . sprintf("%01.2f", $bidamount) . "
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        // track income spent
                                                        $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $bidamount), 'credit');
                                                        $escrowprojectid = $ilance->db->fetch_field(DB_PREFIX . "projects_escrow", "escrow_id = '" . $res_escrow['escrow_id'] . "'", "project_id");
                                                        if (fetch_auction('project_state', $escrowprojectid) == 'service')
                                                        {
                                                                // remember - we update the "amount in escrow" as the original bid amount
                                                                // since the buyer had to pay the full amount + fee so if this was for example
                                                                // an awarded bid for $20.00 and the fee was $5.00 the amount the buyer pays is $25.00
                                                                // however the amount that the held in escrow is $20.00 to the provider
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET escrowamount = '" . $res_escrow['bidamount'] . "',
                                                                        status = 'confirmed',
                                                                        date_paid = '" . DATETIME24H . "'
                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                $currencyid = intval(fetch_auction('currencyid', $escrowprojectid));
                                                                $existing = array(
                                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                                        '{{invoice_id}}' => intval($invoiceid),
									'{{paymethod}}' => $gateway,
									'{{gateway}}' => $gateway,
									'{{project_title}}' => fetch_auction('project_title', $escrowprojectid),
									'{{winner}}' => fetch_user('username', intval($userid)),
									'{{owner}}' => fetch_user('username', intval($res_escrow['user_id'])),
									'{{amount}}' => $ilance->currency->format($res_escrow['bidamount'], $currencyid),
                                                                );
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('escrow_payment_forward_admin');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                $ilance->email->mail = fetch_user('email', $userid);
                                                                $ilance->email->slng = fetch_user_slng($userid);
                                                                $ilance->email->get('escrow_payment_forward_provider');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                                $ilance->email->get('escrow_payment_forward_buyer');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                        }
                                                        else if (fetch_auction('project_state', $escrowprojectid) == 'product')
                                                        {
                                                                // remember - we update the "amount in escrow" as the original bid amount + shipping
                                                                // since the bidder had to pay the full amount + fee (maybe) so if this was for example
                                                                // an winning bid for $20.00 and the fee was $5.00 the amount the bidder pays is $25.00
                                                                // however the amount that is held in escrow is $20.00 to the merchant
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "projects_escrow
                                                                        SET escrowamount = '" . ($res_escrow['bidamount'] + $res_escrow['shipping']) . "',
                                                                        status = 'started',
                                                                        date_paid = '" . DATETIME24H . "'
                                                                        WHERE escrow_id = '" . $res_escrow['escrow_id'] . "'
                                                                ", 0, null, __FILE__, __LINE__);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_bids
									SET winnermarkedaspaid = '1',
									winnermarkedaspaiddate = '" . DATETIME24H . "',
									winnermarkedaspaidmethod = 'account'
									WHERE bid_id = '" . $res_escrow['bid_id'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
								$ilance->db->query("
									UPDATE " . DB_PREFIX . "project_realtimebids
									SET winnermarkedaspaid = '1',
									winnermarkedaspaiddate = '" . DATETIME24H . "',
									winnermarkedaspaidmethod = 'account'
									WHERE bid_id = '" . $res_escrow['bid_id'] . "'
									LIMIT 1
								", 0, null, __FILE__, __LINE__);
                                                                $existing = array(
                                                                        '{{project_id}}' => $res_escrow['project_id'],
                                                                        '{{invoice_id}}' => intval($invoiceid),
                                                                        '{{biddername}}' => fetch_user('username', $res_escrow['user_id']),
                                                                        '{{ownername}}' => fetch_user('username', $res_escrow['project_user_id']),
                                                                        '{{project_title}}' => fetch_auction('project_title', $res_escrow['project_id']),
                                                                        '{{escrow_amount}}' => $ilance->currency->format($bidamount),
									'{{paymethod}}' => '{_account_balance}'
                                                                );
                                                                $ilance->email->mail = SITE_EMAIL;
                                                                $ilance->email->slng = fetch_site_slng();
                                                                $ilance->email->get('product_escrow_payment_foward_admin');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['project_user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['project_user_id']);
                                                                $ilance->email->get('product_escrow_payment_forward_merchant');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                                $ilance->email->mail = fetch_user('email', $res_escrow['user_id']);
                                                                $ilance->email->slng = fetch_user_slng($res_escrow['user_id']);
                                                                $ilance->email->get('product_escrow_payment_forward_bidder');		
                                                                $ilance->email->set($existing);
                                                                $ilance->email->send();
                                                        }
							if ($silentmode)
							{
								return true;
							}
							$project_state = fetch_auction('project_state', $escrowprojectid);
							$bidsub = ($project_state == 'service') ? 'sub=rfp-escrow' : 'bidsub=product-escrow';
							$output_phrase = ($project_state == 'service') ?  '{_service_escrow_payments_out}' :  '{_product_escrow_payments_out}';                                                         print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['escrow'] . '?cmd=management&amp;'.$bidsub.'', $output_phrase);
							print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['escrow'] . '?cmd=management&amp;'. $bidsub, $output_phrase);
							exit();
                                                }
                                                else
                                                {
							if ($silentmode)
							{
								return false;
							}
                                                        $area_title = '{_invoice_payment_menu_denied_payment_does_not_belong_to_user}';
                                                        $page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_payment}';
                                                        print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}' . '<br /><br />' . '{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
                                                        exit();
                                                }
                                        }
                                        else
                                        {
						if ($silentmode)
						{
							return false;
						}
                                                $area_title = '{_funds_not_available}';
                                                $page_title = SITE_NAME . ' - {_funds_not_available}';
                                                print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}', $ilpage['accounting'], '{_my_account}');
                                                exit();
                                        }                        
                                }
                                else
                                {
					if ($silentmode)
					{
						return false;
					}
                                        $area_title = '{_funds_not_available}';
                                        $page_title = SITE_NAME . ' - {_funds_not_available}';
                                        print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}', $ilpage['accounting'], '{_my_account}');
                                        exit();   
                                }
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