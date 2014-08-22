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

/*
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

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'bidsshortlisted', "INT(10) NOT NULL default '0'", 'AFTER `bids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `user_status`";
*/

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "sessions_bulkupload", 'dateupload', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `data`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "sessions_bulkupload", 'visible', "INT(1) NOT NULL default '1'", 'AFTER `dateupload`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "sessions_bulkupload", 'completed', "INT(1) NOT NULL default '0'", 'AFTER `visible`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'paymethodoptionsemail', "VARCHAR(250) NOT NULL default ''", 'AFTER `paymethodoptions`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "charities", 'groupid', "INT(10) NOT NULL default '0'", 'AFTER `charityid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'buynow_purchases', "INT(10) NOT NULL default '0'", 'AFTER `buynow_qty`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'winnermarkedaspaidmethod', "MEDIUMTEXT NOT NULL", 'AFTER `winnermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'donationinvoiceid', "INT(5) NOT NULL default '0'", 'AFTER `donermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'isdonationfee', "INT(1) NOT NULL default '0'", 'AFTER `isp2bfee`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'ischaritypaid', "INT(1) NOT NULL default '0'", 'AFTER `isdonationfee`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "invoices", 'charityid', "INT(5) NOT NULL default '0'", 'AFTER `ischaritypaid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'isfeepaid', "INT(1) NOT NULL default '0'", 'AFTER `fee2`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'isfee2paid', "INT(1) NOT NULL default '0'", 'AFTER `isfeepaid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'feeinvoiceid', "INT(5) NOT NULL default '0'", 'AFTER `isfee2paid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'fee2invoiceid', "INT(5) NOT NULL default '0'", 'AFTER `feeinvoiceid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "feedback", 'for_user_id', "INT(10) NOT NULL default '0'", 'AFTER `id`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_fees', 'Reverse Auction Fees and Other Related Costs', '', 'tablehead_alt', '490')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_videodescriptioncost', 'How much are buyers charged to use Video Description URLs on their listing?', '0', 'serviceupsell_fees', 'int', '', '', 'If you would like to charge buyers for the use of videos within their listings (considered an enhancement) then enter the amount to charge.  If you do not want to charge buyers a fee for video descriptions set this value to 0.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_videodescriptioncost', 'How much are sellers charged to use Video Description URLs on their listing?', '0', 'productupsell_fees', 'int', '', '', 'If you would like to charge sellers for the use of videos within their listings (considered an enhancement) then enter the amount to charge.  If you do not want to charge sellers a fee for video descriptions set this value to 0.', 1, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('template_metatitle', 'Template Meta Tag Page Title', 'Bid on new and used electronics, clothing, automobiles and more', 'metatags', 'textarea', '', '', 'Your meta tag page title should contain a global slogan or statement regarding the overall purpose of the marketplace, offering or business.  This page title will appear on the main marketplace landing page.', 400, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'paypal', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses PayPal as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 150, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'stormpay_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'stormpay', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses StormPay as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 120, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cashu_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'cashu', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses CashU as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 110, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'moneybookers_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'moneybookers', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses MoneyBookers as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 110, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `filter_escrow` `filter_escrow` INT( 1 ) NOT NULL DEFAULT '0'";
$queries[] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_subscriptions', 'Enable Authorize.Net Recurring Subscriptions? (used in subscription menu)', '0', 'authnet', 'yesno', '', '', '', 110, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment CHANGE `attachtype` `attachtype` ENUM('profile','portfolio','project','itemphoto','bid','pmb','ws','kb','ads','digital','slideshow','stores','storesitemphoto','storesdigital') NOT NULL DEFAULT 'profile'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('expertslistingidentifier', 'Experts Category URL Identifier', 'Experts', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for expert category listings.  Default is Experts.  Example output: domain.com/experts', 1300, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('memberslistingidentifier', 'Members Profile/Feedback URL Identifier', 'Members', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for viewing members including feedback history and profile detail specifics.  Default is Members.  Example output: domain.com/members', 1400, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('portfolioslistingidentifier', 'Portfolios URL Identifier', 'Portfolios', 'globalseo', 'text', '', '', 'This setting works only when SEO is enabled.  This setting will define your search engine friendly url identifier for viewing portfolios. Default is Portfolios.  Example output: domain.com/portfolios', 1500, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "locations ADD INDEX (`locationid`)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback ADD INDEX (`for_user_id`)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback ADD INDEX (`from_user_id`)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "attachment_folder ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "bid_fields ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "bid_fields_answers ADD INDEX ( `fieldid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "bid_fields_answers ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget ADD INDEX ( `budgetgroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget ADD INDEX ( `title` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget ADD INDEX ( `fieldname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget ADD INDEX ( `insertiongroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "budget_groups ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `buyer_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `owner_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `attachid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD INDEX ( `status` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `parentid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `level` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `cattype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `bidgroupdisplay` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `budgetgroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `insertiongroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `finalvaluegroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "categories ADD INDEX ( `incrementgroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "charities ADD INDEX ( `groupid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration ADD INDEX ( `configgroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration ADD INDEX ( `inputname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration_groups ADD INDEX ( `parentgroupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration_groups ADD INDEX ( `class` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "creditcards ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "creditcards ADD INDEX ( `creditcard_number` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "cron ADD INDEX ( `product` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "cronlog ADD INDEX ( `dateline` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "currency ADD INDEX ( `currency_abbrev` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "currency ADD INDEX ( `currency_name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "email ADD INDEX ( `varname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "email ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "email ADD INDEX ( `type` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "email ADD INDEX ( `product` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "email ADD INDEX ( `departmentid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD INDEX ( `logtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD INDEX ( `sent` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD INDEX ( `criteria_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD INDEX ( `feedbackid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD INDEX ( `for_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD INDEX ( `from_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "finalvalue ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "finalvalue ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "finalvalue_groups ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "increments ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "increments ADD INDEX ( `cid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "increments_groups ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "insertion_fees ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "insertion_fees ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "insertion_groups ADD INDEX ( `groupname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "insertion_groups ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoicelog ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoicelog ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoicelog ADD INDEX ( `invoicetype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `parentid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `currency_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `subscriptionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `projectid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `buynowid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `p2b_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `orderid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `status` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `invoicetype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `paymethod` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "invoices ADD INDEX ( `transactionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "language ADD INDEX ( `title` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "language ADD INDEX ( `languagecode` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "locations_cities ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "messages ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "messages ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules ADD INDEX ( `modulegroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules ADD INDEX ( `parentkey` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules ADD INDEX ( `tab` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules ADD INDEX ( `subcmd` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules_group ADD INDEX ( `modulegroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules_group ADD INDEX ( `modulename` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules_group ADD INDEX ( `folder` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "modules_group ADD INDEX ( `configtable` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "payment_configuration ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "payment_configuration ADD INDEX ( `configgroup` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "payment_configuration ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "payment_groups ADD INDEX ( `moduletype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb ADD INDEX ( `event_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb ADD INDEX ( `subject` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb_alerts ADD INDEX ( `event_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb_alerts ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb_alerts ADD INDEX ( `from_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "pmb_alerts ADD INDEX ( `to_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_answers ADD INDEX ( `questionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_answers ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_questions ADD INDEX ( `cid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_questions ADD INDEX ( `formname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_questions ADD INDEX ( `formdefault` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "product_questions ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_answers ADD INDEX ( `questionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_answers ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_answers ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_filter_auction_answers ADD INDEX ( `questionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_filter_auction_answers ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_filter_auction_answers ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_filter_auction_answers ADD INDEX ( `filtertype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_groups ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_groups ADD INDEX ( `description` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_groups ADD INDEX ( `cid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_questions ADD INDEX ( `groupid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_questions ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "profile_questions ADD INDEX ( `filtercategory` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `status` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `project_details` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `project_type` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `project_state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects ADD INDEX ( `charityid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_changelog ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `bid_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `project_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD INDEX ( `status` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_trackbacks ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD INDEX ( `project_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD INDEX ( `status` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_answers ADD INDEX ( `questionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_answers ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `project_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `bidstatus` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `bidstate` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `bidamounttype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bid_retracts ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bid_retracts ADD INDEX ( `bid_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_bid_retracts ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_invitations ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_invitations ADD INDEX ( `buyer_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_invitations ADD INDEX ( `seller_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_questions ADD INDEX ( `cid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_questions ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `bid_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `project_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `bidstatus` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `bidstate` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `bidamounttype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD INDEX ( `state` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "proxybid ADD INDEX ( `project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "proxybid ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "referral_clickthroughs ADD INDEX ( `ipaddress` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "referral_data ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "referral_data ADD INDEX ( `referred_by` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "referral_data ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "register_answers ADD INDEX ( `questionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "register_answers ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "register_questions ADD INDEX ( `pageid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "register_questions ADD INDEX ( `inputtype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "search_favorites ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "search_favorites ADD INDEX ( `cattype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "search_users ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "sessions ADD INDEX ( `userid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "sessions ADD INDEX ( `ipaddress` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "sessions ADD INDEX ( `token` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "sessions_bulkupload ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "skills ADD INDEX ( `parentid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "skills ADD INDEX ( `level` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "skills_answers ADD INDEX ( `cid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "skills_answers ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "stars ADD INDEX ( `pointsfrom` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "stars ADD INDEX ( `pointsto` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "stars ADD INDEX ( `icon` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "styles ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `title` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `subscriptiongroupid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `roleid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `active` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `migrateto` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription ADD INDEX ( `migratelogic` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscriptionlog ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_group ADD INDEX ( `title` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD INDEX ( `subscriptiongroupid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD INDEX ( `accessname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD INDEX ( `accesstype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD INDEX ( `value` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_roles ADD INDEX ( `title` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_roles ADD INDEX ( `roletype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_roles ADD INDEX ( `roleusertype` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `subscriptionid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `paymethod` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `active` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `migratelogic` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user_exempt ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user_exempt ADD INDEX ( `accessname` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user_exempt ADD INDEX ( `value` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "subscription_user_exempt ADD INDEX ( `invoiceid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "templates ADD INDEX ( `name` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "templates ADD INDEX ( `type` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "templates ADD INDEX ( `styleid` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD INDEX ( `user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD INDEX ( `watching_user_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD INDEX ( `watching_project_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD INDEX ( `watching_category_id` )";
$queries[] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD INDEX ( `state` )";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'paypal_registration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'stormpay_registration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'cashu_registration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'moneybookers_registration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'purchaseorder_registration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'enablewireregistration'";
$queries[] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'checkpayment_support'";
$queries[] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalsecurity_htmlencrypt'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('escrowsystem_payercancancelfundsafterrelease', 'Can a user who releases funds cancel and return those funds back to their account balance?', '0', 'escrowsystem', 'yesno', '', '', 'Depending on your niche, you can let the person paying into an escrow and who has already released funds from that escrow into the receivers account balance force a funding cancellation request which would move funds previously paid and released back to the payers online account balance.  By default this setting is disabled as it can lead to many disputes if abused by the end user.  Fees (if applied) will also be reversed.', 3, 1)";

if ($res['value'] == '3.1.8')
{
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '13'
        WHERE name = 'current_sql_version'";
        
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '3.1.9'
        WHERE name = 'current_version'";
}
	
if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.1.8 to 3.1.9</h1><p>Updating database...</p>';
	
        if ($res['value'] == '3.1.8')
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
        echo '<h1>Upgrade from 3.1.8 to 3.1.9</h1><p>The following SQL queries will be executed:</p>';    
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
		
		echo '<div><strong><a href="installer.php?do=install&step=29&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
	}
	else
	{
		echo '<div>It appears there is nothing to upgrade. <a href="installer.php"><strong>Return to installer main menu</strong></a><br /><br /></div>';	
	}
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>