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

// disable time limit for running scripts
@ignore_user_abort(1);
@set_time_limit(0);

// #### setup script location ##################################################
define('LOCATION','cron');
define('SKIP_SESSION', true);

// #### require backend ########################################################
require_once('functions/config.php');

($apihook = $ilance->api('cron_start')) ? eval($apihook) : false;

// load cron engine
include(DIR_CRON . 'automation.php');
if (isset($ilance->GPC['type']) AND $ilance->GPC['type'] == 'image')
{
	header('Location: ' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'spacer.gif');
}

($apihook = $ilance->api('cron_end')) ? eval($apihook) : false;

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>