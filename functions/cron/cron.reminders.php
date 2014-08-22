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

($apihook = $ilance->api('cron_reminders_start')) ? eval($apihook) : false;

// #### subscription invoice reminders #################################
$cronlog .= $ilance->subscription->send_user_subscription_frequency_reminders();
        
// #### other unpaid invoice reminders #################################
$cronlog .= $ilance->accounting_reminders->send_unpaid_invoice_frequency_reminders();

// #### scheduled subscription invoice cancellations ###########################
$cronlog .= $ilance->subscription->cancel_scheduled_subscription_invoices();

// #### expire verified profile verification credentials #######################
$cronlog .= $ilance->portfolio->expire_verified_profile_credentials();

// #### expire featured portfolio items ########################################
$cronlog .= $ilance->portfolio->expire_featured_portfolios();

($apihook = $ilance->api('cron_reminders_end')) ? eval($apihook) : false;

$ilance->timer->stop();
log_cron_action('{_the_reminder_tasks_were_successfully_executed} ' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>