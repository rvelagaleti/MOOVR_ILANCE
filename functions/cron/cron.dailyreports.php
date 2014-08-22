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

if (!isset($GLOBALS['ilance']->db))
{
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}

$ilance->timer->start();

($apihook = $ilance->api('cron_dailyreports_start')) ? eval($apihook) : false;

// #### OVERALL MEMBERSHIP SALES ###############################################
$overall_subscription_earnings = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE paid > 0
            AND invoicetype = 'subscription'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $overall_subscription_earnings = $ilance->currency->format($res['total']);
}

// #### OVERALL COMMISSION SALES ###############################################
$overall_commission_earnings = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE paid > 0
            AND invoicetype = 'commission'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $overall_commission_earnings = $ilance->currency->format($res['total']);
}

// #### SUBSCRIPTION FEES PENDING ##############################################
$overall_subscriptions_pending = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE (status = 'unpaid' OR status = 'scheduled')
                AND amount > 0
                AND invoicetype = 'subscription'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $overall_subscriptions_pending = $ilance->currency->format($res['total']);
}

// #### COMMISSION FEES PENDING ################################################
$commission_payments_pending_total = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE status = 'unpaid'
                AND amount > 0
                AND invoicetype = 'commission'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $commission_payments_pending_total = $ilance->currency->format($res['total']);
}

// #### TOTAL MEMBER COUNT #####################################################
$member_count = '0';
$sql = $ilance->db->query("
        SELECT user_id
        FROM " . DB_PREFIX . "users
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $member_count = $ilance->db->num_rows($sql);
}

// #### NEW REGISTRATIONS TODAY ################################################
$member_registrations_count = '0';
$sql = $ilance->db->query("
        SELECT user_id
        FROM " . DB_PREFIX . "users
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $member_registrations_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF JOB LISTINGS POSTED TODAY ####################################
$projects_posted_today_count = '0';
$sql = $ilance->db->query("
        SELECT project_id
        FROM " . DB_PREFIX . "projects
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
            AND project_state = 'service'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $projects_posted_today_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF ITEM LISTINGS POSTED TODAY ###################################
$products_posted_today_count = '0';
$sql = $ilance->db->query("
        SELECT project_id
        FROM " . DB_PREFIX . "projects
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
            AND project_state = 'product'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $products_posted_today_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF JOB BIDS #####################################################
$project_bids_today_count = '0';
$sql = $ilance->db->query("
        SELECT bid_id
        FROM " . DB_PREFIX . "project_bids
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
                AND state = 'service'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $project_bids_today_count = $ilance->db->num_rows($sql);
}

// #### NUMBER OF ITEM BIDS ####################################################
$item_bids_today_count = '0';
$sql = $ilance->db->query("
        SELECT bid_id
        FROM " . DB_PREFIX . "project_bids
        WHERE date_added LIKE ('%" . DATEYESTERDAY . "%')
                AND state = 'product'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $item_bids_today_count = $ilance->db->num_rows($sql);
}

// #### COMMISSION FEE COUNT ###################################################
$number_commission_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%')
            AND invoicetype = 'commission'
");
if ($ilance->db->num_rows($sql) > 0)
{
        $number_commission_fees_today_count = $ilance->db->num_rows($sql);
}

// #### COMMISSION FEES PAID TODAY #############################################
$amount_paid_commission_fees_today_count_amount = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS paid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'commission' 
            AND status = 'paid'
");
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_paid_commission_fees_today_count_amount = $ilance->currency->format($res['paid']);
}

// #### CREDENTIAL PAYMENT COUNT ###############################################
$number_credential_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'credential'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_credential_fees_today_count = $ilance->db->num_rows($sql);
}

// #### CREDENTIAL FEES PAID TODAY #############################################
$amount_paid_credential_fees_today_count_amount = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%') 
            AND invoicetype = 'credential' 
            AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_paid_credential_fees_today_count_amount = $ilance->currency->format($res['total']);
}

// #### CREDENTIAL FEES UNPAID ALL-TIME ########################################
$amount_unpaid_credential_fees_alltime_count_amount = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE invoicetype = 'credential' 
                AND status = 'unpaid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_unpaid_credential_fees_alltime_count_amount = $ilance->currency->format($res['total']);
}

// #### SUBSCRIPTION PAYMENT COUNT #############################################
$number_paid_subscription_fees_today_count = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%')
                AND paid > 0
                AND invoicetype = 'subscription' 
                AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_paid_subscription_fees_today_count = $ilance->db->num_rows($sql);
}

