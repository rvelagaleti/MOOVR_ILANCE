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

$ver = $ilance->db->query_fetch("
	SELECT value
	FROM " . DB_PREFIX . "configuration
	WHERE name = 'current_version'
", 1, null, __FILE__, __LINE__); // 1 denotes we should hide any db errors

$ilconfig['current_version'] = $ver['value'];
	
if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.1.4 to 3.1.5</h1><p>Updating database...</p>';
        
        $sql = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'current_sql_version'
        ", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        if ($res['value'] == '7')
        {
                // add url to the project questions
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'pulldown', 'multiplechoice', 'range', 'url' ) NOT NULL DEFAULT 'text'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'pulldown', 'multiplechoice', 'range', 'url' ) NOT NULL DEFAULT 'text'", 0, null, __FILE__, __LINE__);
                
                // pay per post admincp auction setting switch
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_payperpost', 'Enable pay as you go auction system? When enabled, any insertion fees incured during the posting of an auction will need to be paid prior to being publically visible in the marketplace. All unpaid auctions will be visible from the users pending auction area.', '0', 'globalauctionsettings', 'yesno', '', '', '', 60)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showfees', 'Enable the listing fees table during the posting of an auction (will display insertion fee table (service/product) and/or budget based insertion fee table (service)', '1', 'globalauctionsettings', 'yesno', '', '', '', 70)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                                ALTER TABLE " . DB_PREFIX . "users
                                ADD `isadmin` INT(1) NOT NULL DEFAULT '0' AFTER `daysonsite`,
                                ADD `permissions` MEDIUMTEXT NOT NULL AFTER `isadmin`
                ", 0, null, __FILE__, __LINE__);
                
                // migrate admin accounts into user table
                $sql = $ilance->db->query("
                        SELECT username, password, salt, email, permissions
                        FROM " . DB_PREFIX . "admin
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                // check if this admin exists as a user already
                                $sql2 = $ilance->db->query("
                                        SELECT *
                                        FROM " . DB_PREFIX . "users
                                        WHERE username = '" . $ilance->db->escape_string($res['username']) . "'
                                ", 0, null, __FILE__, __LINE__);
                                if ($ilance->db->num_rows($sql2) == 0)
                                {
                                        // add admin to user table
                                        $ilance->db->query("
                                                INSERT INTO " . DB_PREFIX . "users
                                                (user_id, username, password, salt, email, isadmin, permissions)
                                                VALUES (
                                                NULL,
                                                '" . $ilance->db->escape_string($res['username']) . "',
                                                '" . $ilance->db->escape_string($res['password']) . "',
                                                '" . $ilance->db->escape_string($res['salt']) . "',
                                                '" . $ilance->db->escape_string($res['email']) . "',
                                                '1',
                                                '" . $ilance->db->escape_string($res['permissions']) . "')
                                        ", 0, null, __FILE__, __LINE__);
                                }
                        }
                }
                
                $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "admin", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                                ALTER TABLE " . DB_PREFIX . "users
                                ADD `searchoptions` MEDIUMTEXT NOT NULL AFTER `permissions`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "projects`
                            CHANGE `ship_costs` `ship_costs` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `buynow_price` `buynow_price` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `buynow_costperitem` `buynow_costperitem` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `reserve_price` `reserve_price` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `startprice` `startprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `retailprice` `retailprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `currentprice` `currentprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `insertionfee` `insertionfee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "buynow_orders`
                            CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `fee` `fee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `fee2` `fee2` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "creditcards`
                            CHANGE `auth_amount1` `auth_amount1` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `auth_amount2` `auth_amount2` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "finalvalue`
                            CHANGE `finalvalue_from` `finalvalue_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `finalvalue_to` `finalvalue_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `amountfixed` `amountfixed` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "increments`
                            CHANGE `increment_from` `increment_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `increment_to` `increment_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "insertion_fees`
                            CHANGE `insertion_from` `insertion_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `insertion_to` `insertion_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                            CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'       
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                            ALTER TABLE `" . DB_PREFIX . "invoices`
                            CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00' DEFAULT '0.00',
                            CHANGE `paid` `paid` FLOAT(10,2) NULL DEFAULT '0.00' DEFAULT '0.00',
                            CHANGE `totalamount` `totalamount` FLOAT(10,2) NOT NULL DEFAULT '0.00' DEFAULT '0.00',
                            CHANGE `taxamount` `taxamount` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "profile_questions` CHANGE `verifycost` `verifycost` FLOAT(10,2) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects_escrow`
                        CHANGE `escrowamount` `escrowamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `shipping` `shipping` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `total` `total` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `fee` `fee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `fee2` `fee2` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects_uniquebids` CHANGE `uniquebid` `uniquebid` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "project_bids`
                        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00' 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "project_realtimebids`
                        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
                        CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "proxybid` CHANGE `maxamount` `maxamount` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "ratings_reviews` CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "subscription` CHANGE `cost` `cost` FLOAT(10,2) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "timezones` ADD PRIMARY KEY ( `timezoneid` )", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_endsoondays', 'Enter the amount (in hours) auctions on the front page will be considered ending soon: values: [-1 = any date, 1 = 1 hour, 2 = 2 hours, 3 = 3 hours, 4 = 4 hours, 5 = 5 hours, 6 = 12 hours, 7 = 24 hours, 8 = 2 days, 9 = 3 days, 10 = 4 days, 11 = 5 days, 12 = 6 days, 13 = 7 days, 14 = 2 weeks, 15 = 1 month]', '7', 'globalauctionsettings', 'int', '', '', '', 80)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapgenres', 'Display custom questions underneath categories when viewing the category map?', '0', 'globalauctionsettings', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_newicondays', 'Enter the amount (in days) auction categories will display a new auction posted icon', '7', 'globalauctionsettings', 'int', '', '', '', 110)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapdepth', 'Enter the category level depth (ie: Level1 > Level2 > Level3) to show within the category map display', '2', 'globalauctionsettings', 'int', '', '', '', 130)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catquestiondepth', 'How many custom category questions to display until the rest are hidden and before a more options link becomes visble', '3', 'globalauctionsettings', 'int', '', '', '', 140)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapgenredepth', 'How many subcategory levels deep will the category question genres be shown in the category map display', '1', 'globalauctionsettings', 'int', '', '', '', 160)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalfilters_defaultdeflated', 'Would you like to deflate (hide) all results by default?', '1', 'globalfilterresults', 'yesno', '', '', '', 1)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showcurrentcat', 'When viewing a selected category in the left nav should the selected category be bold showing subcategories underneath? Disabling will not show the selected category (only subcategories)', '1', 'globalauctionsettings', 'yesno', '', '', '', 170)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catcutoff', 'How many categories to display until the rest are hidden and before a more options link becomes visble', '10', 'globalauctionsettings', 'int', '', '', '', 180)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showbackto', 'Would you like to show a (Back To: [cat]) link in the left nav menu when viewing deep categories while searching the marketplace?', '1', 'globalauctionsettings', 'yesno', '', '', '', 190)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'clean_old_log_entries', 'Delete log entries after n days (n=0 will not delete)?', '0', 'globalsecuritysettings', 'int', '', '', '', 101)", 0, null, __FILE__, __LINE__);
		$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showfeaturedlistings', 'Show featured listings on the homepage and other areas like search and category map?', '1', 'globalauctionsettings', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showendingsoonlistings', 'Show ending soon listings on the homepage?', '1', 'globalauctionsettings', 'yesno', '', '', '', 110)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showlatestlistings', 'Show latest listings scroll box to the right side on the homepage and other areas like the category map?', '1', 'globalauctionsettings', 'yesno', '', '', '', 120)", 0, null, __FILE__, __LINE__);
	
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search_favorites` CHANGE `cattype` `cattype` ENUM( 'rfp', 'product', 'experts', 'stores', 'wantads' ) NOT NULL DEFAULT 'rfp'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "categories` CHANGE `fixedfeeamount` `fixedfeeamount` FLOAT(10,2) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "categories` ADD `description_eng` MEDIUMTEXT NOT NULL AFTER `title_eng`", 0, null, __FILE__, __LINE__);
                
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languagecode != 'english'
                ", 0, null, __FILE__, __LINE__);
                if ($ilance->db->num_rows($sql) > 0)
                {
                        while ($res = $ilance->db->fetch_array($sql))
                        {
                                $slng = mb_substr($res['languagecode'], 0, 3);
                                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories ADD description_" . $slng . " MEDIUMTEXT NOT NULL AFTER `id`", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("UPDATE " . DB_PREFIX . "categories SET description_" . $slng . " = description_eng", 0, null, __FILE__, __LINE__);
                        }
                }
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `rateperhour` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `searchoptions`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "profile_groups` DROP `allcids`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `profilevideourl` MEDIUMTEXT NOT NULL AFTER `rateperhour`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'postjobs'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'serviceprofile'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'createnewstore'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'lancealert'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'referabuyer'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'referaseller'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailprofiletoafriend'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailproductvendor'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailauctionfriend'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailauctionsfriend'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewfeedback'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'uploadlogo'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewtransaction'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewstransaction'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'downloadCSV'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'notifywhenbidreceived'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'notifywhenprojectsposted'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewreferralaccount'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'newsletteropt_in'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'pressreleaseopt_in'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewoldarchives'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbuynow'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchprojects'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchproviders'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbuyers'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbycategory'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'currencycalculator'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'maxinvitesperday'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'maxemailsperinvite'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("RENAME TABLE " . DB_PREFIX . "subscription_group_titles TO " . DB_PREFIX . "subscription_permissions", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices ADD `parentid` INT( 10 ) NOT NULL AFTER `invoiceid`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices ADD `indispute` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isif`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories ADD `useproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `bidgroupdisplay`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "increments` ADD `groupname` VARCHAR( 250 ) NOT NULL DEFAULT 'default' AFTER `incrementid`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "increments` ADD `sort` INT( 5 ) NOT NULL DEFAULT '0' AFTER `amount`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "increments_groups (
                        `groupid` INT(5) NOT NULL AUTO_INCREMENT,
                        `groupname` VARCHAR(50) NOT NULL default 'default',
                        `description` MEDIUMTEXT,
                        PRIMARY KEY  (`groupid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Exempt From Final Value Fees', $accessdescription = 'Defines if a customer within this subscription group is exempt from Final Value Fees', $accessname = 'fvfexempt', $accesstype = 'yesno', $value = 'no', $canremove = 0);
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Exempt From Insertion Fees', $accessdescription = 'Defines if a customer within this subscription group is exempt from Insertion Fees', $accessname = 'insexempt', $accesstype = 'yesno', $value = 'no', $canremove = 0);
                
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'productbid_capactive'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'productbid_caprate'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalfilters_maxsubcategorydisplay'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'moderationsystem_disableauctionmoderation'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'moderationsystem_disableauctionmoderation', 'Disable auction listing moderation? (when disabled, the listing is posted for the public to see whereas when enabled, an email is dispatched for admin to verify)', '0', 'globalauctionsettings', 'yesno', '', '', '', 0)", 0, null, __FILE__, __LINE__);            
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "categories` ADD `incrementgroup` VARCHAR( 250 ) NOT NULL AFTER `finalvaluegroup`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search_favorites` CHANGE `cattype` `cattype` ENUM( 'service', 'product', 'experts', 'stores', 'wantads' ) NOT NULL DEFAULT 'service'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_canada (
                        `ZIPCode` CHAR(30) NOT NULL default '',
                        `City` CHAR(30) NOT NULL default '',
                        `Province` CHAR(30) NOT NULL default '',
                        `Latitude` DOUBLE NOT NULL default '0',
                        `Longitude` DOUBLE NOT NULL default '0',
                        KEY `ZIPCode` (`ZIPCode`),
                        KEY `longlat` (`Longitude`,`Latitude`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_uk (
                        `ZIPCode` CHAR(30) NOT NULL default '',
                        `Latitude` VARCHAR(150) NOT NULL default '0',
                        `Longitude` VARCHAR(150) NOT NULL default '0',
                        KEY `ZIPCode` (`ZIPCode`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_usa (
                        `ZIPCode` CHAR(10) NOT NULL default '',
                        `ZIPCodeType` CHAR(5) NOT NULL default '',
                        `City` CHAR(50) NOT NULL default '',
                        `CityType` CHAR(5) NOT NULL default '',
                        `State` CHAR(50) NOT NULL default '',
                        `StateCode` CHAR(10) NOT NULL default '',
                        `AreaCode` CHAR(10) NOT NULL default '',
                        `Latitude` DOUBLE NOT NULL default '0',
                        `Longitude` DOUBLE NOT NULL default '0',
                        KEY `ZIPCode` (`ZIPCode`),
                        KEY `longlat` (`Longitude`,`Latitude`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "shippers (
                        `shipperid` INT(5) NOT NULL auto_increment,
                        `title` MEDIUMTEXT,
                        PRIMARY KEY  (`shipperid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                flush();
                echo "<li>" . DB_PREFIX . "shippers</li>";
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "shippers
                        (`shipperid`, `title`)
                        VALUES
                        (1, 'US Postal Service Priority Mail (2 to 3 business days)'),
                        (2, 'US Postal Service Priority Mail Flat Rate Envelope (2 to 3 business days)'),
                        (3, 'US Postal Service Priority Mail Flat Rate Box (2 to 3 business days)'),
                        (4, 'US Postal Service Express Mail (1 business day)'),
                        (5, 'US Postal Service Express Mail Flat Rate Envelope (1 business day)'),
                        (6, 'US Postal Service Parcel Post (2 to 9 business days)'),
                        (7, 'US Postal Service Media Mail (2 to 9 business days)'),
                        (8, 'US Postal Service First Class Mail (2 to 5 business days)'),
                        (9, 'UPS Ground (1 to 6 business days)'),
                        (10, 'UPS 3 Day Select (1 to 3 business days)'),
                        (11, 'UPS 2nd Day Air (1 to 2 business days)'),
                        (12, 'UPS Next Day Air Saver (1 business day)'),
                        (13, 'UPS Next Day Air (1 business day)'),
                        (14, 'Standard Flat Rate Shipping Service'),
                        (15, 'Expedited Flat Rate Shipping Service'),
                        (16, 'Overnight Flat Rate Shipping Service (1 business day)'),
                        (17, 'Other (see description)')
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_nl (
                        `ZIPCode` CHAR(30) NOT NULL default '',
                        `Latitude` VARCHAR(150) NOT NULL default '0',
                        `Longitude` VARCHAR(150) NOT NULL default '0',
                        KEY `ZIPCode` (`ZIPCode`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $fieldsextra = '';
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $slng = mb_substr($res['languagecode'], 0, 3);
                        
                        $fieldsextra .= "
                        `title_$slng` MEDIUMTEXT,
                        `description_$slng` MEDIUMTEXT,";
                }
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "skills (
                        `cid` int(100) NOT NULL auto_increment,
                        `parentid` int(100) NOT NULL default '0',
                        `level` int(5) NOT NULL default '1',
                        $fieldsextra
                        `views` int(100) NOT NULL default '0',
                        `keywords` mediumtext,
                        `visible` int(1) NOT NULL default '1',
                        `sort` int(3) NOT NULL default '0',
                        PRIMARY KEY  (`cid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "skills
                        (`cid`, `parentid`, `level`, `title_eng`, `description_eng`, `views`, `keywords`, `visible`, `sort`)
                        VALUES
                        (1, 0, 1, 'Programming', 'Programming', 0, 'Programming', 1, 0),
                        (2, 1, 2, 'AJAX', 'AJAX', 0, 'AJAX', 1, 0),
                        (3, 1, 2, 'ASP', 'ASP', 0, 'ASP', 1, 0),
                        (4, 1, 2, 'ASP.NET+ADO', 'ASP.NET+ADO', 0, 'ASP.NET+ADO', 1, 0),
                        (5, 1, 2, 'ActiveX', 'ActiveX', 0, 'ActiveX', 1, 0),
                        (6, 1, 2, 'Adobe Flex', 'Adobe Flex', 0, 'Adobe Flex', 1, 0),
                        (7, 1, 2, 'Assembler', 'Assembler', 0, 'Assembler', 1, 0),
                        (8, 1, 2, 'Borland C++ Builder', 'Borland C++ Builder', 0, 'Borland C++ Builder', 1, 0),
                        (9, 1, 2, 'C#/.Net', 'C#/.Net', 0, 'C#/.Net', 1, 0),
                        (10, 1, 2, 'C/C++/Unix', 'C/C++/Unix', 0, 'C/C++/Unix', 1, 0),
                        (11, 1, 2, 'C/C++/Win32SDK', 'C/C++/Win32SDK', 0, 'C/C++/Win32SDK', 1, 0),
                        (12, 1, 2, 'CSS', 'CSS', 0, 'CSS', 1, 0),
                        (13, 1, 2, 'CodeWarrior/C++', 'CodeWarrior/C++', 0, 'CodeWarrior/C++', 1, 0),
                        (14, 1, 2, 'ColdFusion', 'ColdFusion', 0, 'ColdFusion', 1, 0),
                        (15, 1, 2, 'Crystal Reports', 'Crystal Reports', 0, 'Crystal Reports', 1, 0),
                        (16, 1, 2, 'Delphi ', 'Delphi ', 0, 'Delphi ', 1, 0),
                        (17, 1, 2, 'Delphi/VB', 'Delphi/VB', 0, 'Delphi/VB', 1, 0),
                        (18, 1, 2, 'Driver development', 'Driver development', 0, 'Driver development', 1, 0),
                        (19, 1, 2, 'Flash/ActionScript', 'Flash/ActionScript', 0, 'Flash/ActionScript', 1, 0),
                        (20, 1, 2, 'FoxPro', 'FoxPro', 0, 'FoxPro', 1, 0),
                        (21, 1, 2, 'GTK programming', 'GTK programming', 0, 'GTK programming', 1, 0),
                        (22, 1, 2, 'Games/Windows', 'Games/Windows', 0, 'Games/Windows', 1, 0),
                        (23, 1, 2, 'HTML/DHTML', 'HTML/DHTML', 0, 'HTML/DHTML', 1, 0),
                        (24, 1, 2, 'Hyperion', 'Hyperion', 0, 'Hyperion', 1, 0),
                        (25, 1, 2, 'IntelliJ IDEA', 'IntelliJ IDEA', 0, 'IntelliJ IDEA', 1, 0),
                        (26, 1, 2, 'J2EE', 'J2EE', 0, 'J2EE', 1, 0),
                        (27, 1, 2, 'JBoss', 'JBoss', 0, 'JBoss', 1, 0),
                        (28, 1, 2, 'JFC', 'JFC', 0, 'JFC', 1, 0),
                        (29, 1, 2, 'JSP', 'JSP', 0, 'JSP', 1, 0),
                        (30, 1, 2, 'JavaScript', 'JavaScript', 0, 'JavaScript', 1, 0),
                        (31, 1, 2, 'Kylix ', 'Kylix ', 0, 'Kylix ', 1, 0),
                        (32, 1, 2, 'LaTeX', 'LaTeX', 0, 'LaTeX', 1, 0),
                        (33, 1, 2, 'Lingo', 'Lingo', 0, 'Lingo', 1, 0),
                        (34, 1, 2, 'Mason', 'Mason', 0, 'Mason', 1, 0),
                        (35, 1, 2, 'OCX', 'OCX', 0, 'OCX', 1, 0),
                        (36, 1, 2, 'PHP', 'PHP', 0, 'PHP', 1, 0),
                        (37, 1, 2, 'PHP/HTML/DHTML', 'PHP/HTML/DHTML', 0, 'PHP/HTML/DHTML', 1, 0),
                        (38, 1, 2, 'PHP/IIS/MS SQL', 'PHP/IIS/MS SQL', 0, 'PHP/IIS/MS SQL', 1, 0),
                        (39, 1, 2, 'PHP/MySQL', 'PHP/MySQL', 0, 'PHP/MySQL', 1, 0),
                        (40, 1, 2, 'Perl', 'Perl', 0, 'Perl', 1, 0),
                        (41, 1, 2, 'Python', 'Python', 0, 'Python', 1, 0),
                        (42, 1, 2, 'Qt', 'Qt', 0, 'Qt', 1, 0),
                        (43, 1, 2, 'Remoting', 'Remoting', 0, 'Remoting', 1, 0),
                        (44, 1, 2, 'Resin', 'Resin', 0, 'Resin', 1, 0),
                        (45, 1, 2, 'Ruby', 'Ruby', 0, 'Ruby', 1, 0),
                        (46, 1, 2, 'SOAP', 'SOAP', 0, 'SOAP', 1, 0),
                        (47, 1, 2, 'SatelliteForms', 'SatelliteForms', 0, 'SatelliteForms', 1, 0),
                        (48, 1, 2, 'Smarty', 'Smarty', 0, 'Smarty', 1, 0),
                        (49, 1, 2, 'Struts', 'Struts', 0, 'Struts', 1, 0),
                        (50, 1, 2, 'SyncML', 'SyncML', 0, 'SyncML', 1, 0),
                        (51, 1, 2, 'TCP/IP', 'TCP/IP', 0, 'TCP/IP', 1, 0),
                        (52, 1, 2, 'Tomcat', 'Tomcat', 0, 'Tomcat', 1, 0),
                        (53, 1, 2, 'Unix Shell', 'Unix Shell', 0, 'Unix Shell', 1, 0),
                        (54, 1, 2, 'VB/.NET', 'VB/.NET', 0, 'VB/.NET', 1, 0),
                        (55, 1, 2, 'VB/Delphi', 'VB/Delphi', 0, 'VB/Delphi', 1, 0),
                        (56, 1, 2, 'VB/Delphi/ASP/IIS', 'VB/Delphi/ASP/IIS', 0, 'VB/Delphi/ASP/IIS', 1, 0),
                        (57, 1, 2, 'VBA', 'VBA', 0, 'VBA', 1, 0),
                        (58, 1, 2, 'Visual Basic ', 'Visual Basic ', 0, 'Visual Basic ', 1, 0),
                        (59, 1, 2, 'VoiceXML', 'VoiceXML', 0, 'VoiceXML', 1, 0),
                        (60, 1, 2, 'WML/WMLScript', 'WML/WMLScript', 0, 'WML/WMLScript', 1, 0),
                        (61, 1, 2, 'WordPress', 'WordPress', 0, 'WordPress', 1, 0),
                        (62, 1, 2, 'XML', 'XML', 0, 'XML', 1, 0),
                        (63, 1, 2, 'XML-RPC', 'XML-RPC', 0, 'XML-RPC', 1, 0),
                        (64, 1, 2, 'XUL', 'XUL', 0, 'XUL', 1, 0),
                        (65, 1, 2, 'Zope/Python', 'Zope/Python', 0, 'Zope/Python', 1, 0),
                        (66, 0, 1, 'Databases', 'Databases', 0, 'Databases', 1, 0),
                        (67, 66, 2, 'Access', 'Access', 0, 'Access', 1, 0),
                        (68, 66, 2, 'Cobol', 'Cobol', 0, 'Cobol', 1, 0),
                        (69, 66, 2, 'Filemaker Pro ', 'Filemaker Pro ', 0, 'Filemaker Pro ', 1, 0),
                        (70, 66, 2, 'Informix', 'Informix', 0, 'Informix', 1, 0),
                        (71, 66, 2, 'InterBase', 'InterBase', 0, 'InterBase', 1, 0),
                        (72, 66, 2, 'MS-SQL', 'MS-SQL', 0, 'MS-SQL', 1, 0),
                        (73, 66, 2, 'MySQL', 'MySQL', 0, 'MySQL', 1, 0),
                        (74, 66, 2, 'Oracle DBA', 'Oracle DBA', 0, 'Oracle DBA', 1, 0),
                        (75, 66, 2, 'Oracle Forms', 'Oracle Forms', 0, 'Oracle Forms', 1, 0),
                        (76, 66, 2, 'Oracle PL/SQL', 'Oracle PL/SQL', 0, 'Oracle PL/SQL', 1, 0),
                        (77, 66, 2, 'Oracle Reports', 'Oracle Reports', 0, 'Oracle Reports', 1, 0),
                        (78, 66, 2, 'PostgreSQ', 'PostgreSQ', 0, 'PostgreSQ', 1, 0),
                        (79, 66, 2, 'SQL', 'SQL', 0, 'SQL', 1, 0),
                        (80, 66, 2, 'SQLite', 'SQLite', 0, 'SQLite', 1, 0),
                        (81, 66, 2, 'Sybase', 'Sybase', 0, 'Sybase', 1, 0),
                        (82, 0, 1, 'Mobile', 'Mobile', 0, 'Mobile', 1, 0),
                        (83, 82, 2, 'Blackberry/RIM ', 'Blackberry/RIM ', 0, 'Blackberry/RIM ', 1, 0),
                        (84, 82, 2, 'J2ME', 'J2ME', 0, 'J2ME', 1, 0),
                        (85, 82, 2, 'PalmOS', 'PalmOS', 0, 'PalmOS', 1, 0),
                        (86, 82, 2, 'PocketPC', 'PocketPC', 0, 'PocketPC', 1, 0),
                        (87, 82, 2, 'Symbian SDK', 'Symbian SDK', 0, 'Symbian SDK', 1, 0),
                        (88, 0, 1, 'Design/Graphics', 'Design/Graphics', 0, 'Design/Graphics', 1, 0),
                        (89, 88, 2, '3D Design', '3D Design', 0, '3D Design', 1, 0),
                        (90, 88, 2, 'Design/Flash', 'Design/Flash', 0, 'Design/Flash', 1, 0),
                        (91, 88, 2, 'Flash/Macromedia', 'Flash/Macromedia', 0, 'Flash/Macromedia', 1, 0),
                        (92, 88, 2, 'Graphics', 'Graphics', 0, 'Graphics', 1, 0),
                        (93, 88, 2, 'Macromedia Director', 'Macromedia Director', 0, 'Macromedia Director', 1, 0),
                        (94, 88, 2, 'Photoshop', 'Photoshop', 0, 'Photoshop', 1, 0),
                        (95, 88, 2, 'QNX', 'QNX', 0, 'QNX', 1, 0),
                        (96, 88, 2, 'UI Design', 'UI Design', 0, 'UI Design', 1, 0),
                        (97, 88, 2, 'Video Streaming', 'Video Streaming', 0, 'Video Streaming', 1, 0),
                        (98, 0, 1, 'Systems Admin', 'Systems Admin', 0, 'Systems Admin', 1, 0),
                        (99, 98, 2, 'AS/400', 'AS/400', 0, 'AS/400', 1, 0),
                        (100, 98, 2, 'LAMP administration ', 'LAMP administration ', 0, 'LAMP administration ', 1, 0),
                        (101, 98, 2, 'Mac OS X', 'Mac OS X', 0, 'Mac OS X', 1, 0),
                        (102, 98, 2, 'Windows Administration', 'Windows Administration', 0, 'Windows Administration', 1, 0),
                        (103, 0, 1, 'Application Servers', 'Application Servers', 0, 'Application Servers', 1, 0),
                        (104, 103, 2, 'Asterisk', 'Asterisk', 0, 'Asterisk', 1, 0),
                        (105, 103, 2, 'Lotus Domino', 'Lotus Domino', 0, 'Lotus Domino', 1, 0),
                        (106, 103, 2, 'Lotus Notes', 'Lotus Notes', 0, 'Lotus Notes', 1, 0),
                        (107, 103, 2, 'MS Navision', 'MS Navision', 0, 'MS Navision', 1, 0),
                        (108, 103, 2, 'Oracle Application Server', 'Oracle Application Server', 0, 'Oracle Application Server', 1, 0),
                        (109, 103, 2, 'OsCommerce', 'OsCommerce', 0, 'OsCommerce', 1, 0),
                        (110, 103, 2, 'Web Sphere', 'Web Sphere', 0, 'Web Sphere', 1, 0),
                        (111, 103, 2, 'WebLogic', 'WebLogic', 0, 'WebLogic', 1, 0),
                        (112, 0, 1, 'Platforms', 'Platforms', 0, 'Platforms', 1, 0),
                        (113, 112, 2, 'DotNetNuke', 'DotNetNuke', 0, 'DotNetNuke', 1, 0),
                        (114, 112, 2, 'EDI', 'EDI', 0, 'EDI', 1, 0),
                        (115, 112, 2, 'Hibernate', 'Hibernate', 0, 'Hibernate', 1, 0),
                        (116, 112, 2, 'Joomla', 'Joomla', 0, 'Joomla', 1, 0),
                        (117, 112, 2, 'Mambo', 'Mambo', 0, 'Mambo', 1, 0),
                        (118, 112, 2, 'Online Payments', 'Online Payments', 0, 'Online Payments', 1, 0),
                        (119, 112, 2, 'PowerBuilder', 'PowerBuilder', 0, 'PowerBuilder', 1, 0),
                        (120, 112, 2, 'Sharepoint', 'Sharepoint', 0, 'Sharepoint', 1, 0),
                        (121, 112, 2, 'Voice/Windows', 'Voice/Windows', 0, 'Voice/Windows', 1, 0),
                        (122, 112, 2, 'Wireless', 'Wireless', 0, 'Wireless', 1, 0),
                        (123, 112, 2, 'phpNuke', 'phpNuke', 0, 'phpNuke', 1, 0),
                        (124, 112, 2, 'postNuke', 'postNuke', 0, 'postNuke', 1, 0),
                        (125, 0, 1, 'Concepts', 'Concepts', 0, 'Concepts', 1, 0),
                        (126, 125, 2, 'Application Design', 'Application Design', 0, 'Application Design', 1, 0),
                        (127, 125, 2, 'Database Modeling', 'Database Modeling', 0, 'Database Modeling', 1, 0),
                        (128, 125, 2, 'Systems Programming', 'Systems Programming', 0, 'Systems Programming', 1, 0),
                        (129, 125, 2, 'UML', 'UML', 0, 'UML', 1, 0),
                        (130, 125, 2, 'VoIP', 'VoIP', 0, 'VoIP', 1, 0),
                        (131, 0, 1, 'Other', 'Other', 0, 'Other', 1, 0),
                        (132, 131, 2, 'Data Entry', 'Data Entry', 0, 'Data Entry', 1, 0),
                        (133, 131, 2, 'Project Management', 'Project Management', 0, 'Project Management', 1, 0),
                        (134, 131, 2, 'QA', 'QA', 0, 'QA', 1, 0),
                        (135, 131, 2, 'Recruiting', 'Recruiting', 0, 'Recruiting', 1, 0),
                        (136, 131, 2, 'SEO', 'SEO', 0, 'SEO', 1, 0),
                        (137, 131, 2, 'Search', 'Search', 0, 'Search', 1, 0),
                        (138, 131, 2, 'Tech Writer', 'Tech Writer', 0, 'Tech Writer', 1, 0),
                        (139, 131, 2, 'Testing', 'Testing', 0, 'Testing', 1, 0)
                ", 0, null, __FILE__, __LINE__);
                
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                        WHERE languagecode != 'english'
                ", 0, null, __FILE__, __LINE__);
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $slng = mb_substr($res['languagecode'], 0, 3);
                        
                        $ilance->db->query("
                                ALTER TABLE " . DB_PREFIX . "skills
                                ADD title_" . $slng . " VARCHAR(100) NOT NULL
                                AFTER `parentid`
                        ", 0, null, __FILE__, __LINE__);
        
                        $ilance->db->query("
                                ALTER TABLE " . DB_PREFIX . "skills
                                ADD description_" . $slng . " VARCHAR(255) NOT NULL
                                AFTER `parentid`
                        ", 0, null, __FILE__, __LINE__);
            
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "skills
                                SET title_" . $slng . " = title_eng
                        ", 0, null, __FILE__, __LINE__);
                        
                        $ilance->db->query("
                                UPDATE " . DB_PREFIX . "skills
                                SET description_" . $slng . " = description_eng
                        ", 0, null, __FILE__, __LINE__);
                }
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "skills_answers (
                        `aid` INT(5) NOT NULL AUTO_INCREMENT,
                        `cid` INT(5) NOT NULL,
                        `user_id` INT(10) NOT NULL,
                        PRIMARY KEY  (`aid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "categories ADD `nondisclosefeeamount` FLOAT( 10, 2 ) NOT NULL AFTER `fixedfeeamount`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects DROP `additional_cid`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "feedback_criteria (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `title` MEDIUMTEXT,
                        `sort` INT(5) NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "feedback_criteria
                        (`id`, `title`, `sort`)
                        VALUES
                        (1, 'Quality', 30),
                        (2, 'Delivery', 40),
                        (3, 'Professionalism', 20),
                        (5, 'Price', 50),
                        (6, 'Item as described', 10),
                        (7, 'Communication', 50),
                        (8, 'Shipping time', 60);
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "feedback (
                        `id` INT(100) NOT NULL AUTO_INCREMENT,
                        `for_user_id` INT(10) NOT NULL default '0',
                        `project_id` INT(10) NOT NULL default '0',
                        `from_user_id` INT(10) NOT NULL default '0',
                        `comments` mediumtext,
                        `date_added` datetime NOT NULL default '0000-00-00 00:00:00',
                        `response` enum('','positive','neutral','negative') NOT NULL default '',
                        `type` enum('','buyer','seller') NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "feedback_ratings (
                        `id` int(100) NOT NULL auto_increment,
                        `user_id` int(10) NOT NULL default '0',
                        `project_id` int(10) NOT NULL default '0',
                        `criteria_id` int(10) NOT NULL default '0',
                        `rating` double NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "taxes ADD `entirecountry` INT( 1 ) NOT NULL DEFAULT '0' AFTER `invoicetypes`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DROP TABLE " . DB_PREFIX . "ratings_reviews", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "users
                            CHANGE `servicerating` `rating` DOUBLE NOT NULL DEFAULT '0.00',
                            CHANGE `servicescore` `score` INT( 5 ) NOT NULL DEFAULT '0'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "users ADD `feedback` DOUBLE NOT NULL DEFAULT '0' AFTER `score`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "projects_changelog (
                        `id` int(5) NOT NULL auto_increment,
                        `project_id` int(5) NOT NULL,
                        `datetime` datetime NOT NULL default '0000-00-00 00:00:00',
                        `changelog` mediumtext,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "categories ADD `level` INT( 5 ) NOT NULL DEFAULT '0' AFTER `parentid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "projects
                        DROP `buynow_items`,
                        DROP `buynow_costperitem`,
                        DROP `buynow_individually`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders DROP `items`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "budget CHANGE `budgetfrom` `budgetfrom` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00',
                        CHANGE `budgetto` `budgetto` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "taxes CHANGE `amount` `amount` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `ship_shipperid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `ship_costs`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `ship_trackingnumber` VARCHAR( 250 ) NOT NULL AFTER `ship_shipperid`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `uniquebidcount` INT( 5 ) NOT NULL DEFAULT '0' AFTER `retailprice`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'portfoliodisplay_popups_width', 'Maxium image width to display when viewing portfolio item in popup mode?', '490', 'portfoliodisplay', 'int', '', '', '', 5)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'portfoliodisplay_popups_height', 'Maxium image height to display when viewing portfolio item in popup mode?', '410', 'portfoliodisplay', 'int', '', '', '', 6)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "projects
                            ADD `isfvfpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvf`,
                            ADD `isifpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isfvfpaid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "search_favorites ADD `added` DATETIME NOT NULL AFTER `subscribed`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `invoiceid` `ifinvoiceid` INT( 5 ) NOT NULL DEFAULT '0'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `fvfinvoiceid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `ifinvoiceid`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `cat_id` `cid` INT( 10 ) NOT NULL DEFAULT '0'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects DROP `image_attachid`, DROP `item_attachid`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "projects
                        ADD `returnaccepted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvfinvoiceid` ,
                        ADD `returnwithin` ENUM( '0', '3', '7', '14', '30' ) NOT NULL DEFAULT '0' AFTER `returnaccepted` ,
                        ADD `returngivenas` ENUM( 'none', 'exchange', 'credit', 'moneyback' ) NOT NULL DEFAULT 'none' AFTER `returnwithin` ,
                        ADD `returnpolicy` MEDIUMTEXT NOT NULL AFTER `returngivenas`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "email ADD `cansend` INT(1) NOT NULL DEFAULT '1' AFTER `product`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `filtered_bidtype` `filtered_bidtype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids CHANGE `bidamounttype` `bidamounttype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_realtimebids CHANGE `bidamounttype` `bidamounttype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `escrowfee` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `amount`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `escrowfeebuyer` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `escrowfee`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE `fee` `fvf` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE `fee2` `fvfbuyer` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "buynow_orders
                        ADD `isescrowfeepaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvfbuyer` ,
                        ADD `isescrowfeebuyerpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfeepaid` ,
                        ADD `isfvfpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfeepaid` ,
                        ADD `isfvfbuyerpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isfvfpaid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "buynow_orders
                        ADD `escrowfeeinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `isfvfbuyerpaid` ,
                        ADD `escrowfeebuyerinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `escrowfeeinvoiceid` ,
                        ADD `fvfinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `escrowfeebuyerinvoiceid` ,
                        ADD `fvfbuyerinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `fvfinvoiceid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'registrationdisplay_defaultcity', 'Default city name to display on registration signup form [ie: Toronto]', 'Toronto', 'registrationdisplay', 'text', '', '', '', 4)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'escrowsystem_feestaxable', 'Enable tax on escrow commission fees? (only works when commission fee invoice types are enabled)', '0', 'escrowsystem', 'yesno', '', '', '', 100)", 0, null, __FILE__, __LINE__);
                
                // update the new level bit for the category structure system.
                $ilance->categories->set_levels();
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '8'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '3.1.5'
                        WHERE name = 'current_version'
                ", 0, null, __FILE__, __LINE__);
                
                // create v3_locations_states
                $dbengine = MYSQL_ENGINE;
                $dbtype = MYSQL_TYPE;
                include(DIR_SERVER_ROOT . 'install/functions/locations_schema.php');
                create_locations_schema($dbengine, $dbtype);
                flush();                
                
                // because the new xml files require 3.1.4, we set the version first (above)
                // then we'll import all new templates and phrases to avoid any system version conflicts
                echo '<br /><strong>Please wait ..</strong><br /><br />';
                
                // import (or detect upgrade) of new phrases
                echo import_language_phrases(10000, 0);
                
                // import (or detect upgrade) of new css templates
                echo import_templates();
                
                // import (or detect upgrade) of new email templates
                echo import_email_templates();
                
                echo '<br /><br /><strong>Complete!</strong>';
                echo "<br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br />\n";
        }
        else
        {
                echo '<br /><br /><strong>Error!</strong><br /><br />';
                echo 'It appears this SQL query has already been executed in the past.  No need to re-run. <a href="installer.php"><strong>Return to installer main menu</strong></a><br /><br />';
        }
}
else
{
        echo '<h1>Upgrade from 3.1.4 to 3.1.5</h1><p>The following SQL query will be executed:</p>';    
        echo '<hr size="1" width="100%" />';

        echo "ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'pulldown', 'multiplechoice', 'range', 'url' ) NOT NULL DEFAULT 'text'";
        echo "ALTER TABLE " . DB_PREFIX . "project_questions CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'pulldown', 'multiplechoice', 'range', 'url' ) NOT NULL DEFAULT 'text'";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_payperpost', 'Enable pay as you go auction system? When enabled, any fees incured during the posting of a single auction [insertion fees / enhancements] will need to be paid in full prior to it being publically visible within the marketplace. Additionally a button called Pay Later will allow the poster to directly pay for this later as well (from pending auctions).  If disabled, the auction will be posted publically and any fees incured will be unpaid pending transactions.', '0', 'globalauctionsettings', 'yesno', '', '', '', 60)";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showfees', 'Enable the listing fees table during the posting of an auction (will display insertion fee table (service/product) and/or budget based insertion fee table (service)', '1', 'globalauctionsettings', 'yesno', '', '', '', 70)<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "users ADD `isadmin` INT(1) NOT NULL DEFAULT '0' AFTER `daysonsite`, ADD `permissions` MEDIUMTEXT NOT NULL AFTER `isadmin`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "users ADD `searchoptions` MEDIUMTEXT NOT NULL AFTER `permissions`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects` CHANGE `ship_costs` `ship_costs` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `buynow_price` `buynow_price` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `buynow_costperitem` `buynow_costperitem` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `reserve_price` `reserve_price` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `startprice` `startprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `retailprice` `retailprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `currentprice` `currentprice` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `insertionfee` `insertionfee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "buynow_orders` CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fee` `fee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fee2` `fee2` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "creditcards`
        CHANGE `auth_amount1` `auth_amount1` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `auth_amount2` `auth_amount2` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "finalvalue`
        CHANGE `finalvalue_from` `finalvalue_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `finalvalue_to` `finalvalue_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `amountfixed` `amountfixed` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "increments`
        CHANGE `increment_from` `increment_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `increment_to` `increment_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "insertion_fees`
        CHANGE `insertion_from` `insertion_from` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `insertion_to` `insertion_to` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "invoices`
        CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00' DEFAULT '0.00',
        CHANGE `paid` `paid` FLOAT(10,2) NULL DEFAULT '0.00' DEFAULT '0.00',
        CHANGE `totalamount` `totalamount` FLOAT(10,2) NOT NULL DEFAULT '0.00' DEFAULT '0.00',
        CHANGE `taxamount` `taxamount` FLOAT(10,2) NOT NULL DEFAULT '0.00' DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "profile_questions` CHANGE `verifycost` `verifycost` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects_escrow`
        CHANGE `escrowamount` `escrowamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `shipping` `shipping` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `total` `total` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fee` `fee` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fee2` `fee2` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects_uniquebids` CHANGE `uniquebid` `uniquebid` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "project_bids`
        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "project_realtimebids`
        CHANGE `bidamount` `bidamount` FLOAT(10,2) NOT NULL DEFAULT '0.00',
        CHANGE `fvf` `fvf` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "proxybid` CHANGE `maxamount` `maxamount` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "ratings_reviews` CHANGE `amount` `amount` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "subscription` CHANGE `cost` `cost` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "timezones` ADD PRIMARY KEY ( `timezoneid` )<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_endsoondays', 'Enter the amount (in hours) auctions on the front page will be considered ending soon: values: [-1 = any date, 1 = 1 hour, 2 = 2 hours, 3 = 3 hours, 4 = 4 hours, 5 = 5 hours, 6 = 12 hours, 7 = 24 hours, 8 = 2 days, 9 = 3 days, 10 = 4 days, 11 = 5 days, 12 = 6 days, 13 = 7 days, 14 = 2 weeks, 15 = 1 month]', '7', 'globalauctionsettings', 'int', '', '', '', 80)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapgenres', 'Display custom questions underneath categories when viewing the category map?', '0', 'globalauctionsettings', 'yesno', '', '', '', 100)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_newicondays', 'Enter the amount (in days) auction categories will display a new auction posted icon', '7', 'globalauctionsettings', 'int', '', '', '', 110)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapdepth', 'Enter the category level depth (ie: Level1 > Level2 > Level3) to show within the category map display', '2', 'globalauctionsettings', 'int', '', '', '', 130)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catquestiondepth', 'How many custom category questions to display until the rest are hidden and before a more options link becomes visble', '3', 'globalauctionsettings', 'int', '', '', '', 140)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catmapgenredepth', 'How many subcategory levels deep will the category question genres be shown in the category map display', '1', 'globalauctionsettings', 'int', '', '', '', 160)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalfilters_defaultdeflated', 'Would you like to deflate (hide) all results by default?', '1', 'globalfilterresults', 'yesno', '', '', '', 1)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showcurrentcat', 'When viewing a selected category in the left nav should the selected category be bold showing subcategories underneath? Disabling will not show the selected category (only subcategories)', '1', 'globalauctionsettings', 'yesno', '', '', '', 170)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_catcutoff', 'How many categories to display until the rest are hidden and before a more options link becomes visble', '10', 'globalauctionsettings', 'int', '', '', '', 180)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalauctionsettings_showbackto', 'Would you like to show a (Back To: [cat]) link in the left nav menu when viewing deep categories while searching the marketplace?', '1', 'globalauctionsettings', 'yesno', '', '', '', 190)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'clean_old_log_entries', 'Delete log entries after n days (n=0 will not delete)?', '0', 'globalsecuritysettings', 'int', '', '', '', 101)<br /><br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showfeaturedlistings', 'Show featured listings on the homepage and other areas like search and category map?', '1', 'globalauctionsettings', 'yesno', '', '', '', 100)<br /><br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showendingsoonlistings', 'Show ending soon listings on the homepage?', '1', 'globalauctionsettings', 'yesno', '', '', '', 110)<br /><br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'showlatestlistings', 'Show latest listings scroll box to the right side on the homepage and other areas like the category map?', '1', 'globalauctionsettings', 'yesno', '', '', '', 120)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "search_favorites` CHANGE `cattype` `cattype` ENUM( 'rfp', 'product', 'experts', 'stores', 'wantads' ) NOT NULL DEFAULT 'rfp'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "categories` CHANGE `fixedfeeamount` `fixedfeeamount` FLOAT(10,2) NOT NULL DEFAULT '0.00'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "categories` ADD `description_eng` MEDIUMTEXT NOT NULL AFTER `title_eng`<br /><br />";        
        echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `rateperhour` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `searchoptions`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "profile_groups` DROP `allcids`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `profilevideourl` MEDIUMTEXT NOT NULL AFTER `rateperhour`<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'postjobs'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'serviceprofile'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'createnewstore'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'lancealert'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'referabuyer'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'referaseller'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailprofiletoafriend'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailproductvendor'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailauctionfriend'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'emailauctionsfriend'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewfeedback'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'uploadlogo'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewtransaction'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewstransaction'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'downloadCSV'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'notifywhenbidreceived'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'notifywhenprojectsposted'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewreferralaccount'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'newsletteropt_in'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'pressreleaseopt_in'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'viewoldarchives'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbuynow'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchprojects'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchproviders'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbuyers'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'searchbycategory'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'currencycalculator'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'maxinvitesperday'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "subscription_group_titles WHERE accessname = 'maxemailsperinvite'<br /><br />";
        
        echo "RENAME TABLE " . DB_PREFIX . "subscription_group_titles TO " . DB_PREFIX . "subscription_permissions<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "invoices ADD `parentid` INT( 10 ) NOT NULL AFTER `invoiceid`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "invoices ADD `indispute` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isif`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "categories` ADD `useproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `bidgroupdisplay`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "increments` ADD `groupname` VARCHAR( 250 ) NOT NULL DEFAULT 'default' AFTER `incrementid`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "increments` ADD `sort` INT( 5 ) NOT NULL DEFAULT '0' AFTER `amount`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "increments_groups (<br />
        `groupid` INT(5) NOT NULL AUTO_INCREMENT,<br />
        `groupname` VARCHAR(50) NOT NULL default 'default',<br />
        `description` MEDIUMTEXT,<br />
        PRIMARY KEY  (`groupid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "categories` ADD `incrementgroup` VARCHAR( 250 ) NOT NULL AFTER `finalvaluegroup`<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'productbid_capactive'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'productbid_caprate'<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalfilters_maxsubcategorydisplay'<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'moderationsystem_disableauctionmoderation'<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'moderationsystem_disableauctionmoderation', 'Disable auction listing moderation? (when disabled, the listing is posted for the public to see whereas when enabled, an email is dispatched for admin to verify)', '0', 'globalauctionsettings', 'yesno', '', '', '', 0)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "search_favorites` CHANGE `cattype` `cattype` ENUM( 'service', 'product', 'experts', 'stores', 'wantads' ) NOT NULL DEFAULT 'service'<br /><br />";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "distance_canada (<br />
                `ZIPCode` CHAR(30) NOT NULL default '',<br />
                `City` CHAR(30) NOT NULL default '',<br />
                `Province` CHAR(30) NOT NULL default '',<br />
                `Latitude` DOUBLE NOT NULL default '0',<br />
                `Longitude` DOUBLE NOT NULL default '0',<br />
                KEY `ZIPCode` (`ZIPCode`),<br />
                KEY `longlat` (`Longitude`,`Latitude`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
        ";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "distance_uk (<br />
                `ZIPCode` CHAR(30) NOT NULL default '',<br />
                `Latitude` VARCHAR(150) NOT NULL default '0',<br />
                `Longitude` VARCHAR(150) NOT NULL default '0',<br />
                KEY `ZIPCode` (`ZIPCode`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
        ";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "distance_usa (<br />
                `ZIPCode` CHAR(10) NOT NULL default '',<br />
                `ZIPCodeType` CHAR(5) NOT NULL default '',<br />
                `City` CHAR(50) NOT NULL default '',<br />
                `CityType` CHAR(5) NOT NULL default '',<br />
                `State` CHAR(50) NOT NULL default '',<br />
                `StateCode` CHAR(10) NOT NULL default '',<br />
                `AreaCode` CHAR(10) NOT NULL default '',<br />
                `Latitude` DOUBLE NOT NULL default '0',<br />
                `Longitude` DOUBLE NOT NULL default '0',<br />
                KEY `ZIPCode` (`ZIPCode`),<br />
                KEY `longlat` (`Longitude`,`Latitude`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
        ";
        
        echo "
		CREATE TABLE " . DB_PREFIX . "distance_nl (<br />
		`ZIPCode` CHAR(30) NOT NULL default '',<br />
		`Latitude` VARCHAR(150) NOT NULL default '0',<br />
		`Longitude` VARCHAR(150) NOT NULL default '0',<br />
		KEY `ZIPCode` (`ZIPCode`)<br />
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
	";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "skills (<br />
                `cid` int(100) NOT NULL auto_increment,<br />
                `parentid` int(100) NOT NULL default '0',<br />
                `level` int(5) NOT NULL default '1',<br />
                `title_eng` mediumtext,<br />
                `description_eng` mediumtext,<br />
                `views` int(100) NOT NULL default '0',<br />
                `keywords` mediumtext,<br />
                `visible` int(1) NOT NULL default '1',<br />
                `sort` int(3) NOT NULL default '0',<br />
                PRIMARY KEY  (`cid`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
        ";
        
        echo "
                INSERT INTO " . DB_PREFIX . "skills<br />
                (`cid`, `parentid`, `level`, `title_eng`, `description_eng`, `views`, `keywords`, `visible`, `sort`)<br />
                VALUES<br />
                (1, 0, 1, 'Programming', 'Programming', 0, 'Programming', 1, 0),<br />
                (2, 1, 2, 'AJAX', 'AJAX', 0, 'AJAX', 1, 0),<br />
                (3, 1, 2, 'ASP', 'ASP', 0, 'ASP', 1, 0),<br />
                (4, 1, 2, 'ASP.NET+ADO', 'ASP.NET+ADO', 0, 'ASP.NET+ADO', 1, 0),<br />
                (5, 1, 2, 'ActiveX', 'ActiveX', 0, 'ActiveX', 1, 0),<br />
                (6, 1, 2, 'Adobe Flex', 'Adobe Flex', 0, 'Adobe Flex', 1, 0),<br />
                (7, 1, 2, 'Assembler', 'Assembler', 0, 'Assembler', 1, 0),<br />
                (8, 1, 2, 'Borland C++ Builder', 'Borland C++ Builder', 0, 'Borland C++ Builder', 1, 0),<br />
                (9, 1, 2, 'C#/.Net', 'C#/.Net', 0, 'C#/.Net', 1, 0),<br />
                (10, 1, 2, 'C/C++/Unix', 'C/C++/Unix', 0, 'C/C++/Unix', 1, 0),<br />
                (11, 1, 2, 'C/C++/Win32SDK', 'C/C++/Win32SDK', 0, 'C/C++/Win32SDK', 1, 0),<br />
                (12, 1, 2, 'CSS', 'CSS', 0, 'CSS', 1, 0),<br />
                (13, 1, 2, 'CodeWarrior/C++', 'CodeWarrior/C++', 0, 'CodeWarrior/C++', 1, 0),<br />
                (14, 1, 2, 'ColdFusion', 'ColdFusion', 0, 'ColdFusion', 1, 0),<br />
                (15, 1, 2, 'Crystal Reports', 'Crystal Reports', 0, 'Crystal Reports', 1, 0),<br />
                (16, 1, 2, 'Delphi ', 'Delphi ', 0, 'Delphi ', 1, 0),<br />
                (17, 1, 2, 'Delphi/VB', 'Delphi/VB', 0, 'Delphi/VB', 1, 0),<br />
                (18, 1, 2, 'Driver development', 'Driver development', 0, 'Driver development', 1, 0),<br />
                (19, 1, 2, 'Flash/ActionScript', 'Flash/ActionScript', 0, 'Flash/ActionScript', 1, 0),<br />
                (20, 1, 2, 'FoxPro', 'FoxPro', 0, 'FoxPro', 1, 0),<br />
                (21, 1, 2, 'GTK programming', 'GTK programming', 0, 'GTK programming', 1, 0),<br />
                (22, 1, 2, 'Games/Windows', 'Games/Windows', 0, 'Games/Windows', 1, 0),<br />
                (23, 1, 2, 'HTML/DHTML', 'HTML/DHTML', 0, 'HTML/DHTML', 1, 0),<br />
                (24, 1, 2, 'Hyperion', 'Hyperion', 0, 'Hyperion', 1, 0),<br />
                (25, 1, 2, 'IntelliJ IDEA', 'IntelliJ IDEA', 0, 'IntelliJ IDEA', 1, 0),<br />
                (26, 1, 2, 'J2EE', 'J2EE', 0, 'J2EE', 1, 0),<br />
                (27, 1, 2, 'JBoss', 'JBoss', 0, 'JBoss', 1, 0),<br />
                (28, 1, 2, 'JFC', 'JFC', 0, 'JFC', 1, 0),<br />
                (29, 1, 2, 'JSP', 'JSP', 0, 'JSP', 1, 0),<br />
                (30, 1, 2, 'JavaScript', 'JavaScript', 0, 'JavaScript', 1, 0),<br />
                (31, 1, 2, 'Kylix ', 'Kylix ', 0, 'Kylix ', 1, 0),<br />
                (32, 1, 2, 'LaTeX', 'LaTeX', 0, 'LaTeX', 1, 0),<br />
                (33, 1, 2, 'Lingo', 'Lingo', 0, 'Lingo', 1, 0),<br />
                (34, 1, 2, 'Mason', 'Mason', 0, 'Mason', 1, 0),<br />
                (35, 1, 2, 'OCX', 'OCX', 0, 'OCX', 1, 0),<br />
                (36, 1, 2, 'PHP', 'PHP', 0, 'PHP', 1, 0),<br />
                (37, 1, 2, 'PHP/HTML/DHTML', 'PHP/HTML/DHTML', 0, 'PHP/HTML/DHTML', 1, 0),<br />
                (38, 1, 2, 'PHP/IIS/MS SQL', 'PHP/IIS/MS SQL', 0, 'PHP/IIS/MS SQL', 1, 0),<br />
                (39, 1, 2, 'PHP/MySQL', 'PHP/MySQL', 0, 'PHP/MySQL', 1, 0),<br />
                (40, 1, 2, 'Perl', 'Perl', 0, 'Perl', 1, 0),<br />
                (41, 1, 2, 'Python', 'Python', 0, 'Python', 1, 0),<br />
                (42, 1, 2, 'Qt', 'Qt', 0, 'Qt', 1, 0),<br />
                (43, 1, 2, 'Remoting', 'Remoting', 0, 'Remoting', 1, 0),<br />
                (44, 1, 2, 'Resin', 'Resin', 0, 'Resin', 1, 0),<br />
                (45, 1, 2, 'Ruby', 'Ruby', 0, 'Ruby', 1, 0),<br />
                (46, 1, 2, 'SOAP', 'SOAP', 0, 'SOAP', 1, 0),<br />
                (47, 1, 2, 'SatelliteForms', 'SatelliteForms', 0, 'SatelliteForms', 1, 0),<br />
                (48, 1, 2, 'Smarty', 'Smarty', 0, 'Smarty', 1, 0),<br />
                (49, 1, 2, 'Struts', 'Struts', 0, 'Struts', 1, 0),<br />
                (50, 1, 2, 'SyncML', 'SyncML', 0, 'SyncML', 1, 0),<br />
                (51, 1, 2, 'TCP/IP', 'TCP/IP', 0, 'TCP/IP', 1, 0),<br />
                (52, 1, 2, 'Tomcat', 'Tomcat', 0, 'Tomcat', 1, 0),<br />
                (53, 1, 2, 'Unix Shell', 'Unix Shell', 0, 'Unix Shell', 1, 0),<br />
                (54, 1, 2, 'VB/.NET', 'VB/.NET', 0, 'VB/.NET', 1, 0),<br />
                (55, 1, 2, 'VB/Delphi', 'VB/Delphi', 0, 'VB/Delphi', 1, 0),<br />
                (56, 1, 2, 'VB/Delphi/ASP/IIS', 'VB/Delphi/ASP/IIS', 0, 'VB/Delphi/ASP/IIS', 1, 0),<br />
                (57, 1, 2, 'VBA', 'VBA', 0, 'VBA', 1, 0),<br />
                (58, 1, 2, 'Visual Basic ', 'Visual Basic ', 0, 'Visual Basic ', 1, 0),<br />
                (59, 1, 2, 'VoiceXML', 'VoiceXML', 0, 'VoiceXML', 1, 0),<br />
                (60, 1, 2, 'WML/WMLScript', 'WML/WMLScript', 0, 'WML/WMLScript', 1, 0),<br />
                (61, 1, 2, 'WordPress', 'WordPress', 0, 'WordPress', 1, 0),<br />
                (62, 1, 2, 'XML', 'XML', 0, 'XML', 1, 0),<br />
                (63, 1, 2, 'XML-RPC', 'XML-RPC', 0, 'XML-RPC', 1, 0),<br />
                (64, 1, 2, 'XUL', 'XUL', 0, 'XUL', 1, 0),<br />
                (65, 1, 2, 'Zope/Python', 'Zope/Python', 0, 'Zope/Python', 1, 0),<br />
                (66, 0, 1, 'Databases', 'Databases', 0, 'Databases', 1, 0),<br />
                (67, 66, 2, 'Access', 'Access', 0, 'Access', 1, 0),<br />
                (68, 66, 2, 'Cobol', 'Cobol', 0, 'Cobol', 1, 0),<br />
                (69, 66, 2, 'Filemaker Pro ', 'Filemaker Pro ', 0, 'Filemaker Pro ', 1, 0),<br />
                (70, 66, 2, 'Informix', 'Informix', 0, 'Informix', 1, 0),<br />
                (71, 66, 2, 'InterBase', 'InterBase', 0, 'InterBase', 1, 0),<br />
                (72, 66, 2, 'MS-SQL', 'MS-SQL', 0, 'MS-SQL', 1, 0),<br />
                (73, 66, 2, 'MySQL', 'MySQL', 0, 'MySQL', 1, 0),<br />
                (74, 66, 2, 'Oracle DBA', 'Oracle DBA', 0, 'Oracle DBA', 1, 0),<br />
                (75, 66, 2, 'Oracle Forms', 'Oracle Forms', 0, 'Oracle Forms', 1, 0),<br />
                (76, 66, 2, 'Oracle PL/SQL', 'Oracle PL/SQL', 0, 'Oracle PL/SQL', 1, 0),<br />
                (77, 66, 2, 'Oracle Reports', 'Oracle Reports', 0, 'Oracle Reports', 1, 0),<br />
                (78, 66, 2, 'PostgreSQ', 'PostgreSQ', 0, 'PostgreSQ', 1, 0),<br />
                (79, 66, 2, 'SQL', 'SQL', 0, 'SQL', 1, 0),<br />
                (80, 66, 2, 'SQLite', 'SQLite', 0, 'SQLite', 1, 0),<br />
                (81, 66, 2, 'Sybase', 'Sybase', 0, 'Sybase', 1, 0),<br />
                (82, 0, 1, 'Mobile', 'Mobile', 0, 'Mobile', 1, 0),<br />
                (83, 82, 2, 'Blackberry/RIM ', 'Blackberry/RIM ', 0, 'Blackberry/RIM ', 1, 0),<br />
                (84, 82, 2, 'J2ME', 'J2ME', 0, 'J2ME', 1, 0),<br />
                (85, 82, 2, 'PalmOS', 'PalmOS', 0, 'PalmOS', 1, 0),<br />
                (86, 82, 2, 'PocketPC', 'PocketPC', 0, 'PocketPC', 1, 0),<br />
                (87, 82, 2, 'Symbian SDK', 'Symbian SDK', 0, 'Symbian SDK', 1, 0),<br />
                (88, 0, 1, 'Design/Graphics', 'Design/Graphics', 0, 'Design/Graphics', 1, 0),<br />
                (89, 88, 2, '3D Design', '3D Design', 0, '3D Design', 1, 0),<br />
                (90, 88, 2, 'Design/Flash', 'Design/Flash', 0, 'Design/Flash', 1, 0),<br />
                (91, 88, 2, 'Flash/Macromedia', 'Flash/Macromedia', 0, 'Flash/Macromedia', 1, 0),<br />
                (92, 88, 2, 'Graphics', 'Graphics', 0, 'Graphics', 1, 0),<br />
                (93, 88, 2, 'Macromedia Director', 'Macromedia Director', 0, 'Macromedia Director', 1, 0),<br />
                (94, 88, 2, 'Photoshop', 'Photoshop', 0, 'Photoshop', 1, 0),<br />
                (95, 88, 2, 'QNX', 'QNX', 0, 'QNX', 1, 0),<br />
                (96, 88, 2, 'UI Design', 'UI Design', 0, 'UI Design', 1, 0),<br />
                (97, 88, 2, 'Video Streaming', 'Video Streaming', 0, 'Video Streaming', 1, 0),<br />
                (98, 0, 1, 'Systems Admin', 'Systems Admin', 0, 'Systems Admin', 1, 0),<br />
                (99, 98, 2, 'AS/400', 'AS/400', 0, 'AS/400', 1, 0),<br />
                (100, 98, 2, 'LAMP administration ', 'LAMP administration ', 0, 'LAMP administration ', 1, 0),<br />
                (101, 98, 2, 'Mac OS X', 'Mac OS X', 0, 'Mac OS X', 1, 0),<br />
                (102, 98, 2, 'Windows Administration', 'Windows Administration', 0, 'Windows Administration', 1, 0),<br />
                (103, 0, 1, 'Application Servers', 'Application Servers', 0, 'Application Servers', 1, 0),<br />
                (104, 103, 2, 'Asterisk', 'Asterisk', 0, 'Asterisk', 1, 0),<br />
                (105, 103, 2, 'Lotus Domino', 'Lotus Domino', 0, 'Lotus Domino', 1, 0),<br />
                (106, 103, 2, 'Lotus Notes', 'Lotus Notes', 0, 'Lotus Notes', 1, 0),<br />
                (107, 103, 2, 'MS Navision', 'MS Navision', 0, 'MS Navision', 1, 0),<br />
                (108, 103, 2, 'Oracle Application Server', 'Oracle Application Server', 0, 'Oracle Application Server', 1, 0),<br />
                (109, 103, 2, 'OsCommerce', 'OsCommerce', 0, 'OsCommerce', 1, 0),<br />
                (110, 103, 2, 'Web Sphere', 'Web Sphere', 0, 'Web Sphere', 1, 0),<br />
                (111, 103, 2, 'WebLogic', 'WebLogic', 0, 'WebLogic', 1, 0),<br />
                (112, 0, 1, 'Platforms', 'Platforms', 0, 'Platforms', 1, 0),<br />
                (113, 112, 2, 'DotNetNuke', 'DotNetNuke', 0, 'DotNetNuke', 1, 0),<br />
                (114, 112, 2, 'EDI', 'EDI', 0, 'EDI', 1, 0),<br />
                (115, 112, 2, 'Hibernate', 'Hibernate', 0, 'Hibernate', 1, 0),<br />
                (116, 112, 2, 'Joomla', 'Joomla', 0, 'Joomla', 1, 0),<br />
                (117, 112, 2, 'Mambo', 'Mambo', 0, 'Mambo', 1, 0),<br />
                (118, 112, 2, 'Online Payments', 'Online Payments', 0, 'Online Payments', 1, 0),<br />
                (119, 112, 2, 'PowerBuilder', 'PowerBuilder', 0, 'PowerBuilder', 1, 0),<br />
                (120, 112, 2, 'Sharepoint', 'Sharepoint', 0, 'Sharepoint', 1, 0),<br />
                (121, 112, 2, 'Voice/Windows', 'Voice/Windows', 0, 'Voice/Windows', 1, 0),<br />
                (122, 112, 2, 'Wireless', 'Wireless', 0, 'Wireless', 1, 0),<br />
                (123, 112, 2, 'phpNuke', 'phpNuke', 0, 'phpNuke', 1, 0),<br />
                (124, 112, 2, 'postNuke', 'postNuke', 0, 'postNuke', 1, 0),<br />
                (125, 0, 1, 'Concepts', 'Concepts', 0, 'Concepts', 1, 0),<br />
                (126, 125, 2, 'Application Design', 'Application Design', 0, 'Application Design', 1, 0),<br />
                (127, 125, 2, 'Database Modeling', 'Database Modeling', 0, 'Database Modeling', 1, 0),<br />
                (128, 125, 2, 'Systems Programming', 'Systems Programming', 0, 'Systems Programming', 1, 0),<br />
                (129, 125, 2, 'UML', 'UML', 0, 'UML', 1, 0),<br />
                (130, 125, 2, 'VoIP', 'VoIP', 0, 'VoIP', 1, 0),<br />
                (131, 0, 1, 'Other', 'Other', 0, 'Other', 1, 0),<br />
                (132, 131, 2, 'Data Entry', 'Data Entry', 0, 'Data Entry', 1, 0),<br />
                (133, 131, 2, 'Project Management', 'Project Management', 0, 'Project Management', 1, 0),<br />
                (134, 131, 2, 'QA', 'QA', 0, 'QA', 1, 0),<br />
                (135, 131, 2, 'Recruiting', 'Recruiting', 0, 'Recruiting', 1, 0),<br />
                (136, 131, 2, 'SEO', 'SEO', 0, 'SEO', 1, 0),<br />
                (137, 131, 2, 'Search', 'Search', 0, 'Search', 1, 0),<br />
                (138, 131, 2, 'Tech Writer', 'Tech Writer', 0, 'Tech Writer', 1, 0),<br />
                (139, 131, 2, 'Testing', 'Testing', 0, 'Testing', 1, 0)<br /><br />
                ";
        
        echo "
		CREATE TABLE " . DB_PREFIX . "skills_answers (<br />
		`aid` INT(5) NOT NULL AUTO_INCREMENT,<br />
		`cid` INT(5) NOT NULL,<br />
		`user_id` INT(10) NOT NULL,<br />
		PRIMARY KEY  (`aid`)<br />
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
	";
        
        echo "ALTER TABLE " . DB_PREFIX . "categories ADD `nondisclosefeeamount` FLOAT( 10, 2 ) NOT NULL AFTER `fixedfeeamount`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "projects DROP `additional_cid`<br /><br />";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "feedback_criteria (<br />
                `id` INT(10) NOT NULL AUTO_INCREMENT,<br />
                `title` MEDIUMTEXT,<br />
                `sort` INT(5) NOT NULL,<br />
                PRIMARY KEY  (`id`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "
                INSERT INTO " . DB_PREFIX . "feedback_criteria<br />
                (`id`, `title`, `sort`)<br />
                VALUES<br />
                (1, 'Quality', 30),<br />
                (2, 'Delivery', 40),<br />
                (3, 'Professionalism', 20),<br />
                (5, 'Price', 50),<br />
                (6, 'Item as described', 10),<br />
                (7, 'Communication', 50),<br />
                (8, 'Shipping time', 60);<br /><br />";
        
        echo "
		CREATE TABLE " . DB_PREFIX . "feedback (<br />
		`id` INT(100) NOT NULL AUTO_INCREMENT,<br />
		`for_user_id` INT(10) NOT NULL default '0',<br />
		`project_id` INT(10) NOT NULL default '0',<br />
		`from_user_id` INT(10) NOT NULL default '0',<br />
		`comments` mediumtext,<br />
		`date_added` datetime NOT NULL default '0000-00-00 00:00:00',<br />
		`response` enum('','positive','neutral','negative') NOT NULL default '',<br />
		`type` enum('','buyer','seller') NOT NULL,<br />
		PRIMARY KEY  (`id`)<br />
		) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
	";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "feedback_ratings (<br />
                `id` int(100) NOT NULL auto_increment,<br />
                `user_id` int(10) NOT NULL default '0',<br />
                `project_id` int(10) NOT NULL default '0',<br />
                `criteria_id` int(10) NOT NULL default '0',<br />
                `rating` double NOT NULL,<br />
                PRIMARY KEY  (`id`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
                ";
        
        echo "ALTER TABLE " . DB_PREFIX . "taxes ADD `entirecountry` INT( 1 ) NOT NULL DEFAULT '0' AFTER `invoicetypes`<br /><br />";
        
        echo "DROP TABLE " . DB_PREFIX . "ratings_reviews<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "users<br />
        CHANGE `servicerating` `rating` DOUBLE NOT NULL DEFAULT '0.00',<br />
        CHANGE `servicescore` `score` INT( 5 ) NOT NULL DEFAULT '0'<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "users ADD `feedback` DOUBLE NOT NULL DEFAULT '0' AFTER `score`";
        
        echo "
                CREATE TABLE " . DB_PREFIX . "projects_changelog (<br />
                `id` int(5) NOT NULL auto_increment,<br />
                `project_id` int(5) NOT NULL,<br />
                `datetime` datetime NOT NULL default '0000-00-00 00:00:00',<br />
                `changelog` mediumtext,<br />
                PRIMARY KEY  (`id`)<br />
                ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />
        ";
        
        echo "ALTER TABLE " . DB_PREFIX . "categories ADD `level` INT( 5 ) NOT NULL DEFAULT '0' AFTER `parentid`<br />";
        
        echo "
                        ALTER TABLE " . DB_PREFIX . "projects<br />
                        DROP `buynow_items`,<br />
                        DROP `buynow_costperitem`,<br />
                        DROP `buynow_individually`<br /><br />
                ";
                
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders DROP `items`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "budget CHANGE `budgetfrom` `budgetfrom` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00',<br />
                        CHANGE `budgetto` `budgetto` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'<br />
                ";
        
        echo "ALTER TABLE " . DB_PREFIX . "taxes CHANGE `amount` `amount` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'<br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "projects ADD `ship_shipperid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `ship_costs`<br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "projects ADD `ship_trackingnumber` VARCHAR( 250 ) NOT NULL AFTER `ship_shipperid`<br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "projects ADD `uniquebidcount` INT( 5 ) NOT NULL DEFAULT '0' AFTER `retailprice`<br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'portfoliodisplay_popups_width', 'Maxium image width to display when viewing portfolio item in popup mode?', '490', 'portfoliodisplay', 'int', '', '', '', 5)<br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'portfoliodisplay_popups_height', 'Maxium image height to display when viewing portfolio item in popup mode?', '410', 'portfoliodisplay', 'int', '', '', '', 6)<br />";
        
        echo "
        ALTER TABLE " . DB_PREFIX . "projects<br />
        ADD `isfvfpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvf`,<br />
        ADD `isifpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isfvfpaid`<br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "search_favorites ADD `added` DATETIME NOT NULL AFTER `subscribed`<br />";
        echo "ALTER TABLE " . DB_PREFIX . "projects CHANGE `invoiceid` `ifinvoiceid` INT( 5 ) NOT NULL DEFAULT '0'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "projects ADD `fvfinvoiceid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `ifinvoiceid`<br />";
        echo "ALTER TABLE " . DB_PREFIX . "projects CHANGE `cat_id` `cid` INT( 10 ) NOT NULL DEFAULT '0'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "projects DROP `image_attachid`, DROP `item_attachid`";
        
        echo "
                ALTER TABLE " . DB_PREFIX . "projects<br />
                ADD `returnaccepted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvfinvoiceid` ,<br />
                ADD `returnwithin` ENUM( '0', '3', '7', '14', '30' ) NOT NULL DEFAULT '0' AFTER `returnaccepted` ,<br />
                ADD `returngivenas` ENUM( 'none', 'exchange', 'credit', 'moneyback' ) NOT NULL DEFAULT 'none' AFTER `returnwithin` ,<br />
                ADD `returnpolicy` MEDIUMTEXT NOT NULL AFTER `returngivenas`<br />
        <br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "email ADD `cansend` INT( 1 ) NOT NULL DEFAULT '1' AFTER `product`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "projects CHANGE `filtered_bidtype` `filtered_bidtype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "project_bids CHANGE `bidamounttype` `bidamounttype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "project_realtimebids CHANGE `bidamounttype` `bidamounttype` ENUM( 'entire', 'hourly', 'daily', 'weekly', 'monthly', 'lot', 'item', 'weight' ) NOT NULL DEFAULT 'entire'<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `escrowfee` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `amount`<br />";
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `escrowfeebuyer` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00' AFTER `escrowfee`<br />";
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE `fee` `fvf` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE `fee2` `fvfbuyer` FLOAT( 10, 2 ) NOT NULL DEFAULT '0.00'<br />";
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `isescrowfeepaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `fvfbuyer` ,<br />ADD `isescrowfeebuyerpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfeepaid` ,<br />ADD `isfvfpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfeepaid` ,<br />ADD `isfvfbuyerpaid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isfvfpaid`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "buynow_orders<br />
                                ADD `escrowfeeinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `isfvfbuyerpaid` ,<br />
                                ADD `escrowfeebuyerinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `escrowfeeinvoiceid` ,<br />
                                ADD `fvfinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `escrowfeebuyerinvoiceid` ,<br />
                                ADD `fvfbuyerinvoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `fvfinvoiceid`<br /><br />";
                                
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'registrationdisplay_defaultcity', 'Default city name to display on registration signup form [ie: Toronto]', 'Toronto', 'registrationdisplay', 'text', '', '', '', 4)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'escrowsystem_feestaxable', 'Enable tax on escrow commission fees? (only works when commission fee invoice types are enabled)', '0', 'escrowsystem', 'yesno', '', '', '', 100)<br /><br />";
        echo '<hr size="1" width="100%" />';
        
        echo "UPDATE `" . DB_PREFIX . "configuration` SET value = '8' WHERE name = 'current_sql_version'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET value = '3.1.5' WHERE name = 'current_version'<br /><br />";
        
        echo '<hr size="1" width="100%" />';
        
        echo '<strong><a href="installer.php?do=install&step=25&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, templates and phrases for you)';
}
?>
