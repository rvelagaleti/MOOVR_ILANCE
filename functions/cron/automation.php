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

require_once(DIR_CORE . 'functions_cron.php');
$cronid = isset($_SERVER['argv'][1]) ? intval($_SERVER['argv'][1]) : null;
if ($cronid < 1)
{
        $cronid = null;
}
execute_task($cronid);  

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>