// #### SUBSCRIPTION PAYMENTS TODAY ############################################
$amount_paid_subscription_fees_today_count_amount = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS amount
        FROM " . DB_PREFIX . "invoices
        WHERE createdate LIKE ('%" . DATEYESTERDAY . "%')
                AND amount > 0
                AND invoicetype = 'subscription' 
                AND status = 'paid'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_paid_subscription_fees_today_count_amount = $ilance->currency->format($res['amount']);
}

// #### FAILED LOGINS TODAY ####################################################
$number_of_failed_logins_today_count = '0';
$sql = $ilance->db->query("
        SELECT id
        FROM " . DB_PREFIX . "failed_logins
        WHERE datetime_failed LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_failed_logins_today_count = $ilance->db->num_rows($sql);
}

// #### FAILED LOGINS (ALL-TIME) ###############################################
$number_of_failed_logins_alltime_count = '0';
$sql = $ilance->db->query("
        SELECT id
        FROM " . DB_PREFIX . "failed_logins
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_failed_logins_alltime_count = $ilance->db->num_rows($sql);
}

// #### PERCENTAGE OF MEMBERS NOT SIGNED IN FOR LAST 12 MONTHS #################
$percent_of_members_not_signedin = '0.0%';
$current_year = explode('-', DATETIME24H);
@$current_year[0] = $current_year[0] - 1;
$past_year = implode('-', $current_year);

$sql = $ilance->db->query("
        SELECT user_id
        FROM " . DB_PREFIX . "users
        WHERE lastseen BETWEEN  '" . $past_year . "' AND '" . DATETIME24H . "'
        	AND status = 'active'
", 0, null, __FILE__, __LINE__);
$users_signedin = $ilance->db->num_rows($sql);

$sql_total_users = $ilance->db->query("
        SELECT user_id
        FROM " . DB_PREFIX . "users
        WHERE status = 'active'
", 0, null, __FILE__, __LINE__);
$total_users = $ilance->db->num_rows($sql_total_users);

if ($users_signedin == '0')
{
	$percent_of_members_not_signedin = '100%';
}
else 
{
	$percent_of_members_not_signedin = 100 - (($users_signedin / $total_users) * 100);
	$percent_of_members_not_signedin = round($percent_of_members_not_signedin, 1) . '%';
}

// #### TOTAL AMOUNT OF BULK UPLOAD SESSION TODAY ##############################
$number_of_bulk_upload_session_today = '0';
$sql = $ilance->db->query("
        SELECT dateupload
        FROM " . DB_PREFIX . "bulk_sessions
        WHERE dateupload  LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_bulk_upload_session_today = $ilance->db->num_rows($sql);
}

// #### TOTAL AMOUNT OF ITEM LISTING BULK UPLOAD SESSION TODAY #################
$amount_of_item_listing_bulk_upload_session_today = 0;
$sql = $ilance->db->query("
        SELECT itemsuploaded
        FROM " . DB_PREFIX . "bulk_sessions
        WHERE dateupload LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
        {
                $amount_of_item_listing_bulk_upload_session_today += $res['itemsuploaded'];
	}
}

// #### NUMBER OF PAID INVOICES TODAY ##########################################
$number_of_paid_invoices_today = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate LIKE ('%" . DATEYESTERDAY . "%')
                AND amount > 0
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_paid_invoices_today = $ilance->db->num_rows($sql);
}

// #### AMOUNT OF PAID INVOICES TODAY ##########################################
$amount_of_paid_invoices_today = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(paid) AS amount
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate LIKE ('%" . DATEYESTERDAY . "%')
                AND paid > 0
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_of_paid_invoices_today = $ilance->currency->format($res['amount']);
}

// #### NUMBER OF SAVED SEARCHES DIGEST TODAY ##################################
$number_of_saved_search_digest = '0';
$sql = $ilance->db->query("
        SELECT searchid
        FROM " . DB_PREFIX . "search_favorites
        WHERE added LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_saved_search_digest = $ilance->db->num_rows($sql);
}

// #### NUMBER OF EMAIL NOTIFICATIONS TODAY ####################################
$number_of_notifications_today = '0';
$sql = $ilance->db->query("
        SELECT emaillogid
        FROM " . DB_PREFIX . "emaillog
        WHERE date LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_notifications_today = $ilance->db->num_rows($sql);
}

