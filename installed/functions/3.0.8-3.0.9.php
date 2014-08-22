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

echo '<h1>Upgrade 3.0.8 to 3.0.9</h1><p>Updating database...</p>';
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'authnet', 'yesno', '', '', '', 9)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'plug_n_pay', 'yesno', '', '', '', 9)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'psigate', 'yesno', '', '', '', 8)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'eway', 'yesno', '', '', '', 7)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'Authorize.Net transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'authnet', 'int', '', '', '', 10)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PlugNPay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'plug_n_pay', 'int', '', '', '', 10)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'PSIGate transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'psigate', 'int', '', '', '', 9)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'eWAY transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'eway', 'int', '', '', '', 8)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'paypal', 'int', '', '', '', 7)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_master_currency', 'Enter the currency used in Paypal transactions', 'USD', 'paypal', 'int', '', '', '', 10)", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "payment_groups WHERE parentgroupname = 'defaultipn'", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'enable_outside_fees'", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'enable_internal_fees'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_invitations ADD `name` VARCHAR( 250 ) NOT NULL AFTER `email`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `buynow_left` `buynow_qty` INT( 10 ) NOT NULL DEFAULT '0'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects CHANGE `minimum_bidamount` `startprice` VARCHAR( 10 ) NOT NULL DEFAULT '0.00'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `currentprice` VARCHAR( 20 ) NOT NULL DEFAULT '0.00' AFTER `keywords`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `buynow_items` INT( 10 ) NOT NULL AFTER `buynow_qty`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `buynow_costperitem` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `buynow_items`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids ADD `qty` INT( 10 ) NOT NULL DEFAULT '1' AFTER `bidamount`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `buynow_individually` INT( 1 ) NOT NULL DEFAULT '0' AFTER `buynow_costperitem`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `items` INT( 10 ) NOT NULL DEFAULT '0' AFTER `qty`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `attachid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `invoiceid`", 0, null, __FILE__, __LINE__);

$sqlver = $ilance->db->query("SELECT version() AS version", 0, null, __FILE__, __LINE__);
$sqlres = $ilance->db->fetch_array($sqlver);
$dbengine = (version_compare($sqlres['version'], '4.0.18', '<')) ? 'TYPE' : 'ENGINE';
$dbtype = 'MyISAM';

$ilance->db->query("CREATE TABLE " . DB_PREFIX . "projects_uniquebids (
`uid` INT(100) NOT NULL AUTO_INCREMENT,
`project_id` INT(10) NOT NULL default '0',
`project_user_id` INT(100) NOT NULL default '0',
`user_id` INT(100) NOT NULL default '0',
`uniquebid` VARCHAR(10) NOT NULL default '0.00',
`date` DATETIME NOT NULL default '0000-00-00 00:00:00',
`status` ENUM('nonunique','unique') NOT NULL default 'nonunique',
PRIMARY KEY  (`uid`)
) " . $dbengine . "=" . $dbtype . "", 0, null, __FILE__, __LINE__);

$ilance->db->query("RENAME TABLE " . DB_PREFIX . "db_cache TO " . DB_PREFIX . "cache", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "cache CHANGE `id` `title` VARCHAR( 250 ) NOT NULL DEFAULT ''", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "users ADD `bidstoday` INT( 10 ) NOT NULL DEFAULT '0' AFTER `productscore`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "users ADD `bidsthismonth` INT( 10 ) NOT NULL DEFAULT '0' AFTER `bidstoday`", 0, null, __FILE__, __LINE__);

$ilance->db->query("CREATE TABLE " . DB_PREFIX . "cron (
cronid INT UNSIGNED NOT NULL AUTO_INCREMENT,
nextrun INT UNSIGNED NOT NULL DEFAULT '0',
weekday SMALLINT NOT NULL DEFAULT '0',
day SMALLINT NOT NULL DEFAULT '0',
hour SMALLINT NOT NULL DEFAULT '0',
minute VARCHAR(100) NOT NULL DEFAULT '',
filename CHAR(50) NOT NULL DEFAULT '',
loglevel SMALLINT NOT NULL DEFAULT '0',
active SMALLINT NOT NULL DEFAULT '1',
varname VARCHAR(100) NOT NULL DEFAULT '',
PRIMARY KEY (cronid),
KEY nextrun (nextrun),
UNIQUE KEY (varname)
) " . $dbengine . "=" . $dbtype . "", 0, null, __FILE__, __LINE__);

$ilance->db->query("INSERT INTO " . DB_PREFIX . "cron
(nextrun, weekday, day, hour, minute, filename, loglevel, varname)
VALUES
(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.subscriptions.php', 1, 'subscriptions'),
(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.rfp.php',	      1, 'rfp'),
(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.reminders.php',     1, 'reminders'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.currency.php',      1, 'currency'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyreports.php',  0, 'dailyreports'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.dailyrfp.php',      1, 'dailyrfp'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.creditcards.php',   1, 'creditcards'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.warnings.php',      1, 'warnings'),
(1053271600, -1,  1, -1, 'a:1:{i:0;i:0;}',  'cron.monthly.php',       1, 'monthly'),
(1053271600, -1,  1, -1, 'a:1:{i:0;i:-1;}',  'cron.watchlist.php',   1, 'watchlist')", 0, null, __FILE__, __LINE__);

$ilance->db->query("CREATE TABLE " . DB_PREFIX . "cronlog (
cronlogid INT UNSIGNED NOT NULL AUTO_INCREMENT,
varname VARCHAR(100) NOT NULL DEFAULT '',
dateline INT UNSIGNED NOT NULL DEFAULT '0',
description MEDIUMTEXT,
PRIMARY KEY (cronlogid),
KEY (varname)
) " . $dbengine . "=" . $dbtype . "", 0, null, __FILE__, __LINE__);

$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'databasecronjobs', 'Select yes if you prefer to use the ILance database cron job system vs file system', '0', 'automation', 'yesno', '', '', '', 1)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES (NULL, 'automation', 'automation', 'Automation System Settings', '')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES (NULL, 'search', 'search', 'Search Engine Settings', '')", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_user ADD `recurring` INT( 1 ) NOT NULL DEFAULT '0' AFTER `migratelogic`", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_subscriptions', 'Enable Paypal Recurring Subscriptions? (used in subscription menu)', '0', 'paypal', 'yesno', '', '', '', 120)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalfilters_cansendpms', 'Enable the ability for members to compose private messages to each other?', '0', 'globalfilterspmb', 'yesno', '', '', '', 20)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'invoicesystem_showlivedepositfees', 'Show live deposit gateway fee + calculation breakdown in deposit menu?', '0', 'invoicesystem', 'yesno', '', '', '', 20)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'searchfloodprotect', 'Enable search engine flood protection (can query search every x seconds)?', '0', 'search', 'yesno', '', '', '', 10)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'searchflooddelay', 'If flood protection is on how many seconds must each user wait between searches?', '20', 'search', 'int', '', '', '', 20)", 0, null, __FILE__, __LINE__);

$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'min_1_stars_value', 'Minimum rating (out of 5) to show at least 1 star', '1', 'servicerating', 'int', '', '', '', 60)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'max_1_stars_value', 'Maximum rating (out of 5) to show at least 1 star', '1.99', 'servicerating', 'int', '', '', '', 70)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'min_2_stars_value', 'Minimum rating (out of 5) to show at least 2 stars', '2', 'servicerating', 'int', '', '', '', 80)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'max_2_stars_value', 'Maximum rating (out of 5) to show at least 2 stars', '2.99', 'servicerating', 'int', '', '', '', 90)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'min_3_stars_value', 'Minimum rating (out of 5) to show at least 3 stars', '3', 'servicerating', 'int', '', '', '', 100)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'max_3_stars_value', 'Maximum rating (out of 5) to show at least 3 stars', '3.99', 'servicerating', 'int', '', '', '', 110)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'min_4_stars_value', 'Minimum rating (out of 5) to show at least 4 stars', '4', 'servicerating', 'int', '', '', '', 120)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'max_4_stars_value', 'Maximum rating (out of 5) to show at least 4 stars', '4.84', 'servicerating', 'int', '', '', '', 130)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'min_5_stars_value', 'Minimum rating (out of 5) to show at least 5 stars', '4.85', 'servicerating', 'int', '', '', '', 140)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'max_5_stars_value', 'Maximum rating (out of 5) to show at least 5 stars', '5', 'servicerating', 'int', '', '', '', 150)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'productbid_awardbidretract', 'Can a bidder retract their winning bid after they have won an auction?', '0', 'productbid_limits', 'yesno', '', '', '', 20)", 0, null, __FILE__, __LINE__);

$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `subcategory_id`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `buynow_id`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `nda_id`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `profile_id`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `bid_id`", 0, null, __FILE__, __LINE__);

$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'current_version', 'Framework repository files to serve [do not change]', '".ILANCEVERSION."', 'globalserverliveupdate', 'int', '', '', '', 4)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'current_sql_version', 'SQL framework repository files to serve [do not change]', '0', 'globalserverliveupdate', 'int', '', '', '', 5)", 0, null, __FILE__, __LINE__);

// add new subscription permissions to all existing subscription groups
$ilance->subscription_plan->add_subscription_permissions('Private Messages Limit', 'Total amount of private message boards a customer can create within this subscription group', 'pmbtotal', 'int', 500, 0);
$ilance->subscription_plan->add_subscription_permissions('Private Message Composing', 'Defines if any customer within this subscription group can compose new private messages to other registered members without going through the auction event process', 'pmbcompose', 'yesno', 'no', 0);

// add new template folder identifier
$sqlt = $ilance->db->query("SELECT styleid FROM " . DB_PREFIX . "styles", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqlt) > 0)
{
    while ($res = $ilance->db->fetch_array($sqlt))
    {
        $ilance->db->query("INSERT INTO " . DB_PREFIX . "templates
        (tid, gid, name, description, original, content, type, status, createdate, author, version, styleid) VALUES (
        NULL,
        '-1',
        'template_folder',
        'HTML Templates Folder Location',
        'templates/',
        'templates/',
        'variable',
        '1',
        NOW(),
        'ILance',
        '1.0',
        '".$res['styleid']."')", 0, null, __FILE__, __LINE__);
    }
}

// properly name the character set field rather than have other developers second guess what a metaequiv is :-)
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "language CHANGE `metaequiv` `charset` VARCHAR( 100 ) NOT NULL", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "preferences ADD `dnbnumber` VARCHAR( 100 ) NOT NULL AFTER `regnumber`", 0, null, __FILE__, __LINE__);
// fixes bug where bids are not shown in project detail pages
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids CHANGE `bidstate` `bidstate` ENUM('', 'reviewing', 'wait_approval', 'shortlisted', 'invited', 'archived', 'expired', 'retracted' ) default ''", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_escrow ADD `fee2` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `fee`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "sessions DROP `iscron`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "sessions DROP `ismod`", 0, null, __FILE__, __LINE__);

$ilance->db->query("CREATE TABLE " . DB_PREFIX . "sessions_acp (
`sesskey` VARCHAR(32) NOT NULL default '',
`expiry` INT(11) NOT NULL default '0',
`value` MEDIUMTEXT NOT NULL default '',
`userid` INT(11) NOT NULL default '0',
`agent` TEXT NOT NULL default '',
`ipaddress` VARCHAR(25) NOT NULL default '',
`url` VARCHAR(250) NOT NULL default '',
`title` VARCHAR(100) NOT NULL default '',
`firstclick` VARCHAR(50) NOT NULL default '',
`lastclick` VARCHAR(50) NOT NULL default '',
`browser` VARCHAR(50) NOT NULL default 'unknown',
PRIMARY KEY  (`sesskey`)
) " . $dbengine . "=" . $dbtype . "", 0, null, __FILE__, __LINE__);

$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_escrow ADD `shipping` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `bidamount`", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "api VALUES ('main_start', '')", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `paymethodoptions` MEDIUMTEXT NOT NULL AFTER `paymethod`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `fee` VARCHAR( 20 ) NOT NULL DEFAULT '0.00' AFTER `amount`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `fee2` VARCHAR( 20 ) NOT NULL DEFAULT '0.00' AFTER `fee`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_roles CHANGE `roletype` `roletype` ENUM( 'service', 'product', 'both' ) NOT NULL DEFAULT 'service'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_roles CHANGE `roleusertype` `roleusertype` ENUM( 'servicebuyer', 'serviceprovider', 'productbuyer', 'merchantprovider', 'all' ) NOT NULL DEFAULT 'servicebuyer'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE `status` `status` ENUM( 'paid', 'cancelled', 'pending_delivery', 'delivered', 'fraud', 'offline', 'offline_delivered' ) NOT NULL DEFAULT 'paid'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_escrow ADD `total` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `shipping`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `insertionfee` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `currentprice`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids ADD `fvf` VARCHAR( 20 ) NOT NULL DEFAULT '0.00' AFTER `bidcustom`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices ADD `isfvf` INT( 1 ) NOT NULL DEFAULT '0' AFTER `iswithdraw`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices ADD `isif` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isfvf`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALERT TABLE " . DB_PREFIX . "pmb_alerts ADD `track_popup` INT( 1 ) NOT NULL DEFAULT '0' AFTER `track_dateread`", 0, null, __FILE__, __LINE__);

