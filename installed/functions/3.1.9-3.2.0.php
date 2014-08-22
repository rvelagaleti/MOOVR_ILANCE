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

// we will need to have a `product` field for addon developers adding their own custom css ..
$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "templates", 'product', "VARCHAR(250) NOT NULL default 'ilance'", 'AFTER `styleid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

// with css being more orgainzed we are moving toward designers being able to sort their css elements exactly as required ..
$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "templates", 'sort', "INT(10) NOT NULL default '100'", 'AFTER `product`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

// new nested category logic for ilance introduced
$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'lft', "INT(10) NOT NULL", 'AFTER `sort`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'rgt', "INT(10) NOT NULL", 'AFTER `lft`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "categories", 'sets', "LINESTRING NOT NULL", 'AFTER `parentid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "attachment", 'thumbnail_filedata', "LONGBLOB NOT NULL", 'AFTER `filedata`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "attachment", 'thumbnail_filesize', "INT(10) NOT NULL default '0'", 'AFTER `filesize`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "locations", 'region', "VARCHAR(150) NOT NULL default ''", 'AFTER `cc`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "users", 'gender', "ENUM('','male','female') NOT NULL", 'AFTER `profileintro`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "users", 'freelancing', "ENUM('', 'individual', 'business') NOT NULL", 'AFTER `gender`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "shippers", 'domestic', "INT(1) NOT NULL default '1'", 'AFTER `title`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "shippers", 'international', "INT(1) NOT NULL default '0'", 'AFTER `domestic`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "attachment", 'thumbnail_date', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `date`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'currencyid', "INT(5) NOT NULL default '0'", 'AFTER `donationinvoiceid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'filter_gateway', "INT(1) NOT NULL default '0'", 'AFTER `filter_escrow`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'filter_offline', "INT(1) NOT NULL default '0'", 'AFTER `filter_gateway`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "shippers", 'shipcode', "VARCHAR(250) NOT NULL", 'AFTER `title`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'buyerpaymethod', "VARCHAR(250) NOT NULL", 'AFTER `paiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'buyershipcost', "FLOAT(10,2) NOT NULL default '0.00'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'buyershipperid', "INT(5) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'buyerpaymethod', "VARCHAR(250) NOT NULL", 'AFTER `isshortlisted`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'buyershipcost', "FLOAT(10,2) NOT NULL default '0.00'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'buyershipperid', "INT(5) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'buyerpaymethod', "VARCHAR(250) NOT NULL", 'AFTER `isshortlisted`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'buyershipcost', "FLOAT(10,2) NOT NULL default '0.00'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'buyershipperid', "INT(5) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'buyerpaymethod', "VARCHAR(250) NOT NULL", 'AFTER `totalbids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'buyershipcost', "FLOAT(10,2) NOT NULL default '0.00'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'buyershipperid', "INT(5) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'countryid', "INT(5) NOT NULL default '0'", 'AFTER `currencyid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'country', "VARCHAR(250) NOT NULL", 'AFTER `countryid`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'state', "VARCHAR(250) NOT NULL", 'AFTER `country`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'city', "VARCHAR(250) NOT NULL", 'AFTER `state`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects", 'zipcode', "VARCHAR(50) NOT NULL", 'AFTER `city`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "shippers", 'carrier', "VARCHAR(250) NOT NULL", 'AFTER `international`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$queries[] = "ALTER TABLE " . DB_PREFIX . "configuration_groups DROP `class`";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalcategorysettings', 'globalcategorysettings', 'Global Category Settings', '', '500')";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_catmapgenres'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_newicondays'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_catmapdepth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_catquestiondepth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_catmapgenredepth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_showcurrentcat'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_catcutoff'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'globalauctionsettings_showbackto'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'categorymapcache'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'categorymapcachetimeout'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'globalcategorysettings' WHERE name = 'multilevelpulldown'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopup', 'Would you like to display a pop-out menu when a users mouse hovers over Categories link from the top nav menu?', '0', 'globalcategorysettings', 'yesno', '', '', 'This setting can ultimately lead to bandwidth saving where root categories can be quickly seen and clicked on without additional page loading from anywhere within the marketplace', 1600, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorylinkheaderpopuptype', 'If the category pop-out menu is active enter the category type to show (service or product)', 'product', 'globalcategorysettings', 'text', '', '', 'Since the category pop-out menu cannot show both category systems in a single pop-out please enter either service or product in the field to show one category type or another when the users mouse hovers over the category link within the top nav header menu.  Default value is product.', 1700, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('categorymainsingleleftnavcount', 'Would you like to display category listing counters on the main marketplace menu in the left nav?', '0', 'globalcategorysettings', 'yesno', '', '', 'You can enable or disable main menu category listing counters within the left nav menu.  Default is disabled.', 1800, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('genderactive', 'Would you like to ask members their gender?', '0', 'registrationdisplay', 'yesno', '', '', 'For marketing purposes you may wish to ask members what their gender is during registration.  If you enable this, users will have the option of selecting their gender.  Additionally, if this option is enabled users can update their gender preference from their personal profile menu (not public profile).', 5, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `project_details` `project_details` ENUM( 'public', 'invite_only', 'realtime', 'unique', 'penny' ) NOT NULL DEFAULT 'public'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productphotosettings', 'Product Auction Picture Settings', '', '220')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productslideshowsettings', 'Product Auction Slideshow Picture Settings', '', '230')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_productdigitalsettings', 'Product Auction Digital File Attachment Settings', '', '240')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_searchresultsettings', 'Search Results Picture and Thumbnail Settings', '', '250')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_bidsettings', 'Service Auction Bid Attachment Settings', '', '260')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_pmbsettings', 'Private Message Board (PMB) Attachment Settings', '', '270')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('attachmentlimit', 'attachmentlimit_workspacesettings', 'Mediashare / Workspace Attachment Settings', '', '280')";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_workspacesettings' WHERE name = 'attachmentlimit_mediasharemaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_workspacesettings' WHERE name = 'attachmentlimit_mediasharemaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_workspacesettings' WHERE name = 'attachmentlimit_mediasharemaxsize'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_pmbsettings' WHERE name = 'attachmentlimit_pmbmaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_pmbsettings' WHERE name = 'attachmentlimit_pmbmaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_pmbsettings' WHERE name = 'attachmentlimit_pmbmaxsize'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_bidsettings' WHERE name = 'attachmentlimit_bidmaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_bidsettings' WHERE name = 'attachmentlimit_bidmaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_bidsettings' WHERE name = 'attachmentlimit_bidmaxsize'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_searchresultsettings' WHERE name = 'attachmentlimit_searchresultsmaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_searchresultsettings' WHERE name = 'attachmentlimit_searchresultsmaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productdigitalsettings' WHERE name = 'attachmentlimit_digitalfileextensions'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productdigitalsettings' WHERE name = 'attachmentlimit_digitalfilemaxsize'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productslideshowsettings' WHERE name = 'attachmentlimit_slideshowextensions'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productslideshowsettings' WHERE name = 'attachmentlimit_slideshowmaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productslideshowsettings' WHERE name = 'attachmentlimit_slideshowmaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productslideshowsettings' WHERE name = 'attachmentlimit_slideshowmaxsize'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productphotosettings' WHERE name = 'attachmentlimit_productphotoextensions'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productphotosettings' WHERE name = 'attachmentlimit_productphotomaxwidth'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productphotosettings' WHERE name = 'attachmentlimit_productphotomaxheight'";
$queries[] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'attachmentlimit_productphotosettings' WHERE name = 'attachmentlimit_productphotomaxsize'";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxwidth', 'Maximum search result gallery view thumbnail [WIDTH in px]', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 30, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultsgallerymaxheight', 'Maximum search result gallery view thumbnail [HEIGHT in px]', '150', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 40, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxwidth', 'Maximum search result snapshot view thumbnail [WIDTH in px]', '110', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 50, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_searchresultssnapshotmaxheight', 'Maximum search result snapshot view thumbnail [HEIGHT in px]', '110', 'attachmentlimit_searchresultsettings', 'int', '', '', '', 60, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `paymethod` `paymethod` MEDIUMTEXT NOT NULL";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `paymethodoptions` `paymethodoptions` MEDIUMTEXT NOT NULL";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `paymethodoptionsemail` `paymethodoptionsemail` MEDIUMTEXT NOT NULL";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharacterstitle', 'Maximum number of characters of the title on the main page', '0', 'globalfilterresults', 'int', '', '', 'Enter 0 for unlimited characters or any other number to cut off the title', 401, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_clientcpnag', 'Would you like to be reminded when you are viewing the site as Admin in the front end?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting might be a good idea when enabled to remind you that you are viewing the front end (client cp) as an administrative user.  The reminder will let you know that some elements on the front end will be visible to you as an admin user such as sealed bidders being non-sealed when being viewed as an admin vs. regular level access.', 402, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_countries`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_trackingnumber`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_handling`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_country`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `sellermarkedasshipped`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `sellermarkedasshippeddate`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_costs`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `ship_shipperid`";
	
$queries[] = "
CREATE TABLE " . DB_PREFIX . "distance_fr (
`ZIPCode` VARCHAR(255) default NULL,
`City` MEDIUMTEXT,
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
`State` MEDIUMTEXT,
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE .
"=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "distance_it (
`ZIPCode` VARCHAR(255) default NULL,
`City` MEDIUMTEXT,
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
`State` MEDIUMTEXT,
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "distance_jp (
`ZIPCode` VARCHAR(255) default NULL,
`City` MEDIUMTEXT,
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
`State` MEDIUMTEXT,
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

// #### we're going to group country pulldowns by regions instead.  Much more flexible.
$regions = array();
$regions['100'] = 'Africa';
$regions['101'] = 'Europe';
$regions['102'] = 'Oceania';
$regions['103'] = 'North America';
$regions['104'] = 'Africa';
$regions['105'] = 'Africa';
$regions['106'] = 'North America';
$regions['107'] = 'Oceania';
$regions['108'] = 'Europe';
$regions['109'] = 'North America';
$regions['110'] = 'Africa';
$regions['111'] = 'Africa';
$regions['112'] = 'Africa';
$regions['113'] = 'Asia';
$regions['114'] = 'Europe';
$regions['116'] = 'Oceania';
$regions['117'] = 'Oceania';
$regions['118'] = 'North America';
$regions['119'] = 'Africa';
$regions['120'] = 'Africa';
$regions['121'] = 'Europe';
$regions['122'] = 'Asia';
$regions['123'] = 'Asia';
$regions['124'] = 'Oceania';
$regions['125'] = 'North America';
$regions['126'] = 'Oceania';
$regions['127'] = 'South America';
$regions['128'] = 'South America';
$regions['129'] = 'Asia';
$regions['130'] = 'Europe';
$regions['131'] = 'Europe';
$regions['132'] = 'Asia';
$regions['133'] = 'Africa';
$regions['134'] = 'Europe';
$regions['135'] = 'Europe';
$regions['136'] = 'Africa';
$regions['148'] = 'Asia';
$regions['150'] = 'Africa';
$regions['151'] = 'Africa';
$regions['152'] = 'Africa';
$regions['153'] = 'Asia';
$regions['154'] = 'Africa';
$regions['155'] = 'Africa';
$regions['156'] = 'Europe';
$regions['157'] = 'Asia';
$regions['158'] = 'Africa';
$regions['159'] = 'South America';
$regions['160'] = 'Africa';
$regions['161'] = 'Europe';
$regions['162'] = 'Europe';
$regions['163'] = 'Asia';
$regions['164'] = 'Asia';
$regions['165'] = 'Africa';
$regions['166'] = 'Asia';
$regions['167'] = 'Africa';
$regions['168'] = 'North America';
$regions['169'] = 'Africa';
$regions['170'] = 'Asia';
$regions['172'] = 'Africa';
$regions['173'] = 'Europe';
$regions['174'] = 'Asia';
$regions['176'] = 'South America';
$regions['177'] = 'Europe';
$regions['178'] = 'South America';
$regions['180'] = 'Asia';
$regions['182'] = 'Africa';
$regions['183'] = 'Africa';
$regions['184'] = 'Europe';
$regions['185'] = 'Asia';
$regions['186'] = 'Asia';
$regions['188'] = 'Europe';
$regions['193'] = 'Africa';
$regions['194'] = 'Europe';
$regions['195'] = 'North America';
$regions['196'] = 'Asia';
$regions['197'] = 'Europe';
$regions['199'] = 'Asia';
$regions['200'] = 'Asia';
$regions['203'] = 'Europe';
$regions['207'] = 'Europe';
$regions['208'] = 'Europe';
$regions['209'] = 'Europe';
$regions['210'] = 'Oceania';
$regions['211'] = 'Asia';
$regions['212'] = 'Asia';
$regions['213'] = 'Europe';
$regions['215'] = 'Asia';
$regions['216'] = 'Europe';
$regions['217'] = 'North America';
$regions['219'] = 'Oceania';
$regions['220'] = 'Asia';
$regions['221'] = 'Africa';
$regions['222'] = 'Europe';
$regions['223'] = 'Asia';
$regions['225'] = 'Oceania';
$regions['226'] = 'Oceania';
$regions['228'] = 'Europe';
$regions['229'] = 'Africa';
$regions['231'] = 'Oceania';
$regions['232'] = 'Asia';
$regions['233'] = 'Oceania';
$regions['234'] = 'Asia';
$regions['242'] = 'Oceania';
$regions['243'] = 'Oceania';
$regions['249'] = 'Oceania';
$regions['253'] = 'Oceania';
$regions['258'] = 'Oceania';
$regions['262'] = 'Europe';
$regions['301'] = 'Africa';
$regions['302'] = 'Africa';
$regions['303'] = 'North America';
$regions['304'] = 'North America';
$regions['305'] = 'South America';
$regions['306'] = 'North America';
$regions['307'] = 'Oceania';
$regions['308'] = 'Europe';
$regions['309'] = 'North America';
$regions['310'] = 'Asia';
$regions['311'] = 'Asia';
$regions['312'] = 'North America';
$regions['314'] = 'Europe';
$regions['315'] = 'Europe';
$regions['316'] = 'North America';
$regions['317'] = 'Africa';
$regions['318'] = 'North America';
$regions['319'] = 'Asia';
$regions['320'] = 'South America';
$regions['322'] = 'Africa';
$regions['323'] = 'South America';
$regions['324'] = 'North America';
$regions['325'] = 'Asia';
$regions['326'] = 'Europe';
$regions['327'] = 'Africa';
$regions['328'] = 'Africa';
$regions['329'] = 'Africa';
$regions['330'] = 'North America';
$regions['331'] = 'Africa';
$regions['332'] = 'North America';
$regions['333'] = 'Africa';
$regions['334'] = 'Africa';
$regions['336'] = 'South America';
$regions['337'] = 'Asia';
$regions['338'] = 'South America';
$regions['339'] = 'Africa';
$regions['340'] = 'North America';
$regions['342'] = 'Asia';
$regions['343'] = 'Europe';
$regions['344'] = 'Europe';
$regions['345'] = 'Africa';
$regions['346'] = 'North America';
$regions['347'] = 'North America';
$regions['348'] = 'South America';
$regions['349'] = 'Africa';
$regions['350'] = 'North America';
$regions['352'] = 'Africa';
$regions['353'] = 'Europe';
$regions['354'] = 'Africa';
$regions['355'] = 'Oceania';
$regions['356'] = 'Europe';
$regions['357'] = 'Europe';
$regions['358'] = 'South America';
$regions['360'] = 'Africa';
$regions['361'] = 'Europe';
$regions['362'] = 'Africa';
$regions['363'] = 'Europe';
$regions['364'] = 'North America';
$regions['365'] = 'North America';
$regions['367'] = 'North America';
$regions['368'] = 'Africa';
$regions['369'] = 'Africa';
$regions['370'] = 'South America';
$regions['371'] = 'North America';
$regions['372'] = 'Asia';
$regions['373'] = 'Europe';
$regions['374'] = 'Europe';
$regions['375'] = 'Asia';
$regions['376'] = 'Asia';
$regions['377'] = 'Asia';
$regions['378'] = 'Europe';
$regions['380'] = 'Asia';
$regions['381'] = 'Europe';
$regions['383'] = 'North America';
$regions['384'] = 'Asia';
$regions['385'] = 'Asia';
$regions['386'] = 'Africa';
$regions['388'] = 'Asia';
$regions['389'] = 'Europe';
$regions['390'] = 'Asia';
$regions['391'] = 'Africa';
$regions['392'] = 'Europe';
$regions['393'] = 'Europe';
$regions['394'] = 'Europe';
$regions['395'] = 'Asia';
$regions['396'] = 'Africa';
$regions['397'] = 'Africa';
$regions['398'] = 'Asia';
$regions['399'] = 'Asia';
$regions['500'] = 'North America';
$regions['501'] = 'Asia';
$regions['502'] = 'Oceania';
$regions['503'] = 'Antarctica';
$regions['504'] = 'Oceania';
$regions['506'] = 'Asia';
$regions['508'] = 'Asia';
$regions['509'] = 'North America';
$regions['510'] = 'Asia';
$regions['511'] = 'Africa';
$regions['512'] = 'North America';
$regions['513'] = 'Africa';
$regions['514'] = 'Africa';
$regions['515'] = 'South America';
$regions['516'] = 'Europe';
$regions['517'] = 'Africa';
$regions['518'] = 'Antarctica';
$regions['519'] = 'Africa';
$regions['521'] = 'Oceania';
$regions['522'] = 'Europe';
$regions['523'] = 'Oceania';
$regions['524'] = 'Europe';
$regions['525'] = 'Oceania';
$regions['526'] = 'Europe';
$regions['527'] = 'Oceania';
$regions['528'] = 'Europe';
$regions['529'] = 'Oceania';
$regions['530'] = 'Europe';
$regions['531'] = 'Asia';
$regions['532'] = 'Asia';
$regions['533'] = 'Africa';
$regions['535'] = 'Africa';
$regions['536'] = 'Oceania';
$regions['537'] = 'North America';
$regions['538'] = 'Oceania';
$regions['539'] = 'Oceania';
$regions['540'] = 'North America';
$regions['541'] = 'Africa';
$regions['542'] = 'North America';
$regions['543'] = 'North America';
$regions['544'] = 'North America';
$regions['545'] = 'North America';
$regions['546'] = 'Oceania';
$regions['547'] = 'Europe';
$regions['548'] = 'Antarctica';
$regions['549'] = 'Asia';
$regions['550'] = 'Europe';
$regions['551'] = 'Africa';
$regions['552'] = 'Oceania';
$regions['553'] = 'North America';
$regions['554'] = 'Europe';
$regions['555'] = 'Asia';
$regions['556'] = 'Africa';

foreach ($regions AS $locationid => $region)
{
	$queries[] = "UPDATE " . DB_PREFIX . "locations SET region = '" . $ilance->db->escape_string($region) . "' WHERE locationid = '" . intval($locationid) . "'";	
}
unset($regions);

$isocodes = array();
$isocodes['100'] = 'ML';
$isocodes['101'] = 'MT';
$isocodes['102'] = 'MH';
$isocodes['103'] = 'MQ';
$isocodes['104'] = 'MR';
$isocodes['105'] = 'MU';
$isocodes['106'] = 'MX';
$isocodes['107'] = 'FM';
$isocodes['108'] = 'MC';
$isocodes['109'] = 'MS';
$isocodes['110'] = 'MA';
$isocodes['111'] = 'MZ';
$isocodes['112'] = 'WA';
$isocodes['113'] = 'NP';
$isocodes['114'] = 'NL';
$isocodes['116'] = 'NC';
$isocodes['117'] = 'NZ';
$isocodes['118'] = 'NI';
$isocodes['119'] = 'NE';
$isocodes['120'] = 'NG';
$isocodes['121'] = 'NO';
$isocodes['122'] = 'OM';
$isocodes['123'] = 'PK';
$isocodes['124'] = 'PW';
$isocodes['125'] = 'PA';
$isocodes['126'] = 'PG';
$isocodes['127'] = 'PY';
$isocodes['128'] = 'PE';
$isocodes['129'] = 'PH';
$isocodes['130'] = 'PL';
$isocodes['131'] = 'PT';
$isocodes['132'] = 'QA';
$isocodes['133'] = 'RE';
$isocodes['134'] = 'RO';
$isocodes['135'] = 'RU';
$isocodes['136'] = 'RW';
$isocodes['148'] = 'SA';
$isocodes['150'] = 'SN';
$isocodes['151'] = 'SC';
$isocodes['152'] = 'SL';
$isocodes['153'] = 'SG';
$isocodes['154'] = 'SO';
$isocodes['155'] = 'ZA';
$isocodes['156'] = 'ES';
$isocodes['157'] = 'LK';
$isocodes['158'] = 'SD';
$isocodes['159'] = 'SR';
$isocodes['160'] = 'SZ';
$isocodes['161'] = 'SE';
$isocodes['162'] = 'CH';
$isocodes['163'] = 'SY';
$isocodes['164'] = 'TW';
$isocodes['165'] = 'TZ';
$isocodes['166'] = 'TH';
$isocodes['167'] = 'TG';
$isocodes['168'] = 'TT';
$isocodes['169'] = 'TN';
$isocodes['170'] = 'TR';
$isocodes['172'] = 'UG';
$isocodes['173'] = 'UA';
$isocodes['174'] = 'AE';
$isocodes['176'] = 'UY';
$isocodes['177'] = 'VT';
$isocodes['178'] = 'VE';
$isocodes['180'] = 'YE';
$isocodes['182'] = 'ZM';
$isocodes['183'] = 'ZW';
$isocodes['184'] = 'AL';
$isocodes['185'] = 'AM';
$isocodes['186'] = 'AZ';
$isocodes['188'] = 'BK';
$isocodes['193'] = 'CI';
$isocodes['194'] = 'HR';
$isocodes['195'] = 'HT';
$isocodes['196'] = 'IQ';
$isocodes['197'] = 'IM';
$isocodes['199'] = 'KZ';
$isocodes['200'] = 'KG';
$isocodes['203'] = 'MD';
$isocodes['207'] = 'RS';
$isocodes['208'] = 'SK';
$isocodes['209'] = 'SI';
$isocodes['210'] = 'SB';
$isocodes['211'] = 'TJ';
$isocodes['212'] = 'TM';
$isocodes['213'] = 'MK';
$isocodes['215'] = 'GE';
$isocodes['216'] = 'GI';
$isocodes['217'] = 'GL';
$isocodes['219'] = 'KI';
$isocodes['220'] = 'LA';
$isocodes['221'] = 'LR';
$isocodes['222'] = 'MK';
$isocodes['223'] = 'MN';
$isocodes['225'] = 'NR';
$isocodes['226'] = 'NU';
$isocodes['228'] = 'SM';
$isocodes['229'] = 'ST';
$isocodes['231'] = 'TV';
$isocodes['232'] = 'UZ';
$isocodes['233'] = 'VU';
$isocodes['234'] = 'VN';
$isocodes['242'] = 'CK';
$isocodes['243'] = 'PF';
$isocodes['249'] = 'TO';
$isocodes['253'] = 'WF';
$isocodes['258'] = 'NF';
$isocodes['262'] = 'GB';
$isocodes['301'] = 'DZ';
$isocodes['302'] = 'AO';
$isocodes['303'] = 'AI';
$isocodes['304'] = 'AG';
$isocodes['305'] = 'AR';
$isocodes['306'] = 'AW';
$isocodes['307'] = 'AU';
$isocodes['308'] = 'AT';
$isocodes['309'] = 'BS';
$isocodes['310'] = 'BH';
$isocodes['311'] = 'BD';
$isocodes['312'] = 'BB';
$isocodes['314'] = 'BY';
$isocodes['315'] = 'BE';
$isocodes['316'] = 'BZ';
$isocodes['317'] = 'BJ';
$isocodes['318'] = 'BM';
$isocodes['319'] = 'BT';
$isocodes['320'] = 'BO';
$isocodes['322'] = 'BW';
$isocodes['323'] = 'BR';
$isocodes['324'] = 'VI';
$isocodes['325'] = 'BN';
$isocodes['326'] = 'BG';
$isocodes['327'] = 'BF';
$isocodes['328'] = 'BI';
$isocodes['329'] = 'CM';
$isocodes['330'] = 'CA';
$isocodes['331'] = 'CV';
$isocodes['332'] = 'KY';
$isocodes['333'] = 'CF';
$isocodes['334'] = 'TD';
$isocodes['336'] = 'CL';
$isocodes['337'] = 'CH';
$isocodes['338'] = 'CO';
$isocodes['339'] = 'CG';
$isocodes['340'] = 'CR';
$isocodes['342'] = 'CY';
$isocodes['343'] = 'CZ';
$isocodes['344'] = 'DK';
$isocodes['345'] = 'DJ';
$isocodes['346'] = 'DM';
$isocodes['347'] = 'DO';
$isocodes['348'] = 'EC';
$isocodes['349'] = 'EG';
$isocodes['350'] = 'SV';
$isocodes['352'] = 'CQ';
$isocodes['353'] = 'EE';
$isocodes['354'] = 'ET';
$isocodes['355'] = 'FJ';
$isocodes['356'] = 'FI';
$isocodes['357'] = 'FR';
$isocodes['358'] = 'GF';
$isocodes['360'] = 'GM';
$isocodes['361'] = 'DE';
$isocodes['362'] = 'GH';
$isocodes['363'] = 'GR';
$isocodes['364'] = 'GD';
$isocodes['365'] = 'GP';
$isocodes['367'] = 'GT';
$isocodes['368'] = 'GN';
$isocodes['369'] = 'GW';
$isocodes['370'] = 'GY';
$isocodes['371'] = 'HN';
$isocodes['372'] = 'HK';
$isocodes['373'] = 'HU';
$isocodes['374'] = 'IS';
$isocodes['375'] = 'IN';
$isocodes['376'] = 'ID';
$isocodes['377'] = 'IR';
$isocodes['378'] = 'EI';
$isocodes['380'] = 'IL';
$isocodes['381'] = 'IT';
$isocodes['383'] = 'JM';
$isocodes['384'] = 'JP';
$isocodes['385'] = 'JO';
$isocodes['386'] = 'KE';
$isocodes['388'] = 'KW';
$isocodes['389'] = 'LV';
$isocodes['390'] = 'LB';
$isocodes['391'] = 'LS';
$isocodes['392'] = 'LI';
$isocodes['393'] = 'LT';
$isocodes['394'] = 'LU';
$isocodes['395'] = 'MO';
$isocodes['396'] = 'MG';
$isocodes['397'] = 'MW';
$isocodes['398'] = 'MY';
$isocodes['399'] = 'MV';
$isocodes['500'] = 'US';
$isocodes['501'] = 'AF';
$isocodes['502'] = 'AS';
$isocodes['503'] = 'AQ';
$isocodes['504'] = 'AS';
$isocodes['506'] = 'BU';
$isocodes['508'] = 'CX';
$isocodes['509'] = 'CL';
$isocodes['510'] = 'CC';
$isocodes['511'] = 'KM';
$isocodes['512'] = 'CU';
$isocodes['513'] = 'ER';
$isocodes['514'] = 'EI';
$isocodes['515'] = 'FK';
$isocodes['516'] = 'FO';
$isocodes['517'] = 'GA';
$isocodes['518'] = 'GS';
$isocodes['519'] = 'GI';
$isocodes['521'] = 'GU';
$isocodes['522'] = 'GG';
$isocodes['523'] = 'HM';
$isocodes['524'] = 'VA';
$isocodes['525'] = 'HI';
$isocodes['526'] = 'IE';
$isocodes['527'] = 'JI';
$isocodes['528'] = 'JE';
$isocodes['529'] = 'JA';
$isocodes['530'] = 'JD';
$isocodes['531'] = 'KP';
$isocodes['532'] = 'KR';
$isocodes['533'] = 'LY';
$isocodes['535'] = 'YT';
$isocodes['536'] = 'MI';
$isocodes['537'] = 'AN';
$isocodes['538'] = 'MP';
$isocodes['539'] = 'PN';
$isocodes['540'] = 'PR';
$isocodes['541'] = 'SH';
$isocodes['542'] = 'KN';
$isocodes['543'] = 'LC';
$isocodes['544'] = 'SP';
$isocodes['545'] = 'VC';
$isocodes['546'] = 'WS';
$isocodes['547'] = 'SC';
$isocodes['548'] = 'GS';
$isocodes['549'] = 'SI';
$isocodes['550'] = 'SJ';
$isocodes['551'] = 'TO';
$isocodes['552'] = 'TK';
$isocodes['553'] = 'VI';
$isocodes['554'] = 'WA';
$isocodes['555'] = 'WB';
$isocodes['556'] = 'EH';

foreach ($isocodes AS $locationid => $isocode)
{
	$queries[] = "UPDATE " . DB_PREFIX . "locations SET cc = '" . $ilance->db->escape_string($isocode) . "' WHERE locationid = '" . intval($locationid) . "'";	
}
unset($isocodes);

$queries[] = "DROP TABLE IF EXISTS " . DB_PREFIX . "shippers";
$queries[] = "
CREATE TABLE " . DB_PREFIX . "shippers (
`shipperid` INT(5) NOT NULL AUTO_INCREMENT,
`title` MEDIUMTEXT,
`shipcode` VARCHAR(250) NOT NULL,
`domestic` INT(1) NOT NULL default '1',
`international` INT(1) NOT NULL default '0',
`carrier` VARCHAR(250) NOT NULL,
PRIMARY KEY  (`shipperid`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
INSERT INTO " . DB_PREFIX . "shippers
(`shipperid`, `title`, `shipcode`, `domestic`, `international`, `carrier`)
VALUES
(NULL, 'FedEx Priority Overnight', '01', 1, 0, 'fedex'),
(NULL, 'FedEx First Class', '06', 1, 0, 'fedex'),
(NULL, 'FedEx 2-Day Air', '03', 1, 0, 'fedex'),
(NULL, 'FedEx Standard Overnight', '05', 1, 0, 'fedex'),
(NULL, 'FedEx Express Saver', '20', 1, 0, 'fedex'),
(NULL, 'FedEx Home Delivery', '90', 1, 0, 'fedex'),
(NULL, 'FedEx Ground (1 to 6 business days)', '92', 1, 0, 'fedex'),
(NULL, 'FedEx International Priority Overnight', '01', 0, 1, 'fedex'),
(NULL, 'FedEx International First Class', '06', 0, 1, 'fedex'),
(NULL, 'FedEx International Economy', '03', 0, 1, 'fedex'),
(NULL, 'FedEx International Home Delivery', '90', 0, 1, 'fedex'),
(NULL, 'FedEx International Ground', '92', 0, 1, 'fedex'),
(NULL, 'UPS Ground (1 to 6 business days)', '03', 1, 0, 'ups'),
(NULL, 'UPS 3-Day Select', '12', 1, 0, 'ups'),
(NULL, 'UPS 2nd Day Air', '02', 1, 0, 'ups'),
(NULL, 'UPS Next Day Air Saver', '13', 1, 0, 'ups'),
(NULL, 'UPS Next Day Air Early AM', '14', 1, 0, 'ups'),
(NULL, 'UPS Next Day Air', '01', 1, 0, 'ups'),
(NULL, 'UPS Worldwide Express', '07', 1, 1, 'ups'),
(NULL, 'UPS Worldwide Expedited', '08', 1, 1, 'ups'),
(NULL, 'UPS Standard', '11', 1, 0, 'ups'),
(NULL, 'UPS Next Day Air Saver', '13', 1, 0, 'ups'),
(NULL, 'UPS Worldwide Express Plus', '54', 1, 1, 'ups'),
(NULL, 'UPS Express Saver', '65', 1, 0, 'ups'),
(NULL, 'USPS Express Mail', 'Express', 1, 0, 'usps'),
(NULL, 'USPS First Class Mail', 'First Class', 1, 0, 'usps'),
(NULL, 'USPS Priority Mail', 'Priority', 1, 0, 'usps'),
(NULL, 'USPS Parcel Mail', 'Parcel', 1, 0, 'usps'),
(NULL, 'USPS Library Mail', 'Library', 1, 0, 'usps'),
(NULL, 'USPS BPM Mail', 'BPM', 1, 0, 'usps'),
(NULL, 'USPS Media Mail', 'Media', 1, 0, 'usps')";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "projects_shipping (
`project_id` INT(5) NOT NULL,
`ship_method` ENUM('flatrate', 'calculated', 'localpickup') NOT NULL default 'localpickup',
`ship_handlingtime` ENUM('1','2','3','4','5','10','15','30') NOT NULL default '1',
`ship_handlingfee` FLOAT(10,2) NOT NULL default '0.00',
`ship_packagetype` VARCHAR(250) NOT NULL default '',
`ship_length` INT(5) NOT NULL default '0',
`ship_width` INT(5) NOT NULL default '0',
`ship_height` INT(5) NOT NULL default '0',
`ship_weightlbs` INT(5) NOT NULL default '1',
`ship_weightoz` INT(5) NOT NULL default '0',
INDEX ( `project_id` ) 
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'sellermarkedasshipped', "INT(1) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'sellermarkedasshipped', "INT(1) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'sellermarkedasshipped', "INT(1) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'sellermarkedasshipped', "INT(1) NOT NULL default '0'", 'AFTER `buyerpaymethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'sellermarkedasshippeddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'sellermarkedasshippeddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'sellermarkedasshippeddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'sellermarkedasshippeddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'sellermarkedasshipped', "INT(1) NOT NULL default '0'", 'AFTER `status`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_escrow", 'sellermarkedasshippeddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `sellermarkedasshipped`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'winnermarkedaspaidmethod', "MEDIUMTEXT NOT NULL", 'AFTER `isshortlisted`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'winnermarkedaspaiddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `winnermarkedaspaidmethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_bids", 'winnermarkedaspaid', "INT(1) NOT NULL default '0'", 'AFTER `winnermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'winnermarkedaspaidmethod', "MEDIUMTEXT NOT NULL", 'AFTER `isshortlisted`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'winnermarkedaspaiddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `winnermarkedaspaidmethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "project_realtimebids", 'winnermarkedaspaid', "INT(1) NOT NULL default '0'", 'AFTER `winnermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'winnermarkedaspaidmethod', "MEDIUMTEXT NOT NULL", 'AFTER `totalbids`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'winnermarkedaspaiddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `winnermarkedaspaidmethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "projects_uniquebids", 'winnermarkedaspaid', "INT(1) NOT NULL default '0'", 'AFTER `winnermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'winnermarkedaspaidmethod', "MEDIUMTEXT NOT NULL", 'AFTER `paiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'winnermarkedaspaiddate', "DATETIME NOT NULL default '0000-00-00 00:00:00'", 'AFTER `winnermarkedaspaidmethod`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$temp = $ilance->db->add_field_if_not_exist(DB_PREFIX . "buynow_orders", 'winnermarkedaspaid', "INT(1) NOT NULL default '0'", 'AFTER `winnermarkedaspaiddate`', $doquery = false);
if ($temp != '')
{
	$queries[] = $temp;
	unset($temp);
}

