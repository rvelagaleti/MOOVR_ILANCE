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

($apihook = $ilance->api('cron_creditcards_start')) ? eval($apihook) : false;

if ($ilconfig['use_internal_gateway'] != 'none')
{
        
        
        // expire cards that have not started authentication
        $days_ago_expired_noauth = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $ilconfig['admin_cc_expired_days'], date('Y')));
        $days_ago_expired_yesauth = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - $ilconfig['admin_cc_auth_expired_days'], date('Y')));
        
        $sql_noauthattempts = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE authorized = 'no'
                    AND date_added LIKE '%" . $ilance->db->escape_string($days_ago_expired_noauth) . "%'
                    AND auth_amount1 = ''
                    AND auth_amount2 = ''
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_noauthattempts) > 0)
        {
                while ($res_noauthattempts = $ilance->db->fetch_array($sql_noauthattempts, DB_ASSOC))
                {
                        $sql_user = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $res_noauthattempts['user_id'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_user) > 0)
                        {
                                $res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
                                // remove the card from the db
                                $ilance->db->query("
                                        DELETE FROM " . DB_PREFIX . "creditcards
                                        WHERE cc_id = '" . $res_noauthattempts['cc_id'] . "'
                                            AND user_id = '" . $res_user['user_id'] . "'
                                ", 0, null, __FILE__, __LINE__);
                                // email user
                                $ilance->email->mail = array($res_user['email'], $res_noauthattempts['email_of_cardowner']);
                                $ilance->email->slng = fetch_user_slng($res_noauthattempts['user_id']);
                                $ilance->email->get('expired_creditcard_removal_notice');		
                                $ilance->email->set(array(
                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                        '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " (" . ucfirst($res_user['username']) . ")",
                                ));
                                $ilance->email->send();
                        }
                }
        }
        // expire credit cards that have attempted authentication but have expired after x days
        $sql_cc_yesattempts = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE authorized = 'no'
                    AND date_added LIKE '%" . $days_ago_expired_yesauth . "%'
                    AND auth_amount1 != ''
                    AND auth_amount2 != ''
                    AND trans1_id != ''
                    AND trans2_id != ''
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sql_cc_yesattempts) > 0)
        {
                while ($res_cc_yesattempts = $ilance->db->fetch_array($sql_cc_yesattempts, DB_ASSOC))
                {
                        $ccid = $res_cc_yesattempts['cc_id'];
                        $sql_user = $ilance->db->query("
                                SELECT *
                                FROM " . DB_PREFIX . "users
                                WHERE user_id = '" . $res_cc_yesattempts['user_id'] . "'
                        ", 0, null, __FILE__, __LINE__);
                        if ($ilance->db->num_rows($sql_user) > 0)
                        {
                                $res_user = $ilance->db->fetch_array($sql_user, DB_ASSOC);
                                $name_on_card = $res_cc_yesattempts['name_on_card'];
                                $namesplit = explode(' ', $name_on_card);
                                // does admin allow automated refunds via cron job?
                                if ($ilconfig['cron_refund_on_max_cc_auth_days'])
                                {
                                        $v3customer_ccid = $ccid;
                                        $v3customer_lname = '';
                                        $v3customer_fname = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "name_on_card"));
                                        $v3customer_address = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_billing_address1")) . " " . stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_billing_address2"));
                                        $v3customer_city = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_city"));
                                        $v3customer_state = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_state"));
                                        $v3customer_zip = stripslashes($ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_postalzip"));
                                        $v3customer_country = stripslashes($ilance->db->fetch_field(DB_PREFIX . "locations", "locationid = '" . $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "card_country") . "'", "location_" . fetch_site_slng()));
                                        $input_auth = $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "auth_amount1") + $ilance->db->fetch_field(DB_PREFIX . "creditcards", "cc_id = '" . $v3customer_ccid . "'", "auth_amount2");
                                        // refund to credit card
                                        $refundsuccess = $ilance->accounting_creditcard->creditcard_authentication_refund($input_auth, $v3customer_ccid, $v3customer_fname, $v3customer_lname, $v3customer_address, $v3customer_city, $v3customer_state, $v3customer_zip, $v3customer_country);
                                        if ($refundsuccess)
                                        {
                                                // remove credit card from db
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "creditcards
                                                        WHERE cc_id = '" . $res_cc_yesattempts['cc_id'] . "'
                                                            AND user_id = '" . $res_user['user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                $existing = array(
                                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                        '{{customer}}' => ucfirst($v3customer_fname) . " (" . $res_user['username'] . ")",
                                                        '{{refundamount}}' => $ilance->currency->format($res_cc_yesattempts['auth_amount1'] + $res_cc_yesattempts['auth_amount2']),
                                                        '{{paymentmodule}}' => $ilconfig['paymodulename'],
                                                        '{{paytype}}' => '{_credit_card}'
                                                );
                                                // email user
                                                $ilance->email->mail = array($res_user['email'], $res_cc_yesattempts['email_of_cardowner']);
                                                $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                $ilance->email->get('expired_creditcard_removal_and_refund');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                // email admin
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('expired_creditcard_removal_and_refund_admin');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                        }
                                        else
                                        {
                                                $amount = $res_cc_yesattempts['auth_amount1'] + $res_cc_yesattempts['auth_amount2'];
                                                // remove credit card from db
                                                $ilance->db->query("
                                                        DELETE FROM " . DB_PREFIX . "creditcards
                                                        WHERE cc_id = '" . $res_cc_yesattempts['cc_id'] . "'
                                                            AND user_id = '" . $res_user['user_id'] . "'
                                                ", 0, null, __FILE__, __LINE__);
                                                // credit customer account balance for 2 amounts previously debit from their credit card
                                                $ilance->db->query("
                                                        UPDATE " . DB_PREFIX . "users
                                                        SET available_balance = available_balance + $amount,
                                                        total_balance = total_balance + $amount
                                                        WHERE user_id = '" . intval($res_user['user_id']) . "'
                                                ");
                                                // create transaction record for credit
                                                $refundinvoiceid = $ilance->accounting->insert_transaction(
                                                        0,
                                                        0,
                                                        0,
                                                        intval($res_user['user_id']),
                                                        0,
                                                        0,
                                                        0,
                                                        'Credit Card Authentication Attempt Failure Refund Credit',
                                                        sprintf("%01.2f", $amount),
                                                        sprintf("%01.2f", $amount),
                                                        'paid',
                                                        'credit',
                                                        'account',
                                                        DATETIME24H,
                                                        DATETIME24H,
                                                        DATETIME24H,
                                                        '{_auto_credited_to_online_account_balance}',
                                                        0,
                                                        0,
                                                        1
                                                );
                                                $existing = array(
                                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                        '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " (" . $res_user['username'] . ")",
                                                        '{{refundamount}}' => $ilance->currency->format($amount),
                                                        '{{paymentmodule}}' => '',
                                                        '{{paytype}}' => '{_online_account}'
                                                );
                                                // email user
                                                $ilance->email->mail = array($res_user['email'], $res_cc_yesattempts['email_of_cardowner']);
                                                $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                                $ilance->email->get('expired_creditcard_removal_and_refund');		
                                                $ilance->email->set($existing);
                                                $ilance->email->send();
                                                // send email to admin informing of account credit to customer balance
                                                $ilance->email->mail = SITE_EMAIL;
                                                $ilance->email->slng = fetch_site_slng();
                                                $ilance->email->get('expired_creditcard_removal_and_autorefund_admin');		
                                                $ilance->email->set(array(
                                                        '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                        '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " (" . $res_user['username'] . ")",
                                                        '{{refundamount}}' => $ilance->currency->format($amount),
                                                ));
                                                $ilance->email->send();
                                        }
                                }
                                else
                                {
                                        $amount = $res_cc_yesattempts['auth_amount1'] + $res_cc_yesattempts['auth_amount2'];
                                        // remove credit card from db
                                        $ilance->db->query("
                                                DELETE FROM " . DB_PREFIX . "creditcards
                                                WHERE cc_id = '" . $res_cc_yesattempts['cc_id'] . "'
                                                    AND user_id = '" . $res_user['user_id'] . "'
                                        ", 0, null, __FILE__, __LINE__);
                                        // credit customer account balance for 2 amounts previously debit from their credit card
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "users
                                                SET available_balance = available_balance + $amount,
                                                total_balance = total_balance + $amount
                                                WHERE user_id = '" . intval($res_user['user_id']) . "'
                                        ");
                                        // create transaction record for credit
                                        $refundinvoiceid = $ilance->accounting->insert_transaction(
                                                0,
                                                0,
                                                0,
                                                intval($res_user['user_id']),
                                                0,
                                                0,
                                                0,
                                                'Credit Card Authentication Attempt Failure Refund Credit',
                                                sprintf("%01.2f", $amount),
                                                sprintf("%01.2f", $amount),
                                                'paid',
                                                'credit',
                                                'account',
                                                DATETIME24H,
                                                DATETIME24H,
                                                DATETIME24H,
                                                '{_auto_credited_to_online_account_balance}',
                                                0,
                                                0,
                                                1
                                        );
                                        $existing = array(
                                                '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " (" . $res_user['username'] . ")",
                                                '{{refundamount}}' => $ilance->currency->format($amount),
                                                '{{paymentmodule}}' => '',
                                                '{{paytype}}' => '{_online_account}'
                                        );
                                        // email user
                                        $ilance->email->mail = array($res_user['email'], $res_cc_yesattempts['email_of_cardowner']);
                                        $ilance->email->slng = fetch_user_slng($res_user['user_id']);
                                        $ilance->email->get('expired_creditcard_removal_and_refund');		
                                        $ilance->email->set($existing);
                                        $ilance->email->send();
                                        // send email to admin informing of account credit to customer balance
                                        $ilance->email->mail = SITE_EMAIL;
                                        $ilance->email->slng = fetch_site_slng();
                                        $ilance->email->get('expired_creditcard_removal_and_autorefund_admin');		
                                        $ilance->email->set(array(
                                                '{{expiredays}}' => $ilconfig['admin_cc_expired_days'],
                                                '{{customer}}' => ucfirst($res_user['first_name']) . " " . ucfirst($res_user['last_name']) . " (" . $res_user['username'] . ")",
                                                '{{refundamount}}' => $ilance->currency->format($amount),
                                        ));
                                        $ilance->email->send();
                                }                
                        }
                }
        }
        // expired credit card month/year checkup
        $sqlccexpiries = $ilance->db->query("
                SELECT *
                FROM " . DB_PREFIX . "creditcards
                WHERE creditcard_status != 'expired'
        ", 0, null, __FILE__, __LINE__);
        if ($ilance->db->num_rows($sqlccexpiries) > 0)
        {
                while ($resccexpiries = $ilance->db->fetch_array($sqlccexpiries, DB_ASSOC))
                {
                        $ccexpiry = $resccexpiries['creditcard_expiry'];
                        $ccexpirymonth = mb_substr($ccexpiry, 0, -2);
                        $ccexpiryyear = mb_substr($ccexpiry, -2);
                        if ($ccexpiryyear > date('y'))
                        {
                        }
                        else if ($ccexpiryyear == date('y'))
                        {
                                if ($ccexpirymonth > date('m'))
                                {
                                }
                                else if ($ccexpirymonth == date('m'))
                                {
                                        $ilance->db->query("
                                                UPDATE " . DB_PREFIX . "creditcards
                                                SET creditcard_status = 'expired',
                                                authorized = 'no'
                                                WHERE cc_id = '" . $resccexpiries['cc_id'] . "'
                                                LIMIT 1
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
        }
}

($apihook = $ilance->api('cron_creditcards_end')) ? eval($apihook) : false;

$ilance->timer->stop();
log_cron_action('{_credit_card_tasks_were_executed_successfully}', $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>