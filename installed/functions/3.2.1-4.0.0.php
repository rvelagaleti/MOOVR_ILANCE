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
$current_version = $ilance->db->fetch_field(DB_PREFIX . "configuration", "name = 'current_version'", "value");
$new_sql_version = $sql_version = $ilance->db->fetch_field(DB_PREFIX . "configuration", "name = 'current_sql_version'", "value");
// #### BEGIN SQL UPDATE CODE ##################################################
$queries = array();
// #### HELPER FUNCTIONS ###############################################
// add_field_if_not_exist($table = '', $column = '', $attributes = '', $addaftercolumn = '') ....
// table_exists($table = '') ....
// field_exists($field = '', $table = '') ....
// $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Newsletter Resources', $accessdescription = 'Defines if any customer within this subscription group can opt-in to any of the available newsletter resources', $accessname = 'newsletteropt_in', $accesstype = 'yesno', $value = 'yes', $canremove = 0);
$queries['164'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_regions DROP country, DROP region";
$queries['165'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_regions CHANGE countryid countryid SMALLINT UNSIGNED NOT NULL DEFAULT '0'";
$queries['166'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_regions CHANGE project_id project_id INT UNSIGNED NOT NULL";
$queries['167'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_regions CHANGE row row TINYINT UNSIGNED NOT NULL";
$queries['168'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `city` MEDIUMTEXT NOT NULL AFTER `currency`";
$queries['169'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `state` MEDIUMTEXT NOT NULL AFTER `city`";
$queries['170'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `zipcode` MEDIUMTEXT NOT NULL AFTER `state`";
$queries['171'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `country` MEDIUMTEXT NOT NULL AFTER `zipcode`";
$queries['172'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadcolsep', ',', 'globalfiltersrfp', 'text', '', '', '9', '1')";
$queries['173'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadcolencap', '" . $ilance->db->escape_string("'") . "', 'globalfiltersrfp', 'text', '', '', '10', '1')";
$queries['174'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp CHANGE `project_details` `project_details` MEDIUMTEXT NOT NULL DEFAULT ''";
$queries['175'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `dateupload` DATE NOT NULL default '0000-00-00' AFTER `country`";
$queries['176'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `buynow_qty_lot` INT(1) NOT NULL DEFAULT '0' AFTER `buynow_qty`";
$queries['177'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `buynow_qty_lot` INT(1) NOT NULL DEFAULT '0' AFTER `buynow_qty`";
$queries['178'] = "ALTER TABLE " . DB_PREFIX . "attachment_folder ADD `user_id` INT(10) NOT NULL DEFAULT '0' AFTER `project_id`";
$queries['179'] = "ALTER TABLE " . DB_PREFIX . "referral_data DROP `paylanceads`";
$queries['180'] = "ALTER TABLE " . DB_PREFIX . "subscription ADD `visible_registration` ENUM('0','1') DEFAULT '1' AFTER `visible`";
$queries['181'] = "ALTER TABLE " . DB_PREFIX . "subscription ADD `visible_upgrade` ENUM('0','1') DEFAULT '1' AFTER `visible`";
$queries['182'] = "ALTER TABLE " . DB_PREFIX . "subscription DROP `visible`";
$queries['183'] = "ALTER TABLE " . DB_PREFIX . "invoices ADD `canceldate` DATETIME NOT NULL AFTER `paiddate`";
$queries['184'] = "ALTER TABLE " . DB_PREFIX . "invoices ADD `canceluserid` INT(5) NOT NULL DEFAULT '0' AFTER `canceldate`";
$queries['185'] = "ALTER TABLE " . DB_PREFIX . "invoices ADD `isautopayment` INT(1) NOT NULL DEFAULT '0' AFTER `indispute`";
$queries['186'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp CHANGE `startprice` `startprice` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `buynow_price` `buynow_price` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `reserve_price` `reserve_price` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `buynow_qty` `buynow_qty` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `buynow_qty_lot` `buynow_qty_lot` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `filtered_auctiontype` `filtered_auctiontype` MEDIUMTEXT  NOT NULL DEFAULT '',
CHANGE `cid` `cid` MEDIUMTEXT NOT NULL DEFAULT '',
CHANGE `currency` `currency` MEDIUMTEXT NOT NULL DEFAULT ''";
$queries['187'] = "ALTER TABLE " . DB_PREFIX . "distance_sp ADD `City` MEDIUMTEXT NOT NULL AFTER `ZIPCode`";
$queries['188'] = "CREATE TABLE " . DB_PREFIX . "industries (
`cid` int(100) NOT NULL auto_increment,
`parentid` int(100) NOT NULL default '0',
`level` int(5) NOT NULL default '1',
`title_eng` mediumtext,
`description_eng` mediumtext,
`views` int(100) NOT NULL default '0',
`keywords` mediumtext,
`visible` int(1) NOT NULL default '1',
`sort` int(3) NOT NULL default '0',
PRIMARY KEY  (`cid`),
INDEX ( `parentid` ),
INDEX ( `level` )) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['189'] = "CREATE TABLE " . DB_PREFIX . "industries_answers (
`aid` INT(5) NOT NULL AUTO_INCREMENT,
`cid` INT(5) NOT NULL,
`user_id` INT(10) NOT NULL,
PRIMARY KEY  (`aid`),
INDEX ( `cid` ),
INDEX ( `user_id` )) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['190'] = "INSERT INTO `" . DB_PREFIX . "industries` (`cid`, `parentid`, `level`, `title_eng`, `description_eng`, `views`, `keywords`, `visible`, `sort`) VALUES
(1, 0, 1, 'Agriculture', NULL, 0, 'Agriculture', 1, 100),
(2, 0, 1, 'Arts', NULL, 0, 'Arts', 1, 200),
(3, 0, 1, 'Construction', NULL, 0, 'Construction', 1, 300),
(4, 0, 1, 'Consumer Goods', NULL, 0, 'Consumer Goods', 1, 400),
(5, 0, 1, 'Corporate', NULL, 0, 'Corporate', 1, 500),
(6, 0, 1, 'Educational', NULL, 0, 'Educational', 1, 600),
(7, 0, 1, 'Finance', NULL, 0, 'Finance', 1, 700),
(8, 0, 1, 'Government', NULL, 0, 'Government', 1, 800),
(9, 0, 1, 'High Tech', NULL, 0, 'High Tech', 1, 900),
(10, 0, 1, 'Legal', NULL, 0, 'Legal', 1, 1000),
(11, 0, 1, 'Manufacturing', NULL, 0, NULL, 1, 1100),
(12, 0, 1, 'Media', NULL, 0, 'Media', 1, 1200),
(13, 0, 1, 'Non-profit', NULL, 0, 'Non-profit', 1, 1400),
(14, 0, 1, 'Recreational', NULL, 0, 'Recreational', 1, 1500),
(15, 0, 1, 'Service', NULL, 0, 'Service', 1, 1600),
(16, 0, 1, 'Transportation', NULL, 0, 'Transportation', 1, 1700),
(17, 1, 2, 'Farming', NULL, 0, 'Farming', 1, 10),
(18, 1, 2, 'Ranching', NULL, 0, 'Ranching', 1, 20),
(19, 1, 2, 'Dairy', NULL, 0, 'Dairy', 1, 30),
(20, 1, 2, 'Fishery', NULL, 0, 'Fishery', 1, 40),
(21, 2, 2, 'Motion Pictures and Film', NULL, 0, 'Motion Pictures and Film', 1, 10),
(22, 2, 2, 'Museums and Institutions', NULL, 0, 'Museums and Institutions', 1, 20),
(23, 2, 2, 'Fine Art', NULL, 0, 'Fine Art', 1, 30),
(24, 2, 2, 'Performing Arts', NULL, 0, 'Performing Arts', 1, 40),
(25, 2, 2, 'Design', NULL, 0, 'Design', 1, 50),
(26, 2, 2, 'Arts and Crafts', NULL, 0, 'Arts and Crafts', 1, 60),
(27, 2, 2, 'Photography', NULL, 0, 'Photography', 1, 70),
(28, 2, 2, 'Graphic Design', NULL, 0, 'Graphic Design', 1, 80),
(29, 3, 2, 'Construction', NULL, 0, 'Construction', 1, 10),
(30, 3, 2, 'Building Materials', NULL, 0, 'Building Materials', 1, 20),
(31, 3, 2, 'Architecture & Planning', NULL, 0, 'Architecture & Planning', 1, 30),
(32, 3, 2, 'Civil Engineering', NULL, 0, 'Civil Engineering', 1, 40),
(33, 4, 2, 'Cosmetics', NULL, 0, 'Cosmetics', 1, 1),
(34, 4, 2, 'Apparel & Fashion', NULL, 0, 'Apparel & Fashion', 1, 2),
(35, 4, 2, 'Sporting Goods', NULL, 0, 'Sporting Goods', 1, 3),
(36, 4, 2, 'Tobacco', NULL, 0, 'Tobacco', 1, 4),
(37, 4, 2, 'Supermarkets', NULL, 0, 'Supermarkets', 1, 5),
(38, 4, 2, 'Food Production', NULL, 0, 'Food Production', 1, 6),
(39, 4, 2, 'Consumer Electronics', NULL, 0, 'Consumer Electronics', 1, 7),
(40, 4, 2, 'Consumer Goods', NULL, 0, 'Consumer Goods', 1, 8),
(41, 4, 2, 'Furniture', NULL, 0, 'Furniture', 1, 9),
(42, 4, 2, 'Retail', NULL, 0, 'Retail', 1, 10),
(43, 4, 2, 'Wholesale', NULL, 0, 'Wholesale', 1, 11),
(44, 4, 2, 'Import and Export', NULL, 0, 'Import and Export', 1, 12),
(45, 4, 2, 'Wine and Spirits', NULL, 0, 'Wine and Spirits', 1, 13),
(46, 4, 2, 'Luxury Goods & Jewelry', NULL, 0, 'Luxury Goods & Jewelry', 1, 14),
(47, 5, 2, 'Management Consulting', NULL, 0, 'Management Consulting', 1, 1),
(48, 5, 2, 'Marketing and Advertising', NULL, 0, 'Marketing and Advertising', 1, 2),
(49, 5, 2, 'Market Research', NULL, 0, 'Market Research', 1, 3),
(50, 5, 2, 'Public Relations and Communications', NULL, 0, 'Public Relations and Communications', 1, 4),
(51, 5, 2, 'Staffing and Recruiting', NULL, 0, 'Staffing and Recruiting', 1, 5),
(52, 5, 2, 'Professional Training & Coaching', NULL, 0, 'Professional Training & Coaching', 1, 6),
(53, 5, 2, 'Security and Investigations', NULL, 0, 'Security and Investigations', 1, 7),
(54, 5, 2, 'Facilities Services', NULL, 0, 'Facilities Services', 1, 8),
(55, 5, 2, 'Outsourcing/Offshoring', NULL, 0, 'Outsourcing/Offshoring', 1, 9),
(56, 5, 2, 'Human Resources', NULL, 0, 'Human Resources', 1, 10),
(57, 5, 2, 'Business Supplies and Equipment', NULL, 0, 'Business Supplies and Equipment', 1, 11),
(58, 6, 2, 'Primary/Secondary Education', NULL, 0, 'Primary/Secondary Education', 1, 1),
(59, 6, 2, 'Higher Education', NULL, 0, 'Higher Education', 1, 2),
(60, 6, 2, 'Education Management', NULL, 0, 'Education Management', 1, 3),
(61, 6, 2, 'Research', NULL, 0, 'Research', 1, 4),
(62, 6, 2, 'E-Learning', NULL, 0, 'E-Learning', 1, 5),
(63, 7, 2, 'Banking', NULL, 0, 'Banking', 1, 1),
(64, 7, 2, 'Insurance', NULL, 0, 'Insurance', 1, 2),
(65, 7, 2, 'Financial Services', NULL, 0, 'Financial Services', 1, 3),
(66, 7, 2, 'Real Estate', NULL, 0, 'Real Estate', 1, 4),
(67, 7, 2, 'Investment Banking', NULL, 0, 'Investment Banking', 1, 5),
(68, 7, 2, 'Investment Management', NULL, 0, 'Investment Management', 1, 6),
(69, 7, 2, 'Accounting', NULL, 0, 'Accounting', 1, 7),
(70, 7, 2, 'Venture Capital & Private Equity', NULL, 0, 'Venture Capital & Private Equity', 1, 8),
(71, 7, 2, 'Commercial Real Estate', NULL, 0, 'Commercial Real Estate', 1, 9),
(72, 7, 2, 'Capital Markets', NULL, 0, 'Capital Markets', 1, 10),
(73, 8, 2, 'Military', NULL, 0, 'Military', 1, 1),
(74, 8, 2, 'Legislative Office', NULL, 0, 'Legislative Office', 1, 2),
(75, 8, 2, 'Judiciary', NULL, 0, 'Judiciary', 1, 3),
(76, 8, 2, 'International Affairs', NULL, 0, 'International Affairs', 1, 4),
(77, 8, 2, 'Government Administration', NULL, 0, 'Government Administration', 1, 5),
(78, 8, 2, 'Executive Office', NULL, 0, 'Executive Office', 1, 6),
(79, 8, 2, 'Law Enforcement', NULL, 0, 'Law Enforcement', 1, 7),
(80, 8, 2, 'Public Safety', NULL, 0, 'Public Safety', 1, 8),
(81, 8, 2, 'Public Policy', NULL, 0, 'Public Policy', 1, 9),
(82, 8, 2, 'Political Organization', NULL, 0, 'Political Organization', 1, 10),
(83, 8, 2, 'Government Relations', NULL, 0, 'Government Relations', 1, 11),
(84, 9, 2, 'Defense & Space', NULL, 0, 'Defense & Space', 1, 1),
(85, 9, 2, 'Computer Hardware', NULL, 0, 'Computer Hardware', 1, 2),
(86, 9, 2, 'Computer Software', NULL, 0, 'Computer Software', 1, 3),
(87, 9, 2, 'Computer Networking', NULL, 0, 'Computer Networking', 1, 4),
(88, 9, 2, 'Internet', NULL, 0, 'Internet', 1, 5),
(89, 9, 2, 'Semiconductors', NULL, 0, 'Semiconductors', 1, 6),
(90, 9, 2, 'Telecommunications', NULL, 0, 'Telecommunications', 1, 7),
(91, 9, 2, 'Information Technology and Services', NULL, 0, 'Information Technology and Services', 1, 8),
(92, 9, 2, 'Nanotechnology', NULL, 0, 'Nanotechnology', 1, 9),
(93, 9, 2, 'Computer & Network Security', NULL, 0, 'Computer & Network Security', 1, 10),
(94, 9, 2, 'Wireless', NULL, 0, 'Wireless', 1, 11),
(95, 10, 2, 'Law Practice', NULL, 0, 'Law Practice', 1, 1),
(96, 10, 2, 'Legal Services', NULL, 0, 'Legal Services', 1, 2),
(97, 10, 2, 'Alternative Dispute Resolution', NULL, 0, 'Alternative Dispute Resolution', 1, 3),
(98, 11, 2, 'Aviation & Aerospace', NULL, 0, 'Aviation & Aerospace', 1, 1),
(99, 11, 2, 'Automotive', NULL, 0, 'Automotive', 1, 2),
(100, 11, 2, 'Chemicals', NULL, 0, 'Chemicals', 1, 3),
(101, 11, 2, 'Machinery', NULL, 0, 'Machinery', 1, 4),
(102, 11, 2, 'Mining & Metals', NULL, 0, 'Mining & Metals', 1, 5),
(103, 11, 2, 'Oil & Energy', NULL, 0, 'Oil & Energy', 1, 6),
(104, 11, 2, 'Shipbuilding', NULL, 0, 'Shipbuilding', 1, 7),
(105, 11, 2, 'Utilities', NULL, 0, 'Utilities', 1, 8),
(106, 11, 2, 'Textiles', NULL, 0, 'Textiles', 1, 9),
(107, 11, 2, 'Paper & Forest Products', NULL, 0, 'Paper & Forest Products', 1, 10),
(108, 11, 2, 'Railroad Manufacture', NULL, 0, 'Railroad Manufacture', 1, 11),
(109, 11, 2, 'Electrical/Electronic Manufacturing', NULL, 0, 'Electrical/Electronic Manufacturing', 1, 12),
(110, 11, 2, 'Plastics', NULL, 0, 'Plastics', 1, 13),
(111, 11, 2, 'Mechanical or Industrial Engineering', NULL, 0, 'Mechanical or Industrial Engineering', 1, 14),
(112, 11, 2, 'Renewables & Environment', NULL, 0, 'Renewables & Environment', 1, 15),
(113, 11, 2, 'Glass, Ceramics & Concrete', NULL, 0, 'Glass, Ceramics & Concrete', 1, 16),
(114, 11, 2, 'Packaging and Containers', NULL, 0, 'Packaging and Containers', 1, 17),
(115, 11, 2, 'Industrial Automation', NULL, 0, 'Industrial Automation', 1, 18),
(116, 12, 2, 'Broadcast Media', NULL, 0, 'Broadcast Media', 1, 1),
(117, 12, 2, 'Newspapers', NULL, 0, 'Newspapers', 1, 2),
(118, 12, 2, 'Publishing', NULL, 0, 'Publishing', 1, 3),
(119, 12, 2, 'Printing', NULL, 0, 'Printing', 1, 4),
(120, 12, 2, 'Writing and Editing', NULL, 0, 'Writing and Editing', 1, 5),
(121, 12, 2, 'Online Media', NULL, 0, 'Online Media', 1, 6),
(122, 12, 2, 'Media Production', NULL, 0, 'Media Production', 1, 7),
(123, 12, 2, 'Animation', NULL, 0, 'Animation', 1, 8),
(124, 0, 1, 'Medical', NULL, 0, 'Medical', 1, 1300),
(125, 124, 2, 'Biotechnology', NULL, 0, 'Biotechnology', 1, 1),
(126, 124, 2, 'Medical Practice', NULL, 0, 'Medical Practice', 1, 2),
(127, 124, 2, 'Hospital & Health Care', NULL, 0, 'Hospital & Health Care', 1, 3),
(128, 124, 2, 'Pharmaceuticals', NULL, 0, 'Pharmaceuticals', 1, 4),
(129, 124, 2, 'Veterinary', NULL, 0, 'Veterinary', 1, 5),
(130, 124, 2, 'Medical Devices', NULL, 0, 'Medical Devices', 1, 6),
(131, 124, 2, 'Health, Wellness and Fitness', NULL, 0, 'Health, Wellness and Fitness', 1, 7),
(132, 124, 2, 'Alternative Medicine', NULL, 0, 'Alternative Medicine', 1, 8),
(133, 124, 2, 'Mental Health Care', NULL, 0, 'Mental Health Care', 1, 9),
(134, 13, 2, 'Consumer Services', NULL, 0, 'Consumer Services', 1, 1),
(135, 13, 2, 'Non-Profit Organization Management', NULL, 0, 'Non-Profit Organization Management', 1, 2),
(136, 13, 2, 'Fund-Raising', NULL, 0, 'Fund-Raising', 1, 3),
(137, 13, 2, 'Program Development', NULL, 0, 'Program Development', 1, 4),
(138, 13, 2, 'Think Tanks', NULL, 0, 'Think Tanks', 1, 5),
(139, 13, 2, 'Philanthropy', NULL, 0, 'Philanthropy', 1, 6),
(140, 13, 2, 'International Trade and Development', NULL, 0, 'International Trade and Development', 1, 7),
(141, 14, 2, 'Entertainment', NULL, 0, 'Entertainment', 1, 1),
(142, 14, 2, 'Gambling & Casinos', NULL, 0, 'Gambling & Casinos', 1, 2),
(143, 14, 2, 'Leisure, Travel & Tourism', NULL, 0, 'Leisure, Travel & Tourism', 1, 3),
(144, 14, 2, 'Hospitality', NULL, 0, 'Hospitality', 1, 4),
(145, 14, 2, 'Restaurants', NULL, 0, 'Restaurants', 1, 5),
(146, 14, 2, 'Sports', NULL, 0, 'Sports', 1, 6),
(147, 14, 2, 'Food & Beverages', NULL, 0, 'Food & Beverages', 1, 7),
(148, 14, 2, 'Recreational Facilities and Services', NULL, 0, 'Recreational Facilities and Services', 1, 8),
(149, 14, 2, 'Computer Games', NULL, 0, 'Computer Games', 1, 9),
(150, 14, 2, 'Events Services', NULL, 0, 'Events Services', 1, 10),
(151, 15, 2, 'Information Services', NULL, 0, 'Information Services', 1, 1),
(152, 15, 2, 'Libraries', NULL, 0, 'Libraries', 1, 2),
(153, 15, 2, 'Environmental Services', NULL, 0, 'Environmental Services', 1, 3),
(154, 15, 2, 'Individual & Family Services', NULL, 0, 'Individual & Family Services', 1, 4),
(155, 15, 2, 'Religious Institutions', NULL, 0, 'Religious Institutions', 1, 5),
(156, 15, 2, 'Civic & Social Organization', NULL, 0, 'Civic & Social Organization', 1, 6),
(157, 15, 2, 'Translation and Localization', NULL, 0, 'Translation and Localization', 1, 7),
(158, 16, 2, 'Package/Freight Delivery', NULL, 0, 'Package/Freight Delivery', 1, 0),
(159, 16, 2, 'Transportation/Trucking/Railroad', NULL, 0, 'Transportation/Trucking/Railroad', 1, 2),
(160, 16, 2, 'Warehousing', NULL, 0, 'Warehousing', 1, 3),
(161, 16, 2, 'Airlines/Aviation', NULL, 0, 'Airlines/Aviation', 1, 4),
(162, 16, 2, 'Maritime', NULL, 0, 'Maritime', 1, 5),
(163, 16, 2, 'Logistics and Supply Chain', NULL, 0, 'Logistics and Supply Chain', 1, 6);";
$queries['191'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `sku` VARCHAR(250) NOT NULL DEFAULT '' AFTER `zipcode`";
$queries['192'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `upc` VARCHAR(250) NOT NULL DEFAULT '' AFTER `sku`";
$queries['193'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `ean` VARCHAR(250) NOT NULL DEFAULT '' AFTER `upc`";
$queries['194'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `isbn` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ean`";
$queries['195'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `partnumber` VARCHAR(250) NOT NULL DEFAULT '' AFTER `isbn`";
$queries['196'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `modelnumber` VARCHAR(250) NOT NULL DEFAULT '' AFTER `partnumber`";
$queries['197'] = "ALTER TABLE " . DB_PREFIX . "projects DROP `isbn`";
$queries['198'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `isbn10` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ean`";
$queries['199'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `isbn13` VARCHAR(250) NOT NULL DEFAULT '' AFTER `isbn10`";
$queries['200'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `attributes` MEDIUMTEXT AFTER `country`";
$queries['201'] = "INSERT INTO " . DB_PREFIX . "cron (nextrun, weekday, day, hour, minute, filename, loglevel, varname, product)
VALUES
(1053532560, -1, -1, -1, 'a:1:{i:0;i:-1;}', 'cron.minute.php', 1, 'minute', 'ilance'),
(1053532560, -1, -1, -1, 'a:1:{i:0;i:30;}', 'cron.halfhour.php', 1, 'halfhour', 'ilance'),
(1053532560, -1, -1, -1, 'a:1:{i:0;i:60;}', 'cron.hourly.php', 1, 'hourly', 'ilance'),
(1053271600, -1, -1,  0, 'a:1:{i:0;i:0;}',  'cron.daily.php', 1, 'daily', 'ilance')
";
$queries['202'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `hasimage` INT(1) NOT NULL DEFAULT '0' AFTER `sellerfeedback`";
$queries['203'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `hasimageslideshow` INT(1) NOT NULL DEFAULT '0' AFTER `hasimage`";
$queries['204'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `hasdigitalfile` INT(1) NOT NULL DEFAULT '0' AFTER `hasimageslideshow`";
$queries['205'] = "CREATE TABLE " . DB_PREFIX . "product_questions_choices (
`optionid` INT(10) NOT NULL AUTO_INCREMENT,
`questionid` INT(5) NOT NULL DEFAULT '0',
`choice` MEDIUMTEXT,
`auctioncount` INT(5) NOT NULL DEFAULT '0',
`sort` INT(3) NOT NULL DEFAULT '0',
`visible` INT(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`optionid`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['206'] = "CREATE TABLE " . DB_PREFIX . "project_questions_choices (
`optionid` INT(10) NOT NULL AUTO_INCREMENT,
`questionid` INT(5) NOT NULL DEFAULT '0',
`choice` MEDIUMTEXT,
`auctioncount` INT(5) NOT NULL DEFAULT '0',
`sort` INT(3) NOT NULL DEFAULT '0',
`visible` INT(1) NOT NULL DEFAULT '1',
PRIMARY KEY (`optionid`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['207'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `items_in_lot` TINYINT NOT NULL DEFAULT '0' AFTER `buynow_qty_lot`";
$queries['208'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('post_request_whitelist', 'paypal.com moneybookers.com authorize.net cashu.com plugnpay.com', 'globalsecuritymime', 'textarea', '', '', '3', '1')";
$queries['209'] = "CREATE TABLE " . DB_PREFIX . "projects_skills_answers (
`aid` INT(5) NOT NULL AUTO_INCREMENT,
`cid` INT(5) NOT NULL,
`project_id` INT(10) NOT NULL,
PRIMARY KEY  (`aid`),
INDEX ( `cid` ),
INDEX ( `project_id` )
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['210'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_archivedays', '90', 'globalauctionsettings', 'int', '', '', '90', '1')";
$queries['211'] = "ALTER TABLE " . DB_PREFIX . "email ADD `buyer` INT(1) NOT NULL DEFAULT '0' AFTER `departmentid`";
$queries['212'] = "ALTER TABLE " . DB_PREFIX . "email ADD `seller` INT(1) NOT NULL DEFAULT '0' AFTER `buyer`";
$queries['213'] = "ALTER TABLE " . DB_PREFIX . "email ADD `admin` INT(1) NOT NULL DEFAULT '0' AFTER `seller`";
$queries['214'] = "CREATE TABLE " . DB_PREFIX . "deposit_offline_methods (
`id` INT(10) NOT NULL AUTO_INCREMENT,
`name` MEDIUMTEXT,
`number` MEDIUMTEXT,
`swift` MEDIUMTEXT,
`visible` INT(1) NOT NULL DEFAULT '1',
`sort` INT(3) NOT NULL DEFAULT '0',
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['215'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_minofflinedepositamount', '100', 'invoicesystem', 'int', '', '', '12', '1')";
$queries['216'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_maxofflinedepositamount', '1000', 'invoicesystem', 'int', '', '', '13', '1')";
$queries['217'] = "ALTER TABLE " . DB_PREFIX . "deposit_offline_methods ADD `company_name` MEDIUMTEXT NOT NULL AFTER `swift`";
$queries['218'] = "ALTER TABLE " . DB_PREFIX . "deposit_offline_methods ADD `company_address` MEDIUMTEXT NOT NULL AFTER `company_name`";
$queries['219'] = "ALTER TABLE " . DB_PREFIX . "deposit_offline_methods ADD `custom_notes` MEDIUMTEXT NOT NULL AFTER `company_address`";
$queries['220'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype = 'text' WHERE name = 'paypal_master_currency' LIMIT 1";
$queries['221'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype = 'text' WHERE name = 'stormpay_master_currency' LIMIT 1";
$queries['222'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype = 'text' WHERE name = 'cashu_master_currency' LIMIT 1";
$queries['223'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype = 'text' WHERE name = 'moneybookers_master_currency' LIMIT 1";
$queries['224'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype = 'text' WHERE name = 'platnosci_master_currency' LIMIT 1";
$queries['225'] = "ALTER TABLE " . DB_PREFIX . "register_questions ADD `roleid` MEDIUMTEXT NOT NULL AFTER `guests`";
$queries['226'] = "CREATE TABLE " . DB_PREFIX . "email_optout (
`id` INT(10) NOT NULL AUTO_INCREMENT,
`email` MEDIUMTEXT,
`varname` MEDIUMTEXT,
PRIMARY KEY (`id`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['227'] = "ALTER TABLE " . DB_PREFIX . "subscription_user ADD `autorenewal` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `autopayment`";
$queries['228'] = "UPDATE " . DB_PREFIX . "configuration SET inputtype = 'pulldown' WHERE name = 'registrationdisplay_defaultcountry' LIMIT 1";
$queries['229'] = "UPDATE " . DB_PREFIX . "configuration SET inputtype = 'pulldown' WHERE name = 'registrationdisplay_defaultstate' LIMIT 1";
$queries['230'] = "ALTER TABLE " . DB_PREFIX . "templates CHANGE `type` `type` ENUM('variable','cssclient','cssadmin','csswysiwyg','csstabs','csscommon','csscustom') NOT NULL DEFAULT 'variable'";
$queries['231'] = "ALTER TABLE " . DB_PREFIX . "cronlog ADD `time` FLOAT(10,2) NOT NULL default '0.00' AFTER `description`";
$queries['232'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_1` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_1`";
$queries['233'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_2` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_2`";
$queries['234'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_3` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_3`";
$queries['235'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_4` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_4`";
$queries['236'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_5` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_5`";
$queries['237'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_6` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_6`";
$queries['238'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_7` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_7`";
$queries['239'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_8` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_8`";
$queries['240'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_9` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_9`";
$queries['241'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_fee_next_10` FLOAT(10,2) NOT NULL default '0.00' AFTER `ship_fee_10`";
$queries['242'] = "UPDATE " . DB_PREFIX . "configuration SET value = '2' WHERE name = 'portfoliodisplay_thumbsperrow'";
$queries['243'] = "ALTER TABLE " . DB_PREFIX . "configuration_groups DROP `description` , DROP `help`";
$queries['244'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_deletearchivedlistings', '0', 'globalauctionsettings', 'yesno', '', '', '150', '1')";
$queries['245'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `keywords` MEDIUMTEXT NOT NULL AFTER `attributes`";
$queries['246'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `project_type` MEDIUMTEXT NOT NULL AFTER `keywords`";
$queries['247'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `project_state` MEDIUMTEXT NOT NULL AFTER `project_type`";
$queries['248'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachment_forceproductupload', '1', 'attachmentlimit_productphotosettings', 'yesno',  '', '', '41', '1');";
$queries['249'] = "ALTER TABLE " . DB_PREFIX . "product_answers ADD `optionid` INT(5) NOT NULL DEFAULT '0' AFTER `answer`";
$queries['250'] = "ALTER TABLE " . DB_PREFIX . "project_answers ADD `optionid` INT(5) NOT NULL DEFAULT '0' AFTER `answer`";
$queries['251'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('automation_removewatchlist', '1', 'globalauctionsettings', 'yesno',  '', '', '41', '1');";
$queries['252'] = "CREATE TABLE `" . DB_PREFIX . "locations_regions` (
`regionid` INT(100) NOT NULL AUTO_INCREMENT,
`region_eng` VARCHAR(150) NOT NULL default '',
PRIMARY KEY  (`regionid`),
KEY location (`region_eng`),
INDEX (`region_eng`),
INDEX (`regionid`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['253'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (1, 'Africa')";
$queries['254'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (2, 'Antarctica')";
$queries['255'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (3, 'Asia')";
$queries['256'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (4, 'Europe')";
$queries['257'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (5, 'North America')";
$queries['258'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (6, 'Oceania')";
$queries['259'] = "INSERT INTO `" . DB_PREFIX . "locations_regions` VALUES (7, 'South America')";
$queries['260'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '1' WHERE region = 'Africa'";
$queries['261'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '2' WHERE region = 'Antarctica'";
$queries['262'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '3' WHERE region = 'Asia'";
$queries['263'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '4' WHERE region = 'Europe'";
$queries['264'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '5' WHERE region = 'North America'";
$queries['265'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '6' WHERE region = 'Oceania'";
$queries['266'] = "UPDATE `" . DB_PREFIX . "locations` SET region = '7' WHERE region = 'South America'";
$queries['267'] = "ALTER TABLE `" . DB_PREFIX . "locations` CHANGE `region` `regionid` INT( 100 ) NOT NULL DEFAULT '0'";
$queries['268'] = "ALTER TABLE " . DB_PREFIX . "search ADD `cid` INT(5) NOT NULL DEFAULT '0' AFTER `searchmode`";
$queries['269'] = "ALTER TABLE " . DB_PREFIX . "search_users ADD `cid` INT(5) NOT NULL DEFAULT '0' AFTER `user_id`";
$queries['270'] = "ALTER TABLE " . DB_PREFIX . "skills ADD `rootcid` INT(5) NOT NULL DEFAULT '0' AFTER `level`";
$queries['271'] = "ALTER TABLE `" . DB_PREFIX . "subscription` CHANGE `title` `title_eng` VARCHAR( 100 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['272'] = "ALTER TABLE `" . DB_PREFIX . "subscription` CHANGE `description` `description_eng` VARCHAR( 250 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['273'] = "ALTER TABLE `" . DB_PREFIX . "subscription_group` CHANGE `title` `title_eng` VARCHAR( 100 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['274'] = "ALTER TABLE `" . DB_PREFIX . "subscription_group` CHANGE `description` `description_eng` VARCHAR( 250 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['275'] = "ALTER TABLE `" . DB_PREFIX . "subscription_roles` CHANGE `purpose` `purpose_eng` VARCHAR( 250 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['276'] = "ALTER TABLE `" . DB_PREFIX . "subscription_roles` CHANGE `title` `title_eng` VARCHAR( 250 ) CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['277'] = "UPDATE `" . DB_PREFIX . "configuration` SET `inputtype` = 'text' WHERE `name` = 'servicecatschema'";
$queries['278'] = "UPDATE `" . DB_PREFIX . "configuration` SET `inputtype` = 'text' WHERE `name` = 'productcatschema'";
$queries['279'] = "UPDATE `" . DB_PREFIX . "configuration` SET `inputtype` = 'text' WHERE `name` = 'servicelistingschema'";
$queries['280'] = "UPDATE `" . DB_PREFIX . "configuration` SET `inputtype` = 'text' WHERE `name` = 'productlistingschema'";
$queries['281'] = "ALTER TABLE `" . DB_PREFIX . "subscription` ADD `sort` INT(8) NOT NULL DEFAULT '0' AFTER `icon`";
$queries['282'] = "ALTER TABLE `" . DB_PREFIX . "categories` CHANGE `keywords` `keywords_eng` MEDIUMTEXT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . " NOT NULL DEFAULT ''";
$queries['283'] = "ALTER TABLE `" . DB_PREFIX . "users` ADD `posthtml` INT(1) NOT NULL DEFAULT '0' AFTER `autopayment`";
$queries['284'] = "INSERT INTO `" . DB_PREFIX . "subscription_permissions` VALUES (NULL, 1, 'posthtml', 'yesno', 'no', 0, 1, 0, 1)";
$queries['285'] = "ALTER TABLE `" . DB_PREFIX . "project_questions` ADD `guests` INT(1) NOT NULL DEFAULT '1' AFTER `recursive`";
$queries['286'] = "ALTER TABLE `" . DB_PREFIX . "product_questions` ADD `guests` INT(1) NOT NULL DEFAULT '1' AFTER `recursive`";
$queries['287'] = "ALTER TABLE `" . DB_PREFIX . "profile_questions` ADD `guests` INT(1) NOT NULL DEFAULT '0' AFTER `filtercategory`";
$queries['288'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalfilters', 'globalfilterspsp', '370')";
$queries['289'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_emailfilterpsp', '1', 'globalfilterspsp', 'yesno', '', '', '1', '1')";
$queries['290'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_domainfilterpsp', '1', 'globalfilterspsp', 'yesno', '', '', '2', '1')";
$queries['291'] = "ALTER TABLE `" . DB_PREFIX . "projects` ADD `filter_bidlimit` INT(1) NOT NULL DEFAULT '0' AFTER `filtered_zip`";
$queries['292'] = "ALTER TABLE `" . DB_PREFIX . "projects` ADD `filtered_bidlimit` INT(10) NOT NULL DEFAULT '0' AFTER `filter_bidlimit`";
$queries['293'] = "ALTER TABLE `" . DB_PREFIX . "emaillog` CHANGE `date` `date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'";
$queries['294'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE `paymethod` `paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','paypal_pro','check','purchaseorder','stormpay','cashu','moneybookers','external') NOT NULL DEFAULT 'account'";
$queries['295'] = "ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `paymethod` `paymethod` ENUM('account','bank','visa','amex','mc','disc','paypal','paypal_pro','check','stormpay','cashu','moneybookers') NOT NULL DEFAULT 'account'";
$queries['296'] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('paypal_pro','paypal_pro','Paypal Payments Pro Gateway Configuration','','gateway')";
$queries['297'] = "UPDATE " . DB_PREFIX . "payment_configuration SET `inputcode` = '" . $ilance->db->escape_string('<select name=\"config[use_internal_gateway]\" style=\"font-family: verdana\"><option value=\"authnet\">Authorize.Net</option><option value=\"bluepay\">BluePay</option><option value=\"plug_n_pay\">PlugNPay</option><option value=\"psigate\">PSIGate</option><option value=\"eway\">eWAY</option><option value=\"paypal_pro\">Paypal Payments Pro</option><option value=\"none\" selected=\"selected\">Disable Credit Card Support</option></select>') . "' WHERE name='use_internal_gateway'";
$queries['298'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Paypal Payments Pro', 'paypal_pro', 'text', '', '', '', 10, 1)";
$queries['299'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_username', 'Enter your Paypal Payments Pro username', '', 'paypal_pro', 'text', '', '', '', 20, 1)";
$queries['300'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_password', 'Enter your Paypal Payments Pro password', '', 'paypal_pro', 'pass', '', '', '', 30, 1)";
$queries['301'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_signature', 'Enter your Paypal Payments Pro signature', '', 'paypal_pro', 'text', '', '', '', 40, 1)";
$queries['302'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_master_currency', 'Enter the currency used in Paypal Payments Pro transactions', 'USD', 'paypal_pro', 'text', '', '', '', 50, 1)";
$queries['303'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_transaction_fee', 'Enter transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'paypal_pro', 'int', '', '', '', 60, 1)";
$queries['304'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_transaction_fee2', 'Enter transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'paypal_pro', 'int', '', '', '', 70, 1)";
$queries['305'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_currency', 'Enter available currency in Paypal Payments Pro transactions', 'CAD|EUR|GBP|USD|JPY|AUD|NZD|CHF|HKD|SGD|SEK|DKK|PLN|NOK|HUF|CZK|ILS|MXN|BRL|MYR|PHP|TWD|THB', 'paypal_pro', 'textarea', '', '', '', 80, 1)";
$queries['306'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_directpayment', 'Allow buyers to directly pay the seller (admins only) through this gateway?', '0', 'paypal_pro', 'yesno', '', '', '', 90, 1)";
$queries['307'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_sandbox', 'Enable Paypal Payments Pro Sandbox testing environment?', '0', 'paypal_pro', 'yesno', '', '', '', 100, 1)";
$queries['308'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `filter_ccgateway` INT(1) NOT NULL default '0' AFTER `filter_gateway`";
$queries['309'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `paymethodcc` MEDIUMTEXT AFTER `paymethod`";
$queries['310'] = "ALTER TABLE " . DB_PREFIX . "email CHANGE `name` `name_eng` VARCHAR( 255 ) NOT NULL DEFAULT ''";
$queries['311'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_slideshowimages', '1', 'globalfiltersrfp', 'yesno',  '', '', '11', '1')";
$queries['312'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_rowsslideshowimages', '1', 'globalfiltersrfp', 'int',  '', '', '12', '1')";
$queries['313'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_candeposit', 'Allow members to deposit funds using this gateway?', '0', 'paypal_pro', 'yesno', '', '', '', 110, 1)";
$queries['314'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_pro_subscriptions', 'Enable Paypal Payments Pro Recurring Subscriptions? (used in subscription menu)', '0', 'paypal_pro', 'yesno', '', '', '', 120, 1)";
$queries['315'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `returnwithin`  `returnwithin` ENUM(  '0',  '3',  '7',  '14',  '30',  '60' ) NOT NULL DEFAULT  '0'";
$queries['316'] = "INSERT INTO " . DB_PREFIX . "cron (nextrun, weekday, day, hour, minute, filename, loglevel, varname, product) VALUES (1053532560, -1, -1, 0, 'a:1:{i:0;i:0;}', 'cron.sitemap.php', 1, 'sitemap', 'ilance')";
$queries['317'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `returnwithin`  `returnwithin` ENUM(  '0',  '3',  '7',  '14',  '30',  '90' ) NOT NULL DEFAULT  '0'";
$queries['318'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `returnwithin`  `returnwithin` ENUM(  '0',  '3',  '7',  '14',  '30',  '90' ) NOT NULL DEFAULT  '0'";
$queries['319'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `returnwithin`  `returnwithin` ENUM(  '0',  '3',  '7',  '14',  '30',  '60', '90' ) NOT NULL DEFAULT  '0'";
$queries['320'] = "ALTER TABLE " . DB_PREFIX . "bid_fields CHANGE `question_eng` `question_eng` MEDIUMTEXT NOT NULL";
$queries['321'] = "ALTER TABLE " . DB_PREFIX . "bid_fields CHANGE `description_eng` `description_eng` MEDIUMTEXT NOT NULL";
$queries['322'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `buynoworderid` INT(5) NOT NULL DEFAULT '0' AFTER `project_id`";
$queries['323'] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD `buynoworderid` INT(5) NOT NULL DEFAULT '0' AFTER `project_id`";
$queries['324'] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD `buynoworderid` INT(5) NOT NULL DEFAULT '0' AFTER `project_id`";
$queries['325'] = "ALTER TABLE " . DB_PREFIX . "phrases CHANGE `text_original` `text_original` MEDIUMTEXT NOT NULL";
$queries['326'] = "ALTER TABLE " . DB_PREFIX . "language_phrases CHANGE `text_eng` `text_eng` MEDIUMTEXT NOT NULL";
$queries['327'] = "INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_configuration_groups', 'Admincp Configuration Groups', 'ilance')";
$queries['328'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `releasedate` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `paiddate`";
$queries['329'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `countdownresets` INT(5) NOT NULL DEFAULT '0' AFTER `modelnumber`";
$queries['330'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_countdownresets', '5', 'productbid_limits', 'int', '', '', '4', '1')";
$queries['331'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `featuredonsearchresults` INT( 1 ) NOT NULL DEFAULT '0' AFTER `featured_date`";
$queries['332'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('digitaldownload', '1', 'shippingsettings', 'yesno', '', '', '1920', '1')";
$queries['333'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('footercronjob', '1', 'globalfilterresults', 'yesno', '' , '', '415', '1')";
$queries['334'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotowidth', '400', 'attachmentlimit_productphotosettings', 'int', '', '', '50', '1')";
$queries['335'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotoheight', '400', 'attachmentlimit_productphotosettings', 'int', '', '', '60', '1')";
$queries['336'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotothumbwidth', '32', 'attachmentlimit_productphotosettings', 'int', '', '', '70', '1')";
$queries['337'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_productphotothumbheight', '24', 'attachmentlimit_productphotosettings', 'int', '', '', '80', '1')";
$queries['338'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsactive', '1', 'productupsell_featured_searchresults', 'yesno', '', '', '1', '1')";
$queries['339'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsfees', '1', 'productupsell_featured_searchresults', 'yesno', '', '', '2', '1')";
$queries['340'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_featured_searchresultsfee', '2.75', 'productupsell_featured_searchresults', 'int', '', '', '3', '1')";
$queries['341'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsactive', '1', 'serviceupsell_featured_searchresults', 'yesno', '', '', '1', '1')";
$queries['342'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsfees', '1', 'serviceupsell_featured_searchresults', 'yesno', '', '', '1', '1')";
$queries['343'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('serviceupsell_featured_searchresultsfee', '2.75', 'serviceupsell_featured_searchresults', 'int', '', '', '2', '1')";
$queries['344'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured_searchresults', '90')";
$queries['345'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productupsell', 'productupsell_featured_searchresults', '90')";
$queries['346'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `featuredonsearchresults` `featured_searchresults` INT( 1 ) NOT NULL DEFAULT '0'";
$queries['347'] = "ALTER TABLE " . DB_PREFIX . "attachment CHANGE `attachtype` `attachtype` ENUM('profile','portfolio','project','itemphoto','bid','pmb','ws','kb','ads','digital','slideshow','stores','storesitemphoto','storesdigital','storesbackground') NOT NULL DEFAULT 'profile'";
$queries['348'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `cid` INT(10) NOT NULL DEFAULT '0' AFTER `type`";
$queries['349'] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD `cid` INT(10) NOT NULL DEFAULT '0' AFTER `rating`";
$queries['350'] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD `cid` INT(10) NOT NULL DEFAULT '0' AFTER `type`";
$queries['351'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `cattype` ENUM('','service','product') NOT NULL DEFAULT '' AFTER `cid`";
$queries['352'] = "ALTER TABLE " . DB_PREFIX . "feedback_ratings ADD `cattype` ENUM('','service','product') NOT NULL DEFAULT '' AFTER `cid`";
$queries['353'] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD `cattype` ENUM('','service','product') NOT NULL DEFAULT '' AFTER `cid`";
$queries['354'] = "ALTER TABLE " . DB_PREFIX . "search ADD `visible` INT(1) NOT NULL DEFAULT '0' AFTER `cid`";
$queries['355'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `description_html` MEDIUMTEXT AFTER `description`";
$queries['356'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `bulkid` INT(5) NOT NULL DEFAULT '0' AFTER `countdownresets`";
$queries['357'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableclassifiedtab', '0', 'globalauctionsettings', 'yesno', '', '', '150', '1')";
$queries['358'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productupsell_classifiedcost', '0', 'productupsell_fees', 'int', '', '', '1', '1')";
$queries['359'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `filtered_auctiontype` `filtered_auctiontype` ENUM('regular','fixed','classified') NOT NULL DEFAULT 'regular'";
$queries['360'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `classified_phone` VARCHAR(32) NOT NULL default '' AFTER `filtered_auctiontype`";
$queries['361'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `classified_price` FLOAT(10,2) NOT NULL DEFAULT '0.00' AFTER `classified_phone`";
$queries['362'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `urgent` INT(1) NOT NULL DEFAULT '0' AFTER `classified_price`";
$queries['363'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productbid_displaybidname', '1', 'productbid_limits', 'yesno', '', '', '21', '1')";
$queries['364'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_catanswerdepth', '5', 'globalcategorysettings', 'int', '', '', '150', '1')";
$queries['365'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enableoffsitedepositpayment', '1', 'invoicesystem', 'yesno', '', '', '25', '1')";
$queries['366'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_enableoffsitepaymenttypes', '1', 'invoicesystem', 'yesno', '', '', '30', '1')";
$queries['367'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `bulk_id` INT NOT NULL DEFAULT '0' AFTER `pmb_id`";
$queries['368'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, '1', 'bulkattachlimit', 'int', '10000000', '0', '1', '0', '1')";
$queries['369'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_publicboards', '1', 'search', 'yesno', '', '', '50', '1')";
$queries['370'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_images', '1', 'search', 'yesno', '', '', '60', '1')";
$queries['371'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_noimages', '1', 'search', 'yesno', '', '', '70', '1')";
$queries['372'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_freeship', '1', 'search', 'yesno', '', '', '80', '1')";
$queries['373'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_lots', '1', 'search', 'yesno', '', '', '90', '1')";
$queries['374'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_escrow', '1', 'search', 'yesno', '', '', '100', '1')";
$queries['375'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_donation', '1', 'search', 'yesno', '', '', '110', '1')";
$queries['376'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_product_completed', '1', 'search', 'yesno', '', '', '120', '1')";
$queries['377'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_public', '1', 'search', 'yesno', '', '', '130', '1')";
$queries['378'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_escrow', '1', 'search', 'yesno', '', '', '140', '1')";
$queries['379'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_nondisclosed', '1', 'search', 'yesno', '', '', '150', '1')";
$queries['380'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('search_work_completed', '1', 'search', 'yesno', '', '', '160', '1')";
$queries['381'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('registration_allow_special', '0', 'registrationdisplay', 'yesno', '', '', '110', '1')";
$queries['382'] = "ALTER TABLE " . DB_PREFIX . "deposit_offline_methods ADD `fee` FLOAT(10,2) NOT NULL DEFAULT '0.00' AFTER `custom_notes`";
$queries['383'] = "CREATE TABLE " . DB_PREFIX . "attachment_color (
`colorid` INT(10) NOT NULL AUTO_INCREMENT,
`attachid` INT(7) NOT NULL default '0',
`project_id` INT(10) NOT NULL default '0',
`color` VARCHAR(7) NOT NULL default '',
`count` INT(5) NOT NULL default '0',
`relativecolor` VARCHAR(7) NOT NULL default '',
`relativetitle` VARCHAR(100) NOT NULL default '',
`relativefont` VARCHAR(100) NOT NULL default '',
PRIMARY KEY  (`colorid`),
INDEX (`attachid`),
INDEX (`project_id`),
INDEX (`relativefont`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['384'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `color` INT(1) NOT NULL DEFAULT '0' AFTER `isexternal`";
$queries['385'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `amount`  `amount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['386'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `escrowfee`  `escrowfee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['387'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `escrowfeebuyer`  `escrowfeebuyer` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['388'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `fvf`  `fvf` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['389'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `fvfbuyer`  `fvfbuyer` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['390'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders CHANGE  `buyershipcost`  `buyershipcost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['391'] = "ALTER TABLE " . DB_PREFIX . "categories CHANGE  `fixedfeeamount`  `fixedfeeamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['392'] = "ALTER TABLE " . DB_PREFIX . "categories CHANGE  `nondisclosefeeamount`  `nondisclosefeeamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['393'] = "ALTER TABLE " . DB_PREFIX . "charities CHANGE  `earnings`  `earnings` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['394'] = "ALTER TABLE " . DB_PREFIX . "creditcards CHANGE  `auth_amount1`  `auth_amount1` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['395'] = "ALTER TABLE " . DB_PREFIX . "creditcards CHANGE  `auth_amount2`  `auth_amount2` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['396'] = "ALTER TABLE " . DB_PREFIX . "deposit_offline_methods CHANGE  `fee`  `fee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['397'] = "ALTER TABLE " . DB_PREFIX . "finalvalue CHANGE  `finalvalue_from`  `finalvalue_from` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['398'] = "ALTER TABLE " . DB_PREFIX . "finalvalue CHANGE  `finalvalue_to`  `finalvalue_to` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['399'] = "ALTER TABLE " . DB_PREFIX . "finalvalue CHANGE  `amountfixed`  `amountfixed` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['400'] = "ALTER TABLE " . DB_PREFIX . "increments CHANGE  `increment_from`  `increment_from` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['401'] = "ALTER TABLE " . DB_PREFIX . "increments CHANGE  `increment_to`  `increment_to` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['402'] = "ALTER TABLE " . DB_PREFIX . "increments CHANGE  `amount`  `amount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['403'] = "ALTER TABLE " . DB_PREFIX . "insertion_fees CHANGE  `insertion_from`  `insertion_from` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['404'] = "ALTER TABLE " . DB_PREFIX . "insertion_fees CHANGE  `insertion_to`  `insertion_to` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['405'] = "ALTER TABLE " . DB_PREFIX . "insertion_fees CHANGE  `amount`  `amount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['406'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `amount`  `amount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['407'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `paid`  `paid` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['408'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `totalamount`  `totalamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['409'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `taxamount`  `taxamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['410'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `depositcreditamount`  `depositcreditamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['411'] = "ALTER TABLE " . DB_PREFIX . "invoices CHANGE  `withdrawdebitamount`  `withdrawdebitamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['412'] = "ALTER TABLE " . DB_PREFIX . "profile_questions CHANGE  `verifycost`  `verifycost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['413'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `classified_price`  `classified_price` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['414'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `buynow_price`  `buynow_price` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['415'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `reserve_price`  `reserve_price` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['416'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `startprice`  `startprice` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['417'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `retailprice`  `retailprice` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['418'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `currentprice`  `currentprice` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['419'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `insertionfee`  `insertionfee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['420'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `enhancementfee`  `enhancementfee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['421'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `fvf`  `fvf` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['422'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `escrowamount`  `escrowamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['423'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `bidamount`  `bidamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['424'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `shipping`  `shipping` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['425'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `total`  `total` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['426'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `fee`  `fee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['427'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow CHANGE  `fee2`  `fee2` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['428'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping CHANGE  `ship_handlingfee`  `ship_handlingfee` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['429'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_1`  `ship_fee_1` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['430'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_1`  `ship_fee_next_1` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['431'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_1`  `ship_additionalfee_1` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['432'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_2`  `ship_fee_2` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['433'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_2`  `ship_fee_next_2` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['434'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_2`  `ship_additionalfee_2` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['435'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_3`  `ship_fee_3` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['436'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_3`  `ship_fee_next_3` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['437'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_3`  `ship_additionalfee_3` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['438'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_4`  `ship_fee_4` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['439'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_4`  `ship_fee_next_4` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['440'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_4`  `ship_additionalfee_4` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['441'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_5`  `ship_fee_5` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['442'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_5`  `ship_fee_next_5` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['443'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_5`  `ship_additionalfee_5` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['444'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_6`  `ship_fee_6` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['445'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_6`  `ship_fee_next_6` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['446'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_6`  `ship_additionalfee_6` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['447'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_7`  `ship_fee_7` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['448'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_7`  `ship_fee_next_7` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['449'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_7`  `ship_additionalfee_7` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['450'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_8`  `ship_fee_8` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['451'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_8`  `ship_fee_next_8` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['452'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_8`  `ship_additionalfee_8` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['453'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_9`  `ship_fee_9` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['454'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_9`  `ship_fee_next_9` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['455'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_9`  `ship_additionalfee_9` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['456'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_10`  `ship_fee_10` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['457'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_fee_next_10`  `ship_fee_next_10` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['458'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations CHANGE  `ship_additionalfee_10`  `ship_additionalfee_10` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['459'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids CHANGE  `buyershipcost`  `buyershipcost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['460'] = "ALTER TABLE " . DB_PREFIX . "project_bids CHANGE  `bidamount`  `bidamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['461'] = "ALTER TABLE " . DB_PREFIX . "project_bids CHANGE  `fvf`  `fvf` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['462'] = "ALTER TABLE " . DB_PREFIX . "project_bids CHANGE  `buyershipcost`  `buyershipcost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['463'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids CHANGE  `bidamount`  `bidamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['464'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids CHANGE  `fvf`  `fvf` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['465'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids CHANGE  `buyershipcost`  `buyershipcost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['466'] = "ALTER TABLE " . DB_PREFIX . "proxybid CHANGE  `maxamount`  `maxamount` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['467'] = "ALTER TABLE " . DB_PREFIX . "subscription CHANGE  `cost`  `cost` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['468'] = "ALTER TABLE " . DB_PREFIX . "users CHANGE  `available_balance`  `available_balance` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['469'] = "ALTER TABLE " . DB_PREFIX . "users CHANGE  `total_balance`  `total_balance` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['470'] = "ALTER TABLE " . DB_PREFIX . "users CHANGE  `income_reported`  `income_reported` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['471'] = "ALTER TABLE " . DB_PREFIX . "users CHANGE  `income_spent`  `income_spent` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['472'] = "ALTER TABLE " . DB_PREFIX . "users CHANGE  `rateperhour`  `rateperhour` DOUBLE( 10, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['473'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_name', 'Owner bank name', '', 'owner_bank_info', 'textarea', '', '', '', 10, 1)";
$queries['474'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_account_number', 'Owner bank account number', '', 'owner_bank_info', 'textarea', '', '', '', 20, 1)";
$queries['475'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'owner_bank_swift', 'Owner bank Swift', '', 'owner_bank_info', 'textarea', '', '', '', 30, 1)";
$queries['476'] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('owner_bank_info', 'owner_bank_info', 'Owner Bank Configuration', '', '')";
$queries['477'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalauctionsettings_showflashcountdown' LIMIT 1";
$queries['478'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('trend_tab', '1', 'globaltabvisibility', 'yesno', '', '', '10', '1')";
$queries['479'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globaltabvisibility', 'globaltabvisibility', '520')";
$queries['480'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('didyoumeancorrection', '1', 'search', 'yesno', '', '', '45', '1')";
$queries['481'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `parentid` INT(5) NOT NULL DEFAULT '0' AFTER `orderid`";
$queries['482'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, 1, 'servicefvfgroup', 'int', '0', 0, 1, 0, 1)";
$queries['483'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, 1, 'productfvfgroup', 'int', '0', 0, 1, 0, 1)";
$queries['484'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, 1, 'serviceinsgroup', 'int', '0', 0, 1, 0, 1)";
$queries['485'] = "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, 1, 'productinsgroup', 'int', '0', 0, 1, 0, 1)";
$queries['486'] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('googlecheckout', 'googlecheckout', 'Google Checkout IPN Gateway Configuration', '', 'ipn')";
$queries['487'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Google Checkout', 'googlecheckout', 'text', NULL, '', '', 0, 1)";
$queries['488'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'gc_merchant_id', 'Enter the merchant id', '', 'googlecheckout', 'text', NULL, '', '', 2, 1)";
$queries['489'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'gc_merchant_key', 'Enter Merchant Key', '', 'googlecheckout', 'text', NULL, '', '', 3, 1)";
$queries['490'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'gc_default_currency', 'Enter the currency used in Google Checkout Transactions', 'USD', 'googlecheckout', 'text', NULL, '', '', 4, 1)";
$queries['491'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'gc_enable', 'Enable Google Checkout', '1', 'googlecheckout', 'yesno', NULL, '', '', 6, 1)";
$queries['492'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'gc_available_currency', 'Enter available currency in Google Checkout Transactions', 'USD|GBP|EUR|CAD', 'googlecheckout', 'textarea', NULL, '', '', 5, 1)";
$queries['493'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'googlecheckout_active', 'Allow members to deposit funds using this gateway?', '1', 'googlecheckout', 'yesno', NULL, '', '', 7, 1)";
$queries['494'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'googlecheckout_sandbox', 'Enable Google Checkout Sandbox testing environment?', '1', 'googlecheckout', 'yesno', NULL, '', '', 8, 1)";
$queries['495'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'googlecheckout_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'googlecheckout', 'int', NULL, '', '', 0, 1)";
$queries['496'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'googlecheckout_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.35', 'googlecheckout', 'int', NULL, '', '', 0, 1)";
$queries['497'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `shiptracknumber` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sellermarkedasshippeddate`";
$queries['498'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD `shiptracknumber` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sellermarkedasshippeddate`";
$queries['499'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `shiptracknumber` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sellermarkedasshippeddate`";
$queries['500'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `shiptracknumber` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sellermarkedasshippeddate`";
$queries['501'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `shiptracknumber` VARCHAR(255) NOT NULL DEFAULT '' AFTER `sellermarkedasshippeddate`";
$queries['502'] = "UPDATE " . DB_PREFIX . "subscription_permissions SET value = '-1' WHERE accessname = 'bulkattachlimit'";
$queries['503'] = "ALTER TABLE " . DB_PREFIX . "search_users ADD `uservisible` INT(1) NOT NULL DEFAULT '1' AFTER `added`";
$queries['504'] = "ALTER TABLE " . DB_PREFIX . "search_users ADD `project_id` INT(10) NOT NULL DEFAULT '0' AFTER `user_id`";
$queries['505'] = "ALTER TABLE " . DB_PREFIX . "search_users ADD `ipaddress` VARCHAR(100) NOT NULL DEFAULT '' AFTER `added`";
$queries['506'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('legend_tab', '1', 'globaltabvisibility', 'yesno', '', '', '20', '1')";
$queries['507'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('popular_tab', '1', 'globaltabvisibility', 'yesno', '', '', '30', '1')";
$queries['508'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('keywords_tab', '1', 'globaltabvisibility', 'yesno', '', '', '40', '1')";
$queries['509'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('lowestpricecombined', '0', 'globaltabvisibility', 'yesno', '', '', '50', '1')";
$queries['510'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('timeleftblocks', '1', 'globaltabvisibility', 'yesno', '', '', '60', '1')";
$queries['511'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('categoryboxorder', '1', 'globalcategorysettings', 'yesno', '', '', '1900', '1')";
$queries['512'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width` INT(5) NOT NULL DEFAULT '0' AFTER `filetype`";
$queries['513'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height` INT(5) NOT NULL DEFAULT '0' AFTER `width`";
$queries['514'] = "ALTER TABLE " . DB_PREFIX . "attachment DROP `thumbnail_filedata`";
$queries['515'] = "ALTER TABLE " . DB_PREFIX . "attachment DROP `thumbnail_filesize`";
$queries['516'] = "ALTER TABLE " . DB_PREFIX . "attachment DROP `thumbnail_date`";
$queries['517'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `lastpost` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `canpost`";
$queries['518'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('publicfacing', '1', 'globalauctionsettings', 'yesno', '', '', '160', '1')";
$queries['519'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `salestaxstate` VARCHAR(250) NOT NULL DEFAULT '' AFTER `modelnumber`";
$queries['520'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `salestaxrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `salestaxstate`";
$queries['521'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `salestaxshipping` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['522'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `title` VARCHAR(250) NOT NULL DEFAULT '' AFTER `project_id`"; // needed when listings get removed to show on feedback page
$queries['523'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `finalprice` DOUBLE(10,2) NOT NULL default '0.00' AFTER `title`"; // needed when listings get removed to show on feedback page
$queries['524'] = "ALTER TABLE " . DB_PREFIX . "shippers ADD `trackurl` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `carrier`";
$queries['525'] = "UPDATE " . DB_PREFIX . "shippers SET trackurl = 'http://www.fedex.com/Tracking?action=track&tracknumbers=' WHERE carrier = 'fedex'";
$queries['526'] = "UPDATE " . DB_PREFIX . "shippers SET trackurl = 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=' WHERE carrier = 'ups'";
$queries['527'] = "UPDATE " . DB_PREFIX . "shippers SET trackurl = 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=' WHERE carrier = 'usps'";
$queries['528'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `salestax` DOUBLE(10,2) NOT NULL DEFAULT '0.00' AFTER `amount`";
$queries['529'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `salestax` DOUBLE(10,2) NOT NULL DEFAULT '0.00' AFTER `bidamount`";
$queries['530'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `salestax` DOUBLE(10,2) NOT NULL DEFAULT '0.00' AFTER `bidamount`";
$queries['531'] = "ALTER TABLE " . DB_PREFIX . "projects_escrow ADD `salestax` DOUBLE(10,2) NOT NULL DEFAULT '0.00' AFTER `bidamount`";
$queries['532'] = "ALTER TABLE " . DB_PREFIX . "sessions CHANGE `title` `title` MEDIUMTEXT NOT NULL DEFAULT ''";
$queries['533'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `salestax` DOUBLE(10,2) NOT NULL DEFAULT '0.00' AFTER `uniquebid`";
$queries['534'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `salestaxstate` VARCHAR(250) NOT NULL DEFAULT '' AFTER `salestax`";
$queries['535'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `salestaxrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `salestaxstate`";
$queries['536'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `salestaxshipping` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['537'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `salestaxstate` VARCHAR(250) NOT NULL DEFAULT '' AFTER `salestax`";
$queries['538'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `salestaxrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `salestaxstate`";
$queries['539'] = "ALTER TABLE " . DB_PREFIX . "project_bids ADD `salestaxshipping` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['540'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `salestaxstate` VARCHAR(250) NOT NULL DEFAULT '' AFTER `salestax`";
$queries['541'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `salestaxrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `salestaxstate`";
$queries['542'] = "ALTER TABLE " . DB_PREFIX . "projects_uniquebids ADD `salestaxshipping` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['543'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `salestaxstate` VARCHAR(250) NOT NULL DEFAULT '' AFTER `salestax`";
$queries['544'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `salestaxrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `salestaxstate`";
$queries['545'] = "ALTER TABLE " . DB_PREFIX . "project_realtimebids ADD `salestaxshipping` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['546'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationdays', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,GTC', 'globalfiltersrfp', 'text', '', '', '100', '1')";
$queries['547'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationhours', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30', 'globalfiltersrfp', 'text', '', '', '200', '1')";
$queries['548'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('durationminutes', '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30', 'globalfiltersrfp', 'text', '', '', '300', '1')";
$queries['549'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicesearchheadercolumns', '1', 'search', 'yesno', '', '', '170', '1')";
$queries['550'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('productsearchheadercolumns', '1', 'search', 'yesno', '', '', '180', '1')";
$queries['551'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('expertssearchheadercolumns', '1', 'search', 'yesno', '', '', '190', '1')";
$queries['552'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `from_username` VARCHAR(250) NOT NULL DEFAULT '' AFTER `from_user_id`"; // needed when listings get removed to show on feedback page
$queries['553'] = "ALTER TABLE " . DB_PREFIX . "feedback ADD `for_username` VARCHAR(250) NOT NULL DEFAULT '' AFTER `for_user_id`"; // needed when listings get removed to show on feedback page
$queries['554'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_original` LONGBLOB AFTER `filedata`";
$queries['555'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filetype_original` VARCHAR(50) NOT NULL DEFAULT '' AFTER `filetype`";
$queries['556'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_original` INT(5) NOT NULL DEFAULT '0' AFTER `width`";
$queries['557'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_original` INT(5) NOT NULL DEFAULT '0' AFTER `height`";
$queries['558'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_original` INT(10) NOT NULL DEFAULT '0' AFTER `filesize`";
$queries['559'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `watermarked` INT(1) NOT NULL DEFAULT '0' AFTER `color`";
$queries['560'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark', '0', 'attachmentsystem', 'yesno', '', '', '20', '1')";
$queries['561'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_text', '', 'attachmentsystem', 'text', '', '', '30', '1')";
$queries['562'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_image', '', 'attachmentsystem', 'text', '', '', '40', '1')";
$queries['563'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_textsize', '10', 'attachmentsystem', 'int', '', '', '50', '1')";
$queries['564'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_imageopacity', '80', 'attachmentsystem', 'int', '', '', '60', '1')";
$queries['565'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_textfont', 'in901xki.ttf', 'attachmentsystem', 'text', '', '', '70', '1')";
$queries['566'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_profiles', '0', 'attachmentsystem', 'yesno', '', '', '80', '1')";
$queries['567'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_portfolios', '0', 'attachmentsystem', 'yesno', '', '', '90', '1')";
$queries['568'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_itemphoto', '0', 'attachmentsystem', 'yesno', '', '', '100', '1')";
$queries['569'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('watermark_storesitemphoto', '0', 'attachmentsystem', 'yesno', '', '', '110', '1')";
$queries['570'] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD `from_username` VARCHAR(250) NOT NULL DEFAULT '' AFTER `from_user_id`"; // needed when listings get removed to show on feedback page
$queries['571'] = "ALTER TABLE " . DB_PREFIX . "feedback_response ADD `for_username` VARCHAR(250) NOT NULL DEFAULT '' AFTER `for_user_id`"; // needed when listings get removed to show on feedback page
$queries['572'] = "ALTER TABLE " . DB_PREFIX . "users ADD `username_history` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `posthtml`";
$queries['573'] = "ALTER TABLE " . DB_PREFIX . "users ADD `password_lastchanged` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `username_history`";
$queries['574'] = "ALTER TABLE " . DB_PREFIX . "users ADD `timezone` VARCHAR(250) NOT NULL default 'America/New_York' AFTER `currencyid`";
$queries['575'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_sitetimezone', 'America/Toronto', 'globalserverlocale', 'pulldown', '', 'timezones', '5', '1')";
$queries['576'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalserverlocale_officialtimezone' LIMIT 1";
$queries['577'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalserverlocale_officialtimezonedst' LIMIT 1";
$queries['578'] = "ALTER TABLE " . DB_PREFIX . "users DROP `timezoneid`";
$queries['579'] = "ALTER TABLE " . DB_PREFIX . "users DROP `timezone_dst`";
$queries['580'] = "DROP TABLE IF EXISTS " . DB_PREFIX . "timezones";
$queries['581'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `durationdays` MEDIUMTEXT NOT NULL AFTER `catimage`";
$queries['582'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `durationhours` MEDIUMTEXT NOT NULL AFTER `durationdays`";
$queries['583'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `durationminutes` MEDIUMTEXT NOT NULL AFTER `durationhours`";
$queries['584'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE  `description_html` `ishtml` ENUM('0','1') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'";
$queries['585'] = "UPDATE " . DB_PREFIX . "projects SET ishtml = '0'";
$queries['586'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'wysiwygsettings', '530')";
$queries['587'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_pmb_wysiwyg', 'bbeditor', 'wysiwygsettings', 'pulldown', '<select name=\'config[default_pmb_wysiwyg]\'><option value=\'bbeditor\' selected>BBeditor</option><option value=\'ckeditor\'>CKeditor</option></select>', '', '10', '1')";
$queries['588'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_listingdescriptiontoolbar', '" . $ilance->db->escape_string("{ name: 'document', items : [ 'Source','-','Save','NewPage','DocProps','Preview','Print','-','Templates' ] },
{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 
    'HiddenField' ] },
'/',
{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
'/',
{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
{ name: 'colors', items : [ 'TextColor','BGColor' ] },
{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }") . "', 'wysiwygsettings', 'textarea', '', '', '20', '1')";
$queries['589'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `gtc` INT(1) NOT NULL DEFAULT '0' AFTER `date_end`";
$queries['590'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `salestaxentirecountry` INT(1) NOT NULL DEFAULT '0' AFTER `salestaxrate`";
$queries['591'] = "DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'wysiwygsettings'";
$queries['592'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'default_pmb_wysiwyg'";
$queries['593'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'ckeditor_listingdescriptiontoolbar'";
$queries['594'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'pmb_wysiwygsettings', '530')";
$queries['595'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'listingdescription_wysiwygsettings', '540')";
$queries['596'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'proposal_wysiwygsettings', '550')";
$queries['597'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_pmb_wysiwyg', 'bbeditor', 'pmb_wysiwygsettings', 'pulldown', '<select name=\'config[default_pmb_wysiwyg]\'><option value=\'bbeditor\' selected>BBeditor</option><option value=\'ckeditor\'>CKeditor</option></select>', '', '10', '1')";
$queries['598'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_pmbtoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline'] }") . "', 'pmb_wysiwygsettings', 'textarea', '', '', '20', '1')";
$queries['599'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_listingdescriptiontoolbar', '" . $ilance->db->escape_string("{ name: 'document', items : [ 'Source','-','DocProps','Preview','Print','-','Templates' ] },
{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
'/',
{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
{ name: 'insert', items : [ 'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' ] },
'/',
{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
{ name: 'colors', items : [ 'TextColor','BGColor' ] },
{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] }") . "', 'listingdescription_wysiwygsettings', 'textarea', '', '', '20', '1')";
$queries['600'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_proposal_wysiwyg', 'bbeditor', 'proposal_wysiwygsettings', 'pulldown', '<select name=\'config[default_proposal_wysiwyg]\'><option value=\'bbeditor\' selected>BBeditor</option><option value=\'ckeditor\'>CKeditor</option></select>', '', '10', '1')";
$queries['601'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_proposaltoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline'] }") . "', 'proposal_wysiwygsettings', 'textarea', '', '', '20', '1')";
$queries['602'] = "ALTER TABLE " . DB_PREFIX . "pmb ADD `ishtml` ENUM('0', '1') NOT NULL DEFAULT '0' AFTER  `subject`";
$queries['603'] = "ALTER TABLE " . DB_PREFIX . "projects ADD `gtc_cancelled` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `gtc`";
$queries['604'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('oneleftribbonsearchresults', '0', 'search', 'yesno', '', '', '200', '1')";
$queries['605'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_full` LONGBLOB AFTER `filedata_original`";
$queries['606'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_mini` LONGBLOB AFTER `filedata_full`";
$queries['607'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_search` LONGBLOB AFTER `filedata_mini`";
$queries['608'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_gallery` LONGBLOB AFTER `filedata_search`";
$queries['609'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filedata_snapshot` LONGBLOB AFTER `filedata_gallery`";
$queries['610'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_full` INT(5) NOT NULL default '0' AFTER `width_original`";
$queries['611'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_mini` INT(5) NOT NULL default '0' AFTER `width_full`";
$queries['612'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_search` INT(5) NOT NULL default '0' AFTER `width_mini`";
$queries['613'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_gallery` INT(5) NOT NULL default '0' AFTER `width_search`";
$queries['614'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `width_snapshot` INT(5) NOT NULL default '0' AFTER `width_gallery`";
$queries['615'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_full` INT(5) NOT NULL default '0' AFTER `height_original`";
$queries['616'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_mini` INT(5) NOT NULL default '0' AFTER `height_full`";
$queries['617'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_search` INT(5) NOT NULL default '0' AFTER `height_mini`";
$queries['618'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_gallery` INT(5) NOT NULL default '0' AFTER `height_search`";
$queries['619'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `height_snapshot` INT(5) NOT NULL default '0' AFTER `height_gallery`";
$queries['620'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_full` INT(10) UNSIGNED NOT NULL default '0' AFTER `filesize_original`";
$queries['621'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_mini` INT(10) UNSIGNED NOT NULL default '0' AFTER `filesize_full`";
$queries['622'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_search` INT(10) UNSIGNED NOT NULL default '0' AFTER `filesize_mini`";
$queries['623'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_gallery` INT(10) UNSIGNED NOT NULL default '0' AFTER `filesize_search`";
$queries['624'] = "ALTER TABLE " . DB_PREFIX . "attachment ADD `filesize_snapshot` INT(10) UNSIGNED NOT NULL default '0' AFTER `filesize_gallery`";
$queries['625'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_vatregistrationoption', '', 'globalserversettings', 'yesno', '', '', '30', '1')";
$queries['626'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_dunsoption', '', 'globalserversettings', 'yesno', '', '', '40', '1')";
$queries['627'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_1` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_1`";
$queries['628'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_2` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_2`";
$queries['629'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_3` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_3`";
$queries['630'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_4` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_4`";
$queries['631'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_5` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_5`";
$queries['632'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_6` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_6`";
$queries['633'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_7` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_7`";
$queries['634'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_8` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_8`";
$queries['635'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_9` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_9`";
$queries['636'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_packagetype_10` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_service_10`";
$queries['637'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping DROP `ship_packagetype`";
$queries['638'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_1` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_1`";
$queries['639'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_2` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_2`";
$queries['640'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_3` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_3`";
$queries['641'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_4` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_4`";
$queries['642'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_5` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_5`";
$queries['643'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_6` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_6`";
$queries['644'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_7` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_7`";
$queries['645'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_8` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_8`";
$queries['646'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_9` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_9`";
$queries['647'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping_destinations ADD `ship_pickuptype_10` VARCHAR(250) NOT NULL DEFAULT '' AFTER `ship_packagetype_10`";
$queries['648'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `ounces` DOUBLE NOT NULL default '0' AFTER `weight`";
$queries['649'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `container` VARCHAR(250) NOT NULL default '' AFTER `ounces`";
$queries['650'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `size` VARCHAR(250) NOT NULL default '' AFTER `container`";
$queries['651'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `machinable` VARCHAR(250) NOT NULL default '' AFTER `size`";
$queries['652'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `weightunit` VARCHAR(20) NOT NULL default '' AFTER `weight`";
$queries['653'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `dimensionunit` VARCHAR(20) NOT NULL default '' AFTER `weightunit`";
$queries['654'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `length` INT(5) NOT NULL default '0' AFTER `machinable`";
$queries['655'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `width` INT(5) NOT NULL default '0' AFTER `length`";
$queries['656'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `height` INT(5) NOT NULL default '0' AFTER `width`";
$queries['657'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `pickuptype` VARCHAR(250) NOT NULL default '' AFTER `height`";
$queries['658'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `packagetype` VARCHAR(250) NOT NULL default '' AFTER `pickuptype`";
$queries['659'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `from_state` VARCHAR(250) NOT NULL default '' AFTER `from_country`";
$queries['660'] = "ALTER TABLE " . DB_PREFIX . "shipping_rates_cache ADD `to_state` VARCHAR(250) NOT NULL default '' AFTER `to_country`";
$queries['661'] = "TRUNCATE TABLE " . DB_PREFIX . "shippers";
$queries['662'] = "INSERT INTO " . DB_PREFIX . "shippers
(`shipperid`, `title`, `shipcode`, `domestic`, `international`, `carrier`, `trackurl`)
VALUES
(NULL, 'Priority Overnight', 'PRIORITYOVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Standard Overnight', 'STANDARDOVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'First Overnight', 'FIRSTOVERNIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '2 Day', 'FEDEX2DAY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Express Saver', 'FEDEXEXPRESSSAVER', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Ground', 'FEDEXGROUND', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Overnight Day Freight', 'FFEDEX1DAYFREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '2 Day Freight', 'FEDEX2DAYFREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, '3 Day Freight', 'FEDEX3DAYFREIGHT', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Home Delivery', 'GROUNDHOMEDELIVERY', 1, 0, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Economy', 'INTERNATIONALECONOMY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International First', 'INTERNATIONALFIRST', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'International Priority', 'INTERNATIONALPRIORITY', 0, 1, 'fedex', 'http://www.fedex.com/Tracking?action=track&tracknumbers='),
(NULL, 'Ground', '03', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, '3-Day Select', '12', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, '2nd Day Air', '02', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Next Day Air Saver', '13', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Next Day Air Early AM', '14', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Next Day Air', '01', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Worldwide Express', '07', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Worldwide Expedited', '08', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Standard', '11', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Next Day Air Saver', '13', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, '2nd Day Air Early AM', '59', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Worldwide Express Plus', '54', 1, 1, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Express Saver', '65', 1, 0, 'ups', 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums='),
(NULL, 'Express Mail', 'EXPRESS', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'First Class Mail', 'FIRST CLASS', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'Priority Mail', 'PRIORITY', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'Parcel Mail', 'PARCEL', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'Library Mail', 'LIBRARY', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'Bound Printed Matter', 'BPM', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum='),
(NULL, 'Media Mail', 'MEDIA', 1, 0, 'usps', 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=')";
$queries['663'] = "ALTER TABLE " . DB_PREFIX . "locations_states ADD `sc` VARCHAR(10) NOT NULL default '' AFTER `state`";
$queries['664'] = "CREATE TABLE " . DB_PREFIX . "distance_hu (
`ZIPCode` INT(5) NOT NULL,
`City` MEDIUMTEXT,
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['665'] = "UPDATE `" . DB_PREFIX . "configuration` SET `value` = 'D, M d, Y h:i A' WHERE `name` = 'globalserverlocale_globaltimeformat'";
$queries['666'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `sku` MEDIUMTEXT NOT NULL AFTER `attributes`";
$queries['667'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `upc` MEDIUMTEXT NOT NULL AFTER `sku`";
$queries['668'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `partnumber` MEDIUMTEXT NOT NULL AFTER `upc`";
$queries['669'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `modelnumber` MEDIUMTEXT NOT NULL AFTER `partnumber`";
$queries['670'] = "ALTER TABLE " . DB_PREFIX . "bulk_tmp ADD `ean` MEDIUMTEXT NOT NULL AFTER `modelnumber`";
$queries['671'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_listinginventory', '0', 'globalfilterresults', 'yesno', '', '', '416', '1')";
$queries['672'] = "ALTER TABLE `" . DB_PREFIX . "shippers` ADD `sort` INT NOT NULL DEFAULT '10' AFTER `trackurl`";
$queries['673'] = "CREATE TABLE " . DB_PREFIX . "feedback_import (
`id` INT(100) NOT NULL AUTO_INCREMENT,
`userid` INT(10) NOT NULL default '0',
`fb_ebay` INT(10) NOT NULL default '0',
`dv_ebay` datetime NOT NULL default '0000-00-00 00:00:00',
`id_ebay` mediumtext,
`fb_yahoo` INT(10) NOT NULL default '0',
`dv_yahoo` datetime NOT NULL default '0000-00-00 00:00:00',
`id_yahoo` mediumtext,
`fb_emarket` INT(10) NOT NULL default '0',
`dv_emarket` datetime NOT NULL default '0000-00-00 00:00:00',
`id_emarket` mediumtext,
`fb_bonanzle` INT(10) NOT NULL default '0',
`dv_bonanzle` datetime NOT NULL default '0000-00-00 00:00:00',
`id_bonanzle` mediumtext,
`fb_etsy` INT(10) NOT NULL default '0',
`dv_etsy` datetime NOT NULL default '0000-00-00 00:00:00',
`id_etsy` mediumtext,
`fb_ioffer` INT(10) NOT NULL default '0',
`dv_ioffer` datetime NOT NULL default '0000-00-00 00:00:00',
`id_ioffer` mediumtext,
`fb_overstock` INT(10) NOT NULL default '0',
`dv_overstock` datetime NOT NULL default '0000-00-00 00:00:00',
`id_overstock` mediumtext,
`fb_ricardo` INT(10) NOT NULL default '0',
`dv_ricardo` datetime NOT NULL default '0000-00-00 00:00:00',
`id_ricardo` mediumtext,
`fb_amazon` INT(10) NOT NULL default '0',
`dv_amazon` datetime NOT NULL default '0000-00-00 00:00:00',
`id_amazon` mediumtext,
`fb_ebid` INT(10) NOT NULL default '0',
`dv_ebid` datetime NOT NULL default '0000-00-00 00:00:00',
`id_ebid` mediumtext,
`fb_ebidus` INT(10) NOT NULL default '0',
`dv_ebidus` datetime NOT NULL default '0000-00-00 00:00:00',
`id_ebidus` mediumtext,
PRIMARY KEY  (`id`),
INDEX (`userid`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['674'] = "ALTER TABLE `" . DB_PREFIX . "buynow_orders` CHANGE  `amount`  `amount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['675'] = "ALTER TABLE `" . DB_PREFIX . "feedback` CHANGE  `finalprice`  `finalprice` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['676'] = "ALTER TABLE `" . DB_PREFIX . "invoices` CHANGE  `amount`  `amount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `paid`  `paid` DOUBLE( 17, 2 ) NULL DEFAULT  '0.00',
CHANGE  `totalamount`  `totalamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['677'] = "ALTER TABLE `" . DB_PREFIX . "projects` CHANGE  `buynow_price`  `buynow_price` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `reserve_price`  `reserve_price` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `startprice`  `startprice` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `classified_price`  `startprice` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `currentprice`  `currentprice` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['678'] = "ALTER TABLE `" . DB_PREFIX . "projects_escrow` CHANGE  `escrowamount`  `escrowamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `bidamount`  `bidamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `total`  `total` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['679'] = "ALTER TABLE `" . DB_PREFIX . "project_bids` CHANGE  `bidamount`  `bidamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['680'] = "ALTER TABLE `" . DB_PREFIX . "project_realtimebids` CHANGE  `bidamount`  `bidamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['681'] = "ALTER TABLE `" . DB_PREFIX . "proxybid` CHANGE  `maxamount`  `maxamount` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['682'] = "ALTER TABLE `" . DB_PREFIX . "users` CHANGE  `available_balance`  `available_balance` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `total_balance`  `total_balance` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `income_reported`  `income_reported` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00',
CHANGE  `income_spent`  `income_spent` DOUBLE( 17, 2 ) NOT NULL DEFAULT  '0.00'";
$queries['683'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('wysiwygsettings', 'profileintro_wysiwygsettings', '560')";
$queries['684'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('default_profileintro_wysiwyg', 'textarea', 'profileintro_wysiwygsettings', 'pulldown', '<select name=\'config[default_profileintro_wysiwyg]\'><option value=\'textarea\' selected>Textarea</option><option value=\'ckeditor\'>CKeditor</option></select>', '', '10', '1')";
$queries['685'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ckeditor_profileintrotoolbar', '" . $ilance->db->escape_string("{ name: 'basicstyles', items : [ 'Bold','Italic','Underline'] }") . "', 'profileintro_wysiwygsettings', 'textarea', '', '', '20', '1')";
$queries['686'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('productblocks', 'productblocks', '300')";
$queries['687'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_draft_block', '1', 'productblocks', 'yesno', '', '', '100', '1')";
$queries['688'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_invite_block', '1', 'productblocks', 'yesno', '', '', '200', '1')";
$queries['689'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_restrictions_block', '1', 'productblocks', 'yesno', '', '', '300', '1')";
$queries['690'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_publicboard_block', '1', 'productblocks', 'yesno', '', '', '400', '1')";
$queries['691'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_returnpolicy_block', '1', 'productblocks', 'yesno', '', '', '500', '1')";
$queries['692'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_scheduled_bidding_block', '1', 'productblocks', 'yesno', '', '', '600', '1')";
$queries['693'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_globaldateformat', 'M d, Y', 'globalserverlocale', 'text', '', '', '2', '1')";
$queries['694'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('shippingapiservices', 'shippingapiservices', '300')";
$queries['695'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_access_id', '', 'shippingapiservices', 'text', '', '', '110', '1')";
$queries['696'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_username', '', 'shippingapiservices', 'text', '', '', '120', '1')";
$queries['697'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_password', '', 'shippingapiservices', 'pass', '', '', '130', '1')";
$queries['698'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('ups_server', 'https://www.ups.com/ups.app/xml/Rate', 'shippingapiservices', 'text', '', '', '210', '1')";
$queries['699'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_login', '', 'shippingapiservices', 'text', '', '', '220', '1')";
$queries['700'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_password', '', 'shippingapiservices', 'pass', '', '', '230', '1')";
$queries['701'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('usps_server', 'http://production.shippingapis.com/ShippingAPI.dll', 'shippingapiservices', 'text', '', '', '240', '1')";
$queries['702'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_account', '', 'shippingapiservices', 'text', '', '', '310', '1')";
$queries['703'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_access_id', '', 'shippingapiservices', 'text', '', '', '320', '1')";
$queries['704'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fedex_server', 'https://gatewaybeta.fedex.com/GatewayDC', 'shippingapiservices', 'text', '', '', '330', '1')";
$queries['705'] = "ALTER TABLE " . DB_PREFIX . "invoices ADD `last_reminder_sent` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `isautopayment`";
$queries['706'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('product_videodescription_block', '1', 'productblocks', 'yesno', '', '', '700', '1')";
$queries['707'] = "ALTER TABLE " . DB_PREFIX . "invoices ADD `refund_date` DATETIME NOT NULL default '0000-00-00 00:00:00' AFTER `last_reminder_sent`";
$queries['708'] = "ALTER TABLE " . DB_PREFIX . "projects CHANGE `items_in_lot` `items_in_lot` MEDIUMINT UNSIGNED NOT NULL DEFAULT '0'";
$queries['709'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('legend_tab_search_results', '1', 'globaltabvisibility', 'yesno', '', '', '21', '1')";
$queries['710'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_username', 'Enter your Paypal API Username', '', 'paypal', 'text', '', '', '', 21, 1)";
$queries['711'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_password', 'Enter your Paypal API Password', '', 'paypal', 'pass', '', '', '', 22, 1)";
$queries['712'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_signature', 'Enter your Paypal API Signature', '', 'paypal', 'text', '', '', '', 23, 1)";
$queries['713'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('invoicesystem_sendinvoice', '1', 'invoicesystem', 'yesno', '', '', '40', '1')";
$queries['714'] = "ALTER TABLE " . DB_PREFIX . "admincp_news ADD `terms` LONGTEXT NOT NULL default '' AFTER `visible`";
$queries['715'] = "ALTER TABLE " . DB_PREFIX . "admincp_news ADD `privacy` LONGTEXT NOT NULL default '' AFTER `terms`";
$queries['716'] = "ALTER TABLE " . DB_PREFIX . "admincp_news ADD `about` LONGTEXT NOT NULL default '' AFTER `privacy`";
$queries['717'] = "ALTER TABLE " . DB_PREFIX . "admincp_news ADD `registrationterms` LONGTEXT NOT NULL default '' AFTER `about`";
$queries['718'] = "INSERT INTO " . DB_PREFIX . "admincp_news VALUES (1, NULL, '0000-00-00 00:00:00', '1', 'Marketplace rules', 'Marketplace privacy', 'About us coming soon.  Thanks for your patience.', 'Terms and agreement')";
$queries['719'] = "ALTER TABLE " . DB_PREFIX . "admincp_news ADD `news` LONGTEXT NOT NULL AFTER `registrationterms`";
$queries['720'] = "UPDATE `" . DB_PREFIX . "configuration` SET `value` = '" . $ilance->db->escape_string("{ name: 'document', items : [ 'Source','-','DocProps','Preview','Print','-','Templates' ] },
{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
'/',
{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
{ name: 'insert', items : [ 'Image','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] },
'/',
{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
{ name: 'colors', items : [ 'TextColor','BGColor' ] },
{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] }") . "' WHERE name = 'ckeditor_listingdescriptiontoolbar' LIMIT 1";
$queries['721'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('worldwideshipping', '1', 'shippingsettings', 'yesno', '', '', '1800', '1')";
$queries['722'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('shipping_regions', 'a:7:{i:0;s:6:\"europe\";i:1;s:6:\"africa\";i:2;s:10:\"antarctica\";i:3;s:4:\"asia\";i:4;s:13:\"north_america\";i:5;s:7:\"oceania\";i:6;s:13:\"south_america\";}', 'shippingsettings', 'pulldown', '<select name=\'config[shipping_regions][]\' multiple=\'multiple\'><option value=\'europe\' selected=\'selected\'>{_europe}</option><option value=\'africa\' selected=\'selected\'>{_africa}</option><option value=\'antarctica\' selected=\'selected\'>{_antarctica}</option><option value=\'asia\' selected=\'selected\'>{_asia}</option><option value=\'north_america\' selected=\'selected\'>{_north_america}</option><option value=\'oceania\' selected=\'selected\'>{_oceania}</option><option value=\'south_america\' selected=\'selected\'>{_south_america}</option></select>', '', '1810', '1')";
$queries['723'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_jsminify', '0', 'globalfilterresults', 'yesno', '' , '', '417', '1')";
$queries['724'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharactersdescriptionbulk', '5000', 'globalfilterresults', 'int', '', '', '390', '1')";
$queries['725'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalserversmtp', 'globalserversmtp', '600')";
$queries['726'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_enabled', '0', 'globalserversmtp', 'yesno', '', '', '100', '1')";
$queries['727'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_usetls', '0', 'globalserversmtp', 'yesno', '', '', '200', '1')";
$queries['728'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_host', '', 'globalserversmtp', 'text', '', '', '300', '1')";
$queries['729'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_port', '25', 'globalserversmtp', 'int', '', '', '400', '1')";
$queries['730'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_user', '', 'globalserversmtp', 'text', '', '', '500', '1')";
$queries['731'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversmtp_pass', '', 'globalserversmtp', 'pass', '', '', '600', '1')";
$queries['732'] = "UPDATE " . DB_PREFIX . "configuration SET `inputtype` = 'pulldown' WHERE `name` = 'defaultstyle'";
$queries['733'] = "CREATE TABLE " . DB_PREFIX . "distance_ma (
`ZIPCode` INT(5) NOT NULL,
`City` MEDIUMTEXT,
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
$queries['734'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `originalcurrencyid` INT(5) NOT NULL DEFAULT '0' AFTER `amount`";
$queries['735'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `originalcurrencyidrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `originalcurrencyid`";
$queries['736'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `convertedtocurrencyid` INT(5) NOT NULL DEFAULT '0' AFTER `originalcurrencyidrate`";
$queries['737'] = "ALTER TABLE " . DB_PREFIX . "buynow_orders ADD `convertedtocurrencyidrate` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `convertedtocurrencyid`";
$queries['738'] = "ALTER TABLE " . DB_PREFIX . "distance_in ADD `City` MEDIUMTEXT NOT NULL AFTER `ZIPCode`";
$queries['739'] = "ALTER TABLE " . DB_PREFIX . "categories ADD `canpostclassifieds` INT(1) NOT NULL DEFAULT '1' AFTER `canpost`";
$queries['740'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('globalservercache', 'globalservercache', '700')";
$queries['741'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_engine', 'none', 'globalservercache', 'pulldown', '<select name=\'config[globalservercache_engine]\'><option value=\'none\' selected>None</option><option value=\'filecache\'>File Cache</option></select>', '', '100', '1')";
$queries['742'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_prefix', 'ilance_', 'globalservercache', 'text', NULL, '', '200', '1')";
$queries['743'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalservercache_expiry', '300', 'globalservercache', 'int', NULL, '', '300', '1')";
$queries['744'] = "ALTER TABLE `" . DB_PREFIX . "users` ADD `htmlperm` INT(1) NOT NULL DEFAULT '0' AFTER `password_lastchanged`";
$queries['745'] = "ALTER TABLE `" . DB_PREFIX . "users` DROP `htmlperm`";

if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.2.1 to 4.0.0</h1><p>Updating database...</p>';
        if ($current_version == '3.2.1')
        {
                if (isset($queries) AND !empty($queries) AND is_array($queries) AND $sql_version < 745)
                {
                        for ($i = $sql_version; $i <= 745; ++$i)
                        {
                                if (isset($queries[$i]))
                                {
                                        if (!empty($queries[$i]))
                                        {
                                                $ilance->db->query($queries[$i], 0, null, __FILE__, __LINE__, true, array('1062'));
                                        }
                                        $new_sql_version = $i;
                                }
                        }
                }
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '" . $new_sql_version . "' WHERE name = 'current_sql_version'");
                $show['upgrade_mode'] = true;
                
                ($apihook = $ilance->api('init_configuration_end')) ? eval($apihook) : false;
        
                // import (or detect upgrade) of new phrases for 4.0.0
                echo import_language_phrases(10000, 0);
        
                // import (or detect upgrade) of new css templates for 4.0.0
                echo import_templates();
        
                // import (or detect upgrade) of new email templates for 4.0.0
                echo import_email_templates();
        
                // rebuild the recursive category logic for 4.0.0
                print_progress_begin('<b>Rebuilding hierarchical logic within the category table</b>, please wait.', '.', 'progressspan99');
                rebuild_category_tree(0, 1);
                print_progress_end();
                // optimize new category table to support spatial indexing for 4.0.0
                //echo rebuild_spatial_category_indexes();
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '4.0.0' WHERE name = 'current_version'");
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
        echo '<h1>Upgrade from 3.2.1 to 4.0.0</h1><p>The following SQL queries will be executed:</p><hr size="1" width="100%" style="margin:0px; padding:0px" />';
        if (isset($queries) AND !empty($queries) AND is_array($queries))
        {
                if ($sql_version == 745)
                {
                        echo '<div>You have the latest SQL version.</div>';
                }
                else
                {
                        for ($i = $sql_version; $i <= 745; ++$i)
                        {
                                if (isset($queries[$i]) AND !empty($queries[$i]))
                                {
                                        echo '<div><textarea style="font-family: verdana" cols="80" rows="5">' . $queries[$i] . '</textarea></div><hr size="1" width="100%" />';
                                }
                        }
                }
                echo '<div class="redhlite"><strong>Notice: </strong><span>Before upgrade make sure you have backup of your database and files. <br/>You should also export your language and template into XML file. During upgrade process your default template will be overwritten with the new template. If you use more than one template then you will need to open /install/xml/master-style.xml and edit line <br />' . htmlspecialchars('<style name="Default Style" ilversion="4.0.0">') . '<br />Instead of "Default Style" enter name of your style here. Now you should goto AdminCP->Styles / CSS->Import and import this edited style. This will overwrite your style. </span></div>';
                if (function_exists("version_compare") AND version_compare(MYSQL_VERSION, "5.1", ">="))
                {
                        echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">MySQL version</font><br /></b></span>MySQL version >= 5.1.x</div><div><strong><a href="installer.php?do=install&step=32&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
                }
                else
                {
                        echo '<div class="redhlite"><span class="smaller"><b><font color="red">MySQL version</font></b></span><br />MySQL version should be greater or equal than 5.1.x however ILance still supports backward compatibility for MySQL < 5.1.x. If you use large number of categories then you will need MySQL 5.1.x</div><div><strong><a href="installer.php?do=install&step=32&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
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