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
        echo '<h1>Upgrade 3.1.3 to 3.1.4</h1><p>Updating database...</p>';
        
        $sql = $ilance->db->query("
                SELECT value
                FROM " . DB_PREFIX . "configuration
                WHERE name = 'current_sql_version'
        ", 0, null, __FILE__, __LINE__);
        $res = $ilance->db->fetch_array($sql);
        if ($res['value'] == '6')
        {
                $ilance->subscription_plan->add_subscription_permissions($accesstext = 'Maximum Profile Categories Opt-In', $accessdescription = 'Maximum amount of selectable profile categories a user within this subscription can opt-in', $accessname = 'maxprofilegroups', $accesstype = 'int', $value = 5, $canremove = 0);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "templates
                        SET original = 'templates/default/', content = 'templates/default/'
                        WHERE name = 'template_folder'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "templates
                        WHERE type = 'common'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        DELETE FROM " . DB_PREFIX . "templates
                        WHERE type = 'static'", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "templates`
                        CHANGE `type` `type` ENUM('variable', 'cssclient', 'cssadmin', 'csswysiwyg', 'csstabs', 'csscommon')
                        NOT NULL DEFAULT 'variable'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("DROP TABLE IF EXISTS " . DB_PREFIX . "template_groups", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "templates`
                        CHANGE `name` `name` VARCHAR( 250 ) NOT NULL
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "project_questions`
                        CHANGE `inputtype` `inputtype`
                        ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' )
                        NOT NULL DEFAULT 'text'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "product_questions`
                        CHANGE `inputtype` `inputtype`
                        ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' )
                        NOT NULL DEFAULT 'text'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "profile_questions`
                        CHANGE `inputtype` `inputtype`
                        ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range')
                        NOT NULL DEFAULT 'text'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "register_questions`
                        CHANGE `inputtype` `inputtype`
                        ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range')
                        NOT NULL DEFAULT 'text' 
                ", 0, null, __FILE__, __LINE__);
                
                // new withdraw fee options for check withdrawal requests
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'check', 'yesno', '', '', '', 40)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'check', 'int', '', '', '', 50)", 0, null, __FILE__, __LINE__);
                
                // new withdraw fee options for bank transfer withdrawal requests
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'bank', 'yesno', '', '', '', 40)", 0, null, __FILE__, __LINE__);
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'bank', 'int', '', '', '', 50)", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET description = 'Highlight background color CSS class',
                        value = 'featured_highlight',
                        inputtype = 'text'
                        WHERE name = 'productupsell_highlightcolor'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET description = 'Highlight background color CSS class',
                        value = 'featured_highlight',
                        inputtype = 'text'
                        WHERE name = 'serviceupsell_highlightcolor'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "templates DROP `gid`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "styles DROP `cssclient`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "styles DROP `cssadmin`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "styles DROP `csswysiwyg`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "styles DROP `csstabs`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "styles DROP `csscustom`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "profile_questions
                        ADD `isfilter` INT( 1 ) NOT NULL DEFAULT '0' AFTER `verifycost`,
                        ADD `filtertype` ENUM('pulldown', 'multiplechoice', 'range') NOT NULL DEFAULT 'pulldown' AFTER `isfilter`,
                        ADD `filtercategory` INT( 10 ) NOT NULL DEFAULT '0' AFTER `filtertype`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "users
                        ADD `timeonsite` INT( 10 ) NOT NULL AFTER `usecompanyname`,
                        ADD `daysonsite` INT( 10 ) NOT NULL AFTER `timeonsite`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE " . DB_PREFIX . "email
                        ADD `subject_original` VARCHAR( 255 ) NOT NULL AFTER `name`,
                        ADD `message_original` TEXT NOT NULL AFTER `subject_original`
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        CREATE TABLE " . DB_PREFIX . "profile_filter_auction_answers (
                        `answerid` INT( 10 ) NOT NULL AUTO_INCREMENT,
                        `questionid` INT( 10 ) NOT NULL ,
                        `project_id` INT( 10 ) NOT NULL ,
                        `user_id` INT( 10 ) NOT NULL ,
                        `answer` TEXT NOT NULL ,
                        `date` DATETIME NOT NULL ,
                        `visible` INT( 1 ) NOT NULL ,
                        PRIMARY KEY answerid (`answerid`)
                        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        ALTER TABLE `" . DB_PREFIX . "invoices`
                        ADD `withdrawinvoiceid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `iswithdraw`", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '7'
                        WHERE name = 'current_sql_version'
                ", 0, null, __FILE__, __LINE__);
                
                $ilance->db->query("
                        UPDATE " . DB_PREFIX . "configuration
                        SET value = '3.1.4'
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
        echo '<h1>Upgrade from 3.1.3 to 3.1.4</h1><p>The following SQL query will be executed:</p>';    
        echo '<hr size="1" width="100%" />';
        
        echo "PHP:<br /><br />$ilance->subscription_plan->add_subscription_permissions(\$accesstext = 'Maximum Profile Categories Opt-In', \$accessdescription = 'Maximum amount of selectable profile categories a user within this subscription can opt-in', \$accessname = 'maxprofilegroups', \$accesstype = 'int', \$value = 5, \$canremove = 0);";
        
        echo '<hr size="1" width="100%" />';
        
        echo "UPDATE " . DB_PREFIX . "templates SET original = 'templates/default/', content = 'templates/default/' WHERE name = 'template_folder'<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "templates WHERE type = 'common'<br /><br />";
        
        echo "DELETE FROM " . DB_PREFIX . "templates WHERE type = 'static'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "templates` CHANGE `type` `type` ENUM('variable', 'cssclient', 'cssadmin', 'csswysiwyg', 'csstabs', 'csscommon') NOT NULL DEFAULT 'variable'<br /><br />";
        
        echo "DROP TABLE IF EXISTS " . DB_PREFIX . "template_groups<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "templates` CHANGE `name` `name` VARCHAR( 250 ) NOT NULL<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "project_questions` CHANGE `inputtype` `inputtype` ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' ) NOT NULL DEFAULT 'text'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "product_questions` CHANGE `inputtype` `inputtype` ENUM('yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' ) NOT NULL DEFAULT 'text'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "profile_questions` CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' ) NOT NULL DEFAULT 'text'<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "register_questions` CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown', 'range' ) NOT NULL DEFAULT 'text'<br /><br />";
        
        // new withdraw fee options for check withdrawal requests
        echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'check', 'yesno', '', '', '', 40)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'check_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'check', 'int', '', '', '', 50)<br /><br />";
        
        // new withdraw fee options for bank transfer withdrawal requests
        echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee_active', 'Enable withdraw payment usage fees?', '1', 'bank', 'yesno', '', '', '', 40)<br /><br />";
        echo "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bank_withdraw_fee', 'Enter the withdraw usage fee amount', '5.00', 'bank', 'int', '', '', '', 50)<br /><br />";
        
        echo "UPDATE " . DB_PREFIX . "configuration SET description = 'Highlight background color CSS class', value = 'featured_highlight', inputtype = 'text' WHERE name = 'productupsell_highlightcolor'<br /><br />";        
        echo "UPDATE " . DB_PREFIX . "configuration SET description = 'Highlight background color CSS class', value = 'featured_highlight', inputtype = 'text' WHERE name = 'serviceupsell_highlightcolor'<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "templates DROP `gid`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "styles DROP `cssclient`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "styles DROP `cssadmin`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "styles DROP `csswysiwyg`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "styles DROP `csstabs`<br /><br />";
        echo "ALTER TABLE " . DB_PREFIX . "styles DROP `csscustom`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "profile_questions ADD `isfilter` INT( 1 ) NOT NULL DEFAULT '0' AFTER `verifycost`, ADD `filtertype` ENUM('pulldown', 'multiplechoice', 'range') NOT NULL DEFAULT 'pulldown' AFTER `isfilter`, ADD `filtercategory` INT( 10 ) NOT NULL AFTER `filtertype`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "users ADD `timeonsite` INT( 10 ) NOT NULL AFTER `usecompanyname`, ADD `daysonsite` INT( 10 ) NOT NULL AFTER `timeonsite`<br /><br />";
        
        echo "ALTER TABLE " . DB_PREFIX . "email ADD `subject_original` VARCHAR( 255 ) NOT NULL AFTER `name`, ADD `message_original` TEXT NOT NULL AFTER `subject_original`<br /><br />";
        
        echo "CREATE TABLE " . DB_PREFIX . "profile_filter_auction_answers (<br />
        `answerid` INT( 10 ) NOT NULL AUTO_INCREMENT,<br />
        `questionid` INT( 10 ) NOT NULL ,<br />
        `project_id` INT( 10 ) NOT NULL ,<br />
        `user_id` INT( 10 ) NOT NULL ,<br />
        `answer` TEXT NOT NULL ,<br />
        `date` DATETIME NOT NULL ,<br />
        `visible` INT( 1 ) NOT NULL, <br />
        PRIMARY KEY answerid (`answerid`) <br />
        ) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . "<br /><br />";
        
        echo "ALTER TABLE `" . DB_PREFIX . "invoices` ADD `withdrawinvoiceid` INT( 5 ) NOT NULL DEFAULT '0' AFTER `iswithdraw`<br /><br />";
        
        echo "UPDATE `" . DB_PREFIX . "configuration` SET value = '7' WHERE name = 'current_sql_version'<br /><br />";
        echo "UPDATE " . DB_PREFIX . "configuration SET value = '3.1.4' WHERE name = 'current_version'<br /><br />";
        
        echo '<hr size="1" width="100%" />';
        
        echo '<strong>If you are manually entering these values to your phpmyadmin, you will need to import new ilance-phrases.xml, ilance-emails.xml and ilance-templates.xml within the AdminCP so you have latest templates and phrases.  If you are going to execute these queries by clicking the execute link below, then this process will automatically upgrade your templates and phrases for you (to prevent an additional step).</strong>';
        
        echo '<hr size="1" width="100%" />';
        echo '<strong><a href="installer.php?do=install&step=24&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, templates and phrases for you)';
}
?>