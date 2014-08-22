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
$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=feedback', $_SESSION['ilancedata']['user']['slng']);
$area_title = '{_feedback_manager}';
$page_title = SITE_NAME . ' - {_feedback_manager}';
$sqlfields = $sqlfieldsinput = '';
$sql = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "language
");
while ($res = $ilance->db->fetch_array($sql))
{
	$slng = mb_strtolower(mb_substr($res['languagecode'], 0, 3));
	$sqlfields .= "title_$slng, ";
	$res['slng'] = $slng;
	$res['code'] = $res['languagecode'];
	$languages[] = $res;
}
// #### ADD FEEDBACK RATING CRITERIA ###########################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'add')
{
	if (empty($ilance->GPC['sort']))
	{
		$ilance->GPC['sort'] = 100;
	}
	foreach ($ilance->GPC['title'] AS $shortlang => $input)
	{
		$sqlfieldsinput .= "'" . $ilance->db->escape_string($input) . "',";
	}
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "feedback_criteria
		(id, $sqlfields sort)
		VALUES(
		NULL,
		$sqlfieldsinput
		'" . intval($ilance->GPC['sort']) . "')
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=feedback');
	exit();
}
// #### REMOVE FEEDBACK RATING CRITERIA ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'removecriteria' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "feedback_criteria
		WHERE id = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=feedback');
	exit();
}
// #### UPDATE FEEDBACK RATING CRITERIA ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update' AND !empty($ilance->GPC['sort']) AND is_array($ilance->GPC['sort']))
{
	//foreach ($ilance->GPC['title'] as $criteriaid => $title)
	foreach ($ilance->GPC['title'] AS $shortlanguage => $array)
	{
		foreach ($array AS $criteriaid => $title)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "feedback_criteria
				SET title_$shortlanguage = '" . $ilance->db->escape_string($title) . "'
				WHERE id = '" . intval($criteriaid) . "'
			");
		}
	}
	foreach ($ilance->GPC['sort'] AS $criteriaid => $sort)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "feedback_criteria
			SET sort = '" . intval($sort) . "'
			WHERE id = '" . intval($criteriaid) . "'
		");
	}
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=feedback');
	exit();
}
// #### DELETE FEEDBACK ########################################
else if (isset($ilance->GPC['remove']) AND !empty($ilance->GPC['remove']))
{
	foreach ($ilance->GPC['id'] AS $key => $value)
	{
		$ilance->db->query("
			DELETE FROM " . DB_PREFIX . "feedback
			WHERE id = '" . intval($value) . "'
			LIMIT 1
		");
	}
}
// #### UPDATE FEEDBACK  #######################################
else if (isset($ilance->GPC['update']) AND !empty($ilance->GPC['update']))
{
	foreach ($ilance->GPC['id'] AS $key => $value)
	{
		$index = 'comm_'.$value;
		$com = $ilance->GPC[$index];
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "feedback
			SET comments = '" . $ilance->db->escape_string($com) . "'
			WHERE id = '" . intval($value) . "'
			LIMIT 1
		");
	}
}
$area_title = '{_feedback_criteria_manager}';
$page_title = SITE_NAME . ' - {_feedback_criteria_manager}';

($apihook = $ilance->api('admincp_feedback_settings')) ? eval($apihook) : false;

// #### is admin searching feedback?
$queryextra = '';
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'search')
{
	// searching via auction listing id?
	if (isset($ilance->GPC['project_id']) AND !empty($ilance->GPC['project_id']) AND $ilance->GPC['project_id'] > 0)
	{
		$queryextra .= " AND project_id = '" . intval($ilance->GPC['project_id']) . "'";	
	}
	if (isset($ilance->GPC['rangepast']) AND !empty($ilance->GPC['rangepast']))
	{
		$startdate = print_datetime_from_timestamp(print_convert_to_timestamp($ilance->GPC['rangepast']));
		$enddate = print_datetime_from_timestamp(time());
		$queryextra .= " AND (date_added <= '" . $enddate . "' AND date_added >= '" . $startdate . "')";
	}
}
// #### latest feedback recorded ###############################
if (!isset($ilance->GPC['page']) OR isset($ilance->GPC['page']) AND $ilance->GPC['page'] <= 0)
{
	$ilance->GPC['page'] = 1;
}
else
{
	$ilance->GPC['page'] = intval($ilance->GPC['page']);
}
$limit = ' ORDER BY date_added DESC LIMIT ' . (($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay']) . ',' . $ilconfig['globalfilters_maxrowsdisplay'];
$show['nolatestfeedback'] = true;
$sql = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "feedback
	WHERE for_user_id > 0
		AND from_user_id > 0
		AND project_id > 0
	$queryextra
	ORDER BY date_added DESC
");
$numberrows = $ilance->db->num_rows($sql);
$counter = ($ilance->GPC['page'] - 1) * $ilconfig['globalfilters_maxrowsdisplay'];
$row_count = 0;
$sql = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "feedback
	WHERE for_user_id > 0
		AND from_user_id > 0
		AND project_id > 0
	$queryextra
	$limit
");
if ($ilance->db->num_rows($sql) > 0)
{
	$row_count = 0;
	$show['nolatestfeedback'] = false;
	while ($row = $ilance->db->fetch_array($sql))
	{
		$row['cb'] = '<input type="checkbox" name="id[]" value="' . $row['id'] . '" />';
		$row['userby'] = fetch_user('username', $row['from_user_id']);
		$row['userfor'] = fetch_user('username', $row['for_user_id']);
		$type = fetch_auction('project_state', $row['project_id']);
		if ($type == 'service')
		{
			$row['auction'] = '<a href="' . HTTP_SERVER . $ilpage['rfp'] . '?id=' . $row['project_id'] . '" target="_blank">' . fetch_auction('project_title', $row['project_id']) . '</a> <span class="litegray">(' . $row['project_id'] . ')</span>';
		}
		else
		{
			$row['auction'] = '<a href="' . HTTP_SERVER . $ilpage['merch'] . '?id=' . $row['project_id'] . '" target="_blank">' . fetch_auction('project_title', $row['project_id']) . '</a> <span class="litegray">(' . $row['project_id'] . ')</span>';
		}

		($apihook = $ilance->api('feedback_direct')) ? eval($apihook) : false;

		if ($row['response'] == 'negative')
		{
			$row['response'] = '<span style="color:red">' . ucwords($row['response']). '</span>';
		}
		else if (($row['response'] == 'neutral'))
		{
			$row['response'] = '<span style="color:black">' . ucwords($row['response']). '</span>';
		}
		else
		{
			$row['response'] = ucwords($row['response']);
		}
		$id = $row['id'];
		$row['comments'] = '<input type="text" name="comm_' . $id . '" id="comm_' . $id . '" value="' . handle_input_keywords($row['comments']) . '" style="width:290px" class="input">';
		// since comments will be shown in bubble, addslashes where possible
		//$row['comments'] = addslashes($row['comments']);
		$row['date'] = print_date($row['date_added']);
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$latestfeedback[] = $row;
		$row_count++;
	}
}
$urlquery = print_hidden_fields($string = true, $excluded = array('cmd','page'), $questionmarkfirst = false);
$prevnext = print_pagnation($numberrows, $ilconfig['globalfilters_maxrowsdisplay'], $ilance->GPC['page'], $counter, $ilpage['settings'] . '?cmd=feedback' . $urlquery);
// #### feedback criteria ######################################
$sql = $ilance->db->query("
	SELECT id, $sqlfields sort
	FROM " . DB_PREFIX . "feedback_criteria
	ORDER BY sort ASC
");
if ($ilance->db->num_rows($sql) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sql))
	{
		$row['title'] = '';
		foreach ($languages AS $shortlang)
		{
			$row['title'] .= '<div align="right"><span class="gray" style="float:left; padding-right:7px; padding-top:4px">' . ucfirst($shortlang['code']) . ': </span><input type="text" name="title[' . $shortlang['slng'] . '][' . $row['id'] . ']" value="' . stripslashes($row["title_$shortlang[slng]"]) . '" class="input" style="width:85%" /></div><div style="padding-top:5px"></div>';
		}
		$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=feedback&amp;subcmd=removecriteria&amp;id=' . $row['id'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['sortaction'] = '<input type="text" name="sort[' . $row['id'] . ']" value="' . $row['sort'] . '" class="input" size="3" style="text-align: center" />';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		// fetch total number of ratings for this specific criteria
		$sql2 = $ilance->db->query("
			SELECT COUNT(*) AS count
			FROM " . DB_PREFIX . "feedback_ratings
			WHERE criteria_id = '" . $row['id'] . "'
		");
		if ($ilance->db->num_rows($sql) > 0)
		{
			$res2 = $ilance->db->fetch_array($sql2);
			$row['ratings'] = $res2['count'];
		}
		else
		{
			$row['ratings'] = 0;
		}
		$feedback[] = $row;
		$row_count++;
	}
	$show['no_feedback_rows'] = false;
}
else
{
	$show['no_feedback_rows'] = true;
}
$titlesinput = $validation_if = '';
foreach ($languages AS $shortlang)
{
	$titlesinput .= '<div align="right"><span class="gray" style="float:left; padding-right:7px; padding-top:4px">' . ucfirst($shortlang['code']) . ': </span><input type="text" name="title[' . $shortlang['slng'] . ']" id="title[' . $shortlang['slng'] . ']" style="width:85%" class="input" /></div>';
	$validation_if .= empty($validation_if) ? '' : ' && ';
	$validation_if .= 'fetch_js_object(\'title[' . $shortlang['slng'] . ']\') && fetch_js_object(\'title[' . $shortlang['slng'] . ']\').value != \'\'';
}
$headinclude .= '<script type="text/javascript">
<!--
function validate_add_criteria()
{
if (' . $validation_if . ')
{
	return true;
}
alert_js(phrase[\'_please_fill_all_fields\']);
return false;
}
//-->
</script>';
// #### reporting range pulldown ###############################
$reportrange = '<select name="rangepast" class="select"><option value="">{_any_day}</option><option value="-1 day"';
if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 day")
{
	$reportrange .= ' selected="selected"'; 
}
$reportrange .= '>{_the_past_day}</option><option value="-1 week"';
if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 week")
{
	$reportrange .= ' selected="selected"'; 
}
$reportrange .= '>{_the_past_week}</option><option value="-1 month"';
if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 month")
{
	$reportrange .= ' selected="selected"';
}
$reportrange .= '>{_the_past_month}</option><option value="-1 year"'; 
if (isset($ilance->GPC['rangepast']) AND $ilance->GPC['rangepast'] == "-1 year")
{
	$reportrange .= ' selected="selected"';
}
$reportrange .= '>{_the_past_year}</option></select>';
$pprint_array = array('prevnext','reportrange','titlesinput','roletypepulldown','roleusertypepulldown','role_pulldown','migrate_billing_pulldown','migrate_plan_pulldown','commission_group_pulldown','permission_group_pulldown','currency','new_resource_item');

($apihook = $ilance->api('admincp_feedback_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'feedback.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'feedback');
$ilance->template->parse_loop('main', 'latestfeedback');
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();
/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>