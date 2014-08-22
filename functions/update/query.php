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
$query['746'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_auctionstypeenabled', 'product', 'globalauctionsettings', 'pulldown', '', '', '1', '1')"; 
$query['747'] = "UPDATE " . DB_PREFIX . "configuration SET visible = '0' WHERE name = 'globalauctionsettings_productauctionsenabled'";
$query['748'] = "UPDATE " . DB_PREFIX . "configuration SET value = '0', visible = '0' WHERE name = 'globalauctionsettings_serviceauctionsenabled'";
$query['749'] = "CREATE TABLE " . DB_PREFIX . "distance_ng (`ZIPCode` INT(5) NOT NULL, `City` MEDIUMTEXT, `Latitude` DOUBLE NOT NULL default '0', `Longitude` DOUBLE NOT NULL default '0', `State` MEDIUMTEXT, KEY `ZIPCode` (`ZIPCode`), KEY `Latitude` (`Latitude`), KEY `Longitude` (`Longitude`)) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['750'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_ilanceaid', '', 'globalserversettings', 'text', '', '', '50', '1')";
$query['751'] = "DELETE FROM " . DB_PREFIX . "templates WHERE type != 'variable'";
$query['752'] = "ALTER TABLE " . DB_PREFIX . "templates ADD `iscustom` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER `status`";
$query['753'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `seourl_eng` MEDIUMTEXT NOT NULL AFTER `level`";
$query['754'] = "ALTER TABLE " . DB_PREFIX . "styles ADD `filehash` CHAR(32) NOT NULL DEFAULT '' AFTER `name`";
$query['755'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'externalcss' OR name = 'externalcsstimeout' OR name = 'refreshcsscache'";
$query['756'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `catimagehero` VARCHAR(250) NOT NULL DEFAULT '' AFTER `catimage`";
$query['757'] = "CREATE TABLE " . DB_PREFIX . "distance_ro (`ZIPCode` VARCHAR(255) NOT NULL, `City` MEDIUMTEXT, `Latitude` DOUBLE NOT NULL default '0', `Longitude` DOUBLE NOT NULL default '0', `State` MEDIUMTEXT, KEY `ZIPCode` (`ZIPCode`), KEY `Latitude` (`Latitude`), KEY `Longitude` (`Longitude`)) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['758'] = "CREATE TABLE " . DB_PREFIX . "distance_tr (`ZIPCode` VARCHAR(255) NOT NULL, `City` MEDIUMTEXT, `Latitude` DOUBLE NOT NULL default '0', `Longitude` DOUBLE NOT NULL default '0', `State` MEDIUMTEXT, KEY `ZIPCode` (`ZIPCode`), KEY `Latitude` (`Latitude`), KEY `Longitude` (`Longitude`)) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['759'] = "ALTER TABLE " . DB_PREFIX . "attachment CHANGE  `attachtype`  `attachtype` ENUM(  'profile',  'portfolio',  'project',  'itemphoto',  'bid',  'pmb',  'ws',  'kb',  'ads',  'digital',  'slideshow',  'stores',  'storesitemphoto',  'storesdigital',  'storesbackground',  'bb' ) NOT NULL DEFAULT  'profile'";
$query['760'] = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "email_queue (
`id` int(11) NOT NULL AUTO_INCREMENT,
`mail` tinytext NOT NULL,
`from` tinytext NOT NULL,
`fromname` tinytext NOT NULL,
`departmentid` smallint(6) NOT NULL DEFAULT '0',
`subject` text NOT NULL,
`message` mediumtext NOT NULL,
`dohtml` enum('1','0') NOT NULL DEFAULT '1',
`date_added` int(11) NOT NULL,
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['761'] = "INSERT INTO " . DB_PREFIX . "cron (`cronid`, `nextrun`, `weekday`, `day`, `hour`, `minute`, `filename`, `loglevel`, `active`, `varname`, `product`) VALUES
(NULL, 1, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.emailqueue.php', 1, 1, 'emailqueue', 'ilance');";  
$query['762'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversession', 'globalserversession', '800')";
$query['763'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_guesttimeout', '10', 'globalserversession', 'int', '', '', '100', '1')";
$query['764'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_membertimeout', '90', 'globalserversession', 'int', '', '', '200', '1')";
$query['765'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_admintimeout', '90', 'globalserversession', 'int', '', '', '300', '1')";
$query['766'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversession_crawlertimeout', '5', 'globalserversession', 'int', '', '', '400', '1')";
$query['767'] = "ALTER TABLE " . DB_PREFIX . "email_queue CHANGE `from` `fromemail` TINYTEXT NOT NULL";
$query['768'] = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "hero (
`id` int(5) NOT NULL AUTO_INCREMENT,
`mode` enum('homepage','categorymap') NOT NULL DEFAULT 'homepage',
`cid` INT(5) NOT NULL default '0',
`filename` tinytext NOT NULL,
`imagemap` mediumtext,
`date_added` DATETIME NOT NULL default '0000-00-00 00:00:00',
`sort` INT(5) NOT NULL default '0',
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['769'] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD varname VARCHAR(100) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT '' AFTER `date`";
$query['770'] = "ALTER TABLE " . DB_PREFIX . "email_queue ADD varname VARCHAR(100) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT '' AFTER `date_added`";
$query['771'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('emailssettings_queueenabled', '1', 'emailssettings', 'yesno', '', '', '100', '1')";
$query['772'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('emailssettings', 'emailssettings', '100')";
$query['773'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchlocationcriteria', '1', 'search', 'yesno', '', '', '300', '1')";
$query['774'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_format_tab', '1', 'globaltabvisibility', 'yesno', '', '', '70', '1')";
$query['775'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_price_tab', '1', 'globaltabvisibility', 'yesno', '', '', '80', '1')";
$query['776'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_currency_tab', '1', 'globaltabvisibility', 'yesno', '', '', '90', '1')";
$query['777'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_seller_tab', '1', 'globaltabvisibility', 'yesno', '', '', '100', '1')";
$query['778'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_location_tab', '1', 'globaltabvisibility', 'yesno', '', '', '110', '1')";
$query['779'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_options_tab', '1', 'globaltabvisibility', 'yesno', '', '', '120', '1')";
$query['780'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_colors_tab', '1', 'globaltabvisibility', 'yesno', '', '', '130', '1')";
$query['781'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_bidrange_tab', '1', 'globaltabvisibility', 'yesno', '', '', '140', '1')";
$query['782'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_radius_tab', '1', 'globaltabvisibility', 'yesno', '', '', '150', '1')";
$query['783'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_localsearch_tab', '1', 'globaltabvisibility', 'yesno', '', '', '151', '1')";
$query['784'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'searchlocationcriteria'";
$query['785'] = "UPDATE " . DB_PREFIX . "configuration SET inputtype = 'pulldown' WHERE name = 'globalauctionsettings_endsoondays'";
$query['786'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_facebookurl', '#', 'globalserversettings', 'text', '', '', '60', '1')";
$query['787'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_twitterurl', '#', 'globalserversettings', 'text', '', '', '70', '1')";
$query['788'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_googleplusurl', '#', 'globalserversettings', 'text', '', '', '80', '1')";
$query['789'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_developer_key', '', 'shippingapiservices', 'text', '', '', '325', '1')";
$query['790'] = "ALTER TABLE " . DB_PREFIX . "locations ADD visible ENUM('1','0') NOT NULL DEFAULT '1'";
$query['791'] = "DELETE FROM " . DB_PREFIX . "shippers WHERE carrier = 'fedex'";
$query['792'] = "INSERT INTO " . DB_PREFIX . "shippers
(`shipperid`, `title`, `shipcode`, `domestic`, `international`, `carrier`, `trackurl`)
VALUES
(NULL, 'Express Saver', 'FEDEX_EXPRESS_SAVER', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Ground', 'FEDEX_GROUND', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '2 Day', 'FEDEX_2_DAY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'First Overnight', 'FIRST_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Ground Home Delivery', 'GROUND_HOME_DELIVERY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Priority Overnight', 'PRIORITY_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Smart Post', 'SMART_POST', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Standard Overnight', 'STANDARD_OVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Freight', 'FEDEX_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '1 Day Freight', 'FEDEX_1_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '2 Day Freight', 'FEDEX_2_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '3 Day Freight', 'FEDEX_3_DAY_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'National Freight', 'FEDEX_NATIONAL_FREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Economy', 'INTERNATIONAL_ECONOMY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Economy Freight', 'INTERNATIONAL_ECONOMY_FREIGHT', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International First', 'INTERNATIONAL_FIRST', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Priority', 'INTERNATIONAL_PRIORITY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Priority Freight', 'INTERNATIONAL_PRIORITY_FREIGHT', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Europe First International Priority', 'EUROPE_FIRST_INTERNATIONAL_PRIORITY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers=')";
$query['793'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_password', '', 'shippingapiservices', 'pass', '', '', '321', '1')";
$query['794'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('keywords_tab_textcutoff', '35', 'globaltabvisibility', 'int', '', '', '45', '1')";
$query['795'] = "ALTER TABLE " . DB_PREFIX . "locations_states ADD visible ENUM('1','0') NOT NULL DEFAULT '1' AFTER `sc`";
$query['796'] = "ALTER TABLE " . DB_PREFIX . "locations_states ADD `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
$query['797'] = "ALTER TABLE " . DB_PREFIX . "locations_cities ADD `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
$query['798'] = "ALTER TABLE " . DB_PREFIX . "locations_cities ADD visible ENUM('1','0') NOT NULL DEFAULT '1' AFTER `city`";
$query['799'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_homepageadurl', '#', 'globalserversettings', 'text', '', '', '90', '1')";
$query['800'] = "ALTER TABLE " . DB_PREFIX . "email ADD `ishtml` INT(1) NOT NULL DEFAULT '0' AFTER `admin`";
$query['801'] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD `ishtml` INT(1) NOT NULL DEFAULT '0' AFTER `sent`";
$query['802'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE configgroup = 'stormpay'";
$query['803'] = "DELETE FROM " . DB_PREFIX . "payment_groups WHERE parentgroupname = 'stormpay'";
$query['804'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_quality', '100', 'attachmentsystem', 'int', '', '', '72', '1')";
$query['805'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_angle', '0', 'attachmentsystem', 'int', '', '', '74', '1')";
$query['806'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_position', 'TOPLEFT', 'attachmentsystem', 'text', '', '', '76', '1')";
$query['807'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_padding', '5', 'attachmentsystem', 'int', '', '', '78', '1')";
$query['808'] = "CREATE TABLE " . DB_PREFIX . "cms (
`terms` LONGTEXT NOT NULL default '',
`privacy` LONGTEXT NOT NULL default '',
`about` LONGTEXT NOT NULL default '',
`registrationterms` LONGTEXT NOT NULL default '',
`news` LONGTEXT NOT NULL default ''
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['809'] = "INSERT INTO " . DB_PREFIX . "cms (terms, privacy, about, registrationterms, news) SELECT terms, privacy, about, registrationterms, news FROM " . DB_PREFIX . "admincp_news";
$query['810'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` DROP `terms`";
$query['811'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` DROP `privacy`";
$query['812'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` DROP `about`";
$query['813'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` DROP `registrationterms`";
$query['814'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` DROP `news`";
$query['815'] = "ALTER TABLE `" . DB_PREFIX . "admincp_news` ADD `subject` VARCHAR(250) NOT NULL DEFAULT '' AFTER `newsid`";
$query['816'] = "TRUNCATE TABLE `" . DB_PREFIX . "admincp_news`";
$query['817'] = "ALTER TABLE `" . DB_PREFIX . "subscription_permissions` ADD `accessgroup` VARCHAR(250) NOT NULL DEFAULT '' AFTER `subscriptiongroupid`";
$query['818'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'attachment' WHERE accessname = 'attachments' OR accessname = 'attachlimit' OR accessname = 'uploadlimit' OR accessname = 'maxpmbattachments' OR accessname = 'maxbidattachments' OR accessname = 'maxprojectattachments' OR accessname = 'maxprofileattachments' OR accessname = 'maxportfolioattachments' OR accessname = 'bulkattachlimit' OR accessname = 'addportfolio'";
$query['819'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'accounting' WHERE accessname = 'deposit' OR accessname = 'withdraw' OR accessname = 'addcreditcard' OR accessname = 'delcreditcard' OR accessname = 'usecreditcard' OR accessname = 'addbankaccount' OR accessname = 'delbankaccount' OR accessname = 'usebankaccount' OR accessname = 'generateinvoices' OR accessname = 'enablecurrencyconversion'";
$query['820'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'messaging' WHERE accessname = 'pmb' OR accessname = 'pmbtotal' OR accessname = 'pmbcompose'";
$query['821'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'listingbiddinglimits' WHERE accessname = 'auctiondelists' OR accessname = 'bidretracts' OR accessname = 'bidlimitperday' OR accessname = 'bidlimitpermonth' OR accessname = 'buynow' OR accessname = 'createserviceauctions' OR accessname = 'servicebid' OR accessname = 'createproductauctions' OR accessname = 'productbid' OR accessname = 'cansealbids' OR accessname = 'canblindbids' OR accessname = 'canfullprivacybids'";
$query['822'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'other' WHERE accessname = 'searchresults' OR accessname = 'workshare' OR accessname = 'distance' OR accessname = 'updateprofile' OR accessname = 'maxprofilegroups' OR accessname = 'newsletteropt_in' OR accessname = 'maxskillscat' OR accessname = 'inviteprovider' OR accessname = 'addtowatchlist' OR accessname = 'iprestrict' OR accessname = 'createserviceprofile'";
$query['823'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessgroup = 'exemptions' WHERE accessname = 'fvfexempt' OR accessname = 'insexempt' OR accessname = 'payasgoexempt' OR accessname = 'posthtml' OR accessname = 'servicefvfgroup' OR accessname = 'productfvfgroup' OR accessname = 'serviceinsgroup' OR accessname = 'productinsgroup'";
$query['824'] = "ALTER TABLE `" . DB_PREFIX . "subscription_permissions` ADD `accessmode` ENUM('global', 'service', 'product') NOT NULL DEFAULT 'global' AFTER `accesstype`";
$query['825'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessmode = 'global' WHERE accessname = 'attachments' OR accessname = 'attachlimit' OR accessname = 'uploadlimit' OR accessname = 'maxpmbattachments' OR accessname = 'maxprofileattachments' OR accessname = 'deposit' OR accessname = 'withdraw' OR accessname = 'addcreditcard' OR accessname = 'delcreditcard' OR accessname = 'usecreditcard' OR accessname = 'addbankaccount' OR accessname = 'delbankaccount' OR accessname = 'usebankaccount' OR accessname = 'enablecurrencyconversion' OR accessname = 'pmb' OR accessname = 'pmbtotal' OR accessname = 'pmbcompose' OR accessname = 'auctiondelists' OR accessname = 'bidretracts' OR accessname = 'bidlimitperday' OR accessname = 'bidlimitpermonth' OR accessname = 'cansealbids' OR accessname = 'canblindbids' OR accessname = 'canfullprivacybids' OR accessname = 'searchresults' OR accessname = 'distance' OR accessname = 'updateprofile' OR accessname = 'newsletteropt_in' OR accessname = 'addtowatchlist' OR accessname = 'iprestrict' OR accessname = 'fvfexempt' OR accessname = 'insexempt' OR accessname = 'payasgoexempt' OR accessname = 'posthtml'";
$query['826'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessmode = 'service' WHERE accessname = 'maxbidattachments' OR accessname = 'maxprojectattachments' OR accessname = 'maxportfolioattachments' OR accessname = 'addportfolio' OR accessname = 'generateinvoices' OR accessname = 'createserviceauctions' OR accessname = 'servicebid' OR accessname = 'workshare' OR accessname = 'maxprofilegroups' OR accessname = 'maxskillscat' OR accessname = 'inviteprovider' OR accessname = 'createserviceprofile' OR accessname = 'servicefvfgroup' OR accessname = 'serviceinsgroup'";
$query['827'] = "UPDATE `" . DB_PREFIX . "subscription_permissions` SET accessmode = 'product' WHERE accessname = 'bulkattachlimit' OR accessname = 'buynow' OR accessname = 'createproductauctions' OR accessname = 'productbid' OR accessname = 'productfvfgroup' OR accessname = 'productinsgroup'";
$query['828'] = "CREATE TABLE " . DB_PREFIX . "subscription_permissions_groups (
`groupid` INT(5) NOT NULL AUTO_INCREMENT,
`accessgroup` VARCHAR(250) NOT NULL default '',
`original` INT(1) NOT NULL default '0',
`visible` INT(1) NOT NULL default '1',
PRIMARY KEY  (`groupid`),
INDEX ( `accessgroup` )
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['829'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions_groups 
VALUES
(NULL, 'attachment', 1, 1),
(NULL, 'accounting', 1, 1),
(NULL, 'messaging', 1, 1),
(NULL, 'listingbiddinglimits', 1, 1),
(NULL, 'other', 1, 1),
(NULL, 'exemptions', 1, 1)";
$query['830'] = "ALTER TABLE " . DB_PREFIX . "emaillog ADD `type` ENUM('global','service','product') NOT NULL DEFAULT 'global' AFTER `varname`";
$query['831'] = "ALTER TABLE " . DB_PREFIX . "email_queue ADD `type` ENUM('global','service','product') NOT NULL DEFAULT 'global' AFTER `varname`";
$query['832'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `date_retracted` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `date_awarded`";
$query['833'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `date_retracted` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `date_awarded`";
$query['834'] = "ALTER TABLE " . DB_PREFIX . "project_bid_retracts CHANGE `date` `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
$query['835'] = "ALTER TABLE " . DB_PREFIX . "project_bid_retracts ADD `bidamount` DOUBLE(17, 2) NOT NULL DEFAULT '0.00' AFTER `project_id`";
$query['836'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `bid_id` INT(5) NOT NULL DEFAULT '0' AFTER `owner_id`";
$query['837'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `returndate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `releasedate`";
$query['838'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `returnedondate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `returndate`";
$query['839'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `catimageherourl` MEDIUMTEXT NOT NULL AFTER `catimagehero`";
$query['840'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'enable_uniquebidding'";
$query['841'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `project_details` `project_details` ENUM('public','invite_only','realtime') NOT NULL DEFAULT 'public'";
$query['842'] = "ALTER TABLE " . DB_PREFIX . "projects DROP `retailprice`";
$query['843'] = "ALTER TABLE " . DB_PREFIX . "projects DROP `uniquebidcount`";
$query['844'] = "DROP TABLE IF EXISTS " . DB_PREFIX . "projects_uniquebids";
$query['845'] = "DROP TABLE IF EXISTS " . DB_PREFIX . "projects_trackbacks";
$query['846'] = "ALTER TABLE " . DB_PREFIX . "skills ADD `seourl_eng` MEDIUMTEXT NOT NULL AFTER `rootcid`";
$query['847'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'template_metatitle' OR name = 'template_metadescription' OR name = 'template_metakeywords'";
$query['848'] = "DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'metatags'";
$query['849'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('subscriptions_defaultroleid', '0', 'subscriptions_settings', 'int',  '', '', '2', '1')";
$query['850'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST";
$query['851'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `gatewayrequest` MEDIUMTEXT NOT NULL AFTER `datetime`";
$query['852'] = "ALTER TABLE " . DB_PREFIX . "product_questions_choices ADD `parentoptionid` INT( 5 ) NOT NULL AFTER `optionid`";
$query['853'] = "ALTER TABLE " . DB_PREFIX . "project_questions_choices ADD `parentoptionid` INT( 5 ) NOT NULL AFTER `optionid`";
$query['854'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_fedex', '300')";
$query['855'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_ups', '310')";
$query['856'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices_usps', '320')";
$query['857'] = "DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'shippingapiservices'";
$query['858'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_ups' WHERE name = 'ups_access_id' LIMIT 1";
$query['859'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_ups' WHERE name = 'ups_username' LIMIT 1";
$query['860'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_ups' WHERE name = 'ups_password' LIMIT 1";
$query['861'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_ups' WHERE name = 'ups_server' LIMIT 1";
$query['862'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_usps' WHERE name = 'usps_login' LIMIT 1";
$query['863'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_usps' WHERE name = 'usps_password' LIMIT 1";
$query['864'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_usps' WHERE name = 'usps_server' LIMIT 1";
$query['865'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_fedex' WHERE name = 'fedex_account' LIMIT 1";
$query['866'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_fedex' WHERE name = 'fedex_access_id' LIMIT 1";
$query['867'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_fedex' WHERE name = 'fedex_server' LIMIT 1";
$query['868'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_fedex' WHERE name = 'fedex_developer_key' LIMIT 1";
$query['869'] = "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'shippingapiservices_fedex' WHERE name = 'fedex_password' LIMIT 1";
$query['870'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'fedex_server' LIMIT 1"; // got from wdsl file..
$query['871'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('shippingapi_debug', '0', 'shippingsettings', 'yesno', '', '', '1915', '1')";
$query['872'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `from_city` MEDIUMTEXT NOT NULL AFTER `from_state`";
$query['873'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `to_city` MEDIUMTEXT NOT NULL AFTER `to_state`";
$query['874'] = "ALTER TABLE " . DB_PREFIX . "locations_cities ADD `locationid` INT(100) NOT NULL DEFAULT '0' AFTER `id`";
$query['875'] = "ALTER TABLE " . DB_PREFIX . "distance_usa DROP `ZIPCodeType`";
$query['876'] = "ALTER TABLE " . DB_PREFIX . "distance_usa DROP `CityType`";
$query['877'] = "ALTER TABLE " . DB_PREFIX . "distance_usa DROP `StateCode`";
$query['878'] = "ALTER TABLE " . DB_PREFIX . "distance_usa DROP `AreaCode`";
$query['879'] = "ALTER TABLE " . DB_PREFIX . "configuration_groups ADD `type` ENUM('global','service','product') NOT NULL DEFAULT 'global' AFTER `sort`";
$query['880'] = "ALTER TABLE " . DB_PREFIX . "configuration ADD `type` ENUM('global','service','product') NOT NULL DEFAULT 'global' AFTER `visible`";
$query['881'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE configgroup = 'googlecheckout'";
$query['882'] = "DELETE FROM " . DB_PREFIX . "payment_groups WHERE groupname = 'googlecheckout'";
$query['883'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalfilters_contactform_member' LIMIT 1";
$query['884'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'search_work_public' LIMIT 1";
$query['885'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'search_format_tab' LIMIT 1";
$query['886'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'moneybookers_md5_digest' LIMIT 1";
$query['887'] = "ALTER TABLE " . DB_PREFIX . "product_questions_choices ADD `choice_eng` MEDIUMTEXT NOT NULL AFTER `questionid`";
$query['888'] = "ALTER TABLE " . DB_PREFIX . "project_questions_choices ADD `choice_eng` MEDIUMTEXT NOT NULL AFTER `questionid`";
$query['889'] = "ALTER TABLE " . DB_PREFIX . "product_questions DROP `multiplechoice`";
$query['890'] = "ALTER TABLE " . DB_PREFIX . "project_questions DROP `multiplechoice`";
$query['891'] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD mode ENUM('service','product') NOT NULL DEFAULT 'product' AFTER `state`";
$query['892'] = "ALTER TABLE " . DB_PREFIX . "watchlist ADD `dateadded` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' AFTER `comment`";
$query['893'] = "ALTER TABLE " . DB_PREFIX . "product_questions_choices DROP `auctioncount`";
$query['894'] = "ALTER TABLE " . DB_PREFIX . "project_questions_choices DROP `auctioncount`";
$query['895'] = "INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('seo', 'SEO Phrases', 'ilance')";
$query['896'] = "CREATE TABLE " . DB_PREFIX . "distance_br (`ZIPCode` CHAR(25) NOT NULL, `City` MEDIUMTEXT, `Latitude` DOUBLE NOT NULL default '0', `Longitude` DOUBLE NOT NULL default '0', `State` MEDIUMTEXT, KEY `ZIPCode` (`ZIPCode`), KEY `Latitude` (`Latitude`), KEY `Longitude` (`Longitude`)) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$query['897'] = "UPDATE " . DB_PREFIX . "configuration SET inputtype = 'text', value = '_marketplace_currently_in_maintenance_mode' WHERE name = 'maintenance_message' LIMIT 1";
$query['898'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('seourls_utf8', '1', 'globalseo', 'yesno', '', '', '3', '1', 'global')";

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>