<?php
/*==========================================================================*\
|| ######################################################################## ||
|| # ILance Marketplace Software
|| # -------------------------------------------------------------------- # ||
|| # Copyright ©2000–2009 ILance Inc. All Rights Reserved.	          # ||
|| # This file may not be redistributed in whole or significant part. 	  # ||
|| # ----------------- ILANCE IS NOT FREE SOFTWARE ---------------------- # ||
|| # http://www.ilance.com | http://www.ilance.com/eula	| info@ilance.com # ||
|| # -------------------------------------------------------------------- # ||
|| ######################################################################## ||
\*==========================================================================*/
if (!isset($GLOBALS['ilance']->db))
{
    die('<strong>Warning:</strong> This script does not appear to have database functions loaded.  Operation aborted.');
}



if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
    echo '<h1>Upgrade 3.0.9 to 3.1.0</h1><p>Updating database...</p>';
    // 04-25-2007 ##################################################################
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 125, 'globalserver_distancelimit', 'How many results from distance server to fetch before limit? [higher number = slower radius calculation searches].  Additionally, if you allow 10000 results this means you are searching your local db for this amount of information which may put a strain on the server resources.', '10000', 'globalserverdistanceapi', 'text', '', '', '', 7)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'stormpay', 'stormpay', 'StormPay IPN Gateway Configuration', '', 'ipn')", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'cashu', 'cashu', 'CashU IPN Gateway Configuration', '', 'ipn')", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'paypal', 'yesno', '', '', '', 130)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_subscriptions', 'Enable Authorize.Net Recurring Subscriptions? (used in subscription menu)', '0', 'authnet', 'yesno', '', '', '', 110)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'authnet', 'yesno', '', '', '', 120)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'plug_n_pay', 'yesno', '', '', '', 110)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'psigate', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'eway', 'yesno', '', '', '', 90)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Stormpay', 'stormpay', 'text', '', '', '', 10)", 0, null, __FILE__, __LINE__);
    // STORMPAY
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Stormpay', 'stormpay', 'text', '', '', '', 10)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_business_email', 'Enter your Stormpay email address', 'payments@yourdomain.com', 'stormpay', 'text', '', '', '', 20)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_secret_code', 'Enter the secret passphrase code [must be set at stormpay.com]', 'mypassphrase', 'stormpay', 'text', '', '', '', 30)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_md5_digest', 'Enable MD5 hash encryption feature? [must be set at stormpay.com]', '1', 'stormpay', 'yesno', '', '', '', 40)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_master_currency', 'Enter the currency used in Stormpay transactions', 'USD', 'stormpay', 'int', '', '', '', 50)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'stormpay', 'int', '', '', '', 60)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'stormpay', 'int', '', '', '', 70)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_active', 'Allow members to deposit funds using this gateway?', '1', 'stormpay', 'yesno', '', '', '', 80)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_subscriptions', 'Enable Stormpay Recurring Subscriptions? (used in subscription menu)', '0', 'stormpay', 'yesno', '', '', '', 90)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'stormpay', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_registration', 'Show this payment method within registration pulldown menu?', '0', 'stormpay', 'yesno', '', '', '', 110)", 0, null, __FILE__, __LINE__);
    // CASHU
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'CashU', 'cashu', 'text', '', '', '', 10)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_business_email', 'Enter your CashU Merchant ID', 'payments@yourdomain.com', 'cashu', 'text', '', '', '', 20)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_secret_code', 'Enter the secret passphrase code [must be set at cashu.com]', 'mypassphrase', 'cashu', 'text', '', '', '', 30)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_master_currency', 'Enter the currency used in CashU transactions', 'USD', 'cashu', 'int', '', '', '', 40)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'cashu', 'int', '', '', '', 50)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'cashu', 'int', '', '', '', 60)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_active', 'Allow members to deposit funds using this gateway?', '1', 'cashu', 'yesno', '', '', '', 70)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'cashu', 'yesno', '', '', '', 80)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_registration', 'Show this payment method within registration pulldown menu?', '0', 'cashu', 'yesno', '', '', '', 90)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_testmode', 'Put this payment module in test mode only?', '0', 'cashu', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
    // PAYPAL
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_registration', 'Show this payment method within registration pulldown menu?', '0', 'paypal', 'yesno', '', '', '', 140)", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices CHANGE `paymethod` `paymethod` ENUM( 'account', 'bank', 'visa', 'amex', 'mc', 'disc', 'paypal', 'check', 'purchaseorder', 'stormpay', 'cashu' ) NOT NULL DEFAULT 'account'", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `paymethod` `paymethod` ENUM( 'account', 'bank', 'visa', 'amex', 'mc', 'disc', 'paypal', 'check', 'stormpay', 'cashu' ) NOT NULL DEFAULT 'account'", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'purchaseorder_registration', 'Enable purchase orders which allows members to choose invoice me for this order during registration?', '0', 'check', 'yesno', '', '', '', 30)", 0, null, __FILE__, __LINE__);
    
    // 04-27-2007 ##################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "register_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "profile_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')", 0, null, __FILE__, __LINE__);
    
    // 04-28-2007 ##################################################################
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'livesync_get_curl', 'Would you prefer to use CURL for fetching remote livesync file data?', '0', 'globalserverliveupdate', 'yesno', '', '', '', 6)", 0, null, __FILE__, __LINE__);
    
    // 05-01-2007 ##################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "budget ADD `insertiongroup` VARCHAR( 250 ) NOT NULL AFTER `budgetto`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "emaillog CHANGE `logtype` `logtype` enum('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','watchlist')", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "search_favorites", 0, null, __FILE__, __LINE__);
    $ilance->db->query("CREATE TABLE " . DB_PREFIX . "search_favorites (
    `searchid` INT(10) NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) NOT NULL,
    `searchoptions` MEDIUMTEXT NOT NULL,
    `searchoptionstext` MEDIUMTEXT NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `cattype` ENUM('rfp','product','providers','stores','wantads') NOT NULL default 'rfp',
    `subscribed` INT(1) NOT NULL default '0',
    PRIMARY KEY  (`searchid`)
    )", 0, null, __FILE__, __LINE__);
    
    // 05-04-2007 ##################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `additional_cid` `additional_cid` INT( 10 ) NOT NULL DEFAULT '0'", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_seoreplacements', 'Characters in URL Replacements [format: A|a, B|b, C|c] separate each replacement with 1 comma and 1 space', '', 'globalauctionsettings', 'textarea', '', '', '', 50)", 0, null, __FILE__, __LINE__);
    
    // 05-06-2007 ##################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "register_questions ADD `cansearch` INT( 1 ) NOT NULL DEFAULT '0' AFTER `profile`", 0, null, __FILE__, __LINE__);
    
    // 05-08-2007 ##################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "sessions ADD `token` VARCHAR( 32 ) NOT NULL AFTER `browser`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("TRUNCATE TABLE " . DB_PREFIX . "sessions", 0, null, __FILE__, __LINE__);
    
    // OTHER #######################################################################
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "referral_data
    ADD `payfvf` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paysubscription`,
    ADD `payins` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payfvf`,
    ADD `paylanceads` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payins`,
    ADD `payportfolio` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paylanceads`,
    ADD `paycredential` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payportfolio`,
    ADD `payenhancements` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paycredential`", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "referral_data`
    CHANGE `postauction` `postauction` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `awardauction` `awardauction` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `paysubscription` `paysubscription` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `payfvf` `payfvf` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `payins` `payins` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `paylanceads` `paylanceads` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `payportfolio` `payportfolio` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `paycredential` `paycredential` INT( 10 ) NOT NULL DEFAULT '0',
    CHANGE `payenhancements` `payenhancements` INT( 10 ) NOT NULL DEFAULT '0'", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'servicebid_awardbidretract', 'Can a service provider retract their bid after they have been awarded?', '0', 'servicebid_limits', 'yesno', '', '', '', 10)", 0, null, __FILE__, __LINE__);
    $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES (NULL, 'servicebid', 'servicebid_limits', 'Service Bidding Settings', '')", 0, null, __FILE__, __LINE__);
    $ilance->db->query("CREATE TABLE " . DB_PREFIX . "project_realtimebids (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `bid_id` INT(100) NOT NULL default '0',
    `user_id` INT(100) NOT NULL default '0',
    `project_id` INT(100) NOT NULL default '0',
    `project_user_id` INT(100) NOT NULL default '0',
    `proposal` TEXT NOT NULL default '',
    `bidamount` DOUBLE NOT NULL default '0',
    `qty` INT(10) NOT NULL default '1',
    `estimate_days` INT(100) NOT NULL default '0',
    `date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
    `date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',
    `date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',
    `bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',
    `bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',
    `bidamounttype` ENUM('entire','hourly','daily','weekly','biweekly','monthly','lot','item','weight') NOT NULL default 'entire',
    `bidcustom` VARCHAR(100) NOT NULL default '',
    `fvf` VARCHAR(20) NOT NULL default '0.00',
    `state` ENUM('service','product') NOT NULL default 'service',
    PRIMARY KEY (`id`))", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("ALTER TABLE " . DB_PREFIX . "portfolio ADD `featured_date` DATETIME NOT NULL AFTER `featured`", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "profile_questions` CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown' ) NOT NULL DEFAULT 'text'", 0, null, __FILE__, __LINE__);    
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "profile_questions` ADD `multiplechoice` TEXT NOT NULL AFTER `inputtype`", 0, null, __FILE__, __LINE__);
    
    // move accountdata to users table migration
    
    // 1 - create new fields
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `account_number` VARCHAR( 25 ) NOT NULL AFTER `rid`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `available_balance` DOUBLE NOT NULL DEFAULT '0' AFTER `account_number`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `total_balance` DOUBLE NOT NULL DEFAULT '0' AFTER `available_balance`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `income_reported` DOUBLE NOT NULL DEFAULT '0' AFTER `total_balance`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `income_spent` DOUBLE NOT NULL DEFAULT '0' AFTER `income_reported`", 0, null, __FILE__, __LINE__);
    
    // 2 - select all old accountdata results and update new fields in user table
    $sel = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "accountdata ORDER BY account_id ASC", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($sel) > 0)
    {
        while ($data = $ilance->db->fetch_array($sel))
        {
            $ilance->db->query("UPDATE " . DB_PREFIX . "users
            SET account_number = '".$data['account_number']."',
            available_balance = '".$data['available_balance']."',
            total_balance = '".$data['total_balance']."',
            income_reported = '".$data['income_reported']."',
            income_spent = '".$data['income_spent']."'
            WHERE user_id = '".$data['user_id']."'
            LIMIT 1", 0, null, __FILE__, __LINE__);
        }
        
        // 3 - finally remove the obsolete accountdata table
        $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "accountdata", 0, null, __FILE__, __LINE__);
    }
    
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "audit", 0, null, __FILE__, __LINE__);
    $ilance->db->query("CREATE TABLE " . DB_PREFIX . "audit (
    `logid` INT(10) NOT NULL AUTO_INCREMENT,
    `user_id` INT(10) NOT NULL default '0',
    `admin_id` INT(10) NOT NULL default '0',
    `script` VARCHAR(200) NOT NULL default '',
    `cmd` VARCHAR(250) NOT NULL default '',
    `subcmd` VARCHAR(250) NOT NULL default '',
    `otherinfo` MEDIUMTEXT NOT NULL default '',
    `datetime` INT(11) NOT NULL default '0',
    `ipaddress` VARCHAR(50) NOT NULL default '',
    PRIMARY KEY  (`logid`))", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "news", 0, null, __FILE__, __LINE__);
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "newsletters", 0, null, __FILE__, __LINE__);
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "secure_forms", 0, null, __FILE__, __LINE__);
    
    // move preferences to users table migration
    
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users`
    ADD `startpage` VARCHAR(250) NOT NULL DEFAULT 'main' AFTER `income_spent`,
    ADD `styleid` VARCHAR(3) NOT NULL AFTER `startpage`,
    ADD `project_distance` ENUM('yes','no') NOT NULL AFTER `styleid`,
    ADD `currency_calculation` ENUM('yes','no') NOT NULL AFTER `project_distance`,
    ADD `languageid` INT(3) NOT NULL AFTER `currency_calculation`,
    ADD `currencyid` INT(3) NOT NULL AFTER `languageid`,
    ADD `timezoneid` INT(3) NOT NULL AFTER `currencyid`,
    ADD `timezone_dst` INT(1) NOT NULL AFTER `timezoneid`,
    ADD `notifyservices` INT(1) NOT NULL AFTER `timezone_dst`,
    ADD `notifyproducts` INT(1) NOT NULL AFTER `notifyservices`,
    ADD `notifyservicescats` TEXT NOT NULL AFTER `notifyproducts`,
    ADD `notifyproductscats` TEXT NOT NULL AFTER `notifyservicescats`,
    ADD `displayprofile` INT(1) NOT NULL AFTER `notifyproductscats`,
    ADD `emailnotify` INT(1) NOT NULL AFTER `displayprofile`,
    ADD `displayfinancials` INT(1) NOT NULL AFTER `emailnotify`,
    ADD `vatnumber` VARCHAR(250) NOT NULL AFTER `displayfinancials`,
    ADD `regnumber` VARCHAR(250) NOT NULL AFTER `vatnumber`,
    ADD `dnbnumber` VARCHAR(250) NOT NULL AFTER `regnumber`,
    ADD `companyname` VARCHAR(100) NOT NULL AFTER `dnbnumber`,
    ADD `usecompanyname` INT(1) NOT NULL AFTER `companyname`", 0, null, __FILE__, __LINE__);
    
    // migrate preferences fields over to user table
    $sel2 = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "preferences ORDER BY user_id ASC", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($sel2) > 0)
    {
        while ($res = $ilance->db->fetch_array($sel2))
        {
            $ilance->db->query("UPDATE " . DB_PREFIX . "users
            SET startpage = '".$res['start_page']."',
            styleid = '".$res['styleid']."',
            project_distance = '".$res['project_distance']."',
            currency_calculation = '".$res['currency_calculation']."',
            languageid = '".$res['languageid']."',
            currencyid = '".$res['currencyid']."',
            timezoneid = '".$res['timezoneid']."',
            timezone_dst = '".$res['timezone_dst']."',
            notifyservices = '".$res['notifyservices']."',
            notifyproducts = '".$res['notifyproducts']."',
            notifyservicescats = '".$res['notifyservicescats']."',
            notifyproductscats = '".$res['notifyproductscats']."',
            displayprofile = '".$res['displayprofile']."',
            emailnotify = '".$res['emailnotify']."',
            displayfinancials = '".$res['displayfinancials']."',
            vatnumber = '".$res['vatnumber']."',
            regnumber = '".$res['regnumber']."',
            dnbnumber = '".$res['dnbnumber']."',
            companyname = '".$res['companyname']."',
            usecompanyname = '".$res['usecompanyname']."'            
            WHERE user_id = '".$res['user_id']."'", 0, null, __FILE__, __LINE__);
        }
        
        // 3 - finally remove the obsolete preferences table
        $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "preferences", 0, null, __FILE__, __LINE__);
    }
    
    // remove obsolete field in watchlist table as we no longer use this subcat logic
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "watchlist` DROP `watching_subcategory_id`", 0, null, __FILE__, __LINE__);
    
    // changing datatype for v3_categories 'bidamounttypes'
    $bidtypes = $ilance->db->query("SELECT cid, bidamounttypes FROM " . DB_PREFIX . "categories WHERE bidamounttypes != ''", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($bidtypes) > 0)
    {
        while ($restypes = $ilance->db->fetch_array($bidtypes))
        {
            $newtype = $restypes['bidamounttypes'];
            $newtype = urldecode($newtype);
            $newtype = base64_decode($newtype);
            // save data as serialized only
            $ilance->db->query("UPDATE " . DB_PREFIX . "categories SET bidamounttypes = '".$ilance->db->escape_string($newtype)."' WHERE cid = '".$restypes['cid']."'", 0, null, __FILE__, __LINE__);
        }
        unset($restypes);
    }
    
    // changing datatype for v3_product_answers 'answer'
    $productanswers = $ilance->db->query("SELECT answerid, questionid, answer FROM " . DB_PREFIX . "product_answers WHERE answer != ''", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($productanswers) > 0)
    {
        while ($resproductanswers = $ilance->db->fetch_array($productanswers))
        {
            if ($ilance->auction_questions->is_question_multiplechoice($resproductanswers['questionid'], 'product'))
            {
                $newtype = $resproductanswers['answer'];
                $newtype = urldecode($newtype);
                $newtype = base64_decode($newtype);
                // save data as serialized only
                $ilance->db->query("UPDATE " . DB_PREFIX . "product_answers SET answer = '".$ilance->db->escape_string($newtype)."' WHERE answerid = '".$resproductanswers['answerid']."'", 0, null, __FILE__, __LINE__);
            }
        }
        unset($resproductanswers);
    }
    
    // changing datatype for v3_profile_answers 'answer' 
    $profileanswers = $ilance->db->query("SELECT answerid, questionid, answer FROM " . DB_PREFIX . "profile_answers WHERE answer != ''", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($profileanswers) > 0)
    {
        while ($resprofileanswers = $ilance->db->fetch_array($profileanswers))
        {
            if ($ilance->auction_questions->is_question_multiplechoice($resprofileanswers['questionid'], 'profile'))
            {
                $newtype = $resprofileanswers['answer'];
                $newtype = urldecode($newtype);
                $newtype = base64_decode($newtype);
                // save data as serialized only
                $ilance->db->query("UPDATE " . DB_PREFIX . "profile_answers SET answer = '".$ilance->db->escape_string($newtype)."' WHERE answerid = '".$resprofileanswers['answerid']."'", 0, null, __FILE__, __LINE__);
            }
        }
        unset($resprofileanswers);
    }
    
    // changing datatype for v3_project_answers 'answer'
    $projectanswers = $ilance->db->query("SELECT answerid, questionid, answer FROM " . DB_PREFIX . "project_answers WHERE answer != ''", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($projectanswers) > 0)
    {
        while ($resprojectanswers = $ilance->db->fetch_array($projectanswers))
        {
            if ($ilance->auction_questions->is_question_multiplechoice($resprojectanswers['questionid'], 'project'))
            {
                $newtype = $resprojectanswers['answer'];
                $newtype = urldecode($newtype);
                $newtype = base64_decode($newtype);
                // save data as serialized only
                $ilance->db->query("UPDATE " . DB_PREFIX . "project_answers SET answer = '".$ilance->db->escape_string($newtype)."' WHERE answerid = '".$resprojectanswers['answerid']."'", 0, null, __FILE__, __LINE__);
            }
        }
        unset($resprojectanswers);
    }
    
    // changing datatype for v3_register_answers 'answer'
    $registeranswers = $ilance->db->query("SELECT answerid, questionid, answer FROM " . DB_PREFIX . "register_answers WHERE answer != ''", 0, null, __FILE__, __LINE__);
    if ($ilance->db->num_rows($registeranswers) > 0)
    {
        while ($resregisteranswers = $ilance->db->fetch_array($registeranswers))
        {
            if ($ilance->auction_questions->is_question_multiplechoice($resregisteranswers['questionid'], 'register'))
            {
                $newtype = $resregisteranswers['answer'];
                $newtype = urldecode($newtype);
                $newtype = base64_decode($newtype);
                // save data as serialized only
                $ilance->db->query("UPDATE " . DB_PREFIX . "register_answers SET answer = '".$ilance->db->escape_string($newtype)."' WHERE answerid = '".$resregisteranswers['answerid']."'", 0, null, __FILE__, __LINE__);
            }
        }
        unset($resregisteranswers);
    }
    
    // drop obsolete subcategory_id field in portfolio table
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "portfolio` DROP `subcategory_id`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` DROP `entryfee`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` DROP `entrybids`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` DROP `entrybidscount`", 0, null, __FILE__, __LINE__);
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` ADD `fvf` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `insertionfee`", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "bbcodes", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "bankaccounts` ADD `beneficiary_bank_state` VARCHAR( 100 ) NOT NULL AFTER `beneficiary_bank_city`", 0, null, __FILE__, __LINE__);
    
    $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '3.1.0' WHERE name = 'current_version'", 0, null, __FILE__, __LINE__);
    $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '5' WHERE name = 'current_sql_version'", 0, null, __FILE__, __LINE__);
    
    echo '<br /><br /><strong>Complete!</strong> Please follow the next steps:<br /><br />
    1. New payment module options now available.  View within AdminCP > Pay Modules<br />';
    echo "<br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br />\n";
}
else
{
    echo '<h1>Upgrade 3.0.9 to 3.1.0</h1><p>The following SQL query will be executed:</p>';
    echo '<hr size="1" width="100%" />';
    
    // 04-25-2007 ##################################################################
    echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 125, 'globalserver_distancelimit', 'How many results from distance server to fetch before limit? [higher number = slower radius calculation searches].  Additionally, if you allow 10000 results this means you are searching your local db for this amount of information which may put a strain on the server resources.', '10000', 'globalserverdistanceapi', 'text', '', '', '', 7)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'stormpay', 'stormpay', 'StormPay IPN Gateway Configuration', '', 'ipn')<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'cashu', 'cashu', 'CashU IPN Gateway Configuration', '', 'ipn')<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'paypal', 'yesno', '', '', '', 130)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_subscriptions', 'Enable Authorize.Net Recurring Subscriptions? (used in subscription menu)', '0', 'authnet', 'yesno', '', '', '', 110)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'authnet', 'yesno', '', '', '', 120)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'plug_n_pay', 'yesno', '', '', '', 110)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'psigate', 'yesno', '', '', '', 100)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'eway', 'yesno', '', '', '', 90)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Stormpay', 'stormpay', 'text', '', '', '', 10)<br /><br />";
    // STORMPAY
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Stormpay', 'stormpay', 'text', '', '', '', 10)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_business_email', 'Enter your Stormpay email address', 'payments@yourdomain.com', 'stormpay', 'text', '', '', '', 20)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_secret_code', 'Enter the secret passphrase code [must be set at stormpay.com]', 'mypassphrase', 'stormpay', 'text', '', '', '', 30)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_md5_digest', 'Enable MD5 hash encryption feature? [must be set at stormpay.com]', '1', 'stormpay', 'yesno', '', '', '', 40)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_master_currency', 'Enter the currency used in Stormpay transactions', 'USD', 'stormpay', 'int', '', '', '', 50)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'stormpay', 'int', '', '', '', 60)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'stormpay', 'int', '', '', '', 70)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_active', 'Allow members to deposit funds using this gateway?', '1', 'stormpay', 'yesno', '', '', '', 80)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_subscriptions', 'Enable Stormpay Recurring Subscriptions? (used in subscription menu)', '0', 'stormpay', 'yesno', '', '', '', 90)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'stormpay', 'yesno', '', '', '', 100)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_registration', 'Show this payment method within registration pulldown menu?', '0', 'stormpay', 'yesno', '', '', '', 110)<br /><br />";
    // CASHU
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'CashU', 'cashu', 'text', '', '', '', 10)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_business_email', 'Enter your CashU Merchant ID', 'payments@yourdomain.com', 'cashu', 'text', '', '', '', 20)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_secret_code', 'Enter the secret passphrase code [must be set at cashu.com]', 'mypassphrase', 'cashu', 'text', '', '', '', 30)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_master_currency', 'Enter the currency used in CashU transactions', 'USD', 'cashu', 'int', '', '', '', 40)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'cashu', 'int', '', '', '', 50)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'cashu', 'int', '', '', '', 60)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_active', 'Allow members to deposit funds using this gateway?', '1', 'cashu', 'yesno', '', '', '', 70)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_directpayment', 'Enable Direct Payments? (generates live payment forms for direct payment)', '0', 'cashu', 'yesno', '', '', '', 80)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_registration', 'Show this payment method within registration pulldown menu?', '0', 'cashu', 'yesno', '', '', '', 90)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_testmode', 'Put this payment module in test mode only?', '0', 'cashu', 'yesno', '', '', '', 100)<br /><br />";
    // PAYPAL
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_registration', 'Show this payment method within registration pulldown menu?', '0', 'paypal', 'yesno', '', '', '', 140)<br /><br />";
    
    echo "ALTER TABLE " . DB_PREFIX . "invoices CHANGE `paymethod` `paymethod` ENUM( 'account', 'bank', 'visa', 'amex', 'mc', 'disc', 'paypal', 'check', 'purchaseorder', 'stormpay', 'cashu' ) NOT NULL DEFAULT 'account'<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `paymethod` `paymethod` ENUM( 'account', 'bank', 'visa', 'amex', 'mc', 'disc', 'paypal', 'check', 'stormpay', 'cashu' ) NOT NULL DEFAULT 'account'<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'purchaseorder_registration', 'Enable purchase orders which allows members to choose invoice me for this order during registration?', '0', 'check', 'yesno', '', '', '', 30)<br /><br />";
    
    // 04-27-2007 ##################################################################
    echo "ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "register_questions CHANGE `multiplechoice` `multiplechoice` TEXT NOT NULL<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "profile_questions CHANGE `inputtype` `inputtype` enum('yesno','int','textarea','text','pulldown','multiplechoice')<br /><br />";
    
    // 04-28-2007 ##################################################################
    echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'livesync_get_curl', 'Would you prefer to use CURL for fetching remote livesync file data?', '0', 'globalserverliveupdate', 'yesno', '', '', '', 6)<br /><br />";
    
    // 05-01-2007 ##################################################################
    echo "ALTER TABLE " . DB_PREFIX . "budget ADD `insertiongroup` VARCHAR( 250 ) NOT NULL AFTER `budgetto`<br /><br />";
    echo "ALTER TABLE " . DB_PREFIX . "emaillog CHANGE `logtype` `logtype` enum('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','watchlist')<br /><br />";
    
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "search_favorites<br />";
    echo "CREATE TABLE " . DB_PREFIX . "search_favorites (<br />
    `searchid` INT(10) NOT NULL AUTO_INCREMENT,<br />
    `user_id` INT(10) NOT NULL,<br />
    `searchoptions` MEDIUMTEXT NOT NULL,<br />
    `searchoptionstext` MEDIUMTEXT NOT NULL,<br />
    `title` VARCHAR(200) NOT NULL,<br />
    `cattype` ENUM('rfp','product','providers','stores','wantads') NOT NULL default 'rfp',<br />
    `subscribed` INT(1) NOT NULL default '0',<br />
    PRIMARY KEY  (`searchid`)<br />
    )<br /><br />";
    
    
    // 05-04-2007 ##################################################################
    echo "ALTER TABLE " . DB_PREFIX . "projects CHANGE `additional_cid` `additional_cid` INT( 10 ) NOT NULL DEFAULT '0'<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_seoreplacements', 'Characters in URL Replacements [format: A|a, B|b, C|c] separate each replacement with 1 comma and 1 space', '', 'globalauctionsettings', 'textarea', '', '', '', 50)<br /><br />";
    
    // 05-06-2007 ##################################################################
    echo "ALTER TABLE " . DB_PREFIX . "register_questions ADD `cansearch` INT( 1 ) NOT NULL DEFAULT '0' AFTER `profile`<br /><br />";
    
    // 05-08-2007 ##################################################################
    echo "ALTER TABLE " . DB_PREFIX . "sessions ADD `token` VARCHAR( 32 ) NOT NULL AFTER `browser`<br /><br />";
    echo "TRUNCATE TABLE " . DB_PREFIX . "sessions<br /><br />";
    
    // OTHER #######################################################################
    echo "ALTER TABLE " . DB_PREFIX . "referral_data<br />
    ADD `payfvf` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paysubscription`,<br />
    ADD `payins` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payfvf`,<br />
    ADD `paylanceads` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payins`,<br />
    ADD `payportfolio` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paylanceads`,<br />
    ADD `paycredential` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `payportfolio`,<br />
    ADD `payenhancements` ENUM('yes', 'no') NOT NULL DEFAULT 'no' AFTER `paycredential`<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "referral_data`<br />
    CHANGE `postauction` `postauction` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `awardauction` `awardauction` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `paysubscription` `paysubscription` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `payfvf` `payfvf` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `payins` `payins` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `paylanceads` `paylanceads` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `payportfolio` `payportfolio` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `paycredential` `paycredential` INT( 10 ) NOT NULL DEFAULT '0',<br />
    CHANGE `payenhancements` `payenhancements` INT( 10 ) NOT NULL DEFAULT '0'<br /><br />";
    
    echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'servicebid_awardbidretract', 'Can a service provider retract their bid after they have been awarded?', '0', 'servicebid_limits', 'yesno', '', '', '', 10)<br /><br />";
    echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES (NULL, 'servicebid', 'servicebid_limits', 'Service Bidding Settings', '')<br /><br />";
    echo "CREATE TABLE " . DB_PREFIX . "project_realtimebids (<br />
    `id` INT(100) NOT NULL AUTO_INCREMENT,<br />
    `bid_id` INT(100) NOT NULL default '0',<br />
    `user_id` INT(100) NOT NULL default '0',<br />
    `project_id` INT(100) NOT NULL default '0',<br />
    `project_user_id` INT(100) NOT NULL default '0',<br />
    `proposal` TEXT NOT NULL default '',<br />
    `bidamount` DOUBLE NOT NULL default '0',<br />
    `qty` INT(10) NOT NULL default '1',<br />
    `estimate_days` INT(100) NOT NULL default '0',<br />
    `date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
    `date_updated` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
    `date_awarded` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
    `bidstatus` ENUM('placed','awarded','declined','choseanother','outbid') NOT NULL default 'placed',<br />
    `bidstate` ENUM('','reviewing','wait_approval','shortlisted','invited','archived','expired','retracted') default '',<br />
    `bidamounttype` ENUM('entire','hourly','daily','weekly','biweekly','monthly','lot','item','weight') NOT NULL default 'entire',<br />
    `bidcustom` VARCHAR(100) NOT NULL default '',<br />
    `fvf` VARCHAR(20) NOT NULL default '0.00',<br />
    `state` ENUM('service','product') NOT NULL default 'service',<br />
    PRIMARY KEY (`id`))<br /><br />";
    
    echo "ALTER TABLE " . DB_PREFIX . "portfolio ADD `featured_date` DATETIME NOT NULL AFTER `featured`<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "profile_questions` CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown' ) NOT NULL DEFAULT 'text'<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "profile_questions` ADD `multiplechoice` TEXT NOT NULL AFTER `inputtype`<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `account_number` VARCHAR( 25 ) NOT NULL AFTER `rid`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `available_balance` DOUBLE NOT NULL DEFAULT '0' AFTER `account_number`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `total_balance` DOUBLE NOT NULL DEFAULT '0' AFTER `available_balance`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `income_reported` DOUBLE NOT NULL DEFAULT '0' AFTER `total_balance`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `income_spent` DOUBLE NOT NULL DEFAULT '0' AFTER `income_reported`<br /><br />";
    
    echo "<hr size=1 width=100% />";
    echo "<strong>PHP Code to be executed:</strong><br /><br />";
    echo "\$sel = \$ilance-&gt;db-&gt;query(&quot;SELECT * FROM &quot;.DB_PREFIX.&quot;accountdata ORDER BY account_id ASC&quot;);<br />
    if (\$ilance-&gt;db-&gt;num_rows(\$sel) &gt; 0)<br />
    {<br />
    &nbsp;&nbsp;&nbsp;&nbsp;while (\$data = \$ilance-&gt;db-&gt;fetch_array(\$sel))<br />
    &nbsp;&nbsp;&nbsp;&nbsp;{<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$ilance-&gt;db-&gt;query(&quot;UPDATE &quot;.DB_PREFIX.&quot;users<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SET account_number = '&quot;.\$data['account_number'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;available_balance = '&quot;.\$data['available_balance'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;total_balance = '&quot;.\$data['total_balance'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;income_reported = '&quot;.\$data['income_reported'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;income_spent = '&quot;.\$data['income_spent'].&quot;'<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WHERE user_id = '&quot;.\$data['user_id'].&quot;'<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIMIT 1&quot;);<br />
    &nbsp;&nbsp;&nbsp;&nbsp;}<br />
    <br />
    &nbsp;&nbsp;&nbsp;&nbsp;\$ilance-&gt;db-&gt;query(&quot;DROP TABLE IF EXISTS &quot;.DB_PREFIX.&quot;accountdata&quot;);<br />
}";
    
    echo "<hr size=1 width=100% />";
    
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "audit<br /><br />";
    echo "CREATE TABLE " . DB_PREFIX . "audit (<br />
    `logid` INT(10) NOT NULL AUTO_INCREMENT,<br />
    `user_id` INT(10) NOT NULL default '0',<br />
    `admin_id` INT(10) NOT NULL default '0',<br />
    `script` VARCHAR(200) NOT NULL default '',<br />
    `cmd` VARCHAR(250) NOT NULL default '',<br />
    `subcmd` VARCHAR(250) NOT NULL default '',<br />
    `otherinfo` MEDIUMTEXT NOT NULL default '',<br />
    `datetime` INT(11) NOT NULL default '0',<br />
    `ipaddress` VARCHAR(50) NOT NULL default '',<br />
    PRIMARY KEY  (`logid`))<br /><br />";
    
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "news<br /><br />";
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "newsletters<br /><br />";
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "secure_forms<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "users`<br />
    ADD `startpage` VARCHAR(250) NOT NULL DEFAULT 'main' AFTER `income_spent`,<br />
    ADD `styleid` VARCHAR(3) NOT NULL AFTER `startpage`,<br />
    ADD `project_distance` ENUM('yes','no') NOT NULL AFTER `styleid`,<br />
    ADD `currency_calculation` ENUM('yes','no') NOT NULL AFTER `project_distance`,<br />
    ADD `languageid` INT(3) NOT NULL AFTER `currency_calculation`,<br />
    ADD `currencyid` INT(3) NOT NULL AFTER `languageid`,<br />
    ADD `timezoneid` INT(3) NOT NULL AFTER `currencyid`,<br />
    ADD `timezone_dst` INT(1) NOT NULL AFTER `timezoneid`,<br />
    ADD `notifyservices` INT(1) NOT NULL AFTER `timezone_dst`,<br />
    ADD `notifyproducts` INT(1) NOT NULL AFTER `notifyservices`,<br />
    ADD `notifyservicescats` TEXT NOT NULL AFTER `notifyproducts`,<br />
    ADD `notifyproductscats` TEXT NOT NULL AFTER `notifyservicescats`,<br />
    ADD `displayprofile` INT(1) NOT NULL AFTER `notifyproductscats`,<br />
    ADD `emailnotify` INT(1) NOT NULL AFTER `displayprofile`,<br />
    ADD `displayfinancials` INT(1) NOT NULL AFTER `emailnotify`,<br />
    ADD `vatnumber` VARCHAR(250) NOT NULL AFTER `displayfinancials`,<br />
    ADD `regnumber` VARCHAR(250) NOT NULL AFTER `vatnumber`,<br />
    ADD `dnbnumber` VARCHAR(250) NOT NULL AFTER `regnumber`,<br />
    ADD `companyname` VARCHAR(100) NOT NULL AFTER `dnbnumber`,<br />
    ADD `usecompanyname` INT(1) NOT NULL AFTER `companyname`<br /><br />";
    
    echo "// migrate preferences fields over to user table<br />
    \$sel2 = \$ilance-&gt;db-&gt;query(&quot;SELECT * FROM &quot;.DB_PREFIX.&quot;preferences ORDER BY user_id ASC&quot;);<br />
    if (\$ilance-&gt;db-&gt;num_rows(\$sel2) &gt; 0)<br />
    {<br />
    &nbsp;&nbsp;&nbsp;&nbsp;while (\$res = \$ilance-&gt;db-&gt;fetch_array(\$sel2))<br />
    &nbsp;&nbsp;&nbsp;&nbsp;{<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\$ilance-&gt;db-&gt;query(&quot;UPDATE &quot;.DB_PREFIX.&quot;users<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;SET startpage = '&quot;.\$res['start_page'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;styleid = '&quot;.\$res['styleid'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;project_distance = '&quot;.\$res['project_distance'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;currency_calculation = '&quot;.\$res['currency_calculation'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;languageid = '&quot;.\$res['languageid'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;currencyid = '&quot;.\$res['currencyid'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;timezoneid = '&quot;.\$res['timezoneid'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;timezone_dst = '&quot;.\$res['timezone_dst'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;notifyservices = '&quot;.\$res['notifyservices'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;notifyproducts = '&quot;.\$res['notifyproducts'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;notifyservicescats = '&quot;.\$res['notifyservicescats'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;notifyproductscats = '&quot;.\$res['notifyproductscats'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;displayprofile = '&quot;.\$res['displayprofile'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;emailnotify = '&quot;.\$res['emailnotify'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;displayfinancials = '&quot;.\$res['displayfinancials'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;vatnumber = '&quot;.\$res['vatnumber'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;regnumber = '&quot;.\$res['regnumber'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;dnbnumber = '&quot;.\$res['dnbnumber'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;companyname = '&quot;.\$res['companyname'].&quot;',<br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;usecompanyname = '&quot;.\$res['usecompanyname'].&quot;' <br />
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;WHERE user_id = '&quot;.\$res['user_id'].&quot;'&quot;);<br />
    &nbsp;&nbsp;&nbsp;&nbsp;}<br />
    <br />
    // 3 - finally remove the obsolete preferences table<br />
    \$ilance-&gt;db-&gt;query(&quot;DROP TABLE IF EXISTS &quot;.DB_PREFIX.&quot;preferences&quot;);<br />
    }<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "watchlist` DROP `watching_subcategory_id`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "portfolio` DROP `subcategory_id`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "projects` DROP `entryfee`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "projects` DROP `entrybids`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "projects` DROP `entrybidscount`<br /><br />";
    echo "ALTER TABLE `" . DB_PREFIX . "projects` ADD `fvf` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `insertionfee`<br /><br />";
    echo "DROP TABLE IF EXISTS " . DB_PREFIX . "bbcodes<br /><br />";
    
    echo "ALTER TABLE `" . DB_PREFIX . "bankaccounts` ADD `beneficiary_bank_state` VARCHAR( 100 ) NOT NULL AFTER `beneficiary_bank_city`<br /><br />";
    
    echo "UPDATE " . DB_PREFIX . "configuration SET value = '3.1.0' WHERE name = 'current_version'<br /><br />";
    echo "UPDATE " . DB_PREFIX . "configuration SET value = '5' WHERE name = 'current_sql_version'<br /><br />";
    
    echo '<hr size="1" width="100%" />';
    
    echo '<strong><a href="installer.php?do=install&step=17&execute=1">Execute</a></strong> this SQL query';
}
?>