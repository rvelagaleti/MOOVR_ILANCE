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
        echo '<h1>Upgrade 3.1.5 to 3.1.6</h1><p>Updating database...</p>';
        
        $sql = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'current_sql_version'
        ", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        if ($res['value'] == '8')
        {
                // add url to the project questions
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "language ADD `htmlextra` VARCHAR( 250 ) NOT NULL AFTER `htmldoctype`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration
                        VALUES (
                        NULL,
                        0,
                        'refreshcsscache',
                        'Enable CSS cache regeneration? (when disabled will not overwrite your CSS cache files within ./cache/)',
                        '1',
                        'template',
                        'yesno',
                        '',
                        '',
                        '',
                        2)
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration_groups
                        VALUES (
                        NULL,
                        'language',
                        'language',
                        'Language Settings',
                        '')
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration
                        VALUES (
                        NULL,
                        0,
                        'externaljsphrases',
                        'Enable external javascript phrase cache file? (disabled will create inline document phrases)',
                        '0',
                        'language',
                        'yesno',
                        '',
                        '',
                        '',
                        1)
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "rating_criteria CHANGE `title` `title_eng` MEDIUMTEXT NOT NULL", 0, null, __FILE__, __LINE__);
                
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
                                $ilance->db->query("ALTER TABLE " . DB_PREFIX . "rating_criteria ADD title_" . $slng . " VARCHAR(100) NOT NULL AFTER `id`", 0, null, __FILE__, __LINE__);
                                $ilance->db->query("UPDATE " . DB_PREFIX . "rating_criteria SET title_" . $slng . " = title_eng", 0, null, __FILE__, __LINE__);
                        }
                }
                
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration
                        VALUES (
                        NULL,
                        0,
                        'categorymapcache',
                        'Enable static category template caching? (will create html templates within ./cache/ speeding up the category maps and pulldown menus when displayed)',
                        '0',
                        'globalauctionsettings',
                        'yesno',
                        '',
                        '',
                        '',
                        300)
                ", 0, null, __FILE__, __LINE__);
                $ilance->db->query("
                        INSERT INTO " . DB_PREFIX . "configuration
                        VALUES (
                        NULL,
                        0,
                        'categorymapcachetimeout',
                        'Enter the time to live (in minutes) when the static category template caching should regenerate',
                        '30',
                        'globalauctionsettings',
                        'int',
                        '',
                        '',
                        '',
                        310)
                ", 0, null, __FILE__, __LINE__);

		$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD `visible` INT(1) NOT NULL default '1' AFTER `iscustom`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE ".DB_PREFIX."configuration
                        SET value = '9'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE ".DB_PREFIX."configuration
                        SET value = '3.1.6'
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
        echo '<h1>Upgrade from 3.1.5 to 3.1.6</h1><p>The following SQL query will be executed:</p>';    
        echo '<hr size="1" width="100%" />';

        echo "ALTER TABLE " . DB_PREFIX . "language ADD `htmlextra` VARCHAR( 250 ) NOT NULL AFTER `htmldoctype`<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'refreshcsscache', 'Enable CSS cache regeneration? (when disabled will not overwrite your CSS cache files within ./cache/)', '1', 'template', 'yesno', '', '', '', 2)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES (NULL, 'language', 'language', 'Language Settings', '')<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'externaljsphrases', 'Enable external javascript phrase cache file? (disabled will create inline document phrases)', '0', 'language', 'yesno', '', '', '', 1)<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "rating_criteria CHANGE `title` `title_eng` MEDIUMTEXT NOT NULL<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "subscription_permissions ADD `visible` INT(1) NOT NULL default '1' AFTER `iscustom`";
        echo '<hr size="1" width="100%" />';
        
        echo "UPDATE `".DB_PREFIX."configuration` SET value = '9' WHERE name = 'current_sql_version'<br /><br />";
        echo "UPDATE ".DB_PREFIX."configuration SET value = '3.1.6' WHERE name = 'current_version'<br /><br />";
        
        echo '<hr size="1" width="100%" />';
        
        echo '<strong><a href="installer.php?do=install&step=26&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, templates and phrases for you)';
}
?>