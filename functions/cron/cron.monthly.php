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
        die('<strong>Warning:</strong> This script cannot be loaded indirectly.  Operation aborted.');
}
$ilance->timer->start();
$cronlog = '';
$ilance->db->query("
        UPDATE " . DB_PREFIX . "users
        SET bidsthismonth = '0',
        auctiondelists = '0',
        bidretracts = '0'
", 0, null, __FILE__, __LINE__);
if ($ilconfig['resetpopulartags'])
{
        $ilance->db->query("
                UPDATE " . DB_PREFIX . "search
                SET count = '0'
                WHERE count > " . intval($ilconfig['populartagcount']) . "
        ", 0, null, __FILE__, __LINE__);
}
$ilance->db->query("
        TRUNCATE TABLE " . DB_PREFIX . "shipping_rates_cache
", 0, null, __FILE__, __LINE__);
$cronlog .= $ilance->auction->remove_shipping_regions_from_deleted_listings();
$cronlog .= $ilance->auction->remove_photos_from_deleted_listings();
$ilance->db->query("
        TRUNCATE TABLE " . DB_PREFIX . "cronlog
", 0, null, __FILE__, __LINE__);
$cronlog = substr($cronlog, 0, -2);
$ilance->callhome();

($apihook = $ilance->api('cron_monthly')) ? eval($apihook) : false;

$ilance->timer->stop();
log_cron_action('{_the_monthly_tasks_were_executed_reset_keyword_searches_count_this_month_to_where_keyword_count} ' . intval($ilconfig['populartagcount']) . ' {_to_prevent_keyword_spamming_abuse_reset_bids_this_month_for_all_users_reset_auction_delists_this_month_for_all_users}' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>