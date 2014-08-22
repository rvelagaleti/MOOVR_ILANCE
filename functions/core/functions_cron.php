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

/**
* Core Cron Job and automated task functions for iLance.
*
* @package      iLance\Global\Cron
* @version      4.0.0.8059
* @author       ILance
*/

/**
* Function to log actions created by specific cron job tasks in the system
*
* @param	string		description of task
* @param	array		array holding the next cron job item details
* @param        integer         script/task execution time
*
* @return	nothing
*/
function log_cron_action($description, $nextitem, $time = 0)
{
	global $ilance;
	if ($nextitem['loglevel'])
	{
		$ilance->db->query("
			INSERT INTO " . DB_PREFIX . "cronlog
			(varname, dateline, description, time)
			VALUES(
			'" . $ilance->db->escape_string($nextitem['varname']) . "',
			" . TIMESTAMPNOW . ",
			'" . $ilance->db->escape_string($description) . "',
			'" . $ilance->db->escape_string($time) . "')
		");
	}
}

/**
* Fetches the next run time for a particular cron job
*
* @param	string		date array
* @param	integer		hour
* @param	integer		minute
*
* @return	array		Return single date array
*/
function fetch_cron_next_run($data, $hour = -2, $minute = -2)
{
	if ($hour == -2)
	{
		$hour = intval(date('H', TIMESTAMPNOW));
	}
	if ($minute == -2)
	{
		$minute = intval(date('i', TIMESTAMPNOW));
	}
	$data['minute'] = unserialize($data['minute']);
	if ($data['hour'] == -1 AND $data['minute'][0] == -1)
	{
		$newdata['hour'] = $hour;
		$newdata['minute'] = $minute + 1;
	}
	else if ($data['hour'] == -1 AND $data['minute'][0] != -1)
	{
		$newdata['hour'] = $hour;
		$nextminute = fetch_cron_next_minute($data['minute'], $minute);
		if ($nextminute === false)
		{
			++$newdata['hour'];
			$nextminute = $data['minute'][0];
		}
		$newdata['minute'] = $nextminute;
	}
	else if ($data['hour'] != -1 AND $data['minute'][0] == -1)
	{
		if ($data['hour'] < $hour)
		{
			$newdata['hour'] = -1;
			$newdata['minute'] = -1;
		}
		else if ($data['hour'] == $hour)
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = $minute + 1;
		}
		else
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = 0;
		}
	}
	else if ($data['hour'] != -1 AND $data['minute'][0] != -1)
	{
		$nextminute = fetch_cron_next_minute($data['minute'], $minute);
		if ($data['hour'] < $hour OR ($data['hour'] == $hour AND $nextminute === false))
		{
			$newdata['hour'] = -1;
			$newdata['minute'] = -1;
		}
		else
		{
			$newdata['hour'] = $data['hour'];
			$newdata['minute'] = $nextminute;
		}
	}
	return $newdata;
}

/**
* Fetches the next minute for a particular cron job
*
* @param	array		minute array
* @param	integer		minute
*
* @return	boolean
*/
function fetch_cron_next_minute($minutedata, $minute)
{
	foreach ($minutedata AS $nextminute)
	{
		if ($nextminute > $minute)
		{
			return $nextminute;
		}
	}
	return false;
}

