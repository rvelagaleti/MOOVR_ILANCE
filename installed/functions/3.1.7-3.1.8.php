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

// #### FETCH VERSION INFO #####################################################
$sql = $ilance->db->query("
	SELECT value
	FROM " . DB_PREFIX . "configuration
	WHERE name = 'current_version'
", 0, null, __FILE__, __LINE__);
$res = $ilance->db->fetch_array($sql);

// #### BEGIN SQL UPDATE CODE ##################################################
$queries = array();

// #### HELPER FUNCTIONS ###############################################
// add_field_if_not_exist($table = '', $column = '', $attributes = '', $addaftercolumn = '') ....
// table_exists($table = '') ....
// field_exists($field = '', $table = '') ....
// $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Newsletter Resources', $accessdescription = 'Defines if any customer within this subscription group can opt-in to any of the available newsletter resources', $accessname = 'newsletteropt_in', $accesstype = 'yesno', $value = 'yes', $canremove = 0);

if ($ilance->db->table_exists(DB_PREFIX . "projects_trackbacks") == false)
{
	$queries[] =
        "CREATE TABLE `" . DB_PREFIX . "projects_trackbacks` (
        `trackbackid` INT(100) NOT NULL AUTO_INCREMENT,
        `project_id` INT(50) NOT NULL default '0',
        `ipaddress` MEDIUMTEXT,
        `url` MEDIUMTEXT,
        `visible` INT(1) NOT NULL default '1',
        PRIMARY KEY (`trackbackid`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "admincp_news") == false)
{
	$queries[] =
        "CREATE TABLE `" . DB_PREFIX . "admincp_news` (
        `newsid` INT(5) NOT NULL AUTO_INCREMENT,
        `content` MEDIUMTEXT,
        `datetime` DATETIME NOT NULL,
        `visible` INT(1) NOT NULL default '1',
        PRIMARY KEY (`newsid`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "motd") == false)
{
	$queries[] =
        "CREATE TABLE `" . DB_PREFIX . "motd` (
        `motdid` INT(5) NOT NULL AUTO_INCREMENT,
        `content` MEDIUMTEXT,
        `date` DATE NOT NULL,
        `visible` INT(1) NOT NULL default '1',
        PRIMARY KEY (`motdid`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "charities") == false)
{
	$queries[] =
        "CREATE TABLE `" . DB_PREFIX . "charities` (
        `charityid` INT(5) NOT NULL AUTO_INCREMENT,
        `title` MEDIUMTEXT NOT NULL,
        `description` MEDIUMTEXT NOT NULL,
        `url` VARCHAR(250) NOT NULL,
        `donations` INT(5) NOT NULL default '0',
	`earnings` FLOAT(10,2) NOT NULL default '0.00',
        `visible` INT(1) NOT NULL default '1',
        PRIMARY KEY (`charityid`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "distance_sp") == false)
{
        $queries[] =
        "CREATE TABLE " . DB_PREFIX . "distance_sp (
        `ZIPCode` CHAR(30) NOT NULL default '',
        `Latitude` DOUBLE NOT NULL default '0',
        `Longitude` DOUBLE NOT NULL default '0',
        KEY `ZIPCode` (`ZIPCode`),
        KEY `Latitude` (`Latitude`),
        KEY `Longitude` (`Longitude`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "distance_in") == false)
{
        $queries[] =
        "CREATE TABLE " . DB_PREFIX . "distance_in (
        `ZIPCode` CHAR(30) NOT NULL default '',
        `Latitude` DOUBLE NOT NULL default '0',
        `Longitude` DOUBLE NOT NULL default '0',
        KEY `ZIPCode` (`ZIPCode`),
        KEY `Latitude` (`Latitude`),
        KEY `Longitude` (`Longitude`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "distance_be") == false)
{
        $queries[] =
        "CREATE TABLE " . DB_PREFIX . "distance_be (
        `ZIPCode` CHAR(30) NOT NULL default '',
        `City` MEDIUMTEXT,
        `Latitude` DOUBLE NOT NULL default '0',
        `Longitude` DOUBLE NOT NULL default '0',
        KEY `ZIPCode` (`ZIPCode`),
        KEY `Latitude` (`Latitude`),
        KEY `Longitude` (`Longitude`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

if ($ilance->db->table_exists(DB_PREFIX . "sessions_bulkupload") == false)
{
        $queries[] =
        "CREATE TABLE " . DB_PREFIX . "sessions_bulkupload (
        `bulkid` INT(10) NOT NULL AUTO_INCREMENT,
        `user_id` INT(11) NOT NULL default '0',
        `col` MEDIUMTEXT,
        `data` MEDIUMTEXT,
        `dateupload` DATETIME NOT NULL default '0000-00-00 00:00:00',
        `visible` INT(1) NOT NULL default '1',
        `completed` INT(1) NOT NULL default '0',
        PRIMARY KEY  (`bulkid`)
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'bidsshortlisted', "INT(10) NOT NULL default '0'", 'AFTER `bids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'bidsretracted', "INT(10) NOT NULL default '0'", 'AFTER `bids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'bidsdeclined', "INT(10) NOT NULL default '0'", 'AFTER `bids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'haswinner', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `sellerfeedback`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'hasbuynowwinner', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `haswinner`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'winner_user_id', "INT(5) NOT NULL DEFAULT '0'", 'AFTER `hasbuynowwinner`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'useantisnipe', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `usereserveprice`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'charityid', "INT(5) NOT NULL DEFAULT '0'", 'AFTER `winner_user_id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'donationpercentage', "INT(5) NOT NULL DEFAULT '0'", 'AFTER `charityid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'donation', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `winner_user_id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'sellermarkedasshipped', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `ship_trackingnumber`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'sellermarkedasshippeddate', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'winnermarkedaspaid', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `winner_user_id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'winnermarkedaspaiddate', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'AFTER `winnermarkedaspaid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'donermarkedaspaid', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `donationpercentage`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'donermarkedaspaiddate', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'AFTER `donermarkedaspaid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "payment_configuration", 'visible', "INT(1) NOT NULL DEFAULT '1'", 'AFTER `sort`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'isregisterbonus', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `isp2bfee`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'depositcreditamount', "FLOAT(10,2) NOT NULL DEFAULT '0.00'", 'AFTER `isdeposit`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'withdrawdebitamount', "FLOAT(10,2) NOT NULL DEFAULT '0.00'", 'AFTER `withdrawinvoiceid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "users", 'autopayment', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `profileintro`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'p2b_paymethod', "MEDIUMTEXT NOT NULL", 'AFTER `p2b_user_id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'p2b_markedaspaid', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `p2b_paymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'featured_date', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'AFTER `featured`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'autorelist', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `bold`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'autorelist_date', "DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'", 'AFTER `autorelist`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "attachment", 'invoiceid', "INT(10) NOT NULL DEFAULT '0'", 'AFTER `exifdata`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "attachment", 'isexternal', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `invoiceid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'paymentgateway', "VARCHAR(200) NOT NULL DEFAULT ''", 'AFTER `paymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'isdonation', "INT(1) NOT NULL DEFAULT '0'", 'AFTER `indispute`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'charityid', "INT(5) NOT NULL DEFAULT '0'", 'AFTER `isdonation`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'currency_id', "INT(5) NOT NULL DEFAULT '0'", 'AFTER `parentid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'currency_rate', "VARCHAR(10) NOT NULL DEFAULT '0'", 'AFTER `currency_id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `user_status`";
$queries[] = "DELETE FROM " . DB_PREFIX . "email WHERE varname = 'product_auction_expired_via_cron'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('seourls_lowercase', 'Should characters in a SEF URL be all lowercase?', '0', 'globalseo', 'yesno', '', '', 'By default, SEF URLs if enabled will treat the URL characters with uppercase as well as lowercase characters.  This options allows you to force all characters in a SEF URL lowercase.  Once established in a production environment, it would not be recommended to change this option as it may affect search engine rankings when a url change is detected.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('showadmincpnews', 'Would you like to enable News from ILance on the Dashboard?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting will only pull latest news from ILance and when news is available a tab will be displayed on your main admin control panel dashboard letting you view the latest news from Team ILance.', 400, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_highlightactive', 'Enable highlight service auction feature?', '1', 'serviceupsell_highlight', 'yesno', '', '', '', 1, 1)";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'paypal_directpayment'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'stormpay_directpayment'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'cashu_directpayment'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'authnet_subscriptions'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget CHANGE `budgetfrom` `budgetfrom` DECIMAL(10,2) NOT NULL DEFAULT '0.00', CHANGE `budgetto` `budgetto` DECIMAL(10,2) NOT NULL DEFAULT '0.00'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('metatags', 'metatags', 'Meta Tag Settings', 'Manage search engine meta tag data within this area', 'tablehead_alt', '460')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metadescription', 'Template Meta Tag Description', '24 x 7 Auction Marketplace - Post your listings today', 'metatags', 'textarea', '', '', 'Your meta tag description should contain information about your marketplace, offering and other aspects of your business.  This meta description will appear everywhere except for instances where a custom description would apply (viewing a listing, searching the marketplace, category maps, etc).', 500, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metakeywords', 'Template Meta Tag Keywords', 'auction, reverse, marketplace, jobs, post a job, easy, providers, buy now, e-commerce', 'metatags', 'textarea', '', '', 'Your meta tag keywords should contain global keywords that directly relate to your offering or business.  These keywords will appear everywhere except for instances where a custom keyword would apply (searching the marketplace, category maps, etc).', 600, 1)";
$queries[] = "DELETE FROM " . DB_PREFIX . "templates WHERE name = 'template_metadescription'";
$queries[] = "DELETE FROM " . DB_PREFIX . "templates WHERE name = 'template_metakeywords'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `paymethod` `paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','check','stormpay','cashu','moneybookers') NOT NULL DEFAULT 'account'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE `paymethod` `paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','check','purchaseorder','stormpay','cashu','moneybookers','external') NOT NULL DEFAULT 'account'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE `invoicetype` `invoicetype` ENUM('store','storesubscription','subscription','commission','p2b','buynow','credential','debit','credit','escrow','refund') NOT NULL default 'subscription'";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'MoneyBookers', 'moneybookers', 'text', '', '', '', 10, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_business_email', 'Enter your MoneyBookers email address', 'payments@yourdomain.com', 'moneybookers', 'text', '', '', '', 20, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_secret_code', 'Enter the secret passphrase code [must be set at moneybookers.com]', 'mypassphrase', 'moneybookers', 'text', '', '', '', 30, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_md5_digest', 'Enable MD5 hash encryption feature? [must be set at moneybookers.com]', '1', 'moneybookers', 'yesno', '', '', '', 40, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_master_currency', 'Enter the currency used in MoneyBookers transactions', 'USD', 'moneybookers', 'int', '', '', '', 50, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'moneybookers', 'int', '', '', '', 60, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'moneybookers', 'int', '', '', '', 70, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_active', 'Allow members to deposit funds using this gateway?', '1', 'moneybookers', 'yesno', '', '', '', 80, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_registration', 'Show this payment method within registration pulldown menu?', '0', 'moneybookers', 'yesno', '', '', '', 90, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('moneybookers', 'moneybookers', 'MoneyBookers IPN Gateway Configuration', '', 'ipn')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_bidretract', 'Can service providers retract their bid proposal before they are awarded by the buyer?', '0', 'servicebid_limits', 'yesno', '', '', 'This setting defines the ability to let pre-awarded service providers (placebid status only) retract (remove) their bid proposal.  If you do not want service providers to retract their bids before a buyer awards them set this feature to disabled.', 10, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_bidretract', 'Can a product buyer retract their bid before the listing ends?', '0', 'productbid_limits', 'yesno', '', '', 'This setting allows you to define if a product buyer is able to retract their bid from a listing before the event ends.  When disabled, buyers cannot retract their bids before the listing ends.', 10, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_buyerunaward', 'Can buyers unaward a providers bid proposal after it was awarded?', '0', 'servicebid_limits', 'yesno', '', '', 'This setting defines the ability to let buyers unaward already awarded service providers.  If you do not want service buyers to unaward an already awarded bid they have already comitted to set this feature to disabled.', 10, 1)";
$queries[] = "TRUNCATE TABLE " . DB_PREFIX . "payment_methods";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_see_comments_for_payment_methods_accepted', 10)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_master_card', 20)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_visa', 30)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_debit_card', 40)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_paypal', 50)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_money_order', 60)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, '_personal_check', 70)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('resetpopulartags', 'Would you like to reset popular tags every month?', '1', 'globalfilterresults', 'yesno', '', '', 'To prevent abuse and keyword stuffing by users you can reset popular tags on a monthly basis.', 100, 1)";
$queries[] = "TRUNCATE TABLE " . DB_PREFIX . "sessions";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_slideshowcost', 'How much are sellers charged to upload each additional Slideshow Picture to their listing?', '0', 'productupsell_fees', 'int', '', '', 'When sellers list their item they can upload 1 free picture which is showcased in the search results.  If you would like to charge sellers for uploading more pictures (considered a slideshow) then enter the amount per each uploaded picture.  If you do not want to charge sellers for uploading slideshow pictures set this value to 0.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_reservepricecost', 'How much are sellers charged to set a Reserve Price amount on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for setting a Reserve Price (a bid-price that must be met before the item can sell) on their listing (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for setting a Reserve Price amount on their listing set this value to 0.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_buynowcost', 'How much are sellers charged to set a Buy Now Fixed Price amount on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for setting a Buy Now Fixed Price (a setting that lets buyers instantly purchase items to skip the bidding process) on their listing (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for setting a Buy Now Fixed Price amount on their listing set this value to 0.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_autorelist', 'Auto-Relist Upsell Listing Features', '', 'tablehead_alt', '80')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_autorelist', 'Auto-Relist Upsell Listing Features', '', 'tablehead_alt', '80')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistactive', 'Would you like to enable the auto-relist listing feature?', '1', 'productupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a seller choose to have their listing auto-relist if no bids are placed until the countdown expires.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfees', 'Can users pay to auto-relist their listing?', '1', 'productupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a seller have the ability to pay to auto-relist their listing through the use of auto-relist feature.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistfee', 'How much does it cost to use the auto-relist feature?', '3.75', 'productupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to auto-relist their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_autorelistmaxdays', 'How many days will the item be relisted for?', '7', 'productupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled auto-relist.  For example, enter 7 if you would like to auto-relist listings for 7 days.  After this 7 day period (for example) users will have to post a new listing.', 2, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistactive', 'Would you like to enable the auto-relist listing feature?', '1', 'serviceupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a buyer choose to have their listing auto-relist if no bids are placed until the countdown expires.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfees', 'Can users pay to auto-relist their listing?', '1', 'serviceupsell_autorelist', 'yesno', '', '', 'When enabled this setting will let a buyer have the ability to pay to auto-relist their listing through the use of auto-relist feature.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistfee', 'How much does it cost to use the auto-relist feature?', '3.75', 'serviceupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled the ability for users to pay to auto-relist their listing.  For example, enter 5.00 if you would like to charge five dollars.', 2, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_autorelistmaxdays', 'How many days will the project be relisted for?', '7', 'serviceupsell_autorelist', 'int', '', '', 'This setting works only when you have enabled auto-relist.  For example, enter 7 if you would like to auto-relist listings for 7 days.  After this 7 day period (for example) users will have to post a new project listing.', 2, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('bluepay', 'bluepay', 'BluePay Gateway Configuration', '', 'gateway')";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'BluePay', 'bluepay', 'text', '', '', '', 10, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'BluePay username', 'testing', 'bluepay', 'text', '', '', '', 20, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'BluePay password', 'testing', 'bluepay', 'pass', '', '', '', 30, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_key', 'BluePay transaction key', '', 'bluepay', 'pass', '', '', '', 40, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'BluePay transaction usage fee 1 [value in percentage; i.e: 0.029]', '0.029', 'bluepay', 'int', '', '', '', 50, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee2', 'BluePay transaction usage fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'bluepay', 'int', '', '', '', 60, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'BluePay credit card authentication process capture mode [auth|charge|capture]?', 'charge', 'bluepay', 'text', '', '', '', 70, 1)";	
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'BluePay credit card authentication process refund mode [process|void|credit]?', 'credit', 'bluepay', 'text', '', '', '', 80, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '1', 'bluepay', 'yesno', '', '', '', 90, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_enabled', 'Enable BluePay gateway module?', '1', 'bluepay', 'yesno', '', '', '', 100, 1)";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'use_internal_gateway'";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'use_internal_gateway', 'Please select your credit card payment gateway', 'none', 'defaultgateway', 'pulldown', '<select name=\"config[use_internal_gateway]\" style=\"font-family: verdana\"><option value=\"authnet\">Authorize.Net</option><option value=\"bluepay\">BluePay</option><option value=\"plug_n_pay\">PlugNPay</option><option value=\"psigate\">PSIGate</option><option value=\"eway\">eWAY</option><option value=\"none\" selected=\"selected\">Disable Credit Card Support</option></select>', 'defaultgateway', 'This setting ultimately informs the marketplace that you are allowing users to fund their online account balance using a credit card based on the selected merchant gateway.  If you would like to disable credit card support select disable credit card support from the pulldown menu.', 20, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_subscriptions', 'Enable MoneyBookers Recurring Subscriptions? (used in subscription menu)', '0', 'moneybookers', 'yesno', '', '', '', 90, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablenonprofits', 'Would you like to enable the Nonprofit Charity system?', '0', 'nonprofits', 'yesno', '', '', 'This option will allow sellers to choose a donation percentage and a nonprofit orgainzation. When enabled, the seller can choose if they will donate during the posting of their listing.  This feature is optional and is not forced upon your sellers.', 700, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('nonprofits', 'nonprofits', 'Nonprofit Settings', 'Manage Nonprofits and other related settings', 'tablehead_alt', '460')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkupload', 'Would you like to enable Bulk Listing Uploads?', '0', 'globalfiltersrfp', 'yesno', '', '', 'When enabled, sellers can choose to post their new items for sale via Bulk Upload.  This requires the seller to upload a .csv (comma separated value) file with pre-filled in values.  For example, in one upload session a seller could potentially upload 1000 listings in a matter of seconds.', 5, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadlimit', 'Maximum number of listings per Bulk Upload session?', '1000', 'globalfiltersrfp', 'int', '', '', 'This setting works only when Bulk Uploading is enabled.  This setting will define how many listings can be uploaded by any given user at any given time on a per bulk-import basis (meaning they can upload another xxx listings in a new bulk upload session).', 7, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalseo', 'globalseo', 'Search Engine Optimization Manager', 'Manage SEO settings and define various URL schemas', 'tablehead_alt', '480')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatschema', 'Search Engine Friendly Service Category Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly service category listing url schema.', 100, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatschema', 'Search Engine Friendly Product Category Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{CID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly product category listing url schema.', 200, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatidentifier', 'Service Category URL Identifier', 'Projects', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service categories.  Default is Projects.  Example output: domain.com/projects', 300, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatidentifier', 'Product Category URL Identifier', 'Items', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product categories.  Default is Items.  Example output: domain.com/items', 400, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicecatmapidentifier', 'Service Category Map URL Identifier', 'Categories/Projects', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service category maps.  Default is Categories/Projects.  Example output: domain.com/categories/projects', 500, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productcatmapidentifier', 'Product Category Map URL Identifier', 'Categories/Items', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product category maps.  Default is Categories/Items.  Example output: domain.com/categories/items', 600, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('categoryidentifier', 'Main Category URL Identifier', 'Categories', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for the main category index map.  Default is Categories.  Example output: domain.com/categories', 700, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('listingsidentifier', 'Main Listings URL Identifier', 'Listings', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for the main listings index map.  Default is Listings.  Example output: domain.com/listings', 800, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingschema', 'Search Engine Friendly Service Auction Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly service auction listing url schema.', 900, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingschema', 'Search Engine Friendly Product Auction Listing Schema', '{HTTP_SERVER}{IDENTIFIER}/{ID}/{KEYWORDS}{CATEGORY}{URLBIT}', 'globalseo', 'textarea', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly product auction listing url schema.', 1000, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicelistingidentifier', 'Service Auction URL Identifier', 'Project', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for service auction listings.  Default is Project.  Example output: domain.com/project', 1100, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productlistingidentifier', 'Product Auction URL Identifier', 'Item', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for product auction listings.  Default is Item.  Example output: domain.com/item', 1200, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users CHANGE `project_distance` `project_distance` INT(1) NOT NULL DEFAULT '1'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users CHANGE `currency_calculation` `currency_calculation` INT(1) NOT NULL DEFAULT '1'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users CHANGE `iprestrict` `iprestrict` INT(1) NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_invitations CHANGE `bid_placed` `bid_placed` INT(1) NOT NULL DEFAULT '0'";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `autopayment` `autopayment` INT(1) NOT NULL DEFAULT '1'";