// #### NUMBER OF PRIVATE MESSAGE POSTS TODAY ##################################
$number_of_private_message_post = '0';
$sql = $ilance->db->query("
        SELECT messageid
        FROM " . DB_PREFIX . "messages
        WHERE date LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_private_message_post = $ilance->db->num_rows($sql);
}

// #### HIGHEST VIEWED JOB CATYEGORY ###########################################
$highest_viewed_job_catgory = 'N/A';
$sql = $ilance->db->query("
        SELECT title_" . fetch_site_slng() . " AS title, MAX(views) AS max
        FROM " . DB_PREFIX . "categories
        WHERE cattype = 'service' 
        LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $highest_viewed_job_catgory = $res['title'];
}

// #### HIGHEST VIEWED ITEM CATEGORY ###########################################
$highest_viewed_item_catgory = 'N/A';
$sql = $ilance->db->query("
        SELECT title_" . fetch_site_slng() . " AS title, MAX(views) AS max
        FROM " . DB_PREFIX . "categories
        WHERE cattype = 'product' 
        LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $highest_viewed_item_catgory = $res['title'];
}

// #### HIGHEST VIEWED JOB LISTING #############################################
$highest_viewed_job_listing = 'N/A';
$sql = $ilance->db->query("
        SELECT project_title, MAX(views) AS max
        FROM " . DB_PREFIX . "projects 
        WHERE project_type = 'reverse' 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $highest_viewed_job_listing = $res['project_title'];
}

// #### HIGHEST VIEWED ITEM LISTING ############################################
$highest_viewed_item_listing = 'N/A';
$sql = $ilance->db->query("
        SELECT project_title, MAX(views) AS max
        FROM " . DB_PREFIX . "projects 
        WHERE project_state = 'product'
        LIMIT 1
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $highest_viewed_item_listing = $res['project_title'];
}

// #### NUMBER OF ACCOUNTS DEPOSIT TODAY #######################################
$num_accounts_deposit = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate LIKE ('%" . DATEYESTERDAY . "%')
                AND paid > 0
                AND isdeposit = '1'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $num_accounts_deposit = $ilance->db->num_rows($sql);
}

// #### TOTAL AMOUNT IN ACCOUNTS DEPOSIT TODAY #################################
$total_amount_deposit_today = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate  LIKE ('%" . DATEYESTERDAY . "%')
                AND amount > 0
                AND isdeposit = '1'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $total_amount_deposit_today = $ilance->currency->format($res['total']);
}

// #### NUMBER OF WITHDRAW REQUEST TODAY #######################################
$total_amount_withdraw_today = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(amount) AS total
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate  LIKE ('%" . DATEYESTERDAY . "%')
                AND amount > 0
                AND iswithdraw = '1'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $total_amount_withdraw_today = $ilance->currency->format($res['total']);
}

// #### NUMBER OF ACCOUNTS DEPOSIT TODAY #######################################
$num_accounts_withdraw = '0';
$sql = $ilance->db->query("
        SELECT invoiceid
        FROM " . DB_PREFIX . "invoices
        WHERE paiddate LIKE ('%" . DATEYESTERDAY . "%')
                AND paid > 0
                AND iswithdraw = '1'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $num_accounts_withdraw = $ilance->db->num_rows($sql);
}

// #### NUMBER OF ESCROW ACCOUNTS FUNDED TODAY #################################
$num_escrow_accounts_funded = '0';
$sql = $ilance->db->query("
        SELECT escrow_id
        FROM " . DB_PREFIX . "projects_escrow
        WHERE date_paid LIKE ('%" . DATEYESTERDAY . "%') 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $num_escrow_accounts_funded = $ilance->db->num_rows($sql);
}

// #### TOTAL AMOUNT IN ESCROW ACCOUNT FUNDING TODAY ###########################
$total_amount_in_escrow_account = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(escrowamount) AS total
        FROM " . DB_PREFIX . "projects_escrow
        WHERE date_paid LIKE ('%" . DATEYESTERDAY . "%')
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $total_amount_in_escrow_account = $ilance->currency->format($res['total']);
}

