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
if (!defined('LOCATION') OR defined('LOCATION') != 'admin')
{
	die('<strong>Fatal:</strong> This script cannot be parsed indirectly.');
}
$area_title = '{_scheduled_tasks_and_automation_menu}';
$page_title = SITE_NAME . ' - {_scheduled_tasks_and_automation_menu}';

($apihook = $ilance->api('admincp_automation_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=automation', $_SESSION['ilancedata']['user']['slng']);

// #### PRUNE TASKS ####################################################
if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'prune')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['cronid'] = intval($ilance->GPC['cronid']);
	$ilance->GPC['varname'] = $ilance->admincp->fetch_task_varname($ilance->GPC['cronid']);
	$ilance->GPC['days'] = intval($ilance->GPC['days']);
	$ilance->GPC['cutoff'] = TIMESTAMPNOW - (86400 * $ilance->GPC['days']);
	$conds = '';
	if (!empty($ilance->GPC['varname']))
	{
		$conds = " AND varname = '" . $ilance->db->escape_string($ilance->GPC['varname']) . "'";
	}
	$sql = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "cronlog
			WHERE dateline < " . $ilance->GPC['cutoff'] . " " . $conds);
	$count = number_format($ilance->db->num_rows($sql));
	$ilance->db->query("
			DELETE
			FROM " . DB_PREFIX . "cronlog
			WHERE dateline < " . $ilance->GPC['cutoff'] . " " . $conds);

	print_action_success('{_scheduled_task_logs_pruned}: ' . $count, $ilance->GPC['return']);
	exit();
}
// #### UPDATE SCHEDULE TASK ###########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-crontab')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['title'] = str_replace(' ', '_', $ilance->GPC['title']);
	$newminute = array (0 => -1);
	if (isset($ilance->GPC['minute']))
	{
		foreach ($ilance->GPC['minute'] AS $key => $value)
		{
			if ($value != '-1')
			{
				$newminute[$key] = $value;
			}
		}
	}
	$ilance->GPC['minute'] = serialize($newminute);
	$ilance->db->query("
		    UPDATE " . DB_PREFIX . "cron
		    SET weekday = '" . intval($ilance->GPC['weekday']) . "',
			day = '" . intval($ilance->GPC['day']) . "',
			hour = '" . intval($ilance->GPC['hour']) . "',
			minute = '" . $ilance->db->escape_string($ilance->GPC['minute']) . "',
			filename = '" . $ilance->db->escape_string($ilance->GPC['filename']) . "',
			loglevel = '" . intval($ilance->GPC['loglevel']) . "',
			active = '1',
			varname = '" . $ilance->db->escape_string($ilance->GPC['title']) . "',
			product = '" . $ilance->db->escape_string($ilance->GPC['product']) . "',
			nextrun = '" . TIMESTAMPNOW . "'
		    WHERE cronid = '" . intval($ilance->GPC['cronid']) . "'
		    LIMIT 1
		");
	print_action_success('{_existing_scheduled_task_event_was_successfully_updated}', $ilance->GPC['return']);
	exit();
}
// #### REMOVE SCHEDULED TASK ##########################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove' AND isset($ilance->GPC['cronid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "cron
			WHERE cronid = '" . intval($ilance->GPC['cronid']) . "'
			LIMIT 1
		");
	print_action_success('{_scheduled_task_has_been_removed_from_the_cron_system}', $ilpage['settings'] . '?cmd=automation');
	exit();
}
// #### RUN TASK #######################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'run' AND isset($ilance->GPC['cronid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "cron
		SET nextrun = '" . TIMESTAMPNOW . "',
		active = '1'
		WHERE cronid = '" . intval($ilance->GPC['cronid']) . "'
		LIMIT 1
	");
	print_action_success('{_scheduled_task_has_been_launched}', $ilpage['settings'] . '?cmd=automation');
	exit();
}
// #### ADD NEW TASK ###################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add-new-task' AND isset($ilance->GPC['title']) AND isset($ilance->GPC['filename']) AND $ilance->GPC['filename'] != '.php')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['title'] = str_replace(' ', '_', $ilance->GPC['title']);
	$ilance->GPC['product'] = isset($ilance->GPC['product']) ? $ilance->GPC['product'] : 'ilance';
	$newminute = array (0 => 0);
	if (isset($ilance->GPC['minute']))
	{
		foreach ($ilance->GPC['minute'] AS $key => $value)
		{
			if ($value != '-1')
			{
				$newminute[$key] = $value;
			}
		}
	}
	$ilance->GPC['minute'] = serialize($newminute);
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "cron
		(cronid, nextrun, weekday, day, hour, minute, filename, loglevel, active, varname, product)
		VALUES
		(NULL,
		'" . TIMESTAMPNOW . "',
		'" . intval($ilance->GPC['weekday']) . "',
		'" . intval($ilance->GPC['day']) . "',
		'" . intval($ilance->GPC['hour']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['minute']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['filename']) . "',
		'" . intval($ilance->GPC['loglevel']) . "',
		'1',
		'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['product']) . "')
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=automation');
	exit();
}
// #### EDIT TASK ######################################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'edit' AND isset($ilance->GPC['cronid']))
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "cron
		WHERE cronid = '" . intval($ilance->GPC['cronid']) . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql))
		{
			$minutes = stripslashes($res['minute']);
			$minutes = unserialize($minutes);
			#### MINUTES 1 #################################
			$res['minute1'] = '<select name="minute[0]" tabindex="1" class="select-75">';
			if (!isset($minutes[1]))
			{
				$res['minute1'] .= '<option value="-1" selected="selected">*</option>';
			}
			else
			{
				$res['minute1'] .= '<option value="-1">*</option>';
			}
			for ($m = 0; $m <= 59; $m++)
			{
				if (isset($minutes[0]) AND $minutes[0] == $m)
				{
					$res['minute1'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
				}
				else
				{
					$res['minute1'] .= '<option value="' . $m . '">' . $m . '</option>';
				}
			}
			$res['minute1'] .= '</select>';
			#### MINUTES 2 #################################
			$res['minute2'] = '<select name="minute[1]" tabindex="1" class="select-75">';
			if (!isset($minutes[1]))
			{
				$res['minute2'] .= '<option value="-1" selected="selected">-</option>';
			}
			else
			{
				$res['minute2'] .= '<option value="-1">-</option>';
			}
			for ($m = 0; $m <= 59; $m++)
			{
				if (isset($minutes[1]) AND $minutes[1] == $m)
				{
					$res['minute2'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
				}
				else
				{
					$res['minute2'] .= '<option value="' . $m . '">' . $m . '</option>';
				}
			}
			$res['minute2'] .= '</select>';
			#### MINUTES 3 #################################
			$res['minute3'] = '<select name="minute[2]" tabindex="1" class="select-75">';
			if (!isset($minutes[2]))
			{
				$res['minute3'] .= '<option value="-1" selected="selected">-</option>';
			}
			else
			{
				$res['minute3'] .= '<option value="-1">-</option>';
			}
			for ($m = 0; $m <= 59; $m++)
			{
				if (isset($minutes[2]) AND $minutes[2] == $m)
				{
					$res['minute3'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
				}
				else
				{
					$res['minute3'] .= '<option value="' . $m . '">' . $m . '</option>';
				}
			}
			$res['minute3'] .= '</select>';
			#### MINUTES 4 #################################
			$res['minute4'] = '<select name="minute[3]" tabindex="1" class="select-75">';
			if (!isset($minutes[3]))
			{
				$res['minute4'] .= '<option value="-1" selected="selected">-</option>';
			}
			else
			{
				$res['minute4'] .= '<option value="-1">-</option>';
			}
			for ($m = 0; $m <= 59; $m++)
			{
				if (isset($minutes[3]) AND $minutes[3] == $m)
				{
					$res['minute4'] .= '<option value="' . $m . '" selected="selected">' . $m . '</option>';
				}
				else
				{
					$res['minute4'] .= '<option value="' . $m . '">' . $m . '</option>';
				}
			}
			$res['minute4'] .= '</select>';
			#### HOURS #####################################
			$res['hours'] = '<select name="hour" id="sel_hour" tabindex="1" class="select-75">';
			if ($res['hour'] == '-1')
			{
				$res['hours'] .= '<option value="-1" selected="selected">*</option>';
			}
			else
			{
				$res['hours'] .= '<option value="-1">*</option>';
			}
			for ($h = 0; $h <= 23; $h++)
			{
				if (isset($res['hour']) AND $res['hour'] == $h)
				{
					$res['hours'] .= '<option value="' . $h . '" selected="selected">' . $h . '</option>';
				}
				else
				{
					$res['hours'] .= '<option value="' . $h . '">' . $h . '</option>';
				}
			}
			$res['hours'] .= '</select>';
			#### DAYS OF THE WEEK ##########################
			$res['dow'] = '<select name="weekday" id="sel_weekday" tabindex="1" class="select-75">';
			if ($res['weekday'] == '-1')
			{
				$res['dow'] .= '<option value="-1" selected="selected">*</option>';
			}
			else
			{
				$res['dow'] .= '<option value="-1">*</option>';
			}
			$days = array ('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
			for ($dow = 0; $dow <= 6; $dow++)
			{
				$day = $days[$dow];
				$weekday = '{_' . $day . '}';
				if (isset($res['weekday']) AND $res['weekday'] == $dow)
				{
					$res['dow'] .= '<option value="' . $dow . '" selected="selected">' . $weekday . '</option>';
				}
				else
				{
					$res['dow'] .= '<option value="' . $dow . '">' . $weekday . '</option>';
				}
			}
			$res['dow'] .= '</select>';
			#### DAY OF THE MONTH ##########################
			$res['dom'] = '<select name="day" id="sel_day" tabindex="1" class="select-75">';
			if ($res['weekday'] == '-1')
			{
				if ($res['day'] == '-1')
				{
					$res['dom'] .= '<option value="-1" selected="selected">*</option>';
				}
				else
				{
					$res['dom'] .= '<option value="-1">*</option>';
				}
			}
			for ($dom = 1; $dom <= 31; $dom++)
			{
				if (isset($res['day']) AND $res['day'] == $dom)
				{
					$res['dom'] .= '<option value="' . $dom . '" selected="selected">' . $dom . '</option>';
				}
				else
				{
					$res['dom'] .= '<option value="' . $dom . '">' . $dom . '</option>';
				}
			}
			$res['dom'] .= '</select>';
			$savelog_1 = '';
			$savelog_0 = 'checked="checked"';
			if ($res['loglevel'] == 1)
			{
				$savelog_1 = 'checked="checked"';
				$savelog_0 = '';
			}
			$res['products_pulldown'] = $ilance->admincp->products_pulldown($res['product']);
			$tasks[] = $res;
		}
		$cronid = isset($ilance->GPC['cronid']) ? intval($ilance->GPC['cronid']) : 0;
		$pprint_array = array ('cronid', 'savelog_1', 'savelog_0', 'automationsettings', 'crontab');

		($apihook = $ilance->api('admincp_automation_edit_end')) ? eval($apihook) : false;

		$ilance->template->fetch('main', 'automation_edit.html', 1);
		$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
		$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
		$ilance->template->parse_loop('main', 'tasks');
		$ilance->template->parse_if_blocks('main');
		$ilance->template->pprint('main', $pprint_array);
		exit();
	}
}
// #### SCHEDULED TASKS ################################################
else
{
	$sql = $ilance->db->query("
		SELECT cron.*, AVG(cronlog.time) AS average
		FROM " . DB_PREFIX . "cron cron
		LEFT JOIN " . DB_PREFIX . "cronlog cronlog ON (cron.varname = cronlog.varname)
		GROUP BY cron.filename
		ORDER BY nextrun ASC
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$count = 0;
		while ($res = $ilance->db->fetch_array($sql, DB_ASSOC))
		{
			$nextrun = $ilance->datetimes->fetch_datetime_from_timestamp($res['nextrun']);
			$res['nextrun'] = print_date($nextrun, $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
			$timerule = $ilance->admincp->fetch_cron_schedule($res);
			$res['minute'] = $timerule['minute'];
			$res['hour'] = $timerule['hour'];
			$res['day'] = $timerule['day'];
			$res['month'] = $timerule['month'];
			$res['day_of_week'] = $timerule['weekday'];
			$res['job'] = $res['filename'];
			$res['average'] = number_format($res['average'], 1) . '{_s_shortform}';
			if ($res['product'] == 'ilance' OR empty($res['product']))
			{
				$res['product'] = 'ILance';
			}
			else
			{
				$res['product'] = ucfirst($res['product']);
			}
			if ($show['ADMINCP_TEST_MODE'])
			{
				$res['action'] = '<div><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil_gray.gif" border="0" alt="" /> &nbsp; <img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete_gray.gif" border="0" alt="" /></div>';
			}
			else
			{
				$run = ($res['active'] == '1') ? '<a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=run&amp;cronid=' . $res['cronid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/selectedcat.gif" border="0" alt="" /></a>' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/selectedcat_gray.gif" border="0" alt="" />';
				$res['action'] = '<div>' . $run . ' &nbsp; 
					<a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=edit&amp;cronid=' . $res['cronid'] . '"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a> &nbsp; 
					<a href="' . $ilpage['settings'] . '?cmd=automation&amp;subcmd=remove&amp;cronid=' . $res['cronid'] . '" style="color:#990000" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a></div>';
			}
			$res['class'] = ($count % 2) ? 'alt2' : 'alt1';
			$count++;
			$tasks[] = $res;
		}
	}
	$selected = isset($ilance->GPC['cronid']) ? intval($ilance->GPC['cronid']) : '';
	$pp = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : '15';
	$tasks_pulldown = $ilance->admincp->print_scheduled_tasks_pulldown($selected);
	$products_pulldown = $ilance->admincp->products_pulldown();
	$minute_array['-1'] = '*';
	for ($i = 0; $i <= 59 ; $i++)
	{
	    $minute_array[$i] = $i;
	}
	$minute0 = construct_pulldown('minute[0]', 'minute[0]', $minute_array, '-1', 'tabindex="1" class="select-75"');
	$minute_array['-1'] = '-';
	$minute1 = construct_pulldown('minute[1]', 'minute[1]', $minute_array, '-1', 'tabindex="1" class="select-75"');
	$minute2 = construct_pulldown('minute[2]', 'minute[2]', $minute_array, '-1', 'tabindex="1" class="select-75"');
	$minute3 = construct_pulldown('minute[3]', 'minute[3]', $minute_array, '-1', 'tabindex="1" class="select-75"');
	unset($minute_array);
	
	$hour_array['-1'] = '*';
	for ($i = 0; $i <= 23 ; $i++)
	{
	    $hour_array[$i] = $i;
	}
	$hour = construct_pulldown('sel_hour"', 'hour', $hour_array, '-1', 'tabindex="1" class="select-75"');
	unset($hour_array);
	
	$day_array['-1'] = '*';
	for ($i = 1; $i <= 31 ; $i++)
	{
	    $day_array[$i] = $i;
	}
	$day = construct_pulldown('sel_day"', 'day', $day_array, '-1', 'tabindex="1" class="select-75"');
	unset($day_array);
	
	$pp_pulldown = construct_pulldown('sel_pp"', 'pp', array('5' => '5','10' => '10','15' => '15','20' => '20','25' => '25','30' => '30','40' => '40','50' => '50','100' => '100'), $pp, 'tabindex="1" class="select-75"');
	// #### VIEWING TASK LOG ###########################################
	if (isset($ilance->GPC['do']) AND $ilance->GPC['do'] == 'view')
	{
		// filters
		$ilance->GPC['pp'] = isset($ilance->GPC['pp']) ? intval($ilance->GPC['pp']) : $ilconfig['globalfilters_maxrowsdisplay'];
		$ilance->GPC['cronid'] = intval($ilance->GPC['cronid']);
		$ilance->GPC['where'] = '';
		if ($ilance->GPC['cronid'] > 0)
		{
			$ilance->GPC['where'] = "AND varname = '" . $ilance->admincp->fetch_task_varname($ilance->GPC['cronid']) . "'";
		}
		if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
		{
			$ilance->GPC['page'] = 1;
		}
		else
		{
			$ilance->GPC['page'] = intval($ilance->GPC['page']);
		}
		$ilance->GPC['displayorder'] = isset($ilance->GPC['displayorder']) ? $ilance->GPC['displayorder'] : 'asc';
		if (isset($ilance->GPC['displayorder']) AND !empty($ilance->GPC['displayorder']) AND ($ilance->GPC['displayorder'] == 'asc' OR $ilance->GPC['displayorder'] == 'desc'))
		{
			$ilance->GPC['displayorder'] = strip_tags($ilance->GPC['displayorder']);
		}

		$ilance->GPC['orderby'] = isset($ilance->GPC['orderby']) ? $ilance->GPC['orderby'] : 'dateline';
		if (isset($ilance->GPC['orderby']) AND !empty($ilance->GPC['orderby']) AND ($ilance->GPC['orderby'] == 'dateline' OR $ilance->GPC['orderby'] == 'time' OR $ilance->GPC['orderby'] == 'varname'))
		{
			$ilance->GPC['orderby'] = strip_tags($ilance->GPC['orderby']);
		}
		$ilance->GPC['limit'] = ' ORDER BY ' . $ilance->db->escape_string($ilance->GPC['orderby']) . ' ' . strtoupper($ilance->db->escape_string($ilance->GPC['displayorder'])) . ' LIMIT ' . (($ilance->GPC['page'] - 1) * $ilance->GPC['pp']) . ',' . $ilance->GPC['pp'];
		$crontmp = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "cronlog
				WHERE cronlogid > 0 " . $ilance->GPC['where'] . "
			");
		$ilance->GPC['totalcount'] = $ilance->db->num_rows($crontmp);
		$ilance->GPC['counter'] = ($ilance->GPC['page'] - 1) * $ilance->GPC['pp'];
		$cron = $ilance->db->query("
				SELECT *
				FROM " . DB_PREFIX . "cronlog
				WHERE cronlogid > 0 " . $ilance->GPC['where'] . " " . $ilance->GPC['limit']);
		if ($ilance->db->num_rows($cron) > 0)
		{
			$count = 0;
			while ($res = $ilance->db->fetch_array($cron, DB_ASSOC))
			{
				$res['varname'] = $ilance->admincp->scheduled_task_phrase($res['varname']);
				$res['class'] = ($count % 2) ? 'alt2' : 'alt1';
				$res['dateline'] = print_date($ilance->datetimes->fetch_datetime_from_timestamp($res['dateline']), $ilconfig['globalserverlocale_globaltimeformat'], 0, 0);
				$cronlog[] = $res;
				$count++;
			}
		}
		$prevnext = print_pagnation($ilance->GPC['totalcount'], $ilance->GPC['pp'], $ilance->GPC['page'], $ilance->GPC['counter'], $ilpage['settings'] . '?cmd=automation&amp;do=view&amp;cronid=' . $ilance->GPC['cronid'] . '&amp;orderby=' . $ilance->GPC['orderby']. '&amp;displayorder=' . $ilance->GPC['displayorder']);
	}
	$pprint_array = array ('pp_pulldown','day','hour','minute0','minute1','minute2','minute3','products_pulldown','prevnext','tasks_pulldown');

	($apihook = $ilance->api('admincp_automation_end')) ? eval($apihook) : false;

	$ilance->template->fetch('main', 'automation.html', 1);
	$ilance->template->parse_hash('main', array ('ilpage' => $ilpage));
	$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
	$ilance->template->parse_loop('main', 'tasks');
	$ilance->template->parse_loop('main', 'cronlog');
	$ilance->template->parse_if_blocks('main');
	$ilance->template->pprint('main', $pprint_array);
	exit();
}
	
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/

?>