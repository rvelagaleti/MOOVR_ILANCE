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

function import_cities_kenya()
{
    global $ilance, $dbengine, $dbtype;
    // ** All cities within the state of Kenya **************************************************************
    print_progress_begin('<b>Importing 69 Kenya cities</b>, please wait.', '.', 'progressspancitieskenya');
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Kiambu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Kirinyaga', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Muranga', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Nyandarua', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Nyeri', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Thika', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Central', 'Maragua', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Kilifi', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Kwale', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Lamu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Malindi', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Mombasa', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Taita-Taveta', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Coast', 'Tana River', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Embu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Isiolo', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Kitui', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Makueni', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Machakos', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Marsabit', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Mbeere', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Meru Central', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Meru North', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Meru South', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Moyale', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Mwingi', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Eastern', 'Tharaka', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nairobi Area', 'Nairobi', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'North Eastern', 'Garissa', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'North Eastern', 'Mandera', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'North Eastern', 'Wajir', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'North Eastern', 'Ijara', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Gucha', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Homa Bay', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Kisii Central', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Kisumu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Kuria', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Migori', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Nyamira', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Rachuonyo', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Siaya', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Suba', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Bondo', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Nyanza', 'Nyando', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Baringo', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Bomet', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Buret', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Keiyo', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Kajiado', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Kericho', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Koibatek', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Laikipia', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Marakwet', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Nakuru', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Nandi', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Narok', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Samburu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Trans Mara', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Trans-Nzoia', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Turkana', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'Uasin Gishu', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Rift Valley', 'West Pokot', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Bungoma', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Busia', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Butere/Mumias', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Mount Elgon', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Kakamega', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Lugari', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Teso', '1')", 0, null, __FILE__, __LINE__);
$ilance->db->query("INSERT INTO `" . DB_PREFIX . "locations_cities` (`id`, `locationid`, `state`, `city`, `visible`) VALUES (NULL, '386', 'Western', 'Vihiga', '1')", 0, null, __FILE__, __LINE__);
    print_progress_end();
}

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>