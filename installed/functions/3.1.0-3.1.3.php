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
        echo '<h1>Upgrade 3.1.1 or 3.1.2 to 3.1.3</h1><p>Updating database...</p>';
        
        $sql = $ilance->db->query("SELECT value FROM " . DB_PREFIX . "configuration WHERE name = 'current_sql_version'", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        if ($res['value'] == '5')
        {
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "emaillog`
                        CHANGE `logtype` `logtype`
                        ENUM('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','dailyfavorites','watchlist')
                        NOT NULL DEFAULT 'alert'                   
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "cron` ADD `product` VARCHAR(200) NOT NULL AFTER `varname`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM `" . DB_PREFIX . "cron` WHERE filename = 'cron.warnings.php'                   
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "users` DROP warnings
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "users` DROP warning_level
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "users` DROP warning_bans
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DROP TABLE IF EXISTS " . DB_PREFIX . "sessions_acp
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "timezones (
                        `timezoneid` VARCHAR(5) NOT NULL,
                        `timezone` VARCHAR(200) NOT NULL default '',
                        `sort` INT(5) NOT NULL default '0')
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-12, '(UTC -12:00) Baker Island Time', 1)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-11, '(UTC -11:00) Niue Time, Samoa Standard Time', 2)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-10, '(UTC -10:00) Hawaii-Aleutian Standard Time', 3)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-9.5, '(UTC -9:30) Marquesas Islands Time', 4)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-9, '(UTC -9:00) Alaska Standard Time', 5)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-8, '(UTC -8:00) Pacific Standard Time', 6)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-7, '(UTC -7:00) Mountain Standard Time', 7)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-6, '(UTC -6:00) Central Standard Time', 8)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-5, '(UTC -5:00) Eastern Standard Time', 9)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-4, '(UTC -4:00) Atlantic Standard Time', 10)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-3.5, '(UTC -3:30) Newfoundland Standard Time', 11)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-3, '(UTC -3:00) Amazon Standard Time', 12)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-2, '(UTC -2:00) Fernando de Noronha Time, South Georgia Time', 13)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (-1, '(UTC -1:00) Azores Standard Time, Eastern Greenland Time', 14)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (0, '(UTC) Western European Time, Greenwich Mean Time', 15)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (1, '(UTC +1:00) Central European Time, West African Time', 16)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (2, '(UTC +2:00) Eastern European Time, Central African Time', 17)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (3, '(UTC +3:00) Moscow Standard Time, Eastern African Time', 18)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (3.5, '(UTC +3:30) Iran Standard Time', 19)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (4, '(UTC +4:00) Gulf Standard Time, Samara Standard Time', 20)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (4.5, '(UTC +4:30) Afghanistan Time', 21)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5, '(UTC +5:00) Pakistan Standard Time', 22)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5.5, '(UTC +5:30) Indian Standard Time, Sri Lanka Time', 23)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (5.75, '(UTC +5:45) Nepal Time', 24)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (6, '(UTC +6:00) Bangladesh Time, Bhutan Time', 25)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (6.5, '(UTC +6:30) Cocos Islands Time, Myanmar Time', 26)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (7, '(UTC +7:00) Indochina Time, Krasnoyarsk Standard Time', 27)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (8, '(UTC +8:00) Chinese Standard Time', 28)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (8.75, '(UTC +8:45) Southeastern Western Australia Standard Time', 29)", 0, null, __FILE__, __LINE__);	
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (9, '(UTC +9:00) Japan Standard Time, Korea Standard Time', 30)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (9.5, '(UTC +9:30) Australian Central Standard Time', 31)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (10, '(UTC +10:00) Australian Eastern Standard Time', 32)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (10.5, '(UTC +10:30) Lord Howe Standard Time', 33)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (11, '(UTC +11:00) Solomon Island Time, Magadan Standard Time', 34)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (11.5, '(UTC +11:30) Norfolk Island Time', 35)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (12, '(UTC +12:00) New Zealand Time, Fiji Time', 36)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (12.75, '(UTC +12:45) Chatham Islands Time', 37)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (13, '(UTC +13:00) Tonga Time, Phoenix Islands Time', 38)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "timezones VALUES (14, '(UTC +14:00) Line Island Time', 39)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "templates CHANGE `type` `type` ENUM( 'common', 'static', 'dynamic', 'variable', 'css' ) NOT NULL DEFAULT 'static'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "email ADD `product` VARCHAR( 100 ) NOT NULL DEFAULT 'ilance' AFTER `type`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "email WHERE varname = 'cron_warnings_ban_removed'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "currency VALUES (NULL, 'RON', 'ROMANIA NEW LEI', '3.3751', '2007-03-01 10:17:47', 1, 'RON', '', '.', ',', '2')", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects ADD `invoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `fvf`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'cc_directpayment'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE `" . DB_PREFIX . "configuration`
                        SET value = '6'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '3.1.3'
                        WHERE name = 'current_version'
                ", 0, null, __FILE__, __LINE__);
                
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
        echo '<h1>Upgrade from 3.1.1 or 3.1.2 to 3.1.3</h1><p>The following SQL query will be executed:</p>';    
        echo '<hr size="1" width="100%" />';
        
        echo "ALTER TABLE `" . DB_PREFIX . "emaillog`<br />CHANGE `logtype` `logtype` ENUM('escrow','subscription','subscriptionremind','send2friend','alert','queue','dailyservice','dailyproduct','dailyreport','dailyfavorites','watchlist') NOT NULL DEFAULT 'alert'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "cron` ADD `product` VARCHAR(200) NOT NULL AFTER `varname`<br /><br />";
        
        echo "DELETE FROM `" . DB_PREFIX . "cron` WHERE filename = 'cron.warnings.php'<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "users` DROP warnings<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "users` DROP warning_level<br /><br />";
        echo "ALTER TABLE `" . DB_PREFIX . "users` DROP warning_bans<br /><br />";
        
        echo "DROP TABLE IF EXISTS " . DB_PREFIX . "sessions_acp<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "timezones (<br />
        `timezoneid` VARCHAR(10) NOT NULL,<br />
        `timezone` VARCHAR(200) NOT NULL default '',<br />
        `sort` INT(5) NOT NULL default '0')<br />";
        
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-12, '(UTC -12:00) Baker Island Time', 1)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-11, '(UTC -11:00) Niue Time, Samoa Standard Time', 2)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-10, '(UTC -10:00) Hawaii-Aleutian Standard Time', 3)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-9.5, '(UTC -9:30) Marquesas Islands Time', 4)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-9, '(UTC -9:00) Alaska Standard Time', 5)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-8, '(UTC -8:00) Pacific Standard Time', 6)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-7, '(UTC -7:00) Mountain Standard Time', 7)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-6, '(UTC -6:00) Central Standard Time', 8)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-5, '(UTC -5:00) Eastern Standard Time', 9)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-4, '(UTC -4:00) Atlantic Standard Time', 10)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-3.5, '(UTC -3:30) Newfoundland Standard Time', 11)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-3, '(UTC -3:00) Amazon Standard Time', 12)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-2, '(UTC -2:00) Fernando de Noronha Time, South Georgia Time', 13)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (-1, '(UTC -1:00) Azores Standard Time, Eastern Greenland Time', 14)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (0, '(UTC) Western European Time, Greenwich Mean Time', 15)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (1, '(UTC +1:00) Central European Time, West African Time', 16)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (2, '(UTC +2:00) Eastern European Time, Central African Time', 17)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (3, '(UTC +3:00) Moscow Standard Time, Eastern African Time', 18)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (3.5, '(UTC +3:30) Iran Standard Time', 19)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (4, '(UTC +4:00) Gulf Standard Time, Samara Standard Time', 20)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (4.5, '(UTC +4:30) Afghanistan Time', 21)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (5, '(UTC +5:00) Pakistan Standard Time', 22)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (5.5, '(UTC +5:30) Indian Standard Time, Sri Lanka Time', 23)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (5.75, '(UTC +5:45) Nepal Time', 24)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (6, '(UTC +6:00) Bangladesh Time, Bhutan Time', 25)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (6.5, '(UTC +6:30) Cocos Islands Time, Myanmar Time', 26)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (7, '(UTC +7:00) Indochina Time, Krasnoyarsk Standard Time', 27)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (8, '(UTC +8:00) Chinese Standard Time', 28)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (8.75, '(UTC +8:45) Southeastern Western Australia Standard Time', 29)<br />";	
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (9, '(UTC +9:00) Japan Standard Time, Korea Standard Time', 30)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (9.5, '(UTC +9:30) Australian Central Standard Time', 31)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (10, '(UTC +10:00) Australian Eastern Standard Time', 32)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (10.5, '(UTC +10:30) Lord Howe Standard Time', 33)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (11, '(UTC +11:00) Solomon Island Time, Magadan Standard Time', 34)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (11.5, '(UTC +11:30) Norfolk Island Time', 35)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (12, '(UTC +12:00) New Zealand Time, Fiji Time', 36)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (12.75, '(UTC +12:45) Chatham Islands Time', 37)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (13, '(UTC +13:00) Tonga Time, Phoenix Islands Time', 38)<br />";
        echo "INSERT INTO " . DB_PREFIX . "timezones VALUES (14, '(UTC +14:00) Line Island Time', 39)<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "templates CHANGE `type` `type` ENUM( 'common', 'static', 'dynamic', 'variable', 'css' ) NOT NULL DEFAULT 'static'<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "email ADD `product` VARCHAR( 100 ) NOT NULL DEFAULT 'ilance' AFTER `type`<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "email WHERE varname = 'cron_warnings_ban_removed'<br /><br />";
        
        echo "INSERT INTO " . DB_PREFIX . "currency VALUES (NULL, 'RON', 'ROMANIA NEW LEI', '3.3751', '2007-03-01 10:17:47', 1, 'RON', '', '.', ',', '2')<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "projects` ADD `invoiceid` INT( 10 ) NOT NULL DEFAULT '0' AFTER `fvf`<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'cc_directpayment'<br /><br />";
        
        echo "UPDATE `" . DB_PREFIX . "configuration` SET value = '6' WHERE name = 'current_sql_version'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET value = '3.1.3' WHERE name = 'current_version'<br /><br />";
        
        echo '<hr size="1" width="100%" />';    
        echo '<strong><a href="installer.php?do=install&step=23&execute=1">Execute</a></strong> this SQL query';
}
?>