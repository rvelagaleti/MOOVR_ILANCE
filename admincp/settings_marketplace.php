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

$area_title = '{_marketplace_configuration}';
$page_title = SITE_NAME . ' - {_marketplace_configuration}';

($apihook = $ilance->api('admincp_marketplace_settings')) ? eval($apihook) : false;

$subnav_settings = $ilance->admincp->print_admincp_subnav($ilpage['settings'], $ilpage['settings'] . '?cmd=marketplace', $_SESSION['ilancedata']['user']['slng']);
$question_id_hidden = '';

// #### ASSIGN BUDGET GROUP TO ALL CATEGORIES ##################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-budget' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET budgetgroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'service'
	");
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### ASSIGN SERVICE INSERTION GROUP TO ALL CATEGORIES #######
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-service-insertion' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'service'
	");
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### ASSIGN PRODUCT INSERTION GROUP TO ALL CATEGORIES #######
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-product-insertion' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'product'
	");
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### ASSIGN SERVICE FVF GROUP TO ALL CATEGORIES #############
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-service-finalvalue' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'service'
	");
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### ASSIGN PRODUCT FVF GROUP TO ALL CATEGORIES #############
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'assign-product-finalvalue' AND isset($ilance->GPC['title']))
{
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['title']) . "'
		WHERE cattype = 'product'
	");
	print_action_success('{_all_categories_have_been_assigned_to_the_selected_bid_increment_group}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### INSERT NEW SHIPPING PARTNER ############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-shipper')
{
	$ilance->GPC['title'] = isset($ilance->GPC['title']) ? $ilance->GPC['title'] : '';
	$ilance->GPC['shipcode'] = isset($ilance->GPC['shipcode']) ? $ilance->GPC['shipcode'] : '';
	$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? intval($ilance->GPC['domestic']) : 0;
	$ilance->GPC['international'] = isset($ilance->GPC['international']) ? intval($ilance->GPC['international']) : 0;
	$ilance->GPC['trackurl'] = isset($ilance->GPC['trackurl']) ? $ilance->db->escape_string($ilance->GPC['trackurl']) : '';
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->db->escape_string($ilance->GPC['sort']) : 0;
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "shippers
		(shipperid, title, shipcode, domestic, international, carrier, trackurl, sort)
		VALUES(
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['title']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['shipcode']) . "',
		'" . $ilance->GPC['domestic'] . "',
		'" . $ilance->GPC['international'] . "',
		'" . $ilance->db->escape_string($ilance->GPC['carrier']) . "',
		'" . $ilance->GPC['trackurl'] . "',
		'" . $ilance->GPC['sort'] . "')
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE SHIPPING PARTNER ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'remove-shipper' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "shippers
		WHERE shipperid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### UPDATE SHIPPING PARTNERS ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'update-shippers')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->GPC['international'] = isset($ilance->GPC['international']) ? $ilance->GPC['international'] : array();
	$ilance->GPC['title'] = isset($ilance->GPC['title']) ? $ilance->GPC['title'] : array();
	$ilance->GPC['domestic'] = isset($ilance->GPC['domestic']) ? $ilance->GPC['domestic'] : array();
	$ilance->GPC['carrier'] = isset($ilance->GPC['carrier']) ? $ilance->GPC['carrier'] : array();
	$ilance->GPC['shipcode'] = isset($ilance->GPC['shipcode']) ? $ilance->GPC['shipcode'] : array();
	$ilance->GPC['trackurl'] = isset($ilance->GPC['trackurl']) ? $ilance->GPC['trackurl'] : array();
	$ilance->GPC['sort'] = isset($ilance->GPC['sort']) ? $ilance->GPC['sort'] : array();
	foreach ($ilance->GPC['title'] AS $shipperid => $title)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET title = '" . $ilance->db->escape_string($title) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "shippers
		SET domestic = '0', international = '0'
	");
	foreach ($ilance->GPC['domestic'] AS $shipperid => $value)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET domestic = '" . intval($value) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	foreach ($ilance->GPC['international'] AS $shipperid => $value)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET international = '" . intval($value) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	foreach ($ilance->GPC['carrier'] AS $shipperid => $title)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET carrier = '" . $ilance->db->escape_string($title) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	foreach ($ilance->GPC['shipcode'] AS $shipperid => $title)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET shipcode = '" . $ilance->db->escape_string($title) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	foreach ($ilance->GPC['trackurl'] AS $shipperid => $trackurl)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET trackurl = '" . $ilance->db->escape_string($trackurl) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	foreach ($ilance->GPC['sort'] AS $shipperid => $sort)
	{
		$ilance->db->query("
			UPDATE " . DB_PREFIX . "shippers
			SET sort = '" . $ilance->db->escape_string($sort) . "'
			WHERE shipperid = '" . intval($shipperid) . "'
		");
	}
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### UPDATE FINAL VALUE FEE SORTING #########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-sort')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (!empty($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] AS $tierid => $sortvalue)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "finalvalue
				SET sort = '" . intval($sortvalue) . "'
				WHERE tierid = '" . intval($tierid) . "'
				LIMIT 1
			");
		}
		refresh($ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
}
// #### UPDATE INSERTION FEE SORTING ###########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-sort')
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	if (!empty($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] AS $insertionid => $sortvalue)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "insertion_fees
				SET sort = '" . intval($sortvalue) . "'
				WHERE insertionid = '" . intval($insertionid) . "'
				ORDER BY sort ASC
				LIMIT 1
			");
		}
		refresh($ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
}
// #### UPDATE BUDGET SORTING ##################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-sort')
{
	if (!empty($ilance->GPC['sort']))
	{
		foreach ($ilance->GPC['sort'] AS $budgetid => $sortvalue)
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "budget
				SET sort = '" . intval($sortvalue) . "'
				WHERE budgetid = '".intval($budgetid)."'
				LIMIT 1
			");
		}
		refresh($ilpage['settings'] . '?cmd=marketplace');
		exit();
	}
}
// #### ADD NEW BUDGET RANGE ###################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-budget-range' AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['budgetfrom']) AND isset($ilance->GPC['budgetto']) AND isset($ilance->GPC['groupid']))
{
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$groupname = $ilance->db->fetch_field(DB_PREFIX . "budget_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$insgroupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['insertiongroupid']) . "'", "groupname");
	$ilance->GPC['fieldname'] = str_replace(' ', '_', $ilance->GPC['title']);
	$ilance->GPC['fieldname'] = mb_strtolower($ilance->GPC['fieldname']);
	$ilance->GPC['fieldname'] = $ilance->GPC['fieldname'] . '_' . rand(1, 99999);
	$ilance->admincp->insert_budget_range($ilance->GPC['title'], $ilance->GPC['fieldname'], $ilance->GPC['budgetfrom'], $ilance->GPC['budgetto'], $groupname, $insgroupname, $sort);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### ADD INSERTION FEE ######################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-insertion' AND isset($ilance->GPC['insertion_from']) AND isset($ilance->GPC['insertion_to']) AND isset($ilance->GPC['amount']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
{
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$groupname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$ilance->admincp->insert_insertion_fee($ilance->GPC['insertion_from'], $ilance->GPC['insertion_to'], $ilance->GPC['amount'], $groupname, $sort, $ilance->GPC['state']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### ADD FINAL VALUE FEE ####################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-fv' AND isset($ilance->GPC['finalvalue_from']) AND isset($ilance->GPC['finalvalue_to']))
{
	$groupname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$ilance->admincp->insert_fv_fee($ilance->GPC['finalvalue_from'], $ilance->GPC['finalvalue_to'], $ilance->GPC['amountfixed'], $ilance->GPC['amountpercent'], intval($ilance->GPC['sort']), $groupname, $ilance->GPC['state']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### REMOVE INSERTION FEE ###################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-insertion-fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->admincp->remove_insertion_fee($ilance->GPC['id']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE INSERTION GROUP #################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-insertion-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	// remove group, remove fees tied to group, update categories group to '0' to disable fees
	$ilance->admincp->remove_insertion_group($ilance->GPC['groupid']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE FINAL VALUE FEE #################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-fv-fee' AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->admincp->remove_fv_fee($ilance->GPC['id']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE FINAL VALUE GROUP ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-fv-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	// remove group, remove fees tied to group, update categories group to '0' to disable fees
	$ilance->admincp->remove_fv_group($ilance->GPC['groupid']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}        
// #### ADD PROFILE QUESTION ###################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_add-profile-question' AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['question']))
{
	if ($ilance->GPC['verifycost'] != '0.00')
	{
		$ilance->GPC['verifycost'] = $ilance->GPC['verifycost'];
	}
	else
	{
	    $ilance->GPC['verifycost'] = '0.00';
	}
	if (!empty($ilance->GPC['sort']))
	{
		$ilance->GPC['sort'] = intval($ilance->GPC['sort']);
	}
	else
	{
		$ilance->GPC['sort'] = '10';
	}
	if (isset($ilance->GPC['profile_question_active']) AND $ilance->GPC['profile_question_active'])
	{
		$ilance->GPC['visible'] = '1';
	}
	else
	{
		$ilance->GPC['visible'] = '0';	    
	}
	if (isset($ilance->GPC['canverify']) AND $ilance->GPC['canverify'])
	{
		$ilance->GPC['canverify'] = '1';
	}
	else
	{
		$ilance->GPC['canverify'] = '0';
	}		
	if (isset($ilance->GPC['required']) AND $ilance->GPC['required'])
	{
		$ilance->GPC['required'] = '1';
	}
	else
	{
		$ilance->GPC['required'] = '0';	
	}
	if (isset($ilance->GPC['isfilter']) AND $ilance->GPC['isfilter'])
	{
		$ilance->GPC['isfilter'] = '1';
	}
	else
	{
		$ilance->GPC['isfilter'] = '0';	
	}
	
	if (isset($ilance->GPC['guests']) AND $ilance->GPC['guests'])
	{
		$ilance->GPC['guests'] = '1';
	}
	else
	{
		$ilance->GPC['guests'] = '0';	
	}		
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "profile_questions
		(questionid, groupid, question, description, inputtype, multiplechoice, sort, visible, required, canverify, verifycost, isfilter, filtertype, filtercategory, guests)
		VALUES(
		NULL,
		'" . intval($ilance->GPC['groupid']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['question']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
		'" . intval($ilance->GPC['sort']) . "',
		'" . intval($ilance->GPC['visible']) . "',
		'" . intval($ilance->GPC['required']) . "',
		'" . intval($ilance->GPC['canverify']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['verifycost']) . "',
		'" . intval($ilance->GPC['isfilter']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['filtertype']) . "',
		'" . intval($ilance->GPC['filtercategory']) . "',
		'" . intval($ilance->GPC['guests']) . "')
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE PROFILE QUESTION HANDLER ########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-profile-question' AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['question']) AND isset($ilance->GPC['description']))
{
	if (isset($ilance->GPC['profile_question_active']) AND $ilance->GPC['profile_question_active'])
	{
		$ilance->GPC['visible'] = '1';
	}
	else
	{
		$ilance->GPC['visible'] = '0';    
	}
	if (isset($ilance->GPC['canverify']) AND $ilance->GPC['canverify'])
	{
		$ilance->GPC['canverify'] = '1';
	}
	else
	{
		$ilance->GPC['canverify'] = '0';
	}		
	if (isset($ilance->GPC['verifycost']) AND $ilance->GPC['verifycost'] > 0)
	{
		$ilance->GPC['verifycost'] = sprintf("%01.2f", $ilance->GPC['verifycost'], 'credit');
	}
	else
	{
		$ilance->GPC['verifycost'] = '0.00';
	}		
	if (isset($ilance->GPC['required']) AND $ilance->GPC['required'])
	{
		$ilance->GPC['required'] = '1';
	}
	else
	{
		$ilance->GPC['required'] = '0';
	}			
	if (!isset($ilance->GPC['multiplechoice']))
	{
		$ilance->GPC['multiplechoice'] = '';   
	}
	if (isset($ilance->GPC['isfilter']) AND $ilance->GPC['isfilter'])
	{
		$ilance->GPC['isfilter'] = '1';
	}
	else
	{
		$ilance->GPC['isfilter'] = '0';
	}
	if (isset($ilance->GPC['guests']) AND $ilance->GPC['guests'])
	{
		$ilance->GPC['guests'] = '1';
	}
	else
	{
		$ilance->GPC['guests'] = '0';
	}
	if (!isset($ilance->GPC['filtertype']))
	{
		$ilance->GPC['filtertype'] = '';   
	}
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_questions
		SET groupid = '" . intval($ilance->GPC['groupid']) . "',
		question = '" . $ilance->db->escape_string($ilance->GPC['question']) . "',
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
		inputtype = '" . $ilance->db->escape_string($ilance->GPC['inputtype']) . "',
		multiplechoice = '" . $ilance->db->escape_string($ilance->GPC['multiplechoice']) . "',
		sort = '" . intval($ilance->GPC['sort']) . "',
		visible = '" . intval($ilance->GPC['visible']) . "',
		required = '" . intval($ilance->GPC['required']) . "',
		canverify = '" . intval($ilance->GPC['canverify']) . "',
		verifycost = '" . $ilance->db->escape_string($ilance->GPC['verifycost']) . "',
		isfilter = '" . intval($ilance->GPC['isfilter']) . "',
		filtertype = '" . $ilance->db->escape_string($ilance->GPC['filtertype']) . "',
		filtercategory = '" . $ilance->db->escape_string($ilance->GPC['filtercategory']) . "',
		guests = '". intval($ilance->GPC['guests']) ."'
		WHERE questionid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### EDIT PROFILE QUESTIONS #################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question' AND $ilance->GPC['groupid'] > 0 AND $ilance->GPC['id'] > 0)
{
	$id = intval($ilance->GPC['id']);
	$groupid = intval($ilance->GPC['groupid']);
	$question_id_hidden = '<input type="hidden" name="id" value="' . $id . '" />';
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_questions
		WHERE groupid = '" . $groupid . "'
		    AND questionid = '" . $id . "'
		LIMIT 1
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		$res = $ilance->db->fetch_array($sql);
		$question = stripslashes($res['question']);
		$question_description = stripslashes($res['description']);
		$inputtype = $res['inputtype'];
		$sort = $res['sort'];
		$visible = $res['visible'];
		$required = $res['required'];
		$canverify = $res['canverify'];
		$multiplechoiceprofile = $res['multiplechoice'];
		$guests = $res['guests'];
		$isfilter = $res['isfilter'];
		$filtertype = $res['filtertype'];
		$filtercategory = intval($res['filtercategory']);
	}
	if ($sort == '0')
	{
		$sort = '10';
	}
	else
	{
		$sort = $res['sort'];
	}
	$checked_profile_question_active = '';
	if ($visible)
	{
		$checked_profile_question_active = 'checked="checked"';
	}
	$regchecked_guests = '';
	if ($guests)
	{
		$regchecked_guests = 'checked="checked"';
	}
	$checked_profile_question_canverify = '';
	$verifycost = "0.00";	
	if ($canverify)
	{
		$checked_profile_question_canverify = 'checked="checked"';
		$verifycost = number_format($res['verifycost'], 2);
	}
	$checked_profile_question_required = '';	
	if ($required)
	{
		$checked_profile_question_required = 'checked="checked"';
	}
	$checked_profile_question_isfilter = '';
	if ($isfilter)
	{
		$checked_profile_question_isfilter = 'checked="checked"';
	}
	$filter_type_pulldown = $ilance->admincp->print_profile_filtertype_pulldown($filtertype);
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
	$filter_category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($filtercategory, $cattype = 'service', $type = 'level', $fieldname = 'filtercategory', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
	$submit_profile_question = '<input type="submit" name="update" value="{_save}" style="font-size:15px" class="buttons" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
}	
// #### UPDATE PROFILE QUESTIONS SORTING #######################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-questions-sort' AND $ilance->GPC['groupid'] > 0)
{
	foreach ($ilance->GPC['sort'] AS $key => $value)
	{
		if (!empty($key) AND !empty($value))
		{
			$ilance->db->query("
				UPDATE " . DB_PREFIX . "profile_questions
				SET sort = '" . $ilance->db->escape_string($value) . "'
				WHERE questionid = '" . $ilance->db->escape_string($key) . "'
				    AND groupid = '" . intval($ilance->GPC['groupid']) . "'
				LIMIT 1
			");    
		}
	}
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE PROFILE GROUP ###################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-profile-group' AND isset($ilance->GPC['name']) AND isset($ilance->GPC['description']) AND $ilance->GPC['groupid'] > 0)
{
	$visible = (isset($ilance->GPC['profile_group_active']) AND $ilance->GPC['profile_group_active']) ? '1' : '0';	    
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "profile_groups
		SET name = '" . $ilance->db->escape_string($ilance->GPC['name']) . "',
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "',
		visible = '" . intval($visible) . "',
		cid = '" . intval($ilance->GPC['cid']) . "'
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### REMOVE PROFILE GROUP (REMOVES QUESTIONS ALSO) ##########
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-profile-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$notice = '';
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "profile_groups
		WHERE groupid = '".intval($ilance->GPC['groupid'])."'
	");
	
	$notice = '{_the_action_requested_was_completed_successfully}';
    
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_questions
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
	");
	if ($ilance->db->num_rows($sql) > 0)
	{
		while ($res = $ilance->db->fetch_array($sql))
		{
			// remove answers to questions from within this group
			$ilance->db->query("
				DELETE FROM " . DB_PREFIX . "profile_answers
				WHERE questionid = '" . $res['questionid'] . "'
				LIMIT 1
			");
		}
	}
	
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "profile_questions
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
	");			
	
	print_action_success($notice, $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE PROFILE QUESTIONS ###############################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-profile-question' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "profile_questions
		WHERE groupid = '".intval($ilance->GPC['groupid'])."'
		    AND questionid = '".intval($ilance->GPC['id'])."'
		LIMIT 1
	");
	
	// remove all profile answers for this profile question we're deleting
	$ilance->db->query("
		DELETE FROM " . DB_PREFIX . "profile_answers
		WHERE questionid = '" . intval($ilance->GPC['id']) . "'
	");
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### ADD NEW PROFILE GROUP ##################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-profile-group' AND isset($ilance->GPC['name']) AND isset($ilance->GPC['description']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$visible = '0';
	if (isset($ilance->GPC['profile_group_active']) AND $ilance->GPC['profile_group_active'])
	{
		$visible = '1';
	}
	
	$ilance->db->query("
		INSERT INTO " . DB_PREFIX . "profile_groups
		(groupid, name, description, visible, cid)
		VALUES(
		NULL,
		'" . $ilance->db->escape_string($ilance->GPC['name']) . "',
		'" . $ilance->db->escape_string($ilance->GPC['description']) . "',
		'" . $visible . "',
		'" . intval($ilance->GPC['cid']) . "')
	");
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### UPDATE INSERTION GROUP HANDLER #########################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$oldname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "insertion_groups
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "insertion_fees
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
		    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE insertiongroup = '" . $ilance->db->escape_string($oldname) . "'
		    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// does admin create final value group?
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-fv-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->insert_fv_group($ilance->GPC['groupname'], $ilance->GPC['state'], $ilance->GPC['description']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// does admin update final value group?
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
{
	$oldname = $ilance->db->fetch_field(DB_PREFIX . "finalvalue_groups", "groupid = '".intval($ilance->GPC['groupid'])."'", "groupname");
	    
	// update group table
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "finalvalue_groups
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	
	// update fees table
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "finalvalue
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
		    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	
	// update categories table
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET finalvaluegroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE finalvaluegroup = '" . $ilance->db->escape_string($oldname) . "'
		    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// does admin request updating of a final value group?
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-fv-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['state']))
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "finalvalue_groups
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql);
	
	$subcmdfv = '_update-fv-group';
	$hiddenfvgroupid = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
	$groupnamefv = stripslashes($res['groupname']);
	$groupnamefv2 = $groupnamefv;
	$descriptionfv = stripslashes($res['description']);
	$descriptionfv2 = $descriptionfv;
	$submitfv = '<input type="submit" name="submit" value="{_save}" class="buttons" style="font-size:15px" />&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
}
else 
{
	$hiddenfvgroupid = $groupnamefv = $descriptionfv = '';
	$subcmdfv = '_create-fv-group';
	$submitfv = '<input type="submit" name="save" value="{_save}" class="buttons" style="font-size:15px" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')" />';
	$groupnamefv2 = $groupnamefv;
	$descriptionfv2 = $descriptionfv;
}
// does admin create insertion group?
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_create-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->insert_insertion_group($ilance->GPC['groupname'], $ilance->GPC['state'], $ilance->GPC['description']);
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// #### CALLED WHEN ADMIN CLICKS EDIT INSERTION GROUP PENCIL ICON ######
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-insertion-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['state']))
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "insertion_groups
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		ORDER BY groupname ASC
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$subcmdinsertion = '_update-insertion-group';
	$hiddengroupid = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
	$groupname = stripslashes($res['groupname']);
	$groupname2 = $groupname;
	$description = stripslashes($res['description']);
	$description2 = $description;
	$insertiongroupdescription = stripslashes($res['description']);
	$insertiongroupdescription2 = $insertiongroupdescription;
	$submitinsertion = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
}
else 
{
	$hiddengroupid = $groupname = $description = $insertiongroupdescription = '';
	$subcmdinsertion = '_create-insertion-group';
	$submitinsertion = '<input type="submit" style="font-size:15px" value="{_save}" class="buttons" />';
	$groupname2 = $groupname;
	$description2 = $description;
	$insertiongroupdescription2 = $insertiongroupdescription;
}
// #### UPDATE INSERTION GROUP HANDLER #################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['state']) AND isset($ilance->GPC['groupid']))
{
	$oldname = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupid = '" . intval($ilance->GPC['groupid']) . "'", "groupname");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "insertion_groups
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "',
		description = '" . $ilance->db->escape_string($ilance->GPC['description']) . "'
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "insertion_fees
		SET groupname = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE groupname = '" . $ilance->db->escape_string($oldname) . "'
		    AND state = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	$ilance->db->query("
		UPDATE " . DB_PREFIX . "categories
		SET insertiongroup = '" . $ilance->db->escape_string($ilance->GPC['groupname']) . "'
		WHERE insertiongroup = '" . $ilance->db->escape_string($oldname) . "'
		    AND cattype = '" . $ilance->db->escape_string($ilance->GPC['state']) . "'
	");
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilance->GPC['return']);
	exit();
}
// admin requestion edit/update mode?
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$name = $description = $checked_profile_group_active = '';
	$groupid = intval($ilance->GPC['groupid']);
	$hiddengroupid = '<input type="hidden" name="groupid" value="' . $groupid . '">';
	$submit = '<input type="submit" name="update" value="{_save}" style="font-size:15px" class="buttons" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')">&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
	$subcmd = '_update-profile-group';
				
	$sqlupdate = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "profile_groups
		WHERE groupid = '" . $groupid . "'
	");
	if ($ilance->db->num_rows($sqlupdate) > 0)
	{
		$resupdate = $ilance->db->fetch_array($sqlupdate);
		$name = stripslashes($resupdate['name']);
		$description = stripslashes($resupdate['description']);
		$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
		$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown($resupdate['cid'], $cattype = 'service', $type = 'level', $fieldname = 'cid', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
		$checked_profile_group_active = '';
		if ($resupdate['visible'])
		{
			$checked_profile_group_active = 'checked="checked"';
		}
	}
}
else
{
	$name = $description = '';
	$subcmd = '_create-profile-group';
	$submit = '<input type="submit" name="save" value="{_save}" style="font-size:15px" class="buttons" />';
	$checked_profile_group_active = 'checked';
}

if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
{
	$question_subcmd = '_update-profile-question';
}
else
{
	$question_subcmd = '_add-profile-question';
	$submit_profile_question = '<input type="submit" name="update" value="{_save}" style="font-size:15px" class="buttons" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')" />&nbsp;';
	
	$filter_type_pulldown = $ilance->admincp->print_profile_filtertype_pulldown('');
	$ilance->categories->build_array($cattype = 'service', $_SESSION['ilancedata']['user']['slng'], $mode = 0, $propersort = true);
	$filter_category_pulldown = $ilance->categories_pulldown->print_cat_pulldown(0, $cattype = 'service', $type = 'level', $fieldname = 'filtercategory', $showpleaseselectoption = 0, $_SESSION['ilancedata']['user']['slng'], $nooptgroups = 1, $prepopulate = '', $mode = 0, $showallcats = 1, $dojs = 0, $width = '540px', $uid = 0, $forcenocount = 1, $expertspulldown = 0, $canassigntoall = true, $showbestmatching = false, $ilance->categories->cats);
}

// requesting normal mode or edit/update mode?
$profile_inputtype_pulldown = '<select name="inputtype" class="select-250">';
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
{
	$profile_inputtype_pulldown .= '<option value="yesno"'; if ($res['inputtype'] == "yesno") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_radio_selection_box_yes_or_no_type_question}</option>';
	$profile_inputtype_pulldown .= '<option value="int"'; if ($res['inputtype'] == "int") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_integer_field_numbers_only}</option>';
	$profile_inputtype_pulldown .= '<option value="textarea"'; if ($res['inputtype'] == "textarea") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_textarea_field_multiline}</option>';
	$profile_inputtype_pulldown .= '<option value="text"'; if ($res['inputtype'] == "text") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_input_text_field_singleline}</option>';
	$profile_inputtype_pulldown .= '<option value="multiplechoice"'; if ($res['inputtype'] == "multiplechoice") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_multiple_choice_enter_values_below}</option>';
	$profile_inputtype_pulldown .= '<option value="pulldown"'; if ($res['inputtype'] == "pulldown") { $profile_inputtype_pulldown .= ' selected="selected"'; } $profile_inputtype_pulldown .= '>{_pulldown_menu_enter_values_below}</option>';
}
else
{
	$profile_inputtype_pulldown .= '<option value="yesno">{_radio_selection_box_yes_or_no_type_question}</option>';
	$profile_inputtype_pulldown .= '<option value="int">{_integer_field_numbers_only}</option>';
	$profile_inputtype_pulldown .= '<option value="textarea">{_textarea_field_multiline}</option>';
	$profile_inputtype_pulldown .= '<option value="text">{_input_text_field_singleline}</option>';
	$profile_inputtype_pulldown .= '<option value="multiplechoice">{_multiple_choice_enter_values_below}</option>';
	$profile_inputtype_pulldown .= '<option value="pulldown">{_pulldown_menu_enter_values_below}</option>';
}
$profile_inputtype_pulldown .= '</select>';
	
// #### UPDATE FINAL VALUE FEE HANDLER #############################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-fv-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['tierid']) AND $ilance->GPC['tierid'] > 0)
{
	$ilance->admincp->update_fv_fee($ilance->GPC['finalvalue_from'], $ilance->GPC['finalvalue_to'], $ilance->GPC['amountfixed'], $ilance->GPC['amountpercent'], $ilance->GPC['groupid'], $ilance->GPC['tierid'], $ilance->GPC['sort']);
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### UPDATE INSERTION FEE HANDLER ###################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-insertion-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['insertionid']) AND $ilance->GPC['insertionid'] > 0)
{
	$ilance->admincp->update_insertion_fee($ilance->GPC['insertion_from'], $ilance->GPC['insertion_to'], $ilance->GPC['amount'], $ilance->GPC['groupid'], $ilance->GPC['insertionid'], $ilance->GPC['sort']);
	
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### CALLED WHEN ADMIN CLICKS PENCIL ICON TO EDIT INSERTION FEE RANGE
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-insertion-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['state']))
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "insertion_fees
		WHERE insertionid = '" . intval($ilance->GPC['id']) . "'
		ORDER BY sort ASC
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$insamount = $res['amount'];
	$insfrom = $res['insertion_from'];
	$insto = $res['insertion_to'];
	$inssort = $res['sort'];
	$insform = '_update-insertion-fee';
	$inshidden = '<input type="hidden" name="insertionid" value="' . $res['insertionid'] . '" />';
	$inssubmit = '<input type="submit" style="font-size:15px" value="{_save}" class="buttons" /> &nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
	// service
	$insamount2 = $insamount;
	$insfrom2 = $insfrom;
	$insto2 = $insto;
	$insform2 = $insform;
	$inshidden2 = $inshidden;
	$inssubmit2 = $inssubmit;
	$inssort2 = $inssort;
}
else 
{
	// product
	$insamount = '0.00';
	$insfrom = '0.00';
	$insto = '0.00';
	$insform = 'insert-insertion';
	$inssort = '0';
	$inshidden = '';
	$inssubmit = '<input type="submit" value="{_save}" class="buttons" style="font-size:15px" />';
	// service
	$insamount2 = $insamount;
	$insfrom2 = $insfrom;
	$insto2 = $insto;
	$insform2 = $insform;
	$inshidden2 = $inshidden;
	$inssubmit2 = $inssubmit;
	$inssort2 = $inssort;
}
    
// #### UPDATE BUDGET FEE RANGE HANDLER ################################
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-range' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['title']) AND !empty($ilance->GPC['title']) AND isset($ilance->GPC['budgetfrom']) AND isset($ilance->GPC['budgetto']) AND isset($ilance->GPC['budgetid']))
{
	$sort = isset($ilance->GPC['sort']) ? intval($ilance->GPC['sort']) : 0;
	$ilance->admincp->update_budget_range($ilance->GPC['budgetid'], $ilance->GPC['title'], $ilance->GPC['budgetfrom'], $ilance->GPC['budgetto'], $ilance->GPC['groupid'], $ilance->GPC['insertiongroupid'], $sort);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### INSERT BUDGET FEE GROUP HANDLER ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == 'insert-budget-group' AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->insert_budget_group($ilance->GPC['groupname'], $ilance->GPC['description']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE BUDGET FEE GROUP HANDLER ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-budget-group' AND isset($ilance->GPC['groupid']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->admincp->remove_budget_group($ilance->GPC['groupid']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### REMOVE BUDGET FEE RANGE HANDLER ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_remove-budget' AND isset($ilance->GPC['id']))
{
	if ($show['ADMINCP_TEST_MODE'])
	{
		print_action_failed('{_demo_mode_only}', $ilpage['components']);
		exit();
	}
	$ilance->admincp->remove_budget_range($ilance->GPC['id']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### UPDATE BUDGET FEE GROUP HANDLER ################################
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_update-budget-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['groupname']) AND isset($ilance->GPC['description']))
{
	$ilance->admincp->update_budget_group($ilance->GPC['groupid'], $ilance->GPC['groupname'], $ilance->GPC['description']);
	print_action_success('{_the_action_requested_was_completed_successfully}', $ilpage['settings'] . '?cmd=marketplace');
	exit();
}
// #### CALLED WHEN ADMIN CLICKS EDIT BUDGET FEE GROUP PENCIL ICON #####
else if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-budget-group' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0)
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "budget_groups
		WHERE groupid = '" . intval($ilance->GPC['groupid']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$budgetgroupname = $res['groupname'];
	$budgetgroupdescription = $res['description'];
	$submitbudget = '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
	$subcmdbudgetgroup = '_update-budget-group';
	$hiddenbudgetgroupid2 = '<input type="hidden" name="groupid" value="' . $res['groupid'] . '" />';
}
else 
{
	$budgetgroupname = $budgetgroupdescription = $hiddenbudgetgroupid2 = '';
	$submitbudget = '<input type="submit" value="{_save}" style="font-size:15px" class="buttons" />';
	$subcmdbudgetgroup = 'insert-budget-group';
}

// #### CALLED WHEN ADMIN CLICKS EDIT BUDGET FEE RANGE PENCIL ICON #####
if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-budget' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0)
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "budget
		WHERE budgetid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	$ilance->GPC['insertiongroupid'] = $ilance->db->fetch_field(DB_PREFIX . "insertion_groups", "groupname = '" . $ilance->db->escape_string($res['insertiongroup']) . "'", "groupid");
	$title = $res['title'];
	$budgetfieldname = $res['fieldname'];
	$budgetfrom = $res['budgetfrom'];
	if ($res['budgetto'] == '-1.00')
	{
		$res['budgetto'] = '-1';
	}
	$budgetto = $res['budgetto'];
	$budgetsort = $res['sort'];
	$budgetsubmit = '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
	$hiddenbudgetgroupid = '<input type="hidden" name="budgetid" value="' . $res['budgetid'] . '" />';
	$budgetsubcmd = '_update-budget-range';
	$budgethidden = '<input type="hidden" name="budgetid" value="' . $res['budgetid'] . '" />';
}
else 
{
	$title = $budgetfieldname = $budgetfrom = $budgetto = $budgethidden = $hiddenbudgetgroupid = '';
	$budgetsort = 0;
	$budgetsubmit = '<input type="submit" value=" {_save} " style="font-size:15px" class="buttons" />';
	$budgetsubcmd = 'insert-budget-range';
}

if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-fv-fee' AND isset($ilance->GPC['groupid']) AND $ilance->GPC['groupid'] > 0 AND isset($ilance->GPC['id']) AND $ilance->GPC['id'] > 0 AND isset($ilance->GPC['state']))
{
	$sql = $ilance->db->query("
		SELECT *
		FROM " . DB_PREFIX . "finalvalue
		WHERE tierid = '" . intval($ilance->GPC['id']) . "'
		LIMIT 1
	");
	$res = $ilance->db->fetch_array($sql, DB_ASSOC);
	// product
	$fvamountfixed = $res['amountfixed'];
	$fvamountpercent = $res['amountpercent'];
	$fvfrom = $res['finalvalue_from'];
	$fvto = $res['finalvalue_to'];
	$fvsort = $res['sort'];
	$fvsubcmd = '_update-fv-fee';
	$fvhidden = '<input type="hidden" name="tierid" value="' . $res['tierid'] . '" />';
	$fvsubmit = '<input type="submit" name="submit" value=" {_save} " class="buttons" style="font-size:15px" />&nbsp;&nbsp;&nbsp;&nbsp;<span class="blue"><a href="' . $ilpage['settings'] . '?cmd=marketplace">{_cancel}</a></span>';
	// service
	$fvamountfixed2 = $fvamountfixed;
	$fvamountpercent2 = $fvamountpercent;
	$fvfrom2 = $fvfrom;
	$fvto2 = $fvto;
	$fvsort2 = $fvsort;
	$fvsubcmd2 = $fvsubcmd;
	$fvhidden2 = $fvhidden;
	$fvsubmit2 = $fvsubmit;
}
else 
{
	// product
	$fvamountfixed = '0';
	$fvamountpercent = '0.0';
	$fvfrom = $fvto = $fvhidden = '';
	$fvsort = '10';
	$fvsubcmd = 'insert-fv';
	$fvsubmit = '<input type="submit" value=" {_save} " class="buttons" style="font-size:15px" />';
	// service
	$fvamountfixed2 = $fvamountfixed;
	$fvamountpercent2 = $fvamountpercent;
	$fvfrom2 = $fvfrom;
	$fvto2 = $fvto;
	$fvsort2 = $fvsort;
	$fvsubcmd2 = $fvsubcmd;
	$fvhidden2 = $fvhidden;
	$fvsubmit2 = $fvsubmit;
}

// #### SETTINGS MENU ##################################################
$sqlprofilegroupz = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "profile_groups
	ORDER BY name ASC
");
if ($ilance->db->num_rows($sqlprofilegroupz) > 0)
{
	$row_count = 0;
	while ($rowz = $ilance->db->fetch_array($sqlprofilegroupz, DB_ASSOC))
	{
		// question count
		$qcount = $ilance->db->query("
			SELECT COUNT(*) AS questions 
			FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '" . $rowz['groupid'] . "'
		");
		if ($ilance->db->num_rows($qcount) > 0)
		{
			$rescount = $ilance->db->fetch_array($qcount, DB_ASSOC);
			$rowz['questions'] = $rescount['questions'];
		}
		else
		{
			$rowz['questions'] = '0';
		}
		if ($rowz['canremove'] == 0)
		{
			$rowz['category'] = '{_all_categories}';
			$rowz['remove_group'] = '-';
			$rowz['edit'] = '-';
		}
		else
		{
			$rowz['category'] = ($rowz['cid'] <= 0) ? '{_all_categories}' : $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $rowz['cid']);
			$rowz['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-profile-group&amp;groupid=' . $rowz['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
			$rowz['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-profile-group&amp;groupid=' . $rowz['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		}
		$rowz['active'] = ($rowz['visible']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		$rowz['groupname'] = handle_input_keywords(stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid = '".$rowz['groupid']."'", "name")));
		$rowz['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$profile_groups[] = $rowz;
		$row_count++;
	}
}
else
{
	$show['no_profile_groups'] = true;
}
    
$sqlprofilegroups = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "profile_groups
	ORDER BY name ASC
");
if ($ilance->db->num_rows($sqlprofilegroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlprofilegroups, DB_ASSOC))
	{
		$qcount = $ilance->db->query("
			SELECT COUNT(*) AS questions
			FROM " . DB_PREFIX . "profile_questions
			WHERE groupid = '" . $row['groupid'] . "'
		");
		if ($ilance->db->num_rows($qcount) > 0)
		{
			$rescount = $ilance->db->fetch_array($qcount);
			$row['questions'] = $rescount['questions'];
		}
		else
		{
			$row['questions'] = '0';
		}
		if ($row['visible'])
		{
			$row['active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
		}
		else
		{
			$row['active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
		}
		$row['category'] = ($row['cid'] <= 0) ? '{_all_categories}' : $ilance->categories->title($_SESSION['ilancedata']['user']['slng'], $row['cid']);
		$sqlquestions = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "profile_questions 
			WHERE groupid = '" . $row['groupid'] . "'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlquestions) > 0)
		{
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlquestions, DB_ASSOC))
			{
				$rows['question_description'] = stripslashes($rows['description']);
				$rows['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-profile-question&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['questionid'] . '#profilequestion"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['remove'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-profile-question&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['questionid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				if ($rows['visible'])
				{
					$rows['question_active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
				}
				else
				{
					$rows['question_active'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				}
				if ($rows['required'])
				{
					$rows['isrequired'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
				}
				else
				{
					$rows['isrequired'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				}
				if ($rows['canverify'])
				{
					$rows['canverify'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';
					$rows['cost'] = $ilance->currency->format($rows['verifycost']);
				}
				else
				{
					$rows['canverify'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
					$rows['cost'] = '-';
				}
				if ($rows['isfilter'])
				{
					$rows['isfilter'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />';	
				}
				else
				{
					$rows['isfilter'] = '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				}
				$rows['guests'] = ($rows['guests']) ? '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'checked.gif" border="0" alt="" />' : '<img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'unchecked.gif" border="0" alt="" />';
				$rows['inputtype'] = mb_strtolower($rows['inputtype']);
				$rows['sortinput'] = '<input type="text" name="sort[' . $rows['questionid'] . ']" value="' . $rows['sort'] . '" style="text-align:center" class="input" size="3" />';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$GLOBALS['profile_questions' . $row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS['no_profile_questions' . $row['groupid']][] = 1;	
		}
		$row['groupname'] = stripslashes($ilance->db->fetch_field(DB_PREFIX . "profile_groups", "groupid=" . $row['groupid'], "name"));
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$profile_question_groups[] = $row;
		$row_count++;
	}
}
	
// fetch product insertion fee groups
$sqlinsertionproductgroups = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "insertion_groups
	WHERE state = 'product'
	ORDER BY groupname ASC
");
if ($ilance->db->num_rows($sqlinsertionproductgroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlinsertionproductgroups, DB_ASSOC))
	{
		// fetch insertion fees in this group
		$sqlproductfees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "insertion_fees
			WHERE groupname = '" . $row['groupname'] . "'
				AND state = 'product'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlproductfees) > 0)
		{		
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlproductfees, DB_ASSOC))
			{
				$rows['from'] = $ilance->currency->format($rows['insertion_from']);
				if ($rows['insertion_to'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['insertion_to']);
				}
				else 
				{
					$rows['to'] = '{_or_more}';
				}                                
				$rows['amount'] = $ilance->currency->format($rows['amount']);
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '&amp;state=product#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$GLOBALS["productinsertionfees".$row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS["no_productinsertionfees".$row['groupid']][] = 1;	
		}
		$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
		$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
		$row['groupcount'] = $ilance->admincp->fetch_insertion_catcount('product', $row['groupname']);
		$row['groupcount2'] = $ilance->admincp->fetch_insertion_permcount('product', $row['groupid']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$productinsertion_groups[] = $row;
		$productinsertion1_groups[] = $row;
		$row_count++;
	}
}
else
{
	$show['no_productinsertion1_groups'] = true;
}
	
// #### SERVICE INSERTION GROUPS #######################################
// fetch service insertion fee groups
$sqlinsertionservicegroups = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "insertion_groups
	WHERE state = 'service'
	ORDER BY groupname ASC
");
if ($ilance->db->num_rows($sqlinsertionservicegroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlinsertionservicegroups, DB_ASSOC))
	{
		// fetch insertion fees in this group
		$sqlservicefees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "insertion_fees
			WHERE groupname = '" . $row['groupname'] . "'
				AND state = 'service'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlservicefees) > 0)
		{
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlservicefees, DB_ASSOC))
			{
				$rows['from'] = $ilance->currency->format($rows['insertion_from']);
				if ($rows['insertion_to'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['insertion_to']);
				}
				else 
				{
					$rows['to'] = '{_or_more}';
				}
				$rows['amount'] = $ilance->currency->format($rows['amount']);
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '&amp;state=service#question"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-fee&amp;groupid=' . $row['groupid'] . '&amp;id=' . $rows['insertionid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$GLOBALS["serviceinsertionfees".$row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS["no_serviceinsertionfees".$row['groupid']][] = 1;	
		}                                
		$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-insertion-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt=""></a>';
		$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt=""></a>';
		$row['groupcount'] = $ilance->admincp->fetch_insertion_catcount('service', $row['groupname']);
		$row['groupcount2'] = $ilance->admincp->fetch_insertion_permcount('service', $row['groupid']);
		$row['budgetgroupcount'] = $ilance->admincp->fetch_insertion_budget_catcount($row['groupname']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-insertion-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$serviceinsertion_groups[] = $row;
		$serviceinsertion1_groups[] = $row;
		$row_count++;
	}
}
else
{
	$show['no_serviceinsertion1_groups'] = true;
}
    
// #### PRODUCT FINAL VALUE GROUPS #####################################
// fetch product final value fee groups
$tier = 0;
$sqlfinalproductgroups = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "finalvalue_groups
	WHERE state = 'product'
");
if ($ilance->db->num_rows($sqlfinalproductgroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlfinalproductgroups, DB_ASSOC))
	{
		// fetch final value fees in this group
		$sqlproductfees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "finalvalue
			WHERE groupname = '" . $ilance->db->escape_string($row['groupname']) . "'
			    AND state = 'product'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlproductfees) > 0)
		{
			$tier = 0;
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlproductfees, DB_ASSOC))
			{
				$tier++;
				$rows['from'] = $ilance->currency->format($rows['finalvalue_from']);
				if ($rows['finalvalue_to'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['finalvalue_to']);
				}
				else 
				{
					$rows['to'] = '{_or_more}';
				}
				if ($rows['amountfixed'] > 0)
				{
					$rows['amountfixed'] = $ilance->currency->format($rows['amountfixed']);
				}
				else
				{
					$rows['amountfixed'] = '-';
				}
				if ($rows['amountpercent'] > 0)
				{
					$rows['amountpercent'] = $rows['amountpercent'] . '%';
				}
				else
				{
					$rows['amountpercent'] = '-';
				}
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'&amp;state=product#productfvf"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$rows['tier'] = $tier;
				$GLOBALS['productfvfees' . $row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS['no_productfvfees' . $row['groupid']][] = 1;	
		}
		$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$row['groupcount'] = $ilance->admincp->fetch_fv_catcount('product', $row['groupname']);
		$row['groupcount2'] = $ilance->admincp->fetch_fv_permcount('product', $row['groupid']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=product#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$productfv_groups[] = $row;
		$productfv1_groups[] = $row;
		$row_count++;
	}
}
else
{
	$show['no_productfv_groups'] = true;
}
	
// #### SERVICE FINAL VALUE GROUPS #####################################
// fetch service final value fee groups
$tier = 0;
$sqlfinalservicegroups = $ilance->db->query("
	SELECT *
	FROM " . DB_PREFIX . "finalvalue_groups
	WHERE state = 'service'
");
if ($ilance->db->num_rows($sqlfinalservicegroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlfinalservicegroups, DB_ASSOC))
	{
		// fetch final value fees in this group
		$sqlservicefees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "finalvalue
			WHERE groupname = '" . $ilance->db->escape_string($row['groupname']) . "'
				AND state = 'service'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlservicefees) > 0)
		{
			$tier = 0;
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlservicefees, DB_ASSOC))
			{
				$tier++;
				$rows['from'] = $ilance->currency->format($rows['finalvalue_from']);
				if ($rows['finalvalue_to'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['finalvalue_to']);
				}
				else 
				{
					$rows['to'] = '{_or_more}';
				}
				if ($rows['amountfixed'] > 0)
				{
					$rows['amountfixed'] = $ilance->currency->format($rows['amountfixed']);
				}
				else
				{
					$rows['amountfixed'] = '-';        
				}
				if ($rows['amountpercent'] > 0)
				{
					$rows['amountpercent'] = $rows['amountpercent'] . '%';
				}
				else
				{
					$rows['amountpercent'] = '-';        
				}
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'&amp;state=service#servicefvf"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-fee&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['tierid'].'" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$rows['tier'] = $tier;
				$GLOBALS['servicefvfees' . $row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS["no_servicefvfees".$row['groupid']][] = 1;	
		}
		$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-fv-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$row['groupcount'] = $ilance->admincp->fetch_fv_catcount('service', $row['groupname']);
		$row['groupcount2'] = $ilance->admincp->fetch_fv_permcount('service', $row['groupid']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-fv-group&amp;groupid=' . $row['groupid'] . '&amp;state=service#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$servicefv_groups[] = $row;
		$servicefv1_groups[] = $row;
		$row_count++;
	}
}
else
{
	$show['no_servicefv_groups'] = true;
	$no_servicefv1_groups = 1;
}
	
#####################################################
## SERVICE BUDGET GROUPS ############################
#####################################################
// fetch service insertion fee groups
$sqlbudgetgroups = $ilance->db->query("SELECT * FROM " . DB_PREFIX . "budget_groups ORDER BY groupname ASC");
if ($ilance->db->num_rows($sqlbudgetgroups) > 0)
{
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sqlbudgetgroups, DB_ASSOC))
	{
		// fetch budget values in this group
		$sqlfees = $ilance->db->query("
			SELECT *
			FROM " . DB_PREFIX . "budget
			WHERE budgetgroup = '" . $row['groupname' ]. "'
			ORDER BY sort ASC
		");
		if ($ilance->db->num_rows($sqlfees) > 0)
		{
			$row_count2 = 0;
			while ($rows = $ilance->db->fetch_array($sqlfees, DB_ASSOC))
			{
				$rows['from'] = $ilance->currency->format($rows['budgetfrom']);
				if ($rows['budgetto'] != '-1')
				{
					$rows['to'] = $ilance->currency->format($rows['budgetto']);
				}
				else 
				{
					$rows['to'] = '{_or_more}';
				}
				$rows['actions'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['budgetid'].'#editbudgetgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
				$rows['actions'] .= '&nbsp;&nbsp;&nbsp;<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-budget&amp;groupid=' . $row['groupid'] . '&amp;id='.$rows['budgetid'].'" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
				$rows['class2'] = ($row_count2 % 2) ? 'alt2' : 'alt1';
				$rows['insgroup'] = (isset($rows['insertiongroup']) AND !empty($rows['insertiongroup'])) ? $rows['insertiongroup'] : '-';
				$GLOBALS['budgetfees' . $row['groupid']][] = $rows;
				$row_count2++;
			}
		}
		else
		{
			$GLOBALS["no_budgetfees" . $row['groupid']][] = 1;
		}
		$row['remove_group'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_remove-budget-group&amp;groupid=' . $row['groupid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['edit'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget-group&amp;groupid=' . $row['groupid'] . '#editgroup"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/pencil.gif" border="0" alt="" /></a>';
		$row['groupcount'] = $ilance->admincp->fetch_budget_catcount($row['groupname']);
		$row['groupnameplain'] = $row['groupname'];
		$row['groupname'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=_edit-budget-group&amp;groupid=' . $row['groupid'] . '#editgroup">' . $row['groupname'] . '</a>';
		$row['class'] = ($row_count % 2) ? 'alt2' : 'alt1';
		$budget_groups[] = $row;
		$budget1_groups[] = $row;
		$row_count++;
	}
}
else
{
	$no_budget_groups = 1;
	$show['no_budget1_groups'] = true;
}

// create insertion group pulldown for budget range updating
$igroup = isset($ilance->GPC['insertiongroupid']) ? intval($ilance->GPC['insertiongroupid']) : '-1';
$insertiongrouppulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'service', 'insertiongroupid');
    
// profile groups
$sqlgroupquestions = $ilance->db->query("
	SELECT groupid, name, description, visible, canremove, cid
	FROM " . DB_PREFIX . "profile_groups
	ORDER BY name ASC
");
if ($ilance->db->num_rows($sqlgroupquestions) > 0)
{
	$profile_group_pulldown = '<select name="groupid" class="select-250">';
	while ($res = $ilance->db->fetch_array($sqlgroupquestions, DB_ASSOC))
	{
		if (isset($ilance->GPC['subcmd']) AND $ilance->GPC['subcmd'] == '_edit-profile-question')
		{
			$profile_group_pulldown .= '<option value="' . $res['groupid'] . '"';
			if ($res['groupid'] == $groupid)
			{
				$profile_group_pulldown .= ' selected="selected"';
			}
		}
		else
		{
			$profile_group_pulldown .= '<option value="' . $res['groupid'] . '"';
		}                            
		$profile_group_pulldown .= '>' . stripslashes($res['name']) . '</option>';
	}
	$profile_group_pulldown .= '</select>';
}
else
{
	$profile_group_pulldown = '{_no_results_found}';
}
    
// profile questions category pulldown
if (empty($category_pulldown))
{
	$ilance->categories->build_array('service', $_SESSION['ilancedata']['user']['slng'], 0, true);
	$category_pulldown = $ilance->categories_pulldown->print_cat_pulldown(0, 'service', 'level', 'cid', 0, $_SESSION['ilancedata']['user']['slng'], 1, '', 0, 1, 0, $width = '540px', 0, 1, 0, true, false, $ilance->categories->cats);
}

// #### SHIPPING PARTNERS ######################################
$show['no_shippers_rows'] = true;
$sql = $ilance->db->query("
	SELECT shipperid, title, shipcode, domestic, international, carrier, trackurl, sort
	FROM " . DB_PREFIX . "shippers
	ORDER BY sort ASC
");
if ($ilance->db->num_rows($sql) > 0)
{
	$show['no_shippers_rows'] = false;
	$row_count = 0;
	while ($row = $ilance->db->fetch_array($sql, DB_ASSOC))
	{
		$row['class'] = ($row['international']) ? 'alt1' : 'alt1';				
		$row['action'] = '<a href="' . $ilpage['settings'] . '?cmd=marketplace&amp;subcmd=remove-shipper&amp;id=' . $row['shipperid'] . '" onclick="return confirm_js(\'{_please_take_a_moment_to_confirm_your_action}\')"><img src="' . $ilconfig['template_relativeimagepath'] . $ilconfig['template_imagesfolder'] . 'icons/delete.gif" border="0" alt="" /></a>';
		$row['title'] = '<input type="text" name="title[' . $row['shipperid'] . ']" value="' . stripslashes(handle_input_keywords($row['title'])) . '" class="input" style="width:365px" />';
		$row['shipcode'] = '<input type="text" name="shipcode[' . $row['shipperid'] . ']" value="' . stripslashes(handle_input_keywords($row['shipcode'])) . '" class="input" style="width:100px; text-align:center" />';
		$row['domestic'] = '<input type="checkbox" name="domestic[' . $row['shipperid'] . ']" value="1" ' . ($row['domestic'] ? 'checked="checked"' : '') . ' />';
		$row['international'] = '<input type="checkbox" name="international[' . $row['shipperid'] . ']" value="1" ' . ($row['international'] ? 'checked="checked"' : '') . ' />';
		$row['carrier'] = '<input type="text" name="carrier[' . $row['shipperid'] . ']" value="' . stripslashes(handle_input_keywords($row['carrier'])) . '" class="input" style="width:40px" />';
		$row['trackurl'] = '<input type="text" name="trackurl[' . $row['shipperid'] . ']" value="' . stripslashes(handle_input_keywords($row['trackurl'])) . '" style="width:365px;background-color:#ebebeb;color:#555" class="input" />';
		$row['sort'] = '<input type="text" name="sort[' . $row['shipperid'] . ']" value="' . stripslashes(intval($row['sort'])) . '" class="input" style="width:30px;text-align:center" />';
		$shippers[] = $row;
		$row_count++;
	}
}

// #### insertion group pulldowns ##############################
$igroup = isset($ilance->GPC['groupid']) ? intval($ilance->GPC['groupid']) : '';
$insertiongroupservicepulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'service');
$insertiongroupproductpulldown = $ilance->admincp->print_insertion_group_pulldown($igroup, 1, 'product');

// #### final value group pulldowns ############################
$finalvaluegroupservicepulldown = $ilance->admincp->print_fv_group_pulldown($igroup, 1, 'service');
$finalvaluegroupproductpulldown = $ilance->admincp->print_fv_group_pulldown($igroup, 1, 'product');

// #### budget group pulldowns #################################
$budgetgrouppulldown = $ilance->admincp->print_budget_group_pulldown($igroup, 1);

// #### tabs ###################################################
$configuration_servicerating = $ilance->admincp->construct_admin_input('servicerating', $ilpage['settings'] . '?cmd=marketplace');
$configuration_servicebid = $ilance->admincp->construct_admin_input('servicebid', $ilpage['settings'] . '?cmd=marketplace');
$configuration_serviceupsell = $ilance->admincp->construct_admin_input('serviceupsell', $ilpage['settings'] . '?cmd=marketplace');
$configuration_productupsell = $ilance->admincp->construct_admin_input('productupsell', $ilpage['settings'] . '?cmd=marketplace');
$configuration_productaward = $ilance->admincp->construct_admin_input('productaward', $ilpage['settings'] . '?cmd=marketplace');
$configuration_portfoliodisplay = $ilance->admincp->construct_admin_input('portfoliodisplay', $ilpage['settings'] . '?cmd=marketplace');
$configuration_portfolioupsell = $ilance->admincp->construct_admin_input('portfolioupsell', $ilpage['settings'] . '?cmd=marketplace');
$configuration_shippingsettings = $ilance->admincp->construct_admin_input('shippingsettings', $ilpage['settings'] . '?cmd=marketplace');
$configuration_shippingapiservices = $ilance->admincp->construct_admin_input('shippingapiservices', $ilpage['settings'] . '?cmd=marketplace');
$configuration_productblocks = $ilance->admincp->construct_admin_input('productblocks', $ilpage['settings'] . '?cmd=marketplace');

$pprint_array = array('configuration_shippingapiservices','configuration_productblocks','configuration_shippingsettings','regchecked_guests','filter_type_pulldown','checked_profile_question_isfilter','filtertype','filter_category_pulldown','multiplechoiceprofile','configuration_servicebid','checked_question_cansearch','multiplechoice','insertiongrouppulldown','hiddenbudgetgroupid','hiddenbudgetgroupid2','subcmdbudgetgroup','submitbudget','budgetgroupdescription','budgetgroupname','budgetsubcmd','budgethidden','subcmdbudget','subcmdbudgetgroup','budgetsort','inssort','inssort2','insertiongroupdescription','insertiongroupdescription2','fvsort','fvsort2','hiddenbudgetgroupid','budgetsubmit','title','fieldname','budgetfrom','budgetto','budgetgrouppulldown','descriptionfv','descriptionfv2','fvsubcmd','fvhidden','fvfrom','fvto','fvamountfixed','fvamountpercent','finalvaluegroupproductpulldown','finalvaluegroupservicepulldown','fvsubmit','subcmdfv','hiddenfvgroupid','groupnamefv','groupnamefv2','submitfv','groupname2','description','description2','inssubmit2','inshidden2','insform2','insto2','insamount2','insfrom2','subcmdinsertion','submitinsertion','insertiongroupservicepulldown','insertiongroupproductpulldown','groupname','inssubmit','inshidden','insfrom','insto','insamount','insform','insertionid','taxsubmit','tax_subcmd','tax_id_hidden','invoicetypetax','state','city','amount','taxlabel','countryname','regformname','regformdefault','regprofile_inputtype_pulldown','regsubmit_profile_question','regchecked_guests','regchecked_public','regchecked_required','regchecked_visible','regsort','register_page_pulldown','category_pulldown','checked_profile_question_required','subcatname','catname','service_subcategories','product_categories','configuration_invoicesystem','configuration_escrowsystem','configuration_attachmentsettings','configuration_portfoliodisplay','configuration_portfolioupsell','configuration_productaward','configuration_productupsell','configuration_serviceupsell','configuration_servicerating','checked_spell_check_enabled_true','checked_spell_check_enabled_false','service_fee_highlite','service_fee_highlite_color','checked_service_fee_highlite_active_true','checked_service_fee_highlite_active_false','service_spell_check_pulldown','service_quality','service_delivery','service_price','product_quality','product_price','verifycost','question_id_hidden','question_subcmd','question_description','submit_profile_question','checked_profile_question_canverify','canverify','checked_profile_question_active','question','sort','hiddengroupid','checked_profile_group_active','profile_inputtype_pulldown','profile_group_pulldown','subcmd','id','submit','description','name','checked_profile_group_active');

($apihook = $ilance->api('admincp_marketplace_end')) ? eval($apihook) : false;

$ilance->template->fetch('main', 'marketplace.html', 1);
$ilance->template->parse_hash('main', array('ilpage' => $ilpage));
$ilance->template->parse_loop('main', array('v3nav', 'subnav_settings'), false);
$ilance->template->parse_loop('main', 'register_questions');
$ilance->template->parse_loop('main', 'increments');
$ilance->template->parse_loop('main', 'profile_groups');
$ilance->template->parse_loop('main', 'verify_questions');
$ilance->template->parse_loop('main', 'registerlanguages');
$ilance->template->parse_loop('main', 'profile_question_groups');
if (!isset($profile_question_groups))
{
	$profile_question_groups = array();
}		
@reset($profile_question_groups);
while ($i = @each($profile_question_groups))
{
	$ilance->template->parse_loop('main', 'profile_questions' . $i['value']['groupid']);
}

// #### product insertion fees / groups ########################
$ilance->template->parse_loop('main', 'productinsertion1_groups');
$ilance->template->parse_loop('main', 'productinsertion_groups');
if (!isset($productinsertion_groups))
{
	$productinsertion_groups = array();
}
@reset($productinsertion_groups);
while ($i = @each($productinsertion_groups))
{
	$ilance->template->parse_loop('main', 'productinsertionfees' . $i['value']['groupid']);
}

// #### service insertion fees / groups ########################
$ilance->template->parse_loop('main', 'serviceinsertion1_groups');
$ilance->template->parse_loop('main', 'serviceinsertion_groups');
if (!isset($serviceinsertion_groups))
{
	$serviceinsertion_groups = array();
}
@reset($serviceinsertion_groups);
while ($i = @each($serviceinsertion_groups))
{
	$ilance->template->parse_loop('main', 'serviceinsertionfees' . $i['value']['groupid']);
}

// #### product final value fees / groups ######################
$ilance->template->parse_loop('main', 'productfv1_groups');
$ilance->template->parse_loop('main', 'productfv_groups');	
if (!isset($productfv_groups))
{
	$productfv_groups = array();
}
@reset($productfv_groups);
while ($i = @each($productfv_groups))
{
	$ilance->template->parse_loop('main', 'productfvfees' . $i['value']['groupid']);
}

// #### service final value fees / groups ######################
$ilance->template->parse_loop('main', 'servicefv1_groups');
$ilance->template->parse_loop('main', 'servicefv_groups');
if (!isset($servicefv_groups))
{
    $servicefv_groups = array();
}
@reset($servicefv_groups);
while ($i = @each($servicefv_groups))
{
	$ilance->template->parse_loop('main', 'servicefvfees' . $i['value']['groupid']);
}

// #### service buget groups ###################################
$ilance->template->parse_loop('main', 'budget_groups');
$ilance->template->parse_loop('main', 'budget1_groups');
if (!isset($budget_groups))
{
	$budget_groups = array();
}
@reset($budget_groups);
while ($i = @each($budget_groups))
{
	$ilance->template->parse_loop('main', 'budgetfees' . $i['value']['groupid']);
}

// #### shippers ###############################################
$ilance->template->parse_loop('main', 'shippers');
$ilance->template->parse_loop('main', 'paytypes');

// #### payment types ##########################################
$ilance->template->parse_if_blocks('main');
$ilance->template->pprint('main', $pprint_array);
exit();

/*======================================================================*\
|| ####################################################################
|| # Downloaded: Thu, Jul 31st, 2014
|| ####################################################################
\*======================================================================*/
?>