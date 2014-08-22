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

($apihook = $ilance->api('cron_mailqueue_start')) ? eval($apihook) : false;

if (isset($ilconfig['emailssettings_queueenabled']) AND $ilconfig['emailssettings_queueenabled'])
{
	$s = $ns = 0;
	$sql = $ilance->db->query("
		SELECT id, mail, fromemail, fromname, departmentid, subject, message, dohtml, date_added, varname, type
		FROM " . DB_PREFIX . "email_queue
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$ilance->email->toqueue = false;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$ilance->email->mail = $res['mail'];
			$ilance->email->from = $res['fromemail'];
			$ilance->email->fromname = $res['fromname'];
			$ilance->email->departmentid = $res['departmentid'];
			$ilance->email->subject = $res['subject'];
			$ilance->email->message = $res['message'];
			$ilance->email->dohtml = $res['dohtml'];
			$ilance->email->varname = $res['varname'];
			$ilance->email->type = $res['type'];
			$ilance->email->send();
			$ilance->db->query("DELETE FROM " . DB_PREFIX . "email_queue WHERE id = '" . intval($res['id']) . "'");
			if ($ilance->email->sent)
			{
				$s++;
			}
			else
			{
				$ns++;
			}
		}
	}
	$cronlog .= $s . ' emails from email queue were sent and ' . $ns . ' were not sent. ';
}

($apihook = $ilance->api('cron_mailqueue_end')) ? eval($apihook) : false;

$ilance->timer->stop();
log_cron_action('Mail queue: ' . $cronlog, $nextitem, $ilance->timer->get());

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>