/**
* Function to determine the next run time for a particular task within the ILance automation system
*
* @param	integer         cron id
* @param        array           cron data array
*
* @return	integer         returns next run time (or 0)
*/
function construct_cron_item($cronid, $data = '')
{
	global $ilance;
	if (!is_array($data))
	{
		$data = $ilance->db->query_fetch("
			SELECT cronid, nextrun, weekday, day, hour, minute, filename, loglevel, active, varname, product
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . intval($cronid) . "'
		");
	}
	$minutenow = intval(date('i', TIMESTAMPNOW));
	$hournow = intval(date('H', TIMESTAMPNOW));
	$daynow = intval(date('d', TIMESTAMPNOW));
	$monthnow = intval(date('m', TIMESTAMPNOW));
	$yearnow = intval(date('Y', TIMESTAMPNOW));
	$weekdaynow = intval(date('w', TIMESTAMPNOW));
	if ($data['weekday'] == -1)
	{
		if ($data['day'] == -1)
		{
			$firstday = $daynow;
			$secondday = $daynow + 1;
		}
		else
		{
			$firstday = $data['day'];
			$secondday = $data['day'] + date('t', TIMESTAMPNOW);
		}
	}
	else
	{
		$firstday = $daynow + ($data['weekday'] - $weekdaynow);
		$secondday = $firstday + 7;
	}
	if ($firstday < $daynow)
	{
		$firstday = $secondday;
	}
	if ($firstday == $daynow)
	{
		$todaytime = fetch_cron_next_run($data);
		if ($todaytime['hour'] == -1 AND $todaytime['minute'] == -1)
		{
			$data['day'] = $secondday;
			$newtime = fetch_cron_next_run($data, 0, -1);
			$data['hour'] = $newtime['hour'];
			$data['minute'] = $newtime['minute'];
		}
		else
		{
			$data['day'] = $firstday;
			$data['hour'] = $todaytime['hour'];
			$data['minute'] = $todaytime['minute'];
		}
	}
	else
	{
		$data['day'] = $firstday;
		$newtime = fetch_cron_next_run($data, 0, -1);
		$data['hour'] = $newtime['hour'];
		$data['minute'] = $newtime['minute'];
	}
	$nextrun = mktime($data['hour'], $data['minute'], 0, $monthnow, $data['day'], $yearnow);
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "cron
		SET nextrun = '" . $nextrun . "'
		WHERE cronid = '" . intval($cronid) . "'
			AND nextrun = '" . $data['nextrun'] . "'
	");
	$norun = ($ilance->db->affected_rows() > 0);
	build_cron_next_runtime($nextrun);
	return iif($norun, $nextrun, 0);
}

/**
* Function to build a cron job's next execution time
*
* @param	integer         cron id
*
* @return	array           array with next run information
*/
function build_cron_next_runtime($nextrun = '')
{
	global $ilance;
	if (!$nextcron = $ilance->db->query_fetch("SELECT MIN(nextrun) AS nextrun FROM " . DB_PREFIX . "cron AS cron"))
	{
		$nextcron['nextrun'] = TIMESTAMPNOW + 60 * 60;
	}
	return $nextrun;
}

/**
* Function to execute a task within the cron job system
*
* @param	integer         cron id (default null)
*
* @return	nothing
*/
function execute_task($cronid = null)
{
	global $ilance, $phrase, $ilconfig, $show;
	if ($cronid = intval($cronid))
	{
		$nextitem = $ilance->db->query_fetch("
			SELECT cronid, nextrun, weekday, day, hour, minute, filename, loglevel, active, varname, product
			FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . $cronid . "'
		");
	}
	else
	{
		$nextitems = $ilance->db->query("
			SELECT cron.cronid, cron.nextrun, cron.weekday, cron.day, cron.hour, cron.minute, cron.filename, cron.loglevel, cron.active, cron.varname, cron.product
			FROM " . DB_PREFIX . "cron AS cron
			WHERE (cron.nextrun <= " . TIMESTAMPNOW . " AND cron.active = 1) OR (cron.nextrun+900 <= " . TIMESTAMPNOW . " AND cron.active = -1)
			ORDER BY cron.nextrun
		");
	}
	if (isset($nextitem))
	{
		if ($nextrun = construct_cron_item($nextitem['cronid'], $nextitem))
		{
			if (!empty($nextitem['filename']) AND file_exists(DIR_CRON . $nextitem['filename']))
			{
				lock_task($nextitem['cronid']);
				include_once(DIR_CRON . $nextitem['filename']);
				unlock_task($nextitem['cronid']);
			}
		}	
	}
	else if (isset($nextitems))
	{
		while ($nextitem = $ilance->db->fetch_array($nextitems, DB_ASSOC))
		{
			if ($nextrun = construct_cron_item($nextitem['cronid'], $nextitem))
			{
				if (!empty($nextitem['filename']) AND file_exists(DIR_CRON . $nextitem['filename']))
				{
					lock_task($nextitem['cronid']);
					include_once(DIR_CRON . $nextitem['filename']);
					unlock_task($nextitem['cronid']);
				}
			}	
		}
	}
	else
	{
		build_cron_next_runtime();
	}
}

/**
* Function to lock a task based on a cron id supplied argument
*
* @param	integer         cron id (default null)
*
* @return	nothing
*/
function lock_task($cronid)
{
	global $ilance;
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "cron
		SET active = -1
		WHERE cronid = '" . intval($cronid) . "'
	");	
}	

/**
* Function to unlock a task based on a cron id supplied argument
*
* @param	integer         cron id (default null)
*
* @return	nothing
*/
function unlock_task($cronid)
{
	global $ilance;
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "cron
		SET active = 1
		WHERE cronid = '" . intval($cronid) . "'
	");	
}	

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>