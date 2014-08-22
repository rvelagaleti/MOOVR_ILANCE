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

($apihook = $ilance->api('cron_subscriptions_start')) ? eval($apihook) : false;

// expire subscription exemptions
$notice = '';
$notice .= $ilance->subscription_expiry->user_subscription_exemptions();
// expire user subscription plans
// does not include recurring subscription plans as they are handled using a different logic (deleted then recreated based on paypal hitting the ipn script)
$notice .= $ilance->subscription_expiry->user_subscription_plans();
if (empty($notice))
{
        $notice = 'None';
}

($apihook = $ilance->api('cron_subscriptions_end')) ? eval($apihook) : false;

$ilance->timer->stop();
log_cron_action('{_the_following_subscription_tasks_were_executed} ' . $notice, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>