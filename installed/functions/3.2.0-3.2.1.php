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

			$queries['15'] = "ALTER TABLE " . DB_PREFIX . "categories ADD hidebuynow INT(1) default 0 AFTER bidgroupdisplay";
			$queries['16'] = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "error_log(
				`log_id` INT(5) NOT NULL AUTO_INCREMENT,
				`error_id` INT(5),
				`name` MEDIUMTEXT,
				`info` MEDIUMTEXT,
				`value` INT(1) NOT NULL default '1',
				PRIMARY KEY  (`log_id`)
				) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE . "";
			$queries['17'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_contactform_listing', 'Show contact form on the product page', '1', 'globalfiltersrfp', 'yesno', '', '', 'When you enable this option your users will be able to send requests regarding specific product (by email)', 8, 1)";
			$queries['18'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_contactform_member', 'Show contact form on the profile page ', '1', 'globalfiltersrfp', 'yesno', '', '', 'When you enable this option your users will be able to send questions (by email) between each other from the profile page', 9, 1)";
			
			$queries['19'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES ('', 'paypal_currency', 'Enter available currency in PayPal transactions', 'CAD|EUR|GBP|USD|JPY|AUD|NZD|CHF|HKD|SGD|SEK|DKK|PLN|NOK|HUF|CZK|ILS|MXN|BRL|MYR|PHP|TWD|THB', 'paypal', 'textarea', '', '', '', 140, 1)";
			$queries['20'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES ('', 'moneybookers_currency', 'Enter available currency in MoneyBookers transactions', 'EUR|GBP|BGN|USD|AUD|CAD|CZK|DKK|EEK|HKD|HUF|ILS|JPY|LTL|LVL|MYR|TWD|TRY|NZD|NOK|PLN|SGD|SKK|ZAR|KRW|SEK|CHF', 'moneybookers', 'textarea', '', '', '', 110, 1)";
			$queries['21'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES ('', 'cashu_currency', 'Enter available currency in CashU transactions', 'EUR|GBP|BGN|USD|AUD|CAD|CZK|DKK|EEK|HKD|HUF|ILS|JPY|LTL|LVL|MYR|TWD|TRY|NZD|NOK|PLN|SGD|SKK|ZAR|KRW|SEK|CHF', 'cashu', 'textarea', '', '', '', 100, 1)";
			
			// **** added by Peter on July 26, 2010 based on bug: http://www.ilance.com/forum/project.php?issueid=971 
			$queries['22'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_countdowndelayms', 'How many milliseconds should an auction countdown timer be delayed?', '1000', 'globalfilterresults', 'int', '', '', 'This setting will allow you to increase or decrease the milliseconds taken to refresh the live details on an auction page.  The higher the number the slower the countdown (for older, slower servers).  Default rate is 1000 (every second).', 404, 1)";
			$queries['23'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_categorydelayms', 'How many milliseconds should the master category selector timer be delayed?', '1200', 'globalfilterresults', 'int', '', '', 'This setting will allow you to increase or decrease the milliseconds taken to refresh the live category widget boxes in the marketplace.  The higher the number the slower the category widgets will take to display (for older, slower servers).  Default rate is 1200 (every second).', 405, 1)";
			$queries['24'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_categorynextdelayms', 'How many milliseconds should the additional category selector timers be delayed?', '800', 'globalfilterresults', 'int', '', '', 'This setting will allow you to increase or decrease the milliseconds taken to refresh additional category widget boxes in the marketplace when previous category selections have already been made.  The higher the number the slower the additional category widgets will take to display (for older, slower servers).  Default rate is 800 (< 1 second).', 406, 1)";
			$queries['25'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalauctionsettings_maincatcutoff', 'How many categories to display on the main page until a more option link becomes visble ', '50', 'globalcategorysettings', 'int', '', '', 'This feature can really help the look and feel of your category on the main page. If you have many categories, you can define how many of them will show before the rest become hidden and a More link becomes visible allowing the user to see the rest.', 175, 1)";
			
			// ***** platnosci
			$queries['26'] = "INSERT INTO " . DB_PREFIX . "payment_groups VALUES ('platnosci', 'platnosci', 'Platnosci.pl', '', 'ipn')";
			$queries['27'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paymodulename', 'Enter the name of this payment module', 'Platnosci.pl', 'platnosci', 'text', '', '', '', 10, 1)";
			$queries['28'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_id', 'Enter your POS id', '', 'platnosci', 'text', '', '', '', 20, 1)";
			$queries['29'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_auth_key', 'Enter your POS Auth Key', '', 'platnosci', 'text', '', '', '', 30, 1)";
			$queries['30'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_key1', 'Enter your Key1', '', 'platnosci', 'text', '', '', '', 40, 1)";
			$queries['31'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_pos_key2', 'Enter your Key2', '', 'platnosci', 'text', '', '', '', 50, 1)";
			$queries['32'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_transaction_fee', 'Enter deposit transaction fee 1 [value in percentage; i.e: 0.029]', '0.029', 'platnosci', 'int', '', '', '', 60, 1)";
			$queries['33'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_transaction_fee2', 'Enter deposit transaction fee 2 [value in fixed format; i.e: 0.30]', '0.30', 'platnosci', 'int', '', '', '', 70, 1)";
			$queries['34'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_master_currency', 'Enter the currency used in Platnosci transactions', 'PLN', 'platnosci', 'int', '', '', '', 80, 1)";
			$queries['35'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_withdraw_active', 'Allow members to request withdrawals using this gateway?', '0', 'platnosci', 'yesno', '', '', '', 100, 1)";
			$queries['36'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_active', 'Allow members to deposit funds using this gateway?', '0', 'platnosci', 'yesno', '', '', '', 110, 1)";
			$queries['37'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_subscriptions', 'Enable Platnosci Recurring Subscriptions? (used in subscription menu)', '0', 'platnosci', 'yesno', '', '', '', 120, 1)";
			$queries['38'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'platnosci_directpayment', 'Allow members to directly pay other members through this gateway?', '0', 'platnosci', 'yesno', '', '', 'For example, if a buyer purchases an item from a seller and the seller chooses Platnosci as their gateway, the marketplace will directly send the buyer to the sellers gateway for direct payment.  After payment, the buyer is redirected back to the Marketplace.', 130, 1)";
			
			// ***** description lenght error
			$queries['39'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_maxcharactersdescription', 'Maximum number of characters of description in auctions listing', '40', 'globalfilterresults', 'int', '', '', 'Enter 0 for unlimited characters or any other number to cut off the description', 407, 1)";
			$queries['40'] = "UPDATE " . DB_PREFIX . "configuration SET value='30' WHERE name = 'globalfilters_maxcharacterstitle'";
			$queries['41'] = "UPDATE " . DB_PREFIX . "configuration SET description ='Maximum number of characters of title in auctions listing' WHERE name = 'globalfilters_maxcharacterstitle'";
			
			// ***** bluepay recurring subscriptions support (live cc gateway)
			$queries['42'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_subscriptions', 'Enable BluePay Recurring Subscriptions? (used in subscription menu)', '0', 'bluepay', 'yesno', '', '', '', 100, 1)";
			
			// ***** ability to enable or disable the "specials tab" on the home page
			$queries['43'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_specialshomepage', 'Would you like to show the specials tab on the home page?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting would allow you to show or hide the specials tab and image on the home page.', 408, 1)";
			
			// ***** platnosci 
			$queries['44'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name='platnosci_withdraw_active'";
			$queries['45'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE NAME='platnosci_subscriptions'";
			
			// ***** enhancement pay as you go
			$queries['46'] = "ALTER TABLE " . DB_PREFIX . "projects ADD enhancementfee float(10,2) NOT NULL default '0.00' AFTER insertionfee";
			$queries['47'] = "ALTER TABLE " . DB_PREFIX . "projects ADD isenhancementfeepaid INT(1) NOT NULL default '0' AFTER isifpaid";
			$queries['48'] = "ALTER TABLE " . DB_PREFIX . "projects ADD enhancementfeeinvoiceid INT(5) NOT NULL default '0' AFTER ifinvoiceid";
			
			// ***** bluepay
			$queries['49'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_accountid', 'Enter your BluePay Account ID', 'demo', 'bluepay', 'text', '', '', '', 15, 1)";
			$queries['50'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_userid', description='Enter your BluePay userID' WHERE name='cc_login' AND configgroup='bluepay'";
			$queries['51'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_masterid', description='Enter your BluePay masterID' WHERE name='cc_password' AND configgroup='bluepay'";
			$queries['52'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_key', description='Enter your BluePay Secret Key' WHERE name='cc_key' AND configgroup='bluepay'";
			$queries['53'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_transaction_fee' WHERE name='cc_transaction_fee' AND configgroup='bluepay'";
			$queries['54'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_transaction_fee2' WHERE name='cc_transaction_fee2' AND configgroup='bluepay'";
			$queries['55'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_authentication_capture' WHERE name='authentication_capture' AND configgroup='bluepay'";
			$queries['56'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_authentication_refund' WHERE name='authentication_refund' AND configgroup='bluepay'";
			$queries['57'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_candeposit' WHERE name='cc_candeposit' AND configgroup='bluepay'";
			
			// ***** configurable number of slideshow attachments: http://www.ilance.com/forum/project.php?issueid=1223
			$queries['58'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('attachmentlimit_slideshowmaxfiles', 'Maximum number of slideshow attachments', '5', 'attachmentlimit_productslideshowsettings', 'int', '', '', '', 10, 1)";
			
			// ***** turn AJAX on or off from listing page
			$queries['59'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_ajaxrefresh', 'Would you like to enable AJAX on the auction listing page?', '0', 'globalfilterresults', 'yesno', '', '', 'This setting would allow you to turn AJAX live page updates on or off.  By enabling this option may increase the server load which can still be adjusted using the How many milliseconds should an auction countdown timer be delayed option.', 409, 1)";
			
			// ***** turn popup modals for location on or off
			$queries['60'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_regionmodal', 'Would you like to enable the region selector popup modals?', '1', 'globalfilterresults', 'yesno', '', '', 'This setting would allow you to turn the region/location popup modal window on or off on popular sections of the marketplace such as search results and the detailed auction listing pages.  By enabling this option lets the application learn more about a users location such as Country and/or Zip code which helps to calculate the best price on shipping to the closest experts and jobs by proximity and location.', 410, 1)";
			
			// ***** paypal sandbox
			$queries['61'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'paypal_sandbox', 'Enable Paypal Sandbox testing environment?', '0', 'paypal', 'yesno', '', '', '', 150, 1)";
			
			// ***** bluepay back to orginal configuration and some changes
			$queries['62'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'bluepay_userid' LIMIT 1";
			$queries['63'] = "UPDATE " . DB_PREFIX . "payment_configuration SET description='BluePay Account ID' WHERE name='bluepay_accountid' AND configgroup='bluepay'";
			$queries['64'] = "UPDATE " . DB_PREFIX . "payment_configuration SET name='bluepay_secretkey', description='BluePay Secret Key', inputtype='text' WHERE name='bluepay_masterid' AND configgroup='bluepay'";
			$queries['65'] = "DELETE FROM " . DB_PREFIX . "payment_configuration WHERE name = 'bluepay_key' LIMIT 1";
			$queries['66'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'bluepay_test', 'Enable Test Mode', '0', 'bluepay', 'yesno', '', '', '', 120, 1)";
			
			// ***** Authnet test mode
			$queries['67'] = "INSERT INTO " . DB_PREFIX . "payment_configuration VALUES (NULL, 'authnet_test', 'Enable Test Mode', '0', 'authnet', 'yesno', '', '', '', 120, 1)";
			
			// ***** BlukUploadPreview
			$queries['68'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_bulkuploadpreviewlimit', 'Maximum number of listings in Bulk Upload Preview?', '50', 'globalfiltersrfp', 'int', '', '', 'This setting works only when Bulk Uploading is enabled.  This setting will define how many listings can be displayed on Bulk Upload Preview.', 8, 1)";
			
			// ***** Kenya Distance table:
			$queries['69'] = "CREATE TABLE IF NOT EXISTS " . DB_PREFIX . "distance_ke (
`ZIPCode` CHAR(30) NOT NULL default '',
`Latitude` DOUBLE NOT NULL default '0',
`Longitude` DOUBLE NOT NULL default '0',
KEY `ZIPCode` (`ZIPCode`),
KEY `Latitude` (`Latitude`),
KEY `Longitude` (`Longitude`)
) " . MYSQL_ENGINE . "=" . MYSQL_TYPE . " DEFAULT CHARACTER SET " . DB_CHARSET . " COLLATE " . DB_COLLATE;
			
			// ***** Kenya Cities
			$queries['70'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Kiambu')";
			$queries['71'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Kirinyaga')";
			$queries['72'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Muranga')";
			$queries['73'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Nyandarua')";
			$queries['74'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Nyeri')";
			$queries['75'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Thika')";
			$queries['76'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Central', 'Maragua')";
			$queries['77'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Kilifi')";
			$queries['78'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Kwale')";
			$queries['79'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Lamu')";
			$queries['80'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Malindi')";
			$queries['81'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Mombasa')";
			$queries['82'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Taita-Taveta')";
			$queries['83'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Coast', 'Tana River')";
			$queries['84'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Embu')";
			$queries['85'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Isiolo')";
			$queries['86'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Kitui')";
			$queries['87'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Makueni')";
			$queries['88'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Machakos')";
			$queries['89'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Marsabit')";
			$queries['90'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Mbeere')";
			$queries['91'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Meru Central')";
			$queries['92'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Meru North')";
			$queries['93'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Meru South')";
			$queries['94'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Moyale')";
			$queries['95'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Mwingi')";
			$queries['96'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Eastern', 'Tharaka')";
			$queries['97'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nairobi Area', 'Nairobi')";
			$queries['98'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('North Eastern', 'Garissa')";
			$queries['99'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('North Eastern', 'Mandera')";
			$queries['100'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('North Eastern', 'Wajir')";
			$queries['101'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('North Eastern', 'Ijara')";
			$queries['102'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Gucha')";
			$queries['103'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Homa Bay')";
			$queries['104'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Kisii Central')";
			$queries['105'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Kisumu')";
			$queries['106'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Kuria')";
			$queries['107'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Migori')";
			$queries['108'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Nyamira')";
			$queries['109'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Rachuonyo')";
			$queries['110'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Siaya')";
			$queries['111'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Suba')";
			$queries['112'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Bondo')";
			$queries['113'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Nyanza', 'Nyando')";
			$queries['114'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Baringo')";
			$queries['115'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Bomet')";
			$queries['116'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Buret')";
			$queries['117'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Keiyo')";
			$queries['118'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Kajiado')";
			$queries['119'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Kericho')";
			$queries['120'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Koibatek')";
			$queries['121'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Laikipia')";
			$queries['122'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Marakwet')";
			$queries['123'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Nakuru')";
			$queries['124'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Nandi')";
			$queries['125'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Narok')";
			$queries['126'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Samburu')";
			$queries['127'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Trans Mara')";
			$queries['128'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Trans-Nzoia')";
			$queries['129'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Turkana')";
			$queries['130'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'Uasin Gishu')";
			$queries['131'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Rift Valley', 'West Pokot')";
			$queries['132'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Bungoma')";
			$queries['133'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Busia')";
			$queries['134'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Butere/Mumias')";
			$queries['135'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Mount Elgon')";
			$queries['136'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Kakamega')";
			$queries['137'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Lugari')";
			$queries['138'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Teso')";
			$queries['139'] = "INSERT INTO " . DB_PREFIX . "locations_cities VALUES ('Western', 'Vihiga')";
			
			$queries['140'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name='externaljsphrases' LIMIT 1";
			
			$queries['141'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype='text' WHERE name='paypal_master_currency'";
			$queries['142'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype='text' WHERE name='stormpay_master_currency'";
			$queries['143'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype='text' WHERE name='cashu_master_currency'";
			$queries['144'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype='text' WHERE name='moneybookers_master_currency'";
			$queries['145'] = "UPDATE " . DB_PREFIX . "payment_configuration SET inputtype='text' WHERE name='platnosci_master_currency'";
			$queries['146'] = "ALTER TABLE " . DB_PREFIX . "sessions CHANGE url url TEXT NOT NULL";
			
			$queries['147'] = "UPDATE " . DB_PREFIX . "configuration SET value='25' WHERE name = 'globalfilters_maxcharacterstitle'";
			$queries['148'] = "ALTER TABLE " . DB_PREFIX . "projects_shipping CHANGE ship_method ship_method enum('flatrate','calculated','localpickup', 'digital')";
			
			// ***** currency more less cutoff on search page
			$queries['149'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalserverlocale_currencycatcutoff', 'How many currencies to display until More link becomes activated?', '5', 'globalserverlocalecurrency', 'int', '', 'currencyrates', 'This setting is useful when we have lot of auctions in different currency.', 3, 1)";
			
			$queries['150'] = "UPDATE " . DB_PREFIX . "configuration SET description = 'Maximum number of characters of title in auction' WHERE name='globalfilters_maxcharacterstitle'";
			$queries['151'] = "DELETE FROM " . DB_PREFIX . "configuration WHERE name='globalfilters_maxcharactersdescription' LIMIT 1";
			$queries['152'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_auctiondescriptioncutoff', 'How many characters to cut off from auction description?', '40', 'globalfilterresults', 'int', '', '', 'Maximum number of characters of auction description to display in tabs like: Featured, New, Viewed or Ending Soon where is not enough space to print whole auction description. Enter 0 for unlimited characters or any other number to cut off the description', 411, 1)";
			$queries['153'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_auctiontitlecutoff', 'How many characters to cut off from auction title?', '25', 'globalfilterresults', 'int', '', '', 'Maximum number of characters of auction title to display in tabs like: Featured, New, Viewed or Ending Soon where is not enough space to print whole auction title. Enter 0 for unlimited characters or any other number to cut off the title.', 412, 1)";
			
			// **** introducing weekly cron script
			$queries['154'] = "INSERT INTO " . DB_PREFIX . "cron VALUES (NULL, 1053532560, 1, -1, -1, 'a:1:{i:0;i:0;}', 'cron.weekly.php', 1, 1, 'weekly', 'ilance')";
			
			// **** Location Format
			$queries['155'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_locationformat', 'Location display format:', '[city], [zip], [state], [country]', 'globalfilterresults', 'text', '', '', 'You can localize your marketplace using this option. Instead of using default country, state you can define your own format. You can use this variables [city] [zip] [state] [country]', 413, 1)";
			
			$queries['156'] = "UPDATE " . DB_PREFIX . "configuration SET value='100' WHERE name='globalfilters_maxcharacterstitle'";
			
			$queries['157'] = "INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_permissions', 'Admincp Permissions Phrases', 'ilance')";
			$queries['158'] = "INSERT INTO " . DB_PREFIX . "language_phrasegroups VALUES ('admincp_configuration', 'Admincp Configuration Phrases', 'ilance')";
			
			$queries['159'] = ($ilance->db->field_exists('description', DB_PREFIX . 'configuration') AND $ilance->db->field_exists('help', DB_PREFIX . 'configuration')) ? "ALTER TABLE " . DB_PREFIX . "configuration DROP description, DROP help" : '';
			$queries['160'] = ($ilance->db->field_exists('accesstext_eng', DB_PREFIX . 'subscription_permissions') AND $ilance->db->field_exists('accessdescription_eng', DB_PREFIX . 'subscription_permissions')) ? "ALTER TABLE " . DB_PREFIX . "subscription_permissions DROP accesstext_eng, DROP accessdescription_eng" : '';
			
			$queries['161'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('globalfilters_gzhandler', '0', 'globalfilterresults', 'yesno', '' , '', '414', '1')";
			
			$queries['162'] = "INSERT INTO " . DB_PREFIX . "configuration VALUES ('subscriptions_emailexpiryreminder', '1', 'subscriptions_settings', 'yesno', '', '', '1', '1')";
			$queries['163'] = "INSERT INTO " . DB_PREFIX . "configuration_groups VALUES ('subscriptions_settings', 'subscriptions_settings', 'Subscriptions Settings', 'Subscriptions Settings', '510')";
		
			
				
if (isset($_REQUEST['execute']) AND $_REQUEST['execute'] == 1)
{
        echo '<h1>Upgrade 3.2.0 to 3.2.1</h1><p>Updating database...</p>';
	
        if ($current_version == '3.2.0')
        {
	        	if (isset($queries) AND !empty($queries) AND is_array($queries) AND $sql_version < 163)
				{
						for($i = $sql_version ; $i <= 163 ; ++$i)
						{
								if (isset($queries[$i]))
								{
									if(!empty($queries[$i]))
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
				
                // import (or detect upgrade) of new phrases for 3.2.1
                echo import_language_phrases(10000, 0);
                
                // import (or detect upgrade) of new css templates for 3.2.1
                echo import_templates();
                
                // import (or detect upgrade) of new email templates for 3.2.1
                echo import_email_templates();
		
				// rebuild the recursive category logic for 3.2.1
				print_progress_begin('<b>Rebuilding hierarchical logic within the category table</b>, please wait.', '.', 'progressspan99');
				rebuild_category_tree(0, 1);
				print_progress_end();
				
				// optimize new category table to support spatial indexing for 3.2.1
				//echo rebuild_spatial_category_indexes();
                
				$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '3.2.1' WHERE name = 'current_version'"); 
				
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
        echo '<h1>Upgrade from 3.2.0 to 3.2.1</h1><p>The following SQL queries will be executed:</p>';    
        echo '<hr size="1" width="100%" style="margin:0px; padding:0px" />';

        if (isset($queries) AND !empty($queries) AND is_array($queries))
		{
				if($sql_version == 163)
				{
					echo '<div>
					<span>You have the latest SQL version.</span>
		            </div>';
				}
				else 
				{
					for($i = $sql_version ; $i <= 163 ; ++$i)
					{
						if (isset($queries[$i]) AND !empty($queries[$i]))
						{
							echo '<div><textarea style="font-family: verdana" cols="80" rows="5">' . $queries[$i] . '</textarea></div>';
							echo '<hr size="1" width="100%" />';
						}
					}
				}
				
				echo '<div class="redhlite">
					<strong>
					Notice: </strong><span>Before upgrade make sure you have backup of your database and files. <br/>You should also export your language and template into XML file.
		            During upgrade process your default template will be overwritten with the new template. If you use more than one template then you will need to open /install/xml/master-style.xml and edit line
		            <br/>
					'.htmlspecialchars('<style name="Default Style" ilversion="3.2.1">').'
		            <br/>
					Instead of "Default Style" enter name of your style here. Now you should goto AdminCP->Styles / CSS->Import and import this edited style. This will overwrite your style. 
		            </span>
		            </div>';
		
				if (function_exists("version_compare") AND version_compare(MYSQL_VERSION, "5.1", ">="))
				{
					echo '<div class="bluehlite"><span class="smaller"><b><font color="#000000">MySQL version</font><br />
					</b></span>MySQL version >= 5.1.x</div>';
					
					echo '<div><strong><a href="installer.php?do=install&step=31&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
				}
				else
				{
					echo '<div class="redhlite"><span class="smaller"><b><font color="red">MySQL version</font><br />
					</b></span>MySQL version should be greater or equal than 5.1.x however ILance still supports backward compatibility for MySQL < 5.1.x. If you use large number of categories then you will need MySQL 5.1.x</div>';
					
					echo '<div><strong><a href="installer.php?do=install&step=31&execute=1">Execute</a></strong> these SQL query updates (will also upgrade email, css and phrases for you)</div>';
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