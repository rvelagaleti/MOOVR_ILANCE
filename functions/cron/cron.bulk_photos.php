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
global $ilconfig;
$cronlog = '';
if ($ilconfig['globalfilters_bulkupload'])
{
	$cronlog .= $ilance->auction_pictures->process_bulk_upload_photos();
}
$ilance->timer->stop();
log_cron_action('{_the_bulk_upload_photos_tasks_were_successfully_executed} ' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>