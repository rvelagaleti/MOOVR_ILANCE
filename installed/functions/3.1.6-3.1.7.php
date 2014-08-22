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
        echo '<h1>Upgrade 3.1.6 to 3.1.7</h1><p>Updating database...</p>';
        
        $sql = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'current_version'
        ", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        if ($res['value'] == '3.1.6')
        {
		$sql_sitename = $ilance->db->query("
			SELECT value
			FROM " . DB_PREFIX . "configuration
			WHERE name = 'globalserversettings_sitename'
		", 0, null, __FILE__, __LINE__);
		$res_sitename = $ilance->db->fetch_array($sql_sitename);
	
		$sql_siteemail = $ilance->db->query("
			SELECT value
			FROM " . DB_PREFIX . "configuration
			WHERE name = 'globalserversettings_siteemail'
		", 0, null, __FILE__, __LINE__);
		$res_siteemail = $ilance->db->fetch_array($sql_siteemail);
                
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX ."configuration
			WHERE name = 'globalserverlanguage_localeset'
		", 0, null, __FILE__, __LINE__);
		
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'connections_crawlerstrings'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalfilters_maxrfpsend2friendperday'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalfilters_enableexpandcollapsedisplay'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalfilters_defaultdeflated'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalfilters_enablerfpdownloadreport'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalserverlocale_officialtimeformat'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'globalserverlocale_officialtimeformat2'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'serviceplugins_spellcheck'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration_groups
                        WHERE groupname = 'globalserverlanguage'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration_groups
                        WHERE groupname = 'serviceplugins'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX ."configuration
                        WHERE name = 'invoicesystem_insertwarningafter'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET configgroup = 'language',
                        inputtype = 'int',
                        inputcode = '',
                        inputname = ''
                        WHERE name = 'globalserverlanguage_defaultlanguage'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET configgroup = 'globalsecuritymime'
                        WHERE name = 'current_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET configgroup = 'globalsecuritymime'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration_groups
                        SET parentgroupname = 'globalserverlocalecurrency'
                        WHERE parentgroupname = 'globalserverlocale'
                            AND groupname = 'globalserverlocalecurrency'
                ", 0, null, __FILE__, __LINE__);
                
		$ilance->db->query("
			ALTER TABLE " . DB_PREFIX . "language
			ADD `locale` VARCHAR(20) NOT NULL DEFAULT 'en_US' AFTER `charset`,
                        ADD `author` VARCHAR(100) NOT NULL DEFAULT 'ilance' AFTER `locale`
		", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "language_phrases
                        ADD `ismaster` INT(1) NOT NULL DEFAULT '1' AFTER `ismoved`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "language_phrasegroups`
                        ADD `product` VARCHAR( 250 ) NOT NULL DEFAULT 'ilance' AFTER `description`
                ", 0, null, __FILE__, __LINE__);
		
		$ilance->db->query("
			ALTER TABLE " . DB_PREFIX . "sessions
			ADD `iserror` INT(1) NOT NULL DEFAULT '0' AFTER `isrobot`,
                        ADD `languageid` INT(1) NOT NULL DEFAULT '0' AFTER `iserror`,
                        ADD `styleid` INT(1) NOT NULL DEFAULT '0' AFTER `languageid`
		", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration
                        VALUES (
                        NULL,
                        0,
                        'serveroverloadlimit',
                        'Enter the overload limit on this server before a notice is presented to users informing them to retry the site later (0 to disable)',
                        '0',
                        'globalsecuritysettings',
                        'int',
                        '',
                        '',
                        '',
                        400)
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "calendar (
                        `calendarid` INT(5) NOT NULL auto_increment,
                        `userid` INT(5) NOT NULL default '0',
                        `dateline` date NOT NULL,
                        `comment` mediumtext,
                        `visible` INT(1) NOT NULL default '1',
                        PRIMARY KEY  (`calendarid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
	    
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration_groups` DROP `groupid` 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD PRIMARY KEY (`groupname`) 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD `sort` INT( 5 ) NOT NULL DEFAULT '0' AFTER `help`
                ", 0, null, __FILE__, __LINE__);
            
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "categories` ADD `usereserveprice` INT( 1 ) NOT NULL DEFAULT '1' AFTER `useproxybid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration` DROP `parentid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration` DROP `id`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "configuration` ADD PRIMARY KEY (`name`) 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "payment_groups` DROP `groupid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "payment_groups` ADD PRIMARY KEY (`groupname`) 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects` CHANGE `ship_handling` `ship_handling` ENUM( 'none', 'flatrate', 'sellerpays', 'digital' ) NOT NULL DEFAULT 'flatrate' 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects` CHANGE `ship_country` `ship_country` ENUM( 'none', 'worldwide', 'pickup', 'custom' ) NOT NULL DEFAULT 'worldwide' 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` ADD `textdirection` VARCHAR( 3 ) NOT NULL DEFAULT 'ltr' AFTER `author`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` ADD `languageiso` VARCHAR( 10 ) NOT NULL DEFAULT 'en' AFTER `textdirection`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` ADD `installdate` DATETIME NOT NULL AFTER `languageiso`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` ADD `canselect` INT( 1 ) NOT NULL DEFAULT '1' AFTER `languageiso`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` DROP `htmldoctype`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` DROP `htmlextra`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO `" . DB_PREFIX . "templates` (
                        `tid` ,
                        `name` ,
                        `description` ,
                        `original` ,
                        `content` ,
                        `type` ,
                        `status` ,
                        `createdate` ,
                        `author` ,
                        `request` ,
                        `version` ,
                        `isupdated` ,
                        `updatedate` ,
                        `styleid`)
                        VALUES (
                        NULL ,
                        'template_htmldoctype',
                        'Template Document Type',
                        '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">',
                        '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">',
                        'variable',
                        '1',
                        NOW( ) ,
                        '',
                        '',
                        '1.0',
                        '0',
                        '2008-06-12 12:20:18',
                        '1')
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO `" . DB_PREFIX . "templates` (
                        `tid` ,
                        `name` ,
                        `description` ,
                        `original` ,
                        `content` ,
                        `type` ,
                        `status` ,
                        `createdate` ,
                        `author` ,
                        `request` ,
                        `version` ,
                        `isupdated` ,
                        `updatedate` ,
                        `styleid` )
                        VALUES (
                        NULL ,
                        'template_htmlextra',
                        'Template Document Type Extra Bit',
                        '',
                        '',
                        'variable',
                        '1',
                        '2008-08-15 04:52:57',
                        '',
                        '',
                        '1.0',
                        '0',
                        '2008-06-12 12:20:18',
                        '1')
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "templates
                        WHERE name = 'template_charset'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "templates
                        WHERE name = 'template_languagecode'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "templates
                        WHERE name = 'template_textdirection'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_quickregistration', 'Would you like to enable the Quick Registration feature for new users?', '0', 'registrationdisplay', 'yesno', '', '', 'The quick registration setting allows users to quickly register to the marketplace though the use of AJAX.  Note: quick registration should only be used if you experience a low registration rate on a day to day basis.  When quick registration is enabled users will not be building their full profile and must update their personal information after they have registered.  Quick registration can be found on the log-in menu.', 100)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightfees'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightcolor'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightfee'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldactive'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldfees'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldfee'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredactive'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredfees'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredfee'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredlength'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'serviceupsell'", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_bold', 'Bold Upsell Listing Features', '', '50')", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured', 'Featured Homepage Upsell Listing Features', '', '60')", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_highlight', 'Highlight Upsell Listing Features', '', '70')", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD `class` VARCHAR( 250 ) NOT NULL DEFAULT 'tablehead_alt' AFTER `help`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "project_bids` ADD `isproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `state`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "invoices`
                            ADD `isportfoliofee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isif` ,
                            ADD `isenhancementfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isportfoliofee` ,
                            ADD `isescrowfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isenhancementfee` ,
                            ADD `iswithdrawfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfee` ,
                            ADD `isp2bfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `iswithdrawfee`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "modules_group` ADD `developer` VARCHAR( 250 ) NOT NULL DEFAULT 'ILance' AFTER `url`
                ", 0, null, __FILE__, __LINE__);
            
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "email_departments (
                        `departmentid` INT(10) NOT NULL AUTO_INCREMENT,
                        `title` MEDIUMTEXT,
                        `email` VARCHAR(250) NOT NULL default '',
                        `canremove` INT(1) NOT NULL default '1',
                        PRIMARY KEY (`departmentid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "email_departments
                        (`departmentid`, `title`, `email`, `canremove`)
                        VALUES
                        (NULL, '" . addslashes($res_sitename['value']) . "', '" . addslashes($res_siteemail['value']) . "', 0)
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "email` ADD `departmentid` INT( 5 ) NOT NULL DEFAULT '1' AFTER `cansend`", 0, null, __FILE__, __LINE__);
            
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "abuse_reports (
                        `abuseid` INT( 5 ) NOT NULL AUTO_INCREMENT,
                        `regarding` MEDIUMTEXT,
                        `username` MEDIUMTEXT,
                        `email` MEDIUMTEXT,
                        `itemid` INT( 5 ) NOT NULL DEFAULT '0',
                        `abusetype` ENUM('listing','bid','portfolio','profile','feedback','pmb') NOT NULL default 'listing',
                        `type` VARCHAR(100) NOT NULL default '',
                        `status` INT( 1 ) NOT NULL DEFAULT '1',
                        `dateadded` DATETIME NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY (`abuseid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $bidfieldsextra = '';
                $sql = $ilance->db->query("
                        SELECT languagecode
                        FROM " . DB_PREFIX . "language
                ", 0, null, __FILE__, __LINE__);
                while ($res = $ilance->db->fetch_array($sql))
                {
                        $slng = mb_substr($res['languagecode'], 0, 3);
                        $bidfieldsextra .= "
                        `question_$slng` MEDIUMTEXT,
                        `description_$slng` MEDIUMTEXT,";
                }
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "bid_fields (
                        `fieldid` INT(10) NOT NULL AUTO_INCREMENT,
                        $bidfieldsextra
                        `inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','date') NOT NULL default 'text',
                        `multiplechoice` MEDIUMTEXT,
                        `sort` INT(3) NOT NULL default '0',
                        `visible` INT(1) NOT NULL default '1',
                        `required` INT(1) NOT NULL default '0',
                        `canremove` INT(1) NOT NULL default '1',
                        PRIMARY KEY  (`fieldid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "bid_fields_answers (
                        `answerid` INT(10) NOT NULL AUTO_INCREMENT,
                        `fieldid` INT(10) NOT NULL default '0',
                        `project_id` INT(10) NOT NULL default '0',
                        `bid_id` INT(10) NOT NULL default '0',
                        `answer` MEDIUMTEXT,
                        `date` DATETIME NOT NULL default '0000-00-00 00:00:00',
                        `visible` INT(1) NOT NULL default '1',
                        PRIMARY KEY  (`answerid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "categories` ADD `bidfields` MEDIUMTEXT NOT NULL AFTER `usereserveprice` 
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("RENAME TABLE `" . DB_PREFIX . "rating_criteria` TO `" . DB_PREFIX . "feedback_criteria`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "buynow_orders`
                        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `paiddate` ,
                        ADD `sellerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `buyerfeedback`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects_escrow`
                        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `qty` ,
                        ADD `sellerfeedback` INT NOT NULL DEFAULT '0' AFTER `buyerfeedback`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "projects`
                        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `returnpolicy` ,
                        ADD `sellerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `buyerfeedback`
                ", 0, null, __FILE__, __LINE__);
                
                // #############################################################
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "project_bids` ADD `isshortlisted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isproxybid`", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardwaitperiod', 'If a buyer awarded a service provider how long should the marketplace wait for the provider to accept the buyers award (in days)?', '7', 'servicebid_limits', 'int', '', '', 'This setting ensures that a buyers project does not get a deadbeat provider delaying the project.  This setting automatically resets the buyers project to open if there is any time left and respectively declines the providers awarded bid so others can become awarded.', 10)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "project_realtimebids`
                        ADD `isproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `state`,
                        ADD `isshortlisted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isproxybid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "feedback_response (
                        `id` INT(100) NOT NULL AUTO_INCREMENT,
                        `feedbackid` INT(10) NOT NULL default '0',
                        `project_id` INT(10) NOT NULL default '0',
                        `from_user_id` INT(10) NOT NULL default '0',
                        `comments` mediumtext,
                        `date_added` datetime NOT NULL default '0000-00-00 00:00:00',
                        `type` enum('','buyer','seller') NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_refresh', 'Would you like to enable the Refresh splash screen when an action has been performed on the marketplace?', '1', 'globalfilterresults', 'yesno', '', '', 'This option will produce a message to the user asking them to please wait while your request has been completed.  When disabled the refresh logic will immediately redirect the user without any message shown.', 10)", 0, null, __FILE__, __LINE__);
                
                $defaultsearchoptions = 'a:19:{s:7:"perpage";s:2:"10";s:4:"sort";s:3:"0|1";s:8:"username";s:4:"true";s:14:"latestfeedback";s:4:"true";s:6:"online";s:4:"true";s:15:"displayfeatured";s:4:"true";s:10:"showtimeas";s:6:"static";s:11:"description";s:4:"true";s:5:"icons";s:4:"true";s:15:"currencyconvert";s:4:"true";s:8:"proxybit";s:4:"true";s:4:"list";s:4:"list";s:15:"serviceselected";a:7:{i:0;s:5:"title";i:1;s:4:"bids";i:2;s:8:"timeleft";i:3;s:10:"averagebid";i:4;s:8:"category";i:5;s:7:"country";i:6;s:3:"sel";}s:15:"productselected";a:8:{i:0;s:6:"sample";i:1;s:5:"title";i:2;s:5:"price";i:3;s:8:"shipping";i:4;s:4:"bids";i:5;s:8:"timeleft";i:6;s:7:"country";i:7;s:3:"sel";}s:14:"expertselected";a:11:{i:0;s:11:"profilelogo";i:1;s:6:"expert";i:2;s:11:"rateperhour";i:3;s:11:"credentials";i:4;s:8:"feedback";i:5;s:5:"rated";i:6;s:8:"earnings";i:7;s:9:"portfolio";i:8;s:6:"awards";i:9;s:7:"country";i:10;s:3:"sel";}s:13:"defaultupdate";s:4:"true";s:13:"membersupdate";s:4:"true";s:10:"hidelisted";s:5:"false";s:11:"hideverbose";s:5:"false";}';
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchdefaultcolumns', 'This will contain specific code data used by the advanced search options menu.  Do not manually edit.', '" . $ilance->db->escape_string($defaultsearchoptions) . "', 'search', 'text', '', '', 'This option is used as a datastore for the presentation of the search results columns displayed in the marketplace.  Please do not edit this string.', 30)", 0, null, __FILE__, __LINE__);
                unset($defaultsearchoptions);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('multilevelpulldown', 'Would you like to show all subcategories within the pulldown menus?', '1', 'globalauctionsettings', 'yesno', '', '', 'This feature should be enabled if your category system is small (ie: less than 50 categories).  If you have over 50 categories and you enable this feature, it will provide your users with a very fast pulldown menu showing only base root categories.', 320)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "profile_filter_auction_answers` ADD `filtertype` ENUM( 'range', 'checkbox', 'pulldown' ) NOT NULL AFTER `answer`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "search_users (
                        `id` INT(100) NOT NULL AUTO_INCREMENT,
                        `user_id` INT(10) NOT NULL,
                        `keyword` MEDIUMTEXT,
                        `added` DATETIME NOT NULL default '0000-00-00 00:00:00',
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language` ADD `replacements` MEDIUMTEXT NOT NULL AFTER `installdate`");
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalauctionsettings_seoreplacements'");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('skills', 'skills', 'Skills Options and Settings', 'Skills provide members the ability to precisely define their expertise via skill categories that you create.  End users can use the advanced search system to narrow down specific expertise as required.', 'tablehead_alt', '450')");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "configuration` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1' AFTER `sort`");
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "payment_methods (
                        `id` INT(10) NOT NULL AUTO_INCREMENT,
                        `title` MEDIUMTEXT,
                        `sort` INT(5) NOT NULL,
                        PRIMARY KEY  (`id`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ");
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'See item description for payment methods accepted', 10)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card', 20)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Visa', 30)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Debit Card', 40)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal', 50)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Money Order', 60)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Personal Check', 70)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa', 80)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Debit Card', 90)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, PayPal', 100)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Visa, PayPal', 110)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Money Order', 120)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Personal Check, Money Order', 130)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Personal Check, Money Order', 140)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Money Order', 150)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Master Card, Money Order', 160)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Money Order', 170)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Debit, Money order', 180)");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Master, Visa, Debit, Money order', 190)");
                
		$ilance->subscription_plan->add_subscription_permissions($accesstext = 'Newsletter Resources', $accessdescription = 'Defines if any customer within this subscription group can opt-in to any of the available newsletter resources', $accessname = 'newsletteropt_in', $accesstype = 'yesno', $value = 'yes', $canremove = 0);
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Exempt From Pay as you Go', $accessdescription = 'Defines if a customer within this subscription group is exempt from Pay as you go.  When enabled, the users posted listing will automatically be visible without having to pay before it going live.', $accessname = 'payasgoexempt', $accesstype = 'yesno', $value = 'no', $canremove = 0);
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Can use Sealed Bidding', $accessdescription = 'Defines if any customer within this subscription group can set sealed bidding privacy when listing an auction', $accessname = 'cansealbids', $accesstype = 'yesno', $value = 'yes', $canremove = 0);
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Can use Blind Bidding', $accessdescription = 'Defines if any customer within this subscription group can set blind bidding privacy when listing an auction', $accessname = 'canblindbids', $accesstype = 'yesno', $value = 'yes', $canremove = 0);
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Can use Full Bid Privacy (Sealed + Blind)', $accessdescription = 'Defines if any customer within this subscription group can set full bidding privacy when listing an auction', $accessname = 'canfullprivacybids', $accesstype = 'yesno', $value = 'yes', $canremove = 0);
		$ilance->subscription_plan->add_subscription_permissions($accesstext = 'Maximum Skill Categories Opt-In', $accessdescription = 'Maximum amount of selectable skill categories a user within this subscription can opt-in', $accessname = 'maxskillscat', $accesstype = 'int', $value = '5', $canremove = 0);
		
		$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableskills', 'Would you like to enable the Skills system?', '1', 'skills', 'yesno', '', '', 'This option will allow users to make use of the skills system.  They can opt into various skill categories and can be searched based on skills from the advanced search menu.', 10, 1)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablepopulartags', 'Would you like to display the popular search tags on the main menu?', '1', 'globalfilterresults', 'yesno', '', '', 'The popular search tags feature produces realtime keyword search tags queried within the marketplace.', 100, 1)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartagcount', 'How many searches does it take before a tag becomes popular?', '30', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many times a keyword tag needs to be searched by users in the system before it becomes popular and displayed within the popular search tag area.', 200, 1)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartaglimit', 'How many popular keyword to display at once?', '50', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many actual popular keyword tags will be displayed at any given time when users are viewing the popular keyword tags.', 300, 1)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search_favorites` ADD `lastsent` DATETIME NOT NULL AFTER `added`");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search_favorites` ADD `lastseenids` MEDIUMTEXT NOT NULL AFTER `lastsent`");
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_au (
                        `ZIPCode` INT(5) NOT NULL,
                        `City` VARCHAR(39) NOT NULL,
                        `State` VARCHAR(3) NOT NULL,
                        `Longitude` DOUBLE default NULL,
                        `Latitude` DOUBLE default NULL,
                        KEY `ZIPCode` (`ZIPCode`)
                      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ");
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_pl (
                        `ZIPCode` varchar(255) default NULL,
                        `City` varchar(255) default NULL,
                        `Latitude` varchar(255) default NULL,
                        `Longitude` varchar(255) default NULL,
                        `State` varchar(255) default NULL,
                        KEY `ZIPCode` (`ZIPCode`)
                      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ");
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "distance_de (
                        `ZIPCode` varchar(255) default NULL,
                        `City` varchar(255) default NULL,
                        `Latitude` varchar(255) default NULL,
                        `Longitude` varchar(255) default NULL,
                        `State` varchar(255) default NULL,
                        KEY `ZIPCode` (`ZIPCode`)
                      ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ");
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('fulltextsearch', 'Would you like to enable Fulltext search?', '1', 'search', 'yesno', '', '', 'Boolean fulltext mode queries became available in MySQL in version 4, and allow expressions to make use of a complex set of boolean rules to let users refine their searches. These queries are very powerful when applied to fulltext searching and sorting of results when enabled.', 30, 1)");
                
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language_phrasegroups` DROP `id`");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language_phrasegroups` ADD PRIMARY KEY (`groupname`)");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language_phrases` ADD `phrasegroup` MEDIUMTEXT NOT NULL AFTER `phraseid`");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'accounting' WHERE phrasegroupid = '1'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'main' WHERE phrasegroupid = '2'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'search' WHERE phrasegroupid = '3'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'preferences' WHERE phrasegroupid = '4'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'registration' WHERE phrasegroupid = '5'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'subscription' WHERE phrasegroupid = '6'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'ipn' WHERE phrasegroupid = '7'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'buying' WHERE phrasegroupid = '8'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'feedback' WHERE phrasegroupid = '9'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'rfp' WHERE phrasegroupid = '10'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'watchlist' WHERE phrasegroupid = '11'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'selling' WHERE phrasegroupid = '12'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'pmb' WHERE phrasegroupid = '13'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'cron' WHERE phrasegroupid = '14'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'portfolio' WHERE phrasegroupid = '15'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'administration' WHERE phrasegroupid = '16'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'livebid' WHERE phrasegroupid = '17'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'mediashare' WHERE phrasegroupid = '18'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'livesync' WHERE phrasegroupid = '19'");
                $ilance->db->query("UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'javascript' WHERE phrasegroupid = '20'");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "language_phrases` DROP `phrasegroupid`");
                $ilance->db->query("DELETE FROM `" . DB_PREFIX . "language_phrasegroups` WHERE groupname = 'livesync'");
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('savedsearches', 'Would you like to enable Saved Searches?', '1', 'search', 'yesno', '', '', 'This feature allows logged in members the ability to save their search when searching for products or services.  Additionally, users can subscribe via email so when new matches are met they receive a new email with results.', 40, 1)");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` ADD `returnshippaidby` ENUM( 'none', 'buyer', 'seller' ) NOT NULL DEFAULT 'none' AFTER `returngivenas`");
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_companyname', 'Company name', 'Company Name', 'globalserversettings', 'text', '', '', 'This company name should not be confused with your Site title or Site Name.  For example, ILance Inc. is a company name, ILance Marketplace could be the Site title / name.  Company name will only show on invoices and transactions generated from the company to the users.', 1, 1)");
                $ilance->db->query("DELETE FROM `" . DB_PREFIX . "payment_configuration` WHERE name = 'registration_cc_pay_attempts'");
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'save_credit_cards', 'Would you like to save credit cards in the database?', '0', 'defaultgateway', 'yesno', '', '', '', 1)");
                $ilance->db->query("UPDATE " . DB_PREFIX . "profile_groups SET cid = '-1' WHERE name = 'All Profile Categories'");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "modules_group` ADD `filestructure` MEDIUMTEXT NOT NULL AFTER `developer`");
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "modules_group`
                        ADD `installdate` DATETIME NOT NULL AFTER `filestructure`,
                        ADD `upgradedate` DATETIME NOT NULL AFTER `installdate`
                ");
		
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "users` ADD `profileintro` MEDIUMTEXT NOT NULL AFTER `profilevideourl`");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search` ADD `searchmode` MEDIUMTEXT NOT NULL AFTER `keyword`");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "search_users` ADD `searchmode` MEDIUMTEXT NOT NULL AFTER `keyword`");
                $ilance->db->query("ALTER TABLE `" . DB_PREFIX . "projects` ADD `description_videourl` MEDIUMTEXT NOT NULL AFTER `description`");
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "projects_trackbacks (
                        `trackbackid` INT(100) NOT NULL AUTO_INCREMENT,
                        `project_id` INT(50) NOT NULL default '0',
                        `ipaddress` MEDIUMTEXT,
                        `url` MEDIUMTEXT,
                        `visible` INT(1) NOT NULL default '1',
                        PRIMARY KEY (`trackbackid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ");
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '11'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '3.1.7'
                        WHERE name = 'current_version'
                ", 0, null, __FILE__, __LINE__);
                
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
        $sql_sitename = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'globalserversettings_sitename'
        ", 0, null, __FILE__, __LINE__);
        $res_sitename = $ilance->db->fetch_array($sql_sitename);

        $sql_siteemail = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'globalserversettings_siteemail'
        ", 0, null, __FILE__, __LINE__);
        $res_siteemail = $ilance->db->fetch_array($sql_siteemail);
        	
        echo '<h1>Upgrade from 3.1.6 to 3.1.7</h1><p>The following SQL query will be executed:</p>';    
        echo '<hr size="1" width="100%" />';

	echo "
		DELETE FROM " . DB_PREFIX ."configuration<br />
		WHERE name = 'globalserverlanguage_localeset'<br /><br />
	";

        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'connections_crawlerstrings'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalfilters_maxrfpsend2friendperday'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalfilters_enableexpandcollapsedisplay'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalfilters_defaultdeflated'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'invoicesystem_insertwarningafter'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration_groups<br />
                WHERE groupname = 'globalserverlanguage'<br /><br />
        ";

        echo "
                UPDATE " . DB_PREFIX . "configuration<br />
                SET configgroup = 'language',<br />
                inputtype = 'int',<br />
                inputcode = '',<br />
                inputname = ''<br />
                WHERE name = 'globalserverlanguage_defaultlanguage'<br /><br />
        ";
        
        echo "
                UPDATE " . DB_PREFIX . "configuration<br />
                SET configgroup = 'globalsecuritymime'<br />
                WHERE name = 'current_version'<br /><br />
        ";
        
        echo "
                UPDATE " . DB_PREFIX . "configuration<br />
                SET configgroup = 'globalsecuritymime'<br />
                WHERE name = 'current_sql_version'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalfilters_enablerfpdownloadreport'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalserverlocale_officialtimeformat'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'globalserverlocale_officialtimeformat2'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration<br />
                WHERE name = 'serviceplugins_spellcheck'<br /><br />
        ";
        
        echo "
                DELETE FROM " . DB_PREFIX ."configuration_groups<br />
                WHERE groupname = 'serviceplugins'<br /><br />
        ";
        
        echo "
                UPDATE " . DB_PREFIX . "configuration_groups<br />
                SET parentgroupname = 'globalserverlocalecurrency'<br />
                WHERE parentgroupname = 'globalserverlocale'<br />
                    AND groupname = 'globalserverlocalecurrency'<br /><br />
        ";

        echo "
		ALTER TABLE " . DB_PREFIX . "language<br />
		ADD `locale` VARCHAR(20) NOT NULL DEFAULT 'en_US' AFTER `charset`,<br />
                ADD `author` VARCHAR(100) NOT NULL DEFAULT 'ilance' AFTER `locale`<br /><br />
	";
        
        echo "
                ALTER TABLE " . DB_PREFIX . "language_phrases<br />
                ADD `ismaster` INT(1) NOT NULL DEFAULT '1' AFTER `ismoved`<br /><br />
        ";
        
        echo "
                ALTER TABLE `" . DB_PREFIX . "language_phrasegroups`<br />
                ADD `product` VARCHAR( 250 ) NOT NULL DEFAULT 'ilance' AFTER `description`<br /><br />
        ";
	
	echo "ALTER TABLE " . DB_PREFIX . "sessions ADD `iserror` INT(1) NOT NULL DEFAULT '0' AFTER `isrobot`,<br />ADD `languageid` INT(1) NOT NULL DEFAULT '0' AFTER `iserror`,<br />ADD `styleid` INT(1) NOT NULL DEFAULT '0' AFTER `languageid`<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration<br />
        VALUES (<br />
        NULL,<br />
        0,<br />
        'serveroverloadlimit',<br />
        'Enter the overload limit on this server before a notice is presented to users informing them to retry the site later (0 to disable)',<br />
        '0',<br />
        'globalsecuritysettings',<br />
        'int',<br />
        '',<br />
        '',<br />
        '',<br />
        400)<br /><br />";
	
        echo "CREATE TABLE " . DB_PREFIX . "calendar (<br />
                `calendarid` INT(5) NOT NULL auto_increment,<br />
                `userid` INT(5) NOT NULL default '0',<br />
                `dateline` date NOT NULL,<br />
                `comment` mediumtext,<br />
                `visible` INT(1) NOT NULL default '1',<br />
                PRIMARY KEY  (`calendarid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "configuration_groups` DROP `groupid`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD PRIMARY KEY (`groupname`)<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD `sort` INT( 5 ) NOT NULL DEFAULT '0' AFTER `help`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "categories` ADD `usereserveprice` INT( 1 ) NOT NULL DEFAULT '1' AFTER `useproxybid`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "configuration` DROP `parentid`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "configuration` DROP `id`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "configuration` ADD PRIMARY KEY (`name`)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "payment_groups` DROP `groupid`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "payment_groups` ADD PRIMARY KEY (`groupname`)<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "projects` CHANGE `ship_handling` `ship_handling` ENUM( 'none', 'flatrate', 'sellerpays', 'digital' ) NOT NULL DEFAULT 'flatrate'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects` CHANGE `ship_country` `ship_country` ENUM( 'none', 'worldwide', 'pickup', 'custom' ) NOT NULL DEFAULT 'worldwide'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` ADD `textdirection` VARCHAR( 3 ) NOT NULL DEFAULT 'ltr' AFTER `author`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` ADD `languageiso` VARCHAR( 10 ) NOT NULL DEFAULT 'en' AFTER `textdirection`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` ADD `installdate` DATETIME NOT NULL AFTER `languageiso`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` ADD `canselect` INT( 1 ) NOT NULL DEFAULT '1' AFTER `languageiso`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` DROP `htmldoctype`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language` DROP `htmlextra`<br /><br />";
        
        echo "INSERT INTO `" . DB_PREFIX . "templates` (<br />
            `tid` ,<br />
            `name` ,<br />
            `description` ,<br />
            `original` ,<br />
            `content` ,<br />
            `type` ,<br />
            `status` ,<br />
            `createdate` ,<br />
            `author` ,<br />
            `request` ,<br />
            `version` ,<br />
            `isupdated` ,<br />
            `updatedate` ,<br />
            `styleid`)<br />
            VALUES (<br />
            NULL ,<br />
            'template_htmldoctype',<br />
            'Template Document Type',<br />
            '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">',<br />
            '<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">',<br />
            'variable',<br />
            '1',<br />
            NOW( ) ,<br />
            '',<br />
            '',<br />
            '1.0',<br />
            '0',<br />
            '2008-06-12 12:20:18',<br />
            '1');<br /><br />";
            
        echo "INSERT INTO `" . DB_PREFIX . "templates` (<br />
            `tid` ,<br />
            `name` ,<br />
            `description` ,<br />
            `original` ,<br />
            `content` ,<br />
            `type` ,<br />
            `status` ,<br />
            `createdate` ,<br />
            `author` ,<br />
            `request` ,<br />
            `version` ,<br />
            `isupdated` ,<br />
            `updatedate` ,<br />
            `styleid` )<br />
            VALUES (<br />
            NULL ,<br />
            'template_htmlextra',<br />
            'Template Document Type Extra Bit',<br />
            '',<br />
            '',<br />
            'variable',<br />
            '1',<br />
            '2008-08-15 04:52:57',<br />
            '',<br />
            '',<br />
            '1.0',<br />
            '0',<br />
            '2008-06-12 12:20:18',<br />
            '1')<br /><br />";
        
       echo "
            DELETE FROM " . DB_PREFIX . "templates
            WHERE name = 'template_charset'<br /><br />
        ";
        
        echo "
            DELETE FROM " . DB_PREFIX . "templates
            WHERE name = 'template_languagecode'<br /><br />
        ";
        
        echo "
            DELETE FROM " . DB_PREFIX . "templates
            WHERE name = 'template_textdirection'<br /><br />
        ";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('registrationdisplay_quickregistration', 'Would you like to enable the Quick Registration feature for new users?', '0', 'registrationdisplay', 'yesno', '', '', 'The quick registration setting allows users to quickly register to the marketplace though the use of AJAX.  Note: quick registration should only be used if you experience a low registration rate on a day to day basis.  When quick registration is enabled users will not be building their full profile and must update their personal information after they have registered.  Quick registration can be found on the log-in menu.', 100)<br /><br />";
        
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightfees'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightcolor'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_highlight' WHERE name = 'serviceupsell_highlightfee'<br /><br />";
        
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldactive'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldfees'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_bold' WHERE name = 'serviceupsell_boldfee'<br /><br />";
        
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredactive'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredfees'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredfee'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET configgroup = 'serviceupsell_featured' WHERE name = 'serviceupsell_featuredlength'<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "configuration_groups WHERE groupname = 'serviceupsell'<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_bold', 'Bold Upsell Listing Features', '', '50')<br /><br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_featured', 'Featured Homepage Upsell Listing Features', '', '60')<br /><br />";
	echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('serviceupsell', 'serviceupsell_highlight', 'Highlight Upsell Listing Features', '', '70')<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "configuration_groups` ADD `class` VARCHAR( 250 ) NOT NULL DEFAULT 'tablehead_alt' AFTER `help`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "styles` ADD `help` MEDIUMTEXT NOT NULL AFTER `visible`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "project_bids` ADD `isproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `state`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "invoices`<br />
                    ADD `isportfoliofee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isif` ,<br />
                    ADD `isenhancementfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isportfoliofee` ,<br />
                    ADD `isescrowfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isenhancementfee` ,<br />
                    ADD `iswithdrawfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isescrowfee` ,<br />
                    ADD `isp2bfee` INT( 1 ) NOT NULL DEFAULT '0' AFTER `iswithdrawfee`<br /><br />
        ";
        
        echo "ALTER TABLE `" . DB_PREFIX . "modules_group` ADD `developer` VARCHAR( 250 ) NOT NULL DEFAULT 'ILance' AFTER `url`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "email_departments (<br />
        `departmentid` INT(10) NOT NULL AUTO_INCREMENT,<br />
        `title` MEDIUMTEXT,<br />
        `email` VARCHAR(250) NOT NULL default '',<br />
        `canremove` INT(1) NOT NULL default '1',<br />
        PRIMARY KEY (`departmentid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "email_departments<br />
        (`departmentid`, `title`, `email`, `canremove`)<br />
        VALUES<br />
        (NULL, '" . addslashes($res_sitename['value']) . "', '" . addslashes($res_siteemail['value']) . "', 0)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "email` ADD `departmentid` INT( 5 ) NOT NULL DEFAULT '1' AFTER `cansend`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "abuse_reports (<br />
        `abuseid` INT( 5 ) NOT NULL AUTO_INCREMENT,<br />
        `regarding` MEDIUMTEXT,<br />
        `username` MEDIUMTEXT,<br />
        `email` MEDIUMTEXT,<br />
        `itemid` INT( 5 ) NOT NULL DEFAULT '0',<br />
        `abusetype` ENUM('listing','bid','portfolio','profile','feedback','pmb') NOT NULL default 'listing',<br />
        `type` VARCHAR(100) NOT NULL default '',<br />
        `status` INT( 1 ) NOT NULL DEFAULT '1',<br />
        `dateadded` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
        PRIMARY KEY (`abuseid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "bid_fields (<br />
        `fieldid` INT(10) NOT NULL AUTO_INCREMENT,<br />
        `question_eng` MEDIUMTEXT,<br />
        `description_eng` MEDIUMTEXT,<br />
        `inputtype` ENUM('yesno','int','textarea','text','pulldown','multiplechoice','date') NOT NULL default 'text',<br />
        `multiplechoice` MEDIUMTEXT,<br />
        `sort` INT(3) NOT NULL default '0',<br />
        `visible` INT(1) NOT NULL default '1',<br />
        `required` INT(1) NOT NULL default '0',<br />
        `canremove` INT(1) NOT NULL default '1',<br />
        PRIMARY KEY  (`fieldid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "bid_fields_answers (<br />
        `answerid` INT(10) NOT NULL AUTO_INCREMENT,<br />
        `fieldid` INT(10) NOT NULL default '0',<br />
        `project_id` INT(10) NOT NULL default '0',
        `bid_id` INT(10) NOT NULL default '0',<br />
        `answer` MEDIUMTEXT,<br />
        `date` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
        `visible` INT(1) NOT NULL default '1',<br />
        PRIMARY KEY  (`answerid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "categories` ADD `bidfields` MEDIUMTEXT NOT NULL AFTER `usereserveprice`<br /><br />";
        
        echo "RENAME TABLE `" . DB_PREFIX . "rating_criteria` TO `" . DB_PREFIX . "feedback_criteria`";
        
        echo "ALTER TABLE `" . DB_PREFIX . "buynow_orders`<br />
        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `paiddate` ,<br />
        ADD `sellerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `buyerfeedback`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects_escrow`<br />
        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `qty` ,<br />
        ADD `sellerfeedback` INT NOT NULL DEFAULT '0' AFTER `buyerfeedback`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects`<br />
        ADD `buyerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `returnpolicy` ,<br />
        ADD `sellerfeedback` INT( 1 ) NOT NULL DEFAULT '0' AFTER `buyerfeedback`<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "subscription_permissions VALUES (NULL, 1, 'newsletteropt_in', 'Newsletter Resources', 'Defines if any customer within this subscription group can opt-in to any of the available newsletter resources', 'yesno', 'yes', 0, 1, 0, 1)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "project_bids` ADD `isshortlisted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isproxybid`<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('servicebid_awardwaitperiod', 'If a buyer awarded a service provider how long should the marketplace wait for the provider to accept the buyers award (in days)?', '7', 'servicebid_limits', 'int', '', '', 'This setting ensures that a buyers project does not get a deadbeat provider delaying the project.  This setting automatically resets the buyers project to open if there is any time left and respectively declines the providers awarded bid so others can become awarded.', 10)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "project_realtimebids`<br />
        ADD `isproxybid` INT( 1 ) NOT NULL DEFAULT '0' AFTER `state`,<br />
        ADD `isshortlisted` INT( 1 ) NOT NULL DEFAULT '0' AFTER `isproxybid`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "feedback_response (<br />
        `id` INT(100) NOT NULL AUTO_INCREMENT,<br />
        `feedbackid` INT(10) NOT NULL default '0',<br />
        `project_id` INT(10) NOT NULL default '0',<br />
        `from_user_id` INT(10) NOT NULL default '0',<br />
        `comments` mediumtext,<br />
        `date_added` datetime NOT NULL default '0000-00-00 00:00:00',<br />
        `type` enum('','buyer','seller') NOT NULL,<br />
        PRIMARY KEY  (`id`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_refresh', 'Would you like to enable the Refresh splash screen when an action has been performed on the marketplace?', '1', 'globalfilterresults', 'yesno', '', '', 'This option will produce a message to the user asking them to please wait while your request has been completed.  When disabled the refresh logic will immediately redirect the user without any message shown.', 10)<br /><br />";
        
        $defaultsearchoptions = 'a:19:{s:7:"perpage";s:2:"10";s:4:"sort";s:3:"0|1";s:8:"username";s:4:"true";s:14:"latestfeedback";s:4:"true";s:6:"online";s:4:"true";s:15:"displayfeatured";s:4:"true";s:10:"showtimeas";s:6:"static";s:11:"description";s:4:"true";s:5:"icons";s:4:"true";s:15:"currencyconvert";s:4:"true";s:8:"proxybit";s:4:"true";s:4:"list";s:4:"list";s:15:"serviceselected";a:7:{i:0;s:5:"title";i:1;s:4:"bids";i:2;s:8:"timeleft";i:3;s:10:"averagebid";i:4;s:8:"category";i:5;s:7:"country";i:6;s:3:"sel";}s:15:"productselected";a:8:{i:0;s:6:"sample";i:1;s:5:"title";i:2;s:5:"price";i:3;s:8:"shipping";i:4;s:4:"bids";i:5;s:8:"timeleft";i:6;s:7:"country";i:7;s:3:"sel";}s:14:"expertselected";a:11:{i:0;s:11:"profilelogo";i:1;s:6:"expert";i:2;s:11:"rateperhour";i:3;s:11:"credentials";i:4;s:8:"feedback";i:5;s:5:"rated";i:6;s:8:"earnings";i:7;s:9:"portfolio";i:8;s:6:"awards";i:9;s:7:"country";i:10;s:3:"sel";}s:13:"defaultupdate";s:4:"true";s:13:"membersupdate";s:4:"true";s:10:"hidelisted";s:5:"false";s:11:"hideverbose";s:5:"false";}';
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('searchdefaultcolumns', 'This will contain specific code data used by the advanced search options menu.  Do not manually edit.', '" . $ilance->db->escape_string($defaultsearchoptions) . "', 'search', 'text', '', '', 'This option is used as a datastore for the presentation of the search results columns displayed in the marketplace.  Please do not edit this string.', 30)<br /><br />";
        unset($defaultsearchoptions);
        
        echo "ALTER TABLE `" . DB_PREFIX . "profile_filter_auction_answers` ADD `filtertype` ENUM( 'range', 'checkbox', 'pulldown' ) NOT NULL AFTER `answer`";
        
        echo "CREATE TABLE " . DB_PREFIX . "search_users (<br />
        `id` INT(100) NOT NULL AUTO_INCREMENT,<br />
        `user_id` INT(10) NOT NULL,<br />
        `keyword` MEDIUMTEXT,<br />
        `added` DATETIME NOT NULL default '0000-00-00 00:00:00',<br />
        PRIMARY KEY  (`id`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "language` ADD `replacements` MEDIUMTEXT NOT NULL AFTER `installdate`<br /><br />";
        echo "DELETE FROM " . DB_PREFIX . "configuration WHERE name = 'globalauctionsettings_seoreplacements'<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('skills', 'skills', 'Skills Options and Settings', 'Skills provide your members to precisely define their expertise by selecting various categories that you create in the system.  End users can use the advanced search system to narrow down specific expertise as required.', 'tablehead_alt', '450')<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "configuration` ADD `visible` INT( 1 ) NOT NULL DEFAULT '1' AFTER `sort`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "payment_methods (<br />
        `id` INT(10) NOT NULL AUTO_INCREMENT,<br />
        `title` MEDIUMTEXT,<br />
        `sort` INT(5) NOT NULL,<br />
        PRIMARY KEY  (`id`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'See item description for payment methods accepted', 10)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card', 20)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Visa', 30)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Debit Card', 40)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal', 50)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Money Order', 60)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Personal Check', 70)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa', 80)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Debit Card', 90)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, PayPal', 100)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Visa, PayPal', 110)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Money Order', 120)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Personal Check, Money Order', 130)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Personal Check, Money Order', 140)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Money Order', 150)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Master Card, Money Order', 160)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Money Order', 170)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'Master Card, Visa, Debit, Money order', 180)<br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_methods VALUES (NULL, 'PayPal, Master, Visa, Debit, Money order', 190)<br /><br />";
	
	echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enableskills', 'Would you like to enable the Skills system?', '1', 'skills', 'yesno', '', '', 'This option will allow users to make use of the skills system.  They can opt into various skill categories and can be searched based on skills from the advanced search menu.', 10, 1)<br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('enablepopulartags', 'Would you like to display the popular search tags on the main menu?', '1', 'globalfilterresults', 'yesno', '', '', 'The popular search tags feature produces realtime keyword search tags queried within the marketplace.', 100, 1)<br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartagcount', 'How many searches does it take before a tag becomes popular?', '30', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many times a keyword tag needs to be searched by users in the system before it becomes popular and displayed within the popular search tag area.', 200, 1)<br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('populartaglimit', 'How many popular keyword to display at once?', '50', 'globalfilterresults', 'int', '', '', 'This setting lets you define how many actual popular keyword tags will be displayed at any given time when users are viewing the popular keyword tags.', 300, 1)<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "search_favorites` ADD `lastsent` DATETIME NOT NULL AFTER `added`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "search_favorites` ADD `lastseenids` MEDIUMTEXT NOT NULL AFTER `lastsent`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "distance_au (<br />
        `ZIPCode` INT(5) NOT NULL,<br />
        `City` VARCHAR(39) NOT NULL,<br />
        `State` VARCHAR(3) NOT NULL,<br />
        `Longitude` DOUBLE default NULL,<br />
        `Latitude` DOUBLE default NULL,<br />
        KEY `ZIPCode` (`ZIPCode`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "distance_pl (<br />
        `ZIPCode` varchar(255) default NULL,<br />
        `City` varchar(255) default NULL,<br />
        `Latitude` varchar(255) default NULL,<br />
        `Longitude` varchar(255) default NULL,<br />
        `State` varchar(255) default NULL,<br />
        KEY `ZIPCode` (`ZIPCode`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "distance_de (<br />
        `ZIPCode` varchar(255) default NULL,<br />
        `City` varchar(255) default NULL,<br />
        `Latitude` varchar(255) default NULL,<br />
        `Longitude` varchar(255) default NULL,<br />
        `State` varchar(255) default NULL,<br />
        KEY `ZIPCode` (`ZIPCode`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('fulltextsearch', 'Would you like to enable Fulltext search?', '1', 'search', 'yesno', '', '', 'Boolean fulltext mode queries became available in MySQL in version 4, and allow expressions to make use of a complex set of boolean rules to let users refine their searches. These queries are very powerful when applied to fulltext searching and sorting of results when enabled.', 30, 1)";
        
        echo "ALTER TABLE `" . DB_PREFIX . "language_phrasegroups` DROP `id`<br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language_phrasegroups` ADD PRIMARY KEY (`groupname`)<br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language_phrases` ADD `phrasegroup` MEDIUMTEXT NOT NULL AFTER `phraseid`<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'accounting' WHERE phrasegroupid = '1'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'main' WHERE phrasegroupid = '2'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'search' WHERE phrasegroupid = '3'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'preferences' WHERE phrasegroupid = '4'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'registration' WHERE phrasegroupid = '5'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'subscription' WHERE phrasegroupid = '6'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'ipn' WHERE phrasegroupid = '7'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'buying' WHERE phrasegroupid = '8'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'feedback' WHERE phrasegroupid = '9'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'rfp' WHERE phrasegroupid = '10'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'watchlist' WHERE phrasegroupid = '11'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'selling' WHERE phrasegroupid = '12'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'pmb' WHERE phrasegroupid = '13'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'cron' WHERE phrasegroupid = '14'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'portfolio' WHERE phrasegroupid = '15'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'administration' WHERE phrasegroupid = '16'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'livebid' WHERE phrasegroupid = '17'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'mediashare' WHERE phrasegroupid = '18'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'livesync' WHERE phrasegroupid = '19'<br />";
        echo "UPDATE " . DB_PREFIX . "language_phrases SET phrasegroup = 'javascript' WHERE phrasegroupid = '20'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "language_phrases` DROP `phrasegroupid`<br /><br />";
        echo "DELETE FROM `" . DB_PREFIX . "language_phrasegroups` WHERE groupname = 'livesync'<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('savedsearches', 'Would you like to enable Saved Searches?', '1', 'search', 'yesno', '', '', 'This feature allows logged in members the ability to save their search when searching for products or services.  Additionally, users can subscribe via email so when new matches are met they receive a new email with results.', 40, 1)<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "projects` ADD `returnshippaidby` ENUM( 'none', 'buyer', 'seller' ) NOT NULL DEFAULT 'none' AFTER `returngivenas`<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserversettings_companyname', 'Company name', 'Company Name', 'globalserversettings', 'text', '', '', 'This company name should not be confused with your Site title or Site Name.  For example, ILance Inc. is a company name, ILance Marketplace could be the Site title / name.  Company name will only show on invoices and transactions generated from the company to the users.', 1, 1)<br /><br />";
        echo "DELETE FROM `" . DB_PREFIX . "payment_configuration` WHERE name = 'registration_cc_pay_attempts'<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'save_credit_cards', 'Would you like to save credit cards in the database?', '0', 'defaultgateway', 'yesno', '', '', '', 1)<br /><br />";
        echo "UPDATE " . DB_PREFIX . "profile_groups SET cid = '-1' WHERE name = 'All Profile Categories'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "modules_group` ADD `filestructure` MEDIUMTEXT NOT NULL AFTER `developer`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "modules_group`<br />
        ADD `installdate` DATETIME NOT NULL AFTER `filestructure`,<br />
        ADD `upgradedate` DATETIME NOT NULL AFTER `installdate`<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "users` ADD `profileintro` MEDIUMTEXT NOT NULL AFTER `profilevideourl`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "search` ADD `searchmode` MEDIUMTEXT NOT NULL AFTER `keyword`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "search_users` ADD `searchmode` MEDIUMTEXT NOT NULL AFTER `keyword`<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "projects` ADD `description_videourl` MEDIUMTEXT NOT NULL AFTER `description`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "projects_trackbacks (<br />
        `trackbackid` INT(100) NOT NULL AUTO_INCREMENT,<br />
        `project_id` INT(50) NOT NULL default '0',<br />
        `ipaddress` MEDIUMTEXT,<br />
        `url` MEDIUMTEXT,<br />
        `visible` INT(1) NOT NULL default '1',<br />
        PRIMARY KEY (`trackbackid`)<br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo '<hr size="1" width="100%" />';
        
        echo "UPDATE " . DB_PREFIX . "configuration SET value = '11' WHERE name = 'current_sql_version'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET value = '3.1.7' WHERE name = 'current_version'<br /><br />";
        
        echo '<hr size="1" width="100%" />';
        
        echo '<strong><a href="installer.php?do=install&step=27&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, templates and phrases for you)';
}
?>