// #### NUMBER OF ESCROW PAYMENT RELESE TODAY###################################
$number_of_escrow_payment_relese_today = '0';
$sql = $ilance->db->query("
        SELECT escrow_id
        FROM " . DB_PREFIX . "projects_escrow
        WHERE date_released  LIKE ('%" . DATEYESTERDAY . "%') 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
         $number_of_escrow_payment_relese_today = $ilance->db->num_rows($sql);
}

// #### TOTAL AMOUNT IN ESCROW ACCOUNT RELESE TODAY#############################
$amount_in_escrow_account_relese = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(escrowamount) AS total
        FROM " . DB_PREFIX . "projects_escrow
	WHERE date_released  LIKE ('%" . DATEYESTERDAY . "%') 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $amount_in_escrow_account_relese = $ilance->currency->format($res['total']);
}

// #### NUMBER OF ESCROW ACCOUNTS FUNDS PENDING  ###############################
$num_escrow_accounts_fund_pending = '0';
$sql = $ilance->db->query("
        SELECT escrow_id
        FROM " . DB_PREFIX . "projects_escrow
        WHERE status = 'pending'  
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
         $num_escrow_accounts_fund_pending = $ilance->db->num_rows($sql);
}

// #### TOTAL AMOUNT IN ESCROW FUNDS PENDING ACCOUNTS ##########################
$total_amount_in_escrow_fund_pending = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(escrowamount) AS total
        FROM " . DB_PREFIX . "projects_escrow
	WHERE status = 'pending' 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $total_amount_in_escrow_fund_pending = $ilance->currency->format($res['total']);
}

// #### NUMBER OF ESCROW ACCOUNTS CANCELLED TODAY ##############################
$number_of_escrow_accounts_cancel_today = '0';
$sql = $ilance->db->query("
        SELECT escrow_id 
        FROM " . DB_PREFIX . "projects_escrow
        WHERE date_cancelled LIKE ('%" . DATEYESTERDAY . "%') 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0 && $res['total'] != '')
{
         $number_of_escrow_accounts_cancel_today = $ilance->db->num_rows($sql);
}

// #### NUMBER OF SEARCHES TODAY ###############################################
$number_of_search_today = '0';
$sql = $ilance->db->query("
        SELECT id
        FROM " . DB_PREFIX . "search_users
        WHERE added LIKE ('%" . DATEYESTERDAY . "%') 
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_search_today = $ilance->db->num_rows($sql);
}

// #### NUMBER OF KEYWORDS ENTERED INTO SEARCH TODAY ###########################
$number_of_keywords_entered_into_search = '0';
$sql = $ilance->db->query("
        SELECT id
        FROM " . DB_PREFIX . "search_users 
        WHERE added  LIKE ('%" . DATEYESTERDAY . "%')
        GROUP BY keyword
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_keywords_entered_into_search = $ilance->db->num_rows($sql);
}

// #### NUMBER OF REFUNDS TODAY ###########################
$number_of_refunds = $buynowid = '0';
$sql = $ilance->db->query("
        SELECT DISTINCT( buynowid) , refund_date
        FROM " . DB_PREFIX . "invoices 
        WHERE refund_date LIKE ('%" . DATEYESTERDAY . "%')
        ", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $number_of_refunds = $ilance->db->num_rows($sql);
		$res = $ilance->db->fetch_array($sql, DB_ASSOC);
       	$buynowid = $res['buynowid'];
}

// #### TOTAL AMOUNT IN ESCROW FUNDS PENDING ACCOUNTS ##########################
$total_amount_refund_today = $ilance->currency->format(0);
$sql = $ilance->db->query("
        SELECT SUM(DISTINCT totalamount) AS total
        FROM " . DB_PREFIX . "invoices
	    WHERE refund_date LIKE ('%" . DATEYESTERDAY . "%') AND buynowid = '" . $buynowid . "'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
        $total_amount_refund_today = '('.$ilance->currency->format($res['total']).')';
}