$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_uniquebids CHANGE `status` `status` ENUM( 'nonunique', 'unique', 'lowestunique' ) NOT NULL DEFAULT 'nonunique'", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'enable_uniquebidding', 'Enable the unique bidding event system (only admins can create)?', '0', 'globalauctionsettings', 'yesno', '', '', '', 50)", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "product_questions CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown' ) NOT NULL DEFAULT 'text'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `retailprice` VARCHAR( 10 ) NOT NULL DEFAULT '0.00' AFTER `startprice`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `response` TEXT NOT NULL AFTER `uniquebid`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `totalbids` INT( 10 ) NOT NULL DEFAULT '1' AFTER `status`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "emaillog CHANGE `logtype` `logtype` ENUM( 'escrow', 'subscription', 'subscriptionremind', 'send2friend', 'alert', 'queue', 'dailyservice', 'dailyproduct', 'dailyreport', 'watchlist' ) NOT NULL DEFAULT 'alert'", 0, null, __FILE__, __LINE__);

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
) " . $dbengine . "=" . $dbtype . "", 0, null, __FILE__, __LINE__);

$ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'serviceupsell_wysiwyg' LIMIT 1", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'productupsell_wysiwyg' LIMIT 1", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'productupsell' AND parentgroupname = 'productupsell' LIMIT 1", 0, null, __FILE__, __LINE__);

echo '<br /><br /><strong>Upgrade Complete!</strong> Please follow the next steps:<br /><br />
1. Import the latest phrases: ./install/xml/ilance-phrases.xml in your AdminCP > Languages > Import Menu
2. In AdminCP > Settings > Automation menu > Settings (tab) choose to run the cron jobs via Database mode!!
';
echo "<br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br />\n";
?>