// #### new 3.1.8 indexes
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `project_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `username` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `email` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `first_name` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `last_name` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `zip_code` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `country` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `rating` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `city` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `state` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `status` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `serviceawards` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "users ADD INDEX ( `score` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `project_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `cid` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `project_title` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "portfolio ADD INDEX ( `user_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "portfolio ADD INDEX ( `category_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "portfolio ADD INDEX ( `description` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment ADD INDEX ( `user_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment ADD INDEX ( `portfolio_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment ADD INDEX ( `project_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment ADD INDEX ( `category_id` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "locations ADD INDEX ( `location_eng` );";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_categories ADD INDEX ( `cid` );";

if ($res['value'] == '3.1.7')
{
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '12'
        WHERE name = 'current_sql_version'";
        
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '3.1.8'
        WHERE name = 'current_version'";
}
	
if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.1.7 to 3.1.8</h1><p>Updating database...</p>';
	
        if ($res['value'] == '3.1.7')
        {
                if (isset($queries) AND !empty($queries) AND is_array($queries))
		{
			foreach ($queries AS $upgradequery)
			{
				if (isset($upgradequery) AND !empty($upgradequery))
				{
					$ilance->db->query($upgradequery, 0, null, __FILE__, __LINE__);
				}
			}
		}
		
		// convert all tables to utf8 / utf8_general_ci
		echo convert_all_tables_collation('utf8_general_ci', 'utf8');
                
                // import (or detect upgrade) of new phrases
                echo import_language_phrases(10000, 0);
                
                // import (or detect upgrade) of new css templates
                echo import_templates();
                
                // import (or detect upgrade) of new email templates
                echo import_email_templates();
                
                echo '<br /><br /><strong>Complete!</strong>';
                echo "<div><br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br /></div>";
        }
        else
        {
                echo '<br /><br /><strong>Error!</strong><br /><br />';
                echo '<div>It appears this SQL query has already been executed in the past.  No need to re-run. <a href="installer.php"><strong>Return to installer main menu</strong></a><br /><br /></div>';
        }
}
else
{
        echo '<h1>Upgrade from 3.1.7 to 3.1.8</h1><p>The following SQL queries will be executed:</p>';    
        echo '<hr size="1" width="100%" style="margin:0px; padding:0px" />';

        if (isset($queries) AND !empty($queries) AND is_array($queries))
	{
		foreach ($queries AS $upgradequery)
		{
			if (isset($upgradequery) AND !empty($upgradequery))
			{
				echo '<div><textarea style="font-family: verdana" cols="102" rows="7">' . $upgradequery . '</textarea></div>';
				echo '<hr size="1" width="100%" />';
			}
		}
		
		echo '<div><strong><a href="installer.php?do=install&step=28&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
	}
	else
	{
		echo '<div>It appears there is nothing to upgrade. <a href="installer.php"><strong>Return to installer main menu</strong></a><br /><br /></div>';	
	}
}
?>