// cron logic to ensure daily reports only send once per day
$sql = $ilance->db->query("
        SELECT emaillogid
        FROM " . DB_PREFIX . "emaillog
        WHERE user_id = '-1'
            AND logtype = 'dailyreport'
            AND date LIKE '%" . DATETODAY . "%'
", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) == 0)
{
        
        
        $addon_apihook = '';
        
        ($apihook = $ilance->api('cron_dailyreports_start')) ? eval($apihook) : false;                                                                
        
        $existing = array(
                '{{membercount}}' => $member_count,
                '{{memberregistercount}}' => $member_registrations_count,
                '{{subscriptionpaymentstoday}}' => $number_paid_subscription_fees_today_count,
                '{{subscriptionpaymentsamounttoday}}' => $amount_paid_subscription_fees_today_count_amount,
                '{{subscriptionpaymentsamountalltime}}' => $overall_subscription_earnings,
                '{{subscriptionpaymentsamountpendingalltime}}' => $overall_subscriptions_pending,
                '{{credentialrequeststoday}}' => $number_credential_fees_today_count,
                '{{credentialpaymentsamounttoday}}' => $amount_paid_credential_fees_today_count_amount,
                '{{credentialpaymentsamountpendingalltime}}' => $amount_unpaid_credential_fees_alltime_count_amount,
                '{{commissionfeestoday}}' => $number_commission_fees_today_count,
                '{{commissionpaymentsamounttoday}}' => $amount_paid_commission_fees_today_count_amount,
                '{{commissionpaymentsamountalltime}}' => $overall_commission_earnings,
                '{{commissionpaymentsamountpendingalltime}}' => $commission_payments_pending_total,
                '{{servicespostedtoday}}' => $projects_posted_today_count,
                '{{productspostedtoday}}' => $products_posted_today_count,
                '{{servicebidstoday}}' => $project_bids_today_count,
                '{{productbidstoday}}' => $item_bids_today_count,
                '{{failedlogincount}}' => $number_of_failed_logins_today_count,
                '{{failedloginalltime}}' => $number_of_failed_logins_alltime_count,
                '{{percentofmembernotsignedin}}' => $percent_of_members_not_signedin,
                '{{bulkuploadsessiontoday}}' => $number_of_bulk_upload_session_today,
                '{{totalamountofitemlisting}}' => $amount_of_item_listing_bulk_upload_session_today,
                '{{numberofpaidinvoicestoday}}' => $number_of_paid_invoices_today,
                '{{amountofpaidinvoicestoday}}' => $amount_of_paid_invoices_today,
                '{{numberofsavedsearchdigest}}' => $number_of_saved_search_digest,
                '{{numberofnotificationstoday}}' => $number_of_notifications_today,
                '{{numberofprivatemessagepost}}' => $number_of_private_message_post,
                '{{highestviewjobcategory}}' => $highest_viewed_job_catgory,
                '{{highestviewitemcategory}}' => $highest_viewed_item_catgory,
                '{{highestviewedjoblisting}}' => $highest_viewed_job_listing,
                '{{highestvieweditemlisting}}' => $highest_viewed_item_listing,
                '{{numberofaccountsdeposit}}' => $num_accounts_deposit,
                '{{totalamountdeposittoday}}' => $total_amount_deposit_today,
                '{{numaccountswithdraw}}' => $num_accounts_withdraw,
                '{{totalamountwithdraw}}' => $total_amount_withdraw_today,
                '{{numberofescrowrelese}}' => $number_of_escrow_payment_relese_today,
                '{{numberescrowaccountsfundpending}}' => $num_escrow_accounts_fund_pending,
                '{{totalamountinescrowfundpending}}' => $total_amount_in_escrow_fund_pending,
                '{{numberofescrowaccountcancel}}' => $number_of_escrow_accounts_cancel_today,
                '{{numescrowaccountsfunded}}' => $num_escrow_accounts_funded,
                '{{totalamountinescrowaccount}}' => $total_amount_in_escrow_account,
                '{{amountinescrowaccountrelese}}' => $amount_in_escrow_account_relese,
                '{{numberofsearchtoday}}' => $number_of_search_today,
                '{{numberofkeywordsenteredintosearch}}' => $number_of_keywords_entered_into_search,
		'{{numberofrefunds}}' => $number_of_refunds,
                '{{amountrefundtoday}}' => $total_amount_refund_today,
                '{{apihook}}' => $addon_apihook,
        );
        
        ($apihook = $ilance->api('cron_dailyreports_end')) ? eval($apihook) : false;
                           
        $ilance->email->logtype = 'dailyreport';
        $ilance->email->mail = SITE_EMAIL;
        $ilance->email->slng = fetch_site_slng();
        $ilance->email->get('cron_daily_reports');		
        $ilance->email->set($existing);
        $ilance->email->send();
        
        $ilance->timer->stop();
        log_cron_action('{_the_daily_marketplace_report_was_successfully_emailed_to} ' . SITE_EMAIL, $nextitem, $ilance->timer->get());
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>