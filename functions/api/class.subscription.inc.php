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
* Membership class to perform the majority of membership functionality in ILance.
*
* @package      iLance\Membership
* @version      4.0.0.8059
* @author       ILance
*/
class subscription
{
        /**
        * Function for processing a subscription plan payment from a previously generated unpaid subscription transaction.
        *
        * @param       integer      user id
        * @param       integer      invoice id
        * @param       string       method of payment (ipn or account)
        * @param       string       name of gateway which will be processing this payment (optional)
        * @param       string       gateway transaction id (from gateway provider) (optional)
        * @param       boolean      is refunded payment? (default false)
        * @param       string       gateway original transaction id (if payment is refunded by gateway)
        * @param       boolean      silent mode (return only true or false; default false)
        *
        * @return      mixed        for ipn processing, boolean is used, others will use a print_notice() function to end user.
        */
        function payment($userid = 0, $invoiceid = 0, $method = 'account', $gateway = '', $gatewaytxn = '', $isrefund = false, $originalgatewaytxn = '', $silentmode = false)
        {
                global $ilance, $phrase, $page_title, $area_title, $ilconfig, $ilpage;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng'; 
                if ($method == 'ipn')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                        AND (status = 'unpaid' OR status = 'scheduled')
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "invoices
                                        SET paid = '" . $res['totalamount'] . "',
                                        status = 'paid',
                                        paiddate = '" . DATETIME24H . "',
                                        referer = '" . $ilance->db->escape_string(REFERRER) . "',
                                        custommessage = '" . $ilance->db->escape_string($gatewaytxn) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                                AND invoiceid = '" . intval($res['invoiceid']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user
                                        SET paymethod = '" . $ilance->db->escape_string($gateway) . "'
                                        WHERE user_id = '" . $res['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                $ilance->accounting_payment->insert_income_spent($res['user_id'], sprintf("%01.2f", $res['totalamount']), 'credit');
                                $ilance->referral->update_referral_action('subscription', $res['user_id']);
                                $sql_subscription_plan = $ilance->db->query("
                                        SELECT subscriptionid, title_" . $slng. " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription 
                                        WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql_subscription_plan) > 0)
                                {
                                        $subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                        $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET active = 'yes',
                                                renewdate = '" . $subscription_renew_date . "',
                                                startdate = '" . DATETIME24H . "',
                                                autopayment = '1',
                                                subscriptionid = '" . $res['subscriptionid'] . "',
                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                invoiceid = '" . intval($res['invoiceid']) . "'
                                                WHERE user_id = '" . $res['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        $existing = array(
                                                '{{provider}}' => fetch_user('username', $res['user_id']),
                                                '{{invoice_id}}' => $res['invoiceid'],
                                                '{{invoice_amount}}' => $ilance->currency->format($res['totalamount']),
                                        );
                                        $ilance->email->mail = fetch_user('email', $res['user_id']);
                                        $ilance->email->slng = fetch_user_slng($res['user_id']);
                                        $ilance->email->get('subscription_fee_paid_creditcard');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('subscription_fee_paid_creditcard_admin');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        return true;                    
                                }
                        }
                        return false;
                }
                else if ($method == 'account')
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "invoices
                                WHERE invoiceid = '" . intval($invoiceid) . "'
                                        AND user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                if ($silentmode)
                                {
                                        return false;        
                                }
                                $area_title = '{_invoice_payment_menu_denied_subscription_payment_does_not_belong_to_user}';
                                $page_title = SITE_NAME . ' - {_invoice_payment_menu_denied_subscription_payment}';
                                print_notice('{_invoice_error}', '{_were_sorry_this_invoice_does_not_exist}'."<br /><br />".'{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
                                exit();
                        }
                        $res_invoiceprice = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $totalamount = (($res_invoiceprice['istaxable'] > 0 AND $res_invoiceprice['totalamount'] > 0) ? $res_invoiceprice['totalamount'] : $res_invoiceprice['amount']);
                        $sel_balance = $ilance->db->query("
                                SELECT available_balance, total_balance
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $res_balance = $ilance->db->fetch_array($sel_balance, DB_ASSOC);
                        if ($res_balance['available_balance'] <= $totalamount)
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
                        // pay the subscription fee invoice
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "invoices
                                SET paid = '" . $totalamount . "',
                                status = 'paid',
                                paiddate = '" . DATETIME24H . "',
                                paymethod = 'account',
                                referer = '" . $ilance->db->escape_string(REFERRER) . "'
                                WHERE user_id = '" . intval($userid) . "'
                                    AND invoiceid = '" . intval($invoiceid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $ilance->accounting_payment->insert_income_spent(intval($userid), $totalamount, 'credit');
                        $ilance->referral->update_referral_action('subscription', intval($userid));
                        $paymethod = 'account';
                        $_SESSION['ilancedata']['user']['active'] = 'yes';
                        $sql_subscription_plan = $ilance->db->query("
                                SELECT subscriptionid, title_" . $slng . " as title, description_" . $slng . " as description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res_invoiceprice['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_subscription_plan) > 0)
                        {
                                $subscription_plan_result = $ilance->db->fetch_array($sql_subscription_plan, DB_ASSOC);
                                $subscription_plan_cost = number_format($subscription_plan_result['cost'], 2);
                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                $ilance->db->query("
                                        UPDATE " . DB_PREFIX . "subscription_user
                                        SET paymethod = 'account',
                                        startdate = '" . DATETIME24H . "',
                                        renewdate = '" . $subscription_renew_date . "',
                                        autopayment = '1',
                                        active = 'yes',
                                        cancelled = '0',
                                        subscriptionid = '" . intval($res_invoiceprice['subscriptionid']) . "',
                                        roleid = '" . $subscription_plan_result['roleid'] . "',
                                        migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                        migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                        invoiceid = '" . intval($invoiceid) . "'
                                        WHERE user_id = '" . intval($userid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                $_SESSION['ilancedata']['user']['subscriptionid'] = $res_invoiceprice['subscriptionid'];
                        }
                        $new_total = ($res_balance['total_balance'] - $totalamount);
                        $new_avail = ($res_balance['available_balance'] - $totalamount);
                        // update account minus subscription fee amount
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET available_balance = '" . sprintf("%01.2f", $new_avail) . "',
                                total_balance = '" . sprintf("%01.2f", $new_total) . "'
                                WHERE user_id = '" . intval($userid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        $existing = array(
                                '{{provider}}' => fetch_user('username', intval($userid)),
                                '{{invoice_id}}' => intval($invoiceid),
                                '{{invoice_amount}}' => $ilance->currency->format($totalamount, $res_invoiceprice['currency_id']),
                        );
                        $ilance->email->mail = SITE_EMAIL;
                        $ilance->email->slng = fetch_site_slng();
                        $ilance->email->get('subscription_paid_online_account_admin');		
                        $ilance->email->set($existing);
                        if ($silentmode == false)
                        {
                                $ilance->email->send();
                        }
                        $ilance->email->mail = fetch_user('email', intval($userid));
                        $ilance->email->slng = fetch_user_slng(intval($userid));
                        $ilance->email->get('subscription_paid_online_account');		
                        $ilance->email->set($existing);
                        if ($silentmode == false)
                        {
                                $ilance->email->send();
                        }
                        if ($silentmode)
                        {
                                return true;
                        }
                        $area_title = '{_subscription_payment_via_online_account_complete}';
                        $page_title = SITE_NAME . ' - {_subscription_payment_via_online_account_complete}';
                        print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $ilpage['accounting'], '{_my_account}');
                        exit();
                }
        }
        
        /**
        * Function used to obtain the time left of a subscription.
        *
        * @param       integer        countdown
        *
        * @return      string         Returns time left
        */
        function subscription_countdown_timeleft($countdown)
        {
                global $phrase;
                $dif = $countdown;
                $ndays = floor($dif / 86400);
                $dif -= $ndays * 86400;
                $nhours = floor($dif / 3600);
                $dif -= $nhours * 3600;
                $nminutes = floor($dif / 60);
                $dif -= $nminutes * 60;
                $nseconds = $dif;
                $sign = '+';
                if ($countdown < 0) 
                {
                        $countdown = - $countdown;
                        $sign = '-';
                }
                if ($sign == '-') 
                {
                        $subscription_time_left = '{_subscription_expired}';
                }
                else 
                {
                        if ($ndays != '0') 
                        {
                                $subscription_time_left = $ndays . '{_d_shortform}' . ', ';	
                                $subscription_time_left .= $nhours . '{_h_shortform}' . '+ ';
                                $subscription_time_left .= $nminutes . '{_m_shortform}' . '+ ';
                                $subscription_time_left .= $nseconds . '{_s_shortform}' . '+';
                        }
                        elseif ($nhours != '0') 
                        {
                                $subscription_time_left = $nhours . '{_h_shortform}' . ', ';
                                $subscription_time_left .= $nminutes . '{_m_shortform}' . '+ ';
                                $subscription_time_left .= $nseconds . '{_s_shortform}' . '+';
                        }
                        else
                        {
                                $subscription_time_left = $nminutes . '{_m_shortform}' . ', ';
                                $subscription_time_left .= $nseconds . '{_s_shortform}' . '+';
                        }
                }
                $subscription_countdown = $subscription_time_left;
                return $subscription_countdown;
        }
            
        /**
        * Function used to obtain the subscription length (in days) from a supplied unit (D/M/Y) and length (in days)
        *
        * @param       string         unit (D or M or Y)
        * @param       integer        length in days
        *
        * @return      string         Returns time left
        */
        function subscription_length($units, $length)
        {
                $days = ($length < 1 ? 1 : $length);        
                switch ($units)
                {
                        case 'Y':
                        {
                                $value = 365 * intval($days);
                                break;
                        }                
                        case 'M':
                        {
                                $value = 30 * intval($days);
                                break;
                        }
                        case 'D':
                        {
                                $value = intval($days);
                                break;
                        }
                }
                return $value;
        }
        
        /**
        * Function to display any subscription alerts from their my account area
        *
        * @param       integer        user id
        *
        * @return      string         Returns HTML formatted text
        */
        function alerts($userid = 0)
        {
                global $ilance, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage, $SCRIPT_URL;
                $sql = $ilance->db->query("
                        SELECT active, cancelled
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['cancelled'])
                        {
                                $html = '{_you_have_cancelled_your_subscription_plan_your_subscription_plan_will_remain_active_until_the_expiration_date_your_account_will_not_be_billed}';
                        }
                        else
                        {
                                if ($res['active'] == 'no')
                                {
                                        $html = '{_please_optin_to_a_valid_subscription_plan_to_enable_access_permissions_to_your_online_account_failing_to_optin_to_a_subscription_plan_will_not_allow_you_to_participate}'.' <a href="' . HTTP_SERVER . $ilpage['subscription'].'">'.'{_click_here_to_upgrade_your_subscription}'.'</a>.';
                                }
                                else
                                {
                                        $html = '{_your_subscription_plan_is_active}'.'  <a href="' . HTTP_SERVER . $ilpage['subscription'].'">'.'{_click_here_to_view_other_subscription_plans}'.'</a>.';
                                }
                        }
                }
                else
                {
                        $html = '{_the_subscription_plan_system_is_currently_under_maintenance_and_will_be_available_shortly_thank_you_for_your_continued_patience}';
                }
                return $html;
        }
        
        /**
        * Function to display subscription plans within a pulldown menu element
        *
        * @return      string         Returns HTML pulldown menu element
        */
        function plans_pulldown()
        {
                global $ilance;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';   
                $html = '<select name="subscriptionid" class="select-250">';            
                $sql = $ilance->db->query("
                        SELECT subscriptionid, title_" . $slng . " AS title, cost, length, units
                        FROM " . DB_PREFIX . "subscription
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= '<option value="' . $res['subscriptionid'] . '">' . stripslashes($res['title']) . ' (' . $res['length'] . print_unit($res['units']) . ' - ' . $ilance->currency->format($res['cost']) . ')</option>';
                        }
                }            
                $html .= '</select>';
                return $html;
        }
            
        /**
        * Function to display for users any subscription plans within a pulldown menu element
        *
        * @return      string         Returns HTML pulldown menu element
        */
        function pulldown()
        {
                global $ilance, $phrase;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
                $html = '<select name="subscriptionid" class="select"><optgroup label="{_please_select}">';
                $sql = $ilance->db->query("
                        SELECT subscriptionid, title_" . $slng . " as title
                        FROM " . DB_PREFIX . "subscription
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $html .= '<option value="0">{_all_subscribers}</option>';                        
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= '<option value="' . $res['subscriptionid'] . '">' . stripslashes($res['title']) . '</option>';
                        }
                }
                $html .= '</optgroup></select>';
                return $html;
        }
            
        /**
        * Function to display any subscription alerts from their my account area
        *
        * @param       integer        user id
        *
        * @return      string         Returns the subscription plan as requested
        */
        function fetch_subscription_plan($userid = 0)
        {
                global $ilance, $phrase;
                $sql = $ilance->db->query("
                        SELECT subscriptionid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
                        $sql2 = $ilance->db->query("
                                SELECT title_" . $slng . " as title
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        $res2 = $ilance->db->fetch_array($sql2, DB_ASSOC);
                        return stripslashes($res2['title']);
                }
                else
                {
                        return '{_no_plan}';
                }
        }
            
        /**
        * Function to display any subscription plan exemptions within a pulldown menu element.
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function exemptions_pulldown()
        {
                global $ilance, $phrase;
                $html = '<select name="accessname" class="select">';            
                $sql = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "subscription_permissions
                        GROUP BY accessname
                        ORDER BY accessname ASC
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $html .= '<option value="' . $res['accessname'] . '">' . $res['accessname'] . ' - {_' . $res['accessname'] . '_text} (' . $res['accesstype'] . ')</option>';
                        }
                }            
                $html .= '</select>';
                return $html;
        }
            
        /**
        * Function to handle the subscription exemption upgrade process for end users.
        *
        * @param       integer        user id
        * @param       string         access permission name
        * @param       string         access permission value
        * @param       integer        cost for this exemption
        * @param       integer        days this exemption shall last for
        * @param       string         logic to use for determining what to do
        * @param       string         end user comments
        * @param       boolean        defines if this function should dispatch email once it's finished
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function construct_subscription_exemption($userid = 0, $accessname = '', $accessvalue = '', $cost = 0, $days = 0, $logic = '', $comments = '', $doemail = '')
        {
                global $ilance, $ilconfig, $phrase;
                $userid = isset($userid) ? intval($userid) : '';
                $accessname = isset($accessname) ? $accessname : '';
                $accessvalue = isset($accessvalue) ? $accessvalue : '';
                $cost = isset($cost) ? $cost : 0;
                $days = isset($days) ? $days : 7;
                $exemptfrom = DATETIME24H;
                $exemptto = $ilance->datetimes->fetch_date_fromnow($days) . ' ' . TIMENOW;
                $nofunds = 0;
                if ($userid == '')
                {
                        return 0;
                }
                if ($accessname == '')
                {
                        return 0;
                }
                if ($accessvalue == '')
                {
                        return 0;
                }
                if (isset($logic))
                {
                        $sql = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "subscription_user_exempt
                                WHERE user_id = '" . intval($userid) . "'
                                    AND accessname = '".$ilance->db->escape_string($accessname)."'
                                    AND active = '1'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) == 0)
                        {
                                switch ($logic)
                                {
                                        case 'active':
                                        {
                                                // insert permission and waive transaction fee for cost amount
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                $userid,
                                                0,
                                                0,
                                                0,
                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                sprintf("%01.2f", 0),
                                                sprintf("%01.2f", 0),
                                                'paid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                $comments,
                                                0,
                                                0,
                                                1);
                                                break;
                                        }
                                        case 'activepaid':
                                        {
                                                // insert permission and insert new paid transaction for cost amount
                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                $userid,
                                                0,
                                                0,
                                                0,
                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                sprintf("%01.2f", $cost),
                                                sprintf("%01.2f", $cost),
                                                'paid',
                                                'debit',
                                                'account',
                                                DATETIME24H,
                                                DATEINVOICEDUE,
                                                DATETIME24H,
                                                $comments,
                                                0,
                                                0,
                                                1);
                                                break;
                                        }                                        
                                        case 'activedebit':
                                        {
                                                // attempt to debit customers account for payment for permissions
                                                $sql = $ilance->db->query("
                                                        SELECT available_balance, total_balance
                                                        FROM " . DB_PREFIX . "users
                                                        WHERE user_id = '".$userid."'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql) > 0)
                                                {
                                                        $res = $ilance->db->fetch_array($sql);
                                                        if ($cost <= $res['available_balance'])
                                                        {
                                                                // customer has sufficient funds
                                                                $invoiceid = $ilance->accounting->insert_transaction(
                                                                0,
                                                                0,
                                                                0,
                                                                $userid,
                                                                0,
                                                                0,
                                                                0,
                                                                'Subscription Permission Exemption: '.$accessname.' (From: '.$exemptfrom.' To: '.$exemptto.')',
                                                                sprintf("%01.2f", $cost),
                                                                sprintf("%01.2f", $cost),
                                                                'paid',
                                                                'debit',
                                                                'account',
                                                                DATETIME24H,
                                                                DATEINVOICEDUE,
                                                                DATETIME24H,
                                                                $comments,
                                                                0,
                                                                0,
                                                                1);
                                                            
                                                                // debit amount from online account
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "users
                                                                        SET available_balance = available_balance - $cost,
                                                                        total_balance = total_balance - $cost
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else 
                                                        {
                                                                $nofunds = 1;
                                                        }
                                                }
                                                break;
                                        }
                                }
                                if ($nofunds == 0)
                                {
                                        // create new exemption
                                        $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "subscription_user_exempt
                                        (user_id, accessname, value, exemptfrom, exemptto, comments, invoiceid, active) 
                                        VALUES (
                                        '" . intval($userid) . "',
                                        '" . $accessname . "',
                                        '" . $accessvalue . "',
                                        '" . $exemptfrom . "',
                                        '" . $exemptto . "',
                                        '" . $comments . "',
                                        '" . intval($invoiceid) . "',
                                        '1')", 0, null, __FILE__, __LINE__);
                                        
                                        return 1;
                                }
                                else 
                                {
                                        return 0;	
                                }
                        }
                        else
                        {
                                return 0;
                        }
                }
                else 
                {
                        return 0;	
                }
        }
        
        /**
        * Function to handle the subscription upgrade process for end users.
        *
        * @param       integer        user id
        * @param       integer        subscription id
        * @param       boolean        end user agreement of terms value (true / false)
        * @param       boolean        end user instant payment value (true / false)
        * @param       boolean        defines if the subscription cost is zero or not
        * @param       boolean        defines if the transaction will be using the recurring subscription logic
        * @param       string         payment method chosen by the end user
        * @param       boolean        defines if this transaction is a recurring subscription modification or not
        * @param       boolean        defines if this function should automatically delete any previous free or paid subscription transactions to reduce the amount of pending invoices in the admincp
        * @param       string         return url (optional)
        *
        * @return      string         Returns the subscription exemptions as requested
        */
        function subscription_upgrade_process($userid = 0, $subscriptionid = 0, $agreecheck = 0, $instantpay = 0, $nocost = 0, $recurring = 0, $paymethod = '', $ismodify = 0, $removepending = false, $returnurl = '')
        {
                global $ilance, $form, $phrase, $iltemplate, $page_title, $area_title, $ilconfig, $ilpage, $show, $hidden_form_start, $hidden_form_end, $cardtype_pulldown, $ilcrumbs, $navcrumb;
                // #### REMOVE ANY PENDING SUBSCRIPTION TRANSACTIONS ###########
                if (isset($removepending) AND $removepending)
                {
                        // removing latest pending transactions before we process the new one
                        $sql = $ilance->db->query("
                                SELECT subscriptionid, transactionid, amount
                                FROM " . DB_PREFIX . "invoices
                                WHERE subscriptionid > 0
				    AND user_id = '" . intval($userid) . "'
				    AND (status = 'scheduled' OR status = 'pending')
                                ORDER BY createdate DESC
                                LIMIT 1
                        ");
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                // old transaction exists! let's remove it!
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "invoices
                                        WHERE user_id = '" . intval($userid) . "'
					    AND transactionid = '" . $res['transactionid'] . "'
					    AND invoicetype = 'subscription'
                                        LIMIT 1
                                ");
                        }
                        unset($sql, $res);
                }
                // #### FREE SUBSCRIPTION PLAN #################################
                if (isset($nocost) AND $nocost)
                {
			$invoice_due_date = DATEINVOICEDUE;
                        $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';  
                        $sql = $ilance->db->query("
                                SELECT subscriptionid, title_" . $slng . " as title, description_" . $slng . " as description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . intval($subscriptionid) . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $subscription_plan_result = $ilance->db->fetch_array($sql, DB_ASSOC);
                                $subscription_plan_cost = $subscription_plan_result['cost'];
                                if ($subscription_plan_cost <= 0)
                                {
                                        // customer agree to site terms?
                                        if (isset($agreecheck) AND $agreecheck)
                                        {
                                                $area_title = '{_subscription_upgrade_via_online_account_process}';
                                                $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_process}';
                                                $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                        intval($subscriptionid),
                                                        0,
                                                        0,
                                                        intval($userid),
                                                        0,
                                                        0,
                                                        0,
                                                        '{_subscription_payment_for}' . ' ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                        sprintf("%01.2f", $subscription_plan_cost),
                                                        sprintf("%01.2f", $subscription_plan_cost),
                                                        'paid',
                                                        'subscription',
                                                        'account',
                                                        DATETIME24H,
                                                        $invoice_due_date,
                                                        DATETIME24H,
                                                        '',
                                                        0,
                                                        0,
                                                        1
                                                );
						$subscription_item_name = '{_subscription_payment_for}' . ' ' . stripslashes($subscription_plan_result['title']) . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')';
						$ilance->template->templateregistry['subscription_item_name'] = $subscription_item_name;
						$subscription_item_name = $ilance->template->parse_template_phrases('subscription_item_name');
                                                $subscription_item_cost = sprintf("%01.2f", $subscription_plan_cost);
                                                $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                $sqlcheck = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "subscription_user
                                                        WHERE user_id = '" . intval($userid) . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sqlcheck) > 0)
                                                {
                                                        // set subscription to active
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                SET active = 'yes',
								    renewdate = '" . $subscription_renew_date . "',
								    startdate = '" . DATETIME24H . "',
								    subscriptionid = '" . intval($subscriptionid) . "',
								    migrateto = '" . $subscription_plan_result['migrateto'] . "',
								    migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
								    invoiceid = '" . $subscription_invoice_id . "',
								    roleid = '" . $subscription_plan_result['roleid'] . "',
								    cancelled = '0'
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                else
                                                {
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, cancelled, roleid, migrateto, migratelogic, invoiceid)
                                                                VALUES(
                                                                NULL,
                                                                '" . intval($subscriptionid) . "',
                                                                '" . intval($userid) . "',
                                                                'account',
                                                                '" . DATETIME24H . "',
                                                                '" . $subscription_renew_date . "',
                                                                '1',
                                                                'yes',
                                                                '0',
                                                                '" . $subscription_plan_result['roleid'] . "',
                                                                '" . $subscription_plan_result['migrateto'] . "',
                                                                '" . $subscription_plan_result['migratelogic'] . "',
                                                                '" . $subscription_invoice_id . "')
                                                        ", 0, null, __FILE__, __LINE__);
                                                }
                                                // #### update subscription for user
                                                $_SESSION['ilancedata']['user']['subscriptionid'] = intval($subscriptionid);
                                                if (!empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no')
                                                {
                                                        $_SESSION['ilancedata']['user']['active'] = 'yes';
                                                }
                                                
                                                $ilance->email->mail = fetch_user('email', intval($userid));
                                                $ilance->email->slng = fetch_user_slng(intval($userid));
                                                $ilance->email->get('subscription_paid_online_account');		
                                                $ilance->email->set(array(
                                                        '{{provider}}' => fetch_user('username', intval($userid)),
                                                        '{{invoice_id}}' => $subscription_invoice_id,
                                                        '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost),
                                                ));
                                                $ilance->email->send();
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('subscription_paid_online_account_admin');		
                                                $ilance->email->set(array(
                                                        '{{provider}}' => fetch_user('username', intval($userid)),
                                                        '{{invoice_id}}' => $subscription_invoice_id,
                                                        '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost),
                                                ));
                                                $ilance->email->send();
                                                $area_title = '{_subscription_upgrade_via_online_account_process_complete}';
                                                $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_process_complete}';
                                                $url = !empty($returnurl) ? urldecode($returnurl) : HTTPS_SERVER . $ilpage['accounting'];
                                                $title = !empty($returnurl) ? '{_return_to_the_previous_page}' : '{_my_account}';
                                                print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $url, $title);
                                                exit();
                                        }
                                        else
                                        {
                                                $page_title = '{_subscription_denied_customer_did_not_agree_with_terms}';
                                                $area_name = SITE_NAME . ' - {_subscription_denied_customer_did_not_agree_with_terms}';
                                                print_notice('{_access_denied}', '{_subscription_denied_customer_did_not_agree_with_terms}', 'javascript:history.back(1);', '{_back}');
                                                exit();                                                
                                        }
                                }
                                else
                                {
                                        $page_title = '{_subscription_denied_invalid_subscription_information}';
                                        $area_name = SITE_NAME . ' - {_subscription_denied_invalid_subscription_information}';
                                        print_notice('{_access_denied}', '{_subscription_denied_invalid_subscription_information}', 'javascript:history.back(1);', '{_back}');
                                        exit();
                                }
                        }
                        else
                        {
                                $page_title = '{_subscription_denied_invalid_subscription_information}';
                                $area_name = SITE_NAME . ' - {_subscription_denied_invalid_subscription_information}';
                                print_notice('{_access_denied}', '{_subscription_denied_invalid_subscription_information}', 'javascript:history.back(1);', '{_back}');
                                exit();
                        }
                }
                // #### PAID SUBSCRIPTION PLAN #################################
                else
                {
                        // #### RECURRING SUBSCRIPTION LOGIC ###################
                        if ($recurring)
                        {
                                $navcrumb = array();
                                $navcrumb["$ilpage[subscription]"] = '{_subscription}';
                                $navcrumb[""] = '{_preview}';
                                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
                                $sql = $ilance->db->query("
                                        SELECT subscriptionid, title_" . $slng . " AS title, description_" . $slng . " AS description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        
                                        ($apihook = $ilance->api('recurring_subscription_logic_start')) ? eval($apihook) : false;  
                                        
                                        if (isset($paymethod))
                                        {
                                                // should $res['cost'] include taxes if applicable??
                                                $tax = 0;
                                                if ($ilance->tax->is_taxable(intval($userid), 'subscription'))
                                                {
                                                        $tax = $ilance->tax->fetch_amount(intval($userid), sprintf("%01.2f", $res['cost']), 'subscription', 0);
                                                }
                                                $res['cost'] = sprintf("%01.2f", $res['cost'] + $tax);
						unset($tax);
                                                $arr_month = array('' => 'MM', '01' => '01 - {_january}', '02' => '02 - {_february}','03' => '03 - {_march}','04' => '04 - {_april}','05' => '05 - {_may}','06' => '06 - {_june}','07' => '07 - {_july}','08' => '08 - {_august}','09' => '09 - {_september}','10' => '10 - {_october}','11' => '11 - {_november}','12' => '12 - {_december}');
						$monthpulldown = construct_pulldown('creditcard_month', 'creditcard_month', $arr_month, '', 'style="font-family: verdana"');
						$arr_year = array('' => 'YYYY');
						for ($i = 2013 ; $i < 2024 ; $i++)
						{
                                                        $arr_year[$i] = $i;
						}
						$yearpulldown = construct_pulldown('creditcard_year', 'creditcard_year', $arr_year, '', 'style="font-family: verdana"');
						unset($arr_month, $arr_year);
						$customencrypted = 'RECURRINGSUBSCRIPTION|' . intval($userid) . '|0|0|' . $res['length'] . '|' . $res['units'] . '|' . intval($subscriptionid) . '|' . $res['cost'] . '|' . $res['roleid'];
                                                switch ($paymethod)
                                                {
                                                	// #### PAYPAL PRO RECURRING SERVICE
                                                        case 'paypal_pro':
                                                        {
                                                        	$conf = array(
                                                                        'api_username' => trim($ilconfig['paypal_pro_username']), 
                                                                        'api_password' => trim($ilconfig['paypal_pro_password']), 
                                                                        'api_signature' => trim($ilconfig['paypal_pro_signature']), 
                                                                        'use_proxy' => '', 
                                                                        'proxy_host' => '', 
                                                                        'proxy_port' => '', 
                                                                        'return_url' => '', 
                                                                        'cancel_url' => ''
								);
                                                                $ilance->paypal_pro = construct_object('api.paypal_pro', $conf, $ilconfig['paypal_pro_sandbox']);
                                                                global $cc_type_pulldown, $amount;
                                                                $cc_type_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'creditcard_type');
                                                                $cost = convert_currency($ilance->currency->currencies['USD']['currency_id'], $res['cost'], $ilconfig['globalserverlocale_defaultcurrency']);
                                                                $fee = round(($cost * $ilconfig['paypal_pro_transaction_fee']) + $ilconfig['paypal_pro_transaction_fee2'], 2);
                                                                $total = $cost;// + $fee; //without fee
								$amount = $ilance->currency->format($total);
                                                                $hidden_form_start = $ilance->paypal_pro->print_recurring_payment_form($ilconfig['paypal_business_email'], $subscriptionid, $total, $res['units'], $res['length'], '{_recurring_subscription}', '{_recurring_subscription}', $ilconfig['paypal_pro_master_currency'], $customencrypted, $onsubmit = 'return validate_pp_email(this);', $ismodify);
                                                                $hidden_form_end = '</form>';
                                                                $pprint_array = array('amount','cc_type_pulldown','hidden_form_start','hidden_form_end','form','monthpulldown','yearpulldown');
                                                                $ilance->template->fetch('main', 'subscription_paypal_pro_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                        }	
                                                        // #### PAYPAL RECURRING SERVICE
                                                        case 'paypal':
                                                        {
                                                                $ilance->paypal = construct_object('api.paypal');
                                                                $hidden_form_start = $ilance->paypal->print_recurring_payment_form($ilconfig['paypal_business_email'], $subscriptionid, $res['cost'], $res['units'], $res['length'], '{_recurring_subscription}', '{_recurring_subscription}', $ilconfig['paypal_master_currency'], $customencrypted, $onsubmit = 'return validate_pp_email(this);', $ismodify);
                                                                $hidden_form_end = '</form>';
                                                                $pprint_array = array('hidden_form_start','hidden_form_end','form','monthpulldown','yearpulldown');
                                                                $ilance->template->fetch('main', 'subscription_paypal_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                        }
                                                        // #### MONEYBOOKERS RECURRING SERVICE
                                                        case 'moneybookers':
                                                        {
                                                                $ilance->moneybookers = construct_object('api.moneybookers');
                                                                $hidden_form_start = $ilance->moneybookers->print_recurring_payment_form($ilconfig['moneybookers_business_email'], $res['cost'], $res['units'], $res['length'], '{_recurring_subscription}', $ilconfig['moneybookers_master_currency'], $customencrypted, $onsubmit = 'return validate_mb_email(this);');
                                                                $hidden_form_end = '</form>';
                                                                $pprint_array = array('hidden_form_start','hidden_form_end','form','monthpulldown','yearpulldown');
                                                                $ilance->template->fetch('main', 'subscription_moneybookers_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                        }
                                                        // #### AUTHORIZE.NET RECURRING SERVICE
                                                        case 'authnet':
                                                        {
                                                                $ilance->authorizenet = construct_object('api.authorizenet');
                                                                $iscancel = $ismodify = 0;
                                                                $hidden_form_start = $ilance->authorizenet->print_recurring_payment_form(DATETODAY, $subscriptionid, $res['roleid'], $res['cost'], $totaloccurrences = 9999, $trialamount = 0, $trialoccurrences = 0, $res['units'], $res['length'], '{_recurring_subscription}', $onsubmit = 'return validate_authorizenet_info(this);', $ismodify, $iscancel);
                                                                $hidden_form_end = '</form>';
                                                                $cardtype_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'cardType');
                                                                $pprint_array = array('cardtype_pulldown','hidden_form_start','hidden_form_end','form','monthpulldown','yearpulldown');
                                                                $ilance->template->fetch('main', 'subscription_authnet_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                        }
                                                        // #### BLUEPAY RECURRING SERVICE
                                                        case 'bluepay':
                                                        {
                                                                $ilance->bluepay = construct_object('api.bluepay');
                                                                $iscancel = 0;
                                                                $hidden_form_start = $ilance->bluepay->print_recurring_payment_form(DATETODAY, $subscriptionid, $res['roleid'], $res['cost'], $totaloccurrences = 9999, $trialamount = 0, $trialoccurrences = 0, $res['units'], $res['length'], '{_recurring_subscription}', $onsubmit = 'return validate_bluepay_info(this);', $ismodify, $iscancel);
                                                                $hidden_form_end = '</form>';
                                                                $cardtype_pulldown = $ilance->accounting->creditcard_type_pulldown('', 'cardType');
                                                                $pprint_array = array('cardtype_pulldown','hidden_form_start','hidden_form_end','form','monthpulldown','yearpulldown');
                                                                $ilance->template->fetch('main', 'subscription_bluepay_recurring.html');
                                                                $ilance->template->parse_hash('main', array('ilpage' => $ilpage));
                                                                $ilance->template->parse_if_blocks('main');
                                                                $ilance->template->pprint('main', $pprint_array);
                                                                exit();
                                                        }
                                                }
                                                
                                                ($apihook = $ilance->api('subscription_upgrade_process_recurring_paymethod')) ? eval($apihook) : false;
                                        }
                                }
                        }
                        // #### REGULAR SUBSCRIPTION UPGRADE LOGIC #############
                        else
                        {
                                $subscription_plan_result = array();
                                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';
                                $sql = $ilance->db->query("
                                        SELECT subscriptionid, title_" . $slng . " as title, description_" . $slng . " as description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                                        FROM " . DB_PREFIX . "subscription
                                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $subscription_plan_result = $ilance->db->fetch_array($sql, DB_ASSOC);
                                        
                                        ($apihook = $ilance->api('regular_subscription_logic_start')) ? eval($apihook) : false;
                                        
                                        $subscription_plan_cost = $subscription_plan_result['cost'];
                                        $subscription_plan_cost_notax = $subscription_plan_cost;
                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                        $invoice_due_date = print_subscription_renewal_datetime($subscription_length);
                                        if ($agreecheck)
                                        {
                                                // is user taxable for this invoice type?
                                                $extrainvoicesql = "totalamount = '" . sprintf("%01.2f", $subscription_plan_cost_notax) . "',";
                                                if ($ilance->tax->is_taxable(intval($userid), 'subscription'))
                                                {
                                                        // fetch total amount to hold within the "totalamount" field
                                                        $subscription_plan_cost = ($subscription_plan_cost_notax + $ilance->tax->fetch_amount(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'subscription', 0));
                                                        
                                                        // fetch tax amount to charge for this invoice type
                                                        $taxamount = $ilance->tax->fetch_amount(intval($userid), $subscription_plan_cost_notax, 'subscription', 0);
                                                        
                                                        // fetch total amount to hold within the "totalamount" field
                                                        $totalamount = ($subscription_plan_cost_notax + $taxamount);
                                                        
                                                        // fetch tax bit to display when we display tax infos
                                                        $taxinfo = $ilance->tax->fetch_amount(intval($userid), $subscription_plan_cost_notax, 'subscription', 1);
                                                        
                                                        // #### extra bit to assign tax logic to the transaction 
                                                        $extrainvoicesql = "
                                                                istaxable = '1',
                                                                totalamount = '" . sprintf("%01.2f", $totalamount) . "',
                                                                taxamount = '" . sprintf("%01.2f", $taxamount) . "',
                                                                taxinfo = '" . $ilance->db->escape_string($taxinfo) . "',
                                                        ";
                                                }
                                                // does customer take advantage of instant payment from online account balance?
                                                if ($instantpay)
                                                {
                                                        $area_title = '{_subscription_upgrade_via_online_account_process}';
                                                        $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_process}';
                                                        // create scheduled subscription invoice transaction
                                                        $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                                intval($subscriptionid),
                                                                0,
                                                                0,
                                                                intval($userid),
                                                                0,
                                                                0,
                                                                0,
                                                                '{_subscription_payment_for} ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                sprintf("%01.2f", $subscription_plan_cost_notax),
                                                                '',
                                                                'scheduled',
                                                                'subscription',
                                                                'account',
                                                                DATETIME24H,
                                                                $invoice_due_date,
                                                                '',
                                                                '',
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET
                                                                $extrainvoicesql
                                                                isfvf = '0'
                                                                WHERE invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $subscription_item_name = '{_subscription_payment_for} ' . stripslashes($subscription_plan_result['title']) . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')';
                                                        $ilance->template->templateregistry['subscription_item_name'] = $subscription_item_name;
                                                        $subscription_item_name = $ilance->template->parse_template_phrases('subscription_item_name');
                                                        $subscription_item_cost = $subscription_plan_cost;
                                                        $insorupd = $ilance->db->query("
                                                                SELECT *
                                                                FROM " . DB_PREFIX . "subscription_user
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($insorupd) > 0)
                                                        {
                                                                // set payment method to online account and auto payments to active
                                                                $ilance->db->query("
                                                                        UPDATE " . DB_PREFIX . "subscription_user
                                                                        SET paymethod = 'account',
                                                                        autopayment = '1',
                                                                        migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                        migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                        roleid = '" . $subscription_plan_result['roleid'] . "',
                                                                        invoiceid = '" . $subscription_invoice_id . "',
                                                                        cancelled = '0'
                                                                        WHERE user_id = '" . intval($userid) . "'
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        else
                                                        {
                                                                $ilance->db->query("
                                                                        INSERT INTO " . DB_PREFIX . "subscription_user
                                                                        (id, subscriptionid, user_id, paymethod, autopayment, active, roleid, migrateto, migratelogic, invoiceid)
                                                                        VALUES(
                                                                        NULL,
                                                                        '" . intval($subscriptionid) . "',
                                                                        '" . intval($userid) . "',
                                                                        'account',
                                                                        '1',
                                                                        'no',
                                                                        '" . $subscription_plan_result['roleid'] . "',
                                                                        '" . $subscription_plan_result['migrateto'] . "',
                                                                        '" . $subscription_plan_result['migratelogic'] . "',
                                                                        '" . $subscription_invoice_id . "')
                                                                ", 0, null, __FILE__, __LINE__);
                                                        }
                                                        // calculate subscription renewal date
                                                        $subscription_length = $this->subscription_length($subscription_plan_result['units'], $subscription_plan_result['length']);
                                                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                                                        $sqlgetacc = $ilance->db->query("
                                                                SELECT total_balance, available_balance
                                                                FROM " . DB_PREFIX . "users
                                                                WHERE user_id = '" . intval($userid) . "'
                                                        ", 0, null, __FILE__, __LINE__);
                                                        if ($ilance->db->num_rows($sqlgetacc) > 0)
                                                        {
                                                                $resgetacc = $ilance->db->fetch_array($sqlgetacc, DB_ASSOC);
                                                                if ($resgetacc['available_balance'] >= $subscription_plan_cost)
                                                                {
                                                                        $new_total = sprintf("%01.2f", $resgetacc['total_balance'] - $subscription_plan_cost);
                                                                        $new_avail = sprintf("%01.2f", $resgetacc['available_balance'] - $subscription_plan_cost);
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET available_balance = '" . $new_avail . "',
                                                                                total_balance = '" . $new_total . "'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // update invoice with payment from online account balance
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "invoices
                                                                                SET paid = '" . $subscription_plan_cost . "',
                                                                                status = 'paid',
                                                                                paiddate = '" . DATETIME24H . "'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                                    AND invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                                    AND invoicetype = 'subscription'
                                                                                    AND subscriptionid = '" . intval($subscriptionid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // track income spent
                                                                        $ilance->accounting_payment->insert_income_spent(intval($userid), sprintf("%01.2f", $subscription_plan_cost), 'credit');
                                                                        $bidtotal = $ilance->permissions->check_access($userid, 'bidlimitperday');
                                                                        $bidsleft = ($bidtotal - fetch_bidcount_today($userid)) * (-1);
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET bidstoday = '" . $bidsleft . "'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // upgrade customers subscription plan
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "subscription_user
                                                                                SET active = 'yes',
                                                                                renewdate = '" . $subscription_renew_date . "',
                                                                                startdate = '" . DATETIME24H . "',
                                                                                subscriptionid = '" . intval($subscriptionid) . "',
                                                                                roleid = '" . $subscription_plan_result['roleid'] . "',
                                                                                migrateto = '" . $subscription_plan_result['migrateto'] . "',
                                                                                migratelogic = '" . $subscription_plan_result['migratelogic'] . "',
                                                                                invoiceid = '" . $subscription_invoice_id . "',
                                                                                cancelled = '0'
                                                                                WHERE user_id = '" . intval($userid) . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $_SESSION['ilancedata']['user']['subscriptionid'] = intval($subscriptionid);
                                                                        if (!empty($_SESSION['ilancedata']['user']['active']) AND $_SESSION['ilancedata']['user']['active'] == 'no')
                                                                        {
                                                                                $_SESSION['ilancedata']['user']['active'] = 'yes';
                                                                        }
                                                                        
                                                                        $ilance->email->mail = fetch_user('email', intval($userid));
                                                                        $ilance->email->slng = fetch_user_slng(intval($userid));
                                                                        $ilance->email->get('subscription_paid_online_account');		
                                                                        $ilance->email->set(array(
                                                                                '{{provider}}' => fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost)
                                                                        ));
                                                                        $ilance->email->send();
                                                                        $ilance->email->mail = SITE_EMAIL;
                                                                        $ilance->email->slng = fetch_site_slng();
                                                                        $ilance->email->get('subscription_paid_online_account_admin');		
                                                                        $ilance->email->set(array(
                                                                                '{{provider}}' => fetch_user('username', intval($userid)),
                                                                                '{{invoice_id}}' => $subscription_invoice_id,
                                                                                '{{invoice_amount}}' => $ilance->currency->format($subscription_item_cost)
                                                                        ));
                                                                        $ilance->email->send();
                                                                        $area_title = '{_subscription_upgrade_via_online_account_process_complete}';
                                                                        $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_process_complete}';
                                                                        $url = !empty($returnurl) ? urldecode($returnurl) : HTTPS_SERVER . $ilpage['accounting'];
                                                                        $title = !empty($returnurl) ? '{_return_to_the_previous_page}' : '{_my_account}';
                                                                        
                                                                        ($apihook = $ilance->api('regular_subscription_payment_end')) ? eval($apihook) : false;
                                                                        
                                                                        print_notice('{_invoice_payment_complete}', '{_your_invoice_has_been_paid_in_full}', $url, $title);
                                                                        exit();
                                                                }
                                                                else
                                                                {
                                                                        $area_title = '{_subscription_upgrade_via_online_account_process_denied}';
                                                                        $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_process_denied}';
                                                                        print_notice('{_invoice_payment_warning_insufficient_funds}', '{_were_sorry_this_invoice_can_not_be_paid_due_to_insufficient_funds}' . '<br /><br />' . '{_please_contact_customer_support}', $ilpage['accounting'], '{_my_account}');
                                                                        exit();
                                                                }        
                                                        }
                                                }
                                                else
                                                {
                                                        // no instant payment selected by user
                                                        $area_title = '{_subscription_upgrade_via_online_account_creating_new_invoice}';
                                                        $page_title = SITE_NAME . ' - {_subscription_upgrade_via_online_account_creating_new_invoice}';
                                                        // create scheduled subscription transaction to be paid
                                                        $subscription_invoice_id = $ilance->accounting->insert_transaction(
                                                                intval($subscriptionid),
                                                                0,
                                                                0,
                                                                intval($userid),
                                                                0,
                                                                0,
                                                                0,
                                                                '{_subscription_payment_for} ' . $subscription_plan_result['title'] . ' (' . $subscription_plan_result['length'] . print_unit($subscription_plan_result['units']) . ')',
                                                                sprintf("%01.2f", $subscription_plan_cost_notax),
                                                                '',
                                                                'scheduled',
                                                                'subscription',
                                                                'account',
                                                                DATETIME24H,
                                                                $invoice_due_date,
                                                                '',
                                                                '',
                                                                0,
                                                                0,
                                                                1
                                                        );
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoices
                                                                SET
                                                                $extrainvoicesql
                                                                isfvf = '0'
                                                                WHERE invoiceid = '" . intval($subscription_invoice_id) . "'
                                                                LIMIT 1
                                                        ", 0, null, __FILE__, __LINE__);
                                                        
                                                        ($apihook = $ilance->api('regular_subscription_payment_unpaid_end')) ? eval($apihook) : false;
                                                        
                                                        refresh(HTTPS_SERVER . $ilpage['invoicepayment'] . '?id=' . $subscription_invoice_id);
                                                        exit();
                                                }
                                        }
                                        else
                                        {
                                                $page_title = '{_subscription_denied_customer_did_not_agree_with_terms}';
                                                $area_name = SITE_NAME . ' - {_subscription_denied_customer_did_not_agree_with_terms}';
                                                print_notice('{_access_denied}', '{_subscription_denied_customer_did_not_agree_with_terms}', 'javascript:history.back(1);', '{_back}');
                                                exit();
                                        }
                                }
                                else
                                {
                                        $page_title = '{_subscription_denied_invalid_subscription_information}';
                                        $area_name = SITE_NAME . ' - {_subscription_denied_invalid_subscription_information}';
                                        print_notice('{_access_denied}', '{_subscription_denied_invalid_subscription_information}', 'javascript:history.back(1);', '{_back}');
                                        exit();
                                }
                        }
                }
        }
        
        /**
        * Function to update a users subscription plan within the AdminCP
        *
        * @param       integer      user id
        * @param       integer      subscription id
        * @param       string       transaction description
        * @param       string       subscription action
        */
        function subscription_upgrade_process_admincp($userid = 0, $subscriptionid = 0, $txndescription = '{_no_description}', $action = '')
        {
                global $ilance, $ilconfig, $phrase;
                $slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng'; 
                $sql = $ilance->db->query("
                        SELECT subscriptionid, title_" . $slng . " as title, description_" . $slng . " as description, cost, length, units, subscriptiongroupid, roleid, active, canremove, visible_registration, visible_upgrade, icon, migrateto, migratelogic
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql, DB_ASSOC);
                        $subscription_length = $this->subscription_length($res['units'], $res['length']);
                        $subscription_renew_date = print_subscription_renewal_datetime($subscription_length);
                        // #### MARK ACTIVE - NEW TRANSACTION IS CREATED ###############
                        if ($action == 'active')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status, invoicetype, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '0.00',
                                        '0.00',
                                        '0.00',
                                        'paid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string('{_subscription_fee_waived_by_administration}') . "',
                                        '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        // #### MARK ACTIVE PAID - PAYMENT MADE OUTSIDE OF MARKET ######
                        else if ($action == 'activepaid')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, totalamount, status, invoicetype, createdate, duedate, paiddate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        '" . $res['cost'] . "',
                                        'paid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . DATETIME24H . "',
                                        '" . $ilance->db->escape_string('{_subscription_fee_payment_paid_outside_marketplace_thank_you_for_your_business}') . "',
                                        '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'yes',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'yes',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                        // #### MARK INACTIVE & UNPAID - WILL REQUIRE PAYMENT ##########
                        else if ($action == 'inactive')
                        {
                                $ilance->db->query("
                                        INSERT INTO " . DB_PREFIX . "invoices
                                        (invoiceid, subscriptionid, user_id, description, amount, paid, status, invoicetype, createdate, duedate, custommessage, transactionid, archive)
                                        VALUES(
                                        NULL,
                                        '" . intval($subscriptionid) . "',
                                        '" . intval($userid) . "',
                                        '" . $ilance->db->escape_string($txndescription) . "',
                                        '" . $res['cost'] . "',
                                        '',
                                        'unpaid',
                                        'subscription',
                                        '" . DATETIME24H . "',
                                        '" . DATEINVOICEDUE . "',
                                        '" . $ilance->db->escape_string('{_thank_you_for_your_continued_business}') . "',
                                        '" . $ilance->accounting_payment->construct_transaction_id() . "',
                                        '0')
                                ", 0, null, __FILE__, __LINE__);
                                $newinvoiceid = $ilance->db->insert_id();
                                $sql = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "subscription_user
                                        WHERE user_id = '" . intval($userid) . "'
                                        LIMIT 1
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql) > 0)
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "subscription_user
                                                SET subscriptionid = '" . intval($subscriptionid) . "',
                                                startdate = '" . DATETIME24H . "',
                                                renewdate = '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                autopayment = '1',
                                                active = 'no',
                                                cancelled = '0',
                                                migrateto = '" . $res['migrateto'] . "',
                                                migratelogic = '" . $res['migratelogic'] . "',
                                                invoiceid = '" . $newinvoiceid . "',
                                                roleid = '" . $newroleid . "'
                                                WHERE user_id = '" . intval($userid) . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                                else
                                {
                                        $newroleid = $this->fetch_subscription_roleid(intval($subscriptionid));
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "subscription_user
                                                (id, subscriptionid, user_id, paymethod, startdate, renewdate, autopayment, active, migrateto, migratelogic, invoiceid, roleid)
                                                VALUES(
                                                NULL,
                                                '" . intval($subscriptionid) . "',
                                                '" . intval($userid) . "',
                                                'account',
                                                '" . DATETIME24H . "',
                                                '" . $ilance->db->escape_string($subscription_renew_date) . "',
                                                '1',
                                                'no',
                                                '" . $res['migrateto'] . "',
                                                '" . $res['migratelogic'] . "',
                                                '" . $newinvoiceid . "',
                                                '" . $newroleid . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
        
        /**
        * Function to internally check if a user has an active subscription plan (paid or free).
        *
        * @param       integer        user id
        *
        * @return      bool           Returns true or false
        */
        function has_active_subscription($userid = 0)
        {
                global $ilance;
                $sql = $ilance->db->query("
                        SELECT active, cancelled
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
                        if ($res['active'] == 'yes' AND $res['cancelled'] == '0')
                        {
                                return true;
                        }
                }
                return false;
        }
        
        /**
        * Function to print a user's subscription title
        *
        * @param        integer     user id
        *
        * @return	string      Returns the subscription title
        */
        function print_subscription_title($userid = 0)
        {
                global $ilance, $phrase;
                $sql = $ilance->db->query("
                        SELECT subscriptionid
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        $res = $ilance->db->fetch_array($sql);
			$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';   
                        $sql2 = $ilance->db->query("
                                SELECT title_" . $slng . " as title
                                FROM " . DB_PREFIX . "subscription
                                WHERE subscriptionid = '" . $res['subscriptionid'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                $res2 = $ilance->db->fetch_array($sql2);
                                return stripslashes($res2['title']);
                        }
                }
                return '{_registered_subscriber}';
        }
        
        /**
        * Function to print a user's subscription icon
        *
        * @param        integer     user id
        *
        * @return	string      Returns the subscription icon
        */
        function print_subscription_icon($userid = 0)
        {
                global $ilance, $phrase, $iltemplate, $ilconfig;
		$slng = isset($_SESSION['ilancedata']['user']['slng']) ? $_SESSION['ilancedata']['user']['slng'] : 'eng';   
                $sql = $ilance->db->query("
                        SELECT u.subscriptionid, s.icon, s.title_" . $slng . " as title
                        FROM " . DB_PREFIX . "subscription_user AS u 
				LEFT JOIN " . DB_PREFIX . "subscription AS s ON u.subscriptionid = s.subscriptionid
                        WHERE user_id = '" . intval($userid) . "'
                ", 0, null, __FILE__, __LINE__);
		if ($ilance->db->num_rows($sql) > 0)
                {
                        $res2 = $ilance->db->fetch_array($sql, DB_ASSOC);
                        return '<span title="' . handle_input_keywords(stripslashes($res2['title'])) . '"><img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $res2['icon'] . '" border="0" alt="" style="vertical-align: middle;margin-top:-5px" /></span>';
                }
                return '<span title="{_registered_member}"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/default.gif" border="0" alt="" style="vertical-align: middle;margin-top:-5px" /></span>';
        }
        
        /**
        * Function to dispatch subscription notifications to users "x" days before a subscription is expired
        * This function is run via iLance automation script (cron.dailyrfp.php)
        *
        * @param        integer     days to remind user before expiry (default 7)
        *
        * @return	string      Returns the cron log bit information to append to the cron job log for actions taken within this function
        */
        function send_subscription_expiry_reminders($reminddays = 7)
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $sent = 0;
                $cronlog = '';
                // since this cron script will run once per day, let fetch
                // upcoming subscriptions in x days and send a friendly reminder
                // informing the user about the subscription renewal
                $remind = $ilance->db->query("
                        SELECT user_id, renewdate
                        FROM " . DB_PREFIX . "subscription_user
                        WHERE cancelled = '0'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($remind) > 0)
                {
                        while ($reminds = $ilance->db->fetch_array($remind, DB_ASSOC))
                        {
                                // renew date
                                $date1split = explode(' ', $reminds['renewdate']);
                                $date2split = explode('-', $date1split[0]);
                                // days left for subscription count (ex: reminder in 7 days from now)
                                $reminder = $reminddays;
                                $days = $ilance->datetimes->fetch_days_between(date('m'), date('d'), date('Y'), $date2split[1], $date2split[2], $date2split[0]);
                                if ($days == $reminder)
                                {
                                        $user = $ilance->db->query("
                                                SELECT username, first_name, last_name, email
                                                FROM " . DB_PREFIX . "users
                                                WHERE user_id = '" . $reminds['user_id'] . "'
                                                    AND status = 'active'
                                                    AND email != ''
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($user) > 0)
                                        {
                                                $res_user = $ilance->db->fetch_array($user, DB_ASSOC);
                                                // #### QUICK EMAIL LOG CHECK > DID USER RECEIVE THIS EMAIL TODAY?
                                                $sql_emaillog = $ilance->db->query("
                                                        SELECT *
                                                        FROM " . DB_PREFIX . "emaillog
                                                        WHERE logtype = 'subscriptionremind'
                                                            AND user_id = '" . $reminds['user_id'] . "'
                                                            AND date LIKE '%" . DATETODAY . "%'
                                                            AND sent = 'yes'
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilance->db->num_rows($sql_emaillog) == 0)
                                                {
                                                        $ilance->db->query("
                                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                                (emaillogid, logtype, user_id, date, sent)
                                                                VALUES(
                                                                NULL,
                                                                'subscriptionremind',
                                                                '" . $reminds['user_id'] . "',
                                                                '" . DATETODAY . "',
                                                                'yes')
                                                        ", 0, null, __FILE__, __LINE__);
                                                        $ilance->email->mail = $res_user['email'];
                                                        $ilance->email->slng = fetch_user_slng($reminds['user_id']);                                                        
                                                        $ilance->email->get('upcoming_subscription_reminder');		
                                                        $ilance->email->set(array(
                                                                '{{days}}' => $days,
                                                                '{{customer}}' => ucfirst($res_user['first_name']) . ' ' . ucfirst($res_user['last_name']) . ' (' . ucfirst($res_user['username']) . ')',
                                                                '{{datetime}}' => DATETODAY . ' ' . TIMENOW,
                                                        ));                                                        
                                                        $ilance->email->send();
                                                        $sent++;
                                                }
                                        }
                                }		
                        }
                        $cronlog .= 'Sent uncoming subscription reminders to ' . $sent . ' users, ';
                }
                return $cronlog;
        }
        
        /**
        * Function to dispatch newsletter subscription notifications for latest listings posted that users have opted in
        * This function is run via iLance automation script (cron.dailyrfp.php)
        *
        * @return       string      Return string with information for cron log
        */
        function send_category_notification_subscriptions()
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                
                $cronlog = '';
                if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                        $seller_array = $new_projects_array = $emailsDuplicatePrevention = array();
                        
                        // fetch service auctions posted yesterday
                        $newproducts = $ilance->db->query("
                                SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE date_added LIKE '%" . DATEYESTERDAY . "%'
                                        AND status = 'open'
                                        AND project_details != 'invite_only'
                                        AND project_state = 'service'
                                        AND visible = '1'
                        ", 0, null, __FILE__, __LINE__);
                        while ($row = $ilance->db->fetch_array($newproducts, DB_ASSOC))
                        {
                                $new_projects_array[] = $row;
                        }
                        unset($row);
                        if (count($new_projects_array) > 0)
                        {
                                // fetch sellers with active service newsletter category subscriptions
                                $users = $ilance->db->query("
                                        SELECT user_id, username, email, notifyservicescats, country, zip_code, city
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'active'
                                                AND notifyservices = '1'
                                                AND notifyservicescats != ''
                                                AND emailnotify = '1'
                                                AND email != ''
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($users) > 0)
                                {
                                        while ($row = $ilance->db->fetch_array($users, DB_ASSOC))
                                        {
                                                if (!in_array($row['email'], $emailsDuplicatePrevention))
                                                {
                                                        $sellers[] = $row;
                                                        $emailsDuplicatePrevention[] = $row['email'];
                                                }
                                        }
                                        unset($row);
                                        if (!empty($sellers))
                                        {
                                                $sent = 0;
                                                foreach ($sellers AS $seller)
                                                {
                                                        $messagebody = '';
                                                        $requested_categories = explode(',', $seller['notifyservicescats']);
                                                        $projectsToSend = array();
                                                        foreach ($requested_categories AS $category)
                                                        {
                                                                if ($category > 0)
                                                                {
                                                                        // fetch category's children recursively
                                                                        $tempchildren = $ilance->categories->fetch_children($category, 'service');
                                                                        $children = explode(',', $tempchildren);
                                                                        unset($tempchildren);
                                                                        foreach ($new_projects_array AS $new_project)
                                                                        {
                                                                                if (in_array($new_project['cid'], $children))
                                                                                {
                                                                                        $projectsToSend[] = $new_project;
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        if (count($projectsToSend) > 0)
                                                        {
                                                                foreach ($projectsToSend AS $project)
                                                                {
                                                                        $buyerinfo = $ilance->db->query("
                                                                                SELECT username
                                                                                FROM " . DB_PREFIX . "users
                                                                                WHERE user_id = '" . $project['user_id'] . "'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($buyerinfo) > 0)
                                                                        {
                                                                                $res_buyer_name = $ilance->db->fetch_array($buyerinfo, DB_ASSOC);
                                                                                $messagebody .= strip_vulgar_words(un_htmlspecialchars(stripslashes($project['project_title'])), false) . "\n";
                                                                                // todo: check for seo
                                                                                $messagebody .= HTTP_SERVER . "rfp.php?id=" . $project['project_id'] . "\n";
                                                                                $messagebody .= '{_category}' . ": " . strip_tags($ilance->categories->recursive($project['cid'], 'service', fetch_user_slng($seller['user_id']), 1, '', 0)) . "\n";
                                                                                $messagebody .= '{_buyer}' . ": " . $res_buyer_name['username'] . "\n";
                                                                                $messagebody .= '{_ends}' . ": " . print_date($project['date_end']) . "\n";
                                                                                $messagebody .= "************\n";
                                                                        }
                                                                }
                                                                $messagebody .= "\n";
                                                                $messagebody .= '{_gain_access_to_place_bids_sell_and_promote_your_services_find_out_how}'."\n\n";
                                                                $messagebody .= '{_providing_services}' . ":\n";
                                                                $messagebody .= HTTP_SERVER . "main.php?cmd=selling\n\n";
                                                                $messagebody .= '{_buying_services}' . ":\n";
                                                                $messagebody .= HTTP_SERVER . "main.php?cmd=buying\n\n";
                                                                $messagebody .= "************\n";
                                                                $messagebody .= '{_please_contact_us_if_you_require_any_additional_information_were_always_here_to_help}';
                                                                // #### QUICK EMAIL LOG CHECK > DID USER RECEIVE THIS EMAIL TODAY??
                                                                $sql_emaillog = $ilance->db->query("
                                                                        SELECT emaillogid
                                                                        FROM " . DB_PREFIX . "emaillog
                                                                        WHERE logtype = 'dailyservice'
                                                                            AND user_id = '" . $seller['user_id'] . "'
                                                                            AND date LIKE '%" . DATETODAY . "%'
                                                                            AND sent = 'yes'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($sql_emaillog) == 0)
                                                                {
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                                                (emaillogid, logtype, user_id, date, sent)
                                                                                VALUES(
                                                                                NULL,
                                                                                'dailyservice',
                                                                                '" . $seller['user_id'] . "',
                                                                                '" . DATETODAY . "',
                                                                                'yes')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // just for reference so we can show the user the exact date we sent email last
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET lastemailservicecats = '" . DATETODAY . "'
                                                                                WHERE user_id = '" . $seller['user_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $ilance->email->mail = $seller['email'];
                                                                        $ilance->email->slng = fetch_user_slng($seller['user_id']);                                                                
                                                                        $ilance->email->get('cron_daily_auction_newsletter');		
                                                                        $ilance->email->set(array(
                                                                                '{{username}}' => fetch_user('username', $seller['user_id']),
                                                                                '{{newsletterbody}}' => $messagebody,
                                                                                '{{total}}' => count($projectsToSend),
                                                                        ));                                                                
                                                                        $ilance->email->send();
                                                                        $sent++;
                                                                }
                                                        }
                                                }
                                                unset($sellers);
                                                $cronlog .= 'Sent service auction daily newsletter to ' . $sent . ' users, ';
                                                unset($sent);
                                        }
                                }
                        }
                }
                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        $new_projects_array = $seller_array = $emailsDuplicatePrevention = array();
                        $newprojects = $ilance->db->query("
                                SELECT *, UNIX_TIMESTAMP(date_end) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS mytime, UNIX_TIMESTAMP(date_starts) - UNIX_TIMESTAMP('" . DATETIME24H . "') AS starttime
                                FROM " . DB_PREFIX . "projects
                                WHERE date_added LIKE '%" . DATEYESTERDAY . "%'
                                        AND status = 'open'
                                        AND project_details != 'invite_only'
                                        AND project_state = 'product'
                                        AND visible = '1'
                        ", 0, null, __FILE__, __LINE__);
                        while ($row = $ilance->db->fetch_array($newprojects, DB_ASSOC))
                        {
                                $new_projects_array[] = $row;
                        }
                        if (count($new_projects_array) > 0)
                        {
                                // fetch sellers with active category subscriptions
                                $users = $ilance->db->query("
                                        SELECT user_id, username, email, notifyproductscats, country, zip_code, city
                                        FROM " . DB_PREFIX . "users
                                        WHERE status = 'active'
                                                AND notifyproducts = '1'
                                                AND notifyproductscats != ''
                                                AND emailnotify = '1'
                                                AND email != ''
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($users) > 0)
                                {
                                        while ($row = $ilance->db->fetch_array($users, DB_ASSOC))
                                        {
                                                if (!in_array($row['email'], $emailsDuplicatePrevention))
                                                {
                                                        $sellers[] = $row;
                                                        $emailsDuplicatePrevention[] = $row['email'];
                                                }
                                        }
                                        unset($row);
                                        if (!empty($sellers) AND count($sellers) > 0)
                                        {
                                                $sent = 0;
                                                foreach ($sellers AS $seller)
                                                {
                                                        $messagebody = '';
                                                        $requested_categories = explode(',', $seller['notifyproductscats']);
                                                        $projectsToSend = array();
                                                        foreach ($requested_categories AS $category)
                                                        {
                                                                if ($category > 0)
                                                                {
                                                                        $tempchildren = $ilance->categories->fetch_children($category, 'product');
                                                                        $children = explode(',', $tempchildren);
                                                                        unset($tempchildren);
                                                                        foreach ($new_projects_array AS $new_project)
                                                                        {
                                                                                if (in_array($new_project['cid'], $children))
                                                                                {
                                                                                        $projectsToSend[] = $new_project;
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                        if (count($projectsToSend) > 0)
                                                        {
                                                                foreach ($projectsToSend AS $project)
                                                                {
                                                                        $buyerinfo = $ilance->db->query("
                                                                                SELECT username
                                                                                FROM " . DB_PREFIX . "users
                                                                                WHERE user_id = '" . $project['user_id'] . "'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($buyerinfo) > 0)
                                                                        {
                                                                                $res_buyer_name = $ilance->db->fetch_array($buyerinfo, DB_ASSOC);
                                                                                $messagebody .= strip_vulgar_words(stripslashes($project['project_title']), false) . "\n";
                                                                                // todo: check for seo
                                                                                $messagebody .= HTTP_SERVER . "merch.php?id=" . $project['project_id'] . "\n";
                                                                                $messagebody .= '{_category}' . ": " . strip_tags($ilance->categories->recursive($project['cid'], 'product', fetch_user_slng($seller['user_id']), 1, '', 0)) . "\n";
                                                                                $messagebody .= '{_seller}' . ": " . $res_buyer_name['username'] . "\n";
                                                                                $messagebody .= '{_ends}' . ": " . print_date($project['date_end']) . "\n";
                                                                                $messagebody .= "************\n";
                                                                        }
                                                                }
                                                                $messagebody .= "\n";
                                                                $messagebody .= '{_sell_merchandise_via_product_auctions}' . ":\n";
                                                                $messagebody .= HTTP_SERVER . "main.php?cmd=selling\n\n";
                                                                $messagebody .= '{_browse_product_auctions_and_other_merchandise}' . ":\n";
                                                                $messagebody .= HTTP_SERVER . "merch.php?cmd=listings\n\n";
                                                                $messagebody .= "************\n";
                                                                $messagebody .= '{_please_contact_us_if_you_require_any_additional_information_were_always_here_to_help}';
                                                                // #### QUICK EMAIL LOG CHECK > DID USER RECEIVE THIS EMAIL TODAY?
                                                                $sql_emaillog = $ilance->db->query("
                                                                        SELECT *
                                                                        FROM " . DB_PREFIX . "emaillog
                                                                        WHERE logtype = 'dailyproduct'
                                                                            AND user_id = '" . $seller['user_id'] . "'
                                                                            AND date LIKE '%" . DATETODAY . "%'
                                                                            AND sent = 'yes'
                                                                ", 0, null, __FILE__, __LINE__);
                                                                if ($ilance->db->num_rows($sql_emaillog) == 0)
                                                                {
                                                                        $ilance->db->query("
                                                                                INSERT INTO " . DB_PREFIX . "emaillog
                                                                                (emaillogid, logtype, user_id, date, sent)
                                                                                VALUES(
                                                                                NULL,
                                                                                'dailyproduct',
                                                                                '" . $seller['user_id'] . "',
                                                                                '" . DATETODAY . "',
                                                                                'yes')
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        // just for reference so we can show the user the exact date we sent email last
                                                                        $ilance->db->query("
                                                                                UPDATE " . DB_PREFIX . "users
                                                                                SET lastemailproductcats = '" . DATETODAY . "'
                                                                                WHERE user_id = '" . $seller['user_id'] . "'
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        $ilance->email->mail = $seller['email'];
                                                                        $ilance->email->slng = fetch_user_slng($seller['user_id']);                                                                
                                                                        $ilance->email->get('cron_daily_auction_newsletter');		
                                                                        $ilance->email->set(array(
                                                                                '{{username}}' => fetch_user('username', $seller['user_id']),
                                                                                '{{newsletterbody}}' => $messagebody,
                                                                                '{{total}}' => count($projectsToSend),
                                                                        ));                                                                
                                                                        $ilance->email->send();
                                                                        $sent++;
                                                                }
                                                        }
                                                }
                                        }
                                        unset($sellers);
                                        $cronlog .= 'Sent product auction daily newsletter to ' . $sent . ' users, ';
                                        unset($sent);
                                }
                        }        
                }
                return $cronlog;
        }
        
        /**
        * Function to cancel any scheduled subscription invoices based on a timer which the admin defines in max days of invoice cancellation
        *
        * @return       string      Return string with information for cron log
        */
        function cancel_scheduled_subscription_invoices()
        {
                global $ilance, $phrase, $ilconfig;
                $cronlog = '';
                $schsub = $ilance->db->query("
                        SELECT *
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                                AND (status = 'unpaid' OR status = 'scheduled')
                                AND paiddate = '0000-00-00 00:00:00'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($schsub) > 0)
                {
                        while ($unpaid = $ilance->db->fetch_array($schsub))	
                        {
                                $date1split = explode(' ', $unpaid['createdate']);
                                $date2split = explode('-', $date1split[0]);
                                $totaldaysunpaid = $ilance->datetimes->fetch_days_between($date2split[1], $date2split[2], $date2split[0], date('m'), date('d'), date('Y'));
                                if ($totaldaysunpaid > $ilconfig['invoicesystem_maximumpaymentdays'])
                                {
                                        // cancel this scheduled subscription invoice (no longer being used)
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "invoices 
                                                SET status = 'cancelled'
                                                WHERE invoiceid = '" . $unpaid['invoiceid'] . "'
                                                LIMIT 1
                                        ");
                                }
                        }
                }
                return $cronlog;
        }
        
        /**
        * Function designed to send out subscription reminder notices based on an admin defined email dispatch frequency
        * This function is called from cron.reminders.php
        *
        * @return       string      Return string with information for cron log
        */
        function send_user_subscription_frequency_reminders()
        {
                global $ilance, $phrase, $ilconfig, $ilpage;
                
                $cronlog = '';
                $count = 0;
                $remindfrequency = $ilance->datetimes->fetch_date_fromnow($ilconfig['invoicesystem_resendfrequency']);
                $expiry = $ilance->db->query("
                        SELECT user_id, invoiceid, invoicetype, createdate, description, amount, paid, totalamount, invoicetype, duedate, transactionid, istaxable, taxamount, taxinfo
                        FROM " . DB_PREFIX . "invoices
                        WHERE invoicetype = 'subscription'
                                AND (status = 'unpaid' OR status = 'scheduled')
                                AND amount > 0
                                AND archive = '0'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($expiry) > 0)
                {
                        while ($reminder = $ilance->db->fetch_array($expiry, DB_ASSOC))
                        {
                                $user = $ilance->db->query("
                                        SELECT email, first_name, last_name, username
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $reminder['user_id'] . "'
                                                AND status = 'active'
                                                AND email != ''
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($user) > 0)
                                {
                                        $res_user = $ilance->db->fetch_array($user, DB_ASSOC);
                                        $logs = $ilance->db->query("
                                                SELECT invoicelogid, date_sent, date_remind
                                                FROM " . DB_PREFIX . "invoicelog
                                                WHERE user_id = '" . $reminder['user_id'] . "'
                                                        AND invoiceid = '" . $reminder['invoiceid'] . "'
                                                ORDER BY invoicelogid DESC
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                        if ($ilance->db->num_rows($logs) == 0)
                                        {
                                                $ilance->db->query("
                                                        INSERT INTO " . DB_PREFIX . "invoicelog
                                                        (invoicelogid, user_id, invoiceid, invoicetype, date_sent, date_remind)
                                                        VALUES(
                                                        NULL,
                                                        '" . $reminder['user_id'] . "',
                                                        '" . $reminder['invoiceid'] . "',
                                                        '" . $reminder['invoicetype'] . "',
                                                        '" . DATETODAY . "',
                                                        '" . $remindfrequency . "')
                                                ", 0, null, __FILE__, __LINE__);
                                                if ($ilconfig['invoicesystem_unpaidreminders'])
                                                {
                                                	$crypted = array('id' => $reminder['invoiceid']);
							$invoiceurl = HTTP_SERVER . $ilpage['invoicepayment'] . '?crypted='. encrypt_url($crypted);
                                                        $ilance->email->mail = $res_user['email'];
                                                        $ilance->email->slng = fetch_user_slng($reminder['user_id']);
                                                        $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                        $ilance->email->set(array(
                                                                '{{username}}' => $res_user['username'],
                                                                '{{firstname}}' => $res_user['first_name'],
                                                                '{{description}}' => $reminder['description'],
                                                                '{{transactionid}}' => $reminder['transactionid'],
                                                                '{{amount}}' => $ilance->currency->format($reminder['amount']),
                                                                '{{total}}' => $ilance->currency->format($reminder['totalamount']),
                                                                '{{paid}}' => $ilance->currency->format($reminder['paid']),
								'{{tax}}' => (($reminder['istaxable']) ? $ilance->currency->format($reminder['taxamount']) : '-'),
                                                                '{{duedate}}' => print_date($reminder['duedate']),
                                                                '{{invoiceid}}' => $reminder['invoiceid'],
                                                                '{{reminddate}}' => $remindfrequency,
                                                                '{{membershipurl}}' => HTTP_SERVER . $ilpage['subscription'],
                                                                '{{invoiceurl}}' => $invoiceurl
                                                        ));
                                                        $ilance->email->send();
                                                        $count++;
                                                }                                                
                                        }
                                        else if ($ilance->db->num_rows($logs) > 0)
                                        {
                                                // it appears we have a log for this invoice id ..
                                                $reslogs = $ilance->db->fetch_array($logs, DB_ASSOC);
                                                // time to send an update to this user for this invoice
                                                // make sure we didn't already send one today
                                                if ($reslogs['date_remind'] == DATETODAY AND $reslogs['date_sent'] == DATETODAY)
                                                {
                                                        // we've sent a reminder to this user for this invoice today already.. do nothing until next reminder frequency
                                                }
                                                else if ($reslogs['date_remind'] == DATETODAY AND $reslogs['date_sent'] != DATETODAY)
                                                {
                                                        // time to send a new frequency reminder.. update table with new email sent date as today
                                                        $ilance->db->query("
                                                                UPDATE " . DB_PREFIX . "invoicelog
                                                                SET date_sent = '" . DATETODAY . "',
                                                                date_remind = '" . $remindfrequency . "'
                                                                WHERE invoiceid = '" . $reminder['invoiceid'] . "'
                                                                        AND user_id = '" . $reminder['user_id'] . "'
                                                        ");
                                                        if ($ilconfig['invoicesystem_unpaidreminders'])
                                                        {
                                                        	$crypted = array('id' => $reminder['invoiceid']);
								$invoiceurl = HTTP_SERVER . $ilpage['invoicepayment'] . '?crypted='. encrypt_url($crypted);
                                                                $ilance->email->mail = $res_user['email'];
                                                                $ilance->email->slng = fetch_user_slng($reminder['user_id']);
                                                                $ilance->email->get('cron_expired_subscription_invoice_reminder');		
                                                                $ilance->email->set(array(
                                                                        '{{username}}' => $res_user['username'],
                                                                        '{{firstname}}' => $res_user['first_name'],
                                                                        '{{description}}' => $reminder['description'],
                                                                        '{{transactionid}}' => $reminder['transactionid'],
                                                                        '{{amount}}' => $ilance->currency->format($reminder['amount']),
                                                                        '{{total}}' => $ilance->currency->format($reminder['totalamount']),
                                                                        '{{paid}}' => $ilance->currency->format($reminder['paid']),
									'{{tax}}' => (($reminder['istaxable']) ? $ilance->currency->format($reminder['taxamount']) : '-'),
                                                                        '{{duedate}}' => print_date($reminder['duedate']),
                                                                        '{{invoiceid}}' => $reminder['invoiceid'],
                                                                        '{{reminddate}}' => $remindfrequency,
                                                                        '{{membershipurl}}' => HTTP_SERVER . $ilpage['subscription'],
                                                                        '{{invoiceurl}}' => $invoiceurl
                                                                ));
                                                                $ilance->email->send();
                                                                $count++;
                                                        }
                                                }
                                        }
                                }
                        }
                }
                if ($count > 0)
                {
                        $cronlog .= $count . ' membership email invoice frequency reminders sent, ';
                }
                return $cronlog;
        }
        
        /**
        * Function to dispatch emails based on users saved searches where they choose to opt-in
        * This function is run via iLance automation script (cron.dailyrfp.php)
        *
        * @param        integer     limit (default 50)
        * @param        boolean     force email to send always when function is called (default false)
        *
        * @return       string      Return string with information for cron log
        */
        function send_saved_search_subscriptions($limit = 50, $forceemail = false)
        {
                global $ilance, $ilconfig, $phrase, $ilpage, $show;
                if ($ilconfig['savedsearches'] == false)
                {
                        return;
                }
                
                $limit = intval($limit); 
                $cronlog = '';
                if ($ilconfig['globalauctionsettings_serviceauctionsenabled'])
                {
                        $sqlextra = "AND lastsent NOT LIKE '%" . DATETODAY . "%'";
                        if ($forceemail)
                        {
                                $sqlextra = "";
                        }
                        // #### SERVICES #######################################
                        $sql = $ilance->db->query("
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE cattype = 'service'
                                        AND subscribed = '1'
                                $sqlextra
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $lastseen = $lastseenids = $last = array();
                                        $url = HTTP_SERVER . $ilpage['search'] . '?do=array&list=list' . html_entity_decode($res['searchoptions']);
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_URL, $url);
                                        $results = curl_exec($c);
                                        curl_close($c);
                                        if (!empty($res['lastseenids']) AND is_serialized($res['lastseenids']))
                                        {
                                                $lastseen = unserialize($res['lastseenids']);
                                        }
                                        $messagebody = '';
                                        if (!empty($results))
                                        {
                                                $results = urldecode($results);
                                                if (is_serialized($results))
                                                {
                                                        $results = unserialize($results);
                                                        $messagebody .= '<table border="0" cellspacing="5" cellpadding="5">';
                                                        $sent = 0;
                                                        foreach ($results AS $key => $listing)
                                                        { // services found
                                                                foreach ($listing AS $field => $value)
                                                                { // fields
                                                                        if ($field == 'project_id' AND !in_array($value, $lastseen))
                                                                        { // save item id's so we don't resend duplicates in future (on a different day)
                                                                                $lastseenids[] = $value;
                                                                        }
                                                                }
                                                                if ($sent <= $limit)
                                                                {
                                                                        $sql_attach = $ilance->db->query("
                                                                                SELECT filehash
                                                                                FROM " . DB_PREFIX . "attachment 
                                                                                WHERE project_id = '" . $listing['project_id'] . "' 
                                                                                        AND attachtype = 'project'
                                                                                LIMIT 1
                                                                        ", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($sql_attach) > 0)
                                                                        {
                                                                                $res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
                                                                                $item_photo = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attach['filehash'] . '" border="0" alt="" id="itemphoto_' . $listing['project_id'] . '" />';
                                                                        }
                                                                        else 
                                                                        {
                                                                                $item_photo = '<img src="' . HTTP_SERVER . $ilconfig['template_imagesfolder'] . 'nophoto.gif" border="0" alt="" id="itemphoto_' . $listing['project_id'] . '" />';
                                                                        }
                                                                        unset($sql_attach, $res_attach);
                                                                        $listing['currentbid_plain'] = isset($listing['currentbid_plain']) ? $listing['currentbid_plain'] : '-';
                                                                        $messagebody .= '<tr><td width="50" align="left"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $listing['project_id'] . '">' . $item_photo .'</a></td><td align="left"><span style="font-size:14px;color:#900"><a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $listing['project_id'] . '">' . strip_vulgar_words(un_htmlspecialchars(stripslashes($listing['title_plain']))) . '</a></span> <span style="font-size:14px;color:black">(' . $listing['project_id'] . ')</span><br /><span style="font-size:14px">' . print_username(fetch_auction('user_id', $listing['project_id']), 'plain', 0, '', '') . '</span><br /><span style="font-size:14px;color:black">{_average_bid}: ' . $listing['averagebid_plain'] . '</span><br /><span style="font-size:14px;color:black">{_budget} ' . strip_tags($listing['budget']) . '</span><br /><span style="font-size:14px;color:black">{_time_left}: ' . strip_tags($listing['timeleft']) . '</span></td></tr>';
                                                                        $sent++;
                                                                }
                                                        }
                                                        $messagebody .= '</table>';
                                                }
                                        }
                                        if (!empty($lastseenids) AND is_array($lastseenids))
                                        {
                                                if (!empty($lastseen) AND is_array($lastseen))
                                                {
                                                        $last = array_merge($lastseenids, $lastseen);
                                                }
                                                else
                                                {
                                                        $last = $lastseenids;
                                                }
                                                $ilance->email->mail = fetch_user('email', $res['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_service_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => nl2br($messagebody),
                                                ));                                                
                                                $ilance->email->send();
                                                $last = serialize($last);
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "search_favorites
                                                        SET lastseenids = '" . $ilance->db->escape_string($last) . "',
                                                        lastsent = '" . DATETIME24H . "'
                                                        WHERE searchid = '" . $res['searchid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }        
                                }
                        }
                        // #### EXPERTS ########################################
                        $sql = $ilance->db->query("
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE cattype = 'experts'
                                        AND subscribed = '1'
                                        $sqlextra
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                        	require_once(DIR_CORE . 'functions_search.php');
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $lastseen = $lastseenids = $last = array();
                                        $url = HTTP_SERVER . $ilpage['search'] . '?do=array&list=list' . html_entity_decode($res['searchoptions']);
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_URL, $url);
                                        $results = curl_exec($c);
                                        curl_close($c);
                                        if (!empty($res['lastseenids']) AND is_serialized($res['lastseenids']))
                                        {
                                                $lastseen = unserialize($res['lastseenids']);
                                        }
                                        if (!empty($results))
                                        {
                                                $results = urldecode($results);
                                                if (is_serialized($results))
                                                {
                                                        $results = unserialize($results);
                                                        $messagebody = '<table border="0" cellspacing="5" cellpadding="5">';
                                                        $sent = 0;
                                                        foreach ($results AS $key => $listing)
                                                        { // experts found
                                                                foreach ($listing AS $field => $value)
                                                                { // fields
                                                                        if ($field == 'user_id' AND !in_array($value, $lastseen))
                                                                        { // save item id's so we don't resend duplicates in future (on a different day)
                                                                                $lastseenids[] = $value;
                                                                        }
                                                                }
                                                                if ($sent <= $limit)
                                                                {
                                                                        $sql_attach = $ilance->db->query("
                                                                                SELECT filehash
                                                                                FROM " . DB_PREFIX . "attachment 
                                                                                WHERE user_id = '" . $listing['user_id'] . "' 
                                                                                        AND attachtype = 'profile'
                                                                                LIMIT 1
                                                            		", 0, null, __FILE__, __LINE__);
                                                                        if ($ilance->db->num_rows($sql_attach) > 0)
                                                                        {
                                                                                $res_attach = $ilance->db->fetch_array($sql_attach, DB_ASSOC);
                                                                                $item_photo = '<img src="' . ((PROTOCOL_REQUEST == 'https') ? HTTPS_SERVER : HTTP_SERVER) . $ilpage['attachment'] . '?cmd=thumb&amp;id=' . $res_attach['filehash'] . '" border="0" alt="" id="expert_' . $listing['user_id'] . '" />';
                                                                        }
                                                                        else 
                                                                        {
                                                                                $item_photo = '<img src="' . HTTP_SERVER . $ilconfig['template_imagesfolder'] . 'nophoto2.gif" border="0" heigth="100" width="100" alt="" id="expert_' . $listing['user_id'] . '" />';
                                                                        }
                                                                        unset($sql_attach, $res_attach);
                                                                        $messagebody .= '<tr><td width="50" align="left"><a href="' . HTTP_SERVER . $ilpage['members'] . '?id=' . $listing['user_id'] . '">' . $item_photo .'</a></td><td align="left"><span style="font-size:14px;color:#900"><a href="' . HTTP_SERVER . $ilpage['members'] . '?id=' . $listing['user_id'] . '">' . print_username($listing['user_id'], 'plain') . '</a></span><br /><span style="font-size:14px;color:black">{_hourly_rate}: ' . strip_tags($listing['rateperhour']) . '</span><br /><span style="font-size:14px;color:black">{_skills}: ' . print_skills($listing['user_id'], 50, true) . '</span></td></tr>';
                                                                        $sent++;
                                                                }
                                                        }
                                                        $messagebody .= '</table>';
                                                }
                                        }
                                        if (!empty($lastseenids) AND is_array($lastseenids))
                                        {
                                                if (!empty($lastseen) AND is_array($lastseen))
                                                {
                                                        $last = array_merge($lastseenids, $lastseen);
                                                }
                                                else
                                                {
                                                        $last = $lastseenids;
                                                }
                                                $ilance->email->mail = fetch_user('email', $res['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_expert_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => nl2br($messagebody),
                                                ));                                                
                                                $ilance->email->send();
                                                $last = serialize($last);
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "search_favorites
                                                        SET lastseenids = '" . $ilance->db->escape_string($last) . "',
                                                        lastsent = '" . DATETIME24H. "'
                                                        WHERE searchid = '" . $res['searchid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }        
                                }
                        }
                }
                if ($ilconfig['globalauctionsettings_productauctionsenabled'])
                {
                        // #### product auctions ###############################
                        // 1. select all subscriptions from search_favorites where subscribed = 1 and lastsent != today for products
                        $sqlextra = "AND lastsent NOT LIKE '%" . DATETODAY . "%'";
                        if ($forceemail)
                        {
                                $sqlextra = "";
                        }
                        $sql = $ilance->db->query("
                                SELECT searchid, user_id, searchoptions, searchoptionstext, title, added, lastseenids
                                FROM " . DB_PREFIX . "search_favorites
                                WHERE cattype = 'product'
                                        AND subscribed = '1'
                                $sqlextra
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql) > 0)
                        {
                                while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                                {
                                        $lastseen = $lastseenids = $last = array();
                                        $url = HTTP_SERVER . $ilpage['search'] . '?do=array&list=list' . html_entity_decode($res['searchoptions']);
                                        $c = curl_init();
                                        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
                                        curl_setopt($c, CURLOPT_URL, $url);
                                        $results = curl_exec($c);
                                        curl_close($c);
                                        if (!empty($res['lastseenids']) AND is_serialized($res['lastseenids']))
                                        {
                                                $lastseen = unserialize($res['lastseenids']);
                                        }
                                        if (!empty($results))
                                        {
                                                $results = urldecode($results);
                                                if (is_serialized($results))
                                                {
                                                        $results = unserialize($results);
                                                        $messagebody = '<table border="0" cellspacing="0" cellpadding="12">';
                                                        $sent = 0;
                                                        foreach ($results AS $key => $listing)
                                                        { // items found
                                                                foreach ($listing AS $field => $value)
                                                                { // fields
                                                                        if ($field == 'project_id' AND !in_array($value, $lastseen))
                                                                        { // save item id's so we don't resend duplicates in future (on a different day)
                                                                                $lastseenids[] = $value;
                                                                        }
                                                                }
                                                                if ($sent <= $limit)
                                                                {
                                                                        $url = HTTP_SERVER . $ilpage['merch'] . '?id=' . $listing['project_id'];
                                                                        $item_photo = $ilconfig['globalauctionsettings_seourls']
                                                                                ? $ilance->auction->print_item_photo(construct_seo_url('productauctionplain', 0, $listing['project_id'], $listing['title_plain'], '', 0, '', 0, 0), 'thumb', $listing['project_id'], 1, '#ffffff', 0, '', true, 1, true)
                                                                                : $ilance->auction->print_item_photo($ilpage['merch'] . '?id=' . $listing['project_id'], 'thumb', $listing['project_id'], 1, '#ffffff', 0, '', true, 1, true);
                                                                                        
                                                                        ($apihook = $ilance->api('send_saved_search_subscriptions_items_loop_start')) ? eval($apihook) : false;
                                                                        
                                                                        $listing['currentbid_plain'] = isset($listing['currentbid_plain']) ? $listing['currentbid_plain'] : '-';
                                                                        $messagebody .= '<tr><td width="50" align="left">' . $item_photo .'</td><td align="left"><span style="font-size:13px;color:#900"><a href="' . $url . '">' . strip_vulgar_words(un_htmlspecialchars(stripslashes($listing['title_plain']))) . '</a></span> <span style="font-size:13px;color:black">(' . $listing['project_id'] . ')</span><br /><span style="font-size:13px;color:black">{_ends}: ' . $listing['endtime'] . '</span></td></tr>';
                                                                        $sent++;
                                                                }
                                                        }
                                                        $messagebody .= '</table>';
                                                }
                                        }
                                        if (!empty($lastseenids) AND is_array($lastseenids))
                                        {
                                                if (!empty($lastseen) AND is_array($lastseen))
                                                {
                                                        $last = array_merge($lastseenids, $lastseen);
                                                }
                                                else
                                                {
                                                        $last = $lastseenids;
                                                }
                                                $ilance->email->mail = fetch_user('email', $res['user_id']);
                                                $ilance->email->slng = fetch_user_slng($res['user_id']);
                                                $ilance->email->dohtml = true;
                                                $ilance->email->logtype = 'alert';                                                                                                
                                                $ilance->email->get('cron_send_product_saved_searches');		
                                                $ilance->email->set(array(
                                                        '{{searchtitle}}' => un_htmlspecialchars(stripslashes($res['title'])),
                                                        '{{searchoptions}}' => un_htmlspecialchars($res['searchoptionstext']),					  
                                                        '{{username}}' => fetch_user('username', $res['user_id']),
                                                        '{{messagebody}}' => $messagebody,
                                                ));                                                
                                                $ilance->email->send();
                                                $last = serialize($last);
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "search_favorites
                                                        SET lastseenids = '" . $ilance->db->escape_string($last) . "',
                                                        lastsent = '" . DATETIME24H. "'
                                                        WHERE searchid = '" . $res['searchid'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                        }
                                }
                        }
                }
                return $cronlog;
        }
        
        /**
        * Function to expire the saved search subscriptions after x days defined in the argument
        *
        * @param        integer     days (default 30)
        */
        function expire_saved_search_subscriptions($days = 30)
        {
                global $ilance, $ilconfig, $phrase, $ilpage;
                if ($ilconfig['savedsearches'] == false)
                {
                        return;
                }
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "search_favorites
                        SET subscribed = '0', lastseenids = ''
                        WHERE added < DATE_SUB(CURDATE(), INTERVAL $days DAY)
                ", 0, null, __FILE__, __LINE__);
        }
        
        /**
        * Function to fetch the roleid of a particular subscription plan id
        *
        * @param        integer     subscription id
        *
        * @return	integer     Returns the role id
        */
        function fetch_subscription_roleid($subscriptionid = 0)
        {
                global $ilance, $phrase, $ilconfig;
                $roleid = 0;
                $sql = $ilance->db->query("
                        SELECT roleid
                        FROM " . DB_PREFIX . "subscription
                        WHERE subscriptionid = '" . intval($subscriptionid) . "'
                        LIMIT 1
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $roleid = $res['roleid'];
                        }
                }
                return $roleid;
        }
        
        /**
        * Function to determine if any membership plans need to be removed due to users being deleted from other functions within the user table
        *
        * @return	integer     Returns true or false
        */
        function remove_unlinked_memberships()
        {
                global $ilance, $ilconfig;
                $sql = $ilance->db->query("
                        SELECT user_id
                        FROM " . DB_PREFIX . "subscription_user
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
                        {
                                $sql2 = $ilance->db->query("
                                        SELECT user_id
                                        FROM " . DB_PREFIX . "users
                                        WHERE user_id = '" . $res['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) == 0)
                                {
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "subscription_user
                                                WHERE user_id = '" . $res['user_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
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