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

echo '<h1>Upgrade 3.0.6 to 3.0.7</h1><p>Updating database...</p>';
// update original templates (this requires admin's to revert these specific templates manually
$ilance->db->query("UPDATE " . DB_PREFIX . "templates SET original = '5px 5px 0px 5px' WHERE name = 'template_pagepadding'", 0, null, __FILE__, __LINE__);
$ilance->db->query("UPDATE " . DB_PREFIX . "templates SET original = '8px 5px 5px 5px' WHERE name = 'template_bodymargin'", 0, null, __FILE__, __LINE__);
$ilance->db->query("UPDATE " . DB_PREFIX . "templates SET original = '0px 5px 0px 5px' WHERE name = 'template_bodypadding'", 0, null, __FILE__, __LINE__);

// add pulldown support to project auction questions
$ilance->db->query("ALTER TABLE `" . DB_PREFIX . "project_questions` CHANGE `inputtype` `inputtype` ENUM( 'yesno', 'int', 'textarea', 'text', 'multiplechoice', 'pulldown' ) NOT NULL DEFAULT 'text'", 0, null, __FILE__, __LINE__);

// allow admin to enable/disable portfolio system
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "configuration` VALUES (NULL, 0, 'portfoliodisplay_enabled', 'Enable the portfolio system?', '1', 'portfoliodisplay', 'yesno', '', '', '', 1)", 0, null, __FILE__, __LINE__);
$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET sort = '6' WHERE name = 'portfoliodisplay_thumbsperpage'", 0, null, __FILE__, __LINE__);

// cookie name fix
$ilance->db->query("INSERT INTO " . DB_PREFIX . "configuration VALUES (NULL, 0, 'globalsecurity_cookiename', 'Enter the marketplace cookie name identifier', 'ilancedata', 'globalsecuritymime', 'text', '', '', '', 2)", 0, null, __FILE__, __LINE__);

// handle search profile upgrade
$ilance->db->query("CREATE TABLE `" . DB_PREFIX . "profile_categories` (`user_id` INT(10) NOT NULL default '0', `cid` INT(10) NOT NULL default '0')", 0, null, __FILE__, __LINE__);

// create new verification config group
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "configuration_groups` VALUES (NULL, 'verificationsystem', 'verificationsystem', 'Verification System Settings', '')", 0, null, __FILE__, __LINE__);

// create new verification settings
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "configuration` VALUES (NULL, 0, 'verificationlength', 'Verified profile answers duration length [enter value in days] the verified icon will be displayed', '365', 'verificationsystem', 'int', '', '', '', 1)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "configuration` VALUES (NULL, 0, 'verificationupdateafter', 'Can members update their verified profile answers after successful verification payment?', '0', 'verificationsystem', 'yesno', '', '', '', 2)", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "configuration` VALUES (NULL, 0, 'verificationmoderation', 'Enable profile verification moderation? [admin verify manually via verification manager]?', '1', 'verificationsystem', 'yesno', '', '', '', 3)", 0, null, __FILE__, __LINE__);

// update portfolio display types
$ilance->db->query("UPDATE " . DB_PREFIX . "configuration SET value = '.gif, .jpg, .png, .jpeg' WHERE name = 'portfoliodisplay_imagetypes'", 0, null, __FILE__, __LINE__);

// select old profile category data with members that have already added themselves to searchable categories
$sql = $ilance->db->query("SELECT user_id, profilecats FROM " . DB_PREFIX . "preferences
WHERE profilecats != ''", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sql) > 0)
{
    while ($profiles = $ilance->db->fetch_array($sql))
    {
        $cats = trim($profiles['profilecats']);
        $uid  = $profiles['user_id'];
        
        // explode comma separated categories
        $split = explode(',', $cats);
        if (sizeof($split) > 0)
        {
            for ($i=0; $i < sizeof($split); $i++)
            {
                $ilance->db->query("INSERT INTO " . DB_PREFIX . "profile_categories
                (user_id, cid)
                VALUES (
                '".intval($uid)."',
                '".intval($split[$i])."')", 0, null, __FILE__, __LINE__);
            }
        }
    }
}
$ilance->db->query("ALTER TABLE `" . DB_PREFIX . "preferences` CHANGE `profilecats` `displayprofile` INT( 1 ) NOT NULL DEFAULT '1'", 0, null, __FILE__, __LINE__);
$ilance->db->query("UPDATE `" . DB_PREFIX . "preferences` SET `displayprofile` = '1'", 0, null, __FILE__, __LINE__);

// update custom user bits for the subscription roles
// [fbscore] [fbpercent] [rating] [stars] [store] [verified] [subscription]
$sqlroles = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "subscription_roles", 0, null, __FILE__, __LINE__);
if ($ilance->db->num_rows($sqlroles) > 0)
{
        $ilance->db->query("UPDATE " . DB_PREFIX . "subscription_roles
        SET custom = '[fbscore] [fbpercent] [rating] [stars] [store] [verified] [subscription]'", 0, null, __FILE__, __LINE__);
}

// italy states fix
$ilance->db->query("DELETE FROM `" . DB_PREFIX . "locations_states` WHERE locationid = '381'", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Asti')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Avellino')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Bari')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Barletta-Andria-Trani')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Belluno')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Benevento')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Bergamo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Biella')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Bologna')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Bolzano')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Brescia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Brindisi')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Cagliari')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Caltanissetta')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Campobasso')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Caserta')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Catania')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Catanzaro')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Chieti')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Como')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Cosenza')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Cremona')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Crotone')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Cuneo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Enna')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Fermo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Ferrara')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Firenze')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Foggia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Forli-Cesena')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Frosinone')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Genova')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Gorizia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Grosseto')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Imperia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Isernia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'La-Spezia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Latina')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Lecce')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Lecco')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Livorno')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Lodi')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Lucca')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Macerata')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Mantova')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Massa-Carrara')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Matera')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Messina')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Milano')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Modena')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Monza-Brianza')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Napoli')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Novara')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Nuoro')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Oristano')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Padova')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Palermo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Parma')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pavia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Perugia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pesaro-Urbino')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pescara')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Piacenza')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pisa')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pistoia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Pordenone')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Potenza')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Prato')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Ragusa')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Ravenna')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Reggio-Calabria')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Reggio-Emilia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Rieti')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Rimini')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Roma')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Rovigo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Salerno')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Sassari')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Savona')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Siena')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Siracusa')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Sondrio')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Taranto')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Teramo')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Terni')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Torino')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Trapani')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Trento')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Treviso')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Trieste')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Udine')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Varese')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Venezia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Verbania')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Vercelli')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Verona')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Vibo-Valentia')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Vicenza')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_states` VALUES (381, 'Viterbo')", 0, null, __FILE__, __LINE__);
echo "<br /><br /><strong>Complete!</strong> (<em>Remember to upload new .php scripts, re-import the new language xml file included, upload all templates overwriting existing ones and finally revert template variables to original using the button in the admin cp to the following variables: template_pagepadding, template_bodymargin and template_bodypadding.</em>)";
echo "<br /><br /><a href=\"installer.php\"><strong>Return to installer main menu</strong></a><br /><br />\n";
?>