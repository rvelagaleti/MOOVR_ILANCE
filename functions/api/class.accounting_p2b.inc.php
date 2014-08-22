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

if (!class_exists('accounting'))
{
	exit;
}

/**
* Function to handle provider to buyer based accounting logic
*
* @package      iLance\Accounting\P2B
* @version      4.0.0.8059
* @author       ILance
*/
class accounting_p2b extends accounting
{
	/**
        * Function for processing a provider generated (to buyer) transaction type being paid by the actual buyer.
        *
        * @param       integer      user id
        * @param       integer      invoice id
        * @param       string       invoice type
        * @param       string       amount to process
        * @param       string       method of payment (ipn/account/creditcard)
        * @param       string       name of gateway processing this transaction
        * @param       string       gateway transaction id
        * @param       boolean      is refunded payment? (default false)
        * @param       string       gateway original transaction id (if payment is refunded by gateway)
        * @param       boolean      silent mode (return only true or false; default false)
        *
        * @return      mixed        for ipn processing, boolean is used, others will use a print_notice() function to end user.
        */
        function payment($userid = 0, $invoiceid = 0, $invoicetype = 'p2b', $amount = 0, $method = 'account', $gateway = '', $gatewaytxn = '', $isrefund = false, $originalgatewaytxn = '', $silentmode = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                // #### INSTANT PAYMENT NOTIFICATION ###################################
                if ($method == 'ipn')
                {
                        $sql = $ilance->db->query("
                                SELECT p2b_user_id, projectid
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($invoiceid) . "'
					AND status = 'unpaid'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res_invoice = $ilance->db->fetch_array($sql, DB_ASSOC);
                                $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "invoices 
                                        SET paid = '" . sprintf("%01.2f", $amount) . "',
                                        status = 'paid',
                                        paiddate = '" . DATETIME24H . "',
                                        paymethod = '" . $ilance->db->escape_string($gateway) . "',
                                        custommessage = '".$ilance->db->escape_string($gatewaytxn)."'
                                        WHERE invoiceid = '" . intval($invoiceid) . "'
                                            AND user_id = '" . intval($userid) . "'
                                            AND p2b_user_id = '" . $res_invoice['p2b_user_id'] . "'
                                            AND projectid = '" . $res_invoice['projectid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->accounting_payment->insert_income_reported($res_invoice['p2b_user_id'], sprintf("%01.2f", $amount), 'credit');
                                $ilance->email->mail = fetch_user('email', $res_invoice['p2b_user_id']);
                                $ilance->email->slng = fetch_user_slng($res_invoice['p2b_user_id']);
                                $ilance->email->get('buyer_payment_online_account_provider');		
                                $ilance->email->set(array(
                                        '{{buyer}}' => fetch_user('username', $userid),
                                        '{{provider}}' => fetch_user('username', $res_invoice['p2b_user_id']),
                                        '{{project_title}}' => fetch_auction('project_title', $res_invoice['projectid']),
                                        '{{invoiceid}}' => intval($invoiceid),
                                        '{{invoice_amount}}' => $ilance->currency->format($amount),
                                ));
                                $ilance->email->send();
                                // fetch service providers available account balance
                                $sql_sellerbalance = $ilance->db->query("
                                        SELECT available_balance, total_balance
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $res_invoice['p2b_user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_sellerbalance) > 0)
                                {
                                        $res_sellerbalance = $ilance->db->fetch_array($sql_sellerbalance, DB_ASSOC);
                                        $seller_available_balance = $res_sellerbalance['available_balance'];
                                        $seller_total_balance = $res_sellerbalance['total_balance'];
                                        // re adjust new account balance for provider
                                        $new_seller_available_balance = ($seller_available_balance + $amount);
                                        $new_seller_total_balance = ($seller_total_balance + $amount);
                                        // add funds processed by buyer into service providers online account balance
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET available_balance = '" . sprintf("%01.2f", $new_seller_available_balance) . "',
                                                total_balance = '" . sprintf("%01.2f", $new_seller_total_balance) . "'
                                                WHERE user_id = '" . $res_invoice['p2b_user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $existing = array(
                                                '{{buyer}}' => fetch_user('username', $userid),
                                                '{{provider}}' => fetch_user('username', $res_invoice['p2b_user_id']),
                                                '{{project_title}}' => fetch_auction('project_title', $res_invoice['projectid']),
                                                '{{invoiceid}}' => intval($invoiceid),
                                                '{{invoice_amount}}' => $ilance->currency->format($amount),
                                        );
                                        $ilance->email->mail = fetch_user('email', $userid);
                                        $ilance->email->slng = fetch_user_slng(intval($userid));
                                        $ilance->email->get('buyer_payment_online_account_buyer');		
                                        $ilance->email->set($existing);                                        
                                        $ilance->email->send();
                                        return true;
                                }
                        }
                        return false;
                }
                // #### ONLINE ACCOUNT BALANCE #########################################
                else if ($method == 'account')
                {
                        $sql_buyerbalance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_buyerbalance) > 0)
                        {
                                $res_buyerbalance = $ilance->db->fetch_array($sql_buyerbalance, DB_ASSOC);
                                $buyer_available_balance = $res_buyerbalance['available_balance'];
                                $buyer_total_balance = $res_buyerbalance['total_balance'];
                                if ($buyer_available_balance < $amount)
                                {
					if ($silentmode)
					{
						return false;
					}
                                        $area_title = '{_no_funds_available_in_online_account}';
                                        $page_title = SITE_NAME . ' - {_no_funds_available_in_online_account}';
                                        print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
                                        exit();
                                }
                                $sql_invoice = $ilance->db->query("
                                        SELECT p2b_user_id, projectid, 
                                        FROM " . DB_PREFIX . "invoices
                                        WHERE invoiceid = '" . intval($invoiceid) . "'
						AND status = 'unpaid'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_invoice) > 0)
                                {
                                        $res_invoice = $ilance->db->fetch_array($sql_invoice, DB_ASSOC);
                                        $new_buyer_avail_balance = ($buyer_available_balance - $amount);
                                        $new_buyer_total_balance = ($buyer_total_balance - $amount);
                                        ###################################################################################
                                        ## RE-UPDATE BUYERS ACCOUNT TABLE WITH NEW TOTAL AND AVAIL BALANCE - ACCOUNT DEBIT!
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET available_balance = '" . sprintf("%01.2f", $new_buyer_avail_balance) . "',
                                                total_balance = '" . sprintf("%01.2f", $new_buyer_total_balance) . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $amount), 'credit');
                                        ##################################
                                        ## UPDATE PROVIDER INVOICE TO PAID
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices 
                                                SET paid = '" . sprintf("%01.2f", $amount) . "',
                                                status = 'paid',
                                                paiddate = '" . DATETIME24H . "'
                                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                                    AND user_id = '" . intval($userid) . "'
                                                    AND p2b_user_id = '" . $res_invoice['p2b_user_id'] . "'
                                                    AND projectid = '" . $res_invoice['projectid'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $ilance->accounting_payment->insert_income_reported($res_invoice['p2b_user_id'], sprintf("%01.2f", $amount), 'credit');
                                        $ilance->email->mail = fetch_user('email', $res_invoice['p2b_user_id']);
                                        $ilance->email->slng = fetch_user_slng($res_invoice['p2b_user_id']);
                                        $ilance->email->get('buyer_payment_online_account_provider');		
                                        $ilance->email->set(array(
                                                '{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
                                                '{{provider}}' => fetch_user('username', $res_invoice['p2b_user_id']),
                                                '{{project_title}}' => fetch_auction('project_title', $res_invoice['projectid']),
                                                '{{invoiceid}}' => intval($invoiceid),
                                                '{{invoice_amount}}' => $ilance->currency->format($amount),
                                        ));
                                        $ilance->email->send();
                                        ###########################################
                                        ## FETCH PROVIDER AVAILABLE ACCOUNT BALANCE
                                        $sql_sellerbalance = $ilance->db->query("
                                            SELECT available_balance, total_balance
                                            FROM " . DB_PREFIX . "users
                                            WHERE user_id = '" . $res_invoice['p2b_user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($sql_sellerbalance) > 0)
                                        {
                                                $res_sellerbalance = $ilance->db->fetch_array($sql_sellerbalance, DB_ASSOC);
                                                $seller_available_balance = $res_sellerbalance['available_balance'];
                                                $seller_total_balance = $res_sellerbalance['total_balance'];
                                                $new_seller_available_balance = ($seller_available_balance + $amount);
                                                $new_seller_total_balance = ($seller_total_balance + $amount);
                                                // add funds processed by buyer to providers online account
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = '" . sprintf("%01.2f", $new_seller_available_balance) . "',
                                                        total_balance = '" . sprintf("%01.2f", $new_seller_total_balance) . "'
                                                        WHERE user_id = '" . $res_invoice['p2b_user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                $existing = array(
                                                        '{{buyer}}' => $_SESSION['ilancedata']['user']['username'],
                                                        '{{provider}}' => fetch_user('username', $res_invoice['p2b_user_id']),
                                                        '{{project_title}}' => fetch_auction('project_title', $res_invoice['projectid']),
                                                        '{{invoiceid}}' => intval($invoiceid),
                                                        '{{invoice_amount}}' => $ilance->currency->format($amount),
                                                );
                                                $ilance->email->mail = $_SESSION['ilancedata']['user']['email'];
                                                $ilance->email->slng = fetch_user_slng(intval($userid));
                                                $ilance->email->get('buyer_payment_online_account_buyer');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
						if ($silentmode)
						{
							return true;
						}
                                                $area_title = '{_generated_invoice_payment_complete_menu}';
                                                $page_title = SITE_NAME . ' - {_generated_invoice_payment_complete_menu}';
                                                print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['accounting'], '{_my_account}');
                                                exit();
                                        }
                                        else
                                        {
						if ($silentmode)
						{
							return false;
						}
                                                $area_title = '{_invoice_payment_menu_denied_subscription_payment_does_not_belong_to_user}';
                                                $page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_subscription_payment}';
                                                print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', $ilpage['accounting'], '{_my_account}');
                                                exit();
                                        }
                                }
                                else
                                {
					if ($silentmode)
					{
						return false;
					}
                                        $area_title = '{_invoice_payment_menu_denied_subscription_payment_does_not_belong_to_user}';
                                        $page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_subscription_payment}';
                                        print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', $ilpage['accounting'], '{_my_account}');
                                        exit();
                                }
                        }
                        else
                        {
				if ($silentmode)
				{
					return false;
				}
                                $area_title = '{_invoice_payment_menu_denied_subscription_payment_does_not_belong_to_user}';
                                $page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_subscription_payment}';
                                print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}', $ilpage['accounting'], '{_my_account}');
                                exit();
                        }
                }
        }
	
	/**
        * Function for constructing a provider to buyer generated transaction to be paid through the marketplace.
        *
        * Basically, we have an invoice type of "p2b" where the provider can generate an unpaid invoice through the marketplace officially.
        * As a result, provider to buyer transactions can be viewed by the buyers on a per-project basis and multiple invoices can be generated
        * based on a single auction.  Allowing the service such as this, it is also possible to pre-configure "p2b fees" where you can set a rate
        * of 5% (for example) to let the provider generate an invoice to his/her buyer.  Upon the buyer's payment, the fee will be debitted from that
        * service provider.
        *
        * @param       string       amount to process
        * @param       integer      service provider id
        * @param       integer      service buyer id
        * @param       integer      associated project id
        * @param       string       associated comments
        * @param       string       transaction fee amount (if applicable)
        * @param       bool         defines if instant payment (from account balance) was selected by payer
        * @param       string       defines the providers selected payment status of this newly generated transaction (unpaid or paid) unpaid is used by default
        * @param       string       defines the providers preferred payment method when being viewed on the transaction page
        *
        * @return      nothing      The data inserted into datastore
        */
        function construct_p2b_transaction($amount = 0, $sellerid = 0, $buyerid = 0, $projectid = 0, $comments = '', $txnfee = 0, $instantpay = 0, $paymentstatus = 'unpaid', $paymentmethod = '')
        {
                global $ilance, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage;
                $instantpay = (!empty($instantpay) AND $instantpay > 0) ? 1 : 0;
                $area_title = '{_generating_invoice_to_buyer}';
                $page_title = SITE_NAME . ' - {_generating_invoice_to_buyer}';
                // #### invoice due dates ######################################
                $invoice_due_date = date('Y-m-d H:i:s', (TIMESTAMPNOW + $ilconfig['invoicesystem_maximumpaymentdays']*24*3600));
                // #### is a transaction / commission fee set? #################
                if (isset($txnfee) AND $txnfee > 0)
                {
                        $new_txn_transaction = $ilance->accounting_payment->construct_transaction_id();
                        // construct unpaid transaction fee invoice to the customer generating the invoice
                        $txn_description = '{_transaction_fee_for_rfp}' . ' #' . intval($projectid) . ': ' . fetch_auction('project_title', intval($projectid)) . ': ' . '{_invoice_generation_to}' . ' ' . fetch_user('username', intval($buyerid));
                        $txnfee_invoice_id = $this->insert_transaction(
                                0,
                                intval($projectid),
                                0,
                                $sellerid,
                                0,
                                0,
                                0,
                                $txn_description,
                                sprintf("%01.2f", $txnfee),
                                '',
                                'unpaid',
                                'debit',
                                'account',
                                DATETIME24H,
                                $invoice_due_date,
                                '',
                                '',
                                0,
                                0,
                                1,
                                $new_txn_transaction
                        );
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET isp2bfee = '1'
                                WHERE invoiceid = '" . intval($txnfee_invoice_id) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $transaction_url = HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $txnfee_invoice_id;
                        // does customer wish to pay fees now?
                        if ($instantpay OR fetch_user('autopayment', $sellerid))
                        {
                                $sqlgetacc = $ilance->db->query("
                                        SELECT total_balance, available_balance
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . intval($sellerid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $resgetacc = $ilance->db->fetch_array($sqlgetacc, DB_ASSOC);
                                if ($resgetacc['available_balance'] >= $txnfee)
                                {
                                        $new_total = sprintf("%01.2f", $resgetacc['total_balance'] - $txnfee);
                                        $new_avail = sprintf("%01.2f", $resgetacc['available_balance'] - $txnfee);
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET available_balance = '" . $new_avail . "',
                                                total_balance = '" . $new_total . "'
                                                WHERE user_id = '" . intval($sellerid) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        // track income spent
                                        $ilance->accounting_payment->insert_income_spent($sellerid, sprintf("%01.2f", $txnfee), 'credit');
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices
                                                SET paid = '" . sprintf("%01.2f", $txnfee) . "',
                                                status = 'paid',
                                                paiddate = '" . DATETIME24H . "'
                                                WHERE user_id = '" . intval($sellerid) . "'
                                                    AND invoiceid = '" . intval($txnfee_invoice_id) . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        
                                        $existing = array(
                                                '{{provider}}' => fetch_user('username', $sellerid),
                                                '{{invoiceid}}' => $txnfee_invoice_id,
                                                '{{new_txn_transaction}}' => $new_txn_transaction,
                                                '{{invoice_amount}}' => $ilance->currency->format($txnfee),
                                                '{{description}}' => $txn_description,
                                                '{{datepaid}}' => print_date(DATETIME24H, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0),
                                        );
                                        $ilance->email->mail = fetch_user('email', $sellerid);
                                        $ilance->email->slng = fetch_user_slng($sellerid);
                                        $ilance->email->get('transaction_payment_complete');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('transaction_payment_complete_admin');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                }
                        }
                }
                else
                {
                        $new_txn_transaction = '--';
                        $transaction_url = '';
                        $txn_fee = '0.00';
                }
                $new_inv_transaction = $ilance->accounting_payment->construct_transaction_id();
                $p2b_invoice_id = $this->insert_transaction(
                        0,
                        intval($projectid),
                        0,
                        intval($buyerid),
                        intval($sellerid),
                        0,
                        0,
                        '{_invoice_generated_for_rfp}' . ' #' . intval($projectid) . ': ' . fetch_auction('project_title', intval($projectid)),
                        sprintf("%01.2f", $amount),
                        '',
                        mb_strtolower($paymentstatus),
                        'p2b',
                        'account',
                        DATETIME24H,
                        DATEINVOICEDUE,
                        '',
                        $comments,
                        0,
                        0,
                        1,
                        $new_inv_transaction
                );
                $paidamount = sprintf("%01.2f", 0);
                if (isset($paymentstatus) AND $paymentstatus == 'paid')
                {
                        $paidamount = sprintf("%01.2f", $amount);
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET p2b_markedaspaid = '1',
                                paid = '" . $ilance->db->escape_string($paidamount) . "',
                                paiddate = '" . DATETIME24H . "'
                                WHERE invoiceid = '" . intval($p2b_invoice_id) . "'
                        ");
                }
                if (isset($paymentmethod) AND !empty($paymentmethod))
                {
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET p2b_paymethod = '" . $ilance->db->escape_string($paymentmethod) . "'
                                WHERE invoiceid = '" . intval($p2b_invoice_id) . "'
                        ");
                }
		
		$paymentmethod = ((mb_substr($paymentmethod, 0, 1) == '_') ? '{' . $paymentmethod . '}' : $paymentmethod);
                $existing = array(
                        '{{buyer}}' => fetch_user('username', $buyerid),
                        '{{provider}}' => fetch_user('username', $sellerid),
                        '{{project_title}}' => fetch_auction('project_title', intval($projectid)),
                        '{{invoice_amount}}' => $ilance->currency->format($amount),
                        '{{paymentstatus}}' => ucwords($paymentstatus),
                        '{{paymentmethod}}' => $paymentmethod,
                        '{{invoiceid}}' => $p2b_invoice_id,
                        '{{new_inv_transaction}}' => $new_inv_transaction,
                        '{{new_txn_transaction}}' => $new_txn_transaction,
                        '{{invoice_notes}}' => $comments,
                        '{{url}}' => $transaction_url,
                        '{{transaction_fee}}' => $ilance->currency->format($txnfee)
                );
                $ilance->email->mail = fetch_user('email', intval($buyerid));
                $ilance->email->slng = fetch_user_slng(intval($buyerid));
                $ilance->email->get('provider_invoiced_buyer_buyer');		
                $ilance->email->set($existing);
                $ilance->email->send();
                $ilance->email->mail = fetch_user('email', $sellerid);
                $ilance->email->slng = fetch_user_slng($sellerid);
                $ilance->email->get('provider_invoiced_buyer');		
                $ilance->email->set($existing);
                $ilance->email->send();
                $ilance->email->mail = SITE_EMAIL;
                $ilance->email->slng = fetch_site_slng();
                $ilance->email->get('provider_invoiced_buyer_admin');		
                $ilance->email->set($existing);
                $ilance->email->send();
        }
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>