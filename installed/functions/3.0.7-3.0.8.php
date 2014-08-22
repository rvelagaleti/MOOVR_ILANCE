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

echo '<h1>Upgrade 3.0.7 to 3.0.8</h1><p>Updating database...</p>';
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "payment_configuration DROP `parentid`", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'psigate', 'psigate', 'PSIGate Gateway Configuration', '', 'gateway')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_groups VALUES (NULL, 'eway', 'eway', 'eWAY Gateway Configuration', '', 'gateway')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'PSIGate (Live Gateway)', 'psigate', 'text', '', '', '', 1)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'eWAY (Live Gateway)', 'eway', 'text', '', '', '', 1)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'psigate_enabled', 'Enable PSIGate gateway module?', '0', 'psigate', 'yesno', '', '', '', 2)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'eway_enabled', 'Enable eWAY gateway module?', '0', 'eway', 'yesno', '', '', '', 2)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your PSIGate StoreID', 'teststore', 'psigate', 'text', '', '', '', 3)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_login', 'Enter your eWAY ClientID', '87654321', 'eway', 'text', '', '', '', 3)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_password', 'Enter your PSIGate passphrase', 'psigate1234', 'psigate', 'pass', '', '', '', 4)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'Enter a transaction usage fee [value in percentage; i.e: 1.9]', '2.2', 'psigate', 'int', '', '', '', 5)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'cc_transaction_fee', 'Enter a transaction usage fee [value in percentage; i.e: 1.9]', '2.2', 'eway', 'int', '', '', '', 4)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'PSIGate credit card authentication process capture mode [charge]?', 'charge', 'psigate', 'text', '', '', '', 6)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_capture', 'eWAY credit card authentication process capture mode [charge]?', 'charge', 'eway', 'text', '', '', '', 5)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'PSIGate credit card authentication process refund mode [credit]?', 'credit', 'psigate', 'text', '', '', '', 7)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authentication_refund', 'eWAY credit card authentication process refund mode [credit]?', 'credit', 'eway', 'text', '', '', '', 6)", 0, null, __FILE__, __LINE__);
$ilance->db->query("DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'authnet_debug' LIMIT 1", 0, null, __FILE__, __LINE__);

$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids DROP `bidlock_amount`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids DROP `lowbidnotify`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids DROP `lasthournotify`", 0, null, __FILE__, __LINE__);

// remove obsolete "type" and "usertype" columns - unused and replaced with user roles.
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "project_bids DROP `user_type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "invoices DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "preferences DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "accountdata DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "creditcards DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "bankaccounts DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "users DROP `usertype`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "attachment DROP `usertype`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects_escrow DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "projects DROP `type`", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalfilters_maxrowsdisplaysubscribers', 'Maximum number of rows to display for subscriber result listings in AdminCP? [SQL-specific]', '10', 'globalfilterresults', 'int', '', '', '', 7)", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_user CHANGE `paymethod` `paymethod` ENUM( 'account', 'bank', 'visa', 'amex', 'mc', 'disc', 'paypal', 'check' ) NOT NULL DEFAULT 'account'", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_roles ADD `roletype` ENUM('service','product') NOT NULL DEFAULT 'service' AFTER `custom`", 0, null, __FILE__, __LINE__);
$ilance->db->query("ALTER TABLE " . DB_PREFIX . "subscription_roles ADD `roleusertype` ENUM('servicebuyer','serviceprovider','productbuyer','merchantprovider') NOT NULL DEFAULT 'servicebuyer' AFTER `roletype`", 0, null, __FILE__, __LINE__);
$ilance->db->query("UPDATE " . DB_PREFIX . "stars SET `pointsfrom` = '0' WHERE `starid` = '1' LIMIT 1", 0, null, __FILE__, __LINE__);
echo '<br /><br /><strong>Complete!</strong> Please follow the next steps:<br /><br />
1. 2 New fields added to v3_subscription_roles: `roletype` and `roleusertype`.
Please visit: AdminCP > Settings > Subscriptions > (Roles Tab) and for EVERY role you have installed, please use the EDIT link (pencil icon) to edit EVERY role so you can define the new settings Role Type Key and Role Type User Key.
These settings are self-explanitory.. for service buyers choose role type of service and role user type to servicebuyer and so on..<br />
2. After the above, click AdminCP > Setting > Pay Modules > Processing<br />
3. Set pulldown credit card gateway to disabled<br />
4. Submit form at bottom of page<br />
5. Return this this page and you should see new payment gateways in the pulldown including PSIGate and eWAY<br />
6. Select your gateway (or select none) and Submit form at bottom of page one last time<br />
7. Using FTP client, edit the ./admincp/livesync/ls_state.php file and change "current_version" to 3.0.8';
echo "<br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br />\n";
?>