$queries[] = "
CREATE TABLE " . DB_PREFIX . "projects_shipping_destinations (
`destinationid` INT(100) NOT NULL AUTO_INCREMENT,
`project_id` INT(5) NOT NULL,
`ship_options_1` VARCHAR(250) NOT NULL default '',
`ship_service_1` INT(5) NOT NULL default '0',
`ship_fee_1` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_1` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_1` INT(1) NOT NULL default '0',
`ship_options_2` VARCHAR(250) NOT NULL default '',
`ship_service_2` INT(5) NOT NULL default '0',
`ship_fee_2` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_2` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_2` INT(1) NOT NULL default '0',
`ship_options_3` VARCHAR(250) NOT NULL default '',
`ship_service_3` INT(5) NOT NULL default '0',
`ship_fee_3` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_3` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_3` INT(1) NOT NULL default '0',
`ship_options_4` VARCHAR(250) NOT NULL default '',
`ship_service_4` INT(5) NOT NULL default '0',
`ship_fee_4` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_4` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_4` INT(1) NOT NULL default '0',
`ship_options_5` VARCHAR(250) NOT NULL default '',
`ship_service_5` INT(5) NOT NULL default '0',
`ship_fee_5` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_5` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_5` INT(1) NOT NULL default '0',
`ship_options_6` VARCHAR(250) NOT NULL default '',
`ship_service_6` INT(5) NOT NULL default '0',
`ship_fee_6` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_6` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_6` INT(1) NOT NULL default '0',
`ship_options_7` VARCHAR(250) NOT NULL default '',
`ship_service_7` INT(5) NOT NULL default '0',
`ship_fee_7` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_7` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_7` INT(1) NOT NULL default '0',
`ship_options_8` VARCHAR(250) NOT NULL default '',
`ship_service_8` INT(5) NOT NULL default '0',
`ship_fee_8` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_8` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_8` INT(1) NOT NULL default '0',
`ship_options_9` VARCHAR(250) NOT NULL default '',
`ship_service_9` INT(5) NOT NULL default '0',
`ship_fee_9` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_9` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_9` INT(1) NOT NULL default '0',
`ship_options_10` VARCHAR(250) NOT NULL default '',
`ship_service_10` INT(5) NOT NULL default '0',
`ship_fee_10` FLOAT(10,2) NOT NULL default '0.00',
`ship_additionalfee_10` FLOAT(10,2) NOT NULL default '0.00',
`freeshipping_10` INT(1) NOT NULL default '0',
INDEX ( `destinationid` ),
INDEX ( `project_id` )
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "projects_shipping_regions (
`project_id` INT(5) NOT NULL,
`country` VARCHAR(250) NOT NULL default '',
`countryid` INT(5) NOT NULL default '0',
`region` VARCHAR(250) NOT NULL default '',
`row` VARCHAR(250) NOT NULL default '',
INDEX ( `project_id` ) 
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "shipping_rates_cache (
`carrier` VARCHAR(250) NOT NULL default '',
`shipcode` VARCHAR(250) NOT NULL default '',
`from_country` VARCHAR(250) NOT NULL default '',
`from_zipcode` VARCHAR(250) NOT NULL default '',
`to_country` VARCHAR(250) NOT NULL default '',
`to_zipcode` VARCHAR(250) NOT NULL default '',
`weight` DOUBLE NOT NULL default '1.0',
`datetime` DATETIME NOT NULL default '0000-00-00 00:00:00',
`gatewayresult` MEDIUMTEXT,
`traffic` INT(5) NOT NULL default '1',
INDEX ( `carrier` ),
INDEX ( `from_country` ),
INDEX ( `from_zipcode` ),
INDEX ( `to_country` ),
INDEX ( `to_zipcode` )
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingsettings', 'shippingsettings', 'Shipping Settings', 'Manage shipping services, settings and other aspects of shipping from here', '290')";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('maxshipservices', 'Maximum number of ship services allowed for item listings?', '5', 'shippingsettings', 'int', '', '', 'Default is 5 shipping services.', 1900, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('shippingapi', 'Would you like to enable the Research Shipping Rates API?', '0', 'shippingsettings', 'yesno', '', '', 'When enabled, a link will be presented beside the shipping services pulldown when sellers are posting an item.  This feature allows sellers to research shipping rates based on the integrated shipping calculator.  You will need to define your shipping api user and password details in your config.php file.  Currently supported carriers include FedEx, UPS and USPS.', 1910, 1)";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `winnermarkedaspaid`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `winnermarkedaspaiddate`";
$queries[] = "ALTER TABLE " . DB_PREFIX . "projects DROP `winnermarkedaspaidmethod`";
$queries[] = "INSERT INTO " . DB_PREFIX . "cron (nextrun, weekday, day, hour, minute, filename, loglevel, varname, product) VALUES (1053271600, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.bulk_photos.php', 1, 'bulk_photos', 'ilance')";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "bulk_sessions (
`id` INT(10) NOT NULL AUTO_INCREMENT,
`user_id` INT(4) NOT NULL default '0',
`dateupload` datetime,
`items` INT(5) NOT NULL default '0',
`itemsuploaded` INT(5) default '0',
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "bulk_tmp (
`id` INT(10) NOT NULL AUTO_INCREMENT,
`project_title` MEDIUMTEXT NOT NULL,
`description` MEDIUMTEXT,
`startprice` FLOAT(10,2) NOT NULL default '0.00',
`buynow_price` FLOAT(10,2) NOT NULL default '0.00',
`reserve_price` FLOAT(10,2) NOT NULL default '0.00',
`buynow_qty` INT(10) NOT NULL default '0',
`project_details` ENUM('public','invite_only','realtime','unique','penny') NOT NULL default 'public',
`filtered_auctiontype` ENUM('regular','fixed') NOT NULL default 'regular',
`cid` INT(10) NOT NULL default '0',
`sample` MEDIUMTEXT,
`currency` VARCHAR(250) NOT NULL default '',
`correct` INT(2) NOT NULL default '0',
`user_id` INT(4) NOT NULL default '0',
`rfpid` INT(15) NOT NULL default '0',
`sample_uploaded` INT(2) NOT NULL default '0',
`bulk_id` INT(10) NOT NULL,
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableauctiontab', 'Would you like to enable the auction format tab when sellers post an item?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, sellers can click on the auction tab from their selling format section allowing buyers to place bids on their listings.  When disabled, the auction format tab will not be visible when selling an item.', 130, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablefixedpricetab', 'Would you like to enable the fixed priced tab when sellers post an item?', '1', 'globalauctionsettings', 'yesno', '', '', 'When enabled, sellers can click on the fixed price tab from their selling format section allowing buyers to purchase their items directly from the item listing page. When disabled, the fixed price tab will not be visible when selling an item.', 140, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_currencyselector', 'Enable the currency selector when listing auctions?', '0', 'globalserverlocalecurrency', 'yesno', '', 'currencyrates', 'When enabled, this setting will allow users posting listings to define the currency the listing accepts.  Additionally, if bulk upload is enabled, the API chart will allow a special field to include a currency field (example: EUR, CAD, USD, etc)', 2, 1)";
$queries[] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_whitespacestripper', 'Would you like to enable template whitespace stripping? (compresses HTML output)', '0', 'globalfilterresults', 'yesno', '', '', 'This setting will remove all whitespace from the outputted HTML template after the template parser has compiled the template ready for viewing.  For example, uncompressed search results average 150kb and when whitespace stripping is enabled may reduce this to 100kb or less.', 403, 1)";

$queries[] = "
CREATE TABLE " . DB_PREFIX . "visits (
`vid` INT(100) NOT NULL AUTO_INCREMENT,
`sesskey` VARCHAR(200) default '',
`userid` INT(5) default '0',
`firstdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
`lastdate` DATETIME NOT NULL default '0000-00-00 00:00:00',
`browser` VARCHAR(200) default '',
`ipaddress` VARCHAR(50) default '',
`referrer` MEDIUMTEXT,
KEY `vid` (`vid`),
INDEX ( `ipaddress` ),
INDEX ( `userid` ),
INDEX ( `sesskey` )
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";

$queries[] = "ALTER TABLE `" . DB_PREFIX . "product_questions` ADD `recursive` INT(1) NOT NULL DEFAULT '0' AFTER `canremove`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "project_questions` ADD `recursive` INT(1) NOT NULL DEFAULT '0' AFTER `canremove`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "users` ADD `lastemailservicecats` DATE NOT NULL default '0000-00-00 00:00:00' AFTER `notifyproductscats`, ADD `lastemailproductcats` DATE NOT NULL default '0000-00-00 00:00:00' AFTER `lastemailservicecats`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "audit` ADD `do` VARCHAR(250) NOT NULL AFTER `subcmd`, ADD `action` VARCHAR(250) NOT NULL AFTER `do`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "users` ADD `servicesold` INT(5) NOT NULL DEFAULT '0' AFTER `productawards`, ADD `productsold` INT(5) NOT NULL DEFAULT '0' AFTER `servicesold`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "sessions` ADD `sesskeyapi` VARCHAR(250) NOT NULL default '' AFTER `token`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "sessions` ADD `siteid` VARCHAR(20) NOT NULL DEFAULT '001' AFTER `sesskeyapi`";
$queries[] = "ALTER TABLE `" . DB_PREFIX . "project_invitations` ADD `date_of_remind` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `date_of_bid`";
$queries[] = "DROP TABLE IF EXISTS " . DB_PREFIX . "sessions_bulkupload";
$queries[] = "DROP TABLE IF EXISTS " . DB_PREFIX . "cache";

if ($res['value'] == '3.1.9')
{
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '14'
        WHERE name = 'current_sql_version'";
        
        $queries[] =
        "UPDATE " . DB_PREFIX . "configuration
        SET value = '3.2.0'
        WHERE name = 'current_version'";
}
	
if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.1.9 to 3.2.0</h1><p>Updating database...</p>';
	
        if ($res['value'] == '3.1.9')
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
		
                // import (or detect upgrade) of new phrases for 3.2.0
                echo import_language_phrases(10000, 0);
                
                // import (or detect upgrade) of new css templates for 3.2.0
                echo import_templates();
                
                // import (or detect upgrade) of new email templates for 3.2.0
                echo import_email_templates();
		
		// rebuild the recursive category logic for 3.2.0
		print_progress_begin('<b>Rebuilding hierarchical logic within the category table</b>, please wait.', '.', 'progressspan99');
		rebuild_category_tree(0, 1);
		print_progress_end();
		
		// optimize new category table to support spatial indexing for 3.2.0
		echo rebuild_spatial_category_indexes();
                
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
        echo '<h1>Upgrade from 3.1.9 to 3.2.0</h1><p>The following SQL queries will be executed:</p>';    
        echo '<hr size="1" width="100%" style="margin:0px; padding:0px" />';

        if (isset($queries) AND !empty($queries) AND is_array($queries))
	{
		foreach ($queries AS $upgradequery)
		{
			if (isset($upgradequery) AND !empty($upgradequery))
			{
				echo '<div><textarea style="font-family: verdana" cols="80" rows="5">' . $upgradequery . '</textarea></div>';
				echo '<hr size="1" width="100%" />';
			}
		}
		
		echo '<div class="redhlite">
					<strong>
					Notice: </strong><span>Before upgrade make sure you have backup of your database and files. <br/>You should also export your language and template into XML file.
		            During upgrade process your default template will be overwritten with the new template. If you use more than one template then you will need to open /install/xml/master-style.xml and edit line
		            <br/>
					'.htmlspecialchars('<style name="Default Style" ilversion="3.2.0">').'
		            <br/>
					Instead of "Default Style" enter name of your style here. Now you should goto AdminCP->Styles / CSS->Import and import this edited style. This will overwrite your style. 
		            </span>
		            </div>';
		
		if (function_exists("version_compare") AND version_compare(MYSQL_VERSION, "5.1", ">="))
		{
			echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">MySQL version</font><br />
			</b></span>MySQL version >= 5.1.x</div>';
			
			echo '<div><strong><a href="installer.php?do=install&step=30&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
		}
		else
		{
			echo '<div class="redhlite"><span class="smaller"><b><font color="red">MySQL version</font><br />
			</b></span>MySQL version should be greater or equal than 5.1.x however ILance still supports backward compatibility for MySQL < 5.1.x. If you use large number of categories then you will need MySQL 5.1.x</div>';
			
			echo '<div><strong><a href="installer.php?do=install&step=30&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
		}
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