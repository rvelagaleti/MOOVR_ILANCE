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

($apihook = $ilance->api('cron_dailyrfp_start')) ? eval($apihook) : false;

// #### reset daily bid counters for all members ###############################
$ilance->db->query("
        UPDATE " . DB_PREFIX . "users
        SET bidstoday = '0'
", 0, null, __FILE__, __LINE__);
$cronlog .= '{_reset_the_bids_today_field_for_all_users_back_to_zero}, ';
$cronlog .= $ilance->escrow->cancel_unlinked_escrow_invoices();
$cronlog .= $ilance->workspace->remove_mediashare_content_daily(7);
$cronlog .= $ilance->subscription->send_category_notification_subscriptions();
$cronlog .= $ilance->subscription->send_saved_search_subscriptions(50);
$cronlog .= $ilance->subscription->expire_saved_search_subscriptions(30);
$cronlog .= $ilance->subscription->remove_unlinked_memberships();
$cronlog .= ($ilconfig['subscriptions_emailexpiryreminder'] == '1') ? $ilance->subscription->send_subscription_expiry_reminders(7) : '';
$cronlog .= $ilance->bid->wait_approval_unaward_cron();
//$cronlog .= $ilance->admincp->fetch_latest_news(); // not ready yet -Peter
$cronlog .= $ilance->auction->category_listing_count_fixer();//very time consuming
$cronlog .= $ilance->categories_skills->cron_reset_skill_parentid_duplicates();
$cronlog .= $ilance->auction->remove_answers_from_deleted_listings();
$cronlog .= $ilance->auction->archive_expired_auctions();
$cronlog .= $ilance->colors->remove_colors_from_deleted_listings();
$cronlog .= $ilance->colors->remove_colors_from_deleted_attachments();

// #### clean outdated log entries after n days as defined in admin cp #########
if ($ilconfig['clean_old_log_entries'] > 0)
{
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "emaillog
                WHERE date < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
                        AND logtype != 'alert'
        ", 0, null, __FILE__, __LINE__);
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "cronlog
                WHERE FROM_UNIXTIME(dateline) < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ", 0, null, __FILE__, __LINE__);
        $ilance->db->query("
                DELETE FROM " . DB_PREFIX . "failed_logins
                WHERE datetime_failed < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ", 0, null, __FILE__, __LINE__);
        /*
        $ilance->db->query("
              DELETE FROM " . DB_PREFIX . "invoicelog
              WHERE date_sent < DATE_SUB(CURDATE(), INTERVAL $ilconfig[clean_old_log_entries] DAY)
        ", 0, null, __FILE__, __LINE__);*/
        $cronlog .= '{_log_entries_older_than} ' . $ilconfig['clean_old_log_entries'] . ' {_days_deleted_for_email_cron_and_failed_logins}, ';
}

($apihook = $ilance->api('cron_dailyrfp_end')) ? eval($apihook) : false;

if (!empty($cronlog))
{
        $cronlog = mb_substr($cronlog, 0, -2);
}
$ilance->timer->stop();
log_cron_action('{_the_daily_tasks_executed_the_following_events} ' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>